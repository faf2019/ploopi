<?php
/**
 * NanoGallery : Onglet général
 *
 * @author JPP
 * @copyright DSIC-EST
 */
use ploopi\nanogallery\nanogallery;
use ploopi\nanogallery\folders;

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
$tab = 'edit';	// Pour retoursur le même onglet

// dossiers
// Récupération des dossiers visibles
$arrFolders = folders::getfolders($moduleid);
// Récupération de la structure du treeview
$arrTreeview = folders::gettreeview($arrFolders);

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_save&id=$id&tab=$tab";
$objForm = new ploopi\form('nano_gen_form', ploopi\crypt::urlencode($strUrl), 'post', array(
	'class' => 'ploopi_generate_form nano'
));

// Panels
$objForm->addPanel($objPanel = new ploopi\form_panel('nano_panel_gen','Propriétés générales'));
$this->addText($objPanel, 'label', $gal['label'], "Label", $prefix, "Label de la galerie", true);
$objPanel->addField(new ploopi\form_field('textarea', 'description', $gal['description'], $prefix.'Description', null, array('style' => 'height:50px;')));
$this->addCBox($objPanel, 'useAlbums', $gal['useAlbums'], "Avec les sous-dossiers en albums", $prefix);
$objPanel->addField(new ploopi\form_hidden($gal['id_folder'], $prefix.'id_folder','id_folder',['required' => "required",'label' => "Dossier associé"] ));
$foldername = folders::getFolderName($gal['id_folder']);
$objPanel->addField(new ploopi\form_html(
	'<div style="clear:both;">
		<label for="foldername" class="required">Dossier associé<span></span></label>
		<div style="margin-left:18%;">
			<input type="text" name="foldername" id="foldername" value="'.$foldername.'" disabled tabindex="3" required="required" maxlength="255" style="width:250px;" />
			<div id="treeview" style="margin-top: -25px; margin-left: 320px;">
				<div id="treeview_inner">'
					.folders::display_treeview($arrTreeview['list'], $arrTreeview['tree'], null, -1, true)
			.'</div>
			</div>
		</div>
	</div>'
));

// Boutons
$objForm->addButton( new ploopi\form_button('input:reset', 'Réinitialiser', 'reinit', 'reinit', 
	array(
		'style' => 'margin-left:4px;', 
		'onclick' => "nano_initFolder(${gal['id_folder']});"
	)
));
$objForm->addButton( new ploopi\form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:4px;')));

// Rendu
echo $objForm->render();

?>
<script>nano_initFolder(<?php echo $gal['id_folder']; ?>);</script>

