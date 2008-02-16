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

###############################################################################
#
# user actions management
#
###############################################################################

/**
* ! description !
*
* @param int $id_action
* @param int $id_record
* @param int $id_module_type
* @param int $id_module
* @return void
*
* @version 2.09
* @since 0.1
*
* @category user action management
*/
function ploopi_create_user_action_log($id_action, $id_record, $id_module_type = -1, $id_module = -1)
{
    global $db;

    if ($id_module_type == -1) $id_module_type = $_SESSION['ploopi']['moduletypeid'];
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $user_action_log = new user_action_log();
    $user_action_log->fields['id_user'] = $_SESSION["ploopi"]["userid"];
    $user_action_log->fields['id_action'] = $id_action;
    $user_action_log->fields['id_module_type'] = $id_module_type;
    $user_action_log->fields['id_module'] = $id_module;
    $user_action_log->fields['id_record'] = $id_record;
    $user_action_log->fields['ip'] = $_SERVER['REMOTE_ADDR'];
    $user_action_log->fields['timestp'] = ploopi_createtimestamp();
    $user_action_log->save();
}

/**
* ! description !
*
* @param int $id_record
* @param int $id_action
* @param int $id_module_type
* @param int $id_module
* @return int $user_action
*
* @version 2.09
* @since 0.1
*
* @category user action management
*/
function ploopi_get_user_action_log($id_record, $id_object = -1, $id_action = -1, $id_module_type = -1, $id_module = -1, $limit_offset = 0, $limit_count = 25)
{
    global $db;

    if ($id_module_type == -1) $id_module_type = $_SESSION['ploopi']['moduletypeid'];
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $where = '';
    if ($id_action != -1) $where .= " AND ploopi_user_action_log.id_action = {$id_action}";
    if ($id_object != -1) $where .= " AND ploopi_mb_action.id_object = {$id_object}";

    $sql =  "
            SELECT      ploopi_user_action_log.*,
                        ploopi_user.id,
                        ploopi_user.lastname,
                        ploopi_user.firstname,
                        ploopi_mb_action.label

            FROM        ploopi_user_action_log

            INNER JOIN  ploopi_mb_action
            ON          ploopi_mb_action.id_action = ploopi_user_action_log.id_action
            AND         ploopi_mb_action.id_module_type = ploopi_user_action_log.id_module_type

            LEFT JOIN   ploopi_user ON ploopi_user.id = ploopi_user_action_log.id_user
            WHERE       ploopi_user_action_log.id_module_type = $id_module_type
            AND         ploopi_user_action_log.id_module = $id_module
            AND         ploopi_user_action_log.id_record = $id_record
            {$where}
            ORDER BY    timestp DESC
            LIMIT {$limit_offset}, {$limit_count}
            ";



    $result = $db->query($sql);
    $user_action = array();
    while ($fields = $db->fetchrow($result))
    {
        $user_action[] = array(
                                    'timestp'   =>  $fields['timestp'],
                                    'id_action' =>  $fields['id_action'],
                                    'action_label'  =>  $fields['label'],
                                    'id_user'   =>  $fields['id_user'],
                                    'user_name' =>  ($fields['id'] == null) ? _PLOOPI_UNKNOWNUSER : "{$fields['lastname']} {$fields['firstname']}"
                                );
    }

    return($user_action);
}

function ploopi_actions_getusers($id_action, $id_module_type = -1)
{
    global $db;

    if ($id_module_type == -1) $id_module_type = $_SESSION['ploopi']['moduletypeid'];

    $sql =  "
            SELECT  wur.id_user
            FROM    ploopi_workspace_user_role wur

            INNER JOIN  ploopi_role_action ra ON ra.id_role = wur.id_role

            WHERE   ra.id_action = {$id_action}
            AND     ra.id_module_type = {$id_module_type}
            ";

    $result = $db->query($sql);
    $users = array();
    while ($fields = $db->fetchrow($result))
    {
        $users[] = $fields['id_user'];
    }

    return($users);

}

?>
