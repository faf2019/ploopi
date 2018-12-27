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
    ploopi.popup.show(ploopi.xhr.send('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=calendar_open&selected_date='+jQuery('#'+inputfield_id)[0].value+'&inputfield_id='+inputfield_id), 192, event, false, 'ploopi_popup_calendar',null,null, true);
};
