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
 * Interface publique du module
 *
 * @package dbreport
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

ploopi\module::init('dbreport');

include_once './include/classes/query.php';

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

$strDbReportOp = isset($_REQUEST['dbreport_op']) ? $_REQUEST['dbreport_op'] : '';

switch($strDbReportOp)
{
    case 'query_modify':
        if (!ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE)) ploopi\system::logout();
        include_once './modules/dbreport/public_query_modify.php';
    break;

    default:
        include_once './modules/dbreport/public_query_list.php';
    break;
}
?>
