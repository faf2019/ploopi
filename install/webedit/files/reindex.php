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
 * Réindexation du contenu des articles pour tout le module
 *
 * @package webedit
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
// Instance courante : seuls ses articles sont comptés et réindexés
$intModuleId = (int) $_SESSION['ploopi']['moduleid'];

switch($op)
{
    default:
        $db = ploopi\db::get();

        echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
        echo ploopi\skin::get()->open_simplebloc('Réindexation');

        $arrStats = array();
        foreach(array(
            'headings' => 'ploopi_mod_webedit_heading',
            'articles' => 'ploopi_mod_webedit_article',
            'files' => 'ploopi_mod_webedit_docfile',
            'tags' => 'ploopi_mod_webedit_tag'
        ) as $strStat => $strTable)
        {
            $db->query("SELECT count(*) as c FROM {$strTable} WHERE id_module = {$intModuleId}");
            $arrStats[$strStat] = ($row = $db->fetchrow()) ? (int) $row['c'] : 0;
        }

        // Mots indexés pour les articles du site (base d'indexation, éventuellement distincte)
        $idxdb = ploopi\search_index::getdb();
        $idxdb->query("
            SELECT  count(*) as c
            FROM    ploopi_index_element e
            INNER JOIN ploopi_index_keyword_element ke ON ke.id_element = e.id
            WHERE   e.id_module = {$intModuleId}
            AND     e.id_object = "._WEBEDIT_OBJECT_ARTICLE_PUBLIC."
        ");
        $arrStats['keywords'] = ($row = $idxdb->fetchrow()) ? (int) $row['c'] : 0;

        ?>
        <form method="post" action="<?php echo ploopi\crypt::urlencode('admin.php?webedit_menu=reindex&op=reindex'); ?>">
            <div style="padding:4px;">
                <input type="submit" class="button" value="Réindexer le contenu du site" />
            </div>
            <div style="padding:4px;">
                <label><input type="checkbox" name="force" value="1" /> Réenregistrer aussi chaque article (contenu nettoyé et étiquettes recalculés)</label>
            </div>
        </form>
        <div style="padding:4px;">
            Le site contient :
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['headings']; ?> rubrique(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['articles']; ?> article(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['files']; ?> lien(s) vers un fichier</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['tags']; ?> étiquette(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['keywords']; ?> mot(s) indexé(s)</strong>
        </div>
        <?php
        echo ploopi\skin::get()->close_simplebloc();
    break;

    case 'reindex':
        ini_set('max_execution_time', 0);
        include_once './modules/webedit/class_article.php';

        $db = ploopi\db::get();
        $booForce = !empty($_REQUEST['force']);

        echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
        echo ploopi\skin::get()->open_simplebloc('Réindexation');
        ?>
        <div style="padding:4px;">
        <?php
        $index_start = ploopi\timer::get()->getexectime();
        $intArticles = 0;

        $rsArticles = $db->query("SELECT id FROM ploopi_mod_webedit_article WHERE id_module = {$intModuleId}");

        while ($row = $db->fetchrow($rsArticles))
        {
            $objArticle = new webedit_article();
            if ($objArticle->open($row['id']))
            {
                if ($booForce) $objArticle->save();
                $objArticle->index();
                $intArticles++;
            }
        }
        ?>
        <?php echo $intArticles; ?> article(s) : indexation terminée en <?php printf("%.02fs", ploopi\timer::get()->getexectime() - $index_start); ?>
        <?php if ($booForce) echo "<br />Articles réenregistrés (mode « force »)"; ?>
        <br /><a title="Retour" href="<?php echo ploopi\crypt::urlencode('admin.php?webedit_menu=reindex'); ?>">Retour</a>
        </div>
        <?php
        echo ploopi\skin::get()->close_simplebloc();
    break;
}
