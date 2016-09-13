<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Partie publique du module
 *
 * @package wiki
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */

/*
try {
    // http://obtao.com/blog/2013/10/configuration-elasticsearch-de-maniere-optimale/
    // http://dev.af83.com/2013/05/22/quelques-bases-pour-preparer-une-indexation-dans-elasticsearch.html
    // https://gist.github.com/dadoonet/2146038

    $client = Elasticsearch\ClientBuilder::create()->setHosts([_PLOOPI_ELASTICSEARCH_HOST])->build();

    $exists = $client->indices()->exists(['index' => 'ploopi']);

    if (!$exists || true) {

        // TRUNCATE (Supprime l'index complet)
        echo '<hr />TRUNCATE';
        $response = $client->indices()->delete(['index' => 'ploopi', 'client' => ['ignore' => 404]]);
        // ovensia\ploopi\output::print_r($response);

        $params = [
            'index' => 'ploopi',
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
                                'analyzer' => 'my_analyzer' // 'standard', 'french'
                            ],
                            'timestp_create' => [
                                'type' => 'string',
                            ],
                            'timestp_modify' => [
                                'type' => 'string',
                            ],
                            'timestp_lastindex' => [
                                'type' => 'string',
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
                                'tokenizer' => 'standard', // 'standard'
                                'filter' => ['stopwords', 'lowercase', 'elision', 'word_delimiter', 'snowball', 'phonetic'] // 'asciifolding', 'stemmer', 'phonetic'
                                //'filter' => ['stopwords', 'asciifolding' ,'lowercase', 'snowball', 'elision', 'word_delimiter']

                                // stopwords : suppression des mots communs
                                // asciifolding : suppression des accents
                                // lowercase : transformation en minuscules
                                // elision : suppression des petits mots (voir liste)
                                // word_delimiter : découpage en mots
                                // snowball : racinisation
                                // phonetic : conversion en phonétique

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

        echo '<hr />CREATE INDEX';
        $response = $client->indices()->create($params);
        // ovensia\ploopi\output::print_r($response);

        echo '<hr />GET MAPPING';
        $response = $client->indices()->getMapping();
        // ovensia\ploopi\output::print_r($response);

        // SETTINGS
        echo '<hr />GET SETTINGS';
        $response = $client->indices()->getSettings();
        // ovensia\ploopi\output::print_r($response);


        // Permet que le serveur soit pret ????
        sleep(1);

        // ANALYZE
        // http://stackoverflow.com/questions/25473919/how-to-analyse-a-string-using-the-php-api
        echo '<hr />ANALYZE';
        $params = [
            'index' => 'ploopi',
            'analyzer' => 'my_analyzer', // 'standard', 'french'
            'text' => utf8_encode("Stéphane ESCAICH, c'était une petite pitoune et s'en est encore une"),
        ];

        $response = $client->indices()->analyze($params);
        ovensia\ploopi\output::print_r($response);

        // CREATE INDEX
        $params = [
            'index' => 'ploopi',
            'type' => 'element',
            'id' => '1,2,toto',
            'body' => [
                'id_record' => '12345abc',
                'id_object' => 1,
                'label' => utf8_encode("Stéphane ESCAICH, c'était une petite pitoune et s'en est encore une"),
                'timestp_create' => '20160826081512',
                'timestp_modify' => '20160826081512',
                'timestp_lastindex' => '20160826081512',
                'id_user' => 1,
                'id_workspace' => 2,
                'id_module' => 3,
            ]
        ];

        echo '<hr />INDEX';
        $response = $client->index($params);
        ovensia\ploopi\output::print_r($response);


        // GET ID
        $params = [
            'index' => 'ploopi',
            'type' => 'element',
            'id' => '1,2,toto'
        ];

        $response = $client->get($params);
        echo '<hr />GET ID';
        ovensia\ploopi\output::print_r($response);


        // SEARCH
        // Attente indexation
        sleep(1);

    }

    // Retourne TOUT
    // $params = [
    //     'index' => 'ploopi',
    //     'type' => 'element',
    //     'body' => [
    //         'query' => [
    //             'match_all' => [ ]
    //         ]
    //     ]
    // ];

    $params = [
        'index' => 'ploopi',
        'type' => 'element',
        'body' => [
            'fields' => ['label', 'id_object', 'id_record'],
            'size' => 5,
            'query' => [
                'match' => [
                    'label' => [
                        'query' => utf8_encode('stefan eskech pitoune'),
                    ]
                ]
            ]
        ]
    ];


    $response = $client->search($params);
    echo '<hr />SEARCH';
    ovensia\ploopi\output::print_r($response);
}
catch (Exception $e) {
    echo $e->getMessage();
}
return;
*/

ovensia\ploopi\module::init('wiki');

echo $skin->create_pagetitle(ovensia\ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

// Menu principal
$strWikiMenu = isset($_GET['wiki_menu']) ? $_GET['wiki_menu'] : '';

switch($strWikiMenu)
{
    case 'index_title':
    case 'index_date':
        include_once './modules/wiki/public_index.php';
    break;

    case 'reindex':
        include_once './modules/wiki/public_reindex.php';
    break;

    default: // navigation
        include_once './modules/wiki/public_view.php';
    break;

}
?>
