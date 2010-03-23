  <?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Opérations
 *
 * @package doc
 * @subpackage op
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Si l'utilisateur est connecté
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On vérifie qu'on est bien dans le module DOC.
     * Ces opérations ne peuvent être effectuées que depuis le module DOC.
     */
    if (ploopi_ismoduleallowed('doc'))
    {
        $currentfolder = (isset($_REQUEST['currentfolder'])) ? $_REQUEST['currentfolder'] : 0;

        switch($ploopi_op)
        {
            case 'doc_getstatus':
                /**
                 * @ignore UPLOAD_PATH
                 */
                if (substr(_PLOOPI_CGI_UPLOADTMP, -1, 1) != '/') define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
                else define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP);
                include_once './lib/cupload/status.php';
                ploopi_die();
            break;

            case 'doc_help':
                include_once './modules/doc/public_legend.php';
                ploopi_die();
            break;

            case 'doc_search_next':
                ploopi_init_module('doc');
                include_once './modules/doc/public_search_result.php';
                ploopi_die();
            break;
            
            case 'doc_folder_detail':
                if (isset($_GET['doc_folder_id']) && is_numeric($_GET['doc_folder_id']))
                {
                    ploopi_init_module('doc');
                    
                    // Récupération des dossiers visibles
                    $arrFolders = doc_getfolders();
                    
                    // Récupération de la structure du treeview
                    $arrTreeview = doc_gettreeview($arrFolders);
                    
                    echo $skin->display_treeview($arrTreeview['list'], $arrTreeview['tree'], $currentfolder, $_GET['doc_folder_id']); 
                }
                ploopi_die();
            break;            

            /**
             * Enregistrement d'un document (ou ensemble de document si ajout)
             */
            case 'doc_filesave':
                if (empty($_REQUEST['doc_mode'])) ploopi_redirect("admin.php?doc_fileform&currentfolder={$currentfolder}");

                include_once './modules/doc/class_docfile.php';
                include_once './modules/doc/class_docfolder.php';
                include_once './modules/doc/class_docfiledraft.php';

                ploopi_init_module('doc');

                $draft = false;

                if ($currentfolder != 0) // Autre dossier que "racine"
                {
                    $docfolder = new docfolder();
                    if (!$docfolder->open($currentfolder)) ploopi_redirect("admin.php?doc_browser&currentfolder={$currentfolder}");
                }
                // en mode CGI, il faut récupérer les infos des fichiers uploadés (via le fichier lock)
                // cf class Cupload
                // on écrit tout dans $_FILES pour retomber sur nos pieds dans la suite des traitements
                if ($_REQUEST['doc_mode'] == 'host' && _PLOOPI_USE_CGIUPLOAD && !empty($_POST['sid']))
                {
                    if (!empty($_GET['error']) && $_GET['error'] == 'notwritable')
                    {
                        //alert("Problème lors de l'envoi du fichier\nvérifiez le paramètrage du dossier temporaire d'upload");
                        ploopi_redirect("admin.php?doc_fileform&currentfolder={$currentfolder}&error=1");
                    }

                    define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
                    include './lib/cupload/Cupload.class.php';

                    $_sId = $_POST['sid'];
                    $uploader = & new CUploadSentinel;
                    $uploader->__init($_sId);

                    if (!empty($uploader->files))
                    {
                        foreach($uploader->files as $key => $file)
                        {
                            $_FILES[$file['name']] =
                                array(
                                    'name'      =>  $file['filename'],
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

                // on recherche s'il existe des validateurs pour ce dossier
                $arrWfUsers = array('group' => array(), 'user' => array());
                $arrWfUsersOnly = array(); // utilisateurs uniquement (groupes compris)
                
                $objUser = new user();
                $objUser->open($_SESSION['ploopi']['userid']);
                $arrGroups = $objUser->getgroups(true);
                
                $booWfVal = false;
                foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) 
                {
                    if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booWfVal = true;
                    if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booWfVal = true;
                    
                    $arrWfUsers[$value['type_validation']][] = $value['id_validation'];
                    
                    if ($value['type_validation'] == 'user') $arrWfUsersOnly[] = $value['id_validation'];
                    if ($value['type_validation'] == 'group') 
                    {
                        $objGroup = new group();
                        if ($objGroup->open($value['id_validation'])) $arrWfUsersOnly = array_merge($arrWfUsersOnly, array_keys($objGroup->getusers()));
                    }
                }

                // nouveau fichier ?
                $newfile = (empty($_REQUEST['docfile_md5id']));

                // on crée des documents "draft" s'il existe des validateurs et que l'utilisateur courant n'en fait pas partie
                $draft = ((!empty($arrWfUsers['user']) || !empty($arrWfUsers['group'])) && !$booWfVal);
                
                if ($newfile)
                {
                    for ($i=0;$i<=5;$i++)
                    {
                        $arrListFic = array();
                        
                        $error = true;

                        $tmpfile = '';
                        $filename = '';
                        $filesize = 0;

                        // récupération des infos sur le fichier en fonction du mode d'envoi
                        switch($_REQUEST['doc_mode'])
                        {
                            case 'host':
                            default:
                                if (!empty($_FILES["docfile_file_{$i}"]))
                                {
                                    $file = $_FILES["docfile_file_{$i}"];
                                    $error = $file['error'];

                                    if (!$error)
                                    {
                                        $tmpfile = $file['tmp_name'];
                                        $filename = $file['name'];
                                        $filesize = $file['size'];
                                    }

                                }
                            break;

                            case 'server':
                                if (!empty($_REQUEST["docfile_file_{$i}"]))
                                {
                                    $file = _PLOOPI_PATHSHARED.$_REQUEST["docfile_file_{$i}"];

                                    $error = !file_exists($file);
                                    if (!$error)
                                    {
                                        $tmpfile = $file;
                                        $filename = basename($file);
                                        $filesize = filesize($file);
                                    }
                                }

                            break;
                        }
                        
                        if(!$error) // si pas d'erreur
                        {
                            // Ce n'est pas un fichier compressé, on enregistre le document
                            if (empty($_REQUEST["docfile_decompress_{$i}"]))
                            {
                                $arrListFic[] = 
                                    array(
                                        'name'        => $filename,
                                        'file'        => $filename,
                                        'size'        => $filesize,
                                        'description' => ((empty($_REQUEST["docfile_description_{$i}"])) ? '' : $_REQUEST["docfile_description_{$i}"]),
                                        'readonly'    => ((empty($_REQUEST["docfile_readonly_{$i}"])) ? 0 : 1),
                                        'uncompress'  => false
                                    );
                            }
                            elseif(!empty($_REQUEST["docfile_decompress_{$i}"])) // Si c'est un fichier à décompresser
                            {
                                // Création d'un dossier de travail temporaire
                                $tmpfoldername = md5(uniqid(rand(), true));
                                $uncompress_path = doc_getpath()._PLOOPI_SEP.'zip'._PLOOPI_SEP.$tmpfoldername;
                                if (!is_dir($uncompress_path)) ploopi_makedir($uncompress_path);
                                
                                if (is_writeable($uncompress_path))
                                {
                                    switch (pathinfo($filename,PATHINFO_EXTENSION))
                                    {
                                        /*
                                        case 'gz':
                                        //$gz = new 
                                        break;
                                        
                                        case 'bzip':
                                        break;
                                        
                                        case 'bzip':
                                        break;
                                        */  
                                        default:
                                        case 'zip':
                                            $zip = new ZipArchive;
                                            if ($zip->open($tmpfile)===true && $zip->extractTo($uncompress_path))
                                            {
                                                for ($numFicZip=0; $numFicZip<$zip->numFiles;$numFicZip++) 
                                                {
                                                    $arrInfoFicUnzip = $zip->statIndex($numFicZip);
                                                    if($arrInfoFicUnzip['size'])
                                                    {
                                                        $arrName = explode(_PLOOPI_SEP,$arrInfoFicUnzip['name']);
                                                  
                                                        $arrListFic[] = 
                                                            array(
                                                                'name'        => $arrName[count($arrName)-1],
                                                                'file'        => $arrInfoFicUnzip['name'],
                                                                'size'        => $arrInfoFicUnzip['size'],
                                                                'description' => ((empty($_REQUEST["docfile_description_{$i}"])) ? '' : $_REQUEST["docfile_description_{$i}"]),
                                                                'readonly'    => ((empty($_REQUEST["docfile_readonly_{$i}"])) ? 0 : 1),
                                                                'uncompress'  => true
                                                            );
                                                    }
                                                }
                                            }
                                        break;
                                    }
                                }
                            }
                                
                            if(!empty($arrListFic))
                            {           
                                foreach($arrListFic as $fic)
                                {
                                    $docfile = ($draft) ? new docfiledraft() : new docfile();
                                    $docfile->setuwm();
                                
                                    $docfile->fields['description'] = $fic['description'];
                                    $docfile->fields['readonly'] = $fic['readonly'];
                                
                                    $docfile->fields['id_folder'] = $currentfolder;
                                    $docfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
                                
                                    // si le fichier vient d'un dossier partagé, il ne faut pas le déplacer mais le copier
                                    if($fic['uncompress'])
                                    {
                                        if ($_REQUEST['doc_mode'] == 'server') $docfile->sharedfile = $uncompress_path._PLOOPI_SEP.$fic['file'];
                                        else $docfile->tmpfile = $uncompress_path._PLOOPI_SEP.$fic['file'];
                                    }
                                    else
                                    {
                                        if ($_REQUEST['doc_mode'] == 'server') $docfile->sharedfile = $tmpfile;
                                        else $docfile->tmpfile = $tmpfile;
                                    }
                                
                                    $docfile->fields['name'] = $fic['name'];
                                    $docfile->fields['size'] = $fic['size'];
                                    
                                    $error = $docfile->save();
                                
                                
                                    if (!$error)
                                    {
                                        if ($draft)
                                        {
                                            $_SESSION['ploopi']['tickets']['users_selected'] = $arrWfUsersOnly;
                                            ploopi_tickets_send(
                                                "Demande de validation du document <strong>\"{$docfile->fields['name']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})",
                                                "Ceci est un message automatique envoyé suite à une demande de validation du document \"{$docfile->fields['name']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez accéder à ce document pour le valider en cliquant sur le lien ci-dessous.",
                                                true,
                                                0,
                                                _DOC_OBJECT_FILEDRAFT,
                                                $docfile->fields['md5id'],
                                                $docfile->fields['name']
                                            );
                                        }
                                        
                                        if (!$draft && $currentfolder != 0)
                                        {
                                            // On va chercher les abonnés
                                            $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_ADDFILE, _DOC_ACTION_MODIFYFILE));
                                    
                                            // on envoie le ticket de notification d'action sur l'objet
                                            if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_ADDFILE, $docfile->fields['name'], array_keys($arrSubscribers), 'Cet objet à été créé');
                                        }
                                    
                                        ploopi_create_user_action_log(_DOC_ACTION_ADDFILE, $docfile->fields['id']);
                                    }
                                }
                            }
                            // Suppression du dossier temporaire
                            if(isset($uncompress_path) && is_dir($uncompress_path)) ploopi_deletedir($uncompress_path);
                        }
                    }
                }
                else // mise à jour d'un (unique) fichier
                {
                    $docfile = new docfile();
                    if (empty($_GET['docfile_md5id']) || !$docfile->openmd5($_GET['docfile_md5id'])) ploopi_redirect('admin.php');
                    $docfile_id = $docfile->fields['id'];

                    $error = true;

                    $tmpfile = '';
                    $filename = '';
                    $filesize = 0;

                    // récupération des infos sur le fichier en fonction du mode d'envoi
                    switch($_REQUEST['doc_mode'])
                    {
                        case 'host':
                        default:
                            if (!empty($_FILES["docfile_file_host"]))
                            {
                                $file = $_FILES["docfile_file_host"];
                                $error = $file['error'];

                                if (!$error)
                                {
                                    $tmpfile = $file['tmp_name'];
                                    $filename = $file['name'];
                                    $filesize = $file['size'];
                                }

                            }
                        break;

                        case 'server':
                            if (!empty($_POST["_docfile_file_server"]))
                            {
                                $file = _PLOOPI_PATHSHARED.$_POST["_docfile_file_server"];

                                $error = !file_exists($file);
                                if (!$error)
                                {
                                    $tmpfile = $file;
                                    $filename = basename($file);
                                    $filesize = filesize($file);
                                }
                            }

                        break;
                    }

                    if (!$error)
                    {
                        if ($draft)
                        {
                            $docfile = new docfiledraft();
                            $docfile->setuwm();
                            $docfile->fields['id_docfile'] = $docfile_id;
                            $docfile->fields['id_folder'] = $currentfolder;
                        }
                        else $docfile->createhistory();
                    }

                    $docfile->setvalues($_POST,'docfile_');
                    if (empty($_POST['docfile_readonly'])) $docfile->fields['readonly'] = 0;

                    if (!$error)
                    {
                        $docfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];

                        // si le fichier vient d'un dossier partagé, il ne faut pas le déplacer mais le copier
                        if ($_REQUEST['doc_mode'] == 'server') $docfile->sharedfile = $tmpfile;
                        else $docfile->tmpfile = $tmpfile;

                        $docfile->fields['name'] = $filename;
                        $docfile->fields['size'] = $filesize;
                    }

                    $error = $docfile->save();

                    if (!$error)
                    {
                        if ($draft)
                        {
                            $_SESSION['ploopi']['tickets']['users_selected'] = $arrWfUsersOnly;
                            ploopi_tickets_send(
                                "Demande de validation du document <strong>\"{$docfile->fields['name']}\"</strong> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})",
                                "Ceci est un message automatique envoyé suite à une demande de validation du document \"{$docfile->fields['name']}\" du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}<br /><br />Vous pouvez accéder à ce document pour le valider en cliquant sur le lien ci-dessous.",
                                true,
                                0,
                                _DOC_OBJECT_FILEDRAFT,
                                $docfile->fields['md5id'],
                                $docfile->fields['name']
                            );
                        }                        
                        if (!$draft && $currentfolder != 0)
                        {
                            $docfolder = new docfolder();
                            $docfolder->open($docfile->fields['id_folder']);

                            // On va chercher les abonnés
                            $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_MODIFYFILE));

                            // on envoie le ticket de notification d'action sur l'objet
                            if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_MODIFYFILE, $docfile->fields['name'], array_keys($arrSubscribers), 'Cet objet à été modifié');
                        }
                        ploopi_create_user_action_log(_DOC_ACTION_MODIFYFILE, $docfile_id);
                    }
                }

                ploopi_redirect("admin.php?doc_browser&currentfolder={$currentfolder}");
            break;

            case 'doc_filedownloadzip':

                ploopi_init_module('doc');
                $error = true;

                if (!empty($_GET['docfile_md5id']))
                {
                    include_once './modules/doc/class_docfile.php';
                    $docfile = new docfile();
                    $error = ($docfile->openmd5($_GET['docfile_md5id']) === false);
                }
                if (!empty($_GET['docfiledraft_md5id']))
                {
                    include_once './modules/doc/class_docfiledraft.php';
                    $docfile = new docfiledraft();
                    $error = ($docfile->openmd5($_GET['docfiledraft_md5id']) === false);
                }

                if (!$error)
                {
                    // Création d'un dossier de travail temporaire
                    $tmpfoldername = md5(uniqid(rand(), true));
                    $zip_path = doc_getpath()._PLOOPI_SEP.'zip'._PLOOPI_SEP.$tmpfoldername;
                    if (!is_dir($zip_path)) ploopi_makedir($zip_path);

                    if (file_exists($docfile->getfilepath()) && is_writeable($zip_path))
                    {
                        $zip_filename = $docfile->fields['name'].'.zip';

                        $objZip = new ZipArchive();
                        if ($objZip->open($zip_path._PLOOPI_SEP.$zip_filename, ZIPARCHIVE::CREATE) === TRUE)
                        {
                            $objZip->addFile($docfile->getfilepath(), '/'.$docfile->fields['name']);
                            $objZip->close();
                        }

                        // Téléchargement du fichier zip
                        ploopi_downloadfile($zip_path._PLOOPI_SEP.$zip_filename, $zip_filename, true, true, false);

                        // Suppression du dossier temporaire
                        if(isset($zip_path) && is_dir($zip_path)) ploopi_deletedir($zip_path);

                        // Vidage buffer
                        ploopi_die(null, true);
                    }
                }

                ploopi_redirect("admin.php");
            break;

            case 'doc_filedownload':
            case 'doc_fileview':
                ploopi_init_module('doc');
                if (!empty($_GET['docfile_md5id']))
                {
                    include_once './modules/doc/class_docfile.php';
                    $docfile = new docfile();
                    $docfile->openmd5($_GET['docfile_md5id']);
                    
                    if (!empty($_GET['version']))
                    {
                        include_once './modules/doc/class_docfilehistory.php';
                        $docfilehistory = new docfilehistory();
                        $docfilehistory->open($docfile->fields['id'], $_GET['version']);
                        if (file_exists($docfilehistory->getfilepath())) ploopi_downloadfile($docfilehistory->getfilepath(), $docfilehistory->fields['name'], false, ($ploopi_op != 'doc_fileview'));
                    }
                    else
                    {
                        if (file_exists($docfile->getfilepath())) ploopi_downloadfile($docfile->getfilepath(), $docfile->fields['name'], false, ($ploopi_op != 'doc_fileview'));
                    }
                }

                if (!empty($_GET['docfiledraft_md5id']))
                {
                    include_once './modules/doc/class_docfiledraft.php';
                    $docfiledraft = new docfiledraft();
                    $docfiledraft->openmd5($_GET['docfiledraft_md5id']);
                    if (file_exists($docfiledraft->getfilepath())) ploopi_downloadfile($docfiledraft->getfilepath(),$docfiledraft->fields['name']);
                }

                ploopi_die();
            break;

            case 'doc_filedraftdelete':
                if (!empty($_GET['docfiledraft_md5id']))
                {
                    ploopi_init_module('doc');
                    include_once './modules/doc/class_docfiledraft.php';
                    $docfiledraft = new docfiledraft();
                    if ($docfiledraft->openmd5($_GET['docfiledraft_md5id']))
                    {
                        $error = $docfiledraft->delete();
                    }
                    ploopi_redirect("admin.php?op=doc_browser&currentfolder=$currentfolder&error=$error");
                }
            break;

            case 'doc_filedelete':
                ploopi_init_module('doc');

                if (!empty($_GET['docfile_md5id']))
                {
                    include_once './modules/doc/class_docfile.php';
                    include_once './modules/doc/class_docfolder.php';
                    $docfile = new docfile();
                    if ($docfile->openmd5($_GET['docfile_md5id']))
                    {
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
    
                            // on n'est pas à la racine (pas d'abonnement sur la racine)
                            if (!empty($docfile->fields['id_folder']))
                            {
                                // On va chercher les abonnés
                                $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_MODIFYFILE, _DOC_ACTION_DELETEFILE));
    
                                // on envoie le ticket de notification d'action sur l'objet
                                if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FILE, $docfile->fields['md5id'], _DOC_ACTION_DELETEFILE, $docfile->fields['name'], array_keys($arrSubscribers), 'Cet objet à été supprimé');
                            }
    
                            ploopi_create_user_action_log(_DOC_ACTION_DELETEFILE, $docfile->fields['id']);
                            ploopi_redirect("admin.php?op=doc_browser&currentfolder={$currentfolder}&error={$error}");
                        }
                    }
                }

                ploopi_redirect("admin.php?op=doc_browser&currentfolder={$currentfolder}");
            break;

            case 'doc_fileindex':
                if (!empty($_GET['docfile_md5id']))
                {
                    ploopi_init_module('doc');
                    include_once './modules/doc/class_docfile.php';
                    $docfile = new docfile();
                    if ($docfile->openmd5($_GET['docfile_md5id']))
                    {
                        $docfile->parse();
                        $docfile->save();
                    }
                }
                ploopi_redirect("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$_GET['docfile_md5id']}");
            break;

            case 'doc_filepublish':
                if (!empty($_GET['docfiledraft_md5id']))
                {
                    ploopi_init_module('doc');
                    include_once './modules/doc/class_docfiledraft.php';
                    doc_getvalidation();

                    if (in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']));
                    {
                        $docfiledraft = new docfiledraft();
                        if ($docfiledraft->openmd5($_GET['docfiledraft_md5id'])) $docfiledraft->publish();
                    }
                }
                ploopi_redirect("admin.php?op=doc_browser&currentfolder={$currentfolder}");
            break;

            case 'doc_folderpublish':
                if (!empty($_GET['docfolder_id']))
                {
                    ploopi_init_module('doc');
                    doc_getvalidation();

                    if (in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']));
                    {
                        include_once './modules/doc/class_docfolder.php';
                        $docfolder = new docfolder();
                        if ($docfolder->open($_GET['docfolder_id']))
                        {
                            $docfolder->publish();
    
                            // On va chercher les abonnés
                            $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_MODIFYFOLDER));
    
                            // on envoie le ticket de notification d'action sur l'objet
                            if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_MODIFYFOLDER, $docfolder->fields['name'], array_keys($arrSubscribers), 'Cet objet à été publié');
                        }
                    }
                }

                ploopi_redirect("admin.php?op=doc_browser&currentfolder={$currentfolder}");
            break;

            case 'doc_folderdelete':
                ploopi_init_module('doc');

                if (!empty($_GET['docfolder_id']))
                {
                    include_once './modules/doc/class_docfolder.php';

                    $docfolder = new docfolder();
                    if ($docfolder->open($_GET['docfolder_id']))
                    {
    
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
                            // On va chercher les abonnés
                            $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_DELETEFOLDER, _DOC_ACTION_MODIFYFOLDER));
    
                            // on envoie le ticket de notification d'action sur l'objet
                            if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_DELETEFOLDER, $docfolder->fields['name'], array_keys($arrSubscribers), 'Cet objet à été supprimé');
    
                            $docfolder->delete();
                            ploopi_create_user_action_log(_DOC_ACTION_DELETEFOLDER, $docfolder->fields['id']);
                        }
                    }

                    ploopi_redirect("admin.php?op=doc_browser&currentfolder={$currentfolder}");
                }
            break;

            case 'doc_foldersave':
                ploopi_init_module('doc');

                include_once './modules/doc/class_docfolder.php';

                $docfolder = new docfolder();

                /**
                 * Modification dossier existant
                 */

                if (!empty($_GET['docfolder_id']) && is_numeric($_GET['docfolder_id']))
                {
                    if ($docfolder->open($_GET['docfolder_id']))
                    {
                        $docfolder->setvalues($_POST,'docfolder_');
                        
                        if (empty($_POST['docfolder_readonly'])) $docfolder->fields['readonly'] = 0;
                        if (empty($_POST['docfolder_readonly_content'])) $docfolder->fields['readonly_content'] = 0;
                        if (empty($_POST['docfolder_allow_feeds'])) $docfolder->fields['allow_feeds'] = 0;
                        $docfolder->save();
    
                        // On va chercher les abonnés
                        $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_MODIFYFOLDER));
    
                        // on envoie le ticket de notification d'action sur l'objet
                        if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_MODIFYFOLDER, $docfolder->fields['name'], array_keys($arrSubscribers), 'Cet objet à été modifié');
    
                        // SHARES
                        ploopi_share_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], -1, 'doc_share_folder');
                        doc_resetshare();
    
                        // WORKFLOW
                        ploopi_validation_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], -1, 'doc_validation_folder');
                        doc_resetvalidation();
                        
                        // LOG
                        ploopi_create_user_action_log(_DOC_ACTION_MODIFYFOLDER, $docfolder->fields['id']);
                    }
                }
                else // Nouveau dossier
                {
                    $docfolder->setvalues($_POST,'docfolder_');
                    if (empty($_POST['docfolder_readonly'])) $docfolder->fields['readonly'] = 0;
                    if (empty($_POST['docfolder_readonly_content'])) $docfolder->fields['readonly_content'] = 0;

                    $docfolder->fields['id_folder'] = $currentfolder;
                    $docfolder->setuwm();

                    // on recherche s'il existe des validateurs pour ce dossier
                    $arrWfUsers = array('group' => array(), 'user' => array());
                    $arrWfUsersOnly = array(); // utilisateurs uniquement (groupes compris)
                    
                    $objUser = new user();
                    $objUser->open($_SESSION['ploopi']['userid']);
                    $arrGroups = $objUser->getgroups(true);
                    
                    $booWfVal = false;
                    foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) 
                    {
                        if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booWfVal = true;
                        if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booWfVal = true;
                        
                        $arrWfUsers[$value['type_validation']][] = $value['id_validation'];
                        
                        if ($value['type_validation'] == 'user') $arrWfUsersOnly[] = $value['id_validation'];
                        if ($value['type_validation'] == 'group') 
                        {
                            $objGroup = new group();
                            if ($objGroup->open($value['id_validation'])) $arrWfUsersOnly = array_merge($arrWfUsersOnly, array_keys($objGroup->getusers()));
                        }
                    }
                    
                    if ((!empty($arrWfUsers['user']) || !empty($arrWfUsers['group'])) && !$booWfVal)
                    {
                        $docfolder->fields['published'] = 0;

                        $_SESSION['ploopi']['tickets']['users_selected'] = $arrWfUsersOnly;
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

                    // On va chercher les abonnés
                    $arrSubscribers = $docfolder->getSubscribers(array(_DOC_ACTION_ADDFOLDER, _DOC_ACTION_MODIFYFOLDER));

                    // on envoie le ticket de notification d'action sur l'objet
                    if (!empty($arrSubscribers)) ploopi_subscription_notify(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], _DOC_ACTION_ADDFOLDER, $docfolder->fields['name'], array_keys($arrSubscribers), 'Cet objet à été créé');

                    // SHARES
                    ploopi_share_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], -1, 'doc_share_folder');
                    doc_resetshare();

                    // WORKFLOW
                    ploopi_validation_save(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], -1, 'doc_validation_folder');
                    doc_resetvalidation();

                    // LOG
                    ploopi_create_user_action_log(_DOC_ACTION_ADDFOLDER, $docfolder->fields['id']);
                }
                ploopi_redirect("admin.php?op=doc_folderform&currentfolder={$docfolder->fields['id']}");

            break;

        }
    }

    /**
     * Autre opérations qui ne nécessite pas que l'on soit dans le module DOC
     */
    switch($ploopi_op)
    {
        case 'doc_getfiles':
            if (!empty($_GET['idfolder']) && is_numeric($_GET['idfolder']) && isset($_GET['filter']))
            {
                switch($_GET['filter'])
                {
                    case 'doc_selectimage':
                        $arrFilter = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
                    break;

                    case 'doc_selectflash':
                        $arrFilter = array('swf', 'flv');
                    break;

                    default:
                        $arrFilter = array();
                    break;
                }

                // on cherche les fichiers du dossier "idfolder"
                // en vérifiant au passage que le module est accessible et que le dossier est public

                include_once './include/functions/system.php';
                $arrModules = ploopi_getmoduleid('doc', false);
                $arrFiles = array();

                if (!empty($arrModules))
                {
                    // exec requete + encodage JSON
                    $db->query("
                        SELECT      doc.md5id,
                                    doc.name,
                                    doc.size

                        FROM        ploopi_mod_doc_file doc

                        INNER JOIN  ploopi_mod_doc_folder folder
                        ON          folder.id = doc.id_folder

                        WHERE       doc.id_folder = {$_GET['idfolder']}
                        AND         folder.id_module IN (".implode(',', $arrModules).")
                        AND         folder.foldertype = 'public'

                        ORDER BY    doc.name
                    ");

                    while ($row = $db->fetchrow())
                    {
                        switch($_GET['filter'])
                        {
                            case 'doc_selectimage':
                            case 'doc_selectflash':
                                $row['url'] = "index-quick.php?ploopi_op=doc_file_view&docfile_md5id={$row['md5id']}";
                            break;
        
                            default:
                                $row['url'] = "index-quick.php?ploopi_op=doc_file_download&docfile_md5id={$row['md5id']}";
                            break;
                        }

                        if (empty($arrFilter) || in_array(ploopi_file_getextension($row['name']),$arrFilter)) $arrFiles[] = $row;
                    }
                }

                ploopi_print_json($arrFiles);
            }

            ploopi_die();
        break;

        case 'doc_selectfile':
        case 'doc_selectimage':
        case 'doc_selectflash':
            ob_start();
            include_once './modules/doc/fck_explorer.php';
            $main_content = ob_get_contents();
            @ob_end_clean();

            $template_body->assign_vars(array(
                'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                'ADDITIONAL_JAVASCRIPT' => $ploopi_additional_javascript,
                'PAGE_CONTENT'          => $main_content
                )
            );

            $template_body->pparse('body');
            ploopi_die();
        break;

        case 'doc_image_get':
            $intTimeCache = 2592000; // 30 jours
            
            include_once './include/classes/data_object.php';
            include_once './include/functions/date.php';
            include_once './include/functions/filesystem.php';
            include_once './include/functions/image.php';
            include_once './modules/doc/include/global.php';
            include_once './modules/doc/class_docfile.php';
            include_once './include/classes/cache.php';
            
            ploopi_ob_clean();
            
            
            if ((!empty($_GET['docfile_id']) || !empty($_GET['docfile_md5id'])) && !empty($_GET['version']))
            {
                $docfile = new docfile();
                if(!empty($_GET['docfile_id'])) 
                    $objCache = new ploopi_cache(md5('doc_fck_'.$_GET['docfile_id'].'_'.$_GET['version']), $intTimeCache); // Attribution d'un groupe spécifique pour le cache pour permettre un clean précis
                    
                if(!empty($_GET['docfile_md5id']))
                    $objCache = new ploopi_cache(md5('doc_fck_'.$_GET['docfile_md5id'].'_'.$_GET['version']), $intTimeCache); // Attribution d'un groupe spécifique pour le cache pour permettre un clean précis
                    
                $height = (isset($_GET['height'])) ? $_GET['height'] : 0;
                $width = (isset($_GET['width'])) ? $_GET['width'] : 0;
                $coef = (isset($_GET['coef'])) ? $_GET['coef'] : 0;
                /*
                echo $width.'x'.$height.' ('.$coef.')';
                ploopi_die();
                */
                if(!$objCache->start()) // si pas de cache on le crée
                {
                    ploopi_init_module('doc', false, false, false);
                
                    include_once './modules/doc/class_docfile.php';
                    include './include/classes/mimethumb.php';
                    
                    $objDoc = new docfile();
                    $objThumb = new mimethumb($width,$height,$coef,'png','transparent');
                    
                    if(!empty($_GET['docfile_id']) && $objDoc->open($_GET['docfile_id']))
                        $objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']);
                        
                    elseif(!empty($_GET['docfile_md5id']) && $objDoc->openmd5($_GET['docfile_md5id']))
                        $objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']);
                        
                    if(isset($objCache)) $objCache->end();
                }
                else
                {
                    header("Content-Type: image/png");
                }
            }
            ploopi_die();
        break;

        case 'doc_explorer':
            
            $_SESSION['ploopi']['doc'][$_SESSION['ploopi']['moduleid']]['typeshow'] = 'list';
            
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
        
        case 'doc_explorer_thumb':
            
            $_SESSION['ploopi']['doc'][$_SESSION['ploopi']['moduleid']]['typeshow'] = 'thumb';
            
            ploopi_init_module('doc');

            include_once './modules/doc/class_docfolder.php';

            $docfolder_readonly_content = false;

            if (!empty($currentfolder))
            {
                $docfolder = new docfolder();
                $docfolder->open($currentfolder);
                $docfolder_readonly_content = ($docfolder->fields['readonly_content'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']);
            }

            include_once './modules/doc/public_explorer_thumb.php';
            ploopi_die();
        break;
    }
}

/**
 * Autres opérations publiques (utilisateur non connecté, frontoffice)
 */

switch($ploopi_op)
{
    case 'doc_getthumbnail':
        
        $intTimeCache = 2592000; // 30 jours

        $width = (!empty($_GET['width']) && is_numeric($_GET['width'])) ? $_GET['width'] : 111;
        $height = (!empty($_GET['height']) && is_numeric($_GET['height'])) ? $_GET['height'] : 90;
        
        include_once './include/classes/cache.php';
        ploopi_ob_clean();

        $objCache = new ploopi_cache(md5('doc_thumb_'.$_GET['docfile_md5id'].'_'.$_GET['version']), $intTimeCache); // Attribution d'un groupe spécifique pour le cache pour permettre un clean précis
        $objCache->set_groupe('module_doc_'.$_SESSION['ploopi']['workspaceid'].'_'.$_SESSION['ploopi']['moduleid']); 
        
        if(!$objCache->start()) // si pas de cache on le crée
        {
            ploopi_init_module('doc', false, false, false);
            
            include_once './modules/doc/class_docfile.php';
            include './include/classes/mimethumb.php';
            
            $objDoc = new docfile();
            $objThumb = new mimethumb($width, $height, 0, 'png', 'transparent');
            
            if($objDoc->openmd5($_GET['docfile_md5id']))
                $objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']);
            if(isset($objCache)) $objCache->end();
        }
        else
        {
            header("Content-Type: image/png");
        }
        ploopi_die();
    break;            

    case 'doc_file_view':
    case 'doc_file_download':
        include_once './include/start/constants.php';
        include_once './include/classes/data_object.php';
        include_once './include/functions/date.php';
        include_once './include/functions/filesystem.php';
        include_once './modules/doc/class_docfile.php';

        ploopi_init_module('doc', false, false, false);

        if (!empty($_GET['docfile_md5id']))
        {
            $db->query("SELECT id FROM ploopi_mod_doc_file WHERE md5id = '".$db->addslashes($_GET['docfile_md5id'])."'");
            if ($fields = $db->fetchrow())
            {
                $docfile = new docfile();
                if ($docfile->open($fields['id']) && file_exists($docfile->getfilepath()))
                {
                    ploopi_downloadfile($docfile->getfilepath(),$docfile->fields['name'], false, $ploopi_op == 'doc_file_download');
                }
            }
        }

        ploopi_die();
    break;
}

?>
