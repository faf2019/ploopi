<?php
/**
 * NanoGallery : Onglet navigation
 *
 * @author JPP
 * @copyright DSIC-EST
 */
use ploopi\nanogallery\nanogallery;

// Tests ------------------------------------------------------------
if (!ploopi\acl::isactionallowed([nanogallery::ACTION_CREATE,nanogallery::ACTION_MODIFY]))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));

// Suite des tests et récupération du modèle
$moduleid = $this->getModuleId();
$objGallery = new nanogallery();
if (empty($_GET['id']) || !is_numeric($_GET['id']) || !$objGallery->open($_GET['id'])) { 
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=error'));
}
$id = $objGallery->fields['id'];
$gal = $objGallery->fields;

// Initialisations
$prefix = 'nano_';
$tab = 'nav';	// Pour retoursur le même onglet

$filtertagsValues = array(
	'false'			=> ['label' => 'Non'],
	'true' 			=> ['label' => "Oui"],
	'title' 		=> ['label' => "Basé sur Titre"],
	'description'	=> ['label' => "Basé sur Description"],
);

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_save&id=$id&tab=$tab";
$objForm = new ploopi\form('nano_nav_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'
));

// Panels
$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_nav','Navigation'));
$this->addCBox  ($objPanel, $prefix.'displayBreadcrumb', 							$gal['displayBreadcrumb'], "Affichage du chemin");
$this->addCBox  ($objPanel, $prefix.'breadcrumbAutoHideTopLevel',				 	$gal['breadcrumbAutoHideTopLevel'], "Cacher Le 1e niveau");
$this->addCBox  ($objPanel, $prefix.'breadcrumbOnlyCurrentLevel',				 	$gal['breadcrumbOnlyCurrentLevel'], "Uniquement le niveau courant");
$this->addCBox  ($objPanel, $prefix.'thumbnailLevelUp', 							$gal['thumbnailLevelUp'], "Lien vers le niveau sup.");

$objForm->addPanel($objFilterPanel = new ploopi\form_panel('nano_panel_nav','Filtres'));
$this->addSelect($objFilterPanel, $prefix.'galleryFilterTags', 	$filtertagsValues,	$gal['galleryFilterTags'], "Affichage du filtre");

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;')));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

