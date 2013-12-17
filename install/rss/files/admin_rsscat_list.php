<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
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
 * Administration - gestion de la liste des cat�gories de flux
 *
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Liste des cat�gories de flux
 */

echo $skin->open_simplebloc(_RSS_LABEL_CATLIST);

$array_columns = array();

$array_columns['auto']['desc'] =
    array(
        'label' => _RSS_LABEL_DESCRIPTION,
        'options' => array('sort' => true)
    );

$array_columns['left']['title'] =
    array(
        'label' => _RSS_LABEL_TITLE,
        'width' => 170,
        'options' => array('sort' => true)
    );

$array_columns['actions_right']['actions'] =
    array(
        'label' => _RSS_LABEL_ACTIONS,
        'width' => 60
    );
$array_columns['right']['limit'] =
    array(
        'label' => _RSS_LABEL_LIMIT,
        'width' => 60
    );

$array_columns['right']['tpl_tag'] =
    array(
        'label' => _RSS_LABEL_TPL_TAG_SHORT,
        'width' => 100
    );

$select =   "
            SELECT  *
            FROM    ploopi_mod_rss_cat
            WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
            AND     id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).")
            ORDER BY title
            ";

$result = $db->query($select);

$array_values = array();
$c = 0;
while ($fields = $db->fetchrow($result))
{
    $actions = '';
    if (ploopi_isactionallowed(_RSS_ACTION_CATMODIFY)) $actions .= '<a title="'._PLOOPI_MODIFY.'" href="'.ploopi_urlencode("admin.php?op=rsscat_modify&rsscat_id={$fields['id']}").'"><img alt="'._PLOOPI_MODIFY.'" src="./modules/rss/img/ico_modify.png" /></a>';
    if (ploopi_isactionallowed(_RSS_ACTION_CATDELETE)) $actions .= '<a title="'._PLOOPI_DELETE.'" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=rsscat_delete&rsscat_id={$fields['id']}").'\',\''._RSS_DELETE_CATEGORIE.'\');"><img alt="'._PLOOPI_DELETE.'" src="./modules/rss/img/ico_trash.png" /></a>';

    if (empty($actions)) $actions = '&nbsp;';

    $array_values[$c]['values']['desc'] =
        array(
            'label' => ploopi_htmlentities($fields['description'])
        );

    $array_values[$c]['values']['title'] =
        array(
            'label' => ploopi_htmlentities($fields['title'])
        );

    $array_values[$c]['values']['limit'] =
        array(
            'label' => ($fields['limit'] == 0) ? '---' : ploopi_htmlentities($fields['limit'])
        );

    $array_values[$c]['values']['tpl_tag'] =
        array(
            'label' => ploopi_htmlentities($fields['tpl_tag'])
        );

    $array_values[$c]['values']['actions'] =
        array(
            'label' => $actions
        );

    $array_values[$c]['description'] = strip_tags($fields['title']);

    if (ploopi_isactionallowed(_RSS_ACTION_CATMODIFY)) $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=rsscat_modify&rsscat_id={$fields['id']}");

    if (!empty($_GET['rsscat_id']) && $_GET['rsscat_id'] == $fields['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
    else $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array(
    $array_columns,
    $array_values,
    'array_rsscatlist',
    array(
        'height' => 250,
        'sortable' => true,
        'orderby_default' => 'title'
    )
);

echo $skin->close_simplebloc();

if (!empty($_GET['rsscat_id']) && is_numeric($_GET['rsscat_id']))
{
    include_once './modules/rss/class_rss_cat.php';
    $rsscat = new rss_cat();
    $rsscat->open($_GET['rsscat_id']);
    include_once 'admin_rsscat_form.php';
}
?>
