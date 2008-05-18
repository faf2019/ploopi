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
 * Affichage des utilisateurs "rattachables" � l'espace de travail courant 
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Gestion du filtrage.
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
        // aucun caract�re de filtrage s�lectionn�. On recherche si on en met un par d�faut (si trop d'utilisateurs) ou si on s�lectionne "tous"
        $where = array();

        switch ($_SESSION['system']['level'])
        {
            case _SYSTEM_GROUPS :
                // filtrage sur les groupes partag�s
                if (!empty($groups['list'][$groupid]['groups'])) $where[] = '( gu.id_group IN ('.implode(',',array_keys($groups['list'][$groupid]['groups'])).') OR isnull(gu.id_group))';
                else $where[] = 'gu.id_group = 0';

                $currentusers = $group->getusers();
                if (!empty($currentusers)) $where[] = 'u.id NOT IN ('.implode(',',array_keys($currentusers)).')';
            break;

            case _SYSTEM_WORKSPACES :
                // filtrage sur les groupes partag�s
                if (!empty($workspaces['list'][$workspaceid]['groups'])) $where[] = 'gu.id_group IN ('.implode(',',array_keys($workspaces['list'][$workspaceid]['groups'])).')';
                else $where[] = "gu.id_group = 0";

                $currentusers = $workspace->getusers();
                if (!empty($currentusers)) $where[] = 'u.id NOT IN ('.implode(',',array_keys($currentusers)).')';
            break;

        }

        $select =   "
                    SELECT      count(u.id) as nbuser

                    FROM        ploopi_user u

                    LEFT JOIN   ploopi_group_user gu
                    ON          gu.id_user = u.id

                    WHERE       ".implode(' AND ', $where)."
                    ";

        $db->query($select);
        $fields = $db->fetchrow();
        if ($fields['nbuser'] < 25) $alphaTabItem = 99;
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
        $where[] .=  "(u.lastname LIKE '%{$pattern}%' OR u.firstname LIKE '%{$pattern}%' OR u.login LIKE '%{$pattern}%')";
    }
}
else
{
    $where[] = "u.lastname LIKE '".chr($alphaTabItem+96)."%'";
}

switch ($_SESSION['system']['level'])
{
    case _SYSTEM_GROUPS :
        // filtrage sur les groupes partag�s
        if (!empty($groups['list'][$groupid]['groups'])) $where[] = '( gu.id_group IN ('.implode(',',array_keys($groups['list'][$groupid]['groups'])).') OR isnull(gu.id_group) )';
        else $where[] = 'gu.id_group = 0';

        $currentusers = $group->getusers();
        if (!empty($currentusers)) $where[] = 'u.id NOT IN ('.implode(',',array_keys($currentusers)).')';
    break;

    case _SYSTEM_WORKSPACES :
        // filtrage sur les groupes partag�s
        if (!empty($workspaces['list'][$workspaceid]['groups'])) $where[] = 'gu.id_group IN ('.implode(',',array_keys($workspaces['list'][$workspaceid]['groups'])).')';
        else $where[] = "gu.id_group = 0";

        $currentusers = $workspace->getusers();
        if (!empty($currentusers)) $where[] = 'u.id NOT IN ('.implode(',',array_keys($currentusers)).')';
    break;
}


$select =   "
            SELECT      u.id,
                        u.lastname,
                        u.firstname,
                        u.login,
                        u.service

            FROM        ploopi_user u

            LEFT JOIN   ploopi_group_user gu
            ON          gu.id_user = u.id

            WHERE       ".implode(' AND ', $where)."

            GROUP BY    u.id
            ";

$columns = array();
$values = array();

$columns['left']['name']    = array('label' => _SYSTEM_LABEL_LASTNAME.', '._SYSTEM_LABEL_FIRSTNAME, 'width' => '170', 'options' => array('sort' => true));
$columns['left']['login']       = array('label' => _SYSTEM_LABEL_LOGIN, 'width' => '85', 'options' => array('sort' => true));
$columns['left']['origin']      = array('label' => _SYSTEM_LABEL_ORIGIN, 'width' => '100', 'options' => array('sort' => true));
$columns['auto']['service']     = array('label' => _SYSTEM_LABEL_SERVICE, 'width' => '100', 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => '70');

$c = 0;

$result = $db->query($select);
$user = new user();

while ($fields = $db->fetchrow($result))
{
    $user->fields['id'] = $fields['id'];
    $groups = $user->getgroups();
    if (!empty($groups))
    {
        $currentgroup = current($groups);
        $values[$c]['values']['origin']     = array('label' => '<a href="'.ploopi_urlencode("{$scriptenv}?wspToolbarItem=tabUsers&usrTabItem=tabUserList&groupid={$currentgroup['id']}&alphaTabItem=".(ord(strtolower($fields['lastname']))-96)).'">'.htmlentities($currentgroup['label']).'</a>');
        $service = $fields['service'];
    }
    else
    {
        $values[$c]['values']['origin']     = array('label' => 'non rattach�', 'style' => 'font-style:italic;');
        $service = ' ';
    }

    $values[$c]['values']['name']       = array('label' => htmlentities("{$fields['lastname']}, {$fields['firstname']}"));
    $values[$c]['values']['login']      = array('label' => htmlentities($fields['login']));
    $values[$c]['values']['service']    = array('label' => htmlentities($service));
    $values[$c]['values']['actions']    = array('label' => '<a style="float:left;display:block;margin:2px;" href="'.ploopi_urlencode("{$scriptenv}?op=attach_user&userid={$fields['id']}&alphaTabItem={$alphaTabItem}").'"><img style="float:left;display:block;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_attach.png" title="'._SYSTEM_LABEL_ATTACH.'"></a>');
    $c++;
}

$skin->display_array($columns, $values, 'array_userlist', array('sortable' => true, 'orderby_default' => 'name'));

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    ?>
    <p class="ploopi_va" style="padding:4px;">
        <span style="margin-right:5px;">L�gende:</span>
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



