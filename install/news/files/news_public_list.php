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
 * Affichage de la liste des news
 *
 * @package news
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion de la classe utilisateur
 */

include_once './include/classes/user.php';

echo $skin->create_pagetitle(ploopi_htmlentities($_SESSION['ploopi']['modulelabel']));

/**
 * Recherche des news du module
 */

$select =   "
            SELECT      ploopi_mod_news_entry.*,
                        IFNULL(ploopi_mod_news_cat.title, '"._NEWS_LABEL_UNKNOWN."') as titlecat
            FROM        ploopi_mod_news_entry
            LEFT JOIN   ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat
            WHERE       ploopi_mod_news_entry.id_module = ".$_SESSION['ploopi']['moduleid']."
            AND         ploopi_mod_news_entry.id_workspace IN (".ploopi_viewworkspaces().")
            AND         ploopi_mod_news_entry.published = 1
            ORDER BY    titlecat,
                        date_publish desc
            ";

$result = $db->query($select);

$opened=false;
$titlecat="";

if ($db->numrows()>0)
{
    while ($fields = $db->fetchrow($result))
    {
        $source = ($fields['source']=='') ? _NEWS_LABEL_UNKNOWN : $fields['source'];

        $localdate = ploopi_timestamp2local($fields['date_publish']);

        echo $skin->open_simplebloc(ploopi_htmlentities($fields['title']));

        ?>
        <div class="news">
            <div><b>Publi� le</b> <?php echo ploopi_htmlentities($localdate['date']); ?> � <?php echo ploopi_htmlentities($localdate['time']); ?>
            <?php
            $user = new user();
            if ($user->open($fields['id_user'])) echo ploopi_htmlentities(" par {$user->fields['firstname']} {$user->fields['lastname']}");
            ?>
            </div>
            <div><b><?php echo _NEWS_LABEL_CATEGORY ?></b>:&nbsp;<?php echo ploopi_htmlentities($fields['titlecat']); ?></div>
            <div><b><?php echo _NEWS_LABEL_SOURCE; ?></b>:&nbsp;<?php echo ploopi_htmlentities($source); ?></div>
            <?php
            if ($fields['url']!='')
            {
                if ($fields['urltitle']=='') $urltitle = _NEWS_LABEL_URL;
                else $urltitle = $fields['urltitle'];
                ?>
                    <div><b><?php echo _NEWS_LABEL_URL; ?></b>:&nbsp;<a target="_blank" href="<?php echo $fields['url']; ?>"><?php echo ploopi_htmlentities($urltitle); ?></a></div>
                <?php
            }
            ?>
            <div><b><?php echo _NEWS_LABEL_READS; ?></b>:&nbsp;<?php echo $fields['nbclick']; ?></div>
            <div><?php echo ploopi_htmlentities($fields['content']); ?></div>
        </div>
        <div style="clear:both;border-top:1px solid #c0c0c0;">
            <?php ploopi_annotation(_NEWS_OBJECT_NEWS, $fields['id'], $fields['title']); ?>
        </div>
        <?php
        echo $skin->close_simplebloc();
    }
}
else
{
    echo $skin->open_simplebloc();
    ?>
    <div class="news">
        <div>Aucune news</div>
    </div>
    <?php
    echo $skin->close_simplebloc();
}
?>
