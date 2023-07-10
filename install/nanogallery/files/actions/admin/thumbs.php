<?php
/**
 * NanoGallery : Onglets vignettes
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
$tab = 'thumbs';	// Pour retoursur le même onglet

$transitionValues = array(
	'none'			=> ['label' => 'none'],
	'fadeIn' 		=> ['label' => "fadeIn"],
	'slideUp' 		=> ['label' => "slideUp"],
	'slideDown' 	=> ['label' => "slideDown"],
	'scaleUp' 		=> ['label' => "scaleUp"],
	'scaleDown'		=> ['label' => 'scaleDown'],
	'flipDown' 		=> ['label' => "flipDown"],
	'flipUp' 		=> ['label' => "flipUp"],
	'slideDown2' 	=> ['label' => "slideDown2"],
	'slideUp2' 		=> ['label' => "slideUp2"],
	'slideRight'	=> ['label' => 'slideRight'],
	'slideLeft' 	=> ['label' => "slideLeft"],
	'slideDown2' 	=> ['label' => "slideDown2"],
	'randomScale' 	=> ['label' => "randomScale"],
);

$hoverValues = array(
	''						=> ['label' => ''],
	'toolsAppear'			=> ['label' => 'toolsAppear'],
	'toolsSlideDown' 		=> ['label' => "toolsSlideDown"],
	'toolsSlideUp' 			=> ['label' => "toolsSlideUp"],
	'imageBlurOff' 			=> ['label' => "imageBlurOff"],
	'imageBlurOn' 			=> ['label' => "imageBlurOn"],
	'imageGrayOff'			=> ['label' => 'imageGrayOff'],
	'imageGrayOn' 			=> ['label' => "imageGrayOn"],
	'imageSepiaOff' 		=> ['label' => "imageSepiaOff"],
	'imageSepiaOn' 			=> ['label' => "imageSepiaOn"],
	'borderLighter' 		=> ['label' => "borderLighter"],
	'borderDarker'			=> ['label' => 'borderDarker'],
	'scale120' 				=> ['label' => "scale120"],
	'labelAppear75' 		=> ['label' => "labelAppear75"],
	'labelOpacity50' 		=> ['label' => "labelOpacity50"],
	'scaleLabelOverImage'	=> ['label' => 'scaleLabelOverImage'],
	'overScale'				=> ['label' => 'overScale'],
	'overScaleOutside'		=> ['label' => 'overScaleOutside'],
	'descriptionAppear'		=> ['label' => 'descriptionAppear'],
	'slideUp'				=> ['label' => 'slideUp'],
	'slideDown'				=> ['label' => 'slideDown'],
	'slideRight'			=> ['label' => 'slideRight'],
	'imageScale150'			=> ['label' => 'imageScale150'],
	'imageScaleIn80'		=> ['label' => 'imageScaleIn80'],
	'imageScale150Outside'	=> ['label' => 'imageScale150Outside'],
	'imageSlideUp'			=> ['label' => 'imageSlideUp'],
	'imageSlideDown'		=> ['label' => 'imageSlideDown'],
	'imageSlideRight'		=> ['label' => 'imageSlideRight'],
	'imageSlideLeft'		=> ['label' => 'imageSlideLeft'],
	'labelSlideUpTop'		=> ['label' => 'labelSlideUpTop'],
	'labelSlideUp'			=> ['label' => 'labelSlideUp'],
	'labelSlideDown'		=> ['label' => 'labelSlideDown'],
	'descriptionSlideUp'	=> ['label' => 'descriptionSlideUp'],
);

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_save&id=$id&tab=$tab";
$objForm = new ploopi\form('nano_thumbs_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'
));

// Panels
$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_thumbs','Vignettes'));
$disabled = ($gal['galleryLayout'] == 'justified');
$this->addInt   ($objPanel, $prefix.'thumbnailWidth', 						$gal['thumbnailWidth'], "Largeur vignette", "en pixels", true,0,1000, $disabled);
$disabled = ($gal['galleryLayout'] == 'cascading');
$this->addInt   ($objPanel, $prefix.'thumbnailHeight', 						$gal['thumbnailHeight'], "Hauteur vignette", "en pixels", true,0,1000, $disabled);
$this->addInt   ($objPanel, $prefix.'thumbnailBorderVertical',				$gal['thumbnailBorderVertical'], "Epaisseur de la bordure verticale", "en pixels", true,0,100);
$this->addInt   ($objPanel, $prefix.'thumbnailBorderHorizontal',			$gal['thumbnailBorderHorizontal'], "Epaisseur de la bordure horizontale", "en pixels", true,0,100);
$this->addClr   ($objPanel, $prefix.'thumbnailBorderColor', 				$gal['thumbnailBorderColor'], "Couleur du bord des vignettes");
$this->addClr   ($objPanel, $prefix.'thumbnailBgColor', 					$gal['thumbnailBgColor'], "Couleur du fond des vignettes");
$this->addSelect($objPanel, $prefix.'thumbnailDisplayTransition', $transitionValues, $gal['thumbnailDisplayTransition'], "Transition");
$this->addInt   ($objPanel, $prefix.'thumbnailDisplayTransitionDuration', 	$gal['thumbnailDisplayTransitionDuration'], "Durée de la transition", "en ms", true,0,3000);
$this->addInt   ($objPanel, $prefix.'thumbnailDisplayInterval', 			$gal['thumbnailDisplayInterval'], "Intervalle de transition", "en ms", true,0,300);

$objForm->addPanel($objHoverPanel = new ploopi\form_panel('nano_panel_hover','Effets au passage'));
$this->addSelect($objHoverPanel, $prefix.'thumbnailHoverEffect1', $hoverValues, $gal['thumbnailHoverEffect1'], "Effet 1");
$this->addSelect($objHoverPanel, $prefix.'thumbnailHoverEffect2', $hoverValues, $gal['thumbnailHoverEffect2'], "Effet 2");
$this->addSelect($objHoverPanel, $prefix.'thumbnailHoverEffect3', $hoverValues, $gal['thumbnailHoverEffect3'], "Effet 3");

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;','onclick' => "uiReset()")));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

