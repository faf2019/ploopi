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
 * Suite 05 — contrôles sur les modules livrés dans install/.
 *
 * Les fonctions sont extraites des sources du paquet plutôt que d'une instance déployée :
 * install/<module>/files/ est la source de vérité du packaging (doc/reference.md §2), et les
 * modules concernés ne sont pas nécessairement installés sur l'instance éprouvée.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();
require_once PLOOPI_ROOT.'/vendor/autoload.php';

/**
 * Charge les fonctions nommées d'un fichier de module, sans exécuter le reste.
 */
function charger_fonctions($strFichier, $arrFonctions)
{
    $strSource = file_get_contents(PLOOPI_ROOT.'/'.$strFichier);

    foreach ($arrFonctions as $strFonction)
    {
        if (function_exists($strFonction)) continue;
        if (preg_match('/\n *function '.preg_quote($strFonction, '/').'\(.*?\n\}/s', $strSource, $arrMatches)) eval($arrMatches[0]);
    }
}

charger_fonctions('install/wiki/files/include/global.php',
    array('wiki_safe_url', 'wiki_links', 'wiki_make_links_cb', 'wiki_make_links', 'wiki_render', 'wiki_highlight'));

if (!function_exists('wiki_internal_links')) { function wiki_internal_links($arrMatches) { return '[['.$arrMatches[1].']]'; } }

T::section('Wiki — le contenu d\'une page ne doit pas exécuter de script');
if (!function_exists('wiki_render'))
{
    T::skip('rendu du wiki', 'wiki_render() introuvable dans install/wiki/files/include/global.php');
}
else
{
    foreach (array(
        'balise script'             => '<script>alert(1)</script>',
        'gestionnaire onerror'      => '<img src=x onerror=alert(1)>',
        'onmouseover en attribut'   => '<a href="x" onmouseover="alert(1)" y="">t</a>',
        'iframe'                    => '<iframe src="//evil.test"></iframe>',
        'balise dans texte de lien' => '<a href="http://ok.test"><script>alert(1)</script></a>',
        'svg onload'                => '<svg onload=alert(1)>',
        'style actif'               => '<div style="background:url(javascript:alert(1))">t</div>',
    ) as $strLabel => $strCharge)
        T::chk('neutralisé : '.$strLabel, !preg_match('/<script|<iframe|<svg|onerror|onmouseover|onload|javascript:/i', wiki_render($strCharge)));

    T::section('Wiki — le rendu légitime reste intact');
    foreach (array(
        'titre'             => array('h2. Mon titre', '<h2>Mon titre</h2>'),
        'gras et italique'  => array('Du *gras* et du _italique_.', '<strong>gras</strong>'),
        'liste'             => array("* un\n* deux", '<li>un</li>'),
        'lien Textile'      => array('"Ovensia":http://ovensia.fr', 'href="http://ovensia.fr"'),
        'URL nue'           => array('Voir http://exemple.test/page', 'href="http://exemple.test/page"'),
        'tableau'           => array("|_. A |_. B |\n| 1 | 2 |", '<th>A </th>'),
        'bloc de code'      => array('<code class="php">echo "test";</code>', 'hl-content'),
        'lien interne'      => array('Voir [[MaPage]] aussi', '[[MaPage]]'),
    ) as $strLabel => $arrCas)
        T::chk('conservé : '.$strLabel, strpos(wiki_render($arrCas[0]), $arrCas[1]) !== false);

    T::section('Wiki — assainissement des URL de lien');
    foreach (array('http://ok.test/a', '#ancre', '/relatif', 'mailto:a@b.test') as $strUrl)
        T::chk('conservée : '.$strUrl, wiki_safe_url($strUrl) === $strUrl);
    foreach (array('javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'java&#115;cript:alert(1)', 'data:text/html,x', 'vbscript:x') as $strUrl)
        T::chk('rejetée : '.substr($strUrl, 0, 24), wiki_safe_url($strUrl) === '#');
    T::chk('guillemet échappé', strpos(wiki_safe_url('http://ok.test/?a="onx="'), '"') === false);
}


exit(T::bilan());
