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
?>
<script type="text/javascript">
function user_validate(form)
{
    if (ploopi_validatefield("<? echo _SYSTEM_LABEL_LASTNAME; ?>",form.user_lastname,"string"))
    if (ploopi_validatefield("<? echo _SYSTEM_LABEL_FIRSTNAME; ?>",form.user_firstname,"string"))
    {
        if (form.usernewpass_confirm.value == form.usernewpass.value && form.usernewpass.value == '') return true;
        else
        {
            rep = ploopi_xmlhttprequest('admin-light.php', 'ploopi_op=ploopi_checkpasswordvalidity&password='+form.usernewpass.value)
            if (rep == 0)
            {
                alert('Le mot de passe est invalide\n\nil doit contenir au moins 8 caractères,\nun caractère minuscule,\nun caractère majuscule,\nun chiffre et un caractère de ponctuation');
            }
            else
            {
                if (form.usernewpass_confirm.value != form.usernewpass.value) alert('<? echo _SYSTEM_MSG_PASSWORDERROR; ?>');
                else return true;
            }
        }
    }
    return false;
}
</script>

<? echo $skin->open_simplebloc(_SYSTEM_LABEL_MYACCOUNT); ?>

<form name="form_modify_user" action="<? echo $scriptenv ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return user_validate(this)">
<input type="hidden" name="op" value="save_user">
<div>
<?

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
    <TR>
        <TD ALIGN=CENTER COLSPAN=2>
        <FONT CLASS="Error"><? echo $error; ?></FONT>
        </TD>
    </TR>
    <?
}
?>
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:2px;">
            <p>
                <label><? echo _SYSTEM_LABEL_LASTNAME; ?>:</label>
                <input type="text" class="text" name="user_lastname"  value="<? echo htmlentities($user->fields['lastname']); ?>" tabindex="1" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FIRSTNAME; ?>:</label>
                <input type="text" class="text" name="user_firstname"  value="<? echo htmlentities($user->fields['firstname']); ?>" tabindex="2" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_SERVICE; ?>:</label>
                <input type="text" class="text" name="user_service"  value="<? echo htmlentities($user->fields['service']); ?>" tabindex="3" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FUNCTION; ?>:</label>
                <input type="text" class="text" name="user_function"  value="<? echo htmlentities($user->fields['function']); ?>" tabindex="4" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PHONE; ?>:</label>
                <input type="text" class="text" name="user_phone"  value="<? echo htmlentities($user->fields['phone']); ?>" tabindex="5" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_MOBILE; ?>:</label>
                <input type="text" class="text" name="user_mobile"  value="<? echo htmlentities($user->fields['mobile']); ?>" tabindex="6" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FAX; ?>:</label>
                <input type="text" class="text" name="user_fax"  value="<? echo htmlentities($user->fields['fax']); ?>" tabindex="7" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_ADDRESS; ?>:</label>
                <textarea class="text" name="user_address" tabindex="8"><? echo htmlentities($user->fields['address']); ?></textarea>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_POSTALCODE; ?>:</label>
                <input type="text" class="text" name="user_postalcode"  value="<? echo htmlentities($user->fields['postalcode']); ?>" tabindex="9" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_CITY; ?>:</label>
                <input type="text" class="text" name="user_city"  value="<? echo htmlentities($user->fields['city']); ?>" tabindex="10" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COUNTRY; ?>:</label>
                <input type="text" class="text" name="user_country"  value="<? echo htmlentities($user->fields['country']); ?>"  tabindex="11" />
            </p>
        </div>
    </div>
    <div style="float:left;width:49%" class="ploopi_form">
        <div style="padding:2px;">
            <p>
                <label><? echo _SYSTEM_LABEL_LOGIN; ?>:</label>
                <span><strong><? echo $user->fields['login']; ?></strong></span>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PASSWORD; ?>:</label>
                <input type="password" class="text" name="usernewpass" value="" tabindex="22" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                <input type="password" class="text" name="usernewpass_confirm" value="" tabindex="23" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_EMAIL; ?>:</label>
                <input type="text" class="text" name="user_email"  value="<? echo htmlentities($user->fields['email']); ?>" tabindex="24" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                <input style="width:16px;" type="checkbox" name="user_ticketsbyemail" value="1" <? if ($user->fields['ticketsbyemail']) echo 'checked'; ?> tabindex="25" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_TIMEZONE; ?>:</label>
                <select class="select" name="user_timezone"  tabindex="26">
                <?
                $tz_selected = ($user->fields['timezone'] != '') ? $user->fields['timezone'] : _PLOOPI_DEFAULT_TIMEZONE;

                foreach ($ploopi_timezone as $t => $label)
                {

                    ?>
                    <option value="<? echo $t; ?>" <? if ($t == $tz_selected) echo 'selected'; ?>><? echo $label; ?></option>
                    <?
                }
                ?>
                </select>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COMMENTS; ?>:</label>
                <textarea class="text" name="user_comments" tabindex="28"><? echo htmlentities($user->fields['comments']); ?></textarea>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COLOR; ?>:</label>
                <input type="text" style="width:100px;" class="text" name="user_color" id="user_color" value="<? echo htmlentities($user->fields['color']); ?>" tabindex="29" />
                <a href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('user_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
            </p>
        </div>
    </div>
</div>
<div style="clear:both;float:right;padding:4px;">
    <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
</div>
</form>
<? echo $skin->close_simplebloc(); ?>
