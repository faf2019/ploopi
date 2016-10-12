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
 * Op�rations sur les �v�nements
 *
 * @package booking
 * @subpackage op
 * @copyright Ovensia
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Switch sur les diff�rentes op�rations possibles
 */

if ($_SESSION['ploopi']['connected'])
{
    switch($ploopi_op)
    {
        case 'booking_event_planning_delete':
            ploopi\module::init('booking', false, false, false);

            if (!empty($_GET['booking_event_id']))
            {
                include_once './modules/booking/classes/class_booking_event.php';

                $objEvent = new booking_event();
                if ($objEvent->open($_GET['booking_event_id'])) $objEvent->delete();
            }
            ploopi\output::redirect('admin-light.php?ploopi_op=booking_refresh');
        break;

        case 'booking_event_delete':
            ploopi\module::init('booking', false, false, false);

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
            ploopi\output::redirect('admin.php');
        break;

        case 'booking_event_save':
            ploopi\module::init('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';
            include_once './modules/booking/classes/class_booking_resource.php';
            include_once './modules/booking/classes/class_booking_subresource.php';

            if (!empty($_POST['_booking_event_timestp_begin_d']) && !empty($_POST['booking_event_object']))
            {
                $objEvent = new booking_event();

                $objEvent->setvalues($_POST, 'booking_event_');
                $objEvent->setdetails($_POST, '_booking_event_');
                $objEvent->setsubresources(isset($_POST['booking_sr']) ? $_POST['booking_sr'] : array());

                $objEvent->setuwm();
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') $objEvent->fields['id_module'] = $_GET['booking_moduleid'];


                $booError = !$objEvent->isvalid();
                $booWarning = !$objEvent->isvalid(false);

                // Non valide, collision avec un autre �v�nement
                if ($booError) {
                    $intTs = ploopi\date::timestamp2unixtimestamp(ploopi\date::local2timestamp($_POST['_booking_event_timestp_begin_d']));

                    // Tableau des param�tres compl�mentaires pour la redirection dans le planning
                    $arrParams = array();
                    $arrParams[] = "booking_resource_id={$objEvent->fields['id_resource']}";
                    $arrParams[] = "booking_month=".date('n', $intTs);
                    $arrParams[] = "booking_year=".date('Y', $intTs);
                    $arrParams[] = "booking_week=".date('W', $intTs);
                    $arrParams[] = "booking_day=".date('j', $intTs);
                    $arrParams[] = 'error=collision';

                    if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&'.implode('&', $arrParams));
                    else ploopi\output::redirect('admin.php?'.implode('&', $arrParams));
                }

                /**
                 * Envoyer un ticket aux gestionnaires de la ressource
                 * (les utilisateurs des espaces gestionnaires qui disposent de l'action de validation ou qui sont admin sys)
                 */

                $arrUsers = array();

                $objResource = new booking_resource();
                if ($objResource->open($objEvent->fields['id_resource']))
                {
                    // On r�cup�re les utilisateurs gestionnaires de la ressource
                    $arrUsers = $objResource->getusers();

                    $objEvent->save();

                    // Selection des destinataires du ticket
                    $_SESSION['ploopi']['tickets']['users_selected'] = array();
                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                    $strResource = $objResource->fields['name'];

                    $rowDetails = $objEvent->getrawdetails();
                    $strBegin = $rowDetails['timestp_begin_d'].' � '.sprintf("%02dh%02d", $rowDetails['timestp_begin_h'], $rowDetails['timestp_begin_m']);
                    $strEnd = $rowDetails['timestp_end_d'].' � '.sprintf("%02dh%02d", $rowDetails['timestp_end_h'], $rowDetails['timestp_end_m']);

                    $arrSR = array();
                    if (!empty($_POST['booking_sr'])) {
                        foreach($_POST['booking_sr'] as $intIdSR) {
                            $objSR = new booking_subresource();
                            if ($objSR->open($intIdSR)) $arrSR[] = $objSR->fields['name'];
                        }
                    }

                    $strSR = empty($arrSR) ? '' : ' (incluant: '.implode(', ', $arrSR).')';

                    $strTitle = "Demande de r�servation pour {$strResource}{$strSR} du {$strBegin} au {$strEnd}";
                    $strMessage = "Nouvelle demande de r�servation pour {$strResource}{$strSR} pour la p�riode du {$strBegin} au {$strEnd} pour le motif suivant : <br /><br />".ploopi\str::nl2br(ploopi\str::htmlentities($_POST['booking_event_object'])).'<br /><br />Observations:<br /><br />'.ploopi\str::nl2br(ploopi\str::htmlentities($rowDetails['message']));

                    // Envoi du ticket
                    ploopi\ticket::send($strTitle, $strMessage);
                }

                $intTs = ploopi\date::timestamp2unixtimestamp(ploopi\date::local2timestamp($_POST['_booking_event_timestp_begin_d']));

                // Tableau des param�tres compl�mentaires pour la redirection dans le planning
                $arrParams = array();
                $arrParams[] = "booking_resource_id={$objEvent->fields['id_resource']}";
                $arrParams[] = "booking_month=".date('n', $intTs);
                $arrParams[] = "booking_year=".date('Y', $intTs);
                $arrParams[] = "booking_week=".date('W', $intTs);
                $arrParams[] = "booking_day=".date('j', $intTs);

                // Attention, collision avec un autre �v�nement
                if ($booWarning) $arrParams[] = 'warning=collision';

                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&'.implode('&', $arrParams));
                else ploopi\output::redirect('admin.php?'.implode('&', $arrParams));
            }
            else
            {
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
                else ploopi\output::redirect('admin.php');
            }
        break;

        case 'booking_event_validate':
            ploopi\module::init('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';
            include_once './modules/booking/classes/class_booking_event_detail.php';
            include_once './modules/booking/classes/class_booking_resource.php';
            include_once './modules/booking/classes/class_booking_subresource.php';

            $objEvent = new booking_event();
            $booGlobalError = false;

            if (!empty($_GET['booking_event_id']) && is_numeric($_GET['booking_event_id'])) $objEvent->open($_GET['booking_event_id']);
            else ploopi\output::redirect("admin.php");

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
                            ploopi\date::local2timestamp(
                                $_booking_event_timestp_begin_d,
                                sprintf("%02d:%02d:00",
                                    $_POST['_booking_event_timestp_begin_h'][$intIdEventDetail],
                                    $_POST['_booking_event_timestp_begin_m'][$intIdEventDetail]
                                )
                            );

                        $objEventDetail->fields['timestp_end'] =
                            ploopi\date::local2timestamp(
                                $_POST['_booking_event_timestp_end_d'][$intIdEventDetail],
                                sprintf("%02d:%02d:00",
                                    $_POST['_booking_event_timestp_end_h'][$intIdEventDetail],
                                    $_POST['_booking_event_timestp_end_m'][$intIdEventDetail]
                                )
                            );

                        $objEventDetail->fields['message'] = isset($_POST['_booking_event_message'][$intIdEventDetail]) ? $_POST['_booking_event_message'][$intIdEventDetail] : '';
                        $objEventDetail->fields['emails'] = isset($_POST['_booking_event_emails'][$intIdEventDetail]) ? $_POST['_booking_event_emails'][$intIdEventDetail] : '';

                        // Date de d�but/fin au format local
                        $arrDateBegin = ploopi\date::timestamp2local($objEventDetail->fields['timestp_begin']);
                        $arrDateEnd = ploopi\date::timestamp2local($objEventDetail->fields['timestp_end']);

                        // Extraction heures/minutes
                        $arrDateBegin_h = intval(substr($arrDateBegin['time'], 0, 2));
                        $arrDateBegin_m = intval(substr($arrDateBegin['time'], 2, 2));
                        $arrDateEnd_h = intval(substr($arrDateEnd['time'], 0, 2));
                        $arrDateEnd_m = intval(substr($arrDateEnd['time'], 2, 2));


                        $strMessage = $strtitle = '';
                        $_SESSION['ploopi']['tickets']['users_selected'] = array();


                        // Changement d'�tat (Validation, annulation...)
                        if (isset($_POST['_booking_event_validated'][$intIdEventDetail])) {

                            switch($_POST['_booking_event_validated'][$intIdEventDetail]) {
                                // Validation de la demande
                                case '1':
                                    $objEventDetail->fields['validated'] = 1;
                                    $objEventDetail->fields['canceled'] = 0;

                                    if ($objEventDetail->isvalid($objEvent)) {
                                        // envoyer un ticket, demande valid�e

                                        // On r�cup�re les utilisateurs gestionnaires de la ressource
                                        $arrUsers = $objResource->getusers();

                                        // Selection des destinataires du ticket
                                        foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                        if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                        $strMessage = "La demande de r�servation pour {$strResource}{$strSR} du {$arrDateBegin['date']} � ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} � ".substr($arrDateEnd['time'], 0, 5)." a �t� valid�e par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']}<br /><br />".ploopi\str::nl2br(ploopi\str::htmlentities($objEventDetail->fields['message']));
                                        $strTitle = "Validation de la demande de r�servation pour {$strResource}{$strSR}";
                                    }
                                    else $booError = true;
                                break;

                                // Annulation de la demande
                                case '0':
                                    $objEventDetail->fields['canceled'] = 1;
                                    $objEventDetail->fields['validated'] = 0;

                                    // envoyer un ticket, demande annul�e

                                    // On r�cup�re les utilisateurs gestionnaires de la ressource
                                    $arrUsers = $objResource->getusers();

                                    // Selection des destinataires du ticket
                                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                    $strMessage = "La demande de r�servation pour {$strResource}{$strSR} du {$arrDateBegin['date']} � ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} � ".substr($arrDateEnd['time'], 0, 5)." a �t� refus�e par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi\str::nl2br(ploopi\str::htmlentities($objEventDetail->fields['message']));
                                    $strTitle = "Refus de la demande de r�servation pour {$strResource}{$strSR}";
                                break;

                                // Suppression de la demande
                                case '9':

                                    // envoyer un ticket, demande annul�e

                                    // On r�cup�re les utilisateurs gestionnaires de la ressource
                                    $arrUsers = $objResource->getusers();

                                    // Selection des destinataires du ticket
                                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                                    $strMessage = "La demande de r�servation pour {$strResource}{$strSR} du {$arrDateBegin['date']} � ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} � ".substr($arrDateEnd['time'], 0, 5)." a �t� supprim�e par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi\str::nl2br(ploopi\str::htmlentities($objEventDetail->fields['message']));
                                    $strTitle = "Suppression de la demande de r�servation pour {$strResource}{$strSR}";
                                break;
                            }
                        }
                        // Modification "simple"
                        else {

                            if (!$objEventDetail->isvalid($objEvent)) $booError = true;
                            else {
                                $strMessage = "La demande de r�servation pour {$strResource}{$strSR} du {$arrDateBegin['date']} � ".substr($arrDateBegin['time'], 0, 5)." au {$arrDateEnd['date']} � ".substr($arrDateEnd['time'], 0, 5)." a �t� modifi�e par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']} pour le motif suivant : <br /><br />".ploopi\str::nl2br(ploopi\str::htmlentities($objEventDetail->fields['message']));
                                $strTitle = "Modification de la demande de r�servation pour {$strResource}{$strSR}";

                                // On r�cup�re les utilisateurs gestionnaires de la ressource
                                $arrUsers = $objResource->getusers();

                                // Selection des destinataires du ticket
                                foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                                if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];
                            }
                        }

                        if (!empty($strMessage))
                        {
                            // Envoi d'un ticket � l'initiateur de la demande
                            ploopi\ticket::send(
                                $strTitle,
                                $strMessage
                            );

                            // Envoi d'un mail aux autres personnes concern�es
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

                                    ploopi\mail::send($arrFrom, $arrTo, $strTitle, $strMessage);

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


            // On v�rifie si le traitement de la demand�e est termin�e en v�rifiant les d�tails
            ploopi\db::get()->query("
                SELECT  count(*) as c
                FROM    ploopi_mod_booking_event_detail
                WHERE   id_event = {$objEvent->fields['id']}
                AND     validated = 0
                AND     canceled = 0
            ");

            $objEvent->fields['managed'] = (($row = ploopi\db::get()->fetchrow()) && ($row['c'] == 0)) ? 1 : 0;

            $objEvent->save();

            if ($booGlobalError) {
                if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url'].'&error=collision2');
                else ploopi\output::redirect('admin.php?error=collision2');
            }

            if ($_SESSION['ploopi']['mode'] == 'frontoffice') ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
            else ploopi\output::redirect('admin.php');
        break;

        case 'booking_event_add':
            ob_start();
            ploopi\module::init('booking');

            global $arrBookingColor;
            global $arrBookingPeriodicity;

            // Cas particulier du mode frontoffice, on teste la pr�sence de moduleid
            if ($_SESSION['ploopi']['mode'] == 'frontoffice' && (empty($_GET['booking_moduleid']) || !is_numeric($_GET['booking_moduleid']) || !ploopi\acl::isactionallowed(_BOOKING_ACTION_ASKFOREVENT, $_SESSION['ploopi']['workspaceid'], $_GET['booking_moduleid']))) ploopi\system::kill();

            $strDate = empty($_GET['booking_resource_date']) ? '' : date('d/m/Y', $_GET['booking_resource_date']);

            include_once './modules/booking/classes/class_booking_event.php';

            $arrResources = ($_SESSION['ploopi']['mode'] == 'frontoffice') ? booking_get_resources(false, $_GET['booking_moduleid']) : booking_get_resources();

            if (empty($arrResources)) $arrResources = array(0);

            ploopi\db::get()->query("SELECT * FROM ploopi_mod_booking_subresource WHERE id_resource IN (".implode(',', array_keys($arrResources)).") AND active = 1 ORDER BY name");
            ?>
            <script type="text/javascript">
                booking_json_sr = <?php echo json_encode(ploopi\arr::map('ploopi\str::utf8encode', ploopi\db::get()->getarray())); ?>;
            </script>
            <?php

            $objEvent = new booking_event();
            $objEvent->init_description();
            // Si une ressource est pass�e en param�tre, on la s�lectionne par d�faut
            if (!empty($_GET['booking_resource_id']) && is_numeric($_GET['booking_resource_id'])) $objEvent->fields['id_resource'] = $_GET['booking_resource_id'];



            ?>
            <form action="<?php echo ploopi\crypt::urlencode($_SESSION['ploopi']['mode'] == 'frontoffice' ? "index-light.php?ploopi_op=booking_event_save&booking_event_id={$objEvent->fields['id']}&booking_moduleid={$_GET['booking_moduleid']}" : "admin-light.php?ploopi_op=booking_event_save&booking_event_id={$objEvent->fields['id']}"); ?>" method="post" onsubmit="javascript:return booking_event_validate(this);">
            <div class=ploopi_form>
                <p>
                    <label>Ressource:</label>
                    <select class="select" name="booking_event_id_resource" onchange="javascript:booking_resource_onchange(this);">
                        <option value="">(choisir)</option>
                        <?php
                        $strResourceType = '';
                        foreach ($arrResources as $row)
                        {
                            if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage s�parateur
                            {
                                if ($strResourceType != '') echo '</optgroup>';
                                $strResourceType = $row['rt_name'];
                                ?>
                                <optgroup label="<?php echo ploopi\str::htmlentities($row['rt_name']); ?>">
                                <?php
                            }
                            ?>
                            <option value="<?php echo $row['id']; ?>" style="border-left:2px;" <?php if ($objEvent->fields['id_resource'] == $row['id']) echo 'selected="selected"'; ?>><?php echo ploopi\str::htmlentities($row['name']); ?></option>
                            <?php
                        }
                        if ($strResourceType != '') echo '</optgroup>';
                        ?>
                    </select>
                </p>
                <div style="padding:0;" id="booking_subresources"></div>
                <p>
                    <label>Objet:</label>
                    <input name="booking_event_object" type="text" class="text" value="<?php echo ploopi\str::htmlentities($objEvent->fields['object']); ?>">
                </p>
                <p>
                    <label>Date/heure de d�but:</label>
                    <input name="_booking_event_timestp_begin_d" id="_booking_event_timestp_begin_d" class="text" type="text" value="<?php echo ploopi\str::htmlentities($strDate); ?>" style="width:80px;" onchange="javascript:$('_booking_event_timestp_end_d').value = this.value;" />
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi\date::open_calendar('_booking_event_timestp_begin_d'); ?></span>
                    <select name="_booking_event_timestp_begin_h" id="_booking_event_timestp_begin_h" class="select" style="width:60px;">
                    <?php
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option value="<?php echo $i; ?>"><?php echo sprintf("%02d h", $i); ?></option><?php
                    }
                    ?>
                    </select>

                    <select name="_booking_event_timestp_begin_m" id="_booking_event_timestp_begin_m" class="select" style="width:45px;">
                    <?php
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option value="<?php echo $i*5; ?>"><?php echo sprintf("%02d", $i*5); ?></option><?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>Date/heure de fin:</label>
                    <input name="_booking_event_timestp_end_d" id="_booking_event_timestp_end_d" class="text" type="text" value="<?php echo ploopi\str::htmlentities($strDate); ?>" style="width:80px; "/>
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi\date::open_calendar('_booking_event_timestp_end_d'); ?></span>
                    <select name="_booking_event_timestp_end_h" id="_booking_event_timestp_end_h" class="select" style="width:60px;">
                    <?php
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option value="<?php echo $i; ?>"><?php echo sprintf("%02d h", $i); ?></option><?php
                    }
                    ?>
                    </select>
                    <select name="_booking_event_timestp_end_m" id="_booking_event_timestp_end_h" class="select" style="width:45px;">
                    <?php
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option value="<?php echo $i*5; ?>"><?php echo sprintf("%02d", $i*5); ?></option><?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>P�riodicit�:</label>
                    <select name="booking_event_periodicity" class="select" style="width:100px;">
                        <option value="">(Aucune)</option>
                        <?php
                        foreach ($arrBookingPeriodicity as $key => $value)
                        {
                            ?>
                            <option value="<?php echo ploopi\str::htmlentities($key); ?>"><?php echo ploopi\str::htmlentities($value); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <em style="float:left;">&nbsp;&nbsp;jusqu'au:&nbsp;&nbsp;</em>
                    <input name="_booking_event_periodicity_end_date" id="_booking_event_periodicity_end_date" class="text" type="text" value="" style="width:80px; "/>
                    <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi\date::open_calendar('_booking_event_periodicity_end_date'); ?></span>
                </p>
                <p>
                    <label>Commentaire:</label>
                    <textarea style="height:60px;" class="text" name="_booking_event_message" id="_booking_event_message" placeholder="Indiquez �ventuellement les autres ressources n�cessaires"></textarea>
                </p>
                <p>
                    <label>Destinataires compl�mentaires (adresses de courriel s�par�es par &laquo; , &raquo;:</label>
                    <textarea style="height:60px;" class="text" name="_booking_event_emails" id="_booking_event_emails" placeholder="Indiquez �ventuellement les adresses de courriel des personnes � avertir lors de la validation"></textarea>
                </p>
            </div>
            <div style="padding:4px;text-align:right;">
                <input type="reset" class="button" value="R�initialiser" />
                <input type="submit" class="button" value="Enregistrer" />
            </div>
            </form>
            <?php
            $content = ob_get_contents();
            ob_end_clean();

            include_once './modules/booking/include/global.php';

            echo ploopi\skin::get()->create_popup("Ajout d'une demande de r�servation", $content, 'popup_event');
            ploopi\system::kill();
        break;


        /**
         * Permet de d�verrouiller un �v�nement d�j� trait�
         */
        case 'booking_event_unlock':
            ploopi\module::init('booking', false, false, false);

            include_once './modules/booking/classes/class_booking_event.php';

            $objEvent = new booking_event();

            if (!empty($_GET['booking_element_id']))
            {
                $arrId = explode(',', $_GET['booking_element_id']);

                if ($objEvent->open($arrId[0]))
                {
                    $objEvent->fields['managed'] = 0;
                    $objEvent->save();

                    ploopi\output::redirect("admin.php?ploopi_op=booking_event_open&booking_element_id={$_GET['booking_element_id']}");
                }
            }

            ploopi\system::kill();
        break;

        case 'booking_event_detail_cancel':
            ploopi\module::init('booking', false, false, false);

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
            ploopi\output::redirect("admin.php");
        break;


        case 'booking_event_detail_delete':
            ploopi\module::init('booking', false, false, false);

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


                    $rowDetailsB = ploopi\date::timestamp2local($objEventDetail->fields['timestp_begin']);
                    $rowDetailsE = ploopi\date::timestamp2local($objEventDetail->fields['timestp_end']);

                    $strBegin = $rowDetailsB['date'].' � '.sprintf("%02dh%02d", substr($rowDetailsB['time'], 0, 2), substr($rowDetailsB['time'], 3, 2));
                    $strEnd = $rowDetailsE['date'].' � '.sprintf("%02dh%02d", substr($rowDetailsE['time'], 0, 2), substr($rowDetailsE['time'], 3, 2));

                    $arrSR = array();
                    foreach($objEvent->getsubresources() as $intIdSR) {
                        $objSR = new booking_subresource();
                        if ($objSR->open($intIdSR)) $arrSR[] = $objSR->fields['name'];
                    }

                    $strSR = empty($arrSR) ? '' : ' (incluant: '.implode(', ', $arrSR).')';

                    $_SESSION['ploopi']['tickets']['users_selected'] = array();

                    // On r�cup�re les utilisateurs gestionnaires de la ressource
                    $arrUsers = $objResource->getusers();

                    // Selection des destinataires du ticket
                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                    if (!in_array($objEvent->fields['id_user'], $_SESSION['ploopi']['tickets']['users_selected'])) $_SESSION['ploopi']['tickets']['users_selected'][] = $objEvent->fields['id_user'];

                    $strMessage = "La demande de r�servation pour {$strResource}{$strSR} du {$strBegin} au {$strEnd} a �t� supprim�e par {$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']}";
                    $strTitle = "Suppression de la demande de r�servation pour {$strResource}{$strSR}";

                    // Envoi d'un ticket � l'initiateur de la demande
                    ploopi\ticket::send(
                        $strTitle,
                        $strMessage
                    );

                    $objEventDetail->delete();
                }
            }
            if ($_SESSION['ploopi']['mode'] == 'backoffice') ploopi\output::redirect('admin.php');
            else ploopi\output::redirect($_SESSION['booking'][$_GET['booking_moduleid']]['article_url']);
        break;
    }
}

switch($ploopi_op)
{
    case 'booking_event_open':
        ob_start();
        ploopi\module::init('booking');

        global $arrBookingColor;

        include_once './modules/booking/classes/class_booking_resource.php';
        include_once './modules/booking/classes/class_booking_resource_workspace.php';
        include_once './modules/booking/classes/class_booking_event.php';
        $objEvent = new booking_event();

        if (!empty($_GET['booking_element_id']))
        {
            // $_GET['booking_element_id'] contient l'id de l'�v�nement ou l'id de l'�v�nement et l'id du d�tail
            // $arrId[0] = event_id
            // $arrId[1] = event_detail_id (option)
            $arrId = explode(',', $_GET['booking_element_id']);

            $booking_moduleid = ($_SESSION['ploopi']['mode'] == 'backoffice') ? $_SESSION['ploopi']['moduleid'] : $_GET['booking_moduleid'];

            if ($objEvent->open($arrId[0]))
            {
                $objUser = new ploopi\user();
                $objWorkspace = new ploopi\workspace();
                $objResource = new booking_resource();
                $objResourceWorkspace = new booking_resource_workspace();

                if ($objWorkspace->open($objEvent->fields['id_workspace'])) $strWorkspace = $objWorkspace->fields['label'];
                else $strWorkspace = "inconnu";

                if ($objUser->open($objEvent->fields['id_user'])) $strUser = "{$objUser->fields['lastname']} {$objUser->fields['firstname']}";
                else $strUser = 'inconnu';

                if ($objResource->open($objEvent->fields['id_resource'])) $strResource = $objResource->fields['name'].(empty($objResource->fields['reference']) ? '' : " ({$objResource->fields['reference']})");
                else $strResource = 'inconnue';

                // Validateur ?
                $booValidator = ($_SESSION['ploopi']['mode'] == 'backoffice') && ploopi\acl::isactionallowed(_BOOKING_ACTION_VALIDATE) && $objResourceWorkspace->open($objEvent->fields['id_resource'], $_SESSION['ploopi']['workspaceid']);

                // Modification possible si "traitement non termin�" (managed=0) et "backoffice"
                // Penser � g�rer �galement les droits de modif
                $booModify = ($objEvent->fields['managed'] == 0 && $booValidator);

                // R�cup�ration des d�tails de l'�v�nement
                $arrDetails = $objEvent->getdetails();

                // True si au moins un �v�nement modifiable
                $booModifyEventGlobal = $booModify;

                // On boucle sur l'affichage des d�tails d'�v�nement
                // Pour savoir si au moins un �l�ment est modifiable
                foreach($arrDetails as $detail)
                {
                    $booValidate = ($objEvent->fields['managed'] == 0 && $booValidator && $detail['canceled'] == 0 && $detail['validated'] == 0);
                    $booModifyEvent = $booValidate || ($objEvent->fields['managed'] == 0 && $detail['canceled'] == 0 && $detail['validated'] == 0 && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']);
                    $booModifyEventGlobal = $booModifyEventGlobal || $booModifyEvent;
                }

                $strUrl = $_SESSION['ploopi']['mode'] == 'frontoffice' ? ploopi\crypt::urlencode("index-light.php?ploopi_op=booking_event_validate&booking_event_id={$objEvent->fields['id']}&booking_moduleid={$_GET['booking_moduleid']}") : ploopi\crypt::urlencode("admin-light.php?ploopi_op=booking_event_validate&booking_event_id={$objEvent->fields['id']}");

                if ($booModify) $arrResources = booking_get_resources();
                else $arrResources = array($objEvent->fields['id_resource'] => 1);

                if (!empty($arrResources)) {
                    ploopi\db::get()->query("SELECT * FROM ploopi_mod_booking_subresource WHERE id_resource IN (".implode(',', array_keys($arrResources)).") AND active = 1 ORDER BY name");
                    ?>
                    <script type="text/javascript">
                        booking_json_sr = <?php echo json_encode(ploopi\arr::map('ploopi\str::utf8encode', ploopi\db::get()->getarray())); ?>;
                    </script>
                    <?php
                }
                ?>

                <?php if ($booModifyEventGlobal) { ?><form action="<?php echo $strUrl; ?>" method="post"><?php } ?>

                <div class=ploopi_form>
                    <p>
                        <label>Ressource:</label>
                        <?php
                        if ($booModify)
                        {
                            ?>
                            <select class="select" name="booking_event_id_resource" id="booking_event_id_resource" onchange="javascript:booking_resource_onchange(this);">
                                <option value="">(choisir)</option>
                                <?php
                                $strResourceType = '';
                                foreach ($arrResources as $row)
                                {
                                    if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage s�parateur
                                    {
                                        if ($strResourceType != '') echo '</optgroup>';
                                        $strResourceType = $row['rt_name'];
                                        ?>
                                        <optgroup label="<?php echo ploopi\str::htmlentities($row['rt_name']); ?>">
                                        <?php
                                    }
                                    ?>
                                    <option value="<?php echo $row['id']; ?>" style="border-left:2px;" <?php if ($objEvent->fields['id_resource'] == $row['id']) echo 'selected="selected"'; ?>><?php echo ploopi\str::htmlentities($row['name']); ?></option>
                                    <?php
                                }
                                if ($strResourceType != '') echo '</optgroup>';
                                ?>
                            </select>
                            <?php
                        }
                        else
                        {
                            ?>
                            <span><?php echo ploopi\str::htmlentities($strResource); ?></span>
                            <?php
                        }
                        ?>
                    </p>
                    <div style="padding:0;" id="booking_subresources"></div>
                    <?php
                    if ($booModify)
                    {
                        ?>
                        <script type="text/javascript">
                            booking_resource_onchange($('booking_event_id_resource')[$('booking_event_id_resource').selectedIndex]);
                            <?php
                            foreach($objEvent->getsubresources() as $intIdSR) {
                                ?>
                                $('booking_sr_<?php echo $intIdSR; ?>').checked = true;
                                <?php
                            }
                            ?>
                        </script>
                        <?php
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
                                    if (row.id_resource == <?php echo intval($objEvent->fields['id_resource']); ?>) {
                                        console.log(row);
                                        $('booking_subresources_ct').insert(
                                            '<span>'+row.name+'</span>'
                                        );
                                    }
                                });
                                <?php
                                foreach($objEvent->getsubresources() as $intIdSR) {
                                    ?>
                                    $('booking_sr_<?php echo $intIdSR; ?>').checked = true;
                                    <?php
                                }
                                ?>
                            </script>
                            <?php
                        }
                    }
                    ?>
                    <p>
                        <label>Objet:</label>
                        <?php
                        if ($booModify) { ?><input name="booking_event_object" type="text" class="text" value="<?php echo ploopi\str::htmlentities($objEvent->fields['object']); ?>"><?php }
                        else echo '<span>'.ploopi\str::htmlentities($objEvent->fields['object']).'</span>';
                        ?>
                    </p>
                    <p>
                        <label>Demandeur:</label>
                        <span><?php echo ploopi\str::htmlentities("{$strUser} ({$strWorkspace})"); ?></span>
                    </p>

                    <?php
                    if (!empty($objEvent->fields['periodicity']) && !empty($arrBookingPeriodicity[$objEvent->fields['periodicity']]))
                    {
                        ?>
                        <p>
                            <label>P�riodicit�:</label>
                            <span><?php echo ploopi\str::htmlentities($arrBookingPeriodicity[$objEvent->fields['periodicity']]); ?></span>
                        </p>
                        <?php
                    }
                    ?>
                    <div style="background-color:#c0c0c0;margin:1px;padding:4px;font-weight:bold;">D�tail des �v�nements associ�s � cette demande:</div>
                        <div>
                        <?php
                        // True si au moins un �v�nement modifiable
                        $booModifyEventGlobal = $booModify;
                        /* <div <?php if (sizeof($arrDetails) >2) {?>style="height:300px;overflow:auto;<?php } ?>"> */

                        // On boucle sur l'affichage des d�tail d'�v�nement
                        foreach($arrDetails as $detail)
                        {
                            // On n'affiche que si valid� ou validateur connect� ou propri�taire (ou option de filtrage d�sactiv�e)
                            if ($detail['validated'] || $booValidator  || ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']) || !$_SESSION['ploopi']['modules'][$booking_moduleid]['booking_eventfilter'])
                            {
                                //$booModify = ($objEvent->fields['managed'] == 0 && $_SESSION['ploopi']['mode'] == 'backoffice' && (!isset($arrId[1]) || (isset($arrId[1]) && $arrId[1] == $detail['id'])));
                                //$booModify = ($objEvent->fields['managed'] == 0 && $_SESSION['ploopi']['mode'] == 'backoffice' && $detail['canceled'] == 0 && $detail['validated'] == 0 && (!isset($arrId[1]) || (isset($arrId[1]) && $arrId[1] == $detail['id'])));
                                $booValidate = ($objEvent->fields['managed'] == 0 && $booValidator && $detail['canceled'] == 0 && $detail['validated'] == 0);
                                $booModifyEvent = $booValidate || ($objEvent->fields['managed'] == 0 && $detail['canceled'] == 0 && $detail['validated'] == 0 && $_SESSION['ploopi']['userid'] == $objEvent->fields['id_user']);
                                $booModifyEventGlobal = $booModifyEventGlobal || $booModifyEvent;


                                // Date de d�but/fin au format local
                                $arrDateBegin = ploopi\date::timestamp2local($detail['timestp_begin']);
                                $arrDateEnd = ploopi\date::timestamp2local($detail['timestp_end']);

                                // Extraction heures/minutes
                                $arrDateBegin_h = intval(substr($arrDateBegin['time'], 0, 2), 10);
                                $arrDateBegin_m = intval(substr($arrDateBegin['time'], 3, 2), 10);
                                $arrDateEnd_h = intval(substr($arrDateEnd['time'], 0, 2), 10);
                                $arrDateEnd_m = intval(substr($arrDateEnd['time'], 3, 2), 10);

                                // D�termination de la couleur de fond
                                $strBgcolor = 'background-color:'.($detail['validated'] ? $arrBookingColor['validated'] : ($detail['canceled'] ? $arrBookingColor['canceled'] : $arrBookingColor['unknown'])).';';

                                // Si un d�tail doit �tre s�lectionn�
                                $strBorderColor = isset($arrId[1]) && $arrId[1] == $detail['id'] ? 'border:4px solid #8888ff' : 'border:1px solid #c0c0c0';
                                ?>
                                <div style="<?php echo $strBorderColor; ?>;margin:1px;<?php echo $strBgcolor; ?>" id="booking_event_bg<?php echo $detail['id']; ?>">
                                    <p>
                                        <label>Date/heure de d�but:</label>
                                        <?php
                                        if ($booModifyEvent)
                                        {
                                            ?>
                                            <input name="_booking_event_timestp_begin_d[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_begin_d<?php echo $detail['id']; ?>" class="text" type="text" value="<?php echo ploopi\str::htmlentities($arrDateBegin['date']); ?>" style="width:80px;" onchange="javascript:if ($('_booking_event_timestp_end_d<?php echo $detail['id']; ?>').value == '') $('_booking_event_timestp_end_d<?php echo $detail['id']; ?>').value = this.value;" />
                                            <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi\date::open_calendar("_booking_event_timestp_begin_d{$detail['id']}"); ?></span>
                                            <select name="_booking_event_timestp_begin_h[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_begin_h<?php echo $detail['id']; ?>" class="select" style="width:60px;">
                                            <?php
                                            for ($i = 0; $i < 24; $i++)
                                            {
                                                ?><option value="<?php echo $i; ?>" <?php if ($arrDateBegin_h == $i) echo 'selected="selected"'; ?>><?php echo sprintf("%02d h", $i); ?></option><?php
                                            }
                                            ?>
                                            </select>
                                            <select name="_booking_event_timestp_begin_m[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_begin_m<?php echo $detail['id']; ?>" class="select" style="width:45px;">
                                            <?php
                                            for ($i = 0; $i < 12; $i++)
                                            {
                                                ?><option value="<?php echo $i*5; ?>" <?php if ($arrDateBegin_m == $i*5) echo 'selected="selected"'; ?>><?php echo sprintf("%02d", $i*5); ?></option><?php
                                            }
                                            ?>
                                            </select>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <span>
                                                <?php
                                                echo ploopi\str::htmlentities($arrDateBegin['date']).' '.substr($arrDateBegin['time'], 0, 5);

                                                echo '<strong style="margin-left:10px;">'.($detail['validated'] ? 'Valid�' : ($detail['canceled'] ? 'Refus�' : 'Indetermin�')).'</strong>';

                                                // Peuvent supprimer :
                                                // Propri�taire si non valid�/refus�
                                                // Gestionnaire si valid�/refus�
                                                // Et si non v�rouill�

                                                if ($objEvent->fields['managed'] == 0 && (($_SESSION['ploopi']['userid'] == $objEvent->fields['id_user'] && $detail['validated'] == 0 && $detail['canceled'] == 0)))
                                                {
                                                    $strUrl = ploopi\crypt::urlencode($_SESSION['ploopi']['mode'] == 'backoffice' ? "admin-light.php?ploopi_op=booking_event_detail_delete&booking_event_detail_id={$detail['id']}" : "index-light.php?ploopi_op=booking_event_detail_delete&booking_moduleid={$_GET['booking_moduleid']}&booking_event_detail_id={$detail['id']}");
                                                    ?>
                                                    <strong style="margin-left:10px;">(<a href="javascript:void(0);" onclick="javascript:if (confirm('Attention cette action va supprimer d�finitivement la demande de r�servation.\nVoulez vous continuer ?')) document.location.href = '<?php echo $strUrl; ?>';" style="color:#a60000;" title="Supprimer cette r�servation">Supprimer</a>)</strong>
                                                    <?php
                                                }

                                                // Peuvent annuler :
                                                // Gestionnaire si valid�/refus�
                                                // Et si non v�rouill�

                                                if ($objEvent->fields['managed'] == 0  && $booValidator && ($detail['validated'] == 1 || $detail['canceled'] == 1))
                                                {
                                                    ?>
                                                    <strong style="margin-left:10px;">(<a href="javascript:void(0);" onclick="javascript:if (confirm('Attention cette action va annuler cette validation.\nVoulez vous continuer ?')) document.location.href = '<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=booking_event_detail_cancel&booking_event_detail_id={$detail['id']}"); ?>';" style="color:#a60000;" title="Annuler cette validation">Annuler</a>)</strong>
                                                    <?php
                                                }

                                                ?>
                                            </span>
                                            <?php
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <label>Date/heure de fin:</label>
                                        <?php
                                        if ($booModifyEvent)
                                        {
                                            ?>
                                            <input name="_booking_event_timestp_end_d[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_end_d<?php echo $detail['id']; ?>" class="text" type="text" value="<?php echo ploopi\str::htmlentities($arrDateEnd['date']); ?>" style="width:80px; "/>
                                            <span style="float:left;width:auto;margin:0;padding:1px;"><?php ploopi\date::open_calendar("_booking_event_timestp_end_d{$detail['id']}"); ?></span>
                                            <select name="_booking_event_timestp_end_h[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_end_h<?php echo $detail['id']; ?>" class="select" style="width:60px;">
                                            <?php
                                            for ($i = 0; $i < 24; $i++)
                                            {
                                                ?><option value="<?php echo $i; ?>" <?php if ($arrDateEnd_h == $i) echo 'selected="selected"'; ?>><?php echo sprintf("%02d h", $i); ?></option><?php
                                            }
                                            ?>
                                            </select>
                                            <select name="_booking_event_timestp_end_m[<?php echo $detail['id']; ?>]" id="_booking_event_timestp_end_h<?php echo $detail['id']; ?>" class="select" style="width:45px;">
                                            <?php
                                            for ($i = 0; $i < 12; $i++)
                                            {
                                                ?><option value="<?php echo $i*5; ?>" <?php if ($arrDateEnd_m == $i*5) echo 'selected="selected"'; ?>><?php echo sprintf("%02d", $i*5); ?></option><?php
                                            }
                                            ?>
                                            </select>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <span><?php echo $arrDateEnd['date']; ?>&nbsp;<?php echo substr($arrDateEnd['time'], 0, 5); ?></span>
                                            <?php
                                        }
                                        ?>
                                    </p>

                                    <?php
                                    if ($booValidate)
                                    {
                                        ?>
                                        <div style="font-weight:bold;padding-left:10px;" class="ploopi_va">
                                            <strong>D�cision :</strong>
                                            <?php
                                            // calcul de la diff�rence entre le moment actuel et la date de fin mission
                                            // si mission est pass�e, on ne peut plus la supprimer
                                            $diff=time()-ploopi\date::timestamp2unixtimestamp($detail['timestp_end']);
                                            if (($diff/24/3600)+1 < 1) {
                                                ?>
                                                <input type="radio" class="checkbox" name="_booking_event_validated[<?php echo $detail['id']; ?>]" id="_booking_event_validated<?php echo $detail['id']; ?>_9" value="9" onchange="javascript:$('booking_event_bg<?php echo $detail['id']; ?>').style.backgroundColor = '<?php echo $arrBookingColor['deleted']; ?>';" />
                                                <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<?php echo $detail['id']; ?>_9');">Supprimer</a>
                                                <?php
                                            }
                                            ?>
                                            <input type="radio" class="checkbox" name="_booking_event_validated[<?php echo $detail['id']; ?>]" id="_booking_event_validated<?php echo $detail['id']; ?>_0" value="0" onchange="javascript:$('booking_event_bg<?php echo $detail['id']; ?>').style.backgroundColor = '<?php echo $arrBookingColor['canceled']; ?>';" <?php if ($detail['canceled']) echo 'checked="checked"'; ?> />
                                            <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<?php echo $detail['id']; ?>_0');">Refuser</a>

                                            <input type="radio" class="checkbox" name="_booking_event_validated[<?php echo $detail['id']; ?>]" id="_booking_event_validated<?php echo $detail['id']; ?>_1" value="1" onchange="javascript:$('booking_event_bg<?php echo $detail['id']; ?>').style.backgroundColor = '<?php echo $arrBookingColor['validated']; ?>';" <?php if ($detail['validated']) echo 'checked="checked"'; ?> />
                                            <a href="javascript:void(0);" onclick="javascript:ploopi_checkbox_click(event, '_booking_event_validated<?php echo $detail['id']; ?>_1');">Valider</a>
                                        </div>
                                        <?php
                                    }

                                    // seuls les validateurs et propri�taires peuvent voire "commentaire" et "emails"
                                    ?>
                                    <p>
                                        <label>Commentaire:</label>
                                        <?php
                                        if ($booModifyEvent) { ?><textarea style="height:60px;" class="text" name="_booking_event_message[<?php echo $detail['id']; ?>]" id="_booking_event_message<?php echo $detail['id']; ?>"><?php echo ploopi\str::htmlentities($detail['message']); ?></textarea><?php }
                                        else echo '<span>'.ploopi\str::nl2br(ploopi\str::htmlentities($detail['message'])).'</span>';
                                        ?>
                                    </p>
                                    <p>
                                        <label>Destinataires compl�mentaires (adresses de courriel s�par�es par &laquo; , &raquo;:</label>
                                        <?php
                                        if ($booModifyEvent) { ?><textarea style="height:60px;" class="text" name="_booking_event_emails[<?php echo $detail['id']; ?>]" id="_booking_event_emails<?php echo $detail['id']; ?>"><?php echo ploopi\str::htmlentities($detail['emails']); ?></textarea><?php }
                                        else echo '<span>'.ploopi\str::nl2br(ploopi\str::htmlentities($detail['emails'])).'</span>';
                                        ?>
                                    </p>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div style="padding:4px;text-align:right;">
                    <?php
                    // Enregistrement et/ou validation
                    if ($booModifyEventGlobal) {
                        if ($objEvent->fields['managed'] == 0 && (($_SESSION['ploopi']['userid'] == $objEvent->fields['id_user'] && $detail['validated'] == 0 && $detail['canceled'] == 0)))
                        {
                            $strUrl = ploopi\crypt::urlencode($_SESSION['ploopi']['mode'] == 'backoffice' ? "admin-light.php?ploopi_op=booking_event_detail_delete&booking_event_detail_id={$detail['id']}" : "index-light.php?ploopi_op=booking_event_detail_delete&booking_moduleid={$_GET['booking_moduleid']}&booking_event_detail_id={$detail['id']}");
                            ?>
                                <input type="button" class="button" value="Supprimer" title="Supprimer cette r�servation" style="color:#a60000;font-weight:bold;" onclick="javascript:if (confirm('Attention cette action va supprimer d�finitivement la demande de r�servation.\nVoulez vous continuer ?')) document.location.href = '<?php echo $strUrl; ?>';" />
                            <?php
                        }
                        ?>
                        <input type="reset" class="button" value="R�initialiser" />
                        <input type="submit" class="button" value="Enregistrer" />
                        <?php
                    }
                    // D�verrouillage
                    elseif ($objEvent->fields['managed'] == 1 && ploopi\acl::isactionallowed(_BOOKING_ACTION_VALIDATE)) {

                        if ($_SESSION['ploopi']['mode'] == 'frontoffice') {
                            $strUrl = "ploopi_xmlhttprequest_todiv('index-light.php','".ploopi\crypt::queryencode("ploopi_op=booking_event_unlock&booking_element_id={$_GET['booking_element_id']}&booking_moduleid={$_GET['booking_moduleid']}")."', 'popup_event');";
                        } else {
                            $strUrl = "ploopi_xmlhttprequest_todiv('admin-light.php','".ploopi\crypt::queryencode("ploopi_op=booking_event_unlock&booking_element_id={$_GET['booking_element_id']}")."', 'popup_event');";
                        }

                        ?>
                        <div style="margin-bottom:2px;"><strong style="color:#a60000;">Cette demande est verrouill�e car son traitement est termin�</strong></div>
                        <div>
                            <input type="button" class="button" value="Deverrouiller" onclick="javascript:<?php echo $strUrl; ?>"/>
                            <input type="button" class="button" value="Fermer" onclick="javascript:ploopi_hidepopup('popup_event');"/>
                        </div>
                        <?php
                    }
                    // Tout autre cas
                    else
                    {
                        ?>
                        <div>
                            <input type="button" class="button" value="Fermer" onclick="javascript:ploopi_hidepopup('popup_event');"/>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php if ($booModify) { ?></form><?php } ?>

                <?php
            }
            else
            {
                ?>
                <div style="padding:4px;" class="error">Cette demande n'existe pas</div>
                <?php
            }
            $content = ob_get_contents();
            ob_end_clean();

            include_once './modules/booking/include/global.php';

            echo ploopi\skin::get()->create_popup("Consultation d'une demande de r�servation", $content, 'popup_event');
        }
        ploopi\system::kill();
    break;
}

?>
