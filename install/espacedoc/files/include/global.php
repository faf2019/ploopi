<?php
/**
 * Fonctions du modules, constantes, variables globales
 *
 * @package espacedoc
 * @subpackage global
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */


/**
 * OBJET : document
 */
define('_ESPACEDOC_OBJECT_DOCUMENT',   1);

/**
 * ACTION : Administration des données
 */
define('_ESPACEDOC_ACTION_ADMIN', 10);

/**
 * ACTION : consultation des documents
 */
define('_ESPACEDOC_ACTION_DOCUMENTS', 20);

/**
 * Retourne le numéro de semaine (ISO) à partir d'un timestamp MYSQL
 *
 * @param string $ts (AAAAMMJJhhmmss)
 * @return int numéro de la semaine
 */
function espacedoc_timestamp2week($ts)
{
    return date('W', ploopi_timestamp2unixtimestamp($ts));
}

/**
 * Détermine si le menu est sélectionné ou non
 *
 * @param string $menu identifiant du menu sélectionné
 * @param int $moduleid identifiant du module
 * @return boolean true si le menu est sélectionné
 */
function espacedoc_menusel($menu, $moduleid)
{
    return ($_SESSION['ploopi']['moduleid'] == $moduleid && ((isset($_SESSION['espacedoc']['espacedoc_menu']) && $menu == $_SESSION['espacedoc']['espacedoc_menu'] && !isset($_GET['espacedoc_menu'])) || (isset($_GET['espacedoc_menu']) && $menu == $_GET['espacedoc_menu'])));
}


/**
 * Formate le numéro de téléphone avant enregitrement
 *
 * @param string $str numéro de téléphone "brut"
 * @return string numéro formaté
 */
function espacedoc_format_tel($str)
{
    // string to char array
    $array = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
    $str = '';

    foreach($array as $char)
    {
        if (is_numeric($char) || $char == '+' || $char == '(' || $char == ')')
        {
            $str .= $char;
        }
    }

    return($str);
}

/**
 * Retourne un identifiant unique pour l'upload des fichiers
 *
 * @return string guid
 */

function espacedoc_guid()
{
    $workid = strtoupper(md5(session_id().md5(getmypid().uniqid(rand(),true).$_SERVER['SERVER_NAME'])));

    // hopefully conform to the spec, mark this as a random type
    // lets handle the version byte as a number
    $byte = hexdec( substr($workid,12,2) );
    $byte = $byte & hexdec('0f');
    $byte = $byte | hexdec('40');
    $workid = substr_replace($workid, strtoupper(dechex($byte)), 12, 2);

    // hopefully conform to the spec, mark this common variant
    // lets handle the variant
    $byte = hexdec( substr($workid,16,2) );
    $byte = $byte & hexdec('3f');
    $byte = $byte | hexdec('80');
    $workid = substr_replace($workid, strtoupper(dechex($byte)), 16, 2);

    // build a human readable version
    $wid = substr($workid, 0, 8).'-'
    .substr($workid, 8, 4).'-'
    .substr($workid,12, 4).'-'
    .substr($workid,16, 4).'-'
    .substr($workid,20,12);

    return $wid;
}

/**
 * Retourne la taille maximale d'un fichier uploadable
 *
 * @return int taille maximale en ko
 *
 * @see _PLOOPI_MAXFILESIZE
 * @see _PLOOPI_USE_CGIUPLOAD
 */
function espacedoc_max_filesize()
{
    $ploopi_maxfilesize = sprintf('%d', _PLOOPI_MAXFILESIZE/1024);

    if (_PLOOPI_USE_CGIUPLOAD) return($ploopi_maxfilesize);
    else
    {
        $upload_max_filesize =  intval(ini_get('upload_max_filesize')*1024);
        $post_max_size = intval(ini_get('post_max_size')*1024);
        return(min($upload_max_filesize, $post_max_size, $ploopi_maxfilesize));
    }
}
?>
