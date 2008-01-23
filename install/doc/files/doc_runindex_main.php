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
<? echo $skin->open_simplebloc(_DOC_LABEL_INDEX_DOC,'100%'); ?>
<TABLE WIDTH=100% CELLPADDING=2 CELLSPACING=1>

<FORM ACTION="<? echo $scriptenv; ?>" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="op" VALUE="execute">
<INPUT TYPE="HIDDEN" NAME="tab" VALUE="<? echo _DOC_TAB_RUNINDEX; ?>">

<TR CLASS=Title BGCOLOR="<? echo $skin->values['bgline2']; ?>">
	<TD COLSPAN="2" ALIGN="LEFT"><? echo _DOC_LABEL_INDEX_DOC; ?></TD>
</TR>
<TR BGCOLOR="<? echo $skin->values['bgline1']; ?>">
	<TD>
		<? echo _DOC_EXPLAIN_INDEX_DOC; ?>
	</TD>
	<TD ALIGN="CENTER">
		<INPUT TYPE="Submit" CLASS="Button" VALUE="<? echo _PLOOPI_EXECUTE; ?>">
	</TD>
</TR>
</FORM>

</TABLE>
<? echo $skin->close_simplebloc(); ?>
