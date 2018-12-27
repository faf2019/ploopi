function directory_list_change(list)
{
    //alert(list.value);

    if (list.value != 0)
    {
        jQuery('#directory_list_modify_link')[0].style.display = 'inline';
        jQuery('#directory_list_delete_link')[0].style.display = 'inline';
    }
    else
    {
        jQuery('#directory_list_modify_link')[0].style.display = 'none';
        jQuery('#directory_list_delete_link')[0].style.display = 'none';
    }

    ploopi.popup.hide('popup_directory_addtofavorites');
    ploopi.xhr.ajaxloader('directory_favorites_list');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_favorites&directory_favorites_id_list='+list.value, 'directory_favorites_list');
}

function directory_list_addnew(e)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 400, e, 'click', 'popup_directory_list_form');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_list_addnew', 'popup_directory_list_form');
}

function directory_list_modify(e)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 400, e, 'click', 'popup_directory_list_form');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_list_modify&directory_favorites_id_list='+jQuery('#directory_favorites_id_list')[0].value, 'popup_directory_list_form');
}

function directory_list_delete()
{
    if (confirm('Êtes vous certain de vouloir supprimer cette liste ?'))
        document.location.href=''
        //jQuery('#directory_list_id')[0].value
}

function directory_list_validate(form)
{
    if (ploopi.validatefield('Libellé',form.directory_list_label, 'string'))
        return true;

    return false;
}

function directory_checklist(id_list)
{
    var ck = $('directory_id_list'+id_list);
    ck.checked = !ck.checked;

    if (id_list>0 && jQuery('#directory_id_list0')[0] && jQuery('#directory_id_list0')[0].checked) jQuery('#directory_id_list0')[0].checked = false;

    if (id_list == 0 && jQuery('#directory_id_list0')[0].checked)
    {
        for (i=0;i<$$('input.directory_id_list').length;i++)
        {
            $$('input.directory_id_list')[i].checked = false;
        }
    }
}

function directory_addtofavorites(e, id_user, id_contact)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 250, e, 'click', 'popup_directory_addtofavorites');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_getlists&directory_favorites_id_user='+id_user+'&directory_favorites_id_contact='+id_contact, 'popup_directory_addtofavorites');
}

function directory_view(e, id_user, id_contact)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 700, e, 'click', 'popup_directory_view');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_view&directory_id_user='+id_user+'&directory_id_contact='+id_contact, 'popup_directory_view');
}

function directory_modify(e, id_contact)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 700, e, 'click', 'popup_directory_modify');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_modify&directory_id_contact='+id_contact, 'popup_directory_modify');
}

function directory_choose_photo(e, contact_id, photo_id)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 400, e, false, 'popup_directory_choose_photo');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_choose_photo&directory_contact_id='+contact_id+'&directory_photo_id='+photo_id, 'popup_directory_choose_photo');
}

function directory_delete_photo(contact_id)
{
    ploopi.xhr.send('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_delete_photo&directory_contact_id='+contact_id);
}

function directory_heading_validate(form)
{
    if (ploopi.validatefield('Libellé',form.directory_heading_label, 'string'))
        return true;

    return false;
}

function directory_speeddialing_validate(form)
{
    if (ploopi.validatefield('Libellé',form.directory_speeddialing_label, 'string'))
    if (ploopi.validatefield('Numéro',form.directory_speeddialing_number, 'phone'))
        return true;

    return false;
}

function directory_speeddialing_modify(e, id_speeddialing)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 400, e, 'click', 'popup_directory_speeddialing_modify');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_speeddialing_modify&directory_speeddialing_id='+id_speeddialing, 'popup_directory_speeddialing_modify');
}

function directory_heading_choose_popup(e, id_heading)
{
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, 300, e, 'click', 'popup_directory_heading_choose');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_heading_choose&directory_heading_id='+id_heading, 'popup_directory_heading_choose');
}

function directory_heading_choose(id_heading, label)
{
    ploopi.popup.hide('popup_directory_heading_choose');
    jQuery('#directory_heading_id')[0].value = id_heading;
    jQuery('#directory_heading_id_label')[0].value = label;
}
