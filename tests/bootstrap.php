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
 * Socle commun de la suite de vérification des correctifs de sécurité.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

define('PLOOPI_TESTS_ROOT', __DIR__);
define('PLOOPI_ROOT', dirname(__DIR__));

/**
 * tests/config.php est FACULTATIF : il décrit l'instance à éprouver (URL, base,
 * compte d'administration). Les suites qui en ont besoin le réclament elles-mêmes
 * par exiger_instance() ou exiger_bdd() ; les suites purement unitaires s'exécutent
 * sans lui, sur une copie de travail fraîchement clonée.
 */
$GLOBALS['ploopi_tests_config'] = file_exists(PLOOPI_TESTS_ROOT.'/config.php') ? require PLOOPI_TESTS_ROOT.'/config.php' : array();
if (!is_array($GLOBALS['ploopi_tests_config'])) $GLOBALS['ploopi_tests_config'] = array();

/**
 * Indique si une configuration de test a été fournie.
 *
 * @return boolean
 */

function config_fournie()
{
    return !empty($GLOBALS['ploopi_tests_config']);
}

/**
 * Renvoie une clé de configuration de la suite
 *
 * @param string $strCle nom de la clé
 * @param mixed $mixDefaut valeur de repli
 * @return mixed
 */

function cfg($strCle, $mixDefaut = null)
{
    return isset($GLOBALS['ploopi_tests_config'][$strCle]) ? $GLOBALS['ploopi_tests_config'][$strCle] : $mixDefaut;
}

/**
 * Compteur et affichage des contrôles.
 */

class T
{
    public static $intOk = 0;
    public static $intKo = 0;
    public static $intSkip = 0;
    public static $strSection = '';
    public static $arrEchecs = array();
    public static $booCouleur = null;

    /**
     * Enregistre un contrôle pour les rapports (JUnit, HTML : tests/rapport.php), une
     * ligne JSON par contrôle dans le fichier que désigne PLOOPI_TESTS_CONTROLES
     * (tests/run.sh le renseigne). Sans cette variable, rien n'est écrit.
     */

    private static function enregistrer($strStatut, $strLabel, $strDetail)
    {
        $strFichier = getenv('PLOOPI_TESTS_CONTROLES');
        if (!$strFichier) return;

        $strLigne = json_encode(array(
            'suite' => basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php'),
            'section' => self::$strSection,
            'label' => (string)$strLabel,
            'statut' => $strStatut,
            'detail' => (string)$strDetail,
        ), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        file_put_contents($strFichier, $strLigne."\n", FILE_APPEND | LOCK_EX);
    }

    private static function couleur($strCode, $strTexte)
    {
        if (is_null(self::$booCouleur)) self::$booCouleur = (getenv('NO_COLOR') === false) && (PHP_SAPI === 'cli');

        return self::$booCouleur ? "\033[{$strCode}m{$strTexte}\033[0m" : $strTexte;
    }

    public static function section($strTitre)
    {
        self::$strSection = $strTitre;
        echo "\n".self::couleur('1', "── {$strTitre}")."\n";
    }

    /**
     * Enregistre le résultat d'un contrôle
     *
     * @param string $strLabel intitulé
     * @param boolean $booCondition résultat attendu vrai
     * @param string $strDetail complément affiché en fin de ligne
     * @return boolean
     */

    public static function chk($strLabel, $booCondition, $strDetail = '')
    {
        if ($booCondition)
        {
            self::$intOk++;
            self::enregistrer('ok', $strLabel, $strDetail);
            printf("  %s %-62s %s\n", self::couleur('32', ' OK '), $strLabel, $strDetail);
        }
        else
        {
            self::$intKo++;
            self::$arrEchecs[] = self::$strSection.' › '.$strLabel;
            self::enregistrer('ko', $strLabel, $strDetail);
            printf("  %s %-62s %s\n", self::couleur('31', ' KO '), $strLabel, $strDetail);
        }

        return (bool)$booCondition;
    }

    /**
     * Assertion booléenne dont le détail n'est affiché QU'EN CAS D'ÉCHEC.
     *
     * chk() affiche son détail dans les deux cas, ce qui convient pour annoter un
     * succès (« (IV aléatoire) »). Quand le détail est un diagnostic d'échec — la
     * liste des fichiers fautifs, par exemple — il n'a rien à faire sur une ligne OK.
     *
     * @param string $strLabel intitulé
     * @param mixed $mixCondition condition, vraie si le contrôle passe
     * @param string $strDetailEchec diagnostic affiché seulement si le contrôle échoue
     * @return boolean
     */

    public static function vrai($strLabel, $mixCondition, $strDetailEchec = '')
    {
        return self::chk($strLabel, $mixCondition, $mixCondition ? '' : $strDetailEchec);
    }

    /**
     * Assertion d'égalité stricte. Le détail affiché en cas d'échec montre
     * l'attendu et l'obtenu, ce que chk() seul ne peut pas faire.
     *
     * @param string $strLabel intitulé
     * @param mixed $mixAttendu valeur attendue
     * @param mixed $mixObtenu valeur obtenue
     * @return boolean
     */

    public static function eq($strLabel, $mixAttendu, $mixObtenu)
    {
        return self::chk($strLabel, $mixAttendu === $mixObtenu, $mixAttendu === $mixObtenu ? '' : 'attendu '.self::dump($mixAttendu).', obtenu '.self::dump($mixObtenu));
    }

    /**
     * Assertion de différence stricte.
     *
     * @param string $strLabel intitulé
     * @param mixed $mixRefuse valeur refusée
     * @param mixed $mixObtenu valeur obtenue
     * @return boolean
     */

    public static function neq($strLabel, $mixRefuse, $mixObtenu)
    {
        return self::chk($strLabel, $mixRefuse !== $mixObtenu, $mixRefuse !== $mixObtenu ? '' : 'valeur refusée obtenue : '.self::dump($mixObtenu));
    }

    /**
     * Assertion de présence d'une sous-chaîne.
     *
     * @param string $strLabel intitulé
     * @param string $strAiguille sous-chaîne attendue
     * @param string $strMeule chaîne analysée
     * @return boolean
     */

    public static function contient($strLabel, $strAiguille, $strMeule)
    {
        $booOk = is_string($strMeule) && strpos($strMeule, $strAiguille) !== false;

        return self::chk($strLabel, $booOk, $booOk ? '' : self::dump($strAiguille).' absent de '.self::dump($strMeule));
    }

    /**
     * Assertion d'absence d'une sous-chaîne.
     *
     * @param string $strLabel intitulé
     * @param string $strAiguille sous-chaîne refusée
     * @param string $strMeule chaîne analysée
     * @return boolean
     */

    public static function contientpas($strLabel, $strAiguille, $strMeule)
    {
        $booOk = is_string($strMeule) && strpos($strMeule, $strAiguille) === false;

        return self::chk($strLabel, $booOk, $booOk ? '' : self::dump($strAiguille).' présent dans '.self::dump($strMeule));
    }

    /**
     * Représentation courte d'une valeur, pour les messages d'échec.
     *
     * @param mixed $mixVar valeur
     * @return string
     */

    public static function dump($mixVar)
    {
        if (is_bool($mixVar)) return $mixVar ? 'true' : 'false';
        if (is_null($mixVar)) return 'null';
        if (is_string($mixVar)) return "'".(strlen($mixVar) > 90 ? substr($mixVar, 0, 90).'…' : $mixVar)."'";
        if (is_array($mixVar)) return 'array('.sizeof($mixVar).')'.json_encode(array_slice($mixVar, 0, 8));
        if (is_object($mixVar)) return 'object('.get_class($mixVar).')';

        return (string)$mixVar;
    }

    /**
     * Signale un contrôle non évalué, faute d'un prérequis d'environnement.
     * Un contrôle sauté n'est pas un échec, mais il est compté et rappelé au bilan.
     */

    public static function skip($strLabel, $strRaison = '')
    {
        self::$intSkip++;
        self::enregistrer('skip', $strLabel, $strRaison);
        printf("  %s %-62s %s\n", self::couleur('33', 'SKIP'), $strLabel, $strRaison);
    }

    /**
     * Affiche le bilan et renvoie le code de sortie du script
     *
     * @return int 0 si aucun échec
     */

    public static function bilan($strSuite = '')
    {
        if ($strSuite !== '') echo "\n".self::couleur('1', $strSuite)."\n";

        printf("\n%s\n", self::couleur('1', sprintf("═══ %d OK / %d KO%s ═══",
            self::$intOk, self::$intKo, self::$intSkip ? ' / '.self::$intSkip.' non évalué(s)' : '')));

        if (self::$arrEchecs)
        {
            echo "\nÉchecs :\n";
            foreach (self::$arrEchecs as $strEchec) echo "  - {$strEchec}\n";
        }

        return self::$intKo === 0 ? 0 : 1;
    }
}

/**
 * Client HTTP conservant les cookies d'une requête à l'autre.
 */

class H
{
    private static $strJar = null;

    public static function jar()
    {
        if (is_null(self::$strJar)) self::$strJar = sys_get_temp_dir().'/ploopi_tests_cookies_'.getmypid().'.txt';

        return self::$strJar;
    }

    public static function reset() { @unlink(self::jar()); }

    /**
     * Exécute une requête HTTP
     *
     * @param string $strUrl absolue, ou relative à base_url
     * @param mixed $mixPost corps POST, null pour un GET
     * @param array $arrEntetes entêtes supplémentaires
     * @return array code, headers, body
     */

    public static function req($strUrl, $mixPost = null, $arrEntetes = array())
    {
        $rscCurl = curl_init(strpos($strUrl, 'http') === 0 ? $strUrl : cfg('base_url').$strUrl);

        curl_setopt_array($rscCurl, array(
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_HEADER          => 1,
            CURLOPT_COOKIEJAR       => self::jar(),
            CURLOPT_COOKIEFILE      => self::jar(),
            CURLOPT_FOLLOWLOCATION  => 0,
            CURLOPT_TIMEOUT         => 30
        ));

        // Un entête « Cookie: » remplace les cookies du bocal (session) avec libcurl 8.14
        // et suivants : il passe par CURLOPT_COOKIE, qui s'y ajoute avec toute version
        foreach ($arrEntetes as $intCle => $strEntete)
        {
            if (stripos($strEntete, 'Cookie:') !== 0) continue;
            curl_setopt($rscCurl, CURLOPT_COOKIE, trim(substr($strEntete, 7)));
            unset($arrEntetes[$intCle]);
        }

        if ($arrEntetes) curl_setopt($rscCurl, CURLOPT_HTTPHEADER, array_values($arrEntetes));

        if (!is_null($mixPost))
        {
            curl_setopt($rscCurl, CURLOPT_POST, 1);
            curl_setopt($rscCurl, CURLOPT_POSTFIELDS, $mixPost);
        }

        $strReponse = curl_exec($rscCurl);
        $intTailleEntetes = curl_getinfo($rscCurl, CURLINFO_HEADER_SIZE);
        $intCode = curl_getinfo($rscCurl, CURLINFO_HTTP_CODE);

        return array(
            'code'      => $intCode,
            'headers'   => substr($strReponse, 0, $intTailleEntetes),
            'body'      => substr($strReponse, $intTailleEntetes)
        );
    }

    /** Identifiant de session lu dans le bocal à cookies (format Netscape, tabulations) */
    public static function sid()
    {
        /**
         * Alphabet selon session.sid_bits_per_character : 4 (hexadécimal, défaut en PHP
         * 8.4), 5 ([0-9a-v], défaut antérieur) ou 6 ([0-9a-zA-Z,-]). Restreint à
         * l'hexadécimal, l'identifiant lu sous PHP 8.3 était tronqué, voire vide.
         */
        preg_match('/PHPSESSID\s+([0-9a-zA-Z,-]+)/', @file_get_contents(self::jar()) ?: '', $arrMatches);

        return isset($arrMatches[1]) ? $arrMatches[1] : '';
    }

    /** Connecte le compte administrateur et renvoie la réponse */
    public static function connexion()
    {
        return self::req('/admin.php', 'ploopi_login='.urlencode(cfg('admin_login')).'&ploopi_password='.urlencode(cfg('admin_password')));
    }

    /** Renvoie le ploopi_env (donc le jeton) de la page courante du backoffice */
    public static function env()
    {
        $arrReponse = self::req('/admin.php');
        preg_match("/_PLOOPI_ENV *= *'([^']*)'/", $arrReponse['body'], $arrMatches);

        return isset($arrMatches[1]) ? $arrMatches[1] : '';
    }
}

/**
 * Connexion mysqli à la base de l'instance éprouvée (singleton)
 *
 * @return mysqli
 */

function db()
{
    static $objDb = null;

    if (is_null($objDb))
    {
        if (!config_fournie())
        {
            fwrite(STDERR, "tests/config.php est absent : aucune base à éprouver.\n");
            exit(2);
        }

        try
        {
            $objDb = @new mysqli(cfg('db_host'), cfg('db_user'), cfg('db_pass'), cfg('db_name'), 3306);
        }
        catch (Throwable $objErreur)
        {
            fwrite(STDERR, 'Connexion à la base impossible : '.$objErreur->getMessage()."\n");
            exit(2);
        }

        if ($objDb->connect_errno)
        {
            fwrite(STDERR, "Connexion à la base impossible : {$objDb->connect_error}\n");
            exit(2);
        }

        $objDb->set_charset('utf8');
    }

    return $objDb;
}

/**
 * Le fichier ou le dossier fait-il partie du dépôt ? Les suites qui parcourent le disque
 * n'examinent que ce que git suit (fichiers commités ou ajoutés par « git add ») : une
 * copie de travail porte souvent des restes que le dépôt n'a plus, ou n'a jamais eus
 * (anciens paquets dans install/, copies déployées dans modules/, bibliothèques
 * retirées), et ce n'est pas le code livré. Sans git (archive du dépôt, git absent),
 * tout est examiné.
 *
 * @param string $strChemin chemin absolu, ou relatif à la racine du dépôt
 * @return boolean vrai si suivi (fichier) ou s'il contient un fichier suivi (dossier)
 */

function suivi($strChemin)
{
    static $arrSuivis = null;

    if (is_null($arrSuivis))
    {
        $arrSuivis = false;
        // safe.directory : dépôt d'un autre utilisateur (suite lancée en root, conteneur)
        exec('git -c safe.directory='.escapeshellarg(PLOOPI_ROOT).' -C '.escapeshellarg(PLOOPI_ROOT).' ls-files -z 2>/dev/null', $arrSortie, $intRc);
        if ($intRc === 0 && !empty($arrSortie))
        {
            $arrSuivis = array();
            foreach (explode("\0", implode("\n", $arrSortie)) as $strFichier)
            {
                // Le fichier et chacun de ses dossiers parents
                for ($strPartie = $strFichier; $strPartie !== '' && $strPartie !== '.'; $strPartie = dirname($strPartie)) $arrSuivis[$strPartie] = true;
            }
        }
    }

    if ($arrSuivis === false) return true;

    $strRelatif = strpos($strChemin, PLOOPI_ROOT.'/') === 0 ? substr($strChemin, strlen(PLOOPI_ROOT) + 1) : $strChemin;
    return isset($arrSuivis[rtrim($strRelatif, '/')]);
}

/**
 * Ne garde d'une liste de chemins (glob…) que ceux du dépôt (cf. suivi()).
 *
 * @param array|false $arrChemins chemins
 * @return array chemins suivis, clés renumérotées
 */

function suivis($arrChemins)
{
    return array_values(array_filter((array)$arrChemins, 'suivi'));
}

/**
 * Amorce le socle Ploopi en mode CLI, afin d'éprouver les classes réelles.
 */

function amorcer_socle()
{
    static $booFait = false;

    if ($booFait) return;

    if (!defined('_PLOOPI_DIRNAME')) define('_PLOOPI_DIRNAME', PLOOPI_ROOT);

    chdir(PLOOPI_ROOT);

    // loader::boot_cli() s'arrête net sans config/config.php. Sur une copie de
    // travail non installée, on amorce donc à la main : même autoload, mêmes
    // constantes, et une configuration de test minimale à la place de l'instance.
    if (file_exists(PLOOPI_ROOT.'/config/config.php'))
    {
        include_once PLOOPI_ROOT.'/include/classes/loader.php';
        ploopi\loader::boot_cli();
    }
    else
    {
        if (empty($_SERVER['SCRIPT_FILENAME'])) $_SERVER['SCRIPT_FILENAME'] = PLOOPI_ROOT.'/index.php';

        include_once PLOOPI_ROOT.'/include/classes/loader.php';

        // Même règle de nommage que loader::_classToFile(), dont l'autoload est privé.
        spl_autoload_register(function($strClassName)
        {
            if (strpos($strClassName, 'ploopi\\') !== 0) return false;

            $arrPath = explode('\\', $strClassName);
            $strFile = sizeof($arrPath) == 2
                ? PLOOPI_ROOT.'/include/classes/'.$arrPath[1].'.php'
                : PLOOPI_ROOT.'/modules/'.$arrPath[1].'/classes/'.implode('/', array_slice($arrPath, 2)).'.php';

            if (file_exists($strFile)) include_once $strFile;

            return true;
        });

        if (file_exists(PLOOPI_ROOT.'/vendor/autoload.php')) include_once PLOOPI_ROOT.'/vendor/autoload.php';

        definir_config_tests();

        include_once PLOOPI_ROOT.'/include/constants.php';
    }

    $booFait = true;
}

/**
 * Définit une configuration minimale, reprenant les valeurs par défaut de
 * config/config.php.model, quand aucune instance n'est installée. Les paramètres
 * de connexion éventuels sont repris de tests/config.php.
 *
 * @return boolean true
 */

function definir_config_tests()
{
    $arrDefauts = array(
        '_PLOOPI_SQL_LAYER'                   => 'mysqli',
        '_PLOOPI_DB_SERVER'                   => cfg('db_host', 'localhost'),
        '_PLOOPI_DB_LOGIN'                    => cfg('db_user', ''),
        '_PLOOPI_DB_PASSWORD'                 => cfg('db_pass', ''),
        '_PLOOPI_DB_DATABASE'                 => cfg('db_name', ''),
        '_PLOOPI_PATHDATA'                    => sys_get_temp_dir().'/ploopi_tests_data',
        '_PLOOPI_USE_CACHE'                   => false,
        '_PLOOPI_MAXFILESIZE'                 => '16777216',
        '_PLOOPI_SESSIONTIME'                 => '3600',
        '_PLOOPI_SESSION_HANDLER'             => 'php',
        '_PLOOPI_SESSION_COMPRESSION'         => 1,
        '_PLOOPI_DISPLAY_ERRORS'              => false,
        '_PLOOPI_ERROR_REPORTING'             => E_ALL,
        '_PLOOPI_LOG_ERRORS'                  => false,
        '_PLOOPI_MAIL_ERRORS'                 => false,
        '_PLOOPI_SYSMAIL'                     => 'tests@localhost',
        '_PLOOPI_ADMINMAIL'                   => 'tests@localhost',
        '_PLOOPI_ACTIVELOG'                   => false,
        '_PLOOPI_FILTER_VARS'                 => true,
        '_PLOOPI_URL_ENCODE'                  => true,
        '_PLOOPI_CIPHER'                      => 'aes-256-cbc',
        '_PLOOPI_CIPHER_IV'                   => '0123456789abcdef',
        // bcrypt est le seul algorithme proposé par l'assistant d'installation
        // (cf. suite 03_sources) : la configuration de test reprend ce choix.
        '_PLOOPI_HASH_ALGO'                   => 'bcrypt',
        '_PLOOPI_HASH_ALGO_PREVIOUS'          => '',
        '_PLOOPI_SECRETKEY'                   => 'cle_secrete_de_test_ploopi',
        '_PLOOPI_TOKEN'                       => true,
        '_PLOOPI_TOKENMAX'                    => 200,
        '_PLOOPI_FRONTOFFICE'                 => true,
        '_PLOOPI_FRONTOFFICE_REWRITERULE'     => true,
        '_PLOOPI_DEFAULT_TEMPLATE'            => 'ploopi4',
        '_PLOOPI_USE_COMPLEXE_PASSWORD'       => false,
        '_PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE'  => 8,
        '_PLOOPI_MAX_CONNECTION_ATTEMPS'      => 3,
        '_PLOOPI_JAILING_TIME'                => 600,
        '_PLOOPI_USE_OUTPUT_COMPRESSION'      => false,
        '_PLOOPI_INTERNETPROXY_HOST'          => '',
        '_PLOOPI_INTERNETPROXY_PORT'          => '',
        '_PLOOPI_INTERNETPROXY_USER'          => '',
        '_PLOOPI_INTERNETPROXY_PASS'          => '',
        '_PLOOPI_INDEXATION_WORDSEPARATORS'   => " :;,.!?'^`'\"«»~-_|()[]{}<>\$£µ&#§@%=+/*\\/\n\r",
        '_PLOOPI_INDEXATION_WORDMINLENGHT'    => 2,
        '_PLOOPI_INDEXATION_WORDMAXLENGHT'    => 50,
        '_PLOOPI_INDEXATION_COMMONWORDS_FR'   => './config/commonwords_fr.txt',
        '_PLOOPI_INDEXATION_RATIOMIN'         => 0.01,
        '_PLOOPI_INDEXATION_KEYWORDSMAXPCENT' => 100,
        '_PLOOPI_LOAD_NBCORE'                 => 1,
        '_PLOOPI_S3_ACTIVATED'                => false
    );

    foreach($arrDefauts as $strNom => $mixValeur) if (!defined($strNom)) define($strNom, $mixValeur);

    if (!defined('_PLOOPI_TOKENTIME')) define('_PLOOPI_TOKENTIME', _PLOOPI_SESSIONTIME);

    return true;
}

/**
 * Chemin du fichier de session du serveur web, ou false s'il est introuvable.
 *
 * @param string $strSid identifiant de session
 * @return string|false
 */

function chemin_session($strSid)
{
    if ($strSid === '') return false;

    $arrCandidats = array_filter(array(cfg('session_path'), session_save_path(), ini_get('session.save_path'), '/var/lib/php/sessions', sys_get_temp_dir()));

    foreach ($arrCandidats as $strDossier)
    {
        $strChemin = rtrim($strDossier, '/').'/sess_'.$strSid;
        if (file_exists($strChemin) && is_readable($strChemin)) return $strChemin;
    }

    return false;
}

/**
 * Interrompt la suite si l'instance ne répond pas.
 * Sans cette garde, les contrôles de la forme « rien ne s'est produit » passeraient à tort.
 */

function exiger_instance()
{
    /**
     * Sans tests/config.php, aucune instance n'est annoncée : la suite est déclarée
     * non évaluée, pas en échec. En revanche, si une instance EST configurée et ne
     * répond pas, c'est une anomalie et la suite s'arrête bruyamment — sans quoi les
     * contrôles de la forme « rien ne s'est produit » passeraient à tort.
     */
    if (!config_fournie())
    {
        T::skip('suite entière', 'aucune instance configurée (voir tests/config.php.model)');
        exit(T::bilan());
    }

    $arrReponse = H::req('/admin.php');

    if ($arrReponse['code'] === 0)
    {
        fwrite(STDERR, 'Instance injoignable sur '.cfg('base_url').", suite interrompue.\n");
        exit(2);
    }
}

/**
 * Indique si une base de données est joignable pour les suites qui en ont besoin.
 * Le contrôle passe par une connexion mysqli directe : db() et db::get() s'arrêtent
 * net en cas d'échec, ce qui interdirait d'ignorer proprement la suite.
 *
 * @return boolean
 */

function bdd_disponible()
{
    static $booDisponible = null;

    if (is_null($booDisponible))
    {
        amorcer_socle();

        if (!defined('_PLOOPI_DB_DATABASE') || _PLOOPI_DB_DATABASE === '') return $booDisponible = false;

        /**
         * Depuis PHP 8.1, mysqli signale ses erreurs par une exception : l'opérateur @
         * ne suffit pas et un serveur éteint ferait échouer la fonction censée dire
         * s'il répond.
         */
        try
        {
            $objMysqli = @new mysqli(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
            $booDisponible = !$objMysqli->connect_errno;
            if ($booDisponible) $objMysqli->close();
        }
        catch (Throwable $objErreur)
        {
            $booDisponible = false;
        }
    }

    return $booDisponible;
}

/**
 * Interrompt la suite, sans échec, si aucune base n'est joignable.
 */

function exiger_bdd()
{
    if (bdd_disponible()) return;

    T::skip('suite entière', 'aucune base de données joignable (voir tests/config.php.model)');
    exit(T::bilan());
}

/**
 * Préfixe des tables temporaires créées par les tests.
 */

define('PLOOPI_TESTS_PREFIXE', 'ploopi_tests_');

/**
 * Courriels envoyés par l'instance de test depuis l'appel à courriels_vider().
 *
 * L'instance remplace sendmail par tests/sendmail.sh, qui ajoute chaque message à un
 * fichier (clé « courriels » de tests/config.php).
 *
 * @return array messages bruts (entêtes et corps), dans l'ordre d'envoi
 */

function courriels()
{
    $strFichier = cfg('courriels');
    if (empty($strFichier) || !file_exists($strFichier)) return array();

    clearstatcache();
    return array_values(array_filter(array_map('trim', explode('@@COURRIEL@@', (string)file_get_contents($strFichier)))));
}

/**
 * Oublie les courriels déjà envoyés.
 */

function courriels_vider()
{
    $strFichier = cfg('courriels');
    if (!empty($strFichier) && file_exists($strFichier)) file_put_contents($strFichier, '');
}
