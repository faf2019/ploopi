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

class module_group extends data_object
{
	/**
	* Class constructor
	*
	* @param int $connection_id 
	* @access public
	**/

	function module_group()
	{
		parent::data_object('ploopi_module_group','id_group','id_module');
	}
	

	function save()
	{
		global $db;

		if ($this->new)
		{
			$select = 	"
					SELECT MAX(ploopi_module_group.position) AS position
					FROM ploopi_module_group
					WHERE ploopi_module_group.id_group = ".$this->fields['id_group'];
	
			$result = $db->query($select);
			$fields = $db->fetchrow($result,MYSQL_ASSOC);
			$this->fields['position'] = $fields['position'] + 1;
		}
		
		parent::save();		
	}
	
	function delete()
	{
		global $db;

		$groupid = $this->fields['id_group'];
		$position = $this->fields['position'];
		
		$update = "update ploopi_module_group set position=position-1 where id_group = $groupid and position>$position";
		$db->query($update);;	

		parent::delete();
	}
	
	function changeposition($direction)
	{
	
		global $db;

		$groupid = $this->fields['id_group'];

		$select = 	"
				SELECT 	min(position) as minpos, 
					max(position) as maxpos 
				FROM 	ploopi_module_group
				WHERE	id_group = $groupid
				";

		$result = $db->query($select);
		$fields = $db->fetchrow($result);
		$minpos = $fields['minpos'];
		$maxpos = $fields['maxpos'];
		$position = $this->fields['position'];
		$move = 0;
		
		if ($direction=='down' && $position != $maxpos)
		{
			$move = 1;
		}

		if ($direction=='up' && $position != $minpos)
		{
			$move = -1;
		}

		if ($move!=0)
		{
			$update = "update ploopi_module_group set position=0 where id_group = $groupid and position=".($position+$move);
			$db->query($update);;	
			$update = "update ploopi_module_group set position=".($position+$move)." where id_group = $groupid and position=$position";
			$db->query($update);;	
			$update = "update ploopi_module_group set position=$position where id_group = $groupid and position=0";
			$db->query($update);;	
		}	
	}


	 function getroles()
	 {
		global $db;

		$group = new group();
		$group->open($this->fields['id_group']);
		$parents = str_replace(';',',',$group->fields['parents']);

		$roles = array();
		
		
		// select own roles and shared herited roles
		$select = 	"
				SELECT 		ploopi_role.*,
						ploopi_group.label as labelgroup
				FROM 		ploopi_role,
						ploopi_group
				WHERE 		ploopi_role.id_module = {$this->fields['id_module']}
				AND		(ploopi_role.id_group = {$this->fields['id_group']}
				OR 		(ploopi_role.id_group IN ($parents) AND ploopi_role.shared = 1))
				AND		ploopi_role.id_group = ploopi_group.id
				ORDER BY 	ploopi_role.label
				";
		
		$result = $db->query($select);
		
		while ($role = $db->fetchrow($result,MYSQL_ASSOC))
		{
			$roles[$role['id']] = $role;
		}

		return $roles;
	 }
	
}
?>