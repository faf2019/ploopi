<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Fichier de langue 'français'
 * 
 * @package system
 * @subpackage lang
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_SYSTEM_PAGE_TITLE', 'Administration');
define ('_SYSTEM_LABEL_SYSTEM', 'SYSTEME');
define ('_SYSTEM_LABELICON_SYSTEMUPDATE', 'Mise à jour Système');
define ('_SYSTEM_LABELICON_INSTALLMODULES', 'Installation');
define ('_SYSTEM_LABELICON_MODULES', 'Modules');
define ('_SYSTEM_LABELICON_PARAMS', 'Paramètres');
define ('_SYSTEM_LABELICON_USERS', 'Utilisateurs');
define ('_SYSTEM_LABELICON_TOOLS', 'Outils');
define ('_SYSTEM_LABELICON_LOGS', 'Logs');
define ('_SYSTEM_LABELICON_PROFILES', 'Profils');
define ('_SYSTEM_LABELICON_ROLES', 'Roles');
define ('_SYSTEM_LABELICON_GROUP', 'Groupe');
define ('_SYSTEM_LABELICON_HOMEPAGE', 'Accueil');
define ('_SYSTEM_LABELICON_WORKSPACE', 'Espace');

define ('_SYSTEM_HOSTNAME', 'Nom de Domaine');
define ('_SYSTEM_NEWHOSTNAME', 'Nouveau Nom de Domaine');
define ('_SYSTEM_HOSTSELECTED', 'Domaine sélectionné');
define ('_SYSTEM_HOSTPARAMS', 'Paramètres du Domaine');

define ('_SYSTEM_UPDATE', 'Mise à jour du Système');

define ('_SYSTEM_MODULESELECTED', 'Module sélectionné');
define ('_SYSTEM_MODULEPARAM', 'Paramètres du Module');
define ('_SYSTEM_LABEL_NOMODULEPARAM', 'Aucun paramètre pour ce module');

define ('_SYSTEM_LABELTAB_GROUPLIST', 'Liste des Groupes');

define ('_SYSTEM_LABELTAB_USERLIST', 'Liste des Utilisateurs');
define ('_SYSTEM_LABELTAB_USERATTACH', 'Rattacher un Utilisateur');
define ('_SYSTEM_LABELTAB_USERMOVE', 'Déplacer un Utilisateur');
define ('_SYSTEM_LABELTAB_USERADD', 'Ajouter un Utilisateur');

define ('_SYSTEM_LABELTAB_GROUPATTACH', 'Rattacher un Groupe');

define ('_SYSTEM_LABELTAB_ROLEMANAGEMENT', 'Gestion des Rôles');
define ('_SYSTEM_LABELTAB_ROLEUSERS', 'Attribution des Rôles');
define ('_SYSTEM_LABELTAB_MULTIPLEROLEASSIGNMENT', 'Affectations Groupées');

define ('_SYSTEM_LABELTAB_PROFILEMANAGEMENT', 'Gestion des Profils');
define ('_SYSTEM_LABELTAB_PROFILEADD', 'Ajout d\'un Profil');
define ('_SYSTEM_LABELTAB_PROFILEASSIGNMENT', 'Affectation des Profils');

define ('_SYSTEM_LABELTAB_RULESLIST', 'Liste des règles');
define ('_SYSTEM_LABELTAB_RULESADD', 'Ajouter une règle');

define ('_SYSTEM_LABELTAB_USERIMPORT', 'Importer');

define ('_SYSTEM_LABELTAB_MESSAGEINBOX', 'Boite de Réception');
define ('_SYSTEM_LABELTAB_MESSAGEOUTBOX', 'Boite d\'Envoi');

define ('_SYSTEM_LABEL_RULELABEL', 'Libellé');
define ('_SYSTEM_LABEL_RULETYPE', 'Type');
define ('_SYSTEM_LABEL_RULEFIELD', 'Champ');
define ('_SYSTEM_LABEL_RULEOPERATOR', 'Opérateur');
define ('_SYSTEM_LABEL_RULEVALUE', 'Valeur');
define ('_SYSTEM_LABEL_RULEPROFILE', 'Profil');

define ('_SYSTEM_LABEL_INSTALLEDMODULES', 'Modules Installés');
define ('_SYSTEM_LABEL_NEWMODULEVERSIONS', 'Nouvelles Versions');
define ('_SYSTEM_LABEL_UNINSTALLEDMODULES', 'Nouveaux Modules');
define ('_SYSTEM_LABEL_ADDNEWMODULE', 'Ajouter un nouveau module');
define ('_SYSTEM_LABEL_ADDNEWMODULE_DESC', 'Permet d\'ajouter un nouveau module « installable » en envoyant simplement un fichier .zip');
define ('_SYSTEM_LABEL_ADDNEWMODULE_WARNING', '<b>Erreur !</b> Le répertoire « install » n\'est pas accessible en écriture pour Apache !<br>Effectuez un <a href=\'?ploopi_moduleicon=system_tools&op=diagnostic\'>diagnostic</a> pour de plus amples informations.');
define ('_SYSTEM_LABEL_INSTALLREPORT', 'Rapport d\'installation');
define ('_SYSTEM_LABEL_UPDATEREPORT', 'Rapport de mise à jour');

define ('_SYSTEM_LABEL_TOOLS', 'Outils');
define ('_SYSTEM_LABEL_LOGS', 'Analyse des Logs');

define ('_SYSTEM_LABEL_LABEL', 'Libellé');
define ('_SYSTEM_LABEL_MODULETYPE', 'Type');
define ('_SYSTEM_LABEL_AUTHOR', 'Auteur');
define ('_SYSTEM_LABEL_DESCRIPTION', 'Description');
define ('_SYSTEM_LABEL_VERSION', 'Version');
define ('_SYSTEM_LABEL_DATE', 'Date');
define ('_SYSTEM_LABEL_ACTIONS', 'Actions');
define ('_SYSTEM_LABEL_METABASE', 'Métabase');
define ('_SYSTEM_LABEL_WCEOBJECTS', 'Objets WCE');
define ('_SYSTEM_LABEL_MODULENAME', 'Nom');
define ('_SYSTEM_LABEL_ACTIVE', 'Actif');
define ('_SYSTEM_LABEL_PUBLIC', 'Public');
define ('_SYSTEM_LABEL_AUTOCONNECT', 'Connextion Auto');
define ('_SYSTEM_LABEL_SHARED', 'Partagé');
define ('_SYSTEM_LABEL_HERITED', 'Hérité');
define ('_SYSTEM_LABEL_ADMINRESTRICTED', 'Administration Restreinte');
define ('_SYSTEM_LABEL_MODULEPOSITION', 'Pos.');
define ('_SYSTEM_LABEL_MODIFY', 'Modifier');
define ('_SYSTEM_LABEL_DELETE', 'Supprimer');
define ('_SYSTEM_LABEL_DETACH', 'Détacher');
define ('_SYSTEM_LABEL_ATTACH', 'Rattacher');
define ('_SYSTEM_LABEL_MOVE', 'Déplacer');
define ('_SYSTEM_LABEL_ASSIGN', 'Affecter');
define ('_SYSTEM_LABEL_VIEWMODE', 'Vue');
define ('_SYSTEM_LABEL_ROLECHOICE', 'Rôle');
define ('_SYSTEM_LABEL_TRANSVERSE', 'Transversale');
define ('_SYSTEM_APPLYHERITAGE', 'Appliquer l\'héritage aux sous-groupes');


define ('_SYSTEM_LABEL_INSTALL', 'Installer');
define ('_SYSTEM_LABEL_UPDATE', 'Mettre à Jour');
define ('_SYSTEM_LABEL_UNINSTALL', 'Désinstaller');
define ('_SYSTEM_LABEL_ROLEADD', 'Ajouter un Rôle');

define ('_SYSTEM_LABEL_TYPE', 'Type');
define ('_SYSTEM_LABEL_PROFILE', 'Profil');
define ('_SYSTEM_LABEL_LASTNAME', 'Nom');
define ('_SYSTEM_LABEL_FIRSTNAME', 'Prénom');
define ('_SYSTEM_LABEL_LOGIN', 'Login');
define ('_SYSTEM_LABEL_ACTION', 'Action');
define ('_SYSTEM_LABEL_USER', 'Utilisateur');
define ('_SYSTEM_LABEL_GROUP', 'Groupe');
define ('_SYSTEM_LABEL_PASSWORD', 'Mot de Passe');
define ('_SYSTEM_LABEL_PASSWORD_CONFIRM', 'Confirmation du Mot de Passe');
define ('_SYSTEM_LABEL_EXPIRATION_DATE', 'Date d\'Expiration');
define ('_SYSTEM_LABEL_TIMEZONE', 'Fuseau Horaire');
define ('_SYSTEM_LABEL_ORIGIN', 'Origine');
define ('_SYSTEM_LABEL_LEVEL', 'Niveau');
define ('_SYSTEM_LABEL_COMMENTS', 'Commentaires');
define ('_SYSTEM_LABEL_ADDRESS', 'Adresse');
define ('_SYSTEM_LABEL_POSTALCODE', 'Code Postal');
define ('_SYSTEM_LABEL_CITY', 'Ville');
define ('_SYSTEM_LABEL_COUNTRY', 'Pays');
define ('_SYSTEM_LABEL_PHONE', 'Téléphone');
define ('_SYSTEM_LABEL_MOBILE', 'Tél. Portable');
define ('_SYSTEM_LABEL_FAX', 'Fax');
define ('_SYSTEM_LABEL_EMAIL', 'Mèl');
define ('_SYSTEM_LABEL_TICKETSBYEMAIL', 'Copie des messages par Mèl');
define ('_SYSTEM_LABEL_SERVERTIMEZONE', 'Synchronisé avec le fuseau horaire du serveur');
define ('_SYSTEM_LABEL_FUNCTION', 'Fonction');
define ('_SYSTEM_LABEL_SERVICE', 'Service');
define ('_SYSTEM_LABEL_COLOR', 'Couleur');
define ('_SYSTEM_LABEL_OFFICE', 'Bureau');
define ('_SYSTEM_LABEL_CIVILITY', 'Civilité');

define ('_SYSTEM_LABEL_PARENTS', 'Parents');


define ('_SYSTEM_LABEL_GROUP_MANAGEMENT', 'Gestion du Groupe <LABEL> [ <GROUP> ]');
define ('_SYSTEM_LABEL_WORKSPACE_MANAGEMENT', 'Gestion de l\'Espace <LABEL> [ <GROUP> ]');

define ('_SYSTEM_LABEL_GROUP_ADD', 'Ajout d\'un sous-groupe');
define ('_SYSTEM_LABEL_WORKSPACE_ADD', 'Ajout d\'un sous-espace');

define ('_SYSTEM_LABEL_WORKSPACE_INFORMATION', 'Informations sur l\'Espace');

define ('_SYSTEM_LABEL_GROUP_INFORMATION', 'Informations sur le Groupe');
define ('_SYSTEM_LABEL_GROUP_MODIFY', 'Modifier le Groupe');
define ('_SYSTEM_LABEL_WORKSPACE_MODIFY', 'Modifier l\'Espace');
define ('_SYSTEM_LABEL_FILTERING', 'Filtrage / Sécurité');
define ('_SYSTEM_LABEL_MANAGEMENT', 'Gestion');
define ('_SYSTEM_LABEL_ACCESS', 'Accès');
define ('_SYSTEM_LABEL_INTERFACE', 'Interface');
define ('_SYSTEM_LABEL_META', 'META Informations');

//define ('_SYSTEM_LABEL_HERITAGES', 'Héritages');
define ('_SYSTEM_LABEL_USEDMODULES', 'Modules utilisés');

define ('_SYSTEM_LABEL_GROUP_CODE',         'Code');
define ('_SYSTEM_LABEL_GROUP_NAME',         'Nom');
define ('_SYSTEM_LABEL_GROUP_FATHER',       'Groupe Père');
define ('_SYSTEM_LABEL_GROUP_SYSTEM',       'Système');
define ('_SYSTEM_LABEL_GROUP_SKIN',     'Habillage');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDIP',    'IP Autorisées');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDMAC',   'Adresses MAC Autorisées');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAIN',    'Domaine WEB');
define ('_SYSTEM_LABEL_GROUP_MUSTDEFINERULE',   'Rôle obligatoire pour l\'accès à cet espace');
define ('_SYSTEM_LABEL_GROUP_ACCESSMODE',   'Type d\'accès');
define ('_SYSTEM_LABEL_GROUP_ADMIN',        'Activation du Backoffice');
define ('_SYSTEM_LABEL_GROUP_WEB',      'Activation du Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ACCESSWARNING',    '(nécessite au moins un domaine)');
define ('_SYSTEM_LABEL_GROUP_SHARED',       'Partagé');

define ('_SYSTEM_LABEL_USER_PROFILE',       'Profil par défaut');
define ('_SYSTEM_LABEL_USER_UNDEFINED',     'Non défini');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAINLIST',    'Liste des domaines Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ADMINDOMAINLIST',  'Liste des domaines Backoffice');


define ('_SYSTEM_LABEL_CREATE_CLONE',       'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD',       'Créer');
define ('_SYSTEM_LABEL_CREATE_GROUP',       'Créer un Groupe');
define ('_SYSTEM_LABEL_DELETE_GROUP',       'Supprimer');

define ('_SYSTEM_LABEL_CREATE_CLONE_WORKSPACE',     'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD_WORKSPACE',     'Créer');
define ('_SYSTEM_LABEL_DELETE_WORKSPACE',       'Supprimer');

define ('_SYSTEM_LABEL_GROUP_MODULES_MANAGEMENT',   'Gestion des Modules du Groupe [ <GROUP> ]');
define ('_SYSTEM_LABEL_GROUP_ROLES_MANAGEMENT',     'Gestion des Rôles du Groupe [ <GROUP> ]');
define ('_SYSTEM_LABEL_GROUP_AVAILABLE_MODULES',    'Modules disponibles dans cet Espace');
define ('_SYSTEM_LABEL_GROUP_USABLE_MODULES',       'Modules utilisables pour cet Espace');
define ('_SYSTEM_LABEL_MODULE_PROPERTIES',      'Propriétés du Module [ <MODULE> ]');
define ('_SYSTEM_LABEL_MODULE_PARAMS',          'Paramètres du Module [ <MODULE> ]');
define ('_SYSTEM_LABEL_MODULE_ROLES',           'Module « <MODULE> »');
define ('_SYSTEM_LABEL_PROFILES_AVAILABLE',         'Profils disponibles dans ce Groupe');

define ('_SYSTEM_LABEL_MYPROFILE',              'Mon Profil');
define ('_SYSTEM_LABEL_MYDATAS',                        'Mes Informations');
define ('_SYSTEM_LABEL_MYACCOUNT',                        'Mon Compte');
define ('_SYSTEM_LABEL_ABOUT',              'A Propos');

define ('_SYSTEM_LABEL_MODULE_ADMINISTRATOR',       'Gestionnaire du Module');

define ('_SYSTEM_LABEL_SYSTEM_AVAILABLE_MODULES',   'Modules disponibles');
define ('_SYSTEM_LABEL_SYSTEM_USABLE_MODULES',      'Modules utilisables');

define ('_SYSTEM_LABEL_DEFAULT_PROFILE',        'Profil par défaut');

define ('_SYSTEM_LABEL_NO_MODULE_DEFINED',      'Aucun module défini pour ce groupe');
define ('_SYSTEM_LABEL_NO_USER_DEFINED',        'Aucun utilisateur défini pour ce groupe');
define ('_SYSTEM_LABEL_NO_ROLE_DEFINED',        'Aucun rôle défini pour ce module');
define ('_SYSTEM_LABEL_ROLE_LIST',          'Liste des rôles pour cet espace');
define ('_SYSTEM_LABEL_MODIFY_ROLE_ASSIGNMENT',     'Modifier l\'affectation des Rôles');

define ('_SYSTEM_LABEL_COMMENTARY',     'Commentaire');
define ('_SYSTEM_LABEL_RESULT',     'Résultat');


define ('_SYSTEM_MSG_CONFIRMGROUPDELETE', 'Êtes-vous certain de vouloir\nsupprimer ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMGROUPDETACH', 'Êtes-vous certain de vouloir\ndétacher ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMUSERDETACH', 'Êtes-vous certain de vouloir\ndétacher cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMUSERDELETE', 'Êtes-vous certain de vouloir\nsupprimer cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMROLEDELETE', 'Êtes-vous certain de vouloir\nsupprimer ce Rôle ?');
define ('_SYSTEM_MSG_CONFIRMRULEDELETE', 'Êtes-vous certain de vouloir\nsupprimer cette règle ?');
define ('_SYSTEM_MSG_CONFIRMPROFILEDELETE','Êtes-vous certain de vouloir\nsupprimer ce Profil ?');
define ('_SYSTEM_MSG_CONFIRMLOGDELETE', 'Êtes-vous certain de vouloir\nsupprimer les Logs ?');
define ('_SYSTEM_MSG_PASSWORDERROR', 'Erreur lors de la saisie du mot de passe.\nVous devez saisir deux fois le mot de passe');
define ('_SYSTEM_MSG_LOGINERROR', 'Erreur lors de la création de l\'utilisateur.\nCe login existe déjà.');
define ('_SYSTEM_MSG_LOGINPASSWORDERROR', 'Erreur lors de la saisie du mot de passe.<BR>Votre mot de passe a été rejeté par le système');

define ('_SYSTEM_MSG_CONFIRMMODULEDETACH', 'Êtes-vous certain de vouloir détacher ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMODULEDELETE', 'Êtes-vous certain de vouloir supprimer ce Module ?');
define ('_SYSTEM_MSG_CONFIRMHOMEPAGERESET', 'Êtes-vous certain de vouloir revenir à la page d\\\'accueil par défaut ?');

define ('_SYSTEM_MSG_CONFIRMMODULEUNINSTAL', 'Êtes-vous certain de vouloir désinstaller ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMBUPDATE', 'Êtes-vous certain de vouloir mettre à jour la métabase de ce module ?\n(Rechargement du fichier XML)');

define ('_SYSTEM_MSG_INFODELETE_USERS', 'Vous ne pouvez pas supprimer ce groupe car il contient des utilisateurs');
define ('_SYSTEM_MSG_INFODELETE_GROUPS', 'Vous ne pouvez pas supprimer ce groupe car il contient des sous-groupes');

define ('_SYSTEM_MSG_CANTCOPYGROUP', 'Vous n\\\'avez pas les droits suffisants pour cloner cet espace');




define ('_SYSTEM_EXPLAIN_ABOUT', 'Ploopi est un produit développé par la société <A TARGET=\'blank\' HREF=\'http://www.netlorconcept.com\'>Netlor Concept</A><br>Le logo ainsi que la marque sont déposés et appartiennent à la société Netlor Concept.');

define ('_SYSTEM_EXPLAIN_MODULENAME', 'Nom que portera le module dans l\'interface');
define ('_SYSTEM_EXPLAIN_ACTIVE', 'Détermine si le module est activé ou non (activé = utilisable)');
define ('_SYSTEM_EXPLAIN_PUBLIC', 'Détermine si le module est public ou non (visible par un utilisateur non connecté)');
define ('_SYSTEM_EXPLAIN_AUTOCONNECT', 'Détermine si ce module est affiché par défaut à la connexion de l\'utilisateur');
define ('_SYSTEM_EXPLAIN_SHARED', 'Détermine si ce module est partagé pour les sous-groupes');
define ('_SYSTEM_EXPLAIN_HERITED', 'Détermine si ce module est automatiquement hérité aux sous-groupes');
define ('_SYSTEM_EXPLAIN_ADMINRESTRICTED', 'Si l\'administration du module est restreinte, les administrateurs des sous-groupes qui utilisent ce module auront des droits limités');
define ('_SYSTEM_EXPLAIN_VIEWMODE', 'Choix de la vue qu\'ont les utilisateurs sur les données du module, <b>Privée</b> : les données ne sont vues que par le groupe, <b>Descendante</b> : les données sont vues par le groupe et les sous-groupes (les données « descendent »), <b>Ascendante</b> : les données sont vues par le groupe et les groupes parents (les données « montent »), <b>Globale</b> : les données sont entièrement partagées');

define ('_SYSTEM_LABEL_PHPINFO', 'Config - PhpInfo');
define ('_SYSTEM_LABEL_DIAGNOSTIC', 'Config - Diagnostic');
define ('_SYSTEM_LABEL_CONNECTEDUSERS', 'Logs - Utilisateurs Connectés');
define ('_SYSTEM_LABEL_SQLDUMP', 'SqlDump');
define ('_SYSTEM_LABEL_ZIP', 'Zip');
define ('_SYSTEM_LABEL_BACKUP', 'Sauvegarde');
define ('_SYSTEM_LABEL_ACTIONHISTORY', 'Logs - Historique des Actions');
define ('_SYSTEM_LABEL_SERVERLOAD', 'Logs - Charge du Serveur');

define ('_SYSTEM_EXPLAIN_PHPINFO', 'L\'outil « '._SYSTEM_LABEL_PHPINFO.' » affiche la configuration PHP du serveur');
define ('_SYSTEM_EXPLAIN_DIAGNOSTIC', 'L\'outil « '._SYSTEM_LABEL_DIAGNOSTIC.' » vérifie quelques sources d\'erreurs courantes pouvant entraîner des dysfonctionnements de PLOOPI');
define ('_SYSTEM_EXPLAIN_CONNECTEDUSERS', 'L\'outil « '._SYSTEM_LABEL_CONNECTEDUSERS.' » affiche la liste des utilisateurs connectés au site en temps réel');
define ('_SYSTEM_EXPLAIN_SQLDUMP', 'L\'outil « '._SYSTEM_LABEL_SQLDUMP.' » vous permet de télécharger les données dans un fichier SQL. Attention cette fonctionnalité ne remplace pas un dump classique. Il est également recommandé de n\'utiliser cette fonctionnalité que sur des petites bases de données.');
define ('_SYSTEM_EXPLAIN_ZIP', 'L\'outil « Zip » vous permet de télécharger les sources de PLOOPI dans un fichier ZIP');
define ('_SYSTEM_EXPLAIN_BACKUP', 'L\'outil « Sauvegarde » vous permet de créer une sauvegarde complète du système (données + sources) afin de les restaurer à une date ultérieure');
define ('_SYSTEM_EXPLAIN_ACTIONHISTORY', 'L\'outil « '._SYSTEM_LABEL_ACTIONHISTORY.' » vous permet de consulter les actions effectuées par les utilisateurs');
define ('_SYSTEM_EXPLAIN_SERVERLOAD', 'L\'outil « '._SYSTEM_LABEL_SERVERLOAD.' » affiche la charge du serveur sur plusieurs intervalles de temps');


define ('_SYSTEM_LABEL_HOMEPAGECONTENT',    'Contenu de la Page d\'Accueil');
define ('_SYSTEM_LABEL_PREVIEW',        'Prévisualiser');
define ('_SYSTEM_LABEL_ADDLINE',        'Ajouter une ligne');
define ('_SYSTEM_LABEL_ADDCOLUMN',      'Ajouter une colonne');
define ('_SYSTEM_LABEL_NBCOLUMNS',      'Nombre de colonnes');
define ('_SYSTEM_LABEL_TITLE', 'Titre');
define ('_SYSTEM_LABEL_MODULE', 'Module');
define ('_SYSTEM_LABEL_UNDEFINEDCONTENT', 'Contenu non défini');
define ('_SYSTEM_LABEL_UNDEFINED', 'Non défini');
define ('_SYSTEM_LABEL_SIZE', 'Taille');
define ('_SYSTEM_LABEL_VISIBILITY', 'Visibilité');

define ('_SYSTEM_LABEL_MODULEPARAMETERS', 'Paramètres des Modules');

define ('_SYSTEM_LABEL_BACKTODEFAULTPAGE', 'Revenir à la page par défaut');
define ('_SYSTEM_LABEL_MODIFYMYHOMEPAGE', 'Modifier ma page d\'accueil');

define ('_SYSTEM_LABEL_MODULEINSTANCIATION', 'Instanciation d\'un Module « <LABEL> »');
define ('_SYSTEM_LABEL_MODULEDELETE', 'Suppression du Module « <LABEL> »');
define ('_SYSTEM_LABEL_MODULEUNINSTALL', 'Déinstallation du Module « <LABEL> »');
define ('_SYSTEM_LABEL_USERDETACH', 'Suppression de l\'utilisateur « <LABELUSER> » du groupe « <LABELGROUP> »');
define ('_SYSTEM_LABEL_USERDELETE', 'Suppression de l\'utilisateur « <LABEL> »');
define ('_SYSTEM_LABEL_USERCREATE', 'Création de l\'utilisateur « <LABEL> »');


define ('_SYSTEM_LABEL_IMPORTSRC', 'Source d\'import');


define ('_SYSTEM_LABEL_TICKETS', 'Messages');
define ('_SYSTEM_LABEL_SEARCH', 'Recherche');


define ('_SYSTEM_LABEL_MYTICKETS', 'Mes Messages');
define ('_SYSTEM_LABEL_TICKETS_SENDBOX', 'Messsages envoyés');
define ('_SYSTEM_LABEL_TICKETS_INCOMINGBOX', 'Messsages reçus');
define ('_SYSTEM_LABEL_TICKETS_WAITINGVALIDATION', 'Messages en attente de validation');
define ('_SYSTEM_LABEL_TICKETS_TOVALIDATE', 'Messages à valider');
define ('_SYSTEM_LABEL_TICKETS_ALL', 'Tous les messages');
define ('_SYSTEM_LABEL_NOTICKETS', 'Aucun message');

?>
