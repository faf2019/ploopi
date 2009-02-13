<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Affichage des statistiques (notamment du moteur d'indexation)
 *
 * @package doc
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Affichage du bloc 'statistiques'
 */

echo $skin->open_simplebloc('Statistiques');
?>
<div class="doc_admin_titlebar"><b>Quelques chiffres</b></div>
<?php
/**
 * Premier tableau 'Quelques chiffres'
 */

$array_columns = array();
$array_values = array();

$array_columns['right']['nombre'] = array(  'label' => 'Nombre', 
                                            'width' => '100',
                                            'options' => array('sort' => true)
                                            );

$array_columns['auto']['indicateur'] = array(   'label' => 'Indicateur', 
                                        'options' => array('sort' => true)
                                        );

$c = 1;

/**
 * Recherche nombre de fichiers indexés
 */

$db->query("SELECT count(*) as nb FROM ploopi_mod_doc_file WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
$row = $db->fetchrow();

$nbfichier = $row['nb'];

$array_values[$c]['values']['indicateur']   = array('label' => 'Fichiers indexés', 'style' => '');
$array_values[$c]['values']['nombre']       = array('label' => $row['nb'], 'style' => '');
$array_values[$c]['description'] = 'Fichiers indexés';
$array_values[$c]['link'] = '';
$array_values[$c]['style'] = '';
$c++;

/**
 * Compte nombre de métas indexés
 */

$db->query( "
            SELECT  count(ke.id_keyword) as nb
            FROM    ploopi_index_element e,
                    ploopi_index_keyword_element ke
            
            WHERE   e.id_module = {$_SESSION['ploopi']['moduleid']}
            AND     e.id_object = "._DOC_OBJECT_FILE."
            AND     ke.id_element = e.id
            AND     ke.weight = 999999
            ");

$row = $db->fetchrow();

$array_values[$c]['values']['indicateur']   = array('label' => 'Metas indexés', 'style' => '');
$array_values[$c]['values']['nombre']       = array('label' => $row['nb'], 'style' => '');
$array_values[$c]['description'] = 'Metas indexés';
$array_values[$c]['link'] = '';
$array_values[$c]['style'] = '';
$c++;

/**
 * Compte nombre de mots indexés
 */

$db->query( "
            SELECT  sum(ke.weight) as total_weight
            FROM    ploopi_index_element e,
                    ploopi_index_keyword_element ke
            
            WHERE   e.id_module = {$_SESSION['ploopi']['moduleid']}
            AND     e.id_object = "._DOC_OBJECT_FILE."
            AND     ke.id_element = e.id
            AND     ke.weight <> 999999
            ");

$row = $db->fetchrow();

$total_weight = $row['total_weight'];

$array_values[$c]['values']['indicateur']   = array('label' => 'Mots indexés', 'style' => '');
$array_values[$c]['values']['nombre']       = array('label' => $row['total_weight'], 'style' => '');
$array_values[$c]['description'] = 'Mots indexés';
$array_values[$c]['link'] = '';
$array_values[$c]['style'] = '';
$c++;

$skin->display_array($array_columns, $array_values, 'docparser_stats', array('height' => 100));
?>

<div class="doc_admin_titlebar"><b>Mots les plus fréquents</b></div>
<?php
/**
 * Deuxième tableau 'Mots les plus fréquents'
 */

$array_columns = array();
$array_values = array();

$array_columns['left']['pos'] = array(  'label' => 'Pos.', 
                                        'width' => '60', 
                                        'options' => array('sort' => true)
                                        );

$array_columns['right']['pcent'] = array(   'label' => '%', 
                                            'width' => '50', 
                                            'options' => array('sort' => true)
                                            );

$array_columns['right']['poids'] = array(   'label' => 'Poids', 
                                            'width' => '100', 
                                            'options' => array('sort' => true)
                                            );

$array_columns['right']['taille'] = array(  'label' => 'Taille', 
                                            'width' => '100', 
                                            'options' => array('sort' => true)
                                            );

$array_columns['auto']['mot'] = array(  'label' => 'Mot', 
                                        'options' => array('sort' => true)
                                        );

/**
 * Recherche des mots les plus fréquents
 */                                     

$sql =  "
        SELECT  k.keyword,
                sum(ke.weight) as w
        
        FROM    ploopi_index_element e,
                ploopi_index_keyword_element ke,
                ploopi_index_keyword k
        
        WHERE   e.id_module = {$_SESSION['ploopi']['moduleid']}
        AND     e.id_object = "._DOC_OBJECT_FILE."
        AND     ke.id_element = e.id
        AND     ke.weight <> 999999
        AND     k.id = ke.id_keyword
            
        GROUP BY    k.id
        ORDER BY    w DESC
        LIMIT 0,50
        ";
        
$db->query($sql);

$c = 1;
while ($row = $db->fetchrow())
{
    $weight = ($total_weight == 0) ? 0 : number_format(($row['w']*100)/$total_weight,2);

    $array_values[$c]['values']['pos']  = array('label' => $c, 'style' => '');
    $array_values[$c]['values']['pcent']    = array('label' => $weight, 'style' => '');
    $array_values[$c]['values']['poids']    = array('label' => $row['w'], 'style' => '');
    $array_values[$c]['values']['mot']      = array('label' => $row['keyword'], 'style' => '');
    $array_values[$c]['values']['taille']       = array('label' => strlen($row['keyword']), 'style' => '');
    $array_values[$c]['description'] = "{$c} - {$row['keyword']}";
    $array_values[$c]['link'] = '';
    $array_values[$c]['style'] = '';
    $c++;
}

$skin->display_array($array_columns, $array_values, 'docparser_list', array('height' => 200, 'sortable' => true, 'orderby_default' => 'poids', 'sort_default' => 'DESC'));

echo $skin->close_simplebloc();
?>
