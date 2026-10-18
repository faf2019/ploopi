/*
 * Ploopi — gabarit frontoffice « corporate »
 * Comportements du gabarit, sans gestionnaire en ligne : en-tête au défilement,
 * menu mobile, sous-menus, champ conditionnel de la recherche avancée.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var header = document.querySelector('[data-c-header]');
        if (header) {
            var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        // Menu mobile
        var burger = document.querySelector('[data-c-burger]');
        var nav = burger ? document.getElementById(burger.getAttribute('aria-controls')) : null;
        if (burger && nav) {
            burger.addEventListener('click', function () {
                var open = burger.getAttribute('aria-expanded') !== 'true';
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                nav.classList.toggle('is-open', open);
                document.body.classList.toggle('c-nav-open', open);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && nav.classList.contains('is-open')) burger.click();
            });
        }

        // Sous-menus (mobile)
        Array.prototype.forEach.call(document.querySelectorAll('[data-c-submenu]'), function (btn) {
            btn.addEventListener('click', function () {
                var sub = btn.nextElementSibling;
                var open = btn.getAttribute('aria-expanded') !== 'true';
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (sub) sub.classList.toggle('is-open', open);
            });
        });

        // Champ affiché selon la valeur d'une liste : data-c-toggle="<id>" data-c-toggle-hide="<valeur>"
        Array.prototype.forEach.call(document.querySelectorAll('[data-c-toggle]'), function (sel) {
            var target = document.getElementById(sel.getAttribute('data-c-toggle'));
            if (!target) return;
            var apply = function () { target.hidden = (sel.value === sel.getAttribute('data-c-toggle-hide')); };
            sel.addEventListener('change', apply);
            apply();
        });
    });
})();
