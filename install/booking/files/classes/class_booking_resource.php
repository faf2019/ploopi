<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Gestion des ressources
 *
 * @package booking
 * @subpackage resource
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table 'ploopi_mod_booking_resource'
 *
 * @package booking
 * @subpackage resource
 * @author Stéphane Escaich
 * @copyright OVENSIA
 */

class booking_resource extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return booking_resource
     */

    public function booking_resource()
    {
        parent::__construct('ploopi_mod_booking_resource', 'id');
    }


    /**
     * Enregistre la ressource
     *
     * @return int id de la ressource
     */

    public function save()
    {
        if ($this->new) $this->setuwm();

        return parent::save();
    }

    /**
     * Retourne la liste des espaces gestionnaires de la ressource dans un tableau
     *
     * @return array tableau contenant les espaces
     */

    public function getworkspaces()
    {

        $db = ploopi\db::get();

        if (!empty($this->fields['id']))
        {
            $rs = $db->query("
                SELECT  id_workspace
                FROM    ploopi_mod_booking_resource_workspace
                WHERE   id_resource = {$this->fields['id']}
            ");

            return $db->getarray($rs, true);
        }
        else return array();

    }

    /**
     * Retourne la liste des utilisateurs gestionnaires de la ressource dans un tableau
     *
     * @return array tableau contenant les utilisateurs
     */

    public function getusers()
    {
        $arrUsers = array();

        // On récupère les espaces gestionnaires de la ressource
        $arrWorkspaces = $this->getworkspaces();

        foreach($arrWorkspaces as $intIdWsp)
        {
            $objWorkspace = new ploopi\workspace();
            if ($objWorkspace->open($intIdWsp))
            {
                // On récupère les utilisateurs des espaces gestionnaires
                foreach($objWorkspace->getusers(true) as $arrUser)
                {
                    if (!isset($arrUsers[$arrUser['id']])) // Utilisateur non sélectionné
                    {
                        $objUser = new ploopi\user();

                        if ($objUser->open($arrUser['id']))
                        {
                            // S'il n'est pas administrateur système, on vérifie les actions dont il dispose
                            $arrActions = $objUser->getactions(null, true);

                            // Si l'utilisateur dispose de l'action de validation sur le module booking dans l'espace gestionnaire
                            if (isset($arrActions[$intIdWsp][$this->fields['id_module']][_BOOKING_ACTION_VALIDATE]))
                            {
                                $arrUsers[$arrUser['id']] = $arrUser;
                            }
                        }
                    }
                }
            }
        }

        return $arrUsers;
    }

}
?>
