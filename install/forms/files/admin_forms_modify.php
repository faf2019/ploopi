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
 * Interface de modification d'un formulaire
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On commence par vérifier si l'identifiant du formulaire est valide.
 * Si ok => on l'ouvre. Sinon, nouveau formulaire.
 */

$forms = new form();

if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
{
    $forms->open($_GET['forms_id']);
    $title = _FORMS_MODIFICATION.' &laquo; '.$forms->fields['label'].' &raquo;';
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
?>

<form name="frm_modify" action="<? echo $scriptenv; ?>" method="post" onsubmit="javascript:return forms_validate(this);">
<input type="hidden" name="op" value="forms_save" />
<?
if (!$forms->new)
{
    ?>
    <input type="hidden" name="forms_id" value="<? echo $forms->fields['id']; ?>" />
    <?
}
?>
<div style="overflow:hidden">

    <?
    if (!isset($_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'])) $_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'] = 'none';
    ?>

    <?
    if ($forms->new)
    {
        ?>
        <div class="ploopi_form_title">
            Paramétrage du formulaire
        </div>
        <?
    }
    else
    {
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('forms_modify_options');ploopi_xmlhttprequest('index-quick.php', 'ploopi_op=forms_xml_switchdisplay&switch=forms_modify_options&display='+$('forms_modify_options').style.display, true);">
            <span>Paramétrage du formulaire</span><span style="font-weight:normal;font-size:0.8em;margin-left:20px;">(cliquez pour ouvrir/fermer)</span>
        </a>
        <?
    }
    ?>

    <div id="forms_modify_options" <? if (!$forms->new) echo 'style="display:'.$_SESSION['forms'][$_SESSION['ploopi']['moduleid']]['forms_modify_options'].'"'; ?>>
        <div style="float:left;width:50%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label>*&nbsp;<? echo _FORMS_LABEL; ?>:</label>
                    <input type="text" class="text" name="forms_label" value="<? echo htmlentities($forms->fields['label']); ?>" />
                </p>
                <?
                if ($forms->fields['tablename'] == '') $forms->fields['tablename'] = forms_createphysicalname($forms->fields['label']);
                ?>
                <!--p>
                    <label><? echo _FORMS_TABLENAME; ?>:</label>
                    <input type="text" class="text" style="width:150px;" name="forms_tablename" value="<? echo htmlentities($forms->fields['tablename']); ?>" /> (form_xxxxx)
                </p-->
                <p>
                    <label><? echo _FORMS_PUBDATESTART; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_start" id="forms_pubdate_start" value="<? echo $pubdate_start['date']; ?>">&nbsp;<a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('forms_pubdate_start', event);" /><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                </p>
                <p>
                    <label><? echo _FORMS_PUBDATEEND; ?>:</label>
                    <input type="text" class="text" style="width:70px;" name="forms_pubdate_end" id="forms_pubdate_end" value="<? echo $pubdate_end['date']; ?>">&nbsp;<a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('forms_pubdate_end', event);" /><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                </p>
                <p>
                    <label><? echo _FORMS_DESCRIPTION; ?>:</label>
                    <textarea class="text" style="height:50px;" name="forms_description"><? echo htmlentities($forms->fields['description']); ?></textarea>
                </p>
                <p>
                    <label><? echo _FORMS_TYPEFORM; ?>:</label>
                    <select class="select" name="forms_typeform" onchange="javascript:forms_changetype(this);">
                    <?
                    foreach($form_types as $key => $value)
                    {
                        $sel = ($forms->fields['typeform'] == $key) ? 'selected' : '';
                        echo "<option $sel value=\"{$key}\">{$value}</option>";
                    }
                    ?>
                    </select>
                </p>



                <?
                //if ($forms->fields['typeform'] == 'cms')
                ?>
                <div id="forms_type_cms" <? if ($forms->fields['typeform'] != 'cms') echo 'style="display:none;"'; ?>>
                    <p>
                        <label><? echo _FORMS_RESPONSE; ?>:</label>
                        <textarea class="text" rows="3" name="forms_cms_response"><? echo htmlentities($forms->fields['cms_response']); ?></textarea>
                    </p>
                </div>
                <?
                //if ($forms->fields['typeform'] == 'app')
                ?>
                <div id="forms_type_app" <? if ($forms->fields['typeform'] != 'app') echo 'style="display:none;"'; ?>>
                    <p>
                        <label><? echo _FORMS_MODEL; ?>:</label>
                        <select class="select" name="forms_model">
                        <?
                        foreach($forms_tpl as $tpl_name)
                        {
                            ?>
                            <option <? if ($forms->fields['model'] == $tpl_name) echo 'selected'; ?> value="<? echo htmlentities($tpl_name); ?>"><? echo htmlentities($tpl_name); ?></option>
                            <?
                        }
                        ?>
                        </select>
                    </p>
                    <p>
                        <label style="cursor:pointer;" onclick="javascript:$('forms_option_onlyone').checked = !$('forms_option_onlyone').checked;"><? echo _FORMS_OPTION_ONLYONE; ?>:</label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyone" id="forms_option_onlyone" value="1" <? if ($forms->fields['option_onlyone']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" onclick="javascript:$('forms_option_onlyoneday').checked = !$('forms_option_onlyoneday').checked;"><? echo _FORMS_OPTION_ONLYONEDAY; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_onlyoneday" id="forms_option_onlyoneday" value="1" <? if ($forms->fields['option_onlyoneday']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" onclick="javascript:$('forms_option_displayuser').checked = !$('forms_option_displayuser').checked;"><? echo _FORMS_OPTION_DISPLAY_USER; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displayuser" id="forms_option_displayuser" value="1" <? if ($forms->fields['option_displayuser']) echo 'checked'; ?> />
                    </p>
                    <p>
                        <label style="cursor:pointer;" onclick="javascript:$('forms_option_displaygroup').checked = !$('forms_option_displaygroup').checked;"><? echo _FORMS_OPTION_DISPLAY_GROUP; ?></label>
                        <input type="checkbox" class="checkbox" name="forms_option_displaygroup" id="forms_option_displaygroup" value="1" <? if ($forms->fields['option_displaygroup']) echo 'checked'; ?> />
                    </p>
                </div>
            </div>
        </div>

        <div style="float:left;width:49%;">
            <div class="ploopi_form" style="padding:4px;">
                <p>
                    <label><? echo _FORMS_EMAIL; ?>:</label>
                    <input type="text" class="text" size="30" name="forms_email" value="<? echo htmlentities($forms->fields['email']); ?>" />
                </p>
                <p>
                    <label><? echo _FORMS_NBLINE; ?>:</label>
                    <input type="text" class="text" style="width:30px;"name="forms_nbline" value="<? echo $forms->fields['nbline']; ?>" />
                </p>
                <p>
                    <label style="cursor:pointer;" onclick="javascript:$('forms_option_displaydate').checked = !$('forms_option_displaydate').checked;"><? echo _FORMS_OPTION_DISPLAY_DATE; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displaydate" id="forms_option_displaydate" value="1" <? if ($forms->fields['option_displaydate']) echo 'checked'; ?> />
                </p>

                <p>
                    <label style="cursor:pointer;" onclick="javascript:$('forms_option_displayip').checked = !$('forms_option_displayip').checked;"><? echo _FORMS_OPTION_DISPLAY_IP; ?>:</label>
                    <input type="checkbox" class="checkbox" name="forms_option_displayip" id="forms_option_displayip" value="1" <? if ($forms->fields['option_displayip']) echo 'checked'; ?> />
                </p>

                <p>
                    <label><? echo _FORMS_OPTION_MODIFY; ?>:</label>
                    <select class="select" name="forms_option_modify">
                        <option value="nobody" <? if ($forms->fields['option_modify'] == 'nobody') echo 'selected'; ?>><? echo _FORMS_OPTION_MODIFY_NOBODY; ?></option>
                        <option value="user" <? if ($forms->fields['option_modify'] == 'user') echo 'selected'; ?>><? echo _FORMS_OPTION_MODIFY_USER; ?></option>
                        <option value="group" <? if ($forms->fields['option_modify'] == 'group') echo 'selected'; ?>><? echo _FORMS_OPTION_MODIFY_GROUP; ?></option>
                        <option value="all" <? if ($forms->fields['option_modify'] == 'all') echo 'selected'; ?>><? echo _FORMS_OPTION_MODIFY_ALL; ?></option>
                    </select>
                </p>
                <p>
                    <label><? echo _FORMS_OPTION_VIEW; ?>:</label>
                    <select class="select" name="forms_option_view">
                        <option value="private" <? if ($forms->fields['option_view'] == 'private') echo 'selected'; ?>><? echo _FORMS_OPTION_VIEW_PRIVATE; ?></option>
                        <option value="global" <? if ($forms->fields['option_view'] == 'global') echo 'selected'; ?>><? echo _FORMS_OPTION_VIEW_GLOBAL; ?></option>
                        <option value="asc" <? if ($forms->fields['option_view'] == 'asc') echo 'selected'; ?>><? echo _FORMS_OPTION_VIEW_ASC; ?></option>
                        <option value="desc" <? if ($forms->fields['option_view'] == 'desc') echo 'selected'; ?>><? echo _FORMS_OPTION_VIEW_DESC; ?></option>
                    </select>
                </p>
                <?
                if (!$forms->new)
                {
                    ?>
                    <p>
                        <label>Archiver les données plus anciennes que :</label>
                        <input type="text" class="text" style="width:30px;" name="forms_autobackup" value="<? echo $forms->fields['autobackup']; ?>"> jours (0 = aucun archivage)
                    </p>
                    <p>
                        <label>Archiver les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" name="forms_autobackup_date" id="forms_autobackup_date" value="<? echo $autobackup_date['date']; ?>">&nbsp;<a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('forms_autobackup_date', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                    </p>
                    <p>
                        <label>Supprimer les données jusqu'au :</label>
                        <input type="text" class="text" style="width:70px;" id="forms_delete_date">&nbsp;<a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('forms_delete_date', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <a href="javascript:void(0);" onclick="javascript:forms_deletedata('<? echo $forms->fields['id']; ?>', event);"><img src="./modules/forms/img/ico_trash.png" /></a>
                    </p>
                    <?
                }
                ?>
            </div>
        </div>

        <div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;border-bottom:1px solid #a0a0a0;padding:4px;overflow:auto;">
            <div style="float:right;">
                <?
                /*
                if (!$forms->new)
                {
                    ?>
                    <input type="button" class="flatbutton" style="font-weight:bold;" value="Générer les données physiques" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=forms_generate_tables&forms_id={$_GET['forms_id']}"); ?>'">
                    <?
                }
                */
                ?>
                <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?ploopi_moduletabid=formslist"); ?>'">
                <input type="reset" class="flatbutton" value="<? echo _PLOOPI_RESET; ?>">
                <?
                if (!$forms->new && false) //désactivé
                {
                    ?>
                    <input type="button" class="flatbutton" value="<? echo _FORMS_PREVIEW; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=forms_preview&forms_id={$_GET['forms_id']}"); ?>'">
                    <?
                }
                ?>
                <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
            </div>

            <div>
                <?
                if (!$forms->new) //désactivé
                {
                    ?>
                        <input type="button" class="flatbutton" value="<? echo _FORMS_VIEWRESULT; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?ploopi_action=public&op=forms_viewreplies&forms_id={$_GET['forms_id']}"); ?>'">
                    <?
                }
                ?>
                <span>(*) <? echo _FORMS_OBLIGATORY; ?></span>
            </div>
        </div>
    </div>




</div>

</form>

<?
//
if (!$forms->new)
{
    ?>
    <div class="ploopi_form_title" style=""><? echo _FORMS_FIELDLIST; ?></div>
    <?
    switch($op)
    {
        case 'forms_field_modify':
        case 'forms_field_add':
            ?>
            <a name="addform"></a>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <? include_once './modules/forms/admin_forms_field.php'; ?>
            </div>
            <?
        break;

        case 'forms_separator_modify':
        case 'forms_separator_add':
            ?>
            <div style="padding:4px;border-bottom:1px solid #a0a0a0;">
            <a name="addform"></a>
            <? include_once './modules/forms/admin_forms_separator.php'; ?>
            </div>
            <?
        break;

        default:
            ?>
            <div style="clear:both;background-color:#d0d0d0;overflow:auto;text-align:right;padding:4px;border-bottom:1px solid #a0a0a0;">
                <input type="button" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=forms_separator_add&forms_id={$_GET['forms_id']}"); ?>#addform'" class="flatbutton" value="<? echo _FORMS_ADDSEPARATOR; ?>">
                <input type="button" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=forms_field_add&forms_id={$_GET['forms_id']}"); ?>#addform'" class="flatbutton" value="<? echo _FORMS_ADDFIELD; ?>">
            </div>
            <?
        break;
    }
    ?>

    <?
    $array_columns = array();
    $array_values = array();

    $array_columns['left']['pos'] = array(  'label' => 'P.',
                                            'width' => 35,
                                            'options' => array('sort' => true)
                                            );

    $array_columns['auto']['name'] = array( 'label' => _FORMS_FIELD_NAME,
                                            'options' => array('sort' => true)
                                            );



    $array_columns['right']['export'] = array(  'label' => _FORMS_FIELD_EXPORTVIEW_SHORT,
                                                'width' => 55,
                                                'options' => array('sort' => true)
                                                );

    $array_columns['right']['array'] = array(   'label' => _FORMS_FIELD_ARRAYVIEW_SHORT,
                                                'width' => 55,
                                                'options' => array('sort' => true)
                                                );

    $array_columns['right']['needed'] = array(  'label' => _FORMS_FIELD_NEEDED_SHORT,
                                                'width' => 55,
                                                'options' => array('sort' => true)
                                                );

    /*$array_columns['right']['desc'] = array(  'label' => _FORMS_FIELD_DESCRIPTION,
                                                'width' => 150,
                                                'options' => array('sort' => true)
                                                );
*/
    $array_columns['right']['type'] = array(    'label' => _FORMS_FIELD_TYPE,
                                                'width' => 250,
                                                'options' => array('sort' => true)
                                                );

    $array_columns['actions_right']['actions'] = array('label' => '', 'width' => 74);

    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_field
            WHERE   id_form = {$_GET['forms_id']}
            ORDER BY position
            ";

    $rs_fields = $db->query($sql);

    $c=0;
    while ($fields = $db->fetchrow($rs_fields))
    {

        if ($fields['separator'])
        {
            $desc = str_replace('<LEVEL>',$fields['separator_level'],_FORMS_FIELD_SEPARATOR_DESC);


            $array_values[$c]['values']['name']         = array('label' =>  $fields['name']);
            $array_values[$c]['values']['type']         = array('label' =>  "{$desc} ({$fields['separator_fontsize']} pix)");
            $array_values[$c]['values']['export']       = array('label' =>  '&nbsp;');
            $array_values[$c]['values']['array']        = array('label' =>  '&nbsp;');
            $array_values[$c]['values']['needed']       = array('label' =>  '&nbsp;');
            $array_values[$c]['values']['actions']      = array('label' => '
                <a href="'.ploopi_urlencode("{$scriptenv}?op=forms_field_moveup&field_id={$fields['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
                <a href="'.ploopi_urlencode("{$scriptenv}?op=forms_field_movedown&field_id={$fields['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
                <a style="margin-left:10px;" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=forms_field_delete&field_id={$fields['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
            ');

            $array_values[$c]['description'] = 'Ouvrir le Séparateur &laquo; '.htmlentities($fields['name']).' &raquo;';
            $array_values[$c]['link'] = ploopi_urlencode("{$scriptenv}?op=forms_separator_modify&forms_id={$_GET['forms_id']}&field_id={$fields['id']}");

        }
        else
        {
            $array_values[$c]['values']['name']         = array('label' =>  $fields['name']);
            $array_values[$c]['values']['type']         = array('label' =>  $field_types[$fields['type']].( ($fields['type'] == 'text' && isset($field_formats[$fields['format']])) ? " ( {$field_formats[$fields['format']]} )" : ''));
            $array_values[$c]['values']['export']       = array('label' =>  '<img src="./modules/forms/img/'.((!$fields['option_exportview']) ? 'un' : '').'checked.gif">');
            $array_values[$c]['values']['array']        = array('label' =>  '<img src="./modules/forms/img/'.((!$fields['option_arrayview']) ? 'un' : '').'checked.gif">');
            $array_values[$c]['values']['needed']       = array('label' =>  '<img src="./modules/forms/img/'.((!$fields['option_needed']) ? 'un' : '').'checked.gif">');
            $array_values[$c]['description'] = 'Ouvrir le Champ &laquo; '.htmlentities($fields['name']).' &raquo;';
            $array_values[$c]['link'] = ploopi_urlencode("$scriptenv?op=forms_field_modify&forms_id={$_GET['forms_id']}&field_id={$fields['id']}");
        }

        $array_values[$c]['values']['pos']      = array('label' =>  $fields['position']);
        $array_values[$c]['values']['actions']  = array('label' => '
            <a href="'.ploopi_urlencode("{$scriptenv}?op=forms_field_moveup&forms_id={$_GET['forms_id']}&field_id={$fields['id']}").'"><img src="./modules/forms/img/ico_up2.png"></a>
            <a href="'.ploopi_urlencode("{$scriptenv}?op=forms_field_movedown&forms_id={$_GET['forms_id']}&field_id={$fields['id']}").'"><img src="./modules/forms/img/ico_down2.png"></a>
            <a style="margin-left:10px;" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=forms_field_delete&forms_id={$_GET['forms_id']}&field_id={$fields['id']}").'\',\''._PLOOPI_CONFIRM.'\')"><img src="./modules/forms/img/ico_trash.png"></a>
        ');

        if (isset($_GET['field_id']) && $fields['id'] == $_GET['field_id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

        $c++;
    }

    $skin->display_array($array_columns, $array_values, 'forms_field_list', array('sortable' => true, 'orderby_default' => 'pos'));

}

echo $skin->close_simplebloc();
?>
