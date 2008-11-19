#!/usr/bin/php
<?php
# PHP File Uploader with progress bar Version 1.04a
# Copyright (C) P.E. Baroiller 2006
# http://www.pkconcept.net

# Licence:
# The contents of this file are subject to the Mozilla Public
# License Version 1.1 (the "License"); you may not use this file
# except in compliance with the License. You may obtain a copy of
# the License at http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS
# IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
# implied. See the License for the specific language governing
# rights and limitations under the License.
#
# CHANGES:
# 1.0.01a    : using session
# 1.0.02a    : remove iframe and pass throw ajax for progress display
# 1.0.03a    : remove session handling (too sloooowwww)
# 1.0.04a    : external config file & using Oo :)
#
# FILE DESCRIPTION
# upload process.
# get datas from stdin and create temp filenames.
# while uploading, a status file is updated.
#

# modifie par Stephane ESCAICH / Ovensia
#
# - adaptations pour integration dans PLOOPI (notamment pour la config)
# - recuperation des variables POST dans $uploader->postvars

include '../config/config.php';
include '../lib/cupload/Cupload.class.php';

if (substr(_PLOOPI_CGI_UPLOADTMP, -1, 1) != '/') define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
else define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP);

$_getvars = array();
$_queryparams = array();

# parse query string to get current args. may be there is a better way to do this .
$_args = $_SERVER['QUERY_STRING'];
$_args = explode('&',$_args);

foreach($_args as $_value)
{
    $_arg = explode('=',$_value);
    $_getvars[$_arg[0]] = urldecode($_arg[1]);
}

echo "Content-type: text/html\n\n";

if (isset($_getvars['test']))
{
    echo 'ok';
    die();
}

if(!empty($_getvars['sid']))
{
    # get passed values from post form ( content_length, content_type, ... )
    $uploader = & new CUpload;
    $uploader->__init($_getvars['sid']);
    # upload process
    $uploader->processInput();

    $_query = '';
    
    $strFormContent = '';

    if (!empty($uploader->postvars['redirect'][0]))
    {
        $_query = urldecode($uploader->postvars['redirect'][0]);
        
        $strFormContent .= '<input type="hidden" name="sid" value="'.htmlentities($_getvars['sid']).'" />';

        foreach($uploader->postvars as $key => $arrValue)
        {
            if ($key != 'sid' && $key != 'redirect' && $key != 'MAX_FILE_SIZE') 
            {
                foreach($arrValue as $value) $strFormContent .= '<input type="hidden" name="'.$key.'" value="'.htmlentities($value).'" />';
            }
        }
    }

    # check is there was an error or not
    if(!$uploader->check_complete())
    {
        $strFormContent .= '<input type="hidden" name="notcomplete" value="" />';
    }
    else
    {
        $uploader->setcomplete();
    }

    # force page refresh, this script may not process anything else than uploading files.
    if (!empty($uploader->postvars['redirect'][0]))
    {
        if (!empty($uploader->error)) $_query .= '&error='.urlencode($uploader->error);
        echo '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
        <head>
        </head>
        <body>
            <form name="cgifrmredirect" action="'.$_query.'" method="post">'.$strFormContent.'</form>
            <script type="text/javascript">document.cgifrmredirect.submit();</script>
        </body>
        </html>
        ';
    }
}
else echo 'Chargement impossible';
die();
?>
