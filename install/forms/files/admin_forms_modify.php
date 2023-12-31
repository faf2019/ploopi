<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Interface de modification d'un formulaire
 *
 * @package forms
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * On commence par vérifier si l'identifiant du formulaire est valide.
 * Si ok => on l'ouvre. Sinon, nouveau formulaire.
 */

$objForm = new formsForm();

if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
{
    $objForm->open($_GET['forms_id']);
    $title = _FORMS_MODIFICATION.' &laquo; '.ploopi\str::htmlentities($objForm->fields['label']).' &raquo;';
    $objForm->includeCss(); // permet la prévisu directe en popup ajax
}
else
{
    $objForm->init_description();
    $title = _FORMS_ADD;
}
echo ploopi\skin::get()->open_simplebloc($title);

$pubdate_start = ($objForm->fields['pubdate_start']) ? ploopi\date::timestamp2local($objForm->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($objForm->fields['pubdate_end']) ? ploopi\date::timestamp2local($objForm->fields['pubdate_end']) : array('date' => '');

$autobackup_date = ($objForm->fields['autobackup_date']) ? ploopi\date::timestamp2local($objForm->fields['autobackup_date']) : array('date' => '');

$objForm_tpl = array();
clearstatcache();
$ptrdir = @opendir('./modules/forms/templates/');
while ($dir = @readdir($ptrdir)) if ($dir != '.' && $dir != '..' && is_dir("./modules/forms/templates/{$dir}")) $objForm_tpl[] = $dir;

sort($objForm_tpl);
?>

<form name="frm_modify" action="<?php echo ploopi\crypt::urlencode("admin.php?ploopi_op=forms_save&forms_id={$objForm->fields['id']}"); ?>" method="post" onsubmit="javascript:return forms_validate(this);">
<div style="overflow:hidden">

    <?php
    if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'] = 'none';
    ?>

    <?php
    if ($objForm->new)
    {
        ?>
        <div class="ploopi_form_title">
            Paramétrage du formulaire
        </div>
        <?php
    }
    else
    {
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi.switchdisplay('forms_modify_options');ploopi.xhr.send('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_modify_options&display='+jQuery('#forms_modify_options')[0].style.display, true);">
            <span>Paramétrage du formulaire</span><span style="font-weight:normal;font-size:0.8em;margin-left:20px;">(cliquez pour ouvrir/fermer)</span>
        </a>
        <?php
    }
    ?>

    <div id="forms_modify_options" <?php if (!$objForm->new) echo 'style="display:'.$_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'].'"'; ?>>
        <div style="float:left;width:35%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label>*&nbsp;<?php echo _FORMS_LABEL; ?>:</label>
                    <input type="text" class="text" name="forms_label" value="<?php echo ploopi\str::htmlentities($objForm->fields['label']); ?>" />
                </p>
                <p>
                    <label>&nbsp;<?php echo _FORMS_TABLENAME; ?>:</label>
                    <span><?php echo ploopi\str::htmlentities($objForm->getDataTableName()); ?></span>
                </p>
                <p>
                    <label><?php echo _FORMS_PUBDATESTART; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_start" id="forms_pubdate_start" value="<?php echo ploopi\str::htmlentities($pubdate_start['date']); ?>">&nbsp;
                    <?php echo ploopi\date::open_calendar('forms_pubdate_start'); ?>
                </p>
                <p>
                    <label><?php echo _FORMS_PUBDATEEND; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_end" id="forms_pubdate_end" value="<?php echo ploopi\str::htmlentities($pubdate_end['date']); ?>">&nbsp;
                    <?php echo ploopi\date::open_calendar('forms_pubdate_end'); ?>
                </p>
                <p>
                    <label><?php echo _FORMS_DESCRIPTION; ?>:</label>
                    <textarea class="text" style="height:50px;" name="forms_description"><?php echo ploopi\str::htmlentities($objForm->fields['description']); ?></textarea>
                </p>
                <p>
                    <label><?php echo _FORMS_TYPEFORM; ?>:</label>
                    <select class="select" name="forms_typeform" onchange="javascript:forms_changetype(this);">
                    <?php
                    foreach($form_types as $key => $value)
                    {
                        $sel = ($objForm->fields['typeform'] == $key) ? 'selected' : '';
                        echo "<option $sel value=\"{$key}\">".ploopi\str::htmlentities($value)."</option>";
                    }
                    ?>
                    </select>
                </p>

                <?php
                //if ($objForm->fields['typeform'] == 'cms')
                ?>
                <div id="forms_type_cms" <?php if ($objForm->fields['typeform'] != 'cms') echo 'style="display:none;"'; ?>>
                    <p>
                        <label><?php echo _FORMS_RESPONSE; ?>:</label>
                        <textarea class="text" rows="3" name="forms_cms_response"><?php echo ploopi\str::htmlentities($objForm->fields['cms_response']); ?></textarea>
                    </p>
                </div>
                <?php
                //if ($objForm->fields['typeform'] == 'app')
                ?>
                <div id="forms_type_app" <?php if ($objForm->fields['typeform'] != 'app') echo 'style="display:none;"'; ?>>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_adminonly"><?php echo _FORMS_OPTION_ADMINONLY; ?>:</label>
                        <input type="checkbox" class="checkbox" name="forms_option_adminonly" id="forms_option_adminonly" value="1" <?php if ($objForm->fields['option_adminonly']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_onlyone"><?php echo _FORMS_OPTION_ONLYONE; ?>:</label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyone" id="forms_option_onlyone" value="1" <?php if ($objForm->fields['option_onlyone']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_onlyoneday"><?php echo _FORMS_OPTION_ONLYONEDAY; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyoneday" id="forms_option_onlyoneday" value="1" <?php if ($objForm->fields['option_onlyoneday']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_displayuser"><?php echo _FORMS_OPTION_DISPLAY_USER; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displayuser" id="forms_option_displayuser" value="1" <?php if ($objForm->fields['option_displayuser']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_displaygroup"><?php echo _FORMS_OPTION_DISPLAY_GROUP; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displaygroup" id="forms_option_displaygroup" value="1" <?php if ($objForm->fields['option_displaygroup']) echo 'checked'; ?> />
                    </p>
                </div>
            </div>
        </div>

        <div style="float:left;width:35%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label><?php echo _FORMS_MODEL; ?>:<br /><em>Back/Front</em></label>
                    <select class="select" name="forms_model">
                    <?php
                    foreach($objForm_tpl as $tpl_name)
                    {
                        ?>
                        <option <?php if ($objForm->fields['model'] == $tpl_name) echo 'selected'; ?> value="<?php echo ploopi\str::htmlentities($tpl_name); ?>"><?php echo ploopi\str::htmlentities($tpl_name); ?></option>
                        <?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_STYLE; ?>:</label>
                    <input type="text" class="text" name="forms_style" value="<?php echo ploopi\str::htmlentities($objForm->fields['style']); ?>">
                </p>
                <p>
                    <label><?php echo _FORMS_NBLINE; ?>:</label>
                    <input type="text" class="text" style="width:30px;"name="forms_nbline" value="<?php echo ploopi\str::htmlentities($objForm->fields['nbline']); ?>" />
                </p>
                <p>
                    <label style="cursor:pointer;" onclick="javascript:jQuery('#forms_option_displaydate')[0].checked = !jQuery('#forms_option_displaydate')[0].checked;"><?php echo _FORMS_OPTION_DISPLAY_DATE; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displaydate" id="forms_option_displaydate" value="1" <?php if ($objForm->fields['option_displaydate']) echo 'checked'; ?> />
                </p>

                <p>
                    <label style="cursor:pointer;" onclick="javascript:jQuery('#forms_option_displayip')[0].checked = !jQuery('#forms_option_displayip')[0].checked;"><?php echo _FORMS_OPTION_DISPLAY_IP; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displayip" id="forms_option_displayip" value="1" <?php if ($objForm->fields['option_displayip']) echo 'checked'; ?> />
                </p>

                <p>
                    <label><?php echo _FORMS_OPTION_MODIFY; ?>:</label>
                    <select class="select" name="forms_option_modify">
                        <option value="nobody" <?php if ($objForm->fields['option_modify'] == 'nobody') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_NOBODY; ?></option>
                        <option value="user" <?php if ($objForm->fields['option_modify'] == 'user') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_USER; ?></option>
                        <option value="group" <?php if ($objForm->fields['option_modify'] == 'group') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_GROUP; ?></option>
                        <option value="all" <?php if ($objForm->fields['option_modify'] == 'all') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_ALL; ?></option>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_OPTION_VIEW; ?>:</label>
                    <select class="select" name="forms_option_view">
                        <option value="private" <?php if ($objForm->fields['option_view'] == 'private') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_PRIVATE; ?></option>
                        <option value="global" <?php if ($objForm->fields['option_view'] == 'global') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_GLOBAL; ?></option>
                        <option value="asc" <?php if ($objForm->fields['option_view'] == 'asc') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_ASC; ?></option>
                        <option value="desc" <?php if ($objForm->fields['option_view'] == 'desc') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_DESC; ?></option>
                    </select>
                </p>
                <?php
                if (!$objForm->new)
                {
                    ?>
                    <p>
                        <label>Archiver les données plus anciennes que :</label>
                        <input type="text" class="text" style="width:30px;" name="forms_autobackup" value="<?php echo ploopi\str::htmlentities($objForm->fields['autobackup']); ?>">&nbsp;jours (0 = aucun archivage)
                    </p>
                    <p>
                        <label>Archiver les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" name="forms_autobackup_date" id="forms_autobackup_date" value="<?php echo ploopi\str::htmlentities($autobackup_date['date']); ?>">&nbsp;
                        <?php echo ploopi\date::open_calendar('forms_autobackup_date'); ?>
                    </p>
                    <p>
                        <label>Supprimer les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" id="forms_delete_date">&nbsp;
                        <?php echo ploopi\date::open_calendar('forms_delete_date'); ?>
                        <a href="javascript:void(0);" onclick="javascript:forms_deletedata('<?php echo $objForm->fields['id']; ?>', event);"><img src="./modules/forms/img/ico_trash.png" /></a>
                    </p>
                    <?php
                }
                ?>
            </div>
        </div>

        <div style="float:left;width:29%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <strong>Multi-page:</strong>
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_option_multidisplaysave">Afficher le bouton &laquo; Enregistrer &raquo; sur toutes les pages:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_multidisplaysave" id="forms_option_multidisplaysave" value="1" <?php if ($objForm->fields['option_multidisplaysave']) echo 'checked'; ?> />
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_option_multidisplaypages">Afficher les numéros de pages:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_multidisplaypages" id="forms_option_multidisplaypages" value="1" <?php if ($objForm->fields['option_multidisplaypages']) echo 'checked'; ?> />
                </p>
                <p>
                    <strong>Export (PDF/ODS/XLS):</strong>
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_export_landscape">Format paysage:</label>
                    <input type="checkbox" class="checkbox" name="forms_export_landscape" id="forms_export_landscape" value="1" <?php if ($objForm->fields['export_landscape']) echo 'checked'; ?> />
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_export_fitpage_width">Une seule page en largeur:</label>
                    <input type="checkbox" class="checkbox" name="forms_export_fitpage_width" id="forms_export_fitpage_width" value="1" <?php if ($objForm->fields['export_fitpage_width']) echo 'checked'; ?> />
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_export_fitpage_height">Une seule page en hauteur:</label>
                    <input type="checkbox" class="checkbox" name="forms_export_fitpage_height" id="forms_export_fitpage_height" value="1" <?php if ($objForm->fields['export_fitpage_height']) echo 'checked'; ?> />
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_export_border">Bordures:</label>
                    <input type="checkbox" class="checkbox" name="forms_export_border" id="forms_export_border" value="1" <?php if ($objForm->fields['export_border']) echo 'checked'; ?> />
                </p>
            </div>
        </div>

        <div style="clear:both;border-top:1px solid #a0a0a0;overflow:auto;">
        <?php ploopi\share::selectusers(_FORMS_OBJECT_FORM, $objForm->fields['id'], -1, 'Envoi des réponses par message à...', 'forms_send_email', './modules/forms/img/mail.png'); ?>
        </div>


        <div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;border-bottom:1px solid #a0a0a0;padding:4px;overflow:auto;">
            <div style="float:right;">
                <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?formsTabItem=formslist"); ?>'">
                <input type="reset" class="flatbutton" value="<?php echo _PLOOPI_RESET; ?>">
                <?php
                if (!$objForm->new) //désactivé
                {
                    ?>

                    <?php
                }
                ?>
                <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
            </div>

            <div>
                <?php
                if (!$objForm->new) //désactivé
                {
                    ?>
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_PREVIEW; ?>" onclick="javascript:ploopi.xhr.topopup(780, event, 'forms_preview', 'admin-light.php', '<?php echo ploopi\crypt::queryencode("ploopi_op=forms_preview&forms_id={$objForm->fields['id']}"); ?>', 'post');" />
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_VIEWRESULT; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?ploopi_action=public&op=forms_viewreplies&forms_id={$objForm->fields['id']}"); ?>'">
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_IMPORT; ?>" onclick="javascript:ploopi.xhr.topopup(450, event, 'forms_import', 'admin-light.php', '<?php echo ploopi\crypt::queryencode("ploopi_op=forms_import&forms_id={$objForm->fields['id']}"); ?>', 'post');" />
                    <?php
                }
                ?>
                <span>(*) <?php echo _FORMS_OBLIGATORY; ?></span>
            </div>
        </div>
    </div>

</div>

</form>

<?php
//
if (!$objForm->isnew())
{
    ?>
    <a name="fieldlist"></a>
    <div class="ploopi_form_title" style=""><?php echo _FORMS_FIELDLIST; ?></div>
    <?php
    switch($op)
    {
        case 'forms_field_modify':
        case 'forms_field_add':
            ?>
            <a name="addform"></a>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <?php include_once './modules/forms/admin_forms_field.php'; ?>
            </div>
            <?php
        break;

        case 'forms_separator_modify':
        case 'forms_separator_add':
            ?>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <a name="addform"></a>
            <?php include_once './modules/forms/admin_forms_separator.php'; ?>
            </div>
            <?php
        break;

        case 'forms_html_modify':
        case 'forms_html_add':
            ?>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <a name="addform"></a>
            <?php include_once './modules/forms/admin_forms_html.php'; ?>
            </div>
            <?php
        break;

        default:
            ?>
            <div style="clear:both;background-color:#d0d0d0;overflow:auto;text-align:right;padding:4px;border-bottom:1px solid #a0a0a0;">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_html_add&forms_id={$objForm->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDHTML; ?>">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_separator_add&forms_id={$objForm->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDSEPARATOR; ?>">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_field_add&forms_id={$objForm->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDFIELD; ?>">
            </div>
            <?php
        break;
    }

    $array_columns = array();
    $array_values = array();

    $array_columns['left']['pos'] = array(
        'label' => 'P.',
        'width' => 45,
        'options' => array('sort' => true, 'sort_flag' => SORT_NUMERIC)
     );

    $array_columns['auto']['name'] = array(
        'label' => _FORMS_FIELD_NAME,
        'options' => array('sort' => true)
    );

    $array_columns['right']['options'] = array(
        'label' => _FORMS_OPTIONS,
        'width' => 250,
        'options' => array('sort' => true)
    );


    $array_columns['right']['group'] = array(
        'label' => _FORMS_FIELD_GROUP,
        'width' => 140,
        'options' => array('sort' => true)
     );

    $array_columns['right']['type'] = array(
        'label' => _FORMS_FIELD_TYPE,
        'width' => 240,
        'options' => array('sort' => true)
    );


    $array_columns['actions_right']['actions'] = array('label' => '', 'width' => 74);

    $rs_fields = ploopi\db::get()->query("
        SELECT      f.*, g.label as g_label
        FROM        ploopi_mod_forms_field f
        LEFT JOIN   ploopi_mod_forms_group g ON g.id = f.id_group
        WHERE       f.id_form = {$objForm->fields['id']}
        ORDER BY    f.position
    ");

    $arrFields = ploopi\db::get()->getarray($rs_fields, true);

    $c=0;
    while ($row = ploopi\db::get()->fetchrow($rs_fields))
    {

        if ($row['separator'])
        {
            $arrOptions = array();
            if ($row['option_pagebreak']) $arrOptions[] = 'Saut de page';
            if ($row['option_wceview']) $arrOptions[] = 'Frontoffice';

            $array_values[$c]['values'] = array(
                'name' => array('label' => $row['name']),
                'group' => array('label' => $row['g_label']),
                'type' => array('label' => str_replace('<LEVEL>',$row['separator_level'],_FORMS_FIELD_SEPARATOR_DESC)),
                'options' => array('label' => implode(', ', $arrOptions)),
            );

            $array_values[$c]['values']['actions']      = array('label' => '
                <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_moveup&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
                <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_movedown&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
                <a style="margin-left:10px;" href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin.php?op=forms_field_delete&field_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
            ');

            $array_values[$c]['description'] = 'Ouvrir le Séparateur "'.ploopi\str::htmlentities($row['name']).'"';
            $array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=forms_separator_modify&forms_id={$objForm->fields['id']}&field_id={$row['id']}").'#addform';
            $array_values[$c]['style'] = 'background-color:#ddeeff';

        }
        elseif ($row['html'])
        {
            $arrOptions = array();
            if ($row['option_pagebreak']) $arrOptions[] = 'Saut de page';
            if ($row['option_wceview']) $arrOptions[] = 'Frontoffice';

            $array_values[$c]['values'] = array(
                'name' => array('label' => _FORMS_FIELD_XHTMLCONTENT),
                'group' => array('label' => $row['g_label']),
                'type' => array('label' => _FORMS_FIELD_XHTMLCONTENT),
                'options' => array('label' => implode(', ', $arrOptions)),
            );

            $array_values[$c]['values']['actions']      = array('label' => '
                <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_moveup&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
                <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_movedown&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
                <a style="margin-left:10px;" href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin.php?op=forms_field_delete&field_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
            ');

            $array_values[$c]['description'] = 'Ouvrir le Contenu HTML';
            $array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=forms_html_modify&forms_id={$objForm->fields['id']}&field_id={$row['id']}").'#addform';
            $array_values[$c]['style'] = 'background-color:#ddffee';

        }
        else
        {
            $arrOptions = array();
            if ($row['option_formview']) $arrOptions[] = 'Formulaire';
            if ($row['option_arrayview']) $arrOptions[] = 'Liste';
            if ($row['option_exportview']) $arrOptions[] = 'Export';
            if ($row['option_needed']) $arrOptions[] = 'Requis';
            if ($row['option_adminonly']) $arrOptions[] = 'Admin';
            if ($row['option_pagebreak']) $arrOptions[] = 'Saut de page';
            if ($row['option_wceview']) $arrOptions[] = 'Frontoffice';

            $array_values[$c]['values'] = array(
                'name' => array('label' => ploopi\str::htmlentities($row['name'])),
                'group' => array('label' => ploopi\str::htmlentities($row['g_label'])),
                'type' => array('label' => ploopi\str::htmlentities($field_types[$row['type']].( ($row['type'] == 'text' && isset($field_formats[$row['format']])) ? " ( {$field_formats[$row['format']]} )" : ''))),
                'options' => array('label' => ploopi\str::htmlentities(implode(', ', $arrOptions))),
            );

            $array_values[$c]['description'] = 'Ouvrir le Champ "'.ploopi\str::htmlentities($row['name']).'"';
            $array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=forms_field_modify&forms_id={$objForm->fields['id']}&field_id={$row['id']}")."#addform";
        }


        $array_values[$c]['values']['pos'] = array('label' =>  $row['position']);
        $array_values[$c]['values']['actions'] = array('label' => '
            <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_moveup&forms_id={$objForm->fields['id']}&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
            <a href="'.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_movedown&forms_id={$objForm->fields['id']}&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
            <a style="margin-left:10px;" href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin.php?ploopi_op=forms_field_delete&forms_id={$objForm->fields['id']}&field_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        if (isset($_GET['field_id']) && $row['id'] == $_GET['field_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    ploopi\skin::get()->display_array($array_columns, $array_values, 'forms_field_list', array('sortable' => true, 'orderby_default' => 'pos'));
    ?>

    <div class="ploopi_form_title" style=""><?php echo _FORMS_GROUPLIST; ?></div>
    <?php
    switch($op)
    {
        case 'forms_group_modify':
        case 'forms_group_add':
            ?>
            <a name="addgroup"></a>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <?php include_once './modules/forms/admin_forms_group.php'; ?>
            </div>
            <?php
        break;

        default:
            ?>
            <div style="clear:both;background-color:#d0d0d0;overflow:auto;text-align:right;padding:4px;border-bottom:1px solid #a0a0a0;">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_group_add&forms_id={$objForm->fields['id']}"); ?>#addgroup'" class="flatbutton" value="<?php echo _FORMS_ADDGROUP; ?>">
            </div>
            <?php
        break;
    }

    $array_columns = array();
    $array_values = array();

    $array_columns['left']['label'] = array(
        'label' => _FORMS_GROUP_LABEL,
        'width' => 200,
        'options' => array('sort' => true)
     );

    $array_columns['auto']['description'] = array(
        'label' => _FORMS_GROUP_DESCRIPTION,
        'options' => array('sort' => true)
    );

    $array_columns['right']['formula'] = array(
        'label' => _FORMS_GROUP_FORMULA,
        'width' => 250,
        'options' => array('sort' => true)
     );

    $array_columns['actions_right']['actions'] = array('label' => '', 'width' => 24);

    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_group
            WHERE   id_form = {$objForm->fields['id']}
            ";

    $rs_fields = ploopi\db::get()->query($sql);

    $c=0;
    while ($row = ploopi\db::get()->fetchrow($rs_fields))
    {
        $array_values[$c]['values']['label'] = array('label' => ploopi\str::htmlentities($row['label']));
        $array_values[$c]['values']['description'] = array('label' =>  ploopi\str::nl2br(ploopi\str::htmlentities($row['description'])));
        $array_values[$c]['values']['formula'] = array('label' =>  ploopi\str::htmlentities($row['formula']));
        $array_values[$c]['values']['actions']  = array('label' => '
            <a href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin-light.php?ploopi_op=forms_group_delete&forms_id={$objForm->fields['id']}&forms_group_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        $array_values[$c]['description'] = 'Ouvrir le Groupe &laquo; '.ploopi\str::htmlentities($row['label']).' &raquo;';
        $array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=forms_group_modify&forms_id={$objForm->fields['id']}&forms_group_id={$row['id']}").'#addgroup';

        if (isset($_GET['forms_group_id']) && $row['id'] == $_GET['forms_group_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    ploopi\skin::get()->display_array($array_columns, $array_values, 'forms_group_list', array('sortable' => true, 'orderby_default' => 'label'));
    ?>


    <div class="ploopi_form_title" style=""><?php echo _FORMS_GRAPHICLIST; ?></div>
    <?php
    switch($op)
    {
        case 'forms_graphic_modify':
        case 'forms_graphic_add':
            ?>
            <a name="addgraphic"></a>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <?php include_once './modules/forms/admin_forms_graphic.php'; ?>
            </div>
            <?php
        break;

        default:
            ?>
            <div style="clear:both;background-color:#d0d0d0;overflow:auto;text-align:right;padding:4px;border-bottom:1px solid #a0a0a0;">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_graphic_add&forms_id={$objForm->fields['id']}"); ?>#addgraphic'" class="flatbutton" value="<?php echo _FORMS_ADDGRAPHIC; ?>">
            </div>
            <?php
        break;
    }

    $array_columns = array();
    $array_values = array();

    $array_columns['left']['label'] = array(
        'label' => _FORMS_GRAPHIC_LABEL,
        'width' => 200,
        'options' => array('sort' => true)
     );

    $array_columns['auto']['description'] = array(
        'label' => _FORMS_GRAPHIC_DESCRIPTION,
        'options' => array('sort' => true)
    );

    $array_columns['right']['line_aggregation'] = array(
        'label' => _FORMS_GRAPHIC_LINE_AGGREGATION,
        'width' => 150,
        'options' => array('sort' => true)
    );

    $array_columns['right']['type'] = array(
        'label' => _FORMS_GRAPHIC_TYPE,
        'width' => 200,
        'options' => array('sort' => true)
    );

    $array_columns['actions_right']['actions'] = array('label' => '', 'width' => 24);

    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_graphic
            WHERE   id_form = {$objForm->fields['id']}
            ";

    $rs_fields = ploopi\db::get()->query($sql);

    $c=0;
    while ($row = ploopi\db::get()->fetchrow($rs_fields))
    {
        $array_values[$c]['values']['label'] = array('label' => ploopi\str::htmlentities($row['label']));
        $array_values[$c]['values']['description'] = array('label' =>  ploopi\str::nl2br(ploopi\str::htmlentities($row['description'])));
        $array_values[$c]['values']['type'] = array('label' => ploopi\str::htmlentities(isset($forms_graphic_types[$row['type']]) ? $forms_graphic_types[$row['type']] : ''));
        $array_values[$c]['values']['line_aggregation'] = array('label' => isset($forms_graphic_line_aggregation[$row['line_aggregation']]) ? $forms_graphic_line_aggregation[$row['line_aggregation']] : '');
        $array_values[$c]['values']['actions']  = array('label' => '
            <a href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin-light.php?ploopi_op=forms_graphic_delete&forms_id={$objForm->fields['id']}&forms_graphic_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        $array_values[$c]['description'] = 'Ouvrir le Graphique &laquo; '.ploopi\str::htmlentities($row['label']).' &raquo;';
        $array_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=forms_graphic_modify&forms_id={$objForm->fields['id']}&forms_graphic_id={$row['id']}").'#addgraphic';

        if (isset($_GET['forms_graphic_id']) && $row['id'] == $_GET['forms_graphic_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    ploopi\skin::get()->display_array($array_columns, $array_values, 'forms_graphic_list', array('sortable' => true, 'orderby_default' => 'label'));


}

echo ploopi\skin::get()->close_simplebloc();
?>
