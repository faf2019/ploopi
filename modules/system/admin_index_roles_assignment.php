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
 * Interface de gestion des affectations de rôles
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */
echo ploopi\skin::get()->open_simplebloc();

$arrRole = null;

$parents = str_replace(';',',',$workspace->fields['parents']);

/**
 * On recherche les rôles des modules de l'espace sélectionné (ou hérités des espaces parents)
 */
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

while($row = ploopi\db::get()->fetchrow())
{
    $values[$c]['values']['desc']       = array('label' => ploopi\str::htmlentities($row['description']));
    $values[$c]['values']['module']     = array('label' => ploopi\str::htmlentities($row['module_label']), 'sort_label' => sprintf("%s_%s", $row['module_label'], $row['label']));
    $values[$c]['values']['role']       = array('label' => ploopi\str::htmlentities($row['label']));
    $values[$c]['values']['shared']     = array('label' => '<img src="'.$_SESSION['ploopi']['template_path'].'/img/system/p_'.(($row['shared'] ? 'green' : 'red')).'.png">');
    $values[$c]['values']['origine']    = array('label' => ploopi\str::htmlentities($row['origine']));

    if ($op == 'assign_role' && !empty($_GET['roleid']) && is_numeric($_GET['roleid']) && $_GET['roleid'] == $row['id'])
    {
        $values[$c]['style'] = 'background-color:#ffe0e0;';
        $arrRole = $row;
    }

    $values[$c]['description'] = 'Attribuer ce rôle';
    $values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=assign_role&roleid={$row['id']}");
    $c++;
}

ploopi\skin::get()->display_array($columns, $values, 'array_roles', array('sortable' => true, 'orderby_default' => 'module', 'height' => 150));

echo ploopi\skin::get()->close_simplebloc();

if ($op == 'assign_role' && !empty($_GET['roleid']) && is_numeric($_GET['roleid']))
{
    echo ploopi\skin::get()->open_simplebloc("Gestion des attributions du rôle &laquo; ".ploopi\str::htmlentities($arrRole['label'])." &raquo; du module &laquo; ".ploopi\str::htmlentities($arrRole['module_label'])." &raquo;");
    ?>

    <p class="ploopi_va" style="padding:4px; background-color:#e0e0e0; border-bottom:1px solid #c0c0c0;">
        <span style="font-weight:bold;">Rechercher un utilisateur ou un groupe :</span>
        <input type="text" id="system_roleusers_filter" class="text">
        <img style="cursor:pointer;" onclick="javascript:system_roleusers_search(<?php echo ploopi\str::htmlentities($_GET['roleid']); ?>);" src="<?php echo "{$_SESSION['ploopi']['template_path']}/img/validation/search.png"; ?>">
    </p>

    <div id="system_roleusers_search_result"></div>

    <div id="system_roleusers_list">
    <?php
    $roleid = $_GET['roleid'];
    include './modules/system/admin_index_roles_assignment_list.php';
    ?>
    </div>
    <?php
    echo ploopi\skin::get()->close_simplebloc();
}
?>
