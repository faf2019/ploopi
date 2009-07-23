<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Moteur de rendu frontoffice
 *
 * @package webedit
 * @subpackage frontoffice
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @global Template $template_body template
 * @global string $template_path chemin vers le template
 * @global string $webedit_mode mode d'édition : edit (édition) / render (rendu backoffice) / display (rendu frontoffice)
 */

// webedit_mode : edit / render / display => mode édition / rendu backoffice / affichage frontoffice
// readonly : true / false => article modifiable oui/non (charge fckeditor si false)
// type : draft / online => type du document : brouillon / en ligne

/**
 * Inclusion de la classe template (moteur du template)
 */
include_once './lib/template/template.php';

/**
 * Inclusion des classes du module
 */
include_once './modules/webedit/class_article.php';
include_once './modules/webedit/class_heading.php';

/**
 * Déclaration des variables globales
 * (les plugins/objets sont appelées par des fonctions et ont besoin de ces variables)
 */

global $template_body;
global $template_path;
global $webedit_mode;
global $article;
global $articleid;
global $headingid;

// Date du jour (utile pour vérifier les dates de publication)
$today = ploopi_createtimestamp();

$type = (empty($_GET['type'])) ? '' : $_GET['type'];
$webedit_mode = (empty($_GET['webedit_mode'])) ? 'display' : $_GET['webedit_mode'];
$readonly = (empty($_GET['readonly'])) ? 0 : $_GET['readonly'];

// id article passé en param ?
$articleid = (!empty($_REQUEST['articleid']) && is_numeric($_REQUEST['articleid'])) ? $_REQUEST['articleid'] : '';

// id rubrique passé en param ?
$headingid = (!empty($_REQUEST['headingid']) && is_numeric($_REQUEST['headingid'])) ? $_REQUEST['headingid'] : '';

// code d'erreur renvoyé (principalement 404)
$intErrorCode = 0;

// vérification des paramètres
if ($webedit_mode == 'edit' && !ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT)) $webedit_mode = 'display';

if ($webedit_mode == 'render' || $webedit_mode == 'display')
{
    $readonly = 1;
    $type = '';
}

// requête de recherche ?
$query_string = (empty($_REQUEST['query_string'])) ? '' : $_REQUEST['query_string'];

// requête sur un tag ?
$query_tag = (empty($_REQUEST['query_tag'])) ? '' : $_REQUEST['query_tag'];

// module frontoffice ?
$template_moduleid = (empty($_REQUEST['template_moduleid']) || !is_numeric($_REQUEST['template_moduleid']) || !isset($_SESSION['ploopi']['modules'][$_REQUEST['template_moduleid']]) || !file_exists("./modules/{$_SESSION['ploopi']['modules'][$_REQUEST['template_moduleid']]['moduletype']}/template_content.php")) ? '' : $_REQUEST['template_moduleid'];

// récupération des rubriques
$arrHeadings = webedit_getheadings();

// récupération des partages (mode connecté uniquement)
$arrShares = webedit_getshare();

if ($query_string != '') // Recherche intégrale
{
    $headingid = $arrHeadings['tree'][0][0];
}
elseif ($query_tag != '') // Recherche par tag
{
    $headingid = $arrHeadings['tree'][0][0];
}
elseif (!empty($template_moduleid)) // Module frontoffice
{
    $headingid = $arrHeadings['tree'][0][0];
}
else // affichage standard rubrique/page
{
    // on recherche d'abord la rubrique (qui détermine le template)
    // en fonction des parametres articleid et headingid

    if (empty($articleid)) // pas de lien vers un article
    {
        // homepage
        if (empty($headingid)) $headingid = $arrHeadings['tree'][0][0];
        else // accès par une rubrique
        {
            // rubrique inconnue
            if (!isset($arrHeadings['list'][$headingid]))
            {
                // renvoi à la racine
                $headingid = $arrHeadings['tree'][0][0];
                $intErrorCode = 404;
            }
        }

        if (!$arrHeadings['list'][$headingid]['private'] || isset($arrShares[$arrHeadings['list'][$headingid]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
        {
            switch($arrHeadings['list'][$headingid]['content_type'])
            {
                case 'article_redirect':
                    if ($arrHeadings['list'][$headingid]['linkedpage'])
                    {
                        $article = new webedit_article($type);
                        if (    $article->open($arrHeadings['list'][$headingid]['linkedpage'])
                            &&  ($article->fields['timestp_published'] <= $today || $article->fields['timestp_published'] == 0)
                            &&  ($article->fields['timestp_unpublished'] >= $today || $article->fields['timestp_unpublished'] == 0)
                            )
                        {
                            $articleid = $arrHeadings['list'][$headingid]['linkedpage'];
                            $headingid = $article->fields['id_heading'];
                        }
                    }
                break;

                case 'url_redirect':
                    if (!empty($arrHeadings['list'][$headingid]['url'])) ploopi_redirect($arrHeadings['list'][$headingid]['url'], false, false);
                break;

                case 'article_first':
                    // Cas standard, traité plus bas
                case 'headings':
                    // Traité à l'affichage
                break;
            }
        }

    }
    else // lien vers un article
    {
        if (empty($headingid))
        {
            $article = new webedit_article($type);
            if (    $article->open($articleid)
                &&  ($article->fields['timestp_published'] <= $today || $article->fields['timestp_published'] == 0)
                &&  ($article->fields['timestp_unpublished'] >= $today || $article->fields['timestp_unpublished'] == 0)
                )
            {
                $headingid = $article->fields['id_heading'];
            }
            else // article iconnu
            {
                // renvoi à la racine
                $headingid = $arrHeadings['tree'][0][0];
                $intErrorCode = 404;
            }
        }
        else
        {
            if ($webedit_mode != 'edit')
            {
                $article = new webedit_article($type);
                if (!$article->open($articleid) || empty($arrHeadings['list'][$headingid]))
                {
                    unset($articleid);
                    // renvoi à la racine
                    $headingid = $arrHeadings['tree'][0][0];
                    $intErrorCode = 404;
                }
            }
        }
    }
}

$nav = $arrHeadings['list'][$headingid]['nav'];
$arrNav = explode('-',$nav);

// CHARGEMENT DU TEMPLATE

// get template name
$template_name = (!empty($arrHeadings['list'][$headingid]['template'])) ? $arrHeadings['list'][$headingid]['template'] : 'default';
if (!file_exists(_WEBEDIT_TEMPLATES_PATH."/$template_name")) $template_name = 'default';

$template_path = _WEBEDIT_TEMPLATES_PATH."/$template_name";

$template_body = new Template($template_path);

// fichier template par défaut
$template_file = 'index.tpl';

if (file_exists("{$template_path}/config.php")) include_once "{$template_path}/config.php";

// Inclusion op modules en environnement frontoffice (permet par exemple de connaître le template frontoffice utilisé)
$_SESSION['ploopi']['frontoffice']['template_path'] = $template_path;
include_once './include/op.php';

webedit_template_assign(&$arrHeadings, &$arrShares, $arrNav, 0, '', 0);

if ($query_string != '') // recherche intégrale
{
    // résultat de la recherche
    $arrRelevance = array();

    // booléen à true si le module DOC existe physiquement
    if ($boolModDocExists = ploopi_init_module('doc', false, false, false)) include_once './modules/doc/class_docfile.php';

    if (file_exists("./templates/frontoffice/{$template_name}/search.tpl")) $template_file = 'search.tpl';

    $template_body->assign_block_vars("switch_search", array());

    // Recherche dans les articles
    $arrRelevance += ploopi_search($query_string, _WEBEDIT_OBJECT_ARTICLE_PUBLIC, '', $_SESSION['ploopi']['moduleid']);

    // Recherche des modules DOC utilisés par les documents liés
    $sql =
        "
        SELECT      id_module_docfile,
                    md5id_docfile
        FROM        ploopi_mod_webedit_docfile
        WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
        ";

    $db->query($sql);

    while ($row = $db->fetchrow()) $arrModDoc[$row['id_module_docfile']][] = $row['md5id_docfile'];

    if (!empty($arrModDoc) && $boolModDocExists) // Il y a des documents indexés
    {
        // pour chaque module DOC, on récupère le résultat de la recherche
        foreach($arrModDoc as $idModDoc => $arrDoc) $arrRelevance += ploopi_search($query_string, _DOC_OBJECT_FILE, $arrDoc, $idModDoc);

        // tri général de la recherche
        uasort($arrRelevance, create_function('$a,$b', 'return $b[\'relevance\'] > $a[\'relevance\'];'));
    }

    $responses = 0;

    // pour chaque réponse du moteur de recherche
    foreach($arrRelevance as $key => $result)
    {
        if (isset($arrModDoc[$result['id_module']])) // s'agit-il d'un document ?
        {
            if ($boolModDocExists)
            {
                $objDocFile = new docfile();

                if ($objDocFile->openmd5($result['id_record']))
                {
                    $link = ploopi_urlrewrite("index-quick.php?ploopi_op=doc_file_download&docfile_md5id={$result['id_record']}", doc_getrewriterules(), $objDocFile->fields['name'], null, true);

                    $template_body->assign_block_vars('switch_search.result',
                        array(
                            'RELEVANCE' => sprintf("%.02f", $result['relevance']),
                            'TITLE' => htmlentities($objDocFile->fields['name']),
                            'TITLE_RAW' => $objDocFile->fields['name'],
                            'AUTHOR' => '',
                            'EXTRACT' => '',
                            'METATITLE' => htmlentities($objDocFile->fields['name']),
                            'METATITLE_RAW' => $objDocFile->fields['name'],
                            'METAKEYWORDS' => '',
                            'METADESCRIPTION' => '',
                            'DATE' => current(ploopi_timestamp2local($objDocFile->fields['timestp_create'])),
                            'SIZE' => sprintf("%.02f", $objDocFile->fields['size']/1024),
                            'LINK' => $link,
                            'SHORT_LINK' => ploopi_strcut($link, 50, 'middle')
                        )
                    );

                    $responses++;
                }
            }
        }
        else // c'est un article
        {

            $objArticle = new webedit_article();
            if ($objArticle->open($result['id_record']) && $objArticle->isenabled())
            {
                if (!$arrHeadings['list'][$objArticle->fields['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$objArticle->fields['id_heading']]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
                {
                    $cleaned_content = strip_tags(html_entity_decode($objArticle->fields['content_cleaned']));

                    // Gestion des url par mode de rendu
                    switch($webedit_mode)
                    {
                        case 'edit';
                        case 'render';
                            $link = "index.php?webedit_mode=render&headingid={$objArticle->fields['id_heading']}&articleid={$result['id_record']}";
                        break;

                        default:
                        case 'display';
                            $arrParents = array();
                            if (isset($arrHeadings['list'][$objArticle->fields['id_heading']])) foreach(split(';', $arrHeadings['list'][$objArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                            $link = ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$result['id_record']}", webedit_getrewriterules(), $objArticle->fields['metatitle'], $arrParents);
                        break;
                    }

                    $template_body->assign_block_vars('switch_search.result',
                        array(
                            'RELEVANCE' => sprintf("%.02f", $result['relevance']),
                            'TITLE' => htmlentities($objArticle->fields['title']),
                            'TITLE_RAW' => $objArticle->fields['title'],
                            'AUTHOR' => htmlentities($objArticle->fields['author']),
                            'AUTHOR_RAW' => $objArticle->fields['author'],
                            'EXTRACT' => ploopi_highlight($cleaned_content, array_merge(array_keys($result['kw']), array_keys($result['stem']))),
                            'METATITLE' => htmlentities($objArticle->fields['metatitle']),
                            'METATITLE_RAW' => $objArticle->fields['metatitle'],
                            'METAKEYWORDS' => htmlentities($objArticle->fields['metakeywords']),
                            'METAKEYWORDS_RAW' => $objArticle->fields['metakeywords'],
                            'METADESCRIPTION' => htmlentities($objArticle->fields['metadescription']),
                            'METADESCRIPTION_RAW' => $objArticle->fields['metadescription'],
                            'DATE' => ($objArticle->fields['timestp']!='') ? current(ploopi_timestamp2local($objArticle->fields['timestp'])) : '',
                            'SIZE' => sprintf("%.02f", strlen($cleaned_content)/1024),
                            'LINK' => $link,
                            'SHORT_LINK' => ploopi_strcut($link, 50, 'middle')
                        )
                    );

                    $responses++;
                }
            }
        }
    }

    if ($responses == 0) // pas de réponse valide !
    {
        $template_body->assign_block_vars('switch_search.switch_notfound',array());
    }

    $title_raw = "Résultat de la recherche pour \" {$query_string} \"";
    $title = htmlentities($title_raw);

    $template_body->assign_vars(
        array(
            'SEARCH_RESPONSES' => $responses,
            'PAGE_TITLE' => $title,
            'PAGE_TITLE_RAW' => $title_raw,
            'PAGE_KEYWORDS' => htmlentities($query_string),
            'PAGE_KEYWORDS_RAW' => $query_string,
            'PAGE_DESCRIPTION' => $title,
            'PAGE_DESCRIPTION_RAW' => $title_raw,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_TITLE_RAW' => $title_raw,
            'PAGE_META_KEYWORDS' => htmlentities($query_string),
            'PAGE_META_KEYWORDS_RAW' => $query_string,
            'PAGE_META_DESCRIPTION' => $title,
            'PAGE_META_DESCRIPTION_RAW' => $title
        )
    );

}
elseif($query_tag != '') // recherche par tag
{
    if (file_exists("./templates/frontoffice/{$template_name}/search.tpl")) $template_file = 'search.tpl';

    $template_body->assign_block_vars("switch_tagsearch", array());

    $sql =  "
            SELECT      a.*

            FROM        ploopi_mod_webedit_tag t,
                        ploopi_mod_webedit_article_tag at,
                        ploopi_mod_webedit_article a

            WHERE       t.tag = '".$db->addslashes($query_tag)."'
            AND         at.id_tag = t.id
            AND         at.id_article = a.id
            AND         (a.timestp_published <= $today OR a.timestp_published = 0)
            AND         (a.timestp_unpublished >= $today OR a.timestp_unpublished = 0)
            ";

    $db->query($sql);

    while ($row = $db->fetchrow())
    {
        if (!$arrHeadings['list'][$row['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
        {
            $size = sprintf("%.02f", strlen(strip_tags(html_entity_decode($row['content_cleaned'])))/1024);

            switch($webedit_mode)
            {
                case 'edit';
                case 'render';
                    $link = "index.php?webedit_mode=render&headingid={$row['id_heading']}&articleid={$row['id']}";
                break;

                default:
                case 'display';
                    $arrParents = array();
                    if (isset($arrHeadings['list'][$row['id_heading']])) foreach(split(';', $arrHeadings['list'][$row['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $link = ploopi_urlrewrite("index.php?headingid={$row['id_heading']}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                break;
            }

            $template_body->assign_block_vars('switch_tagsearch.result',
                array(
                    'TITLE' => htmlentities($row['title']),
                    'AUTHOR' => htmlentities($row['author']),
                    'META_TITLE' => htmlentities($row['metatitle']),
                    'META_KEYWORDS' => htmlentities($row['metakeywords']),
                    'META_DESCRIPTION' => htmlentities($row['metadescription']),
                    'DATE' => ($row['timestp']!='') ? current(ploopi_timestamp2local($row['timestp'])) : '',
                    'SIZE' => $size,
                    'LINK' => $link
                )
            );
        }
    }

    $title_raw = "Liste des articles contenant le tag \" {$query_tag} \"";
    $title = htmlentities($title_raw);

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => $title,
            'PAGE_TITLE_RAW' => $title,
            'PAGE_KEYWORDS' => htmlentities($query_tag),
            'PAGE_KEYWORDS_RAW' => $query_tag,
            'PAGE_DESCRIPTION' => $title,
            'PAGE_DESCRIPTION_RAW' => $title_raw,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_TITLE_RAW' => $title_raw,
            'PAGE_META_KEYWORDS' => htmlentities($query_tag),
            'PAGE_META_KEYWORDS_RAW' => $query_tag,
            'PAGE_META_DESCRIPTION' => $title,
            'PAGE_META_DESCRIPTION_RAW' => $title_raw
        )
    );
}
elseif (!empty($template_moduleid))
{
    $template_body->assign_block_vars("switch_content_module_{$_SESSION['ploopi']['modules'][$template_moduleid]['moduletype']}", array());

    include_once "./modules/{$_SESSION['ploopi']['modules'][$template_moduleid]['moduletype']}/template_content.php";

    $template_body->assign_vars(
        array(
            'MODULE_ID' => $template_moduleid,
            'MODULE_TITLE' => $_SESSION['ploopi']['modules'][$template_moduleid]['label'],
            'MODULE_VERSION' => $_SESSION['ploopi']['modules'][$template_moduleid]['version'],
            'MODULE_AUTHOR' => $_SESSION['ploopi']['modules'][$template_moduleid]['author'],
            'MODULE_DATE' => current(ploopi_timestamp2local($_SESSION['ploopi']['modules'][$template_moduleid]['date']))
        )
    );
}
elseif (!empty($ploopi_op) && $ploopi_op == 'webedit_unsubscribe') // message affiché lors du désabonnement (lien depuis email)
{
    $title_raw = 'Désabonnement';
    $title = htmlentities($title_raw);

    $template_body->assign_block_vars('switch_content_message', array());

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => $title,
            'PAGE_TITLE_RAW' => $title_raw,
            'PAGE_KEYWORDS' => $title,
            'PAGE_KEYWORDS_RAW' => $title_raw,
            'PAGE_DESCRIPTION' => $title,
            'PAGE_DESCRIPTION_RAW' => $title_raw,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_TITLE_RAW' => $title_raw,
            'PAGE_META_KEYWORDS' => $title,
            'PAGE_META_KEYWORDS_RAW' => $title_raw,
            'PAGE_META_DESCRIPTION' => $title,
            'PAGE_META_DESCRIPTION_RAW' => $title_raw,
            'MESSAGE_TITLE' => $title,
            'MESSAGE_CONTENT' => htmlentities('Votre demande de désabonnement a été prise en compte.')
        )
    );
}
else // affichage standard rubrique/page
{
    // Rubrique privée et non autorisée
    if ($arrHeadings['list'][$headingid]['private'] && !isset($arrShares[$arrHeadings['list'][$headingid]['herited_private']]))
    {
        $template_body->assign_block_vars('switch_private', array());
        if (!$_SESSION['ploopi']['connected']) $template_body->assign_block_vars('switch_private.switch_notconnected', array());
        else $template_body->assign_block_vars('switch_private.switch_notallowed', array());
    }
    else
    {

        if($arrHeadings['list'][$headingid]['content_type'] == 'headings' && empty($articleid)) // affichage rubriques
        {
           $template_body->assign_block_vars('switch_content_heading', array());
           webedit_template_assign_headings(&$arrHeadings, &$arrShares, $headingid);
        }

        if($arrHeadings['list'][$headingid]['content_type'] == 'sitemap' && empty($articleid)) // affichage plan de site
        {
           $template_body->assign_block_vars('switch_content_sitemap', array());
           webedit_template_assign_headings(&$arrHeadings, &$arrShares, 0, 'switch_content_sitemap.', 'heading', 0);
        }

        // détermination du type de tri des articles
        switch($arrHeadings['list'][$headingid]['sortmode'])
        {
            case 'bydate':
                $article_orderby = 'timestp DESC, id DESC';
            break;

            case 'bydaterev':
                $article_orderby = 'timestp, id';
            break;

            case 'bypos':
            default:
                $article_orderby = 'position';
            break;
        }

        // get articles
        switch($type)
        {
            case 'draft':
                $select =   "
                            SELECT      *
                            FROM        ploopi_mod_webedit_article_draft
                            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         id_heading = {$headingid}
                            AND         (timestp_published <= $today OR timestp_published = 0)
                            AND         (timestp_unpublished >= $today OR timestp_unpublished = 0)
                            ORDER BY    {$article_orderby}
                            ";
            break;

            default:
                $select =   "
                            SELECT      *
                            FROM        ploopi_mod_webedit_article
                            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         id_heading = {$headingid}
                            AND         (timestp_published <= $today OR timestp_published = 0)
                            AND         (timestp_unpublished >= $today OR timestp_unpublished = 0)
                            ORDER BY    {$article_orderby}
                            ";
            break;
        }

        $db->query($select);
        if ($db->numrows())
        {
            $template_body->assign_block_vars('switch_pages', array());

            // visible articles
            $nbvisart = 0;

            $article_array = array();

            while ($row = $db->fetchrow())
            {
                $article_array[] = $row;
                if ($row['visible']) $nbvisart++;
            }

            $numvisart = 0;

            foreach($article_array as $row)
            {
                // pas d'article sélectionné
                // choix du premier article par défaut (sauf si la rubrique affiche des sous-rubriques
                if (empty($articleid) && $arrHeadings['list'][$headingid]['content_type'] != 'headings' && $arrHeadings['list'][$headingid]['content_type'] != 'sitemap') $articleid = $row['id'];

                if ($row['visible'])
                {
                    $numvisart++;

                    switch($webedit_mode)
                    {
                        case 'edit';
                            $script = "javascript:window.parent.document.location.href='".ploopi_urlencode("admin.php?op=article_modify&headingid={$headingid}&articleid={$row['id']}")."';";
                        break;

                        case 'render';
                            $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&articleid={$row['id']}";
                            //$script = "admin.php?nav={$nav}&articleid={$row['id']}";
                        break;

                        default:
                        case 'display';
                            $arrParents = array();
                            if (isset($arrHeadings['list'][$headingid])) foreach(split(';', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                            $script = ploopi_urlrewrite("index.php?headingid={$headingid}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                        break;
                    }

                    $sel = '';
                    if ($articleid == $row['id']) $sel = 'selected';

                    $ldate_pub = ($row['timestp_published']!='') ? ploopi_timestamp2local($row['timestp_published']) : array('date' => '');
                    $ldate_unpub = ($row['timestp_unpublished']!='') ? ploopi_timestamp2local($row['timestp_unpublished']) : array('date' => '');
                    $ldate_lastupdate = ($row['lastupdate_timestp']!='') ? ploopi_timestamp2local($row['lastupdate_timestp']) : array('date' => '', 'time' => '');
                    $ldate_timestp = ($row['timestp']!='') ? ploopi_timestamp2local($row['timestp']) : array('date' => '');

                    $var_tpl_page =
                        array(
                            'REFERENCE'     => htmlentities($row['reference']),
                            'LABEL'         => htmlentities($row['title']),
                            'LABEL_RAW'     => $row['title'],
                            'CONTENT'       => htmlentities($row['content_cleaned']),
                            'AUTHOR'        => htmlentities($row['author']),
                            'AUTHOR_RAW'    => $row['author'],
                            'VERSION'       => htmlentities($row['version']),
                            'DATE'          => htmlentities($ldate_timestp['date']),
                            'LASTUPDATE_DATE' => htmlentities($ldate_lastupdate['date']),
                            'LASTUPDATE_TIME' => htmlentities($ldate_lastupdate['time']),
                            'DATE_PUB'   => $ldate_pub['date'],
                            'DATE_UNPUB' => $ldate_unpub['date'],
                            'TIMESTP_PUB'   => $ldate_pub['date'],
                            'TIMESTP_UNPUB' => $ldate_unpub['date'],
                            'LINK'          => $script,
                            'POSITION'      => $row['position'],
                            'SEL'           => $sel
                        );

                    $template_body->assign_block_vars('switch_pages.page', $var_tpl_page);

                    if ($arrHeadings['list'][$headingid]['content_type'] == 'headings' && empty($articleid)) // affichage rubriques
                    {
                        $template_body->assign_block_vars('switch_content_heading.page', $var_tpl_page);
                    }

                    if ($numvisart < $nbvisart) $template_body->assign_block_vars('switch_pages.page.sw_separator',array());
                }
            }
        }

        $booIsHomePage = false;

        if (!empty($articleid)) // article à afficher
        {
            $content = '';

            $article = new webedit_article($type);

            if ($articleid == -1 && $webedit_mode == 'edit')
            {
                $article->init_description();
                $template_body->assign_block_vars('article',array(
                    'TITLE' => 'nouvel article',
                    'LINK' => '',
                    'SEL' => 'sel'
                ));
            }
            else
            {
                if ($article->open($articleid))
                {
                    // Article hors des dates de publication
                    if (($article->fields['timestp_published'] != 0 && $article->fields['timestp_published'] > $today) || ($article->fields['timestp_unpublished'] != 0 && $article->fields['timestp_unpublished'] < $today)) $intErrorCode = 404;

                    $booIsHomePage =
                        (
                            !empty($headingid) && !empty($articleid)
                            &&
                                (
                                    (   $article->fields['position'] == 1
                                        &&  $arrHeadings['list'][$headingid]['depth'] == 1
                                        &&  $arrHeadings['list'][$headingid]['position'] == 1
                                        &&  empty($arrHeadings['list'][$headingid]['linkedpage'])
                                    )
                                    || $arrHeadings['list'][$arrHeadings['tree'][0][0]]['linkedpage'] == $articleid
                                )
                        );
                }
                else $intErrorCode = 404; // article non trouvé
            }

            if ($webedit_mode == 'edit')
            {
                if (!$readonly)
                {
                    if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] = 'beginner';

                    ob_start();

                    include_once './FCKeditor/fckeditor.php' ;

                    $oFCKeditor = new FCKeditor('fck_webedit_article_content') ;

                    $oFCKeditor->BasePath = './FCKeditor/';

                    // default value
                    $oFCKeditor->Value= $article->fields['content'];

                    // width & height
                    $oFCKeditor->Width='100%';
                    $oFCKeditor->Height='500';

                    $oFCKeditor->Config['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/modules/webedit/fckeditor/fckconfig.js';
                    $oFCKeditor->Config['ToolbarLocation'] = 'Out:parent(xToolbar)';
                    $oFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH.'/';

                    if ($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] == 'beginner') $oFCKeditor->ToolbarSet = 'Beginner' ;
                    else $oFCKeditor->ToolbarSet = 'Default' ;

                    if (file_exists("{$template_path}/fckeditor/fck_editorarea.css")) $oFCKeditor->Config['EditorAreaCSS'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fck_editorarea.css';

                    if (file_exists("{$template_path}/fckeditor/fcktemplates.xml")) $oFCKeditor->Config['TemplatesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fcktemplates.xml';

                    if (file_exists("{$template_path}/fckeditor/fckstyles.xml")) $oFCKeditor->Config['StylesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fckstyles.xml';

                    // render
                    $oFCKeditor->Create('FCKeditor_1') ;

                    $editor = ob_get_contents();
                    ob_end_clean();
                }
            }
            else
            {
                if (!$intErrorCode)
                {
                    // Test si l'article a déjà été visité pendant la session
                    if (!isset($_SESSION['webedit']['counter'][$article->fields['id']]))
                    {
                        // Enregistrement d'un hit pour cet article
                        include_once './modules/webedit/class_counter.php';
                        $counter =
                            array(
                                'year' => date('Y'),
                                'month' => date('n'),
                                'day' => date('j'),
                                'week' => date('o').date('W')
                            );

                        $objCounter = new webedit_counter();
                        if (!$objCounter->open($counter['year'], $counter['month'], $counter['day'], $article->fields['id']))
                        {
                            $objCounter->fields['year'] = $counter['year'];
                            $objCounter->fields['month'] = $counter['month'];
                            $objCounter->fields['day'] = $counter['day'];
                            $objCounter->fields['week'] = $counter['week'];
                            $objCounter->fields['id_article'] = $article->fields['id'];
                            $objCounter->fields['id_module'] = $article->fields['id_module'];
                        }
                        $objCounter->hit();

                        $_SESSION['webedit']['counter'][$article->fields['id']] = '';
                    }
                }
            }

            if (!$intErrorCode || $webedit_mode == 'edit')
            {
                $template_body->assign_block_vars('switch_content_page', array());

                /**
                 * Traitement des objets WCE/WebEdit
                 * avec détection de chaîne et remplacement par le contenu d'une fonction
                 */

                if (!empty($editor)) $content = $editor;
                else $content = preg_replace_callback('/\[\[(.*)\]\]/i','webedit_getobjectcontent', $webedit_mode == 'edit' ? $article->fields['content_cleaned'] : webedit_replace_links($article->fields['content_cleaned'], $webedit_mode, $arrHeadings) );

                $article_timestp = ($article->fields['timestp']!='') ? ploopi_timestamp2local($article->fields['timestp']) : array('date' => '');
                $article_lastupdate = ($article->fields['lastupdate_timestp']!='') ? ploopi_timestamp2local($article->fields['lastupdate_timestp']) : array('date' => '', 'time' => '');

                $user = new user();
                if ($user->open($article->fields['lastupdate_id_user']))
                {
                    $user_lastname = $user->fields['lastname'];
                    $user_firstname = $user->fields['firstname'];
                    $user_login = $user->fields['login'];
                }
                else $user_lastname = $user_firstname = $user_login = '';

                list($keywords) = ploopi_getwords("{$article->fields['metatitle']} {$article->fields['metakeywords']} {$article->fields['author']}");

                $kwds_raw = implode(', ', array_keys($keywords));
                $kwds = htmlentities($kwds_raw);

                $desc_raw = $article->fields['metadescription'];
                $desc = htmlentities($article->fields['metadescription']);

                $template_body->assign_vars(
                    array(
                        'PAGE_REFERENCE' => htmlentities($article->fields['reference']),
                        'PAGE_REFERENCE_RAW' => $article->fields['reference'],
                        'PAGE_TITLE' => htmlentities($article->fields['title']),
                        'PAGE_TITLE_RAW' => $article->fields['title'],
                        'PAGE_TITLE_ESCAPED' => addslashes($article->fields['title']),
                        'PAGE_KEYWORDS' => $kwds,
                        'PAGE_KEYWORDS_RAW' => $kwds_raw,
                        'PAGE_DESCRIPTION' => $desc,
                        'PAGE_DESCRIPTION_RAW' => $desc_raw,
                        'PAGE_DESCRIPTION_ESCAPED' => addslashes($article->fields['metadescription']),
                        'PAGE_META_TITLE' => htmlentities($article->fields['metatitle']),
                        'PAGE_META_TITLE_RAW' => $article->fields['metatitle'],
                        'PAGE_META_KEYWORDS' => $kwds,
                        'PAGE_META_KEYWORDS_RAW' => $kwds_raw,
                        'PAGE_META_DESCRIPTION' => $desc,
                        'PAGE_META_DESCRIPTION_RAW' => $desc_raw,
                        'PAGE_AUTHOR' => htmlentities($article->fields['author']),
                        'PAGE_AUTHOR_RAW' => $article->fields['author'],
                        'PAGE_VERSION' => htmlentities($article->fields['version']),
                        'PAGE_VERSION_RAW' => $article->fields['version'],
                        'PAGE_DATE' => htmlentities($article_timestp['date']),
                        'PAGE_LASTUPDATE_DATE' => htmlentities($article_lastupdate['date']),
                        'PAGE_LASTUPDATE_TIME' => htmlentities($article_lastupdate['time']),
                        'PAGE_LASTUPDATE_USER_LASTNAME' => htmlentities($user_lastname),
                        'PAGE_LASTUPDATE_USER_FIRSTNAME' => htmlentities($user_firstname),
                        'PAGE_LASTUPDATE_USER_LOGIN' => htmlentities($user_login),
                        'PAGE_CONTENT' => $content,
                        'PAGE_HEADCONTENT' => $article->fields['headcontent']
                    )
                );

                $tags = $article->gettags();
                if (!empty($tags))
                {
                    $template_body->assign_block_vars('switch_content_page.switch_tags', array());

                    foreach($tags as $tag)
                    {
                        switch($webedit_mode)
                        {
                            case 'edit';
                            case 'render';
                                $link =  "index.php?webedit_mode=render&query_tag={$tag['tag']}";
                            break;

                            default:
                            case 'display';
                                $link =  ploopi_urlrewrite("index.php?query_tag={$tag['tag']}", webedit_getrewriterules());
                            break;
                        }

                        $template_body->assign_block_vars('switch_content_page.switch_tags.tag',
                            array(
                                'TAG' => $tag['tag'],
                                'LINK' => $link
                            )
                        );
                    }
                }
            } // if (!$intErrorCode)
        }
        else
        {
            // pas d'article par défaut, on teste si on est sur la rubrique d'accueil
            $booIsHomePage = (!empty($headingid) && $arrHeadings['tree'][0][0] == $headingid);
        }

        // Doit on afficher le flux de la rubrique ?
        if (!$booIsHomePage && isset($arrHeadings['list'][$headingid]) && $arrHeadings['list'][$headingid]['feed_enabled'])
        {
            $template_body->assign_block_vars(
                'switch_atomfeed_heading',
                array(
                    'URL' => ploopi_urlrewrite("index.php?ploopi_op=webedit_backend&format=atom&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']), //ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom&headingid={$headingid}", null, null, null, null, false),
                    'TITLE' => htmlentities($arrHeadings['list'][$headingid]['label']),
                )
            );

            $template_body->assign_block_vars(
                'switch_rssfeed_heading',
                array(
                    'URL' => ploopi_urlrewrite("index.php?ploopi_op=webedit_backend&format=rss&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']),//ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss&headingid={$headingid}", null, null, null, null, false),
                    'TITLE' => htmlentities($arrHeadings['list'][$headingid]['label']),
                )
            );
        }

        // Doit on autoriser les abonnements ?
        if (($booIsHomePage && isset($arrHeadings['subscription_enabled']) && $arrHeadings['subscription_enabled']) || (isset($arrHeadings['list'][$headingid]) && $arrHeadings['list'][$headingid]['subscription_enabled']))
        {
            // Gestion des url par mode de rendu
            switch($webedit_mode)
            {
                case 'edit';
                case 'render';
                    $link = ploopi_urlencode("index.php?webedit_mode=render&ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);
                break;

                default:
                case 'display';
                    $link = ploopi_urlencode("index.php?ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);
                break;
            }

            $template_body->assign_block_vars(
                'switch_subscription',
                array(
                    'ACTION' => $link,
                    'HEADINGID' => $booIsHomePage ? 0 : $headingid,
                    'ROOTID' => 0
                )
            );

            if (!empty($_GET['subscription_return']) && isset($webedit_subscription_messages[$_GET['subscription_return']])) // réponse suite à une demande d'abonnement
            {
                $template_body->assign_block_vars(
                    'switch_subscription.switch_response',
                    array(
                        'CONTENT' => $webedit_subscription_messages[$_GET['subscription_return']]
                    )
                );

            }
        }

        // template de la home page
        if ($booIsHomePage && file_exists("./templates/frontoffice/{$template_name}/home.tpl")) $template_file = 'home.tpl';
    }
}

if ($intErrorCode && $webedit_mode != 'edit')
{
    ploopi_h404();
    $template_body->assign_block_vars('switch_content_error', array());

    $strError = 'Erreur '.$intErrorCode;

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => htmlentities($strError),
            'PAGE_TITLE_RAW' => $strError,
            'PAGE_TITLE_ESCAPED' => addslashes($strError),
            'PAGE_KEYWORDS' => htmlentities($strError),
            'PAGE_KEYWORDS_RAW' => $strError,
            'PAGE_DESCRIPTION' => htmlentities($strError),
            'PAGE_DESCRIPTION_RAW' => $strError,
            'PAGE_DESCRIPTION_ESCAPED' => addslashes($strError),
            'PAGE_META_TITLE' => htmlentities($strError),
            'PAGE_META_TITLE_RAW' => $strError,
            'PAGE_META_KEYWORDS' => $strError,
            'PAGE_META_KEYWORDS_RAW' => $strError,
            'PAGE_META_DESCRIPTION' => htmlentities($strError),
            'PAGE_META_DESCRIPTION_RAW' => $strError,
            'PAGE_ERROR_CODE' => $intErrorCode,
        )
    );

}

// load a specific template file in edition mode (if exists)
if ($webedit_mode == 'edit' && file_exists("./templates/frontoffice/{$template_name}/fck_{$template_file}")) $template_file = "fck_{$template_file}";

// on vérifie l'existence du fichier TPL, sinon => die
if (!file_exists("./templates/frontoffice/{$template_name}/{$template_file}") || ! is_readable("./templates/frontoffice/{$template_name}/{$template_file}")) {

    ploopi_die(
        str_replace(
            array('<FILE>', '<TEMPLATE>'),
            array($template_file, "./templates/frontoffice/{$template_name}"),
            _PLOOPI_ERROR_TEMPLATE_FILE
        )
    );

}

$template_body->set_filenames(array('body' => $template_file));

if (!empty($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
{
    foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $key => $template_moduleid)
    {
        if ($_SESSION['ploopi']['modules'][$template_moduleid]['active'])
        {
            $template_body->assign_block_vars('modules' , array(
                'ID' => $template_moduleid,
                'LABEL' => $_SESSION['ploopi']['modules'][$template_moduleid]['label'],
                'TYPE' => $_SESSION['ploopi']['modules'][$template_moduleid]['moduletype'],
                'TYPEID' => $_SESSION['ploopi']['modules'][$template_moduleid]['id_module_type']
                )
            );

            if (file_exists("./modules/{$_SESSION['ploopi']['modules'][$template_moduleid]['moduletype']}/template.php")) include_once "./modules/{$_SESSION['ploopi']['modules'][$template_moduleid]['moduletype']}/template.php";
        }

    }
}

// Mode connecté, on propose les infos de l'utilisateur connecté
if ($_SESSION['ploopi']['connected'])
{
    $template_body->assign_block_vars('switch_user_logged_in', array());
    $template_body->assign_vars(
        array(
            'USER_LOGIN'            => $_SESSION['ploopi']['login'],
            'USER_FIRSTNAME'        => $_SESSION['ploopi']['user']['firstname'],
            'USER_LASTNAME'         => $_SESSION['ploopi']['user']['lastname'],
            'USER_EMAIL'            => $_SESSION['ploopi']['user']['email'],
            'USER_SHOWPROFILE'      => ploopi_urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showprofile'),
            'USER_SHOWTICKETS'      => ploopi_urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showtickets'),
            'USER_SHOWFAVORITES'    => ploopi_urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showfavorites'),
            'USER_ADMINISTRATION'   => ploopi_urlencode('admin.php'.''),
            'USER_DECONNECT'        => ploopi_urlencode('index.php?ploopi_logout')
        )
    );
}
else
{
    $template_body->assign_block_vars('switch_user_logged_out', array());
    if (!empty($_GET['ploopi_errorcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array('MESSAGE' => isset($ploopi_errormsg[$_GET['ploopi_errorcode']]) ? $ploopi_errormsg[$_GET['ploopi_errorcode']] : ''));
    }
}

// GET MODULE ADDITIONAL JS
ob_start();
// buffer flushing
include './include/javascript.php';

if ($webedit_mode != 'display')
{
    ?>
    function webedit_autofit_iframe()
    {
        try
        {
            if (document.getElementById || !window.opera && !document.mimeType && document.all && document.getElementById)
            {
                height = this.document.body.scrollHeight + 50;
                if (height < 400) height = 400;
                parent.document.getElementById('webedit_frame_editor').style.height = height + 'px';
            }
        }
        catch (e)
        {
            height = this.document.body.offsetHeight;
            if (height < 400) height = 400;
            parent.document.getElementById('webedit_frame_editor').style.height = height + 'px';
        }
    }

    window.onload = function() { webedit_autofit_iframe();};
    <?php
}

$additional_javascript = ob_get_contents();
@ob_end_clean();

$lastupdate = ($lastupdate = webedit_getlastupdate()) ? ploopi_timestamp2local($lastupdate) : array('date' => '', 'time' => '');

// template assignments

list($keywords) = ploopi_getwords("{$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']} {$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_keywords']} {$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']}");

// PLOOPI JS
$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './lib/protoaculous/protoaculous.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$title_raw = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'];
$title = htmlentities($title_raw);

// Doit on afficher le flux du site ?
if (isset($arrHeadings['feed_enabled']) && $arrHeadings['feed_enabled'])
{
    $template_body->assign_block_vars(
        'switch_atomfeed_site',
        array(
            'URL' => ploopi_urlrewrite("index.php?ploopi_op=webedit_backend&format=atom", webedit_getrewriterules(), $title_raw), // ploopi_urlrewrite("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom", webedit_getrewriterules(), 'titre'),  //ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom", null, null, null, null, false),
            'TITLE' => $title,
        )
    );
    $template_body->assign_block_vars(
        'switch_rssfeed_site',
        array(
            'URL' => ploopi_urlrewrite("index.php?ploopi_op=webedit_backend&format=rss", webedit_getrewriterules(), $title_raw), //ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss", null, null, null, null, false),
            'TITLE' => $title,
        )
    );
}

// Validation du basepath (doit se terminer par /)
$strBasePath = _PLOOPI_BASEPATH;
if (substr($strBasePath, -1) != '/') $strBasePath .= '/';

$template_body->assign_vars(
    array(
        'TEMPLATE_PATH'                 => $template_path,
        'TEMPLATE_NAME'                 => $template_name,
        'ADDITIONAL_JAVASCRIPT'         => $additional_javascript,
        'SITE_TITLE'                    => $title,
        'SITE_TITLE_RAW'                => $title_raw,
        'WORKSPACE_TITLE'               => $title,
        'WORKSPACE_TITLE_RAW'           => $title_raw,
        'WORKSPACE_META_DESCRIPTION'    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description']),
        'WORKSPACE_META_KEYWORDS'       => htmlentities(implode(', ', array_keys($keywords))),
        'WORKSPACE_META_AUTHOR'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']),
        'WORKSPACE_META_COPYRIGHT'      => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright']),
        'WORKSPACE_META_ROBOTS'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_robots']),
        'PAGE_QUERYSTRING'              => $query_string,
        'PAGE_QUERYTAG'                 => $query_tag,
        'SITE_HOME'                     => in_array($webedit_mode, array('render', 'edit')) ? 'index.php?webedit_mode=render' : 'index.php',
        'HOST'                          => $_SERVER['HTTP_HOST'],
        'DATE_DAY'                      => date('d'),
        'DATE_MONTH'                    => date('m'),
        'DATE_YEAR'                     => date('Y'),
        'DATE_DAYTEXT'                  => $ploopi_days[date('w')],
        'DATE_MONTHTEXT'                => $ploopi_months[date('n')],
        'LASTUPDATE_DATE'               => $lastupdate['date'],
        'LASTUPDATE_TIME'               => $lastupdate['time'],
        'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
        'SITE_ANONYMOUSUSERS'           => $_SESSION['ploopi']['anonymoususers'],
        'SITE_BASEPATH'                 => $strBasePath,
        'PLOOPI_VERSION'                => _PLOOPI_VERSION,
        'PLOOPI_REVISION'               => _PLOOPI_REVISION
    )
);

/**
 * Génération du nuage de tags en fonction des articles publiés
 */

$sql =  "
        SELECT      t.*, a.id_heading
        FROM        ploopi_mod_webedit_tag t

        INNER JOIN  ploopi_mod_webedit_article_tag at
        ON          at.id_tag = t.id

        INNER JOIN  ploopi_mod_webedit_article a
        ON          at.id_article = a.id

        WHERE       t.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         (a.timestp_published <= $today OR a.timestp_published = 0)
        AND         (a.timestp_unpublished >= $today OR a.timestp_unpublished = 0)
        ";


$db->query($sql);

$arrTags = array();
while ($row = $db->fetchrow())
{
    if (!$arrHeadings['list'][$row['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
    {
        $strTag = strtolower(ploopi_convertaccents($row['tag']));
        if (!isset($arrTags[$strTag])) $arrTags[$strTag] = 0;
        $arrTags[$strTag]++;
    }
}

// Tri en fonction du nombre d'apparition du tag
arsort($arrTags);
// Valeur max d'apparition
$intMax = current($arrTags);

// Calcul de la taille d'affichage de chaque tag
$intMinSize = 50;
foreach($arrTags as $strTag => &$row)
{
    $row = array(
        'nb' => $row,
        'size' => round(100 * $row / $intMax)
    );

    if ($row['size'] < $intMinSize) $row['size'] = $intMinSize;
}
// Tri des tags par ordre alphabétique
ksort($arrTags);

foreach ($arrTags as $strTag => $arrTag)
{
    // Gestion des url par mode de rendu
    switch($webedit_mode)
    {
        case 'edit';
        case 'render';
            $link = "index.php?webedit_mode=render&query_tag={$strTag}";
        break;

        default:
        case 'display';
            $link = ploopi_urlrewrite("index.php?query_tag={$strTag}", webedit_getrewriterules());
        break;
    }

    $template_body->assign_block_vars('tagcloud' , array(
        'TAG' => $strTag,
        'SIZE' => $arrTag['size'],
        'OCCURENCE' => $arrTag['nb'],
        'LINK' => $link,
        'SELECTED' => ($strTag == $query_tag) ? 'selected' : ''
        )
    );
}

$template_body->pparse('body');
?>
