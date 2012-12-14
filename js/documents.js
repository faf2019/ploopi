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

/* DOCUMENTS FUNCTIONS */

function ploopi_documents_openfolder(query, event)
{
    ploopi_showpopup('', 400, event, 'click', 'ploopi_documents_openfolder_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopi_documents_openfolder_popup');
}

function ploopi_documents_openfile(query, event)
{
    ploopi_showpopup('', 400, event, 'click', 'ploopi_documents_openfile_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopi_documents_openfile_popup');
}

function ploopi_documents_deletefile(query, documents_id)
{
    ploopi_xmlhttprequest_todiv('admin-light.php',query, 'ploopidocuments_'+documents_id);
}

function ploopi_documents_deletefolder(query, documents_id)
{
    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopidocuments_'+documents_id);
}

function ploopi_documents_browser(query, documents_id, asynchronous)
{
    // ploopi_documents_browser(documents_id, currentfolder, mode, orderby, asynchronous)
    //if (typeof(currentfolder) == 'undefined') currentfolder = '';
    //if (typeof(orderby) == 'undefined') orderby = '';
    //if (typeof(mode) == 'undefined') mode = '';
    // var option = (orderby != '') ? '&orderby='+orderby : '';

    if (typeof(asynchronous) == 'undefined') asynchronous = false;


    if (asynchronous)
    {
        // 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_browser&mode='+mode+'&currentfolder='+currentfolder+option
        ploopi_ajaxloader('ploopidocuments_'+documents_id);
        ploopi_xmlhttprequest_todiv('admin-light.php', query,'ploopidocuments_'+documents_id);
    }
    else ploopi_innerHTML('ploopidocuments_'+documents_id, ploopi_xmlhttprequest('admin-light.php', query));
}

function ploopi_documents_validate(form)
{
    if (form.documentsfile_name)
    {
        if (!ploopi_validatefield('Fichier',form.documentsfile_name,"string")) return false;
    }
    else if (!ploopi_validatefield('Fichier',form.documentsfile_file,"string")) return false;

    if (ploopi_validatefield('Libellé',form.documentsfile_label,"string"))
    return true;

    return false;
}

function ploopi_documents_popup(id_object, id_record, id_module, destfield, event, callback, rootname, width)
{
    var documents_id = ploopi_base64_encode(id_module+'_'+id_object+'_'+ploopi_addslashes(id_record)+'_popup');

    if (typeof(callback) == 'undefined') callback = false;
    if (typeof(rootname) == 'undefined') rootname = '';
    if (typeof(width) == 'undefined') width = 600;

    query = 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_selectfile&id_object='+id_object+'&id_record='+id_record+'&documents_id='+documents_id+'&destfield='+encodeURIComponent(destfield)+'&rootname='+encodeURIComponent(rootname)+'&callback='+(callback?1:0);

    ploopi_showpopup(ploopi_ajaxloader_content, width, event, 'click', 'ploopi_documents_popup');

    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopi_documents_popup', 'get');
}
