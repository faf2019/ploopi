/*
    Copyright (c) 2007-2013 Ovensia
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
    ploopi_showpopup('', 460, event, 'click', 'ploopi_documents_openfolder_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopi_documents_openfolder_popup');
}

function ploopi_documents_openfile(query, event)
{
    ploopi_showpopup('', 460, event, 'click', 'ploopi_documents_openfile_popup');
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
    if (typeof(asynchronous) == 'undefined') asynchronous = false;

    if (asynchronous)
    {
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

// Ouverture d'un popup de sélection de fichier
function ploopi_documents_selectfile(query, event, width)
{
    if (typeof(width) == 'undefined') width = 600;
    ploopi_showpopup(ploopi_ajaxloader_content, width, event, 'click', 'ploopi_documents_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php', query, 'ploopi_documents_popup', 'get');
}
