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
 * Partie commune des scripts de chargement de l'environnement Ploopi (start.php, start-light.php).
 * Démarrage du timer principal.
 * Chargement du fichier de config.
 * Chargement du handler de gestion du buffer.
 * Chargement du handler de gestion des erreurs.
 * Chargement du handler de gestion des sessions.
 * Filtrage des variables $POST, $GET, $COOKIE, $SERVER.
 * Connexion à la base de données.
 * Mise à jour de la session
 * 
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Démarrage du timer principal.
 */

include_once './include/classes/timer.php' ;
$ploopi_timer = new timer();
$ploopi_timer->start();

/**
 * Création du buffer principal.
 * On utilise une astuce pour contourner le fait que la fonction ploopi_ob_callback n'existe pas encore.
 */
ob_start(create_function('$buffer', 'return ploopi_ob_callback($buffer);'));

/**
 * Chargement du fichier de configuration
 */
if (!file_exists('./config/config.php'))
{
    header("Location: ./config/install.php");
    ploopi_die();
}
include_once './config/config.php'; // load config (mysql, path, etc.)

/**
 * Initialisation du gestionnaire d'erreur
 */
include_once './include/functions/errors.php';
set_error_handler('ploopi_errorhandler');

/**
 * Chargement des constantes, globales
 */
include_once './include/start/constants.php';

/**
 * Chargements des classes et fonctions principales 
 * Attention de bien garder ces inclusions. Sinon problèmes avec ploopi_die et la gestion du buffer.
 */
include_once './include/classes/data_object.php';
include_once './include/classes/log.php' ;
include_once './include/functions/date.php';
include_once './include/functions/session.php';
include_once './include/functions/system.php';

/**
 * Gestionnaire de session par $db
 */
include_once './include/classes/session.php' ;

/**
 * Filtrage des variables entrantes
 */
include_once './include/start/import_gpr.php';

/**
 * Initialisation du header par défaut
 */
include_once './include/start/header.php';

/**
 * Connexion à la base de données
 */
if (file_exists('./include/classes/db_'._PLOOPI_SQL_LAYER.'.php')) include_once './include/classes/db_'._PLOOPI_SQL_LAYER.'.php';

global $db;

$db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
if(!$db->isconnected()) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);

/**
 * Initialisation du gestionnaire de session
 */
global $session;
$session = new ploopi_session();

if (defined('_PLOOPI_USE_DBSESSION') && _PLOOPI_USE_DBSESSION)
{

    ini_set('session.save_handler', 'user');
    ini_set('session.gc_probability', 10);
    ini_set('session.gc_maxlifetime', _PLOOPI_SESSIONTIME);
    
    session_set_save_handler(   array($session, 'open'),
                                array($session, 'close'),
                                array($session, 'read'),
                                array($session, 'write'),
                                array($session, 'destroy'),
                                array($session, 'gc')
                            );
}

/**
 * Démarrage de la session
 */
session_start();

/**
 * Séquence de logout
 */
if (isset($_REQUEST['ploopi_logout']))
{
    $session->regenerate_id();
    $_SESSION = array();
    session_destroy();
    header('Location: '._PLOOPI_BASEPATH.'/'.basename($_SERVER['PHP_SELF']));
    ploopi_die();
}



$ploopi_initsession = false;

if (empty($_SESSION) || (!empty($_SESSION['ploopi']['host']) && $_SESSION['ploopi']['host'] != $_SERVER['HTTP_HOST']))  { ploopi_session_reset(); $ploopi_initsession = true; }

/**
 * Mise à jour des données de la session
 */
ploopi_session_update();
?>