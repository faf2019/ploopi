<?php
/**
 * Enregistre la galerie
 */
use ploopi\crypt;
use ploopi\nanogallery\nanogallery;

if (ploopi\acl::isactionallowed([nanogallery::ACTION_CREATE,nanogallery::ACTION_MODIFY])) {

	$objGallery = new nanogallery();
    if (!empty($_GET['id'])) {
    	if (!is_numeric($_GET['id'])) 
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
		if (!$objGallery->open($_GET['id'])) 
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
	}
    $objGallery->setvalues($_POST,'nano_');
    $objGallery->save();

	ploopi\user_action_log::record(nanogallery::ACTION_MODIFY, $objGallery->fields['id']);
    ploopi\output::redirect(crypt::urlencode("admin.php?entity=admin&action={$_GET['tab']}&id={$objGallery->fields['id']}"));
}

ploopi\output::redirect(crypt::urlencode("admin.php?entity=forbidden"));


