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
$forms = new forms();
$forms->open($forms_id);


if (is_numeric($forms->fields['width'])) 
{
	$inputwidth = $forms->fields['width']-10;
	$style = "style=\"width:{$inputwidth}px;\"";
}
else 
{
	$style = "style=\"width:400px;\"";
}

$pubdate_start = ($forms->fields['pubdate_start']) ? ploopi_timestamp2local($forms->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($forms->fields['pubdate_end']) ? ploopi_timestamp2local($forms->fields['pubdate_end']) : array('date' => '');

echo $skin->open_simplebloc($forms->fields['label'].' ('._FORMS_PREVIEW.')', '100%');
?>
<table cellpadding="2" cellspacing="1" width="100%" bgcolor="<? echo $skin->values['bgline1']; ?>">
<tr>
	<td align="right"><input type="button" class="flatbutton" value="<? echo _PLOOPI_COMPLETE; ?>" onclick="javascript:document.location.href='<? echo "$scriptenv?op=forms_modify&forms_id={$forms_id}"; ?>'"></td>
</tr>
</form>
</table>

<table cellpadding="0" cellspacing="0" bgcolor="<? echo $skin->values['colsec']; ?>" width="100%"><tr><td height="1"></td></tr></table>

<table cellpadding="0" cellspacing="0" width="<? echo $forms->fields['width']; ?>" style="border: solid 1px <? echo $skin->values['colsec']; ?>; margin:10px">
<tr>
	<td>

		<table cellpadding="2" cellspacing="1" width="100%" bgcolor="<? echo $skin->values['bgline2']; ?>">
		<tr>
			<td><h2><? echo $forms->fields['label']; ?></h2>
			<? 
			if ($pubdate_start['date'] != '' || $pubdate_end['date'] != '')
			{
				?>
				<strong>Publié du <? echo $pubdate_start['date']; ?> au <? echo $pubdate_end['date']; ?></strong>
				<?
			}
			?>
			</td>
		</tr>

		<tr>
			<td><? echo nl2br($forms->fields['description']); ?></td>
		</tr>
		</table>


		<?
		$sql = 	"
				SELECT 	* 
				FROM 	ploopi_mod_forms_field
				WHERE 	id_form = {$forms_id}
				ORDER BY position
				";
				
		$db->query($sql);
			
		while ($fields = $db->fetchrow())
		{
			$color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
			?>
			<table cellpadding="0" cellspacing="0" bgcolor="<? echo $skin->values['colsec']; ?>"  width="100%"><tr><td height="1"></td></tr></table>

			<table cellpadding="2" cellspacing="1"  width="100%" bgcolor="<? echo $color; ?>">
			<tr>
				<td><h3><? echo $fields['position']; ?>. <? echo $fields['name']; ?></h2></td>
			</tr>
			<tr>
				<td><? echo nl2br($fields['description']); ?></td>
			</tr>
			<tr>
				<td>
				<?
				$values = explode('||',$fields['values']);
				
				switch($fields['type'])
				{
					
					case 'textarea':
					?>
						<textarea <? echo $style; ?> name="<? echo $fields['name']; ?>" class="textarea" rows="10"></textarea>
					<?
					break;
					
					case 'select':
						?>
						<select <? echo $style; ?> name="<? echo $fields['id']; ?>" class="select">
						<?
						foreach($values as $value)
						{
							?>
							<option value="<? echo $value; ?>"><? echo $value; ?></option>
							<?
						}
						?>
						</select>
						<?
					break;

					case 'checkbox':
						?>
						<table cellpadding="2" cellspacing="1">
						<?
						foreach($values as $value)
						{
							?>
							<tr><td><input type="checkbox" name="<? echo $fields['name']; ?>" value="<? echo $value; ?>"></td><td><? echo $value; ?></td></tr>
							<?
						}
						?>
						</table>
						<?
					break;

					case 'radio':
						?>
						<table cellpadding="2" cellspacing="1">
						<?
						foreach($values as $value)
						{
							?>
							<tr><td><input type="radio" name="<? echo $fields['name']; ?>" value="<? echo $value; ?>"></td><td><? echo $value; ?></td></tr>
							<?
						}
						?>
						</table>
						<?
					break;

					default:
						case 'text':
						$maxlength = ($fields['maxlength'] > 0 && $fields['maxlength'] != '') ? $fields['maxlength'] : '50';
						?>
						<input type="text" class="text" size="<? echo $maxlength; ?>" maxlength="<? echo $maxlength; ?>">
						<?
					break;
				}
				?>
				</td>
			</tr>
			</table>
			<?
			
		}
	?>
	</td>
</tr>
</table>
<?
echo $skin->close_simplebloc();
?>