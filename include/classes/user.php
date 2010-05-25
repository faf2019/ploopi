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
 * Gestion des utilisateurs
 *
 * @package ploopi
 * @subpackage user
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table ploopi_user
 *
 * @package ploopi
 * @subpackage user
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class user extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return user
     */

    public function user()
    {
        parent::data_object('ploopi_user');
        $this->fields['date_creation'] = ploopi_createtimestamp();
    }

    /**
     * Supprime l'utilisateur et les donn�es associ�es : validation, param, share, annotation, subscription, etc..
     */

    public function delete()
    {
        include_once './include/classes/group.php';

        global $db;

        $db->query("DELETE FROM ploopi_validation WHERE type_validation = 'user' AND id_validation = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_share WHERE type_share = 'user' AND id_share = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_tag WHERE id_user = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_annotation WHERE id_user = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_param_user WHERE id_user = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_workspace_user_role WHERE id_user = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_group_user WHERE id_user = {$this->fields['id']}");
        $db->query("DELETE FROM ploopi_subscription WHERE id_user = {$this->fields['id']}");

        $select = "SELECT * FROM ploopi_workspace_user WHERE id_user = {$this->fields['id']}";
        $rs = $db->query($select);
        while($fields = $db->fetchrow($rs))
        {
            $workspace_user = new workspace_user();
            $workspace_user->open($fields['id_workspace'], $fields['id_user']);
            $workspace_user->delete();
        }

        $this->deletephoto();
        
        parent::delete();
    }
    
    /**
     * Supprime la photo associ�e � l'utilisateur
     */
    public function deletephoto()
    {
        $strPhotoPath = $this->getphotopath();
        
        if (file_exists($strPhotoPath)) unlink($strPhotoPath);        
    }

    /**
     * Retourne un tableau contenant les espaces auxquels l'utilisateur est (plus ou moins directement) rattach�
     *
     * @param boolean $lite true si la fonction ne doit renvoyer que le nom des espaces
     * @return array tableau d'espaces
     */

    public function getworkspaces($lite = false)
    {
        /**
         * 1. R�cup�re les groupes auxquels l'utilisateur est rattach�.
         * 2. A partir des groupes, r�cup�re les espaces auxquels les groupes sont rattach�s directement ou pas (on regarde les parents).
         * 3. R�cup�re les espaces auxquels l'utilisateur est directement rattach�.
         */

        global $db;

        $workspaces = array();

        // on r�cup�re l'ensemble des groupes d'utilisateurs et leurs parents
        $groups = $this->getgroups();

        if (sizeof($groups))
        {
            $parents = array();

            foreach($groups as $org)
            {
                $parents = array_merge($parents,explode(';',$org['parents']));
                $parents[] = $org['id'];
            }

            $parents = array_unique($parents);

            $groups = implode("','", $parents);

            $select =   "
                        SELECT      w.*,
                                    wg.id_group,
                                    wg.adminlevel

                        FROM        ploopi_workspace w

                        LEFT JOIN   ploopi_workspace_group wg ON wg.id_workspace = w.id

                        WHERE       wg.id_group IN ('{$groups}')
                        ";

            $result = $db->query($select);

            while ($fields = $db->fetchrow($result))
            {
                if ($lite) $workspaces[$fields['id']] = $fields['label'];
                else
                {
                    if (empty($workspaces[$fields['id']])) $workspaces[$fields['id']] = $fields;
                    else $workspaces[$fields['id']]['adminlevel'] = max($workspaces[$fields['id']]['adminlevel'], $fields['adminlevel']);

                    $workspaces[$fields['id']]['groups'][] = $fields['id_group'];
                }

            }
        }

        $select =   "
                    SELECT      w.*,
                                wu.adminlevel

                    FROM        ploopi_workspace w

                    INNER JOIN  ploopi_workspace_user wu ON wu.id_workspace = w.id

                    WHERE       wu.id_user = {$this->fields['id']}

                    ORDER BY    w.depth, id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result))
        {
            if ($lite) $workspaces[$fields['id']] = $fields['label'];
            else
            {
                if (empty($workspaces[$fields['id']])) $workspaces[$fields['id']] = $fields;
                else $workspaces[$fields['id']]['adminlevel'] = max($workspaces[$fields['id']]['adminlevel'], $fields['adminlevel']);
            }
        }

        return $workspaces;
    }

    /**
     * Retourne un tableau contenant les groupes auxquels l'utilisateur est rattach�
     *
     * @param boolean $lite true si la fonction ne doit renvoyer que le nom des groupes
     * @return array tableau de groupes
     */

    public function getgroups($lite = false)
    {
        global $db;
        
        ploopi_init_module('system', false, false, false);
        
        $select =   "
                    SELECT      g.*

                    FROM        ploopi_group_user gu

                    LEFT JOIN   ploopi_group g
                    ON          gu.id_group = g.id

                    WHERE       gu.id_user = {$this->fields['id']}

                    ORDER BY    g.depth ASC
                    ";

        $result = $db->query($select);

        $groups = array();
        while ($fields = $db->fetchrow($result))
        {
            // group 0 = virtual group SYSTEM
            if ($fields['id'] == _SYSTEM_SYSTEMADMIN) $fields['label'] = _SYSTEM_LABEL_SYSTEM;

            if ($lite) $groups[$fields['id']] = $fields['label'];
            else $groups[$fields['id']] = $fields;
        }

        return $groups;
    }

    /**
     * Attache l'utilisateur � un groupe
     *
     * @param int $groupid identifiant du groupe
     */

    public function attachtogroup($groupid)
    {
        include_once './include/classes/group.php';

        global $db;

        $group_user = new group_user();
        $group_user->fields['id_user'] = $this->fields['id'];
        $group_user->fields['id_group'] = $groupid;
        $group_user->save();
    }

    /**
     * Attache l'utilisateur � un espace de travail
     *
     * @param unknown_type $workspaceid
     */

    public function attachtoworkspace($workspaceid)
    {
        global $db;

        $workspace_user = new workspace_user();
        $workspace_user->fields['id_user'] = $this->fields['id'];
        $workspace_user->fields['id_workspace'] = $workspaceid;
        $workspace_user->save();

        // search for modules
        $select =   "
                    SELECT  m.id, m.label, mt.label as moduletype
                    FROM    ploopi_module_workspace mg,
                            ploopi_module m,
                            ploopi_module_type mt
                    WHERE   mg.id_workspace = {$workspaceid}
                    AND     mg.id_module = m.id
                    AND     m.id_module_type = mt.id
                    ";

        $db->query($select);
        while ($fields = $db->fetchrow())
        {
            $admin_userid = $this->fields['id'];
            $admin_workspaceid = $workspaceid;
            $admin_moduleid = $fields['id'];

            echo "<br><b>� {$fields['label']} �</b> ({$fields['moduletype']})<br>";
            if (file_exists("./modules/{$fields['moduletype']}/include/admin_user_create.php")) include "./modules/{$fields['moduletype']}/include/admin_user_create.php";
        }

    }

    /**
     * Retourne un tableau des actions autoris�es pour cet utilisateur.
     * $actions[id_workspace][id_module][$fields['id_action']]
     * 
     * @param array $arrActions tableau d'actions d�j� existant (optionnel)
     * @param boolean $booWithGroups true si la m�thode doit renvoyer les actions des groupes de l'utilisateurs (optionnel, false par d�faut)
     * @return array tableau des actions
     */
    
    public function getactions($arrActions = null, $booWithGroups = false)
    {
        include_once './include/classes/group.php';
        
        global $db;

        $result = $db->query("
            SELECT      ploopi_workspace_user_role.id_workspace,
                        ploopi_role_action.id_action,
                        ploopi_role.id_module
            FROM        ploopi_role_action,
                        ploopi_role,
                        ploopi_workspace_user_role
            WHERE       ploopi_workspace_user_role.id_role = ploopi_role.id
            AND         ploopi_role.id = ploopi_role_action.id_role
            AND         ploopi_workspace_user_role.id_user = {$this->fields['id']}
        ");

        while ($fields = $db->fetchrow($result)) $arrActions[$fields['id_workspace']][$fields['id_module']][$fields['id_action']] = true;
        
        if ($booWithGroups)
        {
            foreach ($this->getgroups() as $arrGroup)
            {
                $objGroup = new group();
                if ($objGroup->open($arrGroup['id']))
                {
                    $arrActions = $objGroup->getactions($arrActions);
                }
            }
        }
        
        return $arrActions;
    }
    
    /**
     * Retourne un tableau contenant les utilisateurs "visibles" par l'utilisateur
     *
     * @return tableau d'utilisateurs
     */

    public function getusersgroup()
    {
        global $db;
        $usrlist=array();
        // r�cup�ration de ts les espaces de travail
        $workspaces = array_keys($this->getworkspaces());

        // r�cup�ration de ceux qui sont attach�s directement � ceuxci
         $select =  "
                    SELECT      ploopi_workspace_user.id_user
                    FROM        ploopi_workspace_user
                    WHERE       ploopi_workspace_user.id_workspace in (".implode(",",$workspaces).")";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) array_push($usrlist,$fields['id_user']);

        // r�cup�ration de ceux qui sont attach�s par un groupe

        $select =   "
                    SELECT      distinct id_user
                    FROM        ploopi_group_user
                    INNER JOIN  ploopi_workspace_group
                    ON          ploopi_workspace_group.id_group=ploopi_group_user.id_group
                    AND     ploopi_workspace_group.id_workspace in (".implode(",",$workspaces).")";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) array_push($usrlist,$fields['id_user']);

        return($usrlist);
    }

    /**
     * Affecte un nouveau mot de passe � l'utilisateur
     *
     * @param string $strPassword mot de passe en clair
     */

    public function setpassword($strPassword)
    {
        $this->fields['password'] = md5(_PLOOPI_SECRETKEY."/{$this->fields['login']}/".md5($strPassword));
    }

    /**
     * Retourne le chemin vers la photo de l'utilisateur
     *
     * @return string chemin vers la photo de l'utilisateur
     */

    public function getphotopath()
    {
        return (_PLOOPI_PATHDATA._PLOOPI_SEP.'system'._PLOOPI_SEP.$this->fields['id'].'.png');
    }
}
?>
