function directory_list_change(list)
{
    //alert(list.value);

    if (list.value != 0)
    {
        $('directory_list_modify_link').style.display = 'inline';
        $('directory_list_delete_link').style.display = 'inline';
    }
    else
    {
        $('directory_list_modify_link').style.display = 'none';
        $('directory_list_delete_link').style.display = 'none';
    }

    ploopi_hidepopup('popup_directory_addtofavorites');
    ploopi_ajaxloader('directory_favorites_list');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_favorites&directory_favorites_id_list='+list.value, 'directory_favorites_list');
}

function directory_list_addnew(e)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 300, e, 'click', 'popup_directory_list_form');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_list_addnew', 'popup_directory_list_form');
}

function directory_list_modify(e)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 300, e, 'click', 'popup_directory_list_form');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_list_modify&directory_favorites_id_list='+$('directory_favorites_id_list').value, 'popup_directory_list_form');
}

function directory_list_delete()
{
    if (confirm('Êtes vous certain de vouloir supprimer cette liste ?'))
        document.location.href=''
        //$('directory_list_id').value
}

function directory_list_validate(form)
{
    if (ploopi_validatefield('Libellé',form.directory_list_label, 'string'))
        return true;

    return false;
}

function directory_checklist(id_list)
{
    var ck = $('directory_id_list'+id_list);
    ck.checked = !ck.checked;

    if (id_list>0 && $('directory_id_list0') && $('directory_id_list0').checked) $('directory_id_list0').checked = false;

    if (id_list == 0 && $('directory_id_list0').checked)
    {
        for (i=0;i<$$('input.directory_id_list').length;i++)
        {
            $$('input.directory_id_list')[i].checked = false;
        }
    }
}

function directory_addtofavorites(e, id_user, id_contact)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 250, e, 'click', 'popup_directory_addtofavorites');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_getlists&directory_favorites_id_user='+id_user+'&directory_favorites_id_contact='+id_contact, 'popup_directory_addtofavorites');
}

function directory_view(e, id_user, id_contact)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 600, e, 'click', 'popup_directory_view');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_view&directory_id_user='+id_user+'&directory_id_contact='+id_contact, 'popup_directory_view');
}

function directory_modify(e, id_contact)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 600, e, 'click', 'popup_directory_modify');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_modify&directory_id_contact='+id_contact, 'popup_directory_modify');
}

function directory_choose_photo(e, contact_id, photo_id)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 400, e, false, 'popup_directory_choose_photo');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_choose_photo&directory_contact_id='+contact_id+'&directory_photo_id='+photo_id, 'popup_directory_choose_photo');
}

function directory_heading_validate(form)
{
    if (ploopi_validatefield('Libellé',form.directory_heading_label, 'string'))
        return true;

    return false;
}

function directory_speeddialing_validate(form)
{
    if (ploopi_validatefield('Libellé',form.directory_speeddialing_label, 'string'))
    if (ploopi_validatefield('Numéro',form.directory_speeddialing_number, 'phone'))
        return true;

    return false;
}

function directory_speeddialing_modify(e, id_speeddialing)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 300, e, 'click', 'popup_directory_speeddialing_modify');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_speeddialing_modify&directory_speeddialing_id='+id_speeddialing, 'popup_directory_speeddialing_modify');
}

function directory_heading_choose_popup(e, id_heading)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 300, e, 'click', 'popup_directory_heading_choose');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=directory_heading_choose&directory_heading_id='+id_heading, 'popup_directory_heading_choose');
}

function directory_heading_choose(id_heading, label)
{
    ploopi_hidepopup('popup_directory_heading_choose');
    $('directory_heading_id').value = id_heading;
    $('directory_heading_id_label').value = label;
}