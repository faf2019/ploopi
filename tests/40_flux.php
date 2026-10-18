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

/**
 * Tests unitaires de ploopi\feed, qui remplace mibe/feedwriter (non maintenu depuis
 * 2023, obsolète sous PHP 8.4 et 8.5, GPL-3.0) pour les flux RSS et Atom de webedit
 * et de la GED.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();

use ploopi\feed;

/**
 * @param string $strXml flux
 * @return DOMXPath|null null si le XML est mal formé
 */
function flux_xpath($strXml)
{
    $objDom = new DOMDocument();
    if (!@$objDom->loadXML($strXml)) return null;
    $objXpath = new DOMXPath($objDom);
    $objXpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

    return $objXpath;
}

$intDate = mktime(14, 30, 0, 5, 13, 2024);

/**
 * Un flux de deux articles, avec ce qu'un contenu réel peut porter : esperluettes,
 * chevrons, guillemets, caractère de contrôle (interdit en XML), HTML dans le résumé.
 */
$funcFlux = function($strFormat) use ($intDate) {
    $objFeed = new feed($strFormat);
    $objFeed->setTitle('Mon site - Actualités');
    $objFeed->setLink('http://exemple.fr');
    $objFeed->setDescription('Arts & "lettres" <neuves>');
    $objFeed->setAuthor('Équipe Ovensia');
    $objFeed->setDate($intDate);
    $objFeed->addItem('Été & <printemps>', 'http://exemple.fr/web/article-1.html', $intDate, '<p>Résumé <b>gras</b> ]]> fin</p>');
    $objFeed->addItem("Contrôle\x01interdit", 'http://exemple.fr/web/article-2.html?a=1&b=2', $intDate - 3600);

    return $objFeed->render();
};

T::section('Atom 1.0');
$strAtom = $funcFlux(feed::ATOM);
$objX = flux_xpath($strAtom);
T::vrai('XML bien formé (caractère de contrôle retiré)', $objX !== null, $strAtom);
if ($objX)
{
    T::eq('racine feed dans l\'espace de noms Atom', 1, $objX->query('/a:feed')->length);
    T::eq('titre échappé puis restitué', 'Mon site - Actualités', $objX->evaluate('string(/a:feed/a:title)'));
    T::eq('sous-titre', 'Arts & "lettres" <neuves>', $objX->evaluate('string(/a:feed/a:subtitle)'));
    T::eq('lien du site', 'http://exemple.fr', $objX->evaluate('string(/a:feed/a:link/@href)'));
    T::eq('date au format Atom', date(DATE_ATOM, $intDate), $objX->evaluate('string(/a:feed/a:updated)'));
    T::eq('auteur', 'Équipe Ovensia', $objX->evaluate('string(/a:feed/a:author/a:name)'));
    /**
     * Identifiants repris de FeedWriter (urn:uuid tiré du md5 du titre du flux, du lien
     * de l'article) : un lecteur de flux ne revoit pas les articles déjà lus comme neufs.
     */
    T::eq('identifiant du flux inchangé (FeedWriter)', 'urn:uuid:a1f501e1-3694-5468-03d7-464af67a86b7', $objX->evaluate('string(/a:feed/a:id)'));
    T::eq('deux entrées', 2, $objX->query('/a:feed/a:entry')->length);
    T::eq('identifiant d\'un article inchangé (FeedWriter)', 'urn:uuid:9be7386d-51c2-cd58-2c6c-2185e3ec722e', $objX->evaluate('string(/a:feed/a:entry[1]/a:id)'));
    T::eq('titre d\'article', 'Été & <printemps>', $objX->evaluate('string(/a:feed/a:entry[1]/a:title)'));
    T::eq('lien d\'article avec &', 'http://exemple.fr/web/article-2.html?a=1&b=2', $objX->evaluate('string(/a:feed/a:entry[2]/a:link/@href)'));
    T::eq('résumé déclaré HTML', 'html', $objX->evaluate('string(/a:feed/a:entry[1]/a:summary/@type)'));
    T::eq('résumé HTML restitué tel quel, « ]]> » compris', '<p>Résumé <b>gras</b> ]]> fin</p>', $objX->evaluate('string(/a:feed/a:entry[1]/a:summary)'));
    T::eq('pas de résumé vide', 0, $objX->query('/a:feed/a:entry[2]/a:summary')->length);
    T::eq('date d\'article', date(DATE_ATOM, $intDate - 3600), $objX->evaluate('string(/a:feed/a:entry[2]/a:updated)'));
}

T::section('RSS 2.0');
$strRss = $funcFlux(feed::RSS);
$objX = flux_xpath($strRss);
T::vrai('XML bien formé', $objX !== null, $strRss);
if ($objX)
{
    T::eq('racine rss 2.0', '2.0', $objX->evaluate('string(/rss/@version)'));
    T::eq('titre', 'Mon site - Actualités', $objX->evaluate('string(/rss/channel/title)'));
    T::eq('lien', 'http://exemple.fr', $objX->evaluate('string(/rss/channel/link)'));
    T::eq('description', 'Arts & "lettres" <neuves>', $objX->evaluate('string(/rss/channel/description)'));
    T::eq('date de construction au format RSS', date(DATE_RSS, $intDate), $objX->evaluate('string(/rss/channel/lastBuildDate)'));
    T::eq('aucun élément Atom égaré dans le canal (updated, author)', 0, $objX->query('/rss/channel/updated | /rss/channel/author')->length);
    T::eq('deux articles', 2, $objX->query('/rss/channel/item')->length);
    T::eq('lien d\'article', 'http://exemple.fr/web/article-1.html', $objX->evaluate('string(/rss/channel/item[1]/link)'));
    T::eq('guid = lien, permanent', 'http://exemple.fr/web/article-1.html', $objX->evaluate('string(/rss/channel/item[1]/guid[@isPermaLink="true"])'));
    T::eq('date de publication', date(DATE_RSS, $intDate), $objX->evaluate('string(/rss/channel/item[1]/pubDate)'));
    T::eq('description HTML restituée', '<p>Résumé <b>gras</b> ]]> fin</p>', $objX->evaluate('string(/rss/channel/item[1]/description)'));
}

T::section('Valeurs par défaut et entrées dégradées');
$objFeed = new feed('inconnu');
$objX = flux_xpath($objFeed->render());
T::eq('format inconnu : Atom', 1, $objX ? $objX->query('/a:feed')->length : 0);
T::vrai('titre vide : flux tout de même servi, titre de repli', $objX && $objX->evaluate('string(/a:feed/a:title)') !== '');
$objFeed = new feed(feed::RSS);
$objFeed->setTitle("Octets \xff invalides");
$objFeed->addItem('', 'http://exemple.fr/x');
T::vrai('UTF-8 invalide : XML bien formé', flux_xpath($objFeed->render()) !== null);
T::eq('type MIME Atom', 'application/atom+xml', feed::mimetype(feed::ATOM));
T::eq('type MIME RSS', 'application/rss+xml', feed::mimetype(feed::RSS));

T::section('FeedWriter retiré');
T::vrai('plus de mibe/feedwriter dans composer.json', strpos(file_get_contents(PLOOPI_ROOT.'/composer.json'), 'mibe/feedwriter') === false);
foreach (array('install/webedit/files/backend.php', 'install/doc/files/backend.php') as $strFichier)
{
    T::vrai("{$strFichier} : ploopi\\feed, sans FeedWriter", strpos(file_get_contents(PLOOPI_ROOT.'/'.$strFichier), 'FeedWriter') === false && strpos(file_get_contents(PLOOPI_ROOT.'/'.$strFichier), 'ploopi\\feed') !== false);
}

exit(T::bilan('40_flux'));
