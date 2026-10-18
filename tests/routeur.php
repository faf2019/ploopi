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
 * Routeur du serveur PHP intégré, pour l'instance de test montée par tests/instance.sh.
 *
 * Il reproduit les deux règles de .htaccess_modele dont dépend le socle :
 *
 * 1. tout script .php est redirigé vers index.php, de même que toute URL ne
 *    correspondant pas à un fichier existant ;
 * 2. REDIRECT_STATUS vaut 200, sans quoi loader::_rewrite() ne s'exécute pas et les
 *    chemins virtuels (admin.php, admin-light.php, index-quick.php, backend.php) ne
 *    sont pas reconnus.
 *
 * Il n'est PAS destiné à la production : le serveur intégré de PHP est mono-processus
 * et sans durcissement. Apache ou nginx avec .htaccess_modele restent la cible.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$strRacine = dirname(__DIR__);
$strUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$strFichier = $strRacine.$strUri;

// Dossiers interdits par .htaccess_modele (RewriteRule ^(config|bin|data|doc|include|install|lang|tools)/.*$ - [F]) :
// le serveur intégré servirait data/ tel quel — c'est le risque visé par le lot 10
// (_PLOOPI_PATHDATA sous la racine web) : le routeur reproduit l'interdiction.
if (preg_match('@^/(config|bin|data|doc|include|install|lang|tools)/@', $strUri))
{
    http_response_code(403);
    exit;
}

// Exemples, tests et documentation des bibliothèques, interdits par .htaccess_modele
// (RewriteRule ^vendor/.+/(samples|examples|tests?|docs?)/ - [F])
if (preg_match('@^/vendor/.+/(samples|examples|tests?|docs?)/@', $strUri))
{
    http_response_code(403);
    exit;
}

// Fichier statique existant : servi tel quel par le serveur intégré.
if ($strUri !== '/' && file_exists($strFichier) && !is_dir($strFichier) && !preg_match('/\.php[345]?$/', $strUri)) return false;

/**
 * REDIRECT_STATUS n'est posé que sur une RÉÉCRITURE interne. Apache sert « / » par
 * DirectoryIndex, sans réécriture : loader::_rewrite() ne s'exécute donc pas et le
 * frontoffice est rendu. Le poser systématiquement faisait répondre 404 sur « / »,
 * le chemin n'étant reconnu par aucune règle.
 */
if ($strUri !== '/' && $strUri !== '/index.php') $_SERVER['REDIRECT_STATUS'] = '200';

/*
 * « /index.php » non plus : la règle « tout .php vers index.php » le réécrirait vers
 * lui-même, ce que mod_rewrite ignore (« initial URL equal rewritten URL »). Apache le
 * sert donc sans REDIRECT_STATUS, comme « / ». C'est l'URL des formulaires publics
 * (index.php?ploopi_url=…), qui répondait 404.
 */

/**
 * Après réécriture, Apache expose le script cible : PHP_SELF et SCRIPT_NAME valent
 * « /index.php ». Le serveur intégré, selon la version de PHP, y laisse l'URI
 * demandée : pour « /articles/x-h1.html », _PLOOPI_SELFPATH (dirname de PHP_SELF)
 * devenait « /articles », retranché de l'URI avant la réécriture inverse, et le
 * frontoffice répondait 404.
 */
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $strRacine.'/index.php';

chdir($strRacine);
require $strRacine.'/index.php';
