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
 * Gestion de la metabase.
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mb_schema
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_schema extends data_object
{
    function mb_schema()
    {
        parent::data_object('ploopi_mb_schema','tablesrc','tabledest','id_module_type');
    }
}

/**
 * Classe d'accès à la table ploopi_mb_relation
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_relation extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_relation
     */

    function mb_relation()
    {
        parent::data_object('ploopi_mb_relation','tablesrc','fieldsrc','tabledest','fielddest','id_module_type');
    }
}

/**
 * Classe d'accès à la table ploopi_mb_table
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_table extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_table
     */

    function mb_table()
    {
        parent::data_object('ploopi_mb_table','name','id_module_type');
    }

    /**
     * Supprime la table ainsi que les champs, les relations et le schéma associés
     */

    function delete()
    {
        global $db;

        $db->query("DELETE FROM ploopi_mb_field WHERE tablename = '".$db->addslashes($this->fields['name'])."' AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_relation WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_schema WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        parent::delete();
    }
}

/**
 * Classe d'accès à la table ploopi_mb_field
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_field extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_field
     */

    function mb_field()
    {
        parent::data_object('ploopi_mb_field','tablename','name');
    }
}

/**
 * Classe d'accès à la table ploopi_mb_object
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_object extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_object
     */

    function mb_object()
    {
        parent::data_object('ploopi_mb_object', 'id', 'id_module_type');
    }
}

/**
 * Classe d'accès à la table ploopi_mb_wce_object
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_wce_object extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_wce_object
     */

    function mb_wce_object()
    {
        parent::data_object('ploopi_mb_wce_object');
    }

}

/**
 * Classe d'accès à la table ploopi_mb_action
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_action extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_action
     */

    function mb_action()
    {
        parent::data_object('ploopi_mb_action','id_module_type','id_action');
    }

    /**
     * Enregistre l'action
     *
     * @return int identifiant de l'action
     */

    function save()
    {
        global $db;

        if ($this->new && ($this->fields['id_action'] == '' || $this->fields['id_action'] <= 0))
        {
            $answer = $db->query("select max(id_action) as maxi from ploopi_mb_action where id_module_type={$this->fields['id_module_type']}");
            $resfields=$db->fetchrow($answer);
            $this->fields['id_action']=$resfields['maxi']+1;
        }
        return(parent::save());
    }

    /**
     * Supprime l'action
     *
     * @param boolean $preserve_data false si la suppression doit se faire en cascade (rôles associés). Par défaut : false.
     */

    function delete($preserve_data = false)
    {
        include_once './include/classes/role.php';

        global $db;

        if ($this->fields['id_action']!=-1 && !$preserve_data)
        {
            $select =   "
                        SELECT  *
                        FROM    ploopi_role_action
                        WHERE   id_action = {$this->fields['id_action']}
                        AND     id_module_type = {$this->fields['id_module_type']}
                        ";

            $rs = $db->query($select);
            while ($deletefields = $db->fetchrow($rs))
            {
                $role_action = new role_action();
                $role_action->open($deletefields['id_role'],$this->fields['id_action'],$this->fields['id_module_type']);
                $role_action->delete();
            }
        }
        parent::delete();
    }
}
?>