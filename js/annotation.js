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

function ploopi_annotation(id_annotation)
{
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation&id_annotation='+id_annotation, '', 'ploopiannotation_'+id_annotation);
}

var tag_timer;
var tag_search;
var tag_results = new Array();

var tag_last_array = new Array();
var tag_new_array = new Array();

var tag_lastedit = '';
var tag_modified = -1

function ploopi_annotation_tag_init(id_annotation)
{
    $('ploopi_annotationtags_'+id_annotation).onkeyup = ploopi_annotation_tag_keyup;
    $('ploopi_annotationtags_'+id_annotation).onkeypress = ploopi_annotation_tag_keypress;
}

function ploopi_annotation_tag_search(id_annotation, search)
{
    clearTimeout(tag_timer);
    tag_search = search;
    tag_timer = setTimeout("ploopi_annotation_tag_searchtimeout('"+id_annotation+"')", 100);
}

function ploopi_annotation_tag_searchtimeout(id_annotation)
{
    // replace(/(^\s*)|(\s*$)/g,'') = TRIM
    list_tags = tag_search.split(' ');

    if (list_tags.length>0) ploopi_xmlhttprequest_tofunction('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_searchtags&tag='+list_tags[list_tags.length-1],ploopi_annotation_tag_display,id_annotation);
}

function ploopi_annotation_tag_display(result,ticket)
{
    if (result != '')
    {
        tag_results = new Array();

        splited_result = result.split('|');
        tagstoprint = '';

        for (i=0;i<splited_result.length;i++)
        {
            detail = splited_result[i].split(';');
            if (tagstoprint != '') tagstoprint += ' ';
            if (i==0) tagstoprint += '<b>';
            tagstoprint += '<a href="javascript:ploopi_annotation_tag_complete(\''+ticket+'\','+i+')">'+detail[0]+'</a> ('+detail[1]+')';
            if (i==0) tagstoprint += '</b>';
            tag_results[i] = detail[0];
        }

        $('tagsfound_'+ticket).innerHTML = tagstoprint;
    }
    else
    {
        $('tagsfound_'+ticket).innerHTML = '';
        tag_results = new Array();
    }
}

function ploopi_annotation_tag_prevent(e)
{
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
}



function ploopi_annotation_tag_keypress(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    switch(e.keyCode)
    {
        case 38: case 40:
            prevent(e)
        break
        case 9:
            ploopi_annotation_tag_prevent(e)
        break
        case 13:
            ploopi_annotation_tag_prevent(e)
        break
        default:
            tag_lastedit = $(src.id).value;
        break;
    }
}

function ploopi_annotation_tag_keyup(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target; // get source field
    idrecord = src.id.split('_')[2]; // get id record from source field id

    switch(e.keyCode)
    {
        case 38: case 40:
            prevent(e);
        break
        case 9:
            ploopi_annotation_tag_complete(idrecord);
            ploopi_annotation_tag_prevent(e);
        break
        case 13:
            ploopi_annotation_tag_complete(idrecord);
            ploopi_annotation_tag_prevent(e);
        break
        case 35: //end
        case 36: //home
        case 39: //right
        case 37: //left
        //case 32: //space
        break
        default:
            tag_last_array = new Array();
            tag_new_array = new Array();

            tag_last_array = tag_lastedit.split(' ');
            tag_new_array = $(src.id).value.split(' ');

            tag_modified = -1;
            for (i=0;i<tag_new_array.length;i++)
            {
                if (tag_new_array[i] != tag_last_array[i])
                {
                    if (tag_modified == -1) tag_modified = i;
                    else tag_modified = -2
                }
            }
            if (tag_modified>=0) ploopi_annotation_tag_search(idrecord, tag_new_array[tag_modified]);
        break;
    }
}

function ploopi_annotation_tag_complete(idrecord, idtag)
{
    if (!(idtag>=0)) idtag = 0;

    if (tag_results[idtag])
    {
        tag_new_array[tag_modified] = tag_results[idtag];

        taglist = '';
        for (i=0;i<tag_new_array.length;i++)
        {
            if (taglist != '') taglist += ' ';
            taglist += tag_new_array[i]
        }

        $('ploopi_annotationtags_'+idrecord).value = taglist.replace(/(^\s*)|(\s*$)/g,'')+' ';
        $('tagsfound_'+idrecord).innerHTML = '';
    }

    tag_results = new Array();
}

function ploopi_annotation_delete(id_annotation, id)
{
    if (confirm('Êtes vous certain de vouloir supprimer cette annotation ?')) ploopi_xmlhttprequest('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_delete&ploopi_annotation_id='+id);
    ploopi_annotation(id_annotation);
}

function ploopi_annotation_validate(form)
{
    if (ploopi_validatefield('Titre',form.ploopi_annotationtags,"string")) return true;

    return false;
}
