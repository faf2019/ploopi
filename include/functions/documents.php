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

function ploopi_documents_getid($id_object, $id_record, $id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    return base64_encode("{$id_module}_{$id_object}_".addslashes($id_record));
}

function ploopi_documents($id_object, $id_record, $rights = array(), $default_folders = array(), $params = array(), $id_user = -1, $id_workspace = -1, $id_module = -1)
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
    //base64_encode("{$id_module}_{$id_object}_".addslashes($id_record));

    if (empty($rights)) $rights['DOCUMENT_CREATE'] = $rights['DOCUMENT_MODIFY'] = $rights['DOCUMENT_DELETE'] = $rights['FOLDER_CREATE'] = $rights['FOLDER_MODIFY'] = $rights['FOLDER_DELETE'] = true;

    if (empty($params['ROOT_NAME'])) $params['ROOT_NAME'] = 'Racine';
    if (empty($params['ATTACHEMENT'])) $params['ATTACHEMENT'] = true;
    if (empty($params['FIELDS'])) $params['FIELDS'] = array();

    $_SESSION['documents'] = array (    'id_object'     => $id_object,
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

    include_once './include/classes/class_documentsfolder.php';

    // on va chercher la racine
    $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = 0 and id_object = '{$_SESSION['documents']['id_object']}' and id_record = '".addslashes($_SESSION['documents']['id_record'])."'");

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
    ?>
    <div id="ploopidocuments_<? echo $documents_id; ?>">
    </div>
    <script type="text/javascript">
        /*
        function ploopi_documents_browser<? echo $ploopi_documents_idinstance; ?>_onload()
        {
            ploopi_documents_browser('','<? echo $documents_id; ?>','', '', true);
        }
        ploopi_window_onload_stock(ploopi_documents_browser<? echo $ploopi_documents_idinstance; ?>_onload);
        */
        ploopi_documents_browser('','<? echo $documents_id; ?>','', '', true);
    </script>
    <?
}

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

function ploopi_documents_getfiles($id_object, $id_record, $id_folder = 0)
{
    global $db;

    $files = array();

    if ($id_folder == 0) $db->query("SELECT * FROM ploopi_documents_file WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY name");
    else $db->query("SELECT * FROM ploopi_documents_file WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' and id_folder = {$id_folder} ORDER BY name");
    while ($row = $db->fetchrow()) $files[] = $row;

    return($files);
}

function ploopi_documents_getfolders($id_object, $id_record)
{
    global $db;

    $folders = array();

    $db->query("SELECT * FROM ploopi_documents_folder WHERE id_object = '{$id_object}' AND id_record = '{$id_record}' ORDER BY name");

    while ($row = $db->fetchrow())
    {
        $folders[$row['id_folder']][] = $row;
    }

    return($folders);
}
?>
