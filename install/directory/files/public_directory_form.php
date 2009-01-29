<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Modification d'un contact
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Formulaire de modification d'un contact
 */

unset($_SESSION['directory']['contact_photopath']);
?>

<form action="<? echo ploopi_urlencode("admin.php?ploopi_op=directory_contact_save&directory_contact_id={$directory_contact->fields['id']}".(!empty($intHeadingId) ? "&directory_heading_id={$intHeadingId}" : '')); ?>" method="post">
<div style="border-bottom:2px solid #c0c0c0;overflow:auto;">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><? echo _DIRECTORY_NAME; ?>:</label>
                <input type="text" class="text" name="directory_contact_lastname" value="<? echo htmlentities($directory_contact->fields['lastname']); ?>" tabindex="101" />
            </p>
            <p>
                <label><? echo _DIRECTORY_FIRSTNAME; ?>:</label>
                <input type="text" class="text" name="directory_contact_firstname" value="<? echo htmlentities($directory_contact->fields['firstname']); ?>" tabindex="102" />
            </p>
            <p>
                <label><? echo _DIRECTORY_CIVILITY; ?>:</label>
                <select class="select" name="directory_contact_civility" style="width:100px;" tabindex="102">
                    <option value=""></option>
                    <?
                    foreach ($ploopi_civility as $value)
                    {
                        ?>
                        <option value="<? echo htmlentities($value); ?>" <? if ($directory_contact->fields['civility'] == $value) echo 'selected'; ?>><? echo htmlentities($value); ?></option>
                        <?
                    }
                    ?>
                </select>
            </p>
            <p>
                <label><? echo _DIRECTORY_SERVICE; ?>:</label>
                <input type="text" class="text" name="directory_contact_service" value="<? echo htmlentities($directory_contact->fields['service']); ?>" tabindex="103" />
            </p>
            <p>
                <label><? echo _DIRECTORY_OFFICE; ?>:</label>
                <input type="text" class="text" name="directory_contact_office"  value="<? echo htmlentities($directory_contact->fields['office']); ?>" tabindex="103" />
            </p>
            <p>
                <label><? echo _DIRECTORY_FUNCTION; ?>:</label>
                <input type="text" class="text" name="directory_contact_function" value="<? echo htmlentities($directory_contact->fields['function']); ?>" tabindex="104" />
            </p>
            <p>
                <label><? echo _DIRECTORY_NUMBER; ?>:</label>
                <input type="text" class="text" name="directory_contact_number"  value="<? echo htmlentities($directory_contact->fields['number']); ?>" tabindex="104" />
            </p>
            <p>
                <label><? echo _DIRECTORY_PHONE; ?>:</label>
                <input type="text" class="text" name="directory_contact_phone" value="<? echo htmlentities($directory_contact->fields['phone']); ?>" tabindex="105" />
            </p>
            <p>
                <label><? echo _DIRECTORY_MOBILE; ?>:</label>
                <input type="text" class="text" name="directory_contact_mobile" value="<? echo htmlentities($directory_contact->fields['mobile']); ?>" tabindex="106" />
            </p>
            <p>
                <label><? echo _DIRECTORY_FAX; ?>:</label>
                <input type="text" class="text" name="directory_contact_fax" value="<? echo htmlentities($directory_contact->fields['fax']); ?>" tabindex="107" />
            </p>
            <p>
                <label><? echo _DIRECTORY_EMAIL; ?>:</label>
                <input type="text" class="text" name="directory_contact_email" value="<? echo htmlentities($directory_contact->fields['email']); ?>" tabindex="108" />
            </p>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><? echo _DIRECTORY_ADDRESS; ?>:</label>
                <textarea class="text" name="directory_contact_address" tabindex="115"><? echo htmlentities($directory_contact->fields['address']); ?></textarea>
            </p>
            <p>
                <label><? echo _DIRECTORY_POSTALCODE; ?>:</label>
                <input type="text" class="text" name="directory_contact_postalcode" style="width:100px;" value="<? echo htmlentities($directory_contact->fields['postalcode']); ?>" tabindex="116" />
            </p>
            <p>
                <label><? echo _DIRECTORY_CITY; ?>:</label>
                <input type="text" class="text" name="directory_contact_city" value="<? echo htmlentities($directory_contact->fields['city']); ?>" tabindex="117" />
            </p>
            <p>
                <label><? echo _DIRECTORY_COUNTRY; ?>:</label>
                <input type="text" class="text" name="directory_contact_country" value="<? echo htmlentities($directory_contact->fields['country']); ?>" tabindex="118" />
            </p>
            <p>
                <label><? echo _DIRECTORY_COMMENTARY; ?>:</label>
                <textarea class="text" name="directory_contact_comments" tabindex="109"><? echo htmlentities($directory_contact->fields['comments']); ?></textarea>
            </p>

            <? $strPhotoId = md5(uniqid(rand(), true)); ?>
            <p>
                <label><? echo _DIRECTORY_PHOTO; ?>:</label>
                <span>
                    <a href="javascript:void(0);" onclick="javascript:directory_choose_photo(event, '<? echo $directory_contact->fields['id']; ?>', '<? echo $strPhotoId; ?>');">Choisir une photo</a>
                    <br /><span id="directory_contact_photo<? echo $strPhotoId; ?>">
                    <?
                    if (file_exists($directory_contact->getphotopath()))
                    {
                        ?><img src="<? echo ploopi_urlencode("admin-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$directory_contact->fields['id']}"); ?>" /><?
                    }
                    ?>
                    </span>
                </span>
            </p>


        </div>
    </div>
    <div style="clear:both;padding:2px 4px;text-align:right;">
        <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php"); ?>';" tabindex="120" />
        <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="119" />
    </div>
</div>
</form>
