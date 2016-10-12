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
 * Ajout d'un groupe d'utilisateurs
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */
echo ploopi\skin::get()->open_simplebloc();

if (empty($group))
{
    $group = new ploopi\group();
    $group->init_description();
    $parentlabel = $workspace->fields['label'];
}
else
{
    $parentlabel = $group->fields['label'];
}
?>

<form action="<?php echo ploopi\crypt::urlencode("admin.php?op=save_group&group_id_group={$group->fields['id']}"); ?>" method="POST" onsubmit="javascript:return system_group_validate(this);">

<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($parentlabel); ?> &raquo; <?php echo _SYSTEM_LABEL_GROUP_ADD; ?>
</div>
<div class="ploopi_form" style="clear:both;padding:2px">
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
        <input type="text" class="text" name="group_label"  value="<?php echo ploopi\str::htmlentities("fils de {$parentlabel}"); ?>">
    </p>
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_SHARED; ?>:</label>
        <input style="width:16px;" type="checkbox" name="group_shared" value="1">(disponible pour les sous-espaces)
    </p>
</div>

<div style="text-align:right;padding:4px;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>

<?php echo ploopi\skin::get()->close_simplebloc(); ?>
