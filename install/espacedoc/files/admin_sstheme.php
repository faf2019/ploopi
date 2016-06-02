<?php
/**
 * Administration / Gestion des sous-th�mes
 * 
 * @package espacedoc
 * @subpackage admin
 * @author St�phane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */
?>

<div class="espacedoc_titre">
<h1>Gestion des <? echo ploopi_getparam('espacedoc_sstheme'); ?>s</h1>
</div>
<div class="espacedoc_toolbar">
    <em style="float:left;color:#a60000;margin-left:4px;">En rouge, les �l�ments inactifs</em>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_cocher();"><img src="./modules/espacedoc/img/ico_checkbox.png">Cocher/D�cocher tout</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_supprimer('sstheme');"><img src="./modules/espacedoc/img/ico_trash.png">Supprimer les �l�ments coch�s</a>
    <a href="javascript:void(0);" onclick="javascript:espacedoc_element_ajouter('sstheme', event);"><img src="./modules/espacedoc/img/ico_new.png">Ajouter un �l�ment</a>
</div>

<?
$array_columns = array();
$array_values = array();

$array_columns['left']['libelle_th'] = 
    array(   
        'label' => ploopi_getparam('espacedoc_theme'),
        'width' => 250,
        'options' => array('sort' => true)
    );

$array_columns['auto']['libelle'] = 
    array(   
        'label' => ploopi_getparam('espacedoc_sstheme'),
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

$db->query( "
            SELECT      sth.*, 
                        th.actif as actif_th,
                        th.libelle as libelle_th,
                        if(isnull(d.id), 0, count(*)) as c
            
            FROM        ploopi_mod_espacedoc_sstheme sth
            
            LEFT JOIN   ploopi_mod_espacedoc_theme th
            ON          sth.id_theme = th.id
            
            LEFT JOIN   ploopi_mod_espacedoc_document d
            ON          d.id_sstheme = sth.id
            
            GROUP BY    sth.id
            
            ORDER BY    sth.libelle, libelle_th
            ");

$c = 1;
while ($row = $db->fetchrow())
{
    if (is_null($row['libelle_th'])) 
    {
        $label = '(rattachement supprim�)';
    }
    else
    {
        $label = $row['libelle_th'];
        if (!$row['actif_th']) $label .= ' (inactif)';
    }
    
    $array_values[$c]['values']['libelle_th'] =
        array(
            'label' => $label,
            'style' => ($row['actif_th']) ? '' : 'color:#a60000;'
        );

        
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

    $array_values[$c]['description'] = 'Modifier cet �l�ment';
    $array_values[$c]['link'] = 'javascript:void(0);';
    $array_values[$c]['onclick'] = "espacedoc_element_modifier('sstheme', {$row['id']}, event);";
    $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($array_columns, $array_values, 'espacedoc_sstheme', array('height' => 400, 'sortable' => true, 'orderby_default' => 'libelle_th', 'sort_default' => 'ASC'));
?>