/*
    Copyright (c) 2002-2007 Netlor
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

function forms_display_fieldvalues()
{
    t = document.form_field.field_type;
    if (t.value == 'textarea' || t.value == 'text' || t.value == 'file' || t.value == 'autoincrement' || t.value == 'tablelink' || t.value == 'calculation') jQuery('#fieldvalues')[0].style.display='none';
    else jQuery('#fieldvalues')[0].style.display='block';

    verifcolor = (t.value == 'color');
}

function forms_display_fieldformats()
{
    t = document.form_field.field_type;
    if (t.value == 'text') jQuery('#fieldformats')[0].style.display='block';
    else jQuery('#fieldformats')[0].style.display='none';
}

function forms_display_tablelink()
{
    t = document.form_field.field_type;
    if (t.value == 'tablelink') jQuery('#tablelink')[0].style.display='block';
    else jQuery('#tablelink')[0].style.display='none';
}

function forms_display_calculation()
{
    t = document.form_field.field_type;
    if (t.value == 'calculation') jQuery('#calculation')[0].style.display='block';
    else jQuery('#calculation')[0].style.display='none';
}

function forms_field_add_value(lst,val)
{
    if (val.value != '')
    {
        if ((verifcolor && ploopi.validatefield('couleur', val, 'color')) || !verifcolor)
        {
            if (verifcolor)
            {
                color = new ploopi.rgbcolor(val.value);
                rgbcolor = color.toHex();
                lst.options[lst.length] = new Option('', rgbcolor);
                lst.options[lst.length-1].style.backgroundColor = rgbcolor;
            }
            else lst.options[lst.length] = new Option(val.value, val.value);

        }
    }
    val.value = '';
    val.focus();
}

function forms_field_modify_value(lst,val)
{
    if ((verifcolor && ploopi.validatefield('couleur', val, 'color')) || !verifcolor)
    {
        sel = lst.selectedIndex;
        if (sel>-1)
        {
            if (verifcolor)
            {
                color = new ploopi.rgbcolor(val.value);
                rgbcolor = color.toHex();
                lst.options[sel].value = rgbcolor;
                lst.options[sel].text = '';
                lst.options[sel].style.backgroundColor = color.toHex();
            }
            else
            {
                lst.options[sel].value = val.value;
                lst.options[sel].text = val.value;
            }
        }
    }
    val.focus();
}

function forms_field_delete_value(lst)
{
    sel = lst.selectedIndex;

    if (sel < lst.length-1)
    {
        lst[sel] = lst[sel+1];
        lst.selectedIndex = sel;
    }
    else lst.length--;
}

function forms_field_move_value(lst,mv)
{
    sel = lst.selectedIndex;
    if (sel-mv>=0 && sel-mv<lst.length)
    {
        var tmp;
        tmp = lst[sel-mv].value;

        if (verifcolor)
        {
            lst[sel-mv].value = lst[sel].value;
            lst[sel-mv].style.backgroundColor = lst[sel-mv].value;

            lst[sel].value = tmp;
            lst[sel].style.backgroundColor = lst[sel].value;
        }
        else
        {
            lst[sel-mv].text = lst[sel].value;
            lst[sel-mv].value = lst[sel].value;

            lst[sel].text = tmp;
            lst[sel].value = tmp;
        }
        lst.selectedIndex=lst.selectedIndex-mv;
    }
}

function forms_deletedata(form_id, event)
{
    if (ploopi.validatefield('date',jQuery('#forms_delete_date')[0],'date'))
    {
        if (confirm('Êtes vous certain ?'))
        {
            content = ploopi.xhr.send('admin.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_delete_data&form_id='+form_id+'&form_delete_date='+jQuery('#forms_delete_date')[0].value)
            ploopi.popup.show(content,'',event,'click','forms_deletedata');
        }
    }
}

function forms_display(fuid, options)
{
    options = (options) ? '&'+options : '';

    dest = 'form_'+fuid;
    ploopi.xhr.ajaxloader(dest);
    ploopi.xhr.todiv('admin.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_display&forms_fuid='+fuid+options,dest);
}

function forms_openreply(fuid, id_reply, event)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content,350,event,'click','popup_forms_openreply');
    ploopi.xhr.todiv('admin.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_openreply&forms_fuid='+fuid+'&forms_record_id='+id_reply,'popup_forms_openreply');
}

function forms_changetype(t)
{
    if (t.value == 'app')
    {
        jQuery('#forms_type_cms')[0].style.display = 'none';
        jQuery('#forms_type_app')[0].style.display = 'block';
    }

    if (t.value == 'cms')
    {
        jQuery('#forms_type_app')[0].style.display = 'none';
        jQuery('#forms_type_cms')[0].style.display = 'block';
    }
}

function forms_graphic_type_onchange(field)
{
    jQuery('#forms_graphic_pie')[0].style.display = 'none';
    jQuery('#forms_graphic_line')[0].style.display = 'none';

    if (field.value == 'pie' || field.value == 'pie3d') jQuery('#forms_graphic_pie')[0].style.display = 'block';
    else if (field.value == 'line' || field.value == 'linec' || field.value == 'bar' || field.value == 'barc' || field.value == 'radar' || field.value == 'radarc') jQuery('#forms_graphic_line')[0].style.display = 'block';
}

/**
 * Retourne le contenu d'une liste de choix liée à une table
 * @param current champ sélectionné
 * @param fields liste des champs imbriqués
 * @param url url controleur
 */
function forms_field_tablelink_onchange(current, fields, url)
{
    var params = new Hash();
    var lastparam = false;
    var requested = '';

    fields.each(function(item) {
        if (!lastparam)
        {
            params.set('forms_params['+item+']', $('field_'+item).value);
            if (item == current) lastparam = true;
        }
        else
        {
            // Stockage de l'item demandé
            if (requested == '') requested = item;
            // Vidage des sous-listes
            while ($('field_'+item).length > 1) $('field_'+item).remove(1);
        }
    });

    params.set('forms_fields', fields.join(','));
    params.set('forms_requested', requested);

    new Ajax.Request(url, {
        method:     'get',
        parameters: params,
        encoding:   'utf-8',
        onSuccess:  function(transport, json) {
            if(null == json) {
                json = transport.responseText.evalJSON();
            }

            if (json) {
                json.each(function(item) {
                    $('field_'+requested).appendChild(newOpt = document.createElement("OPTION"));
                    newOpt.value = item;
                    newOpt.text = item;
                });
            }
        },
        onFailure: function(message) { alert(message); }
    });
}


function forms_setcolumn(f)
{
    ploopi_insertatcursor(jQuery('#field_formula')[0], 'C'+f.value);
    f.selectedIndex = 0;
    jQuery('#field_formula')[0].focus();
}

function forms_setfunction(f)
{
    ploopi_insertatcursor(jQuery('#field_formula')[0], f.value+'()');
    f.selectedIndex = 0;
    jQuery('#field_formula')[0].focus();
}

function forms_setoperator(f)
{
    ploopi_insertatcursor(jQuery('#field_formula')[0], f.value);
    jQuery('#field_formula')[0].focus();
}


function forms_savevalue(form_id, field_id, field_value)
{
    var params = new Hash();

    params.set('ploopi_op', 'forms_save_value');
    params.set('forms_form_id', form_id);
    params.set('forms_field_id', field_id);
    params.set('forms_field_value', field_value);

    new Ajax.Request('admin-light.php', {
        method:     'get',
        parameters: params,
        encoding:   'utf-8',
        onSuccess:  function(transport, json) {
        },
        onFailure: function(message) { alert('error: '+message); }
    });
}


function forms_removeaccents(s)
{
    var r=s.toLowerCase();
    r = r.replace(new RegExp("[àáâãäå]", 'g'),"a");
    r = r.replace(new RegExp("æ", 'g'),"ae");
    r = r.replace(new RegExp("ç", 'g'),"c");
    r = r.replace(new RegExp("[èéêë]", 'g'),"e");
    r = r.replace(new RegExp("[ìíîï]", 'g'),"i");
    r = r.replace(new RegExp("ñ", 'g'),"n");
    r = r.replace(new RegExp("[òóôõö]", 'g'),"o");
    r = r.replace(new RegExp("[ùúûü]", 'g'),"u");
    r = r.replace(new RegExp("[ýÿ]", 'g'),"y");
    return r;
};
