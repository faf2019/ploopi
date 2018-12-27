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
 * Gestion des rubriques
 *
 * @package directory
 * @subpackage heading
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Inclusion de la classe contact.
 */

include_once './modules/directory/class_directory_contact.php';

class directory_heading extends ploopi\data_object
{
    private $modules;
    private $plans;
    private $position;

    public function __construct()
    {
        parent::__construct('ploopi_mod_directory_heading', 'id');
        $this->position = 0;
    }

    public function open(...$args)
    {
        $this->position = 0;

        if ($ret = parent::open($args[0]))
        {
            $this->position = $this->fields['position'];
        }

        return $ret;
    }

    public function save($booForcePos = false)
    {
        $db = ploopi\db::get();

        if ($this->new) $this->setuwm();
        else
        {
            if (!$booForcePos)
            {
                if ($this->position != $this->fields['position']) // nouvelle position définie
                {
                    if ($this->fields['position'] < 1) $this->fields['position'] = 1;
                    else
                    {
                        $db->query("
                            SELECT  MAX(position) AS maxpos
                            FROM    ploopi_mod_directory_heading
                            WHERE   id_heading = {$this->fields['id_heading']}
                        ");

                        $row = $db->fetchrow();

                        if ($this->fields['position'] > $row['maxpos']) $this->fields['position'] = $row['maxpos'];
                    }

                    // Mise à jour de la position des autres rubriques
                    if ($this->fields['position'] > $this->position)
                    {
                        $db->query("
                            UPDATE  ploopi_mod_directory_heading
                            SET     position = position-1
                            WHERE   position BETWEEN ".($this->position + 1)." AND {$this->fields['position']}
                            AND     id_heading = {$this->fields['id_heading']}
                            AND     id <> {$this->fields['id']}
                        ");
                    }
                    else
                    {
                       $db->query("
                            UPDATE  ploopi_mod_directory_heading
                            SET     position = position+1
                            WHERE   position BETWEEN {$this->fields['position']} AND ".($this->position - 1)."
                            AND     id_heading = {$this->fields['id_heading']}
                            AND     id <> {$this->fields['id']}
                        ");
                    }
                }
            }
        }

        return(parent::save());
    }

    /**
     * Supprime la rubrique et les sous-rubriques associées
     *
     * @return boolean
     */

    public function delete()
    {
        include_once './modules/directory/include/global.php';

        $db = ploopi\db::get();

        // Effacer les contacts attachés
        $rs_contact = $db->query("SELECT id FROM ploopi_mod_directory_contact WHERE id_heading = {$this->fields['id']}");
        while ($row = $db->fetchrow($rs_contact))
        {
            $objContact = new directory_contact();
            if ($objContact->open($row['id'])) $objContact->delete();
        }

        // Mise à jour de la position des autres rubriques
        $db->query("UPDATE ploopi_mod_directory_heading SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");

        // Suppression des gestionnaires (workflow)
        ploopi\validation::remove(_DIRECTORY_OBJECT_HEADING, $this->fields['id']);

        // Effacer les fils
        $rs_headings = $db->query("SELECT id FROM ploopi_mod_directory_heading WHERE id_heading = {$this->fields['id']}");
        while ($row = $db->fetchrow($rs_headings))
        {
            $objChild = new directory_heading();
            if ($objChild->open($row['id'])) $objChild->delete();
        }

        return(parent::delete());
    }

    /**
     * Crée une rubrique fils
     *
     * @return directory_heading
     */

    function create_child()
    {
        $db = ploopi\db::get();

        // calcul pos
        $rs = $db->query("SELECT max(position) AS maxpos FROM ploopi_mod_directory_heading WHERE id_heading = {$this->fields['id']}");
        $row = $db->fetchrow();

        if (!isset($row['maxpos'])) $row['maxpos'] = 0;

        $objChild = new directory_heading();
        $objChild->fields = $this->fields;
        $objChild->fields['id_heading'] = $this->fields['id'];
        $objChild->fields['label'] = 'Sous Rubrique de '.$objChild->fields['label'];
        $objChild->fields['position'] = $row['maxpos'] + 1;
        unset($objChild->fields['id']);

        return($objChild);
    }
}
?>
