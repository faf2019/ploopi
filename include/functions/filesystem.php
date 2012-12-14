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
 * Fonction d'acc�s � l'espace physique de stockage.
 * Cr�ation de dossier, copie de fichiers, t�l�chargement de fichiers...
 *
 * @package ploopi
 * @subpackage filesystem
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Copie r�cursive du contenu d'un dossier source vers un dossier destination
 *
 * @param string $src dossier source
 * @param string $dest dossier destination
 * @param string $folder_mode mode attribu� aux dossiers
 * @param string $file_mode mode attribu� aux fichiers
 * @return boolean true si pas de probl�me de copie
 */

function ploopi_copydir($src , $dest, $folder_mode = 0750, $file_mode = 0640)
{
    $ok = true;

    if (_PLOOPI_SERVER_OSTYPE == 'unix') $processid = posix_getuid();

    $folder = opendir($src);

    if (!file_exists($dest)) mkdir($dest, $folder_mode);

    while ($file = readdir($folder))
    {
        if (!in_array($file, array('.', '..')))
        {
            $src_file = "{$src}/{$file}";
            $dest_file = "{$dest}/{$file}";

            if (is_dir($src_file))
            {
                $ok = ploopi_copydir($src_file, $dest_file, $folder_mode = 0750, $file_mode = 0640);
            }
            else
            {
                // test if writable
                if (!(file_exists($dest_file) && !is_writable($dest_file)))
                {
                    copy($src_file, $dest_file);

                    // changement des droits uniquement le processus courant est propri�taire du fichier
                    if (_PLOOPI_SERVER_OSTYPE == 'unix' && fileowner($dest_file) == $processid) chmod($dest_file, $file_mode);
                }
                else $ok = false;
            }
        }
    }
    return $ok;
}

/**
 * Suppression r�cursive du contenu d'un dossier source vers un dossier destination
 *
 * @param string dossier � supprimer
 */

function ploopi_deletedir($strPath)
{
    if (file_exists($strPath))
    {
        $resFolder = opendir($strPath);

        while ($strFile = readdir($resFolder))
        {
            if (!in_array($strFile, array('.', '..')))
            {
                $strFilePath = $strPath._PLOOPI_SEP.$strFile;

                if (is_dir($strFilePath)) ploopi_deletedir($strFilePath);
                else unlink($strFilePath);
            }
        }

        if (is_dir($strPath)) rmdir($strPath);
    }
}

/**
 * Cr�ation r�cursive d'un dossier
 *
 * @param string chemin � cr�er
 */

function ploopi_makedir($strPath, $octMode = 0750)
{
    if (!file_exists($strPath))
    {
        $arrFolder = explode(_PLOOPI_SEP, $strPath);

        $strOldPath = _PLOOPI_SERVER_OSTYPE == 'unix'  ? _PLOOPI_SEP : '';

        foreach($arrFolder as $strFolder)
        {
            if ($strFolder != '')
            {
                $strFolder = $strOldPath.$strFolder;

                if (!is_dir($strFolder)) mkdir($strFolder, $octMode);

                $strOldPath = $strFolder._PLOOPI_SEP;
            }
        }
    }
}

/**
 * Renvoie le type mime du fichier en fonction de son extension (mais pas par rapport au contenu)
 *
 * @param string $filename chemin du fichier
 * @return string type mime
 *
 * @see ploopi_downloadfile
 */

function ploopi_getmimetype($filename)
{
    $ext = ploopi_file_getextension($filename);

    global $db;

    // Si mimetype = '' ou pas trouv� c'est que c'est un octetstream donc on passe
    $sqlMime = $db->query("SELECT mimetype FROM ploopi_mimetype WHERE ext = '{$ext}' AND mimetype != ''");
    if($db->numrows($sqlMime))
    {
        $fieldMime = $db->fetchrow($sqlMime);
        return $fieldMime['mimetype'];
    }

    $strUserBrowser = '';
    if (!empty($_SERVER['HTTP_USER_AGENT']))
    {
        if (preg_match('/Opera(\/| )([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
            $strUserBrowser = "Opera";
        elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
            $strUserBrowser = "IE";
    }

    /// important for download im most browser
    $strMimetype = ($strUserBrowser == 'IE' || $strUserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

    return($strMimetype);
}

/**
 * T�l�chargement d'un fichier vers le navigateur. Compl�te automatiquement les ent�tes en renseignant notamment le type mime.
 *
 * @param string $filepath chemin physique du fichier
 * @param string $destfilename nom du fichier tel qu'il appara�tra au moment du t�l�chargement
 * @param boolean $deletefile true si le fichier doit �tre supprim� apr�s t�l�chargement
 * @param boolean $attachment true si le fichier doit �tre envoy� en "attachment", false si il doit �tre envoy� "inline"
 * @param boolean $die true si la fonction doit arr�ter le script
 * @return boolean false si le fichier n'existe pas, rien sinon
 *
 * @see ploopi_getmimetype
 * @see ploopi_file_getextension
 */

function ploopi_downloadfile($filepath, $destfilename, $deletefile = false, $attachment = true, $die = true)
{
    clearstatcache();

    if (file_exists($filepath))
    {
        ploopi_ob_clean(true);

        @set_time_limit(0);

        $filepath = rawurldecode($filepath);
        $size = filesize($filepath);

        $chunksize = 1*(1024*1024);

        header('Content-Type: ' . ploopi_getmimetype($destfilename));
        header('Content-Length: '.$size);

        if (ploopi_file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');
        else header('Content-Encoding: identity');

        if ($attachment) header("Content-disposition: attachment; filename=\"{$destfilename}\"");
        else header("Content-disposition: inline; filename=\"{$destfilename}\"");
        header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
        header('Accept-Ranges: bytes');
        header('Cache-control: private');
        header('Pragma: private');

        ob_start();
        if ($fp = fopen($filepath, 'r'))
        {
            while(!feof($fp) && connection_status() == 0)
            {
                echo fread($fp, $chunksize);
                ob_flush();
            }
            fclose($fp);
            ob_end_flush();
        }
        else
        {
            header('Content-type: text/html; charset=iso-8859-1');
            ploopi_die('Impossible d\'ouvrir le fichier');
        }

        if ($deletefile && is_writable($filepath)) @unlink($filepath);

        if ($die) ploopi_die(null, true);

    }
    else return false;
}

/**
 * Extrait l'extension d'un fichier
 *
 * @param string $filename chemin physique du dossier
 * @return string extension du fichier
 *
 * @see ploopi_downloadfile
 */

function ploopi_file_getextension($filename)
{
    $filename_array = explode('.',$filename);
    return(strtolower($filename_array[sizeof($filename_array)-1]));
}

?>
