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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

include_once './modules/doc/class_docfolder.php';

// Contrôles
if(empty($_GET['id_folder'])) { echo ''; ploopi\system::kill(); }

$objFolder = new docfolder();
if(!$objFolder->open($_GET['id_folder'])
    || $objFolder->fields['foldertype'] != 'public'
    || !$objFolder->fields['allow_feeds']
    || !$objFolder->fields['published'])
{
    // Erreur, flux retourne faux !
    ploopi\output::h404();
    ploopi\system::kill();
}

// Format du flux (RSS / ATOM)
$format = (empty($_GET['format'])) ? 'atom' : strtolower($_GET['format']);

// Mise en cache
$objCache = new ploopi\cache(md5('doc_feeds_folder_'.$format.'_'.$_GET['id_folder']), 300);
$objCache->set_groupe('module_doc_feeds_'.$objFolder->fields['id_workspace'].'_'.$objFolder->fields['id_module']);  // Attribution d'un groupe spécifique pour le cache pour permettre un clean précis

// Vidage du buffer
ploopi\buffer::clean();

if (!$objCache->start())
{
    ploopi\module::init('doc', false, false, false);

    // ploopi\feed échappe lui-même les valeurs (déjà en UTF-8) : les passer brutes
    $objDocFeed = new ploopi\feed($format == 'rss' ? ploopi\feed::RSS : ploopi\feed::ATOM);
    $objDocFeed->setLink(_PLOOPI_BASEPATH.'/'.ploopi\str::urlrewrite('backend.php?format='.($format == 'rss' ? 'rss' : 'atom').'&ploopi_moduleid='.$objFolder->fields['id_module'].'&id_folder='.$objFolder->fields['id'], doc_getrewriterules(), $objFolder->fields['name'].'.xml',null,true));
    $objDocFeed->setLanguage('fr-fr');
    $objDocFeed->setTitle($objFolder->fields['name']);
    $objDocFeed->setDescription($objFolder->fields['description']);

    if(!empty($_SESSION['ploopi']['workspaces'][$objFolder->fields['id_workspace']]['meta_author']))
    {
        $objDocFeed->setAuthor($_SESSION['ploopi']['workspaces'][$objFolder->fields['id_workspace']]['meta_author']);
    }

    // On recherche tous les dossiers enfants publics du dossier selectionné
    $select = "
        SELECT      id, name
        FROM        ploopi_mod_doc_folder

        WHERE       (parents LIKE '%,{$objFolder->fields['id']},%'
        OR          parents LIKE '%,{$objFolder->fields['id']}'
        OR          id = '{$objFolder->fields['id']}')
        AND         foldertype = 'public'
        AND         published = 1
    ";
    $sql_folder = ploopi\db::get()->query($select);

    if(!ploopi\db::get()->numrows($sql_folder))
    {
        // Aucun fichier, flux retourne faux !
        ploopi\output::h404();
        ploopi\system::kill();
    }

    $arrFolder = ploopi\db::get()->getarray($sql_folder,true);
    $strFolderSql = implode(',',array_keys($arrFolder));

    $limit = '';
    if(!empty($_SESSION['ploopi']['modules'][$objFolder->fields['id_module']]['doc_nbDocFeed']))
        $limit = 'LIMIT '.(int) $_SESSION['ploopi']['modules'][$objFolder->fields['id_module']]['doc_nbDocFeed'];

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

    $sql_file = ploopi\db::get()->query($select);

    if(!ploopi\db::get()->numrows($sql_file))
    {
        // Aucun fichier, flux retourne faux !
        ploopi\output::h404();
        ploopi\system::kill();
    }

    while ($file = ploopi\db::get()->fetchrow($sql_file))
    {
        $link = _PLOOPI_BASEPATH.'/'.ploopi\str::urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$file['md5id']}", doc_getrewriterules(), $file['name'], null, true);

        $title =  $file['name'];
        $title .=  ($file['id_folder'] != $objFolder->fields['id']) ? ' / '.$arrFolder[$file['id_folder']] : '';
        $title .= ' / '.sprintf("%0.2f kio", ($file['size']/1024));
        $title .= ' / '.trim($file['lastname'].' '. $file['firstname']);

        // Résumé en HTML : la description, échappée. Pas de vignette : son URL porterait le
        // jeton de la session qui a produit le flux, mis en cache et servi à tous
        $description = ($file['description'] != '') ? '<div>'.ploopi\str::nl2br(ploopi\str::htmlentities($file['description'])).'</div>' : '';

        $objDocFeed->addItem($title, $link, ploopi\date::timestamp2unixtimestamp($file['timestp_modify']), $description);
    }

    // Génération du flux
    echo $objDocFeed->render();

    $objCache->end();
}
header("Content-type: text/xml; charset=utf-8");
?>
