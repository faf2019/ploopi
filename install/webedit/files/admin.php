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
 * Interface d'administration du module
 *
 * @package webedit
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * 
 * @global int $headingid identifiant de la rubrique s�lectionn�e
 * @global int $articleid identifiant de l'article s�lectionn�
 */

/**
 * Initialisation du module
 */
ploopi_init_module('webedit');


$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];
$menu = (empty($_REQUEST['webedit_menu'])) ? '' : $_REQUEST['webedit_menu'];

switch($menu)
{
    case 'reindex':
        switch($op)
        {
            default:
                echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
                echo $skin->open_simplebloc();
                
                $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_heading WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
                $arrStats['headings'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
                
                $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_article WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
                $arrStats['articles'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
                
                $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_docfile WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
                $arrStats['files'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
                
                $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_tag WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
                $arrStats['tags'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
                
                $db->query( 
                    "
                    SELECT  count(*) as c
                    FROM    ploopi_index_element e,
                            ploopi_index_keyword_element ke
                    
                    WHERE   e.id_module = {$_SESSION['ploopi']['moduleid']}
                    AND     e.id_object = "._WEBEDIT_OBJECT_ARTICLE_PUBLIC."
                    AND     ke.id_element = e.id
                    "
                );
                
                $arrStats['keywords'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
                
                ?>
                <div style="padding:4px;">
                    <input type="button" class="button" value="R�indexer le contenu du site" onclick="javascript:document.location.href='<? echo ploopi_urlencode('admin.php?webedit_menu=reindex&op=reindex'); ?>';" />
                </div>
                <div style="padding:4px;">
                    Le site contient :
                </div>
                <div style="padding:4px;">
                    <strong><? echo $arrStats['headings']; ?> rubrique(s)</strong>
                </div>
                <div style="padding:4px;">
                    <strong><? echo $arrStats['articles']; ?> articles(s)</strong>
                </div>
                <div style="padding:4px;">
                    <strong><? echo $arrStats['files']; ?> lien(s) vers un fichier</strong>
                </div>
                <div style="padding:4px;">
                    <strong><? echo $arrStats['tags']; ?> tag(s)</strong>
                </div>
                <div style="padding:4px;">
                    <strong><? echo $arrStats['keywords']; ?> mot(s) index�(s)</strong>
                </div>
                <?
                echo $skin->close_simplebloc();
            break;
            
            case 'reindex':
                if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);
                include_once './modules/webedit/class_article.php';
                
                echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
                echo $skin->open_simplebloc();
                ?>
                <div style="padding:4px;">
                <?
                $index_start = $ploopi_timer->getexectime();
                
                $rsArticles = $db->query("SELECT id FROM ploopi_mod_webedit_article");
                
                while ($row = $db->fetchrow($rsArticles))
                {
                    $objArticle = new webedit_article();
                    if ($objArticle->open($row['id']))
                    {
                        $objArticle->index();
                    }
                }
                ?>
                indexation termin�e en <? printf("%.02fs", $ploopi_timer->getexectime() - $index_start); ?>
                <br /><a title="Retour" href="<? echo ploopi_urlencode('admin.php?webedit_menu=reindex'); ?>">Retour</a>
                </div>
                <?
                echo $skin->close_simplebloc();
            break;
        }
    break;
    
    default:
        /**
         * Inclusion des classes du module
         */
        include_once './modules/webedit/class_article.php';
        include_once './modules/webedit/class_heading.php';
        include_once './modules/webedit/class_heading_subscriber.php';
        
        global $headingid;
        global $articleid;
        
        if (isset($_GET['type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = $_GET['type'];
        
        if (isset($_POST['webedit_display_type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] = $_POST['webedit_display_type'];
        
        // heading id
        if (!empty($_GET['headingid']) && is_numeric($_GET['headingid']))
        {
            $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $_GET['headingid'];
            $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = '';
        }
        
        // article id
        if (!empty($_GET['articleid']) && is_numeric($_GET['articleid']))
        {
            $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = $_GET['articleid'];
        }
        
        if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';
        if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = '';
        if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'] = '';
        if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display'] = 'block';
        if (!isset($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'])) $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] = 'beginner';
        
        
        $type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'];
        $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'];
        $articleid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['articleid'];
        $display_type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'];
                

        switch($op)
        {
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
                    $heading_new->setuwm();
        
                    $headingid = $heading_new->save();
        
                    ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);
        
                    ploopi_redirect("admin.php?headingid={$headingid}");
                }
                else ploopi_redirect('admin.php');
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
        
                    $heading_new->setuwm();
        
                    $headingid = $heading_new->save();
        
                    /* DEBUT ABONNEMENT */
        
                    // on construit la liste des objets parents (y compris l'objet courant)
                    $arrHeadingList = split(';', "{$heading->fields['parents']};{$heading->fields['id']}");
                    
                    // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
                    $arrUsers = array();
                    foreach ($arrHeadingList as $intObjectId)
                        $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
                    
                    // on envoie le ticket de notification d'action sur l'objet
                    ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet � �t� cr��');
                    
                    /* FIN ABONNEMENT */
                    
                    ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);
        
                    ploopi_redirect("admin.php?headingid={$headingid}");
                }
                else ploopi_redirect('admin.php');
            break;
        
        
            case 'heading_save':
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    $heading = new webedit_heading();
                    $heading->open($headingid);
        
                    $newposition = $_POST['head_position'];
                    if ($newposition != $heading->fields['position']) // nouvelle position d�finie
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
                    
                    if (empty($_POST['webedit_heading_feed_enabled'])) $heading->fields['feed_enabled'] = 0;
                    if (empty($_POST['webedit_heading_subscription_enabled'])) $heading->fields['subscription_enabled'] = 0;
                    
                    $heading->save();
                    
                    /* DEBUT ABONNEMENT */
        
                    // on construit la liste des objets parents (y compris l'objet courant)
                    $arrHeadingList = split(';', "{$heading->fields['parents']};{$heading->fields['id']}");
                    
                    // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
                    $arrUsers = array();
                    foreach ($arrHeadingList as $intObjectId)
                        $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
                    
                    // on envoie le ticket de notification d'action sur l'objet
                    ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet � �t� modifi�');
                    
                    /* FIN ABONNEMENT */
        
                    ploopi_workflow_save(_WEBEDIT_OBJECT_HEADING, $heading->fields['id']);
                    
                    ploopi_redirect("admin.php?headingid={$headingid}");
                }
                else ploopi_redirect('admin.php');
        
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
                                        
                        // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
                        $arrUsers = array();
                        foreach ($arrHeadingList as $intObjectId)
                            $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_CATEGORY_EDIT));
                        
                        // on envoie le ticket de notification d'action sur l'objet
                        ploopi_subscription_notify(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], _WEBEDIT_ACTION_CATEGORY_EDIT, $heading->fields['label'], array_keys($arrUsers), 'Cet objet � �t� supprim�');
                        
                        /* FIN ABONNEMENT */
                    
                        $heading->delete(); // you don't have to delete the root heading
                        ploopi_create_user_action_log(_WEBEDIT_ACTION_CATEGORY_EDIT, $headingid);
                        ploopi_redirect("admin.php?headingid={$heading->fields['id_heading']}");
                    }
                    else ploopi_redirect('admin.php');
                }
            break;
        
        
            case 'article_selectfile':
                include_once './modules/webedit/admin_imagegalery.php';
            break;
            
            case 'article_save':
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) && $type == 'draft')
                {
                    $tablename = 'ploopi_mod_webedit_article_draft';
                    
                    $strTypeTicket = '';
        
                    $article = new webedit_article('draft');
                    
                    // article existant ?
                    if (!empty($_POST['articleid']) && is_numeric($_POST['articleid']) && $article->open($_POST['articleid']))
                    {
                        // modification de la rubrique parent
                        // => modification de la position de l'article et des autres articles de la rubrique courante
                        if (isset($_POST['webedit_article_id_heading']) && is_numeric($_POST['webedit_article_id_heading']) && $_POST['webedit_article_id_heading'] != $article->fields['id_heading'])
                        {
                            // on recherche la nouvelle position de l'article dans sa nouvelle rubrique
                            $select = "Select max(position) as maxpos from {$tablename} WHERE id_heading = {$_POST['webedit_article_id_heading']}";
                            $db->query($select);
                            $fields = $db->fetchrow();
                            $maxpos = $fields['maxpos'];
                            if (!is_numeric($maxpos)) $maxpos = 0;
                            
                            // on remonte les articles de la rubrique actuelle
                            $db->query("UPDATE {$tablename} SET position = position - 1 WHERE position > {$article->fields['position']} AND id_heading = {$article->fields['id_heading']}");
                            
                            // on affecte la nouvelle position � l'article
                            $article->fields['position'] = $maxpos+1;
                        }
                        else // si la rubrique parent n'est pas modifi�e
                        {
                            // modification de la position d'un article 
                            if (isset($_POST['webedit_art_position']))
                            {
                                $newposition = $_POST['webedit_art_position'];
                                if ($newposition != $article->fields['position']) // nouvelle position d�finie
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
                        }
                    }
                    else // nouvel article
                    {
                        $strTypeTicket = 'new';
                        
                        $article->setuwm();
                        $select = "Select max(position) as maxpos from {$tablename} WHERE id_heading = {$headingid}";
                        $db->query($select);
                        $fields = $db->fetchrow();
                        $maxpos = $fields['maxpos'];
                        if (!is_numeric($maxpos)) $maxpos = 0;
                        $article->fields['position'] = $maxpos+1;
                        $article->fields['id_heading'] = $headingid;
                    }
        
                    /*
                     * On envoie un ticket pour validation si :
                     * brouillon + statut en attente + modification de statut
                     * 
                     * Note : l'envoi est effectu� plus bas, apr�s avoir cherch� la liste des validateurs
                     */
                    $sendtickets = 
                        (
                                $type == 'draft' 
                            &&  $_POST['webedit_article_status'] == 'wait' 
                            &&  $article->fields['status'] != $_POST['webedit_article_status']
                        );
                    
                    // article modifiable, on enregistre les nouvelles donn�es
                    if ($article->fields['status'] != 'wait') 
                    {
                        $article->setvalues($_POST,'webedit_article_');
                        if (isset($_POST['fck_webedit_article_content'])) $article->fields['content'] = $_POST['fck_webedit_article_content'];
                        
                        $article->fields['timestp'] = ploopi_local2timestamp($_POST['webedit_article_timestp']);
                        
                        if (!empty($_POST['webedit_article_timestp_published'])) $article->fields['timestp_published'] = ploopi_local2timestamp($_POST['webedit_article_timestp_published']);
                        if (!empty($_POST['webedit_article_timestp_unpublished'])) $article->fields['timestp_unpublished'] = ploopi_local2timestamp($_POST['webedit_article_timestp_unpublished']);
        
                        $article->fields['lastupdate_timestp'] = ploopi_createtimestamp();
                        $article->fields['lastupdate_id_user'] = $_SESSION['ploopi']['userid'];
                        
                        if (empty($_POST['webedit_article_visible'])) $article->fields['visible'] = 0;
                        if (empty($_POST['webedit_article_comments_allowed'])) $article->fields['comments_allowed'] = 0;
                        //if (isset($_POST['webedit_article_timestp_published'])) $article->fields['timestp_published'] = ploopi_local2timestamp($_POST['webedit_article_timestp_published']);
                        //if (isset($_POST['webedit_article_timestp_unpublished'])) $article->fields['timestp_unpublished'] = ploopi_local2timestamp($_POST['webedit_article_timestp_unpublished']);
                    }
                    else $article->setvalues($_POST,'webedit_article_');
        
                    
                    // recherche la liste des validateurs de cette rubrique
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
        
                    // action "publier" et l'utilisateur est un validateur => OK
                    if (isset($_POST['publish']) && in_array($_SESSION['ploopi']['userid'],$wfusers))
                    {
                        $strTypeTicket = ($article->publish()) ? 'published_new' : 'published';
                        
                        ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_PUBLISH, $articleid);
                        
                    }
        
                    $articleid = $article->save();
                    
                    /* DEBUT ABONNEMENT */
                    
                    // on construit la liste des objets parents (y compris l'objet courant)
                    $arrHeadingList = split(';', "{$objHeading->fields['parents']};{$objHeading->fields['id']}");
                    
        
                    switch($strTypeTicket)
                    {
                        case 'new':
                            $strMsg = 'Cet objet � �t� cr��';
                            $intActionId = _WEBEDIT_ACTION_ARTICLE_EDIT;
                        break;
                            
                        case 'published':
                        case 'published_new':
                            $strMsg = 'Cet objet � �t� publi�';
                            $intActionId = _WEBEDIT_ACTION_ARTICLE_PUBLISH;
                            
                            // Gestion des abonn�s frontoffice
                            $sql =  "
                                    SELECT  * 
                                    FROM    ploopi_mod_webedit_heading_subscriber 
                                    WHERE   id_module = {$_SESSION['ploopi']['moduleid']} 
                                    AND     id_heading IN (".implode(',', $arrHeadingList).")
                                    ";
                            
                            $db->query($sql);
                            
                            // envoi d'un mail � chaque abonn�
                            while ($row = $db->fetchrow())
                            {
                                $from[] = 
                                    array(
                                        'name' => $_SERVER['HTTP_HOST'], 
                                        'address' => (empty($_SESSION['ploopi']['user']['email'])) ? _PLOOPI_ADMINMAIL : $_SESSION['ploopi']['user']['email']
                                    );
                                
                                switch($strTypeTicket)
                                {
                                    case 'published':
                                        $mail_title = "{$_SERVER['HTTP_HOST']} : modification d'un article";
                                        $mail_content = "Bonjour,\n\nvous recevez ce message car vous �tes abonn� au site {$_SERVER['HTTP_HOST']}.\n\nUn article intitul� \"{$article->fields['title']}\" a �t� modifi�.\n\nVous pouvez le consulter en cliquant sur ce lien : "._PLOOPI_BASEPATH."/".$article->geturl();
                                    break;  
                                    
                                    case 'published_new':
                                        $mail_title = "{$_SERVER['HTTP_HOST']} : publication d'un article";
                                        $mail_content = "Bonjour,\n\nvous recevez ce message car vous �tes abonn� au site {$_SERVER['HTTP_HOST']}.\n\nUn nouvel article intitul� \"{$article->fields['title']}\" a �t� publi�.\n\nVous pouvez le consulter en cliquant sur ce lien : "._PLOOPI_BASEPATH."/".$article->geturl();
                                    break;  
                                }
                                
                                $mail_content .= "\n\nVous pouvez vous d�sabonner en cliquant sur le lien suivant : "._PLOOPI_BASEPATH."/unsubscribe-".ploopi_base64_encode($row['email']).".html";
                                
                                ploopi_send_mail($from, $row['email'], $mail_title, $mail_content, null, null, null, null, false);
                            }
                        break;
                            
                        default:
                            $strMsg = 'Cet objet � �t� modifi�';
                            $intActionId = _WEBEDIT_ACTION_ARTICLE_EDIT;
                        break;
                    }
                    
                    // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
                    $arrUsers = array();
                    foreach ($arrHeadingList as $intObjectId)
                        $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array($intActionId));
                    
                    $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, array($intActionId));
                    
                    // on envoie le ticket de notification d'action sur l'objet
                    ploopi_subscription_notify(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, $intActionId, $article->fields['title'], array_keys($arrUsers), $strMsg);
                    
                    /* FIN ABONNEMENT */
                    
                    // on envoie un ticket de demande de validation si l'utilisateur n'est pas un validateur
                    if ($sendtickets && !in_array($_SESSION['ploopi']['userid'],$wfusers))
                    {
                        $_SESSION['ploopi']['tickets']['users_selected'] = $wfusers;
                        ploopi_tickets_send("Demande de validation de l'article <strong>\"{$article->fields['title']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})", "Ceci est un message automatique envoy� suite � une demande de validation de l'article \"{$article->fields['title']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez acc�der � cet article pour le valider en cliquant sur le lien ci-dessous.", true, 0, _WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $article->fields['title']);
                    }
        
                    if (!empty($_POST['articleid'])) ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $articleid);
                    else ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $articleid);
                    ploopi_redirect("admin.php?op=article_modify&articleid={$articleid}");
                }
                else ploopi_redirect('admin.php');
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
                        
                        // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
                        $arrUsers = array();
                        foreach ($arrHeadingList as $intObjectId)
                            $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_HEADING, $intObjectId, array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH));
                        
                        $arrUsers += ploopi_subscription_getusers(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $_GET['articleid'], array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH));
            
                        // on envoie le ticket de notification d'action sur l'objet
                        ploopi_subscription_notify(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $articleid, _WEBEDIT_ACTION_ARTICLE_EDIT, $article->fields['title'], array_keys($arrUsers), 'Cet objet � �t� supprim�');
                        
                        /* FIN ABONNEMENT */                
                        
                        $article->delete();
                        ploopi_create_user_action_log(_WEBEDIT_ACTION_ARTICLE_EDIT, $_GET['articleid']);
                    }
                }
                ploopi_redirect('admin.php');
            break;
            
            case 'subscriber_delete':
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE) && !empty($_GET['subscriber_email']))
                {
                    $heading_subscriber = new webedit_heading_subscriber();
                    $heading_subscriber->open($headingid, $_GET['subscriber_email']);
                    $heading_subscriber->delete();
                }        
                ploopi_redirect('admin.php');
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
                            ploopi_redirect('admin.php');
                        }
                    }
                }
        
                echo $skin->create_pagetitle(str_replace("LABEL",$_SESSION['ploopi']['modulelabel'], _WEBEDIT_PAGE_TITLE));
                echo $skin->open_simplebloc();
        
                ?>
                <div id="webedit_header">
                    <form action="<? echo ploopi_urlencode('admin.php?op='.$op); ?>" method="post" id="webedit_form_display_type">
                        <input type="hidden" name="webedit_display_type" id="webedit_display_type" value="<? echo $display_type; ?>" /> 
                        
                        <p class="ploopi_checkbox" style="float:right;margin-left:6px;" onclick="javascript:webedit_switch_display_type('advanced');">
                            <img src="./modules/webedit/img/radio-<? echo ($display_type == 'advanced') ? 'on' : 'off'; ?>.png" /><span>&nbsp;Avanc�</span>
                        </p>
                        
                        <p class="ploopi_checkbox" style="float:right;margin-left:6px;" onclick="javascript:webedit_switch_display_type('beginner');">
                            <img src="./modules/webedit/img/radio-<? echo ($display_type == 'beginner') ? 'on' : 'off'; ?>.png" /><span>&nbsp;Simplifi�</span>
                        </p>
                        
                        <p class="ploopi_va" style="float:right;">
                            <span>Affichage : </span>
                        </p>
                    </form>
                    
                    <p class="ploopi_va" style="float:left;cursor:pointer;" onclick="javascript:ploopi_switchdisplay('webedit_tree');ploopi_switchdisplay('webedit_article_options');ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_switchdisplay_treeview&display='+ploopi_getelem('webedit_tree').style.display, true);">
                        <img title="Afficher/Cacher l'arborescence des rubriques" alt="Afficher/Cacher l'arborescence des rubriques" src="./modules/webedit/img/fullscreen.png">
                        <span>Afficher/Cacher l'arborescence des rubriques</span>        
                    </p>
                </div>
                
                
                <div style="clear:both;">
                    <div class="webedit_tree" id="webedit_tree" style="display:<? echo $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display']; ?>;">
                        <div class="webedit_tree_padding" style="">
                            <?
                            $headings = webedit_getheadings();
                            $articles = webedit_getarticles();
        
                            if (empty($headingid) || !isset($headings['list'][$headingid])) $headingid = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['headingid'] = $headings['tree'][0][0];
        
                            $treeview = webedit_gettreeview();
                            $node_id = (!empty($articleid)) ? "a{$articleid}" : "h{$headingid}";
                            echo $skin->display_treeview($treeview['list'], $treeview['tree'], $node_id);
                            ?>
                        </div>
                        <div id="webedit_legende">
                            <p class="ploopi_va" style="padding-bottom:4px;">
                                <span><strong>L�gende:</strong></span>
                            </p>
                            <p class="ploopi_va" style="clear:both;">
                                <img style="float:left;display:block;" src="./modules/webedit/img/base.png"><span>Racine</span>
                            </p>
                            <p class="ploopi_va" style="clear:both;">
                                <img style="float:left;display:block;" src="./modules/webedit/img/folder.png"><span>Rubrique</span>
                            </p>
                            <p class="ploopi_va" style="clear:both;">
                                <img style="float:left;display:block;" src="./modules/webedit/img/doc0.png"><span>Article publi�</span>
                            </p>
                            <p class="ploopi_va" style="clear:both;">
                                <img style="float:left;display:block;" src="./modules/webedit/img/doc1.png"><span>Article modifi�</span>
                            </p>
                            <p class="ploopi_va" style="clear:both;">
                                <img style="float:left;display:block;" src="./modules/webedit/img/doc2.png"><span>Article non publi�</span>
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
    break;
}