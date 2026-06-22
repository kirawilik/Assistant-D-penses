## Why

Les reçus soumis contiennent du texte brut (`texte_source`) qui doit être transformé en dépenses structurées. L'extraction IA automatisée évite la saisie manuelle et accélère le suivi budgétaire.

## What Changes

- **Agent** `ExtraireDepensesAgent` avec `HasStructuredOutput` et `#[Provider(Lab::Groq)]` — schéma structuré avec clés accentuées (`libellé`, `quantité`, `catégorie`)
- **Job** `ExtraireDepensesDuRecu` dispatché sur une queue après création du reçu, contenant toute la logique : appel agent → mapping clés accentuées → colonnes sans accent → persistance
- **Mapping dans le Job** : `"libellé" → libelle`, `"quantité" → quantite`, `"catégorie" → categorie` ; `prix_unitaire` inchangé
- **Dispatch** depuis `RecuController::store` → `ExtraireDepensesDuRecu::dispatch($recu)->onQueue('extractions')`
- **RecuStatus** : `en_attente | traite | echoue`
- **DepenseCategorie** : `alimentaire | boissons | hygiène | entretien | autre`
- **Colonnes DB** : `libelle`, `quantite`, `prix_unitaire`, `categorie` (sans accents)
- **Migration** `depenses` avec `recu_id` → `cascadeOnDelete()` en base ; modèle Eloquent sans `cascadeOnDelete()`
- **Ownership** : scope utilisateur + Policy Laravel
- **Eager loading** : `Recu::with('depenses')` — pas de N+1
- **Tests Pest** : 4 scénarios avec `ExtraireDepensesAgent::fake()`

## Capabilities

### New Capabilities
- `extraction-ia`: Extraction automatique des dépenses depuis le texte d'un reçu via Agent IA structuré, avec persistance en base, mapping clés accentuées → colonnes sans accent, et gestion complète des erreurs

### Modified Capabilities

<!-- Aucune spec existante modifiée -->

## Impact

- `composer require laravel/ai`
- `app/Agents/ExtraireDepensesAgent.php` — nouvel Agent avec schéma structuré et Provider Groq
- `app/Jobs/ExtraireDepensesDuRecu.php` — nouveau Job d'orchestration extraction
- `app/Models/Depense.php` — nouveau modèle
- `app/Enums/RecuStatus.php` — alignement sur `en_attente | traite | echoue`
- `app/Enums/DepenseCategorie.php` — nouvel enum
- `app/Http/Controllers/RecuController.php` — dispatch du Job dans store()
- `app/Policies/RecuPolicy.php` — règles d'ownership
- `database/migrations/` — table `depenses` + colonne `error_message` sur `recus`
- `tests/` — tests Pest pour les 4 scénarios d'extraction
