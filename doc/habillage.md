# Habillage du backoffice : le gabarit `ploopi3`

`ploopi3` a été l'habillage du backoffice des **nouvelles installations** jusqu'au
10 octobre 2026 ; depuis, c'est `ploopi4` ([`ploopi4.md`](ploopi4.md)). Il est livré
**à côté** de `ploopi2`, que gardent les installations existantes tant qu'on ne les
bascule pas. Il se choisit espace par espace et se retire de la même façon : rien à
migrer, rien à désinstaller, retour arrière immédiat.

Il reprend le balisage de `ploopi2` à l'identique : les modules, le rendu du socle
(`ploopi\skin`) et le JavaScript ne voient aucune différence. Seul l'aspect change.

Captures : [`screenshots/ploopi3/`](../screenshots/ploopi3/), à comparer écran par écran
avec celles de `ploopi2` dans [`screenshots/`](../screenshots/).

Sa suite, un shell adaptatif avec thèmes clair et sombre, est le gabarit `ploopi4` :
[`ploopi4.md`](ploopi4.md). Il reprend les feuilles de `ploopi3` pour le contenu des
modules.

---

## 1. Ce que montraient les captures de `ploopi2`

| Constat | Cause dans le code | Réponse de `ploopi3` |
|---|---|---|
| Une photo occupe tout l'arrière-plan ; les panneaux translucides s'arrêtent à mi-hauteur et le texte clair sur sarcelle se lit mal | `#background` en `wallpaper.jpg`, `.simplebloc` en `rgba(255,255,255,0.8)` | fond uni, cartes blanches opaques, contrastes AA |
| Titres de formulaires et du wiki en Times | `h1–h4 { font-family: "Ubuntu", serif }` : police jamais livrée, repli `serif` (de même pour le corps) | pile de polices sans empattement, Open Sans en tête |
| Fenêtres (profil, messages) à demi transparentes tant que la souris n'est pas dessus | `.ploopi_popup > * { opacity: 0.8 }` | fenêtres opaques, ombre portée |
| Une seule barre pour onze modules, l'espace, la recherche et l'utilisateur ; le module courant n'est signalé que par un texte jaune ; le menu d'un module n'apparaît qu'au survol | `index.tpl` | barre de l'utilisateur, barre des modules, et **menu du module courant toujours visible** |
| Trois bandeaux sombres empilés (barre, titre de page, titre de bloc) | `.pagetitle`, `.simplebloc_title` en dégradé sombre | hiérarchie portée par la typographie : titre de page, cartes à en-tête clair |
| Bandeaux de service saturés : annotations vert-jaune, abonnements bleu vif, partages orange, validations saumon | `base_annotation.css`, `base_subscription.css`, `base_share.css`, `base_validation.css` | teinte légère et liseré coloré : le code couleur reste, le bruit disparaît |
| Icônes de quatre générations côte à côte (glyphes blancs sur bleu clair, PNG en dégradé, GIF) | `img/system/`, `img/tickets/`, `img/export/`, `install/<module>/files/img/`… | un seul jeu au trait pour l'administration et les modules (§ 9) |
| Aucun repère de focus ; menus des modules inaccessibles au clavier | `:hover` seul | `:focus-visible`, menus ouverts aussi par `:focus-within` |
| Connexion : boîte de 300 px sur la photo ; « mot de passe perdu » fait glisser un second panneau sous le premier | `index.tpl` | carte centrée, les deux formulaires se remplacent, page lisible sur téléphone |

## 2. Ce qui ne change pas : le contrat avec le socle et les modules

C'est la condition pour ne rien casser.

- **Le balisage produit par `include/classes/skin.php` et par les modules est inchangé.**
  Les feuilles de `ploopi3` reprennent tous les sélecteurs de `ploopi2` ; elles en
  changent l'aspect (couleurs, bordures, rayons, ombres), jamais la structure :
  flottants, largeurs, positions et marges horizontales des cellules restent ceux dont
  dépendent les tableaux de `display_array()` (colonnes à largeur fixe, séparateurs en
  position absolue).
- **Taille de texte : 12 px, comme `ploopi2`.** Les modules fixent la largeur de
  nombreuses colonnes en pixels ; à 13 px, les dates passaient sur deux lignes. Seules
  la navigation et les titres sont à 13 px et plus (`--p3-font-size-nav`).
- **Identifiants et blocs du gabarit** : `#pagecontent.hook` (conteneur défilant où
  `ploopi.popup` accroche les fenêtres, placé sous `#dock` d'après sa hauteur),
  `#tpl_ploopi_tickets_new`, `#login_form`, `#ploopi_login`, `#ploopi_password`,
  `#ploopi_password_new`, `#ploopi_password_new_confirm`, `#checkpass`,
  `#lostpassword_form`, et tous les blocs et variables qu'alimente
  `include/backoffice.php`.
- **Images** : même arborescence `img/` (les modules les appellent par
  `$_SESSION['ploopi']['template_path'].'/img/…'`). Le jeu d'icônes (§ 9) remplace les
  images sous les mêmes noms et aux mêmes tailles.
- **`skin.ini`** : mêmes clés (`bgline1`, `bgline2`… lues par la messagerie, les
  annotations et `forms`).
- **`light.tpl` et `ticket.tpl`** : même structure.
- **Largeur minimale** du backoffice connecté : 1180 px, comme `ploopi2` ; les écrans
  des modules ne sont pas prévus pour moins.

## 3. Ce qui change

- **`index.tpl`** : barre de l'utilisateur (espace, recherche, messages, profil,
  déconnexion), barre des modules, puis menu du module courant, tiré du bloc
  `switch_blocksel` que `include/backoffice.php` alimentait déjà et que `ploopi2`
  n'affichait pas. Le menu déroulant du module courant est alors masqué : il ferait
  doublon et recouvrirait la page juste après le clic.
- **Plus aucun attribut `onclick` / `onchange` dans le gabarit** : les gestionnaires
  sont posés en jQuery, ce qui va dans le sens du passage à une CSP effective (lot 11 de
  la feuille de route). `tpl_open_profile()` reste défini.
- **Icônes de la barre supérieure** en SVG dans la page, à la couleur du texte.
- **Feuilles de style** construites sur des variables CSS (`:root` de
  `css/base_main.css`) : toute la palette tient en une trentaine de lignes.
- **Page de connexion** refaite, qui s'adapte aux petits écrans.

## 4. Activer, généraliser, revenir en arrière

**Installation neuve** : `ploopi4` d'emblée depuis le 10 octobre 2026 (`ploopi3`
auparavant). Les deux espaces créés par l'installation (`install/system/ploopi.sql`) le
portent, et `config/config.php.model` le désigne comme gabarit par défaut
(`_PLOOPI_DEFAULT_TEMPLATE`, aussi utilisé en repli par `include/backoffice.php`). Une
installation existante n'est pas modifiée : la basculer par l'un des deux réglages
ci-dessous (en y nommant `ploopi3` ou `ploopi4`).

**Pour un espace** : *Administration → Espaces de travail →* l'espace *→ onglet Espace →
Accès → Habillage : `ploopi3` → Enregistrer*. Le gabarit est relu à chaque requête :
l'effet est immédiat, sans reconnexion. La page de connexion prend l'habillage de
l'espace qui répond au domaine.

**Pour toute l'instance** : dans `config/config.php`,
`define('_PLOOPI_DEFAULT_TEMPLATE', 'ploopi3');`. Ce réglage vaut pour les espaces dont
l'habillage n'est pas renseigné.

**Retour arrière** : remettre `ploopi2` au même endroit.

## 5. Contraste

Les variables visent des rapports nets, au-delà du minimum AA : texte courant 16:1,
libellés de formulaire 10,5:1 (`--p3-text-label`), texte secondaire 7,8:1
(`--p3-text-muted`), indication de saisie 4,5:1 ; le bord des champs atteint 3:1
(`--p3-border-field`, seuil des composants d'interface), les cadres et séparateurs
(`--p3-border`, `--p3-line`) restent discrets mais visibles, et le fond de l'application
(`--p3-bg`) est assez soutenu pour que les cartes s'en détachent. Les en-têtes de
tableaux sont écrits en couleur de texte, plus en gris.

## 6. Écrans d'administration

`css/base_system.css` met en forme les écrans du module `system`. Quelques styles en
ligne de `modules/system` y sont devenus des classes, dont
`modules/system/include/styles.css` donne l'aspect historique (`ploopi2` est inchangé,
vérifié au pixel près) et que `ploopi3` redessine :

| Classe | Où | Sous `ploopi3` |
|---|---|---|
| `system_bandeau` (`_titre`, `_cadre`, `_clair`) | en-têtes de listes, d'imports, d'outils ; recherche d'utilisateurs d'un rôle | encadré clair à liseré |
| `system_line_selected`, `system_line_system` | rôle choisi ; module `system` dans l'installation | ligne sélectionnée sarcelle ; ligne atténuée |
| `system_user_level` | niveau de l'utilisateur dans l'espace (fiche) | réglage mis en avant |
| `system_warning` | adresse de l'administrateur absente de la configuration | encadré d'avertissement |
| `system_tree_node`, `system_tree_link` (`selected`) | arborescence des espaces et groupes | élément courant en pastille |

S'y ajoutent, dans la feuille du gabarit seulement : balises du modèle de courriel en
étiquettes, panneau d'actions d'un espace ou d'un groupe, titres de section à liseré,
champ de fichier stylé, fenêtres d'envoi de fichier aérées, boîtes de la messagerie en
commande segmentée, et actions secondaires (« Annuler », « Réinitialiser »,
« Retour ») en boutons clairs, repérées par leur libellé. La jauge de l'outil « Charge du
serveur » passe du vert et rouge saturés à des teintes claires (les deux gabarits).

## 7. Écrans des modules

Même méthode que pour le module `system`, appliquée aux modules :

- **Styles en ligne → classes.** Les bandeaux gris, barres de boutons, cadres et filets
  écrits en dur dans les écrans d'administration de neuf modules sont devenus des
  classes `<module>_*` (`forms_barre_boutons`, `webedit_liste_titre`, `doc_separateur`…),
  déclarées dans `install/<module>/files/include/styles.css` avec exactement les
  mêmes déclarations : `ploopi2` est inchangé (vérifié au pixel près sur 49 écrans).
  Les fichiers servis au **frontoffice** (`wce*.php`, `template*.php`, `display*.php`,
  et `op_event.php` de `booking` et `planning`, appelé par l'affichage public) gardent
  leurs styles en ligne : la feuille du module n'y est pas chargée.
- **Palette déclinée automatiquement.** `tools/ploopi3_modules.php` lit les feuilles
  des modules et écrit `css/base_modules.css` : les mêmes sélecteurs (seuls ceux
  propres au module, pour ne rien appliquer aux autres pages), avec leurs **seules
  couleurs** réécrites dans les variables `--p3-*`. Aucune dimension, marge ou
  bordure n'est reprise : la mise en page des modules ne peut pas bouger. Après
  toute modification d'une feuille de module :

  ```sh
  php tools/ploopi3_modules.php     # régénère css/base_modules.css (--check : vérifie)
  tools/compress.sh                 # agrégat styles.pack.css
  ```

- **Retouches à la main** dans `css/base_modules_retouches.css`, sous la même règle
  (liserés en ombre intérieure, sans géométrie nouvelle) : liseré gris des bandeaux
  d'information, titres de section en sarcelle, barres de boutons sans aplat, fil
  d'Ariane de la GED, brouillons en teinte d'avertissement.
- Boutons « Supprimer » en style « danger », repérés par leur libellé comme les
  actions secondaires.

Les modules concernés ont changé de version (*Mettre à jour* recopie leurs fichiers).

## 8. Décliner la palette

Les couleurs, rayons et ombres sont des variables `--p3-*` en tête de
`templates/backoffice/ploopi3/css/base_main.css`. Pour une autre couleur
d'établissement, changer `--p3-primary` et ses déclinaisons (`-hover`, `-dark`, `-soft`,
`-softer`) en gardant un contraste d'au moins 4,5:1 avec le blanc, puis :

```sh
tools/compress.sh      # régénère css/styles.pack.css et son .gz
```

Pour un habillage distinct plutôt qu'une simple variante, copier le dossier
`ploopi3` sous un autre nom (`compress.sh` traite tous les gabarits) et renommer la
classe dans `class_skin.php`.

## 9. Icônes

`ploopi2` accumulait au moins quatre générations d'icônes (glyphes plats blancs, PNG en
dégradé, GIF, quelques SVG), et chaque module apportait les siennes : sur un même écran
d'outils, de messagerie ou de la GED, aucune ne ressemblait à sa voisine. Un seul jeu les
remplace : [Tabler Icons](https://tabler.io/icons) (MIT), au trait.

- **Même nom, même taille** : chaque image est rastérisée à la taille exacte de celle
  qu'elle remplace ; le code qui l'appelle et la mise en page ne changent pas.
- **Une palette réduite, porteuse de sens** : sarcelle pour la navigation et les objets,
  gris pour les actions courantes, rouge pour supprimer et interdire, vert pour valider
  ou publier, gris clair pour une action indisponible. Là où l'ancienne icône codait un
  état par sa seule couleur, le glyphe le dit aussi : niveaux d'administration
  (utilisateur, étoile, engrenage, bouclier), dossiers de la GED (privé en sarcelle,
  partagé en bleu, public en ambre, cadenas s'il est protégé), articles du site
  (publié, modifié depuis, jamais publié), favoris de l'annuaire (étoile vide ou
  pleine), vue courante des plannings (en couleur, les autres en gris).

### Administration (gabarit `ploopi3`)

Les 151 images de `templates/backoffice/ploopi3/img/` qu'appellent le socle et les
modules : barres d'outils, outils du système, niveaux d'administration, actions des
listes, messagerie, abonnements, exports, arborescences, sélecteur de date, partages,
validations, champ obligatoire des formulaires générés, et GED du socle (boutons,
dossiers, types de fichiers). Les boutons de la GED passent leur glyphe en blanc par
`filter: brightness(0) invert(1)`, quelle que soit sa couleur : les mêmes SVG servent,
colorés, dans les listes. `ploopi2` garde ses images.

Trois images de la racine passaient outre le gabarit : exports des listes
d'utilisateurs (`img/export/`), icône de courriel (`email.gif`) et bouton du sélecteur
de date (`calendar.gif`). Le module `system`, `date::open_calendar()`, la recherche de
`doc` et l'édition des articles et blocs de `webedit` les demandent désormais au
gabarit ; `ploopi2` en a reçu des copies au pixel près (vérifié), il ne change donc pas
d'aspect.

### Modules et types de fichiers, à la source

Les 244 images des dix modules (`install/<module>/files/img/`) et les 34 icônes et
vignettes de types de fichiers du socle (`img/mimetypes/`, GED et
`ploopi\mimethumb`). Elles ne dépendent pas du gabarit : `ploopi2` et les objets que
le frontoffice affiche (annuaire, réservations, wiki…) prennent donc aussi le nouveau
jeu.

Chaque module a changé de version (`doc` 4.10.17, `webedit` 2.8.11…) : *Système →
Installation des modules → Mettre à jour* recopie ses images, sans script SQL. Tant
qu'un module n'est pas mis à jour, il garde ses anciennes icônes ; celles de
`img/mimetypes/` changent dès le déploiement du code.

Restent tels quels, volontairement : les illustrations et images de contenu
(`webedit/img/default_img.png`, `nanogallery/img/icone-video-2.png`,
`news2/img/desk/64/icon.png`), les images de fond (`booking/img/menu.png`,
`doc/img/sep.png`), les traits de l'arborescence de `nanogallery`, les GIF que plus rien
n'appelle (sept dans `forms`, un dans `doc`), et `img/calendar/calendar.gif`, encore
utilisé par le frontoffice (gabarits, historique de `booking`).

### Régénérer

Les correspondances (fichier → glyphe, couleur) sont dans
`tools/icones/correspondances.js` (administration) et `tools/icones/modules.js`
(modules, types de fichiers), les glyphes utilisés dans `tools/icones/tabler/`. Pour
changer une icône ou une couleur :

```sh
npm install playwright && npx playwright install chromium    # une fois
node tools/icones/generer.js --planche /tmp/planche.png        # tout, et la planche de contrôle
node tools/icones/generer.js --seul install/doc                # un seul jeu
```

Le rendu est reproductible : une régénération sans changement de correspondance rend
les mêmes fichiers, à l'octet près. Les trois images qui ne sont pas en PNG
(`directory/img/nopic.gif`, `forms/img/link.gif`, `nanogallery/img/Folder-Icon.jpg`)
passent par ImageMagick (`convert`). Après un changement dans un module, monter sa
version (`description.xml`, `changelog.txt`) pour que la mise à jour recopie ses images.

Planches : [`screenshots/ploopi3/icones_planche.png`](../screenshots/ploopi3/icones_planche.png)
(administration) et
[`screenshots/ploopi3/icones_modules_planche.png`](../screenshots/ploopi3/icones_modules_planche.png)
(modules, types de fichiers).

## 10. Vérifications faites

Sur une instance de démonstration (les dix modules installés par *Système →
Installation* et instanciés dans un espace, données fictives), parcourue par Playwright
sous `ploopi2` puis sous `ploopi3` :

- **21 écrans** (connexion, administration de l'espace et du système, chaque module,
  messagerie, profil) : aucune erreur JavaScript ; la console ne relève que ce qu'elle
  relevait déjà sous `ploopi2` (avertissement de CKEditor 4.22, et `jscolor` de
  `webedit` introuvable, chemin à la mauvaise casse, corrigé depuis) ;
- **14 interactions**, mêmes résultats sous les deux gabarits : erreur de connexion,
  mot de passe perdu (aller et retour), connexion, tri d'un tableau, sélecteur de date,
  recherche intégrale, fenêtre de nouveau message (ouverture, fermeture), changement
  d'espace, fenêtre de profil (ouverture, fermeture), déconnexion. Une seule différence :
  le menu d'un module s'ouvre au clavier sous `ploopi3`, pas sous `ploopi2` ;
- **mot de passe expiré** : formulaire de renouvellement, jauge de robustesse, contrôle
  de concordance ;
- **connexion sur téléphone** (390 px) : pas de défilement horizontal, rien ne se
  chevauche ;
- **icônes des modules** : les dix modules installés dans leur version précédente puis
  mis à jour par *Système → Installation des modules* (fichiers déployés identiques au
  paquet), 43 écrans parcourus avant et après, sous `ploopi2` puis sous `ploopi3` :
  105 images de modules chargées, aucune manquante ni illisible, aucune erreur
  JavaScript ; les vignettes de `img/mimetypes/` passent par `image::resize()` ;
- **barres de défilement parasites** : navigateur lancé avec ses barres de défilement
  visibles, et recherche, sur 97 écrans d'administration et de modules plus les
  sous-écrans de `forms`, des éléments défilants qui dépassent de quelques pixels :
  aucun depuis que les lignes de formulaire sont en `display: flow-root` ;
- **suite de tests** (`tests/instance.sh test`) : verte ; la suite 12 compile les
  gabarits de `ploopi3`, la suite 33 contrôle son `.gz`, la suite 13 n'y trouve aucun
  reste de Prototype.

## 11. Limites connues

- **Couleurs portées par les données** (couleur d'une ressource, d'un évènement) et
  styles en ligne des écrans du frontoffice : inchangés (§ 7).
- Deux conforts reposent sur `:has()` (navigateurs de 2023 et après) : le masquage du
  menu déroulant du module courant et celui des menus vides. Sans `:has()`, le
  comportement est celui de `ploopi2`.
- Pas de mode sombre : les couleurs écrites en dur dans les modules le rendraient
  incohérent.
- Le **frontoffice** (`templates/frontoffice/`, captures `A0` à `A3`) garde son habillage ;
  seules les icônes des modules qu'il affiche suivent le nouveau jeu (§ 9).
