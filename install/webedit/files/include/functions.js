/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
    Copyright (c) 2009-2010 HeXad
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
        if (article_status == 'wait') return true;
        else
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
        c = ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_getbackup&backup_timestp='+$('article_backup').value+'&backup_id_article='+$('articleid').value);

        var fck_instance = $('webedit_frame_editor').contentWindow.FCKeditorAPI.GetInstance('fck_webedit_article_content');
        fck_instance.SetData(c);
    }
}

/**
 * Sélectionne un article ou une rubrique en redirection sur une rubrique
 */

function webedit_select_article_or_heading(id, title, e)
{
    $('webedit_heading_linkedpage').value = id;
    $('linkedpage_displayed').value = title;
    ploopi_checkbox_click(e, 'heading_content_type_article_redirect');
    ploopi_hidepopup('webedit_popup_selectredirect');
}

/**
 * Sélectionne une nouvelle rubrique parent pour un article
 */

function webedit_select_heading(id, label, e)
{
    $('webedit_article_id_heading').value = id;
    $('heading_displayed').value = label;
    ploopi_hidepopup('webedit_popup_selectheading');
}

function webedit_switch_display_type(value)
{
    if ($('webedit_display_type').value != value) // nouvelle valeur
    {
        if (confirm('Attention, changer d\'affichage n\'enregistre pas vos dernières modifications'))
        {
            $('webedit_display_type').value = value;
            $('webedit_form_display_type').submit();
        }
    }
}

function webedit_stats_open(article_id, heading_id, e)
{
    query = 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_article_stats';

    if (typeof(article_id) != 'undefined' && article_id != null) query += '&webedit_article_id='+article_id;
    if (typeof(heading_id) != 'undefined' && heading_id != null) query += '&webedit_heading_id='+heading_id;

    ploopi_showpopup(
        ploopi_xmlhttprequest(
            'admin-light.php',
            query,
            false,
            false,
            'POST'
        ),
        600,
        e,
        false,
        'popup_webedit_article_stats'
    );
}

function webedit_stats_refresh(article_id, heading_id, year, month)
{
    query = 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_article_stats';

    if (typeof(article_id) != 'undefined' && article_id != null) query += '&webedit_article_id='+article_id;
    if (typeof(heading_id) != 'undefined' && heading_id != null) query += '&webedit_heading_id='+heading_id;
    if (typeof(year) != 'undefined') query += '&webedit_yearsel='+year;
    if (typeof(month) != 'undefined') query += '&webedit_monthsel='+month;

    ploopi_innerHTML(
        'popup_webedit_article_stats',
        ploopi_xmlhttprequest(
            'admin-light.php',
            query,
            false,
            false,
            'POST'
        )
    );
}

function webedit_comment_publish(id_comment, id_article, publish)
{
	publish = (publish == true) ? '1' : '0';
	
	new Ajax.Request('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_publish&id_comment='+id_comment+'&publish='+publish, {
		method: 'get',
		onSuccess: function() {
			new Ajax.Updater('webeditcomment_'+id_article, 'admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_refresh&id_article='+id_article, { method: 'get' });
		}
	});
}

function webedit_comment_delete(id_comment,id_article)
{
    if (confirm('Êtes vous certain de vouloir supprimer ce commentaire ?'))
    {
    	new Ajax.Request('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_delete&id_comment='+id_comment, {
    		method: 'get',
    		onSuccess: function() {
    			new Ajax.Updater('webeditcomment_'+id_article, 'admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_refresh&id_article='+id_article, { method: 'get' });
    		}
    	});
    }
}

