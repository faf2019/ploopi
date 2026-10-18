<?php
/**
 * Reroutage de l'action par défaut vers la liste des galeries
 *
 * @author JPP
 * @copyright DSIC-EST
 */
// Cas par défaut : la liste des galeries (spcolor est facultatif, cf. controller::setBlock)
ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=public&action=list'));
