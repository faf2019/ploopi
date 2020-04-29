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
	$("#treeview_node" + id + " a")[1].classList.add("active");
}

