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
