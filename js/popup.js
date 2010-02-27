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
var ploopi_arrpopup = new Array();

function ploopi_showpopup(popup_content, w, e, centered, id, pposx, pposy, enable_esc)
{
    var ploopi_popup;
    var active_effect = false;

    if (!id) id = 'ploopi_popup';
    if(typeof(enable_esc) == 'undefined') enable_esc = false;

    if (!$(id)) // Nouvelle popup
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
        
        if (enable_esc) { ploopi_arrpopup.push(id); }
    }
    else // id existe
	{
    	ploopi_popup = $(id);
    	
    	if (enable_esc && ploopi_arrpopup[ploopi_arrpopup.length-1] != id)
    	{
	        // supprime le id popup déjà ouvert pour le basculer en fin de table de ploopi_arrpopup
	        var ploopi_arrpopup_tmp = new Array();
	        for (var i=0; i < ploopi_arrpopup.length; ++i) {
	        	if(ploopi_arrpopup[i] != id) ploopi_arrpopup_tmp.push(ploopi_arrpopup[i]);
	        }
	        ploopi_arrpopup = ploopi_arrpopup_tmp;
	        ploopi_arrpopup.push(id); // On remet a la fin
    	}
	}
    
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
        	var coordScroll = document.body.cumulativeScrollOffset();
            posx = e.clientX + coordScroll.left;
            posy = e.clientY + coordScroll.top;
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
        	   var coordScroll = document.body.cumulativeScrollOffset();
        	   var posx = parseInt(document.viewport.getWidth()/2)-parseInt(w/2)+coordScroll.left;
        	   var posy = parseInt(coordScroll.top)+20;
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

        if (20 + w + parseInt(tmpleft) > parseInt(document.viewport.getWidth()))
        {
            tmpleft = parseInt(tmpleft) - w - 40;
        }

        left = tmpleft+'px';
        top = tmptop+'px';
    }

    if (active_effect) new Effect.Appear(id, { duration: 0.4, from: 0.0, to: 1 });

    if (enable_esc)
	{
    	Event.stopObserving(document, 'keydown');
    	ploopi_popupEnableEscape();

	    // Tableau des popups ouvertes pour dépilage via esc 
	    // On surveille les clics sur les popup pour repasser la popup active à la fin de ploopi_arrpopup pour les escapes
	    Event.observe(id, 'click', function(event) {
	    	if(ploopi_arrpopup[ploopi_arrpopup.length-1] != id) // Dernier est déjà id, rien à faire.
	    	{
		        // supprime le id popup déjà ouvert pour le basculer en fin de table de ploopi_arrpopup
		        var ploopi_arrpopup_tmp = new Array();
		        for (var i=0; i < ploopi_arrpopup.length; ++i) {
		        	if(ploopi_arrpopup[i] != id) ploopi_arrpopup_tmp.push(ploopi_arrpopup[i]);
		        }
		        ploopi_arrpopup = ploopi_arrpopup_tmp;
		        // on le remet à la fin
			    ploopi_arrpopup.push(id);
	    	}
	    });
	}
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

                        if(ploopi_arrpopup.length > 0)
                        {
	                        // Enleve la popup de ploopi_arrpopup
	                        var ploopi_arrpopup_tmp = new Array();
	                        for (var i=0; i < ploopi_arrpopup.length; ++i) {
	                        	if(ploopi_arrpopup[i] != id) ploopi_arrpopup_tmp.push(ploopi_arrpopup[i]);
	                        }
	                        ploopi_arrpopup = ploopi_arrpopup_tmp;
                        
	                        // Si plus de popup on arrete les Event.observe
	                        Event.stopObserving(id, 'click');
	                        if(ploopi_arrpopup.length == 0) { Event.stopObserving(document, 'keydown'); }
                        }
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
    for (var i = 0; i < l; i++)
	{
    	bodys[0].removeChild(popups[i]);
        Event.stopObserving(popups[i], 'click');

	}
    // On vide le tableau des popup et on arrete le Event.observe
    if(ploopi_arrpopup.length > 0)
    {
	    ploopi_arrpopup = new Array();
	    Event.stopObserving(document, 'keydown');
    }
}

function ploopi_popupize(id, w, centered, pposx, pposy)
{
    if ($(id))
    {
        $(id).setAttribute('class', 'ploopi_popup');
        $(id).setAttribute('className', 'ploopi_popup'); // IE
		$(id).setAttribute('style', 'z-index:'+(1000+ploopi_nbpopup)+';');        
		
	    w = parseInt(w);
	    if (!w) w = 200;

        bodys = document.getElementsByTagName('body');

        switch(centered)
        {
            case false:
                pposx = parseInt(pposx);
                pposy = parseInt(pposy);
            break;

            default:
            case true:
                var p_width = parseInt(bodys[0].offsetWidth);
                var p_left = parseInt(bodys[0].scrollLeft);
                pposx = (p_width/2)-(w/2)+p_left;
            break;
        }		
		
		
		$(id).style.left = pposx+'px';
		$(id).style.top = pposy+'px';

        bodys[0].appendChild($(id));

		new Effect.Appear(id, { duration: 0.4, from: 0.0, to: 1 });
    }
}

function ploopi_popupEnableEscape()
{
	// on va fermer le dernier popup ouvert grâce à ploopi_arrpopup
	Event.observe(document, 'keydown', function(event) {
	    if (event.keyCode == Event.KEY_ESC) {
	    	ploopi_hidepopup(ploopi_arrpopup[ploopi_arrpopup.length-1]); 
    		ploopi_arrpopup.pop();
		}
	});
}