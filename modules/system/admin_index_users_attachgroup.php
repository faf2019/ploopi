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
// Affichage des groupes "rattachables" à l'espace courant

if (isset($_POST['reset'])) $pattern = '';
else $pattern = (empty($_POST['pattern'])) ? '' : $_POST['pattern'];

// liste des groupes (id) "rattachables" (sans filtrage)

$grp_list = array_diff(array_keys($workspaces['list'][$workspaceid]['groups']),array_keys($workspace->getgroups()));

if ($pattern != '') $alphaTabItem = 99; // tous
else
{
	$alphaTabItem = (empty($_GET['alphaTabItem'])) ? -1 : $_GET['alphaTabItem'];

	if ($alphaTabItem == -1)
	{
		// aucun caractère de filtrage sélectionné. On recherche si on en met un par défaut (si trop de groupes) ou si on sélectionne "tous"
		if (sizeof($grp_list) < 25) $alphaTabItem = 99;
	}
}

?>
<div style="padding:4px;">
	<?
	$tabs_char = array();

	for($i=1;$i<27;$i++) $tabs_char[$i] = array('title' => chr($i+64), 'url' => "{$scriptenv}?alphaTabItem={$i}");

	$tabs_char[99] = array('title' => "&nbsp;tous&nbsp;", 'url' => "{$scriptenv}?alphaTabItem=99");

	echo $skin->create_tabs('',$tabs_char,$alphaTabItem);
	?>
</div>

<form action="<? echo $scriptenv; ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
	<span><? echo _SYSTEM_LABEL_GROUP; ?> :</span>
	<input class="text" ID="system_user" name="pattern" type="text" size="15" maxlength="255" value="<? echo htmlentities($pattern); ?>">
	<input type="submit" value="<? echo _PLOOPI_FILTER; ?>" class="button">
	<input type="submit" name="reset" value="<? echo _PLOOPI_RESET; ?>" class="button">
</p>
</form>

<?
$where = array();

if ($alphaTabItem == 99) // tous ou recherche
{
	if ($pattern != '')
	{
		$pattern = $db->addslashes($pattern);
		$where[] .=  "ploopi_group.label LIKE '%{$pattern}%'";
	}
}
else
{
	$where[] = "ploopi_group.label LIKE '".chr($alphaTabItem+96)."%'";
}

if (!empty($grp_list)) $where[] = 'ploopi_group.id IN ('.implode(',',$grp_list).')';
else $where[] = 'ploopi_group.id = 0';

$sql = 	"
		SELECT 		ploopi_group.id,
					ploopi_group.label,
					ploopi_group.parents

		FROM 		ploopi_group

		WHERE 		".implode(' AND ', $where)."
		";

$columns = array();
$values = array();

$columns['left']['label']		= array('label' => _SYSTEM_LABEL_GROUP, 'width' => '200', 'options' => array('sort' => true));
$columns['auto']['parents']		= array('label' => _SYSTEM_LABEL_PARENTS, 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => '70', 'style' => 'text-align:center;');

$c = 0;

$result = $db->query($sql);

while ($fields = $db->fetchrow($result))
{
	$group = new group();
	$array_parents = $group->getparents($fields['parents']);
	array_shift($array_parents);

	$str_parents = '';
	foreach($array_parents as $parent) $str_parents .= ($str_parents == '') ? $parent['label']: " > {$parent['label']}";

	$values[$c]['values']['label']		= array('label' => htmlentities($fields['label']));
	$values[$c]['values']['parents']	= array('label' => htmlentities($str_parents));
	$values[$c]['values']['actions']	= array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?op=attach_group&orgid={$fields['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_attach.png" title="'._SYSTEM_LABEL_ATTACH.'"></a>');

	$c++;
}

$skin->display_array($columns, $values, 'array_grouplist', array('sortable' => true, 'orderby_default' => 'label'));
?>
