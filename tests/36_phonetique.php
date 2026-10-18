<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Tests unitaires de ploopi\phonetique, qui remplace soundex-fr-bundle (CC BY-NC,
 * non libre) pour la recherche phonétique de l'index.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();

use ploopi\phonetique;

T::section('même prononciation, même code');

foreach (array(
    array('Faure', 'Fort'), array('Philippe', 'Filipe'), array('Escaich', 'Escaish'),
    array('chat', 'chats'), array('parler', 'parlé'), array('pain', 'pin'), array('vingt', 'vin'),
    array('temps', 'tant'), array('photo', 'foto'), array('Thomas', 'Tomas'), array('quand', 'camp'),
    array('cerise', 'serise'), array('nation', 'nasion'), array('Gérard', 'Jérard'), array('bateau', 'batô'),
    array('Martin', 'Martain'), array('Lefebvre', 'Lefèvre'), array('écoles', 'école'), array('oiseau', 'oizo'),
    array('cœur', 'keur'), array('Dupont', 'Dupond'), array('Mathieu', 'Matthieu'), array('Rousseau', 'Roussot'),
    array('Hugo', 'Ugo'), array('ÉCOLE', 'école'),
) as $arrPaire)
    T::eq("{$arrPaire[0]} = {$arrPaire[1]}", phonetique::code($arrPaire[0]), phonetique::code($arrPaire[1]));

T::section('prononciations distinctes, codes distincts');

foreach (array(
    array('bon', 'ban'), array('pain', 'pont'), array('rose', 'rousse'), array('vin', 'van'),
    array('mur', 'mour'), array('chat', 'chien'), array('bus', 'but'), array('peur', 'pire'),
    array('donne', 'dune'), array('Martin', 'Marton'),
) as $arrPaire)
    T::neq("{$arrPaire[0]} ≠ {$arrPaire[1]}", phonetique::code($arrPaire[0]), phonetique::code($arrPaire[1]));

T::section('forme du code');

T::eq('mot vide', '', phonetique::code(''));
T::eq('sans lettre', '', phonetique::code('1234 !'));
$booForme = true;
foreach (array('anticonstitutionnellement', 'Ploopi', "l'été", 'Œuvre', 'naïveté', 'x') as $strMot)
    if (!preg_match('/^[a-z]{0,20}$/', phonetique::code($strMot))) $booForme = false;
T::vrai('minuscules a-z, 20 caractères au plus (colonne phonetic char(20), requête sans échappement)', $booForme);
T::eq('déterministe', phonetique::code('Escaich'), phonetique::code('Escaich'));

T::section('la bibliothèque non libre a disparu');

$strComposer = file_get_contents(PLOOPI_ROOT.'/composer.json');
T::contientpas('soundex-fr-bundle retiré de composer.json', 'soundex-fr-bundle', $strComposer);
T::contientpas('search_index ne l\'utilise plus', 'SoundexFr', file_get_contents(PLOOPI_ROOT.'/include/classes/search_index.php'));
T::contient('search_index utilise ploopi\phonetique', 'phonetique::code(', file_get_contents(PLOOPI_ROOT.'/include/classes/search_index.php'));

/**
 * Sur une instance : un contenu indexé se retrouve par un homophone, et seulement
 * par la voie phonétique.
 */
T::section('recherche phonétique dans l\'index (instance)');

if (!config_fournie() || !file_exists(PLOOPI_ROOT.'/config/config.php')) T::skip('suite d\'index', 'aucune instance installée');
else
{
    $intObjet = 99;
    $intModule = 65000;          // instance fictive (colonne smallint)
    $strContenu = 'Le château de Monsieur Faure, au bord de la Garonne';
    ploopi\search_index::add($intObjet, 'phon1', 'Témoin phonétique', $strContenu, '', true, 0, 0, 0, 0, $intModule);

    $intPho = (int)db()->query("SELECT COUNT(*) FROM ploopi_index_phonetic_element pe INNER JOIN ploopi_index_element e ON e.id = pe.id_element WHERE e.id_module = {$intModule} AND pe.phonetic = '".phonetique::code('Faure')."'")->fetch_row()[0];
    T::vrai('le code de « Faure » est indexé', $intPho == 1, "{$intPho} ligne(s)");

    $arrAvec = ploopi\search_index::search('Fort', $intObjet, null, null, array('stem' => false, 'phonetic' => true));
    $arrSans = ploopi\search_index::search('Fort', $intObjet, null, null, array('stem' => false, 'phonetic' => false));
    T::vrai('« Fort » trouve « Faure » par la phonétique', sizeof($arrAvec) == 1);
    T::vrai('témoin : sans phonétique, rien', sizeof($arrSans) == 0);

    ploopi\search_index::remove($intObjet, 'phon1', $intModule);
    $intReste = (int)db()->query("SELECT COUNT(*) FROM ploopi_index_element WHERE id_module = {$intModule}")->fetch_row()[0];
    T::vrai('index du témoin retiré', $intReste == 0);

    /**
     * Mots en UTF-8 : ç, ë, û coupés par le découpage octet par octet ; métadonnées mises
     * en minuscules comme de l'ISO-8859-1 (« Été » altéré) ; racine de l'index calculée
     * sur le mot sans accents (« abandonnée » → « abandonne »), qui ne rejoignait pas
     * celle de la recherche (« abandonner » → « abandon »).
     */
    T::section('mots accentués dans l\'index (instance)');
    $strContenu = 'Les hôpitaux accueillent le garçon de Noël, piste abandonnée';
    ploopi\search_index::add($intObjet, 'utf1', 'Témoin UTF-8', $strContenu, 'Été Sûreté', false, 0, 0, 0, 0, $intModule);
    $arrMotsIndex = array();
    $objRs = db()->query("SELECT ke.keyword FROM ploopi_index_keyword_element ke INNER JOIN ploopi_index_element e ON e.id = ke.id_element WHERE e.id_module = {$intModule}");
    while ($arrLigne = $objRs->fetch_row()) $arrMotsIndex[] = $arrLigne[0];
    T::vrai('« garçon » et « noël » indexés entiers', in_array('garcon', $arrMotsIndex) && in_array('noel', $arrMotsIndex) && !in_array('gar', $arrMotsIndex), implode(' ', $arrMotsIndex));
    T::vrai('métadonnées « Été Sûreté » indexées sans altération', in_array('ete', $arrMotsIndex) && in_array('surete', $arrMotsIndex), implode(' ', $arrMotsIndex));
    $arrOptions = array('stem' => false, 'phonetic' => false, 'usecommonwords' => false);
    T::vrai('« garçon » trouvé par le mot', sizeof(ploopi\search_index::search('garçon', $intObjet, null, null, $arrOptions)) == 1);
    T::vrai('« hôpital » trouve « hôpitaux » par la racine', sizeof(ploopi\search_index::search('hôpital', $intObjet, null, null, array('stem' => true) + $arrOptions)) == 1);
    T::vrai('« abandonner » trouve « abandonnée » par la racine', sizeof(ploopi\search_index::search('abandonner', $intObjet, null, null, array('stem' => true) + $arrOptions)) == 1);
    T::vrai('témoin : sans racine, « abandonner » ne le trouve pas', sizeof(ploopi\search_index::search('abandonner', $intObjet, null, null, $arrOptions)) == 0);
    ploopi\search_index::remove($intObjet, 'utf1', $intModule);

    /**
     * Mot sans lettre latine (forme sans accents vide), long : sa racine, calculée sur le
     * mot d'origine, dépassait la colonne (char(20)) et l'INSERT échouait en SQL strict.
     */
    $strContenu = str_repeat('中文测试内容非常长的词语用于检查数据库列宽度是否足够存储 ', 3).'fin';
    $intErreurs = ploopi\error::$nb;
    ploopi\search_index::add($intObjet, 'utf2', 'Témoin non latin', $strContenu, '', false, 0, 0, 0, 0, $intModule);
    T::eq('texte non latin indexé sans erreur SQL', 0, ploopi\error::$nb - $intErreurs);
    $intLongues = (int)db()->query("SELECT COUNT(*) FROM ploopi_index_stem_element se INNER JOIN ploopi_index_element e ON e.id = se.id_element WHERE e.id_module = {$intModule} AND e.id_record = 'utf2' AND CHAR_LENGTH(se.stem) > 0 AND se.stem <> 'fin'")->fetch_row()[0];
    T::eq('aucune racine pour un mot sans lettre latine', 0, $intLongues);
    ploopi\search_index::remove($intObjet, 'utf2', $intModule);

    /**
     * Résultats rangés par id_record dès qu'un module était donné : deux contenus de même
     * identifiant (dans deux modules, ou deux types d'objets) s'écrasaient, la recherche
     * intégrale en perdait un. Clé id_record pour un seul type d'objet d'un seul module
     * (la GED lit ses md5id dans les clés), élément d'index sinon. Tri par pertinence.
     */
    T::section('recherche sur plusieurs modules (instance)');
    $strContenu = 'Contenu commun zephyrine';
    ploopi\search_index::add($intObjet, 'r1', 'Témoin A', $strContenu, '', true, 0, 0, 0, 0, $intModule);
    $strContenu = 'Contenu commun zephyrine zephyrine zephyrine';
    ploopi\search_index::add($intObjet, 'r1', 'Témoin B', $strContenu, '', true, 0, 0, 0, 0, $intModule + 1);
    $arrDeux = ploopi\search_index::search('zephyrine', -1, null, array($intModule, $intModule + 1));
    T::eq('même identifiant dans deux modules : deux résultats', 2, sizeof($arrDeux));
    $arrUn = ploopi\search_index::search('zephyrine', $intObjet, null, $intModule);
    T::eq('un type d\'objet d\'un module : clé id_record', array('r1'), array_keys($arrUn));
    $arrTries = array_values(array_map(function($r) { return (float) $r['relevance']; }, $arrDeux));
    T::vrai('tri par pertinence décroissante', sizeof($arrTries) == 2 && $arrTries[0] >= $arrTries[1], implode(' ; ', $arrTries));
    ploopi\search_index::remove($intObjet, 'r1', $intModule);
    ploopi\search_index::remove($intObjet, 'r1', $intModule + 1);
}

exit(T::bilan('36_phonetique'));
