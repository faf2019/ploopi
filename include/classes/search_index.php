<?php
/*
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
 * Gestion de l'indexation de recherche
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_index_element
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_element extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_element
     */
    
    public function index_element()
    {
        parent::data_object('ploopi_index_element', 'id');
    }
}

/**
 * Classe d'accès à la table ploopi_index_keyword_element
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_keyword_element extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_keyword_element
     */
    
    public function index_keyword_element()
    {
        parent::data_object('ploopi_index_keyword_element', 'id_keyword', 'id_element');
    }
}

/**
 * Classe d'accès à la table ploopi_index_keyword
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_keyword extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_keyword
     */
    
    public function index_keyword()
    {
        parent::data_object('ploopi_index_keyword', 'id');
    }

    /**
     * Enregistre le mot clé
     *
     * @return boolean true si l'enregistrement a été correctement réalisé
     */
    
    public function save()
    {
        $this->fields['twoletters'] = substr($this->fields['keyword'],0,2);
        return(parent::save());
    }
}


/**
 * Classe d'accès à la table ploopi_index_stem_element
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_stem_element extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_stem_element
     */
    
    public function index_stem_element()
    {
        parent::data_object('ploopi_index_stem_element', 'id_stem', 'id_element');
    }
}

/**
 * Classe d'accès à la table ploopi_index_stem
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_stem extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_stem
     */
    
    public function index_stem()
    {
        parent::data_object('ploopi_index_stem', 'id');
    }
}
?>
