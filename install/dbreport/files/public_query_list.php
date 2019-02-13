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
 * Liste des requêtes déjà créées
 *
 * @package dbreport
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$arrColumns = array();
$arrValues = array();

$arrColumns['auto']['query'] =
    array(
        'label' => 'Requête',
        'options' => array('sort' => true)
    );

$arrColumns['right']['webservice'] =
    array(
        'label' => 'Webservice / Identifiant',
        'width' => 300
    );

$arrColumns['actions_right']['actions'] =
    array(
        'label' => '&nbsp;',
        'width' => ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE) ? 130 : 60
    );

/*$arrColumns['actions_right']['exports'] =
    array(
        'label' => 'Exports',
        'width' => 145
    );
  */

$objQuery = new ploopi\query_select();
$objQuery->add_from('ploopi_mod_dbreport_query');
$objQuery->add_where('id_workspace IN (%e)', ploopi\system::viewworkspaces());
$objQuery->add_where('id_module = %d', $_SESSION['ploopi']['moduleid']);
$objRs = $objQuery->execute();

while ($row = $objRs->fetchrow())
{
    $strActions = '<a class="dbreport_action" title="Exécuter la requête" href="javascript:void(0);" onclick="javascript:ploopi.xhr.topopup(550, event, \'dbreport_query_export_popup\', \'admin-light.php\', \''.ploopi\crypt::queryencode("ploopi_op=dbreport_query_export&dbreport_query_id={$row['id']}").'\', \'POST\');"><img src="./modules/dbreport/img/ico_execute.png" /></a>';

    $strActions .= '<a class="dbreport_action" title="Voir le graphique" href="javascript:void(0);" onclick="javascript:ploopi.openwin(\''.ploopi\crypt::urlencode("admin-light.php?ploopi_op=dbreport_chart_generate&dbreport_query_id={$row['id']}").'\', '.($row['chart_width']+20).', '.($row['chart_height']+30).');"><img src="./modules/dbreport/img/ico_graph.png" /></a>';

    $booModify = ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE) && (!$row['locked'] || ploopi\acl::isactionallowed(dbreport::_ACTION_LOCK));

    if ($booModify) $strActions .= '<a class="dbreport_action" title="Modifier la requête" href="'.ploopi\crypt::urlencode("admin.php?dbreport_op=query_modify&dbreport_query_id={$row['id']}").'"><img src="./modules/dbreport/img/ico_modify.png" /></a>';

    if (ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE)) $strActions .= '<a class="dbreport_action" title="Cloner la requête" href="javascript:void(0);" onclick="if (confirm(\'Êtes vous certains de vouloir cloner cette requête ?\')) document.location.href=\''.ploopi\crypt::urlencode("admin-light.php?ploopi_op=dbreport_query_clone&dbreport_query_id={$row['id']}").'\';"><img src="./modules/dbreport/img/ico_clone.png" /></a>';

    if ($booModify) $strActions .= '<a class="dbreport_action" title="Supprimer la requête" href="javascript:void(0);" onclick="if (confirm(\'Êtes vous certains de vouloir supprimer cette requête ?\')) document.location.href=\''.ploopi\crypt::urlencode("admin-light.php?ploopi_op=dbreport_query_delete&dbreport_query_id={$row['id']}").'\';"><img src="./modules/dbreport/img/ico_delete.png" /></a>';

    $arrValues[] = array(
        'values' => array(
            'query' => array('label' => ploopi\str::htmlentities($row['label'])),
            'webservice' => array('label' => ploopi\str::htmlentities($row['ws_id'])),
            'actions' => array('label' =>  $strActions),
            'exports' => array(
                'label' => '
                    <a title="Export SQL" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=SQL"><img src="./modules/dbreport/img/mime/sql.png" /></a>
                    <a title="Export HTML" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=HTML"><img src="./modules/dbreport/img/mime/html.png" /></a>
                    <a title="Export XML" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=XML"><img src="./modules/dbreport/img/mime/xml.png" /></a>
                    <a title="Export CSV" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=CSV"><img src="./modules/dbreport/img/mime/csv.png" /></a>
                    <a title="Export XLS" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=XLS"><img src="./modules/dbreport/img/mime/xls.png" /></a>
                    <a title="Export ODS" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=ODS"><img src="./modules/dbreport/img/mime/ods.png" /></a>
                    <a title="Export PDF" href="./admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id='.$row['id'].'&dbreport_format=PDF"><img src="./modules/dbreport/img/mime/pdf.png" /></a>
                    '
            )
        ),
        'description' => $booModify ? 'Modifier la requête' : 'Exécuter la requête',
        'link' => $booModify ? ploopi\crypt::urlencode("admin.php?dbreport_op=query_modify&dbreport_query_id={$row['id']}") : "javascript:void(0);",
        'onclick' => $booModify ? 'null' : 'javascript:ploopi.xhr.topopup(550, event, \'dbreport_query_export_popup\', \'admin-light.php\', \''.ploopi\crypt::queryencode("ploopi_op=dbreport_query_export&dbreport_query_id={$row['id']}").'\', \'POST\');',
        'style' => ''
    );
}


echo ploopi\skin::get()->open_simplebloc('Liste des requêtes');

if (ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE))
{
    ?>
    <div class="ploopi_tabs">
        <a href="javascript:void(0);" onclick="javascript:ploopi.xhr.topopup(400, event, 'dbreport_query_add', 'admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=dbreport_query_add'); ?>', 'POST');"><img src="./modules/dbreport/img/ico_new.png" /><span>Nouvelle requête</span></a>
    </div>
    <?php
}

ploopi\skin::get()->display_array($arrColumns, $arrValues, 'dbreport_query_list', array('sortable' => true, 'orderby_default' => 'query', 'limit' => 25));

echo ploopi\skin::get()->close_simplebloc();
?>
