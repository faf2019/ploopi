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
 * Gestion deslogs
 *
 * @package ploopi
 * @subpackage log
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion des d�pendances
 */
include_once './include/classes/session.php';

/**
 * Classe d'acc�s � la table ploopi_log.
 *
 * @package ploopi
 * @subpackage log
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class ploopi_log extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return log
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_log'
        );

        if (ploopi_session::get_usedb()) $this->setdb($this->getdb());
    }

    public function getdb()
    {
        if (ploopi_session::get_usedb()) return ploopi_session::get_db();
        else { global $db; return $db; }
    }
}

/**
 * Classe d'acc�s � la table ploopi_connecteduser.
 * Gestion des utilisateurs connect�s.
 *
 * @package ploopi
 * @subpackage log
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class connecteduser extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return connecteduser
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_connecteduser',
            'sid'
        );
    }
}

/**
 * Classe d'acc�s � la table user_action_log.
 * Gestion des actions utilisateurs.
 *
 * @package ploopi
 * @subpackage log
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class user_action_log extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return user_action_log
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_user_action_log',
            'id_user',
            'id_action',
            'id_module_type'
        );

        if (ploopi_session::get_usedb()) $this->setdb($this->getdb());
    }

    public function getdb()
    {
        if (ploopi_session::get_usedb()) return ploopi_session::get_db();
        else { global $db; return $db; }
    }

}
