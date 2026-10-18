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
 * Onglet « Modules » des gabarits de génération 4 : modules de l'espace (ordre, états,
 * propriétés d'une instance) et modules qu'on peut y ajouter.
 *
 * Remplace admin_index_modules.php sous ploopi4. Les opérations (switch_*, moveup,
 * movedown, unlinkinstance, save_module_props, delete, add, apply_heritage) restent
 * celles de admin_index.php.
 *
 * Variables fournies par admin_index.php : $workspace, $workspaceid, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

global $ploopi_viewmodes;

$objSkin = ploopi\skin::get();

$arrOwnModules = $workspace->getmodules();
$arrSharedModules = $workspace->getsharedmodules();
$arrInstalled = system_getinstalledmodules();

// Instance dont on modifie les propriétés : seulement une instance de cet espace
$objModule = null;
if ($op == 'modify' && !empty($_GET['moduleid']) && is_numeric($_GET['moduleid']) && isset($arrOwnModules[$_GET['moduleid']]) && $arrOwnModules[$_GET['moduleid']]['instanceworkspace'] == $workspaceid)
{
    $objModule = new ploopi\module();
    if (!$objModule->open($_GET['moduleid'])) $objModule = null;
}

if (!is_null($objModule))
{
    $arrFields = $objModule->fields;

    echo ui::card_open(str_replace('<MODULE>', $arrFields['label'], _SYSTEM_MODF_TITLE), $objSkin->moduleicon($arrOwnModules[$arrFields['id']]['label']), array(), array('id' => 'modify', 'class' => 'p4-module-props', 'subtitle' => $arrOwnModules[$arrFields['id']]['label']));
    ?>
    <form class="p4-form" action="<?php echo ploopi\crypt::urlencode("admin.php?op=save_module_props&moduleid={$arrFields['id']}"); ?>" method="post" data-p4-validate>
        <?php
        echo ui::section_open(_SYSTEM_WSF_SEC_IDENTITY, _SYSTEM_EXPLAIN_MODULENAME, 'id');
        echo ui::field(_SYSTEM_LABEL_MODULENAME, ui::input('module_label', $arrFields['label'], array('check' => 'string', 'required' => true, 'maxlength' => 255, 'autofocus' => true)), array('width' => 'c12', 'for' => 'module_label', 'required' => true));
        echo ui::section_close();

        echo ui::section_open(_SYSTEM_MODF_SEC_DISPLAY, _SYSTEM_MODF_SEC_DISPLAY_DESC, 'eye');
        echo '<div class="p4-c12 p4-toggles">';
        echo ui::toggle('module_active', $arrFields['active'], _SYSTEM_LABEL_ACTIVE, ui::e(_SYSTEM_EXPLAIN_ACTIVE));
        echo ui::toggle('module_visible', $arrFields['visible'], _SYSTEM_LABEL_VISIBLE, ui::e(_SYSTEM_EXPLAIN_VISIBLE));
        echo ui::toggle('module_autoconnect', $arrFields['autoconnect'], _SYSTEM_LABEL_AUTOCONNECT, ui::e(_SYSTEM_EXPLAIN_AUTOCONNECT));
        echo '</div>';
        echo ui::section_close();

        echo ui::section_open(_SYSTEM_MODF_SEC_SHARING, _SYSTEM_MODF_SEC_SHARING_DESC, 'binary-tree');
        echo '<div class="p4-c12 p4-toggles">';
        echo ui::toggle('module_shared', $arrFields['shared'], _SYSTEM_LABEL_SHARED, ui::e(_SYSTEM_EXPLAIN_SHARED));
        echo ui::toggle('module_herited', $arrFields['herited'], _SYSTEM_LABEL_HERITED, ui::e(_SYSTEM_EXPLAIN_HERITED).' <a href="'.ui::e(ploopi\crypt::urlencode("admin.php?op=apply_heritage&moduleid={$arrFields['id']}")).'" data-p4-confirm="'.ui::e(_SYSTEM_MODF_APPLYHERITAGE_CONFIRM).'">'.ui::e(_SYSTEM_APPLYHERITAGE).'</a>');
        echo ui::toggle('module_adminrestricted', $arrFields['adminrestricted'], _SYSTEM_LABEL_ADMINRESTRICTED, ui::e(_SYSTEM_EXPLAIN_ADMINRESTRICTED));
        echo '</div>';
        echo ui::section_close();

        echo ui::section_open(_SYSTEM_MODF_SEC_VIEW, _SYSTEM_MODF_SEC_VIEW_DESC, 'eye');
        echo ui::field(_SYSTEM_LABEL_VIEWMODE, ui::select('module_viewmode', $ploopi_viewmodes, $arrFields['viewmode']), array('for' => 'module_viewmode', 'hint' => _SYSTEM_EXPLAIN_VIEWMODE));
        echo '<div class="p4-c12">'.ui::toggle('module_transverseview', $arrFields['transverseview'], _SYSTEM_LABEL_TRANSVERSE, ui::e(_SYSTEM_MODF_TRANSVERSE_HINT)).'</div>';
        echo ui::section_close();

        echo ui::formactions(array(
            array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?wspToolbarItem=tabModules'), 'class' => 'ghost'),
            array('label' => _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
        ));
        ?>
    </form>
    <?php
    echo ui::card_close();
}

// Modules de l'espace
echo ui::card_open(_SYSTEM_MODF_OWN, 'puzzle', array(), array('class' => 'p4-card-flush'.(is_null($objModule) ? '' : ' p4-mt-16'), 'subtitle' => sizeof($arrOwnModules) ? str_replace('<NB>', sizeof($arrOwnModules), _SYSTEM_MODF_OWN_COUNT) : ''));

if (empty($arrOwnModules)) echo ui::emptystate('puzzle', _SYSTEM_MODF_OWN_NONE);
else
{
    ?>
    <div class="p4-table-wrap">
        <table class="p4-table p4-modules-table">
            <thead>
                <tr>
                    <th class="p4-col-pos"><?php echo ui::e(_SYSTEM_MODF_POSITION); ?></th>
                    <th><?php echo ui::e(_SYSTEM_LABEL_MODULENAME); ?></th>
                    <th class="p4-col-view"><?php echo ui::e(_SYSTEM_LABEL_VIEWMODE); ?></th>
                    <th class="p4-center"><?php echo ui::e(_SYSTEM_LABEL_ACTIVE); ?></th>
                    <th class="p4-center"><?php echo ui::e(_SYSTEM_LABEL_VISIBLE); ?></th>
                    <th class="p4-center"><?php echo ui::e(_SYSTEM_LABEL_SHARED); ?></th>
                    <th class="p4-center"><?php echo ui::e(_SYSTEM_LABEL_HERITED); ?></th>
                    <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $intRank = 0;
                $intLast = sizeof($arrOwnModules) - 1;

                foreach($arrOwnModules as $arrModule)
                {
                    $booOwner = $arrModule['instanceworkspace'] == $workspaceid;
                    $intId = (int) $arrModule['instanceid'];
                    $strBase = "admin.php?wspToolbarItem=tabModules&moduleid={$intId}&op=";

                    $strView = isset($ploopi_viewmodes[$arrModule['viewmode']]) ? $ploopi_viewmodes[$arrModule['viewmode']] : '';
                    if ($arrModule['transverseview']) $strView .= ' · '._SYSTEM_MODF_TRANSVERSE;

                    $arrActions = array();
                    if ($booOwner)
                    {
                        $arrActions[] = array('label' => _SYSTEM_MODF_CONFIGURE, 'icon' => 'settings', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode($strBase.'modify').'#modify');
                        $arrActions[] = array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost icon sm', 'href' => ploopi\crypt::urlencode($strBase.'delete'), 'confirm' => _SYSTEM_MODF_DELETE_CONFIRM);
                    }
                    elseif (!$arrModule['adminrestricted'])
                    {
                        $arrActions[] = array('label' => _SYSTEM_MODF_DETACH, 'icon' => 'unlink', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode($strBase.'unlinkinstance'), 'confirm' => _SYSTEM_MODF_DETACH_CONFIRM);
                    }
                    ?>
                    <tr<?php if (!is_null($objModule) && $objModule->fields['id'] == $intId) echo ' class="p4-row-current"'; ?>>
                        <td class="p4-col-pos" data-label="<?php echo ui::e(_SYSTEM_MODF_POSITION); ?>">
                            <div class="p4-row p4-order">
                                <?php
                                echo ui::button(array('label' => _SYSTEM_MODF_MOVEUP, 'icon' => 'arrow-up', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode($strBase.'moveup'), 'disabled' => $intRank == 0));
                                echo ui::button(array('label' => _SYSTEM_MODF_MOVEDOWN, 'icon' => 'arrow-down', 'class' => 'ghost icon sm', 'href' => ploopi\crypt::urlencode($strBase.'movedown'), 'disabled' => $intRank == $intLast));
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="p4-cell-main">
                                <?php echo ui::icon($objSkin->moduleicon($arrModule['label'])); ?>
                                <span>
                                    <b><?php echo ui::e($arrModule['instancename']); ?></b>
                                    <small><?php echo ui::e($arrModule['label']); ?><?php if (!$booOwner && isset($arrSharedModules[$intId])) echo ui::e(' · '.str_replace('<WORKSPACE>', $arrSharedModules[$intId]['workspacelabel'], _SYSTEM_MODF_SHAREDBY)); elseif (!$booOwner) echo ui::e(' · '._SYSTEM_MODF_FROMPARENT); ?></small>
                                </span>
                            </div>
                        </td>
                        <td class="p4-col-view p4-text-sm" data-label="<?php echo ui::e(_SYSTEM_LABEL_VIEWMODE); ?>"><?php echo ui::e($strView); ?></td>
                        <?php
                        foreach(array('active' => _SYSTEM_LABEL_ACTIVE, 'visible' => _SYSTEM_LABEL_VISIBLE, 'shared' => _SYSTEM_LABEL_SHARED, 'herited' => _SYSTEM_LABEL_HERITED) as $strState => $strLabel)
                        {
                            ?>
                            <td class="p4-center" data-label="<?php echo ui::e($strLabel); ?>"><?php echo ui::switchlink(!empty($arrModule[$strState]), $booOwner ? ploopi\crypt::urlencode($strBase."switch_{$strState}") : '', $strLabel.' : '.$arrModule['instancename']); ?></td>
                            <?php
                        }
                        ?>
                        <td class="p4-col-acts"><div class="p4-acts"><?php echo ui::actions($arrActions); ?></div></td>
                    </tr>
                    <?php
                    $intRank++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
echo ui::card_close();

// Modules qu'on peut ajouter : instances partagées par les espaces parents, nouvelles instances
echo ui::card_open(_SYSTEM_MODF_ADD, 'plus', array(), array('class' => 'p4-mt-16', 'subtitle' => _SYSTEM_MODF_ADD_DESC));
?>
<div class="p4-tiles">
    <?php
    foreach($arrSharedModules as $intInstanceId => $arrInstance)
    {
        if (isset($arrOwnModules[$intInstanceId])) continue;
        ?>
        <div class="p4-tile p4-tile-muted">
            <div class="p4-tile-head">
                <span class="p4-tile-icon"><?php echo ui::icon($objSkin->moduleicon($arrInstance['moduletype'])); ?></span>
                <div class="p4-grow">
                    <h3><?php echo ui::e($arrInstance['label']); ?></h3>
                    <p class="p4-text-xs p4-muted"><?php echo ui::e(str_replace('<WORKSPACE>', $arrInstance['workspacelabel'], _SYSTEM_MODF_SHAREDBY)); ?></p>
                </div>
            </div>
            <p><?php echo ui::e($arrInstance['description']); ?></p>
            <div class="p4-tile-foot">
                <span class="p4-grow"><?php echo ui::badge($arrInstance['moduletype']); ?></span>
                <?php echo ui::button(array('label' => _SYSTEM_MODF_USE, 'icon' => 'link', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?wspToolbarItem=tabModules&op=add&instance=SHARED,{$workspaceid},{$intInstanceId}"))); ?>
            </div>
        </div>
        <?php
    }

    foreach($arrInstalled as $arrModuleType)
    {
        ?>
        <div class="p4-tile">
            <div class="p4-tile-head">
                <span class="p4-tile-icon"><?php echo ui::icon($objSkin->moduleicon($arrModuleType['label'])); ?></span>
                <div class="p4-grow">
                    <h3><?php echo ui::e($arrModuleType['label']); ?></h3>
                    <p class="p4-text-xs p4-muted"><?php echo ui::e(_SYSTEM_MODF_NEWINSTANCE); ?></p>
                </div>
            </div>
            <p><?php echo ui::e($arrModuleType['description']); ?></p>
            <div class="p4-tile-foot">
                <span class="p4-grow"></span>
                <?php echo ui::button(array('label' => _SYSTEM_MODF_INSTANTIATE, 'icon' => 'plus', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?wspToolbarItem=tabModules&op=add&instance=NEW,{$workspaceid},{$arrModuleType['id']}"))); ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<?php
echo ui::card_close();
echo ui::script();
