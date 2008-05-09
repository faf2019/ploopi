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

define ('_PLOOPI_LEVEL_USER',       'Utilisateur');
define ('_PLOOPI_LEVEL_GROUPMANAGER',   'Gestionnaire d\'Espace');
define ('_PLOOPI_LEVEL_GROUPADMIN',     'Administrateur d\'Espace');
define ('_PLOOPI_LEVEL_SYSTEMADMIN',    'Administrateur Système');

define ('_PLOOPI_JS_EMAIL_ERROR_1', "L'adresse mèl n'est pas valide.\\nIl n'y a pas de caractère @\\nUne adresse mèl valide est du type \\\'adresse@domaine.com\\\'");
define ('_PLOOPI_JS_EMAIL_ERROR_2', "L'adresse mèl n'est pas valide.\\nIl ne peut pas y avoir un point (.) juste après @\\nUne adresse mèl valide est du type \\\'adresse@domaine.com\\\'");
define ('_PLOOPI_JS_EMAIL_ERROR_3', "L'adresse mèl n'est pas valide.\\nL'adresse mèl ne peut pas finir par un point (.)\\nUne adresse mèl valide est du type \\\'adresse@domaine.com\\\'");
define ('_PLOOPI_JS_EMAIL_ERROR_4', "L'adresse mèl n'est pas valide.\\nL'adresse mèl ne peut pas contenir 2 points (.) qui se suivent.\\nUne adresse mèl valide est du type \\\'adresse@domaine.com\\\'");
define ('_PLOOPI_JS_STRING_ERROR',  "Le champ '<FIELD_LABEL>' ne doit pas être vide");
define ('_PLOOPI_JS_INT_ERROR',     "Le champ '<FIELD_LABEL>' doit être un nombre entier valide");
define ('_PLOOPI_JS_FLOAT_ERROR',   "Le champ '<FIELD_LABEL>' doit être un nombre réel valide");
define ('_PLOOPI_JS_DATE_ERROR',    "Le champ '<FIELD_LABEL>' doit être une date valide");
define ('_PLOOPI_JS_TIME_ERROR',    "Le champ '<FIELD_LABEL>' doit être une heure valide");
define ('_PLOOPI_JS_CHECK_ERROR',   "Vous devez sélectionner une valeur pour le champ '<FIELD_LABEL>'");
define ('_PLOOPI_JS_COLOR_ERROR',   "Le champ '<FIELD_LABEL>' doit être une couleur valide (#ffff00 / jaune / yellow)");

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



global $ploopi_agenda_days;
global $ploopi_agenda_months;
global $ploopi_timezone;
global $ploopi_errormsg;

$ploopi_agenda_days[1] = 'Lundi';
$ploopi_agenda_days[2] = 'Mardi';
$ploopi_agenda_days[3] = 'Mercredi';
$ploopi_agenda_days[4] = 'Jeudi';
$ploopi_agenda_days[5] = 'Vendredi';
$ploopi_agenda_days[6] = 'Samedi';
$ploopi_agenda_days[0] = 'Dimanche';

$ploopi_agenda_months[1] = 'Janvier';
$ploopi_agenda_months[2] = 'Février';
$ploopi_agenda_months[3] = 'Mars';
$ploopi_agenda_months[4] = 'Avril';
$ploopi_agenda_months[5] = 'Mai';
$ploopi_agenda_months[6] = 'Juin';
$ploopi_agenda_months[7] = 'Juillet';
$ploopi_agenda_months[8] = 'Août';
$ploopi_agenda_months[9] = 'Septembre';
$ploopi_agenda_months[10] = 'Octobre';
$ploopi_agenda_months[11] = 'Novembre';
$ploopi_agenda_months[12] = 'Décembre';

$ploopi_errormsg[_PLOOPI_ERROR_NOWORKSPACEDEFINED]  = 'Aucun espace de travail n\'est défini pour cet utilisateur';
$ploopi_errormsg[_PLOOPI_ERROR_LOGINERROR]          = 'Utilisateur ou Mot de passe incorrect';
$ploopi_errormsg[_PLOOPI_ERROR_LOGINEXPIRE]         = 'Votre mot de passe a expiré';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONEXPIRE]       = 'Votre session a expiré';
$ploopi_errormsg[_PLOOPI_ERROR_SESSIONINVALID]      = 'Votre session est invalide';

?>
