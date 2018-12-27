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
 * Définition des modes de parcours récursifs en fonction de la profondeur de rubrique.
 * Par défaut le moteur de template parcours les rubriques en largeur (toutes les rubriques, niveau par niveau)
 * Pour certains besoins (menus dynamiques) on peut vouloir un parcours en profondeur des menus (disposer des sous menus pour chaque rubrique)
 *
 * @package template
 * @subpackage front_default
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$recursive_mode = array(
                    '1' => 'prof',
                    '2' => 'prof',
                    '3' => 'prof',
                    '4' => 'prof'
                );
?>
