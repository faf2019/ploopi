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

	private $colorOk = false;

	public function setBlock() {
		/**
		 * Le module spcolor (sélecteur de couleurs) est facultatif : il n'est pas livré
		 * avec le dépôt. Sans lui, les couleurs se saisissent en texte (#rrggbb, rgba…).
		 * classExists() teste le fichier : class_exists() seul déclenchait l'autoload d'un
		 * fichier absent.
		 */
		if (ploopi\loader::classExists('ploopi\spcolor\color') && class_exists('ploopi\spcolor\color')) {
			// Fichiers css et js pour spcolor
			ploopi\spcolor\color::install();
			$this->colorOk = true;
		}

		// CSS pour sliders
		$template_body = nanogallery::template();
		if (is_object($template_body)) $template_body->assign_block_vars('module_css', array('PATH' => './vendor/components/jqueryui/themes/base/all.css'));

		// Les sous-menus
		$this->addBlockMenu('<b>Galeries photo</b>', 'public', 'list');
		$arrGalleries = ploopi\nanogallery\nanogallery::getGalleries($this->getModuleId());
		foreach($arrGalleries as $objGallery) {
			$this->addBlockMenu(
				'<img src="./modules/nanogallery/img/images_alpha.png" style="width:24px;height:20px;margin-right:5px;"> '
				.ploopi\str::htmlentities($objGallery['label']), 'public', "display&id={$objGallery['id']}"
			);
		}
	}

	// Renvoie la disponibilité du module spcolor
	protected function isColorOk() { return $this->colorOk; }

	// Ajoute une saisie de texte simple
	public function addText($panel, $field, $value, $label, $placeholder = '', $required = false, $disabled = false, $maxlen = 255) {
		$panel->addField(
			new ploopi\form_field(
				'input:text', $label, $value, $field, $field, 
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
	public function addInt($panel, $field, $value, $label, $placeholder = '', $required = false, 
		$min = 0, $max=255, $disabled = false, $maxlen = 10, $tabidx ='') {

		$panel->addField(
			new ploopi\form_html(
				'<div id="'.$field.'_form">
					<label for="'.$field.'" '.($required?' class="required"':'').'>'.$label.'<span></span></label>
					<input type="number" value="'.$value.'" name="'.$field.'" 
						id="'.$field.'" '.(empty($tabidx) ? '' : ' tabindex="'.$tabidx.'"')
						.($required?'required="required" ':'').($disabled?'disabled="disabled" ':'')
						.'autocomplete="off" autocorrect="off" spellcheck="false" maxlength="10"
						min="'.$min.'" max="'.$max.'"
						data-nano-slider="'.ploopi\str::htmlentities(json_encode(array('min' => (int) $min, 'max' => (int) $max, 'value' => (int) $value, 'disabled' => (bool) $disabled))).'">
					<div id="slider_'.$field.'" style="margin-top:5px;width:calc(48% - 80px);float:right;"></div>
				</div>'
			)
		);		
	}

	// Ajoute une case à cocher
	// N'utilise pas la fonction standard pour pouvoir ajouter 
	// un second label qui est en fait la visualisation de la case à cocher 
	public function addCBox($panel, $field, $value, $label, $tabidx ='') {
		$panel->addField(new ploopi\form_hidden('0', $field));
		$panel->addField( 
			new ploopi\form_html(
				'<div id="'.$field.'_form">
					<label for="'.$field.'">'.$label.'<span></span></label>
					<input type="checkbox" value="1" name="'.$field.'" 
						id="'.$field.'" '.($value ? 'checked="checked"' : '')
						.(empty($tabidx) ? '' : ' tabindex="'.$tabidx.'"')
						.' class="'.'nano_check onclick"/>
					<label for="'.$field.'" class="nano_clabel"></label>
				</div>'
			)
		);		
	}

	// Ajoute une saisie de couleur
	public function addClr($panel, $field, $value, $label) {
		$panel->addField(
			new ploopi\form_field('input:text', $label, $value, $field, $field,
				// Valeur d'origine, rendue par « Réinitialiser » (uiReset)
				array_merge($this->colorOk ? array('class' => $this->getColorClass()) : array('placeholder' => '#rrggbb', 'maxlength' => 32), array('dataset' => array('nano-reset' => $value))))
		);
	}
	
	// Ajoute une boîte de sélection
	public function addSelect($panel, $field, $arrValues, $value, $label) {
		$panel->addField(
			new ploopi\form_select($label, $arrValues, $value, $field, $field, array())
		);
	}

	// Renvoie les classes css pour la saisie des couleurs
	private function getColorClass() {
		$classes = $this->getParam('colortype');
		switch ($this->getParam('colorclass')) {
			case 'rect'   : $classes .= ' palette dark'; break;
			case 'square' : $classes .= ' square palette dark'; break;
			case 'circle' : $classes .= ' circle palette dark'; break;
		}
		return $classes;
	}

	// Demande de paramètres
	public function getNbImagesPerPage() { return $this->getParam('nbimgs'); }
	public function getNbGallerysPerPage() { return $this->getParam('nbgals'); }

}

