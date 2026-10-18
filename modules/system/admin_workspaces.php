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
 * Espaces de travail et groupes : présentation des gabarits de génération 4 (ploopi4).
 *
 * Arborescence filtrable à gauche (tiroir sur petit écran), carte de l'espace ou du
 * groupe choisi à droite. Le contenu de la carte (en-tête, onglets, écrans) est
 * produit par admin_index.php, qui garde ses opérations et ses contrôles.
 *
 * Variables fournies par admin.php : $workspaces, $groups (system_getwg),
 * $workspaceid, $groupid (validés), $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$objSkin = ploopi\skin::get();

$booSysAdmin = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN;

// Racine de l'arborescence : tout pour l'administrateur système, l'espace courant sinon
$intRootWid = $booSysAdmin ? 1 : $workspaces['list'][$_SESSION['ploopi']['workspaceid']]['id_workspace'];
$arrTree = system_tree_nodes('workspaces', $intRootWid);
$arrCounts = system_tree_counts();

$strSelType = empty($groupid) ? 'workspace' : 'group';
$intSelId = empty($groupid) ? $workspaceid : $groupid;

/**
 * Rendu récursif de l'arborescence. Un nœud est déplié s'il contient le nœud choisi
 * ou s'il est au premier niveau.
 *
 * @return array [html, contient le nœud choisi]
 */
$funcTree = function($arrNodes, $intDepth) use (&$funcTree, $objSkin, $arrCounts, $strSelType, $intSelId)
{
    $strHtml = '';
    $booHasSel = false;

    foreach($arrNodes as $arrNode)
    {
        list($strChildren, $booChildSel) = $arrNode['children'] ? $funcTree($arrNode['children'], $intDepth + 1) : array('', false);

        $booSel = $arrNode['type'] == $strSelType && $arrNode['id'] == $intSelId;
        $booHasSel = $booHasSel || $booSel || $booChildSel;
        $booOpen = $booChildSel || $intDepth == 0;

        $strUrl = ploopi\crypt::urlencode($arrNode['type'] == 'workspace' ? "admin.php?workspaceid={$arrNode['id']}" : "admin.php?groupid={$arrNode['id']}");
        $intCount = isset($arrCounts[$arrNode['type']][$arrNode['id']]) ? $arrCounts[$arrNode['type']][$arrNode['id']] : 0;
        $strIcon = $arrNode['type'] == 'workspace' ? (empty($arrNode['frontoffice']) ? 'building' : 'world-www') : 'users';

        $strHtml .= '<li role="treeitem"'.($strChildren != '' ? ' aria-expanded="'.($booOpen ? 'true' : 'false').'"' : '').($booSel ? ' aria-selected="true"' : '').($strChildren != '' && !$booOpen ? ' class="p4-collapsed"' : '').'>'
            .'<div class="p4-node'.($arrNode['type'] == 'workspace' ? ' p4-node-ws' : '').($booSel ? ' p4-active' : '').'">'
            .($strChildren != '' ? '<button type="button" class="p4-node-tw" data-p4-tree-toggle aria-label="'.ploopi\str::htmlentities(_SYSTEM_WS_TREE_TOGGLE).'">'.$objSkin->icon('chevron-down').'</button>' : '<span class="p4-node-tw"></span>')
            .'<a href="'.$strUrl.'"'.($booSel ? ' aria-current="page"' : '').'>'.$objSkin->icon($strIcon, 'p4-node-icon').'<span class="p4-node-label">'.ploopi\str::htmlentities($arrNode['label']).'</span>'.($intCount ? '<span class="p4-node-count">'.$intCount.'</span>' : '').'</a>'
            .'</div>'
            .($strChildren != '' ? '<ul role="group">'.$strChildren.'</ul>' : '')
            .'</li>';
    }

    return array($strHtml, $booHasSel);
};

list($strTree) = $funcTree($arrTree, 0);

// Actions de création, selon l'entité choisie et le niveau d'administration (cf. admin_index.php)
$arrActions = array();

if ($strSelType == 'workspace')
{
    $arrActions[] = array(
        'label' => _SYSTEM_WS_NEWGROUP,
        'icon' => 'users-group',
        'url' => "admin.php?wspToolbarItem=tabWorkspaces&op=groupchild&workspaceid={$workspaceid}",
        'class' => ''
    );

    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
    {
        $arrActions[] = array(
            'label' => _SYSTEM_WS_NEWWORKSPACE,
            'icon' => 'building',
            'url' => "admin.php?wspToolbarItem=tabWorkspaces&op=child&gworkspaceid={$workspaceid}",
            'class' => ' p4-btn-primary'
        );
    }
}
elseif ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
{
    $arrActions[] = array(
        'label' => _SYSTEM_WS_NEWGROUP,
        'icon' => 'users-group',
        'url' => "admin.php?wspToolbarItem=tabGroups&op=child&groupid={$groupid}",
        'class' => ' p4-btn-primary'
    );
}
?>
<div class="p4-page p4-ws-page">
    <div class="p4-page-head p4-ui">
        <div class="p4-grow">
            <h1><?php echo ploopi\str::htmlentities(_SYSTEM_MENU_WORKSPACES); ?></h1>
            <p><?php echo ploopi\str::htmlentities(_SYSTEM_WS_SUBTITLE); ?></p>
        </div>
        <div class="p4-row p4-wrap">
            <?php
            foreach($arrActions as $arrAction)
            {
                ?>
                <a class="p4-btn<?php echo $arrAction['class']; ?>" href="<?php echo ploopi\crypt::urlencode($arrAction['url']); ?>"><?php echo $objSkin->icon('plus', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities($arrAction['label']); ?></a>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="p4-split">
        <section class="p4-card p4-tree-card p4-ui" aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_LABEL); ?>">
            <div class="p4-tree-search">
                <div class="p4-input-icon">
                    <?php echo $objSkin->icon('search', 'p4-icon-sm'); ?>
                    <input class="p4-input" type="search" placeholder="<?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_FILTER); ?>" data-p4-tree-filter aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_FILTER); ?>" />
                </div>
            </div>
            <div class="p4-tree-body">
                <ul class="p4-tree" role="tree" data-p4-tree><?php echo $strTree; ?></ul>
                <div class="p4-empty" data-p4-tree-empty hidden><?php echo $objSkin->icon('search'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_NONE); ?></div>
            </div>
        </section>

        <section class="p4-card p4-entity">
            <?php include_once './modules/system/admin_index.php'; ?>
            </div><?php /* corps de la carte, ouvert par admin_workspaces_head.php */ ?>
        </section>
    </div>
</div>

<div class="p4-drawer-backdrop" data-p4-close></div>
<aside class="p4-drawer p4-drawer-left p4-ui" id="p4-tree-drawer" aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_TITLE); ?>" aria-hidden="true">
    <div class="p4-drawer-header">
        <?php echo $objSkin->icon('binary-tree'); ?>
        <h2><?php echo ploopi\str::htmlentities(_SYSTEM_WS_TREE_TITLE); ?></h2>
        <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon" data-p4-close aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_CLOSE); ?>"><?php echo $objSkin->icon('x'); ?></button>
    </div>
    <div class="p4-drawer-body" data-p4-tree-drawer></div>
</aside>

<script type="text/javascript" src="./modules/system/include/workspaces.js?v=<?php echo urlencode(_PLOOPI_VERSION); ?>"></script>
