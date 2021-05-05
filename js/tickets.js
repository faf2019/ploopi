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

ploopi.tickets = {};

/**
 * Ouverture d'un popup pour envoyer un nouveau message
 */

// ploopi.tickets.create
ploopi.tickets.create = function(event, id_object, id_record, object_label, id_user, reload, x, y) {
    var data = '';

    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+object_label;
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+id_object;
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+id_record;
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+reload;
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+id_user;

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi.popup.show('', 550, event, true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_new'+data,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        var script = document.createElement('script');
        script.onload = function () {
            jQuery('#system_popupticket').html(html);
            CKEDITOR.replace( 'fck_ticket_message', {
                customConfig: _PLOOPI_BASEPATH+'/js/ckeditor/ck_config.js'
            });
        };
        script.src = './vendor/ckeditor/ckeditor/ckeditor.js';
        document.head.appendChild(script);
    });

};

ploopi.tickets.open = function(event, query) {
    var data = '';

    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+object_label;
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+id_object;
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+id_record;
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+reload;
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+id_user;

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi.popup.show('', 550, event, true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: query,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        var script = document.createElement('script');
        script.onload = function () {
            jQuery('#system_popupticket').html(html);
            CKEDITOR.replace( 'fck_ticket_message', {
                customConfig: _PLOOPI_BASEPATH+'/js/ckeditor/ck_config.js'
            });
        };
        script.src = './vendor/ckeditor/ckeditor/ckeditor.js';
        document.head.appendChild(script);
    });

};


/**
 * Rafraichissement de la zone indiquant le nombre de tickets non lus + alerte sur nouveau ticket
 */

// ploopi.tickets.refresh
ploopi.tickets.refresh = function(lastnewticket, timeout, str_left, str_right) {

    var intPloopiLastNewTicket = lastnewticket;
    var boolAlert = false;

    if (typeof(str_left) == 'undefined') str_left = '';
    if (typeof(str_right) == 'undefined') str_right = '';

    (function worker() {
        jQuery.get('index-quick.php?ploopi_op=tickets_getnum', function(data, textStatus, request) {
            console.log(data);
            console.log(request.getResponseHeader('Ploopi-Connected'));

            if (request.getResponseHeader('Ploopi-Connected') == '1') {

                var res = data.split(',');
                if (res.length == 2) {
                    var nb = parseInt(res[0],10);
                    var last = parseInt(res[1],10);

                    $('#tpl_ploopi.tickets.create').html(str_left+nb+str_right);

                    if (last > intPloopiLastNewTicket && !boolAlert)
                    {
                        ploopi.tickets.alert();
                        boolAlert = true;
                    }
                    intPloopiLastNewTicket = last;
                }

                setTimeout(worker, timeout*1000);
            }
            else {
                console.log('Not connected');
            }
        });
    })();
};

/**
 * Recherche de destinataires pour un message
 */
// ploopi.tickets.select_users
ploopi.tickets.select_users = function(query, filtertype, filter, dest) {

    var request = jQuery.ajax({
        type: 'POST',
        url: 'admin-light.php?'+query,
        data: {
            'ploopi_ticket_userfilter': filter,
            'ploopi_ticket_typefilter': filtertype,
            'ploopi_env': _PLOOPI_ENV
        },
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        jQuery('#'+dest).html(html);
    });
};


/**
 * Affiche un popup d'alerte à la réception de nouveaux messages
 */

// ploopi.tickets.alert
ploopi.tickets.alert = function()
{
    ploopi.popup.show('', 350, null, true, 'popup_tickets_new_alert', 0, 200);
    ploopi.xhr.ajaxloader('popup_tickets_new_alert');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_alert', 'popup_tickets_new_alert');
};


/**
 * Recherche utilisateur
 */

ploopi.tickets.selectusers_init = function()
{
    jQuery('#ploopi_ticket_userfilter')[0].onkeyup = ploopi.tickets.selectusers_keypress;
    jQuery('#ploopi_ticket_userfilter')[0].onkeypress = ploopi.tickets.selectusers_keypress;
};

ploopi.tickets.selectusers_prevent = function(e)
{
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
};

ploopi.tickets.selectusers_keypress = function(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    switch(e.keyCode)
    {
        case 9: case 13:
            ploopi_tickets_selectusers_prevent(e);
            ploopi.event.dispatch_onclick('ploopi_ticket_search_btn');
        break;

        default:
        break;
    }
};

/*
 * Contrôle spécifique au ticket, vérif qu'au moins un destinataire est sélectionné
 */
function ploopi_ticket_validateTo(field_label, field_object)
{
    var ok = true;
    var msg = new String();
    var reg = new RegExp("<FIELD_LABEL>","gi");

    if (field_object)
    {

        field_value = field_object.value;

        ok = (field_value.replace(/(^\s*)|(\s*$)/g,'').length > 0)
    }
    else
    {
        ok = false;
    }

    if (!ok)
    {
        msg = lstmsg[4];
        alert(msg.replace(reg,field_label));
    }

    return (ok);

}
