<?php
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

/**
 * Opérations sur les types de ressources
 *
 * @package booking
 * @subpackage op
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Switch sur les différentes opérations possibles
 */

switch($_REQUEST['ploopi_op'])
{
    case 'booking_resourcetype_delete':
        ploopi_init_module('booking', false, false, false);

        if (!empty($_GET['booking_element_list']))
        {
            include_once './modules/booking/classes/class_booking_resourcetype.php';

            $element_array = preg_split('/,/', $_GET['booking_element_list']);
            foreach($element_array as $elementid)
            {
                $objResourceType = new booking_resourcetype();
                if ($objResourceType->open($elementid)) $objResourceType->delete();
            }
        }
        ploopi_redirect('admin.php');
    break;

    case 'booking_resourcetype_save':
        ploopi_init_module('booking', false, false, false);

        include_once './modules/booking/classes/class_booking_resourcetype.php';

        $objResourceType = new booking_resourcetype();

        if (!empty($_GET['booking_resourcetype_id']) && is_numeric($_GET['booking_resourcetype_id'])) $objResourceType->open($_GET['booking_resourcetype_id']);
        $objResourceType->setvalues($_POST, 'booking_resourcetype_');

        if (!isset($_POST['booking_resourcetype_active'])) $objResourceType->fields['active'] = 0;

        $objResourceType->save();

        ploopi_redirect("admin.php?booking_tab=resourcetype");
    break;

    case 'booking_resourcetype_add':
    case 'booking_resourcetype_open':
        ploopi_init_module('booking');
        ob_start();
        include_once './modules/booking/classes/class_booking_resourcetype.php';

        $objResourceType = new booking_resourcetype();

        switch($_REQUEST['ploopi_op'])
        {
            case 'booking_resourcetype_add':
                $objResourceType->init_description();
            break;

            case 'booking_resourcetype_open':
                if (!empty($_GET['booking_element_id']) && is_numeric($_GET['booking_element_id'])) $objResourceType->open($_GET['booking_element_id']);
                else $objResourceType->init_description();
            break;
        }
        ?>
        <form action="<? echo ploopi_urlencode("admin-light.php?ploopi_op=booking_resourcetype_save&booking_resourcetype_id={$objResourceType->fields['id']}"); ?>" method="post" onsubmit="javascript:return booking_resourcetype_validate(this);">
        <div class=ploopi_form>
            <p>
                <label>Intitulé:</label>
                <input name="booking_resourcetype_name" type="text" class="text" value="<? echo ploopi_htmlentities($objResourceType->fields['name']); ?>">
            </p>
            <p onclick="javascript:ploopi_checkbox_click(event,'booking_resourcetype_active');">
                <label for="booking_resourcetype_active">Actif:</label>
                <input name="booking_resourcetype_active" id="booking_resourcetype_active" type="checkbox" class="checkbox" value="1" <? if ($objResourceType->fields['active']) echo 'checked'; ?> tabindex="111" />
            </p>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser" />
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        include_once './modules/booking/include/global.php';

        $titre = ($_REQUEST['ploopi_op'] == 'booking_resourcetype_add') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'un type de ressource", $content, 'popup_resourcetype');
        ploopi_die();
    break;
}
?>
