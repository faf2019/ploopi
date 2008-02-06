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
include_once('./modules/system/class_annotation_tag.php');
include_once('./modules/system/class_tag.php');

class annotation extends data_object
{

	/**
	* Class constructor
	*
	* @param int $idconnexion
	* @access public
	**/
	function annotation()
	{
		parent::data_object('ploopi_annotation','id');
	}

	function save()
	{
		global $db;

		$this->fields['id_element'] = ploopi_search_generate_id($this->fields['id_module'], $this->fields['id_object'], $this->fields['id_record']);

		$id_annotation = parent::save();

		$tags = preg_split('/(,)|( )/',$this->tags,-1,PREG_SPLIT_NO_EMPTY);
		foreach($tags as $tag)
		{
			$tag = trim($tag);

			$tag_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($tag));

			$select = "SELECT id FROM ploopi_tag WHERE tag = '".$db->addslashes($tag)."' AND id_user = {$this->fields['id_user']}";
			$rs = $db->query($select);
			if (!($row = $db->fetchrow($rs)))
			{
				$objtag = new tag();
				$objtag->fields['tag'] = $tag;
				$objtag->fields['tag_clean'] = $tag_clean;
				$objtag->fields['id_user'] = $this->fields['id_user'];
				$id_tag = $objtag->save();
			}
			else $id_tag = $row['id'];

			$annotation_tag = new annotation_tag();
			$annotation_tag->fields['id_tag'] = $id_tag;
			$annotation_tag->fields['id_annotation'] = $id_annotation;
			$annotation_tag->save();
		}

		return($id_annotation);
	}


	function delete()
	{
		global $db;

		$select = 	"
					SELECT 	*
					FROM 	ploopi_annotation_tag
					WHERE 	id_annotation = {$this->fields['id']}
					";

		$rs = $db->query($select);
		while ($row = $db->fetchrow($rs))
		{
			$annotation_tag = new annotation_tag();
			$annotation_tag->open($this->fields['id'], $row['id_tag']);
			$annotation_tag->delete();
		}

		parent::delete();
	}
}
?>
