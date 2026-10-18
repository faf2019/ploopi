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
 * Paramètres des modules, gabarits de génération 4 : liste des modules à gauche,
 * paramètres du module choisi à droite.
 *
 * Un seul écran pour trois niveaux de la cascade des paramètres, selon
 * $strParamContext fourni par l'appelant :
 *  - 'system'    : valeurs par défaut (admin_system_param.php), tous les modules ;
 *  - 'workspace' : valeurs de l'espace $workspace (admin_index_param.php), ses modules ;
 *  - 'user'      : valeurs de l'utilisateur (public_module_param.php), paramètres publics
 *                  des modules des espaces qu'il administre.
 *
 * Les enregistrements restent ceux des écrans historiques (op=save, op=paramsave).
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
 * Modules proposés : identifiant => array(nom, type)
 */
$arrParamModules = array();

switch($strParamContext)
{
    case 'workspace':
        foreach($workspace->getmodules() as $arrModule) $arrParamModules[$arrModule['instanceid']] = array($arrModule['instancename'], $arrModule['label']);
        $strParamSave = 'save';
        $strParamNav = 'admin.php?wspToolbarItem=tabParams&idmodule=';
    break;

    case 'user':
        $arrIds = array();
        foreach($_SESSION['ploopi']['workspaces'] as $arrWorkspace)
        {
            if (!empty($arrWorkspace['adminlevel']) && !empty($arrWorkspace['modules'])) $arrIds = array_merge($arrIds, $arrWorkspace['modules']);
        }
        foreach(array_unique($arrIds) as $intId)
        {
            if (isset($_SESSION['ploopi']['modules'][$intId])) $arrParamModules[$intId] = array($_SESSION['ploopi']['modules'][$intId]['label'], $_SESSION['ploopi']['modules'][$intId]['moduletype']);
        }
        $strParamSave = 'paramsave';
        $strParamNav = 'admin.php?op=param&idmodule=';
    break;

    case 'system':
    default:
        foreach($_SESSION['ploopi']['modules'] as $intId => $arrModule)
        {
            if ($intId > 0) $arrParamModules[$intId] = array($arrModule['label'], $arrModule['moduletype']);
        }
        $strParamSave = 'save';
        $strParamNav = 'admin.php?sysToolbarItem=params&idmodule=';
    break;
}

uasort($arrParamModules, function($a, $b) { return strcasecmp($a[0], $b[0]); });

// Module choisi : celui demandé s'il est proposé, le premier sinon
$intParamModule = 0;
if (!empty($_REQUEST['idmodule']) && is_numeric($_REQUEST['idmodule']) && isset($arrParamModules[$_REQUEST['idmodule']])) $intParamModule = (int) $_REQUEST['idmodule'];
elseif (!empty($arrParamModules)) $intParamModule = (int) key($arrParamModules);

$arrParams = array();
$arrDescriptions = array();

if ($intParamModule)
{
    $objParam = new ploopi\param();
    switch($strParamContext)
    {
        case 'workspace': $objParam->open($intParamModule, $workspaceid); break;
        case 'user': $objParam->open($intParamModule, 0, $_SESSION['ploopi']['userid'], 1); break;
        default: $objParam->open($intParamModule); break;
    }

    $arrParams = $objParam->getvalues();
    if (!is_array($arrParams)) $arrParams = array();

    // Explications des paramètres (description.xml du module)
    if (!empty($arrParams))
    {
        $arrFirst = current($arrParams);
        $rs = $db->query("SELECT name, description FROM ploopi_param_type WHERE id_module_type = ".((int) $arrFirst['id_module_type']));
        while ($row = $db->fetchrow($rs)) $arrDescriptions[$row['name']] = $row['description'];
    }
}

if (empty($arrParamModules))
{
    echo ui::card_open();
    echo ui::emptystate('adjustments-horizontal', _SYSTEM_PARAMF_NOMODULE);
    echo ui::card_close();
    return;
}
?>
<div class="p4-aside-layout p4-ui">
    <?php echo ui::card_open(_SYSTEM_PARAMF_MODULES, '', array(), array('tag' => 'nav', 'class' => 'p4-navcard', 'body' => false)); ?>
        <ul class="p4-navlist">
            <?php
            foreach($arrParamModules as $intId => $arrModule)
            {
                ?>
                <li><a href="<?php echo ploopi\crypt::urlencode($strParamNav.$intId); ?>"<?php if ($intId == $intParamModule) echo ' aria-current="page"'; ?>><?php echo ui::icon($objSkin->moduleicon($arrModule[1])); ?><span><?php echo ui::e($arrModule[0]); ?><small><?php echo ui::e($arrModule[1]); ?></small></span></a></li>
                <?php
            }
            ?>
        </ul>
    <?php echo ui::card_close('', array('tag' => 'nav', 'body' => false)); ?>

    <div class="p4-stack">
        <div class="p4-navlist-select">
            <?php
            $arrNavChoices = array();
            foreach($arrParamModules as $intId => $arrModule) $arrNavChoices[ploopi\crypt::urlencode($strParamNav.$intId)] = "{$arrModule[0]} ({$arrModule[1]})";
            echo ui::select('p4_param_module', $arrNavChoices, ploopi\crypt::urlencode($strParamNav.$intParamModule), array('data-p4-nav' => true, 'aria-label' => _SYSTEM_PARAMF_MODULES));
            ?>
        </div>
        <?php
        $arrCurrent = $arrParamModules[$intParamModule];
        echo ui::card_open($arrCurrent[0], $objSkin->moduleicon($arrCurrent[1]), array(), array('subtitle' => $arrCurrent[1].' · '.($strParamContext == 'workspace' ? _SYSTEM_PARAMF_LEVEL_WORKSPACE : ($strParamContext == 'user' ? _SYSTEM_PARAMF_LEVEL_USER : _SYSTEM_PARAMF_LEVEL_DEFAULT))));

        if (empty($arrParams)) echo ui::emptystate('adjustments-horizontal', _SYSTEM_LABEL_NOMODULEPARAM);
        else
        {
            ?>
            <form class="p4-form" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
                <input type="hidden" name="op" value="<?php echo ui::e($strParamSave); ?>" />
                <input type="hidden" name="idmodule" value="<?php echo $intParamModule; ?>" />
                <div class="p4-fgrid">
                    <?php
                    foreach($arrParams as $strName => $arrParam)
                    {
                        $strId = 'p4_param_'.preg_replace('/[^a-z0-9_]/i', '_', $strName);
                        $strValue = (string) $arrParam['value'];
                        $strHint = empty($arrDescriptions[$strName]) ? '' : ui::e(strip_tags($arrDescriptions[$strName]));

                        if (!empty($arrParam['choices']))
                        {
                            $strControl = ui::select($strName, $arrParam['choices'], $strValue, array('id' => $strId));
                            $strWidth = 'c6';
                        }
                        elseif (strlen($strValue) > 200 || strpos($strValue, "\n") !== false)
                        {
                            $strControl = ui::textarea($strName, $strValue, array('id' => $strId, 'rows' => 5));
                            $strWidth = 'c12';
                        }
                        else
                        {
                            $strControl = ui::input($strName, $strValue, array('id' => $strId));
                            $strWidth = 'c6';
                        }

                        echo ui::field($arrParam['label'] != '' ? $arrParam['label'] : $strName, $strControl, array('for' => $strId, 'width' => $strWidth, 'hint' => $strHint));
                    }
                    ?>
                </div>
                <?php
                echo ui::formactions(array(array('label' => _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')), ui::e(str_replace('<NB>', sizeof($arrParams), _SYSTEM_PARAMF_COUNT)));
                ?>
            </form>
            <?php
        }

        echo ui::card_close();
        ?>
    </div>
</div>
<?php
echo ui::script();
