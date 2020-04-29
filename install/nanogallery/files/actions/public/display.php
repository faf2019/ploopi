<?php
/**
 * NanoGallery : Affichage backoffice de la galerie
 *
 * @author JPP
 * @copyright DSIC-EST
 */

// Id de la galerie obligatoire
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=error'));
}

$objGallery = new ploopi\nanogallery\nanogallery();
if($objGallery->open($_GET['id'])) {
		?>
		<div class="ploopi_tabs">
			<a href="<?php echo ploopi\crypt::urlencode("admin.php?entity=public&action=list"); ?>">
				<img src="./modules/nanogallery/img/angle_double-left_alpha.png" 
					style="width:20px;height:18px;">Retour à la liste des galeries</a>
		</div>
		<?php
		echo ploopi\skin::get()->open_simplebloc('Affichage de la galerie "'.$objGallery->fields['label'].'"');
		$objGallery->display();
		echo ploopi\skin::get()->close_simplebloc();
} else {
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=error'));
}


