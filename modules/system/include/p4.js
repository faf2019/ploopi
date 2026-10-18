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

/*
 * Comportements des écrans refondus du module system (gabarits de génération 4).
 *
 * Tout passe par des attributs data-p4-* posés par ploopi\system\ui ou les écrans,
 * et par délégation d'évènements sur le document : les contenus chargés plus tard
 * (admin-light.php) en profitent sans réinitialisation.
 *
 *  [data-p4-confirm]            confirmation avant de suivre un lien, d'envoyer un formulaire
 *  form[data-p4-validate]       ploopi.validatefield sur chaque [data-p4-check] (type) avec
 *                               [data-p4-label] ; [data-p4-password] : contrôle du mot de passe
 *  [data-p4-autosubmit]         envoi du formulaire au changement
 *  select[data-p4-nav]          navigation vers l'URL de l'option choisie
 *  [data-p4-checkall]           coche (data-p4-checked="1") ou décoche les cases du sélecteur
 *  [data-p4-insert]             insère le texte au curseur du champ data-p4-target
 *  [data-p4-toggles]            case qui affiche (ou masque, data-p4-toggles-off) un élément
 *  input[data-p4-date]          date du navigateur recopiée au format local dans le champ caché
 *  [data-p4-table]              tableau filtrable, triable, paginé, avec initiales
 *  [data-p4-load]               charge une URL (admin-light.php) dans data-p4-target
 *  [data-p4-search-load]        recherche : charge l'URL complétée du texte saisi
 *  [data-p4-photo-file]          photo d'un utilisateur : fichier envoyé en arrière-plan, aperçu
 *  [data-p4-photo-delete]        suppression de la photo (prise en compte à l'enregistrement)
 *  [data-p4-checkpass]          jauge de robustesse du mot de passe du champ indiqué
 *  input[data-p4-color]         sélecteur de couleur recopié dans le champ indiqué
 *  [data-p4-checkall-box]       case qui coche ou décoche les cases du sélecteur
 *  [data-p4-ticket…]            messagerie : composer, répondre, déplier un message
 */

(function () {
    'use strict';

    var qs = function (s, c) { return (c || document).querySelector(s); };
    var qsa = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

    var norm = function (s) {
        return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    };

    var p4 = window.ploopi && window.ploopi.p4 ? window.ploopi.p4 : {};

    /* Confirmation */
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-p4-confirm]');
        if (!el || el.tagName === 'FORM') return;
        // bouton d'envoi : la confirmation se fait sur le formulaire
        if (el.tagName === 'BUTTON' && el.type === 'submit' && el.form) return;
        if (!window.confirm(el.getAttribute('data-p4-confirm'))) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    /* Validation et confirmation des formulaires */
    function validate(form) {
        var fields = qsa('[data-p4-check]', form);
        for (var i = 0; i < fields.length; i++) {
            var f = fields[i];
            if (f.disabled || f.closest('[hidden]')) continue;
            var label = f.getAttribute('data-p4-label') || (f.id && qs('label[for="' + f.id + '"]', form) ? qs('label[for="' + f.id + '"]', form).textContent.replace(/\*/g, '').trim() : f.name);
            if (!ploopi.validatefield(label, f, f.getAttribute('data-p4-check'))) return false;
        }

        var pw = qs('[data-p4-password]', form);
        if (pw) {
            var confirm = document.getElementById(pw.getAttribute('data-p4-password'));
            var required = pw.hasAttribute('data-p4-password-required');
            if (pw.value === '' && confirm && confirm.value === '' && !required) return true;
            if (pw.value === '' || !confirm || pw.value !== confirm.value) {
                window.alert(pw.getAttribute('data-p4-password-mismatch'));
                return false;
            }
            var rep = ploopi.xhr.send('admin.php', 'ploopi_env=' + _PLOOPI_ENV + '&ploopi_op=ploopi_checkpasswordvalidity&password=' + encodeURIComponent(pw.value), false, false, 'POST');
            if (rep == 0) {
                window.alert(pw.getAttribute('data-p4-password-invalid'));
                return false;
            }
        }
        return true;
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-p4-validate') && !validate(form)) { e.preventDefault(); return; }
        if (form.hasAttribute('data-p4-confirm') && !window.confirm(form.getAttribute('data-p4-confirm'))) { e.preventDefault(); return; }
        var sub = e.submitter;
        if (sub && sub.hasAttribute('data-p4-confirm') && !window.confirm(sub.getAttribute('data-p4-confirm'))) e.preventDefault();
    });

    /* Changements : envoi automatique, navigation, dates, bascules d'affichage */
    document.addEventListener('change', function (e) {
        var t = e.target;

        if (t.hasAttribute('data-p4-autosubmit') && t.form) { t.form.submit(); return; }

        if (t.matches('select[data-p4-nav]')) {
            var url = t.options[t.selectedIndex] ? t.options[t.selectedIndex].value : '';
            if (url) document.location.href = url;
            return;
        }

        if (t.matches('input[data-p4-date]')) { syncDate(t); return; }

        if (t.hasAttribute('data-p4-photo-file')) { sendPhoto(t); return; }

        if (t.hasAttribute('data-p4-toggles')) applyToggle(t);

        if (t.hasAttribute('data-p4-checkall-box')) {
            qsa(t.getAttribute('data-p4-checkall-box')).forEach(function (c) { if (!c.disabled) c.checked = t.checked; });
        }
    });

    /*
     * Photo d'un utilisateur : le fichier choisi part en arrière-plan (system_send_photo),
     * l'aperçu montre la photo temporaire (system_get_photo), enregistrée avec la fiche.
     */
    function sendPhoto(input) {
        if (!input.files || !input.files[0] || !window.fetch || !window.FormData) return;
        var data = new FormData();
        data.append('system_user_photo', input.files[0]);
        var box = document.getElementById('system_user_photo');
        if (box) box.setAttribute('aria-busy', 'true');
        fetch(input.getAttribute('data-p4-photo-url'), { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function () {
                if (box) {
                    var img = document.createElement('img');
                    img.alt = '';
                    img.src = input.getAttribute('data-p4-photo-preview') + '&t=' + Date.now();
                    box.innerHTML = '';
                    box.appendChild(img);
                    box.removeAttribute('aria-busy');
                }
                var del = qs('[data-p4-photo-delete]');
                if (del) del.hidden = false;
                input.value = '';
            })
            .catch(function () { if (box) box.removeAttribute('aria-busy'); });
    }

    function syncDate(input) {
        var hidden = input.form ? input.form.querySelector('input[type="hidden"][name="' + input.getAttribute('data-p4-date') + '"]') : null;
        if (!hidden) hidden = input.nextElementSibling;
        if (!hidden) return;
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(input.value);
        if (!m) { hidden.value = ''; return; }
        hidden.value = (input.getAttribute('data-p4-date-format') || 'd/m/Y').replace('d', m[3]).replace('m', m[2]).replace('Y', m[1]);
    }

    function applyToggle(box) {
        var el = document.getElementById(box.getAttribute('data-p4-toggles'));
        if (!el) return;
        var show = box.hasAttribute('data-p4-toggles-off') ? !box.checked : box.checked;
        el.hidden = !show;
    }

    /* Clics : tout cocher, insertion au curseur, chargements */
    document.addEventListener('click', function (e) {
        var t = e.target;

        var all = t.closest('[data-p4-checkall]');
        if (all) {
            e.preventDefault();
            var on = all.getAttribute('data-p4-checked') === '1';
            qsa(all.getAttribute('data-p4-checkall')).forEach(function (c) { if (!c.disabled) c.checked = on; });
            return;
        }

        var ins = t.closest('[data-p4-insert]');
        if (ins) {
            e.preventDefault();
            var target = document.getElementById(ins.getAttribute('data-p4-target'));
            if (!target) return;
            var text = ins.getAttribute('data-p4-insert');
            var s = target.selectionStart, end = target.selectionEnd;
            if (typeof s === 'number') {
                target.value = target.value.slice(0, s) + text + target.value.slice(end);
                target.selectionStart = target.selectionEnd = s + text.length;
            }
            else target.value += text;
            target.focus();
            return;
        }

        var load = t.closest('[data-p4-load]');
        if (load) {
            e.preventDefault();
            p4.load(load.getAttribute('data-p4-load'), load.getAttribute('data-p4-target'), load.getAttribute('data-p4-toast'));
            return;
        }

        var pd = t.closest('[data-p4-photo-delete]');
        if (pd && typeof window.system_delete_photo === 'function') {
            e.preventDefault();
            window.system_delete_photo(pd.getAttribute('data-p4-photo-delete'));
            var photo = document.getElementById('system_user_photo');
            if (photo) photo.innerHTML = '';
            pd.hidden = true;
            return;
        }

        // Messagerie : composer, répondre, déplier un message (opérations historiques)
        var tn = t.closest('[data-p4-ticket-new]');
        if (tn && ploopi.tickets) { e.preventDefault(); ploopi.tickets.create(e, null, null, null, null, true); return; }

        var to = t.closest('[data-p4-ticket-open]');
        if (to && ploopi.tickets) { e.preventDefault(); ploopi.tickets.open(e, 'ploopi_env=' + _PLOOPI_ENV + '&' + to.getAttribute('data-p4-ticket-open')); return; }

        var tk = t.closest('[data-p4-ticket]');
        if (tk) { e.preventDefault(); toggleTicket(tk); return; }

        var sb = t.closest('[data-p4-search-go]');
        if (sb) {
            e.preventDefault();
            var input = document.getElementById(sb.getAttribute('data-p4-search-go'));
            if (input) searchLoad(input);
        }
    });

    /* Couleur : le sélecteur du navigateur recopie sa valeur dans le champ texte (qui peut rester vide) */
    document.addEventListener('input', function (e) {
        var t = e.target;
        if (t.matches('input[data-p4-color]')) {
            var field = document.getElementById(t.getAttribute('data-p4-color'));
            if (field) field.value = t.value;
        }
    });

    function toggleTicket(btn) {
        var id = btn.getAttribute('data-p4-ticket');
        var detail = document.getElementById('tickets_detail_' + id);
        if (!detail) return;
        var open = detail.hidden;
        detail.hidden = !open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (!open || btn.p4Opened) return;
        btn.p4Opened = true;

        var base = 'admin-light.php?ploopi_env=' + encodeURIComponent(_PLOOPI_ENV) + '&ticket_id=' + encodeURIComponent(id) + '&ploopi_op=';
        // les réponses d'un message arrivent avec lui (data-p4-ticket-reply)
        if (!btn.hasAttribute('data-p4-ticket-reply')) p4.load(base + 'tickets_open_responses', 'tickets_responses_' + id);
        if (window.jQuery) window.jQuery.ajax({ url: base + 'tickets_open', type: 'GET', cache: false });

        var item = btn.closest('[data-p4-ticket-item]');
        if (item) item.classList.remove('p4-ticket-new');
        var title = document.getElementById('tickets_title_' + id);
        if (title) title.classList.remove('p4-unread');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.matches('input[data-p4-search-load]')) {
            e.preventDefault();
            searchLoad(e.target);
        }
    });

    function searchLoad(input) {
        p4.load(input.getAttribute('data-p4-search-load') + encodeURIComponent(input.value), input.getAttribute('data-p4-target'));
    }

    /**
     * Charge une URL (admin-light.php?…) dans un élément
     */
    p4.load = function (url, targetId, toast) {
        var target = document.getElementById(targetId);
        if (!target || !window.jQuery) return;
        target.setAttribute('aria-busy', 'true');
        window.jQuery.ajax({ url: url, type: 'GET', dataType: 'html', cache: false })
            .done(function (html) {
                target.innerHTML = html;
                target.removeAttribute('aria-busy');
                initTables(target);
                if (toast && ploopi.shell && ploopi.shell.toast) ploopi.shell.toast(toast);
            })
            .fail(function () { target.removeAttribute('aria-busy'); });
    };

    /*
     * Tableaux : [data-p4-table] contient un <table> dont les lignes de <tbody> sont
     * filtrées par [data-p4-table-filter], triées par les boutons th [data-p4-col]
     * (valeur de tri : data-p4-sort-value de la cellule, sinon son texte), paginées
     * (data-p4-table="50"), filtrées par initiale (data-p4-letter des lignes,
     * [data-p4-table-alpha]). [data-p4-table-count] reçoit le nombre de lignes,
     * [data-p4-table-empty] s'affiche quand aucune ne correspond.
     */
    function initTable(root) {
        if (root.p4Table) return;
        root.p4Table = true;

        var body = qs('tbody', root);
        if (!body) return;
        var rows = qsa(':scope > tr', body);
        var perPage = parseInt(root.getAttribute('data-p4-table'), 10) || 0;
        var filter = qs('[data-p4-table-filter]', root);
        var pager = qs('[data-p4-table-pager]', root);
        var count = qs('[data-p4-table-count]', root);
        var empty = qs('[data-p4-table-empty]', root);
        var alpha = qs('[data-p4-table-alpha]', root);
        var state = { q: '', letter: '', page: 1, col: -1, dir: 1 };

        rows.forEach(function (r) { r.p4Text = norm(r.getAttribute('data-p4-text') || r.textContent); });

        if (alpha) {
            var have = {};
            rows.forEach(function (r) { have[r.getAttribute('data-p4-letter')] = true; });
            var html = '<button type="button" data-p4-letter-btn="" class="p4-active" aria-pressed="true">' + (alpha.getAttribute('data-p4-all') || '*') + '</button>';
            'abcdefghijklmnopqrstuvwxyz#'.split('').forEach(function (c) {
                html += '<button type="button" data-p4-letter-btn="' + c + '"' + (have[c] ? '' : ' disabled') + '>' + c.toUpperCase() + '</button>';
            });
            alpha.innerHTML = html;
            alpha.addEventListener('click', function (e) {
                var b = e.target.closest('[data-p4-letter-btn]');
                if (!b || b.disabled) return;
                state.letter = b.getAttribute('data-p4-letter-btn');
                state.page = 1;
                qsa('[data-p4-letter-btn]', alpha).forEach(function (x) {
                    var on = x === b;
                    x.classList.toggle('p4-active', on);
                    x.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
                render();
            });
        }

        function sortValue(r) {
            var cell = r.children[state.col];
            if (!cell) return '';
            var v = cell.getAttribute('data-p4-sort-value');
            return v !== null ? v : norm(cell.textContent.trim());
        }

        function render() {
            var q = norm(state.q).trim();
            var list = rows.filter(function (r) {
                if (state.letter && r.getAttribute('data-p4-letter') !== state.letter) return false;
                return !q || q.split(/\s+/).every(function (w) { return r.p4Text.indexOf(w) !== -1; });
            });

            if (state.col >= 0) {
                list.sort(function (a, b) {
                    var x = sortValue(a), y = sortValue(b);
                    var nx = parseFloat(x), ny = parseFloat(y);
                    var c = (!isNaN(nx) && !isNaN(ny) && String(nx) === x && String(ny) === y) ? nx - ny : String(x).localeCompare(String(y), 'fr');
                    return c * state.dir;
                });
                list.forEach(function (r) { body.appendChild(r); });
            }

            var pages = perPage ? Math.max(1, Math.ceil(list.length / perPage)) : 1;
            if (state.page > pages) state.page = pages;
            var from = perPage ? (state.page - 1) * perPage : 0;
            var to = perPage ? from + perPage : list.length;

            rows.forEach(function (r) { r.hidden = true; });
            list.slice(from, to).forEach(function (r) { r.hidden = false; });

            if (count) count.textContent = list.length;
            if (empty) empty.hidden = list.length > 0;
            var wrap = qs('.p4-table-wrap', root);
            if (wrap && empty) wrap.hidden = list.length === 0;

            if (pager) {
                pager.hidden = pages < 2;
                var h = '';
                if (pages > 1) {
                    var btn = function (n, label, aria) {
                        return '<button type="button" class="p4-btn p4-btn-sm' + (n === state.page && !aria ? ' p4-active' : '') + '" data-p4-page="' + n + '"' + (n < 1 || n > pages ? ' disabled' : '') + (aria ? ' aria-label="' + aria + '"' : '') + '>' + label + '</button>';
                    };
                    h += btn(state.page - 1, '&lsaquo;', pager.getAttribute('data-p4-prev') || '');
                    var shown = {};
                    [1, pages, state.page - 1, state.page, state.page + 1].forEach(function (n) { if (n >= 1 && n <= pages) shown[n] = true; });
                    var last = 0;
                    Object.keys(shown).map(Number).sort(function (a, b) { return a - b; }).forEach(function (n) {
                        if (last && n - last > 1) h += '<span class="p4-muted">…</span>';
                        h += btn(n, n);
                        last = n;
                    });
                    h += btn(state.page + 1, '&rsaquo;', pager.getAttribute('data-p4-next') || '');
                }
                pager.innerHTML = h;
            }
        }

        if (filter) filter.addEventListener('input', function () { state.q = filter.value; state.page = 1; render(); });

        if (pager) pager.addEventListener('click', function (e) {
            var b = e.target.closest('[data-p4-page]');
            if (!b || b.disabled) return;
            state.page = parseInt(b.getAttribute('data-p4-page'), 10);
            render();
        });

        qsa('thead [data-p4-col]', root).forEach(function (b) {
            b.addEventListener('click', function () {
                var col = b.closest('th').cellIndex;
                state.dir = state.col === col ? -state.dir : 1;
                state.col = col;
                qsa('thead [data-p4-col]', root).forEach(function (x) {
                    var th = x.closest('th');
                    if (x === b) th.setAttribute('aria-sort', state.dir > 0 ? 'ascending' : 'descending');
                    else th.removeAttribute('aria-sort');
                });
                render();
            });
        });

        // Tri initial : th[aria-sort] posé par le serveur
        var initial = qs('thead th[aria-sort]', root);
        if (initial && qs('[data-p4-col]', initial)) {
            state.col = initial.cellIndex;
            state.dir = initial.getAttribute('aria-sort') === 'descending' ? -1 : 1;
        }

        render();
    }

    function initTables(scope) { qsa('[data-p4-table]', scope).forEach(initTable); }

    /* Jauge de robustesse du mot de passe (ploopi.checkpass) */
    function initCheckpass() {
        qsa('[data-p4-checkpass]').forEach(function (el) {
            if (el.p4Checkpass || !ploopi.checkpass || !el.id) return;
            el.p4Checkpass = true;
            new ploopi.checkpass(el.getAttribute('data-p4-checkpass'), el.id, { minchar: parseInt(el.getAttribute('data-p4-minchar'), 10) || 6, scores: [5, 10, 20, 30] });
        });
    }

    function init() {
        initTables(document);
        initCheckpass();
        // Charge du serveur : rafraîchissement historique (include/functions.js)
        if (qs('[data-p4-serverload]') && typeof window.system_serverload === 'function') window.system_serverload();
        qsa('[data-p4-toggles]').forEach(applyToggle);
        qsa('input[data-p4-date]').forEach(syncDate);
    }

    p4.initTables = initTables;
    window.ploopi = window.ploopi || {};
    window.ploopi.p4 = p4;

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
