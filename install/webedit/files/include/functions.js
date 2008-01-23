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
function webedit_showheading(hid,str)
{
	elt = $('webedit_plus'+hid);

	if (elt.style.background.indexOf('plusbottom') != -1) elt.style.background = elt.style.background.replace('plusbottom', 'minusbottom');
	else  if (elt.style.background.indexOf('minusbottom')  != -1) elt.style.background = elt.style.background.replace('minusbottom', 'plusbottom');
	else  if (elt.style.background.indexOf('plus')  != -1) elt.style.background = elt.style.background.replace('plus', 'minus');
	else  if (elt.style.background.indexOf('minus')  != -1) elt.style.background = elt.style.background.replace('minus', 'plus');


	if (elt = $('webedit_dest'+hid))
	{
		if (elt.style.display == 'none')
		{
			if (elt.innerHTML.length < 20) ploopi_xmlhttprequest_todiv('admin-light.php','op=xml_detail_heading&hid='+hid+'&str='+str,'','webedit_dest'+hid);
			elt.style.display='block';
		}
		else
		{
			elt.style.display='none';
		}
	}
}

function webedit_heading_validate(form)
{
	if (ploopi_validatefield('Libellé', form.webedit_heading_label, 'string'))
		return true;

	return false;
}

function webedit_article_validate(form, article_type, article_status, validator)
{
	next = true;

	if (article_type == 'draft')
	{
		if (form.webedit_article_status.value != article_status && form.webedit_article_status.value == 'wait' && !validator)
		{
			// confirm sending tickets on waiting validation
			next = confirm('Cette action va envoyer\nune demande de publication\naux validateurs de cette rubrique\n\nÊtes-vous certain de vouloir continuer ?');
		}
	}

	if (next)
	{
		var fck_instance = $('webedit_frame_editor').contentWindow.FCKeditorAPI.GetInstance('fck_webedit_article_content');

		// get fckeditor content
		$('fck_webedit_article_content').value = fck_instance.GetData(true)

		if (ploopi_validatefield('Titre', form.webedit_article_title, 'string'))
		if ($('fck_webedit_article_content').value == '')
		{
			if (confirm("ATTENTION !! Cet article semble vide.\n\n Si vous l'enregistrez, vous perdrez tout son contenu\n\nÊtes-vous malgré tout certain de vouloir enregistrer cet article ?")) return true;
		}
		else return true;
	}

	return false;
}


function webedit_article_keywordscomplete(kw)
{
	var listekw = $('webedit_article_metakeywords').value;

	if (listekw.length>0 && listekw[listekw.length-1] != ' ') listekw += ' ';

	listekw += kw;

	$('webedit_article_metakeywords').value = listekw;
}


function webedit_backup_reload()
{
	if (confirm("ATTENTION !! Cette opération va remplacer le contenu de l'article\n\nÊtes-vous certain de vouloir continuer ?"))
	{
		c = ploopi_xmlhttprequest('admin-light.php','ploopi_op=webedit_getbackup&backup_timestp='+$('article_backup').value+'&backup_id_article='+$('articleid').value);

		var fck_instance = $('webedit_frame_editor').contentWindow.FCKeditorAPI.GetInstance('fck_webedit_article_content');
		fck_instance.SetData(c);
	}
}
