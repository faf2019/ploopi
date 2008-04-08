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

switch($ploopi_op)
{
      /***********************/
     /** DOCUMENTS_BROWSER **/
    /***********************/

    case 'documents_selectfile':

        $_SESSION['documents']['id_object'] = $_GET['id_object'];
        $_SESSION['documents']['id_record'] = $_GET['id_record'];
        $_SESSION['documents']['id_user'] = $_SESSION['ploopi']['userid'];
        $_SESSION['documents']['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $_SESSION['documents']['id_module'] = $_SESSION['ploopi']['moduleid'];
        $_SESSION['documents']['documents_id'] = $_GET['documents_id'];
        $_SESSION['documents']['mode'] = 'selectfile';
        $_SESSION['documents']['destfield'] = $_GET['destfield'];

        ?>
        <div id="ploopidocuments_<? echo $_SESSION['documents']['documents_id']; ?>">
        <?
        
    case 'documents_browser':
        include_once './include/classes/class_documentsfolder.php';
        include_once './include/classes/class_documentsfile.php';

        if (isset($_REQUEST['currentfolder'])) $currentfolder = $_REQUEST['currentfolder'];
        if (isset($_REQUEST['mode'])) $_SESSION['documents']['mode'] = $_REQUEST['mode'];

        if (empty($currentfolder)) // on va chercher la racine
        {
            $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = 0 and id_object = '{$_SESSION['documents']['id_object']}' and id_record = '".$db->addslashes($_SESSION['documents']['id_record'])."'");

            if ($row = $db->fetchrow()) $currentfolder = $row['id'];
            else // racine inexistante, il faut la créer
            {
                $documentsfolder = new documentsfolder();
                $documentsfolder->fields['name'] = 'Racine';
                $documentsfolder->fields['id_folder'] = 0;
                $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
                $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
                $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
                $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
                $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
                $currentfolder = $documentsfolder->save();
            }
        }
        ?>

        <div class="documents_browser">

            <div class="documents_path">
                <?
                // voir pour une optimisation de cette partie car on ouvre un docfolder sans doute pour rien
                $documentsfolder = new documentsfolder();

                if (!empty($currentfolder)) $documentsfolder->open($currentfolder);
                ?>

                <a title="Rechercher un Fichier" href="javascript:void(0);" style="float:right;"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_search.png"></a>
                <?
                if (empty($_SESSION['documents']['mode']))
                {
                    if ($_SESSION['documents']['rights']['DOCUMENT_CREATE'])
                    {
                        ?><a title="Créer un nouveau fichier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfile('<? echo $currentfolder; ?>','',event);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfile.png"></a><?
                    }
                    if ($_SESSION['documents']['rights']['FOLDER_CREATE'])
                    {
                        ?>
                        <a title="Créer un nouveau Dossier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfolder('<? echo $currentfolder; ?>','',event);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfolder.png"></a>
                        <?
                    }
                }
                ?>
                <a title="Aller au Dossier Racine" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_browser('','<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>','',true);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_home.png"></a>

                <div>Emplacement :</div>
                <?
                if ($currentfolder != 0)
                {
                    $documentsfolder = new documentsfolder();
                    $documentsfolder->open($currentfolder);

                    $db->query("SELECT id, name, id_folder FROM ploopi_documents_folder WHERE id in ({$documentsfolder->fields['parents']},{$currentfolder}) ORDER by id");

                    while ($row = $db->fetchrow())
                    {
                        // change root name
                        $foldername = (!$row['id_folder']) ? $_SESSION['documents']['root_name'] : $row['name'];
                        ?>
                        <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:ploopi_documents_browser('<? echo $row['id']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>','',true);">
                            <p class="ploopi_va">
                                <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_folder.png" />
                                <span><? echo $foldername; ?></span>
                            </p>
                        </a>
                        <?
                    }
                }
                ?>
            </div>
            <?

            // initialisation  du tri par défaut pour le browser courant
            if (empty($_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'])) $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] = 'nom';
            if (empty($_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'])) $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] = 'ASC';

            // doit-on inverser le sens du tri ? (si l'orderby demandé et le meme que celui stocké en session)
            $invertsort = (!empty($_GET['orderby']) && $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] == $_GET['orderby']);

            // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
            $orderby = (empty($_GET['orderby'])) ? $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] : $_GET['orderby'];

            // doit-on réinitialiser le sens du tri ?
            $resetsort = ($_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] != $orderby);

            $sort_option = '';

            if ($resetsort) $sort_option = 'ASC';
            else
            {
                if ($invertsort)
                {
                    if ($_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] == 'ASC') $sort_option = 'DESC';
                    else $sort_option = 'ASC';
                }
                else $sort_option = $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'];
            }

            $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] = $orderby;
            $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] = $sort_option;

            $sort_img = '';
            if (!empty($sort_option)) $sort_img = ($sort_option == 'DESC') ? '<img src="'.$_SESSION['ploopi']['template_path'].'/img/arrays/arrow_down.png">' : '<img src="'.$_SESSION['ploopi']['template_path'].'/img/arrays/arrow_up.png">';

            $documents_columns = array();


            $sort_column = ($orderby == 'nom') ? $sort_img : '';
            $documents_columns['auto'][1] = array(  'label' => '<span>Nom&nbsp;</span>'.$sort_column,
                                                    'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'nom',true);",
                                                    'style' => ($orderby == 'nom') ? 'background-color:#e0e0e0;' : ''
                                                    );

            if (empty($_SESSION['documents']['fields']) || in_array('type', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'type') ? $sort_img : '';
                $documents_columns['right'][3] = array( 'label' => '<span>Type&nbsp;</span>'.$sort_column,
                                                        'width' => '65',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'type',true);",
                                                        'style' => ($orderby == 'type') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['fields']) || in_array('timestp_modify', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'date_modif') ? $sort_img : '';
                $documents_columns['right'][4] = array( 'label' => '<span>Date Modif&nbsp;</span>'.$sort_column,
                                                        'width' => '130',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'date_modif',true);",
                                                        'style' => ($orderby == 'date_modif') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['fields']) || in_array('timestp_file', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'date') ? $sort_img : '';
                $documents_columns['right'][5] = array( 'label' => '<span>Date&nbsp;</span>'.$sort_column,
                                                        'width' => '80',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'date',true);",
                                                        'style' => ($orderby == 'date') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['fields']) || in_array('ref', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'ref') ? $sort_img : '';
                $documents_columns['right'][6] = array( 'label' => '<span>Ref&nbsp;</span>'.$sort_column,
                                                        'width' => '75',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'ref',true);",
                                                        'style' => ($orderby == 'ref') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['fields']) || in_array('label', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'libelle') ? $sort_img : '';
                $documents_columns['right'][7] = array( 'label' => '<span>Libellé&nbsp;</span>'.$sort_column,
                                                        'width' => '110',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'libelle',true);",
                                                        'style' => ($orderby == 'libelle') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['fields']) || in_array('size', $_SESSION['documents']['fields']))
            {
                $sort_column = ($orderby == 'taille') ? $sort_img : '';
                $documents_columns['right'][8] = array( 'label' => '<span>Taille&nbsp;</span>'.$sort_column,
                                                        'width' => '90',
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'taille',true);",
                                                        'style' => ($orderby == 'taille') ? 'background-color:#e0e0e0;' : ''
                                                        );
            }

            if (empty($_SESSION['documents']['mode'])) $documents_columns['actions_right'][9] = array('label' => 'Actions', 'width' => '85');

            // DISPLAY FOLDERS
            $documents_folder_values = array();

            $orderby_option = '';

            if (!empty($orderby))
            {
                switch($orderby)
                {
                    case 'date_modify':
                        $orderby_option = 'f.timestp_modify';
                    break;

                    case 'taille':
                        $orderby_option = 'f.nbelements';
                    break;

                    case 'libelle':
                        $orderby_option = 'f.description';
                    break;

                    default:
                    case 'nom':
                        $orderby_option = 'f.name';
                    break;
                }
            }

            $orderby_option = "ORDER BY {$orderby_option} {$sort_option}";

            $sql =  "
                    SELECT      f.*,
                                u.login
                    FROM        ploopi_documents_folder f
                    LEFT JOIN   ploopi_user u
                    ON          f.id_user = u.id
                    WHERE       f.id_folder = {$currentfolder}

                    {$orderby_option}
                    ";

            $db->query($sql);

            $i = 0;
            while ($row = $db->fetchrow())
            {
                $ldate = ploopi_timestamp2local($row['timestp_modify']);

                $documents_folder_values[$i]['values'][1] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/ico_folder.png\" /><span>&nbsp;{$row['name']}</span>", 'style' => '');
                $documents_folder_values[$i]['values'][3] = array('label' => 'Dossier', 'style' => '');
                $documents_folder_values[$i]['values'][4] = array('label' => "{$ldate['date']} {$ldate['time']}", 'style' => '');
                $documents_folder_values[$i]['values'][5] = array('label' => '&nbsp;', 'style' => '');
                $documents_folder_values[$i]['values'][6] = array('label' => '&nbsp;', 'style' => '');
                $documents_folder_values[$i]['values'][7] = array('label' => '&nbsp;', 'style' => '');
                $documents_folder_values[$i]['values'][8] = array('label' => "{$row['nbelements']} element(s)", 'style' => '');

                $actions = '';
                if ($_SESSION['documents']['rights']['DOCUMENT_MODIFY']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le dossier et son contenu\')) ploopi_documents_deletefolder(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
                if ($_SESSION['documents']['rights']['DOCUMENT_DELETE']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfolder(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

                if ($actions == '') $actions = '&nbsp;';

                if (empty($_SESSION['documents']['mode'])) $documents_folder_values[$i]['values'][9] = array('label' => $actions, 'style' => '');

                $documents_folder_values[$i]['description'] = '';
                $documents_folder_values[$i]['link'] = 'javascript:void(0);';
                $documents_folder_values[$i]['option'] = 'onclick="javascript:ploopi_documents_browser(\''.$row['id'].'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$_SESSION['documents']['mode'].'\',\'\',true);"';
                $documents_folder_values[$i]['style'] = '';

                $i++;
            }

            // DISPLAY FILES
            $documents_file_values = array();

            $orderby_option = '';

            if (!empty($orderby))
            {
                switch($orderby)
                {
                    case 'date_modif':
                        $orderby_option = 'f.timestp_modify';
                    break;

                    case 'ref':
                        $orderby_option = 'f.ref';
                    break;

                    case 'nom':
                        $orderby_option = 'f.name';
                    break;

                    case 'libelle':
                        $orderby_option = 'f.label';
                    break;

                    case 'type':
                        $orderby_option = 'f.name';
                    break;

                    case 'date':
                        $orderby_option = 'f.timestp_file';
                    break;

                    case 'taille':
                        $orderby_option = 'f.size';
                    break;
                }
            }

            $orderby_option = "ORDER BY {$orderby_option} {$sort_option}";

            $sql =  "
                    SELECT      f.*,
                                u.login,
                                e.filetype

                    FROM        ploopi_documents_file f

                    LEFT JOIN   ploopi_user u
                    ON          f.id_user = u.id

                    LEFT JOIN   ploopi_documents_ext e
                    ON          e.ext = f.extension

                    WHERE       f.id_folder = {$currentfolder}

                    {$orderby_option}
                    ";

            $db->query($sql);

            while ($row = $db->fetchrow())
            {
                $ksize = sprintf("%.02f",$row['size']/1024);
                $ldate = ploopi_timestamp2local($row['timestp_modify']);

                $ldate_file = ($row['timestp_file'] != 0) ? ploopi_timestamp2local($row['timestp_file']) : array('date' => '');

                $ico = (file_exists("{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

                $actions = '';

                if ($_SESSION['documents']['rights']['FOLDER_MODIFY']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le fichier\')) ploopi_documents_deletefile(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
                if ($_SESSION['documents']['rights']['FOLDER_DELETE']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfile(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

                $documents_file_values[$i]['values'][1] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/{$ico}\" /><span>&nbsp;{$row['name']}</span>", 'style' => '');
                $documents_file_values[$i]['values'][3] = array('label' => 'Fichier', 'style' => '');
                $documents_file_values[$i]['values'][4] = array('label' => "{$ldate['date']} {$ldate['time']}", 'style' => '');
                $documents_file_values[$i]['values'][5] = array('label' => $ldate_file['date'], 'style' => '');
                $documents_file_values[$i]['values'][6] = array('label' => $row['ref'], 'style' => '');
                $documents_file_values[$i]['values'][7] = array('label' => $row['label'], 'style' => '');
                $documents_file_values[$i]['values'][8] = array('label' => "{$ksize} ko", 'style' => '');
                $documents_file_values[$i]['values'][9] = array('label' => $actions.'<a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("{$scriptenv}?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download.png" /></a>
                                                                                    <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("{$scriptenv}?ploopi_op=documents_downloadfile_zip&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download_zip.png" /></a>
                                                                                    ', 'style' => '');
                $documents_file_values[$i]['description'] = '';
                if ($_SESSION['documents']['mode'] == 'selectfile')
                {
                    $documents_file_values[$i]['link'] = 'javascript:void(0);';
                    $documents_file_values[$i]['onclick'] = "javascript:ploopi_getelem('{$_SESSION['documents']['destfield']}').value='{$row['name']}';ploopi_getelem('{$_SESSION['documents']['destfield']}_id').value='{$row['id']}';ploopi_hidepopup('ploopi_documents_popup');";
                }
                else $documents_file_values[$i]['link'] = ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}&attachement=".$_SESSION['documents']['attachement']);


                $documents_file_values[$i]['style'] = '';

                $i++;
            }


            if ($sort_option == 'ASC') $skin->display_array($documents_columns, array_merge($documents_folder_values, $documents_file_values));
            else $skin->display_array($documents_columns, array_merge($documents_file_values, $documents_folder_values));
            ?>
        </div>
        <?
        
        if ($ploopi_op == 'documents_selectfile')
        {
            ?>
            </div>
            <?
            $content = ob_get_contents();
            ob_end_clean();
        
            echo $skin->create_popup('Explorateur de fichiers', $content, 'ploopi_documents_popup');
        }
        
        ploopi_die();
    break;

    case 'documents_popup':
        //include_once './include/functions/documents.php';
        //ploopi_documents($_GET['id_object'], $_GET['id_record']);
        ?>
        <div id="ploopidocuments_<? echo ploopi_documents_getid($_GET['id_object'], $_GET['id_record']); ?>"></div>
        <?
        ploopi_die();
    break;

    case 'documents_downloadfile':
        if (!empty($_GET['documentsfile_id']))
        {
            include_once './include/classes/class_documentsfile.php';

            $documentsfile = new documentsfile();
            $documentsfile->open($_GET['documentsfile_id']);

            $attachement = true;

            if (isset($_GET['attachement']) && ($_GET['attachement'] == 0 || $_GET['attachement'] == 'false')) $attachement = false;

            if (file_exists($documentsfile->getfilepath())) ploopi_downloadfile($documentsfile->getfilepath(),$documentsfile->fields['name'], false, $attachement);
        }
        ploopi_die();
    break;

    case 'documents_downloadfile_zip':
        $zip_path = ploopi_documents_getpath()._PLOOPI_SEP.'zip';
        if (!is_dir($zip_path)) mkdir($zip_path);

        if (!empty($_GET['documentsfile_id']))
        {
            include_once './lib/pclzip/pclzip.lib.php';
            include_once './include/classes/class_documentsfile.php';

            $documentsfile = new documentsfile();
            $documentsfile->open($_GET['documentsfile_id']);

            if (file_exists($documentsfile->getfilepath()) && is_writeable($zip_path))
            {
                // create a temporary file with the real name
                $tmpfilename = $zip_path._PLOOPI_SEP.$documentsfile->fields['name'];

                copy($documentsfile->getfilepath(),$tmpfilename);

                // create zip file
                $zip_filename = "archive_{$_GET['documentsfile_id']}.zip";
                echo $zip_filepath = $zip_path._PLOOPI_SEP.$zip_filename;
                $zip = new PclZip($zip_filepath);
                $zip->create($tmpfilename,PCLZIP_OPT_REMOVE_ALL_PATH);

                // delete temporary file
                unlink($tmpfilename);

                // download zip file
                ploopi_downloadfile($zip_filepath, $zip_filename, true);
            }
        }

        ploopi_die();
    break;

    case 'documents_savefolder':
        include_once './include/classes/class_documentsfolder.php';
        $documentsfolder = new documentsfolder();

        if (!empty($_POST['documentsfolder_id']))
        {
            $documentsfolder->open($_POST['documentsfolder_id']);
            $documentsfolder->setvalues($_POST,'documentsfolder_');
            $documentsfolder->save();
        }
        else // new folder
        {
            $documentsfolder->setvalues($_POST,'documentsfolder_');
            $documentsfolder->fields['id_folder'] = $_POST['currentfolder'];
            $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
            $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
            $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
            $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
            $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
            $documentsfolder->save();
        }
        ?>
        <script type="text/javascript">
            window.parent.ploopi_documents_browser('<? echo $_POST['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>')
            window.parent.ploopi_hidepopup('ploopi_documents_openfolder_popup');
        </script>
        <?
        ploopi_die();
    break;

    case 'documents_openfolder':
        include_once './include/classes/class_documentsfolder.php';
        $documentsfolder = new documentsfolder();

        if (empty($_GET['documentsfolder_id']))
        {
            $documentsfolder->init_description();
            $title = "Nouveau Dossier";
        }
        else
        {
            $documentsfolder->open($_GET['documentsfolder_id']);
            $title = "Modification du Dossier";
        }
        ?>
        <form id="documents_folderform" action="admin-light.php" method="post" target="documents_folderform_iframe" enctype="multipart/form-data">
        <input type="hidden" name="ploopi_op" value="documents_savefolder">
        <input type="hidden" name="currentfolder" value="<? echo $_GET['currentfolder']; ?>">
        <?
        if (!empty($_GET['documentsfolder_id']))
        {
            ?>
            <input type="hidden" name="documentsfolder_id" value="<? echo $_GET['documentsfolder_id']; ?>">
            <?
        }
        ?>

        <div class="ploopi_form">
            <div class="documents_formcontent">
                <p>
                    <label>Libellé:</label>
                    <input type="text" class="text" name="documentsfolder_name" value="<? echo htmlentities($documentsfolder->fields['name']); ?>">
                </p>
                <p>
                    <label>Commentaire:</label>
                    <textarea class="text" name="documentsfolder_description"><? echo htmlentities($documentsfolder->fields['description']); ?></textarea>
                </p>
            </div>
            <div class="documents_formcontent" style="text-align:right;padding:4px;">
                <input type="button" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('ploopi_documents_openfolder_popup');">
                <input type="submit" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_SAVE; ?>">
                <!-- onclick="javascript:ploopi_hidepopup();ploopi_documents_browser('<? echo $_GET['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>')" -->
            </div>
        </div>
        </form>
        <iframe name="documents_folderform_iframe" src="./img/blank.gif" style="width:0;height:0;visibility:hidden;display:none;"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();
    
        echo $skin->create_popup($title, $content, 'ploopi_documents_openfolder_popup');
        ploopi_die();
    break;

    case 'documents_savefile':
        include_once './include/classes/class_documentsfile.php';
        $documentsfile = new documentsfile();

        if (!empty($_POST['documentsfile_id'])) $documentsfile->open($_POST['documentsfile_id']);
        else
        {
            $documentsfile->fields['id_object'] = $_SESSION['documents']['id_object'];
            $documentsfile->fields['id_record'] = $_SESSION['documents']['id_record'];
            $documentsfile->fields['id_module'] = $_SESSION['documents']['id_module'];
            $documentsfile->fields['id_user'] = $_SESSION['documents']['id_user'];
            $documentsfile->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
        }

        $documentsfile->setvalues($_POST,'documentsfile_');
        $documentsfile->fields['timestp_file'] = ploopi_local2timestamp($documentsfile->fields['timestp_file']);
        $documentsfile->fields['id_folder'] = $_POST['currentfolder'];

        if (!empty($_FILES['documentsfile_file']['name']))
        {
            $documentsfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
            $documentsfile->tmpfile = $_FILES['documentsfile_file']['tmp_name'];
            $documentsfile->fields['name'] = $_FILES['documentsfile_file']['name'];
            $documentsfile->fields['size'] = $_FILES['documentsfile_file']['size'];
        }

        $error = $documentsfile->save();
        ?>
        <script type="text/javascript">
            window.parent.ploopi_documents_browser('<? echo $_POST['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>')
            window.parent.ploopi_hidepopup('ploopi_documents_openfile_popup');
        </script>
        <?
        ploopi_die();
    break;

    case 'documents_openfile':
        include_once './include/classes/class_documentsfile.php';
        $documentsfile = new documentsfile();

        if (empty($_GET['documentsfile_id']))
        {
            $documentsfile->init_description();
            $title = "Nouveau Fichier";
        }
        else
        {
            $documentsfile->open($_GET['documentsfile_id']);
            $title = "Modification du Fichier";
        }

        $ldate = ($documentsfile->fields['timestp_file']!=0 && $documentsfile->fields['timestp_file']!='') ? ploopi_timestamp2local($documentsfile->fields['timestp_file']) : array('date' => '');
        ?>
        <form id="documents_folderform" action="admin-light.php" method="post" target="documents_fileform_iframe" enctype="multipart/form-data" onsubmit="javascript:return ploopi_documents_validate(this)">
        <input type="hidden" name="ploopi_op" value="documents_savefile">
        <input type="hidden" name="currentfolder" value="<? echo $_GET['currentfolder']; ?>">
        <?
        if (!empty($_GET['documentsfile_id']))
        {
            ?>
            <input type="hidden" name="documentsfile_id" value="<? echo $_GET['documentsfile_id']; ?>">
            <?
        }
        ?>
        <div class="ploopi_form">
            <div class="documents_formcontent">
                <?
                if (empty($_GET['documentsfile_id']))
                {
                    ?>
                    <p>
                        <label>Fichier:</label>
                        <input type="file" class="text" name="documentsfile_file" tabindex="1">
                    </p>
                    <?
                }
                else
                {
                    ?>
                    <p>
                        <label>Nom du Fichier:</label>
                        <input type="input" class="text" name="documentsfile_name" value="<? echo htmlentities($documentsfile->fields['name']); ?>" tabindex="2">
                    </p>
                    <p>
                        <label>Nouveau Fichier:</label>
                        <input type="file" class="text" name="documentsfile_file" tabindex="2">
                    </p>
                    <?
                }
                ?>
                <p>
                    <label>Libellé:</label>
                    <input class="text" name="documentsfile_label" value="<? echo htmlentities($documentsfile->fields['label']); ?>" tabindex="3">
                </p>
                <p>
                    <label>Référence:</label>
                    <input class="text" name="documentsfile_ref" value="<? echo htmlentities($documentsfile->fields['ref']); ?>" tabindex="4">
                </p>
                <p>
                    <label>Date:</label>
                    <input class="text" id="documentsfile_timestp_file" name="documentsfile_timestp_file" value="<? echo $ldate['date']; ?>" readonly style="width:75px;" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);" tabindex="5">
                    <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                </p>
                <p>
                    <label>Mots Clés:</label>
                    <textarea class="text" name="documentsfile_description" tabindex="6"><? echo htmlentities($documentsfile->fields['description']); ?></textarea>
                </p>
            </div>
            <div class="documents_formcontent" style="text-align:right;padding:4px;">
                <input type="button" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('ploopi_documents_openfile_popup');">
                <input type="submit" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_SAVE; ?>" tabindex="7">
            </div>
        </div>
        </form>
        <iframe name="documents_fileform_iframe" src="./img/blank.gif" style="width:0;height:0;visibility:hidden;display:none;"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();
    
        echo $skin->create_popup($title, $content, 'ploopi_documents_openfile_popup');
        ploopi_die();
    break;

    case 'documents_deletefile':
        if (!empty($_GET['documentsfile_id']))
        {
            include_once './include/classes/class_documentsfile.php';

            $documentsfile = new documentsfile();
            $documentsfile->open($_GET['documentsfile_id']);

            $documentsfile->delete();
        }

        ploopi_redirect("{$scriptenv}?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}");
    break;

    case 'documents_deletefolder':
        if (!empty($_GET['documentsfolder_id']))
        {
            include_once './include/classes/class_documentsfolder.php';

            $documentsfolder = new documentsfolder();
            $documentsfolder->open($_GET['documentsfolder_id']);

            $documentsfolder->delete();
        }
        ploopi_redirect("{$scriptenv}?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}");
    break;
}
?>