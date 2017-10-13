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
 * Opérations sur les événements
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

if ($_SESSION['ploopi']['connected'])
{
    switch($ploopi_op)
    {
        case 'booking_event_planning_delete':
            ploopi_init_module('booking', false, false, false);

            if (!empty($_GET['booking_event_id']))
            {
                include_once './modules/booking/classes/class_booking_event.php';

                $objEvent = new booking_event();
                if ($objEvent->open($_GET['booking_event_id'])) $objEvent->delete();
            }
            ploopi_redirect('admin-light.php?ploopi_op=booking_refresh');
        break;

        case 'booking_event_delete':
            ploopi_init_module('booking', false, false, false);

            if (!empty($_GET['booking_element_list']))
            {
                include_once './modules/booking/classes/class_booking_event.php';

                $element_array = explode(',', $_GET['booking_element_list']);
                foreach($element_array as $elementid)
                {
                    $objEvent = new booking_event();
                    if ($objEvent->open($elementid)) $objEvent->delete();
                }
            }
            ploopi_redirect('admin.php');
        break;

        case 'booking_event_save':
            ploopi_init_module('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';
            include_once './modules/booking/classes/class_booking_resource.php';
            include_once './modules/booking/classes/class_booking_subresource.php';

            if (!empty($_POST['_booking_event_timestp_begin_d']) && !empty($_POST['booking_event_object']))
            {
                $booking_no_collision = 0;
                $objEvent = new booking_event();

                $objEvent->setvalues($_POST, 'booking_event_');
                $objEvent->setdetails($_POST, '_booking_event_');
                $objEvent->setsubresources(isset($_POST['booking_sr']) ? $_POST['booking_sr'] : array());

                $objEvent->setuwm();
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') {
                    $objEvent->fields['id_module'] = $_GET['booking_moduleid'];
                    $booking_no_collision = ploopi_getparam('booking_default_no_collision',$objEvent->fields['id_module']);
                } else {
                    $booking_no_collision = ploopi_getparam('booking_default_no_collision');
                }

                $booError = !$objEvent->isvalid();
                $booWarning = !$objEvent->isvalid(false);
                if ($booking_no_collision) $booError = $booWarning;

                // Non valide, collision avec un autre événement
                if ($booError) {
                    $intTs = ploopi_timestamp2unixtimestamp(ploopi_local2timestamp($_POST['_booking_event_timestp_begin_d']));

                    // Tableau des paramètres complémentaires pour la redirection dans le planning
                    $arrParams = array();
                    $arrParams[] = "booking_resource_id={$objEvent->fields['id_resource']}";
                    $arrParams[] = "booking_month=".date('n', $intTs);
                    $arrParams[] = "booking_year=".date('Y', $intTs);
                    $arrParams[] = "booking_week=".date('W', $intTs);
                    $arrParams[] = "booking_day=".date('j', $intTs);
                    $arrParams[] = 'error=collision';

                    if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&'.implode('&', $arrParams));
                    else ploopi_redirect('admin.php?'.implode('&', $arrParams));
                }

                /**
                 * Envoyer un ticket aux gestionnaires de la ressource
                 * (les utilisateurs des espaces gestionnaires qui disposent de l'action de validation ou qui sont admin sys)
                 */

                $arrUsers = array();

                $objResource = new booking_resource();
                if ($objResource->open($objEvent->fields['id_resource']))
                {
                    // On récupère les utilisateurs gestionnaires de la ressource
                    $arrUsers = $objResource->getusers();

                    $objEvent->save();

                    // Selection des destinataires du ticket
                    $_SESSION['ploopi']['tickets']['users_selected'] = array();
                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                    $strResource = $objResource->fields['name'];

                    $rowDetails = $objEvent->getrawdetails();
                    $strBegin = $rowDetails['timestp_begin_d'].' à '.sprintf("%02dh%02d", $rowDetails['timestp_begin_h'], $rowDetails['timestp_begin_m']);
                    $strEnd = $rowDetails['timestp_end_d'].' à '.sprintf("%02dh%02d", $rowDetails['timestp_end_h'], $rowDetails['timestp_end_m']);

                    $arrSR = array();
                    if (!empty($_POST['booking_sr'])) {
                        foreach($_POST['booking_sr'] as $intIdSR) {
                            $objSR = new booking_subresource();
                            if ($objSR->open($intIdSR)) $arrSR[] = $objSR->fields['name'];
                        }
                    }

                    $strSR = empty($arrSR) ? '' : ' (incluant: '.implode(', ', $arrSR).')';

                    $strTitle = "Demande de réservation pour {$strResource}{$strSR} du {$strBegin} au {$strEnd}";
                    $strMessage = "Nouvelle demande de réservation pour {$strResource}{$strSR} pour la période du {$strBegin} au {$strEnd} pour le motif suivant : <br /><br />".ploopi_nl2br(ploopi_htmlentities($_POST['booking_event_object'])).'<br /><br />Observations:<br /><br />'.ploopi_nl2br(ploopi_htmlentities($rowDetails['message']));

                    // Envoi du ticket
                    ploopi_tickets_send($strTitle, $strMessage);
                }

                $intTs = ploopi_timestamp2unixtimestamp(ploopi_local2timestamp($_POST['_booking_event_timestp_begin_d']));

                // Tableau des paramètres complémentaires pour la redirection dans le planning
                $arrParams = array();
                $arrParams[] = "booking_resource_id={$objEvent->fields['id_resource']}";
                $arrParams[] = "booking_month=".date('n', $intTs);
                $arrParams[] = "booking_year=".date('Y', $intTs);
                $arrParams[] = "booking_week=".date('W', $intTs);
                $arrParams[] = "booking_day=".date('j', $intTs);

                // Attention, collision avec un autre événement
                if ($booWarning) $arrParams[] = 'warning=collision';

                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&'.implode('&', $arrParams));
                else ploopi_redirect('admin.php?'.implode('&', $arrParams));
            }
            else
            {
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
                else ploopi_redirect('admin.php');
            }
        break;

        case 'booking_event_validate':
            ploopi_init_module('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';
            include_once './modules/booking/classes/class_booking_resource.php';
            include_once './modules/booking/classes/class_booking_subresource.php';

            $objEvent = new booking_event();
            $booGlobalError = false;

            if (!empty($_GET['booking_event_id']) && is_numeric($_GET['booking_event_id'])) $objEvent->open($_GET['booking_event_id']);
            else ploopi_redirect("admin.php");

            $objEvent->setvalues($_POST, 'booking_event_');
            $objEvent->setsubresources(isset($_POST['booking_sr']) ? $_POST['booking_sr'] : array());

            $objResource = new booking_resource();
            $strResource = $objResource->open($objEvent->fields['id_resource']) ? $objResource->fields['name'] : 'inconnu';

            $arrSR = array();
            if (!empty($_POST['booking_sr'])) {
                foreach($_POST['booking_sr'] as $intIdSR) {
                    $objSR = new booking_subresource();
                    if ($objSR->open($intIdSR)) $arrSR[] = $objSR->fields['name'];
                }
            }

            $strSR = empty($arrSR) ? '' : ' (incluant: '.implode(', ', $arrSR).')';


            if (!empty($_POST['_booking_event_timestp_begin_d']) && is_array($_POST['_booking_event_timestp_begin_d']))
            {
                $booManaged = true;

                foreach($_POST['_booking_event_timestp_begin_d'] as $intIdEventDetail => $_booking_event_timestp_begin_d)
                {
                    $booError = false;

                    $objEventDetail = new booking_event_detail();
                    if ($objEventDetail->open($intIdEventDetail))
                    {
                        $objEventDetail->fields['timestp_begin'] =
                            ploopi_local2timestamp(
                                $_booking_event_timestp_begin_d,
                                sprintf("%02d:%02d:00",
                                    $_POST['_booking_event_timestp_begin_h'][$intIdEventDetail],
                                    $_POST['_booking_event_timestp_begin_m'][$intIdEventDetail]
                                )
                            );

                        $objEventDetail->fields['timestp_end'] =
                            ploopi_local2timestamp(
                                $_POST['_booking_event_timestp_end_d'][$intIdEventDetail],
                                sprintf("%02d:%02d:00",
                                    $_POST['_booking_event_timestp_end_h'][$intIdEventDetail],
                                    $_POST['_booking_event_timestp_end_m'][$intIdEventDetail]
                                )
                            );

                        $objEventDetail->fields['message'] = isset($_POST['_booking_event_message'][$intIdEventDetail]) ? $_POST['_booking_event_message'][$intIdEventDetail] : '';
                        $objEventDetail->fields['emails'] = isset($_POST['_booking_event_emails'][$intIdEventDetail]) ? $_POST['_booking_event_emails'][$intIdEventDetail] : '';

                        // Date de début/fin au format local
                        $arrDateBegin = ploopi_timestamp2local($objEventDetail->fields['timestp_begin']);
                        $arrDateEnd = ploopi_timestamp2local($objEventDetail->fields['timestp_end']);

                        // Extraction heures/minutes
                        $arrDateBegin_h = intval(substr($arrDateBegin['time'], 0, 2));
                        $arrDateBegin_m = intval(substr($arrDateBegin['time'], 2, 2));
                        $arrDateEnd_h = intval(substr($arrDateEnd['time'], 0, 2));
                        $arrDateEnd_m = intval(substr($arrDateEnd['time'], 2, 2));


                        $strMessage = $strtitle = '';
                        $_SESSION['ploopi']['tickets']['users_selected'] = array();


                        // Changement d'état (Validation, annulation...)
                        if (isset($_POST['_booking_event_validated'][$intIdEventDetail])) {

                            switch($_POST['_booking_event_validated'][$intIdEventDetail]) {
                                // Validation de la demande
                                case '1':
                                    $objEventDetail->fields['validated'] = 1;
                                    $objEventDetail->fields['canceled'] = 0;

                                    if ($objEventDetail->isvalid($objEvent)) {
                                        // envoyer un ticket, demande validée

                                        // On récupère les utilisateurs gestionnaires de la ressource
                                        $arrUsers = $objResource->getusers();

                                        // Selection des destinataires du ticket
                                        foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                        if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                        $strMessage = "La demande de réservation pour {$strResource}{$strSR} du {$arrDateBegin['date']} à ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} à ".substr($arrDateEnd['time'], 0, 5)." a été validée par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']}<br /><br />".ploopi_nl2br(ploopi_htmlentities($objEventDetail->fields['message']));
                                        $strTitle = "Validation de la demande de réservation pour {$strResource}{$strSR}";
                                    }
                                    else $booError = true;
                                break;

                                // Annulation de la demande
                                case '0':
                                    $objEventDetail->fields['canceled'] = 1;
                                    $objEventDetail->fields['validated'] = 0;

                                    // envoyer un ticket, demande annulée

                                    // On récupère les utilisateurs gestionnaires de la ressource
                                    $arrUsers = $objResource->getusers();

                                    // Selection des destinataires du ticket
                                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                    $strMessage = "La demande de réservation pour {$strResource}{$strSR} du {$arrDateBegin['date']} à ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} à ".substr($arrDateEnd['time'], 0, 5)." a été refusée par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi_nl2br(ploopi_htmlentities($objEventDetail->fields['message']));
                                    $strTitle = "Refus de la demande de réservation pour {$strResource}{$strSR}";
                                break;

                                // Suppression de la demande
                                case '9':

                                    // envoyer un ticket, demande annulée

                                    // On récupère les utilisateurs gestionnaires de la ressource
                                    $arrUsers = $objResource->getusers();

                                    // Selection des destinataires du ticket
                                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                    $strMessage = "La demande de réservation pour {$strResource}{$strSR} du {$arrDateBegin['date']} à ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} à ".substr($arrDateEnd['time'], 0, 5)." a été supprimée par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi_nl2br(ploopi_htmlentities($objEventDetail->fields['message']));
                                    $strTitle = "Suppression de la demande de réservation pour {$strResource}{$strSR}";
                                break;
                            }
                        }
                        // Modification "simple"
                        else {

                            if (!$objEventDetail->isvalid($objEvent)) $booError = true;
                            else {
                                $strMessage = "La demande de réservation pour {$strResource}{$strSR} du {$arrDateBegin['date']} à ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} à ".substr($arrDateEnd['time'], 0, 5)." a été modifiée par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi_nl2br(ploopi_htmlentities($objEventDetail->fields['message']));
                                $strTitle = "Modification de la demande de réservation pour {$strResource}{$strSR}";

                                // On récupère les utilisateurs gestionnaires de la ressource
                                $arrUsers = $objResource->getusers();

                                // Selection des destinataires du ticket
                                foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];
                            }
                        }

                        if (!empty($strMessage))
                        {
                            // Envoi d'un ticket à l'initiateur de la demande
                            ploopi_tickets_send(
                                $strTitle,
                                $strMessage
                            );

                            // Envoi d'un mail aux autres personnes concernées
                            if (!empty($objEventDetail->fields['emails']))
                            {
                                $arrEmails = explode(",", $objEventDetail->fields['emails']);
                                $arrTo = array();

                                foreach($arrEmails as $strEmail)
                                {
                                    $strEmail = trim($strEmail);

                                    $arrTo[] =
                                        array(
                                            'address' => $strEmail,
                                            'name' => $strEmail
                                        );
                                }

                                if (!empty($arrTo))
                                {
                                    $arrFrom = array();

                                    if (!empty($_SESSION['ploopi']['user']['email']))
                                    {
                                        $arrFrom[] =
                                            array(
                                                'address' => $_SESSION['ploopi']['user']['email'],
                                                'name' => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                                            );
                                    }
                                    else
                                    {
                                        $arrFrom[] =
                                            array(
                                                'address' => trim(current(explode(',', _PLOOPI_ADMINMAIL))),
                                                'name' => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                                            );
                                    }

                                    ploopi_send_mail($arrFrom, $arrTo, $strTitle, $strMessage);

                                }
                            }
                        }

                        if ($objEventDetail->fields['validated'] == 0 && $objEventDetail->fields['canceled'] == 0) $booManaged = false;

                        // Suppression effective de la demande
                        if (isset($_POST['_booking_event_validated'][$intIdEventDetail]) && $_POST['_booking_event_validated'][$intIdEventDetail] == '9')
                        {
                            $objEventDetail->delete();
                        }
                        else {
                            // Pas d'erreur ? On peut enregistrer
                            if (!$booError) $objEventDetail->save();
                        }
                    }

                    $booGlobalError = $booGlobalError || $booError;
                }
            }


            // On vérifie si le traitement de la demandée est terminée en vérifiant les détails
            $db->query("
                SELECT  count(*) as c
                FROM    ploopi_mod_booking_event_detail
                WHERE   id_event = {$objEvent->fields['id']}
                AND     validated = 0
                AND     canceled = 0
            ");

            $objEvent->fields['managed'] = (($row = $db->fetchrow()) && ($row['c'] == 0)) ? 1 : 0;

            $objEvent->save();

            if ($booGlobalError) {
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&error=collision2');
                else ploopi_redirect('admin.php?error=collision2');
            }

            if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
            else ploopi_redirect('admin.php');
        break;

        case 'booking_event_add':
            ob_start();
            ploopi_init_module('booking');

            global $arrBookingColor;
            global $arrBookingPeriodicity;

            // Cas particulier du mode frontoffice, on teste la présence de moduleid
            if ($_SESSION['ploopi']['mode'] == 'frontoffice' && (empty($_GET['booking_moduleid']) || !is_numeric($_GET['booking_moduleid']) || !ploopi_isactionallowed(_BOOKING_ACTION_ASKFOREVENT, $_SESSION['ploopi']['workspaceid'], $_GET['booking_moduleid']))) ploopi_die();

            $strDate = empty($_GET['booking_resource_date']) ? '' : date('d/m/Y', $_GET['booking_resource_date']);

            include_once './modules/booking/classes/class_booking_event.php';

            $arrResources = ($_SESSION['ploopi']['mode'] == 'frontoffice') ? booking_get_resources(false, $_GET['booking_moduleid']) : booking_get_resources();

            if (!empty($arrResources)) {
                $db->query("SELECT * FROM ploopi_mod_booking_subresource WHERE id_resource IN (".implode(',', array_keys($arrResources)).") AND active = 1 ORDER BY name");
                ?>
                <script type="text/javascript">
                    booking_json_sr = <? echo json_encode(ploopi_array_map('ploopi_utf8encode', $db->getarray())); ?>;
                </script>
                <?
            }

            $objEvent = new booking_event();
            $objEvent->init_description();
            // Si une ressource est passée en paramètre, on la sélectionne par défaut
            if (!empty($_GET['booking_resource_id']) && is_numeric($_GET['booking_resource_id'])) $objEvent->fields['id_resource'] = $_GET['booking_resource_id'];



            ?>
            <form action="<? echo ploopi_urlencode($_SESSION['ploopi']['mode'] == 'frontoffice' ? "index-light.php?ploopi_op=booking_event_save&booking_event_id={$objEvent->fields['id']}&booking_moduleid={$_GET['booking_moduleid']}" : "admin-light.php?ploopi_op=booking_event_save&booking_event_id={$objEvent->fields['id']}"); ?>" method="post" onsubmit="javascript:return booking_event_validate(this);">
            <div class=ploopi_form>
                <p>
                    <label>Ressource:</label>
                    <select class="select" name="booking_event_id_resource" onchange="javascript:booking_resource_onchange(this);">
                        <option value="">(choisir)</option>
                        <?
                        $strResourceType = '';
                        foreach ($arrResources as $row)
                        {
                            if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
                            {
                                if ($strResourceType != '') echo '</optgroup>';
                                $strResourceType = $row['rt_name'];
                                ?>
                                <optgroup label="<? echo ploopi_htmlentities($row['rt_name']); ?>">
                                <?
                            }
                            ?>
                            <option value="<? echo $row['id']; ?>" style="border-left:2px;" <? if ($objEvent->fields['id_resource'] == $row['id']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($row['name']); ?></option>
                            <?
                        }
                        if ($strResourceType != '') echo '</optgroup>';
                        ?>
                    </select>
                </p>
                <div style="padding:0;" id="booking_subresources"></div>
                <p>
                    <label>Objet:</label>
                    <input name="booking_event_object" type="text" class="text" value="<? echo ploopi_htmlentities($objEvent->fields['object']); ?>">
                </p>
                <p>
                    <label>Date/heure de début:</label>
                    <input name="_booking_event_timestp_begin_d" id="_booking_event_timestp_begin_d" class="text" type="text" value="<?php echo ploopi_htmlentities($strDate); ?>" style="width:80px;" onchange="javascript:$('_booking_event_timestp_end_d').value = this.value;" />
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi_open_calendar('_booking_event_timestp_begin_d'); ?></span>
                    <select name="_booking_event_timestp_begin_h" id="_booking_event_timestp_begin_h" class="select" style="width:60px;">
                    <?
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option value="<? echo $i; ?>"><? echo sprintf("%02d h", $i); ?></option><?
                    }
                    ?>
                    </select>

                    <select name="_booking_event_timestp_begin_m" id="_booking_event_timestp_begin_m" class="select" style="width:45px;">
                    <?
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option value="<? echo $i*5; ?>"><? echo sprintf("%02d", $i*5); ?></option><?
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>Date/heure de fin:</label>
                    <input name="_booking_event_timestp_end_d" id="_booking_event_timestp_end_d" class="text" type="text" value="<?php echo ploopi_htmlentities($strDate); ?>" style="width:80px; "/>
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi_open_calendar('_booking_event_timestp_end_d'); ?></span>
                    <select name="_booking_event_timestp_end_h" id="_booking_event_timestp_end_h" class="select" style="width:60px;">
                    <?
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option value="<? echo $i; ?>"><? echo sprintf("%02d h", $i); ?></option><?
                    }
                    ?>
                    </select>
                    <select name="_booking_event_timestp_end_m" id="_booking_event_timestp_end_m" class="select" style="width:45px;">
                    <?
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option value="<? echo $i*5; ?>"><? echo sprintf("%02d", $i*5); ?></option><?
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>Périodicité:</label>
                    <select name="booking_event_periodicity" class="select" style="width:100px;">
                        <option value="">(Aucune)</option>
                        <?
                        foreach ($arrBookingPeriodicity as $key => $value)
                        {
                            ?>
                            <option value="<? echo ploopi_htmlentities($key); ?>"><? echo ploopi_htmlentities($value); ?></option>
                            <?
                        }
                        ?>
                    </select>
                    <em style="float:left;">&nbsp;&nbsp;jusqu'au:&nbsp;&nbsp;</em>
                    <input name="_booking_event_periodicity_end_date" id="_booking_event_periodicity_end_date" class="text" type="text" value="" style="width:80px; "/>
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi_open_calendar('_booking_event_periodicity_end_date'); ?></span>
                </p>
                <p>
                    <label>Commentaire:</label>
                    <textarea style="height:60px;" class="text" name="_booking_event_message" id="_booking_event_message" placeholder="Indiquez éventuellement les autres ressources nécessaires"></textarea>
                </p>
                <p>
                    <label>Destinataires complémentaires (adresses de courriel séparées par &laquo; , &raquo;:</label>
                    <textarea style="height:60px;" class="text" name="_booking_event_emails" id="_booking_event_emails" placeholder="Indiquez éventuellement les adresses de courriel des personnes à avertir lors de la validation"></textarea>
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

            echo $skin->create_popup("Ajout d'une demande de réservation", $content, 'popup_event');
            ploopi_die();
        break;


        /**
         * Permet de déverrouiller un événement déjà traité
         */
        case 'booking_event_unlock':
            ploopi_init_module('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';

            $objEvent = new booking_event();

            if (!empty($_GET['booking_element_id']))
            {
                $arrId = explode(',', $_GET['booking_element_id']);

                if ($objEvent->open($arrId[0]))
                {
                    $objEvent->fields['managed'] = 0;
                    $objEvent->save();

                    ploopi_redirect("admin.php?ploopi_op=booking_event_open&booking_element_id={$_GET['booking_element_id']}");
                }
            }

            ploopi_die();
        break;

        case 'booking_event_detail_cancel':
            ploopi_init_module('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';

            $objEventDetail = new booking_event_detail();

            if (!empty($_GET['booking_event_detail_id']) && is_numeric($_GET['booking_event_detail_id']) && $objEventDetail->open($_GET['booking_event_detail_id']))
            {
                $objEvent = new booking_event();
                if ($objEvent->open($objEventDetail->fields['id_event']))
                {
                    $objEventDetail->fields['validated'] = 0;
                    $objEventDetail->fields['canceled'] = 0;
                    $objEventDetail->save();
                }
            }
            ploopi_redirect("admin.php");
        break;


        case 'booking_event_detail_delete':
            ploopi_init_module('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';
            include_once './modules/booking/classes/class_booking_resource.php';
            include_once './modules/booking/classes/class_booking_subresource.php';

            $objEventDetail = new booking_event_detail();

            if (!empty($_GET['booking_event_detail_id']) && is_numeric($_GET['booking_event_detail_id']) && $objEventDetail->open($_GET['booking_event_detail_id']))
            {

                $objEvent = new booking_event();
                if ($objEvent->open($objEventDetail->fields['id_event']) && $objEvent->fields['id_user'] == $_SESSION['ploopi']['userid'])
                {
                    $objResource = new booking_resource();
                    $strResource = $objResource->open($objEvent->fields['id_resource']) ? $objResource->fields['name'] : 'inconnu';


                    $rowDetailsB = ploopi_timestamp2local($objEventDetail->fields['timestp_begin']);
                    $rowDetailsE = ploopi_timestamp2local($objEventDetail->fields['timestp_end']);

                    $strBegin = $rowDetailsB['date'].' à '.sprintf("%02dh%02d", substr($rowDetailsB['time'], 0, 2), substr($rowDetailsB['time'], 3, 2));
                    $strEnd = $rowDetailsE['date'].' à '.sprintf("%02dh%02d", substr($rowDetailsE['time'], 0, 2), substr($rowDetailsE['time'], 3, 2));

                    $arrSR = array();
                    foreach($objEvent->getsubresources() as $intIdSR) {
                        $objSR = new booking_subresource();
                        if ($objSR->open($intIdSR)) $arrSR[] = $objSR->fields['name'];
                    }

                    $strSR = empty($arrSR) ? '' : ' (incluant: '.implode(', ', $arrSR).')';

                    $_SESSION['ploopi']['tickets']['users_selected'] = array();

                    // On récupère les utilisateurs gestionnaires de la ressource
                    $arrUsers = $objResource->getusers();

                    // Selection des destinataires du ticket
                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                    $strMessage = "La demande de réservation pour {$strResource}{$strSR} du {$strBegin} au {$strEnd} a été supprimée par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']}";
                    $strTitle = "Suppression de la demande de réservation pour {$strResource}{$strSR}";

                    // Envoi d'un ticket à l'initiateur de la demande
                    ploopi_tickets_send(
                        $strTitle,
                        $strMessage
                    );

                    $objEventDetail->delete();
                }
            }
            if ($_SESSION['ploopi']['mode'] == 'backoffice') ploopi_redirect('admin.php');
            else ploopi_redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
        break;
    }
}

switch($ploopi_op)
{
    case 'booking_event_open':
        ob_start();
        ploopi_init_module('booking');

        global $arrBookingColor;

        include_once './modules/booking/classes/class_booking_resource.php';
        include_once './modules/booking/classes/class_booking_resource_workspace.php';
        include_once './modules/booking/classes/class_booking_event.php';
        $objEvent = new booking_event();

        if (!empty($_GET['booking_element_id']))
        {
            // $_GET['booking_element_id'] contient l'id de l'événement ou l'id de l'événement et l'id du détail
            // $arrId[0] = event_id
            // $arrId[1] = event_detail_id (option)
            $arrId = explode(',', $_GET['booking_element_id']);

            $booking_moduleid = ($_SESSION['ploopi']['mode'] == 'backoffice') ? $_SESSION['ploopi']['moduleid'] : $_GET['booking_moduleid'];

            if ($objEvent->open($arrId[0]))
            {
                $objUser = new user();
                $objWorkspace = new workspace();
                $objResource = new booking_resource();
                $objResourceWorkspace = new booking_resource_workspace();

                if ($objWorkspace->open($objEvent->fields['id_workspace'])) $strWorkspace = $objWorkspace->fields['label'];
                else $strWorkspace = "inconnu";

                if ($objUser->open($objEvent->fields['id_user'])) $strUser = "{$objUser->fields['lastname']} {$objUser->fields['firstname']}";
                else $strUser = 'inconnu';

                if ($objResource->open($objEvent->fields['id_resource'])) $strResource = $objResource->fields['name'].(empty($objResource->fields['reference']) ? '' : " ({$objResource->fields['reference']})");
                else $strResource = 'inconnue';

                // Validateur ?
                $booValidator = ($_SESSION['ploopi']['mode'] == 'backoffice') && ploopi_isactionallowed(_BOOKING_ACTION_VALIDATE) && $objResourceWorkspace->open($objEvent->fields['id_resource'], $_SESSION['ploopi']['workspaceid']);

                // Modification possible si "traitement non terminé" (managed=0) et "backoffice"
                // Penser à gérer également les droits de modif
                $booModify = ($objEvent->fields['managed'] == 0 && $booValidator);

                // Récupération des détails de l'événement
                $arrDetails = $objEvent->getdetails();

                // True si au moins un événement modifiable
                $booModifyEventGlobal = $booModify;

                // On boucle sur l'affichage des détails d'événement
                // Pour savoir si au moins un élément est modifiable
                foreach($arrDetails as $detail)
                {
                    $booValidate = ($objEvent->fields['managed'] == 0 && $booValidator && $detail['canceled'] == 0 && $detail['validated'] == 0);
                    $booModifyEvent = $booValidate || ($objEvent->fields['managed'] == 0 && $detail['canceled'] == 0 && $detail['validated'] == 0 && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']);
                    $booModifyEventGlobal = $booModifyEventGlobal || $booModifyEvent;
                }

                $strUrl = $_SESSION['ploopi']['mode'] == 'frontoffice' ? ploopi_urlencode("index-light.php?ploopi_op=booking_event_validate&booking_event_id={$objEvent->fields['id']}&booking_moduleid={$_GET['booking_moduleid']}") : ploopi_urlencode("admin-light.php?ploopi_op=booking_event_validate&booking_event_id={$objEvent->fields['id']}");

                if ($booModify) $arrResources = booking_get_resources();
                else $arrResources = array($objEvent->fields['id_resource'] => 1);

                if (!empty($arrResources)) {
                    $db->query("SELECT * FROM ploopi_mod_booking_subresource WHERE id_resource IN (".implode(',', array_keys($arrResources)).") AND active = 1 ORDER BY name");
                    ?>
                    <script type="text/javascript">
                        booking_json_sr = <? echo json_encode(ploopi_array_map('ploopi_utf8encode', $db->getarray())); ?>;
                    </script>
                    <?
                }
                ?>

                <? if ($booModifyEventGlobal) { ?><form action="<? echo $strUrl; ?>" method="post" onsubmit="javascript:return booking_event_validate_2(this);"><? } ?>

                <div class=ploopi_form>
                    <p>
                        <label>Ressource:</label>
                        <?
                        if ($booModify)
                        {
                            ?>
                            <select class="select" name="booking_event_id_resource" id="booking_event_id_resource" onchange="javascript:booking_resource_onchange(this);">
                                <option value="">(choisir)</option>
                                <?
                                $strResourceType = '';
                                foreach ($arrResources as $row)
                                {
                                    if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
                                    {
                                        if ($strResourceType != '') echo '</optgroup>';
                                        $strResourceType = $row['rt_name'];
                                        ?>
                                        <optgroup label="<? echo ploopi_htmlentities($row['rt_name']); ?>">
                                        <?
                                    }
                                    ?>
                                    <option value="<? echo $row['id']; ?>" style="border-left:2px;" <? if ($objEvent->fields['id_resource'] == $row['id']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($row['name']); ?></option>
                                    <?
                                }
                                if ($strResourceType != '') echo '</optgroup>';
                                ?>
                            </select>
                            <?
                        }
                        else
                        {
                            ?>
                            <span><? echo ploopi_htmlentities($strResource); ?></span>
                            <?
                        }
                        ?>
                    </p>
                    <div style="padding:0;" id="booking_subresources"></div>
                    <?
                    if ($booModify)
                    {
                        ?>
                        <script type="text/javascript">
                            booking_resource_onchange($('booking_event_id_resource')[$('booking_event_id_resource').selectedIndex]);
                            <?
                            foreach($objEvent->getsubresources() as $intIdSR) {
                                ?>
                                $('booking_sr_<? echo $intIdSR; ?>').checked = true;
                                <?
                            }
                            ?>
                        </script>
                        <?
                    }
                    else {
                        $subresources = $objEvent->getsubresources();
                        if (!empty($subresources)) {
                            ?>
                            <script type="text/javascript">
                                $('booking_subresources').insert(
                                    '<p><label>Sous-ressources</label><span id="booking_subresources_ct"></span>'
                                );

                                booking_json_sr.each(function(row) {
                                    if (row.id_resource == <? echo intval($objEvent->fields['id_resource']); ?>) {
                                        console.log(row);
                                        $('booking_subresources_ct').insert(
                                            '<span>'+row.name+'</span>'
                                        );
                                    }
                                });
                            </script>
                            <?
                        }
                    }
                    ?>
                    <p>
                        <label>Objet:</label>
                        <?
                        if ($booModify) { ?><input name="booking_event_object" type="text" class="text" value="<? echo ploopi_htmlentities($objEvent->fields['object']); ?>"><? }
                        else echo '<span>'.ploopi_htmlentities($objEvent->fields['object']).'</span>';
                        ?>
                    </p>
                    <p>
                        <label>Demandeur:</label>
                        <span><? echo ploopi_htmlentities("{$strUser} ({$strWorkspace})"); ?></span>
                    </p>

                    <?
                    if (!empty($objEvent->fields['periodicity']) && !empty($arrBookingPeriodicity[$objEvent->fields['periodicity']]))
                    {
                        ?>
                        <p>
                            <label>Périodicité:</label>
                            <span><? echo ploopi_htmlentities($arrBookingPeriodicity[$objEvent->fields['periodicity']]); ?></span>
                        </p>
                        <?
                    }
                    ?>
                    <div style="background-color:#c0c0c0;margin:1px;padding:4px;font-weight:bold;">Détail des événements associés à cette demande:</div>
                        <div>
                        <?
                        // True si au moins un événement modifiable
                        $booModifyEventGlobal = $booModify;
                        /* <div <? if (sizeof($arrDetails) >2) {?>style="height:300px;overflow:auto;<? } ?>"> */

                        // On boucle sur l'affichage des détail d'événement
                        foreach($arrDetails as $detail)
                        {
                            // On n'affiche que si validé ou validateur connecté ou propriétaire (ou option de filtrage désactivée)
                            if ($detail['validated'] || $booValidator  || ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']) || !$_SESSION['ploopi']['modules'][$booking_moduleid]['booking_eventfilter'])
                            {
                                //$booModify = ($objEvent->fields['managed'] == 0 && $_SESSION['ploopi']['mode'] == 'backoffice' && (!isset($arrId[1]) || (isset($arrId[1]) && $arrId[1] == $detail['id'])));
                                //$booModify = ($objEvent->fields['managed'] == 0 && $_SESSION['ploopi']['mode'] == 'backoffice' && $detail['canceled'] == 0 && $detail['validated'] == 0 && (!isset($arrId[1]) || (isset($arrId[1]) && $arrId[1] == $detail['id'])));
                                $booValidate = ($objEvent->fields['managed'] == 0 && $booValidator && $detail['canceled'] == 0 && $detail['validated'] == 0);
                                $booModifyEvent = $booValidate || ($objEvent->fields['managed'] == 0 && $detail['canceled'] == 0 && $detail['validated'] == 0 && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']);
                                $booModifyEventGlobal = $booModifyEventGlobal || $booModifyEvent;


                                // Date de début/fin au format local
                                $arrDateBegin = ploopi_timestamp2local($detail['timestp_begin']);
                                $arrDateEnd = ploopi_timestamp2local($detail['timestp_end']);

                                // Extraction heures/minutes
                                $arrDateBegin_h = intval(substr($arrDateBegin['time'], 0, 2), 10);
                                $arrDateBegin_m = intval(substr($arrDateBegin['time'], 3, 2), 10);
                                $arrDateEnd_h = intval(substr($arrDateEnd['time'], 0, 2), 10);
                                $arrDateEnd_m = intval(substr($arrDateEnd['time'], 3, 2), 10);

                                // Détermination de la couleur de fond
                                $strBgcolor = 'background-color:'.($detail['validated'] ? $arrBookingColor['validated'] : ($detail['canceled'] ? $arrBookingColor['canceled'] : $arrBookingColor['unknown'])).';';

                                // Si un détail doit être sélectionné
                                $strBorderColor = isset($arrId[1]) && $arrId[1] == $detail['id'] ? 'border:4px solid #8888ff' : 'border:1px solid #c0c0c0';
                                ?>
                                <div style="<? echo $strBorderColor; ?>;margin:1px;<? echo $strBgcolor; ?>" id="booking_event_bg<? echo $detail['id']; ?>">
                                    <p>
                                        <label>Date/heure de début:</label>
                                        <?
                                        if ($booModifyEvent)
                                        {
                                            ?>
                                            <input name="_booking_event_timestp_begin_d[<? echo $detail['id']; ?>]" id="_booking_event_timestp_begin_d<? echo $detail['id']; ?>" data-id="<? echo $detail['id']; ?>" class="booking_date text" type="text" value="<?php echo ploopi_htmlentities($arrDateBegin['date']); ?>" style="width:80px;" onchange="javascript:if ($('_booking_event_timestp_end_d<? echo $detail['id']; ?>').value == '') $('_booking_event_timestp_end_d<? echo $detail['id']; ?>').value = this.value;" />
                                            <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi_open_calendar("_booking_event_timestp_begin_d{$detail['id']}"); ?></span>
                                            <select name="_booking_event_timestp_begin_h[<? echo $detail['id']; ?>]" id="_booking_event_timestp_begin_h<? echo $detail['id']; ?>" class="select" style="width:60px;">
                                            <?
                                            for ($i = 0; $i < 24; $i++)
                                            {
                                                ?><option value="<? echo $i; ?>" <? if ($arrDateBegin_h == $i) echo 'selected="selected"'; ?>><? echo sprintf("%02d h", $i); ?></option><?
                                            }
                                            ?>
                                            </select>
                                            <select name="_booking_event_timestp_begin_m[<? echo $detail['id']; ?>]" id="_booking_event_timestp_begin_m<? echo $detail['id']; ?>" class="select" style="width:45px;">
                                            <?
                                            for ($i = 0; $i < 12; $i++)
                                            {
                                                ?><option value="<? echo $i*5; ?>" <? if ($arrDateBegin_m == $i*5) echo 'selected="selected"'; ?>><? echo sprintf("%02d", $i*5); ?></option><?
                                            }
                                            ?>
                                            </select>
                                            <?
                                        }
                                        else
                                        {
                                            ?>
                                            <span>
                                                <?
                                                echo ploopi_htmlentities($arrDateBegin['date']).' '.substr($arrDateBegin['time'], 0, 5);

                                                echo '<strong style="margin-left:10px;">'.($detail['validated'] ? 'Validé' : ($detail['canceled'] ? 'Refusé' : 'Indeterminé')).'</strong>';

                                                // Peuvent supprimer :
                                                // Propriétaire si non validé/refusé
                                                // Gestionnaire si validé/refusé
                                                // Et si non vérouillé

                                                if ($objEvent->fields['managed'] == 0 && (($_SESSION['ploopi']['userid'] == $objEvent->fields['id_user'] && $detail['validated'] == 0 && $detail['canceled'] == 0)))
                                                {
                                                    $strUrl = ploopi_urlencode($_SESSION['ploopi']['mode'] == 'backoffice' ? "admin-light.php?ploopi_op=booking_event_detail_delete&booking_event_detail_id={$detail['id']}" : "index-light.php?ploopi_op=booking_event_detail_delete&booking_moduleid={$_GET['booking_moduleid']}&booking_event_detail_id={$detail['id']}");
                                                    ?>
                                                    <strong style="margin-left:10px;">(<a href="javascript:void(0);" onclick="javascript:if (confirm('Attention cette action va supprimer définitivement la demande de réservation.\nVoulez vous continuer ?')) document.location.href = '<? echo $strUrl; ?>';" style="color:#a60000;" title="Supprimer cette réservation">Supprimer</a>)</strong>
                                                    <?
                                                }

                                                // Peuvent annuler :
                                                // Gestionnaire si validé/refusé
                                                // Et si non vérouillé

                                                if ($objEvent->fields['managed'] == 0  && $booValidator && ($detail['validated'] == 1 || $detail['canceled'] == 1))
                                                {
                                                    ?>
                                                    <strong style="margin-left:10px;">(<a href="javascript:void(0);" onclick="javascript:if (confirm('Attention cette action va annuler cette validation.\nVoulez vous continuer ?')) document.location.href = '<? echo ploopi_urlencode("admin-light.php?ploopi_op=booking_event_detail_cancel&booking_event_detail_id={$detail['id']}"); ?>';" style="color:#a60000;" title="Annuler cette validation">Annuler</a>)</strong>
                                                    <?
                                                }

                                                ?>
                                            </span>
                                            <?
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <label>Date/heure de fin:</label>
                                        <?
                                        if ($booModifyEvent)
                                        {
                                            ?>
                                            <input name="_booking_event_timestp_end_d[<? echo $detail['id']; ?>]" id="_booking_event_timestp_end_d<? echo $detail['id']; ?>" class="text" type="text" value="<?php echo ploopi_htmlentities($arrDateEnd['date']); ?>" style="width:80px; "/>
                                            <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi_open_calendar("_booking_event_timestp_end_d{$detail['id']}"); ?></span>
                                            <select name="_booking_event_timestp_end_h[<? echo $detail['id']; ?>]" id="_booking_event_timestp_end_h<? echo $detail['id']; ?>" class="select" style="width:60px;">
                                            <?
                                            for ($i = 0; $i < 24; $i++)
                                            {
                                                ?><option value="<? echo $i; ?>" <? if ($arrDateEnd_h == $i) echo 'selected="selected"'; ?>><? echo sprintf("%02d h", $i); ?></option><?
                                            }
                                            ?>
                                            </select>
                                            <select name="_booking_event_timestp_end_m[<? echo $detail['id']; ?>]" id="_booking_event_timestp_end_m<? echo $detail['id']; ?>" class="select" style="width:45px;">
                                            <?
                                            for ($i = 0; $i < 12; $i++)
                                            {
                                                ?><option value="<? echo $i*5; ?>" <? if ($arrDateEnd_m == $i*5) echo 'selected="selected"'; ?>><? echo sprintf("%02d", $i*5); ?></option><?
                                            }
                                            ?>
                                            </select>
                                            <?
                                        }
                                        else
                                        {
                                            ?>
                                            <span><? echo $arrDateEnd['date']; ?>&nbsp;<? echo substr($arrDateEnd['time'], 0, 5); ?></span>
                                            <?
                                        }
                                        ?>
                                    </p>

                                    <?
                                    if ($booValidate)
                                    {
                                        ?>
                                        <div style="font-weight:bold;padding-left:10px;" class="ploopi_va">
                                            <strong>Décision :</strong>
                                            <?
                                            // calcul de la différence entre le moment actuel et la date de fin mission
                                            // si mission est passée, on ne peut plus la supprimer
                                            $diff=time()-ploopi_timestamp2unixtimestamp($detail['timestp_end']);
                                            if (($diff/24/3600)+1 < 1) {
                                                ?>
                                                <input type="radio" class="checkbox" name="_booking_event_validated[<? echo $detail['id']; ?>]" id="_booking_event_validated<? echo $detail['id']; ?>_9" value="9" onchange="javascript:$('booking_event_bg<? echo $detail['id']; ?>').style.backgroundColor = '<? echo $arrBookingColor['deleted']; ?>';" />
                                                <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<? echo $detail['id']; ?>_9');">Supprimer</a>
                                                <?
                                            }
                                            ?>
                                            <input type="radio" class="checkbox" name="_booking_event_validated[<? echo $detail['id']; ?>]" id="_booking_event_validated<? echo $detail['id']; ?>_0" value="0" onchange="javascript:$('booking_event_bg<? echo $detail['id']; ?>').style.backgroundColor = '<? echo $arrBookingColor['canceled']; ?>';" <? if ($detail['canceled']) echo 'checked="checked"'; ?> />
                                            <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<? echo $detail['id']; ?>_0');">Refuser</a>

                                            <input type="radio" class="checkbox" name="_booking_event_validated[<? echo $detail['id']; ?>]" id="_booking_event_validated<? echo $detail['id']; ?>_1" value="1" onchange="javascript:$('booking_event_bg<? echo $detail['id']; ?>').style.backgroundColor = '<? echo $arrBookingColor['validated']; ?>';" <? if ($detail['validated']) echo 'checked="checked"'; ?> />
                                            <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<? echo $detail['id']; ?>_1');">Valider</a>
                                        </div>
                                        <?
                                    }

                                    // seuls les validateurs et propriétaires peuvent voire "commentaire" et "emails"
                                    ?>
                                    <p>
                                        <label>Commentaire:</label>
                                        <?
                                        if ($booModifyEvent) { ?><textarea style="height:60px;" class="text" name="_booking_event_message[<? echo $detail['id']; ?>]" id="_booking_event_message<? echo $detail['id']; ?>"><? echo ploopi_htmlentities($detail['message']); ?></textarea><? }
                                        else echo '<span>'.ploopi_nl2br(ploopi_htmlentities($detail['message'])).'</span>';
                                        ?>
                                    </p>
                                    <p>
                                        <label>Destinataires complémentaires (adresses de courriel séparées par &laquo; , &raquo;:</label>
                                        <?
                                        if ($booModifyEvent) { ?><textarea style="height:60px;" class="text" name="_booking_event_emails[<? echo $detail['id']; ?>]" id="_booking_event_emails<? echo $detail['id']; ?>"><? echo ploopi_htmlentities($detail['emails']); ?></textarea><? }
                                        else echo '<span>'.ploopi_nl2br(ploopi_htmlentities($detail['emails'])).'</span>';
                                        ?>
                                    </p>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>
                </div>
                <div style="padding:4px;text-align:right;">
                    <?
                    // Enregistrement et/ou validation
                    if ($booModifyEventGlobal) {
                        if ($objEvent->fields['managed'] == 0 && (($_SESSION['ploopi']['userid'] == $objEvent->fields['id_user'] && $detail['validated'] == 0 && $detail['canceled'] == 0)))
                        {
                            $strUrl = ploopi_urlencode($_SESSION['ploopi']['mode'] == 'backoffice' ? "admin-light.php?ploopi_op=booking_event_detail_delete&booking_event_detail_id={$detail['id']}" : "index-light.php?ploopi_op=booking_event_detail_delete&booking_moduleid={$_GET['booking_moduleid']}&booking_event_detail_id={$detail['id']}");
                            ?>
                                <input type="button" class="button" value="Supprimer" title="Supprimer cette réservation" style="color:#a60000;font-weight:bold;" onclick="javascript:if (confirm('Attention cette action va supprimer définitivement la demande de réservation.\nVoulez vous continuer ?')) document.location.href = '<? echo $strUrl; ?>';" />
                            <?
                        }
                        ?>
                        <input type="reset" class="button" value="Réinitialiser" />
                        <input type="submit" class="button" value="Enregistrer" />
                        <?
                    }
                    // Déverrouillage
                    elseif ($objEvent->fields['managed'] == 1 && ploopi_isactionallowed(_BOOKING_ACTION_VALIDATE)) {

                        if ($_SESSION['ploopi']['mode'] == 'frontoffice') {
                            $strUrl = "ploopi_xmlhttprequest_todiv('index-light.php','".ploopi_queryencode("ploopi_op=booking_event_unlock&booking_element_id={$_GET['booking_element_id']}&booking_moduleid={$_GET['booking_moduleid']}")."', 'popup_event');";
                        } else {
                            $strUrl = "ploopi_xmlhttprequest_todiv('admin-light.php','".ploopi_queryencode("ploopi_op=booking_event_unlock&booking_element_id={$_GET['booking_element_id']}")."', 'popup_event');";
                        }

                        ?>
                        <div style="margin-bottom:2px;"><strong style="color:#a60000;">Cette demande est verrouillée car son traitement est terminé</strong></div>
                        <div>
                            <input type="button" class="button" value="Deverrouiller" onclick="javascript:<? echo $strUrl; ?>"/>
                            <input type="button" class="button" value="Fermer" onclick="javascript:ploopi_hidepopup('popup_event');"/>
                        </div>
                        <?
                    }
                    // Tout autre cas
                    else
                    {
                        ?>
                        <div>
                            <input type="button" class="button" value="Fermer" onclick="javascript:ploopi_hidepopup('popup_event');"/>
                        </div>
                        <?
                    }
                    ?>
                </div>

                <? if ($booModify) { ?></form><? } ?>

                <?
            }
            else
            {
                ?>
                <div style="padding:4px;" class="error">Cette demande n'existe pas</div>
                <?
            }
            $content = ob_get_contents();
            ob_end_clean();

            include_once './modules/booking/include/global.php';

            echo $skin->create_popup("Consultation d'une demande de réservation", $content, 'popup_event');
        }
        ploopi_die();
    break;
}

?>
