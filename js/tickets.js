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

function ploopi_tickets_new(event, id_object, id_record, object_label, id_user, reload)
{
    var data = '';

    if (object_label) data += '&ploopi_tickets_object_label='+object_label;
    if (id_object) data += '&ploopi_tickets_id_object='+id_object;
    if (id_record) data += '&ploopi_tickets_id_record='+id_record;
    if (reload) data += '&ploopi_tickets_reload='+reload;
    if (id_user) data += '&ploopi_tickets_id_user='+id_user;

    ploopi_showpopup('',550,event,'click', 'system_popupticket');
    ploopi_ajaxloader('system_popupticket');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_new'+data,'system_popupticket');
}

/* Rafraichissement de la zone indiquant le nombre de tickets non lus + alerte sur nouveau ticket */
function ploopi_tickets_refresh(lastnewticket, timeout, str_left, str_right)
{
    var intPloopiLastNewTicket = lastnewticket;
    var boolAlert = false;
    
    if (typeof(str_left) == 'undefined') str_left = '';
    if (typeof(str_right) == 'undefined') str_right = '';

    new PeriodicalExecuter( function(pe) { 
        new Ajax.Request('index-quick.php?ploopi_op=tickets_getnum',
            {
                method:     'get',
                encoding:   'iso-8859-15',
                onSuccess:  function(transport) { 
                                 var res = transport.responseText.split(',');
                                 if (res.length == 2)
                                 {
                                     var nb = parseInt(res[0],10);
                                     var last = parseInt(res[1],10);
                                     
                                     $('tpl_ploopi_tickets_new').innerHTML =  str_left+nb+str_right;
                                     
                                     if (last > intPloopiLastNewTicket && !boolAlert)
                                     {
                                         ploopi_tickets_alert();
                                         boolAlert = true;
                                     }
                                     intPloopiLastNewTicket = last;
                                 }
                            }
            }
        ); 
    }
    ,timeout
    ); 
}

function ploopi_tickets_alert()
{
    ploopi_showpopup('', 350, null, true, 'popup_tickets_new_alert', 0, 200);
    ploopi_ajaxloader('popup_tickets_new_alert');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_alert', 'popup_tickets_new_alert');
}
