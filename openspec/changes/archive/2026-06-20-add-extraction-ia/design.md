## Context

Les reçus soumis par l'utilisateur contiennent du texte brut (`texte_source`) qui doit être transformé en dépenses structurées. Le projet utilise Laravel 13 avec une base SQLite/MySQL. Un modèle `Recu` existe déjà avec une relation `hasMany` vers `Depense` (modèle à créer). Aucune capacité d'extraction IA n'existe actuellement.

Le package `laravel/ai` doit être installé pour fournir les Agents avec `HasStructuredOutput`. Le provider cible est Groq, fixé explicitement via `#[Provider(Lab::Groq)]`.

## Goals / Non-Goals

**Goals:**
- Extraire automatiquement les dépenses depuis le texte d'un reçu via un Agent IA structuré
- Persister les articles extraits comme lignes `Depense` liées au `Recu`
- Gérer tous les cas d'échec sans remonter d'erreur à l'utilisateur
- Assurer la sécurité par périmètre utilisateur et l'optimisation N+1

**Non-Goals:**
- Interface utilisateur pour l'extraction (hors périmètre)
- Support de plusieurs providers IA simultanés
- Extraction de données autres que les dépenses (dates, magasins, etc.)
- Appels synchrones sans queue

## Decisions

### Architecture Agent + Job (vs. contrôleur synchrone)

L'appel à l'IA est délégué à un Job dispatché sur une queue, pas exécuté synchrone dans le contrôleur. Cela évite de bloquer la réponse HTTP et permet de retenter en cas d'échec.

### Provider explicite sur l'Agent vs. provider global

`#[Provider(Lab::Groq)]` est fixé directement sur `ExtraireDepensesAgent` plutôt que de dépendre du provider par défaut global. Cela garantit que l'extraction utilise toujours Groq même si le provider par défaut change.

### Mapping clés accentuées → colonnes sans accent

L'Agent renvoie un JSON avec des clés accentuées (`libellé`, `quantité`, `catégorie`) conformément au brief. Les colonnes DB utilisent des noms sans accent (`libelle`, `quantite`, `categorie`). Le Job assure la transformation.

`prix_unitaire` est identique dans les deux domaines et n'a pas besoin de mapping.

### cascadeOnDelete en migration uniquement

La contrainte `cascadeOnDelete()` est déclarée dans la migration (`$table->foreignId('recu_id')->constrained()->cascadeOnDelete()`). Le modèle Eloquent `Recu` définit une relation `hasMany` simple sans cascade explicite.

### Tests avec fake()

`ExtraireDepensesAgent::fake()` permet de simuler les réponses de l'agent sans appel Groq réel, couvrant les 4 scénarios.

## Architecture

| Couche | Composant | Rôle |
|---|---|---|
| Agent | `ExtraireDepensesAgent` | Appel LLM via Groq, `#[Provider(Lab::Groq)]`, `HasStructuredOutput`, `schema()` avec clés accentuées |
| Job | `ExtraireDepensesDuRecu` | Orchestration : appel agent → mapping clés → persistance |
| Controller | `RecuController::store` | Dispatch du Job après création du Recu |
| Model | `Depense` | `belongsTo Recu`, fillable sans accents : `libelle`, `quantite`, `prix_unitaire`, `categorie` |
| Enum | `RecuStatus` | `en_attente`, `traite`, `echoue` |
| Enum | `DepenseCategorie` | `alimentaire`, `boissons`, `hygiène`, `entretien`, `autre` |

### Flux détaillé

1. `POST /recus` → `RecuController::store` crée le Recu (`statut = en_attente`)
2. `ExtraireDepensesDuRecu::dispatch($recu)->onQueue('extractions')`
3. Job → `ExtraireDepensesAgent::handle($recu->texte_source)` → réponse structurée
4. **Succès articles** : mapping des clés → `$recu->depenses()->createMany(mapped)` → `statut = traite`, `payload_ia = réponse`
5. **Succès zéro article** : `statut = traite`, zéro dépense, `payload_ia` conservé
6. **Échec** (exception/timeout/schéma) : `statut = echoue`, `error_message = message`, log

### Mapping Agent → DB (dans le Job)

```php
$agentResponse = $agent->handle($recu->texte_source);
// "libellé" → libelle, "quantité" → quantite, "catégorie" → categorie
$mapped = collect($agentResponse['articles'])->map(fn ($a) => [
    'libelle'       => $a['libellé'],
    'quantite'      => $a['quantité'],
    'prix_unitaire' => $a['prix_unitaire'],
    'categorie'     => $a['catégorie'],
]);
```

### Casts Eloquent

| Model | Colonne | Cast |
|---|---|---|
| `Recu` | `statut` | `RecuStatus` (string-backed enum) |
| `Recu` | `payload_ia` | `array` (JSON) |
| `Depense` | `categorie` | `DepenseCategorie` (string-backed enum) |
| `Depense` | `prix_unitaire` | `decimal:2` |

### Migration clé étrangère

```php
Schema::create('depenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('recu_id')->constrained()->cascadeOnDelete();
    $table->string('libelle');
    $table->integer('quantite');
    $table->decimal('prix_unitaire', 10, 2);
    $table->string('categorie');
    $table->timestamps();
});
```

### Ownership

- `RecuController::index` : `Auth::user()->recus()->with('depenses')->latest()->get()`
- `RecuPolicy::view` : `$recu->user_id === Auth::id()`
- Interdiction de `Recu::all()` / `Depense::all()` sans scope utilisateur

### Eager loading

- `index` : `with('depenses')` dans la query
- `show` : `$recu->load('depenses')` après authorization
- `$with` ou chargement explicite — pas de N+1

## Risks / Trade-offs

- **Provider Groq indisponible** → Le Job gère par `try/catch` et passe en `echoue`. Aucun retry automatique dans cette version.
- **Timeout API** → Configurable via timeout de l'Agent. En cas de timeout, `echoue`.
- **Schéma non respecté** → La validation du structured output est assurée par `HasStructuredOutput`. Si la réponse ne matche pas, le cast `array` ou la validation métier dans le Job détecte l'anomalie.
- **Texte ambigu** → Si l'agent ne détecte aucun article, c'est un succès avec zéro dépense, pas une erreur.
