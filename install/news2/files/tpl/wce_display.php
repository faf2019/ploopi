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
 * Affichage d'un objet news dans une page de contenu (WebEdit)
 *
 * @package news
 * @subpackage wce
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @author Jean-Pierre Pawlak
 */

$template_news = new Template("./templates/frontoffice/{$template_name}");
$specialid = ploopi\str::clean_filename(ploopi\param::get('specialid', $obj['module_id']));
$fileTpl = ($specialid != '' ? 'news2_'.$specialid : 'news2'); 
$rootTpl = 'sw_'.$fileTpl;

if (file_exists("./templates/frontoffice/{$template_name}/{$fileTpl}.tpl"))
{
	$nbCol = ploopi\param::get('nbcol', $obj['module_id']);
	$template_news->assign_var("news2_nbcol",$nbCol);
	$currentPage = (empty($_REQUEST['news2page'])) ? 1 : $_REQUEST['news2page'];
    $template_news->set_filenames(array('news_display' => "{$fileTpl}.tpl"));
	$news_result = ploopi\news2\tools::getNews($obj['module_id']);
	$news_count = $news_result->numrows();
	$template_news->assign_block_vars($rootTpl, array('NEWS2_COUNT' => $news_count));

	// Pagination
	$nbPerPage = ploopi\param::get('nbnewsdisplay', $obj['module_id']);
	if ($news_count > $nbPerPage) {
		$nbPages = ceil($news_count / $nbPerPage);
		for ($i=0; $i < $nbPages; $i++) {
			$template_news->assign_block_vars("{$rootTpl}.pages" , array(
				'NO' => $i + 1,
				'CURRENT' => ($i + 1 == $currentPage) ? 'disabled="disabled"' : ""
			));
		}
	} else {
		$currentPage = 1;
	}

    $opened=false;
    $titlecat="";
	$rank = 0;

    while ($news_fields = $news_result->fetchrow())
    {
		$rank++;
		if (ceil($rank / $nbPerPage) == $currentPage) {
		
			$localdate = ploopi\date::timestamp2local($news_fields['date_publish']);

			$user = new ploopi\user();
			if ($user->open($news_fields['id_user']))
			{
				$author_firstname = $user->fields['firstname'];
				$author_lastname = $user->fields['lastname'];
				$author_login = $user->fields['login'];
				$author_email = $user->fields['email'];
			}
			else
			{
				$author_firstname = '';
				$author_lastname = 'inconnu';
				$author_login = 'inconnu';
				$author_email = '';
			}

			$newscat = new ploopi\news2\news2cat();
			$category = ($newscat->open($news_fields['id_cat'])) ? $newscat->fields['title'] : 'Inconnue';

			$template_news->assign_block_vars("{$rootTpl}.news" , array(
				'ID' => $news_fields['id'],
				'TITLE' => ploopi\str::htmlentities($news_fields['title']),
				'SOURCE' => ploopi\str::htmlentities(($news_fields['source'] == '') ? 'Inconnue' : $news_fields['source']),
				'CONTENT' => $news_fields['content'],
				'HOT' => ($news_fields['hot']) ? 'hot' : '',
				'DATE' => $localdate['date'],
				'TIME' => $localdate['time'],
				'URL' => $news_fields['url'],
				'URLTITLE' => ploopi\str::htmlentities($news_fields['urltitle']),
				'NBCLICK' => $news_fields['nbclick'],
				'CATEGORY' => ploopi\str::htmlentities($category),
				'AUTHOR_FIRSTNAME' => ploopi\str::htmlentities($author_firstname),
				'AUTHOR_LASTNAME' => ploopi\str::htmlentities($author_lastname),
				'AUTHOR_LOGIN' => ploopi\str::htmlentities($author_login),
				'AUTHOR_EMAIL' => ploopi\str::htmlentities($author_email),
				'RANK' => $rank,
				'BACKGROUND' => $news_fields['background'],
				'LINK' => "modcontent=".$obj['module_id']."&newsid={$news_fields['id']}",
				'CAT_ID' => $news_fields['id_cat'],
				'CAT_LABEL' => $category
			));
		}
    }

    $template_news->pparse('news_display');
}
// else echo "Fichier ./templates/frontoffice/{$template_name}/news2.tpl manquant";
