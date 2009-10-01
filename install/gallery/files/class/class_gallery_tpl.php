<?
/*
  Copyright (c) 2009 HeXad

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
 * Gestion des gallery
 *
 * @package gallery
 * @subpackage tpl
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

class gallery_tpl extends data_object
{

    private $booAutoSaveInfo = true;
    
    /**
     * Constructeur de la classe
     *
     * @return gallery_tpl
     */
    function gallery_tpl()
    {
        parent::data_object('ploopi_mod_gallery_tpl');
    }
    
    function save()
    {
        if($this->booAutoSaveInfo)
        {
            $this->setuwm();
        }
        parent::save();
    }
    
    function setautosaveinfo($booAutoSave)
    {
        $this->booAutoSaveInfo = $booAutoSave;
    }
}
?>