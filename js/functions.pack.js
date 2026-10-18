ploopi = {};

/*!
 * jQuery viewportOffset - v0.3 - 2/3/2010
 * http://benalman.com/projects/jquery-misc-plugins/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

// Like the built-in jQuery .offset() method, but calculates left and top from
// the element's position relative to the viewport, not the document.

(function($){
  '$:nomunge'; // Used by YUI compressor.

  var win = $(window);

  $.fn.viewportOffset = function() {
    var offset = $(this).offset();

    return {
      left: offset.left - win.scrollLeft(),
      top: offset.top - win.scrollTop()
    };
  };

})(jQuery);

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

/* ANNOTATIONS FUNCTIONS */
ploopi.annotations = {};

ploopi.annotations.tag_timer = null;
ploopi.annotations.search = '';
ploopi.annotations.tag_lastedit = '';
ploopi.annotations.tag_results = new Array();
ploopi.annotations.tag_new_array = new Array();
ploopi.annotations.tag_modified = -1;

ploopi.annotations.display = function(id_annotation)
{
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation&id_annotation='+id_annotation, 'ploopiannotation_'+id_annotation);
};

ploopi.annotations.tag_init = function(id_annotation) {
    /*
    $('#ploopi_annotationtags_'+id_annotation)[0].onkeyup = ploopi.annotations.tag_keyup;
    $('#ploopi_annotationtags_'+id_annotation)[0].onkeypress = ploopi.annotations.tag_keypress;
    */

    //$('#ploopi_annotationtags_'+id_annotation).bind('keypress', self.tag_keypress);

    jQuery('#ploopi_annotationtags_'+id_annotation).bind('keyup', function(e) {
        src = e.target; // get source field

        idrecord = src.id.split('_')[2]; // get id record from source field id

        switch(e.keyCode)
        {
            case 38: case 40:
                ploopi.annotations.tag_prevent(e);
            break
            case 9:
                ploopi.annotations.tag_complete(idrecord);
                ploopi.annotations.tag_prevent(e);
            break
            case 13:
                ploopi.annotations.tag_complete(idrecord);
                ploopi.annotations.tag_prevent(e);
            break
            case 35: //end
            case 36: //home
            case 39: //right
            case 37: //left
            //case 32: //space
            break
            default:
                tag_last_array = new Array();
                ploopi.annotations.tag_new_array = new Array();

                tag_last_array = ploopi.annotations.tag_lastedit.split(' ');
                ploopi.annotations.tag_new_array = src.value.split(' ');

                ploopi.annotations.tag_modified = -1;
                for (i=0;i<ploopi.annotations.tag_new_array.length;i++)
                {
                    if (ploopi.annotations.tag_new_array[i] != tag_last_array[i])
                    {
                        if (ploopi.annotations.tag_modified == -1) ploopi.annotations.tag_modified = i;
                        else ploopi.annotations.tag_modified = -2
                    }
                }
                if (ploopi.annotations.tag_modified>=0) ploopi.annotations.tag_search(idrecord, ploopi.annotations.tag_new_array[ploopi.annotations.tag_modified]);
            break;
        }

    });

};

ploopi.annotations.tag_search = function(id_annotation, search) {
    clearTimeout(ploopi.annotations.tag_timer);
    ploopi.annotations.search = search;
    ploopi.annotations.tag_timer = setTimeout(function() { ploopi.annotations.tag_searchtimeout(id_annotation); }, 100);
};

ploopi.annotations.tag_searchtimeout = function(id_annotation) {
    list_tags = ploopi.annotations.search.split(' ');

    if (list_tags.length>0) ploopi.xhr.tocb('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_searchtags&tag='+list_tags[list_tags.length-1], ploopi.annotations.tag_display, id_annotation);
};

ploopi.annotations.tag_display = function(result,ticket) {
    if (result != '')
    {
        ploopi.annotations.tag_results = new Array();

        splited_result = result.split('|');
        tagstoprint = '';

        for (i=0;i<splited_result.length;i++)
        {
            detail = splited_result[i].split(';');
            if (tagstoprint != '') tagstoprint += ' ';
            if (i==0) tagstoprint += '<b>';
            tagstoprint += '<a href="javascript:ploopi.annotations.tag_complete(\''+ticket+'\','+i+')">'+detail[0]+'</a> ('+detail[1]+')';
            if (i==0) tagstoprint += '</b>';
            ploopi.annotations.tag_results[i] = detail[0];
        }

        document.getElementById('tagsfound_'+ticket).innerHTML = tagstoprint;
    }
    else
    {
        document.getElementById('tagsfound_'+ticket).innerHTML = '';
        ploopi.annotations.tag_results = new Array();
    }
};

ploopi.annotations.tag_prevent = function(e) {
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
};

ploopi.annotations.tag_keypress = function(e) {
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    console.log(self.tag_lastedit);

    switch(e.keyCode)
    {
        case 38: case 40:
            ploopi.annotations.tag_prevent(e)
        break
        case 9:
            ploopi.annotations.tag_prevent(e)
        break
        case 13:
            ploopi.annotations.tag_prevent(e)
        break
        default:
            ploopi.annotations.tag_lastedit = $(src.id).value;
        break;
    }
};


ploopi.annotations.tag_complete = function(idrecord, idtag) {
    if (!(idtag>=0)) idtag = 0;

    if (ploopi.annotations.tag_results[idtag])
    {
        ploopi.annotations.tag_new_array[ploopi.annotations.tag_modified] = ploopi.annotations.tag_results[idtag];

        taglist = '';
        for (i=0;i<ploopi.annotations.tag_new_array.length;i++)
        {
            if (taglist != '') taglist += ' ';
            taglist += ploopi.annotations.tag_new_array[i]
        }

        document.getElementById('ploopi_annotationtags_'+idrecord).value = taglist.replace(/(^\s*)|(\s*$)/g,'')+' ';
        document.getElementById('tagsfound_'+idrecord).innerHTML = '';
    }

    ploopi.annotations.tag_results = new Array();
};

ploopi.annotations.remove = function(id_annotation, id) {
    if (confirm('Êtes vous certain de vouloir supprimer cette annotation ?')) ploopi.xhr.send('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_delete&ploopi_annotation_id='+id);
    ploopi.annotations.display(id_annotation);
};

ploopi.annotations.validate = function(form) {
    // Le texte est requis ; les étiquettes sont facultatives (l'ancien contrôle les exigeait,
    // sous le libellé « Titre »)
    if (ploopi.validatefield('Commentaire', form.ploopi_annotation_content, "string")) return true;

    return false;
};

/**
 * Bloc d'annotations du gabarit ploopi4 (annotation::display_p4) : attributs
 * data-p4-anno-*, délégation sur le document, sans gestionnaire en ligne
 */
(function() {
    function bloc(el) {
        var section = el.closest('[data-p4-anno]');
        return section ? section.getAttribute('data-p4-anno') : null;
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest) return;
        var el;

        // Déplier, replier (l'état est gardé en session)
        if ((el = e.target.closest('[data-p4-anno-toggle]'))) {
            var id = bloc(el);
            var body = document.getElementById('annotation_list_' + id);
            if (!body) return;
            var open = body.hidden;
            body.hidden = !open;
            el.setAttribute('aria-expanded', open ? 'true' : 'false');
            ploopi.xhr.send('admin-light.php', 'ploopi_env=' + _PLOOPI_ENV + '&ploopi_op=annotation_show&object_id=' + encodeURIComponent(id));
            if (open) {
                var champ = body.querySelector('textarea');
                if (champ) champ.focus();
            }
            return;
        }

        // Supprimer une annotation (la sienne)
        if ((el = e.target.closest('[data-p4-anno-delete]'))) {
            if (!confirm('Êtes vous certain de vouloir supprimer cette annotation ?')) return;
            ploopi.xhr.send('index-quick.php', 'ploopi_env=' + _PLOOPI_ENV + '&ploopi_op=annotation_delete&ploopi_annotation_id=' + encodeURIComponent(el.getAttribute('data-p4-anno-delete')));
            ploopi.annotations.display(bloc(el));
            return;
        }

        // Fermer la fenêtre des annotations d'une étiquette
        if ((el = e.target.closest('[data-p4-anno-close]'))) {
            var popup = el.closest('.ploopi_popup');
            ploopi.popup.hide(popup ? popup.id : null);
            return;
        }

        // Autres annotations portant une étiquette
        if ((el = e.target.closest('[data-p4-anno-tag]'))) {
            ploopi.popup.show('', 420, e, 'click');
            ploopi.xhr.todiv('admin-light.php', 'ploopi_env=' + _PLOOPI_ENV + '&ploopi_op=annotation_taghistory&id_tag=' + encodeURIComponent(el.getAttribute('data-p4-anno-tag')), 'ploopi_popup');
        }
    });

    // Suggestions d'étiquettes : branchées au premier passage dans le champ
    document.addEventListener('focusin', function(e) {
        var el = e.target;
        if (!el.matches || !el.matches('[data-p4-anno-tags]') || el.getAttribute('data-p4-anno-ready')) return;
        el.setAttribute('data-p4-anno-ready', '1');
        ploopi.annotations.tag_init(bloc(el));
    });

    // Le texte est requis : message dans le bloc plutôt qu'une alerte
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.matches || !form.matches('[data-p4-anno-form]')) return;
        var champ = form.elements['ploopi_annotation_content'];
        var erreur = form.querySelector('.p4-anno-error');
        var vide = !champ || champ.value.replace(/\s+/g, '') === '';
        if (erreur) erreur.hidden = !vide;
        if (champ) champ.setAttribute('aria-invalid', vide ? 'true' : 'false');
        if (vide) {
            e.preventDefault();
            if (champ) champ.focus();
        }
    }, true);

    document.addEventListener('reset', function(e) {
        var form = e.target;
        if (!form.matches || !form.matches('[data-p4-anno-form]')) return;
        var erreur = form.querySelector('.p4-anno-error');
        if (erreur) erreur.hidden = true;
        var suggest = form.querySelector('.p4-anno-suggest');
        if (suggest) suggest.innerHTML = '';
    }, true);
})();

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

ploopi.calendar = {};

/**
 * Ne plus utiliser cette fonction, utiliser de préférence l'appel PHP : ploopi_open_calendar()
 */
ploopi.calendar.open = function(inputfield_id, event) {
    ploopi.popup.show(ploopi.xhr.send('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=calendar_open&selected_date='+jQuery('#'+inputfield_id)[0].value+'&inputfield_id='+inputfield_id), document.querySelector('.p4-app') ? 272 : 192, event, false, 'ploopi_popup_calendar',null,null, true);
};

/**
 * Choix d'une date (date::open_calendar, opération calendar_open) : ouverture, jour
 * choisi et « Aujourd'hui » par attributs, sans gestionnaire en ligne (CSP)
 */
jQuery(document).on('click', '[data-ploopi-calendar]', function(event) {
    event.preventDefault();
    var id = this.getAttribute('data-ploopi-calendar');
    var field = document.getElementById(id);
    ploopi.xhr.topopup(parseInt(this.getAttribute('data-ploopi-width'), 10) || 192, event, 'ploopi_popup_calendar', this.getAttribute('data-ploopi-url'), 'selected_date='+encodeURIComponent(field ? field.value : '')+'&inputfield_id='+encodeURIComponent(id), 'POST');
});

jQuery(document).on('click', '[data-ploopi-calendar-day]', function(event) {
    event.preventDefault();
    var calendar = jQuery(this).closest('[data-ploopi-calendar-field]');
    var id = calendar.attr('data-ploopi-calendar-field');
    var field = id ? document.getElementById(id) : null;
    if (field) field.value = this.getAttribute('data-ploopi-calendar-day');
    ploopi.popup.hide('ploopi_popup_calendar');
    if (field) ploopi.event.dispatch_onchange(id);
});

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
 * Planning du socle, rendu moderne (ploopi\calendar sous ploopi4) : déplacement d'un
 * événement (data-calendar-drop) par son en-tête, à la souris, au stylet ou au doigt,
 * vers une autre journée et une autre heure, à la demi-heure. La requête est celle du
 * rendu historique : calendar_event_date (AAAAMMJJ) et calendar_event_hour (12.5 =
 * 12 h 30), envoyée à l'URL de l'événement, réponse affichée dans sa zone.
 */

ploopi.calendargrid = (function() {

    var drag = null;

    var dayAt = function(x, y, ev) {
        ev.style.visibility = 'hidden';
        var under = document.elementFromPoint(x, y);
        ev.style.visibility = '';
        var day = under ? under.closest('.p4-cal-day[data-calendar-date]') : null;
        return day && day.closest('.p4-cal') === ev.closest('.p4-cal') ? day : null;
    };

    var mark = function(day) {
        if (drag.day && drag.day !== day) drag.day.classList.remove('is-drop-target');
        if (day) day.classList.add('is-drop-target');
        drag.day = day;
    };

    var end = function() {
        if (!drag) return null;
        var d = drag;
        drag = null;
        if (d.day) d.day.classList.remove('is-drop-target');
        d.ev.classList.remove('is-dragging');
        d.ev.style.transform = '';
        return d;
    };

    jQuery(document).on('pointerdown', '.p4-cal-ev.is-draggable .p4-cal-ev-head', function(e) {
        if (e.button !== 0 || jQuery(e.target).closest('a, button').length) return;
        var ev = this.closest('.p4-cal-ev');
        drag = { ev: ev, x: e.clientX, y: e.clientY, dy: e.clientY - ev.getBoundingClientRect().top, moved: false, day: null };
        if (this.setPointerCapture) this.setPointerCapture(e.pointerId);
        e.preventDefault();
    });

    jQuery(document).on('pointermove', function(e) {
        if (!drag) return;
        var dx = e.clientX - drag.x, dy = e.clientY - drag.y;
        if (!drag.moved && Math.abs(dx) + Math.abs(dy) < 5) return;
        drag.moved = true;
        drag.ev.classList.add('is-dragging');
        drag.ev.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
        mark(dayAt(e.clientX, e.clientY, drag.ev));
    });

    jQuery(document).on('pointercancel', end);

    jQuery(document).on('pointerup', function(e) {
        if (!drag) return;
        var day = drag.moved ? dayAt(e.clientX, e.clientY, drag.ev) : null;
        var d = end();
        if (!day) return;

        var root = day.closest('.p4-cal');
        var begin = parseFloat(root.getAttribute('data-calendar-hour-begin')) || 0;
        var finish = parseFloat(root.getAttribute('data-calendar-hour-end')) || 24;
        var rect = day.getBoundingClientRect();

        // Heure du haut de l'événement déposé, à la demi-heure
        var hour = begin + (e.clientY - d.dy - rect.top) / rect.height * (finish - begin);
        hour = Math.min(finish - 0.5, Math.max(begin, Math.round(hour * 2) / 2));

        var target = d.ev.getAttribute('data-calendar-drop-target');
        if (!document.getElementById(target)) return;
        ploopi.xhr.todiv(d.ev.getAttribute('data-calendar-drop'), 'calendar_event_date=' + day.getAttribute('data-calendar-date') + '&calendar_event_hour=' + hour, target);
    });

    // Ouverture : la grille défile jusqu'à l'heure utile (data-calendar-scroll : premier
    // événement de la période, ou 8 h), sur la page puis dans tout contenu inséré
    var scroll = function(node) {
        jQuery(node).find('.p4-cal-days[data-calendar-scroll]').addBack('.p4-cal-days[data-calendar-scroll]').each(function() {
            var scroller = this.querySelector('.p4-cal-scroll'), hour = this.querySelector('.p4-cal-hour');
            if (!scroller || !hour) return;
            var begin = parseFloat(this.getAttribute('data-calendar-hour-begin')) || 0;
            scroller.scrollTop = Math.max(0, (parseFloat(this.getAttribute('data-calendar-scroll')) - begin) * hour.offsetHeight);
        });
    };

    jQuery(function() {
        if (ploopi.popup && ploopi.popup.addenhancer) ploopi.popup.addenhancer(scroll);
    });

    return { dayAt: dayAt, scroll: scroll };
})();

/* Simple password strength checker (c) 2010 Bermi Ferrer
*
* Inspired by http://plugins.jquery.com/project/pstrength
* Modified by OVENSIA (back to jQuery !)
*
* Distributable under the terms of an MIT-style license.
* For details, see the git site:
* http://github.com/bermi/protopass
*
*--------------------------------------------------------------------------*/

ploopi.checkpass = function(item, hook, options) {

    // Permet de gérer le conflit this
    var that = this;

    that.initialize = function(item, hook, options) {
        that.item = jQuery('#' + item).eq(0);
        that.hook = jQuery('#' + hook).eq(0);
        that.field_name = that.item.id;
        that.options = {
            messages: ["Non sûr!", "Trop court", "Très faible", "Faible", "Moyen", "Fort", "Très fort"],
            colors: ["#f00", "#999", "#f00", "#c06", "#f60", "#3c0", "#2c0"],
            scores: [10, 15, 30, 40],
            common: ["password", "123456", "123", "1234", "mypass", "pass", "letmein", "azerty", "qsdfgh", "wxcvbn"],
            minchar: 8
        };
        jQuery.extend(that.options, options || { });
        that.hook.prepend("<div class=\"password-strength-info\" id=\""+that.field_name+"_text\"></div>");
        that.hook.prepend("<div class=\"password-strength-bar\" id=\""+that.field_name+"_bar\" style=\"margin:5px 0; width: 0px;\"></div>");

        that.bar = jQuery('#' + that.field_name + "_bar").eq(0);
        that.feedback_text = jQuery('#' + that.field_name + "_text").eq(0);

        that.item.on('keyup', function() {
            that.checkUserPasswordStrength();
        });
    };

    that.checkUserPasswordStrength = function (field_name) {
        var options = that.options;
        var value = that.item[0].value;
        var field_name = that.field_name;

        strength = that.getPasswordScore(value, options);

        if (strength == -200) {
            that.displayPasswordStrengthFeedback(0, 0);
        } else {
            if (strength < 0 && strength > -199) {
                that.displayPasswordStrengthFeedback(1, 10);
            } else {
                if (strength <= options.scores[0]) {
                    that.displayPasswordStrengthFeedback(2, 10);
                } else {
                    if (strength > options.scores[0] && strength <= options.scores[1]) {
                        that.displayPasswordStrengthFeedback(3, 25);
                    } else if (strength > options.scores[1] && strength <= options.scores[2]) {
                        that.displayPasswordStrengthFeedback(4, 55);
                    } else if (strength > options.scores[2] && strength <= options.scores[3]) {
                        that.displayPasswordStrengthFeedback(5, 80);
                    } else {
                        that.displayPasswordStrengthFeedback(6, 98);
                    }
                }
            }
        }
    };

    that.displayPasswordStrengthFeedback = function(setting_index, percent_rate) {
        that.feedback_text[0].innerHTML = "<span style='color: " + that.options.colors[setting_index] + ";'>" + that.options.messages[setting_index] + "</span>";

        that.bar.css('width', percent_rate+'%');
        that.bar.css('background-color', that.options.colors[setting_index]);
        that.bar.css('height', '8px');
    };

    that.getPasswordScore = function (value, options) {
        var strength = 0;

        if (value.length < options.minchar) {
            strength = (strength - 100);
        } else {
            if (value.length >= options.minchar && value.length <= (options.minchar + 2)) {
                strength = (strength + 6);
            } else {
                if (value.length >= (options.minchar + 3) && value.length <= (options.minchar + 4)) {
                    strength = (strength + 12);
                } else {
                    if (value.length >= (options.minchar + 5)) {
                        strength = (strength + 18);
                    }
                }
            }
        }

        if (value.match(/[a-z]/)) {
            strength = (strength + 1);
        }
        if (value.match(/[A-Z]/)) {
            strength = (strength + 5);
        }
        if (value.match(/\d+/)) {
            strength = (strength + 5);
        }
        if (value.match(/(.*[0-9].*[0-9].*[0-9])/)) {
            strength = (strength + 7);
        }
        if (value.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) {
            strength = (strength + 5);
        }
        if (value.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) {
            strength = (strength + 7);
        }
        if (value.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
            strength = (strength + 2);
        }
        if (value.match(/([a-zA-Z])/) && value.match(/([0-9])/)) {
            strength = (strength + 3);
        }
        if (value.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)) {
            strength = (strength + 3);
        }
        for (var i = 0; i < options.common.length; i++) {
            if (value.toLowerCase() == options.common[i]) {
                strength = -200
            }
        }
        return strength;
    };


    that.initialize(item, hook, options);
};

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
 * Éditeur de texte riche (TinyMCE, vendor/tinymce/tinymce, GPL 2+ : license_key 'gpl').
 *
 * Point d'entrée unique du socle et des modules. Un champ devient éditeur :
 * - par son balisage : <textarea data-ploopi-editor="simple|complet"> ; les champs ajoutés
 *   plus tard (fenêtres, contenus chargés) sont détectés, sans script en ligne ;
 * - ou par ploopi.editor.create(champ, options), qui renvoie une promesse de l'éditeur.
 *
 * Attributs (et options équivalentes) :
 * - data-ploopi-editor          profil : simple (messages, descriptions), complet (articles)
 * - data-ploopi-editor-link     URL de la fenêtre de choix d'un lien (option picker.link)
 * - data-ploopi-editor-image    URL de la fenêtre de choix d'une image (option picker.image)
 * - data-ploopi-editor-css      feuilles du contenu, séparées par des virgules (option css)
 * - data-ploopi-editor-height   hauteur minimale en pixels (option height)
 * - data-ploopi-editor-objects  bouton « Insérer un objet » : objets des modules insérés
 *                               dans un contenu (<span class="ckTag">[[clé/libellé]]</span>,
 *                               liste de l'opération ploopi_getobjects) ; « 1 », ou l'URL
 *                               de l'opération hors du backoffice (option objects)
 *
 * Fenêtres de choix : elles reçoivent CKEditorFuncNum (nom hérité de CKEditor 4) et
 * rendent l'URL choisie par window.opener.ploopi.editor.picked(numéro, url).
 *
 * Le HTML existant est conservé (valid_elements '*[*]') : le contenu est filtré à
 * l'enregistrement, côté serveur (ploopi\str::htmlpurifier).
 */

ploopi.editor = (function() {
    var loading = null;
    var pickers = {};
    var seq = 0;

    var profiles = {
        simple: {
            toolbar: 'bold italic underline strikethrough removeformat | fontsize forecolor backcolor | numlist bullist | link unlink',
            plugins: 'lists link autolink autoresize',
            min_height: 220,
            max_height: 520
        },
        complet: {
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough subscript superscript removeformat | forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link unlink anchor image media table | charmap searchreplace visualblocks code fullscreen',
            plugins: 'lists advlist link autolink anchor image media table charmap searchreplace visualblocks code fullscreen autoresize',
            min_height: 420,
            max_height: 900
        }
    };

    function basepath() {
        return (typeof _PLOOPI_BASEPATH != 'undefined' && _PLOOPI_BASEPATH) ? _PLOOPI_BASEPATH : '.';
    }

    // Chargement de TinyMCE à la première demande
    function load() {
        if (window.tinymce) return Promise.resolve(window.tinymce);
        if (!loading) {
            loading = new Promise(function(resolve, reject) {
                var script = document.createElement('script');
                script.src = basepath() + '/vendor/tinymce/tinymce/tinymce.min.js';
                script.onload = function() { resolve(window.tinymce); };
                script.onerror = function() { loading = null; reject(new Error('TinyMCE introuvable')); };
                document.head.appendChild(script);
            });
        }
        return loading;
    }

    // Thème sombre de ploopi4 (data-theme sur <html>) : interface et contenu sombres
    function dark() {
        var theme = document.documentElement.getAttribute('data-theme');
        if (theme === 'dark') return true;
        return theme === 'auto' && !!window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Fenêtre de choix (lien, image) : l'URL choisie revient par ploopi.editor.picked()
    function picker(url, callback) {
        var n = ++seq;
        pickers[n] = callback;
        var win = window.open(url + (url.indexOf('?') < 0 ? '?' : '&') + 'CKEditor=editor&CKEditorFuncNum=' + n + '&langCode=fr', 'ploopi_editor_picker', 'width=860,height=620,resizable=yes,scrollbars=yes');
        if (win) win.focus();
    }

    // Éditeurs dont le champ a quitté la page (fenêtre refermée, contenu rechargé)
    function collect() {
        if (!window.tinymce) return;
        tinymce.get().slice().forEach(function(ed) {
            var el = ed.getElement();
            if (!el || !document.body.contains(el)) ed.remove();
        });
    }

    /**
     * Bouton « Insérer un objet » : choix parmi les objets insérables des modules
     * (opération ploopi_getobjects, réponse JSON clé => libellé). L'objet est une balise
     * non modifiable ; un double clic la remplace.
     */
    function objects(ed, url) {
        if (typeof url != 'string' || url === '' || url === '1') {
            url = 'admin-light.php?ploopi_env=' + encodeURIComponent(typeof _PLOOPI_ENV != 'undefined' ? _PLOOPI_ENV : '') + '&ploopi_op=ploopi_getobjects';
        }

        function dialog(current) {
            fetch(url, { credentials: 'same-origin', cache: 'no-store' })
                .then(function(r) { return r.json(); })
                .then(function(json) {
                    var items = [{ text: '(Choisir)', value: '' }];
                    for (var key in (json || {})) items.push({ text: String(json[key]), value: String(key) });
                    ed.windowManager.open({
                        title: 'Insérer un objet',
                        body: { type: 'panel', items: [{ type: 'selectbox', name: 'object', label: 'Objet', items: items }] },
                        initialData: { object: current || '' },
                        buttons: [{ type: 'cancel', text: 'Annuler' }, { type: 'submit', text: 'Insérer', primary: true }],
                        onSubmit: function(api) {
                            var key = api.getData().object;
                            if (key !== '') {
                                var label = '';
                                items.forEach(function(it) { if (it.value === key) label = it.text; });
                                var span = ed.dom.create('span', { 'class': 'ckTag' }, ed.dom.encode('[[' + key + '/' + label + ']]'));
                                ed.insertContent(ed.dom.getOuterHTML(span));
                            }
                            api.close();
                        }
                    });
                })
                .catch(function() { ed.notificationManager.open({ text: 'Liste des objets indisponible.', type: 'error' }); });
        }

        ed.ui.registry.addButton('ploopiobjects', {
            icon: 'embed',
            tooltip: 'Insérer un objet',
            onAction: function() { dialog(''); }
        });

        ed.on('dblclick', function(e) {
            var el = e.target;
            if (el && el.classList && el.classList.contains('ckTag')) {
                ed.selection.select(el);
                dialog(el.textContent.replace(/^\[\[/, '').replace(/\]\]$/, '').split('/')[0]);
            }
        });
    }

    function attr(el, name) {
        return el.getAttribute('data-ploopi-editor' + (name ? '-' + name : ''));
    }

    /**
     * Crée un éditeur sur un champ (élément ou identifiant)
     *
     * @param mixed target textarea, div (édition en place) ou identifiant
     * @param object options profile, picker {link, image}, css [], height, objects (booléen ou URL),
     *                       setup, config (options TinyMCE ; null retire l'option)
     * @return Promise éditeur
     */
    function create(target, options) {
        var el = typeof target === 'string' ? document.getElementById(target) : target;
        if (!el) return Promise.reject(new Error('Champ introuvable'));
        options = options || {};
        el.setAttribute('data-ploopi-editor-ready', '1');

        return load().then(function(tinymce) {
            collect();
            if (el.id && tinymce.get(el.id)) tinymce.get(el.id).remove();

            var profile = profiles[options.profile] || profiles.simple;
            var sombre = dark();
            var css = (options.css && options.css.length) ? options.css : [sombre ? 'dark' : 'default'];

            var config = {
                target: el,
                license_key: 'gpl',
                promotion: false,
                language: 'fr-FR',
                language_url: basepath() + '/vendor/mklkj/tinymce-i18n/langs8/fr-FR.js',
                skin: sombre ? 'oxide-dark' : 'oxide',
                content_css: css,
                menubar: false,
                statusbar: options.profile === 'complet',
                toolbar: profile.toolbar + (options.objects ? ' | ploopiobjects' : ''),
                toolbar_mode: 'wrap',
                plugins: profile.plugins,
                min_height: options.height || profile.min_height,
                max_height: profile.max_height,
                autoresize_bottom_margin: 16,
                // Contenu tel quel : URL, entités et balisage existants conservés
                convert_urls: false,
                entity_encoding: 'raw',
                valid_elements: '*[*]',
                extended_valid_elements: '*[*]',
                valid_children: '+body[style],+a[div|p|h1|h2|h3|h4|h5|h6|ul|ol|table|img]',
                // Objets insérés ([[…]]) : non modifiables, repérés à l'écran
                noneditable_class: 'ckTag',
                content_style: '.ckTag { background: #d7f3f8; color: #0e4a5a; border-radius: 3px; padding: 0 2px; cursor: default; }',
                browser_spellcheck: true,
                contextmenu: false,
                setup: function(ed) {
                    // Le champ reste à jour : formulaires envoyés en AJAX (ploopi.xhr.submit…)
                    ed.on('change input undo redo', function() { ed.save(); });
                    if (options.objects) objects(ed, options.objects);
                    if (typeof options.setup === 'function') options.setup(ed);
                }
            };

            if (options.picker && (options.picker.link || options.picker.image)) {
                config.file_picker_types = 'file image media';
                config.file_picker_callback = function(callback, value, meta) {
                    var url = (meta.filetype === 'image' && options.picker.image) ? options.picker.image : (options.picker.link || options.picker.image);
                    picker(url, function(chosen, attrs) { callback(chosen, attrs || {}); });
                };
            }

            if (options.config) {
                for (var k in options.config) {
                    if (options.config[k] === null) delete config[k];
                    else config[k] = options.config[k];
                }
            }

            return tinymce.init(config).then(function(editors) { return editors[0]; });
        });
    }

    // Champs déclarés par leur balisage
    function scan(root) {
        var list = (root || document).querySelectorAll('textarea[data-ploopi-editor]:not([data-ploopi-editor-ready]), div[data-ploopi-editor]:not([data-ploopi-editor-ready])');
        Array.prototype.forEach.call(list, function(el) {
            var css = attr(el, 'css');
            var objs = attr(el, 'objects');
            create(el, {
                profile: attr(el) || 'simple',
                picker: { link: attr(el, 'link'), image: attr(el, 'image') },
                css: css ? css.split(',') : null,
                height: parseInt(attr(el, 'height'), 10) || null,
                objects: (objs && objs !== '0') ? objs : false
            });
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function() { scan(); });
    else scan();

    // Contenus ajoutés plus tard (fenêtres, chargements AJAX) : une recherche par image
    if (window.MutationObserver) {
        var pending = false;
        new MutationObserver(function(mutations) {
            if (pending) return;
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].addedNodes.length) {
                    pending = true;
                    (window.requestAnimationFrame || window.setTimeout)(function() { pending = false; scan(); });
                    return;
                }
            }
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    return {
        profiles: profiles,
        load: load,
        create: create,
        scan: scan,

        /** Recopie le contenu de tous les éditeurs dans leurs champs (avant un envoi en AJAX) */
        save: function() { if (window.tinymce) tinymce.triggerSave(); },

        /** Éditeur d'un champ (identifiant), ou null */
        get: function(id) { return window.tinymce ? tinymce.get(id) : null; },

        /** Retire l'éditeur d'un champ */
        remove: function(id) { var ed = window.tinymce ? tinymce.get(id) : null; if (ed) ed.remove(); },

        /**
         * Retour d'une fenêtre de choix
         *
         * @param int n numéro reçu par la fenêtre (CKEditorFuncNum)
         * @param string url URL choisie
         * @param object attrs attributs (alt, title, text…)
         */
        picked: function(n, url, attrs) {
            if (pickers[n]) { pickers[n](url, attrs); delete pickers[n]; }
        }
    };
})();

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

ploopi.event = {};

ploopi.event.dispatch_onchange = function(inputfield_id) {
    jQuery('#'+inputfield_id).trigger('change');
};

ploopi.event.dispatch_onclick = function(inputfield_id) {
    jQuery('#'+inputfield_id).trigger('click');
};



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

ploopi.popup = {};
ploopi.popup.nb = 0;
ploopi.popup.hooks = null;
// Fonctions appelées sur tout contenu inséré dans la page (ploopi.popup.enhance)
ploopi.popup.onadd = [];
ploopi.popup.ready = false;

/**
 * Inscrit une fonction appelée sur la page puis sur tout contenu inséré ensuite. Si la
 * page est déjà prête (script d'un module, chargé après le socle), elle est appliquée
 * aussitôt à la page.
 *
 * @param function fn fonction(nœud)
 */
ploopi.popup.addenhancer = function(fn) {
    ploopi.popup.onadd.push(fn);
    if (ploopi.popup.ready) fn(document);
};

jQuery(function() {
    ploopi.popup.hooks = jQuery('.hook');
    if (!ploopi.popup.hooks.length) ploopi.popup.hooks = jQuery('body');

    ploopi.popup.enhance(document);
    ploopi.popup.ready = true;
    // Fenêtres insérées ensuite, par quelque chemin que ce soit (ploopi.xhr.todiv, .html()…)
    if (window.MutationObserver) {
        new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                    if (mutations[i].addedNodes[j].nodeType == 1) ploopi.popup.enhance(mutations[i].addedNodes[j]);
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    }
});

/**
 * Contenu inséré dans la page : fonctions inscrites dans ploopi.popup.onadd (formulaire
 * d'un fichier de la GED…), puis fenêtres de ploopi4 (skin::create_popup, attribut
 * data-p4-popup), déplaçables par
 * leur titre et, si le paramètre system_focus_popup le demande (data-p4-popup-focus),
 * mises en vue. Remplace le script que la fenêtre portait (CSP : aucun script en ligne).
 *
 * @param node racine à examiner
 */
ploopi.popup.enhance = function(node) {
    for (var i = 0; i < ploopi.popup.onadd.length; i++) ploopi.popup.onadd[i](node);

    var list = jQuery(node).find('[data-p4-popup]').addBack('[data-p4-popup]');

    list.each(function() {
        var id = this.getAttribute('data-p4-popup');
        var popup = document.getElementById(id);
        if (!popup || this.getAttribute('data-p4-popup-ready')) return;
        this.setAttribute('data-p4-popup-ready', '1');

        if (jQuery.fn.draggable) jQuery(popup).draggable({ zindex: 99999, handle: '.handle_'+id });
        if (this.getAttribute('data-p4-popup-focus')) document.location.href = '#anchor_'+id;
    });
};

ploopi.popup.show = function(popup_content, w, e, centered, id, pposx, pposy) {

    var ploopi_popup;
    var active_effect = false;

    if (!id) id = 'ploopi_popup';


    if (!jQuery('#'+id).length) // Nouvelle popup
    {
        ploopi.popup.nb++;
        ploopi_popup = document.createElement('div');
        ploopi_popup.setAttribute('class', 'ploopi_popup');
        ploopi_popup.setAttribute('className', 'ploopi_popup'); // IE
        ploopi_popup.setAttribute('id', id);
        ploopi_popup.setAttribute('style', 'z-index:'+(10000+ploopi.popup.nb)+';');
        ploopi_popup.style.display = 'none';

        ploopi.popup.hooks[0].appendChild(ploopi_popup);

        active_effect = true;
    }

    ploopi_popup = jQuery('#'+id).eq(0);

    var wd = jQuery(window);
    var ph = jQuery(ploopi.popup.hooks[0]);

    w = parseInt(w);
    if (!w) w = 200;

    // Largeur bornée à celle de la zone d'accroche : sur un écran étroit (tablette,
    // téléphone), la fenêtre reste entière à l'écran. Sans effet sur un écran de bureau.
    var maxw = Math.max(200, ph.innerWidth() - 16);
    if (w > maxw) w = maxw;

    var posx = 0;
    var posy = 0;

    pposx = parseInt(pposx);
    pposy = parseInt(pposy);

    if (pposx) posx = pposx;
    if (pposy) posy = pposy;

    if(e) // event ok
    {
        if (e.pageX || e.pageY) {
            posx = e.pageX - ph.offset().left + ph.scrollLeft() + wd.scrollLeft();
            posy = e.pageY - ph.offset().top + ph.scrollTop() + wd.scrollTop();
        }
        else if (e.clientX || e.clientY) {
            posx = e.clientX - ph.scrollLeft() + wd.scrollLeft();
            posy = e.clientY - ph.scrollTop() + wd.scrollTop();
        }
    }
    else
    {
        switch(centered)
        {
           case false:
           break;

           default:
           case true:
                // Centrée dans la zone d'accroche (toute la largeur de la fenêtre,
                // sauf le menu latéral de ploopi4)
                if (!posx) posx = ph.innerWidth()/2 - w/2 + ph.scrollLeft() + wd.scrollLeft();
                if (!posy) posy = ph.scrollTop() + wd.scrollTop() + 20;

            break;
        }
    }


    if (typeof(popup_content) != 'undefined') ploopi_popup.html(popup_content);

    tmpleft = parseInt(posx) + 20;
    tmptop = parseInt(posy);

    if (w > 0) {
        ploopi_popup.css({
            width: w
        });
    }
    else w = parseInt(ploopi_popup.width());

    if (e && ((20 + w + parseInt(tmpleft) + ph.offset().left - ph.scrollLeft()) > parseInt(wd.width())))
    {
        tmpleft = parseInt(tmpleft) - w - 40;
    }

    // Jamais au-delà du bord gauche de la zone d'accroche
    if (tmpleft < ph.scrollLeft() + 8) tmpleft = ph.scrollLeft() + 8;

    ploopi_popup.css({
        left: tmpleft,
        top: tmptop
    });

    if (active_effect) ploopi_popup.fadeIn();
};


ploopi.popup.hide = function(id) {
    if (!id) id = 'ploopi_popup';

    if (jQuery('#'+id).length) {
        jQuery('#'+id).eq(0).fadeOut('normal', function() { this.remove(); });
    }
};


ploopi.popup.hideall = function() {

    var popups = jQuery('.ploopi_popup');

    jQuery('.ploopi_popup').each(function(key, item) {
        item.remove();
    });
};


ploopi.popup.move = function(id, e, pposx, pposy, popup_content) {
    var ploopi_popup;

    if (!id) id = 'ploopi_popup';

    ploopi_popup = jQuery('#'+id).eq(0);

    posx = 0;
    posy = 0;

    pposx = parseInt(pposx);
    pposy = parseInt(pposy);

    if (pposx) posx = pposx;
    if (pposy) posy = pposy;

    if(e) // event ok
    {
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        }
        else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft;
            posy = e.clientY + document.body.scrollTop;
        }
    }

    ploopi_popup.html(popup_content);

    tmpleft = parseInt(posx) + 20;
    tmptop = parseInt(posy);

    w = parseInt(ploopi_popup.offsetWidth);

    if (20 + w + parseInt(tmpleft) > parseInt(document.body.offsetWidth))
    {
        tmpleft = parseInt(tmpleft) - w - 40;
    }

    ploopi_popup.css({
        left: tmpleft,
        top: tmptop
    });

};


ploopi.popup.ize = function (id, w, centered, pposx, pposy)
{
    var ploopi_popup;

    if (jQuery('#'+id).length)
    {
        ploopi_popup = jQuery('#'+id);
        ploopi_popup.attr('class', 'ploopi_popup');
        ploopi_popup.attr('className', 'ploopi_popup'); // IE
        ploopi_popup.attr('style', 'display:block;z-index:'+(10000+ploopi.popup.nb)+';');

        var wd = jQuery(window);
        var ph = jQuery(ploopi.popup.hooks[0]);
        var posx, posy;

        w = parseInt(w);
        if (!w) w = 200;

        // Largeur bornée à celle de la zone d'accroche (cf. ploopi.popup.show)
        var maxw = Math.max(200, ph.innerWidth() - 16);
        if (w > maxw) w = maxw;

        switch(centered)
        {
            case false:
                // Position donnée par rapport à la fenêtre du navigateur (viewportOffset) :
                // ramenée à la zone d'accroche, qui n'est pas forcément le document
                // (ploopi4 : #pagecontent, à droite du menu latéral, défilant)
                posx = parseInt(pposx) || 0;
                posy = parseInt(pposy) || 0;
                posx = posx + wd.scrollLeft() - ph.offset().left + ph.scrollLeft();
                posy = posy + wd.scrollTop() - ph.offset().top + ph.scrollTop();
            break;

            default:

            case true:
                posx = ph.innerWidth()/2 - w/2 + ph.scrollLeft() + wd.scrollLeft();
                posy = ph.scrollTop() + wd.scrollTop() + 20;
            break;
        }

        // Toujours entière dans la zone d'accroche
        if (posx + w > ph.scrollLeft() + ph.innerWidth() - 8) posx = ph.scrollLeft() + ph.innerWidth() - w - 8;
        if (posx < ph.scrollLeft() + 8) posx = ph.scrollLeft() + 8;
        if (posy < ph.scrollTop()) posy = ph.scrollTop();

        ploopi_popup.css({
            left: posx,
            top: posy,
            width: w
        });

        ph.append(ploopi_popup);
    }
};

/**
 * Bouton qui ferme une fenêtre : data-ploopi-popup-hide="<id>" (aucun gestionnaire en
 * ligne, CSP stricte)
 */
jQuery(document).on('click', '[data-ploopi-popup-hide]', function(event) {
    event.preventDefault();
    ploopi.popup.hide(this.getAttribute('data-ploopi-popup-hide'));
});

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

ploopi.skin = {};


// ploopi_skin_array_refresh
ploopi.skin.array_refresh = function(array_id, array_orderby, array_page, callback) {

    // Gabarit ploopi4 : tableau trié et paginé dans le navigateur
    var main = document.getElementById('ploopi_explorer_main_'+array_id);
    if (main && main.hasAttribute('data-p4-arr')) {
        ploopi.skin.p4arr.refresh(main, array_orderby, array_page, callback);
        return;
    }

    jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data:         {
            ploopi_env: _PLOOPI_ENV,
            ploopi_op: 'ploopi_skin_array_refresh',
            array_id: array_id,
            array_orderby: array_orderby,
            array_page: array_page,
            ploopi_randomize: Math.random()
        },
        async: true,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    }).done(function(html) {
        jQuery('#ploopi_explorer_main_'+array_id).html(html);
        if (callback != '') ploopi.execute_function(callback, window, array_id, array_orderby, array_page);
    });
};


/**
 * Met à jour l'affichage des tableaux générés par la classe skin.
 * Il faut corriger certains problèmes liés à l'affichage ou non d'une barre de défilement vertical.
 * Il faut également corriger les lacunes de IE.
 */

ploopi.skin.array_renderupdate = function(array_id) {
    // Rien à ajuster pour un tableau fluide (ploopi4) ou absent
    if (!jQuery('#ploopi_explorer_values_inner_'+array_id).length || !jQuery('#ploopi_explorer_values_outer_'+array_id).length) return;

    var greater = jQuery('#ploopi_explorer_values_inner_'+array_id)[0].offsetHeight > jQuery('#ploopi_explorer_values_outer_'+array_id)[0].offsetHeight;

    if (greater)
    {
        // N'existe pas ?
        if (!jQuery('#ploopi_explorer_spacer_'+array_id).length) {

            // Récupération de la largeur de la scrollbar verticale
            var scrollbar_width = jQuery('#ploopi_explorer_values_outer_'+array_id)[0].offsetWidth - jQuery('#ploopi_explorer_values_inner_'+array_id)[0].offsetWidth;

            // Insertion d'un bloc de la largeur de la scrollbar dans la ligne de titre
            jQuery('#ploopi_explorer_title_'+array_id).html(
                '<div id="ploopi_explorer_spacer_'+array_id+'" style=\'float:right;width:'+scrollbar_width+'px;\'>&nbsp;</div>'+jQuery('#ploopi_explorer_title_'+array_id).html()
            );

            var columns = jQuery('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right, 10)+scrollbar_width)+'px';
                }
            }
        }
    }
    else {

        if (jQuery('#ploopi_explorer_spacer_'+array_id).length) {

            var scrollbar_width = jQuery('#ploopi_explorer_spacer_'+array_id).width();

            jQuery('#ploopi_explorer_spacer_'+array_id).remove();

            var columns = jQuery('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right, 10)-scrollbar_width)+'px';
                }
            }
        }
    }

    if (jQuery.browser == 'msie')
    {
        var columns = jQuery('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');
        for (j=0;j<columns.length;j++)
        {
            columns[j].style.height = jQuery('#ploopi_explorer_main_'+array_id)[0].offsetHeight+'px';
        }
    }
};

ploopi.skin.treeview_shownode = function(node_id, query, script) {

    if (typeof(script) == 'undefined') script = 'admin-light.php';

    var elt = jQuery('#t'+node_id)[0];
    var dest = jQuery('#n'+node_id)[0];
    var treenode = jQuery('#treeview_node'+node_id)[0];

    // Arborescence de ploopi4 (p4-tree-node) : classes ajoutées, chevron data-p4-open ;
    // sinon, classes et images plus/minus historiques
    var p4 = treenode && treenode.classList.contains('p4-tree-node');
    var loading = function(on) {
        if (!treenode) return;
        if (p4) treenode.classList.toggle('treeview_node_loading', on);
        else treenode.className = on ? 'treeview_node_loading' : 'treeview_node';
    };

    loading(true);

    if (elt && elt.tagName == 'IMG') {
        if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
        else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');
    }
    else if (elt) elt.setAttribute('data-p4-open', elt.getAttribute('data-p4-open') == '1' ? '0' : '1');

    if (dest)
    {
        if (dest.style.display == 'none')
        {
            if (dest.innerHTML.length < 20) dest.innerHTML = ploopi.xhr.send(script, query);

            dest.style.display = 'block';
        }
        else dest.style.display = 'none';
    }

    loading(false);
};

/**
 * Tableaux avancés fluides (gabarit ploopi4, ploopi\skin::display_array) : tri et
 * pagination dans le navigateur.
 *
 * Le conteneur [data-p4-arr] porte le tri et la page initiaux ; chaque cellule d'une
 * colonne triable porte sa clé (data-p4-arr-key, calculée par le serveur comme pour
 * l'affichage historique), chaque ligne son rang d'origine (data-p4-arr-i), pour un
 * tri stable. Le tri et la page d'un tableau nommé sont mémorisés pour la session de
 * l'onglet (sessionStorage). Les tableaux insérés après le chargement (AJAX, fenêtres)
 * sont pris en charge à leur insertion.
 */

ploopi.skin.p4arr = (function() {

    var STORE = 'ploopi_arr_';
    var collator = (window.Intl && Intl.Collator) ? new Intl.Collator('fr', { numeric: true }) : null;

    var own = function(main, selector) {
        return Array.prototype.filter.call(main.querySelectorAll(selector), function(el) {
            return el.closest('[data-p4-arr]') === main;
        });
    };

    var sortButton = function(main, col) {
        return own(main, 'thead [data-p4-arr-sort]').filter(function(b) { return b.getAttribute('data-p4-arr-sort') === col; })[0] || null;
    };

    var setIcon = function(btn, name) {
        var use = btn ? btn.querySelector('svg use') : null;
        if (use) use.setAttribute('href', use.getAttribute('href').replace(/#i-[a-z0-9-]*$/, '#i-'+name));
    };

    var store = function(main, state) {
        if (!main.hasAttribute('data-p4-arr-persist')) return;
        try { sessionStorage.setItem(STORE+main.getAttribute('data-p4-arr'), JSON.stringify(state)); } catch (e) {}
    };

    var restore = function(main, state) {
        if (!main.hasAttribute('data-p4-arr-persist')) return;
        var saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(STORE+main.getAttribute('data-p4-arr')) || 'null'); } catch (e) {}
        if (!saved || typeof saved !== 'object') return;
        // Colonne relue seulement si elle est (encore) triable
        if (typeof saved.orderby === 'string' && sortButton(main, saved.orderby)) state.orderby = saved.orderby;
        if (saved.sort === 'ASC' || saved.sort === 'DESC') state.sort = saved.sort;
        if (parseInt(saved.page, 10) > 0) state.page = parseInt(saved.page, 10);
    };

    var pagerButton = function(n, label, current, pages, aria) {
        var disabled = n < 1 || n > pages;
        return '<button type="button" class="p4-btn p4-btn-sm'+(n === current && !aria ? ' p4-active" aria-current="page' : '')+'"'
            +' data-p4-arr-page="'+n+'"'+(disabled ? ' disabled' : '')+(aria ? ' aria-label="'+aria+'" title="'+aria+'"' : '')+'>'+label+'</button>';
    };

    var render = function(main) {
        var state = main.p4Arr;
        var table = own(main, 'table.p4-arr')[0];
        if (!table || !table.tBodies.length) return;
        var tbody = table.tBodies[0];
        var rows = Array.prototype.slice.call(tbody.rows);

        // Tri : clé de la colonne, puis rang d'origine ; ordre inversé en DESC
        var btn = state.orderby ? sortButton(main, state.orderby) : null;
        if (btn) {
            var idx = btn.closest('th').cellIndex;
            var numeric = btn.hasAttribute('data-p4-arr-num');
            var dir = state.sort === 'DESC' ? -1 : 1;
            var items = rows.map(function(r) {
                var cell = r.cells[idx];
                return { row: r, key: cell ? (cell.getAttribute('data-p4-arr-key') || '') : '', rank: parseInt(r.getAttribute('data-p4-arr-i'), 10) || 0 };
            });
            items.sort(function(a, b) {
                var c;
                if (numeric) c = (parseFloat(a.key) || 0) - (parseFloat(b.key) || 0);
                else c = collator ? collator.compare(a.key, b.key) : (a.key < b.key ? -1 : (a.key > b.key ? 1 : 0));
                if (!c) c = a.rank - b.rank;
                return c * dir;
            });
            rows = items.map(function(i) { return i.row; });
            var fragment = document.createDocumentFragment();
            rows.forEach(function(r) { fragment.appendChild(r); });
            tbody.appendChild(fragment);
        }

        // En-têtes et barre de tri
        own(main, 'thead [data-p4-arr-sort]').forEach(function(b) {
            var th = b.closest('th');
            if (b === btn) {
                th.setAttribute('aria-sort', state.sort === 'DESC' ? 'descending' : 'ascending');
                setIcon(b, state.sort === 'DESC' ? 'chevron-down' : 'chevron-up');
            }
            else {
                th.removeAttribute('aria-sort');
                setIcon(b, 'arrows-sort');
            }
        });
        own(main, '[data-p4-arr-select]').forEach(function(s) { if (btn) s.value = state.orderby; });
        own(main, '[data-p4-arr-dir]').forEach(function(b) { setIcon(b, state.sort === 'DESC' ? 'chevron-down' : 'chevron-up'); });

        // Pagination
        var limit = parseInt(main.getAttribute('data-p4-arr-limit'), 10) || 0;
        var total = rows.length;
        var pages = limit ? Math.max(1, Math.ceil(total / limit)) : 1;
        if (state.page > pages) state.page = pages;
        if (state.page < 1) state.page = 1;
        var from = limit ? (state.page - 1) * limit : 0;
        var to = limit ? Math.min(from + limit, total) : total;

        rows.forEach(function(r, i) { r.hidden = i < from || i >= to; });

        own(main, '[data-p4-arr-count]').forEach(function(c) {
            c.textContent = (from + 1)+'–'+to+' '+(main.getAttribute('data-p4-arr-of') || '/')+' '+total;
        });

        own(main, '[data-p4-arr-pager]').forEach(function(pager) {
            var h = '';
            if (pages > 1) {
                h += pagerButton(state.page - 1, '&lsaquo;', state.page, pages, pager.getAttribute('data-p4-arr-prev') || '&lsaquo;');
                var shown = {};
                [1, pages, state.page - 1, state.page, state.page + 1].forEach(function(n) { if (n >= 1 && n <= pages) shown[n] = true; });
                var last = 0;
                Object.keys(shown).map(Number).sort(function(a, b) { return a - b; }).forEach(function(n) {
                    if (last && n - last > 1) h += '<span class="p4-arr-gap" aria-hidden="true">…</span>';
                    h += pagerButton(n, String(n), state.page, pages);
                    last = n;
                });
                h += pagerButton(state.page + 1, '&rsaquo;', state.page, pages, pager.getAttribute('data-p4-arr-next') || '&rsaquo;');
            }
            pager.innerHTML = h;
            pager.hidden = pages < 2;
        });

        store(main, state);
    };

    // Rappel du module après une action de l'utilisateur, comme ploopi.skin.array_refresh
    var notify = function(main, callback) {
        if (typeof callback === 'undefined') callback = main.getAttribute('data-p4-arr-callback') || '';
        if (callback) ploopi.execute_function(callback, window, main.getAttribute('data-p4-arr'), main.p4Arr.orderby, main.p4Arr.page);
    };

    // Garde-fou : un conteneur de requêtes placé dans un contexte à largeur intrinsèque
    // (cellule de tableau, flottant sans largeur) s'effondrerait ; il redevient un bloc
    // ordinaire (affichage en tableau, défilement horizontal)
    var watch = function(main) {
        if (!window.ResizeObserver) return;
        var ro = new ResizeObserver(function() {
            if (main.offsetParent === null || main.classList.contains('p4-arr-nocq')) return;
            if (main.clientWidth < 120) {
                main.classList.add('p4-arr-nocq');
                ro.disconnect();
            }
        });
        ro.observe(main);
    };

    var init = function(main) {
        if (main.p4Arr) return;
        main.p4Arr = {
            orderby: main.getAttribute('data-p4-arr-orderby') || '',
            sort: main.getAttribute('data-p4-arr-sort') === 'DESC' ? 'DESC' : 'ASC',
            page: parseInt(main.getAttribute('data-p4-arr-page'), 10) || 1
        };
        restore(main, main.p4Arr);
        render(main);
        watch(main);
    };

    var initAll = function(scope) {
        if (scope.nodeType !== 1 && scope.nodeType !== 9) return;
        if (scope.nodeType === 1 && scope.hasAttribute('data-p4-arr')) init(scope);
        Array.prototype.forEach.call(scope.querySelectorAll('[data-p4-arr]'), init);
    };

    // Tri par une colonne : nouvelle colonne en ASC, même colonne en ordre inversé
    var sortBy = function(main, col) {
        var state = main.p4Arr;
        if (!state || !sortButton(main, col)) return;
        state.sort = (state.orderby === col && state.sort === 'ASC') ? 'DESC' : 'ASC';
        state.orderby = col;
        state.page = 1;
        render(main);
    };

    document.addEventListener('click', function(e) {
        // Boutons seulement : le conteneur porte aussi data-p4-arr-sort et -page (état initial),
        // et tout clic dans le tableau (liens, actions) aurait été annulé
        var el = e.target.closest ? e.target.closest('button[data-p4-arr-sort], button[data-p4-arr-page], button[data-p4-arr-dir]') : null;
        if (!el) return;
        var main = el.closest('[data-p4-arr]');
        if (!main || !main.p4Arr) return;
        e.preventDefault();

        if (el.hasAttribute('data-p4-arr-sort')) sortBy(main, el.getAttribute('data-p4-arr-sort'));
        else if (el.hasAttribute('data-p4-arr-dir')) {
            if (!main.p4Arr.orderby) return;
            main.p4Arr.sort = main.p4Arr.sort === 'ASC' ? 'DESC' : 'ASC';
            main.p4Arr.page = 1;
            render(main);
        }
        else {
            if (el.disabled) return;
            main.p4Arr.page = parseInt(el.getAttribute('data-p4-arr-page'), 10) || 1;
            render(main);
            var wrap = own(main, '.p4-table-wrap')[0];
            if (wrap && wrap.getBoundingClientRect().top < 0) main.scrollIntoView({ block: 'start' });
        }
        notify(main);
    });

    document.addEventListener('change', function(e) {
        var select = e.target.closest ? e.target.closest('[data-p4-arr-select]') : null;
        if (!select) return;
        var main = select.closest('[data-p4-arr]');
        if (!main || !main.p4Arr) return;
        main.p4Arr.orderby = '';
        sortBy(main, select.value);
        notify(main);
    });

    var start = function() {
        initAll(document);
        if (window.MutationObserver) {
            new MutationObserver(function(mutations) {
                mutations.forEach(function(m) { Array.prototype.forEach.call(m.addedNodes, initAll); });
            }).observe(document.documentElement, { childList: true, subtree: true });
        }
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();

    return {
        init: initAll,

        /**
         * Équivalent de ploopi.skin.array_refresh pour un tableau fluide : tri par une
         * colonne (même logique qu'un clic sur son en-tête) et/ou changement de page
         */
        refresh: function(main, orderby, page, callback) {
            init(main);
            if (orderby) sortBy(main, orderby);
            if (page && parseInt(page, 10) > 0) { main.p4Arr.page = parseInt(page, 10); render(main); }
            notify(main, callback || '');
        }
    };
})();

/**
 * Largeur automatique d'un rendu dessiné en pixels par le serveur (calendriers du
 * planning et des réservations en taille « Auto »).
 *
 * L'élément porte data-autowidth (largeur du dessin), data-autowidth-op (opération
 * ploopi_op de rafraîchissement), data-autowidth-param (nom du paramètre de largeur),
 * data-autowidth-target (conteneur rechargé) et, facultatif, data-autowidth-avoid
 * (fenêtre qui, quand elle flotte au-dessus, est déduite de la largeur). Quand la
 * largeur disponible diffère du dessin, l'opération est rappelée avec la largeur
 * mesurée : au chargement, après chaque remplacement du contenu, au redimensionnement.
 */

ploopi.skin.autowidth = (function() {

    var timer = null;

    var check = function(el) {
        if (!el.offsetParent) return;

        // Planning en grille fluide (rendu moderne de ploopi\calendar) : rien à redessiner
        if (el.querySelector('.p4-cal')) return;

        var width = el.clientWidth - 10;

        var avoid = el.getAttribute('data-autowidth-avoid') ? document.getElementById(el.getAttribute('data-autowidth-avoid')) : null;
        if (avoid && avoid.offsetParent && window.getComputedStyle(avoid).position != 'static') {
            var ra = avoid.getBoundingClientRect(), re = el.getBoundingClientRect();
            if (ra.left < re.right && ra.right > re.left) width = Math.min(width, Math.floor(ra.left - re.left) - 12);
        }

        if (width < 280) width = 280;
        if (Math.abs(width - (parseInt(el.getAttribute('data-autowidth'), 10) || 0)) <= 16) return;

        // Une seule demande par largeur
        if (el.getAttribute('data-autowidth-asked') == String(width)) return;
        el.setAttribute('data-autowidth-asked', String(width));

        var op = el.getAttribute('data-autowidth-op'), param = el.getAttribute('data-autowidth-param'), target = el.getAttribute('data-autowidth-target');
        if (!/^[a-z0-9_]+$/.test(op) || !/^[a-z0-9_]+$/.test(param) || !document.getElementById(target)) return;

        ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op='+op+'&'+param+'='+width, target);
    };

    var run = function() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-autowidth]'), check);
    };

    var later = function() {
        if (timer) clearTimeout(timer);
        timer = setTimeout(run, 150);
    };

    var start = function() {
        if (!document.querySelector('[data-autowidth]') && !window.MutationObserver) return;
        later();
        window.addEventListener('resize', later);
        if (window.MutationObserver) {
            new MutationObserver(function(mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                        var n = mutations[i].addedNodes[j];
                        if (n.nodeType == 1 && (n.hasAttribute('data-autowidth') || n.querySelector('[data-autowidth]'))) { later(); return; }
                    }
                }
            }).observe(document.documentElement, { childList: true, subtree: true });
        }
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();

    return { check: run };
})();

/**
 * Blocs repliables sur petit écran : <details class="ploopi_fold" open> dont le résumé
 * est masqué par la feuille du module (bloc toujours ouvert) et affiché par un gabarit
 * adaptatif sur petit écran. Quand le résumé est affiché, le bloc est replié à son
 * apparition (chargement ou insertion AJAX), pour montrer d'abord le contenu principal
 * (arbre des rubriques de l'annuaire, des dossiers de la GED).
 */

ploopi.skin.fold = (function() {

    var fold = function(scope) {
        Array.prototype.forEach.call((scope || document).querySelectorAll('details.ploopi_fold:not([data-folded])'), function(d) {
            d.setAttribute('data-folded', '1');
            var s = d.querySelector(':scope > summary');
            if (s && window.getComputedStyle(s).display != 'none') d.open = false;
        });
    };

    var start = function() {
        fold(document);
        if (window.MutationObserver) {
            new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    Array.prototype.forEach.call(m.addedNodes, function(n) { if (n.nodeType == 1 && n.querySelector) fold(n.parentNode || n); });
                });
            }).observe(document.documentElement, { childList: true, subtree: true });
        }
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();

    return { fold: fold };
})();

/**
 * Dépliage d'un nœud de l'arborescence chargé à la demande (skin::display_treeview,
 * clé 'node_attrs') : data-ploopi-treeview-node="<id>", data-ploopi-query,
 * [data-ploopi-script] ; remplace node_onclick="ploopi.skin.treeview_shownode(…)"
 */
jQuery(document).on('click', '[data-ploopi-treeview-node]', function(event) {
    event.preventDefault();
    ploopi.skin.treeview_shownode(this.getAttribute('data-ploopi-treeview-node'), this.getAttribute('data-ploopi-query') || '', this.getAttribute('data-ploopi-script') || 'admin-light.php');
});

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

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

ploopi.keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";

ploopi.base64_encode = function(input) {
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;

   do {
      chr1 = input.charCodeAt(i++);
      chr2 = input.charCodeAt(i++);
      chr3 = input.charCodeAt(i++);

      enc1 = chr1 >> 2;
      enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
      enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
      enc4 = chr3 & 63;

      if (isNaN(chr2)) {
         enc3 = enc4 = 64;
      } else if (isNaN(chr3)) {
         enc4 = 64;
      }

      output = output + ploopi.keyStr.charAt(enc1) + ploopi.keyStr.charAt(enc2) +
         ploopi.keyStr.charAt(enc3) + ploopi.keyStr.charAt(enc4);
   } while (i < input.length);

   return output;
};

ploopi.base64_decode = function(input) {
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;

   // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
   input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

   do {
      enc1 = ploopi.keyStr.indexOf(input.charAt(i++));
      enc2 = ploopi.keyStr.indexOf(input.charAt(i++));
      enc3 = ploopi.keyStr.indexOf(input.charAt(i++));
      enc4 = ploopi.keyStr.indexOf(input.charAt(i++));

      chr1 = (enc1 << 2) | (enc2 >> 4);
      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
      chr3 = ((enc3 & 3) << 6) | enc4;

      output = output + String.fromCharCode(chr1);

      if (enc3 != 64) {
         output = output + String.fromCharCode(chr2);
      }
      if (enc4 != 64) {
         output = output + String.fromCharCode(chr3);
      }
   } while (i < input.length);

   return output;
};


ploopi.addslashes = function(str) {
    str = String(str);
    str = str.replace(/\\/g,"\\\\");
    str = str.replace(/\'/g,"\\'");
    str = str.replace(/\"/g,"\\\"");
    return(str);
};

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

ploopi.subscription = {};

ploopi.subscription.display = function(ploopi_subscription_id, next)
{
    if (typeof(next) == 'undefined') next = '';
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=subscription&ploopi_subscription_id='+ploopi_subscription_id+'&next='+next, 'ploopi_subscription_'+ploopi_subscription_id);
};

ploopi.subscription.checkaction = function (id_subscription, id_action)
{
    var ck = (id_action == -1) ? jQuery('#ploopi_subscription_unsubscribe')[0] : jQuery('#ploopi_subscription_action_'+id_action)[0];

    ck.checked = !ck.checked

    if (id_action == -1 && ck.checked) jQuery('.ploopi_subscription_action').each(function(item) { this.checked = false; });

    if (id_action > -1 && jQuery('#ploopi_subscription_unsubscribe')[0] && jQuery('#ploopi_subscription_unsubscribe')[0].checked) jQuery('#ploopi_subscription_unsubscribe')[0].checked = false;

    if (id_action == 0 && ck.checked) jQuery('.ploopi_subscription_action').each(function(item) { this.checked = true; });

    if (id_action > 0 && !ck.checked && jQuery('#ploopi_subscription_action_0')[0].checked) jQuery('#ploopi_subscription_action_0')[0].checked = false;
};

/**
 * Actions des abonnements (attributs data-ploopi-action, js/actions.js) : la case est
 * basculée une fois, que le clic porte sur la case elle-même ou sur sa ligne
 */
ploopi.actions.subscription_checkaction = function(el, event, id_subscription, id_action) {
    if (event.target && event.target.type == 'checkbox') event.target.checked = !event.target.checked;
    ploopi.subscription.checkaction(id_subscription, id_action);
};

ploopi.actions.subscription_cancel = function(el, event, formid, divid) {
    var form = document.getElementById(formid);
    if (form) form.reset();
    ploopi.switchdisplay(divid);
};

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

ploopi.tickets = {};

/**
 * Largeur de la fenêtre de composition : plus large sous ploopi4 (destinataires en pastilles)
 */
ploopi.tickets.popupwidth = function() {
    return (document.body && document.body.classList.contains('p4-body')) ? 640 : 550;
};

/**
 * Sous ploopi4, la fenêtre s'ouvre centrée en haut de la page plutôt qu'au clic : ouverte
 * depuis le bas d'une liste, elle sortait de l'écran une fois l'éditeur chargé
 */
ploopi.tickets.popupevent = function(event) {
    return (document.body && document.body.classList.contains('p4-body')) ? null : event;
};

/**
 * Ouverture d'un popup pour envoyer un nouveau message
 */

// ploopi.tickets.create
ploopi.tickets.create = function(event, id_object, id_record, object_label, id_user, reload, x, y) {
    var data = '';

    // Valeurs encodées : un libellé d'objet (nom de document…) peut contenir & ou #
    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+encodeURIComponent(object_label);
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+encodeURIComponent(id_object);
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+encodeURIComponent(id_record);
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+encodeURIComponent(reload);
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+encodeURIComponent(id_user);

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi.popup.show('', ploopi.tickets.popupwidth(), ploopi.tickets.popupevent(event), true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_new'+data,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    // Le champ du message porte data-ploopi-editor : l'éditeur se crée seul (js/editor.js)
    request.done(function(html) {
        jQuery('#system_popupticket').html(html);
    });

};

ploopi.tickets.open = function(event, query) {
    var data = '';

    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+object_label;
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+id_object;
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+id_record;
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+reload;
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+id_user;

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi.popup.show('', ploopi.tickets.popupwidth(), ploopi.tickets.popupevent(event), true, 'system_popupticket', x, y);
    ploopi.xhr.ajaxloader('system_popupticket');

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: query,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    // Le champ du message porte data-ploopi-editor : l'éditeur se crée seul (js/editor.js)
    request.done(function(html) {
        jQuery('#system_popupticket').html(html);
    });

};


/**
 * Rafraichissement de la zone indiquant le nombre de tickets non lus + alerte sur nouveau ticket
 *
 * index-quick.php n'exécute les opérations du socle qu'avec un jeton valide : la requête
 * transporte celui de la page (_PLOOPI_ENV). Le nombre est écrit dans #tpl_ploopi_tickets_new
 * (gabarits du backoffice) et diffusé par l'événement « ploopi_tickets_count » sur document,
 * pour les gabarits qui en tirent un affichage (ploopi3 : pastille).
 */

// ploopi.tickets.refresh
ploopi.tickets.refresh = function(lastnewticket, timeout, str_left, str_right) {

    var intPloopiLastNewTicket = lastnewticket;
    var boolAlert = false;

    if (typeof(str_left) == 'undefined') str_left = '';
    if (typeof(str_right) == 'undefined') str_right = '';

    (function worker() {
        jQuery.get('index-quick.php', { 'ploopi_op': 'tickets_getnum', 'ploopi_env': _PLOOPI_ENV }, function(data, textStatus, request) {

            // Session terminée : on cesse d'interroger le serveur
            if (request.getResponseHeader('Ploopi-Connected') != '1') return;

            var res = String(data).split(',');
            if (res.length == 2) {
                var nb = parseInt(res[0],10);
                var last = parseInt(res[1],10);

                jQuery('#tpl_ploopi_tickets_new').html(str_left+nb+str_right);
                jQuery(document).trigger('ploopi_tickets_count', [nb]);

                if (last > intPloopiLastNewTicket && !boolAlert)
                {
                    ploopi.tickets.alert();
                    boolAlert = true;
                }
                intPloopiLastNewTicket = last;
            }

            setTimeout(worker, timeout*1000);
        });
    })();
};

/**
 * Recherche de destinataires pour un message
 */
// ploopi.tickets.select_users
ploopi.tickets.select_users = function(query, filtertype, filter, dest) {

    var request = jQuery.ajax({
        type: 'POST',
        url: 'admin-light.php?'+query,
        data: {
            'ploopi_ticket_userfilter': filter,
            'ploopi_ticket_typefilter': filtertype,
            'ploopi_env': _PLOOPI_ENV
        },
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        jQuery('#'+dest).html(html);
    });
};


/**
 * Affiche un popup d'alerte à la réception de nouveaux messages
 */

// ploopi.tickets.alert
ploopi.tickets.alert = function()
{
    ploopi.popup.show('', 350, null, true, 'popup_tickets_new_alert', 0, 200);
    ploopi.xhr.ajaxloader('popup_tickets_new_alert');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_alert', 'popup_tickets_new_alert');
};


/**
 * Recherche utilisateur
 */

ploopi.tickets.selectusers_init = function()
{
    jQuery('#ploopi_ticket_userfilter')[0].onkeyup = ploopi.tickets.selectusers_keypress;
    jQuery('#ploopi_ticket_userfilter')[0].onkeypress = ploopi.tickets.selectusers_keypress;
};

ploopi.tickets.selectusers_prevent = function(e)
{
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
};

ploopi.tickets.selectusers_keypress = function(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    switch(e.keyCode)
    {
        case 9: case 13:
            ploopi.tickets.selectusers_prevent(e);
            ploopi.event.dispatch_onclick('ploopi_ticket_search_btn');
        break;

        default:
        break;
    }
};

/*
 * Contrôle spécifique au ticket, vérif qu'au moins un destinataire est sélectionné
 */
function ploopi_ticket_validateTo(field_label, field_object)
{
    var ok = true;
    var msg = new String();
    var reg = new RegExp("<FIELD_LABEL>","gi");

    if (field_object)
    {

        field_value = field_object.value;

        ok = (field_value.replace(/(^\s*)|(\s*$)/g,'').length > 0)
    }
    else
    {
        ok = false;
    }

    if (!ok)
    {
        msg = lstmsg[4];
        alert(msg.replace(reg,field_label));
    }

    return (ok);

}

/**
 * Fenêtre de composition de ploopi4 (include/op/tickets.php, ticket::selectusers) :
 * comportement porté par les attributs data-p4-ticket-*, délégué au document, sans
 * gestionnaire en ligne.
 * - data-p4-ticket-search : recherche de destinataires à la frappe (type : data-p4-ticket-type)
 * - data-p4-ticket-add / data-p4-ticket-id : ajoute un utilisateur ou les membres d'un groupe
 * - data-p4-ticket-remove : retire un destinataire
 * - data-p4-ticket-cancel : ferme la fenêtre
 * - data-p4-ticket-form : contrôle du titre et des destinataires avant l'envoi
 */
(function() {
    var timer = null;
    var seq = 0;

    function op(query, dest) {
        var url = 'admin-light.php?ploopi_env=' + encodeURIComponent(_PLOOPI_ENV) + '&' + query;
        return fetch(url, { credentials: 'same-origin', cache: 'no-store' })
            .then(function(r) { return r.text(); })
            .then(function(html) { var el = document.getElementById(dest); if (el) el.innerHTML = html; });
    }

    function search(input) {
        var n = ++seq;
        var res = document.getElementById('div_ticket_search_result');
        if (!res) return;
        if (input.value.trim() === '') { res.innerHTML = ''; return; }
        var body = new URLSearchParams();
        body.set('ploopi_ticket_userfilter', input.value);
        body.set('ploopi_ticket_typefilter', input.getAttribute('data-p4-ticket-type') || 'user');
        body.set('ploopi_env', _PLOOPI_ENV);
        fetch('admin-light.php?' + input.getAttribute('data-p4-ticket-url'), { method: 'POST', body: body, credentials: 'same-origin', cache: 'no-store' })
            .then(function(r) { return r.text(); })
            .then(function(html) { if (n === seq) res.innerHTML = html; });
    }

    function error(form, msg) {
        var p = form.querySelector('.p4-ticket-error');
        if (!p) { alert(msg); return; }
        p.textContent = msg;
        p.hidden = (msg === '');
    }

    document.addEventListener('input', function(e) {
        var input = e.target;
        if (!input.hasAttribute || !input.hasAttribute('data-p4-ticket-search')) return;
        clearTimeout(timer);
        timer = setTimeout(function() { search(input); }, 250);
    });

    document.addEventListener('keydown', function(e) {
        var input = e.target;
        if (e.key !== 'Enter' || !input.hasAttribute || !input.hasAttribute('data-p4-ticket-search')) return;
        // Entrée lance la recherche, sans envoyer le message
        e.preventDefault();
        clearTimeout(timer);
        search(input);
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest) return;
        var btn = e.target.closest('button[data-p4-ticket-type], [data-p4-ticket-add], [data-p4-ticket-remove], [data-p4-ticket-cancel]');
        if (!btn || !btn.closest('.p4-ticket-compose')) return;
        e.preventDefault();

        var form = btn.closest('form');
        if (form && !btn.hasAttribute('data-p4-ticket-type')) error(form, '');

        if (btn.hasAttribute('data-p4-ticket-cancel')) {
            ploopi.popup.hide('system_popupticket');
        }
        else if (btn.hasAttribute('data-p4-ticket-type')) {
            var input = document.getElementById('p4_ticket_search');
            Array.prototype.forEach.call(btn.parentNode.querySelectorAll('[data-p4-ticket-type]'), function(b) {
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
            if (input) { input.setAttribute('data-p4-ticket-type', btn.getAttribute('data-p4-ticket-type')); input.focus(); search(input); }
        }
        else if (btn.hasAttribute('data-p4-ticket-add')) {
            var param = btn.getAttribute('data-p4-ticket-add') === 'group' ? 'group_id' : 'user_id';
            op('ploopi_op=tickets_select_user&' + param + '=' + encodeURIComponent(btn.getAttribute('data-p4-ticket-id')), 'div_ticket_users_selected');
            btn.classList.add('p4-ticket-added');
        }
        else if (btn.hasAttribute('data-p4-ticket-remove')) {
            op('ploopi_op=tickets_select_user&remove_user_id=' + encodeURIComponent(btn.getAttribute('data-p4-ticket-remove')), 'div_ticket_users_selected');
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.hasAttribute || !form.hasAttribute('data-p4-ticket-form')) return;
        ploopi.editor.save();

        if (form.elements.ticket_title.value.trim() === '') {
            e.preventDefault();
            error(form, 'Le titre est obligatoire.');
            form.elements.ticket_title.focus();
            return;
        }
        if (form.getAttribute('data-p4-ticket-form') === 'new' && (!form.elements.system_ticket_ctrl_user_to || form.elements.system_ticket_ctrl_user_to.value === '')) {
            e.preventDefault();
            error(form, 'Choisissez au moins un destinataire.');
            var input = document.getElementById('p4_ticket_search');
            if (input) input.focus();
            return;
        }
        error(form, '');
    }, true);
})();

/*
    Copyright (c) 2002-2007 Netlor
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


ploopi.openwin = function(url,w,h,name) {
   var top = (screen.height-(h+60))/2;
   var left = (screen.width-w)/2;

   if(!name) name = 'ploopiwin';

   var ploopiwin = window.open(url,name,'top='+top+',left='+left+',width='+w+', height='+h+', status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20');

   ploopiwin.focus();

   return(ploopiwin);
};

ploopi.confirmform = function(form, message) {
    if (confirm(message)) form.submit();
};

ploopi.confirmlink = function(link, message) {
    if (confirm(message)) location.href = link;
};

ploopi.switchdisplay = function(id) {
    e = jQuery('#'+id);
    if (e) e.css('display', e.css('display') == 'none' ? 'block' : 'none');
};


ploopi.getelem = function(elem, obj) {
    if (typeof(obj) != 'object') obj = document;

    return (obj.getElementById) ? obj.getElementById(elem) : obj.all[elem];
};

ploopi.innerHTML = function(id, html) {
    if (jQuery('#'+id).length) jQuery('#'+id).html(html);
};


// clic sur une zone checkbox/radio
// génère un event équivalent au clic direct sur l'élément
ploopi.checkbox_click = function(e, inputfield_id) {

    var element = jQuery('#'+inputfield_id)[0];

    if (!element || e.target == element) return;

    // Clic sur un libellé lié à la case : le navigateur la bascule déjà (sinon, double bascule)
    var label = jQuery(e.target).closest('label')[0];
    if (label && (label.control == element || jQuery(label).find(element).length)) return;

    switch(element.type) {
        case 'radio':
            element.checked = true;
        break;

        default:
            element.checked = !element.checked;
        break;

    }

    ploopi.event.dispatch_onchange(inputfield_id);
};



ploopi.execute_function = function(functionName, context) {
  var args = Array.prototype.slice.call(arguments, 2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
  for(var i = 0; i < namespaces.length; i++) {
    context = context[namespaces[i]];
  }
  return context[func].apply(context, args);
};



ploopi.get_param = function(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
};

/**
 * Insertion d'un texte dans un champ à la position du curseur
 */
ploopi.insertatcursor = function(field, value) {
    //IE support
    if (document.selection)
    {
        field.focus();
        sel = document.selection.createRange();
        sel.text = value;
    }
    //MOZILLA/NETSCAPE support
    else if (field.selectionStart || field.selectionStart == '0')
    {
        var startPos = field.selectionStart;
        var endPos = field.selectionEnd;
        field.value = field.value.substring(0, startPos) + value + field.value.substring(endPos, field.value.length);
    }
    else
    {
       field.value += value;
    }
};

ploopi_insertatcursor = function(field, value) {
    ploopi.insertatcursor(field, value);
};

// Lot P2 — les icônes de la barre d'outils ne portent plus de onclick inline :
// create_icon() écrit data-url / data-confirm, cet écouteur délégué rejoue le
// comportement historique (confirmation éventuelle puis navigation).
jQuery(document).ready(function() {
    jQuery(document).on('click', 'a.skin_icon', function(event) {
        event.preventDefault();
        var icon = jQuery(this);
        var go = function() { document.location.href = icon.attr('data-url'); };
        var confirmMessage = icon.attr('data-confirm');
        if (confirmMessage) {
            if (confirm(confirmMessage)) go();
        }
        else go();
    });
});

// Lot P2 — fermeture des popups du socle (open_simplebloc / open_popup) :
// le bouton porte data-popup, plus aucun onclick inline.
jQuery(document).on('click', '.ploopi_popup_close', function(event) {
    event.preventDefault();
    ploopi.popup.hide(jQuery(this).attr('data-popup'));
});

// Lot P2 — tri des colonnes et pagination des tableaux avancés :
// le socle écrit data-array / data-orderby / data-callback (tri) et
// data-array / data-page / data-callback (pagination), l'écouteur rejoue
// ploopi.skin.array_refresh.
jQuery(document).on('click', 'a.ploopi_array_sort', function(event) {
    event.preventDefault();
    var link = jQuery(this);
    ploopi.skin.array_refresh(link.attr('data-array'), link.attr('data-orderby'), '', link.attr('data-callback'));
});

jQuery(document).on('click', 'a.ploopi_array_page', function(event) {
    event.preventDefault();
    var link = jQuery(this);
    ploopi.skin.array_refresh(link.attr('data-array'), '', link.attr('data-page'), link.attr('data-callback'));
});

// Lot P2 — zones cliquables des checkbox : data-checkbox au lieu d'onclick
// inline ; l'écouteur rejoue ploopi.checkbox_click (bascule + onchange).
jQuery(document).on('click', '.ploopi_checkbox_zone', function(event) {
    ploopi.checkbox_click(event, jQuery(this).attr('data-checkbox'));
});

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

/**
 * A class to parse color values
 * @author Stoyan Stefanov <sstoo@gmail.com>
 * @link   http://www.phpied.com/rgb-color-parser-in-javascript/
 * @license Use it if you like it
 */

ploopi.rgbcolor = function(color_string) {
    this.ok = false;

    // strip any leading #
    if (color_string.charAt(0) == '#') { // remove # if any
        color_string = color_string.substr(1,6);
    }

    color_string = color_string.replace(/ /g,'');
    color_string = color_string.toLowerCase();

    // before getting into regexps, try simple matches
    // and overwrite the input
    var simple_colors = {
        aliceblue: 'f0f8ff',
        antiquewhite: 'faebd7',
        aqua: '00ffff',
        aquamarine: '7fffd4',
        azure: 'f0ffff',
        beige: 'f5f5dc',
        bisque: 'ffe4c4',
        black: '000000',
        noir: '000000',
        blanchedalmond: 'ffebcd',
        blue: '0000ff',
        bleu: '0000ff',
        blueviolet: '8a2be2',
        brown: 'a52a2a',
        burlywood: 'deb887',
        cadetblue: '5f9ea0',
        chartreuse: '7fff00',
        chocolate: 'd2691e',
        coral: 'ff7f50',
        cornflowerblue: '6495ed',
        cornsilk: 'fff8dc',
        crimson: 'dc143c',
        cyan: '00ffff',
        darkblue: '00008b',
        darkcyan: '008b8b',
        darkgoldenrod: 'b8860b',
        darkgray: 'a9a9a9',
        darkgreen: '006400',
        darkkhaki: 'bdb76b',
        darkmagenta: '8b008b',
        darkolivegreen: '556b2f',
        darkorange: 'ff8c00',
        darkorchid: '9932cc',
        darkred: '8b0000',
        darksalmon: 'e9967a',
        darkseagreen: '8fbc8f',
        darkslateblue: '483d8b',
        darkslategray: '2f4f4f',
        darkturquoise: '00ced1',
        darkviolet: '9400d3',
        deeppink: 'ff1493',
        deepskyblue: '00bfff',
        dimgray: '696969',
        dodgerblue: '1e90ff',
        feldspar: 'd19275',
        firebrick: 'b22222',
        floralwhite: 'fffaf0',
        forestgreen: '228b22',
        fuchsia: 'ff00ff',
        gainsboro: 'dcdcdc',
        ghostwhite: 'f8f8ff',
        gold: 'ffd700',
        goldenrod: 'daa520',
        gray: '808080',
        gris: '808080',
        green: '008000',
        vert: '008000',
        greenyellow: 'adff2f',
        honeydew: 'f0fff0',
        hotpink: 'ff69b4',
        indianred : 'cd5c5c',
        indigo : '4b0082',
        ivory: 'fffff0',
        khaki: 'f0e68c',
        lavender: 'e6e6fa',
        lavenderblush: 'fff0f5',
        lawngreen: '7cfc00',
        lemonchiffon: 'fffacd',
        lightblue: 'add8e6',
        lightcoral: 'f08080',
        lightcyan: 'e0ffff',
        lightgoldenrodyellow: 'fafad2',
        lightgrey: 'd3d3d3',
        lightgreen: '90ee90',
        lightpink: 'ffb6c1',
        lightsalmon: 'ffa07a',
        lightseagreen: '20b2aa',
        lightskyblue: '87cefa',
        lightslateblue: '8470ff',
        lightslategray: '778899',
        lightsteelblue: 'b0c4de',
        lightyellow: 'ffffe0',
        lime: '00ff00',
        limegreen: '32cd32',
        linen: 'faf0e6',
        magenta: 'ff00ff',
        maroon: '800000',
        mediumaquamarine: '66cdaa',
        mediumblue: '0000cd',
        mediumorchid: 'ba55d3',
        mediumpurple: '9370d8',
        mediumseagreen: '3cb371',
        mediumslateblue: '7b68ee',
        mediumspringgreen: '00fa9a',
        mediumturquoise: '48d1cc',
        mediumvioletred: 'c71585',
        midnightblue: '191970',
        mintcream: 'f5fffa',
        mistyrose: 'ffe4e1',
        moccasin: 'ffe4b5',
        navajowhite: 'ffdead',
        navy: '000080',
        oldlace: 'fdf5e6',
        olive: '808000',
        olivedrab: '6b8e23',
        orange: 'ffa500',
        orangered: 'ff4500',
        orchid: 'da70d6',
        palegoldenrod: 'eee8aa',
        palegreen: '98fb98',
        paleturquoise: 'afeeee',
        palevioletred: 'd87093',
        papayawhip: 'ffefd5',
        peachpuff: 'ffdab9',
        peru: 'cd853f',
        pink: 'ffc0cb',
        rose: 'ffc0cb',
        plum: 'dda0dd',
        powderblue: 'b0e0e6',
        purple: '800080',
        red: 'ff0000',
        rouge: 'ff0000',
        rosybrown: 'bc8f8f',
        royalblue: '4169e1',
        saddlebrown: '8b4513',
        salmon: 'fa8072',
        sandybrown: 'f4a460',
        seagreen: '2e8b57',
        seashell: 'fff5ee',
        sienna: 'a0522d',
        silver: 'c0c0c0',
        skyblue: '87ceeb',
        slateblue: '6a5acd',
        slategray: '708090',
        snow: 'fffafa',
        springgreen: '00ff7f',
        steelblue: '4682b4',
        tan: 'd2b48c',
        teal: '008080',
        thistle: 'd8bfd8',
        tomato: 'ff6347',
        turquoise: '40e0d0',
        violet: 'ee82ee',
        violetred: 'd02090',
        wheat: 'f5deb3',
        white: 'ffffff',
        blanc: 'ffffff',
        whitesmoke: 'f5f5f5',
        yellow: 'ffff00',
        jaune: 'ffff00',
        yellowgreen: '9acd32'
    };
    for (var key in simple_colors) {
        if (color_string == key) {
            color_string = simple_colors[key];
        }
    }
    // emd of simple type-in colors

    // array of color definition objects
    var color_defs = [
        {
            re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
            example: ['rgb(123, 234, 45)', 'rgb(255,234,245)'],
            process: function (bits){
                return [
                    parseInt(bits[1]),
                    parseInt(bits[2]),
                    parseInt(bits[3])
                ];
            }
        },
        {
            re: /^(\w{2})(\w{2})(\w{2})$/,
            example: ['#00ff00', '336699'],
            process: function (bits){
                return [
                    parseInt(bits[1], 16),
                    parseInt(bits[2], 16),
                    parseInt(bits[3], 16)
                ];
            }
        },
        {
            re: /^(\w{1})(\w{1})(\w{1})$/,
            example: ['#fb0', 'f0f'],
            process: function (bits){
                return [
                    parseInt(bits[1] + bits[1], 16),
                    parseInt(bits[2] + bits[2], 16),
                    parseInt(bits[3] + bits[3], 16)
                ];
            }
        }
    ];

    // search through the definitions to find a match
    for (var i = 0; i < color_defs.length; i++) {
        var re = color_defs[i].re;
        var processor = color_defs[i].process;
        var bits = re.exec(color_string);
        if (bits) {
            channels = processor(bits);
            this.r = channels[0];
            this.g = channels[1];
            this.b = channels[2];
            this.ok = true;
        }

    }

    // validate/cleanup values
    this.r = (this.r < 0 || isNaN(this.r)) ? 0 : ((this.r > 255) ? 255 : this.r);
    this.g = (this.g < 0 || isNaN(this.g)) ? 0 : ((this.g > 255) ? 255 : this.g);
    this.b = (this.b < 0 || isNaN(this.b)) ? 0 : ((this.b > 255) ? 255 : this.b);

    // some getters
    this.toRGB = function () {
        return 'rgb(' + this.r + ', ' + this.g + ', ' + this.b + ')';
    };

    this.toHex = function () {
        var r = this.r.toString(16);
        var g = this.g.toString(16);
        var b = this.b.toString(16);
        if (r.length == 1) r = '0' + r;
        if (g.length == 1) g = '0' + g;
        if (b.length == 1) b = '0' + b;
        return '#' + r + g + b;
    };

    // help
    this.getHelpXML = function () {

        var examples = new Array();
        // add regexps
        for (var i = 0; i < color_defs.length; i++) {
            var example = color_defs[i].example;
            for (var j = 0; j < example.length; j++) {
                examples[examples.length] = example[j];
            }
        }
        // add type-in colors
        for (var sc in simple_colors) {
            examples[examples.length] = sc;
        }

        var xml = document.createElement('ul');
        xml.setAttribute('id', 'rgbcolor-examples');
        for (var i = 0; i < examples.length; i++) {
            try {
                var list_item = document.createElement('li');
                var list_color = new ploopi.rgbcolor(examples[i]);
                var example_div = document.createElement('div');
                example_div.style.cssText =
                        'margin: 3px; '
                        + 'border: 1px solid black; '
                        + 'background:' + list_color.toHex() + '; '
                        + 'color:' + list_color.toHex()
                ;
                example_div.appendChild(document.createTextNode('test'));
                var list_item_value = document.createTextNode(
                    ' ' + examples[i] + ' -> ' + list_color.toRGB() + ' -> ' + list_color.toHex()
                );
                list_item.appendChild(example_div);
                list_item.appendChild(list_item_value);
                xml.appendChild(list_item);

            } catch(e){}
        }
        return xml;

    };
};

/**
 * Vérifie qu'une chaîne contient une date valide au format local (_PLOOPI_DATEFORMAT)
 *
 * Les séparateurs '/', '-' et '.' sont acceptés, ainsi que les jours/mois sur 1 ou 2
 * chiffres et les années sur 2 à 4 chiffres, comme le fait ploopi\date::local2timestamp().
 *
 * @param string date à vérifier
 * @return bool true si la date est valide
 */
ploopi.validatedate = function(field_value) {
    var date_match;
    var date_day;
    var date_month;
    var date_year;

    field_value = String(field_value).replace(/(^\s*)|(\s*$)/g, '');

    /* Format US : Y-m-d */
    if (typeof _PLOOPI_DATEFORMAT != 'undefined' && _PLOOPI_DATEFORMAT == 'Y-m-d')
    {
        date_match = field_value.match(/^([0-9]{2,4})[-\/.]([0-9]{1,2})[-\/.]([0-9]{1,2})$/);
        if (!date_match) return false;

        date_year = parseInt(date_match[1], 10);
        date_month = parseInt(date_match[2], 10);
        date_day = parseInt(date_match[3], 10);
    }
    /* Format FR (défaut) : d/m/Y */
    else
    {
        date_match = field_value.match(/^([0-9]{1,2})[-\/.]([0-9]{1,2})[-\/.]([0-9]{2,4})$/);
        if (!date_match) return false;

        date_day = parseInt(date_match[1], 10);
        date_month = parseInt(date_match[2], 10);
        date_year = parseInt(date_match[3], 10);
    }

    /* Année sur 2 chiffres : même règle que ploopi\date::local2timestamp() */
    if (date_year < 100) date_year += 2000;

    /* Vérifie que la date existe réellement (ex : 31/02/2026 est rejetée) */
    var date_test = new Date(date_year, date_month - 1, date_day);

    return (date_test.getFullYear() == date_year && date_test.getMonth() == date_month - 1 && date_test.getDate() == date_day);
};

ploopi.validatefield = function(field_label, field_object, field_type) {
    var ok = true;
    var i;
    var nbpoint = 0;
    var msg = new String();
    var reg = new RegExp("<FIELD_LABEL>","gi");

    if (field_object)
    {
        field_value = field_object.value;
        /* strip tags */
        field_label = field_label.replace(/(<([^>]+)>)/ig,"");

        /* Vérifie qu'un élément de liste a été sélectionné */
        if (field_type == 'selected')
        {
            msg = lstmsg[9];
            ok = (field_object.selectedIndex >= 0 && field_object.value != '');
        }

        /* Vérifie qu'une checkbox (ou bouton radio) à été cochée */
        if (field_type == 'checked')
        {
            ok = false;
            if(field_object.length == undefined) // Une seule case à cocher
            {
                msg = lstmsg[14];
                if (field_object.checked) ok = true;
            }
            else
            {
                msg = lstmsg[9];
                for (c = 0; c < field_object.length; c++)
                {
                    if (field_object[c].checked) ok = true;
                }
            }
        }

        /* Vérifie que le champ contient une numéro de téléphone valide */
        if (field_type == 'phone' || field_type == 'emptyphone')
        {
            if (field_type == 'emptyphone' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^\+?(\([0-9 ]+\))?[0-9 ]+$/) != -1);

            if (!ok) msg = (field_type == 'phone' && field_value.length == 0) ? lstmsg[4] : lstmsg[11];
        }

        /* Vérifie que le champ contient une adresse email valide */
        if (field_type == 'email' || field_type == 'emptyemail')
        {
            if(field_type == 'emptyemail' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^[a-zA-Z0-9]{1}([a-zA-Z0-9._-])+@[a-zA-Z0-9]{1}([a-zA-Z0-9.-])+\.[a-zA-Z]{2,64}$/) != -1);

            if (!ok) msg = (field_type == 'email' && field_value.length == 0) ? lstmsg[4] : lstmsg[0];
        }

        /* Vérifie que le champ contient une URL valide
         * ftp, http ou https, avec ou sans login/mot de passe, avec ou sans numero de port.
         */
        if (field_type == 'url' || field_type == 'emptyurl')
        {
            if(field_type == 'emptyurl' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^(((ht|f)tp(s?))\:\/\/)?(([a-zA-Z0-9]+([@\-\.]?[a-zA-Z0-9]+)*)(\:[a-zA-Z0-9\-\.]+)?@)?(www.|ftp.|[a-zA-Z]+.)?[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,})(\:[0-9]+)?\/?/g) != -1);

            if (!ok) msg = (field_type == 'url' && field_value.length == 0) ? lstmsg[4] : lstmsg[13];
        }

        /* Vérifie que le champ contient une couleur valide */
        if (field_type == 'color')
        {
            var color = new ploopi.rgbcolor(field_value);
            if (!color.ok)
            {
                ok = false;
                msg = lstmsg[10];
            }
        }

        /* Vérifie que le champ contient une chaîne non vide (espace non compris) */
        if (field_type == 'string')
        {
            ok = (field_value.replace(/(^\s*)|(\s*$)/g,'').length > 0)
            if (!ok) msg = lstmsg[4];
        }

        /* Vérifie que le champ contient une valeur entière ou vide */
        if (field_type == 'int' || field_type == 'emptyint')
        {
            if (field_type == 'emptyint' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^(\-?[0-9]+)$/) != -1);

            if (!ok) msg = (field_type == 'int' && field_value.length == 0) ? lstmsg[4] : lstmsg[5];
        }

        /* Vérifie que le champ contient une valeur réelle ou vide */

        if (field_type == 'float' || field_type == 'emptyfloat')
        {
            if (field_type == 'emptyfloat' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^(\-?((([0-9]+(\.)?)|([0-9]*\.[0-9]+))))$/) != -1);

            if (!ok) msg = (field_type == 'float' && field_value.length == 0) ? lstmsg[4] : lstmsg[6];
        }

        /* Vérifie que le champ contient une date valide ou vide */
        if (field_type == 'date' || field_type == 'emptydate')
        {
            var date_value = field_value.replace(/(^\s*)|(\s*$)/g,'');

            if (date_value.length == 0)
                ok = (field_type == 'emptydate');
            else
                ok = ploopi.validatedate(date_value);

            if (!ok) msg = (date_value.length == 0) ? lstmsg[4] : lstmsg[7];
        }

        /* Vérifie que le champ contient une heure valide ou vide */
        if (field_type == 'time' || field_type == 'emptytime')
        {
            if (field_type == 'emptytime' && field_value.length == 0)
                ok = true;
            else
                ok = (field_value.search(/^(0[0-9]|1[0-9]|2[0-4]):[0-5][0-9](:[0-5][0-9])?$/) != -1);

            if (!ok) msg = (field_type == 'time' && field_value.length == 0) ? lstmsg[4] : lstmsg[8];
        }

        /* Vérifie que le champ captcha est correct */
        if (field_type == 'captcha')
        {
            ok = false;
            if(arguments.length >= 4 && field_value.length > 0)
            {
                jQuery.ajax({
                    url: arguments[3],
                    async: false,
                    type: 'GET',
                    data: { value: field_value },
                    success: function(response) {
                        ok = (response == 1);
                    }
                });
            }

            if((!ok) && arguments.length >= 6 && jQuery(arguments[4]) && arguments[5].length > 0)
                jQuery(arguments[4]).src = arguments[5]+'&random='+Math.random();

            if (!ok) msg = (field_type == 'captcha' && field_value.length == 0) ? lstmsg[4] : lstmsg[12];
        }
    }
    else ok = false;

    if (!ok)
    {
        alert(msg.replace(reg,field_label));
        if (field_type != 'checked')
        {
            jQuery(field_object).addClass('ploopi_validate_error');
            field_object.focus();
        }
    }

    return (ok);
};

ploopi.validatereset = function(form) {
    jQuery(form).find('input,textarea,select').removeClass('ploopi_validate_error');
};

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

ploopi.xhr = {};

ploopi.xhr.ajaxloader_content = '<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.png"></div>';

ploopi.xhr.ajaxloader = function(id) {
    if (jQuery('#'+id).length) {
        jQuery('#'+id).html(ploopi.xhr.ajaxloader_content);
    }
    else return ploopi.xhr.ajaxloader_content;
}

ploopi.xhr.send = function(url, data, asynchronous, getxml, method) {

    if (typeof(asynchronous) == 'undefined') asynchronous = false;
    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var result = '';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        async: asynchronous,
        dataType: getxml ? 'xml' : 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(txt) {
        result = txt;
    });

    if (!asynchronous) return result;
};


/**
 * Affiche le contenu contenu d'une requête HTTP dans un élément de la page
 *
 * @param string url nom du script à appeler
 * @param string data paramètres complémentaires
 * @param string div identifiant de l'élément
 * @param string method méthode http à utiliser (GET/POST)
 */

ploopi.xhr.todiv = function(url, data, id, method) {
    if (typeof(method) == 'undefined') method = 'GET';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        jQuery('#'+id).html(html);
    });
};

ploopi.xhr.tocb = function(url, data, callback, ticket, getxml, method) {

    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        dataType: getxml ? 'xml' : 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(txt) {
        callback(txt, ticket);
    });

};

/**
 * Permet d'ouvrir un popup avec le contenu d'une requête HTTP
 *
 * @param int width largeur du popup
 * @param event e événement déclencheur
 * @param string id identifiant du popup
 * @param string url nom du script à appeler
 * @param string data paramètres complémentaires
 * @param string method méthode http à utiliser (GET/POST)
 * @param boolean true|false active la capture de la touche escape pour fermeture de la popup
 */

ploopi.xhr.topopup = function(width, e, id, url, data, method) {
    if (typeof(method) == 'undefined') method = 'GET';

    ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, e, 'click', id, null, null);
    ploopi.xhr.todiv(url, data, id, method);
};


/**
 * Permet de valider automatiquement un formulaire via xmlhttprequest
 *
 * @param object form formulaire
 * @param string id identifiant du popup
 * @param function beforesubmit fonction appelée avant validation (doit retourner true/false)
 *
 * @todo possibilité de ne pas renvoyer la réponse vers du contenu
 */

ploopi.xhr.submit = function(form, id, beforesubmit)
{
    var submit = true;
    if (typeof(beforesubmit) == 'function') submit = beforesubmit(form);

    query = jQuery(form).serialize();
    query += (query == '' ? '' : '&')+'ploopi_xhr=1';
    if (submit) ploopi.xhr.todiv(form.action, query, id, 'POST');
}

