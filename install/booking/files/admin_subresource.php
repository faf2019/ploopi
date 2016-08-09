<?php
/*
    Copyright (c) 2015 Ovensia
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
 * Administration / Sous-ressources
 *
 * @package booking
 * @subpackage admin
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
?>

<div class="booking_toolbar">
    <em style="float:left;color:#a60000;margin-left:4px;">En rouge, les éléments inactifs</em>
    <a href="javascript:void(0);" onclick="javascript:booking_element_check();"><img src="./modules/booking/img/ico_checkbox.png">Cocher/Décocher tout</a>
    <a href="javascript:void(0);" onclick="javascript:booking_element_delete('subresource');"><img src="./modules/booking/img/ico_trash.png">Supprimer les éléments cochés</a>
    <a href="javascript:void(0);" onclick="javascript:booking_element_add('subresource', event);"><img src="./modules/booking/img/ico_new.png">Ajouter un élément</a>
</div>

<?php

$arrResult =
    array(
        'columns' => array(),
        'rows' => array()
    );


$arrResult['columns']['left']['resource'] =
    array(
        'label' => 'Ressource',
        'width' => 200,
        'options' => array('sort' => true)
    );

$arrResult['columns']['left']['reference'] =
    array(
        'label' => 'Référence',
        'width' => 120,
        'options' => array('sort' => true)
    );

$arrResult['columns']['auto']['name'] =
    array(
        'label' => 'Intitulé',
        'options' => array('sort' => true)
    );

$arrResult['columns']['right']['active'] =
    array(
        'label' => 'Actif',
        'width' => 60,
        'options' => array('sort' => true)
    );


$arrResult['columns']['actions_right']['actions'] =
    array(
        'label' => '',
        'width' => '24'
    );

// Récupération des sous-ressources
$db->query("
    SELECT      sr.*,
                r.name as res_name

    FROM        ploopi_mod_booking_subresource sr

    INNER JOIN  ploopi_mod_booking_resource r ON r.id = sr.id_resource

    WHERE       sr.id_module = {$_SESSION['ploopi']['moduleid']}
");

while ($row = $db->fetchrow())
{
    $arrResult['rows'][] =
        array(
            'values' =>
                array(
                    'reference' =>
                        array(
                            'label' => ploopi_htmlentities($row['reference']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'resource' =>
                        array(
                            'label' => ploopi_htmlentities($row['res_name']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'name' =>
                        array(
                            'label' => ploopi_htmlentities($row['name']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'active' => array('label' => ($row['active']) ? 'oui' : 'non'),
                    'actions' => array('label' => '<input type="checkbox" class="booking_element_checkbox" value="'.$row['id'].'">')
                ),
            'description' => "Modifier la sous-ressource '".ploopi_htmlentities($row['name'])."'",
            'link' => 'javascript:void(0);',
            'onclick' => "booking_element_open('subresource', '{$row['id']}', event);"
        );
}

$skin->display_array(
    $arrResult['columns'],
    $arrResult['rows'],
    'booking_subresource',
    array(
        'sortable' => true,
        'orderby_default' => 'name',
        'sort_default' => 'ASC'
    )
);
?>
