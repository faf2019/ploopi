<?php

/**
 * Header du menu "admin"
 *
 */

if (!ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_ANY))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

$strTab = self::getAction();
$arrTabs = array();

if (
		ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_MODIFY) 
		|| ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_PUBLISH)
) {
	$arrTabs['default'] = array(
        'title' => 'Liste des News',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=default")
    );
}

if (ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_WRITE)) {
	$arrTabs['write'] = array(
        'title' => 'Rédiger une News',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=write")
    );
}

if (ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_MANAGECAT)) {
	$arrTabs['catmodify'] = array(
        'title' => 'Liste des Catégories',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify")
    );
	$arrTabs['catwrite'] = array(
        'title' => 'Ajouter une Catégorie',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=catwrite")
    );
}


echo ploopi\skin::get()->create_tabs($arrTabs, $strTab);

