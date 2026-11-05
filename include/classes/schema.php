<?php
/*
    Copyright (c) 2007-2026 Ovensia
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

namespace ploopi;

/**
 * Migration des tables existantes, y compris celles des modules métiers.
 * @package ploopi
 * @subpackage database
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
abstract class schema
{
    /** Convertit les tables de la base courante ; une reprise ignore les tables déjà converties. */
    public static function innodb()
    {
        $objDb = db::get();
        $resTables = $objDb->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB' ORDER BY TABLE_NAME");
        if (!$resTables) throw new \RuntimeException('Impossible de lire les moteurs de stockage.');
        $arrTables = $objDb->getarray($resTables);
        $arrConverted = array();
        foreach ($arrTables as $arrTable)
        {
            $strName = $arrTable['TABLE_NAME'];
            $strQuoted = str_replace('`', '``', $strName);
            if (is_null($objDb->query("ALTER TABLE `{$strQuoted}` ENGINE=InnoDB, ROW_FORMAT=DYNAMIC")))
                throw new \RuntimeException("Conversion InnoDB impossible pour {$strName}. La version n'a pas été avancée ; corriger l'erreur puis reprendre la mise à jour.");
            $arrConverted[] = $strName;
        }
        return $arrConverted;
    }

    /** Ajoute un index absent ; sans effet à la reprise d'une mise à jour interrompue. */
    public static function index($strTable, $strIndex, $strColumns)
    {
        $objDb = db::get();
        $resIndex = $objDb->query("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '".$objDb->addslashes($strTable)."' AND INDEX_NAME = '".$objDb->addslashes($strIndex)."'");
        if (!$resIndex) throw new \RuntimeException('Impossible de lire les index.');
        if ($objDb->numrows($resIndex)) return false;
        if (is_null($objDb->query('ALTER TABLE `'.str_replace('`', '``', $strTable).'` ADD INDEX `'.str_replace('`', '``', $strIndex).'` ('.$strColumns.')')))
            throw new \RuntimeException("Index {$strIndex} impossible à créer sur {$strTable}. La version n'a pas été avancée ; corriger l'erreur puis reprendre la mise à jour.");
        return true;
    }

    /** Applique une étape de mise à jour, sans masquer une conversion ou une requête échouée. */
    public static function upgrade($strFile, $strVersion)
    {
        if (!is_readable($strFile)) throw new \RuntimeException('Fichier de migration illisible.');
        if ($strVersion === '2.1.0')
        {
            set_time_limit(0);
            self::innodb();
            // Purge du journal par le cron : sans index, chaque passage parcourt toute la table.
            self::index('ploopi_log', 'ts', 'ts');
        }
        $strSql = file_get_contents($strFile);
        $arrQueries = db::split_sql($strSql);
        // Un découpage en échec (limite PCRE) rendrait un tableau vide : ne pas avancer la version sans rien avoir exécuté.
        if (preg_last_error() !== PREG_NO_ERROR || (!$arrQueries && trim($strSql) !== ''))
            throw new \RuntimeException('Découpage du fichier de migration impossible. La version n\'a pas été avancée.');
        foreach ($arrQueries as $strQuery)
        {
            if (trim($strQuery) !== '' && is_null(db::get()->query($strQuery)))
                throw new \RuntimeException('Échec SQL pendant la migration. La mise à jour doit être reprise après correction.');
        }
    }
}
