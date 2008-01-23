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
<? 
if (!$ploopi_cache->start("news_public"))
{
	
	echo $skin->create_pagetitle($_SESSION['session_env']['modulelabel'],'100%');
	
	$select = 
	"SELECT ploopi_mod_news_entry.*, ploopi_mod_news_cat.name as titlecat 
	FROM ploopi_mod_news_entry LEFT JOIN ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat 
	WHERE ploopi_mod_news_entry.id_module = $_SESSION['session_env']['moduleid'] ORDER BY titlecat, ploopi_mod_news_entry.name";
	
	$result = $db->query($select);
	
	while ($fields = $db->fetchrow($result))
	{
		$content = $fields['content'];
		$content = ploopi_nl2br($content);
	
		echo $skin->open_simplebloc($fields['title'],'100%');
		?>
		<TABLE WIDTH=100% CELLPADDING="2" CELLSPACING="1">
		<TR>
			<TD></TD>
			<TD WIDTH=95%><? echo $content; ?></TD>
			<TD></TD>	
		</TR>
		</TABLE>
		<?
		echo $skin->close_simplebloc();
	}
	
	$ploopi_cache->end();
}
?>