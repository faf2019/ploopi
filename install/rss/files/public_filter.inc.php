<?php
/*
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
 * Filtre sur les flux
 *
 * @package rss
 * @subpackage public
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

echo $skin->open_simplebloc(_RSS_LABEL_FILTER_FEED);

if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = '';
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';

?>
<div style="float:right;width:60%;">
    <div id="rss_filter_feed"></div>
</div>

<div style="float:left;width:40%;">
    <div id="rss_filter_list"></div>
    <div id="rss_filter_element_list"></div>
</div>
<script type="text/javascript">
ploopi_window_onload_stock(rss_filter_list_get);
<?php
if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']>0)
{
  ?>
  ploopi_window_onload_stock(rss_filter_element_list_get);
  <?php
}
?>
</script>

<?php echo $skin->close_simplebloc(); ?>