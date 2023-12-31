<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Modification du rattachement d'un groupe d'utilisateur à un espace (permet de modifier le niveau)
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @todo supprimer les <table>
 */

/**
 * Affichage du formulaire de modification du niveau de rattachement
 */
?>

<FORM NAME="form_modify_group" ACTION="<?php echo ploopi\crypt::urlencode("admin.php?op=save_group&orgid={$org->fields['id']}"); ?>" METHOD="POST">
<TABLE CELLPADDING="2" CELLSPACING="1" ALIGN="CENTER">
<TR>
    <TD ALIGN=RIGHT><?php echo _SYSTEM_LABEL_LEVEL; ?>:&nbsp;</TD>
    <TD ALIGN=LEFT>
    <SELECT class="select" NAME="workspacegroup_adminlevel">
    <?php

    foreach ($ploopi_system_levels as $id => $label)
    {

        if ($id <= $_SESSION['ploopi']['adminlevel'])
        {

            $sel = ($workspace_group->fields['adminlevel'] == $id) ? 'selected' : '';
            echo "<option $sel value=\"$id\">".ploopi\str::htmlentities($label)."</option>";
        }
    }
    ?>
    </SELECT>
    </TD>
</TR>
<TR>
    <TD ALIGN=RIGHT COLSPAN=2>
        <INPUT TYPE="Submit" class="flatbutton" VALUE="<?php echo _PLOOPI_SAVE; ?>">
    </TD>
</TR>
</TABLE>
</FORM>
