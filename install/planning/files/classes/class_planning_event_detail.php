<?php
/*
    Copyright (c) 2009 Ovensia
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
 * @package planning
 * @subpackage event_detail
 * @copyright Ovensia
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion des autres classes utilis�es
 */
include_once './modules/planning/classes/class_planning_event_detail_resource.php';


/**
 * Classe d'acc�s � la table 'ploopi_mod_planning_event_detail'
 *
 * @package planning
 * @subpackage event_detail
 * @author St�phane Escaich
 * @copyright Ovensia
 */

class planning_event_detail extends ploopi\data_object
{
    private $arrResource;

    /**
     * Constructeur de la classe
     *
     * @return planning_event_detail
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_mod_planning_event_detail',
            'id'
        );

        $this->initresources();
    }

    public function open(...$args)
    {
        if (parent::open($args[0]))
        {
            $db = ploopi\loader::getdb();

            $this->initresources();

            // Recherche des ressources li�es � l'�v�nement
            $db->query("SELECT * FROM ploopi_mod_planning_event_detail_resource WHERE id_event_detail = {$this->fields['id']} ORDER BY type_resource, id_resource");
            while ($row = $db->fetchrow()) $this->arrResource[$row['type_resource']][$row['id_resource']] = $row['id_resource'];

            return true;
        }
        else return false;
    }

    public function save()
    {
        if (!$this->isnew())
        {
            $db = ploopi\loader::getdb();
            // Suppression des resources associ�es au d�tail
            $db->query("DELETE FROM ploopi_mod_planning_event_detail_resource WHERE id_event_detail = {$this->fields['id']}");
        }

        // Probl�me entre heure de d�but et fin
        if ($this->fields['timestp_begin'] > $this->fields['timestp_end'])
        {
            $intTs = $this->fields['timestp_begin'];
            $this->fields['timestp_begin'] = $this->fields['timestp_end'];
            $this->fields['timestp_end'] = $intTs;
        }

        $res = parent::save();

        if (!empty($this->arrResource) && is_array($this->arrResource))
        {
            foreach($this->arrResource['user'] as $intResourceId)
            {
                $objEventDetailResource = new planning_event_detail_resource();
                $objEventDetailResource->fields['id_event_detail'] = $this->fields['id'];
                $objEventDetailResource->fields['id_resource'] = $intResourceId;
                $objEventDetailResource->fields['type_resource'] = 'user';
                $objEventDetailResource->fields['id_event'] = $this->fields['id_event'];
                $objEventDetailResource->save();
            }
            foreach($this->arrResource['group'] as $intResourceId)
            {
                $objEventDetailResource = new planning_event_detail_resource();
                $objEventDetailResource->fields['id_event_detail'] = $this->fields['id'];
                $objEventDetailResource->fields['id_resource'] = $intResourceId;
                $objEventDetailResource->fields['type_resource'] = 'group';
                $objEventDetailResource->fields['id_event'] = $this->fields['id_event'];
                $objEventDetailResource->save();
            }
        }

        return $res;
    }

    /**
     * Supprime un d�tail en v�rifiant que l'�v�nement peut �tre supprim� ou non (planning_event)
     *
     * @return boolean
     */
    public function delete()
    {
        $db = ploopi\loader::getdb();

        // Suppression des ressources li�es
        $db->query("
            DELETE FROM ploopi_mod_planning_event_detail_resource WHERE id_event_detail = {$this->fields['id']}
        ");


        // Recherche si l'�v�nement contient d'autres d�tails.
        // S'il n'en contient pas, on le supprime
        $db->query("
            SELECT id FROM ploopi_mod_planning_event_detail WHERE id_event = {$this->fields['id_event']} AND id != {$this->fields['id']}
        ");

        // Pas d'autres d�tails rattach�s
        if ($db->numrows() == 0)
        {
            include_once './modules/planning/classes/class_planning_event.php';
            $objEvent = new planning_event();
            $objEvent->open($this->fields['id_event']);
            $objEvent->delete();
        }

        return(parent::delete());
    }

    /**
     * Affecte les ressources concern�es
     *
     * @param array $arrResource tableau des ressources (groupes/utilisateurs)
     * @param boolean $booVerify true s'il faut v�rifier le contenu de la variable
     */

    public function setresources($arrResource, $booVerify = true)
    {
        if ($booVerify)
        {
            $this->initresources();

            if (is_array($arrResource))
            {
                foreach($arrResource as $strResourceType => $arrTypeResource)
                {
                    foreach($arrTypeResource as $intIdResource)
                    {
                        switch($strResourceType)
                        {
                            case 'user': // utilisateur
                                $objUser = new ploopi\user();
                                if ($objUser->open($intIdResource)) // utilisateur existe
                                {
                                    $this->arrResource['user'][$intIdResource] = $intIdResource;
                                }
                            break;

                            case 'group': // groupe
                                $objGroup = new ploopi\group();
                                if ($objGroup->open($intIdResource)) // groupe existe
                                {
                                    $this->arrResource['group'][$intIdResource] =$intIdResource;
                                }
                            break;
                        }
                    }
                }
            }
        }
        else $this->arrResource = $arrResource;
    }

    /**
     * Retourne un tableau contenant les ressources
     *
     * @return array tableau des ressources
     */
    public function getresources()
    {
        return $this->arrResource;
    }

    /**
     * Initialise le tableau des ressources
     */
    private function initresources()
    {
        $this->arrResource = array(
            'user' => array(),
            'group' => array()
        );
    }

}
