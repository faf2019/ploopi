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
 * Procédure de désinstallation d'un module
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * On vérifie le contenu de la variable GET
 */
if (empty($_GET['uninstallidmoduletype'])) ploopi\output::redirect('admin.php');

$module_type = new ploopi\module_type();
if ($module_type->open($_GET['uninstallidmoduletype']))
{
    ploopi\user_action_log::record(_SYSTEM_ACTION_UNINSTALLMODULE, $module_type->fields['label']);

    if (!empty($module_type->fields['label']))
    {
        if (file_exists("./modules/{$module_type->fields['label']}/include/admin_uninstall.php")) include "./modules/{$module_type->fields['label']}/include/admin_uninstall.php";

        // DELETE FILES
        $filestodelete = "./modules/".$module_type->fields['label'];
        if (file_exists($filestodelete)) ploopi\fs::deletedir($filestodelete);
    }

    // DELETE TABLES
    $select = "SELECT * FROM ploopi_mb_table WHERE id_module_type = {$_GET['uninstallidmoduletype']}";
    $rs = ploopi\db::get()->query($select);
    while ($fields = ploopi\db::get()->fetchrow($rs))
    {
        ploopi\db::get()->query("DROP TABLE IF EXISTS `{$fields['name']}`");
    }

    // DELETE MODULE TYPE, MODULES, ACTIONS, etc...
    $module_type->delete();
}
else ploopi\output::redirect('admin.php');
?>
