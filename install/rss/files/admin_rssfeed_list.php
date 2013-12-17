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
 * Administration - gestion de la liste des flux RSS
 *
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Liste des flux
 */

echo $skin->open_simplebloc(_RSS_LABEL_FEEDLIST);

$array_columns = array();

$array_columns['auto']['subtitle'] =
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
    
$array_columns['right']['limit'] =
    array(
        'label' => _RSS_LABEL_LIMIT,
        'width' => 60,
        'options' => array('sort' => true)
    );

$array_columns['right']['tpl_tag'] =
    array(
        'label' => _RSS_LABEL_TPL_TAG_SHORT,
        'width' => 100
    );

$array_columns['right']['revisit'] =
    array(
        'label' => _RSS_LABEL_FEED_RENEW_SHORT,
        'width' => 60,
        'options' => array('sort' => true)
    );
    
$array_columns['right']['category'] =
    array(
        'label' => _RSS_LABEL_CATEGORY,
        'width' => 120,
        'options' => array('sort' => true)
    );
    
$array_columns['actions_right']['actions'] =
    array(
        'label' => _RSS_LABEL_ACTIONS,
        'width' => 60
    );
    

$select =   "
            SELECT      feed.*,
                        IFNULL(cat.title, '') as titlecat
            FROM        ploopi_mod_rss_feed feed

            LEFT JOIN   ploopi_mod_rss_cat cat
            ON          feed.id_cat = cat.id
            WHERE       feed.id_module = {$_SESSION['ploopi']['moduleid']}
            AND         feed.id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).")
            ORDER BY    feed.title
            ";

$result = $db->query($select);

$array_values = array();
$c = 0;
while ($fields = $db->fetchrow($result))
{
    $actions = '';
    if (ploopi_isactionallowed(_RSS_ACTION_FEEDMODIFY)) $actions .= '<a title="'._PLOOPI_MODIFY.'" href="'.ploopi_urlencode("admin.php?op=rssfeed_modify&rssfeed_id={$fields['id']}").'"><img alt="'._PLOOPI_MODIFY.'" src="./modules/rss/img/ico_modify.png" /></a>';
    if (ploopi_isactionallowed(_RSS_ACTION_FEEDDELETE)) $actions .= '<a title="'._PLOOPI_DELETE.'" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=rssfeed_delete&rssfeed_id={$fields['id']}").'\',\''._RSS_DELETE_FEED.'\');"><img alt="'._PLOOPI_DELETE.'" src="./modules/rss/img/ico_trash.png" /></a>';

    if (empty($actions)) $actions = '&nbsp;';

    $array_values[$c]['values']['subtitle'] =
        array(
            'label' => strip_tags($fields['subtitle'], '<b><i>')
        );
        
    $array_values[$c]['values']['title'] =
        array(
            'label' => strip_tags($fields['title'], '<b><i>')
        );
        
    $array_values[$c]['values']['revisit'] =
        array(
            'label' => (isset($rss_revisit_values[$fields['revisit']])) ? ploopi_htmlentities($rss_revisit_values[$fields['revisit']]) : '',
            'sort_label' => (isset($rss_revisit_values[$fields['revisit']])) ? $fields['revisit'] : ''
        );
        
    $array_values[$c]['values']['category'] =
        array(
            'label' => ploopi_htmlentities($fields['titlecat'])
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

    $array_values[$c]['description'] = ploopi_htmlentities($fields['title']);

    if (ploopi_isactionallowed(_RSS_ACTION_FEEDMODIFY)) $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=rssfeed_modify&rssfeed_id={$fields['id']}");

    if (!empty($_GET['rssfeed_id']) && $_GET['rssfeed_id'] == $fields['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
    else $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array(
    $array_columns,
    $array_values,
    'array_rssfeedlist',
    array(
        'height' => 250,
        'sortable' => true,
        'orderby_default' => 'title'
    )
);

echo $skin->close_simplebloc();

if (!empty($_GET['rssfeed_id']) && is_numeric($_GET['rssfeed_id']))
{
    include_once './modules/rss/class_rss_feed.php';
    $rssfeed = new rss_feed();
    $rssfeed->open($_GET['rssfeed_id']);
    include_once 'admin_rssfeed_form.php';
}
?>


