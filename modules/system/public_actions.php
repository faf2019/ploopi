<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Affichage des actions possibles d'un utilisateur (par module).
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Création du titre de la page
 */
echo ploopi\skin::get()->create_pagetitle(_PLOOPI_LABEL_MYWORKSPACE);
echo ploopi\skin::get()->open_simplebloc(_PLOOPI_LABEL_MYDATA);
?>
<div style="padding:4px;">
<?php

$red = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/check_off.png\" style=\"margin-right:4px;\" />";
$green = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/check_on.png\" style=\"margin-right:4px;\" />";

$arrActions = array();

ploopi\db::get()->query("SELECT * FROM ploopi_mb_action");
while ($fields = ploopi\db::get()->fetchrow()) $arrActions[$fields['id_module_type']][$fields['id_action']] = $fields;

$num_array = 0;

foreach ($_SESSION['ploopi']['workspaces'] as $group)
{
    if (!empty($group['adminlevel']) && $group['id'] != _PLOOPI_SYSTEMGROUP)
    {
        echo ploopi\skin::get()->open_simplebloc(ploopi\str::htmlentities("Espace « {$group['label']} »"));

        $columns = array();
        $values = array();
        $c = 0;

        $columns['left']['module']       = array('label' => 'Module', 'width' => '250', 'options' => array('sort' => true));
        $columns['auto']['actions']     = array('label' => 'Actions');

        if (isset($group['modules']))
        {
            foreach ($group['modules'] as $moduleid)
            {
                $strActions = '';

                if (!empty($arrActions[$_SESSION['ploopi']['modules'][$moduleid]['id_module_type']]))
                {
                    foreach($arrActions[$_SESSION['ploopi']['modules'][$moduleid]['id_module_type']] as $id => $action)
                    {
                        $puce = ploopi\acl::isactionallowed($id, $group['id'], $moduleid) ? $green : $red;
                        $strActions .= "<p class=\"ploopi_va\">{$puce}<span>".ploopi\str::htmlentities($action['label'])."</span></p>";
                    }
                }

                if ($strActions == '') $strActions = 'Aucune action pour ce module';

                $values[$c]['values']['module'] = array('label' => ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$moduleid]['label']));
                $values[$c]['values']['actions'] = array('label' => $strActions);

                $values[$c]['description'] = '';
                $c++;
            }
        }

        ploopi\skin::get()->display_array($columns, $values, 'system_actions_list'.$num_array);
        echo ploopi\skin::get()->close_simplebloc();
        $num_array++;
    }
}
?>
</div>
<?php echo ploopi\skin::get()->close_simplebloc(); ?>
