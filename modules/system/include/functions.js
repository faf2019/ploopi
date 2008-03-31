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

function system_showgroup(typetree, gid, str)
{
    if (typetree == 'groups') dest = 'g'+gid;
    else dest = 'w'+gid;

    elt = $('n'+dest);
    
    if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
    else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');

    if ($(dest))
    {
        if ($(dest).style.display == 'none')
        {
            $(dest).style.display='block';
            if ($(dest).innerHTML.length < 20)
            {
                ploopi_ajaxloader(dest);
                ploopi_xmlhttprequest_todiv('admin-light.php','op=xml_detail_group&typetree='+typetree+'&gid='+gid+'&str='+str,'',dest);
            }
        }
        else $(dest).style.display='none';
    }
}

function system_checkall(type_element, val)
{
    for (i=0;i<$$(type_element).length;i++) $$(type_element)[i].checked = val;
}

function system_roleusers(roleid)
{
    if ($('system_roleusers_detail'+roleid).style.display != 'block')
    {
        ploopi_ajaxloader('system_roleusers_list'+roleid);
        $('system_roleusers_detail'+roleid).style.display = 'block';
        ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=system_roleusers&system_roleusers_roleid='+roleid,'','system_roleusers_list'+roleid);
    }
    else $('system_roleusers_detail'+roleid).style.display = 'none';
}

function system_roleusers_search(roleid)
{
    ploopi_ajaxloader('system_roleusers_search_result'+roleid);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=system_roleusers_search&system_roleusers_roleid='+roleid+'&system_roleusers_filter='+$('system_roleusers_filter'+roleid).value,'','system_roleusers_search_result'+roleid);
}

function system_roleusers_select(roleid, userid, type)
{
    ploopi_ajaxloader('system_roleusers_list'+roleid);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=system_roleusers_select_'+type+'&system_roleusers_roleid='+roleid+'&system_roleusers_'+type+'id='+userid,'','system_roleusers_list'+roleid);
}

function system_roleusers_delete(roleid, userid, type)
{
    ploopi_ajaxloader('system_roleusers_list'+roleid);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=system_roleusers_delete_'+type+'&system_roleusers_roleid='+roleid+'&system_roleusers_'+type+'id='+userid,'','system_roleusers_list'+roleid);
}


/* TICKETS FUNCTIONS */

function system_tickets_search_users(filter)
{
    ploopi_getxmlhttp_ext('index-light.php','ploopi_op=tickets_search_users&ploopi_ticket_userfilter='+filter,0,'div_ticket_search_result');
}

function system_tickets_new(event, id_object, id_record, object_label)
{
    var data = '';

    if (object_label) data += '&object_label='+object_label;
    if (id_object) data += '&id_object='+id_object;
    if (id_record) data += '&id_record='+id_record;

    ploopi_showpopup('',400,event,'click', 'system_popupticket');
    ploopi_ajaxloader('system_popupticket');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_new'+data,'','system_popupticket');
}

function system_tickets_display(ticket_id, opened, isroot, tpl_path)
{
    disp = $('tickets_detail_'+ticket_id);

    if (disp.style.display == 'block') disp.style.display='none';
    else
    {
        if (isroot) // get responses
        {
            $('watch_notify_'+ticket_id).innerHTML = '<img src="'+tpl_path+'/img/system/email_read.png">';
            resp = $('tickets_responses_'+ticket_id);
            // if empty
            if (resp.innerHTML.length < 5)  ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_open_responses&ticket_id='+ticket_id,'','tickets_responses_'+ticket_id);
        }
        ploopi_xmlhttprequest('admin-light.php','ploopi_op=tickets_open&ticket_id='+ticket_id);

        if (!opened)
        {
            /*
            puce = $('tickets_puce_'+ticket_id);
            puce.style.backgroundColor = '#2020ff';
            */
            title = $('tickets_title_'+ticket_id);
            title.style.fontWeight = 'normal';
        }
        disp.style.display='block';
    }
}


function system_search_next()
{
    ploopi_ajaxloader('system_search_result');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=system_search&system_search_keywords='+$('system_search_keywords').value+'&system_search_workspace='+$('system_search_workspace').value+'&system_search_date1='+$('system_search_date1').value+'&system_search_date2='+$('system_search_date2').value,'','system_search_result');
}

function system_serverload()
{
    pe_serverload = new PeriodicalExecuter(function(pe) {
        $('system_serverload_loading').style.visibility = 'visible';
        new Ajax.Request('admin-light.php?ploopi_op=system_serverload',
            {
                method:     'get',
                encoding:   'iso-8859-15',
                onSuccess:  function(transport) {$('system_serverload').innerHTML =  transport.responseText;}
            }
        );
    }, 15);
}