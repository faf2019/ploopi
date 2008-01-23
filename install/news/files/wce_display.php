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


$template_news = new Template("./templates/frontoffice/{$template_name}");
if (file_exists("./templates/frontoffice/{$template_name}/news.tpl"))
{
	$template_news->set_filenames(array('news_display' => 'news.tpl'));

	$where = '';

	if (!empty($_GET['newsid']) && is_numeric($_GET['newsid'])) $where = " AND ploopi_mod_news_entry.id = {$_GET['newsid']} ";

	$select = 	"
				SELECT 		ploopi_mod_news_entry.*,
							ploopi_mod_news_cat.title as titlecat
				FROM 		ploopi_mod_news_entry
				LEFT JOIN 	ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat
				WHERE 		ploopi_mod_news_entry.id_module = ".$obj['module_id']."
				AND			ploopi_mod_news_entry.published = 1
				{$where}
				ORDER BY 	titlecat,
							date_publish desc
				";


	$news_result = $db->query($select);

	$opened=false;
	$titlecat="";

	while ($news_fields = $db->fetchrow($news_result))
	{
		$localdate = ploopi_timestamp2local($news_fields['date_publish']);

		$user = new user();
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

		$newscat = new newscat();
		$category = ($newscat->open($news_fields['id_cat'])) ? $newscat->fields['title'] : _NEWS_LABEL_UNKNOWN;


		$template_news->assign_block_vars('news' , array(
							'ID' => $news_fields['id'],
							'TITLE' => $news_fields['title'],
							'SOURCE' => ($news_fields['source'] == '') ? _NEWS_LABEL_UNKNOWN : $news_fields['source'],
							'CONTENT' => $news_fields['content'],
							'HOT' => ($news_fields['hot']) ? 'hot' : '',
							'DATE' => $localdate['date'],
							'TIME' => $localdate['time'],
							'URL' => $news_fields['url'],
							'URLTITLE' => $news_fields['urltitle'],
							'NBCLICK' => $news_fields['nbclick'],
							'CATEGORY' => $category,
							'AUTHOR_FIRSTNAME' => $author_firstname,
							'AUTHOR_LASTNAME' => $author_lastname,
							'AUTHOR_LOGIN' => $author_login,
							'AUTHOR_EMAIL' => $author_email
							)
						);
	}


	$template_news->pparse('news_display');
}
