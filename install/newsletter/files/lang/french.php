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
 * Fichier de langue fran�ais
 *
 * @package newsletter
 * @subpackage lang
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * D�finition des constantes
 */

/**
 * Messages li�s � l'abonnement � la newsletter en frontoffice
 */
global $newsletter_subscription_messages;

$newsletter_subscription_messages =
    array(
        _NEWSLETTER_SUBSCRIPTION_SUBSCRIBED   =>  'Votre abonnement a �t� valid�.',
        _NEWSLETTER_SUBSCRIPTION_UNSUBSCRIBED =>  'Votre d�sabonnement a �t� valid�.',
        _NEWSLETTER_SUBSCRIPTION_ERROR_EMAIL  =>  'Votre adresse email n\'est pas valide.',
        _NEWSLETTER_SUBSCRIPTION_ERROR_PARAM  =>  'Param�tre incorrect.'

    );


define ('_NEWSLETTER_PAGE_TITLE', 'Gestion du Module �LABEL�');
define ('_NEWSLETTER_PAGE_TITLE_CONSULT', 'Consultation des Newsletters');

define ('_NEWSLETTER_ADMIN',      'Administration');
define ('_NEWSLETTER_CONSULT',    'Consultation');
define ('_NEWSLETTER_VALIDATE',   'Valider');
define ('_NEWSLETTER_DEFAULT',    'D�faut');
define ('_NEWSLETTER_RETURN',     'Retour');
define ('_NEWSLETTER_DELETED',    'Supprim�(e)');
define ('_NEWSLETTER_OPEN',       'Ouvrir');

define ('_NEWSLETTER_LABELTAB_LETTER_LIST', 'Gestion des Newsletters');
define ('_NEWSLETTER_LABELTAB_SEND',        'Gestion des envois');
define ('_NEWSLETTER_LABELTAB_SUBSCRIBER',  'Gestion des inscrits');
define ('_NEWSLETTER_LABELTAB_BANNIERE',    'Gestion des banni�res');
define ('_NEWSLETTER_LABELTAB_PARAM',       'Param�tres');

define ('_NEWSLETTER_NAMECOLUMN_EMAIL',     'Adresse mail');
define ('_NEWSLETTER_NAMECOLUMN_SUBSCRIBE', 'Inscription');
define ('_NEWSLETTER_NAMECOLUMN_IP',        'IP inscription');
define ('_NEWSLETTER_NAMECOLUMN_ACTIVE',    'Active');
define ('_NEWSLETTER_NAMECOLUMN_TITLE',     'Titre');
define ('_NEWSLETTER_NAMECOLUMN_STATUS',    'Statut');
define ('_NEWSLETTER_NAMECOLUMN_CREATE',    'Cr�ation');
define ('_NEWSLETTER_NAMECOLUMN_VALID',     'Validation');

define ('_NEWSLETTER_NAMECOLUMN_ACTION',    'Action');

// Inscriptions
define ('_NEWSLETTER_LABEL_EMAIL',            'Adresse mail');
define ('_NEWSLETTER_LABEL_SUBSCRIBER',       'Inscrit');
define ('_NEWSLETTER_LABEL_SUBSCRIBER_MODIF', 'Modification de l\'inscription de ');
define ('_NEWSLETTER_LABEL_ACTIVE',           'Adresse active');
define ('_NEWSLETTER_LABEL_IP',               'IP d\'inscription');
define ('_NEWSLETTER_LABEL_TIMESTP_SUBSCRIBE','Date d\'inscription');

define ('_NEWSLETTER_CONFIRM_SUBSCRIBE_DELETE',   'Confirmez-vous la suppression de l\'inscription de %email ?');

// Newsletter
define ('_NEWSLETTER_LABEL_NEWSLETTER_LIST',      'Liste des newsletters');
define ('_NEWSLETTER_LABEL_NEWSLETTER_MANAGE',    'R�daction des newsletters');
define ('_NEWSLETTER_LABEL_VALIDATOR_GLB_MANAGE', 'Gestion les validateurs globaux');
define ('_NEWSLETTER_LABEL_VALIDATOR_GLB_SAVE',   'Enregistrer les validateurs globaux');
define ('_NEWSLETTER_LABEL_VALIDATOR_GLB',        'Validateurs globaux');

define ('_NEWSLETTER_LABEL_CONTENT',              'Contenu');
define ('_NEWSLETTER_LABEL_PRINC_PROPRIETY',      'Propri�t�s principales de la Newsletter');
define ('_NEWSLETTER_LABEL_TITLE',                'Titre');
define ('_NEWSLETTER_LABEL_TITLE_EXPLAIN',        'Utilis� dans l\'ent�te du mail');
define ('_NEWSLETTER_LABEL_SUBJECT',              'Sujet');
define ('_NEWSLETTER_LABEL_SUBJECT_EXPLAIN',      'Utilis� comme sujet au mail');
define ('_NEWSLETTER_LABEL_GABARIT',              'Gabarit');
define ('_NEWSLETTER_LABEL_GABARIT_EXPLAIN',      'Enregistrement n�cessaire pour rendre effectif dans l\'�diteur');
define ('_NEWSLETTER_LABEL_BANNIERE',             'Banni�re');
define ('_NEWSLETTER_LABEL_BACKGROUND_COLOR',     'Couleur de l\'arri�re plan');
define ('_NEWSLETTER_LABEL_CONTENT_COLOR',        'Couleur du fond');
define ('_NEWSLETTER_LABEL_TEXT_COLOR',     'Couleur du texte');

define ('_NEWSLETTER_LABEL_STATUS',               'Statut');
define ('_NEWSLETTER_LABEL_STATUS_DRAFT',         'Brouillon');
define ('_NEWSLETTER_LABEL_STATUS_WAIT',          'En attente de validation');
define ('_NEWSLETTER_LABEL_STATUS_VALID',         'Valid�e');
define ('_NEWSLETTER_LABEL_STATUS_SEND',          'Exp�di�e');
define ('_NEWSLETTER_LABEL_DISPLAY',              'Afficher');
define ('_NEWSLETTER_LABEL_GENERATE_PDF',         'G�n�rer PDF');
define ('_NEWSLETTER_LABEL_CREATE',               'Cr�ation');
define ('_NEWSLETTER_LABEL_MODIF',                'Modification');
define ('_NEWSLETTER_LABEL_VALID',                'Validation');


define ('_NEWSLETTER_CONFIRM_NEWSLETTER_DELETE',  'Confirmez-vous la suppression de cette Newsletter ?');
define ('_NEWSLETTER_CONFIRM_NEWSLETTER_SEND',  'Confirmez-vous l\'envoi de cette Newsletter ?');
define ('_NEWSLETTER_CONFIRM_STATUS_WAIT_NEWSLETTER',  '"ATTENTION !! Cette newsletter semble vide.\n\n Si vous l\'enregistrez, vous perdrez tout son contenu\n\n�tes-vous malgr� tout certain de vouloir enregistrer cette Newsletter ?"');


define ('_NEWSLETTER_LABELICON_LIST', 'Liste');
define ('_NEWSLETTER_LABELICON_NEW',  'R�daction');

// Send
define ('_NEWSLETTER_LABELICON_SEND_TODO',        'A exp�dier');
define ('_NEWSLETTER_LABELICON_SEND_OK',          'Exp�di�e(s)');

define ('_NEWSLETTER_NAMECOLUMN_SEND',            'Exp�dition');

define ('_NEWSLETTER_LABEL_LIST_TO',              'Liste des personnes ayant re�u la Newsletter');

define ('_NEWSLETTER_LABEL_RETURN_SEND_OK',       'Newsletter exp�di�e');
define ('_NEWSLETTER_LABEL_RETURN_SEND_ERROR',    'Erreur � l\'exp�dition de la Newsletter');

// Param
define ('_NEWSLETTER_LABEL_NEWSLETTER_PARAM',     'Param�tres');
define ('_NEWSLETTER_LABEL_HOST',                 'Racine du site');
define ('_NEWSLETTER_LABEL_HOST_EXPLAIN',         'Utilis� dans le lien en ligne de la newsletter<br/>ex: http://www.monsite.fr/');
define ('_NEWSLETTER_LABEL_FROM_NAME',            'Nom exp�diteur');
define ('_NEWSLETTER_LABEL_FROM_NAME_EXPLAIN',    'Nom d\'exp�diteur utilis� dans les newsletters envoy�s');
define ('_NEWSLETTER_LABEL_FROM_EMAIL',           'Mail exp�diteur');
define ('_NEWSLETTER_LABEL_FROM_EMAIL_EXPLAIN',   'Adresse mail utilis� dans les newsletters envoy�s et comme adresse de r�ponse �ventuelle');
define ('_NEWSLETTER_LABEL_SEND_BY',              'Exp�dier par paquet de');
define ('_NEWSLETTER_LABEL_SEND_BY_EXPLAIN',      'Possibilit� d\'exp�dier les newsletters par paquet de destinataires (0 = pas de limite)');
define ('_NEWSLETTER_LABEL_SEND_BY_WARNING',      'ATTENTION: certain fournisseurs d\'acc�s internet d�tectent l\'envoi de mail massif non signal� comme �tant du spam invonlontaire (virus) et peuvent couper sans pr�avis votre connexion internet. Pour plus d\'information, prennez contact avec votre fournisseur.');
define ('_NEWSLETTER_LABEL_SEND_BY_INFO',         '<span style="font-size: 1.1em; font-weight: bold;">D�clarez votre fichier d\'adresses de courriers �lectroniques aupr�s de la CNIL</span><br/>
                                                   Conform�ment � l\'article 23 de la loi du 6 janvier 1978 modifi�e,  tout traitement automatis� d\'informations nominatives comportant des adresses �lectroniques doit �tre d�clar� aupr�s de la CNIL.<br/>A toutes fins utiles, reportez-vous � la Rubrique "Vos responsabilit�s" du site web de la CNIL : <a href="http://www.cnil.fr/vos-responsabilites/">http://www.cnil.fr/vos-responsabilites/</a><br/>
                                                   Tout manquement � cette obligation est sanctionn� par l\'article 226-16 du Code p�nal (5 ans d\'emprisonnement et 300 000 euros d\'amende).');


?>