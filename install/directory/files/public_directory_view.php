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
 * Affichage d'un contact
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Création d'un buffer pour alimenter un popup
 */

ob_start();

include_once './modules/directory/class_directory_contact.php';

if (!empty($_GET['directory_id_contact']))
{
    $usr = new directory_contact();
    $usr->open($_GET['directory_id_contact']);
    $popup_title = _DIRECTORY_VIEWCONTACT;
}
elseif (!empty($_GET['directory_id_user']))
{
    $usr = new user();
    $usr->open($_GET['directory_id_user']);
    $popup_title = _DIRECTORY_VIEWUSER;
}
else ploopi_die();
?>

<div>
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_NAME; ?>:</label>
                <span><? echo htmlentities($usr->fields['lastname']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_FIRSTNAME; ?>:</label>
                <span><? echo htmlentities($usr->fields['firstname']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_SERVICE; ?>:</label>
                <span><? echo htmlentities($usr->fields['service']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_FUNCTION; ?>:</label>
                <span><? echo htmlentities($usr->fields['function']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_PHONE; ?>:</label>
                <span><? echo htmlentities($usr->fields['phone']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_MOBILE; ?>:</label>
                <span><? echo htmlentities($usr->fields['mobile']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_FAX; ?>:</label>
                <span><? echo htmlentities($usr->fields['fax']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_EMAIL; ?>:</label>
                <span><a href="mailto:<? echo htmlentities($usr->fields['email']); ?>"><? echo htmlentities($usr->fields['email']); ?></a></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_COMMENTARY; ?>:</label>
                <span><? echo ploopi_nl2br(htmlentities($usr->fields['comments'])); ?></span>
            </p>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_ADDRESS; ?>:</label>
                <span><? echo ploopi_nl2br(htmlentities($usr->fields['address'])); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_POSTALCODE; ?>:</label>
                <span><? echo htmlentities($usr->fields['postalcode']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_CITY; ?>:</label>
                <span><? echo htmlentities($usr->fields['city']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_COUNTRY; ?>:</label>
                <span><? echo htmlentities($usr->fields['country']); ?></span>
            </p>
        </div>
    </div>
</div>

<?
/**
 * On récupère le contenu du buffer
 */

$content = ob_get_contents();
ob_end_clean();

/**
 * On affiche le popup
 */

echo $skin->create_popup($popup_title, $content, "popup_directory_view{$_GET['directory_id_user']}_{$_GET['directory_id_contact']}");
?>
