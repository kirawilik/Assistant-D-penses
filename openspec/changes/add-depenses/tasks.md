# Tâches — add-depenses

### Tâche 1.1 — Ajouter `label()` sur `DepenseCategorie`
- [x] **Fichier :** `app/Enums/DepenseCategorie.php`
- [x] **Action :** Ajouter une méthode `label(): string` qui retourne le nom formaté

### Tâche 2.1 — Créer `DepensePolicy`
- [x] **Fichier :** `app/Policies/DepensePolicy.php`
- [x] **Action :** `viewAny(User $user): bool` → `true`

### Tâche 2.2 — Enregistrer `DepensePolicy`
- [x] **Fichier :** `app/Providers/AppServiceProvider.php`
- [x] **Action :** Ajouter `Gate::policy(Depense::class, DepensePolicy::class);`

### Tâche 3.1 — Créer `DepenseController`
- [x] **Fichier :** `app/Http/Controllers/DepenseController.php`
- [x] **Action :** Méthode `index()` avec scoping, filtre, eager loading

### Tâche 3.2 — Ajouter la route
- [x] **Fichier :** `routes/web.php`
- [x] **Action :** `Route::get('/depenses', ...)` dans le groupe `auth`

### Tâche 4.1 — Créer la vue `depenses/index.blade.php`
- [x] **Fichier :** `resources/views/depenses/index.blade.php`
- [x] **Action :** Select HTML + tableau + N+1 free + message vide

### Tâche 5.1 — Tests `DepensesListeTest`
- [x] **Fichier :** `tests/Feature/DepensesListeTest.php`
- [x] **Actions :** 5 méthodes PHPUnit (isolation, liste, filtre valide, filtre invalide, vide)

### Tâche 5.2 — Vérification N+1 avec Debugbar
- [ ] **Action :** Charger `/depenses`, ouvrir Debugbar, vérifier ≤ 3 requêtes (manuelle)