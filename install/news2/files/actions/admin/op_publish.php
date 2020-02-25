<?php

/**
 * Publie ou dépublie la news
 */


if (	!empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_PUBLISH)
	) {
		$news = new ploopi\news2\news2();
		if($news->open($_GET['id']))
		{
			if ($news->fields['published']) {
				$news->fields['published'] = 0;
			} else {
 				$news->fields['published'] = 1;
				ploopi\user_action_log::record(
					ploopi\news2\tools::ACTION_PUBLISH, $news->fields['id']
				);
			}
			$news->save();
		}
		ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin"));

} else {
	ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));
}


