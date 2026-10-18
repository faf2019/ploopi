<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Mon espace › Mes droits, gabarits de génération 4 : pour chaque espace, les
 * modules et les actions autorisées (ploopi\acl::isactionallowed).
 *
 * Remplace la présentation de public_actions.php sous ploopi4.
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$objSkin = ploopi\skin::get();

$arrActions = array();
$rs = ploopi\db::get()->query('SELECT * FROM ploopi_mb_action ORDER BY id_action');
while ($row = ploopi\db::get()->fetchrow($rs)) $arrActions[$row['id_module_type']][$row['id_action']] = $row;

echo ui::pagehead(_PLOOPI_LABEL_MYDATA, _SYSTEM_MYS_ACTIONS_SUBTITLE);

$booAny = false;

foreach($_SESSION['ploopi']['workspaces'] as $arrWorkspace)
{
    if (empty($arrWorkspace['adminlevel']) || $arrWorkspace['id'] == _PLOOPI_SYSTEMGROUP) continue;
    $booAny = true;

    global $ploopi_system_levels;
    $strLevel = isset($ploopi_system_levels[$arrWorkspace['adminlevel']]) ? $ploopi_system_levels[$arrWorkspace['adminlevel']] : '';

    echo ui::card_open($arrWorkspace['label'], 'building', array(), array('subtitle' => $strLevel));

    if (empty($arrWorkspace['modules'])) echo ui::emptystate('puzzle', _SYSTEM_MODF_OWN_NONE);
    else
    {
        echo '<ul class="p4-rights p4-ui">';
        foreach($arrWorkspace['modules'] as $intModule)
        {
            if (!isset($_SESSION['ploopi']['modules'][$intModule])) continue;
            $arrModule = $_SESSION['ploopi']['modules'][$intModule];

            $strChips = '';
            $intAllowed = 0;
            if (!empty($arrActions[$arrModule['id_module_type']]))
            {
                foreach($arrActions[$arrModule['id_module_type']] as $intAction => $arrAction)
                {
                    $booAllowed = ploopi\acl::isactionallowed($intAction, $arrWorkspace['id'], $intModule);
                    if ($booAllowed) $intAllowed++;
                    $strChips .= '<span class="p4-badge'.($booAllowed ? ' p4-badge-success' : ' p4-right-off').'">'.ui::icon($booAllowed ? 'check' : 'x').ui::e($arrAction['label']).'</span>';
                }
            }

            echo '<li><div class="p4-cell-main">'.ui::icon($objSkin->moduleicon($arrModule['moduletype'])).'<span><b>'.ui::e($arrModule['label']).'</b><small>'.ui::e($arrModule['moduletype']).'</small></span></div>'
                .'<div class="p4-chips">'.($strChips == '' ? '<span class="p4-muted p4-text-sm">'.ui::e(_SYSTEM_MYS_NOACTION).'</span>' : $strChips).'</div></li>';
        }
        echo '</ul>';
    }

    echo ui::card_close();
}

if (!$booAny)
{
    echo ui::card_open();
    echo ui::emptystate('shield', _SYSTEM_MYS_NOWORKSPACE);
    echo ui::card_close();
}
