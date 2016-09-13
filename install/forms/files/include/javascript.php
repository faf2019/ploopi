<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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
 * Fonctions javascript dynamiques
 *
 * @package forms
 * @subpackage javascript
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
if (ovensia\ploopi\acl::ismoduleallowed('forms'))
{
    ovensia\ploopi\module::init('forms', false, false, false);
    ?>

    var verifcolor = false;

    function forms_validate(form)
    {
        if (ploopi_validatefield('<?php echo addslashes(ovensia\ploopi\str::html_entity_decode(_FORMS_LABEL)); ?>',form.forms_label,"string"))
        if (ploopi_validatefield('<?php echo addslashes(ovensia\ploopi\str::html_entity_decode(_FORMS_PUBDATESTART)); ?>',form.forms_pubdate_start,"emptydate"))
        if (ploopi_validatefield('<?php echo addslashes(ovensia\ploopi\str::html_entity_decode(_FORMS_PUBDATEEND)); ?>',form.forms_pubdate_end,"emptydate"))
            return(true);

        return(false);
    }

    function forms_field_validate(form)
    {
        form.field_values.value = '';

        t = form.field_type;
        if (t.value == 'select' || t.value == 'radio' || t.value == 'checkbox' || t.value == 'color')
        {
            for (i = 0; i < form.f_values.length; i++)
            {
                if (form.field_values.value != '') form.field_values.value += '||';
                form.field_values.value += form.f_values[i].value;
            }
        }
        else if (t.value == 'tablelink') form.field_values.value = form.f_formfield.value;

        if (ploopi_validatefield('<?php echo _FORMS_FIELD_NAME; ?>',form.field_name,"string"))
            return(true);

        return(false);
    }

    function forms_graphic_validate(form)
    {
        if (ploopi_validatefield('<?php echo addslashes(ovensia\ploopi\str::html_entity_decode(_FORMS_GRAPHIC_LABEL)); ?>',form.forms_graphic_label,"string"))
            return(true);

        return(false);
    }

    function forms_group_validate(form)
    {
        if (ploopi_validatefield('<?php echo addslashes(ovensia\ploopi\str::html_entity_decode(_FORMS_GROUP_LABEL)); ?>',form.forms_group_label,"string"))
            return(true);

        return(false);
    }

    <?php
}
?>
