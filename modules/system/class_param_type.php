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

include_once('./include/classes/class_data_object.php');
include_once('./modules/system/class_param_choice.php');

class param_type extends data_object
{
	/**
	* Class constructor
	*
	* @param int $connection_id
	* @access public
	**/

	function param_type()
	{
		parent::data_object('ploopi_param_type', 'id_module_type', 'name');
	}


	function delete($preserve_data = false)
	{
		global $db;

		$delete = "DELETE FROM ploopi_param_choice WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
		$db->query($delete);

		if (!$preserve_data)
		{
			$delete = "DELETE FROM ploopi_param_default WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
			$db->query($delete);

			$delete = "DELETE FROM ploopi_param_workspace WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
			$db->query($delete);

			$delete = "DELETE FROM ploopi_param_user WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
			$db->query($delete);
		}

		parent::delete();
	}

	function getallchoices($id = 0)
	{
		global $db;
		$param_choice = array();

		if ($id != 0) $id_param_type = $id;
		else $id_param_type = $this->fields['id'];

		$select = "SELECT * FROM ploopi_param_choice WHERE id_param_type = {$id_param_type}";
		$db->query($select);
		while ($fields = $db->fetchrow())
		{
			$param_choice[$fields['value']] = $fields['displayed_value'];
		}

		return($param_choice);
	}
}
?>
