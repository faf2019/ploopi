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

ploopi.calendar = {};

/**
 * Ne plus utiliser cette fonction, utiliser de préférence l'appel PHP : ploopi_open_calendar()
 */
ploopi.calendar.open = function(inputfield_id, event) {
    ploopi.popup.show(ploopi.xhr.send('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=calendar_open&selected_date='+jQuery('#'+inputfield_id)[0].value+'&inputfield_id='+inputfield_id), document.querySelector('.p4-app') ? 272 : 192, event, false, 'ploopi_popup_calendar',null,null, true);
};

/**
 * Choix d'une date (date::open_calendar, opération calendar_open) : ouverture, jour
 * choisi et « Aujourd'hui » par attributs, sans gestionnaire en ligne (CSP)
 */
jQuery(document).on('click', '[data-ploopi-calendar]', function(event) {
    event.preventDefault();
    var id = this.getAttribute('data-ploopi-calendar');
    var field = document.getElementById(id);
    ploopi.xhr.topopup(parseInt(this.getAttribute('data-ploopi-width'), 10) || 192, event, 'ploopi_popup_calendar', this.getAttribute('data-ploopi-url'), 'selected_date='+encodeURIComponent(field ? field.value : '')+'&inputfield_id='+encodeURIComponent(id), 'POST');
});

jQuery(document).on('click', '[data-ploopi-calendar-day]', function(event) {
    event.preventDefault();
    var calendar = jQuery(this).closest('[data-ploopi-calendar-field]');
    var id = calendar.attr('data-ploopi-calendar-field');
    var field = id ? document.getElementById(id) : null;
    if (field) field.value = this.getAttribute('data-ploopi-calendar-day');
    ploopi.popup.hide('ploopi_popup_calendar');
    if (field) ploopi.event.dispatch_onchange(id);
});
