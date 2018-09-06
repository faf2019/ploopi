<?php
/*
    Copyright (c) 2007-2011 Ovensia
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
 * Interface de modification d'un groupe conditionnel
 *
 * @package forms
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */


/**
 * On commence par v�rifier si l'identifiant du groupe est valide.
 * Si ok => on l'ouvre. Sinon, nouveau groupe.
 */

$objGroup = new formsGroup();

if (!empty($_GET['forms_group_id']) && is_numeric($_GET['forms_group_id']) && $objGroup->open($_GET['forms_group_id']))
{
    $strTitle = _FORMS_GROUPMODIFICATION;
}
else
{
    $objGroup->init_description();
    $strTitle = _FORMS_GROUPCREATION;
}

echo $skin->open_simplebloc($strTitle);

$arrParams = array();
$arrParams[] = "ploopi_op=forms_group_save";
$arrParams[] = "forms_id={$objForm->fields['id']}";
if (!$objGroup->isnew()) $arrParams[] = "forms_group_id={$objGroup->fields['id']}";
?>
<form name="form_field" action="<?php echo ploopi_urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:return forms_group_validate(this);">
<div style="overflow:hidden">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><?php echo _FORMS_GROUP_LABEL; ?>:</label>
                <input type="text" class="text" name="forms_group_label" value="<?php echo ploopi_htmlentities($objGroup->fields['label']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_GROUP_DESCRIPTION; ?>:</label>
                <textarea class="text" name="forms_group_description"><?php echo ploopi_htmlentities($objGroup->fields['description']); ?></textarea>
            </p>
            <p>
                <label><?php echo _FORMS_GROUP_FORMULA; ?>:<br /><em>ex: (C1 AND C2) OR C3</em></label>
                <? for ($i = 1; $i <= ploopi_getparam('form_nb_cond'); $i++) { ?> <input type="button" class="button" value="C<? echo $i; ?>" title="Ins�rer la condition C<? echo $i; ?>" style="width:30px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), this.value); $('forms_group_formula').focus();" /> <? } ?>
                <input type="button" class="button" value="AND" title="Ins�rer l'op�rateur AND" style="width:40px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), ' '+this.value+' '); $('forms_group_formula').focus();" />
                <input type="button" class="button" value="OR" title="Ins�rer l'op�rateur OR" style="width:40px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), ' '+this.value+' '); $('forms_group_formula').focus();" />
                <input type="button" class="button" value="NOT" title="Ins�rer l'op�rateur NOT" style="width:40px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), ' '+this.value+' '); $('forms_group_formula').focus();" />
                <input type="button" class="button" value="(" title="Ins�rer une parenth�se ouvrante" style="width:20px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), this.value); $('forms_group_formula').focus();" />
                <input type="button" class="button" value=")" title="Ins�rer une parenth�se fermante" style="width:20px;" onclick="javascript:ploopi_insertatcursor($('forms_group_formula'), this.value); $('forms_group_formula').focus();" />
                <br />
                <input style="margin-top:2px;" type="text" class="text" name="forms_group_formula" id="forms_group_formula" value="<?php echo ploopi_htmlentities($objGroup->fields['formula']); ?>">
                <?
                // Test de l'expression bool�enne
                include_once './modules/forms/classes/formsBooleanParser.php';
                try {
                    $objParser = new formsBooleanParser($objGroup->fields['formula']);
                }
                catch (Exception $e) { echo '<br /><span><strong>'.$e->getMessage().'</strong></span>'; }
                ?>
            </p>
        </div>
    </div>

    <div style="float:left;width:49%;">
        <div class="ploopi_form" style="padding:4px;">
        <?
        include_once './include/functions/crypt.php';

        $arrConditions = $objGroup->getConditions();

        for ($intI = 1; $intI <= ploopi_getparam('form_nb_cond'); $intI++)
        {
            ?>
            <p>
                <strong><?php echo _FORMS_GROUP_CONDITION.' C'.$intI; ?></strong>
                <br />
                <select class="select" style="width:30%;" name="_forms_group_cond[<?php echo $intI; ?>][field]">
                    <option value="">(Champ)</option>
                    <?
                    foreach($arrFields as $intIdField => $arrField)
                    {
                        if (!$arrField['separator'] && !$arrField['captcha'] && !$arrField['html'])
                        {
                            ?>
                             <option value="<?php echo $intIdField; ?>" <?php echo (isset($arrConditions[$intI]['field']) && $intIdField == $arrConditions[$intI]['field']) ? 'selected="selected"' : ''; ?>><?php echo ploopi_htmlentities($arrField['name']); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <select class="select" style="width:20%;" name="_forms_group_cond[<?php echo $intI; ?>][op]">
                    <option value="">(Filtre)</option>
                    <?php
                    global $field_operators;
                    foreach($field_operators as $strKey => $strValue)
                    {
                         ?>
                         <option value="<?php echo ploopi_htmlentities($strKey, null, 'ISO-8859-1', false); ?>" <?php echo (isset($arrConditions[$intI]['op']) && $strKey == $arrConditions[$intI]['op']) ? 'selected="selected"' : ''; ?>><?php echo ploopi_htmlentities($strValue, null, 'ISO-8859-1', false); ?></option>
                         <?php
                    }
                    ?>
                </select>
                <input style="width:46%;" type="text" class="text" name="_forms_group_cond[<?php echo $intI; ?>][value]" value="<? echo isset($arrConditions[$intI]['value']) ? ploopi_htmlentities($arrConditions[$intI]['value']) : ''; ?>">
            </p>
            <?
        }
        ?>

        </div>
    </div>
</div>

<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_modify&forms_id={$objForm->fields['id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<?php
echo $skin->close_simplebloc();
?>
