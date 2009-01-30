<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Affichage du backend des pages publiées en frontoffice
 *
 * @package webedit
 * @subpackage backend
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusions des fonctions sur les dates et les chaînes (l'appel via backend.php est minimal, les fonctions ne sont donc pas déjà incluses)
 */
include_once './include/functions/date.php';
include_once './include/functions/string.php';

/**
 * La classe heading
 */
include_once './modules/webedit/class_heading.php';

/**
 * FeedWriter qui permet de générer le flux
 */
include_once './lib/feedwriter/FeedWriter.php';

$module_name =  $row['label'];

$today = ploopi_createtimestamp();

// Format du flux (RSS / ATOM)
$format = (empty($_REQUEST['format'])) ? 'atom' : $_REQUEST['format']; 

// Si une rubrique est définie, le flux porte le titre de la rubrique
if (isset($_REQUEST['headingid']))
{
    $objHeading = new webedit_heading();
    if ($objHeading->open($_REQUEST['headingid']))
    {
        $where = "AND (heading.id = {$objHeading->fields['id']} OR heading.parents = '{$objHeading->fields['parents']};{$objHeading->fields['id']}' OR heading.parents LIKE '{$objHeading->fields['parents']};{$objHeading->fields['id']};%')";
        $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'].' - '.$objHeading->fields['label'];
        $feed_description = $objHeading->fields['description'];
    }
    else 
    {
        ploopi_h404();
        ploopi_die();
    }
}
else // sinon du site
{
    $where = '';
    $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'];
    $feed_description = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description'];
}

$select = "
    SELECT      article.*
    FROM        ploopi_mod_webedit_article article,
                ploopi_mod_webedit_heading heading
    WHERE       article.id_module = {$ploopi_moduleid}
    AND         article.id_heading = heading.id 
    AND         heading.feed_enabled = 1
    {$where}
    AND         (article.timestp_published <= {$today} OR article.timestp_published = 0)
    AND         (article.timestp_unpublished >= {$today} OR article.timestp_unpublished = 0)
    ORDER BY    article.timestp DESC
    LIMIT       0,10
";

$db->query($select);
$articles = $db->getarray();

switch($format)
{
    case 'rss';
        $feedformat = RSS2;
    break;
    
    default:
    case 'atom';
        $feedformat = ATOM;
    break;
}

$feed = new FeedWriter($feedformat);

$feed->setTitle(ploopi_xmlentities(utf8_encode($feed_title), true));
$feed->setLink(_PLOOPI_BASEPATH);

$feed->setChannelElement('updated', date(DATE_ATOM , time()));
$feed->setChannelElement('author', array('name '=> ploopi_xmlentities(utf8_encode($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']), true)));
    
foreach($articles as $key => $article)
{
    if (empty($article['metatitle'])) $article['metatitle'] = $article['title'];
    $url = ploopi_urlrewrite("index.php?headingid={$article['id_heading']}&articleid={$article['id']}", "{$article['metatitle']} {$article['metakeywords']}");
    
    // Création d'un nouvel item
    $item = $feed->createNewItem();
    
    $item->setTitle(ploopi_xmlentities(utf8_encode($article['title']), true));
    $item->setLink(_PLOOPI_BASEPATH.'/'.$url);
    $item->setDate(ploopi_timestamp2unixtimestamp($article['timestp']));
    $item->setDescription(ploopi_nl2br(htmlentities($article['metadescription'])));

    // Ajout de l'item dans le flux
    $feed->addItem($item);
}
    
// Vidage du buffer (par sécurité car il doit être vide...)
ploopi_ob_clean();

// Génération du flux
$feed->generateFeed();
?>
