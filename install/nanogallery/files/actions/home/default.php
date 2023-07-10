<?php
/**
 * Reroutage de l'action par défaut (liste des galeries) si spcolor est installé
 *
 * @author JPP
 * @copyright DSIC-EST
 */
// Cas par défaut

if ($this->isColorOk()) {
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=public&action=list'));
} else {
	echo ploopi\skin::get()->open_simplebloc('ERREUR : Module manquant');
	?>
	<div style="font-size:140%;color:#A00000;padding:10px;font-weight:bold;">Le module système "spcolor" doit être installé.</div>
	<?php
	echo ploopi\skin::get()->close_simplebloc();
}
