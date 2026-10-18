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
 * Onglet « Groupe » des gabarits de génération 4 : propriétés du groupe choisi, ou
 * création d'un groupe (sous-groupe d'un groupe, ou groupe propre à un espace).
 *
 * Remplace admin_index_group.php et admin_index_group_add.php sous ploopi4.
 * L'enregistrement reste celui de admin_index.php (op=save_group).
 *
 * Variables fournies par admin_index.php : $group (null dans un espace), $groupid,
 * $groups, $workspace, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$booInWorkspace = $_SESSION['system']['level'] == _SYSTEM_WORKSPACES;
$booNew = $booInWorkspace || $op == 'child';

// Sous-groupe d'un groupe : réservé aux administrateurs d'espace (cf. admin_index.php)
if (!$booInWorkspace && $op == 'child' && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_GROUPADMIN) ploopi\output::redirect('admin.php');

if ($booInWorkspace)
{
    $strParent = $workspace->fields['label'];
    $strBack = 'admin.php?wspToolbarItem=tabUsers';
    $strAction = 'admin.php?op=save_group&group_id_group=';
}
elseif ($booNew)
{
    $strParent = $group->fields['label'];
    $strBack = 'admin.php?wspToolbarItem=tabGroups';
    $strAction = "admin.php?op=save_group&group_id_group={$group->fields['id']}";
}
else
{
    $strAction = "admin.php?op=save_group&group_id={$group->fields['id']}";
}

if ($booNew)
{
    ?>
    <div class="p4-users-head p4-ui">
        <a class="p4-back" href="<?php echo ploopi\crypt::urlencode($strBack); ?>"><?php echo ui::icon('arrow-left', 'p4-icon-sm'); ?><?php echo ui::e($strParent); ?></a>
        <h3 class="p4-users-subtitle p4-grow"><?php echo ui::e($booInWorkspace ? _SYSTEM_GRF_NEW_WORKSPACE_TITLE : _SYSTEM_GRF_NEW_TITLE); ?></h3>
    </div>
    <?php
}
else
{
    $intNbGroups = !empty($groups['tree'][$groupid]) ? sizeof($groups['tree'][$groupid]) : 0;
    $intNbUsers = (int) $group->countusers();

    echo ui::stats(array(
        array(_SYSTEM_GRF_STAT_GROUPS, $intNbGroups, 'users-group'),
        array(_SYSTEM_WSF_STAT_USERS, $intNbUsers, 'users')
    ));
}
?>
<form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode($strAction); ?>" method="post" data-p4-validate>
    <?php
    echo ui::section_open(_SYSTEM_WSF_SEC_IDENTITY, $booInWorkspace ? _SYSTEM_GRF_SEC_IDENTITY_WS_DESC : _SYSTEM_GRF_SEC_IDENTITY_DESC, 'users-group');
    echo ui::field(_SYSTEM_LABEL_GROUP_NAME, ui::input('group_label', $booNew ? '' : $group->fields['label'], array('check' => 'string', 'required' => true, 'maxlength' => 255, 'autofocus' => $booNew, 'placeholder' => $booNew ? _SYSTEM_GRF_NEW_PLACEHOLDER : null)), array('width' => 'c12', 'for' => 'group_label', 'required' => true));
    echo '<div class="p4-c12">'.ui::toggle('group_shared', !$booNew && !empty($group->fields['shared']), _SYSTEM_LABEL_GROUP_SHARED, ui::e(_SYSTEM_GRF_SHARED_HINT)).'</div>';
    echo ui::section_close();

    echo ui::formactions(array(
        $booNew ? array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode($strBack), 'class' => 'ghost') : '',
        array('label' => $booNew ? _SYSTEM_GRF_CREATE : _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
    ));
    ?>
</form>
<?php
if (!$booNew)
{
    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER)
    {
        // Cloner (administrateur d'espace), supprimer (gestionnaire, groupe vide) : règles de admin_index.php
        $arrClone = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN ?
            array('label' => _SYSTEM_LABEL_CREATE_CLONE, 'icon' => 'copy', 'href' => ploopi\crypt::urlencode("admin.php?op=clone&groupid={$groupid}"), 'confirm' => _SYSTEM_GRF_CLONE_CONFIRM) :
            array('label' => _SYSTEM_LABEL_CREATE_CLONE, 'icon' => 'copy', 'disabled' => true);

        if ($intNbGroups) $strDeleteHint = _SYSTEM_GRF_DELETE_CHILDREN;
        elseif ($intNbUsers) $strDeleteHint = _SYSTEM_WSF_DELETE_USERS;
        else $strDeleteHint = '';

        $arrDelete = $strDeleteHint == '' ?
            array('label' => _SYSTEM_LABEL_DELETE_GROUP, 'icon' => 'trash', 'class' => 'danger-ghost', 'href' => ploopi\crypt::urlencode("admin.php?op=delete&groupid={$groupid}"), 'confirm' => _SYSTEM_GRF_DELETE_CONFIRM) :
            array('label' => _SYSTEM_LABEL_DELETE_GROUP, 'icon' => 'trash', 'class' => 'danger-ghost', 'disabled' => true);

        echo ui::card_open(_SYSTEM_GRF_ACTIONS, 'alert-triangle', array(), array('class' => 'p4-danger-zone p4-mt-16'));
        ?>
        <div class="p4-danger-row"><div><strong><?php echo ui::e(_SYSTEM_GRF_CLONE_TITLE); ?></strong><span><?php echo ui::e($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN ? _SYSTEM_GRF_CLONE_HINT : _SYSTEM_GRF_CLONE_DENIED); ?></span></div><?php echo ui::button($arrClone); ?></div>
        <div class="p4-danger-row"><div><strong><?php echo ui::e(_SYSTEM_GRF_DELETE_TITLE); ?></strong><span><?php echo ui::e($strDeleteHint == '' ? _SYSTEM_GRF_DELETE_HINT : $strDeleteHint); ?></span></div><?php echo ui::button($arrDelete); ?></div>
        <?php
        echo ui::card_close();
    }

    echo ui::card_open(_SYSTEM_WSF_NOTES, 'notes', array(), array('class' => 'p4-mt-16'));
    echo '<div class="p4-legacy">';
    ploopi\annotation::display(_SYSTEM_OBJECT_GROUP, $group->fields['id'], $group->fields['label']);
    echo '</div>';
    echo ui::card_close();
}

echo ui::script();
