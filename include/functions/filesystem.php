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
 * Fonction d'accès à l'espace physique de stockage.
 * Création de dossier, copie de fichiers, téléchargement de fichiers...
 *
 * @package ploopi
 * @subpackage filesystem
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Copie récursive du contenu d'un dossier source vers un dossier destination
 *
 * @param string $src dossier source
 * @param string $dest dossier destination
 * @param string $folder_mode mode attribué aux dossiers
 * @param string $file_mode mode attribué aux fichiers
 * @return boolean true si pas de problème de copie
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
 * Création récursive d'un dossier
 *
 * @param string chemin à créer
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

    $strUserBrowser = '';
    if (!empty($_SERVER['HTTP_USER_AGENT']))
    {
        if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
            $strUserBrowser = "Opera";
        elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
            $strUserBrowser = "IE";
    }

    /// important for download im most browser
    $strMimetype = ($strUserBrowser == 'IE' || $strUserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

    $arrMimetypes =
        array(
            //texte
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'shtm' => 'text/html',
            'xhtml' => 'text/xhtml',
            'xhtm' => 'text/xhtml',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'latex' => 'application/x-latex',
            'g' => 'text/plain',
            'bas' => 'text/plain',
            'h' => 'text/plain',
            'c' => 'text/plain',
            'cc' => 'text/plain',
            'cpp' => 'text/plain',
            'hpp' => 'text/plain',
            'java' => 'text/plain',
            'hh' => 'text/plain',
            'm' => 'text/plain',
            'f90' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'php' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'sql' => 'text/x-sql',
            '323' => 'text/h323',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'latex' => 'application/x-tex',
            'ltx' => 'application/x-tex',
            'texi' => 'application/x-tex',
            'ctx' => 'application/x-tex',
            'py' => 'text/x-python',
            'pl' => 'text/x-perl',

            //images
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'bmp' => 'image/bmp',
            'pcx' => 'image/pcx',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'pnm' => 'image/x-portable-anymap',
            'pbm' => 'image/x-portable-bitmap',
            'pgm' => 'image/x-portable-graymap',
            'ppm' => 'image/x-portable-pixmap',
            'xbm' => 'image/x-xbitmap',
            'xpm' => 'image/x-xpixmap',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'rgb' => 'image/x-rgb',
            'jng' => 'image/x-jng',
            'cdr' => 'image/x-coreldraw',
            'pat' => 'image/x-coreldrawpattern',
            'cdt' => 'image/x-coreldrawtemplate',
            'cpt' => 'image/x-corelphotopaint',
        
            // thumbnails
            'thm' => 'application/vnd.eri.thm',

            //archives
            'bz2' => 'application/x-bzip',
            'gz' => 'application/x-gzip',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-gzip',
            'zip' => 'application/zip',
            'z' => 'application/x-compress',
            'sit' => 'application/x-stuffit',
            'sitx' => 'application/x-stuffit',
            'lzh' => 'application/lzh',
            'lhw' => 'application/lzh',
            'lzs' => 'application/lzh',
            'lzw' => 'application/lzh',
            'ace' => 'application/x-ace',
            'rar' => 'application/x-rar',
            'arj' => 'application/x-arj',
            '7z' => 'application/x-7z-compressed',

            //packages
            'rpm' => 'application/x-redhat-package',
            'deb' => 'application/x-debian-package',
            'udeb' => 'application/x-debian-package',
        
            //audio
            'aif' => 'audio/aiff',
            'aiff' => 'audio/aiff',
            'aifc' => 'audio/aiff',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'kar' => 'audio/midi',
            'rmi' => 'audio/midi',
            'mp3' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mpa' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',
            'au' => 'audio/basic',
            'snd' => 'audio/basic',
            'flac' => 'audio/flac',
            'aac' => 'audio/mp4',
            'm4a' => 'audio/mp4',
            'mka' => 'audio/x-matroska',
            'ac3' => 'audio/ac3',
            'mpc' => 'audio/x-musepack',
            
            //audio/tracker
            'mod' => 'audio/x-mod',
            'xm' => '"audio/x-xm',
            'xi' => '"audio/x-xi',
            's3m' => '"audio/x-s3m',
            'stm' => '"audio/x-stm',
            'it' => '"audio/x-it',
        
            //video
            'asf' => 'video/x-ms-asf',
            'asx' => 'video/x-ms-asf',
            'avi' => 'video/avi',
            'mpg' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpa' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp4' => 'audio/mp4',
            'mp2' => 'audio/mpeg',
            'movie' => 'video/x-sgi-movie',
            'flv' => 'video/x-flv',
            'mkv' => 'video/x-matroska',
            '3gp' => 'video/3gpp',
            'dv' => 'video/dv',
            'dif' => 'video/dv',
            'dl' => 'video/dl',
            'h264' => 'video/h264',
            'viv' => 'video/vivo',
            'vivo' => 'video/vivo',
            'mng' => 'video/x-mng',
            'gl' => 'video/gl',
            'fli' => 'video/fli',

            //real
            'ra' => 'audio/vnd.rn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'application/vnd.rn-realmedia',
            'rv' => 'video/vnd.rn-realvideo',
            'rmvb' => 'application/vnd.rn-realmedia-vbr',
            'smil' => 'application/smil',
            'smi' => 'application/smil',

            //playlist
            'pls' => 'audio/scpls',
            'm3u' => 'audio/x-mpegurl',
            'mxu' => 'video/vnd.mpegurl',
            'pla' => 'audio/x-iriver-pla',
        
            //xml
            'xml' => 'text/xml',
            'xsl' => 'text/xsl',
            'sgml' => 'text/x-sgml',
            'sgm' => 'text/x-sgml',
            'flr' => 'x-world/x-vrml',
            'vrml' => 'x-world/x-vrml',
            'wrl' => 'x-world/x-vrml',
            'wrz' => 'x-world/x-vrml',
            'xaf' => 'x-world/x-vrml',
            'xof' => 'x-world/x-vrml',
            'rss' => 'application/rss+xml',
            'rdf' => 'application/rdf+xml',
            'atom' => 'application/atom+xml',
            'opml' => 'application/opml+xml',
            'xul' => 'application/vnd.mozilla.xul+xml',
         
            //bureautique
            'abw' => 'application/x-abiword',
            'gnumeric' => 'application/x-gnumeric',
            'kwd' => 'application/x-kword',
            'kwt' => 'application/x-kword',
            'ksp' => 'application/x-kspread',
            'kpr' => 'application/x-kpresenter',
            'kpt' => 'application/x-kpresenter',
        
            //microsoft
            'doc' => 'application/msword',
            'dot' => 'application/msword',
            'xls' => 'application/excel',
            'xla' => 'application/excel',
            'xlc' => 'application/excel',
            'xlm' => 'application/excel',
            'xlt' => 'application/excel',
            'xlw' => 'application/excel',
            'pps' => 'application/vnd.ms-powerpoint',
            'ppt' => 'application/vnd.ms-powerpoint',
            'ppz' => 'application/vnd.ms-powerpoint',
            'pot' => 'application/vnd.ms-powerpoint',
            'hlp' => 'application/mshelp',
            'chm' => 'application/mshelp',
            'msg' => 'application/vnd.ms-outlook',
            'mpp' => 'application/vnd.ms-project',
            'wcm' => 'application/vnd.ms-works',
            'wdb' => 'application/vnd.ms-works',
            'wks' => 'application/vnd.ms-works',
            'wps' => 'application/vnd.ms-works',
            'mdb' => 'application/x-msaccess',
            'wmf' => 'application/x-msmetafile',
            'mny' => 'application/x-msmoney',
            'pub' => 'application/x-mspublisher',
            'scd' => 'application/x-msschedule',
            'trm' => 'application/x-msterminal',
            'wri' => 'application/x-mswrite',
            'vsd' => 'application/vnd.visio',

            //open office
            'sxw' => 'application/vnd.sun.xml.writer',
            'stw' => 'application/vnd.sun.xml.writer.template',
            'sxg' => 'application/vnd.sun.xml.writer.global',
            'sxc' => 'application/vnd.sun.xml.calc',
            'stc' => 'application/vnd.sun.xml.calc.template',
            'sxi' => 'application/vnd.sun.xml.impress',
            'sti' => 'application/vnd.sun.xml.impress.template',
            'sxd' => 'application/vnd.sun.xml.draw',
            'std' => 'application/vnd.sun.xml.draw.template',
            'sxm' => 'application/vnd.sun.xml.math',

            'odt' => 'application/vnd.oasis.opendocument.text',
            'otm' => 'application/vnd.oasis.opendocument.text-master',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'otc' => 'application/vnd.oasis.opendocument.chart-template',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'otf' => 'application/vnd.oasis.opendocument.formula-template',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'otg' => 'application/vnd.oasis.opendocument.graphics-template',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'oti' => 'application/vnd.oasis.opendocument.image-template',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'otp' => 'application/vnd.oasis.opendocument.presentation-template',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'oth' => 'application/vnd.oasis.opendocument.text-web',

            //texte enrichi
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',

            //adobe
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'psd' => 'image/psd',
            'ps' => 'application/postscript',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
        
            //macromedia
            'swf' => 'application/x-shockwave-flash',
            'swfl' => 'application/x-shockwave-flash',
            'fla' => 'application/x-shockwave-flash',

            //binaires/executables
            'hqx' => 'application/mac-binhex40',
            'exe' => 'application/x-msdownload',
            'com' => 'application/x-msdownload',
            'msi' => 'application/x-msi',
            'class' => 'application/x-java-class',
            'jar' => 'application/java',
            'jad' => 'text/vnd.sun.j2me.app-descriptor',
        
            //shell
            'sh' => 'application/x-sh',
            'bat' => 'application/x-msdownload',
        
            //fonts
            'otf' => 'font/opentype',
            'ttf' => 'application/x-font-ttf',
            'ttc' => 'application/x-font-ttf',
            'pfb' => 'application/x-font-type1',
            'pfa' => 'application/x-font-type1',

            //chiffrement/certificats
            'cer' => 'application/x-x509-ca-cert',
            'crt' => 'application/x-x509-ca-cert',
            'der' => 'application/x-x509-ca-cert',
            'p12' => 'application/x-pkcs12',
            'pfx' => 'application/x-pkcs12',
            'p7b' => 'application/x-pkcs7-certificates',
            'spc' => 'application/x-pkcs7-certificates',
            'p7r' => 'application/x-pkcs7-certreqresp',
            'p7c' => 'application/x-pkcs7-mime',
            'p7m' => 'application/x-pkcs7-mime',
            'p7s' => 'application/x-pkcs7-signature',

            //disk images
            'iso' => 'application/x-iso9660-image',
            'nrg' => 'application/x-extension-nrg',
            'ccd' => 'text/x-cdwizard',
            'dmg' => 'application/x-apple-diskimage',

            //divers
            'vcf' => 'text/x-vcard',
            'vcs' => 'text/x-vcalendar',
            'ics' => 'text/calendar',
            'icz' => 'text/calendar',
            'mht' => 'message/rfc822',
            'mhtml' => 'message/rfc822',
            'torrent' => 'application/x-bittorrent'

        );

    $ext = ploopi_file_getextension($filename);

    if (isset($arrMimetypes[$ext])) $strMimetype = $arrMimetypes[$ext];

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
 * @return boolean false si le fichier n'existe pas, rien sinon
 *
 * @see ploopi_getmimetype
 * @see ploopi_file_getextension
 */

function ploopi_downloadfile($filepath, $destfilename, $deletefile = false, $attachment = true, $die = true)
{
    //if (substr($path,-1) == '/') $path = substr($path, 0, strlen($path)-1);

    if (file_exists($filepath))
    {
        ploopi_ob_clean();

        @set_time_limit(0);

        $filepath = rawurldecode($filepath);
        $size = filesize($filepath);

        header('Content-Type: ' . ploopi_getmimetype($destfilename));

        if (ploopi_file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');

        if ($attachment) header("Content-disposition: attachment; filename=\"{$destfilename}\"");
        else header("Content-disposition: inline; filename=\"{$destfilename}\"");

        header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
        header('Accept-Ranges: bytes');
        header('Cache-control: private');
        header('Pragma: private');
        header('Content-length: '.$size);
        header("Content-Encoding: None");

        $chunksize = 1*(1024*1024);

        if ($fp = fopen($filepath, 'r'))
        {
            while(!feof($fp) && connection_status() == 0)
            {
                echo fread($fp, $chunksize);
                flush();
            }
            fclose($fp);
        }
        else
        {
            header('Content-type: text/html; charset=iso-8859-1');
            ploopi_die('Impossible d\'ouvrir le fichier');
        }

        if ($deletefile && is_writable($filepath)) @unlink($filepath);

        if ($die) ploopi_die(null, false);

    }
    else return(false);
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
