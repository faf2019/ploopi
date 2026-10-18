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
 * Gabarit ploopi4 : comportements du shell (ploopi.shell).
 *
 * Menus déroulants [data-p4-menu], menu latéral (rail, tiroir), recherche,
 * thème et densité, tiroirs et fenêtres [data-p4-open] / [data-p4-close],
 * messages flottants, raccourcis clavier, page de connexion.
 * Aucun gestionnaire en ligne dans le gabarit : tout passe par la délégation
 * d'évènements, ce qui prépare une CSP effective.
 */

(function () {
    'use strict';

    if (typeof window.ploopi == 'undefined') window.ploopi = {};

    var root = document.documentElement;
    var shell = ploopi.shell = ploopi.shell || {};

    var qs = function (s, c) { return (c || document).querySelector(s); };
    var qsa = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

    /* Stockage local (préférences de ce navigateur), sans erreur en navigation privée */
    shell.store = function (k, v) {
        try {
            if (v === undefined) return window.localStorage.getItem(k);
            if (v === null) window.localStorage.removeItem(k);
            else window.localStorage.setItem(k, v);
        }
        catch (e) { return null; }
        return null;
    };

    /* Icône du sprite du gabarit */
    shell.icon = function (name, cls) {
        var sprite = document.body ? document.body.getAttribute('data-p4-sprite') : '';
        return '<svg class="p4-icon' + (cls ? ' ' + cls : '') + '" aria-hidden="true"><use href="' + sprite + '#i-' + String(name).replace(/[^a-z0-9-]/g, '') + '"/></svg>';
    };

    /* Échappement HTML */
    shell.esc = function (s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    /* Connecté : les préférences vont en base (paramètres utilisateur de system) */
    function connected() {
        return !!(document.body && document.body.hasAttribute('data-p4-module') && typeof window._PLOOPI_ENV != 'undefined');
    }

    function savePref(name, value) {
        if (!connected() || typeof jQuery == 'undefined') return;
        jQuery.ajax({
            type: 'POST',
            url: 'admin-light.php',
            data: 'ploopi_env=' + _PLOOPI_ENV + '&ploopi_op=system_display_prefs_save&' + name + '=' + encodeURIComponent(value)
        });
    }

    /* Thème : light | dark | auto */
    shell.setTheme = function (t, save) {
        if (t !== 'light' && t !== 'dark') t = 'auto';
        root.setAttribute('data-theme', t);
        qsa('[data-p4-theme]').forEach(function (b) {
            var on = b.getAttribute('data-p4-theme') === t;
            b.classList.toggle('p4-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (save) {
            // Mémorisé aussi pour la page de connexion de ce navigateur
            document.cookie = 'ploopi_theme=' + t + ';path=/;max-age=31536000;SameSite=Lax';
            savePref('system_theme', t);
        }
    };

    /* Densité : comfortable | compact */
    shell.setDensity = function (d, save) {
        if (d !== 'compact') d = 'comfortable';
        root.setAttribute('data-density', d);
        qsa('[data-p4-density]').forEach(function (b) {
            var on = b.getAttribute('data-p4-density') === d;
            b.classList.toggle('p4-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (save) savePref('system_density', d);
    };

    /* Messages flottants */
    shell.toast = function (msg, icon) {
        var box = qs('.p4-toasts');
        if (!box) {
            box = document.createElement('div');
            box.className = 'p4-toasts p4-ui';
            box.setAttribute('role', 'status');
            document.body.appendChild(box);
        }
        var t = document.createElement('div');
        t.className = 'p4-toast';
        t.innerHTML = shell.icon(icon || 'circle-check') + '<span></span>';
        t.querySelector('span').textContent = msg;
        box.appendChild(t);
        setTimeout(function () {
            t.classList.add('p4-leaving');
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 260);
        }, 2800);
    };

    /* Menus déroulants : [data-p4-menu="id"] ouvre #id */
    shell.closeMenus = function (except) {
        qsa('.p4-menu.p4-open').forEach(function (m) {
            if (m === except) return;
            m.classList.remove('p4-open');
            qsa('[data-p4-menu="' + m.id + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        });
    };

    /* Tiroirs et fenêtres : [data-p4-open="id"], fermés par [data-p4-close] ou Échap */
    var lastFocus = null;
    shell.open = function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        shell.closeMenus();
        lastFocus = document.activeElement;
        el.classList.add('p4-open');
        el.setAttribute('aria-hidden', 'false');
        var backdrop = el.previousElementSibling && el.previousElementSibling.classList.contains('p4-drawer-backdrop') ? el.previousElementSibling : qs('.p4-drawer-backdrop');
        if (backdrop) backdrop.classList.add('p4-open');
        var focus = qs('[data-p4-close]', el) || el;
        if (focus.focus) setTimeout(function () { focus.focus(); }, 30);
    };
    shell.close = function () {
        var opened = qsa('.p4-drawer.p4-open, .p4-modal.p4-open');
        opened.forEach(function (el) { el.classList.remove('p4-open'); el.setAttribute('aria-hidden', 'true'); });
        qsa('.p4-drawer-backdrop.p4-open').forEach(function (b) { b.classList.remove('p4-open'); });
        if (opened.length && lastFocus && lastFocus.focus) lastFocus.focus();
        return opened.length > 0;
    };

    /* Menu latéral : rail sur bureau, surimpression sur tablette, tiroir sur téléphone */
    var mqDesktop = window.matchMedia('(min-width: 1280px)');
    var mqTablet = window.matchMedia('(min-width: 900px) and (max-width: 1279px)');
    var mqMobile = window.matchMedia('(max-width: 760px)');

    shell.toggleSidebar = function () {
        var app = qs('.p4-app');
        if (!app) return;
        if (mqDesktop.matches) {
            app.classList.toggle('p4-collapsed');
            shell.store('ploopi4-sidebar', app.classList.contains('p4-collapsed') ? 'collapsed' : null);
        }
        else if (mqTablet.matches) app.classList.toggle('p4-expanded');
        else app.classList.toggle('p4-open');
    };

    shell.closeSidebar = function () {
        var app = qs('.p4-app');
        if (app) app.classList.remove('p4-open', 'p4-expanded');
    };

    /* Recherche intégrale */
    shell.openSearch = function () {
        var search = qs('#p4-search'), input = qs('#p4-search-input');
        if (!search || !input) return;
        if (mqMobile.matches) search.classList.add('p4-search-open');
        input.focus();
        input.select();
    };

    /**
     * Recherche intégrale : suggestions à la frappe. Le formulaire porte l'adresse
     * chiffrée de l'opération system_search_suggest (data-p4-suggest), qui renvoie la
     * liste déjà échappée. Flèches, Entrée et Échap ; Entrée sans choix lance la
     * recherche complète, comme avant.
     */
    shell.initSuggest = function () {
        var form = qs('#p4-search'), input = qs('#p4-search-input'), box = qs('#p4-search-suggest');
        if (!form || !input || !box || !form.getAttribute('data-p4-suggest') || !window.fetch) return;

        var url = form.getAttribute('data-p4-suggest'), timer = null, ctrl = null, last = '';
        var options = function () { return qsa('[role="option"]', box); };
        var current = function () { var o = options(); for (var i = 0; i < o.length; i++) { if (o[i].classList.contains('p4-active')) return i; } return -1; };
        var show = function (on) { box.hidden = !on; input.setAttribute('aria-expanded', on ? 'true' : 'false'); if (!on) setActive(-1); };
        var setActive = function (i) {
            var o = options();
            o.forEach(function (el, j) { el.classList.toggle('p4-active', j === i); el.setAttribute('aria-selected', j === i ? 'true' : 'false'); });
            if (o[i]) { input.setAttribute('aria-activedescendant', o[i].id); if (o[i].scrollIntoView) o[i].scrollIntoView({ block: 'nearest' }); }
            else input.removeAttribute('aria-activedescendant');
        };

        var query = function () {
            var v = input.value.trim();
            if (v === last) return;
            last = v;
            if (ctrl) ctrl.abort();
            if (v.length < 2) { box.innerHTML = ''; show(false); return; }
            ctrl = window.AbortController ? new AbortController() : null;
            fetch(url, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'system_search_suggest=' + encodeURIComponent(v),
                signal: ctrl ? ctrl.signal : undefined
            })
                .then(function (r) { return r.ok ? r.text() : ''; })
                .then(function (html) {
                    if (input.value.trim() !== v) return;
                    box.innerHTML = html;
                    show(html.trim() !== '' && document.activeElement === input);
                })
                .catch(function () {});
        };

        input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(query, 250); });
        input.addEventListener('focus', function () { if (box.innerHTML.trim() !== '' && input.value.trim().length >= 2) show(true); });
        input.addEventListener('blur', function () { setTimeout(function () { show(false); }, 120); });
        input.addEventListener('keydown', function (e) {
            if (box.hidden) return;
            var o = options(), i = current();
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(i + 1 >= o.length ? 0 : i + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(i <= 0 ? o.length - 1 : i - 1); }
            else if (e.key === 'Enter' && i >= 0) { e.preventDefault(); o[i].click(); }
            else if (e.key === 'Escape') { e.stopPropagation(); show(false); }
        });
        // Clic dans la liste : le champ garde le focus (sa perte referme la recherche sur téléphone)
        box.addEventListener('mousedown', function (e) { e.preventDefault(); });
        box.addEventListener('click', function (e) { if (e.target.closest('[data-p4-suggest-all]')) { show(false); form.submit(); } });
    };

    /* Mon profil : l'écran refondu (lien #tpl_profile du menu utilisateur) */
    ploopi.template = ploopi.template || {};
    ploopi.template.open_profile = function () {
        var a = qs('#tpl_profile');
        if (a && a.href) window.location.href = a.href;
    };
    // Conservée pour les appels existants
    window.tpl_open_profile = ploopi.template.open_profile;

    /* Évènements délégués */
    document.addEventListener('click', function (e) {
        var t = e.target;

        var trigger = t.closest('[data-p4-menu]');
        if (trigger) {
            var menu = document.getElementById(trigger.getAttribute('data-p4-menu'));
            if (menu) {
                var open = !menu.classList.contains('p4-open');
                shell.closeMenus(menu);
                menu.classList.toggle('p4-open', open);
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    var first = qs('.p4-menu-item', menu);
                    if (first && e.detail === 0) first.focus();
                }
            }
            e.preventDefault();
            return;
        }

        if (!t.closest('.p4-menu')) shell.closeMenus();
        else if (t.closest('.p4-menu-item') && !t.closest('[data-p4-keep-open]')) shell.closeMenus();

        var theme = t.closest('[data-p4-theme]');
        if (theme) shell.setTheme(theme.getAttribute('data-p4-theme'), true);

        var density = t.closest('[data-p4-density]');
        if (density) shell.setDensity(density.getAttribute('data-p4-density'), true);

        var opener = t.closest('[data-p4-open]');
        if (opener) { e.preventDefault(); shell.open(opener.getAttribute('data-p4-open')); return; }

        if (t.closest('[data-p4-close]')) { shell.close(); return; }

        if (t.closest('#p4-sidebar-toggle') || t.closest('#p4-sidebar-collapse')) { shell.toggleSidebar(); return; }
        if (t.closest('#p4-sidebar-close') || t.closest('#p4-backdrop')) { shell.closeSidebar(); return; }

        // Sur tablette, le menu déplié en surimpression se referme en dehors de lui
        var app = qs('.p4-app');
        if (app && app.classList.contains('p4-expanded') && !t.closest('.p4-sidebar')) app.classList.remove('p4-expanded');

        if (t.closest('#p4-search-open')) { shell.openSearch(); return; }


        // Page de connexion
        if (t.closest('#tpl_passwordlost')) { e.preventDefault(); showLogin(false); return; }
        if (t.closest('#tpl_passwordlost_cancel')) { e.preventDefault(); showLogin(true); return; }

        var pw = t.closest('[data-p4-pw]');
        if (pw) {
            var input = document.getElementById(pw.getAttribute('data-p4-pw'));
            if (input) {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                pw.setAttribute('aria-pressed', show ? 'true' : 'false');
                pw.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                var use = qs('use', pw);
                if (use) use.setAttribute('href', use.getAttribute('href').replace(/#i-[a-z-]+$/, show ? '#i-eye-off' : '#i-eye'));
                input.focus();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        var tag = document.activeElement ? document.activeElement.tagName : '';
        var inField = /^(INPUT|TEXTAREA|SELECT)$/.test(tag) || (document.activeElement && document.activeElement.isContentEditable);

        if (e.key === 'Escape') {
            if (!shell.close()) {
                shell.closeMenus();
                shell.closeSidebar();
                var search = qs('#p4-search');
                if (search && search.classList.contains('p4-search-open')) { search.classList.remove('p4-search-open'); document.activeElement.blur(); }
            }
            return;
        }

        if (!qs('.p4-app')) return;

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); shell.openSearch(); }
        else if (e.altKey && !e.ctrlKey && e.key.toLowerCase() === 'm') { e.preventDefault(); shell.toggleSidebar(); }
        else if (!inField && !e.ctrlKey && !e.metaKey && !e.altKey) {
            if (e.key === '/') { e.preventDefault(); shell.openSearch(); }
            else if (e.key === '?') { e.preventDefault(); shell.open('p4-shortcuts'); }
        }
    });

    /* Page de connexion : connexion / mot de passe perdu */
    function showLogin(login) {
        var l = qs('#login'), lost = qs('#lostpassword_form');
        if (!l || !lost) return;
        l.hidden = !login;
        lost.hidden = login;
        var input = login ? qs('#ploopi_login') : qs('#ploopi_lostpassword_login');
        if (input) input.focus();
    }

    function initLogin() {
        // Mot de passe perdu : l'identifiant ou l'adresse suffit
        var lost = qs('#lostpassword_form');
        if (lost) lost.addEventListener('submit', function (e) {
            if (lost.ploopi_lostpassword_login.value.trim() === '' && lost.ploopi_lostpassword_email.value.trim() === '') {
                e.preventDefault();
                lost.ploopi_lostpassword_login.focus();
            }
        });

        // Verrouillage des majuscules
        qsa('[data-p4-caps]').forEach(function (hint) {
            var input = document.getElementById(hint.getAttribute('data-p4-caps'));
            if (!input) return;
            ['keydown', 'keyup'].forEach(function (ev) {
                input.addEventListener(ev, function (e) { if (e.getModifierState) hint.classList.toggle('p4-show', e.getModifierState('CapsLock')); });
            });
        });

        // Mot de passe expiré : jauge de robustesse et concordance des deux saisies
        var cp = qs('[data-p4-checkpass]');
        var pnew = qs('#ploopi_password_new'), pconf = qs('#ploopi_password_new_confirm');
        if (cp && pnew && pconf) {
            if (typeof ploopi.checkpass == 'function') {
                new ploopi.checkpass('ploopi_password_new', 'checkpass', { minchar: parseInt(cp.getAttribute('data-p4-checkpass'), 10) || 6, scores: [5, 10, 20, 30] });
            }
            var check = function () {
                var bad = pnew.value !== '' && pconf.value !== '' && pnew.value !== pconf.value;
                pconf.setCustomValidity(bad ? 'Les deux saisies sont différentes.' : '');
            };
            pnew.addEventListener('change', check);
            pconf.addEventListener('input', check);
            qs('#login_form').addEventListener('submit', function (e) {
                if (pnew.value === '') { e.preventDefault(); pnew.setCustomValidity('Votre mot de passe a expiré : saisissez-en un nouveau.'); pnew.reportValidity(); pnew.setCustomValidity(''); return; }
                check();
                if (!pconf.checkValidity()) { e.preventDefault(); pconf.reportValidity(); }
            });
            pnew.focus();
        }
        else if (qs('#ploopi_login')) qs('#ploopi_login').focus();
    }

    function init() {
        shell.setTheme(root.getAttribute('data-theme'));
        shell.setDensity(root.getAttribute('data-density'));

        var app = qs('.p4-app');
        if (app) {
            // Profil incomplet à la connexion : la page est servie entière, puis l'écran Mon profil
            var profile = qs('#tpl_profile[data-p4-profile-required="1"]');
            if (profile && profile.href) { window.location.replace(profile.href); return; }

            if (shell.store('ploopi4-sidebar') === 'collapsed') app.classList.add('p4-collapsed');

            // Tiroirs et fenêtres des écrans : sortis de la zone défilante (#pagecontent,
            // contexte d'empilement propre) pour passer au-dessus du shell
            var page = qs('#pagecontent');
            if (page) qsa('.p4-drawer-backdrop, .p4-drawer, .p4-modal', page).forEach(function (el) { app.appendChild(el); });

            // Messagerie : compteur tenu à jour par ploopi.tickets.refresh, pastille masquée à zéro
            var badge = qs('#tpl_messages');
            var count = function (nb) { if (badge) badge.classList.toggle('p4-no-count', !(nb > 0)); };
            var counter = qs('#tpl_ploopi_tickets_new');
            count(counter ? parseInt(counter.textContent.replace(/[^0-9]/g, ''), 10) : 0);
            if (typeof jQuery != 'undefined') jQuery(document).on('ploopi_tickets_count', function (e, nb) { count(nb); });
            if (ploopi.tickets && typeof ploopi.tickets.refresh == 'function') {
                ploopi.tickets.refresh(parseInt(document.body.getAttribute('data-p4-lastticket'), 10) || 0, 30, '', '');
            }

            var input = qs('#p4-search-input');
            if (input) input.addEventListener('blur', function () { qs('#p4-search').classList.remove('p4-search-open'); });
            shell.initSuggest();

            // L'entrée courante du menu reste visible
            var current = qs('.p4-nav-sub a.selected') || qs('.p4-nav a.selected');
            if (current && current.scrollIntoView) current.scrollIntoView({ block: 'nearest' });
        }
        else initLogin();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
