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
 * Interface d'administration du module.
 * 
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss');

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$groups = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

switch($op)
{
    case 'reindex':
        if (ploopi_isactionallowed(0))
        {
            $sql =  "
                    SELECT      e.*,
                                f.id_cat
                    FROM        ploopi_mod_rss_entry e

                    INNER JOIN  ploopi_mod_rss_feed f
                    ON          f.id = e.id_feed

                    WHERE       e.id_module = {$_SESSION['ploopi']['moduleid']}
                    ";

            $rs = $db->query($sql);

            while ($row = $db->fetchrow($rs))
            {
                $ts = ploopi_unixtimestamp2timestamp($row['published']);
                ploopi_search_create_index(_RSS_OBJECT_NEWS_ENTRY, sprintf("%06d%06d%s", $row['id_cat'], $row['id_feed'], $row['id']), $row['title'], strip_tags(html_entity_decode($row['content'])), strip_tags(html_entity_decode("{$row['title']} {$row['subtitle']} {$row['author']}")), true, $ts, $ts, $row['id_user'], $row['id_workspace'], $row['id_module'] );
            }
        }

        ploopi_redirect("{$scriptenv}?end");
    break;

    case 'rsscat_save':
        if (ploopi_isactionallowed(_RSS_ACTION_CATMODIFY) || ploopi_isactionallowed(_RSS_ACTION_CATCREATE))
        {
            include_once './modules/rss/class_rss_cat.php';
            $rsscat = new rss_cat();

            if (!empty($_POST['rsscat_id']) && is_numeric($_POST['rsscat_id']) && ploopi_isactionallowed(_RSS_ACTION_CATMODIFY)) $rsscat->open($_POST['rsscat_id']);

            $rsscat->setvalues($_POST,'rsscat_');
            $rsscat->setuwm();
            $rsscat->save();

            if ($rsscat->new) ploopi_create_user_action_log(_RSS_ACTION_CATCREATE, $rsscat->fields['id']);
            else ploopi_create_user_action_log(_RSS_ACTION_CATMODIFY, $rsscat->fields['id']);
        }
        ploopi_redirect("{$scriptenv}?rssTabItem=tabCatList");
    break;

    case 'rsscat_delete':
        if (ploopi_isactionallowed(_RSS_ACTION_CATDELETE))
        {
            include_once './modules/rss/class_rss_cat.php';
            $rsscat = new rss_cat();
            if (!empty($_GET['rsscat_id']) && is_numeric($_GET['rsscat_id']) && $rsscat->open($_GET['rsscat_id']))
            {
                ploopi_create_user_action_log(_RSS_ACTION_CATDELETE, $rsscat->fields['id']);
                $rsscat->delete();
            }
        }
        ploopi_redirect($scriptenv);
    break;

    case 'rssfeed_save':
        if (ploopi_isactionallowed(_RSS_ACTION_FEEDMODIFY) || ploopi_isactionallowed(_RSS_ACTION_FEEDCREATE))
        {
            include_once './modules/rss/class_rss_feed.php';
            $rssfeed = new rss_feed();

            if (!empty($_POST['rssfeed_id']) && is_numeric($_POST['rssfeed_id']) && ploopi_isactionallowed(_RSS_ACTION_FEEDMODIFY)) $rssfeed->open($_POST['rssfeed_id']);

            $rssfeed->setvalues($_POST,'rssfeed_');
            $rssfeed->setuwm();

            $rssfeed->save();

            if ($rssfeed->new) ploopi_create_user_action_log(_RSS_ACTION_FEEDCREATE, $rssfeed->fields['id']);
            else ploopi_create_user_action_log(_RSS_ACTION_FEEDMODIFY, $rssfeed->fields['id']);
        }
        ploopi_redirect("{$scriptenv}?rssTabItem=tabFeedModify&rssfeed_id={$rssfeed->fields['id']}");
    break;

    case 'rssfeed_delete':
        if (ploopi_isactionallowed(_RSS_ACTION_FEEDDELETE) && !empty($_GET['rssfeed_id']) && is_numeric($_GET['rssfeed_id']))
        {
            $rssfeed = new rss_feed();
            $rssfeed->open($_GET['rssfeed_id']);
            ploopi_create_user_action_log(_RSS_ACTION_DELETE, $rssfeed->fields['id']);
            $rssfeed->delete();
        }
        ploopi_redirect($scriptenv);
    break;


    case 'clean_feeds':

        echo $delete =  "
                    DELETE FROM ploopi_mod_rssfeed
                    WHERE       ploopi_mod_rssfeed.id_module = {$_SESSION['ploopi']['moduleid']}
                    AND         ploopi_mod_rssfeed.id_workspace IN ($groups)
                    AND         ploopi_mod_rssfeed.error > 5
                    ";

        $result = $db->query($delete);
        ploopi_redirect($scriptenv);

    break;


    case 'update_all_feeds':
        ini_set('max_execution_time',1200);

        $select =   "
                SELECT      ploopi_mod_rssfeed.*
                FROM        ploopi_mod_rssfeed
                LEFT JOIN   ploopi_mod_rsscat ON ploopi_mod_rsscat.id = ploopi_mod_rssfeed.id_cat
                AND     ploopi_mod_rsscat.id_workspace IN ($groups)
                WHERE       ploopi_mod_rssfeed.id_module = {$_SESSION['ploopi']['moduleid']}
                AND         ploopi_mod_rssfeed.id_workspace IN ($groups)
                ";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            echo "&nbsp;-&nbsp;{$fields['title']}...";
            rss_updatecache($fields['id'], $fields['url']);
            echo "updated<BR>";
        }
        ploopi_redirect($scriptenv);
    break;

    case 'update_outdated_feeds':
        ini_set('max_execution_time',1200);

        $select =   "
                SELECT      ploopi_mod_rssfeed.*
                FROM        ploopi_mod_rssfeed
                LEFT JOIN   ploopi_mod_rsscat ON ploopi_mod_rsscat.id = ploopi_mod_rssfeed.id_cat
                AND     ploopi_mod_rsscat.id_workspace IN ($groups)
                WHERE       ploopi_mod_rssfeed.id_module = {$_SESSION['ploopi']['moduleid']}
                AND         ploopi_mod_rssfeed.id_workspace IN ($groups)
                ";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            echo "&nbsp;-&nbsp;{$fields['title']}...";
            if (!rss_isuptodate($fields))
            {
                rss_updatecache($fields['id'], $fields['url']);
                echo "updated<BR>";
            }
            else echo "not updated<BR>";
        }

        ploopi_redirect($scriptenv);
    break;

}



$tabs['tabFeedList'] = array(   'title' => _RSS_LABEL_FEEDLIST,
                                'url' => "{$scriptenv}?rssTabItem=tabFeedList"
                                );

$tabs['tabFeedAdd'] = array(    'title' => _RSS_LABEL_FEEDADD,
                                'url' => "{$scriptenv}?rssTabItem=tabFeedAdd"
                            );

$tabs['tabCatList'] = array(    'title' => _RSS_LABEL_CATLIST,
                                'url' => "{$scriptenv}?rssTabItem=tabCatList"
                                );

if (ploopi_isactionallowed(_RSS_ACTION_CATADD))
{
    $tabs['tabCatAdd'] = array( 'title' => _RSS_LABEL_CATADD,
                                'url' => "{$scriptenv}?rssTabItem=tabCatAdd"
                                );
}

if (ploopi_isactionallowed(0))
{
    $tabs['tabTools'] = array(  'title' => _RSS_LABEL_TOOLS,
                                'url' => "{$scriptenv}?rssTabItem=tabTools"
                                );
}

if (!empty($_GET['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = $_GET['rssTabItem'];
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = '';

echo $skin->create_pagetitle(str_replace("LABEL",$_SESSION['ploopi']['modulelabel'],_RSS_PAGE_TITLE));
echo $skin->create_tabs($tabs,$_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem']);

switch($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])
{
    case 'tabCatList':
        include './modules/rss/admin_rsscat_list.php';
    break;

    case 'tabCatAdd':
        if (ploopi_isactionallowed(_RSS_ACTION_CATADD))
        {
            include_once './modules/rss/class_rss_cat.php';
            $rsscat = new rss_cat();
            $rsscat->init_description();
            include './modules/rss/admin_rsscat_form.php';
        }
    break;

    case 'tabFeedList':
        include './modules/rss/admin_rssfeed_list.php';
    break;

    case 'tabFeedAdd':
        if (ploopi_isactionallowed(_RSS_ACTION_FEEDADD))
        {
            include_once './modules/rss/class_rss_feed.php';
            $rssfeed = new rss_feed();
            $rssfeed->init_description();
            $rssfeed->fields['revisit'] = '3600';
            include './modules/rss/admin_rssfeed_form.php';
        }
    break;

    case 'tabTools':
        if (ploopi_isactionallowed(0))
        {
            include './modules/rss/admin_tools.php';
        }
    break;
}
?>
