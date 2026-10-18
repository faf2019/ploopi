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
 * Module system, écran des espaces de travail (gabarits de génération 4) :
 * arborescence filtrable et repliable, liste des utilisateurs (recherche, initiale,
 * filtres, tri, pagination, sélection, actions groupées), fiche en tiroir.
 * S'appuie sur ploopi.shell (gabarit ploopi4) pour les menus et les tiroirs.
 */

(function () {
    'use strict';

    var qs = function (s, c) { return (c || document).querySelector(s); };
    var qsa = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    var shell = (window.ploopi && ploopi.shell) || {};
    var esc = shell.esc || function (s) { return String(s); };
    var icon = shell.icon || function () { return ''; };

    // Comparaison sans casse ni accents
    var norm = function (s) {
        return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    };

    /* ------------------------------------------------------------ */
    /* Arborescence                                                 */
    /* ------------------------------------------------------------ */

    function filterTree(scope, query) {
        var tree = qs('[data-p4-tree]', scope);
        if (!tree) return;
        var q = norm(query.trim());
        var shown = 0;

        // Du plus profond au plus haut : un nœud reste visible si lui ou un descendant correspond
        qsa('li', tree).reverse().forEach(function (li) {
            var lbl = qs('.p4-node-label', li);
            var text = lbl.getAttribute('data-label');
            if (text === null) { text = lbl.textContent; lbl.setAttribute('data-label', text); }
            lbl.innerHTML = esc(text);

            if (!q) { li.hidden = false; return; }

            var i = norm(text).indexOf(q);
            var sub = qsa(':scope > ul > li', li).some(function (c) { return !c.hidden; });
            li.hidden = i < 0 && !sub;
            if (sub) { li.classList.remove('p4-collapsed'); li.setAttribute('aria-expanded', 'true'); }
            if (i >= 0) {
                lbl.innerHTML = esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length));
                shown++;
            }
        });

        var empty = qs('[data-p4-tree-empty]', scope);
        if (empty) empty.hidden = !q || shown > 0;
    }

    function initTree() {
        var card = qs('.p4-tree-card');
        var drawer = qs('[data-p4-tree-drawer]');
        if (!card) return;

        // Tiroir des petits écrans : copie de l'arborescence
        if (drawer && !drawer.firstChild) {
            drawer.appendChild(qs('.p4-tree-search', card).cloneNode(true));
            drawer.appendChild(qs('.p4-tree-body', card).cloneNode(true));
        }

        qsa('[data-p4-tree-filter]').forEach(function (input) {
            var scope = input.closest('.p4-tree-card, [data-p4-tree-drawer]');
            input.addEventListener('input', function () { filterTree(scope, input.value); });
        });

        document.addEventListener('click', function (e) {
            var tw = e.target.closest('[data-p4-tree-toggle]');
            if (!tw) return;
            var li = tw.closest('li');
            var collapsed = li.classList.toggle('p4-collapsed');
            li.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });

        // Le nœud courant reste visible dans la carte défilante
        var current = qs('.p4-node.p4-active', card);
        if (current) {
            var body = qs('.p4-tree-body', card);
            if (body && current.offsetTop > body.clientHeight - 40) body.scrollTop = current.offsetTop - body.clientHeight / 2;
        }
    }

    /* ------------------------------------------------------------ */
    /* Liste des utilisateurs                                       */
    /* ------------------------------------------------------------ */

    function initUsers() {
        var root = qs('[data-p4-users]');
        if (!root) return;

        var data;
        try { data = JSON.parse(qs('[data-p4-users-data]', root).textContent); }
        catch (e) { return; }

        var L = data.labels;
        var users = data.users;
        var byId = {};
        users.forEach(function (u) {
            byId[u.id] = u;
            u.key = norm(u.ln + ' ' + u.fn + ' ' + u.login + ' ' + u.email + ' ' + u.svc + ' ' + u.ent + ' ' + u.org);
            var c = norm(u.ln).charAt(0);
            u.letter = /[a-z]/.test(c) ? c : '#';
        });

        var PAGE = 50;
        var state = { q: '', letter: '', adv: {}, sort: { key: 'name', dir: 1 }, page: 1, selected: {} };
        var list = users;

        var body = qs('[data-p4-users-body]', root);
        var search = qs('[data-p4-users-search]', root);
        var alpha = qs('[data-p4-alpha]', root);
        var bulk = qs('[data-p4-bulk]', root);
        var summary = qs('[data-p4-users-summary]', root);
        var pager = qs('[data-p4-pager]', root);
        var checkAll = qs('[data-p4-check-all]', root);
        var filtersBtn = qs('[data-p4-users-filters]', root);
        var filtersPanel = qs('#p4-users-filters');
        var cols = qsa('thead th', root).length;

        var sortLabels = {};
        qsa('[data-p4-sort]', root).forEach(function (b) { sortLabels[b.getAttribute('data-p4-sort')] = b.textContent.trim().toLowerCase(); });

        function initials(u) {
            var s = (u.fn.trim().charAt(0) + u.ln.trim().charAt(0)).toUpperCase();
            return s || u.login.slice(0, 2).toUpperCase();
        }
        function tone(u) { return (u.ln.charCodeAt(0) || 0) % 5; }

        function filtered() {
            var q = norm(state.q.trim());
            var a = state.adv;
            return users.filter(function (u) {
                if (state.letter && u.letter !== state.letter) return false;
                if (q && u.key.indexOf(q) < 0) return false;
                if (a.ln && norm(u.ln).indexOf(norm(a.ln)) < 0) return false;
                if (a.fn && norm(u.fn).indexOf(norm(a.fn)) < 0) return false;
                if (a.login && norm(u.login).indexOf(norm(a.login)) < 0) return false;
                if (a.email && norm(u.email).indexOf(norm(a.email)) < 0) return false;
                if (a.from && (!u.last || u.last < a.from.replace(/-/g, ''))) return false;
                if (a.to && (!u.last || u.last > a.to.replace(/-/g, ''))) return false;
                return true;
            }).sort(function (x, y) {
                var k = state.sort.key, r;
                if (k === 'lvl') r = (x.lvl || 0) - (y.lvl || 0);
                else if (k === 'last') r = (x.last || '0').localeCompare(y.last || '0');
                else if (k === 'org') r = norm(x.org).localeCompare(norm(y.org));
                else if (k === 'svc') r = norm(x.svc + ' ' + x.ent).localeCompare(norm(y.svc + ' ' + y.ent));
                else r = 0;
                if (r === 0) r = norm(x.ln + ' ' + x.fn).localeCompare(norm(y.ln + ' ' + y.fn));
                return r * state.sort.dir;
            });
        }

        function renderAlpha() {
            var have = {};
            users.forEach(function (u) { have[u.letter] = true; });
            var html = '<button type="button" data-p4-letter=""' + (!state.letter ? ' class="p4-active" aria-pressed="true"' : '') + '>' + esc(L.all) + '</button>';
            'abcdefghijklmnopqrstuvwxyz#'.split('').forEach(function (c) {
                html += '<button type="button" data-p4-letter="' + c + '"' + (state.letter === c ? ' class="p4-active" aria-pressed="true"' : '') + (have[c] ? '' : ' disabled') + '>' + c.toUpperCase() + '</button>';
            });
            alpha.innerHTML = html;
        }

        function row(u) {
            var sel = !!state.selected[u.id];
            var acts = '';
            if (u.email) acts += '<a class="p4-btn p4-btn-ghost p4-btn-icon p4-btn-sm" href="mailto:' + esc(u.email) + '" title="' + esc(u.email) + '" aria-label="' + esc(u.email) + '">' + icon('mail', 'p4-icon-sm') + '</a>';
            if (u.edit) acts += '<a class="p4-btn p4-btn-ghost p4-btn-icon p4-btn-sm" href="' + esc(u.edit) + '" title="' + esc(L.edit) + '" aria-label="' + esc(L.edit) + '">' + icon('pencil', 'p4-icon-sm') + '</a>';
            if (u.detach) acts += '<button type="button" class="p4-btn p4-btn-ghost p4-btn-icon p4-btn-sm" data-p4-act="detach" title="' + esc(L.detach) + '" aria-label="' + esc(L.detach) + '">' + icon('unlink', 'p4-icon-sm') + '</button>';
            if (u.del) acts += '<button type="button" class="p4-btn p4-btn-danger-ghost p4-btn-icon p4-btn-sm" data-p4-act="delete" title="' + esc(L.delete) + '" aria-label="' + esc(L.delete) + '">' + icon('trash', 'p4-icon-sm') + '</button>';

            return '<tr data-id="' + u.id + '"' + (sel ? ' class="p4-selected"' : '') + '>' +
                '<td class="p4-chk"><input type="checkbox" data-p4-sel' + (sel ? ' checked' : '') + ' aria-label="' + esc(L.select + ' : ' + u.fn + ' ' + u.ln) + '"></td>' +
                '<td data-label="' + esc(L.user) + '"><div class="p4-u"><span class="p4-avatar p4-av-' + tone(u) + '">' + esc(initials(u)) + '</span>' +
                    '<button type="button" class="p4-u-text" data-p4-card><b>' + esc(u.ln.toUpperCase()) + (u.fn ? ', ' + esc(u.fn) : '') + '</b><small>' + esc(u.login) + '</small></button>' +
                    (u.off ? '<span class="p4-badge p4-badge-danger">' + esc(L.disabled) + '</span>' : '') + '</div></td>' +
                '<td class="p4-col-org" data-label="' + esc(sortLabels.org || '') + '">' + (u.org ? '<span class="p4-badge">' + esc(u.org) + '</span>' : '<span class="p4-muted">—</span>') + '</td>' +
                '<td class="p4-col-svc" data-label="' + esc(sortLabels.svc || '') + '">' + (u.svc || u.ent ? '<span class="p4-svc"><b>' + esc(u.svc) + '</b><small>' + esc(u.ent) + '</small></span>' : '<span class="p4-muted">—</span>') + '</td>' +
                (data.workspace ? '<td class="p4-col-lvl" data-label="' + esc(sortLabels.lvl || '') + '"><span class="p4-badge' + (u.lvl >= 20 ? ' p4-badge-brand' : '') + '">' + esc(u.lvlLabel) + '</span></td>' : '') +
                '<td class="p4-col-last p4-nowrap" data-label="' + esc(sortLabels.last || '') + '">' + (u.lastLabel ? esc(u.lastLabel) : '<span class="p4-muted">' + esc(L.never) + '</span>') + '</td>' +
                '<td class="p4-col-acts"><div class="p4-acts">' + acts + '</div></td>' +
                '</tr>';
        }

        function renderPager(pages) {
            if (pages <= 1) { pager.innerHTML = ''; return; }
            var p = state.page, html = '';
            var btn = function (n, label, aria) {
                return '<button type="button" class="p4-btn p4-btn-sm' + (n === p && !aria ? ' p4-active' : '') + '" data-p4-page="' + n + '"' + (n < 1 || n > pages ? ' disabled' : '') + (aria ? ' aria-label="' + aria + '"' : '') + '>' + label + '</button>';
            };
            html += btn(p - 1, icon('chevron-left', 'p4-icon-sm'), esc(L.prev));
            var shown = {};
            [1, pages, p - 1, p, p + 1].forEach(function (n) { if (n >= 1 && n <= pages) shown[n] = true; });
            var prev = 0;
            Object.keys(shown).map(Number).sort(function (a, b) { return a - b; }).forEach(function (n) {
                if (prev && n - prev > 1) html += '<span class="p4-muted">…</span>';
                html += btn(n, String(n));
                prev = n;
            });
            html += btn(p + 1, icon('chevron-right', 'p4-icon-sm'), esc(L.next));
            pager.innerHTML = html;
        }

        function render() {
            list = filtered();
            var pages = Math.max(1, Math.ceil(list.length / PAGE));
            if (state.page > pages) state.page = pages;
            var slice = list.slice((state.page - 1) * PAGE, state.page * PAGE);

            renderAlpha();
            body.innerHTML = slice.map(row).join('') ||
                '<tr><td colspan="' + cols + '"><div class="p4-empty">' + icon(users.length ? 'search' : 'users') + esc(users.length ? L.none : L.empty) + '</div></td></tr>';

            var total = users.length;
            var n = list.length;
            summary.textContent = (n === total ? total + ' ' + (total > 1 ? L.users.toLowerCase() : L.user.toLowerCase())
                : L.summary.replace('<NB>', n).replace('<TOTAL>', total + ' ' + (total > 1 ? L.users.toLowerCase() : L.user.toLowerCase())))
                + ' · ' + L.sortedBy + ' ' + (sortLabels[state.sort.key] || '');

            qsa('[data-p4-sort]', root).forEach(function (b) {
                var on = b.getAttribute('data-p4-sort') === state.sort.key;
                b.setAttribute('aria-sort', on ? (state.sort.dir > 0 ? 'ascending' : 'descending') : 'none');
                var use = qs('use', b);
                if (use) use.setAttribute('href', use.getAttribute('href').replace(/#i-[a-z-]+$/, on ? (state.sort.dir > 0 ? '#i-chevron-up' : '#i-chevron-down') : '#i-arrows-sort'));
            });

            renderPager(pages);
            updateBulk();
        }

        function selectedIds() { return Object.keys(state.selected); }

        function updateBulk() {
            var ids = selectedIds();
            bulk.hidden = ids.length === 0;
            qs('[data-p4-bulk-count]', bulk).textContent = ids.length + ' ' + (ids.length > 1 ? L.selectedPlural : L.selected);
            var rows = qsa('tr[data-id]', body);
            checkAll.checked = rows.length > 0 && rows.every(function (r) { return state.selected[r.getAttribute('data-id')]; });
            checkAll.indeterminate = !checkAll.checked && rows.some(function (r) { return state.selected[r.getAttribute('data-id')]; });
        }

        /* Fiche dans le tiroir */
        function openCard(u) {
            var drawer = qs('#p4-user-drawer');
            if (!drawer) return;
            var av = qs('[data-p4-card-avatar]', drawer);
            av.textContent = initials(u);
            av.className = 'p4-avatar p4-av-' + tone(u);
            qs('[data-p4-card-title]', drawer).textContent = (u.fn ? u.fn + ' ' : '') + u.ln.toUpperCase();
            var cardBody = qs('[data-p4-card-body]', drawer);
            cardBody.innerHTML = '<div class="p4-empty">' + icon('clock') + '</div>';
            var edit = qs('[data-p4-card-edit]', drawer);
            edit.hidden = !u.edit;
            edit.setAttribute('href', u.edit || '#');
            if (shell.open) shell.open('p4-user-drawer');
            jQuery.ajax({ type: 'GET', url: 'admin-light.php', data: data.card + '&system_user_id=' + u.id, dataType: 'html' })
                .done(function (html) { cardBody.innerHTML = html; });
        }

        /* Évènements */
        search.addEventListener('input', function () { state.q = search.value; state.page = 1; render(); });

        alpha.addEventListener('click', function (e) {
            var b = e.target.closest('[data-p4-letter]');
            if (!b || b.disabled) return;
            state.letter = b.getAttribute('data-p4-letter');
            state.page = 1;
            render();
        });

        qsa('[data-p4-sort]', root).forEach(function (b) {
            b.addEventListener('click', function () {
                var k = b.getAttribute('data-p4-sort');
                if (state.sort.key === k) state.sort.dir = -state.sort.dir;
                else state.sort = { key: k, dir: k === 'last' ? -1 : 1 };
                render();
            });
        });

        pager.addEventListener('click', function (e) {
            var b = e.target.closest('[data-p4-page]');
            if (!b || b.disabled) return;
            state.page = parseInt(b.getAttribute('data-p4-page'), 10);
            render();
            root.scrollIntoView({ block: 'start', behavior: 'smooth' });
        });

        checkAll.addEventListener('change', function () {
            var on = checkAll.checked;
            qsa('tr[data-id]', body).forEach(function (r) {
                if (on) state.selected[r.getAttribute('data-id')] = true;
                else delete state.selected[r.getAttribute('data-id')];
            });
            render();
        });

        qs('[data-p4-bulk-clear]', bulk).addEventListener('click', function () { state.selected = {}; render(); });

        // Détacher la sélection : seuls les utilisateurs sur lesquels on a la main sont envoyés
        bulk.addEventListener('submit', function (e) {
            var ids = selectedIds().filter(function (id) { return byId[id] && byId[id].can; });
            if (!ids.length || !window.confirm(L.confirmDetachAll)) { e.preventDefault(); return; }
            qsa('input[name="user_id[]"]', bulk).forEach(function (i) { i.parentNode.removeChild(i); });
            ids.forEach(function (id) {
                var i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'user_id[]';
                i.value = id;
                bulk.appendChild(i);
            });
        });

        // Exports : la liste filtrée ou la sélection
        root.addEventListener('click', function (e) {
            var a = e.target.closest('[data-p4-export], [data-p4-export-selection]');
            if (!a) return;
            var ids = a.hasAttribute('data-p4-export-selection') ? selectedIds() : (list.length === users.length ? [] : list.map(function (u) { return u.id; }));
            if (!ids.length) return;
            e.preventDefault();
            window.location.href = a.getAttribute('href') + '&system_directory_ids=' + ids.join(',');
        });

        body.addEventListener('click', function (e) {
            var tr = e.target.closest('tr[data-id]');
            if (!tr) return;
            var id = tr.getAttribute('data-id');
            var u = byId[id];

            if (e.target.closest('[data-p4-sel]')) {
                if (e.target.checked) state.selected[id] = true;
                else delete state.selected[id];
                tr.classList.toggle('p4-selected', !!state.selected[id]);
                updateBulk();
                return;
            }

            var act = e.target.closest('[data-p4-act]');
            if (act) {
                var kind = act.getAttribute('data-p4-act');
                if (kind === 'detach' && window.confirm(L.confirmDetach)) window.location.href = u.detach;
                if (kind === 'delete' && window.confirm(L.confirmDelete)) window.location.href = u.del;
                return;
            }

            // Fiche : le nom (bouton, accessible au clavier) ou le reste de la ligne
            if (!e.target.closest('[data-p4-card]') && e.target.closest('a, button, input, label')) return;
            openCard(u);
        });

        /* Filtres avancés */
        filtersBtn.addEventListener('click', function () {
            var open = filtersPanel.hidden;
            filtersPanel.hidden = !open;
            filtersBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) { var f = qs('input', filtersPanel); if (f) f.focus(); }
        });

        function applyFilters() {
            var adv = {}, n = 0;
            qsa('[data-p4-filter]', filtersPanel).forEach(function (i) {
                var v = i.value.trim();
                if (v) { adv[i.getAttribute('data-p4-filter')] = v; n++; }
            });
            state.adv = adv;
            state.page = 1;
            var count = qs('[data-p4-users-filters-count]', root);
            count.textContent = n;
            count.hidden = !n;
            render();
        }

        qs('[data-p4-filters-apply]', filtersPanel).addEventListener('click', applyFilters);
        qs('[data-p4-filters-reset]', filtersPanel).addEventListener('click', function () {
            qsa('[data-p4-filter]', filtersPanel).forEach(function (i) { i.value = ''; });
            applyFilters();
        });
        filtersPanel.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyFilters(); } });

        render();
    }

    function init() {
        initTree();
        initUsers();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
