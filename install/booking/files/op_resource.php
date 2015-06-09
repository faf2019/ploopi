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
 * Opérations sur les ressources
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
    case 'booking_resource_delete':
        if (!empty($_GET['booking_element_list']))
        {
            include_once './modules/booking/classes/class_booking_resource.php';

            $element_array = preg_split('/,/', $_GET['booking_element_list']);
            foreach($element_array as $elementid)
            {
                $objResource = new booking_resource();
                if ($objResource->open($elementid)) $objResource->delete();
            }
        }
        ploopi_redirect('admin.php');
    break;

    case 'booking_resource_save':
        include_once './modules/booking/classes/class_booking_resource.php';
        include_once './modules/booking/classes/class_booking_resource_workspace.php';

        $objResource = new booking_resource();

        if (!empty($_GET['booking_resource_id']) && is_numeric($_GET['booking_resource_id'])) $objResource->open($_GET['booking_resource_id']);
        $objResource->setvalues($_POST, 'booking_resource_');

        if (!isset($_POST['booking_resource_active'])) $objResource->fields['active'] = 0;

        $intIdRes = $objResource->save();

        // suppression des espaces déjà rattachés
        $db->query("
            DELETE FROM ploopi_mod_booking_resource_workspace
            WHERE       id_resource = {$intIdRes}
        ");

        if (!empty($_POST['booking_resourceworkspace_id_workspace']))
        {
            foreach($_POST['booking_resourceworkspace_id_workspace'] as $intIdw)
            {
                $ObjResourceWorkspace = new booking_resource_workspace();
                $ObjResourceWorkspace->fields['id_workspace'] = $intIdw;
                $ObjResourceWorkspace->fields['id_resource'] = $intIdRes;
                $ObjResourceWorkspace->save();
            }
        }


        ploopi_redirect("admin.php?booking_tab=resource");
    break;

    case 'booking_resource_add':
    case 'booking_resource_open':
        ob_start();
        ploopi_init_module('booking');

        include_once './modules/booking/classes/class_booking_resource.php';

        $objResource = new booking_resource();

        switch($_REQUEST['ploopi_op'])
        {
            case 'booking_resource_add':
                $objResource->init_description();
            break;

            case 'booking_resource_open':
                if (!empty($_GET['booking_element_id']) && is_numeric($_GET['booking_element_id'])) $objResource->open($_GET['booking_element_id']);
                else $objResource->init_description();
            break;
        }
        ?>
        <form action="<? echo ploopi_urlencode("admin-light.php?ploopi_op=booking_resource_save&booking_resource_id={$objResource->fields['id']}"); ?>" method="post" onsubmit="javascript:return booking_resource_validate(this);">
        <div class=ploopi_form>
            <p>
                <label>Intitulé:</label>
                <input name="booking_resource_name" type="text" class="text" value="<? echo ploopi_htmlentities($objResource->fields['name']); ?>">
            </p>
            <p>
                <label>Référence:</label>
                <input name="booking_resource_reference" type="text" class="text" value="<? echo ploopi_htmlentities($objResource->fields['reference']); ?>">
            </p>
            <p>
                <label>Type de ressource:</label>
                <?
                $db->query("SELECT * FROM ploopi_mod_booking_resourcetype WHERE id_module = {$_SESSION['ploopi']['moduleid']} ORDER BY name");
                ?>
                <select name="booking_resource_id_resourcetype" class="select">
                    <option value="">(Choisir)</option>
                    <?
                    while ($row = $db->fetchrow())
                    {
                        ?>
                        <option value="<? echo $row['id']; ?>" <? if ($objResource->fields['id_resourcetype'] == $row['id']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($row['name']); ?></option>
                        <?
                    }
                    ?>
                </select>
            </p>
            <p>
                <label>Géré par:</label>
                <div id="booking_treeview">
                    <?
                    // Espaces concernés par la ressource
                    $arrResWorkspaces = $objResource->getworkspaces();

                    // Arbre complet des espaces ayant accès au module "booking"
                    $arrWorkspacesTree = booking_get_workspaces($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_workspace'], $arrResWorkspaces);

                    echo booking_display_workspaces($arrWorkspacesTree, 'booking_resourceworkspace_id_workspace[]', $arrResWorkspaces);
                    ?>
                </div>
            </p>
            <p>
                <label>Couleur planning:</label>
                <span>
                    <input name="booking_resource_color" id="booking_resource_color" class="text" type="text" value="<? echo ploopi_htmlentities($objResource->fields['color']); ?>" style="width:60px;cursor:pointer;" />
                </span>
                <script type="text/javascript">new jscolor.color($('booking_resource_color'), {hash:true})</script>
            </p>
            <p onclick="javascript:ploopi_checkbox_click(event,'booking_resource_active');">
                <label for="booking_resource_active">Actif:</label>
                <input name="booking_resource_active" id="booking_resource_active" type="checkbox" class="checkbox" value="1" <? if ($objResource->fields['active']) echo 'checked'; ?> tabindex="111" />
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

        $titre = ($_REQUEST['ploopi_op'] == 'booking_resource_add') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'une ressource", $content, 'popup_resource');
        ploopi_die();
    break;
}
?>
