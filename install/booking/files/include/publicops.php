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
 * Aucun jeton n'est émis hors backoffice : le planning public, inséré dans un article
 * webedit comme objet WCE, se navigue (jour, semaine, mois, choix des ressources) par
 * des appels AJAX à index-light.php. Sans cette déclaration, le répartiteur les ignore
 * et le visiteur reçoit la page entière à la place du planning.
 *
 * booking_refresh et booking_setresources ne font que rendre le planning et conserver,
 * dans la session et un cookie du visiteur, ses critères d'affichage. Hors backoffice,
 * op.php ne les exécute que pour une instance du module accessible depuis l'espace.
 *
 * Les opérations sur les demandes (booking_event_*) ne sont pas déclarées : elles
 * écrivent avec l'identité de la session, et resteraient exposées à une requête forgée
 * depuis un site tiers. Faire une demande depuis le frontoffice reste donc impossible
 * tant qu'aucun jeton n'y est émis.
 *
 * @package booking
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see include/op.php
 * @see op.php
 */

return array(
    // Rechargement du planning (navigation par période, affichage multicolonne)
    'booking_refresh',
    // Choix des ressources affichées
    'booking_setresources',
);
