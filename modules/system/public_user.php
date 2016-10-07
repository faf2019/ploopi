<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$booReadonly = ploopi\param::get('system_profile_edit_allowed') == '0';

$strDisabled = $booReadonly ? 'disabled="disabled"' : '';

/**
 * Fonction javascript de validation
 */

if (!$booReadonly) {

    $arrRequiredFields = explode(',', ploopi\param::get('system_user_required_fields', _PLOOPI_MODULE_SYSTEM));

    ?>
    <script type="text/javascript">
    function user_validate(form)
    {
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LASTNAME; ?>",form.user_lastname,"string"))
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FIRSTNAME; ?>",form.user_firstname,"string"))
        <?php if (in_array('civility', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_CIVILITY; ?>", form.user_civility, 'select'))
        <?php } ?>

        <?php if (in_array('entity', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_ENTITY; ?>", form.user_entity, 'string'))
        <?php } ?>
        <?php if (in_array('service', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_SERVICE; ?>", form.user_service, 'string'))
        <?php } ?>
        <?php if (in_array('service2', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_SERVICE2; ?>", form.user_service2, 'string'))
        <?php } ?>
        <?php if (in_array('function', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FUNCTION; ?>", form.user_function, 'string'))
        <?php } ?>
        <?php if (in_array('rank', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_RANK; ?>", form.user_rank, 'string'))
        <?php } ?>
        <?php if (in_array('number', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_NUMBER; ?>", form.user_number, 'string'))
        <?php } ?>

        <?php if (in_array('phone', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"phone"))
        <?php } else { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"emptyphone"))
        <?php } ?>
        <?php if (in_array('mobile', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"phone"))
        <?php } else { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"emptyphone"))
        <?php } ?>
        <?php if (in_array('fax', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"phone"))
        <?php } else { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"emptyphone"))
        <?php } ?>

        <?php if (in_array('email', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"email"))
        <?php } else { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"emptyemail"))
        <?php } ?>

        <?php if (in_array('building', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_BUILDING; ?>", form.user_building, 'string'))
        <?php } ?>
        <?php if (in_array('floor', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FLOOR; ?>", form.user_floor, 'string'))
        <?php } ?>
        <?php if (in_array('office', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_OFFICE; ?>", form.user_office, 'string'))
        <?php } ?>
        <?php if (in_array('address', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_ADDRESS; ?>", form.user_address, 'string'))
        <?php } ?>
        <?php if (in_array('postalcode', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_POSTALCODE; ?>", form.user_postalcode, 'string'))
        <?php } ?>
        <?php if (in_array('city', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_CITY; ?>", form.user_city, 'string'))
        <?php } ?>
        <?php if (in_array('country', $arrRequiredFields)) { ?>
        if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_COUNTRY; ?>", form.user_country, 'string'))
        <?php } ?>
        {
            if (form.usernewpass_confirm.value == form.usernewpass.value && form.usernewpass.value == '') return true;
            else
            {
                if (form.usernewpass_confirm.value != form.usernewpass.value) alert('<?php echo _SYSTEM_MSG_PASSWORDERROR_JS; ?>');
                else {
                    if (form.useroldpass.value == '') alert('<?php echo _SYSTEM_MSG_PASSWORDERROR2_JS; ?>');
                    else {
                        rep = ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+encodeURIComponent(form.usernewpass.value), false, false, 'POST');

                        if (rep == 0) alert('Le mot de passe est invalide\n\nil doit contenir au moins 8 caractères,\nun caractère minuscule,\nun caractère majuscule,\nun chiffre et un caractère de ponctuation');
                        else return true;
                    }
                }
            }
        }
        return false;
    }
    </script>
    <?php
}

// Suppression de la variable de stockage de la photo temporaire
if (isset($_SESSION['system']['user_photopath'])) unset($_SESSION['system']['user_photopath']);

echo $skin->create_pagetitle(_PLOOPI_LABEL_MYWORKSPACE);
echo $skin->open_simplebloc(_PLOOPI_LABEL_MYPROFILE);

/**
 * Ouverture de l'instance de l'utilisateur à modifier
 */
$user = new ploopi\user();
$user->open($_SESSION['ploopi']['userid']);

ploopi\session::setvar("deletephoto_{$_SESSION['ploopi']['userid']}", 0);


// detect server timezone
$date = date_create();
$server_timezone = date_timezone_get($date);
$server_timezoneid = timezone_name_get($server_timezone);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $server_timezoneid;

$user_date_expire = (!empty($user->fields['date_expire'])) ? ploopi\date::timestamp2local($user->fields['date_expire']) : array('date' => '');


if (!$booReadonly) { ?><form name="form_modify_user" action="<?php echo ploopi\crypt::urlencode('admin.php?op=save_user'); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return user_validate(this)"><?php } ?>

<div>
<?php
$error = '';
if (isset($_GET['error']))
{
    switch($_GET['error'])
    {
        case 'password':
            $error = ploopi\str::nl2br(_SYSTEM_MSG_PASSWORDERROR);
        break;

        case 'oldpassword':
            $error = ploopi\str::nl2br(_SYSTEM_MSG_OLDPASSWORDERROR);
        break;

        case 'passrejected':
            $error = ploopi\str::nl2br(_SYSTEM_MSG_LOGINPASSWORDERROR);
        break;

        case 'login':
            $error = ploopi\str::nl2br(_SYSTEM_MSG_LOGINERROR);
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
                        <label><?php echo _SYSTEM_LABEL_LASTNAME; ?> *:</label>
                        <input type="text" class="text" name="user_lastname"  value="<?php echo ploopi\str::htmlentities($user->fields['lastname']); ?>" <?php echo $strDisabled; ?> tabindex="1" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FIRSTNAME; ?> *:</label>
                        <input type="text" class="text" name="user_firstname"  value="<?php echo ploopi\str::htmlentities($user->fields['firstname']); ?>" <?php echo $strDisabled; ?> tabindex="2" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CIVILITY; ?><?php if (in_array('civility', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <select class="select" name="user_civility" style="width:100px;" <?php echo $strDisabled; ?> tabindex="3">
                            <option value=""></option>
                            <?php
                            foreach ($ploopi_civility as $value)
                            {
                                ?>
                                <option value="<?php echo ploopi\str::htmlentities($value); ?>" <?php if ($user->fields['civility'] == $value) echo 'selected'; ?>><?php echo ploopi\str::htmlentities($value); ?></option>
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
                        <label><?php echo _SYSTEM_LABEL_ENTITY; ?><?php if (in_array('entity', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_entity"  value="<?php echo ploopi\str::htmlentities($user->fields['entity']); ?>" <?php echo $strDisabled; ?> tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE; ?><?php if (in_array('service', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_service"  value="<?php echo ploopi\str::htmlentities($user->fields['service']); ?>" <?php echo $strDisabled; ?> tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE2; ?><?php if (in_array('service2', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_service2"  value="<?php echo ploopi\str::htmlentities($user->fields['service2']); ?>" <?php echo $strDisabled; ?> tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FUNCTION; ?><?php if (in_array('function', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_function"  value="<?php echo ploopi\str::htmlentities($user->fields['function']); ?>" <?php echo $strDisabled; ?> tabindex="5" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_RANK; ?><?php if (in_array('rank', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_rank"  value="<?php echo ploopi\str::htmlentities($user->fields['rank']); ?>" <?php echo $strDisabled; ?> tabindex="6" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_NUMBER; ?><?php if (in_array('number', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_number"  value="<?php echo ploopi\str::htmlentities($user->fields['number']); ?>" <?php echo $strDisabled; ?> tabindex="7" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHONE; ?><?php if (in_array('phone', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_phone"  value="<?php echo ploopi\str::htmlentities($user->fields['phone']); ?>" <?php echo $strDisabled; ?> tabindex="8" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_MOBILE; ?><?php if (in_array('mobile', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_mobile"  value="<?php echo ploopi\str::htmlentities($user->fields['mobile']); ?>" <?php echo $strDisabled; ?> tabindex="9" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FAX; ?><?php if (in_array('fax', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_fax"  value="<?php echo ploopi\str::htmlentities($user->fields['fax']); ?>" <?php echo $strDisabled; ?> tabindex="10" />
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Lieu de travail</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_BUILDING; ?><?php if (in_array('building', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_building"  value="<?php echo ploopi\str::htmlentities($user->fields['building']); ?>" <?php echo $strDisabled; ?> tabindex="11" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FLOOR; ?><?php if (in_array('floor', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_floor"  value="<?php echo ploopi\str::htmlentities($user->fields['floor']); ?>" <?php echo $strDisabled; ?> tabindex="12" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_OFFICE; ?><?php if (in_array('office', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_office"  value="<?php echo ploopi\str::htmlentities($user->fields['office']); ?>" <?php echo $strDisabled; ?> tabindex="13" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_ADDRESS; ?><?php if (in_array('address', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <textarea class="text" name="user_address" <?php echo $strDisabled; ?> tabindex="14"><?php echo ploopi\str::htmlentities($user->fields['address']); ?></textarea>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_POSTALCODE; ?><?php if (in_array('postalcode', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_postalcode"  value="<?php echo ploopi\str::htmlentities($user->fields['postalcode']); ?>" <?php echo $strDisabled; ?> tabindex="15" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CITY; ?><?php if (in_array('city', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_city"  value="<?php echo ploopi\str::htmlentities($user->fields['city']); ?>" <?php echo $strDisabled; ?> tabindex="16" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COUNTRY; ?><?php if (in_array('country', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_country"  value="<?php echo ploopi\str::htmlentities($user->fields['country']); ?>" <?php echo $strDisabled; ?> tabindex="17" />
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
                        <strong><?php echo ploopi\str::htmlentities($user->fields['login']); ?></strong>
                    </p>
                    <p>
                        <label>Ancien mot de passe:</label>
                        <input type="password" class="text" name="useroldpass" id="useroldpass" value="" <?php echo $strDisabled; ?> tabindex="22" style="width:180px;" />
                    </p>
                    <p>
                        <label>Nouveau mot de passe:</label>
                        <input type="password" class="text" name="usernewpass" id="usernewpass" value="" <?php echo $strDisabled; ?> tabindex="22" style="width:180px;" />
                    </p>
                    <div id="protopass"></div>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                        <input type="password" class="text" name="usernewpass_confirm" id="usernewpass_confirm" value="" <?php echo $strDisabled; ?> tabindex="23" style="width:180px;" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EXPIRATION_DATE; ?>:</label>
                        <input type="text" style="width:100px;" class="text" readonly="readonly" disabled="disabled" value="<?php echo ploopi\str::htmlentities($user_date_expire['date']); ?>" tabindex="24" />
                    </p>

                    <?php if (!empty($user->fields['password_validity'])) { ?>
                    <p>
                        <label>Date d'expiration du mot de passe:</label>
                        <input type="text" style="width:100px;" class="text" readonly="readonly" disabled="disabled" value="<?php echo date('d/m/Y', ploopi\date::timestamp2unixtimestamp($user->fields['password_last_update'])+$user->fields['password_validity']*86400); ?>" tabindex="24" />
                    </p>
                    <?php } ?>

                    <p>
                        <label><?php echo _SYSTEM_LABEL_EMAIL; ?><?php if (in_array('email', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_email" value="<?php echo ploopi\str::htmlentities($user->fields['email']); ?>" <?php echo $strDisabled; ?> tabindex="25" />
                    </p>
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_ticketsbyemail');">
                        <label><?php echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                        <input style="width:16px;" type="checkbox" id="user_ticketsbyemail" name="user_ticketsbyemail" value="1" <?php if ($user->fields['ticketsbyemail']) echo 'checked'; ?> <?php echo $strDisabled; ?> tabindex="26" />
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
                            <input style="width:16px;" type="checkbox" id="user_servertimezone" name="user_servertimezone" value="1" <?php if ($user->fields['servertimezone']) echo 'checked'; ?> <?php echo $strDisabled; ?> tabindex="27" onchange="$('user_timezone_choice').style.display = (this.checked) ? 'none' : 'block';"/>
                            <?php echo "{$server_timezoneid} (". (($offset == 0) ? 'UTC' : sprintf("UTC %s%02dh%02d",$s, $hh, $mm)). ")"; ?>
                        </span>
                    </p>

                    <p id="user_timezone_choice" style="display:<?php echo ($user->fields['servertimezone']) ? 'none' : 'block'; ?>">
                        <label><?php echo _SYSTEM_LABEL_TIMEZONE; ?>:</label>
                        <?php
                        $timezone_abbreviations = timezone_abbreviations_list();
                        //ploopi\output::print_r($timezone_abbreviations);

                        foreach($timezone_abbreviations as $value)
                        {
                            foreach($value as $key => $value)
                            {
                                if (!empty($value['timezone_id']) && strpos($value['timezone_id'], '/') !== false)
                                {
                                    // Bug php avec version 5.2.0-8+etch15
                                    // timezone_open() [function.timezone-open]: Unknown or bad timezone (US/Pacific-New)
                                    ploopi\error::unset_handler();
                                    $objDateTimeZone = timezone_open($value['timezone_id']);
                                    ploopi\error::set_handler();

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
                        <select class="select" name="user_timezone" <?php echo $strDisabled; ?> tabindex="28">
                        <?php
                        foreach ($arrZones as $key => $value)
                        {
                            ?>
                            <option value="<?php echo ploopi\str::htmlentities($key); ?>" <?php if ($user->fields['timezone'] == $key) echo 'selected'; ?>><?php echo ploopi\str::htmlentities($value['label']); ?> (<?php echo ploopi\str::htmlentities($value['offset_display']); ?>)</option>
                            <?php
                        }
                        ?>
                        </select>
                    </p>
                    <?php if ($booReadonly) { ?>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COLOR; ?>:</label>
                        <input type="text" style="width:100px;background-color:<?php echo $user->fields['color']; ?>" class="text" name="user_color" id="user_color" value="<?php echo ploopi\str::htmlentities($user->fields['color']); ?>" <?php echo $strDisabled; ?> tabindex="29" />
                    </p>
                    <?php } else { ?>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COLOR; ?>:</label>
                        <input type="text" style="width:100px;cursor:pointer;" class="text jscolor {hash:true}" name="user_color" id="user_color" value="<?php echo ploopi\str::htmlentities($user->fields['color']); ?>" tabindex="29" />
                    </p>
                    <?php } ?>

                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHOTO; ?>:</label>
                        <span>
                        <?php
                        $booPhotoExists = file_exists($user->getphotopath());

                        if (!$booReadonly) { ?><a href="javascript:void(0);" onclick="javascript:system_choose_photo(event, '<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_edit.png" /></a><?php } ?>

                        <?php if ($booPhotoExists) { ?> <a href="javascript:void(0);" onclick="javascript:$('system_user_photo').innerHTML = ''; system_delete_photo('<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_delete.png" /></a><?php } ?>
                        <br /><span id="system_user_photo">
                        <?php
                        if ($booPhotoExists) { ?><img src="<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}"); ?>" /><?php } ?>
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
                        <textarea class="text" name="user_comments" <?php echo $strDisabled; ?> tabindex="30"><?php echo ploopi\str::htmlentities($user->fields['comments']); ?></textarea>
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<?php if (!$booReadonly) { ?>
    <div style="clear:both;text-align:right;padding:4px;">
        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
    </div>
    </form>

    <script type="text/javascript">
        Event.observe(window, 'load', function() {
            $('useroldpass').value = '';
            $('usernewpass').value = '';
            $('usernewpass_confirm').value = '';


            <?php if (_PLOOPI_USE_COMPLEXE_PASSWORD) { ?>
            var options = {
                minchar: <?php echo _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE; ?>,
                scores: [5, 10, 20, 30]
            };
            <?php } else { ?>
            var options = {
                minchar: 6,
                scores: [5, 10, 20, 30]
            };
            <?php } ?>

            new Protopass('usernewpass', 'protopass', options);

            Event.observe($('usernewpass_confirm'), 'change', function() {

                if ($('usernewpass').value == $('usernewpass_confirm').value) {
                    $('usernewpass_confirm').style.backgroundColor = $('usernewpass').style.backgroundColor = 'lightgreen';
                } else {
                    $('usernewpass_confirm').style.backgroundColor = $('usernewpass').style.backgroundColor = 'indianred';
                }
            });

        });
    </script>
<?php } ?>

<fieldset class="fieldset" style="clear:both;padding:0px;margin:4px;">
    <legend>Documents liés à l'utilisateur</legend>
    <?php
    ploopi\documents::insert(
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


<style>
    #protopass {padding:0;margin:0;margin-left:30%;padding-left:0.5em;width:195px;} #protopass * {font-size:10px;}
    #protopass .password-strength-bar {border-radius:2px;}
</style>



<?php echo $skin->close_simplebloc(); ?>
