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

/**
 * Gestion des Groupes
 *
 * @package forms
 * @subpackage forms_group
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe d'acc�s � la table ploopi_mod_forms_group
 *
 * @package forms
 * @subpackage forms_group
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class formsGroup extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return formsGroup
     */

    public function __construct() { parent::__construct('ploopi_mod_forms_group'); }

    /**
     * G�re le clone
     */

    public function __clone()
    {
        // Personnalisation du clone
        $this->new = true;
        $this->fields['id'] = null;
    }

    /**
     * Suppression du groupe
     */

    public function delete()
    {
        // Suppression de la d�pendance
        $objQuery = new ploopi\query_update();
        $objQuery->add_from('ploopi_mod_forms_field');
        $objQuery->add_set('id_group = 0');
        $objQuery->add_where('id_group = %d', $this->fields['id']);
        $objQuery->execute();

        return parent::delete();
    }

    /**
     * Retourne les conditions sous forme d'un tableau
     */

    public function getConditions() { return ploopi\crypt::unserialize($this->fields['conditions']); }

}
?>
