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
 * Rattacher un utilisateur existant (groupe ou espace) ou un groupe (espace),
 * gabarits de génération 4 : liste filtrable des candidats, un bouton par ligne.
 *
 * Remplace admin_index_users_attachlist.php et admin_index_users_attachgroup.php
 * sous ploopi4 ; candidats : system_user_isattachable(), system_group_isattachable()
 * (include/functions.php), que les opérations attach_user et attach_group vérifient.
 *
 * Variables fournies : $strAttachType ('user' ou 'group'), $workspace, $workspaceid,
 * $group, $groupid.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$db = ploopi\db::get();
$booGroupLevel = $_SESSION['system']['level'] == _SYSTEM_GROUPS;

// Au-delà, la page deviendrait trop lourde : recherche côté serveur
$intMax = 3000;
$strQuery = isset($_GET['attach_q']) ? trim($_GET['attach_q']) : '';

$funcLetter = function($strText)
{
    $strFirst = strtolower(substr(ploopi\str::convertaccents(trim($strText)), 0, 1));
    return ($strFirst >= 'a' && $strFirst <= 'z') ? $strFirst : '#';
};

$arrRows = array();
$intTotal = 0;

if ($strAttachType == 'group')
{
    $arrWhere = array('g.system = 0', 'g.id NOT IN (SELECT id_group FROM ploopi_workspace_group WHERE id_workspace = '.((int) $workspaceid).')');

    if (!ploopi\acl::isadmin())
    {
        $arrAllowed = empty($workspaces['list'][$workspaceid]['groups']) ? array() : array_map('intval', array_keys($workspaces['list'][$workspaceid]['groups']));
        $arrWhere[] = empty($arrAllowed) ? '0' : 'g.id IN ('.implode(',', $arrAllowed).')';
    }

    $rs = $db->query('SELECT g.id, g.label, g.parents, (SELECT COUNT(*) FROM ploopi_group_user gu WHERE gu.id_group = g.id) AS nbusers FROM ploopi_group g WHERE '.implode(' AND ', $arrWhere).' ORDER BY g.label');
    while ($row = $db->fetchrow($rs)) $arrRows[] = $row;
    $intTotal = sizeof($arrRows);
}
else
{
    // Candidats : membres des groupes proposés (tous pour l'administrateur système), et dans un groupe, utilisateurs sans groupe
    $arrOr = array();
    if (ploopi\acl::isadmin()) $arrOr[] = 'u.id IN (SELECT id_user FROM ploopi_group_user)';
    else
    {
        $arrAllowed = system_attach_groups();
        if (!empty($arrAllowed)) $arrOr[] = 'u.id IN (SELECT id_user FROM ploopi_group_user WHERE id_group IN ('.implode(',', $arrAllowed).'))';
    }
    if ($booGroupLevel) $arrOr[] = 'u.id NOT IN (SELECT id_user FROM ploopi_group_user)';

    $arrWhere = array(empty($arrOr) ? '0' : '('.implode(' OR ', $arrOr).')');
    $arrWhere[] = $booGroupLevel ? 'u.id NOT IN (SELECT id_user FROM ploopi_group_user WHERE id_group = '.((int) $groupid).')' : 'u.id NOT IN (SELECT id_user FROM ploopi_workspace_user WHERE id_workspace = '.((int) $workspaceid).')';

    $row = $db->fetchrow($db->query('SELECT COUNT(*) AS nb FROM ploopi_user u WHERE '.implode(' AND ', $arrWhere)));
    $intTotal = (int) $row['nb'];

    if ($strQuery != '')
    {
        $strLike = $db->addslashes(str_replace(array('%', '_'), array('\\%', '\\_'), $strQuery));
        $arrWhere[] = "(u.lastname LIKE '%{$strLike}%' OR u.firstname LIKE '%{$strLike}%' OR u.login LIKE '%{$strLike}%' OR u.email LIKE '%{$strLike}%')";
    }

    if ($intTotal <= $intMax || $strQuery != '')
    {
        $rs = $db->query('SELECT u.id, u.lastname, u.firstname, u.login, u.email, u.service, u.entity FROM ploopi_user u WHERE '.implode(' AND ', $arrWhere).' ORDER BY u.lastname, u.firstname LIMIT '.$intMax);
        while ($row = $db->fetchrow($rs)) $arrRows[$row['id']] = $row + array('origin' => '');

        // Groupe d'origine : le premier de chaque utilisateur (le moins profond)
        if (!empty($arrRows))
        {
            $rs = $db->query('SELECT gu.id_user, g.label FROM ploopi_group_user gu INNER JOIN ploopi_group g ON g.id = gu.id_group WHERE gu.id_user IN ('.implode(',', array_keys($arrRows)).') ORDER BY g.depth, g.label');
            while ($row = $db->fetchrow($rs)) if ($arrRows[$row['id_user']]['origin'] === '') $arrRows[$row['id_user']]['origin'] = $row['label'];
        }
    }
}

$strTarget = $booGroupLevel ? $group->fields['label'] : $workspace->fields['label'];
echo '<p class="p4-text-sm p4-muted p4-ui p4-attach-intro">'.ui::e(str_replace('<TARGET>', $strTarget, $strAttachType == 'group' ? _SYSTEM_ATT_GROUP_INTRO : _SYSTEM_ATT_USER_INTRO)).'</p>';

if ($strAttachType == 'user' && ($intTotal > $intMax || $strQuery != ''))
{
    // Recherche côté serveur
    ?>
    <form class="p4-row p4-ui p4-attach-search" method="get" action="admin.php">
        <?php
        // Le jeton et le contexte voyagent dans ploopi_url, la saisie en clair
        parse_str(ploopi\crypt::queryencode('usrTabItem=tabUserAttach'), $arrHidden);
        foreach($arrHidden as $strName => $strValue) echo '<input type="hidden" name="'.ui::e($strName).'" value="'.ui::e($strValue).'" />';
        ?>
        <div class="p4-input-icon p4-grow"><?php echo ui::icon('search', 'p4-icon-sm'); ?><input class="p4-input" type="search" name="attach_q" value="<?php echo ui::e($strQuery); ?>" placeholder="<?php echo ui::e(_SYSTEM_ATT_SERVER_SEARCH); ?>" aria-label="<?php echo ui::e(_SYSTEM_ATT_SERVER_SEARCH); ?>" /></div>
        <?php echo ui::button(array('label' => _SYSTEM_ROLEF_SEARCH_GO, 'icon' => 'search', 'type' => 'submit')); ?>
    </form>
    <?php
    if ($strQuery == '')
    {
        echo ui::callout('info', ui::e(str_replace('<NB>', $intTotal, _SYSTEM_ATT_TOOMANY)));
        echo ui::script();
        return;
    }
}

if (empty($arrRows))
{
    echo ui::emptystate($strAttachType == 'group' ? 'users-group' : 'user-plus', $strAttachType == 'group' ? _SYSTEM_ATT_GROUP_NONE : _SYSTEM_ATT_USER_NONE);
    echo ui::script();
    return;
}
?>
<div class="p4-ui" data-p4-table="50">
    <div class="p4-table-tools">
        <div class="p4-input-icon p4-list-search">
            <?php echo ui::icon('search', 'p4-icon-sm'); ?>
            <input class="p4-input" type="search" data-p4-table-filter placeholder="<?php echo ui::e($strAttachType == 'group' ? _SYSTEM_GRL_FILTER : _SYSTEM_USERS_SEARCH); ?>" aria-label="<?php echo ui::e(_SYSTEM_USERS_SEARCH); ?>" autofocus />
        </div>
        <span class="p4-text-xs p4-muted"><span data-p4-table-count><?php echo sizeof($arrRows); ?></span> <?php echo ui::e($strAttachType == 'group' ? _SYSTEM_GRL_COUNT : _SYSTEM_ATT_USER_COUNT); ?></span>
        <div class="p4-alpha" role="group" aria-label="<?php echo ui::e(_SYSTEM_USERS_ALPHA); ?>" data-p4-table-alpha data-p4-all="<?php echo ui::e(_SYSTEM_USERS_ALL); ?>"></div>
    </div>
    <div class="p4-table-wrap">
        <table class="p4-table">
            <thead>
                <tr>
                    <?php
                    if ($strAttachType == 'group')
                    {
                        ?>
                        <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_WS_TYPE_GROUP).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-col-path"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_GRL_PATH).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-center"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_TAB_USERS).ui::icon('arrows-sort'); ?></button></th>
                        <?php
                    }
                    else
                    {
                        ?>
                        <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_USERS_USER).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-col-svc"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_LABEL_SERVICE).ui::icon('arrows-sort'); ?></button></th>
                        <th class="p4-col-org"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_LABEL_ORIGIN).ui::icon('arrows-sort'); ?></button></th>
                        <?php
                    }
                    ?>
                    <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($arrRows as $arrRow)
                {
                    if ($strAttachType == 'group')
                    {
                        $arrParents = system_getparents($arrRow['parents'], 'group');
                        array_shift($arrParents);
                        $arrPath = array();
                        foreach($arrParents as $arrParent) $arrPath[] = $arrParent['label'];
                        ?>
                        <tr data-p4-letter="<?php echo ui::e($funcLetter($arrRow['label'])); ?>">
                            <td data-p4-sort-value="<?php echo ui::e(mb_strtolower($arrRow['label'])); ?>"><span class="p4-cell-main"><?php echo ui::icon('users-group'); ?><b><?php echo ui::e($arrRow['label']); ?></b></span></td>
                            <td class="p4-col-path p4-text-sm p4-muted" data-label="<?php echo ui::e(_SYSTEM_GRL_PATH); ?>"><?php echo ui::e(implode(' › ', $arrPath)); ?></td>
                            <td class="p4-center" data-p4-sort-value="<?php echo (int) $arrRow['nbusers']; ?>" data-label="<?php echo ui::e(_SYSTEM_TAB_USERS); ?>"><span class="p4-count p4-count-muted"><?php echo (int) $arrRow['nbusers']; ?></span></td>
                            <td class="p4-col-acts"><?php echo ui::button(array('label' => _SYSTEM_LABEL_ATTACH, 'icon' => 'link', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?usrTabItem=tabGroupAttach&op=attach_group&orgid={$arrRow['id']}"))); ?></td>
                        </tr>
                        <?php
                    }
                    else
                    {
                        ?>
                        <tr data-p4-letter="<?php echo ui::e($funcLetter($arrRow['lastname'])); ?>">
                            <td data-p4-sort-value="<?php echo ui::e(mb_strtolower("{$arrRow['lastname']} {$arrRow['firstname']}")); ?>"><span class="p4-cell-main"><?php echo ui::icon('user'); ?><span><b><?php echo ui::e(mb_strtoupper($arrRow['lastname']).($arrRow['firstname'] != '' ? ', '.$arrRow['firstname'] : '')); ?></b><small class="p4-mono"><?php echo ui::e($arrRow['login']); ?></small></span></span></td>
                            <td class="p4-col-svc" data-label="<?php echo ui::e(_SYSTEM_LABEL_SERVICE); ?>"><span class="p4-svc"><b><?php echo ui::e($arrRow['service']); ?></b><small><?php echo ui::e($arrRow['entity']); ?></small></span></td>
                            <td class="p4-col-org p4-text-sm" data-label="<?php echo ui::e(_SYSTEM_LABEL_ORIGIN); ?>"><?php echo $arrRow['origin'] === '' ? '<em class="p4-muted">'.ui::e(_SYSTEM_ATT_NOGROUP).'</em>' : ui::e($arrRow['origin']); ?></td>
                            <td class="p4-col-acts"><?php echo ui::button(array('label' => _SYSTEM_LABEL_ATTACH, 'icon' => 'link', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?usrTabItem=tabUserAttach&op=attach_user&userid={$arrRow['id']}"))); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div data-p4-table-empty hidden><?php echo ui::emptystate('search', _SYSTEM_USERS_NONE); ?></div>
    <div class="p4-table-foot"><span></span><div class="p4-pager" data-p4-table-pager data-p4-prev="<?php echo ui::e(_SYSTEM_USERS_PREV); ?>" data-p4-next="<?php echo ui::e(_SYSTEM_USERS_NEXT); ?>"></div></div>
</div>
<?php
echo ui::script();
