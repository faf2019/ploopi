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

// Récupération du modèle
$moduleid = $this->getModuleId();
$objGallery = new nanogallery();
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $objGallery->open($_GET['id'])) { ; } else { $objGallery->open(); }
$id = $objGallery->fields['id'];

if (is_null($objGallery))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=error'));
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

// Les éléments commentés ne seront utiles que lorsque la gestion des albums sera effective

// Panels
$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_nav','Navigation'));
$this->addCBox  ($objPanel, 'displayBreadcrumb', 			$gal['displayBreadcrumb'], "Affichage du chemin", $prefix);
$this->addCBox  ($objPanel, 'breadcrumbAutoHideTopLevel', 	$gal['breadcrumbAutoHideTopLevel'], "Cacher Le 1e niveau", $prefix);
$this->addCBox  ($objPanel, 'breadcrumbOnlyCurrentLevel', 	$gal['breadcrumbOnlyCurrentLevel'], "Uniquement le niveau courant", $prefix);
$this->addCBox  ($objPanel, 'thumbnailLevelUp', 		$gal['thumbnailLevelUp'], "Lien vers le niveau sup.", $prefix);

$objForm->addPanel($objFilterPanel = new ploopi\form_panel('nano_panel_nav','Filtres'));
$this->addSelect($objFilterPanel, 'galleryFilterTags', 			$filtertagsValues,	$gal['galleryFilterTags'], "Affichage du filtre", $prefix);

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;')));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

