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
?>
<?
// all available modules
$installedmodules = system_getinstalledmodules();
// shared modules
$sharedmodules = $workspace->getsharedmodules();
// own modules
$ownmodules = $workspace->getmodules();

echo $skin->open_simplebloc(_SYSTEM_LABEL_GROUP_AVAILABLE_MODULES);

$columns = array();
$values = array();
$c = 0;

$columns['auto']['name']        = array('label' => _SYSTEM_LABEL_MODULENAME, 'options' => array('sort' => true));

$columns['left']['position']    = array('label' => _SYSTEM_LABEL_MODULEPOSITION, 'width' => '50', 'style' => 'text-align:center;');
$columns['left']['type']        = array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '100', 'options' => array('sort' => true));
$columns['right']['actions']    = array('label' => _SYSTEM_LABEL_ACTION, 'width' => '50');
$columns['right']['herited']    = array('label' => 'Her.', 'width' => '50', 'options' => array('sort' => true));
$columns['right']['shared']     = array('label' => 'Par.', 'width' => '50', 'options' => array('sort' => true));
$columns['right']['active']     = array('label' => 'Act.', 'width' => '50', 'options' => array('sort' => true));
$columns['right']['viewmode']   = array('label' => _SYSTEM_LABEL_VIEWMODE, 'width' => '80', 'options' => array('sort' => true));

foreach ($ownmodules AS $index => $module)
{
    $active = $public = $shared = $herited = '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/p_red.png">';

    $p_green = '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/p_green.png">';

    if ($module['active']) $active = $p_green;
    if ($module['public']) $public = $p_green;
    if ($module['shared']) $shared = $p_green;
    if ($module['herited']) $herited = $p_green;

    // owner
    if ($module['instanceworkspace'] == $workspaceid)
    {
        $modify = '<a href="'.ploopi_urlencode("{$scriptenv}?op=modify&moduleid={$module['instanceid']}").'#modify"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png"></a>';
        $delete = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=delete&moduleid={$module['instanceid']}").'\',\''._SYSTEM_MSG_CONFIRMMODULEDELETE.'\')"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png"></a>';
    }
    else
    {
        $modify = '<img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png">';
        if ($module['adminrestricted']) $delete = '<img style="margin:0 2px;" src="./modules/system/img/ico_noway.gif">';
        else $delete = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=unlinkinstance&moduleid={$module['instanceid']}").'\',\''._SYSTEM_MSG_CONFIRMMODULEDETACH.'\')"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_cut.png"></a>';
    }

    $updown =   '
                <a href="'.ploopi_urlencode("{$scriptenv}?op=movedown&moduleid={$module['instanceid']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/arrow_down.png"></a>
                <a href="'.ploopi_urlencode("{$scriptenv}?op=moveup&moduleid={$module['instanceid']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/arrow_up.png"></a>
                ';

    $viewmode = $ploopi_viewmodes[$module['viewmode']];

    if ($module['transverseview']) $viewmode .= ' '._SYSTEM_LABEL_TRANSVERSE;

    $values[$c]['values']['position'] = array('label' => $updown, 'style' => 'text-align:center;', 'sort_label' => $module['position']);
    $values[$c]['values']['type'] = array('label' => htmlentities($module['label']));
    $values[$c]['values']['name'] = array('label' => htmlentities($module['instancename']));
    $values[$c]['values']['viewmode'] = array('label' => $viewmode);

    if ($module['instanceworkspace'] == $workspaceid)
    {
        $values[$c]['values']['active'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=switch_active&moduleid={$module['instanceid']}").'">'.$active.'</a>', 'sort_label' => ($module['active']) ? 1 : 0);
        $values[$c]['values']['public'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=switch_public&moduleid={$module['instanceid']}").'">'.$public.'</a>', 'sort_label' => ($module['public']) ? 1 : 0);
        $values[$c]['values']['shared'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=switch_shared&moduleid={$module['instanceid']}").'">'.$shared.'</a>', 'sort_label' => ($module['shared']) ? 1 : 0);
        $values[$c]['values']['herited'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=switch_herited&moduleid={$module['instanceid']}").'">'.$herited.'</a>', 'sort_label' => ($module['herited']) ? 1 : 0);
    }
    else
    {
        $values[$c]['values']['active'] = array('label' => $active);
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

    echo '<a name="modify">';
    echo $skin->open_simplebloc(str_replace('<MODULE>',$module->fields['label'],_SYSTEM_LABEL_MODULE_PROPERTIES));
    ?>
    <TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0">
    <TR BGCOLOR="<? echo $skin->values['bgline1']; ?>">
        <TD ALIGN="CENTER">
        <FORM NAME="form_modify_module" ACTION="<? echo $scriptenv; ?>" METHOD="POST">
        <INPUT TYPE="HIDDEN" NAME="op" VALUE="save_module_props">
        <INPUT TYPE="HIDDEN" NAME="moduleid" VALUE="<? echo $module->fields['id']; ?>">
        <TABLE WIDTH="100%" CELLPADDING="2" CELLSPACING="1">
        <TR BGCOLOR="<? echo $skin->values['bgline2']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_MODULENAME; ?>:&nbsp;</B></TD>
            <TD ALIGN="LEFT"><INPUT class="text" TYPE="Text" SIZE=30 MAXLENGTH=100 NAME="module_label" VALUE="<? echo htmlentities($module->fields['label']); ?>"></TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_MODULENAME; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline1']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_ACTIVE; ?>:&nbsp;</B></TD>
            <TD>
            <INPUT TYPE="Radio" <? if ($module->fields['active']) echo "checked" ?> VALUE="1" NAME="module_active"><? echo _PLOOPI_YES; ?>
            <INPUT TYPE="Radio" <? if (!$module->fields['active']) echo "checked" ?> VALUE="0" NAME="module_active"><? echo _PLOOPI_NO; ?>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_ACTIVE; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline2']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_AUTOCONNECT; ?>:&nbsp;</B></TD>
            <TD>
            <INPUT TYPE="Radio" <? if ($module->fields['autoconnect']) echo "checked" ?> VALUE="1" NAME="module_autoconnect"><? echo _PLOOPI_YES; ?>
            <INPUT TYPE="Radio" <? if (!$module->fields['autoconnect']) echo "checked" ?> VALUE="0" NAME="module_autoconnect"><? echo _PLOOPI_NO; ?>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_AUTOCONNECT; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline1']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_SHARED; ?>:&nbsp;</B></TD>
            <TD>
            <INPUT TYPE="Radio" <? if ($module->fields['shared']) echo "checked" ?> VALUE="1" NAME="module_shared"><? echo _PLOOPI_YES; ?>
            <INPUT TYPE="Radio" <? if (!$module->fields['shared']) echo "checked" ?> VALUE="0" NAME="module_shared"><? echo _PLOOPI_NO; ?>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_SHARED; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline2']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_HERITED; ?>:&nbsp;</B></TD>
            <TD>
            <INPUT TYPE="Radio" <? if ($module->fields['herited']) echo "checked" ?> VALUE="1" NAME="module_herited"><? echo _PLOOPI_YES; ?>
            <INPUT TYPE="Radio" <? if (!$module->fields['herited']) echo "checked" ?> VALUE="0" NAME="module_herited"><? echo _PLOOPI_NO; ?>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_HERITED; ?></I><br><a href="<? echo ploopi_urlencode("{$scriptenv}?op=apply_heritage&moduleid={$module->fields['id']}"); ?>"><? echo _SYSTEM_APPLYHERITAGE; ?></a></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline1']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_ADMINRESTRICTED; ?>:&nbsp;</B></TD>
            <TD>
            <INPUT TYPE="Radio" <? if ($module->fields['adminrestricted']) echo "checked" ?> VALUE="1" NAME="module_adminrestricted"><? echo _PLOOPI_YES; ?>
            <INPUT TYPE="Radio" <? if (!$module->fields['adminrestricted']) echo "checked" ?> VALUE="0" NAME="module_adminrestricted"><? echo _PLOOPI_NO; ?>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_ADMINRESTRICTED; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline2']; ?>">
            <TD ALIGN="RIGHT"><B><? echo _SYSTEM_LABEL_VIEWMODE; ?>:&nbsp;</B></TD>
            <TD>
            <TABLE CELLPADDING=0 CELLSPACING=0>
            <TR>
                <TD VALIGN="MIDDLE">
                <SELECT class="select" NAME="module_viewmode">
                <?
                foreach($ploopi_viewmodes as $id => $viewmode)
                {
                    if ($module->fields['viewmode'] == $id) $sel = 'selected';
                    else $sel = '';
                    echo "<OPTION $sel VALUE=\"$id\">$viewmode</OPTION>";
                }
                ?>
                </SELECT>
                </TD>
                <TD VALIGN="MIDDLE" NOWRAP>&nbsp;<INPUT TYPE="Checkbox" <? if ($module->fields['transverseview']) echo "checked" ?> VALUE="1" NAME="module_transverseview"></TD>
                <TD VALIGN="MIDDLE"><? echo _SYSTEM_LABEL_TRANSVERSE; ?></TD>
            </TR>
            </TABLE>
            </TD>
            <TD ALIGN="LEFT"><I><? echo _SYSTEM_EXPLAIN_VIEWMODE; ?></I></TD>
        </TR>
        <TR BGCOLOR="<? echo $skin->values['bgline2']; ?>">
            <TD ALIGN="RIGHT" COLSPAN="3">
                <INPUT TYPE="Submit" class="flatbutton" VALUE="<? echo _PLOOPI_SAVE; ?>">
            </TD>
        </TR>
        </TABLE>
        </FORM>

        </TD>
    </TR>
    </TABLE>
    <?
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
        $desc = sprintf("<span>%s / <b>%s</b> partagé par <b></span><a href=\"%s\">%s</a></b>", htmlentities($instance['description']), htmlentities($instance['label']), ploopi_urlencode("{$scriptenv}?workspaceid={$instance['id_workspace']}"), htmlentities($instance['workspacelabel']));;
        $values[$c]['values']['type'] = array('label' => htmlentities($instance['moduletype']));
        $values[$c]['values']['desc'] = array('label' => $desc);
        $values[$c]['values']['actions'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=add&instance=SHARED,{$workspaceid},{$instanceid}").'">utiliser</a>', 'sort_label' => 0);
        $c++;
    }
}

foreach ($installedmodules AS $index => $moduletype)
{
    $values[$c]['values']['type'] = array('label' => htmlentities($moduletype['label']));
    $values[$c]['values']['desc'] = array('label' => htmlentities($moduletype['description']));
    $values[$c]['values']['actions'] = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=add&instance=NEW,{$workspaceid},{$moduletype['id']}").'">instancier</a>', 'sort_label' => 1);
    $c++;
}

$skin->display_array($columns, $values, 'array_modules', array('sortable' => true, 'orderby_default' => 'actions'));

echo $skin->close_simplebloc();
?>
