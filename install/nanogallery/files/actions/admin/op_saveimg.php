<?php
/**
 * Enregistre les descriptions des images
 */
use ploopi\crypt;
use ploopi\nanogallery\nanogallery;
use ploopi\nanogallery\img;

// Premiers tests
if (ploopi\acl::isactionallowed([nanogallery::ACTION_CREATE,nanogallery::ACTION_MODIFY])) {
	if (ploopi\acl::isactionallowed(nanogallery::ACTION_DESCRIPTION)) {

		// Tests
		$objGallery = new nanogallery();
		if (empty($_GET['id']) || !is_numeric($_GET['id']) || !$objGallery->open($_GET['id'])) {
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
		}

		// Enregistrements
		foreach($_POST as $key => $value) {
			if (substr($key,0,6) == 'title_') {
				$fields = [];
				$fields['id'] = ploopi\db::get()->addslashes(substr($key,6));
				$fields['title'] = $value;
				$fields['description'] = $_POST['desc_'.$fields['id']];
				$objImg = new img();
				$objImg->open($fields['id']);
				$objImg->fields = $fields;
				$objImg->save();
			}
		}

		// Log et redirection
		ploopi\user_action_log::record(nanogallery::ACTION_DESCRIPTION, $objGallery->fields['id']);
		ploopi\output::redirect(crypt::urlencode("admin.php?entity=admin&action=description&id={$_GET['id']}"));
	}
}

ploopi\output::redirect(crypt::urlencode("admin.php?entity=forbidden"));


