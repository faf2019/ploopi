<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Fonctions de recherche et d'indexation de contenu.
 *
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class search_index
{

    /**
     * Connexion à la base de données d'indexation
     */

    public static function getdb() {
        static $client = null;
        if (!is_null($client)) return $client;

        try {

            $client = \Elasticsearch\ClientBuilder::create()->setHosts([_PLOOPI_ELASTICSEARCH_HOST])->build();

            // TEST UNIQUEMENT
            // $response = $client->indices()->delete(['index' => _PLOOPI_DB_DATABASE, 'client' => ['ignore' => 404]]);

            $exists = $client->indices()->exists(['index' => _PLOOPI_DB_DATABASE]);

            // Création de l'index
            if (!$exists) {

                $params = [
                    'index' => _PLOOPI_DB_DATABASE,
                    'body' => [

                        'mappings' => [
                            'element' => [
                                '_source' => [
                                    'enabled' => true
                                ],
                                'properties' => [
                                    'id_record' => [
                                        'type' => 'string',
                                    ],
                                    'id_object' => [
                                        'type' => 'integer',
                                    ],
                                    'label' => [
                                        'type' => 'string',
                                        'analyzer' => 'my_analyzer', // 'standard', 'french'
                                        'boost' => 2
                                    ],
                                    'content' => [
                                        'type' => 'string',
                                        'analyzer' => 'my_analyzer' // 'standard', 'french'
                                    ],
                                    'meta' => [
                                        'type' => 'string',
                                        'analyzer' => 'my_analyzer_light', // 'standard', 'french'
                                        'boost' => 3
                                    ],
                                    'timestp_create' => [
                                        'type' => 'long',
                                        'index' => 'not_analyzed'
                                    ],
                                    'timestp_modify' => [
                                        'type' => 'long',
                                        'index' => 'not_analyzed'
                                    ],
                                    'timestp_lastindex' => [
                                        'type' => 'long',
                                        'index' => 'not_analyzed'
                                    ],
                                    'id_user' => [
                                        'type' => 'integer'
                                    ],
                                    'id_workspace' => [
                                        'type' => 'integer'
                                    ],
                                    'id_module' => [
                                        'type' => 'integer'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => [
                            'number_of_shards' => 10,
                            'number_of_replicas' => 1,
                            'analysis' => [
                                'analyzer' => [
                                    'my_analyzer' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'standard', // 'ngram', 'standard'
                                        'filter' => ['stopwords', 'lowercase', 'elision', 'word_delimiter', 'snowball', 'phonetic'] // 'asciifolding', 'stemmer', 'phonetic'
                                        //'filter' => ['stopwords', 'asciifolding' ,'lowercase', 'snowball', 'elision', 'word_delimiter']

                                        // stopwords : suppression des mots communs
                                        // asciifolding : suppression des accents
                                        // lowercase : transformation en minuscules
                                        // elision : suppression des petits mots (voir liste)
                                        // word_delimiter : découpage en mots
                                        // snowball : racinisation
                                        // phonetic : conversion en phonétique

                                    ],
                                    'my_analyzer_light' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'standard',
                                        'filter' => ['lowercase', 'elision', 'word_delimiter', 'snowball', 'phonetic']
                                    ]
                                ],
                                'tokenizer' => [
                                    'ngram' => [
                                        'type' => 'nGram',
                                        'min_gram' => 5,
                                        'max_gram' => 20,
                                        'token_chars' => [ "letter", "digit" ]
                                    ]
                                ],
                                'filter' => [
                                    'stopwords' => [
                                        'type' => 'stop',
                                        'stopwords' =>  ['_french_'],
                                        'ignore_case' => true
                                    ],
                                    'snowball' => [
                                        'type' => 'snowball',
                                        'language' => 'French'
                                    ],
                                    'elision' => [
                                        'type' => 'elision',
                                        'articles' => ['c', 'l', 'm', 't', 'qu', 'n', 's', 'j', 'd']
                                    ],
                                    'phonetic' => [
                                        'type' => 'phonetic',
                                        'encoder' => 'beider_morse',
                                        'languageset' => 'french'
                                    ],
                                    'stemmer' => [
                                        'type' => 'stemmer',
                                        'name' => 'light_french'
                                    ],

                                ],
                            ]
                        ],
                    ]
                ];

                $response = $client->indices()->create($params);
            }
        }
        catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        return $client;
    }


    /**
     * Retourne l'identifiant unique pour un enregistrement d'un objet
     *
     * @param int $id_module identifiant du module
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @return string identifiant unique de l'enregistrement (hash SHA)
     *
     */

    public static function getid($id_module, $id_object, $id_record)
    {
        return sha1($id_module.'_'.$id_object.'_'.$id_record);
    }

    /**
     * Supprime l'index (mots clés) associé à un enregistrement d'un objet
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_module identifiant du module (optionnel)
     */

    public static function remove($id_object, $id_record, $id_module = -1)
    {
        $client = self::getdb();

        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

        $key = self::getid($id_module,$id_object,$id_record);

        $response = $client->delete([
            'index' => _PLOOPI_DB_DATABASE,
            'type' => 'element',
            'id' => $key,
            'client' => ['ignore' => 404]
        ]);
    }

    /**
     * Supprime l'index d'un module
     *
     * @param int $id_module identifiant du module
     */

    public static function remove_module($id_module = -1)
    {
        $client = self::getdb();

        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module = $_SESSION['ploopi']['moduleid'];

        $client->deleteByQuery([
            'index' => _PLOOPI_DB_DATABASE,
            'type' => 'element',
            'body'  => [
                'query' => [
                    'filtered' => [
                        'filter' => [
                            'must' => [
                                ['term' => ['id_module' => $id_module]],
                            ]
                        ]
                    ]
                ]
            ]
        ]);
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
     */

    public static function add($id_object, $id_record, $label, $content, $meta = '', $usecommonwords = true, $timestp_create = 0, $timestp_modify = 0, $id_user = -1, $id_workspace = -1, $id_module = -1)
    {
        global $ploopi_timer;

        if ($id_user == -1 && !empty($_SESSION['ploopi']['userid'])) $id_user = $_SESSION['ploopi']['userid'];
        if ($id_workspace == -1 && !empty($_SESSION['ploopi']['workspaceid'])) $id_workspace = $_SESSION['ploopi']['workspaceid'];
        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];


        $client = self::getdb();

        $key = self::getid($id_module, $id_object, $id_record);

        // CREATE INDEX
        $params = [
            'index' => _PLOOPI_DB_DATABASE,
            'type' => 'element',
            'id' => $key,
            'body' => [
                'id_record' => $id_record,
                'id_object' => $id_object,
                'label' => utf8_encode($label),
                'content' => utf8_encode($content),
                'meta' => utf8_encode($meta),
                'timestp_create' => $timestp_create,
                'timestp_modify' => $timestp_modify,
                'timestp_lastindex' => date::createtimestamp(),
                'id_user' => $id_user,
                'id_workspace' => $id_workspace,
                'id_module' => $id_module,
            ]
        ];

        $response = $client->index($params);
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
     */

    public static function get($id_object, $id_record, $limit = 100, $id_module = -1)
    {
        // curl -XGET 'http://localhost:9200/ploopidev/element/3986a02efba021669fe1e64ff1bfcceaf077aecd/_termvectors?fields=content&pretty=true' | more

        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

        $client = self::getdb();

        $key = self::getid($id_module, $id_object, $id_record);

        $params = [
            'index' => _PLOOPI_DB_DATABASE,
            'type' => 'element',
            'id' => $key,
            'fields' => ['label', 'content', 'meta'],
        ];

        $response = $client->termvectors($params);

        $tokens = array();
        if (!empty($response['term_vectors'])) {
            foreach(array('label', 'content', 'meta') as $field) {
                if (isset($response['term_vectors'][$field]['terms'])) {
                    foreach($response['term_vectors'][$field]['terms'] as $term => $detail) {
                        if (!isset($tokens[$term])) $tokens[$term] = 0;
                        $tokens[$term] += $detail['term_freq'];
                    }
                }
            }
        }

        // Tri
        asort($tokens, SORT_NUMERIC);

        // Limit
        $tokens = array_slice($tokens, 0, $limit);

        return $tokens;
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

    public static function search($keywords, $id_object = -1, $id_record = null, $id_module = null, $options = null)
    {
        $client = self::getdb();

        // Contrôle et formatage des paramètres
        if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module = $_SESSION['ploopi']['moduleid'];

        if (empty($id_module)) $id_module = array();
        elseif (!is_array($id_module)) $id_module = array($id_module);

        if (empty($id_record)) $id_record = array();
        elseif (!is_array($id_record)) $id_record = array($id_record);

        $limit = (isset($options['limit'])) ? $options['limit'] : 200;

        $orderby = (empty($options['orderby'])) ? '_score' : $options['orderby'];

        $sort = (isset($options['sort'])) ? $options['sort'] : 'desc';
        $sort = strtolower($sort);
        if (!in_array($sort, array('desc', 'asc'))) $sort = 'asc';

        // https://www.elastic.co/guide/en/elasticsearch/guide/current/_boosting_query_clauses.html
        // http://stackoverflow.com/questions/28538760/elasticsearch-bool-query-combine-must-with-or
        // https://www.elastic.co/guide/en/elasticsearch/guide/current/combining-filters.html
        // http://elasticsearch-cheatsheet.jolicode.com/

        $params = [
            'index' => _PLOOPI_DB_DATABASE,
            'type' => 'element',
            'body' => [
                'fields' => ['label', 'id_object', 'id_record', 'id_user', 'id_workspace', 'id_module', 'timestp_create', 'timestp_modify', 'timestp_lastindex'],
                'size' => $limit,
                'sort' => [$orderby => $sort],
                // 'min_score' => 0.01,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                '_all' => [
                                    'query' => utf8_encode($keywords),
                                    // 'operator' => 'and'
                                ],
                            ],
                        ],
                        'should' => [
                            [
                                'match' => [
                                    'label' => [
                                        'query' => utf8_encode($keywords),
                                    ]
                                ]
                            ],
                            [
                                'match' => [
                                    'content' => [
                                        'query' => utf8_encode($keywords),
                                    ]
                                ]
                            ],
                            [
                                'match' => [
                                    'meta' => [
                                        'query' => utf8_encode($keywords),
                                    ]
                                ]
                            ]
                        ],
                        'minimum_should_match' => 1,
                    ]
                ]
            ]
        ];

        $filter = array();


        // Filtre module
        if (!empty($id_module)) $filter['terms']['id_module'] = $id_module;

        // Filtre objet
        if ($id_object != -1) $filter['term']['id_object'] = $id_object;

        // Filtre record
        if (!empty($id_record)) $filter['terms']['id_record'] = $id_record;

        // Prise en compte de la vue sur les données pour chaque module
        // ATTENTION, SIMULATION ASSEZ COMPLEXE DU "OR" SQL
        foreach($id_module as $idm) {
            $wsp = array_merge(array(-1,0), explode(',', system::viewworkspaces($idm)));

            $filter['query']['bool']['should'][]['bool']['must'] = [
                [
                    'terms' => [
                        'id_workspace' => $wsp
                    ]
                ],
                [
                    'term' => [
                        'id_module' => $idm
                    ]
                ]
            ];
        }

        $filter['query']['bool']['minimum_should_match'] = 1;

        // Intégration du filtre dans la requete
        $params['body']['query']['bool']['filter'] = $filter;

        // output::print_r($params);
        $response = $client->search($params);

        // Mise en forme des résultats
        $arrRelevance = array();

        foreach($response['hits']['hits'] as $row) {
            $arrRelevance[] = [
                'relevance' => min(round($row['_score']*100), 100),
                'label' => $row['fields']['label'][0],
                'id_object' => $row['fields']['id_object'][0],
                'id_record' => $row['fields']['id_record'][0],
                'id_user' => $row['fields']['id_user'][0],
                'id_workspace' => $row['fields']['id_workspace'][0],
                'id_module' => $row['fields']['id_module'][0],
                'timestp_create' => $row['fields']['timestp_create'][0],
                'timestp_modify' => $row['fields']['timestp_modify'][0],
                'timestp_lastindex' => $row['fields']['timestp_lastindex'][0],
            ];
        }

        // output::print_r($arrRelevance);
        output::print_r($response);

        return $arrRelevance;
    }


    /**
     * Retourne les enregistrements indexés pour un objet d'un module
     *
     * @param int $id_object (optionnel)
     * @param int $id_module (optionnel)
     * @return array tableau des id_record d'enregistrements indexés
     */

    /*
    function ploopi_search_get_records($id_object = null, $id_module = null)
    {
        $db = self::getdb();

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
    */
}
