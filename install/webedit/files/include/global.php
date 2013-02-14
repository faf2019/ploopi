<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Fonctions, constantes, variables globales
 *
 * @package webedit
 * @subpackage global
 * @copyright Netlor, Ovensia, HeXad
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
 * Gérer les autorisations d'accès
 */
define ('_WEBEDIT_ACTION_ACCESS_MANAGE',   6);

/**
 * Stats
 */
define ('_WEBEDIT_ACTION_STATS',   8);

/**
 * Réindexation
 */
define ('_WEBEDIT_ACTION_REINDEX',   9);

/**
 * Gerer les commentaires
 */
define ('_WEBEDIT_ACTION_COMMENT',   10);

/**
 * Gerer les rédacteurs
 */
define ('_WEBEDIT_ACTION_HEADING_BACK_EDITOR_MANAGE',   11);

/**
 * Etre rédacteur potentiel
 */
define ('_WEBEDIT_ACTION_HEADING_BACK_EDITOR',   12);

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
 * Objet : REDACTEUR DE RUBRIQUE
 */
define ('_WEBEDIT_OBJECT_HEADING_BACK_EDITOR',  4);

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

function webedit_gettreeview($arrHeadings = array(), $articles = array(), $option = '', $moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    switch($option)
    {
        // déplacement d'un article vers une rubrique
        case 'selectheading':
            $prefix = 'h';
        break;

        // redirection depuis une rubrique vers un article ou rubrique
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
                    $link = '';
                    $onclick = "webedit_select_article_or_heading('h{$fields['id']}', '".addslashes($fields['label'])."', event)";
                break;

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
                    'parents' => preg_split('/;/', $fields['parents']),
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
                            $onclick = "webedit_select_article_or_heading('{$fields['id']}', '".addslashes($fields['title'])."', event)";
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

    $select = "
        SELECT      wh.*, count(distinct(s.id)) as shares
        FROM        ploopi_mod_webedit_heading wh
        LEFT JOIN   ploopi_share s ON s.id_record = wh.id AND s.id_module = wh.id_module AND s.id_object = "._WEBEDIT_OBJECT_HEADING."
        WHERE       wh.id_module = {$moduleid}
        GROUP BY    wh.id
        ORDER BY    wh.depth, wh.position
    ";

    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        $arrHeadings['list'][$fields['id']] = $fields;
        $arrHeadings['tree'][$fields['id_heading']][] = $fields['id'];

        $parents = preg_split('/;/',$arrHeadings['list'][$fields['id']]['parents']);
        if (isset($parents[0])) unset($parents[0]);
        $parents[] = $fields['id'];

        $arrHeadings['list'][$fields['id']]['nav'] = implode('-',$parents);

        if ($arrHeadings['list'][$fields['id']]['template'] == '' && isset($arrHeadings['list'][$fields['id_heading']]) && $arrHeadings['list'][$fields['id_heading']]['template'] != '')
        {
            $arrHeadings['list'][$fields['id']]['template'] = $arrHeadings['list'][$fields['id_heading']]['template'];
            $arrHeadings['list'][$fields['id']]['herited_template'] = 1;
        }

        if ($arrHeadings['list'][$fields['id']]['private']) 
        {
            // Cas particulier si aucun partage et qu'il existe un parent privé, on hérite des partages du parent
            if ($fields['shares'] == 0 && isset($arrHeadings['list'][$fields['id_heading']]) && $arrHeadings['list'][$fields['id_heading']]['private']) 
            {
                $arrHeadings['list'][$fields['id']]['private_visible'] = $arrHeadings['list'][$fields['id_heading']]['private_visible'];
                $arrHeadings['list'][$fields['id']]['herited_private'] = $arrHeadings['list'][$fields['id_heading']]['herited_private'];
            }
            else $arrHeadings['list'][$fields['id']]['herited_private'] = $fields['id'];
        }

        if (!$arrHeadings['list'][$fields['id']]['private'] && isset($arrHeadings['list'][$fields['id_heading']]) && $arrHeadings['list'][$fields['id_heading']]['private'])
        {
            $arrHeadings['list'][$fields['id']]['private'] = 1;
            $arrHeadings['list'][$fields['id']]['private_visible'] = $arrHeadings['list'][$fields['id_heading']]['private_visible'];
            $arrHeadings['list'][$fields['id']]['herited_private'] = $arrHeadings['list'][$fields['id_heading']]['herited_private'];
        }

        // Il suffit qu'une rubrique active le flux pour que le flux soit également activé sur le site global (en respectant le choix de chaque rubrique)
        if ($fields['feed_enabled'] && !$arrHeadings['feed_enabled']) $arrHeadings['feed_enabled'] = true;

        // Il suffit qu'une rubrique active l'abonnement pour que l'abonnement soit également activé sur le site global (en respectant le choix de chaque rubrique)
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

function webedit_getarticles($moduleid = -1, $booBlocs = false)
{
    global $db;

    $key = $booBlocs ? 'blocs' : 'articles';
    $sql_filter = $booBlocs ? ' AND ad.id_heading = 0 ' : ' AND ad.id_heading > 0 ';

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    $today = ploopi_createtimestamp();

    if (!isset($_SESSION['webedit'][$key])) $_SESSION['webedit'][$key] = array('list' => array());
    // Reset tree
    $_SESSION['webedit'][$key]['tree'] = array();

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
        {$sql_filter}
        ORDER BY    ad.position
    ");

    while ($fields = $db->fetchrow($result))
    {
        if (!isset($_SESSION['webedit'][$key]['list'][$fields['id']]) || $fields['lastupdate_timestp'] != $_SESSION['webedit'][$key]['list'][$fields['id']]['lastupdate_timestp'])
        {
            // nouvel article ou article modifié
            if (is_null($fields['online_id'])) $fields['new_version'] = 2;
            else $fields['new_version'] = (strip_tags($fields['content']) != strip_tags($fields['online_content']) || $fields['id_heading'] != $fields['online_id_heading']) ? '1' : '0';

            $fields['date_ok'] = (($fields['timestp_published'] <= $today || $fields['timestp_published'] == 0) && ($fields['timestp_unpublished'] >= $today || $fields['timestp_unpublished'] == 0));

            unset($fields['content']);
            unset($fields['online_content']);

            $_SESSION['webedit'][$key]['list'][$fields['id']] = $fields;
            $arrArticles[$fields['id']] = $fields;
        }
        else $arrArticles[$fields['id']] = $_SESSION['webedit'][$key]['list'][$fields['id']];

        $_SESSION['webedit'][$key]['list'] = $arrArticles;
        $_SESSION['webedit'][$key]['tree'][$fields['id_heading']][] = $fields['id'];
    }

    return $_SESSION['webedit'][$key];
}

/**
 * Traduit les rubriques en variables template en fonction de la position dans l'arbre des rubriques
 *
 * @param array $arrHeadings tableau contenant les rubriques
 * @param array $arrShares tableau contenant les partages
 * @param array $nav tableau contenant les rubriques déjà sélectionnées
 * @param int $hid identifiant de la rubrique à afficher
 * @param string $var nom du bloc parent (template)
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign(&$arrHeadings, &$arrShares, &$nav, $hid, $var = '', $link = '')
{
    global $template_body;
    global $webedit_mode;

    // La rubrique à afficher ($hid) dispose-t-elle de rubriques filles ?
    if (isset($arrHeadings['tree'][$hid]))
    {
        // Petit filtrage rapide pour ne garder que les rubriques accessibles, etc.
        $arrAccessibleHeadings = array();
        foreach($arrHeadings['tree'][$hid] as $id)
        {
            $arrHeading = $arrHeadings['list'][$id];

            // Visible ET (Publique OU (Privée ET (Autorisé OU Autorisé par un module OU Toujours Visible)))
            if ($arrHeading['visible'] && (!$arrHeading['private'] || ($arrHeading['private'] && (isset($arrShares[$arrHeading['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeading['herited_private']]) || $arrHeading['private_visible']))))
            {
                $arrAccessibleHeadings[] = &$arrHeadings['list'][$id];
            }
        }

        // On compte le nombre de boucle pour savoir si on est sur la dernière rubrique du cycle
        $intC = 1;
        // Pour chaque rubrique fille accessible
        foreach($arrAccessibleHeadings as $arrHeading)
        {
            $id = $arrHeading['id'];

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
            $locallink = ($link!='') ? "{$link}-{$arrHeading['id']}" : "{$arrHeading['id']}";

            switch($webedit_mode)
            {
                case 'edit';
                    $script = "javascript:window.parent.document.location.href='admin.php?headingid={$arrHeading['id']}';";
                break;

                case 'render';
                    $script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$arrHeading['id']}";
                break;

                default:
                case 'display';
                    $arrParents = array();
                    foreach(preg_split('/;/', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $script = ploopi_urlrewrite($script = "index.php?headingid={$arrHeading['id']}", webedit_getrewriterules(), $arrHeading['label'], $arrParents);
                break;
            }

            $sel = '';

            // Si rubrique fille sélectionnée, traitements particuliers => génération de variables spéciales
            if (isset($nav[$depth]) && $nav[$depth] == $arrHeading['id'])
            {
                // La rubrique n'a pas encore été traitée dans ce parcours (done_full)
                if (empty($arrHeading['done_full']))
                {
                    $template_body->assign_block_vars('path' , array(
                        'DEPTH' => $depth,
                        'LABEL' => $strHtmlLabel,
                        'LINK' => $script
                        ));

                    /* Déprécié : remplacé par le bloc ci-dessous */
                    $template_body->assign_var("HEADING{$depth}_TITLE",         $strHtmlLabel);
                    $template_body->assign_var("HEADING{$depth}_TITLE_RAW",     $arrHeading['label']);
                    $template_body->assign_var("HEADING{$depth}_ID",            $arrHeading['id']);
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
                }

                $sel = 'selected';
            }

            // Visible ET (Publique OU (Privée ET (Autorisé OU Autorisé par un module OU Toujours Visible)))
            if ($arrHeading['visible'] && (!$arrHeading['private'] || ($arrHeading['private'] && (isset($arrShares[$arrHeading['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeading['herited_private']]) || $arrHeading['private_visible']))))
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

                if ($intC < sizeof($arrAccessibleHeadings)) $template_body->assign_block_vars("{$localvar}.sw_separator" , array());

                $arrHeadings['list'][$arrHeading['id']]['done_full'] = true;

                // Parcours 1 : Génération de l'arbre complet des rubriques
                // Si des rubriques filles disponibles pour la rubrique courante, on fait un appel récursif
                if (isset($arrHeadings['tree'][$arrHeading['id']]))
                {
                    $template_body->assign_block_vars($localvar.'.switch_submenu' , array());
                    webedit_template_assign($arrHeadings, $arrShares, $nav, $arrHeading['id'], "{$localvar}.", $locallink);
                }
            }

            $intC++;
        }

        // Parcours 2 : génération uniquement de la branche sélectionnée
        // La rubrique existe et n'a pas encore été traitée dans ce parcours (done_nav)
        if (isset($arrHeadings['list'][$hid]) && !isset($arrHeadings['list'][$hid]['done_nav']))
        {
            $arrHeadings['list'][$hid]['done_nav'] = true;
            $depth = $arrHeadings['list'][$hid]['depth'];
            if ($depth > 0  && isset($nav[$depth-1]) && $nav[$depth-1] == $hid)
            {
                if ($link!='' && isset($nav[$depth])) $link .= "-$nav[$depth]";
                elseif (isset($nav[$depth])) $link = "$nav[$depth]";

                if (isset($nav[$depth]) && isset($arrHeadings['tree'][$nav[$depth]])) webedit_template_assign($arrHeadings, $arrShares, $nav, $nav[$depth], '', $link);
            }
        }

    }
}

/**
 * Traduit les rubriques en variables template pour le contenu d'une page
 *
 * @param array $arrHeadings tableau contenant les rubriques
 * @param array $arrShares tableau contenant les partages
 * @param int $hid identifiant de la rubrique à afficher
 * @param string $var nom du bloc parent (template)
 * @param string $prefix préfixe pour le nommage des blocs
 * @param int $depth profondeur relative de la rubrique
 * @param string $link lien de la rubrique parent
 */

function webedit_template_assign_headings(&$arrHeadings, &$arrArticles, &$arrShares, $hid, $var = 'switch_content_heading.', $prefix = 'subheading', $depth = 1, $link = '')
{
    global $template_body;
    global $webedit_mode;

    if (isset($arrHeadings['tree'][$hid]))
    {
        foreach($arrHeadings['tree'][$hid] as $id)
        {
            $arrHeading = $arrHeadings['list'][$id];

            // Visible ET (Publique OU (Privée ET (Autorisé OU Autorisé par un module OU Toujours Visible)))
            if ($arrHeading['visible'] && (!$arrHeadings['list'][$arrHeading['id']]['private'] || ($arrHeadings['list'][$arrHeading['id']]['private'] && (isset($arrShares[$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$arrHeading['id']]['herited_private']]) || $arrHeadings['list'][$arrHeading['id']]['private_visible']))))
            {
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
                        foreach(preg_split('/;/', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                        $script = ploopi_urlrewrite($script = "index.php?headingid={$id}", webedit_getrewriterules(), $arrHeading['label'], $arrParents);
                    break;
                }


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


                if (isset($arrArticles[$arrHeading['id']]))
                {
                    foreach($arrArticles[$arrHeading['id']] as $row)
                    {

                        // Bloc PAGE (boutons pour les pages)
                        if($webedit_mode == 'render')
                            $scriptUrlArticle = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$arrHeading['id']}&articleid={$row['id']}";
                        else
                        {
                            $arrParents = array();
                            if (isset($arrHeadings['list'][$arrHeading['id']])) foreach(preg_split('/;/', $arrHeadings['list'][$arrHeading['id']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                            $scriptUrlArticle = ploopi_urlrewrite("index.php?headingid={$arrHeading['id']}&articleid={$row['id']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);
                        }

                        $ldate_pub = (!empty($row['timestp_published'])) ? ploopi_timestamp2local($row['timestp_published']) : array('date' => '');
                        $ldate_unpub = (!empty($row['timestp_unpublished'])) ? ploopi_timestamp2local($row['timestp_unpublished']) : array('date' => '');
                        $ldate_lastupdate = (!empty($row['lastupdate_timestp'])) ? ploopi_timestamp2local($row['lastupdate_timestp']) : array('date' => '', 'time' => '');
                        $ldate_timestp = (!empty($row['timestp'])) ? ploopi_timestamp2local($row['timestp']) : array('date' => '');

                        $var_tpl_page =
                            array(
                                'REFERENCE'     => htmlentities($row['reference']),
                                'LABEL'         => htmlentities($row['title']),
                                'LABEL_RAW'     => $row['title'],
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
                                'LINK'          => $scriptUrlArticle,
                                'POSITION'      => $row['position'],
                            );


                        $template_body->assign_block_vars($localvar.".page" , $var_tpl_page);
                    }
                }

                if (isset($arrHeadings['tree'][$id])) webedit_template_assign_headings($arrHeadings, $arrArticles, $arrShares, $id, "{$localvar}.", $prefix, $depth+1, $locallink);
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
        $key = preg_split('/\//',$matches[1]);
        $id_object = preg_split('/,/',$key[0]);

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
                        $content = ob_get_contents();
                    }
                    else $content = "Objet WCE non trouvé";
                }
                else $content = "Objet WCE indisponible";

                ob_end_clean();
            }
        }
    }
    return $content;
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

/**
 * Remplace les liens internes par leur équivalent réécrit
 *
 * @param string $strContent contenu d'un article
 * @param string $mode mode d'affichage
 * @param string $arrHeadings tableau des rubriques
 * @return contenu de l'article dont les liens ont été modifiés
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

        // Traitement des ancres (incompatibilité fckeditor / <base href>)
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
                    if (isset($arrHeadings['list'][$objArticle->fields['id_heading']])) foreach(preg_split('/;/', $arrHeadings['list'][$objArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

                    $arrReplace[] = str_replace($strAnchor, ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$objArticle->fields['id']}", webedit_getrewriterules(), $objArticle->fields['metatitle'], $arrParents).$strAnchor, $arrMatches[1][$key]);
                break;
            }
        }

        preg_match_all('/(index\.php[^\"]+articleid=([0-9]+)[^\"]*)/i', $strContent, $arrMatches);
        foreach($arrMatches[2] as $key => $idart)
        {
            $objLinkArticle = new webedit_article();
            if (!empty($idart) && $objLinkArticle->open($idart)) // article trouvé
            {
                $arrSearch[] = $arrMatches[1][$key];

                switch ($mode)
                {
                    case 'render':
                        $arrReplace[] = "index.php?webedit_mode={$mode}&headingid={$objLinkArticle->fields['id_heading']}&articleid={$idart}";
                    break;

                    default:
                        $arrParents = array();
                        if (isset($arrHeadings['list'][$objLinkArticle->fields['id_heading']])) foreach(preg_split('/;/', $arrHeadings['list'][$objLinkArticle->fields['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

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
                if (!empty($md5) && $objDocFile->openmd5($md5)) // clé md5 présente & document trouvé
                {
                    $arrSearch[] = $arrMatches[1][$key];
                    // ATTENTION ! _PLOOPI_BASEPATH est nécessaire pour la lecture des vidéos flash (chemin absolu sinon ne fonctionne pas)
                    $arrReplace[] = _PLOOPI_BASEPATH.'/'.ploopi_urlrewrite(html_entity_decode($arrMatches[1][$key]), doc_getrewriterules(), $objDocFile->fields['name'], null, true);
                }
            }
        }

        $objCache->save_var($strReplaced = str_replace($arrSearch, $arrReplace, $strContent));
    }

    return $strReplaced;
}

/**
 * Génère le fichier sitemap.xml du site (gestion de mise en cache incluse)
 */
/**
 * Génère le fichier sitemap.xml du site (gestion de mise en cache incluse)
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

        // récupération des rubriques
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
                foreach(preg_split('/;/', $arrHeadings['list'][$row['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

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
                foreach(preg_split('/;/', $arrHeading['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
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
 * Retourne les partages web (frontoffice) du module pour l'utilisateur connecté
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
 * Retourne un tableau contenant les règles de réécriture proposées par le module WEBEDIT
 *
 * @return array tableau contenant les règles de réécriture
 */
function webedit_getrewriterules()
{
    return array(
        'patterns' => array(
            // Blog
            '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&yearmonth=([0-9]{6})&day=([0-9]{2})/',
            '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&yearmonth=([0-9]{6})/',
            '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&year=([0-9]{4})/',
            '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)/',
            '/index.php\?headingid=([0-9]*)&yearmonth=([0-9]{6})&day=([0-9]{2})/',
            '/index.php\?headingid=([0-9]*)&yearmonth=([0-9]{6}|<YEARMONTH>)/',
            '/index.php\?headingid=([0-9]*)&year=([0-9]{4}|<YEAR>)/',
            // Article
            '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)&comment_return=([0-9]*)/',
            '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)/',
            '/index.php\?headingid=([0-9]*)&comment_return=([0-9]*)/',
            '/index.php\?headingid=([0-9]*)/',
            '/index.php\?articleid=([0-9]*)&comment_return=([0-9]*)/',
            '/index.php\?articleid=([0-9]*)/',
            // Divers
            '/index.php\?ploopi_op=webedit_unsubscribe&subscription_email=([a-z0-9]{32})/',
            '/index.php\?query_tag=([a-zA-Z0-9]*)/',
            '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)&headingid=([0-9]*)/',
            '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)/',
            '/index.php\?ploopi_op=webedit_backend&query_tag=([a-zA-Z0-9]*)&moduleid=([0-9]*)/',

        ),

        'replacements' => array(
            // Blog
            'blog/<FOLDERS><TITLE>-h$1p$2ym$3d$4.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1p$2ym$3.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1p$2y$3.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1p$2.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1ym$2d$3.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1ym$2.<EXT>',
            'blog/<FOLDERS><TITLE>-h$1y$2.<EXT>',
            // Article
            'articles/<FOLDERS><TITLE>-h$1a$2r$3.<EXT>', // avec reponse (de commentaire par ex.)
            'articles/<FOLDERS><TITLE>-h$1a$2.<EXT>',
            'articles/<FOLDERS><TITLE>-h$1r$2.<EXT>', // avec reponse (de commentaire par ex.)
            'articles/<FOLDERS><TITLE>-h$1.<EXT>',
            'articles/<FOLDERS><TITLE>-a$1r$2.<EXT>', // avec reponse (de commentaire par ex.)
            'articles/<FOLDERS><TITLE>-a$1.<EXT>',
            // Divers
            'unsubscribe/$1/index.<EXT>',
            'tags/$1.<EXT>',
            'web/$1/<TITLE>-h$2.xml',
            'web/$1/<TITLE>.xml',
            'tag3D/$1/$2.xml'
            )
    );
}

/**
 * Retourne un tableau en session avec les id_heading à afficher même s'ils sont privés
 *
 * @Param int/array $heading liste des id_heading à afficher
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
 * Supprime du tableau en session les id_heading à afficher même s'ils sont privés
 *
 * @Param int/array $heading liste des id_heading à supprimer
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

/**
 * Contrôle si le user connecté est un Rédacteur
 *
 * @Param int $heading id_heading à contrôler
 * @Param int $user identifiant du user ou du groupe (optionnel)
 * @Param string $type type de user (user/group) (optionnel)
 * @Param int $id_module identifiant du module (optionnel)
 *
 * @return boolean true/false
 */
function webedit_isEditor($heading, $user = null, $type = 'user', $id_module = null)
{
    if ((empty($heading) || !is_numeric($heading)) && $heading != 'b') return false;

    if (is_null($user)) $user = $_SESSION['ploopi']['userid'];

    $type = strtolower($type);
    $type = ($type == 'user' &&  $type == 'group') ? $type : 'user';

    if (is_null($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];

    // On cherche les parents de cet heading
    include_once './modules/webedit/class_heading.php';

    if ($heading != 'b')
    {
        $objHeading = new webedit_heading();
        if(!$objHeading->open($heading)) return false;
        $arrHeading = explode(';',$objHeading->fields['parents']);
    }
    $arrHeading[] = $heading;

    // On test si c'est un rédacteur avec le user
    $arrEditor = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $arrHeading, $id_module, $user, $type);

    // on a verifié par le user et il n'est pas rédacteur, on va verif ses groupes
    if(empty($arrEditor) && $type == 'user')
    {
        include_once './include/classes/user.php';

        $objUser = new user();
        $objUser->open($user);
        $arrGroups = array_keys($objUser->getgroups(true));

        if(!empty($arrGroups))
            $arrEditor = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $arrHeading, $id_module, $arrGroups, 'group');
    }

    return (!empty($arrEditor));
}


/**
 * Contrôle si le user connecté est un Validateur
 *
 * @Param int $heading id_heading à contrôler
 * @Param int $user identifiant du user ou du groupe (optionnel)
 * @Param string $type type de user (user/group) (optionnel)
 * @Param int $id_module identifiant du module (optionnel)
 *
 * @return boolean true/false
 */
function webedit_isValidator($heading, $user = null, $type = 'user', $id_module = null)
{
    if (empty($heading) || !is_numeric($heading)) return false;

    if (is_null($user)) $user = $_SESSION['ploopi']['userid'];

    $type = strtolower($type);
    $type = ($type == 'user' &&  $type == 'group') ? $type : 'user';

    if (is_null($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];

    // On cherche les parents de cet heading
    include_once './modules/webedit/class_heading.php';

    $objHeading = new webedit_heading();
    if(!$objHeading->open($heading)) return false;
    $arrHeading = explode(';',$objHeading->fields['parents']);
    $arrHeading[] = $heading;

    // On test si c'est un rédacteur avec le user
    $arrEditor = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING, $arrHeading, $id_module, $user, $type);

    // on a verifié par le user et il n'est pas rédacteur, on va verif ses groupes
    if(empty($arrEditor) && $type == 'user')
    {
        include_once './include/classes/user.php';

        $objUser = new user();
        $objUser->open($user);
        $arrGroups = array_keys($objUser->getgroups(true));

        if(!empty($arrGroups))
            $arrEditor = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING, $arrHeading, $id_module, $arrGroups, 'group');
    }

    return (!empty($arrEditor));
}


/**
 * Contrôle si la redirection vers une rubrique ou un article n'est pas une boucle infinie
 *
 * @Param int $intIdHeading id_heading courant à contrôler
 * @Param string $strIdRedirect id de redirection (ex: h3 => id heading = 3, 5 => id article = 5)
 * @Param array $arrHeadings tableau des heading provenant de webedit_getheadings() ou  (optionnel)
 *
 * @return boolean true/false
 */

function webedit_ctrl_infinite_loops_redirect($intIdHeading, $strIdRedirect, $arrHeadings =array())
{
    $arrRedirect = array();

    $arrRedirect[] = $intIdHeading = 'h'.$intIdHeading;

    if($intIdHeading == $strIdRedirect) return true; // Boucle infinie sur lui-même !

    if(empty($arrHeadings)) $arrHeadings = webedit_getheadings();

    do {
        if(substr($strIdRedirect,0,1) == 'h') // Heading
        {
            $intIdRedirect = substr($strIdRedirect,1);

            if(isset($arrHeadings['list'][$intIdRedirect]))
            {
                if($arrHeadings['list'][$intIdRedirect]['content_type'] != 'article_redirect') return false; //heading non redirigé donc aps de rique de boucle

                if(in_array($arrHeadings['list'][$intIdRedirect]['linkedpage'], $arrRedirect)) return true; // boucle !!!
                $strIdRedirect = $arrRedirect[] = $arrHeadings['list'][$intIdRedirect]['linkedpage'];
            }
            else
                return true; // Erreur ! Pour assurer on retourne comme si boucle.
        }
        else // Article (donc Impossible d'avoir une boucle...)
            return false; // pas de boucle

    } while(!empty($strIdRedirect));

    return false; // pas de boucle
}

/**
 * Retourne une liste "linéaire" de rubriques à partir du tableau tree/list
 **/
function webedit_headinglist2template(&$arrHeadings, &$arrShares, $template_body, $intSel = 0,  $intHeadingId = 0)
{
    if (isset($arrHeadings['tree'][$intHeadingId]))
    {
        foreach($arrHeadings['tree'][$intHeadingId] as $intId)
        {
            if (!$arrHeadings['list'][$intId]['private'] || isset($arrShares[$arrHeadings['list'][$intId]['herited_private']]) || isset($_SESSION['webedit']['allowedheading'][$_SESSION['ploopi']['moduleid']][$arrHeadings['list'][$intId]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
            {
                $template_body->assign_block_vars('switch_advanced_search.headings', array(
                    'ID' => $intId,
                    'LABEL' => htmlentities($arrHeadings['list'][$intId]['label']),
                    'DEPTH' => $arrHeadings['list'][$intId]['depth'],
                    'SELECTED' => $intSel == $intId ? 'selected="selected"' : ''
                ));

                webedit_headinglist2template($arrHeadings, $arrShares, $template_body, $intSel, $intId);
            }
        }
    }
}
?>
