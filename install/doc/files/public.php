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

/**
 * Chargement des
 */
include_once './modules/doc/class_docfile.php';
include_once './modules/doc/class_docfolder.php';
include_once './modules/doc/class_docfiledraft.php';

$op = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : 'doc_browser';
$currentfolder = (isset($_REQUEST['currentfolder'])) ? $_REQUEST['currentfolder'] : 0;

switch($op)
{
    case 'doc_bd_search':
        echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
        echo $skin->open_simplebloc();
        ?>

        <div id="doc_browser"></div>

        <script type="text/javascript">
            doc_search();
        </script>

        <?
        //include_once './modules/doc/public_legend.php';
        echo $skin->close_simplebloc();
    break;

    default:
        echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
        echo $skin->open_simplebloc();
        ?>
        <div id="doc_browser">
            <?
            switch($op)
            {
                case 'doc_filedraftvalidate':
                    if (!empty($_GET['docfiledraft_md5id'])) // ouverture directe d'un fichier (lien externe au module)
                    {
                        include_once './modules/doc/class_docfiledraft.php';
                        $docfile = new docfiledraft();
                        if ($docfile->openmd5($_GET['docfiledraft_md5id'])) $currentfolder = $docfile->fields['id_folder'];
                    }
                    
                case 'doc_fileform':
                case 'doc_folderform':
                case 'doc_foldermodify':
                case 'doc_browser':
                case 'doc_search':
            
                    include_once './modules/doc/class_docfile.php';
                    include_once './modules/doc/class_docfolder.php';
                    include_once './modules/doc/class_docfiledraft.php';
                    ?>
                    <div class="doc_path">
                        <a title="Aide" href="javascript:void(0);" onclick="javascript:doc_openhelp(event);" style="float:right;"><img src="./modules/doc/img/ico_help.png"></a>
                        <?
                        // voir pour une optimisation de cette partie car on ouvre un docfolder sans doute pour rien
                        $docfolder = new docfolder();
                        $readonly = false;
                        if (!empty($currentfolder))
                        {
                            if (!$docfolder->open($currentfolder)) $currentfolder = 0;
                            else
                            {
                                $readonly = ($docfolder->fields['readonly_content'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']);
                            }
                        }
                        ?>
            
                        <a title="Rechercher un Fichier" href="<? echo ploopi_urlencode("admin.php?op=doc_search&currentfolder=0"); ?>" style="float:right;"><img src="./modules/doc/img/ico_search.png"></a>
            
                        <?
                        if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_ADDFILE) && !$readonly))
                        {
                            ?>
                            <a title="Créer un nouveau fichier" href="<? echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}"); ?>" style="float:right;"><img src="./modules/doc/img/ico_newfile.png"></a>
                            <?
                        }
                        else
                        {
                            ?>
                            <a title="Créer un nouveau fichier" href="javascript:void(0);" style="float:right;"><img src="./modules/doc/img/ico_newfile_grey.png"></a>
                            <?
                        }
                        ?>
                        <?
                        if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_ADDFOLDER) && !$readonly))
                        {
                            ?>
                            <a title="Créer un nouveau Dossier" href="<? echo ploopi_urlencode("admin.php?op=doc_folderform&currentfolder={$currentfolder}&addfolder=1"); ?>" style="float:right;"><img src="./modules/doc/img/ico_newfolder.png"></a>
                            <?
                        }
                        else
                        {
                            ?>
                            <a title="Créer un nouveau Dossier" href="javascript:void(0);" style="float:right;"><img src="./modules/doc/img/ico_newfolder_grey.png"></a>
                            <?
                        }
                        ?>
                        <a title="Aller au Dossier Racine" href="<? echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder=0"); ?>" style="float:right;"><img src="./modules/doc/img/ico_home.png"></a>
            
                        <div>Emplacement :</div>
                        <a <? if ($currentfolder == 0) echo 'class="doc_pathselected"'; ?> href="<? echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder=0"); ?>">
                            <div style="float:left;position:relative;padding:0;height:16px;">
                                <img style="display:block;position:absolute;" src="./modules/doc/img/ico_folder_home.png" />
                            </div>
                            <span style="margin-left:18px;">Racine</span>
                        </a>
                        <?
                        if ($currentfolder != 0)
                        {
                            $docfolder = new docfolder();
                            $docfolder->open($currentfolder);
            
                            doc_getshare();
            
                            $db->query("SELECT id, name, foldertype, readonly, id_user FROM ploopi_mod_doc_folder WHERE id in ({$docfolder->fields['parents']},{$currentfolder}) ORDER BY id");
            
                            while ($row = $db->fetchrow())
                            {
                                $allowed = false;
            
                                if ($row['id_user'] == $_SESSION['ploopi']['userid'] || $row['foldertype'] == 'public' || ($row['foldertype'] == 'shared' && in_array($row['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders']))) $allowed = true;
            
                                if ($allowed)
                                {
                                    ?>
                                    <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="<? echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$row['id']}"); ?>">
                                    <?
                                }
                                else
                                {
                                    ?>
                                    <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:alert('Vous n\'avez pas l\'autorisation d'\accéder à ce dossier');">
                                    <?
                                }
                                ?>
                                    <div style="float:left;position:relative;padding:0;height:16px;">
                                        <img style="display:block;position:absolute;" src="./modules/doc/img/ico_folder<? if ($row['foldertype'] == 'shared') echo '_shared'; ?><? if ($row['foldertype'] == 'public') echo '_public'; ?><? if ($row['readonly']) echo '_locked'; ?>.png" />
                                        <?
                                        if (!$allowed)
                                        {
                                            ?><img style="display:block;position:absolute;" src="./modules/doc/img/notallowed.png"><?
                                        }
                                        ?>
                                    </div>
                                    <span style="margin-left:18px;"><? echo $row['name']; ?></span>
                                </a>
                                <?
                            }
                        }
                        ?>
                    </div>
            
                    <?
                    $docfolder_readonly_content = false;
            
                    if (!empty($currentfolder))
                    {
                        $docfolder->open($currentfolder);
                        $docfolder_readonly_content = ($docfolder->fields['readonly_content'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']);
                    }
                    ?>
            
                    <?
                    switch($op)
                    {
                        case 'doc_search':
                            include_once './modules/doc/public_search.php';
                        break;
            
                        case 'doc_fileform':
                            include_once './modules/doc/public_folder_info.php';
                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <? include_once './modules/doc/public_file_form.php'; ?>
                            </div>
                            <?
                        break;
            
                        case 'doc_folderform':
                        case 'doc_foldermodify':
                            include_once './modules/doc/public_folder_info.php';
                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <? include_once './modules/doc/public_folder_form.php'; ?>
                            </div>
                            <?
                        break;
            
                        default:
                            include_once './modules/doc/public_folder_info.php';
                            ?>
                            <div id="doc_explorer" class="doc_explorer_main">
                            <? include_once './modules/doc/public_explorer.php'; ?>
                            </div>
                            <?
                        break;
                    }
                break;
            }
            ?> 
        </div>
        <?
        echo $skin->close_simplebloc();
    break;

}
?>
