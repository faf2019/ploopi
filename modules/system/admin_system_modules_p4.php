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
 * Système › Modules, gabarits de génération 4 : mises à jour disponibles, modules
 * installés, paquets à installer (dossier install/).
 *
 * Remplace admin_system_installmodules.php sous ploopi4 ; mêmes opérations
 * (op=install, op=update, op=uninstall, ploopi_op=updatedesc, ploopi_op=updatemb).
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

/**
 * Paquets du dossier install/ (description.xml)
 */
$arrPackages = array();

if ($resDir = @opendir('./install/'))
{
    while (($strFile = readdir($resDir)) !== false)
    {
        if ($strFile[0] == '.' || !is_dir("./install/{$strFile}") || !file_exists("./install/{$strFile}/description.xml")) continue;

        $objXml = new ploopi\xml2array();
        $arrXml = $objXml->parseFile("./install/{$strFile}/description.xml");

        if ($arrXml)
        {
            $arrInfo = &$arrXml['root']['ploopi'][0]['moduletype'][0];
            $arrPackages[$arrInfo['label'][0]] = array(
                'label' => $arrInfo['label'][0],
                'version' => $arrInfo['version'][0],
                'date' => $arrInfo['date'][0],
                'author' => $arrInfo['author'][0],
                'description' => $arrInfo['description'][0]
            );
        }
        else $arrPackages[$strFile] = array('label' => $strFile, 'version' => '', 'date' => '', 'author' => '', 'description' => '', 'error' => true);
    }
    closedir($resDir);
}
ksort($arrPackages);

/**
 * Modules installés, avec leurs actions, métabase, objets WebEdit et instances
 */
$arrCounts = array();
foreach(array('actions' => 'ploopi_mb_action', 'mb' => 'ploopi_mb_table', 'wce' => 'ploopi_mb_wce_object', 'instances' => 'ploopi_module') as $strKey => $strTable)
{
    $rs = $db->query("SELECT id_module_type AS id, COUNT(*) AS nb FROM {$strTable} GROUP BY id_module_type");
    while ($row = $db->fetchrow($rs)) $arrCounts[$strKey][$row['id']] = (int) $row['nb'];
}

$arrInstalled = array();
$rs = $db->query('SELECT * FROM ploopi_module_type ORDER BY label');
while ($row = $db->fetchrow($rs)) $arrInstalled[$row['label']] = $row;

/**
 * Comparaison de versions tolérante aux suffixes alphabétiques (cf. écran historique)
 */
$funcVersion = function($strVersion)
{
    $arrParts = explode('.', preg_replace('@([0-9]+)([a-zA-Z]+)@', '$1.$2', $strVersion));
    foreach($arrParts as $intK => $strPart) if (!is_numeric($strPart)) $arrParts[$intK] = ord($strPart);
    return implode('.', $arrParts);
};

$arrUpdates = array();
$arrNew = array();
foreach($arrPackages as $strLabel => $arrPackage)
{
    if (isset($arrInstalled[$strLabel]))
    {
        if (empty($arrPackage['error']) && version_compare($funcVersion($arrPackage['version']), $funcVersion($arrInstalled[$strLabel]['version'])) > 0) $arrUpdates[$strLabel] = $arrPackage;
    }
    else $arrNew[$strLabel] = $arrPackage;
}

echo ui::pagehead(_SYSTEM_MENU_INSTALLMODULES, _SYSTEM_SYSMOD_SUBTITLE);

// Installation vierge : site de démonstration en un clic (ploopi\system\demo)
if (ploopi\system\demo::isavailable())
{
    echo ui::card_open(_SYSTEM_SYSMOD_DEMO, 'world', array(), array('subtitle' => _SYSTEM_SYSMOD_DEMO_DESC));
    echo '<div class="p4-row p4-ui">'.ui::button(array('label' => _SYSTEM_SYSMOD_DEMO_BUTTON, 'icon' => 'player-play', 'class' => 'primary', 'confirm' => _SYSTEM_SYSMOD_DEMO_CONFIRM, 'href' => ploopi\crypt::urlencode('admin.php?op=demo'))).'</div>';
    echo ui::card_close();
}

// Mises à jour disponibles
if (!empty($arrUpdates))
{
    echo ui::card_open(_SYSTEM_SYSMOD_UPDATES, 'refresh', array(), array('class' => 'p4-updates-card', 'subtitle' => _SYSTEM_SYSMOD_UPDATES_DESC));
    ?>
    <ul class="p4-assign-list">
        <?php
        foreach($arrUpdates as $strLabel => $arrPackage)
        {
            $arrDate = ploopi\date::timestamp2local($arrPackage['date']);
            ?>
            <li>
                <span class="p4-assign-icon"><?php echo ui::icon($objSkin->moduleicon($strLabel)); ?></span>
                <span class="p4-grow"><b><?php echo ui::e($strLabel); ?></b><small><?php echo ui::e("{$arrInstalled[$strLabel]['version']} → {$arrPackage['version']} · {$arrDate['date']} · {$arrPackage['description']}"); ?></small></span>
                <?php echo ui::button(array('label' => _SYSTEM_LABEL_UPDATE, 'icon' => 'refresh', 'class' => 'primary sm', 'href' => ploopi\crypt::urlencode("admin.php?op=update&idmoduletype={$arrInstalled[$strLabel]['id']}&updatefrom={$arrInstalled[$strLabel]['version']}&updateto={$arrPackage['version']}"))); ?>
            </li>
            <?php
        }
        ?>
    </ul>
    <?php
    echo ui::card_close();
}

// Modules installés
echo ui::card_open(_SYSTEM_SYSMOD_INSTALLED, 'package', array(), array('class' => 'p4-card-flush', 'subtitle' => str_replace('<NB>', sizeof($arrInstalled), _SYSTEM_SYSMOD_INSTALLED_COUNT)));
?>
<div data-p4-table>
    <div class="p4-table-tools p4-card-tools">
        <div class="p4-input-icon p4-list-search">
            <?php echo ui::icon('search', 'p4-icon-sm'); ?>
            <input class="p4-input" type="search" data-p4-table-filter placeholder="<?php echo ui::e(_SYSTEM_SYSMOD_FILTER); ?>" aria-label="<?php echo ui::e(_SYSTEM_SYSMOD_FILTER); ?>" />
        </div>
    </div>
    <div class="p4-table-wrap">
        <table class="p4-table p4-sysmod-table">
            <thead>
                <tr>
                    <th aria-sort="ascending"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_SYSMOD_MODULE).ui::icon('arrows-sort'); ?></button></th>
                    <th><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_LABEL_VERSION).ui::icon('arrows-sort'); ?></button></th>
                    <th class="p4-col-author"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_LABEL_AUTHOR).ui::icon('arrows-sort'); ?></button></th>
                    <th class="p4-center"><button type="button" class="p4-sort" data-p4-col><?php echo ui::e(_SYSTEM_SYSMOD_INSTANCES).ui::icon('arrows-sort'); ?></button></th>
                    <th class="p4-col-caps"><?php echo ui::e(_SYSTEM_SYSMOD_CAPS); ?></th>
                    <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_ACTIONS); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($arrInstalled as $strLabel => $arrModule)
                {
                    $intId = (int) $arrModule['id'];
                    $arrDate = ploopi\date::timestamp2local($arrModule['date']);
                    $booSystem = $intId == _PLOOPI_MODULE_SYSTEM;

                    $strCaps = '';
                    foreach(array('actions' => _SYSTEM_LABEL_ACTIONS, 'mb' => _SYSTEM_LABEL_METABASE, 'wce' => _SYSTEM_SYSMOD_WCE) as $strKey => $strCap)
                    {
                        if (!empty($arrCounts[$strKey][$intId])) $strCaps .= ui::badge($strCap, 'info').' ';
                    }

                    $arrItems = array(
                        array('label' => _SYSTEM_SYSMOD_RELOADDESC, 'desc' => _SYSTEM_SYSMOD_RELOADDESC_DESC, 'icon' => 'refresh', 'href' => ploopi\crypt::urlencode("admin.php?ploopi_op=updatedesc&moduletype={$strLabel}&idmoduletype={$intId}"), 'confirm' => _SYSTEM_SYSMOD_RELOADDESC_CONFIRM),
                        array('label' => _SYSTEM_SYSMOD_RELOADMB, 'desc' => _SYSTEM_SYSMOD_RELOADMB_DESC, 'icon' => 'database', 'href' => ploopi\crypt::urlencode("admin.php?ploopi_op=updatemb&moduletype={$strLabel}&idmoduletype={$intId}"), 'confirm' => _SYSTEM_SYSMOD_RELOADMB_CONFIRM)
                    );
                    if (!$booSystem)
                    {
                        $arrItems[] = '-';
                        $arrItems[] = array('label' => _SYSTEM_LABEL_UNINSTALL, 'desc' => _SYSTEM_SYSMOD_UNINSTALL_DESC, 'icon' => 'trash', 'class' => 'p4-danger', 'href' => ploopi\crypt::urlencode("admin.php?op=uninstall&uninstallidmoduletype={$intId}"), 'confirm' => _SYSTEM_SYSMOD_UNINSTALL_CONFIRM);
                    }
                    ?>
                    <tr<?php if ($booSystem) echo ' class="p4-row-muted"'; ?>>
                        <td data-p4-sort-value="<?php echo ui::e($strLabel); ?>">
                            <div class="p4-cell-main">
                                <span class="p4-tile-icon p4-tile-icon-sm"><?php echo ui::icon($objSkin->moduleicon($strLabel)); ?></span>
                                <span><b><?php echo ui::e($strLabel); ?></b><small><?php echo ui::e($arrModule['description']); ?></small></span>
                            </div>
                        </td>
                        <td data-p4-sort-value="<?php echo ui::e($funcVersion($arrModule['version'])); ?>" data-label="<?php echo ui::e(_SYSTEM_LABEL_VERSION); ?>"><span class="p4-mono"><?php echo ui::e($arrModule['version']); ?></span><small class="p4-sub"><?php echo ui::e($arrDate['date']); ?></small><?php if (isset($arrUpdates[$strLabel])) echo ' '.ui::badge(str_replace('<VERSION>', $arrUpdates[$strLabel]['version'], _SYSTEM_SYSMOD_AVAILABLE), 'warning'); ?></td>
                        <td class="p4-col-author p4-text-sm" data-label="<?php echo ui::e(_SYSTEM_LABEL_AUTHOR); ?>"><?php echo ui::e($arrModule['author']); ?></td>
                        <td class="p4-center" data-p4-sort-value="<?php echo isset($arrCounts['instances'][$intId]) ? $arrCounts['instances'][$intId] : 0; ?>" data-label="<?php echo ui::e(_SYSTEM_SYSMOD_INSTANCES); ?>"><span class="p4-count p4-count-muted"><?php echo isset($arrCounts['instances'][$intId]) ? $arrCounts['instances'][$intId] : 0; ?></span></td>
                        <td class="p4-col-caps"><div class="p4-chips"><?php echo $strCaps; ?></div></td>
                        <td class="p4-col-acts"><?php echo ui::menu("p4-sysmod-{$intId}", array('label' => _SYSTEM_USERS_ACTIONS, 'icon' => 'dots-vertical', 'class' => 'ghost icon sm'), $arrItems); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <div data-p4-table-empty hidden><?php echo ui::emptystate('search', _SYSTEM_SYSMOD_NOMATCH); ?></div>
</div>
<?php
echo ui::card_close();

// Paquets à installer
echo ui::card_open(_SYSTEM_SYSMOD_NEW, 'download', array(), array('subtitle' => empty($arrNew) ? '' : _SYSTEM_SYSMOD_NEW_DESC));

if (empty($arrNew)) echo ui::emptystate('package', _SYSTEM_SYSMOD_NEW_NONE);
else
{
    ?>
    <div class="p4-tiles">
        <?php
        foreach($arrNew as $strLabel => $arrPackage)
        {
            $arrDate = ploopi\date::timestamp2local($arrPackage['date']);
            ?>
            <div class="p4-tile<?php if (!empty($arrPackage['error'])) echo ' p4-tile-muted'; ?>">
                <div class="p4-tile-head">
                    <span class="p4-tile-icon"><?php echo ui::icon(empty($arrPackage['error']) ? $objSkin->moduleicon($strLabel) : 'alert-triangle'); ?></span>
                    <div class="p4-grow">
                        <h3><?php echo ui::e($strLabel); ?></h3>
                        <p class="p4-text-xs p4-muted"><?php echo empty($arrPackage['error']) ? ui::e(trim("{$arrPackage['version']} · {$arrDate['date']} · {$arrPackage['author']}", ' ·')) : ui::e(_SYSTEM_SYSMOD_XMLERROR); ?></p>
                    </div>
                </div>
                <p><?php echo ui::e($arrPackage['description']); ?></p>
                <div class="p4-tile-foot">
                    <span class="p4-grow"></span>
                    <?php if (empty($arrPackage['error'])) echo ui::button(array('label' => _SYSTEM_LABEL_INSTALL, 'icon' => 'download', 'class' => 'soft sm', 'href' => ploopi\crypt::urlencode("admin.php?op=install&installmoduletype={$strLabel}"))); ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

echo ui::card_close();
echo ui::script();
