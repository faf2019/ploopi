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
 * Gestion de la relation Espace de travail / Utilisateur (table ploopi_workspace_user)
 *
 * @package ploopi
 * @subpackage workspace
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class workspace_user extends data_object
{
    /**
     * Constructeur de la classe
     */

    public function __construct()
    {
        parent::__construct('ploopi_workspace_user','id_workspace','id_user');
        $this->fields['adminlevel'] = _PLOOPI_ID_LEVEL_USER;
    }

    /**
     * Supprime la relation utilisateur / espace de rattachement
     */

    public function delete()
    {
        $db = db::get();

        // search for modules
        $select =   "
                    SELECT  m.id, m.label, mt.label as moduletype
                    FROM    ploopi_module_workspace mw,
                            ploopi_module m,
                            ploopi_module_type mt
                    WHERE   mw.id_workspace = {$this->fields['id_workspace']}
                    AND     mw.id_module = m.id
                    AND     m.id_module_type = mt.id
                    ";

        $rs_modules = $db->query($select);

        while ($fields = $db->fetchrow($rs_modules))
        {
            $admin_userid = $this->fields['id_user'];
            $admin_workspaceid = $this->fields['id_workspace'];
            $admin_moduleid = $fields['id'];

            echo "<br /><strong>&laquo; ".str::htmlentities($fields['label'])." &raquo;</strong> (".str::htmlentities($fields['moduletype']).")<br />";
            if (file_exists("./modules/{$fields['moduletype']}/include/admin_user_delete.php")) include "./modules/{$fields['moduletype']}/include/admin_user_delete.php";
        }

        parent::delete();
    }

}
