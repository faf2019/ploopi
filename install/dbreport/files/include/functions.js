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

function dbreport_functionlist_onchange(f)
{
    ploopi.insertatcursor(jQuery('#dbreport_queryfield_function')[0], f.value);
    f.selectedIndex = 0;
    jQuery('#dbreport_queryfield_function')[0].focus();
}

/**
 * Actions appelées par attributs data-ploopi-action (js/actions.js du socle), sans
 * gestionnaire en ligne (CSP stricte)
 */
ploopi.actions = ploopi.actions || {};

// Graphique d'une requête, dans une fenêtre à sa taille
ploopi.actions.dbreport_openwin = function(el, event, url, width, height) {
    event.preventDefault();
    ploopi.openwin(url, width, height);
};

// Fonction SQL choisie dans une liste : insérée dans le champ « Fonction appliquée »
ploopi.actions.dbreport_functionlist = function(el, event) {
    if (el.value != '') dbreport_functionlist_onchange(el);
};

// Type de jointure d'une relation : enregistré au changement
ploopi.actions.dbreport_typejoin = function(el, event, url) {
    document.location.href = url+'&dbreport_queryrelation_type_join='+encodeURIComponent(el.value);
};

// Champ affiché selon la valeur d'une liste : quand elle vaut « value », ou, sans
// « value », quand elle n'est pas vide ; vidé quand il est masqué
ploopi.actions.dbreport_toggle = function(el, event, field, value) {
    var show = (typeof value == 'undefined') ? el.value != '' : el.value == value;
    var block = document.getElementById(field+'_form'), input = document.getElementById(field);
    if (block) block.style.display = show ? 'block' : 'none';
    if (!show && input) input.value = '';
};
// Champs affichés (show = true) ou masqués (show = false) quand la liste prend l'une des
// valeurs données, dans l'état inverse sinon
ploopi.actions.dbreport_showif = function(el, event, fields, values, show) {
    var match = jQuery.inArray(el.value, values) >= 0;
    jQuery.each(fields, function(i, field) {
        var block = document.getElementById(field+'_form');
        if (block) block.style.display = (match == show) ? 'block' : 'none';
    });
};

/**
 * Aperçu SQL : copie de la requête dans le presse-papiers
 */
ploopi.actions.dbreport_copysql = function(el, event, id) {
    event.preventDefault();
    var pre = document.getElementById(id);
    if (!pre || !navigator.clipboard) return;
    navigator.clipboard.writeText(pre.textContent).then(function() {
        var label = el.textContent;
        el.textContent = 'Copiée ✓';
        setTimeout(function() { el.textContent = label; }, 1500);
    });
};
