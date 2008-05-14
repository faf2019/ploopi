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
 * @license GPL
 */

///////////////////////////////////////////////////////////////////////////
// GET WORKSPACES (FOR THIS DOMAIN)
// on en profite pour appliquer l'héritage implicite des domaines pour les sous-espaces de travail
///////////////////////////////////////////////////////////////////////////

$select = "SELECT * FROM ploopi_workspace where system = 0 order by depth";
$db->query($select);

$workspaces = array();

while ($fields = $db->fetchrow())
{
    $web_domain_array = split("\r\n",preg_replace('/\s*/','', $fields['web_domainlist']));
    $admin_domain_array = split("\r\n",preg_replace('/\s*/','', $fields['admin_domainlist']));

    $workspaces[$fields['id']] = $fields;
    $workspaces[$fields['id']]['parents_array'] = split(';',$workspaces[$fields['id']]['parents']);
    $workspaces[$fields['id']]['web_domain_array'] = $web_domain_array;
    $workspaces[$fields['id']]['admin_domain_array'] = $admin_domain_array;

    if (trim($workspaces[$fields['id']]['web_domainlist']) == '')
    {
        $p_array = $workspaces[$fields['id']]['parents_array'];
        for ($i=sizeof($p_array)-1;$i>=0;$i--)
        {
            if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['web_domainlist']) != '')
            {
                $workspaces[$fields['id']]['web_domainlist'] = $workspaces[$p_array[$i]]['web_domainlist'];
                $workspaces[$fields['id']]['web_domain_array'] = $workspaces[$p_array[$i]]['web_domain_array'];
                break;
            }
        }
    }

    if (trim($workspaces[$fields['id']]['admin_domainlist']) == '')
    {
        $p_array = $workspaces[$fields['id']]['parents_array'];
        for ($i=sizeof($p_array)-1;$i>=0;$i--)
        {
            if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['admin_domainlist']) != '')
            {
                $workspaces[$fields['id']]['admin_domainlist'] = $workspaces[$p_array[$i]]['admin_domainlist'];
                $workspaces[$fields['id']]['admin_domain_array'] = $workspaces[$p_array[$i]]['admin_domain_array'];
                break;
            }
        }
    }
}

$_SESSION['ploopi']['allworkspaces'] = implode(',', array_keys($workspaces));

$host_array = array($_SESSION['ploopi']['host'], '*');
$_SESSION['ploopi']['hosts'] = array('web' => array(), 'admin' => array());

// on garde les id de groupes autorisés en fonction du domaine courant
foreach($workspaces as $gid => $grp)
{
    foreach($grp['web_domain_array'] as $domain)
    {
        if ($workspaces[$gid]['web'] && sizeof(array_intersect($workspaces[$gid]['web_domain_array'], $host_array)) && !in_array($gid, $_SESSION['ploopi']['hosts']['web'])) $_SESSION['ploopi']['hosts']['web'][] = $gid;
    }
    foreach($grp['admin_domain_array'] as $domain)
    {
        if ($workspaces[$gid]['admin'] && sizeof(array_intersect($workspaces[$gid]['admin_domain_array'], $host_array)) && !in_array($gid, $_SESSION['ploopi']['hosts']['admin'])) $_SESSION['ploopi']['hosts']['admin'][] = $gid;
    }
}

if (isset($_SESSION['ploopi']['hosts']['web'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['web'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['web'][0]];
if (isset($_SESSION['ploopi']['hosts']['admin'][0])) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['admin'][0]] = $workspaces[$_SESSION['ploopi']['hosts']['admin'][0]];

foreach($_SESSION['ploopi']['workspaces'] as $wid => $wsp)
{
    $workspace = new workspace();
    $workspace->open($wid);
    
    $_SESSION['ploopi']['workspaces'][$wid]['children']  = $workspace->getworkspacechildrenlite();
    $_SESSION['ploopi']['workspaces'][$wid]['parents'] = explode(';',$workspace->fields['parents']);
    $_SESSION['ploopi']['workspaces'][$wid]['brothers']  = $workspace->getworkspacebrotherslite();
    $_SESSION['ploopi']['workspaces'][$wid]['list_parents'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
    $_SESSION['ploopi']['workspaces'][$wid]['list_children'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['children']);
    $_SESSION['ploopi']['workspaces'][$wid]['list_brothers'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['brothers']);
    $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);        
}
    
?>