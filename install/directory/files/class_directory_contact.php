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
 * Gestion des contacts
 *
 * @package directory
 * @subpackage contacts
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_directory_contact
 *
 * @package directory
 * @subpackage contacts
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class directory_contact extends data_object
{
    private $intPosition;
    
    /**
     * Constructeur de la classe
     *
     * @return directory_contact
     */
    
    public function __construct()
    {
        parent::__construct('ploopi_mod_directory_contact');
        $this->fields['position'] = $this->intPosition = 0;
    }
    
    /**
     * Ouvre le contact
     */
    public function open($intId)
    {
        $res = parent::open($intId);
        // Sauvegarde de la position actuelle
        if ($res) $this->intPosition = $this->fields['position'];
        
        return $res;
    }
    
    /**
     * Enregistre le contact
     */
    public function save($booForcePos = false)
    {
        global $db;
        
        if (!$booForcePos)
        {
            // Recherche position max
            $db->query("SELECT MAX(position) as pos FROM ploopi_mod_directory_contact WHERE id_heading = '{$this->fields['id_heading']}'");
            $intMaxPos = ($row = $db->fetchrow()) ? $row['pos'] : 0;
            if ($this->fields['position'] > $intMaxPos) $this->fields['position'] = $intMaxPos;
            if ($this->fields['position'] < 1) $this->fields['position'] = 1;
            
            // Nouveau contact
            if ($this->isnew())
            {
                $this->fields['position'] = $intMaxPos + 1;
            }
            else
            {
                if ($this->intPosition != $this->fields['position']) // Changement de position
                {
                    if ($this->fields['position'] > $this->intPosition)
                    {
                        $db->query("UPDATE ploopi_mod_directory_contact SET position = position - 1 WHERE position > {$this->intPosition} AND position <= {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");
                    }
                    else
                    {
                        $db->query("UPDATE ploopi_mod_directory_contact SET position = position + 1 WHERE position >= {$this->fields['position']} AND position < {$this->intPosition} AND id_heading = {$this->fields['id_heading']}");
                    }
                }
            }
        }
        
        return parent::save();
    }
    

    /**
     * Supprime le contact et les favoris associés
     */

    public function delete()
    {
        global $db;
                
        $db->query("UPDATE ploopi_mod_directory_contact SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");
        
        $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_contact = {$this->fields['id']}");
        
        $this->deletephoto();
        
        parent::delete();
    }

    public function getphotopath()
    {
        return (_PLOOPI_PATHDATA._PLOOPI_SEP.'directory'._PLOOPI_SEP.$this->fields['id'].'.png');
    }
    
    public function deletephoto()
    {
        $strPhotoPath = $this->getphotopath();
        
        if (file_exists($strPhotoPath)) unlink($strPhotoPath);
    }
    
}
?>