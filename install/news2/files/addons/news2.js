//////////////////////////////////////////////////////////////////////////////////////////////////////
//
//	Code utile pour un carrousel de news
//
//////////////////////////////////////////////////////////////////////////////////////////////////////

var news2_catalog_name = "news2_catalog_all";
var news2_catalog = -1;
var news2_count_all;
var news2_count_opt = [];
var news2_arr_all = [];
var news2_arr_opt = [];
var news2_crtnews = 0;
var news2_clicked = false;


// ---------------------------------------------------------------------------------------------------
// function setNews2Catalog(idx)
// ---------------------------------------------------------------------------------------------------
// Sélectionne le catalogue courant
// ---------------------------------------------------------------------------------------------------
// Paramètre idx :
//		-1 : Tout
//		 0 : A la Une
//		>0 : Id de la catégorie
// ---------------------------------------------------------------------------------------------------
function setNews2Catalog(idx) {
	news2_select(false);
	try {
		document.getElementById(news2_catalog_name).classList.remove('crt');
	} catch(e) {}
	var el;
	switch(idx) {
		case -1  :
		case '-1': news2_catalog_name = "news2_catalog_all"; break;
		case 0   :
		case '0' : news2_catalog_name = "news2_catalog_hot"; break;
		default  : news2_catalog_name = "news2_catalog_cat" + idx; break;
	}
	try {
		document.getElementById(news2_catalog_name).classList.add('crt');
	} catch(e) {}
	news2_catalog = idx;
	news2_crtnews = 0;
	news2_next(true);
	return false;
}

// ---------------------------------------------------------------------------------------------------
// function news2_next(click)
// ---------------------------------------------------------------------------------------------------
// Sélectionne la news suivante du catalogue en cours
// ---------------------------------------------------------------------------------------------------
// Paramètre click :
//		true  : Suite à une manipulation de l'utilisateur, le prochain appel sera ignoré
//		false : Appel par timer, le prochain appel sera traité
// ---------------------------------------------------------------------------------------------------
function news2_next(click) {
	var newsNb = ((news2_catalog < 0) ? news2_count_all : news2_count_opt[news2_catalog]);
	var newCrt = ((news2_crtnews == newsNb) ? 1 : news2_crtnews + 1);
	news2_go(newCrt,click);
}

// ---------------------------------------------------------------------------------------------------
// function news2_go(new_crt, click)
// ---------------------------------------------------------------------------------------------------
// Sélectionne la news dont l'index dans la liste du catalogue en cours est donné en paramètre
// ---------------------------------------------------------------------------------------------------
// Paramètre new_crt : index dans la liste du catalogue en cours, commence à 1
// Paramètre click   :
//		true  : Suite à une manipulation de l'utilisateur, le prochain appel sera ignoré
//		false : Appel par timer, le prochain appel sera traité
// Si 'click' est faux et que news2_clicked est vrai, la fonction ne fait que désactiver cette dernière
// ---------------------------------------------------------------------------------------------------
function news2_go(new_crt, click) {
	if (click || !news2_clicked ) {
		news2_select(false);
		news2_crtnews = new_crt;
		news2_select(true);
	}
	news2_clicked = click;
	return false;
}

// ---------------------------------------------------------------------------------------------------
// function news2_select(toSelect)
// ---------------------------------------------------------------------------------------------------
// Sélectionne ou désectionne la news courante et son bouton en ajoutant ou supprimant la classe "crt"
// ---------------------------------------------------------------------------------------------------
// Paramètre toSelect   :
//		true  : Sélectionne
//		false : Déselectionne
// ---------------------------------------------------------------------------------------------------
function news2_select(toSelect) {
	if (news2_crtnews > 0) {
		// Bouton
		var btn;
		switch(news2_catalog) {
			case -1   :
			case '-1' : btn = "news2_btn_all_" + news2_crtnews; break;
			case 0    :
			case '0'  : btn = "news2_btn_hot_" + news2_crtnews; break;
			default   : btn = "news2_btn_cat" + news2_catalog + "_" + news2_crtnews; break;
		}
		// News
		var crtNews = (news2_catalog < 0) ? news2_arr_all[news2_crtnews - 1] : news2_arr_opt[news2_catalog][news2_crtnews - 1];
		// Go
		if (toSelect) {
			try {
				document.getElementById(btn).classList.add("crt");
			} catch(e) {}
			document.getElementById("news2_" + crtNews).classList.add("crt");
			document.getElementById("news2_title_" + crtNews).classList.add("crt");
		} else {
			try {
				document.getElementById(btn).classList.remove("crt");
			} catch(e) {}
			document.getElementById("news2_" + crtNews).classList.remove("crt");
			document.getElementById("news2_title_" + crtNews).classList.remove("crt");
		}
	}
}
