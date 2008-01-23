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

class annotation_tag extends data_object
{

	/**
	* Class constructor
	*
	* @param int $idconnexion
	* @access public
	**/
	function annotation_tag()
	{
		parent::data_object('ploopi_annotation_tag','id_annotation','id_tag');
	}

	function delete()
	{
		global $db;

		$select = 	"
					SELECT 	count(*) as c
					FROM	ploopi_annotation_tag
					WHERE	id_tag = {$this->fields['id_tag']}
					AND		id_annotation <> {$this->fields['id_annotation']}
					";

		$rs = $db->query($select);
		if (!($row = $db->fetchrow($rs)) || $row['c'] == 0)
		{
			$tag = new tag();
			$tag->open($this->fields['id_tag']);
			$tag->delete();
		}

		parent::delete();
	}

}
?>