<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Interface de gestion des modules d'un espace de travail.
 * Permet d'instancier, hériter, modifier la configuration d'un module.
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Tous les modules dispos
 */
$installedmodules = system_getinstalledmodules();

/**
 * Modules partagés
 */
$sharedmodules = $workspace->getsharedmodules();

/**
 * Modules de cet espace
 */
$ownmodules = $workspace->getmodules();

echo $skin->open_simplebloc(_SYSTEM_LABEL_GROUP_AVAILABLE_MODULES);

$columns = array();
$values = array();
$c = 0;

$columns['auto']['name']        = array('label' => _SYSTEM_LABEL_MODULENAME, 'options' => array('sort' => true));

$columns['left']['position']    = array('label' => _SYSTEM_LABEL_MODULEPOSITION, 'width' => '75', 'options' => array('sort' => true));
$columns['left']['type']        = array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '150', 'options' => array('sort' => true));
$columns['right']['actions']    = array('label' => _SYSTEM_LABEL_ACTION, 'width' => '50');
$columns['right']['herited']    = array('label' => 'Her.', 'width' => '55', 'options' => array('sort' => true));
$columns['right']['shared']     = array('label' => 'Par.', 'width' => '55', 'options' => array('sort' => true));
$columns['right']['visible']     = array('label' => 'Vis.', 'width' => '55', 'options' => array('sort' => true));
$columns['right']['active']     = array('label' => 'Act.', 'width' => '55', 'options' => array('sort' => true));
$columns['right']['viewmode']   = array('label' => _SYSTEM_LABEL_VIEWMODE, 'width' => '80', 'options' => array('sort' => true));

foreach ($ownmodules as $index => $module)
{
    $active = $visible = $public = $shared = $herited = '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/check_off.png">';

    $p_green = '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/check_on.png">';

    if ($module['active']) $active = $p_green;
    if ($module['visible']) $visible = $p_green;
    if ($module['public']) $public = $p_green;
    if ($module['shared']) $shared = $p_green;
    if ($module['herited']) $herited = $p_green;

    // owner
    if ($module['instanceworkspace'] == $workspaceid)
    {
        $modify = '<a href="'.ploopi_urlencode("admin.php?op=modify&moduleid={$module['instanceid']}").'#modify"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png"></a>';
        $delete = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=delete&moduleid={$module['instanceid']}").'\',\''._SYSTEM_MSG_CONFIRMMODULEDELETE.'\')"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png"></a>';
    }
    else
    {
        $modify = '<img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png">';
        if ($module['adminrestricted']) $delete = '<img style="margin:0 2px;" src="./modules/system/img/ico_noway.gif">';
        else $delete = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=unlinkinstance&moduleid={$module['instanceid']}").'\',\''._SYSTEM_MSG_CONFIRMMODULEDETACH.'\')"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_cut.png"></a>';
    }

    $updown =   '
                <a href="'.ploopi_urlencode("admin.php?op=movedown&moduleid={$module['instanceid']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/arrow_down.png"></a>
                <a href="'.ploopi_urlencode("admin.php?op=moveup&moduleid={$module['instanceid']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/arrow_up.png"></a>
                ';

    $viewmode = $ploopi_viewmodes[$module['viewmode']];

    if ($module['transverseview']) $viewmode .= ' '._SYSTEM_LABEL_TRANSVERSE;

    $values[$c]['values']['position'] = array('label' => "{$updown}{$module['position']}", 'sort_label' => $module['position']);
    $values[$c]['values']['type'] = array('label' => htmlentities($module['label']));
    $values[$c]['values']['name'] = array('label' => htmlentities($module['instancename']));
    $values[$c]['values']['viewmode'] = array('label' => $viewmode);

    if ($module['instanceworkspace'] == $workspaceid)
    {
        $values[$c]['values']['active'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=switch_active&moduleid={$module['instanceid']}").'">'.$active.'</a>', 'sort_label' => ($module['active']) ? 1 : 0);
        $values[$c]['values']['visible'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=switch_visible&moduleid={$module['instanceid']}").'">'.$visible.'</a>', 'sort_label' => ($module['visible']) ? 1 : 0);
        $values[$c]['values']['public'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=switch_public&moduleid={$module['instanceid']}").'">'.$public.'</a>', 'sort_label' => ($module['public']) ? 1 : 0);
        $values[$c]['values']['shared'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=switch_shared&moduleid={$module['instanceid']}").'">'.$shared.'</a>', 'sort_label' => ($module['shared']) ? 1 : 0);
        $values[$c]['values']['herited'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=switch_herited&moduleid={$module['instanceid']}").'">'.$herited.'</a>', 'sort_label' => ($module['herited']) ? 1 : 0);
    }
    else
    {
        $values[$c]['values']['active'] = array('label' => $active);
        $values[$c]['values']['visible'] = array('label' => $visible);
        $values[$c]['values']['public'] = array('label' => $public);
        $values[$c]['values']['shared'] = array('label' => $shared);
        $values[$c]['values']['herited'] = array('label' => $herited);
    }

    $values[$c]['values']['actions'] = array('label' => "{$modify}{$delete}", 'style' => 'text-align:center;');
    $c++;
}

$skin->display_array($columns, $values, 'array_ownmodules', array('sortable' => true, 'orderby_default' => 'position'));

echo $skin->close_simplebloc();

if ($op == 'modify' && !empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
{
    $module = new module();
    $module->open($_GET['moduleid']);

    echo '<a name="modify"></a>';
    echo $skin->open_simplebloc(str_replace('<MODULE>',$module->fields['label'],_SYSTEM_LABEL_MODULE_PROPERTIES));
    ?>

    <form name="form_modify_module" action="<?php echo ploopi_urlencode("admin.php?op=save_module_props&moduleid={$module->fields['id']}"); ?>" method="post">
    <div class="ploopi_form">
        <div style="padding:2px;">
            <p>
                <label><?php echo _SYSTEM_LABEL_MODULENAME; ?>:</label>
                <input type="text" class="text" name="module_label" id="module_label" value="<?php echo htmlentities($module->fields['label']); ?>" tabindex="1" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_MODULENAME; ?></em></span>
            </p>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_active');">
                <label><?php echo _SYSTEM_LABEL_ACTIVE; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_active" id="module_active" value="1" <?php if ($module->fields['active']) echo 'checked="checked"'; ?> tabindex="2" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_ACTIVE; ?></em></span>
            </p>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_visible');">
                <label><?php echo _SYSTEM_LABEL_VISIBLE; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_visible" id="module_visible" value="1" <?php if ($module->fields['visible']) echo 'checked="checked"'; ?> tabindex="3" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_VISIBLE; ?></em></span>
            </p>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_autoconnect');">
                <label><?php echo _SYSTEM_LABEL_AUTOCONNECT; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_autoconnect" id="module_autoconnect" value="1" <?php if ($module->fields['autoconnect']) echo 'checked="checked"'; ?> tabindex="4" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_AUTOCONNECT; ?></em></span>
            </p>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_shared');">
                <label><?php echo _SYSTEM_LABEL_SHARED; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_shared" id="module_shared" value="1" <?php if ($module->fields['shared']) echo 'checked="checked"'; ?> tabindex="5" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_SHARED; ?></em></span>
            </p>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_herited');">
                <label><?php echo _SYSTEM_LABEL_HERITED; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_herited" id="module_herited" value="1" <?php if ($module->fields['herited']) echo 'checked="checked"'; ?> tabindex="6" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><a href="<?php echo ploopi_urlencode("admin.php?op=apply_heritage&moduleid={$module->fields['id']}"); ?>"><?php echo _SYSTEM_APPLYHERITAGE; ?></a><br /><em><?php echo _SYSTEM_EXPLAIN_HERITED; ?></em></span>
            </p>
            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_adminrestricted');">
                <label><?php echo _SYSTEM_LABEL_ADMINRESTRICTED; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_adminrestricted" id="module_adminrestricted" value="1" <?php if ($module->fields['adminrestricted']) echo 'checked="checked"'; ?> tabindex="7" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_ADMINRESTRICTED; ?></em></span>
            </p>
            <p>
                <label><?php echo _SYSTEM_LABEL_VIEWMODE; ?>:</label>
                <select class="select" name="module_viewmode" tabindex="8">
                <?php
                foreach($ploopi_viewmodes as $id => $viewmode)
                {
                    if ($module->fields['viewmode'] == $id) $sel = 'selected';
                    else $sel = '';

                    ?>
                    <option <?php if ($module->fields['viewmode'] == $id) echo 'selected="selected"'; ?> value="<?php echo $id; ?>"><?php echo $viewmode; ?></option>
                    <?php
                }
                ?>
                </select>
            </p>
            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'module_transverseview');">
                <label><?php echo _SYSTEM_LABEL_TRANSVERSE; ?>:</label>
                <input type="checkbox" class="checkbox" name="module_transverseview" id="module_transverseview" value="1" <?php if ($module->fields['transverseview']) echo 'checked="checked"'; ?> tabindex="9" />
            </p>
            <p>
                <label>&nbsp;</label>
                <span><em><?php echo _SYSTEM_EXPLAIN_VIEWMODE; ?></em></span>
            </p>
        </div>
        <div style="clear:both;text-align:right;padding:4px;">
            <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="10" />
        </div>
    </div>
    </form>
    <script type="text/javascript">
        ploopi_window_onload_stock(
            function() {
                $('module_label').focus();
            }
        );
    </script>
    <?php
    echo $skin->close_simplebloc();
}

echo $skin->open_simplebloc(_SYSTEM_LABEL_GROUP_USABLE_MODULES);

$columns = array();
$values = array();
$c = 0;

$columns['left']['type']        = array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '150', 'options' => array('sort' => true));
$columns['auto']['desc']        = array('label' => _SYSTEM_LABEL_DESCRIPTION, 'options' => array('sort' => true));
$columns['right']['actions']    = array('label' => _SYSTEM_LABEL_ACTION, 'width' => '100', 'options' => array('sort' => true));

foreach ($sharedmodules AS $instanceid => $instance)
{
    if (!array_key_exists($instanceid,$ownmodules))
    {
        $desc = sprintf("<span>%s / <b>%s</b> partagé par<b>&nbsp;</span><a href=\"%s\">%s</a></b>", htmlentities($instance['description']), htmlentities($instance['label']), ploopi_urlencode("admin.php?workspaceid={$instance['id_workspace']}"), htmlentities($instance['workspacelabel']));;
        $values[$c]['values']['type'] = array('label' => htmlentities($instance['moduletype']));
        $values[$c]['values']['desc'] = array('label' => $desc);
        $values[$c]['values']['actions'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=add&instance=SHARED,{$workspaceid},{$instanceid}").'">utiliser</a>', 'sort_label' => 0);
        $c++;
    }
}

foreach ($installedmodules AS $index => $moduletype)
{
    $values[$c]['values']['type'] = array('label' => htmlentities($moduletype['label']));
    $values[$c]['values']['desc'] = array('label' => htmlentities($moduletype['description']));
    $values[$c]['values']['actions'] = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=add&instance=NEW,{$workspaceid},{$moduletype['id']}").'">instancier</a>', 'sort_label' => 1);
    $c++;
}

$skin->display_array($columns, $values, 'array_modules', array('sortable' => true, 'orderby_default' => 'type'));

echo $skin->close_simplebloc();
?>
