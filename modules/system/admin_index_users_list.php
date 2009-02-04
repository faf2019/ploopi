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
 * Affichage des utilisateurs du groupe d'utilisateurs ou de l'espace de travail courant
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
        // aucun caractère de filtrage sélectionné. On recherche si on en met un par défaut (si trop d'utilisateurs) ou si on sélectionne "tous"
        switch ($_SESSION['system']['level'])
        {
            case _SYSTEM_GROUPS :
                $select =   "
                            SELECT      count(ploopi_user.id) as nbuser
                            FROM        ploopi_user

                            INNER JOIN  ploopi_group_user ON ploopi_group_user.id_user = ploopi_user.id
                            AND         ploopi_group_user.id_group = {$groupid}
                            ";
                break;
            case _SYSTEM_WORKSPACES :
                $select =   "
                            SELECT      count(ploopi_user.id) as nbuser
                            FROM        ploopi_user

                            INNER JOIN  ploopi_workspace_user ON ploopi_workspace_user.id_user = ploopi_user.id
                            AND         ploopi_workspace_user.id_workspace = {$workspaceid}
                            ";
                break;

        }
        $db->query($select);
        $fields = $db->fetchrow();
        if ($fields['nbuser'] < 25) $alphaTabItem = 99;
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

    echo $skin->create_tabs($tabs_char, $alphaTabItem);
    ?>
</div>

<form action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
    <span><?php echo _SYSTEM_LABEL_USER; ?> :</span>
    <input class="text" ID="system_user" name="pattern" type="text" size="15" maxlength="255" value="<?php echo htmlentities($pattern); ?>">
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
        $pattern = $db->addslashes($pattern);
        $where[] .=  "(ploopi_user.lastname LIKE '%{$pattern}%' OR ploopi_user.firstname LIKE '%{$pattern}%' OR ploopi_user.login LIKE '%{$pattern}%')";
    }
}
else
{
    // 98 : # => non alpha
    if ($alphaTabItem == 98) $where[] = "ASCII(LCASE(LEFT(ploopi_user.lastname,1))) NOT BETWEEN 97 AND 122";
    // alpha
    else $where[] = "ASCII(LCASE(LEFT(ploopi_user.lastname,1))) = ".($alphaTabItem+96);
}

$where = (empty($where)) ? '' : 'WHERE '.implode(' AND ', $where);


switch ($_SESSION['system']['level'])
{
    case _SYSTEM_WORKSPACES :
        $select =   "
                    SELECT      ploopi_user.id,
                                ploopi_user.lastname,
                                ploopi_user.firstname,
                                ploopi_user.login,
                                ploopi_user.service,
                                ploopi_workspace.id as idref,
                                ploopi_workspace.label as label,
                                ploopi_workspace_user.adminlevel

                    FROM        ploopi_user


                    INNER JOIN  ploopi_workspace_user
                    ON          ploopi_workspace_user.id_user = ploopi_user.id
                    AND         ploopi_workspace_user.id_workspace = {$workspaceid}

                    INNER JOIN  ploopi_workspace
                    ON          ploopi_workspace.id = ploopi_workspace_user.id_workspace

                    {$where}
                    ";
    break;

    case _SYSTEM_GROUPS :
        $select =   "
                    SELECT      ploopi_user.id,
                                ploopi_user.lastname,
                                ploopi_user.firstname,
                                ploopi_user.login,
                                ploopi_user.service,
                                ploopi_user.function,
                                ploopi_group.id as idref,
                                ploopi_group.label as label


                    FROM        ploopi_user


                    INNER JOIN  ploopi_group_user
                    ON          ploopi_group_user.id_user = ploopi_user.id
                    AND         ploopi_group_user.id_group = {$groupid}

                    INNER JOIN  ploopi_group
                    ON          ploopi_group.id = ploopi_group_user.id_group

                    {$where}
                    ";
    break;
}

$columns = array();
$values = array();

$columns['left']['name'] = 
    array(
        'label' => _SYSTEM_LABEL_LASTNAME.', '._SYSTEM_LABEL_FIRSTNAME, 
        'width' => 170, 
        'options' => array('sort' => true)
    );
    
$columns['left']['login'] = 
    array(
        'label' => _SYSTEM_LABEL_LOGIN, 
        'width' => 85, 
        'options' => array('sort' => true)
    );
    
if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES) 
    $columns['left']['adminlevel'] = 
        array(
            'label' => 'Niv.', 
            'width' => 50, 
            'options' => array('sort' => true)
        );
        
$columns['left']['origin'] = 
    array(
        'label' => _SYSTEM_LABEL_ORIGIN, 
        'width' => 100, 
        'options' => array('sort' => true)
    );
    
$columns['auto']['service'] = 
    array(
        'label' => _SYSTEM_LABEL_SERVICE, 
        'width' => 100, 
        'options' => array('sort' => true)
    );
    
$columns['actions_right']['actions'] = 
    array(
        'label' => '&nbsp;', 
        'width' => 70
    );

$c = 0;

$result = $db->query($select);
$user = new user();

while ($fields = $db->fetchrow($result))
{
    $user->fields['id'] = $fields['id'];
    $groups = $user->getgroups();
    $currentgroup = current($groups);

    $action = ' <a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=detach_user&user_id={$fields['id']}").'\',\''._SYSTEM_MSG_CONFIRMUSERDETACH.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_cut.png" title="'._SYSTEM_TITLE_USERDETACH.'"></a>
                <a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=delete_user&user_id={$fields['id']}").'\',\''._SYSTEM_MSG_CONFIRMUSERDELETE.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" title="'._SYSTEM_LABEL_DELETE.'"></a>
                ';

    $values[$c]['values']['name']       = array('label' => htmlentities("{$fields['lastname']}, {$fields['firstname']}"));
    $values[$c]['values']['login']      = array('label' => htmlentities($fields['login']));
    $values[$c]['values']['origin']     = array('label' => '<a href="'.ploopi_urlencode("admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&groupid={$currentgroup['id']}&alphaTabItem=".(ord(strtolower($fields['lastname']))-96)).'">'.htmlentities($currentgroup['label']).'</a>');
    $values[$c]['values']['service']    = array('label' => htmlentities($fields['service']));

    switch ($_SESSION['system']['level'])
    {
        case _SYSTEM_WORKSPACES :

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
                $manage_user =  '<a href="'.ploopi_urlencode("admin.php?op=modify_user&user_id={$fields['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" title="'._SYSTEM_LABEL_MODIFY.'"></a>'.$action;
            else
                $manage_user =  '<img src="./modules/system/img/ico_noway.gif" title="">&nbsp;&nbsp;<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_noway.png" alt="">';

            $values[$c]['values']['actions']        = array('label' => $manage_user);

        break;

        case _SYSTEM_GROUPS :

            $values[$c]['values']['actions']        = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=modify_user&user_id={$fields['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" title="'._SYSTEM_LABEL_MODIFY.'"></a>'.$action);
        break;
    }

    $c++;
}

$skin->display_array($columns, $values, 'array_userlist', array('sortable' => true, 'orderby_default' => 'name'));

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    ?>
    <p class="ploopi_va" style="padding:4px;">
        <span style="margin-right:5px;">Légende:</span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_user.png" />
        <span style="margin-right:5px;"><?php echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupmanager.png" />
        <span style="margin-right:5px;"><?php echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPMANAGER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupadmin.png" />
        <span style="margin-right:5px;"><?php echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPADMIN]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_systemadmin.png" />
        <span style="margin-right:5px;"><?php echo htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_SYSTEMADMIN]); ?></span>
    </p>
    <?php
}
?>
