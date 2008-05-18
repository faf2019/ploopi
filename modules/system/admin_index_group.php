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
 * Interface de modification d'un groupe d'utilisateurs.
 * Permet de copier/cloner/supprimer.
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Affichage du bloc
 */

echo $skin->open_simplebloc(); 
?>

<div>
    <div class="system_group_icons">
        <div class="system_group_icons_padding">
            <?
            $nbusers = sizeof($group->getusers());
            $nbgroups = (!empty($groups['tree'][$groupid])) ? sizeof($groups['tree'][$groupid]) : 0;
            ?>
            <div style="padding:4px;">
                Ce groupe est composé de
                <br /><strong><? echo $nbgroups; ?> groupe(s)</strong>
                <br /><strong><? echo $nbusers; ?> utilisateur(s)</strong>
            </div>
            <?
            $toolbar_group[] = array(
                                    'title'     => str_replace('<LABEL>','<br /><b>'.$childgroup.'</b>', _SYSTEM_LABEL_CREATE_CHILD),
                                    'url'       => "$scriptenv?op=child&groupid=$groupid",
                                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_child.png"
                                );

            $toolbar_group[] = array(
                                    'title'     => str_replace('<LABEL>','<br /><b>'.$currentgroup.'</b>', _SYSTEM_LABEL_CREATE_CLONE),
                                    'url'       => "$scriptenv?op=clone&groupid=$groupid",
                                    'icon'      => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_copy.png"
                                );

            $sizeof_groups = sizeof($group->getgroupchildrenlite());
            $sizeof_users = sizeof($group->getusers());

            // delete button if group not protected and no children


            // if (!$group->fields['protected'] && !$sizeof_groups && !$sizeof_users)
            if (!$sizeof_groups && !$sizeof_users)
            {
                $toolbar_group[] = array(
                                        'title'     => str_replace('<LABEL>','<br /><b>'.$currentgroup.'</b>', _SYSTEM_LABEL_DELETE_GROUP),
                                        'url'       => "$scriptenv?op=delete&groupid=$groupid",
                                        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_delete.png",
                                        'confirm'   => _SYSTEM_MSG_CONFIRMGROUPDELETE
                                    );
            }
            else
            {
                if ($sizeof_groups || $sizeof_users)
                {
                    $msg = '';
                    if ($sizeof_groups) $msg = _SYSTEM_MSG_INFODELETE_GROUPS;
                    elseif ($sizeof_users) $msg = _SYSTEM_MSG_INFODELETE_USERS;

                    $toolbar_group[] = array(
                                            'title'     => str_replace('<LABEL>','<br /><b>'.$currentgroup.'</b>', _SYSTEM_LABEL_DELETE_GROUP),
                                            'url'       => $scriptenv,
                                            'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_delete_gray.png",
                                            'confirm'   => $msg
                                        );
                }
            }

            echo $skin->create_toolbar($toolbar_group, $x=0, false, true);
            ?>
        </div>
    </div>

    <div class="system_group_main">
        <?
        if ($father = $group->getfather())
        {
            $parentlabel = $father->fields['label'];
            $parentid = $father->fields['id'];
        }
        else
        {
            $parentlabel = 'Racine';
            $parentid = '';
        }

        $users = $group->getusers();
        $nbusers = sizeof($users);

        $templatelist_back = ploopi_getavailabletemplates('backoffice');
        $templatelist_front = ploopi_getavailabletemplates('frontoffice');
        ?>
        <form name="" action="<? echo $scriptenv; ?>" method="POST" onsubmit="javascript:return system_group_validate(this);">
        <input type="hidden" name="op" value="save_group">
        <input type="hidden" name="group_id" value="<? echo $group->fields['id']; ?>">

            <div class="ploopi_form_title">
                <? echo $group->fields['label']; ?> &raquo;
                <?
                    echo _SYSTEM_LABEL_GROUP_MODIFY;
                ?>
            </div>
            <div class="ploopi_form" style="clear:both;padding:2px">
                <p>
                    <label><? echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
                    <input type="text" class="text" name="group_label"  value="<? echo $group->fields['label']; ?>">
                </p>
                <p>
                    <label><? echo _SYSTEM_LABEL_GROUP_SHARED; ?>:</label>
                    <input style="width:16px;" type="checkbox" name="group_shared" <? if($group->fields['shared']) echo "checked"; ?> value="1">(disponible pour les sous-espaces)
                </p>
            </div>
            <div style="clear:both;float:right;padding:4px;">
                <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
            </div>
        </form>
    </div>
</div>
<?
echo $skin->close_simplebloc();

echo $skin->open_simplebloc();
ploopi_annotation(_SYSTEM_OBJECT_GROUP, $group->fields['id'], $group->fields['label']);
echo $skin->close_simplebloc();
?>
