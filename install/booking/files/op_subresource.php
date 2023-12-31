<?php
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

/**
 * Opérations sur les sous-ressources
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
    case 'booking_subresource_delete':
        ploopi\module::init('booking', false, false, false);
        if (!empty($_GET['booking_element_list']))
        {
            include_once './modules/booking/classes/class_booking_subresource.php';

            $element_array = preg_split('/,/', $_GET['booking_element_list']);
            foreach($element_array as $elementid)
            {
                $objSubresource = new booking_subresource();
                if ($objSubresource->open($elementid)) $objSubresource->delete();
            }
        }
        ploopi\output::redirect('admin.php');
    break;

    case 'booking_subresource_save':
        ploopi\module::init('booking', false, false, false);
        include_once './modules/booking/classes/class_booking_subresource.php';

        $objSubresource = new booking_subresource();

        if (!empty($_GET['booking_subresource_id']) && is_numeric($_GET['booking_subresource_id'])) $objSubresource->open($_GET['booking_subresource_id']);
        $objSubresource->setvalues($_POST, 'booking_subresource_');

        if (!isset($_POST['booking_subresource_active'])) $objSubresource->fields['active'] = 0;

        $objSubresource->save();

        ploopi\output::redirect("admin.php?booking_tab=subresource");
    break;

    case 'booking_subresource_add':
    case 'booking_subresource_open':
        ob_start();
        ploopi\module::init('booking');

        include_once './modules/booking/classes/class_booking_subresource.php';

        $objSubresource = new booking_subresource();

        switch($_REQUEST['ploopi_op'])
        {
            case 'booking_subresource_add':
                $objSubresource->init_description();
            break;

            case 'booking_subresource_open':
                if (!empty($_GET['booking_element_id']) && is_numeric($_GET['booking_element_id'])) $objSubresource->open($_GET['booking_element_id']);
                else $objSubresource->init_description();
            break;
        }
        ?>
        <form action="<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=booking_subresource_save&booking_subresource_id={$objSubresource->fields['id']}"); ?>" method="post" onsubmit="javascript:return booking_subresource_validate(this);">
        <div class=ploopi_form>
            <p>
                <label>Intitulé:</label>
                <input name="booking_subresource_name" type="text" class="text" value="<?php echo ploopi\str::htmlentities($objSubresource->fields['name']); ?>">
            </p>
            <p>
                <label>Référence:</label>
                <input name="booking_subresource_reference" type="text" class="text" value="<?php echo ploopi\str::htmlentities($objSubresource->fields['reference']); ?>">
            </p>
            <p>
                <label>Ressource liée:</label>
                <?php
                ploopi\db::get()->query("SELECT * FROM ploopi_mod_booking_resource WHERE id_module = {$_SESSION['ploopi']['moduleid']} ORDER BY name");
                ?>
                <select name="booking_subresource_id_resource" class="select" <?php if ($_REQUEST['ploopi_op'] == 'booking_subresource_open') echo 'disabled="disabled"'; ?>>
                    <option value="">(Choisir)</option>
                    <?php
                    while ($row = ploopi\db::get()->fetchrow())
                    {
                        ?>
                        <option value="<?php echo $row['id']; ?>" <?php if ($objSubresource->fields['id_resource'] == $row['id']) echo 'selected="selected"'; ?>><?php echo ploopi\str::htmlentities($row['name']); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <p onclick="javascript:ploopi.checkbox_click(event,'booking_subresource_active');">
                <label for="booking_subresource_active">Actif:</label>
                <input name="booking_subresource_active" id="booking_subresource_active" type="checkbox" class="checkbox" value="1" <?php if ($objSubresource->fields['active']) echo 'checked'; ?> tabindex="111" />
            </p>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser" />
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        include_once './modules/booking/include/global.php';

        $titre = ($_REQUEST['ploopi_op'] == 'booking_subresource_add') ? 'Ajout' : 'Modification';

        echo ploopi\skin::get()->create_popup("{$titre} d'une sous-ressource", $content, 'popup_subresource');
        ploopi\system::kill();
    break;
}
?>
