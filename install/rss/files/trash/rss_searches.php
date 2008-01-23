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
<? echo $skin->open_simplebloc(_RSS_LABEL_MYSEARCHES,'100%'); ?>
<TABLE CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
<TR>
	<TD VALIGN="TOP" WIDTH="25%">
	<TABLE CELLPADDING="2" CELLSPACING="1" WIDTH="100%" CLASS="Skin">
	<TR>
		<TD>
		<?
		$select = 	"
				SELECT 		ploopi_mod_rssrequest.*,
							ploopi_mod_rsscat.title
				FROM		ploopi_mod_rssrequest
				LEFT JOIN 	ploopi_mod_rsscat ON ploopi_mod_rsscat.id = ploopi_mod_rssrequest.id_cat 
				AND			ploopi_mod_rsscat.id_workspace IN ($groups)
				WHERE		ploopi_mod_rssrequest.id_user = {$_SESSION['ploopi']['userid']}
				AND			ploopi_mod_rssrequest.id_module = {$_SESSION['ploopi']['moduleid']}
				ORDER BY	ploopi_mod_rssrequest.request
				";

		$db->query($select);
	
		$color=$skin->values['bgline2'];
		?>
		<TABLE CELLPADDING="2" CELLSPACING="1" WIDTH="100%">
		<TR CLASS="Title" BGCOLOR="<? echo $color; ?>">
			<TD WIDTH="1%"><? echo _RSS_LABEL_REQUEST; ?></TD>
			<TD><? echo _RSS_LABEL_CATEGORY; ?></TD>
			<TD WIDTH="1%">&nbsp;</TD>
		</TR>
		<?
		while($fields = $db->fetchrow())
		{
			if ($color==$skin->values['bgline2']) $color=$skin->values['bgline1'];
			else $color=$skin->values['bgline2'];
			//$fields['request'] = htmlspecialchars($fields['request']);
			
			$array_request = explode('<OR>',$fields['request']);
			$keywords = array();
			
			foreach($array_request as $key1 => $value1)
			{
				$array_request[$key1] = trim($array_request[$key1],' ');
				$array_request[$key1] = explode('<AND>',$array_request[$key1]);
				foreach($array_request[$key1] as $key2 => $value2)
				{
					$array_request[$key1][$key2] = trim($array_request[$key1][$key2] ,' ');
					$keywords[] = $array_request[$key1][$key2];
				}
				
			}

			$search = '(';
			
			foreach($array_request as $keyOR => $valueOR)
			{
				if ($keyOR > 0) $search .= ') OR (';
				foreach($array_request[$keyOR] as $keyAND => $valueAND)
				{
					if ($keyAND > 0) $search .= ' AND ';
					$search .= "<B>$valueAND</B>";
				}
			}
			
			$search .= ')';
		
			echo 	"
				<TR BGCOLOR=\"$color\">
					<TD WIDTH=\"1%\" NOWRAP><A HREF=\"$scriptenv?op=show_request&rssrequest_id={$fields['id']}\">$search</A></TD>
					<TD>{$fields['title']}</TD>
					<TD WIDTH=\"1%\" NOWRAP>&nbsp;<A HREF=\"$scriptenv?op=delete_request&rssrequest_id={$fields['id']}\"><IMG BORDER=\"0\" SRC=\"./modules/rss/img/delete.gif\"></A>&nbsp;</TD>
				</TR>
				";
		}
		?>
		</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
		&nbsp;&#149;&nbsp;<B><A HREF="<? echo $scriptenv; ?>?op=add_request">Ajouter une Requête</A></B>
		</TD>
	</TR>
	<?
	if ($op == 'add_request')
	{
		?>
		<TR>
			<TD>
			<TABLE CELLPADDING="2" CELLSPACING="1" WIDTH="100%" CLASS="Skin">
			<FORM ACTION="<? echo $scriptenv; ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="op" VALUE="save_request">
			<TR>
				<TD WIDTH="1%" NOWRAP VALIGN="TOP">Mots Clés:</TD>
				<TD WIDTH="1%" NOWRAP>
					<TABLE CELLPADDING="1" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD></TD>
						<TD>
							<INPUT TYPE="Text" class="text" NAME="rssrequest_request1" SIZE="40">
						</TD>
					</TR>
					<TR>
						<TD NOWRAP><SELECT class="select" NAME="rssrequest_OP2"><OPTION>AND</OPTION><OPTION>OR</OPTION></SELECT></TD>
						<TD>
							<INPUT TYPE="Text" class="text" NAME="rssrequest_request2" SIZE="40">
						</TD>
					</TR>
					<TR>
						<TD NOWRAP><SELECT class="select" NAME="rssrequest_OP3"><OPTION>AND</OPTION><OPTION>OR</OPTION></SELECT></TD>
						<TD>
							<INPUT TYPE="Text" class="text" NAME="rssrequest_request3" SIZE="40">
						</TD>
					</TR>
					</TABLE>
				</TD>
			</TR>
			<TR>
				<TD WIDTH="1%" NOWRAP><? echo _RSS_LABEL_CATEGORY; ?>:&nbsp;</TD>
				<TD>
				<SELECT class="select" NAME="rssrequest_id_cat">
				<OPTION VALUE="0"><? echo _RSS_LABEL_NOCATEGORY; ?></OPTION>
				<?
				$select = "SELECT * FROM ploopi_mod_rsscat WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND id_workspace IN ($groups) ORDER BY title";
				$answer = $db->query($select);
				while ($fields = $db->fetchrow($answer))
				{
					echo "<OPTION VALUE=\"".$fields['id']."\">".$fields['title']."</OPTION>";
				}	
				?>
				</SELECT>
				</TD>
				<TD><INPUT TYPE="Submit" class="flatbutton" VALUE="<? echo _PLOOPI_ADD; ?>" ROWSPAN="2"></TD>
			</TR>
			</FORM>
			</TABLE>
			</TD>
		</TR>
		<?
	}
	?>
	</TABLE>
	</TD>
	<TD VALIGN="TOP" WIDTH="75%">
		<table cellpadding="2" cellspacing="1" width="100%" class="Skin">
		<?
		if ($op == 'show_request')
		{
			  
  			$rssrequest = new rssrequest();
			$rssrequest->open($rssrequest_id);
			
			if (trim($rssrequest->fields['request']) != '')
			{
				$array_request = explode('<OR>',$rssrequest->fields['request']);
				$keywords = array();
				
				foreach($array_request as $key1 => $value1)
				{
					$array_request[$key1] = trim($array_request[$key1],' ');
					$array_request[$key1] = explode('<AND>',$array_request[$key1]);
					foreach($array_request[$key1] as $key2 => $value2)
					{
						$array_request[$key1][$key2] = trim($array_request[$key1][$key2] ,' ');
						$keywords[] = $array_request[$key1][$key2];
					}
				}

				$search = '(';
				$pattern = "(UCASE(ploopi_mod_rsscache.title) LIKE UCASE('%<KEYWORD>%') OR UCASE(ploopi_mod_rsscache.description) LIKE UCASE('%<KEYWORD>%'))";
				
				foreach($array_request as $keyOR => $valueOR)
				{
					if ($keyOR > 0) $search .= ') OR (';
					foreach($array_request[$keyOR] as $keyAND => $valueAND)
					{
						if ($keyAND > 0) $search .= ' AND ';
						$search .= str_replace('<KEYWORD>',$valueAND,$pattern);
					}
				}
				
				$search .= ')';
				
				$where = " AND ($search)";
			}
			else $where = '';

			if ($rssrequest->fields['id_cat'] != 0)
			{
				$where .= " AND ploopi_mod_rssfeed.id_cat = {$rssrequest->fields['id_cat']}";
			}
			
			$select = 	"
					SELECT 		ploopi_mod_rsscache.*,
								ploopi_mod_rssfeed.title AS feed_title,
								ploopi_mod_rssfeed.link AS feed_link
					FROM		ploopi_mod_rsscache,
								ploopi_mod_rssfeed
					WHERE 		ploopi_mod_rssfeed.id = ploopi_mod_rsscache.id_feed
					AND			ploopi_mod_rssfeed.id_workspace IN ($groups)
								$where
					ORDER BY	timestp DESC, id
					LIMIT		0,50
					";
			
			$db->query($select);
			$feed_title = '';
			
			$first = true;
			while ($rsscache_fields = $db->fetchrow())
			{

				if ($color == $skin->values['bgline2']) $color = $skin->values['bgline1'];
				else $color = $skin->values['bgline2'];

				$ld = ploopi_timestamp2local($rsscache_fields['timestp']);
				//echo $skin->create_sep();
				//$rsscache_fields['description'] = strip_tags($rsscache_fields['description'],'img');
				//echo $skin->create_menu("<B>{$rsscache_fields['title']}</B><BR>{$rsscache_fields['description']}",$rsscache_fields['link'],'','_blank');

				foreach($keywords as $key => $word)
				{
					$rsscache_fields['title'] = eregi_replace("($word)", "<FONT CLASS=\"RSS_Highlighted\">\\1</FONT>", $rsscache_fields['title']);
					$rsscache_fields['description'] = eregi_replace("($word)", "<FONT CLASS=\"RSS_Highlighted\">\\1</FONT>", $rsscache_fields['description']);
				}

				echo 	"
						<tr><td bgcolor=\"$color\" style=\"font-size:14px;font-weight:bold;\">{$rsscache_fields['feed_title']}</td></tr>
						<tr><td bgcolor=\"$color\"><A TARGET=\"_blank\" HREF=\"{$rsscache_fields['link']}\"><b>{$rsscache_fields['title']}</b></A> - {$ld['date']} {$ld['time']}</td></tr>
						<tr><td bgcolor=\"$color\">{$rsscache_fields['description']}</td></tr>
						<tr><td bgcolor=\"$color\">» <A TARGET=\"_blank\" HREF=\"{$rsscache_fields['link']}\">{$rsscache_fields['link']}</A></td></tr>
						";

				/*
				if ($fields['feed_title'] != $feed_title)
				{
					if (!$first) 
					{
						echo "</TABLE>";
						echo $skin->close_simplebloc();
					}

					$first = false;
					$color = $skin->values['bgline2'];

					echo $skin->open_simplebloc("<A TARGET=\"_blank\" CLASS=\"SubTitle\" HREF=\"{$fields['feed_link']}\">{$fields['feed_title']}</A>",'100%');
					echo "<TABLE CELLPADDING=\"2\" CELLSPACING=\"1\" WIDTH=\"100%\">";
					$feed_title = $fields['feed_title'];
				}
				
				
				if ($color == $skin->values['bgline2']) $color = $skin->values['bgline1'];
				else $color = $skin->values['bgline2'];

				echo "<TR><TD BGCOLOR=\"$color\"><A TARGET=\"_blank\" HREF=\"{$fields['link']}\"><B>{$fields['title']}</B><BR>{$fields['description']}</A></TD></TR>";
				*/

			}
		}
		?>
		</table>
	</TD>
</TR>
</TABLE>
<? echo $skin->close_simplebloc(); ?>
