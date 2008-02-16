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
?>
<div id="forms_info_box">
    <?
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

        if (!$ct &&  $_SESSION['forms'][$_GET['forms_fuid']]['rights']['_FORMS_ACTION_ADDREPLY'])
        {
            ?>
            <div style="float:right;;margin-left:10px;">
                <input type="button" class="flatbutton" style="font-weight:bold" value="Ajouter un enregistrement" onclick="javascript:forms_openreply('<? echo $_GET['forms_fuid']; ?>', '0', event);">
            </div>
            <?
        }
    }
    ?>

    <?
    $numrows = sizeof($data);

    if ($forms->fields['nbline'] > 0 && $numrows > $forms->fields['nbline'])
    {
        $numpages = (($numrows - ($numrows % $forms->fields['nbline'])) / $forms->fields['nbline']) + (($numrows % $forms->fields['nbline'])>0);
        ?>
        <div style="float:right">
        <?
        if ($_SESSION['forms']['page']>0)
        {
            echo "<input type\"button\" class=\"button\" value=\"««\" size=\"4\" OnClick=\"javascript:document.location.href='$scriptenv?op=viewlist&forms_id={$id_form}&page=".($_SESSION['forms']['page']-1)."'\">";
        }
        ?>
        Page <? echo $_SESSION['forms']['page']+1; ?> / <? echo $numpages; ?>
        <?
        if ($_SESSION['forms']['page']+1<$numpages)
        {
            echo "<input type\"button\" class=\"button\" value=\"»»\" size=\"4\" OnClick=\"javascript:document.location.href='$scriptenv?op=viewlist&forms_id={$id_form}&page=".($_SESSION['forms']['page']+1)."'\">";
            //echo "<a href=\"$scriptenv?page=".($page+1)."\">»»</a>";
        }
        ?>
        </div>
        <?
    }
    ?>
    <div style="float:left;">
    Nombre d'Enregistrements : <b><? echo sizeof($data); ?></b>
    </div>

    <?
    if ( $_SESSION['forms'][$_GET['forms_fuid']]['rights']['_FORMS_ACTION_EXPORT'])
    {
        ?>
            <div style="float:left;margin-left:10px;">
            <a title="<? echo _FORMS_EXPORT; ?> XLS" href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_op=forms_export&forms_fuid={$_GET['forms_fuid']}&forms_export_format=XLS&orderby={$orderby}&option={$option}"); ?>"><img border="0" alt="<? echo _FORMS_EXPORT; ?> XLS" src="./modules/forms/img/download_xls.gif"></a>
            <a title="<? echo _FORMS_EXPORT; ?> CSV" href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_op=forms_export&forms_fuid={$_GET['forms_fuid']}&forms_export_format=CSV&orderby={$orderby}&option={$option}"); ?>"><img border="0" alt="<? echo _FORMS_EXPORT; ?> CSV" src="./modules/forms/img/download_csv.gif"></a>
            </div>
        <?
    }
    ?>
</div>

<div class="viewlist" <? if ($_SESSION['forms'][$_GET['forms_fuid']]['options']['height']>0) echo 'style="height:'.$_SESSION['forms'][$_GET['forms_fuid']]['options']['height'].'px;overflow:auto;"'; ?>>
    <table class="viewlist">
    <?
    $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
    ?>
    <tr style="background-color:<? echo $color; ?>;">
        <?
        foreach ($data_title as $key => $value)
        {
            $value = $value['label'];
            $display = false;
            switch($key)
            {
                case 'object':
                    $display = $_SESSION['forms'][$_GET['forms_fuid']]['options']['object_display'];
                break;

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
                if ($orderby == $key)
                {
                    $new_option = ($option == 'DESC') ? '' : 'DESC';
                    $style_col = 'class="selected"';
                    $sort_cell = ($option == 'DESC') ? 'arrow_down' : 'arrow_up';
                }
                ?>
                <th>
                    <a <? echo $style_col; ?> href="javascript:void(0);" onclick="javascript:forms_display('<? echo $_GET['forms_fuid']; ?>', '<? echo "orderby={$key}&option={$new_option}"; ?>');">
                    <p class="ploopi_va">
                        <span><? echo $value; ?></span>
                        <img src="./modules/forms/img/<? echo $sort_cell; ?>.png">
                    </p>
                    </a>
                </th>
                <?
            }
        }
        if ($_SESSION['ploopi']['action'] == 'public')
        {
            ?>
            <td></td>
            <?
        }
        ?>
    </tr>

    <?
    $c=0;

    reset($data);

    foreach ($data as $reply_id => $detail)
    {
        // filtre sur la page sélectionnée

        if (($forms->fields['nbline'] == 0) || ($c >= ($_SESSION['forms']['page'])*$forms->fields['nbline'] && $c < ($_SESSION['forms']['page']+1)*$forms->fields['nbline']))
        {
            $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
            ?>
            <tr bgcolor="<? echo $color; ?>">
                <?
                foreach ($detail as $key => $value)
                {
                    $display = false;
                    switch($key)
                    {
                        case 'object':
                            $display = $_SESSION['forms'][$_GET['forms_fuid']]['options']['object_display'];
                        break;

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
                                if ($value != '') $value = $value.'<a href="'."{$scriptenv}?op=download_file&forms_id={$id_form}&reply_id={$reply_id}&field_id={$key}".'"><img style="border:0px" src="./modules/forms/img/link.gif"></a>';
                            break;

                            case 'color':
                                $value = '<div style="background-color:'.$value.';">&nbsp;&nbsp;</div>';
                            break;

                            default:
                                $value = str_replace('||','<br />',$value);
                                $value = ploopi_make_links(ploopi_nl2br($value));
                            break;
                        }
                        echo "<td class=\"data\">{$value}</td>";
                    }
                }
                $modify = ploopi_urlencode("$scriptenv?op=modify&forms_id={$id_form}&reply_id={$reply_id}");
                $delete = ploopi_urlencode("$scriptenv?op=delete_reply&forms_id={$id_form}&reply_id={$reply_id}");
                if ($_SESSION['ploopi']['action'] == 'public')
                {
                    ?>
                    <td align="left" nowrap>
                        <?
                        if ( $_SESSION['forms'][$_GET['forms_fuid']]['rights']['_FORMS_ACTION_ADDREPLY'] && (($forms->fields['option_modify'] == 'user' && $detail['userid'] == $_SESSION['ploopi']['userid']) || ($forms->fields['option_modify'] == 'group' && $detail['workspaceid'] == $_SESSION['ploopi']['workspaceid'])  || ($forms->fields['option_modify'] == 'all')))
                        {
                            ?>
                            <a href="<? echo $modify; ?>"><img alt="ouvrir" border="0" src="./modules/forms/img/ico_modify.png"></a>
                            <?
                            if ($_SESSION['forms'][$_GET['forms_fuid']]['rights']['_FORMS_ACTION_DELETE'])
                            {
                                ?>
                                <a href="javascript:ploopi_confirmlink('<? echo $delete; ?>','<? echo _PLOOPI_CONFIRM; ?>')"><img alt="supprimer" border="0" src="./modules/forms/img/ico_trash.png"></a>
                                <?
                            }
                        }
                        ?>
                    </td>
                    <?
                }
                ?>
            </tr>
            <?
        }
        $c++;
    }
    ?>
    </table>
</div>

<?
$_SESSION['forms']['export'] = $data;
$_SESSION['forms']['export_title'] = $data_title;
$_SESSION['forms']['export_fields'] = $array_fields;
?>
