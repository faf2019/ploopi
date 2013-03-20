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
 * Gestion des rôles.
 *
 * @package ploopi
 * @subpackage role
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_role
 *
 * @package ploopi
 * @subpackage role
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class role extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return role
     */

    public function role()
    {
        parent::data_object('ploopi_role');
    }

    /**
     * Enregistre un rôle
     *
     * @param array $actions tableau des actions qui constituent le rôle
     * @param int $id_module_type identifiant du type de module concerné
     */

    public function save($actions, $id_module_type)
    {
        global $db;

        parent::save();

        $delete =   "
                    DELETE
                    FROM    ploopi_role_action
                    WHERE   id_role = {$this->fields['id']}
                    ";

        $db->query($delete);

        foreach($actions as $key => $id_action)
        {
            $role_action = new role_action();
            $role_action->fields['id_role'] = $this->fields['id'];
            $role_action->fields['id_action'] = $id_action;
            $role_action->fields['id_module_type'] = $id_module_type;
            $role_action->save();
        }
    }

    /**
     * Supprime le rôle
     */

    public function delete()
    {
        global $db;

        $delete = "DELETE FROM ploopi_role_action WHERE id_role = ".$this->fields['id'];
        $db->query($delete);

        parent::delete();
    }

    /**
     * Retourne un tableau contenant les actions d'un rôle
     *
     * @return array tableau contenant les actions d'un rôle
     */

    public function getactions()
    {
        global $db;

        $arrActions = array();

        $select =   "
                    SELECT      ploopi_mb_action.*,
                                ploopi_role_action.id_action

                    FROM        ploopi_role_action

                    LEFT JOIN       ploopi_mb_action
                    ON          ploopi_role_action.id_action = ploopi_mb_action.id_action
                    AND         ploopi_role_action.id_module_type = ploopi_mb_action.id_module_type

                    WHERE       ploopi_role_action.id_role = {$this->fields['id']}

                    ORDER BY    ploopi_mb_action.label
                    ";

        $result = $db->query($select);

        while ($row = $db->fetchrow($result)) $arrActions[$row['id_action']] = $row;

        return $arrActions;
    }

}

/**
 * Classe d'accès à la table ploopi_role_action
 *
 * @package ploopi
 * @subpackage role
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class role_action extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return role_action
     */

    public function role_action()
    {
        parent::data_object('ploopi_role_action','id_role','id_action','id_module_type');
    }

}
