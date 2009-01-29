<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Fonctions, constantes, variables globales
 *
 * @package directory
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_DIRECTORY_MANAGE_GROUP',  1);
define ('_DIRECTORY_MANAGE_COMMON', 2);

define ('_DIRECTORY_ACTION_CONTACTS',         1);
define ('_DIRECTORY_ACTION_MANAGERS',         2);

define ('_DIRECTORY_OBJECT_HEADING',        1);

/**
 * Retourne l'ensemble des rubriques dans un tableau
 *
 * @return array tableau contenant les rubriques
 */

function directory_getheadings()
{
    global $db;

    $headings = 
        array(
            'list' => array(), 
            'tree' => array()
        );
        
    $result = $db->query("
        SELECT      * 
        FROM        ploopi_mod_directory_heading
        ORDER BY    id_heading, position
    ");
    
    while ($fields = $db->fetchrow($result)) 
    {
        $fields['parents'] = (isset($headings['list'][$fields['id_heading']])) ? "{$headings['list'][$fields['id_heading']]['parents']};{$fields['id_heading']}" : $fields['id_heading'];

        $headings['list'][$fields['id']] = $fields;
        $headings['tree'][$fields['id_heading']][] = $fields['id'];
    }
    
    return($headings);
}

/**
 * Retourne l'arbre des rubriques pour la méthode skin::display_treeview()
 *
 * @param array $rubriques les rubriques
 * @return array treeview
 * 
 * @see risques_getrubriques
 * @see skin::display_treeview
 */

function directory_gettreeview($headings = array())
{
    global $db;

    $treeview = 
        array(
            'list' => array(), 
            'tree' => array()
        );

    foreach($headings['list'] as $id => $fields)
    {
        $treeview['list'][$fields['id']] = 
            array(
                'id' => $fields['id'],
                'label' => $fields['label'],
                'description' => $fields['description'],
                'parents' => split(';', $fields['parents']),
                'node_link' => '',
                'node_onclick' => "ploopi_skin_treeview_shownode('{$fields['id']}', '".ploopi_queryencode("ploopi_op=directory_heading_detail&directory_heading_id={$fields['id']}")."', 'admin-light.php');",
                'link' => ploopi_urlencode("admin.php?directory_heading_id={$fields['id']}"),
                'onclick' => '',
                'icon' => './modules/directory/img/ico_heading.png'
            );
        
        $treeview['tree'][$fields['id_heading']][] = $fields['id'];                        
    }
    
    return($treeview);
}


?>
