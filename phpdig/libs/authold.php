<?php
/*
    R�alisation     : BIOT Nicolas pour PHPindex
    Contact        : BIOT Nicolas <nicolas@globalis-ms.com>

    ----------------------------------------------------------
    Fichier        : auth.inc.php
    Description    : Script d'authentification
    Date cr�ation    : 14/05/2001
    Date de modif    : 10/11/2001 Antoine Bajolet
*/

exit(); // unless you use it, then instead add error_reporting(0);

$user = PHPDIG_ADM_USER;
$pwd = PHPDIG_ADM_PASS;

if (isset($_SERVER['PHP_AUTH_USER'])) {
    $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
}
if (isset($_SERVER['PHP_AUTH_PW'])) {
    $PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'];
}

function phpdigAuth(){
    $realm="Administration PhpDig";

    Header("WWW-Authenticate: Basic realm='".$realm."'");
    Header("HTTP/1.0 401 Unauthorized");

    echo " ";
    // echo "Vous ne pouvez acc�der � cette page";
    // la redirection est impossible
    // mais vous pouvez inclure une page html d'erreur
    exit();
}

if (PHPDIG_ADM_AUTH == 1)
{
if( !isset($PHP_AUTH_USER) && !isset($PHP_AUTH_PW) ) {
    phpdigAuth();
}
else {
    if( $PHP_AUTH_USER==$user && $PHP_AUTH_PW==$pwd ) {
        // la suite du script sera ex�cut�e
    }
    else{
        // rappel de la fonction d'identification
        phpdigAuth();
    }
}
}
?>