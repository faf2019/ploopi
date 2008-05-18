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
 * Gestion des réponses
 *
 * @package forms
 * @subpackage reply
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_reply
 *
 * @package forms
 * @subpackage reply
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class reply extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return reply
     */
    
    function reply()
    {
        parent::data_object('ploopi_mod_forms_reply');
    }

    /**
     * Supprime cette réponse et les champs attachés
     */
    
    function delete()
    {
        global $db;

        // delete reply fields & attached files
        $db->query( "
                    SELECT  rf.*
                    FROM    ploopi_mod_forms_reply_field rf,
                            ploopi_mod_forms_field f
                    WHERE   rf.id_form = '{$this->fields['id_form']}'
                    AND     rf.id_reply = '{$this->fields['id']}'
                    AND     f.id_form = rf.id_form
                    AND     f.id = rf.id_field
                    AND     f.type='file'
                    ");

        while($fields = $db->fetchrow())
        {
            $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$this->fields['id_module']._PLOOPI_SEP.$this->fields['id_form']._PLOOPI_SEP.$this->fields['id']._PLOOPI_SEP;
            if (file_exists($path.$fields['value'])) unlink($path.$fields['value']);
        }

        $db->query("DELETE FROM ploopi_mod_forms_reply_field WHERE id_form = '{$this->fields['id_form']}' AND id_reply = '{$this->fields['id']}'");
        parent::delete();
    }
}
?>
