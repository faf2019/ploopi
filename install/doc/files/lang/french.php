<?php
/*
    Copyright (c) 2007-2018 Ovensia
    Copyright (c) 2009 HeXad
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
 * @package doc
 * @subpackage lang
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Définition des constantes
 */

define ('_DOC_PAGE_TITLE', 'Gestion des Documents du module \'LABEL\'');

define ('_DOC_TAB_TITLE_PARSERS',       'Gestion des analyseurs');
define ('_DOC_TAB_TITLE_INDEX',         'Indexation');
define ('_DOC_TAB_TITLE_CLEAN_CACHE',   'Vidage des différents caches');
define ('_DOC_TAB_TITLE_STATS',         'Statistiques');


define ('_DOC_MYDOCUMENTS', 'Mes Documents');
define ('_DOC_ALLDOCUMENTS', 'Tous les Documents');
define ('_DOC_SEARCH', 'Recherche');

define ('_DOC_LABEL_ADMIN', 'Administration');

define ("_DOC_MSG_CONFIRMDELETEFILE", "Etes-vous sûr de vouloir supprimer ce fichier ?");
define ("_DOC_MSG_CONFIRMDELETEFOLDER", "Etes-vous sûr de vouloir supprimer ce dossier ?");

define ("_DOC_LABEL_ERROR_FOLDEREXISTS",        '<b>Erreur ! </b>Création du dossier impossible - le dossier existe déjà');
define ("_DOC_LABEL_ERROR_UNAVAILABLEPATH",     '<b>Erreur ! </b>Création du dossier impossible - le chemin n\'est pas valide');
define ("_DOC_LABEL_ERROR_FOLDERNOTWRITABLE",   '<b>Erreur ! </b>Création du dossier impossible - le répertoire physique n\'est pas accessible en écriture');

define ("_DOC_LABEL_ERROR_EMPTYFILE",           '<b>Erreur ! </b>Enregistrement du fichier impossible - le fichier est vide');
define ("_DOC_LABEL_ERROR_FILENOTWRITABLE",     '<b>Erreur ! </b>Enregistrement du fichier impossible - le fichier n\'est pas accessible en écriture');
define ("_DOC_LABEL_ERROR_MAXFILESIZE",         '<b>Erreur ! </b>Enregistrement du fichier impossible - le fichier est trop volumineux');

global $foldertypes;
$foldertypes = array ('private' => 'Personnel', 'shared' => 'Partagé', 'public' => 'Public');

?>
