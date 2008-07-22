<?php
/*
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
 * Fonctions de recherche et d'indexation de contenu.
 * Extraction des mots clés d'un texte, gestion des METAs, mise en valeur des mots clés recherchés, suppression des mots communs.
 * Utilisation de la technique de lemmisation (racinisation).
 * 
 * @see index_element
 * @see index_keyword
 * @see index_keyword_element
 * @see index_stem
 * @see index_stem_element
 *
 * @link http://pecl.php.net/package/stem
 * 
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/search_index.php';

/**
 * Génération d'un identifiant unique pour un enregistrement d'un objet
 *
 * @param int $id_module identifiant du module
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @return string identifiant unique de l'enregistrement (hash MD5, 32 caractères)
 * 
 * @see md5
 */

function ploopi_search_generate_id($id_module, $id_object, $id_record)
{
    return(md5(sprintf("%04d%04d%s", $id_module, $id_object, $id_record)));
}

/**
 * Supprime l'index (mots clés) associé à un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module (optionnel)
 */

function ploopi_search_remove_index($id_object, $id_record, $id_module = -1)
{
    global $db;

    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];
    $id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

    $db->query("DELETE FROM ploopi_index_element WHERE id = '{$id_element}'");
    $db->query("DELETE FROM ploopi_index_keyword_element WHERE id_element = '{$id_element}'");
    $db->query("DELETE FROM ploopi_index_stem_element WHERE id_element = '{$id_element}'");
}

/**
 * Alimente l'index d'un enregistrement d'un objet avec les annotation liées
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param string $tags chaîne contenant les mots clés de l'annotation
 * @param int $id_module identifiant du module (optionnel)
 */

function ploopi_search_create_index_annotation($id_object, $id_record, $tags, $id_module = -1)
{
    global $db;
    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

    $id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

    list($arrKeywords) = ploopi_getwords($tags, true, false);

    foreach($arrKeywords as $kw)
    {
        // on calcule le hash md5
        $kw_md5 = md5($kw);

        // enregistrement du mot clé si n'existe pas déjà
        $db->query("SELECT id FROM ploopi_index_keyword WHERE id = '{$kw_md5}'");
        if (!$db->numrows())
        {
            $objKw = new index_keyword();
            $objKw->fields['id'] = $kw_md5;
            $objKw->fields['keyword'] = $kw;
            $objKw->fields['id_stem'] = $stem_md5;
            $objKw->save();
        }

        // enregistrement du lien mot clé <-> enregistrement
        $objKwElement = new index_keyword_element();
        $objKwElement->fields['id_keyword'] = $kw_md5;
        $objKwElement->fields['id_element'] = $id_element;
        $objKwElement->fields['weight'] = $kw_weight;
        $objKwElement->fields['ratio'] = (empty($kw['meta'])) ? ((empty($words_overall)) ? 0 : ($kw_weight / $words_overall)*100) : 1;
        $objKwElement->fields['relevance'] = (empty($kw['meta'])) ? ($kw_weight*100)/$max_weight : 100;
        $objKwElement->save();

    }
}

/**
 * Création de l'index d'un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param string $label libellé de l'objet
 * @param string $content contenu de l'objet à indéxer
 * @param string $meta chaîne contenant des METAs informations (le poids accordé sera maximal)
 * @param boolean $usecommonwords true si la liste des mots communs doit être utilisée (les mots communs seront dans ce cas retirés) 
 * @param int $timestp_create date/heure de création au format timestamp MYSQL 
 * @param int $timestp_modify date/heure de modification au format timestamp MYSQL
 * @param int $id_user identifiant de l'utilisateur
 * @param int $id_workspace identifiant de l'espace
 * @param int $id_module identifiant du module
 * @param boolean $debug true si le mode 'debug' est activé
 * 
 * @see index_element
 * @see index_keyword
 * @see index_keyword_element
 * @see index_stem
 * @see index_stem_element
 * 
 * @see _PLOOPI_INDEXATION_COMMONWORDS_FR
 * @see _PLOOPI_INDEXATION_WORDSEPARATORS
 * @see _PLOOPI_INDEXATION_WORDMINLENGHT
 * @see _PLOOPI_INDEXATION_WORDMAXLENGHT
 * @see _PLOOPI_INDEXATION_METAWEIGHT
 * @see _PLOOPI_INDEXATION_KEYWORDSMAXPCENT
 * 
 * @see ploopi_search_generate_id
 * @see ploopi_convertaccents
 * 
 * @link http://pecl.php.net/package/stem
 */

function ploopi_search_create_index($id_object, $id_record, $label, $content, $meta = '', $usecommonwords = true, $timestp_create = 0, $timestp_modify = 0, $id_user = -1, $id_workspace = -1, $id_module = -1, $debug = false)
{
    global $db;
    global $ploopi_timer;

    if ($id_user == -1 && !empty($_SESSION['ploopi']['userid'])) $id_user = $_SESSION['ploopi']['userid'];
    if ($id_workspace == -1 && !empty($_SESSION['ploopi']['workspaceid'])) $id_workspace = $_SESSION['ploopi']['workspaceid'];
    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

    $id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

    $words = array();
    $words_indexed = $words_overall = 0;

    if ($usecommonwords && !isset($_SESSION['ploopi']['commonwords']) && file_exists(_PLOOPI_INDEXATION_COMMONWORDS_FR) )
    {
        $filecontent = '';
        $handle = @fopen(_PLOOPI_INDEXATION_COMMONWORDS_FR, 'r');
        if ($handle)
        {
            while (!feof($handle)) $filecontent .= fgets($handle);
            fclose($handle);
        }

        $_SESSION['ploopi']['commonwords'] = array_flip(split("[\n]", str_replace("\r",'',$filecontent)));
    }

    if (empty($_SESSION['ploopi']['commonwords'])) $_SESSION['ploopi']['commonwords'] = array();


    // TRAITEMENT DU CONTENT
    for ($kw = strtok($content, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
    {
        // mot en minuscule avec accents
        $kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

        // mot en minuscule sans accent et sans caractère parasite
        $kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

        // on vérifie qu'il n'est pas dans la liste des mots à exclure
        if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
        {
            // on vérifie sa taille
            $len = strlen($kw);
            if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
            {
                // détermination du lemme (racine)
                $kw_stem = stem_french($kw);

                if (!isset($words[$kw_stem])) $words[$kw_stem] = array('weight' => 1, 'meta' => 0);
                else $words[$kw_stem]['weight']++;

                if (!isset($words[$kw_stem]['words'][$kw_clean])) $words[$kw_stem]['words'][$kw_clean] = array('weight' => 1, 'meta' => 0);
                else $words[$kw_stem]['words'][$kw_clean]['weight']++;

                $words_indexed++;
            }
        }
        $words_overall++;
    }

    // tri des lemmes par poids
    arsort($words);

    $stem = current($words);
    $max_weight = $stem['weight'];

    // TRAITEMENT DES METAS
    for ($kw = strtok($meta, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
    {
        // mot en minuscule avec accents
        $kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

        // mot en minuscule sans accent et sans caractère parasite
        $kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

        // on vérifie qu'il n'est pas dans la liste des mots à exclure
        if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
        {
            // on vérifie sa taille
            $len = strlen($kw);
            if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
            {
                // détermination du lemme (racine)
                $kw_stem = stem_french($kw);

                $words[$kw_stem]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
                $words[$kw_stem]['meta'] = 1;

                $words[$kw_stem]['words'][$kw_clean]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
                $words[$kw_stem]['words'][$kw_clean]['meta'] = 1;
            }
        }
    }

    // tri des lemmes par poids
    arsort($words);

    if ($debug) printf("<br />GETWORDS: %0.2f",$ploopi_timer->getexectime()*1000);

    // nettoyage index
    ploopi_search_remove_index($id_object, $id_record);
    if ($debug) printf("<br />REMOVE INDEX: %0.2f",$ploopi_timer->getexectime()*1000);


    $stem = current($words);
    $stem_ratio = (empty($words_overall)) ? 1 : ($stem['weight']*100 / $words_overall);

    $max_stem = (_PLOOPI_INDEXATION_KEYWORDSMAXPCENT) ? (sizeof($words)*_PLOOPI_INDEXATION_KEYWORDSMAXPCENT)/100 : sizeof($words);

    $objElement = new index_element();
    $objElement->fields['id'] = $id_element;
    $objElement->fields['id_object'] = $id_object;
    $objElement->fields['id_record'] = $id_record;
    $objElement->fields['label'] = $label;
    $objElement->fields['id_user'] = $id_user;
    $objElement->fields['id_workspace'] = $id_workspace;
    $objElement->fields['id_module'] = $id_module;
    $objElement->fields['timestp_create'] = $timestp_create;
    $objElement->fields['timestp_modify'] = $timestp_modify;
    $objElement->fields['timestp_lastindex'] = ploopi_createtimestamp();
    $objElement->save();

    for ($i = 1; ($i <= $max_stem && $stem_ratio >= _PLOOPI_INDEXATION_RATIOMIN) || $stem['meta']; $i++)
    {
        // on calcule le hash md5
        $stem_value = key($words);
        $stem_md5 = md5($stem_value);

        // enregistrement de la racine si n'existe pas déjà
        $db->query("SELECT id FROM ploopi_index_stem WHERE id = '{$stem_md5}'");
        if (!$db->numrows())
        {
            $objStem = new index_stem();
            $objStem->fields['id'] = $stem_md5;
            $objStem->fields['stem'] = $stem_value;
            $objStem->save();
        }

        // enregistrement du lien racine <-> enregistrement
        $objStemElement = new index_stem_element();
        if (!$objStemElement->open($stem_md5, $id_element))
        {
            $objStemElement->fields['id_stem'] = $stem_md5;
            $objStemElement->fields['id_element'] = $id_element;
            $objStemElement->fields['weight'] = $stem['weight'];
            $objStemElement->fields['ratio'] = (empty($stem['meta']) && $stem_ratio<1) ? $stem_ratio : 1;
            $objStemElement->fields['relevance'] = (empty($stem['meta'])) ? ($stem['weight']*100)/$max_weight : 100;
            $objStemElement->save();
        }

        foreach($stem['words'] as $kw_value => $kw)
        {
            $kw_weight = $kw['weight'];
            $kw_ratio = (empty($words_overall)) ? 1 : ($kw['weight']*100 / $words_overall);

            // on calcule le hash md5
            $kw_md5 = md5($kw_value);

            // enregistrement du mot clé si n'existe pas déjà
            $db->query("SELECT id FROM ploopi_index_keyword WHERE id = '{$kw_md5}'");
            if (!$db->numrows())
            {
                $objKw = new index_keyword();
                $objKw->fields['id'] = $kw_md5;
                $objKw->fields['keyword'] = $kw_value;
                $objKw->fields['id_stem'] = $stem_md5;
                $objKw->save();
            }

            // enregistrement du lien mot clé <-> enregistrement
            $objKwElement = new index_keyword_element();
            if (!$objKwElement->open($kw_md5, $id_element))
            {
                $objKwElement->fields['id_keyword'] = $kw_md5;
                $objKwElement->fields['id_element'] = $id_element;
                $objKwElement->fields['weight'] = $kw['weight'];
                $objKwElement->fields['ratio'] = (empty($kw['meta']) && $kw_ratio<1) ? $kw_ratio : 1;
                $objKwElement->fields['relevance'] = (empty($kw['meta'])) ? ($kw_weight*100)/$max_weight : 100;
                $objKwElement->save();
            }
        }

        $stem = next($words);
        $stem_ratio = (empty($words_overall)) ? 1 : ($stem['weight'] / $words_overall)*100;
    }

    if ($debug) printf("<br />KEYWORDS: %0.2f",$ploopi_timer->getexectime()*1000);
}

/**
 * Renvoie l'index associé à un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $limit nombre de lignes renvoyées
 * @param int $id_module identifiant du module (optionnel)
 * @return array tableau contenant l'index de l'enregistrement
 * 
 * @see ploopi_search_generate_id
 */

function ploopi_search_get_index($id_object, $id_record, $limit = 100, $id_module = -1)
{
    global $db;

    $index = array();

    if (is_numeric($limit))
    {
        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

        $id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

        $sql =  "
                SELECT      k.keyword, ke.weight, ke.ratio, s.stem, se.relevance

                FROM        ploopi_index_keyword_element ke

                INNER JOIN  ploopi_index_keyword k
                ON          ke.id_keyword = k.id

                INNER JOIN  ploopi_index_stem s
                ON          s.id = k.id_stem

                INNER JOIN  ploopi_index_stem_element se
                ON          se.id_stem = s.id
                AND         se.id_element = '{$id_element}'

                WHERE       ke.id_element = '{$id_element}'

                ORDER BY    se.relevance DESC, ke.weight DESC, k.keyword

                LIMIT       0,{$limit}
            ";

        $db->query($sql);
        $index = $db->getarray();
    }

    return($index);
}

/**
 * Effectue une recherche d'un ou plusieurs mots dans l'index
 *
 * @param string $keywords mots clés recherchés
 * @param int $id_object identifiant de l'objet recherché (optionnel)
 * @param string $id_record tableau d'enregistrement ou masque d'enregistrement recherché, recherche de type abc% (optionnel)
 * @param mixed $id_module identifiant du module ou tableau d'idenfiants de modules (optionnel)
 * @param array $options tableau des options de recherche : 'orderby', 'sort', 'limit' (optionnel)
 * @return array tableau contenant le résultat de la recherche
 */

function ploopi_search($keywords, $id_object = -1, $id_record = null, $id_module = null, $options = null)
{
    global $db;
    
    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module = $_SESSION['ploopi']['moduleid'];

    // on récupère la liste des racines contenues dans la liste des mots clés
    list($arrStems) = ploopi_getwords($keywords, true, true);

    // on récupère la liste des mots contenus dans la liste des mots clés
    list($arrKeywords) = ploopi_getwords($keywords, true, false);

    $arrSearch = array();
    $arrRelevance = array();
    $arrElements = array();

    if (!empty($id_record))
    {
        if (is_array($id_record)) 
        {
            $arrIdRecord = array();
            foreach($id_record as $rec) $arrIdRecord[] = "'".$db->addslashes($rec)."'";
            
            $arrSearch[] = "e.id_record IN (".implode(',', $arrIdRecord).")";
        }
        elseif ($id_record != '') $arrSearch[] = "e.id_record LIKE '".$db->addslashes($id_record)."%'";
    }
    
    if ($id_object != -1) $arrSearch[] = "e.id_object = {$id_object}";
    
    if (!empty($id_module))
    {
        if (is_array($id_module)) $arrSearch[] = "e.id_module IN (".implode(',', $id_module).")";
        else $arrSearch[] = "e.id_module = {$id_module}";
        //$arrSearch[] = 'e.id_workspace IN ('.ploopi_viewworkspaces($id_module).')';
    }

    $strSearch = (empty($arrSearch)) ? '' : ' AND '.implode(' AND ', $arrSearch);

    $orderby = (empty($options['orderby'])) ? 'relevance' : $options['orderby'];
    $sort = (isset($options['sort'])) ? $options['sort'] : 'DESC';
    
    $limit = (isset($options['limit'])) ? $options['limit'] : 200;

    // pour chaque racine (stem), on cherche les occurences d'éléments correspondants
    foreach($arrStems as $stem => $occ)
    {
        $id_stem = md5($stem);
        
        $towl = substr($stem,0,2);

        $sql =  "
                SELECT      e.*,
                            se.relevance
                            
                FROM        ploopi_index_stem_element se,
                            ploopi_index_element e

                WHERE       e.id = se.id_element
                AND         se.id_stem = '{$id_stem}'
                {$strSearch}

                ORDER BY {$orderby} {$sort}
                ";
                
        $db->query($sql);
        
        while ($row = $db->fetchrow())
        {
            $id = ($id_module != '') ? $row['id_record'] : $row['id'];
            
            $arrElements[$id] = $row;
            $arrRelevance[$id]['relevance'] = $row['relevance'];
            $arrRelevance[$id]['count'] = 1;
            $arrRelevance[$id]['kw'] = array();
            $arrRelevance[$id]['stem'] = array($stem => 1); 
        }
    }

    
    // pour chaque mot, on cherche les occurences d'éléments correspondants
    foreach($arrKeywords as $kw => $occ)
    {
        $sql =  "
                SELECT      e.*,
                            ke.relevance,
                            k.keyword

                FROM        ploopi_index_keyword k,
                            ploopi_index_keyword_element ke,
                            ploopi_index_element e

                WHERE       e.id = ke.id_element
                AND         k.id = ke.id_keyword
                AND         k.keyword like '".$db->addslashes($kw)."%'
                {$strSearch}
                
                ORDER BY {$orderby} {$sort}
                ";
                
        $db->query($sql);

        while ($row = $db->fetchrow())
        {
            $id = ($id_module != '') ? $row['id_record'] : $row['id'];

            // relevance = relevance * ratio de similarité entre les 2 chaines
            $row['relevance'] *= (strlen($kw)/strlen($row['keyword']));

            if (!isset($arrElements[$id]))
            {
                $arrElements[$id] = $row;
                $arrRelevance[$id]['relevance'] = $row['relevance'];
                $arrRelevance[$id]['count'] = 1;
                $arrRelevance[$id]['kw'] = array();
                $arrRelevance[$id]['stem'] = array(); 
            }
            else
            {
                $arrRelevance[$id]['relevance'] += $row['relevance'];
                $arrRelevance[$id]['count'] ++;
            }

            $arrRelevance[$id]['kw'][$kw] = 1;
        }

        
        // recherche dans les annotation de l'utilisateur (considéré comme meta ?)
        $sql =  "
                SELECT      e.*,
                            100 as relevance,
                            t.tag_clean as keyword

                FROM        ploopi_annotation a,
                            ploopi_annotation_tag at,
                            ploopi_tag t,
                            ploopi_index_element e

                WHERE       at.id_tag = t.id
                AND         at.id_annotation = a.id
                AND         a.id_element = e.id
                AND         t.tag_clean like '".$db->addslashes($kw)."%'
                
                {$strSearch}
                
                ORDER BY {$orderby} {$sort}
                ";

        $db->query($sql);

        while ($row = $db->fetchrow())
        {
            $id = ($id_module != '') ? $row['id_record'] : $row['id'];

            // relevance = relevance * ratio de similarité entre les 2 chaines
            $row['relevance'] *= (strlen($kw)/strlen($row['keyword']));

            if (!isset($arrElements[$id]))
            {
                $arrElements[$id] = $row;
                $arrRelevance[$id]['relevance'] = $row['relevance'];
                $arrRelevance[$id]['count'] = 1;
                $arrRelevance[$id]['kw'] = array();
                $arrRelevance[$id]['stem'] = array(); 
            }
            else
            {
                $arrRelevance[$id]['relevance'] += $row['relevance'];
                $arrRelevance[$id]['count'] ++;
            }

            $arrRelevance[$id]['kw'][$kw] = 1;
        }

    }
    
    foreach($arrRelevance as $key => $element)
    {
        $arrRelevance[$key]['kw_ratio'] = (sizeof($arrRelevance[$key]['kw'])+sizeof($arrRelevance[$key]['stem'])) / (sizeof($arrKeywords)+sizeof($arrStems));
        $arrRelevance[$key]['relevance'] = ($arrRelevance[$key]['relevance']/$arrRelevance[$key]['count']) * $arrRelevance[$key]['kw_ratio'];
    }
    
    // tri du résultat en fonction du champ et de l'ordre
    $compare_sign = ($sort == 'DESC') ? '>' : '<';
    uasort($arrRelevance, create_function('$a,$b', 'return $b[\''.$orderby.'\'] '.$compare_sign.' $a[\''.$orderby.'\'];'));
    
    $arrResult = array();
    
    $c = 0;
    while (current($arrRelevance) !== false && $c++ < $limit)
    {
        $arrResult[key($arrRelevance)] = array_merge($arrElements[key($arrRelevance)], $arrRelevance[key($arrRelevance)]);
        next($arrRelevance);
    }
    
    return($arrResult);
}

/**
 * Extrait les mots clés ou racines d'un texte
 *
 * @param string $content contenu du texte à analyser
 * @param boolean $usecommonwords true si la liste des mots communs doit être utilisée.
 * @param boolean $getstem true si la méthode de lemmisation/racinisation doit être utilisée
 * @param boolean $sort true si le résultat doit être trié par occurence d'apparition du mot 
 * @return tableau de mots clés ou de racines
 * 
 * @see _PLOOPI_INDEXATION_COMMONWORDS_FR
 * @see _PLOOPI_INDEXATION_WORDSEPARATORS
 * @see _PLOOPI_INDEXATION_WORDMINLENGHT
 * @see _PLOOPI_INDEXATION_WORDMAXLENGHT
 * 
 * @see ploopi_convertaccents
 * 
 * @link http://pecl.php.net/package/stem
 */

function ploopi_getwords($content, $usecommonwords = true, $getstem = false, $sort = true)
{
    $words = array();
    $words_indexed = $words_overall = 0;

    if ($usecommonwords && !isset($_SESSION['ploopi']['commonwords']) && file_exists(_PLOOPI_INDEXATION_COMMONWORDS_FR) )
    {
        $filecontent = '';
        $handle = @fopen(_PLOOPI_INDEXATION_COMMONWORDS_FR, 'r');
        if ($handle)
        {
            while (!feof($handle)) $filecontent .= fgets($handle);
            fclose($handle);
        }

        $_SESSION['ploopi']['commonwords'] = array_flip(split("[\n]", str_replace("\r",'',$filecontent)));
    }

    if (empty($_SESSION['ploopi']['commonwords'])) $_SESSION['ploopi']['commonwords'] = array();

    for ($kw = strtok($content, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
    {
        // remove empty characters
        $kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

        // only keep "normal" characters
        $kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

        if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
        {
            $len = strlen($kw_clean);
            if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
            {
                $kw = ($getstem) ? stem_french($kw) : $kw_clean;

                if (!isset($words[$kw])) $words[$kw] = 1;
                else $words[$kw]++;

                $words_indexed++;
            }
        }
        $words_overall++;
    }

    if ($sort) arsort($words);

    return(array($words, $words_indexed, $words_overall));
}

/**
 * Met en valeur les mots recherchés dans un texte et génère des extraits.
 * Grandement inspiré du code de phpdig.
 *
 * @param string $content contenu du texte
 * @param string $words mots recherchés
 * @param int $snippet_length longueur de l'extrait
 * @param int $snippet_num nombre d'extraits
 * @param string $highlight_class classe css utilisée pour la mise en valeur des mots
 * @return string extraits avec les mots clés
 */

function ploopi_highlight($content, $words, $snippet_length = 150, $snippet_num = 3, $highlight_class = 'ploopi_highlight')
{
    // on calcule l'encodeur
    $string_subst = 'A:ÀÁÂÃÄÅ,a:àáâãäå,O:ÒÓÔÕÖØ,o:òóôõöø,E:ÈÉÊË,e:èéêë,C:Ç,c:ç,I:ÌÍÎÏ,i:ìíîï,U:ÙÚÛÜ,u:ùúûü,Y:Ý,y:ÿý,N:Ñ,n:ñ';

    $arrEncoder = array();

    $tempArray = explode(',',$string_subst);

    $arrEncoder['str'] = '';
    $arrEncoder['tr'] = '';
    $arrEncoder['char'] = array();
    $arrEncoder['ereg'] = array();

    foreach ($tempArray as $tempSubstitution)
    {
        $chrs = explode(':',$tempSubstitution);
        $arrEncoder['char'][strtolower($chrs[0])] = strtolower($chrs[0]);
        settype($arrEncoder['ereg'][strtolower($chrs[0])],'string');
        $arrEncoder['ereg'][strtolower($chrs[0])] .= $chrs[0].$chrs[1];
        for($i=0; $i < strlen($chrs[1]); $i++)
        {
            $arrEncoder['str'] .= $chrs[1][$i];
            $arrEncoder['tr']  .= $chrs[0];
        }
    }
    foreach($arrEncoder['ereg'] as $id => $ereg)
    {
        $arrEncoder['ereg'][$id] = '['.$ereg.']';
    }

    $string = str_replace('\\','',implode('@#@',$words));

    $string = str_replace('Æ','ae',str_replace('æ','ae',$string));
    $string = strtr($string, $arrEncoder['str'], $arrEncoder['tr']);

    $string = preg_quote(strtolower($string));
    $string = str_replace($arrEncoder['char'],$arrEncoder['ereg'],$string);

    $reg_strings = str_replace('@#@','|', $string);
    $stop_regs = "[][(){}[:blank:]=&?!&#%\$£*@+%:;,/\.'\"]";
    $reg_strings = "({$stop_regs}{1}|^)({$reg_strings})()";

    $num_extracts = 0;
    $c = 0;
    $my_extract_size = $snippet_length;
    $extract = '';

    $content_size = strlen($content);

    while (($num_extracts == 0) && ($my_extract_size <= $content_size))
    {
        while($num_extracts < $snippet_num && $extract_content = preg_replace("/([ ]{2,}|\n|\r|\r\n)/"," ",substr($content, $c*$snippet_length, $snippet_length)))
        {
            if(eregi($reg_strings,$extract_content))
            {
                $match_this_spot = eregi_replace($reg_strings,"\\1<\\2>\\3",$extract_content);
                $first_bold_spot = strpos($match_this_spot,"<");
                $first_bold_spot = max($first_bold_spot - round(($snippet_length/ 2),0), 0);
                $extract_content = substr($extract_content,$first_bold_spot,$snippet_length);

                $extract_content = @eregi_replace($reg_strings,"\\1<^#_>\\2</_#^>\\3",@eregi_replace($reg_strings,"\\1<^#_>\\2</_#^>\\3",$extract_content));
                $extract_content = str_replace("^#_","span class=\"$highlight_class\"",str_replace("_#^","span",$extract_content));

                $extract .= " ...{$extract_content}... ";
                $num_extracts++;
            }

            $c++;
        }

        if ($my_extract_size < $content_size)
        {
            $my_extract_size *= 100;
            if ($my_extract_size > $content_size)
            {
                $my_extract_size = $content_size;
            }
        }
        else
        {
            $my_extract_size++;
        }
    }

    return($extract);
}

?>
