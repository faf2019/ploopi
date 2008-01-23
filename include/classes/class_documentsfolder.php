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
include_once './include/classes/class_documentsfile.php';

class documentsfolder extends data_object
{
	function documentsfolder()
	{
		parent::data_object('ploopi_documents_folder');
		$this->fields['timestp_create'] = ploopi_createtimestamp();
		$this->fields['timestp_modify'] = $this->fields['timestp_create'];
		$this->fields['parents']=0;
	}

	function save()
	{
		if ($this->fields['id_folder'] != 0)
		{
			$docfolder_parent = new documentsfolder();
			$docfolder_parent->open($this->fields['id_folder']);
			$this->fields['parents'] = "{$docfolder_parent->fields['parents']},{$this->fields['id_folder']}";
			$ret = parent::save();
			$docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
			$docfolder_parent->save();
		}
		else $ret = parent::save();
		
		return ($ret);
	}

	function delete()
	{
		global $db;
		
		// on recherche tous les fichiers pour les supprimer
		$rs = $db->query("SELECT id FROM ploopi_documents_file WHERE id_folder = {$this->fields['id']}");
		while($row = $db->fetchrow($rs))
		{
			$file = new documentsfile();
			$file->open($row['id']);
			$file->delete();
		}

		// on recherche tous les dossiers fils pour les supprimer
		$rs = $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = {$this->fields['id']}");
		while($row = $db->fetchrow($rs))
		{
			$folder = new documentsfolder();
			$folder->open($row['id']);
			$folder->delete();
		}
		
		parent::delete();
		
		if ($this->fields['id_folder'] != 0)
		{
			$docfolder_parent = new documentsfolder();
			$docfolder_parent->open($this->fields['id_folder']);
			$docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
			$docfolder_parent->save();
		}
		
	}
}
