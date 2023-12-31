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
 * Interface de gestion des rôles
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Ouverture du bloc
 */

echo ploopi\skin::get()->open_simplebloc();

$parents = str_replace(';',',',$workspace->fields['parents']);

// on recherche les rôles des modules de l'espace sélectionné (ou hérités des espaces parents)
$sql =  "
        SELECT      r.id,
                    r.label,
                    r.description,
                    r.shared,
                    r.id_workspace,
                    m.label as module_label,
                    m.id as module_id,
                    mt.label as module_type,
                    w.label as origine

        FROM        ploopi_role r

        INNER JOIN  ploopi_module m ON m.id = r.id_module

        INNER JOIN  ploopi_module_type mt ON mt.id = m.id_module_type

        INNER JOIN  ploopi_workspace w ON w.id = r.id_workspace

        INNER JOIN  ploopi_module_workspace mw ON mw.id_workspace = {$workspaceid} AND mw.id_module = m.id

        WHERE       ((r.id_workspace = {$workspaceid}
        OR          (r.id_workspace IN ({$parents}) AND r.shared = 1)))

        GROUP BY    r.id
        ORDER BY    module_type, m.label
        ";

ploopi\db::get()->query($sql);

$columns = array();
$values = array();
$c = 0;

$columns['auto']['desc']        = array('label' => 'Description', 'options' => array('sort' => true));
$columns['left']['module']      = array('label' => 'Module', 'width' => '150', 'options' => array('sort' => true));
$columns['left']['role']        = array('label' => 'Rôle', 'width' => '200', 'options' => array('sort' => true));
$columns['right']['shared']     = array('label' => 'Partagé', 'width' => '65');
$columns['right']['origine']    = array('label' => 'Origine', 'width' => '150', 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '44');

while($row = ploopi\db::get()->fetchrow())
{
    if ($row['id_workspace'] == $workspaceid)
    {
        $actions =  '
                    <a href="'.ploopi\crypt::urlencode("admin.php?op=modify_role&roleid={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" alt="'._SYSTEM_LABEL_MODIFY.'"></a>
                    <a href="javascript:ploopi.confirmlink(\''.ploopi\crypt::urlencode("admin.php?op=delete_role&roleid={$row['id']}").'\',\''._SYSTEM_MSG_CONFIRMROLEDELETE.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" alt="'._SYSTEM_LABEL_DELETE.'"></a>
                    ';
    }
    else $actions = '&nbsp;';

    $values[$c]['values']['desc']       = array('label' => ploopi\str::htmlentities($row['description']));
    $values[$c]['values']['module']     = array('label' => ploopi\str::htmlentities($row['module_label']), 'sort_label' => sprintf("%s_%s", $row['module_label'], $row['label']));
    $values[$c]['values']['role']       = array('label' => ploopi\str::htmlentities($row['label']));
    $values[$c]['values']['shared']     = array('label' => '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/check_'.(($row['shared'] ? 'on' : 'off')).'.png">');
    $values[$c]['values']['origine']    = array('label' => ploopi\str::htmlentities($row['origine']));
    $values[$c]['values']['actions']    = array('label' => $actions);

    $values[$c]['description'] = ploopi\str::htmlentities($row['description']);
    if ($row['id_workspace'] == $workspaceid) $values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=modify_role&roleid={$row['id']}");
    $c++;
}

ploopi\skin::get()->display_array($columns, $values, 'array_roles', array('sortable' => true, 'orderby_default' => 'module'));

echo ploopi\skin::get()->close_simplebloc();
?>
