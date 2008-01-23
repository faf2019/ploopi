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
echo $skin->open_simplebloc(_SYSTEM_LABEL_ACTIONHISTORY);

$maxres = 100;

$search_pattern = array();
$search_pattern['date'] = (isset($_POST['filter_date'])) ? $_POST['filter_date'] : '';
$search_pattern['date2'] = (isset($_POST['filter_date2'])) ? $_POST['filter_date2'] : '';
$search_pattern['user'] = (isset($_POST['filter_user'])) ? $_POST['filter_user'] : '';
$search_pattern['module'] = (isset($_POST['filter_module'])) ? $_POST['filter_module'] : '';
$search_pattern['action'] = (isset($_POST['filter_action'])) ? $_POST['filter_action'] : '';
$search_pattern['record'] = (isset($_POST['filter_record'])) ? $_POST['filter_record'] : '';
$search_pattern['ip'] = (isset($_POST['filter_ip'])) ? $_POST['filter_ip'] : '';

$where = array();


if (!empty($search_pattern['date'])) 	$where[] = "ploopi_user_action_log.timestp >= '".$db->addslashes(ploopi_local2timestamp($search_pattern['date']))."'";
if (!empty($search_pattern['date2'])) 	$where[] = "ploopi_user_action_log.timestp <= '".$db->addslashes(ploopi_timestamp_add(ploopi_local2timestamp($search_pattern['date2']),0,0,0,0,1))."'";
if (!empty($search_pattern['user'])) 	$where[] = "login LIKE '%".$db->addslashes($search_pattern['user'])."%'";
if (!empty($search_pattern['module'])) 	$where[] = "ploopi_module.label LIKE '%".$db->addslashes($search_pattern['module'])."%'";
if (!empty($search_pattern['action'])) 	$where[] = "ploopi_mb_action.label LIKE '%".$db->addslashes($search_pattern['action'])."%'";
if (!empty($search_pattern['record'])) 	$where[] = "ploopi_user_action_log.id_record LIKE '%".$db->addslashes($search_pattern['record'])."%'";
if (!empty($search_pattern['ip'])) 		$where[] = "ploopi_user_action_log.ip LIKE '".$db->addslashes($search_pattern['ip'])."%'";

$wheresql = (empty($where)) ? '' : ' WHERE '.implode(' AND ', $where);

if (!empty($_POST['historyoption']))
{
	switch($_POST['historyoption'])
	{
		case 'delete':
			$sql =  "
					DELETE			ploopi_user_action_log
					FROM			ploopi_user_action_log
					LEFT JOIN		ploopi_user ON ploopi_user_action_log.id_user = ploopi_user.id
					LEFT JOIN		ploopi_module ON ploopi_user_action_log.id_module = ploopi_module.id
					LEFT JOIN		ploopi_mb_action
					ON 				ploopi_user_action_log.id_action = ploopi_mb_action.id_action
					AND				ploopi_mb_action.id_module_type = ploopi_module.id_module_type
					{$wheresql}
					";
			$db->query($sql);

			ploopi_redirect("$scriptenv?op=actionhistory");
		break;

		case 'exportcsv':
			@ob_end_clean();

			$sql =  "
					SELECT			ploopi_user_action_log.*,
									ploopi_user.login,
									ploopi_user.firstname,
									ploopi_user.lastname,
									ploopi_module.label as label_module,
									ploopi_mb_action.label as label_action
					FROM			ploopi_user_action_log
					LEFT JOIN		ploopi_user ON ploopi_user_action_log.id_user = ploopi_user.id
					LEFT JOIN		ploopi_module ON ploopi_user_action_log.id_module = ploopi_module.id
					LEFT JOIN		ploopi_mb_action
					ON				ploopi_user_action_log.id_action = ploopi_mb_action.id_action
					AND				ploopi_mb_action.id_module_type = ploopi_module.id_module_type
					{$wheresql}
					ORDER BY		ploopi_user_action_log.timestp DESC
					";

			$db->query($sql);

			header("Cache-control: private");
			header("Content-type: text/x-csv");
			header("Content-Disposition: attachment; filename=actionlog.csv");
			header("Pragma: public");

			echo "\"timestamp\";\"ip\";\"id_user\";\"login\";\"id_module\";\"module\";\"id_action\";\"action\";\"record\"\r\n";
			while($row = $db->fetchrow())
			{
				$login = (is_null($row['login'])) ? "supprimé ({$row['id_user']})" : $row['login'];
				$module = (is_null($row['label_module'])) ? "supprimé ({$row['id_module']})" : $row['label_module'];
				$action = (is_null($row['label_action'])) ? "supprimée ({$row['id_action']})" : $row['label_action'];

				echo "\"{$row['timestp']}\";\"{$row['ip']}\";\"{$row['id_user']}\";\"{$login}\";{$row['id_module']};\"{$module}\";\"{$row['id_action']}\";\"{$action}\";\"".addslashes($row['id_record'])."\"\r\n";
			}

			ploopi_die();
		break;
	}
}

?>

<form action="<? echo $scriptenv; ?>" method="post" id="form_loghistory">
<input type="hidden" name="op" value="actionhistory">
<input type="hidden" name="historyoption" id="historyoption" value="">
<div style="border-bottom:2px solid #c0c0c0;padding:4px;">
	<div style="float:left;width:50%;" class="ploopi_form">
		<p>
			<label>Entre le (date):</label>
			<input type="text" class="text" name="filter_date" id="filter_date" style="width:100px;" value="<? echo htmlentities($search_pattern['date']); ?>"><a href="#" onclick="javascript:ploopi_calendar_open('filter_date', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
		</p>
		<p>
			<label>et le (date):</label>
			<input type="text" class="text" name="filter_date2" id="filter_date2" style="width:100px;" value="<? echo htmlentities($search_pattern['date2']); ?>"><a href="#" onclick="javascript:ploopi_calendar_open('filter_date2', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
		</p>
		<p>
			<label>Utilisateur:</label>
			<input type="text" class="text" name="filter_user" value="<? echo htmlentities($search_pattern['user']); ?>">
		</p>
	</div>

	<div style="float:left;width:50%;" class="ploopi_form">
		<p>
			<label>Module:</label>
			<input type="text" class="text" name="filter_module" value="<? echo htmlentities($search_pattern['module']); ?>">
		</p>
		<p>
			<label>Action:</label>
			<input type="text" class="text" name="filter_action" value="<? echo htmlentities($search_pattern['action']); ?>">
		</p>
		<p>
			<label>Enregistrement:</label>
			<input type="text" class="text" name="filter_record" value="<? echo htmlentities($search_pattern['record']); ?>">
		</p>
		<p>
			<label>IP:</label>
			<input type="text" class="text" name="filter_ip" value="<? echo htmlentities($search_pattern['ip']); ?>">
		</p>
	</div>
	<div style="clear:both;text-align:right;padding:4px;">
		<input type="button" class="button" value="Effacer les logs (selon le filtre)" onclick="javascript:if (confirm('<? echo _SYSTEM_MSG_CONFIRMLOGDELETE; ?>')) {$('historyoption').value='delete';$('form_loghistory').submit();}">
		<input type="button" class="button" value="Export CSV" onclick="javascript:$('historyoption').value='exportcsv';$('form_loghistory').submit();">
		<input type="submit" class="button" value="Filtrer">
	</div>
</div>
</form>

<?

$sql =  "
		SELECT		count(*) as c
		FROM		ploopi_user_action_log
		LEFT JOIN	ploopi_user ON ploopi_user_action_log.id_user = ploopi_user.id
		LEFT JOIN	ploopi_module ON ploopi_user_action_log.id_module = ploopi_module.id
		LEFT JOIN	ploopi_mb_action
		ON 			ploopi_user_action_log.id_action = ploopi_mb_action.id_action
		AND			ploopi_mb_action.id_module_type = ploopi_module.id_module_type
		{$wheresql}
		";

$db->query($sql);
$row = $db->fetchrow();
$count = $row['c'];

?>
<div style="padding:4px;border-bottom:1px solid #c0c0c0;background:#e0e0e0;"><b><? echo $count; ?> élément(s) trouvés</b> <? if ($count > $maxres) { ?>- Affichage des <? echo $maxres; ?> premiers résultats<? } ?></div>
<?

$sql =  "
		SELECT		ploopi_user_action_log.*,
					ploopi_user.login, ploopi_user.firstname, ploopi_user.lastname,
					ploopi_module.label as label_module,
					ploopi_mb_action.label as label_action
		FROM		ploopi_user_action_log
		LEFT JOIN	ploopi_user ON ploopi_user_action_log.id_user = ploopi_user.id
		LEFT JOIN	ploopi_module ON ploopi_user_action_log.id_module = ploopi_module.id
		LEFT JOIN	ploopi_mb_action
		ON 			ploopi_user_action_log.id_action = ploopi_mb_action.id_action
		AND			ploopi_mb_action.id_module_type = ploopi_module.id_module_type
		{$wheresql}
		LIMIT		0,{$maxres}
		";

$db->query($sql);

$columns = array();
$values = array();

$columns['left']['timestp'] = array('label' => 'Date/Heure', 'width' => '130', 'options' => array('sort' => true));
$columns['left']['ip'] 		= array('label' => 'IP client', 'width' => '110', 'options' => array('sort' => true));
$columns['left']['login'] 	= array('label' => 'Login', 'width' => '100', 'options' => array('sort' => true));
$columns['left']['module'] 	= array('label' => 'Module', 'width' => '100', 'options' => array('sort' => true));
$columns['left']['action'] 	= array('label' => 'Action', 'width' => '200', 'options' => array('sort' => true));
$columns['auto']['record'] 	= array('label' => 'Enregistrement', 'options' => array('sort' => true));

$c = 0;

while($row = $db->fetchrow())
{
	$date_local = ploopi_timestamp2local($row['timestp']);

	$values[$c]['values']['ip'] 	= array('label' => htmlentities($row['ip']));

	$values[$c]['values']['timestp'] 	= array('label' => htmlentities("{$date_local['date']} {$date_local['time']}"), 'sort_label' => $row['timestp']);

	if (is_null($row['login'])) $values[$c]['values']['login'] 	= array('label' => 'supprimé', 'style' => 'font-style:italic;');
	else $values[$c]['values']['login'] 	= array('label' => htmlentities($row['login']));

	if (is_null($row['label_module'])) $values[$c]['values']['module'] 	= array('label' => 'supprimé', 'style' => 'font-style:italic;');
	else $values[$c]['values']['module'] 	= array('label' => htmlentities($row['label_module']));

	if (is_null($row['label_action'])) $values[$c]['values']['action'] 	= array('label' => 'supprimée', 'style' => 'font-style:italic;');
	else $values[$c]['values']['action'] 	= array('label' => htmlentities($row['label_action']));

	$values[$c]['values']['record'] 	= array('label' => htmlentities($row['id_record']));
	$c++;
}

$skin->display_array($columns, $values, 'array_actionlog', array('sortable' => true, 'orderby_default' => 'timestp', 'sort_default' => 'DESC'));

echo $skin->close_simplebloc();
?>
