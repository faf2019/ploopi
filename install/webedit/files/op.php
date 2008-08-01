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
 * Op�rations
 *
 * @package webedit
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */


switch($_REQUEST['ploopi_op'])
{
    case 'webedit_subscribe':
        ploopi_init_module('webedit');
        
        $return = -1;
        
        if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']) && !empty($_POST['subscription_headingid']) && is_numeric($_POST['subscription_headingid']))
        {
            if (!empty($_POST['subscription_email']) && ploopi_checkemail($_POST['subscription_email']))
            {
                include_once './modules/webedit/class_heading_subscriber.php';
                
                $subscriber = new webedit_heading_subscriber();
                
                if (!$subscriber->open($_POST['subscription_headingid'], $_POST['subscription_email']))
                {
                    $subscriber->fields['id_heading'] = $_POST['subscription_headingid'];
                    $subscriber->fields['email'] = $_POST['subscription_email'];
                    $subscriber->fields['validated'] = 1;
                    $subscriber->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
                    $subscriber->save();
                    $return = _WEBEDIT_SUBSCRIPTION_SUBSCRIBED;
                }
                else 
                {
                    $subscriber->delete();
                    $return = _WEBEDIT_SUBSCRIPTION_UNSUBSCRIBED;
                }
            }
            else $return = _WEBEDIT_SUBSCRIPTION_ERROR_EMAIL;
        }
        else $return = _WEBEDIT_SUBSCRIPTION_ERROR_FATAL;
        
        ploopi_redirect("index.php?headingid={$_GET['headingid']}".(empty($_GET['articleid']) ? '' : "&articleid={$_GET['articleid']}")."&subscription_return={$return}");
    break;
}

/**
 * Op�rations accessibles pour les utilisateurs connect�s
 */
if ($_SESSION['ploopi']['connected'])
{
    switch($_REQUEST['ploopi_op'])
    {
        case 'webedit_detail_heading':
            ploopi_init_module('webedit');
            if (!empty($_GET['hid']))
            {
                $option = (empty($_GET['option'])) ? '' : $_GET['option'];
                
                $treeview = webedit_gettreeview($option);
                echo $skin->display_treeview($treeview['list'], $treeview['tree'], null, $_GET['hid']);
            }
            ploopi_die();
        break;
    
        case 'webedit_switchdisplay_treeview':
            if (!empty($_GET['display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = $_GET['display'];
            ploopi_die();
        break;         
        
        case 'webedit_selectlink':
        case 'webedit_detail_heading';
            ob_start();
            include_once './modules/webedit/fck_link.php';
            $main_content = ob_get_contents();
            @ob_end_clean();
    
            $template_body->assign_vars(array(
                'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
                'PAGE_CONTENT'          => $main_content
                )
            );
    
            $template_body->assign_block_vars('module_css',array(
                                                        'PATH' => "./modules/webedit/include/styles.css"
                                                    )
                                            );
    
            $template_body->assign_block_vars('module_css_ie',array(
                                                        'PATH' => "./modules/webedit/include/styles_ie.css"
                                                    )
                                            );
    
            $template_body->pparse('body');
            ploopi_die();
        break;
        
        case 'webedit_heading_selectredirect':
            ob_start();
            ploopi_init_module('webedit');
            ?>
            <div style="padding:4px;height:150px;overflow:auto;">
            <?
            $treeview = webedit_gettreeview('selectredirect');
            echo $skin->display_treeview($treeview['list'], $treeview['tree']);
            ?>
            </div>
            <?
            $content = ob_get_contents();
            ob_end_clean();
            
            echo $skin->create_popup('Choix d\'une page', $content, 'webedit_popup_selectredirect');
    
            ploopi_die();
        break;
        
        case 'webedit_article_selectheading':
            ob_start();
            ploopi_init_module('webedit');
            ?>
            <div style="padding:4px;height:150px;overflow:auto;">
            <?
            $hid = (empty($_GET['hid'])) ? '' : 'hh'.$_GET['hid'];
            
            $treeview = webedit_gettreeview('selectheading');
            echo $skin->display_treeview($treeview['list'], $treeview['tree'],$hid);
            ?>
            </div>
            <?
            $content = ob_get_contents();
            ob_end_clean();
            
            echo $skin->create_popup('Choix d\'une rubrique de rattachement', $content, 'webedit_popup_selectheading');
    
            ploopi_die();
        break;
        
        case 'webedit_getbackup':
            include_once './modules/webedit/class_article_backup.php';
    
            $article_backup = new webedit_article_backup();
            if (!empty($_GET['backup_id_article']) && !empty($_GET['backup_timestp']) && is_numeric($_GET['backup_id_article']) && is_numeric($_GET['backup_timestp']) && $article_backup->open($_GET['backup_id_article'],$_GET['backup_timestp']))
            {
                echo $article_backup->fields['content'];
            }
            ploopi_die();
        break;
        
        case 'webedit_article_stats':
            if (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id'])) ploopi_die();
            
            $intYearSel = (empty($_POST['webedit_yearsel']) || !is_numeric($_POST['webedit_yearsel'])) ? date('Y') : $_POST['webedit_yearsel'];
            $intMonthSel = (empty($_POST['webedit_monthsel']) || empty($_POST['webedit_yearsel'])  || !is_numeric($_POST['webedit_monthsel']) || !is_numeric($_POST['webedit_yearsel'])) ? '' : $_POST['webedit_monthsel'];
            
            include_once './modules/webedit/class_article.php';
            
            $objArticle = new webedit_article();
            $objArticle->open($_POST['webedit_article_id']);
            
            $db->query("SELECT distinct(year) FROM ploopi_mod_webedit_counter WHERE id_article = {$_POST['webedit_article_id']} ORDER BY year");
            $arrSelectYear = $db->getarray();
            
            $db->query("SELECT distinct(month) FROM ploopi_mod_webedit_counter WHERE id_article = {$_POST['webedit_article_id']} AND year = {$intYearSel} ORDER BY month");
            $arrSelectMonth = $db->getarray();
            
            // aucun mois s�lectionn�
            if (empty($intMonthSel))
            {
                // ann�e en cours
                if ($intYearSel == date('Y')) $intMonthSel = date('n');
                else $intMonthSel = current($arrSelectMonth);
            }
            
            ob_start();
            ?>
            <div id="webedit_stats_select">
                <p>
                    <strong>Ann�e:</strong>
                    <?
                    foreach($arrSelectYear as $year)
                    {
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:webedit_stats_refresh(<? echo $_POST['webedit_article_id']; ?>, <? echo $year; ?>);" <? if ($year == $intYearSel) echo 'class="selected"'; ?>><? echo $year; ?></a>
                        <?
                    }
                    ?>
                </p>
                <p>
                    <strong>Mois:</strong>
                    <?
                    foreach($arrSelectMonth as $month)
                    {
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:webedit_stats_refresh(<? echo $_POST['webedit_article_id']; ?>, <? echo $intYearSel; ?>, <? echo $month; ?>);" <? if ($month == $intMonthSel) echo 'class="selected"'; ?>><? echo $ploopi_months[$month]; ?></a>
                        <?
                    }
                    ?>
                </p>
            </div>
            <?
            include_once './include/classes/barchart.php';
            
            // 1er Diagramme : ann�e par mois
            
            $dataset = array();
            $legend = array();
            
            foreach($ploopi_months as $key => $value) 
            {
                $dataset[$key] = 0;
                $legend[$key] = substr($value,0,3);
            }
            
            $db->query(
                "
                SELECT  month, 
                        sum(hits) as c 
                FROM    ploopi_mod_webedit_counter 
                WHERE   id_article = {$_POST['webedit_article_id']} 
                AND     year = {$intYearSel}
                GROUP BY month
                ORDER BY month
                ");
                
            while ($row = $db->fetchrow()) $dataset[$row['month']] = $row['c'];

            $objBarChartYear = new barchart(550, 100);
            $objBarChartYear->setvalues($dataset);
            $objBarChartYear->setlegend($legend);
            
            // 1er Diagramme : mois par jours
            
            $dataset = array();
            $legend = array();
            
            $nbdays = date('t', mktime(0, 0, 0, $intMonthSel, 1, $intYearSel));
            
            for ($d=1;$d<=$nbdays;$d++) 
            {
                $dataset[$d] = 0;
                $legend[$d] = $d;
            }
            
            $db->query(
                "
                SELECT  day, 
                        sum(hits) as c 
                FROM    ploopi_mod_webedit_counter 
                WHERE   id_article = {$_POST['webedit_article_id']} 
                AND     year = {$intYearSel}
                AND     month = {$intMonthSel}
                GROUP BY month
                ORDER BY month
                ");
                
            while ($row = $db->fetchrow()) $dataset[$row['day']] = $row['c'];

            $objBarChartMonth = new barchart(550, 100);
            $objBarChartMonth->setvalues($dataset);
            $objBarChartMonth->setlegend($legend);
            
            // Affichage
            ?>
            <div class="webedit_stats_graph">
                <h1>Statistiques de fr�quentation pour <em><? echo $intYearSel ?></em></h1>
                <div><? $objBarChartYear->draw(); ?></div>
            </div>
            <div class="webedit_stats_graph">
                <h1>Statistiques de fr�quentation pour <em><? echo $ploopi_months[$intMonthSel] ?> <? echo $intYearSel ?></em></h1>
                <div><? $objBarChartMonth->draw(); ?></div>
            </div>
            <?
            $content = ob_get_contents();
            ob_end_clean();
            
            echo $skin->create_popup("Statistiques de fr�quentation de l'article &laquo; ".htmlentities($objArticle->fields['title'])." &raquo;", $content, 'popup_webedit_article_stats');
    
            
            ploopi_die();
        break;
            
        
        
    }
}