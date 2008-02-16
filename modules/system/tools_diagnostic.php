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

echo $skin->open_simplebloc(_SYSTEM_LABEL_DIAGNOSTIC);


$columns = array();
$values = array();

$columns['left']['function']    = array('label' => _SYSTEM_LABEL_FUNCTION, 'width' => '200', 'options' => array('sort' => true));
$columns['left']['desc']        = array('label' => _SYSTEM_LABEL_DESCRIPTION, 'width' => '250', 'options' => array('sort' => true));
$columns['right']['result']     = array('label' => _SYSTEM_LABEL_RESULT, 'width' => '80', 'options' => array('sort' => true));
$columns['auto']['comment']     = array('label' => _SYSTEM_LABEL_COMMENTARY, 'options' => array('sort' => true));

$c = 0;

$array_path = array();
$array_path['data'] = _PLOOPI_PATHDATA;
$array_path['modules'] = realpath('.')._PLOOPI_SEP.'modules';

/* TEST 1 - Ecriture dans les dossiers DATA et MODULES */

foreach($array_path as $key => $path)
{
    $comment = 'Le dossier est accessible en �criture.';
    $testok = true;

    switch($key)
    {
        case 'data':
            $desc = "Le dossier � {$key} � est utilis� par PLOOPI pour stocker les fichiers envoy�s par l'utilisateur ou g�n�r�s � partir des modules. Ce dossier doit �tre accessible en �criture sinon certains modules peuvent ne pas fonctionner correctement.";
        break;

        case 'modules':
            $desc = "Le dossier � {$key} � est utilis� par PLOOPI lors de l'installation d'un module. Ce dossier doit �tre accessible en �criture durant la phase d'installation des modules. L'acc�s en �criture peut �tre d�sactiv� le reste du temps.";
        break;
    }

    if (!file_exists($path))
    {
        $comment = "Le dossier � $path � n'existe pas";
        $testok = false;
    }
    else
    {
        $path_stat = stat($path);
        $path_pwuid = posix_getpwuid($path_stat['uid']);
        $path_grgid = posix_getgrgid($path_stat['gid']);
        $path_username = $path_pwuid['name'];
        $path_goupname = $path_grgid['name'];

        $path_perm = substr(decoct($path_stat['mode']),-4);
        $path_owner = $path_stat['uid'];

        $apache_pwuid = posix_getpwuid(posix_getuid());
        $apache_grgid = posix_getgrgid(posix_getgid());
        $apache_username = $apache_pwuid['name'];
        $apache_goupname = $apache_grgid['name'];

        if (!is_writable($path))
        {

            $comment = "Le dossier � {$path} � n'est pas accessible en �criture.\nIl appartient actuellement � l'utilisateur {$path_username} du groupe {$path_goupname}.\nPour rappel, l'utilisateur Apache ({$apache_username}) du groupe {$apache_goupname} doit pouvoir �crire dans ce dossier.";
            $testok = false;
        }
    }

    switch($key)
    {
        case 'data':
            $bullet = ($testok) ? 'green' : 'red';
        break;

        case 'modules':
            $bullet = ($testok) ? 'green' : 'orange';
            $comment .= ($testok) ? '' : "\nIl n'est pas possible d'installer des modules dans cette configuration.";
        break;
    }

    $values[$c]['values']['function']   = array('label' => htmlentities("Acc�s en �criture au dossier � {$key} �"));
    $values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities($desc)), 'style' => '');
    $values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
    $values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
    $c++;

}

/* TEST 2 - Connectivit� internet */

require_once 'HTTP/Request.php';

$testurl = 'http://www.ovensia.fr';

$request = new HTTP_Request($testurl, array('timeout', 1000));

if (_PLOOPI_INTERNETPROXY_HOST != '')
{
    $request->setProxy( _PLOOPI_INTERNETPROXY_HOST,
                        _PLOOPI_INTERNETPROXY_PORT,
                        _PLOOPI_INTERNETPROXY_USER,
                        _PLOOPI_INTERNETPROXY_PASS
                        );
}

$comment = 'Connexion internet ouverte';
$testok = true;
$res = $request->sendRequest();

if ($res !== true)
{
    $comment = "Probl�me de connexion internet\nPLOOPI n'a pas pu se connecter sur {$testurl}";
    if ($_SESSION['ploopi']['modules'][1]['system_proxy_host'] != '') $comment .= "\nen utilisant les param�tres Proxy suivants :\nproxy_host: {$_SESSION['ploopi']['modules'][1]['system_proxy_host']}, proxy_port: {$_SESSION['ploopi']['modules'][1]['system_proxy_port']}, proxy_user: {$_SESSION['ploopi']['modules'][1]['system_proxy_user']}, proxy_pass: {$_SESSION['ploopi']['modules'][1]['system_proxy_pass']}";

    $testok = false;
}

$bullet = ($testok) ? 'green' : 'red';

$values[$c]['values']['function']   = array('label' => htmlentities("Connexion internet"));
$values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities("Certains modules de PLOOPI ont besoin de se connecter � internet. Ce test vous indique si le serveur arrive � ouvrir une connexion internet.")), 'style' => '');
$values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
$values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
$c++;


/* TEST 3 - config PHP */

$testok = !get_magic_quotes_gpc();

$comment = ($testok) ? 'La directive est correctement configur�e' : 'Vous devriez modifier la valeur de cette directive';
$bullet = ($testok) ? 'green' : 'orange';

$values[$c]['values']['function']   = array('label' => htmlentities("PHP - magic_quote_gpc"));
$values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities("La directive php 'magic_quote_gpc' permet d'ajouter automatiquement des � ' � dans le contenu des superglobales \$_GET, \$_POST, \$_COOKIE. Il est recommand� de d�sactiver cette fonctionnalit�.")), 'style' => '');
$values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
$values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
$c++;

$testok = !ini_get('register_globals');

$comment = ($testok) ? 'La directive est correctement configur�e.' : 'Vous devriez modifier la valeur de cette directive.';
$bullet = ($testok) ? 'green' : 'red';

$values[$c]['values']['function']   = array('label' => htmlentities("PHP - register_globals"));
$values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities("La directive php 'register_globals' permet d'affecter automatiquement les param�tres en variables globales. Il est fortement recommand� de d�sactiver cette fonctionnalit�.")), 'style' => '');
$values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
$values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
$c++;


$testok = !ini_get('display_errors');

$comment = ($testok) ? 'La directive est correctement configur�e' : 'Vous devriez modifier la valeur de cette directive';
$bullet = ($testok) ? 'green' : 'orange';

$values[$c]['values']['function']   = array('label' => htmlentities("PHP - display_errors"));
$values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities("La directive php 'display_errors' permet d'afficher les erreurs d'ex�cution. Il est fortement recommand� de d�sactiver cette fonctionnalit�, notamment pour un site en production.")), 'style' => '');
$values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
$values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
$c++;


$ploopi_maxfilesize = sprintf('%.02f', _PLOOPI_MAXFILESIZE/1024);
$upload_max_filesize =  intval(ini_get('upload_max_filesize')*1024);
$post_max_size = intval(ini_get('post_max_size')*1024);

// Test la taille max d'upload de fichier
$fmax = (_PLOOPI_USE_CGIUPLOAD) ? $ploopi_maxfilesize : min($ploopi_maxfilesize, $upload_max_filesize, $post_max_size);

$testok = 1;
if ($fmax < 2048) $testok = 3; // 2 Mo
elseif ($fmax < 5120) $testok = 2; // 5 Mo

$fmax = sprintf("%.02f", $fmax/1024);

switch($testok)
{
    case 1:
        $bullet = 'green';
        $comment = 'Vous pouvez uploader des fichiers d\'un poids sup�rieur � 5 Mio (max : '.$fmax.' Mio).';
    break;

    case 2:
        $bullet = 'orange';
        $comment = 'Vous pouvez uploader des fichiers d\'un poids maximum inf�rieur � 5 Mio.';
    break;

    case 3:
        $bullet = 'red';
        $comment = 'Vous pouvez uploader des fichiers d\'un poids maximum inf�rieur 2 Mio.';
    break;
}

if (_PLOOPI_USE_CGIUPLOAD) $comment .= "\nLe mode CGIUPLOAD est activ�. Attention car il n�cessite une configuration particuli�re d'Apache";

if ($testok>1) $comment .= "\nSi vous voulez modifier cette limite vous pouvez modifier les param�tres suivants:\n- upload_max_filesize (PHP / php.ini) : {$upload_max_filesize} kio\n- post_max_size (PHP / php.ini) : {$post_max_size} kio\n- _PLOOPI_MAXFILESIZE (PLOOPI / config.php) : {$ploopi_maxfilesize} kio";

$values[$c]['values']['function']   = array('label' => htmlentities("Capacit� d'upload"));
$values[$c]['values']['desc']       = array('label' => ploopi_nl2br(htmlentities("La capacit� d'upload permet de d�terminer le poids maximum d'un fichier pouvant �tre accept� par le serveur. Si le poids est trop petit, vous risquez de ne pas pouvoir accepter certains documents.")), 'style' => '');
$values[$c]['values']['comment']    = array('label' => ploopi_nl2br(htmlentities($comment)), 'style' => '');
$values[$c]['values']['result']     = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />", 'style' => '');
$c++;




$skin->display_array($columns, $values, 'array_diagnostic', array('sortable' => true, 'orderby_default' => 'function'));
?>

<p class="ploopi_va" style="padding:2px;">
<img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_red.png">
<span>Une puce rouge indique un probl�me potentiellement bloquant</span>
</p>
<p class="ploopi_va" style="padding:2px;">
<img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_orange.png">
<span>Une puce orange indique qu'une fonctionnalit� ou une configuration peut poser probl�me</span>
</p>
<p class="ploopi_va" style="padding:2px;">
<img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_green.png">
<span>Une puce verte indique que tout va bien !</span>
</p>

<? echo $skin->close_simplebloc(); ?>
