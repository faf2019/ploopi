<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
define ('_DOC_ACTION_WEBSERVICE',   8);
define ('_DOC_ACTION_ADMIN',        9);

define('_DOC_OBJECT_FOLDER',    1);
define('_DOC_OBJECT_FILE',      2);
define('_DOC_OBJECT_FILEDRAFT', 3);

define('_DOC_ERROR_MAXFILESIZE',        100);
define('_DOC_ERROR_FILENOTWRITABLE',    101);
define('_DOC_ERROR_EMPTYFILE',          102);

global $doc_arrDocViewableFormats;

// http://highlightjs.readthedocs.io/en/latest/css-classes-reference.html
$doc_arrDocViewableFormats =
    array(
        'pdf'   => 'iframe',
        'doc'   => 'iframe:unoconv',
        'odt'   => 'iframe:unoconv',
        'rtf'   => 'iframe:unoconv',
        'sxw'   => 'iframe:unoconv',
        'ltx'   => 'iframe:unoconv', // LaTeX
        'sdw'   => 'iframe:unoconv', // StarWriter

        'ppt'   => 'iframe:unoconv',
        'odp'   => 'iframe:unoconv',

        'xls'   => 'iframe:unoconv',
        'ods'   => 'iframe:unoconv',
        'sdc'   => 'iframe:unoconv',

        'odd'   => 'iframe:unoconv',
        'odg'   => 'iframe:unoconv',
        'eps'   => 'iframe:unoconv',
        'emf'   => 'iframe:unoconv',
        'tiff'  => 'iframe:unoconv',
        'tif'   => 'iframe:unoconv',
        'wmf'   => 'iframe:unoconv',
        'bmp'   => 'iframe:unoconv',

        'csv'   => 'div',
        'txt'   => 'div',
        'conf'  => 'highlighter:apache',
        'bat'   => 'highlighter:bat',
        'cmd'   => 'highlighter:cmd',
        'lua'   => 'highlighter:lua',
        'xml'   => 'highlighter:xml',
        'dtd'   => 'highlighter:dtd',
        'css'   => 'highlighter:css',
        'cs'    => 'highlighter:cs',
        'php'   => 'highlighter:php',
        'py'    => 'highlighter:py',
        'pl'    => 'highlighter:pl',
        'basic' => 'highlighter:basic',
        'c'     => 'highlighter:c',
        'cpp'   => 'highlighter:cpp',
        'php'   => 'highlighter:php',
        'php3'  => 'highlighter:php',
        'php4'  => 'highlighter:php',
        'php5'  => 'highlighter:php',
        'php7'  => 'highlighter:php',
        'java'  => 'highlighter:java',
        'jsp'   => 'highlighter:jsp',
        'js'    => 'highlighter:js',
        'json'  => 'highlighter:json',
        'vbs'   => 'highlighter:vbs',
        'rb'    => 'highlighter:rb',
        'rs'    => 'highlighter:rs',
        'diff'  => 'highlighter:diff',
        'patch' => 'highlighter:patch',
        'html'  => 'highlighter:html',
        'htm'   => 'highlighter:html',
        'rss'   => 'highlighter:rss',
        'bat'   => 'div',
        'sh'    => 'highlighter:sh',
        'bash'  => 'highlighter:sh',
        'mysql' => 'highlighter:sql',
        'sql'   => 'highlighter:sql',
        'md'    => 'highlighter:md',
        'jpg'   => 'iframe',
        'jpeg'  => 'iframe',
        'gif'   => 'iframe',
        'png'   => 'iframe',
        'svg'   => 'iframe',
        'aac'   => 'video',
        'mp3'   => 'video',
        'wav'   => 'video',
        'mp4'   => 'video',
        'h264'  => 'video',
        'h265'  => 'video',
        'webm'  => 'video',
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
    $db = ploopi\db::get();

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

        $objUser = new ploopi\user();
        if ($objUser->open($_SESSION['ploopi']['userid']))
        {
            $arrGroups = array_keys($objUser->getgroups(true));

            foreach(ploopi\share::get(-1, -1, -1, $id_module) as $sh)
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

        $objUser = new ploopi\user();
        if ($objUser->open($_SESSION['ploopi']['userid']))
        {
            $arrGroups = array_keys($objUser->getgroups(true));

            foreach(ploopi\validation::get(_DOC_OBJECT_FOLDER, '', $id_module) as $wf)
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
 */
function doc_max_filesize()
{
    $ploopi_maxfilesize = sprintf('%d', _PLOOPI_MAXFILESIZE/1024);

    $upload_max_filesize =  intval(doc_return_bytes(ini_get('upload_max_filesize')), 10);
    $post_max_size = intval(doc_return_bytes(ini_get('post_max_size')), 10);
    return(min($upload_max_filesize, $post_max_size, $ploopi_maxfilesize));
}

/**
 * Retourne la taille maximale qu'un formulaire peut accepter en POST
 *
 * @return int taille maximale en ko
 */

function doc_max_formsize()
{
    return intval(doc_return_bytes(ini_get('post_max_size')), 10);
}


function doc_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    if (!empty($last)) $val = substr($val, 0, -1);
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
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

    if (ploopi\acl::isadmin()) return true;

    switch($id_object)
    {
        case _DOC_OBJECT_FOLDER;
            include_once './modules/doc/class_docfolder.php';

            $objFolder = new docfolder();
            if ($objFolder->open($id_record))
            {
                if (ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, $objFolder->fields['id_workspace'], $id_module) || $objFolder->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
                else
                {
                    if ($objFolder->fields['foldertype'] == 'public' && in_array($objFolder->fields['id_workspace'], explode(',', ploopi\system::viewworkspaces()))) $enabled = true;
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
                //ploopi\output::print_r($objFile);

                if (ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, $objFile->fields['id_workspace'], $id_module) || $objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
                else
                {
                    $objFolder = new docfolder();
                    if ($objFolder->open($objFile->fields['id_folder']))
                    {
                        if ($objFolder->fields['foldertype'] == 'public' && in_array($objFolder->fields['id_workspace'], explode(',', ploopi\system::viewworkspaces()))) $enabled = true;
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
                if (ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, $objFile->fields['id_workspace'], $id_module) || $objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
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
 * en prenant compte la vue de l'utilisateur (partages, etc.)
 *
 * @return array tableau des dossiers
 */
function doc_getfolders()
{
    $db = ploopi\db::get();

    $id_module = $_SESSION['ploopi']['moduleid'];
    $id_user = $_SESSION['ploopi']['userid'];

    $arrFolders =
        array(
            'list' => array(),
            'tree' => array()
        );

    // Charge les validations
    doc_getvalidation();

    // Charge les partages
    doc_getshare();

    $arrWhere = array();

    // Module
    $arrWhere['module'] = "f.id_module = {$id_module}";

    // Utilisateur "standard"
    if (!ploopi\acl::isadmin() && !ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN))
    {
        // Publié (ou propriétaire)
        $arrWhere['published'] = "(f.published = 1 OR f.id_user = {$id_user})";

        // Prioriétaire
        $arrWhere['visibility']['user'] = "f.id_user = {$id_user}";

        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders'])) $arrWhere['visibility']['wf'] = "f.id_folder IN (".implode(', ', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']).")";


        // Partagé
        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) $arrWhere['visibility']['shared'] = "(f.foldertype = 'shared' AND f.id IN (".implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])."))";
        // Public
        $arrWhere['visibility']['public'] = "(f.foldertype = 'public' AND f.id_workspace IN (".ploopi\system::viewworkspaces()."))";

        // Synthèse visibilité
        $arrWhere['visibility'] = '('.implode(' OR ', $arrWhere['visibility']).')';
    }

    $strWhere = implode(' AND ', $arrWhere);

    $result = $db->query("
        SELECT      f.id,
                    f.name,
                    f.foldertype,
                    f.readonly,
                    f.parents,
                    f.id_user,
                    f.published,
                    f.waiting_validation,
                    f.id_folder,
                    u.id as user_id,
                    u.login as user_login,
                    u.lastname as user_lastname,
                    u.firstname as user_firstname,
                    w.id as workspace_id,
                    w.label as workspace_label

        FROM        ploopi_mod_doc_folder f

        LEFT JOIN   ploopi_user u
        ON          f.id_user = u.id

        LEFT JOIN   ploopi_workspace w
        ON          f.id_workspace = w.id

        LEFT JOIN   ploopi_mod_doc_folder f_val
        ON          f_val.id = f.waiting_validation

        WHERE  {$strWhere}

        ORDER by f.name
    ");


    $arrFolders['list'][0] = array(
        'id' => 0,
        'name' => 'Racine',
        'foldertype' => 'private',
        'readonly' => 0,
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

    return $arrFolders;
}

/**
 * Retourne le treeview de navigation dans les dossiers
 *
 * @param array $arrFolder tableau contenant les dossiers
 * @return array tableau contenant la description du treeview
 */
function doc_gettreeview($arrFolders = array(), $strPrefix = '', $arrExcludes = null)
{
    $db = ploopi\db::get();

    $arrTreeview = array(
        'list' => array(),
        'tree' => array()
    );

    $strExcludes = isset($arrExcludes) ? implode(',', $arrExcludes) : '';

    foreach($arrFolders['list'] as $id => $fields)
    {
        if (empty($arrExcludes) || !in_array($id, $arrExcludes))
        {
            $strIco = 'ico_folder';
            $strClass = '';

            if ($fields['foldertype'] == 'shared') $strIco .= '_shared';
            if ($fields['foldertype'] == 'public') $strIco .= '_public';
            if ($fields['readonly']) $strIco .= '_locked';
            if (!$fields['published']) $strClass = 'doc_draft';

            // Mode standard, arbre de navigation
            if ($strPrefix == '')
            {
                $strId = $fields['id'];
                $strparents = preg_split('/;/', $fields['parents']);
                $strLink = ploopi\crypt::urlencode("admin.php?op=doc_browser&currentfolder={$fields['id']}");
                $strOnClick = '';
            }
            // Arbre de sélection
            else
            {
                // On ne propose que les dossiers qui ne sont pas en lecture seule

                $strId = $strPrefix.$fields['id'];
                $strparents = preg_split('/;/', $strPrefix.str_replace(';', ";{$strPrefix}", $fields['parents']));
                $strLink = 'javascript:void(0);';
                if ($id == 0 || !doc_folder_contentisreadonly($arrFolders['list'][$id]))
                {
                    $strOnClick = "jQuery('#docfolder_id_folder')[0].value='{$fields['id']}'; jQuery('#docfolder_id_folder_name')[0].innerHTML='".addslashes(ploopi\str::htmlentities($fields['name']))."'; ploopi.popup.hide('doc_popup_folderselect');";
                }
                else
                {
                    $strOnClick = "alert('Vous ne disposez pas des autorisations requises');";
                }
            }

            $arrTreeview['list'][$strId] = array(
                'id' => $strId,
                'label' => $fields['name'],
                'description' => $fields['name'],
                'parents' => preg_split('/;/', $strPrefix.str_replace(';', ";{$strPrefix}", $fields['parents'])),
                'node_link' => '',
                'node_onclick' => "ploopi_skin_treeview_shownode('{$strId}', '".ploopi\crypt::queryencode("ploopi_op=doc_folder_detail&doc_folder_id={$fields['id']}&doc_prefix={$strPrefix}&doc_excludes={$strExcludes}")."', 'admin-light.php');",
                'link' => $strLink,
                'onclick' => $strOnClick,
                'icon' => "./modules/doc/img/{$strIco}.png",
                'class' => $strClass
            );


            $arrTreeview['tree'][$strPrefix.$fields['id_folder']][] = $strPrefix.$fields['id'];
        }
    }

    return($arrTreeview);
}

/**
 * Retourne un tableau contenant les règles de réécriture proposées par le module DOC
 *
 * @return array tableau contenant les règles de réécriture
 */
function doc_getrewriterules($inline = false)
{
    return array(
        'patterns' => array(
            '/[a-z\-]*.php\?ploopi_op=doc_file_download&docfile_md5id=([a-z0-9]{32})/',
            '/[a-z\-]*.php\?ploopi_op=doc_file_view&docfile_md5id=([a-z0-9]{32})/',
            // Flux RSS/Atom
            '/backend.php\?format=(rss|atom)&ploopi_moduleid=([0-9]*)&id_folder=([0-9]*)/'
        ),

        'replacements' => array(
            $inline ? 'inlinedocs/$1/<TITLE>.<EXT>' : 'documents/$1/<TITLE>.<EXT>',
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
            <option value="<?php echo $intIdChild; ?>" label="<?php echo ploopi\str::htmlentities($arrFolders['list'][$intIdChild]['name']); ?>"><?php echo ploopi\str::htmlentities("{$strPath} / {$arrFolders['list'][$intIdChild]['name']}"); ?></option>
            <?php
            doc_fckexplorer_displayfolders($arrFolders, $intIdChild, "{$strPath} / {$arrFolders['list'][$intIdChild]['name']}");
        }
    }
}

/**
 * Retourne le moteur de rendu du format de document
 */
function doc_getrenderer($strExtension)
{
    global $doc_arrDocViewableFormats;
    $strExtension = strtolower($strExtension);

    return (in_array($strExtension, array_keys($doc_arrDocViewableFormats))) ? explode(':', $doc_arrDocViewableFormats[$strExtension]) : array('iframe');
}

/**
 * Retourne true si le dossier n'est pas modifiable
 * Modifiable par l'admin sys, le rôle admin, le propriétaire, accessible + role sup
 */
function doc_folder_isreadonly($row, $action = null)
{
    $booActionIsOk = is_null($action) || ploopi\acl::isactionallowed($action);

    return !(ploopi\acl::isadmin() || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN) || $row['id_user'] == $_SESSION['ploopi']['userid'] || (doc_folder_isenabled($row) && $booActionIsOk));
}

/**
 * Retourne true si le dossier est accessible dans le contexte courant
 * Equivalent de la méthode doc->isEnabled
 */
function doc_folder_isenabled($row)
{
    doc_getshare();

    return  $row['id_user'] == $_SESSION['ploopi']['userid'] // Propriétaire ?
            || ploopi\acl::isadmin() // Admin sys ?
            || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN) // Rôle admin ?
            || ($row['foldertype'] == 'public' && in_array($row['id_workspace'], explode(',', ploopi\system::viewworkspaces()))) // Public pour l'espace courant ?
            || ($row['foldertype'] == 'shared' &&  in_array($row['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])); // Partagé pour l'utilisateur ?
}


function doc_folder_contentisreadonly($row, $action = null, $id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $booActionIsOk = is_null($action) || ploopi\acl::isactionallowed($action, -1, $id_module);

    $root = empty($row['id']);

    // On peut écrire dans le dossier si
    // - racine & le droit d'écrire dans la racine & actionOK
    // - pas racine & pas en lecture seule & actionOK
    // - propriétaire du dossier & actionOK
    // - admin du module
    // - super admin
    return !((((!$root && empty($row['readonly'])) || $row['id_user'] == $_SESSION['ploopi']['userid'] || ($root && ploopi\param::get('doc_rootwritable', $id_module))) && $booActionIsOk) || ploopi\acl::isadmin() || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, -1, $id_module));
}


/**
 * Retourne true si le fichier n'est pas modifiable
 * Modifiable par l'admin sys, le rôle admin, le propriétaire, non readonly + role sup
 */

function doc_file_isreadonly($row, $action = null)
{
    $booActionIsOk = is_null($action) || ploopi\acl::isactionallowed($action);

    return !(ploopi\acl::isadmin() || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN) || $row['id_user'] == $_SESSION['ploopi']['userid'] || ($booActionIsOk && empty($row['readonly'])));
}
?>
