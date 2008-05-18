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

/**
 * Affichage des utilisateurs du groupe ou de l'espace courant 
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Gestion du filtrage
 * Il est possible de filtrer les utilisateurs par 'lettre'
 */

if (isset($_POST['reset'])) $pattern = '';
else $pattern = (empty($_POST['pattern'])) ? '' : $_POST['pattern'];

if ($pattern != '') $alphaTabItem = 99; // tous
else
{
    $alphaTabItem = (empty($_GET['alphaTabItem'])) ? -1 : $_GET['alphaTabItem'];

    if ($alphaTabItem == -1)
    {
        $select =   "
                    SELECT      count(ploopi_group.id) as nbgroup

                    FROM        ploopi_group

                    INNER JOIN  ploopi_workspace_group
                    ON          ploopi_workspace_group.id_group = ploopi_group.id
                    AND         ploopi_workspace_group.id_workspace = {$workspaceid}
                    ";

        $db->query($select);
        $fields = $db->fetchrow();
        if ($fields['nbgroup'] < 25) $alphaTabItem = 99;
    }
}

?>
<div style="padding:4px;">
    <?
    $tabs_char = array();

    for($i=1;$i<27;$i++) $tabs_char[$i] = array('title' => chr($i+64), 'url' => "{$scriptenv}?alphaTabItem={$i}");

    $tabs_char[99] = array('title' => "&nbsp;tous&nbsp;", 'url' => "{$scriptenv}?alphaTabItem=99");

    echo $skin->create_tabs($tabs_char,$alphaTabItem);
    ?>
</div>

<form action="<? echo $scriptenv; ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
    <span><? echo _SYSTEM_LABEL_USER; ?> :</span>
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

$where = (empty($where)) ? '' : 'WHERE '.implode(' AND ', $where);

$sql =  "
        SELECT      ploopi_group.id,
                    ploopi_group.label,
                    ploopi_group.parents,
                    ploopi_workspace.id as idref,
                    ploopi_workspace.label as labelworkspace,
                    ploopi_workspace_group.adminlevel

        FROM        ploopi_group

        INNER JOIN  ploopi_workspace_group
        ON          ploopi_workspace_group.id_group = ploopi_group.id
        AND         ploopi_workspace_group.id_workspace = {$workspaceid}

        INNER JOIN  ploopi_workspace
        ON          ploopi_workspace.id = ploopi_workspace_group.id_workspace

        {$where}
        ";


$columns = array();
$values = array();

$columns['left']['label']       = array('label' => _SYSTEM_LABEL_GROUP, 'width' => '200', 'options' => array('sort' => true));
$columns['left']['adminlevel']  = array('label' => 'Niv.', 'width' => '50', 'options' => array('sort' => true));
$columns['auto']['parents']     = array('label' => _SYSTEM_LABEL_PARENTS, 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => '70');

$c = 0;

$result = $db->query($sql);

while ($fields = $db->fetchrow($result))
{
    $group = new group();
    $array_parents = $group->getparents($fields['parents']);
    array_shift($array_parents);

    $str_parents = '';
    foreach($array_parents as $parent) $str_parents .= ($str_parents == '') ? $parent['label']: " > {$parent['label']}";

    $action = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=detach_group&orgid={$fields['id']}").'\',\''._SYSTEM_MSG_CONFIRMGROUPDETACH.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_cut.png" alt="'._SYSTEM_LABEL_DETACH.'"></a>';

    $values[$c]['values']['label']      = array('label' => htmlentities($fields['label']));
    $values[$c]['values']['parents']    = array('label' => htmlentities($str_parents));

    switch($fields['adminlevel'])
    {
        case _PLOOPI_ID_LEVEL_USER:
            $icon = 'level_user';
        break;
        case _PLOOPI_ID_LEVEL_GROUPMANAGER:
            $icon = 'level_groupmanager';
        break;
        case _PLOOPI_ID_LEVEL_GROUPADMIN:
            $icon = 'level_groupadmin';
        break;
        case _PLOOPI_ID_LEVEL_SYSTEMADMIN:
            $icon = 'level_systemadmin';
        break;
    }

    $values[$c]['values']['adminlevel'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/adminlevels/{$icon}.png\" />", 'sort_label' => $fields['adminlevel']);

    if ($_SESSION['ploopi']['adminlevel'] >= $fields['adminlevel'])
        $manage_grp =   '<a href="'.ploopi_urlencode("{$scriptenv}?op=modify_group&orgid={$fields['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" title="'._SYSTEM_LABEL_MODIFY.'"></a>'.$action;
    else
        $manage_grp =   '<img src="'.$_SESSION['ploopi']['template_path'].'/img/ico_noway.gif" title=""><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png" title="">';


    $values[$c]['values']['actions']        = array('label' => $manage_grp);

    $c++;
}

$skin->display_array($columns, $values, 'array_grouplist', array('sortable' => true, 'orderby_default' => 'name'));

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    ?>
    <p class="ploopi_va" style="padding:4px;">
        <span style="margin-right:5px;">Légende:</span>
        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_user.png" />
        <span style="margin-right:5px;"><? echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]); ?></span>
        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupmanager.png" />
        <span style="margin-right:5px;"><? echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPMANAGER]); ?></span>
        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupadmin.png" />
        <span style="margin-right:5px;"><? echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPADMIN]); ?></span>
        <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_systemadmin.png" />
        <span style="margin-right:5px;"><? echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_SYSTEMADMIN]); ?></span>
    </p>
    <?
}
?>
