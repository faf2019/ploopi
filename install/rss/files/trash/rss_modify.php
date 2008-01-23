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
?>
<? echo $skin->open_simplebloc(_RSS_LIST,'100%'); ?>

	<TABLE CELLPADDING="2" CELLSPACING="1">
	<FORM NAME="form_rss_list" ACTION="<? echo $scriptenv; ?>" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="op" VALUE="modify_rss">
	<TR>
		<TD ALIGN=LEFT>
			<SELECT class="select" SIZE=10 NAME="rssfeed_id">
			<?
			$select = 	"
					SELECT 		ploopi_mod_rssfeed.*, 
								ploopi_mod_rsscat.title as titlecat
					FROM 		ploopi_mod_rssfeed
					LEFT JOIN 	ploopi_mod_rsscat ON ploopi_mod_rsscat.id = ploopi_mod_rssfeed.id_cat 
					AND			ploopi_mod_rsscat.id_workspace IN ($groups) 
					WHERE 		ploopi_mod_rssfeed.id_module = {$_SESSION['ploopi']['moduleid']}
					AND 		ploopi_mod_rssfeed.id_workspace IN ($groups) 
					ORDER BY 	title, titlecat
					";
			
			$result = $db->query($select);
			while ($fields = $db->fetchrow($result))
			{
				$titlecat = $fields['titlecat'];
				if ($titlecat==null) $titlecat = _RSS_LABEL_NOCATEGORY;
				else $titlecat='['.$titlecat.']';
				
				$sel = '';
				if (isset($rssfeed_id))
				{
					if ($rssfeed_id == $fields['id']) $sel = 'selected';
				}

				if ($fields['default']) $default = 'Style="color:'.$skin->values['colsec'].';background-color:'.$skin->values['colprim'].'"';
				else $default = '';
				
				echo "<OPTION $sel $default VALUE=\"".$fields['id']."\">$fields[title] - $titlecat</OPTION>";
			}	
			?>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT>
			<?
			if (ploopi_isactionallowed(_RSS_ACTION_MODIFY))
			{
				?>
			 	<INPUT TYPE="Submit" class="flatbutton" VALUE="<? echo _PLOOPI_MODIFY; ?>">
			 	<?
			}
			if (ploopi_isactionallowed(_RSS_ACTION_DELETE))
			{
				?>
				&nbsp;<INPUT TYPE="Button" class="flatbutton" VALUE="<? echo _PLOOPI_DELETE; ?>" OnClick="javascript:document.form_rss_list.op.value='delete_rss'; ploopi_confirmform(document.form_rss_list,'<? echo _PLOOPI_CONFIRM; ?>')">
				<?
			}
			?>
		</TD>
	</TR>
	</FORM>
	<TR>
		<TD>
		&nbsp;&#149;&nbsp;<A HREF="<? echo $scriptenv; ?>?op=update_outdated_feeds"><B><? echo _RSS_LABEL_UPDATE_OUTDATED_FEEDS; ?></B></A>
		<BR />&nbsp;&#149;&nbsp;<A HREF="<? echo $scriptenv; ?>?op=update_all_feeds"><B><? echo _RSS_LABEL_UPDATE_ALL_FEEDS; ?></B></A>
		<BR />&nbsp;&#149;&nbsp;<A HREF="<? echo $scriptenv; ?>?op=clean_feeds"><B><? echo _RSS_LABEL_DELETE_UNAVAILABLE_FEEDS; ?></B></A>
		</TD>
	</TR>
	</TABLE>
<? echo $skin->close_simplebloc(); ?>

<?
if (isset($rssfeed_id))
{
	$rssfeed->open($rssfeed_id);

	echo $skin->open_simplebloc(str_replace("LABEL",$rssfeed->fields['title'],_RSS_MODIFY),'100%');
	?>
	<TABLE CELLPADDING="2" CELLSPACING="1">
	<FORM NAME="form_rss" ACTION="<? echo $scriptenv; ?>" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="op" VALUE="save_rss">
	<INPUT TYPE="HIDDEN" NAME="rssfeed_id" VALUE="<? echo $rssfeed->fields['id']; ?>">
	<TR>
		<TD ALIGN=RIGHT><? echo _RSS_LABEL_TITLE; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT class="text" TYPE="Text" SIZE=30 NAME="rssfeed_title" VALUE="<? echo $rssfeed->fields['title']; ?>"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _RSS_LABEL_URL; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT class="text" TYPE="Text" SIZE=30 NAME="rssfeed_url" VALUE="<? echo $rssfeed->fields['url']; ?>">&nbsp;<A HREF="javascript:ploopi_openwin('<? echo $rssfeed->fields['url']; ?>',640,480)"><IMG BORDER="0" SRC="./modules/rss/img/view.gif"></A></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _RSS_LABEL_CATEGORY; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT>
			<SELECT class="select" NAME="rssfeed_id_cat">
			<OPTION VALUE="0"><? echo _RSS_LABEL_NOCATEGORY; ?></OPTION>
			<?
			$select = "SELECT * FROM ploopi_mod_rsscat WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND id_workspace IN ($groups) ORDER BY title";
			$result = $db->query($select);
			while ($fields = $db->fetchrow($result))
			{
				if  ($fields['id'] == $rssfeed->fields['id_cat']) $sel = "selected";
				else $sel = "";
				echo "<OPTION $sel VALUE=\"".$fields['id']."\">".$fields['title']."</OPTION>";
			}	
			?>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _RSS_LABEL_FEED_RENEW; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT>
			<SELECT class="select" NAME="rssfeed_revisit">
			<OPTION <? if ($rssfeed->fields['revisit'] == 900) echo 'selected'; ?> VALUE="900">15mn</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 1800) echo 'selected'; ?> VALUE="1800">30mn</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 3600) echo 'selected'; ?> VALUE="3600">1h</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 7200) echo 'selected'; ?> VALUE="7200">2h</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 14400) echo 'selected'; ?> VALUE="14400">4h</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 21600) echo 'selected'; ?> VALUE="21600">6h</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 43200) echo 'selected'; ?> VALUE="43200">12h</OPTION>
			<OPTION <? if ($rssfeed->fields['revisit'] == 86400) echo 'selected'; ?> VALUE="86400">24h</OPTION>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _RSS_LABEL_DEFAULT; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT>
			<SELECT class="select" NAME="rssfeed_default">
			<OPTION <? if ($rssfeed->fields['default'] == 0) echo 'selected'; ?> VALUE="0"><? echo _PLOOPI_NO; ?></OPTION>
			<OPTION <? if ($rssfeed->fields['default'] == 1) echo 'selected'; ?> VALUE="1"><? echo _PLOOPI_YES; ?></OPTION>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT COLSPAN=2>
			<INPUT TYPE="Submit" class="flatbutton" VALUE="<? echo _PLOOPI_SAVE; ?>">
		</TD>
	</TR>
	
	</FORM>
	</TABLE>
	<?	
	echo $skin->close_simplebloc();
	
	echo $skin->open_simplebloc(_RSS_ACTIONHISTORY,'100%');
	?>
	<TABLE WIDTH=100% CELLPADDING=2 CELLSPACING=1>
	<TR BGCOLOR=<? echo $skin->values['bgline1']; ?>>
		<TD><B><? echo _RSS_LABEL_DATE; ?></B></TD>
		<TD><B><? echo _RSS_LABEL_USER; ?></B></TD>
		<TD><B><? echo _RSS_LABEL_ACTION; ?></B></TD>
	</TR>
	
		
	<?
	$user_action = ploopi_get_user_action_log($rssfeed_id);

	$color = $skin->values['bgline1'];
	
	foreach($user_action as $key => $value)
	{
		if ($color==$skin->values['bgline2']) $color=$skin->values['bgline1'];
		else $color=$skin->values['bgline2'];
		
		$localdate = ploopi_timestamp2local($value['timestp']);
		
		?>
		<TR BGCOLOR="<? echo $color; ?>">
			<TD><? echo "$localdate[date] $localdate[time]"; ?></TD>
			<TD><? echo $value['user_name']; ?></TD>
			<TD><? echo $value['action_label']; ?></TD>
		</TR>
		<?
	}
	?>
	</TABLE>
	<?
	echo $skin->close_simplebloc();
}
?>
