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
