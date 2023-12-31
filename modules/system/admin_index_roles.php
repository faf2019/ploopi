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
 * Gestion des rôles
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Inclusion des classes de gestion des roles
 */

switch($op)
{
    case 'delete_role':
        if (!empty($_GET['roleid']) && is_numeric($_GET['roleid']))
        {
            $role = new ploopi\role();
            $role->open($_GET['roleid']);
            ploopi\user_action_log::record(_SYSTEM_ACTION_DELETEROLE, "{$role->fields['label']} ({$role->fields['id']})");
            $role->delete();
            ploopi\output::redirect("admin.php?roleTabItem=tabRoleManagement&reloadsession");
        }
        ploopi\output::redirect("admin.php?roleTabItem=tabRoleManagement");
    break;

    case 'save_role':
        $role = new ploopi\role();

        if (!empty($_POST['roleid']) && is_numeric($_POST['roleid'])) $role->open($_POST['roleid']);

        $isnew = $role->new;

        if ($isnew) $role->fields['id_workspace'] = $workspaceid;

        $role->setvalues($_POST,'role_');
        if (empty($_POST['role_shared'])) $role->fields['shared'] = 0;

        $module = new ploopi\module();
        if ($module->open($_POST['role_id_module']))
        {
            $role->save();
            $role->saveactions($_POST['id_action'], $module->fields['id_module_type']);
            ploopi\user_action_log::record(($isnew) ?_SYSTEM_ACTION_CREATEROLE : _SYSTEM_ACTION_MODIFYROLE, "{$role->fields['label']} ({$role->fields['id']})");
            ploopi\output::redirect("admin.php?roleTabItem=tabRoleManagement&reloadsession");
        }

        ploopi\output::redirect("admin.php?roleTabItem=tabRoleManagement");
    break;
}

if (isset($system_roletabid)) $_SESSION['system']['roletabid'] = $system_roletabid;

if (!isset($_SESSION['system']['roletabid'])) $_SESSION['system']['roletabid'] = '';

$tabs['tabRoleManagement'] = array (
    'title' => _SYSTEM_LABELTAB_ROLEMANAGEMENT,
    'url' => "admin.php?roleTabItem=tabRoleManagement"
);

$tabs['tabRoleAdd'] = array (
    'title' => 'Ajouter un Rôle',
    'url' => "admin.php?roleTabItem=tabRoleAdd"
);

$tabs['tabRoleUsers'] = array (
    'title' => _SYSTEM_LABELTAB_ROLEUSERS,
    'url' => "admin.php?roleTabItem=tabRoleUsers"
);

if (!empty($_GET['roleTabItem']))  $_SESSION['system']['roleTabItem'] = $_GET['roleTabItem'];
if (!isset($_SESSION['system']['roleTabItem'])) $_SESSION['system']['roleTabItem'] = '';

echo ploopi\skin::get()->create_tabs($tabs, $_SESSION['system']['roleTabItem']);

switch($_SESSION['system']['roleTabItem'])
{
    case 'tabRoleManagement':
        switch($op)
        {
            case 'modify_role':
                include './modules/system/admin_index_roles_form.php';
            break;

            default:
                include './modules/system/admin_index_roles_management.php';
            break;

        }
    break;

    case 'tabRoleAdd':
        include './modules/system/admin_index_roles_form.php';
    break;

    case 'tabRoleUsers':
        include './modules/system/admin_index_roles_assignment.php';
    break;
}
?>
