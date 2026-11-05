# Construire des filtres SQL

Les méthodes historiques de `query_select`, `query_update`, `query_delete` et
`query_insert` conservent leurs signatures et leur SQL par défaut. Les ajouts
ci-dessous sont facultatifs et utilisent le même formateur (`%d`, `%f`, `%s`…).

## Grouper des conditions

`add_where_group()` ajoute un groupe parenthésé aux autres filtres, toujours
relié par `AND`. Son opérateur interne vaut `OR` par défaut, ou `AND` :

```php
$q = new ploopi\query_select();
$q->add_from('contacts');
$q->add_where('id_workspace = %d', $idWorkspace);
$q->add_where_group(array(
    array('rawsql' => 'nom = %s', 'values' => $nom),
    array('rawsql' => 'email = %s', 'values' => $email),
));
// WHERE id_workspace = … AND ((nom = …) OR (email = …))
```

Chaque entrée contient `rawsql` et éventuellement `values` (scalaire ou liste de
paramètres, comme `add_where`). Les marqueurs positionnels repartent de 1 pour
chaque entrée. Chaque condition est aussi parenthésée pour préserver sa priorité.
Un groupe vide, une clause vide ou un opérateur autre que `AND`/`OR` déclenche
`InvalidArgumentException` avant tout ajout à la requête.

## Listes typées et valeurs nulles

Sur SELECT, UPDATE et DELETE :

```php
$q->add_where_in('id', array(2, 4, 8));           // IN, entiers par défaut
$q->add_where_in('prix', array(1.5, 2), 'f');     // flottants
$q->add_where_in('statut', array('clos'), 's', true); // NOT IN, chaînes
$q->add_where_null('date_suppression');          // IS NULL
$q->add_where_null('date_validation', true);     // IS NOT NULL
```

Seuls les types `d`, `f` et `s` sont acceptés ; les valeurs sont converties ou
échappées par `sqlformat`, avec ses conventions existantes (notamment le retrait
des espaces en bordure des éléments de liste). Les valeurs `null` ou non scalaires
(tableaux, objets) dans une liste sont refusées : utiliser un filtre `IS NULL` explicite selon la logique souhaitée.

Une liste vide produit `1 = 0` pour `IN` et `1 = 1` pour `NOT IN`. Un UPDATE ou
DELETE avec `IN []` ne touche donc aucune ligne. `NOT IN []` ne restreint aucune
ligne : ajouter les autres filtres nécessaires avant une modification.

Les expressions SQL (premier argument), comme les clauses `rawsql`, doivent venir
du code de l'application. Elles ne sont pas des identifiants automatiquement
échappés. Les données utilisateur passent exclusivement dans les valeurs.
`remove_where()` retire également tous ces nouveaux filtres.

## Éliminer les doublons

```php
$q->add_select('statut');
$q->set_distinct();       // SELECT DISTINCT statut …
$q->set_distinct(false);  // retour à SELECT statut …
```

`set_distinct()` est propre à `query_select`, désactivé par défaut et indépendant
de `remove_select()`. Il conserve les clauses de tri, de regroupement et de limite.

## Tester l’existence d’une relation

`add_where_exists()` et `add_where_not_exists()` acceptent un objet
`query_select`. Ils sont disponibles sur SELECT, UPDATE et DELETE :

```php
$relation = new ploopi\query_select();
$relation->add_select('1');
$relation->add_from('commandes c');
$relation->add_where('c.id_contact = contacts.id');
$relation->add_where('c.statut = %s', $statut);
$q->add_where_exists($relation);
// AND EXISTS (SELECT 1 FROM commandes c WHERE c.id_contact = contacts.id AND …)
// Ou : $q->add_where_not_exists($relation);
```

La sous-requête est copiée lors de l'ajout : modifier ensuite `$relation` ne
change pas le filtre déjà enregistré. Ses paramètres sont formatés avec la
connexion courante de la requête parente, y compris après `set_db()`. Les
sous-requêtes imbriquées sont acceptées et les valeurs contenant `%` ne sont pas
interprétées une seconde fois. Une sous-requête ne produisant aucun SQL est
refusée par `InvalidArgumentException`. `remove_where()` retire ces filtres.

## Paginer un SELECT

```php
$q->add_orderby('id');       // tri stable choisi par l’application
$q->add_pagination(3, 20);   // LIMIT 40, 20 : pages numérotées depuis 1
```

La page et la taille doivent être des entiers strictement positifs, ou des chaînes
validées comme tels par `FILTER_VALIDATE_INT`. Les fractions, booléens et valeurs
hors capacité des entiers PHP sont refusés. Le calcul de l'offset est contrôlé
contre les débordements. En cas d'erreur, `InvalidArgumentException` laisse la
limite précédente intacte.

Cette méthode appartient uniquement à `query_select`. Elle remplace la limite
précédente ; `add_limit()` peut la remplacer à son tour, et `remove_limit()` la
supprime. Aucun tri n'est ajouté automatiquement et les autres clauses restent
inchangées.

## Retirer des clauses pour réutiliser un objet

Les retraits vident uniquement la clause indiquée et peuvent être répétés :

| Méthode ajoutée | Classes concernées |
|---|---|
| `remove_raw()` | Toutes les classes `query_*` |
| `remove_from()` | SELECT, UPDATE, DELETE |
| `remove_set()`, `remove_table()` | INSERT |
| `remove_leftjoin()` | UPDATE (déjà disponible sur SELECT) |
| `remove_innerjoin()`, `remove_leftjoin()`, `remove_delete()` | DELETE |

`remove_delete()` retire les cibles d'un DELETE multitable, sans retirer FROM ni
ses jointures. `remove_from()` et `remove_table()` ne retirent pas les autres
clauses : ajouter une nouvelle table avant de générer une requête réutilisée.
Après `remove_set()`, ajouter les nouvelles affectations avant d'exécuter un
INSERT.

## Vérifier

`tests/run.sh 47_query_builder` vérifie le SQL sans base ni Composer.
La suite `10_sql`, via `tests/instance.sh test` ou `tests/docker.sh 8.4`, vérifie
l'exécution avec mysqli et MariaDB/MySQL, notamment les listes vides en écriture.
