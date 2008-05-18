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

/**
 * Affichage du bloc de menu
 * 
 * @package forms
 * @subpackage block
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('forms', false, false, false);

$date_today = ploopi_createtimestamp();

$forms_select =     "
                    SELECT      *
                    FROM        ploopi_mod_forms_form
                    WHERE       id_module = {$menu_moduleid}
                    AND         id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).")
                    AND         (pubdate_start <= '{$date_today}' OR pubdate_start = '')
                    AND         (pubdate_end >= '{$date_today}' OR pubdate_end = '')
                    ";


$forms_result = $db->query($forms_select);

while ($forms_fields = $db->fetchrow($forms_result))
{
    $block->addmenu($forms_fields['label'], ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&op=forms_viewreplies&forms_id={$forms_fields['id']}"));
}

if (ploopi_isactionallowed(_FORMS_ACTION_ADMIN, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>'._FORMS_ADMIN.'</b>', ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}
?>

