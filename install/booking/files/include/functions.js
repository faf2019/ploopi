/*
    Copyright (c) 2008 Ovensia
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

var element_checked = false;

function booking_element_check()
{
    element_checked = !element_checked;
    for (i=0;i<$$('input.booking_element_checkbox').length;i++) $$('input.booking_element_checkbox')[i].checked = element_checked;
}

function booking_element_delete(type_element)
{
    element_list = '';
    for (i=0;i<$$('input.booking_element_checkbox').length;i++)
    {
        if ($$('input.booking_element_checkbox')[i].checked)
        {
            if (element_list != '') element_list += ',';
            element_list += $$('input.booking_element_checkbox')[i].value;
        }
    }

    if (element_list != '')
    {
        if (confirm('Êtes-vous certain de vouloir supprimer ces éléments ?'))
        {
            document.location.href = 'admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_'+type_element+'_delete&booking_element_list='+element_list;
        }
    }
}

function booking_element_add(type_element, e, width)
{
    if (typeof(width) == 'undefined') width = 500;
    ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_'+type_element+'_add','popup_'+type_element);
}

function booking_element_open(type_element, id, e, width, center)
{
    if (typeof(width) == 'undefined') width = 450;
    if (typeof(center) == 'undefined') center = false;
    
    if (center) 
    {
        var posy = 250;
        if (e.pageY) posy = e.pageY;
        else if (e.clientY) posy = e.clientY + document.body.scrollTop;
        ploopi_showpopup(ploopi_ajaxloader_content, width, null, true, 'popup_'+type_element, null, posy);
    }
    else ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_'+type_element+'_open&booking_element_id='+id, 'popup_'+type_element);
}

function booking_event_unlock(id)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_event_unlock&booking_element_id='+id, 'popup_event');
}

/**
 * VALIDATION
 */

function booking_resourcetype_validate(form)
{
    if (ploopi_validatefield('Intitulé',form.booking_resourcetype_name, 'string'))
        return true;

    return false;
}

function booking_resource_validate(form)
{
    var col = document.getElementsByName('booking_resourceworkspace_id_workspace[]');
    var checked = false;
    for (var i=0;i<col.length;i++) checked =  checked || col[i].checked;

    if (ploopi_validatefield('Intitulé',form.booking_resource_name, 'string'))
    if (ploopi_validatefield('Type de ressource',form.booking_resource_id_resourcetype, 'selected'))
    if (checked) return true;
    else alert('Vous devez sélectionner au moins un espace de travail gestionnaire.');

    return false;
}

/**
 * AJOUT D'UN EVENEMENT
 */
 
function booking_event_add(e, date)
{
    ploopi_showpopup(ploopi_ajaxloader_content, '450', e, 'click', 'popup_event');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_event_add&booking_resource_date='+date, 'popup_event');   
}

function booking_event_validate(form)
{
    if (ploopi_validatefield('Ressource',form.booking_event_id_resource, 'selected'))
    if (ploopi_validatefield('Objet',form.booking_event_object, 'string'))
    if (ploopi_validatefield('Date de début',form._booking_event_timestp_begin_d, 'date'))
    if (ploopi_validatefield('Date de fin',form._booking_event_timestp_end_d, 'date'))
        return true;

    return false;
}

/**
 * NAVIGATION CALENDRIER
 */
 
function booking_nextmonth()
{
    if ($('booking_month').value < 12) $('booking_month').selectedIndex++;
    else {$('booking_month').selectedIndex = 0; $('booking_year').selectedIndex++;}

    $('booking_form_view').onsubmit();
}

function booking_prevmonth()
{
    if ($('booking_month').value > 1) $('booking_month').selectedIndex--;
    else {$('booking_month').selectedIndex = 11; $('booking_year').selectedIndex--;}

    $('booking_form_view').onsubmit();
}

function booking_nextweek()
{
    if ($('booking_week').selectedIndex < $('booking_week').length - 1) $('booking_week').selectedIndex++;
    else {$('booking_week').selectedIndex = 0; $('booking_year').selectedIndex++;}

    $('booking_form_view').onsubmit();
}

function booking_prevweek()
{
    if ($('booking_week').selectedIndex > 0) $('booking_week').selectedIndex--;
    else {$('booking_week_previousyear').value = '1'; $('booking_year').selectedIndex--;}

    $('booking_form_view').onsubmit();
}

function booking_nextday()
{
    if ($('booking_day').selectedIndex < $('booking_day').length - 1)
    {
        $('booking_day').selectedIndex++;
        $('booking_form_view').onsubmit();
    }
    else
    {
        $('booking_day').selectedIndex = 0;
        booking_nextmonth();
    }
}

function booking_prevday()
{
    if ($('booking_day').selectedIndex > 0)
    {
        $('booking_day').selectedIndex--;
        $('booking_form_view').onsubmit();
    }
    else
    {
        $('booking_week_previousmonth').value = '1'
        booking_prevmonth();
    }
}

/**
 * FRONTOFFICE
 */
 
function booking_front_event_add(e, date, moduleid)
{
    ploopi_showpopup(ploopi_ajaxloader_content, '450', e, 'click', 'popup_event');
    ploopi_xmlhttprequest_todiv('index-light.php', 'ploopi_op=booking_event_add&booking_moduleid='+moduleid+'&booking_resource_date='+date, 'popup_event');
    
}
 
function booking_front_element_open(type_element, id, e, moduleid)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 450, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('index-light.php','ploopi_op=booking_'+type_element+'_open&booking_element_id='+id+'&booking_moduleid='+moduleid, 'popup_'+type_element);
}
 
