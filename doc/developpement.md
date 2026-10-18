# Développer Ploopi

Ce guide s'adresse à qui veut modifier le socle ou un module, ou en écrire un nouveau.
La **référence technique détaillée** (amorçage, contrôleur, ORM, droits, formulaires,
rendu, sécurité, pièges connus) est [`reference.md`](reference.md), dans `doc/` : ce
document en donne le mode d'emploi au quotidien et renvoie vers elle.

Pour installer Ploopi en production, voir [`deploiement.md`](deploiement.md).

---

## 1. Prérequis

- **PHP 8.1 à 8.5** en ligne de commande, avec les extensions `mysqli`, `gd`, `zip`,
  `curl`, `xml`, `mbstring`, `openssl` et `memcached` (exigées par `composer.json`) ;
- **Composer** ;
- **MariaDB** (10.11 en CI) ou MySQL ;
- `git`.

Le plus simple est de ne rien installer de tout cela à la main et d'utiliser DDEV.

## 2. Environnement de développement avec DDEV

[DDEV](https://ddev.com) monte l'environnement complet en local : Apache + PHP
(basculable 8.1 → 8.5), MariaDB 10.11 (la même version que la CI), Mailpit pour voir
les courriels envoyés, et `ddev ssh` pour un shell prêt à l'emploi.

C'est **complémentaire** du déploiement Docker (`docker/LISEZMOI.md`) : DDEV est fait
pour développer, le `docker-compose.yml` pour installer.

### Démarrer

```sh
# une seule fois : installer DDEV (https://ddev.com/get-started/)
# puis, dans le dépôt cloné :
ddev start
ddev composer install
ddev describe                  # affiche les URL et les accès
```

| URL | Usage |
|---|---|
| `https://ploopidev.ddev.site` | le dépôt servi par Apache |
| `http://ploopidev.ddev.site:8025` | Mailpit — tous les courriels interceptés |
| `http://ploopidev.ddev.site:8036` | phpMyAdmin, si l'add-on est installé |

Compte MariaDB du projet DDEV : `db` / `db` (utilisateur `db`, base `db`), et
`root` / `root`. Dans l'assistant d'installation : serveur `db`, base `db`.
Dossier des documents : `../ploopi-data` est hors du dépôt, donc hors du conteneur ;
indiquer un dossier du dépôt protégé par le `.htaccess`, par exemple `./data`
(après `cp .htaccess_modele .htaccess`).

### Bascule de version de PHP

La CI éprouve 8.1 à 8.5 ; pour reproduire localement :

```sh
ddev php-version 8.1           # ou 8.3, 8.4, 8.5…
ddev restart
ddev test
```

### Déboguer

```sh
ddev xdebug on                 # Xdebug (IDE à l'écoute sur le port 9003)
ddev xdebug off
```

`.ddev/php/custom.ini` relève les limites de dépôt et de mémoire. Aucun réglage de
`request_order` n'est nécessaire : `loader::boot()` ne garde que GET et POST dans
`$_REQUEST`.

### Commandes utiles

| Commande | Effet |
|---|---|
| `ddev ssh` | shell dans le conteneur web (php, composer, mysql…) |
| `ddev logs` | journaux Apache |
| `ddev snapshot --name avant-test` | sauvegarde la base |
| `ddev restore-snapshot avant-test` | la restaure |
| `ddev stop` | arrête les conteneurs (les données restent) |
| `ddev delete --omit-snapshot` | ⚠️ supprime base et données |

### Sans DDEV

Tout serveur Apache + PHP + MariaDB fait l'affaire : suivre l'installation manuelle de
[`deploiement.md`](deploiement.md), en activant l'affichage des erreurs dans `config/config.php`
(`_PLOOPI_DISPLAY_ERRORS`).

---

## 3. Organisation du dépôt

| Chemin | Contenu |
|---|---|
| `index.php`, `admin.php`… | points d'entrée web ; `cli` et `cron` pour la ligne de commande |
| `include/classes/` | le socle : classes du namespace `ploopi` |
| `include/`, `lang/`, `js/`, `lib/` | amorçage, opérations, langue, JavaScript, bibliothèques embarquées |
| `modules/system/` | le seul module natif (administration) |
| `install/<module>/` | **paquets d'installation** des modules : source de vérité |
| `install/system/` | schéma du socle (`ploopi.sql`) et ses mises à jour |
| `templates/` | gabarits backoffice, frontoffice et assistant d'installation |
| `config/` | modèle de configuration et assistant d'installation |
| `tests/` | suite de tests et harnais (`tests/README.md`) |
| `tools/` | scripts de maintenance (voir § 7) |
| `docker/`, `.ddev/` | déploiement Docker et environnement DDEV |
| `doc/` | cette documentation |
| `screenshots/` | captures d'écran de référence |

Le détail (amorçage, routage, modèle conceptuel, API) est dans `doc/reference.md` § 2 à § 7.

### Les deux jeux de fichiers d'un module

`modules/` est **ignoré par git** (hors `modules/system/`) : un module installé dans
`modules/<module>/` n'est qu'une **copie déployée** de `install/<module>/files/`.
**Toute modification pérenne d'un module se fait dans `install/<module>/files/`**, puis
se déploie :

- par le backoffice : *Administration système → Installation des modules →
  Mettre à jour* (copie les fichiers, applique les scripts SQL, recharge la métabase) ;
- ou, pour itérer vite, par copie : `cp -r install/<module>/files/. modules/<module>/`.

### Fichiers ignorés par git

`.gitignore` exclut `modules/`, `*.sql`, `*.sh`, `data/`, `vendor/`, `build/`,
`config/config.php` et `.htaccess`. Les fichiers versionnés dans ces chemins s'ajoutent
en le forçant :

```sh
git add -f install/monmodule/structure.sql tools/monscript.sh
git add -u modules/system          # modifications de fichiers déjà suivis
```

---

## 4. Faire évoluer un module

1. Modifier les sources dans `install/<module>/files/`.
2. Déployer (voir ci-dessus) et vérifier dans le navigateur.
3. Si le **schéma** change : ajouter `install/<module>/update/update_<version>.sql`
   (appliqué si sa version est strictement supérieure à la version installée et
   inférieure ou égale à la version cible) et reporter le changement dans
   `structure.sql` (installation neuve) et `mb.xml`.
4. Si des fichiers **disparaissent** du paquet : les lister dans
   `install/<module>/obsolete.txt` (un chemin relatif au module par ligne) ; la mise à
   jour les supprime de `modules/<module>/`.
5. Incrémenter `<version>` et `<date>` (`YmdHis`) dans `install/<module>/description.xml`
   et documenter le changement en tête de `install/<module>/changelog.txt`.
6. Si des JS/CSS changent : `tools/compress.sh` (voir § 6).
7. Lancer les tests (§ 5).

Après un changement de `description.xml` (paramètres, actions), recharger la session :
se reconnecter ou ajouter `?reloadsession` à l'URL.

**Écrire un nouveau module** : partir de l'architecture contrôleur / entités / actions
de `install/news2/` ou `install/nanogallery/`, et suivre la check-list de `doc/reference.md`
§ 16. Le modèle « legacy » (`admin.php` / `public.php` / `block.php`) ne sert qu'à
maintenir les modules historiques.

### Faire évoluer le socle

- Classes : `include/classes/<classe>.php`, une classe par fichier (autoload
  `ploopi\<classe>`).
- Schéma : ajouter `install/system/update_ploopi_<version>.sql`, reporter dans
  `install/system/ploopi.sql`, et relever `_PLOOPI_VERSION` dans
  `include/constants.php`. À la connexion suivante, l'administrateur système se voit
  proposer la mise à jour de la base (`modules/system/admin_system_update.php`).

---

## 5. Tests

Le dépôt a sa propre suite, sans dépendance Composer : `tests/bootstrap.php` en est le
harnais, chaque suite est un fichier `tests/NN_nom.php`. Le détail est dans
[`tests/README.md`](../tests/README.md).

### Lancer les tests

| Niveau | Commande | Quand |
|---|---|---|
| Suites unitaires | `tests/run.sh` | En continu : quelques secondes, PHP seul, sans instance |
| Suite complète | `tests/instance.sh test` | Avant de pousser : instance jetable, toutes les suites (PHP, MySQL, `curl`) ; `--navigateur` ajoute le parcours Playwright |
| Docker | `tests/docker.sh 8.x` ou `tout` | Une version de PHP précise, ou les cinq comme la CI, avec Docker seul (ni PHP, ni MySQL, ni Node) ; `--publier` affiche le résultat sur le commit dans GitHub (statuts `tests-docker/php8.x`), sans minute de CI |
| CI | *Actions* → *Tests* → *Run workflow* | À la demande : matrice 8.1 à 8.5, `composer audit`, PHPCompatibility |

```sh
tests/run.sh                         # suites unitaires seules : rien à préparer
tests/instance.sh test               # suite complète sur une instance jetable
tests/instance.sh test 01_http       # une suite précise
tests/instance.sh test --navigateur  # puis le parcours navigateur
tests/docker.sh tout --navigateur    # tout, sur PHP 8.1 à 8.5, dans Docker
ddev test                            # la suite complète depuis DDEV
```

Chaque passage écrit dans `tests/resultats/<version de PHP>/` (non versionné) un rapport
`rapport.html` autonome (bilan, échecs, captures du parcours), un rapport `junit.xml`, et
les journaux de l'instance conservés au démontage.

`tests/docker.sh … --publier` exige `gh` connecté (ou un jeton `GITHUB_TOKEN`) pour
l'utilisateur qui lance le script, et un commit poussé : mise en place pas à pas dans
[`tests/README.md`](../tests/README.md#publier-les-résultats-sur-github) ; avec
`PLOOPI_BADGE_GIST`, il met aussi à jour le badge du README (même section).

`tests/instance.sh test` crée une base dédiée, déroule l'assistant d'installation,
démarre le serveur PHP intégré, lance toutes les suites puis
nettoie tout. Il refuse de démarrer si `config/config.php` existe : un dépôt qui sert
déjà une instance n'est jamais touché (renommer temporairement le fichier, ou tester
depuis un second clone).

La CI GitHub (`.github/workflows/tests.yml`) lance la suite complète sur PHP 8.1 à 8.5
avec MariaDB 10.11, et lance `composer audit` sur les dépendances verrouillées par
`composer.lock`. Elle ne se déclenche qu'à la main (onglet *Actions* → *Tests* →
*Run workflow*) : lancer `tests/instance.sh test` localement avant de pousser.

Une modification n'est terminée que lorsque `tests/instance.sh test` est vert. Un
correctif se livre de préférence avec le contrôle qui l'éprouve, dans la suite la plus
proche ou dans une nouvelle suite numérotée.

---

## 6. JavaScript et feuilles de style

Les navigateurs ne reçoivent pas les sources mais des fichiers agrégés, et, derrière
Apache, leur version `.gz` (le `.htaccess` sert le `.gz` dès qu'il existe) :

| Servi | Construit à partir de |
|---|---|
| `js/functions.pack.js` | concaténation de `js/*.js` par ordre alphabétique (`aa_init.js` en premier : il crée l'objet `ploopi`) |
| `templates/backoffice/<gabarit>/css/styles.pack.css` | concaténation des `base_*.css` du gabarit |
| `<fichier>.gz` | `gzip` de `<fichier>` |

Après toute modification d'un JS ou d'une CSS :

```sh
tools/compress.sh
```

Le script reconstruit les agrégats et recompresse chaque `.gz` désaccordé de sa source
(les autres ne sont pas touchés, pour ne pas polluer l'historique). Il ne minifie
pas : la suite `33_icons_sans_inline` vérifie que chaque `.gz` est exactement la
version compressée de sa source, et qu'aucun agrégat n'est en retard.

Les ressources d'un module (`include/functions.js`, `include/styles.css`,
`include/javascript.php`, `include/head.php`) sont déclarées automatiquement par
`module::init()`. jQuery et jQuery UI sont fournis par Composer (`vendor/components/`).

### Éditeur de texte riche (`js/editor.js`)

Le socle et les modules partagent un seul éditeur, TinyMCE (Composer
`tinymce/tinymce`, GPL-2.0-or-later, clé de licence `'gpl'`), chargé à la première
demande par `ploopi.editor`. Un champ devient éditeur par son seul balisage, y compris
dans un contenu chargé plus tard (fenêtre, AJAX) :

```php
<textarea name="fck_message" id="message" data-ploopi-editor="simple"><?php
    echo ploopi\str::htmlentities($strContenu); // ★ toujours échappé : un « </textarea> » sortirait du champ
?></textarea>
```

| Attribut | Rôle |
|---|---|
| `data-ploopi-editor` | profil : `simple` (messages, descriptions) ou `complet` (articles) |
| `data-ploopi-editor-link` / `-image` | URL de la fenêtre de choix d'un lien / d'une image (`ploopi_op=doc_selectfile`, `doc_selectimage`, `webedit_selector`) |
| `data-ploopi-editor-objects` | `1` : bouton « Insérer un objet » (objets `mb_wce_object`, balise `<span class="ckTag">[[…]]</span>`) |
| `data-ploopi-editor-css` / `-height` | feuilles du contenu, hauteur minimale |

Côté PHP, `ploopi\form_richtext` produit ce balisage (options `profile`, `height`,
`css`, `link`, `image`). En JavaScript : `ploopi.editor.create(champ, options)` (promesse de
l'éditeur ; options `config` pour un réglage TinyMCE), `ploopi.editor.save()` avant un
envoi en AJAX, `ploopi.editor.get(id)`. Une fenêtre de choix reçoit `CKEditorFuncNum`
(nom hérité) et rend l'URL par `window.opener.ploopi.editor.picked(numéro, url, attributs)`.

L'éditeur conserve le HTML existant : le contenu se filtre **à l'enregistrement**
(`ploopi\str::htmlpurifier()`), ou à l'affichage pour un contenu historique. Sous le thème
sombre de `ploopi4`, son interface et son contenu sont sombres.

---

## 7. Scripts de maintenance (`tools/`)

Tous se lancent depuis n'importe quel dossier : ils se replacent à la racine du dépôt.

| Script | Rôle |
|---|---|
| `tools/lint.sh` | `php -l` sur tous les fichiers PHP (hors `vendor/`, `data/`) ; n'affiche que les erreurs, code retour non nul en cas d'échec |
| `tools/clean.sh [dossier…]` | normalise les sources : tabulations → 4 espaces, espaces en fin de ligne, lignes vides superflues |
| `tools/compress.sh` | reconstruit `functions.pack.js`, les `styles.pack.css` et les `.gz` désaccordés |
| `tools/compress_png.sh` | optimise les PNG sans perte (`optipng`) |
| `node tools/icones/generer.js [--seul chemin] [--planche f.png]` | rastérise les icônes du gabarit `ploopi3` (`tools/icones/correspondances.js`), des modules et des types de fichiers (`tools/icones/modules.js`) d'après les glyphes Tabler Icons ; exige Node.js, Playwright et, pour trois images, ImageMagick (voir `doc/habillage.md` § 9). Après un changement dans un module, monter sa version |
| `tools/postinstall.sh [user:groupe] [dossier_documents]` | droits des fichiers d'une installation manuelle (voir `deploiement.md`) |
| `tools/detect_mariadb_no_default_value_bug.sql` | requête de diagnostic : colonnes sans valeur par défaut, à vérifier lors d'une montée de version de MariaDB |

Il n'y a plus de génération automatique de la documentation d'API : phpDox, abandonné, ne
fonctionne plus sous PHP 8. La référence de l'API est `doc/reference.md` § 7, et le PHPDoc des
classes de `include/classes/` se lit directement ou dans un IDE.

---

## 8. Conventions

Le détail est dans `doc/reference.md` § 8 (sécurité) et § 14 (code). L'essentiel :

- **Français** partout : commentaires, PHPDoc, libellés, messages de commit.
- **En-tête GPL** en tête de chaque fichier PHP (copier celui d'un fichier voisin) et
  bloc PHPDoc `@package`, `@copyright`, `@license`, `@author`. Voir
  [`licence.md`](licence.md).
- Indentation de **4 espaces**, jamais de tabulation (contrôlé par la suite 13).
- Notation hongroise des variables (`$str…`, `$int…`, `$arr…`, `$obj…`, `$boo…`).
- URL internes toujours par `ploopi\crypt::urlencode()` / `queryencode()` (jeton
  anti-CSRF) ; affichage toujours par `ploopi\str::htmlentities()` ; SQL toujours par
  les marqueurs `%s` / `%d` du constructeur de requêtes ou `addslashes()`.
- Droits contrôlés en tête de **chaque** action, y compris les `op_*`.
- **Pas de nouvelle dépendance** (Composer ou bibliothèque embarquée) sans vérifier sa
  licence : elle doit être compatible avec la GPL (voir [`licence.md`](licence.md) § 4).

### Branches et pull requests

- Travailler sur une branche, ouvrir une pull request vers `master` ; la suite
  (`tests/instance.sh test`, ou la CI lancée à la main) doit être verte avant fusion.
- Un message de commit décrit le *pourquoi* du changement, en français.
