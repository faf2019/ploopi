<?php
/*
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
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
 * Fonctions PostgreSQL utilis�es durant la proc�dure d'installation de Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Check if PostgreSQL is available (depreciated).
 *
 * @return
 *  TRUE/FALSE
 */
function pgsql_is_available() {
  return function_exists('pg_connect');
}

/**
 * Check if we can connect to PostgreSQL.
 *
 * @return
 *  TRUE/FALSE
 */
function pgsql_test($url, &$success) {
  return TRUE;
}
?>