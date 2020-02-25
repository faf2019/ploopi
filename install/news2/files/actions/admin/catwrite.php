<?php
/*
    Copyright (c) 2007-2020 Ovensia
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
 * Administration des catégories - ajout/modification
 *
 * @package news
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

// Droits
if (!ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_MANAGECAT)) {
	ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=forbidden"));
}

// Récupération du modèle
$newscat = new ploopi\news2\news2cat();
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $newscat->open($_GET['id'])) {
	;
} else {
	$newscat->init_description();
}

// Vue
if ($newscat->new) 
	echo ploopi\skin::get()->open_simplebloc('Ajouter une Catégorie');
else 
	echo ploopi\skin::get()->open_simplebloc(ploopi\str::htmlentities(str_replace("LABEL",$newscat->fields['title'],'Modifier la Catégorie \'LABEL\'')));

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_catwrite";
if (!$newscat->isnew()) $strUrl .= "&id={$newscat->fields['id']}";
$arrFormOptions = array('class' => 'ploopi_generate_form news2catform');
$objForm = new ploopi\form('news2cat_form', ploopi\crypt::urlencode($strUrl), 'post', $arrFormOptions);

// Panneau 1
$objForm->addPanel($objPanel = new ploopi\form_panel(
	'',
	null,
	['style' => 'width:49%;float:left;clear:none;border:none;']
));

$objForm->addField(new ploopi\form_field(
	'input:text', 
	'Titre :', 
	$newscat->fields['title'], 
	"newscat_title", 
	"newscat_title", 
	['title' => "Titre de la catégorie", 'required' => true]
));

$objForm->addField(new ploopi\form_field(
	'textarea', 
	'Description :', 
	$newscat->fields['description'], 
	"newscat_description", 
	"newscat_description", 
	['title' => "Description de la catégorie"]
));


// Boutons
$objForm->addButton( new ploopi\form_button(
	'input:button', 
	'Annuler', 
	null, 
	null, 
	['style' => 'margin-left:4px;', 'onclick' => "document.location.href='".ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify")."';" ]
));

$objForm->addButton( new ploopi\form_button(
	'input:reset', 
	'Réinitialiser', 
	null, 
	null, 
	['style' => 'margin-left:4px;']
));

$objForm->addButton( new ploopi\form_button(
	'input:submit', 
	'Enregistrer', 
	null, 
	null, 
	['style' => 'margin-left:4px;']
));


// Rendu
echo $objForm->render();
echo ploopi\skin::get()->close_simplebloc(); 

