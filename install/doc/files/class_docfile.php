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

include_once './modules/doc/class_docfilehistory.php';
include_once './modules/doc/class_docmeta.php';
include_once './modules/doc/class_dockeyword.php';
include_once './modules/doc/class_dockeywordfile.php';

class docfile extends data_object
{
	/**
	* Class constructor
	*
	* @param int $connection_id
	* @access public
	**/

	var $oldname;
	var $tmpfile;
	var $draftfile;

	function docfile()
	{
		parent::data_object('ploopi_mod_doc_file');
		$this->fields['id_user'] = 0;
		$this->fields['timestp_create'] = ploopi_createtimestamp();
		$this->fields['timestp_modify'] = $this->fields['timestp_create'];
		$this->fields['description']='';
		$this->fields['size'] = 0;
		$this->fields['version'] = 1;
		$this->fields['nbclick'] = 0;

		$this->oldname = '';
		$this->tmpfile = null;
		$this->draftfile = null;
	}

	function open($id)
	{
		$res = parent::open($id);
		$this->oldname = $this->fields['name'];
		return($res);
	}


	function openmd5($md5id)
	{
		global $db;

		$db->query("SELECT id FROM ploopi_mod_doc_file WHERE md5id = '".$db->addslashes($md5id)."'");
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

			if ($this->tmpfile == 'none' && $this->draftfile == 'none') $error = _DOC_ERROR_EMPTYFILE;

			if ($this->fields['size'] > _PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;

			if (!$error)
			{
				$this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

				$id = parent::save();

				$this->fields['md5id'] = md5(sprintf("%s_%d_%d",$this->fields['timestp_create'],$id,$this->fields['version']));

				parent::save();

				$basepath = $this->getbasepath();
				$filepath = $this->getfilepath();

				if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

				if (!$error && is_writable($basepath))
				{
					if ($this->draftfile != null)
					{
						if (!rename($this->draftfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
					}
					elseif ($this->tmpfile != null)
					{
						if (!rename($this->tmpfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
					}

					if (!$error)
					{
						$this->parse();
						parent::save();
						chmod($filepath, 0640);
					}
				}
				else $error = _DOC_ERROR_FILENOTWRITABLE;
			}
		}
		else // update
		{

			if ((!empty($this->tmpfile) && $this->tmpfile != 'none') || (!empty($this->draftfile) && $this->draftfile != 'none'))
			{
				$this->fields['version']++;

				if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;

				if (!$error)
				{
					$this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

					$basepath = $this->getbasepath();
					$filepath = $this->getfilepath();

					//$filepath_vers = $basepath._PLOOPI_SEP."{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}";

					if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

					if (!$error)
					{
						// on déplace l'ancien fichier
						/*
						if (file_exists($filepath) && is_writable($basepath))
						{
							rename($filepath, $filepath_vers);
							//$this->createhistory();
						}
						*/

						// on copie le nouveau
						if (!$error && is_writable($basepath))
						{
							if ($this->draftfile != 'none')
							{
								if (rename($this->draftfile, $filepath))
								{
									$this->parse();
									chmod($filepath, 0640);
								}
								else $error = _DOC_ERROR_FILENOTWRITABLE;
							}
							if ($this->tmpfile != 'none')
							{
								if (rename($this->tmpfile, $filepath))
								{
									$this->parse();
									chmod($filepath, 0640);
								}
								else $error = _DOC_ERROR_FILENOTWRITABLE;
							}
						}
						else $error = _DOC_ERROR_FILENOTWRITABLE;
					}
				}

				$this->fields['timestp_modify'] = ploopi_createtimestamp();

				$this->oldname = $this->fields['name'];
			}

			// renommage
			if ($this->oldname != $this->fields['name'])
			{
				// renommage avec modification de type
				if (($newext = substr(strrchr($this->fields['name'], "."),1)) != $this->fields['extension'])
				{
					$basepath = $this->getbasepath();
					$filepath = $this->getfilepath();
					$newfilepath = substr($filepath,0,strlen($filepath)-strlen($this->fields['extension'])).$newext;

					if (file_exists($filepath) && is_writable($basepath))
					{
						rename($filepath, $newfilepath);
						$this->fields['extension'] = $newext;
						parent::save();
					}
					else $error = _DOC_ERROR_FILENOTWRITABLE;
				}
				else
				{
					parent::save();
				}
			}
			else
			{
				parent::save();
			}
		}

		if ($this->fields['id_folder'] != 0)
		{
			$docfolder_parent = new docfolder();
			$docfolder_parent->open($this->fields['id_folder']);
			$docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
			$docfolder_parent->save();
		}

		return($error);
	}


	function delete()
	{
		$filepath = $this->getfilepath();
		if (file_exists($filepath)) @unlink($filepath);

		$basepath = $this->getbasepath();
		if (file_exists($basepath)) @rmdir($basepath);

		parent::delete();

		if ($this->fields['id_folder'] != 0)
		{
			$docfolder_parent = new docfolder();
			$docfolder_parent->open($this->fields['id_folder']);
			$docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
			$docfolder_parent->save();
		}

		ploopi_search_remove_index(_DOC_OBJECT_FILE, $this->fields['id']);
	}


	function getbasepath_deprecated()
	{
		$basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.$this->fields['id'];
		ploopi_makedir($basepath);
		return($basepath);
	}

	function getbasepath()
	{
		$basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
		ploopi_makedir($basepath);
		return($basepath);
	}

	function getfilepath_deprecated()
	{
		return($this->getbasepath_deprecated()._PLOOPI_SEP."{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}");
	}

	function getfilepath()
	{
		return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}");
	}

	function getwebpath()
	{
		return(_PLOOPI_WEBPATHDATA."doc-{$this->fields['id_module']}/{$this->fields['id']}/{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}");
	}

	function gethistory()
	{
		global $db;

		$rs = $db->query(	"
							SELECT 		h.*,
										f.md5id,
										u.login,
										u.firstname,
										u.lastname

							FROM 		ploopi_mod_doc_file_history h

							INNER JOIN 	ploopi_mod_doc_file f
							ON 			h.id_docfile = f.id

							INNER JOIN 	ploopi_user u
							ON 			h.id_user_modify = u.id

							WHERE 		h.id_docfile = {$this->fields['id']}

							ORDER BY 	h.version DESC
							");

		$history = array();

		while($row = $db->fetchrow($rs))
		{
			$history[$row['version']] = $row;
		}

		return($history);
	}

	function createhistory()
	{
		$docfilehistory = new docfilehistory();
		$docfilehistory->fields['id_docfile'] = $this->fields['id'];
		$docfilehistory->fields['version'] = $this->fields['version'];
		$docfilehistory->fields['name'] = $this->fields['name'];
		$docfilehistory->fields['description'] = $this->fields['description'];
		$docfilehistory->fields['timestp_create'] = $this->fields['timestp_create'];
		$docfilehistory->fields['timestp_modify'] = $this->fields['timestp_modify'];
		$docfilehistory->fields['id_user_modify'] = $this->fields['id_user_modify'];
		$docfilehistory->fields['size'] = $this->fields['size'];
		$docfilehistory->fields['extension'] = $this->fields['extension'];
		$docfilehistory->fields['id_module'] = $this->fields['id_module'];
		$docfilehistory->save();
	}

	function parse($debug = false)
	{
		global $db;

		global $ploopi_timer;
		if ($debug) printf("<br />START: %0.2f",$ploopi_timer->getexectime()*1000);

		if (!ini_get('safe_mode')) @set_time_limit(0);

		$metakeywords_str = '';

		$allowedmeta_list = array(	'Camera Make', 		//jpg
									'Camera Model', 	//jpg
									'Comment', 			//jpg
									'Producer',			//png
									'Creator',			//pdf,png
									'Author',			//doc
									'Title'				//pdf
								);

		$res_txt = '';

		// on recherche les parsers adaptés au format du fichier
		$sql = 	"
				select		lcase(f.extension) as ext,
							p.path
				from 		ploopi_mod_doc_file f
				left join	ploopi_mod_doc_parser p on lcase(f.extension) = lcase(p.extension)
				where		f.id = {$this->fields['id']}
				";

		$res = $db->query($sql);

		$fields = $db->fetchrow($res);

		$path = $this->getfilepath();
		if (file_exists($path))
		{
			/* GESTION/EXTRACTION DES METADONNEES */

			$this->fields['metadata'] = '';

			switch($fields['ext'])
			{
				case 'pdf':
					$exec = "pdfinfo \"{$path}\"";
				break;

				case 'jpg':
				case 'jpeg':
					$exec = "jhead \"{$path}\"";
				break;

				default:
					$exec = "hachoir-metadata --quiet --raw \"{$path}\"";
				break;
			}

			$res_txt = "<div style=\"background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\"><b>{$this->fields['name']}</b> : {$exec}</div>";

			exec($exec,$array_result);
			if ($debug) printf("<br />META: %0.2f",$ploopi_timer->getexectime()*1000);

			// delete existing meta for current file
			$db->query("DELETE FROM ploopi_mod_doc_meta WHERE id_file = {$this->fields['id']}");

			// parse doc metadata
			foreach($array_result as $value)
			{
				if ($value!="")
				{
					foreach(split("\n",$value) as $line)
					{
						unset($meta_information);

						switch($fields['ext'])
						{
							case 'pdf':
								ereg("([A-Za-z0-9_. ]*):(.*)", $value, $meta_information);
							break;

							case 'jpg':
							case 'jpeg':
								ereg("([A-Za-z0-9_. ]*) : (.*)", $value, $meta_information);
							break;

							default:
								if ($value != 'Metadata:') ereg("- ([A-Za-z0-9_. ]*): (.*)", $value, $meta_information);
							break;
						}

						if (!empty($meta_information))
						{
							$res_txt .= "<div style=\"background-color:#e0f0e0;border-bottom:1px solid #c0c0c0;padding:1px;\"><b>{$meta_information['1']}</b> = {$meta_information['2']}</div>";
							$docmeta = new docmeta();
							$docmeta->fields['id_file'] = $this->fields['id'];
							$docmeta->fields['meta'] = trim(ucwords(str_replace('_',' ',$meta_information['1'])));
							$docmeta->fields['value'] = trim($meta_information['2']);
							$docmeta->save();

							if (in_array($docmeta->fields['meta'],$allowedmeta_list)) $metakeywords_str .= ' '.$docmeta->fields['value'];
						}
					}
					//$this->fields['metadata'] .= strtolower($value)."\n";
					$this->fields['metadata'] .= $value."\n";
				}

			}
			unset($array_result);

			if ($debug) printf("<br />META 2: %0.2f",$ploopi_timer->getexectime()*1000);

			/* EXTRACTION DES CONTENUS */

			$content = '';

			if (!is_null($fields['path']))
			{
				// parse doc content
				$exec = str_replace('%f',"\"{$path}\"",$fields['path']);

				$res_txt .= "<div style=\"background-color:#ffe0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\">{$exec}</div>";

				exec($exec,$array_result);
				if ($debug) printf("<br />CONTENT: %0.2f",$ploopi_timer->getexectime()*1000);

				foreach($array_result as $key => $value)
				{
					if ($value!="")
					{
						switch($fields['ext'])
						{
							case 'odg':
							case 'odt':
							case 'ods':
							case 'odp':
							case 'sxw':
								$value = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
							break;
						}
						$content .= $value.' ';
					}
				}

				unset($array_result);
			}

			$res_txt .= "<div style=\"background-color:#e0e0f0;border-bottom:1px solid #c0c0c0;padding:1px;\">".ploopi_strcut($content,200)."</div>";

			$metakeywords_str .= " {$this->fields['name']} {$this->fields['description']}";

			ploopi_search_create_index(_DOC_OBJECT_FILE, $this->fields['id'], $this->fields['name'], $content, $metakeywords_str, true);

		}
		else $res_txt .= "<div><strong>erreur de fichier sur {$path}</strong></div>";

		return($res_txt);
	}

	/*

	function parse_OK()
	{
		if (!ini_get('safe_mode')) @set_time_limit(0);

		global $db;

		$param_maxwordspercent = $_SESSION['ploopi']['modules'][$this->fields['id_module']]['doc_index_maxwordspercent'];
		$param_minoccpercent = $_SESSION['ploopi']['modules'][$this->fields['id_module']]['doc_index_minoccpercent'];

		if (!is_numeric($param_maxwordspercent)) $param_maxwordspercent = 100;
		if (!is_numeric($param_minoccpercent)) $param_minoccpercent = 0.1;

		$metakeywords_str = '';

		$allowedmeta_list = array(	'Camera Make', 		//jpg
									'Camera Model', 	//jpg
									'Comment', 			//jpg
									'Producer',			//png
									'Creator',			//pdf,png
									'Author',			//doc
									'Title'				//pdf
								);

		$res_txt = '';

		// on recherche les parsers adaptés au format du fichier
		$sql = 	"
				select		lcase(f.extension) as ext,
							p.path
				from 		ploopi_mod_doc_file f
				left join	ploopi_mod_doc_parser p on lcase(f.extension) = lcase(p.extension)
				where		f.id = {$this->fields['id']}
				";

		$res = $db->query($sql);

		$fields = $db->fetchrow($res);

		$path = $this->getfilepath();
		if (file_exists($path))
		{
			// extract doc metadata
			$this->fields['metadata'] = '';

			switch($fields['ext'])
			{
				case 'pdf':
					$exec = "pdfinfo \"{$path}\"";
				break;

				case 'jpg':
				case 'jpeg':
					$exec = "jhead \"{$path}\"";
				break;

				default:
					$exec = "hachoir-metadata --quiet --raw \"{$path}\"";
				break;
			}

			$res_txt = "<div style=\"background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\"><b>{$this->fields['name']}</b> : {$exec}</div>";

			exec($exec,$array_result);

			// delete existing meta for current file
			$db->query("DELETE FROM ploopi_mod_doc_meta WHERE id_file = {$this->fields['id']}");

			// parse doc metadata
			foreach($array_result as $value)
			{
				if ($value!="")
				{
					foreach(split("\n",$value) as $line)
					{
						unset($meta_information);

						switch($fields['ext'])
						{
							case 'pdf':
								ereg("([A-Za-z0-9_. ]*):(.*)", $value, $meta_information);
							break;

							case 'jpg':
							case 'jpeg':
								ereg("([A-Za-z0-9_. ]*) : (.*)", $value, $meta_information);
							break;

							default:
								if ($value != 'Metadata:') ereg("- ([A-Za-z0-9_. ]*): (.*)", $value, $meta_information);
							break;
						}

						if (!empty($meta_information))
						{
							$res_txt .= "<div style=\"background-color:#e0f0e0;border-bottom:1px solid #c0c0c0;padding:1px;\"><b>{$meta_information['1']}</b> = {$meta_information['2']}</div>";
							$docmeta = new docmeta();
							$docmeta->fields['id_file'] = $this->fields['id'];
							$docmeta->fields['meta'] = trim(ucwords(str_replace('_',' ',$meta_information['1'])));
							$docmeta->fields['value'] = trim($meta_information['2']);
							$docmeta->save();

							if (in_array($docmeta->fields['meta'],$allowedmeta_list)) $metakeywords_str .= ' '.$docmeta->fields['value'];
						}
					}
					//$this->fields['metadata'] .= strtolower($value)."\n";
					$this->fields['metadata'] .= $value."\n";
				}

			}
			unset($array_result);

			$content = '';

			if (!is_null($fields['path']))
			{
				// parse doc content
				$exec = str_replace('%f',"\"{$path}\"",$fields['path']);

				$res_txt .= "<div style=\"background-color:#ffe0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\">{$exec}</div>";

				exec($exec,$array_result);

				foreach($array_result as $key => $value)
				{
					if ($value!="")
					{
						switch($fields['ext'])
						{
							case 'odg':
							case 'odt':
							case 'ods':
							case 'odp':
							case 'sxw':
								$value = utf8_decode($value);
							break;
						}
						$content .= $value.' ';
					}
				}

				unset($array_result);
			}

			$res_txt .= "<div style=\"background-color:#e0e0f0;border-bottom:1px solid #c0c0c0;padding:1px;\">".ploopi_strcut($content,200)."</div>";

			// delete existing keywords for current file
			$db->query("DELETE FROM ploopi_mod_doc_keyword_file WHERE id_file = {$this->fields['id']}");

			list($keywords, $this->fields['words_indexed'], $this->fields['words_overall']) = ploopi_getwords($content);

			$metakeywords_str .= " {$this->fields['name']} {$this->fields['description']}";

			$nb = current($keywords);
			$kw = key($keywords);
			$maxkw = (sizeof($keywords)*$param_maxwordspercent)/100;
			$occ_p100 = ($nb / $this->fields['words_overall'])*100;
			for ($i = 1; $i <= $maxkw && $occ_p100 >= $param_minoccpercent; $i++)
			{
				$db->query("SELECT id FROM ploopi_mod_doc_keyword WHERE twoletters = '".$db->addslashes(substr($kw,0,2))."' AND keyword = '".$db->addslashes($kw)."' AND id_module = {$this->fields['id_module']}");
				if ($db->numrows())
				{
					$row = $db->fetchrow();
					$dockeywordfile = new dockeywordfile();
					$dockeywordfile->open($this->fields['id'], $row['id']);
					if (!isset($dockeywordfile->fields['weight'])) $dockeywordfile->fields['weight'] = 0;
					$dockeywordfile->fields['weight'] += $nb;
					$dockeywordfile->fields['id_module'] = $this->fields['id_module'];
					$dockeywordfile->save();
				}
				else
				{
					$dockeyword = new dockeyword();
					$dockeyword->fields['keyword'] = $kw;
					$dockeyword->fields['id_module'] = $this->fields['id_module'];
					$id_kw = $dockeyword->save();

					$dockeywordfile = new dockeywordfile();
					$dockeywordfile->fields['id_file'] = $this->fields['id'];
					$dockeywordfile->fields['id_keyword'] = $id_kw;
					$dockeywordfile->fields['weight'] = $nb;
					$dockeywordfile->fields['id_module'] = $this->fields['id_module'];
					$dockeywordfile->save();
				}
				$nb = next($keywords);
				$kw = key($keywords);
				$occ_p100 = ($nb / $this->fields['words_overall'])*100;
			}


		}
		else $res_txt .= "<div><strong>erreur de fichier sur {$path}</strong></div>";

		list($metakeywords) = ploopi_getwords($metakeywords_str);

		foreach($metakeywords as $kw => $nb)
		{
			$db->query("SELECT id FROM ploopi_mod_doc_keyword WHERE twoletters = '".$db->addslashes(substr($kw,0,2))."' and keyword = '".$db->addslashes($kw)."' ");
			if ($db->numrows())
			{
				$row = $db->fetchrow();
				$dockeywordfile = new dockeywordfile();
				$dockeywordfile->open($this->fields['id'], $row['id']);
				if (!isset($dockeywordfile->fields['weight'])) $dockeywordfile->fields['weight'] = 0;
				$dockeywordfile->fields['meta'] = 1;
				$dockeywordfile->fields['id_module'] = $this->fields['id_module'];
				$dockeywordfile->save();
			}
			else
			{
				$dockeyword = new dockeyword();
				$dockeyword->fields['keyword'] = $kw;
				$dockeyword->fields['id_module'] = $this->fields['id_module'];
				$id_kw = $dockeyword->save();

				$dockeywordfile = new dockeywordfile();
				$dockeywordfile->fields['id_file'] = $this->fields['id'];
				$dockeywordfile->fields['id_keyword'] = $id_kw;
				$dockeywordfile->fields['meta'] = 1;
				$dockeywordfile->fields['id_module'] = $this->fields['id_module'];
				$dockeywordfile->save();
			}
		}

		return($res_txt);
	}
	*/
}
