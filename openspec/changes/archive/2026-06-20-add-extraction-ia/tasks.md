## 1. Dépendances et configuration

- [x] 1.1 Installer le package `laravel/ai` : `composer require laravel/ai`
- [x] 1.2 Configurer la variable d'environnement `GROQ_API_KEY` dans `.env`

## 2. Migrations et base de données

- [x] 2.1 Créer la migration pour la table `depenses` : `recu_id` (foreign, cascadeOnDelete), `libelle`, `quantite`, `prix_unitaire` (decimal 10,2), `categorie`
- [x] 2.2 Créer la migration pour ajouter `error_message` (text, nullable) à la table `recus`
- [x] 2.3 Exécuter `php artisan migrate`

## 3. Énumérations

- [x] 3.1 Créer `app/Enums/DepenseCategorie.php` : `alimentaire`, `boissons`, `hygiène`, `entretien`, `autre` (string-backed)
- [x] 3.2 Aligner `app/Enums/RecuStatus.php` : cas `en_attente`, `traite`, `echoue` (string-backed)

## 4. Modèles Eloquent

- [x] 4.1 Créer `app/Models/Depense.php` : fillable (`libelle`, `quantite`, `prix_unitaire`, `categorie`), casts (`categorie → DepenseCategorie`, `prix_unitaire → decimal:2`), relation `belongsTo Recu`
- [x] 4.2 Mettre à jour `app/Models/Recu.php` : ajouter `error_message` aux fillable, casts (`statut → RecuStatus`, `payload_ia → array`), relation `hasMany depenses()` sans cascadeOnDelete

## 5. Agent IA

- [x] 5.1 Créer `app/Agents/ExtraireDepensesAgent.php` : `#[Provider(Lab::Groq)]`, implémente `HasStructuredOutput`, méthode `schema()` retournant le contrat JSON avec clés accentuées

## 6. Job d'extraction

- [x] 6.1 Créer `app/Jobs/ExtraireDepensesDuRecu.php` : prend un `Recu` en paramètre, `handle()` avec appel agent, mapping des clés accentuées vers colonnes sans accent, persistance via `createMany()`
- [x] 6.2 Implémenter le cas succès (articles → création Depenses, `statut = traite`, `payload_ia` conservé)
- [x] 6.3 Implémenter le cas zéro article (`statut = traite`, aucune dépense créée, ce n'est pas une erreur)
- [x] 6.4 Implémenter la gestion d'échec (exception/timeout → `statut = echoue`, `error_message` renseigné, log, pas de crash)
- [x] 6.5 Implémenter la validation du schéma de réponse (payload invalide → `statut = echoue`)

## 7. Contrôleur et dispatch

- [x] 7.1 Mettre à jour `RecuController::store` : dispatcher `ExtraireDepensesDuRecu::dispatch($recu)->onQueue('extractions')` après création du Recu
- [x] 7.2 Configurer la queue `extractions` dans `config/queue.php`

## 8. Sécurité et performance

- [x] 8.1 Créer `app/Policies/RecuPolicy.php` : méthode `view` vérifiant `$recu->user_id === Auth::id()`
- [x] 8.2 Mettre à jour `RecuController::index` : `Auth::user()->recus()->with('depenses')->latest()->get()`
- [x] 8.3 Mettre à jour `RecuController::show` : `$recu->load('depenses')` après authorization
- [x] 8.4 Enregistrer la Policy dans `AuthServiceProvider`

## 9. Tests Pest

- [x] 9.1 Créer le fichier de test `tests/Feature/ExtraireDepensesTest.php`
- [x] 9.2 Test : extraction réussie avec 3 articles (vérifier `statut = traite`, 3 Depenses créées, `payload_ia` présent)
- [x] 9.3 Test : extraction réussie avec zéro article détecté (vérifier `statut = traite`, 0 Depense créée)
- [x] 9.4 Test : échec par exception du provider (vérifier `statut = echoue`, `error_message` renseigné)
- [x] 9.5 Test : échec par réponse hors schéma (vérifier `statut = echoue`, message d'erreur sur validation)
- [x] 9.6 Exécuter la suite de tests : `php artisan test --filter=ExtraireDepensesTest`
