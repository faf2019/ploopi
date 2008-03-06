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

ploopi_init_module('webedit');
include_once './modules/webedit/class_article.php';
include_once './modules/webedit/class_heading.php';

global $headingid;
global $articleid;

if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = '';
if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = '';
if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';
if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = 'block';

if (isset($_GET['type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = $_GET['type'];

$headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'];
$articleid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'];
$type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'];

if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']))
{
    $headingid = $_GET['headingid'];

    // reset articleid if new headingid
    if ($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] != $headingid) unset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid']);

    $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $headingid;
}

if (!empty($_GET['articleid']) && is_numeric($_GET['articleid'])) $articleid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = $_GET['articleid'];

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

switch($op)
{
    // ===============
    // XML Actions
    // ===============

    case 'xml_detail_heading':
        ob_end_clean();
        if (!empty($_GET['hid']) && is_numeric($_GET['hid']))
        {
            $headings = webedit_getheadings();
            $articles = webedit_getarticles();
            echo webedit_build_tree($headings, $articles, $_GET['hid'], (empty($_GET['str'])) ? '' : $_GET['str'], (empty($_GET['option'])) ? '' : $_GET['option']);
        }
        ploopi_die();
    break;

    case 'xml_switchdisplay':
        if (!empty($_GET['display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = $_GET['display'];
        ploopi_die();
    break;

    // ===============
    // GLOBAL Actions
    // ===============
    case 'heading_addroot':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {

            $heading_new = new webedit_heading();

            $select = "Select max(position) as maxpos from ploopi_mod_webedit_heading WHERE id_heading = 0 AND id_module = {$_SESSION['ploopi']['moduleid']}";
            $db->query($select);
            $fields = $db->fetchrow();
            $maxpos = $fields['maxpos'];
            if (!is_numeric($maxpos)) $maxpos = 0;
            $heading_new->fields['position'] = $maxpos+1;

            $heading_new->fields['label'] = "Racine {$heading_new->fields['position']}";
            $heading_new->fields['id_heading'] = 0;
            $heading_new->fields['parents'] = 0;
            $heading_new->fields['depth'] = 1;
            $heading_new->setugm();

            $headingid = $heading_new->save();

            ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);

            ploopi_redirect("{$scriptenv}?headingid={$headingid}");
        }
        else ploopi_redirect($scriptenv);
    break;

    case 'heading_addnew':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            $heading = new webedit_heading();
            $heading->open($headingid);

            $heading_new = new webedit_heading();
            $heading_new->fields['label'] = "Sous rubrique de '{$heading->fields['label']}'";
            $heading_new->fields['id_heading'] = $headingid;
            $heading_new->fields['parents'] = "{$heading->fields['parents']};{$headingid}";
            $heading_new->fields['depth'] = $heading->fields['depth']+1;

            $select = "Select max(position) as maxpos from ploopi_mod_webedit_heading WHERE id_heading = $headingid";
            $db->query($select);
            $fields = $db->fetchrow();
            $maxpos = $fields['maxpos'];
            if (!is_numeric($maxpos)) $maxpos = 0;
            $heading_new->fields['position'] = $maxpos+1;

            $heading_new->setugm();

            $headingid = $heading_new->save();

            /* DEBUT ABONNEMENT */

            // on construit la liste des objets parents (y compris l'objet courant)
            $arrHeadingList = split(';', "{$heading->fields['parents']};{$heading->fields['id']}");
            
            // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
            $arrUsers = array();
            foreach ($arrHeadingList as $intObjectId)
                $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
            
            // on envoie le ticket de notification d'action sur l'objet
            ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet à été créé');
            
            /* FIN ABONNEMENT */
            
            ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);

            ploopi_redirect("{$scriptenv}?headingid={$headingid}");
        }
        else ploopi_redirect($scriptenv);
    break;


    case 'heading_save':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            $heading = new webedit_heading();
            $heading->open($headingid);

            $newposition = $_POST['head_position'];
            if ($newposition != $heading->fields['position']) // nouvelle position définie
            {
                if ($newposition<1) $newposition=1;
                else
                {
                    $select = "Select max(position) as maxpos from ploopi_mod_webedit_heading where id_heading = {$heading->fields['id_heading']} AND id_module = {$_SESSION['ploopi']['moduleid']}";
                    $db->query($select);
                    $fields = $db->fetchrow();
                    if ($newposition > $fields['maxpos']) $newposition = $fields['maxpos'];
                }

                $db->query("update ploopi_mod_webedit_heading set position=0 where position={$heading->fields['position']} AND id_heading = {$heading->fields['id_heading']} AND id_module = {$_SESSION['ploopi']['moduleid']}");
                if ($newposition > $heading->fields['position'])
                {
                    $db->query("update ploopi_mod_webedit_heading set position=position-1 where position BETWEEN ".($heading->fields['position']-1)." AND {$newposition} AND id_heading = {$heading->fields['id_heading']} AND id_module = {$_SESSION['ploopi']['moduleid']}");
                }
                else
                {
                    $db->query("update ploopi_mod_webedit_heading set position=position+1 where position BETWEEN {$newposition} AND ".($heading->fields['position']-1)." AND id_heading = {$heading->fields['id_heading']} AND id_module = {$_SESSION['ploopi']['moduleid']}");
                }
                $db->query("update ploopi_mod_webedit_heading set position={$newposition} where position=0 AND id_heading = {$heading->fields['id_heading']} AND id_module = {$_SESSION['ploopi']['moduleid']}");
                $heading->fields['position'] = $newposition;
            }

            $heading->setvalues($_POST,'webedit_heading_');

            if (empty($_POST['webedit_heading_visible'])) $heading->fields['visible'] = 0;
            if (empty($_POST['webedit_heading_url_window'])) $heading->fields['url_window'] = 0;
            
            $heading->save();
            
            /* DEBUT ABONNEMENT */

            // on construit la liste des objets parents (y compris l'objet courant)
            $arrHeadingList = split(';', "{$heading->fields['parents']};{$heading->fields['id']}");
            
            // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
            $arrUsers = array();
            foreach ($arrHeadingList as $intObjectId)
                $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
            
            // on envoie le ticket de notification d'action sur l'objet
            ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet à été modifié');
            
            /* FIN ABONNEMENT */

            ploopi_workflow_save(_WEBEDIT_OBJECT_HEADING, $heading->fields['id']);
            
            ploopi_redirect("{$scriptenv}?headingid={$headingid}");
        }
        else ploopi_redirect($scriptenv);

    break;

    case 'heading_delete':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            $heading = new webedit_heading();
            $heading->open($headingid);

            if (!($heading->fields['id_heading'] == 0 && $heading->fields['position'] == 1))
            {
                /* DEBUT ABONNEMENT */
                
                // on construit la liste des objets parents (y compris l'objet courant)
                $arrHeadingList = split(';', "{$heading->fields['parents']};{$heading->fields['id']}");
                                
                // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                $arrUsers = array();
                foreach ($arrHeadingList as $intObjectId)
                    $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
                
                // on envoie le ticket de notification d'action sur l'objet
                ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet à été supprimé');
                
                /* FIN ABONNEMENT */
            
                $heading->delete(); // you don't have to delete the root heading
                ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);
                ploopi_redirect("{$scriptenv}?headingid={$heading->fields['id_heading']}");
            }
            else ploopi_redirect($scriptenv);
        }
    break;


    case 'article_selectfile':
        include_once './modules/webedit/admin_imagegalery.php';
    break;

    case 'article_selectlink':
        $headings = webedit_getheadings();
        $articles = webedit_getarticles();
        echo webedit_build_tree($headings, $articles, 0, '', 'selectlink');
    break;

    case 'heading_selectredirect':
        ob_end_clean();
        ob_start();
        ?>
        <div style="padding:4px;height:150px;overflow:auto;">
        <?
        $headings = webedit_getheadings();
        $articles = webedit_getarticles();
        echo webedit_build_tree($headings, $articles, 0, '', 'selectredirect');
        ?>
        </div>
        <?
        $content = ob_get_contents();
        ob_end_clean();
        
        echo $skin->create_popup('Choix d\'une page', $content, 'webedit_popup_selectredirect');

        ploopi_die();
    break;


    case 'article_save':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) && $type == 'draft')
        {
            $tablename = 'ploopi_mod_webedit_article';
            if ($type == 'draft') $tablename .= '_draft';
            
            $strTypeTicket = '';

            $article = new webedit_article($type);
            if (!empty($_POST['articleid']) && is_numeric($_POST['articleid']) && $article->open($_POST['articleid']))
            {
                $newposition = $_POST['webedit_art_position'];
                if ($newposition != $article->fields['position']) // nouvelle position définie
                {
                    if ($newposition<1) $newposition=1;
                    else
                    {
                        $select = "Select max(position) as maxpos from {$tablename} where id_heading = {$headingid}";
                        $db->query($select);
                        $fields = $db->fetchrow();
                        if ($newposition > $fields['maxpos']) $newposition = $fields['maxpos'];
                    }

                    $db->query("update {$tablename} set position=0 where position={$article->fields['position']} AND id_heading = {$article->fields['id_heading']}");
                    if ($newposition > $article->fields['position'])
                    {
                        $db->query("update {$tablename} set position=position-1 where position BETWEEN ".($article->fields['position']-1)." AND {$newposition} AND id_heading = {$article->fields['id_heading']}");
                    }
                    else
                    {
                        $db->query("update {$tablename} set position=position+1 where position BETWEEN {$newposition} AND ".($article->fields['position']-1)." AND id_heading = {$article->fields['id_heading']}");
                    }
                    $db->query("update {$tablename} set position={$newposition} where position=0 AND id_heading = {$article->fields['id_heading']}");
                    $article->fields['position'] = $newposition;
                }

            }
            else
            {
                $strTypeTicket = 'new';
                
                $article->setuwm();
                $select = "Select max(position) as maxpos from {$tablename} WHERE id_heading = {$headingid}";
                $db->query($select);
                $fields = $db->fetchrow();
                $maxpos = $fields['maxpos'];
                if (!is_numeric($maxpos)) $maxpos = 0;
                $article->fields['position'] = $maxpos+1;
            }

            $sendtickets = ($type == 'draft' && $_POST['webedit_article_status'] == 'wait' && $article->fields['status'] != $_POST['webedit_article_status']);

            $article->setvalues($_POST,'webedit_article_');


            if (isset($_POST['fck_webedit_article_content'])) $article->fields['content'] = $_POST['fck_webedit_article_content'];

            $article->fields['timestp'] = ploopi_local2timestamp($_POST['webedit_article_timestp']);
            if (isset($_POST['webedit_article_timestp_published'])) $article->fields['timestp_published'] = ploopi_local2timestamp($_POST['webedit_article_timestp_published']);
            if (isset($_POST['webedit_article_timestp_unpublished'])) $article->fields['timestp_unpublished'] = ploopi_local2timestamp($_POST['webedit_article_timestp_unpublished']);

            $article->fields['lastupdate_timestp'] = ploopi_createtimestamp();
            $article->fields['lastupdate_id_user'] = $_SESSION['ploopi']['userid'];

            if (empty($_POST['webedit_article_visible'])) $article->fields['visible'] = 0;
            if (empty($_POST['webedit_article_comments_allowed'])) $article->fields['comments_allowed'] = 0;

            $article->fields['id_heading'] = $headingid;

            // get workflow validators
            $wfusers = array();
            $wf = ploopi_workflow_get(_WEBEDIT_OBJECT_HEADING, $headingid);
            $wf_headingid = $headingid;

            $objHeading = new webedit_heading();
            $objHeading->open($headingid);
            
            if (empty($wf)) // pas de validateur pour cette rubrique, on recherche sur les parents
            {
                $parents = explode(';', $objHeading->fields['parents']);
                for ($i = sizeof($parents)-1; $i >= 0; $i--)
                {
                    $wf = ploopi_workflow_get(_WEBEDIT_OBJECT_HEADING, $parents[$i]);
                    if (!empty($wf)) break;
                }
            }

            foreach($wf as $value) $wfusers[] = $value['id_workflow'];

            if (isset($_POST['publish']) && in_array($_SESSION['ploopi']['userid'],$wfusers))
            {
                $article->publish();
                ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_PUBLISH, $articleid);
                $strTypeTicket = 'published';
            }

            $articleid = $article->save();
            
            /* DEBUT ABONNEMENT */
            
            // on construit la liste des objets parents (y compris l'objet courant)
            $arrHeadingList = split(';', "{$objHeading->fields['parents']};{$objHeading->fields['id']}");
            

            switch($strTypeTicket)
            {
                case 'new':
                    $strMsg = 'Cet objet à été créé';
                    $intActionId = _WEBEDIT_ACTION_ARTICLE_EDIT;
                break;
                    
                case 'published':
                    $strMsg = 'Cet objet à été publié';
                    $intActionId = _WEBEDIT_ACTION_ARTICLE_PUBLISH;
                break;
                    
                default:
                    $strMsg = 'Cet objet à été modifié';
                    $intActionId = _WEBEDIT_ACTION_ARTICLE_EDIT;
                break;
            }
            
            // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
            $arrUsers = array();
            foreach ($arrHeadingList as $intObjectId)
                $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array($intActionId));
            
            $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, array($intActionId));
            
            // on envoie le ticket de notification d'action sur l'objet
            ploopi_subscription_notify(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, $intActionId, $article->fields['title'], array_keys($arrUsers), $strMsg);
            
            /* FIN ABONNEMENT */
            
            if ($sendtickets && !in_array($_SESSION['ploopi']['userid'],$wfusers))
            {
                $_SESSION['ploopi']['tickets']['users_selected'] = $wfusers;
                ploopi_tickets_send("Demande de validation de l'article <strong>\"{$article->fields['title']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})", "Ceci est un message automatique envoyé suite à une demande de validation de l'article \"{$article->fields['title']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez accéder à cet article pour le valider en cliquant sur le lien ci-dessous.", true, 0, _WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $article->fields['title']);
            }

            if (!empty($_POST['articleid'])) ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $articleid);
            else ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $articleid);
            ploopi_redirect("{$scriptenv}?op=article_modify&articleid={$articleid}");
        }
        else ploopi_redirect($scriptenv);
    break;

    case 'article_delete':
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) && $type == 'draft')
        {
            $article = new webedit_article($type);
            if (!empty($_GET['articleid']) && is_numeric($_GET['articleid']) && $article->open($_GET['articleid']))
            {

                /* DEBUT ABONNEMENT */
                
                $objHeading = new webedit_heading();
                $objHeading->open($headingid);
                
                // on construit la liste des objets parents (y compris l'objet courant)
                $arrHeadingList = split(';', "{$objHeading->fields['parents']};{$objHeading->fields['id']}");
                
                // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                $arrUsers = array();
                foreach ($arrHeadingList as $intObjectId)
                    $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH));
                
                $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $_GET['articleid'], array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH));
    
                // on envoie le ticket de notification d'action sur l'objet
                ploopi_subscription_notify(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, _WEBEDIT_ACTION_ARTICLE_EDIT, $article->fields['title'], array_keys($arrUsers), 'Cet objet à été supprimé');
                
                /* FIN ABONNEMENT */                
                
                $article->delete();
                ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $_GET['articleid']);
            }
        }
        ploopi_redirect("$scriptenv");
    break;

    case 'display_iframe':
        ?>
        <script type="text/javascript" src="./FCKeditor/fckeditor.js"></script>
        <script type="text/javascript">
        function webedit_getcontent()
        {
            var oEditor = FCKeditorAPI.GetInstance('fck_webedit_article_content') ;
            return(oEditor.GetXHTML(true));
        }
        </script>
        <?
        include_once './modules/webedit/display.php';
    break;


    // ===============
    // INTERFACE
    // ===============

    default :


        if ($op == 'article_modify')
        {
            $article = new webedit_article($type);
            if ($article->open($articleid))
            {
                $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $article->fields['id_heading'];
            }
            else
            {
                $type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';
                $article = new webedit_article($type);
                if ($article->open($articleid))
                {
                    $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $article->fields['id_heading'];
                }
                else // article inconnu
                {
                    ploopi_redirect($scriptenv);
                }
            }
        }

        echo $skin->create_pagetitle(str_replace("LABEL",$_SESSION['ploopi']['modulelabel'],_WEBEDIT_PAGE_TITLE),'100%');
        echo $skin->open_simplebloc();

        ?>
        <div>
            <div class="webedit_tree" id="webedit_tree" style="display:<? echo $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display']; ?>;">
                <div class="webedit_tree_padding" style="">
                    <?
                    $headings = webedit_getheadings();
                    $articles = webedit_getarticles();

                    if (empty($headingid) || !isset($headings['list'][$headingid])) $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $headings['tree'][0][0];

                    if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']))
                    {
                        $headingid = $_GET['headingid'];
                        if (!isset($headings['list'][$headingid])) $headingid = $headings['tree'][0][0]; // id n'existe pas

                        // reset articleid if new headingid
                        if ($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] != $headingid) unset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid']);

                        $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $headingid;
                    }

                    if (!empty($_GET['articleid']) && is_numeric($_GET['articleid'])) $articleid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = $_GET['articleid'];

                    if (empty($headingid)) $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $headings['tree'][0][0];

                    echo webedit_build_tree($headings, $articles);
                    ?>
                </div>
                <div id="webedit_legende">
                    <p class="ploopi_va" style="padding-bottom:4px;">
                        <span><strong>Légende:</strong></span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <img style="float:left;display:block;" src="./modules/webedit/img/base.png"><span>Racine</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <img style="float:left;display:block;" src="./modules/webedit/img/folder.png"><span>Rubrique</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <img style="float:left;display:block;" src="./modules/webedit/img/doc0.png"><span>Article publié</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <img style="float:left;display:block;" src="./modules/webedit/img/doc1.png"><span>Article modifié</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <img style="float:left;display:block;" src="./modules/webedit/img/doc2.png"><span>Article non publié</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <span style="float:left;width:16px;text-align:center;color:#ff0000;font-weight:bold;">*</span><span>Attente de validation</span>
                    </p>
                    <p class="ploopi_va" style="clear:both;">
                        <span style="float:left;width:16px;text-align:center;color:#ff0000;font-weight:bold;">~</span><span>Hors ligne</span>
                    </p>
                </div>
            </div>
            <div class="webedit_main">
                <div class="webedit_main2">
                <?
                if ($op == 'article_addnew' || $op == 'article_modify') include_once './modules/webedit/admin_article.php';
                else include_once './modules/webedit/admin_heading.php';
                ?>
                </div>
            </div>
        </div>
        <?
        echo $skin->close_simplebloc();
    break;
}

