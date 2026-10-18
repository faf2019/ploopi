/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2018 Ovensia
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
    if (ploopi.validatefield('Libellé', form.webedit_heading_label, 'string'))
        return true;

    return false;
}

/**
 * Éditeur de l'article (TinyMCE, js/editor.js), ou null s'il n'est pas prêt
 *
 * @param boolean bloc true : bloc, édité dans la page ; false : article, édité dans la
 *                     page du site (iframe webedit_frame_editor)
 */
function webedit_editor(bloc)
{
    try
    {
        var win = bloc ? window : jQuery('#webedit_frame_editor')[0].contentWindow;
        var editor = win.ploopi.editor.get('editor');
        if (editor) return editor;
    }
    catch (e) {}

    alert("L'éditeur n'est pas encore prêt, veuillez réessayer dans un instant.");
    return null;
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

            // Contenu de l'éditeur (page du site en iframe, js/editor.js)
            var editor = webedit_editor();
            if (!editor) return false;
            jQuery('#fck_webedit_article_content')[0].value = editor.getContent();

            if (ploopi.validatefield('Titre', form.webedit_article_title, 'string'))
            if (jQuery('#fck_webedit_article_content')[0].value == '')
            {
                if (confirm("ATTENTION !! Cet article semble vide.\n\n Si vous l'enregistrez, vous perdrez tout son contenu\n\nÊtes-vous malgré tout certain de vouloir enregistrer cet article ?")) return true;
            }
            else return true;
        }
    }

    return false;
}


function webedit_bloc_validate(form, article_type, article_status, validator)
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
            var editor = webedit_editor(true);
            if (!editor) return false;
            editor.save();

            if (ploopi.validatefield('Identifiant', form.webedit_article_title, 'string'))
            if (editor.getContent() == '')
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
    var listekw = jQuery('#webedit_article_metakeywords')[0].value;

    if (listekw.length>0 && listekw[listekw.length-1] != ' ') listekw += ' ';

    listekw += kw;

    jQuery('#webedit_article_metakeywords')[0].value = listekw;
}


function webedit_backup_reload(bloc)
{
    if (confirm("ATTENTION !! Cette opération va remplacer le contenu de l'article\n\nÊtes-vous certain de vouloir continuer ?"))
    {
        var content = ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_getbackup&backup_timestp='+jQuery('#article_backup')[0].value+'&backup_id_article='+jQuery('#articleid')[0].value);

        var editor = webedit_editor(typeof(bloc) != 'undefined' && bloc);
        if (editor) editor.setContent(content);
    }
}

function webedit_bloc_backup_reload()
{
    if (confirm("ATTENTION !! Cette opération va remplacer le contenu de l'article\n\nÊtes-vous certain de vouloir continuer ?"))
    {
        c = ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_getbackup&backup_timestp='+jQuery('#article_backup')[0].value+'&backup_id_article='+jQuery('#articleid')[0].value);

        var editor = webedit_editor(true);
        if (editor) editor.setContent(c);
    }
}

/**
 * Sélectionne un article ou une rubrique en redirection sur une rubrique
 */

function webedit_select_article_or_heading(id, title, e)
{
    jQuery('#webedit_heading_linkedpage')[0].value = id;
    jQuery('#linkedpage_displayed')[0].value = title;
    ploopi.checkbox_click(e, 'heading_content_type_article_redirect');
    ploopi.popup.hide('webedit_popup_selectredirect');
}

/**
 * Sélectionne une nouvelle rubrique parent pour un article
 */

function webedit_select_heading(id, label, e)
{
    jQuery('#webedit_article_id_heading')[0].value = id;
    jQuery('#heading_displayed')[0].value = label;
    ploopi.popup.hide('webedit_popup_selectheading');
}

function webedit_switch_display_type(value)
{
    if (jQuery('#webedit_display_type')[0].value != value) // nouvelle valeur
    {
        if (confirm('Attention, changer d\'affichage n\'enregistre pas vos dernières modifications'))
        {
            jQuery('#webedit_display_type')[0].value = value;
            jQuery('#webedit_form_display_type')[0].submit();
        }
    }
}

function webedit_stats_open(article_id, heading_id, e)
{
    query = 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_article_stats';

    if (typeof(article_id) != 'undefined' && article_id != null) query += '&webedit_article_id='+article_id;
    if (typeof(heading_id) != 'undefined' && heading_id != null) query += '&webedit_heading_id='+heading_id;

    ploopi.popup.show(
        ploopi.xhr.send(
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
        ploopi.xhr.send(
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

    jQuery.get('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_publish&id_comment='+id_comment+'&publish='+publish, function() {
        jQuery('#webeditcomment_'+id_article).load('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_refresh&id_article='+id_article);
    });
}

function webedit_comment_delete(id_comment,id_article)
{
    if (confirm('Êtes vous certain de vouloir supprimer ce commentaire ?'))
    {
        jQuery.get('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_delete&id_comment='+id_comment, function() {
            jQuery('#webeditcomment_'+id_article).load('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_comment_refresh&id_article='+id_article);
        });
    }
}
