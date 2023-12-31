<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Interface d'ajout d'un espace de travail
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Ouverture du bloc
 */

echo ploopi\skin::get()->open_simplebloc();
?>

<form name="" action="<?php echo ploopi\crypt::urlencode("admin.php?op=save_workspace&workspace_id_workspace={$workspace->fields['id']}"); ?>" method="post" onsubmit="javascript:return system_workspace_validate(this);">
<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?> &raquo;
    <?php
     echo _SYSTEM_LABEL_WORKSPACE_ADD;
    ?>
</div>
<div class="ploopi_form" style="clear:both;padding:2px">
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
        <input type="text" class="text" name="workspace_label"  value="fils de <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?>">
    </p>
    <?php
        $templatelist_back = ploopi\system::getavailabletemplates('backoffice');
        $templatelist_front = ploopi\system::getavailabletemplates('frontoffice');

        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
        {
            ?>
            <p>
                <label><?php echo _SYSTEM_LABEL_GROUP_CODE; ?>:</label>
                <input type="text" class="text" name="workspace_code"  value="<?php echo ploopi\str::htmlentities($workspace->fields['code']); ?>">
            </p>
            <p>
                <label><?php echo _SYSTEM_LABEL_GROUP_PRIORITY; ?>:</label>
                <input type="text" class="text" name="workspace_priority"  value="<?php echo ploopi\str::htmlentities(pow(10, $workspace->fields['depth']-1)); ?>">
            </p>
            <?php
        }
        ?>
</div>

<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?> &raquo; <?php echo _SYSTEM_LABEL_ACCESS; ?>
</div>

<div class="ploopi_form" style="clear:both;padding:2px">
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_ADMIN; ?>:</label>
        <input type="checkbox" name="workspace_backoffice" <?php if($workspace->fields['backoffice']) echo "checked"; ?> value="1">
    </p>
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_SKIN; ?>:</label>
        <select class="select" name="workspace_template">
            <option value=""><?php echo _PLOOPI_NONE; ?></option>
            <?php
            foreach($templatelist_back as $index => $tpl_name)
            {
                $sel = ($tpl_name == $workspace->fields['template']) ? 'selected' : '';
                echo "<option $sel>$tpl_name</option>";
            }
            ?>
        </select>
    </p>
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_ADMINDOMAINLIST; ?>:</label>
        <textarea class="text" name="workspace_backoffice_domainlist"><?php echo ploopi\str::htmlentities($workspace->fields['backoffice_domainlist']); ?></textarea>
    </p>
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_WEB; ?>:</label>
        <input type="checkbox" name="workspace_frontoffice" <?php if($workspace->fields['frontoffice']) echo "checked"; ?> value="1">
    </p>
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_WEBDOMAINLIST; ?>:</label>
        <textarea class="text" name="workspace_frontoffice_domainlist"><?php echo ploopi\str::htmlentities($workspace->fields['frontoffice_domainlist']); ?></textarea>
    </p>

</div>

<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?> &raquo; <?php echo _SYSTEM_LABEL_META; ?>
</div>
<div class="ploopi_form" id="system_meta" style="clear:both;padding:2px;">
    <p>
        <label>Titre:</label>
        <input type="text" class="text" name="workspace_title" value="<?php echo ploopi\str::htmlentities($workspace->fields['title']); ?>">
    </p>
    <p>
        <label>Description:</label>
        <input type="text" class="text" name="workspace_meta_description" value="<?php echo ploopi\str::htmlentities($workspace->fields['meta_description']); ?>">
    </p>
    <p>
        <label>Mots Clés:</label>
        <input type="text" class="text" name="workspace_meta_keywords" value="<?php echo ploopi\str::htmlentities($workspace->fields['meta_keywords']); ?>">
    </p>
    <p>
        <label>Auteur:</label>
        <input type="text" class="text" name="workspace_meta_author" value="<?php echo ploopi\str::htmlentities($workspace->fields['meta_author']); ?>">
    </p>
    <p>
        <label>Copyright:</label>
        <input type="text" class="text" name="workspace_meta_copyright" value="<?php echo ploopi\str::htmlentities($workspace->fields['meta_copyright']); ?>">
    </p>
    <p>
        <label>Robots:</label>
        <input type="text" class="text" name="workspace_meta_robots" value="<?php echo ploopi\str::htmlentities($workspace->fields['meta_robots']); ?>">
    </p>
</div>

<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?> &raquo; <?php echo _SYSTEM_LABEL_FILTERING; ?>
</div>
<div class="ploopi_form" id="system_filtering" style="clear:both;padding:2px;">
    <p>
        <label><?php echo _SYSTEM_LABEL_GROUP_ALLOWEDIP; ?>:</label>
        <input type="text" class="text" name="workspace_iprules"  value="<?php echo ploopi\str::htmlentities($workspace->fields['iprules']); ?>">
    </p>
</div>

<div class="ploopi_form_title">
    Modèle de courriel
</div>

<div class="ploopi_form" id="system_filtering" style="clear:both;padding:2px;">
    <p>
        <label>Modèle de courriel (création de compte utilisateur):
        <br /><em style="padding:4px;">Balises disponibles:</em>
        <br /><em style="padding:4px;"><? foreach(array('firstname', 'lastname', 'email', 'login', 'password', 'date', 'time', 'url') as $key) { ?><a class="mail_model_tag" href="javascript:void(0);" onclick="ploopi_insertatcursor($('workspace_mail_model'), '{<? echo $key; ?>}');">{<? echo $key; ?>}</a> <? } ?></em>

        </label>
        <?php
        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
        {
            ?>
            <textarea type="text" class="text" name="workspace_mail_model" id="workspace_mail_model"><?php echo $workspace->fields['mail_model']; ?></textarea>
            <?php
        }
        else echo '<span>'.ploopi\str::htmlentities($workspace->fields['iprules']).'</span>';
        ?>

    </p>
</div>

<div class="ploopi_form_title">
    <?php echo ploopi\str::htmlentities($workspace->fields['label']); ?> &raquo; <?php echo _SYSTEM_LABEL_USEDMODULES; ?>
</div>
<div class="ploopi_form">
    <?php
    $child = new ploopi\workspace();
    $child->fields['parents'] = $workspace->fields['parents'].';'.$workspace->fields['id'];
    $sharedmodules = $child->getsharedmodules(false);
    $heritedmodules = $child->getsharedmodules(true);
    $installedmodules = system_getinstalledmodules();

    $columns = array();
    $values = array();

    $columns['left']['check'] =
        array(
            'label' => '&nbsp;',
            'width' => 44,
            'options' => array('sort' => true)
        );

    $columns['left']['label'] =
        array(
            'label' => _SYSTEM_LABEL_MODULENAME,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $columns['left']['type'] =
        array(
            'label' => _SYSTEM_LABEL_MODULETYPE,
            'width' => 100,
            'options' => array('sort' => true)
        );

    $columns['auto']['description'] =
        array(
            'label' => _SYSTEM_LABEL_DESCRIPTION,
            'options' => array('sort' => true)
        );

      foreach ($sharedmodules AS $instanceid => $instance)
      {
        $values[]['values'] =
            array(
                'check' => array('label' => '<input type="checkbox" name="heritedmodule[]" value="SHARED,'.$instanceid.'" '.(isset($heritedmodules[$instanceid]) ? 'checked="checked"' : '').'>', 'sort_label' => isset($heritedmodules[$instanceid]) ? '0' : '1'),
                'type' => array('label' => ploopi\str::htmlentities($instance['moduletype'])),
                'label' => array('label' => ploopi\str::htmlentities($instance['label'])),
                'description' => array('label' => ploopi\str::htmlentities($instance['description']))
            );
      }

      foreach ($installedmodules AS $index => $moduletype)
      {
        $values[]['values'] =
            array(
                'check' => array('label' => '<input type="checkbox" name="heritedmodule[]" value="NEW,'.$moduletype['id'].'">', 'sort_label' => '9'),
                'type' => array('label' => ploopi\str::htmlentities($moduletype['label'])),
                'label' => array('label' => '&nbsp;'),
                'description' => array('label' => ploopi\str::htmlentities($moduletype['description']))
            );
      }

    ploopi\skin::get()->display_array($columns, $values, 'array_choosemodules', array('sortable' => true, 'orderby_default' => 'check'));

    ?>
</div>

<div style="clear:both;float:right;padding:4px;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>

<?php echo ploopi\skin::get()->close_simplebloc(); ?>
