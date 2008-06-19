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

/**
 * Interface de création d'un rôle 
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
echo $skin->open_simplebloc();

/**
 * On récupère la liste des modules de l'espace de travail courant
 */
$modules = $workspace->getmodules();
?>

<div style="padding:4px;">
    <div style="margin-bottom:4px;">Module concerné :</div>
    <?
    switch ($op)
    {
        case 'add_role':
        case 'modify_role':
            $role = new role();

            if ($op == 'modify_role')
            {
                if (empty($_GET['roleid']) || !is_numeric($_GET['roleid'])) ploopi_redirect('admin.php');

                // ouverture du role
                $role->open($_GET['roleid']);
                $actions_checked = $role->getactions();
            }
            else
            {
                if (empty($_POST['role_id_module']) || !is_numeric($_POST['role_id_module']) || !isset($modules[$_POST['role_id_module']])) ploopi_redirect('admin.php');

                // nouveau role
                $role->init_description();
                $role->fields['id_module'] = $_POST['role_id_module'];
                $actions_checked = array();
            }

            $module = &$modules[$role->fields['id_module']];
            ?>
            <div style="font-weight:bold;margin-bottom:4px;"><? echo "{$module['instancename']} ({$module['label']})"; ?></div>

            <form action="<? echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return role_validate(this);">
            <input type="hidden" name="op" value="save_role">
            <input type="hidden" name="roleid" value="<? echo $role->fields['id']; ?>">
            <input type="hidden" name="role_id_module" value="<? echo $role->fields['id_module']; ?>">

            <label><? echo _SYSTEM_LABEL_LABEL; ?>:</label>
            <input type="text" class="text" name="role_label" style="width:300px;margin-bottom:4px;display:block;" value="<? echo htmlentities($role->fields['label']); ?>">

            <label><? echo _SYSTEM_LABEL_DESCRIPTION; ?>:</label>
            <textarea class="text" name="role_description" style="width:300px;height:50px;margin-bottom:4px;display:block;"><? echo htmlentities($role->fields['description']); ?></textarea>

            <p class="ploopi_va">
                <input type="checkbox" name="role_shared" id="role_shared" value="1" <? if ($role->fields['shared']) echo 'checked'; ?>>
                <span style="cursor:pointer;" onclick="javascript:$('role_shared').checked = !$('role_shared').checked;"><? echo _SYSTEM_LABEL_SHARED; ?></span>
            </p>
            <div style="margin:4px 0;font-weight:bold;">Choix des Actions : <a href="javascript:void(0);" onclick="javascript:system_checkall('input.role_action', true);">cocher tout</a> / <a href="javascript:void(0);" onclick="javascript:system_checkall('input.role_action', false);">décocher tout</a></div>

            <?
            $module_type = new module_type();
            $module_type->open($module['id_module_type']);
            $actions = $module_type->getactions();

            //ploopi_print_r($actions);
            foreach ($actions as $id => $action)
            {
                ?>
                <p class="ploopi_va">
                    <input type="checkbox" class="role_action" id="role_action_<? echo $action['id_action']; ?>" name="id_action[]" <? echo (isset($actions_checked[$id])) ? 'checked' : ''; ?> value="<? echo $action['id_action']; ?>">
                    <span style="cursor:pointer;" onclick="javascript:$('role_action_<? echo $action['id_action']; ?>').checked = !$('role_action_<? echo $action['id_action']; ?>').checked;"><? echo "{$action['id_action']} - {$action['label']}"; ?></span>
                </p>
                <?
            }
            ?>
            <div style="padding:4px 2px;">
                <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?roleTabItem=tabRoleManagement"); ?>';">
                <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>">
            </div>
            </form>
            <?
        break;

        default:
            ?>
            <form action="<? echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="add_role">
            <div style="margin-bottom:4px;">
                <select class="select" name="role_id_module">
                <?
                foreach($modules as $module)
                {
                    ?>
                    <option value="<? echo $module['instanceid']; ?>"><? echo "{$module['instancename']} ({$module['label']})"; ?></option>
                    <?
                }
                ?>
                </select>
            </div>
            <div style="padding:4px 2px;">
                <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?roleTabItem=tabRoleManagement"); ?>';">
                <input type="submit" class="button" value="Suivant">
            </div>
            </form>
            <?
        break;
    }
    ?>
</div>
<?
echo $skin->close_simplebloc();
?>