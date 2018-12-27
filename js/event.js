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

ploopi.event = {};

ploopi.event.dispatch_onchange = function(inputfield_id) {
    jQuery('#'+inputfield_id).trigger('change');
};

ploopi.event.dispatch_onclick = function(inputfield_id) {
    jQuery('#'+inputfield_id).trigger('click');
};


/*
var ploopi_window_onload_functions = new Array();
var ploopi_window_onunload_functions = new Array();

function ploopi_window_onload_stock(func)
{
    ploopi_window_onload_functions[ploopi_window_onload_functions.length] = func;
}

function ploopi_window_onload_launch()
{
    window.onload = function()
    {
        for (var i = 0; i < ploopi_window_onload_functions.length; i++) ploopi_window_onload_functions[i]();
    }
}

function ploopi_window_onunload_stock(func)
{
    ploopi_window_onunload_functions[ploopi_window_onunload_functions.length] = func;
}

function ploopi_window_onunload_launch()
{
    window.onunload = function()
    {
        for (var i = 0; i < ploopi_window_onunload_functions.length; i++) ploopi_window_onunload_functions[i]();
    }
}

function ploopi.event.dispatch_onchange(inputfield_id)
{
    if (Prototype.Browser.IE)
    {
        $(inputfield_id).fireEvent('onChange');
    }
    else
    {
        var e = document.createEvent('HTMLEvents');
        e.initEvent('change', false, false);
        $(inputfield_id).dispatchEvent(e);
    }
}

function ploopi.event.dispatch_onclick(inputfield_id)
{
    if (Prototype.Browser.IE)
    {
        $(inputfield_id).fireEvent('onclick');
    }
    else
    {
        var e = document.createEvent('MouseEvents');
        e.initEvent('click', false, false);
        $(inputfield_id).dispatchEvent(e);
    }
}


ploopi_window_onload_launch();
ploopi_window_onunload_launch();
*/
