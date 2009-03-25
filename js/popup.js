/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

var ploopi_nbpopup = 0;

function ploopi_showpopup(popup_content, w, e, centered, id, pposx, pposy)
{
    var ploopi_popup;
    var active_effect = false;

    if (!id) id = 'ploopi_popup';

    if (!$(id))
    {
        bodys = document.getElementsByTagName('body');

        ploopi_nbpopup++;
        ploopi_popup = document.createElement('div');
        ploopi_popup.setAttribute('class', 'ploopi_popup');
        ploopi_popup.setAttribute('className', 'ploopi_popup'); // IE
        ploopi_popup.setAttribute('id', id);
        ploopi_popup.setAttribute('style', 'z-index:'+(1000+ploopi_nbpopup)+';');
        ploopi_popup.style.display = 'none';

        bodys[0].appendChild(ploopi_popup);

        active_effect = true;
    }
    else ploopi_popup = $(id);

    w = parseInt(w);
    if (!w) w = 200;

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
    else
    {
        switch(centered)
        {
           case false:
           break;

           default:
           case true:
                var p_width = parseInt(document.body.offsetWidth);
                var p_left = parseInt(document.body.scrollLeft);
                var posx = (p_width/2)-(w/2)+p_left;
            break;
        }
    }

    with (ploopi_popup.style)
    {
        if (typeof(popup_content) != 'undefined') ploopi_innerHTML(id, popup_content);

        tmpleft = parseInt(posx) + 20;
        tmptop = parseInt(posy);

        if (w > 0) width = w+'px';
        else w = parseInt(ploopi_popup.offsetWidth);

        if (20 + w + parseInt(tmpleft) > parseInt(document.body.offsetWidth))
        {
            tmpleft = parseInt(tmpleft) - w - 40;
        }

        left = tmpleft+'px';
        top = tmptop+'px';
    }

    if (active_effect) new Effect.Appear(id, { duration: 0.4, from: 0.0, to: 1 });
}

function ploopi_movepopup(id, e, pposx, pposy, popup_content)
{
    var ploopi_popup;

    if (!id) id = 'ploopi_popup';

    ploopi_popup = $(id);

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

    with (ploopi_popup.style)
    {
        ploopi_innerHTML(id, popup_content);

        tmpleft = parseInt(posx) + 20;
        tmptop = parseInt(posy);

        w = parseInt(ploopi_popup.offsetWidth);

        if (20 + w + parseInt(tmpleft) > parseInt(document.body.offsetWidth))
        {
            tmpleft = parseInt(tmpleft) - w - 40;
        }

        left = tmpleft+'px';
        top = tmptop+'px';
    }

}

function ploopi_hidepopup(id)
{
    if (!id) id = 'ploopi_popup';

    if ($(id))
    {
        new Effect.Fade(id,
                {
                    duration: 0.3,
                    afterFinish:function()
                    {
                        var bodys = document.getElementsByTagName('body');
                        bodys[0].removeChild($(id));
                    }
                }
             );
    }
}

function ploopi_hideallpopups()
{
    var popups = document.getElementsByClassName('ploopi_popup');
    var bodys = document.getElementsByTagName('body');
    var l = popups.length;
    for (var i = 0; i < l; i++) bodys[0].removeChild(popups[i]);
}
