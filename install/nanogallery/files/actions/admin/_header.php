<?php
/**
 * Header du menu "admin"
 *
 * @author JPP
 * @copyright DSIC/SGAMI-EST
 */
use ploopi\nanogallery\nanogallery;

// Tests ------------------------------------------------------------
if (!ploopi\acl::isactionallowed([nanogallery::ACTION_CREATE,nanogallery::ACTION_MODIFY]))
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));

$isCreate = empty($_GET['id']) || !is_numeric($_GET['id']);

// Initialisation ---------------------------------------------------
$moduleid = $this->getModuleId();
if (!$isCreate) {
	$gal = new nanogallery();
	$gal->open($_GET['id']);
}
$strTab = self::getAction();

// Traitement -------------------------------------------------------
echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
$title = ($isCreate ? "Création d'une galerie" : 'Edition de la galerie "'.$gal->fields['label'].'"');
echo ploopi\skin::get()->open_simplebloc($title);

if ($isCreate) {
	$arrTabs = array(
		'general' => array(
		    'title' => 'Général',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=edit")
		)
	);

} else {
	$id = $_GET['id'];
	$arrTabs = array(
		'edit' => array(
		    'title' => 'Général',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=edit&id=$id")
		),
		'gallery' => array(
		    'title' => 'Galerie',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=gallery&id=$id")
		),
		'thumbs' => array(
		    'title' => 'Vignettes',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=thumbs&id=$id")
		),
		'labels' => array(
		    'title' => 'Labels',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=labels&id=$id")
		),
		'nav' => array(
		    'title' => 'Navigation / Filtres',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=nav&id=$id")
		)
	);

	if (ploopi\acl::isactionallowed(nanogallery::ACTION_DESCRIPTION)) {
		$arrTabs['description'] = array(
		    'title' => 'Descriptions',
		    'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=description&id=$id")
		);
	}

}

echo ploopi\skin::get()->create_tabs($arrTabs, $strTab);
?>
<div class="ploopi_tabs">
	<?php 
	if (!$isCreate) {
		?>
		<a href="<?php echo ploopi\crypt::urlencode("admin.php?entity=public&action=display&id={$_GET['id']}"); ?>">
			<img src="./modules/nanogallery/img/eye_alpha.png" style="width:30px;height:18px;"> Afficher la galerie</a>
		<?php
	}
	?>
	<a href="<?php echo ploopi\crypt::urlencode("admin.php?entity=public&action=list"); ?>">
		<img src="./modules/nanogallery/img/angle_double-left_alpha.png" style="width:20px;height:18px;"> Retour à la liste des galeries</a>
</div>
<?php

