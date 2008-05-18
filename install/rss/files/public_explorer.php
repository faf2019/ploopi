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
 * Explorateur de flux
 *
 * @package rss
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Affichage de l'explorateur sous forme de petits blocs.
 * Les blocs sont ensuite remplis à l'aide de requêtes ajax.
 */

echo $skin->open_simplebloc(_RSS_LABEL_FEEDEXPLORER);

if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] = '';
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] = '';

if (isset($_REQUEST['rss_search_kw'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'] = $_REQUEST['rss_search_kw'];
$rss_search_kw = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'] : '';

if (substr($rss_search_kw,0,6) == 'entry:') $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] = $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] = '';

?>
<div style="float:right;width:60%;">
    <div id="rss_explorer_feed"></div>
</div>

<div style="float:left;width:40%;">
    <div id="rss_explorer_search">
    <? echo $skin->open_simplebloc(); ?>
    <form action="" method="post" onsubmit="javascript:rss_explorer_feed_get(this.rss_search_kw.value); return false;">
    <div style="padding:4px;font-weight:bold;">Mots Clés : <input type="text" class="text" name="rss_search_kw" value="<? echo htmlentities($rss_search_kw, ENT_QUOTES); ?>"/>&nbsp;<input type="submit" class="button" value="Filtrer"></div>
    </form>
    <? echo $skin->close_simplebloc(); ?>
    </div>
    <div id="rss_explorer_catlist"></div>
    <div id="rss_explorer_feedlist"></div>
</div>

<script type="text/javascript">
ploopi_window_onload_stock(rss_explorer_catlist_get);
<?
if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id']))
{
    ?>
    ploopi_window_onload_stock(rss_explorer_feedlist_get);
    <?
}
?>
</script>

<? echo $skin->close_simplebloc(); ?>
