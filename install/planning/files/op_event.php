<?php
/*
    Copyright (c) 2009 Ovensia
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
 * @package planning
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

switch($ploopi_op)
{
    case 'planning_event_detail_delete':
        include_once './modules/planning/classes/class_planning_event_detail.php';

        $objEventDetail = new planning_event_detail();
        if (!empty($_GET['planning_event_detail_id']) && is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
        {
            $objEventDetail->delete();
        }

        ploopi_redirect('admin-light.php?ploopi_op=planning_refresh');
    break;

    case 'planning_event_save':
        ploopi_init_module('planning', false, false, false);

        include_once './modules/planning/classes/class_planning_event.php';
        include_once './modules/planning/classes/class_planning_event_detail.php';

        if (!empty($_GET['planning_event_detail_id'])) // mise � jour
        {
            $objEventDetail = new planning_event_detail();

            if (is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
            {
                $objEvent = new planning_event();

                if ($objEvent->open($objEventDetail->fields['id_event']))
                {
                    $objEvent->setvalues($_POST, 'planning_event_');
                    $objEvent->save();

                    $objEventDetail->setresources($_POST['_planning_eventresource_id']);
                    $objEventDetail->fields['timestp_begin'] = ploopi_local2timestamp($_POST['_planning_event_timestp_begin_d'], sprintf("%02d:%02d:00", $_POST['_planning_event_timestp_begin_h'], $_POST['_planning_event_timestp_begin_m']));
                    $objEventDetail->fields['timestp_end'] = ploopi_local2timestamp($_POST['_planning_event_timestp_begin_d'], sprintf("%02d:%02d:00", $_POST['_planning_event_timestp_end_h'], $_POST['_planning_event_timestp_end_m']));
                    $objEventDetail->save();
                }
            }
        }
        else  // cr�ation
        {
            if (!empty($_POST['_planning_event_timestp_begin_d']) && !empty($_POST['planning_event_object']) && !empty($_POST['_planning_eventresource_id']))
            {
                $objEvent = new planning_event();

                $objEvent->setvalues($_POST, 'planning_event_');
                $objEvent->setdetails($_POST, '_planning_event_');
                $objEvent->setresources($_POST['_planning_eventresource_id']);

                $objEvent->setuwm();
                $objEvent->save();

                /**
                 * Envoyer un ticket aux gestionnaires de la ressource
                 * (les utilisateurs des espaces gestionnaires qui disposent de l'action de validation ou qui sont admin sys)
                 */

                /*
                $arrUsers = array();

                $objResource = new planning_resource();
                if ($objResource->open($objEvent->fields['id_resource']))
                {
                    // On r�cup�re les espaces gestionnaires de la ressource
                    $arrWorkspaces = $objResource->getworkspaces();

                    foreach($arrWorkspaces as $intIdWsp)
                    {
                        $objWorkspace = new workspace();
                        if ($objWorkspace->open($intIdWsp))
                        {
                            // On r�cup�re les utilisateurs des espaces gestionnaires
                            foreach($objWorkspace->getusers(true) as $arrUser)
                            {
                                if (!isset($arrUsers[$arrUser['id']])) // Utilisateur non s�lectionn�
                                {
                                    // S'il s'agit d'un administrateyr syst�me, on le s�lectionne automatiquement
                                    if ($arrUser['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                                    {
                                        $arrUsers[$arrUser['id']] = $arrUser;
                                    }
                                    else
                                    {
                                        $objUser = new user();

                                        if ($objUser->open($arrUser['id']))
                                        {
                                            // S'il n'est pas administrateur syst�me, on v�rifie les actions dont il dispose
                                            $arrActions = $objUser->getactions(null, true);

                                            // Si l'utilisateur dispose de l'action de validation sur le module planning dans l'espace gestionnaire
                                            if (isset($arrActions[$intIdWsp][$objEvent->fields['id_module']][_BOOKING_ACTION_VALIDATE]))
                                            {
                                                $arrUsers[$arrUser['id']] = $arrUser;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $objEvent->save();

                    // Selection des destinataires du ticket
                    foreach(array_keys($arrUsers) as $intIdUser) $_SESSION['ploopi']['tickets']['users_selected'][] = $intIdUser;

                    $strResource = $objResource->open($objEvent->fields['id_resource']) ? $objResource->fields['name'] : 'inconnu';

                    $strMessage = "Nouvelle demande de r�servation pour {$strResource} pour le motif suivant : <br /><br />".ploopi_nl2br(htmlentities($_POST['planning_event_object']));
                    $strTitle = "Nouvelle demande de r�servation pour {$strResource} ";

                    // Envoi du ticket
                    ploopi_tickets_send($strTitle, $strMessage);
                }
                */
            }
        }

        ploopi_redirect('admin-light.php?ploopi_op=planning_refresh');
    break;


    case 'planning_event_detail_quicksave';
        ploopi_init_module('planning', false, false, false);

        include_once './modules/planning/classes/class_planning_event_detail.php';

        if (!empty($_GET['planning_event_detail_id'])) // mise � jour
        {
            $objEventDetail = new planning_event_detail();

            if (is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
            {
                $arrDateEnd = ploopi_gettimestampdetail($objEventDetail->fields['timestp_end']);
                $arrDateBegin = ploopi_gettimestampdetail($objEventDetail->fields['timestp_begin']);

                $intDuration =  ($arrDateEnd[_PLOOPI_DATE_HOUR] + $arrDateEnd[_PLOOPI_DATE_MINUTE]/60) - ($arrDateBegin[_PLOOPI_DATE_HOUR] + $arrDateBegin[_PLOOPI_DATE_MINUTE]/60);
                $intHourEnd = $_GET['calendar_event_hour'] + $intDuration;

                $objEventDetail->fields['timestp_begin'] = sprintf("%08d%02d%02d00", $_GET['calendar_event_date'], floor($_GET['calendar_event_hour']), (ceil($_GET['calendar_event_hour'])-$_GET['calendar_event_hour']) * 60);
                $objEventDetail->fields['timestp_end'] = sprintf("%08d%02d%02d00", $_GET['calendar_event_date'], floor($intHourEnd), (ceil($intHourEnd)-$intHourEnd) * 60);

                $objEventDetail->save();
            }
        }

        ploopi_redirect('admin-light.php?ploopi_op=planning_refresh');
    break;

    case 'planning_event_detail_open':
    case 'planning_event_add':
        ob_start();

        ploopi_init_module('planning', false, false, false);

        include_once './modules/planning/classes/class_planning_event.php';
        include_once './modules/planning/classes/class_planning_event_detail.php';


        $arrResources = ($_SESSION['ploopi']['mode'] == 'frontoffice') ? planning_get_resources(false, $_GET['planning_moduleid']) : planning_get_resources();

        $objEvent = new planning_event();
        $objEventDetail = new planning_event_detail();

        $arrParams = array();
        $arrParams[] = "ploopi_op=planning_event_save";

        switch($ploopi_op)
        {
            case 'planning_event_add':
                // Si une ressource est pass�e en param�tre, on la s�lectionne par d�faut
                if (!empty($_GET['planning_resource_id']) && is_numeric($_GET['planning_resource_id'])) $objEvent->fields['id_resource'] = $_GET['planning_resource_id'];
                $objEvent->init_description();
                $objEventDetail->init_description();

                $strPopupTitle = "Ajout d'un �v�nement";

                $arrDateTimeBegin['date'] = $arrDateTimeEnd['date'] = empty($_GET['planning_resource_date']) ? '' : date('d/m/Y', $_GET['planning_resource_date']);
                $arrDateTimeBegin['time'] = $arrDateTimeEnd['time'] = split(':', '00:00');
            break;

            case 'planning_event_detail_open':
                if (!empty($_GET['planning_event_detail_id']) && is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
                {
                    if (!$objEvent->open($objEventDetail->fields['id_event'])) ploopi_die();
                }
                else ploopi_die();

                $strPopupTitle = "Modification d'un �v�nement";

                $arrDateTimeBegin = ploopi_timestamp2local($objEventDetail->fields['timestp_begin']);
                $arrDateTimeEnd = ploopi_timestamp2local($objEventDetail->fields['timestp_end']);
                $arrDateTimeBegin['time'] = split(':', $arrDateTimeBegin['time']);
                $arrDateTimeEnd['time'] = split(':', $arrDateTimeEnd['time']);

                $arrParams[] = "planning_event_detail_id={$_GET['planning_event_detail_id']}";

            break;
        }

        ?>
        <form action="<? echo ploopi_urlencode("admin-light.php?".implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform(this, 'planning_main', planning_event_validate); return false;">
        <div class=ploopi_form>
            <p>
                <label>Utilisateur / Groupe:</label>
                <span>
                    <div class="planning_event_ressource_list">
                        <div class="planning_event_ressource_list_inner">
                        <?
                        $arrResources = planning_get_resources();

                        // R�cup�ration des ressources associ�es � l'�v�nement
                        $arrEventDetailRessources = $objEventDetail->getresources();

                        foreach ($arrResources as $strResourceType => $arrResourceType)
                        {
                            foreach($arrResourceType as $row)
                            {
                                $booChecked = isset($arrEventDetailRessources[$strResourceType][$row['id']]);
                                ?>
                                <p class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, '_planning_eventresource_id<? echo $strResourceType[0].$row['id']; ?>');">
                                    <input type="checkbox" name="_planning_eventresource_id[<? echo $strResourceType; ?>][<? echo $row['id']; ?>]" id="_planning_eventresource_id<? echo $strResourceType[0].$row['id']; ?>" value="<? echo $row['id']; ?>" <? if ($booChecked) echo 'checked="checked"'; ?>/>
                                    <img style="background-color:<? echo $row['color']; ?>;" src="./modules/planning/img/ico_<? echo $strResourceType; ?>.png" />
                                    <em><? echo $row['label']; ?></em>
                                </p>
                                <?
                            }
                        }
                        ?>
                        </div>
                    </div>
               </span>
            </p>
            <p>
                <label>Objet:</label>
                <input name="planning_event_object" type="text" class="text" value="<? echo htmlentities($objEvent->fields['object']); ?>">
            </p>
            <p>
                <label>Date<? if ($ploopi_op == 'planning_event_add') echo ' de d�but'; ?>:</label>
                <input name="_planning_event_timestp_begin_d" id="_planning_event_timestp_begin_d" class="text" type="text" value="<? echo $arrDateTimeBegin['date']; ?>" style="width:80px;" <? if ($ploopi_op == 'planning_event_add') { ?>onchange="javascript:$('_planning_event_timestp_end_d').value = this.value;"<? } ?> />
                <?php ploopi_open_calendar('_planning_event_timestp_begin_d'); ?>
            </p>
            <p>
                <label>Heure de d�but:</label>
                <select name="_planning_event_timestp_begin_h" id="_planning_event_timestp_begin_h" class="select" style="width:45px;">
                <?
                for ($i = 0; $i < 24; $i++)
                {
                    ?><option <? if ($arrDateTimeBegin['time'][0] == $i) echo 'selected="selected"';  ?> value="<? echo $i; ?>"><? echo sprintf("%02d", $i); ?></option><?
                }
                ?>
                </select>
                H
                <select name="_planning_event_timestp_begin_m" id="_planning_event_timestp_begin_m" class="select" style="width:45px;">
                <?
                for ($i = 0; $i < 12; $i++)
                {
                    ?><option <? if ($arrDateTimeBegin['time'][1] == $i*5) echo 'selected="selected"';  ?> value="<? echo $i*5; ?>"><? echo sprintf("%02d", $i*5); ?></option><?
                }
                ?>
                </select>
            </p>
            <?
            if ($ploopi_op == 'planning_event_add')
            {
                ?>
                <p>
                    <label>Date de fin:</label>
                    <input name="_planning_event_timestp_end_d" id="_planning_event_timestp_end_d" class="text" type="text" value="<? echo $arrDateTimeEnd['date']; ?>" style="width:80px; "/>
                    <?php ploopi_open_calendar('_planning_event_timestp_end_d'); ?>
                </p>
                <?
            }
            ?>
            <p>
                <label>Heure de fin:</label>
                <select name="_planning_event_timestp_end_h" id="_planning_event_timestp_end_h" class="select" style="width:45px;">
                <?
                for ($i = 0; $i < 24; $i++)
                {
                    ?><option <? if ($arrDateTimeEnd['time'][0] == $i) echo 'selected="selected"';  ?> value="<? echo $i; ?>"><? echo sprintf("%02d", $i); ?></option><?
                }
                ?>
                </select>
                H
                <select name="_planning_event_timestp_end_m" id="_planning_event_timestp_end_h" class="select" style="width:45px;">
                <?
                for ($i = 0; $i < 12; $i++)
                {
                    ?><option <? if ($arrDateTimeEnd['time'][1] == $i*5) echo 'selected="selected"';  ?> value="<? echo $i*5; ?>"><? echo sprintf("%02d", $i*5); ?></option><?
                }
                ?>
                </select>
            </p>
            <?
            if ($ploopi_op == 'planning_event_add')
            {
                ?>
                <p>
                    <label>P�riodicit�:</label>
                    <select name="planning_event_periodicity" class="select" style="width:100px;">
                        <option value="">(Aucune)</option>
                        <?
                        foreach ($arrPlanningPeriodicity as $key => $value)
                        {
                            ?>
                            <option value="<? echo $key; ?>"><? echo htmlentities($value); ?></option>
                            <?
                        }
                        ?>
                    </select>

                    termin�e le:
                    <input name="_planning_event_periodicity_end_date" id="_planning_event_periodicity_end_date" class="text" type="text" value="" style="width:80px; "/>
                    <?php ploopi_open_calendar('_planning_event_periodicity_end_date'); ?>
                </p>
                <?
            }
            ?>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="R�initialiser" />
            <?
            if ($ploopi_op == 'planning_event_detail_open')
            {
                ?><input type="button" class="button" value="Supprimer" style="font-weight:bold;color:#a60000" onclick="javascript:if (confirm('�tes vous certain de vouloir supprimer cet �v�nement ?')) ploopi_xmlhttprequest_todiv('admin-light.php', '<? echo ploopi_queryencode("ploopi_op=planning_event_detail_delete&planning_event_detail_id={$_GET['planning_event_detail_id']}"); ?>', 'planning_main'); ploopi_hidepopup('popup_planning_event');" /><?
            }
            ?>
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        echo $skin->create_popup($strPopupTitle, $content, 'popup_planning_event');
        ploopi_die();
    break;

    case 'planning_event_detail_delete':

        include_once './modules/planning/classes/class_planning_event.php';
        include_once './modules/planning/classes/class_planning_event_detail.php';

        $objEventDetail = new planning_event_detail();

        if (!empty($_GET['planning_event_detail_id']) && is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
        {

            $objEvent = new planning_event();
            if ($objEvent->open($objEventDetail->fields['id_event']) && $objEvent->fields['id_user'] == $_SESSION['ploopi']['userid'])
            {
                $objEventDetail->delete();
            }
        }
        if (ploopi_urlencode($_SESSION['ploopi']['mode'] == 'backoffice')) ploopi_redirect("admin.php");
        else ploopi_redirect($_SESSION['planning'][$_GET['planning_moduleid']]['article_url']);
    break;
}
?>