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

var ploopi_ajaxloader_content = '<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.gif"></div>';

function ploopi_ajaxloader(div)
{
    if (div && $(div)) $(div).innerHTML = ploopi_ajaxloader_content;
    else return ajaxloader;
}

function ploopi_gethttpobject(callback)
{
    var xmlhttp = false;

    /*@cc_on
    @if (@_jscript_version >= 5)
    try
    {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
        try
        {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (E)
        {
            xmlhttp = false;
        }
    }
    @else
    xmlhttp = false;
    @end @*/

    /* on essaie de créer l'objet si ce n'est pas déjà fait */
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
    {
        try
        {
            xmlhttp = new XMLHttpRequest();
        }
        catch (e)
        {
            xmlhttp = false;
        }
    }

    return xmlhttp;
}

function ploopi_sendxmldata(method, url, data, xmlhttp, asynchronous)
{
    if (!xmlhttp)
    {
        return false;
    }

    if(method == "GET")
    {
        if(data == 'null')
        {
            xmlhttp.open("GET", url, asynchronous);
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
        }
        else
        {
            xmlhttp.open("GET", url+"?"+data, asynchronous);
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
        }
        xmlhttp.send(null);
    }
    else if(method == "POST")
    {
        xmlhttp.open("POST", url, asynchronous);
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-1');
        xmlhttp.send(data);
    }
    return true;
}

function ploopi_xmlhttprequest(url, data, asynchronous, getxml, method)
{
    if (typeof(asynchronous) == 'undefined') asynchronous = false;
    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var xmlhttp = ploopi_gethttpobject();
    ploopi_sendxmldata(method, url, data, xmlhttp, asynchronous);

    // if asynchronous = false => return request content
    if (!asynchronous)
    {
        if (getxml) return(xmlhttp.responseXML);
        else return(xmlhttp.responseText);
    }
}


function ploopi_xmlhttprequest_tofunction(url, data, callback, ticket, getxml, method)
{
    var xmlhttp = ploopi_gethttpobject();

    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    if (xmlhttp)
    {
        /* on définit ce qui doit se passer quand la page répondra */
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState == 4)
            {
                if (xmlhttp.status == 200)
                {
                    if (getxml) callback(xmlhttp.responseXML,ticket);
                    else callback(xmlhttp.responseText,ticket);
                }
            }
        }
    }

    return !ploopi_sendxmldata(method, url, data, xmlhttp, true);
}

/**
 * Affiche le contenu contenu d'une requête HTTP dans un élément de la page
 *
 * @param string url nom du script à appeler 
 * @param string data paramètres complémentaires 
 * @param string div identifiant de l'élément 
 * @param string method méthode http à utiliser (GET/POST) 
 */
 
 
function ploopi_xmlhttprequest_todiv(url, parameters, id, method)
{
    if (typeof(method) == 'undefined') method = 'GET';

	new Ajax.Request(url, {
        method:     method,
        parameters: parameters,
	    encoding:   'iso-8859-15',
	    onSuccess:  function(transport) {
	        ploopi_innerHTML(id, transport.responseText);
        }
	});
}

/**
 * Permet d'ouvrir un popup avec le contenu d'une requête HTTP
 *
 * @param int width largeur du popup 
 * @param event e événement déclencheur 
 * @param string id identifiant du popup 
 * @param string url nom du script à appeler 
 * @param string data paramètres complémentaires 
 * @param string method méthode http à utiliser (GET/POST) 
 */
 
function ploopi_xmlhttprequest_topopup(width, e, id, url, data, method)
{
    if (typeof(method) == 'undefined') method = 'GET';

    ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', id);
    ploopi_xmlhttprequest_todiv(url, data, id, method);
}

/**
 * Permet de valider automatiquement un formulaire via xmlhttprequest
 *
 * @param object form formulaire 
 * @param string id identifiant du popup 
 * @param function beforesubmit fonction appelée avant validation (doit retourner true/false) 
 *
 * @todo possibilité de ne pas renvoyer la réponse vers du contenu
 */
 
function ploopi_xmlhttprequest_submitform(form, id, beforesubmit)
{
    var submit = true;
    if (typeof(beforesubmit) == 'function') submit = beforesubmit(form);
    
    query = form.serialize();
    query += (query == '' ? '' : '&')+'ploopi_xhr=1';
    if (submit) ploopi_xmlhttprequest_todiv(form.action, query, id, 'POST');
}