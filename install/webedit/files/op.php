<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Opérations
 *
 * @package webedit
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

switch($ploopi_op)
{
    case 'webedit_unsubscribe':
        // Désabonnement par le lien signé des courriels (message : display.php)

        ploopi\module::init('webedit');

        if (isset($_GET['subscription_token']) && ($arrDemande = webedit_subscription_read($_GET['subscription_token'], 'u')) !== false)
        {
            $db = ploopi\db::get();
            $db->query("DELETE FROM ploopi_mod_webedit_heading_subscriber WHERE email = '".$db->addslashes($arrDemande['email'])."' AND id_module = {$arrDemande['moduleid']}");
        }
    break;

    case 'webedit_subscription_confirm':
        // Confirmation d'abonnement par le lien signé du courriel (message : display.php)

        ploopi\module::init('webedit');

        if (isset($_GET['subscription_token']) && ($arrDemande = webedit_subscription_read($_GET['subscription_token'], 'c')) !== false)
        {
            include_once './modules/webedit/class_heading_subscriber.php';

            $subscriber = new webedit_heading_subscriber();
            if (!$subscriber->open($arrDemande['headingid'], $arrDemande['email']))
            {
                $subscriber = new webedit_heading_subscriber();
                $subscriber->fields['id_heading'] = $arrDemande['headingid'];
                $subscriber->fields['email'] = $arrDemande['email'];
                $subscriber->fields['validated'] = 1;
                $subscriber->fields['id_module'] = $arrDemande['moduleid'];
                $subscriber->save();
            }
        }
    break;

    case 'webedit_subscribe':
        /**
         * Demande d'abonnement depuis le formulaire public : rien n'est écrit, un
         * courriel part vers l'adresse saisie. Il porte un lien de confirmation signé,
         * ou, si l'adresse est déjà abonnée, un lien de désabonnement. Seul le titulaire
         * de l'adresse peut donc abonner ou désabonner celle-ci (double opt-in).
         */
        ploopi\module::init('webedit');

        $return = -1;
        $intModuleId = $_SESSION['ploopi']['moduleid'];

        if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']) && isset($_POST['subscription_headingid']) && is_numeric($_POST['subscription_headingid']))
        {
            $intHeadingId = intval($_POST['subscription_headingid']);
            $db = ploopi\db::get();

            // La rubrique doit appartenir au site (0 : le site entier)
            if ($intHeadingId > 0 && !$db->numrows($db->query("SELECT id FROM ploopi_mod_webedit_heading WHERE id = {$intHeadingId} AND id_module = {$intModuleId}")))
            {
                $return = _WEBEDIT_SUBSCRIPTION_ERROR_PARAM;
            }
            elseif (!empty($_POST['subscription_email']) && is_string($_POST['subscription_email']) && ploopi\mail::check($_POST['subscription_email']))
            {
                $strEmail = $_POST['subscription_email'];
                $strBase = webedit_subscription_baseurl();

                if ($strBase === false)
                {
                    ploopi\error::syslog(LOG_WARNING, "Abonnement webedit : hôte '".preg_replace('/[^\x21-\x7e]/', '', substr((string)$_SERVER['HTTP_HOST'], 0, 255))."' non déclaré, aucun courriel envoyé. Renseigner _PLOOPI_PUBLIC_URL dans config/config.php ou le domaine dans l'espace");
                    $return = _WEBEDIT_SUBSCRIPTION_ERROR_SEND;
                }
                else
                {
                    include_once './modules/webedit/class_heading_subscriber.php';

                    $strSite = parse_url($strBase, PHP_URL_HOST);
                    $subscriber = new webedit_heading_subscriber();

                    if ($subscriber->open($intHeadingId, $strEmail))
                    {
                        $strLien = $strBase.'/'.ploopi\str::urlrewrite('index.php?ploopi_op=webedit_unsubscribe&subscription_token='.webedit_subscription_token('u', $intModuleId, 0, $strEmail), webedit_getrewriterules());
                        $strTitre = "{$strSite} : votre abonnement";
                        $strMessage = "Bonjour,\n\nune demande d'abonnement aux nouveautés du site {$strSite} a été faite pour votre adresse, qui y est déjà abonnée.\n\nPour vous désabonner, suivez ce lien :\n\n{$strLien}\n\nSinon, ignorez ce message.";
                    }
                    else
                    {
                        $strLien = $strBase.'/'.ploopi\str::urlrewrite('index.php?ploopi_op=webedit_subscription_confirm&subscription_token='.webedit_subscription_token('c', $intModuleId, $intHeadingId, $strEmail, time() + _WEBEDIT_SUBSCRIPTION_DELAY), webedit_getrewriterules());
                        $strTitre = "{$strSite} : confirmez votre abonnement";
                        $strMessage = "Bonjour,\n\nune demande d'abonnement aux nouveautés du site {$strSite} a été faite pour votre adresse.\n\nPour la confirmer, suivez ce lien (valable ".intval(_WEBEDIT_SUBSCRIPTION_DELAY / 3600)." heures) :\n\n{$strLien}\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message : vous ne serez pas abonné.";
                    }

                    $from = array(array('name' => $strSite, 'address' => trim(current(explode(',', _PLOOPI_ADMINMAIL)))));
                    ploopi\mail::send($from, $strEmail, $strTitre, $strMessage, null, null, null, null, false);

                    // Même réponse dans les deux cas : le formulaire ne révèle pas qui est abonné
                    $return = _WEBEDIT_SUBSCRIPTION_PENDING;
                }
            }
            else $return = _WEBEDIT_SUBSCRIPTION_ERROR_EMAIL;
        }
        else $return = _WEBEDIT_SUBSCRIPTION_ERROR_PARAM;

        ploopi\output::redirect("index.php?".(empty($_GET['webedit_mode']) ? '' : "webedit_mode={$_GET['webedit_mode']}&")."headingid={$_GET['headingid']}".(empty($_GET['articleid']) ? '' : "&articleid={$_GET['articleid']}")."&subscription_return={$return}");
    break;

    case 'webedit_sitemap':
        ploopi\module::init('webedit', false, false, false);
        webedit_sitemap();
        ploopi\system::kill();
    break;

    case 'webedit_backend':
        include_once './modules/webedit/backend.php';
        ploopi\system::kill();
    break;
}

/**
 * Opérations accessibles pour les utilisateurs connectés
 */
if ($_SESSION['ploopi']['connected'])
{
    switch($ploopi_op)
    {

        case 'webedit_subscriber_delete':
            ploopi\module::init('webedit', false, false, false);
            include_once './modules/webedit/class_heading_subscriber.php';
            if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE) && !empty($_GET['webedit_subscriber_email']) && isset($_GET['webedit_subscriber_id_heading']) && is_numeric($_GET['webedit_subscriber_id_heading']))
            {
                $heading_subscriber = new webedit_heading_subscriber();
                if($heading_subscriber->open($_GET['webedit_subscriber_id_heading'], $_GET['webedit_subscriber_email'])) $heading_subscriber->delete();
            }
            ploopi\output::redirect('admin.php');
        break;

        case 'webedit_selector':
            $template_body = ploopi\loader::get_template();

            ob_start();
            ?>
            <style>body, html, div { border:0;padding:0;margin:0;display:block;height:100%;overflow:hidden; }</style>
            <iframe style="height:100%;float:left;width:40%;border:0;border-right:1px solid #aaa;box-sizing:border-box;" src="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=webedit_selectlink&CKEditor=editor&CKEditorFuncNum='.(isset($_REQUEST['CKEditorFuncNum']) ? (int)$_REQUEST['CKEditorFuncNum'] : 0).'&langCode=fr'); ?>">
            </iframe>
            <iframe style="height:100%;float:left;width:60%;border:0;box-sizing:border-box;" src="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=doc_selectfile&CKEditor=editor&CKEditorFuncNum='.(isset($_REQUEST['CKEditorFuncNum']) ? (int)$_REQUEST['CKEditorFuncNum'] : 0).'&langCode=fr'); ?>">
            </iframe>
            <?php
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
            ploopi\system::kill();
        break;

        case 'webedit_selectlink':
        case 'webedit_detail_heading':
            $template_body = ploopi\loader::get_template();


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
            ploopi\system::kill();
        break;
    }

    /**
     * On vérifie qu'on est bien dans le module WebEdit.
     * Ces opérations ne peuvent être effectuées que depuis le module WebEdit.
     */
    if (ploopi\acl::ismoduleallowed('webedit'))
    {
        switch($_REQUEST['ploopi_op'])
        {
            case 'webedit_detail_heading':
                ploopi\module::init('webedit');
                if (!empty($_GET['hid']))
                {
                    $option = (empty($_GET['option'])) ? '' : $_GET['option'];

                    $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), $option);
                    echo ploopi\skin::get()->display_treeview($treeview['list'], $treeview['tree'], null, $_GET['hid']);
                }
                ploopi\system::kill();
            break;

            case 'webedit_switchdisplay_treeview':
                if (!empty($_GET['display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = $_GET['display'];
                ploopi\system::kill();
            break;

            case 'webedit_heading_selectredirect':
                ob_start();
                ploopi\module::init('webedit');
                ?>
                <div style="padding:4px;height:150px;overflow:auto;">
                <?php
                $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), 'selectredirect');
                echo ploopi\skin::get()->display_treeview($treeview['list'], $treeview['tree']);
                ?>
                </div>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                echo ploopi\skin::get()->create_popup('Choix d\'une page', $content, 'webedit_popup_selectredirect');

                ploopi\system::kill();
            break;

            case 'webedit_article_selectheading':
                ob_start();
                ploopi\module::init('webedit');
                ?>
                <div style="padding:4px;height:150px;overflow:auto;">
                <?php
                $hid = (empty($_GET['hid'])) ? '' : 'hh'.$_GET['hid'];

                $treeview = webedit_gettreeview(webedit_getheadings(), webedit_getarticles(), 'selectheading');
                echo ploopi\skin::get()->display_treeview($treeview['list'], $treeview['tree'],$hid);
                ?>
                </div>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                echo ploopi\skin::get()->create_popup('Choix d\'une rubrique de rattachement', $content, 'webedit_popup_selectheading');

                ploopi\system::kill();
            break;

            case 'webedit_getbackup':
                include_once './modules/webedit/class_article_backup.php';

                $article_backup = new webedit_article_backup();
                if (!empty($_GET['backup_id_article']) && !empty($_GET['backup_timestp']) && is_numeric($_GET['backup_id_article']) && is_numeric($_GET['backup_timestp']) && $article_backup->open($_GET['backup_id_article'],$_GET['backup_timestp']))
                {
                    echo $article_backup->fields['content'];
                }
                ploopi\system::kill();
            break;

            case 'webedit_article_stats':
                ploopi\module::init('webedit');

                // pas le droit => stop
                if (!ploopi\acl::isactionallowed(_WEBEDIT_ACTION_STATS)) ploopi\system::kill();

                // ni article, ni rubrique => stop
                if ( (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id'])) && (empty($_POST['webedit_heading_id']) || !is_numeric($_POST['webedit_heading_id'])) ) ploopi\system::kill();

                $type_stat = (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id'])) ? 'heading' : 'article';

                $intYearSel = (empty($_POST['webedit_yearsel']) || !is_numeric($_POST['webedit_yearsel'])) ? date('Y') : $_POST['webedit_yearsel'];
                $intMonthSel = (empty($_POST['webedit_monthsel']) || empty($_POST['webedit_yearsel'])  || !is_numeric($_POST['webedit_monthsel']) || !is_numeric($_POST['webedit_yearsel'])) ? '' : $_POST['webedit_monthsel'];

                switch($type_stat)
                {
                    case 'article':
                        include_once './modules/webedit/class_article.php';

                        $objArticle = new webedit_article();
                        if (empty($_POST['webedit_article_id']) || !is_numeric($_POST['webedit_article_id']) || !$objArticle->open($_POST['webedit_article_id'])) ploopi\system::kill();

                        $intArticleId = $_POST['webedit_article_id'];
                        $intHeadingId = 'null';

                        ploopi\db::get()->query("SELECT distinct(year) FROM ploopi_mod_webedit_counter WHERE id_article = {$intArticleId} ORDER BY year");
                        $rs = $arrSelectYear = ploopi\db::get()->getarray($rs, true);

                        $rs = ploopi\db::get()->query("SELECT distinct(month) FROM ploopi_mod_webedit_counter WHERE id_article = {$intArticleId} AND year = {$intYearSel} ORDER BY month");
                        $arrSelectMonth = ploopi\db::get()->getarray($rs, true);

                        $strPopupTitle = "Statistiques de fréquentation de l'article &laquo; ".ploopi\str::htmlentities($objArticle->fields['title'])." &raquo;";
                    break;

                    case 'heading':
                        include_once './modules/webedit/class_heading.php';

                        $objHeading = new webedit_heading();
                        if (empty($_POST['webedit_heading_id']) || !is_numeric($_POST['webedit_heading_id']) || !$objHeading->open($_POST['webedit_heading_id'])) ploopi\system::kill();

                        $intArticleId = 'null';
                        $intHeadingId = $_POST['webedit_heading_id'];

                        $rs = ploopi\db::get()->query(
                            "
                            SELECT      distinct(c.year)

                            FROM        ploopi_mod_webedit_counter c

                            INNER JOIN  ploopi_mod_webedit_article a
                            ON          a.id = c.id_article
                            AND         a.id_heading = {$intHeadingId}

                            ORDER BY    c.year
                            "
                        );

                        $arrSelectYear = ploopi\db::get()->getarray($rs, true);

                        $rs = ploopi\db::get()->query(
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

                        $arrSelectMonth = ploopi\db::get()->getarray($rs, true);

                        $strPopupTitle = "Statistiques de fréquentation de la rubrique &laquo; ".ploopi\str::htmlentities($objHeading->fields['label'])." &raquo;";
                    break;
                }

                // aucun mois sélectionné
                if (empty($intMonthSel))
                {
                    // année en cours
                    if ($intYearSel == date('Y')) $intMonthSel = date('n');
                    else $intMonthSel = current($arrSelectMonth);
                }

                ob_start();
                ?>
                <div id="webedit_stats">
                    <div id="webedit_stats_select">
                        <p>
                            <strong>Année:</strong>
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
                    // 1er Diagramme : année par mois

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
                            ploopi\db::get()->query(
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
                            ploopi\db::get()->query(
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

                    while ($row = ploopi\db::get()->fetchrow()) $dataset[$row['month']] = $row['counter'];

                    $objBarChartYear = new ploopi\barchart(550, 150, array('padding' => 1));
                    $objBarChartYear->setvalues($dataset, 'Fréquentation mensuelle', '#1E64A1', '#f0f0f0');
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
                            ploopi\db::get()->query(
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
                            ploopi\db::get()->query(
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

                    while ($row = ploopi\db::get()->fetchrow()) $dataset[$row['day']] = $row['counter'];

                    $objBarChartMonth = new ploopi\barchart(550, 150, array('padding' => 1));
                    $objBarChartMonth->setvalues($dataset, 'Fréquentation quotidienne', '#4FA11E', '#f0f0f0');
                    $objBarChartMonth->setlegend($legend);

                    // Affichage
                    ?>
                    <div class="webedit_stats_graph">
                        <h1>Statistiques de fréquentation pour <em><?php echo $intYearSel ?></em> (nombre de visites)</h1>
                        <div><?php $objBarChartYear->draw(); ?></div>
                    </div>
                    <div class="webedit_stats_graph">
                        <h1>Statistiques de fréquentation pour <em><?php echo $ploopi_months[$intMonthSel] ?> <?php echo $intYearSel ?></em> (nombre de visites)</h1>
                        <div><?php $objBarChartMonth->draw(); ?></div>
                    </div>
                </div>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                echo ploopi\skin::get()->create_popup($strPopupTitle, $content, 'popup_webedit_article_stats');

                ploopi\system::kill();
            break;
        case 'webedit_comment_refresh':
            if (isset($_GET['id_article']))
            {
                ploopi\module::init('webedit',false,false,false);

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                $objComment->admin_comment_refresh($_GET['id_article']);
            }
            ploopi\system::kill();
            break;
        case 'webedit_comment_publish':
            if (isset($_GET['id_comment']) && isset($_GET['publish']))
            {
                ploopi\module::init('webedit',false,false,false);

                if(!ploopi\acl::isactionallowed(_WEBEDIT_ACTION_COMMENT)) ploopi\system::kill();

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                if($objComment->open($_GET['id_comment']))
                    $objComment->publish($_GET['publish']==1);
            }
            ploopi\system::kill();
            break;
        case 'webedit_comment_delete':
            if (isset($_GET['id_comment']))
            {
                ploopi\module::init('webedit',false,false,false);

                if(!ploopi\acl::isactionallowed(_WEBEDIT_ACTION_COMMENT)) ploopi\system::kill();

                include_once './modules/webedit/class_article_comment.php';

                $objComment = new webedit_article_comment();
                if($objComment->open($_GET['id_comment'])) $objComment->delete();
            }
            ploopi\system::kill();
            break;
        case 'webedit_comment_show':
            if (isset($_GET['id_article']))
            {
                if (isset($_SESSION['ploopi']['comment']['show'][$_GET['id_article']])) unset($_SESSION['ploopi']['comment']['show'][$_GET['id_article']]);
                else $_SESSION['ploopi']['comment']['show'][$_GET['id_article']] = 1;
            }
            ploopi\system::kill();
            break;
        }
    }
}
