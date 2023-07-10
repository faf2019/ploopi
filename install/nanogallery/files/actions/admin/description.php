<?php
/**
 * NanoGallery : Descriptions des images
 *
 * @author JPP
 * @copyright DSIC-EST
 */

use ploopi\str;
use ploopi\nanogallery\nanogallery;

// Tests ------------------------------------------------------------
// Il faut le rôle ACTION_CREATE ou ACTION_MODIFY + le rôle ACTION_DESCRIPTION
if (!ploopi\acl::isactionallowed([nanogallery::ACTION_CREATE,nanogallery::ACTION_MODIFY]))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));
if (!ploopi\acl::isactionallowed(nanogallery::ACTION_DESCRIPTION))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));

// Suite des tests
$moduleid = $this->getModuleId();
$objGallery = new nanogallery();
if (empty($_GET['id']) || !is_numeric($_GET['id']) || !$objGallery->open($_GET['id'])) { 
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=error'));
}
// Récupération du modèle
$id = $objGallery->fields['id'];
$arrImages = $objGallery->getAllImages();

// Vue
echo ploopi\skin::get()->open_simplebloc('Edition des titres et description des photos de la galerie');

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_saveimg&id=$id";
?><form id="nano_img_form" name="nano_img_form" action="<?php echo ploopi\crypt::urlencode($strUrl); ?>" method="post"><?php
$objForm = new ploopi\form('nano_gen_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'));

// Initialisation du tableau contenant les galeries
// ------------------------------------------------
// Colonnes
$img_columns = array();
$img_columns['left']['image'] = array('label' => 'Image', 'width' => 120,);
$img_columns['left']['name'] = array('label' => 'Nom', 'width' => 220);
$img_columns['left']['specs'] = array('label' => 'Détails', 'width' => 240);
$img_columns['auto']['description'] = array('label' => 'Description');
$img_columns['left']['title'] = array('label' => 'Titre', 'width' => 300);

// Lignes
$imgs = array();
$pre = '00000';
$c = 0;
if (!empty($arrImages)) {
	foreach ($arrImages as $img) {
		$imgs[$c]['values']['image'] = array('label' => '<img src="'.$img['file'].'" style="height:75px;display:block;margin:auto;">' );
		$imgs[$c]['values']['name'] = array('label' => $img['name']);
		$imgs[$c]['values']['specs'] = array('label' => $img['specs']);
		$tmpid = '"title_'.$img['md5id'].'"';
		$tmp = '<input type="text" id='.$tmpid.' name='.$tmpid.' value="'.$img['title'].'" style="width:95%;">';
		$imgs[$c]['values']['title'] = array('label' => $tmp);
		$tmpid = '"desc_'.$img['md5id'].'"';
		$tmp = '<textarea type="text" id='.$tmpid.' name='.$tmpid
			.' rows="3" style="width:95%;max-height:60px!important;margin:1px 5px;">'.$img['description'].'</textarea>';
		$imgs[$c]['values']['description'] = array('label' => $tmp);
		$c++;
	}

	// Affichage
	ploopi\skin::get()->display_array($img_columns, $imgs, 'array_imglist', ['limit' => $this->getNbImagesPerPage() ]);

	// Boutons
	?><div style="margin-top:10px;" class="buttons">
		<button type="submit" name="" style="margin-left:4px;">Enregistrer</button>
		<button type="reset" name="" style="margin-left:4px;">R&eacute;initialiser</button>
	</div><?php

} else { echo "Aucune photo"; }

echo '</form>'.ploopi\skin::get()->close_simplebloc();

