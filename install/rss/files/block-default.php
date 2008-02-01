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

ploopi_init_module('rss');

include_once './modules/rss/class_rss_pref.php';

$title = '';

$block_rssfeed_id = empty($_GET['block_rssfeed_id']) ? 0 : $_GET['block_rssfeed_id'];

if ($block_rssfeed_id)
{
	$rsspref = new rss_pref();
	$rsspref->fields['id_user'] = $_SESSION['ploopi']['userid'];
	$rsspref->fields['id_module'] = $menu_moduleid;
	$rsspref->fields['id_feed'] = $_GET['block_rssfeed_id'];
	$rsspref->save();
}
else
{
	$db->query("SELECT id_feed FROM ploopi_mod_rss_pref WHERE id_user = '{$_SESSION['ploopi']['userid']}' AND id_module = '{$menu_moduleid}'");
	if ($fpref = $db->fetchrow())
	{
		$block_rssfeed_id = $fpref['id_feed'];
	}
}

$rssfeed_select = 	"
					SELECT 		feed.*,
								cat.title as titlecat
					FROM 		ploopi_mod_rss_feed feed
					LEFT JOIN 	ploopi_mod_rss_cat cat ON cat.id = feed.id_cat
					WHERE 		feed.id_module = {$menu_moduleid}
					AND 		feed.id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).")
					ORDER BY	feed.title
					";

$rssfeed_result = $db->query($rssfeed_select);

$arrFeeds = array();

$strFeedsOptions = '';

while($rssfeed_row = $db->fetchrow($rssfeed_result))
{
	if (!$block_rssfeed_id) $block_rssfeed_id = $rssfeed_row['id'];
	$arrFeeds[] = $rssfeed_row;
	$sel = ($block_rssfeed_id == $rssfeed_row['id']) ? 'selected' : '';

	$strFeedsOptions .= "<option $sel value=\"{$rssfeed_row['id']}\">{$rssfeed_row['title']}</option>";
}


if ($block_rssfeed_id)
{
	include_once('./modules/rss/class_rss_feed.php');
	$rss_feed = new rss_feed();
	$rss_feed->open($block_rssfeed_id);

	if (!$rss_feed->isuptodate()) $rss_feed->updatecache();

	$block->addmenu("<b>{$rss_feed->fields['title']}</b>".(!empty($rss_feed->fields['subtitle']) ? '<br /><i>'.strip_tags($rss_feed->fields['subtitle'], '<b><i>').'</i>' : ''), $rss_feed->fields['link'], '', '_blank');

	$rssentry_select = 	"
						SELECT 		ploopi_mod_rss_entry.*
						FROM 		ploopi_mod_rss_entry
						WHERE 		ploopi_mod_rss_entry.id_feed = {$block_rssfeed_id}
						ORDER BY 	published DESC, timestp DESC, id
						LIMIT 		0,{$_SESSION['ploopi']['modules'][$menu_moduleid]['nbitemdisplay']}
						";
	$rssentry_result = $db->query($rssentry_select);

	while($rssentry_row = $db->fetchrow($rssentry_result))
	{
		$ld = ploopi_timestamp2local($rssentry_row['timestp']);
		$block->addmenu(strip_tags($rssentry_row['title'], '<b><i>').'<br />'.ploopi_unixtimestamp2local($rssentry_row['published']), $rssentry_row['link'], '', '_blank');
	}

}

if ($strFeedsOptions != '')
{
	$content = 	"
				<div style=\"padding:2px;\">
					<form name=\"bloc_rss_switch\">
					<select name=\"block_rssfeed_id\" class=\"select\" style=\"width:95%;\" OnChange=\"javascript:bloc_rss_switch.submit()\">{$strFeedsOptions}</select>
					</form>
					<div style=\"font-weight:bold;padding:2px 0px;\">{$title}</div>
				</div>
				";
	$block->addcontent($content);
}

if ($_SESSION['ploopi']['connected']) $block->addmenu('<b>'._RSS_LABEL_SEARCH.'</b>', ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"));


if (ploopi_isactionallowed(-1,$_SESSION['ploopi']['workspaceid'],$menu_moduleid))
{
	$block->addmenu('<b>'._RSS_LABEL_ADMIN.'</b>', ploopi_urlencode("{$scriptenv}?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}
?>
