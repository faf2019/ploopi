<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Affichage des données d'un formulaire
 *
 * @package forms
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

echo $skin->open_simplebloc($forms->fields['label'].' ('._FORMS_VIEWLIST.')', '100%');
?>

<div style="overflow:hidden;">

    <?php
    if (ploopi_isactionallowed(_FORMS_ACTION_FILTER))
    {
        if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box'] = 'none';
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_filter_box');ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_filter_box&display='+$('forms_filter_box').style.display, true);">
            Filtrage des données<sub style="font-weight:normal;">&nbsp;&nbsp;&nbsp;(cliquez pour ouvrir/fermer)</sub>
        </a>
        <div id="forms_filter_box"  style="display:<?php echo $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box']; ?>;">
            <form style="margin:0;" id="filtre_frm" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="forms_filter">
            <input type="hidden" name="forms_id" value="<?php echo $forms_id; ?>">

            <p><b>Filtre:</b></p>
            <?php
            for ($l=1;$l<=$lmax;$l++)
            {
                ?>
                <p>
                    <select class="select" name="filter_field_<?php echo $l; ?>" style="width:150px">
                    <option></option>
                    <?php
                    $lev = 0;
                    foreach ($data_title as $key => $value)
                    {
                        if ($value['sep'])
                        {
                            for ($i=$value['seplev'];$i<=$lev;$i++) echo "</optgroup>";
                            $padding = ($lev>1) ? str_repeat('&nbsp;', $lev-1) : '';

                            ?>
                            <optgroup label="<?php echo $padding.htmlentities($value['label']); ?>">
                            <?php
                        }
                        else
                        {
                            $value = $value['label'];
                            $sel = (isset(${"filter_field_{$l}"}) && ${"filter_field_{$l}"} == $key) ? 'selected' : '';
                            ?>
                            <option <?php echo $sel; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php
                        }
                    }
                    for ($i=1;$i<=$lev;$i++) echo "</optgroup>";
                    ?>
                    </select>
                    <select class="select" name="filter_op_<?php echo $l; ?>" style="width:70px">
                        <?php
                        foreach($field_operators as $key => $value)
                        {
                            $sel = (isset(${"filter_op_{$l}"}) && ${"filter_op_{$l}"} == $key) ? 'selected' : '';
                            echo "<option $sel value=\"{$key}\">{$value}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" value="<?php if (isset(${"filter_value_{$l}"})) echo ${"filter_value_{$l}"}; ?>" size="25" class="text" name="filter_value_<?php echo $l; ?>">
                </p>
                <?php
            }
            if ($forms->fields['autobackup'] > 0 || !empty($forms->fields['autobackup_date']))
            {
                ?>
                <p>
                    <input type="checkbox" name="unlockbackup" <?php if ($_SESSION['forms'][$forms_id]["unlockbackup"]) echo 'checked'; ?> value="1">Afficher les enregistrements archivés
                </p>
                <?php
            }
            ?>
            <p>
                <?php
                if (ploopi_isactionallowed(_FORMS_ACTION_DELETE))
                {
                    ?>
                    <input type="button" class="flatbutton" value="Supprimer les données filtrées" onclick="javascript:if (confirm('Attention, cette action va supprimer définitivement les données filtrées, continuer ?')) {$('filtre_frm').op.value='forms_deletedata';$('filtre_frm').submit();}">
                    <?php
                }
                ?>
                <input type="button" class="flatbutton" value="<?php echo _PLOOPI_RESET; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$forms_id}&reset"); ?>'">
                <input type="submit" class="flatbutton" style="font-weight:bold" value="<?php echo _PLOOPI_FILTER; ?>">
            </p>
            </form>
        </div>
        <?php
    }
    ?>


    <?php
    if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_archive_box'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_archive_box'] = 'none';

    if (ploopi_isactionallowed(_FORMS_ACTION_BACKUP))
    {
        $autobackup_date = ($forms->fields['autobackup_date']) ? ploopi_timestamp2local($forms->fields['autobackup_date']) : array('date' => '');
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_archive_box');ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_archive_box&display='+$('forms_archive_box').style.display, true);">
            Archivage automatique des données<sub style="font-weight:normal;">&nbsp;&nbsp;&nbsp;(cliquez pour ouvrir/fermer)</sub>
        </a>
        <div id="forms_archive_box" style="display:<?php echo $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_archive_box']; ?>;">
            <form name="frm_modify" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="forms_save">
            <input type="hidden" name="forms_id" value="<?php echo $forms->fields['id']; ?>">

                <div class="ploopi_form">
                    <p>
                        <label>Archiver les données plus anciennes que :</label>
                        <input type="text" class="text" style="width:30px;" name="forms_autobackup" value="<?php echo $forms->fields['autobackup']; ?>"> jours (0 = aucun archivage)
                    </p>
                    <p>
                        <label>Archiver les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" name="forms_autobackup_date" id="forms_autobackup_date" value="<?php echo $autobackup_date['date']; ?>">&nbsp;<a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('forms_autobackup_date', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                    </p>
                    <p>
                        <label>&nbsp;</label>
                        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" style="width:100px;">
                    </p>
                </div>

            </form>
        </div>
        <?php
    }
    ?>

    <div id="forms_info_box">
        <?php
        if ($_SESSION['ploopi']['action'] == 'public')
        {
            $ct = 0;
            if ($forms->fields['option_onlyone'] || $forms->fields['option_onlyoneday'])
            {
                $select = "select count(*) as ct from ploopi_mod_forms_reply where 1 ";
                if ($forms->fields['option_onlyone']) $select .= " AND id_user = {$_SESSION['ploopi']['userid']}";
                if ($forms->fields['option_onlyoneday']) $select .= " AND LEFT(date_validation,8) = '".substr(ploopi_createtimestamp(),0,8)."'";
                $db->query($select);
                if ($fields = $db->fetchrow()) $ct = $fields['ct'];
            }

            if (!$ct &&  ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY))
            {
                ?>
                <div style="float:right;;margin-left:10px;">
                    <input type="button" class="flatbutton" style="font-weight:bold" value="Ajouter un enregistrement" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_reply_add&forms_id={$forms_id}"); ?>'">
                </div>
                <?php
            }
        }
        ?>

        <?php
        $numrows = sizeof($export);

        if ($forms->fields['nbline'] > 0 && $numrows > $forms->fields['nbline'])
        {
            $numpages = (($numrows - ($numrows % $forms->fields['nbline'])) / $forms->fields['nbline']) + (($numrows % $forms->fields['nbline'])>0);
            ?>
            <div style="float:right">
                <div style="float:left;width:40px;"><?php
                if ($_SESSION['forms'][$forms->fields['id']]['page']>0)
                {
                    ?><input type="button" class="button" value="««" style="width:90%;" onclick="javascript:document.location.href='<?php echo "admin.php?op=forms_viewreplies&forms_id={$forms->fields['id']}&page=".($_SESSION['forms'][$forms->fields['id']]['page']-1); ?>'"><?php
                }
                ?></div>
                <div style="float:left;margin:0 10px;">Page <?php echo $_SESSION['forms'][$forms->fields['id']]['page']+1; ?> / <?php echo $numpages; ?></div>
                <div style="float:left;width:40px;"><?php
                if ($_SESSION['forms'][$forms->fields['id']]['page']+1<$numpages)
                {
                    ?><input type="button" class="button" value="»»" style="width:90%;" onclick="javascript:document.location.href='<?php echo "admin.php?op=forms_viewreplies&forms_id={$forms->fields['id']}&page=".($_SESSION['forms'][$forms->fields['id']]['page']+1); ?>'"><?php
                }
                ?></div>
            </div>
            <?php
        }
        ?>
        <div style="float:left;">
        Nombre d'Enregistrements : <b><?php echo sizeof($data); ?></b> - Avec le Filtre : <b><?php echo sizeof($export); ?></b>
        </div>

        <?php
        if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT))
        {
            ?>
                <div style="float:left;margin-left:10px;">
                <a title="<?php echo _FORMS_EXPORT; ?> XLS" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$forms_id}&forms_export_format=XLS"); ?>"><img border="0" alt="<?php echo _FORMS_EXPORT; ?> XLS" src="./modules/forms/img/download_xls.gif"></a>
                <a title="<?php echo _FORMS_EXPORT; ?> CSV" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$forms_id}&forms_export_format=CSV"); ?>"><img border="0" alt="<?php echo _FORMS_EXPORT; ?> CSV" src="./modules/forms/img/download_csv.gif"></a>
                </div>
            <?php
        }
        ?>
    </div>

    <div class="viewlist">
        <table class="viewlist">
        <?php
        $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
        ?>
        <tr style="background-color:<?php echo $color; ?>;">
            <?php
            foreach ($data_title as $key => $value)
            {
                $value = $value['label'];
                $display = false;
                switch($key)
                {
                    case 'datevalidation':
                        $display = ($forms->fields['option_displaydate']);
                    break;

                    case 'user':
                        $display = ($forms->fields['option_displayuser']);
                    break;

                    case 'group':
                        $display = ($forms->fields['option_displaygroup']);
                    break;

                    case 'ip':
                        $display = ($forms->fields['option_displayip']);
                    break;

                    default:
                        $display = (isset($array_fields[$key]) && $array_fields[$key]['option_arrayview']);
                    break;
                }

                if ($display)
                {
                    $new_option = $style_col = $sort_cell = '';
                    if ($_SESSION['forms'][$forms_id]['orderby'] == $key)
                    {
                        $new_option = ($_SESSION['forms'][$forms_id]['option'] == 'DESC') ? '' : 'DESC';
                        $style_col = 'class="selected"';
                        $sort_cell = ($_SESSION['forms'][$forms_id]['option'] == 'DESC') ? 'arrow_down' : 'arrow_up';
                    }

                    ?>
                    <th>
                        <a <?php echo $style_col; ?> href="<?php echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$forms_id}&orderby={$key}&option={$new_option}"); ?>">
                        <p class="ploopi_va">
                            <span><?php echo $value; ?></span>
                            <img src="./modules/forms/img/<?php echo $sort_cell; ?>.png">
                        </p>
                        </a>
                    </th>
                    <?php
                }
            }
            if ($_SESSION['ploopi']['action'] == 'public')
            {
                ?>
                <td></td>
                <?php
            }
            ?>
        </tr>

        <?php
        $c=0;

        foreach ($export as $reply_id => $detail)
        {
            // filtre sur la page sélectionnée

            if (($forms->fields['nbline'] == 0) || ($c >= ($_SESSION['forms'][$forms->fields['id']]['page'])*$forms->fields['nbline'] && $c < ($_SESSION['forms'][$forms->fields['id']]['page']+1)*$forms->fields['nbline']))
            {
                $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
                ?>
                <tr bgcolor="<?php echo $color; ?>">
                    <?php
                    foreach ($detail as $key => $value)
                    {
                        $display = false;
                        switch($key)
                        {
                            case 'datevalidation':
                                $display = ($forms->fields['option_displaydate']);
                            break;

                            case 'user':
                                $display = ($forms->fields['option_displayuser']);
                            break;

                            case 'group':
                                $display = ($forms->fields['option_displaygroup']);
                            break;

                            case 'ip':
                                $display = ($forms->fields['option_displayip']);
                            break;

                            default:
                                $display = (isset($array_fields[$key]) && $array_fields[$key]['option_arrayview']);
                            break;
                        }

                        if ($display)
                        {
                            switch($data_title[$key]['type'])
                            {
                                case 'file':
                                    if ($value != '') $value = $value.'<a href="'.ploopi_urlencode("admin.php?op=forms_download_file&forms_id={$forms_id}&reply_id={$reply_id}&field_id={$key}").'"><img style="border:0px" src="./modules/forms/img/link.gif"></a>';
                                break;

                                case 'color':
                                    $value = '<div style="background-color:'.$value.';">&nbsp;&nbsp;</div>';
                                break;

                                default:
                                    $value = str_replace('||','<br />',$value);
                                    $value = ploopi_make_links($value);
                                break;
                            }
                            echo "<td class=\"data\">{$value}</td>";
                        }
                    }
                    $modify = ploopi_urlencode("admin.php?op=forms_reply_modify&forms_id={$forms_id}&reply_id={$reply_id}");
                    $delete = ploopi_urlencode("admin.php?op=forms_reply_delete&forms_id={$forms_id}&reply_id={$reply_id}");
                    $display = ploopi_urlencode("admin.php?op=forms_reply_display&forms_id={$forms_id}&reply_id={$reply_id}");
                    if ($_SESSION['ploopi']['action'] == 'public')
                    {
                        ?>
                        <td align="left" nowrap>
                            <?php
                            if (ploopi_isadmin() || (ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY) && (($forms->fields['option_modify'] == 'user' && $detail['userid'] == $_SESSION['ploopi']['userid']) || ($forms->fields['option_modify'] == 'group' && $detail['workspaceid'] == $_SESSION['ploopi']['workspaceid'])  || ($forms->fields['option_modify'] == 'all'))))
                            {
                                ?>
                                <a title="Ouvrir" href="<?php echo $display; ?>"><img alt="ouvrir" border="0" src="./modules/forms/img/ico_display.png"></a>
                                <a title="Modifier" href="<?php echo $modify; ?>"><img alt="ouvrir" border="0" src="./modules/forms/img/ico_modify.png"></a>
                                <?php
                                if (ploopi_isactionallowed(_FORMS_ACTION_DELETE))
                                {
                                    ?>
                                    <a title="Supprimer" href="javascript:ploopi_confirmlink('<?php echo $delete; ?>','<?php echo _PLOOPI_CONFIRM; ?>')"><img alt="supprimer" border="0" src="./modules/forms/img/ico_trash.png"></a>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            $c++;
        }
        ?>
        </table>
    </div>
</div>

<?php echo $skin->close_simplebloc(); ?>
