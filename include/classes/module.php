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
 * Gestion des modules.
 *
 * @package ploopi
 * @subpackage module
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès à la table ploopi_module
 *
 * @package ploopi
 * @subpackage module
 * @copyright Ovensia
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
        $db = loader::getdb();

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

        $db = loader::getdb();

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
        $db = loader::getdb();

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
        $db = loader::getdb();

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
        $db = loader::getdb();

        $sql =  "
                DELETE
                FROM        ploopi_module_workspace
                WHERE       ploopi_module_workspace.id_workspace = {$workspaceid}
                AND         ploopi_module_workspace.id_module = {$this->fields['id']}
                ";

        $db->query($sql);
    }


    /**
     * Charge l'environnement du module : variables globales, constantes, fonctions.
     * En option : fichiers javascript, feuilles de styles, entêtes (head)
     *
     * @param string $moduletype nom du module
     * @param boolean $js true si les fichiers javascript doivent être chargés
     * @param boolean $css true si les feuilles de style doivent être chargées
     * @param boolean $head true si l'entête doit être chargée
     * @return boolean true si le module a été initialisé
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function init($moduletype, $js = true, $css = true, $head = true)
    {
        global $ploopi_additional_head;
        global $ploopi_additional_javascript;
        global $template_body;

        if (is_dir($strModulePath = "./modules/{$moduletype}"))
        {
            $version = (empty($_SESSION['ploopi']['moduletypes'][$moduletype]['version'])) ? '' : '?v='.urlencode($_SESSION['ploopi']['moduletypes'][$moduletype]['version']);

            if (!defined("_PLOOPI_INITMODULE_{$moduletype}"))
            {
                define("_PLOOPI_INITMODULE_{$moduletype}",    1);

                $defaultlanguagefile = "{$strModulePath}/lang/french.php";
                $languagefile = (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'])) ? "{$strModulePath}/lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php" : '';

                $globalfile = "{$strModulePath}/include/global.php";

                if (file_exists($globalfile)) include_once($globalfile);

                if (file_exists($defaultlanguagefile)) include_once($defaultlanguagefile);

                if ($languagefile != 'french' && file_exists($languagefile)) include_once($languagefile);
            }

            if ($head)
            {
                if (!defined("_PLOOPI_INITMODULE_HEAD_{$moduletype}"))
                {
                    define("_PLOOPI_INITMODULE_HEAD_{$moduletype}",    1);

                    $headfile = "{$strModulePath}/include/head.php";

                    // GET MODULE ADDITIONAL HEAD
                    if (file_exists($headfile))
                    {
                        ob_start();
                        include $headfile;
                        $ploopi_additional_head .= ob_get_contents();
                        @ob_end_clean();
                    }
                }
            }

            if ($js)
            {
                if (!defined("_PLOOPI_INITMODULE_JS_{$moduletype}"))
                {
                    define("_PLOOPI_INITMODULE_JS_{$moduletype}",    1);

                    $jsfile_php = "{$strModulePath}/include/javascript.php";
                    $jsfile = "{$strModulePath}/include/functions.js";

                    // GET MODULE ADDITIONAL JS
                    if (file_exists($jsfile_php))
                    {
                        ob_start();
                        include $jsfile_php;
                        $ploopi_additional_javascript .= ob_get_contents();
                        @ob_end_clean();
                    }

                    // GET MODULE ADDITIONAL JS
                    if (file_exists($jsfile) && isset($template_body))
                    {
                        $template_body->assign_block_vars('module_js', array(
                            'PATH' => "{$jsfile}{$version}"
                        ));
                    }
                }
            }

            if ($css)
            {
                if (!defined("_PLOOPI_INITMODULE_CSS_{$moduletype}"))
                {
                    define("_PLOOPI_INITMODULE_CSS_{$moduletype}",    1);

                    $cssfile = "{$strModulePath}/include/styles.css";
                    $cssfile_ie = "{$strModulePath}/include/styles_ie.css";

                    // GET MODULE STYLE
                    if (file_exists($cssfile) && isset($template_body))
                    {
                        $template_body->assign_block_vars('module_css', array(
                            'PATH' => "{$cssfile}{$version}"
                        ));
                    }

                    // GET MODULE STYLE FOR IE
                    if (file_exists($cssfile_ie) && isset($template_body))
                    {
                        $template_body->assign_block_vars('module_css_ie', array(
                            'PATH' => "{$cssfile_ie}{$version}"
                        ));
                    }
                }
            }
        }
        else return false;

        return true;
    }

    /**
     * Retourne l'id du module passé en paramètre
     *
     * @param string $strModuleName nom du module
     * @param boolean $booFirstOnly true si on souhaite simplement retourner le premier id de module (cas des instances multiples)
     * @return mixed identifiant du module ou tableau des identifiants de modules ou false si aucun module
     *
     * @copyright Exyzt, Ovensia
     * @author Julio Renella, Stéphane Escaich
     */

    public static function getid($strModuleName, $booFirstOnly = true)
    {
        $arrModuleId = array();

        foreach ($_SESSION['ploopi']['workspaces'][ $_SESSION['ploopi']['workspaceid'] ]['modules'] as $intModuleId)
        {
            if ($_SESSION['ploopi']['modules'][$intModuleId]['moduletype'] == $strModuleName)
            {
                if ($booFirstOnly) return $intModuleId;
                $arrModuleId[] = $intModuleId;
            }
        }

        if (!empty($arrModuleId)) return $arrModuleId;

        return false;
    }

}

