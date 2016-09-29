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

namespace ovensia\ploopi;

use ovensia\ploopi;

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
