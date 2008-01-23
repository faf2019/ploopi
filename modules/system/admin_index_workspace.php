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
<? echo $skin->open_simplebloc(); ?>

<div>
	<div class="system_group_icons">
		<div class="system_group_icons_padding">
			<?
			$nbusers = sizeof($workspace->getusers());
			$nbgroups = sizeof($workspace->getgroups());
			$nbworkspaces = (!empty($workspaces['tree'][$workspaceid])) ? sizeof($workspaces['tree'][$workspaceid]) : 0;
			?>
			<div style="padding:4px;">
				Cet espace est composé de
				<br /><strong><? echo $nbworkspaces; ?> espace(s)</strong>
				<br /><strong><? echo $nbgroups; ?> groupe(s)</strong>
				<br /><strong><? echo $nbusers; ?> utilisateur(s)</strong>
			</div>
			<?

			$toolbar_workspace[] = array(
									'title' 	=> str_replace('<LABEL>','<br /><b>'.$childworkspace.'</b>', _SYSTEM_LABEL_CREATE_CHILD_WORKSPACE),
									'url'		=> "$scriptenv?op=child&gworkspaceid=$workspaceid",
									'icon'	=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_child.png",
								);

			if ($_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN && $_SESSION['ploopi']['workspaceid'] == $workspaceid)
			{
				$toolbar_workspace[] = array(
										'title' 	=> str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE),
										'url'		=> $scriptenv,
										'icon'	=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_copy_gray.png",
										'confirm'	=> _SYSTEM_MSG_CANTCOPYGROUP
									);
			}
			else
			{
				$toolbar_workspace[] = array(
						'title' 	=> str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_CREATE_CLONE_WORKSPACE),
						'url'		=> "$scriptenv?op=clone&workspaceid=$workspaceid",
						'icon'		=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_copy.png",
					);
			}

			$sizeof_workspaces = sizeof($workspace->getworkspacechildrenlite());
			$sizeof_users = sizeof($workspace->getusers());

			// delete button if group not protected and no children
			if (!$workspace->fields['protected'] && !$sizeof_workspaces && !$sizeof_users)
			{
				$toolbar_workspace[] = array(
										'title' 	=> str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_DELETE_WORKSPACE),
										'url'		=> "$scriptenv?op=delete&workspaceid=$workspaceid",
										'icon'	=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_delete.png",
									);
			}
			else
			{
				if ($sizeof_workspaces || $sizeof_users)
				{
					$msg = '';
					if ($sizeof_workspaces) $msg = _SYSTEM_MSG_INFODELETE_GROUPS;
					elseif ($sizeof_users) $msg = _SYSTEM_MSG_INFODELETE_USERS;

					$toolbar_workspace[] = array(
											'title' 	=> str_replace('<LABEL>','<br /><b>'.$currentworkspace.'</b>', _SYSTEM_LABEL_DELETE_WORKSPACE),
											'url'		=> $scriptenv,
											'icon'	=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_workspace_delete_gray.png",
											'confirm'	=> $msg
										);

				}
			}

			$toolbar_workspace[] = array(
									'title' 	=> _SYSTEM_LABEL_CREATE_GROUP,
									'url'		=> "$scriptenv?op=groupchild&workspaceid=$workspaceid",
									'icon'	=> "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_group_child.png",
								);



			echo $skin->create_toolbar($toolbar_workspace, $x = 0, false, true);
			?>
		</div>
	</div>

	<div class="system_group_main">
	<?

	if ($father = $workspace->getfather())
	{
		$parentlabel = $father->fields['label'];
		$parentid = $father->fields['id'];
	}
	else
	{
		$parentlabel = 'Racine';
		$parentid = '';
	}

	$templatelist_back = ploopi_getavailabletemplates('backoffice');
	$templatelist_front = ploopi_getavailabletemplates('frontoffice');
	?>
	<form name="" action="<? echo $scriptenv; ?>" method="POST" onsubmit="javascript:return system_workspace_validate(this);">
	<input type="hidden" name="op" value="save_workspace">
	<input type="hidden" name="workspace_id" value="<? echo $workspace->fields['id']; ?>">

		<div class="ploopi_form_title">
			<? echo $workspace->fields['label']; ?> &raquo;
			<?
			echo _SYSTEM_LABEL_WORKSPACE_MODIFY;
			?>
		</div>
		<div class="ploopi_form" style="clear:both;padding:2px">
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
				<input type="text" class="text" name="workspace_label"  value="<? echo $workspace->fields['label']; ?>">
			</p>
			<?
			if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
			{
				?>
				<p>
					<label><? echo _SYSTEM_LABEL_GROUP_CODE; ?>:</label>
					<input type="text" class="text" name="workspace_code"  value="<? echo $workspace->fields['code']; ?>">
				</p>
				<?
			}
			?>
		</div>

		<div class="ploopi_form_title">
			<? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_ACCESS; ?>
		</div>

		<div class="ploopi_form" style="clear:both;padding:2px">
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_ADMIN; ?>:</label>
				<input style="width:16px;" type="checkbox" name="workspace_admin" <? if($workspace->fields['admin']) echo "checked"; ?> value="1">
			</p>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_SKIN; ?>:</label>
				<select class="select" name="workspace_admin_template">
					<option value=""><? echo _PLOOPI_NONE; ?></option>
					<?
					foreach($templatelist_back as $index => $tpl_name)
					{
						$sel = ($tpl_name == $workspace->fields['admin_template']) ? 'selected' : '';
						echo "<option $sel>$tpl_name</option>";
					}
					?>
				</select>
			</p>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_ADMINDOMAINLIST; ?>:</label>
				<textarea class="text" name="workspace_admin_domainlist"><? echo $workspace->fields['admin_domainlist']; ?></textarea>
			</p>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_WEB; ?>:</label>
				<input style="width:16px;" type="checkbox" name="workspace_web" <? if($workspace->fields['web']) echo "checked"; ?> value="1">
			</p>
			<!--p>
				<label><? echo _SYSTEM_LABEL_GROUP_SKIN; ?>:</label>
				<select class="select" name="workspace_web_template">
					<option value=""><? echo _PLOOPI_NONE; ?></option>
					<?
					foreach($templatelist_front as $index => $tpl_name)
					{
						$sel = ($tpl_name == $workspace->fields['web_template']) ? 'selected' : '';
						echo "<option $sel>$tpl_name</option>";
					}
					?>
				</select>
			</p-->
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_WEBDOMAINLIST; ?>:</label>
				<textarea class="text" name="workspace_web_domainlist"><? echo $workspace->fields['web_domainlist']; ?></textarea>
			</p>
			<?
			if ($workspace->fields['web'])
			{
				// check if webedit used in this workgroup
				$webedit_ready = false;
				foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
				{
					if ($_SESSION['ploopi']['modules'][$idm]['moduletype'] == 'webedit') {$webedit_ready = true;}
				}

				if (!$webedit_ready)
				{
					?>
					<div class="system_wce_warning">
						<img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" style="display:block;float:left;margin:0 4px 4px 0;">
						Attention, pour pouvoir activer l'accès Frontoffice, vous devez créer une instance du module <strong>WebEdit</strong>
					</div>
					<?
				}
			}
			?>
		</div>

		<a class="ploopi_form_title" href="javascript:ploopi_switchdisplay('system_meta');">
			<? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_META; ?>
		</a>

		<div class="ploopi_form" id="system_meta" style="clear:both;padding:2px;display:none;">
			<p>
				<label>Titre:</label>
				<input type="text" class="text" name="workspace_title" value="<? echo $workspace->fields['title']; ?>">
			</p>
			<p>
				<label>Description:</label>
				<input type="text" class="text" name="workspace_meta_description" value="<? echo $workspace->fields['meta_description']; ?>">
			</p>
			<p>
				<label>Mots Clés:</label>
				<input type="text" class="text" name="workspace_meta_keywords" value="<? echo $workspace->fields['meta_keywords']; ?>">
			</p>
			<p>
				<label>Auteur:</label>
				<input type="text" class="text" name="workspace_meta_author" value="<? echo $workspace->fields['meta_author']; ?>">
			</p>
			<p>
				<label>Copyright:</label>
				<input type="text" class="text" name="workspace_meta_copyright" value="<? echo $workspace->fields['meta_copyright']; ?>">
			</p>
			<p>
				<label>Robots:</label>
				<input type="text" class="text" name="workspace_meta_robots" value="<? echo $workspace->fields['meta_robots']; ?>">
			</p>
		</div>

		<a class="ploopi_form_title" href="javascript:ploopi_switchdisplay('system_filtering');">
			<? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_FILTERING; ?>
		</a>

		<div class="ploopi_form" id="system_filtering" style="clear:both;padding:2px;display:none;">
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_ALLOWEDIP; ?>:</label>
				<input type="text" class="text" name="workspace_iprules"  value="<? echo $workspace->fields['iprules']; ?>">
			</p>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_ALLOWEDMAC; ?>:</label>
				<input type="text" class="text" name="workspace_macrules"  value="<? echo $workspace->fields['macrules']; ?>">
			</p>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_MUSTDEFINERULE; ?>:</label>
				<input type="checkbox" name="workspace_mustdefinerule" <? if($workspace->fields['mustdefinerule']) echo "checked"; ?> value="1">
			</p>
		</div>

		<div style="clear:both;float:right;padding:4px;">
			<input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
		</div>

		<div style="clear:both;float:right;padding:4px;">
		</div>
	</form>

	</div>

</div>
<?
echo $skin->close_simplebloc();

echo $skin->open_simplebloc('');
ploopi_annotation(_SYSTEM_OBJECT_WORKSPACE, $workspace->fields['id'], $workspace->fields['label']);
echo $skin->close_simplebloc();
?>
