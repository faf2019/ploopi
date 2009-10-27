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
 * Fonctions de base du coeur de Ploopi
 *
 * @package ploopi
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Affiche un message et termine le script courant.
 * Peut envoyer un mail contenant les erreurs rencontr�es durant l'ex�cution du script.
 * Peut vider le buffer en cours.
 * Ferme la session en cours.
 * Ferme la connexion � la base de donn�es (si ouverte).
 *
 * @param mixed $var variable � afficher
 * @param boolean $flush true si la sortie doit �tre vid�e (true par d�faut)
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see die
 * @see ploopi_print_r
 */

function ploopi_die($var = null, $flush = true)
{
    global $ploopi_errorlevel;
    global $ploopi_errors_level;
    global $ploopi_errors_nb;
    global $ploopi_errors_msg;
    global $db;

    global $ploopi_timer;

    $strHost = (php_sapi_name() != 'cli') ? $_SERVER['HTTP_HOST'] : '';

    if (
        !empty($ploopi_errors_level) &&
        $ploopi_errors_level &&
        defined('_PLOOPI_MAIL_ERRORS') &&
        _PLOOPI_MAIL_ERRORS  &&
        defined('_PLOOPI_ADMINMAIL') &&
        _PLOOPI_ADMINMAIL != ''
        )
    {
        mail(
            _PLOOPI_ADMINMAIL,
            "[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$strHost}]",
            "{$ploopi_errors_nb} erreur(s) sur {$ploopi_errors_msg}".
            "\n_SERVER:\n".print_r($_SERVER, true).
            "\n_POST:\n".print_r($_POST, true).
            "\n_GET:\n".print_r($_GET, true)
            // ."\n_SESSION:\n".print_r($_SESSION, true)
        );
    }

    if (!is_null($var))
    {
        if (is_string($var)) echo $var;
        else ploopi_print_r($var);
    }

    if (php_sapi_name() != 'cli') session_write_close();

    if ($flush) while (ob_get_level()>1) ob_end_flush();

    die();
}

/**
 * D�tecte si le navigateur supporte la compression gzip
 *
 * @return boolean true si le navigateur supporte la compression gzip
 *
 * @copyright tellinya.com
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @link http://www.tellinya.com/read/2007/09/09/106.html
 */

function ploopi_accepts_gzip()
{
    return isset($_SERVER['HTTP_ACCEPT_ENCODING']) && in_array('gzip', explode(',', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_ENCODING']))));
}

/**
 * G�re la sortie du buffer principal.
 * Met � jour le rendu final en mettant � jour les variables d'�xection.
 * Compresse �ventuellement le contenu.
 * Ecrit dans le log.
 *
 * @param string $buffer contenu du buffer de sortie
 * @return string buffer modifi�
 *
 * @see _PLOOPI_USE_OUTPUT_COMPRESSION
 * @see ob_start
 */

function ploopi_ob_callback($buffer)
{
    global $ploopi_timer;
    global $db;

    // On essaye de r�cup�rer le content-type du contenu du buffer
    $content_type = 'text/html';
    $headers = headers_list();
    $booDownloadFile = false;

    foreach($headers as $property)
    {
        $matches = array();

        if (preg_match('/Content-type:((.*);(.*)|(.*))/i', $property, $matches))
        {
            $content_type = (empty($matches[2])) ? $matches[1] : $matches[2];
            $content_type = strtolower(trim($content_type));
        }

        if (preg_match('/X-Ploopi:(.*)/i', $property, $matches))
        {
            if (isset($matches[1]))
            {
                switch(trim($matches[1]))
                {
                    case 'Download': $booDownloadFile = true; break;
                }
            }
        }
    }

    $ploopi_stats = array();

    if (isset($buffer)) $ploopi_stats['pagesize'] = strlen($buffer);
    else $ploopi_stats['pagesize'] = 0;

    if (isset($db))
    {
        $ploopi_stats['numqueries'] = $db->get_num_queries();
        $ploopi_stats['sql_exectime'] = round($db->get_exectime_queries()*1000,0);
    }
    else
    {
        $ploopi_stats['numqueries'] = 0;
        $ploopi_stats['sql_exectime'] = 0;
    }

    if (isset($ploopi_timer))
    {
        $ploopi_stats['total_exectime'] = round($ploopi_timer->getexectime()*1000,0);
        $ploopi_stats['sql_ratiotime'] = round(($ploopi_stats['sql_exectime']*100)/$ploopi_stats['total_exectime'] ,0);
        $ploopi_stats['php_ratiotime'] = 100 - $ploopi_stats['sql_ratiotime'];
    }
    else
    {
        $ploopi_stats['total_exectime'] = 0;
        $ploopi_stats['sql_ratiotime'] = 0;
        $ploopi_stats['php_ratiotime'] = 0;
    }

    $ploopi_stats['php_memory'] = memory_get_peak_usage();

    $ploopi_stats['sessionsize'] = isset($_SESSION) ? strlen(session_encode()) : 0;

    if (defined('_PLOOPI_ACTIVELOG') && _PLOOPI_ACTIVELOG && isset($db))
    {
        include_once './include/functions/date.php';
        include_once './include/classes/log.php';

        $log = new log();

        $log->fields['request_method'] = $_SERVER['REQUEST_METHOD'];
        $log->fields['query_string'] = $_SERVER['QUERY_STRING'];
        $log->fields['remote_addr'] = (empty($_SESSION['ploopi']['remote_ip'])) ? '' : implode(',', $_SESSION['ploopi']['remote_ip']);
        $log->fields['remote_port'] = $_SERVER['REMOTE_PORT'];
        $log->fields['script_filename'] = $_SERVER['SCRIPT_FILENAME'];
        $log->fields['script_name'] = $_SERVER['SCRIPT_NAME'];
        $log->fields['request_uri'] = $_SERVER['REQUEST_URI'];
        $log->fields['ploopi_moduleid'] = (empty($_SESSION['ploopi']['moduleid'])) ? 0 : $_SESSION['ploopi']['moduleid'];
        $log->fields['ploopi_userid'] = (empty($_SESSION['ploopi']['userid'])) ? 0 : $_SESSION['ploopi']['userid'];
        $log->fields['ploopi_workspaceid'] = (empty($_SESSION['ploopi']['workspaceid'])) ? 0 : $_SESSION['ploopi']['workspaceid'];;
        $log->fields['ts'] = ploopi_createtimestamp();

        require_once 'Net/UserAgent/Detect.php';

        $log->fields['browser'] = Net_UserAgent_Detect::getBrowserString();
        $log->fields['system'] = Net_UserAgent_Detect::getOSString();

        $log->fields['total_exec_time'] = $ploopi_stats['total_exectime'];
        $log->fields['sql_exec_time'] = $ploopi_stats['sql_exectime'];
        $log->fields['sql_percent_time'] = $ploopi_stats['sql_ratiotime'];
        $log->fields['php_percent_time'] = $ploopi_stats['php_ratiotime'];
        $log->fields['numqueries'] = $ploopi_stats['numqueries'];
        $log->fields['page_size'] = $ploopi_stats['pagesize'];
        $log->save();
    }

    if ($content_type == 'text/html' && !$booDownloadFile)
    {
        $array_tags = array(
            '<PLOOPI_PAGE_SIZE>',
            '<PLOOPI_EXEC_TIME>',
            '<PLOOPI_PHP_P100>',
            '<PLOOPI_SQL_P100>',
            '<PLOOPI_NUMQUERIES>',
            '<PLOOPI_SESSION_SIZE>',
            '<PLOOPI_PHP_MEMORY>'
        );

        $array_values = array(
            sprintf("%.02f",$ploopi_stats['pagesize']/1024),
            $ploopi_stats['total_exectime'],
            $ploopi_stats['php_ratiotime'],
            $ploopi_stats['sql_ratiotime'],
            $ploopi_stats['numqueries'],
            sprintf("%.02f",$ploopi_stats['sessionsize']/1024),
            sprintf("%.02f",$ploopi_stats['php_memory']/1024)
       );

        $buffer = trim(str_replace($array_tags, $array_values, $buffer));
    }

    if (!$booDownloadFile && _PLOOPI_USE_OUTPUT_COMPRESSION && ploopi_accepts_gzip() && ($content_type == 'text/html' || $content_type == 'text/xml' || $content_type == 'text/x-json'))
    {
        header("Content-Encoding: gzip");
        return gzencode($buffer);
        
    }
    else
    {
        // Attention, Content-Encoding: none ET Content-Type: text/html ne font pas bon m�nage !
        // => Probl�me avec le validateur W3C : Line 1, Column 0: end of document in prolog
        if ($content_type != 'text/html') header("Content-Encoding: none");

        return $buffer;
    }
}

/**
 * Affiche des informations lisibles pour une variable php (bas� sur la fonction php print_r())
 *
 * @param mixed $var variable � afficher
 * @param boolean $return true si le contenu doit �tre retourn�, false si le contenu doit �tre affich� (false par d�faut)
 * @return mixed rien si $return = false, sinon les informations lisibles de la variable (html)
 */

function ploopi_print_r($var, $return = false)
{
    $p = '<pre style="text-align:left;">'.print_r($var, true).'</pre>';
    if($return) return($p);
    else echo $p;
}

/**
 * Vide les buffers de sortie ouverts en pr�servant le buffer principal
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see ploopi_ob_callback
 */

function ploopi_ob_clean()
{
    while (ob_get_level() > 1) @ob_end_clean();
    if (ob_get_level() == 1) ob_clean();
}

/**
 * Version sp�ciale de ploopi_redirect qui n�cessite que les param�tres soient d�j� urlencod�s (via la fonction urlencode())
 *
 * @see ploopi_redirect
 */
function ploopi_redirect_trusted($url, $urlencode = true, $internal = true, $refresh = 0)
{
    ploopi_redirect($url, $urlencode, $internal, $refresh, true);
}

/**
 * Redirige le script vers une url et termine le script courant
 *
 * @param string $url URL de redirection
 * @param boolean $urlencode true si l'URL doit �tre chiffr�e (true par d�faut)
 * @param boolean $internal true si la redirection est interne au site (true par d�faut)
 * @param int $refresh dur�e en seconde avant la redirection (0 par d�faut)
 */

function ploopi_redirect($url, $urlencode = true, $internal = true, $refresh = 0, $trusted = false)
{
    include_once './include/functions/crypt.php';

    if ($internal) $url = _PLOOPI_BASEPATH.'/'.$url;
    if ($urlencode) $url = $trusted ? ploopi_urlencode_trusted($url) : ploopi_urlencode($url);

    if (empty($refresh) || !is_numeric($refresh)) header("Location: {$url}");
    else header("Refresh: {$refresh}; url={$url}");

    ploopi_die();
}

/**
 * Charge l'environnement du module : variables globales, constantes, fonctions.
 * En option : fichiers javascript, feuilles de styles, ent�tes (head)
 *
 * @param string $moduletype nom du module
 * @param boolean $js true si les fichiers javascript doivent �tre charg�s
 * @param boolean $css true si les feuilles de style doivent �tre charg�es
 * @param boolean $head true si l'ent�te doit �tre charg�e
 * @return boolean true si le module a �t� initialis�
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_init_module($moduletype, $js = true, $css = true, $head = true)
{
    global $ploopi_additional_head;
    global $ploopi_additional_javascript;
    global $template_body;

    if (is_dir($strModulePath = "./modules/{$moduletype}"))
    {
        $version = (empty($_SESSION['ploopi']['moduletypes'][$moduletype]['version'])) ? '' : '?v='.urlencode($_SESSION['ploopi']['moduletypes'][$moduletype]['version']);

        if (!defined("_PLOOPI_INITMODULE_{$moduletype}"))
        {
            define("_PLOOPI_INITMODULE_{$moduletype}",    1);

            $defaultlanguagefile = "{$strModulePath}/lang/french.php";
            $languagefile = (isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'])) ? "{$strModulePath}/lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php" : '';

            $globalfile = "{$strModulePath}/include/global.php";

            if (file_exists($globalfile)) include_once($globalfile);

            if (file_exists($defaultlanguagefile)) include_once($defaultlanguagefile);

            if ($languagefile != 'french' && file_exists($languagefile)) include_once($languagefile);
        }

        if ($head)
        {
            if (!defined("_PLOOPI_INITMODULE_HEAD_{$moduletype}"))
            {
                define("_PLOOPI_INITMODULE_HEAD_{$moduletype}",    1);

                $headfile = "{$strModulePath}/include/head.php";

                // GET MODULE ADDITIONAL HEAD
                if (file_exists($headfile))
                {
                    ob_start();
                    include $headfile;
                    $ploopi_additional_head .= ob_get_contents();
                    @ob_end_clean();
                }
            }
        }

        if ($js)
        {
            if (!defined("_PLOOPI_INITMODULE_JS_{$moduletype}"))
            {
                define("_PLOOPI_INITMODULE_JS_{$moduletype}",    1);

                $jsfile_php = "{$strModulePath}/include/javascript.php";
                $jsfile = "{$strModulePath}/include/functions.js";

                // GET MODULE ADDITIONAL JS
                if (file_exists($jsfile_php))
                {
                    ob_start();
                    include $jsfile_php;
                    $ploopi_additional_javascript .= ob_get_contents();
                    @ob_end_clean();
                }

                // GET MODULE ADDITIONAL JS
                if (file_exists($jsfile) && isset($template_body))
                {
                    $template_body->assign_block_vars('module_js', array(
                        'PATH' => "{$jsfile}{$version}"
                    ));
                }
            }
        }

        if ($css)
        {
            if (!defined("_PLOOPI_INITMODULE_CSS_{$moduletype}"))
            {
                define("_PLOOPI_INITMODULE_CSS_{$moduletype}",    1);

                $cssfile = "{$strModulePath}/include/styles.css";
                $cssfile_ie = "{$strModulePath}/include/styles_ie.css";

                // GET MODULE STYLE
                if (file_exists($cssfile) && isset($template_body))
                {
                    $template_body->assign_block_vars('module_css', array(
                        'PATH' => "{$cssfile}{$version}"
                    ));
                }

                // GET MODULE STYLE FOR IE
                if (file_exists($cssfile_ie) && isset($template_body))
                {
                    $template_body->assign_block_vars('module_css_ie', array(
                        'PATH' => "{$cssfile_ie}{$version}"
                    ));
                }
            }
        }
    }
    else return false;

    return true;
}

/**
 * Retourne l'id du module pass� en param�tre
 *
 * @param string $strModuleName nom du module
 * @param boolean $booFirstOnly true si on souhaite simplement retourner le premier id de module (cas des instances multiples)
 * @return mixed identifiant du module ou tableau des identifiants de modules ou false si aucun module
 *
 * @copyright Exyzt, Ovensia
 * @author Julio Renella, St�phane Escaich
 */

function ploopi_getmoduleid($strModuleName, $booFirstOnly = true)
{
    $arrModuleId = array();

    foreach ($_SESSION['ploopi']['workspaces'][ $_SESSION['ploopi']['workspaceid'] ]['modules'] as $intModuleId)
    {
        if ($_SESSION['ploopi']['modules'][$intModuleId]['moduletype'] == $strModuleName)
        {
            if ($booFirstOnly) return $intModuleId;
            $arrModuleId[] = $intModuleId;
        }
    }

    if (!empty($arrModuleId)) return $arrModuleId;

    return false;
}

/**
 * Chargement des param�tres des modules
 */

function ploopi_loadparams()
{
    // load params
    foreach($_SESSION['ploopi']['params'] as $param_idmodule => $param_type)
    {
        if (!empty($param_type['default']))
            foreach($param_type['default'] as $param_name => $param_value)
                $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;

        if (!empty($param_type['workspace'][$_SESSION['ploopi']['backoffice']['workspaceid']]))
            foreach($param_type['workspace'][$_SESSION['ploopi']['backoffice']['workspaceid']] as $param_name => $param_value)
                $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;

        if (!empty($param_type['user']))
            foreach($param_type['user'] as $param_name => $param_value)
                $_SESSION['ploopi']['modules'][$param_idmodule][$param_name] = $param_value;
    }
}

/**
 * Retourne la liste des espaces affect�s par la vue du module (ascendante/descendante/globale/priv�e/transversale)
 *
 * @param int $moduleid identifiant du module
 * @return string chaine contenant la liste des espaces s�par�s par une virgule
 */

function ploopi_viewworkspaces($moduleid = -1)
{

    if ($_SESSION['ploopi']['workspaceid'] == '') $current_workspaceid = _PLOOPI_SYSTEMGROUP; // HOME PAGE / NO GROUP;
    else $current_workspaceid = $_SESSION['ploopi']['workspaceid'];

    $workspaces = '';

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    switch($_SESSION['ploopi']['modules'][$moduleid]['viewmode'])
    {
        default:
        case _PLOOPI_VIEWMODE_PRIVATE:
            $workspaces = $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_DESC:
            $workspaces = $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_parents'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_ASC:
            $workspaces = $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_children'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_GLOBAL:
            $workspaces = $_SESSION['ploopi']['allworkspaces'];
        break;

        case _PLOOPI_VIEWMODE_ASCDESC:
            $arrWorkspaces = array_merge($_SESSION['ploopi']['workspaces'][$current_workspaceid]['parents'], $_SESSION['ploopi']['workspaces'][$current_workspaceid]['children']);
            $arrWorkspaces[] = $current_workspaceid;

            $workspaces .= implode(',', $arrWorkspaces);
        break;

    }

    if ($_SESSION['ploopi']['modules'][$moduleid]['transverseview'] && $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_brothers'] != '')
    {
        if ($workspaces!='') $workspaces.=',';
        $workspaces .= $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_brothers'];
    }

    return $workspaces;
}

/**
 * Retourne la liste des espaces inversement affect�s par la vue du module (ascendante/descendante/globale/priv�e/transversale)
 *
 * @param int $moduleid identifiant du module
 * @return string chaine contenant la liste des espaces s�par�s par une virgule
 */

function ploopi_viewworkspaces_inv($moduleid = -1)
{

    if ($_SESSION['ploopi']['workspaceid'] == '') $current_workspaceid = _PLOOPI_SYSTEMGROUP; // HOME PAGE / NO GROUP;
    else $current_workspaceid = $_SESSION['ploopi']['workspaceid'];

    $workspaces = '';

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    switch($_SESSION['ploopi']['modules'][$moduleid]['viewmode'])
    {
        default:
        case _PLOOPI_VIEWMODE_PRIVATE:
            $workspaces = $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_ASC:
            $workspaces = $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_parents'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_DESC:
            $workspaces = $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_children'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $current_workspaceid;
        break;

        case _PLOOPI_VIEWMODE_GLOBAL:
            $workspaces = $_SESSION['ploopi']['allworkspaces'];
        break;
    }

    if ($_SESSION['ploopi']['modules'][$moduleid]['transverseview'] && $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_brothers'] != '')
    {
        if ($workspaces!='') $workspaces.=',';
        $workspaces .= $_SESSION['ploopi']['workspaces'][$current_workspaceid]['list_brothers'];
    }

    return $workspaces;
}

/**
 * V�rifie si un drapeau a �t� pos� et met � jour le drapeau
 *
 * @param string $var type de drapeau
 * @param string $value valeur � tester
 * @return bool
 */

function ploopi_set_flag($var, $value)
{
    if (!isset($_SESSION['flags'][$var])) $_SESSION['flags'][$var] = array();;

    if (!isset($_SESSION['flags'][$var][$value]))
    {
        $_SESSION['flags'][$var][$value] = 1;
        return(true);
    }
    else return(false);
}

/**
 * Renvoie un tableau des templates disponibles (frontoffice ou backoffice)
 *
 * @param string $type au choix entre 'frontoffice' et 'backoffice', par d�faut 'frontoffice'
 * @return array tableau des templates disponibles
 */

function ploopi_getavailabletemplates($type = 'frontoffice')
{
    $templates = array();
    $basepath = '.'._PLOOPI_SEP.'templates'._PLOOPI_SEP.$type;

    clearstatcache();

    $p = @opendir(realpath($basepath));

    while ($template = @readdir($p))
    {
        $tplpath=realpath($basepath._PLOOPI_SEP.$template);

        if ((substr($template, 0, 1) != '.') && is_dir($tplpath) && file_exists($tplpath._PLOOPI_SEP.'index.tpl')) $templates[] = $template;
    }

    closedir($p);

    sort($templates);

    return($templates);
}

/**
 * Applique r�cursivement une fonction sur les �l�ments d'un tableau
 *
 * @param callback $func fonction � appliquer sur le tableau
 * @param array $var variable � modifier
 * @return array le tableau modifi�
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see array_map
 */

function ploopi_array_map($func, $var)
{
    if (is_array($var)) { foreach($var as $key => $value) $var[$key] = ploopi_array_map($func, $value); return $var; } else return call_user_func($func, $var);
}

/**
 * Renvoie une erreur 404 dans les ent�tes
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see header
 */

function ploopi_h404() { header("HTTP/1.0 404 Not Found"); }

/**
 * D�connecte l'utilisateur, nettoie la session et renvoie �ventuellement un code d'erreur
 *
 * @param int $errorcode code d'erreur
 * @param int $sleep dur�e d'attente avant la redirection en seconde
 */
function ploopi_logout($intErrorCode = null, $intSleep = 1, $booRedirect = true)
{
    global $session;
    global $arrParsedURI;
    
    // Suppression de l'information de connexion
    $objConnectedUser = new connecteduser();
    if ($objConnectedUser->open(session_id())) $objConnectedUser->delete();

    ploopi_session::destroy_id();

    if ($intSleep > 0) sleep($intSleep);

    // Pr�paration de l'url de redirection
    require_once 'Net/URL.php';
    if ($booRedirect)
    {
        $objUrl = new Net_URL($_SERVER['HTTP_REFERER']);
        if (isset($intErrorCode)) $objUrl->addQueryString('ploopi_errorcode', $intErrorCode);
        ploopi_redirect($objUrl->getURL(), false, false);
    }
    else
    {
        ploopi_redirect(basename($arrParsedURI['path']).(isset($intErrorCode) ? "?ploopi_errorcode={$intErrorCode}" : ''), false);
    }

}

/**
 * Retourne un param�tre de module
 *
 * @param string $strParamName nom du param�tre � lire
 * @param int $intModuleId identifiant du module (optionnel, le module courant si non d�fini)
 * @return string valeur du param�tre
 */
function ploopi_getparam($strParamName, $intModuleId = null)
{
    if (is_null($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
    
    return isset($_SESSION['ploopi']['modules'][$intModuleId][$strParamName]) ? $_SESSION['ploopi']['modules'][$intModuleId][$strParamName] : null;  
}

/**
 * Lit une variable de module en session
 *
 * @param string $strVarName nom de la variable � lire
 * @param int $intModuleId identifiant du module (optionnel, le module courant si non d�fini)
 * @param integer $intWorkspaceId Identifiant de l'espace de travail (optionnel, l'espace courant si non d�fini)
 * @return mixed valeur de la variable
 */
function ploopi_getsessionvar($strVarName, $intModuleId = null, $intWorkspaceId = null)
{
    if (is_null($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
    if (is_null($intWorkspaceId)) $intWorkspaceId = $_SESSION['ploopi']['workspaceid'];
        
    if (!empty($_SESSION['ploopi']['modules'][$intModuleId]['moduletype']))
    {
        $strModuleType = $_SESSION['ploopi']['modules'][$intModuleId]['moduletype'];
        
        return isset($_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName]) ? $_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName] : null;
    } 

    return null;
}

/**
 * Enregistre une variable de module en session
 *
 * @param string $strVarName nom de la variable
 * @param mixed $mixVar contenu de la variable
 * @param integer $intModuleId Identifiant du module (optionnel, le module courant si non d�fini)
 * @param integer $intWorkspaceId Identifiant de l'espace de travail (optionnel, l'espace courant si non d�fini)
 */
function ploopi_setsessionvar($strVarName, $mixVar = null, $intModuleId = null, $intWorkspaceId = null)
{
    if (is_null($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
    if (is_null($intWorkspaceId)) $intWorkspaceId = $_SESSION['ploopi']['workspaceid'];
    
    if (!empty($_SESSION['ploopi']['modules'][$intModuleId]['moduletype']))
    {
        $strModuleType = $_SESSION['ploopi']['modules'][$intModuleId]['moduletype'];
        
        $_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName] = $mixVar;
    }  
}
?>
