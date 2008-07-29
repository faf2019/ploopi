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

function ploopi_openwin(url,w,h,name)
{
   var top = (screen.height-(h+60))/2;
   var left = (screen.width-w)/2;

   if(!name) name = 'ploopiwin';

   var ploopiwin = window.open(url,name,'top='+top+',left='+left+',width='+w+', height='+h+', status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20');

   ploopiwin.focus();

   return(ploopiwin);
}

function ploopi_confirmform(form, message)
{
    if (confirm(message)) form.submit();
}

function ploopi_confirmlink(link, message)
{
    if (confirm(message)) location.href = link;
}

function ploopi_switchdisplay(id)
{
    e = $(id);
    if (e) e.style.display = (e.style.display == 'none') ? 'block' : 'none';
}

// clic sur une zone checkbox/radio
// génère un event équivalent au clic direct sur l'élément

function ploopi_checkbox_click(e, inputfield_id)
{
    src = (e.srcElement) ? e.srcElement : e.target;
    
    if (typeof(src.id) == 'undefined' || src.id != inputfield_id)
    {
        if (Prototype.Browser.IE)
        {
            switch ($(inputfield_id).type)
            {
                case 'radio':
                    $(inputfield_id).checked = true; 
                break;
                
                default:
                    $(inputfield_id).checked = !$(inputfield_id).checked; 
                break;
            }      
            
            $(inputfield_id).fireEvent('onchange');
        }
        else
        {
            
            var e = document.createEvent('MouseEvents');
            e.initEvent('click', false, false);
            $(inputfield_id).dispatchEvent(e);
        }
    }
}


function ploopi_checkall(form, mask, value, byid)
{
    var len = form.elements.length;
    var reg = new RegExp(mask,"g");

    if (!byid) byid = false;

    for (var i = 0; i < len; i++)
    {
        var e = form.elements[i];

        if (byid)
        {
            if (e.id.match(reg)) e.checked = value;
        }
        else
        {
            if (e.name.match(reg)) e.checked = value;
        }
    }
}

function ploopi_getelem(elem, obj)
{
    if (typeof(obj) != 'object') obj = document;
    
    return (obj.getElementById) ? obj.getElementById(elem) : eval("document.all['"+ploopi_addslashes(elem)+"']");
}


function ploopi_innerHTML(div, html)
{
    if ($(div))
    {
        $(div).innerHTML = html;
        $(div).innerHTML.evalScripts();
    }
}