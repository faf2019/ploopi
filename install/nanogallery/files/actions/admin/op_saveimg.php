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
		if (empty($_GET['id']) || !is_numeric($_GET['id']) || !$objGallery->open($_GET['id']) || $objGallery->fields['id_module'] != $this->getModuleId()) {
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
		}

		// Enregistrements : seulement les images de cette galerie
		$arrIds = array_flip($objGallery->getImageIds());
		foreach($_POST as $key => $value) {
			if (substr($key,0,6) == 'title_' && isset($arrIds[substr($key,6)])) {
				$fields = [];
				$fields['id'] = substr($key,6);
				$fields['title'] = (string)$value;
				$fields['description'] = isset($_POST['desc_'.$fields['id']]) ? (string)$_POST['desc_'.$fields['id']] : '';
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


