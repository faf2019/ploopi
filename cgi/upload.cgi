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

define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');

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
	echo "ok";
	die();
}

if(!empty($_getvars['sid']))
{
	# get passed values from post form ( content_lengt, content_type, ... )
	$uploader = & new CUpload;
	$uploader->__init($_getvars['sid']);
	# upload process
	$uploader->processInput();
	# check is there was an error or not
	if(!$uploader->check_complete())
	{
		# error redirect
		//echo '<META HTTP-EQUIV=Refresh CONTENT="0; URL=/upload_php/error.php?'.$_query.'&'.$_redirect.'">';
	}
	$uploader->setcomplete();

	# force page refresh, this script may not process anything else than uploading files.
	# build _FILES array.
	//echo '<pre>'; print_r($uploader->postvars);echo '</pre>';
	//echo '<pre>'; print_r($_getvars);echo '</pre>';

	if (!empty($uploader->postvars['redirect']))
	{
		$_query = urldecode($uploader->postvars['redirect']);

		$_queryparams[] = 'sid='.urlencode($_getvars['sid']);

		foreach($uploader->postvars as $key => $value)
		{
			if ($key != 'sid' && $key != 'redirect' && $key != 'MAX_FILE_SIZE') $_queryparams[] = "{$key}=".urlencode($value);
		}

		if (!empty($_queryparams)) $_query .= '?'.implode('&',$_queryparams);

		echo '<META HTTP-EQUIV=Refresh CONTENT="0; URL='.$_query.'">';
	}

	die();
}
else
{
	echo "Chargement impossible";
	die();
}

?>
