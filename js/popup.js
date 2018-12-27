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

jQuery(function() {
    ploopi.popup.hooks = jQuery('.hook');
    if (!ploopi.popup.hooks.length) ploopi.popup.hooks = jQuery('body');
});

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

    ploopi_popup = $('#'+id).eq(0);

    w = parseInt(w);
    if (!w) w = 200;

    var posx = 0;
    var posy = 0;

    pposx = parseInt(pposx);
    pposy = parseInt(pposy);

    if (pposx) posx = pposx;
    if (pposy) posy = pposy;

    if(e) // event ok
    {
        wd = jQuery(window);
        ph = jQuery(ploopi.popup.hooks[0]);

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
                wd = jQuery(window);
                ph = jQuery(ploopi.popup.hooks[0]);

                if (!posx) posx = wd.width()/2 - w/2 + ph.scrollLeft() + wd.scrollLeft();
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

    if (e && ((20 + w + parseInt(tmpleft)) > parseInt(jQuery(window).width())))
    {
        tmpleft = parseInt(tmpleft) - w - 40;
    }

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

    ploopi_popup = $('#'+id).eq(0);

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








function ploopi_popupize(id, w, centered, pposx, pposy)
{
    var ploopi_popup;

    if ($(id))
    {
        ploopi_popup = $(id);
        ploopi_popup.setAttribute('class', 'ploopi_popup');
        ploopi_popup.setAttribute('className', 'ploopi_popup'); // IE
        ploopi_popup.setAttribute('style', 'display:block;z-index:'+(10000+ploopi.popup.nb)+';');

        w = parseInt(w);
        if (!w) w = 200;

        switch(centered)
        {
            case false:
                posx = parseInt(pposx);
                posy = parseInt(pposy);
            break;

            default:
            case true:
               var coordScroll = document.viewport.getScrollOffsets();
               posx = parseInt(document.viewport.getWidth()/2)-parseInt(w/2)+coordScroll.left;
               posy = parseInt(coordScroll.top)+20;
            break;
        }


        with(ploopi_popup.style)
        {
            left = posx+'px';
            top = posy+'px';
            width = w+'px';
        }

        ploopi.popup.hooks[0].appendChild(ploopi_popup);
    }
}
