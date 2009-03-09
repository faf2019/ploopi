<?php
/*
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
 * Outils d'administration (réindexation pour le moment)
 *
 * @package rss
 * @subpackage admin
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

echo $skin->open_simplebloc(_RSS_LABEL_TOOLS);

if (isset($_GET['end']))
{
    echo '<div style="padding:4px;">'._RSS_MESS_REINDEX.'</div>';
}
?>

<div style="padding:4px;">
<input type="button" class="button" value="<?php echo _RSS_LABEL_REINDEX; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=reindex"); ?>';">
</div>

<?php echo $skin->close_simplebloc(); ?>
