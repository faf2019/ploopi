<?php
/*
  Copyright (c) 2007-2016 Ovensia
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

session_start();

ovensia\ploopi\buffer::clean();

clearstatcache();

function field_control($field, $value) {
    switch($field) {
        case 'DATAPATH':
            $rp = realpath($value);
            if (file_exists($rp) && is_dir($rp) && is_writable($rp)) return 'ok';
        break;

        case 'ELASTIC_HOST':
            try {
                $client = \Elasticsearch\ClientBuilder::create()->setHosts([$value])->build();
                return 'ok';
            }
            catch (Exception $e) { }
        break;

        case 'DB_SERVER':
            $host = explode(':', preg_replace('@[^0-9a-z\.:]@', '', strtolower($value)));
            if (empty($host[1])) $host[1] = 3306;

            $str = exec($cmd = "nc -zv {$host[0]} {$host[1]}", $output, $return);

            if ($return == 0) return 'ok';
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
    $S['secretkey'] = substr(base64_encode(openssl_random_pseudo_bytes(10)), 0, -2);
    $S['password'] = substr(base64_encode(openssl_random_pseudo_bytes(8)), 0, -2);
    $S['nbcore'] = @intval(`cat /proc/cpuinfo | grep processor | wc -l`);
    $S['processuser'] = posix_getpwuid(posix_geteuid())['name'];
}
else $S = &$_SESSION['ploopi_install'];


// Contrôles via AJAX
if (isset($_REQUEST['ajax'])) {

    $value = isset($_REQUEST['value']) ? utf8_decode($_REQUEST['value']) : '';

    if ($_REQUEST['ajax'] == 'DB_LOGIN' && isset($_REQUEST['server']) && isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
        $value = [
            'server' => utf8_decode($_REQUEST['server']),
            'login' => utf8_decode($_REQUEST['login']),
            'password' => utf8_decode($_REQUEST['password'])
        ];
    }

    die(field_control($_REQUEST['ajax'], $value));
}

// Config accessible en écriture ?
$rp = realpath('./config');
$writable = file_exists($rp) && is_dir($rp) && is_writable($rp);

// Sauvegarde POST
if (!empty($_POST)) {
    $model = './config/config.php.model';
    $config = './config/config.php';
    $sql = './install/system/ploopi.sql';

    $S['saved'] = $_POST;

    // Paramètres valides ? on installe ?
    $arrControl = array();

    $arrControl[] = field_control('DATAPATH', $S['saved']['DATAPATH']);
    $arrControl[] = field_control('ELASTIC_HOST', $S['saved']['ELASTIC_HOST']);
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

        foreach($S['saved'] as $k => $v) {
            $tags[] = "<{$k}>";
            $replacements[] = $v;
        }

        // Génération du fichier config.php
        file_put_contents($config, str_replace($tags, $replacements, file_get_contents($model)));
        chmod($config, 0640);
        clearstatcache();
        if (!file_exists($config)) header('Location: .');

        // Import SQL
        $host = explode(':', preg_replace('@[^0-9a-z\.:]@', '', strtolower($S['saved']['DB_SERVER'])));
        if (empty($host[1])) $host[1] = 3306;

        $mysqli = @new mysqli($host[0], $S['saved']['DB_LOGIN'], $S['saved']['DB_PASSWORD'], '', $host[1]);

        if ($mysqli->connect_errno) {
            unlink($config);
            header('Location: .');
        }

        $mysqli->set_charset('latin1');

        @$mysqli->real_query("DROP DATABASE IF EXISTS `{$S['saved']['DB_DATABASE']}`");
        $mysqli->next_result();

        @$mysqli->real_query("CREATE DATABASE `{$S['saved']['DB_DATABASE']}`");
        $mysqli->next_result();

        if (!$mysqli->select_db($S['saved']['DB_DATABASE'])) {
            unlink($config);
            header('Location: .');
        }

        $mysqli->multi_query(file_get_contents($sql));
        while ($mysqli->more_results()) $mysqli->next_result();

        $hash = hash($S['saved']['HASH_ALGO'], "{$S['saved']['SECRETKEY']}/{$S['saved']['ADMIN_LOGIN']}/".hash($S['saved']['HASH_ALGO'], $S['saved']['ADMIN_PASSWORD']));

        $res = $mysqli->real_query($sql = "
            UPDATE  `ploopi_user`
            SET     `login` = '{$S['saved']['ADMIN_LOGIN']}',
                    `password` = '{$hash}',
                    `email` = '{$S['saved']['ADMIN_MAIL']}',
                    `date_creation` = '".date('YmdHis')."'
            WHERE   `login` = 'admin'
        ");
        $mysqli->next_result();

        $mysqli->close();

        session_destroy();
    }

    header('Location: .');
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
            '',
            $S['password']
        ],
    ],
    'database' => [
        'DB_SERVER' => [
            'Adresse du serveur MariaDB',
            'Ex: "hostname:port"',
            'localhost'
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
            '',
            'ploopi'
        ],
        'ELASTIC_HOST' => [
            'Adresse du serveur ElasticSearch',
            '',
            'localhost:9200'
        ],
    ],
    'misc' => [
        'DATAPATH' => [
            'Dossier de stockage des documents',
            'Doit être accessible en écriture à &laquo; '.$S['processuser'].' &raquo;',
            './data'
        ],
        'SYS_MAIL' => [
            'Adresse destinatrice des mails d\'erreurs',
            "Plusieurs destinataires peuvent être saisis\nChaque adresse doit être séparée par une virgule",
            ''
        ],
        'ADMIN_MAIL' => [
            'Adresse émettrice des mails (erreurs, alertes, tickets...)',
            "Ce champ ne peut contenir qu'une seule adresse",
            ''
        ],
    ],

    'security' => [
        'CIPHER' => [
            'Algorithme de chiffrement des URLs',
            '',
            'MCRYPT_RIJNDAEL_128',
            [
                'MCRYPT_CAST_128' => 'CAST-128',
                'MCRYPT_GOST' => 'GOST',
                'MCRYPT_RIJNDAEL_128' => 'RIJNDAEL-128',
                'MCRYPT_CAST_256' => 'CAST-128',
                'MCRYPT_TWOFISH' => 'TWOFISH',
                'MCRYPT_LOKI97' => 'LOKI97',
                'MCRYPT_SAFERPLUS' => 'SAFER+',
                'MCRYPT_SERPENT' => 'SERPENT',
                'MCRYPT_XTEA' => 'XTEA',
                'MCRYPT_RC2' => 'TC2',
                'MCRYPT_RIJNDAEL_256' => 'RIJNDAEL-256',
                'MCRYPT_BLOWFISH' => 'BLOWFISH',
                'MCRYPT_DES' => 'DES',
                'MCRYPT_TRIPLEDES' => '3DES',
            ]
        ],
        'HASH_ALGO' => [
            'Algorithme de hashage pour le stockage des mots de passe',
            'Recommandé : sha256, sha384, sha512, whirlpool',
            'sha256',
            [
                'md5' => 'MD5',
                'sha1' => 'SHA-1',
                'sha256' => 'SHA-256',
                'sha384' => 'SHA-384',
                'sha512' => 'SHA-512',
                'ripemd128' => 'RIPEMD-128',
                'ripemd160' => 'RIPEMD-160',
                'ripemd256' => 'RIPEMD-256',
                'ripemd320' => 'RIPEMD-320',
                'whirlpool' => 'WHIRLPOOL',
                'tiger128,4' => 'TIGER-128',
                'tiger160,4' => 'TIGER-160',
                'tiger192,4' => 'TIGER-192',
                'gost' => 'GOST',
                'haval128,5' => 'HAVAL-128',
                'haval160,5' => 'HAVAL-160',
                'haval192,5' => 'HAVAL-192',
                'haval224,5' => 'HAVAL-224',
                'haval256,5' => 'HAVAL-256'
            ]
        ],
        'SECRETKEY' => [
            'Clé secrète',
            'Utilisée pour le chiffrement des URLs et des mots de passe',
            $S['secretkey']
        ]
    ]
];

// Init groupes
$arrGroupes = [
    'admin' => 'Administrateur',
    'database' => 'Base de données',
    'misc' => 'Système',
    'security' => 'Sécurité'
];

// Sauvegarde des valeurs par défaut
if (empty($S['saved'])) {
    foreach($arrVariables as $grp => $varlist) {
        foreach($varlist as $var => $row) {
            $S['saved'][$var] = $row[2];
        }
    }
}

$tabid = 1;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <title>Installation de Ploopi <?php echo _PLOOPI_VERSION; ?> (<?php echo _PLOOPI_REVISION; ?>)</title>
    <link rel="icon" href="./templates/backoffice/eyeos/img/favicon.png" type="image/png" />
    <style>
    #background {display:none;}
    #htaccess {
        display:block;
        font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
        font-size: 12px;
        width:600px;
        margin:20px auto 0 auto;
        padding:10px;
        border:2px solid #a60000;
        color:#000;
        background:#f0f0f0;
    }
    #htaccess pre {
        border:1px dotted #c0c0c0;
        padding:2px 4px;
        background:#fff;
    }
    </style>
    <link type="text/css" rel="stylesheet" href="./templates/install/css/styles.css" media="screen" />
    <script type="text/javascript" src="./lib/protoaculous/protoaculous.min.js"></script>

</head>
<body>

    <div id="background">
        <pre>
        <?
        print_r($_SERVER);
        ?>
        </pre>

        <div id="window">
            <h1>Installation de Ploopi <?php echo _PLOOPI_VERSION; ?> (<?php echo _PLOOPI_REVISION; ?>)</h1>
            <form id="form" action="" method="post">
            <div class="form_box">
                <?php foreach($arrVariables as $grp => $varlist) { ?>
                    <div>
                        <h2><?php echo $arrGroupes[$grp]; ?></h2>
                        <?php foreach($varlist as $var => $row) { ?>
                            <p>
                                <label for="<?php echo $var; ?>"><?php echo $row[0]; ?></label>
                                <em><?php echo $row[1]; ?></em>
                                <span id="sp_<?php echo $var; ?>">
                                <?php if (empty($row[3])) { ?>
                                    <input class="field" type="text" id="<?php echo $var; ?>" name="<?php echo $var; ?>" value="<?php echo ovensia\ploopi\str::htmlentities(isset($S['saved'][$var]) ? $S['saved'][$var] : $row[2]); ?>" tabindex="<?php echo $tabid; ?>" />
                                <?php } else { ?>
                                    <select class="field" id="<?php echo $var; ?>" name="<?php echo $var; ?>" tabindex="<?php echo $tabid; ?>">
                                        <?php foreach($row[3] as $k => $v) { ?>
                                            <option value="<?php echo $k ?>" <?php if ($k == (isset($S['saved'][$var]) ? $S['saved'][$var] : $row[2])) echo 'selected="selected"'; ?>><?php echo ovensia\ploopi\str::htmlentities($v); ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                                </span>
                            </p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <?php if (!$writable) { ?>
            <div class="error">
                <img src="./templates/install/gfx/error.png" />
                <span>Le dossier &laquo; config &raquo; n'est pas accessible en écriture à l'utilisateur &laquo; <? echo $S['processuser'] ?> &raquo;</span>
            </div>
            <?php } ?>
            <div class="login_btn_right">
                <button type="submit">
                    <strong style="margin-right:4px;">Installer</strong>
                    <img src="./templates/backoffice/eyeos/img/template/enter.png" />
                </button>
            </div>
            </form>
        </div>
    </div>
    <div id="htaccess">
        La feuille de style n'a pas pu être chargée correctement.
        <br />Vous devriez vérifier la valeur de la directive <em>RewriteBase</em> (dans le fichier <em>.htaccess</em>) pour qu'elle pointe sur la racine de votre site.
        <br /><br />Par exemple, si votre URL d'accès à Ploopi est de la forme http://mondomaine/ploopi/ , vous devez paramétrer <em>RewriteBase</em> de la manière suivante :
        <br />
        <pre>RewriteBase /ploopi</pre>
    </div>

</body>

<script>
    Event.observe(window, 'load', function() {
        console.log('load');

        $$('.field').each(function(field) {
            field_control(field);

            field.observe('change', function(e) {
                field_control(field);
            });
        });
    });

    function field_addclass(field, classname) {
        field.addClassName(classname);
        field.parentNode.addClassName(classname);
    }

    function field_remclass(field, classname) {
        field.removeClassName(classname);
        field.parentNode.removeClassName(classname);
    }

    function field_control(field) {
        field_remclass(field, 'error');

        if (field.name == 'ADMIN_PASSWORD' || field.name == 'ADMIN_LOGIN' || field.name == 'SECRETKEY') {
            if (field.value == '') field_addclass(field, 'error');
        }

        if (field.name == 'DATAPATH' || field.name == 'ELASTIC_HOST' || field.name == 'DB_SERVER' || field.name == 'DB_DATABASE') {
            new Ajax.Request('', {
                method:     'get',
                asynchronous:true,
                parameters: {
                    'ajax': field.name,
                    'value': field.value
                },

                onSuccess:  function(transport, json) {

                    if (transport.responseText != 'ok') field_addclass(field, 'error');

                },

                onFailure: function() {
                    field_addclass(field, 'error');
                }
            });
        }

        if (field.name == 'DB_LOGIN' || field.name == 'DB_PASSWORD') {
            field_remclass($('DB_LOGIN'), 'error');
            field_remclass($('DB_PASSWORD'), 'error');

            new Ajax.Request('', {
                method:     'get',
                asynchronous:true,
                parameters: {
                    'ajax': 'DB_LOGIN',
                    'server': $('DB_SERVER').value,
                    'login': $('DB_LOGIN').value,
                    'password': $('DB_PASSWORD').value
                },

                onSuccess:  function(transport, json) {

                    if (transport.responseText != 'ok') {
                        field_addclass($('DB_LOGIN'), 'error');
                        field_addclass($('DB_PASSWORD'), 'error');
                    }

                },

                onFailure: function() {
                    field_addclass($('DB_LOGIN'), 'error');
                    field_addclass($('DB_PASSWORD'), 'error');
                }
            });
        }

    }
</script>

</html>
