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

ploopi_skin_array_renderupdate_done = new Array();

/**
 * Met à jour l'affichage des tableaux générés par la classe skin.
 * Il faut corriger certains problèmes liés à l'affichage ou non d'une barre de défilement vertical.
 * Il faut également corriger les lacunes de IE.
 */
 
function ploopi_skin_array_renderupdate(array_id)
{
    greater = $('ploopi_explorer_values_inner_'+array_id).offsetHeight > $('ploopi_explorer_values_outer_'+array_id).offsetHeight;
    
    if (greater)
    {
        if (typeof(ploopi_skin_array_renderupdate_done[array_id]) == 'undefined')
        {
            $('ploopi_explorer_title_'+array_id).innerHTML = '<div style=\'float:right;width:16px;\'>&nbsp;</div>'+$('ploopi_explorer_title_'+array_id).innerHTML;
    
            columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    diff = (Prototype.Browser.IE) ? 22 : 16;
                    columns[j].style.right = (parseInt(columns[j].style.right)+diff)+'px';
                }
            }
            
            ploopi_skin_array_renderupdate_done[array_id] = true;
        }
    }

    if (Prototype.Browser.IE)
    {
        columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
        for (j=0;j<columns.length;j++)
        {
            columns[j].style.height = $('ploopi_explorer_main_'+array_id).offsetHeight+'px'
        }
    }
}

function ploopi_skin_treeview_shownode(node_id, query, script)
{
    if (typeof(script) == 'undefined') script = 'admin-light.php';
    
    elt = $('t'+node_id);
    dest = $('n'+node_id);
    
    if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
    else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');

    if ($(dest))
    {
        if ($(dest).style.display == 'none')
        {
            $(dest).style.display='block';
            if ($(dest).innerHTML.length < 20)
            {
                ploopi_ajaxloader(dest);
                ploopi_xmlhttprequest_todiv(script, query, dest);
            }
        }
        else $(dest).style.display='none';
    }
}


function ploopi_skin_array_refresh(array_id, array_orderby)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_skin_array_refresh&array_id='+array_id+'&array_orderby='+array_orderby,'ploopi_explorer_main_'+array_id);
}
