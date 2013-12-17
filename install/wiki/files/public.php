<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Partie publique du module
 *
 * @package wiki
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */

ploopi_init_module('wiki');

echo $skin->create_pagetitle(ploopi_htmlentities($_SESSION['ploopi']['modulelabel']));

// Menu principal
$strWikiMenu = isset($_GET['wiki_menu']) ? $_GET['wiki_menu'] : '';

switch($strWikiMenu)
{
    case 'index_title':
    case 'index_date':
        include_once './modules/wiki/public_index.php';
    break;

    case 'reindex':
        include_once './modules/wiki/public_reindex.php';
    break;

    default: // navigation
        include_once './modules/wiki/public_view.php';
    break;

}
?>
