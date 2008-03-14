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

###############################################################################
#
# copy / delete functions
#
###############################################################################

/**
* recursive copy of src folder into dest folder
*
* @param string path to source file
* @param string path to destination file
* @param int chmod'like
* @return boolean
*
* @version 2.09
* @since 0.1
*
* @category files manipulations
*/
function ploopi_copydir($src , $dest, $mask = 0750)
{
    $ok = true;
    $folder=opendir($src);

    if (!file_exists($dest))
    {
        mkdir($dest, $mask);
        //chmod($dest, $mask);
    }

    while ($file = readdir($folder))
    {
        $l = array('.', '..');
        if (!in_array( $file, $l))
        {
            if (is_dir($src."/".$file))
            {
                $ok = ploopi_copydir("$src/$file", "$dest/$file", $mask);
            }
            else
            {
                // test if writable
                if (!(file_exists("$dest/$file") && !is_writable("$dest/$file")))
                {
                    copy("$src/$file", "$dest/$file");
                    //chmod("$dest/$file", $mask);
                }
                else $ok = false;
            }
        }
    }
    return $ok;
}

/**
* recursive delete of folder
*
* @param string path to delete
* @return void
*
* @version 2.09
* @since 0.1
*
* @category files manipulations
*/
function ploopi_deletedir($src)
{
    if (file_exists($src))
    {
        $folder=opendir($src);

        while ($file = readdir($folder))
        {
            $l = array('.', '..');
            if (!in_array( $file, $l))
            {
                if (is_dir($src."/".$file))
                {
                    ploopi_deletedir("$src/$file");
                }
                else
                {
                    unlink("$src/$file");
                }
            }
        }

        if (is_dir($src)) rmdir($src);
    }
}

/**
* recursive create of folder
*
* @param string path to create
* @return void
*
* @version 2.09
* @since 0.1
*
* @category files manipulations
*/

function ploopi_makedir($path)
{
    $array_folder = explode(_PLOOPI_SEP, $path);
    $old_path = '';
    foreach($array_folder as $current_path)
    {
        if ($current_path != '')
        {
            $current_path = $old_path. _PLOOPI_SEP .$current_path;

            if (!is_dir($current_path)) mkdir ($current_path, 0750);

            $old_path = $current_path;
        }
    }
    return $current_path;
}


function ploopi_getmimetype($filename)
{
    if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
        $UserBrowser = "Opera";
    elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
        $UserBrowser = "IE";
    else
        $UserBrowser = '';

    /// important for download im most browser
    $mimetype = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

    $mimetypes_array = array(

        //texte
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'shtml' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'latex' => 'application/x-latex',
        'g' => 'text/plain',
        'bas' => 'text/plain',
        'h' => 'text/plain',
        'c' => 'text/plain',
        'cc' => 'text/plain',
        'cpp' => 'text/plain',
        'java' => 'text/plain',
        'hh' => 'text/plain',
        'm' => 'text/plain',
        'f90' => 'text/plain',
        'csv' => 'text/csv',
        'tsv' => 'text/tab-separated-values',
        'php' => 'application/x-httpd-php',
        'php3' => 'application/x-httpd-php',
        'phtml' => 'application/x-httpd-php',

        //images
        'png' => 'image/png',
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'bmp' => 'image/bmp',
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


        //archives
        'bz2' => 'application/x-bzip',
        'gz' => 'application/x-gzip',
        'tar' => 'application/x-tar',
        'tgz' => 'application/x-gzip',
        'zip' => 'application/zip',
        'z' => 'application/x-compress',

        //audio
        'aif' => 'audio/aiff',
        'aiff' => 'audio/aiff',
        'aifc' => 'audio/aiff',
        'mid' => 'audio/mid',
        'midi' => 'audio/mid',
        'mp3' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mpa' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/wav',
        'wma' => 'audio/x-ms-wma',
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'mid' => 'audio/x-midi',
        'midi' => 'audio/x-midi',

        //video
        'asf' => 'video/x-ms-asf',
        'asx' => 'video/x-ms-asf',
        'avi' => 'video/avi',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp4' => 'audio/mp4',

        //playlist
        'pls' => 'audio/scpls',
        'm3u' => 'audio/x-mpegurl',

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

        //texte enrichi
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',

        //adobe
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'psd' => 'image/psd',
        'ps' => 'application/postscript',

        //macromedia
        'swf' => 'application/x-shockwave-flash',

        //real
        'ra' => 'audio/vnd.rn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'application/vnd.rn-realmedia',
        'rv' => 'video/vnd.rn-realvideo',

        //binaires/executables
        'hqx' => 'application/mac-binhex40',

        //cryptage/certificats
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

        //divers
        'vcf' => 'text/x-vcard'

    );

    $ext = ploopi_file_getextension($filename);

    if (isset($mimetypes_array[$ext])) $mimetype = $mimetypes_array[$ext];

    return($mimetype);
}

function ploopi_downloadfile($filepath, $destfilename, $deletefile = false, $attachment = true)
{
    //if (substr($path,-1) == '/') $path = substr($path, 0, strlen($path)-1);

    if (file_exists($filepath))
    {
        while (ob_get_contents()) @ob_end_clean();
        @set_time_limit(3600);

        // new download function works with IE6+SSL(http://fr.php.net/manual/fr/function.header.php#65404)
        $filepath = rawurldecode($filepath);
        $size = filesize($filepath);

        header('Content-Type: ' . ploopi_getmimetype($destfilename));

        if (ploopi_file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');

        if ($attachment) header("Content-disposition: attachment; filename=\"{$destfilename}\"");
        else header("Content-disposition: inline; filename=\"{$destfilename}\"");

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Accept-Ranges: bytes');
        header('Cache-control: private');
        header('Pragma: private');

        //  multipart-download and resume-download
        if (isset($_SERVER['HTTP_RANGE']))
        {
            list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']);
            str_replace($range, "-", $range);
            $size2 = $size - 1;
            $new_length = $size - $range;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: {$new_length}");
            header("Content-Range: bytes {$range}{$size2}/{$size}");
        }
        else
        {
            $size2 = $size-1;
            header("Content-Length: {$size}");
        }

        $chunksize = 1*(1024*1024);

        $bytes_send = 0;

        if ($fp = fopen($filepath, 'r'))
        {
            if(isset($_SERVER['HTTP_RANGE'])) fseek($fp, $range);

            while(!feof($fp) and (connection_status()==0))
            {
                $buffer = fread($fp, $chunksize);
                print($buffer);
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($fp);
        }
        else 
        {
            header('Content-type: text/html; charset=iso-8859-1');
            ploopi_die('Impossible d\'ouvrir le fichier');
        }
        
        //if ($deletefile && is_writable($filepath)) @unlink($filepath);

        ploopi_die();

    }
    else return(false);
}

function ploopi_file_getextension($filename)
{
    $filename_array = explode('.',$filename);
    return(strtolower($filename_array[sizeof($filename_array)-1]));
}

function ploopi_unzip($zip_file, $src_dir, $extract_dir)
{
    $cwd = getcwd();
    copy($src_dir . "/" . $zip_file, $extract_dir . "/" . $zip_file);
    chdir($extract_dir);
    shell_exec("unzip $zip_file");
    shell_exec("rm $zip_file");
    shell_exec("chmod -R 777 *");
    chdir($cwd);
}

?>
