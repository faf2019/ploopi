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
    Assistant d'installation (config/install/install.php) : contrôle des champs à la
    saisie, affichage du mot de passe, thème, état « en cours » du lancement.

    Les contrôles interrogent l'assistant lui-même (?ajax=<champ>&value=…), qui répond
    « ok » ou « err ». L'état est porté par le bloc .p4-install-field (p4-ok, p4-err,
    p4-wait) ; le serveur pose le même état après une tentative refusée.
*/
(function () {
    'use strict';

    var form = document.getElementById('form');
    if (!form) return;

    var root = document.documentElement;
    var seq = {};

    function el(name) { return form.elements[name]; }

    function setState(input, state) {
        var field = input && input.closest('.p4-install-field');
        if (!field) return;
        field.classList.remove('p4-ok', 'p4-err', 'p4-wait');
        if (state) field.classList.add('p4-' + state);

        var msg = document.getElementById(input.name + '_msg');
        var hint = document.getElementById(input.name + '_aide');
        var ids = [];
        if (hint) ids.push(hint.id);
        if (msg && state === 'err') ids.push(msg.id);
        if (ids.length) input.setAttribute('aria-describedby', ids.join(' '));
        else input.removeAttribute('aria-describedby');

        if (state === 'err') input.setAttribute('aria-invalid', 'true');
        else input.removeAttribute('aria-invalid');
    }

    // Contrôle par l'assistant ; seule la dernière réponse d'un même contrôle compte
    function ask(key, params, inputs) {
        var n = seq[key] = (seq[key] || 0) + 1;
        inputs.forEach(function (i) { setState(i, 'wait'); });

        var query = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');

        fetch('?' + query, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.ok ? r.text() : 'err'; })
            .catch(function () { return 'err'; })
            .then(function (rep) {
                if (n !== seq[key]) return;
                inputs.forEach(function (i) { setState(i, String(rep).trim() === 'ok' ? 'ok' : 'err'); });
            });
    }

    function check(name) {
        var input = el(name);
        if (!input) return;

        switch (name) {
            case 'ADMIN_LOGIN':
            case 'ADMIN_PASSWORD':
            case 'SECRETKEY':
                setState(input, input.value.trim() === '' ? 'err' : 'ok');
                break;

            case 'DATAPATH':
            case 'DB_DATABASE':
                ask(name, { ajax: name, value: input.value }, [input]);
                break;

            case 'DB_SERVER':
                ask(name, { ajax: name, value: input.value }, [input]);
                if (el('DB_LOGIN').value !== '') check('DB_LOGIN');
                break;

            case 'DB_LOGIN':
            case 'DB_PASSWORD':
                ask('DB_LOGIN', {
                    ajax: 'DB_LOGIN',
                    server: el('DB_SERVER').value,
                    login: el('DB_LOGIN').value,
                    password: el('DB_PASSWORD').value
                }, [el('DB_LOGIN'), el('DB_PASSWORD')]);
                break;
        }
    }

    // Au chargement : les champs déjà renseignés (un champ vide garde l'état posé par le serveur)
    ['ADMIN_LOGIN', 'ADMIN_PASSWORD', 'SECRETKEY', 'DATAPATH', 'DB_DATABASE', 'DB_SERVER'].forEach(function (name) {
        var input = el(name);
        if (input && input.value.trim() !== '') check(name);
    });

    form.addEventListener('change', function (e) {
        if (e.target && e.target.name) check(e.target.name);
    });

    // Afficher ou masquer un mot de passe
    form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-p4-reveal]');
        if (!btn) return;
        var input = el(btn.getAttribute('data-p4-reveal'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        var label = show ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
        btn.setAttribute('aria-label', label);
        btn.title = label;
    });

    // Thème, mémorisé pour la page de connexion de ce navigateur (cookie lu par ploopi\skin)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-p4-theme]');
        if (!btn) return;
        var theme = btn.getAttribute('data-p4-theme');
        if (theme !== 'light' && theme !== 'dark') theme = 'auto';
        root.setAttribute('data-theme', theme);
        Array.prototype.forEach.call(document.querySelectorAll('[data-p4-theme]'), function (b) {
            b.setAttribute('aria-pressed', b.getAttribute('data-p4-theme') === theme ? 'true' : 'false');
        });
        document.cookie = 'ploopi_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
    });

    // L'import de la base prend quelques secondes : le bouton l'indique
    form.addEventListener('submit', function () {
        var btn = form.querySelector('.p4-install-submit');
        if (!btn) return;
        btn.classList.add('p4-install-busy');
        btn.setAttribute('aria-busy', 'true');
        window.setTimeout(function () { btn.disabled = true; }, 0);
    });
})();
