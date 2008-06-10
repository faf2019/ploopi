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
 * Fichier de langue 'fran�ais'
 * 
 * @package system
 * @subpackage lang
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * D�finition des constantes
 */

define ('_SYSTEM_PAGE_TITLE', 'Administration');
define ('_SYSTEM_LABEL_SYSTEM', 'SYSTEME');
define ('_SYSTEM_LABELICON_SYSTEMUPDATE', 'Mise � jour Syst�me');
define ('_SYSTEM_LABELICON_INSTALLMODULES', 'Installation');
define ('_SYSTEM_LABELICON_MODULES', 'Modules');
define ('_SYSTEM_LABELICON_PARAMS', 'Param�tres');
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
define ('_SYSTEM_HOSTSELECTED', 'Domaine s�lectionn�');
define ('_SYSTEM_HOSTPARAMS', 'Param�tres du Domaine');

define ('_SYSTEM_UPDATE', 'Mise � jour du Syst�me');

define ('_SYSTEM_MODULESELECTED', 'Module s�lectionn�');
define ('_SYSTEM_MODULEPARAM', 'Param�tres du Module');
define ('_SYSTEM_LABEL_NOMODULEPARAM', 'Aucun param�tre pour ce module');

define ('_SYSTEM_LABELTAB_GROUPLIST', 'Liste des Groupes');

define ('_SYSTEM_LABELTAB_USERLIST', 'Liste des Utilisateurs');
define ('_SYSTEM_LABELTAB_USERATTACH', 'Rattacher un Utilisateur');
define ('_SYSTEM_LABELTAB_USERMOVE', 'D�placer un Utilisateur');
define ('_SYSTEM_LABELTAB_USERADD', 'Ajouter un Utilisateur');

define ('_SYSTEM_LABELTAB_GROUPATTACH', 'Rattacher un Groupe');

define ('_SYSTEM_LABELTAB_ROLEMANAGEMENT', 'Gestion des R�les');
define ('_SYSTEM_LABELTAB_ROLEUSERS', 'Attribution des R�les');
define ('_SYSTEM_LABELTAB_MULTIPLEROLEASSIGNMENT', 'Affectations Group�es');

define ('_SYSTEM_LABELTAB_PROFILEMANAGEMENT', 'Gestion des Profils');
define ('_SYSTEM_LABELTAB_PROFILEADD', 'Ajout d\'un Profil');
define ('_SYSTEM_LABELTAB_PROFILEASSIGNMENT', 'Affectation des Profils');

define ('_SYSTEM_LABELTAB_RULESLIST', 'Liste des r�gles');
define ('_SYSTEM_LABELTAB_RULESADD', 'Ajouter une r�gle');

define ('_SYSTEM_LABELTAB_USERIMPORT', 'Importer');

define ('_SYSTEM_LABELTAB_MESSAGEINBOX', 'Boite de R�ception');
define ('_SYSTEM_LABELTAB_MESSAGEOUTBOX', 'Boite d\'Envoi');

define ('_SYSTEM_LABEL_RULELABEL', 'Libell�');
define ('_SYSTEM_LABEL_RULETYPE', 'Type');
define ('_SYSTEM_LABEL_RULEFIELD', 'Champ');
define ('_SYSTEM_LABEL_RULEOPERATOR', 'Op�rateur');
define ('_SYSTEM_LABEL_RULEVALUE', 'Valeur');
define ('_SYSTEM_LABEL_RULEPROFILE', 'Profil');

define ('_SYSTEM_LABEL_INSTALLEDMODULES', 'Modules Install�s');
define ('_SYSTEM_LABEL_NEWMODULEVERSIONS', 'Nouvelles Versions');
define ('_SYSTEM_LABEL_UNINSTALLEDMODULES', 'Nouveaux Modules');
define ('_SYSTEM_LABEL_ADDNEWMODULE', 'Ajouter un nouveau module');
define ('_SYSTEM_LABEL_ADDNEWMODULE_DESC', 'Permet d\'ajouter un nouveau module � installable � en envoyant simplement un fichier .zip');
define ('_SYSTEM_LABEL_ADDNEWMODULE_WARNING', '<b>Erreur !</b> Le r�pertoire � install � n\'est pas accessible en �criture pour Apache !<br>Effectuez un <a href=\'?ploopi_moduleicon=system_tools&op=diagnostic\'>diagnostic</a> pour de plus amples informations.');
define ('_SYSTEM_LABEL_INSTALLREPORT', 'Rapport d\'installation');
define ('_SYSTEM_LABEL_UPDATEREPORT', 'Rapport de mise � jour');

define ('_SYSTEM_LABEL_TOOLS', 'Outils');
define ('_SYSTEM_LABEL_LOGS', 'Analyse des Logs');

define ('_SYSTEM_LABEL_LABEL', 'Libell�');
define ('_SYSTEM_LABEL_MODULETYPE', 'Type');
define ('_SYSTEM_LABEL_AUTHOR', 'Auteur');
define ('_SYSTEM_LABEL_DESCRIPTION', 'Description');
define ('_SYSTEM_LABEL_VERSION', 'Version');
define ('_SYSTEM_LABEL_DATE', 'Date');
define ('_SYSTEM_LABEL_ACTIONS', 'Actions');
define ('_SYSTEM_LABEL_METABASE', 'M�tabase');
define ('_SYSTEM_LABEL_WCEOBJECTS', 'Objets WCE');
define ('_SYSTEM_LABEL_MODULENAME', 'Nom');
define ('_SYSTEM_LABEL_ACTIVE', 'Actif');
define ('_SYSTEM_LABEL_PUBLIC', 'Public');
define ('_SYSTEM_LABEL_AUTOCONNECT', 'Connextion Auto');
define ('_SYSTEM_LABEL_SHARED', 'Partag�');
define ('_SYSTEM_LABEL_HERITED', 'H�rit�');
define ('_SYSTEM_LABEL_ADMINRESTRICTED', 'Administration Restreinte');
define ('_SYSTEM_LABEL_MODULEPOSITION', 'Pos.');
define ('_SYSTEM_LABEL_MODIFY', 'Modifier');
define ('_SYSTEM_LABEL_DELETE', 'Supprimer');
define ('_SYSTEM_LABEL_DETACH', 'D�tacher');
define ('_SYSTEM_LABEL_ATTACH', 'Rattacher');
define ('_SYSTEM_LABEL_MOVE', 'D�placer');
define ('_SYSTEM_LABEL_ASSIGN', 'Affecter');
define ('_SYSTEM_LABEL_VIEWMODE', 'Vue');
define ('_SYSTEM_LABEL_ROLECHOICE', 'R�le');
define ('_SYSTEM_LABEL_TRANSVERSE', 'Transversale');
define ('_SYSTEM_APPLYHERITAGE', 'Appliquer l\'h�ritage aux sous-groupes');


define ('_SYSTEM_LABEL_INSTALL', 'Installer');
define ('_SYSTEM_LABEL_UPDATE', 'Mettre � Jour');
define ('_SYSTEM_LABEL_UNINSTALL', 'D�sinstaller');
define ('_SYSTEM_LABEL_ROLEADD', 'Ajouter un R�le');

define ('_SYSTEM_LABEL_TYPE', 'Type');
define ('_SYSTEM_LABEL_PROFILE', 'Profil');
define ('_SYSTEM_LABEL_LASTNAME', 'Nom');
define ('_SYSTEM_LABEL_FIRSTNAME', 'Pr�nom');
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
define ('_SYSTEM_LABEL_PHONE', 'T�l�phone');
define ('_SYSTEM_LABEL_MOBILE', 'T�l. Portable');
define ('_SYSTEM_LABEL_FAX', 'Fax');
define ('_SYSTEM_LABEL_EMAIL', 'M�l');
define ('_SYSTEM_LABEL_TICKETSBYEMAIL', 'Copie des messages par M�l');
define ('_SYSTEM_LABEL_SERVERTIMEZONE', 'Synchronis� avec le fuseau horaire du serveur');
define ('_SYSTEM_LABEL_FUNCTION', 'Fonction');
define ('_SYSTEM_LABEL_SERVICE', 'Service');
define ('_SYSTEM_LABEL_COLOR', 'Couleur');
define ('_SYSTEM_LABEL_OFFICE', 'Bureau');
define ('_SYSTEM_LABEL_CIVILITY', 'Civilit�');

define ('_SYSTEM_LABEL_PARENTS', 'Parents');


define ('_SYSTEM_LABEL_GROUP_MANAGEMENT', 'Gestion du Groupe <LABEL> [ <GROUP> ]');
define ('_SYSTEM_LABEL_WORKSPACE_MANAGEMENT', 'Gestion de l\'Espace <LABEL> [ <GROUP> ]');

define ('_SYSTEM_LABEL_GROUP_ADD', 'Ajout d\'un sous-groupe');
define ('_SYSTEM_LABEL_WORKSPACE_ADD', 'Ajout d\'un sous-espace');

define ('_SYSTEM_LABEL_WORKSPACE_INFORMATION', 'Informations sur l\'Espace');

define ('_SYSTEM_LABEL_GROUP_INFORMATION', 'Informations sur le Groupe');
define ('_SYSTEM_LABEL_GROUP_MODIFY', 'Modifier le Groupe');
define ('_SYSTEM_LABEL_WORKSPACE_MODIFY', 'Modifier l\'Espace');
define ('_SYSTEM_LABEL_FILTERING', 'Filtrage / S�curit�');
define ('_SYSTEM_LABEL_MANAGEMENT', 'Gestion');
define ('_SYSTEM_LABEL_ACCESS', 'Acc�s');
define ('_SYSTEM_LABEL_INTERFACE', 'Interface');
define ('_SYSTEM_LABEL_META', 'META Informations');

//define ('_SYSTEM_LABEL_HERITAGES', 'H�ritages');
define ('_SYSTEM_LABEL_USEDMODULES', 'Modules utilis�s');

define ('_SYSTEM_LABEL_GROUP_CODE',         'Code');
define ('_SYSTEM_LABEL_GROUP_NAME',         'Nom');
define ('_SYSTEM_LABEL_GROUP_FATHER',       'Groupe P�re');
define ('_SYSTEM_LABEL_GROUP_SYSTEM',       'Syst�me');
define ('_SYSTEM_LABEL_GROUP_SKIN',     'Habillage');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDIP',    'IP Autoris�es');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDMAC',   'Adresses MAC Autoris�es');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAIN',    'Domaine WEB');
define ('_SYSTEM_LABEL_GROUP_MUSTDEFINERULE',   'R�le obligatoire pour l\'acc�s � cet espace');
define ('_SYSTEM_LABEL_GROUP_ACCESSMODE',   'Type d\'acc�s');
define ('_SYSTEM_LABEL_GROUP_ADMIN',        'Activation du Backoffice');
define ('_SYSTEM_LABEL_GROUP_WEB',      'Activation du Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ACCESSWARNING',    '(n�cessite au moins un domaine)');
define ('_SYSTEM_LABEL_GROUP_SHARED',       'Partag�');

define ('_SYSTEM_LABEL_USER_PROFILE',       'Profil par d�faut');
define ('_SYSTEM_LABEL_USER_UNDEFINED',     'Non d�fini');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAINLIST',    'Liste des domaines Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ADMINDOMAINLIST',  'Liste des domaines Backoffice');


define ('_SYSTEM_LABEL_CREATE_CLONE',       'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD',       'Cr�er');
define ('_SYSTEM_LABEL_CREATE_GROUP',       'Cr�er un Groupe');
define ('_SYSTEM_LABEL_DELETE_GROUP',       'Supprimer');

define ('_SYSTEM_LABEL_CREATE_CLONE_WORKSPACE',     'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD_WORKSPACE',     'Cr�er');
define ('_SYSTEM_LABEL_DELETE_WORKSPACE',       'Supprimer');

define ('_SYSTEM_LABEL_GROUP_MODULES_MANAGEMENT',   'Gestion des Modules du Groupe [ <GROUP> ]');
define ('_SYSTEM_LABEL_GROUP_ROLES_MANAGEMENT',     'Gestion des R�les du Groupe [ <GROUP> ]');
define ('_SYSTEM_LABEL_GROUP_AVAILABLE_MODULES',    'Modules disponibles dans cet Espace');
define ('_SYSTEM_LABEL_GROUP_USABLE_MODULES',       'Modules utilisables pour cet Espace');
define ('_SYSTEM_LABEL_MODULE_PROPERTIES',      'Propri�t�s du Module [ <MODULE> ]');
define ('_SYSTEM_LABEL_MODULE_PARAMS',          'Param�tres du Module [ <MODULE> ]');
define ('_SYSTEM_LABEL_MODULE_ROLES',           'Module � <MODULE> �');
define ('_SYSTEM_LABEL_PROFILES_AVAILABLE',         'Profils disponibles dans ce Groupe');

define ('_SYSTEM_LABEL_MYPROFILE',              'Mon Profil');
define ('_SYSTEM_LABEL_MYDATAS',                        'Mes Informations');
define ('_SYSTEM_LABEL_MYACCOUNT',                        'Mon Compte');
define ('_SYSTEM_LABEL_ABOUT',              'A Propos');

define ('_SYSTEM_LABEL_MODULE_ADMINISTRATOR',       'Gestionnaire du Module');

define ('_SYSTEM_LABEL_SYSTEM_AVAILABLE_MODULES',   'Modules disponibles');
define ('_SYSTEM_LABEL_SYSTEM_USABLE_MODULES',      'Modules utilisables');

define ('_SYSTEM_LABEL_DEFAULT_PROFILE',        'Profil par d�faut');

define ('_SYSTEM_LABEL_NO_MODULE_DEFINED',      'Aucun module d�fini pour ce groupe');
define ('_SYSTEM_LABEL_NO_USER_DEFINED',        'Aucun utilisateur d�fini pour ce groupe');
define ('_SYSTEM_LABEL_NO_ROLE_DEFINED',        'Aucun r�le d�fini pour ce module');
define ('_SYSTEM_LABEL_ROLE_LIST',          'Liste des r�les pour cet espace');
define ('_SYSTEM_LABEL_MODIFY_ROLE_ASSIGNMENT',     'Modifier l\'affectation des R�les');

define ('_SYSTEM_LABEL_COMMENTARY',     'Commentaire');
define ('_SYSTEM_LABEL_RESULT',     'R�sultat');


define ('_SYSTEM_MSG_CONFIRMGROUPDELETE', '�tes-vous certain de vouloir\nsupprimer ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMGROUPDETACH', '�tes-vous certain de vouloir\nd�tacher ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMUSERDETACH', '�tes-vous certain de vouloir\nd�tacher cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMUSERDELETE', '�tes-vous certain de vouloir\nsupprimer cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMROLEDELETE', '�tes-vous certain de vouloir\nsupprimer ce R�le ?');
define ('_SYSTEM_MSG_CONFIRMRULEDELETE', '�tes-vous certain de vouloir\nsupprimer cette r�gle ?');
define ('_SYSTEM_MSG_CONFIRMPROFILEDELETE','�tes-vous certain de vouloir\nsupprimer ce Profil ?');
define ('_SYSTEM_MSG_CONFIRMLOGDELETE', '�tes-vous certain de vouloir\nsupprimer les Logs ?');
define ('_SYSTEM_MSG_PASSWORDERROR', 'Erreur lors de la saisie du mot de passe.\nVous devez saisir deux fois le mot de passe');
define ('_SYSTEM_MSG_LOGINERROR', 'Erreur lors de la cr�ation de l\'utilisateur.\nCe login existe d�j�.');
define ('_SYSTEM_MSG_LOGINPASSWORDERROR', 'Erreur lors de la saisie du mot de passe.<BR>Votre mot de passe a �t� rejet� par le syst�me');

define ('_SYSTEM_MSG_CONFIRMMODULEDETACH', '�tes-vous certain de vouloir d�tacher ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMODULEDELETE', '�tes-vous certain de vouloir supprimer ce Module ?');
define ('_SYSTEM_MSG_CONFIRMHOMEPAGERESET', '�tes-vous certain de vouloir revenir � la page d\\\'accueil par d�faut ?');

define ('_SYSTEM_MSG_CONFIRMMODULEUNINSTAL', '�tes-vous certain de vouloir d�sinstaller ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMBUPDATE', '�tes-vous certain de vouloir mettre � jour la m�tabase de ce module ?\n(Rechargement du fichier XML)');

define ('_SYSTEM_MSG_INFODELETE_USERS', 'Vous ne pouvez pas supprimer ce groupe car il contient des utilisateurs');
define ('_SYSTEM_MSG_INFODELETE_GROUPS', 'Vous ne pouvez pas supprimer ce groupe car il contient des sous-groupes');

define ('_SYSTEM_MSG_CANTCOPYGROUP', 'Vous n\\\'avez pas les droits suffisants pour cloner cet espace');




define ('_SYSTEM_EXPLAIN_ABOUT', 'Ploopi est un produit d�velopp� par la soci�t� <A TARGET=\'blank\' HREF=\'http://www.netlorconcept.com\'>Netlor Concept</A><br>Le logo ainsi que la marque sont d�pos�s et appartiennent � la soci�t� Netlor Concept.');

define ('_SYSTEM_EXPLAIN_MODULENAME', 'Nom que portera le module dans l\'interface');
define ('_SYSTEM_EXPLAIN_ACTIVE', 'D�termine si le module est activ� ou non (activ� = utilisable)');
define ('_SYSTEM_EXPLAIN_PUBLIC', 'D�termine si le module est public ou non (visible par un utilisateur non connect�)');
define ('_SYSTEM_EXPLAIN_AUTOCONNECT', 'D�termine si ce module est affich� par d�faut � la connexion de l\'utilisateur');
define ('_SYSTEM_EXPLAIN_SHARED', 'D�termine si ce module est partag� pour les sous-groupes');
define ('_SYSTEM_EXPLAIN_HERITED', 'D�termine si ce module est automatiquement h�rit� aux sous-groupes');
define ('_SYSTEM_EXPLAIN_ADMINRESTRICTED', 'Si l\'administration du module est restreinte, les administrateurs des sous-groupes qui utilisent ce module auront des droits limit�s');
define ('_SYSTEM_EXPLAIN_VIEWMODE', 'Choix de la vue qu\'ont les utilisateurs sur les donn�es du module, <b>Priv�e</b> : les donn�es ne sont vues que par le groupe, <b>Descendante</b> : les donn�es sont vues par le groupe et les sous-groupes (les donn�es � descendent �), <b>Ascendante</b> : les donn�es sont vues par le groupe et les groupes parents (les donn�es � montent �), <b>Globale</b> : les donn�es sont enti�rement partag�es');

define ('_SYSTEM_LABEL_PHPINFO', 'Config - PhpInfo');
define ('_SYSTEM_LABEL_DIAGNOSTIC', 'Config - Diagnostic');
define ('_SYSTEM_LABEL_CONNECTEDUSERS', 'Logs - Utilisateurs Connect�s');
define ('_SYSTEM_LABEL_SQLDUMP', 'SqlDump');
define ('_SYSTEM_LABEL_ZIP', 'Zip');
define ('_SYSTEM_LABEL_BACKUP', 'Sauvegarde');
define ('_SYSTEM_LABEL_ACTIONHISTORY', 'Logs - Historique des Actions');
define ('_SYSTEM_LABEL_SERVERLOAD', 'Logs - Charge du Serveur');

define ('_SYSTEM_EXPLAIN_PHPINFO', 'L\'outil � '._SYSTEM_LABEL_PHPINFO.' � affiche la configuration PHP du serveur');
define ('_SYSTEM_EXPLAIN_DIAGNOSTIC', 'L\'outil � '._SYSTEM_LABEL_DIAGNOSTIC.' � v�rifie quelques sources d\'erreurs courantes pouvant entra�ner des dysfonctionnements de PLOOPI');
define ('_SYSTEM_EXPLAIN_CONNECTEDUSERS', 'L\'outil � '._SYSTEM_LABEL_CONNECTEDUSERS.' � affiche la liste des utilisateurs connect�s au site en temps r�el');
define ('_SYSTEM_EXPLAIN_SQLDUMP', 'L\'outil � '._SYSTEM_LABEL_SQLDUMP.' � vous permet de t�l�charger les donn�es dans un fichier SQL. Attention cette fonctionnalit� ne remplace pas un dump classique. Il est �galement recommand� de n\'utiliser cette fonctionnalit� que sur des petites bases de donn�es.');
define ('_SYSTEM_EXPLAIN_ZIP', 'L\'outil � Zip � vous permet de t�l�charger les sources de PLOOPI dans un fichier ZIP');
define ('_SYSTEM_EXPLAIN_BACKUP', 'L\'outil � Sauvegarde � vous permet de cr�er une sauvegarde compl�te du syst�me (donn�es + sources) afin de les restaurer � une date ult�rieure');
define ('_SYSTEM_EXPLAIN_ACTIONHISTORY', 'L\'outil � '._SYSTEM_LABEL_ACTIONHISTORY.' � vous permet de consulter les actions effectu�es par les utilisateurs');
define ('_SYSTEM_EXPLAIN_SERVERLOAD', 'L\'outil � '._SYSTEM_LABEL_SERVERLOAD.' � affiche la charge du serveur sur plusieurs intervalles de temps');


define ('_SYSTEM_LABEL_HOMEPAGECONTENT',    'Contenu de la Page d\'Accueil');
define ('_SYSTEM_LABEL_PREVIEW',        'Pr�visualiser');
define ('_SYSTEM_LABEL_ADDLINE',        'Ajouter une ligne');
define ('_SYSTEM_LABEL_ADDCOLUMN',      'Ajouter une colonne');
define ('_SYSTEM_LABEL_NBCOLUMNS',      'Nombre de colonnes');
define ('_SYSTEM_LABEL_TITLE', 'Titre');
define ('_SYSTEM_LABEL_MODULE', 'Module');
define ('_SYSTEM_LABEL_UNDEFINEDCONTENT', 'Contenu non d�fini');
define ('_SYSTEM_LABEL_UNDEFINED', 'Non d�fini');
define ('_SYSTEM_LABEL_SIZE', 'Taille');
define ('_SYSTEM_LABEL_VISIBILITY', 'Visibilit�');

define ('_SYSTEM_LABEL_MODULEPARAMETERS', 'Param�tres des Modules');

define ('_SYSTEM_LABEL_BACKTODEFAULTPAGE', 'Revenir � la page par d�faut');
define ('_SYSTEM_LABEL_MODIFYMYHOMEPAGE', 'Modifier ma page d\'accueil');

define ('_SYSTEM_LABEL_MODULEINSTANCIATION', 'Instanciation d\'un Module � <LABEL> �');
define ('_SYSTEM_LABEL_MODULEDELETE', 'Suppression du Module � <LABEL> �');
define ('_SYSTEM_LABEL_MODULEUNINSTALL', 'D�installation du Module � <LABEL> �');
define ('_SYSTEM_LABEL_USERDETACH', 'Suppression de l\'utilisateur � <LABELUSER> � du groupe � <LABELGROUP> �');
define ('_SYSTEM_LABEL_USERDELETE', 'Suppression de l\'utilisateur � <LABEL> �');
define ('_SYSTEM_LABEL_USERCREATE', 'Cr�ation de l\'utilisateur � <LABEL> �');


define ('_SYSTEM_LABEL_IMPORTSRC', 'Source d\'import');


define ('_SYSTEM_LABEL_TICKETS', 'Messages');
define ('_SYSTEM_LABEL_SEARCH', 'Recherche');


define ('_SYSTEM_LABEL_MYTICKETS', 'Mes Messages');
define ('_SYSTEM_LABEL_TICKETS_SENDBOX', 'Messsages envoy�s');
define ('_SYSTEM_LABEL_TICKETS_INCOMINGBOX', 'Messsages re�us');
define ('_SYSTEM_LABEL_TICKETS_WAITINGVALIDATION', 'Messages en attente de validation');
define ('_SYSTEM_LABEL_TICKETS_TOVALIDATE', 'Messages � valider');
define ('_SYSTEM_LABEL_TICKETS_ALL', 'Tous les messages');
define ('_SYSTEM_LABEL_NOTICKETS', 'Aucun message');

?>
