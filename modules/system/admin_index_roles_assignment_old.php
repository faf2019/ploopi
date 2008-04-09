<?php
/*
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

$parents = str_replace(';',',',$workspace->fields['parents']);

// on recherche les rôles des modules de l'espace sélectionné (ou hérités des espaces parents)
$sql =  "
        SELECT      r.id,
                    r.label,
                    r.description,
                    r.shared,
                    r.id_workspace,
                    m.label as module_label,
                    m.id as module_id,
                    mt.label as module_type,
                    w.label as origine

        FROM        ploopi_role r

        INNER JOIN  ploopi_module m ON m.id = r.id_module

        INNER JOIN  ploopi_module_type mt ON mt.id = m.id_module_type

        INNER JOIN  ploopi_workspace w ON w.id = r.id_workspace

        WHERE       (r.id_workspace = {$workspaceid}
        OR          (r.id_workspace IN ({$parents}) AND r.shared = 1))

        ORDER BY    module_type, m.label
        ";

$db->query($sql);

?>
<div style="overflow:hidden;">
<?
while($row = $db->fetchrow())
{
    ?>
    <a class="system_roleusers" href="javascript:void(0);" onclick="javascript:system_roleusers(<? echo $row['id']; ?>);">
        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_role.png" />
        <span><? echo "{$row['label']} de {$row['module_label']} ({$row['module_type']})"; ?></span>
    </a>

    <div class="system_roleusers_detail" id="system_roleusers_detail<? echo $row['id']; ?>" style="display:none;">
        <div class="system_roleusers_search_form">
            <p class="ploopi_va">
                <span>Recherche groupes/utilisateurs:&nbsp;</span>
                <input type="text" id="system_roleusers_filter<? echo $row['id']; ?>" class="text">
                <img onmouseover="javascript:this.style.cursor='pointer';" onclick="javascript:system_roleusers_search(<? echo $row['id']; ?>);" style="border:0px" src="<? echo "{$_SESSION['ploopi']['template_path']}/img/workflow/search.png"; ?>">
            </p>
        </div>
        <div id="system_roleusers_search_result<? echo $row['id']; ?>"></div>

        <div class="system_roleusers_list" id="system_roleusers_list<? echo $row['id']; ?>">
        </div>
    </div>
    <?
}
?>
</div>
