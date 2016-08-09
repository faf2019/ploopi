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
 * Opérations sur les événements
 *
 * @package planning
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

        if (!empty($_GET['planning_event_detail_id'])) // mise à jour
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
        else  // création
        {
            if (!empty($_POST['_planning_event_timestp_begin_d']) && !empty($_POST['planning_event_object']) && !empty($_POST['_planning_eventresource_id']))
            {
                $objEvent = new planning_event();

                $objEvent->setvalues($_POST, 'planning_event_');
                $objEvent->setdetails($_POST, '_planning_event_');
                $objEvent->setresources($_POST['_planning_eventresource_id']);

                $objEvent->setuwm();
                $objEvent->save();
            }
        }

        ploopi_redirect('admin-light.php?ploopi_op=planning_refresh');
    break;


    case 'planning_event_detail_quicksave';
        ploopi_init_module('planning', false, false, false);

        include_once './modules/planning/classes/class_planning_event_detail.php';

        if (!empty($_GET['planning_event_detail_id'])) // mise à jour
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

        global $arrPlanningPeriodicity;

        $arrResources = ($_SESSION['ploopi']['mode'] == 'frontoffice') ? planning_get_resources(false, $_GET['planning_moduleid']) : planning_get_resources();

        $objEvent = new planning_event();
        $objEventDetail = new planning_event_detail();

        $arrParams = array();
        $arrParams[] = "ploopi_op=planning_event_save";

        switch($ploopi_op)
        {
            case 'planning_event_add':
                // Si une ressource est passée en paramètre, on la sélectionne par défaut
                if (!empty($_GET['planning_resource_id']) && is_numeric($_GET['planning_resource_id'])) $objEvent->fields['id_resource'] = $_GET['planning_resource_id'];
                $objEvent->init_description();
                $objEventDetail->init_description();

                $strPopupTitle = "Ajout d'un événement";

                $arrDateTimeBegin['date'] = $arrDateTimeEnd['date'] = empty($_GET['planning_resource_date']) ? '' : date('d/m/Y', $_GET['planning_resource_date']);
                $arrDateTimeBegin['time'] = $arrDateTimeEnd['time'] = preg_split('/:/', '00:00');
            break;

            case 'planning_event_detail_open':
                if (!empty($_GET['planning_event_detail_id']) && is_numeric($_GET['planning_event_detail_id']) && $objEventDetail->open($_GET['planning_event_detail_id']))
                {
                    if (!$objEvent->open($objEventDetail->fields['id_event'])) ploopi_die();
                }
                else ploopi_die();

                $strPopupTitle = "Modification d'un événement";

                $arrDateTimeBegin = ploopi_timestamp2local($objEventDetail->fields['timestp_begin']);
                $arrDateTimeEnd = ploopi_timestamp2local($objEventDetail->fields['timestp_end']);
                $arrDateTimeBegin['time'] = preg_split('/:/', $arrDateTimeBegin['time']);
                $arrDateTimeEnd['time'] = preg_split('/:/', $arrDateTimeEnd['time']);

                $arrParams[] = "planning_event_detail_id={$_GET['planning_event_detail_id']}";

            break;
        }

        if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
        {
            ?>
            <form id="planning_add_form" action="<?php echo ploopi_urlencode("admin-light.php?".implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform($('planning_add_form'), 'planning_main', planning_event_validate); return false;">
            <?php
        }
        ?>
        <div class="ploopi_form">
            <p>
                <label>Utilisateur / Groupe:</label>
                <?php
                if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
                {
                    ?>
                    <span class="planning_event_ressource_list">
                        <?php
                        $arrResources = planning_get_resources();

                        // Récupération des ressources associées à l'événement
                        $arrEventDetailRessources = $objEventDetail->getresources();

                        foreach ($arrResources as $strResourceType => $arrResourceType)
                        {
                            foreach($arrResourceType as $row)
                            {
                                $booChecked = isset($arrEventDetailRessources[$strResourceType][$row['id']]);
                                ?>
                                <em class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, '_planning_eventresource_id<?php echo $strResourceType[0].$row['id']; ?>');">
                                    <input type="checkbox" name="_planning_eventresource_id[<?php echo $strResourceType; ?>][<?php echo $row['id']; ?>]" id="_planning_eventresource_id<?php echo $strResourceType[0].$row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if ($booChecked) echo 'checked="checked"'; ?>/>
                                    <img style="background-color:<?php echo $row['color']; ?>;" src="./modules/planning/img/ico_<?php echo $strResourceType; ?>.png" />
                                    <?php echo ploopi_htmlentities($row['label']); ?>
                                </em>
                                <?php
                            }
                        }
                        ?>
                    </span>
                    <?php
                }
                else
                {
                    ?>
                    <span>
                        <?php
                        $arrResources = planning_get_resources();

                        // Récupération des ressources associées à l'événement
                        $arrEventDetailRessources = $objEventDetail->getresources();

                        foreach ($arrResources as $strResourceType => $arrResourceType)
                        {
                            foreach($arrResourceType as $row)
                            {
                                if (isset($arrEventDetailRessources[$strResourceType][$row['id']]))
                                {
                                    ?>
                                    <em style="display:block;">
                                        <img style="background-color:<?php echo $row['color']; ?>;" src="./modules/planning/img/ico_<?php echo $strResourceType; ?>.png" />
                                        <?php echo ploopi_htmlentities($row['label']); ?>
                                    </em>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </span>
                    <?php
                }
                ?>
            </p>
            <p>
                <label>Objet:</label>
                <?php
                if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
                {
                    ?><input name="planning_event_object" type="text" class="text" value="<?php echo ploopi_htmlentities($objEvent->fields['object']); ?>"><?php
                }
                else
                {
                    ?><span><?php echo ploopi_htmlentities($objEvent->fields['object']); ?></span><?php
                }
                ?>
            </p>
            <p>
                <label>Date<?php if ($ploopi_op == 'planning_event_add') echo ' de début'; ?>:</label>
                <?php
                if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
                {
                    ?>
                    <input name="_planning_event_timestp_begin_d" id="_planning_event_timestp_begin_d" class="text" type="text" value="<?php echo ploopi_htmlentities($arrDateTimeBegin['date']); ?>" style="width:80px;" <?php if ($ploopi_op == 'planning_event_add') { ?>onchange="javascript:$('_planning_event_timestp_end_d').value = this.value;"<?php } ?> />
                    <?php ploopi_open_calendar('_planning_event_timestp_begin_d'); ?>
                    <?php
                }
                else
                {
                    ?><span><?php echo ploopi_htmlentities($arrDateTimeBegin['date']); ?></span><?php
                }
                ?>

            </p>
            <p>
                <label>Heure de début:</label>
                <?php
                if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
                {
                    ?>
                    <select name="_planning_event_timestp_begin_h" id="_planning_event_timestp_begin_h" class="select" style="width:60px;">
                    <?php
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option <?php if ($arrDateTimeBegin['time'][0] == $i) echo 'selected="selected"';  ?> value="<?php echo $i; ?>"><?php echo sprintf("%02d h", $i); ?></option><?php
                    }
                    ?>
                    </select>
                    <select name="_planning_event_timestp_begin_m" id="_planning_event_timestp_begin_m" class="select" style="width:45px;">
                    <?php
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option <?php if ($arrDateTimeBegin['time'][1] == $i*5) echo 'selected="selected"';  ?> value="<?php echo $i*5; ?>"><?php echo sprintf("%02d", $i*5); ?></option><?php
                    }
                    ?>
                    </select>
                    <?php
                }
                else
                {
                    ?><span><?php echo ploopi_htmlentities($arrDateTimeBegin['time'][0].':'.$arrDateTimeBegin['time'][1]); ?></span><?php
                }
                ?>
            </p>
            <?php
            if ($ploopi_op == 'planning_event_add')
            {
                ?>
                <p>
                    <label>Date de fin:</label>
                    <input name="_planning_event_timestp_end_d" id="_planning_event_timestp_end_d" class="text" type="text" value="<?php echo ploopi_htmlentities($arrDateTimeEnd['date']); ?>" style="width:80px; "/>
                    <?php ploopi_open_calendar('_planning_event_timestp_end_d'); ?>
                </p>
                <?php
            }
            ?>
            <p>
                <label>Heure de fin:</label>
                <?php
                if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
                {
                    ?>
                    <select name="_planning_event_timestp_end_h" id="_planning_event_timestp_end_h" class="select" style="width:60px;">
                    <?php
                    for ($i = 0; $i < 24; $i++)
                    {
                        ?><option <?php if ($arrDateTimeEnd['time'][0] == $i) echo 'selected="selected"';  ?> value="<?php echo $i; ?>"><?php echo sprintf("%02d h", $i); ?></option><?php
                    }
                    ?>
                    </select>
                    <select name="_planning_event_timestp_end_m" id="_planning_event_timestp_end_h" class="select" style="width:45px;">
                    <?php
                    for ($i = 0; $i < 12; $i++)
                    {
                        ?><option <?php if ($arrDateTimeEnd['time'][1] == $i*5) echo 'selected="selected"';  ?> value="<?php echo $i*5; ?>"><?php echo sprintf("%02d", $i*5); ?></option><?php
                    }
                    ?>
                    </select>
                    <?php
                }
                else
                {
                    ?><span><?php echo ploopi_htmlentities($arrDateTimeEnd['time'][0].':'.$arrDateTimeEnd['time'][1]); ?></span><?php
                }
                ?>
            </p>
            <?php
            if ($ploopi_op == 'planning_event_add')
            {
                ?>
                <p>
                    <label>Périodicité:</label>
                    <select name="planning_event_periodicity" class="select" style="width:100px;">
                        <option value="">(Aucune)</option>
                        <?php
                        foreach ($arrPlanningPeriodicity as $key => $value)
                        {
                            ?>
                            <option value="<?php echo ploopi_htmlentities($key); ?>"><?php echo ploopi_htmlentities($value); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </p>
                <p>
                    <label><em>se termine le</em>:</label>
                    <input name="_planning_event_periodicity_end_date" id="_planning_event_periodicity_end_date" class="text" type="text" value="" style="width:80px; "/>
                    <?php ploopi_open_calendar('_planning_event_periodicity_end_date'); ?>
                </p>
                <?php
            }
            ?>
        </div>
        <?php
        if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
        {
            ?>
            <div style="padding:4px;text-align:right;">
                <input type="reset" class="button" value="Réinitialiser" />
                <?php
                if ($ploopi_op == 'planning_event_detail_open')
                {
                    ?><input type="button" class="button" value="Supprimer" style="font-weight:bold;color:#a60000" onclick="javascript:if (confirm('Êtes vous certain de vouloir supprimer cet événement ?')) ploopi_xmlhttprequest_todiv('admin-light.php', '<?php echo ploopi_queryencode("ploopi_op=planning_event_detail_delete&planning_event_detail_id={$_GET['planning_event_detail_id']}"); ?>', 'planning_main'); ploopi_hidepopup('popup_planning_event');" /><?php
                }
                ?>
                <input type="submit" class="button" value="Enregistrer" />
            </div>
            </form>
            <div style="border-top:1px solid #aaa;"><?php ploopi_annotation(_PLANNING_OBJECT_EVENT, $objEventDetail->fields['id'], $objEvent->fields['object']); ?></div>
            <?php
        }
        ?>
        <?php
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
