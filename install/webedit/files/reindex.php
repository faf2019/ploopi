<?php
/*
    Copyright (c) 2008 Ovensia
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
 * @author Stéphane Escaich
 */

switch($op)
{
    default:
        echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
        echo $skin->open_simplebloc();
        
        $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_heading WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
        $arrStats['headings'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
        
        $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_article WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
        $arrStats['articles'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
        
        $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_docfile WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
        $arrStats['files'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
        
        $db->query("SELECT count(*) as c FROM ploopi_mod_webedit_tag WHERE id_module = {$_SESSION['ploopi']['moduleid']}");
        $arrStats['tags'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
        
        $db->query( 
            "
            SELECT  count(*) as c
            FROM    ploopi_index_element e,
                    ploopi_index_keyword_element ke
            
            WHERE   e.id_module = {$_SESSION['ploopi']['moduleid']}
            AND     e.id_object = "._WEBEDIT_OBJECT_ARTICLE_PUBLIC."
            AND     ke.id_element = e.id
            "
        );
        
        $arrStats['keywords'] = ($row = $db->fetchrow()) ? $row['c'] : 0;
        
        ?>
        <div style="padding:4px;">
            <input type="button" class="button" value="Réindexer le contenu du site" onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php?webedit_menu=reindex&op=reindex'); ?>';" />
        </div>
        <div style="padding:4px;">
            Le site contient :
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['headings']; ?> rubrique(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['articles']; ?> articles(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['files']; ?> lien(s) vers un fichier</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['tags']; ?> tag(s)</strong>
        </div>
        <div style="padding:4px;">
            <strong><?php echo $arrStats['keywords']; ?> mot(s) indexé(s)</strong>
        </div>
        <?php
        echo $skin->close_simplebloc();
    break;
    
    case 'reindex':
        if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);
        include_once './modules/webedit/class_article.php';
        
        echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
        echo $skin->open_simplebloc();
        ?>
        <div style="padding:4px;">
        <?php
        $index_start = $ploopi_timer->getexectime();
        
        $rsArticles = $db->query("SELECT id FROM ploopi_mod_webedit_article");
        
        while ($row = $db->fetchrow($rsArticles))
        {
            $objArticle = new webedit_article();
            if ($objArticle->open($row['id']))
            {
                if (isset($_REQUEST['force'])) $objArticle->save();
                $objArticle->index();
            }
        }
        ?>
        indexation terminée en <?php printf("%.02fs", $ploopi_timer->getexectime() - $index_start); ?>
        <?php if (isset($_REQUEST['force'])) echo "<br />Mode 'force' activé"; ?>
        <br /><a title="Retour" href="<?php echo ploopi_urlencode('admin.php?webedit_menu=reindex'); ?>">Retour</a>
        </div>
        <?php
        echo $skin->close_simplebloc();
    break;
}
?>