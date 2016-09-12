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
 * Affichage des utilisateurs "rattachables" à l'espace de travail ou au groupe courant
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Gestion du filtrage.
 * Il est possible de filtrer les utilisateurs par 'lettre'
 */
$arrFilter = array();

// On ne veut pas les caractères % et | dans la recherche avec LIKE
$pattern = '/%|_/';

// Lecture SESSION
if (isset($_SESSION['system']['directoryform']) && !isset($_GET['reset'])) $arrFilter = $_SESSION['system']['directoryform'];

// Lecture Params
if (isset($_POST['ploopi_lastname']) && !preg_match($pattern, $_POST['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = $_POST['ploopi_lastname'];
if (isset($_POST['ploopi_firstname']) && !preg_match($pattern, $_POST['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = $_POST['ploopi_firstname'];
if (isset($_POST['ploopi_login']) && !preg_match($pattern, $_POST['ploopi_login'])) $arrFilter['ploopi_login'] = $_POST['ploopi_login'];
if (isset($_POST['ploopi_email']) && !preg_match($pattern, $_POST['ploopi_email'])) $arrFilter['ploopi_email'] = $_POST['ploopi_email'];
if (isset($_POST['ploopi_last_connection_1'])) $arrFilter['ploopi_last_connection_1'] = $_POST['ploopi_last_connection_1'];
if (isset($_POST['ploopi_last_connection_2'])) $arrFilter['ploopi_last_connection_2'] = $_POST['ploopi_last_connection_2'];

// Affectation de valeurs par défaut si non défini
if (!isset($arrFilter['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = '';
if (!isset($arrFilter['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = '';
if (!isset($arrFilter['ploopi_login'])) $arrFilter['ploopi_login'] = '';
if (!isset($arrFilter['ploopi_email'])) $arrFilter['ploopi_email'] = '';
if (!isset($arrFilter['ploopi_last_connection_1'])) $arrFilter['ploopi_last_connection_1'] = '';
if (!isset($arrFilter['ploopi_last_connection_2'])) $arrFilter['ploopi_last_connection_2'] = '';

$_SESSION['system']['directoryform'] = $arrFilter;


// construction du tableau de filtrage pour recherche les utilisateurs
$arrWhere = array();

switch ($_SESSION['system']['level'])
{
    case _SYSTEM_GROUPS :
        if (!ploopi_isadmin())
        {
            // filtrage sur les groupes partagés
            if (!empty($groups['list'][$groupid]['groups'])) $arrWhere[] = '( gu.id_group IN ('.implode(',',array_keys($groups['list'][$groupid]['groups'])).'))';
            else $arrWhere[] = 'gu.id_group = 0';
        }

        $currentusers = "
            SELECT  ploopi_group_user.id_user

            FROM    ploopi_group_user

            WHERE   ploopi_group_user.id_group = {$groupid}
        ";

        $arrWhere[] = "u.id NOT IN ({$currentusers})";
    break;

    case _SYSTEM_WORKSPACES :
        if (!ploopi_isadmin())
        {
            // filtrage sur les groupes partagés
            if (!empty($workspaces['list'][$workspaceid]['groups'])) $arrWhere[] = 'gu.id_group IN ('.implode(',',array_keys($workspaces['list'][$workspaceid]['groups'])).')';
            else $arrWhere[] = "gu.id_group = 0";
        }

        $currentusers = "
            SELECT  ploopi_workspace_user.id_user

            FROM    ploopi_workspace_user

            WHERE   ploopi_workspace_user.id_workspace = {$workspaceid}
        ";

        $arrWhere[] = "u.id NOT IN ({$currentusers})";
    break;
}

$strWhere = (empty($arrWhere)) ? '' : ' AND '.implode(' AND ', $arrWhere);

// Filtre vide ?
if (trim(implode('', $arrFilter)) != '') $alphaTabItem = 99; // tous
else
{
    // aucun caractère de filtrage sélectionné. On recherche si on en met un par défaut (si trop d'utilisateurs) ou si on sélectionne "tous"

    $alphaTabItem = (empty($_GET['alphaTabItem'])) ? ploopi_getsessionvar('system_alphatabitem') : $_GET['alphaTabItem'];

    if (is_null($alphaTabItem))
    {
        $db->query("
            SELECT      count(distinct(u.id)) as nbuser

            FROM        ploopi_user u,
                        ploopi_group_user gu

            WHERE       gu.id_user = u.id
            {$strWhere}
        ");

        $fields = $db->fetchrow();

        $c = $fields['nbuser'];

        if ($_SESSION['system']['level'] == _SYSTEM_GROUPS)
        {
            // Utilisateurs non rattachés
            $db->query("
                SELECT      count(distinct(u.id)) as nbuser

                FROM        ploopi_user u

                WHERE       u.id NOT IN (SELECT distinct(id_user) FROM ploopi_group_user gu)
            ");

            $fields = $db->fetchrow();

            $c += $fields['nbuser'];
        }

        if ($c < 100) $alphaTabItem = 99;
    }
}

ploopi_setsessionvar('system_alphatabitem', $alphaTabItem);
?>
<div style="padding: 4px;">
    <?php
    $tabs_char = array();

    // Génération des onglets
    for($i=1;$i<27;$i++)
        $tabs_char[$i] =
            array(
                'title' => chr($i+64),
                'url' => "admin.php?usrTabItem=tabUserAttach&alphaTabItem={$i}&reset"
            );

    $tabs_char[98] =
        array(
            'title' => '#',
            'url' => 'admin.php?usrTabItem=tabUserAttach&alphaTabItem=98&reset'
        );

    $tabs_char[99] =
        array(
            'title' => '<em>tous</em>',
            'url' => 'admin.php?usrTabItem=tabUserAttach&alphaTabItem=99&reset'
        );

    echo $skin->create_tabs($tabs_char, $alphaTabItem);
    ?>
</div>

<form action="<?php echo ploopi_urlencode('admin.php?usrTabItem=tabUserAttach'); ?>" method="post">
<div class="ploopi_va" style="padding:6px;">
    <label>Nom: </label>
    <input type="text" class="text" name="ploopi_lastname" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_lastname']); ?>" style="width:100px;" tabindex="100" />

    <label>Prénom: </label>
    <input type="text" class="text" name="ploopi_firstname" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_firstname']); ?>" style="width:100px;" tabindex="105" />

    <label>Identifiant: </label>
    <input type="text" class="text" name="ploopi_login" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_login']); ?>" style="width:100px;" tabindex="110" />

    <label>Email: </label>
    <input type="text" class="text" name="ploopi_email" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_email']); ?>" style="width:150px;" tabindex="120" />

    <label>Connexion entre le: </label>
    <input type="text" class="text" name="ploopi_last_connection_1" id="ploopi_last_connection_1" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_last_connection_1']); ?>" style="width:100px;" tabindex="116" />
    <? ploopi_open_calendar('ploopi_last_connection_1'); ?>
    <label>et le: </label>
    <input type="text" class="text" name="ploopi_last_connection_2" id="ploopi_last_connection_2" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_last_connection_2']); ?>" style="width:100px;" tabindex="117" />
    <? ploopi_open_calendar('ploopi_last_connection_2'); ?>

    <input type="submit" class="button" value="Filtrer" tabindex="150" />
    <input type="button" class="button" value="Réinitialiser" onclick="document.location.href='<?php echo ploopi_urlencode('admin.php?usrTabItem=tabUserAttach&reset'); ?>';" tabindex="160" />
</div>
</form>

<?php
$strWhereName = '';

// Filtrage par lettre + nom
if ($alphaTabItem == 99) // tous ou recherche
{
    $where = array();
    if ($arrFilter['ploopi_lastname'] != '') $where[] = "lastname LIKE '%".$db->addslashes($arrFilter['ploopi_lastname'])."%'";
    if ($arrFilter['ploopi_firstname'] != '') $where[] = "firstname LIKE '%".$db->addslashes($arrFilter['ploopi_firstname'])."%'";
    if ($arrFilter['ploopi_login'] != '') $where[] = "login LIKE '%".$db->addslashes($arrFilter['ploopi_login'])."%'";
    if ($arrFilter['ploopi_email'] != '') $where[] = "email LIKE '%".$db->addslashes($arrFilter['ploopi_email'])."%'";
    if ($arrFilter['ploopi_last_connection_1'] != '') $where[] = "last_connection >= '".ploopi_local2timestamp($arrFilter['ploopi_last_connection_1'], '00:00:00')."'";
    if ($arrFilter['ploopi_last_connection_2'] != '') $where[] = "last_connection <= '".ploopi_local2timestamp($arrFilter['ploopi_last_connection_2'], '23:59:59')."'";

    $strWhereName = empty($where) ? '' : ' AND '.implode(' AND ', $where);
}
else
{
    // 98 : # => non alpha
    if ($alphaTabItem == 98) $strWhereName= " AND ASCII(LCASE(LEFT(u.lastname,1))) NOT BETWEEN 97 AND 122 ";
    // alpha
    else $strWhereName = " AND ASCII(LCASE(LEFT(u.lastname,1))) = ".($alphaTabItem+96).' ';
}

$db->query("
    SELECT      u.id,
                u.lastname,
                u.firstname,
                u.login,
                u.service,
                u.entity

    FROM        ploopi_user u,
                ploopi_group_user gu

    WHERE      gu.id_user = u.id
    {$strWhere}

    {$strWhereName}

    GROUP BY    u.id
");

$arrUsers = $db->getarray();

if ($_SESSION['system']['level'] == _SYSTEM_GROUPS)
{
    // Utilisateurs non rattachés
    $db->query("
        SELECT      u.id,
                    u.lastname,
                    u.firstname,
                    u.login,
                    u.service,
                    u.entity

        FROM        ploopi_user u

        WHERE       u.id NOT IN (SELECT distinct(id_user) FROM ploopi_group_user gu)

        {$strWhereName}

        GROUP BY    u.id
    ");

    $arrUsers = array_merge($arrUsers, $db->getarray());
}

$intNbRep = sizeof($arrUsers);

if ($intNbRep > 2000)
{
    ?>
    <div style="padding:4px;text-align:center;" class="error">Trop de réponses (<?php echo $intNbRep; ?>)</div>
    <?php
}
else
{

    $columns = array();
    $values = array();

    $columns['auto']['name'] =
        array(
            'label' => _SYSTEM_LABEL_LASTNAME.', '._SYSTEM_LABEL_FIRSTNAME,
            'options' => array('sort' => true)
        );

    $columns['right']['service'] =
        array(
            'label' => _SYSTEM_LABEL_SERVICE,
            'width' => 150,
            'options' => array('sort' => true)
        );

    $columns['right']['entity'] =
        array(
            'label' => _SYSTEM_LABEL_ENTITY,
            'width' => 150,
            'options' => array('sort' => true)
        );

    $columns['right']['origin'] =
        array(
            'label' => _SYSTEM_LABEL_ORIGIN,
            'width' => 150,
            'options' => array('sort' => true)
        );

    $columns['right']['login'] =
        array(
            'label' => _SYSTEM_LABEL_LOGIN,
            'width' => 150,
            'options' => array('sort' => true)
        );

    $columns['actions_right']['actions'] =
        array(
            'label' => '&nbsp;',
            'width' => 24
        );

    $c = 0;

    $user = new user();

    foreach($arrUsers as $fields)
    {
        $user->fields['id'] = $fields['id'];
        $groups = $user->getgroups();
        if (!empty($groups))
        {
            $currentgroup = current($groups);
            $values[$c]['values']['origin']     = array('label' => '<a href="'.ploopi_urlencode("admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&groupid={$currentgroup['id']}&alphaTabItem=".(ord(strtolower($fields['lastname']))-96)).'">'.ploopi_htmlentities($currentgroup['label']).'</a>');
        }
        else
        {
            $values[$c]['values']['origin']     = array('label' => 'non rattaché', 'style' => 'font-style:italic;');
        }

        $values[$c]['values']['name']       = array('label' => ploopi_htmlentities("{$fields['lastname']}, {$fields['firstname']}"));
        $values[$c]['values']['login']      = array('label' => ploopi_htmlentities($fields['login']));
        $values[$c]['values']['service']    = array('label' => ploopi_htmlentities($fields['service']));
        $values[$c]['values']['entity']    = array('label' => ploopi_htmlentities($fields['entity']));
        $values[$c]['values']['actions']    = array('label' => '<a style="float:left;display:block;margin:2px;" href="'.ploopi_urlencode("admin.php?op=attach_user&userid={$fields['id']}&alphaTabItem={$alphaTabItem}").'"><img style="float:left;display:block;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_attach.png" title="'._SYSTEM_LABEL_ATTACH.'"></a>');
        $c++;
    }

    $skin->display_array($columns, $values, 'array_userlist', array('sortable' => true, 'orderby_default' => 'name', 'limit' => 50));
}

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    ?>
    <p class="ploopi_va" style="padding: 4px;">
        <span style="margin-right: 5px;">Légende:</span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_user.png" />
        <span style="margin-right: 5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupmanager.png" />
        <span style="margin-right: 5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPMANAGER]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupadmin.png" />
        <span style="margin-right: 5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPADMIN]); ?></span>
        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_systemadmin.png" />
        <span style="margin-right: 5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_SYSTEMADMIN]); ?></span>
    </p>
    <?php
}
?>
