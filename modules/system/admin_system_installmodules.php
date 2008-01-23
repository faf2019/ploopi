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

echo $skin->open_simplebloc(_SYSTEM_LABEL_INSTALLEDMODULES);

$tabmoduletype_installed = array();
$tabmoduletype_install = array();

// get all modules in install folder
if ($dir = @opendir("./install/"))
{
	include_once './include/classes/class_xml2array.php';

	while($file = readdir($dir))
	{
    	if (is_dir("./install/{$file}") && !ereg( "([.]{1,2})", $file)) // read folders in install
    	{
    		$descfile = "./install/{$file}/description.xml";
			if (file_exists($descfile))
			{
				$xml2array = new xml2array();
				$xmlarray = $xml2array->parseFile($descfile);
				if ($xmlarray)
				{
					$moduleinfo = &$xmlarray['root']['ploopi'][0]['moduletype'][0];

					$tabmoduletype_install[$moduleinfo['label'][0]] = array(
						'label' => $moduleinfo['label'][0],
						'version' => $moduleinfo['version'][0],
						'date' => $moduleinfo['date'][0],
						'author' => $moduleinfo['author'][0],
						'description' => $moduleinfo['description'][0]
					);
				}
				else // erreur XML
				{

					$tabmoduletype_install[$file] = array(
						'label' => $file,
						'version' => '',
						'date' => '',
						'author' => '',
						'description' => '',
						'error' => true
					);
				}
			}
    	}
  	}
  	closedir($dir);
}
ksort($tabmoduletype_install);



$columns = array();
$values = array();
$c = 0;

$columns['auto']['desc'] 		= array('label' => _SYSTEM_LABEL_DESCRIPTION, 'options' => array('sort' => true));
$columns['right']['action'] 	= array('label' => _SYSTEM_LABEL_UNINSTALL, 'width' => '100', 'style' => 'text-align:center;');
$columns['right']['wce'] 		= array('label' => _SYSTEM_LABEL_WCEOBJECTS, 'width' => '100');
$columns['right']['metabase'] 	= array('label' => _SYSTEM_LABEL_METABASE, 'width' => '80');
$columns['right']['actions'] 	= array('label' => _SYSTEM_LABEL_ACTIONS, 'width' => '60');
$columns['right']['date'] 		= array('label' => _SYSTEM_LABEL_DATE, 'width' => '80', 'options' => array('sort' => true));
$columns['right']['version'] 	= array('label' => _SYSTEM_LABEL_VERSION, 'width' => '75', 'options' => array('sort' => true));
$columns['right']['author'] 	= array('label' => _SYSTEM_LABEL_AUTHOR, 'width' => '130', 'options' => array('sort' => true));
$columns['left']['mtype'] 		= array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '80', 'options' => array('sort' => true));

// get all modules installed in a table
$select = 	"
			SELECT 	*
			FROM 	ploopi_module_type
			WHERE	system != 1
			ORDER 	BY label
			";

$result = $db->query($select);

while ($fields = $db->fetchrow($result))
{
	$select = "SELECT * FROM ploopi_mb_action WHERE id_module_type = {$fields['id']}";
	$db->query($select);
	if ($db->numrows()) $has_actions = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_green.png\" align=\"middle\">";
	else $has_actions = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_red.png\" align=\"middle\">";

	$select = "SELECT * FROM ploopi_mb_table WHERE id_module_type = {$fields['id']}";
	$db->query($select);
	if ($db->numrows()) $has_mb = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_green.png\" align=\"middle\">";
	else $has_mb = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_red.png\" align=\"middle\">";

	$select = "SELECT * FROM ploopi_mb_wce_object WHERE id_module_type = {$fields['id']}";
	$db->query($select);
	if ($db->numrows()) $has_cmsop = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_green.png\" align=\"middle\">";
	else $has_cmsop = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_red.png\" align=\"middle\">";

	$ldate = ploopi_timestamp2local($fields['date']);

	$values[$c]['values']['desc'] = array('label' => $fields['description'], 'style' => '');
	$values[$c]['values']['mtype'] = array('label' => $fields['label'], 'style' => '');
	$values[$c]['values']['author'] = array('label' => $fields['author'], 'style' => '');
	$values[$c]['values']['version'] = array('label' => $fields['version'], 'style' => '');
	$values[$c]['values']['date'] = array('label' => $ldate['date'], 'style' => '', 'sort_label' => $fields['date']);
	$values[$c]['values']['actions'] = array('label' => $has_actions, 'style' => 'text-align:center');
	$values[$c]['values']['metabase'] = array('label' => "<a title=\""._PLOOPI_UPDATE."\" href=\"javascript:ploopi_confirmlink('".ploopi_urlencode("{$scriptenv}?op=updatemb&moduletype={$fields['label']}&idmoduletype={$fields['id']}")."','"._SYSTEM_MSG_CONFIRMMBUPDATE."')\">{$has_mb}</a>", 'style' => 'text-align:center');
	$values[$c]['values']['wce'] = array('label' => $has_cmsop, 'style' => 'text-align:center');
	$values[$c]['values']['action'] = array('label' => "<a href=\"javascript:ploopi_confirmlink('".ploopi_urlencode("{$scriptenv}?op=uninstall&uninstallidmoduletype={$fields['id']}")."','"._SYSTEM_MSG_CONFIRMMODULEUNINSTAL."')\">"._SYSTEM_LABEL_UNINSTALL."</a>", 'style' => 'text-align:center;');

	$values[$c]['description'] = $fields['description'];
	$values[$c]['link'] = '';
	$values[$c]['style'] = '';
	$c++;

	$tabmoduletype_installed[$fields['label']]['version'] = $fields['version'];
	$tabmoduletype_installed[$fields['label']]['id'] = $fields['id'];
}

$skin->display_array($columns, $values, 'array_installed_modules', array('sortable' => true, 'orderby_default' => 'mtype'));

echo $skin->close_simplebloc();

$columns = array();
$values = array();
$c = 0;

$columns['auto']['desc'] = array('label' => _SYSTEM_LABEL_DESCRIPTION, 'options' => array('sort' => true));
$columns['right']['action'] = array('label' => _SYSTEM_LABEL_UPDATE, 'width' => '100', 'style' => 'text-align:center;');
$columns['right']['date'] = array('label' => _SYSTEM_LABEL_DATE, 'width' => '80', 'options' => array('sort' => true));
$columns['right']['version'] = array('label' => _SYSTEM_LABEL_VERSION, 'width' => '75', 'options' => array('sort' => true));
$columns['right']['author'] = array('label' => _SYSTEM_LABEL_AUTHOR, 'width' => '130', 'options' => array('sort' => true));
$columns['left']['mtype'] = array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '80', 'options' => array('sort' => true));

foreach($tabmoduletype_install as $label => $fields)
{
	if (isset($tabmoduletype_installed[$label])) // new module version if already defined in installed module and greater version
	{
		if ($tabmoduletype_install[$label]['version'] > $tabmoduletype_installed[$label]['version'])
		{
			if (empty($fields['error']))
			{
				$ldate = ploopi_timestamp2local($fields['date']);

				$values[$c]['values']['desc'] = array('label' => $fields['description'], 'style' => '');
				$values[$c]['values']['mtype'] = array('label' => $fields['label'], 'style' => '');
				$values[$c]['values']['author'] = array('label' => $fields['author'], 'style' => '');
				$values[$c]['values']['version'] = array('label' => $fields['version'], 'style' => '');
				$values[$c]['values']['date'] = array('label' => $ldate['date'], 'style' => '', 'sort_label' => $fields['date']);
				$values[$c]['values']['action'] = array('label' => "<a href=\"".ploopi_urlencode("{$scriptenv}?op=update&idmoduletype={$tabmoduletype_installed[$label]['id']}&installmoduletype={$tabmoduletype_install[$label]['label']}&updatefrom={$tabmoduletype_installed[$label]['version']}&updateto={$fields['version']}")."\">"._SYSTEM_LABEL_UPDATE."</a>", 'style' => 'text-align:center;');
			}
			else
			{
				$values[$c]['values']['desc'] = array('label' => 'Erreur dans la structure XML', 'style' => 'font-weight:bold;color:#a60000;');
				$values[$c]['values']['mtype'] = array('label' => $fields['label']);
				$values[$c]['values']['author'] = array('label' => '&nbsp;');
				$values[$c]['values']['version'] = array('label' => '&nbsp;');
				$values[$c]['values']['date'] = array('label' => '&nbsp;');
				$values[$c]['values']['action'] = array('label' => '&nbsp;');
			}
			$c++;
		}
	}
}


echo $skin->open_simplebloc(_SYSTEM_LABEL_NEWMODULEVERSIONS,'100%');
$skin->display_array($columns, $values, 'array_toupdate_modules', array('sortable' => true, 'orderby_default' => 'mtype'));
echo $skin->close_simplebloc();

$columns = array();
$values = array();
$c = 0;

$columns['auto']['desc'] = array('label' => _SYSTEM_LABEL_DESCRIPTION, 'options' => array('sort' => true));
$columns['right']['action'] = array('label' => _SYSTEM_LABEL_INSTALL, 'width' => '100', 'style' => 'text-align:center;');
$columns['right']['date'] = array('label' => _SYSTEM_LABEL_DATE, 'width' => '80', 'options' => array('sort' => true));;
$columns['right']['version'] = array('label' => _SYSTEM_LABEL_VERSION, 'width' => '75', 'options' => array('sort' => true));
$columns['right']['author'] = array('label' => _SYSTEM_LABEL_AUTHOR, 'width' => '130', 'options' => array('sort' => true));
$columns['left']['mtype'] = array('label' => _SYSTEM_LABEL_MODULETYPE, 'width' => '80', 'options' => array('sort' => true));


foreach($tabmoduletype_install as $label => $fields)
{
	if (!isset($tabmoduletype_installed[$label])) // module is new if not defined in installed module
	{
		if (empty($fields['error']))
		{
			$ldate = ploopi_timestamp2local($fields['date']);

			$values[$c]['values']['desc'] = array('label' => $fields['description']);
			$values[$c]['values']['mtype'] = array('label' => $fields['label']);
			$values[$c]['values']['author'] = array('label' => $fields['author']);
			$values[$c]['values']['version'] = array('label' => $fields['version']);
			$values[$c]['values']['date'] = array('label' => $ldate['date']);
			$values[$c]['values']['action'] = array('label' => "<a href=\"".ploopi_urlencode("{$scriptenv}?op=install&installmoduletype={$fields['label']}")."\">"._SYSTEM_LABEL_INSTALL."</a>", 'style' => 'text-align:center;');
		}
		else
		{
			$values[$c]['values']['desc'] = array('label' => 'Erreur dans la structure XML', 'style' => 'font-weight:bold;color:#a60000;');
			$values[$c]['values']['mtype'] = array('label' => $fields['label']);
			$values[$c]['values']['author'] = array('label' => '&nbsp;');
			$values[$c]['values']['version'] = array('label' => '&nbsp;');
			$values[$c]['values']['date'] = array('label' => '&nbsp;');
			$values[$c]['values']['action'] = array('label' => '&nbsp;');
		}
		$c++;
	}
}

echo $skin->open_simplebloc(_SYSTEM_LABEL_UNINSTALLEDMODULES,'100%');
$skin->display_array($columns, $values, 'array_new_modules', array('sortable' => true, 'orderby_default' => 'mtype'));
echo $skin->close_simplebloc();
?>
