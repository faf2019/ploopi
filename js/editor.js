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
