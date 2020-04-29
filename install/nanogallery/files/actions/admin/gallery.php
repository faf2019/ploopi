<?php
/**
 * NanoGallery : Onglet Galerie
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
$tab = 'gallery';	// Pour retoursur le même onglet

$layoutValues = array(
	'grid' 		=> ['label' => 'Grille'],
	'justified' => ['label' => "Justifié"],
	'cascading' => ['label' => "En cascade"]
);
$displayValues = array(
	'fullContent'	=> ['label' => 'Complet'],
	'rows' 			=> ['label' => "Lignes"],
	'moreButton' 	=> ['label' => "Bouton 'suite'"],
	'pagination' 	=> ['label' => "Paginé"]
);
$paginationValues = array(
	'rectangles'	=> ['label' => 'Rectangles'],
	'dots' 			=> ['label' => "Points"],
	'numbers' 		=> ['label' => "Nombres"]
);
$sortingValues = array(
	''			=> ['label' => ''],
	'random' 	=> ['label' => "Hasard"],
	'reversed' 	=> ['label' => "Inversé"],
	'titleasc' 	=> ['label' => "Titre ascendant"],
	'titledesc' => ['label' => "Titre descendant"]
);
$alignValues = array(
	'center'	=> ['label' => 'Au centre'],
	'left' 		=> ['label' => "A gauche"],
	'right' 	=> ['label' => "A droite"],
	'justified' => ['label' => "Justifié"]
);

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_save&id=$id&tab=$tab";
$objForm = new ploopi\form('nano_gal_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'
));

// Panels
$objForm->addPanel($objFramePanel = new ploopi\form_panel('nano_panel_frame','Propriétés du cadre'));
$this->addClr   ($objFramePanel, 'frameBgColor', 			$gal['frameBgColor'], 				"Couleur du fond", $prefix);
$this->addClr   ($objFramePanel, 'frameBorderColor', 		$gal['frameBorderColor'], 			"Couleur du bord", $prefix);
$this->addInt   ($objFramePanel, 'frameBorderVertical', 	$gal['frameBorderVertical'], 		"Epaiseur de la bordure verticale", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameBorderHorizontal', 	$gal['frameBorderHorizontal'], 		"Epaiseur de la bordure horizontale", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameBorderRadius', 		$gal['frameBorderRadius'], 			"Arrondi de la bordure", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameInternalVertical', 	$gal['frameInternalVertical'], 		"Espace interne vertical", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameInternalHorizontal', $gal['frameInternalHorizontal'], 	"Espace interne horizontal", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameExternalVertical', 	$gal['frameExternalVertical'], 		"Espace externe vertical", $prefix, "", true);
$this->addInt   ($objFramePanel, 'frameExternalHorizontal', $gal['frameExternalHorizontal'], 	"Espace externe horizontal", $prefix, "", true);


$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_gen','Propriétés générales'));
$this->addSelect($objPanel, 'galleryLayout', 		$layoutValues, 		$gal['galleryLayout'], "Présentation", $prefix);
$this->addSelect($objPanel, 'galleryDisplayMode', 	$displayValues, 	$gal['galleryDisplayMode'], "Mode d'affichage", $prefix);
$this->addInt   ($objPanel, 'galleryMaxRows', 							$gal['galleryMaxRows'], "Nombre max de lignes (au départ)"
	, $prefix, "", true);
$this->addInt   ($objPanel, 'galleryDisplayMoreStep', 					$gal['galleryDisplayMoreStep'], "Nombre de lignes par ajout", $prefix, "", true);
$this->addCBox  ($objPanel, 'galleryLastRowFull', 						$gal['galleryLastRowFull'], "Dernière ligne complète", $prefix);
$this->addSelect($objPanel, 'galleryPaginationMode',$paginationValues, 	$gal['galleryPaginationMode'], "Mode de pagination", $prefix);
$this->addSelect($objPanel, 'gallerySorting', 		$sortingValues, 	$gal['gallerySorting'], "Tri", $prefix);
$this->addInt   ($objPanel, 'galleryMaxItems', 							$gal['galleryMaxItems'], "Nombre max de photos", $prefix, "", true);
$this->addSelect($objPanel, 'thumbnailAlignment', 	$alignValues, 		$gal['thumbnailAlignment'], "Alignement des vignettes", $prefix);
$this->addInt   ($objPanel, 'thumbnailGutterWidth', 					$gal['thumbnailGutterWidth'], "Largeur des gouttières", $prefix, "", true);
$this->addInt   ($objPanel, 'thumbnailGutterHeight', 					$gal['thumbnailGutterHeight'], "Hauteur des gouttières", $prefix, "", true);

$objForm->addPanel($objImagePanel = new ploopi\form_panel('nano_panel_image','Affichage des grandes images'));
$this->addCBox  ($objImagePanel, 'thumbnailOpenImage', 	$gal['thumbnailOpenImage'], "activation du mode", $prefix);

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;')));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();


