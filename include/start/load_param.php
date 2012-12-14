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
 * Chargement des paramètres des modules
 *
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$_SESSION['ploopi']['modules'] = array();
$_SESSION['ploopi']['moduletypes'] = array();

// On récupère les modules
$db->query("
    SELECT      m.id,
                m.label,
                m.active,
                m.visible,
                m.public,
                m.autoconnect,
                m.shared,
                m.viewmode,
                m.transverseview,
                m.id_module_type,
                m.id_workspace,
                mt.label as moduletype,
                mt.version,
                mt.author,
                mt.date

    FROM        ploopi_module m

    INNER JOIN  ploopi_module_type mt ON m.id_module_type = mt.id
");

while ($fields = $db->fetchrow())
{
    if (empty($_SESSION['ploopi']['moduletypes'][$fields['moduletype']]))
    {
        $_SESSION['ploopi']['moduletypes'][$fields['moduletype']] = array('version' => $fields['version'], 'author' => $fields['author'], 'date' => $fields['date']);
    }
    $_SESSION['ploopi']['modules'][$fields['id']] = $fields;
}

$listmodules = implode(',',array_keys($_SESSION['ploopi']['modules']));

ploopi_loadparams();

$_SESSION['ploopi']['paramloaded'] = true;
?>
