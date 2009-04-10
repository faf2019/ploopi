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
 * Opération du module 'Système'
 *
 * @package system
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Opérations accessibles pour les utilisateurs connectés dans le module système
 */
if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM)
{
    switch($ploopi_op)
    {
        /**
         * Opérations sur les tickets
         */
        case 'tickets_delete':
            include_once './include/classes/ticket.php';

            $arrTickets = array();

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $arrTickets[] = $_GET['ticket_id'];
            }
            elseif (isset($_POST['tickets_delete_id']) && is_array($_POST['tickets_delete_id']))
            {
                $arrTickets = $_POST['tickets_delete_id'];
            }

            foreach($arrTickets as $ticket_id)
            {
                $ticket = new ticket();
                if (is_numeric($ticket_id) && $ticket->open($ticket_id))
                {
                    if ($_SESSION['ploopi']['userid'] == $ticket->fields['id_user']) // utilisateur = emetteur
                    {
                        $ticket->fields['deleted'] = 1;
                        $ticket->save();
                    }

                    $ticket_dest = new ticket_dest();
                    if ($ticket_dest->open($_SESSION['ploopi']['userid'], $ticket_id))
                    {
                        $ticket_dest->fields['deleted'] = 1;
                        $ticket_dest->save();
                    }
                }
            }

            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;

        case 'tickets_open':
            include_once './include/classes/ticket.php';
            $ticket = new ticket();

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id']))
            {
                $ticket_status = new ticket_status();

                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_OPENED))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_OPENED;
                    $ticket_status->save();
                }

                $ticket_watch = new ticket_watch();
                $ticket_watch->open($_GET['ticket_id'], $_SESSION['ploopi']['userid']);
                $ticket_watch->fields['id_ticket'] = $_GET['ticket_id'];
                $ticket_watch->fields['id_user'] = $_SESSION['ploopi']['userid'];
                $ticket_watch->fields['notify'] = 0;
                $ticket_watch->save();

                $ticket->fields['count_read']++;
                $ticket->save();
            }
            ploopi_die();
        break;

        case 'tickets_open_responses':
            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $rootid = $db->addslashes($_GET['ticket_id']);

                $sql =  "
                        SELECT      t.id,
                                    t.title,
                                    t.message,
                                    t.timestp,
                                    t.lastedit_timestp,
                                    t.id_module,
                                    t.parent_id,
                                    t.root_id,
                                    t.id_user as sender_uid,
                                    ts.status,
                                    u.login,
                                    u.firstname,
                                    u.lastname

                        FROM        ploopi_ticket t

                        INNER JOIN  ploopi_user u
                        ON          t.id_user = u.id

                        LEFT JOIN   ploopi_ticket_status ts
                        ON          ts.id_ticket = t.id
                        AND         ts.id_user = {$_SESSION['ploopi']['userid']}

                        WHERE       t.root_id = {$rootid}
                        AND         t.id <> {$rootid}

                        ORDER BY    t.timestp DESC
                        ";

                $tickets = array();
                $parents = array();

                $rs = $db->query($sql);

                while ($fields = $db->fetchrow($rs))
                {
                    if (!isset($tickets[$fields['id']]))
                    {
                        $tickets[$fields['id']] = $fields;
                        $parents[$fields['parent_id']][] = $fields['id'];
                    }

                }

                if (!empty($tickets)) system_tickets_displayresponses($parents, $tickets, $_GET['ticket_id']);
            }
            ploopi_die();
        break;

        case 'tickets_validate':
            include_once './include/classes/ticket.php';

            $ticket_status = new ticket_status();

            if (!empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_DONE))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_DONE;
                    $ticket_status->save();
                }
            }
            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;

        /**
         * Moteur de recherche
         */

        case 'system_search':
            include_once('./modules/system/public_search_result.php');
            ploopi_die();
        break;

        default:
            /**
             * Autres opérations nécessitant un niveau d'accrédiation plus élevé (gestionnaire ou admin sys)
             */

            if (ploopi_isadmin())
            {
                switch($ploopi_op)
                {
                    // update description
                    case 'updatedesc':
                        include_once './include/classes/module.php';
                        ploopi_init_module('system', false, false, false);

                        $module_type = new module_type();
                        if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                        {
                            $xmlfile_desc = "./install/{$module_type->fields['label']}/description.xml";
                            $critical_error = $module_type->update_description($xmlfile_desc);
                            if (!$critical_error) ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMODULE, "{$module_type->fields['label']} (reload)");
                        }

                        ploopi_redirect('admin.php');
                    break;

                    // update metabase
                    case 'updatemb':
                        include_once './include/classes/module.php';
                        ploopi_init_module('system', false, false, false);

                        $module_type = new module_type();
                        if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                        {
                            global $idmoduletype;
                            $idmoduletype = $_GET['idmoduletype'];

                            include './modules/system/xmlparser_mb.php';

                            ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMETABASE, $module_type->fields['label']);

                            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$_GET['idmoduletype']}");
                            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$_GET['idmoduletype']}");

                            $mbfile = "./install/{$module_type->fields['label']}/mb.xml";

                            if (file_exists($mbfile))
                            {
                                $xml_parser = xmlparser_mb();
                                xml_parse($xml_parser,  file_get_contents($mbfile));
                                xml_parser_free($xml_parser);
                            }
                        }

                        ploopi_redirect('admin.php');
                    break;
                }
            }

            if (ploopi_ismanager())
            {
                switch($ploopi_op)
                {
                    case 'system_roleusers':
                        if (empty($_GET['system_roleusers_roleid'])) ploopi_die();
                        $roleid = $_GET['system_roleusers_roleid'];
                        include './modules/system/admin_index_roles_assignment_list.php';
                        ploopi_die();
                    break;

                    // suppression de l'affectation d'un rôle à un utilisateur
                    case 'system_roleusers_delete_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wur = new workspace_user_role();

                        if ($wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wur->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // suppression de l'affectation d'un rôle à un groupe
                    case 'system_roleusers_delete_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wgr = new workspace_group_role();

                        if ($wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wgr->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un utilisateur
                    case 'system_roleusers_select_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wur = new workspace_user_role();

                        if (!$wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wur->fields['id_user'] = $_GET['system_roleusers_userid'];
                            $wur->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wur->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wur->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un groupe
                    case 'system_roleusers_select_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './include/classes/workspace.php';

                        $wgr = new workspace_group_role();

                        if (!$wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wgr->fields['id_group'] = $_GET['system_roleusers_groupid'];
                            $wgr->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wgr->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wgr->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // résultat de la recherche utilisateurs / groupes
                    case 'system_roleusers_search':
                        if (!isset($_GET['system_roleusers_filter'])) ploopi_die();

                        $cleanedfilter = $db->addslashes($_GET['system_roleusers_filter']);
                        $userfilter = "(u.login LIKE '%{$cleanedfilter}%' OR u.firstname LIKE '%{$cleanedfilter}%' OR u.lastname LIKE '%{$cleanedfilter}%')";

                        $sql =  "
                                SELECT      u.id,
                                            u.lastname,
                                            u.firstname,
                                            u.login,
                                            u.service

                                FROM        ploopi_user u

                                INNER JOIN  ploopi_workspace_user wu
                                ON          wu.id_user = u.id
                                AND         wu.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$userfilter}

                                ORDER BY    u.lastname, u.firstname
                                ";

                        $db->query($sql);
                        $users = $db->getarray();

                        $groupfilter = "g.label LIKE '%{$cleanedfilter}%'";

                        $sql =  "
                                SELECT      g.id,
                                            g.label,
                                            g.parents

                                FROM        ploopi_group g

                                INNER JOIN  ploopi_workspace_group wg
                                ON          wg.id_group = g.id
                                AND         wg.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$groupfilter}

                                ORDER BY    g.label
                                ";

                        $db->query($sql);
                        $groups = $db->getarray();

                        if (empty($users) && empty($groups))
                        {
                            ?>
                            <p class="ploopi_va" style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">
                                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png">
                                <span>aucun utilisateur/groupe trouv&eacute;</span>
                            </p>
                            <?php
                        }
                        else
                        {
                            ?>
                            <div id="system_roleusers_result">
                                <?php
                                // pour chaque groupe
                                foreach($groups as $group)
                                {
                                    ?>
                                    <a class="system_roleusers_select" title="Sélectionner ce groupe et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<?php echo $_GET['system_roleusers_roleid']; ?>, <?php echo $group['id']; ?>, 'group');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><?php echo "{$group['label']}"; ?></span></p>
                                    </a>
                                    <?php
                                }
                                ?>
                                <?php
                                // pour chaque utilisateur
                                foreach($users as $user)
                                {
                                    ?>
                                    <a class="system_roleusers_select" title="Sélectionner cet utilisateur et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<?php echo $_GET['system_roleusers_roleid']; ?>, <?php echo $user['id']; ?>, 'user');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo "{$user['lastname']} {$user['firstname']} ({$user['login']})"; ?></span></p>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            <div id="system_roleusers_legend">
                                <p class="ploopi_va" style="float:right;">
                                    <span style="font-weight:bold;">Légende:&nbsp;&nbsp;&nbsp;</span>
                                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>&nbsp;Groupe d'Utilisateur&nbsp;&nbsp;</span>
                                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>&nbsp;Utilisateur</span>
                                </p>
                                <p class="ploopi_va" style="float:left;">
                                    <span>Cliquez sur un utilisateur ou un groupe pour l'ajouter</span>
                                </p>
                            </div>
                            <?php
                        }

                        ploopi_die();
                    break;

                    case 'system_serverload':
                        include './modules/system/tools_serverload.php';
                        ploopi_die();
                    break;

                    case 'system_tools_phpinfo':
                        phpinfo();
                        ?>
                        <script type="text/javascript">
                        function system_autofit_iframe()
                        {
                            try
                            {
                                if (document.getElementById || !window.opera && !document.mimeType && document.all && document.getElementById)
                                {
                                    height = this.document.body.scrollHeight + 50;
                                    if (height < 400) height = 400;
                                    parent.document.getElementById('system_tools_phpinfo').style.height = height + 'px';
                                }
                            }
                            catch (e)
                            {
                                height = this.document.body.offsetHeight;
                                if (height < 400) height = 400;
                                parent.document.getElementById('system_tools_phpinfo').style.height = height + 'px';
                            }
                        }

                        window.onload = function() { system_autofit_iframe();};
                        </script>
                        <?php
                        ploopi_die();
                    break;

                    case 'system_choose_photo':
                        // Popup de choix d'une photo pour un utilisateur
                        ob_start();
                        ploopi_init_module('system');
                        ?>
                        <form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=system_send_photo"); ?>" method="post" enctype="multipart/form-data" target="system_user_photo_iframe">
                        <p class="ploopi_va" style="padding:2px;">
                            <label><?php echo _SYSTEM_LABEL_PHOTO; ?>: </label>
                            <input type="file" class="text" name="system_user_photo" />
                            <input type="submit" class="button" name="<?php echo _PLOOPI_SAVE; ?>" />
                        </p>
                        </form>
                        <iframe name="system_user_photo_iframe" style="display:none;"></iframe>
                        <?php
                        $content = ob_get_contents();
                        ob_end_clean();

                        echo $skin->create_popup("Chargement d'une nouvelle photo", $content, 'popup_system_choose_photo');
                        ploopi_die();
                    break;

                    case 'system_send_photo':
                        // Envoi d'une photo temporaire dans la fiche utilisateur
                        // On vérifie qu'un fichier a bien été uploadé
                        if (!empty($_FILES['system_user_photo']['tmp_name']))
                        {
                            $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
                            ploopi_makedir($strTmpPath);
                            $_SESSION['system']['user_photopath'] = tempnam($strTmpPath, '');
                            ploopi_resizeimage($_FILES['system_user_photo']['tmp_name'], 0, 100, 150, 'png', 0, $_SESSION['system']['user_photopath']);
                        }
                        ?>
                        <script type="text/javascript">
                            new function() {
                                window.parent.ploopi_getelem('system_user_photo', window.parent.document).innerHTML = '<img src="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=system_get_photo'); ?>" />';
                                window.parent.ploopi_hidepopup('popup_system_choose_photo');
                            }
                        </script>
                        <?php
                    break;

                    case 'system_get_photo':
                        // Envoi de la photo temporaire vers le client
                        if (!empty($_SESSION['system']['user_photopath'])) ploopi_downloadfile($_SESSION['system']['user_photopath'], 'user.png', false, false);
                        ploopi_die();
                    break;

                    case 'system_delete_user':
                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                        {
                            ploopi_init_module('system');
                            $objUser = new user();
                            if (!empty($_GET['system_user_id']) && is_numeric($_GET['system_user_id']) && $objUser->open($_GET['system_user_id']))
                            {
                                if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($objUser->fields['login'], '', true);
                                ploopi_create_user_action_log(_SYSTEM_ACTION_DELETEUSER, "{$objUser->fields['login']} - {$objUser->fields['lastname']} {$objUser->fields['firstname']} (id:{$objUser->fields['id']})");
                                $objUser->delete();
                            }
                        }
                        ploopi_redirect('admin.php?system_level=system&sysToolbarItem=directory');
                    break;
                    
                    case 'system_user_import':

                        $_SESSION['system']['user_import'] = array();
                        
                        if (!empty($_FILES['system_user_file']) && !empty($_FILES['system_user_file']['name']))
                        {
                            // Récupération & contrôle du séparateur de champs
                            $strSep = empty($_POST['system_user_sep']) ? ',' : $_POST['system_user_sep'];
                            if (!in_array($strSep, array(',', ';'))) $strSep = ',';
                             
                            // Lecture du fichier si ok
                            if (file_exists($_FILES['system_user_file']['tmp_name']))
                            {
                                $ptrHandle = fopen($_FILES['system_user_file']['tmp_name'], 'r');
                                
                                while (($arrLineData = fgetcsv($ptrHandle, null, $strSep)) !== FALSE) 
                                {
                                    if (is_array($arrLineData))
                                    {
                                        $_SESSION['system']['user_import'][] = $arrLineData;
                                    }
                                }
                            }
                        }
                        
                        ploopi_redirect("admin.php?usrTabItem=tabUserImport&op=preview");
                    break;
                }
            }
        break;
    }
}
?>
