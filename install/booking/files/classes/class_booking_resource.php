<?php
/*
    Copyright (c) 2008 Ovensia
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
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table 'ploopi_mod_booking_resource'
 *
 * @package booking
 * @subpackage resource
 * @author St�phane Escaich
 * @copyright OVENSIA
 */

class booking_resource extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return booking_resource
     */

    public function booking_resource()
    {
        parent::data_object('ploopi_mod_booking_resource', 'id');
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

        global $db;

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

        // On r�cup�re les espaces gestionnaires de la ressource
        $arrWorkspaces = $this->getworkspaces();

        foreach($arrWorkspaces as $intIdWsp)
        {
            $objWorkspace = new workspace();
            if ($objWorkspace->open($intIdWsp))
            {
                // On r�cup�re les utilisateurs des espaces gestionnaires
                foreach($objWorkspace->getusers(true) as $arrUser)
                {
                    if (!isset($arrUsers[$arrUser['id']])) // Utilisateur non s�lectionn�
                    {
                        $objUser = new user();

                        if ($objUser->open($arrUser['id']))
                        {
                            // S'il n'est pas administrateur syst�me, on v�rifie les actions dont il dispose
                            $arrActions = $objUser->getactions(null, true);

                            // Si l'utilisateur dispose de l'action de validation sur le module booking dans l'espace gestionnaire
                            if (isset($arrActions[$intIdWsp][$objEvent->fields['id_module']][_BOOKING_ACTION_VALIDATE]))
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
