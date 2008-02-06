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

	if (empty($_SESSION['system']['groups']))
	{
		$groups = array('list' => array(), 'tree' => array(), 'workspace_tree' => array());

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

	if (empty($_SESSION['system']['workspaces']))
	{
		$workspaces = array('list' => array(), 'tree' => array());

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


function system_build_tree($typetree, $from_wid = 1, $from_gid = 0, $str = '')
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

					//$islast = (!isset($workspaces['tree'][$from_wid]) || $c == sizeof($workspaces['tree'][$from_wid])-1);
					$islast = ((!isset($workspaces['tree'][$from_wid]) || $c == sizeof($workspaces['tree'][$from_wid])-1) && !isset($groups['workspace_tree'][$from_wid]));

					$decalage = '';
					$decalage_close = '';

					for($s=0;$s<strlen($str);$s++)
					{
						if ($s==0) $marginleft = 0;
						else $marginleft = 19;

						switch($str[$s])
						{
							case 's':
								$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png') top left repeat-y;\">";
								$decalage_close .= '</div>';
							break;

							case 'b':
								$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png') top left repeat-y;\">";
								$decalage_close .= '</div>';
							break;
						}
					}

					if ($isworkspacesel) $style_sel = 'bold';
					else $style_sel = 'none';

					$icon = ($workspace['web']) ? 'workspace-web' : 'workspace';
					$new_str = ''; // decalage pour les noeuds suivants


					if ($workspace['depth'] == 2 || $workspace['id'] == $_SESSION['ploopi']['workspaceid']) {/* racine */}
					else
					{
						if (!$islast) $new_str = $str.'s'; // |
						else $new_str = $str.'b';  // (vide)

						$link_div ="<a onclick=\"javascript:system_showgroup('workspaces', '{$wid}', '{$new_str}');\" href=\"javascript:void(0);\">";

						$last = 'joinbottom';
						if ($islast) $last = 'join';

						if (isset($workspaces['tree'][$wid]) || isset($groups['workspace_tree'][$wid]))
						{
							if ($islast)
							{
								if ($isworkspacesel || $isworkspaceopened) $last = 'minus';
								else $last = 'plus';
							}
							else
							{
								if ($isworkspacesel || $isworkspaceopened) $last = 'minusbottom';
								else $last = 'plusbottom';
							}
						}

						if ($workspace['depth'] <= 3) $marginleft = 0;
						else $marginleft = 19;

						$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$last}.png') top left repeat-y;\" id=\"w{$workspace['id']}_plus\">{$link_div}<img style=\"width:19px;height:18px;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png\" /></a>";
						$decalage_close .= '</div>';
					}

					$link = "<a style=\"font-weight:{$style_sel};\" href=\"".ploopi_urlencode("admin.php?workspaceid={$workspace['id']}")."\">";


					$html_rec = '';
					if ($isworkspacesel || $isworkspaceopened || $workspace['depth'] == 2)  $html_rec .= system_build_tree('workspaces', $wid, 0, $new_str);

					$display = ($html_rec == '') ? 'none' : 'block';

					if ($workspace['depth'] == 2 || $workspace['id'] == $_SESSION['ploopi']['workspaceid']) $marginleft = 0;
					else $marginleft = 19;

					$html .=	"
								<div class=\"system_tree_node\">
									{$decalage}
										<div style=\"margin-left:{$marginleft}px;\">
											<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
											<span style=\"display:block;margin-left:20px;\">{$link}{$workspace['label']}</a></span>
										</div>
									{$decalage_close}
								</div>
								<div style=\"clear:left;display:{$display};\" id=\"w{$workspace['id']}\">{$html_rec}</div>
								";
					$c++;
				}
			}

			// 2eme PARTIE, groupes



			if (isset($groups['workspace_tree'][$from_wid]))
			{
				$c=0;
				foreach($groups['workspace_tree'][$from_wid] as $gid)
				{
					$group = $groups['list'][$gid];

					//echo '<br />'.$group['label'].' : '.$workspaces['list'][$wid]['depth'];

					$isgroupsel = (!empty($groupid) && ($groupid == $gid));

					$gselparents = (isset($groupsel)) ? explode(';',$groupsel['parents']) : array();
					$testparents = explode(';',$group['parents']);
					$testparents[] = $group['id'];

					// group opened if parents array intersects
					$isgroupopened = sizeof(array_intersect_assoc($gselparents, $testparents)) == sizeof($testparents);
					$islast = (!isset($groups['workspace_tree'][$from_wid]) || $c == sizeof($groups['workspace_tree'][$from_wid])-1);

					$l = ($islast) ? 'O' : 'N';

					$decalage = '';
					$decalage_close = '';

					for($s=0;$s<strlen($str);$s++)
					{
						if ($s==0) $marginleft = 0;
						else $marginleft = 19;

						switch($str[$s])
						{
							case 's':
								$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png') top left repeat-y;\">";
								$decalage_close .= '</div>';
							break;

							case 'b':
								$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png') top left repeat-y;\">";
								$decalage_close .= '</div>';
							break;
						}
					}

					if ($isgroupsel) $style_sel = 'bold';
					else $style_sel = 'none';

					$icon = 'group';
					$new_str = '' ; // decalage pour les noeuds suivants

					if ($workspaces['list'][$from_wid]['depth'] > 2)
					{
						if (!$islast) $new_str = $str.'s'; // |
						else $new_str = $str.'b';  // (vide)

						$link_div ="<a onclick=\"javascript:system_showgroup('groups', '{$gid}','{$new_str}');\" href=\"javascript:void(0);\">";

						$last = 'joinbottom';
						if ($islast) $last = 'join';
						if (isset($groups['tree'][$gid]))
						{
							if ($islast)
							{
								if ($isgroupsel || $isgroupopened) $last = 'minus';
								else $last = 'plus';
							}
							else
							{
								if ($isgroupsel || $isgroupopened) $last = 'minusbottom';
								else $last = 'plusbottom';
							}
						}

						$decalage .= "<div style=\"margin-left:19px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$last}.png') top left repeat-y;\" id=\"g{$group['id']}_plus\">{$link_div}<img style=\"width:19px;height:18px;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png\" /></a>";
						$decalage_close .= '</div>';
					}

					$link = "<a style=\"font-weight:{$style_sel};padding-left:2px;\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">";

					$html_rec = '';

					if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid, $new_str);

					$display = ($html_rec == '') ? 'none' : 'block';

					if ($workspaces['list'][$from_wid]['depth'] <= 2) $marginleft = 0;
					else $marginleft = 19;


					$html .=	"
								<div class=\"system_tree_node\">
									{$decalage}
										<div style=\"margin-left:{$marginleft}px;\">
											<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
											<span style=\"display:block;margin-left:20px;\">{$link}{$group['label']}</a></span>
										</div>
									{$decalage_close}
								</div>
								<div style=\"clear:left;display:{$display};\" id=\"g{$group['id']}\">{$html_rec}</div>
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


						$decalage = '';
						$decalage_close = '';

						for($s=0;$s<strlen($str);$s++)
						{
							if ($s==0) $marginleft = 0;
							else $marginleft = 19;

							switch($str[$s])
							{
								case 's':
									$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/line.png') top left repeat-y;\">";
									$decalage_close .= '</div>';
								break;

								case 'b':
									$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png') top left repeat-y;\">";
									$decalage_close .= '</div>';
								break;
							}
						}

						if ($isgroupsel) $style_sel = 'bold';
						else $style_sel = 'none';

						$icon = 'group';
						$new_str = ' '; // decalage pour les noeuds suivants

						if (!empty($str) || $group['depth'] > 2)
						{
							if (!$islast) $new_str = $str.'s'; // |
							else $new_str = $str.'b';  // (vide)

							$link_div ="<a onclick=\"javascript:system_showgroup('groups', '{$gid}', '{$new_str}');\" href=\"javascript:void(0);\">";

							$last = 'joinbottom';
							if ($islast) $last = 'join';
							if (isset($groups['tree'][$gid]))
							{
								if ($islast)
								{
									if ($isgroupsel || $isgroupopened) $last = 'minus';
									else $last = 'plus';
								}
								else
								{
									if ($isgroupsel || $isgroupopened) $last = 'minusbottom';
									else $last = 'plusbottom';
								}
							}

							if (empty($str) && $group['depth'] == 3) $marginleft = 0;
							else $marginleft = 19;

							/*
							if ($group['depth'] <= 3) $marginleft = 0;
							else $marginleft = 19;
							*/

							$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$last}.png') top left repeat-y;\" id=\"g{$group['id']}_plus\">{$link_div}<img style=\"width:19px;height:18px;\" src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/empty.png\" /></a>";
							$decalage_close .= '</div>';
						}

						$link = "<a style=\"font-weight:{$style_sel};padding-left:2px;\" href=\"".ploopi_urlencode("admin.php?groupid={$group['id']}")."\">";

						$html_rec = '';
						if ($isgroupsel || $isgroupopened || ($group['depth'] == 2 && $group['id_workspace'] < 2)) $html_rec = system_build_tree('groups', 0, $gid, $new_str);

						$display = ($html_rec == '') ? 'none' : 'block';

						$html .=	"
									<div class=\"system_tree_node\">
										{$decalage}
											<div style=\"margin-left:19px;\">
												<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/treeview/{$icon}.png\" />
												<span style=\"display:block;margin-left:20px;\">{$link}{$group['label']}</a></span>
											</div>
										{$decalage_close}
									</div>
									<div style=\"clear:left;display:{$display};\" id=\"g{$group['id']}\">{$html_rec}</div>
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

	$select = 	"
				SELECT 		*
				FROM 		ploopi_module_type
				WHERE		system != 1
				ORDER BY 	label
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


// fonction permettant la vérification de l'ensemble des fils d'un group passe en parametre
function system_verifyuser_groupsrules($objuser,$id_group,$groups,$usergroup,$listrules)
{
	global $db;

	// on sélectionne que les fils du group pere $id_group
	//$result = $db->query("select * from ploopi_group where id_group=".$id_group);

	//while ($group = $db->fetchrow($result))
	foreach ($groups as $group)
	{
		// vérification de l'appartenance de id_user dans le groupe id_group

		//$usergroup = "SELECT * FROM ploopi_group_user WHERE id_group =".$group['id']." and id_user=".$objuser->fields['id'];

		$continue=1;

		//$resusergroup = $db->query($usergroup);
		//if ($rule = $db->fetchrow($resusergroup))

		if (isset($usergroup[$group['id']][$objuser->fields['id']]))
		{
		 $isnew=0;
		 $continue=system_verifyusergroup($objuser,$group['id'],0,$listrules);
		}
		else
		{
		 $isnew=1;
		 $continue=system_verifyusergroup($objuser,$group['id'],0,$listrules);
		}
		//echo "group: ".$group['id']." - continue ".$continue." isnew ".$isnew;

		if ($continue)
		{
			//echo $group['id']."<br>";
			// on verifie l'attribution d'un profil au user courant

			//$id_profile=system_verifyusergroupprofile($objuser,$group['id'],!$isnew,$listrules);
			$id_profile=0;
			//echo " - profil ".$id_profile;
			//on continue, les règles sont valides, on vérifie déja l'appartenance
			if ($isnew)
			{
			 	// il n'existe pas d'attachement, on le cree
			 	$objuser->attachtogroup($group['id'],$id_profile,0);
			}
			else
			{
				$db->query("update ploopi_group_user set id_profile=".$id_profile." where id_group=".$group['id']." and id_user=".$objuser->fields['id']);
			}


			// on rappelle la fonction sur ce groupe courant pour traiter les fils
			//system_verifyuser_groupsrules($objuser,$group['id'],$usergroup,$listrules);

		} // fin de $continue
		else
		{
			// on detache si il etait attache
			if (!$isnew)
				$objuser->detachfromgroup($group['id']);
		}

		//echo "<br>";
	}// fin de la boucle sur les groupes fils de id_group

}// fin de system_verifyuser_groupsrules



function system_verifyusergroup($objuser,$id_group,$persistent,$listrules)
{
	global $db;
	$continue=1;
	$valreturn=0;

	// on recupere l'ensemble des règles appliquées à ce groupe
	//$select = "SELECT * FROM ploopi_rule WHERE id_group = $id_group and id_type=1  order by position";
	//$result = $db->query($select);

	//if ($db->numrows()==0)
	if (!isset($listrules[1][$id_group]))
	{
	 return($continue);
	}

	//while (($rule = $db->fetchrow($result)))

	foreach ($listrules[1][$id_group] as $rule)
	{
		// on va vérifier l'ensemble des règles du groupe

		//$valreturn=system_verifyuser_rule($objuser,$rule);
		$continue=$continue & system_verifyuser_rule($objuser,$rule);
		//echo "rule:".$rule['id']." -> ".$valreturn."<Br>";
		//if ($valreturn) $continue=1;
	}

	return $continue;
}


// fonction permettant l'attribution automatique d'un profil à une personne
function system_verifyusergroupprofile($objuser,$id_group,$persistent,$listrules)
{
	global $db;
	$continue=0;
	$id_profile=0;


	// on recupere l'ensemble des règles appliquées à ce groupe
	//$select = "SELECT * FROM ploopi_rule WHERE id_group = $id_group and id_type=2 order by position";
	//$result = $db->query($select);

	//while ($rule = $db->fetchrow($result))
	foreach ($listrules[2][$id_group] as $rule)
	{
		// on va vérifier l'ensemble des règles du groupe
		$continue=system_verifyuser_rule($objuser,$rule);
		//echo "rule:".$rule['id']." profile:".$rule['id_profile']." -> ".$continue."<Br>";
		if ($continue) $id_profile=$rule['id_profile'];
	}

	return $id_profile;
}


// fonction permettant la vérification d'une règle de gestion sur le user courant
function system_verifyuser_rule($objuser,$rule)
{
	$res=0;

	switch($rule['operator'])
	{
		case '=':
			if ($objuser->fields[$rule['field']]==$rule['value']) $res=1;
			break;
		case '!=':
			if ($objuser->fields[$rule['field']]!=$rule['value']) $res=1;
			break;
		case '>=':
			if ($objuser->fields[$rule['field']]>=$rule['value']) $res=1;
			break;
		case '<=':
			if ($objuser->fields[$rule['field']]<=$rule['value']) $res=1;
			break;
		case '>':
			if ($objuser->fields[$rule['field']]>$rule['value']) $res=1;
			break;
		case '<':
			if ($objuser->fields[$rule['field']]<$rule['value']) $res=1;
			break;
	}// end switch

	return $res;
}



function system_getsharevalue(&$tabshare,$moduleid,$type_object="",$idgroup="",&$listworkspacegroup,&$listworkspacegroupauth)
{
	global $db;

	if ($type_object=="")
		$res=$db->query("select * from ploopi_share where id_module=$moduleid");
	else
		$res=$db->query("select * from ploopi_share where id_module=$moduleid and id_object=$type_object");


	if ($idgroup=="") $idgroup=$_SESSION["ploopi"]["groupid"];

	while ($fields=$db->fetchrow($res))
	{
		$tabshare[$fields['id_object']][$fields['id_record']][$fields['type_share']."_".$fields['id_share']]=1;
	}

	// construction de la liste d'appartenance des groupes
	// 01/07/2006 00H05
	// bug corrige car pas seulement sur la liste des groupes contenant le user courant mais tous ceux du groupe de travail

	$sql="select id_org from ploopi_workspace_group as o,ploopi_group_user as u where o.id_group=".$idgroup."
		 and u.id_group=o.id_org";

	$res=$db->query($sql);

	while ($fields=$db->fetchrow($res))
	{
		$listworkspacegroup[$fields['id_org']]=$fields['id_org'];
	}

	$sql="select id_org from ploopi_workspace_group as o,ploopi_group_user as u where o.id_group=".$_SESSION["ploopi"]["groupid"]."
		 and u.id_group=o.id_org and id_user=".$_SESSION["ploopi"]["userid"];

	$resu=$db->query($sql);

	while ($fieldsu=$db->fetchrow($resu))
	{
		$listworkspacegroupauth[$fieldsu['id_org']]=$fieldsu['id_org'];
	}

}

function system_verifshare($tabshare,$idobject,$idrecord,$listworkspacegroupauth)
{
 $res=0;
 $iduser=$_SESSION["ploopi"]["userid"];
 $idgroup=$_SESSION["ploopi"]["groupid"];

/*
if ($idrecord==301)
{
 ploopi_print_r($tabshare[$idobject][$idrecord]);
 ploopi_print_r($listworkspacegroupauth);
echo $idgroup;
}
*/
 // vérification de l'existence de droit sur l'element courant
 if (isset($tabshare[$idobject][$idrecord]))
 {

	$elem=$tabshare[$idobject][$idrecord];

	// test si droit sur user
	if (isset($elem['user'."_".$iduser])) $res=1;

	// test de droit sur all
	if (isset($elem['all'."_".$idgroup])) $res=2;

	// test group de travail $_SESSION["ploopi"]["groupid"]
	if (isset($elem['work'."_".$_SESSION["ploopi"]["groupid"]])) $res=3;

	// test sur le droit du groupe d'organisation autorisé

	if (isset($listworkspacegroupauth))
	{
		foreach($listworkspacegroupauth as $numorg)
		{
			if (isset($elem['org'."_".$numorg])) $res=4;

			if (isset($elem['all'."_".$numorg])) $res=4;

		}
	}
 }
 else
	$res=1;


 return $res;
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
