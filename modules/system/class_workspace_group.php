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
?>
<?
include_once('./modules/system/class_workspace_group_role.php');

class workspace_group extends data_object
{

	/**
	* Class constructor
	*
	* @param int $idconnexion
	* @access public
	**/
	function workspace_group()
	{
		parent::data_object('ploopi_workspace_group','id_workspace','id_group');
		$this->fields['adminlevel'] = _PLOOPI_ID_LEVEL_USER;
	}

	function getroles()
	{
		global $db;

		$roles = array();

		$select = 	"
					SELECT 		ploopi_role.*,
								ploopi_module.label as modulelabel
					FROM 		ploopi_role,
								ploopi_workspace_group_role,
								ploopi_module
					WHERE 		ploopi_workspace_group_role.id_group = {$this->fields['id_group']}
					AND			ploopi_workspace_group_role.id_workspace = {$this->fields['id_workspace']}
					AND			ploopi_workspace_group_role.id_role = ploopi_role.id
					AND			ploopi_module.id = ploopi_role.id_module
					ORDER BY 	ploopi_role.label
					";

		$result = $db->query($select);

		while ($role = $db->fetchrow($result,MYSQL_ASSOC))
		{
			$roles[$role['id']] = $role;
		}

		return $roles;
	}

	function saveroles($roles)
	{
		global $db;

		$delete = 	"
				DELETE FROM 	ploopi_workspace_group_role
				WHERE 			ploopi_workspace_group_role.id_group = {$this->fields['id_group']}
				AND				ploopi_workspace_group_role.id_workspace = {$this->fields['id_workspace']}
				";

		$db->query($delete);

		foreach($roles as $key => $idrole)
		{
			$workspace_group_role = new workspace_group_role();
			$workspace_group_role->fields['id_group'] = $this->fields['id_group'];
			$workspace_group_role->fields['id_workspace'] = $this->fields['id_workspace'];
			$workspace_group_role->fields['id_role'] = $idrole;
			$workspace_group_role->save();
		}
	}

	function delete()
	{
		global $db;

		// search for modules
		$select = 	"
					SELECT 	m.id, m.label, mt.label as moduletype
					FROM	ploopi_module_workspace mw,
							ploopi_module m,
							ploopi_module_type mt
					WHERE	mw.id_workspace = {$this->fields['id_workspace']}
					AND		mw.id_module = m.id
					AND		m.id_module_type = mt.id
					";

		$db->query($select);
		while ($fields = $db->fetchrow())
		{
			$admin_groupid = $this->fields['id_group'];
			$admin_workspaceid = $this->fields['id_workspace'];
			$admin_moduleid = $fields['id'];

			echo "<br><b>« {$fields['label']} »</b> ({$fields['moduletype']})<br>";
			if (file_exists("./modules/{$fields['moduletype']}/include/admin_org_delete.php")) include("./modules/{$fields['moduletype']}/include/admin_org_delete.php");
		}
		parent::delete();
	}
}

?>
