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

ploopi.subscription = {};

ploopi.subscription.display = function(ploopi_subscription_id, next)
{
    if (typeof(next) == 'undefined') next = '';
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=subscription&ploopi_subscription_id='+ploopi_subscription_id+'&next='+next, 'ploopi_subscription_'+ploopi_subscription_id);
};

ploopi.subscription.checkaction = function (id_subscription, id_action)
{
    var ck = (id_action == -1) ? jQuery('#ploopi_subscription_unsubscribe')[0] : jQuery('#ploopi_subscription_action_'+id_action)[0];

    ck.checked = !ck.checked

    if (id_action == -1 && ck.checked) jQuery('.ploopi_subscription_action').each(function(item) { this.checked = false; });

    if (id_action > -1 && jQuery('#ploopi_subscription_unsubscribe')[0] && jQuery('#ploopi_subscription_unsubscribe')[0].checked) jQuery('#ploopi_subscription_unsubscribe')[0].checked = false;

    if (id_action == 0 && ck.checked) jQuery('.ploopi_subscription_action').each(function(item) { this.checked = true; });

    if (id_action > 0 && !ck.checked && jQuery('#ploopi_subscription_action_0')[0].checked) jQuery('#ploopi_subscription_action_0')[0].checked = false;
};

/**
 * Actions des abonnements (attributs data-ploopi-action, js/actions.js) : la case est
 * basculée une fois, que le clic porte sur la case elle-même ou sur sa ligne
 */
ploopi.actions.subscription_checkaction = function(el, event, id_subscription, id_action) {
    if (event.target && event.target.type == 'checkbox') event.target.checked = !event.target.checked;
    ploopi.subscription.checkaction(id_subscription, id_action);
};

ploopi.actions.subscription_cancel = function(el, event, formid, divid) {
    var form = document.getElementById(formid);
    if (form) form.reset();
    ploopi.switchdisplay(divid);
};
