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

namespace ploopi;

use ploopi;

/**
 * Fonctions de contrôle des droits
 *
 * @package ploopi
 * @subpackage acl
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class acl
{
    /**
     * Renvoie un tableau des utilisateurs qui peuvent exécuter une action
     *
     * @param mixed $id_action identifiant de l'action
     * @param integer $id_module identifiant du module (optionnel)
     * @param mixed $id_workspace identifiant de l'espace (optionnel)
     * @return array tableau des utilisateurs (tableau indexé d'id)
     */

    function actions_getusers($id_action, $id_module = -1, $id_workspace = -1)
    {
        $db = db::get();

        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];
        if ($id_workspace == -1) $id_workspace = $_SESSION['ploopi']['workspaceid'];
        if (!is_array($id_action)) $id_action = array($id_action);
        if (!is_array($id_workspace)) $id_workspace = array($id_workspace);

        $id_action = array_map('intval', $id_action);
        $id_module = intval($id_module);
        $id_workspace = array_map('intval', $id_workspace);

        $rs = $db->query($sql = "
            SELECT *

            FROM (
                SELECT      u.*, wu.id_workspace
                FROM        ploopi_user u
                INNER JOIN  ploopi_workspace_user wu ON wu.id_user = u.id AND wu.id_workspace IN (".implode(',', $id_workspace).")
                LEFT JOIN   ploopi_workspace_user_role wur ON wur.id_user = u.id AND wur.id_workspace = wu.id_workspace
                LEFT JOIN   ploopi_role r ON r.id = wur.id_role AND r.id_module = {$id_module}
                LEFT JOIN   ploopi_role_action ra ON ra.id_role = r.id AND ra.id_action IN (".implode(',', $id_action).")
                WHERE       wu.adminlevel = "._PLOOPI_ID_LEVEL_SYSTEMADMIN." OR NOT ISNULL(ra.id_role)

                UNION

                SELECT      u.*, wg.id_workspace
                FROM        ploopi_user u
                INNER JOIN  ploopi_group_user gu ON gu.id_user = u.id
                INNER JOIN  ploopi_workspace_group wg ON wg.id_group = gu.id_group AND wg.id_workspace IN (".implode(',', $id_workspace).")
                LEFT JOIN   ploopi_workspace_group_role wgr ON wgr.id_group = gu.id_group AND wgr.id_workspace = wg.id_workspace
                LEFT JOIN   ploopi_role r ON r.id = wgr.id_role AND r.id_module = {$id_module}
                LEFT JOIN   ploopi_role_action ra ON ra.id_role = r.id AND ra.id_action IN (".implode(',', $id_action).")
                WHERE       wg.adminlevel = "._PLOOPI_ID_LEVEL_SYSTEMADMIN." OR NOT ISNULL(ra.id_role)
            ) as us

            GROUP BY us.id
        ");

        return $db->getarray($rs, true);
    }


    /**
     * Indique si l'utilisateur courant est administrateur système (niveau maxi) dans l'espace courant
     *
     * @param int $workspaceid identifiant de l'espace (optionnel)
     * @return boolean true si l'utilisateur est administrateur système dans cet espace
     */

    public static function isadmin($workspaceid = -1)
    {
        if (!isset($_SESSION['ploopi'])) return false;

        if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['backoffice']['workspaceid']; // get session value if not defined
        return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN);
    }

    /**
     * Indique si l'utilisateur courant est gestionnaire d'espace (ou +)
     *
     * @param int $workspaceid identifiant de l'espace (optionnel)
     * @return boolean true si l'utilisateur est gestionnaire de cet espace (ou +)
     */

    public static function ismanager($workspaceid = -1)
    {
        if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['backoffice']['workspaceid']; // get session value if not defined
        return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER);
    }

    /**
     * Indique si l'utilisateur courant à la droit d'exécuter une action
     *
     * @param int $actionid identifiant de l'action (optionnel)
     * @param int $workspaceid identifiant de l'espace (optionnel)
     * @param int $moduleid identifiant du module (optionnel)
     * @return boolean true si l'utilisateur courant à la droit d'exécuter cette action
     */

    public static function isactionallowed($actionid = -1, $workspaceid = -1, $moduleid = -1)
    {
        if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

        $booAllowed = false;

        if (self::isadmin($workspaceid)) $booAllowed = true;
        else
        {
            if (is_array($actionid))
            {
                foreach($actionid as $aid)
                {
                    $booAllowed = $booAllowed || isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$aid]);
                }
            }
            else
            {
                if ($actionid == -1) $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid]);
                else $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$actionid]);
            }
        }

        return($booAllowed);
    }

    /**
     * Indique si l'utilisateur courant peut accéder à un module
     *
     * @param string $moduletype type de module
     * @param int $moduleid identifiant du module (optionnel)
     * @param int $workspaceid identidiant de l'espace (optionnel)
     * @return boolean true l'utilisateur courant peut accéder au module
     */

    public static function ismoduleallowed($moduletype, $moduleid = -1, $workspaceid = -1)
    {
        if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

        // module existe && module du type indiqué && module affecté à l'espace courant
        return(
                !empty($_SESSION['ploopi']['modules'][$moduleid])
            &&  $_SESSION['ploopi']['modules'][$moduleid]['moduletype'] == $moduletype
            &&  in_array($moduleid ,$_SESSION['ploopi']['workspaces'][$workspaceid]['modules'])
        );
    }
}
