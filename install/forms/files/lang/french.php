<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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
 * Fichier de langue fran�ais
 *
 * @package forms
 * @subpackage lang
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * D�finition des constantes
 */

define ('_FORMS_LABELTAB_LIST',     'Liste des Formulaires');
define ('_FORMS_LABELTAB_ADD',  'Cr�ation d\'un Formulaire');

define ('_FORMS_ADMIN',     'Administration');

define ('_FORMS_LABEL',     'Libell�');
define ('_FORMS_TABLENAME',     'Nom de la Table');
define ('_FORMS_DESCRIPTION',   'Description');
define ('_FORMS_RESPONSE',      'R�ponse apr�s Validation (WebEdit)');
define ('_FORMS_PUBDATESTART',  'Date de d�but de Publication');
define ('_FORMS_PUBDATEEND',    'Date de fin de Publication');
define ('_FORMS_EMAIL',     'Envoyer par email � ');
define ('_FORMS_EMAIL_EXPLAIN', '(Destinataires : Plusieurs adresses possibles s�par�es par des points-virgules et sans espace.)');
define ('_FORMS_FROM',          'Exp�diteur de l\'email');
define ('_FORMS_WIDTH',     'Largeur (*: variable)');
define ('_FORMS_ADD',           'Cr�ation d\'un Formulaire');
define ('_FORMS_MODIFICATION',  'Modification du Formulaire');
define ('_FORMS_FIELDLIST',     'Liste des Champs');
define ('_FORMS_ADDFIELD',      'Ajouter un Champ');
define ('_FORMS_ADDSEPARATOR',  'Ajouter un S�parateur');
define ('_FORMS_ADDCAPTCHA',  'Ajouter un Captcha');
define ('_FORMS_LIST',      'Liste des Formulaires');
define ('_FORMS_FILL',      'Utiliser le Formulaire');
define ('_FORMS_MODEL',     'Mod�le d\'Affichage');
define ('_FORMS_TYPEFORM',  'Type de Formulaire');
define ('_FORMS_AUTOBACKUP', 'Archivage Automatique (En Jours)');
define ('_FORMS_OBLIGATORY', 'Obligatoire');
define ('_FORMS_GRAPHICLIST',     'Liste des Graphiques');
define ('_FORMS_ADDGRAPHIC',      'Ajouter un Graphique');
define ('_FORMS_EXPORT_TO_TABLE',    'Stockage physique pour synchro DBREPORT');
define ('_FORMS_STYLE',       		'Style formulaire (optionnel)<br /><em>ex : width:500px; </em>');

define ('_FORMS_FIELDCREATION',         'Ajout d\'un Champ');
define ('_FORMS_SEPARATORCREATION',     'Ajout d\'un S�parateur');
define ('_FORMS_CAPTCHACREATION',     'Ajout d\'un Captcha');
define ('_FORMS_GRAPHICCREATION',     'Ajout d\'un Graphique');

define ('_FORMS_FIELDMODIFICATION',         'Modification d\'un Champ');
define ('_FORMS_SEPARATORMODIFICATION',     'Modification d\'un S�parateur');
define ('_FORMS_CAPTCHAMODIFICATION',       'Modification du Captcha');
define ('_FORMS_GRAPHICMODIFICATION',         'Modification d\'un Graphique');

define ('_FORMS_USER',      'Utilisateur');
define ('_FORMS_WORKSPACE',     'Espace');
define ('_FORMS_IP',    'Adresse IP');
define ('_FORMS_MODULE',    'Module');
define ('_FORMS_DATEVALIDATION',    'Date de Validation');
define ('_FORMS_NBLINE',    'Nb Ligne/Page');


define ('_FORMS_PREVIEW',       'Aper�u');
define ('_FORMS_VIEWRESULT',        'Basculer vers l\'interface utilisateur');
define ('_FORMS_VIEWLIST',      'Mode Liste');

define ('_FORMS_FILTER',        'Filtre');
define ('_FORMS_EXPORT',        'Export');
define ('_FORMS_MOREDETAILS',       'Plus de d�tails');


define ('_FORMS_FIELD_NAME',        'Intitul�');
define ('_FORMS_FIELD_FIELDNAME',       'Nom du Champ');
define ('_FORMS_FIELD_POSITION',        'Position');
define ('_FORMS_FIELD_INTERLINE',       'Interligne');
define ('_FORMS_FIELD_DESCRIPTION', 'Description');
define ('_FORMS_FIELD_TYPE',        'Type');
define ('_FORMS_FIELD_FORMAT',      'Format');
define ('_FORMS_FIELD_VALUES',      'Valeurs');
define ('_FORMS_FIELD_MAXLENGTH',       'Longueur maximum (caract�res)');
define ('_FORMS_FIELD_STYLE_FORM',       'Style �l�ment (optionnel)<br /><em>ex : color:red; </em>');
define ('_FORMS_FIELD_STYLE_FIELD',       'Style champ (optionnel)<br /><em>ex : height:50px; </em>');
define ('_FORMS_FIELD_NEEDED',      'Saisie Requise');
define ('_FORMS_FIELD_FORMVIEW',      'Visible dans le Formulaire');
define ('_FORMS_FIELD_EXPORTVIEW',      'Visible en Export');
define ('_FORMS_FIELD_ARRAYVIEW',       'Visible en Liste');
define ('_FORMS_FIELD_ADMINONLY',      'Limit� aux administrateurs');
define ('_FORMS_FIELD_DEFAULTVALUE',        'Valeur par D�faut');
define ('_FORMS_FIELD_FORMFIELD',       'Formulaire / Champ');
define ('_FORMS_FIELD_WCEVIEW',         'Visible lors d\'une int�gration en objet WebEdit (frontoffice)');
define ('_FORMS_FIELD_FORMULA_EDITOR',         'Editeur de Formule');


define ('_FORMS_FIELD_SEPARATOR_LEVEL',         'Niveau du S�parateur');
define ('_FORMS_FIELD_SEPARATOR_FONTSIZE',      'Taille de Police (pix)');
define ('_FORMS_FIELD_SEPARATOR_DESC',      'S�parateur de Niveau <LEVEL>');

define ('_FORMS_FIELD_MULTICOLDISPLAY',         'Affichage multi-colonne');

define ('_FORMS_GRAPHIC_LABEL',       'Intitul�');
define ('_FORMS_GRAPHIC_TYPE',        'Type de graphique');
define ('_FORMS_GRAPHIC_DESCRIPTION', 'Description');
define ('_FORMS_GRAPHIC_LINE_AGGREGATION', 'Unit� de temps');
define ('_FORMS_GRAPHIC_DATASET', 'Jeu de donn�es');


define ('_FORMS_FIELD_NEEDED_SHORT',        'Req.');
define ('_FORMS_FIELD_FORMVIEW_SHORT',        'For.');
define ('_FORMS_FIELD_EXPORTVIEW_SHORT',        'Exp.');
define ('_FORMS_FIELD_ARRAYVIEW_SHORT',         'Lst.');
define ('_FORMS_FIELD_ADMINONLY_SHORT',      'Adm.');

define ('_FORMS_ALLREADYFILLED', 'Vous avez d�j� rempli ce formulaire');

define ('_FORMS_OPTION_ONLYONE',            'Une seule saisie par utilisateur');
define ('_FORMS_OPTION_ONLYONEDAY',         'Une seule saisie par jour');
define ('_FORMS_OPTION_USER_VIEW',      'L\'utilisateur peut consulter ses saisies');
define ('_FORMS_OPTION_GROUP_VIEW',         'Les utilisateurs du m�me espace peuvent consulter les saisies');
define ('_FORMS_OPTION_ALL_VIEW',       'Tous les utilisateurs peuvent consulter les saisies');
define ('_FORMS_OPTION_MODIFY',             'Droit de Modification');
define ('_FORMS_OPTION_MODIFY_NOBODY',      'Personne');
define ('_FORMS_OPTION_MODIFY_USER',        'Le propri�taire');
define ('_FORMS_OPTION_MODIFY_GROUP',       'L\'espace');
define ('_FORMS_OPTION_MODIFY_ALL',         'Tout le monde');
define ('_FORMS_OPTION_ADMINONLY',      'Uniquement visible par les administrateurs');


define ('_FORMS_OPTION_VIEW',               'Vue sur les donn�es');
define ('_FORMS_OPTION_VIEW_GLOBAL',        'Globale');
define ('_FORMS_OPTION_VIEW_PRIVATE',       'Priv�');
define ('_FORMS_OPTION_VIEW_ASC',           'Ascendante');
define ('_FORMS_OPTION_VIEW_DESC',          'Descendante');


define ('_FORMS_OPTION_DISPLAY_USER',       'Afficher � Utilisateur �');
define ('_FORMS_OPTION_DISPLAY_GROUP',      'Afficher � Espace �');
define ('_FORMS_OPTION_DISPLAY_DATE',       'Afficher � Date de Validation �');
define ('_FORMS_OPTION_DISPLAY_IP',         'Afficher � Adresse IP �');

define ('_FORMS_FIELDNEEDED', '* champs obligatoire');
define ('_FORMS_FILLEDBY', 'Rempli par');
define ('_FORMS_ANONYMOUS', 'Anonyme');

define ('_FORMS_HELP_EMAIL', 'Vous pouvez saisir plusieurs adresses s�par�es par le caract�re &laquo; ; &raquo;');
define ('_FORMS_HELP_TYPEFORM', 'Certaines options sont sp�cifiques au type de formulaire que vous cr�ez');
define ('_FORMS_HELP_SEARCH', 'Vous pouvez filtrer un champs sur une liste de valeurs (s�parateur &laquo; ; &raquo;) avec les op�rateur &laquo; = &raquo;, &laquo; Contient &raquo; et &laquo; Commence par &raquo;');

define ('_FORMS_MESS_OK_DEFAULT',   'Ok');
define ('_FORMS_MESS_OK_1',         'Formulaire enregistr� !');
define ('_FORMS_MESS_OK_2',         'Formulaire supprim� !');
define ('_FORMS_MESS_OK_3',         'Champ enregistr� !');
define ('_FORMS_MESS_OK_4',         'Champ supprim� !');
define ('_FORMS_MESS_OK_5',         'Graphique enregistr� !');
define ('_FORMS_MESS_OK_6',         'Graphique supprim� !');
define ('_FORMS_MESS_OK_7',         'Champ d�plac� !');

define ('_FORMS_ERROR_CAPTCHA',             'Erreur de captcha !'); // Front !

define ('_FORMS_ERROR',         'ERREUR');
define ('_FORMS_ERROR_DEFAULT', 'Erreur inconnue - Veuillez tenter de r�it�rer votre action.<br/><b>Si l\'erreur persiste, contactez l\'auteur du module concern�.</b>');
define ('_FORMS_ERROR_1',       'Vous ne disposez pas des autorisations n�cessaires pour effectuer cette action.');
define ('_FORMS_ERROR_2',       'Tentative de modification de l\'url.');
define ('_FORMS_ERROR_3',       'Vous ne pouvez ins�rer qu\'un seul captcha par formulaire');

?>
