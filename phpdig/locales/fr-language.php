<?php

// Antoine Bajolet - fr - bajolet@toiletoine.net

//'keyword' => 'translation'

$phpdig_mess = array (
'mode'          =>'mode',
'query'         =>'query',
'list_meanings' =>'� Total - lists the total number of searches for each query
� Query - lists the various keywords for each search query
� Mode - lists the "and, exact, or" search mode per query
� Links - lists the average number of links found per query
� Time - lists the most recent GMT timestamp of each query',
'with_no_results' =>'with no results',
'with_results' =>'with results',
'searches'     =>'searches',
'page'         =>'Page',
'of'           =>'of',
'to'           =>'to',
'listing'      =>'Listing',
'viewList'     =>'View List of Queries',
'one_per_line' =>'Enter one link per line',

'StopSpider'   =>'Stop spider',
'id'           =>'ID',
'url'          =>'URL',
'days'         =>'Days',
'links'        =>'Links',
'depth'        =>'Depth',
'viewRSS'      =>'View RSS for this Page',
'powered_by'   =>'Powered by PhpDig',
'searchall'    =>'Search All',
'wait'         =>'Wait... ',
'done'         =>'Done!',
'limit'        =>'Limit',
'manage'       =>'Here you can manage:',
'dayscron'     =>'- the number of <b>days</b> crontab waits to reindex (0 = ignore)',
'links_mean'   =>'- the max number of <b>links</b> per depth per site (0 = unlimited)',
'depth_mean'   =>'- the max search <b>depth</b> per site (0 = none, depth trumps links)',
'max_found'    =>'Maximum links found is ((links * depth) + 1) when links is greater than zero.',
'default_vals' =>'Default values',
'use_vals_from' =>'Use values from',
'table_present' =>'table if present and use<br/>default values if values absent from table?',
'admin_msg_1'   =>'- To empty tempspider table click delete button <i>without</i> selecting a site',
'admin_msg_2'   =>'- Search depth of zero tries to crawl just that page regardless of links per',
'admin_msg_3'   =>'- Set links per depth to the max number of links to check at each depth',
'admin_msg_4'   =>'- Links per depth of zero means to check for all links at each seach depth',
'admin_msg_5'   =>'- Clean dashes removes \'-\' index pages from blue arrow listings of pages',
'admin_panel'   =>'Admin Panel',

'choose_temp'  =>'Choose a template',
'select_site'  =>'Select a site to search',
'restart'      =>'Restart',
'narrow_path'  =>'Narrow Path to Search',
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'oui',
'no'           =>'non',
'delete'       =>'supprimer',
'reindex'      =>'r�indexer',
'back'         =>'Retour',
'files'        =>'fichiers',
'admin'        =>'Administration',
'warning'      =>'Attention !',
'index_uri'    =>'Quelle URI voulez-vous indexer ?',
'spider_depth' =>'Profondeur de recherche',
'spider_warn'  =>'Assurez-vous que personne ne soit en train d\'indexer
des pages du m�me site avant de lancer votre propre indexation.
Un m�canisme de lock sera inclu dans une version ult�rieure',
'site_update'  =>'Mise � jour d\'un site (ou d\'une de ses branches)',
'clean'        =>'Nettoyer',
't_index'      =>'l\'index',
't_dic'        =>'le dictionnaire',
't_stopw'      =>'les mots courants',
't_dash'       =>'dashes',

'update'       =>'Mise � jour',
'exclude'      =>'Effacer et exclure la branche',
'excludes'     =>'Chemins exclus',
'tree_found'   =>'Arborescence trouv�e',
'update_mess'  =>'R�indexer ou supprimer une arborescence ',
'update_warn'  =>'L\'exclusion efface les indexations',
'update_help'  =>'Cliquez sur la croix pour exclure une branche
Cliquez sur le plus pour mettre � jour la branche
Cliquez sur le sens interdit pour effacer et exclure une branche des indexations futures',
'branch_start' =>'S�lectionnez le r�pertoire � afficher sur le volet de gauche',
'branch_help1' =>'Vous pouvez s�lectionner ici individuellement
les index des pages � mettre � jour',
'branch_help2' =>'Cliquez sur la croix supprimer la page
Cliquez sur le plus pour une r�indexation',
'redepth'      =>'niveaux',
'branch_warn'  =>'L\'effacement est d�finitif',
'to_admin'     =>'� l\'interface d\'administration',
'to_update'    =>'� l\'interface de mise � jour',

'search'       =>'Rechercher',
'results'      =>'r�sultats',
'display'      =>'afficher',
'w_begin'      =>'D�but de mot',
'w_whole'      =>'Mot entier',
'w_part'       =>'Partie de mot',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limiter �',
'this_path'    =>'ce chemin',
'total'        =>'au total',
'seconds'      =>'secondes',
'w_common_sing'     =>'sont des mots courants et ont �t� ignor�s.',
'w_short_sing'      =>'sont des mots trop courts et ont �t� ignor�s.',
'w_common_plur'     =>'sont des mots courants et ont �t� ignor�s.',
'w_short_plur'      =>'sont des mots trop courts et ont �t� ignor�s.',
's_results'    =>'R�sultats de la recherche',
'previous'     =>'Pr�c�dents',
'next'         =>'Suivants',
'on'           =>'pour',

'id_start'     =>'Indexation du site',
'id_end'       =>'Indexation termin�e !',
'id_recent'    =>'A �t� index� r�cemment',
'num_words'    =>'Nombre de mots',
'time'         =>'temps',
'error'        =>'Erreur',
'no_spider'    =>'Spider non lanc�',
'no_site'      =>'Ce site n\'existe pas dans la base de donn�es',
'no_temp'      =>'Pas de liens dans la table temporaire',
'no_toindex'   =>'Rien � indexer',
'double'       =>'Doublon avec un document existant',

'spidering'    =>'Exploration des liens en cours...',
'links_more'   =>'liens en plus',
'level'        =>'niveau',
'links_found'  =>'liens trouv�s',
'define_ex'    =>'D�finir des exclusions',
'index_all'    =>'Tout indexer',

'end'          =>'fin',
'no_query'     =>'Veuillez renseigner le formulaire de recherche',
'pwait'        =>'Veuillez patienter',
'statistics'   =>'Statistiques',

// INSTALL
'slogan'   =>'Le plus petit moteur de recherche de l\'univers : version',
'installation'   =>'Installation',
'instructions' =>'Veuillez entrer vos param�tres MYSQL. '
                 .'Sp�cifiez un utilisateur autoris� � cr�er une base de donn�es '
                 .'si vous choisissez de cr�er ou de mettre � jour la base.',
'hostname'   =>'H�te :',
'port'   =>'Port (none = default) :',
'sock'   =>'Sock (none = default) :',
'user'   =>'Utilisateur :',
'password'   =>'Mot de passe :',
'phpdigdatabase'   =>'Base de PhpDig :',
'tablesprefix'   =>'Pr�fixe des tables :',
'instructions2'   =>'* facultatif. Utilisez des minuscules, 16 caract�res max.',
'installdatabase'   =>'Installer la base de phpdig',
'error1'   =>'Can\'t find connexion template. ',
'error2'   =>'Can\'t write connexion template. ',
'error3'   =>'Impossible de trouver le fichier init_db.sql. ',
'error4'   =>'Impossible de cr�er les tables. ',
'error5'   =>'Impossible de trouver tous les fichiers de configuration de la base. ',
'error6'   =>'Impossible de cr�er la base de donn�es.<br />Veuillez v�rifier que vous disposez des droits suffisants. ',
'error7'   =>'Impossible de se connecter � la base de donn�es.<br />Veuillez v�rifier les informations de connexion � la base. ',
'createdb' =>'Cr�er la base',
'createtables' =>'Cr�er les tables uniquement',
'updatedb' =>'Mettre � jour la base',
'existingdb' =>'Param�tres de connexion uniquement',
// CLEANUP_ENGINE
'cleaningindex'   =>'Nettoyage de l\'index',
'enginenotok'   =>' r�f�rence(s) dans l\'index ne correspondai(en)t plus � un mot cl� existant.',
'engineok'   =>'Le moteur est coh�rent.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Nettoyage du dictionnaire',
'keywordsok'   =>'Tous les mots cl�s sont pr�sents dans au moins une page.',
'keywordsnotok'   =>' mot(s) cl�(s) n\'�tai(en)t plus pr�sent(s) dans aucune page.',
// CLEANUP_COMMON
'cleanupcommon' =>'Nettoyage des mots courants',
'cleanuptotal' =>'Un total de ',
'cleaned' =>' mot(s) courant(s) a (ont) �t� effac�.',
'deletedfor' =>' suppression(s) pour ',
// INDEX ADMIN
'digthis' =>'Indexer !',
'databasestatus' =>'Stats de la base de donn�es PhpDig',
'entries' =>' enregistrements ',
'updateform' =>'Mettre � jour',
'deletesite' =>'Effacer le site',
// SPIDER
'spiderresults' =>'Les r�sultats de l\'indexation',
// STATISTICS
'mostkeywords' =>'Mots cl�s les plus nombreux',
'richestpages' =>'Pages avec le plus de mots cl�s',
'mostterms'    =>'Termes les plus recherch�s',
'largestresults'=>'Les plus grands r�sultats',
'mostempty'     =>'Les recherches infructueuses les plus courantes',
'lastqueries'   =>'Les derni�res recherches',
'responsebyhour'=>'Temps de r�ponse horaire',
// UPDATE
'userpasschanged' =>'Nom d\'utilisateur / mot de passe modifi�(s) !',
'uri' =>'URI : ',
'change' =>'Modifier',
'root' =>'Racine',
'pages' =>' pages',
'locked' => 'Verrouill�',
'unlock' => 'D�verrouiller le site',
'onelock' => 'Un site est verrouill� car en cours d\'indexation.
vous ne pouvez effectuer cette action',
// PHPDIG_FORM
'go' =>'Chercher',
// SEARCH_FUNCTION
'noresults' =>'Aucun r�sultat'
);
?>