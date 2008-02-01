<?php
/*
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

echo $skin->open_simplebloc(_RSS_LABEL_TOOLS);

if (isset($_GET['end']))
{
	?>
	<div style="padding:4px;">
	Indexation terminée
	</div>
	<?
}
?>

<div style="padding:4px;">
<input type="button" class="button" value="Ré-indéxer les flux" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=reindex"); ?>';">
</div>


<? echo $skin->close_simplebloc(); ?>
