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
 * Administration des news - liste
 *
 * @package news
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */
use ploopi\news2;

// Droits
if (!ploopi\acl::isactionallowed([news2\tools::ACTION_MODIFY,news2\tools::ACTION_PUBLISH])) {
	ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=forbidden"));
}

// Récupération du modèle
$newsRs = news2\tools::getNews($this->getModuleid(),false);

// Vue
echo ploopi\skin::get()->open_simplebloc('Liste des news');

// Initialisation du tableau contenant les news
// Colonnes
$news_columns = array();
$news_columns['auto']['title'] = array('label' => 'Titre', 'options' => array('sort' => true));
$news_columns['right']['published'] = array('label' => 'Publié', 'width' => 70, 'options' => array('sort' => true));
$news_columns['right']['date'] = array('label' => 'Date', 'width' => 140, 'options' => array('sort' => true));
$news_columns['right']['cat'] = array('label' => 'Catégorie', 'width' => 200, 'options' => array('sort' => true));
$news_columns['right']['hot'] = array('label' => 'A la Une', 'width' => 80, 'options' => array('sort' => true));
$news_columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => 60);

// Lignes

$c = 0;
if ($newsRs->numrows()) {
	while ($fields = $newsRs->fetchrow()) {
		$titlecat = $fields['titlecat'];
		if (is_null($titlecat)) $titlecat = '(Aucune Catégorie)';

		/**
		 * Conversion timestamp en date locale
		 */
		$localdate = ploopi\date::timestamp2local($fields['date_publish']);

		/**
		 * Le champ 'hot' permet de mettre une news en avant
		 */
		if ($fields['hot']) 
			$hot = 'Style="color:'.ploopi\skin::get()->values['colsec'].';background-color:'.ploopi\skin::get()->values['colprim'].'"';
		else 
			$hot = '';

		$news_values[$c]['values']['title'] = array('label' => ploopi\str::htmlentities($fields['title']));
		$news_values[$c]['values']['cat'] = array('label' => ploopi\str::htmlentities($titlecat));
		$news_values[$c]['values']['date'] = array('label' => ploopi\str::htmlentities(
			$localdate['date']).' '.ploopi\str::htmlentities($localdate['time']), 'sort_label' => $fields['date_publish'].$localdate['time']
		);
		$news_values[$c]['values']['published'] = [
			'label' => ($fields['published']) ? 'Oui' : 'Non', 
			'style' => ($fields['published']) ? 'color:#00AA00;text-align:center;' : 'color:#AA0000;text-align:center;'
		];
		$news_values[$c]['values']['hot'] = [
			'label' => ($fields['hot']) ? 'Oui' : 'Non', 
			'style' => ($fields['hot']) ? 'color:#00AA00;text-align:center;' : 'color:#AA0000;text-align:center;'
		];

		$arrActions = array();
		
		if (ploopi\acl::isactionallowed(news2\tools::ACTION_MODIFY)) {
		    $arrActions[] = '<a title="Modifier" href="'
			.ploopi\crypt::urlencode("admin.php?entity=admin&action=default&id={$fields['id']}")
			.'"><img alt="Modifier" src="./modules/news2/img/ico_modify.png" /></a>';
		}
		
		if (ploopi\acl::isactionallowed(news2\tools::ACTION_PUBLISH)) {
		    if ($fields['published'])
		        $arrActions[] = '<a title="Retirer" href="'
				.ploopi\crypt::urlencode("admin.php?entity=admin&action=op_publish&id={$fields['id']}")
				.'"><img alt="Retirer" src="./modules/news2/img/ico_withdraw.png" /></a>';
		    else
		        $arrActions[] = '<a title="Publier" href="'
				.ploopi\crypt::urlencode("admin.php?entity=admin&action=op_publish&id={$fields['id']}")
				.'"><img alt="Publier" src="./modules/news2/img/ico_publish.png" /></a>';
		}
		
		if (ploopi\acl::isactionallowed(news2\tools::ACTION_DELETE)) {
		    $arrActions[] = '<a title="Supprimer" href="javascript:ploopi.confirmlink(\''
			.ploopi\crypt::urlencode("admin.php?entity=admin&action=op_delete&id={$fields['id']}")
			.'\',\'Êtes-vous certain de vouloir supprimer cette actualité ?\');">'
			.'<img alt="Supprimer" src="./modules/news2/img/ico_trash.png" /></a>';
		}
		
		$news_values[$c]['values']['actions'] = ['label' =>  implode('', $arrActions)];

		$news_values[$c]['description'] = ploopi\str::htmlentities($fields['title']);
		$news_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?entity=admin&action=default&id={$fields['id']}");
		if (!empty($_GET['news_id']) && $_GET['news_id'] == $fields['id']) $news_values[$c]['style'] = 'background-color:#ffe0e0;';
		else $news_values[$c]['style'] = '';
		$c++;
	}

	// Affichage
	ploopi\skin::get()->display_array(
		$news_columns, 
		$news_values, 
		'array_newslist', 
		['sortable' => true, 'orderby_default' => 'date', 'sort_default' => 'DESC', 'limit' => 10]
	);
} else {
	echo "Aucune news";
}
echo ploopi\skin::get()->close_simplebloc();

/**
 * Modification d'une news
 */
$news = new news2\news2();
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $news->open($_GET['id'])) {
    include_once 'write.php';
}

