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
 * Classe d'accÃ¨s Ã  la table ploopi_annotation_tag
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class annotation_tag extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return annotation_tag
     */

    public function __construct()
    {
        parent::__construct('ploopi_annotation_tag', 'id_annotation', 'id_tag');
    }

    /**
     * Supprime le tag s'il n'est plus utilisÃ©
     */

    public function delete()
    {
        $db = db::get();

        $select =   "
                    SELECT  count(*) as c
                    FROM    ploopi_annotation_tag
                    WHERE   id_tag = {$this->fields['id_tag']}
                    AND     id_annotation <> {$this->fields['id_annotation']}
                    ";

        $rs = $db->query($select);
        if (!($row = $db->fetchrow($rs)) || $row['c'] == 0)
        {
            $tag = new tag();
            $tag->open($this->fields['id_tag']);
            $tag->delete();
        }

        parent::delete();
    }

}
