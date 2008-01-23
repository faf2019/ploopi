<?php
/*
----------------------------------------------------------------------------------
PhpDig Version 1.8.x - See the config file for the full version number.
This program is provided WITHOUT warranty under the GNU/GPL license.
See the LICENSE file for more information about the GNU/GPL license.
Contributors are listed in the CREDITS and CHANGELOG files in this package.
Developer from inception to and including PhpDig v.1.6.2: Antoine Bajolet
Developer from PhpDig v.1.6.3 to and including current version: Charter
Copyright (C) 2001 - 2003, Antoine Bajolet, http://www.toiletoine.net/
Copyright (C) 2003 - current, Charter, http://www.phpdig.net/
Contributors hold Copyright (C) to their code submissions.
Do NOT edit or remove this copyright or licence information upon redistribution.
If you modify code and redistribute, you may ADD your copyright to this notice.
----------------------------------------------------------------------------------
*/

// prevent caching code from php.net
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0

$relative_script_path = '..';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";

// extract vars
extract( phpdigHttpVars(
     array('message'=>'string')
     ),EXTR_SKIP);

?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : <?php phpdigPrnMsg('admin') ?></title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<div align='center'>
<table border="0"><tr><td>
 <a href="<?php print $relative_script_path."/".SEARCH_PAGE ?>"><img src="../phpdig_logo_2.png" width="200" height="114" alt="PhpDig <?php print PHPDIG_VERSION ?>" border="0" /></a> 
PhpDig v.<?php print PHPDIG_VERSION ?> <?php phpdigPrnMsg('admin_panel'); ?><br />
 </td><td>
 <div align='center'>
<?php
$phpdig_tables = array('sites'=>'Hosts','spider'=>'Pages','engine'=>'Index','keywords'=>'Keywords','tempspider'=>'Temporary table');
print "<table class=\"borderCollapse\">\n";
print "<tr><td class=\"greyFormDark\" colspan='2' align='center'><b>".phpdigMsg('databasestatus')."</b></td></tr>\n";
while (list($table,$name) = each($phpdig_tables))
       {
       $result = mysql_fetch_array(mysql_query("SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."$table"),MYSQL_ASSOC);
       print "<tr>\n\t<td class=\"greyFormLight\">\n$name : </td>\n\t<td class=\"greyForm\">\n<b>".$result['num']."</b>".phpdigMsg('entries')."</td>\n</tr>\n";
       }
print "</table>\n";
?>
 </div>
</td></tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr><td valign="top">
<h3><?php phpdigPrnMsg('index_uri') ?></h3>
<form class="grey" action="spider.php" method="post">
<?php phpdigPrnMsg('one_per_line') ?><br/>
<textarea name="url" rows="9" cols="50" wrap="virtual">
http://
http://
http://
</textarea>
<br/>
<?php phpdigPrnMsg('use_vals_from'); ?> <a href="limit_update.php"><?php phpdigPrnMsg('upd_sites'); ?></a> 
<?php phpdigPrnMsg('table_present'); ?>
<input type="radio" name="usetable" value="yes"> <?php phpdigPrnMsg('yes'); ?> 
<input type="radio" name="usetable" value="no" checked> <?php phpdigPrnMsg('no'); ?> 
<br/><br/>
<?php phpdigPrnMsg('default_vals') ?><hr>
<?php phpdigPrnMsg('spider_depth') ?> :
<select class="phpdigSelect" name="limit">
<?php
//select list for the depth limit of spidering
for($i = 0; $i <= SPIDER_MAX_LIMIT; $i++) {
    print "\t<option value=\"$i\">$i</option>\n";
} ?>
</select>
<?php phpdigPrnMsg('links_per') ?> :
<select class="phpdigSelect" name="linksper">
<?php
//select list for the max links per each depth
for($i = 0; $i <= LINKS_MAX_LIMIT; $i++) {
    print "\t<option value=\"$i\">$i</option>\n";
} ?>
</select><hr>
<input type="submit" name="spider" value="<?php phpdigPrnMsg('digthis'); ?>" />
</form>
</td><td valign="top">
<div align='center'>
<h3><?php phpdigPrnMsg('site_update') ?></h3>
<form action="update_frame.php" method="post">
<select class="phpdigSelect" name="site_ids[]" multiple="multiple" size="10">
<?php
//list of sites in the database
$query = "SELECT site_id,site_url,port,locked FROM ".PHPDIG_DB_PREFIX."sites ORDER BY site_url";
$result_id = mysql_query($query,$id_connect);
while (list($id,$url,$port,$locked) = mysql_fetch_row($result_id))
    {
    if ($port)
        $url .= " (port #$port)";
    if ($locked) {
        $url = '*'.phpdigMsg('locked').'* '.$url;
    }
    print "\t<option value='$id'>$url</option>\n";
    }
?>
</select>
<br/>
<input type="submit" name="update" value="<?php phpdigPrnMsg('updateform'); ?>" />
<input type="submit" name="delete" value="<?php phpdigPrnMsg('deletesite'); ?>" />
</form>
</div>
<p class='grey'>
<a href="cleanup_engine.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_index'); ?></a><br/>
<a href="cleanup_keywords.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_dic'); ?></a><br/>
<a href="cleanup_common.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_stopw'); ?></a><br/>
<a href="cleanup_dashes.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_dash'); ?></a><br/>
<a href="limit_update.php"><?php print phpdigMsg('upd_sites'); ?></a><br/>
<a href="statistics.php"><?php print phpdigMsg('statistics') ?></a><br/>
<a href="stop_spider.php?stop=1"><?php print phpdigMsg('StopSpider') ?></a><br/>
<a href="logout.php"><?php print phpdigMsg('logout') ?></a>
</p>
</td>
</tr>
<tr>
<td colspan="2">
<p class="grey">
<?php phpdigPrnMsg('admin_msg_1'); ?><br/>
<?php phpdigPrnMsg('admin_msg_2'); ?><br/>
<?php phpdigPrnMsg('admin_msg_3'); ?><br/>
<?php phpdigPrnMsg('admin_msg_4'); ?><br/>
<?php phpdigPrnMsg('admin_msg_5'); ?><br/>
</p>
<p class="blue">
<?php if ($message) { phpdigPrnMsg($message); } ?>
</p>
</td>
</tr>
</table>
</div>
</body>
</html>
