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
 * Elles sont appelées par des URL qui n'en portent pas : la vignette d'un document est une
 * balise <img>, et un document lié depuis une page publique l'est par une URL réécrite.
 *
 * N'y déclarer que des opérations sans effet de bord. Les trois ci-dessous sont en lecture
 * seule et effectuent leur propre contrôle d'accès sur le document demandé.
 *
 * @package doc
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see include/op.php
 */

return array(
    // Vignette d'un document, insérée par functions.js dans une balise <img>
    'doc_image_get',
    // Consultation d'un document, lien produit par op.php
    'doc_file_view',
    // Téléchargement d'un document, lien produit par op.php et par les URL réécrites de webedit
    'doc_file_download',
);
