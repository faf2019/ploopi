<?php
/*
    Copyright (c) 2007-2009 Ovensia
    Copyright (c) 2009 HeXad
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

switch($op)
{
    case 'thumbnails':
        @include_once 'Cache/Lite.php';
        if(!class_exists('Cache_Lite')) return false;
        
        include_once './include/functions/filesystem.php';
        include_once './modules/doc/class_docfile.php';
        include_once './modules/doc/class_docfiledraft.php';
        include './include/classes/mimethumb.php';

        // Vignettes des fichiers
        $sql = 'SELECT md5id, name, version, id_workspace, id_module FROM ploopi_mod_doc_file';
        $sqlResult = $db->query($sql);
        while ($row = $db->fetchrow($sqlResult))
        {
            $objCache = new Cache_Lite(array( 'cacheDir' => _PLOOPI_PATHCACHE._PLOOPI_SEP, 'lifeTime' => 2592000)); // 30 jours
            
            $objDoc = new docfile();
            
            $objThumb = new mimethumb(111,90,'png','transparent');
            $objThumb->setIdmw($row['id_module'],$row['id_workspace']);
               
            if($objDoc->openmd5($row['md5id']))
            {
                ob_start();
                if($objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']))
                {
                    $content = ob_get_contents();
                    $objCache->save($content, md5('doc_thumb_'.$row['md5id'].'_'.$row['version']),'module_doc_'.$row['id_workspace'].'_'.$row['id_module']);
                    /*
                    // Pour tests
                    $file = fopen(_PLOOPI_PATHCACHE._PLOOPI_SEP.$row['name'].'.png','w');
                    fwrite($file,$content);
                    fclose($file);
                    */
                }
                ob_end_clean();
            }
            unset($objCache);
        }        

        // Vignettes des fichiers DRAFT !
        $sql = "SELECT md5id, name, id_workspace, id_module FROM ploopi_mod_doc_file_draft";
        $sqlResult = $db->query($sql);
        while ($row = $db->fetchrow($sqlResult))
        {
            $objCache = new Cache_Lite(array( 'cacheDir' => _PLOOPI_PATHCACHE._PLOOPI_SEP, 'lifeTime' => 2592000)); // 30 jours
            
            $objDocDraft = new docfiledraft();
            
            $objThumb = new mimethumb(111,90,'png','transparent');
            $objThumb->setIds($row['id_module'],$row['id_workspace']);
               
            if($objDocDraft->openmd5($row['md5id']))
            {
                ob_start();
                if($objThumb->getThumbnail($objDocDraft->getfilepath(),$objDocDraft->fields['extension']))
                {
                    $objCache->save(ob_get_contents(), md5('doc_thumb_'.$row['md5id'].'_draft'),'module_doc_'.$row['id_workspace'].'_'.$row['id_module']);
                }
                ob_end_clean();
            }
            unset($objCache);
        }        
        break;
        
    case 'reindex':
    default:
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
        break;
}
?>