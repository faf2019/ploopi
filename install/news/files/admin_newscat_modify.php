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
 * Administration des catégories - liste 
 *
 * @package news
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_NEWS_LIST);

/**
 * Initialisation du tableau contenant les catégories
 */

$array_columns = array();

$array_columns['auto']['desc'] = array('label' => _NEWS_LABEL_DESCRIPTION, 'options' => array('sort' => true));
$array_columns['left']['title'] = array('label' => _NEWS_LABEL_TITLE, 'width' => 200, 'options' => array('sort' => true));
$array_columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => 60);

$select =   "
            SELECT  *
            FROM    ploopi_mod_news_cat
            WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
            AND     id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).")
            ORDER BY title
            ";

$result = $db->query($select);

$array_values = array();
$c = 0;
while ($fields = $db->fetchrow($result))
{
    $array_values[$c]['values']['desc'] = array('label' => $fields['description']);
    $array_values[$c]['values']['title'] = array('label' => $fields['title']);
    $array_values[$c]['values']['actions'] = array('label' =>   "
                                                            <a title=\"Modifier\" href=\"{$scriptenv}?op=modify_newscat&newscat_id={$fields['id']}\"><img alt=\"Modifier\" src=\"./modules/news/img/ico_modify.png\" /></a>
                                                            <a title=\"Supprimer\" href=\"javascript:ploopi_confirmlink('{$scriptenv}?op=delete_newscat&newscat_id={$fields['id']}','Êtes-vous certain de vouloir supprimer cette catégorie ?');\"><img alt=\"Supprimer\" src=\"./modules/news/img/ico_trash.png\" /></a>
                                                            ");


    $array_values[$c]['description'] = $fields['title'];
    $array_values[$c]['link'] = "{$scriptenv}?op=modify_newscat&newscat_id={$fields['id']}";
    if (!empty($_GET['newscat_id']) && $_GET['newscat_id'] == $fields['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
    else $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($array_columns, $array_values, 'array_newscatlist', array('height' => 150, 'sortable' => true, 'orderby_default' => 'title'));
echo $skin->close_simplebloc();
?>

<?
/**
 * Modification d'une catégorie
 */
if (!empty($_GET['newscat_id']) && is_numeric($_GET['newscat_id']))
{
    $newscat->open($_GET['newscat_id']);
    include_once 'admin_newscat_write.php';
}
?>
