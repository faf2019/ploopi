<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Utilisateurs et groupes à qui un rôle est attribué dans l'espace courant
 * ($_SESSION['system']['workspaceid']), gabarits de génération 4.
 *
 * Inclus par admin_workspaces_roles.php et par l'opération system_roleusers (op.php,
 * via admin_index_roles_assignment_list.php), après chaque attribution ou retrait.
 * Variable fournie : $roleid (rôle autorisé dans l'espace, cf. system_isroleallowed).
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$db = ploopi\db::get();
$intRoleWorkspace = (int) $_SESSION['system']['workspaceid'];
$intRole = (int) $roleid;

$arrRoleGroups = $db->getarray($db->query("
    SELECT      g.id, g.label
    FROM        ploopi_workspace_group_role wgr
    INNER JOIN  ploopi_group g ON g.id = wgr.id_group
    WHERE       wgr.id_role = {$intRole}
    AND         wgr.id_workspace = {$intRoleWorkspace}
    ORDER BY    g.label
"));

$arrRoleUsers = $db->getarray($db->query("
    SELECT      u.id, u.login, u.lastname, u.firstname, u.service
    FROM        ploopi_workspace_user_role wur
    INNER JOIN  ploopi_user u ON u.id = wur.id_user
    WHERE       wur.id_role = {$intRole}
    AND         wur.id_workspace = {$intRoleWorkspace}
    ORDER BY    u.lastname, u.firstname
"));

if (empty($arrRoleGroups) && empty($arrRoleUsers))
{
    echo ui::emptystate('user-check', _SYSTEM_ROLEF_ASSIGNED_NONE);
    return;
}
?>
<ul class="p4-assign-list p4-ui">
    <?php
    foreach($arrRoleGroups as $arrGroup)
    {
        $strUrl = ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_roleusers_delete_group&system_roleusers_roleid={$intRole}&system_roleusers_groupid={$arrGroup['id']}");
        ?>
        <li>
            <span class="p4-assign-icon"><?php echo ui::icon('users-group'); ?></span>
            <span class="p4-grow"><b><?php echo ui::e($arrGroup['label']); ?></b><small><?php echo ui::e(_SYSTEM_WS_TYPE_GROUP); ?></small></span>
            <?php echo ui::button(array('label' => _SYSTEM_ROLEF_UNASSIGN, 'icon' => 'x', 'class' => 'danger-ghost icon sm', 'confirm' => _SYSTEM_ROLEF_UNASSIGN_GROUP_CONFIRM, 'attrs' => array('data-p4-load' => $strUrl, 'data-p4-target' => 'system_roleusers_list', 'data-p4-toast' => _SYSTEM_ROLEF_UNASSIGNED))); ?>
        </li>
        <?php
    }

    foreach($arrRoleUsers as $arrUser)
    {
        $strUrl = ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_roleusers_delete_user&system_roleusers_roleid={$intRole}&system_roleusers_userid={$arrUser['id']}");
        ?>
        <li>
            <span class="p4-assign-icon"><?php echo ui::icon('user'); ?></span>
            <span class="p4-grow"><b><?php echo ui::e(trim("{$arrUser['firstname']} {$arrUser['lastname']}")); ?></b><small><?php echo ui::e($arrUser['login'].($arrUser['service'] != '' ? ' · '.$arrUser['service'] : '')); ?></small></span>
            <?php echo ui::button(array('label' => _SYSTEM_ROLEF_UNASSIGN, 'icon' => 'x', 'class' => 'danger-ghost icon sm', 'confirm' => _SYSTEM_ROLEF_UNASSIGN_USER_CONFIRM, 'attrs' => array('data-p4-load' => $strUrl, 'data-p4-target' => 'system_roleusers_list', 'data-p4-toast' => _SYSTEM_ROLEF_UNASSIGNED))); ?>
        </li>
        <?php
    }
    ?>
</ul>
