<?php
/*
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
 * Affichage du backend des document
 *
 * @package doc
 * @subpackage backend
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

include_once './modules/doc/class_docfolder.php';

// Contr�les
if(empty($_GET['id_folder'])) { echo ''; ploopi_die(); }

$objFolder = new docfolder();
if(!$objFolder->open($_GET['id_folder'])
    || $objFolder->fields['foldertype'] != 'public' 
    || !$objFolder->fields['allow_feeds'] 
    || !$objFolder->fields['published'])
{
    // Erreur, flux retourne faux !
    ploopi_h404();
    ploopi_die();
}

// Format du flux (RSS / ATOM)
$format = (empty($_GET['format'])) ? 'atom' : strtolower($_GET['format']);

// Mise en cache
include_once './include/classes/cache.php';

$objCache = new ploopi_cache(md5('doc_feeds_folder_'.$format.'_'.$_GET['id_folder']), 300);
$objCache->set_groupe('module_doc_feeds_'.$objFolder->fields['id_workspace'].'_'.$objFolder->fields['id_module']);  // Attribution d'un groupe sp�cifique pour le cache pour permettre un clean pr�cis

// Vidage du buffer
ploopi_ob_clean();

if (!$objCache->start())
{
    /**
     * Inclusions des fonctions sur les dates et les cha�nes (l'appel via backend.php est minimal, les fonctions ne sont donc pas d�j� incluses)
     */
    include_once './include/start/functions.php';
    
    /**
     * FeedWriter qui permet de g�n�rer le flux
     */
    include_once './lib/feedwriter/FeedWriter.php';

    ploopi_init_module('doc', false, false, false);

    $intTsToday = ploopi_createtimestamp();
    
    switch($format)
    {
        case 'rss';
            $objDocFeed = new FeedWriter(RSS2);
            $objDocFeed->setLink(_PLOOPI_BASEPATH.'/'.ploopi_urlrewrite('backend.php?format=rss&ploopi_moduleid='.$objFolder->fields['id_module'].'&id_folder='.$objFolder->fields['id'], doc_getrewriterules(), $objFolder->fields['name'].'.xml',null,true));
            $objDocFeed->setChannelElement('language', 'fr-fr');
            $objDocFeed->setChannelElement('pubDate', date(DATE_RSS , time()));
            $objDocFeed->setChannelElement('lastBuildDate', date(DATE_RSS , time()));
            break;

        default:
            case 'atom';
            $objDocFeed = new FeedWriter(ATOM);
            $objDocFeed->setLink(_PLOOPI_BASEPATH.'/'.ploopi_urlrewrite('backend.php?format=atom&ploopi_moduleid='.$objFolder->fields['id_module'].'&id_folder='.$objFolder->fields['id'], doc_getrewriterules(), $objFolder->fields['name'].'.xml',null,true));
            $objDocFeed->setChannelElement('updated', date(DATE_ATOM , time()));
            break;
    }
    
    $objDocFeed->setTitle(utf8_encode($objFolder->fields['name']));
    $objDocFeed->setDescription(utf8_encode($objFolder->fields['description']));
    
    if(!empty($_SESSION['ploopi']['workspaces'][$objFolder->fields['id_workspace']]['meta_author']))
    {
        $meta_author = ploopi_xmlentities(utf8_encode($_SESSION['ploopi']['workspaces'][$objFolder->fields['id_workspace']]['meta_author']), true);
        $objDocFeed->setChannelElement('author', array('name '=> $meta_author));
    }
    
    // On recherche tous les dossiers enfants publics du dossier selectionn�
    $select = "
        SELECT      id, name
        FROM        ploopi_mod_doc_folder
        
        WHERE       (parents LIKE '%,{$objFolder->fields['id']},%' 
        OR          parents LIKE '%,{$objFolder->fields['id']}'
        OR          id = '{$objFolder->fields['id']}')
        AND         foldertype = 'public'
        AND         published = 1
    ";
    $sql_folder = $db->query($select);
    
    if(!$db->numrows($sql_folder))
    {
        // Aucun fichier, flux retourne faux !
        ploopi_h404();
        ploopi_die();
    }
    
    $arrFolder = $db->getarray($sql_folder,true);
    $strFolderSql = implode(',',array_keys($arrFolder));
    
    $limit = '';
    if(isset($_SESSION['ploopi']['modules'][$objFolder->fields['id_module']]['doc_nbDocFeed']))
        $limit = 'LIMIT '.$_SESSION['ploopi']['modules'][$objFolder->fields['id_module']]['doc_nbDocFeed'];
    
    $select = "
        SELECT      f.id, f.md5id, f.name, f.description, f.timestp_modify,
                    f.size, f.version, f.id_folder,
                    u.lastname, u.firstname
                    
        FROM        ploopi_mod_doc_file f
        
        LEFT JOIN   ploopi_user u
        ON          u.id = f.id_user
        
        WHERE       f.id_folder IN ({$strFolderSql})
        
        ORDER BY    f.timestp_modify DESC

        {$limit}
    ";

    $sql_file = $db->query($select);
    
    if(!$db->numrows($sql_file))
    {
        // Aucun fichier, flux retourne faux !
        ploopi_h404();
        ploopi_die();
    }

    while ($file = $db->fetchrow($sql_file))
    {
        // Cr�ation d'un nouvel item
        $link = _PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$file['md5id']}", doc_getrewriterules(), $file['name'], null, true);
        $img  = _PLOOPI_BASEPATH.'/'.ploopi_urlencode("index-light.php?ploopi_op=doc_getthumbnail&docfile_md5id={$file['md5id']}&version={$file['version']}");
        
        $objDocItem = $objDocFeed->createNewItem();
        
        $title =  $file['name'];
        $title .=  ($file['id_folder'] != $objFolder->fields['id']) ? ' / '.$arrFolder[$file['id_folder']] : '';
        $title .= ' / '.sprintf("%0.2f kio", ($file['size']/1024));
        $title .= ' / '.$file['lastname'].' '. $file['firstname'];
        
        $objDocItem->setTitle(utf8_encode(ploopi_xmlentities($title)));
        $objDocItem->setLink($link);
        
        $description = '<a href="'.$link.'"><img src="'.$img.'" align="left" hspace="20" alt="'.$file['name'].'" border="0" /></a>';
        $description .='<div>';
        $description .=ploopi_nl2br(ploopi_xmlentities($file['description']));
        $description .='</div>';
        
        $objDocItem->setDescription(utf8_encode($description)); // Pas de ploopi_xmlentities car on affiche du html !

        $objDocItem->setDate(ploopi_timestamp2unixtimestamp($file['timestp_modify']));
        
        // Ajout de l'item dans le flux
        $objDocFeed->addItem($objDocItem);
    }
    
    // G�n�ration du flux
    $objDocFeed->generateFeed();

    $objCache->end();
}
header("Content-type: text/xml");
?>