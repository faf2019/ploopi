<?php
/**
 * NanoGallery : Classe du contrôleur
 * inclus : méthodes d'insertion de champs de saisie
 *
 * @author JPP
 * @copyright DSIC-EST
 */

namespace ploopi\nanogallery;
use ploopi;

class controller extends ploopi\controller {

	public function setBlock() {
		$this->addBlockMenu('<b>Galeries photo</b>', 'public', 'list');
		$arrGalleries = ploopi\nanogallery\nanogallery::getGalleries($this->getModuleId());
		foreach($arrGalleries as $objGallery) {
			$this->addBlockMenu(
				'<img src="./modules/nanogallery/img/images_alpha.png" style="width:24px;height:20px;margin-right:5px;"> '
				.$objGallery['label'], 'public', "display&id=${objGallery['id']}"
			);
		}
	}


	// Ajoute une saisie de texte simple
	function addText($panel, $field, $value, $label, $prefix ='', $placeholder = '', $required = false, $disabled = false, $maxlen = 255) {
		$panel->addField(
			new ploopi\form_field(
				'input:text', 
				$label, 
				$value, 
				$prefix.$field, 
				$prefix.$field, 
				array(
					'placeholder' => $placeholder,
					'maxlength' => $maxlen,
					'required' => $required,
					'disabled' => $disabled
				)
			)
		);
	}

	// Ajoute une saisie de nombre entier
	function addInt($panel, $field, $value, $label, $prefix='', $placeholder = '', $required = false, $disabled = false, $maxlen = 10) {
		$panel->addField(
			new ploopi\form_field(
				'input:text',
				$label,
				$value,
				$prefix.$field, 
				$prefix.$field, 
				array(
					'placeholder' => $placeholder,
					'datatype' => 'int',
					'required' => $required,
					'disabled' => $disabled,
					'autocomplete' => false,
					'spellcheck' => false,
					'autocorrect' => false,
					'maxlength' => $maxlen
				)
			)
		);
	}

	// Ajoute une case à cocher
	function addCBox($panel, $field, $value, $label, $prefix ='', $tabidx ='') {
		$panel->addField(new ploopi\form_hidden('0', $prefix.$field));
		$panel->addField( 
			new ploopi\form_html(
				'<div id="'.$prefix.$field.'_form"><label for="'.$prefix.$field.'">'.$label
				.'<span></span></label><input type="checkbox" value="1" name="'
				.$prefix.$field.'" id="'.$prefix.$field.'" '.($value ? 'checked="checked"' : '')
				.(empty($tabidx) ? '' : ' tabindex="'.$tabidx.'"')
				.' class="'.'nano_check onclick"/><label for="'.$prefix.$field
				.'" class="nano_clabel"></label></div>'
			)
		);		
	}

	// Ajoute une saisie de couleur
	// N'utilise pas la fonction standard pour pouvoir ajouter 
	// un second label qui est en fait la visualisation de la case à cocher 
	function addClr($panel, $field, $value, $label, $prefix = '') {
		$panel->addField(
			new ploopi\form_field(
				'input:text', 
				$label, 
				$value, 
				$prefix.$field, 
				$prefix.$field, 
				array(
					'placeholder' => '#XXXXXX',
					'class' => 'wcfg_clr jscolor {hash:true,required:false}',
					'style' => 'width:60px'
				)
			)
		);
	}
	
	// Ajoute une boîte de sélection
	static function addSelect($panel, $field, $arrValues, $value, $label, $prefix = '') {
		$panel->addField(
			new ploopi\form_select(
				$label,
				$arrValues,
				$value,
				$prefix.$field,
				$prefix.$field,
				array()
			)
		);
	}

}

