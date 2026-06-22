# Assistant Dépenses Laravel

## Présentation

Assistant Dépenses est une application Laravel permettant à un commerçant de transformer automatiquement le texte brut de ses reçus fournisseurs en dépenses structurées grâce à l'intelligence artificielle.

L'utilisateur colle le contenu d'un reçu (souvent mal formaté ou écrit en darija), puis l'application extrait automatiquement :

* le libellé de l'article
* la quantité
* le prix unitaire
* la catégorie

Les données extraites sont ensuite validées, typées et enregistrées en base de données.

---

# Problématique métier

Si Brahim possède une petite épicerie et reçoit plusieurs dizaines de reçus fournisseurs chaque mois.

Ses problèmes :

* reçus papier difficiles à exploiter
* absence de suivi des dépenses
* saisie manuelle trop longue
* impossibilité de connaître les dépenses par catégorie

L'application automatise entièrement cette étape grâce à l'IA.

---

# Fonctionnalités

## Authentification

* Inscription
* Connexion
* Déconnexion

## Gestion des reçus

* Liste des reçus
* Consultation du détail
* Suppression
* Suivi du statut

## Extraction IA

* Analyse automatique du reçu
* Structured Output garanti
* Validation du schéma
* Enregistrement en base

## Suivi des dépenses

* Liste complète
* Filtre par catégorie
* Catégories formatées

---

# Architecture Technique

## Pourquoi utiliser une Queue ?

L'appel à l'API IA est lent.

Sans Queue :

1. utilisateur soumet un reçu
2. Laravel attend la réponse Groq
3. page bloquée pendant plusieurs secondes

Avec Queue :

1. utilisateur soumet un reçu
2. Laravel crée un Job
3. Job envoyé dans une Queue
4. réponse immédiate à l'utilisateur
5. Worker traite le reçu en arrière-plan

---

## Workflow complet

Utilisateur
↓
Création du reçu
↓
Status = Pending
↓
Dispatch Job
↓
Queue
↓
Worker
↓
Appel IA
↓
Validation Structured Output
↓
Création des dépenses
↓
Status = Processed

---

# Technologies

* Laravel 12
* Laravel AI SDK
* Groq API
* MySQL
* Laravel Queue
* Eloquent ORM
* Pest (bonus)

---

# Base de données

## Table recus

| Champ        | Type        |
| ------------ | ----------- |
| id           | bigint      |
| user_id      | foreign key |
| texte_source | text        |
| status       | enum        |
| ai_payload   | json        |
| created_at   | timestamp   |

---

## Table depenses

| Champ         | Type        |
| ------------- | ----------- |
| id            | bigint      |
| recu_id       | foreign key |
| libelle       | string      |
| quantite      | integer     |
| prix_unitaire | decimal     |
| categorie     | enum        |

---

# Relations Eloquent

Un reçu possède plusieurs dépenses.

```php
class Recu extends Model
{
    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }
}
```

```php
class Depense extends Model
{
    public function recu()
    {
        return $this->belongsTo(Recu::class);
    }
}
```

---

# Enums

## ReceiptStatus

```php
enum ReceiptStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';
}
```

## ExpenseCategory

```php
enum ExpenseCategory: string
{
    case Alimentaire = 'alimentaire';
    case Boissons = 'boissons';
    case Hygiene = 'hygiene';
    case Entretien = 'entretien';
    case Autre = 'autre';
}
```

---

# Eloquent Casts

```php
protected function casts(): array
{
    return [
        'status' => ReceiptStatus::class,
        'ai_payload' => 'array',
    ];
}
```

```php
protected function casts(): array
{
    return [
        'categorie' => ExpenseCategory::class,
    ];
}
```

---

# Validation avec Form Request

## Création

```bash
php artisan make:request StoreRecuRequest
```

## Règles

```php
public function rules(): array
{
    return [
        'texte_source' => [
            'required',
            'string',
            'min:10',
            'max:10000'
        ]
    ];
}
```

Cette validation est exécutée avant tout appel IA afin d'éviter un coût API inutile.

---

# Queue & Job

## Création du Job

```bash
php artisan make:job ExtraireDepensesDuRecu
```

Fichier :

```text
app/Jobs/ExtraireDepensesDuRecu.php
```

## Dispatch du Job

```php
ExtraireDepensesDuRecu::dispatch($recu);
```

## Worker

```bash
php artisan queue:work
```

Le Worker surveille la Queue et exécute automatiquement les Jobs.

---

# Structured Output

Le modèle doit toujours respecter le contrat suivant :

```json
{
  "articles": [
    {
      "libellé": "string",
      "quantité": 1,
      "prix_unitaire": 10.50,
      "catégorie": "alimentaire"
    }
  ],
  "total_estimé": 10.50,
  "devise": "MAD"
}
```

---

# Intégration Laravel AI

Configuration :

```env
GROQ_API_KEY=xxxxxxxx
```

L'application utilise le SDK Laravel AI.

L'appel au modèle est abstrait derrière le SDK afin de pouvoir remplacer Groq par un autre fournisseur sans modifier le code métier.

---

# Prévention du N+1

Chargement des dépenses :

```php
$recus = Recu::with('depenses')->get();
```

Debugbar est utilisée pour vérifier l'absence de requêtes N+1.

---

# Installation

## Cloner le projet

```bash
git clone repository-url
```

## Installer les dépendances

```bash
composer install
npm install
```

## Configuration

```bash
cp .env.example .env
php artisan key:generate
```

## Migration

```bash
php artisan migrate
```

## Lancer les files d'attente

```bash
php artisan queue:work
```

## Démarrer l'application

```bash
composer run dev
```

---

# OpenSpec

Le projet utilise OpenSpec pour documenter les fonctionnalités.

Structure :

```text
specs/
├── receipts.md
├── expenses.md
├── ai-extraction.md
├── authentication.md
```

Chaque fonctionnalité suit :

* Proposal
* Specification
* Tasks

---

# AGENTS.md

Le projet contient un fichier AGENTS.md décrivant :

* workflow AI-assisted
* règles de génération de code
* conventions de commits
* stratégie Plan → Build

---

# Tests

Exécution :

```bash
php artisan test
```

ou

```bash
./vendor/bin/pest
```

Les tests d'extraction utilisent les fakes du SDK Laravel AI afin d'éviter les appels réels à Groq.

---

# Auteur

Projet réalisé dans le cadre du brief Assistant Dépenses Laravel.
