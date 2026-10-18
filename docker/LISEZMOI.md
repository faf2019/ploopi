# Installer Ploopi avec Docker — le guide pour débutants

Vous n'avez besoin que de **Docker** (avec `docker compose`, inclus dans
Docker Desktop sur Windows/Mac, ou le paquet `docker-compose-plugin` sur
Linux). Rien d'autre : pas de PHP, pas de serveur web, pas de MySQL à
installer à la main.

## 1. Récupérer le dépôt

```sh
git clone https://github.com/faf2019/ploopi.git
cd ploopi
```

## 2. Modifier les mots de passe (30 secondes)

Ouvrez `docker-compose.yml` et remplacez :

- `ploopi_motdepasse` → un vrai mot de passe pour la base,
- `root_motdepasse` → un vrai mot de passe root MariaDB.

Ce sont les **seules** valeurs à changer. Les majuscules, chiffres et
symboles sont autorisés ; évitez juste les guillemets `"` et le signe `$`.

## 3. Démarrer

```sh
docker compose up --build -d
```

- la **première fois**, Docker télécharge les images et compile PHP, puis le
  conteneur installe les dépendances de Ploopi (`composer install`, dans
  `vendor/`) et crée le `.htaccess` (quelques minutes selon votre connexion) ;
- les fois suivantes, c'est quasi instantané ;
- `-d` laisse tout tourner en arrière-plan.

Attendez ~30 secondes que la base démarre, puis ouvrez :

> **http://localhost:8080/**

L'assistant d'installation de Ploopi s'affiche.

## 4. L'assistant d'installation

| Champ | Que mettre |
|---|---|
| Serveur SQL | **db**, déjà proposé (c'est le nom du service ; jamais `localhost`) |
| Utilisateur | **ploopi** |
| Mot de passe | celui que vous avez mis dans `docker-compose.yml` |
| Nom de la base | **ploopi** |
| Dossier de stockage des documents | laisser **../ploopi-data** (le volume Docker `documents`) |
| Identifiant administrateur | votre choix (ex. `admin`) |
| Mot de passe administrateur | votre choix — **notez-le** |

Suivez ensuite les écrans ; l'assistant écrit la configuration et crée les
tables tout seul.

Le **backoffice** est sur **http://localhost:8080/admin.php**.

Pour voir tout de suite à quoi ressemble un site Ploopi : dans le backoffice,
*Administration → Modules*, bouton **Générer le site de démonstration** (proposé tant
qu'aucun module n'est installé). Le site public est ensuite sur
**http://localhost:8080/**.

## 5. Au quotidien

| Commande | Effet |
|---|---|
| `docker compose ps` | voir si tout tourne |
| `docker compose logs -f web` | suivre les journaux de Ploopi |
| `docker compose down` | tout arrêter (les données sont conservées) |
| `docker compose up -d` | tout redémarrer |

## Où sont vos données ?

- **Base de données** : dans le volume Docker `donnees_db` (géré par Docker,
  conservé même après `down`).
- **Fichiers déposés** (GED, vignettes, cache) : dans le volume Docker
  `documents`, hors de la racine web — **faites-en une sauvegarde**.
- **Configuration** : dans le volume Docker `configuration`
  (`/var/www/ploopi-config/config.php` dans le conteneur), **pas** dans
  `config/config.php` du dossier cloné. Une installation de Ploopi hors Docker
  dans le même dossier garde ainsi son propre `config/config.php`, que le
  conteneur ne lit ni ne modifie : les deux cohabitent. Pour modifier la
  configuration Docker :
  ```sh
  docker compose cp web:/var/www/ploopi-config/config.php ./config-docker.php
  # … modifier config-docker.php …
  docker compose cp ./config-docker.php web:/var/www/ploopi-config/config.php
  docker compose restart web
  ```
- **Modules installés** : `modules/`, dans le dossier cloné. Apache tourne sous
  le propriétaire du dossier (votre utilisateur) : les fichiers qu'il crée
  restent modifiables sans `sudo`. Seul un dépôt appartenant à root fait
  exception : Apache tourne alors sous `www-data`, qui reçoit `config/` et
  `modules/` (sans quoi aucun module ne s'installe : « Impossible de copier les
  fichiers »).

Sauvegarde rapide de la base :

```sh
docker compose exec db mariadb-dump -u root -p"root_motdepasse" ploopi > sauvegarde.sql
```

Et des documents, puis de la configuration (elle contient les clés de
chiffrement : sans elle, les liens et mots de passe enregistrés sont perdus) :

```sh
docker compose cp web:/var/www/ploopi-data ./sauvegarde-documents
docker compose cp web:/var/www/ploopi-config/config.php ./sauvegarde-config.php
```

## Mettre à jour Ploopi

```sh
git pull
docker compose up --build -d
docker compose exec web composer install --no-dev --ignore-platform-req=ext-memcached
```

## Choisir la version de PHP

L'image utilise PHP 8.5 par défaut. Pour PHP 8.2 :

```sh
PHP_VERSION=8.2 docker compose up --build -d
```

ou, pour que ce choix soit retenu, un fichier `.env` à côté de
`docker-compose.yml` contenant la ligne `PHP_VERSION=8.2`. Chaque version a
sa propre image (`ploopi-web:php8.2`, `ploopi-web:php8.5`) : on passe de l'une
à l'autre sans recompiler, la base, les documents et la configuration restent
les mêmes. `docker compose exec web php -v` affiche la version en service.

## Dépanner

- **« localhost ne répond pas »** : `docker compose ps` — si `web` n'est pas
  `running`, regardez `docker compose logs web`.
- **`chown: changing ownership of 'config': Operation not permitted` en
  boucle** : version antérieure de l'image, sur un dépôt placé sur un disque
  externe (exFAT, NTFS), un partage réseau, ou avec Docker en mode
  `userns-remap`. Ces systèmes refusent de changer le propriétaire d'un
  fichier. Mettez à jour le dépôt (`git pull`) puis
  `docker compose up --build -d`. Si un message `ploopi : impossible de
  donner config/ à www-data` subsiste, le dépôt appartient à root : clonez-le
  avec votre utilisateur habituel plutôt qu'en root.
- **`include_once(…/config/config.php): Failed to open stream: Permission
  denied`** : `config.php` a été écrit par une version antérieure de
  l'image, sous `www-data`. Le conteneur le rend à votre utilisateur au
  démarrage : `docker compose up --build -d` suffit. À défaut, depuis l'hôte :
  `sudo chown -R "$(id -u):$(id -g)" config`.
- **`ploopi : config/config.php ignoré` dans les journaux** : le dossier
  contient le `config/config.php` d'une installation de Ploopi hors Docker. Rien
  à faire : le conteneur a sa propre configuration et ne touche pas à ce
  fichier. (Les versions antérieures de l'image le lisaient et affichaient
  `Database connection error` : il fallait alors le renommer à la main.)
- **`ploopi : config/config.php (installation Docker antérieure) repris`** :
  l'image précédente écrivait sa configuration dans `config/config.php`. Au
  premier démarrage de la nouvelle image, elle est recopiée dans le volume
  `configuration` (seulement si sa base répond depuis le conteneur, ce qui
  écarte une installation hors Docker). Le fichier d'origine reste en place ;
  vous pouvez le supprimer s'il ne sert à aucune installation hors Docker.
- **L'assistant dit « connexion à la base impossible »** : attendez encore
  quelques secondes (la base démarre lentement la première fois) puis
  rechargez la page ; vérifiez le mot de passe dans `docker-compose.yml`.
- **Tout remettre à zéro** (⚠️ efface la base, les documents et la
  configuration ; un `config/config.php` hors Docker n'est pas touché) :
  ```sh
  docker compose down -v
  docker compose up --build -d
  ```

## Et en « vrai » (production) ?

`docker-compose.yml` convient aux tests et à une utilisation personnelle. Pour
une mise en ligne, utilisez `docker-compose.prod.yml` :

- **Caddy** devant Ploopi : certificat HTTPS obtenu et renouvelé tout seul
  (Let's Encrypt), HTTP redirigé vers HTTPS ;
- le code est **intégré à l'image** et en lecture seule pour Apache ; seuls les
  modules installés, les documents et la configuration sont dans des volumes ;
- la base n'est joignable que par Ploopi, les mots de passe sont dans `.env`,
  hors du dépôt ; opcache est actif.

Prérequis : un serveur avec Docker, un nom de domaine qui pointe vers lui, les
ports 80 et 443 ouverts.

```sh
cp docker/env.prod.modele .env    # puis renseigner domaine et mots de passe
docker compose -f docker-compose.prod.yml up --build -d
```

Ouvrez `https://<votre domaine>/` : l'assistant d'installation propose déjà
la base (`db`, utilisateur `ploopi`, mot de passe de `.env`). Renseignez
l'adresse publique en `https://…` et gardez l'affichage des erreurs désactivé.

**Mettre à jour** : `git pull` puis la même commande `up --build -d`. Au
démarrage, le module `system` et le code des modules installés sont repris de
la nouvelle image ; terminez par la mise à jour des modules depuis
l'administration système.

**Après l'installation ou la mise à jour d'un module depuis le backoffice**, ou
une modification de la configuration, redémarrez Ploopi
(`docker compose -f docker-compose.prod.yml restart web`) : opcache ne relit
pas les fichiers modifiés.

**Sauvegarder** (à planifier, et restauration à éprouver) :

```sh
f="docker compose -f docker-compose.prod.yml"
$f exec db sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction ploopi' > ploopi-$(date +%F).sql
$f cp web:/var/www/ploopi-data ./ploopi-data-$(date +%F)
$f cp web:/var/www/ploopi-config/config.php ./config-$(date +%F).php
```

Le volume `caddy_data` contient les certificats : ne le supprimez pas (pas de
`down -v`), Let's Encrypt limite le nombre de nouvelles demandes.
