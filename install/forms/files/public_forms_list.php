<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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

echo $skin->open_simplebloc(_FORMS_LIST);

$date_today = ploopi_createtimestamp();

$db->query("
    SELECT      *
    FROM        ploopi_mod_forms_form
    WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
    {$sqllimitgroup}
    AND         (pubdate_start <= '{$date_today}' OR pubdate_start = '')
    AND         (pubdate_end >= '{$date_today}' OR pubdate_end = '')
");


while ($row = $db->fetchrow())
{
	$pubdate_start = ($row['pubdate_start']) ? ploopi_timestamp2local($row['pubdate_start']) : array('date' => '');
	$pubdate_end = ($row['pubdate_end']) ? ploopi_timestamp2local($row['pubdate_end']) : array('date' => '');

	?>
	<a class="forms_public_link" href="<? echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$row['id']}"); ?>">
	<div>
        <h1><? echo $row['label']; ?></h1>
        <div><? echo ploopi_nl2br($row['description']); ?></div>
	</div>
	</a>
	<?
}
?>


<?
echo $skin->close_simplebloc();
?>
