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
 * Interface de gestion du module
 *
 * @package booking
 * @subpackage public
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */
ploopi\module::init('booking');

global $arrBookingPeriodicity;
global $arrBookingSize;
global $arrBookingColor;

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

if (!empty($_GET['booking_menu'])) $_SESSION['booking']['$booking_menu'] = $_GET['booking_menu'];
if (!isset($_SESSION['booking']['$booking_menu'])) $_SESSION['booking']['$booking_menu'] = '';

switch($_SESSION['booking']['$booking_menu'])
{
    case 'monitoring':
        include_once './modules/booking/public_monitoring.php';
    break;

    default:
    case 'planning':
        echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities("{$_SESSION['ploopi']['modulelabel']} - Gestion"));
        echo ploopi\skin::get()->open_simplebloc('Planning des réservations');
        ?>
        <div id="booking_main">
        <?php
        include_once './modules/booking/public_planning.php';
        ?>
        </div>
        <?php
        echo ploopi\skin::get()->close_simplebloc();

        /**
         * Affichage du popup de sélection des ressources
         */
        ob_start();

        ?>
        <div id="booking_ressource_list">
        <form id="booking_resource_list_form" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=booking_setresources'); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform($('booking_resource_list_form'), 'booking_main'); return false;">
        <?php
        $strResourceType = '';

        foreach ($arrResources as $row)
        {
            if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
            {
                if ($strResourceType != '') echo '</div>';
                $strResourceType = $row['rt_name'];
                ?>

                <div style="border-width:1px 0;border-style:solid;border-color:#bbb;background-color:#ddd;overflow:auto;clear:both;padding:2px;">
                    <input style="display:block;float:left;margin:0;" type="checkbox" onclick="ploopi_checkall(booking_resource_list_form, 'booking_resource<?php echo $row['rt_name']; ?>', this.checked, true);$('booking_resource_list_form').onsubmit();" />
                    <a style="display:block;margin-left:20px;" href="javascript:void(0);" onclick="javascript:with ($('booking_<?php echo $strResourceType; ?>_list')) { style.display = (style.display == 'block') ? 'none' : 'block'; }">
                        <strong><?php echo ploopi\str::htmlentities($strResourceType); ?></strong>
                    </a>
                </div>

                <div id="booking_<?php echo ploopi\str::htmlentities($row['rt_name']); ?>_list" style="display:block;">
                <?php
            }
            ?>
            <p class="checkbox" style="background-color:<?php echo ploopi\str::htmlentities($row['color']); ?>;" onclick="javascript:ploopi_checkbox_click(event, 'booking_resource<?php echo $row['rt_name'].$row['id']; ?>');">
                <input type="checkbox" name="booking_resources[<?php echo $row['id']; ?>]" id="booking_resource<?php echo $row['rt_name'].$row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if (!empty($arrSearchPattern['booking_resources'][$row['id']])) echo 'checked="checked"'; ?> onchange="javascript:$('booking_resource_list_form').onsubmit();" />
                <span><?php echo ploopi\str::htmlentities($row['name']); ?><span>
            </p>

            <?php
        }
        if ($strResourceType != '') echo '</div>';
        ?>
        </form>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        echo ploopi\skin::get()->open_popup(
            'Ressources affichées',
            $content,
            'popup_booking',
            array(
                'intWidth' => 200,
                'intPosx' => '$(\'planning_display\').viewportOffset().left +  $(\'planning_display\').getWidth() - 206',
                'intPosy' => '$(\'planning_display\').viewportOffset().top + 20',
                'booCentered' => false
            )
        );
    break;
}

if (!empty($_REQUEST['error'])) {
    ?>
    <script type="text/javascript">
        Event.observe(window, 'load', function() {
            <?php
            switch($_REQUEST['error']) {
                case 'collision': ?> alert('Il y a déjà une autre réservation validée pour cette ressource aux dates demandées.\nVotre demande n\'a pas pu être enregistrée.'); <?php break;
                case 'collision2': ?> alert('Il y a déjà une autre réservation validée pour cette ressource aux dates demandées.\nVotre demande n\'a pas pu être correctement enregistrée.'); <?php break;
            }
            ?>
        });
    </script>
    <?php
}

if (!empty($_REQUEST['warning'])) {
    ?>
    <script type="text/javascript">
        Event.observe(window, 'load', function() {
            <?php
            switch($_REQUEST['warning']) {
                case 'collision': ?> alert('Il y a déjà une autre réservation pour cette ressource aux dates demandées.\nVotre demande a tout de même été enregistrée.'); <?php break;
            }
            ?>
        });
    </script>
    <?php
}
