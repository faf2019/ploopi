<?php
/*
    Copyright (c) 2007-2018 Ovensia
    Copyright (c) 2009-2010 HeXad
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
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
global $template_name;
global $webedit_mode;
global $article;
global $articleid;
global $headingid;

global $ploopi_additional_head;
global $ploopi_additional_javascript;
global $webedit_subscription_messages;

$ploopi_additional_head = '';
$ploopi_additional_javascript = '';

// Date du jour (utile pour vérifier les dates de publication)
$today = ploopi\date::createtimestamp();

$type = (empty($_GET['type'])) ? '' : $_GET['type'];
$webedit_mode = (empty($_GET['webedit_mode'])) ? 'display' : $_GET['webedit_mode'];
$readonly = (empty($_GET['readonly'])) ? 0 : $_GET['readonly'];

// id article passé en param ?
$articleid = (!empty($_REQUEST['articleid']) && is_numeric($_REQUEST['articleid'])) ? $_REQUEST['articleid'] : '';

// id rubrique passé en param ?
$headingid = (!empty($_REQUEST['headingid']) && is_numeric($_REQUEST['headingid'])) ? $_REQUEST['headingid'] : '';

// On verif que ce

// code d'erreur renvoyé (principalement 404)
$intErrorCode = 0;

// vérification des paramètres
if ($webedit_mode == 'edit' && !(ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || webedit_isEditor($headingid))) $webedit_mode = 'display';

if ($webedit_mode == 'render')
{
    $readonly = 1;
    $type = '';
}

// requête de recherche ?
$query_string = (empty($_REQUEST['query_string'])) ? '' : $_REQUEST['query_string'];
$query_content_type = (empty($_REQUEST['query_content_type'])) ? '' : $_REQUEST['query_content_type'];
$query_heading_id = (empty($_REQUEST['query_heading_id'])) ? '' : $_REQUEST['query_heading_id'];
$query_date_b = (empty($_REQUEST['query_date_b'])) ? '' : $_REQUEST['query_date_b'];
$query_date_e = (empty($_REQUEST['query_date_e'])) ? '' : $_REQUEST['query_date_e'];
$query_mime_type = (empty($_REQUEST['query_mime_type'])) ? '' : strtolower($_REQUEST['query_mime_type']);
$advanced_search = (empty($_REQUEST['advanced_search'])) ? false : true;

// requête sur un tag ?
$query_tag = (empty($_REQUEST['query_tag'])) ? '' : $_REQUEST['query_tag'];

// module frontoffice ?
$intTemplateModuleId = (empty($_REQUEST['template_moduleid']) || !is_numeric($_REQUEST['template_moduleid']) || !isset($_SESSION['ploopi']['modules'][$_REQUEST['template_moduleid']]) || !file_exists("./modules/{$_SESSION['ploopi']['modules'][$_REQUEST['template_moduleid']]['moduletype']}/template_content.php")) ? '' : $_REQUEST['template_moduleid'];

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
elseif ($advanced_search) // Recherche avancée
{
    $headingid = $arrHeadings['tree'][0][0];
}
elseif (!empty($intTemplateModuleId)) // Module frontoffice
{
    if (empty($headingid)) $headingid = $arrHeadings['tree'][0][0];
}
else // affichage standard rubrique/page ou blog
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

        //Redirection de rubrique vers rubrique ! (Valable pour la racine !)
        if($arrHeadings['list'][$headingid]['content_type'] == 'article_redirect' && substr($arrHeadings['list'][$headingid]['linkedpage'],0,1) == 'h') // redirection ! On verif si redirect sur une autre rubrique.
        {
            $headingid = substr($arrHeadings['list'][$headingid]['linkedpage'],1);

            do {
                $objHeading = new webedit_heading();

                if($objHeading->open($headingid) && substr($objHeading->fields['linkedpage'],0,1) == 'h')
                    $headingid = substr($arrHeadings['list'][$headingid]['linkedpage'],1);
                else
                    break;

            } while(!empty($objHeading->fields['linkedpage']) && substr($objHeading->fields['linkedpage'],0,1) == 'h');
        }

        if (!$arrHeadings['list'][$headingid]['private'] || isset($arrShares[$arrHeadings['list'][$headingid]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$headingid]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
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
                    if (!empty($arrHeadings['list'][$headingid]['url'])) ploopi\output::redirect($arrHeadings['list'][$headingid]['url'], false, false);
                break;

                case 'article_first':
                    // Cas standard, traité plus bas
                case 'headings':
                    // Traité à l'affichage
                case 'blog':
                    // Afficher tous les articles a la suite les uns des autres dans l'ordre inverse de date de parution.
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

// PARAMETRES POUR LA PARTIE BLOG
if(isset($arrHeadings['list'][$headingid]['content_type']) && $arrHeadings['list'][$headingid]['content_type'] == 'blog')
{
    // Numero de page pour les blog
    $intNumPage = (!empty($_REQUEST['numpage']) && is_numeric($_REQUEST['numpage'])) ? $_REQUEST['numpage'] : '1';;
    // Calcul des articles à afficher
    $intNumMinBlog = ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nb_art_blog']*($intNumPage-1))+1;
    $intNumMaxBlog = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nb_art_blog']*$intNumPage;

    if($headingid)
    {
        if(!isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['blog'][$headingid]))
            $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['blog'][$headingid] = array();

        // Pour facilier la lecture pointeur vers la session de 3km de long
        $arrSessionBlog = &$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['blog'][$headingid];

        // mois / année passé en parametre pour calendrier
        if(!isset($arrSessionBlog['year'])) $arrSessionBlog['year'] = date('Y');
        if(!isset($arrSessionBlog['month'])) $arrSessionBlog['month'] = date('m');

        if(isset($_GET['yearmonth']))
        {
            $arrSessionBlog['year'] = substr($_GET['yearmonth'],0,4);
            $arrSessionBlog['month'] = substr($_GET['yearmonth'],4,2);
        }
        if(isset($_GET['year']))
        {
            $arrSessionBlog['year'] = substr($_GET['year'],0,4);
        }
    }
}
// FIN DE PARAMETRES POUR LA PARTIE BLOG

$nav = $arrHeadings['list'][$headingid]['nav'];
$arrNav = explode('-',$nav);

// CHARGEMENT DU TEMPLATE

// get template name
$template_name = (!empty($arrHeadings['list'][$headingid]['template'])) ? $arrHeadings['list'][$headingid]['template'] : 'default';
if (!file_exists(_WEBEDIT_TEMPLATES_PATH."/$template_name")) $template_name = 'default';

$template_path = _WEBEDIT_TEMPLATES_PATH."/$template_name";

$template_body = self::$template_body = new Template($template_path);

// fichier template par défaut
$template_file = 'index.tpl';

// Inclusion op modules en environnement frontoffice (permet par exemple de connaître le template frontoffice utilisé)
$_SESSION['ploopi']['frontoffice']['template_path'] = $template_path;

if (!empty($_SESSION['ploopi']['frontoffice']['template_path']) && file_exists("{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php"))
{
    include_once "{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php";
    $skin = ploopi\skin::get();
}


include_once './include/op.php';

webedit_template_assign($arrHeadings, $arrShares, $arrNav, 0, '', 0);

if (!empty($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
{
    foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $intModuleId)
    {
        if ($_SESSION['ploopi']['modules'][$intModuleId]['active'])
        {
            $template_body->assign_block_vars('modules' , array(
                'ID' => $intModuleId,
                'LABEL' => $_SESSION['ploopi']['modules'][$intModuleId]['label'],
                'TYPE' => $_SESSION['ploopi']['modules'][$intModuleId]['moduletype'],
                'TYPEID' => $_SESSION['ploopi']['modules'][$intModuleId]['id_module_type']
                )
            );

            if (file_exists("./modules/{$_SESSION['ploopi']['modules'][$intModuleId]['moduletype']}/template.php"))
            {
                $template_moduleid = $intModuleId;
                include "./modules/{$_SESSION['ploopi']['modules'][$intModuleId]['moduletype']}/template.php";
                unset($template_moduleid);
            }
        }
    }
}

if ($query_string != '' || $advanced_search) // recherche intégrale
{
    if ($advanced_search) // recherche intégrale avancée
    {
        $template_body->assign_block_vars('switch_advanced_search', array());

        /**
         * Génération de la liste des rubriques pour la recherche étendue
         */
        webedit_headinglist2template($arrHeadings, $arrShares, $template_body, $query_heading_id);

    }

    // résultat de la recherche
    $arrRelevance = array();

    if (file_exists("./templates/frontoffice/{$template_name}/search.tpl")) $template_file = 'search.tpl';

    if ($query_string != '' || $query_date_b != '' || $query_date_e != '' || $query_content_type != '' || $query_mime_type != '' || $query_heading_id != '')
    {
        // Conversion des dates de recherche en timestamp
        $ts_date_b = empty($query_date_b) ? 0 : ploopi\date::local2timestamp($query_date_b);
        $ts_date_e = empty($query_date_e) ? 0 : ploopi\date::local2timestamp($query_date_e, '23:59:59');

        $template_body->assign_block_vars('switch_search', array());

        // Recherche d'articles ?
        if ($query_content_type != 'doc' && $query_mime_type == '')
        {
            if ($query_heading_id != '')
            {
                // Liste des articles de la rubrique sélectionnée
                $rs = ploopi\db::get()->query("
                    SELECT  a.id
                    FROM    ploopi_mod_webedit_heading h1,
                            ploopi_mod_webedit_heading h2,
                            ploopi_mod_webedit_article a
                    WHERE   h2.id = {$query_heading_id}
                    AND     (h1.parents LIKE CONCAT(h2.parents, ';', h2.id, ';', '%') OR h1.id = h2.id)
                    AND     a.id_heading = h1.id
                ");

                $arrQueryHeadingArticles = ploopi\db::get()->getarray($rs, true);

                if (!empty($arrQueryHeadingArticles))
                {
                    foreach(ploopi\search_index::search($query_string, _WEBEDIT_OBJECT_ARTICLE_PUBLIC, $arrQueryHeadingArticles, $_SESSION['ploopi']['moduleid']) as $row)
                    {
                        $arrRelevance['webedit_'.$row['id_record']] = $row;
                    }
                }
            }
            else
            {
                foreach(ploopi\search_index::search($query_string, _WEBEDIT_OBJECT_ARTICLE_PUBLIC, null, $_SESSION['ploopi']['moduleid']) as $row)
                {
                    $arrRelevance['webedit_'.$row['id_record']] = $row;
                }
            }

            /**
             * Recherche dans les contenus d'objets intégrés dans les pages
             */

            // Recherche des objets utilisés
            $sql =
                "
                SELECT      ao.*,
                            a.id_heading

                FROM        ploopi_mod_webedit_article_object ao,
                            ploopi_mod_webedit_article a

                WHERE       a.id_module = {$_SESSION['ploopi']['moduleid']}
                AND         ao.id_article = a.id
                ";
            ploopi\db::get()->query($sql);

            $arrObject = array();

            while ($row = ploopi\db::get()->fetchrow())
            {
                if ($row['id_heading'] == 0 || !$arrHeadings['list'][$row['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$row['id_heading']]['herited_private']]) || $webedit_mode == 'edit') // Rubrique non privée ou accessible par l'utilisateur
                {
                    // Recherche par rubrique, on contrôle l'id de la rubrique (et parents) du document avec la rubrique sélectionnée
                    if ($query_heading_id != '')
                    {
                        $booObjectOk = false;
                        $intId = $row['id_heading'];
                        while ($intId != 0 && $booObjectOk == false) {
                            if ($intId == $query_heading_id) $booObjectOk = true;
                            $intId = $arrHeadings['list'][$intId]['id_heading'];
                        }
                    }
                    else $booObjectOk = true;

                    unset($row['id_heading']);

                    if ($booObjectOk) $arrObject[md5(serialize($row))] = $row;
                }
            }

            // pour chaque objet, on récupère le résultat de la recherche
            foreach($arrObject as $rowObject)
            {
                if (isset($_SESSION['ploopi']['modules'][$rowObject['id_module']]) && $_SESSION['ploopi']['modules'][$rowObject['id_module']]['active']) // Module existe et actif ?
                {
                    // Charge le fichier global.php du module
                    ploopi\module::init($_SESSION['ploopi']['modules'][$rowObject['id_module']]['moduletype'], false, false, false);
                    // Vérifie l'existence d'une fonction de recherche dans les objets
                    $cbSearchFunc = $_SESSION['ploopi']['modules'][$rowObject['id_module']]['moduletype'].'_object_search';

                    if (function_exists($cbSearchFunc))
                    {
                        foreach($cbSearchFunc($query_string, $rowObject) as $row)
                        {
                            $row['id_article'] = $rowObject['id_article'];
                            $arrRelevance[$_SESSION['ploopi']['modules'][$rowObject['id_module']]['moduletype'].'_'.$row['id_record']] = $row;
                        }
                    }
                }
            }
            // tri général de la recherche
            // uasort($arrRelevance, create_function('$a,$b', 'return $b[\'relevance\'] > $a[\'relevance\'];'));
        }

        // Recherche de documents ?
        if ($query_content_type != 'art')
        {
            // booléen à true si le module DOC existe physiquement
            if ($boolModDocExists = ploopi\module::init('doc', false, false, false)) include_once './modules/doc/class_docfile.php';

            // Recherche des modules DOC utilisés par les documents liés
            $sql =
                "
                SELECT      df.id_module_docfile,
                            df.md5id_docfile,
                            a.id_heading,
                            d.*

                FROM        ploopi_mod_webedit_docfile df,
                            ploopi_mod_webedit_article a,
                            ploopi_mod_doc_file d

                WHERE       df.id_module = {$_SESSION['ploopi']['moduleid']}
                AND         df.id_article = a.id
                AND         df.id_docfile = d.id
                ";

            // Recherche pas date
            if (!empty($ts_date_b)) $sql .= " AND d.timestp_create >= '{$ts_date_b}' ";
            if (!empty($ts_date_e)) $sql .= " AND d.timestp_create <= '{$ts_date_e}' ";

            ploopi\db::get()->query($sql);

            while ($row = ploopi\db::get()->fetchrow())
            {
                if ($row['id_heading'] == 0 || !$arrHeadings['list'][$row['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$row['id_heading']]['herited_private']]) || $webedit_mode == 'edit') // Rubrique non privée ou accessible par l'utilisateur
                {
                    // Recherche par rubrique, on contrôle l'id de la rubrique (et parents) du document avec la rubrique sélectionnée
                    if ($query_heading_id != '')
                    {
                        $booDocOk = false;
                        $intId = $row['id_heading'];
                        while ($intId != 0 && $booDocOk == false) {
                            if ($intId == $query_heading_id) $booDocOk = true;
                            $intId = $arrHeadings['list'][$intId]['id_heading'];
                        }
                    }
                    else $booDocOk = true;

                    if ($booDocOk) $arrModDoc[$row['id_module_docfile']][] = $row['md5id_docfile'];
                }
            }

            if (!empty($arrModDoc) && $boolModDocExists) // Il y a des documents indexés
            {
                // pour chaque module DOC, on récupère le résultat de la recherche
                foreach($arrModDoc as $idModDoc => $arrDoc)
                {
                    // Filtrage par extension
                    foreach (ploopi\search_index::search($query_string, _DOC_OBJECT_FILE, $arrDoc, $idModDoc) as $row)
                    {
                        if ($query_mime_type != '')
                        {
                            if (strripos(strtolower($row['label']), strtolower($query_mime_type)) === strlen($row['label']) - strlen($query_mime_type))
                            {
                                $arrRelevance['doc_'.$row['id_record']] = $row;
                            }
                        }
                        else $arrRelevance['doc_'.$row['id_record']] = $row;
                    }
                }
            }
        }

        // tri général de la recherche
        uasort($arrRelevance, create_function('$a,$b', 'return $b[\'relevance\'] > $a[\'relevance\'];'));

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
                        $link = ploopi\str::urlrewrite("index-quick.php?ploopi_op=doc_file_download&docfile_md5id={$result['id_record']}", doc_getrewriterules(), $objDocFile->fields['name'], null, true);

                        $template_body->assign_block_vars('switch_search.result',
                            array(
                                'RELEVANCE' => sprintf("%.02f", $result['relevance']),
                                'TITLE' => ploopi\str::htmlentities($objDocFile->fields['name']),
                                'TITLE_RAW' => $objDocFile->fields['name'],
                                'AUTHOR' => '',
                                'EXTRACT' => '',
                                'METATITLE' => ploopi\str::htmlentities($objDocFile->fields['name']),
                                'METATITLE_RAW' => $objDocFile->fields['name'],
                                'METAKEYWORDS' => '',
                                'METADESCRIPTION' => '',
                                'DATE' => current(ploopi\date::timestamp2local($objDocFile->fields['timestp_create'])),
                                'SIZE' => sprintf("%.02f", $objDocFile->fields['size']/1024),
                                'LINK' => $link,
                                'SHORT_LINK' => ploopi\str::cut($link, 50, 'middle')
                            )
                        );

                        $responses++;
                    }
                }
            }
            else // c'est un article
            {
                $objArticle = new webedit_article();

                // Soit l'id de l'article (si objet lié) ou id de l'enregistrement
                $idrecord = isset($result['id_article']) ? $result['id_article'] : $result['id_record'];

                if ($objArticle->open($idrecord) && $objArticle->isenabled())
                {
                    // Contrôle de la date de publication (au sens création de l'article) si renseignée dans la recherche
                    if (($objArticle->fields['timestp'] >= $ts_date_b || empty($ts_date_b)) && ($objArticle->fields['timestp'] <= $ts_date_e || empty($ts_date_e)))
                    {
                        // Rubrique non privée ou accessible par l'utilisateur
                        if (!$arrHeadings['list'][$objArticle->fields['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$objArticle->fields['id_heading']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$objArticle->fields['id_heading']]['herited_private']]) || $webedit_mode == 'edit')
                        {
                            // c'est un objet intégré dans un article
                            if (isset($result['id_article']))
                            {

                                $cbSearchFunc = $_SESSION['ploopi']['modules'][$result['id_module']]['moduletype'].'_object_searchresult';

                                if (function_exists($cbSearchFunc))
                                {
                                    $cbSearchFunc($template_body, $result, $objArticle, $arrHeadings);
                                    $responses++;
                                }
                            }
                            else
                            {
                                $cleaned_content = strip_tags(ploopi\str::html_entity_decode($objArticle->fields['content_cleaned']));

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
                                        if (isset($arrHeadings['list'][$objArticle->fields['id_heading']])) foreach(explode(';', $arrHeadings['list'][$objArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                                        $link = ploopi\str::urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$result['id_record']}", webedit_getrewriterules(), $objArticle->fields['metatitle'], $arrParents);
                                    break;
                                }

                                if (!isset($result['kw'])) $result['kw'] = [];
                                if (!isset($result['stem'])) $result['stem'] = [];

                                $template_body->assign_block_vars('switch_search.result',
                                    array(
                                        'RELEVANCE' => sprintf("%.02f", $result['relevance']),
                                        'TITLE' => ploopi\str::htmlentities($objArticle->fields['title']),
                                        'TITLE_RAW' => $objArticle->fields['title'],
                                        'AUTHOR' => ploopi\str::htmlentities($objArticle->fields['author']),
                                        'AUTHOR_RAW' => $objArticle->fields['author'],
                                        'EXTRACT' => empty($result['kw']) && empty($result['stem']) ? $cleaned_content : ploopi\str::highlight($cleaned_content, array_merge(array_keys($result['kw']), array_keys($result['stem']))),
                                        'METATITLE' => ploopi\str::htmlentities($objArticle->fields['metatitle']),
                                        'METATITLE_RAW' => $objArticle->fields['metatitle'],
                                        'METAKEYWORDS' => ploopi\str::htmlentities($objArticle->fields['metakeywords']),
                                        'METAKEYWORDS_RAW' => $objArticle->fields['metakeywords'],
                                        'METADESCRIPTION' => ploopi\str::htmlentities($objArticle->fields['metadescription']),
                                        'METADESCRIPTION_RAW' => $objArticle->fields['metadescription'],
                                        'DATE' => ($objArticle->fields['timestp']!='') ? current(ploopi\date::timestamp2local($objArticle->fields['timestp'])) : '',
                                        'SIZE' => sprintf("%.02f", strlen($cleaned_content)/1024),
                                        'LINK' => $link,
                                        'SHORT_LINK' => ploopi\str::cut($link, 50, 'middle')
                                    )
                                );

                                $responses++;
                            }
                        }
                    }
                }
            }
        }

        if ($responses == 0) // pas de réponse valide !
        {
            $template_body->assign_block_vars('switch_search.switch_notfound',array());
        }

        $title_raw = "Résultat de la recherche pour \" {$query_string} \"";
        $title = ploopi\str::htmlentities($title_raw);

        $template_body->assign_vars(
            array(
                'SEARCH_RESPONSES' => $responses,
                'PAGE_TITLE' => $title,
                'PAGE_TITLE_RAW' => $title_raw,
                'PAGE_KEYWORDS' => ploopi\str::htmlentities($query_string),
                'PAGE_KEYWORDS_RAW' => $query_string,
                'PAGE_DESCRIPTION' => $title,
                'PAGE_DESCRIPTION_RAW' => $title_raw,
                'PAGE_META_TITLE' => $title,
                'PAGE_META_TITLE_RAW' => $title_raw,
                'PAGE_META_KEYWORDS' => ploopi\str::htmlentities($query_string),
                'PAGE_META_KEYWORDS_RAW' => $query_string,
                'PAGE_META_DESCRIPTION' => $title,
                'PAGE_META_DESCRIPTION_RAW' => $title
            )
        );
    }

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

            WHERE       t.tag = '".ploopi\db::get()->addslashes($query_tag)."'
            AND         at.id_tag = t.id
            AND         at.id_article = a.id
            AND         (a.timestp_published <= $today OR a.timestp_published = 0)
            AND         (a.timestp_unpublished >= $today OR a.timestp_unpublished = 0)
            ";

    ploopi\db::get()->query($sql);

    while ($row = ploopi\db::get()->fetchrow())
    {
        if (!$arrHeadings['list'][$row['id_heading']]['private'] || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$row['id_heading']]['herited_private']]) || $webedit_mode == 'edit') // Rubrique non privée ou accessible par l'utilisateur
        {
            $size = sprintf("%.02f", strlen(strip_tags(ploopi\str::html_entity_decode($row['content_cleaned'])))/1024);

            switch($webedit_mode)
            {
                case 'edit';
                case 'render';
                    $link = "index.php?webedit_mode=render&headingid={$row['id_heading']}&articleid={$row['id']}";
                break;

                default:
                case 'display';
                    $arrParents = array();
                    if (isset($arrHeadings['list'][$row['id_heading']])) foreach(preg_split('/;/', $arrHeadings['list'][$row['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $link = ploopi\str::urlrewrite("index.php?headingid={$row['id_heading']}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                break;
            }

            $template_body->assign_block_vars('switch_tagsearch.result',
                array(
                    'TITLE' => ploopi\str::htmlentities($row['title']),
                    'AUTHOR' => ploopi\str::htmlentities($row['author']),
                    'META_TITLE' => ploopi\str::htmlentities($row['metatitle']),
                    'META_KEYWORDS' => ploopi\str::htmlentities($row['metakeywords']),
                    'META_DESCRIPTION' => ploopi\str::htmlentities($row['metadescription']),
                    'DATE' => ($row['timestp']!='') ? current(ploopi\date::timestamp2local($row['timestp'])) : '',
                    'SIZE' => $size,
                    'LINK' => $link,
                    'SHORT_LINK' => ploopi\str::cut($link, 50, 'middle')
                )
            );
        }
    }

    $title_raw = "Liste des articles contenant le tag \" {$query_tag} \"";
    $title = ploopi\str::htmlentities($title_raw);

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => $title,
            'PAGE_TITLE_RAW' => $title,
            'PAGE_KEYWORDS' => ploopi\str::htmlentities($query_tag),
            'PAGE_KEYWORDS_RAW' => $query_tag,
            'PAGE_DESCRIPTION' => $title,
            'PAGE_DESCRIPTION_RAW' => $title_raw,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_TITLE_RAW' => $title_raw,
            'PAGE_META_KEYWORDS' => ploopi\str::htmlentities($query_tag),
            'PAGE_META_KEYWORDS_RAW' => $query_tag,
            'PAGE_META_DESCRIPTION' => $title,
            'PAGE_META_DESCRIPTION_RAW' => $title_raw
        )
    );
}
elseif (!empty($intTemplateModuleId)) // contenu d'un module
{
    $template_body->assign_block_vars("switch_content_module_{$_SESSION['ploopi']['modules'][$intTemplateModuleId]['moduletype']}", array());

    global $template_moduleid;
    $template_moduleid = $intTemplateModuleId;
    include_once "./modules/{$_SESSION['ploopi']['modules'][$intTemplateModuleId]['moduletype']}/template_content.php";
    unset($template_moduleid);

    $template_body->assign_vars(
        array(
            'MODULE_ID' => $intTemplateModuleId,
            'MODULE_TITLE' => $_SESSION['ploopi']['modules'][$intTemplateModuleId]['label'],
            'MODULE_VERSION' => $_SESSION['ploopi']['modules'][$intTemplateModuleId]['version'],
            'MODULE_AUTHOR' => $_SESSION['ploopi']['modules'][$intTemplateModuleId]['author'],
            'MODULE_DATE' => current(ploopi\date::timestamp2local($_SESSION['ploopi']['modules'][$intTemplateModuleId]['date']))
        )
    );
}
elseif (!empty($ploopi_op) && $ploopi_op == 'webedit_unsubscribe') // message affiché lors du désabonnement (lien depuis email)
{
    $title_raw = 'Désabonnement';
    $title = ploopi\str::htmlentities($title_raw);

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
            'MESSAGE_CONTENT' => ploopi\str::htmlentities('Votre demande de désabonnement a été prise en compte.')
        )
    );
}

// MODE BLOG
elseif($arrHeadings['list'][$headingid]['content_type'] == 'blog' && $webedit_mode != 'edit' && empty($articleid)) // Affichage sous forme de blog. Toutes les page de la rubrique les une sous les autres.
{
    if(isset($headingid))
    {
        // Rubrique privée et non autorisée
        if ($arrHeadings['list'][$headingid]['private'] && !isset($arrShares[$arrHeadings['list'][$headingid]['herited_private']]) && !isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$headingid]['herited_private']]) && $webedit_mode != 'edit')
        {
            $template_body->assign_block_vars('switch_private', array());
            if (!$_SESSION['ploopi']['connected']) $template_body->assign_block_vars('switch_private.switch_notconnected', array());
            else $template_body->assign_block_vars('switch_private.switch_notallowed', array());
        }
        else
        {
            // get all articles for calendar et month
            if($type == 'draft')
            {
                $select =   "
                            SELECT      id, author, comments_allowed, content, content_cleaned,
                                        headcontent, id_module, lastupdate_id_user, lastupdate_timestp,
                                        metadescription, metakeywords, metatitle, position,
                                        reference, timestp, timestp_published, timestp_unpublished,
                                        title, version, visible
                            FROM        ploopi_mod_webedit_article_draft

                            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         id_heading = {$headingid}
                            AND         timestp > 0
                            AND         (timestp_published <= $today OR timestp_published = 0)
                            AND         (timestp_unpublished >= $today OR timestp_unpublished = 0)
                            ORDER BY    timestp DESC, id DESC
                            ";
            }
            else
            {
                $select =   "
                            SELECT      id, author, comments_allowed, content, content_cleaned,
                                        headcontent, id_module, lastupdate_id_user, lastupdate_timestp,
                                        metadescription, metakeywords, metatitle, position,
                                        reference, timestp, timestp_published, timestp_unpublished,
                                        title, version, visible
                            FROM        ploopi_mod_webedit_article

                            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         id_heading = {$headingid}
                            AND         timestp > 0
                            AND         (timestp_published <= $today OR timestp_published = 0)
                            AND         (timestp_unpublished >= $today OR timestp_unpublished = 0)

                            ORDER BY    timestp DESC, id DESC
                            ";
            }

            $resSQL = ploopi\db::get()->query($select); // ATTENTION CE RESULTAT DE REQUETE "$resSQL" SERT A NOUVEAU PLUS BAS !!!

            if (ploopi\db::get()->numrows($resSQL))
            {
                // Tableau tampon des infos user (dans un blog on retrouve souvent les meme info sur le redacteur ! pas besoin de $objuser->open(iduser) 50x !)
                $arrTmpUser = array();

                $objArticle = new webedit_article($type);

                // Contenu du blog
                $template_body->assign_block_vars('switch_content_blog', array());

                // Bouton de menu
                $booIsSwitchPages = false;

                $booIsHomePage = false;
                // On a une page avant ?
                $booPageBefore = false;
                // On a une page après ?
                $booPageAfter = false;

                // nb d'article visible
                $nbvisart = 0;
                // compteur d'article
                $intnumArt = 0;
                // Tableau des id article à traiter
                $arrShowArticle = array();

                // On balaye TOUS les article du id heading pour générer les boutons
                // On filtre aussi dans $arrShowArticle les articles à afficher
                while ($row = ploopi\db::get()->fetchrow($resSQL))
                {
                    $arrDate = ploopi\date::gettimestampdetail($row['timestp']);

                    $year       = $arrDate[1];
                    $month      = $arrDate[2];
                    $day        = $arrDate[3];
                    $date       = $arrDate[1].$arrDate[2].$arrDate[3];
                    $yearmonth  = $arrDate[1].$arrDate[2];
                    unset($arrDate);

                    $booUseArticle = true;

                    // Filtre sur les article à traiter (Filtre sur le mois ou sur le jour !)
                    if(isset($_GET['yearmonth']) && is_numeric($_GET['yearmonth']) && isset($_GET['day']) && is_numeric($_GET['day']))
                        $booUseArticle = ($date == $_GET['yearmonth'].$_GET['day']);
                    elseif(isset($_GET['yearmonth']) && is_numeric($_GET['yearmonth']))
                        $booUseArticle = ($yearmonth == $_GET['yearmonth']);
                    elseif(isset($_GET['year']) && is_numeric($_GET['year']))
                        $booUseArticle = ($year == $_GET['year']);


                    if($booUseArticle) $intnumArt++;

                    // Array des pages à traiter immédiatement
                    if($booUseArticle && ($intNumMaxBlog == 0 || ($intNumMaxBlog >= $intnumArt && $intnumArt >= $intNumMinBlog)))
                    {
                        $arrShowArticle[] = $row;
                        $nbvisart++;
                    }
                    else
                    {
                        // des pages avant ? donc date plus anciennes (pour "page précédente")
                        if($booPageBefore || ($booUseArticle && $intNumMaxBlog != 0 && $intnumArt > $intNumMaxBlog)) $booPageBefore = true;
                        // des pages apres ? donc date plus récentes en date (pour "Page suivante")
                        if($booPageAfter || ($booUseArticle && $intNumMaxBlog != 0 && $intnumArt < $intNumMinBlog)) $booPageAfter = true;
                    }
                }

                // Compteur pour comparer avec $nbvisart et générer le switch pour séparateur
                $numvisart = 0;

                // AFFICHAGE DES ARTICLES
                foreach ($arrShowArticle as $row)
                {
                    $numvisart++;

                    // Bloc PAGE (boutons pour les pages)
                    if($webedit_mode == 'render')
                        $scriptUrlArticle = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&articleid={$row['id']}";
                    else
                    {
                        $arrParents = array();
                        if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                        $scriptUrlArticle = ploopi\str::urlrewrite("index.php?headingid={$headingid}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                    }

                    $sel = '';
                    if ($articleid == $row['id']) $sel = 'selected';

                    $ldate_pub = (!empty($row['timestp_published'])) ? ploopi\date::timestamp2local($row['timestp_published']) : array('date' => '');
                    $ldate_unpub = (!empty($row['timestp_unpublished'])) ? ploopi\date::timestamp2local($row['timestp_unpublished']) : array('date' => '');
                    $ldate_lastupdate = (!empty($row['lastupdate_timestp'])) ? ploopi\date::timestamp2local($row['lastupdate_timestp']) : array('date' => '', 'time' => '');
                    $ldate_timestp = (!empty($row['timestp'])) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '');

                    $var_tpl_page =
                        array(
                            'REFERENCE'     => ploopi\str::htmlentities($row['reference']),
                            'LABEL'         => ploopi\str::htmlentities($row['title']),
                            'LABEL_RAW'     => $row['title'],
                            'CONTENT'       => ploopi\str::htmlentities($row['content_cleaned']),
                            'AUTHOR'        => ploopi\str::htmlentities($row['author']),
                            'AUTHOR_RAW'    => $row['author'],
                            'VERSION'       => ploopi\str::htmlentities($row['version']),
                            'DATE'          => ploopi\str::htmlentities($ldate_timestp['date']),
                            'LASTUPDATE_DATE' => ploopi\str::htmlentities($ldate_lastupdate['date']),
                            'LASTUPDATE_TIME' => ploopi\str::htmlentities($ldate_lastupdate['time']),
                            'DATE_PUB'   => $ldate_pub['date'],
                            'DATE_UNPUB' => $ldate_unpub['date'],
                            'TIMESTP_PUB'   => $ldate_pub['date'],
                            'TIMESTP_UNPUB' => $ldate_unpub['date'],
                            'LINK'          => $scriptUrlArticle,
                            'POSITION'      => $row['position'],
                            'SEL'           => $sel
                        );

                    // Bouton de menu
                    if($row['visible'])
                    {
                        if(!$booIsSwitchPages) { $booIsSwitchPages = true; $template_body->assign_block_vars('switch_pages', array()); }
                        $template_body->assign_block_vars('switch_pages.page', $var_tpl_page);
                    }


                    $content = '';

                    // Test si l'article a déjà été visité pendant la session
                    if (!isset($_SESSION['webedit']['counter'][$row['id']]))
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
                        if (!$objCounter->open($counter['year'], $counter['month'], $counter['day'], $row['id']))
                        {
                            $objCounter->fields['year'] = $counter['year'];
                            $objCounter->fields['month'] = $counter['month'];
                            $objCounter->fields['day'] = $counter['day'];
                            $objCounter->fields['week'] = $counter['week'];
                            $objCounter->fields['id_article'] = $row['id'];
                            $objCounter->fields['id_module'] = $row['id_module'];
                        }
                        $objCounter->hit();

                        $_SESSION['webedit']['counter'][$row['id']] = '';
                    }


                    /**
                     * Traitement des objets WCE/WebEdit
                     * avec détection de chaîne et remplacement par le contenu d'une fonction
                     */
                    if (!empty($editor)) $content = $editor;
                    else
                    {
                        $objArticle->fields = $row; // astuce pour pouvoir se servir de webedit_replace_links() !
                        $content = preg_replace_callback('/\[\[(.*)\]\]/i','webedit_getobjectcontent', $webedit_mode == 'edit' ? $row['content_cleaned'] : webedit_replace_links($objArticle, $webedit_mode, $arrHeadings) );
                    }

                    $article_timestp = ($row['timestp']!='') ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '');
                    $article_lastupdate = ($row['lastupdate_timestp']!='') ? ploopi\date::timestamp2local($row['lastupdate_timestp']) : array('date' => '', 'time' => '');

                    if(isset($arrTmpUser[$row['lastupdate_id_user']]))
                    {
                        if(!empty($arrTmpUser[$row['lastupdate_id_user']]))
                        {
                            $user_lastname = $arrTmpUser[$row['lastupdate_id_user']]['lastname'];
                            $user_firstname = $arrTmpUser[$row['lastupdate_id_user']]['firstname'];
                            $user_login = $arrTmpUser[$row['lastupdate_id_user']]['login'];
                        }
                        else
                            $user_lastname = $user_firstname = $user_login = '';
                    }
                    else
                    {
                        $user = new ploopi\user();
                        if ($user->open($row['lastupdate_id_user']))
                        {
                            $user_lastname = $user->fields['lastname'];
                            $user_firstname = $user->fields['firstname'];
                            $user_login = $user->fields['login'];
                            $arrTmpUser[$row['lastupdate_id_user']] = $user->fields;  // On garde l'info temporairement
                        }
                        else
                        {
                            $user_lastname = $user_firstname = $user_login = '';
                            $arrTmpUser[$row['lastupdate_id_user']] = '';   // On garde l'info temporairement
                        }
                    }
                    list($keywords) = ploopi\str::getwords("{$row['metatitle']} {$row['metakeywords']} {$row['author']}");

                    $kwds_raw = implode(', ', array_keys($keywords));
                    $kwds = ploopi\str::htmlentities($kwds_raw);

                    $desc_raw = $row['metadescription'];
                    $desc = ploopi\str::htmlentities($row['metadescription']);

                    $template_body->assign_block_vars('switch_content_blog.article',
                        array(
                            'PAGE_ID' => $row['id'],
                            'PAGE_REFERENCE' => ploopi\str::htmlentities($row['reference']),
                            'PAGE_REFERENCE_RAW' => $row['reference'],
                            'PAGE_TITLE' => ploopi\str::htmlentities($row['title']),
                            'PAGE_TITLE_RAW' => $row['title'],
                            'PAGE_TITLE_ESCAPED' => addslashes($row['title']),
                            'PAGE_KEYWORDS' => $kwds,
                            'PAGE_KEYWORDS_RAW' => $kwds_raw,
                            'PAGE_DESCRIPTION' => $desc,
                            'PAGE_DESCRIPTION_RAW' => $desc_raw,
                            'PAGE_DESCRIPTION_ESCAPED' => addslashes($row['metadescription']),
                            'PAGE_META_TITLE' => ploopi\str::htmlentities($row['metatitle']),
                            'PAGE_META_TITLE_RAW' => $row['metatitle'],
                            'PAGE_META_KEYWORDS' => $kwds,
                            'PAGE_META_KEYWORDS_RAW' => $kwds_raw,
                            'PAGE_META_DESCRIPTION' => $desc,
                            'PAGE_META_DESCRIPTION_RAW' => $desc_raw,
                            'PAGE_AUTHOR' => ploopi\str::htmlentities($row['author']),
                            'PAGE_AUTHOR_RAW' => $row['author'],
                            'PAGE_VERSION' => ploopi\str::htmlentities($row['version']),
                            'PAGE_VERSION_RAW' => $row['version'],
                            'PAGE_DATE' => ploopi\str::htmlentities($article_timestp['date']),
                            'PAGE_LASTUPDATE_DATE' => ploopi\str::htmlentities($article_lastupdate['date']),
                            'PAGE_LASTUPDATE_TIME' => ploopi\str::htmlentities($article_lastupdate['time']),
                            'PAGE_LASTUPDATE_USER_LASTNAME' => ploopi\str::htmlentities($user_lastname),
                            'PAGE_LASTUPDATE_USER_FIRSTNAME' => ploopi\str::htmlentities($user_firstname),
                            'PAGE_LASTUPDATE_USER_LOGIN' => ploopi\str::htmlentities($user_login),
                            'PAGE_CONTENT' => $content,
                            'PAGE_HEADCONTENT' => $row['headcontent'],
                            'PAGE_URL_ARTICLE' => $scriptUrlArticle
                        )
                    );

                    if ($numvisart < $nbvisart) $template_body->assign_block_vars('switch_content_blog.article.sw_separator',array());
                    if (!empty($article_lastupdate['date'])) $template_body->assign_block_vars('switch_content_blog.article.sw_modify',array());

                    // Recherche les tags
                    $sqlTag =  "
                        SELECT  t.tag
                        FROM    ploopi_mod_webedit_tag t,
                                ploopi_mod_webedit_article_tag at
                        WHERE   t.id = at.id_tag
                        AND     at.id_article = {$row['id']}
                        ";
                    $resSqltag = ploopi\db::get()->query($sqlTag);
                    if(ploopi\db::get()->numrows($resSqltag))
                    {
                        $template_body->assign_block_vars('switch_content_blog.article.switch_tags', array());

                        while ($tag = ploopi\db::get()->fetchrow($resSqltag))
                        {
                            if($webedit_mode == 'render')
                                $link =  "index.php?webedit_mode=render&query_tag={$tag['tag']}";
                            else
                                $link =  ploopi\str::urlrewrite("index.php?query_tag={$tag['tag']}", webedit_getrewriterules());

                            $template_body->assign_block_vars('switch_content_blog.article.switch_tags.tag',
                                array(
                                    'TAG' => $tag['tag'],
                                    'LINK' => $link
                                )
                            );
                        }
                    }
                    else
                    {
                        // pas d'article par défaut, on teste si on est sur la rubrique d'accueil
                        $booIsHomePage = (!empty($headingid) && $arrHeadings['tree'][0][0] == $headingid);
                    }


                    // Doit on autoriser les commentaires
                    if ($row['comments_allowed'])
                    {
                        $nbComment = 0;

                        $action = 'index.php?ploopi_op=webedit_save_comment';
                        if(isset($webedit_mode) && $webedit_mode != 'display') $action .= '&webedit_mode='.$webedit_mode;
                        if(isset($headingid)) $action .= '&headingid='.$headingid;
                        if(isset($row['id'])) $action .= '&articleid='.$row['id'];

                        $template_body->assign_block_vars('switch_content_blog.article.sw_comment',
                            array(
                                'LIBELLE_POST'  => _WEBEDIT_COMMENT_POST,
                                'ACTION'        => ploopi\crypt::urlencode($action)
                            )
                        );

                        // On recherche les commentaires de l'article
                        $selectComment = "
                            SELECT      id, comment, email, timestp, publish, nickname
                            FROM        ploopi_mod_webedit_article_comment
                            WHERE       id_article = '{$row['id']}'
                            AND         id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         publish = 1
                            ORDER BY    timestp DESC
                            ";

                        $resSqlComment = ploopi\db::get()->query($selectComment);

                        if(ploopi\db::get()->numrows())
                        {
                            if($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nb_comm_blog'])
                            {
                                while($rowComment = ploopi\db::get()->fetchrow($resSqlComment))
                                {
                                    $date_comment = (!empty($row['timestp_published'])) ? ploopi\date::timestamp2local($rowComment['timestp']) : array('date' => '', 'time' => '');

                                    $nbComment++;
                                    if($nbComment <= $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nb_comm_blog'])
                                    {
                                        $template_body->assign_block_vars('switch_content_blog.article.sw_comment.comment',
                                            array(
                                                'ID'        => $rowComment['id'],
                                                'PUBLISHED' => $rowComment['publish'],
                                                'COMMENT'   => ploopi\str::htmlentities($rowComment['comment']),
                                                'EMAIL'     => ploopi\str::htmlentities($rowComment['email']),
                                                'NICKNAME'  => ploopi\str::htmlentities($rowComment['nickname']),
                                                'DATE'      => $date_comment['date'],
                                                'TIME'      => $date_comment['time'],
                                                'POSTBY'    => sprintf(_WEBEDIT_COMMENT_COMMENT_POSTBY,ploopi\str::htmlentities($rowComment['nickname']),$date_comment['date'],$date_comment['time'])
                                            )
                                        );
                                    }
                                    else // Si il y en a + on affiche pour voir les autres commentaires
                                    {
                                        $template_body->assign_block_vars('switch_content_blog.article.sw_comment.sw_showall',
                                            array(
                                                'LIBELLE_SHOW'  => _WEBEDIT_COMMENT_SHOWALL
                                            )
                                        );
                                        $nbComment = ploopi\db::get()->numrows($resSqlComment);
                                        break;
                                    }
                                }
                            }
                        }

                        $nbComment = ploopi\db::get()->numrows($resSqlComment);

                        $template_body->assign_block_vars('switch_content_blog.article.sw_comment.info',
                            array(
                                'LIBELLE'       => _WEBEDIT_COMMENT_COMMENT,
                                'NB_COMMENT'    => $nbComment,
                            )
                        );
                    }
                }

                $param = '';
                if($booPageBefore || $booPageAfter)
                {
                    if(isset($_GET['yearmonth'])) $param .= '&yearmonth='.$_GET['yearmonth'];
                    if(isset($_GET['day'])) $param .= '&day='.$_GET['day'];
                }

                // PAGE PRECEDENTE
                if($booPageBefore)
                {
                    if($webedit_mode == 'render')
                        $strUrl = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&numpage=".($intNumPage+1).$param;
                    else
                    {
                        $arrParents = array();
                        if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                        $strUrl = ploopi\str::urlrewrite("index.php?headingid={$headingid}&numpage=".($intNumPage+1).$param,webedit_getrewriterules(),$arrHeadings['list'][$headingid]['label'], $arrParents);
                    }

                    $template_body->assign_block_vars('switch_content_blog.page_before', array('URL' => $strUrl));
                }

                // PAGE SUIVANTE
                if($booPageAfter)
                {
                    if($webedit_mode == 'render')
                        $strUrl = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&numpage=".($intNumPage-1).$param;
                    else
                    {
                        $arrParents = array();
                        if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                        $strUrl = ploopi\str::urlrewrite("index.php?headingid={$headingid}&numpage=".($intNumPage-1).$param,webedit_getrewriterules(),$arrHeadings['list'][$headingid]['label'], $arrParents);
                    }

                    $template_body->assign_block_vars('switch_content_blog.page_after', array('URL' => $strUrl));
                }

                // Doit on afficher le flux de la rubrique ?
                if (!$booIsHomePage && isset($arrHeadings['list'][$headingid]) && $arrHeadings['list'][$headingid]['feed_enabled'])
                {
                    $template_body->assign_block_vars(
                        'switch_atomfeed_heading',
                        array(
                            'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=atom&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']), //ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom&headingid={$headingid}", null, null, null, null, false),
                            'TITLE' => ploopi\str::htmlentities($arrHeadings['list'][$headingid]['label']),
                        )
                    );

                    $template_body->assign_block_vars(
                        'switch_rssfeed_heading',
                        array(
                            'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=rss&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']),//ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss&headingid={$headingid}", null, null, null, null, false),
                            'TITLE' => ploopi\str::htmlentities($arrHeadings['list'][$headingid]['label']),
                        )
                    );
                }

                // Doit on autoriser les abonnements ?
                if (($booIsHomePage && isset($arrHeadings['subscription_enabled']) && $arrHeadings['subscription_enabled']) || (isset($arrHeadings['list'][$headingid]) && $arrHeadings['list'][$headingid]['subscription_enabled']))
                {
                    // Gestion des url par mode de rendu
                    if($webedit_mode == 'render')
                        $link = ploopi\crypt::urlencode("index.php?webedit_mode=render&ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);
                    else
                        $link = ploopi\crypt::urlencode("index.php?ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);

                    $template_body->assign_block_vars(
                        'switch_subscription',
                        array(
                            'ACTION' => $link,
                            'HEADINGID' => $headingid,
                            'ROOTID' => 0
                        )
                    );
                }

                if (!empty($_GET['subscription_return']) && isset($webedit_subscription_messages[$_GET['subscription_return']])) // réponse suite à une demande d'abonnement
                {
                    $template_body->assign_block_vars(
                        'switch_subscription.switch_response',
                        array(
                            'CONTENT' => $webedit_subscription_messages[$_GET['subscription_return']]
                        )
                    );
                }

                // template de la home page
                if ($booIsHomePage && file_exists("./templates/frontoffice/{$template_name}/home.tpl")) $template_file = 'home.tpl';
            }
        }
    }
    else // !isset($headingid)
    {
        // renvoi à la racine
        $headingid = $arrHeadings['tree'][0][0];
        $intErrorCode = 404;
    }
}
else // affichage standard rubrique/page
{

    // Rubrique privée et non autorisée
    if ($arrHeadings['list'][$headingid]['private'] && !isset($arrShares[$arrHeadings['list'][$headingid]['herited_private']]) && !isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$headingid]['herited_private']]) && $webedit_mode != 'edit')
    {
        $template_body->assign_block_vars('switch_private', array());
        if (!$_SESSION['ploopi']['connected']) $template_body->assign_block_vars('switch_private.switch_notconnected', array());
        else $template_body->assign_block_vars('switch_private.switch_notallowed', array());
    }
    else
    {
        if(in_array($arrHeadings['list'][$headingid]['content_type'], array('headings', 'sitemap')) && empty($articleid)) // affichage rubriques ou plan de site
        {

            ploopi\db::get()->query("
                SELECT      id, visible, metatitle, timestp_published, timestp_unpublished, lastupdate_timestp,
                            timestp, reference, title, content, content_cleaned, author, version, position, id_heading
                FROM        ploopi_mod_webedit_article
                WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                AND         (timestp_published <= {$today} OR timestp_published = 0)
                AND         (timestp_unpublished >= {$today} OR timestp_unpublished = 0)
                ORDER BY    id_heading, position
            ");

            $arrArticles = array();
            while ($row = ploopi\db::get()->fetchrow()) $arrArticles[$row['id_heading']][] = $row;
            // Affichage rubriques
            if ($arrHeadings['list'][$headingid]['content_type'] == 'headings')
            {
                $template_body->assign_block_vars('switch_content_heading', array());
                webedit_template_assign_headings($arrHeadings, $arrArticles, $arrShares, $headingid);
            }
            // Affichage plan de site
            else
            {
                $template_body->assign_block_vars('switch_content_sitemap', array());
                webedit_template_assign_headings($arrHeadings, $arrArticles, $arrShares, 0, 'switch_content_sitemap.', 'heading', 0);
            }
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
                            SELECT      id, visible, metatitle, timestp_published, timestp_unpublished, lastupdate_timestp,
                                        timestp, reference, title, content_cleaned, author, version,position
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
                            SELECT      id, visible, metatitle, timestp_published, timestp_unpublished, lastupdate_timestp,
                                        timestp, reference, title, content_cleaned, author, version,position
                            FROM        ploopi_mod_webedit_article
                            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         id_heading = {$headingid}
                            AND         (timestp_published <= $today OR timestp_published = 0)
                            AND         (timestp_unpublished >= $today OR timestp_unpublished = 0)
                            ORDER BY    {$article_orderby}
                            ";
            break;
        }

        $resSQL = ploopi\db::get()->query($select); // ATTENTION CE RESULTAT DE REQUETE "$resSQL" PEUT SERVIR A NOUVEAU PLUS BAS !!!

        if (ploopi\db::get()->numrows($resSQL))
        {
            // Bouton de menu
            $booIsSwitchPages = false;

            // visible articles
            $nbvisart = 0;

            $article_array = array();

            while ($row = ploopi\db::get()->fetchrow($resSQL))
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
                            $script = "javascript:window.parent.document.location.href='".ploopi\crypt::urlencode("admin.php?op=article_modify&headingid={$headingid}&articleid={$row['id']}")."';";
                        break;

                        case 'render';
                            $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&articleid={$row['id']}";
                            //$script = "admin.php?nav={$nav}&articleid={$row['id']}";
                        break;

                        default:
                        case 'display';
                            $arrParents = array();
                            if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                            $script = ploopi\str::urlrewrite("index.php?headingid={$headingid}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                        break;
                    }

                    $sel = '';
                    if ($articleid == $row['id']) $sel = 'selected';

                    $ldate_pub = (!empty($row['timestp_published'])) ? ploopi\date::timestamp2local($row['timestp_published']) : array('date' => '');
                    $ldate_unpub = (!empty($row['timestp_unpublished'])) ? ploopi\date::timestamp2local($row['timestp_unpublished']) : array('date' => '');
                    $ldate_lastupdate = (!empty($row['lastupdate_timestp'])) ? ploopi\date::timestamp2local($row['lastupdate_timestp']) : array('date' => '', 'time' => '');
                    $ldate_timestp = (!empty($row['timestp'])) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '');

                    $var_tpl_page =
                        array(
                            'REFERENCE'     => ploopi\str::htmlentities($row['reference']),
                            'LABEL'         => ploopi\str::htmlentities($row['title']),
                            'LABEL_RAW'     => $row['title'],
                            'CONTENT'       => ploopi\str::htmlentities($row['content_cleaned']),
                            'AUTHOR'        => ploopi\str::htmlentities($row['author']),
                            'AUTHOR_RAW'    => $row['author'],
                            'VERSION'       => ploopi\str::htmlentities($row['version']),
                            'DATE'          => ploopi\str::htmlentities($ldate_timestp['date']),
                            'LASTUPDATE_DATE' => ploopi\str::htmlentities($ldate_lastupdate['date']),
                            'LASTUPDATE_TIME' => ploopi\str::htmlentities($ldate_lastupdate['time']),
                            'DATE_PUB'   => $ldate_pub['date'],
                            'DATE_UNPUB' => $ldate_unpub['date'],
                            'TIMESTP_PUB'   => $ldate_pub['date'],
                            'TIMESTP_UNPUB' => $ldate_unpub['date'],
                            'LINK'          => $script,
                            'POSITION'      => $row['position'],
                            'SEL'           => $sel
                        );

                    if(!$booIsSwitchPages) { $booIsSwitchPages = true; $template_body->assign_block_vars('switch_pages', array()); }

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
                    ?>
                    <script src="./vendor/ckeditor/ckeditor/ckeditor.js"></script>
                    <div id="editor"><?php echo $article->fields['content']; ?></div>
                    <script>

                        // Ajout d'un plugin externe
                        CKEDITOR.plugins.addExternal('tag', '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/plugins/tag/'; ?>', 'plugin.js');

                        /*
                        // http://docs.ckeditor.com/#!/guide/plugin_sdk_styles
                        CKEDITOR.plugins.add( 'tag', {
                            init: function( editor ) {
                                var pluginDirectory = '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/plugins/tag/'; ?>';
                                editor.addContentsCss( pluginDirectory + 'styles.css' );
                            }
                        } );
                        */

                        // http://docs.ckeditor.com/#!/guide/dev_file_browser_api
                        CKEDITOR.replace( 'editor', {
                            customConfig: '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/config.js'; ?>',
                            // doc_selectfile
                            filebrowserBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=webedit_selector'); ?>',
                            filebrowserImageBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=doc_selectimage'); ?>',
                            // Url de choix des objets
                            objectBrowserUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=ploopi_getobjects'); ?>',
                            // Chargement de styles complémentaires (on remet le fichier par défaut en 1er)
                            // Puis on ajoute la feuille de style des plugins...
                            contentsCss: [
                                CKEDITOR.basePath+'contents.css',
                                '<?php echo _PLOOPI_BASEPATH; ?>/modules/webedit/ckeditor/plugins/tag/styles.css',
                            ],
                            <?php
                            if ($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] == 'beginner') {
                                ?>
                                toolbar: [
                                    ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ],
                                    ['Styles','Format','Font','FontSize'],
                                    ['TextColor','BGColor'],
                                    ['Image','Link','Unink'],
                                ]
                                <?php
                            }
                            ?>
                        });
                    </script>

                    <?php
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
                else $content = preg_replace_callback('/\[\[(.*)\]\]/i','webedit_getobjectcontent', $webedit_mode == 'edit' ? $article->fields['content_cleaned'] : webedit_replace_links($article, $webedit_mode, $arrHeadings) );

                $article_timestp = ($article->fields['timestp']!='') ? ploopi\date::timestamp2local($article->fields['timestp']) : array('date' => '');
                $article_lastupdate = ($article->fields['lastupdate_timestp']!='') ? ploopi\date::timestamp2local($article->fields['lastupdate_timestp']) : array('date' => '', 'time' => '');

                $user = new ploopi\user();
                if ($user->open($article->fields['lastupdate_id_user']))
                {
                    $user_lastname = $user->fields['lastname'];
                    $user_firstname = $user->fields['firstname'];
                    $user_login = $user->fields['login'];
                }
                else $user_lastname = $user_firstname = $user_login = '';

                list($keywords) = ploopi\str::getwords("{$article->fields['metatitle']} {$article->fields['metakeywords']} {$article->fields['author']}");

                $kwds_raw = implode(', ', array_keys($keywords));
                $kwds = ploopi\str::htmlentities($kwds_raw);

                $desc_raw = $article->fields['metadescription'];
                $desc = ploopi\str::htmlentities($article->fields['metadescription']);

                $id_captcha = md5('article_comment_catpcha_'.$article->fields['id']);

                $template_body->assign_vars(
                    array(
                        'PAGE_ID' => $article->fields['id'],
                        'PAGE_REFERENCE' => ploopi\str::htmlentities($article->fields['reference']),
                        'PAGE_REFERENCE_RAW' => $article->fields['reference'],
                        'PAGE_TITLE' => ploopi\str::htmlentities($article->fields['title']),
                        'PAGE_TITLE_RAW' => $article->fields['title'],
                        'PAGE_TITLE_ESCAPED' => addslashes($article->fields['title']),
                        'PAGE_KEYWORDS' => $kwds,
                        'PAGE_KEYWORDS_RAW' => $kwds_raw,
                        'PAGE_DESCRIPTION' => $desc,
                        'PAGE_DESCRIPTION_RAW' => $desc_raw,
                        'PAGE_DESCRIPTION_ESCAPED' => addslashes($article->fields['metadescription']),
                        'PAGE_META_TITLE' => ploopi\str::htmlentities($article->fields['metatitle']),
                        'PAGE_META_TITLE_RAW' => $article->fields['metatitle'],
                        'PAGE_META_KEYWORDS' => $kwds,
                        'PAGE_META_KEYWORDS_RAW' => $kwds_raw,
                        'PAGE_META_DESCRIPTION' => $desc,
                        'PAGE_META_DESCRIPTION_RAW' => $desc_raw,
                        'PAGE_AUTHOR' => ploopi\str::htmlentities($article->fields['author']),
                        'PAGE_AUTHOR_RAW' => $article->fields['author'],
                        'PAGE_VERSION' => ploopi\str::htmlentities($article->fields['version']),
                        'PAGE_VERSION_RAW' => $article->fields['version'],
                        'PAGE_DATE' => ploopi\str::htmlentities($article_timestp['date']),
                        'PAGE_LASTUPDATE_DATE' => ploopi\str::htmlentities($article_lastupdate['date']),
                        'PAGE_LASTUPDATE_TIME' => ploopi\str::htmlentities($article_lastupdate['time']),
                        'PAGE_LASTUPDATE_USER_LASTNAME' => ploopi\str::htmlentities($user_lastname),
                        'PAGE_LASTUPDATE_USER_FIRSTNAME' => ploopi\str::htmlentities($user_firstname),
                        'PAGE_LASTUPDATE_USER_LOGIN' => ploopi\str::htmlentities($user_login),
                        'PAGE_CONTENT' => $content,
                        'PAGE_HEADCONTENT' => $article->fields['headcontent'],
                        'PAGE_IDCAPTCHA' => $id_captcha,
                        'PAGE_URL_UPDATECAPTCHA' => ploopi\crypt::urlencode('index-light.php?ploopi_op=ploopi_get_captcha&time='.date('His').'&id_captcha='.$id_captcha),
                        'PAGE_URL_CONTROLCAPTCHA' => ploopi\crypt::urlencode('index-light.php?ploopi_op=ploopi_get_captcha_verif&time='.date('His').'&id_captcha='.$id_captcha)
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
                                $link =  ploopi\str::urlrewrite("index.php?query_tag={$tag['tag']}", webedit_getrewriterules());
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

            // Doit on autoriser les commentaires
            if (!$intErrorCode && !empty($article->fields['comments_allowed']) && $webedit_mode !== 'edit')
            {
                $nbComment = 0;

                $action = 'index.php?ploopi_op=webedit_save_comment&headingid='.$headingid;

                if(isset($article->fields['id'])) $action .= '&articleid='.$article->fields['id'];
                if(isset($webedit_mode) && $webedit_mode != 'display') $action .= '&webedit_mode='.$webedit_mode;

                $action .= '&id_captcha='.$id_captcha;

                $template_body->assign_block_vars('switch_content_page.sw_comment',
                    array(
                        'ACTION'        => ploopi\crypt::urlencode($action),
                        'IDCAPTCHA'     => $id_captcha,
                        'URLTOCAPTCHA'      => ploopi\crypt::urlencode('index-light.php?ploopi_op=ploopi_get_captcha&id_captcha='.$id_captcha),
                        // Passage au flash nécessite constament une url_encodée
                        'URLTOCAPTCHASOUND' => (defined('_PLOOPI_URL_ENCODE') && (_PLOOPI_URL_ENCODE)) ? ploopi\crypt::urlencode('index-light.php?ploopi_op=ploopi_get_captcha_sound&id_captcha='.$id_captcha) : urlencode('index-light.php?ploopi_op=ploopi_get_captcha_sound&id_captcha='.$id_captcha)
                    )
                );

                if(isset($_GET['comment_return']) && defined('_WEBEDIT_COMMENT_COMMENT_SEND_'.$_GET['comment_return']))
                {
                    $template_body->assign_block_vars('switch_content_page.sw_comment.sw_comment_response',
                        array(
                            'RESPONSE' => constant('_WEBEDIT_COMMENT_COMMENT_SEND_'.$_GET['comment_return'])
                        )
                    );
                }

                // On recherche les commentaires de l'article
                $selectComment = "
                    SELECT      id, comment, email, timestp, publish, nickname
                    FROM        ploopi_mod_webedit_article_comment
                    WHERE       id_article = '{$article->fields['id']}'
                    AND         id_module = {$_SESSION['ploopi']['moduleid']}
                    AND         publish = 1
                    ORDER BY    timestp DESC
                    ";

                $resSqlComment = ploopi\db::get()->query($selectComment);
                if(ploopi\db::get()->numrows())
                {
                    while($rowComment = ploopi\db::get()->fetchrow($resSqlComment))
                    {
                        $date_comment = ($article->fields['timestp_published']!='') ? ploopi\date::timestamp2local($rowComment['timestp']) : array('date' => '', 'time' => '');

                        $nbComment++;

                        $template_body->assign_block_vars('switch_content_page.sw_comment.comment',
                            array(
                                'ID'        => $rowComment['id'],
                                'PUBLISHED' => $rowComment['publish'],
                                'COMMENT'   => ploopi\str::htmlentities($rowComment['comment']),
                                'EMAIL'     => ploopi\str::htmlentities($rowComment['email']),
                                'NICKNAME'  => ploopi\str::htmlentities($rowComment['nickname']),
                                'DATE'      => $date_comment['date'],
                                'TIME'      => $date_comment['time'],
                                'POSTBY'    => sprintf(_WEBEDIT_COMMENT_COMMENT_POSTBY,ploopi\str::htmlentities($rowComment['nickname']),$date_comment['date'],$date_comment['time'])
                            )
                        );
                    }
                }

                $template_body->assign_block_vars('switch_content_page.sw_comment.info',
                    array(
                        'LIBELLE'       => _WEBEDIT_COMMENT_COMMENT,
                        'NB_COMMENT'    => $nbComment
                    )
                );
            }

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
                    'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=atom&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']), //ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom&headingid={$headingid}", null, null, null, null, false),
                    'TITLE' => ploopi\str::htmlentities($arrHeadings['list'][$headingid]['label']),
                )
            );

            $template_body->assign_block_vars(
                'switch_rssfeed_heading',
                array(
                    'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=rss&headingid={$headingid}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label']),//ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss&headingid={$headingid}", null, null, null, null, false),
                    'TITLE' => ploopi\str::htmlentities($arrHeadings['list'][$headingid]['label']),
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
                    $link = ploopi\crypt::urlencode("index.php?webedit_mode=render&ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);
                break;

                default:
                case 'display';
                    $link = ploopi\crypt::urlencode("index.php?ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}"), null, null, null, null, false);
                break;
            }

            $template_body->assign_block_vars(
                'switch_subscription',
                array(
                    'ACTION' => $link,
                    'HEADINGID' => $headingid,
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
    ploopi\output::h404();
    $template_body->assign_block_vars('switch_content_error', array());

    $strError = 'Erreur '.$intErrorCode;

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => ploopi\str::htmlentities($strError),
            'PAGE_TITLE_RAW' => $strError,
            'PAGE_TITLE_ESCAPED' => addslashes($strError),
            'PAGE_KEYWORDS' => ploopi\str::htmlentities($strError),
            'PAGE_KEYWORDS_RAW' => $strError,
            'PAGE_DESCRIPTION' => ploopi\str::htmlentities($strError),
            'PAGE_DESCRIPTION_RAW' => $strError,
            'PAGE_DESCRIPTION_ESCAPED' => addslashes($strError),
            'PAGE_META_TITLE' => ploopi\str::htmlentities($strError),
            'PAGE_META_TITLE_RAW' => $strError,
            'PAGE_META_KEYWORDS' => $strError,
            'PAGE_META_KEYWORDS_RAW' => $strError,
            'PAGE_META_DESCRIPTION' => ploopi\str::htmlentities($strError),
            'PAGE_META_DESCRIPTION_RAW' => $strError,
            'PAGE_ERROR_CODE' => $intErrorCode,
        )
    );

}

// load a specific template file in edition mode (if exists)
if ($webedit_mode == 'edit' && file_exists("./templates/frontoffice/{$template_name}/fck_{$template_file}")) $template_file = "fck_{$template_file}";

// on vérifie l'existence du fichier TPL, sinon => die
if (!file_exists("./templates/frontoffice/{$template_name}/{$template_file}") || ! is_readable("./templates/frontoffice/{$template_name}/{$template_file}")) {

    ploopi\system::kill(
        str_replace(
            array('<FILE>', '<TEMPLATE>'),
            array($template_file, "./templates/frontoffice/{$template_name}"),
            _PLOOPI_ERROR_TEMPLATE_FILE
        )
    );

}

$template_body->set_filenames(array('body' => $template_file));

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
            'USER_SHOWPROFILE'      => ploopi\crypt::urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showprofile'),
            'USER_SHOWTICKETS'      => ploopi\crypt::urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showtickets'),
            'USER_SHOWFAVORITES'    => ploopi\crypt::urlencode('index.php?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showfavorites'),
            'USER_ADMINISTRATION'   => ploopi\crypt::urlencode('admin.php'.''),
            'USER_DECONNECT'        => ploopi\crypt::urlencode('index.php?ploopi_logout')
        )
    );
}
else
{
    $template_body->assign_block_vars('switch_user_logged_out', array());
    if (!empty($_SESSION['ploopi']['errorcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array('MESSAGE' => isset($ploopi_errormsg[$_SESSION['ploopi']['errorcode']]) ? $ploopi_errormsg[$_SESSION['ploopi']['errorcode']] : ''));
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

$ploopi_additional_javascript .= ob_get_contents();
@ob_end_clean();

$lastupdate = ($lastupdate = webedit_getlastupdate()) ? ploopi\date::timestamp2local($lastupdate) : array('date' => '', 'time' => '');

// template assignments
$wsp = self::get_workspace();

list($keywords) = ploopi\str::getwords("{$wsp['title']} {$wsp['meta_keywords']} {$wsp['meta_author']}");

// PLOOPI JS
$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './vendor/components/jquery/jquery.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './vendor/components/jqueryui/jquery-ui.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars(
    'ploopi_js',
    array(
        'PATH' => './vendor/EastDesire/jscolor/jscolor.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$title_raw = $wsp['title'];
$title = ploopi\str::htmlentities($title_raw);

// Doit on afficher le flux du site ?
if (isset($arrHeadings['feed_enabled']) && $arrHeadings['feed_enabled'])
{
    $template_body->assign_block_vars(
        'switch_atomfeed_site',
        array(
            'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=atom", webedit_getrewriterules(), $title_raw), // ploopi\str::urlrewrite("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom", webedit_getrewriterules(), 'titre'),  //ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom", null, null, null, null, false),
            'TITLE' => $title,
        )
    );
    $template_body->assign_block_vars(
        'switch_rssfeed_site',
        array(
            'URL' => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&format=rss", webedit_getrewriterules(), $title_raw), //ploopi\crypt::urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss", null, null, null, null, false),
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
        'ADDITIONAL_JAVASCRIPT'         => $ploopi_additional_javascript,
        'ADDITIONAL_HEAD'               => $ploopi_additional_head,
        'SITE_TITLE'                    => $title,
        'SITE_TITLE_RAW'                => $title_raw,
        'WORKSPACE_TITLE'               => $title,
        'WORKSPACE_TITLE_RAW'           => $title_raw,
        'WORKSPACE_META_DESCRIPTION'    => ploopi\str::htmlentities($wsp['meta_description']),
        'WORKSPACE_META_KEYWORDS'       => ploopi\str::htmlentities($wsp['meta_keywords']),
        'WORKSPACE_META_AUTHOR'         => ploopi\str::htmlentities($wsp['meta_author']),
        'WORKSPACE_META_COPYRIGHT'      => ploopi\str::htmlentities($wsp['meta_copyright']),
        'WORKSPACE_META_ROBOTS'         => ploopi\str::htmlentities($wsp['meta_robots']),
        'UNIQUE_KEYWORDS'               => ploopi\str::htmlentities(implode(', ', array_keys($keywords))),
        'PAGE_QUERYSTRING'              => ploopi\str::htmlentities($query_string),
        'PAGE_QUERYDATE_B'              => ploopi\str::htmlentities($query_date_b),
        'PAGE_QUERYDATE_E'              => ploopi\str::htmlentities($query_date_e),
        'PAGE_QUERYMT'                  => ploopi\str::htmlentities($query_mime_type),
        'PAGE_QUERYCT_ALL_SELECTED'     => $query_content_type == 'all' ? 'selected="selected"' : '',
        'PAGE_QUERYCT_ART_SELECTED'     => $query_content_type == 'art' ? 'selected="selected"' : '',
        'PAGE_QUERYCT_DOC_SELECTED'     => $query_content_type == 'doc' ? 'selected="selected"' : '',
        'PAGE_QUERYTAG'                 => ploopi\str::htmlentities($query_tag),
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
        'PLOOPI_REVISION'               => _PLOOPI_REVISION,
        'URL_XML_TAG3D'                 => ploopi\str::urlrewrite("index.php?ploopi_op=webedit_backend&query_tag=".((!empty($query_tag)) ? $query_tag : 'tag3D')."&moduleid={$_SESSION['ploopi']['workspaceid']}", webedit_getrewriterules())
    )
);

/**
 * Génération du nuage de tags en fonction des articles publiés
 */
$sql =  "
        SELECT      t.tag, a.id_heading
        FROM        ploopi_mod_webedit_tag t

        INNER JOIN  ploopi_mod_webedit_article_tag at
        ON          at.id_tag = t.id

        INNER JOIN  ploopi_mod_webedit_article a
        ON          at.id_article = a.id

        WHERE       t.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         (a.timestp_published <= $today OR a.timestp_published = 0)
        AND         (a.timestp_unpublished >= $today OR a.timestp_unpublished = 0)
        ";


ploopi\db::get()->query($sql);

$arrTags = array();

while ($row = ploopi\db::get()->fetchrow())
{
    // ATTENTION EN CAS DE CHANGEMENT DE FILTRE, NE PAS OUBLIER LES TAG 3D DANS BACKEND.PHP
    if (!$arrHeadings['list'][$row['id_heading']]['private']
        || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']])
        || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$row['id_heading']]['herited_private']])
        || $webedit_mode == 'edit') // Rubrique non privée ou accessible par l'utilisateur
    {
        $strTag = strtolower(ploopi\str::convertaccents($row['tag']));
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
            $link = ploopi\str::urlrewrite("index.php?query_tag={$strTag}", webedit_getrewriterules());
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

// En mode "BLOG" on va proposer le calendrier et les archives
if(isset($arrHeadings['list'][$headingid]['content_type']) && $arrHeadings['list'][$headingid]['content_type'] == 'blog')
{
    // Calendrier, mois
    $template_body->assign_block_vars('switch_blog', array());

    //Tableau des mois avec des articles (+ nb article/mois)
    $arrArticleBlogByMonth = array();

    // Date d'article maximum
    $maxTimeStpArt = '19000101000000';
    // Date d'article minimum
    $minTimeStpArt = date('Ymd').'235959';

    // CALENDRIER
    include_once './modules/webedit/class_calendar_blog.php';

    $objCalendarBlog = new webedit_calendar_blog(150 , 100, 'month',
        array(
            'class_name' => 'ploopi_calendar_blog',
            'headingid' => $headingid
        )
    );

    // la requete a déjà été effectuée dans l'affichage "blog" ou "page" plus haut
    if(isset($resSQL) && ploopi\db::get()->numrows($resSQL))
    {
        ploopi\db::get()->dataseek($resSQL); // Repositionne au debut;

        while ($row = ploopi\db::get()->fetchrow($resSQL))
        {
            $arrDate = ploopi\date::gettimestampdetail($row['timestp']);

            $year       = $arrDate[1];
            $month      = $arrDate[2];
            $day        = $arrDate[3];
            $date       = $arrDate[1].$arrDate[2].$arrDate[3];
            $yearmonth  = $arrDate[1].$arrDate[2];
            unset($arrDate);

            if($maxTimeStpArt < $row['timestp']) $maxTimeStpArt = $row['timestp'];
            if($minTimeStpArt > $row['timestp']) $minTimeStpArt = $row['timestp'];

            if($webedit_mode == 'render')
            {
                $scriptArchive = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&yearmonth={$yearmonth}";
                $scriptDate = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&yearmonth={$yearmonth}&day={$day}";
            }
            else
            {
                $arrParents = array();
                if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                $arrParents[] = $year;
                $arrParents[] = $month;
                $scriptArchive = ploopi\str::urlrewrite("index.php?headingid={$headingid}&yearmonth={$yearmonth}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label'], $arrParents);

                $arrParents[] = $day;
                $scriptDate = ploopi\str::urlrewrite("index.php?headingid={$headingid}&yearmonth={$yearmonth}&day={$day}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label'], $arrParents);
            }

            // ARCHIVES
            // On recup au passage, les dates des articles pour "calendrier" et "mois" et on passe l'url correspondante
            if(isset($arrArticleBlogArchive[$year]) && isset($arrArticleBlogArchive[$year][$month]))
                $arrArticleBlogArchive[$year][$month]['nbArt'] = $arrArticleBlogArchive[$year][$month]['nbArt']+1;
            else
            {
                if(!isset($arrArticleBlogArchive[$year])) $arrArticleBlogArchive[$year] = array();
                $arrArticleBlogArchive[$year][$month] = array('nbArt' => 1, 'url' => $scriptArchive);
            }
            // CALENDRIER
            // On passe les jours où il y a des articles
            $objCalendarBlog->addevent(
                new ploopi\calendarEvent(
                    $date.'000000',
                    $date.'000000',
                    $row['metatitle'],
                    '',
                    null,
                    array('strHref' => $scriptDate)
                )
            );
        }

        // CALENDRIER
        if($maxTimeStpArt<$minTimeStpArt) $minTimeStpArt = $maxTimeStpArt = date('Ymd').'000000';
        $maxTimeStpArt = substr($maxTimeStpArt,0,6);
        $minTimeStpArt = substr($minTimeStpArt,0,6);

        // Controle si la date dans le calendrier ne va pas etre supérieur à la date de dernier article pour mémoriser en session
        if(($arrSessionBlog['year'].$arrSessionBlog['month']) > $maxTimeStpArt)
        {
            $arrSessionBlog['year'] = substr($maxTimeStpArt,0,4);
            $arrSessionBlog['month'] = substr($maxTimeStpArt,4,2);
        }

        // Controle si la date dans le calendrier ne va pas etre supérieur à la date de dernier article pour mémoriser en session
        if(($arrSessionBlog['year'].$arrSessionBlog['month']) < $minTimeStpArt)
        {
            $arrSessionBlog['year'] = substr($minTimeStpArt,0,4);
            $arrSessionBlog['month'] = substr($minTimeStpArt,4,2);
        }

        // Mois avant/après
        $intYearMonthPreced =  date("Ym", mktime(0, 0, 0, intval($arrSessionBlog['month'])-1, 1, intval($arrSessionBlog['year'])));
        $intYearMonthNext = date("Ym", mktime(0, 0, 0, intval($arrSessionBlog['month'])+1, 1, intval($arrSessionBlog['year'])));

        // Masque pour le lien lors du clic sur le mois/années du calendrier
        if($webedit_mode == 'render')
        {
            $strUrlCalendarMonthNext = (substr($maxTimeStpArt,0,6) >= $intYearMonthNext) ? "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&yearmonth={$intYearMonthNext}" : '';
            $strUrlCalendarMonthBefore = (substr($minTimeStpArt,0,6) <= $intYearMonthPreced) ? "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&yearmonth={$intYearMonthPreced}" : '';
            $strUrlYear = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&year={$arrSessionBlog['year']}";
        }
        else
        {
            $arrParents = array();
            if (isset($arrHeadings['list'][$headingid])) foreach(preg_split('/;/', $arrHeadings['list'][$headingid]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
            $arrParentsPreced = $arrParents;
            $arrParentsNext = $arrParents;

            $arrParentsPreced[] = substr($intYearMonthPreced,0,4);
            $arrParentsPreced[] = substr($intYearMonthPreced,4,2);

            $arrParentsNext[] = substr($intYearMonthNext,0,4);
            $arrParentsNext[] = substr($intYearMonthNext,4,2);

            $strUrlCalendarMonthNext = (substr($maxTimeStpArt,0,6) >= $intYearMonthNext) ? ploopi\str::urlrewrite("index.php?headingid={$headingid}&yearmonth={$intYearMonthNext}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label'], $arrParentsNext) : '';
            $strUrlCalendarMonthBefore = (substr($minTimeStpArt,0,6) <= $intYearMonthPreced) ? ploopi\str::urlrewrite("index.php?headingid={$headingid}&yearmonth={$intYearMonthPreced}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label'], $arrParentsPreced) : '';

            $strUrlYear = ploopi\str::urlrewrite("index.php?headingid={$headingid}&year={$arrSessionBlog['year']}", webedit_getrewriterules(), $arrHeadings['list'][$headingid]['label'], $arrParents);
        }

        // CALENDRIER
        $objCalendarBlog->setoptions(
            array(
                'month' => $arrSessionBlog['month'],
                'year' => $arrSessionBlog['year'],
                'urlmonthnext' => $strUrlCalendarMonthNext,
                'urlmonthbefore' => $strUrlCalendarMonthBefore,
                'monthnext' => substr($intYearMonthNext,4,2),
                'yearnext' => substr($intYearMonthNext,0,4),
                'monthbefore' => substr($intYearMonthPreced,4,2),
                'yearbefore' => substr($intYearMonthPreced,0,4)
            )
        );

        ob_start();
        $objCalendarBlog->display($headingid);
        $content = ob_get_contents();
        ob_end_clean();

        $template_body->assign_block_vars(
            'switch_blog.calendar',
            array(
                'CONTENT' => $content
            )
        );

        // ARCHIVES
        //krsort($arrArticleBlogByMonth); // la requete le fait déjà :)
        //ploopi\output::print_r($arrArticleBlogArchive);
        foreach($arrArticleBlogArchive as $year => $arrmonth)
        {
            $template_body->assign_block_vars('switch_blog.archive',
                array(
                    'YEAR' => $year
                )
            );

            foreach($arrmonth as $month => $data)
            {
                $template_body->assign_block_vars('switch_blog.archive.month',
                        array(
                            'YEAR'          => $year,
                            'MONTH_NUM'     => intval($month),
                            'MONTH_LETTER'  => $ploopi_months[intval($month)],
                            'MONTH_0NUM'    => sprintf("%02d",$month),
                            'URL'           => $data['url'],
                            'NBART'         => $data['nbArt']
                        )
                    );
            }
        }
    }
}

if($type == 'draft') $rs = ploopi\db::get()->query("SELECT * FROM ploopi_mod_webedit_article_draft WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND id_heading = 0");
else $rs = ploopi\db::get()->query("SELECT * FROM ploopi_mod_webedit_article WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND id_heading = 0");
while ($row = ploopi\db::get()->fetchrow($rs))
{
    $objArticle = new webedit_article();
    $objArticle->fields = $row; // astuce pour pouvoir se servir de webedit_replace_links() !
    $template_body->assign_block_vars($row['title'], array(
        'CONTENT' => preg_replace_callback('/\[\[(.*)\]\]/i','webedit_getobjectcontent', $webedit_mode == 'edit' ? $row['content_cleaned'] : webedit_replace_links($objArticle, $webedit_mode, $arrHeadings))
    ));
}


$template_body->pparse('body');
