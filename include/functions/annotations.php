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
function ploopi_get_nbannotation($id_object, $id_record, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
	global $db;

	if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];
	if ($id_workspace == -1) $id_workspace = $_SESSION['ploopi']['workspaceid'];
	if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

	$select = 	"
				SELECT 		count(*) as c
				FROM		ploopi_annotation a
				WHERE		a.id_record = '".$db->addslashes($id_record)."'
				AND			a.id_object = {$id_object}
				AND			a.id_module = {$id_module}
				AND			(a.private = 0
				OR			(a.private = 1 AND a.id_user = {$id_user}))
				";
	$db->query($select);

	if ($fields = $db->fetchrow()) $nbanno = $fields['c'];
	else $nbanno = 0;

	return($nbanno);
}

function ploopi_annotation($id_object, $id_record, $object_label = '')
{
	global $ploopi_annotation_private;

	// generate annotation id
	$id_annotation = md5("{$_SESSION['ploopi']['moduleid']}_{$id_object}_".addslashes($id_record));


	$_SESSION['annotations'][$id_annotation] = array(	'id_object' => $id_object,
														'id_record' => $id_record,
														'object_label' => $object_label
													);
	session_write_close();

	?>
	<div id="ploopiannotation_<? echo $id_annotation; ?>"></div>
	<script type="text/javascript">
		ploopi_annotation('<? echo $id_annotation; ?>');
	</script>
	<?
}
?>
