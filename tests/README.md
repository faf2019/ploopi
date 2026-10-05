# Tests Ploopi

Suite de tests maison, sans dépendance externe : le dépôt n'embarque volontairement
aucun framework de test (cf. `CLAUDE.md` §14). Chaque suite est un script PHP autonome
qui affiche ses contrôles et retourne `0` si tous passent.

## Lancer les tests

```sh
tests/run.sh            # toutes les suites
tests/run.sh 02 03      # seulement les suites 02_* et 03_*
php tests/02_str.php    # une suite isolée
```

Prérequis : PHP en ligne de commande (>= 7.0, testé jusqu'à 8.4). Aucune installation
de Ploopi n'est nécessaire pour les suites unitaires : à défaut de `config/config.php`,
le harnais définit une configuration de test minimale.

## Suites

| Suite | Contenu | Prérequis |
|---|---|---|
| `01_sources` | Lint PHP de tout l'arbre + conventions (en-tête GPL, tabulations, nommage des classes, autoload, UTF-8) | — |
| `02_str` | `ploopi\str` : coupe, accents, URL, échappement HTML, indexation | `vendor/` pour HTMLPurifier |
| `03_date` | `ploopi\date` : format pivot `YmdHis`, conversions locales, fuseaux, jours fériés | — |
| `04_arr` | `ploopi\arr` : exports JSON/CSV/HTML/XML, normalisation des clés, pagination | — |
| `05_sql` | `ploopi\sqlformat`, `query_*`, `db` : échappement et exécution réelle, charges d'injection | base |
| `06_data_object` | ORM : cycle de vie, `setvalues`, `setuwm`, collections, clé composite | base |
| `07_securite` | `inputfilter`, `security`, `crypt`, `cipher` : filtrage, mots de passe, chiffrement d'URL | — |
| `08_template` | Moteur de template : variables, blocs imbriqués, rendu des 52 `.tpl` du dépôt | — |
| `09_installation` | Paquets `install/<module>/` : XML, versions, `id_action` vs `ACTION_*`, exécution des `structure.sql` | base |

Les contrôles dont le prérequis manque sont **ignorés** (`--`), jamais comptés comme
réussis : le bilan les affiche à part.

## Écrire une suite

Créer `tests/NN_nom.php` — la découverte est automatique :

```php
require_once __DIR__.'/bootstrap.php';
amorcer_socle();                       // autoload + constantes, hors contexte web

T::section('ma_classe::ma_methode');
T::eq('libellé du contrôle', 'attendu', ma_classe::ma_methode('entrée'));

T::bilan('NN_nom');                    // bilan + code de sortie
```

Assertions disponibles : `T::chk()` (booléen), `T::eq()` / `T::neq()` (égalité stricte),
`T::contient()` / `T::contientpas()` (sous-chaîne), `T::skip()` (prérequis absent).

Deux règles de rigueur :

1. **Jamais d'assertion complaisante.** Un contrôle qui dépend d'un prérequis doit être
   précédé du contrôle de ce prérequis, sinon il passe « à vide » et ne protège de rien.
2. **On teste le comportement réel**, pas le comportement souhaité. Quand le comportement
   constaté est discutable (validation de date purement formelle, débordement de
   `timestamp_add` sur les mois…), il est épinglé par un test et commenté : si quelqu'un
   le corrige un jour, le test le signalera au lieu de le laisser passer inaperçu.

## Constats relevés par les tests

Les tests épinglent plusieurs comportements du socle qui peuvent surprendre. Chacun
est commenté à l'endroit où il est vérifié :

| Où | Constat |
|---|---|
| `06_data_object` | `init_description()` pré-remplit la clé primaire avec `''`. Sous `sql_mode` strict — le défaut depuis MySQL 5.7 / MariaDB 10.2 — l'`INSERT` est **rejeté**. L'enchaînement `init_description()` puis `save()` ne fonctionne donc sur une table auto-incrémentée que sur un serveur permissif. Contournement : `unset($objet->fields['id'])` avant `save()`. |
| `06_data_object` | Après un `open()` en échec, `fields` ne vaut pas `false` (contrairement à `CLAUDE.md` §17) mais contient la clé demandée, sans aucune colonne. |
| `07_securite` | `crypt::htpasswd()` passe la constante `CRYPT_STD_DES` comme **sel** : `crypt()` retourne `*0` pour tout mot de passe. Le `.htpasswd` généré ne donne accès à personne. |
| `07_securite` | Le vecteur d'initialisation de `cipher` est fixe : deux URL identiques donnent le même chiffré. |
| `05_sql` | Un tableau passé en valeur est la **liste des paramètres**. Pour alimenter `%e`/`%t`/`%g`, l'encapsuler : `add_where('id IN (%e)', array($ids))`. |
| `05_sql` | `%s` pose lui-même les apostrophes ; `%r` n'applique aucun traitement. |
| `04_arr` | `arr::tojson()` double-encode par défaut un contenu déjà UTF-8. |
| `08_template` | La directive `<!-- INCLUDE -->` documentée dans `CLAUDE.md` §10 n'est pas implémentée : le commentaire ressort tel quel. |
| `01_sources` | Trois écarts aux conventions, listés nommément comme dérogations. |

## Base de données

Les suites qui ont besoin d'une base lisent `tests/config.php` (modèle :
`tests/config.php.model`). Utiliser une base **dédiée** : les suites y créent et y
suppriment leurs propres tables. Sans ce fichier — et sans `config/config.php` —
ces suites sont ignorées.
