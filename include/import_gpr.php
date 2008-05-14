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
 * Filtre les superglobales $_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER
 * Déchiffre l'URL si elle est chiffrée.
 * 
 * @package ploopi
 * @subpackage security
 * @copyright Netlor, Ovensia
 * @license GPL
 * 
 * @see ploopi_filtervar
 * @see ploopi_cipher
 */

include_once './include/functions/security.php';

if (!empty($_GET['ploopi_url']))
{
    $_GET['ploopi_url'] = ploopi_filtervar($_GET['ploopi_url']);

    require_once './include/classes/class_cipher.php';
    $cipher = new ploopi_cipher();
    $ploopi_url = $cipher->decrypt($_GET['ploopi_url']);

    foreach(explode('&',$ploopi_url) as $param)
    {
        if (strstr($param, '=')) list($key, $value) = explode('=',$param);
        else {$key = $param; $value = '';}

        $_REQUEST[$key] = $_GET[$key] = $value;
    }
}

$_GET = ploopi_filtervar($_GET);
$_POST = ploopi_filtervar($_POST);
$_REQUEST = ploopi_filtervar($_REQUEST);
$_COOKIE = ploopi_filtervar($_COOKIE);
$_SERVER = ploopi_filtervar($_SERVER);
?>
