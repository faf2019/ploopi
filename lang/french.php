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
 * Fichier de langue 'fran�ais' du portail
 * 
 * @package ploopi
 * @subpackage lang
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * 
 * @global array $ploopi_agenda_days tableau des jours en fran�ais
 * @global array $ploopi_agenda_months tableau des mois en fran�ais
 * @global array $ploopi_errormsg tableau des messages d'erreur
 */

/**
 * D�finition des constantes
 */

define ('_PLOOPI_ADD', 'Ajouter');
define ('_PLOOPI_MODIFY', 'Modifier');
define ('_PLOOPI_DELETE', 'Supprimer');
define ('_PLOOPI_CANCEL', 'Annuler');
define ('_PLOOPI_SAVE', 'Enregistrer');
define ('_PLOOPI_SEARCH', 'Rechercher');
define ('_PLOOPI_RESET', 'R�initialiser');
define ('_PLOOPI_FILTER', 'Filtrer');
define ('_PLOOPI_EXECUTE', 'Ex�cuter');
define ('_PLOOPI_COMPLETE', 'Terminer');
define ('_PLOOPI_SEND', 'Envoyer');
define ('_PLOOPI_CONTINUE', 'Continuer');
define ('_PLOOPI_UPDATE', 'Mettre � Jour');
define ('_PLOOPI_BACK', 'Retour');
define ('_PLOOPI_YES', 'Oui');
define ('_PLOOPI_NO', 'Non');

define ('_PLOOPI_CONFIRM', '�tes-vous certain ?');

define ('_PLOOPI_MODULE_MANAGEMENT', 'Administration des Modules');
define ('_PLOOPI_GENERAL_ADMINISTRATION', 'Administration');
define ('_PLOOPI_ADMIN_USERS', 'Utilisateurs');
define ('_PLOOPI_ADMIN_MODULES', 'Modules');
define ('_PLOOPI_ADMIN_TICKETS', 'Messages');
define ('_PLOOPI_ADMIN_SYSTEM', 'Syst�me');
define ('_PLOOPI_ADMIN_WORKSPACES', 'Espaces de Travail');
define ('_PLOOPI_ADMIN_GROUPS', 'Groupes d\'Utilisateurs');

define ('_PLOOPI_UNKNOWNUSER', 'Utilisateur inconnu');

define ('_PLOOPI_USER', 'Utilisateur');
define ('_PLOOPI_LABEL_MODULES', 'Modules');

define ('_PLOOPI_LABEL_MYDATA', 'Mes Informations');
define ('_PLOOPI_LABEL_MYPROFILE', 'Mon Profil');
define ('_PLOOPI_LABEL_MYPARAMS', 'Mes Param�tres');
define ('_PLOOPI_LABEL_MYANNOTATIONS', 'Mes Annotations');
define ('_PLOOPI_LABEL_MYTICKETS', 'Mes Messages');
define ('_PLOOPI_LABEL_MYWORKSPACE', 'Mon Espace');
define ('_PLOOPI_LABEL_WORKSPACES', 'Espaces de Travail');
define ('_PLOOPI_LABEL_SEARCH', 'Recherche');

define ('_PLOOPI_LABEL_LOGIN', 'Login');
define ('_PLOOPI_LABEL_PASSWORD', 'Password');
define ('_PLOOPI_LABEL_DISCONNECTION', 'D�connexion');
define ('_PLOOPI_LABEL_ABOUT', 'A Propos');
define ('_PLOOPI_LABEL_USERS', 'Utilisateurs');
define ('_PLOOPI_LABEL_CONNECTEDUSERS', 'Connect�(s)');
define ('_PLOOPI_LABEL_ANONYMOUSUSERS', 'Anonyme(s)');

define ('_PLOOPI_NONE', '- Aucun -');
define ('_PLOOPI_ALL', '- Tout -');

define ('_PLOOPI_ERROR', 'Erreur');

define ('_PLOOPI_LABEL_VIEWMODE_UNDEFINED',     'Non d�fini');
define ('_PLOOPI_LABEL_VIEWMODE_PRIVATE',   'Priv�e');
define ('_PLOOPI_LABEL_VIEWMODE_DESC',      'Descendante');
define ('_PLOOPI_LABEL_VIEWMODE_ASC',       'Ascendante');
define ('_PLOOPI_LABEL_VIEWMODE_GLOBAL',        'Globale');

define ('_PLOOPI_LEVEL_USER',       'Utilisateur');
define ('_PLOOPI_LEVEL_GROUPMANAGER',   'Gestionnaire d\'Espace');
define ('_PLOOPI_LEVEL_GROUPADMIN',     'Administrateur d\'Espace');
define ('_PLOOPI_LEVEL_SYSTEMADMIN',    'Administrateur Syst�me');

define ('_PLOOPI_JS_EMAIL_ERROR', "L'adresse m�l n'est pas valide.\\nUne adresse m�l valide est du type \'adresse@domaine.com\'");
define ('_PLOOPI_JS_STRING_ERROR',  "Le champ '<FIELD_LABEL>' ne doit pas �tre vide");
define ('_PLOOPI_JS_INT_ERROR',     "Le champ '<FIELD_LABEL>' doit �tre un nombre entier valide\\n(ex: 321 ou -321)");
define ('_PLOOPI_JS_FLOAT_ERROR',   "Le champ '<FIELD_LABEL>' doit �tre un nombre r�el valide\\n(ex: 123.45 ou -123.45)");
define ('_PLOOPI_JS_DATE_ERROR',    "Le champ '<FIELD_LABEL>' doit �tre une date valide\\n(ex: 06/07/1977)");
define ('_PLOOPI_JS_TIME_ERROR',    "Le champ '<FIELD_LABEL>' doit �tre une heure valide");
define ('_PLOOPI_JS_CHECK_ERROR',   "Vous devez s�lectionner une valeur pour le champ '<FIELD_LABEL>'");
define ('_PLOOPI_JS_COLOR_ERROR',   "Le champ '<FIELD_LABEL>' doit �tre une couleur valide\\n(ex: #ffff00 / jaune / yellow)");
define ('_PLOOPI_JS_PHONE_ERROR',   "Le champ '<FIELD_LABEL>' doit �tre un num�ro de t�l�phone valide\\n(ex: +33 1 02 03 04 05 ou 0102030405)");

define ('_PLOOPI_LABEL_NEWTICKET', 'Nouveau message');
define ('_PLOOPI_LABEL_TICKET_VALIDATIONREQUIRED', 'Validation requise<br />(optionnel, permet de demander au(x) destinataire(s) de valider le message)');
define ('_PLOOPI_LABEL_TICKET_LINKEDOBJECT', 'Objet li�');
define ('_PLOOPI_LABEL_TICKET_TITLE', 'Titre');
define ('_PLOOPI_LABEL_TICKET_MESSAGE', 'Message');
define ('_PLOOPI_LABEL_TICKET_RECIPIENT', 'Destinataire');
define ('_PLOOPI_LABEL_TICKET_RECIPIENTS', 'Destinataires');
define ('_PLOOPI_LABEL_TICKET_MODIFICATION', 'Modification d\'un message');
define ('_PLOOPI_LABEL_TICKET_RESPONSE', 'R�ponse � un message');
define ('_PLOOPI_LABEL_TICKET_UNKNOWN_USER', 'Utilisateur inconnu');
define ('_PLOOPI_LABEL_TICKET_RECIPIENTSEARCH', 'Recherche destinataires');
define ('_PLOOPI_LABEL_TICKET_DELETERECIPIENT', 'Supprimer ce destinataire');

define ('_PLOOPI_LABEL_TICKET_DELETE_CHECKED', 'Supprimer les messages s�lectionn�s');
define ('_PLOOPI_LABEL_TICKET_CONFIRMDELETE', '�tes-vous certain de vouloir supprimer ce message ?');
define ('_PLOOPI_LABEL_TICKET_CONFIRMDELETE_CHECKED', '�tes-vous certain de vouloir supprimer les messages coch�s ?');



define ('_PLOOPI_LABEL_SUBSCRIPTION_DESCIPTION', 'S�lectionnez les actions pour lesquelles vous souhaitez �tre abonn�.<br />L\'abonnement vous permet de recevoir un message lorsqu\'une action est effectu�e sur un objet ou un ensemble d\'objets h�rit�s.');
define ('_PLOOPI_LABEL_SUBSCRIPTION_SAVED', 'Abonnement enregistr�');
define ('_PLOOPI_LABEL_SUBSCRIPTION_DELETE', 'D�sabonnement enregistr�');
define ('_PLOOPI_LABEL_SUBSCRIPTION_ALLACTIONS', 'Toutes les actions');
define ('_PLOOPI_LABEL_SUBSCRIPTION_UNSUSCRIBE', 'Se d�sabonner');


/**
 * Tableau des jours en fran�ais
 */
global $ploopi_days;

/**
 * Tableau des mois en fran�ais
 */
global $ploopi_months;

/**
 * Tableau des messages d'erreur
 */
global $ploopi_errormsg;

/**
 * Tableau des civilit�s
 */
global $ploopi_civility;


$ploopi_days = 
    array (
        'Dimanche', 
        'Lundi', 
        'Mardi', 
        'Mercredi', 
        'Jeudi', 
        'Vendredi', 
        'Samedi', 
        'Dimanche'
    ); 

$ploopi_months = 
    array(
        1 => 'Janvier',
        2 => 'F�vrier',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Ao�t',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'D�cembre'
    );

$ploopi_civility = 
    array (
        'M',
        'Mme', 
        'Mlle'
    ); 

$ploopi_errormsg[_PLOOPI_ERROR_NOWORKSPACEDEFINED]  = 'Aucun espace de travail n\'est d�fini pour cet utilisateur';
$ploopi_errormsg[_PLOOPI_ERROR_LOGINERROR]          = 'Utilisateur ou Mot de passe incorrect';
$ploopi_errormsg[_PLOOPI_ERROR_LOGINEXPIRE]         = 'Votre mot de passe a expir�';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONEXPIRE]       = 'Votre session a expir�';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONINVALID]      = 'Votre session est invalide';

?>
