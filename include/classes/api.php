<?php
/*
    Copyright (c) 2007-2026 Ovensia
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

namespace ploopi;

/**
 * API HTTP v1, sans session navigateur, en lecture seule et extensible par module.
 * @package ploopi
 * @subpackage api
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
abstract class api
{
    private static $arrKey = null;
    private static $arrWorkspace = array();
    private static $arrProviders = null;

    /** Contrats fournis uniquement par les types de modules installés (code local de confiance). */
    public static function providers()
    {
        if (self::$arrProviders !== null) return self::$arrProviders;
        self::$arrProviders = array();
        $resTypes = db::get()->query('SELECT label FROM ploopi_module_type ORDER BY label');
        while ($arrType = db::get()->fetchrow($resTypes))
        {
            $strType = $arrType['label'];
            if (!preg_match('/^[a-z0-9_]+$/D', $strType)) continue;
            $strFile = './modules/'.$strType.'/include/api.php';
            if (!is_file($strFile)) continue;
            $arrResources = require $strFile;
            if (is_array($arrResources)) self::$arrProviders[$strType] = $arrResources;
        }
        return self::$arrProviders;
    }

    /** Permissions explicites proposées lors de l'émission d'une clé. */
    public static function scopes()
    {
        $arrScopes = array('core:read' => 'Socle : profil, espace et modules');
        foreach (self::providers() as $strType => $arrResources) $arrScopes[$strType.':read'] = $strType.' : lecture';
        return $arrScopes;
    }

    /** Requête paramétrée : identifiants strictement typés, pagination bornée. */
    public static function number($strName, $intDefault, $intMax)
    {
        if (!isset($_GET[$strName])) return $intDefault;
        $mixValue = $_GET[$strName];
        if (!is_string($mixValue) || !preg_match('/^[1-9][0-9]{0,6}$/D', $mixValue) || (int) $mixValue > $intMax)
            throw new \InvalidArgumentException('Paramètre '.$strName.' invalide.');
        return (int) $mixValue;
    }

    /** Collection limitée à l'instance et à l'espace de la clé ; colonnes déclarées par le module. */
    public static function collection($strTable, $arrFields, $strKey, $intModule, $strId = null, $strCondition = '1=1')
    {
        $objDb = db::get();
        $intWorkspace = (int) self::$arrKey['id_workspace'];
        $intPage = $strId === null ? self::number('page', 1, 1000000) : 1;
        $intLimit = $strId === null ? self::number('per_page', 25, 100) : 1;
        $intOffset = ($intPage - 1) * $intLimit;
        // Table, champs et condition sont du code de fournisseur ; aucune expression SQL reçue par HTTP.
        foreach (array_merge(array($strTable, $strKey), $arrFields) as $strIdentifier)
            if (!preg_match('/^[a-zA-Z0-9_]+$/D', $strIdentifier)) throw new \LogicException('Identifiant de fournisseur invalide.');
        $strWhere = 'id_module = '.(int) $intModule.' AND id_workspace = '.$intWorkspace.' AND ('.$strCondition.')';
        if ($strId !== null) $strWhere .= ' AND `'.$strKey."` = '".$objDb->addslashes($strId)."'";
        $strFields = '`'.implode('`, `', $arrFields).'`';
        $resCount = $objDb->query("SELECT COUNT(*) AS total FROM `{$strTable}` WHERE {$strWhere}");
        if (!$resCount) throw new \RuntimeException('Lecture indisponible.');
        $intTotal = (int) $objDb->fetchrow($resCount)['total'];
        if ($strId !== null && !$intTotal) return array(404, array('error' => array('code' => 'not_found', 'message' => 'Ressource introuvable.')));
        $resRows = $objDb->query("SELECT {$strFields} FROM `{$strTable}` WHERE {$strWhere} ORDER BY `{$strKey}` LIMIT {$intLimit} OFFSET {$intOffset}");
        if (!$resRows) throw new \RuntimeException('Lecture indisponible.');
        $arrRows = $objDb->getarray($resRows);
        return array(200, $strId === null ? array('data' => $arrRows, 'meta' => array('page' => $intPage, 'per_page' => $intLimit, 'total' => $intTotal)) : array('data' => $arrRows[0]));
    }

    /** Reconstruit les droits à chaque requête : désactivation, groupes, rattachements et règles IP courants. */
    private static function context()
    {
        $objUser = new user();
        if (!$objUser->open((int) self::$arrKey['id_user']) || !empty($objUser->fields['disabled'])) return false;
        // Compte expiré : refusé comme à la connexion (loader).
        if (!empty($objUser->fields['date_expire']) && $objUser->fields['date_expire'] <= date::createtimestamp()) return false;
        $_SESSION = array('ploopi' => array('userid' => (int) $objUser->fields['id'], 'user' => $objUser->fields, 'modules' => array(), 'moduletypes' => array()));
        $arrWorkspaces = $objUser->getworkspaces();
        $intWorkspace = (int) self::$arrKey['id_workspace'];
        if (empty($arrWorkspaces[$intWorkspace]['adminlevel']) || empty($arrWorkspaces[$intWorkspace]['backoffice'])) return false;
        self::$arrWorkspace = $arrWorkspaces[$intWorkspace];
        if (!ip::isvalid(ip::getrules(self::$arrWorkspace['iprules']))) return false;
        $objWorkspace = new workspace();
        if (!$objWorkspace->open($intWorkspace)) return false;
        self::$arrWorkspace['modules'] = $objWorkspace->getmodules(true);
        $_SESSION['ploopi'] += array('workspaceid' => $intWorkspace, 'moduleid' => 1, 'connected' => true, 'mode' => 'backoffice',
            'adminlevel' => (int) self::$arrWorkspace['adminlevel'], 'workspaces' => array($intWorkspace => self::$arrWorkspace),
            'backoffice' => array('workspaceid' => $intWorkspace, 'moduleid' => 1), 'actions' => $objUser->getactions(array(), true));
        // Respecter mustdefinerule, comme lors d'une connexion au backoffice.
        if (!empty(self::$arrWorkspace['mustdefinerule']) && self::$arrWorkspace['adminlevel'] < _PLOOPI_ID_LEVEL_GROUPADMIN && empty($_SESSION['ploopi']['actions'][$intWorkspace])) return false;
        return true;
    }

    /** Autorisation du périmètre déclaré, en plus des droits utilisateur. */
    private static function scope($strScope)
    {
        return in_array($strScope, explode(' ', self::$arrKey['scopes']), true);
    }

    /** Liste des instances actives réellement rattachées à l'espace de la clé. */
    private static function modules()
    {
        $intWorkspace = (int) self::$arrKey['id_workspace'];
        $resModules = db::get()->query("SELECT m.id, m.label, mt.label AS type FROM ploopi_module m INNER JOIN ploopi_module_type mt ON mt.id = m.id_module_type INNER JOIN ploopi_module_workspace mw ON mw.id_module = m.id WHERE mw.id_workspace = {$intWorkspace} AND m.active = 1 ORDER BY m.id");
        if (!$resModules) throw new \RuntimeException('Modules indisponibles.');
        $arrModules = db::get()->getarray($resModules);
        foreach ($arrModules as &$arrModule) $arrModule['id'] = (int) $arrModule['id'];
        unset($arrModule);
        return $arrModules;
    }

    /** Routage REST explicite. Les méthodes d'écriture sont refusées avant tout fournisseur. */
    private static function dispatch($strRoute)
    {
        if (!self::context()) return array(403, self::error('forbidden', 'Accès à cet espace refusé.'));
        if ($strRoute === '/openapi.json') return self::scope('core:read') ? array(200, self::openapi()) : array(403, self::error('insufficient_scope', 'Permission core:read requise.'));
        if (in_array($strRoute, array('/me', '/workspaces', '/modules'), true))
        {
            if (!self::scope('core:read')) return array(403, self::error('insufficient_scope', 'Permission core:read requise.'));
            switch ($strRoute)
            {
                case '/me':
                    $arrUser = $_SESSION['ploopi']['user'];
                    return array(200, array('data' => array('id' => (int) $arrUser['id'], 'login' => $arrUser['login'], 'firstname' => $arrUser['firstname'], 'lastname' => $arrUser['lastname'])));
                case '/workspaces':
                    return array(200, array('data' => array(array('id' => (int) self::$arrKey['id_workspace'], 'label' => self::$arrWorkspace['label'], 'code' => self::$arrWorkspace['code']))));
                case '/modules': return array(200, array('data' => self::modules()));
            }
        }
        if (preg_match('@^/modules/([1-9][0-9]{0,9})/([a-z][a-z0-9_-]*)(?:/([^/]+))?$@D', $strRoute, $arrMatch))
        {
            $intModule = (int) $arrMatch[1];
            $arrModule = null;
            foreach (self::modules() as $arrCandidate) if ((int) $arrCandidate['id'] === $intModule) $arrModule = $arrCandidate;
            if (!$arrModule) return array(404, self::error('not_found', 'Module introuvable.'));
            $strType = $arrModule['type'];
            if (!self::scope($strType.':read')) return array(403, self::error('insufficient_scope', 'Permission de lecture du module requise.'));
            $arrProviders = self::providers();
            if (!isset($arrProviders[$strType][$arrMatch[2]])) return array(404, self::error('not_found', 'Ressource introuvable.'));
            $arrResource = $arrProviders[$strType][$arrMatch[2]];
            $_SESSION['ploopi']['moduleid'] = $intModule;
            $_SESSION['ploopi']['moduletype'] = $strType;
            $_SESSION['ploopi']['modules'][$intModule] = array('moduletype' => $strType);
            // Un fournisseur peut ajouter un droit d'action au simple accès en lecture au module.
            if (isset($arrResource['action']) && !acl::isactionallowed($arrResource['action'], (int) self::$arrKey['id_workspace'], $intModule)) return array(403, self::error('forbidden', 'Action interdite.'));
            return call_user_func($arrResource['read'], $intModule, isset($arrMatch[3]) ? rawurldecode($arrMatch[3]) : null);
        }
        return array(404, self::error('not_found', 'Route introuvable.'));
    }

    private static function error($strCode, $strMessage)
    {
        return array('error' => array('code' => $strCode, 'message' => $strMessage));
    }

    /** Exécute une requête, sans lire ni créer de cookie ou de session persistante. */
    public static function run($strPath)
    {
        $floatStart = microtime(true);
        $intStatus = 500;
        $arrBody = self::error('internal_error', 'Erreur interne.');
        $strRoute = '/';
        try
        {
            if (!preg_match('@^/v1(/.*)?$@D', $strPath, $arrMatch))
            {
                $intStatus = 404;
                $arrBody = self::error('not_found', 'Version API introuvable.');
            }
            else
            {
                $strRoute = isset($arrMatch[1]) ? $arrMatch[1] : '/';
                $strAuth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : '');
                if (!is_string($strAuth) || !preg_match('/^Bearer (plp_[a-f0-9]{64})$/Di', $strAuth, $arrToken))
                {
                    $intStatus = 401;
                    $arrBody = self::error('unauthorized', 'Clé Bearer requise.');
                }
                else
                {
                    $strHash = hash('sha256', $arrToken[1]);
                    $resKey = db::get()->query("SELECT * FROM ploopi_api_key WHERE token_hash = '{$strHash}' AND revoked_at = 0 AND expires_at > ".time());
                    self::$arrKey = $resKey ? db::get()->fetchrow($resKey) : null;
                    if (!self::$arrKey)
                    {
                        $intStatus = 401;
                        $arrBody = self::error('unauthorized', 'Clé invalide, révoquée ou expirée.');
                    }
                    elseif ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' || $_SERVER['HTTPS'] === '') && (empty($_SERVER['HTTP_X_SSL_REQUEST']) || !ip::trustedproxy((string) $_SERVER['REMOTE_ADDR'])) && !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'), true))
                    {
                        $intStatus = 403;
                        $arrBody = self::error('https_required', 'HTTPS est obligatoire.');
                    }
                    else
                    {
                        $intId = (int) self::$arrKey['id'];
                        $intNow = time();
                        $intWindow = $intNow - 60;
                        $resWindow = db::get()->query("UPDATE ploopi_api_key SET window_count = IF(window_start <= {$intWindow}, 1, window_count + 1), window_start = IF(window_start <= {$intWindow}, {$intNow}, window_start) WHERE id = {$intId} AND (window_start <= {$intWindow} OR window_count < 120)");
                        if (is_null($resWindow)) throw new \RuntimeException('Compteur API indisponible.');
                        if (db::get()->affectedrows() !== 1)
                        {
                            $intStatus = 429;
                            header('Retry-After: 60');
                            $arrBody = self::error('rate_limited', 'Limite de 120 requêtes par minute atteinte.');
                        }
                        elseif ($_SERVER['REQUEST_METHOD'] !== 'GET')
                        {
                            $intStatus = 405;
                            header('Allow: GET');
                            $arrBody = self::error('method_not_allowed', 'API en lecture seule : GET uniquement.');
                        }
                        else list($intStatus, $arrBody) = self::dispatch($strRoute);
                    }
                }
            }
        }
        catch (\InvalidArgumentException $objError)
        {
            $intStatus = 400;
            $arrBody = self::error('invalid_parameter', $objError->getMessage());
        }
        catch (\Throwable $objError)
        {
            error_log('Ploopi API : '.$objError->getMessage());
        }
        if (self::$arrKey)
        {
            $intId = (int) self::$arrKey['id'];
            $intFailure = $intStatus >= 400 ? 1 : 0;
            $intDuration = (int) round((microtime(true) - $floatStart) * 1000);
            $intNow = time();
            db::get()->query("UPDATE ploopi_api_key SET last_used_at = {$intNow}, request_count = request_count + 1, failure_count = failure_count + {$intFailure} WHERE id = {$intId}");
            // Aucun secret, paramètre de requête, contenu ni identifiant métier dans le journal.
            $strSafeRoute = preg_match('@^/modules/[0-9]+/[a-z0-9_-]+(?:/[^/]+)?$@D', $strRoute) ? preg_replace('@(/modules/[0-9]+/[a-z0-9_-]+)(/.*)?$@', '$1/{id}', $strRoute) : (in_array($strRoute, array('/me', '/workspaces', '/modules', '/openapi.json'), true) ? $strRoute : '/unknown');
            $strSafeRoute = db::get()->addslashes(substr($strSafeRoute, 0, 255));
            // Un client qui insiste au-delà de la limite ne remplit pas le journal : les 429 ne sont que comptés.
            if ($intStatus !== 429) db::get()->query("INSERT INTO ploopi_api_request (id_key, route, status, duration_ms, created_at) VALUES ({$intId}, '{$strSafeRoute}', {$intStatus}, {$intDuration}, {$intNow})");
            // Entrées de plus de 30 jours purgées par le cron du module system.
        }
        if (ob_get_level()) ob_clean();
        http_response_code($intStatus);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        if ($intStatus === 401) header('WWW-Authenticate: Bearer realm="Ploopi API"');
        echo json_encode($arrBody, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    }

    /** OpenAPI et documentation HTML utilisent le même registre que le routeur. */
    public static function openapi()
    {
        $arrPaths = array();
        foreach (array('/me' => 'Profil de l’utilisateur de la clé', '/workspaces' => 'Espace autorisé par la clé', '/modules' => 'Modules actifs de cet espace', '/openapi.json' => 'Contrat OpenAPI') as $strPath => $strSummary)
            $arrPaths[$strPath] = array('get' => self::operation($strSummary, array(), 'core:read'));
        $arrCoreFields = array(
            '/me' => array('id' => 'integer', 'login' => 'string', 'firstname' => 'string', 'lastname' => 'string'),
            '/workspaces' => array('id' => 'integer', 'label' => 'string', 'code' => 'string'),
            '/modules' => array('id' => 'integer', 'label' => 'string', 'type' => 'string')
        );
        foreach ($arrCoreFields as $strPath => $arrFields)
        {
            $arrItem = array('type' => 'object', 'properties' => array());
            foreach ($arrFields as $strField => $strType) $arrItem['properties'][$strField] = array('type' => $strType);
            $arrData = $strPath === '/me' ? $arrItem : array('type' => 'array', 'items' => $arrItem);
            $arrPaths[$strPath]['get']['responses'][200]['content'] = array('application/json' => array('schema' => array('type' => 'object', 'required' => array('data'), 'properties' => array('data' => $arrData))));
        }
        $arrPaths['/openapi.json']['get']['responses'][200]['content'] = array('application/json' => array('schema' => array('type' => 'object')));
        foreach (self::providers() as $strType => $arrResources)
        {
            foreach ($arrResources as $strResource => $arrDef)
            {
                $strPath = '/modules/{module_id}/'.$strResource;
                $arrParams = array(array('name' => 'module_id', 'in' => 'path', 'required' => true, 'schema' => array('type' => 'integer', 'minimum' => 1)),
                    array('name' => 'page', 'in' => 'query', 'schema' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 1000000, 'default' => 1)),
                    array('name' => 'per_page', 'in' => 'query', 'schema' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 25)));
                $arrItemSchema = array('type' => 'object', 'properties' => array());
                foreach ($arrDef['fields'] ?? array() as $strField) $arrItemSchema['properties'][$strField] = array('type' => 'string', 'nullable' => true);
                $arrPaths[$strPath] = array('get' => self::operation($arrDef['summary'], $arrParams, $strType.':read'));
                $arrPaths[$strPath]['get']['responses'][200]['content'] = array('application/json' => array('schema' => array('type' => 'object', 'required' => array('data', 'meta'), 'properties' => array(
                    'data' => array('type' => 'array', 'items' => $arrItemSchema), 'meta' => array('$ref' => '#/components/schemas/Pagination')))));

                $arrParams = array($arrParams[0], array('name' => 'id', 'in' => 'path', 'required' => true, 'schema' => array('type' => 'string')));
                $arrPaths[$strPath.'/{id}'] = array('get' => self::operation($arrDef['summary'].' : détail', $arrParams, $strType.':read'));
                $arrPaths[$strPath.'/{id}']['get']['responses'][200]['content'] = array('application/json' => array('schema' => array('type' => 'object', 'required' => array('data'), 'properties' => array('data' => $arrItemSchema))));
            }
        }
        return array('openapi' => '3.0.3', 'info' => array('title' => 'API Ploopi', 'version' => '1.0.0', 'description' => 'Lecture seule. Clés limitées à un utilisateur et un espace, 120 requêtes/minute. Données de module limitées à cet espace.'),
            'servers' => array(array('url' => _PLOOPI_BASEPATH.'/api/v1')), 'security' => array(array('bearerAuth' => array())), 'paths' => $arrPaths,
            'components' => array('securitySchemes' => array('bearerAuth' => array('type' => 'http', 'scheme' => 'bearer')),
                'schemas' => array('Pagination' => array('type' => 'object', 'required' => array('page', 'per_page', 'total'), 'properties' => array('page' => array('type' => 'integer'), 'per_page' => array('type' => 'integer'), 'total' => array('type' => 'integer'))),
                    'Error' => array('type' => 'object', 'required' => array('error'), 'properties' => array('error' => array('type' => 'object', 'required' => array('code', 'message'), 'properties' => array('code' => array('type' => 'string'), 'message' => array('type' => 'string'))))))));
    }

    private static function operation($strSummary, $arrParams, $strScope)
    {
        $arrResponses = array();
        foreach (array(200 => 'Réponse JSON (data et meta pour les collections paginées)', 400 => 'Paramètre invalide', 401 => 'Clé absente, expirée ou révoquée', 403 => 'Droits ou périmètre insuffisants', 404 => 'Ressource introuvable', 405 => 'GET uniquement', 429 => 'Limite atteinte ; Retry-After en secondes', 500 => 'Erreur interne') as $intCode => $strText) $arrResponses[(string) $intCode] = array('description' => $strText);
        foreach ($arrResponses as $strStatus => &$arrResponse)
            if ($strStatus != 200) $arrResponse['content'] = array('application/json' => array('schema' => array('$ref' => '#/components/schemas/Error')));
        unset($arrResponse);
        return array('summary' => $strSummary, 'description' => 'Permission : '.$strScope, 'parameters' => $arrParams, 'responses' => $arrResponses);
    }
}
