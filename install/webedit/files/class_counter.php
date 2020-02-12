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
 * Gestion des compteurs
 *
 * @package webedit
 * @subpackage counter
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Classe d'accès aux table ploopi_mod_webedit_counter
 *
 * @package webedit
 * @subpackage counter
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class webedit_counter extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @param string $type type d'article ('draft' / '')
     * @return webedit_article
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_webedit_counter', 'year', 'month', 'day', 'id_article');
    }

    /**
     * Enregistre un nouveau hit
     *
     * @return boolean true si l'enregistrement a été correctement exécuté
     */

    public function hit()
    {
        $this->fields['hits'] = (!isset($this->fields['hits'])) ? 1 : $this->fields['hits']+1;
        return(parent::save());
    }
}
