// -----------------------------------------------------------------------------------
//
// switch_banner.js
//
// Fonction switch_banner
//
// -----------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------
// Gère les classes CSS amenant à changer l'image du bandeau
// -----------------------------------------------------------------------------------

function switch_banner(elid, maxbg , vtest ) {
	var maxbg = (typeof maxbg !== 'undefined') ? maxbg : 4;
	var vtest = (typeof vtest !== 'undefined') ? vtest : true;
	var old_crt = crt_banner_bg;
	next_bg(maxbg, vtest);
	document.getElementById(elid + crt_banner_bg).classList.toggle("active");
	document.getElementById(elid + old_crt).classList.toggle("active");
}

function next_bg(maxbg, vtest) {
	crt_banner_bg++;
	if (crt_banner_bg > maxbg) crt_banner_bg = 1;
	if (vtest == true) {
		if (banner_bgs[crt_banner_bg - 1] == "") next_bg(maxbg, vtest);
	}
}
