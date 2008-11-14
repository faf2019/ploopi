/*
    Copyright (c) 2008 Ovensia
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

/* FILE EXPLORER FUNCTIONS */

function ploopi_filexplorer_popup(filexplorer_id, event)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 600, event, true, 'ploopi_filexplorer_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=filexplorer_browser&filexplorer_id='+filexplorer_id, 'ploopi_filexplorer_popup')
}


function ploopi_filexplorer_browser(filexplorer_id, folder)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=filexplorer_browser&filexplorer_id='+filexplorer_id+'&filexplorer_folder='+folder, 'ploopi_filexplorer_popup')
}