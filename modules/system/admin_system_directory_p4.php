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
 * Annuaire des utilisateurs (Système › Utilisateurs, onglet Annuaire d'un espace),
 * gabarits de génération 4 : rendu des filtres, de la barre d'outils et du tableau.
 *
 * admin_system_directory.php garde la recherche (rattachements par les groupes et les
 * sous-espaces) et inclut ce fichier pour chaque partie ($strDirectoryPart :
 * 'filters', 'toolbar', 'table'), avec ses variables : $arrFilter, $intNbRep,
 * $intMaxResponse, $arrUser, $arrRoles.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

global $ploopi_system_levels;

$booSysLevel = $_SESSION['system']['level'] == 'system';
$booSysAdmin = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN;
$strDirUrl = $booSysLevel ? 'admin.php?sysToolbarItem=directory' : 'admin.php?wspToolbarItem=tabDirectory';

switch($strDirectoryPart)
{
    /**
     * En-tête et filtres (mêmes champs que l'écran historique)
     */
    case 'filters':
        if ($booSysLevel) echo ui::pagehead(_SYSTEM_MENU_DIRECTORY, _SYSTEM_DIR_SUBTITLE);
        else echo '<p class="p4-text-sm p4-muted p4-ui p4-attach-intro">'.ui::e(_SYSTEM_DIR_WORKSPACE_INTRO).'</p>';

        $funcDate = function($strLocal)
        {
            return $strLocal == '' ? '' : ploopi\date::local2timestamp($strLocal);
        };

        $intActive = 0;
        foreach($arrFilter as $strValue) if ($strValue != '') $intActive++;

        echo ui::card_open(_SYSTEM_USERS_FILTERS, 'filter', array(), array('class' => 'p4-dir-filters', 'subtitle' => $intActive ? str_replace('<NB>', $intActive, _SYSTEM_DIR_ACTIVEFILTERS) : _SYSTEM_DIR_FILTERS_DESC));
        ?>
        <form class="p4-form p4-form-dense" action="<?php echo ploopi\crypt::urlencode($strDirUrl); ?>" method="post">
            <div class="p4-fgrid">
                <?php
                foreach(array(
                    'ploopi_lastname' => _SYSTEM_LABEL_LASTNAME,
                    'ploopi_firstname' => _SYSTEM_LABEL_FIRSTNAME,
                    'ploopi_login' => _SYSTEM_LABEL_LOGIN,
                    'ploopi_email' => _SYSTEM_LABEL_EMAIL,
                    'ploopi_group' => _SYSTEM_WS_TYPE_GROUP,
                    'ploopi_workspace' => _SYSTEM_WS_TYPE_WORKSPACE
                ) as $strName => $strLabel)
                {
                    echo ui::field($strLabel, ui::input($strName, $arrFilter[$strName], array('type' => 'search', 'maxlength' => 100)), array('for' => $strName, 'width' => 'c4'));
                }
                echo ui::field(_SYSTEM_USERS_CONNECTION_FROM, ui::date('ploopi_last_connection_1', $funcDate($arrFilter['ploopi_last_connection_1'])), array('for' => 'ploopi_last_connection_1', 'width' => 'c4'));
                echo ui::field(_SYSTEM_USERS_CONNECTION_TO, ui::date('ploopi_last_connection_2', $funcDate($arrFilter['ploopi_last_connection_2'])), array('for' => 'ploopi_last_connection_2', 'width' => 'c4'));
                ?>
            </div>
            <div class="p4-row p4-wrap p4-filters-actions">
                <?php
                if ($booSysAdmin) echo ui::button(array('label' => _SYSTEM_DIR_DELETEALL, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'type' => 'submit', 'attrs' => array('name' => 'delete', 'value' => '1'), 'confirm' => _SYSTEM_DIR_DELETEALL_CONFIRM));
                ?>
                <span class="p4-grow"></span>
                <?php
                echo ui::button(array('label' => _SYSTEM_USERS_RESET, 'icon' => 'eraser', 'class' => 'ghost sm', 'href' => ploopi\crypt::urlencode($strDirUrl.'&system_filter_reset')));
                echo ui::button(array('label' => _SYSTEM_USERS_FILTER, 'icon' => 'filter', 'class' => 'primary sm', 'type' => 'submit'));
                ?>
            </div>
        </form>
        <?php
        echo ui::card_close();
    break;

    /**
     * Nombre de réponses et exports
     */
    case 'toolbar':
        $arrExports = array(
            'xlsx' => array('Excel (XLSX)', 'file-spreadsheet'),
            'xls' => array('Excel 97 (XLS)', 'file-spreadsheet'),
            'csv' => array('CSV', 'file-text'),
            'xml' => array('XML', 'file-text'),
            'vcf' => array('vCard (VCF)', 'id')
        );
        if (ploopi\param::get('system_unoconv') != '')
        {
            $arrExports['ods'] = array('OpenDocument (ODS)', 'file-spreadsheet');
            $arrExports['pdf'] = array('PDF', 'file-text');
        }

        $arrItems = array();
        foreach($arrExports as $strType => $arrExport) $arrItems[] = array('label' => $arrExport[0], 'icon' => $arrExport[1], 'href' => ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc={$strType}"));
        ?>
        <div class="p4-row p4-wrap p4-ui p4-dir-toolbar">
            <strong class="p4-grow"><?php echo ui::e(str_replace('<NB>', $intNbRep, $intNbRep > 1 ? _SYSTEM_DIR_COUNT_PLURAL : _SYSTEM_DIR_COUNT)); ?></strong>
            <?php if ($intNbRep) echo ui::menu('p4-dir-export', array('label' => _SYSTEM_USERS_EXPORT, 'icon' => 'download', 'hidelabel' => true), $arrItems); ?>
        </div>
        <?php
        if ($intNbRep > $intMaxResponse) echo ui::callout('warning', ui::e(str_replace(array('<NB>', '<MAX>'), array($intNbRep, $intMaxResponse), _SYSTEM_DIR_TOOMANY)));
        if ($booSysLevel) echo ui::callout('info', ui::e(_SYSTEM_DIR_SYSTEM_HINT));
    break;

    /**
     * Tableau des utilisateurs trouvés
     */
    case 'table':
        $funcRoles = function($intWorkspace, $intUser, $arrUserGroups) use ($arrRoles)
        {
            $arrList = array();
            if (isset($arrRoles['groups'][$intWorkspace]))
            {
                foreach($arrRoles['groups'][$intWorkspace] as $intGroup => $arrGroupRoles)
                {
                    if (isset($arrUserGroups[$intGroup])) foreach($arrGroupRoles as $intRole => $arrRole) $arrList[$intRole] = "{$arrRole['role_label']} ({$arrRole['module_label']})";
                }
            }
            if (isset($arrRoles['users'][$intWorkspace][$intUser]))
            {
                foreach($arrRoles['users'][$intWorkspace][$intUser] as $intRole => $arrRole) $arrList[$intRole] = "{$arrRole['role_label']} ({$arrRole['module_label']})";
            }
            asort($arrList);
            return $arrList;
        };
        ?>
        <div class="p4-ui" data-p4-table="50">
            <div class="p4-table-tools">
                <div class="p4-input-icon p4-list-search">
                    <?php echo ui::icon('search', 'p4-icon-sm'); ?>
                    <input class="p4-input" type="search" data-p4-table-filter placeholder="<?php echo ui::e(_SYSTEM_DIR_REFINE); ?>" aria-label="<?php echo ui::e(_SYSTEM_DIR_REFINE); ?>" />
                </div>
            </div>
            <div class="p4-table-wrap">
                <table class="p4-table p4-dir-table">
                    <thead>
                        <tr>
                            <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_USERS_USER).ui::icon('arrows-sort'); ?></button></th>
                            <th class="p4-col-groups"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_USERS_CARD_GROUPS).ui::icon('arrows-sort'); ?></button></th>
                            <th><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_DIR_WORKSPACES).ui::icon('arrows-sort'); ?></button></th>
                            <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($arrUser as $intUserId => $row)
                        {
                            $arrUserGroups = empty($row['groups']) ? array() : $row['groups'];
                            asort($arrUserGroups);
                            $arrUserWorkspaces = empty($row['workspaces']) ? array() : $row['workspaces'];
                            uasort($arrUserWorkspaces, function($a, $b) { return strcasecmp((string) $a['label'], (string) $b['label']); });

                            $strEmail = filter_var((string) $row['email'], FILTER_VALIDATE_EMAIL);
                            $strName = mb_strtoupper($row['lastname']).($row['firstname'] != '' ? ', '.$row['firstname'] : '');

                            // Fiche modifiable depuis le premier groupe de l'utilisateur
                            $strEdit = empty($arrUserGroups) ? '' : ploopi\crypt::urlencode('admin.php?system_level=work&groupid='.key($arrUserGroups)."&wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=modify_user&user_id={$intUserId}");

                            $arrActs = array();
                            if ($strEmail) $arrActs[] = array('label' => _SYSTEM_LABEL_EMAIL.' : '.$strEmail, 'icon' => 'mail', 'class' => 'ghost icon sm', 'href' => 'mailto:'.$strEmail);
                            if ($strEdit != '') $arrActs[] = array('label' => _SYSTEM_LABEL_MODIFY, 'icon' => 'pencil', 'class' => 'ghost icon sm', 'href' => $strEdit);
                            if ($booSysLevel && $booSysAdmin) $arrActs[] = array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost icon sm', 'href' => ploopi\crypt::urlencode("admin.php?ploopi_op=system_delete_user&system_user_id={$intUserId}"), 'confirm' => _SYSTEM_DIR_DELETE_CONFIRM);
                            ?>
                            <tr data-p4-text="<?php echo ui::e("{$row['lastname']} {$row['firstname']} {$row['login']} {$row['email']} ".implode(' ', $arrUserGroups).' '.implode(' ', array_column($arrUserWorkspaces, 'label'))); ?>">
                                <td data-p4-sort-value="<?php echo ui::e(mb_strtolower("{$row['lastname']} {$row['firstname']}")); ?>">
                                    <div class="p4-cell-main">
                                        <span class="p4-avatar p4-av-<?php echo (ord(mb_substr($row['lastname'], 0, 1)) ?: 0) % 5; ?>"><?php echo ui::e(mb_strtoupper(mb_substr($row['firstname'], 0, 1).mb_substr($row['lastname'], 0, 1))); ?></span>
                                        <span>
                                            <?php if ($strEdit != '') { ?><a href="<?php echo ui::e($strEdit); ?>"><b><?php echo ui::e($strName); ?></b></a><?php } else { ?><b><?php echo ui::e($strName); ?></b><?php } ?>
                                            <small class="p4-mono"><?php echo ui::e($row['login']); ?></small>
                                        </span>
                                        <?php if (!empty($row['disabled'])) echo ui::badge(_SYSTEM_USERS_CARD_DISABLED, 'danger'); ?>
                                    </div>
                                </td>
                                <td class="p4-col-groups" data-p4-sort-value="<?php echo ui::e(mb_strtolower(implode(',', $arrUserGroups))); ?>" data-label="<?php echo ui::e(_SYSTEM_USERS_CARD_GROUPS); ?>">
                                    <?php
                                    if (empty($arrUserGroups)) echo '<em class="p4-muted p4-text-sm">'.ui::e(_SYSTEM_DIR_NOGROUP).'</em>';
                                    else
                                    {
                                        echo '<div class="p4-chips">';
                                        foreach($arrUserGroups as $intGroup => $strGroup) echo '<a class="p4-badge" href="'.ui::e(ploopi\crypt::urlencode("admin.php?system_level=work&groupid={$intGroup}")).'">'.ui::icon('users-group').ui::e($strGroup).'</a>';
                                        echo '</div>';
                                    }
                                    ?>
                                </td>
                                <td data-p4-sort-value="<?php echo ui::e(mb_strtolower(implode(',', array_column($arrUserWorkspaces, 'label')))); ?>" data-label="<?php echo ui::e(_SYSTEM_DIR_WORKSPACES); ?>">
                                    <?php
                                    if (empty($arrUserWorkspaces)) echo '<em class="p4-muted p4-text-sm">'.ui::e(_SYSTEM_DIR_NOWORKSPACE).'</em>';
                                    else
                                    {
                                        echo '<ul class="p4-dir-ws">';
                                        foreach($arrUserWorkspaces as $intWorkspace => $arrWorkspace)
                                        {
                                            $intLevel = $arrWorkspace['adminlevel'];
                                            $strLevel = isset($ploopi_system_levels[$intLevel]) ? $ploopi_system_levels[$intLevel] : (isset($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]) ? $ploopi_system_levels[_PLOOPI_ID_LEVEL_USER] : '');
                                            $arrWsRoles = $funcRoles($intWorkspace, $intUserId, $arrUserGroups);

                                            echo '<li><a href="'.ui::e(ploopi\crypt::urlencode("admin.php?system_level=work&workspaceid={$intWorkspace}")).'">'.ui::e($arrWorkspace['label']).'</a> '
                                                .ui::badge($strLevel, $intLevel > _PLOOPI_ID_LEVEL_USER ? 'brand' : '')
                                                .(empty($arrWsRoles) ? '' : '<small>'.ui::e(implode(', ', $arrWsRoles)).'</small>').'</li>';
                                        }
                                        echo '</ul>';
                                    }
                                    ?>
                                </td>
                                <td class="p4-col-acts"><div class="p4-acts"><?php echo ui::actions($arrActs); ?></div></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div data-p4-table-empty hidden><?php echo ui::emptystate('search', _SYSTEM_USERS_NONE); ?></div>
            <div class="p4-table-foot"><span></span><div class="p4-pager" data-p4-table-pager data-p4-prev="<?php echo ui::e(_SYSTEM_USERS_PREV); ?>" data-p4-next="<?php echo ui::e(_SYSTEM_USERS_NEXT); ?>"></div></div>
        </div>
        <?php
    break;
}
