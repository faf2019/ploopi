<?php
/**
 * Administration / Gestion des modèles de documents
 * 
 * @package espacedoc
 * @subpackage admin
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

?>

<div class="espacedoc_titre">
<h1>Gestion des modèles de documents</h1>
</div>
<div class="espacedoc_toolbar">
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_cocher();"><img src="./modules/espacedoc/img/ico_checkbox.png">Cocher/Décocher tout</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_supprimer('modele_document');"><img src="./modules/espacedoc/img/ico_trash.png">Supprimer les éléments cochés</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_ajouter('modele_document', event, 600);"><img src="./modules/espacedoc/img/ico_new.png">Ajouter un élément</a>
</div>

<?
$array_columns = array();
$array_values = array();


$array_columns['left']['type'] = 
    array(   
        'label' => 'Type/Ref',
        'width' => '150',
        'options' => array('sort' => true)
    );
                                                
$array_columns['left']['libelle'] = 
    array(   
        'label' => 'Intitulé',
        'width' => '350',
        'options' => array('sort' => true)
    );
                                                
$array_columns['auto']['fichier'] = 
    array(   
        'label' => 'Fichier',
        'options' => array('sort' => true)
    );

$array_columns['actions_right']['actions'] = array('label' => '', 'width' => '24');

$db->query( "
            SELECT      *
            FROM        ploopi_mod_espacedoc_modele_document
            ORDER BY    type
            ");

$c = 1;

while ($row = $db->fetchrow())
{
    $array_values[$c]['values']['type']      = array('label' => $row['type']);
    $array_values[$c]['values']['fichier']      = array('label' => $row['fichier']);
    $array_values[$c]['values']['libelle']      = array('label' => $row['libelle']);
    
    $array_values[$c]['values']['actions']      = array('label' => '<input type="checkbox" class="espacedoc_element_checkbox" value="'.$row['id'].'">');
    $array_values[$c]['description'] = 'Modifier cet élément';
    $array_values[$c]['link'] = 'javascript:void(0);';
    $array_values[$c]['onclick'] = "espacedoc_element_modifier('modele_document', {$row['id']}, event, 600);";
    $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($array_columns, $array_values, 'espacedoc_modele_document', array('height' => 400, 'sortable' => true, 'orderby_default' => 'type', 'sort_default' => 'ASC'));
?>
