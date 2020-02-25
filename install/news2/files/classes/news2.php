<?php
/*
    Copyright (c) 2007-2020 Ovensia
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

namespace ploopi\news2;

use ploopi;



/**
 * Classe d'accès à la table ploopi_mod_news_entry
 *
 * @package news
 * @subpackage entry
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class news2 extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return news2_entry
     */
    
    public function __construct() 
    {
        parent::__construct('ploopi_mod_news2_entry');
    }

    /**
     * Enregistre la news
     *
     * @return boolean true si l'enregistrement a été effectué
     */

    public function save()
    {
		ploopi\output::log('content:'.$this->fields['content'] );
        // $this->fields['content'] = ploopi\str::htmlpurifier($this->fields['content']);
        return parent::save();
    }

}

