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

/**
 * Gestionnaire de session conforme \SessionHandlerInterface.
 *
 * session_set_save_handler() avec des callbacks séparés est déprécié depuis
 * PHP 8.4 : l'interface est la forme attendue. Chaque méthode délègue aux
 * méthodes statiques de session, qui restent la mise en œuvre.
 *
 * Le namespace est indispensable : loader.php (namespace ploopi) instancie
 * « new session_handler() », soit ploopi\session_handler.
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class session_handler implements \SessionHandlerInterface
{
    #[\ReturnTypeWillChange]
    public function open($path, $name) { return \ploopi\session::open(); }

    #[\ReturnTypeWillChange]
    public function close() { return \ploopi\session::close(); }

    #[\ReturnTypeWillChange]
    public function read($id) { return \ploopi\session::read($id); }

    #[\ReturnTypeWillChange]
    public function write($id, $data) { return \ploopi\session::write($id, $data); }

    #[\ReturnTypeWillChange]
    public function destroy($id) { return \ploopi\session::destroy($id); }

    #[\ReturnTypeWillChange]
    public function gc($max) { return \ploopi\session::gc($max); }
}

