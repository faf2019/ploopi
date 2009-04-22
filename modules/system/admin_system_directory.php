<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Affichage des utilisateurs
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_SYSTEM_LABEL_DIRECTORY);

$intMaxResponse = 250;

$arrFilter = array();

// On ne veut pas les caractères % et | dans la recherche avec LIKE
$pattern = '%|_';

// Lecture SESSION
if (isset($_SESSION['system']['directoryform'])) $arrFilter = $_SESSION['system']['directoryform'];

// Lecture Params
if (isset($_POST['ploopi_lastname']) && !ereg($pattern, $_POST['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = $_POST['ploopi_lastname'];
if (isset($_POST['ploopi_firstname']) && !ereg($pattern, $_POST['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = $_POST['ploopi_firstname'];
if (isset($_POST['ploopi_login']) && !ereg($pattern, $_POST['ploopi_login'])) $arrFilter['ploopi_login'] = $_POST['ploopi_login'];
if (isset($_POST['ploopi_email']) && !ereg($pattern, $_POST['ploopi_email'])) $arrFilter['ploopi_email'] = $_POST['ploopi_email'];

// Affectation de valeurs par défaut si non défini
if (!isset($arrFilter['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = '';
if (!isset($arrFilter['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = '';
if (!isset($arrFilter['ploopi_login'])) $arrFilter['ploopi_login'] = '';
if (!isset($arrFilter['ploopi_email'])) $arrFilter['ploopi_email'] = '';

// Enregistrement SESSION
$_SESSION['system']['directoryform'] = $arrFilter;
?>
<form action="<?php echo ploopi_urlencode('admin.php?sysToolbarItem=directory'); ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:1px solid #ccc;">
    <label>Nom: </label>
    <input type="text" class="text" name="ploopi_lastname" value="<?php echo htmlentities($arrFilter['ploopi_lastname']); ?>" style="width:100px;" />

    <label>Prénom: </label>
    <input type="text" class="text" name="ploopi_firstname" value="<?php echo htmlentities($arrFilter['ploopi_firstname']); ?>" style="width:100px;" />

    <label>Login: </label>
    <input type="text" class="text" name="ploopi_login" value="<?php echo htmlentities($arrFilter['ploopi_login']); ?>" style="width:100px;" />

    <label>Email: </label>
    <input type="text" class="text" name="ploopi_email" value="<?php echo htmlentities($arrFilter['ploopi_email']); ?>" style="width:180px;" />

    <input type="submit" class="button" value="Filtrer" />
    <input type="button" class="button" value="Réinitialiser" onclick="document.location.href='<?php echo ploopi_urlencode('admin.php?sysToolbarItem=directory'); ?>';" />
</p>
</form>
<?php

$arrWhere = array();
$arrWhere[] = '1';

if (!empty($arrFilter['ploopi_lastname'])) $arrWhere[] = "u.lastname LIKE '".$db->addslashes($arrFilter['ploopi_lastname'])."%'";
if (!empty($arrFilter['ploopi_firstname'])) $arrWhere[] = "u.firstname LIKE '".$db->addslashes($arrFilter['ploopi_firstname'])."%'";
if (!empty($arrFilter['ploopi_login'])) $arrWhere[] = "u.login LIKE '".$db->addslashes($arrFilter['ploopi_login'])."%'";
if (!empty($arrFilter['ploopi_email'])) $arrWhere[] = "u.email LIKE '".$db->addslashes($arrFilter['ploopi_email'])."%'";

$db->query("
    SELECT      COUNT(*) as c
    FROM        ploopi_user u
    WHERE       ".implode(' AND ', $arrWhere)."
");

$row = $db->fetchrow();
?>
<div style="padding:4px;background-color:#e0e0e0;border-bottom:1px solid #ccc;">
    <span>Vous pouvez retrouver ici l'ensemble des utilisateurs du sytème avec leur profil complet.<br />Vous ne pouvez cependant pas les gérer. Pour cela vous devez accéder à l'<a href="<?php echo ploopi_urlencode('admin.php?system_level=work'); ?>">interface d'administration des espaces de travail</a>.<br /><strong><?php echo $row['c']; ?> utilisateur(s) trouvé(s).</strong></span>
    <?php
    if ($row['c'] > $intMaxResponse)
    {
        ?><strong class="error">Il y a trop de réponses (<?php echo $intMaxResponse; ?> max), vous devriez préciser votre recherche</strong><?php
    }
    ?>
</div>
<?php
if ($row['c'] > 0 && $row['c'] <= $intMaxResponse)
{
    // Définition des colonnes du tableau (interface)
    $arrResult =
        array(
            'columns' => array(),
            'rows' => array()
        );

    $arrResult['columns']['left']['nom'] =
        array(
            'label' => 'Nom/prénom',
            'width' => '200',
            'options' => array('sort' => true)
        );

    $arrResult['columns']['left']['login'] =
        array(
            'label' => 'Login',
            'width' => '100',
            'options' => array('sort' => true)
        );

    $arrResult['columns']['left']['groups'] =
        array(
            'label' => 'Groupes',
            'width' => '200',
            'options' => array('sort' => true)
        );

    $arrResult['columns']['auto']['workspaces'] =
        array(
            'label' => 'Espaces de travail / Rôles',
            'options' => array('sort' => true)
        );

    $arrResult['columns']['actions_right']['actions'] =
        array(
            'label' => '&nbsp;',
            'width' => 24
        );

    // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
    $ptrRs = $db->query("
        SELECT      u.id,
                    u.lastname,
                    u.firstname,
                    u.login,
                    g.id as groupid,
                    g.label

        FROM        ploopi_user u

        LEFT JOIN   ploopi_group_user gu
        ON          gu.id_user = u.id

        LEFT JOIN   ploopi_group g
        ON          g.id = gu.id_group

        WHERE       ".implode(' AND ', $arrWhere)."
    ");

    // Tableaux qui vont contenir les utilisateurs et les groupes
    $arrUser = array();
    $arrGroup = array();

    while ($row = $db->fetchrow($ptrRs))
    {
        if (!isset($arrUser[$row['id']]))
        {
            $arrUser[$row['id']] = $row;
            $arrUser[$row['id']]['groups'] = array();
        }

        // groupe lié
        if (!empty($row['groupid']))
        {
            $arrUser[$row['id']]['groups'][$row['groupid']] = $row['label'];
            $arrGroup[$row['groupid']] = $row['label'];
        }
    }

    // liste des groupes trouvés
    $strGroupList = implode(',', array_keys($arrGroup));

    // liste des utilisateurs trouvés
    $strUserList = implode(',', array_keys($arrUser));

    // tableau contenant les rôles pour les utilisateurs/groupes trouvés
    $arrRoles = array('groups' => array(), 'users' => array());

    if (!empty($strGroupList))
    {
        // recherche des rôles "groupe"
        $db->query("
            SELECT      wgr.id_group,
                        wgr.id_workspace,
                        r.id,
                        r.id_module,
                        r.label as role_label,
                        m.label as module_label

            FROM        ploopi_role r,
                        ploopi_workspace_group_role wgr,
                        ploopi_module m

            WHERE       wgr.id_role = r.id
            AND         r.id_module = m.id
            AND         wgr.id_group IN ({$strGroupList})
        ");

        while ($row = $db->fetchrow()) $arrRoles['groups'][$row['id_workspace']][$row['id_group']][$row['id']] = $row;
    }

    // recherche des rôles "utilisateur"
    $db->query("
        SELECT      wur.id_user,
                    wur.id_workspace,
                    r.id,
                    r.id_module,
                    r.label as role_label,
                    m.label as module_label

        FROM        ploopi_role r,
                    ploopi_workspace_user_role wur,
                    ploopi_module m

        WHERE       wur.id_role = r.id
        AND         r.id_module = m.id
        AND         wur.id_user IN ({$strUserList})
    ");

    while ($row = $db->fetchrow()) $arrRoles['users'][$row['id_workspace']][$row['id_user']][$row['id']] = $row;

    foreach ($arrUser as $row)
    {
        $objUser = new user();
        $objUser->fields['id'] = $row['id'];

        // récupération et tri des espaces de travail de l'utilisateur
        $arrWorkspaces = $objUser->getworkspaces();
        uasort($arrWorkspaces, create_function('$a,$b', 'return $b[\'adminlevel\'] > $a[\'adminlevel\'];'));

        // tri des groupes par nom
        asort($row['groups']);

        // Indices de tri pour le tableau
        $strSortLabelGroups = implode(',', $row['groups']);
        $strSortLabelWorkspaces = '';

        // conversion du tableau d'espaces en un tableau de liens vers les espaces
        foreach($arrWorkspaces as &$arrWsp)
        {
            // tableau qui va contenir les rôles dont dispose l'utilisateur dans l'espace courant
            $arrUserWspRoles = array();
            if (isset($arrRoles['groups'][$arrWsp['id']]))
            {
                foreach($arrRoles['groups'][$arrWsp['id']] as $intIdGrp => $arrDetail)
                {
                    // L'utilisateur appartient au groupe (donc il a les rôles)
                    if (in_array($intIdGrp, array_keys($row['groups'])))
                    {
                        foreach($arrDetail as $intIdRole => $arrR)
                            $arrUserWspRoles[$intIdRole] =
                                sprintf(
                                    "<a title=\"Accéder à ce rôle\" href=\"%s\">%s</a><span>&nbsp;(</span><a title=\"Accéder à ce module\" href=\"%s\">%s</a><span>)</span>",
                                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$arrWsp['id']}&wspToolbarItem=tabRoles&op=modify_role&roleid={$intIdRole}"),
                                    htmlentities($arrR['role_label']),
                                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$arrWsp['id']}&wspToolbarItem=tabModules&op=modify&moduleid={$arrR['id_module']}"),
                                    htmlentities($arrR['module_label'])
                                );
                    }
                }
            }

            if (isset($arrRoles['users'][$arrWsp['id']][$row['id']]))
            {
                foreach($arrRoles['users'][$arrWsp['id']][$row['id']] as $intIdRole => $arrR)
                    $arrUserWspRoles[$intIdRole] =
                        sprintf(
                                "<a title=\"Accéder à ce rôle\" href=\"%s\">%s</a><span>&nbsp;(</span><a title=\"Accéder à ce module\" href=\"%s\">%s</a><span>)</span>",
                                ploopi_urlencode("admin.php?system_level=work&workspaceid={$arrWsp['id']}&wspToolbarItem=tabRoles&op=modify_role&roleid={$intIdRole}"),
                                htmlentities($arrR['role_label']),
                                ploopi_urlencode("admin.php?system_level=work&workspaceid={$arrWsp['id']}&wspToolbarItem=tabModules&op=modify&moduleid={$arrR['id_module']}"),
                                htmlentities($arrR['module_label'])
                            );
            }

            // Chaine contenant, pour un utilisateur et un espace, la liste des rôles
            $strUserWspRoles = empty($arrUserWspRoles) ? '' : '<span>&nbsp;:&nbsp;</span>'.implode('<span>,&nbsp;</span>', $arrUserWspRoles);

            $strSortLabelWorkspaces .= $arrWsp.',';

            switch($arrWsp['adminlevel'])
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

            $arrWsp =
                sprintf(
                    "<img src=\"%s\" /><a title=\"Accéder à cet espace\" href=\"%s\">%s</a>%s",
                    "{$_SESSION['ploopi']['template_path']}/img/system/adminlevels/{$icon}.png",
                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$arrWsp['id']}"),
                    htmlentities($arrWsp['label']),
                    $strUserWspRoles
                );
        }

        // conversion du tableau de libellé de groupes en un tableau de liens vers les groupes
        foreach($row['groups'] as $intId => &$strLabel)
            $strLabel =
                sprintf(
                    "<a title=\"Accéder à ce groupe\" href=\"%s\">%s</a>",
                    ploopi_urlencode("admin.php?system_level=work&groupid={$intId}"),
                    htmlentities($strLabel)
                );

        $strUserLabel = htmlentities(sprintf("%s %s", $row['lastname'], $row['firstname']));
        $strUserLogin = htmlentities($row['login']);

        // si l'utilisateur est attaché à un groupe, on met un lien vers la fiche de l'utilisateur pour pouvoir la modifier
        if (!empty($row['groups']))
        {
            reset($row['groups']);
            $strUserLabel =
                sprintf(
                    "<a title=\"Accéder à cet utilisateur\" href=\"%s\">%s</a>",
                    ploopi_urlencode("admin.php?system_level=work&groupid=".key($row['groups'])."&wspToolbarItem=tabUsers&op=modify_user&user_id={$row['id']}"),
                    $strUserLabel
                );

            $strUserLogin =
                sprintf(
                    "<a title=\"Accéder à cet utilisateur\" href=\"%s\">%s</a>",
                    ploopi_urlencode("admin.php?system_level=work&groupid=".key($row['groups'])."&wspToolbarItem=tabUsers&op=modify_user&user_id={$row['id']}"),
                    $strUserLogin
                );

        }

        $arrResult['rows'][] =
            array(
                'values' =>
                    array(
                        'nom' => array('label' => $strUserLabel),
                        'login' => array('label' => $strUserLogin),
                        'groups' => array(
                            'label' => (empty($row['label'])) ? '<em>Pas de groupe</em>'  : implode('<br />', $row['groups']),
                            'sort_label' => $strSortLabelGroups
                        ),
                        'workspaces' => array(
                            'label' => (empty($arrWorkspaces)) ? '<em>Pas d\'espace</em>' : implode('<br /> ', $arrWorkspaces),
                            'sort_label' => $strSortLabelWorkspaces
                        ),
                        'actions' => array('label' => '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?ploopi_op=system_delete_user&system_user_id={$row['id']}").'\',\''._SYSTEM_MSG_CONFIRMUSERDELETE.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" title="'._SYSTEM_LABEL_DELETE.'"></a>')
                    )
            );
    }

    $skin->display_array(
        $arrResult['columns'],
        $arrResult['rows'],
        'system_directory',
        array(
            'sortable' => true,
            'orderby_default' => 'login'
        )
    );
}
echo $skin->close_simplebloc();

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
