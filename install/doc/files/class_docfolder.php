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
?>
<?

class docfolder extends data_object
{

    /**
    * Class constructor
    *
    * @access public
    **/

    function docfolder()
    {
        parent::data_object('ploopi_mod_doc_folder');
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['parents']=0;
    }

    function save()
    {
        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new docfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $this->fields['parents'] = "{$docfolder_parent->fields['parents']},{$this->fields['id_folder']}";
            $ret = parent::save();
            $docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }
        else $ret = parent::save();
        
        return ($ret);
    }

    function publish()
    {
        global $db;

        $ret = 0;
        if (!$this->fields['published'])
        {
            $this->fields['published'] = 1; 
            $ret = $this->save();
            $db->query("UPDATE ploopi_mod_doc_folder SET waiting_validation = 0 WHERE waiting_validation = {$this->fields['id']}");
        }
        
        return($ret);
    }
    
    
    function isEnabled()
    {
        $booFolderEnabled = false;
        
        if ($this->fields['id_user'] == $_SESSION['ploopi']['userid']) $booFolderEnabled = true;
        else
        {
            if ($this->fields['foldertype'] == 'public') $booFolderEnabled = true;
            else
            {
                doc_getshares();
                if (in_array($this->fields['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['shares']['folders'])) $booFolderEnabled = true;
            }
        }

        return($booFolderEnabled);
    }
    
    
    function delete()
    {
        global $db;
        
        // on recherche tous les fichiers pour les supprimer
        $rs = $db->query("SELECT id FROM ploopi_mod_doc_file WHERE id_folder = {$this->fields['id']}");
        while($row = $db->fetchrow($rs))
        {
            $file = new docfile();
            $file->open($row['id']);
            $file->delete();
        }

        // on recherche tous les dossiers fils pour les supprimer
        $rs = $db->query("SELECT id FROM ploopi_mod_doc_folder WHERE id_folder = {$this->fields['id']}");
        while($row = $db->fetchrow($rs))
        {
            $folder = new docfolder();
            $folder->open($row['id']);
            $folder->delete();
        }
        
        parent::delete();
        
        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new docfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }
        
    }
}
