<?php
/**
 * Classe des galeries photo nanogallery
 *
 * @author JPP
 * @copyright DSIC/SGAMI-EST
 */

namespace ploopi\nanogallery;
use ploopi;

class nanogallery extends ploopi\data_object {

	const ACTION_ANY        = -1;	// Action : Une au moins
	const ACTION_CREATE     = 10;	// Action : Ajouter une news
	const ACTION_MODIFY     = 20;	// Action : Modifier une news
	const ACTION_DELETE     = 30;	// Action : Supprimer une news
	const OBJECT_NANOGAL    = 1;	// Objet NanoGallery

    /**
     * Méthode statique qui
     * retourne la liste des galeries de l'instance du module
     *
     * @return array
     */
	static public function getGalleries($id_module) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('ng.id, ng.label, ng.description, fo.name, fo.nbelements');
        $objQuery->add_from('ploopi_mod_nanogallery ng');
        $objQuery->add_leftjoin('ploopi_mod_doc_folder fo ON (fo.id = ng.id_folder)');
        $objQuery->add_where("ng.id_module = $id_module");
		return $objQuery->execute()->getarray();
	}

    /**
     * Constructeur
     */
	public function __construct() {	parent::__construct('ploopi_mod_nanogallery','id'); }

    /**
     * Charge l'objet depuis le sgbd
	 * Utilise avec les valeurs par défaut de la base de données sans argument
     */
	public function open(...$args) {
        if(sizeof($args) < 1 ) {
			$this->init_description();
			return true;
		} else {
			return parent::open($args);
		}
	}

    /**
     * Initialise l'objet
	 * Utilise les valeurs par défaut de la base de données 
     */
    public function init_description() {
		$this->fields['id'] = 0;
		$this->save();
		$this->delete();
		$this->fields['id'] = null;
		$this->new = true;
	}

    /**
     * Sauvegarde
     */
	public function save() {
		// Affecte automatiquement l'utilisateur, le workspace et le module
		$this->setuwm();
		// Traitements spécifiques terminés, appel du parent
		return parent::save();
	}
	
    /**
     * Affiche la galerie
     */
	public function display() {
		// Ajout fichiers CSS et JS
		global $template_body;
		$template_body->assign_block_vars('module_css', array('PATH' => './modules/nanogallery/include/css/nanogallery2.min.css'));
		$template_body->assign_block_vars('module_css', array('PATH' => './modules/nanogallery/include/css/nanogallery2.woff.min.css'));
		$template_body->assign_block_vars('module_js', array('PATH' => './modules/nanogallery/include/js/jquery.nanogallery2.min.js'));

		$f = $this->fields;
		// ID du DIV
		$divid = "nanogallery_".$f['id'];
		// Effets au survol
		$thumbnailHoverEffect2 = $f['thumbnailHoverEffect1'];
		if (!empty($f['thumbnailHoverEffect2'])) { $thumbnailHoverEffect2 .= '|'.$f['thumbnailHoverEffect2']; }
		if (!empty($f['thumbnailHoverEffect3'])) { $thumbnailHoverEffect2 .= '|'.$f['thumbnailHoverEffect3']; }
		// Gestion de la présentation
		$thumbnailWidth = $f['galleryLayout'] == 'justified' ? 'auto' : $f['thumbnailWidth'];
		$thumbnailHeight = $f['galleryLayout'] == 'cascading' ? 'auto' : $f['thumbnailHeight'];
		// Bordures du cadre
		$frameBorderHorizontal = empty($f['frameBorderHorizontal']) ? 0 : $f['frameBorderHorizontal'];
		$frameBorderVertical = empty($f['frameBorderVertical']) ? 0 : $f['frameBorderVertical'];
		// Styles du cadre
		$style = 'border-style: solid;';
		if (!empty($f['frameBgColor'])) $style .= "background:{$f['frameBgColor']};";
		if (!empty($f['frameBorderColor'])) $style .= "border-color:{$f['frameBorderColor']};";
		$style .= "border-top-width:${frameBorderHorizontal}px;border-bottom-width:${frameBorderHorizontal}px;";
		$style .= "border-left-width:${frameBorderVertical}px;border-right-width:${frameBorderVertical}px;";
		if (!empty($f['frameBorderRadius'])) $style .= "border-radius:{$f['frameBorderRadius']}px;";
		if (!empty($f['frameInternalVertical'])) $style .= "padding-left:{$f['frameInternalVertical']}px;padding-right:{$f['frameInternalVertical']}px;";
		if (!empty($f['frameInternalHorizontal'])) $style .= "padding-top:{$f['frameInternalHorizontal']}px;padding-bottom:{$f['frameInternalHorizontal']}px;";
		if (!empty($f['frameExternalVertical'])) $style .= "margin-left:{$f['frameExternalVertical']}px;margin-right:{$f['frameExternalVertical']}px;";
		if (!empty($f['frameExternalHorizontal'])) $style .= "margin-top:{$f['frameExternalHorizontal']}px;margin-bottom:{$f['frameExternalHorizontal']}px;";

		?>
			<div ID="<?php echo $divid; ?>" style="<?php echo $style; ?>" data-nanogallery2='{
				"thumbnailOpenImage": <?php echo $f['thumbnailOpenImage']; ?>,
				"thumbnailWidth": "<?php echo $thumbnailWidth; ?>",
				"thumbnailHeight": "<?php echo $thumbnailHeight; ?>",
				"thumbnailBorderVertical": <?php echo $f['thumbnailBorderVertical']; ?>,
				"thumbnailBorderHorizontal": <?php echo $f['thumbnailBorderHorizontal']; ?>,
				"colorScheme": {
				  "thumbnail": {
				    "background": "<?php echo $f['thumbnailBgColor']; ?>",
				    "borderColor": "<?php echo $f['thumbnailBorderColor']; ?>"
				  }
				},
				"galleryDisplayMode": "<?php echo $f['galleryDisplayMode']; ?>",
				"galleryMaxRows": <?php echo $f['galleryMaxRows']; ?>,
				"galleryDisplayMoreStep": <?php echo $f['galleryDisplayMoreStep']; ?>,
				"galleryLastRowFull": <?php echo $f['galleryLastRowFull']; ?>,
				"galleryPaginationMode": "<?php echo $f['galleryPaginationMode']; ?>",
				"thumbnailDisplayTransition": "<?php echo $f['thumbnailDisplayTransition']; ?>",
				"thumbnailDisplayTransitionDuration": <?php echo $f['thumbnailDisplayTransitionDuration']; ?>,
				"thumbnailDisplayInterval": <?php echo $f['thumbnailDisplayInterval']; ?>,
				"thumbnailLabel": {
			          "display": <?php echo $f['thumbnailLabelDisplay']; ?>,
					  "position": "<?php echo $f['thumbnailLabelPosition']; ?>",
					  "align": "<?php echo $f['thumbnailLabelAlignement']; ?>",
					  "titleMultiLine": <?php echo $f['thumbnailLabelTitleMultiline']; ?>,
					  "displayDescription": <?php echo $f['thumbnailLabelDisplayDescription']; ?>,
					  "descriptionMultiLine": <?php echo $f['thumbnailLabelDescriptionMultiline']; ?>
				},
				"thumbnailAlignment": "<?php echo $f['thumbnailAlignment']; ?>",
				"galleryMaxItems": <?php echo $f['galleryMaxItems']; ?>,
				"gallerySorting": "<?php echo $f['gallerySorting']; ?>",
				"thumbnailGutterWidth": <?php echo $f['thumbnailGutterWidth']; ?>,
				"thumbnailGutterHeight": <?php echo $f['thumbnailGutterHeight']; ?>,
				"thumbnailHoverEffect2": "<?php echo $thumbnailHoverEffect2; ?>",
				"displayBreadcrumb": <?php echo $f['displayBreadcrumb']; ?>,
				"breadcrumbAutoHideTopLevel": <?php echo $f['breadcrumbAutoHideTopLevel']; ?>,
				"breadcrumbOnlyCurrentLevel": <?php echo $f['breadcrumbOnlyCurrentLevel']; ?>,
				"galleryFilterTags": "<?php echo $f['galleryFilterTags']; ?>",
				"thumbnailLevelUp": <?php echo $f['thumbnailLevelUp']; ?>	
			  }'>

				<?php
				$arrImages = $this->getImages();
				$width = ($f['thumbnailWidth'] == 'auto' ? 200: $f['thumbnailWidth']) * 3;
				$height = ($f['thumbnailHeight'] == 'auto' ? 200 : $f['thumbnailHeight']) * 3;
				//$thumbs = new ploopi\mimethumb($width , $height, 0, 'jpg');
				//$thumbs->getThumbnail($file);

				foreach ($arrImages as $img) {
					// $thumb = ploopi\crypt::urlencode("index-light.php?ploopi_op=nano_thumb&md5id=".$img['md5id']."&v=".$img['version']."&w=$width&h=$height");
					echo '<a href="'.$img['file'].'" data-ngthumb="'.$img['file'].'" data-ngdesc="">'.$img['name'].'</a>';
				}
				?>
			</div>
		<?php
	}


    /**
     * Renvoie la liste des images de la galerie
     */
	private function getImages() {
		$arrImages = array();
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('f.id, f.md5id, f.name, f.version');
        $objQuery->add_from('ploopi_mod_doc_file f');
        $objQuery->add_where("f.id_folder = ".$this->fields['id_folder']);
        $objQuery->add_where("LCASE(f.extension) IN ('jpg','jpeg','gif','png')");
		$objResponse = $objQuery->execute();
		if(!$objResponse->numrows($images)) { return arrImages; }
	    while ($image = $objResponse->fetchrow()) {
			$row['file'] = "documents/".$image['md5id'].'/'.$image['name'];
			$row['name'] = $image['name'];
			$row['md5id'] = $image['md5id'];
			$row['version'] = $image['version'];
			$arrImages[] = $row;
		}
		return $arrImages;
	}

}
