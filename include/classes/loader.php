<?php
/*
    Copyright (c) 2007-2013 Ovensia
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


abstract class ploopi_loader
{
    private static $initsession = false;
    private static $script = 'index'; // index/admin/light/quick/webservice/backend...
    private static $workspaces = array();
    private static $workspace = array(); // workspace sélectionné

    /**
     * Partie commune des scripts de chargement de l'environnement Ploopi
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
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function boot()
    {
        global $db;
        global $ploopi_timer;

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
            include_once './include/functions/system.php' ;
            include_once './config/install.php' ;
            //header("Location: ./config/install.php");
            ploopi_die();
        }

        include_once './config/config.php'; // load config (mysql, path, etc.)

        /**
         * Initialisation du gestionnaire d'erreur
         */
        include_once './include/functions/errors.php';
        ploopi_set_error_handler();

        /**
         * Chargement des constantes, globales
         */
        include_once './include/constants.php';

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
         * Connexion à la base de données
         */
        if (file_exists('./include/classes/db_'._PLOOPI_SQL_LAYER.'.php')) include_once './include/classes/db_'._PLOOPI_SQL_LAYER.'.php';


        $db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
        if(!$db->isconnected()) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);

        /**
         * Traitement du rewriting inverse
         */
        self::rewrite();

        /**
         * Initialisation du gestionnaire de session
         */

        if (_PLOOPI_SESSION_HANDLER != 'php') {

            /**
             * Gestionnaire interne de session
             */
            include_once './include/classes/session.php' ;

            session_set_save_handler(
                array('ploopi_session', 'open'),
                array('ploopi_session', 'close'),
                array('ploopi_session', 'read'),
                array('ploopi_session', 'write'),
                array('ploopi_session', 'destroy'),
                array('ploopi_session', 'gc')
            );

            session_name('ploopi'.md5(_PLOOPI_BASEPATH));
        }

        /**
         * Démarrage de la session
         */
        session_start();

        /**
         * Filtrage des variables entrantes
         */
        self::importgpr();

        /**
         * Séquence de logout
         */
        if (isset($_REQUEST['ploopi_logout'])) ploopi_logout(0, 0, false);

        /**
         * Pas de session, ou host différent => init session
         */
        if (empty($_SESSION) || (!empty($_SESSION['ploopi']['host']) && $_SESSION['ploopi']['host'] != $_SERVER['HTTP_HOST']))  {
            if (!empty($_SESSION)) ploopi_syslog(LOG_INFO, 'Réinitialisation de session liée à un changement de domaine');
            ploopi_session_reset();
            self::$initsession = true;
        }

        /**
         * Mise à jour des données de la session
         */
        ploopi_session_update();

        /**
         * Initialisation du header par défaut
         */
        self::setheader();

        include_once './include/classes/cache.php' ;
        ploopi_cache::init();

    }

    /**
     * Filtre les superglobales $_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER
     * Déchiffre l'URL si elle est chiffrée.
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see ploopi_filtervar
     * @see ploopi_cipher
     */

    public static function importgpr()
    {
        include_once './include/functions/security.php';

        /**
         * Traitement du paramètre spécial 'ploopi_url' via POST/GET
         */
        foreach(array('POST', 'GET') as $strGlobalVar)
        {
            if (!empty($GLOBALS["_{$strGlobalVar}"]['ploopi_url']))
            {
                $GLOBALS["_{$strGlobalVar}"]['ploopi_url'] = ploopi_filtervar($GLOBALS["_{$strGlobalVar}"]['ploopi_url']);

                require_once './include/classes/cipher.php';
                $strPloopiUrl = ploopi_cipher::singleton()->decrypt($GLOBALS["_{$strGlobalVar}"]['ploopi_url']);

                foreach(explode('&',$strPloopiUrl) as $strParam)
                {
                    if (strstr($strParam, '=')) list($strKey, $strValue) = explode('=',$strParam);
                    else {$strKey = $strParam; $strValue = '';}

                    $strKey = urldecode($strKey);

                    // Variable structurée ?
                    // Traitement des variables de type var[dimension1][dimension2]=value
                    if (($pos = strpos($strKey, '[')) !== false)
                    {
                        // Extraction des [xxx]
                        preg_match_all('@\[([^\]]*)\]@', $strKey, $arrMatches);
                        if (sizeof($arrMatches))
                        {
                            $funcBuildVar = function($var, $key = 0) use ($arrMatches, $strValue, &$funcBuildVar)
                            {
                                // Cas général : on construit la variable en suivant la branche
                                if (isset($arrMatches[1][$key])) {
                                    if (!isset($var[$arrMatches[1][$key]])) $var[$arrMatches[1][$key]] = array();
                                    $var[$arrMatches[1][$key]] = $funcBuildVar($var[$arrMatches[1][$key]], $key+1);
                                }
                                // Cas particuler : terminaison de branche, on stocke la variable
                                else $var = urldecode($strValue);

                                return $var;
                            };

                            // Variable racine
                            $strRootKey = substr($strKey, 0, $pos);

                            if (!isset($_REQUEST[$strRootKey])) $_REQUEST[$strRootKey] = array();
                            $_REQUEST[$strRootKey] = $GLOBALS["_{$strGlobalVar}"][$strRootKey] = $funcBuildVar($_REQUEST[$strRootKey]);
                        }
                    }
                    // variable simple
                    else $_REQUEST[$strKey] = $GLOBALS["_{$strGlobalVar}"][$strKey] = urldecode($strValue);
                }

                unset($strKey);
                unset($strValue);
                unset($strParam);
                unset($strPloopiUrl);
                unset($GLOBALS["_{$strGlobalVar}"]['ploopi_url']);
            }
        }
        unset($strGlobalVar);
        unset($_REQUEST['ploopi_url']);

        $_GET = ploopi_filtervar($_GET, null, !empty($_POST['ploopi_xhr']));;
        $_POST = ploopi_filtervar($_POST, null, !empty($_POST['ploopi_xhr']));
        $_REQUEST = ploopi_filtervar($_REQUEST, null, !empty($_POST['ploopi_xhr']));
        $_COOKIE = ploopi_filtervar($_COOKIE);
        $_SERVER = ploopi_filtervar($_SERVER);
    }

    /**
     * Modifie les entêtes HTTP envoyées.
     * Modifie notamment la gestion du cache (no-cache)
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function setheader()
    {
        header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s"));

        // HTTP/1.1
        header('Cache-Control: private_no_expire, must-revalidate');
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        //header('Cache-Control: post-check=0, pre-check=0', false);
        //header('Cache-Control: max-age=0', false);

        // HTTP/1.0
        header('Pragma: no-cache');

        // On génère un Etag unique
        header('Etag: '.microtime());

        header('Accept-Ranges: bytes');
        header('Content-type: text/html; charset=iso-8859-1');
    }

    /**
     * Indique dans les entêtes si l'utilisateur est connecté
     */

    public static function setheader_connected()
    {
        header('Ploopi-Connected: '.(empty($_SESSION['ploopi']['connected']) ? 0 : 1));
    }


    /**
     * Dispatcher en fonction du point d'entrée
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function dispatch()
    {
        global $db;
        global $skin;
        global $template_body;
        global $ploopi_timer;
        global $ploopi_viewmodes;
        global $ploopi_system_levels;
        global $ploopi_days;
        global $ploopi_months;
        global $ploopi_errormsg;
        global $ploopi_msg;
        global $ploopi_civility;
        global $ploopi_additional_head;
        global $ploopi_additional_javascript;

        switch(self::$script)
        {
            case 'index':
                self::start();
                include_once ($_SESSION['ploopi']['mode'] == 'frontoffice') ? './include/frontoffice.php' : './include/backoffice.php';
            break;

            case 'admin':
                self::start();
                include_once './include/backoffice.php';
            break;

            case 'admin-light':
            case 'index-light':
                self::start();
                include_once './include/light.php';
            break;

            case 'webservice':
                if (isset($_REQUEST['ploopi_login'])) self::start();
                else self::startlight();
                include_once './include/webservice.php';
            break;

            case 'backend':
                self::startlight();
                include_once './include/backend.php';
            break;

            case 'quick':
                if (empty($_SESSION['ploopi']['mode']))
                {
                    self::getworkspaces();
                    self::getmodules();

                    if (!empty($_SESSION['ploopi']['hosts']['frontoffice'][0]))
                    {
                        $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['frontoffice']['workspaceid'] = $_SESSION['ploopi']['hosts']['frontoffice'][0];
                    }
                }

                self::setheader_connected();

                include './include/op.php';
            break;
        }

    }

    /**
     * Gère le rewriting inverse des URL
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function rewrite()
    {
        global $arrParsedURI;

        if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '200')
        {
            $booRewriteRuleFound = false;

            // Attention ! $_SERVER['REQUEST_URI'] peut contenir une url complète avec le nom de domaine
            $arrParsedURI = @parse_url($_SERVER['REQUEST_URI']);
            $strRequestURI = $arrParsedURI['path'].(empty($arrParsedURI['query']) ? '' : "?{$arrParsedURI['query']}");

            if (_PLOOPI_SELFPATH == '' || strpos($strRequestURI, _PLOOPI_SELFPATH) === 0) define('_PLOOPI_REQUEST_URI', substr($strRequestURI, strlen(_PLOOPI_SELFPATH) - strlen($strRequestURI)));
            else define('_PLOOPI_REQUEST_URI', $strRequestURI);

            $arrParsedURI = @parse_url(_PLOOPI_REQUEST_URI);

            if (!empty($arrParsedURI['path']))
            {
                // robots.txt
                if ($booRewriteRuleFound = ($arrParsedURI['path'] == '/robots.txt'))
                {
                    self::$script = 'quick';
                    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_robots';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/admin.php'))
                {
                    self::$script = 'admin';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/admin-light.php'))
                {
                    self::$script = 'admin-light';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/index-light.php'))
                {
                    self::$script = 'index-light';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/webservice.php'))
                {
                    self::$script = 'webservice';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/backend.php'))
                {
                    self::$script = 'backend';
                }
                elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/index-quick.php'))
                {
                    self::$script = 'quick';
                }
            }
            else $arrParsedURI['path'] = '';

            if (!$booRewriteRuleFound)
            {
                // Gestion du rewriting inverse des modules
                clearstatcache();
                $rscFolder = @opendir(realpath('./modules/'));
                while ($strFolderName = @readdir($rscFolder))
                {
                    if (!$booRewriteRuleFound && $strFolderName != '.' && $strFolderName != '..' && file_exists($strModuleRewrite = "./modules/{$strFolderName}/include/rewrite.php")) include_once $strModuleRewrite;
                }
                closedir($rscFolder);
            }

            if (!$booRewriteRuleFound)
            {
                ploopi_h404();
                ploopi_die('Page non trouvée');
            }
        }
    }

    /**
     * Chargement de l'environnement Ploopi.
     * Charge les fonctions et classes principales.
     * Connecte l'utilisateur, initialise la session, charge les paramètres.
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function start()
    {
        global $db;
        global $ploopi_viewmodes;
        global $ploopi_system_levels;

        /**
         * Chargement fonctions génériques
         */

        include_once './include/functions/actions.php';
        include_once './include/functions/annotation.php';
        include_once './include/functions/crypt.php';
        include_once './include/functions/date.php';
        include_once './include/functions/documents.php';
        include_once './include/functions/filesystem.php';
        include_once './include/functions/filexplorer.php';
        include_once './include/functions/image.php';
        include_once './include/functions/ip.php';
        include_once './include/functions/mail.php';
        include_once './include/functions/search_index.php';
        include_once './include/functions/security.php';
        include_once './include/functions/session.php';
        include_once './include/functions/share.php';
        include_once './include/functions/system.php';
        include_once './include/functions/string.php';
        include_once './include/functions/subscription.php';
        include_once './include/functions/tickets.php';
        include_once './include/functions/validation.php';

        /**
         * Chargement des classes principales
         */

        include_once './include/classes/user.php';
        include_once './include/classes/group.php';
        include_once './include/classes/workspace.php';
        include_once './include/classes/param.php';

        /**
         * Gestion de la connexion d'un utilisateur
         */

        if ((!empty($_REQUEST['ploopi_login']) && !empty($_REQUEST['ploopi_password'])))
        {
            $db->query("
                SELECT      *
                FROM        ploopi_user
                WHERE       login = '".$db->addslashes($_REQUEST['ploopi_login'])."'
            ");

            // Un seul utilisateur trouvé
            if ($db->numrows() == 1)
            {
                $fields = $db->fetchrow();

                // Trop de tentatives de connexion : mise en prison pendant _PLOOPI_JAILING_TIME
                if ($fields['jailed_since'] > 0 && $fields['jailed_since'] + _PLOOPI_JAILING_TIME > ploopi_createtimestamp()) {
                    ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'],_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                    ploopi_syslog(LOG_INFO, "Le compte {$_REQUEST['ploopi_login']} est suspendu pendant "._PLOOPI_JAILING_TIME."s suite à un trop grand nombre de tentatives de connexion");
                    ploopi_logout(_PLOOPI_ERROR_ACCOUNTJAILED);
                }

                // Compte désactivé
                if ($fields['disabled']) {
                    ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'],_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                    ploopi_syslog(LOG_INFO, "Le compte {$_REQUEST['ploopi_login']} est désactivé");
                    ploopi_logout(_PLOOPI_ERROR_ACCOUNTEXPIRE);
                }

                // Mot de passe erronné
                if ($fields['password'] != user::generate_hash($_REQUEST['ploopi_password'], $_REQUEST['ploopi_login'])) {
                    $objUser = new user();
                    $objUser->open($fields['id']);
                    $objUser->fields['failed_attemps']++;
                    // Nombre de tentatives echouées trop élevé ? => case prison
                    if (_PLOOPI_MAX_CONNECTION_ATTEMPS && $objUser->fields['failed_attemps'] >= _PLOOPI_MAX_CONNECTION_ATTEMPS) {
                        $objUser->fields['jailed_since'] = ploopi_createtimestamp();
                    }
                    $objUser->save();

                    ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'], _PLOOPI_MODULE_SYSTEM, _PLOOPI_MODULE_SYSTEM);
                    ploopi_syslog(LOG_INFO, "Mot de passe incorrect pour {$_REQUEST['ploopi_login']}");
                    ploopi_logout(_PLOOPI_ERROR_LOGINERROR);
                }
                elseif (!empty($fields['failed_attemps']) || !empty($fields['jailed_since'])) {
                    $objUser = new user();
                    $objUser->open($fields['id']);
                    $objUser->fields['failed_attemps'] = 0;
                    $objUser->fields['jailed_since'] = 0;
                    $objUser->save();
                }

                // Vérification de la validité du compte
                if (!empty($fields['date_expire']))
                {
                    // Compte expiré (définitif sauf intervention administrateur)
                    if ($fields['date_expire'] <= ploopi_createtimestamp())
                    {
                        ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'],_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                        ploopi_syslog(LOG_INFO, "Validité du compte expirée pour {$_REQUEST['ploopi_login']}");
                        ploopi_logout(_PLOOPI_ERROR_ACCOUNTEXPIRE);
                    }
                }

                // On force l'utilisateur à changer de mot de passe ?
                // Le mot de passe est périmé ?
                if (!empty($fields['password_force_update']) || (!empty($fields['password_validity']) && ploopi_timestamp2unixtimestamp($fields['password_last_update'])+$fields['password_validity']*86400 < time())) {

                    $intErrorCode = 0;

                    // Nouveau mot de passe ?
                    if (isset($_REQUEST['ploopi_password_new']) && isset($_REQUEST['ploopi_password_new_confirm']) && $_REQUEST['ploopi_password_new'] != $_REQUEST['ploopi_password']) {

                        // Mot de passe correctement saisi ?
                        if ($_REQUEST['ploopi_password_new'] == $_REQUEST['ploopi_password_new_confirm']) {

                            // Mot de passe valide ?
                            if (!_PLOOPI_USE_COMPLEXE_PASSWORD || ploopi_checkpasswordvalidity($_REQUEST['ploopi_password_new'])) {

                                // On peut mettre à jour la base de données
                                $objUser = new user();
                                $objUser->open($fields['id']);
                                $objUser->setpassword($_REQUEST['ploopi_password_new']);
                                $objUser->fields['password_force_update'] = 0;
                                $objUser->save();
                            }
                            else $intErrorCode = _PLOOPI_ERROR_PASSWORDINVALID;
                        }
                        else $intErrorCode = _PLOOPI_ERROR_PASSWORDERROR;
                    }
                    else $intErrorCode = _PLOOPI_ERROR_PASSWORDRESET;

                    if ($intErrorCode) {
                        ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'], _PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                        ploopi_syslog(LOG_INFO, 'Erreur lors du changement de mot de passe');
                        ploopi_logout($intErrorCode, 1, true, array('login' => $fields['login'], 'password' => $_REQUEST['ploopi_password']));
                    }
                }

                $_SESSION['ploopi']['login'] = $fields['login'];
                $_SESSION['ploopi']['password'] = $_REQUEST['ploopi_password'];
                $_SESSION['ploopi']['userid'] = $fields['id'];
                $_SESSION['ploopi']['user'] = $fields;
                ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_OK, $_REQUEST['ploopi_login'],_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);

                $objUser = new user();
                $objUser->open($fields['id']);
                $objUser->fields['last_connection'] = ploopi_createtimestamp();
                $objUser->save();

                // Reset de la session + nouvel ID
                ploopi_session_reset();
                // Indique qu'il faut recharger la session
                self::$initsession = true;

                $_SESSION['ploopi']['login'] = $fields['login'];
                $_SESSION['ploopi']['password'] = $_REQUEST['ploopi_password'];
                $_SESSION['ploopi']['userid'] = $fields['id'];
                $_SESSION['ploopi']['user'] = $fields;

                // Vérification de la validité du profil
                // Il faut récupérer la liste des champs à contrôler dans les paramètres du module system
                $objParamDefault = new param_default();
                if ($objParamDefault->open(1, 'system_user_required_fields') && !empty($objParamDefault->fields['value']))
                {
                    foreach(explode(',', $objParamDefault->fields['value']) as $strField)
                    {
                        $strField = trim($strField);
                        if (isset($fields[$strField]) && $fields[$strField] == '') { $_SESSION['ploopi']['updateprofile'] = true; break; }
                    }
                }

                if (empty($_REQUEST['noredir'])) {
                    // Gestion de la redirection après login (en fonction de l'url de provenance et du script d'authentification)
                    $arrReferer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : array(); // Provenance
                    $arrRequest = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : array();  // Demande d'authentification

                    $strRefererHost = isset($arrReferer['host']) ? $arrReferer['host'].(isset($arrReferer['port']) && $arrReferer['port'] != 80 ? ':'.$arrReferer['port'] : '') : '';
                    $strRequestHost = $_SERVER['HTTP_HOST'];

                    $strRefererScript = isset($arrReferer['path']) ? ltrim(str_replace(dirname($_SERVER['PHP_SELF']), '', $arrReferer['path']), '/') : '';
                    $strRequestScript = isset($arrRequest['path']) ? ltrim(str_replace(dirname($_SERVER['PHP_SELF']), '', $arrRequest['path']), '/') : '';

                    $strLoginRedirect = '';

                    // Même domaine, même script, redirection acceptée
                    if ($strRefererHost == $strRequestHost && ($strRefererScript == $strRequestScript || $strRequestScript != 'admin.php')) {
                        $strLoginRedirect = $_SERVER['HTTP_REFERER'];
                    }
                    else {
                        $arrParams = $_GET;
                        if (isset($arrParams['ploopi_env'])) {
                            $arrEnv = explode('/', $_REQUEST['ploopi_env']);

                            if (isset($arrEnv[0]) && is_numeric($arrEnv[0])) $arrParams['ploopi_mainmenu'] = $arrEnv[0];

                            if (isset($arrEnv[1]) && is_numeric($arrEnv[1])) $arrParams['ploopi_workspaceid'] = $arrEnv[1];

                            if (isset($arrEnv[2]) && is_numeric($arrEnv[2])) $arrParams['ploopi_moduleid'] = $arrEnv[2];

                            if (isset($arrEnv[3])) $arrParams['ploopi_action'] = $arrEnv[3];
                        }

                        unset($arrParams['ploopi_login']);
                        unset($arrParams['ploopi_password']);
                        unset($arrParams['ploopi_env']);

                        $strLoginRedirect = _PLOOPI_BASEPATH.'/'.ploopi_urlencode($strRequestScript.($arrParams?'?'.http_build_query($arrParams):''));
                    }
                }

            }
            else
            {
                ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $_REQUEST['ploopi_login'], _PLOOPI_MODULE_SYSTEM, _PLOOPI_MODULE_SYSTEM);
                ploopi_syslog(LOG_INFO, "Utilisateur inconnu ({$_REQUEST['ploopi_login']})");
                ploopi_logout(_PLOOPI_ERROR_LOGINERROR);
            }
        }

        /**
         * Permet de forcer un rechargement de session
         */
        self::$initsession |= isset($_REQUEST['reloadsession']);

        /**
         * Permet de gérer le cas ou la session est partiellement chargée (on passe d'abord par index-quick.php...)
         */
        self::$initsession |= empty($_SESSION['ploopi']['mode']);

        /**
         * Chargement Espaces
         */

        if (self::$initsession) self::getworkspaces();

        /**
         * Switch entre backoffice et frontoffice en fonction du nom du script appelant (admin.php/index.php) et de la config du portail
         */

        switch(self::$script)
        {
            case 'index':
            case 'index-light':
                if ((!empty($_GET['webedit_mode'])) && isset($_SESSION['ploopi']['backoffice']['connected']) && $_SESSION['ploopi']['backoffice']['connected'] && isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['backoffice']['moduleid']]) && $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['backoffice']['moduleid']]['moduletype'] == 'webedit')
                {
                    // cas spécial du mode de rendu public du module Webedit (on utilise le rendu frontoffice sans activer tout le processus)
                    $newmode = 'frontoffice';
                    $_SESSION['ploopi']['frontoffice']['workspaceid'] = $_SESSION['ploopi']['backoffice']['workspaceid'];
                    $_SESSION['ploopi']['frontoffice']['moduleid'] = $_SESSION['ploopi']['backoffice']['moduleid'];
                }
                else
                {
                    $newmode = (_PLOOPI_FRONTOFFICE && is_dir('./modules/webedit/') && isset($_SESSION['ploopi']['hosts']['frontoffice'][0])) ? 'frontoffice' : 'backoffice';

                    if ($_SESSION['ploopi']['mode'] != $newmode && $newmode == 'frontoffice')
                    {

                        if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]]['webeditmoduleid']))
                        {
                            // on cherche le module webedit
                            $db->query( "
                                        select      ploopi_module_workspace.id_module

                                        from        ploopi_module,
                                                    ploopi_module_type,
                                                    ploopi_module_workspace

                                        where       ploopi_module.id_module_type = ploopi_module_type.id
                                        and         (ploopi_module_type.label = 'webedit')
                                        and         ploopi_module.id = ploopi_module_workspace.id_module
                                        and         ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['hosts']['frontoffice'][0]}
                                        ");

                            if ($fields = $db->fetchrow()) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]]['webeditmoduleid'] = $fields['id_module'];
                            else $newmode = 'backoffice';
                        }

                        if ($newmode == 'frontoffice')
                        {
                            $_SESSION['ploopi']['frontoffice']['workspaceid'] = $_SESSION['ploopi']['hosts']['frontoffice'][0];
                            $_SESSION['ploopi']['frontoffice']['moduleid'] = $_SESSION['ploopi']['webeditmoduleid'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['frontoffice']['workspaceid']]['webeditmoduleid'];
                        }
                    }
                }

                $_SESSION['ploopi']['mode'] = $newmode;

            break;

            case 'admin':
            case 'admin-light':
            default:
                $_SESSION['ploopi']['mode'] = 'backoffice';
            break;
        }

        if (self::$initsession)
        {
            /**
             * Chargement du profil utilisateur
             */

            if ($_SESSION['ploopi']['userid'] != 0)
            {
                self::getmodules();

                //include './include/start/load_param.php';

                $user = new user();
                if (!$user->open($_SESSION['ploopi']['userid'])) ploopi_logout();

                $booRedirectProfile = false;

                $_SESSION['ploopi']['user'] = $user->fields;

                if ($_SESSION['ploopi']['user']['servertimezone']) $_SESSION['ploopi']['user']['timezone'] = $_SESSION['ploopi']['timezone'];

                $_SESSION['ploopi']['actions'] = array();
                $_SESSION['ploopi']['actions'] = $user->getactions($_SESSION['ploopi']['actions']);

                // get all workspaces of current user
                $db->query("
                    SELECT      w.id,
                                w.iprules,
                                w.mustdefinerule,
                                MAX(GREATEST(IFNULL(wu.adminlevel,0), IFNULL(wg.adminlevel,0))) as adlvl,
                                GROUP_CONCAT(wg.id_group) as groups
                    FROM        ploopi_workspace w
                    LEFT JOIN   ploopi_workspace_user wu ON wu.id_workspace = w.id AND wu.id_user = {$_SESSION['ploopi']['userid']}
                    LEFT JOIN   ploopi_workspace_group wg ON wg.id_workspace = w.id AND wg.id_group IN (SELECT g.id FROM ploopi_group g INNER JOIN ploopi_group_user gu ON gu.id_group = g.id WHERE gu.id_user = {$_SESSION['ploopi']['userid']})
                    GROUP BY    w.id
                    HAVING      adlvl > 0
                    ORDER BY    w.id
                ");

                $user_workspaces = array();
                while ($row = $db->fetchrow()) $user_workspaces[$row['id']] = $row;


                $_SESSION['ploopi']['frontoffice']['connected'] = 0;
                $_SESSION['ploopi']['backoffice']['connected'] = 0;

                foreach ($user_workspaces as $wid => $fields)
                {
                    // workspace frontoffice ?
                    if (in_array($wid, $_SESSION['ploopi']['hosts']['frontoffice']) || $fields['adlvl'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                    {
                        $_SESSION['ploopi']['frontoffice']['connected'] = 1;
                    }

                    // workspace backoffice ?
                    if (in_array($wid, $_SESSION['ploopi']['hosts']['backoffice']) || $fields['adlvl'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                    {
                        $adminlevel = $fields['adlvl'];

                        $workspace = new workspace();
                        $workspace->fields = self::$workspaces[$wid];

                        $iprules = ploopi_getiprules($fields['iprules']);

                        if (ploopi_isipvalid($iprules))
                        {
                            if (!empty($fields['groups']))
                            {
                                foreach(explode(',', $fields['groups']) as $idg)
                                {
                                    $grp = new group();
                                    if ($grp->open($idg)) $_SESSION['ploopi']['actions'] = $grp->getactions($_SESSION['ploopi']['actions']);
                                }
                            }

                            $workspace_ok = true;

                            if ($fields['mustdefinerule']) $workspace_ok = (isset($_SESSION['ploopi']['actions'][$wid])  || ($gu_exists && $group_user->fields['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN));

                            if ($workspace_ok)
                            {
                                $_SESSION['ploopi']['workspaces'][$wid] = self::$workspaces[$wid];
                                $_SESSION['ploopi']['workspaces'][$wid]['adminlevel']  = $adminlevel;
                                $_SESSION['ploopi']['workspaces'][$wid]['backoffice']  = 1;

                                // Faire une requête globale pour les modules ici ?
                                $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);

                                $_SESSION['ploopi']['backoffice']['connected'] = 1;

                            }
                        }
                    }
                }


                if (!$_SESSION['ploopi']['frontoffice']['connected'] && !$_SESSION['ploopi']['backoffice']['connected'] || (!$_SESSION['ploopi']['backoffice']['connected'] && $_SESSION['ploopi']['mode'] == 'backoffice'))
                {
                    ploopi_syslog(LOG_INFO, 'Aucun espace de travail pour cet utilisateur');
                    ploopi_logout(_PLOOPI_ERROR_NOWORKSPACEDEFINED);
                }

                // sorting workspaces by priority/label
                uksort ($_SESSION['ploopi']['workspaces'], create_function('$a,$b', 'return (sprintf("%03d_%s", intval($_SESSION[\'ploopi\'][\'workspaces\'][$b][\'priority\']), $_SESSION[\'ploopi\'][\'workspaces\'][$b][\'label\']) < sprintf("%03d_%s", intval($_SESSION[\'ploopi\'][\'workspaces\'][$a][\'priority\']), $_SESSION[\'ploopi\'][\'workspaces\'][$a][\'label\']));'));

                // create a list with allowed workspaces only
                $_SESSION['ploopi']['workspaces_allowed'] = array();
                foreach($_SESSION['ploopi']['workspaces'] as $idwsp => $workspace) if (!empty($workspace['adminlevel'])) $_SESSION['ploopi']['workspaces_allowed'][] = $idwsp;

                if (!isset($_REQUEST['reloadsession'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;
            }
        }

        if (!$_SESSION['ploopi']['paramloaded']) self::getmodules();

        // Génération du token en mode backoffice uniquement
        if (_PLOOPI_TOKEN && $_SESSION['ploopi']['mode'] == 'backoffice')
        {
            $_SESSION['ploopi']['token'] = uniqid(rand());
            $_SESSION['ploopi']['tokens'][$_SESSION['ploopi']['token']] = time();

            // Injection du nouveau jeton
            if (!empty($strLoginRedirect)) $strLoginRedirect = ploopi_urltoken($strLoginRedirect);
        }

        if (!empty($strLoginRedirect)) ploopi_redirect($strLoginRedirect, false, false);

        unset($strLoginRedirect);

        // Indicateur global de connexion
        $_SESSION['ploopi']['connected'] = isset($_SESSION['ploopi'][$_SESSION['ploopi']['mode']]['connected']) && $_SESSION['ploopi'][$_SESSION['ploopi']['mode']]['connected'];
        self::setheader_connected();

        ///////////////////////////////////////////////////////////////////////////
        // ADMIN SWITCHES
        ///////////////////////////////////////////////////////////////////////////

        if ($_SESSION['ploopi']['mode'] == 'backoffice')
        {
            $strToken = '';

            if (isset($_REQUEST['ploopi_env']))
            {
                /**
                 * ploopi_env contient ploopi_mainmenu (int), ploopi_workspaceid (int), ploopi_moduleid (int), ploopi_action (string) et le token (str) de la page appelante
                 */
                $arrEnv = explode('/', $_REQUEST['ploopi_env']);

                if (isset($arrEnv[0]) && is_numeric($arrEnv[0])) $ploopi_mainmenu = $arrEnv[0];

                if (isset($arrEnv[1]) && is_numeric($arrEnv[1])) $ploopi_workspaceid = $arrEnv[1];

                if (isset($arrEnv[2]) && is_numeric($arrEnv[2])) $ploopi_moduleid = $arrEnv[2];

                if (isset($arrEnv[3])) $ploopi_action = $arrEnv[3];

                if (_PLOOPI_TOKEN && isset($arrEnv[4])) $strToken = $arrEnv[4];
            }


            if ($_SESSION['ploopi']['connected'])
            {
                if (_PLOOPI_TOKEN) {
                    // Vérification de la validité du jeton
                    // On autorise un jeton non valide ou non fourni à l'unique condition que la requête ne contienne aucun paramètre
                    if (!empty($_REQUEST) && ((empty($strToken) || !isset($_SESSION['ploopi']['tokens'][$strToken])))) {
                        if (empty($strToken)) {
                            ploopi_syslog(LOG_INFO, 'Jeton absent');
                            echo 'Jeton absent, redirection en cours...';
                        }
                        else {
                            ploopi_syslog(LOG_INFO, 'Jeton non valide');
                            echo 'Jeton non valide, redirection en cours...';
                        }

                        ploopi_redirect('admin.php', true, true, 2);
                        ploopi_die();
                    }

                    // Mise à jour de la validité du jeon
                    unset($_SESSION['ploopi']['tokens'][$strToken]);
                    $_SESSION['ploopi']['tokens'][$strToken] = time();
                }

                if (isset($_REQUEST['ploopi_mainmenu']) && is_numeric($_REQUEST['ploopi_mainmenu']))
                    $ploopi_mainmenu = $_REQUEST['ploopi_mainmenu'];

                if (isset($_REQUEST['ploopi_workspaceid']) && is_numeric($_REQUEST['ploopi_workspaceid']))
                    $ploopi_workspaceid = $_REQUEST['ploopi_workspaceid'];

                if (isset($_REQUEST['ploopi_moduleid']) && is_numeric($_REQUEST['ploopi_moduleid']))
                    $ploopi_moduleid = $_REQUEST['ploopi_moduleid'];

                if (isset($_REQUEST['ploopi_action']))
                    $ploopi_action = $_REQUEST['ploopi_action'];

                // Cas particulier de la connexion ou du transfert front/back
                if (empty($ploopi_mainmenu) && empty($_SESSION['ploopi']['mainmenu'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;

                ///////////////////////////////////////////////////////////////////////////
                // SWITCH MAIN MENU (Workspaces, Profile, etc.)
                ///////////////////////////////////////////////////////////////////////////
                if (isset($ploopi_mainmenu) && $ploopi_mainmenu != $_SESSION['ploopi']['mainmenu']) // new main menu selected
                {
                    $_SESSION['ploopi']['mainmenu'] = $ploopi_mainmenu;

                    $_SESSION['ploopi']['backoffice']['workspaceid'] = $_SESSION['ploopi']['workspaces_allowed'][0];

                    if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ploopi_loadparams();

                    $_SESSION['ploopi']['backoffice']['moduleid'] = '';
                    $_SESSION['ploopi']['action'] = 'public';
                    $_SESSION['ploopi']['moduletype'] = '';
                    $_SESSION['ploopi']['moduletypeid'] = '';
                    $_SESSION['ploopi']['modulelabel'] = '';

                    switch($_SESSION['ploopi']['mainmenu'])
                    {
                        case _PLOOPI_MENU_MYWORKSPACE:
                        case _PLOOPI_MENU_SEARCH:
                        //case _PLOOPI_MENU_ABOUT:
                            $ploopi_moduleid = _PLOOPI_MODULE_SYSTEM;
                            $ploopi_action = 'public';
                        break;
                    }
                }

                if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
                {

                    ///////////////////////////////////////////////////////////////////////////
                    // SWITCH WORKSPACE
                    ///////////////////////////////////////////////////////////////////////////

                    // Traitement d'un car particulier lié au détachement d'un utilisateur à l'espace qu'il consulte
                    if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]))
                    {
                        $ploopi_workspaceid = $_SESSION['ploopi']['hosts']['backoffice'][0];
                    }

                    if (isset($_REQUEST['ploopi_switch_workspace']) || (isset($ploopi_workspaceid) && $_SESSION['ploopi']['backoffice']['workspaceid'] != $ploopi_workspaceid && isset($_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['backoffice'])) // new group selected
                    {
                        $_SESSION['ploopi']['mainmenu'] = _PLOOPI_MENU_WORKSPACES;
                        $_SESSION['ploopi']['backoffice']['workspaceid'] = $ploopi_workspaceid;
                        $_SESSION['ploopi']['backoffice']['moduleid'] = '';
                        $_SESSION['ploopi']['action'] = 'public';
                        $_SESSION['ploopi']['moduletype'] = '';
                        $_SESSION['ploopi']['moduletypeid'] = '';
                        $_SESSION['ploopi']['modulelabel'] = '';

                        // load params
                        ploopi_loadparams();
                    }

                    ///////////////////////////////////////////////////////////////////////////
                    // LOOK FOR AUTOCONNECT MODULE
                    ///////////////////////////////////////////////////////////////////////////

                    if (!isset($ploopi_moduleid) && $_SESSION['ploopi']['backoffice']['moduleid'] == '')
                    {
                        $arrModules = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]['modules'];
                        $intAutoconnectModuleId = null;

                        foreach($arrModules as $intModuleId)
                        {
                            if (is_null($intAutoconnectModuleId) && $_SESSION['ploopi']['modules'][$intModuleId]['active'] && $_SESSION['ploopi']['modules'][$intModuleId]['autoconnect']) $intAutoconnectModuleId = $intModuleId;
                        }

                        if (is_null($intAutoconnectModuleId) && ploopi_ismanager()) $intAutoconnectModuleId = _PLOOPI_MODULE_SYSTEM;

                        if (!is_null($intAutoconnectModuleId))
                        {

                            $ploopi_moduleid = $intAutoconnectModuleId;

                            $ploopi_action = $intAutoconnectModuleId == _PLOOPI_MODULE_SYSTEM ? 'admin' : 'public';
                        }
                    }
                }

                ///////////////////////////////////////////////////////////////////////////
                // SWITCH MODULE
                ///////////////////////////////////////////////////////////////////////////

                if (isset($ploopi_moduleid) && $ploopi_moduleid != $_SESSION['ploopi']['backoffice']['moduleid']) // new module selected
                {
                    $_SESSION['ploopi']['backoffice']['moduleid'] = $ploopi_moduleid;

                    /**
                    * New module selected
                    * => Load module informations
                    */

                    $select =   "
                                SELECT  ploopi_module.id,
                                        ploopi_module.id_module_type,
                                        ploopi_module.label,
                                        ploopi_module_type.label AS module_type

                                FROM    ploopi_module,
                                        ploopi_module_type

                                WHERE   ploopi_module.id_module_type = ploopi_module_type.id
                                AND     ploopi_module.id = {$_SESSION['ploopi']['backoffice']['moduleid']}
                                ";

                    $answer = $db->query($select);
                    if ($fields = $db->fetchrow($answer))
                    {
                        $_SESSION['ploopi']['moduletype'] = $fields['module_type'];
                        $_SESSION['ploopi']['moduletypeid'] = $fields['id_module_type'];
                        $_SESSION['ploopi']['modulelabel'] = $fields['label'];
                    }
                }

                // new action selected
                if (isset($ploopi_action) && ($ploopi_action == 'public' || $ploopi_action == 'admin') ) $_SESSION['ploopi']['action'] = $ploopi_action;
            }

            if (empty($_SESSION['ploopi']['backoffice']['workspaceid'])) $_SESSION['ploopi']['backoffice']['workspaceid'] = $_SESSION['ploopi']['hosts']['backoffice'][0];

            $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['backoffice']['moduleid'];
            $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['backoffice']['workspaceid'];


            if (_PLOOPI_TOKEN) {
                $_SESSION['ploopi']['env'] = sprintf(
                    "%s/%s/%s/%s/%s",
                    $_SESSION['ploopi']['mainmenu'],
                    $_SESSION['ploopi']['workspaceid'],
                    $_SESSION['ploopi']['moduleid'],
                    $_SESSION['ploopi']['action'],
                    $_SESSION['ploopi']['token']
                );

                // Suppression des jetons périmés
                $mint = time() - _PLOOPI_TOKENTIME;
                foreach($_SESSION['ploopi']['tokens'] as $k => $t) if ($t < $mint) unset($_SESSION['ploopi']['tokens'][$k]);
                // Limitation du nombre de jetons conservés
                $_SESSION['ploopi']['tokens'] = array_slice($_SESSION['ploopi']['tokens'], -_PLOOPI_TOKENMAX, _PLOOPI_TOKENMAX);

            } else {
                $_SESSION['ploopi']['env'] = sprintf(
                    "%s/%s/%s/%s",
                    $_SESSION['ploopi']['mainmenu'],
                    $_SESSION['ploopi']['workspaceid'],
                    $_SESSION['ploopi']['moduleid'],
                    $_SESSION['ploopi']['action']
                );
            }

        }
        else
        {
            $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['frontoffice']['moduleid'];
            $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['frontoffice']['workspaceid'];
        }


        if (in_array(self::$script, array('admin', 'admin-light', 'index', 'index-light'))) {
            self::$workspace = array();
            $objWorkspace = new workspace();
            $objWorkspace->open($_SESSION['ploopi']['workspaceid']);
            self::$workspace = $objWorkspace->fields;

            ///////////////////////////////////////////////////////////////////////////
            // CHOOSE TEMPLATE
            ///////////////////////////////////////////////////////////////////////////

            $template_name = self::$workspace['template'];

            if (empty($template_name) || !file_exists("./templates/backoffice/{$template_name}")) $template_name = _PLOOPI_DEFAULT_TEMPLATE;

            $_SESSION['ploopi']['template_name'] = $template_name;
            $_SESSION['ploopi']['template_path'] = "./templates/backoffice/{$_SESSION['ploopi']['template_name']}";
        }


        // shortcuts for admin & workspaceid
        if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['adminlevel'])) $_SESSION['ploopi']['adminlevel'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['adminlevel'];
        else $_SESSION['ploopi']['adminlevel'] = 0;

        ///////////////////////////////////////////////////////////////////////////
        // LOAD LANGUAGE FILE
        ///////////////////////////////////////////////////////////////////////////

        if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'] != 'french' && file_exists("./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php"))
        {
            include_once "./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php";
        }
        else include_once "./lang/french.php"; // default language file (french)

        // View modes for modules
        $ploopi_viewmodes =
            array(
                _PLOOPI_VIEWMODE_UNDEFINED  => _PLOOPI_LABEL_VIEWMODE_UNDEFINED,
                _PLOOPI_VIEWMODE_PRIVATE    => _PLOOPI_LABEL_VIEWMODE_PRIVATE,
                _PLOOPI_VIEWMODE_DESC       => _PLOOPI_LABEL_VIEWMODE_DESC,
                _PLOOPI_VIEWMODE_ASC        => _PLOOPI_LABEL_VIEWMODE_ASC,
                _PLOOPI_VIEWMODE_GLOBAL     => _PLOOPI_LABEL_VIEWMODE_GLOBAL,
                _PLOOPI_VIEWMODE_ASCDESC   => _PLOOPI_LABEL_VIEWMODE_ASCDESC
            );

        $ploopi_system_levels =
            array(
                _PLOOPI_ID_LEVEL_USER           => _PLOOPI_LEVEL_USER,
                _PLOOPI_ID_LEVEL_GROUPMANAGER   => _PLOOPI_LEVEL_GROUPMANAGER,
                _PLOOPI_ID_LEVEL_GROUPADMIN     => _PLOOPI_LEVEL_GROUPADMIN,
                _PLOOPI_ID_LEVEL_SYSTEMADMIN    => _PLOOPI_LEVEL_SYSTEMADMIN
            );

        ///////////////////////////////////////////////////////////////////////////
        // UPDATE LIVE STATS
        ///////////////////////////////////////////////////////////////////////////
        if (session_id() != '')
        {
            $timestplimit = ploopi_timestamp_add(ploopi_createtimestamp(), 0, 0, -min( _PLOOPI_SESSIONTIME,  86400));
            $db->query("DELETE FROM ploopi_connecteduser WHERE timestp < {$timestplimit}");
            $objConnectedUser = new connecteduser();
            $objConnectedUser->open(session_id());
            $objConnectedUser->fields['sid'] = session_id();
            $objConnectedUser->fields['ip'] = implode(',', $_SESSION['ploopi']['remote_ip']);
            $objConnectedUser->fields['domain'] = (empty($_SESSION['ploopi']['host'])) ? '' : $_SESSION['ploopi']['host'];
            $objConnectedUser->fields['timestp'] = ploopi_createtimestamp();
            $objConnectedUser->fields['user_id'] = $_SESSION['ploopi']['userid'];
            $objConnectedUser->fields['workspace_id'] = $_SESSION['ploopi']['workspaceid'];
            $objConnectedUser->fields['module_id'] = $_SESSION['ploopi']['moduleid'];
            $objConnectedUser->fields['timestp'] = ploopi_createtimestamp();
            $objConnectedUser->save();
            $db->query("SELECT count(*) as c FROM ploopi_connecteduser WHERE user_id > 0");
            $row = $db->fetchrow();
            $_SESSION['ploopi']['connectedusers'] = $row['c'];

            $db->query("SELECT count(*) as c FROM ploopi_connecteduser WHERE user_id = 0");
            $row = $db->fetchrow();
            $_SESSION['ploopi']['anonymoususers'] = $row['c'];
        }

        ///////////////////////////////////////////////////////////////////////////
        // SOME SECURITY TESTS
        ///////////////////////////////////////////////////////////////////////////

        $ploopi_errornum = 0;
        if ($_SESSION['ploopi']['connected'])
        {
            // teste moduleid
            if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]))) $ploopi_errornum = 3;
            // test if module is active
            elseif (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['active'])) $ploopi_errornum = 5;

            // test workspaceid
            if (!$ploopi_errornum && ($_SESSION['ploopi']['workspaceid']!= '' && !isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]))) $ploopi_errornum = 6;
        }

        if ($ploopi_errornum)
        {
            session_destroy();
            echo "<html><body><div style=\"text-align:center;\"><br /><br /><h1>Erreur de sécurité</h1>reconnectez vous ou fermez votre navigateur ou contactez l'administrateur système<br /><br /><b>erreur : $ploopi_errornum</b><br /><br /><a href=\"admin.php\">continuer</a></div></body></html>";
            ploopi_die();
        }

        $_SESSION['ploopi']['uri'] = (empty($_SERVER['QUERY_STRING'])) ? '' : "admin.php?{$_SERVER['QUERY_STRING']}";
    }

    /**
     * Chargement très simplifié de l'environnement Ploopi.
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function startlight()
    {
        include_once './include/classes/workspace.php';

        if (self::$initsession) self::getworkspaces();

        switch(self::$script)
        {
            case 'cron':
            case 'webservice':
            case 'backend':
                $_SESSION['ploopi']['mode'] = 'backoffice';
            break;

            default:
                $_SESSION['ploopi']['mode'] = 'frontoffice';
            break;
        }

        switch ($_SESSION['ploopi']['mode'])
        {
            case 'frontoffice':
                if (isset($_SESSION['ploopi']['hosts']['frontoffice'][0]))
                    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['hosts']['frontoffice'][0];
                else ploopi_die();
            break;

            case 'backoffice':
                if (isset($_SESSION['ploopi']['hosts']['backoffice'][0]))
                    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['hosts']['backoffice'][0];
                else ploopi_die();

                self::getmodules();
            break;
        }

        if (isset($_REQUEST['ploopi_moduleid']) && is_numeric($_REQUEST['ploopi_moduleid'])) $_SESSION['ploopi']['moduleid'] = $_REQUEST['ploopi_moduleid'];

        self::setheader_connected();
    }

    /**
     * Chargement des espaces de travail (avec application du filtrage par nom de domaine)
     *
     * @package ploopi
     * @subpackage start
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function getworkspaces()
    {
        global $db;

        include_once './include/classes/workspace.php';

        /**
         * Suppression des espaces de travail déjà sélectionnés
         */

        unset($_SESSION['ploopi']['workspaces']);

        ///////////////////////////////////////////////////////////////////////////
        // Liste des espaces pour le domaine courant
        // On en profite pour appliquer l'héritage implicite des domaines pour les sous-espaces de travail
        ///////////////////////////////////////////////////////////////////////////

        $db->query("
            SELECT      id,
                        frontoffice_domainlist,
                        backoffice_domainlist,
                        parents,
                        backoffice,
                        frontoffice,
                        priority,
                        label,
                        code

            FROM        ploopi_workspace
            WHERE       system = 0
            ORDER BY    depth,
                        label
        ");

        $workspaces = array();
        while ($fields = $db->fetchrow())
        {
            $workspaces[$fields['id']] = $fields;

            self::$workspaces[$fields['id']] = array(
                'id' => $fields['id'],
                'label' => $fields['label'],
                'code' => $fields['code'],
                'priority' => $fields['priority']
            );

            $workspaces[$fields['id']]['parents_array'] = preg_split('/;/',$workspaces[$fields['id']]['parents']);
            $workspaces[$fields['id']]['frontoffice_domain_array'] = preg_split("/[\s,;]+/", $fields['frontoffice_domainlist']);
            $workspaces[$fields['id']]['backoffice_domain_array'] = preg_split("/[\s,;]+/", $fields['backoffice_domainlist']);

            if (trim($workspaces[$fields['id']]['frontoffice_domainlist']) == '')
            {
                $p_array = $workspaces[$fields['id']]['parents_array'];
                for ($i=sizeof($p_array)-1;$i>=0;$i--)
                {
                    if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['frontoffice_domainlist']) != '')
                    {
                        $workspaces[$fields['id']]['frontoffice_domainlist'] = $workspaces[$p_array[$i]]['frontoffice_domainlist'];
                        $workspaces[$fields['id']]['frontoffice_domain_array'] = $workspaces[$p_array[$i]]['frontoffice_domain_array'];
                        break;
                    }
                }
            }

            if (trim($workspaces[$fields['id']]['backoffice_domainlist']) == '')
            {
                $p_array = $workspaces[$fields['id']]['parents_array'];
                for ($i=sizeof($p_array)-1;$i>=0;$i--)
                {
                    if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['backoffice_domainlist']) != '')
                    {
                        $workspaces[$fields['id']]['backoffice_domainlist'] = $workspaces[$p_array[$i]]['backoffice_domainlist'];
                        $workspaces[$fields['id']]['backoffice_domain_array'] = $workspaces[$p_array[$i]]['backoffice_domain_array'];
                        break;
                    }
                }
            }
        }

        $_SESSION['ploopi']['allworkspaces'] = implode(',', array_keys($workspaces));

        $host_array = array($_SESSION['ploopi']['host'], '*');

        $_SESSION['ploopi']['hosts'] =
            array(
                'frontoffice' => array(),
                'backoffice' => array()
            );

        // on garde les id de espaces autorisés en fonction du domaine courant
        foreach($workspaces as $wid => $wsp)
        {
            foreach($wsp['frontoffice_domain_array'] as $domain)
            {
                if ($workspaces[$wid]['frontoffice'] && sizeof(array_intersect($workspaces[$wid]['frontoffice_domain_array'], $host_array)) && !in_array($wid, $_SESSION['ploopi']['hosts']['frontoffice'])) $_SESSION['ploopi']['hosts']['frontoffice'][] = $wid;
            }
            foreach($wsp['backoffice_domain_array'] as $domain)
            {
                if ($workspaces[$wid]['backoffice'] && sizeof(array_intersect($workspaces[$wid]['backoffice_domain_array'], $host_array)) && !in_array($wid, $_SESSION['ploopi']['hosts']['backoffice'])) $_SESSION['ploopi']['hosts']['backoffice'][] = $wid;
            }
        }

        // Espace par défaut front/back
        if (isset($_SESSION['ploopi']['hosts']['frontoffice'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['frontoffice'][0]];
        if (isset($_SESSION['ploopi']['hosts']['backoffice'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['backoffice'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['backoffice'][0]];

        foreach($_SESSION['ploopi']['workspaces'] as $wid => $wsp)
        {
            $workspace = new workspace();
            if ($workspace->open($wid))
            {
                $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);
            }
        }

        //ploopi_print_r($_SESSION['ploopi']['workspaces']);
        //ploopi_print_r($workspaces);
        //die();

    }

    /**
     * Chargement des modules + paramètres
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function getmodules()
    {
        global $db;

        $_SESSION['ploopi']['modules'] = array();
        $_SESSION['ploopi']['moduletypes'] = array();

        // On récupère les modules
        $db->query("
            SELECT      m.id,
                        m.label,
                        m.active,
                        m.visible,
                        m.public,
                        m.autoconnect,
                        m.shared,
                        m.viewmode,
                        m.transverseview,
                        m.id_module_type,
                        m.id_workspace,
                        mt.label as moduletype,
                        mt.version,
                        mt.author,
                        mt.date

            FROM        ploopi_module m

            INNER JOIN  ploopi_module_type mt ON m.id_module_type = mt.id
        ");

        while ($fields = $db->fetchrow())
        {
            if (empty($_SESSION['ploopi']['moduletypes'][$fields['moduletype']]))
            {
                $_SESSION['ploopi']['moduletypes'][$fields['moduletype']] = array('version' => $fields['version'], 'author' => $fields['author'], 'date' => $fields['date']);
            }
            $_SESSION['ploopi']['modules'][$fields['id']] = $fields;
        }

        $listmodules = implode(',',array_keys($_SESSION['ploopi']['modules']));

        ploopi_loadparams();

        $_SESSION['ploopi']['paramloaded'] = true;
    }

    /**
     * Retourne les informations de l'espace courant
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     * @return array
     */

    public static function getworkspace() { return self::$workspace; }


    /**
     * Retourne le type de script
     *
     * @package ploopi
     * @subpackage loader
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     * @return array
     */

    public static function getscript() { return self::$script; }

}
