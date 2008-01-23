<?
$style = 'style="width:250px;"';

$values = explode('||',$fields['values']);

switch($fields['type'])
{

	case 'textarea':
	
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo ploopi_nl2br($replies[$fields['id']][0]);
		}
		else
		{
			?>
			<textarea class="text" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="text" rows="5"><? if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0]; ?></textarea>
			<?
		}
		
	break;

	case 'tablelink':
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0];
		}
		else
		{
			$select = "SELECT distinct(value) FROM ploopi_mod_forms_reply_field WHERE id_field = '{$values[0]}' AND value <> ''";
			$rs_detail = $db->query($select);
			?>
			<select class="select" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="select">
			<option></option>
			<?
			while($row = $db->fetchrow($rs_detail))
			{
				$selected = (isset($replies[$fields['id']]) && $row['value'] == $replies[$fields['id']][0])? 'selected' : '';
				?>
				<option <? echo $selected; ?> value="<? echo $row['value']; ?>"><? echo $row['value']; ?></option>
				<?
			}
			?>
			</select>
			<?
		}
	break;

	case 'select':
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0];
		}
		else
		{
			?>
			<select class="select" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="select">
			<option></option>
			<?
			foreach($values as $value)
			{
				$selected = (isset($replies[$fields['id']]) && $value == $replies[$fields['id']][0])? 'selected' : '';
				?>
				<option <? echo $selected; ?> value="<? echo $value; ?>"><? echo $value; ?></option>
				<?
			}
			?>
			</select>
			<?
		}
	break;

	case 'color':
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0];
		}
		else
		{
			?>
			<select class="select" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="select" onchange="this.style.backgroundColor=this.value;" style="background-color:<? echo $replies[$fields['id']][0]; ?>">
			<option></option>
			<?
			foreach($values as $value)
			{
				$selected = (isset($replies[$fields['id']]) && $value == $replies[$fields['id']][0])? 'selected' : '';
				?>
				<option <? echo $selected; ?> value="<? echo $value; ?>" style="background-color:<? echo $value; ?>;color:<? echo $value; ?>;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<?
			}
			?>
			</select>
			<?
		}
	break;

	case 'checkbox':
		?>
		<table cellpadding="2" cellspacing="1">
		<tr>
		<?
		$s = sizeof($values);
		$v = 0;
		for ($c=$fields['cols'];$c>=1;$c--) // for each column
		{
			$d = (($s-($s%$c))/$c) + ($s%$c>0); // nb element to place in current column
			echo "<td valign=\"top\"><table cellpadding=\"2\" cellspacing=\"1\">";
			for($i=$v;$i<$v+$d;$i++)
			{
				$value = $values[$i];
				$checked = (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']]))? 'checked' : '';
				?>
				<tr>
					<?
					if ($op == 'forms_reply_display')
					{
						if ($checked == 'checked')
						{
							?>
							<td><img src="./modules/forms/img/checked.gif"></td>
							<?
						}
						else
						{
							?>
							<td><img src="./modules/forms/img/unchecked.gif"></td>
							<?
						}
					}
					else
					{
						?>
						<td><input <? echo $checked; ?> type="checkbox" name="field_<? echo $fields['id']; ?>[]" value="<? echo $value; ?>"></td>
						<?
					}
					?>
					<td><? echo $value; ?></td>
				</tr>
				<?
			}
			echo "</table></td>";
			$v += $d;
			$s -= $d; // element to place for next columns
		}
		?>
		</tr>
		</table>
		<?
	break;

	case 'radio':
		?>
		<table cellpadding="2" cellspacing="1">
		<tr>
		<?
		$s = sizeof($values);
		$v = 0;
		for ($c=$fields['cols'];$c>=1;$c--) // for each column
		{
			$d = (($s-($s%$c))/$c) + ($s%$c>0); // nb element to place in current column
			echo "<td valign=\"top\"><table cellpadding=\"2\" cellspacing=\"1\">";
			for($i=$v;$i<$v+$d;$i++)
			{
				$value = $values[$i];
				$checked = (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']]))? 'checked' : '';
				?>
				<tr>
					<?
					if ($op == 'forms_reply_display')
					{
						if ($checked == 'checked')
						{
							?>
							<td><img src="./modules/forms/img/checked.gif"></td>
							<?
						}
						else
						{
							?>
							<td><img src="./modules/forms/img/unchecked.gif"></td>
							<?
						}
					}
					else
					{
						?>
						<td><input <? echo $checked; ?> type="radio" name="field_<? echo $fields['id']; ?>[]" value="<? echo $value; ?>"></td>
						<?
					}
					?>
					<td><? echo $value; ?></td>
				</tr>
				<?
			}
			echo "</table></td>";
			$v += $d;
			$s -= $d; // element to place for next columns
		}
		?>
		</tr>
		</table>
		<?
	break;

	case 'file':
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0];
		}
		else
		{
			?>
			<input type="file" name="field_<? echo $fields['id']; ?>" class="text" size="<? echo $maxlength; ?>">
			<?
			if (isset($replies[$fields['id']][0]) && $replies[$fields['id']][0] != '')
			{
				echo $replies[$fields['id']][0].'<a href="'."{$scriptenv}?op=download_file&forms_id={$forms_id}&reply_id={$reply_id}&field_id={$fields['id']}".'"><img style="border:0px" src="./modules/forms/img/link.gif"></a>';
			}
		}
	break;

	break;

	case 'autoincrement':
		if (isset($replies[$fields['id']][0]) && $replies[$fields['id']][0] != '')
		{
			echo $replies[$fields['id']][0];
		}
		else
		{
			$select = "SELECT max(value) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_forms = '{$forms_id}' AND id_field = '{$fields['id']}'";
			$rs_maxinc = $db->query($select);
			$fields = $db->fetchrow($rs_maxinc);
			$maxinc = ($fields['maxinc'] == '' || $fields['maxinc'] == 0) ? 1 : $fields['maxinc']+1;
			echo "$maxinc (à valider)";
		}
	break;

	default:
	case 'text':
		if ($op == 'forms_reply_display')
		{
			if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0];
		}
		else
		{
			$maxlength = ($fields['maxlength'] > 0 && $fields['maxlength'] != '') ? $fields['maxlength'] : '50';

			if ($fields['format'] == 'date')
			{
				?>
				<input type="text" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" id="field_<? echo $fields['id']; ?>" value="<? if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0]; ?>" class="text" size="<? echo $maxlength; ?>" maxlength="<? echo $maxlength; ?>">&nbsp;<a href="#" onclick="javascript:ploopi_calendar_open('field_<? echo $fields['id']; ?>', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
				<?
			}
			else
			{
				?>
				<input type="text" <? echo $style; ?> name="field_<? echo $fields['id']; ?>" value="<? if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0]; ?>" class="text" size="<? echo $maxlength; ?>" maxlength="<? echo $maxlength; ?>">
				<?
			}
		}
	break;
}
if (isset($field_formats[$fields['format']]) && $fields['type'] == 'text')
{
		switch ($fields['format'])
		{
			case 'date':
				echo '(jj/mm/aaaa)';
			break;

			case 'heure':
				echo '(hh:mm)';
			break;
		}
		//echo " ({$field_formats[$fields['format']]})";
}
?>
