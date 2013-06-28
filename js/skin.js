/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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
        // N'existe pas ?
        if (!$('ploopi_explorer_spacer_'+array_id)) {
            // Récupération de la largeur de la scrollbar verticale
            scrollbar_width = $('ploopi_explorer_values_outer_'+array_id).offsetWidth - $('ploopi_explorer_values_inner_'+array_id).offsetWidth;

            // Insertion d'un bloc de la largeur de la scrollbar dans la ligne de titre
            $('ploopi_explorer_title_'+array_id).innerHTML = '<div id="ploopi_explorer_spacer_'+array_id+'" style=\'float:right;width:'+scrollbar_width+'px;\'>&nbsp;</div>'+$('ploopi_explorer_title_'+array_id).innerHTML;

            columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right)+scrollbar_width)+'px';
                }
            }
        }
    }
    else {
        if ($('ploopi_explorer_spacer_'+array_id)) {
            scrollbar_width = $('ploopi_explorer_spacer_'+array_id).getWidth();
            $('ploopi_explorer_spacer_'+array_id).remove();

            columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right)-scrollbar_width)+'px';
                }
            }

        }
    }

    if (Prototype.Browser.IE)
    {
        columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
        for (j=0;j<columns.length;j++)
        {
            columns[j].style.height = $('ploopi_explorer_main_'+array_id).offsetHeight+'px';
        }
    }
}

function ploopi_skin_treeview_shownode(node_id, query, script)
{

    if (typeof(script) == 'undefined') script = 'admin-light.php';

    elt = $('t'+node_id);
    dest = $('n'+node_id);
    treenode = $('treeview_node'+node_id);

    treenode.className = 'treeview_node_loading';

    if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
    else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');

    if ($(dest))
    {
        if ($(dest).style.display == 'none')
        {
            //$(dest).style.display='block';
            if ($(dest).innerHTML.length < 20)
            {
                $(dest).innerHTML = ploopi_xmlhttprequest(script, query);
            }
            new Effect.BlindDown(
                dest,
                {
                    duration: 0.2,
                    afterFinish: function() {treenode.className = 'treeview_node';}
                }
            );
        }
        else
        {
            new Effect.BlindUp(
                dest,
                {
                    duration: 0.2,
                    afterFinish: function() {treenode.className = 'treeview_node';}
                }
            );
        }
    }
}

function ploopi_skin_array_refresh(array_id, array_orderby, array_page)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_skin_array_refresh&array_id='+array_id+'&array_orderby='+array_orderby+'&array_page='+array_page+'&ploopi_randomize='+Math.random(),'ploopi_explorer_main_'+array_id);
}
