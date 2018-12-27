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

/* DOCUMENTS FUNCTIONS */
ploopi.documents = {};

ploopi.documents.openfolder = function(query, event) {
    ploopi.popup.show('', 460, event, 'click', 'ploopi_documents_openfolder_popup');
    ploopi.xhr.todiv('admin-light.php', query, 'ploopi_documents_openfolder_popup');
};

ploopi.documents.openfile = function(query, event) {
    ploopi.popup.show('', 460, event, 'click', 'ploopi_documents_openfile_popup');
    ploopi.xhr.todiv('admin-light.php', query, 'ploopi_documents_openfile_popup');
};

ploopi.documents.deletefile = function(query, documents_id) {
    ploopi.xhr.todiv('admin-light.php',query, 'ploopidocuments_'+documents_id);
};

ploopi.documents.deletefolder = function(query, documents_id) {
    ploopi.xhr.todiv('admin-light.php', query, 'ploopidocuments_'+documents_id);
};

ploopi.documents.browser = function(query, documents_id, asynchronous) {
    if (typeof(asynchronous) == 'undefined') asynchronous = false;

    if (asynchronous)
    {
        ploopi.xhr.ajaxloader('ploopidocuments_'+documents_id);
        ploopi.xhr.todiv('admin-light.php', query,'ploopidocuments_'+documents_id);
    }
    else ploopi.innerHTML('ploopidocuments_'+documents_id, ploopi.xhr.send('admin-light.php', query));
};

ploopi.documents.validate = function(form) {
    if (form.documentsfile_name)
    {
        if (!ploopi.validatefield('Fichier',form.documentsfile_name,"string")) return false;
    }
    else if (!ploopi.validatefield('Fichier',form.documentsfile_file,"string")) return false;

    if (ploopi.validatefield('Libellé',form.documentsfile_label,"string"))
    return true;

    return false;
};

// Ouverture d'un popup de sélection de fichier
ploopi.documents.selectfile = function(query, event, width) {
    if (typeof(width) == 'undefined') width = 600;
    ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, event, 'click', 'ploopi_documents_popup');
    ploopi.xhr.todiv('admin-light.php', query, 'ploopi_documents_popup', 'get');
};
