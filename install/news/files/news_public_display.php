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
?>
<?
if (!empty($_GET['news_id']) && is_numeric($_GET['news_id']) && $news->open($_GET['news_id']))
{
    if (ploopi_set_flag('news_nbclick',$_GET['news_id'])) $news->fields['nbclick']++;
    $news->save();


    echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
    echo $skin->open_simplebloc($news->fields['title']);

    $localdate = ploopi_timestamp2local($news->fields['date_publish']);

    $source = ($news->fields['source']=='') ? _NEWS_LABEL_UNKNOWN : $news->fields['source'];

    $newscat = new newscat();
    $cat = ($newscat->open($news->fields['id_cat'])) ? $newscat->fields['title'] : _NEWS_LABEL_UNKNOWN;

    ?>

    <div class="news">
        <div><b>Publi� le</b> <? echo $localdate['date']; ?> � <? echo $localdate['time']; ?>
        <?
        $user = new user();
        if ($user->open($news->fields['id_user'])) echo " par {$user->fields['firstname']} {$user->fields['lastname']}";
        ?>
        </div>
        <div><b><? echo _NEWS_LABEL_CATEGORY ?></b>:&nbsp;<? echo $cat; ?></div>
        <div><b><? echo _NEWS_LABEL_SOURCE; ?></b>:&nbsp;<? echo $source; ?></div>
        <?
        if ($news->fields['url']!='')
        {
            if ($news->fields['urltitle'] == '') $urltitle = _NEWS_LABEL_URL;
            else $urltitle = $news->fields['urltitle'];
            ?>
                <div><b><? echo _NEWS_LABEL_URL; ?></b>:&nbsp;<a target="_blank" href="<? echo $news->fields['url']; ?>"><? echo $urltitle; ?></a>
            <?
        }
        ?>
        <div><b><? echo _NEWS_LABEL_READS; ?></b>:&nbsp;<? echo $news->fields['nbclick']; ?></div>
        <div><? echo $news->fields['content']; ?></div>


    </div>
    <div style="clear:both;border-top:1px solid #c0c0c0;">
        <? ploopi_annotation(_NEWS_OBJECT_NEWS, $news->fields['id'], $news->fields['title']); ?>
    </div>
    <?
}



