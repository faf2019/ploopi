<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

use ploopi;

/**
 * Classe de gestion des recordsets retournés par ploopi_query
 */
class recordset
{
    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    private $objDb;

    /**
     * Recordset courant
     *
     * @var resource
     */
    private $resRs;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     * @param resource $resRs Recordset
     */
    public function __construct($objDb, $resRs)
    {
        $this->objDb = $objDb;
        $this->resRs = $resRs;
    }

    /**
     * Retourne l'enregistrement courant du recordset et avance le pointeur sur l'enregistrement suivant
     *
     * @return array
     */
    public function fetchrow()
    {
        return $this->objDb->fetchrow($this->resRs);
    }

    /**
     * Retourne le nombre d'enregistrements du recordset
     *
     * @return integer
     */
    public function numrows()
    {
        return $this->objDb->numrows($this->resRs);
    }

    /**
     * Déplace le pointeur interne sur un enregistrement particulier
     *
     * @param integer $intPos position dans le recordset
     * @return boolean true si le déplacement a été effectué sinon false
     */
    public function dataseek($intPos = 0)
    {
        return $this->objDb->dataseek($this->resRs, $intPos);
    }

    /**
     * Retourne dans un tableau le contenu du recordset
     *
     * @param $booFirstColKey $firstcolkey true si la première colonne doit servir d'index pour le tableau (optionnel)
     * @return mixed un tableau indexé contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getarray($booFirstColKey = false)
    {
        return $this->objDb->getarray($this->resRs, $booFirstColKey);
    }

    /**
     * Retourne au format JSON le contenu du recordset
     *
     * @param boolean $booUtf8 true si le contenu doit être encodé en utf8, false sinon (true par défaut)
     * @return string une chaîne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getjson($booUtf8 = true)
    {
        return $this->objDb->getjson($this->resRs, $booUtf8);
    }


    /**
     * Retourne au format CSV le contenu du recordset
     *
     * @param array $arrOptions options du format CSV : booHeader:true si la ligne d'entête doit être ajoutée (nom des colonnes), strFieldSep:séparateur de champs, strLineSep:séparateur de lignes, strTextSep:caractère d'encapsulation des contenus
     * @return string une chaîne au format CSV contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getcsv($arrOptions = array()) {

        $arrDefaultOptions = array(
            'booHeader' => true,
            'strFieldSep' => ',',
            'strLineSep' => "\n",
            'strTextSep' => '"',
            'booClean' => true
        );

        $arrOptions = array_merge($arrDefaultOptions, $arrOptions);

        // Fonction d'échappement & formatage du contenu
        $funcLineEchap = null;

        if ($arrOptions['strTextSep'] != '') {
            $funcLineEchap = create_function('$value', 'return \''.$arrOptions['strTextSep'].'\'.str_replace(\''.$arrOptions['strTextSep'].'\', \''.$arrOptions['strTextSep'].$arrOptions['strTextSep'].'\', $value).\''.$arrOptions['strTextSep'].'\';');
        } elseif ($arrOptions['strFieldSep'] != '') {
            $funcLineEchap = create_function('$value', 'return str_replace(\''.$arrOptions['strFieldSep'].'\', \'\\'.$arrOptions['strFieldSep'].'\', $value);');
        }

        $booHeader = false;
        $strCsv = '';

        while ($row = $this->fetchrow()) {

            if ($arrOptions['booClean']) $row = arr::map('str::iso8859_clean', $row);

            // Ajout de la ligne d'entête
            if ($arrOptions['booHeader'] && !$booHeader) {
                $booHeader = true;
                $strCsv = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? array_keys($row) : arr::map($funcLineEchap, array_keys($row))).$arrOptions['strLineSep'];
            }

            $strCsv .= implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? $row : arr::map($funcLineEchap, $row)).$arrOptions['strLineSep'];

        }

        return $strCsv;
    }

}
