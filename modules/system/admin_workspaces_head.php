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
 * En-tête de l'espace ou du groupe choisi et ses onglets (gabarits de génération 4).
 * Inclus par admin_index.php à la place de la barre d'outils : mêmes onglets
 * ($toolbar), même mémorisation de l'onglet courant. Ouvre le corps de la carte,
 * que referme admin_workspaces.php.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$objSkin = ploopi\skin::get();

// Onglet courant : celui demandé s'il existe pour cette entité, la liste des utilisateurs sinon
if (!isset($toolbar[$_SESSION['system']['wspToolbarItem']])) $_SESSION['system']['wspToolbarItem'] = isset($toolbar['tabUsers']) ? 'tabUsers' : key($toolbar);

$booGroup = $_SESSION['system']['level'] == _SYSTEM_GROUPS;

// Ordre, libellés et icônes des onglets
$arrTabs = array(
    'tabWorkspaces' => array(_SYSTEM_LABELICON_WORKSPACE, 'building'),
    'tabGroups' => array(_SYSTEM_LABELICON_GROUP, 'users-group'),
    'tabUsers' => array(_SYSTEM_TAB_USERS, 'users'),
    'tabModules' => array(_SYSTEM_LABELICON_MODULES, 'puzzle'),
    'tabParams' => array(_SYSTEM_LABELICON_PARAMS, 'adjustments-horizontal'),
    'tabRoles' => array(_SYSTEM_LABELICON_ROLES, 'shield'),
    'tabDirectory' => array(_SYSTEM_TAB_DIRECTORY, 'address-book'),
);

if ($booGroup)
{
    $strLabel = $group->fields['label'];
    $strType = _SYSTEM_WS_TYPE_GROUP;
    $strIcon = 'users';
    $intCount = (int) $group->countusers();
}
else
{
    $strLabel = $workspace->fields['label'];
    $strType = _SYSTEM_WS_TYPE_WORKSPACE;
    $strIcon = empty($workspace->fields['frontoffice']) ? 'building' : 'world-www';
    $intCount = (int) $workspace->countusers();
}

/**
 * Chemin de l'entité : espaces parents (hors racine système), puis groupes parents
 */
$arrEntityCrumbs = array();

$strWorkspaceParents = $booGroup ? (isset($groups['list'][$groupid]['parents_workspace']) ? $groups['list'][$groupid]['parents_workspace'] : '') : $workspace->fields['parents'];

foreach(explode(';', $strWorkspaceParents) as $intId)
{
    if ($intId > 1 && isset($workspaces['list'][$intId])) $arrEntityCrumbs[] = array($workspaces['list'][$intId]['label'], "admin.php?workspaceid={$intId}");
}

if ($booGroup)
{
    foreach(explode(';', $group->fields['parents']) as $intId)
    {
        if ($intId > 1 && isset($groups['list'][$intId])) $arrEntityCrumbs[] = array($groups['list'][$intId]['label'], "admin.php?groupid={$intId}");
    }
}
?>
<div class="p4-entity-head p4-ui">
    <div class="p4-head-icon"><?php echo $objSkin->icon($strIcon); ?></div>
    <div class="p4-grow">
        <div class="p4-row p4-wrap">
            <h2 class="p4-entity-title"><?php echo ploopi\str::htmlentities($strLabel); ?></h2>
            <span class="p4-badge p4-badge-brand"><?php echo ploopi\str::htmlentities($strType); ?></span>
        </div>
        <div class="p4-crumbs">
            <?php
            if (empty($arrEntityCrumbs)) echo ploopi\str::htmlentities(_SYSTEM_WS_ROOT);
            else
            {
                foreach($arrEntityCrumbs as $intEntityCrumb => $arrEntityCrumb)
                {
                    if ($intEntityCrumb) echo $objSkin->icon('chevron-right');
                    ?><a href="<?php echo ploopi\crypt::urlencode($arrEntityCrumb[1]); ?>"><?php echo ploopi\str::htmlentities($arrEntityCrumb[0]); ?></a><?php
                }
            }
            ?>
        </div>
    </div>
    <button type="button" class="p4-btn p4-tree-open" data-p4-open="p4-tree-drawer"><?php echo $objSkin->icon('binary-tree', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_OPEN); ?></button>
</div>

<nav class="p4-entity-tabs p4-ui" aria-label="Onglets">
    <div class="p4-tabs">
        <?php
        foreach($arrTabs as $strKey => $arrTab)
        {
            if (!isset($toolbar[$strKey])) continue;
            $booCurrent = $_SESSION['system']['wspToolbarItem'] == $strKey;
            ?>
            <a class="p4-tab<?php if ($booCurrent) echo ' p4-active'; ?>" href="<?php echo ploopi\crypt::urlencode($toolbar[$strKey]['url']); ?>"<?php if ($booCurrent) echo ' aria-current="page"'; ?>>
                <?php echo $objSkin->icon($arrTab[1]); ?><?php echo ploopi\str::htmlentities($arrTab[0]); ?>
                <?php if ($strKey == 'tabUsers') { ?><span class="p4-count p4-count-muted"><?php echo $intCount; ?></span><?php } ?>
            </a>
            <?php
        }
        ?>
    </div>
</nav>

<div class="p4-entity-body">
