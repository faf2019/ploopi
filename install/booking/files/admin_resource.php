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
 * Administration / Ressources
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
    <a href="javascript:void(0);" onclick="javascript:booking_element_delete('resource');"><img src="./modules/booking/img/ico_trash.png">Supprimer les éléments cochés</a>
    <a href="javascript:void(0);" onclick="javascript:booking_element_add('resource', event);"><img src="./modules/booking/img/ico_new.png">Ajouter un élément</a>
</div>

<?php

$arrResult = 
    array(
        'columns' => array(),
        'rows' => array()
    );
    
    
$arrResult['columns']['left']['type'] = 
    array(    
        'label' => 'Type',
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
    
$arrResult['columns']['right']['count'] = 
    array(   
        'label' => 'Evt.',
        'width' => 60,
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['right']['active'] = 
    array(   
        'label' => 'Actif',
        'width' => 60,
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['right']['color'] = 
    array(   
        'label' => 'C.',
        'width' => 20
    );    
    
$arrResult['columns']['right']['workspace'] = 
    array( 
        'label' => 'Créé par',
        'width' => '150',
        'options' => array('sort' => true)
    );
        
$arrResult['columns']['right']['resworkspace'] = 
    array( 
        'label' => 'Géré par',
        'width' => '150',
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['actions_right']['actions'] = 
    array(
        'label' => '', 
        'width' => '24'
    );    
    
// Récupération des espaces gestionnaires
ploopi\db::get()->query("
    SELECT      r.id,
                w.label
                
    FROM        (ploopi_mod_booking_resource r,
                ploopi_mod_booking_resourcetype rt,
                ploopi_mod_booking_resource_workspace rw,
                ploopi_workspace w)
    
    WHERE       r.id_resourcetype = rt.id
    AND         rw.id_resource = r.id
    AND         w.id = rw.id_workspace
    AND         r.id_module = {$_SESSION['ploopi']['moduleid']}
    
    ORDER BY    r.id, w.depth
");   

$arrResWorkspaces = array();
while ($row = ploopi\db::get()->fetchrow()) $arrResWorkspaces[$row['id']][] = ploopi\str::htmlentities($row['label']);

// Récupération des ressources
ploopi\db::get()->query("
    SELECT      r.*,
                rt.name as rt_name,
                w.label as w_label,
                IF(ISNULL(e.id), 0, count(*)) as c
                
    FROM        (ploopi_mod_booking_resource r,
                ploopi_mod_booking_resourcetype rt)
    
    LEFT JOIN   ploopi_mod_booking_event e
    ON          e.id_resource = r.id
    
    LEFT JOIN   ploopi_workspace w
    ON          w.id = r.id_workspace
    
    WHERE       r.id_resourcetype = rt.id
    AND         r.id_module = {$_SESSION['ploopi']['moduleid']}
    
    GROUP BY    r.id
");   

while ($row = ploopi\db::get()->fetchrow())
{
    $arrResult['rows'][] = 
        array(
            'values' => 
                array(
                    'reference' => 
                        array(
                            'label' => ploopi\str::htmlentities($row['reference']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'type' => 
                        array(
                            'label' => ploopi\str::htmlentities($row['rt_name']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'name' => 
                        array(
                            'label' => ploopi\str::htmlentities($row['name']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'count' => array('label' => $row['c']),
                    'resworkspace' => array('label' => empty($arrResWorkspaces[$row['id']]) ? '' : implode('<br />', $arrResWorkspaces[$row['id']])),
                    'workspace' => array('label' => $row['w_label']),
                    'active' => array('label' => ($row['active']) ? 'oui' : 'non'),
                    'color' => 
                        array(
                            'label' => '<div style="width:8px;height:8px;margin-left:4px;border:1px solid #a0a0a0;background-color:'.ploopi\str::htmlentities($row['color']).'"></div>'
                        ),
                    'actions' => array('label' => ($row['c']) ? '&nbsp;' : '<input type="checkbox" class="booking_element_checkbox" value="'.$row['id'].'">')
                ),
            'description' => "Modifier la ressource '".ploopi\str::htmlentities($row['name'])."'",
            'link' => 'javascript:void(0);',
            'onclick' => "booking_element_open('resource', '{$row['id']}', event);"
        );
}

ploopi\skin::get()->display_array(
    $arrResult['columns'], 
    $arrResult['rows'], 
    'booking_resource', 
    array(
        'sortable' => true, 
        'orderby_default' => 'name',
        'sort_default' => 'ASC'
    )
);
?>
