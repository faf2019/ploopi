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
            // Nom en texte : un nom de fichier n'est pas du HTML
            jQuery('#'+that.params.filelist).append(jQuery('<div>').text(that.files[i].name));
        }

    }

    that.init(params);

};


/**
 * Choix d'un fichier (navigateur en mode « tofield » ou « tocallback ») : le nom et
 * l'identifiant vont dans le champ <target> et <target>_id, ou à la fonction <target>
 * (nom éventuellement pointé : « ploopi.editor.picked »)
 */
ploopi.documents.pick = function(mode, target, id, name, url, md5id) {
    if (mode == 'tofield') {
        var dest = document.getElementById(target);
        if (dest) {
            if (dest.type) dest.value = name;
            else dest.textContent = name;
        }
        var destid = document.getElementById(target+'_id');
        if (destid) destid.value = id;
        ploopi.popup.hide('ploopi_documents_popup');
    }
    else if (mode == 'tocallback') {
        var func = target.split('.').reduce(function(obj, key) { return obj ? obj[key] : undefined; }, window);
        if (typeof func == 'function') func(id, name, url, md5id);
    }
};

/**
 * Actions du navigateur de documents (ploopi\documents::browser) : attributs
 * data-ploopi-documents (action), data-query, data-documents-id, data-confirm…,
 * sans gestionnaire en ligne (CSP stricte)
 */
jQuery(document).on('click', '[data-ploopi-documents]', function(event) {
    var el = this;
    var d = function(key) { return el.getAttribute('data-'+key) || ''; };

    event.preventDefault();

    switch(d('ploopi-documents')) {
        case 'openfolder': ploopi.documents.openfolder(d('query'), event); break;
        case 'openfile': ploopi.documents.openfile(d('query'), event); break;
        case 'browser': ploopi.documents.browser(d('query'), d('documents-id'), true); break;
        case 'deletefolder': if (confirm(d('confirm'))) ploopi.documents.deletefolder(d('query'), d('documents-id')); break;
        case 'deletefile': if (confirm(d('confirm'))) ploopi.documents.deletefile(d('query'), d('documents-id')); break;
        case 'download': document.location.href = d('href'); break;
        case 'tofield':
        case 'tocallback':
            ploopi.documents.pick(d('ploopi-documents'), d('target'), parseInt(d('id'), 10), d('name'), d('url'), d('md5id'));
            break;
    }
});

/**
 * Formulaire d'un fichier (opération documents_openfile) : zone de dépôt préparée
 * dès l'insertion de la fenêtre (ploopi.popup.onadd)
 */
ploopi.documents.enhance = function(node) {
    jQuery(node).find('form[data-ploopi-documents-form]').addBack('form[data-ploopi-documents-form]').each(function() {
        if (this.getAttribute('data-ploopi-ready')) return;
        this.setAttribute('data-ploopi-ready', '1');

        if (this.getAttribute('data-ploopi-documents-form') == 'new' && document.getElementById('documents_dropzone')) {
            this.ploopi_dropzone = new ploopi.documents.dropzoneupload({
                dropzone: 'documents_dropzone',
                status: 'documents_status',
                loading: 'documents_loading',
                filelist: 'documents_filelist',
                form: this.id,
                filesize: parseInt(this.getAttribute('data-filesize'), 10) || 50000000
            });
        }
    });
};

jQuery(function() {
    ploopi.popup.addenhancer(ploopi.documents.enhance);
});

/**
 * Envoi du formulaire d'un fichier, en XHR : contrôle des champs, fichiers déposés
 * (nouveau fichier) ou fichier de remplacement (modification), puis navigateur
 * rafraîchi et fenêtre fermée, ou fichier rendu au champ, à la fonction appelante.
 * (La modification partait vers une iframe qui ne recevait que du JSON : ni
 * rafraîchissement ni fermeture.)
 */
jQuery(document).on('submit', 'form[data-ploopi-documents-form]', function(e) {
    var form = this;
    var d = function(key) { return form.getAttribute('data-'+key) || ''; };

    if (!ploopi.documents.validate(form)) {
        e.preventDefault();
        return;
    }

    e.preventDefault();

    if (ploopi.editor && ploopi.editor.save) ploopi.editor.save();

    var formData = new FormData(form);
    var files = form.ploopi_dropzone && form.ploopi_dropzone.files ? form.ploopi_dropzone.files : [];
    for (var i = 0; i < files.length; i++) formData.append('documentsfile_file'+i, files[i]);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action);

    xhr.onload = function() {
        var data = {};
        try { data = JSON.parse(xhr.responseText); } catch (err) { data = {}; }

        switch(d('mode')) {
            case 'tofield':
                ploopi.documents.pick('tofield', d('target'), data.id, data.name);
            break;

            case 'tocallback':
                ploopi.documents.pick('tocallback', d('target'), data.id, data.name, d('download')+'&documentsfile_id='+data.md5id, data.md5id);
                ploopi.popup.hide('ploopi_documents_openfile_popup');
            break;

            default:
                ploopi.documents.browser(d('query'), d('documents-id'));
                ploopi.popup.hide('ploopi_documents_openfile_popup');
            break;
        }
    };

    xhr.send(formData);
});
