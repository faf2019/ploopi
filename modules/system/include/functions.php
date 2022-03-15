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
                                <div style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?groupid={$group['id']}")."\">".ploopi\str::htmlentities($group['label'])."</a>
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
                                <div style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?workspaceid={$workspace['id']}")."\">".ploopi\str::htmlentities($workspace['label'])."</a>
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
                                    <div style=\"overflow:auto;{$bg}\">
                                        <div>
                                            {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$icon}.png\" />
                                            <span style=\"display:block;margin-left:40px;line-height:18px;\">
                                                <a style=\"font-weight:{$style_sel};\" href=\"".ploopi\crypt::urlencode("admin.php?groupid={$group['id']}")."\">{$group['label']}</a>
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

function system_serverload_getcolor($min, $max, $x)
{
    $mil = ($min+$max)/2;

    $r = 0;
    $v = 0;
    $b = 128;

    if ($x<=$mil)
    {
        $v = 255;
        $r = 127+($x*128)/$mil;
    }
    else
    {
        $r = 255;
        if ($x>$max) $v = 127;
        else $v = 127+(($max-$x)*128)/$mil;
    }

    return $color = sprintf("#%02x%02x%02x",$r,$v,$b);
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
 * Formatage d'un numéro de téléphone
 *
 * @param string $strPhone numéro de téléphone à formater
 * @return string numéro formaté
 */
function system_directory_formatphone($strPhone)
{
    $strPhone = preg_replace('@[^0-9\+\(\)]@i', '', $strPhone);

    if (preg_match('@([0-9\+\(\)]*)([1-9]{1})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})@i', $strPhone, $arrMatches) === 1) {
        $strPhone = ($arrMatches[1] == '0' ? $arrMatches[1] : $arrMatches[1].' ').$arrMatches[2].' '.$arrMatches[3].' '.$arrMatches[4].' '.$arrMatches[5].' '.$arrMatches[6];
    }

    return $strPhone;
}

?>
