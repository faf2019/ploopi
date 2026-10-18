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
 * Fichier de langue 'français'
 *
 * @package system
 * @subpackage lang
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Définition des constantes
 */

define ('_SYSTEM_PAGE_TITLE', 'Administration');
define ('_SYSTEM_LABEL_SYSTEM', 'SYSTEME');
define ('_SYSTEM_LABELICON_SYSTEMUPDATE', 'Mise à jour');
define ('_SYSTEM_LABELICON_INSTALLMODULES', 'Installation');
define ('_SYSTEM_LABELICON_MODULES', 'Modules');
define ('_SYSTEM_LABELICON_PARAMS', 'Paramètres');
define ('_SYSTEM_LABELICON_USERS', 'Utilisateurs');
define ('_SYSTEM_LABELICON_AUTHORIZATIONS', 'Autorisations');
define ('_SYSTEM_LABELICON_TOOLS', 'Outils');
define ('_SYSTEM_LABELICON_LOGS', 'Logs');
define ('_SYSTEM_LABELICON_ROLES', 'Rôles');
define ('_SYSTEM_LABELICON_GROUP', 'Groupe');
define ('_SYSTEM_LABELICON_HOMEPAGE', 'Accueil');
define ('_SYSTEM_LABELICON_WORKSPACE', 'Espace');

/**
 * Écrans refondus (gabarits de génération 4 : ploopi4)
 */

define ('_SYSTEM_MENU_ORGANIZATION', 'Organisation');
define ('_SYSTEM_MENU_SYSTEM', 'Système');
define ('_SYSTEM_MENU_WORKSPACES', 'Espaces de travail');
define ('_SYSTEM_MENU_INSTALLMODULES', 'Modules');
define ('_SYSTEM_MENU_PARAMS', 'Paramètres');
define ('_SYSTEM_MENU_DIRECTORY', 'Utilisateurs');
define ('_SYSTEM_MENU_TOOLS', 'Outils');
define ('_SYSTEM_MENU_SYSTEMUPDATE', 'Mise à jour');

define ('_SYSTEM_WS_SUBTITLE', 'Espaces, groupes et utilisateurs de la plateforme.');
define ('_SYSTEM_WS_TREE_FILTER', 'Filtrer les espaces et groupes…');
define ('_SYSTEM_WS_TREE_LABEL', 'Arborescence des espaces et des groupes');
define ('_SYSTEM_WS_TREE_OPEN', 'Changer d\'espace ou de groupe');
define ('_SYSTEM_WS_TREE_TITLE', 'Espaces et groupes');
define ('_SYSTEM_WS_NEWWORKSPACE', 'Nouvel espace');
define ('_SYSTEM_WS_NEWGROUP', 'Nouveau groupe');
define ('_SYSTEM_WS_TYPE_WORKSPACE', 'Espace de travail');
define ('_SYSTEM_WS_TYPE_GROUP', 'Groupe');
define ('_SYSTEM_WS_ROOT', 'Racine');
define ('_SYSTEM_WS_TREE_NONE', 'Aucun espace ni groupe ne correspond.');
define ('_SYSTEM_WS_TREE_TOGGLE', 'Déplier ou replier');
define ('_SYSTEM_TAB_USERS', 'Utilisateurs');
define ('_SYSTEM_TAB_DIRECTORY', 'Annuaire');
define ('_SYSTEM_CLOSE', 'Fermer');

define ('_SYSTEM_USERS_SEARCH', 'Rechercher un utilisateur…');
define ('_SYSTEM_USERS_FILTERS', 'Filtres');
define ('_SYSTEM_USERS_FILTER', 'Filtrer');
define ('_SYSTEM_USERS_RESET', 'Réinitialiser');
define ('_SYSTEM_USERS_EXPORT', 'Exporter');
define ('_SYSTEM_USERS_ADD', 'Ajouter');
define ('_SYSTEM_USERS_ALL', 'Tous');
define ('_SYSTEM_USERS_CONNECTION_FROM', 'Connexion entre le');
define ('_SYSTEM_USERS_CONNECTION_TO', 'et le');
define ('_SYSTEM_USERS_USER', 'Utilisateur');
define ('_SYSTEM_USERS_LASTCONNECTION', 'Connexion');
define ('_SYSTEM_USERS_NEVER', 'Jamais');
define ('_SYSTEM_USERS_SELECTALL', 'Tout sélectionner');
define ('_SYSTEM_USERS_UNSELECT', 'Désélectionner');
define ('_SYSTEM_USERS_SELECTED', 'sélectionné');
define ('_SYSTEM_USERS_SELECTED_PLURAL', 'sélectionnés');
define ('_SYSTEM_USERS_DETACH_SELECTION', 'Détacher');
define ('_SYSTEM_USERS_EXPORT_SELECTION', 'Exporter la sélection');
define ('_SYSTEM_USERS_NONE', 'Aucun utilisateur ne correspond.');
define ('_SYSTEM_USERS_EMPTY', 'Aucun utilisateur rattaché.');
define ('_SYSTEM_USERS_TOOMANY', 'Trop d\'utilisateurs (<NB>) : affinez la recherche par les filtres.');
define ('_SYSTEM_USERS_ADD_NEW', 'Créer un utilisateur');
define ('_SYSTEM_USERS_ADD_NEW_DESC', 'Nouveau compte dans ce groupe');
define ('_SYSTEM_USERS_ADD_ATTACH', 'Rattacher un utilisateur existant');
define ('_SYSTEM_USERS_ADD_ATTACH_DESC', 'Choisir dans l\'annuaire');
define ('_SYSTEM_USERS_ADD_IMPORT', 'Importer');
define ('_SYSTEM_USERS_ADD_IMPORT_DESC', 'Fichier CSV');
define ('_SYSTEM_USERS_ADD_GROUP', 'Rattacher un groupe');
define ('_SYSTEM_USERS_ADD_GROUP_DESC', 'Ses utilisateurs accèdent à l\'espace');
define ('_SYSTEM_USERS_TAB_USERS', 'Utilisateurs');
define ('_SYSTEM_USERS_TAB_GROUPS', 'Groupes rattachés');
define ('_SYSTEM_USERS_BACKTOLIST', 'Retour à la liste');
define ('_SYSTEM_USERS_CARD_GROUPS', 'Groupes');
define ('_SYSTEM_USERS_CARD_WORKSPACES', 'Espaces de travail');
define ('_SYSTEM_USERS_CARD_ROLES', 'Rôles');
define ('_SYSTEM_USERS_CARD_LEVEL', 'Niveau dans l\'espace');
define ('_SYSTEM_USERS_CARD_STATUS', 'Compte');
define ('_SYSTEM_USERS_CARD_DISABLED', 'Désactivé');
define ('_SYSTEM_USERS_CARD_ACTIVE', 'Actif');
define ('_SYSTEM_USERS_CARD_EXPIRED', 'Expiré');
define ('_SYSTEM_USERS_SUMMARY', '<NB> sur <TOTAL>');
define ('_SYSTEM_USERS_SORTEDBY', 'tri par');
define ('_SYSTEM_USERS_ALPHA', 'Initiale du nom');
define ('_SYSTEM_USERS_SELECT', 'Sélectionner');
define ('_SYSTEM_USERS_PREV', 'Page précédente');
define ('_SYSTEM_USERS_NEXT', 'Page suivante');
define ('_SYSTEM_USERS_ACTIONS', 'Actions');
define ('_SYSTEM_MSG_CONFIRMUSERSDETACH', 'Êtes-vous certain de vouloir détacher les utilisateurs sélectionnés ?');

// Gabarits de génération 4 : onglet Espace (propriétés, création d'un sous-espace)
define ('_SYSTEM_WSF_NEW_TITLE', 'Nouveau sous-espace');
define ('_SYSTEM_WSF_NEW_PLACEHOLDER', 'Nom du sous-espace');
define ('_SYSTEM_WSF_CREATE', 'Créer l\'espace');
define ('_SYSTEM_WSF_STAT_WORKSPACES', 'sous-espaces');
define ('_SYSTEM_WSF_STAT_GROUPS', 'groupes rattachés');
define ('_SYSTEM_WSF_STAT_USERS', 'utilisateurs rattachés');
define ('_SYSTEM_WSF_SEC_IDENTITY', 'Identité');
define ('_SYSTEM_WSF_SEC_IDENTITY_DESC', 'Nom affiché dans l\'arborescence et le sélecteur d\'espace, code libre, ordre d\'affichage.');
define ('_SYSTEM_WSF_CODE_HINT', 'Identifiant libre, pour les échanges avec d\'autres applications.');
define ('_SYSTEM_WSF_PRIORITY_HINT', 'Ordre d\'affichage parmi les espaces du même niveau.');
define ('_SYSTEM_WSF_SEC_ACCESS_DESC', 'Interfaces ouvertes pour cet espace et domaines qui y mènent (un par ligne, * pour tous).');
define ('_SYSTEM_WSF_BACKOFFICE_HINT', 'Interface d\'administration et des modules (admin.php).');
define ('_SYSTEM_WSF_FRONTOFFICE_HINT', 'Site public rendu par le module WebEdit.');
define ('_SYSTEM_WSF_SKIN_HINT', 'ploopi4 : interface adaptative, thèmes clair et sombre.');
define ('_SYSTEM_WSF_DOMAINS_HINT', 'Un domaine par ligne ; * : tous les domaines.');
define ('_SYSTEM_WSF_WEBEDIT_WARNING', 'Le frontoffice est activé, mais aucune instance du module WebEdit n\'est rattachée à cet espace.');
define ('_SYSTEM_WSF_SEC_META', 'Référencement');
define ('_SYSTEM_WSF_SEC_META_DESC', 'Informations META des pages publiques de l\'espace.');
define ('_SYSTEM_WSF_META_TITLE', 'Titre');
define ('_SYSTEM_WSF_META_DESCRIPTION', 'Description');
define ('_SYSTEM_WSF_META_KEYWORDS', 'Mots clés');
define ('_SYSTEM_WSF_META_AUTHOR', 'Auteur');
define ('_SYSTEM_WSF_META_COPYRIGHT', 'Copyright');
define ('_SYSTEM_WSF_META_ROBOTS', 'Robots');
define ('_SYSTEM_WSF_ROBOTS_HINT', 'Par exemple : index, follow.');
define ('_SYSTEM_WSF_SEC_FILTERING_DESC', 'Adresses depuis lesquelles l\'espace est accessible.');
define ('_SYSTEM_WSF_IP_HINT', 'Adresses ou plages séparées par des points-virgules ; vide : aucune restriction.');
define ('_SYSTEM_WSF_SEC_MAIL', 'Courriel de bienvenue');
define ('_SYSTEM_WSF_SEC_MAIL_DESC', 'Message envoyé à la création d\'un compte, si le paramètre « courriel aux nouveaux utilisateurs » est actif.');
define ('_SYSTEM_WSF_ADMINMAIL_WARNING', 'L\'adresse de courriel de l\'administrateur du site n\'est pas définie dans le fichier de configuration : aucun courriel ne part.');
define ('_SYSTEM_WSF_MAIL_MODEL', 'Modèle de courriel');
define ('_SYSTEM_WSF_MAIL_TAGS', 'Balises disponibles (cliquer pour insérer) :');
define ('_SYSTEM_WSF_MAIL_INSERT', 'Insérer au curseur');
define ('_SYSTEM_WSF_SEC_MODULES_DESC', 'Modules partagés par les espaces parents (cochés s\'ils sont hérités), ou nouvelles instances.');
define ('_SYSTEM_WSF_MODULE_SHARED', 'Instance partagée');
define ('_SYSTEM_WSF_MODULE_NEW', 'Nouvelle instance');
define ('_SYSTEM_WSF_ACTIONS', 'Actions sur l\'espace');
define ('_SYSTEM_WSF_CLONE_TITLE', 'Cloner l\'espace');
define ('_SYSTEM_WSF_CLONE_HINT', 'Crée un espace frère avec les mêmes propriétés et les modules partagés par le parent.');
define ('_SYSTEM_WSF_CLONE_DENIED', 'Vous n\'avez pas les droits suffisants pour cloner votre propre espace.');
define ('_SYSTEM_WSF_CLONE_CONFIRM', 'Cloner cet espace ?');
define ('_SYSTEM_WSF_DELETE_TITLE', 'Supprimer l\'espace');
define ('_SYSTEM_WSF_DELETE_HINT', 'Supprime l\'espace et les instances de modules qui lui appartiennent.');
define ('_SYSTEM_WSF_DELETE_CONFIRM', 'Êtes-vous certain de vouloir supprimer cet espace et ses modules ?');
define ('_SYSTEM_WSF_DELETE_PROTECTED', 'Cet espace est protégé : il ne peut pas être supprimé.');
define ('_SYSTEM_WSF_DELETE_CHILDREN', 'Supprimez d\'abord ses sous-espaces.');
define ('_SYSTEM_WSF_DELETE_USERS', 'Détachez d\'abord ses utilisateurs.');
define ('_SYSTEM_WSF_NOTES', 'Notes');

// Gabarits de génération 4 : onglet Groupe
define ('_SYSTEM_GRF_NEW_TITLE', 'Nouveau sous-groupe');
define ('_SYSTEM_GRF_NEW_WORKSPACE_TITLE', 'Nouveau groupe de l\'espace');
define ('_SYSTEM_GRF_NEW_PLACEHOLDER', 'Nom du groupe');
define ('_SYSTEM_GRF_CREATE', 'Créer le groupe');
define ('_SYSTEM_GRF_STAT_GROUPS', 'sous-groupes');
define ('_SYSTEM_GRF_SEC_IDENTITY_DESC', 'Nom du groupe d\'utilisateurs et sa visibilité dans les sous-espaces.');
define ('_SYSTEM_GRF_SEC_IDENTITY_WS_DESC', 'Le groupe est créé dans cet espace et y est rattaché : ses membres y ont accès.');
define ('_SYSTEM_GRF_SHARED_HINT', 'Disponible pour les sous-espaces, qui peuvent le rattacher.');
define ('_SYSTEM_GRF_ACTIONS', 'Actions sur le groupe');
define ('_SYSTEM_GRF_CLONE_TITLE', 'Cloner le groupe');
define ('_SYSTEM_GRF_CLONE_HINT', 'Crée un groupe frère avec les mêmes propriétés, sans ses utilisateurs.');
define ('_SYSTEM_GRF_CLONE_DENIED', 'Réservé aux administrateurs d\'espace.');
define ('_SYSTEM_GRF_CLONE_CONFIRM', 'Cloner ce groupe ?');
define ('_SYSTEM_GRF_DELETE_TITLE', 'Supprimer le groupe');
define ('_SYSTEM_GRF_DELETE_HINT', 'Le groupe est vide : il peut être supprimé.');
define ('_SYSTEM_GRF_DELETE_CHILDREN', 'Supprimez d\'abord ses sous-groupes.');
define ('_SYSTEM_GRF_DELETE_CONFIRM', 'Êtes-vous certain de vouloir supprimer ce groupe ?');

// Gabarits de génération 4 : onglet Modules d'un espace
define ('_SYSTEM_MODF_TITLE', 'Propriétés de « <MODULE> »');
define ('_SYSTEM_MODF_SEC_DISPLAY', 'Affichage');
define ('_SYSTEM_MODF_SEC_DISPLAY_DESC', 'Disponibilité du module et présence dans le menu.');
define ('_SYSTEM_MODF_SEC_SHARING', 'Sous-espaces');
define ('_SYSTEM_MODF_SEC_SHARING_DESC', 'Partage de cette instance avec les espaces enfants.');
define ('_SYSTEM_MODF_SEC_VIEW', 'Vue des données');
define ('_SYSTEM_MODF_SEC_VIEW_DESC', 'Espaces dont le module voit les données.');
define ('_SYSTEM_MODF_TRANSVERSE_HINT', 'Ajoute les espaces frères à la vue choisie.');
define ('_SYSTEM_MODF_TRANSVERSE', 'transversale');
define ('_SYSTEM_MODF_APPLYHERITAGE_CONFIRM', 'Rattacher ce module à tous les sous-espaces ?');
define ('_SYSTEM_MODF_OWN', 'Modules de l\'espace');
define ('_SYSTEM_MODF_OWN_COUNT', '<NB> module(s), dans l\'ordre du menu');
define ('_SYSTEM_MODF_OWN_NONE', 'Aucun module dans cet espace : ajoutez-en un ci-dessous.');
define ('_SYSTEM_MODF_POSITION', 'Ordre');
define ('_SYSTEM_MODF_MOVEUP', 'Monter');
define ('_SYSTEM_MODF_MOVEDOWN', 'Descendre');
define ('_SYSTEM_MODF_CONFIGURE', 'Propriétés');
define ('_SYSTEM_MODF_DETACH', 'Détacher de l\'espace');
define ('_SYSTEM_MODF_DELETE_CONFIRM', 'Êtes-vous certain de vouloir supprimer ce module et ses données ?');
define ('_SYSTEM_MODF_DETACH_CONFIRM', 'Êtes-vous certain de vouloir détacher ce module de l\'espace ?');
define ('_SYSTEM_MODF_SHAREDBY', 'partagé par <WORKSPACE>');
define ('_SYSTEM_MODF_FROMPARENT', 'instance d\'un autre espace');
define ('_SYSTEM_MODF_ADD', 'Ajouter un module');
define ('_SYSTEM_MODF_ADD_DESC', 'Utiliser une instance partagée par un espace parent, ou créer une nouvelle instance.');
define ('_SYSTEM_MODF_NEWINSTANCE', 'Nouvelle instance');
define ('_SYSTEM_MODF_USE', 'Utiliser');
define ('_SYSTEM_MODF_INSTANTIATE', 'Instancier');

// Gabarits de génération 4 : paramètres des modules (système, espace, utilisateur)
define ('_SYSTEM_PARAMF_SUBTITLE', 'Valeurs par défaut des paramètres de chaque module, que les espaces et les utilisateurs peuvent remplacer.');
define ('_SYSTEM_PARAMF_MODULES', 'Modules');
define ('_SYSTEM_PARAMF_NOMODULE', 'Aucun module à paramétrer.');
define ('_SYSTEM_PARAMF_LEVEL_DEFAULT', 'valeurs par défaut');
define ('_SYSTEM_PARAMF_LEVEL_WORKSPACE', 'valeurs de l\'espace');
define ('_SYSTEM_PARAMF_LEVEL_USER', 'vos valeurs');
define ('_SYSTEM_PARAMF_COUNT', '<NB> paramètre(s)');

// Gabarits de génération 4 : onglet Rôles
define ('_SYSTEM_ROLEF_TAB_LABEL', 'Rôles et attributions');
define ('_SYSTEM_ROLEF_TAB_ROLES', 'Rôles');
define ('_SYSTEM_ROLEF_TAB_ASSIGN', 'Attributions');
define ('_SYSTEM_ROLEF_NEW', 'Nouveau rôle');
define ('_SYSTEM_ROLEF_EDIT', 'Modifier le rôle');
define ('_SYSTEM_ROLEF_CHOOSE_DESC', 'Un rôle regroupe des actions d\'un module. Choisissez le module concerné.');
define ('_SYSTEM_ROLEF_MODULE', 'Module');
define ('_SYSTEM_ROLEF_NEXT', 'Suivant');
define ('_SYSTEM_ROLEF_FORM_DESC', 'Rôle du module <MODULE>.');
define ('_SYSTEM_ROLEF_SHARED_HINT', 'Utilisable dans les sous-espaces qui utilisent ce module.');
define ('_SYSTEM_ROLEF_ACTIONS', 'Actions autorisées');
define ('_SYSTEM_ROLEF_ACTIONS_DESC', 'Ce que peuvent faire les utilisateurs et les groupes à qui le rôle est attribué.');
define ('_SYSTEM_ROLEF_NOACTION', 'Ce module ne déclare aucune action.');
define ('_SYSTEM_ROLEF_CHECKALL', 'Tout cocher');
define ('_SYSTEM_ROLEF_UNCHECKALL', 'Tout décocher');
define ('_SYSTEM_ROLEF_NONE', 'Aucun rôle pour les modules de cet espace.');
define ('_SYSTEM_ROLEF_NOMODULE', 'Aucun module dans cet espace : les rôles portent sur les actions des modules.');
define ('_SYSTEM_ROLEF_CHOOSE', 'Choisir un rôle');
define ('_SYSTEM_ROLEF_ASSIGN_HINT', 'Choisissez un rôle pour voir et modifier ses attributions.');
define ('_SYSTEM_ROLEF_SEARCH', 'Rechercher un utilisateur ou un groupe de l\'espace…');
define ('_SYSTEM_ROLEF_SEARCH_GO', 'Rechercher');
define ('_SYSTEM_ROLEF_SEARCH_NONE', 'Aucun utilisateur ni groupe de l\'espace ne correspond.');
define ('_SYSTEM_ROLEF_SEARCH_HINT', 'Utilisateurs et groupes rattachés à l\'espace :');
define ('_SYSTEM_ROLEF_ASSIGNED', 'Rôle attribué à');
define ('_SYSTEM_ROLEF_ASSIGNED_NONE', 'Ce rôle n\'est attribué à personne dans cet espace : utilisez la recherche pour l\'attribuer.');
define ('_SYSTEM_ROLEF_ASSIGNED_TOAST', 'Rôle attribué');
define ('_SYSTEM_ROLEF_UNASSIGNED', 'Attribution retirée');
define ('_SYSTEM_ROLEF_ASSIGN', 'Attribuer');
define ('_SYSTEM_ROLEF_UNASSIGN', 'Retirer');
define ('_SYSTEM_ROLEF_UNASSIGN_USER_CONFIRM', 'Retirer ce rôle à cet utilisateur ?');
define ('_SYSTEM_ROLEF_UNASSIGN_GROUP_CONFIRM', 'Retirer ce rôle à ce groupe ?');
define ('_SYSTEM_ROLEF_FILTER', 'Filtrer les rôles…');
define ('_SYSTEM_ROLEF_COUNT', 'rôle(s)');
define ('_SYSTEM_ROLEF_ROLE', 'Rôle');
define ('_SYSTEM_ROLEF_ORIGIN', 'Origine');
define ('_SYSTEM_ROLEF_ASSIGNMENTS', 'Attributions');
define ('_SYSTEM_ROLEF_NOMATCH', 'Aucun rôle ne correspond.');
define ('_SYSTEM_ROLEF_DELETE_CONFIRM', 'Êtes-vous certain de vouloir supprimer ce rôle et ses attributions ?');

// Gabarits de génération 4 : fiche d'un utilisateur (administration, profil)
define ('_SYSTEM_USERF_READONLY', 'La modification du profil est désactivée sur cette plateforme : adressez-vous à votre administrateur.');
define ('_SYSTEM_USERF_SEC_IDENTITY', 'Identité');
define ('_SYSTEM_USERF_SEC_IDENTITY_DESC', 'Le nom et le prénom servent aussi à repérer les homonymes à la création.');
define ('_SYSTEM_USERF_PHOTO_CHOOSE', 'Choisir une photo');
define ('_SYSTEM_USERF_PHOTO_HINT', 'Prise en compte à l\'enregistrement de la fiche.');
define ('_SYSTEM_USERF_SEC_WORK', 'Informations professionnelles');
define ('_SYSTEM_USERF_SEC_WORK_DESC', 'Rattachement dans l\'organisation, repris par l\'annuaire.');
define ('_SYSTEM_USERF_SEC_CONTACT', 'Coordonnées');
define ('_SYSTEM_USERF_SEC_CONTACT_DESC', 'Courriel de notification et numéros de téléphone.');
define ('_SYSTEM_USERF_SEC_PLACE', 'Lieu de travail');
define ('_SYSTEM_USERF_SEC_PLACE_DESC', 'Adresse postale et localisation du poste.');
define ('_SYSTEM_USERF_SEC_ACCOUNT', 'Compte');
define ('_SYSTEM_USERF_SEC_ACCOUNT_DESC', 'Identifiant de connexion, mot de passe et validité du compte.');
define ('_SYSTEM_USERF_SEC_ACCOUNT_PROFILE_DESC', 'Pour changer de mot de passe, saisissez l\'actuel puis deux fois le nouveau.');
define ('_SYSTEM_USERF_LOGIN_EXISTS', 'L\'identifiant « <LOGIN> » existe déjà : choisissez-en un autre.');
define ('_SYSTEM_USERF_OLDPASSWORD', 'Mot de passe actuel');
define ('_SYSTEM_USERF_OLDPASSWORD_HINT', 'Seulement pour changer de mot de passe.');
define ('_SYSTEM_USERF_PASSWORD', 'Mot de passe');
define ('_SYSTEM_USERF_NEWPASSWORD', 'Nouveau mot de passe');
define ('_SYSTEM_USERF_NEWPASSWORD_HINT', 'Laisser vide pour ne pas le changer.');
define ('_SYSTEM_USERF_PASSWORD_MISMATCH', 'Saisissez deux fois le même mot de passe.');
define ('_SYSTEM_USERF_PASSWORD_INVALID', "Le mot de passe est refusé.\n\nIl doit contenir au moins 8 caractères, une minuscule, une majuscule, un chiffre et un caractère de ponctuation.");
define ('_SYSTEM_USERF_PASSWORD_EXPIRE', 'Expiration du mot de passe');
define ('_SYSTEM_USERF_PASSWORD_VALIDITY', 'Validité du mot de passe (jours)');
define ('_SYSTEM_USERF_PASSWORD_VALIDITY_HINT', '0 : pas de limite.');
define ('_SYSTEM_USERF_EXPIRE_HINT', 'Vide : le compte n\'expire pas.');
define ('_SYSTEM_USERF_FORCEUPDATE', 'Changement du mot de passe à la prochaine connexion');
define ('_SYSTEM_USERF_FORCEUPDATE_HINT', 'L\'utilisateur devra choisir un nouveau mot de passe en se connectant.');
define ('_SYSTEM_USERF_DISABLED', 'Compte désactivé');
define ('_SYSTEM_USERF_DISABLED_HINT', 'L\'utilisateur ne peut plus se connecter ; sa fiche et ses rattachements sont conservés.');
define ('_SYSTEM_USERF_LEVEL_HINT', 'Niveau d\'administration dans cet espace, au plus le vôtre.');
define ('_SYSTEM_USERF_SEC_PREFS', 'Préférences');
define ('_SYSTEM_USERF_SEC_PREFS_DESC', 'Notifications, fuseau horaire et couleur dans les plannings.');
define ('_SYSTEM_USERF_TICKETSBYEMAIL_HINT', 'Une copie de chaque message reçu est envoyée par courriel.');
define ('_SYSTEM_USERF_COLOR_HINT', 'Utilisée par les plannings et calendriers ; vide : aucune.');
define ('_SYSTEM_USERF_NOTE_NEW', '* champs obligatoires ; le nom et le prénom servent à détecter les homonymes.');
define ('_SYSTEM_USERF_CREATE', 'Créer l\'utilisateur');
define ('_SYSTEM_USERF_DOCUMENTS', 'Documents liés à l\'utilisateur');

// Gabarits de génération 4 : groupes rattachés, rattachements, import
define ('_SYSTEM_GRL_FILTER', 'Filtrer les groupes…');
define ('_SYSTEM_GRL_COUNT', 'groupe(s)');
define ('_SYSTEM_GRL_NONE', 'Aucun groupe n\'est rattaché à cet espace : rattachez un groupe pour que ses membres y aient accès.');
define ('_SYSTEM_GRL_NOMATCH', 'Aucun groupe ne correspond.');
define ('_SYSTEM_GRL_PATH', 'Chemin');
define ('_SYSTEM_GRL_LEVEL_EDIT', 'Niveau dans l\'espace');
define ('_SYSTEM_GRL_LEVEL_DESC', 'Niveau d\'administration des membres de ce groupe dans l\'espace.');
define ('_SYSTEM_GRL_LEVEL_HINT', 'Au plus votre propre niveau.');
define ('_SYSTEM_GRL_DETACH', 'Détacher de l\'espace');
define ('_SYSTEM_GRL_DETACH_CONFIRM', 'Détacher ce groupe de l\'espace ? Ses membres n\'y auront plus accès par ce groupe.');
define ('_SYSTEM_ATT_USER_INTRO', 'Utilisateurs existants que vous pouvez rattacher à « <TARGET> ».');
define ('_SYSTEM_ATT_GROUP_INTRO', 'Groupes que vous pouvez rattacher à « <TARGET> » : leurs membres accéderont à l\'espace.');
define ('_SYSTEM_ATT_USER_COUNT', 'utilisateur(s)');
define ('_SYSTEM_ATT_USER_NONE', 'Aucun utilisateur à rattacher.');
define ('_SYSTEM_ATT_GROUP_NONE', 'Aucun groupe à rattacher.');
define ('_SYSTEM_ATT_NOGROUP', 'sans groupe');
define ('_SYSTEM_ATT_SERVER_SEARCH', 'Nom, prénom, identifiant ou courriel…');
define ('_SYSTEM_ATT_TOOMANY', '<NB> utilisateurs peuvent être rattachés : recherchez celui qui vous intéresse.');
define ('_SYSTEM_IMP_FILE', 'Fichier');
define ('_SYSTEM_IMP_FILE_DESC', 'Fichier CSV dont la première ligne nomme les colonnes ; délimiteur : guillemet, échappement : barre oblique inverse.');
define ('_SYSTEM_IMP_SOURCE', 'Fichier CSV');
define ('_SYSTEM_IMP_SEPARATOR', 'Séparateur');
define ('_SYSTEM_IMP_SEP_COMMA', 'Virgule ( , )');
define ('_SYSTEM_IMP_SEP_SEMICOLON', 'Point-virgule ( ; )');
define ('_SYSTEM_IMP_COLUMNS', 'Colonnes reconnues');
define ('_SYSTEM_IMP_COLUMNS_DESC', 'Toutes ou une partie de ces colonnes, dans n\'importe quel ordre ; * obligatoire.');
define ('_SYSTEM_IMP_COLUMN', 'Colonne');
define ('_SYSTEM_IMP_SEND', 'Envoyer et vérifier');
define ('_SYSTEM_IMP_FORMAT', 'Un aperçu est affiché avant l\'import.');
define ('_SYSTEM_IMP_INVALIDFILE', 'Fichier invalide.');
define ('_SYSTEM_IMP_INVALIDFIELD', 'Colonne « <FIELD> » inconnue : elle sera ignorée.');
define ('_SYSTEM_IMP_MISSINGFIELD', 'Colonne obligatoire « <FIELD> » absente.');
define ('_SYSTEM_IMP_BADROW', 'Ligne <NUM> : nombre de colonnes différent de l\'en-tête.');
define ('_SYSTEM_IMP_VALID', 'Le fichier semble conforme.');
define ('_SYSTEM_IMP_WARNINGS', 'Le fichier peut être importé, avec ces remarques :');
define ('_SYSTEM_IMP_CRITICAL', 'Le fichier ne peut pas être importé :');
define ('_SYSTEM_IMP_PREVIEW', 'Aperçu de l\'import');
define ('_SYSTEM_IMP_CANCEL', 'Choisir un autre fichier');
define ('_SYSTEM_IMP_CONFIRM', 'Importer <NB> utilisateur(s)');
define ('_SYSTEM_IMP_CONFIRM_MSG', 'Créer les <NB> utilisateurs du fichier dans ce groupe ?');
define ('_SYSTEM_IMP_DONE', 'Import terminé.');
define ('_SYSTEM_IMP_DONE_ERRORS', 'Import terminé, avec des lignes ignorées :');
define ('_SYSTEM_IMP_AGAIN', 'Importer un autre fichier');

// Gabarits de génération 4 : écrans du niveau Système
define ('_SYSTEM_SYSMOD_SUBTITLE', 'Types de modules installés sur la plateforme, mises à jour et paquets disponibles dans le dossier install.');
define ('_SYSTEM_SYSMOD_UPDATES', 'Mises à jour disponibles');
define ('_SYSTEM_SYSMOD_UPDATES_DESC', 'Le paquet du dossier install est plus récent que le module installé.');
define ('_SYSTEM_SYSMOD_INSTALLED', 'Modules installés');
define ('_SYSTEM_SYSMOD_INSTALLED_COUNT', '<NB> type(s) de modules');
define ('_SYSTEM_SYSMOD_FILTER', 'Filtrer les modules…');
define ('_SYSTEM_SYSMOD_MODULE', 'Module');
define ('_SYSTEM_SYSMOD_INSTANCES', 'Instances');
define ('_SYSTEM_SYSMOD_CAPS', 'Déclare');
define ('_SYSTEM_SYSMOD_WCE', 'Objets WebEdit');
define ('_SYSTEM_SYSMOD_AVAILABLE', '<VERSION> disponible');
define ('_SYSTEM_SYSMOD_RELOADDESC', 'Recharger la description');
define ('_SYSTEM_SYSMOD_RELOADDESC_DESC', 'Paramètres et actions de description.xml');
define ('_SYSTEM_SYSMOD_RELOADDESC_CONFIRM', 'Recharger la description de ce module (paramètres, actions) ?');
define ('_SYSTEM_SYSMOD_RELOADMB', 'Recharger la métabase');
define ('_SYSTEM_SYSMOD_RELOADMB_DESC', 'Tables, objets et relations de mb.xml');
define ('_SYSTEM_SYSMOD_RELOADMB_CONFIRM', 'Recharger la métabase de ce module ?');
define ('_SYSTEM_SYSMOD_UNINSTALL_DESC', 'Supprime ses fichiers, ses tables et ses instances');
define ('_SYSTEM_SYSMOD_UNINSTALL_CONFIRM', 'Êtes-vous certain de vouloir désinstaller ce module ? Ses fichiers, ses tables, ses instances et leurs données seront supprimés.');
define ('_SYSTEM_SYSMOD_NOMATCH', 'Aucun module ne correspond.');
define ('_SYSTEM_SYSMOD_NEW', 'Modules disponibles');
define ('_SYSTEM_SYSMOD_NEW_DESC', 'Paquets du dossier install qui ne sont pas encore installés.');
define ('_SYSTEM_SYSMOD_NEW_NONE', 'Tous les paquets du dossier install sont installés.');
define ('_SYSTEM_SYSMOD_DEMO', 'Site de démonstration');
define ('_SYSTEM_SYSMOD_DEMO_DESC', 'Installe le module Site web (webedit) et publie un petit site fictif dans le premier espace de travail, avec le gabarit « corporate », puis y ajoute un annuaire, un agenda, une réservation de salles, des actualités et des rapports garnis de données fictives. Proposé seulement tant qu\'aucun module n\'est installé.');
define ('_SYSTEM_SYSMOD_DEMO_BUTTON', 'Générer le site de démonstration');
define ('_SYSTEM_SYSMOD_DEMO_CONFIRM', 'Installer les modules webedit, directory, planning, booking, news2 et dbreport, et publier la démonstration ?');
define ('_SYSTEM_SYSMOD_DEMO_DONE', 'Site de démonstration');
define ('_SYSTEM_SYSMOD_DEMO_SEE', 'Voir le site');
define ('_SYSTEM_SYSMOD_XMLERROR', 'Erreur dans description.xml');
define ('_SYSTEM_SYSMOD_INSTALL_TITLE', 'Installation de « <MODULE> »');
define ('_SYSTEM_SYSMOD_UPDATE_TITLE', 'Mise à jour de « <MODULE> » (<FROM> → <TO>)');
define ('_SYSTEM_SYSMOD_REPORT_DESC', 'Compte rendu des opérations.');
define ('_SYSTEM_SYSUPD_SUBTITLE', 'Les fichiers de Ploopi sont plus récents que la base de données.');
define ('_SYSTEM_SYSUPD_TITLE', 'Mise à jour de la base de données');
define ('_SYSTEM_SYSUPD_DBVERSION', 'version de la base');
define ('_SYSTEM_SYSUPD_FILESVERSION', 'version des fichiers');
define ('_SYSTEM_SYSUPD_EXPLAIN', 'Pour terminer la mise à jour, appliquez à la base de données les scripts des versions intermédiaires :');
define ('_SYSTEM_SYSUPD_TOAPPLY', 'version <VERSION>');
define ('_SYSTEM_SYSUPD_APPLIED', 'Version <VERSION> appliquée.');
define ('_SYSTEM_SYSUPD_UNREADABLE', 'Fichier illisible : vérifiez les droits en lecture.');
define ('_SYSTEM_SYSUPD_BACKUP', 'Sauvegardez la base de données avant de lancer la mise à jour.');
define ('_SYSTEM_SYSUPD_EXECUTE', 'Mettre à jour la base de données');
define ('_SYSTEM_SYSUPD_CONFIRM', 'Lancer la mise à jour de la base de données ?');
define ('_SYSTEM_SYSUPD_DONE', 'Mise à jour terminée');
define ('_SYSTEM_SYSUPD_NOTHING', 'Aucun script à appliquer.');
define ('_SYSTEM_DIR_SUBTITLE', 'Tous les utilisateurs de la plateforme, leurs groupes, leurs espaces et leurs rôles.');
define ('_SYSTEM_DIR_WORKSPACE_INTRO', 'Utilisateurs de l\'espace et de ses sous-espaces, rattachés directement ou par leurs groupes.');
define ('_SYSTEM_DIR_FILTERS_DESC', 'Recherche dans l\'annuaire ; tous les champs sont facultatifs.');
define ('_SYSTEM_DIR_ACTIVEFILTERS', '<NB> filtre(s) actif(s)');
define ('_SYSTEM_DIR_DELETEALL', 'Supprimer les utilisateurs trouvés');
define ('_SYSTEM_DIR_DELETEALL_CONFIRM', 'Attention : tous les utilisateurs correspondant à ce filtre seront supprimés définitivement. Continuer ?');
define ('_SYSTEM_DIR_COUNT', '<NB> utilisateur trouvé');
define ('_SYSTEM_DIR_COUNT_PLURAL', '<NB> utilisateurs trouvés');
define ('_SYSTEM_DIR_TOOMANY', '<NB> réponses (<MAX> au plus affichées) : précisez la recherche, ou exportez les résultats.');
define ('_SYSTEM_DIR_SYSTEM_HINT', 'Les utilisateurs se gèrent depuis les espaces et les groupes : le nom d\'un utilisateur ouvre sa fiche dans son premier groupe.');
define ('_SYSTEM_DIR_NONE', 'Aucun utilisateur ne correspond à ces filtres.');
define ('_SYSTEM_DIR_REFINE', 'Affiner dans les résultats…');
define ('_SYSTEM_DIR_WORKSPACES', 'Espaces et rôles');
define ('_SYSTEM_DIR_NOGROUP', 'Aucun groupe');
define ('_SYSTEM_DIR_NOWORKSPACE', 'Aucun espace');
define ('_SYSTEM_DIR_DELETE_CONFIRM', 'Êtes-vous certain de vouloir supprimer définitivement cet utilisateur ?');
define ('_SYSTEM_TABLE_EMPTY', 'Aucune donnée.');
define ('_SYSTEM_TOOLS_SUBTITLE', 'Surveillance, journaux et maintenance de la plateforme.');
define ('_SYSTEM_TOOLS_T_CONNECTEDUSERS', 'Utilisateurs connectés');
define ('_SYSTEM_TOOLS_T_ACTIONHISTORY', 'Journal des actions');
define ('_SYSTEM_TOOLS_T_DIAGNOSTIC', 'Diagnostic');
define ('_SYSTEM_TOOLS_T_SERVERLOAD', 'Charge du serveur');
define ('_SYSTEM_TOOLS_T_SQLDUMP', 'Sauvegarde SQL');
define ('_SYSTEM_TOOLS_T_PHPINFO', 'Configuration de PHP');
define ('_SYSTEM_TOOLS_CONNECTEDUSERS_DESC', 'Sessions en cours : utilisateur, espace, module, adresse IP.');
define ('_SYSTEM_TOOLS_ACTIONHISTORY_DESC', 'Journal des actions des utilisateurs, filtrable et exportable.');
define ('_SYSTEM_TOOLS_DIAGNOSTIC_DESC', 'Vérification de la configuration de PHP, des extensions et des dossiers.');
define ('_SYSTEM_TOOLS_SERVERLOAD_DESC', 'Charge, temps de réponse et débit sur plusieurs périodes.');
define ('_SYSTEM_TOOLS_STATS', 'Statistiques');
define ('_SYSTEM_TOOLS_STATS_DESC', 'Statistiques de fréquentation et dernières requêtes.');
define ('_SYSTEM_TOOLS_SQLDUMP_DESC', 'Télécharge les données dans un fichier SQL compressé (petites bases seulement ; ne remplace pas une sauvegarde).');
define ('_SYSTEM_TOOLS_PHPINFO_DESC', 'Configuration complète de PHP sur le serveur.');
define ('_SYSTEM_TOOLS_PHPINFO_VERSION', 'Version');
define ('_SYSTEM_TOOLS_PHPINFO_SAPI', 'Interface serveur');
define ('_SYSTEM_TOOLS_PHPINFO_INI', 'Fichier php.ini chargé');
define ('_SYSTEM_TOOLS_PHPINFO_SECTION', 'Section');
define ('_SYSTEM_TOOLS_PHPINFO_DIRECTIVE', 'Directive');
define ('_SYSTEM_TOOLS_PHPINFO_LOCAL', 'Valeur');
define ('_SYSTEM_TOOLS_PHPINFO_MASTER', 'Valeur principale (si différente)');
define ('_SYSTEM_TOOLS_PHPINFO_FILTER', 'Filtrer : directive, section ou valeur…');
define ('_SYSTEM_TOOLS_PHPINFO_COUNT', 'directive(s)');
define ('_SYSTEM_TOOLS_PHPINFO_NONE', 'Aucune directive ne correspond.');
// Outils : colonnes, filtres et légendes (auparavant écrits en dur)
define ('_SYSTEM_TOOLS_COL_DATETIME', 'Date et heure');
define ('_SYSTEM_TOOLS_COL_IP', 'Adresse IP');
define ('_SYSTEM_TOOLS_COL_USER', 'Utilisateur');
define ('_SYSTEM_TOOLS_COL_WORKSPACE', 'Espace de travail');
define ('_SYSTEM_TOOLS_COL_MODULE', 'Module');
define ('_SYSTEM_TOOLS_COL_ACTION', 'Action');
define ('_SYSTEM_TOOLS_COL_RECORD', 'Enregistrement');
define ('_SYSTEM_TOOLS_COL_DOMAIN', 'Domaine');
define ('_SYSTEM_TOOLS_COL_NAME', 'Nom');
define ('_SYSTEM_TOOLS_COL_LOGIN', 'Identifiant');
define ('_SYSTEM_TOOLS_COL_PERIOD', 'Période');
define ('_SYSTEM_TOOLS_COL_LOAD', 'Charge (%)');
define ('_SYSTEM_TOOLS_COL_RESPONSE', 'Temps de réponse (ms)');
define ('_SYSTEM_TOOLS_COL_RPS', 'Requêtes par seconde');
define ('_SYSTEM_TOOLS_COL_PPS', 'Pages par seconde');
define ('_SYSTEM_TOOLS_COL_BANDWIDTH', 'Bande passante (Ko)');
define ('_SYSTEM_TOOLS_COL_CLIENT', 'Client');
define ('_SYSTEM_TOOLS_COL_PAGEMS', 'Page (ms)');
define ('_SYSTEM_TOOLS_COL_SQLMS', 'SQL (ms)');
define ('_SYSTEM_TOOLS_COL_SQLCOUNT', 'Requêtes SQL');
define ('_SYSTEM_TOOLS_COL_PLOOPI', 'Ploopi');
define ('_SYSTEM_TOOLS_COL_URI', 'Adresse (URI)');
define ('_SYSTEM_TOOLS_ANONYMOUS', 'anonyme');
define ('_SYSTEM_TOOLS_FILTER_DATE1', 'Entre le');
define ('_SYSTEM_TOOLS_FILTER_DATE2', 'et le');
define ('_SYSTEM_TOOLS_LIVELOAD', 'Charge du système en temps réel (<NB> cœurs)');
define ('_SYSTEM_TOOLS_REQHISTORY', 'Historique des <NB> dernières requêtes');
define ('_SYSTEM_TOOLS_LOGROWS', '<NB> lignes dans le journal');
define ('_SYSTEM_TOOLS_LOGPURGE', ', à vider régulièrement, ou par la tâche planifiée (cron) de Ploopi');
define ('_SYSTEM_TOOLS_DIAG_RED', 'Pastille rouge : problème potentiellement bloquant.');
define ('_SYSTEM_TOOLS_DIAG_ORANGE', 'Pastille orange : une fonction ou un réglage peut poser problème.');
define ('_SYSTEM_TOOLS_DIAG_GREEN', 'Pastille verte : tout va bien.');
define ('_SYSTEM_TOOLS_FROM', 'Du');
define ('_SYSTEM_TOOLS_TO', 'Au');
define ('_SYSTEM_TOOLS_ACTION', 'Action');
define ('_SYSTEM_TOOLS_RECORD', 'Enregistrement');
define ('_SYSTEM_TOOLS_IP', 'Adresse IP');
define ('_SYSTEM_TOOLS_LOG_DELETE', 'Effacer le journal filtré');
define ('_SYSTEM_TOOLS_LOG_DELETE_CONFIRM', 'Effacer définitivement les entrées du journal qui correspondent au filtre ?');
define ('_SYSTEM_TOOLS_EXPORTCSV', 'Exporter (CSV)');
define ('_SYSTEM_TOOLS_LOG_COUNT', '<NB> action(s) trouvée(s)');
define ('_SYSTEM_TOOLS_LOG_LIMIT', 'Seules les <NB> plus récentes sont affichées : précisez les filtres.');
define ('_SYSTEM_TOOLS_MONITORING', 'Rafraîchissement automatique');
define ('_SYSTEM_TOOLS_MONITORING_OFF', 'Pas de rafraîchissement automatique');
define ('_SYSTEM_TOOLS_MONITORING_EVERY', 'Rafraîchir toutes les <NB> s');

// Gabarits de génération 4 : Mon espace
define ('_SYSTEM_MYS_PROFILE_SUBTITLE', 'Vos informations, votre mot de passe et vos préférences.');
define ('_SYSTEM_MYS_PROFILE_INCOMPLETE', 'Votre profil n\'est pas complet : merci de renseigner les champs marqués d\'une étoile.');
define ('_SYSTEM_MYS_PARAMS_SUBTITLE', 'Vos réglages des modules, qui priment sur ceux de l\'espace.');
define ('_SYSTEM_MYS_ACTIONS_SUBTITLE', 'Ce que vos rôles vous permettent de faire, espace par espace.');
define ('_SYSTEM_MYS_NOACTION', 'Ce module ne déclare aucune action.');
define ('_SYSTEM_MYS_NOWORKSPACE', 'Vous n\'êtes rattaché à aucun espace.');
define ('_SYSTEM_MYS_NOTES_SUBTITLE', 'Les notes que vous avez posées sur les contenus, classées par étiquettes.');
define ('_SYSTEM_MYS_TAGS', 'Étiquettes');
define ('_SYSTEM_MYS_ALLNOTES', 'Toutes les notes');
define ('_SYSTEM_MYS_TAG_USED', 'utilisée <NB> fois');
define ('_SYSTEM_MYS_NOTAGS', 'Aucune étiquette.');
define ('_SYSTEM_MYS_NONOTES', 'Aucune note.');
define ('_SYSTEM_MYS_NOTE_NOLABEL', 'Contenu sans titre');
define ('_SYSTEM_MYS_NOTE_TEXT', 'Texte');
define ('_SYSTEM_MYS_NOTE_TAGS', 'Étiquettes');
define ('_SYSTEM_MYS_NOTE_TAGS_HINT', 'Facultatives, séparées par des espaces.');
define ('_SYSTEM_MYS_NOTE_PRIVATE', 'Privée');
define ('_SYSTEM_MYS_NOTE_PRIVATE_HINT', 'Visible de vous seul.');
define ('_SYSTEM_MYS_NOTE_SAVE', 'Enregistrer');
define ('_SYSTEM_MYS_NOTE_CONFIRMDELETE', 'Supprimer cette note ?');
define ('_SYSTEM_MYS_SEARCH_SUBTITLE', 'Recherche dans les contenus indexés des modules de l\'espace.');
define ('_SYSTEM_MYS_SEARCH_PLACEHOLDER', 'Mots clés…');
define ('_SYSTEM_MYS_SEARCH_ALLMODULES', 'Tous les modules');
define ('_SYSTEM_MYS_SEARCH_PROMPT', 'Saisissez un ou plusieurs mots clés.');
define ('_SYSTEM_MYS_SEARCH_NONE', 'Aucun contenu ne correspond à cette recherche.');
define ('_SYSTEM_MYS_SEARCH_ALL', 'Tous les résultats pour « <TERM> »');
define ('_SYSTEM_MSG_SUBTITLE', 'Messages échangés avec les autres utilisateurs de la plateforme.');
define ('_SYSTEM_MSG_BOXES', 'Boîtes');
define ('_SYSTEM_MSG_SORT', 'Tri');
define ('_SYSTEM_MSG_SORT_REPLY', 'Tri par date de dernière réponse');
define ('_SYSTEM_MSG_SORT_TICKET', 'Tri par date d\'envoi');
define ('_SYSTEM_MSG_TODAY', 'Aujourd\'hui');
define ('_SYSTEM_MSG_FROMME', 'Moi');
define ('_SYSTEM_MSG_FROMSYSTEM', 'Ploopi');
define ('_SYSTEM_MSG_RECIPIENTS_COUNT', '<NB> destinataire(s)');
define ('_SYSTEM_MSG_TOVALIDATE', 'À valider');
define ('_SYSTEM_MSG_REPLIES', 'Réponses');
define ('_SYSTEM_MSG_NEW', 'Nouveau');
define ('_SYSTEM_MSG_STATE_UNREAD', 'non lu');
define ('_SYSTEM_MSG_STATE_READ', 'lu');
define ('_SYSTEM_MSG_STATE_DONE', 'validé');
define ('_SYSTEM_MSG_READ_ON', 'lu le <DATE> à <TIME>');
define ('_SYSTEM_MSG_DONE_ON', 'validé le <DATE> à <TIME>');
define ('_SYSTEM_MSG_EDITED_ON', 'Modifié le <DATE> à <TIME>');
define ('_SYSTEM_MSG_VALIDATE_ASKED', 'L\'expéditeur vous demande de valider ce message.');
define ('_SYSTEM_MSG_VALIDATE', 'Valider');
define ('_SYSTEM_MSG_OBJECT_DENIED', 'Vous ne pouvez pas ouvrir cet objet');
define ('_SYSTEM_MSG_REPLY', 'Répondre');
define ('_SYSTEM_MSG_QUOTE', 'Citer');
define ('_SYSTEM_MSG_PAGES', 'Pages');
define ('_SYSTEM_MSG_SEARCH', 'Chercher dans les messages');
define ('_SYSTEM_MSG_SEARCH_RESET', 'Effacer');
define ('_SYSTEM_MSG_SEARCH_FOUND', '<NB> message(s) pour « <TERM> »');
define ('_SYSTEM_MSG_SEARCH_NONE', 'Aucun message ne correspond à la recherche.');

define ('_SYSTEM_HOSTNAME', 'Nom de Domaine');
define ('_SYSTEM_NEWHOSTNAME', 'Nouveau Nom de Domaine');
define ('_SYSTEM_HOSTSELECTED', 'Domaine sélectionné');
define ('_SYSTEM_HOSTPARAMS', 'Paramètres du Domaine');

define ('_SYSTEM_UPDATE', 'Mise à jour du Système');

define ('_SYSTEM_MODULESELECTED', 'Module sélectionné');
define ('_SYSTEM_MODULEPARAM', 'Paramètres du Module');
define ('_SYSTEM_LABEL_NOMODULEPARAM', 'Aucun paramètre pour ce module');

define ('_SYSTEM_LABELTAB_GROUPLIST', 'Liste des Groupes');

define ('_SYSTEM_LABELTAB_USERLIST', 'Liste des Utilisateurs');
define ('_SYSTEM_LABELTAB_USERATTACH', 'Choisir un Utilisateur');
define ('_SYSTEM_LABELTAB_USERMOVE', 'Déplacer un Utilisateur');
define ('_SYSTEM_LABELTAB_USERADD', 'Ajouter un Utilisateur');

define ('_SYSTEM_LABELTAB_GROUPATTACH', 'Choisir un Groupe');

define ('_SYSTEM_LABELTAB_ROLEMANAGEMENT', 'Gestion des Rôles');
define ('_SYSTEM_LABELTAB_ROLEUSERS', 'Attribution des Rôles');

define ('_SYSTEM_LABELTAB_USERIMPORT', 'Importer');

define ('_SYSTEM_LABELTAB_MESSAGEINBOX', 'Boite de Réception');
define ('_SYSTEM_LABELTAB_MESSAGEOUTBOX', 'Boite d\'Envoi');

define ('_SYSTEM_LABEL_INSTALLEDMODULES', 'Modules Installés');
define ('_SYSTEM_LABEL_NEWMODULEVERSIONS', 'Nouvelles Versions');
define ('_SYSTEM_LABEL_UNINSTALLEDMODULES', 'Nouveaux Modules');
define ('_SYSTEM_LABEL_ADDNEWMODULE', 'Ajouter un nouveau module');
define ('_SYSTEM_LABEL_ADDNEWMODULE_DESC', 'Permet d\'ajouter un nouveau module « installable » en envoyant simplement un fichier .zip');
define ('_SYSTEM_LABEL_ADDNEWMODULE_WARNING', '<b>Erreur !</b> Le répertoire « install » n\'est pas accessible en écriture pour Apache !<br>Effectuez un <a href=\'?ploopi_moduleicon=system_tools&op=diagnostic\'>diagnostic</a> pour de plus amples informations.');
define ('_SYSTEM_LABEL_INSTALLREPORT', 'Rapport d\'installation');
define ('_SYSTEM_LABEL_UPDATEREPORT', 'Rapport de mise à jour');
define ('_SYSTEM_LABEL_DIRECTORY', 'Utilisateurs');

define ('_SYSTEM_LABEL_TOOLS', 'Outils');
define ('_SYSTEM_LABEL_LOGS', 'Analyse des Logs');

define ('_SYSTEM_LABEL_LABEL', 'Libellé');
define ('_SYSTEM_LABEL_MODULETYPE', 'Type');
define ('_SYSTEM_LABEL_AUTHOR', 'Auteur');
define ('_SYSTEM_LABEL_DESCRIPTION', 'Description');
define ('_SYSTEM_LABEL_VERSION', 'Version');
define ('_SYSTEM_LABEL_DATE', 'Date');
define ('_SYSTEM_LABEL_ACTIONS', 'Actions');
define ('_SYSTEM_LABEL_METABASE', 'Métabase');
define ('_SYSTEM_LABEL_WCEOBJECTS', 'Obj. WebEdit');
define ('_SYSTEM_LABEL_MODULENAME', 'Nom');
define ('_SYSTEM_LABEL_ACTIVE', 'Actif');
define ('_SYSTEM_LABEL_VISIBLE', 'Visible');
define ('_SYSTEM_LABEL_PUBLIC', 'Public');
define ('_SYSTEM_LABEL_AUTOCONNECT', 'Connexion Auto');
define ('_SYSTEM_LABEL_SHARED', 'Partagé');
define ('_SYSTEM_LABEL_HERITED', 'Hérité');
define ('_SYSTEM_LABEL_ADMINRESTRICTED', 'Administration Restreinte');
define ('_SYSTEM_LABEL_MODULEPOSITION', 'Pos.');
define ('_SYSTEM_LABEL_MODIFY', 'Modifier');
define ('_SYSTEM_LABEL_DELETE', 'Supprimer');
define ('_SYSTEM_LABEL_DETACH', 'Détacher');
define ('_SYSTEM_LABEL_ATTACH', 'Rattacher');
define ('_SYSTEM_LABEL_MOVE', 'Déplacer');
define ('_SYSTEM_LABEL_ASSIGN', 'Affecter');
define ('_SYSTEM_LABEL_VIEWMODE', 'Vue');
define ('_SYSTEM_LABEL_ROLECHOICE', 'Rôle');
define ('_SYSTEM_LABEL_TRANSVERSE', 'Vue Transversale');
define ('_SYSTEM_APPLYHERITAGE', 'Appliquer l\'héritage aux sous-espaces');

define ('_SYSTEM_LABEL_INSTALL', 'Installer');
define ('_SYSTEM_LABEL_UPDATE', 'Mettre à Jour');
define ('_SYSTEM_LABEL_UNINSTALL', 'Désinstaller');
define ('_SYSTEM_LABEL_ROLEADD', 'Ajouter un Rôle');

define ('_SYSTEM_LABEL_TYPE', 'Type');
define ('_SYSTEM_LABEL_LASTNAME', 'Nom');
define ('_SYSTEM_LABEL_FIRSTNAME', 'Prénom');
define ('_SYSTEM_LABEL_LOGIN', 'Identifiant');
define ('_SYSTEM_LABEL_ACTION', 'Action');
define ('_SYSTEM_LABEL_USER', 'Utilisateur');
define ('_SYSTEM_LABEL_GROUP', 'Groupe');
define ('_SYSTEM_LABEL_PASSWORD', 'Mot de Passe');
define ('_SYSTEM_LABEL_PASSWORD_CONFIRM', 'Confirmation du mot de Passe');
define ('_SYSTEM_LABEL_EXPIRATION_DATE', 'Date d\'expiration du compte');
define ('_SYSTEM_LABEL_TIMEZONE', 'Fuseau Horaire');
define ('_SYSTEM_LABEL_ORIGIN', 'Origine');
define ('_SYSTEM_LABEL_LEVEL', 'Niveau');
define ('_SYSTEM_LABEL_COMMENTS', 'Commentaires');
define ('_SYSTEM_LABEL_ADDRESS', 'Adresse');
define ('_SYSTEM_LABEL_POSTALCODE', 'Code Postal');
define ('_SYSTEM_LABEL_CITY', 'Ville');
define ('_SYSTEM_LABEL_COUNTRY', 'Pays');
define ('_SYSTEM_LABEL_NUMBER', 'Numéro de Poste');
define ('_SYSTEM_LABEL_PHONE', 'Téléphone');
define ('_SYSTEM_LABEL_MOBILE', 'Tél. Portable');
define ('_SYSTEM_LABEL_FAX', 'Fax');
define ('_SYSTEM_LABEL_EMAIL', 'Courriel');
define ('_SYSTEM_LABEL_TICKETSBYEMAIL', 'Copie des messages par Courriel');
define ('_SYSTEM_LABEL_SERVERTIMEZONE', 'Synchronisé avec le fuseau horaire du serveur');
define ('_SYSTEM_LABEL_FUNCTION', 'Fonction');
define ('_SYSTEM_LABEL_RANK', 'Grade/Niveau');
define ('_SYSTEM_LABEL_ENTITY', 'Organisme');
define ('_SYSTEM_LABEL_SERVICE', 'Service');
define ('_SYSTEM_LABEL_SERVICE2', 'Bureau');
define ('_SYSTEM_LABEL_COLOR', 'Couleur');
define ('_SYSTEM_LABEL_BUILDING', 'Bâtiment');
define ('_SYSTEM_LABEL_FLOOR', 'Etage');
define ('_SYSTEM_LABEL_OFFICE', 'Bureau');
define ('_SYSTEM_LABEL_CIVILITY', 'Civilité');
define ('_SYSTEM_LABEL_PHOTO', 'Photo');

define ('_SYSTEM_LABEL_PARENTS', 'Parents');

define ('_SYSTEM_LABEL_GROUP_MANAGEMENT', 'Gestion du Groupe <LABEL> &laquo; <GROUP> &raquo;');
define ('_SYSTEM_LABEL_WORKSPACE_MANAGEMENT', 'Gestion de l\'Espace <LABEL> &laquo; <GROUP> &raquo;');

define ('_SYSTEM_LABEL_GROUP_ADD', 'Ajout d\'un sous-groupe');
define ('_SYSTEM_LABEL_WORKSPACE_ADD', 'Ajout d\'un sous-espace');

define ('_SYSTEM_LABEL_WORKSPACE_INFORMATION', 'Informations sur l\'Espace');

define ('_SYSTEM_LABEL_GROUP_INFORMATION', 'Informations sur le Groupe');
define ('_SYSTEM_LABEL_GROUP_MODIFY', 'Modifier le Groupe');
define ('_SYSTEM_LABEL_WORKSPACE_MODIFY', 'Modifier l\'Espace');
define ('_SYSTEM_LABEL_FILTERING', 'Filtrage / Sécurité');
define ('_SYSTEM_LABEL_MANAGEMENT', 'Gestion');
define ('_SYSTEM_LABEL_ACCESS', 'Accès');
define ('_SYSTEM_LABEL_INTERFACE', 'Interface');
define ('_SYSTEM_LABEL_META', 'META Informations');

//define ('_SYSTEM_LABEL_HERITAGES', 'Héritages');
define ('_SYSTEM_LABEL_USEDMODULES', 'Modules utilisés');

define ('_SYSTEM_LABEL_GROUP_CODE',         'Code');
define ('_SYSTEM_LABEL_GROUP_PRIORITY',     'Priorité');
define ('_SYSTEM_LABEL_GROUP_NAME',         'Nom');
define ('_SYSTEM_LABEL_GROUP_FATHER',       'Groupe Père');
define ('_SYSTEM_LABEL_GROUP_SYSTEM',       'Système');
define ('_SYSTEM_LABEL_GROUP_SKIN',     'Habillage');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDIP',    'IP Autorisées');
define ('_SYSTEM_LABEL_GROUP_ALLOWEDMAC',   'Adresses MAC Autorisées');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAIN',    'Domaine WEB');
define ('_SYSTEM_LABEL_GROUP_ACCESSMODE',   'Type d\'accès');
define ('_SYSTEM_LABEL_GROUP_ADMIN',        'Activation du Backoffice');
define ('_SYSTEM_LABEL_GROUP_WEB',      'Activation du Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ACCESSWARNING',    '(nécessite au moins un domaine)');
define ('_SYSTEM_LABEL_GROUP_SHARED',       'Partagé');

define ('_SYSTEM_LABEL_USER_UNDEFINED',     'Non défini');
define ('_SYSTEM_LABEL_GROUP_WEBDOMAINLIST',    'Liste des domaines Frontoffice');
define ('_SYSTEM_LABEL_GROUP_ADMINDOMAINLIST',  'Liste des domaines Backoffice');

define ('_SYSTEM_LABEL_CREATE_CLONE',       'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD',       'Créer');
define ('_SYSTEM_LABEL_CREATE_GROUP',       'Créer un Groupe');
define ('_SYSTEM_LABEL_DELETE_GROUP',       'Supprimer');

define ('_SYSTEM_LABEL_CREATE_CLONE_WORKSPACE',     'Cloner');
define ('_SYSTEM_LABEL_CREATE_CHILD_WORKSPACE',     'Créer');
define ('_SYSTEM_LABEL_DELETE_WORKSPACE',       'Supprimer');

define ('_SYSTEM_LABEL_GROUP_MODULES_MANAGEMENT',   'Gestion des Modules du Groupe &laquo; <GROUP> &raquo;');
define ('_SYSTEM_LABEL_GROUP_ROLES_MANAGEMENT',     'Gestion des Rôles du Groupe &laquo; <GROUP> &raquo;');
define ('_SYSTEM_LABEL_GROUP_AVAILABLE_MODULES',    'Modules instanciés dans cet Espace');
define ('_SYSTEM_LABEL_GROUP_USABLE_MODULES',       'Modules utilisables dans cet Espace');
define ('_SYSTEM_LABEL_MODULE_PROPERTIES',      'Propriétés du Module &laquo; <MODULE> &raquo;');
define ('_SYSTEM_LABEL_MODULE_PARAMS',          'Paramètres du Module &laquo; <MODULE> &raquo;');
define ('_SYSTEM_LABEL_MODULE_ROLES',           'Module &laquo; <MODULE> &raquo;');

define ('_SYSTEM_LABEL_MYPROFILE',              'Mon Profil');
define ('_SYSTEM_LABEL_MYDATAS',                        'Mes Informations');
define ('_SYSTEM_LABEL_MYACCOUNT',                        'Mon Compte');
define ('_SYSTEM_LABEL_ABOUT',              'A Propos');

define ('_SYSTEM_LABEL_MODULE_ADMINISTRATOR',       'Gestionnaire du Module');

define ('_SYSTEM_LABEL_SYSTEM_AVAILABLE_MODULES',   'Modules disponibles');
define ('_SYSTEM_LABEL_SYSTEM_USABLE_MODULES',      'Modules utilisables');

define ('_SYSTEM_LABEL_NO_MODULE_DEFINED',      'Aucun module défini pour ce groupe');
define ('_SYSTEM_LABEL_NO_USER_DEFINED',        'Aucun utilisateur défini pour ce groupe');
define ('_SYSTEM_LABEL_NO_ROLE_DEFINED',        'Aucun rôle défini pour ce module');
define ('_SYSTEM_LABEL_ROLE_LIST',          'Liste des rôles pour cet espace');
define ('_SYSTEM_LABEL_MODIFY_ROLE_ASSIGNMENT',     'Modifier l\'affectation des Rôles');

define ('_SYSTEM_LABEL_COMMENTARY',     'Commentaire');
define ('_SYSTEM_LABEL_RESULT',     'Résultat');

define ('_SYSTEM_TITLE_GROUPDETACH', 'Supprimer l\'accès à ce Groupe');
define ('_SYSTEM_TITLE_USERDETACH', 'Supprimer l\'accès à cet Utilisateur');

define ('_SYSTEM_MSG_CONFIRMGROUPDELETE', 'Êtes-vous certain de vouloir\nsupprimer ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMGROUPDETACH', 'Êtes-vous certain de vouloir\nsupprimer l\\\'accès à ce Groupe ?');
define ('_SYSTEM_MSG_CONFIRMUSERDETACH', 'Êtes-vous certain de vouloir\nsupprimer l\\\'accès à cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMUSERDELETE', 'Êtes-vous certain de vouloir\nsupprimer cet Utilisateur ?');
define ('_SYSTEM_MSG_CONFIRMROLEDELETE', 'Êtes-vous certain de vouloir\nsupprimer ce Rôle ?');
define ('_SYSTEM_MSG_CONFIRMLOGDELETE', 'Êtes-vous certain de vouloir\nsupprimer les Logs ?');
define ('_SYSTEM_MSG_PASSWORDERROR_JS', 'Erreur lors de la saisie du mot de passe.\nVous devez saisir deux fois le mot de passe');
define ('_SYSTEM_MSG_PASSWORDERROR2_JS', 'Vous devez saisir l\\\'ancien mot de de passe');

define ('_SYSTEM_MSG_PASSWORDERROR', "Erreur lors de la saisie du mot de passe.\nVous devez saisir deux fois le mot de passe");
define ('_SYSTEM_MSG_OLDPASSWORDERROR', "L'ancien mot de passe est invalide");
define ('_SYSTEM_MSG_LOGINERROR', "Erreur lors de la création de l'utilisateur.\nCe login existe déjà.");
define ('_SYSTEM_MSG_SAVEUSERERROR', "Erreur lors de l'enregistrement de l'utilisateur.\nVérifiez les champs saisis (longueur de l'identifiant notamment).");
define ('_SYSTEM_MSG_LOGINPASSWORDERROR', 'Erreur lors de la saisie du mot de passe.<BR>Votre mot de passe a été rejeté par le système');

define ('_SYSTEM_MSG_CONFIRMMODULEDETACH', 'Êtes-vous certain de vouloir détacher ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMODULEDELETE', 'Êtes-vous certain de vouloir supprimer ce Module ?');
define ('_SYSTEM_MSG_CONFIRMHOMEPAGERESET', 'Êtes-vous certain de vouloir revenir à la page d\\\'accueil par défaut ?');

define ('_SYSTEM_MSG_CONFIRMMODULEUNINSTAL', 'Êtes-vous certain de vouloir désinstaller ce Module ?');
define ('_SYSTEM_MSG_CONFIRMMBUPDATE', 'Êtes-vous certain de vouloir mettre à jour la métabase de ce module ?\n(Rechargement du fichier XML)');
define ('_SYSTEM_MSG_CONFIRMDESCUPDATE', 'Êtes-vous certain de vouloir mettre à jour la description (actions, paramètres, etc.) de ce module ?\n(Rechargement du fichier XML)');

define ('_SYSTEM_MSG_INFODELETE_USERS', 'Vous ne pouvez pas supprimer ce groupe car il contient des utilisateurs');
define ('_SYSTEM_MSG_INFODELETE_GROUPS', 'Vous ne pouvez pas supprimer ce groupe car il contient des sous-groupes');

define ('_SYSTEM_MSG_CANTCOPYGROUP', 'Vous n\\\'avez pas les droits suffisants pour cloner cet espace');

define ('_SYSTEM_MSG_CREATEUSER_CONFIRMATION1', "Il existe au moins un autre utilisateur dont la définition est proche de l'utilisateur que vous souhaitez créer.");
define ('_SYSTEM_MSG_CREATEUSER_CONFIRMATION2', "Vous pouvez, au choix, confirmer la création de votre utilisateur ou choisir de rattacher un utilisateur existant à votre groupe d'utilisateur.");
define ('_SYSTEM_MSG_CREATEUSER_CONFIRMATION3', "Confirmer la création d'un nouvel utilisateur");

define ('_SYSTEM_EXPLAIN_MODULENAME', 'Nom que portera le module dans l\'interface');
define ('_SYSTEM_EXPLAIN_ACTIVE', 'Détermine si le module est activé ou non (activé = utilisable)');
define ('_SYSTEM_EXPLAIN_VISIBLE', 'Détermine si le module est visible ou non (accessible via le menu)');
define ('_SYSTEM_EXPLAIN_PUBLIC', 'Détermine si le module est public ou non (visible par un utilisateur non connecté)');
define ('_SYSTEM_EXPLAIN_AUTOCONNECT', 'Détermine si ce module est affiché par défaut à la connexion de l\'utilisateur');
define ('_SYSTEM_EXPLAIN_SHARED', 'Détermine si ce module est partagé pour les sous-espaces');
define ('_SYSTEM_EXPLAIN_HERITED', 'Détermine si ce module est automatiquement hérité aux sous-espaces');
define ('_SYSTEM_EXPLAIN_ADMINRESTRICTED', 'Si l\'administration du module est restreinte, les administrateurs des sous-espaces qui utilisent ce module auront des droits limités');
define ('_SYSTEM_EXPLAIN_VIEWMODE', 'Choix de la vue qu\'ont les utilisateurs sur les données du module, <b>Privée</b> : les données ne sont vues que par l\'espace, <b>Descendante</b> : les données sont vues par l\'espace et les sous-espaces (les données « descendent »), <b>Ascendante</b> : les données sont vues par l\'espace et les espaces parents (les données « montent »), <b>Globale</b> : les données sont entièrement partagées');

define ('_SYSTEM_LABEL_PHPINFO', 'Config - PhpInfo');
define ('_SYSTEM_LABEL_DIAGNOSTIC', 'Config - Diagnostic');
define ('_SYSTEM_LABEL_CONNECTEDUSERS', 'Logs - Utilisateurs Connectés');
define ('_SYSTEM_LABEL_SQLDUMP', 'SqlDump');
define ('_SYSTEM_LABEL_ZIP', 'Zip');
define ('_SYSTEM_LABEL_BACKUP', 'Sauvegarde');
define ('_SYSTEM_LABEL_ACTIONHISTORY', 'Logs - Historique des Actions');
define ('_SYSTEM_LABEL_SERVERLOAD', 'Logs - Charge du Serveur');
define ('_SYSTEM_LABEL_STATS', 'Stats - Système');

define ('_SYSTEM_EXPLAIN_PHPINFO', 'L\'outil « '._SYSTEM_LABEL_PHPINFO.' » affiche la configuration PHP du serveur');
define ('_SYSTEM_EXPLAIN_DIAGNOSTIC', 'L\'outil « '._SYSTEM_LABEL_DIAGNOSTIC.' » vérifie quelques sources d\'erreurs courantes pouvant entraîner des dysfonctionnements de PLOOPI');
define ('_SYSTEM_EXPLAIN_CONNECTEDUSERS', 'L\'outil « '._SYSTEM_LABEL_CONNECTEDUSERS.' » affiche la liste des utilisateurs connectés au site en temps réel');
define ('_SYSTEM_EXPLAIN_SQLDUMP', 'L\'outil « '._SYSTEM_LABEL_SQLDUMP.' » vous permet de télécharger les données dans un fichier SQL zippé. Attention cette fonctionnalité ne remplace pas un dump classique. Il est également <b>fortement</b> recommandé de n\'utiliser cette fonctionnalité que sur des petites bases de données.');
define ('_SYSTEM_EXPLAIN_ZIP', 'L\'outil « Zip » vous permet de télécharger les sources de PLOOPI dans un fichier ZIP');
define ('_SYSTEM_EXPLAIN_BACKUP', 'L\'outil « Sauvegarde » vous permet de créer une sauvegarde complète du système (données + sources) afin de les restaurer à une date ultérieure');
define ('_SYSTEM_EXPLAIN_ACTIONHISTORY', 'L\'outil « '._SYSTEM_LABEL_ACTIONHISTORY.' » vous permet de consulter les actions effectuées par les utilisateurs');
define ('_SYSTEM_EXPLAIN_SERVERLOAD', 'L\'outil « '._SYSTEM_LABEL_SERVERLOAD.' » affiche la charge du serveur sur plusieurs intervalles de temps');

define ('_SYSTEM_LABEL_HOMEPAGECONTENT',    'Contenu de la Page d\'Accueil');
define ('_SYSTEM_LABEL_PREVIEW',        'Prévisualiser');
define ('_SYSTEM_LABEL_ADDLINE',        'Ajouter une ligne');
define ('_SYSTEM_LABEL_ADDCOLUMN',      'Ajouter une colonne');
define ('_SYSTEM_LABEL_NBCOLUMNS',      'Nombre de colonnes');
define ('_SYSTEM_LABEL_TITLE', 'Titre');
define ('_SYSTEM_LABEL_MODULE', 'Module');
define ('_SYSTEM_LABEL_UNDEFINEDCONTENT', 'Contenu non défini');
define ('_SYSTEM_LABEL_UNDEFINED', 'Non défini');
define ('_SYSTEM_LABEL_SIZE', 'Taille');
define ('_SYSTEM_LABEL_VISIBILITY', 'Visibilité');

define ('_SYSTEM_LABEL_MODULEPARAMETERS', 'Paramètres des Modules');

define ('_SYSTEM_LABEL_BACKTODEFAULTPAGE', 'Revenir à la page par défaut');
define ('_SYSTEM_LABEL_MODIFYMYHOMEPAGE', 'Modifier ma page d\'accueil');

define ('_SYSTEM_LABEL_MODULEINSTANCIATION', 'Instanciation d\'un Module « <LABEL> »');
define ('_SYSTEM_LABEL_MODULEDELETE', 'Suppression du Module « <LABEL> »');
define ('_SYSTEM_LABEL_MODULEUNINSTALL', 'Déinstallation du Module « <LABEL> »');
define ('_SYSTEM_LABEL_USERDETACH', 'Suppression de l\'utilisateur « <LABELUSER> » du groupe « <LABELGROUP> »');
define ('_SYSTEM_LABEL_USERDELETE', 'Suppression de l\'utilisateur « <LABEL> »');
define ('_SYSTEM_LABEL_USERCREATE', 'Création de l\'utilisateur « <LABEL> »');

define ('_SYSTEM_LABEL_IMPORTSRC', 'Source d\'import');

define ('_SYSTEM_LABEL_TICKETS', 'Messages');
define ('_SYSTEM_LABEL_SEARCH', 'Recherche');

define ('_SYSTEM_LABEL_MYTICKETS', 'Mes Messages');
define ('_SYSTEM_LABEL_TICKETS_SENDBOX', 'Messages envoyés');
define ('_SYSTEM_LABEL_TICKETS_INCOMINGBOX', 'Messages reçus');
define ('_SYSTEM_LABEL_TICKETS_WAITINGVALIDATION', 'Messages en attente de validation');
define ('_SYSTEM_LABEL_TICKETS_TOVALIDATE', 'Messages à valider');
define ('_SYSTEM_LABEL_TICKETS_ALL', 'Tous les messages');
define ('_SYSTEM_LABEL_NOTICKETS', 'Aucun message');
?>
