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
 * Gestion des modules.
 *
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_module
 *
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class module extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return module
     */
    public function __construct()
    {
        parent::__construct('ploopi_module','id');
    }

    /**
     * Instancie/Modifie le module
     *
     * @return int identifiant du module
     */

    public function save()
    {
        global $db;

        $res = -1;

        if ($this->new) // insert
        {
            $res = parent::save();

            // insert default parameters
            $insert = "INSERT INTO ploopi_param_default SELECT {$this->fields['id']}, name, default_value, id_module_type FROM ploopi_param_type WHERE id_module_type = ".$this->fields['id_module_type'];
            $db->query($insert);

            // todo when new module
            $objModuleType = new module_type();
            if ($objModuleType->open($this->fields['id_module_type']))
            {
                $admin_moduleid = $this->fields['id'];
                // script to execute to create specific module data
                if (file_exists("./modules/{$objModuleType->fields['label']}/include/create.php")) include "./modules/{$objModuleType->fields['label']}/include/create.php";
                elseif (file_exists("./modules/{$objModuleType->fields['label']}/include/admin_instance_create.php")) include "./modules/{$objModuleType->fields['label']}/include/admin_instance_create.php";
            }
        }
        else $res = parent::save();

        return($res);
    }

    /**
     * Supprime l'instance de module et les données associées : rôles, partages, abonnements, validation, etc...
     */

    public function delete()
    {
        include_once './include/classes/workspace.php';

        global $db;

        if ($this->fields['id']!=-1)
        {
            // delete specific data of the module (call to delete function of the module)
            $select = "SELECT ploopi_module_type.label FROM ploopi_module_type, ploopi_module WHERE ploopi_module_type.id = ploopi_module.id_module_type AND ploopi_module.id = ".$this->fields['id'];
            $answer = $db->query($select);
            if ($fields = $db->fetchrow($answer))
            {
                $admin_moduleid = $this->fields['id'];
                // script to execute to delete specific module data
                if (file_exists("./modules/{$fields['label']}/include/delete.php")) include "./modules/{$fields['label']}/include/delete.php";
                elseif (file_exists("./modules/{$fields['label']}/include/admin_instance_delete.php")) include "./modules/{$fields['label']}/include/admin_instance_delete.php";
            }

            // delete all module_workspace
            $workspaces = $this->getallworkspaces();

            foreach($workspaces as $idw => $workspace)
            {
                $module_workspace  = new module_workspace();
                $module_workspace->open($idw,$workspace['id_module']);
                $module_workspace->delete();
            }

            // delete params (default, workspace, user)
            $delete = "DELETE FROM ploopi_param_default WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_workspace WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_user WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            // delete roles
            $delete = "DELETE FROM ploopi_role WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            // delete share
            $delete = "DELETE FROM ploopi_share WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            // delete subscription
            $delete = "DELETE FROM ploopi_subscription WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            // delete validation
            $delete = "DELETE FROM ploopi_validation WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);
        }

        parent::delete();

    }

    /**
     * Retourne un tableau contenant les espaces de travail auxquels le module est rattaché
     *
     * @return array tableau des espaces de travail
     */

    public function getallworkspaces()
    {
        global $db;

        $workspaces = array();

        $select =   "
                SELECT  *
                FROM    ploopi_module_workspace
                WHERE   id_module = {$this->fields['id']}
                ";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            $workspaces[$fields['id_workspace']] = $fields;
        }

        return($workspaces);
    }

    /**
     * Retourne un tableau contenant les rôles basés sur le module
     *
     * @param boolean $shared true si on ne veut que les rôles partagés
     * @return array tableau des rôles
     */

    public function getroles($shared = false)
    {
        global $db;

        $roles = array();

        $where = ($shared) ? " AND ploopi_role.shared = 1 " : '';

        $select =   "
                SELECT      ploopi_role.*
                FROM        ploopi_role
                WHERE       ploopi_role.id_module = {$this->fields['id']}
                {$where}
                ORDER BY    ploopi_role.label
                ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result)) $roles[$role['id']] = $role;

        return $roles;
    }

    /**
     * Détache le module d'un espace de travail donné
     *
     * @param int $workspaceid identifiant de l'espace de travail
     */

    public function unlink($workspaceid)
    {
        global $db;

        $sql =  "
                DELETE
                FROM        ploopi_module_workspace
                WHERE       ploopi_module_workspace.id_workspace = {$workspaceid}
                AND         ploopi_module_workspace.id_module = {$this->fields['id']}
                ";

        $db->query($sql);
    }

}

/**
 * Classe d'accès à la table ploopi_module_type
 *
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class module_type extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return module_type
     */

    public function __construct()
    {
        parent::__construct('ploopi_module_type');
    }

    /**
     * Supprime le type de module et les données associées : paramètres, modules, actions, métabase, etc..
     */

    public function delete()
    {
        include_once './include/classes/mb.php';
        include_once './include/classes/param.php';

        global $db;
        // delete params

        if ($this->fields['id']!=-1)
        {
            $select = "SELECT * FROM ploopi_param_type WHERE id_module_type = {$this->fields['id']}";
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $param_type = new param_type();
                $param_type->open($this->fields['id'], $deletefields['name']);
                $param_type->delete();
            }

            // delete modules

            $select = "SELECT * FROM ploopi_module WHERE id_module_type = {$this->fields['id']}";
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $module = new module();
                $module->open($deletefields['id']);
                $module->delete();
            }

            // delete actions

            $select = "SELECT * FROM ploopi_mb_action WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $mb_action = new mb_action();
                $mb_action->open($this->fields['id'],$deletefields['id_action']);
                $mb_action->delete();
            }

            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$this->fields['id']}");
        }

        parent::delete();
    }

    /**
     * Supprime les paramètres du type de module. Utilisé notamment pour la mise à jour des modules.
     */

    public function delete_params()
    {
        include_once './include/classes/mb.php';
        include_once './include/classes/param.php';

        global $db;

        if ($this->fields['id']!=-1)
        {
            // delete params
            $select = "SELECT * FROM ploopi_param_type WHERE id_module_type = {$this->fields['id']}";
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $param_type = new param_type();
                $param_type->open($this->fields['id'], $deletefields['name']);
                $param_type->delete(true);
            }

            // delete actions
            $select = "SELECT * FROM ploopi_mb_action WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $mb_action = new mb_action();
                $mb_action->open($this->fields['id'],$deletefields['id_action']);
                $mb_action->delete(true);
            }

            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$this->fields['id']}");
        }
    }

    /**
     * Crée une instance de module à partir du type de module
     *
     * @param int $workspaceid identifiant de l'espace de travail auquel l'instance va être rattachée
     * @return module module instancié
     *
     * @see module
     */

    public function createinstance($workspaceid)
    {
        $position = 0;

        $module = new module();

        $module->fields['label'] = 'Nouveau_module_' . $this->fields['label'];
        $module->fields['id_module_type'] = $this->fields['id'];
        $module->fields['id_workspace'] = $workspaceid;
        $module->fields['active'] = '0';
        $module->fields['public'] = '0';
        $module->fields['shared'] = '0';

        return($module);
    }

    /**
     * Retourne un tableau contenant les actions proposées par le type de module
     *
     * @param boolean $role_enabled true si on ne veut que les actions autorisées pour la création de rôles
     * @return array tableau des actions
     */

    public function getactions($role_enabled = true)
    {
        global $db;

        $actions = array();

        $sql =  "
                SELECT      *
                FROM        ploopi_mb_action
                WHERE       id_module_type = {$this->fields['id']}
                AND         role_enabled = ".(($role_enabled) ? '1' : '0')."
                ORDER BY    id_action
                ";

        $result = $db->query($sql);

        while ($action = $db->fetchrow($result)) $actions[$action['id_action']] = $action;

        return $actions;
    }

    public function update_metabase($xmlfile_desc, $rapport = array())
    {
    }

    public function update_description($xmlfile_desc, &$rapport = array())
    {
        include_once './include/classes/param.php';
        include_once './include/classes/mb.php';
        include_once './include/classes/xml2array.php';

        global $db;

        $testok = true;
        $critical_error = false;
        $detail = '';

        if (file_exists($xmlfile_desc))
        {
            $fp = fopen($xmlfile_desc, 'r');
            $data = fread ($fp, filesize ($xmlfile_desc));
            fclose($fp);

            $x2a = new xml2array();
            $xmlarray = $x2a->parse($data);
            if ($xmlarray)
            {
                $pt = &$xmlarray['root']['ploopi'][0]['moduletype'][0];

                $this->delete_params();

                $this->fields =
                    array_merge(
                        $this->fields,
                        array(
                            'label'         => $pt['label'][0],
                            'version'       => $pt['version'][0],
                            'author'        => $pt['author'][0],
                            'date'          => $pt['date'][0],
                            'description'   => $pt['description'][0]
                        )
                    );

                $this->save();

                if (!empty($pt['paramtype']))
                {
                    foreach($pt['paramtype'] as $key => $value)
                    {
                        if (empty($value['default_value'][0])) $value['default_value'][0] = '';

                        $param_type = new param_type();
                        $param_type->fields =
                            array(
                                'id_module_type'    => $this->fields['id'],
                                'name'              => $value['name'][0],
                                'label'             => $value['label'][0],
                                'default_value'     => $value['default_value'][0],
                                'public'            => $value['public'][0],
                                'description'       => $value['description'][0]
                            );

                        $param_type->save();

                        // on recherche les paramètres mal initialisés (ploopi_param_default manquant)
                        $sql =  "
                                SELECT      m.id

                                FROM        ploopi_module m

                                LEFT JOIN   ploopi_param_default pd
                                ON          pd.id_module = m.id
                                AND         pd.name = '".$db->addslashes($value['name'][0])."'

                                WHERE       m.id_module_type = {$this->fields['id']}
                                AND         ISNULL(pd.name)
                                ";

                        $rs_paramdefault = $db->query($sql);

                        while ($row = $db->fetchrow($rs_paramdefault))
                        {
                            $param_default = new param_default();
                            $param_default->fields =
                                array(
                                    'id_module'         => $row['id'],
                                    'name'              => $value['name'][0],
                                    'value'             => is_null($value['default_value'][0]) ? '' : $value['default_value'][0],
                                    'id_module_type'    => $this->fields['id']
                                );

                            $param_default->save();
                        }

                        if (!empty($value['paramchoice']))
                        {
                            foreach($value['paramchoice'] as $ckey => $cvalue)
                            {
                                $param_choice = new param_choice();
                                $param_choice->fields =
                                    array(
                                        'id_module_type'    => $this->fields['id'],
                                        'name'              => $param_type->fields['name'],
                                        'value'             => $cvalue['value'][0],
                                        'displayed_value'   => $cvalue['displayed_value'][0]
                                    );
                                $param_choice->save();
                            }
                        }
                    }
                }

                if (!empty($pt['cms_object']))
                {
                    foreach($pt['cms_object'] as $key => $value)
                    {
                        $mb_cms_object = new mb_cms_object();
                        $mb_cms_object->fields =
                            array(
                                'id_module_type'    => $this->fields['id'],
                                'label'             => $value['label'][0],
                                'script'            => $value['script'][0],
                                'select_id'         => $value['select_id'][0],
                                'select_label'      => $value['select_label'][0],
                                'select_table'      => $value['select_table'][0]
                            );
                        $mb_cms_object->save();
                    }
                }

                if (!empty($pt['action']))
                {
                    foreach($pt['action'] as $key => $value)
                    {
                        $mb_action = new mb_action();
                        $mb_action->fields =
                            array(
                                'id_module_type'    => $this->fields['id'],
                                'id_action'         => $value['id_action'][0],
                                'label'             => $value['label'][0],
                                'id_object'         => (isset($value['id_object'][0])) ? $value['id_object'][0] : 0,
                                'role_enabled'      => (isset($value['role_enabled'][0])) ? $value['role_enabled'][0] : 1
                            );
                        $mb_action->save();
                    }
                }

                $detail = "Fichier '{$xmlfile_desc}' importé.";
            }
            else
            {
                $detail = "Fichier '{$xmlfile_desc}' mal formé. Vérifiez la structure XML du document.";
                $testok = false;
                $critical_error = true;
            }
        }
        else
        {
            $detail = "Fichier '{$xmlfile_desc}' non trouvé.";
            $testok = false;
            $critical_error = true;
        }

        $rapport[] = array('operation' => 'Chargement des paramètres/actions', 'detail' => $detail, 'res' => $testok);

        return $critical_error;

    }
}
