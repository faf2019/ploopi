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
 * @subpackage gallery
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Inclusion des classes.
 */

include_once './modules/gallery/class/class_gallery_directories.php';


class gallery extends data_object
{

    private $booAutoSaveInfo = true;
    
    /**
     * Constructeur de la classe
     *
     * @return gallery
     */
    function gallery()
    {
        parent::data_object('ploopi_mod_gallery');
    }
    
    function save()
    {
        if($this->booAutoSaveInfo)
        {
            if($this->new)
            {
                $this->fields['create_id_user'] = $_SESSION['ploopi']['userid'] ;
                $this->fields['create_user']    = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
                $this->fields['create_timestp'] = ploopi_createtimestamp();
            }
            else
            {
                $this->fields['lastupdate_id_user'] = $_SESSION['ploopi']['userid'] ;
                $this->fields['lastupdate_user']    = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
                $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();
            }
            
            if(empty($this->fields['thumb_color'])) $this->fields['thumb_color'] = '#FFFFFF';
            if(empty($this->fields['view_color'])) $this->fields['view_color'] = '#FFFFFF';
            
            $this->setuwm();
        }
        parent::save();
    }
    
    function savedirectories($arrDirectories)
    {
        global $db;
        // On supprime toutes les repertoires de cette galerie
        $db->query("DELETE FROM ploopi_mod_gallery_directories WHERE id_gallery = '{$this->fields['id']}'");
        
        // On enregistre tous les repertoires 
        $arrAdd = array();
        foreach ($arrDirectories as $key => $idDir) $arrAdd[] = "(NULL, '{$this->fields['id']}', '{$idDir}')";  
        
        if(!empty($arrAdd))
        {
            $sql = 'INSERT INTO `ploopi_mod_gallery_directories` (`id`, `id_gallery`, `id_directory`) VALUES ';
            $sql .= implode(',',$arrAdd);
            $db->query($sql);
        }
    }

    function deldirectories()
    {
        global $db;
        // On supprime toutes les repertoires de cette galerie
        $db->query("DELETE FROM ploopi_mod_gallery_directories WHERE id_gallery = '{$this->fields['id']}'");
    }
    
    function getdirectories()
    {
        global $db;

        $sqldir = $db->query("SELECT * FROM ploopi_mod_gallery_directories WHERE id_gallery = '{$this->fields['id']}'");
        if($db->numrows($sqldir))
            return $db->getarray($sqldir);
        else
            return false;
    }
    
    function setautosaveinfo($booAutoSave)
    {
        $this->booAutoSaveInfo = $booAutoSave;
    }
}
?>