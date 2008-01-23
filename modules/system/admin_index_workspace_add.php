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
echo $skin->open_simplebloc();
?>

<form name="" action="<? echo $scriptenv; ?>" method="POST" onsubmit="javascript:return system_workspace_validate(this);">
<input type="hidden" name="op" value="save_workspace">
<input type="hidden" name="workspace_id" value="">
<input type="hidden" name="parent_id" value="<? echo $workspace->fields['id']; ?>">
<input type="hidden" name="parent_parents" value="<? echo $workspace->fields['parents']; ?>">

<div class="ploopi_form_title">
	<? echo $workspace->fields['label']; ?> &raquo;
	<?
	 echo _SYSTEM_LABEL_WORKSPACE_ADD;
	?>
</div>
<div class="ploopi_form" style="clear:both;padding:2px">
	<p>
		<label><? echo _SYSTEM_LABEL_GROUP_NAME; ?>:</label>
		<input type="text" class="text" name="workspace_label"  value="fils de <? echo $workspace->fields['label']; ?>">
	</p>
	<?


		$templatelist_back = ploopi_getavailabletemplates('backoffice');
		$templatelist_front = ploopi_getavailabletemplates('frontoffice');

		if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
		{
			?>
			<p>
				<label><? echo _SYSTEM_LABEL_GROUP_CODE; ?>:</label>
				<input type="text" class="text" name="workspace_code"  value="<? echo $workspace->fields['code']; ?>">
			</p>
			<?
			/*
			 * if (sizeof($workspaces_parents))
			{
				?>
				<p>
					<label><? echo _SYSTEM_LABEL_GROUP_FATHER; ?>:</label>
					<select class="select" name="workspace_id_workspace">
						<option value="<? echo _PLOOPI_SYSTEMGROUP; ?>"></option>
						<?
						foreach($workspaces_parents as $index => $fields)
						{
							if ($fields['id'] == $workspace->fields['id_workspace']) {$sel = 'selected';}
							else {$sel = '';}
							echo "<option $sel value=\"$fields[id]\">$fields[fullpath]</option>";
						}
						?>
				</p>
				<?
			}
			*/
		}
		?>
	</div>

	<div class="ploopi_form_title">
		<? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_ACCESS; ?>
	</div>
	<div class="ploopi_form" style="clear:both;padding:2px">
		<p>
			<label><? echo _SYSTEM_LABEL_GROUP_ADMIN; ?>:</label>
			<input type="checkbox" name="workspace_admin" <? if($workspace->fields['admin']) echo "checked"; ?> value="1">
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
			<input type="checkbox" name="workspace_web" <? if($workspace->fields['web']) echo "checked"; ?> value="1">
		</p>
		<p>
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
		</p>
		<p>
			<label><? echo _SYSTEM_LABEL_GROUP_WEBDOMAINLIST; ?>:</label>
			<textarea class="text" name="workspace_web_domainlist"><? echo $workspace->fields['web_domainlist']; ?></textarea>
		</p>
		<?
		if ($workspace->fields['web'])
		{
			// check if cms used in this workgroup
			$cmsready = false;
			foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
			{
				if ($_SESSION['ploopi']['modules'][$idm]['moduletype'] == 'cms') {$cmsready = true;echo "ici";}
			}

			if (!$cmsready)
			{
				?>
				<div style="clear:both;float:right;margin:4px;padding:2px 4px;border:1px solid #c0c0c0;background-color:#f0f0f0;">
				<p class="ploopi_va">
					<img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png">
					<span>Attention, pour pouvoir activer l'accès Frontoffice, vous devez créer une instance du module <a href="">WCE</a></span>
				</p>
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
		<?
	?>
	</div>
	<?
		?>
		<div class="ploopi_form_title">
			<? echo $workspace->fields['label']; ?> &raquo; <? echo _SYSTEM_LABEL_USEDMODULES; ?>
		</div>
		<div class="ploopi_form" id="system_filtering" style="clear:both;padding:2px;">
			<?
			$child = new workspace();
			$child->fields['parents'] = $workspace->fields['parents'].';'.$workspace->fields['id'];
			$sharedmodules = $child->getsharedmodules(false);
			$heritedmodules = $child->getsharedmodules(true);
			$installedmodules = system_getinstalledmodules();
			?>
			<TABLE WIDTH="100%" CELLPADDING="2" CELLSPACING="1">
			<?
			$color=$skin->values['bgline1'];
			echo 	"
				<TR CLASS=\"Title\" BGCOLOR=\"".$color."\">
					<TD ALIGN=\"CENTER\" width=\"20\"></TD>
					<TD ALIGN=\"CENTER\">"._SYSTEM_LABEL_MODULETYPE."</TD>
					<TD ALIGN=\"CENTER\">"._SYSTEM_LABEL_DESCRIPTION."</TD>
				</TR>
				";



			  foreach ($sharedmodules AS $instanceid => $instance)
			  {
				if ($color==$skin->values['bgline2']) $color=$skin->values['bgline1'];
				else $color=$skin->values['bgline2'];

				$checked = (isset($heritedmodules[$instanceid])) ? 'checked' : '';

				echo 	"
						<TR BGCOLOR=\"".$color."\">
							<TD ALIGN=\"CENTER\"><input type=\"checkbox\" name=\"heritedmodule[]\" value=\"SHARED,$instanceid\" $checked></TD>
							<TD ALIGN=\"CENTER\">$instance[label]</TD>
							<TD ALIGN=\"CENTER\">$instance[description]</TD>
						</TR>
						";
				//echo "<option value=\"SHARED,$groupID,$instanceId\" class=\"listParentItem\">$instanceName</option>";
			  }

			  foreach ($installedmodules AS $index => $moduletype)
			  {
				if ($color==$skin->values['bgline2']) $color=$skin->values['bgline1'];
				else $color=$skin->values['bgline2'];

				echo 	"
					<TR BGCOLOR=\"".$color."\">
						<TD ALIGN=\"CENTER\"><input type=\"checkbox\" name=\"heritedmodule[]\" value=\"NEW,{$moduletype['id']}\"></TD>
						<TD ALIGN=\"CENTER\">$moduletype[label]</TD>
						<TD ALIGN=\"CENTER\">$moduletype[description]</TD>
					</TR>
					";
				// Objet temporaire
				// $obj = NEW PLOOPI_MODULE($db->connection_id,$moduletype['instanceid']);
				// $moduleLabel = $obj->adminGetProperty('moduleLabel');
				//echo "<option value=\"NEW,$groupID,{$moduletype['id']}\">{$moduletype['label']}</option>";
			  }
			?>
			</TABLE>
		</div>
		<?
	?>
</div>


<div style="clear:both;float:right;padding:4px;">
	<input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
</div>

<? echo $skin->close_simplebloc(); ?>
