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

<form action="<? echo $scriptenv; ?>" method="post">
<input type="hidden" name="op" value="directory_save">
<input type="hidden" name="contact_id" value="<? echo $directory_contact->fields['id']; ?>">
<div style="border-bottom:2px solid #c0c0c0;overflow:auto;">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><? echo _DIRECTORY_NAME; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_lastname" value="<? echo htmlentities($directory_contact->fields['lastname']); ?>" tabindex="101" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_FIRSTNAME; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_firstname" value="<? echo htmlentities($directory_contact->fields['firstname']); ?>" tabindex="102" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_SERVICE; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_service" value="<? echo htmlentities($directory_contact->fields['service']); ?>" tabindex="103" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_FUNCTION; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_function" value="<? echo htmlentities($directory_contact->fields['function']); ?>" tabindex="104" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_PHONE; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_phone" value="<? echo htmlentities($directory_contact->fields['phone']); ?>" tabindex="105" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_MOBILE; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_mobile" value="<? echo htmlentities($directory_contact->fields['mobile']); ?>" tabindex="106" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_FAX; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_fax" value="<? echo htmlentities($directory_contact->fields['fax']); ?>" tabindex="107" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_EMAIL; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_email" value="<? echo htmlentities($directory_contact->fields['email']); ?>" tabindex="108" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_COMMENTARY; ?>:</label>
                <span><textarea class="text" name="directory_contact_comments" tabindex="109"><? echo htmlentities($directory_contact->fields['comments']); ?></textarea></span>
            </p>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><? echo _DIRECTORY_ADDRESS; ?>:</label>
                <span><textarea class="text" name="directory_contact_address" tabindex="115"><? echo htmlentities($directory_contact->fields['address']); ?></textarea></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_POSTALCODE; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_postalcode" value="<? echo htmlentities($directory_contact->fields['postalcode']); ?>" tabindex="116" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_CITY; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_city" value="<? echo htmlentities($directory_contact->fields['city']); ?>" tabindex="117" /></span>
            </p>
            <p>
                <label><? echo _DIRECTORY_COUNTRY; ?>:</label>
                <span><input type="text" class="text" name="directory_contact_country" value="<? echo htmlentities($directory_contact->fields['country']); ?>" tabindex="118" /></span>
            </p>
        </div>
    </div>
    <div style="clear:both;padding:2px 4px;text-align:right;">
        <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?directoryTabItem=tabMycontacts"); ?>';" tabindex="120" />
        <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="119" />
    </div>
</div>
</form>
