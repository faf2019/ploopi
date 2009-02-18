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

<form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_contact_save&directory_contact_id={$directory_contact->fields['id']}".(!empty($intHeadingId) ? "&directory_heading_id={$intHeadingId}" : '')); ?>" method="post">
<div style="border-bottom:2px solid #c0c0c0;overflow:auto;">
    <div style="float:left;width:50%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Informations personnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _DIRECTORY_NAME; ?>:</label>
                        <input type="text" class="text" name="directory_contact_lastname" value="<?php echo htmlentities($directory_contact->fields['lastname']); ?>" tabindex="101" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_FIRSTNAME; ?>:</label>
                        <input type="text" class="text" name="directory_contact_firstname" value="<?php echo htmlentities($directory_contact->fields['firstname']); ?>" tabindex="102" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_CIVILITY; ?>:</label>
                        <select class="select" name="directory_contact_civility" style="width:100px;" tabindex="103">
                            <option value=""></option>
                            <?php
                            foreach ($ploopi_civility as $value)
                            {
                                ?>
                                <option value="<?php echo htmlentities($value); ?>" <?php if ($directory_contact->fields['civility'] == $value) echo 'selected'; ?>><?php echo htmlentities($value); ?></option>
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
                        <label><?php echo _DIRECTORY_SERVICE; ?>:</label>
                        <input type="text" class="text" name="directory_contact_service" value="<?php echo htmlentities($directory_contact->fields['service']); ?>" tabindex="105" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_FUNCTION; ?>:</label>
                        <input type="text" class="text" name="directory_contact_function" value="<?php echo htmlentities($directory_contact->fields['function']); ?>" tabindex="106" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_RANK; ?>:</label>
                        <input type="text" class="text" name="directory_contact_rank" value="<?php echo htmlentities($directory_contact->fields['rank']); ?>" tabindex="107" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_NUMBER; ?>:</label>
                        <input type="text" class="text" name="directory_contact_number"  value="<?php echo htmlentities($directory_contact->fields['number']); ?>" tabindex="108" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_PHONE; ?>:</label>
                        <input type="text" class="text" name="directory_contact_phone" value="<?php echo htmlentities($directory_contact->fields['phone']); ?>" tabindex="109" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_MOBILE; ?>:</label>
                        <input type="text" class="text" name="directory_contact_mobile" value="<?php echo htmlentities($directory_contact->fields['mobile']); ?>" tabindex="110" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_FAX; ?>:</label>
                        <input type="text" class="text" name="directory_contact_fax" value="<?php echo htmlentities($directory_contact->fields['fax']); ?>" tabindex="111" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_EMAIL; ?>:</label>
                        <input type="text" class="text" name="directory_contact_email" value="<?php echo htmlentities($directory_contact->fields['email']); ?>" tabindex="112" />
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Lieu de travail</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _DIRECTORY_BUILDING; ?>:</label>
                        <input type="text" class="text" name="directory_contact_building"  value="<?php echo htmlentities($directory_contact->fields['building']); ?>" tabindex="120" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_FLOOR; ?>:</label>
                        <input type="text" class="text" name="directory_contact_floor"  value="<?php echo htmlentities($directory_contact->fields['floor']); ?>" tabindex="121" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_OFFICE; ?>:</label>
                        <input type="text" class="text" name="directory_contact_office"  value="<?php echo htmlentities($directory_contact->fields['office']); ?>" tabindex="122" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_ADDRESS; ?>:</label>
                        <textarea class="text" name="directory_contact_address" tabindex="123"><?php echo htmlentities($directory_contact->fields['address']); ?></textarea>
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_POSTALCODE; ?>:</label>
                        <input type="text" class="text" name="directory_contact_postalcode" style="width:100px;" value="<?php echo htmlentities($directory_contact->fields['postalcode']); ?>" tabindex="124" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_CITY; ?>:</label>
                        <input type="text" class="text" name="directory_contact_city" value="<?php echo htmlentities($directory_contact->fields['city']); ?>" tabindex="125" />
                    </p>
                    <p>
                        <label><?php echo _DIRECTORY_COUNTRY; ?>:</label>
                        <input type="text" class="text" name="directory_contact_country" value="<?php echo htmlentities($directory_contact->fields['country']); ?>" tabindex="126" />
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations complémentaires</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _DIRECTORY_COMMENTARY; ?>:</label>
                        <textarea class="text" name="directory_contact_comments" tabindex="130"><?php echo htmlentities($directory_contact->fields['comments']); ?></textarea>
                    </p>
                    <?php $strPhotoId = md5(uniqid(rand(), true)); ?>
                    <p>
                        <label><?php echo _DIRECTORY_PHOTO; ?>:</label>
                        <span>
                            <a href="javascript:void(0);" onclick="javascript:directory_choose_photo(event, '<?php echo $directory_contact->fields['id']; ?>', '<?php echo $strPhotoId; ?>');">Choisir une photo</a>
                            <br /><span id="directory_contact_photo<?php echo $strPhotoId; ?>">
                            <?php
                            if (file_exists($directory_contact->getphotopath()))
                            {
                                ?><img src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$directory_contact->fields['id']}"); ?>" /><?php
                            }
                            ?>
                            </span>
                        </span>
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
    <div style="clear:both;padding:2px 4px;text-align:right;">
        <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php"); ?>';" tabindex="141" />
        <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="140" />
    </div>
</div>
</form>
