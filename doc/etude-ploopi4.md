# Étude de faisabilité : gabarit `ploopi4` d'après la maquette ARGOS

Objet : dire si le style de la maquette
[`argos/maquette/utilisateurs.html`](https://github.com/faf2019/argos/blob/recette/maquette/utilisateurs.html)
peut devenir l'habillage du backoffice de Ploopi, **sans toucher au code des modules**
(seul `system` peut être refondu), à partir d'un nouveau gabarit `ploopi4`.

Statut : **deux jalons réalisés** — shell `ploopi4` et écran « Espaces de travail »
(lots A, C, D), puis les autres écrans de `system` (lot E) : voir
[`ploopi4.md`](ploopi4.md). Restent
le lot F (compléments), la non-régression ARGOS et le passage de `ploopi4` en gabarit
par défaut. Le reste de l'étude décrit ces jalons. Les références `fichier:ligne` renvoient à
`ploopidev` (branche de l'étude) sauf mention `argos/…`.

---

## 1. Synthèse

**C'est faisable**, dans les limites suivantes :

| Couche | Résultat atteignable | Condition |
|---|---|---|
| **Shell** (barre supérieure, menu latéral, sélecteur d'espace, recherche, notifications, menu utilisateur, fil d'Ariane) | **Identique à la maquette**, responsive (bureau, rail tablette, tiroir mobile), thèmes clair/sombre, densité | Ajouts limités au socle (`include/backoffice.php`, `ploopi\block`) |
| **Composants du socle** (`create_pagetitle`, `open_simplebloc`, `create_tabs`, `create_toolbar`, popups, `display_array`, `ploopi\form*`) | **Aspect de la maquette** (cartes, onglets, boutons, tableaux, champs) | Balisage inchangé : CSS seulement (voir § 4) |
| **Module `system`** | **Écrans de la maquette** (arborescence filtrable, liste avec recherche, filtres, sélection, actions groupées, fiche en tiroir) | Refonte des écrans, dont l'essentiel des fonctions existe déjà (§ 6) |
| **Écrans des autres modules** (dont `auzou` d'ARGOS) | Shell neuf, composants restylés, **mais ni responsive ni thème sombre** | Largeurs fixes et styles en ligne : on les héberge dans une « zone historique » claire et défilante (§ 5) |

Ce qui **ne peut pas** venir sans toucher aux modules : la mise en page mobile de leurs
écrans, le thème sombre sur leur contenu, et les écrans métier redessinés de la maquette
(fiche article, dossier transport) — ce sont des refontes du module `auzou`, pas du gabarit.

Ordre de grandeur : **60 à 90 jours** pour l'ensemble (§ 9), dont un **premier jalon
démontrable en 12 à 15 jours** : shell `ploopi4` + écran « Espaces de travail » refondu,
activés sur un seul espace.

---

## 2. Ce que contient la maquette

`argos/maquette/assets/argos.css` (1 378 lignes) est déjà organisé comme un gabarit :

1. **Jetons** (`:root`) : palette de marque, surfaces, texte, états, ombres, rayons,
   dimensions du shell ; **thème sombre** (`prefers-color-scheme` + `data-theme`) ;
   **densité compacte** (`data-density="compact"` : 13 px, contrôles de 32 px).
2. **Base** (typographie Inter, focus visible, `prefers-reduced-motion`).
3. **Composants génériques** : boutons, champs, interrupteurs, contrôle segmenté, puces,
   badges, cartes, menus déroulants, toasts, tiroir, tableaux responsives (lignes en cartes),
   callouts, zone de dépôt.
4. **Shell** : `topbar`, `sidebar` (rail réduit, tiroir < 900 px), `workspace-switch`, `nav`.
5. à 7. Écrans ARGOS (article, connexion, logistique) — hors périmètre du gabarit.
8. **Administration Ploopi** : `split-pane`, `tree`, `entity-head`, `alpha`,
   `filters-panel`, `users-table`, `bulkbar`.

JS sans dépendance : `argos.js` (thème, densité, menus `[data-menu]`, toasts, copie),
`shell.js` (menu latéral, recherche globale, raccourcis `Ctrl+K`, `/`, `Échap`),
`utilisateurs.js` (données fictives, arborescence, filtres, tri, sélection, tiroir),
`icons.js` (sprite Lucide injecté). Tout passe par des attributs `data-*` et de la
délégation d'évènements : **aucun gestionnaire en ligne**, ce qui va dans le sens de la
CSP (feuille de route, lot 11).

---

## 3. Points d'appui dans Ploopi

Quatre mécanismes existants rendent l'opération possible sans toucher aux modules :

1. **Gabarit choisi par espace** (`include/classes/loader.php:1482-1487`, colonne
   `ploopi_workspace.template`). `ploopi4` s'active espace par espace, à côté de
   `ploopi2`/`ploopi3`, avec retour arrière immédiat — exactement comme `ploopi3`
   (`doc/habillage.md` § 4).
2. **Le gabarit fournit sa propre classe de rendu** : `ploopi\skin::get()` inclut
   `<gabarit>/class_skin.php`, dont la classe `skin` **hérite** de `ploopi\skin`
   (`include/classes/skin.php:68-83`). `ploopi4` peut donc surcharger
   `create_pagetitle()`, `open_simplebloc()`, `create_tabs()`, `create_toolbar()`,
   `create_popup()`… sans effet sur les autres gabarits ni sur l'appel des modules.
   Le rafraîchissement AJAX des tableaux passe lui aussi par `skin::get()`
   (`include/op.php:586`).
3. **Le shell est déjà alimenté** par `include/backoffice.php` : espaces accessibles
   (l. 111-119), modules et menus de chaque module (l. 129-200, bloc `switch_blocksel`
   pour le module courant), utilisateur, messagerie, recherche, déconnexion
   (l. 229-271). La correspondance avec la maquette est au § 5.1.
4. **`ploopi3` a déjà fait la moitié du chemin** : variables CSS, aucun `onclick` dans
   le gabarit, icônes unifiées sous les mêmes noms et tailles, styles en ligne de
   `modules/system` et de neuf modules convertis en classes (`doc/habillage.md` § 6-7).
   `ploopi4` repart de ses feuilles pour la zone historique.

---

## 4. Le contrat à ne pas rompre

C'est la contrainte qui dimensionne tout le reste. Constaté dans le code :

| Contrat | Qui en dépend | Conséquence pour `ploopi4` |
|---|---|---|
| `#pagecontent.hook` est le **conteneur défilant** où s'accrochent les popups ; leur position est calculée avec son `scrollTop()` et son `offset()` | `js/popup.js:27-28, 71-95` ; `argos/modules/auzou/include/functions.js:1393, 1557` | Garder un `.hook` qui défile **à l'intérieur** de la zone principale (pas le défilement de la fenêtre comme la maquette) |
| Élément `#background` présent | `argos/modules/auzou/actions/edito/modify_tab_cepv2.php:1318` (y déplace un élément) | Le conserver comme enveloppe de l'application |
| **Structure DOM de `display_array()`** : ids `ploopi_explorer_main_*`, `ploopi_explorer_values_outer_*`, `ploopi_explorer_values_inner_*`, classes `ploopi_explorer_line_1/2`, `_link`, `_multipage` | `argos/modules/auzou/include/functions.js:795-1129` (hauteurs, parcours de lignes) | **Ne pas réécrire le balisage** de `display_array` : restyler en CSS seulement |
| Classes `simplebloc`, `pagetitle`, `ploopi_popup` | feuille d'`auzou` (`include/styles.css:1041-1060, 1322, 1384`) | Garder les classes ; une surcharge de `open_simplebloc()` doit les conserver |
| Images `img/…` sous les mêmes noms et tailles ; clés de `skin.ini` | socle et modules (`template_path.'/img/…'`) | Reprendre `ploopi3/img/` et `skin.ini` tels quels |
| **Largeurs fixes en pixels, police de 12 px** | 144 largeurs de colonnes `'width' => N` dans les modules de Ploopi, **1 939** dans `auzou` ; `ploopi3` impose 1 180 px de large, le gabarit ARGOS **1 600 px** (`argos/templates/backoffice/auzou/index.tpl:291`) | Zone historique en 12 px, largeur minimale, défilement horizontal interne |
| **Styles en ligne et couleurs en dur** | ~920 `style="` et ~120 couleurs hexadécimales dans les modules de Ploopi ; **1 229** `style="` dans `auzou` | Pas de thème sombre sur le contenu des modules |
| jQuery et jQuery UI (popups déplaçables, sélecteur de date, tri) | tous les modules | Les garder ; le JS du shell reste indépendant |
| Moteur de gabarit : pas d'inclusion, `{mot}` pris pour une variable, reste de ligne ignoré après `BEGIN`/`END` (`index.tpl` de `ploopi3`, l. 24-34) | `lib/template/template.php` | JS du shell en fichiers externes, jamais dans `index.tpl` |

---

## 5. Analyse par couche

### 5.1 Shell — faisable, au niveau de la maquette

| Élément de la maquette | Source dans Ploopi | Travail |
|---|---|---|
| Logo + nom | `WORKSPACE_TITLE`, `TEMPLATE_PATH` | gabarit |
| Sélecteur d'espace (menu) | bloc `switch_user_logged_in.workspace` (`TITLE`, `URL`, `SELECTED`) | gabarit ; sous-titre et pastille de couleur par espace : **socle** (nouvelles variables, depuis `ploopi_workspace`) |
| Menu latéral : modules, sections, entrée active | blocs `switch_blockmenu.block` (modules) et `switch_blocksel.menu` (menu du module courant), classe CSS `.nav .sub` déjà prévue par la maquette | gabarit |
| Icônes du menu | aucune aujourd'hui (`ploopi\block::addmenu($label, $url, $selected, $target)`, `include/classes/block.php:73`) | **socle** : paramètres optionnels `icon`, `section`, `count` (rétrocompatible) ; pour les modules non modifiés, icône déduite du type de module par une table du gabarit |
| Recherche globale | formulaire existant (`MAINMENU_SHOWSEARCH_URL`, `SEARCH_KEYWORDS`) | gabarit ; résultats instantanés (`Ctrl+K`) : opération JSON de `system` sur `search_index` (optionnel, § 8) |
| Cloche de notifications | messagerie : `NEWTICKETS`, `#tpl_ploopi_tickets_new`, `ploopi.tickets.refresh` | gabarit ; les « alertes métier » de la maquette (ISBN épuisés…) n'existent pas dans le socle : point d'extension à créer si souhaité |
| Menu utilisateur (profil, thème, densité, raccourcis, déconnexion) | `USER_FIRSTNAME/LASTNAME`, `POPUP_PROFLE`, `USER_DECONNECT` | gabarit ; initiales et libellé du niveau (« Administrateur ») : **socle** (2 variables) |
| Fil d'Ariane, titre de page | `WORKSPACE_LABEL` ; titre émis par `create_pagetitle()` | **socle** : libellés du module et de l'entrée de menu courants (2 variables) ; titre via surcharge de `create_pagetitle()` |
| Thème, densité, menu replié | `localStorage` dans la maquette | **`system`** : préférences utilisateur en base (voir § 7), rendues côté serveur sur `<html data-theme data-density>` — évite le script en ligne du `<head>` |
| Page de connexion (`login.html`) | bloc `switch_user_logged_out`, ids imposés (`#login_form`, `#ploopi_login`…) | gabarit ; afficher/masquer, alerte majuscules : JS du gabarit ; « rester connecté » : **socle** (§ 8) |

Le shell est donc **un travail de gabarit**, plus une dizaine de variables et trois
paramètres optionnels côté socle, tous additifs (`ploopi2` et `ploopi3` les ignorent).

### 5.2 Composants du socle — aspect de la maquette, balisage conservé

| Méthode / classe | Rendu `ploopi4` | Moyen |
|---|---|---|
| `create_pagetitle()` | `page-title` (titre, sous-titre, actions à droite) | surcharge dans `class_skin.php` en **gardant** la classe `pagetitle` |
| `open_simplebloc()` / `close_simplebloc()` | carte (`card`, en-tête clair) | CSS ; surcharge possible en gardant `simplebloc*` |
| `create_tabs()` | onglets soulignés (`tabs`) | CSS (balisage déjà simple : `<div class="tabs"><a>`) |
| `create_toolbar()` | barre d'outils : boutons « icône + libellé » ou onglets d'entité | CSS, ou surcharge (ids et `a.skin_icon[data-url]` conservés, lus par `js/utilities.js`) |
| `create_popup()` / `ploopi.popup` | fenêtre modale à en-tête, bouton fermer, largeur bornée à l'écran | CSS + `js/popup.js` : borner la largeur demandée à celle de la fenêtre (socle, sans effet sur bureau) |
| `display_array()` | tableau de la maquette (en-têtes, zébrage, survol, tri, pagination) | **CSS seulement** (contrat § 4) ; pas de « lignes en cartes » sur mobile pour ces tableaux |
| `ploopi\form*` | libellés au-dessus, grille, sections | CSS sur `.ploopi_generate_form` (`<div><label>…</label><champ></div>` s'y prête) ; à valider écran par écran, la hauteur des formulaires augmente |
| Bandeaux de service (annotations, abonnements, partages, validations, documents, messagerie, calendrier) | cartes et badges | reprise des feuilles `base_*.css` de `ploopi3` sur les nouveaux jetons |

### 5.3 Écrans des modules — la « zone historique »

Le contenu produit par un module autre que `system` est rendu dans `#pagecontent.hook`,
qui devient une **zone historique** :

- **toujours claire** (`color-scheme: light` et jetons clairs forcés sur la zone) : en
  thème sombre, le shell est sombre et le contenu reste une « feuille » claire. Sans
  cela, les quelque 2 200 styles en ligne et couleurs en dur (Ploopi + `auzou`) donneraient du
  texte sombre sur fond sombre ;
- **police de 12 px** (contrainte de `ploopi3`), indépendante de la densité ;
- **largeur minimale** et défilement horizontal interne : 1 180 px pour les modules de
  Ploopi, 1 600 px pour `auzou`. Le menu latéral passe en rail (72 px) sur les écrans
  étroits pour regagner 184 px : à 1 366 px de large, on dispose de 1 294 px, assez pour
  les modules de Ploopi, pas pour `auzou` (défilement horizontal, comme aujourd'hui).

Sur téléphone, le shell est utilisable (tiroir, recherche, messagerie, profil) mais les
écrans des modules restent des écrans de bureau à faire défiler. Le gabarit peut
signaler ces pages (`<body class="p4-historique">`, d'après le type du module courant)
pour adapter la `viewport`.

Ce qu'un module gagne **sans changement de code** : le shell, les composants du § 5.2,
les icônes, la typographie. Ce qu'il ne gagne qu'en étant modifié : le responsive, le
thème sombre de son contenu, et toute nouvelle ergonomie. Les modules à contrôleur
(`news2`, `nanogallery`) pourront adopter les nouveaux composants plus tard, au fil de
l'eau, par un indicateur (`responsive`, `dark`) déclaré par le module.

### 5.4 Module `system` — refonte des écrans

La maquette `utilisateurs.html` reprend des fonctions **qui existent déjà** ; la refonte
est donc surtout une réécriture de l'interface :

| Maquette | Existant | Écart |
|---|---|---|
| Arborescence espaces / groupes | `system_build_tree()` (`modules/system/include/functions.php:227`), enfants chargés à la demande (`admin.php`, `op=xml_detail_group`), `onclick` en ligne (l. 285, 357, 434) | arbre complet en une passe, filtre côté client, compteur d'utilisateurs par groupe (une requête `GROUP BY`) |
| En-tête d'entité + onglets | barres d'outils `tabWorkspaces`, `tabModules`, `tabParams`, `tabDirectory`, `tabUsers`, `tabRoles` (`admin_index.php:188-239`) ; `tabGroups`, `tabUsers` pour un groupe (l. 58-74) | rendu |
| Recherche, filtres (nom, prénom, identifiant, courriel, connexion entre deux dates) | **identiques** dans `admin_index_users_list.php` (filtres l. 46-51) | rendu, filtres repliables |
| Initiale du nom | onglets alphabétiques (`admin_index_users_list.php:112`) | rendu (`alpha`), lettres sans résultat grisées |
| Export XLSX, XLS, CSV, XML, vCard | `system_directory_export` : vcf, xml, csv, pdf, ods, xls, xlsx (l. 137-149) | menu déroulant |
| Ajouter : créer, rattacher un existant, importer CSV | onglets `tabUserAdd`, `tabUserAttach`, `tabUserImport` (`admin_index_users.php:231-282`) | menu déroulant |
| Tri, pagination | `display_array` (l. 355, 50 lignes) | tableau propre à `system` (§ 6) |
| Sélection multiple, actions groupées (détacher, exporter la sélection) | — | **nouveau** : opérations `system` avec jeton et contrôle d'accès |
| Fiche utilisateur en tiroir | formulaire pleine page (`admin_index_users_form.php`, 775 lignes) | **nouveau** : fiche de consultation chargée en `admin-light` ; la modification garde le formulaire existant, restylé |
| Menu latéral par section (Organisation / Système) | 2 entrées seulement (`modules/system/block.php`) ; les écrans du niveau « système » sont derrière une barre d'outils (`admin_system.php`) | `block.php` de `system` : une entrée par écran (Espaces, Annuaire, Rôles, Modules, Paramètres, Utilisateurs connectés, Journaux, Mise à jour) |

Le reste du module (≈ 15 000 lignes : espaces, modules, paramètres, rôles, installation et
mise à jour des modules, annuaire, outils, profil, messagerie, annotations, recherche)
suit le même modèle, écran par écran.

---

## 6. Architecture proposée

```
templates/backoffice/ploopi4/
├── index.tpl, light.tpl, ticket.tpl     shell connecté / connexion ; popups ; messagerie
├── class_skin.php                       class skin extends ploopi\skin : surcharges du § 5.2
│                                        + nouveaux composants (page_header, card, menu, drawer…)
├── skin.ini                             mêmes clés que ploopi3
├── css/
│   ├── 10_tokens.css                    jetons de la maquette (clair, sombre, densité)
│   ├── 20_base.css, 30_composants.css   reprise d'argos.css § 2-3
│   ├── 40_shell.css                     argos.css § 4
│   ├── 50_historique.css                feuilles de ploopi3 remappées sur les jetons,
│   │                                    portée limitée à #pagecontent (zone claire, 12 px)
│   ├── 60_system.css                    écrans refondus du module system (argos.css § 8)
│   └── styles.pack.css(.gz)             agrégat produit par tools/compress.sh
├── js/shell.js                          argos.js + shell.js, sous ploopi.shell.* (pas window.argos)
├── img/                                 copie de ploopi3/img (contrat) + icons.svg (sprite)
└── fonts/                               Inter auto-hébergée (pas de Google Fonts : CSP, RGPD)
```

Choix techniques :

- **Sprite SVG en fichier** (`<use href="{TEMPLATE_PATH}/img/icons.svg#i-menu">`) plutôt
  qu'injecté par JS ; **une seule famille d'icônes** : Tabler (déjà utilisée par `ploopi3`
  et les modules, `tools/icones/`) plutôt que Lucide, pour ne pas mêler deux dessins.
- **Composants nouveaux dans `ploopi\skin`** (socle) avec un rendu de repli simple, et
  rendu riche dans `ploopi4/class_skin.php` : `system` les appelle sans savoir quel
  gabarit est actif.
- **Pas de framework JS** : le JS de la maquette est vanilla et tient en ~170 lignes pour
  le shell ; jQuery reste chargé pour les modules.
- **Thème sombre seulement sur le shell et les écrans de `system`** (et, plus tard, des
  modules qui se déclarent compatibles).
- **Marque ARGOS** : `ploopi4` est neutre (palette Ploopi) ; ARGOS a son dérivé
  (`templates/backoffice/argos4/` : jetons de marque, logo, illustration de connexion),
  comme `auzou` dérive aujourd'hui de `ploopi2`.

### `system` sous `ploopi2` / `ploopi3`

Les écrans refondus utilisent des composants que seuls `ploopi4` habille. Deux options :

- **(recommandé) bascule écran par écran** : `admin.php` de `system` sert l'écran refondu
  si le gabarit le déclare (`skin.ini : composants = 4`), l'écran actuel sinon. Migration
  progressive, retour arrière par espace, anciens écrans retirés une fois la couverture
  complète ;
- `ploopi4` obligatoire pour l'administration : plus simple, mais rupture pour les
  installations restées en `ploopi2`/`ploopi3`.

---

## 7. Modifications du socle (additives)

La demande autorise une refonte de `system` ; le socle (`include/`, `js/`) n'est pas un
module mais il faut y toucher un peu. Tout est rétrocompatible :

| Fichier | Modification |
|---|---|
| `include/backoffice.php` | variables : initiales et niveau de l'utilisateur, libellés du module et du menu courants (fil d'Ariane), type du module courant, sous-titre / couleur des espaces, préférences (thème, densité, menu replié) ; icône et section des entrées de menu |
| `include/classes/block.php` | `addmenu()` : paramètres optionnels `icon`, `section`, `count` |
| `include/classes/skin.php` | nouveaux composants avec rendu de repli (en-tête de page à actions, carte, menu déroulant, tiroir, état vide…) |
| `js/popup.js` | borner la largeur des popups à la fenêtre (téléphone, tablette) |
| préférences utilisateur | paramètres utilisateur du module `system` (`ploopi_param_user`, déjà en cascade) : `system_theme`, `system_density` ; enregistrées par une opération `system` |

---

## 8. Risques et parades

| Risque | Gravité | Parade |
|---|---|---|
| Un module dépend d'un détail du balisage ou d'une position (cas avéré d'`auzou` : `.hook`, `#background`, DOM de `display_array`, hauteur de fenêtre) | élevée | Contrat du § 4 figé dans `doc/habillage.md` ; **parcours de non-régression d'ARGOS** (écrans `auzou` à popups, listes, fiche article) en plus du parcours de 21 écrans et 14 interactions fait pour `ploopi3` (`doc/habillage.md` § 10) |
| Popups mal placées si le défilement change | élevée | `#pagecontent.hook` reste le conteneur défilant ; tests d'ouverture de popup après défilement |
| Largeur utile réduite par le menu latéral | moyenne | rail par défaut sous 1 440 px sur les pages historiques ; mémorisation du choix |
| Thème sombre incohérent | moyenne | zone historique claire forcée (§ 5.3) |
| Formulaires « libellé au-dessus » trop hauts sur certains écrans | faible | activable par une classe ; repli sur le libellé à gauche pour la zone historique si besoin |
| « Rester connecté » | sécurité | jeton persistant à usage unique, haché en base, rotation à chaque usage, révocable depuis le profil et l'administration, exclu pour les administrateurs système si souhaité — lot à part, revue de sécurité |
| Recherche instantanée (`Ctrl+K`) qui fuit des objets non autorisés | sécurité | réutiliser le filtrage de la recherche actuelle (`public_search*`) ; opération soumise au jeton |
| CSP | faible | aucun script ni gestionnaire en ligne dans `ploopi4` ; préférences rendues côté serveur |
| Licences | faible | Inter (OFL 1.1) et Tabler (MIT) : compatibles, à ajouter à `doc/licence.md` |
| Version du socle d'ARGOS | à vérifier | `ploopi4` est construit sur `ploopidev` ; le gabarit ARGOS actuel est en `iso-8859-15` et utilise encore `Effect.Appear` (Prototype) : ARGOS doit être sur le socle courant pour en profiter |

---

## 9. Découpage et ordre de grandeur

Estimations en jours de développement, recette comprise, hors conception graphique (la
maquette en tient lieu) :

| Lot | Contenu | Jours |
|---|---|---|
| A. Gabarit `ploopi4` | jetons, base, composants, shell, connexion, `light.tpl`, `ticket.tpl`, `shell.js`, sprite, polices | 8-10 |
| B. Zone historique | feuilles de `ploopi3` remappées sur les jetons, composants du § 5.2, contrôle sur les 10 modules et `auzou` | 8-12 |
| C. Socle | variables, `block`, composants de `skin`, `popup.js`, préférences | 3-5 |
| D. `system` : espaces, groupes, utilisateurs | écran de la maquette complet, actions groupées, fiche en tiroir | 10-15 |
| E. `system` : autres écrans | espace, modules, paramètres, rôles, groupe ; installation et mise à jour, annuaire, outils, journaux ; profil, messagerie, annotations, recherche | 20-30 |
| F. Compléments | recherche instantanée, notifications, raccourcis, « rester connecté » | 5-8 |
| G. Tests et documentation | suites 12, 13, 29, 33 étendues ; parcours Playwright (390, 1 024, 1 440 px, clair et sombre) ; non-régression ARGOS ; `doc/habillage.md` | 6-10 |
| **Total** | | **60-90** |

**Premier jalon conseillé (12-15 jours)** : lots A et C, l'écran « Espaces de travail »
du lot D, et le parcours de non-régression d'ARGOS. Activé sur l'espace d'administration
seulement, il valide le contrat avec `auzou` avant d'engager le reste.

---

## 10. Décisions à prendre

1. **Socle** : accepter les modifications additives du § 7 (sinon : pas d'icônes de menu,
   pas de fil d'Ariane, préférences en `localStorage` seulement).
2. **`system` hors `ploopi4`** : bascule écran par écran (recommandé) ou `ploopi4`
   obligatoire pour l'administration.
3. **Thème sombre** : shell et `system` seulement, zone historique claire (recommandé),
   ou pas de thème sombre du tout dans un premier temps.
4. **Icônes** : Tabler pour tout (recommandé) ou Lucide comme la maquette.
5. **Compléments** : recherche instantanée, alertes métier dans la cloche, « rester
   connecté » — dans le périmètre ou plus tard.
6. **ARGOS** : dérivé `argos4` de `ploopi4`, et prérequis de passage d'ARGOS sur le socle
   courant.
