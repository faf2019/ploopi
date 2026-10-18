<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion d'accès à l'espace physique de stockage.
 * Création de dossier, copie de fichiers, téléchargement de fichiers...
 *
 * @package ploopi
 * @subpackage filesystem
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class fs
{
    /**
     * Extensions susceptibles d'être confiées à un interpréteur par le serveur web.
     * Elles sont neutralisées au moment du stockage physique d'un fichier déposé par un
     * utilisateur.
     *
     * @var array
     *
     * @see self::safe_extension
     */

    private static $arrUnsafeExtensions = array(
        // PHP
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
        'phar', 'phps', 'phpt', 'pht', 'phtm', 'phtml',
        // Server Side Includes
        'shtml', 'shtm', 'stm',
        // Scripts CGI
        'cgi', 'pl', 'pm', 'py', 'rb', 'sh', 'bash', 'lua',
        // Autres plateformes serveur
        'asa', 'asax', 'ascx', 'ashx', 'asmx', 'asp', 'aspx', 'cer',
        'cfc', 'cfm', 'cfml', 'jsp', 'jspf', 'jspx', 'jsv', 'jsw', 'war',
        // Configuration Apache
        'htaccess', 'htpasswd'
    );

    /**
     * Extension de repli utilisée lorsque l'extension d'origine est jugée dangereuse.
     */

    const NEUTRAL_EXTENSION = 'bin';

    /**
     * Types mime pouvant être envoyés au navigateur en "inline" sans risque d'exécution
     * de script dans l'origine de l'application.
     *
     * image/svg+xml, text/html, text/xml et application/xhtml+xml en sont volontairement
     * absents : ils permettent l'exécution de script.
     *
     * @var array
     *
     * @see self::downloadfile
     */

    private static $arrInlineMimetypes = array(
        'image/bmp',
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/tiff',
        'image/webp',
        'image/x-icon',
        'application/pdf',
        'audio/mp4',
        'audio/mpeg',
        'audio/ogg',
        'audio/wav',
        'audio/webm',
        'audio/x-wav',
        'video/mp4',
        'video/ogg',
        'video/quicktime',
        'video/webm',
        'text/plain'
    );

    /**
     * Neutralise l'extension d'un fichier destinée au stockage physique.
     *
     * Les documents sont stockés sous "<id>.<extension>" dans _PLOOPI_PATHDATA, qui reste
     * fréquemment sous la racine web. Une extension exécutable y ouvre une exécution de code
     * distante dès que le .htaccess n'est pas appliqué (nginx, AllowOverride None).
     *
     * Le nom d'affichage du document n'est pas modifié : seul le nom du fichier sur disque
     * l'est. L'utilisateur retrouve son fichier sous son nom d'origine au téléchargement.
     *
     * @param string $strExtension extension d'origine
     * @return string extension utilisable pour le stockage physique
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     */

    public static function safe_extension($strExtension)
    {
        // Tout caractère hors [a-z0-9] est retiré : un point conservé permettrait une double extension
        $strExtension = preg_replace('@[^a-z0-9]@', '', strtolower(trim((string)$strExtension)));

        if ($strExtension == '') return '';

        return in_array($strExtension, self::$arrUnsafeExtensions) ? self::NEUTRAL_EXTENSION : $strExtension;
    }

    /**
     * Construit les paramètres "filename" d'un entête Content-disposition.
     *
     * Le nom provient d'un fichier déposé par un utilisateur : un guillemet ou un antislash
     * y détournerait l'entête. On produit un nom ASCII échappé et sa version RFC 5987.
     *
     * @param string $strFilename nom du fichier
     * @return string paramètres de l'entête
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     *
     * @link https://tools.ietf.org/html/rfc5987
     */

    public static function getdispositionfilename($strFilename)
    {
        $strFilename = str_replace(array("\r", "\n"), '', basename((string)$strFilename));

        if ($strFilename == '') $strFilename = 'document';

        // Nom de repli : ASCII imprimable, sans guillemet ni antislash
        $strFallback = str::convertaccents($strFilename);
        $strFallback = preg_replace('@[^\x20-\x7e]@', '_', $strFallback);
        $strFallback = str_replace(array('\\', '"'), '_', $strFallback);

        return sprintf('filename="%s"; filename*=UTF-8\'\'%s', $strFallback, rawurlencode($strFilename));
    }

    /**
     * Copie récursive du contenu d'un dossier source vers un dossier destination
     *
     * @param string $src dossier source
     * @param string $dest dossier destination
     * @param string $folder_mode mode attribué aux dossiers
     * @param string $file_mode mode attribué aux fichiers
     *
     * @return boolean true si pas de problème de copie
     */

    public static function copydir($src , $dest, $folder_mode = 0750, $file_mode = 0640)
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
                    $ok = fs::copydir($src_file, $dest_file, $folder_mode = 0750, $file_mode = 0640);
                }
                else
                {
                    // test if writable
                    if (!(file_exists($dest_file) && !is_writable($dest_file)))
                    {
                        copy($src_file, $dest_file);

                        // changement des droits uniquement le processus courant est propriétaire du fichier
                        if (_PLOOPI_SERVER_OSTYPE == 'unix' && fileowner($dest_file) == $processid) chmod($dest_file, $file_mode);
                    }
                    else $ok = false;
                }
            }
        }
        return $ok;
    }

    /**
     * Suppression récursive du contenu d'un dossier source vers un dossier destination
     *
     * @param string dossier à supprimer
     */

    public static function deletedir($strPath)
    {
        if (file_exists($strPath))
        {
            $resFolder = opendir($strPath);

            while ($strFile = readdir($resFolder))
            {
                if (!in_array($strFile, array('.', '..')))
                {
                    $strFilePath = $strPath._PLOOPI_SEP.$strFile;

                    if (is_dir($strFilePath)) fs::deletedir($strFilePath);
                    else unlink($strFilePath);
                }
            }

            if (is_dir($strPath)) rmdir($strPath);
        }
    }

    /**
     * Création récursive d'un dossier
     *
     * @param string chemin à créer
     */

    public static function makedir($strPath, $octMode = 0750)
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
     */

    public static function getmimetype($filename)
    {
        $ext = fs::file_getextension($filename);

        $db = db::get();

        // Si mimetype = '' ou pas trouvé c'est que c'est un octetstream donc on passe
        $sqlMime = $db->query("SELECT mimetype FROM ploopi_mimetype WHERE ext = '".$db->addslashes($ext)."' AND mimetype != ''");
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
     * Téléchargement d'un fichier vers le navigateur. Complète automatiquement les entêtes en renseignant notamment le type mime.
     *
     * @param string $filepath chemin physique du fichier
     * @param string $destfilename nom du fichier tel qu'il apparaîtra au moment du téléchargement
     * @param boolean $deletefile true si le fichier doit être supprimé après téléchargement
     * @param boolean $attachment true si le fichier doit être envoyé en "attachment", false si il doit être envoyé "inline"
     * @param boolean $die true si la fonction doit arrêter le script
     *
     * @return boolean false si le fichier n'existe pas, rien sinon
     */

    public static function downloadfile($filepath, $destfilename, $deletefile = false, $attachment = true, $die = true)
    {
        clearstatcache();

        if (file_exists($filepath))
        {
            buffer::clean(true);

            @set_time_limit(0);

            $filepath = rawurldecode($filepath);
            $size = filesize($filepath);

            $chunksize = 1*(1024*1024);

            $ct = self::getmimetype($destfilename);

            /**
             * Un fichier envoyé "inline" est interprété par le navigateur dans l'origine de
             * l'application : un .html ou un .svg déposé par un utilisateur y exécuterait du
             * script avec la session de la victime. Seuls les types inertes restent autorisés
             * en inline, les autres repassent en "attachment".
             */
            if (!$attachment && !in_array($ct, self::$arrInlineMimetypes)) $attachment = true;

            header('Content-Type: ' . $ct);
            header('Content-Length: '.$size);

            // Interdit au navigateur de deviner un type plus permissif que celui annoncé
            header('X-Content-Type-Options: nosniff');

            if (self::file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');
            else header('Content-Encoding: identity');

            header('Content-disposition: '.($attachment ? 'attachment' : 'inline').'; '.self::getdispositionfilename($destfilename));

            header('Accept-Ranges: bytes');
            header('Pragma: private');

            if (current(explode('/', $ct)) == 'image') {
                header("Expires: " . date(DATE_RFC822, time()+315360000));
                header('Cache-Control: private, max-age=315360000, pre-check=315360000');
            }
            else {
                header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
                header('Cache-control: private');
            }

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
                system::kill('Impossible d\'ouvrir le fichier');
            }

            if ($deletefile && is_writable($filepath)) @unlink($filepath);

            if ($die) system::kill(null, true);

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

    public static function file_getextension($filename)
    {
        $filename_array = explode('.',$filename);
        return(strtolower($filename_array[sizeof($filename_array)-1]));
    }


    /**
     * Renvoie un identifiant unique pour l'explorateur
     *
     * @param string $strBasePath chemin physique vers le dossier
     * @param string $strDestField id du champ récupérant le fichier
     * @param string $strFilExplorerId identifiant du bloc (optionnel)
     *
     * @return string identifiant du bloc
     */

    public static function filexplorer_init($strBasePath, $strDestField, $strFilExplorerId = '')
    {
        if (empty($strFilExplorerId)) $strFilExplorerId = md5(uniqid(rand(), true));

        if ($strBasePath[strlen($strBasePath)-1] == _PLOOPI_SEP) $strBasePath = substr($strBasePath, 0, -1);

        $_SESSION['filexplorer'][$strFilExplorerId] =
            array(
                'basepath' => $strBasePath,
                'destfield' => $strDestField
            );

        return $strFilExplorerId;
    }

}
