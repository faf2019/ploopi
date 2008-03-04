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

if ($_SESSION['ploopi']['connected'])
{
    ploopi_init_module('doc');

    include_once './modules/doc/class_docfile.php';
    include_once './modules/doc/class_docfolder.php';
    include_once './modules/doc/class_docfiledraft.php';

    $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

    $currentfolder = (isset($_REQUEST['currentfolder'])) ? $_REQUEST['currentfolder'] : 0;

    switch($op)
    {
        case 'doc_filedownloadzip':
            include_once './lib/pclzip/pclzip.lib.php';

            $zip_path = doc_getpath()._PLOOPI_SEP.'zip';
            if (!is_dir($zip_path)) mkdir($zip_path);

            if (!empty($_GET['docfile_md5id']))
            {
                $docfile = new docfile();
                $docfile->openmd5($_GET['docfile_md5id']);

                if (file_exists($docfile->getfilepath()) && is_writeable($zip_path))
                {
                    // create a temporary file with the real name
                    $tmpfilename = $zip_path._PLOOPI_SEP.$docfile->fields['name'];

                    copy($docfile->getfilepath(),$tmpfilename);
                    // create zip file
                    $zip_filename = "archive_{$_GET['docfile_md5id']}.zip";
                    $zip_filepath = $zip_path._PLOOPI_SEP.$zip_filename;
                    $zip = new PclZip($zip_filepath);
                    $zip->create($tmpfilename,PCLZIP_OPT_REMOVE_ALL_PATH);

                    // delete temporary file
                    unlink($tmpfilename);

                    // download zip file
                    ploopi_downloadfile($zip_filepath, $zip_filename, true);
                }
            }

            if (!empty($_GET['docfiledraft_md5id']))
            {
                $docfiledraft = new docfiledraft();
                $docfiledraft->openmd5($_GET['docfiledraft_md5id']);

                if (file_exists($docfiledraft->getfilepath()) && is_writeable($zip_path))
                {
                    // create a temporary file with the real name
                    $tmpfilename = $zip_path._PLOOPI_SEP.$docfiledraft->fields['name'];
                    copy($docfiledraft->getfilepath(),$tmpfilename);

                    // create zip file
                    $zip_filename = "archive_draft_{$_GET['docfiledraft_md5id']}.zip";
                    echo $zip_filepath = $zip_path._PLOOPI_SEP.$zip_filename;
                    $zip = new PclZip($zip_filepath);
                    $zip->create($tmpfilename,PCLZIP_OPT_REMOVE_ALL_PATH);

                    // delete temporary file
                    unlink($tmpfilename);

                    // download zip file
                    ploopi_downloadfile($zip_filepath, $zip_filename, true);
                }
            }
        break;

        case 'doc_filedownload':
        case 'doc_fileview':
            if (!empty($_GET['docfile_md5id']))
            {
                $docfile = new docfile();
                $docfile->openmd5($_GET['docfile_md5id']);

                if (!empty($_GET['version']))
                {
                    include_once './modules/doc/class_docfilehistory.php';
                    $docfilehistory = new docfilehistory();
                    $docfilehistory->open($docfile->fields['id'], $_GET['version']);
                    if (file_exists($docfilehistory->getfilepath())) ploopi_downloadfile($docfilehistory->getfilepath(), $docfilehistory->fields['name'], false, ($op != 'doc_fileview'));
                }
                else
                {
                    if (file_exists($docfile->getfilepath())) ploopi_downloadfile($docfile->getfilepath(), $docfile->fields['name'], false, ($op != 'doc_fileview'));
                    else if (file_exists($docfile->getfilepath_deprecated())) ploopi_downloadfile($docfile->getfilepath_deprecated(), $docfile->fields['name'], false, ($op != 'doc_fileview'));
                }
            }

            if (!empty($_GET['docfiledraft_md5id']))
            {
                $docfiledraft = new docfiledraft();
                $docfiledraft->openmd5($_GET['docfiledraft_md5id']);
                if (file_exists($docfiledraft->getfilepath())) ploopi_downloadfile($docfiledraft->getfilepath(),$docfiledraft->fields['name']);
            }
        break;

        case 'doc_filedraftdelete':
            if (!empty($_GET['docfiledraft_md5id']))
            {
                $docfiledraft = new docfiledraft();
                $docfiledraft->openmd5($_GET['docfiledraft_md5id']);
                $error = $docfiledraft->delete();
                ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder=$currentfolder&error=$error");
            }
        break;

        case 'doc_filedelete':
            ploopi_init_module('doc');

            include_once './modules/doc/class_docfolder.php';
            include_once './modules/doc/class_docfile.php';

            if (!empty($_GET['docfile_md5id']))
            {
                $docfile = new docfile();
                $docfile->openmd5($_GET['docfile_md5id']);

                // on vérifie que l'utilisateur a bien le droit de supprimer ce fichier (en fonction du statut du dossier parent)
                $docfolder_readonly_content = false;

                $docfolder = new docfolder();

                if (!empty($docfile->fields['id_folder']))
                {
                    $docfolder->open($docfile->fields['id_folder']);
                    $docfolder_readonly_content = ($docfolder->fields['readonly_content'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']);
                }

                if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFILE) && (!$docfolder_readonly_content || $docfile->fields['id_user'] == $_SESSION['ploopi']['userid'])))
                {
                    $error = $docfile->delete();
                    
                    if (!empty($docfile->fields['id_folder']) && $docfolder->fields['foldertype'] != 'private') 
                    {
                        // on n'est ni à la racine (pas d'abonnement sur la racine)
                        // ni sur un dossier privé
                        /* DEBUT ABONNEMENT */
            
                        // on construit la liste des objets parents (y compris l'objet courant)
                        $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");

                        // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                        $arrUsers = array();
                        foreach ($arrFolderList as $intObjectId)
                            $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_MODIFYFILE, _DOC_ACTION_DELETEFILE));
                        
                        // on envoie le ticket de notification d'action sur l'objet
                        ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_DELETEFILE, $docfile->fields['name'], array_keys($arrUsers), 'Cet objet à été supprimé');
                        
                        /* FIN ABONNEMENT */
                    }
                 
                    
                    ploopi_create_user_action_log(_DOC_ACTION_DELETEFILE, $docfile->fields['id']);
                    ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder=$currentfolder&error=$error");
                }
            }

            ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder=$currentfolder");
        break;

        case 'doc_fileindex':
            if (!empty($_GET['docfile_md5id']))
            {
                $docfile = new docfile();
                $docfile->openmd5($_GET['docfile_md5id']);
                $docfile->parse();
                $docfile->save();
            }
            ploopi_redirect("{$scriptenv}?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$_GET['docfile_md5id']}");
        break;

        case 'doc_filesave':

            $draft = false;

            if (isset($currentfolder))
            {

                // en mode CGI, il faut récupérer les infos des fichiers uploadés (via le fichier lock)
                // cf class Cupload
                // on écrit tout dans $_FILES pour retomber sur nos pieds dans la suite des traitements
                if (_PLOOPI_USE_CGIUPLOAD && !empty($_GET['sid']))
                {
                    define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
                    include './lib/cupload/Cupload.class.php';

                    $_sId = $_GET['sid'];
                    $uploader = & new CUploadSentinel;
                    $uploader->__init($_sId);

                    if (!empty($uploader->files))
                    {
                        foreach($uploader->files as $key => $file)
                        {
                            $_FILES[$file['name']] = array( 'name'      =>  $file['filename'],
                                                            'type'      =>  $file['mime'],
                                                            'tmp_name'  =>  UPLOAD_PATH.$file['tmpname'],
                                                            'error'     =>  0,
                                                            'size'      =>  $file['size']
                                                        );
                        }
                    }

                    $uploader->clear();
                    //@unlink($_lock_file);
                }

                // on se base sur le currentfolder pour connaitre le statut du futur fichier (draft/normal)
                $docfolder = new docfolder();
                $docfolder->open($currentfolder);

                // on recherche s'il existe des validateurs pour ce dossier
                $wfusers = array();
                foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_workflow'];

                // on crée des documents "draft" s'il existe des validateurs et que l'utilisateur courant n'en fait pas partie
                $draft = (!empty($wfusers) && !in_array($_SESSION['ploopi']['userid'],$wfusers));

                // nouveau fichier ?
                $newfile = (empty($_REQUEST['docfile_md5id']));

                if ($newfile)
                {
                    for ($i=0;$i<=5;$i++)
                    {
                        if (!empty($_FILES["docfile_file_{$i}"]))
                        {
                            $file = $_FILES["docfile_file_{$i}"];
                            if (!$file['error'])
                            {
                                $docfile = ($draft) ? new docfiledraft() : new docfile();
                                $docfile->setugm();

                                $docfile->fields['description'] = (empty($_REQUEST["docfile_description_{$i}"])) ? '' : $_REQUEST["docfile_description_{$i}"];
                                $docfile->fields['readonly'] = (empty($_REQUEST["docfile_readonly_{$i}"])) ? 0 : 1;

                                $docfile->fields['id_folder'] = $currentfolder;
                                $docfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
                                $docfile->tmpfile = $file['tmp_name'];
                                $docfile->fields['name'] = $file['name'];
                                $docfile->fields['size'] = $file['size'];

                                $error = $docfile->save();

                                if ($draft)
                                {
                                    $_SESSION['ploopi']['tickets']['users_selected'] = $wfusers;
                                    ploopi_tickets_send("Demande de validation du document <strong>\"{$docfile->fields['name']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})", "Ceci est un message automatique envoyé suite à une demande de validation du document \"{$docfile->fields['name']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez accéder à ce document pour le valider en cliquant sur le lien ci-dessous.", true, 0, _DOC_OBJECT_FILEDRAFT, $currentfolder, $docfile->fields['name']);
                                }

                                if (!$error) 
                                {
                                    if (!$draft)
                                    {
                                        $docfolder = new docfolder();
                                        $docfolder->open($currentfolder);
                                        
                                        /* DEBUT ABONNEMENT */
                            
                                        // on construit la liste des objets parents (y compris l'objet courant)
                                        $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");
                            
                                        // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                                        $arrUsers = array();
                                        foreach ($arrFolderList as $intObjectId)
                                            $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_ADDFILE, _DOC_ACTION_MODIFYFILE));
                                        
                                        // on envoie le ticket de notification d'action sur l'objet
                                        ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_ADDFILE, $docfile->fields['name'], array_keys($arrUsers), 'Cet objet à été créé');
                                        
                                        /* FIN ABONNEMENT */                                        
                                    }
                                    ploopi_create_user_action_log(_DOC_ACTION_ADDFILE, $docfile->fields['id']);
                                }
                            }
                        }
                    }
                    ?>
                    <script type="text/javascript">
                        window.parent.doc_browser_from_iframe(<? echo $currentfolder; ?>);
                    </script>
                    <?
                    ploopi_die();
                }
                else // mise à jour d'un (unique) fichier
                {
                    $docfile = new docfile();
                    if (!$docfile->openmd5($_REQUEST['docfile_md5id'])) ploopi_redirect($scriptenv);
                    $docfile_id = $docfile->fields['id'];

                    if ($draft)
                    {
                        $docfile = new docfiledraft();
                        $docfile->setuwm();
                        $docfile->fields['id_docfile'] = $docfile_id;
                        $docfile->fields['id_folder'] = $currentfolder;
                    }
                    else
                    {
                        if (!empty($_FILES['docfile_file']['name'])) $docfile->createhistory();
                    }

                    $docfile->setvalues($_REQUEST,'docfile_');
                    if (empty($_REQUEST['docfile_readonly'])) $docfile->fields['readonly'] = 0;

                    if (!empty($_FILES['docfile_file']['name']))
                    {
                        $docfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
                        $docfile->tmpfile = $_FILES['docfile_file']['tmp_name'];
                        $docfile->fields['name'] = $_FILES['docfile_file']['name'];
                        $docfile->fields['size'] = $_FILES['docfile_file']['size'];
                    }

                    $error = $docfile->save();

                    if (!$error) 
                    {
                        if (!$draft)
                        {
                            /* DEBUT ABONNEMENT */
                
                            // on construit la liste des objets parents (y compris l'objet courant)
                            $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");
                
                            // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                            $arrUsers = array();
                            foreach ($arrFolderList as $intObjectId)
                                $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_MODIFYFILE));
                            
                            // on envoie le ticket de notification d'action sur l'objet
                            ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_MODIFYFILE, $docfile->fields['name'], array_keys($arrUsers), 'Cet objet à été créé');
                            
                            /* FIN ABONNEMENT */                                        
                        }
                        ploopi_create_user_action_log(_DOC_ACTION_MODIFYFILE, $docfile_id);
                    }

                    ?>
                    <script type="text/javascript">
                        window.parent.doc_browser_from_iframe(<? echo $currentfolder; ?>);
                    </script>
                    <?
                }

                ?>
                <script type="text/javascript">
                    window.parent.doc_browser_from_iframe(<? echo $_POST['currentfolder']; ?>);
                </script>
                <?
            }
        break;

        case 'doc_filepublish':
            if (!empty($_GET['docfiledraft_md5id']))
            {
                doc_getworkflow();

                if (in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']));
                {
                    $docfiledraft = new docfiledraft();
                    $docfiledraft->openmd5($_GET['docfiledraft_md5id']);
                    $docfiledraft->publish();
                }
            }
            ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder={$currentfolder}");
        break;

        case 'doc_folderpublish':
            if (!empty($_GET['docfolder_id']))
            {
                doc_getworkflow();

                if (in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']));
                {
                    $docfolder = new docfolder();
                    $docfolder->open($_GET['docfolder_id']);
                    $docfolder->publish();
                    
                    /* DEBUT ABONNEMENT */
        
                    // on construit la liste des objets parents (y compris l'objet courant)
                    $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");
        
                    // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                    $arrUsers = array();
                    foreach ($arrFolderList as $intObjectId)
                        $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_MODIFYFOLDER));
                    
                    // on envoie le ticket de notification d'action sur l'objet
                    ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_MODIFYFOLDER, $docfolder->fields['name'], array_keys($arrUsers), 'Cet objet à été publié');
                    
                    /* FIN ABONNEMENT */
                }
            }

            ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder={$currentfolder}");
        break;

        case 'doc_folderdelete':
            if (!empty($_GET['docfolder_id']))
            {
                $docfolder = new docfolder();
                $docfolder->open($_GET['docfolder_id']);

                $currentfolder = $docfolder->fields['id_folder'];

                // on vérifie que l'utilisateur a bien le droit de supprimer ce dossier (en fonction du statut du dossier et du dossier parent)
                $docfolder_readonly_content = false;

                if (!empty($docfolder->fields['id_folder']))
                {
                    $docfolder_parent = new docfolder();
                    $docfolder_parent->open($docfolder->fields['id_folder']);
                    $docfolder_readonly_content = ($docfolder_parent->fields['readonly_content'] && $docfolder_parent->fields['id_user'] != $_SESSION['ploopi']['userid']);
                }

                $readonly = (($docfolder->fields['readonly'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']) || $docfolder_readonly_content);
                if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_DELETEFOLDER) && (!$readonly) && ($docfolder->fields['nbelements'] == 0)))
                {
                    /* DEBUT ABONNEMENT */
        
                    // on construit la liste des objets parents (y compris l'objet courant)
                    $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");
    
                    // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                    $arrUsers = array();
                    foreach ($arrFolderList as $intObjectId)
                        $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_DELETEFOLDER, _DOC_ACTION_MODIFYFOLDER));
                    
                    // on envoie le ticket de notification d'action sur l'objet
                    ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_DELETEFOLDER, $docfolder->fields['name'], array_keys($arrUsers), 'Cet objet à été supprimé');
                    
                    /* FIN ABONNEMENT */

                
                    $docfolder->delete();
                    ploopi_create_user_action_log(_DOC_ACTION_DELETEFOLDER, $docfolder->fields['id']);
                }

                ploopi_redirect("{$scriptenv}?op=doc_explorer&currentfolder={$currentfolder}");
            }
        break;

        case 'doc_foldersave':
            $docfolder = new docfolder();

            if (!empty($_POST['docfolder_id']) && is_numeric($_POST['docfolder_id']))
            {
                $docfolder->open($_POST['docfolder_id']);
                $docfolder->setvalues($_POST,'docfolder_');
                if (empty($_POST['docfolder_readonly'])) $docfolder->fields['readonly'] = 0;
                if (empty($_POST['docfolder_readonly_content'])) $docfolder->fields['readonly_content'] = 0;
                $docfolder->save();
                
                /* DEBUT ABONNEMENT */
    
                // on construit la liste des objets parents (y compris l'objet courant)
                $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");

                // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                $arrUsers = array();
                foreach ($arrFolderList as $intObjectId)
                    $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_MODIFYFOLDER));
                
                // on envoie le ticket de notification d'action sur l'objet
                ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_MODIFYFOLDER, $docfolder->fields['name'], array_keys($arrUsers), 'Cet objet à été modifié');
                
                /* FIN ABONNEMENT */

                // SHARES
                ploopi_shares_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id']);
                doc_resetshares();

                // WORKFLOW
                ploopi_workflow_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id']);
                doc_resetworkflow();
                
                // LOG
                ploopi_create_user_action_log(_DOC_ACTION_MODIFYFOLDER, $docfolder->fields['id']);
                ?>
                <script type="text/javascript">
                    //window.parent.doc_browser_from_iframe(<? echo $docfolder->fields['id']; ?>);
                </script>
                <?
            }
            else // new folder
            {
                $docfolder->setvalues($_POST,'docfolder_');
                if (empty($_POST['docfolder_readonly'])) $docfolder->fields['readonly'] = 0;
                if (empty($_POST['docfolder_readonly_content'])) $docfolder->fields['readonly_content'] = 0;

                $docfolder->fields['id_folder'] = $currentfolder;
                $docfolder->setuwm();

                // test if we should publish or not the folder
                $wfusers = array();
                foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_workflow'];
                if (!empty($wfusers) && !in_array($_SESSION['ploopi']['userid'],$wfusers))
                {
                    $docfolder->fields['published'] = 0;

                    $_SESSION['ploopi']['tickets']['users_selected'] = $wfusers;
                    ploopi_tickets_send("Demande de validation du dossier <strong>\"{$docfolder->fields['name']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})", "Ceci est un message automatique envoyé suite à une demande de validation du dossier \"{$docfolder->fields['name']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez accéder à ce dossier pour le valider en cliquant sur le lien ci-dessous.", true, 0, _DOC_OBJECT_FILEDRAFT, $currentfolder, $docfolder->fields['name']);
                }
                else
                {
                    $parentfolder = new docfolder();
                    if ($parentfolder->open($currentfolder))
                    {
                        if ($parentfolder->fields['waiting_validation']>0) $docfolder->fields['waiting_validation'] = $parentfolder->fields['waiting_validation'];
                        if (!$parentfolder->fields['published']) $docfolder->fields['waiting_validation'] = $parentfolder->fields['id'];
                    }
                }

                $currentfolder = $docfolder->save();
                
                /* DEBUT ABONNEMENT */
    
                // on construit la liste des objets parents (y compris l'objet courant)
                $arrFolderList = split(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");

                // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
                $arrUsers = array();
                foreach ($arrFolderList as $intObjectId)
                    $arrUsers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, array(_DOC_ACTION_ADDFOLDER, _DOC_ACTION_MODIFYFOLDER));
                
                // on envoie le ticket de notification d'action sur l'objet
                ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_ADDFOLDER, $docfolder->fields['name'], array_keys($arrUsers), 'Cet objet à été créé');
                
                /* FIN ABONNEMENT */
                
                
                ploopi_shares_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id']);
                doc_resetshares();

                ploopi_workflow_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id']);
                doc_resetworkflow();
                
                ploopi_create_user_action_log(_DOC_ACTION_ADDFOLDER, $docfolder->fields['id']);
                ?>
                <script type="text/javascript">
                    window.parent.doc_browser_from_iframe(<? echo $currentfolder; ?>);
                </script>
                <?
            }

        break;

        case 'doc_explorer':
            ploopi_init_module('doc');

            include_once './modules/doc/class_docfolder.php';

            $docfolder_readonly_content = false;

            if (!empty($currentfolder))
            {
                $docfolder = new docfolder();
                $docfolder->open($currentfolder);
                $docfolder_readonly_content = ($docfolder->fields['readonly_content'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']);
            }

            include_once './modules/doc/public_explorer.php';
            ploopi_die();
        break;

        case 'doc_fileform':
        case 'doc_folderform':
        case 'doc_foldermodify':
        case 'doc_browser':
        case 'doc_search':
            ploopi_init_module('doc');

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

                <a title="Rechercher un Fichier" href="javascript:void(0);" onclick="javascript:doc_search(<? echo $currentfolder; ?>);" style="float:right;"><img src="./modules/doc/img/ico_search.png"></a>

                <?
                if (ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_ADDFILE) && !$readonly))
                {
                    ?>
                    <a title="Créer un nouveau fichier" href="javascript:void(0);" onclick="javascript:doc_fileform(<? echo $currentfolder; ?>);" style="float:right;"><img src="./modules/doc/img/ico_newfile.png"></a>
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
                    <a title="Créer un nouveau Dossier" href="javascript:void(0);" onclick="javascript:doc_folderform(<? echo $currentfolder; ?>,1);" style="float:right;"><img src="./modules/doc/img/ico_newfolder.png"></a>
                    <?
                }
                else
                {
                    ?>
                    <a title="Créer un nouveau Dossier" href="javascript:void(0);" style="float:right;"><img src="./modules/doc/img/ico_newfolder_grey.png"></a>
                    <?
                }
                ?>
                <a title="Aller au Dossier Racine" href="javascript:void(0);" onclick="javascript:doc_browser();" style="float:right;"><img src="./modules/doc/img/ico_home.png"></a>

                <div>Emplacement :</div>
                <a <? if ($currentfolder == 0) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:doc_browser();">
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

                    doc_getshares();

                    $db->query("SELECT id, name, foldertype, readonly, id_user FROM ploopi_mod_doc_folder WHERE id in ({$docfolder->fields['parents']},{$currentfolder}) ORDER BY id");

                    while ($row = $db->fetchrow())
                    {
                        $allowed = false;

                        if ($row['id_user'] == $_SESSION['ploopi']['userid'] || $row['foldertype'] == 'public' || ($row['foldertype'] == 'shared' && in_array($row['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['shares']['folders']))) $allowed = true;

                        if ($allowed)
                        {
                            ?>
                            <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:doc_browser(<? echo $row['id']; ?>);">
                            <?
                        }
                        else
                        {
                            ?>
                            <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:alert('Ce dossier ne vous est pas autorisé');">
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
            ploopi_die();
        break;

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
            <div id="doc_browser"></div>
            <script type="text/javascript">
                <?
                if (!empty($_GET['docfile_md5id'])) // ouverture directe d'un fichier (lien externe au module)
                {
                    $docfile = new docfile();
                    if ($docfile->openmd5($_GET['docfile_md5id']))
                    {
                        $currentfolder = $docfile->fields['id_folder'];
                        ?>
                        ploopi_window_onload_stock(function () {doc_fileform(<? echo $currentfolder; ?>, '<? echo $_GET['docfile_md5id']; ?>');});
                        <?
                    }
                }
                else
                {
                    $objFolder = new docfolder();
                    if (empty($currentfolder) || !$objFolder->open($currentfolder) || !$objFolder->isEnabled()) $currentfolder = 0;
                    ?>
                    ploopi_window_onload_stock(function () {doc_browser(<? echo $currentfolder; ?>);});
                    <?
                }
            ?>
            </script>
            <?
            echo $skin->close_simplebloc();
        break;

    }
}
?>
