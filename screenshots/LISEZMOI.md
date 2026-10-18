# Captures d'écran de Ploopi

Prises le 9 octobre 2026 avec Playwright (Chromium, 1440 px de large) sur une instance
de démonstration : installation par l'assistant, les dix modules installés par
Système → Installation et instanciés dans l'espace « Val Fleuri », données fictives
(agents, réservations, planning, actualités, wiki, annuaire, formulaires, requêtes,
documents, galerie, site web). Code de `master` après les PR #74 et #76.

| Préfixe | Écrans |
|---|---|
| `01`–`13` | Connexion, administration de l'espace (espace, modules, paramètres, utilisateurs, autorisations, rôles, groupe), administration système (installation, paramètres, utilisateurs, outils) |
| `20`–`24` | Formulaires : liste, réponses, édition |
| `30`–`32` | Réservations : planning, suivi, administration |
| `40`–`42` | Requêtes : liste, éditeur, graphique |
| `50`–`51` | Annuaire |
| `60`–`62` | Documents (GED) |
| `65`–`68` | Galeries photo : liste, édition, enregistrement, affichage |
| `70`–`72` | Actualités : liste, fiche, administration |
| `75` | Planning |
| `80`–`84` | Site web : articles, arborescence, rubrique, statistiques, édition en ligne |
| `90`–`92` | Wiki |
| `95`–`96` | Profil, messagerie |
| `A0`–`A3` | Site public (ancien gabarit « exemple », remplacé par « corporate ») |

## `ploopi3/` : le même parcours sous le nouvel habillage

Refaites le 10 octobre 2026, après le renforcement du contraste, les jeux d'icônes et
la reprise des écrans d'administration et des modules. Même outil, même largeur
(1440 × 900, 390 × 844 pour le téléphone), barres de défilement visibles, sur une
instance de démonstration aux données comparables à celles du dossier principal
(espace « Val Fleuri », sous-espace « Mairie de Fleurville », les dix modules installés
et instanciés, données fictives), en habillage `ploopi3` (voir
[`doc/habillage.md`](../doc/habillage.md)). Les numéros reprennent ceux du dossier
principal pour comparer écran par écran ; les suffixes en lettre sont des états
supplémentaires.

| Fichier | Écran |
|---|---|
| `01_connexion`, `01b`–`01c` (téléphone), `01d` (mot de passe expiré), `01e` (erreur) | Connexion |
| `02`–`07` | Administration de l'espace : espace, modules, paramètres, utilisateurs (`05b` : sélecteur de date), rôles, menu déroulant |
| `10`–`11` | Administration système : installation, outils |
| `20`, `24`, `30`, `40`, `50`, `60`, `65`, `70`, `72`, `75`, `80`, `81`, `90` | Un écran par module (`24` : édition d'un formulaire, `72` : rédaction d'une actualité, `81` : gestion du site web) |
| `95`, `96`, `96b` | Profil, messagerie, nouveau message |

Planches du jeu d'icônes unifié (Tabler Icons), générées par
`tools/icones/generer.js --planche` : `ploopi3/icones_planche.png`, les 127 images
matricielles de l'administration (les 24 SVG de la GED n'y figurent pas) ;
`ploopi3/icones_modules_planche.png`, les 278 images des modules et des types de
fichiers du socle.
