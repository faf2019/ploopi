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
 * Gestion des paramètres des modules.
 * Permet de lire/écrire les paramètres d'un module à différents niveaux (utilisateur, espace de travail, système)
 *
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Cette classe permet de lire/écrire les paramètres d'un module à différents niveaux (utilisateur, espace de travail, système)
 * Accède aux tables ploopi_param_type, ploopi_param_default, ploopi_param_workspace, ploopi_param_user.
 * Met à jour les tables ploopi_param_default, ploopi_param_workspace, ploopi_param_user.
 *
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class param
{
    /**
     * Identifiant du module
     *
     * @var int
     */

    private $moduleid;

    /**
     * Identifiant de l'espace de travail
     *
     * @var int
     */

    private $workspaceid;

    /**
     * Identifiant de l'utilisateur
     *
     * @var int
     */

    private $userid;

    /**
     * Tableau des paramètres du module
     *
     * @var array
     */

    private $arrParam;

    /**
     * Constructeur de la classe
     *
     * @return param
     */

    public function __construct()
    {
        $this->moduleid = -1;
    }

    /*******************************************************************************************************

    *******************************************************************************************************/

    /**
     * Charge les parametres d'un module dans un certain contexte (espace / utilisateur / public)
     *
     * @param int $moduleid identifiant du module
     * @param int $workspaceid identifiant de l'espace
     * @param int $userid identifiant de l'utilisateur
     * @param boolean $public true si on ne veut que les paramètres publics (false par défaut)
     */
    public function open($moduleid, $workspaceid = 0, $userid = 0, $public = false)
    {
        global $db;

        $this->moduleid = $moduleid;
        $this->workspaceid = $workspaceid;
        $this->userid = $userid;

        // select default parameters
        $select =   "
                    SELECT      pd.id_module,
                                pt.id_module_type,
                                pt.name,
                                pt.label,
                                pd.value

                    FROM        ploopi_param_default pd

                    INNER JOIN  ploopi_param_type pt
                    ON          pt.name = pd.name
                    AND         pt.id_module_type = pd.id_module_type

                    WHERE       pd.id_module = {$this->moduleid}
                    ";

        if ($public) $select .= " AND pt.public = 1";
        $select .= " ORDER BY pt.name";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result)) $this->arrParam[$fields['name']] = $fields;

        // select group parameters (overide default parameters)
        if ($this->workspaceid != 0)
        {
            $select =   "
                        SELECT      pg.id_module,
                                    pt.id_module_type,
                                    pt.name,
                                    pt.label,
                                    pg.value

                        FROM        ploopi_param_workspace pg

                        INNER JOIN  ploopi_param_type pt
                        ON          pt.name = pg.name
                        AND         pt.id_module_type = pg.id_module_type

                        WHERE       pg.id_module = {$this->moduleid}
                        AND         pg.id_workspace = {$this->workspaceid}
                        ";

            if ($public) $select .= " AND pt.public = 1";
            $select .= " ORDER BY pt.label";

            $result = $db->query($select);
            while ($fields = $db->fetchrow($result)) $this->arrParam[$fields['name']] = $fields;
        }

        // select user parameters (overide user parameters)
        if ($this->userid != 0)
        {
            $select =   "
                        SELECT      pu.id_module,
                                    pt.id_module_type,
                                    pt.name,
                                    pt.label,
                                    pu.value

                        FROM        ploopi_param_user pu

                        INNER JOIN  ploopi_param_type pt
                        ON          pt.name = pu.name
                        AND         pt.id_module_type = pu.id_module_type

                        WHERE       pu.id_module = {$this->moduleid}
                        AND         pu.id_user = {$this->userid}
                        ";

            $result = $db->query($select);
            while ($fields = $db->fetchrow($result))
            {
                if (!is_null($fields['value'])) $this->arrParam[$fields['name']] = $fields;
            }
        }

        $select =   "
                    SELECT      pc.*

                    FROM        ploopi_param_choice pc

                    INNER JOIN  ploopi_module
                    ON          pc.id_module_type = ploopi_module.id_module_type
                    AND         ploopi_module.id = {$this->moduleid}
                    ";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            if (isset($this->arrParam[$fields['name']])) $this->arrParam[$fields['name']]['choices'][$fields['value']] = $fields['displayed_value'];
        }

    }

    /**
     * Affecte de nouvelles valeurs aux paramètres en fonction d'un tableau associatif de valeurs
     *
     * @param array $values tableau associatif contenant les nouvelles valeurs des paramètres
     */

    public function setvalues($values)
    {
        foreach($values as $name => $value)
        {
            if (isset($this->arrParam[$name])) $this->arrParam[$name]['value'] = $value;
        }
    }

    /**
     * Retourne les valeurs des paramètres dans un tableau associatif (nom => valeur)
     *
     * @return array tableau des valeurs des paramètres
     */

    public function getvalues()
    {
        return($this->arrParam);
    }

    /**
     * Retourne la valeur d'un paramètre
     *
     * @param string $param nom du paramètre
     * @return string valeur du paramètre
     */

    public function getparam($param)
    {
        return((isset($this->arrParam[$param])) ? $this->arrParam[$param]['value'] : null);
    }

    /**
     * Enregistre les parametres du module
     */

    public function save()
    {
        global $db;

        foreach($this->arrParam as $name => $param)
        {
            if ($this->workspaceid == 0 && $this->userid == 0) // parametres par défaut
            {
                $db->query("UPDATE ploopi_param_default SET value = '".$db->addslashes($param['value'])."' WHERE name = '".$db->addslashes($name)."' AND id_module = {$this->moduleid}");
            }
            elseif ($this->userid != 0) // parametres de l'utilisateur
            {
                $db->query("SELECT * FROM ploopi_param_user WHERE name = '".$db->addslashes($name)."' AND id_module = {$this->moduleid} AND id_user = {$this->userid}");
                if ($db->numrows())
                {
                    $db->query("UPDATE ploopi_param_user SET value = '".$db->addslashes($param['value'])."' WHERE name = '".$db->addslashes($name)."' AND id_module = {$this->moduleid} AND id_user = {$this->userid}");
                }
                else
                {
                    $db->query("INSERT INTO ploopi_param_user SET value = '".$db->addslashes($param['value'])."', name = '".$db->addslashes($name)."', id_module = {$this->moduleid}, id_user = {$this->userid}, id_module_type = {$param['id_module_type']}");
                }
            }
            elseif ($this->workspaceid != 0) // parametres du groupe
            {
                $db->query("SELECT * FROM ploopi_param_workspace WHERE name = '".$db->addslashes($name)."' AND id_module = {$this->moduleid} AND id_workspace = {$this->workspaceid}");
                if ($db->numrows())
                {
                    $db->query("UPDATE ploopi_param_workspace SET value = '".$db->addslashes($param['value'])."' WHERE name = '".$db->addslashes($name)."' AND id_module = {$this->moduleid} AND id_workspace = {$this->workspaceid}");
                }
                else
                {
                    $db->query("INSERT INTO ploopi_param_workspace SET value = '".$db->addslashes($param['value'])."', name = '".$db->addslashes($name)."', id_module = {$this->moduleid}, id_workspace = {$this->workspaceid}, id_module_type = {$param['id_module_type']}");
                }
            }
        }
    }



    /**
     * Chargement des paramètres des modules
     */

    public static function load()
    {
        global $db;

        $arrParams = array();

        $listmodules = implode(',',array_keys($_SESSION['ploopi']['modules']));

        // On récupère les paramètres par défaut
        $db->query("
            SELECT      pd.id_module,
                        pt.name,
                        pt.label,
                        pd.value

            FROM        ploopi_param_default pd

            INNER JOIN  ploopi_param_type pt
            ON          pt.name = pd.name
            AND         pt.id_module_type = pd.id_module_type

            WHERE       pd.id_module IN ({$listmodules})
        ");

        while ($fields = $db->fetchrow())
        {
            $arrParams[$fields['id_module']]['default'][$fields['name']] = $fields['value'];
        }

        // On récupère les paramètres "espace de travail"
        $db->query("
            SELECT      pg.id_module,
                        pt.name,
                        pt.label,
                        pg.value,
                        pg.id_workspace

            FROM        ploopi_param_workspace pg

            INNER JOIN  ploopi_param_type pt
            ON          pt.name = pg.name
            AND         pt.id_module_type = pg.id_module_type

            WHERE       pg.id_module IN ({$listmodules})
        ");

        while ($fields = $db->fetchrow())
        {
            $arrParams[$fields['id_module']]['workspace'][$fields['id_workspace']][$fields['name']] = $fields['value'];
        }

        // On récupère les paramètres utilisateur
        if (!empty($_SESSION['ploopi']['userid']))
        {
            $db->query("
                SELECT      pu.id_module,
                            pt.name,
                            pt.label,
                            pu.value

                FROM        ploopi_param_user pu

                INNER JOIN  ploopi_param_type pt
                ON          pt.name = pu.name
                AND         pt.id_module_type = pu.id_module_type

                WHERE       pu.id_module IN ({$listmodules})
                AND         pu.id_user = {$_SESSION['ploopi']['userid']}
            ");

            while ($fields = $db->fetchrow())
            {
                $arrParams[$fields['id_module']]['user'][$fields['name']] = $fields['value'];
            }
        }


        // load params
        foreach($arrParams as $param_idmodule => $param_type)
        {
            if (!empty($param_type['default']))
                foreach($param_type['default'] as $param_name => $param_value)
                    $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;

            if (!empty($param_type['workspace'][$_SESSION['ploopi']['backoffice']['workspaceid']]))
                foreach($param_type['workspace'][$_SESSION['ploopi']['backoffice']['workspaceid']] as $param_name => $param_value)
                    $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;

            if (!empty($param_type['user']))
                foreach($param_type['user'] as $param_name => $param_value)
                    $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;
        }
    }





    /**
     * Retourne un paramètre de module
     *
     * @param string $strParamName nom du paramètre à lire
     * @param int $intModuleId identifiant du module (optionnel, le module courant si non défini)
     * @return string valeur du paramètre
     */
    public static function get($strParamName, $intModuleId = null)
    {
        if (is_null($intModuleId) && isset($_SESSION['ploopi']['moduleid'])) $intModuleId = $_SESSION['ploopi']['moduleid'];

        if (is_null($intModuleId)) return null;

        return isset($_SESSION['ploopi']['modules'][$intModuleId][$strParamName]) ? $_SESSION['ploopi']['modules'][$intModuleId][$strParamName] : null;
    }
}
