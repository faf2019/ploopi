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
 * Partie commune des scripts de chargement de l'environnement Ploopi (start.php, start-light.php).
 * Concerne le chargement des espaces de travail (avec application du filtrage par nom de domaine)
 *  
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Package workspace
 */

include_once './include/classes/workspace.php';

///////////////////////////////////////////////////////////////////////////
// GET WORKSPACES (FOR THIS DOMAIN)
// on en profite pour appliquer l'héritage implicite des domaines pour les sous-espaces de travail
///////////////////////////////////////////////////////////////////////////

$select = "SELECT * FROM ploopi_workspace where system = 0 order by depth";
$db->query($select);

$workspaces = array();

while ($fields = $db->fetchrow())
{
    $frontoffice_domain_array = split("\r\n",preg_replace('/\s*/','', $fields['frontoffice_domainlist']));
    $backoffice_domain_array = split("\r\n",preg_replace('/\s*/','', $fields['backoffice_domainlist']));

    $workspaces[$fields['id']] = $fields;
    $workspaces[$fields['id']]['parents_array'] = split(';',$workspaces[$fields['id']]['parents']);
    $workspaces[$fields['id']]['frontoffice_domain_array'] = $frontoffice_domain_array;
    $workspaces[$fields['id']]['backoffice_domain_array'] = $backoffice_domain_array;

    if (trim($workspaces[$fields['id']]['frontoffice_domainlist']) == '')
    {
        $p_array = $workspaces[$fields['id']]['parents_array'];
        for ($i=sizeof($p_array)-1;$i>=0;$i--)
        {
            if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['frontoffice_domainlist']) != '')
            {
                $workspaces[$fields['id']]['frontoffice_domainlist'] = $workspaces[$p_array[$i]]['frontoffice_domainlist'];
                $workspaces[$fields['id']]['frontoffice_domain_array'] = $workspaces[$p_array[$i]]['frontoffice_domain_array'];
                break;
            }
        }
    }

    if (trim($workspaces[$fields['id']]['backoffice_domainlist']) == '')
    {
        $p_array = $workspaces[$fields['id']]['parents_array'];
        for ($i=sizeof($p_array)-1;$i>=0;$i--)
        {
            if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['backoffice_domainlist']) != '')
            {
                $workspaces[$fields['id']]['backoffice_domainlist'] = $workspaces[$p_array[$i]]['backoffice_domainlist'];
                $workspaces[$fields['id']]['backoffice_domain_array'] = $workspaces[$p_array[$i]]['backoffice_domain_array'];
                break;
            }
        }
    }
}

$_SESSION['ploopi']['allworkspaces'] = implode(',', array_keys($workspaces));

$host_array = array($_SESSION['ploopi']['host'], '*');
$_SESSION['ploopi']['hosts'] = array('frontoffice' => array(), 'backoffice' => array());

// on garde les id de groupes autorisés en fonction du domaine courant
foreach($workspaces as $gid => $grp)
{
    foreach($grp['frontoffice_domain_array'] as $domain)
    {
        if ($workspaces[$gid]['frontoffice'] && sizeof(array_intersect($workspaces[$gid]['frontoffice_domain_array'], $host_array)) && !in_array($gid, $_SESSION['ploopi']['hosts']['frontoffice'])) $_SESSION['ploopi']['hosts']['frontoffice'][] = $gid;
    }
    foreach($grp['backoffice_domain_array'] as $domain)
    {
        if ($workspaces[$gid]['backoffice'] && sizeof(array_intersect($workspaces[$gid]['backoffice_domain_array'], $host_array)) && !in_array($gid, $_SESSION['ploopi']['hosts']['backoffice'])) $_SESSION['ploopi']['hosts']['backoffice'][] = $gid;
    }
}

if (isset($_SESSION['ploopi']['hosts']['frontoffice'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['frontoffice'][0]];
if (isset($_SESSION['ploopi']['hosts']['backoffice'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['backoffice'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['backoffice'][0]];

foreach($_SESSION['ploopi']['workspaces'] as $wid => $wsp)
{
    $workspace = new workspace();
    $workspace->open($wid);
    
    $_SESSION['ploopi']['workspaces'][$wid]['children'] = $workspace->getchildren();
    $_SESSION['ploopi']['workspaces'][$wid]['parents'] = explode(';',$workspace->fields['parents']);
    $_SESSION['ploopi']['workspaces'][$wid]['brothers'] = $workspace->getbrothers();
    $_SESSION['ploopi']['workspaces'][$wid]['list_parents'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
    $_SESSION['ploopi']['workspaces'][$wid]['list_children'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['children']);
    $_SESSION['ploopi']['workspaces'][$wid]['list_brothers'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['brothers']);
    $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);        
}
    
?>