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

ploopi_init_module('system');

switch($_SESSION['ploopi']['mainmenu'])
{
	case _PLOOPI_MENU_SEARCH:
		include_once 'public_search.php';
	break;

	case _PLOOPI_MENU_TICKETS:
		include_once 'public_tickets.php';
	break;

	case _PLOOPI_MENU_ANNOTATIONS:
		include_once 'public_annotations.php';
	break;

	case _PLOOPI_MENU_PROFILE:
		include_once('./include/classes/class_param.php');


		$param_module = new param($db->connection_id);


		$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

		echo $skin->create_pagetitle(_SYSTEM_LABEL_MYPROFILE);

		switch($op)
		{
			case 'paramsave':
				if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']))
				{
					$param_module->open($_POST['idmodule'],0,$_SESSION['ploopi']['userid'], 1);
					$param_module->setvalues($_POST);
					$param_module->save();

					// reload all module params of current user in session
					include('./include/load_param.php');
					ploopi_redirect("{$scriptenv}?op=param&idmodule={$_POST['idmodule']}");
				}
				else ploopi_redirect($scriptenv);
			break;

			case 'param':
				include('./modules/system/public_module_param.php');
			break;

			case 'actions':
				include('./modules/system/public_actions.php');
			break;

			case 'save_user':
				$user = new user();
				$user->open($_SESSION['ploopi']['userid']);
				$user->setvalues($_POST,'user_');

				// affectation nouveau password
				$passwordok = true;
				if (isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
				{
					if ($_POST['usernewpass']!='' && $_POST['usernewpass'] == $_POST['usernewpass_confirm'])
					{
						$user->fields['password'] = md5(_PLOOPI_SECRETKEY."/{$user->fields['login']}/".md5($_POST['usernewpass']));
						if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], $_POST['usernewpass']);
					}
					elseif ($_POST['usernewpass'] != $_POST['usernewpass_confirm']) $passwordok = false;
				}

				$user->save();
				if ($passwordok) ploopi_redirect("{$scriptenv}?op=user&reloadsession");
				else ploopi_redirect("{$scriptenv}?op=user&error=password");
			break;

			default:
				$user = new user();
				$user->open($_SESSION['ploopi']['userid']);
				include('./modules/system/public_user.php');
			break;

		}
	break;

	case _PLOOPI_MENU_ABOUT:
		switch($op)
		{
			default:
				echo $skin->open_simplebloc("PLOOPI "._PLOOPI_VERSION,'100%');
				?>
				<TABLE CELLPADDING="2" CELLSPACING="1">
				<TR>
					<TD>
					<? echo _SYSTEM_EXPLAIN_ABOUT; ?>
					</TD>
				</TR>
				<?
				if (file_exists('./whatsnew.txt'))
				{
					?>
					<TR>
						<TD>
						<br>
						<b>Changelog : </b>
						<?
						$handle = fopen('./whatsnew.txt','r');
						$contents = '';
						while (!feof($handle))
						{
							$contents .= fread($handle, 8192);
						}
						echo nl2br($contents);
						?>
						</TD>
					</TR>
					<?
				}
				?>
				</TABLE>
				<?
				echo $skin->close_simplebloc();
			break;


		}

	break;


}
?>
