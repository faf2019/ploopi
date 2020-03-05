<?php

/**
 * Supprime la news
 */
use ploopi\news2;

if (	!empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(news2\tools::ACTION_DELETE)
	) {
		$news = new news2\news2();
		if($news->open($_GET['id']))
		{
			ploopi\user_action_log::record(news2\tools::ACTION_DELETE, $news->fields['id']);
			$news->delete();
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin"));
		}

} 

ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));

