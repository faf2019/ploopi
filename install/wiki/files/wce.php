<?php
ploopi\module::init('wiki');
include_once './modules/wiki/classes/class_wiki_page.php';

// Hérité de webedit
global $articleid; // Id article sélectionné
global $headingid; // Id rubrique sélectionnée
global $template_name; // Template
global $template_path; // Chemin template
global $arrHeadings; // Rubriques
global $article; // Objet article sélectionné


// Récupération du module_id du module intégré
$intIdModule = $obj['module_id'];

$strWikiPageId = (empty($_GET['wikipageid'])) ? '' : $_GET['wikipageid'];

$objWikiPage = new wiki_page();

// cas particulier, recherche de root
if ($strWikiPageId == '' || !$objWikiPage->open($strWikiPageId, $intIdModule))
{
    ploopi\db::get()->query("SELECT id FROM ploopi_mod_wiki_page WHERE root = 1 AND id_module = {$intIdModule}");
    if (ploopi\db::get()->numrows())
    {
        $row = ploopi\db::get()->fetchrow();
        $strWikiPageId = $row['id'];
        $objWikiPage->open($strWikiPageId, $intIdModule);
    }
    // Pas de page root ! => Problème !!!!
    else return;
}

// Calcul des parents de la rubrique courante
$arrParents = array();
foreach(explode(';', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

// Gestion et affichage de l'historique de navigation
$arrPageHistory = ploopi\session::getvar('history_front', $intIdModule);
if (is_null($arrPageHistory)) $arrPageHistory = array();

if (empty($arrPageHistory) || $arrPageHistory[0] != $strWikiPageId)
{
    array_unshift($arrPageHistory, $strWikiPageId);
    if (sizeof($arrPageHistory) > 5) array_pop($arrPageHistory);
}

ploopi\session::setvar('history_front', $arrPageHistory, $intIdModule);

$arrUrlHistory = array();
foreach($arrPageHistory as $strPageId) $arrUrlHistory[] = "<a href=\"".wiki_generatefronturl($strPageId, $headingid, $articleid, $article->fields['metatitle'], $arrParents)."\">{$strPageId}</a>";

// Appel du moteur de rendu avec une fonction anonyme de réécriture de liens basée sur le règles de réécriture du frontoffice
?>
<div class="wiki_history" style="margin:4px 8px;padding-bottom:4px;border-bottom:1px dotted #ccc;">
    Pages visitées : <?php echo implode(' &raquo; ', $arrUrlHistory); ?>
</div>

<div id="wiki_page" class="wiki_page wiki_page_front"><?php echo wiki_render($objWikiPage->fields['content'], function($arrMatches) use($intIdModule, $articleid, $headingid, $article, $arrParents) {

    if (!empty($arrMatches[1]))
    {
        $strPageId = ploopi\str::iso8859_clean(ploopi\str::html_entity_decode(strip_tags($arrMatches[1])));

        $objWikiPage = new wiki_page();
        if ($objWikiPage->open($strPageId, $intIdModule))
        {
            $strLinkClass = 'wiki_link';
            $strTitle = 'Ouvrir la page &laquo; '.ploopi\str::htmlentities($strPageId).' &raquo;';

            return '<span class="'.$strLinkClass.'"><a title="'.$strTitle.'" href="'.wiki_generatefronturl($strPageId, $headingid, $articleid, $article->fields['metatitle'], $arrParents).'">'.$arrMatches[1].'</a><img src="./modules/wiki/img/ico_link.png" /></span>';
        }
        else
        {
            $strLinkClass = 'wiki_link_notfound';
            $strTitle = 'Cette page n\'existe pas';

            return '<span class="'.$strLinkClass.'"><a title="'.$strTitle.'" href="javascript:void(0);">'.$arrMatches[1].'</a><img src="./modules/wiki/img/ico_link.png" /></span>';
        }

    }

    return '';
} ); ?></div>

