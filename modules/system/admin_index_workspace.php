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
 * Interface de modification d'un espace de travail.
 * Permet de copier/cloner/supprimer.
 * 
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */
echo $skin->open_simplebloc();
?>

<div>
    <div class="system_group_icons">
        <div class="system_group_icons_padding">
            <?
            $nbusers = sizeof($workspace->getusers());
            $nbgroups = sizeof($workspace->getgroups());
            $nbworkspaces = (!empty($workspaces['tree'][$workspaceid])) ? sizeof($workspaces['tree'][$workspaceid]) : 0;
            ?>
            <div style="padding:4px;">
                Cet espace est composé de
                <br /><strong><? echo $nbworkspaces; ?> espace(s)</strong>
                <br /><strong><? echo $nbgroups; ?> groupe(s)</strong>
                <br /><strong><? echo $nbusers; ?> utilisateur(s)</strong>
            </div>
            <?
            if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
            {
                $toolbar_workspace[] = 
                    array(
                        'title'     => str_replace('<LABEL>','<br /><b>'.$childworkspace.'</b>', _SYSTEM_LABEL_CREATE_CHILD_WORKSPACE),
                        'url'       => "admin.php?op=child&gworkspaceid=$workspaceid",
                        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_child.png",
                    );
    
                if ($_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN && $_SESSION['ploopi']['workspaceid'] == $workspaceid)
                {
                    $toolbar_workspace[] = 
                        array(
                            'title'     => str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE),
                            'url'       => 'admin.php',
                            'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_copy_gray.png",
                            'confirm'   => _SYSTEM_MSG_CANTCOPYGROUP
                        );
                }
                else
                {
                    $toolbar_workspace[] = 
                        array(
                            'title'     => str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE),
                            'url'       => "admin.php?op=clone&workspaceid=$workspaceid",
                            'icon'      => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_copy.png",
                        );
                }

                $sizeof_workspaces = sizeof($workspace->getchildren());
                $sizeof_users = sizeof($workspace->getusers());
    
                // delete button if group not protected and no children
                if (!$workspace->fields['protected'] && !$sizeof_workspaces && !$sizeof_users)
                {
                    $toolbar_workspace[] = 
                        array(
                            'title'     => str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_DELETE_WORKSPACE),
                            'url'       => "admin.php?op=delete&workspaceid=$workspaceid",
                            'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_delete.png",
                        );
                }
                else
                {
                    if ($sizeof_workspaces || $sizeof_users)
                    {
                        $msg = '';
                        if ($sizeof_workspaces) $msg = _SYSTEM_MSG_INFODELETE_GROUPS;
                        elseif ($sizeof_users) $msg = _SYSTEM_MSG_INFODELETE_USERS;
    
                        $toolbar_workspace[] = 
                            array(
                                'title'     => str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_DELETE_WORKSPACE),
                                'url'       => 'admin.php',
                                'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_delete_gray.png",
                                'confirm'   => $msg
                            );
    
                    }
                }
            }
            
            $toolbar_workspace[] = 
                array(
                    'title'     => _SYSTEM_LABEL_CREATE_GROUP,
                    'url'       => "admin.php?op=groupchild&workspaceid=$workspaceid",
                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_child.png",
                );



            echo $skin->create_toolbar($toolbar_workspace, $x = 0, false, true);
            ?>
        </div>
    </div>

    <div class="system_group_main">
    <?

    if ($father = $workspace->getfather())
    {
        $parentlabel = $father->fields['label'];
        $parentid = $father->fields['id'];
    }
    else
    {
        $parentlabel = 'Racine';
        $parentid = '';
    }

    $templatelist_back = ploopi_getavailabletemplates('backoffice');

    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
    {
        ?>
        <form name="" action="<? echo ploopi_urlencode("admin.php?op=save_workspace&workspace_id={$workspace->fields['id']}"); ?>" method="post" onsubmit="javascript:return system_workspace_validate(this);">
        <?
    }
    ?>

        <div class="ploopi_form_title">
            <? echo $workspace->fields['label']; ?> &raquo;
            <?
            echo _SYSTEM_LABEL_WORKSPACE_MODIFY;
            ?>
        </div>
        <div class="ploopi_form" style="clear:both;padding:2px">
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_label"  value="<? echo htmlentities($workspace->fields['label']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['label']).'</span>';
                ?>
            </p>
            <?
            if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
            {
                ?>
                <p>
                    <label><? echo _SYSTEM_LABEL_GROUP_CODE; ?>:</label>
                    <input type="text" class="text" name="workspace_code"  value="<? echo $workspace->fields['code']; ?>">
                </p>
                <?
            }
            ?>
        </div>

        <div class="ploopi_form_title">
            <? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_ACCESS; ?>
        </div>

        <div class="ploopi_form" style="clear:both;padding:2px">
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_ADMIN; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input style="width:16px;" type="checkbox" name="workspace_backoffice" <? if ($workspace->fields['backoffice']) echo "checked"; ?> value="1">
                    <?
                }
                else echo '<span>'.($workspace->fields['backoffice'] ? _PLOOPI_YES : _PLOOPI_NO).'</span>';
                ?>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_SKIN; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <select class="select" name="workspace_template">
                        <option value=""><? echo _PLOOPI_NONE; ?></option>
                        <?
                        foreach($templatelist_back as $index => $tpl_name)
                        {
                            ?>
                            <option <? if ($tpl_name == $workspace->fields['template']) echo 'selected="selected"'; ?>><? echo htmlentities($tpl_name); ?></option>
                            <?
                        }
                        ?>
                    </select>
                    <?
                }
                else echo '<span>'.$workspace->fields['template'].'</span>';
                ?>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_ADMINDOMAINLIST; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <textarea class="text" name="workspace_backoffice_domainlist"><? echo htmlentities($workspace->fields['backoffice_domainlist']); ?></textarea>
                    <?
                }
                else echo '<span>'.ploopi_nl2br(htmlentities($workspace->fields['backoffice_domainlist'])).'</span>';
                ?>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_WEB; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input style="width:16px;" type="checkbox" name="workspace_frontoffice" <? if($workspace->fields['frontoffice']) echo "checked"; ?> value="1">
                    <?
                }
                else echo '<span>'.($workspace->fields['frontoffice'] ? _PLOOPI_YES : _PLOOPI_NO).'</span>';
                ?>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_WEBDOMAINLIST; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <textarea class="text" name="workspace_frontoffice_domainlist"><? echo htmlentities($workspace->fields['frontoffice_domainlist']); ?></textarea>
                    <?
                }
                else echo '<span>'.ploopi_nl2br(htmlentities($workspace->fields['backoffice_domainlist'])).'</span>';
                ?>
            </p>
            <?
            if ($workspace->fields['frontoffice'])
            {
                // check if webeedit used in this workspace
                $webedit_ready = false;
                foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
                {
                    if ($_SESSION['ploopi']['modules'][$idm]['moduletype'] == 'webedit') {$webedit_ready = true;}
                }

                if (!$webedit_ready)
                {
                    ?>
                    <div class="system_wce_warning">
                        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" style="display:block;float:left;margin:0 4px 4px 0;">
                        Attention, pour pouvoir activer l'accès Frontoffice, vous devez créer une instance du module <strong>WebEdit</strong>
                    </div>
                    <?
                }
            }
            ?>
        </div>

        <div class="ploopi_form_title">
            <? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_META; ?>
        </div>

        <div class="ploopi_form" id="system_meta" style="clear:both;padding:2px;">
            <p>
                <label>Titre:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_title" value="<? echo htmlentities($workspace->fields['title']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['title']).'</span>';
                ?>
            </p>
            <p>
                <label>Description:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_meta_description" value="<? echo htmlentities($workspace->fields['meta_description']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['meta_description']).'</span>';
                ?>
            </p>
            <p>
                <label>Mots Clés:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_meta_keywords" value="<? echo htmlentities($workspace->fields['meta_keywords']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['meta_keywords']).'</span>';
                ?>
            </p>
            <p>
                <label>Auteur:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_meta_author" value="<? echo $workspace->fields['meta_author']; ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['meta_author']).'</span>';
                ?>
            </p>
            <p>
                <label>Copyright:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_meta_copyright" value="<? echo htmlentities($workspace->fields['meta_copyright']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['meta_copyright']).'</span>';
                ?>
            </p>
            <p>
                <label>Robots:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_meta_robots" value="<? echo htmlentities($workspace->fields['meta_robots']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['meta_robots']).'</span>';
                ?>
            </p>
        </div>

        <div class="ploopi_form_title">
            <? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_FILTERING; ?>
        </div>

        <div class="ploopi_form" id="system_filtering" style="clear:both;padding:2px;">
            <p>
                <label><? echo _SYSTEM_LABEL_GROUP_ALLOWEDIP; ?>:</label>
                <?
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
                {
                    ?>
                    <input type="text" class="text" name="workspace_iprules"  value="<? echo htmlentities($workspace->fields['iprules']); ?>">
                    <?
                }
                else echo '<span>'.htmlentities($workspace->fields['iprules']).'</span>';
                ?>
                
            </p>
        </div>

        <?
        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
        {
            ?>
            <div style="clear:both;float:right;padding:4px;">
                <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
            </div>
            <?
        }
        ?>
        <div style="clear:both;float:right;padding:4px;">
        </div>
    <?
    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
    {
        ?>
        </form>
        <?
    }
    ?>

    </div>

</div>
<?
echo $skin->close_simplebloc();

echo $skin->open_simplebloc('');
ploopi_annotation(_SYSTEM_OBJECT_WORKSPACE, $workspace->fields['id'], $workspace->fields['label']);
echo $skin->close_simplebloc();
?>
