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

ploopi_init_module('directory', false, false, false);

if ($_SESSION["ploopi"]["workspaceid"] > 0)  // group selected
{
    if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_mygroup'])
    //if (ploopi_isactionallowed(_DIRECTORY_ACTION_MYGROUP,-1,$menu_moduleid))
    {
        $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : $_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mygroup'],ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabMygroup"));
    }
}

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_mycontacts'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mycontacts'],ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabMycontacts"));
}

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_myfavorites'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_myfavorites'])) ? _DIRECTORY_FAVORITES : $_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_myfavorites'],ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabFavorites"));
}

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_search'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_search'])) ? _DIRECTORY_SEARCH : $_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_search'],ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabSearch"));
}
?>
