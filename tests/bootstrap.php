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
 * Amorçage des tests : micro-harnais d'assertions + chargement du socle Ploopi
 * hors contexte web (pas de session, pas de buffer, pas de template).
 *
 * Le dépôt n'embarque volontairement aucune dépendance de test (cf. CLAUDE.md §14 :
 * « ne pas introduire de dépendance Composer sans nécessité forte »). Ce fichier
 * fournit donc le strict nécessaire : un compteur d'assertions et quelques
 * raccourcis de comparaison.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Racine du dépôt (le dossier parent de tests/).
 */
define('_PLOOPI_TESTS_ROOT', dirname(__DIR__));

/**
 * Compteur et affichage des assertions.
 */
abstract class T
{
    /** @var int nombre d'assertions réussies */
    public static $intOk = 0;
    /** @var int nombre d'assertions échouées */
    public static $intKo = 0;
    /** @var int nombre de contrôles ignorés (prérequis absent) */
    public static $intSkip = 0;
    /** @var string section courante */
    private static $strSection = '';

    /**
     * Ouvre une section de tests (purement cosmétique).
     *
     * @param string $strLabel libellé de la section
     */
    public static function section($strLabel)
    {
        self::$strSection = $strLabel;
        echo "\n### {$strLabel}\n";
    }

    /**
     * Assertion booléenne.
     *
     * @param string $strLabel libellé du contrôle
     * @param mixed $mixCondition condition, vraie si le contrôle passe
     * @param string $strDetail détail affiché en cas d'échec
     * @return boolean true si le contrôle passe
     */
    public static function chk($strLabel, $mixCondition, $strDetail = '')
    {
        if ($mixCondition)
        {
            self::$intOk++;
            echo "  ok   {$strLabel}\n";
            return true;
        }

        self::$intKo++;
        echo "  KO   {$strLabel}".($strDetail === '' ? '' : " -- {$strDetail}")."\n";
        return false;
    }

    /**
     * Assertion d'égalité stricte.
     *
     * @param string $strLabel libellé du contrôle
     * @param mixed $mixAttendu valeur attendue
     * @param mixed $mixObtenu valeur obtenue
     * @return boolean true si le contrôle passe
     */
    public static function eq($strLabel, $mixAttendu, $mixObtenu)
    {
        return self::chk(
            $strLabel,
            $mixAttendu === $mixObtenu,
            'attendu '.self::dump($mixAttendu).', obtenu '.self::dump($mixObtenu)
        );
    }

    /**
     * Assertion de différence stricte.
     *
     * @param string $strLabel libellé du contrôle
     * @param mixed $mixRefuse valeur refusée
     * @param mixed $mixObtenu valeur obtenue
     * @return boolean true si le contrôle passe
     */
    public static function neq($strLabel, $mixRefuse, $mixObtenu)
    {
        return self::chk($strLabel, $mixRefuse !== $mixObtenu, 'valeur refusée obtenue : '.self::dump($mixObtenu));
    }

    /**
     * Assertion de présence d'une sous-chaîne.
     *
     * @param string $strLabel libellé du contrôle
     * @param string $strAiguille sous-chaîne attendue
     * @param string $strMeule chaîne analysée
     * @return boolean true si le contrôle passe
     */
    public static function contient($strLabel, $strAiguille, $strMeule)
    {
        return self::chk(
            $strLabel,
            is_string($strMeule) && strpos($strMeule, $strAiguille) !== false,
            self::dump($strAiguille).' absent de '.self::dump($strMeule)
        );
    }

    /**
     * Assertion d'absence d'une sous-chaîne.
     *
     * @param string $strLabel libellé du contrôle
     * @param string $strAiguille sous-chaîne refusée
     * @param string $strMeule chaîne analysée
     * @return boolean true si le contrôle passe
     */
    public static function contientpas($strLabel, $strAiguille, $strMeule)
    {
        return self::chk(
            $strLabel,
            is_string($strMeule) && strpos($strMeule, $strAiguille) === false,
            self::dump($strAiguille).' présent dans '.self::dump($strMeule)
        );
    }

    /**
     * Contrôle ignoré faute de prérequis (base de données, vendor/, ...).
     * Un skip n'est jamais un succès : il est compté à part et affiché.
     *
     * @param string $strLabel libellé du contrôle
     * @param string $strRaison raison de l'omission
     */
    public static function skip($strLabel, $strRaison)
    {
        self::$intSkip++;
        echo "  --   {$strLabel} (ignoré : {$strRaison})\n";
    }

    /**
     * Représentation courte d'une valeur pour les messages d'échec.
     *
     * @param mixed $mixVar valeur
     * @return string représentation
     */
    public static function dump($mixVar)
    {
        if (is_bool($mixVar)) return $mixVar ? 'true' : 'false';
        if (is_null($mixVar)) return 'null';
        if (is_string($mixVar)) return "'".(strlen($mixVar) > 120 ? substr($mixVar, 0, 120).'…' : $mixVar)."'";
        if (is_array($mixVar)) return 'array('.sizeof($mixVar).')'.json_encode(array_slice($mixVar, 0, 10));
        if (is_object($mixVar)) return 'object('.get_class($mixVar).')';
        return (string)$mixVar;
    }

    /**
     * Bilan de la suite et code de sortie (0 si tout passe, 1 sinon).
     *
     * @param string $strSuite nom de la suite
     */
    public static function bilan($strSuite)
    {
        echo "\n{$strSuite} : ".self::$intOk." OK, ".self::$intKo." KO";
        if (self::$intSkip) echo ", ".self::$intSkip." ignoré(s)";
        echo "\n";
        exit(self::$intKo > 0 ? 1 : 0);
    }
}

/**
 * Charge le socle Ploopi (autoload + constantes) sans contexte web.
 * Idempotent.
 *
 * @return boolean true
 */
function amorcer_socle()
{
    static $booCharge = false;
    if ($booCharge) return true;

    chdir(_PLOOPI_TESTS_ROOT);

    // Certaines classes du socle lisent $_SERVER['SCRIPT_FILENAME'] (cf. _PLOOPI_FINGERPRINT).
    if (empty($_SERVER['SCRIPT_FILENAME'])) $_SERVER['SCRIPT_FILENAME'] = _PLOOPI_TESTS_ROOT.'/index.php';

    include_once _PLOOPI_TESTS_ROOT.'/include/classes/loader.php';

    // L'autoload du socle est privé : on applique ici la même règle de nommage
    // (cf. loader::_classToFile(), CLAUDE.md §7.1).
    spl_autoload_register(function($strClassName)
    {
        if (strpos($strClassName, 'ploopi\\') !== 0) return false;

        $arrPath = explode('\\', $strClassName);

        if (sizeof($arrPath) == 2) $strFile = _PLOOPI_TESTS_ROOT.'/include/classes/'.$arrPath[1].'.php';
        else $strFile = _PLOOPI_TESTS_ROOT.'/modules/'.$arrPath[1].'/classes/'.implode('/', array_slice($arrPath, 2)).'.php';

        if (file_exists($strFile)) include_once $strFile;

        return true;
    });

    if (file_exists(_PLOOPI_TESTS_ROOT.'/vendor/autoload.php')) include_once _PLOOPI_TESTS_ROOT.'/vendor/autoload.php';

    // Le socle lit une configuration : celle de l'instance si elle existe, sinon
    // une configuration de test minimale (permet de lancer les tests unitaires
    // sur une copie de travail fraîchement clonée).
    if (file_exists(_PLOOPI_TESTS_ROOT.'/config/config.php')) include_once _PLOOPI_TESTS_ROOT.'/config/config.php';
    else definir_config_tests();

    include_once _PLOOPI_TESTS_ROOT.'/include/constants.php';

    $booCharge = true;
    return true;
}

/**
 * Indique si les dépendances Composer sont installées.
 *
 * @return boolean true si vendor/autoload.php existe
 */
function vendor_disponible()
{
    return file_exists(_PLOOPI_TESTS_ROOT.'/vendor/autoload.php');
}

/**
 * Lit la configuration de test (tests/config.php, modèle dans tests/config.php.model).
 *
 * @return array|false configuration ou false si absente
 */
function config_tests()
{
    static $mixConfig = null;

    if (is_null($mixConfig))
    {
        $strFichier = __DIR__.'/config.php';
        $mixConfig = file_exists($strFichier) ? include $strFichier : false;
        if (!is_array($mixConfig)) $mixConfig = false;
    }

    return $mixConfig;
}

/**
 * Définit une configuration de test minimale, reprenant les valeurs par défaut de
 * config/config.php.model. Les paramètres de connexion éventuels sont lus dans
 * tests/config.php (modèle : tests/config.php.model).
 *
 * @return boolean true
 */
function definir_config_tests()
{
    $arrConfig = config_tests();

    $arrDefauts = array(
        '_PLOOPI_SQL_LAYER'                 => 'mysqli',
        '_PLOOPI_DB_SERVER'                 => isset($arrConfig['db_server'])   ? $arrConfig['db_server']   : 'localhost',
        '_PLOOPI_DB_LOGIN'                  => isset($arrConfig['db_login'])    ? $arrConfig['db_login']    : '',
        '_PLOOPI_DB_PASSWORD'               => isset($arrConfig['db_password']) ? $arrConfig['db_password'] : '',
        '_PLOOPI_DB_DATABASE'               => isset($arrConfig['db_database']) ? $arrConfig['db_database'] : '',
        '_PLOOPI_PATHDATA'                  => sys_get_temp_dir().'/ploopi_tests_data',
        '_PLOOPI_USE_CACHE'                 => false,
        '_PLOOPI_MAXFILESIZE'               => '16777216',
        '_PLOOPI_SESSIONTIME'               => '3600',
        '_PLOOPI_SESSION_HANDLER'           => 'php',
        '_PLOOPI_SESSION_COMPRESSION'       => 1,
        '_PLOOPI_DISPLAY_ERRORS'            => false,
        '_PLOOPI_ERROR_REPORTING'           => E_ALL,
        '_PLOOPI_LOG_ERRORS'                => false,
        '_PLOOPI_MAIL_ERRORS'               => false,
        '_PLOOPI_SYSMAIL'                   => 'tests@localhost',
        '_PLOOPI_ADMINMAIL'                 => 'tests@localhost',
        '_PLOOPI_ACTIVELOG'                 => false,
        '_PLOOPI_FILTER_VARS'               => true,
        '_PLOOPI_URL_ENCODE'                => true,
        '_PLOOPI_CIPHER'                    => 'aes-256-cbc',
        '_PLOOPI_CIPHER_IV'                 => '0123456789abcdef',
        '_PLOOPI_HASH_ALGO'                 => 'sha256',
        '_PLOOPI_HASH_ALGO_PREVIOUS'        => '',
        '_PLOOPI_SECRETKEY'                 => 'cle_secrete_de_test_ploopi',
        '_PLOOPI_TOKEN'                     => true,
        '_PLOOPI_TOKENMAX'                  => 200,
        '_PLOOPI_FRONTOFFICE'               => true,
        '_PLOOPI_FRONTOFFICE_REWRITERULE'   => true,
        '_PLOOPI_DEFAULT_TEMPLATE'          => 'ploopi2',
        '_PLOOPI_USE_COMPLEXE_PASSWORD'     => false,
        '_PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE'=> 8,
        '_PLOOPI_MAX_CONNECTION_ATTEMPS'    => 3,
        '_PLOOPI_JAILING_TIME'              => 600,
        '_PLOOPI_USE_OUTPUT_COMPRESSION'    => false,
        '_PLOOPI_INTERNETPROXY_HOST'        => '',
        '_PLOOPI_INTERNETPROXY_PORT'        => '',
        '_PLOOPI_INTERNETPROXY_USER'        => '',
        '_PLOOPI_INTERNETPROXY_PASS'        => '',
        '_PLOOPI_INDEXATION_WORDSEPARATORS' => " :;,.!?'^`'\"«»~-_|()[]{}<>\$£µ&#§@%=+/*\\/\n\r",
        '_PLOOPI_INDEXATION_WORDMINLENGHT'  => 2,
        '_PLOOPI_INDEXATION_WORDMAXLENGHT'  => 50,
        '_PLOOPI_INDEXATION_COMMONWORDS_FR' => './config/commonwords_fr.txt',
        '_PLOOPI_INDEXATION_RATIOMIN'       => 0.01,
        '_PLOOPI_INDEXATION_KEYWORDSMAXPCENT' => 100,
        '_PLOOPI_LOAD_NBCORE'               => 1,
        '_PLOOPI_S3_ACTIVATED'              => false
    );

    foreach($arrDefauts as $strNom => $mixValeur) if (!defined($strNom)) define($strNom, $mixValeur);

    if (!defined('_PLOOPI_TOKENTIME')) define('_PLOOPI_TOKENTIME', _PLOOPI_SESSIONTIME);

    return true;
}

/**
 * Indique si la base de données configurée est joignable.
 * Le contrôle est fait par une connexion mysqli directe : db::get() déclenche une
 * erreur fatale quand la connexion échoue, ce qui interdirait d'ignorer proprement
 * les suites concernées.
 *
 * @return boolean true si la base répond
 */
function bdd_disponible()
{
    static $booDisponible = null;

    if (is_null($booDisponible))
    {
        amorcer_socle();

        if (!defined('_PLOOPI_DB_DATABASE') || _PLOOPI_DB_DATABASE === '') return $booDisponible = false;

        $objMysqli = @new mysqli(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
        $booDisponible = !$objMysqli->connect_errno;
        if ($booDisponible) $objMysqli->close();
    }

    return $booDisponible;
}

/**
 * Interrompt la suite courante, sans échec, si aucune base n'est disponible.
 *
 * @param string $strSuite nom de la suite
 */
function exiger_bdd($strSuite)
{
    if (bdd_disponible()) return;

    T::skip('suite entière', 'aucune base de données joignable (voir tests/config.php.model)');
    T::bilan($strSuite);
}

/**
 * Préfixe des tables temporaires créées par les tests.
 */
define('_PLOOPI_TESTS_PREFIXE', 'ploopi_tests_');
