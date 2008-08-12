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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package news
 * @subpackage template
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('news');

//$groups = ploopi_viewworkspaces($template_moduleid,'web');
//$sqllimitgroup = " AND ploopi_mod_news_entry.id_workspace IN ($groups)";
$sqllimitgroup = '';

$news_select =  "
                SELECT      ploopi_mod_news_entry.*, 
                            ploopi_mod_news_cat.title as titlecat 
                FROM        ploopi_mod_news_entry 
                LEFT JOIN   ploopi_mod_news_cat ON ploopi_mod_news_cat.id = ploopi_mod_news_entry.id_cat 
                WHERE       ploopi_mod_news_entry.id_module = {$template_moduleid}
                {$sqllimitgroup}
                AND     ploopi_mod_news_entry.published = 1
                ORDER BY    date_publish desc 
                ";
//              LIMIT       0,".$_SESSION['ploopi']['modules'][$template_moduleid]['nbnewsdisplay'];


$news_result = $db->query($news_select);

while ($news_fields = $db->fetchrow($news_result))
{
    $localdate = ploopi_timestamp2local($news_fields['date_publish']);
    
    $template_body->assign_block_vars("news" , array(
                        'ID' => $news_fields['id'],
                        'TITLE' => $news_fields['title'],
                        'CONTENT' => $news_fields['content'],
                        'HOT' => $news_fields['hot'],
                        'DATE' => $localdate['date'],
                        'TIME' => $localdate['time'],
                        'URL' => $news_fields['url'],
                        'URLTITLE' => $news_fields['urltitle'],
                        'LINK' => "modcontent={$template_moduleid}&newsid={$news_fields['id']}"
                        )
                    );

    $template_body->assign_block_vars("news{$template_moduleid}" , array(
                        'ID' => $news_fields['id'],
                        'TITLE' => $news_fields['title'],
                        'CONTENT' => $news_fields['content'],
                        'HOT' => $news_fields['hot'],
                        'DATE' => $localdate['date'],
                        'TIME' => $localdate['time'],
                        'URL' => $news_fields['url'],
                        'URLTITLE' => $news_fields['urltitle'],
                        'LINK' => "modcontent={$template_moduleid}&newsid={$news_fields['id']}"
                        )
                    );
    
}
?>

