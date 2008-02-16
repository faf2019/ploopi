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
if ($news->new) echo $skin->open_simplebloc(_NEWS_WRITE);
else echo $skin->open_simplebloc(str_replace("LABEL",$news->fields['title'],_NEWS_MODIFY));
?>

<form name="form_news" action="<? echo $scriptenv; ?>" method="post" onsubmit="return news_validate(this);">
<input type="hidden" name="op" value="save_news">
<input type="hidden" name="news_id" value="<? echo $news->fields['id']; ?>">
<div class="ploopi_form">
    <div style="padding:2px;">
        <p>
            <label><? echo _NEWS_LABEL_TITLE; ?>:</label>
            <input class="text" type="text" name="news_title" value="<? echo htmlentities($news->fields['title']); ?>">
        </p>
        <p>
            <label><? echo _NEWS_LABEL_SOURCE; ?>:</label>
            <input class="text" type="text" name="news_source" value="<? echo htmlentities($news->fields['source']); ?>">
        </p>
        <p>
            <div style="float:left;width:30%;text-align:right;margin:0;padding: 0 .5em 0 0;"><? echo _NEWS_LABEL_CONTENT; ?>:</div>
            <div style="float:left;width:65%;">
            <?
            include_once('./FCKeditor/fckeditor.php') ;

            $oFCKeditor = new FCKeditor('fck_news_content') ;

            $basepath = dirname($_SERVER['HTTP_REFERER']); // compatible with proxy rewrite
            if ($basepath == '/') $basepath = '';

            $oFCKeditor->BasePath = "{$basepath}/FCKeditor/";

            // default value
            $oFCKeditor->Value = $news->fields['content'];

            // width & height
            $oFCKeditor->Width='100%';
            $oFCKeditor->Height='350';

            $oFCKeditor->Config['CustomConfigurationsPath'] = "{$basepath}/modules/news/fckeditor/fckconfig.js"  ;
            $oFCKeditor->Config['SkinPath'] = "{$basepath}/modules/news/fckeditor/skins/default/" ;
            $oFCKeditor->Config['EditorAreaCSS'] = "{$basepath}/modules/news/fckeditor/fck_editorarea.css" ;
            $oFCKeditor->Config['BaseHref'] = "{$basepath}/";
            $oFCKeditor->Create('FCKeditor_1') ;
            ?>
            </div>
        </p>
        <p>
            <label><? echo _NEWS_LABEL_CATEGORY; ?>:</label>
            <select class="select" name="news_id_cat">
            <option value="0"><? echo _NEWS_LABEL_NOCATEGORY; ?></option>
            <?
            $select = "SELECT * FROM ploopi_mod_news_cat WHERE id_module = ".$_SESSION['ploopi']['moduleid']." ORDER BY title";
            $answer = $db->query($select);
            while ($fields = $db->fetchrow($answer))
            {
                if  ($fields['id']==$news->fields['id_cat']) $sel = "selected";
                echo "<option {$sel} value=\"".$fields['id']."\">".$fields['title']."</option>";
            }
            ?>
            </select>
        </p>
        <p>
            <label><? echo _NEWS_LABEL_URL; ?>:</label>
            <input class="text" type="text" name="news_url" value="<? echo htmlentities($news->fields['url']); ?>">
        </p>
        <p>
            <label><? echo _NEWS_LABEL_URLTITLE; ?>:</label>
            <input class="text" type="text" name="news_urltitle" value="<? echo htmlentities($news->fields['urltitle']); ?>">
        </p>
        <? $localdate = (!empty($news->fields['date_publish'])) ? ploopi_timestamp2local($news->fields['date_publish']) : array('date' => ploopi_getdate(), 'time' => ploopi_gettime()); ?>
        <p>
            <label><? echo _NEWS_LABEL_PUBLISHDATE; ?>:</label>
            <input class="text" type="text" id="news_date_publish" name="news_date_publish" value="<? echo $localdate['date']; ?>" style="width:100px;">
            <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('news_date_publish', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
        </p>
        <p>
            <label><? echo _NEWS_LABEL_PUBLISHTIME; ?>:</label>
            <input class="text" type="text" name="newsx_time_publish" value="<? echo $localdate['time']; ?>" style="width:100px;">
        </p>
        <p>
            <label><? echo _NEWS_LABEL_HOT; ?>:</label>
            <select class="select" name="news_hot" style="width:50px;">
                <option <? if ($news->fields['hot'] == 0) echo 'selected'; ?> value="0"><? echo _PLOOPI_NO; ?></option>
                <option <? if ($news->fields['hot'] == 1) echo 'selected'; ?> value="1"><? echo _PLOOPI_YES; ?></option>
            </select>
        </p>
    </div>
</div>
<div style="padding:2px;text-align:right;">
    <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>">
</div>
<? echo $skin->close_simplebloc(); ?>
