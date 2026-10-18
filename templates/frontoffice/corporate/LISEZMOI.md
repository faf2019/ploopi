# Gabarit frontoffice « corporate »

Gabarit du site public (module `webedit`), seul gabarit frontoffice livré et gabarit
par défaut : une rubrique sans gabarit, ou qui désigne un gabarit absent, est servie
avec celui-ci.

En-tête collant avec menu à deux niveaux, recherche et connexion ; bandeau et cartes
des rubriques sur l'accueil ; colonne latérale (abonnement, blocs, archives,
étiquettes, flux) ; pied de page. Adaptatif (menu replié sous 960 px), thème sombre
selon la préférence du système, aucune dépendance externe (ni police, ni CDN).

## Fichiers

| Fichier | Rôle |
|---|---|
| `index.tpl` | toutes les pages |
| `home.tpl` | page d'accueil : `index.tpl` plus le bandeau et les cartes des rubriques. **Reporter dans `home.tpl` toute modification d'`index.tpl`** (le moteur de template n'a pas d'inclusion) |
| `system_trombi.tpl` | trombinoscope (objet WCE du module `system`) |
| `class_skin.php` | skin des composants du socle rendus dans une page publique |
| `css/corporate.css` | mise en page et composants du gabarit (classes `c-…`) |
| `css/ploopi.css` | composants du socle : blocs, onglets, calendriers (booking, planning), formulaires (forms), trombinoscope |
| `js/corporate.js` | en-tête au défilement, menu mobile, sous-menus, recherche avancée — aucun gestionnaire en ligne |
| `img/favicon.svg` | icône |

## Personnaliser

Les couleurs, rayons et largeur sont des variables en tête de `css/corporate.css`
(`--c-accent`, `--c-hero-from`, `--c-hero-to`, `--c-width`…), redéfinies pour le
thème sombre. Pour une déclinaison, copier le dossier sous un autre nom, changer le
nom passé au constructeur dans `class_skin.php`, puis choisir ce gabarit sur la
rubrique racine dans webedit.

## Contenu attendu dans webedit

- **Menu principal** : rubriques de la racine n° 1 (`root1`) et leurs sous-rubriques.
- **Pied de page** : rubriques de la racine n° 2 (`root2`).
- **Accueil** : le titre du bandeau est le titre de l'article d'accueil, le texte celui
  de la description de l'espace (méta-description) ; une carte par rubrique de la racine
  n° 1, avec sa description.
- **Colonne latérale** : les articles hors rubrique nommés `bloc1`, `bloc2`, `bloc3`.
