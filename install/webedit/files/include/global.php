<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
    Copyright (c) 2009 HeXad
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
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * D�finition des constantes
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
 * Action : Editer/Modifier une rubrique/cat�gorie
 */
define ('_WEBEDIT_ACTION_CATEGORY_EDIT',        3);

/**
 * G�rer les validateurs
 */
define ('_WEBEDIT_ACTION_WORKFLOW_MANAGE',      4);

/**
 * G�rer les abonn�s
 */
define ('_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE',   5);

/**
 * G�rer les autorisations d'acc�s
 */
define ('_WEBEDIT_ACTION_ACCESS_MANAGE',   6);

/**
 * Stats
 */
define ('_WEBEDIT_ACTION_STATS',   8);

/**
 * R�indexation
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
 * Enregistrement d'un abonn� : OK
 */
define ('_WEBEDIT_SUBSCRIPTION_SUBSCRIBED', 1);

/**
 * Enregistrement d'un abonn� : d�sabonn�
 */
define ('_WEBEDIT_SUBSCRIPTION_UNSUBSCRIBED', 2);

/**
 * Enregistrement d'un abonn� : adresse email invalide
 */
define ('_WEBEDIT_SUBSCRIPTION_ERROR_EMAIL', 9);

/**
 * Enregistrement d'un abonn� : param�tre incorrect
 */
define ('_WEBEDIT_SUBSCRIPTION_ERROR_PARAM', 99);

/**
 * Statuts d'articles (modifiable, � valider)
 */
global $article_status;
$article_status =
    array(
        'edit' => 'Modifiable',
        'wait' => 'A Valider'
    );

/**
 * Types de tris pour les articles
 */
global $heading_sortmodes;
$heading_sortmodes =
    array(
        'bypos' => 'par position croissante',
        'bydate' => 'par date d�croissante',
        'bydaterev' => 'par date croissante'
    );

/**
 * Retourne le timestamp (MYSQL) de derni�re mise � jour
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
 * Retourne un tableau contenant les donn�es n�cessaire � l'affiche du treeview
 *
 * @param string $option option d'affichage qui permet d'adapter le comportement des liens (l'arbre est utilis� de plusieurs mani�res)
 * @param int $moduleid identifiant du module (optionnel)
 * @return array tableau des donn�es du treeview � afficher
 *
 * @see skin::display_treeview
 *
 */

function webedit_gettreeview($arrHeadings = array(), $articles = array(), $option = '', $moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    switch($option)
    {
        // d�placement d'un article vers une rubrique
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

    if (!empty($arrHeadings['list']))
    {
        foreach($arrHeadings['list'] as $id => $fields)
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
    }

    if ($option != 'selectheading')
    {
        $today = ploopi_createtimestamp();

        if (!empty($articles['list']))
        {
            foreach($articles['list'] as $id => $fields)
            {
                if (isset($treeview['list']['h'.$prefix.$fields['id_heading']]))
                {
                    $status = ($fields['status'] == 'wait') ? '<sup style="margin-left:2px;color:#ff0000;font-weight:bold;">*</sup>' : '';
                    $dateok = ($fields['date_ok']) ? '' : '<sup style="margin-left:2px;color:#ff0000;font-weight:bold;">~</sup>';

                    switch($option)
                    {
                        // used for fckeditor and link redirect on heading
                        case 'selectredirect':
                            $link = '';
                            $onclick = "webedit_select_article('{$fields['id']}', '".addslashes($fields['title'])."', event)";
                        break;

                        case 'selectlink':
                            $link = '';
                            $onclick = "ploopi_getelem('txtArticle',parent.document).value='index.php?headingid={$fields['id_heading']}&articleid={$fields['id']}';ploopi_getelem('txtAttTitle',parent.document).value='".addslashes($fields['title'])."';";
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
                            'status' => $status.$dateok,
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

    $arrHeadings = array('list' => array(), 'tree' => array(), 'feed_enabled' => false, 'subscription_enabled' => false);

    $select = "SELECT * FROM ploopi_mod_webedit_heading WHERE id_module = {$moduleid} ORDER BY depth, position";
    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        $arrHeadings['list'][$fields['id']] = $fields;
        $arrHeadings['tree'][$fields['id_heading']][] = $fields['id'];

        $parents = split(';',$arrHeadings['list'][$fields['id']]['parents']);
        if (isset($parents[0])) unset($parents[0]);
        $parents[] = $fields['id'];

        $arrHeadings['list'][$fields['id']]['nav'] = implode('-',$parents);

        if ($arrHeadings['list'][$fields['id']]['template'] == '' && isset($arrHeadings['list'][$fields['id_heading']]) && $arrHeadings['list'][$fields['id_heading']]['template'] != '')
        {
            $arrHeadings['list'][$fields['id']]['template'] = $arrHeadings['list'][$fields['id_heading']]['template'];
            $arrHeadings['list'][$fields['id']]['herited_template'] = 1;
        }

        if ($arrHeadings['list'][$fields['id']]['private']) $arrHeadings['list'][$fields['id']]['herited_private'] = $fields['id'];

        if (!$arrHeadings['list'][$fields['id']]['private'] && isset($arrHeadings['list'][$fields['id_heading']]) && $arrHeadings['list'][$fields['id_heading']]['private'])
        {
            $arrHeadings['list'][$fields['id']]['private'] = 1;
            $arrHeadings['list'][$fields['id']]['private_visible'] = $arrHeadings['list'][$fields['id_heading']]['private_visible'];
            $arrHeadings['list'][$fields['id']]['herited_private'] = $arrHeadings['list'][$fields['id_heading']]['herited_private'];
        }

        // Il suffit qu'une rubrique active le flux pour que le flux soit �galement activ� sur le site global (en respectant le choix de chaque rubrique)
        if ($fields['feed_enabled'] && !$arrHeadings['feed_enabled']) $arrHeadings['feed_enabled'] = true;

        // Il suffit qu'une rubrique active l'abonnement pour que l'abonnement soit �galement activ� sur le site global (en respectant le choix de chaque rubrique)
        if ($fields['feed_enabled'] && !$arrHeadings['subscription_enabled']) $arrHeadings['feed_enabled'] = true;
    }

    return($arrHeadings);
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

    if (!isset($_SESSION['webedit']['articles'])) $_SESSION['webedit']['articles'] = array();

    $_SESSION['webedit']['articles']['tree'] = array();

    $arrArticles = array();

    $result = $db->query("
        SELECT      ad.id,
                    a.id as online_id,
                    a.id_heading as online_id_heading,
                    ad.position,
                    ad.reference,
                    ad.version,
                    ad.title,
                    ad.metadescription,
                    ad.metatitle,
                    ad.author,
                    ad.id_heading,
                    ad.status,
                    ad.lastupdate_timestp,
                    ad.timestp,
                    ad.timestp_published,
                    ad.timestp_unpublished,
                    ad.id_user,
                    ad.content,
                    a.content as online_content
        FROM        ploopi_mod_webedit_article_draft ad
        LEFT JOIN   ploopi_mod_webedit_article a
        ON          a.id = ad.id

        WHERE       ad.id_module = {$moduleid}
        ORDER BY    ad.position
    ");

    while ($fields = $db->fetchrow($result))
    {
        if (!isset($_SESSION['webedit']['articles']['list'][$fields['id']]) || $fields['lastupdate_timestp'] != $_SESSION['webedit']['articles']['list'][$fields['id']]['lastupdate_timestp'])
        {
            // nouvel article ou article modifi�
            if (is_null($fields['online_id'])) $fields['new_version'] = 2;
            else $fields['new_version'] = (strip_tags($fields['content']) != strip_tags($fields['online_content']) || $fields['id_heading'] != $fields['online_id_heading']) ? '1' : '0';

            $fields['date_ok'] = (($fields['timestp_published'] <= $today || $fields['timestp_published'] == 0) && ($fields['timestp_unpublished'] >= $today || $fields['timestp_unpublished'] == 0));

            unset($fields['content']);
            unset($fields['online_content']);

            $_SESSION['webedit']['articles']['list'][$fields['id']] = $fields;
            $arrArticles[$fields['id']] = $fields;
        }
        else $arrArticles[$fields['id']] = $_SESSION['webedit']['articles']['list'][$fields['id']];

        $_SESSION['webedit']['articles']['list'] = $arrArticles;
        $_SESSION['webedit']['articles']['tree'][$fields['id_heading']][] = $fields['id'];
    }

    return($_SESSION['webedit']['articles']);
}

/**
 * Traduit les rubriques en variables template en fonction de la position dans l'arbre des rubriques
 *
 * @param array $arrHeadings tableau contenant les rubriques
 * @param array $arrShares tableau contenant les partages
 * @param array $nav tableau contenant les rubriques d�j� s�lectionn�es
 * @param int $hid identifiant de la rubrique � afficher
 * @param string $var nom du bloc parent (template)
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign($arrHeadings, $arrShares, &$nav, $hid, $var = '', $link = '')
{
    global $template_body;
    global $recursive_mode;
    global $webedit_mode;

    if (isset($arrHeadings['tree'][$hid]))
    {
        foreach($arrHeadings['tree'][$hid] as $id)
        {
            $arrHeading = $arrHeadings['list'][$id];

            $strHtmlLabel = htmlentities($arrHeading['label']);

            $depth = $arrHeading['depth'] - 1;
            if ($depth == 0) // root node
            {
                $localvar = "root{$arrHeading['position']}";
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
                    $arrParents = array();
                    foreach(split(';', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $script = ploopi_urlrewrite($script = "index.php?headingid={$id}", webedit_getrewriterules(), $arrHeading['label'], $arrParents);
                break;
            }

            $sel = '';

            if (isset($nav[$depth]) && $nav[$depth] == $id)
            {
                $template_body->assign_block_vars('path' , array(
                    'DEPTH' => $depth,
                    'LABEL' => $strHtmlLabel,
                    'LINK' => $script
                    ));

                /* D�pr�ci� : remplac� par le bloc ci-dessous */
                $template_body->assign_var("HEADING{$depth}_TITLE",         $strHtmlLabel);
                $template_body->assign_var("HEADING{$depth}_TITLE_RAW",     $arrHeading['label']);
                $template_body->assign_var("HEADING{$depth}_ID",            $id);
                $template_body->assign_var("HEADING{$depth}_POSITION",      $arrHeading['position']);
                $template_body->assign_var("HEADING{$depth}_COLOR",         $arrHeading['color']);
                $template_body->assign_var("HEADING{$depth}_DESCRIPTION",   $arrHeading['description']);
                $template_body->assign_var("HEADING{$depth}_FREE1",         $arrHeading['free1']);
                $template_body->assign_var("HEADING{$depth}_FREE2",         $arrHeading['free2']);

                $template_body->assign_vars(
                    array(
                        'HEADING_ID' => $arrHeading['id'],
                        'HEADING_DEPTH' => $depth,
                        'HEADING_LABEL' => $strHtmlLabel,
                        'HEADING_LABEL_RAW' => $arrHeading['label'],
                        'HEADING_POSITION' => $arrHeading['position'],
                        'HEADING_DESCRIPTION' => htmlentities($arrHeading['description']),
                        'HEADING_DESCRIPTION_RAW' => $arrHeading['description'],
                        'HEADING_LINK' => $script,
                        'HEADING_LINK_TARGET' => ($arrHeading['url_window']) ? 'target="_blank"' : '',
                        'HEADING_POSX' => $arrHeading['posx'],
                        'HEADING_POSY' => $arrHeading['posy'],
                        'HEADING_COLOR' => $arrHeading['color'],
                        'HEADING_FREE1' => $arrHeading['free1'],
                        'HEADING_FREE2' => $arrHeading['free2']
                    )
                );

                $template_body->assign_block_vars("switch_heading{$depth}" , array(
                    'DEPTH' => $depth,
                    'ID' => $arrHeading['id'],
                    'LABEL' => $strHtmlLabel,
                    'LABEL_RAW' => $arrHeading['label'],
                    'POSITION' => $arrHeading['position'],
                    'DESCRIPTION' => htmlentities($arrHeading['description']),
                    'DESCRIPTION_RAW' => $arrHeading['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($arrHeading['url_window']) ? 'target="_blank"' : '',
                    'SEL' => $sel,
                    'POSX' => $arrHeading['posx'],
                    'POSY' => $arrHeading['posy'],
                    'COLOR' => $arrHeading['color'],
                    'FREE1' => $arrHeading['free1'],
                    'FREE2' => $arrHeading['free2']
                    ));

                $sel = 'selected';
            }

            // Visible ET (Publique OU (Priv�e ET (Autoris� OU Autoris� par un module OU Toujours Visible)))
            if ($arrHeading['visible'] && (!$arrHeadings['list'][$arrHeading['id']]['private'] || ($arrHeadings['list'][$arrHeading['id']]['private'] && (isset($arrShares[$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || $arrHeadings['list'][$arrHeading['id']]['private_visible']))))
            {
                $template_body->assign_block_vars($localvar , array(
                    'DEPTH' => $depth,
                    'ID' => $arrHeading['id'],
                    'LABEL' => $strHtmlLabel,
                    'LABEL_RAW' => $arrHeading['label'],
                    'POSITION' => $arrHeading['position'],
                    'DESCRIPTION' => $arrHeading['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($arrHeading['url_window']) ? 'target="_blank"' : '',
                    'SEL' => $sel,
                    'POSX' => $arrHeading['posx'],
                    'POSY' => $arrHeading['posy'],
                    'COLOR' => $arrHeading['color'],
                    'FREE1' => $arrHeading['free1'],
                    'FREE2' => $arrHeading['free2']
                    ));

                if ($depth == 0 || (isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
                {
                    if (isset($arrHeadings['tree'][$id]))
                    {
                        $template_body->assign_block_vars($localvar.'.switch_submenu' , array());
                        webedit_template_assign(&$arrHeadings, &$arrShares, $nav, $id, "{$localvar}.", $locallink);
                    }
                }
            }
        }

        if (isset($arrHeadings['list'][$hid]))
        {
            $depth = $arrHeadings['list'][$hid]['depth'];
            if ($depth > 0  && isset($nav[$depth-1]) && $nav[$depth-1] == $hid && !(isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
            {
                if ($link!='' && isset($nav[$depth])) $link .= "-$nav[$depth]";
                elseif (isset($nav[$depth])) $link = "$nav[$depth]";

                if (isset($nav[$depth]) && isset($arrHeadings['tree'][$nav[$depth]])) webedit_template_assign(&$arrHeadings, &$arrShares, $nav, $nav[$depth], '', $link);
            }
        }

    }
}

/**
 * Traduit les rubriques en variables template pour le contenu d'une page
 *
 * @param array $arrHeadings tableau contenant les rubriques
 * @param array $arrShares tableau contenant les partages
 * @param int $hid identifiant de la rubrique � afficher
 * @param string $var nom du bloc parent (template)
 * @param string $prefix pr�fixe pour le nommage des blocs
 * @param int $depth profondeur relative de la rubrique
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign_headings($arrHeadings, $arrShares, $hid, $var = 'switch_content_heading.', $prefix = 'subheading', $depth = 1, $link = '')
{
    global $template_body;
    global $webedit_mode;

    if (isset($arrHeadings['tree'][$hid]))
    {
        foreach($arrHeadings['tree'][$hid] as $id)
        {
            $arrHeading = $arrHeadings['list'][$id];

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
                    $arrParents = array();
                    foreach(split(';', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $script = ploopi_urlrewrite($script = "index.php?headingid={$id}", webedit_getrewriterules(), $arrHeading['label'], $arrParents);
                break;
            }

            // Visible ET (Publique OU (Priv�e ET (Autoris� OU Autoris� par un module OU Toujours Visible)))
            if ($arrHeading['visible'] && (!$arrHeadings['list'][$arrHeading['id']]['private'] || ($arrHeadings['list'][$arrHeading['id']]['private'] && (isset($arrShares[$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || $arrHeadings['list'][$arrHeading['id']]['private_visible']))))
            {
                $template_body->assign_block_vars($localvar , array(
                    'DEPTH' => $depth,
                    'ID' => $arrHeading['id'],
                    'LABEL' => $arrHeading['label'],
                    'POSITION' => $arrHeading['position'],
                    'DESCRIPTION' => $arrHeading['description'],
                    'LINK' => $script,
                    'LINK_TARGET' => ($arrHeading['url_window']) ? 'target="_blank"' : '',
                    'POSX' => $arrHeading['posx'],
                    'POSY' => $arrHeading['posy'],
                    'COLOR' => $arrHeading['color'],
                    'FREE1' => $arrHeading['free1'],
                    'FREE2' => $arrHeading['free2']
                    ));

                if (isset($arrHeadings['tree'][$id])) webedit_template_assign_headings(&$arrHeadings, &$arrShares, $id, "{$localvar}.", $prefix, $depth+1, $locallink);
            }
        }
    }
}

/**
 * Retourne les templates frontoffice dans un tableau
 *
 * @return array tableau index� contenant la liste tri�e des templates
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
 * G�re l'insertion des objets dans le contenu d'une page.
 * Cette fonction est appel�e par la fonction php preg_replace_callback/
 *
 * @param array $matches tableau contenant les correspondances par rapport � l'expression r�guli�re utilis�e par la fonction appelante
 * @return string contenu modifi�
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

            $resobj = $db->query("
                SELECT  mwo.*,
                        mt.label as module_type

                FROM    ploopi_mb_wce_object mwo,
                        ploopi_module m,
                        ploopi_module_type mt

                WHERE   mwo.id = {$id_object[0]}
                AND     mwo.id_module_type = m.id_module_type
                AND     m.id = {$id_object[1]}
                AND     mt.id = m.id_module_type
            ");

            if($obj = $db->fetchrow($resobj))
            {
                $obj['module_id'] = $id_object[1];
                if (isset($id_object[2])) $obj['object_id'] = $id_object[2];

                $arrQuery = explode("&",trim($obj['script'],"?"));

                foreach ($arrQuery as $key => $value) eval("$".$value.";");

                ob_start();
                // Module actif et disponible dans l'espace de travail
                if ($_SESSION['ploopi']['modules'][$obj['module_id']]['active'] && (in_array($obj['module_id'], $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']) || $obj['module_id'] == _PLOOPI_MODULE_SYSTEM))
                {
                    if (file_exists("./modules/".$_SESSION['ploopi']['modules'][$obj['module_id']]['moduletype']."/wce.php"))
                    {
                        include "./modules/{$obj['module_type']}/wce.php";
                        $content .= ob_get_contents();
                    }
                    else $content = "Objet WCE non trouv�";
                }
                else $content = "Objet WCE indisponible";
                
                ob_end_clean();
            }
        }
    }
    return($content);
}

/**
 * Fonction permettant au moteur de recherche global de v�rifier l'accessibilit� d'un enregistrement d'un objet par un utilisateur.
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

/**
 * Remplace les liens internes par leur �quivalent r��crit
 *
 * @param string $strContent contenu d'un article
 * @param string $mode mode d'affichage
 * @param string $arrHeadings tableau des rubriques
 * @return contenu de l'article dont les liens ont �t� modifi�s
 */

function webedit_replace_links($objArticle, $mode, &$arrHeadings)
{
    // Mise en cache
    $objCache = new ploopi_cache('webedit/article/'._PLOOPI_FRONTOFFICE_REWRITERULE.'/'.$objArticle->fields['id'].'/'.$objArticle->fields['lastupdate_timestp'], 86400);

    if (!$strReplaced = $objCache->get_var())
    {
        include_once './modules/webedit/class_article.php';
    
        $arrSearch = array();
        $arrReplace = array();
        $strContent = $objArticle->fields['content'];
    
        // Traitement des ancres (incompatibilit� fckeditor / <base href>)
        preg_match_all('/(href=\"(#[^\"]*)\")/i', $strContent, $arrMatches);
        foreach($arrMatches[2] as $key => $strAnchor)
        {
            $arrSearch[] = $arrMatches[1][$key];
            
            switch ($mode)
            {
                case 'render':
                    $arrReplace[] = str_replace($strAnchor, "index.php?webedit_mode={$mode}&headingid={$objArticle->fields['id_heading']}&articleid={$objArticle->fields['id']}{$strAnchor}", $arrMatches[1][$key]);
                break;
    
                default:
                    $arrParents = array();
                    if (isset($arrHeadings['list'][$objArticle->fields['id_heading']])) foreach(split(';', $arrHeadings['list'][$objArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
    
                    $arrReplace[] = str_replace($strAnchor, ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$objArticle->fields['id']}", webedit_getrewriterules(), $objArticle->fields['metatitle'], $arrParents).$strAnchor, $arrMatches[1][$key]);
                break;
            }
        }
        
        preg_match_all('/(index\.php[^\"]+articleid=([0-9]+)[^\"]*)/i', $strContent, $arrMatches);
        foreach($arrMatches[2] as $key => $idart)
        {
            $objLinkArticle = new webedit_article();
            if (!empty($idart) && $objLinkArticle->open($idart)) // article trouv�
            {
                $arrSearch[] = $arrMatches[1][$key];
    
                switch ($mode)
                {
                    case 'render':
                        $arrReplace[] = "index.php?webedit_mode={$mode}&headingid={$objLinkArticle->fields['id_heading']}&articleid={$idart}";
                    break;
    
                    default:
                        $arrParents = array();
                        if (isset($arrHeadings['list'][$objLinkArticle->fields['id_heading']])) foreach(split(';', $arrHeadings['list'][$objLinkArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
    
                        $arrReplace[] = ploopi_urlrewrite("index.php?headingid={$objLinkArticle->fields['id_heading']}&articleid={$idart}", webedit_getrewriterules(), $objLinkArticle->fields['metatitle'], $arrParents);
                    break;
                }
            }
        }
    
        if (ploopi_init_module('doc', false, false, false))
        {
            include_once './modules/doc/class_docfile.php';
    
            // traitement des liens vers documents
            preg_match_all('/(index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32}))/i', $strContent, $arrMatches);
            foreach($arrMatches[2] as $key => $md5)
            {
                $objDocFile = new docfile();
                if (!empty($md5) && $objDocFile->openmd5($md5)) // cl� md5 pr�sente & document trouv�
                {
                    $arrSearch[] = $arrMatches[1][$key];
                    $arrReplace[] = ploopi_urlrewrite(html_entity_decode($arrMatches[1][$key]), doc_getrewriterules(), $objDocFile->fields['name'], null, true);
                }
            }
        }
        
        $objCache->save_var($strReplaced = str_replace($arrSearch, $arrReplace, $strContent));
    }
    
    return $strReplaced;
}

/**
 * G�n�re le fichier sitemap.xml du site (gestion de mise en cache incluse)
 */
/**
 * G�n�re le fichier sitemap.xml du site (gestion de mise en cache incluse)
 */
function webedit_sitemap()
{
    include_once './include/functions/string.php';
    include_once './include/classes/cache.php';

    // Mise en cache
    $objCache = new ploopi_cache('sitemap.xml', 300);

    // Vidage du buffer
    ploopi_ob_clean();

    if (!$objCache->start())
    {
        global $db;

        // r�cup�ration des rubriques
        $arrHeadings = webedit_getheadings();
        $intToday = ploopi_createtimestamp();

        $strSiteLastMod = '';

        // Insertion des articles
        $db->query("
            SELECT      *
            FROM        ploopi_mod_webedit_article
            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
            AND         (timestp_published <= $intToday OR timestp_published = 0)
            AND         (timestp_unpublished >= $intToday OR timestp_unpublished = 0)
        ");

        $arrUrls = array();

        while ($row = $db->fetchrow())
        {
            $arrParents = array();
            $floPriority = 1;

            $arrLastMod = ploopi_gettimestampdetail($row['lastupdate_timestp']);
            $strLastMod = sprintf("%4d-%02d-%02d", $arrLastMod[1], $arrLastMod[2], $arrLastMod[3]);

            if ($strLastMod > $strSiteLastMod) $strSiteLastMod = $strLastMod;

            if (isset($arrHeadings['list'][$row['id_heading']]))
            {
                $floPriority = 1 - ($arrHeadings['list'][$row['id_heading']]['depth']-1)/10;
                foreach(split(';', $arrHeadings['list'][$row['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

                $arrHeadings['list'][$row['id_heading']]['lastmod'] = $strLastMod;
            }

            $strScript = ploopi_xmlentities(_PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?headingid={$row['id_heading']}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents));
            $strPriority = sprintf("%.01f", $floPriority);

            $arrUrls[] = '<url><loc>'.$strScript.'</loc><lastmod>'.$strLastMod.'</lastmod><changefreq>monthly</changefreq><priority>'.$strPriority.'</priority></url>';
        }

        // Insertion des rubriques
        foreach($arrHeadings['list'] as $arrHeading)
        {
            $arrParents = array();
            if (isset($arrHeadings['list'][$arrHeading['id']]))
            {
                $floPriority = 1 - ($arrHeading['depth']-1)/10;
                foreach(split(';', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
            }

            $strScript = ploopi_xmlentities(_PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?headingid={$arrHeading['id']}", webedit_getrewriterules(), $arrHeading['label'], $arrParents));
            $strPriority = sprintf("%.01f", $floPriority);

            $strLastMod = empty($arrHeading['lastmod']) ? '' : $arrHeading['lastmod'];

            if (empty($strLastMod)) $arrUrls[] = '<url><loc>'.$strScript.'</loc><changefreq>monthly</changefreq><priority>'.$strPriority.'</priority></url>';
            else $arrUrls[] = '<url><loc>'.$strScript.'</loc><lastmod>'.$strLastMod.'</lastmod><changefreq>monthly</changefreq><priority>'.$strPriority.'</priority></url>';
        }

        // Insertion des tags
        $db->query("
            SELECT      t.*, count(*) as nb
            FROM        ploopi_mod_webedit_tag t

            INNER JOIN  ploopi_mod_webedit_article_tag at
            ON          at.id_tag = t.id

            INNER JOIN  ploopi_mod_webedit_article a
            ON          at.id_article = a.id

            WHERE       t.id_module = {$_SESSION['ploopi']['moduleid']}
            AND         (a.timestp_published <= {$intToday} OR a.timestp_published = 0)
            AND         (a.timestp_unpublished >= {$intToday} OR a.timestp_unpublished = 0)

            GROUP BY    t.id
            ORDER BY    nb DESC
        ");

        while ($row = $db->fetchrow())
        {
            $strScript = ploopi_xmlentities(_PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?query_tag={$row['tag']}", webedit_getrewriterules()));
            $arrUrls[] = '<url><loc>'.$strScript.'</loc><lastmod>'.$strSiteLastMod.'</lastmod><changefreq>monthly</changefreq><priority>0.5</priority></url>';
        }
        
        
        foreach($_SESSION['ploopi']['modules'] as $intIdModule => $arrModule)
        {
            if ($arrModule['active'] && $arrModule['moduletype'] != 'webedit')
            {
                // init du module
                ploopi_init_module($arrModule['moduletype'], false, false, false);
                // si la fonction <module>_sitemap() existe, on l'appelle
                if (function_exists($strFuncName = "{$arrModule['moduletype']}_sitemap")) 
                {
                    $arrUrls = array_merge($arrUrls, $strFuncName($intIdModule));
                }
            }
        }        

        echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
'.implode("\n", $arrUrls).'
</urlset>';

        $objCache->end();
    }

    header('Content-Type: text/xml');
}


/**
 * Retourne les partages web (frontoffice) du module pour l'utilisateur connect�
 *
 * @param int $id_module identifiant du module
 *
 * @see ploopi_share_get
 */

function webedit_getshare($id_user = null, $id_module = null)
{
    $arrShares = array();

    if ($_SESSION['ploopi']['connected'])
    {
        if (is_null($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];
        if (is_null($id_user)) $id_user = $_SESSION['ploopi']['userid'];

        $objUser = new user();
        if ($objUser->open($id_user))
        {
            $arrGroups = array_keys($objUser->getgroups(true));

            foreach(ploopi_share_get(-1, -1, -1, $id_module) as $sh)
            {
                if (($sh['type_share'] == 'user' && $sh['id_share'] == $id_user) || ($sh['type_share'] == 'group' && in_array($sh['id_share'], $arrGroups))) $arrShares[$sh['id_record']] = 1;
            }
        }

    }

    return $arrShares;
}

/**
 * Retourne un tableau contenant les r�gles de r��criture propos�es par le module WEBEDIT
 *
 * @return array tableau contenant les r�gles de r��criture
 */
function webedit_getrewriterules()
{
    return array(
        'patterns' => array(
            '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)/',
            '/index.php\?headingid=([0-9]*)/',
            '/index.php\?articleid=([0-9]*)/',
            '/index.php\?ploopi_op=webedit_unsubscribe&subscription_email=([a-z0-9]{32})/',
            '/index.php\?query_tag=([a-zA-Z0-9]*)/',
            '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)&headingid=([0-9]*)/',
            '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)/'
        ),

        'replacements' => array(
            'articles/<FOLDERS><TITLE>-h$1a$2.<EXT>',
            'articles/<FOLDERS><TITLE>-h$1.<EXT>',
            'articles/<FOLDERS><TITLE>-a$1.<EXT>',
            'unsubscribe/$1/index.<EXT>',
            'tags/$1.<EXT>',
            '$1/<TITLE>-h$2.xml',
            '$1/<TITLE>.xml'
            )
    );
}

/**
 * Retourne un tableau en session avec les id_heading � afficher m�me s'ils sont priv�s
 *
 * @Param int/array $heading liste des id_heading � afficher
 * @Param int $id_module identifiant du module
 *
 * @return array $_SESSION['webedit']['allowedheading'][$id_module]
 */
function webedit_allowheading($heading = null, $id_module = null)
{
  if (is_null($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];

  if(is_array($heading))
  {
    foreach ($heading as $id_heading) $_SESSION['webedit']['allowedheading'][$id_module][$id_heading] = true;
  }
  elseif(is_numeric($heading))
    $_SESSION['webedit']['allowedheading'][$id_module][$heading] = true;
}

/**
 * Supprime du tableau en session les id_heading � afficher m�me s'ils sont priv�s
 *
 * @Param int/array $heading liste des id_heading � supprimer
 * @Param int $id_module identifiant du module
 *
 * @return array $_SESSION['webedit']['allowedheading'][$id_module]
 */
function webedit_disallowheading($heading = null, $id_module = null)
{
  if (is_null($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];

  if(is_array($heading))
  {
    foreach ($heading as $id_heading)
    {
      if(isset($_SESSION['webedit']['allowedheading'][$id_module][$id_heading])) unset($_SESSION['webedit']['allowedheading'][$id_module][$id_heading]);
    }
  }
  elseif(is_numeric($heading))
      if(isset($_SESSION['webedit']['allowedheading'][$id_module][$heading])) unset($_SESSION['webedit']['allowedheading'][$id_module][$heading]);
}
?>
