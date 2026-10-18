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
 * Aucun jeton n'est émis hors backoffice : un formulaire public (typeform « cms »),
 * inséré dans une page webedit, est posté par un visiteur qui n'en a pas. Sans cette
 * déclaration, le répartiteur ignore la soumission et la réponse est perdue.
 *
 * forms_reply_save écrit, contrairement à la règle des opérations publiques (lecture
 * seule) : c'est la nature même d'un formulaire public. Elle se protège donc
 * elle-même — hors backoffice, elle n'accepte qu'un formulaire public et n'ouvre
 * aucune réponse existante ; en backoffice, elle exige le jeton.
 *
 * forms_quicksave ne fait que conserver, dans la session et un cookie du visiteur, la
 * saisie en cours.
 *
 * @package forms
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see include/op.php
 * @see op_reply_save.php
 */

return array(
    // Soumission d'un formulaire public, action du <form> rendu dans une page webedit
    'forms_reply_save',
    // Sauvegarde de la saisie en cours, appelée en arrière-plan par le formulaire
    'forms_quicksave',
    // Impression de la saisie en cours, bouton « Imprimer » d'un formulaire public : hors
    // backoffice, op.php n'imprime qu'un formulaire cms, sans désignation de réponse
    'forms_print',
);
