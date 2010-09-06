<?php
/*
    Copyright (c) 2007-2010 Ovensia
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
 * Gestion des timestamps Ploopi
 * En utilisant "Fluent Interface"
 * 
 * @package ploopi
 * @subpackage timestamp
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe de gestion des timestamps
 * 
 * @package ploopi
 * @subpackage timestamp
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopiTimestamp
{
    /**
     * Format du timestamp
     */
    
    Const _FORMAT = 'YmdHis';
    
    /**
     * Expression rationnelle du format
     */
    
    Const _PREG_FORMAT = '/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/';
    
    /**
     * Le timestamp
     *
     * @var string
     */
    
    private $strTimeStamp;

    /**
     * Constructeur de la classe
     *
     * @return ploopi_timestamp
     */
    
    public function __construct() { $this->strTimeStamp = 0; }
    
    /**
     * Méthode factory, initialise par défaut le timestamp à la date (et heure) du jour 
     *
     * @return ploopiTimestamp
     */
    
    public static function getInstance() 
    {
        $objTimeStamp = new ploopiTimestamp();
        $objTimeStamp->setNow();
        
        return $objTimeStamp;
    }    
   
    /**
     * Retourne le timestamp brut
     *
     * @return string timestamp
     */
    
    public function get() { return $this->strTimeStamp; }
    
    /**
     * Définit une nouvelle valeur pour le timestamp
     *
     * @param string $strTs le nouveau timestamp
     * @return ploopiTimestamp l'objet (fluent)
     */
    
    public function set($strTs)
    {
        preg_match(self::_PREG_FORMAT, $this->strTimeStamp, $arrMatches);
        
        if (is_numeric($strTs) && strlen($strTs) == 14) $this->strTimeStamp = $strTs;
        else throw new ploopiException("Invalid timestamp format for &laquo; {$strTs} &raquo;");
        
        return $this;
    }
    
    /**
     * Définit le timestamp à la date/heure du jour
     *
     * @return ploopiTimestamp l'objet (fluent)
     */
    
    public function setNow() { $this->strTimeStamp = date(self::_FORMAT); return $this; }
    
    /**
     * Ajoute une durée au timestamp
     *
     * @param int $intH nombre d'heure à ajouter
     * @param int $intMn nombre de minute à ajouter
     * @param int $intS nombre de seconde à ajouter
     * @param int $intM nombre de mois à ajouter
     * @param int $intD nombre de jour à ajouter
     * @param int $intY nombre d'année à ajouter
     * @return ploopiTimestamp l'objet (fluent)
     */
    
    public function add($intH = 0, $intMn = 0, $intS = 0, $intM = 0, $intD = 0, $intY = 0)
    {
        $arrTs = $this->getDetails();
        
        $this->strTimeStamp = date(
            self::_FORMAT,
            mktime(
                $arrTs[3] + $intH,
                $arrTs[4] + $intMn,
                $arrTs[5] + $intS,
                $arrTs[1] + $intM,
                $arrTs[2] + $intD,
                $arrTs[0] + $intY
            )
        );        
        
        return $this;
    }    

    /**
     * Retourne un tableau indexé contenant le détail du timestamp
     * 0 => année, 1 => mois, 2 => jour, 3 => heure, 4 => minute, 5 => seconde
     *
     * @return array
     */
    
    public function getDetails()
    {
        return array(
            $this->getYear(),
            $this->getMonth(),
            $this->getDay(),
            $this->getHour(),
            $this->getMinute(),
            $this->getSecond(),
        );
    }
    
    /**
     * Retourne l'année du timestamp
     *
     * @return int année
     */
    
    public function getYear() { return intval(substr($this->strTimeStamp, 0, 4)); }
    
    /**
     * Retourne le mois du timestamp
     *
     * @return int mois
     */
    
    public function getMonth() { return intval(substr($this->strTimeStamp, 4, 2)); }
    
    /**
     * Retourne le jour du timestamp
     *
     * @return int jour
     */
    
    public function getDay() { return intval(substr($this->strTimeStamp, 6, 2)); }
    
    /**
     * Retourne l'heure du timestamp
     *
     * @return int heure
     */
    
    public function getHour() { return intval(substr($this->strTimeStamp, 8, 2)); }
    
    /**
     * Retourne la minute du timestamp
     *
     * @return int minute
     */
    
    public function getMinute() { return intval(substr($this->strTimeStamp, 10, 2)); }
    
    /**
     * Retourne la seconde du timestamp
     *
     * @return int seconde
     */
    
    public function getSecond() { return intval(substr($this->strTimeStamp, 12, 2)); }
    
    /**
     * Méthode magique __toString
     *
     * @return string timestamp brut
     */
    
    function __toString() { return strval($this->get()); }
}
