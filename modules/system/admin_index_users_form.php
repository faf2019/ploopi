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
 * Interface de modification d'un utilisateur
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture d'une instance de l'objet user
 */

$user = new ploopi\user();

// Suppression de la variable de stockage de la photo temporaire
if (isset($_SESSION['system']['user_photopath'])) unset($_SESSION['system']['user_photopath']);
// Nouvel utilisateur
if (empty($_GET['user_id']) || !is_numeric($_GET['user_id']) || !$user->open($_GET['user_id']))
{
    $user->init_description();
    $user->fields['servertimezone'] = 1;
    // Lecture des paramètres "système"
    $user->fields['password_force_update'] = ploopi\param::get('system_password_force_update');
    $user->fields['password_validity'] = ploopi\param::get('system_password_validity');

    // Récuération des données de l'utilisateur (problème lors de la création) => pour remplir le formulaire
    if (!empty($_SESSION['system']['save_user']))
    {
        foreach($user->fields as $field => $value)
        {
            if (isset($_SESSION['system']['save_user']["user_{$field}"])) $user->fields[$field] = $_SESSION['system']['save_user']["user_{$field}"];
        }
    }
}
else ploopi\session::setvar("deletephoto_{$_GET['user_id']}", 0);



// detect server timezone
$date = date_create();
$server_timezone = date_timezone_get($date);
$server_timezoneid = timezone_name_get($server_timezone);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $server_timezoneid;

$user_date_expire = (!empty($user->fields['date_expire'])) ? ploopi\date::timestamp2local($user->fields['date_expire']) : array('date' => '');

$arrFormurl[] = 'op=save_user';
if (isset($_REQUEST['confirm'])) $arrFormurl[] = 'confirm';
if (!$user->new)  $arrFormurl[] = "user_id={$user->fields['id']}";

$arrRequiredFields = explode(',', ploopi\param::get('system_user_required_fields', _PLOOPI_MODULE_SYSTEM));
?>
<script type="text/javascript">
function system_user_validate(form, isnew)
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
        if (isnew)
        {
            if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LOGIN; ?>",form.user_login,"string"))
            if ((form.usernewpass_confirm.value != form.usernewpass.value) || form.usernewpass.value == '' || form.usernewpass_confirm.value == '') alert('<?php echo _SYSTEM_MSG_PASSWORDERROR_JS; ?>');
            else
            {
                rep = ploopi_xmlhttprequest('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+encodeURIComponent(form.usernewpass.value), false, false, 'POST');
                if (rep == 0)
                {
                    alert('Le mot de passe est invalide\n\nil doit contenir au moins 8 caractères,\nun caractère minuscule,\nun caractère majuscule,\nun chiffre et un caractère de ponctuation');
                }
                else return true;
            }
        }
        else
        {
            if (form.usernewpass_confirm.value == form.usernewpass.value && form.usernewpass.value == '') return true;
            else
            {
                if (form.usernewpass_confirm.value != form.usernewpass.value) alert('<?php echo _SYSTEM_MSG_PASSWORDERROR_JS; ?>');
                else
                {
                    rep = ploopi_xmlhttprequest('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+encodeURIComponent(form.usernewpass.value), false, false, 'POST');
                    if (rep == 0)
                    {
                        alert('Le mot de passe est invalide\n\nil doit contenir au moins 8 caractères,\nun caractère minuscule,\nun caractère majuscule,\nun chiffre et un caractère de ponctuation\nex:Pl00p!Rocks');
                    }
                    else return true;
                }
            }
        }
    }
    return false;
}
</script>

<form name="form_modify_user" action="<?php echo ploopi\crypt::urlencode('admin.php?'.implode('&',$arrFormurl)); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return system_user_validate(this, <?php echo ($user->new) ? 'true' : 'false'; ?>)">
<?php

if (isset($_REQUEST['error']))
{
    $error = '';

    switch($_REQUEST['error'])
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
if (isset($_REQUEST['confirm']))
{
    ?>
    <div class="error" style="padding:2px;text-align:center;"><?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION1; ?></div>
    <div style="margin:10px;">
    <?php
    ploopi\db::get()->query("
        SELECT  id,
                login,
                lastname,
                firstname,
                email,
                entity,
                service,
                office,
                function

        FROM    ploopi_user

        WHERE   (lastname = '".ploopi\db::get()->addslashes($_SESSION['system']['save_user']['user_lastname'])."'
        AND     firstname = '".ploopi\db::get()->addslashes($_SESSION['system']['save_user']['user_firstname'])."')
        OR      login = '".ploopi\db::get()->addslashes($_SESSION['system']['save_user']['user_login'])."'
    ");

    $arrColumns = array();
    $arrValues = array();

    $arrColumns['left']['name'] =
        array(
            'label' => _SYSTEM_LABEL_LASTNAME.', '._SYSTEM_LABEL_FIRSTNAME,
            'width' => 150,
            'options' => array('sort' => true)
        );

    $arrColumns['left']['login'] =
        array(
            'label' => _SYSTEM_LABEL_LOGIN,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $arrColumns['left']['entity'] =
        array(
            'label' => _SYSTEM_LABEL_ENTITY,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $arrColumns['left']['service'] =
        array(
            'label' => _SYSTEM_LABEL_SERVICE,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $arrColumns['left']['office'] =
        array(
            'label' => _SYSTEM_LABEL_OFFICE,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $arrColumns['left']['function'] =
        array(
            'label' => _SYSTEM_LABEL_FUNCTION,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $arrColumns['auto']['origin'] =
        array(
            'label' => _SYSTEM_LABEL_ORIGIN,
            'options' => array('sort' => true)
        );

    $booLoginWarning = false;

    while ($row = ploopi\db::get()->fetchrow())
    {
        if ($row['login'] == $_SESSION['system']['save_user']['user_login']) $booLoginWarning = true;

        $objUser = new ploopi\user();
        $objUser->fields['id'] = $row['id'];

        $arrGroups = $objUser->getgroups();
        $currentGroup = current($arrGroups);

        $arrValues[] =
            array(
                'values' =>
                    array(
                        'name' =>
                            array(
                                'label' => ploopi\str::htmlentities("{$row['lastname']}, {$row['firstname']}")
                            ),
                        'login' =>
                            array(
                                'label' => ploopi\str::htmlentities($row['login'])
                            ),
                        'origin' =>
                            array(
                                'label' => ploopi\str::htmlentities($currentGroup['label'])
                            ),
                        'entity' =>
                            array(
                                'label' => ploopi\str::htmlentities($row['entity'])
                            ),
                        'service' =>
                            array(
                                'label' => ploopi\str::htmlentities($row['service'])
                            ),
                        'office' =>
                            array(
                                'label' => ploopi\str::htmlentities($row['office'])
                            ),
                        'function' =>
                            array(
                                'label' => ploopi\str::htmlentities($row['function'])
                            ),
                    ),
                'description' => _SYSTEM_LABEL_ATTACH,
                'link' => ploopi\crypt::urlencode("admin.php?op=attach_user&user_id={$row['id']}")
            );
    }

    echo ploopi\skin::get()->open_simplebloc('Choisir un utilisateur existant');
    ploopi\skin::get()->display_array($arrColumns, $arrValues, 'array_createuser_confirm', array('sortable' => true, 'orderby_default' => 'name'));
    echo ploopi\skin::get()->close_simplebloc();
    ?>
    </div>
    <div class="error" style="padding:2px;text-align:center;"><?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION2; ?></div>
    <?php
}
?>
<div>
    <div style="float:left;width:50%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Informations personnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_LASTNAME; ?><sup style="font-size:.7em">&nbsp;1</sup> *:</label>
                        <input type="text" class="text" name="user_lastname"  value="<?php echo ploopi\str::htmlentities($user->fields['lastname']); ?>" tabindex="1" />
                    </p>
                    <p>
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_FIRSTNAME; ?><sup style="font-size:.7em">&nbsp;1</sup> *:</label>
                        <input type="text" class="text" name="user_firstname"  value="<?php echo ploopi\str::htmlentities($user->fields['firstname']); ?>" tabindex="2" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CIVILITY; ?><?php if (in_array('civility', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <select class="select" name="user_civility" style="width:100px;" tabindex="3">
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
                        <input type="text" class="text" name="user_entity"  value="<?php echo ploopi\str::htmlentities($user->fields['entity']); ?>" tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE; ?><?php if (in_array('service', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_service"  value="<?php echo ploopi\str::htmlentities($user->fields['service']); ?>" tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE2; ?><?php if (in_array('service2', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_service2"  value="<?php echo ploopi\str::htmlentities($user->fields['service2']); ?>" tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FUNCTION; ?><?php if (in_array('function', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_function"  value="<?php echo ploopi\str::htmlentities($user->fields['function']); ?>" tabindex="5" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_RANK; ?><?php if (in_array('rank', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_rank"  value="<?php echo ploopi\str::htmlentities($user->fields['rank']); ?>" tabindex="6" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_NUMBER; ?><?php if (in_array('number', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_number"  value="<?php echo ploopi\str::htmlentities($user->fields['number']); ?>" tabindex="7" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHONE; ?><?php if (in_array('phone', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_phone"  value="<?php echo ploopi\str::htmlentities($user->fields['phone']); ?>" tabindex="8" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_MOBILE; ?><?php if (in_array('mobile', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_mobile"  value="<?php echo ploopi\str::htmlentities($user->fields['mobile']); ?>" tabindex="9" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FAX; ?><?php if (in_array('fax', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_fax"  value="<?php echo ploopi\str::htmlentities($user->fields['fax']); ?>" tabindex="10" />
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Lieu de travail</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_BUILDING; ?><?php if (in_array('building', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_building"  value="<?php echo ploopi\str::htmlentities($user->fields['building']); ?>" tabindex="11" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FLOOR; ?><?php if (in_array('floor', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_floor"  value="<?php echo ploopi\str::htmlentities($user->fields['floor']); ?>" tabindex="12" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_OFFICE; ?><?php if (in_array('office', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_office"  value="<?php echo ploopi\str::htmlentities($user->fields['office']); ?>" tabindex="13" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_ADDRESS; ?><?php if (in_array('address', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <textarea class="text" name="user_address" tabindex="14"><?php echo ploopi\str::htmlentities($user->fields['address']); ?></textarea>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_POSTALCODE; ?><?php if (in_array('postalcode', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_postalcode"  value="<?php echo ploopi\str::htmlentities($user->fields['postalcode']); ?>" tabindex="15" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CITY; ?><?php if (in_array('city', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_city"  value="<?php echo ploopi\str::htmlentities($user->fields['city']); ?>" tabindex="16" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COUNTRY; ?><?php if (in_array('country', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_country"  value="<?php echo ploopi\str::htmlentities($user->fields['country']); ?>"  tabindex="17" />
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
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_LOGIN; ?>:</label>
                        <?php
                        if ($user->new)
                        {
                            ?>
                            <input type="text" class="text" name="user_login"  value="<?php echo ploopi\str::htmlentities($user->fields['login']); ?>" tabindex="21" />
                            <?php
                            if (isset($_REQUEST['confirm']) && !empty($booLoginWarning))
                            {
                                ?>
                                <div class="error">Attention ! &laquo; <?php echo ploopi\str::htmlentities($user->fields['login']); ?> &raquo; existe déjà. Vous devez modifier la propriété &laquo; <?php echo _SYSTEM_LABEL_LOGIN; ?> &raquo; pour pouvoir créer un nouvel utilisateur.</div>
                                <?php
                            }
                        }
                        else
                        {
                            ?>
                            <strong><?php echo ploopi\str::htmlentities($user->fields['login']); ?></strong>
                            <?php
                        }
                        ?>
                    </p>
                    <?php
                    if (!$user->new)
                    {
                        ?>
                        <!--p>
                            <label>Ancien mot de passe:</label>
                            <input type="password" class="text" name="useroldpass" id="useroldpass" value="" tabindex="22" style="width:140px;" />
                        </p-->
                        <p>
                            <label>Nouveau mot de passe:</label>
                            <input type="password" class="text" name="usernewpass" id="usernewpass" value="" tabindex="22" style="width:180px;" />
                        </p>
                        <?php
                    }
                    else {
                        ?>
                        <p>
                            <label>Mot de passe:</label>
                            <input type="password" class="text" name="usernewpass" id="usernewpass" value="" tabindex="22" style="width:180px;" />
                        </p>
                        <?php
                    }
                    ?>
                    <div id="protopass"></div>

                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                        <input type="password" class="text" name="usernewpass_confirm" id="usernewpass_confirm" value="" tabindex="23" style="width:180px;" />
                    </p>
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_password_force_update');">
                        <label>Forcer le changement de mot de passe à la prochaine connexion:</label>
                        <input type="checkbox" id="user_password_force_update" name="user_password_force_update" value="1" <?php if ($user->fields['password_force_update']) echo 'checked="checked"'; ?> tabindex="23" />
                    </p>
                    <p>
                        <label>Durée de validité du mot de passe en jours <em>(0 = pas de limite)</em> :</label>
                        <input type="text" class="text" style="width:60px;" name="user_password_validity" id="user_password_validity" value="<?php echo htmlentities($user->fields['password_validity']); ?>" tabindex="23" />
                    </p>

                    <?php if (!empty($user->fields['password_validity'])) { ?>
                    <p>
                        <label>Date d'expiration du mot de passe:</label>
                        <input type="text" style="width:100px;" class="text" readonly="readonly" disabled="disabled" value="<?php echo date('d/m/Y', ploopi\date::timestamp2unixtimestamp($user->fields['password_last_update'])+$user->fields['password_validity']*86400); ?>" tabindex="24" />
                    </p>
                    <?php } ?>

                    <p>
                        <label><?php echo _SYSTEM_LABEL_EXPIRATION_DATE; ?>:</label>
                        <input type="text" style="width:100px;" class="text" name="user_date_expire" id="user_date_expire" value="<?php echo ploopi\str::htmlentities($user_date_expire['date']); ?>" tabindex="24" />
                        <?php ploopi\date::open_calendar('user_date_expire'); ?>
                    </p>
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_disabled');">
                        <label>Compte désactivé:</label>
                        <input type="checkbox" id="user_disabled" name="user_disabled" value="1" <?php if ($user->fields['disabled']) echo 'checked="checked"'; ?> tabindex="24" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EMAIL; ?><?php if (in_array('email', $arrRequiredFields)) echo ' *'; ?>:</label>
                        <input type="text" class="text" name="user_email"  value="<?php echo ploopi\str::htmlentities($user->fields['email']); ?>" tabindex="25" />
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
                            <option value="<?php echo ploopi\str::htmlentities($key); ?>" <?php if ($user->fields['timezone'] == $key) echo 'selected'; ?>><?php echo ploopi\str::htmlentities($value['label']); ?> (<?php echo ploopi\str::htmlentities($value['offset_display']); ?>)</option>
                            <?php
                        }
                        ?>
                        </select>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COLOR; ?>:</label>
                        <input type="text" style="width:100px;cursor:pointer;" class="text jscolor {hash:true}" name="user_color" id="user_color" value="<?php echo ploopi\str::htmlentities($user->fields['color']); ?>" tabindex="29" readonly="readonly" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHOTO; ?>:</label>
                        <span>
                        <?php
                        $booPhotoExists = file_exists($user->getphotopath());
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:system_choose_photo(event, '<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_edit.png" /></a>
                        <?php if ($booPhotoExists) { ?> <a href="javascript:void(0);" onclick="javascript:$('system_user_photo').innerHTML = ''; system_delete_photo('<?php echo $user->fields['id']; ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_delete.png" /></a><?php } ?>
                        <br /><span id="system_user_photo">
                        <?php
                        if ($booPhotoExists) { ?><img src="<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}"); ?>" /><?php } ?>
                        </span>
                        </span>
                    </p>
                    <?php
                    if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
                    {
                        $workspace_user = new ploopi\workspace_user();
                        if (!empty($workspaceid) && !empty($_GET['user_id']) && $workspace_user->open($workspaceid, $_GET['user_id']))
                        {
                            ?>
                            <p style="margin-top:2px;padding:4px 0;border:2px solid #a0a0a0;background-color:#c0c0c0;">
                                <label style="font-weight:bold;"><?php echo _SYSTEM_LABEL_LEVEL; ?>:</label>
                                <select class="select" name="userworkspace_adminlevel" tabindex="30">
                                <?php


                                foreach ($ploopi_system_levels as $id => $label)
                                {
                                    if ($id <= $_SESSION['ploopi']['adminlevel'])
                                    {
                                        $sel = ($workspace_user->fields['adminlevel'] == $id) ? 'selected' : '';
                                        echo "<option $sel value=\"$id\">".ploopi\str::htmlentities($label)."</option>";
                                    }
                                    // user / group admin
                                }
                                ?>
                                </select>
                            </p>
                            <?php
                        }
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations complémentaires</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COMMENTS; ?>:</label>
                        <textarea class="text" name="user_comments" tabindex="30"><?php echo ploopi\str::htmlentities($user->fields['comments']); ?></textarea>
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<div style="clear:both;padding:4px;text-align:right;">
    <em><?php if ($user->isnew()) echo '<sup>(1)</sup> Champs utilisés pour tester la présence de l\'utilisateur / '; ?>(*) Champs obligatoires</em>&nbsp;
    <?php
    if (isset($_REQUEST['confirm']))
    {
        ?>
        <input type="submit" class="flatbutton" value="<?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION3; ?>">
        <?php
    }
    else
    {
        ?>
        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
        <?php
    }
    ?>
</div>
</form>

<?php
if (!$user->isnew())
{
    ?>
    <fieldset class="fieldset" style="padding:0px;margin:4px;">
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
    <?php
    // ploopi\annotation::display(_SYSTEM_OBJECT_USER, $user->fields['id'], trim("{$user->fields['lastname']} {$user->fields['firstname']}"));
}
?>


<style>
    #protopass {padding:0;margin:0;margin-left:30%;padding-left:0.5em;width:195px;} #protopass * {font-size:10px;}
    #protopass .password-strength-bar {border-radius:2px;}
</style>

<script type="text/javascript">
    Event.observe(window, 'load', function() {
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
