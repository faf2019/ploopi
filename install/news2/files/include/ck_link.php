<?php
/*
    Copyright (c) 2007-2008 Ovensia
    Contributors hold Copyright (c) to their code submissions.
*/

/**
 * Explorateur de rubriques/pages intégré à CKeditor (ou pas)
 */

/**
 * Ce script peut être appelé depuis un module externe.
 * Il faut donc 'choisir' le moduleid de travail.
 * Par défaut on prend le module WEBEDIT sur lequel on est déjà.
 */
 
// On va chercher le 1er dispo dans les modules accessibles depuis l'espace de travail courant.
$webedit_idm = 0;
foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm) {
	if (!empty($_SESSION['ploopi']['modules'][$idm]['active']) && $_SESSION['ploopi']['modules'][$idm]['moduletype'] == 'webedit') 
		$webedit_idm = $idm;
}

if ($webedit_idm) {
    ploopi\module::init('webedit');

	// Chargement des rubriques et articles
	$treeview = ploopi\news2\tools::news2_gettreeview(
		webedit_getheadings($webedit_idm), 
		webedit_getarticles($webedit_idm)
	);

	echo ploopi\skin::get()->display_treeview(
		$treeview['list'], 
		$treeview['tree'], 
		null, 
		$_GET['hid']
	);
	ploopi\system::kill();
} else 
	echo "Aucun module WEBEDIT disponible";

