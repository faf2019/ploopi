<?php
/**
 * NanoGallery : Liste avec actions pour les utilisateurs autorisés
 *
 * @author JPP
 * @copyright DSIC-EST
 */

use ploopi\str;
use ploopi\nanogallery\nanogallery;

// Récupération du modèle
$arrImg = nanogallery::getGalleries($this->getModuleid());

// Vue
echo ploopi\skin::get()->open_simplebloc('Liste des galeries photo');

// Onglets
$strTab = $this->getAction();
$arrTabs = array();
$arrTabs['list'] = array('title' => 'Liste des galeries photo',
    'url' => ploopi\crypt::urlencode("admin.php?entity=publi&action=default")
);
if (ploopi\acl::isactionallowed(nanogallery::ACTION_CREATE)) {
	$arrTabs['create'] = array('title' => 'Créer une galerie photo',
        'url' => ploopi\crypt::urlencode("admin.php?entity=admin&action=edit")
    );
}
echo ploopi\skin::get()->create_tabs($arrTabs, $strTab);


// Initialisation du tableau contenant les galeries
// ------------------------------------------------
// Colonnes
$img_columns = array();
$img_columns['left']['name'] = array('label' => 'Nom de la galerie', 'width' => 200, 'options' => array('sort' => true));
$img_columns['right']['nbfiles'] = array('label' => 'Nb images', 'width' => 100, 'options' => array('sort' => true));
$img_columns['right']['folder'] = array('label' => 'Dossier', 'width' => 180, 'options' => array('sort' => true));
$img_columns['auto']['description'] = array('label' => 'Description', 'options' => array('sort' => true));
$img_columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => 60);

// Lignes
$img_values = array();

$c = 0;
if (!empty($arrImg)) {
	foreach ($arrImg as $img) {

		$img_values[$c]['values']['name'] = array('label' => str::htmlentities($img['label']));
		$img_values[$c]['values']['description'] = array('label' => str::htmlentities($img['description']));
		$img_values[$c]['values']['folder'] = array('label' => str::htmlentities($img['name']));
		$img_values[$c]['values']['nbfiles'] = array('label' => str::htmlentities($img['nbelements']));

		$arrActions = array();
		
		if (ploopi\acl::isactionallowed(nanogallery::ACTION_MODIFY)) {
		    $arrActions[] = '<a title="Modifier" href="'
			.ploopi\crypt::urlencode("admin.php?entity=admin&action=edit&id={$img['id']}")
			.'"><img alt="Modifier" src="./modules/nanogallery/img/edit_alpha.png" style="width:24px;height:20px;" /></a>';
		}
				
		if (ploopi\acl::isactionallowed(nanogallery::ACTION_DELETE)) {
		    $arrActions[] = '<a title="Supprimer" href="javascript:ploopi.confirmlink(\''
			.ploopi\crypt::urlencode("admin-light.php?entity=admin&action=op_delete&id={$img['id']}")
			.'\',\'Êtes-vous certain de vouloir supprimer cette galerie ?\');">'
			.'<img alt="Supprimer" src="./modules/nanogallery/img/trash_alpha.png" style="width:24px;height:20px;"/></a>';
		}
		
		$img_values[$c]['values']['actions'] = ['label' =>  implode('', $arrActions)];
		$img_values[$c]['description'] = str::htmlentities($img['title']);
		$img_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?entity=public&action=display&id={$img['id']}");
		$c++;
	}

	// Affichage
	ploopi\skin::get()->display_array(
		$img_columns, 
		$img_values, 
		'array_imglist', 
		['sortable' => true, 'orderby_default' => 'date', 'sort_default' => 'DESC', 'limit' => 10]
	);
} else {
	echo "Aucune galerie";
}
echo ploopi\skin::get()->close_simplebloc();

