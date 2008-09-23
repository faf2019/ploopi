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
    
    public function param()
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
            $this->arrParam[$fields['name']]['choices'][$fields['value']] = $fields['displayed_value'];
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
}

/**
 * Classe d'accès à la table ploopi_param_default
 *  
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class param_default extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return param_default
     */
    
    public function param_default()
    {
        parent::data_object('ploopi_param_default', 'id_module', 'name');
    }
}

/**
 * Classe d'accès à la table ploopi_param_type
 *  
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class param_type extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return param_default
     */
    public function param_type()
    {
        parent::data_object('ploopi_param_type', 'id_module_type', 'name');
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $preserve_data
     */
    public function delete($preserve_data = false)
    {
        global $db;

        $delete = "DELETE FROM ploopi_param_choice WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
        $db->query($delete);

        if (!$preserve_data)
        {
            $delete = "DELETE FROM ploopi_param_default WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_workspace WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_user WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);
        }

        parent::delete();
    }

    /**
     * Retourne un tableau contenant la liste des choix possibles pour le paramètre 
     *
     * @return array tableau associatif contenant la liste des choix possibles pour le paramètre 
     */
    
    public function getallchoices()
    {
        global $db;

        $arrParamChoice = array();

        $select = "SELECT * FROM ploopi_param_choice WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
        $db->query($select);
        
        while ($fields = $db->fetchrow()) $arrParamChoice[$fields['value']] = $fields['displayed_value'];

        return($arrParamChoice);
    }
}

/**
 * Classe d'accès à la table ploopi_param_choice
 *  
 * @package ploopi
 * @subpackage param
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class param_choice extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return param_choice
     */
    
    public function param_choice()
    {
        parent::data_object('ploopi_param_choice','id_module_type','name');
    }

}
