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

global $template_body;
global $template_path;
global $webedit_mode;

$today = ploopi_createtimestamp();

$type = (empty($_GET['type'])) ? '' : $_GET['type'];
$webedit_mode = (empty($_GET['webedit_mode'])) ? 'display' : $_GET['webedit_mode'];
$readonly = (empty($_GET['readonly'])) ? 0 : $_GET['readonly'];

$articleid = (!empty($_REQUEST['articleid'])) ? $_REQUEST['articleid'] : '';
$headingid = (!empty($_REQUEST['headingid'])) ? $_REQUEST['headingid'] : '';

$code_erreur = 0;

// vérification des paramètres
if ($webedit_mode == 'edit' && !ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT)) $webedit_mode = 'display';

if ($webedit_mode == 'render' || $webedit_mode == 'display')
{
    $readonly = 1;
    $type = '';
}

// requête de recherche
$query_string = (empty($_REQUEST['query_string'])) ? '' : $_REQUEST['query_string'];

// requête sur un tag
$query_tag = (empty($_REQUEST['query_tag'])) ? '' : $_REQUEST['query_tag'];

$headings = webedit_getheadings();

if ($query_string != '') // Recherche intégrale
{
    $headingid = $headings['tree'][0][0];    
}
elseif ($query_tag != '') // Recherche par tag
{
    $headingid = $headings['tree'][0][0];    
}
else // affichage standard rubrique/page
{
    // on recherche d'abord la rubrique (qui détermine le template)
    // en fonction des parametres articleid et headingid
    
    if (empty($articleid)) // pas de lien vers un article
    {
        // homepage
        if (empty($headingid)) $headingid = $headings['tree'][0][0];
        else // accès par une rubrique
        {
            // rubrique inconnue
            if (!isset($headings['list'][$headingid]))
            {
                // renvoi à la racine
                $headingid = $headings['tree'][0][0];
                $code_erreur = 404;
                ploopi_h404();
            }
        }
        
        switch($headings['list'][$headingid]['content_type'])
        {
            case 'article_redirect':
                if ($headings['list'][$headingid]['linkedpage'])
                {
                    $article = new webedit_article($type);
                    if (    $article->open($headings['list'][$headingid]['linkedpage'])
                        &&  ($article->fields['timestp_published'] <= $today || $article->fields['timestp_published'] == 0)
                        &&  ($article->fields['timestp_unpublished'] >= $today || $article->fields['timestp_unpublished'] == 0)
                        )
                    {
                        $articleid = $headings['list'][$headingid]['linkedpage'];
                        $headingid = $article->fields['id_heading'];
                    }
                }
            break;
                
            case 'url_redirect':
                if (!empty($headings['list'][$headingid]['url'])) ploopi_redirect($headings['list'][$headingid]['url'], false, false);
            break;
            
            
            case 'article_first': 
                // Cas standard, traité plus bas
            case 'headings':
                // Traité à l'affichage
            break;
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
                $headingid = $headings['tree'][0][0];
                $code_erreur = 404;
                ploopi_h404();
            }
        }
        else
        {
            if ($webedit_mode != 'edit')
            {
                $article = new webedit_article($type);
                if (!$article->open($articleid) || empty($headings['list'][$headingid]))
                {
                    unset($articleid);
                    // renvoi à la racine
                    $headingid = $headings['tree'][0][0];
                    $code_erreur = 404;
                    ploopi_h404();
                }
            }
        }
    }
}

$nav = $headings['list'][$headingid]['nav'];
$array_nav = explode('-',$nav);

// CHARGEMENT DU TEMPLATE

// get template name
$template_name = (!empty($headings['list'][$headingid]['template'])) ? $headings['list'][$headingid]['template'] : 'default';
if (!file_exists(_WEBEDIT_TEMPLATES_PATH."/$template_name")) $template_name = 'default';

$template_path = _WEBEDIT_TEMPLATES_PATH."/$template_name";

$template_body = new Template($template_path);

// fichier template par défaut
$template_file = 'index.tpl';

if (file_exists("{$template_path}/config.php")) include_once "{$template_path}/config.php";

webedit_template_assign($headings, $array_nav, 0, '', 0);

if ($query_string != '') // recherche intégrale
{
    if (file_exists("./templates/frontoffice/{$template_name}/search.tpl")) $template_file = 'search.tpl';
    
    $template_body->assign_block_vars("switch_search", array());

    $arrRelevance = ploopi_search($query_string, _WEBEDIT_OBJECT_ARTICLE_PUBLIC, '', $_SESSION['ploopi']['moduleid']);
    
    $responses = 0;

    foreach($arrRelevance as $key => $result)
    {
        $objArticle = new webedit_article();
        $intToday = ploopi_createtimestamp();
        if ($objArticle->open($result['id_record']) && $objArticle->isenabled())
        {
            $arrDateArticle = ($objArticle->fields['timestp']!='') ? ploopi_timestamp2local($objArticle->fields['timestp']) : array('date' => '');

            $cleaned_content = strip_tags(html_entity_decode($objArticle->fields['content']));

            $extract = ploopi_highlight($cleaned_content, array_merge(array_keys($result['kw']), array_keys($result['stem'])));
 
            $size = sprintf("%.02f", strlen($cleaned_content)/1024);

            $script = ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$result['id_record']}", $objArticle->fields['metatitle']);

            $template_body->assign_block_vars('switch_search.result',
                array(
                    'RELEVANCE' => sprintf("%.02f", $result['relevance']),
                    'TITLE' => htmlentities($objArticle->fields['title']),
                    'AUTHOR' => htmlentities($objArticle->fields['author']),
                    'EXTRACT' => $extract,
                    'METATITLE' => htmlentities($objArticle->fields['metatitle']),
                    'METAKEYWORDS' => htmlentities($objArticle->fields['metakeywords']),
                    'METADESCRIPTION' => htmlentities($objArticle->fields['metadescription']),
                    'DATE' => $arrDateArticle['date'],
                    'SIZE' => $size,
                    'LINK' => $script
                )
            );
            
            $responses++;
        }
    }
    
    if ($responses == 0) // pas de réponse valide !
    {
        $template_body->assign_block_vars('switch_search.switch_notfound',array());
    }
    
    $title = htmlentities("Résultat de la recherche pour \" {$query_string} \"");
    
    $template_body->assign_vars(
        array(
            'SEARCH_RESPONSES' => $responses,
            'PAGE_TITLE' => $title,
            'PAGE_KEYWORDS' => htmlentities($query_string),
            'PAGE_DESCRIPTION' => $title,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_KEYWORDS' => htmlentities($query_string),
            'PAGE_META_DESCRIPTION' => $title
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
            ";
    
    $db->query($sql);
    
    while ($row = $db->fetchrow())
    {
        $size = sprintf("%.02f", strlen(strip_tags(html_entity_decode($row['content'])))/1024);        
        
        $template_body->assign_block_vars('switch_tagsearch.result',
            array(
                'TITLE' => htmlentities($row['title']),
                'AUTHOR' => htmlentities($row['author']),
                'META_TITLE' => htmlentities($row['metatitle']),
                'META_KEYWORDS' => htmlentities($row['metakeywords']),
                'META_DESCRIPTION' => htmlentities($row['metadescription']),
                'DATE' => ($row['timestp']!='') ? current(ploopi_timestamp2local($row['timestp'])) : '',
                'SIZE' => $size,
                'LINK' => ploopi_urlrewrite("index.php?headingid={$row['id_heading']}&articleid={$row['id']}", $row['metatitle'])
            )
        );    
    }    
    
    $title = htmlentities("Liste des articles contenant le tag \" {$query_tag} \"");

    $template_body->assign_vars(
        array(
            'PAGE_TITLE' => $title,
            'PAGE_KEYWORDS' => htmlentities($query_tag),
            'PAGE_DESCRIPTION' => $title,
            'PAGE_META_TITLE' => $title,
            'PAGE_META_KEYWORDS' => htmlentities($query_tag),
            'PAGE_META_DESCRIPTION' => $title
        )
    );
}
else // affichage standard rubrique/page
{
    if($headings['list'][$headingid]['content_type'] == 'headings' && empty($articleid)) // affichage rubriques
    {
       $template_body->assign_block_vars('switch_content_heading', array());
       webedit_template_assign_headings($headings, $headingid);
    }    
    
    if($headings['list'][$headingid]['content_type'] == 'sitemap' && empty($articleid)) // affichage plan de site
    {
       $template_body->assign_block_vars('switch_content_sitemap', array());
       webedit_template_assign_headings($headings, 0, 'switch_content_sitemap.', 'heading', 0);
    }    
    
    // détermination du type de tri des articles
    switch($headings['list'][$headingid]['sortmode'])
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
            if (empty($articleid) && $headings['list'][$headingid]['content_type'] != 'headings' && $headings['list'][$headingid]['content_type'] != 'sitemap') $articleid = $row['id'];
    
            if ($row['visible'])
            {
                $numvisart++;
    
                switch($webedit_mode)
                {
                    case 'edit';
                        $script = "javascript:window.parent.document.location.href='admin.php?op=article_modify&headingid={$headingid}&articleid={$row['id']}';";
                    break;
    
                    case 'render';
                        $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&articleid={$row['id']}";
                        //$script = "admin.php?nav={$nav}&articleid={$row['id']}";
                    break;
    
                    default:
                    case 'display';
                        $script = ploopi_urlrewrite("index.php?headingid={$headingid}&articleid={$row['id']}", $row['metatitle']);
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
                        'CONTENT'       => htmlentities($row['content']),
                        'AUTHOR'        => htmlentities($row['author']),
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
                
                if ($headings['list'][$headingid]['content_type'] == 'headings' && empty($articleid)) // affichage rubriques
                {
                    $template_body->assign_block_vars('switch_content_heading.page', $var_tpl_page);
                }
                
    
                if ($numvisart < $nbvisart) $template_body->assign_block_vars('switch_pages.page.sw_separator',array());
            }
        }
    }
    
    $ishomepage = false;
    
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
                $ishomepage = 
                    (
                        !empty($headingid) && !empty($articleid)
                        &&  
                            (
                                (   $article->fields['position'] == 1 
                                    &&  $headings['list'][$headingid]['depth'] == 1 
                                    &&  $headings['list'][$headingid]['position'] == 1 
                                    &&  empty($headings['list'][$headingid]['linkedpage'])
                                ) 
                                || $headings['list'][$headings['tree'][0][0]]['linkedpage'] == $articleid
                            )
                    );
            }
            else
            {
                $article->init_description();
                $article->fields['content'] = 'ERREUR 404 - Article non trouvé';
                $code_erreur = 404;
                ploopi_h404();
            }
        }

        if ($webedit_mode == 'edit')
        {
            if (!$readonly)
            {
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
                $oFCKeditor->Config['SkinPath'] = _PLOOPI_BASEPATH.'/modules/webedit/fckeditor/skins/default/';
                
                if (file_exists("{$template_path}/fckeditor/fck_editorarea.css")) $oFCKeditor->Config['EditorAreaCSS'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fck_editorarea.css';

                if (file_exists("{$template_path}/fckeditor/fcktemplates.xml")) $oFCKeditor->Config['TemplatesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fcktemplates.xml';

                if (file_exists("{$template_path}/fckeditor/fckstyles.xml")) $oFCKeditor->Config['StylesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fckstyles.xml';

                $oFCKeditor->ToolbarSet = 'Default';

                // render
                $oFCKeditor->Create('FCKeditor_1') ;

                $editor = ob_get_contents();
                ob_end_clean();
            }
        }

        $template_body->assign_block_vars('switch_content_page', array());

        if (!empty($editor)) $content = $editor;
        else $content = preg_replace_callback('/\[\[(.*)\]\]/i','webedit_getobjectcontent',$article->fields['content']);

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
        //list($allkeywords) = ploopi_getwords("{$article->fields['metatitle']} {$article->fields['metakeywords']} {$article->fields['author']} {$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']} {$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_keywords']} {$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']}");

        $template_body->assign_vars(
            array(
                'PAGE_REFERENCE' => htmlentities($article->fields['reference']),
                'PAGE_TITLE' => htmlentities($article->fields['title']),
                'PAGE_KEYWORDS' => htmlentities(implode(', ', array_keys($keywords))),
                'PAGE_DESCRIPTION' => htmlentities($article->fields['metadescription']),
                'PAGE_META_TITLE' => htmlentities($article->fields['metatitle']),
                'PAGE_META_KEYWORDS' => htmlentities(implode(', ', array_keys($keywords))),
                'PAGE_META_DESCRIPTION' => htmlentities($article->fields['metadescription']),
                'PAGE_TITLE_FAVORITES'  => addslashes($article->fields['title']),
                'PAGE_AUTHOR' => htmlentities($article->fields['author']),
                'PAGE_VERSION' => htmlentities($article->fields['version']),
                'PAGE_DATE' => htmlentities($article_timestp['date']),
                'PAGE_LASTUPDATE_DATE' => htmlentities($article_lastupdate['date']),
                'PAGE_LASTUPDATE_TIME' => htmlentities($article_lastupdate['time']),
                'PAGE_LASTUPDATE_USER_LASTNAME' => htmlentities($user_lastname),
                'PAGE_LASTUPDATE_USER_FIRSTNAME' => htmlentities($user_firstname),
                'PAGE_LASTUPDATE_USER_LOGIN' => htmlentities($user_login),
                'PAGE_CONTENT' => $content
            )
        );
                                
        
        $tags = $article->gettags();
        if (!empty($tags))
        {
            $template_body->assign_block_vars('switch_content_page.switch_tags', array());
            
            foreach($tags as $tag)
            {
                $template_body->assign_block_vars('switch_content_page.switch_tags.tag', 
                    array(
                        'TAG' => $tag['tag'],
                        'LINK' => ploopi_urlrewrite("index.php?query_tag={$tag['tag']}")
                    )
                );
            }
        }
    }
    else 
    {
        // pas d'article par défaut, on teste si on est sur la rubrique d'accueil
        
        $ishomepage = (!empty($headingid) && $headings['tree'][0][0] == $headingid);
    }
        
    // Doit on afficher le flux de la rubrique ?
    if (!$ishomepage && isset($headings['list'][$headingid]) && $headings['list'][$headingid]['feed_enabled'])
    {
        $template_body->assign_block_vars(
            'switch_atomfeed_heading', 
            array(
                'URL' => ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&format=atom"),
                'TITLE' => htmlentities($headings['list'][$headingid]['label']),
            )
        );
        
        $template_body->assign_block_vars(
            'switch_rssfeed_heading', 
            array(
                'URL' => ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$headingid}&format=rss"),
                'TITLE' => htmlentities($headings['list'][$headingid]['label']),
            )
        );
    }    

    // Doit on autoriser les abonnements ?
    if (isset($headings['list'][$headingid]) && $headings['list'][$headingid]['subscription_enabled'])
    {
        $template_body->assign_block_vars(
            'switch_subscription', 
            array(
                'ACTION' => ploopi_urlencode("index.php?ploopi_op=webedit_subscribe&headingid={$headingid}".(empty($articleid) ? '' : "&articleid={$articleid}")),
                'HEADINGID' => $headingid,
                'ROOTID' => $headings['tree'][0][0]
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
    if ($ishomepage && file_exists("./templates/frontoffice/{$template_name}/home.tpl")) $template_file = 'home.tpl';
}

// load a specific template file in edition mode (if exists)
if ($webedit_mode == 'edit' && file_exists("./templates/frontoffice/{$template_name}/fck_{$template_file}")) $template_file = "fck_{$template_file}";

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
    <?
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
        'PATH' => './lib/protoculous/protoculous-packer.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);
    
$template_body->assign_block_vars(
    'ploopi_js', 
    array(
        'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

// Doit on afficher le flux du site ?
if (isset($headings['list'][$headings['tree'][0][0]]) && $headings['list'][$headings['tree'][0][0]]['feed_enabled'])
{
    $template_body->assign_block_vars(
        'switch_atomfeed_site', 
        array(
            'URL' => ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=atom"),
            'TITLE' => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
        )
    );
    $template_body->assign_block_vars(
        'switch_rssfeed_site', 
        array(
            'URL' => ploopi_urlencode("backend.php?ploopi_moduleid={$_SESSION['ploopi']['moduleid']}&format=rss"),
            'TITLE' => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
        )
    );
}

$template_body->assign_vars(
    array(
        'TEMPLATE_PATH'                 => $template_path,
        'ADDITIONAL_JAVASCRIPT'         => $additional_javascript,
        'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
        'SITE_TITLE'                    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
        'WORKSPACE_TITLE'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
        'WORKSPACE_META_DESCRIPTION'    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description']),
        'WORKSPACE_META_KEYWORDS'       => implode(', ', array_keys($keywords)),
        'WORKSPACE_META_AUTHOR'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']),
        'WORKSPACE_META_COPYRIGHT'      => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright']),
        'WORKSPACE_META_ROBOTS'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_robots']),
        'PAGE_QUERYSTRING'              => $query_string,
        'PAGE_QUERYTAG'                 => $query_tag,
        'NAV'                           => $nav,
        'HOST'                          => $_SERVER['HTTP_HOST'],
        'DATE_DAY'                      => date('d'),
        'DATE_MONTH'                    => date('m'),
        'DATE_YEAR'                     => date('Y'),
        'DATE_DAYTEXT'                  => $ploopi_agenda_days[date('w')],
        'DATE_MONTHTEXT'                => $ploopi_agenda_months[date('n')],
        'LASTUPDATE_DATE'               => $lastupdate['date'],
        'LASTUPDATE_TIME'               => $lastupdate['time']
    )
);

/**
 * Génération du nuage de tags
 */

$sql =  "
        SELECT      t.*, count(*) as nb 
        FROM        ploopi_mod_webedit_tag t
        
        INNER JOIN  ploopi_mod_webedit_article_tag at
        ON          at.id_tag = t.id
        
        WHERE       t.id_module = {$_SESSION['ploopi']['moduleid']}
        
        GROUP BY    t.id
        ORDER BY    nb DESC
        ";

$db->query($sql);

$max = 0;
$tags = array();
$min_size = 50;
while ($row = $db->fetchrow())
{
    if (empty($max)) $max = $row['nb'];
    $tags[$row['tag']] = $row;
    $tags[$row['tag']]['size'] = round(100 * $row['nb'] / $max);
    if ($tags[$row['tag']]['size'] < $min_size) $tags[$row['tag']]['size'] = $min_size;
}

ksort($tags);

foreach ($tags as $tag)
{
    $template_body->assign_block_vars('tagcloud' , array(
                        'TAG' => $tag['tag'],
                        'SIZE' => $tag['size'],
                        'OCCURENCE' => $tag['nb'],
                        'LINK' => ploopi_urlrewrite("index.php?query_tag={$tag['tag']}"),
                        'SELECTED' => ($tag['tag'] == $query_tag) ? 'selected' : ''
                        )
                    );
}                            

$template_body->pparse('body');
?>
