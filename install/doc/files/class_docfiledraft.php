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
include_once './modules/doc/class_docfile.php';

class docfiledraft extends data_object
{
	function docfiledraft()
	{
		parent::data_object('ploopi_mod_doc_file_draft');
		$this->fields['timestp_create'] = ploopi_createtimestamp();
	}

	function openmd5($md5id)
	{
		global $db;

		$db->query("SELECT id FROM ploopi_mod_doc_file_draft WHERE md5id = '".$db->addslashes($md5id)."'");
		if ($fields = $db->fetchrow()) return(parent::open($fields['id']));
		else return(false);
	}


	function save()
	{
		global $db;
		$error = 0;
		if (isset($this->fields['folder'])) unset($this->fields['folder']);

		if (!isset($this->oldname)) $this->oldname = '';

		if ($this->new) // insert
		{

			if ($this->tmpfile == 'none') $error = _DOC_ERROR_EMPTYFILE;

			if ($this->fields['size'] > _PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;

			if (!$error)
			{
				$this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

				$id = parent::save();

				$this->fields['md5id'] = md5(sprintf("%s_%d_1",$this->fields['timestp_create'],$id));

				parent::save();

				$basepath = $this->getbasepath();
				$filepath = $this->getfilepath();

				if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

				if (!$error && is_writable($basepath) && rename($this->tmpfile, $filepath))
				{
					chmod($filepath, 0640);
				}
				else $error = _DOC_ERROR_FILENOTWRITABLE;
			}

		}

		return($error);
	}


	function getbasepath()
	{
		$basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.'drafts'._PLOOPI_SEP.$this->fields['id'];
		ploopi_makedir($basepath);
		return($basepath);
	}

	function getfilepath()
	{
		return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id']}.{$this->fields['extension']}");
	}

	function publish()
	{
		$docfile = new docfile();

		if ($this->fields['id_docfile'])
		{
			if ($docfile->open($this->fields['id_docfile']))
			{
				$docfile->createhistory();
				$docfile->fields['md5id'] = $this->fields['md5id'];
				$docfile->fields['name'] = $this->fields['name'];
				$docfile->fields['size'] = $this->fields['size'];
				$docfile->fields['description'] = $this->fields['description'];
				$docfile->fields['extension'] = $this->fields['extension'];
				$docfile->fields['id_user_modify'] = $this->fields['id_user_modify'];
				$docfile->fields['timestp_modify'] = $this->fields['timestp_create'];
				$docfile->draftfile = $this->getfilepath();
				$docfile->save();
			}
		}
		else
		{
			$docfile->fields = $this->fields;
			unset($docfile->fields['id']);
			unset($docfile->fields['id_docfile']);
			$docfile->fields['timestp_modify'] = $docfile->fields['timestp_create'];
			$docfile->fields['version'] = 1;
			$docfile->draftfile = $this->getfilepath();
			$docfile->save();
		}

		$this->delete();
	}

}
