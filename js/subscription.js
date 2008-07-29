/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

function ploopi_subscription(ploopi_subscription_id, next)
{
    if (typeof(next) == 'undefined') next = '';
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=subscription&ploopi_subscription_id='+ploopi_subscription_id+'&next='+next, '', 'ploopi_subscription_'+ploopi_subscription_id);
}

function ploopi_subscription_checkaction(id_subscription, id_action)
{
    var ck = (id_action == -1) ? $('ploopi_subscription_unsubscribe') : $('ploopi_subscription_action_'+id_action);
    
    ck.checked = !ck.checked
    
    if (id_action == -1 && ck.checked) ploopi_checkall($('ploopi_form_subscription_'+id_subscription), 'ploopi_subscription_action_', false, true)
    
    if (id_action > -1 && $('ploopi_subscription_unsubscribe') && $('ploopi_subscription_unsubscribe').checked) $('ploopi_subscription_unsubscribe').checked = false;

    if (id_action == 0 && ck.checked) ploopi_checkall($('ploopi_form_subscription_'+id_subscription), 'ploopi_subscription_action_', true, true)
    
    if (id_action > 0 && !ck.checked && $('ploopi_subscription_action_0').checked) $('ploopi_subscription_action_0').checked = false;
}

