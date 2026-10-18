/*
    Copyright (c) 2007-2026 Ovensia
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

/**
 * Comportements par attributs data-ploopi-*, traités par délégation : ce que les
 * gestionnaires en ligne (onclick, onchange, onsubmit, href="javascript:…") faisaient,
 * sans script en ligne (Content-Security-Policy stricte, ploopi\csp).
 *
 *   data-ploopi-href="url"                    clic : aller à l'url
 *   data-ploopi-confirm="message"             clic sur un lien ou un bouton, envoi d'un
 *                                             formulaire : confirmation d'abord
 *   data-ploopi-todiv="id" data-ploopi-query  clic : ploopi.xhr.todiv(script, query, id)
 *     [data-ploopi-script] [data-ploopi-method]   (script : admin-light.php par défaut)
 *   data-ploopi-topopup="id" data-ploopi-query  clic : ploopi.xhr.topopup(largeur, …)
 *     [data-ploopi-width] [data-ploopi-script] [data-ploopi-method]
 *   data-ploopi-autosubmit                    changement d'un champ : envoi du formulaire
 *   data-ploopi-switchdisplay="id"            clic : ploopi.switchdisplay(id)
 *     [data-ploopi-remember]                  et état gardé en session (ploopi.switchdisplay)
 *   data-ploopi-search="id" data-ploopi-query data-ploopi-field="champ"
 *     data-ploopi-param="nom"                 clic : ploopi.xhr.todiv(…, query&nom=<valeur
 *                                             du champ>, id), après l'indicateur d'attente
 *   data-ploopi-enterclick="id"               Entrée dans le champ : clic sur l'élément id
 *   data-ploopi-load="query" [data-ploopi-script]
 *                                             à l'affichage : contenu chargé dans l'élément
 *   data-ploopi-action="nom" [data-ploopi-args='[…]']
 *                                             évènement (clic, ou data-ploopi-on="change")
 *                                             : ploopi.actions[nom](élément, évènement, …args)
 *
 * ploopi.actions ne contient que les fonctions qu'un module y a inscrites : un attribut
 * injecté ne peut pas appeler une fonction quelconque de la page.
 */

ploopi.actions = ploopi.actions || {};

ploopi.actionsrun = function(el, event) {
    var name = el.getAttribute('data-ploopi-action');
    if (!Object.prototype.hasOwnProperty.call(ploopi.actions, name) || typeof ploopi.actions[name] != 'function') return;

    var args = [];
    if (el.getAttribute('data-ploopi-args')) {
        try { args = JSON.parse(el.getAttribute('data-ploopi-args')); } catch (e) { args = []; }
        if (!jQuery.isArray(args)) args = [args];
    }

    return ploopi.actions[name].apply(el, [el, event].concat(args));
};

jQuery(document).on('click', '[data-ploopi-href], [data-ploopi-confirm]:not(form), [data-ploopi-todiv], [data-ploopi-topopup], [data-ploopi-switchdisplay], [data-ploopi-search], [data-ploopi-action]', function(event) {
    var el = this;
    var d = function(key) { return el.getAttribute('data-ploopi-'+key); };

    if (d('action') !== null && (d('on') || 'click') != 'click') return;

    if (d('confirm') !== null && !confirm(d('confirm'))) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    if (d('href') !== null) {
        event.preventDefault();
        document.location.href = d('href');
    }
    else if (d('todiv') !== null) {
        event.preventDefault();
        ploopi.xhr.todiv(d('script') || 'admin-light.php', d('query') || '', d('todiv'), d('method') || 'GET');
    }
    else if (d('topopup') !== null) {
        event.preventDefault();
        ploopi.xhr.topopup(parseInt(d('width'), 10) || 400, event, d('topopup'), d('script') || 'admin-light.php', d('query') || '', d('method') || 'GET');
    }
    else if (d('switchdisplay') !== null) {
        event.preventDefault();
        ploopi.switchdisplay(d('switchdisplay'));
        var shown = document.getElementById(d('switchdisplay'));
        if (d('remember') !== null && shown) ploopi.xhr.send('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi.switchdisplay&id='+encodeURIComponent(d('switchdisplay'))+'&display='+encodeURIComponent(shown.style.display), true);
    }
    else if (d('search') !== null) {
        event.preventDefault();
        var field = document.getElementById(d('field') || '');
        ploopi.xhr.ajaxloader(d('search'));
        ploopi.xhr.todiv(d('script') || 'admin-light.php', (d('query') || '')+'&'+encodeURIComponent(d('param') || 'filter')+'='+encodeURIComponent(field ? field.value : ''), d('search'), d('method') || 'GET');
    }
    else if (d('action') !== null) {
        if (ploopi.actionsrun(el, event) === false) event.preventDefault();
    }
    // data-ploopi-confirm seul : le lien est suivi, le bouton fait son office
});

jQuery(document).on('keydown', '[data-ploopi-enterclick]', function(event) {
    if (event.key != 'Enter' && event.keyCode != 13) return;
    event.preventDefault();
    var target = document.getElementById(this.getAttribute('data-ploopi-enterclick'));
    if (target) target.click();
});

/**
 * Contenu chargé à l'affichage (data-ploopi-load) : remplace les scripts en ligne qui
 * appelaient ploopi.xhr.todiv au chargement d'un composant
 */
ploopi.actionsload = function(node) {
    jQuery(node).find('[data-ploopi-load]').addBack('[data-ploopi-load]').each(function() {
        if (!this.id || this.getAttribute('data-ploopi-loaded')) return;
        this.setAttribute('data-ploopi-loaded', '1');
        ploopi.xhr.ajaxloader(this.id);
        ploopi.xhr.todiv(this.getAttribute('data-ploopi-script') || 'admin-light.php', this.getAttribute('data-ploopi-load'), this.id);
    });
};

jQuery(document).on('change', '[data-ploopi-autosubmit]', function() {
    if (this.form) {
        if (typeof this.form.requestSubmit == 'function') this.form.requestSubmit();
        else this.form.submit();
    }
});

jQuery(document).on('change input keyup focusin', '[data-ploopi-action][data-ploopi-on]', function(event) {
    var on = this.getAttribute('data-ploopi-on');
    if (event.type == on || (on == 'focus' && event.type == 'focusin')) ploopi.actionsrun(this, event);
});

/**
 * Formulaires du socle (ploopi\form) : la validation, décrite par l'attribut
 * data-ploopi-validate (JSON : panneaux, champs, libellé, format, bloc à ignorer s'il
 * est masqué), remplace la fonction autrefois générée dans un script en ligne. Les
 * fonctions ploopi.<id>_validate(form) restent définies pour les appels existants.
 */
ploopi.form = ploopi.form || {};

ploopi.form.spec = function(form) {
    try { return JSON.parse(form.getAttribute('data-ploopi-validate') || '{}'); } catch (e) { return {}; }
};

ploopi.form.validatepanel = function(form, panel) {
    for (var i = 0; i < panel.fields.length; i++) {
        var f = panel.fields[i];
        if (f.hide && jQuery('#'+f.hide).css('display') == 'none') continue;
        if (!ploopi.validatefield(f.label, form[f.name], f.format)) return false;
    }
    return true;
};

ploopi.form.validate = function(form) {
    var spec = ploopi.form.spec(form);
    ploopi.validatereset(form);
    var panels = spec.panels || [];
    for (var i = 0; i < panels.length; i++) {
        if (!ploopi.form.validatepanel(form, panels[i])) return false;
    }
    return true;
};

ploopi.form.enhance = function(node) {
    jQuery(node).find('form[data-ploopi-validate]').addBack('form[data-ploopi-validate]').each(function() {
        var spec = ploopi.form.spec(this);
        if (!spec.func) return;
        ploopi[spec.func] = ploopi.form.validate;
        jQuery.each(spec.panels || [], function(i, panel) {
            if (panel.func) ploopi[panel.func] = function(form) { return ploopi.form.validatepanel(form, panel); };
        });
    });
};

jQuery(function() {
    ploopi.popup.addenhancer(ploopi.form.enhance);
    ploopi.popup.addenhancer(ploopi.actionsload);
    // Sélecteur de couleur des champs data-jscolor insérés après le chargement (fenêtres AJAX) ;
    // jscolor n'équipe de lui-même que la page, et ignore les champs déjà équipés
    ploopi.popup.addenhancer(function(node) { if (window.jscolor && jscolor.install) jscolor.install(node); });
});

jQuery(document).on('submit', 'form[data-ploopi-validate][data-ploopi-validate-submit], form[data-ploopi-confirm]', function(event) {
    if (this.hasAttribute('data-ploopi-validate-submit') && !ploopi.form.validate(this)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }
    if (this.hasAttribute('data-ploopi-confirm') && !confirm(this.getAttribute('data-ploopi-confirm'))) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
});
