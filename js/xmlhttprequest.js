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

    /* on essaie de cr�er l'objet si ce n'est pas d�j� fait */
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
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
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
        /* on d�finit ce qui doit se passer quand la page r�pondra */
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


function ploopi_xmlhttprequest_todiv(url, data, sep, method)
{
    var xmlhttp = ploopi_gethttpobject();
    var args;

    if (typeof(method) == 'undefined') method = 'GET';

    if (xmlhttp)
    {
        args = ploopi_xmlhttprequest_todiv.arguments;

        /* on d�finit ce qui doit se passer quand la page r�pondra */
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState == 4)
            {
                if (xmlhttp.status == 200)
                {
                    var contents = new Array();
                    var result= xmlhttp.responseText;


                    if (sep == '') contents[0] = result;
                    else contents=result.split(sep);
                    for(i=0;i<args.length-3;i++)
                    {
                        if (contents[i]) ploopi_innerHTML(args[i+3], contents[i]);
                        else ploopi_innerHTML(args[i+3], '');
                    }
                }
            }
        }
    }
    
    return !ploopi_sendxmldata(method, url, data, xmlhttp, true);
}