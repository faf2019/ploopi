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
 * Onglet « Espace » des gabarits de génération 4 : propriétés de l'espace de travail
 * choisi, ou création d'un sous-espace ($op == 'child').
 *
 * Remplace admin_index_workspace.php et admin_index_workspace_add.php sous ploopi4.
 * L'enregistrement reste celui de admin_index.php (op=save_workspace), avec les mêmes
 * noms de champs.
 *
 * Variables fournies par admin_index.php : $workspace, $workspaceid, $workspaces, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$booNew = $op == 'child';
$booAdmin = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN;

// Créer un sous-espace est réservé aux administrateurs d'espace (l'enregistrement l'exige aussi)
if ($booNew && !$booAdmin) ploopi\output::redirect('admin.php');

$arrFields = $workspace->fields;

if ($booNew)
{
    // Le sous-espace hérite de l'accès et des informations de référencement de son parent
    $arrFields['label'] = '';
    $arrFields['code'] = '';
    $arrFields['priority'] = pow(10, max(0, $workspace->fields['depth'] - 1));
}

$arrTemplates = array();
foreach(ploopi\system::getavailabletemplates('backoffice') as $strTemplate) $arrTemplates[$strTemplate] = $strTemplate;

$strAction = $booNew ? "admin.php?op=save_workspace&workspace_id_workspace={$workspace->fields['id']}" : "admin.php?op=save_workspace&workspace_id={$workspace->fields['id']}";

/**
 * Champ texte modifiable, ou valeur en lecture seule pour un gestionnaire
 */
$funcText = function($strLabel, $strName, $arrOptions = array(), $arrAttrs = array()) use ($booAdmin, $arrFields)
{
    $strValue = isset($arrFields[$strName]) ? $arrFields[$strName] : '';
    $booMulti = !empty($arrOptions['multiline']);
    unset($arrOptions['multiline']);

    if (!$booAdmin) return ui::field($strLabel, ui::readonly($strValue, $booMulti), $arrOptions);

    $arrOptions['for'] = "workspace_{$strName}";
    $strControl = $booMulti ? ui::textarea("workspace_{$strName}", $strValue, $arrAttrs) : ui::input("workspace_{$strName}", $strValue, $arrAttrs);

    return ui::field($strLabel, $strControl, $arrOptions);
};

$funcToggle = function($strLabel, $strName, $strHint) use ($booAdmin, $arrFields)
{
    if (!$booAdmin) return ui::field($strLabel, ui::readonly(empty($arrFields[$strName]) ? _PLOOPI_NO : _PLOOPI_YES), array('width' => 'c12'));

    return '<div class="p4-c12">'.ui::toggle("workspace_{$strName}", !empty($arrFields[$strName]), $strLabel, ui::e($strHint)).'</div>';
};

if ($booNew)
{
    ?>
    <div class="p4-users-head p4-ui">
        <a class="p4-back" href="<?php echo ploopi\crypt::urlencode('admin.php?wspToolbarItem=tabWorkspaces'); ?>"><?php echo ui::icon('arrow-left', 'p4-icon-sm'); ?><?php echo ui::e($workspace->fields['label']); ?></a>
        <h3 class="p4-users-subtitle p4-grow"><?php echo ui::e(_SYSTEM_WSF_NEW_TITLE); ?></h3>
    </div>
    <?php
}
else
{
    $intNbWorkspaces = !empty($workspaces['tree'][$workspaceid]) ? sizeof($workspaces['tree'][$workspaceid]) : 0;
    $intNbGroups = sizeof($workspace->getgroups());
    $intNbUsers = sizeof($workspace->getusers());

    echo ui::stats(array(
        array(_SYSTEM_WSF_STAT_WORKSPACES, $intNbWorkspaces, 'building'),
        array(_SYSTEM_WSF_STAT_GROUPS, $intNbGroups, 'users-group'),
        array(_SYSTEM_WSF_STAT_USERS, $intNbUsers, 'users')
    ));
}

if ($booAdmin)
{
    ?>
    <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode($strAction); ?>" method="post" data-p4-validate>
    <?php
}
else
{
    ?>
    <div class="p4-form p4-ui">
    <?php
}

echo ui::section_open(_SYSTEM_WSF_SEC_IDENTITY, _SYSTEM_WSF_SEC_IDENTITY_DESC, 'id');
echo $funcText(_SYSTEM_LABEL_GROUP_NAME, 'label', array('width' => 'c12', 'required' => true), array('check' => 'string', 'required' => true, 'maxlength' => 255, 'autofocus' => $booNew, 'placeholder' => $booNew ? _SYSTEM_WSF_NEW_PLACEHOLDER : null));
echo $funcText(_SYSTEM_LABEL_GROUP_CODE, 'code', array('hint' => ui::e(_SYSTEM_WSF_CODE_HINT)), array('maxlength' => 255));
echo $funcText(_SYSTEM_LABEL_GROUP_PRIORITY, 'priority', array('hint' => ui::e(_SYSTEM_WSF_PRIORITY_HINT)), array('type' => 'number', 'check' => 'emptyint'));
echo ui::section_close();

echo ui::section_open(_SYSTEM_LABEL_ACCESS, _SYSTEM_WSF_SEC_ACCESS_DESC, 'key');
echo $funcToggle(_SYSTEM_LABEL_GROUP_ADMIN, 'backoffice', _SYSTEM_WSF_BACKOFFICE_HINT);
if ($booAdmin) echo ui::field(_SYSTEM_LABEL_GROUP_SKIN, ui::select('workspace_template', $arrTemplates, $arrFields['template'], array('empty' => _PLOOPI_NONE)), array('for' => 'workspace_template', 'hint' => ui::e(_SYSTEM_WSF_SKIN_HINT)));
else echo ui::field(_SYSTEM_LABEL_GROUP_SKIN, ui::readonly($arrFields['template']));
echo $funcText(_SYSTEM_LABEL_GROUP_ADMINDOMAINLIST, 'backoffice_domainlist', array('width' => 'c12', 'multiline' => true, 'hint' => ui::e(_SYSTEM_WSF_DOMAINS_HINT)), array('rows' => 3));
echo $funcToggle(_SYSTEM_LABEL_GROUP_WEB, 'frontoffice', _SYSTEM_WSF_FRONTOFFICE_HINT);
echo $funcText(_SYSTEM_LABEL_GROUP_WEBDOMAINLIST, 'frontoffice_domainlist', array('width' => 'c12', 'multiline' => true, 'hint' => ui::e(_SYSTEM_WSF_DOMAINS_HINT)), array('rows' => 3));

if (!$booNew && !empty($arrFields['frontoffice']))
{
    // Le frontoffice d'un espace est rendu par une instance de webedit qui lui est rattachée
    $booWebedit = false;
    foreach($workspace->getmodules() as $arrModule) if ($arrModule['label'] == 'webedit') $booWebedit = true;

    if (!$booWebedit) echo '<div class="p4-c12">'.ui::callout('warning', ui::e(_SYSTEM_WSF_WEBEDIT_WARNING)).'</div>';
}
echo ui::section_close();

echo ui::section_open(_SYSTEM_WSF_SEC_META, _SYSTEM_WSF_SEC_META_DESC, 'world-www');
echo $funcText(_SYSTEM_WSF_META_TITLE, 'title', array('width' => 'c12'));
echo $funcText(_SYSTEM_WSF_META_DESCRIPTION, 'meta_description', array('width' => 'c12'));
echo $funcText(_SYSTEM_WSF_META_KEYWORDS, 'meta_keywords', array('width' => 'c12'));
echo $funcText(_SYSTEM_WSF_META_AUTHOR, 'meta_author');
echo $funcText(_SYSTEM_WSF_META_COPYRIGHT, 'meta_copyright');
echo $funcText(_SYSTEM_WSF_META_ROBOTS, 'meta_robots', array('hint' => ui::e(_SYSTEM_WSF_ROBOTS_HINT)));
echo ui::section_close();

echo ui::section_open(_SYSTEM_LABEL_FILTERING, _SYSTEM_WSF_SEC_FILTERING_DESC, 'shield-lock');
echo $funcText(_SYSTEM_LABEL_GROUP_ALLOWEDIP, 'iprules', array('width' => 'c12', 'hint' => ui::e(_SYSTEM_WSF_IP_HINT)));
echo ui::section_close();

echo ui::section_open(_SYSTEM_WSF_SEC_MAIL, _SYSTEM_WSF_SEC_MAIL_DESC, 'mail');
if (empty(_PLOOPI_ADMINMAIL)) echo '<div class="p4-c12">'.ui::callout('warning', ui::e(_SYSTEM_WSF_ADMINMAIL_WARNING)).'</div>';

if ($booAdmin)
{
    $strTags = '';
    foreach(array('firstname', 'lastname', 'email', 'login', 'password', 'date', 'time', 'url') as $strKey)
    {
        $strTags .= '<button type="button" class="p4-tag" data-p4-insert="{'.$strKey.'}" data-p4-target="workspace_mail_model" title="'.ui::e(_SYSTEM_WSF_MAIL_INSERT).'">{'.$strKey.'}</button>';
    }

    echo ui::field(_SYSTEM_WSF_MAIL_MODEL, ui::textarea('workspace_mail_model', $arrFields['mail_model'], array('rows' => 8)), array('width' => 'c12', 'for' => 'workspace_mail_model', 'hint' => ui::e(_SYSTEM_WSF_MAIL_TAGS).'<span class="p4-taglist">'.$strTags.'</span>'));
}
else echo ui::field(_SYSTEM_WSF_MAIL_MODEL, ui::readonly($arrFields['mail_model'], true), array('width' => 'c12'));
echo ui::section_close();

if ($booNew)
{
    // Modules du nouvel espace : partagés par les espaces parents (cochés s'ils sont hérités), ou nouvelles instances
    $objChild = new ploopi\workspace();
    $objChild->fields['parents'] = $workspace->fields['parents'].';'.$workspace->fields['id'];
    $arrShared = $objChild->getsharedmodules(false);
    $arrHerited = $objChild->getsharedmodules(true);

    echo ui::section_open(_SYSTEM_LABEL_USEDMODULES, _SYSTEM_WSF_SEC_MODULES_DESC, 'puzzle');
    ?>
    <div class="p4-c12 p4-table-wrap">
        <table class="p4-table">
            <thead>
                <tr>
                    <th class="p4-chk"><span class="p4-sr-only"><?php echo ui::e(_SYSTEM_USERS_SELECT); ?></span></th>
                    <th><?php echo ui::e(_SYSTEM_LABEL_MODULENAME); ?></th>
                    <th><?php echo ui::e(_SYSTEM_LABEL_DESCRIPTION); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($arrShared as $intInstanceId => $arrInstance)
                {
                    ?>
                    <tr>
                        <td class="p4-chk"><input type="checkbox" name="heritedmodule[]" id="p4-hm-s<?php echo (int) $intInstanceId; ?>" value="SHARED,<?php echo (int) $intInstanceId; ?>"<?php if (isset($arrHerited[$intInstanceId])) echo ' checked="checked"'; ?> /></td>
                        <td><label class="p4-cell-main" for="p4-hm-s<?php echo (int) $intInstanceId; ?>"><?php echo ui::icon(ploopi\skin::get()->moduleicon($arrInstance['moduletype'])); ?><span><b><?php echo ui::e($arrInstance['label']); ?></b><small><?php echo ui::e(_SYSTEM_WSF_MODULE_SHARED.' · '.$arrInstance['moduletype']); ?></small></span></label></td>
                        <td class="p4-muted"><?php echo ui::e($arrInstance['description']); ?></td>
                    </tr>
                    <?php
                }

                foreach(system_getinstalledmodules() as $arrModuleType)
                {
                    ?>
                    <tr>
                        <td class="p4-chk"><input type="checkbox" name="heritedmodule[]" id="p4-hm-n<?php echo (int) $arrModuleType['id']; ?>" value="NEW,<?php echo (int) $arrModuleType['id']; ?>" /></td>
                        <td><label class="p4-cell-main" for="p4-hm-n<?php echo (int) $arrModuleType['id']; ?>"><?php echo ui::icon(ploopi\skin::get()->moduleicon($arrModuleType['label'])); ?><span><b><?php echo ui::e($arrModuleType['label']); ?></b><small><?php echo ui::e(_SYSTEM_WSF_MODULE_NEW); ?></small></span></label></td>
                        <td class="p4-muted"><?php echo ui::e($arrModuleType['description']); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    echo ui::section_close();
}

if ($booAdmin)
{
    echo ui::formactions(array(
        $booNew ? array('label' => _PLOOPI_CANCEL, 'href' => ploopi\crypt::urlencode('admin.php?wspToolbarItem=tabWorkspaces'), 'class' => 'ghost') : '',
        array('label' => $booNew ? _SYSTEM_WSF_CREATE : _PLOOPI_SAVE, 'icon' => 'check', 'type' => 'submit', 'class' => 'primary')
    ));
    ?>
    </form>
    <?php
}
else
{
    ?>
    </div>
    <?php
}

if (!$booNew)
{
    if ($booAdmin)
    {
        // Cloner, supprimer : mêmes règles que la barre d'outils historique
        $intNbChildren = sizeof($workspace->getchildren());

        if ($_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN && $_SESSION['ploopi']['workspaceid'] == $workspaceid)
        {
            $arrClone = array('label' => _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE, 'icon' => 'copy', 'disabled' => true);
            $strCloneHint = _SYSTEM_WSF_CLONE_DENIED;
        }
        else
        {
            $arrClone = array('label' => _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE, 'icon' => 'copy', 'href' => ploopi\crypt::urlencode("admin.php?op=clone&workspaceid={$workspaceid}"), 'confirm' => _SYSTEM_WSF_CLONE_CONFIRM);
            $strCloneHint = _SYSTEM_WSF_CLONE_HINT;
        }

        if ($workspace->fields['protected']) $strDeleteHint = _SYSTEM_WSF_DELETE_PROTECTED;
        elseif ($intNbChildren) $strDeleteHint = _SYSTEM_WSF_DELETE_CHILDREN;
        elseif ($intNbUsers) $strDeleteHint = _SYSTEM_WSF_DELETE_USERS;
        else $strDeleteHint = '';

        $arrDelete = $strDeleteHint == '' ?
            array('label' => _SYSTEM_LABEL_DELETE_WORKSPACE, 'icon' => 'trash', 'class' => 'danger-ghost', 'href' => ploopi\crypt::urlencode("admin.php?op=delete&workspaceid={$workspaceid}"), 'confirm' => _SYSTEM_WSF_DELETE_CONFIRM) :
            array('label' => _SYSTEM_LABEL_DELETE_WORKSPACE, 'icon' => 'trash', 'class' => 'danger-ghost', 'disabled' => true);

        echo ui::card_open(_SYSTEM_WSF_ACTIONS, 'alert-triangle', array(), array('class' => 'p4-danger-zone p4-mt-16'));
        ?>
        <div class="p4-danger-row"><div><strong><?php echo ui::e(_SYSTEM_WSF_CLONE_TITLE); ?></strong><span><?php echo ui::e($strCloneHint); ?></span></div><?php echo ui::button($arrClone); ?></div>
        <div class="p4-danger-row"><div><strong><?php echo ui::e(_SYSTEM_WSF_DELETE_TITLE); ?></strong><span><?php echo ui::e($strDeleteHint == '' ? _SYSTEM_WSF_DELETE_HINT : $strDeleteHint); ?></span></div><?php echo ui::button($arrDelete); ?></div>
        <?php
        echo ui::card_close();
    }

    echo ui::card_open(_SYSTEM_WSF_NOTES, 'notes', array(), array('class' => 'p4-mt-16'));
    echo '<div class="p4-legacy">';
    ploopi\annotation::display(_SYSTEM_OBJECT_WORKSPACE, $workspace->fields['id'], $workspace->fields['label']);
    echo '</div>';
    echo ui::card_close();
}

echo ui::script();
