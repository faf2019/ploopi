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
 * Administration des news - ajout/modification
 *
 * @package news
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Affichage du titre en fonction du type d'opération (ajout/modif)
 */

if ($news->new) echo $skin->open_simplebloc(_NEWS_WRITE);
else echo $skin->open_simplebloc(str_replace("LABEL",$news->fields['title'],_NEWS_MODIFY));
?>

<form name="form_news" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return news_validate(this);">
<input type="hidden" name="op" value="save_news">
<input type="hidden" name="news_id" value="<?php echo $news->fields['id']; ?>">
<div>
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:2px;">
            <p>
                <label><?php echo _NEWS_LABEL_TITLE; ?>:</label>
                <input class="text" type="text" name="news_title" value="<?php echo ploopi_htmlentities($news->fields['title']); ?>">
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_CATEGORY; ?>:</label>
                <select class="select" name="news_id_cat">
                <option value="0"><?php echo _NEWS_LABEL_NOCATEGORY; ?></option>
                <?php
                $select = "SELECT * FROM ploopi_mod_news_cat WHERE id_module = ".$_SESSION['ploopi']['moduleid']." ORDER BY title";
                $answer = $db->query($select);
                while ($fields = $db->fetchrow($answer))
                {
                    ?>
                    <option <?php if ($fields['id'] == $news->fields['id_cat']) echo 'selected="selected"'; ?> value="<?php echo $fields['id']; ?>"><?php echo ploopi_htmlentities($fields['title']); ?></option>
                    <?php
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_SOURCE; ?>:</label>
                <input class="text" type="text" name="news_source" value="<?php echo ploopi_htmlentities($news->fields['source']); ?>">
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_HOT; ?>:</label>
                <select class="select" name="news_hot" style="width:50px;">
                    <option <?php if ($news->fields['hot'] == 0) echo 'selected'; ?> value="0"><?php echo _PLOOPI_NO; ?></option>
                    <option <?php if ($news->fields['hot'] == 1) echo 'selected'; ?> value="1"><?php echo _PLOOPI_YES; ?></option>
                </select>
            </p>
        </div>
    </div>

    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:2px;">
            <?php $localdate = (!empty($news->fields['date_publish'])) ? ploopi_timestamp2local($news->fields['date_publish']) : array('date' => ploopi_getdate(), 'time' => ploopi_gettime()); ?>
            <p>
                <label><?php echo _NEWS_LABEL_PUBLISHDATE; ?>:</label>
                <input class="text" type="text" id="news_date_publish" name="news_date_publish" value="<?php echo $localdate['date']; ?>" style="width:100px;">
                <?php ploopi_open_calendar('news_date_publish'); ?>
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_PUBLISHTIME; ?>:</label>
                <input class="text" type="text" name="newsx_time_publish" value="<?php echo $localdate['time']; ?>" style="width:100px;">
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_URL; ?>:</label>
                <input class="text" type="text" name="news_url" value="<?php echo ploopi_htmlentities($news->fields['url']); ?>">
            </p>
            <p>
                <label><?php echo _NEWS_LABEL_URLTITLE; ?>:</label>
                <input class="text" type="text" name="news_urltitle" value="<?php echo ploopi_htmlentities($news->fields['urltitle']); ?>">
            </p>
        </div>
    </div>
</div>
<div style="padding:0 2px;">
<?php
include_once './include/functions/fck.php';

$arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/modules/news/fckeditor/fckconfig.js';
$arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.'/modules/news/fckeditor/fck_editorarea.css';

ploopi_fckeditor('fck_news_content', $news->fields['content'], '100%', '350', $arrConfig);
?>
</div>

<div style="padding:2px;text-align:right;">
    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
<?php echo $skin->close_simplebloc(); ?>
