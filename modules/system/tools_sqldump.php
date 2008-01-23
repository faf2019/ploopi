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
ob_end_clean();
if (!ini_get('safe_mode')) @set_time_limit(0);
$crlf="\n";

header("Cache-control: private");
header("Content-disposition: filename="._PLOOPI_DB_DATABASE.".sql");
header("Content-type: application/octetstream");
header("Pragma: public");
header("Expires: 0");


// return $table's CREATE definition
// returns a string containing the CREATE statement on success
function get_table_def($db, $table, $crlf)
{
    global $drop;

    $schema_create = "";
    if(!empty($drop))
        $schema_create .= "DROP TABLE IF EXISTS $table;$crlf";

    $schema_create .= "CREATE TABLE $table ($crlf";

    //$result = mysql_db_query($db, "SHOW FIELDS FROM $table") or mysql_ploopi_die();
    $result = $db->query("SHOW FIELDS FROM $table");
    while($row = $db->fetchrow($result))
    {
        $schema_create .= "   $row[Field] $row[Type]";

        if(isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"] == "0"))
            $schema_create .= " DEFAULT '$row[Default]'";
        if($row["Null"] != "YES")
            $schema_create .= " NOT NULL";
        if($row["Extra"] != "")
            $schema_create .= " $row[Extra]";
        $schema_create .= ",$crlf";
    }
    $schema_create = ereg_replace(",".$crlf."$", "", $schema_create);
    //$result = mysql_db_query($db, "SHOW KEYS FROM $table") or mysql_ploopi_die();
    $result = $db->query("SHOW KEYS FROM $table");
    while($row = $db->fetchrow($result))
    {
        $kname=$row['Key_name'];
        if(($kname != "PRIMARY") && ($row['Non_unique'] == 0))
            $kname="UNIQUE|$kname";
         if(!isset($index[$kname]))
             $index[$kname] = array();
         $index[$kname][] = $row['Column_name'];
    }

    if (!empty($index))
    while(list($x, $columns) = @each($index))
    {
         $schema_create .= ",$crlf";
         if($x == "PRIMARY")
             $schema_create .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
         elseif (substr($x,0,6) == "UNIQUE")
            $schema_create .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
         else
            $schema_create .= "   KEY $x (" . implode($columns, ", ") . ")";
    }

    $schema_create .= "$crlf)";
    return (stripslashes($schema_create));
}

// Get the content of $table as a series of INSERT statements.
// After every row, a custom callback function $handler gets called.
// $handler must accept one parameter ($sql_insert);
function get_table_content($db, $table, $handler)
{
    //$result = mysql_db_query($db, "SELECT * FROM $table") or mysql_ploopi_die();
    $result = $db->query("SELECT * FROM $table");
    $i = 0;
    while($row = $db->fetchrow($result,MYSQL_NUM))
    {
		if (!ini_get('safe_mode')) @set_time_limit(60);
        $table_list = "(";

        for($j=0; $j<$db->numfields($result);$j++)
            $table_list .= $db->fieldname($result,$j).", ";

        $table_list = substr($table_list,0,-2);
        $table_list .= ")";

        if(isset($GLOBALS["showcolumns"]))
            $schema_insert = "INSERT INTO $table $table_list VALUES (";
        else
            $schema_insert = "INSERT INTO $table VALUES (";

        for($j=0; $j<$db->numfields($result);$j++)
        {
            if(!isset($row[$j]))
                $schema_insert .= " NULL,";
            elseif($row[$j] != "")
                $schema_insert .= " '".addslashes($row[$j])."',";
            else
                $schema_insert .= " '',";
        }
        $schema_insert = ereg_replace(",$", "", $schema_insert);
        $schema_insert .= ")";
        $handler(trim($schema_insert));
        $i++;
    }
    return (true);
}




// doing some DOS-CRLF magic...
$client = getenv("HTTP_USER_AGENT");
if(ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
{
$os = $regs[1];
// this looks better under WinX
if (eregi("Win",$os))
    $crlf="\r\n";
}

function my_handler($sql_insert)
{
    global $crlf, $asfile;

    if(empty($asfile))
    {
        echo htmlspecialchars("$sql_insert;$crlf");
    }
    else
    {
        echo "$sql_insert;$crlf";
    }
}

$tables = $db->listtables();

//$sql ="SHOW tables from `$db`";
//$sql ="SELECT * FROM ploopi_user";
//$tables = mysql_query($sql);

$num_tables = $db->numrows($tables);
if($num_tables == 0)
{
    echo $strNoTablesFound;
}
else
{

    $i = 0;
    print "# MySQL-Dump$crlf";
    print "#$crlf";
    print "# Host: "._PLOOPI_DB_SERVER;
    print " Database: "._PLOOPI_DB_DATABASE."$crlf";

    while($i < $num_tables)
    {
        $table = $db->tablename($tables, $i);

        $strTableStructure = '';

        print $crlf;
        print "# --------------------------------------------------------$crlf";
        print "#$crlf";
        print "# $strTableStructure '$table'$crlf";
        print "#$crlf";
        print $crlf;

        echo get_table_def($db, $table, $crlf).";$crlf$crlf";

		$strDumpingData = '';

		print "#$crlf";
		print "# $strDumpingData '$table'$crlf";
		print "#$crlf";
		print $crlf;

		get_table_content($db, $table, "my_handler");
        $i++;
    }
}

ploopi_die();
?>
