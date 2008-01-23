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
ploopi_init_module('system');
include_once('./modules/system/class_user.php');

if (!isset($op)) $op = '';

switch($op)
{
	case 'showtickets':
		$template_body->assign_block_vars("switch_content_module_system.switch_tickets", array());

		$template_body->assign_block_vars('switch_content_module_system.switch_tickets.menu' , array(
								'LABEL' => 'Messages reçus',
								'URL' => ploopi_urlencode("index.php?modcontent={$template_moduleid}&op=showtickets&menu=received"),
								'SELECTED' => (!isset($_GET['menu']) || (isset($_GET['menu']) && $_GET['menu'] == 'received')) ? 'sel' : 'notsel'
								)
							);

		$template_body->assign_block_vars('switch_content_module_system.switch_tickets.menu' , array(
								'LABEL' => 'Messages envoyés',
								'URL' => ploopi_urlencode("index.php?modcontent={$template_moduleid}&op=showtickets&menu=sent"),
								'SELECTED' => (isset($_GET['menu']) && $_GET['menu'] == 'sent') ? 'sel' : 'notsel'
								)
							);


		if (!isset($_GET['menu']) || (isset($_GET['menu']) && $_GET['menu'] == 'received'))
		{
			$template_body->assign_vars(array('TICKETS_TITLE' => 'Messages reçus'));

			$sql = 	"
					SELECT 		t.id,
								t.title,
								t.message,
								t.timestp,
								t.object_label,
								t.id_object,
								t.id_record,
								td.*,
								u.login,
								u.firstname,
								u.lastname,
								m.label as module_name,
								o.label as object_name,
								o.script
					FROM 		ploopi_ticket t,
								ploopi_ticket_dest td
					LEFT JOIN	ploopi_user u ON t.id_user = u.id
					LEFT JOIN	ploopi_module m ON t.id_module = m.id
					LEFT JOIN	ploopi_mb_object o ON t.id_object = o.id AND m.id_module_type = o.id_module_type
					WHERE 		td.id_user = {$_SESSION['ploopi']['userid']}
					AND			td.id_ticket = t.id
					AND			td.deleted = 0
					AND			t.id = t.root_id
					ORDER BY 	t.timestp DESC
					";
			$rs = $db->query($sql);

			while ($fields = $db->fetchrow($rs))
			{
				$ld = ploopi_timestamp2local($fields['timestp']);
				if (!$fields['opened']) $puce = '#ff2020';
				elseif (!$fields['done']) $puce = '#2020ff';
				else $puce = '#20ff20';

				$template_body->assign_block_vars('switch_content_module_system.switch_tickets.tickets' , array(
									'ID' => $fields['id'],
									'TITLE' => $fields['title'],
									'MESSAGE' => ploopi_nl2br($fields['message']),
									'DATE' => $ld['date'],
									'TIME' => $ld['time'],
									'COLOR' => $puce,
									'USER_LOGIN' => $fields['login'],
									'USER_FIRSTNAME' => $fields['firstname'],
									'USER_LASTNAME' => $fields['lastname'],
									'MODULE_LABEL' => $fields['module_label'],
									'OBJECT_LABEL' => $fields['object_label'],
									'OBJECT_SCRIPT' => $fields['script']
									)
								);
			}
		}

		if (isset($_GET['menu']) && $_GET['menu'] == 'sent')
		{
			$template_body->assign_vars(array('TICKETS_TITLE' => 'Messages envoyés'));

			$sql = 	"
					SELECT 		t.id,
								t.title,
								t.message,
								t.timestp,
								t.object_label,
								t.id_object,
								t.id_record,
								td.*,
								u.login,
								u.firstname,
								u.lastname,
								u2.login as sender_login,
								u2.firstname as sender_firstname,
								u2.lastname as sender_lastname,
								m.label as module_name,
								o.label as object_name,
								o.script
					FROM 		ploopi_ticket t,
								ploopi_ticket_dest td

					LEFT JOIN	ploopi_user u ON td.id_user = u.id
					LEFT JOIN	ploopi_module m ON t.id_module = m.id
					LEFT JOIN	ploopi_mb_object o ON t.id_object = o.id AND m.id_module_type = o.id_module_type
					LEFT JOIN	ploopi_user u2 ON t.id_user = u2.id
					WHERE 		t.id_user = {$_SESSION['ploopi']['userid']}
					AND			t.deleted = 0
					AND			t.id = t.root_id
					AND			t.id = td.id_ticket

					ORDER BY 	t.timestp DESC
					";
			$rs = $db->query($sql);

			$tickets = array();

			while ($fields = $db->fetchrow($rs))
			{
				$tickets[$fields['id']]['fields'] = $fields;
				$tickets[$fields['id']]['dest'][$fields['id_user']]['id'] = $fields['id_user'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['firstname'] = $fields['firstname'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['lastname'] = $fields['lastname'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['login'] = $fields['login'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['opened'] = $fields['opened'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['done'] = $fields['done'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['deleted'] = $fields['deleted'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['opened_timestp'] = $fields['opened_timestp'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['done_timestp'] = $fields['done_timestp'];
				$tickets[$fields['id']]['dest'][$fields['id_user']]['deleted_timestp'] = $fields['deleted_timestp'];
			}

			foreach($tickets as $id => $ticket)
			{
				$fields = $ticket['fields'];
				$ld = ploopi_timestamp2local($fields['timestp']);

				$ticket_status = 2; // 0 = nothing done // 1 = opened // 2 = done

				foreach ($ticket['dest'] as $dest)
				{
					if ($dest['opened'] && !$dest['done'] && $ticket_status == 2) $ticket_status = 1;
					elseif (!$dest['opened'] && !$dest['done']) $ticket_status = 0;

					switch($ticket_status)
					{
						case '1':
							$puce = '#2020ff';
						break;

						case '2':
							$puce = '#20ff20';
						break;

						default:
							$puce = '#ff2020';
						break;
					}
				}

				$template_body->assign_block_vars('switch_content_module_system.switch_tickets.tickets' , array(
									'ID' => $fields['id'],
									'TITLE' => $fields['title'],
									'MESSAGE' => ploopi_nl2br($fields['message']),
									'DATE' => $ld['date'],
									'TIME' => $ld['time'],
									'COLOR' => $puce,
									'USER_LOGIN' => $fields['sender_login'],
									'USER_FIRSTNAME' => $fields['sender_firstname'],
									'USER_LASTNAME' => $fields['sender_lastname'],
									'MODULE_LABEL' => $fields['module_label'],
									'OBJECT_LABEL' => $fields['object_label'],
									'OBJECT_SCRIPT' => $fields['script']
									)
								);

				$template_body->assign_block_vars('switch_content_module_system.switch_tickets.tickets.switch_dest' , array());

				foreach ($ticket['dest'] as $dest)
				{
					if (!$dest['opened']) $puce = '#ff2020';
					elseif (!$dest['done']) $puce = '#2020ff';
					else $puce = '#20ff20';


					$template_body->assign_block_vars('switch_content_module_system.switch_tickets.tickets.switch_dest.dest' , array(
										'COLOR' => $puce,
										'NAME' => "{$dest['firstname']} {$dest['lastname']} ({$dest['login']})",
										)
									);
				}


			}
		}

	break;


	case 'saveprofile':
	case 'showprofile':
		$template_body->assign_block_vars("switch_content_module_system.switch_profile", array());
		$user = new user();
		$user->open($_SESSION['ploopi']['userid']);

		if ($op == 'saveprofile')
		{
			$user->setvalues($_POST,'user_');
			$passwordok = true;
			if ($userx_password!='' && $userx_password == $userx_passwordconfirm) $user->fields['password'] = md5($userx_password);
			elseif ($userx_password != $userx_passwordconfirm) $passwordok = false;
			$user->save();
			ploopi_redirect('./?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=showprofile&reloadsession&ok='.$passwordok);
		}

		if (isset($ok))
		{
			if (!$ok)  $template_body->assign_block_vars("switch_content_module_system.switch_profile.switch_userprofile_passworderror", array());
			$template_body->assign_block_vars("switch_content_module_system.switch_profile.switch_userprofile_saved", array());
		}

		$user->totemplate($template_body, 'USERPROFILE_');
		$template_body->assign_var('USERPROFILE_VALIDATE' , './?modcontent='._PLOOPI_MODULE_SYSTEM.'&op=saveprofile');
	break;

}


?>

