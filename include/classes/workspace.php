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
 * Gestion des espaces de travail
 *
 * @package ploopi
 * @subpackage workspace
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès à la table ploopi_workspace
 *
 * @package ploopi
 * @subpackage workspace
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace extends data_object
{
    // Stockage temporaire des résultats
    // Utile si plusieurs appels à une même fonction dans l'exécution de la page
    // Trop lourd à stocker en session
    private static $_arrCache = array();

    /**
     * Constructeur de la classe
     *
     * @return workspace
     */

    public function __construct()
    {
        parent::__construct('ploopi_workspace');
    }

    /**
     * Enregistre l'espace de travail. Met à jour la profondeur de l'espace.
     *
     * @return boolean true si l'enregistrement a été correctement effectué
     */

    public function save()
    {
        $this->fields['depth'] = sizeof(explode(';',$this->fields['parents']));
        return(parent::save());
    }

    /**
     * Supprime l'espace de travail
     */

    public function delete()
    {
        $db = loader::getdb();

        if ($this->fields['id']!=-1 && !$this->fields['system'])
        {
            include_once './modules/system/include/functions.php';

            $fatherid = $this->fields['id_workspace'];

            // attach children to new father
            $select =
                "
                SELECT  ploopi_workspace.id
                FROM    ploopi_workspace
                WHERE   ploopi_workspace.id_workspace = {$this->fields['id']}
                ";

            $result = $db->query($select);

            while ($child =  $db->fetchrow($result))
            {
                $update =
                    "
                    UPDATE  ploopi_workspace
                    SET     ploopi_workspace.id_workspace = {$fatherid}
                    WHERE   ploopi_workspace.id = {$child['id']}
                    ";

                $db->query($update);
            }

            // update parents workspace
            system_updateparents();

            $db->query("DELETE FROM ploopi_workspace_user WHERE id_workspace = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_workspace_group WHERE id_workspace = {$this->fields['id']}");

            parent::delete();

        }
    }

    /**
     * Retourne un tableau contenant tous les identifiants des espaces fils de l'espace
     *
     * @return array tableau des espaces fils de l'espace (identifiants)
     */

    public function getchildren()
    {
        if (isset(self::$_arrCache[$this->fields['id']]['children'])) return self::$_arrCache[$this->fields['id']]['children'];

        $db = loader::getdb();

        $rs = $db->query("SELECT id FROM ploopi_workspace WHERE parents = '{$this->fields['parents']};{$this->fields['id']}' OR parents LIKE '{$this->fields['parents']};{$this->fields['id']};%'");

        return self::$_arrCache[$this->fields['id']]['children'] = $db->getarray($rs, true);
    }

    /**
     * Retourne un tableau contenant tous les identifiants des espaces frères de l'espace
     *
     * @return array tableau des espaces frères de l'espace (identifiants)
     */

    public function getbrothers()
    {
        if (isset(self::$_arrCache[$this->fields['id']]['brothers'])) return self::$_arrCache[$this->fields['id']]['brothers'];

        $db = loader::getdb();

        $rs = $db->query("
            SELECT  id
            FROM    ploopi_workspace
            WHERE   id_workspace = {$this->fields['id_workspace']}
            AND     id <> {$this->fields['id']}
        ");

        return self::$_arrCache[$this->fields['id']]['brothers'] = $db->getarray($rs, true);
    }

    /**
     * Retourne un tableau associatif (id => fields) contenant tous les parents de l'espace
     *
     * @return array tableau des parents de l'espace
     */

    public function getparents()
    {
        include_once './modules/system/include/functions.php';
        return system_getparents($this->fields['parents'], 'workspace');
    }

    /**
     * Retourne l'espace père ou false
     *
     * @return workspace l'espace père ou false
     */

    public function getfather()
    {
        $father = new workspace();
        if ($father->open($this->fields['id_workspace'])) return $father;
        else return(false);
    }

    /**
     * Retourne un tableau associatif (id => fields) contenant les utilisateurs de l'espace (non récursif)
     *
     * @param boolean $booWithGroups true si la fonction doit renvoyer les utilisateurs des groupes rattachés (false par défaut)
     * @return array tableau des utilisateurs
     */

    public function getusers($booWithGroups = false)
    {
        $db = loader::getdb();

        $arrUsers = array();

        $result = $db->query("
            SELECT  u.*,
                    wu.adminlevel

            FROM    ploopi_user u,
                    ploopi_workspace_user wu
            WHERE
                    wu.id_workspace = {$this->fields['id']}
            AND     wu.id_user = u.id
        ");

        while ($fields = $db->fetchrow($result)) $arrUsers[$fields['id']] = $fields;

        if ($booWithGroups)
        {
            $result = $db->query("
                SELECT  u.*,
                        wg.adminlevel

                FROM    ploopi_user u,
                        ploopi_workspace_group wg,
                        ploopi_group_user gu

                WHERE   wg.id_workspace = {$this->fields['id']}
                AND     wg.id_group = gu.id_group
                AND     gu.id_user = u.id

                GROUP BY u.id
            ");

            while ($fields = $db->fetchrow($result)) $arrUsers[$fields['id']] = $fields;
        }

        return $arrUsers;
    }

    /**
     * Retourne le nombre d'utilisateurs dans l'espace de travail (sans les groupes)
     *
     * @return int
     */
    public function countusers()
    {
        $db = loader::getdb();

        $result = $db->query("
            SELECT  count(id_user) as c

            FROM    ploopi_workspace_user

            WHERE   id_workspace = {$this->fields['id']}
        ");

        $fields = $db->fetchrow($result);

        return $fields['c'];
    }

    /**
     * Retourne un tableau associatif (id => fields) contenant les utilisateurs de l'espace et des sous-espaces
     *
     * @return array tableau des utilisateurs
     */

    public function getallusers()
    {
        $db = loader::getdb();

        $users = array();

        $arrChildren = array();
        $arrChildren = $this->getchildren();
        $arrChildren[] = $this->fields['id'];

        $select =   "
                    SELECT  u.*

                    FROM    ploopi_user u,
                            ploopi_workspace_user wu

                    WHERE   wu.id_workspace IN (".implode(', ', $arrChildren).")
                    AND     wu.id_user = u.id

                    GROUP BY u.id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;

        $select =   "
                    SELECT  u.*

                    FROM    ploopi_user u,
                            ploopi_workspace_group wg,
                            ploopi_group_user gu

                    WHERE   wg.id_workspace IN (".implode(', ', $arrChildren).")
                    AND     wg.id_group = gu.id_group
                    AND     gu.id_user = u.id

                    GROUP BY u.id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;

        return $users;
    }

    /**
     * Retourne un tableau associatif (id => fields) des groupes rattachés à l'espace.
     *
     * @param boolean $getchildren true si la fonction doit renvoyer les sous-groupes (false par défaut)
     * @return array tableau associatif des groupes rattachés à l'espace
     */

    public function getgroups($getchildren = false)
    {
        $db = loader::getdb();

        $groups = array();

        $sql =  "
                    SELECT  ploopi_group.*,
                            ploopi_workspace_group.adminlevel
                    FROM    ploopi_workspace_group,
                            ploopi_group
                    WHERE   ploopi_workspace_group.id_workspace = {$this->fields['id']}
                    AND     ploopi_workspace_group.id_group = ploopi_group.id
                    ";

        $result = $db->query($sql);

        while ($fields = $db->fetchrow($result))
        {
            $groups[$fields['id']] = $fields;
            if ($getchildren)
            {
                $sql =  "
                        SELECT  *
                        FROM    ploopi_group
                        WHERE   parents LIKE '{$fields['parents']};{$fields['id']};%'
                        OR      parents = '{$fields['parents']};{$fields['id']}'
                        ";

                $res_children = $db->query($sql);
                while ($fields = $db->fetchrow($res_children))
                {
                    if (empty($groups[$fields['id']])) $groups[$fields['id']] = $fields;
                }
            }
        }

        return $groups;
    }

    /**
     * Crée un double de l'espace
     *
     * @return workspace
     */

    public function createclone()
    {
        $clone = new workspace();
        $clone->fields = $this->fields;
        unset($clone->fields['id']);
        $clone->fields['label'] = 'clone de '.$this->fields['label'];
        $clone->fields['system'] = 0;
        return($clone);
    }

    /**
     * Retourne un tableau contenant les modules rattachés à l'espace
     *
     * @param boolean $light true si la fonction ne doit renvoyer que les identifiants des modules (false par défaut)
     * @return array tableau contenant les modules rattachés à l'espace
     */

    public function getmodules($light = false)
    {
        $db = loader::getdb();

        $modules = array();

        $select =   "
                    SELECT  ploopi_module_type.*,
                            ploopi_module.label AS instancename,
                            ploopi_module.id AS instanceid,
                            ploopi_module.id_workspace As instanceworkspace,
                            ploopi_module.active,
                            ploopi_module.visible,
                            ploopi_module.shared,
                            ploopi_module.herited,
                            ploopi_module.adminrestricted,
                            ploopi_module.public,
                            ploopi_module.viewmode,
                            ploopi_module.transverseview,
                            ploopi_module.id_module_type,
                            ploopi_module_workspace.position,
                            ploopi_module_workspace.blockposition
                    FROM    ploopi_module_type,
                            ploopi_module,
                            ploopi_module_workspace
                    WHERE   ploopi_module_workspace.id_workspace = {$this->fields['id']}
                    AND     ploopi_module_workspace.id_module = ploopi_module.id
                    AND     ploopi_module.id_module_type = ploopi_module_type.id
                    ORDER BY ploopi_module_workspace.position
                    ";

        $result = $db->query($select);

        while ($module = $db->fetchrow($result))
        {
            if ($light) $modules[] = $module['instanceid'];
            else $modules[$module['instanceid']] = $module;
        }

        return $modules;
    }

    /**
     * Retourne un tableau des modules partagés (ou hérités) par les espaces parents
     *
     * @param boolean $herited true si la fonction doit renvoyer les modules hérités (false par défaut)
     * @return array tableau des modules partagés (ou hérités) par les espaces parents
     */

    public function getsharedmodules($herited = false)
    {
        $db = loader::getdb();

        $modules = array();

        $parents = str_replace(';',',',$this->fields['parents']);

        if ($parents!='')
        {
            if ($herited) $sql_herited = 'AND m.herited = 1';
            else $sql_herited = '';

            $select =   "
                        SELECT      m.id,
                                    m.label,
                                    m.id_workspace,
                                    w.label as workspacelabel,
                                    mt.label as moduletype,
                                    mt.description

                        FROM        ploopi_module m

                        INNER JOIN  ploopi_workspace w
                        ON          w.id = m.id_workspace

                        INNER JOIN  ploopi_module_type mt
                        ON          mt.id = m.id_module_type

                        WHERE       w.id IN ({$parents})
                        AND         m.shared = 1
                        {$sql_herited}
                        ORDER BY    m.label,
                                    workspacelabel
                        ";

            $result = $db->query($select);

            while($module = $db->fetchrow($result)) $modules[$module['id']] = $module;
        }
        return $modules;
    }
}
