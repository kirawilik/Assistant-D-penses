## Data Flow

```
Browser                         DepenseController               DB
  ¦                                     ¦                        ¦
  ¦  GET /depenses?categorie=X          ¦                        ¦
  ¦------------------------------------?¦                        ¦
  ¦                                     ¦                        ¦
  ¦                                     ¦  Depense::query()      ¦
  ¦                                     ¦  ->whereHas('recu',    ¦
  ¦                                     ¦    fn($q) =>           ¦
  ¦                                     ¦    $q->where('user_id',¦
  ¦                                     ¦     Auth::id()))       ¦
  ¦                                     ¦  ->when($cat,          ¦
  ¦                                     ¦    fn($q) =>           ¦
  ¦                                     ¦    $q->where(          ¦
  ¦                                     ¦     'categorie',$cat)) ¦
  ¦                                     ¦  ->with('recu')        ¦
  ¦                                     ¦  ->latest()            ¦
  ¦                                     ¦  ->get()               ¦
  ¦                                     ¦------------------------?¦
  ¦                                     ¦?------------------------¦
  ¦                                     ¦                        ¦
  ¦  View: depenses.index               ¦                        ¦
  ¦  ? select pré-sélectionné           ¦                        ¦
  ¦  ? foreach $depenses                ¦                        ¦
  ¦  ? $depense->categorie->label()     ¦                        ¦
  ¦  ? lien vers recus.show             ¦                        ¦
  ¦?------------------------------------¦                        ¦
```

## Architecture

```
app/
+-- Enums/
¦   +-- DepenseCategorie.php          ? + label()
+-- Models/
¦   +-- Depense.php                   ? existant (inchangé)
+-- Policies/
¦   +-- DepensePolicy.php             ? NOUVEAU
+-- Http/
¦   +-- Controllers/
¦       +-- DepenseController.php     ? NOUVEAU
+-- Providers/
¦   +-- AppServiceProvider.php        ? modifié (Gate::policy)
resources/
+-- views/
    +-- depenses/
        +-- index.blade.php           ? NOUVEAU
routes/
+-- web.php                           ? modifié (route)
tests/
+-- Feature/
    +-- DepensesListeTest.php         ? NOUVEAU
```

## Dépendances

- `Depense::with('recu')` ? `Recu` model existant
- `DepenseCategorie::label()` ? enum existant
- `Auth::user()` ? authentification Laravel Breeze
- `DepensePolicy` ? `Gate::policy` dans AppServiceProvider
