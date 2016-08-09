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
 * Op�rations sur les documents
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

switch($ploopi_op)
{

    // Appel� depuis fonction javascript d'ouverture du popup de navigation/s�lection de fichier
    case 'documents_selectfile':
        if (empty($_REQUEST['documents_id'])) return;

        $documents_id = $_REQUEST['documents_id'];

        ob_start();

        ?>
        <div id="ploopidocuments_<?php echo ploopi_htmlentities($documents_id); ?>">
            <?php ploopi_documents_browser($_SESSION['documents'][$documents_id]['currentfolder'], $documents_id); ?>
        </div>
        <?php

        $content = ob_get_contents();
        ob_end_clean();

        ploopi_die($skin->create_popup('Explorateur de fichiers', $content, 'ploopi_documents_popup'));
    break;



    case 'documents_browser':
        include_once './include/classes/documents.php';

        // V�rification de l'id de l'instance
        if (!isset($_REQUEST['documents_id'])) return;

        $documents_id = $_REQUEST['documents_id'];

        // V�rification des param�tres de l'instance
        if (!isset($_SESSION['documents'][$documents_id])) return;


        if (isset($_REQUEST['currentfolder'])) $currentfolder = $_REQUEST['currentfolder'];
        else $currentfolder = $_SESSION['documents'][$documents_id]['currentfolder'];

        ploopi_documents_browser($currentfolder, $documents_id);

        ploopi_die();
    break;


    case 'documents_downloadfile':
        if (!empty($_GET['documentsfile_id']))
        {
            include_once './include/classes/documents.php';

            $documentsfile = new documentsfile();

            if ($documentsfile->openmd5($_GET['documentsfile_id']))
            {
                $attachement = true;

                if (isset($_GET['attachement']) && ($_GET['attachement'] == 0 || $_GET['attachement'] == 'false')) $attachement = false;

                if (file_exists($documentsfile->getfilepath())) ploopi_downloadfile($documentsfile->getfilepath(),$documentsfile->fields['name'], false, $attachement);
            }
        }

        echo "Le fichier n'existe pas";
        ploopi_redirect('admin.php', true, true, 2);
    break;

    case 'documents_downloadfile_zip':

        if (!empty($_GET['documentsfile_id']))
        {
            include_once './include/classes/documents.php';

            $documentsfile = new documentsfile();
            if ($documentsfile->openmd5($_GET['documentsfile_id']))
            {

                $tmpfoldername = md5(uniqid(rand(), true));
                $zip_path = ploopi_documents_getpath()._PLOOPI_SEP.'zip'._PLOOPI_SEP.$tmpfoldername;
                if (!is_dir($zip_path)) ploopi_makedir($zip_path);

                if (file_exists($documentsfile->getfilepath()) && is_writeable($zip_path))
                {
                    $zip_filename = $documentsfile->fields['name'].'.zip';

                    $objZip = new ZipArchive();
                    if ($objZip->open($zip_path._PLOOPI_SEP.$zip_filename, ZIPARCHIVE::CREATE) === TRUE)
                    {
                        $objZip->addFile($documentsfile->getfilepath(), '/'.$documentsfile->fields['name']);
                        $objZip->close();
                    }

                    // T�l�chargement du fichier zip
                    ploopi_downloadfile($zip_path._PLOOPI_SEP.$zip_filename, $zip_filename, true, true, false);

                    // Suppression du dossier temporaire
                    if(isset($zip_path) && is_dir($zip_path)) ploopi_deletedir($zip_path);

                    // Vidage buffer
                    ploopi_die(null, true);
                }
            }
        }

        echo "Le fichier n'existe pas";
        ploopi_redirect('admin.php', true, true, 2);
    break;

    case 'documents_savefolder':
        include_once './include/classes/documents.php';
        $documentsfolder = new documentsfolder();
        $documentsfolder_parent = new documentsfolder();

        // Contr�le currentfolder
        if (!isset($_GET['currentfolder']) || !$documentsfolder_parent->openmd5($_GET['currentfolder'])) ploopi_die();
        // Contr�le id instance
        if (!isset($_GET['documents_id']) || !isset($_SESSION['documents'][$_GET['documents_id']])) ploopi_die();

        $currentfolder = $_GET['currentfolder'];
        $documents_id = $_GET['documents_id'];

        if (!empty($_GET['documentsfolder_id']))
        {
            $documentsfolder->openmd5($_GET['documentsfolder_id']);
            $documentsfolder->setvalues($_POST,'documentsfolder_');

            if (isset($_POST['fck_documentsfolder_description']))
                $documentsfolder->fields['description'] = $_POST['fck_documentsfolder_description'];

            $documentsfolder->save();
        }
        else // new folder
        {
            $documentsfolder->setvalues($_POST,'documentsfolder_');
            if (isset($_POST['fck_documentsfolder_description']))
                $documentsfolder->fields['description'] = $_POST['fck_documentsfolder_description'];

            $documentsfolder->fields['id_folder'] = $documentsfolder_parent->fields['id'];
            $documentsfolder->fields['id_object'] = $_SESSION['documents'][$documents_id]['id_object'];
            $documentsfolder->fields['id_record'] = $_SESSION['documents'][$documents_id]['id_record'];
            $documentsfolder->fields['id_module'] = $_SESSION['documents'][$documents_id]['id_module'];
            $documentsfolder->fields['id_user'] = $_SESSION['documents'][$documents_id]['id_user'];
            $documentsfolder->fields['id_workspace'] = $_SESSION['documents'][$documents_id]['id_workspace'];
            $documentsfolder->save();

            if (!empty($_SESSION['documents'][$documents_id]['callback_inc'])) include $_SESSION['documents'][$documents_id]['callback_inc'];
            if (!empty($_SESSION['documents'][$documents_id]['callback_func'])) $_SESSION['documents'][$documents_id]['callback_func']('savefolder', $documentsfolder, true);
        }
        ?>
        <script type="text/javascript">
            window.parent.ploopi_documents_browser('<?php echo ploopi_queryencode("ploopi_op=documents_browser&currentfolder={$currentfolder}&documents_id={$documents_id}"); ?>', '<?php echo ploopi_htmlentities($documents_id); ?>');
            window.parent.ploopi_hidepopup('ploopi_documents_openfolder_popup');
        </script>
        <?php
        ploopi_die();
    break;

    case 'documents_openfolder':

        if (empty($_GET['currentfolder'])) return;
        if (empty($_GET['documents_id'])) return;

        ob_start();
        include_once './include/classes/documents.php';
        $documentsfolder = new documentsfolder();


        if (empty($_GET['documentsfolder_id']))
        {
            $documentsfolder->init_description();
            $title = "Nouveau Dossier";
        }
        else
        {
            $documentsfolder->openmd5($_GET['documentsfolder_id']);
            $title = "Modification du Dossier";
        }

        $url = "admin-light.php?ploopi_op=documents_savefolder&currentfolder={$_GET['currentfolder']}&documents_id={$_GET['documents_id']}";
        if (!empty($_GET['documentsfolder_id'])) $url .= "&documentsfolder_id={$_GET['documentsfolder_id']}";

        ?>
        <form id="documents_folderform" action="<?php echo ploopi_urlencode($url); ?>" method="post" target="documents_folderform_iframe" enctype="multipart/form-data">
        <div class="ploopi_form">
            <div class="documents_formcontent">
                <p>
                    <label>Libell�:</label>
                    <input type="text" class="text" name="documentsfolder_name" value="<?php echo ploopi_htmlentities($documentsfolder->fields['name']); ?>">
                </p>
                <p>
                    <label>Description:</label>
                    <span>
                    <?php
                    include_once './lib/fckeditor/fckeditor.php' ;

                    $oFCKeditor = new FCKeditor('fck_documentsfolder_description') ;

                    $oFCKeditor->BasePath = './lib/fckeditor/';

                    // default value
                    $oFCKeditor->Value = $documentsfolder->fields['description'];

                    // width & height
                    $oFCKeditor->Width='100%';
                    $oFCKeditor->Height='150';

                    $oFCKeditor->Config['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/js/documents/fckconfig.js';
                    $oFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH.'/';

                    // render
                    $oFCKeditor->Create('FCKeditor_DocFileDesc') ;
                    ?>
                    </span>
                </p>
            </div>
            <div class="documents_formcontent" style="text-align:right;padding:4px;">
                <input type="button" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('ploopi_documents_openfolder_popup');">
                <input type="submit" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_SAVE; ?>">
            </div>
        </div>
        </form>
        <iframe name="documents_folderform_iframe" src="./img/blank.gif" style="display:none;"></iframe>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        echo $skin->create_popup($title, $content, 'ploopi_documents_openfolder_popup');
        ploopi_die();
    break;

    case 'documents_savemultiplefile':
        include_once './include/classes/documents.php';
        $documentsfolder = new documentsfolder();

        // Contr�le currentfolder
        if (!isset($_GET['currentfolder']) || !$documentsfolder->openmd5($_GET['currentfolder'])) ploopi_die();
        // Contr�le id instance
        if (!isset($_GET['documents_id']) || !isset($_SESSION['documents'][$_GET['documents_id']])) ploopi_die();

        $currentfolder = $_GET['currentfolder'];
        $documents_id = $_GET['documents_id'];

        // Fichiers fournis?
        if (!empty($_FILES['documentsfile_files']['name']))
        {
            foreach(array_keys($_FILES['documentsfile_files']['name']) as $k) {
                $documentsfile = new documentsfile();
                $documentsfile->fields['id_object'] = $_SESSION['documents'][$documents_id]['id_object'];
                $documentsfile->fields['id_record'] = $_SESSION['documents'][$documents_id]['id_record'];
                $documentsfile->fields['id_module'] = $_SESSION['documents'][$documents_id]['id_module'];
                $documentsfile->fields['id_user'] = $_SESSION['documents'][$documents_id]['id_user'];
                $documentsfile->fields['id_workspace'] = $_SESSION['documents'][$documents_id]['id_workspace'];
                $documentsfile->fields['id_folder'] = $documentsfolder->fields['id'];
                $documentsfile->fields['timestp_file'] = ploopi_createtimestamp();
                $documentsfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
                $documentsfile->settmpfile($_FILES['documentsfile_files']['tmp_name'][$k]);
                $documentsfile->fields['name'] = $_FILES['documentsfile_files']['name'][$k];
                $documentsfile->fields['size'] = $_FILES['documentsfile_files']['size'][$k];
                $error = $documentsfile->save();

                if (!$error) {
                    if (!empty($_SESSION['documents'][$documents_id]['callback_inc'])) include_once $_SESSION['documents'][$documents_id]['callback_inc'];
                    if (!empty($_SESSION['documents'][$documents_id]['callback_func'])) $_SESSION['documents'][$documents_id]['callback_func']('savefile', $documentsfile, $k == sizeof($_FILES['documentsfile_files']['name'])-1);
                }
            }
        }

        ?>
        <script type="text/javascript">
            window.parent.ploopi_documents_browser('<?php echo ploopi_queryencode("ploopi_op=documents_browser&currentfolder={$currentfolder}&documents_id={$documents_id}"); ?>', '<?php echo ploopi_htmlentities($documents_id); ?>');
            window.parent.ploopi_hidepopup('ploopi_documents_openfile_popup');
        </script>
        <?php
        ploopi_die();

    break;

    case 'documents_savefile':
        include_once './include/classes/documents.php';
        $documentsfile = new documentsfile();
        $documentsfolder = new documentsfolder();

        // Contr�le currentfolder
        if (!isset($_GET['currentfolder']) || !$documentsfolder->openmd5($_GET['currentfolder'])) ploopi_die();
        // Contr�le id instance
        if (!isset($_GET['documents_id']) || !isset($_SESSION['documents'][$_GET['documents_id']])) ploopi_die();

        $currentfolder = $_GET['currentfolder'];
        $documents_id = $_GET['documents_id'];

        if (!empty($_GET['documentsfile_id'])) $documentsfile->openmd5($_GET['documentsfile_id']);
        else
        {
            $documentsfile->fields['id_object'] = $_SESSION['documents'][$documents_id]['id_object'];
            $documentsfile->fields['id_record'] = $_SESSION['documents'][$documents_id]['id_record'];
            $documentsfile->fields['id_module'] = $_SESSION['documents'][$documents_id]['id_module'];
            $documentsfile->fields['id_user'] = $_SESSION['documents'][$documents_id]['id_user'];
            $documentsfile->fields['id_workspace'] = $_SESSION['documents'][$documents_id]['id_workspace'];
        }

        $documentsfile->fields['id_folder'] = $documentsfolder->fields['id'];
        $documentsfile->setvalues($_POST,'documentsfile_');

        if (isset($_POST['fck_documentsfile_description']))
            $documentsfile->fields['description'] = $_POST['fck_documentsfile_description'];

        if (isset($documentsfile->fields['timestp_file'])) $documentsfile->fields['timestp_file'] = ploopi_local2timestamp($documentsfile->fields['timestp_file']);


        if (!empty($_FILES['documentsfile_file']['name']))
        {
            $documentsfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
            $documentsfile->settmpfile($_FILES['documentsfile_file']['tmp_name']);
            $documentsfile->fields['name'] = $_FILES['documentsfile_file']['name'];
            $documentsfile->fields['size'] = $_FILES['documentsfile_file']['size'];
        }

        $error = $documentsfile->save();

        if (!$error) {
            if (!empty($_SESSION['documents'][$documents_id]['callback_inc'])) include $_SESSION['documents'][$documents_id]['callback_inc'];
            if (!empty($_SESSION['documents'][$documents_id]['callback_func'])) $_SESSION['documents'][$documents_id]['callback_func']('savefile', $documentsfile, true);
            ?>
            <script type="text/javascript">
                <?php
                // S�lection directe d'un fichier
                if (isset($_GET['selectfile'])) {

                    if ($_SESSION['documents'][$documents_id]['mode'] == 'tofield')
                    {
                        echo "dest = $('{$_SESSION['documents'][$documents_id]['target']}'); if (dest.type) dest.value='{$documentsfile->fields['name']}'; else dest.innerHTML='{$documentsfile->fields['name']}'; ploopi_getelem('{$_SESSION['documents'][$documents_id]['target']}_id').value='{$documentsfile->fields['id']}';ploopi_hidepopup('ploopi_documents_popup');";
                    }
                    elseif ($_SESSION['documents'][$documents_id]['mode'] == 'tocallback')
                    {
                        echo "window.parent.{$_SESSION['documents'][$documents_id]['target']}({$documentsfile->fields['id']}, '".addslashes($documentsfile->fields['name'])."', '".ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$documentsfile->fields['md5id']}")."');";
                    }

                }
                // Mise � jour du navigateur
                else {
                    ?>
                    window.parent.ploopi_documents_browser('<?php echo ploopi_queryencode("ploopi_op=documents_browser&currentfolder={$currentfolder}&documents_id={$documents_id}"); ?>', '<?php echo ploopi_htmlentities($documents_id); ?>');
                    <?php
                }
                ?>
                window.parent.ploopi_hidepopup('ploopi_documents_openfile_popup');
            </script>
            <?php
        }
        ploopi_die();
    break;

    case 'documents_addmultiplefiles':
        ob_start();

        if (empty($_GET['currentfolder'])) return;
        if (empty($_GET['documents_id'])) return;

        $url = "admin-light.php?ploopi_op=documents_savemultiplefile&currentfolder={$_GET['currentfolder']}&documents_id={$_GET['documents_id']}";
        ?>
        <form id="documents_folderform" action="<?php echo ploopi_urlencode($url); ?>" method="post" target="documents_fileform_iframe" enctype="multipart/form-data">
        <div class="ploopi_form">
            <div class="documents_formcontent">
                <p>
                    <label>Choix multiple:<br /><em>(touches CTRL et SHIFT)</em></label>
                    <input type="file" multiple="multiple" class="text" name="documentsfile_files[]" tabindex="1">
                </p>
            </div>
            <div class="documents_formcontent" style="text-align:right;padding:4px;">
                <input type="button" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('ploopi_documents_openfile_popup');">
                <input type="submit" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="7">
            </div>
        </div>
        </form>
        <iframe name="documents_fileform_iframe" src="./img/blank.gif" style="display:none;"></iframe>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        echo $skin->create_popup("Ajout mutiple de fichiers", $content, 'ploopi_documents_openfile_popup');
        ploopi_die();
    break;

    case 'documents_openfile':
        ob_start();
        include_once './include/classes/documents.php';
        $documentsfile = new documentsfile();

        if (empty($_GET['currentfolder'])) return;
        if (empty($_GET['documents_id'])) return;

        if (empty($_GET['documentsfile_id']))
        {
            $documentsfile->init_description();
            $documentsfile->fields['id_folder'] = $_GET['currentfolder'];
            $title = "Nouveau Fichier";
        }
        else
        {
            $documentsfile->openmd5($_GET['documentsfile_id']);
            $title = "Modification du Fichier";
        }

        $ldate = ($documentsfile->fields['timestp_file']!=0 && $documentsfile->fields['timestp_file']!='') ? ploopi_timestamp2local($documentsfile->fields['timestp_file']) : array('date' => '');

        $url = "admin-light.php?ploopi_op=documents_savefile&currentfolder={$_GET['currentfolder']}&documents_id={$_GET['documents_id']}";
        if (!empty($_GET['documentsfile_id'])) $url .= "&documentsfile_id={$_GET['documentsfile_id']}";
        if (isset($_GET['selectfile'])) $url .= "&selectfile";

        ?>
        <form id="documents_folderform" action="<?php echo ploopi_urlencode($url); ?>" method="post" target="documents_fileform_iframe" enctype="multipart/form-data" onsubmit="javascript:return ploopi_documents_validate(this)">
        <div class="ploopi_form">
            <div class="documents_formcontent">
                <?php
                if (empty($_GET['documentsfile_id']))
                {
                    ?>
                    <p>
                        <label>Fichier:</label>
                        <input type="file" class="text" name="documentsfile_file" tabindex="1">
                    </p>
                    <?php
                }
                else
                {
                    ?>
                    <p>
                        <label>Nom du Fichier:</label>
                        <input type="input" class="text" name="documentsfile_name" value="<?php echo ploopi_htmlentities($documentsfile->fields['name']); ?>" tabindex="2">
                    </p>
                    <p>
                        <label>Nouveau Fichier:</label>
                        <input type="file" class="text" name="documentsfile_file" tabindex="2">
                    </p>
                    <p>
                        <label>Dossier Parent:</label>
                        <select name="documentsfile_id_folder" tabindex="2">
                        <?php
                        foreach(ploopi_documents_listfolders($_SESSION['documents'][$_GET['documents_id']]['id_object'], $_SESSION['documents'][$_GET['documents_id']]['id_record'], $_SESSION['documents'][$_GET['documents_id']]['id_module']) as $row) {
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $documentsfile->fields['id_folder']) echo 'selected="selected"'; ?>><?php echo ploopi_htmlentities($row['name']); ?></option>
                            <?php
                        }
                        ?>
                        </select>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <label>Libell�:</label>
                    <input class="text" name="documentsfile_label" value="<?php echo ploopi_htmlentities($documentsfile->fields['label']); ?>" tabindex="3" style="width:250px;">
                </p>
                <p>
                    <label>R�f�rence:</label>
                    <input class="text" name="documentsfile_ref" value="<?php echo ploopi_htmlentities($documentsfile->fields['ref']); ?>" tabindex="4" style="width:250px;">
                </p>
                <p>
                    <label>Date:</label>
                    <input class="text" id="documentsfile_timestp_file" name="documentsfile_timestp_file" value="<?php echo ploopi_htmlentities($ldate['date']); ?>" readonly style="width:75px;" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);" tabindex="5">
                    <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                </p>
                <p>
                    <label>Description:</label>
                    <span>
                    <?php
                    include_once './lib/fckeditor/fckeditor.php' ;

                    $oFCKeditor = new FCKeditor('fck_documentsfile_description') ;

                    $oFCKeditor->BasePath = './lib/fckeditor/';

                    // default value
                    $oFCKeditor->Value = $documentsfile->fields['description'];

                    // width & height
                    $oFCKeditor->Width='100%';
                    $oFCKeditor->Height='150';

                    $oFCKeditor->Config['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/js/documents/fckconfig.js';
                    $oFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH.'/';

                    // render
                    $oFCKeditor->Create('FCKeditor_DocFileDesc') ;
                    ?>
                    </span>
                </p>
            </div>
            <div class="documents_formcontent" style="text-align:right;padding:4px;">
                <input type="button" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('ploopi_documents_openfile_popup');">
                <input type="submit" class="flatbutton" style="width:100px;" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="7">
            </div>
        </div>
        </form>
        <iframe name="documents_fileform_iframe" src="./img/blank.gif" style="display:none;"></iframe>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        echo $skin->create_popup($title, $content, 'ploopi_documents_openfile_popup');
        ploopi_die();
    break;

    case 'documents_deletefile':
        // V�rification de l'id de l'instance
        if (!isset($_REQUEST['documents_id'])) return;

        if (!isset($_GET['currentfolder'])) return;

        include_once './include/classes/documents.php';

        $documentsfile = new documentsfile();

        if (!empty($_GET['documentsfile_id']) && $documentsfile->openmd5($_GET['documentsfile_id']))
        {
            $documentsfile->delete();

            if (!empty($_SESSION['documents'][$_REQUEST['documents_id']]['callback_inc'])) include $_SESSION['documents'][$_REQUEST['documents_id']]['callback_inc'];
            if (!empty($_SESSION['documents'][$_REQUEST['documents_id']]['callback_func'])) $_SESSION['documents'][$_REQUEST['documents_id']]['callback_func']('deletefile', $documentsfile, true);
        }

        ploopi_redirect("admin.php?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}&documents_id={$_REQUEST['documents_id']}");
    break;

    case 'documents_deletefolder':
        // V�rification de l'id de l'instance
        if (!isset($_REQUEST['documents_id'])) return;

        if (!isset($_GET['currentfolder'])) return;

        include_once './include/classes/documents.php';

        $documentsfolder = new documentsfolder();

        if (!empty($_GET['documentsfolder_id']) && $documentsfolder->openmd5($_GET['documentsfolder_id']))
        {
            $documentsfolder->delete();

            if (!empty($_SESSION['documents'][$_REQUEST['documents_id']]['callback_inc'])) include_once $_SESSION['documents'][$_REQUEST['documents_id']]['callback_inc'];
            if (!empty($_SESSION['documents'][$_REQUEST['documents_id']]['callback_func'])) $_SESSION['documents'][$_REQUEST['documents_id']]['callback_func']('deletefolder', $documentsfolder, true);
        }
        ploopi_redirect("admin.php?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}&documents_id={$_REQUEST['documents_id']}");
    break;
}
