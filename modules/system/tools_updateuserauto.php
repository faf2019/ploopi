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
include_once('./include/classes/class_data_object.php');
include_once('./modules/system/class_user.php');
include_once('./modules/system/class_group.php');
include_once('./modules/system/class_group_user.php');

include_once './modules/interop-CIECRAM/class_ciecram.php';

//include_once('./modules/system/include/functions.php');

// construction de l'arbre des groupes et des règles
$select_user = "SELECT id FROM ploopi_user where id>2";
	
$res_user = $db->query($select_user);

$cie = new ciecram();

$objuser = new user();
// boucle sur les users de la base
while ($users = $db->fetchrow($res_user))
{
	
	$objuser->open($users['id']);
	
	system_verifyuser_groupsrules($objuser,1);
	
	if($objuser->fields['password']=="")
	{
		$ch=$cie->ConvertPassword($objuser->fields['password']);
		$db->query("update ploopi_user set password='".md5($ch)."' where id = ".$users['id']);
	}
	
	$objuser->fields['newpassword']=md5($cie->ConvertPassword($objuser->fields['password']));
}

?>