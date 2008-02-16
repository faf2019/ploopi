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
<? echo $skin->open_simplebloc(_SYSTEM_LABEL_MYDATAS,'100%'); ?>

<TABLE CELLPADDING="2" CELLSPACING="1">
<?
$select = "SELECT * FROM ploopi_mb_action";
$db->query($select);
while ($fields = $db->fetchrow()) $actions[$fields['id_module_type']][$fields['id_action']] = $fields;

foreach ($_SESSION['ploopi']['workspaces'] as $group)
{
    if (!empty($group['adminlevel']) && $group['id'] != _PLOOPI_SYSTEMGROUP)
    {
        ?>
        <TR bgcolor="<? echo $skin->values['bgline2']; ?>">
            <TD COLSPAN="2"><b>Espace « <? echo $group['label']; ?> »</b></TD>
        </TR>
        <TR bgcolor="<? echo $skin->values['bgline1']; ?>">
            <TD>Niveau Utilisateur :</TD>
            <TD><? echo $ploopi_system_levels[$group['adminlevel']]; ?></TD>
        </TR>
            
        <?
        if (isset($group['modules']))
        foreach ($group['modules'] as $moduleid)
        {
            ?>
            <TR bgcolor="<? echo $skin->values['bgline1']; ?>">
                <TD VALIGN="top">Module « <? echo $_SESSION['ploopi']['modules'][$moduleid]['label']; ?> »</TD>
                <TD VALIGN="top">
                    <TABLE CELLPADDING="0" CELLSPACING="1">
                    
                        <?
                        $red = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_red.png\">";
                        $green = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_green.png\">";
                        
                        if (!empty($actions[$_SESSION['ploopi']['modules'][$moduleid]['id_module_type']]))
                            foreach($actions[$_SESSION['ploopi']['modules'][$moduleid]['id_module_type']] as $id => $action)
                            {
                                $puce = ploopi_isactionallowed($id, $group['id'], $moduleid) ? $green : $red;
                                echo    "<tr>
                                            <td>{$puce}</td>
                                            <td>{$action['label']}</td>
                                        </tr>";
                            }
                        ?>
                    </TABLE>
                </TD>
            </TR>
            <?
        }
    }
}
?>
</TABLE>
<? //ploopi_print_r($_SESSION); ?>
<? echo $skin->close_simplebloc(); ?>
