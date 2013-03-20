<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Ex�cution d'une requ�te + export dans le format demand�
 *
 * @package dbreport
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

include_once './modules/dbreport/classes/class_dbreport_query.php';

$objDbrQuery = new dbreport_query();

if (isset($_REQUEST['dbreport_format']) && isset($_REQUEST['dbreport_query_id']) && is_numeric($_REQUEST['dbreport_query_id']) && $objDbrQuery->open($_REQUEST['dbreport_query_id']))
{

    $intCacheLifetime = ploopi_getparam('dbreport_cache_lifetime');

    if (!$objDbrQuery->generate($_REQUEST)) ploopi_die();

    $strFileName = 'dbreport.'.strtolower($_REQUEST['dbreport_format']);

    // Instanciation du cache fichier (id unique en fonction de la requ�te)
    $objCache = new ploopi_cache($objDbrQuery->getcacheid()."/{$strFileName}", $intCacheLifetime);

    ploopi_ob_clean();

    // Cache existe ?
    if (!$objCache->start())
    {
        if ($_REQUEST['dbreport_format'] == 'sql')
        {
            echo $objDbrQuery->getquery();
        }
        else
        {
            $objDbrQuery->exec($intCacheLifetime); // Gestion interne d'un cache de donn�es ind�pendant du cache sur le fichier
            $objDbrQuery->export($_REQUEST['dbreport_format']);
        }

        // Mise en cache du fichier
        $objCache->end();
    }
}
ploopi_die();
?>
