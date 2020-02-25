<?php

/**
 * Supprime la news
 */


if (	!empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_DELETE)
	) {
		$news = new ploopi\news2\news2();
		if($news->open($_GET['id']))
		{
			ploopi\user_action_log::record(ploopi\news2\tools::ACTION_DELETE, $news->fields['id']);
			$news->delete();
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin"));
		}

} 

ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));

