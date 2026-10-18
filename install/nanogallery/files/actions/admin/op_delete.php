<?php

/**
 * Supprime la galerie
 */
use ploopi\nanogallery\nanogallery;

if ( !empty($_GET['id']) 
		&& is_numeric($_GET['id']) 
		&& ploopi\acl::isactionallowed(nanogallery::ACTION_DELETE)
	) {
		$img = new nanogallery();
		// L'identifiant doit désigner une galerie de l'instance courante
		if($img->open($_GET['id']) && $img->fields['id_module'] == $this->getModuleId()) {
			ploopi\user_action_log::record(nanogallery::ACTION_DELETE, $img->fields['id']);
			$img->delete();
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=public&action=list"));
		}
} 

ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));

