# CLAUDE.md — Développer des modules Ploopi

Guide de référence pour travailler sur ce dépôt, et en particulier pour **écrire, modifier ou packager un module Ploopi**.

---

## 1. Vue d'ensemble

**Ploopi** est une plateforme web PHP (portail / CMS / groupware) éditée par Ovensia, sous licence **GPL 2.0**.
Tout le fonctionnel est apporté par des **modules** installables : `webedit` (contenus web), `doc` (GED), `wiki`,
`planning`, `booking`, `forms`, `dbreport`, `directory`, `news2`, `nanogallery`… Le seul module natif, non
désinstallable, est **`system`** (`modules/system/`).

| Élément | Valeur |
|---|---|
| Langage | PHP >= 7.0 (procédural + classes, pas de framework tiers) |
| Base de données | MySQL / MariaDB via `mysqli` uniquement |
| Namespace racine | `ploopi` |
| Autoload | maison (`ploopi\loader::_autoload`) + Composer (`vendor/autoload.php`) |
| Encodage | UTF-8 partout (`mb_internal_encoding('UTF-8')`) |
| Front JS | jQuery + jQuery UI + namespace global `ploopi.*` |
| Templates | moteur maison type phpBB (`lib/template/template.php`, fichiers `.tpl`) |
| Version courante | `_PLOOPI_VERSION` dans `include/constants.php` |

**Langue du projet : français.** Commentaires, PHPDoc, libellés, messages de commit et noms de constantes de
langue sont en français. S'y conformer.

---

## 2. Arborescence du dépôt

```
/
├── index.php               Point d'entrée frontoffice (et backoffice si FO désactivé)
├── cli                     Point d'entrée CLI (maintenance : reindex, thumbnails…)
├── cron                    Point d'entrée CRON (boucle sur tous les module_type)
├── composer.json           Dépendances (mpdf, phpspreadsheet, ckeditor, htmlpurifier…)
├── .htaccess_modele        Modèle Apache (copié en .htaccess au post-install)
├── build.sh                Lint PHP de tout l'arbre (php -l)
├── clean.sh                Normalisation (tabs → 4 espaces, trailing spaces)
├── compress.sh             Minification/gzip des js/css (YUI Compressor)
├── create_redist.sh        Fabrication du paquet de redistribution
├── phpdox.sh / phpdox.xml  Génération de la doc API (→ doc/api)
│
├── config/
│   ├── config.php.model    Modèle de configuration (constantes _PLOOPI_*)
│   ├── config.php          (généré à l'installation, non versionné)
│   └── install/install.php Assistant d'installation initiale
│
├── include/
│   ├── classes/            ★ Cœur : ~114 classes du namespace `ploopi`
│   ├── constants.php       Constantes globales (_PLOOPI_*, _SYSTEM_*)
│   ├── op.php              Dispatcher des opérations `ploopi_op` (AJAX/actions courtes)
│   ├── op/                 Opérations génériques (documents, annotation, share…)
│   ├── backoffice.php      Rendu backoffice (index.tpl)
│   ├── frontoffice.php     Rendu frontoffice (délègue à webedit)
│   ├── light.php           Rendu allégé (light.tpl) pour AJAX/popups
│   ├── backend.php         Point d'entrée flux (RSS…) → modules/<mod>/backend.php
│   ├── webservice.php      Point d'entrée webservices
│   └── javascript.php      Variables JS globales (_PLOOPI_ENV, _PLOOPI_BASEPATH)
│
├── modules/
│   └── system/             ★ Module natif (admin, utilisateurs, espaces, modules, outils)
│
├── install/                ★ Paquets d'installation des modules (source de vérité du packaging)
│   ├── system/             ploopi.sql (schéma cœur) + mb.xml + update_ploopi_*.sql
│   └── <module>/           description.xml, structure.sql, mb.xml, data.xml, files/, update/
│
├── templates/
│   ├── backoffice/         Templates BO (index.tpl, light.tpl, css, img) — ex: ploopi2
│   ├── frontoffice/        Templates FO (utilisés par webedit)
│   └── install/            Template de l'assistant d'installation
│
├── js/                     JS cœur (concaténé/minifié en functions.pack.js)
├── lib/template/           Moteur de template
├── lang/                   Fichiers de langue du cœur (french.php par défaut)
├── img/, data/, doc/, bin/, tools/
```

> **Note git** : `.gitignore` exclut `modules/`, `*.sql`, `data/`, `vendor/`, `config/config.php`.
> `modules/system/` et les `.sql` de `install/` sont versionnés en forçant l'ajout. Les modules
> installés dans `modules/<mod>/` sont donc, par construction, **des copies déployées** de
> `install/<mod>/files/`. **Toute modification pérenne d'un module se fait dans `install/<mod>/files/`.**

---

## 3. Amorçage et points d'entrée

### 3.1 Séquence de démarrage (web)

`index.php` (ou `admin.php`) →

```php
define('_PLOOPI_DIRNAME', dirname(__FILE__));
include_once './include/classes/loader.php';
ploopi\loader::boot();      // environnement
ploopi\loader::dispatch();  // routage
ploopi\system::kill();      // fin propre (flush buffer)
```

`loader::boot()` enchaîne :

1. Timer principal (`timer::get()`), `mb_internal_encoding('UTF-8')`
2. `spl_autoload_register` + `vendor/autoload.php`
3. Chargement `config/config.php` (sinon → assistant d'installation)
4. `buffer::start()` (bufferisation + gzip), `error::set_handler()`
5. `include './include/constants.php'`
6. Gestionnaire de session (`php`, `db`, `file` ou `memcached` selon `_PLOOPI_SESSION_HANDLER`), `session_start()`
7. `loader::_rewrite()` — rewriting inverse : reconnaissance de `/admin.php`, `/admin-light.php`,
   `/index-light.php`, `/webservice.php`, `/backend.php`, `/index-quick.php`, `/robots.txt`, puis
   **inclusion de `./modules/*/include/rewrite.php`** de chaque module
8. `loader::_importgpr()` — déchiffrement de `ploopi_url` puis `security::filtervar()` sur
   `$_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER`
9. Authentification / session / en-têtes HTTP / `cache::init()`

### 3.2 Les scripts (routage de `loader::dispatch()`)

| Script | Fichier inclus | Usage |
|---|---|---|
| `index` | `frontoffice.php` ou `backoffice.php` | Page publique / portail |
| `admin` | `backoffice.php` | Backoffice complet (menus, blocs, habillage) |
| `admin-light` / `index-light` | `light.php` | **Rendu sans habillage** : AJAX, popups, `op_*` |
| `webservice` | `webservice.php` | API : `webservice.php?module=<nom>` |
| `backend` | `backend.php` | Flux (RSS/Atom) : `modules/<mod>/backend.php` |
| `quick` | `include/op.php` seul | Opérations rapides sans session complète |

Le mode (`$_SESSION['ploopi']['mode']`) vaut `backoffice` ou `frontoffice`.

### 3.3 CLI et CRON

```sh
./cli module=doc action=reindex      # → entity=cli, action=reindex sur le contrôleur du module
./cron                               # → entity=cron sur le contrôleur de CHAQUE module_type
```

Les deux appellent `loader::boot_cli()` (pas de session, pas de buffer, pas de template) puis, pour chaque
module possédant `ploopi\<module>\controller`, forcent `$_REQUEST['entity']` (`cli`/`cron`) et invoquent
`dispatch()`. Rétrocompatibilité : `modules/<mod>/cli.php` et `modules/<mod>/cron.php`.

Crontab type : `* * * * * /var/www/ploopi/cron > /dev/null 2>&1`

---

## 4. Modèle conceptuel

```
module_type  (1)──(n)  module  (n)──(n)  workspace
   │                     │                  │
   │                     │                  ├── workspace_user  (adminlevel)
   ├── param_type        ├── param_default  ├── workspace_group
   ├── param_choice      ├── param_workspace├── workspace_user_role
   ├── mb_action         ├── param_user     └── workspace_group_role
   ├── mb_table/field/…  ├── role ──(n)── role_action
   └── mb_wce_object     └── (données métier : ploopi_mod_<module>_*)
```

| Concept | Table | Description |
|---|---|---|
| **module_type** | `ploopi_module_type` | Le *type* de module installé (le code). Un seul par nom. `label` = nom du dossier. |
| **module** | `ploopi_module` | Une **instance** de module_type. Plusieurs instances possibles. `_PLOOPI_MODULE_SYSTEM = 1`. |
| **workspace** | `ploopi_workspace` | Espace de travail, hiérarchique (`parents`, `depth`), avec domaines FO/BO. |
| **user / group** | `ploopi_user`, `ploopi_group` | Utilisateurs et groupes, rattachés aux espaces. |
| **role / action** | `ploopi_role`, `ploopi_role_action` | Un rôle = un ensemble d'actions sur un module ; affecté à un user ou un groupe dans un espace. |
| **action** | `ploopi_mb_action` | Les actions déclarées par un module_type (`id_action` + libellé). |
| **param** | `ploopi_param_type/_default/_workspace/_user` | Paramètres à 3 niveaux de surcharge. |

### 4.1 Niveaux d'administration

```php
_PLOOPI_ID_LEVEL_VISITOR       0
_PLOOPI_ID_LEVEL_USER         10
_PLOOPI_ID_LEVEL_GROUPMANAGER 15
_PLOOPI_ID_LEVEL_GROUPADMIN   20
_PLOOPI_ID_LEVEL_SYSTEMADMIN  99   // bypasse tous les contrôles d'ACL
```

### 4.2 Modes de vue d'un module (`ploopi_module.viewmode`)

Détermine **la liste des espaces dont un module voit les données** ; à combiner avec `transverseview`
(ajoute les espaces frères). Utiliser systématiquement :

```php
$objQuery->add_where('id_workspace IN ('.ploopi\system::viewworkspaces().')');
```

| Constante | Sens |
|---|---|
| `_PLOOPI_VIEWMODE_PRIVATE` | Espace courant seul (défaut) |
| `_PLOOPI_VIEWMODE_DESC` | Espace courant + parents |
| `_PLOOPI_VIEWMODE_ASC` | Espace courant + enfants |
| `_PLOOPI_VIEWMODE_ASCDESC` | Parents + enfants + courant |
| `_PLOOPI_VIEWMODE_GLOBAL` | Tous les espaces |

### 4.3 La session `$_SESSION['ploopi']`

Structure clé à connaître (renseignée par `loader::start()`) :

```php
$_SESSION['ploopi']['mode']              // 'backoffice' | 'frontoffice'
$_SESSION['ploopi']['userid']            // id utilisateur
$_SESSION['ploopi']['user']              // ligne complète ploopi_user
$_SESSION['ploopi']['workspaceid']       // espace courant
$_SESSION['ploopi']['moduleid']          // instance de module courante
$_SESSION['ploopi']['moduletype']        // ex. 'news2'
$_SESSION['ploopi']['moduletypeid']
$_SESSION['ploopi']['modulelabel']
$_SESSION['ploopi']['action']            // 'public' | 'admin'
$_SESSION['ploopi']['adminlevel']        // niveau dans l'espace courant
$_SESSION['ploopi']['connected']         // bool
$_SESSION['ploopi']['env']               // "mainmenu/workspaceid/moduleid/action[/token]"
$_SESSION['ploopi']['modules'][$id]      // infos module + TOUS ses paramètres résolus
$_SESSION['ploopi']['workspaces'][$id]['modules']   // ids des modules de l'espace
$_SESSION['ploopi']['actions'][$wid][$mid][$aid]    // ACL résolues
$_SESSION['ploopi']['template_path']     // ./templates/backoffice/<nom>
$_SESSION['ploopi']['token'] / ['tokens']
```

---

## 5. Anatomie d'un module

### 5.1 Architecture recommandée (contrôleur / entités / actions)

C'est le modèle **moderne**, à utiliser pour tout nouveau module. Références dans le dépôt :
`install/news2/files/` et `install/nanogallery/files/`.

```
modules/<module>/                      (déployé depuis install/<module>/files/)
├── classes/
│   ├── controller.php                 ★ class ploopi\<module>\controller extends ploopi\controller
│   ├── <module>.php                   Modèle : extends ploopi\data_object
│   └── tools.php                      Helpers statiques + constantes ACTION_*/OBJECT_*
├── actions/
│   ├── <entity>/
│   │   ├── _header.php                Exécuté avant l'action (onglets, contrôle d'accès)
│   │   ├── _footer.php                Exécuté après l'action
│   │   ├── default.php                Action par défaut
│   │   ├── <action>.php               Vue
│   │   └── op_<action>.php            Traitement (POST) — appelé via admin-light.php
│   ├── home/default.php               ★ Obligatoire : entité par défaut
│   └── error/default.php              ★ Obligatoire : entité d'erreur (fallback)
├── include/
│   ├── global.php                     Constantes + fonctions du module (chargé par module::init)
│   ├── head.php                       HTML injecté dans <head>
│   ├── javascript.php                 JS inline généré en PHP
│   ├── functions.js                   JS statique (déclaré comme <script src>)
│   ├── styles.css / styles_ie.css     CSS du module
│   ├── rewrite.php                    Règles de rewriting inverse (frontoffice)
│   ├── create.php                     Exécuté à la création d'une instance
│   └── delete.php                     Exécuté à la suppression d'une instance
├── lang/
│   └── french.php                     Constantes de langue (+ english.php, etc.)
├── img/
├── op.php                             Opérations `ploopi_op` du module
├── wce.php                            Rendu des objets insérables dans une page webedit
├── template.php                       Alimentation des variables du template frontoffice
├── template_content.php               Rendu "module comme contenu de page" (FO)
├── backend.php                        Flux RSS/Atom
└── webservice.php                     API (legacy ; préférer entity=webservice)
```

### 5.2 Le contrôleur

`include/classes/controller.php` fournit la classe de base. Un module la spécialise :

```php
namespace ploopi\news2;
use ploopi;

class controller extends ploopi\controller {
    public function setBlock() {
        $this->addBlockMenu('Toutes les News', 'public', 'all');
        $this->addBlockMenu('Administration', 'admin', 'default', tools::ACTION_ANY);
    }
}
```

**Détection.** Ploopi cherche la classe `ploopi\<moduletype>\controller` (via `loader::classExists()`, qui
mappe `ploopi\news2\controller` → `./modules/news2/classes/controller.php`). Si elle existe, il appelle
`::get()->dispatch()`. Sinon il retombe sur le mode legacy (`admin.php`/`public.php` à la racine du module).

**`dispatch()` fait, dans l'ordre :**

1. Déduit le nom du module depuis le namespace de la classe appelante
2. `$this->_booLight = (CLI || script == 'admin-light' || script == 'webservice')`
3. `ploopi\module::init($module, !$light, !$light, !$light)` — charge global/lang/head/js/css
4. Lit `$_REQUEST['entity']` (défaut `home`) et `$_REQUEST['action']` (défaut `default`),
   **nettoyés** par `_cleanParam()` : minuscules, sans accents, seuls `[a-z0-9/_]` conservés
5. Résout `actions/<entity>/<action>.php` ; si absent → `actions/error/default.php`
6. Mode normal : inclut `actions/<entity>/_header.php` puis l'action puis `actions/<entity>/_footer.php`
   Mode light : `buffer::clean()` avant, `system::kill()` après (donc **pas de footer en mode light**)

**Méthodes utiles héritées :**

| Méthode | Rôle |
|---|---|
| `static get($intModuleId = null)` | Fabrique/singleton par instance de module |
| `dispatch()` | Routage (voir ci-dessus) |
| `getEntity()` / `getAction()` | Entité / action courante |
| `getModuleId()` / `getModuleTypeId()` | Résolution session **puis** BDD (fonctionne en CLI) |
| `getParam($nom)` | Lit `ploopi_param_default` en base (utile hors session/CLI) |
| `getBlock()` | Instance `ploopi\block` du menu latéral |
| `setBlock()` | **À surcharger** pour construire le menu |
| `addBlockMenu($label, $entity, $action = '', $intAction = null)` | Ajoute une entrée (avec contrôle ACL optionnel) |

Dans un fichier d'action, `$this` **est** le contrôleur (l'action est incluse depuis `dispatch()`).
D'où les usages `$this->getModuleId()`, `$this->getAction()` visibles dans `news2`.

⚠️ **Piège connu** : `dispatch()` teste l'existence de `actions/_header.php` / `actions/_footer.php`
mais inclut en réalité `header.php` / `footer.php` à la racine du module. Les en-têtes/pieds fiables sont
donc ceux **au niveau entité** (`actions/<entity>/_header.php`). Aucun module du dépôt n'utilise le niveau
module.

### 5.3 Conventions d'URL

```php
// Vue (rendu complet, habillage BO)
ploopi\crypt::urlencode("admin.php?entity=admin&action=write&id={$id}")

// Traitement / AJAX / popup (rendu nu)
ploopi\crypt::urlencode("admin-light.php?entity=admin&action=op_write&id={$id}")

// Opération courte (ploopi_op) en AJAX
ploopi\crypt::queryencode("ploopi_op=news2_selectredirect")
```

`crypt::urlencode()` / `queryencode()` chiffrent la query string en un paramètre `ploopi_url` **et**
injectent `ploopi_env` (menu/espace/module/action + **jeton CSRF**). Sans cela le backoffice rejette la
requête (« Jeton absent / non valide ») quand `_PLOOPI_TOKEN` est actif.
**Ne jamais construire une URL interne à la main.**

Signature : `urlencode($url, $mainmenu = null, $workspaceid = null, $moduleid = null, $action = null, $addenv = true, $trusted = false)`

### 5.4 Le mode legacy (modules historiques)

Modules sans `classes/controller.php` (webedit, doc, wiki, planning, booking, forms, dbreport, directory) :

- `modules/<mod>/admin.php` — inclus si `$_SESSION['ploopi']['action'] == 'admin'`
- `modules/<mod>/public.php` — inclus sinon
- `modules/<mod>/block.php` — construit le menu ; la variable `$block` (objet `ploopi\block`) est
  fournie par `include/blocks.php`, on y appelle `$block->addmenu(...)` / `$block->addcontent(...)`
- routage interne par `switch($_REQUEST['op'])` et fichiers `admin_xxx.php`
- classe optionnelle `modules/<mod>/classes/<mod>.php` avec `static isAllowed($workspaceid, $moduleid)`
  pour masquer le bloc de menu

Ne pas créer de nouveaux modules sur ce modèle ; s'en inspirer uniquement pour maintenir l'existant.

---

## 6. Le paquet d'installation (`install/<module>/`)

C'est le **format de distribution** d'un module. Traité par
`modules/system/admin_system_installmodules_installproc.php` (installation) et
`…_updateproc.php` (mise à jour).

```
install/<module>/
├── description.xml     ★ Identité, paramètres, actions (obligatoire)
├── structure.sql       Schéma des tables (CREATE TABLE)
├── data.xml            Données initiales (facultatif)
├── mb.xml              Métabase : objets, tables, champs, relations, objets WCE
├── files/              ★ Contenu copié dans modules/<module>/
├── update/
│   └── update_<version>.sql   Deltas SQL appliqués par ordre de version
└── changelog.txt
```

### 6.1 `description.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ploopi>
    <moduletype>
        <label>news2</label>              <!-- = nom du dossier, du namespace, du module_type -->
        <version>3.1.0</version>
        <author>Ovensia</author>
        <date>20260205000000</date>       <!-- timestamp MySQL YmdHis -->
        <description>Gestion de nouvelles</description>

        <paramtype>                        <!-- → ploopi_param_type -->
            <name>nbnewsdisplay</name>
            <label>Nombre de news affichées</label>
            <default_value>10</default_value>
            <public>1</public>             <!-- 1 = modifiable par le gestionnaire d'espace -->
            <description/>
            <paramchoice>                  <!-- optionnel : liste fermée → ploopi_param_choice -->
                <value>1</value>
                <displayed_value>oui</displayed_value>
            </paramchoice>
        </paramtype>

        <action>                           <!-- → ploopi_mb_action (droits attribuables aux rôles) -->
            <id_action>1</id_action>
            <label>Rédiger une news</label>
            <id_object>0</id_object>       <!-- optionnel -->
            <role_enabled>1</role_enabled> <!-- optionnel, 1 par défaut -->
        </action>
    </moduletype>
</ploopi>
```

Les `id_action` déclarés ici **doivent correspondre** aux constantes du module :

```php
abstract class tools {
    const ACTION_ANY       = -1;   // « au moins une action » (convention)
    const ACTION_WRITE     = 1;
    const ACTION_MODIFY    = 2;
    const ACTION_DELETE    = 3;
    const OBJECT_NEWS2     = 1;    // type d'objet métier (indexation, documents, annotations…)
}
```

### 6.2 `structure.sql`

Convention de nommage : **`ploopi_mod_<module>_<entite>`**. Toute table métier porte au minimum :

```sql
CREATE TABLE IF NOT EXISTS `ploopi_mod_news2_entry` (
  `id`           int(10) unsigned NOT NULL auto_increment,
  ...
  `id_module`    int(10) unsigned default '0',   -- ★ cloisonnement par instance
  `id_workspace` int(10) unsigned default '0',   -- ★ cloisonnement par espace
  `id_user`      int(10) unsigned default '0',   -- ★ auteur
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

`data_object::setuwm()` renseigne d'un coup ces trois champs depuis la session.

### 6.3 `data.xml` (données initiales)

Format générique : le nom de balise de 1er niveau est le **nom de la table**, chaque `<row>` un
enregistrement, chaque balise fille un champ.

```xml
<ploopi>
  <ploopi_mod_news2_cat>
    <row><id>1</id><title>Général</title></row>
  </ploopi_mod_news2_cat>
</ploopi>
```

### 6.4 `mb.xml` (métabase)

Décrit le module au reste de la plateforme (utilisé par `dbreport`, l'éditeur de contenus, les liens
inter-modules). `id_module_type` est injecté automatiquement à l'import.

| Bloc | Rôle |
|---|---|
| `<ploopi_mb_object>` | Types d'objets métier + `script` = query string permettant d'ouvrir un enregistrement. Placeholders `<IDWORKSPACE>`, `<IDMODULE>`, `<IDRECORD>`. |
| `<ploopi_mb_table>` | Tables exposées (`name`, `label`, `visible`) |
| `<ploopi_mb_field>` | Champs exposés (`tablename`, `name`, `label`, `type`, `visible`) |
| `<ploopi_mb_schema>` | Couples de tables liées |
| `<ploopi_mb_relation>` | Jointures (`tablesrc`/`fieldsrc` → `tabledest`/`fielddest`) |
| `<ploopi_mb_wce_object>` | Objets insérables dans une page webedit (voir §11.3) |

⚠️ Le parser d'installation gère aussi une balise `<cms_object>` dans `description.xml`, mais elle
instancie une classe `ploopi\mb_cms_object` **qui n'existe pas** dans `include/classes/`
(la classe réelle est `mb_wce_object`). **Déclarer les objets WCE dans `mb.xml`**, pas dans
`description.xml`.

### 6.5 Cycle de vie

| Étape | Déclencheur | Effets |
|---|---|---|
| **Installation** | Admin système → Installer un module | copie `files/` → `modules/<mod>/` ; import `description.xml` ; exécution `structure.sql` ; import `data.xml` ; import `mb.xml` |
| **Mise à jour** | Admin système → Mettre à jour | copie `files/` ; `module_type::update_description()` ; exécution des `update/update_<v>.sql` **strictement > version installée et <= version cible**, triés par `version_compare` ; purge + réimport de la métabase |
| **Création d'instance** | Rattachement d'un module à un espace | duplication de `ploopi_param_type` → `ploopi_param_default` ; inclusion de `modules/<mod>/include/create.php` (legacy : `include/admin_instance_create.php`), avec `$admin_moduleid` disponible |
| **Suppression d'instance** | `module::delete()` | inclusion de `include/delete.php` (legacy : `admin_instance_delete.php`) puis purge des params, rôles, partages, abonnements, validations |
| **Désinstallation** | Admin système | `admin_system_installmodules_uninstallproc.php` |

Le numéro de version dans `description.xml` **pilote** la mise à jour : incrémenter, ajouter le
`update/update_<nouvelle_version>.sql` correspondant, et documenter dans `changelog.txt`.

---

## 7. API PHP — classes du namespace `ploopi`

### 7.1 Autoload : règle de nommage

```
ploopi\<classe>              → ./include/classes/<classe>.php
ploopi\<module>\<classe>     → ./modules/<module>/classes/<classe>.php
ploopi\<module>\<a>\<b>      → ./modules/<module>/classes/<a>/<b>.php
```

Une classe **par fichier**, nom de fichier = nom de classe en minuscules.
`ploopi\loader::classExists($fqcn)` teste l'existence du fichier sans le charger.

### 7.2 ORM : `data_object`

```php
namespace ploopi\news2;
use ploopi;

class news2 extends ploopi\data_object {
    public function __construct() { parent::__construct('ploopi_mod_news2_entry'); }
    // clé composite : parent::__construct('ma_table', 'id_a', 'id_b');
    // ou             parent::__construct('ma_table', ['id_a','id_b']);
}
```

| Méthode | Rôle |
|---|---|
| `open(...$ids)` | Charge un enregistrement (retourne `false` si absent). Positionne `$this->fields`. |
| `open_row($row)` | Hydrate depuis une ligne de recordset (sans requête) |
| `init_description()` | Initialise `fields` depuis `DESCRIBE` (valeurs par défaut) — **à faire avant un insert** |
| `setvalues($array, $prefix = '')` | Alimente depuis `$_POST` avec dépréfixage (`news_title` → `title`) |
| `setuwm()` | Renseigne `id_user`, `id_workspace`, `id_module` depuis la session |
| `save()` | INSERT ou UPDATE selon `$this->new` ; retourne la clé |
| `delete()` | DELETE |
| `isnew()` / `gethash()` / `getsql()` / `gettablename()` | Divers |
| `totemplate($tpl, $prefix)` | Exporte les champs en variables de template (MAJUSCULES) |
| `static get_collection()` / `get_objects()` | Collection (`data_object_collection`) |

Surcharger `save()` / `delete()` pour la logique métier (indexation, purge de documents liés…), en
appelant `parent::`.

`data_object_collection` :

```php
$objCol = news2::get_collection();
$objCol->add_where('id_module = %d', $intModuleId);
$objCol->add_orderby('date_publish DESC');
$arrObjets = $objCol->get_objects();
```

### 7.3 Requêtes : `query_select` / `query_insert` / `query_update` / `query_delete`

```php
$objQuery = new ploopi\query_select();
$objQuery->add_select('e.*');
$objQuery->add_select("IFNULL(c.title, 'Inconnue') AS titlecat");
$objQuery->add_from('ploopi_mod_news2_entry e');
$objQuery->add_leftjoin('ploopi_mod_news2_cat c ON c.id = e.id_cat');
$objQuery->add_innerjoin('ploopi_module m ON m.id = e.id_module');
$objQuery->add_where('e.id_module = %d', $intModuleId);
$objQuery->add_where('e.id_workspace IN ('.ploopi\system::viewworkspaces().')');
$objQuery->add_groupby('e.id');
$objQuery->add_having('COUNT(*) > %d', 0);
$objQuery->add_orderby('e.date_publish DESC');
$objQuery->add_limit(10);

$objRs = $objQuery->execute();                 // ploopi\recordset
while ($row = $objRs->fetchrow()) { … }
$objRs->numrows(); $objRs->getarray(); $objRs->getjson(); $objRs->getcsv();
```

**Le 2e argument de `add_where()`/`add_select()`/`add_having()` est échappé** (format `sprintf` :
`%s` chaîne échappée, `%d` entier). C'est la façon sûre d'injecter des valeurs.

Accès direct :

```php
$db = ploopi\db::get();                       // singleton mysqli
$rs = $db->query("SELECT … WHERE x = '".$db->addslashes($v)."'");
while ($row = $db->fetchrow($rs)) { … }
$db->numrows($rs); $db->insertid(); $db->affectedrows();
$db->begin_transaction(); $db->commit(); $db->rollback();
$db->getarray($rs, $firstcolkey); $db->getjson($rs);
$db->multiplequeries($sql); ploopi\db::split_sql($sql);
```

⚠️ **Aucune requête préparée dans le socle.** Toute valeur externe doit passer par
`$db->addslashes()` / `escape_string()` ou par les placeholders du query builder, ou être castée
(`intval`, `is_numeric`).

### 7.4 Droits : `acl`

```php
ploopi\acl::isactionallowed($actionid, $workspaceid = -1, $moduleid = -1)  // -1 = valeurs de session
ploopi\acl::isactionallowed([tools::ACTION_WRITE, tools::ACTION_MODIFY])   // OU logique
ploopi\acl::isactionallowed(-1)                                           // au moins une action
ploopi\acl::isadmin($workspaceid = -1)        // administrateur système
ploopi\acl::ismanager($workspaceid = -1)      // gestionnaire d'espace ou +
ploopi\acl::ismoduleallowed($moduletype, $moduleid = -1, $workspaceid = -1)
ploopi\acl::actions_getusers($id_action, $id_module = -1, $id_workspace = -1)
```

**Chaque action et chaque vue doit contrôler ses droits en tête de fichier :**

```php
if (!ploopi\acl::isactionallowed(tools::ACTION_WRITE)) {
    ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));
}
```

Un `_PLOOPI_ID_LEVEL_SYSTEMADMIN` passe systématiquement.

### 7.5 Paramètres : `param`

```php
ploopi\param::get('nbnewsdisplay');                 // module courant
ploopi\param::get('nbnewsdisplay', $intModuleId);   // module donné
$_SESSION['ploopi']['modules'][$id]['nbnewsdisplay'];  // équivalent direct
$this->getParam('nbnewsdisplay');                   // depuis le contrôleur (lit la BDD → OK en CLI)
```

Résolution en cascade au chargement de session : `param_default` → `param_workspace` → `param_user`.
`param::load()` est rappelée à chaque changement d'espace.

### 7.6 Rendu : `skin`

`ploopi\skin::get()` retourne le singleton lié au template courant.

```php
echo ploopi\skin::get()->open_simplebloc('Titre', $style, $styletitle, $additionnal_title);
echo ploopi\skin::get()->close_simplebloc();
echo ploopi\skin::get()->create_pagetitle('Titre de page');
echo ploopi\skin::get()->create_tabs($arrTabs, $strTabSel);      // onglets
echo ploopi\skin::get()->create_toolbar($arrIcons, $strIconSel, $sel, $vertical);
echo ploopi\skin::get()->create_popup('Titre', $contenuHtml, 'mon_popup_id');
echo ploopi\skin::get()->display_selectbox($id, $name, $arrValues, $arrOptions, $sel);
echo ploopi\skin::get()->display_treeview($arrNodes, $arrTree, $idSel);
ploopi\skin::get()->values['colprim'];   // couleurs du thème
```

**Tableaux avancés** (tri, pagination, rafraîchissement AJAX) :

```php
$columns = [];
$columns['left']['title']      = ['label' => 'Titre',  'width' => 250, 'options' => ['sort' => true]];
$columns['auto']['desc']       = ['label' => 'Description',            'options' => ['sort' => true]];
$columns['right']['date']      = ['label' => 'Date',   'width' => 140, 'options' => ['sort' => true]];
$columns['actions_right']['actions'] = ['label' => 'Actions', 'width' => 60];

$values = [];
$values[0]['values']['title'] = ['label' => ploopi\str::htmlentities($row['title'])];
$values[0]['values']['date']  = ['label' => $localdate['date'], 'sort_label' => $row['date_publish']];
$values[0]['values']['actions'] = ['label' => '<a href="…"><img …/></a>'];

ploopi\skin::get()->display_array($columns, $values, 'array_news', [
    'height' => 400, 'sortable' => true,
    'orderby_default' => 'date', 'sort_default' => 'DESC',
    'limit' => 50, 'page' => 1, 'callback' => 'maFonctionJs'
]);
```

Groupes de colonnes disponibles : `left`, `auto`, `right`, `actions_left`, `actions_right`.
Propriétés de cellule : `label`, `style`, `class`, `sort_label`.
Le tableau est sérialisé en base (`ploopi_serializedvar`) pour permettre le rafraîchissement AJAX via
`ploopi_op=ploopi_skin_array_refresh`.

### 7.7 Formulaires

```php
use ploopi\form_field;
use ploopi\form_button;

$objForm = new ploopi\form(
    'news2_form',
    ploopi\crypt::urlencode('admin-light.php?entity=admin&action=op_write'),
    'post',
    ['class' => 'ploopi_generate_form news2form']
);

$objForm->addPanel($objPanel = new ploopi\form_panel('', null, ['style' => 'width:49%;float:left;']));

$objPanel->addField(new form_field('input:text', 'Titre :', $v, 'news_title', 'news_title',
    ['title' => 'Titre', 'required' => true, 'maxlength' => 255, 'datatype' => 'string']));
$objPanel->addField(new ploopi\form_select('Catégorie :', $arrCateg, $sel, 'news_id_cat'));
$objPanel->addField(new ploopi\form_html('<textarea …></textarea>'));   // HTML libre

$objForm->addButton(new form_button('input:submit', 'Enregistrer'));
$objForm->addJs("…");                       // JS ajouté au formulaire
echo $objForm->render();
```

**Types de champ** (`form_element`) : `input:hidden|text|number|email|time|date|month|password|file`,
`textarea`, `input:button|submit|reset|img`, `input:radio|checkbox`, `select`, `option`, `text`,
`richtext`, `datetime`.

**Classes dédiées** : `form_field`, `form_select`, `form_checkbox`, `form_checkbox_list`, `form_radio`,
`form_radio_list`, `form_datetime`, `form_richtext`, `form_html`, `form_htmlfield`, `form_hidden`,
`form_text`, `form_button`, `form_panel`, `form_selection_option`.

**Options courantes** : `required`, `readonly`, `disabled`, `multiple`, `placeholder`, `description`,
`maxlength`, `min`/`max`/`step`, `datatype` (`int|float|string|date|time|phone|email|color` → validation
JS), `class`, `style`, `class_form`, `style_form`, `raw` (supprime le `<div><label>` d'habillage),
`autofocus`, `autocomplete`, `spellcheck`, et tous les événements `on*`.

Côté traitement, récupérer avec le préfixe :

```php
$objNews->setvalues($_POST, 'news_');   // news_title → fields['title']
```

### 7.8 Chaînes, dates, tableaux

```php
// str
ploopi\str::htmlentities($s);              // ★ échappement HTML obligatoire à l'affichage
ploopi\str::htmlpurifier($html, $trusted); // nettoyage de HTML riche (HTMLPurifier)
ploopi\str::convertaccents($s); ploopi\str::tourl($s); ploopi\str::clean_filename($s);
ploopi\str::cut($s, 30, 'left'); ploopi\str::nl2br($s); ploopi\str::make_links($t);
ploopi\str::print_json($var); ploopi\str::getwords($c); ploopi\str::highlight($c, $words);
ploopi\str::urlrewrite($url, $rules, $title);

// date — format pivot interne = timestamp MySQL 'YmdHis' (varchar(14))
ploopi\date::createtimestamp();                          // maintenant, YmdHis
ploopi\date::timestamp2local($ts);                       // ['date' => …, 'time' => …]
ploopi\date::local2timestamp($date, $heure, _PLOOPI_DATEFORMAT_US);
ploopi\date::timestamp2unixtimestamp($ts); ploopi\date::unixtimestamp2timestamp($u);
ploopi\date::timestamp_add($ts, $h, $mn, $s, $m, $d, $y);
ploopi\date::tz_timestamp2timestamp($ts, 'UTC', 'Europe/Paris');
ploopi\date::open_calendar('mon_champ_id');              // sélecteur de date

// arr
ploopi\arr::tojson(); ploopi\arr::toxml(); ploopi\arr::tocsv(); ploopi\arr::tohtml();
ploopi\arr::toxls(); ploopi\arr::toexcel(); ploopi\arr::toods();
ploopi\arr::getpages($nbRows, $maxLines, $urlMask, $pageSel);
```

⚠️ Les dates métier sont stockées en **`varchar(14)` au format `YmdHis`**, pas en `DATETIME`.
Les tris/comparaisons se font donc lexicographiquement, ce qui est correct pour ce format.

### 7.9 Sortie, redirection, fin de traitement

```php
ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=admin'));
ploopi\output::redirect($url, $urlencode = true, $internal = true, $refresh = 0, $trusted = false);
ploopi\output::h404();
ploopi\output::print_r($var);            // debug formaté
ploopi\system::kill($message = null);    // fin propre : flush du buffer puis exit
ploopi\buffer::clean();                  // vide le buffer (rendu nu)
```

En **mode light**, le contrôleur appelle déjà `buffer::clean()` avant et `system::kill()` après l'action.

### 7.10 Journalisation et erreurs

```php
ploopi\user_action_log::record($id_action, $id_record, $id_module_type = 0, $id_module = 0, $id_workspace = 0);
ploopi\user_action_log::get($id_record, $id_object, $id_action, …);
ploopi\error::syslog(LOG_INFO, 'message');
```

Tracer systématiquement les créations/modifications/suppressions métier avec `user_action_log::record()`.

### 7.11 Services transverses

| Classe | Usage |
|---|---|
| `search_index` | Indexation plein texte : `add($id_object, $id_record, $label, $content, $meta, …)`, `remove()`, `search($keywords, …)`, `remove_index_module()`. Variante `search_index_es` (Elasticsearch). |
| `documents` | GED attachée à un objet métier : `insert()`, `savefile()`, `savefolder()`, `getfiles()`, `getfolders()`, `getopenfilejs()`, `getselectfilejs()`, `browser()` |
| `annotation` | Notes utilisateurs : `display($id_object, $id_record, $label)`, `getnb()` |
| `subscription` | Abonnements/notifications : `display()`, `subscribed()`, `getusers()`, `notify()` |
| `share` | Partage d'un enregistrement : `selectusers()`, `add()`, `get()` |
| `validation` | Circuit de validation : `selectusers()`, `add()`, `get()`, `remove()` |
| `ticket` | Messagerie interne : `send()`, `getnew()`, `selectusers()` |
| `mail` | `mail::send($from, $to, $subject, $body, …)` (PEAR Mail) |
| `cache` | `new cache($id, $lifetime)` + `start()`/`end()` (sortie) ou `get_var()`/`save_var()` |
| `fs` / `fs_s3` | Système de fichiers : `copydir()`, `deletedir()`, `makedir()`, `downloadfile()`, `getmimetype()` |
| `image`, `mimethumb` | Traitement d'images, vignettes de documents |
| `odf_*`, `uno_converter` | Génération/conversion de documents ODF/PDF |
| `calendar`, `calendarEvent` | Calendriers et évènements (iCal) |
| `barchart` | Graphiques |
| `security` | `filtervar()`, `checkpasswordvalidity()`, `generatepassword()` |
| `ip` | Règles de filtrage IP |
| `tag` | Étiquettes |
| `serializedvar` | Stockage sérialisé en base (utilisé par `display_array`) |

**Les triplets `(id_object, id_record, id_module)`** sont la clé de raccordement entre un enregistrement
métier et ces services : `id_object` = constante `OBJECT_*` du module, `id_record` = clé primaire.

---

## 8. Sécurité — règles non négociables

1. **URLs internes** : toujours `ploopi\crypt::urlencode()` / `queryencode()`. Elles portent le jeton
   anti-CSRF (`_PLOOPI_TOKEN`) et le contexte `ploopi_env`.
2. **Affichage** : toute donnée issue de la base ou de l'utilisateur passe par
   `ploopi\str::htmlentities()`. Pour du HTML riche : `ploopi\str::htmlpurifier()`.
3. **SQL** : placeholders `%s`/`%d` du query builder, ou `$db->addslashes()`, ou cast entier.
   Ne jamais concaténer une variable brute.
4. **Droits** : `ploopi\acl::isactionallowed()` en tête de **chaque** action, y compris les `op_*`
   (elles sont appelables directement en HTTP).
5. **Paramètres d'URL** : valider (`is_numeric`, `preg_match`) avant usage — `security::filtervar()`
   nettoie mais ne type pas.
6. **Redirections** : `output::redirect()` bloque les URL externes sauf `$trusted = true`.
7. **Uploads** : respecter `_PLOOPI_MAXFILESIZE`, passer par `documents::savefile()`.
8. Le contrôleur nettoie déjà `entity`/`action` (`[a-z0-9/_]`), mais **pas** les autres paramètres.

---

## 9. JavaScript

Les fichiers de `js/` sont concaténés puis minifiés en `js/functions.pack.js` par `compress.sh`
(`aa_init.js` doit rester **premier** : il crée l'objet `ploopi = {}`).

```js
// Requêtes
ploopi.xhr.send(url, data, asynchronous, getxml, method)
ploopi.xhr.todiv(url, data, idDiv, method)
ploopi.xhr.tocb(url, data, callback, ticket, getxml, method)
ploopi.xhr.topopup(width, e, id, url, data, method)
ploopi.xhr.submit(form, idDiv, beforesubmit)
ploopi.xhr.ajaxloader(id)

// Popups
ploopi.popup.show(content, w, e, centered, id, posx, posy)
ploopi.popup.hide(id) / ploopi.popup.hideall() / ploopi.popup.move(...) / ploopi.popup.ize(...)

// Tableaux skin
ploopi.skin.array_refresh(arrayId, orderBy, page, callback)
ploopi.skin.treeview_shownode(nodeId, query, script)

// Divers
ploopi.openwin / confirmform / confirmlink / switchdisplay / innerHTML / get_param / insertatcursor
ploopi.event.dispatch_onchange(id) / dispatch_onclick(id)
ploopi.validatefield(label, obj, type) / ploopi.validatereset(form)
ploopi.documents.* / ploopi.annotations.*
```

Variables globales injectées par `include/javascript.php` : **`_PLOOPI_ENV`**, **`_PLOOPI_BASEPATH`**,
et le tableau `lstmsg[]` des messages de validation.

Pattern AJAX standard depuis une vue :

```php
onclick="ploopi.xhr.todiv('admin-light.php',
    '<?php echo ploopi\crypt::queryencode('entity=admin&action=op_delete&id='.$id); ?>',
    'mon_div');"
```

ou, en passant l'environnement courant :

```js
ploopi.xhr.send('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&entity=admin&action=op_x', false)
```

**Ressources JS/CSS d'un module** : elles sont déclarées automatiquement par `module::init()` si elles
existent — `include/functions.js`, `include/styles.css`, `include/styles_ie.css`,
`include/javascript.php` (inline), `include/head.php` (balises `<head>`). Ne pas les inclure à la main.

---

## 10. Templates

Moteur maison (`lib/template/template.php`), syntaxe phpBB :

```php
$tpl = new Template('./templates/frontoffice/'.$template_name);
$tpl->set_filenames(['body' => 'index.tpl']);
$tpl->assign_vars(['TITRE' => …]);
$tpl->assign_var('X', $v);
$tpl->assign_block_vars('articles', ['ID' => …]);        // ouvre une itération
$tpl->assign_block_vars('articles.tags', ['LABEL' => …]); // bloc imbriqué
$tpl->pparse('body');
```

Dans le `.tpl` : `{VARIABLE}`, `<!-- BEGIN bloc --> … <!-- END bloc -->`,
`<!-- INCLUDE fichier.tpl -->`, `{bloc.VARIABLE}`.

Blocs alimentés par le socle et disponibles dans `index.tpl` / `light.tpl` :
`ploopi_js`, `module_js`, `module_css`, `module_css_ie`, `switch_user_logged_in`,
`switch_user_logged_out`, `switch_blockmenu`, plus les variables `PAGE_CONTENT`,
`ADDITIONAL_HEAD`, `ADDITIONAL_JAVASCRIPT`, `TEMPLATE_PATH`, `WORKSPACE_*`, `PLOOPI_VERSION`…

---

## 11. Intégration frontoffice

Le frontoffice **est** le module `webedit` : `include/frontoffice.php` fait
`ploopi\module::init('webedit', false, false, false)` puis inclut `modules/webedit/display.php`.
Un module s'y intègre par trois points d'extension.

### 11.1 `modules/<mod>/template.php`

Inclus par webedit **pour chaque instance active** de module de l'espace, afin d'alimenter le template
frontoffice (blocs latéraux, bandeaux…).

- Variable disponible : **`$template_moduleid`** (id de l'instance)
- Objet disponible : `$template_body` (le `Template` courant)
- Convention : alimenter des blocs `sw_<module>[_<specialid>]`

### 11.2 `modules/<mod>/template_content.php`

Inclus quand une page de contenu demande explicitement l'affichage du module comme **contenu principal**
(`index.php?template_moduleid=<id>`). Même variable `$template_moduleid`.

### 11.3 `modules/<mod>/wce.php` — objets insérables (Web Content Editor)

Un objet WCE est déclaré dans `mb.xml` (`<ploopi_mb_wce_object>`) :

```xml
<row>
    <id>1</id>
    <label>Affichage Galerie</label>
    <script>?entity=front&amp;action=op_display</script>
    <select_id>id</select_id>            <!-- optionnel : choix d'un enregistrement précis -->
    <select_label>label</select_label>
    <select_table>ploopi_mod_nanogallery</select_table>
</row>
```

L'auteur insère l'objet dans un article webedit ; au rendu, webedit :

1. résout l'objet et construit `$obj` (`$obj['module_id']`, `$obj['module_type']`, `$obj['object_id']`)
2. **`eval()`** chaque `clé=valeur` du champ `script` en variables PHP locales
3. inclut `modules/<module_type>/wce.php`, dont la sortie devient le contenu de l'objet

Corollaire : le champ `<script>` est du code interprété — n'y mettre que des affectations littérales.

### 11.4 `modules/<mod>/include/rewrite.php`

Inclus par `loader::_rewrite()` pour toutes les requêtes passées par le rewriting Apache. Contrat :

```php
// $arrParsedURI est disponible (parse_url de l'URI nettoyée)
if (preg_match('/mon-motif\/(.*)\.html/', $arrParsedURI['path'], $m)) {
    $_REQUEST['monid'] = $_GET['monid'] = $m[1];
    $booRewriteRuleFound = true;         // ★ indispensable, sinon 404
}
```

⚠️ `webedit/include/rewrite.php` positionne une variable `$ploopi_access_script` (`index-light`,
`backend`…) : c'est un **reliquat sans effet**, `loader::_rewrite()` ne la lit pas (le script est stocké
dans la propriété privée `loader::$script`). Ne pas s'en inspirer pour un nouveau module.

---

## 12. Internationalisation

- Cœur : `lang/french.php` (défaut) + `lang/<langue>.php`
- Module : `modules/<mod>/lang/french.php` puis `modules/<mod>/lang/<langue>.php`, chargés par
  `module::init()` ; la langue vient de `$_SESSION['ploopi']['modules'][1]['system_language']`
- Les libellés sont des **constantes PHP** préfixées, en MAJUSCULES :
  `_PLOOPI_*` pour le cœur, `_SYSTEM_*` pour le module system, `_<MODULE>_*` pour un module

```php
define('_NEWS2_LABEL_TITLE', 'Titre');
```

En pratique, `news2` et `nanogallery` écrivent les libellés en dur en français dans les vues. Pour un
module destiné à être diffusé, préférer les constantes.

---

## 13. Configuration (`config/config.php`)

Généré à l'installation depuis `config/config.php.model`, structuré par `switch($http_host)` pour
permettre plusieurs configurations sur un même code. Constantes notables :

```
_PLOOPI_DB_SERVER / _LOGIN / _PASSWORD / _DATABASE   Connexion MySQL
_PLOOPI_PATHDATA / _PATHCACHE / _PATHSHARED          Chemins physiques
_PLOOPI_USE_CACHE                                    Cache interne
_PLOOPI_MAXFILESIZE                                  Taille max d'upload
_PLOOPI_SESSIONTIME / _SESSION_HANDLER               php | db | file | memcached
_PLOOPI_MEMCACHED_SERVER / _PORT
_PLOOPI_ELASTICSEARCH_HOST
_PLOOPI_DISPLAY_ERRORS / _ERROR_REPORTING / _MAIL_ERRORS / _ADMINMAIL
_PLOOPI_TOKEN / _TOKENTIME / _TOKENMAX               Jetons anti-CSRF
_PLOOPI_FRONTOFFICE / _FRONTOFFICE_REWRITERULE       Frontoffice et règles de rewriting
_PLOOPI_DEFAULT_TEMPLATE
_PLOOPI_USE_COMPLEXE_PASSWORD / _COMPLEXE_PASSWORD_MIN_SIZE
_PLOOPI_MAX_CONNECTION_ATTEMPS / _JAILING_TIME
_PLOOPI_ACTIVELOG
```

---

## 14. Conventions de code

- **En-tête GPL obligatoire** en tête de chaque fichier PHP (copier depuis un fichier existant)
- **Bloc PHPDoc** avec `@package`, `@subpackage`, `@copyright`, `@license`, `@author`
- **Notation hongroise** sur les variables : `$str…` (string), `$int…`, `$boo…` (bool), `$arr…`,
  `$obj…`, `$res…`/`$rsc…` (ressource), `$mix…`
- Indentation **4 espaces** (jamais de tabulation — cf. `clean.sh`)
- Accolade ouvrante sur sa propre ligne pour les classes/fonctions
- Constantes globales préfixées `_PLOOPI_` / `_SYSTEM_` / `_<MODULE>_`
- Un fichier = une classe, en minuscules, sans underscore superflu
- `use ploopi;` + appels qualifiés `ploopi\str::…` dans les modules ; alias `use ploopi\<module>\tools;`
- Sortie HTML : alterner PHP/HTML avec `?> … <?php` plutôt que de concaténer de longues chaînes
- Ne pas introduire de dépendance Composer sans nécessité forte

---

## 15. Workflow de développement

```sh
./build.sh          # lint PHP sur tout l'arbre (n'affiche que les erreurs)
./clean.sh          # normalisation des espaces/tabs/lignes vides
./compress.sh       # minification + gzip des js/css (nécessite yuicompressor + java)
./phpdox.sh         # documentation API → doc/api
./create_redist.sh  # paquet de redistribution (tar.bz2 + shasum)
```

**Boucle de travail sur un module :**

1. Modifier les sources dans **`install/<module>/files/…`**
2. Déployer vers `modules/<module>/` (copie, lien symbolique, ou réinstallation via le backoffice)
3. Vérifier : `php -l` sur les fichiers touchés (ou `./build.sh`)
4. Si le schéma change : ajouter `install/<module>/update/update_<nouvelle_version>.sql`,
   incrémenter `<version>` dans `description.xml`, mettre à jour `mb.xml` et `changelog.txt`
5. Si des JS/CSS changent : régénérer les `.gz` (`compress.sh`)
6. Reconnexion ou `?reloadsession` pour recharger session/paramètres après un changement de
   `description.xml`

Il n'y a **pas de suite de tests automatisés** dans ce dépôt. La validation est manuelle : lint PHP +
parcours fonctionnel dans le backoffice.

---

## 16. Checklist — créer un nouveau module

```
install/monmodule/
├── description.xml               label/version/author/date/description + paramtype + action
├── structure.sql                 ploopi_mod_monmodule_* (+ id_module, id_workspace, id_user)
├── mb.xml                        mb_object, mb_table, mb_field, mb_schema, mb_relation, mb_wce_object
├── changelog.txt
├── update/                       (vide au départ)
└── files/
    ├── classes/controller.php    namespace ploopi\monmodule; class controller extends ploopi\controller
    ├── classes/monmodule.php     extends ploopi\data_object
    ├── classes/tools.php         abstract, const ACTION_* / OBJECT_* + helpers statiques
    ├── actions/home/default.php  redirige vers l'entité réelle
    ├── actions/error/default.php message d'erreur
    ├── actions/forbidden/default.php message de non-autorisation
    ├── actions/public/_header.php + default.php
    ├── actions/admin/_header.php  (contrôle ACL + onglets) + default.php + write.php + op_write.php
    ├── include/global.php        constantes/fonctions
    ├── include/styles.css, include/functions.js
    ├── lang/french.php
    └── img/
```

À vérifier avant de considérer le module terminé :

- [ ] `id_action` de `description.xml` == constantes `ACTION_*` de `tools.php`
- [ ] Chaque action (vue **et** `op_*`) contrôle ses droits et redirige vers `entity=forbidden`
- [ ] Toutes les URL internes passent par `crypt::urlencode()` / `queryencode()`
- [ ] Toutes les requêtes filtrent sur `id_module` **et** `id_workspace IN (viewworkspaces())`
- [ ] Tout affichage de donnée passe par `str::htmlentities()`
- [ ] Les créations/modifications/suppressions appellent `user_action_log::record()`
- [ ] `setBlock()` construit le menu et respecte les ACL
- [ ] `include/create.php` / `include/delete.php` si l'instance a des données propres
- [ ] Indexation (`search_index::add/remove`) si le contenu doit être cherchable
- [ ] `php -l` propre sur tous les fichiers

---

## 17. Pièges connus

| Piège | Détail |
|---|---|
| En-tête/pied de module | `dispatch()` teste `actions/_header.php` mais inclut `header.php` — utiliser le niveau entité (`actions/<entity>/_header.php`) |
| `<cms_object>` dans `description.xml` | Instancie `ploopi\mb_cms_object`, classe inexistante → **fatal**. Déclarer les objets WCE dans `mb.xml`. |
| Mode light | Pas de `_footer.php`, `system::kill()` implicite en fin d'action : tout code après l'`include` de l'action est ignoré |
| `$_SESSION['ploopi']['modules'][$id]` | Contient à la fois les colonnes de `ploopi_module` **et** les paramètres du module : attention aux collisions de noms de paramètres (`label`, `active`, `visible`, `public`, `shared`, `viewmode`…) |
| Session obsolète | Après modification des paramètres/actions d'un module, ajouter `reloadsession` à l'URL ou se reconnecter |
| `open()` sur data_object | Retourne `false` si absent **mais** laisse `$this->fields` à `false` : toujours tester le retour |
| Insert sans `init_description()` | `save()` n'insère que les champs présents dans `fields` ; sans `init_description()` les colonnes NOT NULL sans défaut échouent |
| `_PLOOPI_MODULE_SYSTEM` = 1 | L'instance système est traitée à part dans de nombreux tests |
| Deux jeux de fichiers | `install/<mod>/files/` (source) vs `modules/<mod>/` (déployé, non versionné) : modifier la source |
| `*.sh`, `modules/`, `*.sql` gitignorés | Les ajouts dans ces chemins nécessitent `git add -f` |
| Encodage des scripts shell | `compress.sh`, `clean.sh`, `build.sh` contiennent des commentaires en latin-1 : ne pas les réencoder sans raison |
