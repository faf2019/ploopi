<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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

// Admin oui/non ?
$booIsAdmin = ploopi_isactionallowed(_FORMS_ACTION_ADMIN);

// Nombre de lignes (visibles dans le profil actuel)
$intTotalNumRows = $objForm->getNumRows(true);

// Lecture des champs statiques du formulaire
$arrStaticFields = formsForm::getStaticFields();

// Lecture des titres de colonnes
$arrTitles = $objForm->getTitles();

echo $skin->open_simplebloc($objForm->fields['label'].' ('._FORMS_VIEWLIST.')', '100%');
?>

<div style="overflow:hidden;">

    <?php
    if (ploopi_isactionallowed(_FORMS_ACTION_FILTER))
    {
        // Lecture des champs dynamiques et séparateurs du formulaire
        $arrFields = $objForm->getFields(true);

        if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box'] = 'none';
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_filter_box');ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_filter_box&display='+$('forms_filter_box').style.display, true);">
            Filtrage des données<sub style="font-weight:normal;">&nbsp;&nbsp;&nbsp;(cliquez pour ouvrir/fermer)</sub>
        </a>
        <div id="forms_filter_box"  style="display:<?php echo $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_filter_box']; ?>;">
            <form style="margin:0;" id="filtre_frm" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="forms_filter">
            <input type="hidden" name="forms_id" value="<?php echo $objForm->fields['id']; ?>">

            <p><b>Filtre:</b></p>
            <?php

            for ($l=1;$l<=sizeof($arrFormFilter)+1;$l++)
            {
                ?>
                <p>
                    <select class="select" name="filter_field_<?php echo $l; ?>" style="width:150px">
                    <option></option>
                    <?php
                    foreach($arrStaticFields as $strKey => $strValue)
                    {
                        $booSel = (isset($arrFormFilter[$l]) && $arrFormFilter[$l]['field'] == $strKey) ? 'selected="selected"' : '';
                        ?>
                        <option <?php echo $booSel; ?> value="<?php echo $strKey; ?>"><?php echo $strValue; ?></option>
                        <?php
                    }

                    $intLevel = 0;

                    foreach($arrFields as $strKey => $objField)
                    {
                        if (!$objField->fields['option_adminonly'] || $booIsAdmin)
                        {
                            if ($objField->fields['separator'])
                            {
                                for ($i = $objField->fields['separator_level']; $i <= $intLevel; $i++) echo "</optgroup>";
                                $strPadding = $intLevel > 1 ? str_repeat('&nbsp;', $intLevel-1) : '';
                                ?>
                                <optgroup label="<?php echo $strPadding.ploopi_htmlentities($objField->fields['name']); ?>">
                                <?php
                            }
                            else
                            {
                                $booSel = (isset($arrFormFilter[$l]) && $arrFormFilter[$l]['field'] == $strKey) ? 'selected="selected"' : '';
                                ?>
                                <option <?php echo $booSel; ?> value="<?php echo $strKey; ?>"><?php echo ploopi_htmlentities($objField->fields['name']); ?></option>
                                <?php
                            }
                        }
                    }

                    for ($i=1;$i<=$intLevel;$i++) echo "</optgroup>";
                    ?>
                    </select>
                    <select class="select" name="filter_op_<?php echo $l; ?>" style="width:70px">
                        <?php
                        foreach($field_operators as $strKey => $strValue)
                        {
                            $booSel = (isset($arrFormFilter[$l]) && $arrFormFilter[$l]['op'] == $strKey) ? 'selected="selected"' : '';
                            echo "<option {$booSel} value=\"{$strKey}\">{$strValue}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" value="<?php if (isset($arrFormFilter[$l])) echo ploopi_htmlentities($arrFormFilter[$l]['value']); ?>" size="80" class="text" name="filter_value_<?php echo $l; ?>">
                </p>
                <?php
            }
            if ($objForm->fields['autobackup'] > 0 || !empty($objForm->fields['autobackup_date']))
            {
                ?>
                <p>
                    <input type="checkbox" name="unlockbackup" <?php if ($_SESSION['forms'][$objForm->fields['id']]["unlockbackup"]) echo 'checked'; ?> value="1">Afficher les enregistrements archivés
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
                <input type="button" class="flatbutton" value="<?php echo _PLOOPI_RESET; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}&reset"); ?>'">
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
        $autobackup_date = ($objForm->fields['autobackup_date']) ? ploopi_timestamp2local($objForm->fields['autobackup_date']) : array('date' => '');
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_archive_box');ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_archive_box&display='+$('forms_archive_box').style.display, true);">
            Archivage automatique des données<sub style="font-weight:normal;">&nbsp;&nbsp;&nbsp;(cliquez pour ouvrir/fermer)</sub>
        </a>
        <div id="forms_archive_box" style="display:<?php echo $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_archive_box']; ?>;">
            <form name="frm_modify" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="forms_save">
            <input type="hidden" name="forms_id" value="<?php echo $objForm->fields['id']; ?>">

                <div class="ploopi_form">
                    <p>
                        <label>Archiver les données plus anciennes que :</label>
                        <input type="text" class="text" style="width:30px;" name="forms_autobackup" value="<?php echo $objForm->fields['autobackup']; ?>"> jours (0 = aucun archivage)
                    </p>
                    <p>
                        <label>Archiver les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" name="forms_autobackup_date" id="forms_autobackup_date" value="<?php echo $autobackup_date['date']; ?>">&nbsp;
                        <?php echo ploopi_open_calendar('forms_autobackup_date'); ?>
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
        <div style="float:right;margin-bottom:2px;">
            <p class="ploopi_va">
            <?php
            if ($objForm->fields['nbline'] > 0 && $intNumRows > $objForm->fields['nbline'])
            {
                include_once './include/functions/array.php';
                echo '<span>Pages&nbsp;:&nbsp;</span>'.ploopi_array_getpages($intNumRows, $objForm->fields['nbline'], "admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}&page={p}", $_SESSION['forms'][$objForm->fields['id']]['page']);
            }

            if ($_SESSION['ploopi']['action'] == 'public')
            {
                // Recherche du nombre de lignes déjà saisies pour le jour ou l'utilisateur
                if (!$objForm->getNumRowsOnly() &&  ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY))
                {
                    ?>
                    <input type="button" class="flatbutton" style="margin-left:10px;font-weight:bold" value="Ajouter un enregistrement" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_reply_add&forms_id={$objForm->fields['id']}"); ?>'">
                    <?php
                }
            }
            ?>
            </p>
        </div>

        <div style="float:left;">
            <p class="ploopi_va">
            <span>
                Nombre d'Enregistrements : <b><?php echo $intTotalNumRows; ?></b> - Avec le Filtre : <b><?php echo $intNumRows; ?></b>
            </span>
            </p>
        </div>

        <div style="clear:both;">
            <p class="ploopi_va">
                <?php
                if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT))
                {
                    ?>
                    Export :
                    <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> XLS" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=XLS"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> XLS" src="./modules/forms/img/mime/xls.png">XLS</a>
                    <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> XLSX" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=XLSX"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> XLSX" src="./modules/forms/img/mime/xls.png">XLSX</a>
                    <?php
                    if (ploopi_getparam('system_jodwebservice', _PLOOPI_MODULE_SYSTEM) != '')
                    {
                    ?>
                        <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> ODS" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=ODS"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> ODS" src="./modules/forms/img/mime/ods.png">ODS</a>
                        <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> PDF" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=PDF"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> PDF" src="./modules/forms/img/mime/pdf.png">PDF</a>
                        <?php
                    }
                    ?>
                    <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> CSV" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=CSV"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> CSV" src="./modules/forms/img/mime/csv.png">CSV</a>
                    <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> HTML" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=HTML"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> HTML" src="./modules/forms/img/mime/html.png">HTML</a>
                    <a class="forms_export_link" title="<?php echo _FORMS_EXPORT; ?> XML" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_export&forms_id={$objForm->fields['id']}&forms_export_format=XML"); ?>"><img alt="<?php echo _FORMS_EXPORT; ?> title="<?php echo _FORMS_EXPORT; ?> XML" src="./modules/forms/img/mime/xml.png">XML</a>
                    <?php
                }
                ?>
                <span style="margin-left:10px;" >Impression :</span>
                <a class="forms_export_link" title="Impression XLS" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_print_array&forms_id={$objForm->fields['id']}&forms_export_format=XLS"); ?>"><img alt="Impression XLS" title="Impression XLS" src="./modules/forms/img/mime/xls.png">XLS</a>
                <a class="forms_export_link" title="Impression XLSX" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_print_array&forms_id={$objForm->fields['id']}&forms_export_format=XLSX"); ?>"><img alt="Impression XLSX" title="Impression XLSX" src="./modules/forms/img/mime/xls.png">XLSX</a>
                <?php
                if (ploopi_getparam('system_jodwebservice', _PLOOPI_MODULE_SYSTEM) != '')
                {
                ?>
                    <a class="forms_export_link" title="Impression ODS" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_print_array&forms_id={$objForm->fields['id']}&forms_export_format=ODS"); ?>"><img alt="Impression ODS" title="Impression ODS" src="./modules/forms/img/mime/ods.png">ODS</a>
                    <a class="forms_export_link" title="Impression PDF" href="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_print_array&forms_id={$objForm->fields['id']}&forms_export_format=PDF"); ?>"><img alt="Impression PDF" title="Impression PDF" src="./modules/forms/img/mime/pdf.png">PDF</a>
                <?
                }

                /* BM et CL - 11/2013 : ajout import csv si role ad hoc (Confer constante _FORMS_ACTION_IMPORT_CSV (action de code 8)) */
                if (ploopi_isactionallowed(_FORMS_ACTION_IMPORT_CSV)) {
                    ?>
                    <span style="margin-left:10px;" >Import : </span>
                    <a class="forms_export_link" title="<?php echo _FORMS_IMPORT; ?> CSV" href="#" onclick="javascript:ploopi_xmlhttprequest_topopup(450, event, 'forms_import', 'admin-light.php', '<?php echo ploopi_queryencode("ploopi_op=forms_import&forms_id={$objForm->fields['id']}&origin=viewreplies"); ?>', 'post');">
                    <img alt="<?php echo _FORMS_IMPORT; ?> title="<?php echo _FORMS_IMPORT; ?> CSV" src="./modules/forms/img/mime/csv.png">CSV</a>
                    <?php
                }
                /* Fin BM */

                if (ploopi_isactionallowed(_FORMS_ACTION_GRAPHICS))
                {
                    $db->query("
                        SELECT  *
                        FROM    ploopi_mod_forms_graphic
                        WHERE   id_form = {$objForm->fields['id']}
                    ");
                    if ($db->numrows())
                    {
                        ?>
                            <span style="margin-left:10px;">Graphique : </span>
                            <?php
                            ?>
                            <select class="select" onchange="javascript:if (this.value != '') {ploopi_xmlhttprequest_topopup(750, event, 'forms_popup_graphic', 'admin-light.php', 'ploopi_op=forms_graphic_display&forms_graphic_id='+this.value+'&forms_graphic_width='+$('forms_graphic_width').value, 'POST');} else {ploopi_hidepopup('forms_popup_graphic');} this.selectedIndex = 0;">
                                <option value="">(Sélectionner un graphique)</option>
                                <?php
                                while ($row = $db->fetchrow())
                                {
                                    ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo ploopi_htmlentities($row['label']); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span>Largeur (px) : </span>
                            <input type="text" class="text" value="" size="4" maxlength="5" id="forms_graphic_width" /> (vide = auto)
                        <?php
                    }
                }
                ?>
            </p>
        </div>

    </div>




    <div class="forms_viewlist">
        <table class="forms_viewlist">
        <?php
        $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
        ?>
        <tr style="background-color:<?php echo $color; ?>;">
            <?php
            foreach ($arrTitles as $strKey => $row)
            {
                if ($row['arrayview'] && (!$row['adminonly']  || $booIsAdmin))
                {
                    $style_col = $sort_cell = '';
                    $new_option = 'ASC';
                    if ($_SESSION['forms'][$objForm->fields['id']]['orderby'] == $strKey)
                    {
                        $new_option = ($_SESSION['forms'][$objForm->fields['id']]['option'] == 'DESC') ? 'ASC' : 'DESC';
                        $style_col = 'class="selected"';
                        $sort_cell = ($_SESSION['forms'][$objForm->fields['id']]['option'] == 'DESC') ? 'arrow_down' : 'arrow_up';
                    }

                    ?>
                    <th>
                        <a <?php echo $style_col; ?> href="<?php echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}&orderby={$strKey}&option={$new_option}"); ?>">
                        <p class="ploopi_va">
                            <span><?php echo ploopi_htmlentities($row['label']); ?></span>
                            <?
                            if ($_SESSION['forms'][$objForm->fields['id']]['orderby'] == $strKey)
                            { ?><img src="./modules/forms/img/<?php echo $sort_cell; ?>.png"><? }
                            ?>
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
        foreach ($arrData as $rowData)
        {
            $intReplyId = $rowData['id'];

            $color = (!isset($color) || $color == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1'];
            ?>
            <tr bgcolor="<?php echo $color; ?>">
                <?php
                foreach ($arrTitles as $strFieldId => $row)
                {
                    if ($row['arrayview'] && (!$row['adminonly']  || $booIsAdmin))
                    {
                        if (isset($rowData[$strFieldId]))
                        {
                            $strValue = $rowData[$strFieldId];

                            if (is_numeric($strFieldId))
                            {
                                switch($row['type'])
                                {

                                    case 'file':
                                        if ($strValue != '') $strValue = $strValue.'<a href="'.ploopi_urlencode("admin.php?ploopi_op=forms_download_file&forms_id={$objForm->fields['id']}&record_id={$intReplyId}&field_id={$strFieldId}").'"><img style="border:0px" src="./modules/forms/img/link.gif"></a>';
                                    break;

                                    case 'color':
                                        $strValue = '<div style="background-color:'.$strValue.';">&nbsp;&nbsp;</div>';
                                    break;

                                    default:
                                        $strValue = ploopi_make_links(str_replace('||','<br />',$strValue));
                                    break;
                                }
                            }
                        }
                        else $strValue = '';

                        // Alignement du contenu en fonction du format des données.
                        if ($row['format'] == 'float' || $row['format'] == 'integer') $strClass = 'data num';
                        else $strClass = 'data';

                        echo "<td class=\"{$strClass}\">{$strValue}</td>";
                    }
                }


                if ($_SESSION['ploopi']['action'] == 'public')
                {
                    ?>
                    <td align="left" nowrap>
                        <a title="Ouvrir" href="<?php echo ploopi_urlencode("admin.php?op=forms_reply_display&forms_id={$objForm->fields['id']}&record_id={$intReplyId}"); ?>"><img alt="ouvrir" border="0" src="./modules/forms/img/ico_display.png"></a>
                        <?php
                        // Droit de modif d'un enregistrement
                        if (ploopi_isadmin() || (
                                ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY) && (
                                    ($objForm->fields['option_modify'] == 'user' && $rowData['user_id'] == $_SESSION['ploopi']['userid']) ||
                                    ($objForm->fields['option_modify'] == 'group' && $rowData['workspace_id'] == $_SESSION['ploopi']['workspaceid'])  ||
                                    ($objForm->fields['option_modify'] == 'all')
                                )
                            ))
                        {
                            ?>
                            <a title="Modifier" href="<?php echo ploopi_urlencode("admin.php?op=forms_reply_modify&forms_id={$objForm->fields['id']}&record_id={$intReplyId}"); ?>"><img alt="ouvrir" border="0" src="./modules/forms/img/ico_modify.png"></a>
                            <?php
                        }
                        // Droit de suppression d'un enregistrement
                        if (ploopi_isadmin() || (
                                ploopi_isactionallowed(_FORMS_ACTION_DELETE) && (
                                    ($objForm->fields['option_modify'] == 'user' && $rowData['user_id'] == $_SESSION['ploopi']['userid']) ||
                                    ($objForm->fields['option_modify'] == 'group' && $rowData['workspace_id'] == $_SESSION['ploopi']['workspaceid'])  ||
                                    ($objForm->fields['option_modify'] == 'all')
                                )
                            ))
                        {
                            ?>
                            <a title="Supprimer" href="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin-light.php?ploopi_op=forms_reply_delete&forms_id={$objForm->fields['id']}&record_id={$intReplyId}"); ?>','<?php echo _PLOOPI_CONFIRM; ?>')"><img alt="supprimer" border="0" src="./modules/forms/img/ico_trash.png"></a>
                            <?php
                        }
                        ?>
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
        }
        ?>
        </table>
    </div>
</div>

<?php echo $skin->close_simplebloc(); ?>
