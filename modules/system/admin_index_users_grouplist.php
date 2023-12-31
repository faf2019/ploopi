<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
        ploopi\db::get()->query("
            SELECT      count(ploopi_group.id) as nbgroup

            FROM        ploopi_group

            INNER JOIN  ploopi_workspace_group
            ON          ploopi_workspace_group.id_group = ploopi_group.id
            AND         ploopi_workspace_group.id_workspace = {$workspaceid}
        ");
        $fields = ploopi\db::get()->fetchrow();
        if ($fields['nbgroup'] < 25) $alphaTabItem = 99;
    }
}

?>
<div style="padding:4px;">
    <?php
    $tabs_char = array();

    // Génération des onglets
    for($i=1;$i<27;$i++)
        $tabs_char[$i] =
            array(
                'title' => chr($i+64),
                'url' => "admin.php?alphaTabItem={$i}"
            );

    $tabs_char[98] =
        array(
            'title' => '#',
            'url' => 'admin.php?alphaTabItem=98'
        );

    $tabs_char[99] =
        array(
            'title' => '<em>tous</em>',
            'url' => 'admin.php?alphaTabItem=99'
        );

    echo ploopi\skin::get()->create_tabs($tabs_char, $alphaTabItem);
    ?>
</div>

<form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
    <span><?php echo _SYSTEM_LABEL_USER; ?> :</span>
    <input class="text" ID="system_user" name="pattern" type="text" size="15" maxlength="255" value="<?php echo ploopi\str::htmlentities($pattern); ?>">
    <input type="submit" value="<?php echo _PLOOPI_FILTER; ?>" class="button">
    <input type="submit" name="reset" value="<?php echo _PLOOPI_RESET; ?>" class="button">
</p>
</form>

<?php
$where = array();

if ($alphaTabItem == 99) // tous ou recherche
{
    if ($pattern != '')
    {
        $pattern = ploopi\db::get()->addslashes($pattern);
        $where[] .=  "ploopi_group.label LIKE '%{$pattern}%'";
    }
}
else
{
    // 98 : # => non alpha
    if ($alphaTabItem == 98) $where[] = "ASCII(LCASE(LEFT(ploopi_group.label,1))) NOT BETWEEN 97 AND 122";
    // alpha
    else $where[] = "ASCII(LCASE(LEFT(ploopi_group.label,1))) = ".($alphaTabItem+96);
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

$columns['left']['label'] =
    array(
        'label' => _SYSTEM_LABEL_GROUP,
        'width' => '200',
        'options' => array('sort' => true)
    );

$columns['left']['adminlevel'] =
    array(
        'label' => 'Niv.',
        'width' => '70',
        'options' => array('sort' => true)
    );

$columns['auto']['parents'] =
    array(
        'label' => _SYSTEM_LABEL_PARENTS,
        'options' => array('sort' => true)
    );

$columns['actions_right']['actions'] =
    array(
        'label' => '&nbsp;',
        'width' => '70'
    );

$c = 0;

$result = ploopi\db::get()->query($sql);

while ($fields = ploopi\db::get()->fetchrow($result))
{
    $array_parents = system_getparents($fields['parents'], 'group');
    array_shift($array_parents);

    $str_parents = '';
    foreach($array_parents as $parent) $str_parents .= ($str_parents == '') ? $parent['label']: " > {$parent['label']}";

    $action = '<a href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin.php?op=detach_group&orgid={$fields['id']}").'\',\''._SYSTEM_MSG_CONFIRMGROUPDETACH.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_cut.png" title="'._SYSTEM_TITLE_GROUPDETACH.'"></a>';

    $values[$c]['values']['label']      = array('label' => ploopi\str::htmlentities($fields['label']));
    $values[$c]['values']['parents']    = array('label' => ploopi\str::htmlentities($str_parents));

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

    $values[$c]['values']['adminlevel'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/adminlevels/{$icon}.png\" />", 'style' => 'text-align:center;', 'sort_label' => $fields['adminlevel']);

    if ($_SESSION['ploopi']['adminlevel'] >= $fields['adminlevel'])
        $manage_grp =   '<a href="'.ploopi\crypt::urlencode("admin.php?op=modify_group&orgid={$fields['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" title="'._SYSTEM_LABEL_MODIFY.'"></a>'.$action;
    else
        $manage_grp =   '<img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png"><img style="margin:0 2px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png">';

    $values[$c]['values']['actions']        = array('label' => $manage_grp);

    $c++;
}

ploopi\skin::get()->display_array($columns, $values, 'array_grouplist', array('sortable' => true, 'orderby_default' => 'label'));

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    ?>
    <p class="ploopi_va" style="padding:4px;">
        <span style="margin-right:5px;">Légende:</span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_user.png" />
        <span style="margin-right:5px;"><?php echo ploopi\str::htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupmanager.png" />
        <span style="margin-right:5px;"><?php echo ploopi\str::htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPMANAGER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupadmin.png" />
        <span style="margin-right:5px;"><?php echo ploopi\str::htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPADMIN]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_systemadmin.png" />
        <span style="margin-right:5px;"><?php echo ploopi\str::htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_SYSTEMADMIN]); ?></span>
    </p>
    <?php
}
?>
