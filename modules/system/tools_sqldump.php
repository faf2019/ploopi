<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Outil permettant de créer un dump SQL de la base de données sans passer par les outils en ligne de commande.
 * Attention, ce script n'est pas adaptés aux grosses bases de données.
 *
 * @package system
 * @subpackage system_tools
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Nettoyage des buffers actifs
 */
ploopi\buffer::clean();

@set_time_limit(0);
$crlf = "\n";

/**
 * Structure d'une table : définition exacte du serveur (SHOW CREATE TABLE),
 * moteur, jeu de caractères et index compris.
 */
function system_get_table_def($fp, $table, $crlf)
{
    $db = ploopi\db::get();

    $rs = $db->query("SHOW CREATE TABLE `{$table}`");
    if (!($row = $db->fetchrow($rs, MYSQLI_NUM))) return false;

    fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;{$crlf}{$row[1]};{$crlf}{$crlf}");

    return true;
}

/**
 * Données d'une table : un INSERT par enregistrement, valeurs échappées par le serveur
 */
function system_get_table_content($fp, $table, $crlf)
{
    $db = ploopi\db::get();

    $rs = $db->query("SELECT * FROM `{$table}`");
    while($row = $db->fetchrow($rs, MYSQLI_NUM))
    {
        @set_time_limit(60);

        $arrValues = array();
        foreach($row as $mixValue) $arrValues[] = is_null($mixValue) ? 'NULL' : "'".$db->addslashes($mixValue)."'";

        fwrite($fp, "INSERT INTO `{$table}` VALUES (".implode(', ', $arrValues).");{$crlf}");
    }

    return true;
}

/**
 * listtables() renvoie les lignes de SHOW TABLES : le nom est leur seule colonne
 */
$tables = array();
foreach((array) ploopi\db::get()->listtables() as $row) $tables[] = is_array($row) ? current($row) : $row;

if (empty($tables)) ploopi\system::kill();

/**
 * Génération du fichier SQL dans le dossier data/tmp
 */

$filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
ploopi\fs::makedir($filepath);

$filename_sql = tempnam($filepath, 'dump_sql');
$filename_zip = tempnam($filepath, 'dump_zip');

$fp = fopen($filename_sql, 'w');

fwrite($fp, "# MySQL-Dump{$crlf}");
fwrite($fp, "#{$crlf}");
fwrite($fp, "# Host: "._PLOOPI_DB_SERVER);
fwrite($fp, " Database: "._PLOOPI_DB_DATABASE.$crlf);
fwrite($fp, $crlf."SET NAMES utf8mb4;{$crlf}SET FOREIGN_KEY_CHECKS = 0;{$crlf}");

foreach($tables as $table)
{
    fwrite($fp, $crlf);
    fwrite($fp, "# --------------------------------------------------------{$crlf}");
    fwrite($fp, "#{$crlf}");
    fwrite($fp, "# Structure '{$table}'{$crlf}");
    fwrite($fp, "#{$crlf}");
    fwrite($fp, $crlf);

    system_get_table_def($fp, $table, $crlf);

    fwrite($fp, "#{$crlf}");
    fwrite($fp, "# Data '{$table}'{$crlf}");
    fwrite($fp, "#{$crlf}");
    fwrite($fp, $crlf);

    system_get_table_content($fp, $table, $crlf);
}

fwrite($fp, $crlf."SET FOREIGN_KEY_CHECKS = 1;{$crlf}");
fclose($fp);

/**
 * Génération du fichier zip
 */

$zip = new ZipArchive();

// OVERWRITE : tempnam() a créé un fichier vide, que libzip refuse comme archive
if ($zip->open($filename_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true)
{
    if (!$zip->addFile($filename_sql, 'dump.sql')) exit('Erreur lors de l\'enregistrement');
    $zip->close();

    // Le fichier SQL n'est plus utile ; l'archive est supprimée après l'envoi
    unlink($filename_sql);

    ploopi\fs::downloadfile($filename_zip, 'dump.zip', true, true);
}

ploopi\system::kill();
