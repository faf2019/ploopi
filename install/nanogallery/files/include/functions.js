/* ===================================================
 *
 *  Fonctions de gestion du dossier 'doc' sélectionné
 *       pour la saisie générale de la galerie
 *
 * ===================================================
 */

// Appelé au clic sur un dossier
function nano_updateFolder(elem, id, name) {
	document.getElementById('id_folder').value = id;
	document.getElementById('foldername').value = name;
	$("#treeview_inner a").removeClass("active");
	elem.classList.add("active");
}

// Appelé au chargement et au reset
function nano_initFolder(id) {
	$("#treeview_inner a").removeClass("active");
	// aucun dossier encore choisi (création d'une galerie) : rien à surligner
	var a = $("#treeview_node" + id + " a")[1];
	if (a) a.classList.add("active");
}

/* ===================================================
 *
 *  Gestion du reset des sliders de saisie d'entiers
 *  et des couleurs spcolor
 *
 * ===================================================
 */

var sliders = {};

function uiReset() {
	resetSliders();
	resetColors();
}

function resetSliders() {
	for (var k in sliders) {
		var v = sliders[k];
		$('#' + k).slider('value',v);
	}
}

// Couleurs : valeur d'origine portée par le champ (data-nano-reset) ; sélecteur spcolor
// facultatif : à défaut, simple champ texte
function resetColors() {
	jQuery('[data-nano-reset]').each(function() {
		var v = this.getAttribute('data-nano-reset');
		if (jQuery.fn.spectrum) jQuery(this).spectrum("set", v);
		else jQuery(this).val(v);
	});
}

/* ===================================================
 *
 *  Initialisations et actions par attributs (aucun
 *  script ni gestionnaire en ligne : CSP stricte)
 *
 * ===================================================
 */

// Saisie d'un entier : curseur jQuery UI lié au champ (data-nano-slider)
function nano_enhance(node) {
	jQuery(node).find('[data-nano-slider]').addBack('[data-nano-slider]').each(function() {
		if (this.getAttribute('data-nano-ready') || !jQuery.fn.slider) return;
		this.setAttribute('data-nano-ready', '1');
		var field = this;
		var opts = {};
		try { opts = JSON.parse(field.getAttribute('data-nano-slider')); } catch (e) { opts = {}; }
		var slider = jQuery('#slider_' + field.id);
		slider.slider({ min: opts.min, max: opts.max, disabled: !!opts.disabled, value: opts.value, slide: function(event, ui) { field.value = ui.value; } });
		field.value = slider.slider('value');
		sliders['slider_' + field.id] = opts.value;
	});
	jQuery(node).find('[data-nano-initfolder]').addBack('[data-nano-initfolder]').each(function() {
		nano_initFolder(this.getAttribute('data-nano-initfolder'));
	});
}

jQuery(document).on('change', '[data-nano-slider]', function() {
	var slider = jQuery('#slider_' + this.id);
	if (slider.hasClass('ui-slider')) slider.slider('value', this.value);
});

jQuery(function() {
	ploopi.popup.addenhancer(nano_enhance);
});

ploopi.actions.nano_updatefolder = function(el, event, id, name) {
	event.preventDefault();
	nano_updateFolder(el, id, name);
};

ploopi.actions.nano_initfolder = function(el, event, id) {
	nano_initFolder(id);
};

ploopi.actions.nano_uireset = function(el, event) {
	uiReset();
};
