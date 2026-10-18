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

        // ploopi4 : calendrier en grille fluide, ressources dans une colonne à côté (sinon : fenêtre flottante)
        $booBookingGrille = ploopi\skin::get()->version() >= 4;
        if ($booBookingGrille) echo '<div class="p4-cal-layout"><div class="p4-cal-layout-main">';

        echo ploopi\skin::get()->open_simplebloc('Planning des réservations');
        ?>
        <div id="booking_main">
        <?php
        include_once './modules/booking/public_planning.php';
        ?>
        </div>
        <?php
        echo ploopi\skin::get()->close_simplebloc();

        if ($booBookingGrille) echo '</div>';

        /**
         * Affichage du popup de sélection des ressources
         */
        ob_start();

        ?>
        <div id="booking_ressource_list">
        <form id="booking_resource_list_form" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=booking_setresources'); ?>" method="post" data-booking-submit="booking_main">
        <?php
        $strResourceType = '';

        foreach ($arrResources as $row)
        {
            if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
            {
                if ($strResourceType != '') echo '</div>';
                $strResourceType = $row['rt_name'];
                ?>

                <div class="booking_bandeau">
                    <input type="checkbox" autocomplete="off" id="booking_rt<?php echo $row['id_resourcetype']; ?>" style="display:block;float:left;margin:0;" data-booking-rt="<?php echo intval($row['id_resourcetype']); ?>" />
                    <a style="display:block;margin-left:20px;" href="#" data-ploopi-switchdisplay="booking_<?php echo intval($row['id_resourcetype']); ?>_list">
                        <strong><?php echo ploopi\str::htmlentities($strResourceType); ?></strong>
                    </a>
                </div>

                <div id="booking_<?php echo $row['id_resourcetype']; ?>_list" style="display:block;">
                <?php
            }
            ?>
            <p class="checkbox ploopi_checkbox_zone" style="<?php echo $booBookingGrille ? '--p4-res:' : 'background-color:'; echo ploopi\str::htmlentities($row['color']); ?>;" data-checkbox="booking_resource<?php echo $row['id_resourcetype'].$row['id']; ?>">
                <input type="checkbox" autocomplete="off" name="booking_resources[<?php echo $row['id']; ?>]" id="booking_resource<?php echo $row['id_resourcetype'].$row['id']; ?>" class="booking_rt<?php echo $row['id_resourcetype']; ?>" value="<?php echo $row['id']; ?>" <?php if (!empty($arrSearchPattern['booking_resources'][$row['id']])) echo 'checked="checked"'; ?> data-booking-resource="<?php echo intval($row['id_resourcetype']); ?>" />
                <span><?php echo ploopi\str::htmlentities($row['name']); ?></span>
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

        if ($booBookingGrille) echo '<aside class="p4-cal-layout-side">'.ploopi\skin::get()->open_simplebloc('Ressources affichées').$content.ploopi\skin::get()->close_simplebloc().'</aside></div>';
        else echo ploopi\skin::get()->open_popup(
            'Ressources affichées',
            $content,
            'popup_booking',
            array(
                'intWidth' => 200,
                'intPosx' => 'jQuery(\'#planning_display\').viewportOffset().left +  $(\'#planning_display\').width() - 206',
                'intPosy' => 'jQuery(\'#planning_display\').viewportOffset().top + 20',
                'booCentered' => false
            )
        );
    break;
}

if (!empty($_REQUEST['error'])) {
    ?>
    <script nonce="<?php echo ploopi\csp::nonce(); ?>">
        jQuery(function() {
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
    <script nonce="<?php echo ploopi\csp::nonce(); ?>">
        jQuery(function() {
            <?php
            switch($_REQUEST['warning']) {
                case 'collision': ?> alert('Il y a déjà une autre réservation pour cette ressource aux dates demandées.\nVotre demande a tout de même été enregistrée.'); <?php break;
            }
            ?>
        });
    </script>
    <?php
}
