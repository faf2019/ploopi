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
 * Fonctions, constantes, variables globales
 *
 * @package wiki
 * @subpackage global
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Définition des constantes
 */

/**
 * Action : MODIFIER
 */

define ('_WIKI_ACTION_PAGE_MODIFY',     10);

/**
 * Action : RENOMMER
 */

define ('_WIKI_ACTION_PAGE_RENAME',     11);

/**
 * Action : SUPPRIMER
 */

define ('_WIKI_ACTION_PAGE_DELETE',     12);

/**
 * Action : LOCKER
 */

define ('_WIKI_ACTION_PAGE_LOCK',       20);

/**
 * Action : DELOCKER
 */

define ('_WIKI_ACTION_PAGE_UNLOCK',     21);

/**
 * Objet : PAGE
 */

define ('_WIKI_OBJECT_PAGE',            1);


/**
 * Traitement des liens internes par expression régulière
 *
 * @param array $arrMatches Tableau des correspondances
 * @return string lien modifié
 */
function wiki_internal_links($arrMatches)
{
    if (!empty($arrMatches[1]))
    {
        $strPageId = strip_tags($arrMatches[1]);

        $objWikiPage = new wiki_page();
        if ($objWikiPage->open($strPageId))
        {
            $strLinkClass = 'wiki_link';
            $strTitle = 'Ouvrir la page &laquo; '.htmlentities($strPageId).' &raquo;';
            $strOp = '';
        }
        else
        {
            $strLinkClass = 'wiki_link_notfound';
            $strTitle = 'Créer la page &laquo; '.htmlentities($strPageId).' &raquo;';
            $strOp = 'op=wiki_page_modify&';
        }

        return '<span class="'.$strLinkClass.'"><a title="'.$strTitle.'" href="'.ploopi_urlencode("admin.php?{$strOp}wiki_page_id={$strPageId}").'">'.$arrMatches[1].'</a><img src="./modules/wiki/img/ico_link.png" /></span>';
    }

    return '';
}

/**
 * Traitement des liens externes et ancres (href) par expression regulière
 *
 * @param array $arrMatches Tableau des correspondances
 * @return string lien modifié
 */
function wiki_links($arrMatches)
{
    if (sizeof($arrMatches) == 3)
    {
        switch($arrMatches[1][0]) // On regarde le 1er caractère du lien
        {
            case '#': // cas particulier : ancre
                return '<span class="wiki_link_ext"><a title="Aller à l\'ancre &laquo; '.htmlentities(strip_tags($arrMatches[2])).' &raquo;" href="'.$arrMatches[1].'">'.$arrMatches[2].'</a><img src="./modules/wiki/img/ico_link_anchor.png" /></span>';
            break;

            default: // autres cas : liens externes
                return '<span class="wiki_link_ext"><a title="Ouvrir le lien externe &laquo; '.htmlentities(strip_tags($arrMatches[1])).' &raquo;" href="'.$arrMatches[1].'">'.$arrMatches[2].'</a><img src="./modules/wiki/img/ico_link_ext.png" /></span>';
            break;

        }
    }

    return '';
}

function wiki_render($strContent)
{
    include_once './lib/textile/classTextile.php';
    $objTextile = new Textile;
    $strTextile = $objTextile->TextileThis($strContent);

    $strTextile = preg_replace_callback ('/<a[^>]*href="(.*)"[^>]*>(.*)<\/a>/i', 'wiki_links', $strTextile);
    $strTextile = preg_replace_callback ('/\[\[(.*)\]\]/i', 'wiki_internal_links', $strTextile);

    return $strTextile;
}
?>
