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

/**
 * Point d'entrée du webservice
 *
 * @package dbreport
 * @subpackage webservice
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */
ploopi\module::init('dbreport', false, false, false);

$strDbreportWsId = empty($_REQUEST['dbreport_ws_id']) ? '' : $_REQUEST['dbreport_ws_id'];
$strDbreportFormat = empty($_REQUEST['format']) ? 'xml' : strtolower($_REQUEST['format']);
$strDbreportCode = empty($_REQUEST['code']) ? '' : $_REQUEST['code'];
$strError = '';

$strFileName = "dbreport.{$strDbreportFormat}";

ploopi\buffer::clean();

echo dbreport::getData($strDbreportWsId, $_REQUEST, $strDbreportFormat, $strDbreportCode);


header('Content-Type: ' . ploopi\fs::getmimetype($strFileName).'; charset=iso-8859-1');
header('Content-Disposition: inline; Filename="'.$strFileName.'"');
header('Cache-Control: private');
header('Pragma: private');
header('Content-Length: '.ob_get_length());
header('Content-Encoding: none');

?>
