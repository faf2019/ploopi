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
    include_once './include/classes/documents.php';

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
    if (empty($params['FIELDS_SIZE'])) $params['FIELDS_SIZE'] = array();
    if (empty($params['CALLBACK_FUNC'])) $params['CALLBACK_FUNC'] = null;
    if (empty($params['CALLBACK_INC'])) $params['CALLBACK_INC'] = null;
    if (empty($params['DEFAULT_FOLDER'])) $params['DEFAULT_FOLDER'] = '';
    if (empty($params['LIMIT'])) $params['LIMIT'] = 0;

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
            'fields'        => $params['FIELDS'],
            'fields_size'   => $params['FIELDS_SIZE'],
            'callback_func' => $params['CALLBACK_FUNC'],
            'callback_inc' => $params['CALLBACK_INC'],
            'default_folder' => $params['DEFAULT_FOLDER'],
            'limit'         => $params['LIMIT']
        );

    $_SESSION['documents']['rights'] = $rights;

    // on va chercher la racine
    $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = 0 AND id_object = '{$_SESSION['documents']['id_object']}' AND id_module = '{$_SESSION['documents']['id_module']}' AND id_record = '".addslashes($_SESSION['documents']['id_record'])."'");

    if ($row = $db->fetchrow()) $currentfolder = $row['id'];
    else // racine inexistante, il faut la créer
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

    if ($load_doc)
    {
        ?>
        <div id="ploopidocuments_<?php echo $_SESSION['documents']['documents_id']; ?>">
            <?php ploopi_documents_browser($currentfolder); ?>
        </div>
        <?php
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
 * Renvoie un tableau de dossiers attachés à un enregistrement d'un objet
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

        return $documentsfile->save() ? null : $documentsfile->fields['id'];
    }
    else return false;
}

/**
 * Affiche l'explorateur de documents
 *
 * @param int $currentfolder identifiant du dossier parcouru
 */

function ploopi_documents_browser($currentfolder)
{
    global $db;
    global $skin;

    ?>
    <div class="documents_browser">

        <div class="documents_path">
            <?php
            // voir pour une optimisation de cette partie car on ouvre un docfolder sans doute pour rien
            $documentsfolder = new documentsfolder();

            if (!empty($currentfolder)) $documentsfolder->open($currentfolder);

            if ($_SESSION['documents']['rights']['SEARCH'])
            {
                ?>
                <a title="Rechercher un Fichier" href="javascript:void(0);" style="float:right;"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_search.png"></a>
                <?php
            }

            if (empty($_SESSION['documents']['mode']))
            {
                if ($_SESSION['documents']['rights']['DOCUMENT_CREATE'])
                {
                    ?><a title="Créer un nouveau fichier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfile('<?php echo $currentfolder; ?>','',event);"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfile.png"></a><?php
                }
                if ($_SESSION['documents']['rights']['FOLDER_CREATE'])
                {
                    ?>
                    <a title="Créer un nouveau Dossier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfolder('<?php echo $currentfolder; ?>','',event);"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfolder.png"></a>
                    <?php
                }
            }
            ?>
            <a title="Aller au Dossier Racine" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_browser('<?php echo $_SESSION['documents']['documents_id']; ?>', '', '<?php echo $_SESSION['documents']['mode']; ?>','',true);"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_home.png"></a>

            <div>Emplacement :</div>
            <?php
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
                    <a <?php if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:ploopi_documents_browser('<?php echo $_SESSION['documents']['documents_id']; ?>', '<?php echo $row['id']; ?>', '<?php echo $_SESSION['documents']['mode']; ?>','',true);">
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

        if (empty($_SESSION['documents']['fields']) || in_array('type', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['type'] =
                array(
                    'label' => 'Type',
                    'width' => empty($_SESSION['documents']['fields_size']['type']) ? 65 : $_SESSION['documents']['fields_size']['type'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['fields']) || in_array('timestp_modify', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['timestp_modify'] =
                array(
                    'label' => 'Date Modif',
                    'width' => empty($_SESSION['documents']['fields_size']['timestp_modify']) ? 130 : $_SESSION['documents']['fields_size']['timestp_modify'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['fields']) || in_array('timestp_file', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['timestp_file'] =
                array(
                    'label' => 'Date',
                    'width' => empty($_SESSION['documents']['fields_size']['timestp_file']) ? 80 : $_SESSION['documents']['fields_size']['timestp_file'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['fields']) || in_array('ref', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['ref'] =
                array(
                    'label' => 'Ref',
                    'width' => empty($_SESSION['documents']['fields_size']['ref']) ? 100 : $_SESSION['documents']['fields_size']['ref'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['fields']) || in_array('label', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['label'] =
                array(
                    'label' => 'Libellé',
                    'width' => empty($_SESSION['documents']['fields_size']['label']) ? 150 : $_SESSION['documents']['fields_size']['label'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['fields']) || in_array('size', $_SESSION['documents']['fields']))
        {
            $documents_columns['right']['size'] =
                array(
                    'label' => 'Taille',
                    'width' => empty($_SESSION['documents']['fields_size']['size']) ? 90 : $_SESSION['documents']['fields_size']['size'],
                    'options' => array('sort' => true)
                );
        }

        if (empty($_SESSION['documents']['mode']))
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
                WHERE       f.id_folder = {$currentfolder}
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
                            'sort_label' => '1_'.$row['timestp_modify']
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
            if ($_SESSION['documents']['rights']['FOLDER_DELETE']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le dossier et son contenu\')) ploopi_documents_deletefolder(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
            if ($_SESSION['documents']['rights']['FOLDER_MODIFY']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfolder(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

            if ($actions == '') $actions = '&nbsp;';

            $documents_values[$i]['values']['actions'] =
                array(
                    'label' => (empty($_SESSION['documents']['mode']) && !$row['system']) ? $actions : '&nbsp;',
                );

            $documents_values[$i]['description'] = '';
            $documents_values[$i]['link'] = 'javascript:void(0);';
            $documents_values[$i]['option'] = 'onclick="javascript:ploopi_documents_browser(\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\',\''.$_SESSION['documents']['mode'].'\',\'\',true);"';
            $documents_values[$i]['style'] = '';

            $i++;
        }

        // DISPLAY FILES
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
                ";

        $db->query($sql);

        while ($row = $db->fetchrow())
        {
            $ksize = sprintf("%.02f",$row['size']/1024);
            $ldate = ploopi_timestamp2local($row['timestp_modify']);

            $ldate_file = ($row['timestp_file'] != 0) ? ploopi_timestamp2local($row['timestp_file']) : array('date' => '');

            $ico = (file_exists("{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

            $actions = '';

            if ($_SESSION['documents']['rights']['DOCUMENT_DELETE']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le fichier\')) ploopi_documents_deletefile(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
            if ($_SESSION['documents']['rights']['DOCUMENT_MODIFY']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfile(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

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
                            'sort_label' => '2_'.$row['timestp_modify']
                         ),
                    'timestp_file' =>
                        array(
                            'label' => $ldate_file['date'],
                            'sort_label' => '2_'.$row['timestp_file']
                        ),
                    'ref' =>
                        array(
                            'label' => $row['ref'],
                            'sort_label' => '2_'.$row['ref']
                        ),
                    'label' =>
                        array(
                            'label' => $row['label'],
                            'sort_label' => '2_'.$row['label']
                        ),
                    'size' =>
                        array(
                            'label' => "{$ksize} ko",
                            'sort_label' => '2_'.$row['size']
                        )
                );

            $documents_values[$i]['values']['actions'] =
                array(
                    'label' => $actions.'<a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download.png" /></a>
                                         <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=documents_downloadfile_zip&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download_zip.png" /></a>',
                );

            $documents_values[$i]['description'] = '';

            if ($_SESSION['documents']['mode'] == 'selectfile')
            {
                $documents_values[$i]['link'] = 'javascript:void(0);';
                $documents_values[$i]['onclick'] = "javascript:ploopi_getelem('{$_SESSION['documents']['destfield']}').value='{$row['name']}';ploopi_getelem('{$_SESSION['documents']['destfield']}_id').value='{$row['id']}';ploopi_hidepopup('ploopi_documents_popup');";
            }
            else $documents_values[$i]['link'] = ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}&attachement=".$_SESSION['documents']['attachement']);

            $documents_values[$i]['style'] = '';

            $i++;
        }

        $skin->display_array(
            $documents_columns,
            $documents_values,
            'ploopi_documents',
            array(
                'sortable' => true,
                'orderby_default' => 'name',
                'sort_default' => 'ASC',
                'limit' => $_SESSION['documents']['limit']
            )
        );
        ?>
    </div>
    <?php
}
?>
