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
 * @package news
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('news');

include_once './modules/news/class_news_entry.php';
include_once './modules/news/class_news_cat.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

switch($op)
{
    /*
    case 'image_galery':
        $explorer_type = 'image_galery';
        include_once './modules/doc/fck_imagegalery.php';
    break;

    case 'article_selectfile':
        include_once './modules/doc/fck_imagegalery.php';
    break;
    */

    case 'article_selectlink':
        $headings = wce_getheadings();
        $articles = wce_getarticles();
        echo wce_build_tree($headings, $articles, 0, '', 1, 'selectlink');
    break;

    case 'save_newscat':
        $newscat = new newscat();
        if (!empty($_POST['newscat_id']) && is_numeric($_POST['newscat_id'])) $newscat->open($_POST['newscat_id']);
        else $newscat->setuwm();

        $newscat->setvalues($_POST,'newscat_');
        $newscat->save();
        ploopi_create_user_action_log(_NEWS_ACTION_MANAGE, $newscat->fields['id']);

        ploopi_redirect("admin.php?newsTabItem=tabNewsCatModify&newscat_id={$newscat->fields['id']}");
    break;

    case 'delete_newscat':
        if (!empty($_GET['newscat_id']) && is_numeric($_GET['newscat_id']))
        {
            $newscat = new newscat();
            $newscat->open($_GET['newscat_id']);
            ploopi_create_user_action_log(_NEWS_ACTION_MANAGE, $newscat->fields['id']);
            $newscat->delete();
        }
        ploopi_redirect("admin.php?newsTabItem=tabNewsCatModify");
    break;

    case 'save_news':
        $news = new news();
        if (!empty($_POST['news_id']) && is_numeric($_POST['news_id'])) $news->open($_POST['news_id']);
        else $news->setuwm();

        $news->setvalues($_POST,'news_');

        if (isset($_POST['fck_news_content'])) $news->fields['content'] = $_POST['fck_news_content'];

        if (isset($_POST['news_date_publish'])) $news->fields['date_publish'] = ploopi_local2timestamp($_POST['news_date_publish'], $_POST['newsx_time_publish']);

        $news->save();

        if ($news->new) ploopi_create_user_action_log(_NEWS_ACTION_WRITE, $news->fields['id']);
        else ploopi_create_user_action_log(_NEWS_ACTION_MODIFY, $news->fields['id']);

        ploopi_redirect("admin.php?newsTabItem=tabNewsModify&news_id={$news->fields['id']}");
    break;

    case 'publish_news':
        if (!empty($_GET['news_id']) && is_numeric($_GET['news_id']) && ploopi_isactionallowed(_NEWS_ACTION_PUBLISH))
        {
            $news = new news();
            $news->open($_GET['news_id']);
            $news->fields['published'] = 1;
            $news->save();

            ploopi_create_user_action_log(_NEWS_ACTION_PUBLISH, $news->fields['id']);
        }
        ploopi_redirect("admin.php?newsTabItem=tabNewsModify");
    break;

    case 'withdraw_news':
        if (!empty($_GET['news_id']) && is_numeric($_GET['news_id']) && ploopi_isactionallowed(_NEWS_ACTION_PUBLISH))
        {
            $news = new news();
            $news->open($_GET['news_id']);
            $news->fields['published'] = 0;
            $news->save();
        }
        ploopi_redirect("admin.php?newsTabItem=tabNewsModify");
    break;

    case 'delete_news':
        if (!empty($_GET['news_id']) && is_numeric($_GET['news_id']) && ploopi_isactionallowed(_NEWS_ACTION_DELETE))
        {
            $news = new news();
            if ($news->open($_GET['news_id']))
            {
                ploopi_create_user_action_log(_NEWS_ACTION_DELETE, $news->fields['id']);
                $news->delete();
            }
        }
        
        ploopi_redirect("admin.php");
    break;

    default:

        if (ploopi_isactionallowed(_NEWS_ACTION_MODIFY) || ploopi_isactionallowed(_NEWS_ACTION_PUBLISH))
            $tabs['tabNewsModify'] = array( 'title' => _NEWS_LABELTAB_NEWS, 'url' => "admin.php?newsTabItem=tabNewsModify");
        if (ploopi_isactionallowed(_NEWS_ACTION_WRITE))
            $tabs['tabNewsWrite'] = array('title'   => _NEWS_LABELTAB_NEWS_WRITE, 'url' => "admin.php?newsTabItem=tabNewsWrite");

        if (ploopi_isactionallowed(_NEWS_ACTION_MANAGECAT))
        {
            $tabs['tabNewsCatModify'] = array('title' => _NEWS_LABELTAB_CAT, 'url' => "admin.php?newsTabItem=tabNewsCatModify");
            $tabs['tabNewsCatWrite'] = array('title' => _NEWS_LABELTAB_CAT_WRITE, 'url' => "admin.php?newsTabItem=tabNewsCatWrite");
        }

        if (!empty($_GET['newsTabItem'])) $_SESSION['news']['newsTabItem'] = $_GET['newsTabItem'];
        if (!isset($_SESSION['news']['newsTabItem'])) $_SESSION['news']['newsTabItem'] = '';

        echo $skin->create_pagetitle(str_replace("LABEL",$_SESSION['ploopi']['modulelabel'],_NEWS_PAGE_TITLE));
        echo $skin->create_tabs($tabs,$_SESSION['news']['newsTabItem']);

        switch($op)
        {
            case 'modify_newscat':
                $newscat = new newscat();
                include './modules/news/admin_newscat_modify.php';
            break;

            default:
                switch($_SESSION['news']['newsTabItem'])
                {
                    case 'tabNewsModify':
                        $news = new news();
                        include './modules/news/admin_news_modify.php';
                    break;

                    case 'tabNewsWrite':
                        $news = new news();
                        $news->init_description();
                        include './modules/news/admin_news_write.php';
                    break;

                    case 'tabNewsCatModify':
                        if (ploopi_isactionallowed(_NEWS_ACTION_MANAGECAT))
                        {
                            $newscat = new newscat();
                            include './modules/news/admin_newscat_modify.php';
                        }
                    break;

                    case 'tabNewsCatWrite':
                        if (ploopi_isactionallowed(_NEWS_ACTION_MANAGECAT))
                        {
                            $newscat = new newscat();
                            $newscat->init_description();
                            include './modules/news/admin_newscat_write.php';
                        }
                    break;
                }
            break;
        }
    break;
}

?>
