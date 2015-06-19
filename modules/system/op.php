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
 * Opération du module 'Système'
 *
 * @package system
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Opérations accessibles pour les utilisateurs connectés
 */

if ($_SESSION['ploopi']['connected'])
{
    switch($ploopi_op)
    {
        case 'system_update_profile_save':
            ploopi_init_module('system', false, false, false);

            // Protection contre modification
            if (ploopi_getparam('system_profile_edit_allowed', 1) == '0') ploopi_redirect("admin.php?op=profile");

            $user = new user();
            $user->open($_SESSION['ploopi']['userid']);

            unset($_POST['user_password']);
            unset($_POST['user_login']);

            $user->setvalues($_POST,'user_');


            // Affectation nouveau mot de passe
            $error = '';

            if (isset($_POST['useroldpass']) && isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
            {
                if ($_POST['usernewpass'] != '')
                {
                    // Vérification de l'ancien mot de passe
                    if (strcmp($user->fields['password'], user::generate_hash($_POST['useroldpass'], $user->fields['login'])) == 0)
                    {
                        // Mots de passes équivalents
                        if ($_POST['usernewpass'] == $_POST['usernewpass_confirm'])
                        {
                            // Complexité ok
                            if (!_PLOOPI_USE_COMPLEXE_PASSWORD || ploopi_checkpasswordvalidity($_POST['usernewpass']))
                            {
                                // Affectation du mot de passe
                                $user->setpassword($_POST['usernewpass']);
                                // Mise à jour htpasswd
                                if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], $_POST['usernewpass']);
                            }
                            else $error = 'passrejected';
                        }
                        else $error = 'password';
                    }
                    else $error = 'oldpassword';
                }
            }

            $user->save();
            $_SESSION['ploopi']['user'] = $user->fields;

            ob_start();
            ?>
            <div style="padding:10px;text-align:center;"><strong>Profil enregistré !</strong></div>
            <?
            if ($error) {
                switch($error)
                {
                    case 'password':
                        $msg = ploopi_nl2br(_SYSTEM_MSG_PASSWORDERROR);
                    break;

                    case 'oldpassword':
                        $msg = ploopi_nl2br(_SYSTEM_MSG_OLDPASSWORDERROR);
                    break;

                    case 'passrejected':
                        $msg = ploopi_nl2br(_SYSTEM_MSG_LOGINPASSWORDERROR);
                    break;

                    case 'login':
                        $msg = ploopi_nl2br(_SYSTEM_MSG_LOGINERROR);
                    break;
                }
                ?>
                <div style="padding:10px;text-align:center;"><strong class="error">Erreur lors de l'enregistrement du mot de passe !<br /><? echo $msg; ?></strong></div>
                <?
            }
            $content = ob_get_contents();
            ob_end_clean();

            echo $skin->create_popup('Validation du profil utilisateur', $content, 'system_popup_update_profile');

            ?>
            <script type="text/javascript">
            new PeriodicalExecuter(function(pe) {
                ploopi_hidepopup('system_popup_update_profile');
                pe.stop();
            }, 2);
            </script>
            <?

            ploopi_die();
        break;

        case 'system_update_profile':
            ploopi_init_module('system');

            ob_start();

            $booReadonly = ploopi_getparam('system_profile_edit_allowed', _PLOOPI_MODULE_SYSTEM) == '0';

            $strDisabled = $booReadonly ? 'disabled="disabled"' : '';

            /**
             * Ouverture de l'instance de l'utilisateur à modifier
             */
            $user = new user();
            $user->open($_SESSION['ploopi']['userid']);

            $arrRequiredFields = explode(',', ploopi_getparam('system_user_required_fields', _PLOOPI_MODULE_SYSTEM));

            if (!$booReadonly) {

                // Fonction javascript de validation du formulaire (créée en fonction des champs requis)
                ?>
                <script type="text/javascript">
                system_user_validate = function (form)
                {
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LASTNAME; ?>", form.user_lastname, 'string'))
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FIRSTNAME; ?>", form.user_firstname, 'string'))

                    <? if (in_array('civility', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_CIVILITY; ?>", form.user_civility, 'select'))
                    <? } ?>

                    <? if (in_array('entity', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_ENTITY; ?>", form.user_entity, 'string'))
                    <? } ?>
                    <? if (in_array('service', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_SERVICE; ?>", form.user_service, 'string'))
                    <? } ?>
                    <? if (in_array('service2', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_SERVICE2; ?>", form.user_service2, 'string'))
                    <? } ?>
                    <? if (in_array('function', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FUNCTION; ?>", form.user_function, 'string'))
                    <? } ?>
                    <? if (in_array('rank', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_RANK; ?>", form.user_rank, 'string'))
                    <? } ?>
                    <? if (in_array('number', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_NUMBER; ?>", form.user_number, 'string'))
                    <? } ?>

                    <? if (in_array('phone', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"phone"))
                    <? } else { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"emptyphone"))
                    <? } ?>
                    <? if (in_array('mobile', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"phone"))
                    <? } else { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"emptyphone"))
                    <? } ?>
                    <? if (in_array('fax', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"phone"))
                    <? } else { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"emptyphone"))
                    <? } ?>

                    <? if (in_array('email', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"email"))
                    <? } else { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"emptyemail"))
                    <? } ?>

                    <? if (in_array('building', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_BUILDING; ?>", form.user_building, 'string'))
                    <? } ?>
                    <? if (in_array('floor', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FLOOR; ?>", form.user_floor, 'string'))
                    <? } ?>
                    <? if (in_array('office', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_OFFICE; ?>", form.user_office, 'string'))
                    <? } ?>
                    <? if (in_array('address', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_ADDRESS; ?>", form.user_address, 'string'))
                    <? } ?>
                    <? if (in_array('postalcode', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_POSTALCODE; ?>", form.user_postalcode, 'string'))
                    <? } ?>
                    <? if (in_array('city', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_CITY; ?>", form.user_city, 'string'))
                    <? } ?>
                    <? if (in_array('country', $arrRequiredFields)) { ?>
                    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_COUNTRY; ?>", form.user_country, 'string'))
                    <? } ?>
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

                <?
                // Vérification de la validité du profil
                $booUpdateProfile = false;
                foreach($arrRequiredFields as $strField)
                {
                    $strField = trim($strField);
                    if (isset($user->fields[$strField]) && $user->fields[$strField] == '') { $booUpdateProfile = true; break; }
                }

                if ($booUpdateProfile)
                {
                    ?>
                    <div style="padding:10px;text-align:center;">
                        Votre profil utilisateur n'est pas complet. Merci de remplir les champs marqués d'une étoile.
                        <br /><em>Vous pouvez passer cette étape en cliquant en bas sur le bouton "Annuler".</em>
                    </div>
                    <?
                }
                ?>

                <form name="form_modify_user" action="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=system_update_profile_save'); ?>" method="POST" onsubmit="javascript:ploopi_xmlhttprequest_submitform(this, 'system_popup_update_profile', system_user_validate ); return false;  ">
                <?
            }
            ?>

            <div>
                <div style="float:left;width:50%;">
                    <div style="padding:2px;">
                        <fieldset class="fieldset">
                            <legend>Informations personnelles</legend>
                            <div class="ploopi_form">
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_LASTNAME; ?> *:</label>
                                    <input type="text" class="text" name="user_lastname"  value="<?php echo ploopi_htmlentities($user->fields['lastname']); ?>" <? echo $strDisabled; ?> tabindex="1" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_FIRSTNAME; ?> *:</label>
                                    <input type="text" class="text" name="user_firstname"  value="<?php echo ploopi_htmlentities($user->fields['firstname']); ?>" <? echo $strDisabled; ?> tabindex="2" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_CIVILITY; ?><? if (in_array('civility', $arrRequiredFields)) echo ' *'; ?> :</label>
                                    <select class="select" name="user_civility" style="width:100px;" <? echo $strDisabled; ?> tabindex="3">
                                        <option value=""></option>
                                        <?php
                                        foreach ($ploopi_civility as $value)
                                        {
                                            ?>
                                            <option value="<?php echo ploopi_htmlentities($value); ?>" <?php if ($user->fields['civility'] == $value) echo 'selected'; ?>><?php echo ploopi_htmlentities($value); ?></option>
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
                                    <label><?php echo _SYSTEM_LABEL_ENTITY; ?><? if (in_array('entity', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_entity"  value="<?php echo ploopi_htmlentities($user->fields['entity']); ?>" <? echo $strDisabled; ?> tabindex="4" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_SERVICE; ?><? if (in_array('service', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_service"  value="<?php echo ploopi_htmlentities($user->fields['service']); ?>" <? echo $strDisabled; ?> tabindex="4" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_SERVICE2; ?><? if (in_array('service2', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_service2"  value="<?php echo ploopi_htmlentities($user->fields['service2']); ?>" <? echo $strDisabled; ?> tabindex="4" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_FUNCTION; ?><? if (in_array('function', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_function"  value="<?php echo ploopi_htmlentities($user->fields['function']); ?>" <? echo $strDisabled; ?> tabindex="5" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_RANK; ?><? if (in_array('rank', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_rank"  value="<?php echo ploopi_htmlentities($user->fields['rank']); ?>" <? echo $strDisabled; ?> tabindex="6" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_NUMBER; ?><? if (in_array('number', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_number"  value="<?php echo ploopi_htmlentities($user->fields['number']); ?>" <? echo $strDisabled; ?> tabindex="7" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_PHONE; ?><? if (in_array('phone', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_phone"  value="<?php echo ploopi_htmlentities($user->fields['phone']); ?>" <? echo $strDisabled; ?> tabindex="8" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_MOBILE; ?><? if (in_array('mobile', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_mobile"  value="<?php echo ploopi_htmlentities($user->fields['mobile']); ?>" <? echo $strDisabled; ?> tabindex="9" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_FAX; ?><? if (in_array('fax', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_fax"  value="<?php echo ploopi_htmlentities($user->fields['fax']); ?>" <? echo $strDisabled; ?> tabindex="10" />
                                </p>
                            </div>
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend>Messagerie</legend>
                            <div class="ploopi_form">
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_EMAIL; ?> *:</label>
                                    <input type="text" class="text" name="user_email"  value="<?php echo ploopi_htmlentities($user->fields['email']); ?>" <? echo $strDisabled; ?> tabindex="25" />
                                </p>
                                <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_ticketsbyemail');">
                                    <label><?php echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                                    <input style="width:16px;" type="checkbox" id="user_ticketsbyemail" name="user_ticketsbyemail" value="1" <?php if ($user->fields['ticketsbyemail']) echo 'checked'; ?> <? echo $strDisabled; ?> tabindex="26" />
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
                                    <strong><?php echo ploopi_htmlentities($user->fields['login']); ?></strong>
                                </p>
                                <p>
                                    <label>Ancien mot de passe:</label>
                                    <input type="password" class="text" name="useroldpass" id="useroldpass" value="" tabindex="22" <? echo $strDisabled; ?> style="width:180px;" />
                                </p>
                                <p>
                                    <label>Nouveau mot de passe:</label>
                                    <input type="password" class="text" name="usernewpass" id="usernewpass" value="" tabindex="22" <? echo $strDisabled; ?> style="width:180px;" />
                                </p>
                                <div id="protopass"></div>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                                    <input type="password" class="text" name="usernewpass_confirm" id="usernewpass_confirm" value="" tabindex="23" <? echo $strDisabled; ?> style="width:180px;" />
                                </p>
                            </div>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend>Lieu de travail</legend>
                            <div class="ploopi_form">
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_BUILDING; ?><? if (in_array('building', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_building"  value="<?php echo ploopi_htmlentities($user->fields['building']); ?>" <? echo $strDisabled; ?> tabindex="11" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_FLOOR; ?><? if (in_array('floor', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_floor"  value="<?php echo ploopi_htmlentities($user->fields['floor']); ?>" <? echo $strDisabled; ?> tabindex="12" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_OFFICE; ?><? if (in_array('office', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_office"  value="<?php echo ploopi_htmlentities($user->fields['office']); ?>" <? echo $strDisabled; ?> tabindex="13" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_ADDRESS; ?><? if (in_array('address', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <textarea class="text" name="user_address" <? echo $strDisabled; ?> tabindex="14"><?php echo ploopi_htmlentities($user->fields['address']); ?></textarea>
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_POSTALCODE; ?><? if (in_array('postalcode', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_postalcode"  value="<?php echo ploopi_htmlentities($user->fields['postalcode']); ?>" <? echo $strDisabled; ?> tabindex="15" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_CITY; ?><? if (in_array('city', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_city"  value="<?php echo ploopi_htmlentities($user->fields['city']); ?>" <? echo $strDisabled; ?> tabindex="16" />
                                </p>
                                <p>
                                    <label><?php echo _SYSTEM_LABEL_COUNTRY; ?><? if (in_array('country', $arrRequiredFields)) echo ' *'; ?>:</label>
                                    <input type="text" class="text" name="user_country"  value="<?php echo ploopi_htmlentities($user->fields['country']); ?>" <? echo $strDisabled; ?> tabindex="17" />
                                </p>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>

            <? if (!$booReadonly) { ?>

                <div style="clear:both;text-align:right;padding:4px;">
                    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popup_update_profile');" />
                    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" />
                </div>
                </form>
                <script type="text/javascript">
                    $('useroldpass').value = '';
                    $('usernewpass').value = '';
                    $('usernewpass_confirm').value = '';


                    <? if (_PLOOPI_USE_COMPLEXE_PASSWORD) { ?>
                    var options = {
                        minchar: <? echo _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE; ?>,
                        scores: [5, 10, 20, 30]
                    };
                    <? } else { ?>
                    var options = {
                        minchar: 6,
                        scores: [5, 10, 20, 30]
                    };
                    <? } ?>

                    new Protopass('usernewpass', 'protopass', options);

                    Event.observe($('usernewpass_confirm'), 'change', function() {

                        if ($('usernewpass').value == $('usernewpass_confirm').value) {
                            $('usernewpass_confirm').style.backgroundColor = $('usernewpass').style.backgroundColor = 'lightgreen';
                        } else {
                            $('usernewpass_confirm').style.backgroundColor = $('usernewpass').style.backgroundColor = 'indianred';
                        }
                    });
                </script>

            <? } else { ?>

                <div style="clear:both;text-align:right;padding:4px;">
                    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popup_update_profile');" />
                </div>

            <? } ?>

            <style>
                #protopass {padding:0;margin:0;margin-left:30%;padding-left:0.5em;width:195px;} #protopass * {font-size:10px;}
                #protopass .password-strength-bar {border-radius:2px;}
            </style>

            <?
            $content = ob_get_contents();
            ob_end_clean();

            echo $skin->create_popup('Validation du profil utilisateur', $content, 'system_popup_update_profile');

            ploopi_die();
        break;
    }

}

if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SEARCH)
{
    switch($ploopi_op)
    {
        /**
         * Moteur de recherche
         */

        case 'system_search':
            include_once('./modules/system/public_search_result.php');
            ploopi_die();
        break;
    }
}

/**
 * Opérations accessibles pour les utilisateurs connectés dans le module système
 */
if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM)
{
    switch($ploopi_op)
    {
        case 'system_directory_export':
            ploopi_init_module('system');

            include_once './include/functions/array.php';
            include_once './include/classes/odf.php';

            // Type du document demandé
            $strTypeDoc = empty($_GET['system_directory_typedoc']) ? 'xls' : strtolower($_GET['system_directory_typedoc']);

            // Récupération de la requête utilisateur
            $strSql = ploopi_getsessionvar('directory_sql');

            // Préparation du jeu de données
            $arrData = array();

            if (!empty($strSql)) {
                $res = $db->query($strSql);

                while ($row = $db->fetchrow($res)) {

                    // on va chercher les espaces auxquels l'utilisateur peut accéder
                    $objUser = new user();
                    $objUser->open($row['id']);
                    // on met les libellés dans un tableau
                    $arrWspList = array();
                    foreach($objUser->getworkspaces() as $rowW) $arrWspList[sprintf("%04d%s", $rowW['depth'], $rowW['label'])] = $rowW['label'];
                    // on trie par profondeur + libellé
                    ksort($arrWspList);

                    // Traitement spécial XLS (date)
                    if ($strTypeDoc == 'xls' || $strTypeDoc == 'xlsx' || $strTypeDoc == 'ods' || $strTypeDoc == 'sxc' || $strTypeDoc == 'pdf') {
                        $row['date_creation'] = ploopi_timestamp2unixtimestamp($row['date_creation']);
                    }


                    $rowData = array(
                        'login' => $row['login'],
                        'lastname' => $row['lastname'],
                        'firstname' => $row['firstname'],
                        'user' => trim($row['lastname'].' '.$row['firstname']),
                        'workspaces' => implode("\n\r",$arrWspList),
                        'service' => $row['service'],
                        'service2' => $row['service2'],
                        'function' => $row['function'],
                        'rank' => $row['rank'],
                        'number' => $row['number'],
                        'phone' => system_directory_formatphone($row['phone']),
                        'mobile' => system_directory_formatphone($row['mobile']),
                        'fax' => system_directory_formatphone($row['fax']),
                        'email' => $row['email'],

                        'building' => $row['building'],
                        'floor' => $row['floor'],
                        'office' => $row['office'],
                        'address' => $row['address'],
                        'postalcode' => $row['postalcode'],
                        'city' => $row['city'],
                        'country' => $row['country'],
                        'date_creation' => $row['date_creation']
                    );

                    // Traitement spécial vCard
                    if ($strTypeDoc != 'vcf') {
                        unset($rowData['lastname']);
                        unset($rowData['firstname']);
                    }

                    $arrData[] = $rowData;

                }
            }

            // Dossier de travail
            $strOutputPath = _PLOOPI_PATHDATA.'/tmp/system_directory';
            ploopi_makedir($strOutputPath);

            // Nom du fichier envoyé à l'utilisateur
            $strFileName = "utilisateurs.{$strTypeDoc}";


            // Format d'export (XLS et dérivés)
            $arrFormats = array(
                'login' => array(
                    'title' => 'Identifiant',
                    'type' => 'string',
                    'width' => 15
                ),
                'user' => array(
                    'title' => 'Utilisateur',
                    'type' => 'string',
                    'width' => 20
                ),
                'workspaces' => array(
                    'title' => 'Liste des Espaces de Travail',
                    'type' => 'string',
                    'width' => 40
                ),
                'service' => array(
                    'title' => 'Service',
                    'type' => 'string',
                    'width' => 20
                ),
                'service2' => array(
                    'title' => 'Bureau',
                    'type' => 'string',
                    'width' => 20
                ),
                'function' => array(
                    'title' => 'Fonction',
                    'type' => 'string',
                    'width' => 20
                ),
                'rank' => array(
                    'title' => 'Grade',
                    'type' => 'string',
                    'width' => 20
                ),
                'number' => array(
                    'title' => 'Numéro de Poste',
                    'type' => 'string',
                    'width' => 20
                ),
                'phone' => array(
                    'title' => 'Téléphone',
                    'type' => 'string',
                    'width' => 20
                ),
                'mobile' => array(
                    'title' => 'Mobile',
                    'type' => 'string',
                    'width' => 20
                ),
                'fax' => array(
                    'title' => 'Fax',
                    'type' => 'string',
                    'width' => 20
                ),
                'email' => array(
                    'title' => 'Courriel',
                    'type' => 'string',
                    'width' => 35
                ),
                'building' => array(
                    'title' => 'Bâtiment',
                    'type' => 'string',
                    'width' => 20
                ),
                'floor' => array(
                    'title' => 'Etage',
                    'type' => 'string',
                    'width' => 20
                ),
                'office' => array(
                    'title' => 'Bureau',
                    'type' => 'string',
                    'width' => 20
                ),
                'address' => array(
                    'title' => 'Adresse',
                    'type' => 'string',
                    'width' => 20
                ),
                'postalcode' => array(
                    'title' => 'Code Postal',
                    'type' => 'string',
                    'width' => 20
                ),
                'city' => array(
                    'title' => 'Ville',
                    'type' => 'string',
                    'width' => 20
                ),
                'country' => array(
                    'title' => 'Pays',
                    'type' => 'string',
                    'width' => 20
                ),
                'date_creation' => array(
                    'title' => 'Date de création',
                    'type' => 'datetime',
                    'width' => 35
                )
            );


            ploopi_ob_clean();

            switch($strTypeDoc)
            {

                case 'vcf': // vCard

                    foreach($arrData as $row) {
                        echo "BEGIN:VCARD\nVERSION:3.0\nN:{$row['lastname']};{$row['firstname']}\nFN:{$row['user']}\nTEL;type=WORK,VOICE:{$row['phone']}\nTEL;type=WORK,CELL:{$row['mobile']}\nTEL;type=WORK,FAX:{$row['fax']}\nEMAIL;type=PREF,INTERNET:{$row['email']}\nEND:VCARD\n";
                    }
                break;

                case 'csv':
                    echo ploopi_array2csv($arrData);
                break;

                case 'xml':
                    echo ploopi_array2xml($arrData);
                break;

                case 'json':
                    echo ploopi_array2json($arrData);
                break;

                case 'xls':
                    echo ploopi_array2excel($arrData, true, $strFileName, 'Export', $arrFormats, array('writer' => 'excel5'));
                break;

                case 'xlsx':
                    echo ploopi_array2excel($arrData, true, $strFileName, 'Export', $arrFormats, array('writer' => 'excel2007'));
                break;

                case 'ods':
                case 'sxc':
                case 'pdf':
                    $objOdfConverter = new odf_converter(ploopi_getparam('system_jodwebservice'));

                    switch($strTypeDoc)
                    {
                        case 'pdf':
                            $strOuputMime = 'application/pdf';
                        break;

                        case 'sxc':
                            $strOuputMime = 'application/vnd.sun.xml.calc';
                        break;

                        case 'ods':
                            $strOuputMime = 'application/vnd.oasis.opendocument.spreadsheet';
                        break;
                    }

                    echo $objOdfConverter->convert(ploopi_array2excel($arrData, true, $strFileName, 'Export', $arrFormats, array('writer' => 'excel2007')), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $strOuputMime);

                break;
            }

            header('Content-Type: ' . ploopi_getmimetype($strFileName));
            header('Content-Disposition: attachment; Filename="'.$strFileName.'"');
            header('Cache-Control: private');
            header('Pragma: private');
            header('Content-Length: '.ob_get_length());
            header("Content-Encoding: None");

            ploopi_die();
        break;

        /**
         * Opérations sur les tickets
         */
        case 'tickets_delete':
            include_once './include/classes/ticket.php';

            $arrTickets = array();

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $arrTickets[] = $_GET['ticket_id'];
            }
            elseif (isset($_POST['tickets_delete_id']) && is_array($_POST['tickets_delete_id']))
            {
                $arrTickets = $_POST['tickets_delete_id'];
            }

            foreach($arrTickets as $ticket_id)
            {
                $ticket = new ticket();
                if (is_numeric($ticket_id) && $ticket->open($ticket_id))
                {
                    if ($_SESSION['ploopi']['userid'] == $ticket->fields['id_user']) // utilisateur = emetteur
                    {
                        $ticket->fields['deleted'] = 1;
                        $ticket->save();
                    }

                    $ticket_dest = new ticket_dest();
                    if ($ticket_dest->open($_SESSION['ploopi']['userid'], $ticket_id))
                    {
                        $ticket_dest->fields['deleted'] = 1;
                        $ticket_dest->save();
                    }
                }
            }

            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;

        case 'tickets_open':
            include_once './include/classes/ticket.php';
            $ticket = new ticket();

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id']))
            {
                $ticket_status = new ticket_status();

                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_OPENED))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_OPENED;
                    $ticket_status->save();
                }

                $ticket_watch = new ticket_watch();
                $ticket_watch->open($_GET['ticket_id'], $_SESSION['ploopi']['userid']);
                $ticket_watch->fields['id_ticket'] = $_GET['ticket_id'];
                $ticket_watch->fields['id_user'] = $_SESSION['ploopi']['userid'];
                $ticket_watch->fields['notify'] = 0;
                $ticket_watch->save();

                $ticket->fields['count_read']++;
                $ticket->save();
            }
            ploopi_die();
        break;

        case 'tickets_open_responses':
            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $rootid = $db->addslashes($_GET['ticket_id']);

                $sql =  "
                        SELECT      t.id,
                                    t.title,
                                    t.message,
                                    t.timestp,
                                    t.lastedit_timestp,
                                    t.id_module,
                                    t.parent_id,
                                    t.root_id,
                                    t.id_user as sender_uid,
                                    ts.status,
                                    u.login,
                                    u.firstname,
                                    u.lastname

                        FROM        ploopi_ticket t

                        INNER JOIN  ploopi_user u
                        ON          t.id_user = u.id

                        LEFT JOIN   ploopi_ticket_status ts
                        ON          ts.id_ticket = t.id
                        AND         ts.id_user = {$_SESSION['ploopi']['userid']}

                        WHERE       t.root_id = {$rootid}
                        AND         t.id <> {$rootid}

                        ORDER BY    t.timestp DESC
                        ";

                $tickets = array();
                $parents = array();

                $rs = $db->query($sql);

                while ($fields = $db->fetchrow($rs))
                {
                    if (!isset($tickets[$fields['id']]))
                    {
                        $tickets[$fields['id']] = $fields;
                        $parents[$fields['parent_id']][] = $fields['id'];
                    }

                }

                if (!empty($tickets)) system_tickets_displayresponses($parents, $tickets, $_GET['ticket_id']);
            }
            ploopi_die();
        break;

        case 'tickets_validate':
            include_once './include/classes/ticket.php';

            $ticket_status = new ticket_status();

            if (!empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_DONE))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_DONE;
                    $ticket_status->save();
                }
            }
            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;


        default:
            /**
             * Autres opérations nécessitant un niveau d'accrédiation plus élevé (gestionnaire ou admin sys)
             */

            if (ploopi_isadmin())
            {
                switch($ploopi_op)
                {
                    // update description
                    case 'updatedesc':
                        include_once './include/classes/module.php';
                        ploopi_init_module('system', false, false, false);

                        $module_type = new module_type();
                        if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                        {
                            $xmlfile_desc = "./install/{$module_type->fields['label']}/description.xml";
                            $critical_error = $module_type->update_description($xmlfile_desc);
                            if (!$critical_error) ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMODULE, "{$module_type->fields['label']} (reload)");
                        }

                        ploopi_redirect('admin.php');
                    break;

                    // update metabase
                    case 'updatemb':
                        include_once './include/classes/module.php';
                        ploopi_init_module('system', false, false, false);

                        $module_type = new module_type();
                        if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                        {
                            global $idmoduletype;
                            $idmoduletype = $_GET['idmoduletype'];

                            include './modules/system/xmlparser_mb.php';

                            ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMETABASE, $module_type->fields['label']);

                            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$_GET['idmoduletype']}");

                            $mbfile = "./install/{$module_type->fields['label']}/mb.xml";

                            if (file_exists($mbfile))
                            {
                                $xml_parser = xmlparser_mb();
                                xml_parse($xml_parser,  file_get_contents($mbfile));
                                xml_parser_free($xml_parser);
                            }
                        }

                        ploopi_redirect('admin.php');
                    break;
                }
            }

            if (ploopi_ismanager())
            {
                switch($ploopi_op)
                {
                    case 'system_roleusers':
                        if (empty($_GET['system_roleusers_roleid'])) ploopi_die();
                        $roleid = $_GET['system_roleusers_roleid'];
                        include './modules/system/admin_index_roles_assignment_list.php';
                        ploopi_die();
                    break;

                    // suppression de l'affectation d'un rôle à un utilisateur
                    case 'system_roleusers_delete_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wur = new workspace_user_role();

                        if ($wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wur->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // suppression de l'affectation d'un rôle à un groupe
                    case 'system_roleusers_delete_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wgr = new workspace_group_role();

                        if ($wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wgr->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un utilisateur
                    case 'system_roleusers_select_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wur = new workspace_user_role();

                        if (!$wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wur->fields['id_user'] = $_GET['system_roleusers_userid'];
                            $wur->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wur->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wur->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un groupe
                    case 'system_roleusers_select_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wgr = new workspace_group_role();

                        if (!$wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wgr->fields['id_group'] = $_GET['system_roleusers_groupid'];
                            $wgr->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wgr->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wgr->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // résultat de la recherche utilisateurs / groupes
                    case 'system_roleusers_search':
                        if (!isset($_GET['system_roleusers_filter'])) ploopi_die();

                        $cleanedfilter = $db->addslashes($_GET['system_roleusers_filter']);
                        $userfilter = "(u.login LIKE '%{$cleanedfilter}%' OR u.firstname LIKE '%{$cleanedfilter}%' OR u.lastname LIKE '%{$cleanedfilter}%')";

                        $sql =  "
                                SELECT      u.id,
                                            u.lastname,
                                            u.firstname,
                                            u.login,
                                            u.service

                                FROM        ploopi_user u

                                INNER JOIN  ploopi_workspace_user wu
                                ON          wu.id_user = u.id
                                AND         wu.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$userfilter}

                                ORDER BY    u.lastname, u.firstname
                                ";

                        $db->query($sql);
                        $users = $db->getarray();

                        $groupfilter = "g.label LIKE '%{$cleanedfilter}%'";

                        $sql =  "
                                SELECT      g.id,
                                            g.label,
                                            g.parents

                                FROM        ploopi_group g

                                INNER JOIN  ploopi_workspace_group wg
                                ON          wg.id_group = g.id
                                AND         wg.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$groupfilter}

                                ORDER BY    g.label
                                ";

                        $db->query($sql);
                        $groups = $db->getarray();

                        if (empty($users) && empty($groups))
                        {
                            ?>
                            <p class="ploopi_va" style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">
                                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png">
                                <span>aucun utilisateur/groupe trouv&eacute;</span>
                            </p>
                            <?php
                        }
                        else
                        {
                            ?>
                            <div id="system_roleusers_result">
                                <?php
                                // pour chaque groupe
                                foreach($groups as $group)
                                {
                                    ?>
                                    <a class="system_roleusers_select" title="Sélectionner ce groupe et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<?php echo ploopi_htmlentities($_GET['system_roleusers_roleid']); ?>, <?php echo $group['id']; ?>, 'group');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><?php echo ploopi_htmlentities("{$group['label']}"); ?></span></p>
                                    </a>
                                    <?php
                                }
                                ?>
                                <?php
                                // pour chaque utilisateur
                                foreach($users as $user)
                                {
                                    ?>
                                    <a class="system_roleusers_select" title="Sélectionner cet utilisateur et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<?php echo ploopi_htmlentities($_GET['system_roleusers_roleid']); ?>, <?php echo $user['id']; ?>, 'user');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo ploopi_htmlentities("{$user['lastname']} {$user['firstname']} ({$user['login']})"); ?></span></p>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            <div id="system_roleusers_legend">
                                <p class="ploopi_va" style="float:right;">
                                    <span style="font-weight:bold;">Légende:&nbsp;&nbsp;&nbsp;</span>
                                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>&nbsp;Groupe d'Utilisateur&nbsp;&nbsp;</span>
                                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>&nbsp;Utilisateur</span>
                                </p>
                                <p class="ploopi_va" style="float:left;">
                                    <span>Cliquez sur un utilisateur ou un groupe pour l'ajouter</span>
                                </p>
                            </div>
                            <?php
                        }

                        ploopi_die();
                    break;

                    case 'system_serverload':
                        include './modules/system/tools_serverload.php';
                        ploopi_die();
                    break;

                    case 'system_tools_phpinfo':
                        phpinfo();
                        ?>
                        <script type="text/javascript">
                        function system_autofit_iframe()
                        {
                            try
                            {
                                if (document.getElementById || !window.opera && !document.mimeType && document.all && document.getElementById)
                                {
                                    height = this.document.body.scrollHeight + 50;
                                    if (height < 400) height = 400;
                                    parent.document.getElementById('system_tools_phpinfo').style.height = height + 'px';
                                }
                            }
                            catch (e)
                            {
                                height = this.document.body.offsetHeight;
                                if (height < 400) height = 400;
                                parent.document.getElementById('system_tools_phpinfo').style.height = height + 'px';
                            }
                        }

                        window.onload = function() { system_autofit_iframe();};
                        </script>
                        <?php
                        ploopi_die();
                    break;

                    case 'system_choose_photo':
                        // Popup de choix d'une photo pour un utilisateur
                        if (!empty($_GET['system_user_id']) && is_numeric($_GET['system_user_id']))
                        {
                            ob_start();
                            ploopi_init_module('system');
                            ?>
                            <form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=system_send_photo&system_user_id={$_GET['system_user_id']}"); ?>" method="post" enctype="multipart/form-data" target="system_user_photo_iframe">
                            <p class="ploopi_va" style="padding:2px;">
                                <label><?php echo _SYSTEM_LABEL_PHOTO; ?>: </label>
                                <input type="file" name="system_user_photo" />
                                <input type="submit" class="button" name="<?php echo _PLOOPI_SAVE; ?>" />
                            </p>
                            </form>
                            <iframe name="system_user_photo_iframe" style="display:none;"></iframe>
                            <?php
                            $content = ob_get_contents();
                            ob_end_clean();

                            echo $skin->create_popup("Chargement d'une nouvelle photo", $content, 'popup_system_choose_photo');
                        }
                        ploopi_die();
                    break;

                    case 'system_send_photo':
                        if (!empty($_GET['system_user_id']) && is_numeric($_GET['system_user_id']))
                        {
                            // Envoi d'une photo temporaire dans la fiche utilisateur
                            // On vérifie qu'un fichier a bien été uploadé
                            if (!empty($_FILES['system_user_photo']['tmp_name']))
                            {
                                // reset suppression
                                ploopi_setsessionvar("deletephoto_{$_GET['system_user_id']}", 0);

                                $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
                                ploopi_makedir($strTmpPath);
                                $_SESSION['system']['user_photopath'] = tempnam($strTmpPath, '');
                                ploopi_resizeimage($_FILES['system_user_photo']['tmp_name'], 0, 100, 150, 'png', 0, $_SESSION['system']['user_photopath']);
                            }
                            ?>
                            <script type="text/javascript">
                                new function() {
                                    window.parent.ploopi_getelem('system_user_photo', window.parent.document).innerHTML = '<img src="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=system_get_photo'); ?>" />';
                                    window.parent.ploopi_hidepopup('popup_system_choose_photo');
                                }
                            </script>
                            <?php
                        }
                    break;

                    case 'system_delete_photo':
                        if (!empty($_GET['system_user_id']) && is_numeric($_GET['system_user_id']))
                        {
                            ploopi_setsessionvar("deletephoto_{$_GET['system_user_id']}", 1);
                            $_SESSION['system']['user_photopath'] = '';
                            ploopi_die();
                        }
                    break;

                    case 'system_get_photo':
                        // Envoi de la photo temporaire vers le client
                        if (!empty($_SESSION['system']['user_photopath'])) ploopi_downloadfile($_SESSION['system']['user_photopath'], 'user.png', false, false);
                        ploopi_die();
                    break;

                    case 'system_delete_user':
                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                        {
                            ploopi_init_module('system');
                            $objUser = new user();
                            if (!empty($_GET['system_user_id']) && is_numeric($_GET['system_user_id']) && $objUser->open($_GET['system_user_id']))
                            {
                                if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($objUser->fields['login'], '', true);
                                ploopi_create_user_action_log(_SYSTEM_ACTION_DELETEUSER, "{$objUser->fields['login']} - {$objUser->fields['lastname']} {$objUser->fields['firstname']} (id:{$objUser->fields['id']})");
                                $objUser->delete();
                            }
                        }
                        ploopi_redirect('admin.php?system_level=system&sysToolbarItem=directory');
                    break;

                    case 'system_user_import':

                        $_SESSION['system']['user_import'] = array();

                        if (!empty($_FILES['system_user_file']) && !empty($_FILES['system_user_file']['name']))
                        {
                            // Récupération & contrôle du séparateur de champs
                            $strSep = empty($_POST['system_user_sep']) ? ',' : $_POST['system_user_sep'];
                            if (!in_array($strSep, array(',', ';'))) $strSep = ',';

                            // Lecture du fichier si ok
                            if (file_exists($_FILES['system_user_file']['tmp_name']))
                            {
                                $ptrHandle = fopen($_FILES['system_user_file']['tmp_name'], 'r');

                                while (($arrLineData = fgetcsv($ptrHandle, null, $strSep)) !== FALSE)
                                {
                                    if (is_array($arrLineData))
                                    {
                                        $_SESSION['system']['user_import'][] = $arrLineData;
                                    }
                                }
                            }
                        }

                        ploopi_redirect("admin.php?usrTabItem=tabUserImport&op=preview");
                    break;
                }
            }
        break;
    }
}
?>
