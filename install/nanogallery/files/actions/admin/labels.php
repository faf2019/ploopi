<?php
/**
 * NanoGallery : Onglet labels
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
$tab = 'labels';	// Pour retoursur le même onglet

$positionsValues = array(
	'overImageOnBottom'	=> ['label' => 'En bas, par dessus'],
	'overImageOnMiddle' => ['label' => "Au mileu, par dessus"],
	'overImageOnTop' 	=> ['label' => "En haut, par dessus"],
	'onBottom' 			=> ['label' => "En bas"],
);

$alignementValues = array(
	'center'	=> ['label' => 'Au centre'],
	'left' => ['label' => "A gauche"],
	'right' 	=> ['label' => "A droite"],
);

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_save&id=$id&tab=$tab";
$objForm = new ploopi\form('nano_labels_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'
));

// Panels
$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_labels','Labels'));
$this->addCBox  ($objPanel, 'thumbnailLabelDisplay', 						$gal['thumbnailLabelDisplay'], "Affichage du label", $prefix);
$this->addSelect($objPanel, 'thumbnailLabelPosition', 	$positionsValues,	$gal['thumbnailLabelPosition'], "Position", $prefix);
$this->addSelect($objPanel, 'thumbnailLabelAlignement', $alignementValues,	$gal['thumbnailLabelAlignement'], "Alignement", $prefix);
$this->addCBox  ($objPanel, 'thumbnailLabelTitleMultiline', 				$gal['thumbnailLabelTitleMultiline'], "Titre multi-lignes", $prefix);
$objForm->addPanel($objDescPanel = new ploopi\form_panel('nano_panel_desc','Descriptions : <span style="color:#F00;">Les descriptions ne sont pas gérées pour l\'instant</span>'));
$this->addCBox  ($objDescPanel, 'thumbnailLabelDisplayDescription', 		$gal['thumbnailLabelDisplayDescription'], "Affichage de la description", $prefix);
$this->addCBox  ($objDescPanel, 'thumbnailLabelDescriptionMultiline', 		$gal['thumbnailLabelDescriptionMultiline'], "Description multi-lignes", $prefix);

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;')));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

