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

// Fenêtre d'un événement : 580 px sous ploopi4 (formulaire en grille, .p4-evform), 450 sinon
function booking_event_width()
{
    return document.querySelector('.p4-caltb') ? 580 : 450;
}

function booking_element_open(type_element, id, e, width, center)
{
    if (typeof(width) == 'undefined') width = type_element == 'event' ? booking_event_width() : 450;
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
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, booking_event_width(), e, 'click', 'popup_event');
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

// Période affichée : formulaire de la vue envoyé en XHR
function booking_view_submit()
{
    ploopi.xhr.submit(jQuery('#booking_form_view')[0], 'booking_main');
}

function booking_nextmonth()
{
    if (jQuery('#booking_month')[0].value < 12) jQuery('#booking_month')[0].selectedIndex++;
    else {jQuery('#booking_month')[0].selectedIndex = 0; jQuery('#booking_year')[0].selectedIndex++;}

    booking_view_submit();
}

function booking_prevmonth()
{
    if (jQuery('#booking_month')[0].value > 1) jQuery('#booking_month')[0].selectedIndex--;
    else {jQuery('#booking_month')[0].selectedIndex = 11; jQuery('#booking_year')[0].selectedIndex--;}

    booking_view_submit();
}

function booking_nextweek()
{
    if (jQuery('#booking_week')[0].selectedIndex < jQuery('#booking_week')[0].length - 1) jQuery('#booking_week')[0].selectedIndex++;
    else {jQuery('#booking_week')[0].selectedIndex = 0; jQuery('#booking_year')[0].selectedIndex++;}

    booking_view_submit();
}

function booking_prevweek()
{
    if (jQuery('#booking_week')[0].selectedIndex > 0) jQuery('#booking_week')[0].selectedIndex--;
    else {jQuery('#booking_week_previousyear')[0].value = '1'; jQuery('#booking_year')[0].selectedIndex--;}

    booking_view_submit();
}

function booking_nextday()
{
    if (jQuery('#booking_day')[0].selectedIndex < jQuery('#booking_day')[0].length - 1)
    {
        jQuery('#booking_day')[0].selectedIndex++;
        booking_view_submit();
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
        booking_view_submit();
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



// Sous-ressources de la ressource choisie (rendues par booking_subresources_html) : les
// autres sont masquées et décochées
function booking_resource_onchange(res)
{
    jQuery('#booking_subresources [data-booking-sr]').each(function() {
        var show = this.getAttribute('data-booking-sr') == res.value;
        this.style.display = show ? '' : 'none';
        if (!show) jQuery(this).find('input[type=checkbox]').prop('checked', false);
    });
}

function booking_rt_autocheck(id_rt) {
    jQuery('#booking_rt'+id_rt).prop('checked', jQuery('.booking_rt'+id_rt+':checked').length == jQuery('.booking_rt'+id_rt).length);
}


/**
 * Comportements par attributs, sans gestionnaire en ligne (CSP stricte) : actions
 * appelées par data-ploopi-action (js/actions.js du socle), formulaires et listes
 */
ploopi.actions = ploopi.actions || {};

jQuery.each(['booking_prevmonth', 'booking_nextmonth', 'booking_prevweek', 'booking_nextweek', 'booking_prevday', 'booking_nextday'], function(i, name) {
    ploopi.actions[name] = function(el, event) { window[name](); };
});

ploopi.actions.booking_element_check = function(el, event) { event.preventDefault(); booking_element_check(); };
ploopi.actions.booking_element_delete = function(el, event, type) { event.preventDefault(); booking_element_delete(type); };
ploopi.actions.booking_element_add = function(el, event, type) { event.preventDefault(); booking_element_add(type, event); };
ploopi.actions.booking_element_open = function(el, event, type, id) { event.preventDefault(); booking_element_open(type, id, event); };
ploopi.actions.booking_event_add = function(el, event, date) { booking_event_add(event, date); };
ploopi.actions.booking_front_event_add = function(el, event, date, moduleid) { booking_front_event_add(event, date, moduleid); };
ploopi.actions.booking_front_element_open = function(el, event, type, id, moduleid) { event.preventDefault(); booking_front_element_open(type, id, event, moduleid); };
ploopi.actions.booking_alert = function(el, event, message) { alert(message); };
ploopi.actions.booking_resource_onchange = function(el, event) { booking_resource_onchange(el); };

// Taille du planning
ploopi.actions.booking_size = function(el, event, url) {
    ploopi.xhr.todiv(url, 'booking_size='+encodeURIComponent(el.value), 'booking_main');
};

// Date de début recopiée dans la date de fin (toujours, ou seulement si elle est vide)
ploopi.actions.booking_copydate = function(el, event, target, onlyIfEmpty) {
    var dest = document.getElementById(target);
    if (dest && (!onlyIfEmpty || dest.value == '')) dest.value = el.value;
};

// Décision sur une réservation : couleur du détail
ploopi.actions.booking_bgcolor = function(el, event, target, color) {
    var dest = document.getElementById(target);
    if (dest) dest.style.backgroundColor = color;
};

// Formulaires envoyés en XHR vers la zone data-booking-submit
jQuery(document).on('submit', 'form[data-booking-submit]', function(e) {
    e.preventDefault();
    ploopi.xhr.submit(this, this.getAttribute('data-booking-submit'));
});

// Formulaires contrôlés avant l'envoi (data-booking-validate)
var booking_validators = {
    resourcetype: function(f) { return booking_resourcetype_validate(f); },
    resource: function(f) { return booking_resource_validate(f); },
    subresource: function(f) { return booking_subresource_validate(f); },
    event: function(f) { return booking_event_validate(f); },
    event_detail: function(f) { return booking_event_validate_2(f); }
};

jQuery(document).on('submit', 'form[data-booking-validate]', function(e) {
    var func = booking_validators[this.getAttribute('data-booking-validate')];
    if (func && !func(this)) e.preventDefault();
});

// Liste des ressources : case d'un type (toutes ses ressources), case d'une ressource
jQuery(document).on('change', 'input[data-booking-rt]', function() {
    jQuery('input.booking_rt'+this.getAttribute('data-booking-rt')).prop('checked', this.checked);
    jQuery(this.form).trigger('submit');
});

jQuery(document).on('change', 'input[data-booking-resource]', function() {
    booking_rt_autocheck(this.getAttribute('data-booking-resource'));
    jQuery(this.form).trigger('submit');
});

// Case d'un type cochée si toutes ses ressources le sont, sur la page et le contenu inséré
ploopi.popup.addenhancer(function(node) {
    jQuery(node).find('input[data-booking-rt]').addBack('input[data-booking-rt]').each(function() {
        booking_rt_autocheck(this.getAttribute('data-booking-rt'));
    });
});
