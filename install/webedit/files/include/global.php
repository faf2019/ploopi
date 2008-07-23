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
 * Fonctions, constantes, variables globales
 *
 * @package webedit
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

/**
 * Action : Editer/Modifier un article
 */
define ('_WEBEDIT_ACTION_ARTICLE_EDIT',         1);

/**
 * Action : Publier un article
 */
define ('_WEBEDIT_ACTION_ARTICLE_PUBLISH',      2);

/**
 * Action : Editer/Modifier une rubrique/catégorie
 */
define ('_WEBEDIT_ACTION_CATEGORY_EDIT',        3);

/**
 * Gérer les validateurs
 */
define ('_WEBEDIT_ACTION_WORKFLOW_MANAGE',      4);

/**
 * Gérer les abonnés
 */
define ('_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE',   5);

/**
 * Réindexation
 */
define ('_WEBEDIT_ACTION_REINDEX',   9);

/**
 * Objet : ARTICLE (admin)
 */
define ('_WEBEDIT_OBJECT_ARTICLE_ADMIN',        1);

/**
 * Objet : ARTICLE (public)
 */
define ('_WEBEDIT_OBJECT_ARTICLE_PUBLIC',       2);

/**
 * Objet : RUBRIQUE
 */
define ('_WEBEDIT_OBJECT_HEADING',              3);

/**
 * Chemin relatif du dossier de stockage des templates
 */
define ('_WEBEDIT_TEMPLATES_PATH', './templates/frontoffice');


/**
 * Enregistrement d'un abonné : OK
 */
define ('_WEBEDIT_SUBSCRIPTION_SUBSCRIBED', 1);

/**
 * Enregistrement d'un abonné : désabonné
 */
define ('_WEBEDIT_SUBSCRIPTION_UNSUBSCRIBED', 2);

/**
 * Enregistrement d'un abonné : adresse email invalide
 */
define ('_WEBEDIT_SUBSCRIPTION_ERROR_EMAIL', 9);

/**
 * Enregistrement d'un abonné : paramètre incorrect
 */
define ('_WEBEDIT_SUBSCRIPTION_ERROR_PARAM', 99);



/**
 * Statuts d'articles (modifiable, à valider)
 */
global $article_status;
$article_status = array(    'edit' => 'Modifiable',
                            'wait' => 'A Valider'
                        );


/**
 * Types de tris pour les articles
 */
global $heading_sortmodes;
$heading_sortmodes = array( 'bypos' => 'par position croissante',
                            'bydate' => 'par date décroissante',
                            'bydaterev' => 'par date croissante'
                        );



/**
 * Retourne le timestamp (MYSQL) de dernière mise à jour
 *
 * @param int $moduleid identifiant du module
 * @return string timestamp MYSQL
 */

function webedit_getlastupdate($moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    $select =   "
                SELECT      MAX(lastupdate_timestp) as maxtimestp
                FROM        ploopi_mod_webedit_article a
                WHERE       a.id_module = {$moduleid}
                ";

    $db->query($select);

    if ($row = $db->fetchrow()) return($row['maxtimestp']);
    else return(0);
}

/**
 * Retourne un tableau contenant les données nécessaire à l'affiche du treeview
 * 
 * @param string $option option d'affichage qui permet d'adapter le comportement des liens (l'arbre est utilisé de plusieurs manières)
 * @param int $moduleid identifiant du module (optionnel)
 * @return array tableau des données du treeview à afficher
 * 
 * @see skin::display_treeview
 * 
 */

function webedit_gettreeview($option = '', $moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];


    switch($option)
    {
        // déplacement d'un article vers une rubrique
        case 'selectheading':
            $prefix = 'h';
        break;
            
        // redirection depuis une rubrique vers un article
        case 'selectredirect':
            $prefix = 'r';
        break;
        
        // choix d'un article pour faire un lien (fckeditor)
        case 'selectlink':
            $prefix = 'l';
        break;
        
        default:
            $prefix = '';
        break;
    }
    
    
    $treeview = array('list' => array(), 'tree' => array());

    $select =   "
                SELECT      * 
                FROM        ploopi_mod_webedit_heading 
                WHERE       id_module = {$moduleid} 
                ORDER BY    depth, 
                            position
                ";
                            
    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        
        switch($option)
        {
            case 'selectheading':
                $link = '';
                $onclick = "webedit_select_heading('{$fields['id']}', '".addslashes($fields['label'])."', event)";
            break;
            
            case 'selectredirect':
            case 'selectlink':
                $link = '';
                $onclick = "ploopi_skin_treeview_shownode('h{$prefix}{$fields['id']}', '".ploopi_queryencode("ploopi_op=webedit_detail_heading&hid=h{$prefix}{$fields['id']}&option={$option}")."', 'admin-light.php')";
            break;
    
            default:
                $link = ploopi_urlencode("admin.php?headingid={$fields['id']}");
                $onclick = '';
            break;
        }            
        
        $node = 
            array(
                'id' => 'h'.$prefix.$fields['id'],
                'label' => $fields['label'],
                'description' => $fields['description'],
                'parents' => split(';', $fields['parents']),
                'node_link' => '',
                'node_onclick' => "ploopi_skin_treeview_shownode('h{$prefix}{$fields['id']}', '".ploopi_queryencode("ploopi_op=webedit_detail_heading&hid=h{$prefix}{$fields['id']}&option={$option}")."', 'admin-light.php')",
                'link' => $link,
                'onclick' => $onclick,
                'icon' => ($fields['id_heading'] == 0) ? './modules/webedit/img/base.png' : './modules/webedit/img/folder.png'
            );
            
        // on rajoute 'h' devant chaque parent
        foreach($node['parents'] as $key => $value) $node['parents'][$key] = 'h'.$prefix.$value;

        $treeview['list']['h'.$prefix.$fields['id']] = $node;
        
        $treeview['tree']['h'.$prefix.$fields['id_heading']][] = 'h'.$prefix.$fields['id'];                        
    }
    
    if ($option != 'selectheading')
    {
        $select =   "
                    SELECT      ad.id,
                                a.id as online_id,
                                ad.position,
                                ad.title,
                                ad.metadescription,
                                ad.metatitle,
                                ad.id_heading,
                                ad.status,
                                ad.timestp,
                                ad.timestp_published,
                                ad.timestp_unpublished,
                                ad.id_user,
                                MD5(ad.content) as md5_content,
                                MD5(a.content) as md5_online_content
                    FROM        ploopi_mod_webedit_article_draft ad
                    LEFT JOIN   ploopi_mod_webedit_article a
                    ON          a.id = ad.id
    
                    WHERE       ad.id_module = {$moduleid}
                    ORDER BY    ad.position
                    ";
        
        $result = $db->query($select);
        
        $today = ploopi_createtimestamp();
        
        while ($fields = $db->fetchrow($result))
        {
            if (isset($treeview['list']['h'.$prefix.$fields['id_heading']]))
            {
                if (is_null($fields['online_id'])) $fields['new_version'] = 2;
                else $fields['new_version'] = ($fields['md5_content'] !=  $fields['md5_online_content']) ? '1' : '0';
        
                $fields['date_ok'] = (($fields['timestp_published'] <= $today || $fields['timestp_published'] == 0) && ($fields['timestp_unpublished'] >= $today || $fields['timestp_unpublished'] == 0));
                
                switch($option)
                {
                    // used for fckeditor and link redirect on heading
                    case 'selectredirect':
                        $link = '';
                        $onclick = "webedit_select_article('{$fields['id']}', '".addslashes($fields['title'])."', event)";
                    break;
    
                    case 'selectlink':
                        $link = '';
                        $onclick = "ploopi_getelem('txtArticle',parent.document).value='".ploopi_urlrewrite("index.php?headingid={$fields['id_heading']}&articleid={$fields['id']}", $fields['metatitle'])."';ploopi_getelem('txtAttTitle',parent.document).value='".addslashes($fields['title'])."';";
                    break;
    
                    default:
                        $link = ploopi_urlencode("admin.php?headingid={$fields['id_heading']}&op=article_modify&articleid={$fields['id']}");
                        $onclick = '';
                    break;
                }        
                
                $node = 
                    array(
                        'id' => 'a'.$prefix.$fields['id'],
                        'label' => $fields['title'],
                        'description' => $fields['metadescription'],
                        'parents' => array_merge($treeview['list']['h'.$prefix.$fields['id_heading']]['parents'], array('h'.$prefix.$fields['id_heading'])),
                        'node_link' => '',
                        'node_onclick' => '',
                        'link' => $link,
                        'onclick' => $onclick,
                        'icon' => "./modules/webedit/img/doc{$fields['new_version']}.png"
                    );
                    
                $treeview['list']['a'.$prefix.$fields['id']] = $node;
                
                $treeview['tree']['h'.$prefix.$fields['id_heading']][] = 'a'.$prefix.$fields['id'];
            }                        
        }
    }
    
    return($treeview);
}

/**
 * Retourne les rubriques du module sous forme d'un tableau
 *
 * @param int $moduleid identifiant du module
 * @return array tableau de rubriques
 */

function webedit_getheadings($moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    $headings = array('list' => array(), 'tree' => array());

    $select = "SELECT * FROM ploopi_mod_webedit_heading WHERE id_module = {$moduleid} ORDER BY depth, position";
    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        $headings['list'][$fields['id']] = $fields;
        $headings['tree'][$fields['id_heading']][] = $fields['id'];

        $parents = split(';',$headings['list'][$fields['id']]['parents']);
        if (isset($parents[0])) unset($parents[0]);
        $parents[] = $fields['id'];

        $headings['list'][$fields['id']]['nav'] = implode('-',$parents);

        if ($headings['list'][$fields['id']]['template'] == '' && isset($headings['list'][$fields['id_heading']]) && $headings['list'][$fields['id_heading']]['template'] != '')
        {
            $headings['list'][$fields['id']]['template'] = $headings['list'][$fields['id_heading']]['template'];
            $headings['list'][$fields['id']]['herited_template'] = 1;
        }
    }

    return($headings);
}

/**
 * Retourne les articles du module sous forme d'un tableau
 *
 * @param int $moduleid identifiant du module
 * @return array tableau d'articles
 */

function webedit_getarticles($moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];
    $today = ploopi_createtimestamp();

    $articles = array();

    $select =   "
                SELECT      ad.id,
                            a.id as online_id,
                            ad.position,
                            ad.reference,
                            ad.version,
                            ad.title,
                            ad.author,
                            ad.id_heading,
                            ad.status,
                            ad.timestp,
                            ad.timestp_published,
                            ad.timestp_unpublished,
                            ad.id_user,
                            MD5(ad.content) as md5_content,
                            MD5(a.content) as md5_online_content
                FROM        ploopi_mod_webedit_article_draft ad
                LEFT JOIN   ploopi_mod_webedit_article a
                ON          a.id = ad.id

                WHERE       ad.id_module = {$moduleid}
                ORDER BY    ad.position
                ";

    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        /*
         * $fields['similar_text'] = similar_text($fields['content'],$fields['online_content']);
        $fields['length_text'] = strlen($fields['content']);
        * */

        if (is_null($fields['online_id'])) $fields['new_version'] = 2;
        else $fields['new_version'] = ($fields['md5_content'] !=  $fields['md5_online_content']) ? '1' : '0';

        $fields['date_ok'] = (($fields['timestp_published'] <= $today || $fields['timestp_published'] == 0) && ($fields['timestp_unpublished'] >= $today || $fields['timestp_unpublished'] == 0));

        $articles['list'][$fields['id']] = $fields;
        $articles['tree'][$fields['id_heading']][] = $fields['id'];
    }

    return($articles);
}

/**
 * Traduit les rubriques en variables template en fonction de la position dans l'arbre des rubriques
 *
 * @param array $headings tableau contenant les rubriques
 * @param array $nav tableau contenant les rubriques déjà sélectionnées
 * @param int $hid identifiant de la rubrique à afficher
 * @param string $var nom du bloc parent (template)
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign($headings, $nav, $hid, $var = '', $link = '')
{
    global $template_body;
    global $recursive_mode;
    global $webedit_mode;
    
    
    if (isset($headings['tree'][$hid]))
    {
        /*
        if (isset($headings['list'][$hid]))
        {
            if ($headings['list'][$hid]['depth'] == 0) $localvar = "sw_root{$headings['list'][$hid]['position']}";
            else $localvar = "{$var}sw_heading{$headings['list'][$hid]['depth']}";
            
            echo '<br />'.$localvar;

            $template_body->assign_block_vars($localvar , array());
            
        }
        */

        foreach($headings['tree'][$hid] as $id)
        {
            $detail = $headings['list'][$id];

            $depth = $detail['depth'] - 1;
            if ($depth == 0) // root node
            {
                $localvar = "root{$detail['position']}";
            }
            else
            {
                $localvar = "{$var}heading{$depth}";
            }
            $locallink = ($link!='') ? "{$link}-{$id}" : "{$id}";

            switch($webedit_mode)
            {
                case 'edit';
                    $script = "javascript:window.parent.document.location.href='admin.php?headingid={$id}';";
                break;

                case 'render';
                    $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$id}";
                break;

                default:
                case 'display';
                    $script = ploopi_urlrewrite($script = "index.php?headingid={$id}", $detail['label']);
                break;
            }

            $sel = '';

            if (isset($nav[$depth]) && $nav[$depth] == $id)
            {
                $template_body->assign_block_vars('path' , array(
                    'DEPTH' => $depth,
                    'LABEL' => $detail['label'],
                    'LINK' => $script
                    ));

                /* Déprécié : remplacé par le bloc ci-dessous */
                $template_body->assign_var("HEADING{$depth}_TITLE",         $detail['label']);
                $template_body->assign_var("HEADING{$depth}_TITLE",         $detail['label']);
                $template_body->assign_var("HEADING{$depth}_ID",            $id);
                $template_body->assign_var("HEADING{$depth}_POSITION",      $detail['position']);
                $template_body->assign_var("HEADING{$depth}_COLOR",         $detail['color']);
                $template_body->assign_var("HEADING{$depth}_DESCRIPTION",   $detail['description']);
                $template_body->assign_var("HEADING{$depth}_FREE1",         $detail['free1']);
                $template_body->assign_var("HEADING{$depth}_FREE2",         $detail['free2']);
                    
                $template_body->assign_block_vars("switch_heading{$depth}" , array(
                    'DEPTH' => $depth,
                    'ID' => $detail['id'],
                    'LABEL' => $detail['label'],
                    'POSITION' => $detail['position'],
                    'DESCRIPTION' => $detail['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($detail['url_window']) ? 'target="_blank"' : '',
                    'SEL' => $sel,
                    'POSX' => $detail['posx'],
                    'POSY' => $detail['posy'],
                    'COLOR' => $detail['color'],
                    'FREE1' => $detail['free1'],
                    'FREE2' => $detail['free2']
                    ));
                              
                $sel = 'selected';
            }
            
            if ($detail['visible'])
            {
                $template_body->assign_block_vars($localvar , array(
                    'DEPTH' => $depth,
                    'ID' => $detail['id'],
                    'LABEL' => $detail['label'],
                    'POSITION' => $detail['position'],
                    'DESCRIPTION' => $detail['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($detail['url_window']) ? 'target="_blank"' : '',
                    'SEL' => $sel,
                    'POSX' => $detail['posx'],
                    'POSY' => $detail['posy'],
                    'COLOR' => $detail['color'],
                    'FREE1' => $detail['free1'],
                    'FREE2' => $detail['free2']
                    ));

                if ($depth == 0 || (isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
                {
                    if (isset($headings['tree'][$id])) webedit_template_assign(&$headings, &$nav, $id, "{$localvar}.", $locallink);
                }
            }
        }

        if (isset($headings['list'][$hid]))
        {
            $depth = $headings['list'][$hid]['depth'];
            if ($depth > 0  && isset($nav[$depth-1]) && $nav[$depth-1] == $hid && !(isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
            {
                if ($link!='' && isset($nav[$depth])) $link .= "-$nav[$depth]";
                elseif (isset($nav[$depth])) $link = "$nav[$depth]";

                if (isset($nav[$depth]) && isset($headings['tree'][$nav[$depth]])) webedit_template_assign(&$headings, &$nav, $nav[$depth], '', $link);
            }
        }

    }
}


/**
 * Traduit les rubriques en variables template pour le contenu d'une page
 *
 * @param array $headings tableau contenant les rubriques
 * @param int $hid identifiant de la rubrique à afficher
 * @param string $var nom du bloc parent (template)
 * @param string $prefix préfixe pour le nommage des blocs
 * @param int $depth profondeur relative de la rubrique
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign_headings($headings, $hid, $var = 'switch_content_heading.', $prefix = 'subheading', $depth = 1, $link = '')
{
    global $template_body;
    global $webedit_mode;
    
    
    if (isset($headings['tree'][$hid]))
    {
        foreach($headings['tree'][$hid] as $id)
        {
            $detail = $headings['list'][$id];

            $localvar = "{$var}{$prefix}{$depth}";

            $locallink = ($link!='') ? "{$link}-{$id}" : "{$id}";

            switch($webedit_mode)
            {
                case 'edit';
                    $script = "javascript:window.parent.document.location.href='admin.php?headingid={$id}';";
                break;

                case 'render';
                    $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$id}";
                break;

                default:
                case 'display';
                    $script = ploopi_urlrewrite($script = "index.php?headingid={$id}", $detail['label']);
                break;
            }

            if ($detail['visible'])
            {
                $template_body->assign_block_vars($localvar , array(
                    'DEPTH' => $depth,
                    'ID' => $detail['id'],
                    'LABEL' => $detail['label'],
                    'POSITION' => $detail['position'],
                    'DESCRIPTION' => $detail['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($detail['url_window']) ? 'target="_blank"' : '',
                    'POSX' => $detail['posx'],
                    'POSY' => $detail['posy'],
                    'COLOR' => $detail['color'],
                    'FREE1' => $detail['free1'],
                    'FREE2' => $detail['free2']
                    ));
                    
                if (isset($headings['tree'][$id])) webedit_template_assign_headings(&$headings, $id, "{$localvar}.", $prefix, $depth+1, $locallink);
            }
        }
    }
}

/**
 * Retourne les templates frontoffice dans un tableau
 *
 * @return array tableau indexé contenant la liste triée des templates
 */

function webedit_gettemplates()
{
    clearstatcache();
    //$rootdir = './modules/webedit/templates';

    $webedit_templates = array();
    $pdir = @opendir(_WEBEDIT_TEMPLATES_PATH);

    while ($tpl = @readdir($pdir))
    {
        if ((substr($tpl, 0, 1) != '.') && is_dir(_WEBEDIT_TEMPLATES_PATH."/{$tpl}"))
        {
            $webedit_templates[] = $tpl;
        }
    }

    sort($webedit_templates);
    
    return($webedit_templates);
}

/**
 * Gère l'insertion des objets dans le contenu d'une page.
 * Cette fonction est appelée par la fonction php preg_replace_callback/
 *
 * @param array $matches tableau contenant les correspondances par rapport à l'expression régulière utilisée par la fonction appelante
 * @return string contenu modifié
 * 
 * @see preg_replace_callback
 */

function webedit_getobjectcontent($matches)
{
    global $db;

    $content = '';

    if (!empty($matches[1]))
    {
        $key = split('/',$matches[1]);
        $id_object = split(',',$key[0]);

        if (sizeof($id_object) == 2 || sizeof($id_object) == 3) // normal size !
        {
            $module_id_cms = $id_object[1];

            $queryobj = "SELECT * FROM ploopi_mb_wce_object WHERE id={$id_object[0]}";

            $resobj = $db->query($queryobj);
            if($obj = $db->fetchrow($resobj))
            {
                $obj['module_id'] = $module_id_cms;
                if (isset($id_object[2])) $obj['object_id'] = $id_object[2];

                $tab = explode("&",trim($obj['script'],"?"));

                foreach ($tab as $key => $value) eval("$".$value.";");

                ob_start();
                include "./modules/".$_SESSION['ploopi']['modules'][$obj['module_id']]['moduletype']."/wce.php";
                $content .= ob_get_contents();
                ob_end_clean();
            }
        }
    }
    return($content);
}

/**
 * Fonction permettant au moteur de recherche global de vérifier l'accessibilité d'un enregistrement d'un objet par un utilisateur.
 * Chaque module peut disposer d'un fonction [module_name]_record_isenabled($id_object, $id_record, $id_module)
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @return boolean true si l'enregistrement est accessible
 */

function webedit_record_isenabled($id_object, $id_record, $id_module)
{
    $enabled = false;

    switch($id_object)
    {
        case _WEBEDIT_OBJECT_ARTICLE_PUBLIC;
            include_once './modules/webedit/class_article.php';

            $article = new webedit_article();
            if ($article->open($id_record)) return($article->isenabled());
        break;

        case _WEBEDIT_ACTION_ARTICLE_EDIT;
            //if (ploopi_isactionallowed(-1,$_SESSION['ploopi']['workspaceid'],$menu_moduleid))
            $enabled = true;
        break;
        
    }

    return($enabled);
}
?>
