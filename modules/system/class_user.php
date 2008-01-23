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
include_once('./include/classes/class_data_object.php');
include_once('./modules/system/class_group_user.php');
include_once('./modules/system/class_workspace_user.php');

class user extends data_object
{

	function user()
	{
		parent::data_object('ploopi_user');
		$this->fields['date_creation'] = ploopi_createtimestamp();
	}

	function delete()
	{
		global $db;

		// delete all group_user => delete module data (cf $group_user->delete())
		$select = "SELECT * FROM ploopi_group_user WHERE id_user = {$this->fields['id']}";
		$db->query($select);
		while($fields=$db->fetchrow())
		{
			$group_user = new group_user();
			$group_user->open($fields['id_group'], $fields['id_user']);
			$group_user->delete();
		}

		$select = "SELECT * FROM ploopi_workspace_user WHERE id_user = {$this->fields['id']}";
		$db->query($select);
		while($fields=$db->fetchrow())
		{
			$workspace_user = new workspace_user();
			$workspace_user->open($fields['id_workspace'], $fields['id_user']);
			$workspace_user->delete();
		}

		parent::delete();
	}

	/*
	 * Retourne l'ensemble des espaces auxquels l'utilisateurs est (plus ou moins directement) rattach�
	 * 1. R�cup�re les groupes auxquels l'utilisateur est rattach�
	 * 2. A partir des groupes, r�cup�re les espaces auxquels les groupes sont rattach�s directement ou pas (on regarde les parents)
	 * 3. R�cup�re les espaces auxquels l'utilisateur est directement rattach�
	 * */

	function getworkspaces()
	{
		global $db;

		$workspaces = array();

		// get organisation groups
		// on r�cup�re l'ensemble des groupes d'utilisateurs et leurs parents
		$groups = $this->getgroups();

		if (sizeof($groups))
		{
			$parents = array();

			foreach($groups as $org)
			{
				$parents = array_merge($parents,explode(';',$org['parents']));
				$parents[] = $org['id'];
			}

			$groups = implode(',',array_keys(array_flip($parents)));

			$select = 	"
						SELECT 		ploopi_workspace.*,
									ploopi_workspace_group.id_group,
									ploopi_workspace_group.adminlevel,
									ploopi_workspace_group.id_profile
						FROM 		ploopi_workspace
						LEFT JOIN	ploopi_workspace_group ON ploopi_workspace_group.id_workspace = ploopi_workspace.id
						WHERE		ploopi_workspace_group.id_group IN ({$groups})
						";

			$result = $db->query($select);

			while ($fields = $db->fetchrow($result))
			{
				if (empty($workspaces[$fields['id']])) $workspaces[$fields['id']] = $fields;
				else $workspaces[$fields['id']]['adminlevel'] = max($workspaces[$fields['id']]['adminlevel'], $fields['adminlevel']);

				$workspaces[$fields['id']]['groups'][] = $fields['id_group'];

			}
		}


		// get workspaces
		// rattachement classique entre un utilisateur et un espace de travail
		/*
		 * $select = 	"
					SELECT 		ploopi_workspace.*,
								ploopi_workspace_user.adminlevel,
								ploopi_workspace_user.id_profile
					FROM 		ploopi_workspace
					LEFT JOIN	ploopi_workspace_user ON ploopi_workspace_user.id_workspace = ploopi_workspace.id
					WHERE		(ploopi_workspace_user.id_user = {$this->fields['id']}
					OR			ploopi_workspace.id = "._PLOOPI_SYSTEMGROUP.")
					ORDER BY	ploopi_workspace.depth, id
					";
		*/

		$select = 	"
					SELECT 		w.*,
								wu.adminlevel,
								wu.id_profile

					FROM 		ploopi_workspace w
					INNER JOIN	ploopi_workspace_user wu ON wu.id_workspace = w.id
					WHERE		wu.id_user = {$this->fields['id']}
					ORDER BY	w.depth, id
					";

		$result = $db->query($select);

		while ($fields = $db->fetchrow($result)) $workspaces[$fields['id']] = $fields;

		return $workspaces;
	}

	/* Retourne l'ensemble des groupes auxquels l'utilisateurs est rattach� */
	function getgroups()
	{
		global $db;

		$select = 	"
					SELECT 		g.*

					FROM 		ploopi_group_user gu

					LEFT JOIN	ploopi_group g
					ON			gu.id_group = g.id

					WHERE		gu.id_user = {$this->fields['id']}

					ORDER BY	g.depth ASC
					";

		$result = $db->query($select);

		$groups = array();
		while ($fields = $db->fetchrow($result))
		{
			// group 0 = virtual group SYSTEM
			if ($fields['id'] == _SYSTEM_SYSTEMADMIN) $fields['label'] = _SYSTEM_LABEL_SYSTEM;
			$groups[$fields['id']] = $fields;
		}

		return $groups;
	}

	function attachtogroup($groupid)
	{
		global $db;

		$group_user = new group_user();
		$group_user->fields['id_user'] = $this->fields['id'];
		$group_user->fields['id_group'] = $groupid;
		$group_user->save();


	}

	function attachtoworkspace($workspaceid, $profileid = 0)
	{
		global $db;

		$workspace_user = new workspace_user();
		$workspace_user->fields['id_user'] = $this->fields['id'];
		$workspace_user->fields['id_workspace'] = $workspaceid;
		$workspace_user->fields['id_profile'] = $profileid;
		$workspace_user->save();


		// search for modules
		$select = 	"
					SELECT 	m.id, m.label, mt.label as moduletype
					FROM	ploopi_module_workspace mg,
							ploopi_module m,
							ploopi_module_type mt
					WHERE	mg.id_workspace = {$workspaceid}
					AND		mg.id_module = m.id
					AND		m.id_module_type = mt.id
					";

		$db->query($select);
		while ($fields = $db->fetchrow())
		{
			$admin_userid = $this->fields['id'];
			$admin_workspaceid = $workspaceid;
			$admin_moduleid = $fields['id'];

			echo "<br><b>� {$fields['label']} �</b> ({$fields['moduletype']})<br>";
			if (file_exists("./modules/{$fields['moduletype']}/include/admin_user_create.php")) include("./modules/{$fields['moduletype']}/include/admin_user_create.php");
		}

	}

	function movetogroup($groupid, $profileid = 0)
	{
		global $db;

		// delete all existing group associations
		$db->query("DELETE FROM ploopi_group_user WHERE id_user = {$this->fields['id']}");

		// create new group association
		$group_user = new group_user();
		$group_user->fields['id_user'] = $this->fields['id'];
		$group_user->fields['id_group'] = $groupid;
		$group_user->fields['id_profile'] = $profileid;
		$group_user->save();


		// search for modules
		$select = 	"
					SELECT 	m.id, m.label, mt.label as moduletype
					FROM	ploopi_module_workspace mg,
							ploopi_module m,
							ploopi_module_type mt
					WHERE	mg.id_group = {$groupid}
					AND		mg.id_module = m.id
					AND		m.id_module_type = mt.id
					";

		$db->query($select);
		while ($fields = $db->fetchrow())
		{
			$admin_userid = $this->fields['id'];
			$admin_groupid = $groupid;
			$admin_moduleid = $fields['id'];

			echo "<br><b>� {$fields['label']} �</b> ({$fields['moduletype']})<br>";
			if (file_exists("./modules/{$fields['moduletype']}/include/admin_user_create.php")) include("./modules/{$fields['moduletype']}/include/admin_user_create.php");
		}

	}

	function getprofile($workspaceid)
	{
		global $db;

		$profile=-1;

		$select = 	"
				SELECT		ploopi_workspace_user.id_profile
				FROM		ploopi_workspace_user
				WHERE		ploopi_workspace_user.id_user= {$this->fields['id']}
				AND		ploopi_workspace_user.id_workspace = {$workspaceid}
				";

		$result = $db->query($select);

		while ($fields = $db->fetchrow($result,MYSQL_ASSOC))
		{
			$profile = $fields['id_profile'];
		}

		return $profile;
	}

	function detachfromgroup($groupid)
	{
		$group_user = new group_user();
		$group_user->open($groupid,$this->fields['id']);
		$group_user->delete();
	}

	function getactions(&$actions)
	{

		global $db;

		$select = 	"
				SELECT		ploopi_workspace_user_role.id_workspace,
							ploopi_role_action.id_action,
							ploopi_role.id_module
				FROM		ploopi_role_action,
							ploopi_role,
							ploopi_workspace_user_role
				WHERE		ploopi_workspace_user_role.id_role = ploopi_role.id
				AND			ploopi_role.id = ploopi_role_action.id_role
				AND			ploopi_workspace_user_role.id_user = {$this->fields['id']}
				";

		$result = $db->query($select);

		while ($fields = $db->fetchrow($result,MYSQL_ASSOC))
		{
			$actions[$fields['id_workspace']][$fields['id_module']][$fields['id_action']] = true;
		}
    }



   /*
    *  function getgroups()
    {
    	global $db;
    	$grplist=array();
    	$select = 	"
				SELECT		distinct id_group
				FROM		ploopi_group_user
				WHERE		id_user = {$this->fields['id']}
				";

		$result = $db->query($select);

		while ($fields = $db->fetchrow($result))
		{
			array_push($grplist,$fields['id_group']);
		}



		return($grplist); //implode(",", $grplist)
    }
       */


    function getusersgroup()
    {
    	global $db;
    	$usrlist=array();
    	// r�cup�ration de ts les espaces de travail
		$workspaces = array_keys($this->getworkspaces());

		// r�cup�ration de ceux qui sont attach�s directement � ceuxci
   		 $select = 	"
					SELECT 		ploopi_workspace_user.id_user
					FROM 		ploopi_workspace_user
					WHERE		ploopi_workspace_user.id_workspace in (".implode(",",$workspaces).")";

		$result = $db->query($select);

		while ($fields = $db->fetchrow($result,MYSQL_ASSOC))
		{
			array_push($usrlist,$fields['id_user']);
		}

		// r�cup�ration de ceux qui sont attach�s par un groupe

    	$select = 	"
					SELECT		distinct id_user
					FROM		ploopi_group_user
					INNER JOIN  ploopi_workspace_group
					ON			ploopi_workspace_group.id_group=ploopi_group_user.id_group
					AND		ploopi_workspace_group.id_workspace in (".implode(",",$workspaces).")";


    	$result = $db->query($select);

		while ($fields = $db->fetchrow($result))
		{
			array_push($usrlist,$fields['id_user']);
		}
		return($usrlist);
    }
}

?>
