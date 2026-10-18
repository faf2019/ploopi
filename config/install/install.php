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

include_once './include/constants.php';

/**
 * Depuis PHP 8.1, mysqli lève une exception à chaque échec : un identifiant erroné
 * faisait planter l'assistant au lieu de marquer le champ en erreur. Les contrôles
 * ci-dessous testent connect_errno, comme ploopi\db.
 */
mysqli_report(MYSQLI_REPORT_OFF);

session_start();

ploopi\buffer::clean();

clearstatcache();

/**
 * Adresse du site telle que l'administrateur l'atteint pour l'installer : la valeur
 * proposée pour _PLOOPI_PUBLIC_URL. À ce stade, l'entête Host est celui de
 * l'administrateur lui-même.
 *
 * @return string schéma, hôte et chemin, sans « / » final
 */
function installer_publicurl() {
    $booSsl = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https');
    $strHote = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
    return ($booSsl ? 'https' : 'http').'://'.$strHote.rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
}

function field_control($field, $value) {
    switch($field) {
        case 'DATAPATH':
            $rp = realpath($value);
            if (file_exists($rp) && is_dir($rp) && is_writable($rp)) return 'ok';
        break;

        case 'DB_SERVER':
            $host = explode(':', preg_replace('@[^0-9a-z\.:]@', '', strtolower($value)));
            if (empty($host[1])) $host[1] = 3306;

            $fp = @fsockopen($host[0], $host[1], $errno, $errstr, 1);

            if ($fp) { fclose($fp); return 'ok'; }
        break;

        case 'DB_LOGIN':
            if (!isset($value['server']) || !isset($value['login']) || !isset($value['password'])) die('err');

            $host = explode(':', preg_replace('@[^0-9a-z\.:]@', '', strtolower($value['server'])));
            if (empty($host[1])) $host[1] = 3306;

            $mysqli = @new mysqli($host[0], $value['login'], $value['password'], '', $host[1]);

            if ($mysqli->connect_errno) return 'err';

            $mysqli->close();
            return 'ok';
        break;

        case 'DB_DATABASE':
            preg_match('@[a-z_]+[0-9a-z_]*@i', strtolower($value), $matches);
            if (isset($matches[0]) && $matches[0] == $value) return 'ok';
        break;

        case 'ADMIN_PASSWORD':
        case 'ADMIN_LOGIN':
        case 'SECRETKEY':
            if (trim($value) != '') return 'ok';
        break;

    }

    return 'err';
}

// Init session
if (!isset($_SESSION['ploopi_install'])) {
    $_SESSION['ploopi_install'] = array();
    $S = &$_SESSION['ploopi_install'];
    // 32 octets : la cle secrete sert de cle AES, de cle d'authentification et de poivre
    // pour le hachage des mots de passe. 10 octets n'en faisaient que 80 bits.
    $S['secretkey'] = substr(base64_encode(random_bytes(32)), 0, 43);
    $S['password'] = substr(base64_encode(openssl_random_pseudo_bytes(8)), 0, -2);
    $S['nbcore'] = @intval(shell_exec('cat /proc/cpuinfo | grep processor | wc -l'));
    $S['processuser'] = posix_getpwuid(posix_geteuid())['name'];
}
else $S = &$_SESSION['ploopi_install'];


// Contrôles via AJAX
if (isset($_REQUEST['ajax'])) {

    /**
     * Les valeurs sont contrôlées telles que le formulaire les enregistrera
     * (UTF-8, sans conversion) : un mot de passe accentué doit être éprouvé
     * tel qu'il sera écrit dans config.php.
     */
    $value = isset($_REQUEST['value']) ? (string)$_REQUEST['value'] : '';

    if ($_REQUEST['ajax'] == 'DB_LOGIN' && isset($_REQUEST['server']) && isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
        $value = [
            'server' => (string)$_REQUEST['server'],
            'login' => (string)$_REQUEST['login'],
            'password' => (string)$_REQUEST['password']
        ];
    }

    die(field_control($_REQUEST['ajax'], $value));
}

// Dossier de la configuration accessible en écriture ? (PLOOPI_CONFIG peut le déplacer)
$strConfigDir = dirname(ploopi\loader::configpath());
$rp = realpath($strConfigDir);
$writable = file_exists($rp) && is_dir($rp) && is_writable($rp);

$model = './config/config.php.model';
$config = ploopi\loader::configpath();
$sql = './install/system/ploopi.sql';

// Sauvegarde POST
if (!empty($_POST)) {

    $S['saved'] = $_POST;

    // Paramètres valides ? on installe ?
    $arrControl = array();

    $arrControl[] = field_control('DATAPATH', $S['saved']['DATAPATH']);
    $arrControl[] = field_control('DB_SERVER', $S['saved']['DB_SERVER']);
    $arrControl[] = field_control('DB_LOGIN', array(
        'server' => $S['saved']['DB_SERVER'],
        'login' => $S['saved']['DB_LOGIN'],
        'password' => $S['saved']['DB_PASSWORD']
    ));
    $arrControl[] = field_control('DB_DATABASE', $S['saved']['DB_DATABASE']);
    $arrControl[] = field_control('ADMIN_PASSWORD', $S['saved']['ADMIN_PASSWORD']);
    $arrControl[] = field_control('ADMIN_LOGIN', $S['saved']['ADMIN_LOGIN']);
    $arrControl[] = field_control('SECRETKEY', $S['saved']['SECRETKEY']);
    $arrControl[] = $writable ? 'ok' : 'err';
    $arrControl[] = file_exists($model) ? 'ok' : 'err';
    $arrControl[] = file_exists($sql) ? 'ok' : 'err';

    $booOk = true;
    foreach($arrControl as $v) if ($v != 'ok') $booOk = false;

    // On peut installer
    if ($booOk) {

        $S['saved']['NBCORE'] = $S['nbcore'];

        // Adresse publique : une URL http(s) sans guillemet, ou rien (config.php.model)
        $strPublique = isset($S['saved']['PUBLIC_URL']) ? trim($S['saved']['PUBLIC_URL']) : installer_publicurl();
        $S['saved']['PUBLIC_URL'] = (filter_var($strPublique, FILTER_VALIDATE_URL) && preg_match('@^https?://[^\'"\\\\\s]+$@i', $strPublique)) ? rtrim($strPublique, '/') : '';

        foreach($S['saved'] as $k => $v) {
            $tags[] = "<{$k}>";
            $replacements[] = $v;
        }

        // Génération IV
        $len = openssl_cipher_iv_length($S['saved']['CIPHER']);
        $tags[] = "<CIPHER_IV>";
        $replacements[] = substr(base64_encode(openssl_random_pseudo_bytes($len)), 0, $len);

        // Génération du fichier config.php
        file_put_contents($config, str_replace($tags, $replacements, file_get_contents($model)));
        chmod($config, 0640);
        clearstatcache();
        if (!file_exists($config)) {
            header('Location: .');
            exit;
        }

        // Import SQL
        $host = explode(':', preg_replace('@[^0-9a-z\.:]@', '', strtolower($S['saved']['DB_SERVER'])));
        if (empty($host[1])) $host[1] = 3306;

        $mysqli = @new mysqli($host[0], $S['saved']['DB_LOGIN'], $S['saved']['DB_PASSWORD'], '', $host[1]);

        if ($mysqli->connect_errno) {
            unlink($config);
            header('Location: .');
            exit;
        }

        $mysqli->set_charset('utf8');

        /**
          * Un nom de base est un identifiant SQL : il ne peut pas etre echappe comme une
          * valeur. On le contraint donc a une forme sure avant de l'interpoler.
          */
        if (!preg_match('@^[A-Za-z0-9_]{1,64}$@', $S['saved']['DB_DATABASE'])) {
            unlink($config);
            header('Location: .');
            exit;
        }

        @$mysqli->real_query("DROP DATABASE IF EXISTS `{$S['saved']['DB_DATABASE']}`");
        $mysqli->next_result();

        @$mysqli->real_query("CREATE DATABASE `{$S['saved']['DB_DATABASE']}` CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci");
        $mysqli->next_result();

        if (!$mysqli->select_db($S['saved']['DB_DATABASE'])) {
            unlink($config);
            header('Location: .');
            exit;
        }

        $mysqli->multi_query(file_get_contents($sql));
        while ($mysqli->more_results()) $mysqli->next_result();

        switch($S['saved']['HASH_ALGO']) {
            case 'bcrypt':
                // Même entrée que user::generate_hash() : bcrypt ne lit que 72 octets
                $hash = password_hash(ploopi\user::bcrypt_input($S['saved']['ADMIN_PASSWORD'], $S['saved']['ADMIN_LOGIN'], $S['saved']['SECRETKEY']), PASSWORD_BCRYPT);
            break;

            default:
                $hash = hash($S['saved']['HASH_ALGO'], "{$S['saved']['SECRETKEY']}/{$S['saved']['ADMIN_LOGIN']}/".hash($S['saved']['HASH_ALGO'], $S['saved']['ADMIN_PASSWORD']));
            break;
        }

        $strAdminLogin = $mysqli->real_escape_string($S['saved']['ADMIN_LOGIN']);
        $strAdminHash  = $mysqli->real_escape_string($hash);
        $strAdminMail  = $mysqli->real_escape_string($S['saved']['ADMIN_MAIL']);

        $res = $mysqli->real_query($sql = "
            UPDATE  `ploopi_user`
            SET     `login` = '{$strAdminLogin}',
                    `password` = '{$strAdminHash}',
                    `email` = '{$strAdminMail}',
                    `date_creation` = '".date('YmdHis')."'
            WHERE   `login` = 'admin'
        ");
        $mysqli->next_result();

        $mysqli->close();

        session_destroy();
    }
    // Échec : le formulaire est réaffiché avec les champs en cause signalés
    else $S['echec'] = true;

    header('Location: .');
    exit;
}

// Init variables
$arrVariables = [
    'admin' => [
        'ADMIN_LOGIN' => [
            'Identifiant',
            '',
            'admin'
        ],
        'ADMIN_PASSWORD' => [
            'Mot de passe',
            'Proposé au hasard : notez-le, il ne sera plus affiché',
            $S['password']
        ],
    ],
    'database' => [
        'DB_SERVER' => [
            'Adresse du serveur MariaDB',
            'Nom d\'hôte, suivi au besoin du port : « hôte:3306 »',
            // Sous Docker, docker-compose.yml désigne le service de la base (« db »)
            getenv('PLOOPI_DB_SERVER') ?: 'localhost'
        ],
        'DB_LOGIN' => [
            'Utilisateur',
            '',
            ''
        ],
        'DB_PASSWORD' => [
            'Mot de passe',
            '',
            ''
        ],
        'DB_DATABASE' => [
            'Nom de la base',
            'Recréée si elle existe déjà',
            'ploopi'
        ]
    ],
    'misc' => [
        'PUBLIC_URL' => [
            'Adresse publique du site',
            'Sert aux liens envoyés par courriel (mot de passe oublié). Ex. : https://www.exemple.fr',
            installer_publicurl()
        ],
        'DATAPATH' => [
            'Dossier de stockage des documents',
            'Accessible en écriture à « '.$S['processuser'].' »'
            .' ; hors de la racine web pour qu\'aucun dépôt ne soit servi directement',
            '../ploopi-data'
        ],
        'SYS_MAIL' => [
            'Adresse destinatrice des mails d\'erreurs',
            'Plusieurs adresses possibles, séparées par une virgule',
            ''
        ],
        'ADMIN_MAIL' => [
            'Adresse émettrice des mails (erreurs, alertes, messages…)',
            'Une seule adresse',
            ''
        ],
    ],

    'security' => [
        'CIPHER' => [
            'Algorithme de chiffrement des URLs',
            '',
            'AES-128-CBC',
            []
        ],
        'HASH_ALGO' => [
            'Algorithme de hachage des mots de passe',
            'bcrypt, lent et salé par construction, est le seul adapté. Les algorithmes rapides (md5, sha*, whirlpool…) restent reconnus à la vérification (_PLOOPI_HASH_ALGO_PREVIOUS), pour migrer une base existante.',
            'bcrypt',
            [
                'bcrypt' => 'BCRYPT'
            ]
        ],
        'SECRETKEY' => [
            'Clé secrète',
            'Utilisée pour le chiffrement des URLs et des mots de passe',
            $S['secretkey']
        ]
    ]
];

foreach(openssl_get_cipher_methods() as $method) {
    $method = strtoupper($method);
    $arrVariables['security']['CIPHER'][3][$method] = $method;
}


// Init groupes : libellé et icône du sprite de ploopi4
$arrGroupes = [
    'admin' => ['Administrateur', 'user-shield'],
    'database' => ['Base de données', 'database'],
    'misc' => ['Système', 'server'],
    'security' => ['Sécurité', 'shield-lock']
];

// Message affiché sous un champ refusé
$arrMessages = [
    'ADMIN_LOGIN' => 'Champ obligatoire',
    'ADMIN_PASSWORD' => 'Champ obligatoire',
    'SECRETKEY' => 'Champ obligatoire',
    'DB_SERVER' => 'Serveur injoignable à cette adresse',
    'DB_LOGIN' => 'Connexion refusée : utilisateur ou mot de passe incorrect',
    'DB_PASSWORD' => 'Connexion refusée : utilisateur ou mot de passe incorrect',
    'DB_DATABASE' => 'Lettres, chiffres et « _ » seulement, sans chiffre au début',
    'DATAPATH' => 'Dossier introuvable ou non accessible en écriture'
];

// Sauvegarde des valeurs par défaut
if (empty($S['saved'])) {
    foreach($arrVariables as $grp => $varlist) {
        foreach($varlist as $var => $row) {
            $S['saved'][$var] = $row[2];
        }
    }
}

/**
 * Après une tentative refusée, l'état de chaque champ contrôlé est calculé ici : la page
 * signale les champs en cause sans attendre le script (qui reprend la main ensuite).
 */
$arrEtats = [];
if (!empty($S['echec'])) {
    $V = $S['saved'];
    foreach(['ADMIN_LOGIN', 'ADMIN_PASSWORD', 'DB_SERVER', 'DB_DATABASE', 'DATAPATH', 'SECRETKEY'] as $var) {
        $arrEtats[$var] = field_control($var, isset($V[$var]) ? (string)$V[$var] : '');
    }
    $arrEtats['DB_LOGIN'] = $arrEtats['DB_PASSWORD'] = field_control('DB_LOGIN', [
        'server' => isset($V['DB_SERVER']) ? (string)$V['DB_SERVER'] : '',
        'login' => isset($V['DB_LOGIN']) ? (string)$V['DB_LOGIN'] : '',
        'password' => isset($V['DB_PASSWORD']) ? (string)$V['DB_PASSWORD'] : ''
    ]);
}

// Prérequis : sans eux, l'installation ne peut pas aboutir
$arrPrerequis = [
    ['PHP '.PHP_VERSION, version_compare(PHP_VERSION, '7.0', '>=')],
    ['Extensions mysqli, openssl, mbstring, posix', extension_loaded('mysqli') && extension_loaded('openssl') && extension_loaded('mbstring') && extension_loaded('posix')],
    ['Dossier de configuration accessible en écriture', $writable],
    ['Modèle de configuration (config.php.model)', file_exists($model)],
    ['Schéma de la base (ploopi.sql)', file_exists($sql)]
];
$booPrerequis = true;
foreach($arrPrerequis as $row) if (!$row[1]) $booPrerequis = false;

// Thème : celui que ce navigateur a choisi sur la page de connexion, sinon celui du système
$strTheme = isset($_COOKIE['ploopi_theme']) && in_array($_COOKIE['ploopi_theme'], ['light', 'dark'], true) ? $_COOKIE['ploopi_theme'] : 'auto';

$strGabarit = './templates/backoffice/ploopi4';

/**
 * Icône du sprite de ploopi4. L'assistant est inclus depuis ploopi\loader : ses variables
 * ne sont pas globales, d'où le chemin écrit ici.
 *
 * @param string $strIcone nom de l'icône
 * @param string $strClasse classes supplémentaires
 * @return string balise svg
 */
function installer_icon($strIcone, $strClasse = '') {
    return '<svg class="p4-icon'.($strClasse == '' ? '' : " {$strClasse}").'" aria-hidden="true"><use href="./templates/backoffice/ploopi4/img/icons.svg#i-'.$strIcone.'"/></svg>';
}

$tabid = 1;

header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
header('Last-Modified: ' . gmdate("D, d M Y H:i:s"));

// HTTP/1.1
header('Cache-Control: private_no_expire, must-revalidate');

// HTTP/1.0
header('Pragma: no-cache');

// On génère un Etag unique
header('Etag: '.microtime());

header('Accept-Ranges: bytes');
header('Content-type: text/html; charset=utf-8');

$strVersion = ploopi\str::htmlentities(_PLOOPI_VERSION.' ('._PLOOPI_REVISION.')');

?><!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $strTheme; ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="theme-color" content="#1e3d44" />
    <title>Installation de Ploopi <?php echo $strVersion; ?></title>
    <link rel="icon" href="<?php echo $strGabarit; ?>/img/favicon.png" type="image/png" />
    <link rel="preload" href="<?php echo $strGabarit; ?>/fonts/Inter-latin.woff2" as="font" type="font/woff2" crossorigin="anonymous" />
    <!--
        Si la feuille de l'assistant ne se charge pas (RewriteBase erroné), le message
        #htaccess reste seul visible : la feuille masque ce message et affiche l'assistant.
    -->
    <style>
    #background {display:none;}
    #htaccess {display:block; font-family:sans-serif; font-size:14px; max-width:600px; margin:20px auto 0 auto; padding:10px 14px; border:2px solid #a60000; color:#000; background:#f0f0f0;}
    #htaccess pre {border:1px dotted #c0c0c0; padding:2px 4px; background:#fff;}
    </style>
    <link type="text/css" rel="stylesheet" href="<?php echo $strGabarit; ?>/css/styles.pack.css?v=<?php echo urlencode(_PLOOPI_VERSION); ?>" media="screen" />
    <link type="text/css" rel="stylesheet" href="./templates/install/css/styles.css?v=<?php echo urlencode(_PLOOPI_VERSION); ?>" media="screen" />
</head>
<body class="p4-body p4-body-install">

<div id="background" class="p4-install p4-ui">
    <aside class="p4-login-visual p4-install-visual">
        <div class="p4-install-brand">
            <img class="p4-brand-mark" src="<?php echo $strGabarit; ?>/img/logo.svg" alt="" />
            <span><strong>ploopi</strong><small>Assistant d'installation</small></span>
        </div>

        <div class="p4-install-tagline">
            <strong>Installer Ploopi</strong>
            <span>Version <?php echo $strVersion; ?></span>
        </div>

        <div class="p4-install-prereq">
            <h2>Prérequis</h2>
            <ul>
                <?php foreach($arrPrerequis as $row) { ?>
                <li class="<?php echo $row[1] ? 'p4-ok' : 'p4-err'; ?>"><?php echo installer_icon($row[1] ? 'circle-check' : 'circle-x'); ?><span><?php echo ploopi\str::htmlentities($row[0]); ?></span></li>
                <?php } ?>
            </ul>
        </div>
    </aside>

    <main class="p4-install-panel">
        <form id="form" class="p4-install-form" action="" method="post" novalidate>
            <header class="p4-install-head">
                <h1>Installation</h1>
                <p class="p4-lead">Renseignez les quatre rubriques : chaque valeur est vérifiée à la saisie. L'assistant crée ensuite la base de données et le fichier de configuration, puis ouvre la page de connexion.</p>
            </header>

            <?php if (!empty($S['echec'])) { ?>
            <div class="p4-callout p4-callout-danger" role="alert">
                <?php echo installer_icon('alert-triangle'); ?>
                <div class="p4-callout-body">L'installation n'a pas abouti : corrigez les champs signalés, puis relancez-la.</div>
            </div>
            <?php } ?>

            <?php if (!$writable) { ?>
            <div class="p4-callout p4-callout-danger" role="alert">
                <?php echo installer_icon('lock'); ?>
                <div class="p4-callout-body">Le dossier « <?php echo ploopi\str::htmlentities($strConfigDir == './config' ? 'config' : $strConfigDir); ?> » n'est pas accessible en écriture à l'utilisateur « <?php echo ploopi\str::htmlentities($S['processuser']); ?> » : le fichier de configuration ne pourra pas être écrit.</div>
            </div>
            <?php } ?>

            <div class="p4-install-groups">
                <?php foreach($arrVariables as $grp => $varlist) { ?>
                <section class="p4-card p4-install-group" aria-labelledby="grp_<?php echo $grp; ?>">
                    <div class="p4-card-header">
                        <?php echo installer_icon($arrGroupes[$grp][1]); ?>
                        <h2 id="grp_<?php echo $grp; ?>"><?php echo ploopi\str::htmlentities($arrGroupes[$grp][0]); ?></h2>
                    </div>
                    <div class="p4-card-body p4-install-fields">
                        <?php
                        foreach($varlist as $var => $row) {
                            $strValeur = isset($S['saved'][$var]) ? (string)$S['saved'][$var] : (string)$row[2];
                            $strEtat = isset($arrEtats[$var]) ? $arrEtats[$var] : '';
                            $booPasse = ($var == 'DB_PASSWORD');
                            $strAide = $row[1] == '' ? '' : "{$var}_aide";
                            $strDecrit = trim($strAide.($strEtat == 'err' ? " {$var}_msg" : ''));
                            ?>
                            <div class="p4-field p4-install-field<?php echo $strEtat == '' ? '' : " p4-{$strEtat}"; ?>" data-field="<?php echo $var; ?>">
                                <label for="<?php echo $var; ?>"><?php echo ploopi\str::htmlentities($row[0]); ?></label>
                                <div class="p4-install-control<?php if ($booPasse) echo ' p4-install-pw'; ?>">
                                    <?php if (empty($row[3])) { ?>
                                    <input class="p4-input field<?php if ($var == 'SECRETKEY') echo ' p4-mono'; ?>" type="<?php echo $booPasse ? 'password' : 'text'; ?>" id="<?php echo $var; ?>" name="<?php echo $var; ?>" value="<?php echo ploopi\str::htmlentities($strValeur); ?>" tabindex="<?php echo $tabid; ?>" autocomplete="off" spellcheck="false"<?php if ($strDecrit != '') echo " aria-describedby=\"{$strDecrit}\""; ?><?php if ($strEtat == 'err') echo ' aria-invalid="true"'; ?> />
                                    <?php } else { ?>
                                    <select class="p4-select field" id="<?php echo $var; ?>" name="<?php echo $var; ?>" tabindex="<?php echo $tabid; ?>"<?php if ($strAide != '') echo " aria-describedby=\"{$strAide}\""; ?>>
                                        <?php foreach($row[3] as $k => $v) { ?>
                                        <option value="<?php echo ploopi\str::htmlentities($k); ?>"<?php if ($k == $strValeur) echo ' selected="selected"'; ?>><?php echo ploopi\str::htmlentities($v); ?></option>
                                        <?php } ?>
                                    </select>
                                    <?php } ?>
                                    <?php if (isset($arrMessages[$var])) { ?>
                                    <span class="p4-install-state" aria-hidden="true"><?php echo installer_icon('circle-check', 'p4-install-ok').installer_icon('circle-x', 'p4-install-err').installer_icon('refresh', 'p4-install-wait'); ?></span>
                                    <?php } ?>
                                    <?php if ($booPasse) { ?>
                                    <button type="button" class="p4-install-reveal" data-p4-reveal="<?php echo $var; ?>" aria-pressed="false" aria-label="Afficher le mot de passe" title="Afficher le mot de passe"><?php echo installer_icon('eye', 'p4-install-eye').installer_icon('eye-off', 'p4-install-eye-off'); ?></button>
                                    <?php } ?>
                                </div>
                                <?php if ($strAide != '') { ?><small class="p4-hint" id="<?php echo $strAide; ?>"><?php echo ploopi\str::htmlentities($row[1]); ?></small><?php } ?>
                                <?php if (isset($arrMessages[$var])) { ?><small class="p4-install-msg" id="<?php echo $var; ?>_msg"><?php echo ploopi\str::htmlentities($arrMessages[$var]); ?></small><?php } ?>
                            </div>
                            <?php
                            $tabid++;
                        }
                        ?>
                    </div>
                </section>
                <?php } ?>
            </div>

            <div class="p4-install-actions">
                <div class="p4-callout p4-callout-warning">
                    <?php echo installer_icon('alert-triangle'); ?>
                    <div class="p4-callout-body">Si la base de données existe déjà, elle est supprimée puis recréée : son contenu est perdu.</div>
                </div>
                <button type="submit" class="p4-btn p4-btn-primary p4-install-submit" tabindex="<?php echo $tabid; ?>"<?php if (!$booPrerequis) echo ' disabled="disabled"'; ?>>
                    <span class="p4-install-submit-label">Installer</span>
                    <span class="p4-install-submit-busy">Installation en cours…</span>
                    <?php echo installer_icon('chevron-right'); ?>
                </button>
            </div>

            <footer class="p4-install-foot">
                <span>Propulsé par <a href="https://www.ploopi.fr">Ploopi</a> · Une création <a href="https://www.ovensia.fr">Ovensia</a></span>
                <span class="p4-theme-switch" role="group" aria-label="Thème">
                    <button type="button" data-p4-theme="light" aria-pressed="<?php echo $strTheme == 'light' ? 'true' : 'false'; ?>" aria-label="Thème clair" title="Thème clair"><?php echo installer_icon('sun'); ?></button>
                    <button type="button" data-p4-theme="auto" aria-pressed="<?php echo $strTheme == 'auto' ? 'true' : 'false'; ?>" aria-label="Thème du système" title="Thème du système"><?php echo installer_icon('device-desktop'); ?></button>
                    <button type="button" data-p4-theme="dark" aria-pressed="<?php echo $strTheme == 'dark' ? 'true' : 'false'; ?>" aria-label="Thème sombre" title="Thème sombre"><?php echo installer_icon('moon'); ?></button>
                </span>
            </footer>
        </form>
    </main>
</div>

<div id="htaccess">
    La feuille de style n'a pas pu être chargée correctement.
    <br />Vous devriez vérifier la valeur de la directive <em>RewriteBase</em> (dans le fichier <em>.htaccess</em>) pour qu'elle pointe sur la racine de votre site.
    <br /><br />Par exemple, si votre URL d'accès à Ploopi est de la forme http://mondomaine/ploopi/ , vous devez paramétrer <em>RewriteBase</em> de la manière suivante :
    <br />
    <pre>RewriteBase /ploopi</pre>
</div>

<script src="./templates/install/js/install.js?v=<?php echo urlencode(_PLOOPI_VERSION); ?>"></script>
</body>
</html>
