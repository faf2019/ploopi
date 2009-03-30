<?php
/*
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
 * Point d'entrée pour la ligne de commande
 * Permet d'exécuter des opérations de maintenance
 * ex: ./cli module=doc op=reindex
 *
 * @package doc
 * @subpackage cli
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 */

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

ploopi_init_module('doc');

include_once './include/functions/search_index.php';
include_once './include/functions/filesystem.php';
include_once './include/functions/string.php';
include_once './modules/doc/class_docfile.php';

// Optimisation de la BDD
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword`");
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword_file`");

// Recherche des instances de modules de documents
$rs = $db->query("
    SELECT      pm.id
    
    FROM        ploopi_module_type pmt
    
    INNER JOIN  ploopi_module pm
    ON          pmt.id = pm.id_module_type
    
    WHERE   pmt.label = 'doc'
");

while ($row = $db->fetchrow($rs))
{
    
    $arrRecords = ploopi_search_get_records(_DOC_OBJECT_FILE, $row['id']);
    
    $sql = "
        SELECT  f.id, f.md5id
        FROM    ploopi_mod_doc_file f
        WHERE   id_module = {$row['id']}
    ";

    $rs_doc = $db->query($sql);
    
    $arrFiles = array();
    
    while($row_doc = $db->fetchrow($rs_doc))
    {
        $arrFiles[] = $row_doc['md5id'];
        $doc = new docfile();
        $doc->open($row_doc['id']);
        echo strip_tags($doc->parse());
        echo "script {$ploopi_timer}\n";
    }

    // Tableau des différences : contient les id de documents indexés mais qui n'existent plus
    $arrDiff = array_diff($arrRecords, $arrFiles);
    
    foreach($arrDiff as $id_record) ploopi_search_remove_index(_DOC_OBJECT_FILE, $id_record);
}

// Optimisation de la BDD
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword`");
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword_file`");
?>