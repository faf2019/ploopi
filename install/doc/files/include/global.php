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
 * Fonctions, constantes, variables globales
 *
 * @package doc
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_DOC_TAB_PARSERS',     1);
define ('_DOC_TAB_INDEX',       2);
define ('_DOC_TAB_CLEAN_CACHE', 3);
define ('_DOC_TAB_STATS',       4);


define ('_DOC_ACTION_ADDFOLDER',    1);
define ('_DOC_ACTION_ADDFILE',      2);
define ('_DOC_ACTION_MODIFYFOLDER', 3);
define ('_DOC_ACTION_MODIFYFILE',   4);
define ('_DOC_ACTION_DELETEFOLDER', 5);
define ('_DOC_ACTION_DELETEFILE',   6);
define ('_DOC_ACTION_WORKFLOW_MANAGE',  7);
define ('_DOC_ACTION_ADMIN',  99);

define('_DOC_OBJECT_FOLDER',    1);
define('_DOC_OBJECT_FILE',      2);
define('_DOC_OBJECT_FILEDRAFT', 3);

define('_DOC_ERROR_MAXFILESIZE',        100);
define('_DOC_ERROR_FILENOTWRITABLE',    101);
define('_DOC_ERROR_EMPTYFILE',          102);

global $doc_arrDocViewableFormats;

$doc_arrDocViewableFormats =
    array(
        'pdf'   => 'iframe',
        'doc'   => 'iframe',
        'xls'   => 'iframe',
        'ppt'   => 'iframe',
        'odt'   => 'iframe',
        'ods'   => 'iframe',
        'odp'   => 'iframe',
        'csv'   => 'div',
        'txt'   => 'div',
        'svg'   => 'highlighter:xml',
        'xml'   => 'highlighter:xml',
        'dtd'   => 'highlighter:dtd',
        'css'   => 'highlighter:css',
        'php'   => 'highlighter:php',
        'py'    => 'highlighter:python',
        'pl'    => 'highlighter:perl',
        'c'     => 'highlighter:cpp',
        'cpp'   => 'highlighter:cpp',
        'java'  => 'highlighter:java',
        'js'    => 'highlighter:javascript',
        'vbs'   => 'highlighter:vbscript',
        'rb'    => 'highlighter:ruby',
        'diff'  => 'highlighter:diff',
        'bat'   => 'div',
        'sh'    => 'highlighter:sh',
        'mysql' => 'highlighter:mysql',
        'sql'   => 'highlighter:sql',
        'html'  => 'iframe',
        'htm'   => 'iframe',
        'jpg'   => 'iframe',
        'jpeg'  => 'iframe',
        'gif'   => 'iframe',
        'png'   => 'iframe',
        'flv'   => 'jw_player',
        'aac'   => 'jw_player',
        'mp3'   => 'jw_player',
        'mp4'   => 'jw_player',
        'ogv'   => 'video',
        'ogg'   => 'video',
        'mpg'   => 'video',
        'mpeg'  => 'video',
        'mov'   => 'video',
        'avi'   => 'video'
    );

/**
 * Retourne un identifiant unique pour l'upload des fichiers
 *
 * @return string guid
 */

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

/**
 * Retourne le chemin de stockage des fichiers
 *
 * @param int $id_module identifiant du module
 * @param unknown_type $createpath
 * @return unknown
 */

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

/**
 * Retourne le nombre d'éléments (fichiers/dossiers) d'un dossier
 *
 * @param int $id_folder identifiant du dossier
 * @return int nombre d'éléments
 */

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
/**
 * Chargement des partages en session (pour éviter les multiples rechargements)
 *
 * @param int $id_module identifiant du module
 *
 * @see ploopi_share_get
 * @see _DOC_OBJECT_FOLDER
 * @see _DOC_OBJECT_FILE
 */

function doc_getshare($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    if (empty($_SESSION['doc'][$id_module]['share']))
    {
        $_SESSION['doc'][$id_module]['share'] = array('folders' => array(), 'files' => array());

        $objUser = new user();
        if ($objUser->open($_SESSION['ploopi']['userid']))
        {
            $arrGroups = array_keys($objUser->getgroups(true));

            foreach(ploopi_share_get(-1, -1, -1, $id_module) as $sh)
            {
                if (($sh['type_share'] == 'user' && $sh['id_share'] == $_SESSION['ploopi']['userid']) || ($sh['type_share'] == 'group' && in_array($sh['id_share'], $arrGroups)))
                {
                    if ($sh['id_object'] == _DOC_OBJECT_FOLDER) $_SESSION['doc'][$id_module]['share']['folders'][] = $sh['id_record'];
                    if ($sh['id_object'] == _DOC_OBJECT_FILE) $_SESSION['doc'][$id_module]['share']['files'][] = $sh['id_record'];
                }

            }
        }
    }
}

/**
 * Supprime le chargement des partages de la session
 *
 * @param int $id_module identifiant du module
 */

function doc_resetshare($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    unset($_SESSION['doc'][$id_module]['share']);
}

/**
 * Chargement des droits de validation en session (pour éviter les multiples rechargements)
 *
 * @param int $id_module identifiant du module
 *
 * @see ploopi_validation_get
 * @see _DOC_OBJECT_FOLDER
 */

function doc_getvalidation($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];
    
    if (empty($_SESSION['doc'][$id_module]['validation']))
    {
        $_SESSION['doc'][$id_module]['validation'] = array('folders' => array());
        
        $objUser = new user();
        if ($objUser->open($_SESSION['ploopi']['userid']))
        {
            $arrGroups = array_keys($objUser->getgroups(true));

            foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, '', $id_module) as $wf)
            {
                if (($wf['type_validation'] == 'user' && $wf['id_validation'] == $_SESSION['ploopi']['userid']) || ($wf['type_validation'] == 'group' && in_array($wf['id_validation'], $arrGroups))) $_SESSION['doc'][$id_module]['validation']['folders'][$wf['id_record']] = $wf['id_record'];
            }
        } 
    }
}

/**
 * Supprime le chargement du validation de la session
 *
 * @param int $id_module identifiant du module
 */

function doc_resetvalidation($id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    unset($_SESSION['doc'][$id_module]['validation']);
}

/**
 * Retourne la taille maximale d'un fichier uploadable
 *
 * @return int taille maximale en ko
 *
 * @see _PLOOPI_MAXFILESIZE
 * @see _PLOOPI_USE_CGIUPLOAD
 */
function doc_max_filesize()
{
    $ploopi_maxfilesize = sprintf('%d', _PLOOPI_MAXFILESIZE/1024);

    if (_PLOOPI_USE_CGIUPLOAD) return($ploopi_maxfilesize);
    else
    {
        $upload_max_filesize =  intval(ini_get('upload_max_filesize')*1024);
        $post_max_size = intval(ini_get('post_max_size')*1024);
        return(min($upload_max_filesize, $post_max_size, $ploopi_maxfilesize));
    }
}

/**
 * Retourne la taille maximale qu'un formulaire peut accepter en POST
 *
 * @return int taille maximale en ko
 *
 * @see _PLOOPI_USE_CGIUPLOAD
 */

function doc_max_formsize()
{
    if (_PLOOPI_USE_CGIUPLOAD) return(0);
    else return(intval(ini_get('post_max_size')*1024));
}

/**
 * Vérifie qu'un enregistrement d'un objet est accessible dans un certain contexte par un utilisateur
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @return boolean true si l'enregistrement est accessible
 *
 * @see _DOC_OBJECT_FOLDER
 * @see _DOC_OBJECT_FILE
 * @see _DOC_OBJECT_FILEDRAFT
 *
 * @see doc_getshare
 * @see doc_getvalidation
 */

function doc_record_isenabled($id_object, $id_record, $id_module)
{
    $enabled = false;

    if (ploopi_isadmin()) return true;

    switch($id_object)
    {
        case _DOC_OBJECT_FOLDER;
            include_once './modules/doc/class_docfolder.php';

            $objFolder = new docfolder();
            if ($objFolder->open($id_record))
            {
                if ($objFolder->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
                else
                {
                    if ($objFolder->fields['foldertype'] == 'public' && in_array($objFolder->fields['id_workspace'], explode(',', ploopi_viewworkspaces()))) $enabled = true;
                    else
                    {
                        doc_getshare($id_module);
                        if (in_array($id_record, $_SESSION['doc'][$id_module]['share']['folders'])) $enabled = true;
                    }
                }
            }
        break;

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
                        if ($objFolder->fields['foldertype'] == 'public' && in_array($objFolder->fields['id_workspace'], explode(',', ploopi_viewworkspaces()))) $enabled = true;
                        else
                        {
                            doc_getshare($id_module);
                            if (in_array($objFile->fields['id_folder'], $_SESSION['doc'][$id_module]['share']['folders'])) $enabled = true;
                        }
                    }
                }
            }
        break;

        case _DOC_OBJECT_FILEDRAFT:
            include_once './modules/doc/class_docfiledraft.php';
            include_once './modules/doc/class_docfolder.php';

            // ok si propriétaire du fichier ou validateur du dossier
            $objFile = new docfiledraft();
            if ($objFile->openmd5($id_record))
            {
                if ($objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
                else
                {
                    $objFolder = new docfolder();
                    if ($objFolder->open($objFile->fields['id_folder']))
                    {
                        doc_getvalidation($objFile->fields['id_module']);
                        $enabled = in_array($objFile->fields['id_folder'], $_SESSION['doc'][$objFile->fields['id_module']]['validation']['folders']);
                    }
                }
            }
        break;
    }

    return($enabled);
}

/**
 * Retourne la liste complète des dossiers sous forme d'un tableau
 *
 * @param int $id_module identifiant du module (optionnel)
 * @param int $id_user identifiant de l'utilisateur (optionnel)
 * @return array tableau des dossiers
 */
function doc_getfolders($id_module = -1, $id_user = -1)
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];
    if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];

    $arrFolders =
        array(
            'list' => array(),
            'tree' => array()
        );

    /**
     * Charge les validations
     */

    doc_getvalidation();

    /**
     * Charge les partages
     */

    doc_getshare();

    // dossiers partagés
    $list_shared_folders = (!empty($_SESSION['doc'][$id_module]['share']['folders'])) ? ' OR (f.id IN ('.implode(',', $_SESSION['doc'][$id_module]['share']['folders']).") AND f.id_user <> {$id_user})" : '';

    // dossiers dont l'utilisateur connecté est le validateur
    $list_wf_folders = (!empty($_SESSION['doc'][$id_module]['validation']['folders'])) ? implode(',', $_SESSION['doc'][$id_module]['validation']['folders']) : '';
    $list_wf_folders_option = ($list_wf_folders != '') ? " OR f_val.id_folder IN ({$list_wf_folders}) " : '';

    $result = $db->query("
        SELECT      f.id,
                    f.name,
                    f.foldertype,
                    f.readonly,
                    f.readonly_content,
                    f.parents,
                    f.id_user,
                    f.published,
                    f.waiting_validation,
                    f.id_folder

        FROM        ploopi_mod_doc_folder f

        LEFT JOIN   ploopi_mod_doc_folder f_val
        ON          f_val.id = f.waiting_validation

        WHERE       f.id_module = {$id_module}
        AND         f.published = 1
        AND         (f.waiting_validation = 0 OR f.id_user = {$id_user} {$list_wf_folders_option})

        AND         (
                        (f.id_user = {$id_user})
                    OR  (f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces().") AND f.id_user <> {$id_user})
                    {$list_shared_folders}
                    )

        ORDER by f.name
    ");

    $arrFolders['list'][0] = array(
        'id' => 0,
        'name' => 'Racine',
        'foldertype' => 'private',
        'readonly' => 0,
        'readonly_content' => 0,
        'parents' => '',
        'published' => 1,
        'waiting_validation' => 0,
        'id_folder' => -1
    );

    $arrFolders['tree'][-1][] = 0;

    while ($fields = $db->fetchrow($result))
    {
        $fields['parents'] = '-1;'.str_replace(',', ';', $fields['parents']);

        $arrFolders['list'][$fields['id']] = $fields;
        $arrFolders['tree'][$fields['id_folder']][] = $fields['id'];
    }

    return($arrFolders);
}

/**
 * Retourne le treeview de navigation dans les dossiers
 *
 * @param array $arrFolder tableau contenant les dossiers
 * @return array tableau contenant la description du treeview
 */
function doc_gettreeview($arrFolder = array())
{
    global $db;

    $arrTreeview =
        array(
            'list' => array(),
            'tree' => array()
        );

    foreach($arrFolder['list'] as $id => $fields)
    {
        $strIco = 'ico_folder';
        if ($fields['foldertype'] == 'shared') $strIco .= '_shared';
        if ($fields['foldertype'] == 'public') $strIco .= '_public';
        if ($fields['readonly']) $strIco .= '_locked';

        $arrTreeview['list'][$fields['id']] =
            array(
                'id' => $fields['id'],
                'label' => $fields['name'],
                'description' => $fields['name'],
                'parents' => preg_split('/;/', $fields['parents']),
                'node_link' => '',
                'node_onclick' => "ploopi_skin_treeview_shownode('{$fields['id']}', '".ploopi_queryencode("ploopi_op=doc_folder_detail&doc_folder_id={$fields['id']}")."', 'admin-light.php');",
                'link' => ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$fields['id']}"),
                'onclick' => '',
                'icon' => "./modules/doc/img/{$strIco}.png"
            );

        $arrTreeview['tree'][$fields['id_folder']][] = $fields['id'];
    }

    return($arrTreeview);
}

/**
 * Retourne un tableau contenant les règles de réécriture proposées par le module DOC
 *
 * @return array tableau contenant les règles de réécriture
 */
function doc_getrewriterules()
{
    return array(
        'patterns' => array(
            '/[a-z\-]*.php\?ploopi_op=doc_file_download&docfile_md5id=([a-z0-9]{32})/',
            '/[a-z\-]*.php\?ploopi_op=doc_file_view&docfile_md5id=([a-z0-9]{32})/',
            // Flux RSS/Atom
            '/backend.php\?format=(rss|atom)&ploopi_moduleid=([0-9]*)&id_folder=([0-9]*)/'
        ),

        'replacements' => array(
            'documents/$1/<TITLE>.<EXT>',
            'media/$1/<TITLE>.<EXT>',
            // Flux RSS/Atom
            'doc/$1/<TITLE>-m$2f$3.<EXT>'
        )
    );
}

/**
 * Affichage des dossiers dans l'explorateur de fichiers appelé depuis FCK Editor
 *
 * @param array $arrFolders tableau contenant les dossiers à afficher
 * @param int $intIdFolder Identifiant du dossier à afficher
 * @param string $strPath Chemin complet du dossier
 */
function doc_fckexplorer_displayfolders(&$arrFolders, $intIdFolder = 0, $strPath = ' ')
{
    if (isset($arrFolders['tree'][$intIdFolder]))
    {
        foreach($arrFolders['tree'][$intIdFolder] as $intIdChild)
        {
            ?>
            <option value="<?php echo $intIdChild; ?>" label="<?php echo $arrFolders['list'][$intIdChild]['name']; ?>"><?php echo htmlentities("{$strPath} / {$arrFolders['list'][$intIdChild]['name']}"); ?></option>
            <?php
            doc_fckexplorer_displayfolders($arrFolders, $intIdChild, "{$strPath} / {$arrFolders['list'][$intIdChild]['name']}");
        }
    }
}
?>
