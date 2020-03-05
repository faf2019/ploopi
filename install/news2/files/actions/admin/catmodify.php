<?php
/*
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
 * Administration des catégories - liste
 *
 * @package news
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */
use ploopi\news2;

// Droits
if (!ploopi\acl::isactionallowed(news2\tools::ACTION_MANAGECAT)) {
	ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=forbidden"));
}

// Récupération du modèle
$catRs = news2\tools::getCategories($this->getModuleid());

// Vue
echo ploopi\skin::get()->open_simplebloc('Liste des Catégories');

// Initialisation du tableau contenant les catégories
// Colonnes

$array_columns = array();
$array_columns['auto']['desc'] = array('label' => 'Description', 'options' => array('sort' => true));
$array_columns['left']['title'] = array('label' => 'Titre', 'width' => 200, 'options' => array('sort' => true));
$array_columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => 60);

// Lignes
$array_values = array();
$c = 0;
if ($catRs->numrows()) {
	while ($fields = $catRs->fetchrow()) {
		$array_values[$c]['values']['desc'] = ['label' => ploopi\str::htmlentities($fields['description'])];
		$array_values[$c]['values']['title'] = ['label' => ploopi\str::htmlentities($fields['title'])];
		$array_values[$c]['values']['actions'] =
		    ['label' =>  
				'<a title="Modifier" href="'
				.ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify&id={$fields['id']}")
				.'"><img alt="Modifier" src="./modules/news2/img/ico_modify.png" /></a>'
				.'<a title="Supprimer" href="javascript:ploopi.confirmlink(\''
				.ploopi\crypt::urlencode("admin.php?entity=admin&action=op_catdelete&id={$fields['id']}")
				.'\',\'Êtes-vous certain de vouloir supprimer cette catégorie ?\');">'
				.'<img alt="Supprimer" src="./modules/news2/img/ico_trash.png" /></a>
			'];
		$array_values[$c]['description'] = ploopi\str::htmlentities($fields['title']);
		$array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify&id={$fields['id']}");
		if (!empty($_GET['id']) && $_GET['id'] == $fields['id']) 
			$array_values[$c]['style'] = 'background-color:#ffe0e0;';
		else 
			$array_values[$c]['style'] = '';
		$c++;
	}

	// Affichage
	ploopi\skin::get()->display_array(
		$array_columns, 
		$array_values, 
		'array_newscatlist', 
		['sortable' => true, 'orderby_default' => 'title', 'limit' => 10]
	);
} else {
	echo "Aucune catégorie";
}
echo ploopi\skin::get()->close_simplebloc();

/**
 * Modification d'une catégorie
 */
$newscat = new news2\news2cat();
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $newscat->open($_GET['id'])) {
    include_once 'catwrite.php';
}

