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
 * Gestion d'un bloc de document associ� � un enregistrement d'un objet.
 * Permet notamment de g�rer des pi�ces jointes � n'importe quel objet de ploopi.
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
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
 * Retourne le code d'appel javascript du popup de cr�ation de fichier
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param array $rights tableau d�finissant les droits 'DOCUMENT_CREATE':boolean, 'DOCUMENT_MODIFY':boolean, 'DOCUMENT_DELETE':boolean, 'FOLDER_CREATE':boolean, 'FOLDER_MODIFY':boolean, 'FOLDER_DELETE': boolean, 'SEARCH': boolean
 * @param array $default_folders tableau d�finissant les sous-dossiers par d�faut � cr�er
 * @param array $params tableau d�finissant les param�tres du bloc 'ROOT_NAME':string, 'ATTACHEMENT':boolean, 'FIELDS':array
 * @param int $width largeur du popup
 * @param int $id_user identifiant de l'utilisateur
 * @param int $id_workspace identifiant de l'espace
 * @param int $id_module identifiant du module
 *
 * @return string code d'appel javascript
 */

function ploopi_documents_getopenfilejs($id_object, $id_record, $rights = array(), $default_folders = array(), $params = array(), $width = 600, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
    // Important : il faut d�finir un mode de fonctionnement et une cible dans le param�tre $params
    // Mode : tofield / tocallback
    // Target : champ de destination (tofield) ou fonction de callback tocallback)

    // Instanciation de la ged
    $documents_id = ploopi_documents($id_object, $id_record, $rights, $default_folders, $params, false, $id_user, $id_workspace, $id_module);

    $query = ploopi_queryencode("ploopi_op=documents_openfile&currentfolder={$_SESSION['documents'][$documents_id]['currentfolder']}&documents_id={$documents_id}&selectfile");

    return "ploopi_documents_openfile('{$query}', event)";
}

/**
 * Retourne le code d'appel javascript du popup de s�lection de fichier
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param array $rights tableau d�finissant les droits 'DOCUMENT_CREATE':boolean, 'DOCUMENT_MODIFY':boolean, 'DOCUMENT_DELETE':boolean, 'FOLDER_CREATE':boolean, 'FOLDER_MODIFY':boolean, 'FOLDER_DELETE': boolean, 'SEARCH': boolean
 * @param array $default_folders tableau d�finissant les sous-dossiers par d�faut � cr�er
 * @param array $params tableau d�finissant les param�tres du bloc 'ROOT_NAME':string, 'ATTACHEMENT':boolean, 'FIELDS':array
 * @param int $width largeur du popup
 * @param int $id_user identifiant de l'utilisateur
 * @param int $id_workspace identifiant de l'espace
 * @param int $id_module identifiant du module
 *
 * @return string code d'appel javascript
 */

function ploopi_documents_getselectfilejs($id_object, $id_record, $rights = array(), $default_folders = array(), $params = array(), $width = 600, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
    // Important : il faut d�finir un mode de fonctionnement et une cible dans le param�tre $params
    // Mode : tofield / tocallback
    // Target : champ de destination (tofield) ou fonction de callback tocallback)

    // Instanciation de la ged
    $documents_id = ploopi_documents($id_object, $id_record, $rights, $default_folders, $params, false, $id_user, $id_workspace, $id_module);

    $query = ploopi_queryencode("ploopi_op=documents_selectfile&documents_id={$documents_id}");

    return "ploopi_documents_selectfile('{$query}', event, {$width})";
}


/**
 * Ins�re un bloc de documents pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param array $rights tableau d�finissant les droits 'DOCUMENT_CREATE':boolean, 'DOCUMENT_MODIFY':boolean, 'DOCUMENT_DELETE':boolean, 'FOLDER_CREATE':boolean, 'FOLDER_MODIFY':boolean, 'FOLDER_DELETE': boolean, 'SEARCH': boolean
 * @param array $default_folders tableau d�finissant les sous-dossiers par d�faut � cr�er
 * @param array $params tableau d�finissant les param�tres du bloc 'ROOT_NAME':string, 'ATTACHEMENT':boolean, 'FIELDS':array
 * @param bool $load_doc true si on souhaite afficher directement les documents
 * @param int $id_user identifiant de l'utilisateur
 * @param int $id_workspace identifiant de l'espace
 * @param int $id_module identifiant du module
 *
 * @return string identifiant de l'instance
 */

function ploopi_documents($id_object, $id_record, $rights = array(), $default_folders = array(), $params = array(), $load_doc = true, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
    include_once './include/classes/documents.php';

    global $db;

    if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];
    if ($id_workspace == -1) $id_workspace = $_SESSION['ploopi']['workspaceid'];
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    // generate documents id
    $documents_id = ploopi_documents_getid($id_object, $id_record, $id_module).(isset($params['UNIQID']) ? $params['UNIQID'] : '');

    // permet de mettre des droits par d�faut si le param�tre est incomplet
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
    if (empty($params['FIELDS_SIZE'])) $params['FIELDS_SIZE'] = array();
    if (empty($params['CALLBACK_FUNC'])) $params['CALLBACK_FUNC'] = null;
    if (empty($params['CALLBACK_INC'])) $params['CALLBACK_INC'] = null;
    if (empty($params['DEFAULT_FOLDER'])) $params['DEFAULT_FOLDER'] = '';
    if (empty($params['LIMIT'])) $params['LIMIT'] = 0;
    if (empty($params['MODE'])) $params['MODE'] = '';
    if (empty($params['TARGET'])) $params['TARGET'] = '';

    // permet de modifier le champ sur lequel va s'effectuer le tri du tableau
    if (empty($params['ORDER_BY'])) $params['ORDER_BY'] = 'name';

    // permet de modifier l'ordre de tri du tableau (croissant/d�croissant)
    if (empty($params['SORT'])) $params['SORT'] = 'ASC';

    // hauteur fixe de la fen�tre
    if (empty($params['HEIGHT'])) $params['HEIGHT'] = 0;

    // permet de param�trer le libell� et l'icone des boutons de la mini-ged
    if (empty($params['ROOT_PLACE'])) $params['ROOT_PLACE'] = 'Aller au Dossier Racine';
    if (empty($params['ROOT_PLACE_IMG'])) $params['ROOT_PLACE_IMG'] = $_SESSION['ploopi']['template_path'] . '/img/documents/ico_home.png';
    if (empty($params['NEW_FOLDER'])) $params['NEW_FOLDER'] = 'Cr�er un nouveau Dossier';
    if (empty($params['NEW_FOLDER_IMG'])) $params['NEW_FOLDER_IMG'] = $_SESSION['ploopi']['template_path'] . '/img/documents/ico_newfolder.png';
    if (empty($params['NEW_FILE'])) $params['NEW_FILE'] = 'Cr�er un nouveau Fichier';
    if (empty($params['NEW_FILE_IMG'])) $params['NEW_FILE_IMG'] = $_SESSION['ploopi']['template_path'] . '/img/documents/ico_newfile.png';

    $_SESSION['documents'][$documents_id] = array (
        'id_object'     => $id_object,
        'id_record'     => $id_record,
        'id_user'       => $id_user,
        'id_workspace'  => $id_workspace,
        'id_module'     => $id_module,
        'documents_id'  => $documents_id,
        'root_name'     => $params['ROOT_NAME'],
        'attachement'   => $params['ATTACHEMENT'],
        'fields'        => $params['FIELDS'],
        'fields_size'   => $params['FIELDS_SIZE'],
        'callback_func' => $params['CALLBACK_FUNC'], // Fonction de callback php appel�e suite � l'ajout d'un fichier/dossier
        'callback_inc'  => $params['CALLBACK_INC'], // Inclusion n�cessaire au callback php
        'default_folder'=> $params['DEFAULT_FOLDER'],
        'order_by'      => $params['ORDER_BY'],
        'sort'          => $params['SORT'],
        'rights'        => $rights,
        'limit'         => $params['LIMIT'],
        'height'        => $params['HEIGHT'],
        'root_place'    => $params['ROOT_PLACE'],
        'root_place_img'=> $params['ROOT_PLACE_IMG'],
        'new_folder'    => $params['NEW_FOLDER'],
        'new_folder_img'=> $params['NEW_FOLDER_IMG'],
        'new_file'      => $params['NEW_FILE'],
        'new_file_img'  => $params['NEW_FILE_IMG'],
        // Pour le s�lecteur de fichier
        'mode'          => $params['MODE'], // peut valoir 'selectfile'
        'target'        => $params['TARGET'], // id de champ ou fonction de callback
    );

    // on va chercher la racine
    $db->query("SELECT md5id FROM ploopi_documents_folder WHERE id_folder = 0 AND id_object = '{$_SESSION['documents'][$documents_id]['id_object']}' AND id_module = '{$_SESSION['documents'][$documents_id]['id_module']}' AND id_record = '".addslashes($_SESSION['documents'][$documents_id]['id_record'])."'");

    if ($row = $db->fetchrow()) $currentfolder = $row['md5id'];
    else // racine inexistante, il faut la cr�er
    {
        $documentsfolder = new documentsfolder();
        $documentsfolder->fields['name'] = $params['ROOT_NAME'];
        $documentsfolder->fields['id_folder'] = 0;
        $documentsfolder->fields['id_object'] = $_SESSION['documents'][$documents_id]['id_object'];
        $documentsfolder->fields['id_record'] = $_SESSION['documents'][$documents_id]['id_record'];
        $documentsfolder->fields['id_module'] = $_SESSION['documents'][$documents_id]['id_module'];
        $documentsfolder->fields['id_user'] = $_SESSION['documents'][$documents_id]['id_user'];
        $documentsfolder->fields['id_workspace'] = $_SESSION['documents'][$documents_id]['id_workspace'];
        $id =  $documentsfolder->save();
        $currentfolder = $documentsfolder->fields['md5id'];

        if (is_array($default_folders))
        {
            foreach ($default_folders as $foldername)
            {
                $documentsfolder = new documentsfolder();
                $documentsfolder->fields['id_folder'] = $id;
                $documentsfolder->fields['id_object'] = $_SESSION['documents'][$documents_id]['id_object'];
                $documentsfolder->fields['id_record'] = $_SESSION['documents'][$documents_id]['id_record'];
                $documentsfolder->fields['id_module'] = $_SESSION['documents'][$documents_id]['id_module'];
                $documentsfolder->fields['id_user'] = $_SESSION['documents'][$documents_id]['id_user'];
                $documentsfolder->fields['id_workspace'] = $_SESSION['documents'][$documents_id]['id_workspace'];
                $documentsfolder->fields['name'] = $foldername;
                $documentsfolder->fields['system'] = 1;
                $documentsfolder->save();
            }
        }
    }

    $_SESSION['documents'][$documents_id]['currentfolder'] = $currentfolder;

    if ($load_doc)
    {
        ?>
        <div id="ploopidocuments_<?php echo $documents_id; ?>">
            <?php ploopi_documents_browser($currentfolder, $documents_id); ?>
        </div>
        <?php
    }

    return $documents_id;
}

/**
 * Renvoie le dossier de stockage des documents
 *
 * @param boolean indique si le dossier doit �tre cr��
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
 * Renvoie le nombre d'�l�ments d'un dossier
 *
 * @param int $id_folder identifiant du dossier
 * @return int nombre d'�l�ment du dossier
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
 * Renvoie un tableau de fichiers attach�s � enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_folder identifiant du sous-dossier (optionnel)
 * @param int $id_workspace identifiant de l'espace de travail (optionnel)
 * @param int $id_module identifiant du module (optionnel)
 * @return array tableau contenant le nom des fichiers
 */

function ploopi_documents_getfiles($id_object, $id_record, $id_folder = 0, $id_module = -1)
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $files = array();

    if ($id_folder == 0) $db->query("SELECT * FROM ploopi_documents_file WHERE id_module = {$id_module} AND id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY name");
    else $db->query("SELECT * FROM ploopi_documents_file WHERE id_module = {$id_module} AND id_object = '{$id_object}' AND id_record = '{$id_record}' and id_folder = {$id_folder} ORDER BY name");
    while ($row = $db->fetchrow()) $files[] = $row;

    return($files);
}

/**
 * Renvoie un tableau de dossiers attach�s � un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_workspace identifiant de l'espace de travail (optionnel)
 * @param int $id_module identifiant du module (optionnel)
 * @return array tableau contenant le nom des dossiers
 */

function ploopi_documents_getfolders($id_object, $id_record, $id_module = -1)
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $folders = array();

    $db->query("SELECT * FROM ploopi_documents_folder WHERE id_module = {$id_module} AND id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY parents, name");

    while ($row = $db->fetchrow()) $folders[$row['id_folder']][] = $row;

    return($folders);
}

/**
 * Sauvegarde un fichier dans un dossier attach� � un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param string $file chemin du fichier
 * @param string $filename nom du fichier
 * @param int $id_folder identifiant du dossier
 * @param string $label libell� du fichier (optionnel)
 * @param string $description description du fichier (optionnel)
 * @param string $ref r�f�rence du fichier (optionnel)
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

        return $documentsfile->save() ? null : $documentsfile->fields['id'];
    }
    else return false;
}

/**
 * Sauvegarde un dossier attach� � un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param string $name nom du dossier
 * @param int $id_folder identifiant du dossier
 * @param string $label libell� du fichier (optionnel)
 * @param string $description description du fichier (optionnel)
 * @param bool $system 1 si c'est un dossier "syst�me" (optionnel)
 * @param int $id_user identifiant du l'utilisateur (optionnel)
 * @param int $id_workspace identifiant de l'espace de travail (optionnel)
 * @param int $id_module identifiant du module (optionnel)
 *
 * @copyright Ovensia
 */

function ploopi_documents_savefolder($id_object, $id_record, $name, $id_folder, $description = '', $system = 0, $id_user = null, $id_workspace = null, $id_module = null)
{

    include_once './include/classes/documents.php';
    $documentsfolder = new documentsfolder();

    if (empty($id_user)) $id_user = $_SESSION['ploopi']['userid'];
    if (empty($id_workspace)) $id_workspace = $_SESSION['ploopi']['workspaceid'];
    if (empty($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];

    $documentsfolder->fields['id_object'] = $id_object;
    $documentsfolder->fields['id_record'] = $id_record;

    $documentsfolder->fields['name'] = $name;
    $documentsfolder->fields['description'] = $description;
    $documentsfolder->fields['system'] = $system;

    $documentsfolder->fields['timestp_create'] = ploopi_createtimestamp();
    $documentsfolder->fields['timestp_modify'] = $documentsfolder->fields['timestp_create'];

    $documentsfolder->fields['id_user'] = $id_user;
    $documentsfolder->fields['id_workspace'] = $id_workspace;
    $documentsfolder->fields['id_module'] = $id_module;

    $documentsfolder->fields['id_folder'] = $id_folder;

    $documentsfolder->fields['id_user_modify'] = $documentsfolder->fields['id_user'];

    return $documentsfolder->save();
}

/**
 * Affiche l'explorateur de documents
 *
 * @param int $currentfolder identifiant du dossier parcouru
 */

function ploopi_documents_browser($currentfolder, $documents_id)
{
    include_once './include/classes/documents.php';

    // V�rification param�tres
    if (!isset($_SESSION['documents'][$documents_id])) return;

    global $db;
    global $skin;
    ?>
    <div class="documents_browser">

        <div class="documents_path">
            <?php
            if ($_SESSION['documents'][$documents_id]['rights']['DOCUMENT_CREATE'])
            {
                ?><a title="<?php echo $_SESSION['documents'][$documents_id]['new_file']; ?>" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfile('<?php echo ploopi_queryencode("ploopi_op=documents_openfile&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfile_id="); ?>', event);"><img src="<?php echo $_SESSION['documents'][$documents_id]['new_file_img']; ?>"></a><?php
            }
            if ($_SESSION['documents'][$documents_id]['rights']['FOLDER_CREATE'])
            {
                ?><a title="<?php echo $_SESSION['documents'][$documents_id]['new_folder']; ?>" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfolder('<?php echo ploopi_queryencode("ploopi_op=documents_openfolder&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfolder_id="); ?>', event);"><img src="<?php echo $_SESSION['documents'][$documents_id]['new_folder_img']; ?>"></a><?php
            }
            ?>
            <a title="<?php echo $_SESSION['documents'][$documents_id]['root_place']; ?>" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_browser('<?php echo ploopi_queryencode("ploopi_op=documents_browser&documents_id={$documents_id}&mode={$_SESSION['documents'][$documents_id]['mode']}"); ?>', '<?php echo $documents_id; ?>', true);"><img src="<?php echo $_SESSION['documents'][$documents_id]['root_place_img']; ?>"></a>

            <div>Emplacement :</div>
            <?php
            $documentsfolder = new documentsfolder();
            if (!empty($currentfolder) && $documentsfolder->openmd5($currentfolder))
            {
                $db->query("SELECT id, md5id, name, id_folder FROM ploopi_documents_folder WHERE id in ({$documentsfolder->fields['parents']},{$documentsfolder->fields['id']}) ORDER by id");

                while ($row = $db->fetchrow())
                {
                    // change root name
                    $foldername = (!$row['id_folder']) ? $_SESSION['documents'][$documents_id]['root_name'] : $row['name'];
                    ?>
                    <a <?php if ($currentfolder == $row['md5id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:ploopi_documents_browser('<?php echo ploopi_queryencode("ploopi_op=documents_browser&currentfolder={$row['md5id']}&documents_id={$documents_id}&mode={$_SESSION['documents'][$documents_id]['mode']}"); ?>', '<?php echo $_SESSION['documents'][$documents_id]['documents_id']; ?>', true);">
                        <p class="ploopi_va">
                            <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_folder.png" />
                            <span><?php echo $foldername; ?></span>
                        </p>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
        <?php

        $documents_columns = array();

        $documents_columns['auto']['name'] =
            array(
                'label' => 'Nom',
                'options' => array('sort' => true)
            );

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('type', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['type'] =
                array(
                    'label' => 'Type',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['type']) ? 65 : $_SESSION['documents'][$documents_id]['fields_size']['type'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('timestp_modify', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['timestp_modify'] =
                array(
                    'label' => 'Date Modif',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['timestp_modify']) ? 130 : $_SESSION['documents'][$documents_id]['fields_size']['timestp_modify'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('timestp_file', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['timestp_file'] =
                array(
                    'label' => 'Date',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['timestp_file']) ? 80 : $_SESSION['documents'][$documents_id]['fields_size']['timestp_file'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('ref', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['ref'] =
                array(
                    'label' => 'Ref',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['ref']) ? 100 : $_SESSION['documents'][$documents_id]['fields_size']['ref'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('label', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['label'] =
                array(
                    'label' => 'Libell�',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['label']) ? 150 : $_SESSION['documents'][$documents_id]['fields_size']['label'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['fields']) || in_array('size', $_SESSION['documents'][$documents_id]['fields']))
        {
            $documents_columns['right']['size'] =
                array(
                    'label' => 'Taille',
                    'width' => empty($_SESSION['documents'][$documents_id]['fields_size']['size']) ? 90 : $_SESSION['documents'][$documents_id]['fields_size']['size'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents'][$documents_id]['mode']))
            $documents_columns['actions_right']['actions'] =
                array(
                    'label' => 'Actions',
                    'width' => 85
                );

        $documents_values = array();

        // DISPLAY FOLDERS
        $sql =  "
                SELECT      f.*,
                            u.login
                FROM        ploopi_documents_folder f
                LEFT JOIN   ploopi_user u
                ON          f.id_user = u.id
                WHERE       f.id_folder = {$documentsfolder->fields['id']}
                ";

        $db->query($sql);

        $i = 0;
        while ($row = $db->fetchrow())
        {
            $ldate = ploopi_timestamp2local($row['timestp_modify']);

            $documents_values[$i]['values'] =
                array(
                    'name' =>
                        array(
                            'label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/ico_folder".($row['system'] ? '_locked' : '').".png\" /><span>&nbsp;{$row['name']}</span>",
                            'sort_label' => '1_'.$row['name']
                        ),
                    'type' =>
                        array(
                            'label' => 'Dossier'
                        ),
                    'timestp_modify' =>
                         array(
                            'label' => "{$ldate['date']} {$ldate['time']}",
                            'sort_label' => $row['timestp_modify']
                         ),
                    'timestp_file' =>
                        array(
                            'label' => '&nbsp;'
                        ),
                    'ref' =>
                        array(
                            'label' => '&nbsp;'
                        ),
                    'label' =>
                        array(
                            'label' => '&nbsp;',
                        ),
                    'size' =>
                        array(
                            'label' => "{$row['nbelements']} element(s)",
                            'sort_label' => '1_'.$row['nbelements']
                        )
                );

            $actions = '';
            if ($_SESSION['documents'][$documents_id]['rights']['FOLDER_DELETE']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Attention, cette action va supprimer d�finitivement le dossier et son contenu\')) ploopi_documents_deletefolder(\''.ploopi_queryencode("ploopi_op=documents_deletefolder&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfolder_id={$row['md5id']}").'\',\''.$_SESSION['documents'][$documents_id]['documents_id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
            if ($_SESSION['documents'][$documents_id]['rights']['FOLDER_MODIFY']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfolder(\''.ploopi_queryencode("ploopi_op=documents_openfolder&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfolder_id={$row['md5id']}").'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

            if ($actions == '') $actions = '&nbsp;';

            $documents_values[$i]['values']['actions'] =
                array(
                    'label' => (empty($_SESSION['documents'][$documents_id]['mode']) && !$row['system']) ? $actions : '&nbsp;',
                );

            $documents_values[$i]['description'] = '';
            $documents_values[$i]['link'] = 'javascript:void(0);';
            $documents_values[$i]['option'] = 'onclick="javascript:ploopi_documents_browser(\''.ploopi_queryencode("ploopi_op=documents_browser&currentfolder={$row['md5id']}&documents_id={$documents_id}&mode={$_SESSION['documents'][$documents_id]['mode']}").'\',\''.$documents_id.'\',true);"';
            $documents_values[$i]['style'] = '';

            $i++;
        }

        // DISPLAY FILES
        $sql =  "
                SELECT      f.*,
                            u.login,
                            mt.filetype

                FROM        ploopi_documents_file f

                LEFT JOIN   ploopi_user u
                ON          f.id_user = u.id

                LEFT JOIN   ploopi_mimetype mt
                ON          mt.ext = f.extension

                WHERE       f.id_folder = {$documentsfolder->fields['id']}
                ";

        $db->query($sql);

        while ($row = $db->fetchrow())
        {
            $ksize = sprintf("%.02f",$row['size']/1024);
            $ldate = ploopi_timestamp2local($row['timestp_modify']);

            $ldate_file = ($row['timestp_file'] != 0) ? ploopi_timestamp2local($row['timestp_file']) : array('date' => '');

            $ico = (file_exists("{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

            $actions = '';

            if ($_SESSION['documents'][$documents_id]['rights']['DOCUMENT_DELETE']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:if (confirm(\'Attention, cette action va supprimer d�finitivement le fichier\')) ploopi_documents_deletefile(\''.ploopi_queryencode("ploopi_op=documents_deletefile&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfile_id={$row['md5id']}").'\',\''.$_SESSION['documents'][$documents_id]['documents_id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
            if ($_SESSION['documents'][$documents_id]['rights']['DOCUMENT_MODIFY']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfile(\''.ploopi_queryencode("ploopi_op=documents_openfile&currentfolder={$currentfolder}&documents_id={$documents_id}&documentsfile_id={$row['md5id']}").'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

            $documents_values[$i]['values'] =
                array(
                    'name' =>
                        array(
                            'label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/{$ico}\" /><span>&nbsp;{$row['name']}</span>",
                            'sort_label' => '2_'.$row['name']
                        ),
                    'type' =>
                        array(
                            'label' => 'Fichier'
                        ),
                    'timestp_modify' =>
                         array(
                            'label' => "{$ldate['date']} {$ldate['time']}",
                            'sort_label' => $row['timestp_modify']
                         ),
                    'timestp_file' =>
                        array(
                            'label' => $ldate_file['date'],
                            'sort_label' => $row['timestp_file']
                        ),
                    'ref' =>
                        array(
                            'label' => $row['ref'],
                            'sort_label' => $row['ref']
                        ),
                    'label' =>
                        array(
                            'label' => $row['label'],
                            'sort_label' => $row['label']
                        ),
                    'size' =>
                        array(
                            'label' => "{$ksize} ko",
                            'sort_label' => '2_'.$row['size']
                        )
                );

            $documents_values[$i]['values']['actions'] =
                array(
                    'label' => $actions.'<a title="T�l�charger" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['md5id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download.png" /></a>
                                         <a title="T�l�charger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=documents_downloadfile_zip&documentsfile_id={$row['md5id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download_zip.png" /></a>',
                );

            $documents_values[$i]['description'] = '';

            if ($_SESSION['documents'][$documents_id]['mode'] == 'tofield')
            {
                $documents_values[$i]['link'] = 'javascript:void(0);';
                $documents_values[$i]['onclick'] = "javascript:dest = $('{$_SESSION['documents'][$documents_id]['target']}'); if (dest.type) dest.value='{$row['name']}'; else dest.innerHTML='{$row['name']}'; ploopi_getelem('{$_SESSION['documents'][$documents_id]['target']}_id').value='{$row['id']}';ploopi_hidepopup('ploopi_documents_popup');";
            }
            elseif ($_SESSION['documents'][$documents_id]['mode'] == 'tocallback')
            {
                $documents_values[$i]['link'] = 'javascript:void(0);';
                $documents_values[$i]['onclick'] = "javascript:{$_SESSION['documents'][$documents_id]['target']}({$row['id']}, '".addslashes($row['name'])."', '".ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['md5id']}")."');";
            }
            else $documents_values[$i]['link'] = ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['md5id']}&attachement=".$_SESSION['documents'][$documents_id]['attachement']);

            $documents_values[$i]['style'] = '';

            $i++;
        }

        $skin->display_array(
            $documents_columns,
            $documents_values,
            "ploopi_documents_array_{$documents_id}",
            array(
                'sortable' => true,
                'orderby_default' => $_SESSION['documents'][$documents_id]['order_by'],
                'sort_default' => $_SESSION['documents'][$documents_id]['sort'],
                'limit' => $_SESSION['documents'][$documents_id]['limit'],
                'height' => $_SESSION['documents'][$documents_id]['height']
            )
        );

        ?>
    </div>
    <?php
}
