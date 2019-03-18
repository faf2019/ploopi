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
    ploopi.popup.show('', 460, null, true, 'ploopi_documents_openfolder_popup');
    ploopi.xhr.todiv('admin-light.php', query, 'ploopi_documents_openfolder_popup');
};

ploopi.documents.openfile = function(query, event) {
    ploopi.popup.show('', 460, null, true, 'ploopi_documents_openfile_popup');
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
    if (form.documentsfile_name) {
        if (!ploopi.validatefield('Fichier',form.documentsfile_name,"string")) return false;
    }

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



/**
 * Gestion d'une drop zone pour upload de fichier
 */
ploopi.documents.dropzoneupload = function(params) {

    var that = this;

    that.params = null;
    that.progressbar = null;
    that.inputfile = null;
    that.updating = null;
    that.start = 0;
    that.files = null;
    // Periodical executer pour la mise à jour du statut d'upload
    that.pe = null;

    that.init = function(params) {

        that.params = params;

        // Gestion du dragover sur dropzone (début du survol avec fichier)
        jQuery('#'+that.params.dropzone).on('dragover', function(e) {
            e.preventDefault();
            jQuery(this).addClass('dragover');

        });

        // Gestion du dragleave sur dropzone (fin du survol avec fichier)
        jQuery('#'+that.params.dropzone).on('dragleave', function(e) {
            e.preventDefault();
            jQuery(this).removeClass('dragover');
        });

        // Gestion du clic sur dropzone (déclenche un clic sur input file caché)
        jQuery('#'+that.params.dropzone).on('click', function(e) {
            e.preventDefault();
            that.inputfile.focus();
            that.inputfile.click();
        });

        // Création du champ input file pour proposer une sélection alternative des fichiers
        that.inputfile = jQuery('<input type="file" multiple="true" style="position:absolute;top:0;z-index:0;" />')

        that.inputfile.on('change', function(e) {
            e.preventDefault();
            // Récupération de la liste des fichiers
            that.files = that.inputfile[0].files;
            // Préparation de l'upload + upload
            that.refresh();
        });

        // Handler de dépôt de fichier dans la dropzone
        jQuery('#'+that.params.dropzone).on('drop', function(e) {
            e.preventDefault();
            jQuery(this).removeClass('dragover');
            // Récupération de la liste des fichiers
            that.files = e.originalEvent.dataTransfer.files;
            // Préparation de l'upload + upload
            that.refresh();
        });
    }

    that.refresh = function() {

        jQuery('#'+that.params.filelist).html('');
        for (var i = 0; i < that.files.length; i++) {
            jQuery('#'+that.params.filelist).append('<div>'+that.files[i].name+'</div>');
        }

    }

    that.init(params);

};
