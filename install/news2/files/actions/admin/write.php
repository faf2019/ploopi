<?php

// Autorisation
if (!ploopi\acl::isactionallowed(ploopi\news2\tools::ACTION_WRITE)) {
	ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?entity=forbidden'));
}

$arrCateg = ploopi\news2\tools::getCategoriesArray(self::getModuleid());
$arrHot = [ 0 => _PLOOPI_NO, 1 => _PLOOPI_YES];
$news = new ploopi\news2\news2();

// Affichage du titre en fonction du type d'opération (ajout/modif)
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $news->open($_GET['id'])) {
	echo ploopi\skin::get()->open_simplebloc(ploopi\str::htmlentities(str_replace("LABEL",$news->fields['title'],'Modifier la news \'LABEL\'')));
} else {
	echo ploopi\skin::get()->open_simplebloc('Rédiger une News');
	$news->init_description();
}
$localdate = (!empty($news->fields['date_publish'])) 
			? ploopi\date::timestamp2local($news->fields['date_publish'],_PLOOPI_DATEFORMAT_US) 
			: array('date' => ploopi\date::getdate(_PLOOPI_DATEFORMAT_US), 'time' => ploopi\date::gettime());

?><script src="./vendor/ckeditor/ckeditor/ckeditor.js"></script><?php

// Formulaire
$strUrl = "admin-light.php?entity=admin&action=op_write";
if (!empty($news->fields['id'])) $strUrl .= "&id=".$news->fields['id'];
$arrFormOptions = array('class' => 'ploopi_generate_form news2form');
$objForm = new ploopi\form('news2_form', ploopi\crypt::urlencode($strUrl), 'post', $arrFormOptions);

// Panneau 1
$objForm->addPanel($objPanel = new ploopi\form_panel(
	'',
	null,
	['style' => 'width:49%;float:left;clear:none;border:none;']
));

$objPanel->addField(new ploopi\form_field(
	'input:text', 
	'Titre :', 
	$news->fields['title'], 
	"news_title", 
	"news_title", 
	['title' => "Titre de la nouvelle", 'required' => true]
));

$objPanel->addField(new ploopi\form_select(
	'Catégorie :', 
	$arrCateg, 
	$news->fields['id_cat'], 
	"news_id_cat", 
	"news_id_cat", 
	['title' => "Catégorie à laquelle appartient la nouvelle"]
));

$objPanel->addField(new ploopi\form_field(
	'input:text', 'Source :', 
	$news->fields['source'], 
	"news_source", 
	"news_source", 
	['title' => "Source d'où est tirée l'information"]
));

$objPanel->addField(new ploopi\form_select(
	'A la Une :', 
	$arrHot, 
	$news->fields['hot'], 
	"news_hot", 
	"news_hot", 
	['style' => "width:60px;"]
));

// Panneau 2
$objForm->addPanel($objPanel = new ploopi\form_panel(
	'',
	null,
	['style' => 'width:49%;float:left;clear:none;border:none;']
));

$objPanel->addField(new ploopi\form_field(
	'input:date', 
	'Date de Publication', 
	ploopi\str::htmlentities($localdate['date']), 
	"news_date_publish", 
	"news_date_publish", 
	['title' => "Date de Publication", 'style' => 'width:100px;']
));

$objPanel->addField(new ploopi\form_field(
	'input:text', 
	'Heure de Publication', 
	ploopi\str::htmlentities($localdate['time']), 
	"newsx_time_publish", 
	"newsx_time_publish", 
	['title' => "Heure de Publication", 'style' => 'width:100px;']
));

$objPanel->addField(new ploopi\form_field(
	'input:text', 
	'Titre du Lien :', 
	$news->fields['urltitle'], 
	"news_urltitle", 
	"news_urltitle", 
	['title' => "Texte affiché pour le lien"]
));

ploopi\news2\tools::addLink(
	$objPanel,
	'url',
	$news->fields['url'],
	'Lien interne ou externe',
	'news_'
);

ploopi\news2\tools::addImg(
	$objPanel,
	'background',
	ploopi\str::htmlentities($news->fields['background']),
	'Image de fond',
	'news_'
);

// Panneau 3 - CKeditor
$objForm->addPanel($objPanel = new ploopi\form_panel('',null,['style' => 'width:100%;border:none;']));

$objPanel->addField(new ploopi\form_html(
	'<textarea style="clear:both;width:95%;padding:0 5px;margin:0 5px;" 
	name="fck_news_content" id="editor">'.$news->fields['content'].'</textarea>'
));

// Boutons
$objForm->addButton( new ploopi\form_button(
	'input:button', 
	'Annuler', 
	null, 
	null, 
	[
		'style' => 'margin-left:4px;', 
		'onclick' => "document.location.href='".ploopi\crypt::urlencode("admin.php?entity=admin&action=default")."';" 
	]
));

$objForm->addButton( new ploopi\form_button(
	'input:reset', 
	'Réinitialiser', 
	null, 
	null, 
	['style' => 'margin-left:4px;']
));

$objForm->addButton( new ploopi\form_button(
	'input:submit', 
	'Enregistrer', 
	null, 
	null, 
	['style' => 'margin-left:4px;']
));


// Rendu
echo $objForm->render();
echo ploopi\skin::get()->close_simplebloc(); 

?>
<script>
    // Ajout d'un plugin externe
    CKEDITOR.plugins.addExternal('tag', '<?php echo _PLOOPI_BASEPATH.'/modules/news2/include/ckeditor/plugins/tag/'; ?>', 'plugin.js');

    // http://docs.ckeditor.com/#!/guide/plugin_sdk_styles
    CKEDITOR.plugins.add( 'tag', {
        init: function( editor ) {
            var pluginDirectory = '<?php echo _PLOOPI_BASEPATH.'/modules/news2/include/ckeditor/plugins/tag/'; ?>';
            editor.addContentsCss( pluginDirectory + 'styles.css' );
        }
    } );

    // http://docs.ckeditor.com/#!/guide/dev_file_browser_api
    CKEDITOR.replace( 'editor', {
        customConfig: '<?php echo _PLOOPI_BASEPATH.'/modules/news2/include/ckeditor/config.js'; ?>',
        filebrowserBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=doc_selectfile'); ?>',
        filebrowserImageBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=doc_selectimage'); ?>',
        // Chargement de styles complémentaires (on remet le fichier par défaut en 1er)
        // Puis on ajoute la feuille de style des plugins...
        contentsCss: [
            CKEDITOR.basePath+'contents.css',
            '<?php echo _PLOOPI_BASEPATH; ?>/modules/news2/include/ckeditor/plugins/tag/styles.css',
        ]
    });

	SetUrl = function(url) { if ($(id_field_url)) $(id_field_url).value = url; };
	
	function search_img(elem) {
		id_field_url = elem;
		ploopi.openwin('<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=doc_selectimage"); ?>', 800, 600, 'popup');
		return false;
	}

</script>




