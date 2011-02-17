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
 * Outil permettant de cr�er un dump SQL de la base de donn�es sans passer par les outils en ligne de commande.
 * Attention, ce script n'est pas adapt�s aux grosses bases de donn�es.
 *
 * @package system
 * @subpackage system_tools
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Nettoyage des buffers actifs
 */
ploopi_ob_clean();

if (!ini_get('safe_mode')) @set_time_limit(0);
$crlf = "\n";

/*
header("Cache-control: private");
header("Content-disposition: filename="._PLOOPI_DB_DATABASE.".sql");
header("Content-type: text/x-sql");
header("Pragma: public");
header("Expires: 0");
*/

// return $table's CREATE definition
// returns a string containing the CREATE statement on success
function system_get_table_def($fp, $db, $table, $crlf)
{
    $schema_create = "";
    $schema_create .= "DROP TABLE IF EXISTS {$table};{$crlf}";
    $schema_create .= "CREATE TABLE {$table} ({$crlf}";

    //$result = mysql_db_query($db, "SHOW FIELDS FROM $table") or mysql_ploopi_die();
    $result = $db->query("SHOW FIELDS FROM {$table}");
    while($row = $db->fetchrow($result))
    {
        $schema_create .= "   {$row['Field']} {$row['Type']}";

        if(isset($row['Default']) && (!empty($row['Default']) || $row['Default'] == "0"))
            $schema_create .= " DEFAULT '{$row['Default']}'";
        if($row['Null'] != "YES")
            $schema_create .= ' NOT NULL';
        if($row['Extra'] != '')
            $schema_create .= " {$row['Extra']}";
        $schema_create .= ",{$crlf}";
    }
    $schema_create = preg_replace("/,".$crlf."$/", "", $schema_create);
    //$result = mysql_db_query($db, "SHOW KEYS FROM $table") or mysql_ploopi_die();
    $result = $db->query("SHOW KEYS FROM {$table}");
    while($row = $db->fetchrow($result))
    {
        $kname=$row['Key_name'];
        if(($kname != "PRIMARY") && ($row['Non_unique'] == 0))
            $kname="UNIQUE|{$kname}";
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

    $schema_create .= "{$crlf});{$crlf}{$crlf}";

    fwrite ($fp, stripslashes($schema_create));

    return true;
}

// Get the content of $table as a series of INSERT statements.
function system_get_table_content($fp, $db, $table, $crlf)
{
    $result = $db->query("SELECT * FROM $table");
    $i = 0;
    while($row = $db->fetchrow($result, MYSQL_NUM))
    {
        if (!ini_get('safe_mode')) @set_time_limit(60);
        $table_list = "(";

        for($j=0; $j<$db->numfields($result);$j++)
            $table_list .= $db->fieldname($result,$j).", ";

        $table_list = substr($table_list,0,-2);
        $table_list .= ")";

        if(isset($GLOBALS["showcolumns"]))
            $schema_insert = "INSERT INTO {$table} {$table_list} VALUES (";
        else
            $schema_insert = "INSERT INTO {$table} VALUES (";

        for($j=0; $j<$db->numfields($result);$j++)
        {
            if(!isset($row[$j]))
                $schema_insert .= " NULL,";
            elseif($row[$j] != "")
                $schema_insert .= " '".$db->addslashes($row[$j])."',";
            else
                $schema_insert .= " '',";
        }
        $schema_insert = preg_replace("/,$/", "", $schema_insert);
        $schema_insert .= ")";
        fwrite ($fp, htmlspecialchars(trim($schema_insert).";{$crlf}"));
        $i++;
    }

    return true;
}

$tables = $db->listtables();


if(empty($tables)) echo $strNoTablesFound;
else
{
    /**
     * G�n�ration du fichier SQL dans le dossier data/tmp
     */

    $filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
    ploopi_makedir($filepath);

    $filename_sql = tempnam($filepath, 'dump_sql');
    $filename_zip = tempnam($filepath, 'dump_zip');
    
    $fp = fopen($filename_sql, 'w');
    
    $i = 0;
    fwrite($fp, "# MySQL-Dump{$crlf}");
    fwrite($fp, "#{$crlf}");
    fwrite($fp, "# Host: "._PLOOPI_DB_SERVER);
    fwrite($fp, " Database: "._PLOOPI_DB_DATABASE.$crlf);

    foreach($tables as $table)
    {
        fwrite($fp, $crlf);
        fwrite($fp, "# --------------------------------------------------------{$crlf}");
        fwrite($fp, "#{$crlf}");
        fwrite($fp, "# Structure '{$table}'{$crlf}");
        fwrite($fp, "#{$crlf}");
        fwrite($fp, $crlf);

        //fwrite($fp, system_get_table_def($db, $table, $crlf).";{$crlf}{$crlf}");
        system_get_table_def($fp, $db, $table, $crlf);

        fwrite($fp, "#{$crlf}");
        fwrite($fp, "# Data '{$table}'{$crlf}");
        fwrite($fp, "#{$crlf}");
        fwrite($fp, $crlf);

        //fwrite($fp, system_get_table_content($db, $table));
        system_get_table_content($fp, $db, $table, $crlf);
        $i++;
    }

    fclose($fp);
    
    /**
     * G�n�ration du fichier zip
     */

    
    $zip = new ZipArchive();
    
    if ($zip->open($filename_zip, ZIPARCHIVE::CREATE) === true)
    {
        if (!$zip->addFile($filename_sql, 'dump.sql')) exit('Erreur lors de l\'enregistrement');
        $zip->close();

        unlink($filename_zip);

        ploopi_downloadfile($filename_zip, 'dump.zip', true, true);
    }
}


ploopi_die();
?>
