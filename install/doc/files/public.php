<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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
 * Partie publique du module
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('doc');

include_once './modules/doc/class_docfile.php';
include_once './modules/doc/class_docfolder.php';
include_once './modules/doc/class_docfiledraft.php';

// Met à jour la variable session permettant de savoir si unoconv est disponible (conversion de documents)
if (is_null(ploopi_getsessionvar('unoconv')))
{
    $strUnovconvPath = ploopi_getparam('system_unoconv', _PLOOPI_MODULE_SYSTEM);
    ploopi_setsessionvar('unoconv', $strUnovconvPath != '' && file_exists($strUnovconvPath));
}

if (ploopi_getsessionvar('unoconv') === false && is_null(ploopi_getsessionvar('jodconv')))
{
    $strJodConverter = ploopi_getparam('system_jodwebservice', _PLOOPI_MODULE_SYSTEM);
    ploopi_setsessionvar('jodconv', $strJodConverter != '');
}

$op = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : 'doc_browser';
$currentfolder = (isset($_REQUEST['currentfolder']) && is_numeric($_REQUEST['currentfolder'])) ? $_REQUEST['currentfolder'] : 0;

// Lien vers document sans folder ?
// Cas du lien depuis le moteur de recherche global
if (!empty($_GET['docfile_md5id']) && empty($currentfolder))
{
    $docfile = new docfile();
    if ($docfile->openmd5($_GET['docfile_md5id'])) $currentfolder = $docfile->fields['id_folder'];
    else ploopi_redirect("admin.php?doc_error=unknown_file"); // Fichier inconnu => redirection
}

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->open_simplebloc('Explorateur de documents');

if (ploopi_getparam('doc_explorer_displaytreeview'))
{
    ?>
    <div id="doc_main">
        <div id="doc_treeview">
            <div id="doc_treeview_inner">
                <?php
                // Récupération des dossiers visibles
                $arrFolders = doc_getfolders();

                // Récupération de la structure du treeview
                $arrTreeview = doc_gettreeview($arrFolders);
                echo $skin->display_treeview($arrTreeview['list'], $arrTreeview['tree'], $currentfolder, -1);
                ?>
            </div>
        </div>
        <div id="doc_browser">
    <?

}
else
{
    ?>
    <div id="doc_main_notreeview">
    <?
}
?>
        <div id="doc_browser_inner">
            <?php
            switch($op)
            {
                case 'doc_filedraftvalidate':
                    if (!empty($_GET['docfiledraft_md5id'])) // ouverture directe d'un fichier (lien externe au module)
                    {
                        $docfile = new docfiledraft();
                        if ($docfile->openmd5($_GET['docfiledraft_md5id'])) $currentfolder = $docfile->fields['id_folder'];
                    }

                case 'doc_fileform':
                case 'doc_folderform':
                case 'doc_foldermodify':
                case 'doc_browser':
                case 'doc_search':
                    if (!empty($_GET['doc_error']))
                    {
                        switch($_GET['doc_error'])
                        {
                            case 'unknown_file':
                                $strMsg = "Le fichier que vous avez tenté d'ouvrir n'existe plus";
                            break;

                            default:
                                $strMsg = "Erreur inconnue";
                            break;
                        }
                        ?>
                        <div class="doc_path" style="background:#fff;">
                            <span class="error"><? echo $strMsg ?></span>
                        </div>
                        <?
                    }
                    ?>
                    <div class="doc_path">
                        <a title="Aide" href="javascript:void(0);" onclick="javascript:doc_openhelp(event);" style="float:right;"><img src="./modules/doc/img/ico_help.png" /></a>
                        <?php
                        $docfolder_readonly_content = false;
                        $objFolder = new docfolder();
                        $objFolder->init_description();
                        if (empty($currentfolder) || !$objFolder->open($currentfolder) || !$objFolder->isEnabled()) $currentfolder = 0;
                        ?>

                        <a title="Rechercher un Fichier" href="<?php echo ploopi_urlencode("admin.php?op=doc_search&currentfolder=0"); ?>" style="float:right;"><img src="./modules/doc/img/ico_search.png"></a>

                        <?php
                        if (!doc_folder_contentisreadonly($objFolder->fields, _DOC_ACTION_ADDFILE))
                        {
                            ?>
                            <a title="Créer un nouveau fichier" href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}"); ?>" style="float:right;"><img src="./modules/doc/img/ico_newfile.png"></a>
                            <?php
                        }

                        if (!doc_folder_contentisreadonly($objFolder->fields, _DOC_ACTION_ADDFOLDER))
                        {
                            ?>
                            <a title="Créer un nouveau Dossier" href="<?php echo ploopi_urlencode("admin.php?op=doc_folderform&currentfolder={$currentfolder}&addfolder=1"); ?>" style="float:right;"><img src="./modules/doc/img/ico_newfolder.png"></a>
                            <?php
                        }
                        ?>
                        <a title="Aller au Dossier Racine" href="<?php echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder=0"); ?>" style="float:right;"><img src="./modules/doc/img/ico_home.png"></a>

                        <div>Emplacement :</div>
                        <a <?php if ($currentfolder == 0) echo 'class="doc_pathselected"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder=0"); ?>">
                            <div style="float:left;position:relative;padding:0;height:16px;">
                                <img style="display:block;position:absolute;" src="./modules/doc/img/ico_folder_home.png" />
                            </div>
                            <span style="margin-left:18px;">Racine</span>
                        </a>
                        <?php
                        if ($currentfolder != 0)
                        {
                            doc_getshare();

                            $db->query("SELECT id, name, foldertype, readonly, id_user FROM ploopi_mod_doc_folder WHERE id in ({$objFolder->fields['parents']},{$currentfolder}) ORDER BY length(parents)");

                            while ($row = $db->fetchrow())
                            {
                                $allowed = false;

                                if ($row['id_user'] == $_SESSION['ploopi']['userid'] || ploopi_isadmin() || ploopi_isactionallowed(_DOC_ACTION_ADMIN) || $row['foldertype'] == 'public' || ($row['foldertype'] == 'shared' && in_array($row['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders']))) $allowed = true;

                                if ($allowed)
                                {
                                    ?>
                                    <a <?php if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$row['id']}"); ?>">
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <a <?php if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:alert('Vous n\'avez pas l\'autorisation d'\accéder à ce dossier');">
                                    <?php
                                }
                                ?>
                                    <div style="float:left;position:relative;padding:0;height:16px;">
                                        <img style="display:block;position:absolute;" src="./modules/doc/img/ico_folder<?php if ($row['foldertype'] == 'shared') echo '_shared'; ?><?php if ($row['foldertype'] == 'public') echo '_public'; ?><?php if ($row['readonly']) echo '_locked'; ?>.png" />
                                        <?php
                                        if (!$allowed)
                                        {
                                            ?><img style="display:block;position:absolute;" src="./modules/doc/img/notallowed.png"><?php
                                        }
                                        ?>
                                    </div>
                                    <span style="margin-left:18px;"><?php echo $row['name']; ?></span>
                                </a>
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <?php
                    switch($op)
                    {
                        case 'doc_search':
                            include_once './modules/doc/public_search.php';
                        break;

                        case 'doc_fileform':
                            include_once './modules/doc/public_folder_info.php';

                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <?php include_once './modules/doc/public_file_form.php'; ?>
                            </div>
                            <?php
                        break;

                        case 'doc_folderform':
                        case 'doc_foldermodify':
                            include_once './modules/doc/public_folder_info.php';
                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <?php include_once './modules/doc/public_folder_form.php'; ?>
                            </div>
                            <?php
                        break;

                        default:
                            include_once './modules/doc/public_folder_info.php';
                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <?php include './modules/doc/public_explorer.php'; ?>
                            </div>
                            <?php
                        break;
                    }
                break;
            }
            ?>
        </div>
    <?
    if (ploopi_getparam('doc_explorer_displaytreeview'))
    {
        ?>
    </div>
        <?
    }
    ?>
</div>
<?php
echo $skin->close_simplebloc();
?>
