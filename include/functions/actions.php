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
 * Gestion des actions utilisateur.
 * Lecture/Ecriture dans le log des actions exécutées, récupération de la liste des utilisateurs pouvant exécuter une action déterminée.
 *
 * @package ploopi
 * @subpackage action
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Enregistre le log d'une action utilisateur
 *
 * @param int $id_action identifiant de l'action
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module_type identifiant du type de module
 * @param int $id_module identifiant du module
 */

function ploopi_create_user_action_log($id_action, $id_record, $id_module_type = -1, $id_module = -1)
{
    include_once './include/classes/log.php';
    include_once './include/classes/mb.php';

    global $db;

    if ($id_module_type == -1) $id_module_type = $_SESSION['ploopi']['moduletypeid'];
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];



    $user_action_log = new user_action_log();

    $user_action_log->fields['user'] = isset($_SESSION['ploopi']['user']) ? trim("{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']} ({$_SESSION['ploopi']['user']['login']})") : '';

    $user_action_log->fields['workspace'] = isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]) ? $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label'] : '';

    if (isset($_SESSION['ploopi']['modules'][$id_module]))
    {
        $user_action_log->fields['module'] = $_SESSION['ploopi']['modules'][$id_module]['label'];
        $user_action_log->fields['module_type'] = $_SESSION['ploopi']['modules'][$id_module]['moduletype'];

        $action = new mb_action();
        if ($action->open($id_module_type, $id_action)) $user_action_log->fields['action'] = $action->fields['label'];
        else $user_action_log->fields['action'] = '';
    }
    else
    {
        $user_action_log->fields['module'] = '';
        $user_action_log->fields['module_type'] = '';
        $user_action_log->fields['action'] = '';
    }

    $user_action_log->fields['id_user'] = isset($_SESSION['ploopi']['userid']) ? $_SESSION['ploopi']['userid'] : 0;
    $user_action_log->fields['id_action'] = $id_action;
    $user_action_log->fields['id_workspace'] = isset($_SESSION['ploopi']['workspaceid']) ? $_SESSION['ploopi']['workspaceid'] : 0;
    $user_action_log->fields['id_module_type'] = $id_module_type;
    $user_action_log->fields['id_module'] = $id_module;
    $user_action_log->fields['id_record'] = $id_record;
    $user_action_log->fields['ip'] = (empty($_SESSION['ploopi']['remote_ip'])) ? '' : implode(',', $_SESSION['ploopi']['remote_ip']);
    $user_action_log->fields['timestp'] = ploopi_createtimestamp();
    $user_action_log->save();
}

/**
 * Renvoie le log pour une action ou un objet
 *
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_object identifiant de l'objet
 * @param int $id_action identifiant de l'action
 * @param int $id_module_type identifiant du type de module
 * @param int $id_module identifiant du module
 * @param int $limit_offset valeur inférieur de la clause LIMIT
 * @param int $limit_count nombre de lignes de log à renvoyer
 * @return array tableau contenant la liste des actions
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

/**
 * Renvoie un tableau des utilisateurs qui peuvent exécuter une action
 *
 * @param int $id_action identifiant de l'action
 * @param int $id_module_type identifiant du type de module
 * @return array tableau des utilisateurs (tableau indexé d'id)
 */

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
