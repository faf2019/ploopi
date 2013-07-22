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
 * Administration - ajout/modification d'une catégorie de flux
 *
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Formulaire d'ajout/modification d'une catégorie de flux
 */

if ($rsscat->new) echo $skin->open_simplebloc(_RSS_LABEL_CATADD);
else echo $skin->open_simplebloc(str_replace('LABEL',$rsscat->fields['title'],_RSS_LABEL_CATMODIFY));
?>

<form name="form_rsscat" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return rsscat_validate(this);">
<input type="hidden" name="op" value="rsscat_save">
<input type="hidden" name="rsscat_id" value="<?php echo $rsscat->fields['id']; ?>">
<div class="ploopi_form">
    <div style="padding:2px;">
        <p>
            <label><?php echo _RSS_LABEL_TITLE; ?>:</label>
            <input class="text" type="text" name="rsscat_title" value="<?php echo ploopi_htmlentities($rsscat->fields['title']); ?>" tabindex="100" />
        </p>
        <p>
            <label><?php echo _RSS_LABEL_LIMIT; ?>:</label>
            <input class="text" type="text" name="rsscat_limit" style="width:50px;" value="<?php echo $rsscat->fields['limit']; ?>" tabindex="101" /><?php echo _RSS_COMMENT_O_NOLIMIT; ?>
        </p>
        <p>
            <label><?php echo _RSS_LABEL_TPL_TAG; ?>:</label>
            <input class="text" type="text" name="rsscat_tpl_tag" style="width:200px;" value="<?php echo $rsscat->fields['tpl_tag']; ?>" tabindex="102" /><br/>
            <label>&nbsp;</label><?php echo _RSS_COMMENT_CAT_TPL_TAG; ?><br/>
            <label>&nbsp;</label><?php echo _RSS_COMMENT_WARNING_TPL_TAG; ?>
        </p>

        <p>
            <label><?php echo _RSS_LABEL_DESCRIPTION; ?>:</label>
            <textarea class="text" name="rsscat_description" tabindex="103"><?php echo ploopi_htmlentities($rsscat->fields['description']); ?></textarea>
        </p>
    </div>
</div>
<div style="padding:2px;text-align:right;">
    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?rssTabItem=tabCatList"); ?>';" tabindex="103" />
    <input type="reset" class="button" value="<?php echo _PLOOPI_RESET; ?>" tabindex="104" />
    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="102" />
</div>
<?php echo $skin->close_simplebloc(); ?>
