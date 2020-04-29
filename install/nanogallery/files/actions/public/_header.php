<?php

/**
 * Header du menu "public"
 *
 */

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

/*
$isList = empty($_GET['id']) || !is_numeric($_GET['id']);
if (!$isList) {
	$objGallery = new nanogallery();
	$objGallery->open($_GET['id']);
}
$title = ($isList ? "Liste des galeries photo" : "Affichage de la galerie ".$objGallery->fields['label']);

echo ploopi\skin::get()->open_simplebloc($title);
$strTab = $this->getAction();
$arrTabs = array();

$arrTabs['default'] = array('title' => 'Liste des galeries photo',
    'url' => ploopi\crypt::urlencode("admin.php?entity=publi&action=default")
);

if (ploopi\acl::isactionallowed(nanogallery::ACTION_MODIFY)) {
	$arrTabs['write'] = array('title' => 'Créer une galerie photo',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=edit")
    );
}

echo ploopi\skin::get()->create_tabs($arrTabs, $strTab);
*/

