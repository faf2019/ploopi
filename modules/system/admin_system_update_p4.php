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
 * Système › Mise à jour, gabarits de génération 4 : mise à jour de la base de données
 * du socle quand les fichiers sont plus récents (install/system/update_ploopi_*.sql).
 *
 * Même traitement que admin_system_update.php (op=system_update_execute).
 * Variables fournies : $strSysVersion (version de la base), $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$strInstallPath = './install/system/';
$strFrom = str_replace(' ', '', $strSysVersion);

// Scripts à jouer : versions strictement supérieures à celle de la base, dans l'ordre
$arrUpdates = array();
if (is_dir($strInstallPath) && ($resDir = @opendir($strInstallPath)))
{
    while (($strFile = readdir($resDir)) !== false)
    {
        if (is_file("{$strInstallPath}{$strFile}") && preg_match('@^update_ploopi_(.*)\.sql$@i', $strFile, $arrMatches) && !empty($arrMatches[1]) && version_compare($arrMatches[1], $strFrom) > 0) $arrUpdates[$arrMatches[1]] = $strFile;
    }
    closedir($resDir);
}
uksort($arrUpdates, 'version_compare');

echo ui::pagehead(_SYSTEM_MENU_SYSTEMUPDATE, _SYSTEM_SYSUPD_SUBTITLE);

if ($op == 'system_update_execute')
{
    if ($strSysVersion == _PLOOPI_VERSION) ploopi\output::redirect('admin.php?reloadsession');

    $arrReport = array();
    foreach($arrUpdates as $strVersion => $strFile)
    {
        if (is_readable("{$strInstallPath}{$strFile}"))
        {
            foreach(ploopi\db::split_sql(file_get_contents("{$strInstallPath}{$strFile}")) as $strQuery)
            {
                $strQuery = trim($strQuery);
                if ($strQuery != '') ploopi\db::get()->query($strQuery);
            }
            $arrReport[] = array('operation' => $strFile, 'detail' => str_replace('<VERSION>', $strVersion, _SYSTEM_SYSUPD_APPLIED), 'res' => true);
        }
        else $arrReport[] = array('operation' => $strFile, 'detail' => _SYSTEM_SYSUPD_UNREADABLE, 'res' => false);
    }

    ploopi\user_action_log::record(_SYSTEM_ACTION_UPDATEMODULE, "system {$strFrom} => "._PLOOPI_VERSION);

    echo ui::card_open(_SYSTEM_SYSUPD_DONE, 'circle-check');
    echo empty($arrReport) ? ui::emptystate('circle-check', _SYSTEM_SYSUPD_NOTHING) : ui::report($arrReport);
    echo ui::card_close(ui::button(array('label' => _PLOOPI_CONTINUE, 'icon' => 'arrow-right', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?reloadsession'))));
}
else
{
    echo ui::card_open(_SYSTEM_SYSUPD_TITLE, 'refresh');
    echo ui::stats(array(
        array(_SYSTEM_SYSUPD_DBVERSION, $strFrom, 'database'),
        array(_SYSTEM_SYSUPD_FILESVERSION, _PLOOPI_VERSION, 'server')
    ));
    echo '<p class="p4-mt-16">'.ui::e(_SYSTEM_SYSUPD_EXPLAIN).'</p>';

    if (!empty($arrUpdates))
    {
        echo '<ul class="p4-report p4-mt-12">';
        foreach($arrUpdates as $strVersion => $strFile) echo '<li>'.ui::icon('database', 'p4-muted').'<strong class="p4-mono">'.ui::e($strFile).'</strong><span>'.ui::e(str_replace('<VERSION>', $strVersion, _SYSTEM_SYSUPD_TOAPPLY)).'</span></li>';
        echo '</ul>';
    }

    echo ui::callout('warning', ui::e(_SYSTEM_SYSUPD_BACKUP));
    echo ui::card_close(ui::button(array('label' => _SYSTEM_SYSUPD_EXECUTE, 'icon' => 'database', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?op=system_update_execute'), 'confirm' => _SYSTEM_SYSUPD_CONFIRM)));
}

echo ui::script();
