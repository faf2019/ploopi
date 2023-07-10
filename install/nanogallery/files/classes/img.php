<?php
/**
 * Classe des photos nanogallery
 *
 * @author JPP
 * @copyright DSIC/SGAMI-EST
 */

namespace ploopi\nanogallery;
use ploopi;

class img extends ploopi\data_object {

    /**
     * Constructeur
     */
	public function __construct() {	parent::__construct('ploopi_mod_nanogallery_img','id'); }

    /**
     * Sauvegarde
     */
	public function save() {
		if (empty($this->fields['id']) || strlen($this->fields['id']) != 32) return false;
		return parent::save();
	}

}
