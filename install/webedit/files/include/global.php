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

define ('_WEBEDIT_ACTION_ARTICLE_EDIT', 		1);
define ('_WEBEDIT_ACTION_ARTICLE_PUBLISH', 		2);
define ('_WEBEDIT_ACTION_CATEGORY_EDIT', 		3);
define ('_WEBEDIT_ACTION_WORKFLOW_MANAGE', 		4);

define ('_WEBEDIT_OBJECT_ARTICLE_ADMIN', 		1);
define ('_WEBEDIT_OBJECT_ARTICLE_PUBLIC', 		2);
define ('_WEBEDIT_OBJECT_HEADING',	 			3);

define ('_WEBEDIT_TEMPLATES_PATH', 	'./templates/frontoffice');

global $article_status;
$article_status = array(	'edit' => 'Modifiable',
							'wait' => 'A Valider'
						);

global $heading_sortmodes;
$heading_sortmodes = array(	'bypos' => 'par position croissante',
							'bydate' => 'par date décroissante',
							'bydaterev' => 'par date croissante'
						);

function webedit_getlastupdate($moduleid = -1)
{
	global $db;

	if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

	$select = 	"
				SELECT 		MAX(lastupdate_timestp) as maxtimestp
				FROM 		ploopi_mod_webedit_article a
				WHERE 		a.id_module = {$moduleid}
				";

	$db->query($select);

	if ($row = $db->fetchrow()) return($row['maxtimestp']);
	else return(0);
}

function webedit_getheadings($moduleid = -1)
{
	global $db;

	if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

	$headings = array('list' => array(), 'tree' => array());

	$select = "SELECT * FROM ploopi_mod_webedit_heading WHERE id_module = {$moduleid} ORDER BY depth, position";
	$result = $db->query($select);
	while ($fields = $db->fetchrow($result))
	{
		$headings['list'][$fields['id']] = $fields;
		$headings['tree'][$fields['id_heading']][] = $fields['id'];

		$parents = split(';',$headings['list'][$fields['id']]['parents']);
		if (isset($parents[0])) unset($parents[0]);
		$parents[] = $fields['id'];

		$headings['list'][$fields['id']]['nav'] = implode('-',$parents);

		if ($headings['list'][$fields['id']]['template'] == '' && isset($headings['list'][$fields['id_heading']]) && $headings['list'][$fields['id_heading']]['template'] != '')
		{
			$headings['list'][$fields['id']]['template'] = $headings['list'][$fields['id_heading']]['template'];
			$headings['list'][$fields['id']]['herited_template'] = 1;
		}
	}

	return($headings);
}

function webedit_getarticles($moduleid = -1)
{
	global $db;

	if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];
	$today = ploopi_createtimestamp();

	$articles = array();

	$select = 	"
				SELECT 		ad.id,
							a.id as online_id,
							ad.position,
							ad.reference,
							ad.version,
							ad.title,
							ad.author,
							ad.id_heading,
							ad.status,
							ad.timestp,
							ad.timestp_published,
							ad.timestp_unpublished,
							ad.id_user,
							MD5(ad.content) as md5_content,
							MD5(a.content) as md5_online_content
				FROM 		ploopi_mod_webedit_article_draft ad
				LEFT JOIN	ploopi_mod_webedit_article a
				ON 			a.id = ad.id

				WHERE 		ad.id_module = {$moduleid}
				ORDER BY 	ad.position
				";

	$result = $db->query($select);
	while ($fields = $db->fetchrow($result))
	{
		/*
		 * $fields['similar_text'] = similar_text($fields['content'],$fields['online_content']);
		$fields['length_text'] = strlen($fields['content']);
		* */

		if (is_null($fields['online_id'])) $fields['new_version'] = 2;
		else $fields['new_version'] = ($fields['md5_content'] !=  $fields['md5_online_content']) ? '1' : '0';

		$fields['date_ok'] = (($fields['timestp_published'] <= $today || $fields['timestp_published'] == 0) && ($fields['timestp_unpublished'] >= $today || $fields['timestp_unpublished'] == 0));

		$articles['list'][$fields['id']] = $fields;
		$articles['tree'][$fields['id_heading']][] = $fields['id'];
	}

	return($articles);
}

/**
* build recursively the whole heading tree
*
*/
function webedit_build_tree($headings, $articles, $fromhid = 0, $str = '', $option = '')
{
	global $headingid;
	global $articleid;

	global $scriptenv;

	switch($option)
	{
		// used for fckeditor and link redirect on heading
		case 'selectredirect':
		case 'selectlink':
			$headingsel = $headings['list'][$headings['tree'][0][0]];
		break;

		default:
			$headingsel = $headings['list'][$headingid];
		break;
	}

	$html = '';
	if (isset($headings['tree'][$fromhid]))
	{
		$c=0;
		foreach($headings['tree'][$fromhid] as $hid)
		{
			$heading = $headings['list'][$hid];
			$isheadingsel = ($headingid == $hid && $option == '');

			$hselparents = explode(';',$headingsel['parents']);
			$testparents = explode(';',$heading['parents']);
			$testparents[] = $heading['id'];

			// heading opened if parents array intersects
			$hasarticles = !empty($articles['tree'][$hid]);
			$isheadingopened = sizeof(array_intersect ($hselparents, $testparents)) == sizeof($testparents);
			// last node or not ?
			$islast = ((!isset($headings['tree'][$fromhid]) || $c == sizeof($headings['tree'][$fromhid])-1) && empty($articles['tree'][$fromhid]));

			$decalage = '';
			$decalage_close = '';

			for($s=0;$s<strlen($str);$s++)
			{
				if ($s==0) $marginleft = 0;
				else $marginleft = 19;

				switch($str[$s])
				{
					case 's':
						$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/line.png') top left repeat-y;\">";
						$decalage_close .= '</div>';
					break;

					case 'b':
						$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/empty.png') top left repeat-y;\">";
						$decalage_close .= '</div>';
					break;
				}
			}

			$style_sel = ($isheadingsel) ? 'bold' : 'none';

			$icon = 'folder';
			$new_str = ''; // decalage pour les noeuds suivants
			if ($heading['depth'] == 1 || $heading['id'] == $fromhid) $icon = 'base';
			else
			{
				if (!$islast) $new_str = $str.'s'; // |
				else $new_str = $str.'b';  // (vide)
			}


			switch($option)
			{
				// used for fckeditor and link redirect on heading
				case 'selectredirect':
				case 'selectlink':
					$link = $link_div ="<a name=\"heading{$hid}\" onclick=\"javascript:webedit_showheading('{$option}{$hid}','{$new_str}&option={$option}');\" href=\"javascript:void(0);\">";
				break;

				default:
					$link_div ="<a name=\"heading{$hid}\" onclick=\"javascript:webedit_showheading('{$option}{$hid}', '{$new_str}');\" href=\"javascript:void(0);\">";
					$link = '<a style="font-weight:'.$style_sel.'" href="'.ploopi_urlencode("admin.php?headingid={$heading['id']}").'">';
				break;
			}

			if ($heading['depth'] > 1)
			{
				$last = 'joinbottom';
				if ($islast) $last = 'join';

				if (isset($headings['tree'][$hid]) || $hasarticles)
				{
					if ($islast) $last = ($isheadingsel || $isheadingopened) ? 'minus' : 'plus';
					else  $last = ($isheadingsel || $isheadingopened) ? 'minusbottom' : 'plusbottom';
				}

				if ($heading['depth'] == 2) $marginleft = 0;
				else $marginleft = 19;

				$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/{$last}.png') top left repeat-y;\" id=\"webedit_plus{$option}{$hid}\">{$link_div}<img style=\"width:19px;height:18px;\" src=\"./modules/webedit/img/empty.png\" /></a>";
				$decalage_close .= '</div>';
			}

			$html_rec = '';

			if ($isheadingsel || $isheadingopened || $heading['depth'] == 1 || !empty($articles['tree'][$hid])) $html_rec = webedit_build_tree($headings, $articles, $hid, $new_str, $option);

			$display = ($isheadingopened || $isheadingsel || $heading['depth'] == 1) ? 'block' : 'none';


			if ($heading['depth'] == 1) $marginleft = 0;
			else $marginleft = 19;

			$html .=	"
						<div class=\"webedit_tree_node\" id=\"webedit_tree_node{$option}{$hid}\">
							{$decalage}
								<div style=\"margin-left:{$marginleft}px;\">
									<img src=\"./modules/webedit/img/{$icon}.png\" />
									<span style=\"display:block;margin-left:16px;\">{$link}{$heading['label']}</a></span>
								</div>
							{$decalage_close}
						</div>
						<div style=\"clear:left;display:{$display};\" id=\"webedit_dest{$option}{$hid}\">{$html_rec}</div>
						";
			$c++;
		}
	}


	// ARTICLES
	if (!empty($articles['tree'][$fromhid]))
	{
		$c=0;
		foreach($articles['tree'][$fromhid] as $aid)
		{
			$article = $articles['list'][$aid];

			$islast = ($c == sizeof($articles['tree'][$fromhid])-1);
			$isarticlesel = ($articleid == $aid);

			$decalage = '';
			for($s=0;$s<strlen($str);$s++)
			{
				if ($s==0) $marginleft = 0;
				else $marginleft = 19;

				switch($str[$s])
				{
					case 's':
						$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/line.png') top left repeat-y;\">";
					break;

					case 'b':
						$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/empty.png') top left repeat-y;\">";
					break;
				}
			}

			switch($option)
			{
				// used for fckeditor and link redirect on heading
				case 'selectredirect':
					$link = "<a name=\"article{$aid}\" href=\"javascript:void(0);\" onclick=\"javascript:ploopi_getelem('webedit_heading_linkedpage').value = '{$aid}';ploopi_getelem('linkedpage_displayed').value = '".$db->addslashes($article['title'])."';ploopi_hidepopup();\">";
				break;

				case 'selectlink':
					$link = "<a name=\"article{$aid}\" href=\"javascript:void(0);\" onclick=\"javascript:ploopi_getelem('txtArticle',parent.document).value='index.php?nav={$headings['list'][$fromhid]['nav']}&headingid={$fromhid}&articleid={$aid}';\">";
				break;

				default:
					$style_sel =  ($isarticlesel) ? 'bold' : 'none';
					$link = '<a style="font-weight:'.$style_sel.'" href="'.ploopi_urlencode("admin.php?headingid={$fromhid}&op=article_modify&articleid={$aid}").'">';
				break;
			}

			if ($headings['list'][$fromhid]['depth'] == 1) $marginleft = 0;
			else $marginleft = 19;

			$last = ($islast) ? 'join' : 'joinbottom';
			$decalage .= "<div style=\"margin-left:{$marginleft}px;background:url('./modules/webedit/img/{$last}.png') top left repeat-y;\">";

			$status = ($article['status'] == 'wait') ? '&nbsp;<span style="color:#ff0000;font-weight:bold;">*</span>' : '';

			$dateok = ($article['date_ok']) ? '' : '&nbsp;<span style="color:#ff0000;font-weight:bold;">~</span>';

			$decalage_close='';
			for ($d=0;$d<$headings['list'][$fromhid]['depth'];$d++) $decalage_close .= '</div>';

			$html .=	"
						<div class=\"webedit_tree_node\">
							{$decalage}
								<div style=\"margin-left:19px;\">
									<img src=\"./modules/webedit/img/doc{$article['new_version']}.png\">
									<span style=\"display:block;margin-left:16px;\">{$link}{$article['title']}</a>{$status}{$dateok}</span>
								</div>
							{$decalage_close}
						</div>
						";

			$c++;
		}
	}

	return $html;
}


function webedit_template_assign($headings, $nav, $hid, $var = '', $link = '')
{
	global $template_body;
	global $recursive_mode;
	global $webedit_mode;
	global $scriptenv;

	if (isset($headings['tree'][$hid]))
	{
		if (isset($headings['list'][$hid]))
		{
			if ($headings['list'][$hid]['depth'] == 0) $localvar = "sw_root{$headings['list'][$hid]['position']}";
			else $localvar = "{$var}sw_heading{$headings['list'][$hid]['depth']}";

			$template_body->assign_block_vars($localvar , array());
		}

		foreach($headings['tree'][$hid] as $id)
		{

			$detail = $headings['list'][$id];

			$depth = $detail['depth'] - 1;
			if ($depth == 0) // root node
			{
				$localvar = "root{$detail['position']}";
			}
			else
			{
				$localvar = "{$var}heading{$depth}";
			}
			$locallink = ($link!='') ? "{$link}-{$id}" : "{$id}";

			/*
			switch($mode)
			{
				case 'edit';
					$script = "$scriptenv?headingid={$id}";
				break;

				case 'render';
					$script = "$scriptenv?nav={$locallink}";
				break;
			}
			*/

			switch($webedit_mode)
			{
				case 'edit';
					$script = "javascript:window.parent.document.location.href='admin.php?headingid={$id}';";
				break;

				case 'render';
					$script = "index.php?webedit_mode=render&moduleid={$_SESSION['ploopi']['moduleid']}&headingid={$id}";
				break;

				default:
				case 'display';
					$script = "index.php?headingid={$id}";
					if (_PLOOPI_FRONTOFFICE_REWRITERULE) $script = ploopi_urlrewrite($script, $detail['label']);
				break;
			}

			$sel = '';

			if (isset($nav[$depth]) && $nav[$depth] == $id)
			{
				$template_body->assign_block_vars('path' , array(
					'DEPTH' => $depth,
					'LABEL' => $detail['label'],
					'LINK' => $script
					));

				$template_body->assign_var("HEADING{$depth}_TITLE",			$detail['label']);
				$template_body->assign_var("HEADING{$depth}_ID",			$id);
				$template_body->assign_var("HEADING{$depth}_POSITION",		$detail['position']);
				$template_body->assign_var("HEADING{$depth}_DESCRIPTION",	$detail['description']);
				$template_body->assign_var("HEADING{$depth}_FREE1",			$detail['free1']);
				$template_body->assign_var("HEADING{$depth}_FREE2",			$detail['free2']);

				$sel = 'selected';
			}

			if ($detail['visible'])
			{
				if (!empty($detail['url']))
				{
					$script = $detail['url'];
					if (_PLOOPI_FRONTOFFICE_REWRITERULE) $script = ploopi_urlrewrite($script, $detail['label']);
				}

				$template_body->assign_block_vars($localvar , array(
					'DEPTH' => $depth,
					'ID' => $detail['id'],
					'LABEL' => $detail['label'],
					'POSITION' => $detail['position'],
					'DESCRIPTION' => $detail['description'],
					'LINK' => $script,
					'LINK_TARGET' => ($detail['url_window']) ? 'target="_blank"' : '',
					'SEL' => $sel,
					'POSX' => $detail['posx'],
					'POSY' => $detail['posy'],
					'COLOR' => $detail['color'],
					'FREE1' => $detail['free1'],
					'FREE2' => $detail['free2']
					));

				if ($depth == 0 || (isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
				{
					if (isset($headings['tree'][$id])) webedit_template_assign(&$headings, &$nav, $id, "{$localvar}.", $locallink);
				}
			}
		}

		if (isset($headings['list'][$hid]))
		{
			$depth = $headings['list'][$hid]['depth'];
			if ($depth > 0  && isset($nav[$depth-1]) && $nav[$depth-1] == $hid && !(isset($recursive_mode[$depth]) && $recursive_mode[$depth] == 'prof'))
			{
				if ($link!='' && isset($nav[$depth])) $link .= "-$nav[$depth]";
				elseif (isset($nav[$depth])) $link = "$nav[$depth]";

				if (isset($nav[$depth]) && isset($headings['tree'][$nav[$depth]])) webedit_template_assign(&$headings, &$nav, $nav[$depth], '', $link);
			}
		}

	}
}


function webedit_getrootid()
{
	global $db;

	$select = "SELECT * FROM ploopi_mod_webedit_heading WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND id_heading = 0";
	$db->query($select);

	if ($row = $db->fetchrow()) return($row['id']);
	else return(0);
}

function webedit_gettemplates()
{
	clearstatcache();
	//$rootdir = './modules/webedit/templates';

	$webedit_templates = array();
	$pdir = @opendir(_WEBEDIT_TEMPLATES_PATH);

	while ($tpl = @readdir($pdir))
	{
		if ($tpl != '.' && $tpl != '..' && is_dir(_WEBEDIT_TEMPLATES_PATH."/{$tpl}"))
		{
			$webedit_templates[] = $tpl;
		}
	}

	return($webedit_templates);
}

function webedit_getobjectcontent($matches)
{
	global $db;

	$content = '';

	if (!empty($matches[1]))
	{
		$key = split('/',$matches[1]);
		$id_object = split(',',$key[0]);

		if (sizeof($id_object) == 2 || sizeof($id_object) == 3) // normal size !
		{
			$module_id_cms = $id_object[1];

			$queryobj = "SELECT * FROM ploopi_mb_wce_object WHERE id={$id_object[0]}";

			$resobj = $db->query($queryobj);
			if($obj = $db->fetchrow($resobj))
			{
				$obj['module_id'] = $module_id_cms;
				if (isset($id_object[2])) $obj['object_id'] = $id_object[2];

				$tab = explode("&",trim($obj['script'],"?"));

				foreach ($tab as $key => $value) eval("$".$value.";");

				ob_start();
				include("./modules/".$_SESSION['ploopi']['modules'][$obj['module_id']]['moduletype']."/wce.php");
				$content .= ob_get_contents();
				ob_end_clean();
			}
		}
	}
	return($content);
}


function webedit_record_isenabled($id_object, $id_record, $id_module)
{
	$enabled = false;

	switch($id_object)
	{
		case _WEBEDIT_OBJECT_ARTICLE_PUBLIC;
			include_once './modules/webedit/class_article.php';

			$article = new webedit_article();
			if ($article->open($id_record))
			{
				$today = ploopi_createtimestamp();
				if (	($article->fields['timestp_published'] <= $today || empty($article->fields['timestp_published'])) &&
						($article->fields['timestp_unpublished'] >= $today || empty($article->fields['timestp_unpublished']))
					)
				{
					$enabled = true;
				}
			}
		break;
	}

	return($enabled);
}
?>
