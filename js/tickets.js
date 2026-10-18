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
 * Largeur de la fenêtre de composition : plus large sous ploopi4 (destinataires en pastilles)
 */
ploopi.tickets.popupwidth = function() {
    return (document.body && document.body.classList.contains('p4-body')) ? 640 : 550;
};

/**
 * Sous ploopi4, la fenêtre s'ouvre centrée en haut de la page plutôt qu'au clic : ouverte
 * depuis le bas d'une liste, elle sortait de l'écran une fois l'éditeur chargé
 */
ploopi.tickets.popupevent = function(event) {
    return (document.body && document.body.classList.contains('p4-body')) ? null : event;
};

/**
 * Ouverture d'un popup pour envoyer un nouveau message
 */

// ploopi.tickets.create
ploopi.tickets.create = function(event, id_object, id_record, object_label, id_user, reload, x, y) {
    var data = '';

    // Valeurs encodées : un libellé d'objet (nom de document…) peut contenir & ou #
    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+encodeURIComponent(object_label);
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+encodeURIComponent(id_object);
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+encodeURIComponent(id_record);
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+encodeURIComponent(reload);
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+encodeURIComponent(id_user);

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi.popup.show('', ploopi.tickets.popupwidth(), ploopi.tickets.popupevent(event), true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_new'+data,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    // Le champ du message porte data-ploopi-editor : l'éditeur se crée seul (js/editor.js)
    request.done(function(html) {
        jQuery('#system_popupticket').html(html);
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

    ploopi.popup.show('', ploopi.tickets.popupwidth(), ploopi.tickets.popupevent(event), true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: query,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    // Le champ du message porte data-ploopi-editor : l'éditeur se crée seul (js/editor.js)
    request.done(function(html) {
        jQuery('#system_popupticket').html(html);
    });

};


/**
 * Rafraichissement de la zone indiquant le nombre de tickets non lus + alerte sur nouveau ticket
 *
 * index-quick.php n'exécute les opérations du socle qu'avec un jeton valide : la requête
 * transporte celui de la page (_PLOOPI_ENV). Le nombre est écrit dans #tpl_ploopi_tickets_new
 * (gabarits du backoffice) et diffusé par l'événement « ploopi_tickets_count » sur document,
 * pour les gabarits qui en tirent un affichage (ploopi3 : pastille).
 */

// ploopi.tickets.refresh
ploopi.tickets.refresh = function(lastnewticket, timeout, str_left, str_right) {

    var intPloopiLastNewTicket = lastnewticket;
    var boolAlert = false;

    if (typeof(str_left) == 'undefined') str_left = '';
    if (typeof(str_right) == 'undefined') str_right = '';

    (function worker() {
        jQuery.get('index-quick.php', { 'ploopi_op': 'tickets_getnum', 'ploopi_env': _PLOOPI_ENV }, function(data, textStatus, request) {

            // Session terminée : on cesse d'interroger le serveur
            if (request.getResponseHeader('Ploopi-Connected') != '1') return;

            var res = String(data).split(',');
            if (res.length == 2) {
                var nb = parseInt(res[0],10);
                var last = parseInt(res[1],10);

                jQuery('#tpl_ploopi_tickets_new').html(str_left+nb+str_right);
                jQuery(document).trigger('ploopi_tickets_count', [nb]);

                if (last > intPloopiLastNewTicket && !boolAlert)
                {
                    ploopi.tickets.alert();
                    boolAlert = true;
                }
                intPloopiLastNewTicket = last;
            }

            setTimeout(worker, timeout*1000);
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
            ploopi.tickets.selectusers_prevent(e);
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

/**
 * Fenêtre de composition de ploopi4 (include/op/tickets.php, ticket::selectusers) :
 * comportement porté par les attributs data-p4-ticket-*, délégué au document, sans
 * gestionnaire en ligne.
 * - data-p4-ticket-search : recherche de destinataires à la frappe (type : data-p4-ticket-type)
 * - data-p4-ticket-add / data-p4-ticket-id : ajoute un utilisateur ou les membres d'un groupe
 * - data-p4-ticket-remove : retire un destinataire
 * - data-p4-ticket-cancel : ferme la fenêtre
 * - data-p4-ticket-form : contrôle du titre et des destinataires avant l'envoi
 */
(function() {
    var timer = null;
    var seq = 0;

    function op(query, dest) {
        var url = 'admin-light.php?ploopi_env=' + encodeURIComponent(_PLOOPI_ENV) + '&' + query;
        return fetch(url, { credentials: 'same-origin', cache: 'no-store' })
            .then(function(r) { return r.text(); })
            .then(function(html) { var el = document.getElementById(dest); if (el) el.innerHTML = html; });
    }

    function search(input) {
        var n = ++seq;
        var res = document.getElementById('div_ticket_search_result');
        if (!res) return;
        if (input.value.trim() === '') { res.innerHTML = ''; return; }
        var body = new URLSearchParams();
        body.set('ploopi_ticket_userfilter', input.value);
        body.set('ploopi_ticket_typefilter', input.getAttribute('data-p4-ticket-type') || 'user');
        body.set('ploopi_env', _PLOOPI_ENV);
        fetch('admin-light.php?' + input.getAttribute('data-p4-ticket-url'), { method: 'POST', body: body, credentials: 'same-origin', cache: 'no-store' })
            .then(function(r) { return r.text(); })
            .then(function(html) { if (n === seq) res.innerHTML = html; });
    }

    function error(form, msg) {
        var p = form.querySelector('.p4-ticket-error');
        if (!p) { alert(msg); return; }
        p.textContent = msg;
        p.hidden = (msg === '');
    }

    document.addEventListener('input', function(e) {
        var input = e.target;
        if (!input.hasAttribute || !input.hasAttribute('data-p4-ticket-search')) return;
        clearTimeout(timer);
        timer = setTimeout(function() { search(input); }, 250);
    });

    document.addEventListener('keydown', function(e) {
        var input = e.target;
        if (e.key !== 'Enter' || !input.hasAttribute || !input.hasAttribute('data-p4-ticket-search')) return;
        // Entrée lance la recherche, sans envoyer le message
        e.preventDefault();
        clearTimeout(timer);
        search(input);
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest) return;
        var btn = e.target.closest('button[data-p4-ticket-type], [data-p4-ticket-add], [data-p4-ticket-remove], [data-p4-ticket-cancel]');
        if (!btn || !btn.closest('.p4-ticket-compose')) return;
        e.preventDefault();

        var form = btn.closest('form');
        if (form && !btn.hasAttribute('data-p4-ticket-type')) error(form, '');

        if (btn.hasAttribute('data-p4-ticket-cancel')) {
            ploopi.popup.hide('system_popupticket');
        }
        else if (btn.hasAttribute('data-p4-ticket-type')) {
            var input = document.getElementById('p4_ticket_search');
            Array.prototype.forEach.call(btn.parentNode.querySelectorAll('[data-p4-ticket-type]'), function(b) {
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
            if (input) { input.setAttribute('data-p4-ticket-type', btn.getAttribute('data-p4-ticket-type')); input.focus(); search(input); }
        }
        else if (btn.hasAttribute('data-p4-ticket-add')) {
            var param = btn.getAttribute('data-p4-ticket-add') === 'group' ? 'group_id' : 'user_id';
            op('ploopi_op=tickets_select_user&' + param + '=' + encodeURIComponent(btn.getAttribute('data-p4-ticket-id')), 'div_ticket_users_selected');
            btn.classList.add('p4-ticket-added');
        }
        else if (btn.hasAttribute('data-p4-ticket-remove')) {
            op('ploopi_op=tickets_select_user&remove_user_id=' + encodeURIComponent(btn.getAttribute('data-p4-ticket-remove')), 'div_ticket_users_selected');
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.hasAttribute || !form.hasAttribute('data-p4-ticket-form')) return;
        ploopi.editor.save();

        if (form.elements.ticket_title.value.trim() === '') {
            e.preventDefault();
            error(form, 'Le titre est obligatoire.');
            form.elements.ticket_title.focus();
            return;
        }
        if (form.getAttribute('data-p4-ticket-form') === 'new' && (!form.elements.system_ticket_ctrl_user_to || form.elements.system_ticket_ctrl_user_to.value === '')) {
            e.preventDefault();
            error(form, 'Choisissez au moins un destinataire.');
            var input = document.getElementById('p4_ticket_search');
            if (input) input.focus();
            return;
        }
        error(form, '');
    }, true);
})();
