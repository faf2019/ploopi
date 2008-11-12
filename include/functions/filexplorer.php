<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Gestion d'un bloc de document associé à un enregistrement d'un objet.
 * Permet notamment de gérer des pièces jointes à n'importe quel objet de ploopi.
 * 
 * @package ploopi
 * @subpackage filexplorer
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * Renvoie un identifiant unique pour l'explorateur
 *
 * @return string identifiant du bloc
 * 
 * @see md5
 */

function ploopi_filexplorer_init($strBasePath, $strDestField, $strFilExplorerId = '')
{
    if (empty($strFilExplorerId)) $strFilExplorerId = md5(uniqid(rand(), true));
    
    if ($strBasePath[strlen($strBasePath)-1] == _PLOOPI_SEP) $strBasePath = substr($strBasePath, 0, -1);

    $_SESSION['filexplorer'][$strFilExplorerId] = 
        array(
            'basepath' => $strBasePath,
            'destfield' => $strDestField
        );
    
    return $strFilExplorerId;    
}
?>
