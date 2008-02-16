<?php
# PHP File Uploader with progress bar Version 1.0a
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
#
# CHANGES:
# 1.0.01a    : using session
# 1.0.02a    : remove iframe and pass throw ajax for progress display
#
# FILE DESCRIPTION:
# return upload status for ajax call
#

include './lib/cupload/Cupload.class.php';

$_sId = $_GET['sid'];
$uploader = & new CUploadSentinel;
$uploader->__init($_sId);
$_total_size = trim(sprintf('%10.2f',$uploader->total_size/1024));
$_complete   = $uploader->complete;
$_received   = trim(sprintf('%10.2f',$uploader->received/1024));
$_current    = $uploader->current;
$_speed      = $uploader->speed;
$_percent    = $uploader->percent;
if($_total_size>0) {
  $output = $_received.'|'.$_total_size.'|'.$_complete.'|'.$_current.'|'.$_speed.'|'.$_percent;
} else $output="";
header('Content-Length: '.strlen($output));         // now headers to resolve browser cache problems
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo $output;

ploopi_die();
?>
