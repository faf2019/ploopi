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
$tab = 'gallery';	// Pour retour sur le même onglet

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
$this->addClr   ($objFramePanel, $prefix.'frameBgColor', 			$gal['frameBgColor'], 				"Couleur du fond");
$this->addClr   ($objFramePanel, $prefix.'frameBorderColor', 		$gal['frameBorderColor'], 			"Couleur du bord");
$this->addInt   ($objFramePanel, $prefix.'frameBorderVertical', 	$gal['frameBorderVertical'], 		"Epaiseur de la bordure verticale", "", true,0,100);
$this->addInt   ($objFramePanel, $prefix.'frameBorderHorizontal', 	$gal['frameBorderHorizontal'], 		"Epaiseur de la bordure horizontale", "", true,0,100);
$this->addInt   ($objFramePanel, $prefix.'frameBorderRadius', 		$gal['frameBorderRadius'], 			"Arrondi de la bordure", "", true,0,100);
$this->addInt   ($objFramePanel, $prefix.'frameInternalVertical', 	$gal['frameInternalVertical'], 		"Espace interne vertical", "", true,0,100);
$this->addInt   ($objFramePanel, $prefix.'frameInternalHorizontal', $gal['frameInternalHorizontal'], 	"Espace interne horizontal", "", true,0,100);
$this->addInt   ($objFramePanel, $prefix.'frameExternalVertical', 	$gal['frameExternalVertical'], 		"Espace externe vertical", "", true,-10,100);
$this->addInt   ($objFramePanel, $prefix.'frameExternalHorizontal', $gal['frameExternalHorizontal'], 	"Espace externe horizontal", "", true,-10,100);


$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_gen','Propriétés générales'));
$this->addSelect($objPanel, $prefix.'galleryLayout', 			$layoutValues, 	$gal['galleryLayout'], "Présentation");
$this->addSelect($objPanel, $prefix.'galleryDisplayMode', 		$displayValues, $gal['galleryDisplayMode'], "Mode d'affichage");
$this->addInt   ($objPanel, $prefix.'galleryMaxRows', 							$gal['galleryMaxRows'], "Nombre max de lignes (au départ)", "", true,0,20);
$this->addInt   ($objPanel, $prefix.'galleryDisplayMoreStep',					$gal['galleryDisplayMoreStep'], "Nombre de lignes par ajout", "", true,1,20);
$this->addCBox  ($objPanel, $prefix.'galleryLastRowFull', 						$gal['galleryLastRowFull'], "Dernière ligne complète");
$this->addSelect($objPanel, $prefix.'galleryPaginationMode',	$paginationValues, 	$gal['galleryPaginationMode'], "Mode de pagination");
$this->addSelect($objPanel, $prefix.'gallerySorting', 			$sortingValues,	$gal['gallerySorting'], "Tri");
$this->addInt   ($objPanel, $prefix.'galleryMaxItems', 							$gal['galleryMaxItems'], "Nombre max de photos", "", true,0,1000);
$this->addSelect($objPanel, $prefix.'thumbnailAlignment', 		$alignValues, 	$gal['thumbnailAlignment'], "Alignement des vignettes");
$this->addInt   ($objPanel, $prefix.'thumbnailGutterWidth', 					$gal['thumbnailGutterWidth'], "Largeur des gouttières", "", true,0,50);
$this->addInt   ($objPanel, $prefix.'thumbnailGutterHeight', 					$gal['thumbnailGutterHeight'], "Hauteur des gouttières", "", true,0,50);

$objForm->addPanel($objImagePanel = new ploopi\form_panel('nano_panel_image','Affichage des grandes images'));
$this->addCBox  ($objImagePanel, $prefix.'thumbnailOpenImage', 					$gal['thumbnailOpenImage'], "activation du mode");

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;','onclick' => "uiReset()")));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

?>

