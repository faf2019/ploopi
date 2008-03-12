<?php
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

function ploopi_isadmin($workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN);
}

function ploopi_ismanager($workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER);
}

function ploopi_isactionallowed($actionid = -1, $workspaceid = -1, $moduleid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    $booAllowed = false;
    
    if (ploopi_isadmin($workspaceid)) $booAllowed = true;
    else
    {
        if (is_array($actionid))
        {
            foreach($actionid as $aid)
            {
                $booAllowed = $booAllowed || isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$aid]);
            }
        }
        else
        {
            if ($actionid == -1) $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid]);
            else $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$actionid]);
        }
    }
    
    return($booAllowed);
}

function ploopi_ismoduleallowed($moduletype, $moduleid = -1, $workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    // module existe && module du type indiqué && module affecté à l'espace courant
    return(     !empty($_SESSION['ploopi']['modules'][$moduleid])
            &&  $_SESSION['ploopi']['modules'][$moduleid]['moduletype'] == $moduletype
            &&  in_array($moduleid ,$_SESSION['ploopi']['workspaces'][$workspaceid]['modules'])
        );
}
?>
