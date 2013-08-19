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
            
            $objThumb = new mimethumb(111,90,0,'png','transparent');
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
            
            $objThumb = new mimethumb(111,90,0,'png','transparent');
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
        
    // Multi Process Reindex
    case 'mpreindex':
        set_time_limit(0);

        include_once './modules/doc/class_docfile.php';

        // On lit les paramètres complémentaires (nom du fichier, p, m)
        if ($argc == 5) {
            $file = explode('=', $argv[3]);
            $limit = explode('=', $argv[4]);
            
            if ($file[0] == 'file' && isset($file[1]) && $limit[0] == 'limit' && isset($limit[1])) {

                // Fichier de communication
                $file = $file[1]; 
                // Limite de traitement des événements
                $limit = $limit[1];

                // Possible d'écrire dans le fichier ?
                if (is_writable($file)) {
                        
                    // Action
                    $rs = $db->query("SELECT id FROM ploopi_mod_doc_file LIMIT {$limit}");
                    $c = 0;
                    
                    while($row = $db->fetchrow($rs)) {

                        $objDocFile = new docfile();
                        $objDocFile->open($row['id']);
                        $objDocFile->parse();

                        // Ecriture de l'avancement
                        $handle = fopen($file, 'w+');
                        fwrite($handle, $c++);
                        fclose($handle);

                    }
                    
                    // Action terminée, suppression du fichier
                    unlink($file);
                }
            }
        }
    break;
        
    case 'reindex':
        set_time_limit(0);

        include_once './include/functions/system.php';

        // Nombre de processus à paralléliser
        $intNbProc = ploopi_getnbcore()*2;
        // Nombre d'enregistrement à traiter par processus
        $intPageSize = 50;
        // Sélection de l'ensemble des documents
        $rs = $db->query("SELECT id FROM ploopi_mod_doc_file");
        // Nombre d'éléments à traiter
        $intNbElt = $db->numrows();
        // Page de démarrage
        $intCurrentPage = 0;
        // Nombre de page traitées
        $intTermine = 0;
        // Nb Page Max
        $intNbPage = ceil($intNbElt/$intPageSize);

        // Taille de la barre de progression (affichage)
        $intSize = 45;
        
        // Paramètre proc
        foreach($argv as $arg) {
            $arg = explode('=', $arg);
            if ($arg[0] == 'proc' && isset($arg[1])) $intNbProc = max(1, intval($arg[1]));
        }

        $timer = new timer();
        $timer->start();

        printf("\n\n\033[1;33mIndexation des {$intNbElt} documents via {$intNbProc} processus\033[0m\n\n");
        
        $arrProcessus = array();
        
        $booFirst = true;
        
        // Pour chaque page à traiter (et tant qu'un processus encore actif
        while ($intCurrentPage < $intNbPage || !empty($arrProcessus)) {
        
            $booTermine = false;
        
            // Un processus est-il terminé ?
            foreach($arrProcessus as $p => $file) {
                if (!file_exists($file)) {
                    $intTermine++;
                    $booTermine = true;
                    // printf("\033[1;33mProcessus {$p} terminé\033[0m\n\n");
                    // Libération du processus
                    unset($arrProcessus[$p]);
                }
            }
            
            // Peut-on lancer de nouveaux processus ?
            for ($p = 1; $p <= $intNbProc; $p++) {
                // Encore des pages à traiter ?
                if ($intCurrentPage < $intNbPage) {
                    // Processus disponible ?
                    if (!isset($arrProcessus[$p])) {
                        // Création d'un fichier temporaire de communication
                        $arrProcessus[$p] = tempnam(sys_get_temp_dir(), 'doc');
                        if (is_writable(dirname($arrProcessus[$p]))) {
                            $handle = fopen($arrProcessus[$p], 'w');
                            fclose($handle);
                            
                            // Calcul de la limite à traiter
                            $intLimit = $intCurrentPage*$intPageSize;
                            
                            // On lance un processus sans attendre la fin d'exécution, il va écrire son statut dans le fichier $tmpfile
                            exec("{$argv[0]} module=doc op=mpreindex file={$arrProcessus[$p]} limit={$intLimit},{$intPageSize} >/dev/null 2>&1 &");
                            
                            // On passe à la page suivante
                            $intCurrentPage++;
                        }
                    }
                }
            }

            // Timer actuel arrondi
            $floCurTime = round($timer->getexectime(),1);

            $floPcent = $intTermine/$intNbPage;
            $intBarSize = round($floPcent*$intSize);

            if ($booTermine || $booFirst) {
                // Projection de la durée du calcul en seconde
                $intProjection = $floPcent ? round($floCurTime / $floPcent) : 0;
            }
            
            // Calcul de l'heure de fin en H:i:s
            $intProjH = floor($intProjection/3600);
            $intProjM = floor(($intProjection%3600)/60);
            $intProjS = floor($intProjection%60);
            
            echo "|".str_repeat("=", $intBarSize).str_repeat(" ", $intSize-$intBarSize)."| ".sprintf("%5s%% %ds (est:%2dh%02dm%02ds)\r", sprintf("%.01f", round($floPcent*100,1)), $floCurTime, $intProjH, $intProjM, $intProjS).$intNbPage;
            
            $booFirst = false;

            // Permet au script de ne pas occuper 100% du cpu pour rien
            // On va effectuer un contrôle par seconde uniquement
            sleep(1);
        }
        
        printf("\n\n\033[1;33mTerminé\033[0m\n\n");

    break;
   
}
?>