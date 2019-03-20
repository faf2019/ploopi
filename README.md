# PLOOPI

## A propos

http://www.ploopi.org/

## Avertissement

Version de développement

## Installation

### Prérequis

Installation des paquets logiciels nécessaires

```console
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install --yes apache2 libapache2-mod-php php php-mysql php-gd php-zip php-mcrypt php-curl php-cli memcached php-memcached mariadb-server unoconv composer python-hachoir-metadata catdoc xpdf-utils jhead unrtf unzip zip
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
composer create-project ovensia/ploopi:dev-trunk .
cp .htaccess_modele .htaccess
chown -R www-data:www-data .
find . -type d -print0 | xargs -0 -n 1 chmod 500
find . -type f -print0 | xargs -0 -n 1 chmod 400
find {data,config,modules} -type d -print0 | xargs -0 -n 1 chmod 700
find {data,config,modules} -type f -print0 | xargs -0 -n 1 chmod 600
```

### Config Apache

Création d'un Virtual Host (optionnel mais recommandé) :

```console
sudo nano /etc/apache2/sites-available/ploopi
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
sudo a2ensite ploopi
sudo service apache2 reload
```

## Licence

GPL 2.0
