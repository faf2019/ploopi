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
$this->addInt   ($objPanel, 'thumbnailWidth', 			$gal['thumbnailWidth'], "Largeur vignette", $prefix, "en pixels", true, $disabled);
$disabled = ($gal['galleryLayout'] == 'cascading');
$this->addInt   ($objPanel, 'thumbnailHeight', 			$gal['thumbnailHeight'], "Hauteur vignette", $prefix, "en pixels", true, $disabled);
$this->addInt   ($objPanel, 'thumbnailBorderVertical',	$gal['thumbnailBorderVertical'], "Epaisseur de la bordure verticale", $prefix, "en pixels", true);
$this->addInt   ($objPanel, 'thumbnailBorderHorizontal',$gal['thumbnailBorderHorizontal'], "Epaisseur de la bordure horizontale", $prefix, "en pixels", true);
$this->addClr   ($objPanel, 'thumbnailBorderColor', 	$gal['thumbnailBorderColor'], "Couleur du bord des vignettes", $prefix);
$this->addClr   ($objPanel, 'thumbnailBgColor', 		$gal['thumbnailBgColor'], "Couleur du fond des vignettes", $prefix);
$this->addSelect($objPanel, 'thumbnailDisplayTransition', $transitionValues, $gal['thumbnailDisplayTransition'], "Transition", $prefix);
$this->addInt   ($objPanel, 'thumbnailDisplayTransitionDuration', $gal['thumbnailDisplayTransitionDuration'], "Durée de la transition", $prefix, "en ms", true);
$this->addInt   ($objPanel, 'thumbnailDisplayInterval', $gal['thumbnailDisplayInterval'], "Intervalle de transition", $prefix, "en ms", true);

$objForm->addPanel($objHoverPanel = new ploopi\form_panel('nano_panel_hover','Effets au passage'));
$this->addSelect($objHoverPanel, 'thumbnailHoverEffect1', $hoverValues, $gal['thumbnailHoverEffect1'], "Effet 1", $prefix);
$this->addSelect($objHoverPanel, 'thumbnailHoverEffect2', $hoverValues, $gal['thumbnailHoverEffect2'], "Effet 2", $prefix);
$this->addSelect($objHoverPanel, 'thumbnailHoverEffect3', $hoverValues, $gal['thumbnailHoverEffect3'], "Effet 3", $prefix);

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-left:4px;')));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

