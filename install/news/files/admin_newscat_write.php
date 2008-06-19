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
 * Administration des catégories - ajout/modification  
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

if ($newscat->new) echo $skin->open_simplebloc(_NEWSCAT_WRITE);
else echo $skin->open_simplebloc(str_replace("LABEL",$newscat->fields['title'],_NEWSCAT_MODIFY));
?>

<form name="form_newscat" action="<? echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="return newscat_validate(this);">
<input type="hidden" name="op" value="save_newscat">
<input type="hidden" name="newscat_id" value="<? echo $newscat->fields['id']; ?>">
<div class="ploopi_form">
    <div style="padding:2px;">
        <p>
            <label><? echo _NEWS_LABEL_TITLE; ?>:</label>
            <input class="text" type="text" name="newscat_title" value="<? echo htmlentities($newscat->fields['title']); ?>" tabindex="100" />
        </p>
        <p>
            <label><? echo _NEWS_LABEL_DESCRIPTION; ?>:</label>
            <textarea class="text" name="newscat_description" tabindex="101"><? echo htmlentities($newscat->fields['description']); ?></textarea>
        </p>
    </div>
</div>
<div style="padding:2px;text-align:right;">
    <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?newsTabItem=tabNewsCatModify"); ?>';" tabindex="103" />
    <input type="reset" class="button" value="<? echo _PLOOPI_RESET; ?>" tabindex="104" />
    <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="102" />
</div>
<? echo $skin->close_simplebloc(); ?>
