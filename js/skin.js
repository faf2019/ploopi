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

ploopi.skin = {};


// ploopi_skin_array_refresh
ploopi.skin.array_refresh = function(array_id, array_orderby, array_page, callback) {

    jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data:         {
            ploopi_env: _PLOOPI_ENV,
            ploopi_op: 'ploopi_skin_array_refresh',
            array_id: array_id,
            array_orderby: array_orderby,
            array_page: array_page,
            ploopi_randomize: Math.random()
        },
        async: true,
        dataType: 'html',
        contentType: 'application/x-www-form-urlencoded'
    }).done(function(html) {
        jQuery('#ploopi_explorer_main_'+array_id).html(html);
        if (callback != '') ploopi.execute_function(callback, window, array_id, array_orderby, array_page);
    });
};


/**
 * Met à jour l'affichage des tableaux générés par la classe skin.
 * Il faut corriger certains problèmes liés à l'affichage ou non d'une barre de défilement vertical.
 * Il faut également corriger les lacunes de IE.
 */

ploopi.skin.array_renderupdate = function(array_id) {
    var greater = jQuery('#ploopi_explorer_values_inner_'+array_id)[0].offsetHeight > jQuery('#ploopi_explorer_values_outer_'+array_id)[0].offsetHeight;

    if (greater)
    {
        // N'existe pas ?
        if (!jQuery('#ploopi_explorer_spacer_'+array_id).length) {

            // Récupération de la largeur de la scrollbar verticale
            var scrollbar_width = $('#ploopi_explorer_values_outer_'+array_id)[0].offsetWidth - $('#ploopi_explorer_values_inner_'+array_id)[0].offsetWidth;

            // Insertion d'un bloc de la largeur de la scrollbar dans la ligne de titre
            $('#ploopi_explorer_title_'+array_id).html(
                '<div id="ploopi_explorer_spacer_'+array_id+'" style=\'float:right;width:'+scrollbar_width+'px;\'>&nbsp;</div>'+$('#ploopi_explorer_title_'+array_id).html()
            );

            var columns = $('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right, 10)+scrollbar_width)+'px';
                }
            }
        }
    }
    else {

        if (jQuery('#ploopi_explorer_spacer_'+array_id).length) {

            var scrollbar_width = $('#ploopi_explorer_spacer_'+array_id).width();

            $('#ploopi_explorer_spacer_'+array_id).remove();

            var columns = $('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');

            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    columns[j].style.right = (parseInt(columns[j].style.right, 10)-scrollbar_width)+'px';
                    console.log(columns[j].style.right);
                }
            }
        }
    }

    if (jQuery.browser == 'msie')
    {
        var columns = $('#ploopi_explorer_main_'+array_id+' .ploopi_explorer_column');
        for (j=0;j<columns.length;j++)
        {
            columns[j].style.height = $('#ploopi_explorer_main_'+array_id)[0].offsetHeight+'px';
        }
    }
};

ploopi.skin.treeview_shownode = function(node_id, query, script) {

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
                $(dest).innerHTML = ploopi.xhr.send(script, query);
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
};
