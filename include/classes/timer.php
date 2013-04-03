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
 * Gestion du timer d'ex�cution
 *
 * @package ploopi
 * @subpackage timer
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe timer
 *
 * @package ploopi
 * @subpackage timer
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class timer
{
    private $start;

    /**
     * Constructeur de la classe
     *
     * @return timer
     */

    public function timer()
    {
        $this->start = 0;
    }

    /**
     * D�marre le timer
     */

    public function start()
    {
        $this->start = $this->getmicrotime();
    }

    /**
     * Retourne le timestamp UNIX actuel en secondes avec les microsecondes
     *
     * @return float timestamp UNIX en secondes
     */

    public function getmicrotime()
    {
        return microtime(true);
    }

    /**
     * Retourne le nombre de secondes �coul�es depuis le d�marrage du timer
     *
     * @return float temps �coul� en secondes
     */

    public function getexectime()
    {
        return($this->getmicrotime() - $this->start);
    }

    /**
     * G�re la conversion de l'objet en cha�ne
     *
     * @return string contenu de l'objet sous forme d'une cha�ne de caract�res
     */

    public function __toString()
    {
        return sprintf("exec time : %s ms", $this->getexectime()*1000);
    }

}
