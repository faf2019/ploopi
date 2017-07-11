<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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
 * @author St�phane Escaich
 */

/**
 * D�finition des constantes
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
        'html'  => 'highlighter:html',
        'htm'   => 'highlighter:html',
        'bat'   => 'div',
        'sh'    => 'highlighter:sh',
        'mysql' => 'highlighter:mysql',
        'sql'   => 'highlighter:sql',
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
 * Retourne le nombre d'�l�ments (fichiers/dossiers) d'un dossier
 *
 * @param int $id_folder identifiant du dossier
 * @return int nombre d'�l�ments
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
 * Chargement des partages en session (pour �viter les multiples rechargements)
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
 * Chargement des droits de validation en session (pour �viter les multiples rechargements)
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
 * V�rifie qu'un enregistrement d'un objet est accessible dans un certain contexte par un utilisateur
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
                if (ploopi_isactionallowed(_DOC_ACTION_ADMIN, $objFolder->fields['id_workspace'], $id_module) || $objFolder->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
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

                if (ploopi_isactionallowed(_DOC_ACTION_ADMIN, $objFile->fields['id_workspace'], $id_module) || $objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
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

            // ok si propri�taire du fichier ou validateur du dossier
            $objFile = new docfiledraft();
            if ($objFile->openmd5($id_record))
            {
                if (ploopi_isactionallowed(_DOC_ACTION_ADMIN, $objFile->fields['id_workspace'], $id_module) || $objFile->fields['id_user'] == $_SESSION['ploopi']['userid']) $enabled = true;
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
 * Retourne la liste compl�te des dossiers sous forme d'un tableau
 * en prenant compte la vue de l'utilisateur (partages, etc.)
 *
 * @return array tableau des dossiers
 */
function doc_getfolders()
{
    global $db;

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
    if (!ploopi_isadmin() && !ploopi_isactionallowed(_DOC_ACTION_ADMIN))
    {
        // Publi� (ou propri�taire)
        $arrWhere['published'] = "(f.published = 1 OR f.id_user = {$id_user})";

        // Priori�taire
        $arrWhere['visibility']['user'] = "f.id_user = {$id_user}";

        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders'])) $arrWhere['visibility']['wf'] = "f.id_folder IN (".implode(', ', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']).")";


        // Partag�
        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) $arrWhere['visibility']['shared'] = "(f.foldertype = 'shared' AND f.id IN (".implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])."))";
        // Public
        $arrWhere['visibility']['public'] = "(f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces()."))";

        // Synth�se visibilit�
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
    global $db;

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
                $strLink = ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$fields['id']}");
                $strOnClick = '';
            }
            // Arbre de s�lection
            else
            {
                // On ne propose que les dossiers qui ne sont pas en lecture seule

                $strId = $strPrefix.$fields['id'];
                $strparents = preg_split('/;/', $strPrefix.str_replace(';', ";{$strPrefix}", $fields['parents']));
                $strLink = 'javascript:void(0);';
                if ($id == 0 || !doc_folder_contentisreadonly($arrFolders['list'][$id]))
                {
                    $strOnClick = "$('docfolder_id_folder').value='{$fields['id']}'; $('docfolder_id_folder_name').innerHTML='".addslashes(ploopi_htmlentities($fields['name']))."'; ploopi_hidepopup('doc_popup_folderselect');";
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
                'node_onclick' => "ploopi_skin_treeview_shownode('{$strId}', '".ploopi_queryencode("ploopi_op=doc_folder_detail&doc_folder_id={$fields['id']}&doc_prefix={$strPrefix}&doc_excludes={$strExcludes}")."', 'admin-light.php');",
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
 * Retourne un tableau contenant les r�gles de r��criture propos�es par le module DOC
 *
 * @return array tableau contenant les r�gles de r��criture
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
 * Affichage des dossiers dans l'explorateur de fichiers appel� depuis FCK Editor
 *
 * @param array $arrFolders tableau contenant les dossiers � afficher
 * @param int $intIdFolder Identifiant du dossier � afficher
 * @param string $strPath Chemin complet du dossier
 */
function doc_fckexplorer_displayfolders(&$arrFolders, $intIdFolder = 0, $strPath = ' ')
{
    if (isset($arrFolders['tree'][$intIdFolder]))
    {
        foreach($arrFolders['tree'][$intIdFolder] as $intIdChild)
        {
            ?>
            <option value="<?php echo $intIdChild; ?>" label="<?php echo ploopi_htmlentities($arrFolders['list'][$intIdChild]['name']); ?>"><?php echo ploopi_htmlentities("{$strPath} / {$arrFolders['list'][$intIdChild]['name']}"); ?></option>
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
 * Modifiable par l'admin sys, le r�le admin, le propri�taire, accessible + role sup
 */
function doc_folder_isreadonly($row, $action = null)
{
    $booActionIsOk = is_null($action) || ploopi_isactionallowed($action);

    return !(ploopi_isadmin() || ploopi_isactionallowed(_DOC_ACTION_ADMIN) || $row['id_user'] == $_SESSION['ploopi']['userid'] || (doc_folder_isenabled($row) && $booActionIsOk));
}

/**
 * Retourne true si le dossier est accessible dans le contexte courant
 * Equivalent de la m�thode doc->isEnabled
 */
function doc_folder_isenabled($row)
{
    doc_getshare();

    return  $row['id_user'] == $_SESSION['ploopi']['userid'] // Propri�taire ?
            || ploopi_isadmin() // Admin sys ?
            || ploopi_isactionallowed(_DOC_ACTION_ADMIN) // R�le admin ?
            || ($row['foldertype'] == 'public' && in_array($row['id_workspace'], explode(',', ploopi_viewworkspaces()))) // Public pour l'espace courant ?
            || ($row['foldertype'] == 'shared' &&  in_array($row['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])); // Partag� pour l'utilisateur ?
}


function doc_folder_contentisreadonly($row, $action = null, $id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $booActionIsOk = is_null($action) || ploopi_isactionallowed($action, -1, $id_module);

    $root = empty($row['id']);

    // On peut �crire dans le dossier si
    // - racine & le droit d'�crire dans la racine & actionOK
    // - pas racine & pas en lecture seule & actionOK
    // - propri�taire du dossier & actionOK
    // - admin du module
    // - super admin
    return !((((!$root && !$row['readonly']) || $row['id_user'] == $_SESSION['ploopi']['userid'] || ($root && ploopi_getparam('doc_rootwritable', $id_module))) && $booActionIsOk) || ploopi_isadmin() || ploopi_isactionallowed(_DOC_ACTION_ADMIN, -1, $id_module));
}


/**
 * Retourne true si le fichier n'est pas modifiable
 * Modifiable par l'admin sys, le r�le admin, le propri�taire, non readonly + role sup
 */

function doc_file_isreadonly($row, $action = null)
{
    $booActionIsOk = is_null($action) || ploopi_isactionallowed($action);

    return !(ploopi_isadmin() || ploopi_isactionallowed(_DOC_ACTION_ADMIN) || $row['id_user'] == $_SESSION['ploopi']['userid'] || ($booActionIsOk && !$row['readonly']));
}
?>
