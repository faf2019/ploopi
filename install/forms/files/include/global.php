<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2011 Ovensia
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
 * Fonctions, constantes, variables globales
 *
 * @package forms
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * D�finition des constantes
 */

/**
 * Action : Ajouter une r�ponse dans un formulaire
 */
define ("_FORMS_ACTION_ADDREPLY",   2);

/**
 * Action : Exporter les donn�es d'un formulaire
 */
define ("_FORMS_ACTION_EXPORT",     3);

/**
 * Action : Filtrer les donn�es d'un formulaire
 */
define ("_FORMS_ACTION_FILTER",     4);

/**
 * Action : Supprimer des enregistrements d'un formulaire
 */
define ("_FORMS_ACTION_DELETE",     5);

/**
 * Action : G�rer l'archivage des donn�es d'un formulaire
 */
define ("_FORMS_ACTION_BACKUP",     6);

/**
 * Action : Afficher les graphiques
 */
define ("_FORMS_ACTION_GRAPHICS",     7);

/**
 * Action : Importer des donn�es CSV
 */
define ("_FORMS_ACTION_IMPORT_CSV",     8);

/**
 * Action : Administrer les formulaires
 */
define ("_FORMS_ACTION_ADMIN",     99);


// Objet Form
define ('_FORMS_OBJECT_FORM',   1);


/**
 * D�finition des variables globales
 */

/**
 * Types de champs (text, textarea, select, etc...)
 */
global $field_types;

/**
 * Formats de champs (string, integer, date, etc...)
 */
global $field_formats;

/**
 * Op�rateurs sur les champs (=, >, <, etc...)
 */
global $field_operators;

/**
 * Types de formulaire (cms, app)
 */
global $form_types;

/**
 * Types de graphique
 */
global $forms_graphic_types;

/**
 * Types d'aggregation
 */
global $forms_graphic_line_aggregation;

/**
 * Types d'op�ration
 */
global $forms_graphic_operation;


$field_types = array(
    'text' => 'Texte Simple',
    'textarea' => 'Texte Avanc�',
    'checkbox' => 'Case � Cocher',
    'radio' => 'Boutons Radio',
    'select' => 'Liste de Choix',
    'tablelink' => 'Lien Formulaire',
    'file' => 'Fichier',
    'autoincrement' => 'Num�ro Auto',
    'color' => 'Palette de Couleur',
    'calculation' => 'Calcul'
);

$field_formats = array(
    'string' => 'Cha�ne de caract�res',
    'integer' => 'Nombre Entier',
    'float' => 'Nombre R�el',
    'date' => 'Date',
    'time' => 'Heure',
    'email' => 'Email',
    'url' => 'Adresse Internet'
);

$field_operators = array(
    '=' => '=',
    '>' => '>',
    '<' => '<',
    '>=' => '>=',
    '<=' => '<=',
    '<>' => '<>',
    'between' => 'Entre',
    'like' => 'Contient',
    'begin' => 'Commence par',
    'in' => 'Dans la liste de valeurs'
);

$form_types = array(
    'cms' => 'Formulaire pour Gestion de Contenu',
    'app' => 'Application PLOOPI'
);

$forms_graphic_types = array(
    'line' => 'Courbes',
    'linec' => 'Courbes cumul�es',
    'bar' => 'Histogrammes',
    'barc' => 'Histogrammes cumul�s',
    'radar' => 'Radars',
    'radarc' => 'Radars cumul�s',
    'pie' => 'Secteurs',
    'pie3d' => 'Secteurs 3D'
);

$forms_graphic_line_aggregation = array(
    'hour' => 'Heure',
    'day' => 'Journ�e',
    'week' => 'Semaine (inactif)',
    'month' => 'Mois'
);

$forms_graphic_operation = array(
    'avg' => 'Moyenne',
    'count' => 'Nombre',
    'sum' => 'Somme'
);
