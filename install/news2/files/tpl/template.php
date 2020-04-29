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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package news
 * @subpackage template
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @author Jean-Pierre Pawlak
 */
use ploopi\str;

$news_result = ploopi\news2\tools::getNews($template_moduleid);
$news_count = $news_result->numrows();

// Traitement
if ($news_count > 0) {
	$specialid = ploopi\str::clean_filename(ploopi\param::get('specialid', $template_moduleid));
	$rootTpl = ($specialid != '' ? 'sw_news2_'.$specialid : 'sw_news2'); 
	$interval = ploopi\param::get('interval', $template_moduleid);
	$template_body->assign_var(($specialid != '' ? "news2_{$specialid}_interval" : "news2_interval"),$interval);
	$nbCol = ploopi\param::get('nbcol', $template_moduleid);
	$template_body->assign_var(($specialid != '' ? "news2_{$specialid}_nbcol" : "news2_nbcol"),$nbCol);
	$template_body->assign_block_vars($rootTpl, array('NEWS2_COUNT' => $news_count));
	$template_body->assign_block_vars("{$rootTpl}.catalog", array());
	$rank = 0;
	$isCat = false;
	$all = array();
	$hot = array();
	$categories = array();

	// Boucle sur les résultats
	while ($news_fields = $news_result->fetchrow())
	{
		$rank++;
		$localdate = ploopi\date::timestamp2local($news_fields['date_publish']);

		// La news en elle-même
		$template_body->assign_block_vars("{$rootTpl}.news" , array(
			'RANK' => $rank,
			'ID' => $news_fields['id'],
			'TITLE' => str::htmlentities($news_fields['title'], ENT_QUOTES),
			'CONTENT' => $news_fields['content'], ENT_QUOTES,
			'HOT' => $news_fields['hot'],
			'DATE' => $localdate['date'],
			'TIME' => $localdate['time'],
			'URL' => $news_fields['url'],
			'URLTITLE' => str::htmlentities($news_fields['urltitle'], ENT_QUOTES),
			'BACKGROUND' => $news_fields['background'],
			'LINK' => "modcontent={$template_moduleid}&newsid={$news_fields['id']}",
			'CAT_ID' => $news_fields['id_cat'],
			'CAT_LABEL' => str::htmlentities($news_fields['titlecat'], ENT_QUOTES)
		));

		// Préparation propriétés ALL
		if (isset($all['count'])) {
			$all['links'] .= ','.$rank;
			$all['count'] += 1;
		} else {
			$all['links'] = ''.$rank;
			$all['count'] = 1;		
		}
		// catalogue
		$template_body->assign_block_vars("{$rootTpl}.catalog.all" , array(
			'RANK' =>$all['count'],
			'TITLE' => str::htmlentities($news_fields['title']),			
		));
		
		// Préparation propriétés HOT
		if ($news_fields['hot']) {
			if (isset($hot['count'])) {
				$hot['links'] .= ','.$rank;
				$hot['count'] += 1;
			} else {
				$hot['links'] = ''.$rank;
				$hot['count'] = 1;		
			}
			// catalogue
			$template_body->assign_block_vars("{$rootTpl}.catalog.hot" , array(
				'RANK' => $hot['count']
			));
		}

		// Préparation propriétés CATEGORIES
		if ($news_fields['id_cat'] > 0) {
			if (!$isCat) {
				$template_body->assign_block_vars("{$rootTpl}.btn_cat", array());
				$isCat = true;
			}
			$categories[$news_fields['id_cat']]['label'] = $news_fields['titlecat'];
			if (isset($categories[$news_fields['id_cat']]['count'])) {
				$categories[$news_fields['id_cat']]['links'] .= ','.$rank;
				$categories[$news_fields['id_cat']]['count'] += 1;
			} else {
				$categories[$news_fields['id_cat']]['links'] = ''.$rank;
				$categories[$news_fields['id_cat']]['count'] = 1;
			}	
			// catalogue
			$template_body->assign_block_vars("{$rootTpl}.catalog.cat".$news_fields['id_cat'] , array(
				'RANK' => $categories[$news_fields['id_cat']]['count']
			));
		}
		
	}

	// Enregistrement propriétés ALL
	$template_body->assign_block_vars("{$rootTpl}.all" , array(
		'COUNT' => $all['count'],
		'LINKS'	=> $all['links']
	));

	// Enregistrement propriétés HOT
	if (isset($hot['count'])) {
		$template_body->assign_block_vars("{$rootTpl}.hot" , array(
			'COUNT' => $hot['count'],
			'LINKS'	=> $hot['links']
		));
	}
	
	// Enregistrement propriétés CATEGORIES
	foreach($categories as $key => $val) {	
		$template_body->assign_block_vars("{$rootTpl}.cat" , array(
			'ID'	=> $key,
			'LABEL' => $val['label'],
			'COUNT' => $val['count'],
			'LINKS'	=> $val['links']
		));
	}

	// Permet le déport dans index_news2.tpl
	global $template_name;
	$dirname = 'index_news2.tpl';
	if (file_exists("./templates/frontoffice/{$template_name}/$dirname") && is_readable("./templates/frontoffice/{$template_name}/$dirname")) {
		$template_body->set_filenames(array('h_news2' => $dirname));
		$template_body->assign_var_from_handle('include_news2', 'h_news2');
	}

}
