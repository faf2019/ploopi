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

    You should have received a copy of the GNU GeneralF Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Point d'entrée de l'affichage template de l'annuaire
 *
 * @package directory
 * @subpackage template
 * @copyright Ovensia
 * @author Ovensia
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

$template_body->assign_vars(
    array(
        'DIRECTORY_FORMACTION' => ploopi\crypt::urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=search"),
        'DIRECTORY_LINK_FULL' => ploopi\crypt::urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=full"),
        'DIRECTORY_LINK_ORGANIGRAM' => ploopi\crypt::urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=organigram"),
        'DIRECTORY_LINK_SPEEDDIALING' => ploopi\crypt::urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=speeddialing")
    )
);
?>
