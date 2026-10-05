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

## Base de données

Les suites qui ont besoin d'une base lisent `tests/config.php` (modèle :
`tests/config.php.model`). Utiliser une base **dédiée** : les suites y créent et y
suppriment leurs propres tables. Sans ce fichier — et sans `config/config.php` —
ces suites sont ignorées.
