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
if (!empty($_REQUEST['idmodule'])) $idmodule = $_REQUEST['idmodule'];

echo $skin->open_simplebloc(_SYSTEM_MODULESELECTED);
?>
<div style="padding:4px;">
<?

$arrModList = array();

foreach($_SESSION['ploopi']['workspaces'] as $idw => $wksp)
{
	if (!empty($wksp['adminlevel']) && !empty($wksp['modules']))
	{
		$arrModList = array_unique(array_merge($arrModList, $wksp['modules']));
	}
}



if (empty($arrModList))
{
	?>
	Aucun module
	<?
}
else
{
	?>
	<form id="form_modparam" action="<? echo $scriptenv; ?>" method="post">
		<input type="hidden" name="op" value="param">
		<select class="select" name="idmodule" onchange="javascript:$('form_modparam').submit();">
		<?
		foreach($arrModList as $idm)
		{
			$mod = &$_SESSION['ploopi']['modules'][$idm];

			if (empty($idmodule)) $idmodule = $idm;
			?>
				<option <? if ($idmodule == $idm) echo 'selected'; ?> value="<? echo $idm; ?>"><? echo "{$mod['label']} ({$mod['moduletype']})"; ?></option>
			<?
		}
		?>
		</select>
	</form>
	<?
}
?>
</div>
<?
echo $skin->close_simplebloc();

if (isset($idmodule))
{
	echo $skin->open_simplebloc(_SYSTEM_MODULEPARAM);

	$param_module->open($idmodule, 0, $_SESSION['ploopi']['userid'], 1);

	if (isset($param_module->tabparam))
	{
		?>
		<div style="padding:4px;">
			<form action="<? echo $scriptenv; ?>" method="post">
			<input type="hidden" name="op" value="paramsave">
			<input type="hidden" name="idmodule" value="<? echo $idmodule; ?>">
			<div class="ploopi_form">
			<?
			foreach($param_module->tabparam as $name => $param)
			{
				?>
				<p>
					<label><? echo $param['label']; ?>:</label>

					<?
					if (!empty($param['choices']))
					{
						?>
						<select class="select" name="<? echo $name; ?>">
						<?
						foreach($param['choices'] as $value => $displayed_value)
						{
							?>
							<option <? if ($param['value'] == $value) echo 'selected'; ?> value="<? echo htmlspecialchars($value); ?>"><? echo $displayed_value; ?></option>
							<?
						}
						?>
						</select>
						<?
					}
					else
					{
						if (strlen($param['value'])>200 || strpos($param['value'], "\n") !== false)
						{
							?>
							<textarea class="text" name="<? echo $name; ?>"><? echo htmlspecialchars($param['value']); ?></textarea>
							<?
						}
						else
						{
							?>
							<input class="text" type="text" name="<? echo $name; ?>" value="<? echo htmlspecialchars($param['value']); ?>" />
							<?
						}
					}
					?>
				</p>
				<?
			}
			?>
			</div>
			<div style="text-align:right;">
					<input class="button" type="submit" value="<? echo _PLOOPI_SAVE; ?>">
			</div>
			</form>
		</div>
	<?
	}
	else echo '&nbsp;'._SYSTEM_LABEL_NOMODULEPARAM;

	echo $skin->close_simplebloc();
}
?>
