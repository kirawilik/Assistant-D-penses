## Purpose

Extraire automatiquement les dépenses structurées depuis le texte brut d'un reçu via un Agent IA, avec persistance en base et gestion complète des erreurs.

## Requirements

### Requirement: Extraction structurée des dépenses depuis un reçu

Le système SHALL extraire automatiquement les dépenses depuis le `texte_source` d'un Reçu via un Agent IA structuré. L'agent SHALL utiliser `HasStructuredOutput` avec un schéma JSON contenant les clés accentuées (`libellé`, `quantité`, `prix_unitaire`, `catégorie`, `total_estimé`, `devise`). Le Job SHALL mapper ces clés vers les colonnes sans accent de la table `depenses` (`libelle`, `quantite`, `prix_unitaire`, `categorie`).

Le schéma SHALL être :
```json
{
  "articles": [
    {
      "libellé": "string",
      "quantité": "number",
      "prix_unitaire": "number",
      "catégorie": "alimentaire|boissons|hygiène|entretien|autre"
    }
  ],
  "total_estimé": "number",
  "devise": "string"
}
```

#### Scenario: Extraction réussie avec plusieurs articles

- **WHEN** un Reçu avec `texte_source` contenant "Pain 2€, Lait 3€, Savon 4€" est soumis
- **THEN** le statut du Reçu passe à `traite`
- **AND** 3 lignes Depense sont créées, liées au Reçu
- **AND** `payload_ia` contient la réponse brute de l'agent

#### Scenario: Extraction réussie sans aucun article détecté

- **WHEN** un Reçu avec `texte_source` vide de sens ("Bonjour") est soumis
- **THEN** le statut du Reçu passe à `traite`
- **AND** aucune Depense n'est créée
- **AND** `payload_ia` contient la réponse brute

### Requirement: Gestion des échecs d'extraction

En cas d'échec technique (exception agent, timeout, provider indisponible) ou de réponse ne respectant pas le schéma attendu, le système SHALL marquer le Reçu comme `echoue`, enregistrer le message d'erreur dans `error_message`, et ne créer aucune Depense. Aucune exception ne SHALL remonter à l'utilisateur.

#### Scenario: Échec par exception du provider

- **WHEN** l'agent ExtraireDepensesAgent lève une exception (API injoignable / timeout)
- **THEN** le statut du Reçu passe à `echoue`
- **AND** `error_message` contient le message d'erreur
- **AND** aucune Depense n'est créée

#### Scenario: Échec par réponse hors schéma

- **WHEN** l'agent renvoie un payload ne respectant pas le schéma JSON attendu
- **THEN** le statut du Reçu passe à `echoue`
- **AND** `error_message` mentionne la validation du schéma
- **AND** aucune Depense n'est créée

### Requirement: Mapping des clés Agent → base de données

Le Job SHALL transformer les clés accentuées de la réponse de l'agent en colonnes sans accent pour la persistance :

| Clé Agent (accentuée) | Colonne DB |
|---|---|
| `libellé` | `libelle` |
| `quantité` | `quantite` |
| `prix_unitaire` | `prix_unitaire` |
| `catégorie` | `categorie` |

`prix_unitaire` est inchangé car identique dans les deux domaines.

### Requirement: Contraintes de données

Les colonnes de la table `depenses` SHALL être : `id`, `recu_id`, `libelle`, `quantite`, `prix_unitaire`, `categorie`, `created_at`, `updated_at`.

La contrainte de clé étrangère `recu_id` SHALL utiliser `cascadeOnDelete()` au niveau migration. Le modèle Eloquent `Recu` SHALL déclarer la relation `hasMany` sans `cascadeOnDelete()`.

Les énumérations SHALL être :
- `RecuStatus` : `en_attente`, `traite`, `echoue`
- `DepenseCategorie` : `alimentaire`, `boissons`, `hygiène`, `entretien`, `autre`

#### Scenario: Suppression d'un reçu avec cascade

- **WHEN** un Reçu est supprimé
- **THEN** toutes ses Depenses associées sont supprimées automatiquement par la cascade en base

### Requirement: Sécurité et périmètre utilisateur

Un utilisateur SHALL uniquement accéder à ses propres Reçus et Depenses. Toute requête MUST être filtrée par l'utilisateur authentifié. Les requêtes globales `Recu::all()` et `Depense::all()` sont interdites pour l'affichage utilisateur.

#### Scenario: Isolation des données utilisateur

- **WHEN** un utilisateur consulte la liste de ses reçus
- **THEN** seuls ses propres reçus sont retournés
- **AND** les reçus des autres utilisateurs ne sont pas visibles

### Requirement: Chargement eager (N+1)

Toute liste ou détail de Reçu MUST charger les Depenses associées via `with('depenses')` ou `load('depenses')` pour éviter les requêtes N+1.

#### Scenario: Pas de N+1 sur l'index

- **WHEN** la page d'index des reçus est chargée
- **THEN** les dépenses sont chargées avec eager loading (`with('depenses')`)
- **AND** aucune requête supplémentaire par reçu n'est exécutée
