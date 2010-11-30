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
 * Interface publique de modification d'un profil utilisateur
 *
 * @package system
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Fonction javascript de validation
 */
?>
<script type="text/javascript">
function user_validate(form)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LASTNAME; ?>",form.user_lastname,"string"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FIRSTNAME; ?>",form.user_firstname,"string"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"emptyemail"))
    {
        if (form.usernewpass_confirm.value == form.usernewpass.value && form.usernewpass.value == '') return true;
        else
        {
            rep = ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+form.usernewpass.value, false, false, 'POST');

            if (rep == 0)
            {
                alert('Le mot de passe est invalide\n\nil doit contenir au moins 8 caractères,\nun caractère minuscule,\nun caractère majuscule,\nun chiffre et un caractère de ponctuation');
            }
            else
            {
                if (form.usernewpass_confirm.value != form.usernewpass.value) alert('<?php echo _SYSTEM_MSG_PASSWORDERROR; ?>');
                else return true;
            }
        }
    }
    return false;
}
</script>

<?php
// Suppression de la variable de stockage de la photo temporaire
if (isset($_SESSION['system']['user_photopath'])) unset($_SESSION['system']['user_photopath']);

echo $skin->create_pagetitle(_PLOOPI_LABEL_MYWORKSPACE);
echo $skin->open_simplebloc(_PLOOPI_LABEL_MYPROFILE);

/**
 * Ouverture de l'instance de l'utilisateur à modifier
 */
$user = new user();
$user->open($_SESSION['ploopi']['userid']);

ploopi_setsessionvar("deletephoto_{$_SESSION['ploopi']['userid']}", 0);


// detect server timezone
$date = date_create();
$server_timezone = date_timezone_get($date);
$server_timezoneid = timezone_name_get($server_timezone);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $server_timezoneid;

$user_date_expire = (!empty($user->fields['date_expire'])) ? ploopi_timestamp2local($user->fields['date_expire']) : array('date' => '');
?>

<form name="form_modify_user" action="<?php echo ploopi_urlencode('admin.php?op=save_user'); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return user_validate(this)">
<div>
<?php
if (isset($error))
{
    switch($error)
    {
        case 'password':
            $error = nl2br(_SYSTEM_MSG_PASSWORDERROR);
        break;

        case 'passrejected':
            $error = nl2br(_SYSTEM_MSG_LOGINPASSWORDERROR);
        break;

        case 'login':
            $error = nl2br(_SYSTEM_MSG_LOGINERROR);
        break;
    }
    ?>
    <div class="error" style="padding:2px;text-align:center;"><?php echo $error; ?></div>
    <?php
}
?>
    <div style="float:left;width:50%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Informations personnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_LASTNAME; ?>:</label>
                        <input type="text" class="text" name="user_lastname"  value="<?php echo htmlentities($user->fields['lastname']); ?>" tabindex="1" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FIRSTNAME; ?>:</label>
                        <input type="text" class="text" name="user_firstname"  value="<?php echo htmlentities($user->fields['firstname']); ?>" tabindex="2" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CIVILITY; ?>:</label>
                        <select class="select" name="user_civility" style="width:100px;" tabindex="3">
                            <option value=""></option>
                            <?php
                            foreach ($ploopi_civility as $value)
                            {
                                ?>
                                <option value="<?php echo htmlentities($value); ?>" <?php if ($user->fields['civility'] == $value) echo 'selected'; ?>><?php echo htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations professionnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE; ?>:</label>
                        <input type="text" class="text" name="user_service"  value="<?php echo htmlentities($user->fields['service']); ?>" tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FUNCTION; ?>:</label>
                        <input type="text" class="text" name="user_function"  value="<?php echo htmlentities($user->fields['function']); ?>" tabindex="5" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_RANK; ?>:</label>
                        <input type="text" class="text" name="user_rank"  value="<?php echo htmlentities($user->fields['rank']); ?>" tabindex="6" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_NUMBER; ?>:</label>
                        <input type="text" class="text" name="user_number"  value="<?php echo htmlentities($user->fields['number']); ?>" tabindex="7" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHONE; ?>:</label>
                        <input type="text" class="text" name="user_phone"  value="<?php echo htmlentities($user->fields['phone']); ?>" tabindex="8" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_MOBILE; ?>:</label>
                        <input type="text" class="text" name="user_mobile"  value="<?php echo htmlentities($user->fields['mobile']); ?>" tabindex="9" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FAX; ?>:</label>
                        <input type="text" class="text" name="user_fax"  value="<?php echo htmlentities($user->fields['fax']); ?>" tabindex="10" />
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Lieu de travail</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_BUILDING; ?>:</label>
                        <input type="text" class="text" name="user_building"  value="<?php echo htmlentities($user->fields['building']); ?>" tabindex="11" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FLOOR; ?>:</label>
                        <input type="text" class="text" name="user_floor"  value="<?php echo htmlentities($user->fields['floor']); ?>" tabindex="12" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_OFFICE; ?>:</label>
                        <input type="text" class="text" name="user_office"  value="<?php echo htmlentities($user->fields['office']); ?>" tabindex="13" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_ADDRESS; ?>:</label>
                        <textarea class="text" name="user_address" tabindex="14"><?php echo htmlentities($user->fields['address']); ?></textarea>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_POSTALCODE; ?>:</label>
                        <input type="text" class="text" name="user_postalcode"  value="<?php echo htmlentities($user->fields['postalcode']); ?>" tabindex="15" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CITY; ?>:</label>
                        <input type="text" class="text" name="user_city"  value="<?php echo htmlentities($user->fields['city']); ?>" tabindex="16" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COUNTRY; ?>:</label>
                        <input type="text" class="text" name="user_country"  value="<?php echo htmlentities($user->fields['country']); ?>"  tabindex="17" />
                    </p>
                </div>
            </fieldset>
        </div>
    </div>

    <div style="float:left;width:49%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Compte d'utilisateur</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_LOGIN; ?>:</label>
                        <strong><?php echo $user->fields['login']; ?></strong>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD; ?>:</label>
                        <input type="password" class="text" name="usernewpass" id="usernewpass" value="" tabindex="22" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                        <input type="password" class="text" name="usernewpass_confirm" id="usernewpass_confirm" value="" tabindex="23" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EXPIRATION_DATE; ?>:</label>
                        <span><?php echo $user_date_expire['date']; ?></span>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EMAIL; ?>:</label>
                        <input type="text" class="text" name="user_email"  value="<?php echo htmlentities($user->fields['email']); ?>" tabindex="25" />
                    </p>
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_ticketsbyemail');">
                        <label><?php echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                        <input style="width:16px;" type="checkbox" id="user_ticketsbyemail" name="user_ticketsbyemail" value="1" <?php if ($user->fields['ticketsbyemail']) echo 'checked'; ?> tabindex="26" />
                    </p>

                    <?php
                    // get server offset
                    $offset = timezone_offset_get($server_timezone, $date);

                    $s = ($offset>0) ? '+' : '-';

                    $hh = floor(abs($offset) / 3600);
                    $mm = floor((abs($offset) % 3600) / 60);
                    ?>

                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_servertimezone');">
                        <label><?php echo _SYSTEM_LABEL_SERVERTIMEZONE; ?>:</label>
                        <span>
                            <input style="width:16px;" type="checkbox" id="user_servertimezone" name="user_servertimezone" value="1" <?php if ($user->fields['servertimezone']) echo 'checked'; ?> tabindex="27" onchange="$('user_timezone_choice').style.display = (this.checked) ? 'none' : 'block';"/>
                            <?php echo "{$server_timezoneid} (". (($offset == 0) ? 'UTC' : sprintf("UTC %s%02dh%02d",$s, $hh, $mm)). ")"; ?>
                        </span>
                    </p>

                    <p id="user_timezone_choice" style="display:<?php echo ($user->fields['servertimezone']) ? 'none' : 'block'; ?>">
                        <label><?php echo _SYSTEM_LABEL_TIMEZONE; ?>:</label>
                        <?php
                        $timezone_abbreviations = timezone_abbreviations_list();
                        //ploopi_print_r($timezone_abbreviations);

                        foreach($timezone_abbreviations as $value)
                        {
                            foreach($value as $key => $value)
                            {
                                if (!empty($value['timezone_id']) && strpos($value['timezone_id'], '/') !== false)
                                {
                                    // Bug php avec version 5.2.0-8+etch15
                                    // timezone_open() [function.timezone-open]: Unknown or bad timezone (US/Pacific-New)
                                    ploopi_unset_error_handler();
                                    $objDateTimeZone = timezone_open($value['timezone_id']);
                                    ploopi_set_error_handler();

                                    if ($objDateTimeZone !== false)
                                    {
                                        $offset = timezone_offset_get($objDateTimeZone, $date);

                                        //don't use $value['offset'] !;

                                        $s = ($offset>0) ? '+' : '-';

                                        $hh = floor(abs($offset) / 3600);
                                        $mm = floor((abs($offset) % 3600) / 60);

                                        $arrZones[$value['timezone_id']] =
                                            array(
                                                'offset' => $offset,
                                                'label' => str_replace(array('/', '_'), array(' / ', ' '), $value['timezone_id']),
                                                'offset_display' => ($offset == 0) ? 'UTC' : sprintf("GMT %s%02dh%02d",$s, $hh, $mm)
                                            );
                                    }
                                }
                            }
                        }

                        ksort($arrZones);

                        ?>
                        <select class="select" name="user_timezone"  tabindex="28">
                        <?php
                        foreach ($arrZones as $key => $value)
                        {
                            ?>
                            <option value="<?php echo htmlentities($key); ?>" <?php if ($user->fields['timezone'] == $key) echo 'selected'; ?>><?php echo htmlentities($value['label']); ?> (<?php echo $value['offset_display']; ?>)</option>
                            <?php
                        }
                        ?>
                        </select>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COLOR; ?>:</label>
                        <input type="text" style="width:100px;cursor:pointer;" class="text color {hash:true}" name="user_color" id="user_color" value="<?php echo htmlentities($user->fields['color']); ?>" tabindex="29" readonly="readonly" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHOTO; ?>:</label>
                        <span>
                        <?
                        $booPhotoExists = file_exists($user->getphotopath());
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:system_choose_photo(event, '<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_edit.png" /></a>
                        <? if ($booPhotoExists) { ?> <a href="javascript:void(0);" onclick="javascript:$('system_user_photo').innerHTML = ''; system_delete_photo('<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_delete.png" /></a><? } ?>
                        <br /><span id="system_user_photo">
                        <?php
                        if ($booPhotoExists) { ?><img src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}"); ?>" /><?php } ?>
                        </span>
                        </span>
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations complémentaires</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COMMENTS; ?>:</label>
                        <textarea class="text" name="user_comments" tabindex="30"><?php echo htmlentities($user->fields['comments']); ?></textarea>
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<div style="clear:both;text-align:right;padding:4px;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<fieldset class="fieldset" style="padding:0px;margin:4px;">
    <legend>Documents liés à l'utilisateur</legend>
    <?php
    ploopi_documents(
        _SYSTEM_OBJECT_USER,
        $user->fields['id'],
        array(
            'DOCUMENT_CREATE' => true,
            'DOCUMENT_MODIFY' => true,
            'DOCUMENT_DELETE' => true,
            'FOLDER_CREATE' => true,
            'FOLDER_MODIFY' => true,
            'FOLDER_DELETE' => true,
            'SEARCH' => false
        ),
        array(
            'PHOTOS',
            'VIDEOS',
            'DOCUMENTS'
        ),
        array(
            'ROOT_NAME' => trim("{$user->fields['lastname']} {$user->fields['firstname']}"),
            'ATTACHEMENT' => false,
            'FIELDS' =>
                array(
                    'name',
                    'timestp_modify',
                    'label',
                    'ref'
            ),
        )
    );
    ?>
</fieldset>

<?php echo $skin->close_simplebloc(); ?>
