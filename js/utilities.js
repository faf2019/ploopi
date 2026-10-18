/*
    Copyright (c) 2002-2007 Netlor
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


ploopi.openwin = function(url,w,h,name) {
   var top = (screen.height-(h+60))/2;
   var left = (screen.width-w)/2;

   if(!name) name = 'ploopiwin';

   var ploopiwin = window.open(url,name,'top='+top+',left='+left+',width='+w+', height='+h+', status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20');

   ploopiwin.focus();

   return(ploopiwin);
};

ploopi.confirmform = function(form, message) {
    if (confirm(message)) form.submit();
};

ploopi.confirmlink = function(link, message) {
    if (confirm(message)) location.href = link;
};

ploopi.switchdisplay = function(id) {
    e = jQuery('#'+id);
    if (e) e.css('display', e.css('display') == 'none' ? 'block' : 'none');
};


ploopi.getelem = function(elem, obj) {
    if (typeof(obj) != 'object') obj = document;

    return (obj.getElementById) ? obj.getElementById(elem) : obj.all[elem];
};

ploopi.innerHTML = function(id, html) {
    if (jQuery('#'+id).length) jQuery('#'+id).html(html);
};


// clic sur une zone checkbox/radio
// génère un event équivalent au clic direct sur l'élément
ploopi.checkbox_click = function(e, inputfield_id) {

    var element = jQuery('#'+inputfield_id)[0];

    if (!element || e.target == element) return;

    // Clic sur un libellé lié à la case : le navigateur la bascule déjà (sinon, double bascule)
    var label = jQuery(e.target).closest('label')[0];
    if (label && (label.control == element || jQuery(label).find(element).length)) return;

    switch(element.type) {
        case 'radio':
            element.checked = true;
        break;

        default:
            element.checked = !element.checked;
        break;

    }

    ploopi.event.dispatch_onchange(inputfield_id);
};



ploopi.execute_function = function(functionName, context) {
  var args = Array.prototype.slice.call(arguments, 2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
  for(var i = 0; i < namespaces.length; i++) {
    context = context[namespaces[i]];
  }
  return context[func].apply(context, args);
};



ploopi.get_param = function(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
};

/**
 * Insertion d'un texte dans un champ à la position du curseur
 */
ploopi.insertatcursor = function(field, value) {
    //IE support
    if (document.selection)
    {
        field.focus();
        sel = document.selection.createRange();
        sel.text = value;
    }
    //MOZILLA/NETSCAPE support
    else if (field.selectionStart || field.selectionStart == '0')
    {
        var startPos = field.selectionStart;
        var endPos = field.selectionEnd;
        field.value = field.value.substring(0, startPos) + value + field.value.substring(endPos, field.value.length);
    }
    else
    {
       field.value += value;
    }
};

ploopi_insertatcursor = function(field, value) {
    ploopi.insertatcursor(field, value);
};

// Lot P2 — les icônes de la barre d'outils ne portent plus de onclick inline :
// create_icon() écrit data-url / data-confirm, cet écouteur délégué rejoue le
// comportement historique (confirmation éventuelle puis navigation).
jQuery(document).ready(function() {
    jQuery(document).on('click', 'a.skin_icon', function(event) {
        event.preventDefault();
        var icon = jQuery(this);
        var go = function() { document.location.href = icon.attr('data-url'); };
        var confirmMessage = icon.attr('data-confirm');
        if (confirmMessage) {
            if (confirm(confirmMessage)) go();
        }
        else go();
    });
});

// Lot P2 — fermeture des popups du socle (open_simplebloc / open_popup) :
// le bouton porte data-popup, plus aucun onclick inline.
jQuery(document).on('click', '.ploopi_popup_close', function(event) {
    event.preventDefault();
    ploopi.popup.hide(jQuery(this).attr('data-popup'));
});

// Lot P2 — tri des colonnes et pagination des tableaux avancés :
// le socle écrit data-array / data-orderby / data-callback (tri) et
// data-array / data-page / data-callback (pagination), l'écouteur rejoue
// ploopi.skin.array_refresh.
jQuery(document).on('click', 'a.ploopi_array_sort', function(event) {
    event.preventDefault();
    var link = jQuery(this);
    ploopi.skin.array_refresh(link.attr('data-array'), link.attr('data-orderby'), '', link.attr('data-callback'));
});

jQuery(document).on('click', 'a.ploopi_array_page', function(event) {
    event.preventDefault();
    var link = jQuery(this);
    ploopi.skin.array_refresh(link.attr('data-array'), '', link.attr('data-page'), link.attr('data-callback'));
});

// Lot P2 — zones cliquables des checkbox : data-checkbox au lieu d'onclick
// inline ; l'écouteur rejoue ploopi.checkbox_click (bascule + onchange).
jQuery(document).on('click', '.ploopi_checkbox_zone', function(event) {
    ploopi.checkbox_click(event, jQuery(this).attr('data-checkbox'));
});
