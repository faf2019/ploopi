<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace ploopi;

/**
 * Code phonétique d'un mot français, pour la recherche approchée de l'index
 * (« Faure » trouve « Fort », « Philippe » trouve « Filipe »).
 *
 * Le mot est réduit à une suite de sons : lettres muettes retirées, graphies
 * d'un même son confondues (ph/f, c/k/qu, eau/au/o, ain/ein/in…), lettres
 * doublées fusionnées. Deux mots qui se prononcent de même reçoivent le même
 * code. Le code ne contient que des minuscules a-z et tient en 20 caractères
 * (colonne ploopi_index_phonetic_element.phonetic).
 *
 * Remplace soundex-fr-bundle, dont la licence (CC BY-NC) n'est pas libre. Les
 * codes diffèrent : un index construit avec l'ancienne bibliothèque se
 * reconstruit par réindexation.
 *
 * @package ploopi
 * @subpackage search
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class phonetique
{
    /**
     * Longueur maximale d'un code
     */
    const LONGUEUR = 20;

    /**
     * Lettres accentuées et ligatures ramenées à l'alphabet latin de base. Les « é »,
     * « è », « ê » sont notés d'emblée comme le son E : jamais muets en finale.
     *
     * @var array
     */
    private static $_arrTranslit = array(
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a',
        'é' => 'E', 'è' => 'E', 'ê' => 'E', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i',
        'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ò' => 'o', 'õ' => 'o',
        'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
        'ÿ' => 'y', 'ý' => 'y', 'ñ' => 'n', 'ç' => 's',
        'cœu' => 'kE', 'cœ' => 'kE', 'œu' => 'E', 'œ' => 'E', 'æ' => 'E', 'ß' => 's',
    );

    /**
     * Fins de mot muettes, retirées avant tout le reste (motif => remplacement).
     * Les mots courts sont épargnés : « bus », « os », « net » gardent leur finale.
     *
     * @var array
     */
    private static $_arrFinales = array(
        '/(?<=...)[sx]$/' => '',              // pluriels : chats, chevaux
        '/(?<=..)e$/' => '@',                 // e muet : faure, rose (@ retiré à la fin)
        '/(?<=..)gt$/' => '',                 // vingt, doigt
        '/(?<=..)(?:er|ez|et)$/' => 'E',      // parler, nez, jouet : son « é »
        '/(?<=.[aeiouyE])[td]$/' => '',       // chat, nid
        '/(?<=..[nr])[td]$/' => '',           // grand, fort
        '/(?<=..m)p$/' => '',                 // champ
        '/(?<=..n)[gc]$/' => '',              // long, banc
    );

    /**
     * Consonnes : graphies d'un même son confondues. Les sons produits sont notés en
     * majuscules pour ne plus être repris par les règles suivantes.
     *
     * @var array
     */
    private static $_arrConsonnes = array(
        '/sch/' => 'X',                       // schéma : « ch »
        '/ch(?=[rl])/' => 'K',                // chrome, chlore
        '/[cs]h/' => 'X',                     // chat, shampoing
        '/ph/' => 'F',
        '/th/' => 'T',
        '/rh/' => 'R',
        '/gn/' => 'N',
        '/qu?/' => 'K',
        '/ck/' => 'K',
        '/cc(?=[eiyE])/' => 'KS',             // accident
        '/sc(?=[eiyE])/' => 'S',              // science
        '/c(?=[eiyE])/' => 'S',
        '/c/' => 'K',
        '/gu(?=[eiyE])/' => 'G',              // guerre
        '/ge(?=[aou])/' => 'J',               // mangeons, geai
        '/g(?=[eiyE])/' => 'J',
        '/bv/' => 'V',                        // Lefebvre
        '/g/' => 'G',
        '/(?<!s)t(?=ion)/' => 'S',            // nation, mais question
        '/x/' => 'KS',
        '/w/' => 'V',
        '/z/' => 'S',
        '/(?<=[aeiouyE])ill/' => 'Y',          // travaille, feuille
        '/h/' => '',                          // h muet restant
    );

    /**
     * Voyelles : groupes d'un même son confondus, nasales comprises (une nasale
     * n'est pas suivie d'une voyelle, e muet compris, ni d'un second n ou m :
     * « dune » n'est pas nasale, « brun » l'est).
     *
     * @var array
     */
    private static $_arrVoyelles = array(
        '/eau|au/' => 'O',
        '/oin(?![aeiouyE@n])/' => 'OA3',       // loin
        '/o[iy]/' => 'OA',                    // roi, royal
        '/ou/' => 'U',
        '/(?<=i)en(?![aeiouyE@nm])/' => '3',   // bien, chien
        '/(?:ai|ei)[nm](?![aeiouyE@nm])/' => '3',  // pain, plein, faim
        '/[iuy][nm](?![aeiouyE@nm])/' => '3',  // vin, simple, brun, parfum
        '/o[nm](?![aeiouyE@nm])/' => '2',      // bon, nom
        '/[ae][nm](?![aeiouyE@nm])/' => '1',   // an, temps, enfant
        '/ai|ei|ay|ey/' => 'E',
        '/eu|oe/' => 'E',
        '/e/' => 'E',
        '/y/' => 'I',
        '/a/' => 'A',
        '/i/' => 'I',
        '/o/' => 'O',
        '/u/' => 'Y',
    );

    /**
     * Notation finale des sons : une lettre minuscule chacun
     *
     * @var array
     */
    private static $_arrSons = array(
        'A' => 'a', 'E' => 'e', 'I' => 'i', 'O' => 'o', 'U' => 'u', 'Y' => 'y',
        '1' => 'h', '2' => 'w', '3' => 'q',
        'K' => 'k', 'S' => 's', 'X' => 'x', 'F' => 'f', 'T' => 't', 'R' => 'r',
        'N' => 'n', 'G' => 'g', 'J' => 'j', 'V' => 'v',
    );

    /**
     * Code phonétique d'un mot
     *
     * @param string $strMot mot (UTF-8), casse et accents indifférents
     * @return string code (a-z, au plus 20 caractères), vide si le mot n'a aucune lettre
     */
    public static function code($strMot)
    {
        $strMot = strtr(mb_strtolower((string)$strMot, 'UTF-8'), self::$_arrTranslit);
        $strMot = preg_replace('/[^a-zE]/', '', $strMot);
        if ($strMot === '') return '';

        foreach (array(self::$_arrFinales, self::$_arrConsonnes, self::$_arrVoyelles) as $arrRegles)
            $strMot = preg_replace(array_keys($arrRegles), array_values($arrRegles), $strMot);

        // Sons restants en minuscules (b, d, l, m, n, p, r, s, t, v… inchangés), e muet retiré
        $strCode = strtolower(strtr(str_replace('@', '', $strMot), self::$_arrSons));

        // Lettres doublées fusionnées : « philippe » = « filipe »
        $strCode = preg_replace('/(.)\1+/', '$1', $strCode);

        return substr($strCode, 0, self::LONGUEUR);
    }
}
