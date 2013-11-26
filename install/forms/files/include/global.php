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
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

/**
 * Action : Ajouter une réponse dans un formulaire
 */
define ("_FORMS_ACTION_ADDREPLY",   2);

/**
 * Action : Exporter les données d'un formulaire
 */
define ("_FORMS_ACTION_EXPORT",     3);

/**
 * Action : Filtrer les données d'un formulaire
 */
define ("_FORMS_ACTION_FILTER",     4);

/**
 * Action : Supprimer des enregistrements d'un formulaire
 */
define ("_FORMS_ACTION_DELETE",     5);

/**
 * Action : Gérer l'archivage des données d'un formulaire
 */
define ("_FORMS_ACTION_BACKUP",     6);

/**
 * Action : Afficher les graphiques
 */
define ("_FORMS_ACTION_GRAPHICS",     7);

/**
 * Action : Importer des données CSV
 */
define ("_FORMS_ACTION_IMPORT_CSV",     8);

/**
 * Action : Administrer les formulaires
 */
define ("_FORMS_ACTION_ADMIN",     99);


// Objet Form
define ('_FORMS_OBJECT_FORM',   1);


/**
 * Définition des variables globales
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
 * Opérateurs sur les champs (=, >, <, etc...)
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
 * Types d'opération
 */
global $forms_graphic_operation;


$field_types = array(
    'text' => 'Texte Simple',
    'textarea' => 'Texte Avancé',
    'checkbox' => 'Case à Cocher',
    'radio' => 'Boutons Radio',
    'select' => 'Liste de Choix',
    'tablelink' => 'Lien Formulaire',
    'file' => 'Fichier',
    'autoincrement' => 'Numéro Auto',
    'color' => 'Palette de Couleur',
    'calculation' => 'Calcul'
);

$field_formats = array(
    'string' => 'Chaîne de caractères',
    'integer' => 'Nombre Entier',
    'float' => 'Nombre Réel',
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
    'linec' => 'Courbes cumulées',
    'bar' => 'Histogrammes',
    'barc' => 'Histogrammes cumulés',
    'radar' => 'Radars',
    'radarc' => 'Radars cumulés',
    'pie' => 'Secteurs',
    'pie3d' => 'Secteurs 3D'
);

$forms_graphic_line_aggregation = array(
    'hour' => 'Heure',
    'day' => 'Journée',
    'week' => 'Semaine (inactif)',
    'month' => 'Mois'
);

$forms_graphic_operation = array(
    'avg' => 'Moyenne',
    'count' => 'Nombre',
    'sum' => 'Somme'
);
