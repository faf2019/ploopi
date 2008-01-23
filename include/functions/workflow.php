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

function ploopi_workflow_selectusers($id_object = -1, $id_record = -1, $id_module = -1, $id_action = -1)
{
	global $db;

	if (isset($_SESSION['ploopi']['workflow']['users_selected'])) unset($_SESSION['ploopi']['workflow']['users_selected']);

	if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

	$db->query("SELECT id_workflow FROM ploopi_workflow WHERE id_object = {$id_object} AND id_record = '".$db->addslashes($id_record)."' AND id_module = {$id_module}");
	while ($row = $db->fetchrow())
	{
		$_SESSION['ploopi']['workflow']['users_selected'][$row['id_workflow']] = $row['id_workflow'];
	}

	?>
	<a class="ploopi_workflow_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('ploopi_workflow');">
		<p class="ploopi_va">
			<img src="<? echo "{$_SESSION['ploopi']['template_path']}/img/workflow/workflow.png"; ?>">
			<span>Validateurs</span>
		</p>
	</a>
	<div id="ploopi_workflow" style="display:block;">
		<div class="ploopi_workflow_search_form">
			<p class="ploopi_va">
				<span>Recherche groupes/utilisateurs:&nbsp;</span>
				<input type="text" id="ploopi_workflow_userfilter" class="text">
				<img onmouseover="javascript:this.style.cursor='pointer';" onclick="ploopi_xmlhttprequest_todiv('index-light.php','ploopi_op=workflow_search_users&ploopi_workflow_userfilter='+ploopi_getelem('ploopi_workflow_userfilter').value+'&id_action=<? echo $id_action; ?>','','div_workflow_search_result');" style="border:0px" src="<? echo "{$_SESSION['ploopi']['template_path']}/img/workflow/search.png"; ?>">
			</p>
		</div>
		<div id="div_workflow_search_result"></div>

		<div class="ploopi_workflow_title">Accréditations :</div>
		<div class="ploopi_workflow_authorizedlist" id="div_workflow_users_selected">
		<? if (empty($_SESSION['ploopi']['workflow']['users_selected'])) echo 'Aucune accrédidation'; ?>
		</div>
		<?
		if (!empty($_SESSION['ploopi']['workflow']['users_selected']))
		{
			?>
			<script type="text/javascript">
				ploopi_ajaxloader('div_workflow_users_selected');
				ploopi_xmlhttprequest_todiv('index-light.php','ploopi_op=workflow_select_user','','div_workflow_users_selected');
			</script>
			<?
		}
		?>
	</div>
	<?
}

function ploopi_workflow_save($id_object = -1, $id_record = -1, $id_module = -1)
{
	global $db;
	include_once './include/classes/class_workflow.php';

	if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

	$db->query("DELETE FROM ploopi_workflow WHERE id_object = {$id_object} AND id_record = '".$db->addslashes($id_record)."' AND id_module = {$id_module}");

	if (!empty($_SESSION['ploopi']['workflow']['users_selected']))
	{
		foreach($_SESSION['ploopi']['workflow']['users_selected'] as $id_user)
		{
			$workflow = new workflow();
			$workflow->fields = array(	'id_module' 	=> $id_module,
									'id_record' 	=> $id_record,
									'id_object' 	=> $id_object,
									'type_workflow' 	=> 'user',
									'id_workflow' 		=> $id_user
								);
			$workflow->save();

		}
	}
}

function ploopi_workflow_get($id_object = -1, $id_record = -1,  $id_module = -1, $id_user = -1)
{
	global $db;

	$workflow = array();

	if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

	$sql =	"SELECT * FROM ploopi_workflow WHERE id_module = {$id_module}";
	if ($id_object != -1) $sql .= " AND id_object = {$id_object}";
	if ($id_record != -1) $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
	if ($id_user != -1) $sql .= " AND id_workflow = {$id_user} AND type_workflow = 'user'";

	$db->query($sql);

	while ($row = $db->fetchrow())
	{
		$workflow[] = $row;
	}

	return($workflow);
}
?>
