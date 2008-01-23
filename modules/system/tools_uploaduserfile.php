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
if ($typefile=="cram")
{
	echo $skin->open_simplebloc('Téléchargement du fichier de la CRAM','100%'); 
}
else
{
	echo $skin->open_simplebloc('Téléchargement du fichier de la UGECAM','100%');
}
?>
	<FORM NAME='upload_form' ACTION='<? echo $scriptenv; ?>' METHOD='Post' ENCTYPE='multipart/form-data'>
	<!-- <INPUT TYPE='Hidden' NAME='section' VALUE='<? echo _UPLOAD; ?>'> -->
	<INPUT TYPE='Hidden' NAME='typefile' VALUE='<? echo $typefile; ?>'>
	<INPUT TYPE='Hidden' NAME='op' VALUE='uploadfile'>

	<TABLE CELLPADDING=2 CELLSPACING=1>
		<TR>
			<TD>&nbsp;Envoyer le fichier :&nbsp;</TD>
			<TD><INPUT CLASS='Text' TYPE='File' NAME='fichier'>
		</TR>
		<TR><TD COLSPAN=2>&nbsp;</TD></TR>
		<TR>
			<TD COLSPAN=2><INPUT CLASS='Button' TYPE='Submit' VALUE='Envoyer'>
		</TR>
	</FORM>
	</TABLE>
<? echo $skin->close_simplebloc(); ?>

<SCRIPT LANGUAGE='JavaScript'>
	document.upload_form.fichier.focus();
</SCRIPT>
