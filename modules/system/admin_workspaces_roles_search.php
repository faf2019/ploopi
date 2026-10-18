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
 * Résultats de la recherche d'utilisateurs et de groupes à qui attribuer un rôle,
 * gabarits de génération 4 (opération system_roleusers_search de op.php).
 *
 * Variables fournies par op.php : $roleid, $users, $groups (rattachés à l'espace
 * courant, filtrés par le texte saisi).
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

if (empty($_SESSION['system']['workspaceid']) || !system_isroleallowed($roleid, $_SESSION['system']['workspaceid'])) ploopi\system::kill();

if (empty($users) && empty($groups))
{
    echo ui::emptystate('search', _SYSTEM_ROLEF_SEARCH_NONE);
    return;
}
?>
<p class="p4-text-xs p4-muted p4-ui"><?php echo ui::e(_SYSTEM_ROLEF_SEARCH_HINT); ?></p>
<ul class="p4-assign-list p4-assign-results p4-ui">
    <?php
    foreach($groups as $arrGroup)
    {
        $strUrl = ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_roleusers_select_group&system_roleusers_roleid={$roleid}&system_roleusers_groupid={$arrGroup['id']}");
        ?>
        <li>
            <span class="p4-assign-icon"><?php echo ui::icon('users-group'); ?></span>
            <span class="p4-grow"><b><?php echo ui::e($arrGroup['label']); ?></b><small><?php echo ui::e(_SYSTEM_WS_TYPE_GROUP); ?></small></span>
            <?php echo ui::button(array('label' => _SYSTEM_ROLEF_ASSIGN, 'icon' => 'plus', 'class' => 'soft sm', 'attrs' => array('data-p4-load' => $strUrl, 'data-p4-target' => 'system_roleusers_list', 'data-p4-toast' => _SYSTEM_ROLEF_ASSIGNED_TOAST))); ?>
        </li>
        <?php
    }

    foreach($users as $arrUser)
    {
        $strUrl = ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_roleusers_select_user&system_roleusers_roleid={$roleid}&system_roleusers_userid={$arrUser['id']}");
        ?>
        <li>
            <span class="p4-assign-icon"><?php echo ui::icon('user'); ?></span>
            <span class="p4-grow"><b><?php echo ui::e(trim("{$arrUser['firstname']} {$arrUser['lastname']}")); ?></b><small><?php echo ui::e($arrUser['login'].($arrUser['service'] != '' ? ' · '.$arrUser['service'] : '')); ?></small></span>
            <?php echo ui::button(array('label' => _SYSTEM_ROLEF_ASSIGN, 'icon' => 'plus', 'class' => 'soft sm', 'attrs' => array('data-p4-load' => $strUrl, 'data-p4-target' => 'system_roleusers_list', 'data-p4-toast' => _SYSTEM_ROLEF_ASSIGNED_TOAST))); ?>
        </li>
        <?php
    }
    ?>
</ul>
