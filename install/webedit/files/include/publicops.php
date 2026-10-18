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
 * Aucun jeton n'est émis hors backoffice. Ces opérations sont déclenchées par un
 * visiteur anonyme, depuis une URL réécrite par include/rewrite.php ou depuis un
 * formulaire du frontoffice : sans cette déclaration, le répartiteur les ignore et le
 * visiteur reçoit la page d'accueil à la place.
 *
 * webedit_sitemap et webedit_backend sont en lecture seule et ne servent que des
 * contenus publiés hors rubrique privée.
 *
 * webedit_subscribe n'écrit rien : elle envoie à l'adresse saisie un courriel portant
 * un lien signé (voir webedit_subscription_token()). webedit_subscription_confirm et
 * webedit_unsubscribe écrivent, contrairement à la règle des opérations publiques :
 * c'est la nature même d'un abonnement par courriel. Elles n'agissent que sur ce que
 * porte le jeton signé reçu par courriel, jamais sur l'utilisateur de la session : une
 * requête forgée depuis un site tiers n'obtient rien sans ce jeton. Le jeton anti-CSRF
 * ne protégerait donc rien.
 *
 * Les commentaires publics ont été retirés (2.8.7) : ils dépendaient d'une classe
 * ploopi\captcha jamais livrée avec le socle.
 *
 * @package webedit
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see include/op.php
 * @see include/rewrite.php
 */

return array(
    // Plan du site, URL réécrite /sitemap.xml
    'webedit_sitemap',
    // Flux RSS et Atom, URL réécrites /web/rss/… et /web/atom/…
    'webedit_backend',
    // Abonnement à une rubrique, action du formulaire rendu dans la page
    'webedit_subscribe',
    // Confirmation d'abonnement, lien /subscribe/<jeton>/index.html du courriel
    'webedit_subscription_confirm',
    // Désabonnement, lien /unsubscribe/<jeton>/index.html des courriels
    'webedit_unsubscribe',
);
