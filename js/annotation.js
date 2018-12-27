/*
    Copyright (c) 2007-2018 Ovensia
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

/* ANNOTATIONS FUNCTIONS */
ploopi.annotations = {};

ploopi.annotations.tag_timer = null;
ploopi.annotations.search = '';
ploopi.annotations.tag_lastedit = '';
ploopi.annotations.tag_results = new Array();
ploopi.annotations.tag_new_array = new Array();
ploopi.annotations.tag_modified = -1;

ploopi.annotations.display = function(id_annotation)
{
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation&id_annotation='+id_annotation, 'ploopiannotation_'+id_annotation);
};

ploopi.annotations.tag_init = function(id_annotation) {
    /*
    $('#ploopi_annotationtags_'+id_annotation)[0].onkeyup = ploopi.annotations.tag_keyup;
    $('#ploopi_annotationtags_'+id_annotation)[0].onkeypress = ploopi.annotations.tag_keypress;
    */

    //$('#ploopi_annotationtags_'+id_annotation).bind('keypress', self.tag_keypress);

    jQuery('#ploopi_annotationtags_'+id_annotation).bind('keyup', function(e) {
        src = e.target; // get source field

        idrecord = src.id.split('_')[2]; // get id record from source field id

        switch(e.keyCode)
        {
            case 38: case 40:
                ploopi.annotations.tag_prevent(e);
            break
            case 9:
                ploopi.annotations.tag_complete(idrecord);
                ploopi.annotations.tag_prevent(e);
            break
            case 13:
                ploopi.annotations.tag_complete(idrecord);
                ploopi.annotations.tag_prevent(e);
            break
            case 35: //end
            case 36: //home
            case 39: //right
            case 37: //left
            //case 32: //space
            break
            default:
                tag_last_array = new Array();
                ploopi.annotations.tag_new_array = new Array();

                tag_last_array = ploopi.annotations.tag_lastedit.split(' ');
                ploopi.annotations.tag_new_array = src.value.split(' ');

                ploopi.annotations.tag_modified = -1;
                for (i=0;i<ploopi.annotations.tag_new_array.length;i++)
                {
                    if (ploopi.annotations.tag_new_array[i] != tag_last_array[i])
                    {
                        if (ploopi.annotations.tag_modified == -1) ploopi.annotations.tag_modified = i;
                        else ploopi.annotations.tag_modified = -2
                    }
                }
                if (ploopi.annotations.tag_modified>=0) ploopi.annotations.tag_search(idrecord, ploopi.annotations.tag_new_array[ploopi.annotations.tag_modified]);
            break;
        }

    });

};

ploopi.annotations.tag_search = function(id_annotation, search) {
    clearTimeout(ploopi.annotations.tag_timer);
    ploopi.annotations.search = search;
    ploopi.annotations.tag_timer = setTimeout("ploopi.annotations.tag_searchtimeout('"+id_annotation+"')", 100);
};

ploopi.annotations.tag_searchtimeout = function(id_annotation) {
    list_tags = ploopi.annotations.search.split(' ');

    if (list_tags.length>0) ploopi.xhr.tocb('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_searchtags&tag='+list_tags[list_tags.length-1], ploopi.annotations.tag_display, id_annotation);
};

ploopi.annotations.tag_display = function(result,ticket) {
    if (result != '')
    {
        ploopi.annotations.tag_results = new Array();

        splited_result = result.split('|');
        tagstoprint = '';

        for (i=0;i<splited_result.length;i++)
        {
            detail = splited_result[i].split(';');
            if (tagstoprint != '') tagstoprint += ' ';
            if (i==0) tagstoprint += '<b>';
            tagstoprint += '<a href="javascript:ploopi.annotations.tag_complete(\''+ticket+'\','+i+')">'+detail[0]+'</a> ('+detail[1]+')';
            if (i==0) tagstoprint += '</b>';
            ploopi.annotations.tag_results[i] = detail[0];
        }

        $('tagsfound_'+ticket).innerHTML = tagstoprint;
    }
    else
    {
        $('tagsfound_'+ticket).innerHTML = '';
        ploopi.annotations.tag_results = new Array();
    }
};

ploopi.annotations.tag_prevent = function(e) {
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
};

ploopi.annotations.tag_keypress = function(e) {
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    console.log(self.tag_lastedit);

    switch(e.keyCode)
    {
        case 38: case 40:
            ploopi.annotations.tag_prevent(e)
        break
        case 9:
            ploopi.annotations.tag_prevent(e)
        break
        case 13:
            ploopi.annotations.tag_prevent(e)
        break
        default:
            ploopi.annotations.tag_lastedit = $(src.id).value;
        break;
    }
};


ploopi.annotations.tag_complete = function(idrecord, idtag) {
    if (!(idtag>=0)) idtag = 0;

    if (ploopi.annotations.tag_results[idtag])
    {
        ploopi.annotations.tag_new_array[ploopi.annotations.tag_modified] = ploopi.annotations.tag_results[idtag];

        taglist = '';
        for (i=0;i<ploopi.annotations.tag_new_array.length;i++)
        {
            if (taglist != '') taglist += ' ';
            taglist += ploopi.annotations.tag_new_array[i]
        }

        $('ploopi_annotationtags_'+idrecord).value = taglist.replace(/(^\s*)|(\s*$)/g,'')+' ';
        $('tagsfound_'+idrecord).innerHTML = '';
    }

    ploopi.annotations.tag_results = new Array();
};

ploopi.annotations.remove = function(id_annotation, id) {
    if (confirm('Êtes vous certain de vouloir supprimer cette annotation ?')) ploopi.xhr.send('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_delete&ploopi_annotation_id='+id);
    ploopi.annotations.display(id_annotation);
};

ploopi.annotations.validate = function(form) {
    if (ploopi.validatefield('Titre', form.ploopi_annotationtags, "string")) return true;

    return false;
};
