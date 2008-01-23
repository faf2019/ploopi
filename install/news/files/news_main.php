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
<? echo $skin->open_simplebloc(_NEWS_CREATE,'100%'); ?>
	<TABLE CELLPADDING="2" CELLSPACING="1">
	<FORM NAME="form_new_news" ACTION="<? echo $scriptenv; ?>" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="op" VALUE="save_news">
	<INPUT TYPE="HIDDEN" NAME="news_id_module" VALUE="<? echo $_SESSION['session_env']['moduleid']; ?>">
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_TITLE; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT CLASS="Text" TYPE="Text" SIZE=30 MAXLENGTH=100 NAME="news_title"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_SOURCE; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT CLASS="Text" TYPE="Text" SIZE=30 MAXLENGTH=100 NAME="news_source"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT VALIGN=TOP><? echo _NEWS_LABEL_CONTENT; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT>
		<TEXTAREA CLASS="Text" TYPE="Text" COLS=60 ROWS=15 NAME="news_content"></TEXTAREA>	
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_CATEGORY; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT>
			<SELECT CLASS="Select" NAME="news_id_cat">
			<OPTION VALUE="0"><? echo _NEWS_LABEL_NOCATEGORY; ?></OPTION>
			<?
			$select = "SELECT * FROM ploopi_mod_news_cat WHERE id_module = ".$_SESSION['session_env']['moduleid']." ORDER BY name";
			$answer = $db->query($select);
			while ($fields = $db->fetchrow($answer))
			{
				echo "<OPTION VALUE=\"".$fields['id']."\">".$fields['name']."</OPTION>";
			}
			?>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_URL; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT CLASS="Text" TYPE="Text" SIZE=30 MAXLENGTH=100 NAME="news_url"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_URLTITLE; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT CLASS="Text" TYPE="Text" SIZE=30 MAXLENGTH=100 NAME="news_urltitle"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT><? echo _NEWS_LABEL_PUBLISHDATE; ?>:&nbsp;</TD>
		<TD ALIGN=LEFT><INPUT CLASS="Text" TYPE="Text" SIZE=10 MAXLENGTH=100 NAME="news_date_publish" VALUE="<? echo ploopi_getdate(); ?>"><INPUT CLASS="Text" TYPE="Text" SIZE=10 MAXLENGTH=100 NAME="newsx_time_publish" VALUE="<? echo ploopi_gettime(); ?>"></TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT COLSPAN=2>
			<INPUT TYPE="Submit" CLASS="Button" VALUE="<? echo _PLOOPI_ADD; ?>">
		</TD>
	</TR>
	</FORM>
	</TABLE>
<? echo $skin->close_simplebloc(); ?>

<? echo $skin->open_simplebloc(_NEWS_LIST,'100%'); ?>
	<TABLE CELLPADDING="2" CELLSPACING="1">
	<FORM NAME="form_modify_news" ACTION="<? echo $scriptenv; ?>" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="op" VALUE="">
	<TR>
		<TD ALIGN=LEFT>
			<SELECT CLASS="Select" SIZE=10 NAME="news_id">
			<?
			$select = "
			SELECT ploopi_mod_news_entry.*, ploopi_mod_news_cat.title as titlecat 
			FROM ploopi_mod_news_entry LEFT JOIN ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat 
			WHERE ploopi_mod_news_entry.id_module = ".$_SESSION['session_env']['moduleid']."
			ORDER BY titlecat, date_publish desc";
			
			$answer = $db->query($select);
			while ($fields = $db->fetchrow($answer))
			{
				$titlecat = $fields['titlecat'];
				if ($titlecat=='') $titlecat='';
				else $titlecat='['.$titlecat.'] - ';
				
				$localdate = ploopi_datetime2local($fields['date_publish']);
				$title = ploopi_strcut($fields['title'],60);
	
				echo "<OPTION VALUE=\"".$fields['id']."\">".$titlecat.$localdate['date'].' '.$localdate['time'].' - '.$title."</OPTION>";
			}	
			?>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD ALIGN=RIGHT>
			<INPUT TYPE="Button" CLASS="Button" VALUE="<? echo _PLOOPI_MODIFY; ?>" OnClick="javascript:ValiderForm(document.form_modify_news,'modify_news','',false)">
			&nbsp;<INPUT TYPE="Button" CLASS="Button" VALUE="<? echo _PLOOPI_DELETE; ?>" OnClick="javascript:ValiderForm(document.form_modify_news,'delete_news','Êtes-vous certain ?',true)">
		</TD>
	</TR>
	</FORM>
	</TABLE>
<? echo $skin->close_simplebloc(); ?>