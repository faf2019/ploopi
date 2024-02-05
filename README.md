# PLOOPI

## A propos

http://www.ploopi.org/

## Avertissement

Version de développement

## Installation

### Prérequis

Installation des paquets logiciels nécessaires

```console
sudo apt update
sudo apt upgrade
sudo apt install --yes apt-transport-https apache2 libapache2-mod-php php php-mysql php-gd php-zip php-curl php-cli php-xml php-mbstring memcached php-memcached mariadb-server unoconv composer catdoc poppler-utils jhead unrtf unzip zip openssl subversion git mediainfo
sudo a2enmod rewrite expires headers
sudo systemctl restart apache2
```

Définitions des droits pour la base de données

```console
sudo mysql -uroot -p -e "GRANT ALL PRIVILEGES ON ploopi.* TO 'ploopi'@'localhost' IDENTIFIED BY 'ploopi' WITH GRANT OPTION;"
```

### Installation Ploopi

```console
mkdir /var/www/ploopi
cd /var/www/ploopi
composer create-project --no-dev --no-interaction ovensia/ploopi:dev-master ploopi
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
sudo systemctl restart apache2
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
sudo systemctl restart apache2
```

## Téléchargement direct

https://daily.ploopi.org/


## Licence

GPL 2.0
