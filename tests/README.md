# Suite de tests

Deux familles de contrôles cohabitent ici.

**Vérification des correctifs de sécurité** (suites `01` à `06`) : des propriétés précises —
un contrôle d'accès refuse, un entête est posé, une valeur est échappée — éprouvées sur une
instance réellement installée.

**Tests unitaires du socle** (suites `07` à `14`) : le comportement des classes utilitaires,
du constructeur de requêtes, de l'ORM, du moteur de template, et la cohérence des paquets
d'installation. Ils ne demandent que PHP en ligne de commande.

Pas de dépendance Composer, pas de framework : uniquement PHP en ligne de commande, `curl` et
MySQL. Le harnais tient dans `tests/bootstrap.php`.

## Lancer les tests

Cinq niveaux, du plus immédiat au plus complet :

| Niveau | Commande | Demande | Sert à |
|---|---|---|---|
| Suites unitaires | `tests/run.sh` | PHP | Boucle courte, quelques secondes : classes du socle, conventions, paquets. Les suites qui exigent une instance sont « non évaluées » |
| Suite complète | `tests/instance.sh test` | PHP, client et serveur MySQL, `curl` (+ Node et Playwright avec `--navigateur`) | Avant de pousser : instance jetable installée par l'assistant, toutes les suites |
| Docker | `tests/docker.sh 8.x` ou `tout` | Docker seul | La suite complète sur une version précise de PHP, ou les cinq comme la CI, sans rien installer d'autre ; `--publier` affiche le résultat sur le commit dans GitHub, sans minute de CI |
| CI | GitHub, *Actions* → *Tests* → *Run workflow* | — | À la demande : matrice PHP 8.1 à 8.5, `composer audit`, PHPCompatibility |

Chaque passage de `tests/run.sh` (et donc des deux suivants) écrit ses résultats dans
`tests/resultats/<version de PHP>/` (voir [Résultats](#résultats)).

## Prérequis

Les suites unitaires n'en ont aucun : `tests/run.sh` fonctionne sur une copie de travail
fraîchement clonée, sans Ploopi installé.

Les suites qui éprouvent une instance demandent en plus :

- une instance Ploopi **installée et en fonctionnement**, servant ce dépôt ;
- l'extension `curl` côté CLI ;
- un accès à la base de données de cette instance ;
- un compte administrateur système.

À défaut, elles se déclarent « non évaluées » — jamais réussies. L'instance doit servir **le
code de ce dépôt** : plusieurs contrôles comparent le comportement observé au code source.

## Mise en route

Pour les seules suites unitaires, rien à préparer :

```sh
tests/run.sh
```

Pour la suite complète, il faut une instance installée et servie. Le plus simple est de
laisser le script en monter une, jetable :

```sh
tests/instance.sh test
```

Il crée une base dédiée, déroule l'installation par l'assistant, démarre le serveur PHP
intégré, écrit `tests/config.php`, lance toutes les suites, puis supprime bases,
configuration et serveur. Une commande, aucune trace.

Il refuse de démarrer si `config/config.php` existe : un dépôt qui sert déjà une instance
n'est jamais touché.

| Commande | Effet |
|---|---|
| `tests/instance.sh test` | monte, teste, démonte — l'usage courant |
| `tests/instance.sh up` | monte l'instance et la laisse en service |
| `tests/instance.sh run` | lance la suite sur l'instance montée |
| `tests/instance.sh down` | démonte et nettoie |
| `tests/instance.sh status` | état courant |

Réglages par variable d'environnement : `PLOOPI_TEST_PORT` (8099), `PLOOPI_TEST_DB`,
`PLOOPI_TEST_DB_HOST`, `PLOOPI_TEST_DB_ADMIN` et `PLOOPI_TEST_DB_ADMINPWD` — le compte
MySQL autorisé à créer une base —, et `PLOOPI_TEST_DB_USER_HOSTS`, les hôtes autorisés
pour le compte de test (`localhost 127.0.0.1` par défaut). Prérequis : `php` en ligne de commande, `curl`, le client
`mysql`, un serveur MySQL/MariaDB joignable, et `vendor/` installé par Composer.

Stockage S3 : avec `PLOOPI_TEST_S3_ENDPOINT`, l'instance range les documents de la GED du
socle dans un dépôt S3 plutôt que sous `data/` (suite 42). Le serveur n'est pas fourni ;
n'importe quel service compatible convient, par exemple
[moto](https://docs.getmoto.org/) (`pip install 'moto[server]'`), qui accepte tous les
identifiants :

```sh
moto_server -H 127.0.0.1 -p 9000 &
PLOOPI_TEST_S3_ENDPOINT=http://127.0.0.1:9000 tests/instance.sh test
```

Réglages associés : `PLOOPI_TEST_S3_REGION` (`us-east-1`), `PLOOPI_TEST_S3_BUCKET`
(`ploopi-tests`, créé s'il manque), `PLOOPI_TEST_S3_KEY` et `PLOOPI_TEST_S3_SECRET`.

Pour éprouver une instance qui vous appartient plutôt qu'une instance jetable :

```sh
cp tests/config.php.model tests/config.php
$EDITOR tests/config.php
tests/run.sh
```

`tests/config.php` n'est pas versionné : il porte des identifiants. Il est **facultatif** —
sans lui, seules les suites unitaires sont évaluées.

## Intégration continue

`.github/workflows/tests.yml` lance la suite complète sur GitHub Actions, pour chaque
version de PHP prise en charge (8.1 à 8.5), sur déclenchement manuel uniquement (onglet
*Actions* → *Tests* → *Run workflow*). PHP 8.0 en est absent : toutes les versions d'`aws/aws-sdk-php` qui
l'acceptent sont visées par un avis de sécurité élevé, que Composer refuse d'installer. Chaque version tourne dans son propre job, avec un service MariaDB 10.11 ;
une version en échec n'interrompt pas les autres. En cas d'échec, les journaux du
serveur (`data/instance_tests.log`) et de Ploopi (`/tmp/ploopi_error.log`) sont joints
au passage en artefact.

Le service MariaDB voit arriver les connexions depuis le réseau des conteneurs, pas
depuis `127.0.0.1` : le workflow fixe `PLOOPI_TEST_DB_USER_HOSTS` à
`localhost 127.0.0.1 %`, qui ouvre le compte de test à tout hôte. Ne pas reprendre ce
réglage sur un serveur partagé.

Il n'y a pas de `composer.lock` : les dépendances sont résolues pour chaque version de
PHP, ce qui vérifie aussi qu'une résolution existe pour chacune.

Depuis la version 2.9, Composer refuse d'installer un paquet visé par un avis de
sécurité. Aucun avis n'est accepté (`config.policy.advisories` absent) : depuis le
remplacement de CKEditor 4 par TinyMCE (feuille de route, §3.4 et §3.28), tout avis
bloque l'installation.

## Les suites

| Suite | Contenu | Effets de bord |
|---|---|---|
| `01_http` | Parcours réels : connexion, jeton anti-CSRF, contrôle d'accès de la GED, téléchargements, annuaire, déconnexion | Crée puis supprime un utilisateur `cible` et des documents de test ; modifie puis restaure l'adresse de l'administrateur |
| `02_unit` | Classes du socle chargées en CLI : `cipher`, `security`, `user`, `fs`, `output`, `inputfilter`, `documents` | Aucun |
| `03_sources` | Modèle de configuration, sources de l'assistant d'installation, échappements SQL et shell exécutés | Crée puis supprime une table `ploopi_tests_sqli` |
| `04_installation` | Dépôt réel d'un fichier, puis **installation neuve déroulée par l'assistant** | **Destructive**, voir ci-dessous |
| `05_modules` | Wiki : rendu d'une page, assainissement des URL | Aucun |
| `06_optoken` | Jeton anti-CSRF sur les opérations de module, et dérogation `publicops.php` | Crée puis retire un module jetable |
| `07_str` | `str` : coupe, accents, URL, échappement HTML/XML, indexation, HTMLPurifier | Aucun |
| `08_date` | `date` : format pivot `YmdHis`, conversions locales, fuseaux, jours fériés | Aucun |
| `09_arr` | `arr` : exports JSON/CSV/HTML/XML, clés, pagination | Aucun |
| `10_sql` | `sqlformat`, `query_*`, `db` : échappement, puis exécution réelle avec charge d'injection | Tables `ploopi_tests_*`, supprimées |
| `11_data_object` | ORM : cycle de vie, `setvalues`, `setuwm`, collections, clé composite | Tables `ploopi_tests_*`, supprimées |
| `12_template` | Moteur de template, et rendu des 52 `.tpl` du dépôt | Aucun |
| `13_conventions` | Lint de tout l'arbre, en-tête GPL, tabulations, nommage, autoload, UTF-8 ; aucun reste de Prototype ni ancienne fonction `ploopi_*` dans le PHP, le JS et les gabarits | Aucun |
| `14_paquets` | Paquets `install/` : XML, versions, `id_action`, exécution des `structure.sql` | Tables du module, supprimées |
| `15_module_doc` | GED : les trois opérations dispensées de jeton, cloisonnement des accès | Déploie puis retire le module `doc` |
| `16_module_wiki` | Wiki : droits déclarés appliqués aux opérations, pages verrouillées | Déploie puis retire le module `wiki` et un utilisateur sans rôle |
| `17_module_webedit` | Webedit : rendu frontoffice, rubriques privées, fenêtre de publication, cloisonnement des sites | Déploie puis retire le module ; bascule l'espace 2 en frontoffice le temps de la suite |
| `18_module_forms` | Formulaires : droits déclarés appliqués aux traitements, cloisonnement des instances | Déploie puis retire le module `forms`, deux utilisateurs et un rôle |
| `19_module_forms_frontoffice` | Formulaire public soumis par un visiteur anonyme depuis une page webedit ; jeton exigé en backoffice | Déploie puis retire `webedit` et `forms` ; bascule l'espace 2 en frontoffice le temps de la suite |
| `20_module_forms_graphiques` | Graphiques de formulaires rendus par formsChart (GD) : PNG valide, tracé, couleurs ; suppression d'un formulaire | Déploie puis retire le module `forms` |
| `21_module_webedit_frontoffice` | Webedit côté visiteur : sitemap.xml, flux RSS/Atom ; abonnement par double opt-in (courriel de confirmation, liens signés, échéance, ancien lien md5 refusé) ; commentaires publics retirés ; caches propres à chaque site ; adresse du nuage 3D ; rien d'une rubrique privée dans le plan du site ni les flux ; page d'étiquette cloisonnée par site ; injection SQL par le nuage d'étiquettes 3D | Déploie puis retire `webedit` ; bascule l'espace 2 en frontoffice le temps de la suite |
| `22_journal` | Journal des actions en mode SQL strict : connexions et échecs journalisés, module et espace repris de la session, valeurs trop longues tronquées | Entrées de test supprimées |
| `23_module_directory` | Annuaire : droits sur rubriques, contacts partagés et personnels, listes de favoris ; frontoffice (fiche, recherche, photos) | Déploie puis retire `directory` et `webedit`, deux utilisateurs ; bascule l'espace 2 en frontoffice le temps de la suite |
| `24_module_booking` | Reservation : droits sur types de ressources, ressources, sous-ressources et demandes (suppression, deverrouillage, validation) | Deploie puis retire `booking`, un utilisateur |
| `25_module_planning` | Planning : droits sur la modification, le déplacement et la suppression d'événements, cloisonnement des instances, y compris dans la recherche | Déploie puis retire `planning`, un utilisateur |
| `26_module_dbreport` | Requêtes : lecture et exécution limitées à l'instance et aux espaces visibles, modification et suppression avec cloisonnement et verrou ; accents des graphiques sans double encodage, exports annoncés en UTF-8 | Déploie puis retire `dbreport`, deux utilisateurs et un rôle |
| `27_modules_news2_nanogallery` | News et galeries : cloisonnement des instances sur les opérations, fiche d'une news limitée aux news publiées de l'instance ; titres d'images enregistrés pour la seule galerie éditée ; liste des news (contenu purifié, liens filtrés) ; galeries sans spcolor, enregistrées et affichées | Déploie puis retire `news2` et `nanogallery`, deux utilisateurs et deux rôles |
| `28_module_booking_frontoffice` | Planning public de booking dans un article webedit : rendu, navigation AJAX sans jeton, ressource sans espace gestionnaire, identifiants de ressources typés | Déploie puis retire `webedit` et `booking` ; bascule l'espace 2 en frontoffice le temps de la suite |
| `29_module_system` | Module system : cloisonnement inter-espaces (rôles affectables, utilisateur modifiable, groupe renommable, arbre de navigation), niveau d'administration exigé pour system_delete_user, updatemb et l'import d'utilisateurs | Crée et supprime un espace, des utilisateurs et un groupe |
| `31_pathdata` | Aucun fichier déposé sous `_PLOOPI_PATHDATA` n'est servi directement : le routeur de test reproduit l'interdiction du `.htaccess_modele` ; l'assistant propose un dossier hors racine web par défaut | Dépose et supprime un document de la GED |
| `32_csp` | CSP Report-Only : entête posée sur les pages servies avec report-uri du point de collecte, opération `ploopi_csp_report` joignable sans session ni jeton, rapport journalisé (directive, document, source), rapport vide ignoré, rapport forgé ramené à une ligne bornée | Écrit quelques lignes dans le journal d'erreurs de l'instance |
| `33_icons_sans_inline` | Migration P2 des `onclick` : rendu réel des icônes (`data-url`, `data-confirm`), un seul attribut `class` par en-tête triable, `functions.pack.js` = sources de `js/`, `.gz` à jour | Aucun (unitaire) |
| `34_session_handler` | Gestionnaire de session interne (`db`) : page servie, connexion, session stockée en base | Bascule `config/config.php` le temps de la suite, restauré à la sortie |
| `35_motdepasse_oublie` | Mot de passe oublié : code de confirmation périmé refusé et supprimé, lien jamais bâti sur un hôte non déclaré (`_PLOOPI_PUBLIC_URL`) | Bascule `config/config.php` et les domaines de l'espace 2 le temps de la suite, restaurés à la sortie |
| `37_ploopi4_gabarit` | Gabarit `ploopi4` : contrat avec le socle et les modules, rien en ligne, sprite d'icônes à jour et complet, agrégat CSS, exclusion de l'interface `.p4-ui` par la zone historique, `block::addmenu` (icône, section), `skin::version/icon/moduleicon/getdisplayprefs`, paramètres d'affichage en base | Aucun (unitaire) |
| `38_ploopi4_system` | Shell `ploopi4` et écran refondu des espaces de travail servis, préférences d'affichage (valeur hors liste, jeton), fiche réservée aux utilisateurs administrables, détachement groupé, export d'une sélection, écran historique sous `ploopi3` | Bascule l'espace 2 en `ploopi4` le temps de la suite ; crée puis retire un espace, un groupe et des utilisateurs |
| `40_flux` | `ploopi\feed` (flux RSS 2.0 et Atom 1.0, qui remplace FeedWriter) : XML bien formé, valeurs échappées, caractères interdits et UTF-8 invalide retirés, identifiants Atom identiques à ceux de FeedWriter, plus de FeedWriter dans les flux de webedit et de la GED | Aucun (unitaire) |
| `41_demo` | Site de démonstration (*Système → Modules*, `ploopi\system\demo`) : bouton proposé sur une instance vierge seulement, webedit installé par la procédure habituelle, site public servi par le gabarit `corporate` avec recherche, bouton retiré et opération sans effet une fois un module installé | Instance vierge (sinon non évaluée) ; destructive, remet tout en état |
| `42_documents_stockage` | Stockage des documents de la GED du socle, local ou S3 selon l'instance : dépôt par le formulaire (envoi multipart), téléchargement (pièce jointe, `nosniff`, aucune copie temporaire laissée), archive zip, remplacement par un fichier d'une autre extension et renommage (l'ancien fichier ne reste pas orphelin), suppression ; le stockage est lu directement (dossier, ou dépôt par le SDK) | Instance ; S3 si `PLOOPI_TEST_S3_ENDPOINT` (voir plus haut) |
| `99_obsolescences` | Passée en dernier : aucune obsolescence (`E_DEPRECATED`) levée par le code de Ploopi dans le journal d'erreurs de l'instance depuis son montage, installation et suites comprises ; celles de `vendor/` sont listées sans faire échouer. Sous PHP 8.5, c'est l'annonce des erreurs de PHP 9 | Aucun (lit le journal) |

Une suite s'exécute seule :

```sh
tests/run.sh 02_unit
```

## La suite 04 est destructive

Elle retire temporairement `config/config.php`, laisse l'assistant créer une base, la vérifie
puis la supprime, et restaure la configuration d'origine. **Ne jamais l'exécuter sur une
instance de production.**

Trois verrous :

- elle ne s'exécute que si `allow_destructive` vaut `true` dans `tests/config.php`, valeur
  fausse par défaut — sinon le contrôle est signalé « non évalué » ;
- elle refuse de démarrer si `install_db` est égal à `db_name` ;
- `config/config.php` est sauvegardé avant retrait et restauré par une fonction d'arrêt, donc
  y compris en cas d'erreur fatale.

Le reste de la suite 04 — le dépôt d'un fichier — s'exécute dans tous les cas.

## Parcours navigateur

`tests/navigateur/` éprouve le backoffice dans un vrai navigateur (Playwright, Chromium),
en option :

```sh
tests/instance.sh test --navigateur   # suite complète, puis le parcours
tests/instance.sh navigateur          # sur une instance déjà montée (up)
```

Le parcours installe d'abord tous les modules de `install/` et en crée une instance
(`preparer.php`, par les procédures du module system : site de démonstration, puis
`moduleinstaller`) ; l'instance n'est donc plus vierge, d'où son passage après la suite.
Puis `parcours.js` se connecte et suit le menu latéral de ploopi4 : chaque écran de
« Mon espace », de l'administration et de chaque module, et chacun de leurs onglets. Il
ouvre ensuite des fenêtres (visibles, dans l'écran, déplaçables, refermables) et les
éditeurs (TinyMCE dans news2, jsToolbar dans le wiki).

Trois passes (`PLOOPI_NAV_PASSES`, défaut `clair,sombre,telephone`) : 1440 px en thème clair
puis sombre (le thème est vérifié écran par écran), 390 px (débordement horizontal). Un
écran est en défaut sur une réponse HTTP en erreur, une ressource introuvable, une erreur
JavaScript, une erreur PHP (journal de Ploopi, journal du serveur, message affiché). Aucune
écriture : les liens de suppression, désinstallation, réindexation… et ceux qui demandent
confirmation ne sont jamais suivis.

Prérequis : Node.js et le module `playwright` (cherché dans `npm root -g`, ou
`PLAYWRIGHT_MODULE`), avec Chromium (`npx playwright install chromium`). Les modules
déployés dans `modules/` pour le parcours sont retirés au démontage.

## Tests dans Docker

`tests/docker.sh` lance `tests/instance.sh test` dans un conteneur PHP de la version
demandée, avec une base MariaDB 10.11 jetable. Seul Docker est nécessaire.

```sh
tests/docker.sh 8.3                   # une version
tests/docker.sh tout                  # 8.1 à 8.5, l'une après l'autre, bilan à la fin
tests/docker.sh 8.5 --navigateur      # avec le parcours navigateur
tests/docker.sh tout --publier        # puis résultat publié sur le commit dans GitHub
```

- L'image `ploopi-tests:php<version>[-navigateur]` (`tests/docker/Dockerfile`) porte les
  extensions de la CI (`mysqli`, `gd`, `zip`, `curl`, `xml`, `mbstring`, `openssl`), Composer
  et le client MariaDB ; Node, Playwright et Chromium avec `--navigateur`. Construite au
  premier passage, puis reprise du cache.
- Le dépôt est monté en volume : on teste la copie de travail. `config/`, `data/` et
  `modules/` sont temporaires dans le conteneur : un `config/config.php` existant n'est ni
  lu ni modifié, et `modules/` ne reçoit que `system` ; les suites déploient les autres
  modules depuis `install/`, jamais depuis une copie ancienne laissée dans `modules/` (sur
  l'hôte, `tests/instance.sh` réutilise une telle copie : la retirer avant de tester).
  `vendor/` aussi : les dépendances sont installées par `composer install` dans le volume
  `ploopi-tests-vendor`, monté à sa place (cache dans `ploopi-tests-composer`). Le `vendor/`
  du dépôt, quel que soit son état, n'est ni lu ni modifié.
- Le `docker-compose.yml` du dépôt (déploiement) n'est pas utilisé.
- Démon Docker injoignable : le script affiche l'erreur de Docker, dit si un démon tourne
  sans répondre ou si aucun ne tourne, et propose de le (re)démarrer (`systemctl restart
  docker`, sinon arrêt puis relance de `dockerd`, Docker Desktop sous macOS). Il ne
  propose rien quand l'accès au socket est refusé (utilisateur hors du groupe `docker`).
  Il propose aussi de retirer les conteneurs `ploopi-tests-*` restés d'un passage
  interrompu. Les questions ne sont posées que dans un terminal.
- `--publier` : résultat publié comme statut du commit sur GitHub, voir
  [Publier les résultats sur GitHub](#publier-les-résultats-sur-github).
- Lancer le script sans `sudo` : l'utilisateur doit appartenir au groupe `docker`
  (`sudo usermod -aG docker "$USER"`, puis rouvrir la session ou `newgrp docker`). Sinon le
  script le signale (« Accès refusé au démon »).
- Réseau qui intercepte le HTTPS (proxy d'entreprise) : `PLOOPI_TEST_CA_BUNDLE=<autorité.pem>`
  pour la construction ; un `HTTPS_PROXY` de l'hôte est transmis.

## Publier les résultats sur GitHub

`tests/docker.sh … --publier` pose, à la fin du passage, un statut par version sur le commit
testé (contexte `tests-docker/php8.1` … `tests-docker/php8.5`, description « 1996 contrôles,
0 échec(s)… »). Il s'affiche sur la pull request comme une CI, sans consommer de minutes
GitHub Actions. C'est un résultat déclaré par la machine qui a lancé les tests, pas
recalculé par GitHub.

### Ce qu'il faut

1. **Un accès à GitHub pour l'utilisateur qui lance le script**, au choix :
   - **`gh` connecté** (le plus simple) :
     - installation : `sudo apt install gh` (Ubuntu 22.04 et suivantes). Sur Ubuntu 20.04,
       absent des dépôts : dépôt officiel, voir https://github.com/cli/cli#installation ;
       macOS : `brew install gh` ;
     - connexion, une fois : `gh auth login`, réponses *GitHub.com*, protocole au choix
       (sans effet sur la publication : le même que `git remote get-url origin`), *Login
       with a web browser*. Vérifier avec `gh auth status` (scope `repo`) ;
     - si `gh` ne peut pas ouvrir de navigateur (session root, machine distante), il reste
       bloqué : lancer `BROWSER=echo gh auth login --web`, ouvrir soi-même
       https://github.com/login/device, saisir le code, puis cliquer sur « Authorize ».
   - **ou un jeton** : GitHub, Settings → Developer settings → Personal access tokens →
     *Fine-grained tokens*, dépôt `faf2019/ploopi`, permission *Commit statuses : Read
     and write* seulement ; puis `export GITHUB_TOKEN=…` avant de lancer le script.
     `GITHUB_TOKEN` (ou `GH_TOKEN`) passe avant `gh`.

   La connexion `gh` et le jeton sont propres à l'utilisateur : après `su` ou `sudo`, root
   ne les a pas. Mieux vaut lancer le script en tant qu'utilisateur, membre du groupe
   `docker` (voir [Tests dans Docker](#tests-dans-docker)). À défaut, en root :
   `GITHUB_TOKEN=$(su <utilisateur> -c 'gh auth token') tests/docker.sh tout --publier`.
2. **Un commit poussé, sans modification de fichier suivi.** Le statut doit attester
   exactement le code publié : le script refuse avant les tests si un fichier suivi est
   modifié (les fichiers non suivis ne comptent pas, les suites les ignorent) ou si le
   commit n'est pas poussé, et ne publie rien si le commit change pendant le passage.

```sh
git push
tests/docker.sh tout --publier
```

### Badge du README (facultatif)

Le dépôt étant privé, ni GitHub ni shields.io ne lisent ses statuts : le badge est un
fichier JSON tenu dans un gist, que `--publier` met à jour et que shields.io affiche.

Le gist du badge est `faf2019/8992326b9a700fd3832d1f3ff85bb8cf` (fichiers `ploopi-tests.json` et `ploopi-dependances.json`, ce dernier créé au premier passage),
déjà affiché en tête de [`README.md`](../README.md). Pour le mettre à jour :

1. Donner le droit d'écrire les gists : `gh auth refresh -s gist`, ou pour un jeton
   *fine-grained*, ajouter la permission de compte *Gists : Read and write*.
2. Lancer sur le dernier commit de master : `tests/docker.sh tout --publier`. Ce gist est
   celui par défaut quand `origin` est `faf2019/ploopi` (ou `faf2019/ploopidev`) ; `PLOOPI_BADGE_GIST=` (vide)
   publie les statuts sans toucher au badge.

Pour un autre gist (fork du dépôt…) : créer un gist secret contenant un fichier
`ploopi-tests.json` (contenu quelconque, il est remplacé), passer son identifiant (fin de son
URL) dans `PLOOPI_BADGE_GIST` et l'URL `https://gist.githubusercontent.com/<compte>/<identifiant>/raw/ploopi-tests.json`
au badge `https://img.shields.io/endpoint?url=…` du README.

Les badges ne sont mis à jour que si le commit testé est le dernier de `origin/master` (après
`git pull`) : un passage sur une branche ne les change pas.

- `ploopi-tests.json` (tests) : « 1996 OK · php8.5 », « 3 échec(s) · php8.5 », ou pour
  `tout`, « OK · php8.1 à 8.5 » / « échec · php8.3 ».
- `ploopi-dependances.json` (dépendances) : résultat de `composer audit` dans le conteneur
  (`tests/resultats/<version>/composer-audit.json`), « aucune faille » en vert, « aucune
  faille · 2 abandonné(s) » en jaune, « 1 faille(s) » en rouge. Pour `tout`, celui de la
  dernière version.

Les badges de licence, de PHP et de base de données du README sont statiques : celui de PHP
est à corriger avec `composer.json` quand une version est ajoutée ou retirée.
shields.io le garde en cache quelques minutes. Le gist est lisible par qui a son URL : il ne
contient que ce décompte.

### Exiger les statuts avant fusion (facultatif)

Après une première publication, sur GitHub : Settings → Branches → règle de protection de
`master` → *Require status checks to pass before merging*, et cocher `tests-docker/php8.1` à
`tests-docker/php8.5` (ils n'apparaissent dans la liste qu'une fois publiés).

## Résultats

Chaque passage écrit dans `tests/resultats/<version de PHP>/` (non versionné, remplacé au
passage suivant de la même version ; autre dossier : `PLOOPI_TESTS_RESULTATS`) :

| Fichier | Contenu |
|---|---|
| `rapport.html` | Rapport autonome : bilan, détail des échecs, suites, contrôles non évalués, galerie des captures du parcours navigateur, journaux. Les captures y sont liées : garder le dossier entier |
| `junit.xml` | Rapport JUnit (une suite par fichier, un cas par contrôle ; le parcours en suite `navigateur`), lisible par les outils d'intégration continue |
| `sorties/<suite>.txt` | Sortie de chaque suite, sans couleurs |
| `navigateur/` | Captures par passe, `rapport.md`, `resultats.json` |
| `composer-audit.json`, `dependances.txt` | `tests/docker.sh` seulement : résultat de `composer audit` et son résumé pour le badge des dépendances |
| `journaux/` | Conservés au démontage de l'instance : `serveur.log` (serveur PHP intégré), `ploopi_erreurs.log` (part du journal d'erreurs écrite par cette instance), `courriels.txt` |

Le harnais enregistre chaque contrôle dans `controles.jsonl` quand `PLOOPI_TESTS_CONTROLES`
le demande (`tests/run.sh`), puis `tests/rapport.php` assemble les deux rapports.

## Lire le résultat

Chaque contrôle est `OK`, `KO`, ou `SKIP` lorsqu'un prérequis d'environnement manque. Les
contrôles sautés ne sont pas des échecs mais sont comptés séparément : c'est le cas des deux
contrôles qui lisent le fichier de session du serveur web si le dossier est introuvable,
auquel cas renseigner `session_path`.

De même, les contrôles qui lisent un courriel envoyé (confirmation d'abonnement de la
suite 21) demandent que le serveur consigne ses courriels au lieu de les expédier :
`tests/instance.sh` lance PHP avec `sendmail_path` pointant sur `tests/sendmail.sh`, qui
les ajoute à `data/courriels_tests.txt` (clé `courriels` de `tests/config.php`). Les
suites les lisent par `courriels()`.

`tests/run.sh` renvoie 0 si aucun contrôle n'échoue, 1 sinon, 2 si l'environnement est
inutilisable — instance annoncée mais injoignable, verrou de sécurité déclenché. L'absence de
`tests/config.php` n'est plus une erreur : les suites concernées sont simplement non évaluées.

## Écrire une suite

Créer `tests/NN_nom.php` — la découverte est automatique :

```php
require __DIR__.'/bootstrap.php';

amorcer_socle();                 // autoload + constantes, sans contexte web
exiger_bdd();                    // facultatif : ignore la suite sans base
// exiger_instance();            // facultatif : ignore la suite sans instance

T::section('ma_classe::ma_methode');
T::eq('libellé du contrôle', 'attendu', ma_classe::ma_methode('entrée'));

exit(T::bilan('NN_nom'));
```

Assertions : `T::chk()` (booléen, détail affiché dans les deux cas), `T::vrai()` (détail
affiché seulement en cas d'échec), `T::eq()` / `T::neq()`, `T::contient()` /
`T::contientpas()`, `T::skip()`.

**Seul le dépôt est examiné.** Une suite qui parcourt le disque (`glob`, itérateur) filtre
les chemins par `suivi()` / `suivis()` (`tests/bootstrap.php`) : seuls les fichiers que git
suit, commités ou ajoutés par `git add`, sont contrôlés. Les restes d'une copie de travail
(anciens paquets dans `install/`, copies déployées dans `modules/`, bibliothèques retirées
du dépôt) sont ignorés. Un nouveau fichier n'est donc contrôlé qu'après son `git add`. Sans
git (archive du dépôt), tout est examiné. Même règle pour `tools/ploopi3_modules.php`
(palette des modules, contrôlée par la suite 37) : seuls les modules suivis de `install/`.

Une suite qui s'arrête avant `T::bilan()` (erreur fatale) est comptée par `tests/run.sh`
comme un échec et nommée sous le total (« Interrompue(s) ») : ses contrôles restants
n'ont pas tourné.

Deux règles de rigueur :

1. **Jamais d'assertion complaisante.** Un contrôle qui dépend d'un prérequis doit être
   précédé du contrôle de ce prérequis, sinon il passe « à vide » et ne protège de rien.
2. **On teste le comportement réel**, pas le comportement souhaité. Quand le comportement
   constaté est discutable, il est épinglé par un test et commenté : si quelqu'un le corrige
   un jour, le test le signalera au lieu de le laisser passer inaperçu.

## Constats relevés par les tests unitaires

Les défauts que ces suites avaient mis au jour — `init_description()` face au mode SQL
strict, `crypt::htpasswd()`, la classe `form_select_option` introuvable, la double
déclaration de `search_index` — ont été corrigés depuis, et les tests correspondants
vérifient désormais le comportement attendu plutôt que le défaut.

Restent les pièges d'API, épinglés par un test commenté :

| Où | Constat |
|---|---|
| `10_sql` | Un tableau passé en valeur est la **liste des paramètres** ; pour `%e`/`%t`/`%g`, l'encapsuler. `%s` pose lui-même les apostrophes ; `%r` n'applique aucun traitement. |
| `09_arr` | `arr::tojson()` double-encode par défaut un contenu déjà UTF-8. |

## Ce que la suite ne couvre pas

- le stockage S3 sans `PLOOPI_TEST_S3_ENDPOINT` : la suite 42 éprouve alors le stockage
  local ; la CI ne fournit pas de serveur S3 ;
- les conversions `unoconv`, `inkscape`, `ffmpeg` : seul l'échappement de leurs arguments est
  vérifié, les binaires n'étant pas supposés présents ;
- la désinstallation d'un module : elle supposerait d'installer un module tiers ;
- le `.htaccess` lui-même : le serveur PHP intégré le remplace par `tests/routeur.php` ;
- `memcached` et l'envoi de courriels ;
- dans le parcours navigateur, les actions qui écrivent (enregistrer, supprimer…) et le
  frontoffice : il ne fait que consulter les écrans du backoffice et ouvrir fenêtres et
  éditeurs ; il n'est pas lancé par la CI.
