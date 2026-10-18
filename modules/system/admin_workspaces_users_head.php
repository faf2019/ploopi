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
 * Onglet « Utilisateurs » d'un espace ou d'un groupe (gabarits de génération 4) :
 * remplace les onglets de second niveau. La liste porte elle-même les actions
 * (ajouter, rattacher, importer) ; dans un espace, un sélecteur passe des
 * utilisateurs aux groupes rattachés ; les sous-écrans ont un lien de retour.
 *
 * Variables fournies par admin_index_users.php : $tabs, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$objSkin = ploopi\skin::get();

if (!isset($tabs[$_SESSION['system']['usrTabItem']])) $_SESSION['system']['usrTabItem'] = 'tabUserList';

$strUsrTab = $_SESSION['system']['usrTabItem'];
$booGroupsFamily = in_array($strUsrTab, array('tabGroupList', 'tabGroupAttach'));
$strListTab = $booGroupsFamily ? 'tabGroupList' : 'tabUserList';
$booSubScreen = $strUsrTab != $strListTab || $op != '';

// Titres des sous-écrans (ceux du menu Ajouter)
$arrSubTitles = array(
    'tabUserAdd' => _SYSTEM_USERS_ADD_NEW,
    'tabUserAttach' => _SYSTEM_USERS_ADD_ATTACH,
    'tabUserImport' => _SYSTEM_USERS_ADD_IMPORT,
    'tabGroupAttach' => _SYSTEM_USERS_ADD_GROUP
);
?>
<div class="p4-users-head p4-ui">
    <?php
    if (isset($tabs['tabGroupList']))
    {
        ?>
        <div class="p4-seg" role="group" aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_TAB_USERS); ?>">
            <a href="<?php echo ploopi\crypt::urlencode($tabs['tabUserList']['url']); ?>"<?php if (!$booGroupsFamily) echo ' class="p4-active" aria-current="page"'; ?>><?php echo $objSkin->icon('users', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_TAB_USERS); ?></a>
            <a href="<?php echo ploopi\crypt::urlencode($tabs['tabGroupList']['url']); ?>"<?php if ($booGroupsFamily) echo ' class="p4-active" aria-current="page"'; ?>><?php echo $objSkin->icon('users-group', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_TAB_GROUPS); ?></a>
        </div>
        <?php
    }

    if ($booSubScreen)
    {
        ?>
        <a class="p4-btn p4-btn-ghost p4-btn-sm" href="<?php echo ploopi\crypt::urlencode($tabs[$strListTab]['url']); ?>"><?php echo $objSkin->icon('chevron-left', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_BACKTOLIST); ?></a>
        <?php
        if ($strUsrTab != $strListTab)
        {
            ?>
            <h3 class="p4-users-subtitle"><?php echo ploopi\str::htmlentities(isset($arrSubTitles[$strUsrTab]) ? $arrSubTitles[$strUsrTab] : strip_tags($tabs[$strUsrTab]['title'])); ?></h3>
            <?php
        }
    }
    ?>
</div>
