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
 * Charge le validation
 */

doc_getvalidation();
$wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']);

/**
 * Charge les partages
 */

doc_getshare();

/**
 * Initialise le tableau principal de l'explorateur de fichiers
 */

$columns = array();
$values = array();

$columns['auto']['label'] = 
    array(
        'label' => 'Nom', 
        'options' => array('sort' => true)
    );

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
    $columns['right']['date'] = 
        array(
            'label' => 'Date/Heure', 
            'width' => 115, 
            'options' => array('sort' => true)
        );
    
if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
    $columns['right']['workspace'] = 
        array(
            'label' => 'Espace', 
            'width' => 130, 
            'options' => array('sort' => true)
        );

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
    $columns['right']['user'] = 
        array(
            'label' => 'Propriétaire', 
            'width' => 120, 
            'options' => array('sort' => true)
        );

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
    $columns['right']['size'] = 
        array(
            'label' => 'Taille', 
            'width' => 90, 
            'options' => array('sort' => true)
        );

$columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => '90');

$c = 0;

// DISPLAY FOLDERS

// + du module
// + du dossier

// + publié + (utilisateur = propriétaire) ou (partagé a l'utilisateur) ou (public) ou (dossier à valider et utilisateur = validateur du dossier)
// ou validateur

$arrWhere = array();

// Module
$arrWhere['module'] = "f.id_module = {$_SESSION['ploopi']['moduleid']}";
// Dossier 
$arrWhere['folder'] = "f.id_folder = {$currentfolder}";

// Utilisateur "standard"
if (!$wf_validator && !ploopi_isadmin()) 
{
    // Publié (ou propriétaire)
    $arrWhere['published'] = "(f.published = 1 OR f.id_user = {$_SESSION['ploopi']['userid']})";
     
    // Prioriétaire
    $arrWhere['visibility']['user'] = "f.id_user = {$_SESSION['ploopi']['userid']}"; 
    // Partagé
    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) $arrWhere['visibility']['shared'] = "(f.foldertype = 'shared' AND f.id IN (".implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])."))";
    // Public
    $arrWhere['visibility']['public'] = "(f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces()."))";
    
    // Synthèse visibilité
    $arrWhere['visibility'] = '('.implode(' OR ', $arrWhere['visibility']).')';
}
    
$strWhere = implode(' AND ', $arrWhere);

$sql = "
    SELECT      f.*,
                u.id as user_id,
                u.login,
                u.lastname,
                u.firstname,
                w.id as workspace_id,
                w.label
                
    FROM        ploopi_mod_doc_folder f

    LEFT JOIN   ploopi_user u
    ON          f.id_user = u.id

    LEFT JOIN   ploopi_workspace w
    ON          f.id_workspace = w.id

    LEFT JOIN   ploopi_mod_doc_folder f_val
    ON          f_val.id = f.waiting_validation

    WHERE  {$strWhere}     
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
        $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer ce dossier ?\')) document.location.href=\''.ploopi_urlencode("admin.php?ploopi_op=doc_folderdelete&docfolder_id={$row['id']}").'\'; return(false);"><img src="./modules/doc/img/ico_trash.png" /></a>';
    }
    else
    {
        $tools = '<a style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:alert(\'Vous ne disposez pas des autorisations nécessaires pour supprimer ce dossier\');"><img src="./modules/doc/img/ico_trash_grey.png" /></a>';
    }

    if (!$row['published'] && ($wf_validator || ploopi_isadmin()))
    {
        $tools .= '<a title="Publier" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=doc_folderpublish&currentfolder={$currentfolder}&docfolder_id={$row['id']}").'"><img src="./modules/doc/img/ico_validate.png" /></a>';
    }    
    
    $tools .= '<a title="Modifier" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?op=doc_foldermodify&currentfolder={$row['id']}&addfolder=0").'"><img src="./modules/doc/img/ico_modify.png" /></a>';

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

    $values[$c]['values']['label']      = array('label' => "<img src=\"./modules/doc/img/{$ico}\" /><span>&nbsp;{$row['name']} {$link}</span>", 'sort_label' => '0 '.strtolower($row['name'])." {$link}");

    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
        $values[$c]['values']['size'] = 
            array(
                'label' => "{$row['nbelements']} élément".($row['nbelements']>1 ? 's' : ''), 
                'style' => 'text-align:right', 
                'sort_label' => sprintf("0 %016d", $row['nbelements'])
            );
    
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
        $values[$c]['values']['user'] = 
            array(
                'label' => empty($row['user_id']) ? '<em>supprimé</em>' : "{$row['lastname']} {$row['firstname']}", 
                'sort_label' => '0 '.(empty($row['user_id']) ? '' : strtolower("{$row['lastname']} {$row['firstname']}"))
            );
    
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
        $values[$c]['values']['workspace'] = 
            array(
                'label' => empty($row['workspace_id']) ? '<em>supprimé</em>' : $row['label'], 
                'sort_label' => '0 '.(empty($row['workspace_id']) ? '' : strtolower($row['label']))
            );
    
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
        $values[$c]['values']['date'] = 
            array(
                'label' => $ldate['date'].' '.substr($ldate['time'], 0, 5), 
                'sort_label' => "0 {$row['timestp_modify']}"
            );
    
    $values[$c]['values']['actions'] = 
        array(
            'label' => $tools, 
            'style' => 'text-align:center'
        );

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$row['id']}");
    $values[$c]['style'] = ($row['published']) ? '' : 'background-color:#ffe0e0;';
    $c++;
}

// DISPLAY FILES
$arrWhere = array();

// Module
$arrWhere['module'] = "f.id_module = {$_SESSION['ploopi']['moduleid']}";

// Dossier : /!\ l'admin system voit tous les fichiers dans 'racine' 
$arrWhere['folder'] = ($currentfolder || ploopi_isadmin()) ? "f.id_folder = {$currentfolder}" : "f.id_folder = {$currentfolder} AND f.id_user = {$_SESSION['ploopi']['userid']}";

$strWhere = implode(' AND ', $arrWhere);

$sql = "
    SELECT      f.*,
                u.id as user_id,
                u.login,
                u.lastname,
                u.firstname,
                w.id as workspace_id,
                w.label,
                e.filetype

    FROM        ploopi_mod_doc_file f

    LEFT JOIN   ploopi_user u
    ON          f.id_user = u.id

    LEFT JOIN   ploopi_workspace w
    ON          f.id_workspace = w.id

    LEFT JOIN   ploopi_mod_doc_ext e
    ON          e.ext = f.extension

    WHERE   {$strWhere}       
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
        $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer ce fichier ?\')) document.location.href=\''.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedelete&currentfolder={$currentfolder}&docfile_md5id={$row['md5id']}").'\'; return(false);"><img src="./modules/doc/img/ico_trash.png" /></a>';
    }
    else
    {
        $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:alert(\'Vous ne disposez pas des autorisations nécessaires pour supprimer ce fichier\');"><img src="./modules/doc/img/ico_trash_grey.png" /></a>';
    }

    $tools .= '
        <a title="Modifier" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$row['md5id']}&docfile_tab=modify").'"><img src="./modules/doc/img/ico_modify.png" /></a>
        <a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownload&docfile_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download.png" /></a>
        <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownloadzip&docfile_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download_zip.png" /></a>
    ';

    $values[$c]['values']['label'] = 
        array(
            'label' => "<img src=\"./modules/doc/img/mimetypes/{$ico}\" /><span>&nbsp;{$row['name']}</span>", 
            'sort_label' => '1 '.strtolower($row['name'])
        );

    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
        $values[$c]['values']['size'] = 
            array(
                'label' => "{$ksize} ko", 
                'style' => 'text-align:right', 
                'sort_label' => sprintf("1 %016d", $ksize*100)
            );
            
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
        $values[$c]['values']['user'] = 
            array(
                'label' => empty($row['user_id']) ? '<em>supprimé</em>' : "{$row['lastname']} {$row['firstname']}", 
                'sort_label' => '1 '.(empty($row['user_id']) ? '' : strtolower("{$row['lastname']} {$row['firstname']}"))
            );
            
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
        $values[$c]['values']['workspace'] = 
            array(
                'label' => empty($row['workspace_id']) ? '<em>supprimé</em>' : $row['label'], 
                'sort_label' => '1 '.(empty($row['workspace_id']) ? '' : strtolower($row['label']))
            );
            
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
        $values[$c]['values']['date'] = 
            array(
                'label' => $ldate['date'].' '.substr($ldate['time'], 0, 5), 
                'sort_label' => "1 {$row['timestp_modify']}"
            );
        
    $values[$c]['values']['actions'] = 
        array(
            'label' => $tools, 
            'style' => 'text-align:center'
        );

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$row['md5id']}&docfile_tab=open");
    $values[$c]['style'] = '';
    $c++;
}

// DISPLAY DRAFT FILES
$arrWhere = array();

// Module
$arrWhere['module'] = "f.id_module = {$_SESSION['ploopi']['moduleid']}";

// Dossier : /!\ l'admin system voit tous les fichiers dans 'racine' 
$arrWhere['folder'] = ($currentfolder || ploopi_isadmin()) ? "f.id_folder = {$currentfolder}" : "f.id_folder = {$currentfolder} AND f.id_user = {$_SESSION['ploopi']['userid']}";

if (!$wf_validator && !ploopi_isadmin()) $arrWhere['user'] = "f.id_user = {$_SESSION['ploopi']['userid']} ";

$strWhere = implode(' AND ', $arrWhere);

$sql = "
    SELECT      f.*,
                u.id as user_id,
                u.login,
                u.lastname,
                u.firstname,
                w.id as workspace_id,
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

    WHERE       {$strWhere}
";

$db->query($sql);

while ($row = $db->fetchrow())
{
    $ksize = sprintf("%.02f",$row['size']/1024);
    $ldate = ploopi_timestamp2local($row['timestp_create']);

    $ico = (file_exists("./modules/doc/img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

    $tools = '';

    if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFILE) && (!$docfolder_readonly_content || $row['id_user'] == $_SESSION['ploopi']['userid'])))
    {
        $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer ce fichier ?\')) document.location.href=\''.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedraftdelete&currentfolder={$currentfolder}&docfiledraft_md5id={$row['md5id']}").'\'; return(false);"><img src="./modules/doc/img/ico_trash.png" /></a>';
    }
    else
    {
        $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:alert(\'Vous ne disposez pas des autorisations nécessaires pour supprimer ce fichier\');"><img src="./modules/doc/img/ico_trash_grey.png" /></a>';
    }

    if ($wf_validator || ploopi_isadmin())
    {
        $tools .= '<a title="Publier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir publier ce fichier ?\')) document.location.href=\''.ploopi_urlencode("admin-light.php?ploopi_op=doc_filepublish&currentfolder={$currentfolder}&docfiledraft_md5id={$row['md5id']}").'\'; return(false);"><img src="./modules/doc/img/ico_validate.png" /></a>';
    }

    $tools .= '
        <a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownload&docfiledraft_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download.png" /></a>
        <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownloadzip&docfiledraft_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download_zip.png" /></a>
    ';

    $name = $row['name'];
    if ($row['id_docfile']) $name .= ($row['dfname'] != $row['name']) ? " (nouvelle version de &laquo; {$row['dfname']} &raquo;)" : ' (nouvelle version)';

    $values[$c]['values']['label'] = 
        array(
            'label' => "<img src=\"./modules/doc/img/mimetypes/{$ico}\" /><span>&nbsp;{$name}</span>", 
            'sort_label' => '3 '.strtolower($row['name'])
        );
        
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
        $values[$c]['values']['size'] = 
            array(
                'label' => "{$ksize} ko", 'style' => 'text-align:right', 
                'sort_label' => sprintf("3 %016d", $ksize*100)
            );

    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
        $values[$c]['values']['user'] = 
            array(
                'label' => empty($row['user_id']) ? '<em>supprimé</em>' : "{$row['lastname']} {$row['firstname']}", 
                'sort_label' => '3 '.(empty($row['user_id']) ? '' : strtolower("{$row['lastname']} {$row['firstname']}"))
            );
            
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
        $values[$c]['values']['workspace']  = 
            array(
                'label' => empty($row['workspace_id']) ? '<em>supprimé</em>' : $row['label'], 
                'sort_label' => '3 '.(empty($row['workspace_id']) ? '' : strtolower($row['label']))
            );

    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
        $values[$c]['values']['date'] = 
            array(
                'label' => $ldate['date'].' '.substr($ldate['time'], 0, 5), 
                'sort_label' => "3 {$row['timestp_create']}"
            );

    $values[$c]['values']['actions'] = 
        array(
            'label' => $tools, 
            'style' => 'text-align:center'
        );

    $values[$c]['description'] = $row['description'];
    $values[$c]['link'] = ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownload&docfiledraft_md5id={$row['md5id']}");
    $values[$c]['style'] = 'background-color:#ffe0e0;';
    $c++;
}


//if ($sort_option == 'ASC')  $skin->display_array($columns, array_merge($folder_values, $file_values, $draftfolder_values, $draftfile_values), 'doc_explorer');
//else $skin->display_array($columns, array_merge($file_values, $folder_values, $draftfile_values, $draftfolder_values), 'doc_explorer');

$skin->display_array($columns, $values, 'doc_explorer', array('sortable' => true, 'orderby_default' => 'label'));

if (!empty($currentfolder)) include './modules/doc/public_folder_actions.php';
?>
