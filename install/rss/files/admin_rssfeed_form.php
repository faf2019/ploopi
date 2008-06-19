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
 * Administration - modification d'un flux RSS
 * 
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Formulaire d'ajout/modification d'un flux
 */

if ($rssfeed->new) echo $skin->open_simplebloc(_RSS_LABEL_FEEDADD);
else echo $skin->open_simplebloc(str_replace('LABEL',$rssfeed->fields['title'],_RSS_LABEL_FEEDMODIFY));

$sql =  "
        SELECT  *
        FROM    ploopi_mod_rss_cat
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        AND     id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).")
        ORDER BY title
        ";
$db->query($sql);
$a_categories = $db->getarray();

?>

<form name="form_rssfeed" action="<? echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return rssfeed_validate(this);">
<input type="hidden" name="op" value="rssfeed_save">
<input type="hidden" name="rssfeed_id" value="<? echo $rssfeed->fields['id']; ?>">
<div class="ploopi_form">
    <div style="padding:2px;">
        <p>
            <label><? echo _RSS_LABEL_FEEDURL; ?>:</label>
            <input class="text" type="text" name="rssfeed_url" value="<? echo htmlentities($rssfeed->fields['url']); ?>" tabindex="100" />
        </p>
        <p>
            <label><? echo _RSS_LABEL_CATEGORY; ?>:</label>
            <select class="select" name="rssfeed_id_cat">
                <option value="0"><? echo _RSS_LABEL_NOCATEGORY; ?></option>
                <?
                foreach($a_categories as $row)
                {
                    ?>
                    <option <? if ($rssfeed->fields['id_cat'] == $row['id']) echo 'selected'; ?> value="<? echo $row['id']; ?>"><? echo htmlentities($row['title']); ?></option>
                    <?
                }
                ?>
            </select>
        </p>
        <p>
            <label><? echo _RSS_LABEL_DEFAULT; ?>:</label>
            <select class="select" name="rssfeed_default">
                <option <? if ($rssfeed->fields['default'] == 0) echo 'selected'; ?> value="0"><? echo _PLOOPI_NO; ?></option>
                <option <? if ($rssfeed->fields['default'] == 1) echo 'selected'; ?> value="1"><? echo _PLOOPI_YES; ?></option>
            </select>
        </p>
        <p>
            <label><? echo _RSS_LABEL_FEED_RENEW; ?>:</label>
            <select class="select" name="rssfeed_revisit">
                <?
                foreach($rss_revisit_values as $key => $value)
                {

                    ?>
                    <option <? if ($rssfeed->fields['revisit'] == $key) echo 'selected'; ?> value="<? echo $key; ?>"><? echo $value; ?></option>
                    <?
                }
                ?>
            </select>
        </p>
    </div>
</div>
<div style="padding:2px;text-align:right;">
    <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?rssTabItem=tabFeedList"); ?>';" tabindex="103" />
    <input type="reset" class="button" value="<? echo _PLOOPI_RESET; ?>" tabindex="104" />
    <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="102" />
</div>
<? echo $skin->close_simplebloc(); ?>
