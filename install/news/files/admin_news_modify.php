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
 * Administration des news - liste
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
 * Initialisation du tableau contenant les news
 */

$news_columns = array();

$news_columns['auto']['title'] = array('label' => 'Titre', 'options' => array('sort' => true));
$news_columns['right']['published'] = array('label' => 'Publié', 'width' => 80, 'options' => array('sort' => true));
$news_columns['right']['date'] = array('label' => 'Date', 'width' => 80, 'options' => array('sort' => true));
$news_columns['right']['cat'] = array('label' => 'Catégorie', 'width' => 150, 'options' => array('sort' => true));
$news_columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => 60);

/**
 * Recherche des news du module
 */

$result = $db->query("
    SELECT      ploopi_mod_news_entry.id,
                ploopi_mod_news_entry.date_publish,
                ploopi_mod_news_entry.published,
                ploopi_mod_news_entry.hot,
                ploopi_mod_news_entry.title as titlenews,
                ploopi_mod_news_cat.title as titlecat,
                ploopi_mod_news_entry.id_workspace
    FROM        ploopi_mod_news_entry
    LEFT JOIN   ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat
    WHERE       ploopi_mod_news_entry.id_module = {$_SESSION['ploopi']['moduleid']}
    AND         ploopi_mod_news_entry.id_workspace IN (".ploopi_viewworkspaces().")
    ORDER BY    date_publish DESC
");

$news_values = array();
$c = 0;
while ($fields = $db->fetchrow($result))
{
    $titlecat = $fields['titlecat'];
    if (is_null($titlecat)) $titlecat = _NEWS_LABEL_NOCATEGORY;

    /**
     * Conversion timestamp en date locale
     */

    $localdate = ploopi_timestamp2local($fields['date_publish']);

    /**
     * Le champ 'hot' permet de mettre une news en avant
     */
    
    if ($fields['hot']) $hot = 'Style="color:'.$skin->values['colsec'].';background-color:'.$skin->values['colprim'].'"';
    else $hot = '';

    $news_values[$c]['values']['title'] = array('label' => $fields['titlenews']);
    $news_values[$c]['values']['cat'] = array('label' => $titlecat);
    $news_values[$c]['values']['date'] = array('label' => $localdate['date'], 'sort_label' => $fields['date_publish']);
    $news_values[$c]['values']['published'] = array('label' => ($fields['published']) ? 'oui' : 'non', 'style' => ($fields['published']) ? 'color:#00AA00;' : 'color:#AA0000;');

    $arrActions = array();
    
    if (ploopi_isactionallowed(_NEWS_ACTION_MODIFY))
    {
        $arrActions[] = '<a title="Modifier" href="'.ploopi_urlencode("admin.php?op=modify_news&news_id={$fields['id']}").'"><img alt="Modifier" src="./modules/news/img/ico_modify.png" /></a>';
    }
    
    if (ploopi_isactionallowed(_NEWS_ACTION_PUBLISH))
    {
        if ($fields['published'])
            $arrActions[] = '<a title="Retirer" href="'.ploopi_urlencode("admin.php?op=withdraw_news&news_id={$fields['id']}").'"><img alt="Retirer" src="./modules/news/img/ico_withdraw.png" /></a>';
        else
            $arrActions[] = '<a title="Publier" href="'.ploopi_urlencode("admin.php?op=publish_news&news_id={$fields['id']}").'"><img alt="Publier" src="./modules/news/img/ico_publish.png" /></a>';
    }
    
    if (ploopi_isactionallowed(_NEWS_ACTION_DELETE))
    {
        $arrActions[] = '<a title="Supprimer" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=delete_news&news_id={$fields['id']}").'\',\'Êtes-vous certain de vouloir supprimer cette actualité ?\');"><img alt="Supprimer" src="./modules/news/img/ico_trash.png" /></a>';
    }
    
    $news_values[$c]['values']['actions'] =
        array(
            'label' =>  implode('', $arrActions)
        );


    $news_values[$c]['description'] = $fields['titlenews'];
    $news_values[$c]['link'] = ploopi_urlencode("admin.php?op=modify_news&news_id={$fields['id']}");
    if (!empty($_GET['news_id']) && $_GET['news_id'] == $fields['id']) $news_values[$c]['style'] = 'background-color:#ffe0e0;';
    else $news_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($news_columns, $news_values, 'array_newslist', array('sortable' => true, 'orderby_default' => 'date', 'sort_default' => 'DESC', 'limit' => 10));
echo $skin->close_simplebloc();
?>

<?php
/**
 * Modification d'une news
 */

if (!empty($_GET['news_id']) && is_numeric($_GET['news_id']) && $news->open($_GET['news_id']))
{
    include_once 'admin_news_write.php';
}
?>
