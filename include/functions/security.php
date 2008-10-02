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

/**
 * Fonctions permettant de mettre en place des mécanismes de sécurité.
 * Validation de mots de passe, filtrage de contenu, vérification de droits.
 *
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Vérifie la solidité d'un mot de passe
 *
 * @param string $password mot de passe à vérifier
 * @param int $min_length longueur mini
 * @param int $max_length longueur maxi
 * @return boolean true si le mot de passe est suffisamment solide
 */

function ploopi_checkpasswordvalidity($password, $min_length = 8, $max_length = 20)
{
    return (preg_match('/^(?=^.{'.$min_length.','.$max_length.'}$)(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_-+}{"":;\'?\/><.,\]\[]).*$$/', $password));
}

/**
 * Génère un mot de passe paramétrable
 *
 * @param int $length longueur du mot de passe
 * @param boolean $use_char_up true si le mot de passe doit inclure au moins un caractère majuscule
 * @param boolean $use_char_numb true si le mot de passe doit inclure au moins un caractère numérique
 * @param boolean $use_ponc  true si le mot de passe doit inclure au moins un caractère de ponctuation
 * @return string le mot de passe généré
 * 
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

function ploopi_generatepassword($length = 8, $use_char_up = true, $use_char_numb = true, $use_ponc = true)
{
    if ($length<4) $length=4;
    
    $arrChar = array();
    $arrChar[] = "abcdefghijklmnopqrstuvwxz";
    if ($use_char_up) $arrChar[] = "ABCDEFGHIJKLMNOPQRSTUVWXZ";
    if ($use_char_numb) $arrChar[] = "0123456789";
    if ($use_ponc) $arrChar[] = ":?!@#$%&*";

    $strChar = implode('', $arrChar);
    
    $strPassword = '';
    
    foreach($arrChar as $str) $strPassword .= substr($str,rand(0,strlen($str)-1),1);
    for($c = strlen($strPassword); $c < $length; $c++) $strPassword .= substr($strChar,rand(0,strlen($strChar)-1),1);
    
    return(str_shuffle($strPassword));
}

/**
 * Filtre le contenu d'une variable. 
 * Gère les tableaux multi-dimensionnels. 
 * Enlève les quotes si get_magic_quotes_gpc est activé.
 *
 * @param mixed $var variable à filtrer
 * @param string $varname nom de la variable (permet notamment de traiter un cas particulier avec les variables préfixées fck_)
 * @return mixed variable filtrée
 * 
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

function ploopi_filtervar($var, $varname = '')
{
    include_once './lib/inputfilter/inputfilter.php';
    
    if (is_array($var)) 
    {
        foreach($var as $key => $value)
        {
            $var[$key] = ploopi_filtervar($value, $key);
        }
    }
    else
    {
        if (get_magic_quotes_gpc()) $var = stripslashes($var);
        if (substr($varname,0,4) != 'fck_')
        {
            $inputFilter = new InputFilter('', '', 0, 0, 0);
            $var = $inputFilter->process($var);
        }
    }
    
    return $var;
}

/**
 * Indique si l'utilisateur courant est administrateur système (niveau maxi) dans l'espace courant
 *
 * @param int $workspaceid identifiant de l'espace (optionnel)
 * @return boolean true si l'utilisateur est administrateur système dans cet espace
 */

function ploopi_isadmin($workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN);
}

/**
 * Indique si l'utilisateur courant est gestionnaire d'espace (ou +)
 *
 * @param int $workspaceid identifiant de l'espace (optionnel)
 * @return boolean true si l'utilisateur est gestionnaire de cet espace (ou +)
 */

function ploopi_ismanager($workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    return ($workspaceid != -1 && !empty($_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$workspaceid]['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER);
}

/**
 * Indique si l'utilisateur courant à la droit d'exécuter une action
 *
 * @param int $actionid identifiant de l'action (optionnel)
 * @param int $workspaceid identifiant de l'espace (optionnel)
 * @param int $moduleid identifiant du module (optionnel)
 * @return boolean true si l'utilisateur courant à la droit d'exécuter cette action
 */

function ploopi_isactionallowed($actionid = -1, $workspaceid = -1, $moduleid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    $booAllowed = false;
    
    if (ploopi_isadmin($workspaceid)) $booAllowed = true;
    else
    {
        if (is_array($actionid))
        {
            foreach($actionid as $aid)
            {
                $booAllowed = $booAllowed || isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$aid]);
            }
        }
        else
        {
            if ($actionid == -1) $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid]);
            else $booAllowed = isset($_SESSION['ploopi']['actions'][$workspaceid][$moduleid][$actionid]);
        }
    }
    
    return($booAllowed);
}

/**
 * Indique si l'utilisateur courant peut accéder à un module
 *
 * @param string $moduletype type de module
 * @param int $moduleid identifiant du module (optionnel)
 * @param int $workspaceid identidiant de l'espace (optionnel)
 * @return boolean true l'utilisateur courant peut accéder au module
 */

function ploopi_ismoduleallowed($moduletype, $moduleid = -1, $workspaceid = -1)
{
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid']; // get session value if not defined
    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

    // module existe && module du type indiqué && module affecté à l'espace courant
    return(     !empty($_SESSION['ploopi']['modules'][$moduleid])
            &&  $_SESSION['ploopi']['modules'][$moduleid]['moduletype'] == $moduletype
            &&  in_array($moduleid ,$_SESSION['ploopi']['workspaces'][$workspaceid]['modules'])
        );
}

?>