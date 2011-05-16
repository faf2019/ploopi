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
 * Interface de modification d'un formulaire
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * On commence par v�rifier si l'identifiant du formulaire est valide.
 * Si ok => on l'ouvre. Sinon, nouveau formulaire.
 */
$booCaptchaInForm = false; // Le formulaire ne contient pas de captcha

$forms = new formsForm();

if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
{
    $forms->open($_GET['forms_id']);
    $booCaptchaInForm = $forms->captchainform();
    $title = _FORMS_MODIFICATION.' &laquo; '.$forms->fields['label'].' &raquo;';

    $forms->includeCss(); // permet la pr�visu directe en popup ajax
}
else
{
    $forms->init_description();
    $title = _FORMS_ADD;
}
echo $skin->open_simplebloc($title);

$pubdate_start = ($forms->fields['pubdate_start']) ? ploopi_timestamp2local($forms->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($forms->fields['pubdate_end']) ? ploopi_timestamp2local($forms->fields['pubdate_end']) : array('date' => '');

$autobackup_date = ($forms->fields['autobackup_date']) ? ploopi_timestamp2local($forms->fields['autobackup_date']) : array('date' => '');

$forms_tpl = array();
clearstatcache();
$ptrdir = @opendir('./modules/forms/templates/');
while ($dir = @readdir($ptrdir)) if ($dir != '.' && $dir != '..' && is_dir("./modules/forms/templates/{$dir}")) $forms_tpl[] = $dir;

sort($forms_tpl);
?>

<form name="frm_modify" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=forms_save&forms_id={$forms->fields['id']}"); ?>" method="post" onsubmit="javascript:return forms_validate(this);">
<div style="overflow:hidden">

    <?php
    if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'] = 'none';
    ?>

    <?php
    if ($forms->new)
    {
        ?>
        <div class="ploopi_form_title">
            Param�trage du formulaire
        </div>
        <?php
    }
    else
    {
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_modify_options');ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=forms_xml_switchdisplay&switch=forms_modify_options&display='+$('forms_modify_options').style.display, true);">
            <span>Param�trage du formulaire</span><span style="font-weight:normal;font-size:0.8em;margin-left:20px;">(cliquez pour ouvrir/fermer)</span>
        </a>
        <?php
    }
    ?>

    <div id="forms_modify_options" <?php if (!$forms->new) echo 'style="display:'.$_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'].'"'; ?>>
        <div style="float:left;width:50%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label>*&nbsp;<?php echo _FORMS_LABEL; ?>:</label>
                    <input type="text" class="text" name="forms_label" value="<?php echo htmlentities($forms->fields['label']); ?>" />
                </p>
                <p>
                    <label><?php echo _FORMS_PUBDATESTART; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_start" id="forms_pubdate_start" value="<?php echo $pubdate_start['date']; ?>">&nbsp;
                    <?php echo ploopi_open_calendar('forms_pubdate_start'); ?>
                </p>
                <p>
                    <label><?php echo _FORMS_PUBDATEEND; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_end" id="forms_pubdate_end" value="<?php echo $pubdate_end['date']; ?>">&nbsp;
                    <?php echo ploopi_open_calendar('forms_pubdate_end'); ?>
                </p>
                <p>
                    <label><?php echo _FORMS_DESCRIPTION; ?>:</label>
                    <textarea class="text" style="height:50px;" name="forms_description"><?php echo htmlentities($forms->fields['description']); ?></textarea>
                </p>
                <p>
                    <label><?php echo _FORMS_MODEL; ?>:<br /><em>Back/Front</em></label>
                    <select class="select" name="forms_model">
                    <?php
                    foreach($forms_tpl as $tpl_name)
                    {
                        ?>
                        <option <?php if ($forms->fields['model'] == $tpl_name) echo 'selected'; ?> value="<?php echo htmlentities($tpl_name); ?>"><?php echo htmlentities($tpl_name); ?></option>
                        <?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_STYLE; ?>:</label>
                    <input type="text" class="text" name="forms_style" value="<?php echo htmlentities($forms->fields['style']); ?>">
                </p>
                <p>
                    <label><?php echo _FORMS_TYPEFORM; ?>:</label>
                    <select class="select" name="forms_typeform" onchange="javascript:forms_changetype(this);">
                    <?php
                    foreach($form_types as $key => $value)
                    {
                        $sel = ($forms->fields['typeform'] == $key) ? 'selected' : '';
                        echo "<option $sel value=\"{$key}\">{$value}</option>";
                    }
                    ?>
                    </select>
                </p>

                <?php
                //if ($forms->fields['typeform'] == 'cms')
                ?>
                <div id="forms_type_cms" <?php if ($forms->fields['typeform'] != 'cms') echo 'style="display:none;"'; ?>>
                    <p>
                        <label><?php echo _FORMS_RESPONSE; ?>:</label>
                        <textarea class="text" rows="3" name="forms_cms_response"><?php echo htmlentities($forms->fields['cms_response']); ?></textarea>
                    </p>
                </div>
                <?php
                //if ($forms->fields['typeform'] == 'app')
                ?>
                <div id="forms_type_app" <?php if ($forms->fields['typeform'] != 'app') echo 'style="display:none;"'; ?>>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_adminonly"><?php echo _FORMS_OPTION_ADMINONLY; ?>:</label>
                        <input type="checkbox" class="checkbox" name="forms_option_adminonly" id="forms_option_adminonly" value="1" <?php if ($forms->fields['option_adminonly']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_onlyone"><?php echo _FORMS_OPTION_ONLYONE; ?>:</label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyone" id="forms_option_onlyone" value="1" <?php if ($forms->fields['option_onlyone']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_onlyoneday"><?php echo _FORMS_OPTION_ONLYONEDAY; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyoneday" id="forms_option_onlyoneday" value="1" <?php if ($forms->fields['option_onlyoneday']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_displayuser"><?php echo _FORMS_OPTION_DISPLAY_USER; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displayuser" id="forms_option_displayuser" value="1" <?php if ($forms->fields['option_displayuser']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" for="forms_option_displaygroup"><?php echo _FORMS_OPTION_DISPLAY_GROUP; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displaygroup" id="forms_option_displaygroup" value="1" <?php if ($forms->fields['option_displaygroup']) echo 'checked'; ?> />
                    </p>
                </div>
            </div>
        </div>

        <div style="float:left;width:49%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label><?php echo _FORMS_EMAIL; ?>:</label>
                    <input type="text" class="text" size="30" name="forms_email" value="<?php echo htmlentities($forms->fields['email']); ?>" />
                </p>
                <p style="padding-top: 0; font-size: 10px; text-align: center;"><?php echo _FORMS_EMAIL_EXPLAIN; ?></p>
                <p>
                    <label><?php echo _FORMS_FROM; ?>:</label>
                    <input type="text" class="text" size="30" name="forms_email_from" value="<?php echo htmlentities($forms->fields['email_from']); ?>" />
                </p>
                <p>
                    <label><?php echo _FORMS_NBLINE; ?>:</label>
                    <input type="text" class="text" style="width:30px;"name="forms_nbline" value="<?php echo $forms->fields['nbline']; ?>" />
                </p>
                <p>
                    <label style="cursor:pointer;" onclick="javascript:$('forms_option_displaydate').checked = !$('forms_option_displaydate').checked;"><?php echo _FORMS_OPTION_DISPLAY_DATE; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displaydate" id="forms_option_displaydate" value="1" <?php if ($forms->fields['option_displaydate']) echo 'checked'; ?> />
                </p>

                <p>
                    <label style="cursor:pointer;" onclick="javascript:$('forms_option_displayip').checked = !$('forms_option_displayip').checked;"><?php echo _FORMS_OPTION_DISPLAY_IP; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displayip" id="forms_option_displayip" value="1" <?php if ($forms->fields['option_displayip']) echo 'checked'; ?> />
                </p>

                <p>
                    <label><?php echo _FORMS_OPTION_MODIFY; ?>:</label>
                    <select class="select" name="forms_option_modify">
                        <option value="nobody" <?php if ($forms->fields['option_modify'] == 'nobody') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_NOBODY; ?></option>
                        <option value="user" <?php if ($forms->fields['option_modify'] == 'user') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_USER; ?></option>
                        <option value="group" <?php if ($forms->fields['option_modify'] == 'group') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_GROUP; ?></option>
                        <option value="all" <?php if ($forms->fields['option_modify'] == 'all') echo 'selected'; ?>><?php echo _FORMS_OPTION_MODIFY_ALL; ?></option>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_OPTION_VIEW; ?>:</label>
                    <select class="select" name="forms_option_view">
                        <option value="private" <?php if ($forms->fields['option_view'] == 'private') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_PRIVATE; ?></option>
                        <option value="global" <?php if ($forms->fields['option_view'] == 'global') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_GLOBAL; ?></option>
                        <option value="asc" <?php if ($forms->fields['option_view'] == 'asc') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_ASC; ?></option>
                        <option value="desc" <?php if ($forms->fields['option_view'] == 'desc') echo 'selected'; ?>><?php echo _FORMS_OPTION_VIEW_DESC; ?></option>
                    </select>
                </p>
                <?php
                if (!$forms->new)
                {
                    ?>
                    <p>
                        <label>Archiver les donn�es plus anciennes que :</label>
                        <input type="text" class="text" style="width:30px;" name="forms_autobackup" value="<?php echo $forms->fields['autobackup']; ?>">&nbsp;jours (0 = aucun archivage)
                    </p>
                    <p>
                        <label>Archiver les donn�es jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" name="forms_autobackup_date" id="forms_autobackup_date" value="<?php echo $autobackup_date['date']; ?>">&nbsp;
                        <?php echo ploopi_open_calendar('forms_autobackup_date'); ?>
                    </p>
                    <p>
                        <label>Supprimer les donn�es jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" id="forms_delete_date">&nbsp;
                        <?php echo ploopi_open_calendar('forms_delete_date'); ?>
                        <a href="javascript:void(0);" onclick="javascript:forms_deletedata('<?php echo $forms->fields['id']; ?>', event);"><img src="./modules/forms/img/ico_trash.png" /></a>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <strong>Multi-page:</strong>
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_option_multidisplaysave">Afficher le bouton &laquo; Enregistrer &raquo; sur toutes les pages:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_multidisplaysave" id="forms_option_multidisplaysave" value="1" <?php if ($forms->fields['option_multidisplaysave']) echo 'checked'; ?> />
                </p>
                <p>
                    <label style="cursor:pointer;" for="forms_option_multidisplaypages">Afficher les num�ros de pages:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_multidisplaypages" id="forms_option_multidisplaypages" value="1" <?php if ($forms->fields['option_multidisplaypages']) echo 'checked'; ?> />
                </p>
            </div>
        </div>

        <div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;border-bottom:1px solid #a0a0a0;padding:4px;overflow:auto;">
            <div style="float:right;">
                <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?formsTabItem=formslist"); ?>'">
                <input type="reset" class="flatbutton" value="<?php echo _PLOOPI_RESET; ?>">
                <?php
                if (!$forms->new) //d�sactiv�
                {
                    ?>

                    <?php
                }
                ?>
                <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
            </div>

            <div>
                <?php
                if (!$forms->new) //d�sactiv�
                {
                    ?>
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_PREVIEW; ?>" onclick="javascript:ploopi_xmlhttprequest_topopup(780, event, 'forms_preview', 'admin-light.php', '<?php echo ploopi_queryencode("ploopi_op=forms_preview&forms_id={$forms->fields['id']}"); ?>', 'post');" />
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_VIEWRESULT; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?ploopi_action=public&op=forms_viewreplies&forms_id={$forms->fields['id']}"); ?>'">
                        <input type="button" class="flatbutton" value="<?php echo _FORMS_IMPORT; ?>" onclick="javascript:ploopi_xmlhttprequest_topopup(450, event, 'forms_import', 'admin-light.php', '<?php echo ploopi_queryencode("ploopi_op=forms_import&forms_id={$forms->fields['id']}"); ?>', 'post');" />
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
if (!$forms->isnew())
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

        /*
        case 'forms_captcha_modify':
        case 'forms_captcha_add':
            ?>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <a name="addform"></a>
            <?php include_once './modules/forms/admin_forms_captcha.php'; ?>
            </div>
            <?php
        break;
        */

        default:
            ?>
            <div style="clear:both;background-color:#d0d0d0;overflow:auto;text-align:right;padding:4px;border-bottom:1px solid #a0a0a0;">
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_separator_add&forms_id={$forms->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDSEPARATOR; ?>">
                <?php
                /*
                if(!$booCaptchaInForm)
                {
                    ?>
                    <input type="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_captcha_add&forms_id={$forms->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDCAPTCHA; ?>">
                    <?php
                }
                */
                ?>
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_field_add&forms_id={$forms->fields['id']}"); ?>#addform'" class="flatbutton" value="<?php echo _FORMS_ADDFIELD; ?>">
            </div>
            <?php
        break;
    }

    $array_columns = array();
    $array_values = array();

    $array_columns['left']['pos'] = array(
        'label' => 'P.',
        'width' => 35,
         'options' => array('sort' => true)
     );

    $array_columns['auto']['name'] = array(
        'label' => _FORMS_FIELD_NAME,
        'options' => array('sort' => true)
    );

    $array_columns['right']['admin'] = array(
        'label' => _FORMS_FIELD_ADMINONLY_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['export'] = array(
        'label' => _FORMS_FIELD_EXPORTVIEW_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['array'] = array(
        'label' => _FORMS_FIELD_ARRAYVIEW_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['pagebreak'] = array(
        'label' => _FORMS_FIELD_PAGEBREAK_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['needed'] = array(
        'label' => _FORMS_FIELD_NEEDED_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['form'] = array(
        'label' => _FORMS_FIELD_FORMVIEW_SHORT,
        'width' => 55,
        'options' => array('sort' => true)
    );

    $array_columns['right']['type'] = array(
        'label' => _FORMS_FIELD_TYPE,
        'width' => 250,
        'options' => array('sort' => true)
    );

    $array_columns['actions_right']['actions'] = array('label' => '', 'width' => 74);

    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_field
            WHERE   id_form = {$forms->fields['id']}
            ORDER BY position
            ";

    $rs_fields = $db->query($sql);
    $arrFields = $db->getarray($rs_fields, true);

    $c=0;
    while ($row = $db->fetchrow($rs_fields))
    {

        if ($row['separator'])
        {
            $array_values[$c]['values'] = array(
                'name' => array('label' => $row['name']),
                'type' => array('label' => str_replace('<LEVEL>',$row['separator_level'],_FORMS_FIELD_SEPARATOR_DESC)),
                'admin' => array('label' => '&nbsp;'),
                'export' => array('label' => '&nbsp;'),
                'array' => array('label' => '&nbsp;'),
                'needed' => array('label' => '&nbsp;'),
                'pagebreak' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_pagebreak']) ? 'un' : '').'checked.gif">'),
                'form' => array('label' => '&nbsp;')
            );

            $array_values[$c]['values']['actions']      = array('label' => '
                <a href="'.ploopi_urlencode("admin.php?ploopi_op=forms_field_moveup&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
                <a href="'.ploopi_urlencode("admin.php?ploopi_op=forms_field_movedown&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
                <a style="margin-left:10px;" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=forms_field_delete&field_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
            ');

            $array_values[$c]['description'] = 'Ouvrir le S�parateur "'.htmlentities($row['name']).'"';
            $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=forms_separator_modify&forms_id={$forms->fields['id']}&field_id={$row['id']}").'#addform';

        }
        elseif ($row['captcha'])
        {
            $array_values[$c]['values'] = array(
                'name' => array('label' =>  $row['name']),
                'type' => array('label' =>  'Captcha'),
                'admin' => array('label' => '&nbsp;'),
                'export' => array('label' => '&nbsp;'),
                'array' => array('label' => '&nbsp;'),
                'needed' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_needed']) ? 'un' : '').'checked.gif">'),
                'pagebreak' => array('label' => '<img src="./modules/forms/img/checked.gif">'),
                'form' => array('label' =>  '&nbsp;'),
            );

            $array_values[$c]['description'] = 'Ouvrir le Captcha "'.htmlentities($row['name']).'"';
            $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=forms_captcha_modify&forms_id={$forms->fields['id']}&field_id={$row['id']}");

        }
        else
        {
            $array_values[$c]['values'] = array(
                'name' => array('label' => $row['name']),
                'type' => array('label' => $field_types[$row['type']].( ($row['type'] == 'text' && isset($field_formats[$row['format']])) ? " ( {$field_formats[$row['format']]} )" : '')),
                'admin' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_adminonly']) ? 'un' : '').'checked.gif">'),
                'export' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_exportview']) ? 'un' : '').'checked.gif">'),
                'array' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_arrayview']) ? 'un' : '').'checked.gif">'),
                'needed' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_needed']) ? 'un' : '').'checked.gif">'),
                'pagebreak' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_pagebreak']) ? 'un' : '').'checked.gif">'),
                'form' => array('label' => '<img src="./modules/forms/img/'.((!$row['option_formview']) ? 'un' : '').'checked.gif">'),
            );

            $array_values[$c]['description'] = 'Ouvrir le Champ "'.htmlentities($row['name']).'"';
            $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=forms_field_modify&forms_id={$forms->fields['id']}&field_id={$row['id']}")."#addform";
        }


        $array_values[$c]['values']['pos'] = array('label' =>  $row['position']);
        $array_values[$c]['values']['actions'] = array('label' => '
            <a href="'.ploopi_urlencode("admin.php?ploopi_op=forms_field_moveup&forms_id={$forms->fields['id']}&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
            <a href="'.ploopi_urlencode("admin.php?ploopi_op=forms_field_movedown&forms_id={$forms->fields['id']}&field_id={$row['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
            <a style="margin-left:10px;" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?ploopi_op=forms_field_delete&forms_id={$forms->fields['id']}&field_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        if (isset($_GET['field_id']) && $row['id'] == $_GET['field_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    $skin->display_array($array_columns, $array_values, 'forms_field_list', array('sortable' => true, 'orderby_default' => 'pos'));
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
                <input type="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_graphic_add&forms_id={$forms->fields['id']}"); ?>#addgraphic'" class="flatbutton" value="<?php echo _FORMS_ADDGRAPHIC; ?>">
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
            WHERE   id_form = {$forms->fields['id']}
            ";

    $rs_fields = $db->query($sql);

    $c=0;
    while ($row = $db->fetchrow($rs_fields))
    {
        $array_values[$c]['values']['label'] = array('label' =>  $row['label']);
        $array_values[$c]['values']['description'] = array('label' =>  ploopi_nl2br($row['description']));
        $array_values[$c]['values']['type'] = array('label' => isset($forms_graphic_types[$row['type']]) ? $forms_graphic_types[$row['type']] : '');
        $array_values[$c]['values']['line_aggregation'] = array('label' => isset($forms_graphic_line_aggregation[$row['line_aggregation']]) ? $forms_graphic_line_aggregation[$row['line_aggregation']] : '');
        $array_values[$c]['values']['actions']  = array('label' => '
            <a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin-light.php?ploopi_op=forms_graphic_delete&forms_id={$forms->fields['id']}&forms_graphic_id={$row['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        $array_values[$c]['description'] = 'Ouvrir le Graphique &laquo; '.htmlentities($row['label']).' &raquo;';
        $array_values[$c]['link'] = ploopi_urlencode("admin.php?op=forms_graphic_modify&forms_id={$forms->fields['id']}&forms_graphic_id={$row['id']}").'#addgraphic';

        if (isset($_GET['forms_graphic_id']) && $row['id'] == $_GET['forms_graphic_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    $skin->display_array($array_columns, $array_values, 'forms_graphic_list', array('sortable' => true, 'orderby_default' => 'label'));


}

echo $skin->close_simplebloc();
?>
