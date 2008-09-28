<?php
/*
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
 * Fonctions, constantes, variables globales
 *
 * @package newsletter
 * @subpackage global
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Définition des constantes
 */

/**
 * Action : Ajouter une newsletter
 */
define ('_NEWSLETTER_ACTION_WRITE',       1);

/**
 * Action : Modifier une newsletter
 */
define ('_NEWSLETTER_ACTION_MODIFY',      2);

/**
 * Action : Supprimer une newsletter
 */
define ('_NEWSLETTER_ACTION_DELETE',      3);

/**
 * Action : Envoyer une newsletter
 */
define ('_NEWSLETTER_ACTION_SEND',        4);

/**
 * Action : Gérer les inscrits
 */
define ('_NEWSLETTER_ACTION_MANAGE_SUBSCRIBER',   5);

/**
 * Action : Gérer les validateur
 */
define ('_NEWSLETTER_ACTION_MANAGE_VALIDATOR',   6);

/**
 * Action : Nouvelle inscription (Pour log uniquement)
 */
define ('_NEWSLETTER_ACTION_NEW_SUBSCRIBER',   7);

/**
 * Action : Modification inscription (Pour log uniquement)
 */
define ('_NEWSLETTER_ACTION_MODIF_SUBSCRIBER',   8);

/**
 * Action : Suppression d'une inscription (Pour log uniquement)
 */
define ('_NEWSLETTER_ACTION_DELETE_SUBSCRIBER',   9);

/**
 * Action : Paramétrer la newsletter
 */
define ('_NEWSLETTER_ACTION_PARAM',   10);

/**
 * Action : Validation d'une newsletter
 */
define ('_NEWSLETTER_ACTION_VALIDATED',   11);

/**
 * Action : Demande de validation d'une newsletter
 */
define ('_NEWSLETTER_ACTION_WAIT_VALID',   12);

/**
 * Enregistrement d'un abonné : OK
 */
define ('_NEWSLETTER_SUBSCRIPTION_SUBSCRIBED', 1);

/**
 * Enregistrement d'un abonné : désabonné
 */
define ('_NEWSLETTER_SUBSCRIPTION_UNSUBSCRIBED', 2);

/**
 * Enregistrement d'un abonné : adresse email invalide
 */
define ('_NEWSLETTER_SUBSCRIPTION_ERROR_EMAIL', 9);

/**
 * Enregistrement d'un abonné : paramètre incorrect
 */
define ('_NEWSLETTER_SUBSCRIPTION_ERROR_PARAM', 99);

/**
 * Objet NEWSLETTER
 */
define ('_NEWSLETTER_OBJECT_NEWSLETTER',        1);

/**
 * Chemin relatif du dossier de stockage des templates newsletter
 */
define ('_NEWSLETTER_TEMPLATES_PATH', './templates/newsletter');
?>
