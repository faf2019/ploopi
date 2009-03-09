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
 * Fonctions javascript dynamique du module Système
 *
 * @package system
 * @subpackage javascript
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('system', false, false, false);

?>
function system_group_validate(form)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_GROUP_NAME; ?>",form.group_label,"string")) return(true);

    return(false);
}

function system_workspace_validate(form)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_GROUP_NAME; ?>",form.workspace_label,"string")) return(true);

    return(false);
}

function system_user_validate(form, isnew)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LASTNAME; ?>",form.user_lastname,"string"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FIRSTNAME; ?>",form.user_firstname,"string"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_PHONE; ?>",form.user_phone,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_MOBILE; ?>",form.user_mobile,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_FAX; ?>",form.user_fax,"emptyphone"))
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_EMAIL; ?>",form.user_email,"emptyemail"))
    {
        if (isnew)
        {
            if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LOGIN; ?>",form.user_login,"string"))
            if ((form.usernewpass_confirm.value != form.usernewpass.value) || form.usernewpass.value == '' || form.usernewpass_confirm.value == '') alert('<?php echo _SYSTEM_MSG_PASSWORDERROR; ?>');
            else
            {
                rep = ploopi_xmlhttprequest('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+form.usernewpass.value, false, false, 'POST');
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
                if (form.usernewpass_confirm.value != form.usernewpass.value) alert('<?php echo _SYSTEM_MSG_PASSWORDERROR; ?>');
                else
                {
                    rep = ploopi_xmlhttprequest('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_checkpasswordvalidity&password='+form.usernewpass.value, false, false, 'POST');
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

function role_validate(form)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LABEL; ?>",form.role_label,"string"))
        return true;

    return false;
}
