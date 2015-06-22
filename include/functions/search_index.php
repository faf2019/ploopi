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
include_once './lib/phonetic/phonetic.php';

if (!function_exists('stem_french')) { function stem_french($str) { return ''; } }


/**
 * Connexion à la base de données d'indexation
 */

function ploopi_search_getdb() {
    static $objDb = null;
    if (!is_null($objDb)) return $objDb;

    if (_PLOOPI_DB_SERVER != _PLOOPI_INDEXATION_DB_SERVER || _PLOOPI_DB_DATABASE != _PLOOPI_INDEXATION_DB_DATABASE)
    {
        $objDb = new ploopi_db(_PLOOPI_INDEXATION_DB_SERVER, _PLOOPI_INDEXATION_DB_LOGIN, _PLOOPI_INDEXATION_DB_PASSWORD, _PLOOPI_INDEXATION_DB_DATABASE);
        if(!$objDb->isconnected()) {
            $objDb = null;
            trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);
        }
    }
    else
    {
        global $db;
        $objDb = $db;
    }

    return $objDb;
}


/**
 * Retourne l'identifiant unique pour un enregistrement d'un objet
 *
 * @param int $id_module identifiant du module
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @return string identifiant unique de l'enregistrement (hash MD5, 32 caractères)
 *
 * @see md5
 */

function ploopi_search_get_id($id_module, $id_object, $id_record)
{
    $db = ploopi_search_getdb();

    $db->query("SELECT id FROM ploopi_index_element WHERE id_module = {$id_module} AND id_object = {$id_object} AND id_record = '".$db->addslashes($id_record)."'");
    if ($row = $db->fetchrow()) return $row['id'];
    else return null;
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
    $db = ploopi_search_getdb();

    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];
    if (($id_element = ploopi_search_get_id($id_module,$id_object,$id_record)) != null) {
        $db->query("DELETE FROM ploopi_index_element WHERE id = '{$id_element}'");
        $db->query("DELETE FROM ploopi_index_keyword_element WHERE id_element = '{$id_element}'");
        $db->query("DELETE FROM ploopi_index_stem_element WHERE id_element = '{$id_element}'");
        $db->query("DELETE FROM ploopi_index_phonetic_element WHERE id_element = '{$id_element}'");
    }
}

/**
 * Supprime l'index d'un module
 *
 * @param int $id_module identifiant du module
 */

function ploopi_search_remove_index_module($id_module = -1)
{
    $db = ploopi_search_getdb();

    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module = $_SESSION['ploopi']['moduleid'];

    $db->query("
        DELETE se.*
        FROM ploopi_index_stem_element se, ploopi_index_element e
        WHERE se.id_element = e.id
        AND e.id_module = {$id_module}
    ");

    $db->query("
        DELETE ke.*
        FROM ploopi_index_keyword_element ke, ploopi_index_element e
        WHERE ke.id_element = e.id
        AND e.id_module = {$id_module}
    ");

    $db->query("
        DELETE pe.*
        FROM ploopi_index_phonetic_element pe, ploopi_index_element e
        WHERE pe.id_element = e.id
        AND e.id_module = {$id_module}
    ");

    $db->query("
        DELETE e.*
        FROM ploopi_index_element e
        WHERE e.id_module = {$id_module}
    ");
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
 * @see ploopi_convertaccents
 *
 * @link http://pecl.php.net/package/stem
 */

function ploopi_search_create_index($id_object, $id_record, $label, &$content, $meta = '', $usecommonwords = true, $timestp_create = 0, $timestp_modify = 0, $id_user = -1, $id_workspace = -1, $id_module = -1)
{
    global $ploopi_timer;

    $db = ploopi_search_getdb();

    if ($id_user == -1 && !empty($_SESSION['ploopi']['userid'])) $id_user = $_SESSION['ploopi']['userid'];
    if ($id_workspace == -1 && !empty($_SESSION['ploopi']['workspaceid'])) $id_workspace = $_SESSION['ploopi']['workspaceid'];
    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

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

        $_SESSION['ploopi']['commonwords'] = array_flip(preg_split("/[\n]/", str_replace("\r",'',$filecontent)));
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
        if (!$usecommonwords || !isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
        {
            // on vérifie sa taille
            $len = strlen($kw);
            if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
            {
                // détermination du lemme (racine)
                // $kw_stem = stem_french($kw);

                if (!isset($words[$kw_clean])) $words[$kw_clean] = array('weight' => 1, 'meta' => 0);
                else $words[$kw_clean]['weight']++;

                /*
                if (!isset($words[$kw_stem]['words'][$kw_clean])) $words[$kw_stem]['words'][$kw_clean] = array('weight' => 1, 'meta' => 0);
                else $words[$kw_stem]['words'][$kw_clean]['weight']++;
                */

                $words_indexed++;
            }
        }
        $words_overall++;
    }

    // tri des mots par poids décroissant
    arsort($words);

    $word = current($words);
    $max_weight = $word['weight'];

    // TRAITEMENT DES METAS
    for ($kw = strtok($meta, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
    {
        // mot en minuscule avec accents
        $kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

        // mot en minuscule sans accent et sans caractère parasite
        $kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

        // on vérifie qu'il n'est pas dans la liste des mots à exclure
        if (!$usecommonwords || !isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
        {
            // on vérifie sa taille
            $len = strlen($kw);
            if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
            {
                // détermination du lemme (racine)
                // $kw_stem = stem_french($kw);

                $words[$kw_clean]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
                $words[$kw_clean]['meta'] = 1;

                //$words[$kw_stem]['words'][$kw_clean]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
                //$words[$kw_stem]['words'][$kw_clean]['meta'] = 1;
            }
        }
    }

    // tri des mots par poids décroissant
    arsort($words);

    // nettoyage index
    ploopi_search_remove_index($id_object, $id_record, $id_module);

    $kw = current($words);
    $kw_ratio = (empty($words_overall)) ? 1 : ($kw['weight']*100 / $words_overall);

    $max_kw = (_PLOOPI_INDEXATION_KEYWORDSMAXPCENT) ? (sizeof($words)*_PLOOPI_INDEXATION_KEYWORDSMAXPCENT)/100 : sizeof($words);


    $objElement = new index_element();
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

    $id_element = $objElement->fields['id'];


    $stems = array();
    $phonetics = array();

    for ($i = 1; ($i <= $max_kw && $kw_ratio >= _PLOOPI_INDEXATION_RATIOMIN) || $kw['meta']; $i++)
    {
        $kw_value = key($words);
        if (strlen($kw_value) <= 20)
        {
            $kw_pho = strtolower(phonetique($kw_value));
            $kw_stem = stem_french($kw_value);

            $ratio = (empty($kw['meta']) && $kw_ratio<1) ? $kw_ratio : 1;
            $relevance = (empty($kw['meta'])) ? ($kw['weight']*100)/$max_weight : 100;

            if ($kw_stem != '') {
                if (empty($stems[$kw_stem])) {
                    $stems[$kw_stem] = array(
                        'weight' => $kw['weight'],
                        'ratio' => $ratio,
                        'relevance' => $relevance
                    );
                }
                else {
                    $stems[$kw_stem]['weight'] = min(999999, $stems[$kw_stem]['weight']+$kw['weight']);
                    $stems[$kw_stem]['ratio'] = min(1, $stems[$kw_stem]['ratio']+$ratio);
                    $stems[$kw_stem]['relevance'] = min(100, $stems[$kw_stem]['relevance']+$relevance);
                }
            }

            if ($kw_pho != '') {
                if (empty($phonetics[$kw_pho])) {
                    $phonetics[$kw_pho] = array(
                        'weight' => $kw['weight'],
                        'ratio' => $ratio,
                        'relevance' => $relevance
                    );
                }
                else {
                    $phonetics[$kw_pho]['weight'] = min(999999, $phonetics[$kw_pho]['weight']+$kw['weight']);
                    $phonetics[$kw_pho]['ratio'] = min(1, $phonetics[$kw_pho]['ratio']+$ratio);
                    $phonetics[$kw_pho]['relevance'] = min(100, $phonetics[$kw_pho]['relevance']+$relevance);
                }
            }

            // enregistrement du lien mot clé <-> enregistrement
            $db->query("INSERT INTO ploopi_index_keyword_element(id_element, keyword, weight, ratio, relevance) VALUES('{$id_element}', '{$kw_value}', {$kw['weight']}, {$ratio}, {$relevance})");
        }

        $kw = next($words);
        $kw_ratio = (empty($words_overall)) ? 1 : ($kw['weight']*100 / $words_overall);
    }

    foreach($stems as $stem => $detail) {
        $db->query("INSERT INTO ploopi_index_stem_element(id_element, stem, weight, ratio, relevance) VALUES('{$id_element}', '{$stem}', {$detail['weight']}, {$detail['ratio']}, {$detail['relevance']})");
    }

    foreach($phonetics as $pho => $detail) {
        $db->query("INSERT INTO ploopi_index_phonetic_element(id_element, phonetic, weight, ratio, relevance) VALUES('{$id_element}', '{$pho}', {$detail['weight']}, {$detail['ratio']}, {$detail['relevance']})");
    }
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
    $db = ploopi_search_getdb();

    $index = array();

    if (is_numeric($limit))
    {
        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

        $id_element = ploopi_search_get_id($id_module,$id_object,$id_record);

        $sql =  "
                SELECT      ke.*

                FROM        ploopi_index_keyword_element ke

                WHERE       ke.id_element = '{$id_element}'

                ORDER BY    ke.relevance DESC, ke.weight DESC, ke.keyword

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
    $db = ploopi_search_getdb();

    if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module = $_SESSION['ploopi']['moduleid'];

    // on récupère la liste des racines contenues dans la liste des mots clés
    list($arrStems) = ploopi_getwords($keywords, isset($options['usecommonwords']) ? $options['usecommonwords'] : true, true);

    // on récupère la liste des mots contenus dans la liste des mots clés
    list($arrKeywords) = ploopi_getwords($keywords, isset($options['usecommonwords']) ? $options['usecommonwords'] : true, false);

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

    if ($id_object != -1) $arrSearch[] = sprintf("e.id_object = %d", $id_object);

    if (empty($id_module)) $id_module = array();
    elseif (!is_array($id_module)) $id_module = array($id_module);

    // Prise en compte de la vue sur les données pour chaque module
    $arrViewFilter = array();
    foreach($id_module as $idm) $arrViewFilter[] = "(e.id_module = {$idm} AND e.id_workspace IN (-1,0,".ploopi_viewworkspaces($idm)."))";

    // Intégration du filtre sur la vue dans le filtre global
    if (!empty($arrViewFilter)) $arrSearch[] = '('.implode(' OR ', $arrViewFilter).')';

    $strSearch = (empty($arrSearch)) ? '' : ' WHERE '.implode(' AND ', $arrSearch);

    $orderby = (empty($options['orderby'])) ? 'relevance' : $options['orderby'];
    $sort = (isset($options['sort'])) ? $options['sort'] : 'DESC';

    $limit = (isset($options['limit'])) ? $options['limit'] : 200;


    if (empty($arrKeywords) && empty($arrStems))
    {
        $sql =  "
                SELECT      e.*

                FROM        ploopi_index_element e

                {$strSearch}

                ORDER BY    e.label
                ";

        $db->query($sql);

        while ($row = $db->fetchrow())
        {
            $id = ($id_module != '') ? $row['id_record'] : $row['id'];

            $arrElements[$id] = $row;
            $arrRelevance[$id] = array(
                'relevance' => 100,
                'count' => 1,
                'kw_ratio' => 0
            );
        }

    }
    else
    {

        $arrSearchs = array('keyword' => array(), 'stem' => array(), 'phonetic' => array());
        foreach($arrKeywords as $kw => $occ)
        {
            $arrSearchs['keyword'][$kw] = 1;

            $stem = stem_french($kw);
            $pho = strtolower(phonetique($kw));
            if ($stem != '') $arrSearchs['stem'][$stem] = 1;
            if ($pho != '') $arrSearchs['phonetic'][$pho] = 1;
        }

        $intNbKw = sizeof($arrSearchs['keyword'])+sizeof($arrSearchs['stem'])+sizeof($arrSearchs['phonetic']);


        global $ploopi_timer;
        $t1 = $ploopi_timer->getexectime();

        foreach($arrSearchs as $type => $detail) {
            foreach(array_keys($detail) as $kw) {

                $id = ord(substr($kw,0,1))-96;

                $sql =  "
                        SELECT       e.*,
                                    ke.relevance

                        FROM        ploopi_index_{$type}_element ke
                        INNER JOIN  ploopi_index_element e ON e.id = ke.id_element

                        {$strSearch}

                        AND         ke.{$type} = '{$kw}'
                        ";

                $db->query($sql);

                while ($row = $db->fetchrow())
                {
                    $id = ($id_module != '') ? $row['id_record'] : $row['id'];

                    if (!isset($arrElements[$id]))
                    {
                        $arrElements[$id] = $row;
                        $arrRelevance[$id]['relevance'] = $row['relevance']/$intNbKw;
                    }
                    else
                    {
                        $arrRelevance[$id]['relevance'] += $row['relevance']/$intNbKw;
                    }
                }
            }
        }
    }

    // tri du résultat en fonction du champ et de l'ordre
    $compare_sign = ($sort == 'DESC') ? '>' : '<';

    uasort($arrRelevance, create_function('$a,$b', 'return $b[\''.$orderby.'\'] '.$compare_sign.' $a[\''.$orderby.'\'];'));

    $arrResult = array();

    $c = 0;
    while (current($arrRelevance) !== false && $c++ < $limit)
    {
        $k = key($arrRelevance);
        $arrResult[$k] = array_merge($arrElements[$k], $arrRelevance[$k]);
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

    if ($usecommonwords && !isset($_SESSION['ploopi']['commonwords']))
    {
        if (file_exists(_PLOOPI_INDEXATION_COMMONWORDS_FR))
        {
            $filecontent = '';
            $handle = @fopen(_PLOOPI_INDEXATION_COMMONWORDS_FR, 'r');
            if ($handle)
            {
                while (!feof($handle)) $filecontent .= fgets($handle);
                fclose($handle);
            }

            $_SESSION['ploopi']['commonwords'] = array_flip(preg_split("/[\n]/", str_replace("\r",'',$filecontent)));
        }
        else $_SESSION['ploopi']['commonwords'] = array();
    }

    for ($kw = strtok($content, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
    {
        // remove empty characters
        $kw = trim(strtolower($kw),"\x0..\x20\xa0");

        // only keep "normal" characters
        $kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

        if (!$usecommonwords || !isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
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
    $stop_regs = "[][(){}[:blank:]=&?!&#%\$£*@+%:;,\/\.'\"]";
    $reg_strings = "/({$stop_regs}{1}|^)({$reg_strings})()/i";

    $num_extracts = 0;
    $c = 0;
    $my_extract_size = $snippet_length;
    $extract = '';

    $content_size = strlen($content);

    while (($num_extracts == 0) && ($my_extract_size <= $content_size))
    {
        while($num_extracts < $snippet_num && $extract_content = preg_replace("/([ ]{2,}|\n|\r|\r\n)/"," ",substr($content, $c*$snippet_length, $snippet_length)))
        {
            if(preg_match($reg_strings,$extract_content))
            {
                $match_this_spot = preg_replace($reg_strings,"\\1<\\2>\\3",$extract_content);
                $first_bold_spot = strpos($match_this_spot,"<");
                $first_bold_spot = max($first_bold_spot - round(($snippet_length/ 2),0), 0);
                $extract_content = substr($extract_content,$first_bold_spot,$snippet_length);

                $extract_content = @preg_replace($reg_strings,"\\1<^#_>\\2</_#^>\\3",@preg_replace($reg_strings,"\\1<^#_>\\2</_#^>\\3",$extract_content));
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

/**
 * Retourne les enregistrements indexés pour un objet d'un module
 *
 * @param int $id_object (optionnel)
 * @param int $id_module (optionnel)
 * @return array tableau des id_record d'enregistrements indexés
 */

function ploopi_search_get_records($id_object = null, $id_module = null)
{
    $db = ploopi_search_getdb();

    $arrWhere = array();

    if (!isset($id_module) && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

    if (isset($id_object) && is_numeric($id_object)) $arrWhere[] = "id_object = '".$db->addslashes($id_object)."'";
    if (isset($id_module) && is_numeric($id_module)) $arrWhere[] = "id_module = '".$db->addslashes($id_module)."'";

    $strWhere = empty($arrWhere) ? '' : 'WHERE '.implode(' AND ', $arrWhere);

    $sql = "
        SELECT  id_record
        FROM    ploopi_index_element
        {$strWhere}
    ";

    $rs = $db->query($sql);

    return $db->getarray($rs, true);
}


/**
 * Import de synonymes
 */

function ploopi_search_import_syn($file) {

    set_time_limit(0);

    $db = ploopi_search_getdb();
    $db->query("TRUNCATE ploopi_index_synonym");

    $handle = @fopen($file, 'r');
    if ($handle) {
        $line = 1;
        $first = true;
        while (($buffer = fgets($handle, 4096)) !== false) {
            if ($line > 1) {
                $parts = explode('|', iconv("UTF-8", "ISO-8859-1//TRANSLIT", trim($buffer))); // ploopi_iso8859_clean(utf8_decode()));
                if ($first) {
                    $word = $parts[0];
                    $lines = $parts[1];
                    $synonyms = array();
                    $first = false;
                }
                else {
                    unset($parts[0]);
                    $synonyms = array_unique(array_merge($synonyms, $parts));
                    $lines--;
                    if ($lines == 0) {
                        $first = true;
                        foreach($synonyms as $synonym) {
                            $word = trim(preg_replace("@\(.*\)@","",$word));
                            $synonym = trim(preg_replace("@\(.*\)@","",$synonym));

                            $word = str_replace(".","",$word);
                            $synonym = str_replace(".","",$synonym);

                            $db->query("REPLACE INTO ploopi_index_synonym VALUES('".$db->addslashes($word)."','".$db->addslashes($synonym)."')");
                        }
                    }
                }
            }
            $line++;
        }
        if (!feof($handle)) {
            echo "Erreur: fgets() a échoué\n";
        }
        fclose($handle);
    }
}

/*
if (isset($_GET['import'])) {
    ploopi_search_import_syn('/var/www/thes_fr.dat');
}
*/
