<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Fichier de langue 'français' du portail
 *
 * @package ploopi
 * @subpackage lang
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @global array $ploopi_days tableau des jours en français
 * @global array $ploopi_months tableau des mois en français
 * @global array $ploopi_errormsg tableau des messages d'erreur
 */

/**
 * Définition des constantes
 */

define ('_PLOOPI_ADD', 'Ajouter');
define ('_PLOOPI_MODIFY', 'Modifier');
define ('_PLOOPI_DELETE', 'Supprimer');
define ('_PLOOPI_CANCEL', 'Annuler');
define ('_PLOOPI_SAVE', 'Enregistrer');
define ('_PLOOPI_SEARCH', 'Rechercher');
define ('_PLOOPI_RESET', 'Réinitialiser');
define ('_PLOOPI_FILTER', 'Filtrer');
define ('_PLOOPI_EXECUTE', 'Exécuter');
define ('_PLOOPI_COMPLETE', 'Terminer');
define ('_PLOOPI_SEND', 'Envoyer');
define ('_PLOOPI_CONTINUE', 'Continuer');
define ('_PLOOPI_UPDATE', 'Mettre à Jour');
define ('_PLOOPI_BACK', 'Retour');
define ('_PLOOPI_YES', 'Oui');
define ('_PLOOPI_NO', 'Non');

define ('_PLOOPI_CONFIRM', 'Êtes-vous certain ?');

define ('_PLOOPI_MODULE_MANAGEMENT', 'Administration des Modules');
define ('_PLOOPI_GENERAL_ADMINISTRATION', 'Administration');
define ('_PLOOPI_ADMIN_USERS', 'Utilisateurs');
define ('_PLOOPI_ADMIN_MODULES', 'Modules');
define ('_PLOOPI_ADMIN_TICKETS', 'Messages');
define ('_PLOOPI_ADMIN_SYSTEM', 'Système');
define ('_PLOOPI_ADMIN_WORKSPACES', 'Espaces de Travail');
define ('_PLOOPI_ADMIN_GROUPS', 'Groupes d\'Utilisateurs');

define ('_PLOOPI_UNKNOWNUSER', 'Utilisateur inconnu');

define ('_PLOOPI_USER', 'Utilisateur');
define ('_PLOOPI_LABEL_MODULES', 'Modules');

define ('_PLOOPI_LABEL_MYDATA', 'Mes Informations');
define ('_PLOOPI_LABEL_MYPROFILE', 'Mon Profil');
define ('_PLOOPI_LABEL_MYPARAMS', 'Mes Paramètres');
define ('_PLOOPI_LABEL_MYANNOTATIONS', 'Mes Annotations');
define ('_PLOOPI_LABEL_MYTICKETS', 'Mes Messages');
define ('_PLOOPI_LABEL_MYWORKSPACE', 'Mon Espace');
define ('_PLOOPI_LABEL_WORKSPACES', 'Espaces de Travail');
define ('_PLOOPI_LABEL_SEARCH', 'Recherche');

define ('_PLOOPI_LABEL_LOGIN', 'Login');
define ('_PLOOPI_LABEL_PASSWORD', 'Password');
define ('_PLOOPI_LABEL_DISCONNECTION', 'Déconnexion');
define ('_PLOOPI_LABEL_ABOUT', 'A Propos');
define ('_PLOOPI_LABEL_USERS', 'Utilisateurs');
define ('_PLOOPI_LABEL_CONNECTEDUSERS', 'Connecté(s)');
define ('_PLOOPI_LABEL_ANONYMOUSUSERS', 'Anonyme(s)');

define ('_PLOOPI_NONE', '- Aucun -');
define ('_PLOOPI_ALL', '- Tout -');

define ('_PLOOPI_ERROR', 'Erreur');

define ('_PLOOPI_LABEL_VIEWMODE_UNDEFINED',     'Non défini');
define ('_PLOOPI_LABEL_VIEWMODE_PRIVATE',   'Privée');
define ('_PLOOPI_LABEL_VIEWMODE_DESC',      'Descendante');
define ('_PLOOPI_LABEL_VIEWMODE_ASC',       'Ascendante');
define ('_PLOOPI_LABEL_VIEWMODE_GLOBAL',        'Globale');
define ('_PLOOPI_LABEL_VIEWMODE_ASCDESC',        'Ascendante & Descendante');

define ('_PLOOPI_LEVEL_USER',       'Utilisateur');
define ('_PLOOPI_LEVEL_GROUPMANAGER',   'Gestionnaire d\'Espace');
define ('_PLOOPI_LEVEL_GROUPADMIN',     'Administrateur d\'Espace');
define ('_PLOOPI_LEVEL_SYSTEMADMIN',    'Administrateur Système');

define ('_PLOOPI_JS_EMAIL_ERROR', "L'adresse mèl n'est pas valide.\\nUne adresse mèl valide est du type \'adresse@domaine.com\'");
define ('_PLOOPI_JS_STRING_ERROR',  "Le champ '<FIELD_LABEL>' ne doit pas être vide");
define ('_PLOOPI_JS_INT_ERROR',     "Le champ '<FIELD_LABEL>' doit être un nombre entier valide\\n(ex: 321 ou -321)");
define ('_PLOOPI_JS_FLOAT_ERROR',   "Le champ '<FIELD_LABEL>' doit être un nombre réel valide\\n(ex: 123.45 ou -123.45)");
define ('_PLOOPI_JS_DATE_ERROR',    "Le champ '<FIELD_LABEL>' doit être une date valide\\n(ex: 06/07/1977)");
define ('_PLOOPI_JS_TIME_ERROR',    "Le champ '<FIELD_LABEL>' doit être une heure valide");
define ('_PLOOPI_JS_CHECK_ERROR',   "Vous devez sélectionner une valeur pour le champ '<FIELD_LABEL>'");
define ('_PLOOPI_JS_ONECHECK_ERROR',"Vous devez cocher le champ '<FIELD_LABEL>'");
define ('_PLOOPI_JS_COLOR_ERROR',   "Le champ '<FIELD_LABEL>' doit être une couleur valide\\n(ex: #ffff00 / jaune / yellow)");
define ('_PLOOPI_JS_PHONE_ERROR',   "Le champ '<FIELD_LABEL>' doit être un numéro de téléphone valide\\n(ex: +33 1 02 03 04 05 ou 0102030405)");
define ('_PLOOPI_JS_CAPTCHA_ERROR',   "Le code de contrôle entré est incorrect.");
define ('_PLOOPI_JS_WEB_ERROR',     "Le champ '<FIELD_LABEL>' doit être une URL valide\\n(ex: http://www.ploopi.org)");

define ('_PLOOPI_ERROR_TEMPLATE_FILE', "Le fichier <FILE> du template <TEMPLATE> n'a pas pu être chargé");

define ('_PLOOPI_LABEL_NEWTICKET', 'Nouveau message');
define ('_PLOOPI_LABEL_TICKET_VALIDATIONREQUIRED', 'Validation requise<br />(optionnel, permet de demander au(x) destinataire(s) de valider le message)');
define ('_PLOOPI_LABEL_TICKET_LINKEDOBJECT', 'Objet lié');
define ('_PLOOPI_LABEL_TICKET_TITLE', 'Titre');
define ('_PLOOPI_LABEL_TICKET_MESSAGE', 'Message');
define ('_PLOOPI_LABEL_TICKET_RECIPIENT', 'Destinataire');
define ('_PLOOPI_LABEL_TICKET_RECIPIENTS', 'Destinataires');
define ('_PLOOPI_LABEL_TICKET_MODIFICATION', 'Modification d\'un message');
define ('_PLOOPI_LABEL_TICKET_RESPONSE', 'Réponse à un message');
define ('_PLOOPI_LABEL_TICKET_UNKNOWN_USER', 'Utilisateur inconnu');
define ('_PLOOPI_LABEL_TICKET_RECIPIENTSEARCH', 'Recherche destinataires');
define ('_PLOOPI_LABEL_TICKET_DELETERECIPIENT', 'Supprimer ce destinataire');

define ('_PLOOPI_LABEL_TICKET_DELETE_CHECKED', 'Supprimer les messages sélectionnés');
define ('_PLOOPI_LABEL_TICKET_CONFIRMDELETE', 'Êtes-vous certain de vouloir supprimer ce message ?');
define ('_PLOOPI_LABEL_TICKET_CONFIRMDELETE_CHECKED', 'Êtes-vous certain de vouloir supprimer les messages cochés ?');

define ('_PLOOPI_LABEL_SUBSCRIPTION_DESCIPTION', 'Sélectionnez les actions pour lesquelles vous souhaitez être abonné.<br />L\'abonnement vous permet de recevoir un message lorsqu\'une action est effectuée sur un objet ou un ensemble d\'objets hérités.');
define ('_PLOOPI_LABEL_SUBSCRIPTION_SAVED', 'Abonnement enregistré');
define ('_PLOOPI_LABEL_SUBSCRIPTION_DELETE', 'Désabonnement enregistré');
define ('_PLOOPI_LABEL_SUBSCRIPTION_ALLACTIONS', 'Toutes les actions');
define ('_PLOOPI_LABEL_SUBSCRIPTION_UNSUSCRIBE', 'Se désabonner');

/**
 * Tableau des jours en français
 */
global $ploopi_days;

/**
 * Tableau des mois en français
 */
global $ploopi_months;

/**
 * Tableau des messages d'erreur
 */
global $ploopi_errormsg;

/**
 * Tableau des messages
 */
global $ploopi_msg;

/**
 * Tableau des civilités
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
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre'
    );

$ploopi_civility =
    array (
        'M',
        'Mme',
        'Mlle'
    );

$ploopi_type_file =
    array (
        'archive'       => 'Archive',
        'audio'         => 'Audio',
        'calendar'      => 'Calendrier',
        'cd'            => 'Image Disque',
        'certificate'   => 'Certificat',
        'document'      => 'Document',
        'exec'          => 'Executable',
        'html'          => 'HTML',
        'image'         => 'Image',
        'package'       => 'Pack',
        'presentation'  => 'Présentation',
        'script'        => 'Script',
        'spreadsheet'   => 'Tableur',
        'text'          => 'Texte',
        'video'         => 'Vidéo'
    );


$ploopi_errormsg[_PLOOPI_ERROR_NOWORKSPACEDEFINED]  = 'Aucun espace de travail n\'est défini pour cet utilisateur';
$ploopi_errormsg[_PLOOPI_ERROR_LOGINERROR]          = 'Utilisateur ou mot de passe incorrect';
$ploopi_errormsg[_PLOOPI_ERROR_PASSWORDEXPIRE]      = 'Votre mot de passe a expiré';
$ploopi_errormsg[_PLOOPI_ERROR_PASSWORDRESET]       = 'Vous devez redéfinir votre mot de passe';
$ploopi_errormsg[_PLOOPI_ERROR_PASSWORDERROR]       = 'Les deux saisies ne correspondent pas';
$ploopi_errormsg[_PLOOPI_ERROR_PASSWORDINVALID]     = 'Le mot de passe est invalide, il doit contenir au moins '._PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE.' caractères, un caractère minuscule, un caractère majuscule, un chiffre et un caractère de ponctuation';
$ploopi_errormsg[_PLOOPI_ERROR_ACCOUNTEXPIRE]       = 'Votre identifiant a expiré';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONEXPIRE]       = 'Votre session a expiré';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONINVALID]      = 'Votre session est invalide';
$ploopi_errormsg[_PLOOPI_ERROR_INVALIDTOKEN]        = 'Ce lien a expiré, vous devez vous reconnecter';
$ploopi_errormsg[_PLOOPI_ERROR_ACCOUNTJAILED]       = 'Trop de tentatives de connexion ont échoué. Ce compte est suspendu. Vous pourrez tenter une nouvelle connexion plus tard.';


$ploopi_errormsg[_PLOOPI_ERROR_LOSTPASSWORD_UNKNOWN]      = 'Ce compte est inconnu';
$ploopi_errormsg[_PLOOPI_ERROR_LOSTPASSWORD_INVALID]      = 'Ce compte n\'a pas d\'adresse de courriel valide';
$ploopi_errormsg[_PLOOPI_ERROR_LOSTPASSWORD_MANYRESPONSES] = 'Ce compte n\'est pas unique';

$ploopi_msg[_PLOOPI_MSG_MAILSENT]      = 'Un message vous a été envoyé';
$ploopi_msg[_PLOOPI_MSG_PASSWORDSENT]  = 'Un nouveau mot de passe vous a été envoyé';

?>
