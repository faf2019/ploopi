<?php
/*
    Copyright (c) 2009 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Fonctions, constantes, variables globales
 *
 * @package wiki
 * @subpackage global
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Définition des constantes
 */

/**
 * Action : MODIFIER
 */

define ('_WIKI_ACTION_PAGE_MODIFY',     10);

/**
 * Action : RENOMMER
 */

define ('_WIKI_ACTION_PAGE_RENAME',     11);

/**
 * Action : SUPPRIMER
 */

define ('_WIKI_ACTION_PAGE_DELETE',     12);

/**
 * Action : LOCKER
 */

define ('_WIKI_ACTION_PAGE_LOCK',       20);

/**
 * Action : DELOCKER
 */

define ('_WIKI_ACTION_PAGE_UNLOCK',     21);

/**
 * Objet : PAGE
 */

define ('_WIKI_OBJECT_PAGE',            1);


/**
 * Traitement des liens internes par expression régulière
 *
 * @param array $arrMatches Tableau des correspondances
 * @return string lien modifié
 *
 * @see wiki_render
 */
function wiki_internal_links($arrMatches, $intIdModule = null)
{
    if (empty($intIdModule)) $intIdModule = $_SESSION['ploopi']['moduleid'];

    if (!empty($arrMatches[1]))
    {
        $strPageId = ovensia\ploopi\str::iso8859_clean(ovensia\ploopi\str::html_entity_decode(strip_tags($arrMatches[1])));

        $objWikiPage = new wiki_page();
        if ($objWikiPage->open($strPageId, $intIdModule))
        {
            $strLinkClass = 'wiki_link';
            $strTitle = 'Ouvrir la page &laquo; '.ovensia\ploopi\str::htmlentities($strPageId).' &raquo;';
            $strOp = '';
        }
        else
        {
            $strLinkClass = 'wiki_link_notfound';
            $strTitle = 'Créer la page &laquo; '.ovensia\ploopi\str::htmlentities($strPageId).' &raquo;';
            $strOp = 'op=wiki_page_modify&';
        }


        return '<span class="'.$strLinkClass.'"><a title="'.$strTitle.'" href="'.ovensia\ploopi\crypt::urlencode_trusted("admin.php?{$strOp}wiki_page_id=".urlencode($strPageId)).'">'.$arrMatches[1].'</a><img src="./modules/wiki/img/ico_link.png" /></span>';
    }

    return '';
}

/**
 * Traitement des liens externes et ancres (href) par expression regulière
 *
 * @param array $arrMatches Tableau des correspondances
 * @return string lien modifié
 *
 * @see wiki_render
 */
function wiki_links($arrMatches)
{
    if (sizeof($arrMatches) == 3)
    {
        switch($arrMatches[1][0]) // On regarde le 1er caractère du lien
        {
            case '#': // cas particulier : ancre
                return '<span class="wiki_link_ext"><a title="Aller à l\'ancre &laquo; '.ovensia\ploopi\str::htmlentities(strip_tags($arrMatches[2])).' &raquo;" href="'.$arrMatches[1].'">'.$arrMatches[2].'</a><img src="./modules/wiki/img/ico_link_anchor.png" /></span>';
            break;

            default: // autres cas : liens externes
                return '<span class="wiki_link_ext"><a title="Ouvrir le lien externe &laquo; '.ovensia\ploopi\str::htmlentities(strip_tags($arrMatches[1])).' &raquo;" href="'.$arrMatches[1].'">'.$arrMatches[2].'</a><img src="./modules/wiki/img/ico_link_ext.png" /></span>';
            break;

        }
    }

    return '';
}

/**
 * Fonction de callback pour la création de liens sur les adresse email
 */

function wiki_make_links_cb($arrMatches)
{
    return stripslashes((strlen($arrMatches[4]) > 0 ? $arrMatches[1].'<a href="mailto:'.$arrMatches[3].'">'.$arrMatches[3].'</a>' : $arrMatches[1].$arrMatches[3]));
}

/**
 * Rend les liens simples et les adresses de courriel cliquables
 *
 * @param string $text le texte à traiter
 * @return string le texte modifié
 */
function wiki_make_links($strContent)
{
    // Liens
    $strContent = preg_replace('@(^|([^\'":!<>]\s*))([hf][tps]{2,4}:\/\/[^\s<>"\'()]{4,})@mi', '$2<a href="$3">$3</a>', $strContent);

    // Adresses email
    $strContent = preg_replace_callback ('/(^|([\s]))(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/mi', 'wiki_make_links_cb', $strContent);

    return $strContent;
}

/**
 * Rendu de la page via le moteur de rendu Textile
 *
 * @param string $strContent chaîne brute utilisant la syntaxe Wiki Textile
 * @return string contenu HTML
 *
 * @see wiki_internal_links
 * @see wiki_links
 */
function wiki_render($strContent, $cbInternalLinks = 'wiki_internal_links')
{
    $objTextile = new \Netcarver\Textile\Parser();

    // Pré-traitement du formatage de code
    // Extraction des zones à formater
    preg_match_all('@(<code[^>]*class="([a-z0-9]*)"[^>]*>(.*?)</code>)@si', $strContent, $arrMatches);

    $arrSearch = array();
    $arrReplace = array();
    $arrHighlight = array();

    foreach($arrMatches[0] as $intKey => $strRaw)
    {
        $arrSearch[] = $strRaw;
        $arrReplace[] = '{{{highlight'.sizeof($arrReplace).'}}}';
        $arrHighlight[] = wiki_highlight($arrMatches[3][$intKey], $arrMatches[2][$intKey]);
    }

    $strContent = wiki_make_links(str_replace($arrSearch, $arrReplace, $strContent));
    //$strContent = str_replace($arrSearch, $arrReplace, $strContent);

    // Renderer textile
    $strTextile = utf8_decode($objTextile->textileThis(utf8_encode($strContent)));

    // Traitement des liens externes
    $strTextile = preg_replace_callback ('/<a[^>]*href="(.*)"[^>]*>(.*)<\/a>/i', 'wiki_links', $strTextile);
    // Traitement des liens internes
    $strTextile = preg_replace_callback ('/\[\[(.*)\]\]/i', $cbInternalLinks, $strTextile);

    // Post-traitement du formatage de code
    $strTextile = str_replace($arrReplace, $arrHighlight, $strTextile);

    return $strTextile;
}


/**
 * Génère la mise en forme du code source inséré
 * @param string $strContent contenu à mettre en forme
 * @param string $strFormat formattage à utiliser (php, c, java, etc...)
 */
function wiki_highlight($strContent, $strFormat = 'php')
{
    $arrAllowedFormats = array('cpp', 'css', 'diff', 'html', 'java', 'sql', 'ruby', 'php', 'xml', 'sh');

    $strLines = implode(range(1, count(explode("\n", $strContent))), '<br />');

    /*
    if (in_array($strFormat, $arrAllowedFormats))
    {
        require_once 'Text/Highlighter.php';
        require_once 'Text/Highlighter/Renderer/Html.php';
        $objHL = Text_Highlighter::factory($strFormat);
        $objHL->setRenderer(new Text_Highlighter_Renderer_Html());
        $strContent = $objHL->highlight($strContent);
    }
    else $strContent = '<pre>'.ovensia\ploopi\str::nl2br(ovensia\ploopi\str::htmlentities($strContent)).'</pre>';
    */

    $strContent = ovensia\ploopi\str::htmlentities($strContent, null, null, false);

    return "<div class=\"hl-content\"><table><tr><td class=\"hl-num\">\n$strLines\n</td><td class=\"hl-src\"><pre><code class=\"{$strFormat}\">{$strContent}</code></pre></td></tr></table></div>";
}


/**
 * Retourne un tableau contenant les règles de réécriture proposées par le module WIKI
 *
 * @return array tableau contenant les règles de réécriture
 */
function wiki_getrewriterules()
{

//     if ($booRewriteRuleFound = (preg_match('/wiki\/(.*)-(h([0-9]*)){0,1}(a([0-9]*)){0,1}\/(.*)\.html/', $arrParsedURI['path'], $arrMatches) == 1))

    return array(
        'patterns' => array(
            '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)&wikipageid=(.*)/'
        ),
        'replacements' => array(
            'wiki/<FOLDERS><TITLE>-h$1a$2/$3.<EXT>',
        )
    );
}

/**
 * Génére une URL de page pour le frontoffice
 * en fonction du contexte courant (rubriques, page, ...)
 */
function wiki_generatefronturl($strPageId, $intHeadingId, $intArticleId, $strArticleTitle, $arrParents) { return ovensia\ploopi\str::urlrewrite("index.php?headingid={$intHeadingId}&articleid={$intArticleId}&wikipageid=".ovensia\ploopi\str::rawurlencode($strPageId), wiki_getrewriterules(), $strArticleTitle, $arrParents); }


/**
 * Recherche dans l'objet
 * Retourne les occurences
 */
function wiki_object_search($strQueryString, $rowObject)
{
    return $arrRelevance = ovensia\ploopi\search_index::search($strQueryString, _WIKI_OBJECT_PAGE, null, $rowObject['id_module']);
}

/**
 * Résultat de recherche dans l'objet
 * Met à jour le template
 */
function wiki_object_searchresult($template_body, $rowResult, $objArticle, $arrHeadings)
{
    include_once './modules/wiki/classes/class_wiki_page.php';

    $objWikiPage = new wiki_page();
    if (!$objWikiPage->open($rowResult['id_record'], $rowResult['id_module'])) return false;

    $arrParents = array();
    if (isset($arrHeadings['list'][$objArticle->fields['id_heading']])) foreach(explode(';', $arrHeadings['list'][$objArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

    $link = wiki_generatefronturl($rowResult['id_record'], $objArticle->fields['id_heading'], $objArticle->fields['id'], $objArticle->fields['metatitle'], $arrParents);

    $template_body->assign_block_vars('switch_search.result',
        array(
            'RELEVANCE' => sprintf("%.02f", $rowResult['relevance']),
            'TITLE' => ovensia\ploopi\str::htmlentities($rowResult['id_record']),
            'TITLE_RAW' => $rowResult['id_record'],
            'AUTHOR' => ovensia\ploopi\str::htmlentities($objArticle->fields['author']),
            'AUTHOR_RAW' => $objArticle->fields['author'],
            'EXTRACT' => '',
            'METATITLE' => ovensia\ploopi\str::htmlentities($rowResult['id_record']),
            'METATITLE_RAW' => $rowResult['id_record'],
            'METAKEYWORDS' => ovensia\ploopi\str::htmlentities($objArticle->fields['metakeywords']),
            'METAKEYWORDS_RAW' => $objArticle->fields['metakeywords'],
            'METADESCRIPTION' => ovensia\ploopi\str::htmlentities($objArticle->fields['metadescription']),
            'METADESCRIPTION_RAW' => $objArticle->fields['metadescription'],
            'DATE' => ($objArticle->fields['timestp']!='') ? current(ovensia\ploopi\date::timestamp2local($objArticle->fields['timestp'])) : '',
            'SIZE' => sprintf("%.02f", strlen($objWikiPage->fields['content'])/1024),
            'LINK' => $link,
            'SHORT_LINK' => ovensia\ploopi\str::cut($link, 50, 'middle')
        )
    );

    return true;
}
