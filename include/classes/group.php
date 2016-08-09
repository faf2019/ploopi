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
 * Gestion des groupes d'utilisateurs.
 *
 * @package ploopi
 * @subpackage group
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_group
 *
 * @package ploopi
 * @subpackage group
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class group extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return group
     */

    public function __construct()
    {
        parent::__construct('ploopi_group');
    }

    /**
     * Enregistre le groupe et met à jour le champ 'depth'
     *
     * @return int identifiant du groupe
     */

    public function save()
    {
        $this->fields['depth'] = sizeof(explode(';',$this->fields['parents']));
        return parent::save();
    }

    /**
     * Supprime le groupe
     */

    public function delete()
    {
        global $db;

        if ($this->fields['id'] != -1 && !$this->fields['system'])
        {
            // supprime les rattachements groupes/utilisateurs
            $delete = "DELETE FROM ploopi_group_user WHERE id_group = {$this->fields['id']}";
            $db->query($delete);

            parent::delete();
        }
    }

    /**
     * Retourne un tableau contenant tous les identifiants des groupes fils du groupe
     *
     * @return array tableau des groupes fils du groupe (identifiants)
     */

    public function getchildren()
    {
        global $db;

        $rs = $db->query("SELECT id FROM ploopi_group WHERE system = 0 AND parents = '{$this->fields['parents']};{$this->fields['id']}' OR parents LIKE '{$this->fields['parents']};{$this->fields['id']};%'");

        return $db->getarray($rs, true);
    }

    /**
     * Retourne un tableau contenant tous les identifiants des groupes frères du groupe
     *
     * @return array tableau des groupes frères du groupe (identifiants)
     */

    public function getbrothers()
    {
        global $db;

        $rs = $db->query("
            SELECT  id
            FROM    ploopi_group
            WHERE   id_group = {$this->fields['id_group']}
            AND     id <> {$this->fields['id']}
        ");

        return $db->getarray($rs, true);
    }

    /**
     * Retourne un tableau associatif (id => fields) contenant tous les parents du groupe
     *
     * @return array tableau des parents du groupe
     */

    public function getparents()
    {
        return system_getparents($this->fields['parents'], 'group');
    }

    /**
     * Retourne le groupe père ou false
     *
     * @return group le groupe père ou false
     */

    public function getfather()
    {
        $father = new group();
        if ($father->open($this->fields['id_group'])) return $father;
        else return(false);
    }

    /**
     * Retourne un tableau associatif (id => fields) contenant les utilisateurs du groupe (non récursif)
     *
     * @return array tableau des utilisateurs
     */

    public function getusers()
    {
        global $db;

        $users = array();

        $select =   "
                    SELECT  ploopi_user.*

                    FROM    ploopi_user,
                            ploopi_group_user

                    WHERE   ploopi_group_user.id_group = {$this->fields['id']}
                    AND     ploopi_group_user.id_user = ploopi_user.id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;

        return $users;
    }

    /**
     * Retourne le nombre d'utilisateurs dans le groupe
     *
     * @return int
     */
    public function countusers()
    {
        global $db;

        $result = $db->query("
            SELECT  count(id_user) as c

            FROM    ploopi_group_user

            WHERE   id_group = {$this->fields['id']}
        ");

        $fields = $db->fetchrow($result);

        return $fields['c'];
    }

    /**
     * Crée un double du groupe
     *
     * @return group
     */

    public function createclone()
    {
        $clone = new group();
        $clone->fields = $this->fields;
        unset($clone->fields['id']);
        $clone->fields['label'] = 'Clone de '.$this->fields['label'];
        $clone->fields['system'] = 0;
        return($clone);
    }

    /**
     * Attache un espace de travail au groupe
     *
     * @param int $workspaceid identifiant de l'espace de travail
     */

    public function attachtogroup($workspaceid)
    {
        include_once './include/classes/workspace.php';

        global $db;

        $workspace_group = new workspace_group();
        $workspace_group->fields['id_group'] = $this->fields['id'];
        $workspace_group->fields['id_workspace'] = $workspaceid;
        $workspace_group->save();
    }

    /**
     * Retourne un tableau des actions autorisées pour ce groupe.
     * $actions[id_workspace][id_module][$fields['id_action']]
     *
     * @param array $actions tableau d'actions déjà existant (optionnel)
     * @return array tableau des actions
     */

    public function getactions($actions = null)
    {
        global $db;

        $select =   "
                    SELECT      ploopi_workspace_group_role.id_workspace,
                                ploopi_role_action.id_action,
                                ploopi_role.id_module
                    FROM        ploopi_role_action,
                                ploopi_role,
                                ploopi_workspace_group_role
                    WHERE       ploopi_workspace_group_role.id_role = ploopi_role.id
                    AND         ploopi_role.id = ploopi_role_action.id_role
                    AND         ploopi_workspace_group_role.id_group = {$this->fields['id']}
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $actions[$fields['id_workspace']][$fields['id_module']][$fields['id_action']] = true;

        return $actions;
    }
}

/**
 * Classe d'accès à la table ploopi_group_user
 *
 * @package ploopi
 * @subpackage group
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class group_user extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return group_user
     */
    public function __construct()
    {
        parent::__construct('ploopi_group_user','id_group','id_user');
    }
}
