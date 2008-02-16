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

define ('_DOC_TAB_PARSERS',     1);
define ('_DOC_TAB_INDEX',       2);
define ('_DOC_TAB_STATS',       3);

define ('_DOC_ACTION_ADDFOLDER',    1);
define ('_DOC_ACTION_ADDFILE',      2);
define ('_DOC_ACTION_MODIFYFOLDER', 3);
define ('_DOC_ACTION_MODIFYFILE',   4);
define ('_DOC_ACTION_DELETEFOLDER', 5);
define ('_DOC_ACTION_DELETEFILE',   6);
define ('_DOC_ACTION_WORKFLOW_MANAGE',  7);

define('_DOC_OBJECT_FOLDER',    1);
define('_DOC_OBJECT_FILE',      2);
define('_DOC_OBJECT_FILEDRAFT', 3);

define('_DOC_ERROR_MAXFILESIZE',        100);
define('_DOC_ERROR_FILENOTWRITABLE',    101);
define('_DOC_ERROR_EMPTYFILE',          102);


function doc_guid()
{
    $workid = strtoupper(md5(session_id().md5(getmypid().uniqid(rand(),true).$_SERVER['SERVER_NAME'])));

    // hopefully conform to the spec, mark this as a random type
    // lets handle the version byte as a number
    $byte = hexdec( substr($workid,12,2) );
    $byte = $byte & hexdec('0f');
    $byte = $byte | hexdec('40');
    $workid = substr_replace($workid, strtoupper(dechex($byte)), 12, 2);

    // hopefully conform to the spec, mark this common variant
    // lets handle the variant
    $byte = hexdec( substr($workid,16,2) );
    $byte = $byte & hexdec('3f');
    $byte = $byte | hexdec('80');
    $workid = substr_replace($workid, strtoupper(dechex($byte)), 16, 2);

    // build a human readable version
    $wid = substr($workid, 0, 8).'-'
    .substr($workid, 8, 4).'-'
    .substr($workid,12, 4).'-'
    .substr($workid,16, 4).'-'
    .substr($workid,20,12);

    return $wid;
}

function doc_getpath($id_module = -1, $createpath = false)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $path = _PLOOPI_PATHDATA._PLOOPI_SEP."doc-{$id_module}";

    if ($createpath)
    {
        // test for existing _PLOOPI_PATHDATA path
        if (!is_dir(_PLOOPI_PATHDATA)) mkdir(_PLOOPI_PATHDATA);

        if ($path != '' && !is_dir($path)) mkdir($path);
    }

    return($path);
}

function doc_countelements($id_folder)
{
    global $db;

    $c = 0;

    $db->query("SELECT count(id) as c FROM ploopi_mod_doc_folder WHERE id_folder = {$id_folder}");
    if ($row = $db->fetchrow()) $c += $row['c'];

    $db->query("SELECT count(id) as c FROM ploopi_mod_doc_file WHERE id_folder = {$id_folder}");
    if ($row = $db->fetchrow()) $c += $row['c'];

    return($c);
}

function doc_getshares($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    if (empty($_SESSION['doc'][$id_module]['shares']))
    {
        $_SESSION['doc'][$id_module]['shares'] = array('folders' => array(), 'files' => array());

        foreach(ploopi_shares_get($_SESSION['ploopi']['userid'], -1, -1, $id_module) as $sh)
        {
            if ($sh['id_object'] == _DOC_OBJECT_FOLDER) $_SESSION['doc'][$id_module]['shares']['folders'][] = $sh['id_record'];
            if ($sh['id_object'] == _DOC_OBJECT_FILE) $_SESSION['doc'][$id_module]['shares']['files'][] = $sh['id_record'];
        }
    }
}

function doc_resetshares($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    unset($_SESSION['doc'][$id_module]['shares']);
}

function doc_getworkflow($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    if (empty($_SESSION['doc'][$id_module]['workflow']))
    {
        $_SESSION['doc'][$id_module]['workflow'] = array('folders' => array());
        foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, -1, -1, $_SESSION['ploopi']['userid']) as $wf)
        {
            $_SESSION['doc'][$id_module]['workflow']['folders'][] = $wf['id_record'];
        }
    }
}

function doc_resetworkflow($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    unset($_SESSION['doc'][$id_module]['workflow']);
}


function doc_max_filesize()
{
    $ploopi_maxfilesize = sprintf('%.02f', _PLOOPI_MAXFILESIZE/1024);

    if (_PLOOPI_USE_CGIUPLOAD) return($ploopi_maxfilesize);
    else
    {
        $upload_max_filesize =  intval(ini_get('upload_max_filesize')*1024);
        $post_max_size = intval(ini_get('post_max_size')*1024);
        return(min($upload_max_filesize, $post_max_size, $ploopi_maxfilesize));
    }
}

function doc_max_formsize()
{
    if (_PLOOPI_USE_CGIUPLOAD) return(0);
    else return(intval(ini_get('post_max_size')*1024));
}


function doc_record_isenabled($id_object, $id_record, $id_module)
{
    $enabled = false;

    switch($id_object)
    {
        case _DOC_OBJECT_FILE;
            include_once './modules/doc/class_docfile.php';
            include_once './modules/doc/class_docfolder.php';

            $objFile = new docfile();
            if ($objFile->openmd5($id_record))
            {
                //ploopi_print_r($objFile);

                if ($objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
                else
                {
                    $objFolder = new docfolder();
                    if ($objFolder->open($objFile->fields['id_folder']))
                    {
                        if ($objFolder->fields['foldertype'] == 'public') $enabled = true;
                        else
                        {
                            doc_getshares($id_module);
                            if (in_array($objFile->fields['id_folder'], $_SESSION['doc'][$id_module]['shares']['folders'])) $enabled = true;
                        }
                    }
                }
            }
        break;
    }

    return($enabled);
}
?>
