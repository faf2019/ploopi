<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
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
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Formulaire d'ajout/modification d'un flux
 */

if ($rssfeed->new) echo $skin->open_simplebloc(_RSS_LABEL_FEEDADD);
else echo $skin->open_simplebloc(ploopi_htmlentities(str_replace('LABEL',$rssfeed->fields['title'],_RSS_LABEL_FEEDMODIFY)));

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

<form name="form_rssfeed" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return rssfeed_validate(this);">
<input type="hidden" name="op" value="rssfeed_save">
<input type="hidden" name="rssfeed_id" value="<?php echo $rssfeed->fields['id']; ?>">
<div class="ploopi_form">
    <div style="padding:2px;">
        <p>
            <label><?php echo _RSS_LABEL_FEEDURL; ?>:</label>
            <input class="text" type="text" name="rssfeed_url" value="<?php echo ploopi_htmlentities($rssfeed->fields['url']); ?>" tabindex="100" />
        </p>
        <p>
            <label><?php echo _RSS_LABEL_CATEGORY; ?>:</label>
            <select class="select" name="rssfeed_id_cat">
                <option value="0"><?php echo _RSS_LABEL_NOCATEGORY; ?></option>
                <?php
                foreach($a_categories as $row)
                {
                    ?>
                    <option <?php if ($rssfeed->fields['id_cat'] == $row['id']) echo 'selected'; ?> value="<?php echo $row['id']; ?>"><?php echo ploopi_htmlentities($row['title']); ?></option>
                    <?php
                }
                ?>
            </select>
        </p>
        <p>
            <label><?php echo _RSS_LABEL_FEED_RENEW; ?>:</label>
            <select class="select" name="rssfeed_revisit">
                <?php
                foreach($rss_revisit_values as $key => $value)
                {
                    ?>
                    <option <?php if ($rssfeed->fields['revisit'] == $key) echo 'selected'; ?> value="<?php echo $key; ?>"><?php echo ploopi_htmlentities($value); ?></option>
                    <?php
                }
                ?>
            </select>
        </p>
        <p>
            <label><?php echo _RSS_LABEL_FEEDLIMIT; ?>:</label>
            <input class="text" type="text" name="rssfeed_limit" style="width:50px;" value="<?php echo ploopi_htmlentities($rssfeed->fields['limit']); ?>" tabindex="103" /><?php echo _RSS_COMMENT_O_NOLIMIT; ?>
        </p>
        <p>
            <label><?php echo _RSS_LABEL_TPL_TAG; ?>:</label>
            <input class="text" type="text" name="rssfeed_tpl_tag" style="width:200px;" value="<?php echo ploopi_htmlentities($rssfeed->fields['tpl_tag']); ?>" tabindex="102" /><br/>)
            <label>&nbsp;</label><?php echo _RSS_COMMENT_FEED_TPL_TAG; ?><br/>
            <label>&nbsp;</label><?php echo _RSS_COMMENT_WARNING_TPL_TAG; ?>
        </p>
    </div>
</div>
<div style="padding:2px;text-align:right;">
    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?rssTabItem=tabFeedList"); ?>';" tabindex="105" />
    <input type="reset" class="button" value="<?php echo _PLOOPI_RESET; ?>" tabindex="106" />
    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="104" />
</div>
<?php echo $skin->close_simplebloc(); ?>
