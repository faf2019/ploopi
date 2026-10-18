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
 * Groupes rattachés à un espace, gabarits de génération 4 : liste (niveau, chemin,
 * actions), niveau d'un groupe dans l'espace.
 *
 * Remplace admin_index_users_grouplist.php et admin_index_group_form.php sous
 * ploopi4. Opérations de admin_index_users.php : modify_group, save_group,
 * detach_group (orgid).
 *
 * Variables fournies : $workspace, $workspaceid, $tabs, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

global $ploopi_system_levels;

$db = ploopi\db::get();

// Niveau d'un groupe dans l'espace (op=modify_group, admin_index_users.php)
if ($op == 'modify_group' && isset($org) && isset($workspace_group))
{
    $arrLevels = array();
    foreach($ploopi_system_levels as $intLevel => $strLevel) if ($intLevel <= $_SESSION['ploopi']['adminlevel']) $arrLevels[$intLevel] = $strLevel;
    ?>
    <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode("admin.php?usrTabItem=tabGroupList&op=save_group&orgid={$org->fields['id']}"); ?>" method="post">
        <?php
        echo ui::section_open($org->fields['label'], _SYSTEM_GRL_LEVEL_DESC, 'users-group');
        echo ui::field(_SYSTEM_USERS_CARD_LEVEL, ui::select('workspacegroup_adminlevel', $arrLevels, $workspace_group->fields['adminlevel']), array('for' => 'workspacegroup_adminlevel', 'hint' => ui::e(_SYSTEM_GRL_LEVEL_HINT)));
        echo ui::section_close();

        echo ui::formactions(array(
            array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabGroupList'), 'class' => 'ghost'),
            array('label' => _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
        ));
        ?>
    </form>
    <?php
    echo ui::script();
    return;
}

$rs = $db->query("
    SELECT      g.id,
                g.label,
                g.parents,
                wg.adminlevel,
                (SELECT COUNT(*) FROM ploopi_group_user gu WHERE gu.id_group = g.id) AS nbusers

    FROM        ploopi_group g

    INNER JOIN  ploopi_workspace_group wg
    ON          wg.id_group = g.id
    AND         wg.id_workspace = ".((int) $workspaceid)."

    ORDER BY    g.label
");

$arrGroups = $db->getarray($rs);

if (empty($arrGroups))
{
    echo ui::emptystate('users-group', _SYSTEM_GRL_NONE, isset($tabs['tabGroupAttach']) ? array(array('label' => _SYSTEM_USERS_ADD_GROUP, 'icon' => 'link', 'class' => 'primary', 'href' => ploopi\crypt::urlencode($tabs['tabGroupAttach']['url']))) : array());
    echo ui::script();
    return;
}
?>
<div class="p4-ui" data-p4-table="50">
    <div class="p4-table-tools">
        <div class="p4-input-icon p4-list-search">
            <?php echo ui::icon('search', 'p4-icon-sm'); ?>
            <input class="p4-input" type="search" data-p4-table-filter placeholder="<?php echo ui::e(_SYSTEM_GRL_FILTER); ?>" aria-label="<?php echo ui::e(_SYSTEM_GRL_FILTER); ?>" />
        </div>
        <span class="p4-text-xs p4-muted"><span data-p4-table-count><?php echo sizeof($arrGroups); ?></span> <?php echo ui::e(_SYSTEM_GRL_COUNT); ?></span>
        <span class="p4-grow"></span>
        <?php if (isset($tabs['tabGroupAttach'])) echo ui::button(array('label' => _SYSTEM_USERS_ADD_GROUP, 'icon' => 'link', 'class' => 'primary', 'href' => ploopi\crypt::urlencode($tabs['tabGroupAttach']['url']))); ?>
    </div>
    <div class="p4-table-wrap">
            <table class="p4-table">
                <thead>
                    <tr>
                        <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_WS_TYPE_GROUP).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-col-path"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_GRL_PATH).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-center"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_TAB_USERS).ui::icon('arrows-sort'); ?></button></th>
                        <th><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_USERS_CARD_LEVEL).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($arrGroups as $arrGroup)
                    {
                        $arrParents = system_getparents($arrGroup['parents'], 'group');
                        array_shift($arrParents);
                        $arrPath = array();
                        foreach($arrParents as $arrParent) $arrPath[] = $arrParent['label'];

                        $booCan = $_SESSION['ploopi']['adminlevel'] >= $arrGroup['adminlevel'];
                        $arrActs = array();
                        if ($booCan)
                        {
                            $arrActs[] = array('label' => _SYSTEM_GRL_LEVEL_EDIT, 'icon' => 'shield', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?usrTabItem=tabGroupList&op=modify_group&orgid={$arrGroup['id']}"));
                            $arrActs[] = array('label' => _SYSTEM_GRL_DETACH, 'icon' => 'unlink', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?usrTabItem=tabGroupList&op=detach_group&orgid={$arrGroup['id']}"), 'confirm' => _SYSTEM_GRL_DETACH_CONFIRM);
                        }
                        $strLevel = isset($ploopi_system_levels[$arrGroup['adminlevel']]) ? $ploopi_system_levels[$arrGroup['adminlevel']] : '';
                        ?>
                        <tr>
                            <td><a class="p4-cell-main" href="<?php echo ploopi\crypt::urlencode("admin.php?groupid={$arrGroup['id']}"); ?>"><?php echo ui::icon('users-group'); ?><b><?php echo ui::e($arrGroup['label']); ?></b></a></td>
                            <td class="p4-col-path p4-text-sm p4-muted" data-label="<?php echo ui::e(_SYSTEM_GRL_PATH); ?>"><?php echo ui::e(implode(' › ', $arrPath)); ?></td>
                            <td class="p4-center" data-p4-sort-value="<?php echo (int) $arrGroup['nbusers']; ?>" data-label="<?php echo ui::e(_SYSTEM_TAB_USERS); ?>"><span class="p4-count p4-count-muted"><?php echo (int) $arrGroup['nbusers']; ?></span></td>
                            <td data-p4-sort-value="<?php echo (int) $arrGroup['adminlevel']; ?>" data-label="<?php echo ui::e(_SYSTEM_USERS_CARD_LEVEL); ?>"><?php echo ui::badge($strLevel, $arrGroup['adminlevel'] > _PLOOPI_ID_LEVEL_USER ? 'brand' : ''); ?></td>
                            <td class="p4-col-acts"><div class="p4-acts"><?php echo ui::actions($arrActs); ?></div></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div data-p4-table-empty hidden><?php echo ui::emptystate('search', _SYSTEM_GRL_NOMATCH); ?></div>
        <div class="p4-table-foot"><span></span><div class="p4-pager" data-p4-table-pager data-p4-prev="<?php echo ui::e(_SYSTEM_USERS_PREV); ?>" data-p4-next="<?php echo ui::e(_SYSTEM_USERS_NEXT); ?>"></div></div>
</div>
<?php
echo ui::script();
