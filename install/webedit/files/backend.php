<?php
/*
 Copyright (c) 2007-2018 Ovensia
 Copyright (c) 2009 HeXad
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
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$strbackendtype = isset($_GET['backendtype']) ? $_GET['backendtype'] : '';

switch ($strbackendtype)
{
    case 'tagcloud3D':
        /**
         * Génération du fichier XML pour le nuage de tags en fonction des articles publiés
         */

        $query_tag = (empty($_REQUEST['query_tag'])) ? '' : $_REQUEST['query_tag'];

        $objCache = new ploopi\cache($query_tag.'-'.$_GET['ploopi_moduleid'].'.xml', 300);

        // Vidage du buffer
        ploopi\buffer::clean();

        if (!$objCache->start())
        {
            include_once './modules/webedit/include/global.php';

            $today = ploopi\date::createtimestamp();

            $strBasePath = _PLOOPI_BASEPATH;
            if (substr($strBasePath, -1) != '/') $strBasePath .= '/';

            // récupération des rubriques
            $arrHeadings = webedit_getheadings($_GET['ploopi_moduleid']);

            // récupération des partages (mode connecté uniquement)
            $arrShares = webedit_getshare(null, $_GET['ploopi_moduleid']);

            $sql =  "
                    SELECT      t.tag, a.id_heading
                    FROM        ploopi_mod_webedit_tag t

                    INNER JOIN  ploopi_mod_webedit_article_tag at
                    ON          at.id_tag = t.id

                    INNER JOIN  ploopi_mod_webedit_article a
                    ON          at.id_article = a.id

                    WHERE       t.id_module = {$_GET['ploopi_moduleid']}
                    AND         (a.timestp_published <= $today OR a.timestp_published = 0)
                    AND         (a.timestp_unpublished >= $today OR a.timestp_unpublished = 0)
                    ";

            ploopi\db::get()->query($sql);

            $arrTags = array();
            while ($row = ploopi\db::get()->fetchrow())
            {
                if (!$arrHeadings['list'][$row['id_heading']]['private']
                    || isset($arrShares[$arrHeadings['list'][$row['id_heading']]['herited_private']])
                    || isset($_SESSION['webedit']['allowedheading'][$_GET['ploopi_moduleid']][$arrHeadings['list'][$row['id_heading']]['herited_private']])) // Rubrique non privée ou accessible par l'utilisateur
                {
                    $strTag = strtolower(ploopi\str::convertaccents($row['tag']));
                    if (!isset($arrTags[$strTag])) $arrTags[$strTag] = 0;
                    $arrTags[$strTag]++;
                }
            }

            // Tri en fonction du nombre d'apparition du tag
            arsort($arrTags);

            // Valeur max d'apparition
            $intMax = current($arrTags);

            // Calcul de la taille d'affichage de chaque tag
            $intMinSize = 8;
            foreach($arrTags as $strTag => &$row)
            {
                $row = array(
                    'nb' => $row,
                    'size' => round(25 * $row / $intMax)
                );

                if ($row['size'] < $intMinSize) $row['size'] = $intMinSize;
            }

            echo '<tags>';

            foreach ($arrTags as $strTag => $arrTag)
            {
                $link = $strBasePath.ploopi\str::urlrewrite("index.php?query_tag={$strTag}", webedit_getrewriterules());
                //$class = ($strTag == $query_tag) ? 'tagcloud3D_selected' : 'tagcloud3D';
                echo '<a href="'.$link.'" title="'.$strTag.'" rel="tag" style="font-size: '.$arrTag['size'].'pt;" >'.ploopi\str::htmlentities($strTag).'</a>';
            }
            echo '</tags>';

            $objCache->end();
        }

        header('Content-Type: text/xml');
        ploopi\system::kill();
        break;

    case 'feedRssAtom' :

        // Format du flux (RSS / ATOM)
        $format = (empty($_REQUEST['format'])) ? 'atom' : $_REQUEST['format'];

        // Mise en cache
        $objCache = new ploopi\cache($format.(isset($_REQUEST['headingid']) ? "-h{$_REQUEST['headingid']}" : '').'.xml', 300);

        // Vidage du buffer
        ploopi\buffer::clean();

        if (!$objCache->start())
        {
            /**
             * La classe heading
             */
            include_once './modules/webedit/class_heading.php';

            /**
             * FeedWriter qui permet de générer le flux
             */
            // include_once './lib/feedwriter/FeedWriter.php';

            ploopi\module::init('webedit', false, false, false);

            // récupération des rubriques
            $arrHeadings = webedit_getheadings();

            $intTsToday = ploopi\date::createtimestamp();

            $feed_title = '';
            $feed_description = '';
            $feed_author = '';

            $objWorkspace = new ploopi\workspace();
            if ($objWorkspace->open($_SESSION['ploopi']['workspaceid'])) {
                $feed_title = $objWorkspace->fields['title'];
                $feed_description = $objWorkspace->fields['meta_description'];
                $feed_author = $objWorkspace->fields['meta_author'];
            }

            // Si une rubrique est définie, le flux porte le titre de la rubrique
            if (isset($_REQUEST['headingid']))
            {
                $objHeading = new webedit_heading();
                if ($objHeading->open($_REQUEST['headingid']))
                {
                    $where = "AND (heading.id = {$objHeading->fields['id']} OR heading.parents = '{$objHeading->fields['parents']};{$objHeading->fields['id']}' OR heading.parents LIKE '{$objHeading->fields['parents']};{$objHeading->fields['id']};%')";
                    $feed_title = trim(trim($feed_title.' - '.$objHeading->fields['label'], ' -'));
                    $feed_description = $objHeading->fields['description'];
                }
                else
                {
                    ploopi\output::h404();
                    ploopi\system::kill();
                }
            }
            else // sinon du site
            {
                $where = '';
            }

            switch($format)
            {
                case 'rss';
                    $feed = new FeedWriter\RSS2();
                break;

                default:
                case 'atom';
                    $feed = new FeedWriter\ATOM();
                break;
            }

            $feed->setTitle(ploopi\str::xmlentities(utf8_encode($feed_title), true));
            $feed->setLink(_PLOOPI_BASEPATH);

            $feed->setChannelElement('updated', date(DATE_ATOM , time()));
            $feed->setChannelElement('author', array('name '=> ploopi\str::xmlentities(utf8_encode($feed_author), true)));


            $select = "
                SELECT      article.id_heading, article.metatitle, article.title, article.id, article.metakeywords, article.timestp, article.metadescription
                FROM        ploopi_mod_webedit_article article,
                            ploopi_mod_webedit_heading heading
                WHERE       article.id_module = {$_SESSION['ploopi']['moduleid']}
                AND         article.id_heading = heading.id
                AND         heading.feed_enabled = 1
                {$where}
                AND         (article.timestp_published <= {$intTsToday} OR article.timestp_published = 0)
                AND         (article.timestp_unpublished >= {$intTsToday} OR article.timestp_unpublished = 0)
                ORDER BY    article.timestp DESC
                LIMIT       0,10
            ";

            ploopi\db::get()->query($select);

            while ($article = ploopi\db::get()->fetchrow())
            {
                if (!$arrHeadings['list'][$article['id_heading']]['private'])
                {
                    if (empty($article['metatitle'])) $article['metatitle'] = $article['title'];

                    $arrParents = array();
                    if (isset($arrHeadings['list'][$article['id_heading']])) foreach(preg_split('/;/', $arrHeadings['list'][$article['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

                    $url = ploopi\str::urlrewrite("index.php?headingid={$article['id_heading']}&articleid={$article['id']}", webedit_getrewriterules(), "{$article['metatitle']} {$article['metakeywords']}", $arrParents);

                    // Création d'un nouvel item
                    $item = $feed->createNewItem();

                    $item->setTitle(ploopi\str::xmlentities(utf8_encode($article['title']), true));
                    $item->setLink(_PLOOPI_BASEPATH.'/'.$url);
                    $item->setDate(ploopi\date::timestamp2unixtimestamp($article['timestp']));
                    $item->setDescription(ploopi\str::nl2br(ploopi\str::htmlentities($article['metadescription'])));

                    // Ajout de l'item dans le flux
                    $feed->addItem($item);
                }
            }

            // Génération du flux
            $feed->printFeed();

            $objCache->end();
        }

        header('Content-Type: text/xml');
        ploopi\system::kill();
        break;

    default:
        break;
}

?>
