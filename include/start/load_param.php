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
 * Chargement des param�tres des modules + description des actions
 *
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

$_SESSION['ploopi']['modules'] = array();
$_SESSION['ploopi']['moduletypes'] = array();
$_SESSION['ploopi']['actions_desc'] = array();

// On r�cup�re les modules
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

// On r�cup�re les param�tres par d�faut
$db->query("
    SELECT      pd.id_module,
                pt.name,
                pt.label,
                pd.value

    FROM        ploopi_param_default pd

    INNER JOIN  ploopi_param_type pt
    ON          pt.name = pd.name
    AND         pt.id_module_type = pd.id_module_type

    WHERE       pd.id_module IN ({$listmodules})
");
    
while ($fields = $db->fetchrow())
{
    $_SESSION['ploopi']['params'][$fields['id_module']]['default'][$fields['name']] = $fields['value'];
}

// On r�cup�re les param�tres "espace de travail"
$db->query("
    SELECT      pg.id_module,
                pt.name,
                pt.label,
                pg.value,
                pg.id_workspace

    FROM        ploopi_param_workspace pg

    INNER JOIN  ploopi_param_type pt
    ON          pt.name = pg.name
    AND         pt.id_module_type = pg.id_module_type

    WHERE       pg.id_module IN ({$listmodules})
");
            
while ($fields = $db->fetchrow())
{
    $_SESSION['ploopi']['params'][$fields['id_module']]['workspace'][$fields['id_workspace']][$fields['name']] = $fields['value'];
}

// On r�cup�re les param�tres utilisateur
if (!empty($_SESSION['ploopi']['userid']))
{
    $db->query("
        SELECT      pu.id_module,
                    pt.name,
                    pt.label,
                    pu.value

        FROM        ploopi_param_user pu

        INNER JOIN  ploopi_param_type pt
        ON          pt.name = pu.name
        AND         pt.id_module_type = pu.id_module_type

        WHERE       pu.id_module IN ({$listmodules})
        AND         pu.id_user = {$_SESSION['ploopi']['userid']}
    ");
        
    while ($fields = $db->fetchrow())
    {
        $_SESSION['ploopi']['params'][$fields['id_module']]['user'][$fields['name']] = $fields['value'];
    }
}

ploopi_loadparams();

// On r�cup�re la description des actions
$db->query("
    SELECT      mba.id_module_type,
                mba.id_action,
                mba.label

    FROM        ploopi_mb_action mba
");
    
while ($row = $db->fetchrow()) $_SESSION['ploopi']['actions_desc'][$row['id_module_type']][$row['id_action']] = $row['label'];

$_SESSION['ploopi']['paramloaded'] = true;
?>
