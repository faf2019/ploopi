<?php

/**
 * Publie ou dépublie la news
 */
use ploopi\news2;

if (!empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(news2\tools::ACTION_PUBLISH)
	) {
		$news = new news2\news2();
		// L'identifiant doit désigner une news de l'instance courante
		if($news->open($_GET['id']) && $news->fields['id_module'] == $this->getModuleId()) {
			if ($news->fields['published']) {
				$news->fields['published'] = 0;
			} else {
 				$news->fields['published'] = 1;
				ploopi\user_action_log::record(news2\tools::ACTION_PUBLISH, $news->fields['id']);
			}
			$news->save();
		}
		ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin"));

} else {
	ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));
}


