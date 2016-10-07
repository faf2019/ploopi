<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Includes
 */
include_once './modules/forms/classes/formsForm.php';

/**
 * Initialisation du module
 */
ploopi\module::init('forms', false, false, false);

$forms_id = isset($_GET['forms_id']) ? $_GET['forms_id'] : '';

$intTsToday = ploopi\date::createtimestamp();

$objDOC = new ploopi\data_object_collection('formsForm');
$objDOC->add_where("id_module = %d", $menu_moduleid);
$objDOC->add_where("(pubdate_start <= %s OR pubdate_start = '')", $intTsToday);
$objDOC->add_where("(pubdate_end >= %s OR pubdate_end = '')", $intTsToday);
$objDOC->add_where("id_workspace IN (%e)", array(explode(',', ploopi\system::viewworkspaces($menu_moduleid))));
$objDOC->add_orderby('label');

foreach($objDOC->get_objects() as $objForm)
{
    if (!$objForm->fields['option_adminonly'] || ploopi\acl::isactionallowed(_FORMS_ACTION_ADMIN, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
        $block->addmenu(ploopi\str::htmlentities($objForm->fields['label']), ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&op=forms_viewreplies&forms_id={$objForm->fields['id']}"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $forms_id == $objForm->fields['id']);
}

$block->addmenu(_FORMS_LIST, ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && empty($forms_id));

if (ploopi\acl::isactionallowed(_FORMS_ACTION_ADMIN, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>'._FORMS_ADMIN.'</b>', ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin');
}
?>
