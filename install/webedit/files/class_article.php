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
include_once './modules/webedit/class_article_backup.php';

class webedit_article extends data_object
{
	private $original_content;

	function webedit_article($type = '')
	{
		if ($type == 'draft') parent::data_object('ploopi_mod_webedit_article_draft');
		else parent::data_object('ploopi_mod_webedit_article');

		$this->original_content = '';
	}

	function open($id)
	{
		$res = parent::open($id);

		if ($res) $this->original_content = $this->fields['content'];

		return($res);
	}

	function save()
	{
		if (empty($this->fields['metatitle'])) $this->fields['metatitle'] = $this->fields['title'];

		if (empty($this->fields['timestp'])) $this->fields['timestp'] = ploopi_createtimestamp();

		$res = parent::save();
		if ($this->tablename == 'ploopi_mod_webedit_article_draft' && $this->fields['content'] != $this->original_content)
		{
			$article_backup = new webedit_article_backup();
			$article_backup->fields['id_article'] = $this->fields['id'];
			$article_backup->fields['content'] = $this->fields['content'];
			$article_backup->fields['timestp'] = ploopi_createtimestamp();
			$article_backup->setuwm();
			$article_backup->save();
		}
		return($res);
	}

	function delete()
	{
		global $db;

		$db->query("UPDATE {$this->tablename} SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");

		if ($this->tablename == 'ploopi_mod_webedit_article_draft')
		{
			$article = new webedit_article();
			$article->open($this->fields['id']);
			$article->delete();
		}

		parent::delete();
	}

	function publish()
	{
		global $db;

		if ($this->tablename == 'ploopi_mod_webedit_article_draft')
		{
			$article = new webedit_article();
			$article->open($this->fields['id']);

			$article->fields['reference'] = $this->fields['reference'];
			$article->fields['title'] = $this->fields['title'];
			$article->fields['content'] = $this->fields['content'];
			$article->fields['metatitle'] = $this->fields['metatitle'];
			$article->fields['metakeywords'] = $this->fields['metakeywords'];
			$article->fields['metadescription'] = $this->fields['metadescription'];
			$article->fields['tags'] = $this->fields['tags'];
			$article->fields['author'] = $this->fields['author'];
			$article->fields['version'] = $this->fields['version'];
			$article->fields['visible'] = $this->fields['visible'];
			$article->fields['timestp'] = $this->fields['timestp'];
			$article->fields['timestp_published'] = $this->fields['timestp_published'];
			$article->fields['timestp_unpublished'] = $this->fields['timestp_unpublished'];
			$article->fields['lastupdate_timestp'] = $this->fields['lastupdate_timestp'];
			$article->fields['lastupdate_id_user'] = $this->fields['lastupdate_id_user'];
			$article->fields['id_heading'] = $this->fields['id_heading'];
			$article->fields['id_module'] = $this->fields['id_module'];
			$article->fields['id_user'] = $this->fields['id_user'];
			$article->fields['id_workspace'] = $this->fields['id_workspace'];
			$article->fields['position'] = $this->fields['position'];
			$article->save();

			ploopi_search_create_index(_WEBEDIT_OBJECT_ARTICLE_PUBLIC, $article->fields['id'], $article->fields['title'], strip_tags(html_entity_decode($article->fields['content'])), "{$article->fields['metatitle']} {$article->fields['metakeywords']} {$article->fields['metadescription']}", true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);


			// update article positions
			$sql = 	"
					UPDATE 	ploopi_mod_webedit_article_draft draft,
							ploopi_mod_webedit_article article

					SET 	article.position = draft.position
					WHERE 	article.id = draft.id
					AND		draft.id_heading = {$this->fields['id_heading']}
					";

			$db->query($sql);

			$this->fields['status'] = 'edit';
		}
	}
}
?>
