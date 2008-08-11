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
 * Gestion d'un bloc de document associé à un enregistrement d'un objet.
 * Permet notamment de gérer des pièces jointes à n'importe quel objet de ploopi.
 * 
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * Renvoie l'identifiant d'un bloc de documents pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @return string identifiant du bloc de documents
 * 
 * @see md5
 */

function ploopi_documents_getid($id_object, $id_record, $id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    return md5("{$id_module}_{$id_object}_".addslashes($id_record));
}

/**
 * Insère un bloc de documents pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param array $rights tableau définissant les droits 'DOCUMENT_CREATE':boolean, 'DOCUMENT_MODIFY':boolean, 'DOCUMENT_DELETE':boolean, 'FOLDER_CREATE':boolean, 'FOLDER_MODIFY':boolean, 'FOLDER_DELETE': boolean, 'SEARCH': boolean
 * @param array $default_folders tableau définissant les sous-dossiers par défaut à créer
 * @param array $params tableau définissant les paramètres du bloc 'ROOT_NAME':string, 'ATTACHEMENT':boolean, 'FIELDS':array 
 * @param int $id_user identifiant de l'utilisateur
 * @param int $id_workspace identifiant de l'espace
 * @param int $id_module identifiant du module
 */

function ploopi_documents($id_object, $id_record, $rights = array(), $default_folders = array(), $params = array(), $load_doc = true, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
    global $db;
    global $ploopi_documents_idinstance;

    if (empty($ploopi_documents_idinstance)) $ploopi_documents_idinstance = 0;
    $ploopi_documents_idinstance++;

    if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];
    if ($id_workspace == -1) $id_workspace = $_SESSION['ploopi']['workspaceid'];
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    // generate documents id
    $documents_id = ploopi_documents_getid($id_object, $id_record, $id_module);

    // permet de mettre des droits par défaut si le paramètre est incomplet
    $rights = 
        array_merge(
            array(
                'DOCUMENT_CREATE' => true,
                'DOCUMENT_MODIFY' => true,
                'DOCUMENT_DELETE' => true,
                'FOLDER_CREATE' => true,
                'FOLDER_MODIFY' => true,
                'FOLDER_DELETE' => true,
                'SEARCH' => true
            ),
            (is_array($rights)) ? $rights : array()
        );
        
    if (empty($params['ROOT_NAME'])) $params['ROOT_NAME'] = 'Racine';
    if (empty($params['ATTACHEMENT'])) $params['ATTACHEMENT'] = true;
    if (empty($params['FIELDS'])) $params['FIELDS'] = array();

    $_SESSION['documents'] = 
        array (    
            'id_object'     => $id_object,
            'id_record'     => $id_record,
            'id_user'       => $id_user,
            'id_workspace'  => $id_workspace,
            'id_module'     => $id_module,
            'documents_id'  => $documents_id,
            'mode'          => '', // peut valoir 'selectfile'
            'root_name'     => $params['ROOT_NAME'],
            'attachement'   => $params['ATTACHEMENT'],
            'fields'        => $params['FIELDS']
        );


    $_SESSION['documents']['rights'] = $rights;

    include_once './include/classes/documents.php';

    // on va chercher la racine
    $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = 0 and id_object = '{$_SESSION['documents']['id_object']}' and id_record = '".addslashes($_SESSION['documents']['id_record'])."'");

    if ($row = $db->fetchrow()) $currentfolder = $row['id'];
    else // racine inexistante, il faut la crÃ©er
    {
        $documentsfolder = new documentsfolder();
        $documentsfolder->fields['name'] = $params['ROOT_NAME'];
        $documentsfolder->fields['id_folder'] = 0;
        $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
        $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
        $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
        $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
        $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
        $currentfolder = $documentsfolder->save();

        if (is_array($default_folders))
        {
            foreach ($default_folders as $foldername)
            {
                $documentsfolder = new documentsfolder();
                $documentsfolder->fields['id_folder'] = $currentfolder;
                $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
                $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
                $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
                $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
                $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
                $documentsfolder->fields['name'] = $foldername;
                $documentsfolder->fields['system'] = 1;
                $documentsfolder->save();
            }
        }
    }
    ?>
    <div id="ploopidocuments_<? echo $documents_id; ?>">
    </div>
    <?
    if ($load_doc)
    {
        ?>
        <script type="text/javascript">
            ploopi_window_onload_stock(function () { ploopi_documents_browser('<? echo $documents_id; ?>', '', '', '', true); });
        </script>
        <?
    }
}


/**
 * Renvoie le dossier de stockage des documents
 *
 * @param boolean indique si le dossier doit être créé
 * @return string chemin physique relatif du dossier de stockage
 */

function ploopi_documents_getpath($createpath = false)
{
    $path = _PLOOPI_PATHDATA._PLOOPI_SEP."documents";

    if ($createpath)
    {
        // test for existing _PLOOPI_PATHDATA path
        if (!is_dir(_PLOOPI_PATHDATA)) mkdir(_PLOOPI_PATHDATA);

        if ($path != '' && !is_dir($path)) mkdir($path);
    }

    return($path);
}

/**
 * Renvoie le nombre d'éléments d'un dossier
 *
 * @param int $id_folder identifiant du dossier
 * @return int nombre d'élément du dossier
 */

function ploopi_documents_countelements($id_folder)
{
    global $db;

    $c = 0;

    $db->query("SELECT count(id) as c FROM ploopi_documents_folder WHERE id_folder = {$id_folder}");
    if ($row = $db->fetchrow()) $c += $row['c'];

    $db->query("SELECT count(id) as c FROM ploopi_documents_file WHERE id_folder = {$id_folder}");
    if ($row = $db->fetchrow()) $c += $row['c'];

    return($c);
}

/**
 * Renvoie un tableau de fichiers attachés à enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_folder identifiant du sous-dossier (optionnel)
 * @return array tableau contenant le nom des fichiers
 */

function ploopi_documents_getfiles($id_object, $id_record, $id_folder = 0)
{
    global $db;

    $files = array();

    if ($id_folder == 0) $db->query("SELECT * FROM ploopi_documents_file WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY name");
    else $db->query("SELECT * FROM ploopi_documents_file WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' and id_folder = {$id_folder} ORDER BY name");
    while ($row = $db->fetchrow()) $files[] = $row;

    return($files);
}

/**
 * Renvoie un tableau de dossiers attachés à un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @return array tableau contenant le nom des dossiers
 */
 
function ploopi_documents_getfolders($id_object, $id_record)
{
    global $db;

    $folders = array();

    $db->query("SELECT * FROM ploopi_documents_folder WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY name");

    while ($row = $db->fetchrow()) $folders[$row['id_folder']][] = $row;

    return($folders);
}

/**
 * Sauvegarde un fichier dans un dossier attaché à un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param string $file chemin du fichier
 * @param string $filename nom du fichier
 * @param int $id_folder identifiant du dossier
 * @param string $label libellé du fichier (optionnel)
 * @param string $description description du fichier (optionnel)
 * @param string $ref référence du fichier (optionnel)
 * @param int $id_user identifiant du l'utilisateur (optionnel)
 * @param int $id_workspace identifiant de l'espace de travail (optionnel)
 * @param int $id_module identifiant du module (optionnel)
 * 
 * @copyright Ovensia
 */

function ploopi_documents_savefile($id_object, $id_record, $file, $filename, $id_folder, $label = '', $description = '', $ref = '', $id_user = null, $id_workspace = null, $id_module = null)
{
    if (file_exists($file))
    {
        include_once './include/classes/documents.php';
        $documentsfile = new documentsfile();
    
        if (empty($id_user)) $id_user = $_SESSION['ploopi']['userid'];
        if (empty($id_workspace)) $id_workspace = $_SESSION['ploopi']['workspaceid'];
        if (empty($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];
    
        
        $documentsfile->fields['id_object'] = $id_object;
        $documentsfile->fields['id_record'] = $id_record;
        
        $documentsfile->fields['name'] = $filename;
        $documentsfile->fields['size'] = filesize($file);
        $documentsfile->fields['extension'] = ploopi_file_getextension($filename);
        $documentsfile->fields['label'] = $label;
        $documentsfile->fields['description'] = $description;
        $documentsfile->fields['ref'] = $ref;
        
        $documentsfile->fields['timestp_file'] = ploopi_createtimestamp();
        $documentsfile->fields['timestp_create'] = $documentsfile->fields['timestp_file'];
        $documentsfile->fields['timestp_modify'] = $documentsfile->fields['timestp_file']; 
        
        $documentsfile->fields['id_user'] = $id_user;
        $documentsfile->fields['id_workspace'] = $id_workspace;
        $documentsfile->fields['id_module'] = $id_module;
        
        $documentsfile->fields['id_folder'] = $id_folder;
        
        $documentsfile->fields['id_user_modify'] = $documentsfile->fields['id_user'];
    
        $documentsfile->setfile($file);
        
        $documentsfile->save();
    }
}
?>
