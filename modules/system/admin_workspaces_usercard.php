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
 * Fiche de consultation d'un utilisateur, affichée dans le tiroir de la liste des
 * utilisateurs (gabarits de génération 4). Servie par l'opération system_user_card,
 * qui a vérifié que l'utilisateur est administrable.
 *
 * Variables fournies : $objUser (ploopi\user ouvert), $intContextWorkspace
 * (espace dont on affiche le niveau et les rôles, 0 sinon).
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

global $ploopi_system_levels;

// Table des niveaux absente hors d'une page complète (index-quick.php) : la reconstruire
if (empty($ploopi_system_levels) && defined('_PLOOPI_LEVEL_USER')) $ploopi_system_levels = array(
    _PLOOPI_ID_LEVEL_USER => _PLOOPI_LEVEL_USER,
    _PLOOPI_ID_LEVEL_GROUPMANAGER => _PLOOPI_LEVEL_GROUPMANAGER,
    _PLOOPI_ID_LEVEL_GROUPADMIN => _PLOOPI_LEVEL_GROUPADMIN,
    _PLOOPI_ID_LEVEL_SYSTEMADMIN => _PLOOPI_LEVEL_SYSTEMADMIN
);

$objSkin = ploopi\skin::get();
$db = ploopi\db::get();
$arrFields = $objUser->fields;
$intUserId = (int) $arrFields['id'];

$funcValue = function($strValue) {
    return trim($strValue) == '' ? '<span class="p4-muted">—</span>' : ploopi\str::htmlentities($strValue);
};

// Dernière connexion
$strLast = '';
if (!empty($arrFields['last_connection']))
{
    $arrLocal = ploopi\date::timestamp2local($arrFields['last_connection']);
    $strLast = "{$arrLocal['date']} {$arrLocal['time']}";
}

// État du compte
if (!empty($arrFields['disabled'])) $arrStatus = array(_SYSTEM_USERS_CARD_DISABLED, 'p4-badge-danger');
elseif (!empty($arrFields['date_expire']) && $arrFields['date_expire'] < date('YmdHis')) $arrStatus = array(_SYSTEM_USERS_CARD_EXPIRED, 'p4-badge-warning');
else $arrStatus = array(_SYSTEM_USERS_CARD_ACTIVE, 'p4-badge-success');

// Niveau dans l'espace du contexte
$strLevel = '';
if ($intContextWorkspace)
{
    $objWorkspaceUser = new ploopi\workspace_user();
    if ($objWorkspaceUser->open($intContextWorkspace, $intUserId) && isset($ploopi_system_levels[$objWorkspaceUser->fields['adminlevel']])) $strLevel = $ploopi_system_levels[$objWorkspaceUser->fields['adminlevel']];
}

$arrGroups = $objUser->getgroups(true);

$arrWorkspaces = array();
foreach($objUser->getworkspaces() as $row) $arrWorkspaces[sprintf('%04d%s', $row['depth'], $row['label'])] = $row['label'];
ksort($arrWorkspaces);

// Rôles dans l'espace du contexte : attribués à l'utilisateur ou à l'un de ses groupes
$arrRoles = array();
if ($intContextWorkspace)
{
    $strGroups = empty($arrGroups) ? '0' : implode(',', array_map('intval', array_keys($arrGroups)));

    $rs = $db->query("
        SELECT      DISTINCT r.id, r.label, m.label AS module
        FROM        ploopi_role r
        LEFT JOIN   ploopi_module m ON m.id = r.id_module
        WHERE       r.id IN (
                        SELECT id_role FROM ploopi_workspace_user_role WHERE id_user = {$intUserId} AND id_workspace = {$intContextWorkspace}
                        UNION
                        SELECT id_role FROM ploopi_workspace_group_role WHERE id_group IN ({$strGroups}) AND id_workspace = {$intContextWorkspace}
                    )
        ORDER BY    m.label, r.label
    ");

    while ($row = $db->fetchrow($rs)) $arrRoles[] = trim(($row['module'] != '' ? "{$row['module']} · " : '').$row['label']);
}
?>
<dl class="p4-kv">
    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_LOGIN); ?></dt>
    <dd class="p4-mono"><?php echo ploopi\str::htmlentities($arrFields['login']); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_EMAIL); ?></dt>
    <dd><?php echo $arrFields['email'] == '' ? $funcValue('') : '<a href="mailto:'.ploopi\str::htmlentities($arrFields['email']).'">'.ploopi\str::htmlentities($arrFields['email']).'</a>'; ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_ENTITY); ?></dt>
    <dd><?php echo $funcValue($arrFields['entity']); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_SERVICE); ?></dt>
    <dd><?php echo $funcValue(trim($arrFields['service'].($arrFields['service2'] != '' ? " / {$arrFields['service2']}" : ''))); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_FUNCTION); ?></dt>
    <dd><?php echo $funcValue($arrFields['function']); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_PHONE); ?></dt>
    <dd><?php echo $funcValue(trim($arrFields['phone'].($arrFields['mobile'] != '' ? ($arrFields['phone'] != '' ? ' · ' : '').$arrFields['mobile'] : ''))); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_LASTCONNECTION); ?></dt>
    <dd><?php echo $strLast == '' ? ploopi\str::htmlentities(_SYSTEM_USERS_NEVER) : ploopi\str::htmlentities($strLast); ?></dd>

    <dt><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_STATUS); ?></dt>
    <dd><span class="p4-badge <?php echo $arrStatus[1]; ?>"><?php echo ploopi\str::htmlentities($arrStatus[0]); ?></span></dd>

    <?php
    if ($strLevel != '')
    {
        ?>
        <dt><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_LEVEL); ?></dt>
        <dd><span class="p4-badge p4-badge-brand"><?php echo ploopi\str::htmlentities($strLevel); ?></span></dd>
        <?php
    }
    ?>
</dl>

<div class="p4-divider"></div>

<div class="p4-label"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_GROUPS); ?></div>
<div class="p4-chips p4-card-chips">
    <?php
    if (empty($arrGroups)) echo $funcValue('');
    foreach($arrGroups as $strGroup) echo '<span class="p4-badge p4-badge-brand">'.$objSkin->icon('users', 'p4-icon-sm').ploopi\str::htmlentities($strGroup).'</span>';
    ?>
</div>

<div class="p4-label"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_WORKSPACES); ?></div>
<div class="p4-chips p4-card-chips">
    <?php
    if (empty($arrWorkspaces)) echo $funcValue('');
    foreach($arrWorkspaces as $strWorkspace) echo '<span class="p4-badge">'.$objSkin->icon('building', 'p4-icon-sm').ploopi\str::htmlentities($strWorkspace).'</span>';
    ?>
</div>

<?php
if ($intContextWorkspace)
{
    ?>
    <div class="p4-label"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_ROLES); ?></div>
    <div class="p4-chips p4-card-chips">
        <?php
        if (empty($arrRoles)) echo $funcValue('');
        foreach($arrRoles as $strRole) echo '<span class="p4-badge">'.$objSkin->icon('shield', 'p4-icon-sm').ploopi\str::htmlentities($strRole).'</span>';
        ?>
    </div>
    <?php
}
