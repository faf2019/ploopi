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
 * Interface de création d'un rôle
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Ouverture du bloc
 */
echo ploopi\skin::get()->open_simplebloc();

/**
 * On récupère la liste des modules de l'espace de travail courant
 */
$modules = $workspace->getmodules();
?>

<div style="padding:4px;">
    <div style="margin-bottom:4px;">Module concerné :</div>
    <?php
    switch ($op)
    {
        case 'add_role':
        case 'modify_role':
            $role = new ploopi\role();

            if ($op == 'modify_role')
            {
                if (empty($_GET['roleid']) || !is_numeric($_GET['roleid'])) ploopi\output::redirect('admin.php');

                // ouverture du role
                $role->open($_GET['roleid']);
                $actions_checked = $role->getactions();
            }
            else
            {
                if (empty($_POST['role_id_module']) || !is_numeric($_POST['role_id_module']) || !isset($modules[$_POST['role_id_module']])) ploopi\output::redirect('admin.php');

                // nouveau role
                $role->init_description();
                $role->fields['id_module'] = $_POST['role_id_module'];
                $actions_checked = array();
            }

            $module = &$modules[$role->fields['id_module']];
            ?>
            <div style="font-weight:bold;margin-bottom:4px;"><?php echo ploopi\str::htmlentities("{$module['instancename']} ({$module['label']})"); ?></div>

            <form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" onsubmit="return role_validate(this);">
            <input type="hidden" name="op" value="save_role">
            <input type="hidden" name="roleid" value="<?php echo $role->fields['id']; ?>">
            <input type="hidden" name="role_id_module" value="<?php echo $role->fields['id_module']; ?>">

            <label><?php echo _SYSTEM_LABEL_LABEL; ?>:</label>
            <input type="text" class="text" name="role_label" style="width:300px;margin-bottom:4px;display:block;" value="<?php echo ploopi\str::htmlentities($role->fields['label']); ?>">

            <label><?php echo _SYSTEM_LABEL_DESCRIPTION; ?>:</label>
            <textarea class="text" name="role_description" style="width:300px;height:50px;margin-bottom:4px;display:block;"><?php echo ploopi\str::htmlentities($role->fields['description']); ?></textarea>

            <p class="ploopi_va">
                <input type="checkbox" name="role_shared" id="role_shared" value="1" <?php if ($role->fields['shared']) echo 'checked'; ?>>
                <label style="cursor:pointer;" for="role_shared"><?php echo _SYSTEM_LABEL_SHARED; ?></label>
            </p>
            <div style="margin:4px 0;font-weight:bold;">Choix des Actions : <a href="javascript:void(0);" onclick="javascript:jQuery('input.role_action').each(function(key, item) { item.checked = true; });">cocher tout</a> / <a href="javascript:void(0);" onclick="javascript:jQuery('input.role_action').each(function(key, item) { item.checked = false; });">décocher tout</a></div>

            <?php
            $module_type = new ploopi\module_type();
            $module_type->open($module['id_module_type']);
            $actions = $module_type->getactions();

            //ploopi\output::print_r($actions);
            foreach ($actions as $id => $action)
            {
                ?>
                <p class="ploopi_va">
                    <input type="checkbox" class="role_action" id="role_action_<?php echo $action['id_action']; ?>" name="id_action[]" <?php echo (isset($actions_checked[$id])) ? 'checked' : ''; ?> value="<?php echo ploopi\str::htmlentities($action['id_action']); ?>">
                    <label style="cursor:pointer;" for="role_action_<?php echo $action['id_action']; ?>"><?php echo ploopi\str::htmlentities("{$action['id_action']} - {$action['label']}"); ?></label>
                </p>
                <?php
            }
            ?>
            <div style="padding:4px 2px;">
                <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleManagement"); ?>';">
                <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>">
            </div>
            </form>
            <?php
        break;

        default:
            ?>
            <form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="add_role">
            <div style="margin-bottom:4px;">
                <select class="select" name="role_id_module">
                <?php
                foreach($modules as $module)
                {
                    ?>
                    <option value="<?php echo $module['instanceid']; ?>"><?php echo ploopi\str::htmlentities("{$module['instancename']} ({$module['label']})"); ?></option>
                    <?php
                }
                ?>
                </select>
            </div>
            <div style="padding:4px 2px;">
                <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleManagement"); ?>';">
                <input type="submit" class="button" value="Suivant">
            </div>
            </form>
            <?php
        break;
    }
    ?>
</div>
<?php
echo ploopi\skin::get()->close_simplebloc();
?>
