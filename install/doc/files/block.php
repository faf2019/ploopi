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
 * Affichage du bloc de menu
 *
 * @package doc
 * @subpackage block
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author StÈphane Escaich
 */

/**
 * Initialisation du module
 */ 

ploopi_init_module('doc', false, false, false);

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_viewfoldersinblock'])
{
    /**
     * Chargement des partages
     */
    
    doc_getshare($menu_moduleid);
    
    /**
     * Affichage des dossiers
     */

    // affichage des raccourcis ? (pour partages + public)
    $option_shortcuts = ($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_displayshortcuts']) ? '' : 'AND f.id_folder = 0';

    // dossiers partagÈs
    $where = (!empty($_SESSION['doc'][$menu_moduleid]['share']['folders'])) ? ' OR (f.id IN ('.implode(',', $_SESSION['doc'][$menu_moduleid]['share']['folders']).") AND f.id_user <> {$_SESSION['ploopi']['userid']} {$option_shortcuts})" : '';

    // dossiers dont l'utilisateur connect√© est le validateur
    $list_wf_folders = (!empty($_SESSION['doc'][$menu_moduleid]['validation']['folders'])) ? implode(',', $_SESSION['doc'][$menu_moduleid]['validation']['folders']) : '';
    $list_wf_folders_option = ($list_wf_folders != '') ? " OR f_val.id_folder IN ({$list_wf_folders}) " : '';

    $sql =  "
            SELECT      f.*,
                        u.login,
                        w.label
            FROM        ploopi_mod_doc_folder f

            LEFT JOIN   ploopi_user u
            ON          f.id_user = u.id

            LEFT JOIN   ploopi_workspace w
            ON          w.id_workspace = w.id

            LEFT JOIN   ploopi_mod_doc_folder f_val
            ON          f_val.id = f.waiting_validation

            WHERE       f.id_module = {$menu_moduleid}
            AND         f.published = 1
            AND         (f.waiting_validation = 0 OR f.id_user = {$_SESSION['ploopi']['userid']} {$list_wf_folders_option})

            AND         ((f.id_user = {$_SESSION['ploopi']['userid']} AND f.id_folder = 0)
                        OR (f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).") AND f.id_user <> {$_SESSION['ploopi']['userid']} {$option_shortcuts})
                        $where
                        )

            ORDER BY    f.name
            ";

    $db->query($sql);

    while ($row = $db->fetchrow())
    {

        if ($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_viewiconsinblock'])
        {
            $ico = 'ico_folder';
            if ($row['foldertype'] == 'shared') $ico .= '_shared';
            if ($row['foldertype'] == 'public') $ico .= '_public';
            if ($row['readonly']) $ico .= '_locked';
            $block->addmenu("<p class=\"ploopi_va\"><img src=\"./modules/doc/img/{$ico}.png\" /><span>&nbsp;{$row['name']}</span></p>", ploopi_urlencode("admin.php?ploopi_moduleid=$menu_moduleid&ploopi_action=public&currentfolder={$row['id']}"));
        }
        else $block->addmenu($row['name'], ploopi_urlencode("admin.php?ploopi_moduleid=$menu_moduleid&ploopi_action=public&currentfolder={$row['id']}"));

    }


}

/**
 * Menu 'Mes Documents' / Racine
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_displayroot'])
{
    $label = (empty($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_rootlabel'])) ? _DOC_MYDOCUMENTS : $_SESSION['ploopi']['modules'][$menu_moduleid]['doc_rootlabel'];
    $block->addmenu($label, ploopi_urlencode("admin.php?ploopi_moduleid=$menu_moduleid&ploopi_action=public"));
}

/**
 * Menu 'Recherche'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['doc_displaysearch'])
{
    $block->addmenu(_DOC_SEARCH, ploopi_urlencode("admin.php?ploopi_moduleid=$menu_moduleid&ploopi_action=public&op=doc_bd_search"));
}

/**
 * Menu 'Administration'
 */

if (ploopi_isactionallowed(0, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>'._DOC_LABEL_ADMIN.'</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}
?>
