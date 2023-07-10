<?php
/**
 * Classe des galeries photo nanogallery
 *
 * @author JPP
 * @copyright DSIC/SGAMI-EST
 */

namespace ploopi\nanogallery;
use ploopi;
use ploopi\nanogallery\img;

class nanogallery extends ploopi\data_object {

	const ACTION_ANY        	= -1;	// Action : Une au moins
	const ACTION_CREATE     	= 10;	// Action : Ajouter une news
	const ACTION_MODIFY     	= 20;	// Action : Modifier une news
	const ACTION_DELETE     	= 30;	// Action : Supprimer une news
	const ACTION_DESCRIPTION	= 40;	// Action : Modifier titre et description des photos
	const OBJECT_NANOGAL    	= 1;	// Objet NanoGallery

	private $_folders = array();

    /**
     * Méthode statique qui
     * retourne la liste des galeries de l'instance du module
     *
     * @return array
     */
	static public function getGalleries($id_module) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('ng.id, ng.label, ng.description, fo.name, fo.nbelements');
        $objQuery->add_select("(SELECT count(*) FROM ploopi_mod_doc_file WHERE id_folder = fo.id AND LCASE(extension) IN ('jpg','jpeg','gif','png')) as nbmedias");
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
		if (!empty($f['frameBorderColor'])) $style .= "border-color:{$f['frameBorderColor']};";
		$style .= "border-top-width:${frameBorderHorizontal}px;border-bottom-width:${frameBorderHorizontal}px;";
		$style .= "border-left-width:${frameBorderVertical}px;border-right-width:${frameBorderVertical}px;";
		if (!empty($f['frameBorderRadius'])) $style .= "border-radius:{$f['frameBorderRadius']}px;";
		if (!empty($f['frameBgColor'])) $style .= "background:{$f['frameBgColor']};";
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
				"thumbnailLevelUp": <?php echo $f['thumbnailLevelUp']; ?>,
				"allowHTMLinData": true
			  }'>

				<?php $this->getFolderContent($f['id_folder'],true); ?>
			</div>
		<?php
	}

    /**
     * Affiche le contenu de l'album (récursif s'il contient des albums)
     */
	private function getFolderContent($idFolder, $toplevel = false) {
		// Albums
		if ($this->fields['useAlbums']) {
			$albums = $this->getAlbums($idFolder);
			if (!empty($albums)) {
				foreach($albums as $album) {
					if (!is_null($album['thumbnailName']) || $this->fields['flatAlbums'] == false) {
						if (is_null($album['thumbnailName'])) $album['thumbnailName'] = "modules/nanogallery/img/Folder-Icon.jpg";
						$str = '<a href="" data-ngkind="album" data-ngthumb="'.$album['thumbnailName'].'"';
						if (!$toplevel && $this->fields['flatAlbums'] == false) $str .= ' data-ngalbumid="'.$album['ngalbumid'].'"';
						$str .= ' data-ngid="'.$album['id'].'">ALBUM "'.$album['name'].'"</a>';
						echo $str;
					}
					$this->getFolderContent($album['id']);
				}
			}
		}
		// Images
		$arrImages = $this->getImages($idFolder);
		$imgIdx = 1;
		foreach ($arrImages as $img) {
			$crtImgId = $idFolder.sprintf('%04s', $imgIdx++);
			$str = '<a href="'.$img['file'].'" data-ngthumb="'.$img['file'].'" data-ngid="'.$img['ngid'].'"';
			if (!$toplevel) $str .= ' data-ngalbumid="'.$img['ngalbumid'].'"';
			$str .= ' data-ngdesc="'.$img['description'].'<hr>'.$img['specs'].'">'.$img['title'].'</a>';
			echo $str;
		}		
	}

    /**
     * Renvoie la liste de toutes les images de la galerie
     */
	public function getAllImages() {
		$idFolder = $this->fields['id_folder'];
		// Images
		$arrImages = $this->getImages($idFolder);
		// Albums
		if ($this->fields['useAlbums']) {
			$arrImages = $this->addAlbumImages($arrImages, $idFolder);
		}
		return $arrImages;
	}

    /**
     * Rajoute les images des albums à la liste (récursif)
     */
	private function addAlbumImages($arrImages, $idFolder ) {
		$albums = $this->getAlbums($idFolder);
		if (!empty($albums)) {
			foreach($albums as $album) {
				$newImages = $this->getImages($album['id']);
				if (!empty($newImages)) {
					foreach($newImages as $img) {
						$arrImages[] = $img;
					}
				}
				$arrImages = $this->addAlbumImages($arrImages,$album['id']);
			}
		}
		return $arrImages;
	}

    /**
     * Renvoie la liste des images de la galerie
     */
	private function getImages($idFolder = null) {
		if (is_null($idFolder)) $idFolder = $this->fields['id_folder'];
		$imgIdx = 1;
		$arrImages = array();
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('id,md5id,name,version,size,lcase(extension) as extension');
        $objQuery->add_from('ploopi_mod_doc_file');
        $objQuery->add_where("id_folder = ".$idFolder);
        $objQuery->add_where("LCASE(extension) IN ('jpg','jpeg','gif','png')");
		$objResponse = $objQuery->execute();
		if(!$objResponse->numrows()) { return $arrImages; }
	    while ($image = $objResponse->fetchrow()) {
			// Infos depuis les tables 'doc'
			$row['ngid'] = $idFolder.sprintf('%04s', $imgIdx++);
			$row['ngalbumid'] = $idFolder;
			$row['file'] = "documents/".$image['md5id'].'/'.$image['name'];
			$row['name'] = $image['name'];
			$row['md5id'] = $image['md5id'];
			$row['version'] = $image['version'];
			$row['extension'] = $image['extension'];
			$size = intdiv( $image['size'], 1024);
			$row['size'] = $size >= 1000 ? number_format( $size / 1024 , 2, ",", " "). " Mo " : $size." Ko ";
			// Titre et description saisis ?
			$objImg = new img();
			if ($objImg->open($image['md5id'])) {
				$row['title'] = $objImg->fields['title'];
				$row['description'] = $objImg->fields['description'];
			} else {
				$row['title'] = substr($image['name'], 0, strlen($image['name']) - strlen($image['extension']) - 1);
				$row['description'] = '';
			}
			// Recherche spécifications
			include_once './modules/doc/class_docfile.php';
			$docfile = new \docfile();
			$docfile->openmd5($image['md5id']);
            $file = $docfile->getfilepath();
			$exif = exif_read_data($file,0,true);
			$specs = $exif['COMPUTED']['Width'].(empty($exif['COMPUTED']['Width']) ? '':'x').$exif['COMPUTED']['Height'].'<br>';
			$detail = '';
			if (isset($exif['COMPUTED']['ApertureFNumber'])) $detail .= $exif['COMPUTED']['ApertureFNumber'].' - ';
			if (isset($exif['EXIF']['ExposureTime'])) $detail .= $exif['EXIF']['ExposureTime'].' - ';
			if (isset($exif['EXIF']['FocalLengthIn35mmFilm'])) $detail .= $exif['EXIF']['FocalLengthIn35mmFilm'].'mm - ';
			if (isset($exif['EXIF']['ISOSpeedRatings'])) $detail .= ' ISO '.$exif['EXIF']['ISOSpeedRatings'];
			$specs .= (empty($detail) ? '' : $detail.'<br>');
			if (isset($exif['EXIF']['DateTimeOriginal'])) $specs .= 'Prise de vue '.$exif['EXIF']['DateTimeOriginal'];
			if (isset($exif['IFD0']['Model'])) $specs .= '<br>'.$exif['IFD0']['Model'];
			$row['specs'] = $row['size'].$specs;
			$arrImages[] = $row;
		}
		return $arrImages;
	}

    /**
     * Renvoie l'adresse de l'image de l'album
	 * La première image, sinon une image standard du module
     */
	private function getAlbumThumbnail($idFolder) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('id,md5id,name,version,size,lcase(extension) as extension');
        $objQuery->add_from('ploopi_mod_doc_file');
        $objQuery->add_where("id_folder = ".$idFolder);
        $objQuery->add_where("LCASE(extension) IN ('jpg','jpeg','gif','png')");
	    $objQuery->add_limit("1");
		$objResponse = $objQuery->execute();
		$thumbnailName = null;
		if($objResponse->numrows() >= 1) { 
			$image = $objResponse->fetchrow();
			$thumbnailName = "documents/".$image['md5id'].'/'.$image['name'];
		}
	    return $thumbnailName;
	}

    /**
     * Renvoie les albums contenus dans le dossier en paramètre
     */
	private function getAlbums($idFolder) {
		$folders = $this->getfolders();
		$childs = [];
		$childsIds = $folders['tree'][$idFolder];
		foreach($childsIds as $id) {
			$childs[$id] = $folders['list'][$id];
			$childs[$id]['thumbnailName'] = $this->getAlbumThumbnail($id);	
			$childs[$id]['ngalbumid'] = $idFolder;
			$childs[$id]['id'] = $id;
		}
		return $childs;
	}

    /**
     * Charge et renvoie les données de l'arborescence de dossiers
     */
	private function getfolders() {
		if (empty($this->_folders)) 
			$this->_folders = ploopi\nanogallery\folders::getfolders($this->fields['id_module']);
		return $this->_folders;
	} 

}
