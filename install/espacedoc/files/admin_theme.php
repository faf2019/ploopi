<?php
/**
 * Administration / Gestion des themes
 * 
 * @package espacedoc
 * @subpackage admin
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */
?>

<div class="espacedoc_titre">
<h1>Gestion des <? echo ploopi_getparam('espacedoc_theme'); ?>s</h1>
</div>
<div class="espacedoc_toolbar">
    <em style="float:left;color:#a60000;margin-left:4px;">En rouge, les éléments inactifs</em>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_cocher();"><img src="./modules/espacedoc/img/ico_checkbox.png">Cocher/Décocher tout</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_supprimer('theme');"><img src="./modules/espacedoc/img/ico_trash.png">Supprimer les éléments cochés</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_ajouter('theme', event);"><img src="./modules/espacedoc/img/ico_new.png">Ajouter un élément</a>
</div>

<?
$array_columns = array();
$array_values = array();

    
$array_columns['auto']['libelle'] = 
    array(   
        'label' => 'Libellé',
        'options' => array('sort' => true)
    );

$array_columns['right']['actif'] = 
    array(   
        'label' => 'Actif',
        'width' => '60',
        'options' => array('sort' => true)
    );
    
$array_columns['right']['documents'] = 
    array(   
        'label' => 'Documents',
        'width' => '100',
        'options' => array('sort' => true)
    );
    
$array_columns['actions_right']['actions'] = 
    array(
        'label' => '', 
        'width' => '24'
    );

$sql =  "
        SELECT      t.*, 
                    if(isnull(d.id), 0, count(*)) as c
        
        FROM        ploopi_mod_espacedoc_theme t
        
        LEFT JOIN   ploopi_mod_espacedoc_document d
        ON          d.id_theme = t.id
        
        GROUP BY    t.id
        
        ORDER BY    t.libelle
        ";

$db->query($sql);

$c = 1;
while ($row = $db->fetchrow())
{
    $array_values[$c]['values']['libelle'] = 
        array(
            'label' => $row['libelle'],
            'style' => ($row['actif']) ? '' : 'color:#a60000;'
        );
        
    $array_values[$c]['values']['documents'] =
        array(
            'label' => $row['c']
        );
        
    $array_values[$c]['values']['actif'] =
        array(
            'label' => ($row['actif']) ? 'oui' : 'non'
        );
    
    $array_values[$c]['values']['actions'] =
        array(
            'label' => ($row['c']) ? '&nbsp;' : '<input type="checkbox" class="espacedoc_element_checkbox" value="'.$row['id'].'">'
        );
        
    $array_values[$c]['description'] = 'Modifier cet élément';
    $array_values[$c]['link'] = 'javascript:void(0);';
    $array_values[$c]['onclick'] = "espacedoc_element_modifier('theme', {$row['id']}, event);";
    $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($array_columns, $array_values, 'espacedoc_theme', array('height' => 400, 'sortable' => true, 'orderby_default' => 'libelle', 'sort_default' => 'ASC'));
?>
