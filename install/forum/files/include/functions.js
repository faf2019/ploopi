/*
  Copyright (c) 2007-2008 Ovensia
  Copyright (c) 2008 HeXad

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

function forum_array_renderupdate(array_id)
{
  greater = $('forum_values_inner_'+array_id).offsetHeight > $('forum_values_outer_'+array_id).offsetHeight;

  if (greater)
  {
    $('forum_title_'+array_id).innerHTML = '<div style=\'float:right;width:16px;\'>&nbsp;</div>'+$('forum_title_'+array_id).innerHTML;

    columns = $('forum_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
    for (j=0;j<columns.length;j++)
    {
      if (columns[j].style.right != '')
      {
        columns[j].style.right = (parseInt(columns[j].style.right)+16)+'px';
      }
    }
  }

  if (Prototype.Browser.IE)
  {
    columns = $('forum_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
    for (j=0;j<columns.length;j++)
    {
      columns[j].style.height = $('forum_main_'+array_id).offsetHeight+'px'
    }
  }
}



function forumContentGereCat()
{
  Sortable.create('forum_values_inner_categ',
    { tag: 'div',
      handle: 'ForumDragBox',
      onUpdate: function() {
        var objAjax = new Ajax.Request(
          'admin.php', 
          {
            method: 'get',
            parameters: 'op=ajax_save_posit_cat&'+Sortable.serialize('forum_values_inner_categ')+'&ploopi_env='+_PLOOPI_ENV
          } 
        )
      }
    }
  );
}