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

    You should have received a copy of the GNU GeneralF Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Affichage frontoffice du planning
 *
 * @package booking
 * @subpackage wce
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/*
 * Particularités frontoffice :
 * - le module_id n'est pas en session, il faut utiliser $obj['module_id'];
 * - l'id article "hôte" est dispo via webedit_get_articleid()
 * - l'id rubrique "hôte" est dispo via webedit_get_headingid()
 * - le template "hôte" est dispo via webedit_get_template_name()
 */

if (isset($object))
{
    ploopi_init_module('booking');

    // Importe les variables globales du module (Rappel : on est dans une fonction !)
    global $arrBookingPeriodicity;
    global $arrBookingSize;
    global $arrBookingColor;

    // Récupération du module_id du module intégré
    $booking_moduleid = $obj['module_id'];

    // Récupération des variables d'environnement de l'article en cours d'affichage
    global $articleid;
    global $headingid;
    global $template_name;
    global $template_path;
    global $skin;

    if (!empty($skin))
    {
        // Url de base de l'article
        // $url = webedit_get_url();
        $url = "index.php?headingid={$headingid}&articleid={$articleid}";

        $_SESSION['booking'][$booking_moduleid]['article_url'] = $url;

        // Récupération du paramètre "op"
        $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

        switch($object)
        {
            case 'display':
            ?>
               <div id="booking_main">
                <?php
                include './modules/booking/wce_display.php';
                /**
                 * Affichage du popup de sélection des ressources
                 */
                ob_start();

                ?>

                <div id="booking_ressource_list">
                <form id="booking_resource_list_form" action="<?php echo ploopi_urlencode("index-light.php?ploopi_op=booking_setresources&booking_moduleid={$booking_moduleid}"); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform($('booking_resource_list_form'), 'booking_main'); return false;">
				<?php
                $strResourceType = '';
                foreach ($arrResources as $row)
                {
                    if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
                    {
                        if ($strResourceType != '') echo '</div>';
                        $strResourceType = $row['rt_name'];
                        ?>
						<div style="float:left;height:18px;margin:0;">
							<input type="checkbox" style="display:inline;" onclick="ploopi_checkall(booking_resource_list_form, 'booking_resource<?php echo $row['rt_name']; ?>', this.checked, true);$('booking_resource_list_form').onsubmit();" />
						</div>
                        <a href="javascript:void(0);" onclick="javascript:with ($('booking_<?php echo ploopi_htmlentities($strResourceType); ?>_list')) { style.display = (style.display == 'block') ? 'none' : 'block'; }">
							<div class="ploopi_va" style="border-width:1px 0;border-style:solid;border-color:#bbb;background-color:#ddd;height:18px;">
								<strong><?php echo ploopi_htmlentities($strResourceType); ?></strong>
                            </div>
                        </a>
                        <div id="booking_<?php echo ploopi_htmlentities($row['rt_name']); ?>_list" style="display:block;">
                        <?php
                    }
                    ?>
                    <p class="checkbox" style="background-color:<?php echo ploopi_htmlentities($row['color']); ?>;" onclick="javascript:ploopi_checkbox_click(event, 'booking_resource<?php echo $row['id']; ?>');">
                        <input type="checkbox" name="booking_resources[<?php echo $row['id']; ?>]" id="booking_resource<?php echo $row['rt_name'].$row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if (!empty($arrSearchPattern['booking_resources'][$row['id']])) echo 'checked="checked"'; ?> onchange="javascript:$('booking_resource_list_form').onsubmit();" />
                        <span><?php echo ploopi_htmlentities($row['name']); ?><span>
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


                echo $skin->open_popup(
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

                unset($content);
                ?>
                </div>
                <?php
            break;
            case 'history': include_once './modules/booking/wce_history.php'; break;
        }

    }
    else echo "Problème de compatibilité avec ce template";
}
?>
