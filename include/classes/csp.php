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

use ploopi;

/**
 * Content-Security-Policy (décision D6, puis § 3.32 de la feuille de route).
 *
 * Trois politiques :
 * - de base, appliquée partout : ce qu'aucun écran n'emploie (plugins, <base>
 *   détourné, mise en cadre par un autre site ; en backoffice, formulaires postés
 *   ailleurs et scripts d'une autre origine). Les gestionnaires en ligne restent
 *   permis ('unsafe-inline') ;
 * - stricte, appliquée aux pages déclarées prêtes (csp::strict()) : scripts du
 *   site et scripts en ligne portant le nonce, rien d'autre — ni gestionnaire
 *   onclick, ni URL javascript:, ni eval. Garde-fou : une page dont le HTML
 *   contient malgré tout du script en ligne sans nonce reste sous la politique de
 *   base (csp::finalize()) ;
 * - cible, en Report-Only : la politique stricte sur toutes les pages, pour mesurer
 *   ce qu'il reste à migrer (rapports journalisés par ploopi_csp_report).
 *
 * Le nonce vaut pour la session : les fragments chargés en AJAX (admin-light.php)
 * portent celui de la page qui les reçoit, et jQuery le reporte sur les scripts
 * qu'il exécute. Il n'est posé qu'à la source, sur les scripts du socle : l'ajouter
 * en fin de requête le donnerait aussi à un script injecté.
 *
 * Réglage (config.php) : _PLOOPI_CSP = 'strict' (défaut), 'base' (jamais de
 * politique stricte) ou 'report' (Report-Only seul, comme avant).
 *
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class csp
{
    /**
     * La page courante est-elle déclarée prête pour la politique stricte ?
     *
     * @var boolean
     */
    private static $booStrict = false;

    /**
     * Nonce de la requête (lu une fois, puis constant jusqu'à la fin de la requête)
     *
     * @var string
     */
    private static $strNonce = null;

    /**
     * Retourne le nonce des scripts en ligne (le même pour toute la session)
     *
     * @return string nonce (hexadécimal)
     */
    public static function nonce()
    {
        if (self::$strNonce !== null) return self::$strNonce;

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['ploopi']) && is_array($_SESSION['ploopi']))
        {
            if (empty($_SESSION['ploopi']['csp_nonce'])) $_SESSION['ploopi']['csp_nonce'] = security::generatetoken(16);
            self::$strNonce = $_SESSION['ploopi']['csp_nonce'];
        }
        else self::$strNonce = security::generatetoken(16);

        return self::$strNonce;
    }

    /**
     * Mode réglé dans config.php
     *
     * @return string 'strict', 'base' ou 'report'
     */
    public static function mode()
    {
        $strMode = defined('_PLOOPI_CSP') ? strtolower((string) _PLOOPI_CSP) : 'strict';
        return in_array($strMode, array('strict', 'base', 'report')) ? $strMode : 'strict';
    }

    /**
     * Déclare la page courante prête (ou non) pour la politique stricte
     *
     * @param boolean $booStrict true si la page ne contient aucun script en ligne sans nonce
     */
    public static function strict($booStrict = true)
    {
        self::$booStrict = (bool) $booStrict;
    }

    /**
     * Un module se déclare prêt pour la politique stricte par la constante
     * _<MODULE>_CSP (vraie) de son include/global.php, chargé avec le module
     *
     * @param string $strModuleType type de module
     * @return boolean
     */
    public static function moduleready($strModuleType)
    {
        if (!preg_match('/^[a-z0-9_]+$/i', $strModuleType)) return false;
        $strConstante = '_'.strtoupper($strModuleType).'_CSP';
        return defined($strConstante) && constant($strConstante) === true;
    }

    /**
     * La page courante est-elle déclarée prête ?
     *
     * @return boolean
     */
    public static function isstrict()
    {
        return self::$booStrict;
    }

    /**
     * Texte d'une politique
     *
     * @param string $strType 'base', 'strict' ou 'report'
     * @param boolean $booBackoffice politique du backoffice (sinon : frontoffice, flux, webservices)
     * @return string politique
     */
    public static function policy($strType, $booBackoffice = true)
    {
        $strNonce = "'nonce-".self::nonce()."'";
        $strCommun = "base-uri 'self'; frame-ancestors 'self'; report-uri "._PLOOPI_BASEPATH.'/index-quick.php?ploopi_op=ploopi_csp_report';

        switch($strType)
        {
            case 'strict':
                // 'unsafe-inline' n'est lu que par les navigateurs qui ignorent les nonces (CSP 1)
                return "script-src 'self' {$strNonce} 'unsafe-inline'; object-src 'none'; form-action 'self'; {$strCommun}";

            case 'report':
                return "default-src 'self'; script-src 'self' {$strNonce}; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; object-src 'none'; form-action 'self'; {$strCommun}";

            default:
                // Frontoffice : les gabarits et les articles des sites peuvent appeler des
                // services tiers (statistiques, lettres d'information, documents intégrés)
                if (!$booBackoffice) return "object-src 'self'; {$strCommun}";
                return "script-src 'self' 'unsafe-inline'; object-src 'none'; form-action 'self'; {$strCommun}";
        }
    }

    /**
     * Pose les entêtes de la requête : politique de base appliquée et politique cible
     * en Report-Only (la politique stricte, si la page est prête, remplace la première
     * en fin de requête : csp::finalize())
     *
     * @param boolean $booBackoffice requête du backoffice
     */
    public static function setheaders($booBackoffice = true)
    {
        if (headers_sent()) return;

        if (self::mode() != 'report') header('Content-Security-Policy: '.self::policy('base', $booBackoffice));
        header('Content-Security-Policy-Report-Only: '.self::policy('report', $booBackoffice));
    }

    /**
     * Ce qui, dans une page, s'exécuterait sans le nonce : gestionnaire d'évènement
     * en attribut, URL javascript:, script en ligne sans nonce (les blocs de données
     * JSON ne sont pas exécutés). Prudente : un texte qui ressemble à un gestionnaire,
     * ou une erreur d'analyse, compte comme du script (la page garde la politique de
     * base, rien ne casse).
     *
     * @param string $strHtml page
     * @return string premier extrait trouvé ('' si la page est propre)
     */
    public static function inlinescript($strHtml)
    {
        $funcExtrait = function($strTexte, $intPos) { return trim(preg_replace('/\s+/', ' ', substr($strTexte, max(0, $intPos - 40), 120))); };

        // Scripts en ligne : seuls ceux qui portent le nonce s'exécuteront
        if (preg_match_all('/<script\b([^>]*)>(.*?)<\/script\s*>/is', $strHtml, $arrScripts, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false) return 'analyse impossible';
        foreach($arrScripts as $arrScript)
        {
            $strAttrs = $arrScript[1][0];
            if (preg_match('/\bsrc\s*=/i', $strAttrs)) continue;
            if (preg_match('/\btype\s*=\s*["\']?application\/(?:ld\+)?json/i', $strAttrs)) continue;
            if (preg_match('/\bnonce\s*=\s*["\']?'.preg_quote(self::nonce(), '/').'["\'\s>]/i', $strAttrs.'>')) continue;
            if (trim($arrScript[2][0]) === '') continue;
            return $funcExtrait($strHtml, $arrScript[0][1] + 40);
        }

        // Le reste de la page, sans le contenu des scripts et des feuilles de style
        $strReste = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1\s*>/is', '<$1></$1>', $strHtml);
        if ($strReste === null) return 'analyse impossible';

        foreach(array('/\son[a-z]+\s*=/i', '/\s(?:href|src|action|formaction|data)\s*=\s*["\']?\s*javascript:/i') as $strMotif)
        {
            $intRes = preg_match($strMotif, $strReste, $arrMatches, PREG_OFFSET_CAPTURE);
            if ($intRes === false) return 'analyse impossible';
            if ($intRes) return $funcExtrait($strReste, $arrMatches[0][1]);
        }

        return '';
    }

    /**
     * Fin de requête (buffer::start()) : applique la politique stricte à une page
     * déclarée prête dont le HTML n'a aucun script en ligne sans nonce ; sinon la
     * page garde la politique de base, et l'écart est journalisé.
     *
     * @param string $strHtml page rendue
     */
    public static function finalize($strHtml)
    {
        if (!self::$booStrict || self::mode() != 'strict' || headers_sent()) return;

        $strExtrait = self::inlinescript($strHtml);

        if ($strExtrait === '') header('Content-Security-Policy: '.self::policy('strict'), true);
        else
        {
            $strMessage = 'CSP stricte non appliquée (script en ligne) sur '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?').' : '.$strExtrait;
            if (class_exists('ploopi\error', false)) error::syslog(LOG_INFO, $strMessage);
            if (defined('_PLOOPI_LOG_ERRORS') && _PLOOPI_LOG_ERRORS && defined('_PLOOPI_LOG_ERRORS_FILE') && _PLOOPI_LOG_ERRORS_FILE != '')
            {
                @file_put_contents(_PLOOPI_LOG_ERRORS_FILE, "=============================================\n".preg_replace('/[\x00-\x1F\x7F]+/', ' ', $strMessage)."\n", FILE_APPEND);
            }
        }
    }
}
