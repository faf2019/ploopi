<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Administration / Types de ressources
 * 
 * @package booking
 * @subpackage admin
 * @copyright Ovensia
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
?>

<div class="booking_toolbar">
    <em style="float:left;color:#a60000;margin-left:4px;">En rouge, les �l�ments inactifs</em>
    <a href="javascript:void(0);" onclick="javascript:booking_element_check();"><img src="./modules/booking/img/ico_checkbox.png">Cocher/D�cocher tout</a>
    <a href="javascript:void(0);" onclick="javascript:booking_element_delete('resourcetype');"><img src="./modules/booking/img/ico_trash.png">Supprimer les �l�ments coch�s</a>
    <a href="javascript:void(0);" onclick="javascript:booking_element_add('resourcetype', event);"><img src="./modules/booking/img/ico_new.png">Ajouter un �l�ment</a>
</div>

<?php

$arrResult = 
    array(
        'columns' => array(),
        'rows' => array()
    );
    
$arrResult['columns']['auto']['name'] = 
    array(    
        'label' => 'Intitul�',
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['right']['count'] = 
    array(   
        'label' => 'Res.',
        'width' => 60,
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['right']['active'] = 
    array(   
        'label' => 'Actif',
        'width' => 60,
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['right']['workspace'] = 
    array( 
        'label' => 'Cr�� par',
        'width' => '150',
        'options' => array('sort' => true)
    );
    
$arrResult['columns']['actions_right']['actions'] = 
    array(
        'label' => '', 
        'width' => '24'
    );    
    
$db->query("
    SELECT      rt.*,
                w.label as w_label,
                IF(ISNULL(r.id), 0, count(*)) as c
                
    FROM        ploopi_mod_booking_resourcetype rt
    
    LEFT JOIN   ploopi_mod_booking_resource r
    ON          r.id_resourcetype = rt.id
    
    LEFT JOIN   ploopi_workspace w
    ON          w.id = rt.id_workspace
    
    WHERE       rt.id_module = {$_SESSION['ploopi']['moduleid']}
    
    GROUP BY    rt.id
");
    

while ($row = $db->fetchrow())
{
    $arrResult['rows'][] = 
        array(
            'values' => 
                array(
                    'name' => 
                        array(
                            'label' => ovensia\ploopi\str::htmlentities($row['name']),
                            'style' => ($row['active']) ? '' : 'color:#a60000;'
                        ),
                    'count' => array('label' => $row['c']),
                    'workspace' => array('label' => ovensia\ploopi\str::htmlentities($row['w_label'])),
                    'active' => array('label' => ($row['active']) ? 'oui' : 'non'),
                    'actions' => array('label' => ($row['c']) ? '&nbsp;' : '<input type="checkbox" class="booking_element_checkbox" value="'.$row['id'].'">')
                ),
            'description' => "Modifier le type de ressource '".ovensia\ploopi\str::htmlentities($row['name'])."'",
            'link' => 'javascript:void(0);',
            'onclick' => "booking_element_open('resourcetype', '{$row['id']}', event);"
        );
}

$skin->display_array(
    $arrResult['columns'], 
    $arrResult['rows'], 
    'booking_resourcetype', 
    array(
        'sortable' => true, 
        'orderby_default' => 'name',
        'sort_default' => 'ASC'
    )
);
?>
