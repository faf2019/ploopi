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
 * Fonctions du module Système
 *
 * @package system
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 *
 * @param unknown_type $array1
 * @param unknown_type $array2
 * @return unknown
 */

function system_mergegroups($array1, $array2)
{
    foreach($array2 as $k => $v) $array1[$k] = $v;
    return($array1);
}

/**
 * Retourne l'arborescence complète des espaces de travail et des groupes d'utilisateurs
 *
 * @return array tableau contenant le tableau des espaces de travail et le tableau des groupes
 */
function system_getwg()
{
    $db = ploopi\db::get();

    $workspaces = array('list' => array(), 'tree' => array(), 'workspaceid' => $_SESSION['ploopi']['workspaceid']);

    /**
     * Recherche de tous les espaces
     */

    $select = "SELECT * FROM ploopi_workspace ORDER BY depth,label";

    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        /**
         * true si l'espace est ajouté à l'arbre des espaces
         */
        $add = true;

        /**
         * Test du niveau d'accréditation (adminlevel) pour déterminer quels sont les espaces accessibles pour l'utilisateur
         */
        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN)
        {
            /**
             * L'utilisateur n'est pas administrateur système => on filtre sur les espaces accessibles
             */
            $array_parents = explode(';',$fields['parents']);
            if (!($fields['id'] == $_SESSION['ploopi']['workspaceid'] || in_array($_SESSION['ploopi']['workspaceid'],$array_parents))) $add = false;
        }

        if ($add)
        {
            /**
             * les propriétés "groups" & "groups_shared" sont remplies par la fonction qui traite les groupes "system_getgroups()"
             */
            $fields['groups'] = array();
            $fields['groups_shared'] = array();
            $workspaces['list'][$fields['id']] = $fields;
            $workspaces['tree'][$fields['id_workspace']][] = $fields['id'];
        }
    }

    /**
     * Construction de l'arbre des groupes, complétion de l'arbre des espaces
     */

    $groups = array('list' => array(), 'tree' => array(), 'workspace_tree' => array(), 'workspaceid' => $_SESSION['ploopi']['workspaceid']);

    $select = "SELECT * FROM ploopi_group WHERE system = 0 ORDER BY depth,label";
    $result = $db->query($select);

    while ($fields = $db->fetchrow($result))
    {
        // Vérification du droit d'accès au groupe
        $add = false;
        // Espace d'appartenance du groupe (si existe)
        if (!empty($fields['id_workspace']) && isset($workspaces['list'][$fields['id_workspace']])) $add = true;
        // Espace d'appartenance des parents du groupe
        if (!$add) {
            foreach(array_reverse(explode(';', $fields['parents'])) as $idg) {
                if (!$add && isset($groups['list'][$idg])) {
                    if (!empty($groups['list'][$idg]['id_workspace']) && isset($workspaces['list'][$groups['list'][$idg]['id_workspace']])) $add = true;
                }
            }
        }

        if ($add) {
            $fields['parents_workspace'] = '';
            $fields['groups'] = array();
            $groups['list'][$fields['id']] = $fields;
            $groups['tree'][$fields['id_group']][] = $fields['id'];
            // Groupe attaché à un espace (existant / autorisé)
            if (!empty($fields['id_workspace']) && isset($workspaces['list'][$fields['id_workspace']]))
            {
                $groups['workspace_tree'][$fields['id_workspace']][] = $fields['id'];
                $workspaces['list'][$fields['id_workspace']]['groups'][$fields['id']] = 0;
                if ($groups['list'][$fields['id']]['shared']) $workspaces['list'][$fields['id_workspace']]['groups_shared'][$fields['id']] = 0;

                // code remplacé par la boucle ci dessous... semble plus rapide...
                //$groups['list'][$fields['id']]['parents_workspace'] = $workspaces['list'][$fields['id_workspace']]['parents'].";{$fields['id_workspace']};{$fields['id']}";
            }
        }
    }

    // $groups['workspace_tree'] contient l'arbre de rattachement des groupes aux espaces
    // => mise à jour du lien parents pour chaque groupe rattaché à un espace (le lien parents contient les id des parents séparés par des ";"
    foreach($groups['workspace_tree'] as $idw => $list_idg)
    {
        foreach($list_idg as $idg)
        {
            //if (isset($workspaces['list'][$idw])) $groups['list'][$idg]['parents_workspace'] = $workspaces['list'][$idw]['parents'].";{$idw};{$idg}";
            if (isset($workspaces['list'][$idw])) $groups['list'][$idg]['parents_workspace'] = $workspaces['list'][$idw]['parents'].";{$idw}";
        }
    }

    // Application de l'héritage du lien de parenté entre un espace et un groupe et aux sous groupes
    // Ainsi, chaque groupe connaît ses espaces parents
    foreach($groups['tree'] as $idg => $list_idg)
    {
        foreach($list_idg as $idg_child)
        {
            if (isset($groups['list'][$idg]))
            {
                $groups['list'][$idg_child]['parents_workspace'] = $groups['list'][$idg]['parents_workspace'];
            }
        }
    }

    foreach($workspaces['list'] as $idw => $workspace)
    {
        // récupération des sous-groupes
        // on met à jour le champ 'group' de workspaces pour y inclure les sous-groupes des groupes déjà rattachés
        foreach($workspaces['list'][$idw]['groups'] as $idg)
        {
            if (isset($groups['tree'][$idg]))
            {
                foreach($groups['tree'][$idg] as $idg2)
                {
                    $workspaces['list'][$idw]['groups'][$idg2] = 0;
                    if ($groups['list'][$idg2]['shared']) $workspaces['list'][$idw]['groups_shared'][$idg2] = 0;
                }
            }
        }

        // Héritage des partages
        // Des groupes peuvent être partagés par les espaces parents => on les rattache aussi comme groupes de l'espace
        if (isset($workspaces['tree'][$idw]) && !empty($workspaces['list'][$idw]['groups']))
        {
            foreach($workspaces['tree'][$idw] as $idw2)
            {
                $workspaces['list'][$idw2]['groups_shared'] = system_mergegroups($workspaces['list'][$idw2]['groups_shared'], $workspaces['list'][$idw]['groups_shared']);
                $workspaces['list'][$idw2]['groups'] = system_mergegroups($workspaces['list'][$idw2]['groups'], $workspaces['list'][$idw2]['groups_shared']);
            }
        }

        // application des partages de groupes aux groupes
        $workspace = $workspaces['list'][$idw];
        foreach(array_keys($workspace['groups']) as $idg)
        {
            $groups['list'][$idg]['groups'] = system_mergegroups($groups['list'][$idg]['groups'], $workspace['groups']);
        }
    }

    // application des partages de groupes aux sous groupes
    foreach($groups['list'] as $idg => $fields)
    {
        $grouplist = [];
        if ($fields['shared']) {
            $oidg = $idg;
            while ($idg !== false && !empty($groups['tree'][$idg])) {
                foreach($groups['tree'][$idg] as $g) {
                    $grouplist[] = $g;
                    $groups['list'][$g]['groups'][$oidg] = 0;
                }
                $idg = next($grouplist);
            }
        }
    }



    return(array(&$workspaces, &$groups));
}


/**
 * Construit l'arbre navigable des espaces/groupes
 *
 * @param unknown_type $typetree
 * @param unknown_type $from_wid
 * @param unknown_type $from_gid
 * @return unknown
 */

function system_build_tree($typetree, $from_wid = 1, $from_gid = 0)
{
    global $workspaces;
    global $groups;
    global $workspaceid;
    global $groupid;

    $html = '';

    if (!empty($workspaceid) && isset($workspaces['list'][$workspaceid])) $workspacesel = $workspaces['list'][$workspaceid];
    if (!empty($groupid) && isset($groups['list'][$groupid])) $groupsel = $groups['list'][$groupid];

    switch($typetree)
    {
        case 'workspaces':
            $html = '';

            // groups
            if (isset($groups['workspace_tree'][$from_wid]))
            {
                $c=0;
                foreach($groups['workspace_tree'][$from_wid] as $gid)
                {
                    $group = $groups['list'][$gid];

                    $isgroupsel = (!empty($groupid) && ($groupid == $gid));

                    $gselparents = (isset($groupsel)) ? explode(';',$groupsel['parents']) : array();
                    $testparents = explode(';',$group['parents']);
                    $testparents[] = $group['id'];

                    // group opened if parents array intersects
                    $isgroupopened = sizeof(array_intersect_assoc($gselparents, $testparents)) == sizeof($testparents);
                    $islast = ((!isset($groups['workspace_tree'][$from_wid]) || $c == sizeof($groups['workspace_tree'][$from_wid])-1) && !isset($workspaces['tree'][$from_wid]));

                    $node = '';
                    $bg = '';

                    if ($isgroupsel) $style_sel = 'bold';
                    else $style_sel = 'none';

                    $icon = 'group';

                    if ($workspaces['list'][$from_wid]['depth'] >= 2)
                    {
                        $typenode = 'join';
                        if (isset($groups['tree'][$gid]))
                        {
                            if ($isgroupsel || $isgroupopened) $typenode = 'minus';
                            else $typenode = 'plus';
                        }

                        if (!$islast)
                        {
                            $typenode .= 'bottom';
                            $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                        }

                        $node = "<a onclick=\"javascript:system_showgroup('groups', '{$gid}', '');\" href=\"javascript:void(0);\"><img id=\"ng{$group['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$typenode}.png\" /></a>";
                    }

                    $html_rec = '';

                    if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid);

                    $display = ($html_rec == '') ? 'none' : 'block';

                    if ($workspaces['list'][$from_wid]['depth'] < 2) $marginleft = 0;
                    else $marginleft = 20;

                    $html .=    "
                                <div class=\"system_tree_node\" style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a class=\"system_tree_link".($style_sel == 'bold' ? ' selected' : '')."\" style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?groupid={$group['id']}")."\">".ploopi\str::htmlentities($group['label'])."</a>
                                        </span>
                                    </div>
                                    <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"g{$group['id']}\">{$html_rec}</div>
                                </div>
                                ";
                    $c++;
                }
            }

            // workspaces
            if (isset($workspaces['tree'][$from_wid]))
            {
                $c=0;
                foreach($workspaces['tree'][$from_wid] as $wid)
                {
                    $workspace = $workspaces['list'][$wid];
                    $isworkspacesel = (!empty($workspaceid) && ($workspaceid == $wid));

                    $wselparents = (isset($workspacesel)) ? explode(';',$workspacesel['parents'].';'.$workspacesel['id']) : explode(';',$groupsel['parents_workspace']);
                    //echo $workspace['label'];
                    //ploopi\output::print_r($groupsel);
                    //ploopi\output::print_r($wselparents);
                    //$gselparents = (isset($workspacesel)) ? explode(';',$workspacesel['parents'].';'.$workspacesel['id']) : explode(';',$groupsel['parents_workspace'].';g'.$groupsel['id']);
                    $currentparents = explode(';',$workspace['parents'].';'.$workspace['id']);

                    // workspace opened if parents array intersects
                    $isworkspaceopened = sizeof(array_intersect_assoc($wselparents, $currentparents)) == sizeof($currentparents);

                    $islast = (!isset($workspaces['tree'][$from_wid]) || $c == sizeof($workspaces['tree'][$from_wid])-1);

                    $node = '';
                    $bg = '';

                    if ($isworkspacesel) $style_sel = 'bold';
                    else $style_sel = 'none';

                    $icon = ($workspace['frontoffice']) ? 'workspace-frontoffice' : 'workspace';

                    if ($workspace['depth'] == 2 || ($workspace['id'] == $_SESSION['ploopi']['workspaceid'] && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN)) {/* racine */}
                    else
                    {
                        $typenode = 'join';
                        if (isset($workspaces['tree'][$wid]) || isset($groups['workspace_tree'][$wid]))
                        {
                            if ($isworkspacesel || $isworkspaceopened) $typenode = 'minus';
                            else $typenode = 'plus';
                        }

                        if (!$islast)
                        {
                            $typenode .= 'bottom';
                            $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                        }

                        $node = "<a onclick=\"javascript:system_showgroup('workspaces', '{$wid}', '');\" href=\"javascript:void(0);\"><img id=\"nw{$workspace['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$typenode}.png\" /></a>";
                    }

                    $html_rec = '';
                    if ($isworkspacesel || $isworkspaceopened || $workspace['depth'] == 2)  $html_rec .= system_build_tree('workspaces', $wid, 0);

                    $display = ($html_rec == '') ? 'none' : 'block';

                    if ($workspace['depth'] == 2 || ($workspace['id'] == $_SESSION['ploopi']['workspaceid']  && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN)) $marginleft = 0;
                    else $marginleft = 20;

                    $html .=    "
                                <div class=\"system_tree_node\" style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a class=\"system_tree_link".($style_sel == 'bold' ? ' selected' : '')."\" style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?workspaceid={$workspace['id']}")."\">".ploopi\str::htmlentities($workspace['label'])."</a>
                                        </span>
                                    </div>
                                    <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"w{$workspace['id']}\">{$html_rec}</div>
                                </div>
                                ";
                    $c++;
                }
            }

        break;

        case 'groups':
            if ($from_gid == 0) $from_gid = 1;

            if (!empty($groupid)) $groupsel = $groups['list'][$groupid];

            if (isset($groups['tree'][$from_gid]))
            {
                $c=0;
                foreach($groups['tree'][$from_gid] as $gid)
                {
                    $group = $groups['list'][$gid];
                    if (!$group['id_workspace'])
                    {
                        $isgroupsel = (!empty($groupid) && ($groupid == $gid));

                        $gselparents = (isset($groupsel)) ? explode(';',$groupsel['parents'].';g'.$groupsel['id']) : array();
                        $testparents = explode(';',$group['parents'].';g'.$group['id']);

                        $gselparents = (isset($groupsel)) ? explode(';',$groupsel['parents']) : array();
                        $testparents = explode(';',$group['parents']);
                        $testparents[] = $group['id'];

                        // group opened if parents array intersects
                        $isgroupopened = sizeof(array_intersect_assoc($gselparents, $testparents)) == sizeof($testparents);
                        $islast = (!isset($groups['tree'][$from_gid]) || $c == sizeof($groups['tree'][$from_gid])-1);

                        $node = '';
                        $bg = '';

                        if ($isgroupsel) $style_sel = 'bold';
                        else $style_sel = 'none';

                        $icon = 'group';

                        if ($group['depth'] > 2)
                        {
                            $typenode = 'join';
                            if (isset($groups['tree'][$gid]))
                            {
                                if ($isgroupsel || $isgroupopened) $typenode = 'minus';
                                else $typenode = 'plus';
                            }

                            if (!$islast)
                            {
                                $typenode .= 'bottom';
                                $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                            }

                            $node = "<a onclick=\"javascript:system_showgroup('groups', '{$gid}', '');\" href=\"javascript:void(0);\"><img id=\"ng{$group['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$typenode}.png\" /></a>";
                        }

                        $html_rec = '';

                        if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid);

                        $display = ($html_rec == '') ? 'none' : 'block';

                        $html .=    "
                                    <div class=\"system_tree_node\" style=\"overflow:auto;{$bg}\">
                                        <div>
                                            {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                            <span style=\"display:block;margin-left:40px;line-height:18px;\">
                                                <a class=\"system_tree_link".($style_sel == 'bold' ? ' selected' : '')."\" style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?groupid={$group['id']}")."\">".ploopi\str::htmlentities($group['label'])."</a>
                                            </span>
                                        </div>
                                        <div style=\"margin-left:20px;display:{$display};\" id=\"g{$group['id']}\">{$html_rec}</div>
                                    </div>
                                    ";
                        $c++;
                    }
                }
            }

        break;
    }

    return $html;
}

/**
 * Arborescence des espaces et des groupes sous forme de données (écrans des
 * gabarits de génération 4). Même parcours que system_build_tree() : sous un
 * espace, ses groupes puis ses sous-espaces ; sous un groupe, ses sous-groupes.
 *
 * @param string $typetree 'workspaces' ou 'groups'
 * @param int $from_wid espace de départ
 * @param int $from_gid groupe de départ
 * @return array nœuds : type ('workspace'|'group'), id, label, frontoffice, children
 */

function system_tree_nodes($typetree, $from_wid = 1, $from_gid = 0)
{
    global $workspaces;
    global $groups;

    $arrNodes = array();

    if ($typetree == 'workspaces')
    {
        if (isset($groups['workspace_tree'][$from_wid]))
        {
            foreach($groups['workspace_tree'][$from_wid] as $gid)
            {
                $arrNodes[] = array(
                    'type' => 'group',
                    'id' => $gid,
                    'label' => $groups['list'][$gid]['label'],
                    'children' => system_tree_nodes('groups', 0, $gid)
                );
            }
        }

        if (isset($workspaces['tree'][$from_wid]))
        {
            foreach($workspaces['tree'][$from_wid] as $wid)
            {
                $arrNodes[] = array(
                    'type' => 'workspace',
                    'id' => $wid,
                    'label' => $workspaces['list'][$wid]['label'],
                    'frontoffice' => !empty($workspaces['list'][$wid]['frontoffice']),
                    'children' => system_tree_nodes('workspaces', $wid, 0)
                );
            }
        }
    }
    else
    {
        if ($from_gid == 0) $from_gid = 1;

        if (isset($groups['tree'][$from_gid]))
        {
            foreach($groups['tree'][$from_gid] as $gid)
            {
                if (!empty($groups['list'][$gid]['id_workspace'])) continue;

                $arrNodes[] = array(
                    'type' => 'group',
                    'id' => $gid,
                    'label' => $groups['list'][$gid]['label'],
                    'children' => system_tree_nodes('groups', 0, $gid)
                );
            }
        }
    }

    return $arrNodes;
}

/**
 * Nombre d'utilisateurs rattachés directement à chaque espace et à chaque groupe
 *
 * @return array ['workspace' => [id => nb], 'group' => [id => nb]]
 */

function system_tree_counts()
{
    $db = ploopi\db::get();
    $arrCounts = array('workspace' => array(), 'group' => array());

    $rs = $db->query("SELECT id_workspace, COUNT(*) AS nb FROM ploopi_workspace_user GROUP BY id_workspace");
    while ($row = $db->fetchrow($rs)) $arrCounts['workspace'][$row['id_workspace']] = (int) $row['nb'];

    $rs = $db->query("SELECT id_group, COUNT(*) AS nb FROM ploopi_group_user GROUP BY id_group");
    while ($row = $db->fetchrow($rs)) $arrCounts['group'][$row['id_group']] = (int) $row['nb'];

    return $arrCounts;
}

/**
 * Indique si l'utilisateur connecté peut administrer un utilisateur : administrateur
 * système, ou utilisateur rattaché (directement ou par un groupe) à un espace ou à
 * un groupe que l'administrateur courant voit dans l'arborescence (system_getwg).
 *
 * @param int $intUserId identifiant de l'utilisateur
 * @return boolean true si l'utilisateur est administrable
 */

function system_user_isadministrable($intUserId)
{
    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN) return true;
    if (!ploopi\acl::ismanager()) return false;

    list($arrWorkspaces, $arrGroups) = system_getwg();

    $db = ploopi\db::get();
    $intUserId = (int) $intUserId;

    $rs = $db->query("SELECT id_workspace FROM ploopi_workspace_user WHERE id_user = {$intUserId}");
    while ($row = $db->fetchrow($rs)) if (isset($arrWorkspaces['list'][$row['id_workspace']])) return true;

    $rs = $db->query("SELECT id_group FROM ploopi_group_user WHERE id_user = {$intUserId}");
    while ($row = $db->fetchrow($rs)) if (isset($arrGroups['list'][$row['id_group']])) return true;

    return false;
}

/**
 * Niveau le plus élevé d'utilisateurs dans les espaces : rattachement direct
 * (ploopi_workspace_user) ou par un groupe (ploopi_workspace_group). Un rattachement à
 * un groupe ne porte pas de niveau : c'est celui-ci qui compte pour le détacher.
 *
 * @param array $arrUserIds identifiants des utilisateurs
 * @return array niveau le plus élevé par identifiant (0 si aucun rattachement)
 */

function system_users_maxlevel($arrUserIds)
{
    $arrUserIds = array_unique(array_map('intval', $arrUserIds));
    $arrLevels = array_fill_keys($arrUserIds, 0);
    if (empty($arrUserIds)) return $arrLevels;

    $db = ploopi\db::get();
    $strIds = implode(',', $arrUserIds);

    $rs = $db->query("
        SELECT  id_user, MAX(adminlevel) AS adminlevel FROM ploopi_workspace_user WHERE id_user IN ({$strIds}) GROUP BY id_user
        UNION ALL
        SELECT  gu.id_user, MAX(wg.adminlevel) FROM ploopi_group_user gu INNER JOIN ploopi_workspace_group wg ON wg.id_group = gu.id_group WHERE gu.id_user IN ({$strIds}) GROUP BY gu.id_user
    ");
    while ($row = $db->fetchrow($rs)) $arrLevels[$row['id_user']] = max($arrLevels[$row['id_user']], (int) $row['adminlevel']);

    return $arrLevels;
}

/**
 * Indique si l'administrateur connecté peut détacher un utilisateur d'un groupe : le
 * niveau le plus élevé de l'utilisateur dans les espaces ne dépasse pas le sien (tous
 * pour l'administrateur système)
 *
 * @param int $intUserId identifiant de l'utilisateur
 * @return boolean true si l'utilisateur peut être détaché
 */

function system_user_isdetachable($intUserId)
{
    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN) return true;

    $arrLevels = system_users_maxlevel(array($intUserId));

    return $arrLevels[(int) $intUserId] <= $_SESSION['ploopi']['adminlevel'];
}

/**
 * Identifiants des groupes dont les utilisateurs peuvent être rattachés à l'entité
 * courante de l'administration (groupe ou espace), pour un administrateur qui n'est
 * pas administrateur système : mêmes règles que la liste « Rattacher un utilisateur ».
 *
 * @return array identifiants des groupes
 */

function system_attach_groups()
{
    global $workspaces, $groups, $workspaceid, $groupid;

    if ($_SESSION['system']['level'] == _SYSTEM_GROUPS) return empty($groups['list'][$groupid]['groups']) ? array() : array_map('intval', array_keys($groups['list'][$groupid]['groups']));

    return empty($workspaces['list'][$workspaceid]['groups']) ? array() : array_map('intval', array_keys($workspaces['list'][$workspaceid]['groups']));
}

/**
 * Indique si un utilisateur peut être rattaché à l'entité courante de l'administration
 * (groupe ou espace) : il n'y est pas déjà, et il appartient à un groupe que
 * l'administrateur peut proposer (tous pour l'administrateur système). Dans un groupe,
 * les utilisateurs rattachés à aucun groupe sont aussi proposés.
 *
 * @param int $intUserId identifiant de l'utilisateur
 * @return boolean true si l'utilisateur peut être rattaché
 */

function system_user_isattachable($intUserId)
{
    global $workspaceid, $groupid;

    $db = ploopi\db::get();
    $intUserId = (int) $intUserId;

    if (!$db->numrows($db->query("SELECT id FROM ploopi_user WHERE id = {$intUserId}"))) return false;

    if ($_SESSION['system']['level'] == _SYSTEM_GROUPS)
    {
        if ($db->numrows($db->query("SELECT id_user FROM ploopi_group_user WHERE id_group = ".((int) $groupid)." AND id_user = {$intUserId}"))) return false;
        if (!$db->numrows($db->query("SELECT id_user FROM ploopi_group_user WHERE id_user = {$intUserId}"))) return true;
    }
    else
    {
        if ($db->numrows($db->query("SELECT id_user FROM ploopi_workspace_user WHERE id_workspace = ".((int) $workspaceid)." AND id_user = {$intUserId}"))) return false;
    }

    if (ploopi\acl::isadmin()) return (bool) $db->numrows($db->query("SELECT id_user FROM ploopi_group_user WHERE id_user = {$intUserId}"));

    $arrAllowed = system_attach_groups();
    if (empty($arrAllowed)) return false;

    return (bool) $db->numrows($db->query("SELECT id_user FROM ploopi_group_user WHERE id_user = {$intUserId} AND id_group IN (".implode(',', $arrAllowed).")"));
}

/**
 * Indique si un groupe peut être rattaché à l'espace courant de l'administration :
 * groupe non système, pas déjà rattaché, et proposé à l'administrateur (tous pour
 * l'administrateur système).
 *
 * @param int $intGroupId identifiant du groupe
 * @return boolean true si le groupe peut être rattaché
 */

function system_group_isattachable($intGroupId)
{
    global $workspaces, $workspaceid;

    $db = ploopi\db::get();
    $intGroupId = (int) $intGroupId;

    if (!$db->numrows($db->query("SELECT id FROM ploopi_group WHERE id = {$intGroupId} AND system = 0"))) return false;
    if ($db->numrows($db->query("SELECT id_group FROM ploopi_workspace_group WHERE id_workspace = ".((int) $workspaceid)." AND id_group = {$intGroupId}"))) return false;
    if (ploopi\acl::isadmin()) return true;

    return isset($workspaces['list'][$workspaceid]['groups'][$intGroupId]);
}

function system_getallworkspaces($idworkspacetop = '')
{
    $db = ploopi\db::get();
    $workspaces = array();

    $select = "SELECT * FROM ploopi_workspace WHERE system = 0 ORDER BY label";
    $result = $db->query($select);
    while ($fields = $db->fetchrow($result))
    {
        $workspaces[$fields['id_workspace']][$fields['id']] = $fields;
    }

    $ar = array();
    $depth = system_getallworkspacesrec($ar, $workspaces, _PLOOPI_SYSTEMGROUP, 0, $idworkspacetop);
    return($ar);
}

function system_updateparents($idgroup=0,$parents='',$depth=1)
{
    $db = ploopi\db::get();

    $select = "SELECT * FROM ploopi_group WHERE id_group = $idgroup AND id <> $idgroup";
    $result = $db->query($select);

    if ($parents!='') $parents .= ';';
    $parents .= $idgroup;

    while ($fields = $db->fetchrow($result))
    {
        $update = "UPDATE ploopi_group SET parents = '$parents', depth = $depth WHERE id = $fields[id]";
        $db->query($update);
        system_updateparents($fields['id'],$parents,$depth+1);
    }
}

function system_getinstalledmodules()
{
    $db = ploopi\db::get();

    $modules = array();

    $select =   "
                SELECT      *
                FROM        ploopi_module_type
                WHERE       system != 1
                ORDER BY    label
                ";

    $result = $db->query($select);

    $i = 0;

    while ($moduletype = $db->fetchrow($result))
    {
        $modules[$moduletype['id']] = $moduletype;
    }

    return $modules;
}



function system_generate_htpasswd($login, $pass, $delete = false)
{
    $content = '';
    $res = '';

    if (file_exists('.htpasswd') && is_readable('.htpasswd'))
    {
        if ($handle = fopen('.htpasswd', 'r'))
        {
            while (!feof($handle)) $content .= fgets($handle, 4096);
            fclose($handle);
        }
    }

    if (is_writable('.'))
    {
        $handle = fopen('.htpasswd', 'w');

        $array_content = preg_split("/\r\n/", $content);

        $array_pass = array();
        foreach($array_content as $line_content)
        {
            if (trim($line_content) != '')
            {
                list($ht_login, $ht_pass) = preg_split("/:/", $line_content);
                $array_pass[$ht_login] = $ht_pass;
            }
        }

        if ($delete && isset($array_pass[$login])) unset($array_pass[$login]);
        else $array_pass[$login] = ploopi\crypt::htpasswd($pass);

        $c = 0;
        foreach($array_pass as $ht_login => $ht_pass)
        {
            if ($c++) $res .= "\r\n";
            $res .= "$ht_login:$ht_pass";
        }

        fwrite($handle, $res);
    }
}

function system_tickets_displayresponses($parents, $tickets, $rootid)
{
    sort($parents[$rootid]);

    $todaydate = ploopi\date::timestamp2local(ploopi\date::createtimestamp());

    foreach($parents[$rootid] as $ticketid)
    {
        $fields = $tickets[$ticketid];

        $localdate = ploopi\date::timestamp2local($fields['timestp']);
        $localdate['date'] = ($todaydate['date'] == $localdate['date'])  ? "Aujourd'hui" : "le {$localdate['date']}";

        $puce = '#ff2020';
        /*
        if (!$fields['opened']) $puce = '#ff2020';
        elseif (!$fields['done']) $puce = '#2020ff';
        else $puce = '#20ff20';
        */

        ?>
        <div class="system_tickets_response">
            <div class="system_tickets_head" onclick="javascript:system_tickets_display(<?php echo $fields['id']; ?>,<?php echo (empty($fields['status'])) ? 0 : 1; ?>, 0);">
                <div  class="system_tickets_date"><?php echo ploopi\str::htmlentities($localdate['date']); ?> à <?php echo ploopi\str::htmlentities($localdate['time']); ?></div>
                <div class="system_tickets_sender"><b><?php echo ploopi\str::htmlentities("{$fields['firstname']} {$fields['lastname']}"); ?></b></div>
                <div class="system_tickets_title" id="tickets_title_<?php echo $fields['id']; ?>" <?php if (is_null($fields['status'])) echo 'style="font-weight:bold;"'; ?>><?php echo ploopi\str::htmlentities($fields['title']); ?></div>
            </div>

            <div class="system_tickets_response_detail" id="tickets_detail_<?php echo $fields['id'];?>">
                <div class="system_tickets_message">
                <?php
                echo ploopi\str::make_links($fields['message']);
                if ($fields['lastedit_timestp'])
                {
                    $lastedit_local = ploopi\date::timestamp2local($fields['lastedit_timestp']);
                    echo "<i>Dernière modification le {$lastedit_local['date']} à {$lastedit_local['time']}</i>";
                }
                ?>
                </div>
                <div class="system_tickets_buttons">
                    <p class="ploopi_va">
                        <a href="javascript:void(0);" onclick="javascript:ploopi.popup.show('','550',event,'click','system_popupticket');ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_replyto&ticket_id=<?php echo $fields['id']; ?>', 'system_popupticket');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_reply.png">Répondre</a>
                        <?php
                        /* <a href="javascript:void(0);" onclick="javascript:ploopi.popup.show('','400',event,'click','system_popupticket');ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_replyto&ticket_id=<?php echo $fields['id']; ?>&quoted=true', 'system_popupticket');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_quote.png">Citer</a> */
                        if ($fields['sender_uid'] == $_SESSION['ploopi']['userid'])
                        {
                            ?>
                            <a href="javascript:void(0);" onclick="javascript:ploopi.popup.show('','550',event,'click','system_popupticket');ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_modify&ticket_id=<?php echo $fields['id']; ?>', 'system_popupticket');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_modify.png">Modifier</a>
                            <?php
                        }
                        ?>
                    </p>
                </div>
            </div>
            <div>
            <?php
                if (isset($parents[$ticketid])) system_tickets_displayresponses($parents, $tickets, $ticketid);
            ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Réponses d'un message, gabarits de génération 4 : fil de réponses dépliables
 * (expéditeur, date, message, Répondre / Modifier), imbriquées comme les réponses.
 *
 * Mêmes données que system_tickets_displayresponses(), mêmes opérations historiques
 * (tickets_replyto, tickets_modify) par include/p4.js.
 *
 * @param array $parents identifiants des réponses par message parent
 * @param array $tickets réponses par identifiant
 * @param int $rootid message dont on affiche les réponses
 */

function system_tickets_p4_responses($parents, $tickets, $rootid)
{
    sort($parents[$rootid]);

    $arrToday = ploopi\date::timestamp2local(ploopi\date::createtimestamp());

    echo '<ul class="p4-thread">';

    foreach($parents[$rootid] as $intTicketId)
    {
        $arrFields = $tickets[$intTicketId];
        $intId = (int) $arrFields['id'];

        $arrLocal = ploopi\date::timestamp2local($arrFields['timestp']);
        $strDate = ($arrLocal['date'] == $arrToday['date'] ? _SYSTEM_MSG_TODAY : $arrLocal['date']).' '.substr($arrLocal['time'], 0, 5);
        $strSender = trim("{$arrFields['firstname']} {$arrFields['lastname']}");
        $strInitials = mb_strtoupper(mb_substr($arrFields['firstname'], 0, 1).mb_substr($arrFields['lastname'], 0, 1));
        $booUnread = is_null($arrFields['status']);

        $arrActs = array(array('label' => _SYSTEM_MSG_REPLY, 'icon' => 'arrow-back-up', 'class' => 'ghost sm', 'attrs' => array('data-p4-ticket-open' => "ploopi_op=tickets_replyto&ticket_id={$intId}")));
        if ($arrFields['sender_uid'] == $_SESSION['ploopi']['userid']) $arrActs[] = array('label' => _SYSTEM_LABEL_MODIFY, 'icon' => 'pencil', 'class' => 'ghost sm', 'attrs' => array('data-p4-ticket-open' => "ploopi_op=tickets_modify&ticket_id={$intId}"));
        ?>
        <li class="p4-thread-item<?php if ($booUnread) echo ' p4-ticket-new'; ?>" data-p4-ticket-item="<?php echo $intId; ?>">
            <button type="button" class="p4-ticket-toggle" data-p4-ticket="<?php echo $intId; ?>" data-p4-ticket-reply aria-expanded="false" aria-controls="tickets_detail_<?php echo $intId; ?>">
                <span class="p4-avatar p4-avatar-sm p4-av-<?php echo (ord($strInitials) ?: 0) % 5; ?>"><?php echo ploopi\system\ui::e($strInitials); ?></span>
                <span class="p4-ticket-main">
                    <span class="p4-ticket-from"><?php echo ploopi\system\ui::e($strSender); ?></span>
                    <span class="p4-ticket-title<?php if ($booUnread) echo ' p4-unread'; ?>" id="tickets_title_<?php echo $intId; ?>"><?php echo ploopi\system\ui::e(html_entity_decode(strip_tags($arrFields['title']), ENT_QUOTES, 'UTF-8')); ?></span>
                </span>
                <span class="p4-ticket-meta">
                    <time class="p4-ticket-date"><?php echo ploopi\system\ui::e($strDate); ?></time>
                    <span class="p4-ticket-unreaddot" title="<?php echo ploopi\system\ui::e(_SYSTEM_MSG_NEW); ?>"></span>
                </span>
            </button>
            <div class="p4-thread-detail" id="tickets_detail_<?php echo $intId; ?>" hidden>
                <div class="p4-ticket-message p4-legacy"><?php echo ploopi\str::make_links($arrFields['message']); ?></div>
                <?php
                if ($arrFields['lastedit_timestp'])
                {
                    $arrEdit = ploopi\date::timestamp2local($arrFields['lastedit_timestp']);
                    echo '<p class="p4-text-xs p4-muted">'.ploopi\system\ui::e(str_replace(array('<DATE>', '<TIME>'), array($arrEdit['date'], $arrEdit['time']), _SYSTEM_MSG_EDITED_ON)).'</p>';
                }
                echo '<div class="p4-row p4-wrap p4-ticket-actions">'.ploopi\system\ui::actions($arrActs).'</div>';
                ?>
            </div>
            <?php if (isset($parents[$intTicketId])) system_tickets_p4_responses($parents, $tickets, $intTicketId); ?>
        </li>
        <?php
    }

    echo '</ul>';
}

function system_serverload_getcolor($min, $max, $x)
{
    // Du vert (valeur minimale) au rouge (valeur maximale), en teintes claires
    // sur lesquelles le texte reste lisible (TSL : saturation 60 %, luminosité 82 %)
    $fltRatio = ($max > $min) ? max(0, min(1, ($x - $min) / ($max - $min))) : 0;
    $fltTeinte = 130 * (1 - $fltRatio);
    $fltSat = 0.60;
    $fltLum = 0.82;

    $fltC = (1 - abs(2 * $fltLum - 1)) * $fltSat;
    $fltX = $fltC * (1 - abs(fmod($fltTeinte / 60, 2) - 1));
    $fltM = $fltLum - $fltC / 2;

    if ($fltTeinte < 60) list($r, $v, $b) = array($fltC, $fltX, 0);
    elseif ($fltTeinte < 120) list($r, $v, $b) = array($fltX, $fltC, 0);
    else list($r, $v, $b) = array(0, $fltC, $fltX);

    return sprintf("#%02x%02x%02x", round(($r + $fltM) * 255), round(($v + $fltM) * 255), round(($b + $fltM) * 255));
}

/**
 * Retourne les parents d'un espace de travail ou d'un groupe d'utilisateur
 *
 * @param string $parents id des parents
 * @param string $type type d'élément cherché (workspace/group)
 * @return array
 */

function system_getparents($parents, $type)
{
    $db = ploopi\db::get();

    $parents = str_replace(';',',',$parents);

    $select = "SELECT * FROM ploopi_{$type} WHERE id IN ({$parents}) ORDER BY depth";
    $result = $db->query($select);

    $groups = array();
    while ($fields = $db->fetchrow($result)) $groups[$fields['id']] = $fields;

    return($groups);
}


/**
 * Indique si un rôle est administrable depuis l'espace courant : il doit appartenir
 * à cet espace, ou à un de ses parents et être partagé — la même règle que la
 * liste des rôles de l'écran d'administration (admin_index_roles_management.php).
 * Sans ce contrôle, un gestionnaire d'espace affectait par identifiant un rôle
 * d'un autre espace à n'importe quel utilisateur, dans son propre espace.
 *
 * @param int $intRole identifiant du rôle
 * @param int $intWorkspace identifiant de l'espace courant
 * @return boolean true si le rôle est administrable depuis cet espace
 */
function system_isroleallowed($intRole, $intWorkspace)
{
    $db = ploopi\db::get();

    $db->query("
        SELECT      w.parents
        FROM        ploopi_workspace w
        WHERE       w.id = ".(int)$intWorkspace."
    ");
    $arrWorkspace = $db->fetchrow();

    $strParents = empty($arrWorkspace['parents']) ? '0' : str_replace(';', ',', trim($arrWorkspace['parents'], ';'));

    $db->query("
        SELECT      r.id
        FROM        ploopi_role r
        WHERE       r.id = ".(int)$intRole."
        AND         (
                        r.id_workspace = ".(int)$intWorkspace."
                        OR (r.id_workspace IN ({$strParents}) AND r.shared = 1)
                    )
    ");
    $arrRole = $db->fetchrow();

    return !empty($arrRole);
}

function system_directory_formatphone($strPhone)
{
    $strPhone = preg_replace('@[^0-9\+\(\)]@i', '', $strPhone);

    if (preg_match('@([0-9\+\(\)]*)([1-9]{1})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})@i', $strPhone, $arrMatches) === 1) {
        $strPhone = ($arrMatches[1] == '0' ? $arrMatches[1] : $arrMatches[1].' ').$arrMatches[2].' '.$arrMatches[3].' '.$arrMatches[4].' '.$arrMatches[5].' '.$arrMatches[6];
    }

    return $strPhone;
}


/**
 * Supprime d'un module déployé les fichiers retirés de son paquet.
 *
 * La mise à jour d'un module recopie install/<module>/files sans rien retirer : un
 * fichier disparu du paquet restait en place, inerte ou pire (l'ancien jpgraph de
 * forms). Le paquet déclare ses retraits dans install/<module>/obsolete.txt, un
 * chemin relatif au module par ligne (fichier ou dossier), « # » pour un commentaire.
 *
 * Seuls les chemins faits de lettres, chiffres, « . », « - », « _ » et « / », sans
 * segment « .. », et situés réellement sous le dossier du module sont supprimés.
 *
 * @param string $strListe chemin de obsolete.txt
 * @param string $strModule dossier du module déployé (modules/<module>)
 * @return array chemins supprimés, relatifs au module
 */
function system_module_purgeobsolete($strListe, $strModule)
{
    $arrSupprimes = array();

    if (!is_file($strListe) || ($strRacine = realpath($strModule)) === false) return $arrSupprimes;

    foreach(file($strListe, FILE_IGNORE_NEW_LINES) as $strLigne)
    {
        $strChemin = trim(preg_replace('/#.*$/', '', $strLigne));
        if ($strChemin === '') continue;

        $strChemin = trim($strChemin, '/');
        if (!preg_match('@^[A-Za-z0-9._/-]+$@', $strChemin) || in_array('..', explode('/', $strChemin), true)) continue;

        $strReel = realpath($strRacine.'/'.$strChemin);
        if ($strReel === false || strpos($strReel, $strRacine.'/') !== 0) continue;

        if (is_dir($strReel)) ploopi\fs::deletedir($strReel);
        elseif (is_file($strReel)) unlink($strReel);

        if (!file_exists($strReel)) $arrSupprimes[] = $strChemin;
    }

    return $arrSupprimes;
}

/**
 * Ouverture d'un bloc d'écran : carte pour les gabarits de génération 4, bloc
 * historique (open_simplebloc) sinon. Sous ploopi4, un titre identique à celui de la
 * page ($GLOBALS['system_p4_pagetitle']) n'est pas répété.
 *
 * @param string $strTitle titre (html historique accepté)
 * @param string $strIcon icône (ploopi4)
 * @return string code html
 */

function system_open_bloc($strTitle = '', $strIcon = '')
{
    if (ploopi\skin::get()->version() < 4) return ploopi\skin::get()->open_simplebloc($strTitle);

    $strTitle = trim(html_entity_decode(strip_tags((string) $strTitle), ENT_QUOTES, 'UTF-8'));
    if (isset($GLOBALS['system_p4_pagetitle']) && $strTitle == $GLOBALS['system_p4_pagetitle']) $strTitle = '';

    return ploopi\system\ui::card_open($strTitle, $strTitle == '' ? '' : $strIcon, array(), array('class' => 'p4-legacy-card'));
}

/**
 * Fermeture d'un bloc ouvert par system_open_bloc()
 *
 * @return string code html
 */

function system_close_bloc()
{
    return ploopi\skin::get()->version() < 4 ? ploopi\skin::get()->close_simplebloc() : ploopi\system\ui::card_close();
}

/**
 * Tableau d'un écran de system : tableau filtrable de ploopi4 (ploopi\system\ui::table)
 * pour les gabarits de génération 4, display_array() historique sinon. Mêmes colonnes,
 * mêmes valeurs.
 *
 * @param array $arrColumns colonnes
 * @param array $arrValues lignes
 * @param string $strId identifiant du tableau historique
 * @param array $arrOptions options de display_array (et de ui::table : filter, empty, count)
 */

function system_display_array($arrColumns, $arrValues, $strId, $arrOptions = array())
{
    if (ploopi\skin::get()->version() < 4) ploopi\skin::get()->display_array($arrColumns, $arrValues, $strId, $arrOptions);
    else echo ploopi\system\ui::table($arrColumns, $arrValues, $arrOptions + array('limit' => 50, 'empty' => _SYSTEM_TABLE_EMPTY));
}

/**
 * Indique si l'utilisateur connecté peut lire un message de la messagerie : il en est
 * l'émetteur ou un destinataire (message racine, ou fil auquel appartient la réponse).
 *
 * @param int $intTicketId identifiant du message
 * @return boolean true si le message est lisible
 */

function system_ticket_isallowed($intTicketId)
{
    $db = ploopi\db::get();
    $intTicketId = (int) $intTicketId;
    $intUserId = (int) $_SESSION['ploopi']['userid'];

    $rs = $db->query("SELECT id, root_id, id_user FROM ploopi_ticket WHERE id = {$intTicketId}");
    if (!($row = $db->fetchrow($rs))) return false;

    $intRootId = empty($row['root_id']) ? $intTicketId : (int) $row['root_id'];

    $rs = $db->query("
        SELECT  t.id
        FROM    ploopi_ticket t
        WHERE   t.id IN ({$intTicketId}, {$intRootId})
        AND     (t.id_user = {$intUserId} OR t.id IN (SELECT td.id_ticket FROM ploopi_ticket_dest td WHERE td.id_user = {$intUserId}))
    ");

    return $db->numrows($rs) > 0;
}

/**
 * Résultats de la recherche intégrale dans l'espace courant : écran de recherche et
 * suggestions à la frappe du shell ploopi4 (opération system_search_suggest).
 *
 * Seuls comptent les modules actifs de l'espace courant (un seul si $mixModule en
 * désigne un), les types d'objets déclarés dans leur métabase et les enregistrements
 * que le module autorise (<module>_record_isenabled).
 *
 * @param string $strKeywords termes cherchés
 * @param mixed $mixModule instance de module, ou '' pour toutes celles de l'espace
 * @param int $intLimit nombre maximal de résultats (0 : pas de limite)
 * @param array $arrOptions options de ploopi\search_index::search() (prefix…)
 * @return array résultats par pertinence décroissante : champs de l'index (id_object,
 *         id_record, id_module, id_workspace, id_user, label, relevance, timestp_create,
 *         timestp_lastindex), module (libellé de l'instance), module_type, object
 *         (libellé du type d'objet) et link (URL chiffrée de l'enregistrement)
 */

function system_search_results($strKeywords, $mixModule = '', $intLimit = 0, $arrOptions = array())
{
    $db = ploopi\db::get();

    $arrAvailableModules = array();
    foreach ($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $intModule)
    {
        if (!empty($_SESSION['ploopi']['modules'][$intModule]['active'])) $arrAvailableModules[] = (int) $intModule;
    }

    // Aucun module dans l'espace : rien à chercher (la requête suivante serait invalide)
    if (empty($arrAvailableModules) || trim((string) $strKeywords) === '') return array();

    // Types d'objets de la métabase, par instance de module
    $arrObjectTypes = array();
    $rs = $db->query('
        SELECT      mbo.*,
                    m.id AS module_id,
                    m.label AS module_label,
                    mt.label AS module_type
        FROM        ploopi_mb_object mbo
        INNER JOIN  ploopi_module m
        ON          m.id_module_type = mbo.id_module_type
        AND         m.id IN ('.implode(',', $arrAvailableModules).')
        INNER JOIN  ploopi_module_type mt
        ON          mt.id = mbo.id_module_type
    ');

    while ($row = $db->fetchrow($rs))
    {
        if (empty($arrObjectTypes[$row['module_id']])) $arrObjectTypes[$row['module_id']] = array('label' => $row['module_label'], 'type' => $row['module_type'], 'objects' => array());
        $arrObjectTypes[$row['module_id']]['objects'][$row['id']] = array('label' => $row['label'], 'script' => $row['script']);
    }

    $mixModule = ($mixModule !== '' && in_array((int) $mixModule, $arrAvailableModules)) ? (int) $mixModule : $arrAvailableModules;

    $arrResults = array();
    foreach (ploopi\search_index::search($strKeywords, -1, '', $mixModule, $arrOptions) as $row)
    {
        if (!isset($arrObjectTypes[$row['id_module']]['objects'][$row['id_object']])) continue;

        $strType = $arrObjectTypes[$row['id_module']]['type'];

        // Fonctions du module, dont l'éventuel contrôle d'accès aux enregistrements
        ploopi\module::init($strType, false, false, false);
        $strFunc = "{$strType}_record_isenabled";
        if (function_exists($strFunc) && !$strFunc($row['id_object'], $row['id_record'], $row['id_module'])) continue;

        $strScript = str_replace(
            array('<IDRECORD>', '<IDMODULE>', '<IDWORKSPACE>'),
            array($row['id_record'], $row['id_module'], $row['id_workspace']),
            (string) $arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['script']
        );

        $arrResults[] = $row + array(
            'module' => $arrObjectTypes[$row['id_module']]['label'],
            'module_type' => $strType,
            'object' => $arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['label'],
            'link' => ploopi\crypt::urlencode("admin.php?ploopi_mainmenu=1&{$strScript}")
        );

        if ($intLimit && sizeof($arrResults) >= $intLimit) break;
    }

    return $arrResults;
}

/**
 * Configuration de PHP (phpinfo()) en lignes, pour l'outil « Configuration de PHP »
 * sous ploopi4 : rendue dans le thème de l'écran plutôt que dans un cadre au fond blanc.
 *
 * La page de phpinfo() est lue comme un document HTML : chaque titre de section (h2)
 * ouvre une section, chaque ligne de tableau à cellules « e » (directive) et « v »
 * (valeurs) donne une ligne. Les valeurs sont reprises en texte.
 *
 * @return array lignes : array('section' => …, 'directive' => …, 'local' => …, 'master' => …)
 *               (master vide quand la valeur principale égale la valeur locale)
 */

function system_phpinfo_rows()
{
    ob_start();
    phpinfo();
    $strHtml = ob_get_clean();

    $objDom = new DOMDocument();
    if (!@$objDom->loadHTML('<?xml encoding="UTF-8">'.$strHtml)) return array();

    $arrRows = array();
    $strSection = 'PHP';
    $objXpath = new DOMXPath($objDom);

    foreach ($objXpath->query('//body//h2 | //body//tr') as $objNode)
    {
        if ($objNode->nodeName == 'h2')
        {
            $strSection = trim($objNode->textContent);
            continue;
        }

        $arrCells = array();
        foreach ($objNode->childNodes as $objCell) if ($objCell->nodeName == 'td') $arrCells[] = $objCell;
        if (sizeof($arrCells) < 2 || strpos(' '.$arrCells[0]->getAttribute('class').' ', ' e ') === false) continue;

        $strLocal = trim($arrCells[1]->textContent);
        $strMaster = isset($arrCells[2]) ? trim($arrCells[2]->textContent) : '';

        $arrRows[] = array(
            'section' => $strSection,
            'directive' => trim($arrCells[0]->textContent),
            'local' => $strLocal,
            'master' => $strMaster === $strLocal ? '' : $strMaster
        );
    }

    return $arrRows;
}
