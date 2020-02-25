<?php

/**
 * Supprime la catégorie
 */

if (	!empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_MANAGECAT)
	) {
		$newscat = new ploopi\news2\news2cat();
		if($newscat->open($_GET['id']))
		{
			ploopi\user_action_log::record(ploopi\news2\tools::ACTION_MANAGECAT, $newscat->fields['id']);
			$newscat->delete();
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify"));
		}
} 

ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));

