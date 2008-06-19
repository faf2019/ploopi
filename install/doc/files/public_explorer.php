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
 * Affichage de l'explorateur de documents
 * 
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Charge le workflow
 */

doc_getworkflow();
$wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']);

/**
 * Charge les partages
 */

doc_getshare();


$columns = array();
$values = array();

$columns['left']['ico']         = array('label' => '&nbsp;', 'width' => '22');
$columns['auto']['label']       = array('label' => 'Nom', 'options' => array('sort' => true));
$columns['right']['date']       = array('label' => 'Date/Heure', 'width' => '130', 'options' => array('sort' => true));
$columns['right']['workspace']  = array('label' => 'Espace', 'width' => '130', 'options' => array('sort' => true));
$columns['right']['user']       = array('label' => 'Propriétaire', 'width' => '110', 'options' => array('sort' => true));
$columns['right']['type']       = array('label' => 'Type', 'width' => '120', 'options' => array('sort' => true));
$columns['right']['size']       = array('label' => 'Taille', 'width' => '90', 'options' => array('sort' => true));

$columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => '90');

$c = 0;


// affichage des raccourcis ? (pour partages + public à la racine)
if ($currentfolder)
{
    $option_shortcuts = '';
    $parent_filter = "AND f.id_folder = {$currentfolder}";
}
else
{
    $option_shortcuts = ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_displayshortcuts']) ? '' : 'AND (f.id_folder = 0)';
    $parent_filter = '';
}

// dossiers partagés
$list_shared_folders = (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) ? ' OR (f.id IN ('.implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders']).") AND f.id_user <> {$_SESSION['ploopi']['userid']} {$option_shortcuts})" : '';

// dossiers dont l'utilisateur connecté est le validateur
$list_wf_folders = (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders'])) ? implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']) : '';
$list_wf_folders_option = ($list_wf_folders != '') ? " OR f_val.id_folder IN ({$list_wf_folders}) " : '';

$sql =  "
        SELECT      f.*,
                    u.login,
                    u.lastname,
                    u.firstname,
                    w.label
        FROM        ploopi_mod_doc_folder f

        LEFT JOIN   ploopi_user u
        ON          f.id_user = u.id

        LEFT JOIN   ploopi_workspace w
        ON          w.id_workspace = w.id

        LEFT JOIN   ploopi_mod_doc_folder f_val
        ON          f_val.id = f.waiting_validation

        WHERE       f.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         f.published = 1
        AND         (f.waiting_validation = 0 OR f.id_user = {$_SESSION['ploopi']['userid']} {$list_wf_folders_option})
        {$parent_filter}

        AND         (
                        (f.id_user = {$_SESSION['ploopi']['userid']} AND f.id_folder = {$currentfolder})
                    OR  (f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces().") AND f.id_user <> {$_SESSION['ploopi']['userid']} {$option_shortcuts})
                    {$list_shared_folders}
                    )

        ORDER by f.name
        ";

$rs = $db->query($sql);

while ($row = $db->fetchrow($rs))
{
    $ldate = ploopi_timestamp2local($row['timestp_modify']);

    $readonly = (($row['readonly'] && $row['id_user'] != $_SESSION['ploopi']['userid']) || $docfolder_readonly_content);

    $ico = 'ico_folder';
    if ($row['foldertype'] == 'shared') $ico .= '_shared';
    if ($row['foldertype'] == 'public') $ico .= '_public';
    if ($row['readonly']) $ico .= '_locked';
    $ico .= '.png';

    $tools = '';

    if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFOLDER) && (!$readonly) && ($row['nbelements'] == 0)))
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_folderdelete({$row['id']});\"><img src=\"./modules/doc/img/ico_trash.png\" /></a>";
    }
    else
    {
        $tools = "<a style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:alert('Vous ne disposez pas des autorisations nécessaires pour supprimer ce dossier');\"><img src=\"./modules/doc/img/ico_trash_grey.png\" /></a>";
    }

    $tools .= "<a title=\"Modifier\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_browser({$row['id']},'','doc_foldermodify&addfolder=0');\"><img src=\"./modules/doc/img/ico_modify.png\" /></a>";

    $linked = ($currentfolder ==0 && $row['id_folder'] != 0 && ($row['foldertype'] == 'shared' || $row['foldertype'] == 'public'));

    $style = $link = '';
    if ($linked)
    {
        $style = 'style="font-style:italic;"';

        $link_rs = $db->query("SELECT id, name, foldertype, readonly FROM ploopi_mod_doc_folder WHERE id in ({$row['parents']},{$row['id']})");
        $link_detail = array(' => ');

        while ($link_row = $db->fetchrow($link_rs)) $link_detail[] = $link_row['name'];

        $link = implode(' / ', $link_detail);
    }

    $values[$c]['values']['ico']        = array('label' => "<img src=\"./modules/doc/img/{$ico}\" />");
    $values[$c]['values']['label']      = array('label' => "{$row['name']} {$link}", 'sort_label' => "0 {$row['name']} {$link}");
    $values[$c]['values']['size']       = array('label' => "{$row['nbelements']} element(s)", 'style' => 'text-align:right', 'sort_label' => sprintf("0 %016d", $row['nbelements']));
    $values[$c]['values']['type']       = array('label' => "Dossier {$foldertypes[$row['foldertype']]}", 'sort_label' => "0 Dossier {$foldertypes[$row['foldertype']]}");
    $values[$c]['values']['user']       = array('label' => "{$row['lastname']} {$row['firstname']}", 'sort_label' => "0 {$row['lastname']} {$row['firstname']}");
    $values[$c]['values']['workspace']  = array('label' => $row['label'], 'sort_label' => "0 {$row['label']}");
    $values[$c]['values']['date']       = array('label' => "{$ldate['date']} {$ldate['time']}", 'sort_label' => "0 {$row['timestp_modify']}");
    $values[$c]['values']['actions']    = array('label' => $tools, 'style' => 'text-align:center');

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = 'javascript:void(0);';
    $values[$c]['onclick'] = "javascript:doc_browser('{$row['id']}');";
    $values[$c]['style'] = '';
    $c++;
}

// DISPLAY DRAFT FOLDERS

$where = ($wf_validator) ? " AND f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces().")" : " AND f.id_user = {$_SESSION['ploopi']['userid']} ";

$sql =  "
        SELECT      f.*,
                    u.login,
                    u.lastname,
                    u.firstname,
                    w.label
        FROM        ploopi_mod_doc_folder f

        LEFT JOIN   ploopi_user u
        ON          f.id_user = u.id

        LEFT JOIN   ploopi_workspace w
        ON          f.id_workspace = w.id

        WHERE       f.id_folder = {$currentfolder}
        AND         f.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         f.published = 0

        {$where}

        ORDER by f.name
        ";

$db->query($sql);

if ($db->numrows())
{
    $values[$c]['values']['ico']        = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['label']      = array('label' => 'Dossiers en attente de publication:', 'sort_label' => "2");
    $values[$c]['values']['size']       = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['type']       = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['user']       = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['workspace']  = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['date']       = array('label' => '', 'sort_label' => "2");
    $values[$c]['values']['actions']    = array('label' => '');

    $values[$c]['description'] = 'Dossiers en attente de publication';
    $values[$c]['link'] = '';
    $values[$c]['style'] = 'background-color:#ffe0e0;font-weight:bold;';
    $c++;
}

while ($row = $db->fetchrow())
{
    $ldate = ploopi_timestamp2local($row['timestp_modify']);

    $readonly = (($row['readonly'] && $row['id_user'] != $_SESSION['ploopi']['userid']) || $docfolder_readonly_content);

    $ico = 'ico_folder';
    if ($row['foldertype'] == 'shared') $ico .= '_shared';
    if ($row['foldertype'] == 'public') $ico .= '_public';
    if ($row['readonly']) $ico .= '_locked';
    $ico .= '.png';

    $tools = '';

    if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFOLDER) && (!$readonly) && ($row['nbelements'] == 0)))
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_folderdelete({$row['id']});\"><img src=\"./modules/doc/img/ico_trash.png\" /></a>";
    }
    else
    {
        $tools = "<a style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:alert('Vous ne disposez pas des autorisations nécessaires pour supprimer ce dossier');\"><img src=\"./modules/doc/img/ico_trash_grey.png\" /></a>";
    }

    if ($wf_validator)
    {
        $tools .= "<a title=\"Publier\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_folderpublish({$currentfolder}, {$row['id']});\"><img src=\"./modules/doc/img/ico_validate.png\" /></a>";
    }

    $tools .= "<a title=\"Modifier\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_browser({$row['id']},'','doc_foldermodify&addfolder=0');\"><img src=\"./modules/doc/img/ico_modify.png\" /></a>";

    $values[$c]['values']['ico']        = array('label' => "<img src=\"./modules/doc/img/{$ico}\" />");
    $values[$c]['values']['label']      = array('label' => "{$row['name']}", 'sort_label' => "2 {$row['name']}");
    $values[$c]['values']['size']       = array('label' => "{$row['nbelements']} element(s)", 'style' => 'text-align:right', 'sort_label' =>  sprintf("2 %016d", $row['nbelements']));
    $values[$c]['values']['type']       = array('label' => "Dossier {$foldertypes[$row['foldertype']]}", 'sort_label' => "2 Dossier {$foldertypes[$row['foldertype']]}");
    $values[$c]['values']['user']       = array('label' => "{$row['lastname']} {$row['firstname']}", 'sort_label' => "2 {$row['lastname']} {$row['firstname']}");
    $values[$c]['values']['workspace']  = array('label' => $row['label'], 'sort_label' => "2 {$row['label']}");
    $values[$c]['values']['date']       = array('label' => "{$ldate['date']} {$ldate['time']}", 'sort_label' => "2 {$row['timestp_modify']}");
    $values[$c]['values']['actions']    = array('label' => $tools, 'style' => 'text-align:center');

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = 'javascript:void(0);';
    $values[$c]['onclick'] = "javascript:doc_browser('{$row['id']}');";
    $values[$c]['style'] = '';
    $c++;
}


// DISPLAY FILES

$where = (!empty($list_sharedfile)) ? ' OR f.id IN ('.implode(',', $list_sharedfile).')' : '';

$sql =  "
        SELECT      f.*,
                    u.login,
                    u.lastname,
                    u.firstname,
                    w.label,
                    e.filetype

        FROM        ploopi_mod_doc_file f

        LEFT JOIN   ploopi_user u
        ON          f.id_user = u.id

        LEFT JOIN   ploopi_workspace w
        ON          f.id_workspace = w.id

        LEFT JOIN   ploopi_mod_doc_ext e
        ON          e.ext = f.extension

        WHERE       f.id_folder = {$currentfolder}
        AND         f.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         ((f.id_user = {$_SESSION['ploopi']['userid']} AND f.id_folder = 0) OR f.id_folder!=0 {$where})

        ORDER by f.name
        ";

$db->query($sql);

while ($row = $db->fetchrow())
{
    $ksize = sprintf("%.02f",$row['size']/1024);
    $ldate = ploopi_timestamp2local($row['timestp_modify']);

    $ico = (file_exists("./modules/doc/img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

    $tools = '';

    //if (ploopi_isactionallowed(_DOC_ACTION_DELETEFILE) && (!$docfolder_readonly_content || $row['id_user'] == $_SESSION['ploopi']['userid']))
    if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFILE) && ((!$docfolder_readonly_content && !$row['readonly']) || $row['id_user'] == $_SESSION['ploopi']['userid'])))
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_filedelete({$currentfolder},'{$row['md5id']}');\"><img src=\"./modules/doc/img/ico_trash.png\" /></a>";
    }
    else
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:alert('Vous ne disposez pas des autorisations nécessaires pour supprimer ce fichier');\"><img src=\"./modules/doc/img/ico_trash_grey.png\" /></a>";
    }

    $tools .=   "
                <a title=\"Modifier\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_fileform({$row['id_folder']},'{$row['md5id']}');\"><img src=\"./modules/doc/img/ico_modify.png\" /></a>
                <a title=\"Télécharger\" style=\"display:block;float:right;\" href=\"".ploopi_urlencode("admin.php?op=doc_filedownload&docfile_md5id={$row['md5id']}")."\"><img src=\"./modules/doc/img/ico_download.png\" /></a>
                <a title=\"Télécharger (ZIP)\" style=\"display:block;float:right;\" href=\"".ploopi_urlencode("admin.php?op=doc_filedownloadzip&docfile_md5id={$row['md5id']}")."\"><img src=\"./modules/doc/img/ico_download_zip.png\" /></a>
                ";

    $values[$c]['values']['ico']        = array('label' => "<img src=\"./modules/doc/img/mimetypes/{$ico}\" />");
    $values[$c]['values']['label']      = array('label' => "{$row['name']}", 'sort_label' => "1 {$row['name']}");
    $values[$c]['values']['size']       = array('label' => "{$ksize} ko", 'style' => 'text-align:right', 'sort_label' => sprintf("1 %016d", $ksize*100));
    $values[$c]['values']['type']       = array('label' => "Fichier", 'sort_label' => "1 Fichier");;
    $values[$c]['values']['user']       = array('label' => "{$row['lastname']} {$row['firstname']}", 'sort_label' => "1 {$row['lastname']} {$row['firstname']}");
    $values[$c]['values']['workspace']  = array('label' => $row['label'], 'sort_label' => "1 {$row['label']}");
    $values[$c]['values']['date']       = array('label' => "{$ldate['date']} {$ldate['time']}", 'sort_label' => "1 {$row['timestp_modify']}");
    $values[$c]['values']['actions']    = array('label' => $tools, 'style' => 'text-align:center');

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = ploopi_urlencode("admin.php?op=doc_filedownload&docfile_md5id={$row['md5id']}");
    $values[$c]['style'] = '';
    $c++;
}

// DISPLAY DRAFT FILES

if (!$wf_validator) $where = " AND f.id_user = {$_SESSION['ploopi']['userid']} ";
else $where = '';

$sql =  "
        SELECT      f.*,
                    u.login,
                    u.lastname,
                    u.firstname,
                    w.label,
                    e.filetype,
                    df.name as dfname

        FROM        ploopi_mod_doc_file_draft f

        LEFT JOIN   ploopi_user u
        ON          f.id_user = u.id

        LEFT JOIN   ploopi_workspace w
        ON          f.id_workspace = w.id

        LEFT JOIN   ploopi_mod_doc_ext e
        ON          e.ext = f.extension

        LEFT JOIN   ploopi_mod_doc_file df
        ON          df.id = f.id_docfile

        WHERE       f.id_folder = {$currentfolder}
        AND         f.id_module = {$_SESSION['ploopi']['moduleid']}
        {$where}

        ORDER by f.name
        ";

$db->query($sql);

if ($db->numrows())
{
    $values[$c]['values']['ico']        = array('label' => '', 'sort_label' => "3");
    $values[$c]['values']['label']      = array('label' => 'Fichiers en attente de publication:', 'sort_label' => "3");
    $values[$c]['values']['size']       = array('label' => '', 'sort_label' => "3");
    $values[$c]['values']['type']       = array('label' => '', 'sort_label' => "3");
    $values[$c]['values']['user']       = array('label' => '', 'sort_label' => "3");;
    $values[$c]['values']['workspace']  = array('label' => '', 'sort_label' => "3");
    $values[$c]['values']['date']       = array('label' => '', 'sort_label' => "3");
    $values[$c]['values']['actions']    = array('label' => '');

    $values[$c]['description'] = 'Fichiers en attente de publication';
    $values[$c]['link'] = '';
    $values[$c]['style'] = 'background-color:#ffe0e0;font-weight:bold;';
    $c++;

}

while ($row = $db->fetchrow())
{
    $ksize = sprintf("%.02f",$row['size']/1024);
    $ldate = ploopi_timestamp2local($row['timestp_create']);

    $ico = (file_exists("./modules/doc/img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

    $tools = '';

    if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFILE) && (!$docfolder_readonly_content || $row['id_user'] == $_SESSION['ploopi']['userid'])))
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_filedelete({$currentfolder},'{$row['md5id']}','draft');\"><img src=\"./modules/doc/img/ico_trash.png\" /></a>";
    }
    else
    {
        $tools = "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:alert('Vous ne disposez pas des autorisations nécessaires pour supprimer ce fichier');\"><img src=\"./modules/doc/img/ico_trash_grey.png\" /></a>";
    }

    if ($wf_validator)
    {
        $tools .= "<a title=\"Publier\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:doc_filepublish({$currentfolder},'{$row['md5id']}');\"><img src=\"./modules/doc/img/ico_validate.png\" /></a>";
    }

    $tools .=   "
                <a title=\"Télécharger\" style=\"display:block;float:right;\" href=\"".ploopi_urlencode("admin.php?op=doc_filedownload&docfiledraft_md5id={$row['md5id']}")."\"><img src=\"./modules/doc/img/ico_download.png\" /></a>
                <a title=\"Télécharger (ZIP)\" style=\"display:block;float:right;\" href=\"".ploopi_urlencode("admin.php?op=doc_filedownloadzip&docfiledraft_md5id={$row['md5id']}")."\"><img src=\"./modules/doc/img/ico_download_zip.png\" /></a>
                ";

    $name = $row['name'];
    if ($row['id_docfile']) $name .= ($row['dfname'] != $row['name']) ? " (nouvelle version de &laquo; {$row['dfname']} &raquo;)" : ' (nouvelle version)';

    $values[$c]['values']['ico']        = array('label' => "<img src=\"./modules/doc/img/mimetypes/{$ico}\" />");
    $values[$c]['values']['label']      = array('label' => $name, 'sort_label' => "3 {$name}");
    $values[$c]['values']['size']       = array('label' => "{$ksize} ko", 'style' => 'text-align:right', 'sort_label' => sprintf("3 %016d", $ksize*100));
    $values[$c]['values']['type']       = array('label' => "Fichier", 'sort_label' => "3 Fichier");
    $values[$c]['values']['user']       = array('label' => "{$row['lastname']} {$row['firstname']}", 'sort_label' => "3 {$row['lastname']} {$row['firstname']}");
    $values[$c]['values']['workspace']  = array('label' => $row['label'], 'sort_label' => "3 {$row['label']}");
    $values[$c]['values']['date']       = array('label' => "{$ldate['date']} {$ldate['time']}", 'sort_label' => "3 {$row['timestp_create']}");
    $values[$c]['values']['actions']    = array('label' => $tools, 'style' => 'text-align:center');

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = ploopi_urlencode("admin.php?op=doc_filedownload&docfiledraft_md5id={$row['md5id']}");
    $values[$c]['style'] = '';
    $c++;
}


//if ($sort_option == 'ASC')  $skin->display_array($columns, array_merge($folder_values, $file_values, $draftfolder_values, $draftfile_values), 'doc_explorer');
//else $skin->display_array($columns, array_merge($file_values, $folder_values, $draftfile_values, $draftfolder_values), 'doc_explorer');

$skin->display_array($columns, $values, 'doc_explorer', array('sortable' => true, 'orderby_default' => 'label'));

if (!empty($currentfolder)) include './modules/doc/public_folder_actions.php';
?>
