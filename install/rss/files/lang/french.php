<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
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
 * Fichier de langue français
 *
 * @package rss
 * @subpackage lang
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_RSS_RETURN', 'Retour');

define ('_RSS_PAGE_TITLE', 'Gestion du Module « LABEL »');

define ('_RSS_LABEL_FEEDLIST',  'Liste des Flux');
define ('_RSS_LABEL_FEEDADD',   'Ajouter un Flux');
define ('_RSS_LABEL_CATLIST',   'Liste des Catégories');
define ('_RSS_LABEL_CATADD',    'Ajouter une Catégorie');
define ('_RSS_LABEL_CATMODIFY', 'Modifier la Catégorie « LABEL »');
define ('_RSS_LABEL_FEEDMODIFY','Modifier le Flux « LABEL »');
define ('_RSS_LABEL_FEEDLIMIT', 'Nombre de lien pour le flux');

define ('_RSS_LABEL_NEWS',      'Actualités');

define ('_RSS_LABEL_FEEDEXPLORER',  'Explorateur de Flux');

define ('_RSS_LABEL_REINDEX',       'Ré-indéxer les flux');
define ('_RSS_MESS_REINDEX',        'Indexation terminée');

define ('_RSS_LABEL_KEYWORD',       'Mots Clés :');
 
define ('_RSS_LABEL_LABEL',         'Libellé');
define ('_RSS_LABEL_DATE_CREAT',    'Création');
define ('_RSS_LABEL_DATE_MODIF',    'Modification');

define ('_RSS_LABEL_DESCRIPTION',   'Description');
define ('_RSS_LABEL_TITLE',         'Titre');
define ('_RSS_LABEL_SUBTITLE',      'Sous-Titre');
define ('_RSS_LABEL_CONTENT',       'Contenu');
define ('_RSS_LABEL_LINK',          'Lien');
define ('_RSS_LABEL_DATE_PUBLIC',   'Date publication');
define ('_RSS_LABEL_CATEGORY',      'Catégorie');
define ('_RSS_LABEL_NOCATEGORY',    '(Aucune Catégorie)');
define ('_RSS_LABEL_FEEDURL',       'Adresse du Flux');
define ('_RSS_LABEL_DEFAULT',       'Par Défaut');
define ('_RSS_LABEL_FEED_RENEW',    'Renouvellement');
define ('_RSS_LABEL_FEEDS',         'Flux');
define ('_RSS_LABEL_LIMIT',         'Limite');
define ('_RSS_LABEL_TPL_TAG',       'Tag pour le template');
define ('_RSS_LABEL_ACTIONS',       'Actions');
define ('_RSS_LABEL_JOIN',          'Jointure');
define ('_RSS_LABEL_CONDITION',     'Condition');
define ('_RSS_LABEL_CONDITION_AND', 'Valident toutes les conditions suivantes');
define ('_RSS_LABEL_CONDITION_OR',  'Valident au moins une des conditions suivantes');

define ('_RSS_LABEL_FILTER_VALUE_TEST','Valeur de condition');

define ('_RSS_LABEL_SEARCH',        'Consultation avancée');
define ('_RSS_LABEL_ADMIN',         'Administration');
define ('_RSS_LABEL_TOOLS',         'Outils');

define ('_RSS_UNLIMIT',             'Pas de limite');

define ('_RSS_SQL_LABEL_LIST',      'Liste des filtres');
define ('_RSS_SQL_LABEL_DETAIL',    'Détail du filtre');
define ('_RSS_SQL_MODIF_FILTER',    'Modification du filtre ');
define ('_RSS_SQL_NEW_FILTER',      'Nouveau filtre');

//FILTER
define ('_RSS_LABEL_FILTER_FEED',      'Filtres de Flux');
define ('_RSS_LABEL_FILTER_NEW',       'Nouveau Filtre');
define ('_RSS_LABEL_FILTER_MODIF',     'Modifier Filtre');
define ('_RSS_LABEL_FILTER',           'Filtrer');
define ('_RSS_LABEL_FILTER_ON',        'Filtre porte sur');
define ('_RSS_LABEL_FILTER_CONDITION', 'condition');
define ('_RSS_LABEL_FILTER_VALUE',     'Valeur');

define ('_RSS_SQL_CONTENT',         'contient');
define ('_RSS_SQL_NOCONTENT',       'ne contient pas');
define ('_RSS_SQL_IS',              'est');
define ('_RSS_SQL_NOIS',            'n\'est pas');
define ('_RSS_SQL_BEGIN',           'commence par');
define ('_RSS_SQL_NOBEGIN',         'ne commence pas par');
define ('_RSS_SQL_BEFORE',          'se trouve avant (<)');
define ('_RSS_SQL_AFTER',           'se trouve après (>=)');
define ('_RSS_SQL_NO_RESULT',       'Aucun résultats à afficher');
define ('_RSS_SQL_REQUEST_ERROR',   'Erreur dans la requête demandée');

define ('_RSS_COMMENT_O_NOLIMIT',   '&nbsp;(0 = Limite par défaut du module)');
define ('_RSS_COMMENT_CAT_TPL_TAG',     '<i>Sans "nom" de tag cette categorie ne pourra être utilisée directement en template (front)</i>');
define ('_RSS_COMMENT_FILTER_TPL_TAG',  '<i>Sans "nom" de tag ce filtre ne pourra être utilisé directement en template (front)</i>');
define ('_RSS_COMMENT_FEED_TPL_TAG',    '<i>Sans "nom" de tag ce flux ne pourra être utilisé directement en template (front)</i>');
define ('_RSS_COMMENT_WARNING_TPL_TAG', '<i><b>Attention: ne pas attribuer de "nom" de tag si celui-ci n\'a pas d\'utilité</b></i>');

define ('_RSS_DELETE_ELEMENT',      'Êtes-vous certain de vouloir supprimer cette condition ?');
define ('_RSS_DELETE_FEED',      'Êtes-vous certain de vouloir supprimer ce flux ?');
define ('_RSS_DELETE_CATEGORIE',      'Êtes-vous certain de vouloir supprimer cette catégorie ?');

define ('_RSS_INFO_CAT_FEED',      '<i>Si aucune Catégorie et aucun flux n\'est selectionné, le filtre portera sur tous les flux.</i>');
?>
