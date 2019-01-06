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

var element_checked = false;

function booking_element_check()
{
    element_checked = !element_checked;
    for (i=0;i<jQuery('input.booking_element_checkbox').length;i++) jQuery('input.booking_element_checkbox')[i].checked = element_checked;
}

function booking_element_delete(type_element)
{
    element_list = '';
    for (i=0;i<jQuery('input.booking_element_checkbox').length;i++)
    {
        if (jQuery('input.booking_element_checkbox')[i].checked)
        {
            if (element_list != '') element_list += ',';
            element_list += jQuery('input.booking_element_checkbox')[i].value;
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
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_'+type_element+'_add','popup_'+type_element);
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
        ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, null, true, 'popup_'+type_element, null, posy);
    }
    else ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_'+type_element+'_open&booking_element_id='+id, 'popup_'+type_element);
}

function booking_event_unlock(id)
{
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_event_unlock&booking_element_id='+id, 'popup_event');
}

/**
 * VALIDATION
 */

function booking_resourcetype_validate(form)
{
    if (ploopi.validatefield('Intitulé',form.booking_resourcetype_name, 'string'))
        return true;

    return false;
}

function booking_resource_validate(form)
{
    var col = document.getElementsByName('booking_resourceworkspace_id_workspace[]');
    var checked = false;
    for (var i=0;i<col.length;i++) checked =  checked || col[i].checked;

    if (ploopi.validatefield('Intitulé',form.booking_resource_name, 'string'))
    if (ploopi.validatefield('Type de ressource',form.booking_resource_id_resourcetype, 'selected'))
    if (checked) return true;
    else alert('Vous devez sélectionner au moins un espace de travail gestionnaire.');

    return false;
}

function booking_subresource_validate(form)
{
    if (ploopi.validatefield('Intitulé',form.booking_subresource_name, 'string'))
    if (ploopi.validatefield('Ressource',form.booking_subresource_id_resource, 'selected'))
        return true;

    return false;
}


/**
 * AJOUT D'UN EVENEMENT
 */

function booking_event_add(e, date)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, '450', e, 'click', 'popup_event');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=booking_event_add&booking_resource_date='+date, 'popup_event');
}

/**
 * Contrôle de validation pour le formulaire d'ajout d'événement
 */
function booking_event_validate(form)
{
    if (ploopi.validatefield('Ressource',form.booking_event_id_resource, 'selected'))
    if (ploopi.validatefield('Objet',form.booking_event_object, 'string'))
    if (ploopi.validatefield('Date de début',form._booking_event_timestp_begin_d, 'date'))
    if (ploopi.validatefield('Date de fin',form._booking_event_timestp_end_d, 'date')) {

        // Contrôle des dates
        var datestr_b = form._booking_event_timestp_begin_d.value.split('/');
        var date_b = new Date(datestr_b[1]+'/'+datestr_b[0]+'/'+datestr_b[2]).getTime();

        var datestr_e = form._booking_event_timestp_end_d.value.split('/');
        var date_e = new Date(datestr_e[1]+'/'+datestr_e[0]+'/'+datestr_e[2]).getTime();

        if (date_e > date_b) return true
        else if (date_e == date_b) {
            time_b = parseInt(form._booking_event_timestp_begin_h.value, 10)*100 + parseInt(form._booking_event_timestp_begin_m.value,10);
            time_e = parseInt(form._booking_event_timestp_end_h.value, 10)*100 + parseInt(form._booking_event_timestp_end_m.value,10);
            if (time_e > time_b) return true
        }

        alert('La date de fin doit être postérieure à la date de début');
    }

    return false;
}

/**
 * Contrôle de validation pour le formulaire de modification d'événement
 */
function booking_event_validate_2(form)
{
    var ok = true;

    jQuery('.booking_date').each(function(id, item) {
        console.log(item);
        console.log(item.dataset.id);

        if (ok) {
            // Contrôle des dates
            var datestr_b = jQuery('#_booking_event_timestp_begin_d'+item.dataset.id).val().split('/');
            var date_b = new Date(datestr_b[1]+'/'+datestr_b[0]+'/'+datestr_b[2]).getTime();

            var datestr_e = jQuery('#_booking_event_timestp_end_d'+item.dataset.id).val().split('/');
            var date_e = new Date(datestr_e[1]+'/'+datestr_e[0]+'/'+datestr_e[2]).getTime();

            if (date_e < date_b) ok = false;
            else if (date_e == date_b) {
                time_b = parseInt(jQuery('#_booking_event_timestp_begin_h'+item.dataset.id).val(), 10)*100 + parseInt(jQuery('#_booking_event_timestp_begin_m'+item.dataset.id).val(),10);
                time_e = parseInt(jQuery('#_booking_event_timestp_end_h'+item.dataset.id).val(), 10)*100 + parseInt(jQuery('#_booking_event_timestp_end_m'+item.dataset.id).val(),10);
                if (time_e <= time_b) ok = false;
            }
        }
    });

    if (!ok) {
        alert('La date de fin doit être postérieure à la date de début');
    }

    return ok;

}

/**
 * NAVIGATION CALENDRIER
 */

function booking_nextmonth()
{
    if (jQuery('#booking_month')[0].value < 12) jQuery('#booking_month')[0].selectedIndex++;
    else {jQuery('#booking_month')[0].selectedIndex = 0; jQuery('#booking_year')[0].selectedIndex++;}

    jQuery('#booking_form_view')[0].onsubmit();
}

function booking_prevmonth()
{
    if (jQuery('#booking_month')[0].value > 1) jQuery('#booking_month')[0].selectedIndex--;
    else {jQuery('#booking_month')[0].selectedIndex = 11; jQuery('#booking_year')[0].selectedIndex--;}

    jQuery('#booking_form_view')[0].onsubmit();
}

function booking_nextweek()
{
    if (jQuery('#booking_week')[0].selectedIndex < jQuery('#booking_week')[0].length - 1) jQuery('#booking_week')[0].selectedIndex++;
    else {jQuery('#booking_week')[0].selectedIndex = 0; jQuery('#booking_year')[0].selectedIndex++;}

    jQuery('#booking_form_view')[0].onsubmit();
}

function booking_prevweek()
{
    if (jQuery('#booking_week')[0].selectedIndex > 0) jQuery('#booking_week')[0].selectedIndex--;
    else {jQuery('#booking_week_previousyear')[0].value = '1'; jQuery('#booking_year')[0].selectedIndex--;}

    jQuery('#booking_form_view')[0].onsubmit();
}

function booking_nextday()
{
    if (jQuery('#booking_day')[0].selectedIndex < jQuery('#booking_day')[0].length - 1)
    {
        jQuery('#booking_day')[0].selectedIndex++;
        jQuery('#booking_form_view')[0].onsubmit();
    }
    else
    {
        jQuery('#booking_day')[0].selectedIndex = 0;
        booking_nextmonth();
    }
}

function booking_prevday()
{
    if (jQuery('#booking_day')[0].selectedIndex > 0)
    {
        jQuery('#booking_day')[0].selectedIndex--;
        jQuery('#booking_form_view')[0].onsubmit();
    }
    else
    {
        jQuery('#booking_week_previousmonth')[0].value = '1'
        booking_prevmonth();
    }
}

/**
 * FRONTOFFICE
 */

function booking_front_event_add(e, date, moduleid)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, '450', e, 'click', 'popup_event');
    ploopi.xhr.todiv('index-light.php', 'ploopi_op=booking_event_add&booking_moduleid='+moduleid+'&booking_resource_date='+date, 'popup_event');

}

function booking_front_element_open(type_element, id, e, moduleid)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 450, e, 'click', 'popup_'+type_element);
    ploopi.xhr.todiv('index-light.php','ploopi_op=booking_'+type_element+'_open&booking_element_id='+id+'&booking_moduleid='+moduleid, 'popup_'+type_element);
}



function booking_resource_onchange(res)
{
    jQuery('#booking_subresources > p').each(function(item) {
        item.remove();
    });

    jQuery.each(booking_json_sr, function(i, row) {
        if (row.id_resource == res.value) {
            jQuery('#booking_subresources').append(
                '<p class="ploopi_checkbox"><label for="booking_sr_'+row.id+'">'+row.name+':</label><input type="checkbox" name="booking_sr[]" value="'+row.id+'" id="booking_sr_'+row.id+'" /></p>'
            );
        }
    });
}

function booking_rt_autocheck(id_rt) {
    jQuery('#booking_rt'+id_rt).prop('checked', jQuery('.booking_rt'+id_rt+':checked').length == jQuery('.booking_rt'+id_rt).length);
}
