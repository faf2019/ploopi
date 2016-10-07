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
 * Fonctions javascript dynamique du module Système
 *
 * @package system
 * @subpackage javascript
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi\module::init('system', false, false, false);

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

function role_validate(form)
{
    if (ploopi_validatefield("<?php echo _SYSTEM_LABEL_LABEL; ?>",form.role_label,"string"))
        return true;

    return false;
}
