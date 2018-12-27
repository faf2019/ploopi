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
 * Gestion des logs (table ploopi_log)
 *
 * @package ploopi
 * @subpackage log
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class log extends data_object
{
    /**
     * Constructeur de la classe
     */

    public function __construct()
    {
        parent::__construct('ploopi_log');

        if (session::get_usedb()) $this->setdb($this->getdb());
    }

    /**
     * Retourne le connecteur à la base de données utilisée
     *
     * @return db connecteur à la base de données
     */

    public static function getdb()
    {
        if (session::get_usedb()) return session::get_db();
        else { $db = db::get(); return $db; }
    }
}
