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
 * Gestion des fichiers de log
 *
 * @package ploopi
 * @subpackage log
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe de gestion des fichiers de log
 *
 * @package ploopi
 * @subpackage log
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopiLogFile
{
    private $ptrFileHandle;

    /**
     * Constructeur de la classe
     *
     * @return log
     */

    public function __construct($strFilePath)
    {
        if (!empty($strFilePath))
        {
            if (!file_exists(dirname($strFilePath))) mkdir(dirname($strFilePath), 0700, true);
            $this->ptrFileHandle = fopen($strFilePath, 'a');
        }
    }

    protected function isWritable()
    {
        return $this->ptrFileHandle !== false && is_resource($this->ptrFileHandle);
    }

    public function write($strMessage)
    {
        if ($this->isWritable())
        {
             fwrite($this->ptrFileHandle, '## '. ploopiTimestamp::getInstance()."\n{$strMessage}\n");
        }
    }

    public function __destruct() { if ($this->isWritable()) fclose($this->ptrFileHandle); }
}
