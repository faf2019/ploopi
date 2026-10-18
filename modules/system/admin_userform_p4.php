<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Fiche d'un utilisateur, gabarits de génération 4 : un formulaire pour deux usages,
 * selon $strUserFormMode fourni par l'appelant :
 *  - 'admin'   : création ou modification par un administrateur (remplace
 *                admin_index_users_form.php) ; op=save_user de admin_index_users.php ;
 *  - 'profile' : profil de l'utilisateur connecté (remplace public_user.php) ;
 *                op=save_user de public.php, ancien mot de passe exigé.
 *
 * Mêmes noms de champs que les formulaires historiques : les enregistrements ne
 * changent pas.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

global $ploopi_civility;
global $ploopi_system_levels;

$booProfile = $strUserFormMode == 'profile';
$objSkin = ploopi\skin::get();
$db = ploopi\db::get();

// Photo temporaire d'une saisie précédente abandonnée
if (isset($_SESSION['system']['user_photopath'])) unset($_SESSION['system']['user_photopath']);

$user = new ploopi\user();

if ($booProfile)
{
    $user->open($_SESSION['ploopi']['userid']);
    $booReadonly = ploopi\param::get('system_profile_edit_allowed') == '0';
}
else
{
    $booReadonly = false;

    if (empty($_GET['user_id']) || !is_numeric($_GET['user_id']) || !$user->open($_GET['user_id']))
    {
        $user->init_description();
        $user->fields['servertimezone'] = 1;
        $user->fields['password_force_update'] = ploopi\param::get('system_password_force_update');
        $user->fields['password_validity'] = ploopi\param::get('system_password_validity');

        // Saisie précédente (homonyme à confirmer)
        if (!empty($_SESSION['system']['save_user']))
        {
            foreach($user->fields as $strField => $mixValue)
            {
                if (isset($_SESSION['system']['save_user']["user_{$strField}"])) $user->fields[$strField] = $_SESSION['system']['save_user']["user_{$strField}"];
            }
        }
    }
}

$booNew = $user->isnew();
if (!$booNew) ploopi\session::setvar("deletephoto_{$user->fields['id']}", 0);

// Fuseau horaire du serveur
$objDate = date_create();
$objServerTz = date_timezone_get($objDate);
$strServerTz = timezone_name_get($objServerTz);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $strServerTz;

$funcOffset = function($intOffset, $strUtc = 'UTC')
{
    if ($intOffset == 0) return $strUtc;
    return sprintf('%s %s%02dh%02d', $strUtc, $intOffset > 0 ? '+' : '-', floor(abs($intOffset) / 3600), floor((abs($intOffset) % 3600) / 60));
};

$arrZones = array();
foreach(timezone_identifiers_list() as $strZone)
{
    if (strpos($strZone, '/') === false) continue;
    $intOffset = timezone_offset_get(timezone_open($strZone), $objDate);
    $arrZones[$strZone] = str_replace(array('/', '_'), array(' / ', ' '), $strZone).' ('.$funcOffset($intOffset, 'GMT').')';
}

$arrRequired = explode(',', ploopi\param::get('system_user_required_fields', _PLOOPI_MODULE_SYSTEM));

/**
 * Champ texte de la fiche : obligatoire selon le paramètre system_user_required_fields,
 * contrôle de saisie selon le type
 */
$funcField = function($strField, $strLabel, $arrOptions = array()) use ($user, $arrRequired, $booReadonly)
{
    $strValue = isset($user->fields[$strField]) ? $user->fields[$strField] : '';
    $booRequired = !empty($arrOptions['required']) || in_array($strField, $arrRequired);
    $strType = isset($arrOptions['type']) ? $arrOptions['type'] : 'string';

    if ($booReadonly) return ui::field($strLabel, ui::readonly($strValue, !empty($arrOptions['multiline'])), array('width' => isset($arrOptions['width']) ? $arrOptions['width'] : 'c6'));

    // Types de ploopi.validatefield : obligatoire, ou format contrôlé seulement si rempli
    $arrChecks = array(
        'string' => $booRequired ? 'string' : null,
        'phone' => $booRequired ? 'phone' : 'emptyphone',
        'email' => $booRequired ? 'email' : 'emptyemail'
    );

    $arrAttrs = array(
        'check' => isset($arrChecks[$strType]) ? $arrChecks[$strType] : null,
        'label' => $strLabel,
        'required' => $booRequired,
        'autocomplete' => isset($arrOptions['autocomplete']) ? $arrOptions['autocomplete'] : null,
        'type' => $strType == 'email' ? 'email' : ($strType == 'phone' ? 'tel' : 'text')
    );

    $strControl = empty($arrOptions['multiline']) ? ui::input("user_{$strField}", $strValue, $arrAttrs) : ui::textarea("user_{$strField}", $strValue, array_diff_key($arrAttrs, array('type' => 1)) + array('rows' => 3));

    return ui::field($strLabel, $strControl, array('for' => "user_{$strField}", 'required' => $booRequired, 'width' => isset($arrOptions['width']) ? $arrOptions['width'] : 'c6'));
};

// Adresse du formulaire
if ($booProfile) $strAction = 'admin.php?op=save_user';
else
{
    $arrUrl = array('op=save_user');
    if (isset($_REQUEST['confirm'])) $arrUrl[] = 'confirm';
    if (!$booNew) $arrUrl[] = "user_id={$user->fields['id']}";
    $strAction = 'admin.php?'.implode('&', $arrUrl);
}

// Erreur de l'enregistrement précédent
$arrErrors = array(
    'password' => _SYSTEM_MSG_PASSWORDERROR,
    'oldpassword' => _SYSTEM_MSG_OLDPASSWORDERROR,
    'passrejected' => strip_tags(str_replace('<BR>', ' ', _SYSTEM_MSG_LOGINPASSWORDERROR)),
    'login' => _SYSTEM_MSG_LOGINERROR,
    'save' => _SYSTEM_MSG_SAVEUSERERROR
);
if (isset($_REQUEST['error']) && isset($arrErrors[$_REQUEST['error']])) echo ui::callout('danger', ploopi\str::nl2br(ui::e($arrErrors[$_REQUEST['error']])));

// En-tête de la fiche : pastille, nom, état du compte
if (!$booNew)
{
    $strInitials = mb_strtoupper(mb_substr($user->fields['firstname'], 0, 1).mb_substr($user->fields['lastname'], 0, 1));
    $booExpired = !empty($user->fields['date_expire']) && $user->fields['date_expire'] < date('YmdHis');
    $booPhoto = file_exists($user->getphotopath());
    ?>
    <div class="p4-userform-head p4-ui">
        <?php
        if ($booPhoto) echo '<img class="p4-avatar p4-avatar-lg p4-avatar-photo" src="'.ui::e(ploopi\crypt::urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}")).'" alt="" />';
        else echo '<span class="p4-avatar p4-avatar-lg p4-av-'.((ord(mb_substr($user->fields['lastname'], 0, 1)) ?: 0) % 5).'">'.ui::e($strInitials).'</span>';
        ?>
        <div class="p4-grow">
            <h3><?php echo ui::e(trim("{$user->fields['firstname']} {$user->fields['lastname']}")); ?></h3>
            <p class="p4-mono p4-text-xs p4-muted"><?php echo ui::e($user->fields['login']); ?></p>
        </div>
        <?php
        if ($user->fields['disabled']) echo ui::badge(_SYSTEM_USERS_CARD_DISABLED, 'danger');
        elseif ($booExpired) echo ui::badge(_SYSTEM_USERS_CARD_EXPIRED, 'warning');
        else echo ui::badge(_SYSTEM_USERS_CARD_ACTIVE, 'success');
        ?>
    </div>
    <?php
}

// Création : homonymes à confirmer (admin_index_users.php, op=save_user)
if (!$booProfile && isset($_REQUEST['confirm']) && !empty($_SESSION['system']['save_user']))
{
    $arrSave = $_SESSION['system']['save_user'];
    $rs = $db->query("
        SELECT  id, login, lastname, firstname, entity, service
        FROM    ploopi_user
        WHERE   (lastname = '".$db->addslashes(isset($arrSave['user_lastname']) ? $arrSave['user_lastname'] : '')."'
        AND     firstname = '".$db->addslashes(isset($arrSave['user_firstname']) ? $arrSave['user_firstname'] : '')."')
        OR      login = '".$db->addslashes(isset($arrSave['user_login']) ? $arrSave['user_login'] : '')."'
    ");

    $booLoginWarning = false;
    $strRows = '';
    while ($row = $db->fetchrow($rs))
    {
        if (isset($arrSave['user_login']) && $row['login'] == $arrSave['user_login']) $booLoginWarning = true;
        $strRows .= '<li><span class="p4-assign-icon">'.ui::icon('user').'</span><span class="p4-grow"><b>'.ui::e("{$row['lastname']}, {$row['firstname']}").'</b><small>'.ui::e(trim("{$row['login']} · {$row['entity']} {$row['service']}")).'</small></span>'
            .ui::button(array('label' => _SYSTEM_LABEL_ATTACH, 'icon' => 'link', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?op=attach_user&user_id={$row['id']}"))).'</li>';
    }

    echo ui::callout('warning', '<strong>'.ui::e(_SYSTEM_MSG_CREATEUSER_CONFIRMATION1).'</strong><br />'.ui::e(_SYSTEM_MSG_CREATEUSER_CONFIRMATION2).($strRows == '' ? '' : '<ul class="p4-assign-list p4-mt-12">'.$strRows.'</ul>'));
}

if (!$booReadonly)
{
    ?>
    <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode($strAction); ?>" method="post" enctype="multipart/form-data" data-p4-validate autocomplete="off">
    <?php
}
else
{
    echo ui::callout('info', ui::e(_SYSTEM_USERF_READONLY));
    ?>
    <div class="p4-form p4-ui">
    <?php
}

echo ui::section_open(_SYSTEM_USERF_SEC_IDENTITY, _SYSTEM_USERF_SEC_IDENTITY_DESC, 'user');
echo $funcField('lastname', _SYSTEM_LABEL_LASTNAME, array('required' => true, 'autocomplete' => 'family-name'));
echo $funcField('firstname', _SYSTEM_LABEL_FIRSTNAME, array('required' => true, 'autocomplete' => 'given-name'));

$arrCivility = array();
foreach($ploopi_civility as $strCivility) $arrCivility[$strCivility] = $strCivility;
$booCivilityRequired = in_array('civility', $arrRequired);
if ($booReadonly) echo ui::field(_SYSTEM_LABEL_CIVILITY, ui::readonly($user->fields['civility']));
else echo ui::field(_SYSTEM_LABEL_CIVILITY, ui::select('user_civility', $arrCivility, $user->fields['civility'], array('empty' => '', 'check' => $booCivilityRequired ? 'selected' : null, 'label' => _SYSTEM_LABEL_CIVILITY)), array('for' => 'user_civility', 'required' => $booCivilityRequired));

// Photo : fichier choisi dans le champ, envoyé en arrière-plan (op.php : system_send_photo,
// aperçu par system_get_photo), enregistrée avec la fiche ; suppression : system_delete_photo
if (!$booNew)
{
    $booPhoto = file_exists($user->getphotopath());
    $strPhoto = '<div class="p4-photo-field"><span id="system_user_photo" class="p4-photo">'.($booPhoto ? '<img src="'.ui::e(ploopi\crypt::urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}")).'" alt="" />' : ui::icon('user')).'</span>';
    if (!$booReadonly)
    {
        $strPhoto .= '<label class="p4-btn p4-btn-sm p4-photo-choose">'.ui::icon('upload', 'p4-icon-sm').ui::e(_SYSTEM_USERF_PHOTO_CHOOSE)
            .'<input class="p4-sr-only" type="file" accept="image/*"'.ui::attrs(array(
                'data-p4-photo-file' => $user->fields['id'],
                'data-p4-photo-url' => ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_send_photo&system_user_id={$user->fields['id']}"),
                'data-p4-photo-preview' => ploopi\crypt::urlencode('admin-light.php?ploopi_op=system_get_photo')
            )).' /></label>';
        $strPhoto .= ui::button(array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'attrs' => array('data-p4-photo-delete' => $user->fields['id'], 'hidden' => !$booPhoto)));
    }
    echo ui::field(_SYSTEM_LABEL_PHOTO, $strPhoto.'</div>', array('hint' => $booReadonly ? '' : ui::e(_SYSTEM_USERF_PHOTO_HINT)));
}
echo ui::section_close();

echo ui::section_open(_SYSTEM_USERF_SEC_WORK, _SYSTEM_USERF_SEC_WORK_DESC, 'briefcase');
echo $funcField('entity', _SYSTEM_LABEL_ENTITY, array('autocomplete' => 'organization'));
echo $funcField('service', _SYSTEM_LABEL_SERVICE);
echo $funcField('service2', _SYSTEM_LABEL_SERVICE2);
echo $funcField('function', _SYSTEM_LABEL_FUNCTION, array('autocomplete' => 'organization-title'));
echo $funcField('rank', _SYSTEM_LABEL_RANK);
echo $funcField('number', _SYSTEM_LABEL_NUMBER);
echo ui::section_close();

echo ui::section_open(_SYSTEM_USERF_SEC_CONTACT, _SYSTEM_USERF_SEC_CONTACT_DESC, 'at');
echo $funcField('email', _SYSTEM_LABEL_EMAIL, array('type' => 'email', 'width' => 'c12', 'autocomplete' => 'email'));
echo $funcField('phone', _SYSTEM_LABEL_PHONE, array('type' => 'phone', 'width' => 'c4', 'autocomplete' => 'tel'));
echo $funcField('mobile', _SYSTEM_LABEL_MOBILE, array('type' => 'phone', 'width' => 'c4'));
echo $funcField('fax', _SYSTEM_LABEL_FAX, array('type' => 'phone', 'width' => 'c4'));
echo ui::section_close();

echo ui::section_open(_SYSTEM_USERF_SEC_PLACE, _SYSTEM_USERF_SEC_PLACE_DESC, 'map-pin');
echo $funcField('building', _SYSTEM_LABEL_BUILDING, array('width' => 'c4'));
echo $funcField('floor', _SYSTEM_LABEL_FLOOR, array('width' => 'c4'));
echo $funcField('office', _SYSTEM_LABEL_OFFICE, array('width' => 'c4'));
echo $funcField('address', _SYSTEM_LABEL_ADDRESS, array('multiline' => true, 'width' => 'c12'));
echo $funcField('postalcode', _SYSTEM_LABEL_POSTALCODE, array('width' => 'c4', 'autocomplete' => 'postal-code'));
echo $funcField('city', _SYSTEM_LABEL_CITY, array('width' => 'c4'));
echo $funcField('country', _SYSTEM_LABEL_COUNTRY, array('width' => 'c4'));
echo ui::section_close();

// Compte : identifiant, mot de passe, validité
echo ui::section_open(_SYSTEM_USERF_SEC_ACCOUNT, $booProfile ? _SYSTEM_USERF_SEC_ACCOUNT_PROFILE_DESC : _SYSTEM_USERF_SEC_ACCOUNT_DESC, 'lock');

if ($booNew)
{
    $strLoginHint = '';
    if (!empty($booLoginWarning)) $strLoginHint = '<span class="p4-text-danger">'.ui::e(str_replace('<LOGIN>', $user->fields['login'], _SYSTEM_USERF_LOGIN_EXISTS)).'</span>';
    echo ui::field(_SYSTEM_LABEL_LOGIN, ui::input('user_login', $user->fields['login'], array('check' => 'string', 'label' => _SYSTEM_LABEL_LOGIN, 'required' => true, 'autocomplete' => 'off', 'class' => 'p4-input p4-mono')), array('for' => 'user_login', 'required' => true, 'width' => 'c12', 'hint' => $strLoginHint));
}
else echo ui::field(_SYSTEM_LABEL_LOGIN, ui::readonly($user->fields['login']), array('width' => 'c12'));

if (!$booReadonly)
{
    $intMinChar = _PLOOPI_USE_COMPLEXE_PASSWORD ? _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE : 6;

    if ($booProfile) echo ui::field(_SYSTEM_USERF_OLDPASSWORD, ui::input('useroldpass', '', array('type' => 'password', 'autocomplete' => 'current-password')), array('for' => 'useroldpass', 'width' => 'c12', 'hint' => ui::e(_SYSTEM_USERF_OLDPASSWORD_HINT)));

    $arrPwAttrs = array(
        'type' => 'password',
        'autocomplete' => 'new-password',
        'data-p4-password' => 'usernewpass_confirm',
        'data-p4-password-required' => $booNew,
        'data-p4-password-mismatch' => _SYSTEM_USERF_PASSWORD_MISMATCH,
        'data-p4-password-invalid' => _SYSTEM_USERF_PASSWORD_INVALID
    );

    echo ui::field($booNew ? _SYSTEM_USERF_PASSWORD : _SYSTEM_USERF_NEWPASSWORD, ui::input('usernewpass', '', $arrPwAttrs).'<div id="p4-checkpass" class="p4-checkpass" data-p4-checkpass="usernewpass" data-p4-minchar="'.(int) $intMinChar.'"></div>', array('for' => 'usernewpass', 'required' => $booNew, 'hint' => $booNew ? '' : ui::e(_SYSTEM_USERF_NEWPASSWORD_HINT)));
    echo ui::field(_SYSTEM_LABEL_PASSWORD_CONFIRM, ui::input('usernewpass_confirm', '', array('type' => 'password', 'autocomplete' => 'new-password')), array('for' => 'usernewpass_confirm', 'required' => $booNew));
}

if (!empty($user->fields['password_validity']) && !empty($user->fields['password_last_update']))
{
    echo ui::field(_SYSTEM_USERF_PASSWORD_EXPIRE, ui::readonly(date('d/m/Y', ploopi\date::timestamp2unixtimestamp($user->fields['password_last_update']) + $user->fields['password_validity'] * 86400)));
}

if ($booProfile)
{
    $arrExpire = ploopi\date::timestamp2local($user->fields['date_expire']);
    echo ui::field(_SYSTEM_LABEL_EXPIRATION_DATE, ui::readonly($arrExpire['date']));
}
else
{
    echo ui::field(_SYSTEM_USERF_PASSWORD_VALIDITY, ui::input('user_password_validity', $user->fields['password_validity'], array('type' => 'number', 'min' => 0, 'check' => 'emptyint', 'label' => _SYSTEM_USERF_PASSWORD_VALIDITY)), array('for' => 'user_password_validity', 'hint' => ui::e(_SYSTEM_USERF_PASSWORD_VALIDITY_HINT)));
    echo ui::field(_SYSTEM_LABEL_EXPIRATION_DATE, ui::date('user_date_expire', $user->fields['date_expire']), array('for' => 'user_date_expire', 'hint' => ui::e(_SYSTEM_USERF_EXPIRE_HINT)));
    echo '<div class="p4-c12 p4-toggles">';
    echo ui::toggle('user_password_force_update', !empty($user->fields['password_force_update']), _SYSTEM_USERF_FORCEUPDATE, ui::e(_SYSTEM_USERF_FORCEUPDATE_HINT));
    echo ui::toggle('user_disabled', !empty($user->fields['disabled']), _SYSTEM_USERF_DISABLED, ui::e(_SYSTEM_USERF_DISABLED_HINT));
    echo '</div>';

    // Niveau dans l'espace (rattachement direct), jusqu'au sien
    if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES && !$booNew)
    {
        $objWorkspaceUser = new ploopi\workspace_user();
        if (!empty($workspaceid) && $objWorkspaceUser->open($workspaceid, $user->fields['id']))
        {
            $arrLevels = array();
            foreach($ploopi_system_levels as $intLevel => $strLevel) if ($intLevel <= $_SESSION['ploopi']['adminlevel']) $arrLevels[$intLevel] = $strLevel;
            echo ui::field(_SYSTEM_USERS_CARD_LEVEL, ui::select('userworkspace_adminlevel', $arrLevels, $objWorkspaceUser->fields['adminlevel']), array('for' => 'userworkspace_adminlevel', 'hint' => ui::e(_SYSTEM_USERF_LEVEL_HINT)));
        }
    }
}
echo ui::section_close();

// Préférences : messagerie, fuseau horaire, couleur
echo ui::section_open(_SYSTEM_USERF_SEC_PREFS, _SYSTEM_USERF_SEC_PREFS_DESC, 'adjustments-horizontal');
if ($booReadonly)
{
    echo ui::field(_SYSTEM_LABEL_TICKETSBYEMAIL, ui::readonly($user->fields['ticketsbyemail'] ? _PLOOPI_YES : _PLOOPI_NO));
    echo ui::field(_SYSTEM_LABEL_TIMEZONE, ui::readonly($user->fields['servertimezone'] ? $strServerTz : $user->fields['timezone']));
}
else
{
    echo '<div class="p4-c12 p4-toggles">';
    echo ui::toggle('user_ticketsbyemail', !empty($user->fields['ticketsbyemail']), _SYSTEM_LABEL_TICKETSBYEMAIL, ui::e(_SYSTEM_USERF_TICKETSBYEMAIL_HINT));
    echo ui::toggle('user_servertimezone', !empty($user->fields['servertimezone']), _SYSTEM_LABEL_SERVERTIMEZONE, ui::e($strServerTz.' ('.$funcOffset(timezone_offset_get($objServerTz, $objDate)).')'), array('data-p4-toggles' => 'p4-user-timezone', 'data-p4-toggles-off' => true));
    echo '</div>';
    echo '<div class="p4-field p4-c12" id="p4-user-timezone"'.($user->fields['servertimezone'] ? ' hidden' : '').'><label for="user_timezone">'.ui::e(_SYSTEM_LABEL_TIMEZONE).'</label>'.ui::select('user_timezone', $arrZones, $user->fields['timezone']).'</div>';
}

$strColor = preg_match('/^#[0-9a-f]{6}$/i', (string) $user->fields['color']) ? $user->fields['color'] : '';
if ($booReadonly) echo ui::field(_SYSTEM_LABEL_COLOR, '<div class="p4-readonly"><span class="p4-swatch" style="background:'.ui::e($strColor ?: 'transparent').'"></span>'.ui::e($strColor).'</div>');
else echo ui::field(_SYSTEM_LABEL_COLOR, '<div class="p4-row"><input type="color" class="p4-color" data-p4-color="user_color" value="'.ui::e($strColor ?: '#3b7f90').'" aria-label="'.ui::e(_SYSTEM_LABEL_COLOR).'" />'.ui::input('user_color', $strColor, array('class' => 'p4-input p4-mono', 'placeholder' => '#rrggbb', 'pattern' => '#[0-9a-fA-F]{6}', 'maxlength' => 7)).'</div>', array('for' => 'user_color', 'hint' => ui::e(_SYSTEM_USERF_COLOR_HINT)));
echo ui::section_close();

echo ui::section_open(_SYSTEM_LABEL_COMMENTS, '', 'notes');
echo $funcField('comments', _SYSTEM_LABEL_COMMENTS, array('multiline' => true, 'width' => 'c12'));
echo ui::section_close();

if (!$booReadonly)
{
    $strNote = '';
    if (!$booProfile && $booNew) $strNote = ui::e(_SYSTEM_USERF_NOTE_NEW);

    echo ui::formactions(array(
        $booProfile ? '' : array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList'), 'class' => 'ghost'),
        array('label' => isset($_REQUEST['confirm']) && !$booProfile ? _SYSTEM_MSG_CREATEUSER_CONFIRMATION3 : ($booNew ? _SYSTEM_USERF_CREATE : _PLOOPI_SAVE), 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
    ), $strNote);
    ?>
    </form>
    <?php
}
else
{
    ?>
    </div>
    <?php
}

// Documents liés à l'utilisateur (administration)
if (!$booProfile && !$booNew)
{
    echo ui::card_open(_SYSTEM_USERF_DOCUMENTS, 'folders', array(), array('class' => 'p4-mt-16'));
    echo '<div class="p4-legacy">';
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
        array('PHOTOS', 'VIDEOS', 'DOCUMENTS'),
        array(
            'ROOT_NAME' => trim("{$user->fields['lastname']} {$user->fields['firstname']}"),
            'ATTACHEMENT' => false,
            'FIELDS' => array('name', 'timestp_modify', 'label', 'ref')
        )
    );
    echo '</div>';
    echo ui::card_close();
}

echo ui::script();
