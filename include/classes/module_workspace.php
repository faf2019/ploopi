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

namespace ploopi;

use ploopi;

/**
 * Gestion du lien module/workspace (table ploopi_module_workspace)
 *
 * @package ploopi
 * @subpackage module
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class module_workspace extends data_object
{
    /**
     * Constructeur de la classe
     */

    public function __construct()
    {
        parent::__construct('ploopi_module_workspace','id_workspace','id_module');
    }

    /**
     * Enregistre les infos sur la relation module / espace de rattachement (position par exemple)
     */

    public function save()
    {
        $db = db::get();

        if ($this->new)
        {
            $select =   "
                        SELECT MAX(ploopi_module_workspace.position) AS position
                        FROM ploopi_module_workspace
                        WHERE ploopi_module_workspace.id_workspace = {$this->fields['id_workspace']}
                        ";

            $result = $db->query($select);
            $fields = $db->fetchrow($result);
            $this->fields['position'] = $fields['position'] + 1;
        }

        parent::save();
    }

    /**
     * Supprime la relation module / espace de rattachement
     */

    public function delete()
    {
        $db = db::get();

        $update = "UPDATE ploopi_module_workspace SET position=position-1 WHERE id_workspace = {$this->fields['id_workspace']} AND position > {$this->fields['position']}";
        $db->query($update);

        parent::delete();
    }

    /**
     * Modifie la position de la relation module / espace de rattachement
     *
     * @param string $direction sens du mouvement 'down' / 'up'
     */

    public function changeposition($direction)
    {
        $db = db::get();

        $workspaceid = $this->fields['id_workspace'];

        $select = "
            SELECT  min(position) as minpos,
                    max(position) as maxpos
            FROM    ploopi_module_workspace
            WHERE   id_workspace = {$workspaceid}
        ";

        $result = $db->query($select);
        $fields = $db->fetchrow($result);
        $minpos = $fields['minpos'];
        $maxpos = $fields['maxpos'];
        $position = $this->fields['position'];
        $move = 0;

        if ($direction == 'down' && $position != $maxpos) $move = 1;

        if ($direction == 'up' && $position != $minpos) $move = -1;

        if ($move!=0)
        {
            $update = "UPDATE ploopi_module_workspace SET position = 0 WHERE id_workspace = {$workspaceid} AND position = ".($position+$move);
            $db->query($update);
            $update = "UPDATE ploopi_module_workspace SET position = ".($position+$move)." WHERE id_workspace = {$workspaceid} AND position = {$position}";
            $db->query($update);
            $update = "UPDATE ploopi_module_workspace SET position = {$position} WHERE id_workspace = {$workspaceid} AND position = 0";
            $db->query($update);
        }
    }
}
