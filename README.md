# PLOOPI

## A propos

http://www.ploopi.org/

## Avertissement

**Branche 1.9.7 en fin de vie.** Elle ne reçoit plus que des correctifs critiques et ne contient
pas les correctifs de sécurité de la version 2 (jeton anti-CSRF des opérations, contrôle d'accès
de la GED et des modules, injections SQL, désérialisation de cookie, XSS du wiki…). Migrer vers la
version 2 (branche `master`).

Correctifs critiques reportés :

- Mots de passe : bcrypt ne lisant que 72 octets, un identifiant long pouvait ouvrir la session
  avec n'importe quel mot de passe. Les empreintes sont recalculées à la connexion ; les comptes
  dont l'ancienne empreinte ne contient pas au moins 8 caractères du mot de passe doivent recevoir
  un nouveau mot de passe.
- `.htaccess_modele` : plus de `session.upload_progress` (erreur 500 depuis PHP 8.2.36 avec les
  gestionnaires de session `db` et `file`).

## Installation

### Prérequis

Installation des paquets logiciels nécessaires

```console
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install --yes apache2 libapache2-mod-php php php-mysql php-gd php-zip php-curl php-cli php-xml php-mbstring memcached php-memcached mariadb-server unoconv composer python-hachoir-metadata catdoc xpdf-utils jhead unrtf unzip zip openssl subversion git netcat
sudo a2enmod rewrite expires headers
sudo service apache2 restart
```

Définitions des droits pour la base de données

```console
sudo mysql -uroot -p -e "GRANT ALL PRIVILEGES ON ploopi.* TO 'ploopi'@'localhost' IDENTIFIED BY 'ploopi' WITH GRANT OPTION;"
```

### Installation Ploopi

```console
mkdir /var/www/ploopi
cd /var/www/ploopi
composer create-project --no-dev --no-interaction ovensia/ploopi:dev-STABLE-1.9.7 .
sudo chown -R www-data:www-data .
sudo find . -type d -print0 | xargs -0 -n 1 chmod 500
sudo find . -type f -print0 | xargs -0 -n 1 chmod 400
sudo find {data,config,modules} -type d -print0 | xargs -0 -n 1 chmod 700
sudo find {data,config,modules} -type f -print0 | xargs -0 -n 1 chmod 600
```

### Config Apache

Création d'un Virtual Host (optionnel mais recommandé) :

```console
sudo nano /etc/apache2/sites-available/ploopi.conf
```

Puis insérez les lignes suivantes :

```apacheconf
<VirtualHost *>
    ServerName ploopi

    DocumentRoot /var/www/ploopi/

    <Directory /var/www/ploopi/>
            AllowOverride All
    </Directory>
</VirtualHost>
```

On active le site et on redémarre Apache :

```console
sudo a2dissite 000-default
sudo a2ensite ploopi
sudo service apache2 reload
```

### Sécurité

```console
sudo nano /etc/apache2/conf-enabled/security.conf
```

```apacheconf
ServerTokens Prod
ServerSignature Off
```

```console
sudo service apache2 reload
```

## Téléchargement direct

https://daily.ploopi.org/


## Licence

GPL 2.0
