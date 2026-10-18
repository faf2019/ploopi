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
 * Onglet « Rôles » des gabarits de génération 4 : rôles des modules de l'espace,
 * création et modification d'un rôle, attribution aux utilisateurs et aux groupes.
 *
 * Remplace les vues de admin_index_roles.php (qui inclut ce fichier après ses
 * opérations delete_role et save_role). Les attributions passent par les opérations
 * system_roleusers_* de op.php, dont la liste est rendue par
 * admin_workspaces_roles_list.php.
 *
 * Variables fournies : $workspace, $workspaceid, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$objSkin = ploopi\skin::get();
$db = ploopi\db::get();

if (!in_array($_SESSION['system']['roleTabItem'], array('tabRoleManagement', 'tabRoleAdd', 'tabRoleUsers'))) $_SESSION['system']['roleTabItem'] = 'tabRoleManagement';
$strRoleTab = $_SESSION['system']['roleTabItem'];

$arrModules = $workspace->getmodules();

// Rôles des modules de l'espace : les siens, et ceux partagés par les espaces parents
$strParents = implode(',', array_map('intval', explode(';', $workspace->fields['parents'])));

$rs = $db->query("
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
    INNER JOIN  ploopi_module_workspace mw ON mw.id_workspace = ".((int) $workspaceid)." AND mw.id_module = m.id

    WHERE       (r.id_workspace = ".((int) $workspaceid)."
    OR          (r.id_workspace IN ({$strParents}) AND r.shared = 1))

    GROUP BY    r.id
    ORDER BY    m.label, r.label
");

$arrRoles = array();
while ($row = $db->fetchrow($rs)) $arrRoles[$row['id']] = $row;

// Nombre d'attributions de chaque rôle dans l'espace
$arrAssigned = array();
if (!empty($arrRoles))
{
    $strIds = implode(',', array_map('intval', array_keys($arrRoles)));
    foreach(array('ploopi_workspace_user_role', 'ploopi_workspace_group_role') as $strTable)
    {
        $rs = $db->query("SELECT id_role, COUNT(*) AS nb FROM {$strTable} WHERE id_workspace = ".((int) $workspaceid)." AND id_role IN ({$strIds}) GROUP BY id_role");
        while ($row = $db->fetchrow($rs)) $arrAssigned[$row['id_role']] = (isset($arrAssigned[$row['id_role']]) ? $arrAssigned[$row['id_role']] : 0) + $row['nb'];
    }
}

/**
 * Sous-écran à afficher
 */
$strScreen = 'list';
if ($strRoleTab == 'tabRoleAdd') $strScreen = $op == 'add_role' ? 'form' : 'choose';
elseif ($strRoleTab == 'tabRoleManagement' && $op == 'modify_role') $strScreen = 'form';
elseif ($strRoleTab == 'tabRoleUsers') $strScreen = 'assign';

$arrSeg = array(
    'tabRoleManagement' => array(_SYSTEM_ROLEF_TAB_ROLES, ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleManagement'), 'shield'),
    'tabRoleUsers' => array(_SYSTEM_ROLEF_TAB_ASSIGN, ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleUsers'), 'user-check')
);
?>
<div class="p4-users-head p4-ui">
    <?php echo ui::seg($arrSeg, $strRoleTab == 'tabRoleAdd' ? 'tabRoleManagement' : $strRoleTab, _SYSTEM_ROLEF_TAB_LABEL); ?>
    <span class="p4-grow"></span>
    <?php if (!empty($arrModules) && $strScreen == 'list') echo ui::button(array('label' => _SYSTEM_ROLEF_NEW, 'icon' => 'plus', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleAdd'))); ?>
</div>
<?php
switch($strScreen)
{
    /**
     * Choix du module d'un nouveau rôle
     */
    case 'choose':
        ?>
        <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleAdd'); ?>" method="post">
            <input type="hidden" name="op" value="add_role" />
            <?php
            echo ui::section_open(_SYSTEM_ROLEF_NEW, _SYSTEM_ROLEF_CHOOSE_DESC, 'shield');

            $arrChoices = array();
            foreach($arrModules as $arrModule) $arrChoices[$arrModule['instanceid']] = "{$arrModule['instancename']} ({$arrModule['label']})";
            asort($arrChoices);

            echo ui::field(_SYSTEM_ROLEF_MODULE, ui::select('role_id_module', $arrChoices), array('for' => 'role_id_module', 'width' => 'c12'));
            echo ui::section_close();

            echo ui::formactions(array(
                array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleManagement'), 'class' => 'ghost'),
                array('label' => _SYSTEM_ROLEF_NEXT, 'icon' => 'arrow-right', 'type' => 'submit', 'class' => 'primary')
            ));
            ?>
        </form>
        <?php
    break;

    /**
     * Formulaire d'un rôle (création ou modification)
     */
    case 'form':
        $objRole = new ploopi\role();

        if ($op == 'modify_role')
        {
            // Seulement un rôle de cet espace
            if (empty($_GET['roleid']) || !is_numeric($_GET['roleid']) || !$objRole->open($_GET['roleid']) || $objRole->fields['id_workspace'] != $workspaceid) ploopi\output::redirect('admin.php?roleTabItem=tabRoleManagement');
            $arrChecked = $objRole->getactions();
        }
        else
        {
            if (empty($_POST['role_id_module']) || !is_numeric($_POST['role_id_module']) || !isset($arrModules[$_POST['role_id_module']])) ploopi\output::redirect('admin.php?roleTabItem=tabRoleAdd');

            $objRole->init_description();
            $objRole->fields['id_module'] = $_POST['role_id_module'];
            $arrChecked = array();
        }

        if (!isset($arrModules[$objRole->fields['id_module']])) ploopi\output::redirect('admin.php?roleTabItem=tabRoleManagement');
        $arrModule = $arrModules[$objRole->fields['id_module']];

        $objModuleType = new ploopi\module_type();
        $objModuleType->open($arrModule['id_module_type']);
        $arrActions = $objModuleType->getactions();
        ?>
        <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" data-p4-validate>
            <input type="hidden" name="op" value="save_role" />
            <input type="hidden" name="roleid" value="<?php echo (int) $objRole->fields['id']; ?>" />
            <input type="hidden" name="role_id_module" value="<?php echo (int) $objRole->fields['id_module']; ?>" />
            <?php
            echo ui::section_open($objRole->new ? _SYSTEM_ROLEF_NEW : _SYSTEM_ROLEF_EDIT, str_replace('<MODULE>', "{$arrModule['instancename']} ({$arrModule['label']})", _SYSTEM_ROLEF_FORM_DESC), $objSkin->moduleicon($arrModule['label']));
            echo ui::field(_SYSTEM_LABEL_LABEL, ui::input('role_label', $objRole->fields['label'], array('check' => 'string', 'required' => true, 'maxlength' => 255, 'autofocus' => $objRole->new)), array('for' => 'role_label', 'width' => 'c12', 'required' => true));
            echo ui::field(_SYSTEM_LABEL_DESCRIPTION, ui::textarea('role_description', $objRole->fields['description'], array('rows' => 2, 'maxlength' => 255)), array('for' => 'role_description', 'width' => 'c12'));
            echo '<div class="p4-c12">'.ui::toggle('role_shared', !empty($objRole->fields['shared']), _SYSTEM_LABEL_SHARED, ui::e(_SYSTEM_ROLEF_SHARED_HINT)).'</div>';
            echo ui::section_close();

            echo ui::section_open(_SYSTEM_ROLEF_ACTIONS, _SYSTEM_ROLEF_ACTIONS_DESC, 'list-check');
            if (empty($arrActions)) echo '<div class="p4-c12">'.ui::emptystate('list-check', _SYSTEM_ROLEF_NOACTION).'</div>';
            else
            {
                ?>
                <div class="p4-c12 p4-row p4-wrap">
                    <button type="button" class="p4-btn p4-btn-ghost p4-btn-sm" data-p4-checkall="input.p4-role-action" data-p4-checked="1"><?php echo ui::icon('checks', 'p4-icon-sm').ui::e(_SYSTEM_ROLEF_CHECKALL); ?></button>
                    <button type="button" class="p4-btn p4-btn-ghost p4-btn-sm" data-p4-checkall="input.p4-role-action" data-p4-checked="0"><?php echo ui::icon('x', 'p4-icon-sm').ui::e(_SYSTEM_ROLEF_UNCHECKALL); ?></button>
                </div>
                <div class="p4-c12 p4-checklist">
                    <?php
                    foreach($arrActions as $intId => $arrAction)
                    {
                        ?>
                        <label class="p4-check">
                            <input type="checkbox" class="p4-role-action" name="id_action[]" value="<?php echo ui::e($arrAction['id_action']); ?>"<?php if (isset($arrChecked[$intId])) echo ' checked="checked"'; ?> />
                            <span><?php echo ui::e($arrAction['label']); ?><small><?php echo ui::e('#'.$arrAction['id_action']); ?></small></span>
                        </label>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            echo ui::section_close();

            echo ui::formactions(array(
                array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleManagement'), 'class' => 'ghost'),
                array('label' => _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
            ));
            ?>
        </form>
        <?php
    break;

    /**
     * Attributions : rôles à gauche, utilisateurs et groupes du rôle choisi à droite
     */
    case 'assign':
        $intRoleId = (!empty($_GET['roleid']) && is_numeric($_GET['roleid']) && isset($arrRoles[$_GET['roleid']])) ? (int) $_GET['roleid'] : 0;

        if (empty($arrRoles))
        {
            echo ui::emptystate('shield', _SYSTEM_ROLEF_NONE);
            break;
        }
        ?>
        <div class="p4-aside-layout p4-roles-assign p4-ui">
            <?php echo ui::card_open(_SYSTEM_ROLEF_TAB_ROLES, '', array(), array('tag' => 'nav', 'class' => 'p4-navcard', 'body' => false)); ?>
                <ul class="p4-navlist">
                    <?php
                    foreach($arrRoles as $arrRole)
                    {
                        ?>
                        <li><a href="<?php echo ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleUsers&op=assign_role&roleid={$arrRole['id']}"); ?>"<?php if ($arrRole['id'] == $intRoleId) echo ' aria-current="page"'; ?>><?php echo ui::icon($objSkin->moduleicon($arrRole['module_type'])); ?><span class="p4-grow"><?php echo ui::e($arrRole['label']); ?><small><?php echo ui::e($arrRole['module_label']); ?></small></span><?php if (!empty($arrAssigned[$arrRole['id']])) echo '<span class="p4-count p4-count-muted">'.(int) $arrAssigned[$arrRole['id']].'</span>'; ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            <?php echo ui::card_close('', array('tag' => 'nav', 'body' => false)); ?>

            <div class="p4-stack">
                <div class="p4-navlist-select">
                    <?php
                    $arrNav = array('' => _SYSTEM_ROLEF_CHOOSE);
                    foreach($arrRoles as $arrRole) $arrNav[ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleUsers&op=assign_role&roleid={$arrRole['id']}")] = "{$arrRole['label']} ({$arrRole['module_label']})";
                    echo ui::select('p4_role_nav', $arrNav, $intRoleId ? ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleUsers&op=assign_role&roleid={$intRoleId}") : '', array('data-p4-nav' => true, 'aria-label' => _SYSTEM_ROLEF_CHOOSE));
                    ?>
                </div>
                <?php
                if (!$intRoleId)
                {
                    echo ui::card_open();
                    echo ui::emptystate('user-check', _SYSTEM_ROLEF_ASSIGN_HINT);
                    echo ui::card_close();
                }
                else
                {
                    $arrRole = $arrRoles[$intRoleId];
                    $strSearchUrl = ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_roleusers_search&system_roleusers_roleid={$intRoleId}").'&system_roleusers_filter=';

                    echo ui::card_open($arrRole['label'], $objSkin->moduleicon($arrRole['module_type']), array(), array('subtitle' => $arrRole['module_label'].($arrRole['description'] != '' ? ' · '.$arrRole['description'] : '')));
                    ?>
                    <div class="p4-row p4-roles-search">
                        <div class="p4-input-icon p4-grow">
                            <?php echo ui::icon('search', 'p4-icon-sm'); ?>
                            <input class="p4-input" type="search" id="p4-roleusers-filter" placeholder="<?php echo ui::e(_SYSTEM_ROLEF_SEARCH); ?>" aria-label="<?php echo ui::e(_SYSTEM_ROLEF_SEARCH); ?>" data-p4-search-load="<?php echo ui::e($strSearchUrl); ?>" data-p4-target="system_roleusers_search_result" />
                        </div>
                        <?php echo ui::button(array('label' => _SYSTEM_ROLEF_SEARCH_GO, 'icon' => 'search', 'attrs' => array('data-p4-search-go' => 'p4-roleusers-filter'))); ?>
                    </div>
                    <div id="system_roleusers_search_result" class="p4-roles-results" aria-live="polite"></div>

                    <h3 class="p4-subhead"><?php echo ui::e(_SYSTEM_ROLEF_ASSIGNED); ?></h3>
                    <div id="system_roleusers_list" aria-live="polite">
                        <?php
                        $roleid = $intRoleId;
                        include './modules/system/admin_workspaces_roles_list.php';
                        ?>
                    </div>
                    <?php
                    echo ui::card_close();
                }
                ?>
            </div>
        </div>
        <?php
    break;

    /**
     * Liste des rôles
     */
    default:
        if (empty($arrModules)) echo ui::emptystate('puzzle', _SYSTEM_ROLEF_NOMODULE);
        elseif (empty($arrRoles)) echo ui::emptystate('shield', _SYSTEM_ROLEF_NONE, array(array('label' => _SYSTEM_ROLEF_NEW, 'icon' => 'plus', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?roleTabItem=tabRoleAdd'))));
        else
        {
            ?>
            <div class="p4-ui" data-p4-table>
                <div class="p4-table-tools">
                    <div class="p4-input-icon p4-list-search">
                        <?php echo ui::icon('search', 'p4-icon-sm'); ?>
                        <input class="p4-input" type="search" data-p4-table-filter placeholder="<?php echo ui::e(_SYSTEM_ROLEF_FILTER); ?>" aria-label="<?php echo ui::e(_SYSTEM_ROLEF_FILTER); ?>" />
                    </div>
                    <span class="p4-text-xs p4-muted"><span data-p4-table-count><?php echo sizeof($arrRoles); ?></span> <?php echo ui::e(_SYSTEM_ROLEF_COUNT); ?></span>
                </div>
                <div class="p4-table-wrap">
                    <table class="p4-table">
                        <thead>
                            <tr>
                                <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_ROLEF_ROLE).ui::icon('arrows-sort'); ?></button></th>
                                <th><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_ROLEF_MODULE).ui::icon('arrows-sort'); ?></button></th>
                                <th class="p4-col-origin"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_ROLEF_ORIGIN).ui::icon('arrows-sort'); ?></button></th>
                                <th class="p4-center"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_ROLEF_ASSIGNMENTS).ui::icon('arrows-sort'); ?></button></th>
                                <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($arrRoles as $arrRole)
                            {
                                $booOwn = $arrRole['id_workspace'] == $workspaceid;
                                $arrActs = array(array('label' => _SYSTEM_ROLEF_ASSIGN, 'icon' => 'user-check', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleUsers&op=assign_role&roleid={$arrRole['id']}")));
                                if ($booOwn)
                                {
                                    $arrActs[] = array('label' => _SYSTEM_LABEL_MODIFY, 'icon' => 'pencil', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleManagement&op=modify_role&roleid={$arrRole['id']}"));
                                    $arrActs[] = array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?roleTabItem=tabRoleManagement&op=delete_role&roleid={$arrRole['id']}"), 'confirm' => _SYSTEM_ROLEF_DELETE_CONFIRM);
                                }
                                $intNb = isset($arrAssigned[$arrRole['id']]) ? (int) $arrAssigned[$arrRole['id']] : 0;
                                ?>
                                <tr>
                                    <td data-p4-sort-value="<?php echo ui::e(mb_strtolower($arrRole['label'])); ?>">
                                        <div class="p4-cell-main">
                                            <?php echo ui::icon('shield'); ?>
                                            <span><b><?php echo ui::e($arrRole['label']); ?></b><?php if ($arrRole['description'] != '') echo '<small>'.ui::e($arrRole['description']).'</small>'; ?></span>
                                        </div>
                                    </td>
                                    <td data-label="<?php echo ui::e(_SYSTEM_ROLEF_MODULE); ?>"><span class="p4-cell-main"><?php echo ui::icon($objSkin->moduleicon($arrRole['module_type'])); ?><span><?php echo ui::e($arrRole['module_label']); ?><small><?php echo ui::e($arrRole['module_type']); ?></small></span></span></td>
                                    <td class="p4-col-origin" data-label="<?php echo ui::e(_SYSTEM_ROLEF_ORIGIN); ?>"><?php echo ui::e($arrRole['origine']); ?><?php if ($arrRole['shared']) echo ' '.ui::badge(_SYSTEM_LABEL_SHARED, 'info'); ?></td>
                                    <td class="p4-center" data-p4-sort-value="<?php echo $intNb; ?>" data-label="<?php echo ui::e(_SYSTEM_ROLEF_ASSIGNMENTS); ?>"><?php echo $intNb ? '<span class="p4-count p4-count-muted">'.$intNb.'</span>' : '<span class="p4-muted">—</span>'; ?></td>
                                    <td class="p4-col-acts"><div class="p4-acts"><?php echo ui::actions($arrActs); ?></div></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div data-p4-table-empty hidden><?php echo ui::emptystate('search', _SYSTEM_ROLEF_NOMATCH); ?></div>
            </div>
            <?php
        }
    break;
}

echo ui::script();
