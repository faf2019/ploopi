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
<TABLE CELLPADDING="2" CELLSPACING="1" ALIGN="CENTER">
<FORM NAME="form_modify_group" ACTION="<? echo $scriptenv ?>" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="op" VALUE="save_group">
<INPUT TYPE="HIDDEN" NAME="orgid" VALUE="<? echo $org->fields['id']; ?>">
<TR>
    <TD ALIGN=RIGHT><? echo _SYSTEM_LABEL_LEVEL; ?>:&nbsp;</TD>
    <TD ALIGN=LEFT>
    <SELECT class="select" NAME="workspacegroup_adminlevel">
    <?php

    foreach ($ploopi_system_levels as $id => $label)
    {

        if ($id <= $_SESSION['ploopi']['adminlevel'])
        {

            $sel = ($workspace_group->fields['adminlevel'] == $id) ? 'selected' : '';
            echo "<option $sel value=\"$id\">$label</option>";
        }
    }
    ?>
    </SELECT>
    </TD>
</TR>
<TR>
    <TD ALIGN=RIGHT COLSPAN=2>
        <INPUT TYPE="Submit" class="flatbutton" VALUE="<? echo _PLOOPI_SAVE; ?>">
    </TD>
</TR>
</FORM>
</TABLE>
