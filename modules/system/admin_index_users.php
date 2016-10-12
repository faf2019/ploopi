<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestion des utilisateurs
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion des classes de gestion des utilisateurs et d'espaces de travail
 */

switch($op)
{
    case 'save_user':
        $user = new ploopi\user();

        // ouverture utilisateur si existant
        if (!empty($_GET['user_id']))
        {
            // ne doit pas modifier le login !
            if (isset($_POST['user_login'])) ploopi\output::redirect('admin.php');

            if (!is_numeric($_GET['user_id']) || !$user->open($_GET['user_id'])) ploopi\output::redirect('admin.php');
        }
        else // nouvel utilisateur
        {
            if (empty($_POST['user_login'])) ploopi\output::redirect('admin.php');

            $_SESSION['system']['save_user'] = $_POST;

            if (!isset($_GET['confirm'])) // pas de confirmation de création demandée
            {
                // test si utilisateur existe déjà => demande de confirmation de création (homonyme ?)
                ploopi\db::get()->query("SELECT id FROM ploopi_user WHERE (lastname = '{$_POST['user_lastname']}' AND firstname = '{$_POST['user_firstname']}') OR (login = '".ploopi\db::get()->addslashes($_POST['user_login'])."')");
                if(ploopi\db::get()->numrows()) ploopi\output::redirect("admin.php?op=manage_account&confirm");
            }
            else // on vérifie qd même le doublon de login
            {
                // test si login deja existant
                ploopi\db::get()->query("SELECT id FROM ploopi_user WHERE login = '".ploopi\db::get()->addslashes($_POST['user_login'])."'");
                // problème, ce login existe déjà => redirect
                if(ploopi\db::get()->numrows()) ploopi\output::redirect("admin.php?op=manage_account&confirm");
            }
        }

        // on efface la sauvegarde des données utilisateur si elles existent
        if (isset($_SESSION['system']['save_user'])) unset($_SESSION['system']['save_user']);

        if (!isset($_POST['user_ticketsbyemail'])) $user->fields['ticketsbyemail'] = 0;
        if (!isset($_POST['user_password_force_update'])) $user->fields['password_force_update'] = 0;
        if (!isset($_POST['user_disabled'])) $user->fields['disabled'] = 0;
        if (!isset($_POST['user_servertimezone'])) $user->fields['servertimezone'] = 0;
        if (!empty($_POST['user_date_expire'])) $_POST['user_date_expire'] = ploopi\date::local2timestamp($_POST['user_date_expire']);

        $user->setvalues($_POST,'user_');

        // affectation nouveau password
        $passwordok = true;
        if (isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
        {
            if ($_POST['usernewpass']!='' && $_POST['usernewpass'] == $_POST['usernewpass_confirm'])
            {
                $user->setpassword($_POST['usernewpass']);
                if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], $_POST['usernewpass']);
            }
            elseif ($_POST['usernewpass'] != $_POST['usernewpass_confirm']) $passwordok = false;
        }


        // Affectation nouveau mot de passe
        $error = '';

        if (($user->new || isset($_POST['useroldpass'])) && isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
        {
            if ($user->new || $_POST['usernewpass'] != '')
            {
                // Mots de passes équivalents
                if ($_POST['usernewpass'] == $_POST['usernewpass_confirm'])
                {
                    // Complexité ok
                    if (!_PLOOPI_USE_COMPLEXE_PASSWORD || ploopi\security::checkpasswordvalidity($_POST['usernewpass']))
                    {
                        // Affectation du mot de passe
                        $user->setpassword($_POST['usernewpass']);
                        // Mise à jour htpasswd
                        if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], $_POST['usernewpass']);
                    }
                    else $error = 'passrejected';
                }
                else $error = 'password';
            }
        }






        if ($user->new)
        {
            $user->save();
            ploopi\user_action_log::record(_SYSTEM_ACTION_CREATEUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']})");
        }
        else
        {
            $user->save();
            ploopi\user_action_log::record(_SYSTEM_ACTION_MODIFYUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']})");
        }

        if (!empty($_SESSION['system']['user_photopath']))
        {
            ploopi\fs::makedir(_PLOOPI_PATHDATA._PLOOPI_SEP.'system');

            // photo temporaire présente => copie dans le dossier définitif
            rename($_SESSION['system']['user_photopath'], $user->getphotopath());
            unset($_SESSION['system']['user_photopath']);
        }

        // Suppression photo
        if (ploopi\session::getvar("deletephoto_{$user->fields['id']}")) $user->deletephoto();

        $reload = ''; // reloadsession or not ?

        if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES && !empty($workspaceid))
        {
            // modify adminlevel for current workspace/user
            $workspace_user = new ploopi\workspace_user();
            if ($workspace_user->open($workspaceid, $user->fields['id'])) {
                if (!empty($_POST['userworkspace_adminlevel']) && $workspace_user->fields['adminlevel'] != $_POST['userworkspace_adminlevel']) $reload = '&reloadsession';

                $workspace_user->setvalues($_POST,'userworkspace_');
                $workspace_user->save();
            }
        }

        if ($_SESSION['system']['level'] == _SYSTEM_GROUPS && !empty($groupid))
        {
            $group_user = new ploopi\group_user();
            $group_user->open($groupid, $user->fields['id']);
            $group_user->save();
        }

        if ($passwordok)
        {
            $alphaTabItem = ord(strtolower($user->fields['lastname']))-96;
            if ($alphaTabItem < 1 || $alphaTabItem > 26) $alphaTabItem = 98; // #
            ploopi\output::redirect("admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&alphaTabItem={$alphaTabItem}{$reload}");
        }
        else ploopi\output::redirect("admin.php?op=modify_user&user_id={$user->fields['id']}&error=password");
    break;

}

$tabs['tabUserList'] = array(
    'title' => _SYSTEM_LABELTAB_USERLIST,
    'url' => "admin.php?usrTabItem=tabUserList"
);

if ($_SESSION['system']['level'] == _SYSTEM_GROUPS)
{
    $tabs['tabUserAdd'] = array(
        'title' => _SYSTEM_LABELTAB_USERADD,
        'url' => "admin.php?usrTabItem=tabUserAdd"
    );
}

if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER)
{
    $tabs['tabUserAttach'] = array(
        'title' => _SYSTEM_LABELTAB_USERATTACH,
        'url' => "admin.php?usrTabItem=tabUserAttach"
    );
}

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    $tabs['tabGroupList'] = array(
        'title' => _SYSTEM_LABELTAB_GROUPLIST,
        'url' => "admin.php?usrTabItem=tabGroupList"
    );

    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER)
    {
        $tabs['tabGroupAttach'] = array(
            'title' => _SYSTEM_LABELTAB_GROUPATTACH,
            'url' => "admin.php?usrTabItem=tabGroupAttach"
        );
    }
}

if ($_SESSION['system']['level'] == _SYSTEM_GROUPS)
{
    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN)
    {
        $tabs['tabUserImport'] = array(
            'title' => _SYSTEM_LABELTAB_USERIMPORT,
            'url' => "admin.php?usrTabItem=tabUserImport"
        );
    }
}

if (!empty($_GET['usrTabItem']))  $_SESSION['system']['usrTabItem'] = $_GET['usrTabItem'];
if (!isset($_SESSION['system']['usrTabItem'])) $_SESSION['system']['usrTabItem'] = '';

echo ploopi\skin::get()->create_tabs($tabs, $_SESSION['system']['usrTabItem']);
echo ploopi\skin::get()->open_simplebloc();

switch($_SESSION['system']['usrTabItem'])
{
    case 'tabGroupList':
        switch($op)
        {
            case 'modify_group':
                if (!empty($_GET['orgid']) && is_numeric($_GET['orgid']))
                {
                    $org = new ploopi\group();
                    $org->open($_GET['orgid']);
                    $workspace_group = new ploopi\workspace_group();
                    $workspace_group->open($workspaceid,$_GET['orgid']);
                    include './modules/system/admin_index_group_form.php';
                }
                else ploopi\output::redirect('admin.php');
            break;

            case 'save_group':
                if (!empty($_GET['orgid']) && is_numeric($_GET['orgid']))
                {
                    // modify adminlevel for current group/user
                    $workspace_group = new ploopi\workspace_group();
                    $workspace_group->open($workspaceid,$_GET['orgid']);
                    $workspace_group->setvalues($_POST,'workspacegroup_');
                    $workspace_group->save();
                    ploopi\output::redirect("admin.php?reloadsession");
                }
                else ploopi\output::redirect('admin.php');
            break;

            case 'detach_group':
                if (!empty($_GET['orgid']) && is_numeric($_GET['orgid']))
                {
                    $workspace_group = new ploopi\workspace_group();
                    $workspace_group->open($workspaceid,$_GET['orgid']);
                    $workspace_group->delete();

                    unset($_SESSION['system']['groups']);
                    unset($_SESSION['system']['workspaces']);

                    ploopi\output::redirect("admin.php?reloadsession");
                }
                else ploopi\output::redirect('admin.php');
            break;

            default:
                include './modules/system/admin_index_users_grouplist.php';
            break;
        }

    break;

    case 'tabGroupAttach':
        switch($op)
        {
            case 'attach_group':
                case _SYSTEM_GROUPS :
                    if (!empty($_GET['orgid']) && is_numeric($_GET['orgid']))
                    {
                        $org = new ploopi\group();
                        $org->open($_GET['orgid']);
                        $org->attachtogroup($workspaceid);

                        unset($_SESSION['system']['groups']);
                        unset($_SESSION['system']['workspaces']);

                        ploopi\user_action_log::record(_SYSTEM_ACTION_ATTACHGROUP, "{$org->fields['label']} (id:{$org->fields['id']}) => {$workspace->fields['label']} (id:$workspaceid)");
                        ploopi\output::redirect("admin.php?reloadsession");
                    }
                    else ploopi\output::redirect('admin.php');
            break;

            default:
                include './modules/system/admin_index_users_attachgroup.php';
            break;
        }
    break;

    case 'tabUserAttach':
        switch($op)
        {
            case 'attach_user':
                if (isset($_GET['userid']) && is_numeric($_GET['userid']))
                {
                    $alphaTabItem = isset($_GET['alphaTabItem']) ? $_GET['alphaTabItem'] : '';
                    switch ($_SESSION['system']['level'])
                    {
                        case _SYSTEM_GROUPS :
                            $user = new ploopi\user();
                            $user->open($_GET['userid']);
                            $user->attachtogroup($groupid);
                            ploopi\user_action_log::record(_SYSTEM_ACTION_ATTACHUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']}) => {$group->fields['label']} (id:$groupid)");
                            ploopi\output::redirect("admin.php?reloadsession&alphaTabItem={$alphaTabItem}");
                        break;

                        case _SYSTEM_WORKSPACES :
                            $user = new ploopi\user();
                            $user->open($_GET['userid']);
                            $user->attachtoworkspace($workspaceid);
                            ploopi\user_action_log::record(_SYSTEM_ACTION_ATTACHUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']}) => {$workspace->fields['label']} (id:$workspaceid)");
                            ploopi\output::redirect("admin.php?reloadsession&alphaTabItem={$alphaTabItem}");
                        break;
                    }
                }
                else ploopi\output::redirect('admin.php');
            break;

            default:
                include './modules/system/admin_index_users_attachlist.php';
            break;
        }
    break;

    case 'tabUserList':

        $user = new ploopi\user();

        switch($op)
        {
            case 'modify_user':
                if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
                {
                    $user->open($_GET['user_id']);
                    $group_user = new ploopi\group_user();
                    $group_user->open($groupid,$_GET['user_id']);
                    include './modules/system/admin_index_users_form.php';
                }
                else ploopi\output::redirect('admin.php');
            break;

            case 'delete_user':
                if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
                {
                    global $admin_redirect;
                    $admin_redirect = true;

                    if ($user->open($_GET['user_id']))
                    {
                        if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], '', true);

                        ploopi\user_action_log::record(_SYSTEM_ACTION_DELETEUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']})");

                        ?>
                        <div style="padding:4px;">
                            <div style="font-weight:bold;">
                                <?php echo str_replace('<LABEL>',$user->fields['login'],_SYSTEM_LABEL_USERDELETE); ?>
                            </div>
                            <?php

                            $user->delete();
                            if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");

                            ?>
                            <div style="text-align:right;">
                                <input type="button" class="button" value="<?php echo _PLOOPI_CONTINUE; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?reloadsession"); ?>'">
                            </div>
                        </div>
                        <?php
                    }
                    else ploopi\output::redirect('admin.php');
                }
                else ploopi\output::redirect('admin.php');
            break;

            case 'attach_user':
                // on efface la sauvegarde des données utilisateur si elles existent
                if (isset($_SESSION['system']['save_user'])) unset($_SESSION['system']['save_user']);

                $user = new ploopi\user();
                if (isset($_GET['user_id']) && is_numeric($_GET['user_id']) && $user->open($_GET['user_id']))
                {
                    $user->attachtogroup($groupid);

                    $alphaTabItem = ord(strtolower($user->fields['lastname']))-96;
                    if ($alphaTabItem < 1 || $alphaTabItem > 26) $alphaTabItem = 98; // #

                    ploopi\output::redirect("admin.php?reloadsession&usrTabItem=tabUserList&alphaTabItem={$alphaTabItem}");
                }
                else ploopi\output::redirect('admin.php');
            break;

            case 'detach_user':
                if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
                {
                    switch ($_SESSION['system']['level'])
                    {
                        case _SYSTEM_GROUPS :
                            global $admin_redirect;
                            $admin_redirect = true;

                            $user = new ploopi\user();
                            $user->open($_GET['user_id']);

                            $group = new ploopi\group();
                            $group->open($groupid);

                            ploopi\user_action_log::record(_SYSTEM_ACTION_DETACHUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']}) => {$group->fields['label']} (id:$groupid)");

                            ?>
                            <div style="padding:4px;">
                                <div style="font-weight:bold;">
                                    <?php echo str_replace('<LABELGROUP>',$group->fields['label'],str_replace('<LABELUSER>',$user->fields['login'],_SYSTEM_LABEL_USERDETACH)); ?>
                                </div>
                                <?php
                                $group_user = new ploopi\group_user();
                                $group_user->open($groupid,$_GET['user_id']);
                                $group_user->delete();

                                if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");
                                ?>
                                <div style="text-align:right;">
                                    <input type="button" class="button" value="<?php echo _PLOOPI_CONTINUE; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?reloadsession"); ?>'">
                                </div>
                            </div>
                            <?php
                        break;

                        case _SYSTEM_WORKSPACES :
                            global $admin_redirect;
                            $admin_redirect = true;

                            $user = new ploopi\user();
                            $user->open($_GET['user_id']);

                            $workspace = new ploopi\workspace();
                            $workspace->open($workspaceid);

                            ploopi\user_action_log::record(_SYSTEM_ACTION_DETACHUSER, "{$user->fields['login']} - {$user->fields['lastname']} {$user->fields['firstname']} (id:{$user->fields['id']}) => {$workspace->fields['label']} (id:$workspaceid)");

                            ?>
                            <div style="padding:4px;">
                                <div style="font-weight:bold;">
                                    <?php echo str_replace('<LABELGROUP>',$workspace->fields['label'],str_replace('<LABELUSER>',$user->fields['login'],_SYSTEM_LABEL_USERDETACH)); ?>
                                </div>
                                <?php

                                $workspace_user = new ploopi\workspace_user();
                                $workspace_user->open($workspaceid,$_GET['user_id']);
                                $workspace_user->delete();

                                if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");
                                ?>
                                <div style="text-align:right;">
                                    <input type="button" class="button" value="<?php echo _PLOOPI_CONTINUE; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?reloadsession"); ?>'">
                                </div>
                            </div>
                            <?php
                        break;
                    }
                }
            break;

            default:
                include './modules/system/admin_index_users_list.php';
            break;

        }
    break;

    case 'tabUserAdd':
        $user = new ploopi\user();

        switch($op)
        {
            case 'modify_user':
                if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) include './modules/system/admin_index_users_form.php';
                else ploopi\output::redirect('admin.php');
            break;

            default:
                $user->init_description();
                $user->fields['id']=-1;
                include './modules/system/admin_index_users_form.php';
            break;
        }
    break;

    case 'tabUserImport':
        switch($op)
        {
            case 'end':
                ?>
                <div style="padding:2px;border-bottom:1px solid #a0a0a0;background-color:#e0e0e0;"><strong>Import d'un fichier CSV contenant des utilisateurs :</strong></div>
                <div style="padding:4px;">Traitement terminé</div>
                <?php
                if (!empty($_SESSION['system']['user_import_errors']))
                {
                    ?>
                    <div style="padding:4px;">
                        <?php
                        foreach($_SESSION['system']['user_import_errors'] as $strMsg)
                        {
                            ?>
                            <p class="ploopi_va">
                                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" style="margin-right:4px;" />
                                <span><?php echo ploopi\str::htmlentities($strMsg); ?></span>
                            </p>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
                <div style="padding:4px;"><input type="button" class="button" value="Retour" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?usrTabItem=tabUserImport"); ?>';" /></div>
                <?php
            break;

            case 'import':
                $_SESSION['system']['user_import_errors'] = array();

                if (!empty($_SESSION['system']['user_import']))
                {
                    for ($intI = 1; $intI < count($_SESSION['system']['user_import']); $intI++)
                    {
                        $objUser = new ploopi\user();
                        $objUser->init_description();

                        $intJ = 0;
                        foreach($_SESSION['system']['user_import'][0] as $strFieldName)
                        {
                            if (isset($_SESSION['system']['user_import'][$intI][$intJ]))
                            {
                                if ($strFieldName != 'id' && isset($objUser->fields[$strFieldName])) // le champ existe
                                {
                                    $objUser->fields[$strFieldName] = $_SESSION['system']['user_import'][$intI][$intJ];
                                }
                            }
                            $intJ++;
                        }

                        // On vérifie que le login n'existe pas déjà
                        ploopi\db::get()->query("
                            SELECT  login
                            FROM    ploopi_user
                            WHERE   login = '".ploopi\db::get()->addslashes($objUser->fields['login'])."'
                        ");

                        if (!ploopi\db::get()->numrows()) // ok pas de login identique dans la BDD
                        {
                            $objUser->setpassword($objUser->fields['password']);

                            // On ajoute l'utilisateur
                            $objUser->save();

                            // On le rattache au groupe sélectionné
                            $objUser->attachtogroup($groupid);
                        }
                        else
                        {
                            $_SESSION['system']['user_import_errors'][] = "Un utilisateur existe déjà avec le login '{$objUser->fields['login']}'";
                        }
                    }
                }

                ploopi\output::redirect("admin.php?usrTabItem=tabUserImport&op=end");
            break;

            case 'preview':
                include_once './modules/system/admin_index_users_preview.php';
            break;

            default:
                include_once './modules/system/admin_index_users_import_form.php';
            break;
        }
    break;

}

echo ploopi\skin::get()->close_simplebloc();
?>
