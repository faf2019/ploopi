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

ploopi.xhr = {};

ploopi.xhr.ajaxloader_content = '<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.png"></div>';

ploopi.xhr.ajaxloader = function(id) {
    if (jQuery('#'+id).length) {
        jQuery('#'+id).html(ploopi.xhr.ajaxloader_content);
    }
    else return ploopi.xhr.ajaxloader_content;
}

ploopi.xhr.send = function(url, data, asynchronous, getxml, method) {

    if (typeof(asynchronous) == 'undefined') asynchronous = false;
    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var result = '';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        async: asynchronous,
        dataType: getxml ? 'xml' : 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(txt) {
        result = txt;
    });

    if (!asynchronous) return result;
};


/**
 * Affiche le contenu contenu d'une requête HTTP dans un élément de la page
 *
 * @param string url nom du script à appeler
 * @param string data paramètres complémentaires
 * @param string div identifiant de l'élément
 * @param string method méthode http à utiliser (GET/POST)
 */

ploopi.xhr.todiv = function(url, data, id, method) {
    if (typeof(method) == 'undefined') method = 'GET';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(html) {
        jQuery('#'+id).html(html);
    });
};

ploopi.xhr.tocb = function(url, data, callback, ticket, getxml, method) {

    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var request = jQuery.ajax({
        type: method,
        url: url,
        data: data,
        dataType: getxml ? 'xml' : 'html',
        contentType: 'application/x-www-form-urlencoded'
    });

    request.done(function(txt) {
        callback(txt, ticket);
    });

};

/**
 * Permet d'ouvrir un popup avec le contenu d'une requête HTTP
 *
 * @param int width largeur du popup
 * @param event e événement déclencheur
 * @param string id identifiant du popup
 * @param string url nom du script à appeler
 * @param string data paramètres complémentaires
 * @param string method méthode http à utiliser (GET/POST)
 * @param boolean true|false active la capture de la touche escape pour fermeture de la popup
 */

ploopi.xhr.topopup = function(width, e, id, url, data, method) {
    if (typeof(method) == 'undefined') method = 'GET';

    ploopi.popup.show(ploopi.xhr.ajaxloader_content, width, e, 'click', id, null, null);
    ploopi.xhr.todiv(url, data, id, method);
};


/**
 * Permet de valider automatiquement un formulaire via xmlhttprequest
 *
 * @param object form formulaire
 * @param string id identifiant du popup
 * @param function beforesubmit fonction appelée avant validation (doit retourner true/false)
 *
 * @todo possibilité de ne pas renvoyer la réponse vers du contenu
 */

ploopi.xhr.submit = function(form, id, beforesubmit)
{
    var submit = true;
    if (typeof(beforesubmit) == 'function') submit = beforesubmit(form);

    query = jQuery(form).serialize();
    query += (query == '' ? '' : '&')+'ploopi_xhr=1';
    if (submit) ploopi.xhr.todiv(form.action, query, id, 'POST');
}
