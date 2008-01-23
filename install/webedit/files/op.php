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
switch($_REQUEST['ploopi_op'])
{
	case 'webedit_getploopiobjects':
		ob_start();
		?>
		<script language="javascript">

		var oEditor = window.parent.InnerDialogLoaded() ;
		var FCKLang = oEditor.FCKLang ;
		var FCKPlaceholders = oEditor.FCKPlaceholders ;

		window.onload = function ()
		{
			// First of all, translate the dialog box texts
			oEditor.FCKLanguageManager.TranslatePage( document ) ;

			LoadSelected() ;

			// Show the "Ok" button.
			window.parent.SetOkButton( true ) ;
		}

		var eSelected = oEditor.FCKSelection.GetSelectedElement() ;

		function LoadSelected()
		{
			if ( !eSelected )
				return ;

			var info = eSelected._fckplaceholder.split("/");
			var sValue = info[0];

			if ( eSelected.tagName == 'SPAN' && eSelected._fckplaceholder )
			{
				var obj = document.getElementById('ploopi_webedit_objects');
				for (i=0;i<obj.length;i++) if (obj[i].value == sValue) obj.selectedIndex = i;
			}
			else
				eSelected == null ;
		}

		function Ok()
		{
			var obj = document.getElementById('ploopi_webedit_objects');

			var sValue = obj[obj.selectedIndex].value+'/'+obj[obj.selectedIndex].text ;

			if ( eSelected && eSelected._fckplaceholder == sValue )
				return true ;

			if ( sValue.length == 0 )
			{
				alert( FCKLang.PlaceholderErrNoName ) ;
				return false ;
			}

			if ( FCKPlaceholders.Exist( sValue ) )
			{
				alert( FCKLang.PlaceholderErrNameInUse ) ;
				return false ;
			}

			FCKPlaceholders.Add( sValue ) ;
			return true ;
		}

		</script>

		<div style="padding:4px 0;">Choix d'un objet PLOOPI à insérer dans la page :</div>
		<?
		$select_object =	"
							SELECT 	ploopi_mb_wce_object.*,
									ploopi_module.label as module_label,
									ploopi_module.id as module_id

							FROM 	ploopi_mb_wce_object,
									ploopi_module,
									ploopi_module_workspace

							WHERE	ploopi_mb_wce_object.id_module_type = ploopi_module.id_module_type
							AND		ploopi_module_workspace.id_module = ploopi_module.id
							AND		ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['workspaceid']}
							";

		$result_object = $db->query($select_object);
		while ($fields_object = $db->fetchrow($result_object))
		{
			if ($fields_object['select_label'] != '')
			{
				$select = "select {$fields_object['select_id']}, {$fields_object['select_label']} from {$fields_object['select_table']} where id_module = {$fields_object['module_id']}";
				$db->query($select);

				while ($fields = $db->fetchrow())
				{
					$fields_object['object_label'] = $fields[$fields_object['select_label']];
					$array_modules["{$fields_object['id']},{$fields_object['module_id']},{$fields[$fields_object['select_id']]}"] = $fields_object;
				}
			}
			else $array_modules["{$fields_object['id']},{$fields_object['module_id']}"] = $fields_object;
		}
		?>
		<select id="ploopi_webedit_objects" style="width:100%;">
			<option value="0">(aucun)</option>
			<?
			foreach($array_modules as $key => $value)
			{
				//if ($fields_column['id_object'] == $key) $sel = 'selected';
				//else $sel = '';
				$sel = '';
				?>
				<option <? echo $sel; ?> value="<? echo $key; ?>"><? echo "{$value['module_label']} » {$value['label']}"; if (!empty($value['object_label'])) echo " » {$value['object_label']}"; ?></option>
				<?
			}
			?>
		</select>
		<?
		$main_content = ob_get_contents();
		@ob_end_clean();

		$template_body->assign_vars(array(
			'TEMPLATE_PATH' 		=> $_SESSION['ploopi']['template_path'],
			'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
			'PAGE_CONTENT' 			=> $main_content
			)
		);

		$template_body->pparse('body');
		ploopi_die();
	break;


	case 'webedit_selectlink':
	case 'webedit_detail_heading';
		ob_start();
		include_once './modules/webedit/fck_link.php';
		$main_content = ob_get_contents();
		@ob_end_clean();

		$template_body->assign_vars(array(
			'TEMPLATE_PATH' 		=> $_SESSION['ploopi']['template_path'],
			'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
			'PAGE_CONTENT' 			=> $main_content
			)
		);

		$template_body->assign_block_vars('module_css',array(
													'PATH' => "./modules/webedit/include/styles.css"
												)
										);

		$template_body->assign_block_vars('module_css_ie',array(
													'PATH' => "./modules/webedit/include/styles_ie.css"
												)
										);


		$template_body->pparse('body');
		ploopi_die();
	break;


	case 'webedit_getbackup':
		include_once './modules/webedit/class_article_backup.php';

		$article_backup = new webedit_article_backup();
		if (!empty($_GET['backup_id_article']) && !empty($_GET['backup_timestp']) && is_numeric($_GET['backup_id_article']) && is_numeric($_GET['backup_timestp']) && $article_backup->open($_GET['backup_id_article'],$_GET['backup_timestp']))
		{
			echo $article_backup->fields['content'];
		}
		ploopi_die();
	break;
}
