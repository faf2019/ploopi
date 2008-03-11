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

function system_mergegroups($array1, $array2)
{
    foreach($array2 as $k => $v) $array1[$k] = $v;
    return($array1);
}

function system_getgroups()
{
    global $db;
    global $groupid;
    global $workspaces;

    if (empty($_SESSION['system']['groups']) || (!empty($_SESSION['system']['groups']['workspaceid']) && $_SESSION['system']['groups']['workspaceid'] != $_SESSION['ploopi']['workspaceid']))
    {
        $groups = array('list' => array(), 'tree' => array(), 'workspace_tree' => array(), 'workspaceid' => $_SESSION['ploopi']['workspaceid']);

        $select = "SELECT * FROM ploopi_group WHERE system = 0 ORDER BY depth,label";
        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            $fields['parents_workspace'] = '';
            $fields['groups'] = array();
            $groups['list'][$fields['id']] = $fields;
            $groups['tree'][$fields['id_group']][] = $fields['id'];
            if (!empty($fields['id_workspace']) && isset($workspaces['list'][$fields['id_workspace']]))
            {
                $groups['workspace_tree'][$fields['id_workspace']][] = $fields['id'];
                $workspaces['list'][$fields['id_workspace']]['groups'][$fields['id']] = 0;
                if ($groups['list'][$fields['id']]['shared']) $workspaces['list'][$fields['id_workspace']]['groups_shared'][$fields['id']] = 0;

                // code remplacé par la boucle ci dessous... semble plus rapide...
                //$groups['list'][$fields['id']]['parents_workspace'] = $workspaces['list'][$fields['id_workspace']]['parents'].";{$fields['id_workspace']};{$fields['id']}";
            }
        }

        // $groups['workspace_tree'] contient l'arbre de rattachement des groupes aux espaces
        // => mise à jour du lien parents pour chaque groupe rattaché à un espace (le lien parents contient les id des parents séparés par des ";"
        foreach($groups['workspace_tree'] as $idw => $list_idg)
        {
            foreach($list_idg as $idg)
            {
                if (isset($workspaces['list'][$idw])) $groups['list'][$idg]['parents_workspace'] = $workspaces['list'][$idw]['parents'].";{$idw};{$idg}";
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
            while (list($idg) = each($workspaces['list'][$idw]['groups']))
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

        $_SESSION['system']['groups'] = $groups;
        $_SESSION['system']['workspaces'] = $workspaces;
    }
    else $groups = $_SESSION['system']['groups'];

    return($groups);
}


function system_getworkspaces()
{
    global $db;
    
    if (empty($_SESSION['system']['workspaces']) || (!empty($_SESSION['system']['workspaces']['workspaceid']) && $_SESSION['system']['workspaces']['workspaceid'] != $_SESSION['ploopi']['workspaceid']))
    {
        $workspaces = array('list' => array(), 'tree' => array(), 'workspaceid' => $_SESSION['ploopi']['workspaceid']);

        //$select = "SELECT * FROM ploopi_workspace WHERE system = 0 ORDER BY depth,label";
        $select = "SELECT * FROM ploopi_workspace ORDER BY depth,label";
        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            $add = true;
            if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN)
            {
                // get allowed only groups
                $array_parents = explode(';',$fields['parents']);
                if (!($fields['id'] == $_SESSION['ploopi']['workspaceid'] || in_array($_SESSION['ploopi']['workspaceid'],$array_parents))) $add = false;
            }

            if ($add)
            {
                $fields['groups'] = array();
                $fields['groups_shared'] = array();
                $workspaces['list'][$fields['id']] = $fields;
                $workspaces['tree'][$fields['id_workspace']][] = $fields['id'];
            }
        }

        $_SESSION['system']['workspaces'] = $workspaces;
    }
    else $workspaces = $_SESSION['system']['workspaces'];

    return($workspaces);
}


/**
* build recursively the whole groups tree
*
*/


function system_build_tree($typetree, $from_wid = 1, $from_gid = 0)
{
    global $scriptenv;
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
                    $islast = ((!isset($groups['tree'][$from_wid]) || $c == sizeof($groups['tree'][$from_wid])-1) && !isset($workspaces['tree'][$from_wid]));
                    
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
                            $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png) 0 0 repeat-y;";
                        }
                        
                        $node = "<a onclick=\"javascript:system_showgroup('groups', '{$gid}', '');\" href=\"javascript:void(0);\"><img id=\"ng{$group['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$typenode}.png\" /></a>";
                    }

                    $link = "<a style=\"font-weight:{$style_sel};padding-left:2px;\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">";

                    $html_rec = '';

                    if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid);

                    $display = ($html_rec == '') ? 'none' : 'block';

                    if ($workspaces['list'][$from_wid]['depth'] < 2) $marginleft = 0;
                    else $marginleft = 20;

                    $html .=    "
                                <div style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a style=\"font-weight:{$style_sel};\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">{$group['label']}</a>
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

                    $gselparents = (isset($workspacesel)) ? explode(';',$workspacesel['parents'].';'.$workspacesel['id']) : explode(';',$groupsel['parents_workspace'].';g'.$groupsel['id']);
                    $currentparents = explode(';',$workspace['parents'].';'.$workspace['id']);

                    // workspace opened if parents array intersects
                    $isworkspaceopened = sizeof(array_intersect_assoc($gselparents, $currentparents)) == sizeof($currentparents);

                    $islast = (!isset($workspaces['tree'][$from_wid]) || $c == sizeof($workspaces['tree'][$from_wid])-1);

                    $node = '';
                    $bg = '';

                    if ($isworkspacesel) $style_sel = 'bold';
                    else $style_sel = 'none';

                    $icon = ($workspace['web']) ? 'workspace-web' : 'workspace';

                    if ($workspace['depth'] == 2 || $workspace['id'] == $_SESSION['ploopi']['workspaceid']) {/* racine */}
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
                            $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png) 0 0 repeat-y;";
                        }
                        
                        $node = "<a onclick=\"javascript:system_showgroup('workspaces', '{$wid}', '');\" href=\"javascript:void(0);\"><img id=\"nw{$workspace['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$typenode}.png\" /></a>";
                    }

                    $html_rec = '';
                    if ($isworkspacesel || $isworkspaceopened || $workspace['depth'] == 2)  $html_rec .= system_build_tree('workspaces', $wid, 0);

                    $display = ($html_rec == '') ? 'none' : 'block';

                    if ($workspace['depth'] == 2 || $workspace['id'] == $_SESSION['ploopi']['workspaceid']) $marginleft = 0;
                    else $marginleft = 20;

                    $html .=    "
                                <div style=\"overflow:auto;{$bg}\">
                                    <div>
                                        {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
                                        <span style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;\">
                                            <a style=\"font-weight:{$style_sel};\" href=\"".ploopi_urlencode("admin.php?workspaceid={$workspace['id']}")."\">{$workspace['label']}</a>
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
                                $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png) 0 0 repeat-y;";
                            }
                            
                            $node = "<a onclick=\"javascript:system_showgroup('groups', '{$gid}', '');\" href=\"javascript:void(0);\"><img id=\"ng{$group['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$typenode}.png\" /></a>";
                        }

                        $link = "<a style=\"font-weight:{$style_sel};padding-left:2px;\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">";

                        $html_rec = '';

                        if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid);

                        $display = ($html_rec == '') ? 'none' : 'block';
                        
                        $html .=    "
                                    <div style=\"overflow:auto;{$bg}\">
                                        <div>
                                            {$node}<img style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
                                            <span style=\"display:block;margin-left:40px;line-height:18px;\">
                                                <a style=\"font-weight:{$style_sel};\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">{$group['label']}</a>
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
    global $db;
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
    global $db;

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
    global $db;

    $modules = array();

    $select =   "
                SELECT      *
                FROM        ploopi_module_type
                WHERE       system != 1
                ORDER BY    label
                ";

    $result = $db->query($select);

    $i = 0;

    while ($moduletype = $db->fetchrow($result,MYSQL_ASSOC))
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

        $array_content = split("\r\n", $content);

        $array_pass = array();
        foreach($array_content as $line_content)
        {
            if (trim($line_content) != '')
            {
                list($ht_login, $ht_pass) = split(":", $line_content);
                $array_pass[$ht_login] = $ht_pass;
            }
        }

        if ($delete && isset($array_pass[$login])) unset($array_pass[$login]);
        else $array_pass[$login] = ploopi_htpasswd($pass);

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
    global $skin;
    global $scriptenv;

    sort($parents[$rootid]);

    $todaydate = ploopi_timestamp2local(ploopi_createtimestamp());

    foreach($parents[$rootid] as $ticketid)
    {
        $fields = $tickets[$ticketid];

        $localdate = ploopi_timestamp2local($fields['timestp']);
        $localdate['date'] = ($todaydate['date'] == $localdate['date'])  ? "Aujourd'hui" : "le {$localdate['date']}";

        $puce = '#ff2020';
        /*
        if (!$fields['opened']) $puce = '#ff2020';
        elseif (!$fields['done']) $puce = '#2020ff';
        else $puce = '#20ff20';
        */

        ?>
        <div class="system_tickets_response">
            <div class="system_tickets_head" onclick="javascript:system_tickets_display(<? echo $fields['id']; ?>,<? echo (empty($fields['status'])) ? 0 : 1; ?>, 0);">
                <div  class="system_tickets_date"><? echo $localdate['date']; ?> à <? echo $localdate['time']; ?></div>
                <div class="system_tickets_sender"><b><? echo "{$fields['firstname']} {$fields['lastname']}"; ?></b></div>
                <div class="system_tickets_title" id="tickets_title_<? echo $fields['id']; ?>" <? if (is_null($fields['status'])) echo 'style="font-weight:bold;"'; ?>><? echo $fields['title']; ?></div>
            </div>

            <div class="system_tickets_response_detail" id="tickets_detail_<? echo $fields['id'];?>">
                <div class="system_tickets_message">
                <?
                echo ploopi_make_links($fields['message']);
                if ($fields['lastedit_timestp'])
                {
                    $lastedit_local = ploopi_timestamp2local($fields['lastedit_timestp']);
                    echo "<i>Dernière modification le {$lastedit_local['date']} à {$lastedit_local['time']}</i>";
                }
                ?>
                </div>
                <div class="system_tickets_buttons">
                    <p class="ploopi_va">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_replyto&ticket_id=<? echo $fields['id']; ?>','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_reply.png">Répondre</a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_replyto&ticket_id=<? echo $fields['id']; ?>&quoted=true','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_quote.png">Citer</a>
                        <?
                        if ($fields['sender_uid'] == $_SESSION['ploopi']['userid'])
                        {
                            ?>
                            <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_modify&ticket_id=<? echo $fields['id']; ?>','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_modify.png">Modifier</a>
                            <?
                        }
                        ?>
                    </p>
                </div>
            </div>
            <div>
            <?
                if (isset($parents[$ticketid])) system_tickets_displayresponses($parents, $tickets, $ticketid);
            ?>
            </div>
        </div>
        <?
    }
}
?>
