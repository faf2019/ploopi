<?php
/*
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
 * Point d'entrée pour l'affichage des flux
 *
 * @package ploopi
 * @subpackage backend
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

// La requête doit forcément porter sur un module valide
if (isset($_REQUEST['ploopi_moduleid']) && is_numeric($_REQUEST['ploopi_moduleid']) && isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'][$_REQUEST['ploopi_moduleid']]))
{
    $db->query( "
        SELECT      mt.label labeltype,
                    m.label
        FROM        ploopi_module m
        INNER JOIN  ploopi_module_type mt
        ON          mt.id = m.id_module_type
        WHERE       m.id = '".$db->addslashes($_REQUEST['ploopi_moduleid'])."'
    ");

    if ($row = $db->fetchrow())
    {
        if (file_exists($backend_filepath = "./modules/{$row['labeltype']}/backend.php"))
        {
            include_once $backend_filepath;
            ovensia\ploopi\system::kill();
        }
    }
}
ovensia\ploopi\output::h404();
