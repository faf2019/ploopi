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
 * Op�rations
 *
 * @package webedit
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

switch($ploopi_op)
{
    case 'webedit_unsubscribe':
        // D�sabonnement par mail

        ploopi_init_module('webedit');

        if (!empty($_GET['subscription_email']))
        {
            include_once './include/functions/crypt.php';
            $db->query("DELETE FROM ploopi_mod_webedit_heading_subscriber WHERE md5(email) = '".$db->addslashes($_GET['subscription_email'])."'");
        }
        //ploopi_redirect('index.php', false);
    break;

    case 'webedit_subscribe':
        ploopi_init_module('webedit');

        $return = -1;

        if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']) && isset($_POST['subscription_headingid']) && is_numeric($_POST['subscription_headingid']))
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
        else $return = _WEBEDIT_SUBSCRIPTION_ERROR_PARAM;

        ploopi_redirect("index.php?".(empty($_GET['webedit_mode']) ? '' : "webedit_mode={$_GET['webedit_mode']}&")."headingid={$_GET['headingid']}".(empty($_GET['articleid']) ? '' : "&articleid={$_GET['articleid']}")."&subscription_return={$return}");
    break;

    case 'webedit_sitemap':
        ploopi_init_module('webedit', false, false, false);
        webedit_sitemap();
        ploopi_die();
    break;

    case 'webedit_backend':
        include_once './modules/webedit/backend.php';
        ploopi_die();
    break;

    case 'webedit_save_comment':
        if(isset($_GET['articleid']) && is_numeric($_GET['articleid']) && isset($_GET['id_captcha']) && !empty($_GET['id_captcha']))
        {

            // Ultime verif du captcha
            include_once './include/classes/captcha.php';
            $objcaptcha = new captcha($_GET['id_captcha']);
            if(!$objcaptcha->verifCaptcha($_POST['captcha_code'],true)) return '0';

            include_once './modules/webedit/class_article_comment.php';
            $objComment = new webedit_article_comment();

            $objComment->setvalues($_POST,'comment_');
            $objComment->fields['id_article'] = $_GET['articleid'];
            if($objComment->save())
                $return = ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['comment_ctrl']) ? '2' : '1';
            else
                return '0';

        }

        $arrHeadings = webedit_getheadings();
        if (isset($arrHeadings['list'][$_GET['headingid']])) foreach(preg_split('/;/', $arrHeadings['list'][$_GET['headingid']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
        ploopi_redirect(ploopi_urlrewrite("index.php?headingid={$_GET['headingid']}".(empty($_GET['articleid']) ? '' : "&articleid={$_GET['articleid']}")."&comment_return={$return}",webedit_getrewriterules(),$arrHeadings['list'][$_GET['headingid']]['label'], $arrParents));
    break;
}

/**
 * Op�rations accessibles pour les utilisateurs connect�s
 */
if ($_SESSION['ploopi']['connected'])
{
    switch($ploopi_op)
    {

        case 'webedit_subscriber_delete':
            ploopi_init_module('webedit', false, false, false);
            include_once './modules/webedit/class_heading_subscriber.php';
            if (ploopi_isactionallowed(_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE) && !empty($_GET['webedit_subscriber_email']) && isset($_GET['webedit_subscriber_id_heading']) && is_numeric($_GET['webedit_subscriber_id_heading']))
            {
                $heading_subscriber = new webedit_heading_subscriber();
                if($heading_subscriber->open($_GET['webedit_subscriber_id_heading'], $_GET['webedit_subscriber_email'])) $heading_subscriber->delete();
            }
            ploopi_redirect('admin.php');
        break;

        case 'webedit_selectlink':
        case 'webedit_detail_heading';
            ob_start();
            include_once './modules/webedit/fck_link.php';
            $main_content = ob_get_contents();
            @ob_end_clean();

            $template_body->assign_vars(array(
                'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                'ADDITIONAL_JAVASCRIPT' => $ploopi_additional_javascript,
                'PAGE_CONTENT'          => $main_content
                )
            );

            $template_body->assign_block_vars('module_css',
                array(
                    'PATH' => "./modules/webedit/include/styles.css"
                )
            );

            $template_body->assign_block_vars('module_css_ie',
                array(
                     'PATH' => "./modules/webedit/include/styles_ie.css"
                )
            );

            $template_body->pparse('body');
            ploopi_die();
        break;
    }

    /**
     * On v�rifie qu'on est bien dans le module WebEdit.
     * Ces op�rations ne peuvent �tre effectu�es que depuis le module WebEdit.
     */
    if (ploopi_ismoduleallowed('webedit'))
    {
        switch($_REQUEST['ploopi_op'])
        {
            case 'webedit_detail_heading':
                ploopi_init_module('webedit');
                if (!empty($_GET['hid']))
                {
                    $option = (empty($_GET['option'])) ? '' : $_GET['option'];

                    $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), $option);
                    echo $skin->display_treeview($treeview['list'], $treeview['tree'], null, $_GET['hid']);
                }
                ploopi_die();
            break;

            case 'webedit_switchdisplay_treeview':
                if (!empty($_GET['display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = $_GET['display'];
                ploopi_die();
            break;

            case 'webedit_heading_selectredirect':
                ob_start();
                ploopi_init_module('webedit');
                ?>
                <div style="padding:4px;height:150px;overflow:auto;">
                <?php
                $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), 'selectredirect');
                echo $skin->display_treeview($treeview['list'], $treeview['tree']);
                ?>
                </div>
                <?php
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
                <?php
                $hid = (empty($_GET['hid'])) ? '' : 'hh'.$_GET['hid'];

                $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), 'selectheading');
                echo $skin->display_treeview($treeview['list'], $treeview['tree'],$hid);
                ?>
                </div>
                <?php
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
                ploopi_init_module('webedit');

                // pas le droit => stop
                if (!ploopi_isactionallowed(_WEBEDIT_ACTION_STATS)) ploopi_die();

                // ni article, ni rubrique => stop
                if ( (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id'])) && (empty($_POST['webedit_heading_id']) || !is_numeric($_POST['webedit_heading_id'])) ) ploopi_die();

                $type_stat = (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id'])) ? 'heading' : 'article';

                $intYearSel = (empty($_POST['webedit_yearsel']) || !is_numeric($_POST['webedit_yearsel'])) ? date('Y') : $_POST['webedit_yearsel'];
                $intMonthSel = (empty($_POST['webedit_monthsel']) || empty($_POST['webedit_yearsel'])  || !is_numeric($_POST['webedit_monthsel']) || !is_numeric($_POST['webedit_yearsel'])) ? '' : $_POST['webedit_monthsel'];

                switch($type_stat)
                {
                    case 'article':
                        include_once './modules/webedit/class_article.php';

                        $objArticle = new webedit_article();
                        if (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id']) || !$objArticle->open($_POST['webedit_article_id'])) ploopi_die();

                        $intArticleId = $_POST['webedit_article_id'];
                        $intHeadingId = 'null';

                        $db->query("SELECT distinct(year) FROM ploopi_mod_webedit_counter WHERE id_article = {$intArticleId} ORDER BY year");
                        $rs = $arrSelectYear = $db->getarray($rs, true);

                        $rs = $db->query("SELECT distinct(month) FROM ploopi_mod_webedit_counter WHERE id_article = {$intArticleId} AND year = {$intYearSel} ORDER BY month");
                        $arrSelectMonth = $db->getarray($rs, true);

                        $strPopupTitle = "Statistiques de fr�quentation de l'article &laquo; ".ploopi_htmlentities($objArticle->fields['title'])." &raquo;";
                    break;

                    case 'heading':
                        include_once './modules/webedit/class_heading.php';

                        $objHeading = new webedit_heading();
                        if (empty($_POST['webedit_heading_id']) || !is_numeric($_POST['webedit_heading_id']) || !$objHeading->open($_POST['webedit_heading_id'])) ploopi_die();

                        $intArticleId = 'null';
                        $intHeadingId = $_POST['webedit_heading_id'];

                        $rs = $db->query(
                            "
                            SELECT      distinct(c.year)

                            FROM        ploopi_mod_webedit_counter c

                            INNER JOIN  ploopi_mod_webedit_article a
                            ON          a.id = c.id_article
                            AND         a.id_heading = {$intHeadingId}

                            ORDER BY    c.year
                            "
                        );

                        $arrSelectYear = $db->getarray($rs, true);

                        $rs = $db->query(
                            "
                            SELECT      distinct(c.month)

                            FROM        ploopi_mod_webedit_counter c

                            INNER JOIN  ploopi_mod_webedit_article a
                            ON          a.id = c.id_article
                            AND         a.id_heading = {$intHeadingId}

                            WHERE       c.year = {$intYearSel}

                            ORDER BY    c.month
                            "
                        );

                        $arrSelectMonth = $db->getarray($rs, true);

                        $strPopupTitle = "Statistiques de fr�quentation de la rubrique &laquo; ".ploopi_htmlentities($objHeading->fields['label'])." &raquo;";
                    break;
                }

                // aucun mois s�lectionn�
                if (empty($intMonthSel))
                {
                    // ann�e en cours
                    if ($intYearSel == date('Y')) $intMonthSel = date('n');
                    else $intMonthSel = current($arrSelectMonth);
                }

                ob_start();
                ?>
                <div id="webedit_stats">
                    <div id="webedit_stats_select">
                        <p>
                            <strong>Ann�e:</strong>
                            <?php
                            foreach($arrSelectYear as $year)
                            {
                                ?>
                                <a href="javascript:void(0);" onclick="javascript:webedit_stats_refresh(<?php echo $intArticleId; ?>, <?php echo $intHeadingId; ?>, <?php echo $year; ?>);" <?php if ($year == $intYearSel) echo 'class="selected"'; ?>><?php echo $year; ?></a>
                                <?php
                            }
                            ?>
                        </p>
                        <p>
                            <strong>Mois:</strong>
                            <?php
                            foreach($arrSelectMonth as $month)
                            {
                                ?>
                                <a href="javascript:void(0);" onclick="javascript:webedit_stats_refresh(<?php echo $intArticleId; ?>, <?php echo $intHeadingId; ?>, <?php echo $intYearSel; ?>, <?php echo $month; ?>);" <?php if ($month == $intMonthSel) echo 'class="selected"'; ?>><?php echo $ploopi_months[$month]; ?></a>
                                <?php
                            }
                            ?>
                        </p>
                    </div>
                    <?php
                    include_once './include/classes/barchart.php';

                    // 1er Diagramme : ann�e par mois

                    $dataset = array();
                    $legend = array();

                    foreach($ploopi_months as $key => $value)
                    {
                        $dataset[$key] = 0;
                        $legend[$key] = substr($value,0,3);
                    }

                    switch($type_stat)
                    {
                        case 'article':
                            $db->query(
                                "
                                SELECT  month,
                                        sum(hits) as counter

                                FROM    ploopi_mod_webedit_counter

                                WHERE   id_article = {$_POST['webedit_article_id']}
                                AND     year = {$intYearSel}

                                GROUP BY month
                                ORDER BY month
                                "
                            );
                        break;

                        case 'heading':
                            $db->query(
                                "
                                SELECT      c.month,
                                            sum(c.hits) as counter

                                FROM        ploopi_mod_webedit_counter c

                                INNER JOIN  ploopi_mod_webedit_article a
                                ON          a.id = c.id_article
                                AND         a.id_heading = {$_POST['webedit_heading_id']}

                                WHERE       c.year = {$intYearSel}

                                GROUP BY    c.month
                                ORDER BY    c.month
                                "
                            );
                        break;
                    }

                    while ($row = $db->fetchrow()) $dataset[$row['month']] = $row['counter'];

                    $objBarChartYear = new barchart(550, 150, array('padding' => 1));
                    $objBarChartYear->setvalues($dataset, 'Fr�quentation mensuelle', '#1E64A1', '#f0f0f0');
                    $objBarChartYear->setlegend($legend);

                    // 1er Diagramme : mois par jours

                    $dataset = array();
                    $legend = array();

                    $nbdays = date('t', mktime(0, 0, 0, $intMonthSel, 1, $intYearSel));

                    for ($d=1;$d<=$nbdays;$d++)
                    {
                        $weekday = date('N', mktime(0, 0, 0, $intMonthSel, $d, $intYearSel));
                        $dataset[$d] = 0;
                        $legend[$d] = substr($ploopi_days[$weekday],0,2).'<br />'.$d;
                    }

                    switch($type_stat)
                    {
                        case 'article':
                            $db->query(
                                "
                                SELECT  c.day,
                                        sum(c.hits) as counter

                                FROM    ploopi_mod_webedit_counter c

                                WHERE   c.id_article = {$_POST['webedit_article_id']}
                                AND     c.year = {$intYearSel}
                                AND     c.month = {$intMonthSel}

                                GROUP BY c.day
                                ORDER BY c.day
                                "
                            );
                        break;

                        case 'heading':
                            $db->query(
                                "
                                SELECT      c.day,
                                            sum(c.hits) as counter

                                FROM        ploopi_mod_webedit_counter c

                                INNER JOIN  ploopi_mod_webedit_article a
                                ON          a.id = c.id_article
                                AND         a.id_heading = {$_POST['webedit_heading_id']}

                                WHERE       c.year = {$intYearSel}
                                AND         c.month = {$intMonthSel}

                                GROUP BY    c.day
                                ORDER BY    c.day
                                "
                            );
                        break;
                    }

                    while ($row = $db->fetchrow()) $dataset[$row['day']] = $row['counter'];

                    $objBarChartMonth = new barchart(550, 150, array('padding' => 1));
                    $objBarChartMonth->setvalues($dataset, 'Fr�quentation quotidienne', '#4FA11E', '#f0f0f0');
                    $objBarChartMonth->setlegend($legend);

                    // Affichage
                    ?>
                    <div class="webedit_stats_graph">
                        <h1>Statistiques de fr�quentation pour <em><?php echo $intYearSel ?></em> (nombre de visites)</h1>
                        <div><?php $objBarChartYear->draw(); ?></div>
                    </div>
                    <div class="webedit_stats_graph">
                        <h1>Statistiques de fr�quentation pour <em><?php echo $ploopi_months[$intMonthSel] ?> <?php echo $intYearSel ?></em> (nombre de visites)</h1>
                        <div><?php $objBarChartMonth->draw(); ?></div>
                    </div>
                </div>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                echo $skin->create_popup($strPopupTitle, $content, 'popup_webedit_article_stats');

                ploopi_die();
            break;
        case 'webedit_comment_refresh':
            if (isset($_GET['id_article']))
            {
                ploopi_init_module('webedit',false,false,false);

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                $objComment->admin_comment_refresh($_GET['id_article']);
            }
            ploopi_die();
            break;
        case 'webedit_comment_publish':
            if (isset($_GET['id_comment']) && isset($_GET['publish']))
            {
                ploopi_init_module('webedit',false,false,false);

                if(!ploopi_isactionallowed(_WEBEDIT_ACTION_COMMENT)) ploopi_die();

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                if($objComment->open($_GET['id_comment']))
                    $objComment->publish($_GET['publish']==1);
            }
            ploopi_die();
            break;
        case 'webedit_comment_delete':
            if (isset($_GET['id_comment']))
            {
                ploopi_init_module('webedit',false,false,false);

                if(!ploopi_isactionallowed(_WEBEDIT_ACTION_COMMENT)) ploopi_die();

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                if($objComment->open($_GET['id_comment'])) $objComment->delete();
            }
            ploopi_die();
            break;
        case 'webedit_comment_show':
            if (isset($_GET['id_article']))
            {
                if (isset($_SESSION['ploopi']['comment']['show'][$_GET['id_article']])) unset($_SESSION['ploopi']['comment']['show'][$_GET['id_article']]);
                else $_SESSION['ploopi']['comment']['show'][$_GET['id_article']] = 1;
            }
            ploopi_die();
            break;
        }
    }
}
