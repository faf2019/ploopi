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
ploopi_init_module('forms');
include_once './modules/forms/class_form.php';
include_once './modules/forms/class_field.php';
include_once './modules/forms/class_reply.php';
include_once './modules/forms/class_reply_field.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);

switch($op)
{
	case 'forms_save':
		$forms = new forms();
		if (isset($_POST['forms_id']) && $_POST['forms_id'] != '')
		{
			$forms->open($_POST['forms_id']);
			$forms->fields['autobackup'] = $_POST['forms_autobackup'];
			$forms->fields['autobackup_date'] = ploopi_local2timestamp($_POST['forms_autobackup_date']);
			$forms->save();
			ploopi_redirect("{$scriptenv}?op=forms_viewreplies&forms_id={$_POST['forms_id']}");
		}
		ploopi_redirect($scriptenv);
	break;

	case 'forms_download_file':
		if (!empty($_GET['reply_id']) && !empty($_GET['field_id']) && is_numeric($_GET['reply_id']) && is_numeric($_GET['field_id']))
		{
			$reply_field = new reply_field();
			$reply_field->open($_GET['reply_id'], $_GET['field_id']);

			$path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$_SESSION['ploopi']['moduleid']._PLOOPI_SEP.$reply_field->fields['id_form']._PLOOPI_SEP.$_GET['reply_id']._PLOOPI_SEP;

			if (!ploopi_downloadfile("{$path}{$reply_field->fields['value']}", $reply_field->fields['value']))
			{
				if (!empty($_GET['forms_id']) && !empty($_GET['forms_id'])) ploopi_redirect("{$scriptenv}?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
				else ploopi_redirect($scriptenv);
			}
		}
		else
		{
			if (!empty($_GET['forms_id']) && !empty($_GET['forms_id'])) ploopi_redirect("{$scriptenv}?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
			else ploopi_redirect($scriptenv);
		}
	break;


	case 'forms_export':
		if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT))
		{
			include_once './modules/forms/public_forms_preparedata.php';

			$id_module = $_SESSION['ploopi']['moduleid'];
			include('./modules/forms/public_forms_export.php');
		}
	break;

	case 'forms_reply_delete':

		if (ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY))
		{
			$reply = new reply();
			if (!empty($_GET['reply_id']) && is_numeric($_GET['reply_id']) && $reply->open($_GET['reply_id']))
			{
				$reply->delete();
				ploopi_redirect("{$scriptenv}?op=forms_viewreplies&forms_id={$reply->fields['id_form']}");
			}
			else ploopi_redirect($scriptenv);
		}
		else ploopi_redirect($scriptenv);
	break;

	case 'forms_reply_display':
	case 'forms_reply_add':
	case 'forms_reply_modify':
		if (ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY) || $op == 'forms_reply_display')
		{
			$forms = new forms();

			if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id']))
			{
				if (ploopi_set_flag('forms_nbclick',$_GET['forms_id'])) $forms->fields['viewed']++;
				$forms->save();
				include('./modules/forms/public_forms_display.php');
			}
			else ploopi_redirect($scriptenv);
		}
		else ploopi_redirect($scriptenv);
	break;

	case 'forms_viewreplies':
	case 'forms_filter':
	case 'forms_deletedata':
		include_once './modules/forms/public_forms_preparedata.php';
		include_once './modules/forms/public_forms_viewreplies.php';
	break;

	// sauve un enregistrement du formulaire
	case 'forms_reply_save':
		$forms = new forms();
		if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id']) && $forms->open($_POST['forms_id']))
		{

			$email_array = array();

			$isnew = false;

			$reply = new reply();
			if (!empty($_POST['forms_reply_id'])) // existant
			{
				$reply->open($_POST['forms_reply_id']);
				$email_array['Formulaire']['Opération'] = 'Modification d\'Enregistrement';
			}
			else // nouveau
			{
				$isnew = true;
				$email_array['Formulaire']['Opération'] = 'Nouvel Enregistrement';
				$reply->fields['date_validation'] = ploopi_createtimestamp();
				$reply->setuwm();
			}

			$email_array['Formulaire']['Titre'] = $forms->fields['label'];
			$email_array['Formulaire']['Date'] = $reply->fields['date_validation'];

			$reply->fields['id_form'] = $forms->fields['id'];
			$reply->fields['ip'] = $_SERVER['REMOTE_ADDR'];
			$reply->save();

			$email_array['Formulaire']['Adresse IP'] = $reply->fields['ip'];

			$sql = 	"
					SELECT 	*
					FROM 	ploopi_mod_forms_field
					WHERE 	id_form = {$forms->fields['id']}
					ORDER BY position
					";

			$rs_fields = $db->query($sql);

			while ($fields = $db->fetchrow($rs_fields))
			{
				$value = '';
				$fieldok = false;
				$error = false;

				if ($fields['type'] == 'file' && isset($_FILES['field_'.$fields['id']]['name']))
				{
					$fieldok = true;
					$value = $_FILES['field_'.$fields['id']]['name'];
					$path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$_SESSION['ploopi']['moduleid']._PLOOPI_SEP.$forms->fields['id']._PLOOPI_SEP.$reply->fields['id']._PLOOPI_SEP;
					$error = ($_FILES['field_'.$fields['id']]['size'] > _PLOOPI_MAXFILESIZE);

					if (!$error)
					{
						ploopi_makedir($path);
						if (file_exists($path) && is_writable($path))
						{
							move_uploaded_file($_FILES['field_'.$fields['id']]['tmp_name'], $path.$value);
							{
								chmod($path.$value, 0660);
							}
						}
					}
				}

				if (isset($_POST['field_'.$fields['id']]))
				{
					$fieldok = true;
					if (is_array($_POST['field_'.$fields['id']]))
					{
						foreach($_POST['field_'.$fields['id']] as $val)
						{
							if ($value != '') $value .= '||';
							$value .= $val;
						}
					}
					else $value = $_POST['field_'.$fields['id']];
				}
				else
				{
					if ($fields['type'] == 'autoincrement' && $isnew) // not in form => need to be calculated
					{
						$fieldok = true;
						$select = "SELECT max(value*1) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_form = '{$forms->fields['id']}' AND id_field = '{$fields['id']}'";
						$rs_maxinc = $db->query($select);
						$fields_maxinc = $db->fetchrow($rs_maxinc);
						$value = ($fields_maxinc['maxinc'] == '' || $fields_maxinc['maxinc'] == 0) ? 1 : $fields_maxinc['maxinc']+1;
					}
				}


				if ($fieldok = true)
				{
					$reply_field = new reply_field();
					if (isset($_POST['forms_reply_id']))
					{
						$reply_field->open($_POST['forms_reply_id'], $fields['id']);
					}

					$reply_field->fields['id_field'] = $fields['id'];
					$reply_field->fields['id_form'] = $forms->fields['id'];
					$reply_field->fields['id_reply'] = $reply->fields['id'];

					$reply_field->fields['value'] = (!(($fields['type'] == 'autoincrement' || $fields['type'] == 'file') && $value == '')) ? $value : '';

					$reply_field->save();

					$email_array['Contenu']["({$fields['id']}) {$fields['name']}"] = $reply_field->fields['value'];
				}

			}

			if ($forms->fields['email'] != '')
			{
				$list_email = explode(';',$forms->fields['email']);
				foreach($list_email as $email)
				{
					$from[0] = array('name' => $email, 'address' => $email);
					$to[] = array('name' => $email, 'address' => $email);
				}
				ploopi_send_form($from, $to, $email_array['Formulaire']['Titre'], $email_array);

			}


			ploopi_redirect("{$scriptenv}?op=forms_viewreplies&forms_id={$forms->fields['id']}");
		}
		else ploopi_redirect($scriptenv);
	break;

	default:
		include('./modules/forms/public_forms_list.php');
	break;
}
?>
