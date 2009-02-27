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
 * Indexation des documents
 *
 * @package doc
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * On modifie la durée d'exécution maximum du script
 */

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

include_once './modules/doc/class_docfile.php';

$arrRecords = ploopi_search_get_records();

echo $skin->open_simplebloc('Indexation');
?>
<div style="padding:4px;">
<?php
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword`"); 
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword_file`"); 

$sql = "
    SELECT  f.id, f.md5id
    FROM    ploopi_mod_doc_file f
    WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
";

$res = $db->query($sql);

$arrFiles = array();

while($fields = $db->fetchrow($res))
{
    $arrFiles[] = $fields['md5id'];
    $doc = new docfile();
    $doc->open($fields['id']);
    echo $doc->parse();
    $doc->save();
}

// Tableau des différences : contient les id de documents indexés mais qui n'existent plus
$arrDiff = array_diff($arrRecords, $arrFiles);

foreach($arrDiff as $id_record) ploopi_search_remove_index(_DOC_OBJECT_FILE, $id_record);

$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword`"); 
$db->query("OPTIMIZE TABLE `ploopi_mod_doc_keyword_file`"); 
?>
</div>
<?php
echo $skin->close_simplebloc();
?>
