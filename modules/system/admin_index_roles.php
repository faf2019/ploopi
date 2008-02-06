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

include_once './modules/system/class_role.php';

switch($op)
{
	case 'delete_role':
		if (!empty($_GET['roleid']) && is_numeric($_GET['roleid']))
		{
			$role = new role();
			$role->open($_GET['roleid']);
			ploopi_create_user_action_log(_SYSTEM_ACTION_DELETEROLE, "{$role->fields['label']} ({$role->fields['id']})");
			$role->delete();
			ploopi_redirect("{$scriptenv}?roleTabItem=tabRoleManagement&reloadsession");
		}
		ploopi_redirect("{$scriptenv}?roleTabItem=tabRoleManagement");
	break;

	case 'save_role':
		$role = new role();

		if (!empty($_POST['roleid']) && is_numeric($_POST['roleid'])) $role->open($_POST['roleid']);

		$isnew = $role->new;

		if ($isnew) $role->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];

		$role->setvalues($_POST,'role_');
		if (empty($_POST['role_shared'])) $role->fields['shared'] = 0;

		$module = new module();
		if ($module->open($_POST['role_id_module']))
		{
			$role->save($_POST['id_action'], $module->fields['id_module_type']);
			ploopi_create_user_action_log(($isnew) ?_SYSTEM_ACTION_CREATEROLE : _SYSTEM_ACTION_MODIFYROLE, "{$role->fields['label']} ({$role->fields['id']})");
			ploopi_redirect("{$scriptenv}?roleTabItem=tabRoleManagement&reloadsession");
		}

		ploopi_redirect("{$scriptenv}?roleTabItem=tabRoleManagement");
	break;
}



if (isset($system_roletabid)) $_SESSION['system']['roletabid'] = $system_roletabid;

if (!isset($_SESSION['system']['roletabid'])) $_SESSION['system']['roletabid'] = '';

$tabs['tabRoleManagement'] = array (	'title' => _SYSTEM_LABELTAB_ROLEMANAGEMENT,
										'url' => "{$scriptenv}?roleTabItem=tabRoleManagement");

$tabs['tabRoleAdd'] = array (	'title' => 'Ajouter un Rôle',
								'url' => "{$scriptenv}?roleTabItem=tabRoleAdd");

$tabs['tabRoleUsers'] = array (	'title' => _SYSTEM_LABELTAB_ROLEUSERS,
										'url' => "{$scriptenv}?roleTabItem=tabRoleUsers");

if (!empty($_GET['roleTabItem']))  $_SESSION['system']['roleTabItem'] = $_GET['roleTabItem'];
if (!isset($_SESSION['system']['roleTabItem'])) $_SESSION['system']['roleTabItem'] = '';

echo $skin->create_tabs('',$tabs, $_SESSION['system']['roleTabItem']);

echo $skin->open_simplebloc();

switch($_SESSION['system']['roleTabItem'])
{
    case 'tabRoleManagement':
		switch($op)
		{
			case 'modify_role':
				include('./modules/system/admin_index_roles_form.php');
			break;

			default:
				include('./modules/system/admin_index_roles_management.php');
			break;

		}
    break;

    case 'tabRoleAdd':
		include('./modules/system/admin_index_roles_form.php');
    break;

    case 'tabRoleUsers':
		include('./modules/system/admin_index_roles_assignment.php');
    break;
}


echo $skin->close_simplebloc();
?>
