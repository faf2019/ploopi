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

function system_showgroup(typetree, gid, str)
{
    if (typetree == 'groups') dest = 'g'+gid;
    else dest = 'w'+gid;

    elt = jQuery('#n'+dest)[0];

    if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
    else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');

    if (jQuery('#'+dest).length)
    {
        dest_elt = jQuery('#'+dest).get(0);

        if (dest_elt.style.display == 'none')
        {
            if (dest_elt.innerHTML.length < 20)
            {
                dest_elt.innerHTML = ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&op=xml_detail_group&typetree='+typetree+'&gid='+gid+'&str='+str);
            }

            jQuery('#'+dest).eq(0).slideDown('fast');
        }
        else
        {
            jQuery('#'+dest).eq(0).slideUp('fast');
        }
    }
}

function system_roleusers(roleid)
{
    detail = jQuery('#system_roleusers_detail'+roleid).get(0);

    if (detail.style.display != 'block')
    {
        ploopi.xhr.ajaxloader('system_roleusers_list'+roleid);
        detail.style.display = 'block';
        ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_roleusers&system_roleusers_roleid='+roleid,'system_roleusers_list'+roleid);
    }
    else detail.style.display = 'none';
}

function system_roleusers_search(roleid)
{
    ploopi.xhr.ajaxloader('system_roleusers_search_result');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_roleusers_search&system_roleusers_roleid='+roleid+'&system_roleusers_filter='+jQuery('#system_roleusers_filter')[0].value,'system_roleusers_search_result');
}

function system_roleusers_select(roleid, userid, type)
{
    ploopi.xhr.ajaxloader('system_roleusers_list');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_roleusers_select_'+type+'&system_roleusers_roleid='+roleid+'&system_roleusers_'+type+'id='+userid,'system_roleusers_list');
    alert('Le rôle a été attribué');
}

function system_roleusers_delete(roleid, userid, type)
{
    ploopi.xhr.ajaxloader('system_roleusers_list');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_roleusers_delete_'+type+'&system_roleusers_roleid='+roleid+'&system_roleusers_'+type+'id='+userid,'system_roleusers_list');
    alert('L\'attribution du rôle a été retirée');
}

/* TICKETS FUNCTIONS */

function system_tickets_display(ticket_id, opened, isroot, tpl_path)
{
    disp = jQuery('#tickets_detail_'+ticket_id)[0];

    if (disp.style.display == 'block') disp.style.display='none';
    else
    {
        if (isroot) // get responses
        {
            jQuery('#watch_notify_'+ticket_id)[0].innerHTML = '<img src="'+tpl_path+'/img/system/email_read.png">';
            resp = jQuery('#tickets_responses_'+ticket_id)[0];
            // if empty
            if (resp.innerHTML.length < 5)  ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_open_responses&ticket_id='+ticket_id,'tickets_responses_'+ticket_id);
        }
        ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_open&ticket_id='+ticket_id);

        if (!opened)
        {
            title = jQuery('#tickets_title_'+ticket_id)[0];
            title.style.fontWeight = 'normal';
        }
        disp.style.display='block';
    }
}

function system_search_next()
{
    ploopi.xhr.ajaxloader('system_search_result');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_search&system_search_keywords='+jQuery('#system_search_keywords')[0].value+'&system_search_module='+jQuery('#system_search_module')[0].value+'&system_search_date1='+jQuery('#system_search_date1')[0].value+'&system_search_date2='+jQuery('#system_search_date2')[0].value,'system_search_result');
}

function system_serverload()
{
    (function worker() {
        jQuery.get('admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_serverload', function(data, textStatus, request) {

            console.log(request.getResponseHeader('Ploopi-Connected'));

            if (request.getResponseHeader('Ploopi-Connected') == '1') {

                jQuery('#system_serverload')[0].innerHTML =  data;

                setTimeout(worker, 1000);
            }
            else {
                console.log('Not connected');
            }
        });
    })();
}

function system_choose_photo(e, user_id)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 400, e, false, 'popup_system_choose_photo');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_choose_photo&system_user_id='+user_id, 'popup_system_choose_photo');
}

function system_delete_photo(user_id)
{
    ploopi.xhr.send('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=system_delete_photo&system_user_id='+user_id);
}
