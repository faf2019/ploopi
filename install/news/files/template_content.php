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
?>
<?
ploopi_init_module('news');
include_once('./modules/news/class_news_entry.php');

if (isset($newsid))
{
	$news = new news();
	$news->open($newsid);
	$localdate = ploopi_timestamp2local($news->fields['date_publish']);
	
	$template_body->assign_vars(array(
						'NEWS_ID' => $news->fields['id'],
						'NEWS_TITLE' => $news->fields['title'],
						'NEWS_RESUME' => $news->fields['resume'],
						'NEWS_CONTENT' => $news->fields['content'],
						'NEWS_SOURCE' => $news->fields['source'],
						'NEWS_HOT' => $news->fields['hot'],
						'NEWS_DATE' => $localdate['date'],
						'NEWS_TIME' => $localdate['time'],
						'NEWS_URL' => $news->fields['url'],
						'NEWS_URLTITLE' => $news->fields['urltitle'],
						'NEWS_NBCLICK' => $news->fields['nbclick']
						)
					);
}

?>

