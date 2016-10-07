<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestion de la partie droite de l'interface d'administration des groupes d'utilisateurs et espaces de travail
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * switch principal entre groupes d'utilisateurs et espaces de travail
 */

switch ($_SESSION['system']['level'])
{
    case _SYSTEM_GROUPS :
        $group = new ploopi\group();
        $group->open($groupid);
        $workspace = null;

        $currentgroup = '';
        $childgroup = '';

        if (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth{$group->fields['depth']}_label"]) && $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth{$group->fields['depth']}_label"] != '')
        {
            $currentgroup = "(" . $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth{$group->fields['depth']}_label"] . ")";
        }

        if (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth".($group->fields['depth']+1)."_label"]) && $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth".($group->fields['depth']+1)."_label"] != '')
        {
            $childgroup = "(" . $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_groupdepth".($group->fields['depth']+1)."_label"] . ")";
        }

        $toolbar = array();
        $toolbar['tabGroups'] =
            array(
                'title'     => _SYSTEM_LABELICON_GROUP,
                'url'       => "admin.php?wspToolbarItem=tabGroups",
                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group.png"
            );

        $toolbar['tabUsers'] =
            array(
                'title'     => _SYSTEM_LABELICON_USERS,
                'url'       => "admin.php?wspToolbarItem=tabUsers",
                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_user.png"
            );

        if (!empty($_GET['wspToolbarItem']))  $_SESSION['system']['wspToolbarItem'] = $_GET['wspToolbarItem'];
        if (!isset($_SESSION['system']['wspToolbarItem'])) $_SESSION['system']['wspToolbarItem'] = '';
        echo $skin->create_toolbar($toolbar,$_SESSION['system']['wspToolbarItem']);

        switch($_SESSION['system']['wspToolbarItem'])
        {
            // ---------------
            // ONGLET "GROUPE"
            // ---------------
            case 'tabGroups':
                switch($op)
                {
                    case 'save_group' :

                        $group = new ploopi\group();

                        if (!empty($_REQUEST['group_id']) && is_numeric($_REQUEST['group_id'])) $group->open($_REQUEST['group_id']);

                        if (!empty($_REQUEST['group_id_group']) && is_numeric($_REQUEST['group_id_group']))
                        {
                            $parent_group = new ploopi\group();
                            $parent_group->open($_REQUEST['group_id_group']);
                            $group->fields['parents'] = "{$parent_group->fields['parents']};{$_REQUEST['group_id_group']}";
                        }

                        $group->setvalues($_REQUEST,'group_');
                        if (empty($_REQUEST['group_shared'])) $group->fields['shared'] = 0;

                        $group_id = $group->save();

                        if ($group->new) ploopi\user_action_log::record(_SYSTEM_ACTION_CREATEGROUP, "{$group->fields['label']} ({$group_id})");
                        else ploopi\user_action_log::record(_SYSTEM_ACTION_MODIFYGROUP, "{$group->fields['label']} ({$group_id})");

                        unset($_SESSION['system']['groups']);
                        unset($_SESSION['system']['workspaces']);


                        ploopi\output::redirect("admin.php?groupid={$group_id}&reloadsession");
                    break;

                    case 'child' :
                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                        {
                            include './modules/system/admin_index_group_add.php';
                        }
                    break;

                    case 'clone' :
                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                        {
                            $clone = $group->createclone();
                            $groupid = $clone->save();
                            ploopi\user_action_log::record(_SYSTEM_ACTION_CLONEGROUP, "{$clone->fields['label']} ({$groupid})");

                            unset($_SESSION['system']['groups']);
                            unset($_SESSION['system']['workspaces']);
                            ploopi\output::redirect("admin.php?groupid={$groupid}");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'delete' :
                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER)
                        {
                            $sizeof_groups = sizeof($group->getchildren());
                            $sizeof_users = sizeof($group->getusers());
                            if (!$sizeof_groups && !$sizeof_users)
                            {
                                ploopi\user_action_log::record(_SYSTEM_ACTION_DELETEGROUP, "{$group->fields['label']} ({$group->fields['id_group']})");
                                $group->delete();

                                unset($_SESSION['system']['groups']);
                                unset($_SESSION['system']['workspaces']);

                                if(!empty($group->fields['id_workspace'])) ploopi\output::redirect("admin.php?workspaceid={$group->fields['id_workspace']}");
                                else ploopi\output::redirect("admin.php?groupid={$group->fields['id_group']}");
                            }
                        }
                        ploopi\output::redirect('admin.php');
                    break;

                    default :
                        include_once './modules/system/admin_index_group.php';
                    break;
                }
            break;

            // ---------------------
            // USER MANAGEMENT
            // ---------------------
            case 'tabUsers':
                include_once './modules/system/admin_index_users.php';
            break;
        } // switch
    break;

    case _SYSTEM_WORKSPACES :
        $workspace = new ploopi\workspace();
        if (!$workspace->open($workspaceid)) ploopi\output::redirect("admin.php?workspaceid=0");

        $group = null;

        $currentworkspace = '';
        $childworkspace = '';

        if (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth{$workspace->fields['depth']}_label"]) && $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth{$workspace->fields['depth']}_label"] != '')
        {
            $currentworkspace = "(" . $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth{$workspace->fields['depth']}_label"] . ")";
        }

        if (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth".($workspace->fields['depth']+1)."_label"]) && $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth".($workspace->fields['depth']+1)."_label"] != '')
        {
            $childworkspace = "(" . $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]["system_workspacedepth".($group->fields['depth']+1)."_label"] . ")";
        }

        $toolbar = array();
        $toolbar['tabWorkspaces'] =
            array(
                'title'     => _SYSTEM_LABELICON_WORKSPACE,
                'url'       => "admin.php?wspToolbarItem=tabWorkspaces",
                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace.png"
            );

        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
        {

                $toolbar['tabModules'] =
                    array(
                        'title'     => _SYSTEM_LABELICON_MODULES,
                        'url'       => "admin.php?wspToolbarItem=tabModules",
                        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_module.png"
                    );

                $toolbar['tabParams'] =
                    array(
                        'title'     => _SYSTEM_LABELICON_PARAMS,
                        'url'       => "admin.php?wspToolbarItem=tabParams",
                        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_systemparams.png"
                    );
        }

        $toolbar['tabDirectory'] =
            array(
                'title'     => _SYSTEM_LABELICON_USERS,
                'url'       => "admin.php?wspToolbarItem=tabDirectory",
                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_directory.png"
            );

        $toolbar['tabUsers'] =
            array(
                'title'     => _SYSTEM_LABELICON_AUTHORIZATIONS,
                'url'       => "admin.php?wspToolbarItem=tabUsers",
                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_user.png"
            );

        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
        {
            $toolbar['tabRoles'] =
                array(
                    'title'     => _SYSTEM_LABELICON_ROLES,
                    'url'       => "admin.php?wspToolbarItem=tabRoles",
                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_role.png"
                );
        }

        if (!empty($_GET['wspToolbarItem']))  $_SESSION['system']['wspToolbarItem'] = $_GET['wspToolbarItem'];
        if (!isset($_SESSION['system']['wspToolbarItem'])) $_SESSION['system']['wspToolbarItem'] = '';
        echo $skin->create_toolbar($toolbar, $_SESSION['system']['wspToolbarItem']);

        switch($_SESSION['system']['wspToolbarItem'])
        {
            // ---------------
            // ONGLET "ESPACE DE TRAVAIL"
            // ---------------
            case 'tabWorkspaces':
                switch($op)
                {
                    case 'save_group' :
                        $group = new ploopi\group();

                        $group->setvalues($_POST,'group_');
                        $group->setvalues($_GET,'group_');
                        if (empty($_POST['group_shared'])) $group->fields['shared'] = 0;
                        $group->fields['id_group'] = 1;
                        $group->fields['id_workspace'] = $workspaceid;
                        $group->fields['parents'] = '0;1';

                        $group_id = $group->save();

                        ploopi\user_action_log::record(_SYSTEM_ACTION_CREATEGROUP, "{$group->fields['label']} (id:{$group_id})");

                        $group->attachtogroup($workspaceid);

                        ploopi\user_action_log::record(_SYSTEM_ACTION_ATTACHGROUP, "{$group->fields['label']} (id:{$group_id}) => {$workspace->fields['label']} (id:{$workspaceid})");

                        unset($_SESSION['system']['groups']);
                        unset($_SESSION['system']['workspaces']);

                        ploopi\output::redirect("admin.php?groupid={$group_id}&reloadsession");
                    break;

                    case 'save_workspace' :
                        // Il faut être admin d'espace ou mieux pour pouvoir sauvegarder un espace de travail
                        if ($_SESSION['ploopi']['adminlevel'] <= _PLOOPI_ID_LEVEL_GROUPMANAGER) ploopi\output::redirect("admin.php?workspaceid={$workspace_id}");

                        $workspace = new ploopi\workspace();
                        if (!empty($_GET['workspace_id']) && is_numeric($_GET['workspace_id'])) $workspace->open($_GET['workspace_id']);

                        $workspace->setvalues($_POST,'workspace_');

                        if (!empty($_GET['workspace_id_workspace']))
                        {
                            $parent_workspace = new ploopi\workspace();
                            $parent_workspace->open($_GET['workspace_id_workspace']);
                            $workspace->fields['parents'] = "{$parent_workspace->fields['parents']};{$_GET['workspace_id_workspace']}";
                            $workspace->fields['id_workspace'] = $_GET['workspace_id_workspace'];
                        }

                        if (empty($_POST['workspace_backoffice'])) $workspace->fields['backoffice'] = 0;
                        if (empty($_POST['workspace_frontoffice'])) $workspace->fields['frontoffice'] = 0;
                        if (empty($_POST['workspace_mustdefinerule'])) $workspace->fields['mustdefinerule'] = 0;

                        $workspace_id = $workspace->save();

                        if ($workspace->new) ploopi\user_action_log::record(_SYSTEM_ACTION_CREATEWORKSPACE, "{$workspace->fields['label']} ({$workspace_id})");
                        else ploopi\user_action_log::record(_SYSTEM_ACTION_MODIFYWORKSPACE, "{$workspace->fields['label']} ({$workspace_id})");

                        system_updateparents();

                        if (empty($_GET['workspace_id']) && isset($_POST['heritedmodule']))
                        {
                            foreach($_POST['heritedmodule'] as $instance)
                            {
                                $data = explode(',',$instance);
                                $instancetype = $data[0];
                                if ($instancetype == 'NEW')
                                {
                                    $moduletype_id = $data[1];
                                    $module_type = new ploopi\module_type();
                                    $module_type->open($moduletype_id);

                                    ploopi\user_action_log::record(_SYSTEM_ACTION_USEMODULE, $module_type->fields['label']);

                                    $module = $module_type->createinstance($workspace_id);
                                    $module_id = $module->save();

                                    $module_workspace = new ploopi\module_workspace();
                                    $module_workspace->fields['id_module'] = $module_id;
                                    $module_workspace->fields['id_workspace'] = $workspace_id;
                                    $module_workspace->save();
                                }
                                elseif ($instancetype == 'SHARED')
                                {
                                    $module_id = $data[1];
                                    $module = new ploopi\module();
                                    $module->open($module_id);

                                    ploopi\user_action_log::record(_SYSTEM_ACTION_USEMODULE, $module->fields['label']);

                                    $module_workspace = new ploopi\module_workspace();
                                    $module_workspace->fields['id_module'] = $module_id;
                                    $module_workspace->fields['id_workspace'] = $workspace_id;
                                    $module_workspace->save();
                                }
                            }
                        }

                        unset($_SESSION['system']['groups']);
                        unset($_SESSION['system']['workspaces']);

                        ploopi\output::redirect("admin.php?workspaceid={$workspace_id}&reloadsession");
                    break;

                    case 'groupchild':
                        include './modules/system/admin_index_group_add.php';
                    break;

                    case 'child' :
                        include './modules/system/admin_index_workspace_add.php';
                    break;

                    case 'clone' :
                        $clone = $workspace->createclone();
                        $workspaceid = $clone->save();
                        ploopi\user_action_log::record(_SYSTEM_ACTION_CLONEGROUP, "{$clone->fields['label']} ($workspaceid)");

                        // get father

                        if ($father = $workspace->getfather())
                        {
                            $modules = $father->getsharedmodules(TRUE);

                            // inherit shared modules from father to clone (brother) of current group
                            foreach($modules as $moduleid => $module)
                            {
                                $module_workspace = new ploopi\module_workspace();
                                $module_workspace->fields['id_workspace'] = $workspaceid;
                                $module_workspace->fields['id_module'] = $moduleid;
                                $module_workspace->save();
                            }
                        }

                        unset($_SESSION['system']['groups']);
                        unset($_SESSION['system']['workspaces']);

                        ploopi\output::redirect("admin.php?workspaceid=$workspaceid");
                    break;

                    case 'delete' :
                        $sizeof_workspaces = sizeof($workspace->getchildren());
                        $sizeof_users = sizeof($workspace->getusers());
                        if (!$sizeof_workspaces && !$sizeof_users)
                        {
                            $modules = $workspace->getmodules();

                            foreach ($modules AS $moduleid => $moduleinfos)
                            {
                                $module = new ploopi\module();
                                $module->open($moduleid);

                                // Si le module appartient au groupe, on supprime le module
                                if ($moduleinfos['instanceworkspace'] == $workspaceid)
                                {
                                    $module->delete();
                                }
                                else
                                {
                                    $module->unlink($workspaceid);
                                }
                            }

                            $idfather = $workspace->fields['id_workspace'];
                            ploopi\user_action_log::record(_SYSTEM_ACTION_DELETEGROUP, "{$workspace->fields['label']} ({$workspace->fields['id_workspace']})");
                            $workspace->delete();

                            unset($_SESSION['system']['groups']);
                            unset($_SESSION['system']['workspaces']);

                            ploopi\output::redirect("admin.php?workspaceid=$idfather");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    default :
                        include_once './modules/system/admin_index_workspace.php';
                    break;
                }
            break;

            case 'tabModules':
                switch ($op)
                {
                    case 'add' :
                        if (empty($_GET['instance'])) ploopi\output::redirect('admin.php');

                        global $admin_redirect;
                        $admin_redirect = true;

                        //  create new instance or attach existing instance to current group
                        $data = explode(',',$_GET['instance']);
                        $instancetype = $data[0];
                        $workspace_id = $data[1];
                        if ($instancetype == 'NEW')
                        {
                            if (isset($data[2]) && is_numeric($data[2]))
                            {
                                $moduletype_id = $data[2];
                                $module_type = new ploopi\module_type();
                                if ($module_type->open($moduletype_id))
                                {

                                    ploopi\user_action_log::record(_SYSTEM_ACTION_USEMODULE, $module_type->fields['label']);

                                    echo $skin->open_simplebloc(ploopi\str::htmlentities(str_replace('<LABEL>',$module_type->fields['label'],_SYSTEM_LABEL_MODULEINSTANCIATION)));
                                    ?>
                                    <TABLE CELLPADDING="2" CELLSPACING="1"><TR><TD>
                                    <?php

                                    $module = $module_type->createinstance($workspace_id);
                                    if ($module_id = $module->save())
                                    {

                                        $module_workspace = new ploopi\module_workspace();
                                        $module_workspace->fields['id_module'] = $module_id;
                                        $module_workspace->fields['id_workspace'] = $workspace_id;
                                        $module_workspace->save();

                                        if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession&tab=modules&op=modify&moduleid=$module_id#modify");
                                        else
                                        {
                                            ?>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD ALIGN="RIGHT">
                                                    <INPUT TYPE="Button" CLASS="FlatButton" VALUE="<?php echo _PLOOPI_CONTINUE; ?>" OnClick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?reloadsession&tab=modules&op=modify&moduleid={$module_id})")."#modify"; ?>'">
                                                    </TD>
                                                </TR>
                                                </TABLE>
                                            <?php
                                            echo $skin->close_simplebloc();
                                        }
                                    }
                                    else ploopi\output::redirect('admin.php');
                                }
                                else ploopi\output::redirect('admin.php');
                            }
                            else ploopi\output::redirect('admin.php');
                        }
                        elseif ($instancetype == 'SHARED')
                        {
                            if (isset($data[2]) && is_numeric($data[2]))
                            {
                                $module_id = $data[2];
                                $module = new ploopi\module();
                                if ($module->open($module_id))
                                {
                                    ploopi\user_action_log::record(_SYSTEM_ACTION_USEMODULE, $module->fields['label']);

                                    $module_workspace = new ploopi\module_workspace();
                                    $module_workspace->fields['id_module'] = $module_id;
                                    $module_workspace->fields['id_workspace'] = $workspace_id;
                                    $module_workspace->save();
                                    if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");
                                }
                                else ploopi\output::redirect('admin.php');
                            }
                            else ploopi\output::redirect('admin.php');
                        }
                        else ploopi\output::redirect("admin.php?reloadsession");
                    break;

                    case 'switch_active':
                    case 'switch_visible':
                    case 'switch_public':
                    case 'switch_shared':
                    case 'switch_herited':
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            $module = new ploopi\module();
                            $module->open($_GET['moduleid']);
                            ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, $module->fields['label']);

                            if ($op == 'switch_active') $module->fields['active'] = ($module->fields['active']+1)%2;
                            if ($op == 'switch_visible') $module->fields['visible'] = ($module->fields['visible']+1)%2;
                            if ($op == 'switch_public') $module->fields['public'] = ($module->fields['public']+1)%2;
                            if ($op == 'switch_shared') $module->fields['shared'] = ($module->fields['shared']+1)%2;
                            if ($op == 'switch_herited') $module->fields['herited'] = ($module->fields['herited']+1)%2;

                            $module->save();
                            ploopi\output::redirect("admin.php?reloadsession");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'moveup' :
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            $module_workspace = new ploopi\module_workspace();
                            $module_workspace->open($workspaceid,$_GET['moduleid']);
                            $module_workspace->changeposition('up');
                            ploopi\output::redirect("admin.php?reloadsession");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'movedown' :
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            $module_workspace = new ploopi\module_workspace();
                            $module_workspace->open($workspaceid,$_GET['moduleid']);
                            $module_workspace->changeposition('down');
                            ploopi\output::redirect("admin.php?reloadsession");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'unlinkinstance' :
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            $module = new ploopi\module();
                            $module->open($_GET['moduleid']);
                            ploopi\user_action_log::record(_SYSTEM_ACTION_UNLINKMODULE, $module->fields['label']);

                            $module_workspace = new ploopi\module_workspace();
                            $module_workspace->open($workspaceid,$_GET['moduleid']);
                            $module_workspace->delete();
                            ploopi\output::redirect("admin.php?reloadsession");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'save_module_props' :
                        $module = new ploopi\module();
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']) && $module->open($_GET['moduleid']))
                        {
                            ploopi\user_action_log::record(_SYSTEM_ACTION_CONFIGUREMODULE, $module->fields['label']);

                            $module->setvalues($_POST,'module_');

                            if (!isset($_POST['module_active'])) $module->fields['active'] = 0;
                            if (!isset($_POST['module_visible'])) $module->fields['visible'] = 0;
                            if (!isset($_POST['module_autoconnect'])) $module->fields['autoconnect'] = 0;
                            if (!isset($_POST['module_shared'])) $module->fields['shared'] = 0;
                            if (!isset($_POST['module_herited'])) $module->fields['herited'] = 0;
                            if (!isset($_POST['module_adminrestricted'])) $module->fields['adminrestricted'] = 0;
                            if (!isset($_POST['module_transverseview'])) $module->fields['transverseview'] = 0;

                            if (!$module->fields['shared']) $module->fields['herited'] = 0;
                            $module->save();

                            ploopi\output::redirect("admin.php?moduleid={$module->fields['id']}&reloadsession");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'delete' :
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            global $admin_redirect;
                            $admin_redirect = true;

                            $module = new ploopi\module();
                            $module->open($_GET['moduleid']);

                            ploopi\user_action_log::record(_SYSTEM_ACTION_DELETEMODULE, $module->fields['label']);

                            echo $skin->open_simplebloc(ploopi\str::htmlentities(str_replace('<LABEL>',$module->fields['label'],_SYSTEM_LABEL_MODULEDELETE)));
                            ?>
                            <TABLE CELLPADDING="2" CELLSPACING="1"><TR><TD>
                            <?php

                            $module->delete();

                            if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");
                            else
                            {
                                ?>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN="RIGHT">
                                        <INPUT TYPE="Button" CLASS="FlatButton" VALUE="<?php echo _PLOOPI_CONTINUE; ?>" OnClick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?reloadsession"); ?>'">
                                        </TD>
                                    </TR>
                                    </TABLE>
                                <?php
                                echo $skin->close_simplebloc();
                            }
                        }
                    break;

                    case 'apply_heritage' :
                        if (!empty($_GET['moduleid']) && is_numeric($_GET['moduleid']))
                        {
                            $children = $workspace->getchildren();

                            foreach($children as $idchildren)
                            {
                                $module_workspace = new ploopi\module_workspace();
                                $module_workspace->open($idchildren,$_GET['moduleid']);
                                $module_workspace->save();
                            }
                            ploopi\output::redirect("admin.php?op=modify&moduleid={$_GET['moduleid']}#modify");
                        }
                        else ploopi\output::redirect('admin.php');
                    break;

                    case 'modify':
                    default :
                        include_once './modules/system/admin_index_modules.php';
                    break;

                }
            break;

            case 'tabParams' :
                $param_module = new ploopi\param();

                switch($op)
                {
                    case "save":

                        if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']))
                        {
                            $module = new ploopi\module();
                            $module->open($_POST['idmodule']);
                            ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, $module->fields['label']);

                            $param_module->open($_POST['idmodule'], $workspaceid);
                            $param_module->setvalues($_POST);
                            $param_module->save();

                            ploopi\output::redirect("admin.php?idmodule={$_POST['idmodule']}&reloadsession");
                        }
                    break;

                    default:
                        include_once './modules/system/admin_index_param.php';
                    break;
                }
            break;

            case 'tabDirectory':
                include_once './modules/system/admin_system_directory.php';
            break;

            case 'tabRoles':
                include_once './modules/system/admin_index_roles.php';
            break;

            // ---------------------
            // USER MANAGEMENT
            // ---------------------
            case 'tabUsers':
                include_once './modules/system/admin_index_users.php';
            break;

        } // switch
    break;

}//switch
?>
