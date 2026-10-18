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
 * Opérations du module dispensées du jeton anti-CSRF.
 *
 * Aucun jeton n'est émis hors backoffice : l'annuaire affiché en frontoffice
 * (template_content.php) insère la photo d'un contact par une balise <img> pointant sur
 * index-light.php. Sans cette déclaration, le répartiteur ignore la requête.
 *
 * directory_contact_getphoto est en lecture seule, et son seul paramètre est typé. Elle
 * ne sert que la photo d'un contact partagé, ou d'un contact personnel à son
 * propriétaire.
 *
 * @package directory
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see include/op.php
 * @see op.php
 */

return array(
    // Photo d'un contact, balise <img> de la fiche frontoffice
    'directory_contact_getphoto',
);
