<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Fichier de langue français
 *
 * @package webedit
 * @subpackage lang
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_WEBEDIT_PAGE_TITLE', 'Gestion du Module «LABEL»');

/**
 * Messages liés à l'abonnement en frontoffice
 */

global $webedit_subscription_messages;

$webedit_subscription_messages =
    array(
        _WEBEDIT_SUBSCRIPTION_SUBSCRIBED => 'Votre abonnement a été validé.',
        _WEBEDIT_SUBSCRIPTION_UNSUBSCRIBED => 'Votre désabonnement a été validé.',
        _WEBEDIT_SUBSCRIPTION_ERROR_EMAIL => 'Votre adresse email n\'est pas valide.',
        _WEBEDIT_SUBSCRIPTION_ERROR_PARAM => 'Paramètre incorrect.'

    );

define ('_WEBEDIT_COMMENT_SHOWALL',         'Voir tous les commentaires');
define ('_WEBEDIT_COMMENT_POST',            'Envoyer un commentaire');
define ('_WEBEDIT_COMMENT_COMMENT',         'commentaire(s)');
define ('_WEBEDIT_COMMENT_COMMENT_POSTBY',  'Posté par %s le %s à %s');

define ('_WEBEDIT_COMMENT_COMMENT_SEND_0',  'Erreur - Votre commentaire n\'a pas été enregistré.');
define ('_WEBEDIT_COMMENT_COMMENT_SEND_1',  'Commentaire Enregistré');
define ('_WEBEDIT_COMMENT_COMMENT_SEND_2',  'Commentaire Enregistré.<br/>Il sera contrôlé par un modérateur avant d\'être publié.');


?>
