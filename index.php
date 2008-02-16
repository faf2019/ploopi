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
?>
<?
include_once './include/start.php';
include_once './include/op.php';
include_once ($_SESSION['ploopi']['mode'] == 'web') ? './include/frontoffice.php' : './include/backoffice.php';

if ($ploopi_errors_level && _PLOOPI_MAIL_ERRORS && _PLOOPI_ADMINMAIL != '') mail(_PLOOPI_ADMINMAIL,"[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$_SERVER['HTTP_HOST']}]", "$ploopi_errors_nb erreur(s) sur $ploopi_errors_msg\n\nDUMP:\n$ploopi_errors_vars");
if (defined('_PLOOPI_ACTIVELOG') && _PLOOPI_ACTIVELOG)  include './modules/system/hit.php';

$db->close();
?>
