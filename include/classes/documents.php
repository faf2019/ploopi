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
 * Gestion internet des documents
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';
include_once './include/classes/data_object_collection.php';

/**
 * Classe de gestion des documents (ne pas confondre avec le module DOC)
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class documentsfile extends data_object
{
    private $oldname;
    private $tmpfile;
    private $file;

    /**
     * Constructeur de la classe
     *
     * @return documentsfile
     */
    public function documentsfile()
    {
        parent::data_object('ploopi_documents_file');
        $this->fields['id_user'] = 0;
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['description']='';
        $this->fields['size'] = 0;
        $this->fields['nbclick'] = 0;
        $this->fields['name'] = '';

        $this->oldname = '';
        $this->tmpfile = 'none';
        $this->file = 'none';
    }

    /**
     * Ouvre un document
     *
     * @param int $id identifiant du document
     * @return boolean true si le document a été ouvert
     */
    public function open($id)
    {
        if ($res = parent::open($id)) $this->oldname = $this->fields['name'];
        return($res);
    }
    
    
    /**
     * Ouvre un document avec son identifiant MD5
     *
     * @param string $md5id identifiant MD5 du document
     * @return boolean true si le document a été ouvert
     */

    function openmd5($md5id)
    {
        global $db;

        $db->query("SELECT id FROM ploopi_documents_file WHERE md5id = '".$db->addslashes($md5id)."'");
        if ($fields = $db->fetchrow()) return($this->open($fields['id']));
        else return false;
    }    

    /**
     * Enregistre le document.
     * Gère la sauvegarde physique du fichier, le renommage.
     *
     * @return int identifiant du document
     */
    public function save()
    {
    	global $db;
        $error = 0;
        if (isset($this->fields['folder'])) unset($this->fields['folder']);

        if (!isset($this->oldname)) $this->oldname = '';

        if ($this->new) // insert
        {
        	if ($this->tmpfile == 'none' && $this->file == 'none') $error = _PLOOPI_ERROR_EMPTYFILE;

        	if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _PLOOPI_ERROR_MAXFILESIZE;

        	if (!$error)
            {
                $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                $id = parent::save();
                
                $this->fields['md5id'] = md5(sprintf("%s_%d", $this->fields['timestp_create'], $id));
                
                parent::save();
                
                $basepath = $this->getbasepath();
                $filepath = $this->getfilepath();

                if (file_exists($filepath) && !is_writable($filepath)) $error = _PLOOPI_ERROR_FILENOTWRITABLE;

                if (!$error && is_writable($basepath))
                {
                    if ($this->tmpfile != 'none')
                    {
                        if (!move_uploaded_file($this->tmpfile, $filepath)) $error = _PLOOPI_ERROR_FILENOTWRITABLE;
                    }

                    if ($this->file != 'none')
                    {
                        if (!copy($this->file, $filepath)) $error = _PLOOPI_ERROR_FILENOTWRITABLE;
                    }

                    if (!$error) chmod($filepath, 0640);
                }
                else $error = _PLOOPI_ERROR_FILENOTWRITABLE;
            }

        }
        else // update
        {
            if (!empty($this->tmpfile) && $this->tmpfile != 'none')
            {
                if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _PLOOPI_ERROR_MAXFILESIZE;

                if (!$error)
                {
                    $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                    $basepath = $this->getbasepath();
                    $filepath = $this->getfilepath();

                    if (file_exists($filepath) && !is_writable($filepath)) $error = _PLOOPI_ERROR_FILENOTWRITABLE;

                    if (!$error)
                    {
                        // on copie le nouveau
                        if (!$error && is_writable($basepath))
                        {
                            if ($this->tmpfile != 'none')
                            {
                                if (move_uploaded_file($this->tmpfile, $filepath)) chmod($filepath, 0640);
                                else $error = _PLOOPI_ERROR_FILENOTWRITABLE;
                            }
                        }
                        else $error = _PLOOPI_ERROR_FILENOTWRITABLE;
                    }
                }

                $this->fields['timestp_modify'] = ploopi_createtimestamp();

                $this->oldname = $this->fields['name'];
            }

            // renommage
            if ($this->oldname != $this->fields['name'])
            {
                // renommage avec modification de type
                if (($newext = substr(strrchr($this->fields['name'], "."),1)) != $this->fields['extension'])
                {
                    $basepath = $this->getbasepath();
                    $filepath = $this->getfilepath();
                    $newfilepath = substr($filepath,0,strlen($filepath)-strlen($this->fields['extension'])).$newext;

                    if (file_exists($filepath) && is_writable($basepath))
                    {
                        rename($filepath, $newfilepath);
                        $this->fields['extension'] = $newext;
                        parent::save();
                    }
                    else $error = _PLOOPI_ERROR_FILENOTWRITABLE;
                }
                else parent::save();
            }
            else parent::save();
        }

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new documentsfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }

        return $error;
    }

    /**
     * Supprime le document (physiquement et dans la base de données)
     */
    public function delete()
    {
        $filepath = $this->getfilepath();
        if (file_exists($filepath)) unlink($filepath);

        parent::delete();

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new documentsfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }
    }

    /**
     * Retourne le chemin physique de stockage du document.
     * Crée le dossier si nécessaire.
     *
     * @return string chemin de stockage du document
     */
    public function getbasepath()
    {
        $basepath = ploopi_documents_getpath()._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
        ploopi_makedir($basepath);
        return $basepath;
    }

    /**
     * Retourne le chemin physique complet du fichier.
     *
     * @return string chemin du fichier
     */
    public function getfilepath()
    {
        return $this->getbasepath()._PLOOPI_SEP."{$this->fields['id']}.{$this->fields['extension']}";
    }

    /**
     * Retourne l'URL permettant de télécharger le document
     *
     * @param boolean $attachement true si le fichier doit être "attaché"
     * @return string URL de téléchargement
     */
    public function geturl($attachement = true)
    {
        return ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$this->fields['id']}&attachement={$attachement}");
    }

    /**
     * Permet de définir l'emplacement du fichier manuellement (ajout uniquement)
     *
     * @param string $file chemin du fichier
     */

    public function setfile($file)
    {
        $this->file = $file;
    }

    /**
     * Permet de définir l'emplacement du fichier temporaire (après upload)
     *
     * @param string $file chemin du fichier
     */

    public function settmpfile($file)
    {
        $this->tmpfile = $file;
    }

}

/**
 * Classe de gestion des fichiers (ne pas confondre avec le module DOC)
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class documentsfolder extends data_object
{
    /**
     * Contructeur de la classe
     *
     * @return documentsfolder
     */
    function documentsfolder()
    {
        parent::data_object('ploopi_documents_folder');
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['parents']=0;
    }
    
    
    /**
     * Ouvre un dossier avec son identifiant MD5
     *
     * @param string $md5id identifiant MD5 du dossier
     * @return boolean true si le dossier a été ouvert
     */

    function openmd5($md5id)
    {
        global $db;

        $db->query("SELECT id FROM ploopi_documents_folder WHERE md5id = '".$db->addslashes($md5id)."'");
        if ($fields = $db->fetchrow()) return($this->open($fields['id']));
        else return false;
    }    
    
    /**
     * Enregistre le dossier
     *
     * @return int identifiant du dossier
     */
    function save()
    {
    	if ($this->new)
    	{
	        $id = parent::save();
            $this->fields['md5id'] = md5(sprintf("%s_%d", $this->fields['timestp_create'], $id));
	        
	        if ($this->fields['id_folder'] != 0)
	        {
	            $docfolder_parent = new documentsfolder();
	            $docfolder_parent->open($this->fields['id_folder']);
	            $this->fields['parents'] = "{$docfolder_parent->fields['parents']},{$this->fields['id_folder']}";
	            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
	            $docfolder_parent->save();
	        }
    		
            parent::save();
    	}
    	
        else $id= parent::save();

        return $id;
    }

    /**
     * Supprime le dossier et son contenu.
     * Met à jour les informations du dossier parent.
     */
    function delete()
    {
        global $db;

        // on recherche tous les fichiers pour les supprimer
        $rs = $db->query("SELECT id FROM ploopi_documents_file WHERE id_folder = {$this->fields['id']}");
        while($row = $db->fetchrow($rs))
        {
            $file = new documentsfile();
            $file->open($row['id']);
            $file->delete();
        }

        // on recherche tous les dossiers fils pour les supprimer
        $rs = $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = {$this->fields['id']}");
        while($row = $db->fetchrow($rs))
        {
            $folder = new documentsfolder();
            $folder->open($row['id']);
            $folder->delete();
        }

        parent::delete();

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new documentsfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }

    }

    /**
     * Crée un sous-dossier
     *
     * @param string $name nom du dossier
     * @param string $description description du dossier
     * @return documentsfolder dossier fils
     */

    function create_child($name, $description = '')
    {
        $objDocChild = new documentsfolder();
        $objDocChild->fields['id_record'] = $this->fields['id_record'];
        $objDocChild->fields['id_object'] = $this->fields['id_object'];
        $objDocChild->fields['id_module'] = $this->fields['id_module'];
        $objDocChild->fields['id_workspace'] = $this->fields['id_workspace'];
        $objDocChild->fields['id_user'] = $this->fields['id_user'];
        $objDocChild->fields['name'] = $name;
        $objDocChild->fields['description'] = $description;
        $objDocChild->fields['timestp_create'] = $objDocChild->fields['timestp_modify'] = ploopi_createtimestamp();
        $objDocChild->fields['nbelements'] = 0;
        $objDocChild->fields['parents'] = "{$this->fields['parents']},{$this->fields['id']}";
        $objDocChild->fields['id_folder'] = $this->fields['id'];

        return $objDocChild;
    }
    
    /**
     * Retourne l'arbre complet des dossiers et des fichiers sous forme d'un tableau d'objets
     * $arr[id_folder] => array('folders' => array(), 'files' => array());
     * @return array
     */
    public function gettree()
    {
        // Lecture des fichiers
        $objDO = new data_object_collection('documentsfile');
        $objDO->add_orderby('name');
        $arrObjFile = $objDO->get_objects(true);
        
        // Lecture des dossiers        
        $objDO = new data_object_collection('documentsfolder');
        $objDO->add_where('id_object = %d', $this->fields['id_object']);
        $objDO->add_where('id_record = %s', $this->fields['id_record']);
        $objDO->add_where('id_module = %d', $this->fields['id_module']);
        //$objDO->add_where("parents LIKE %s OR parents = %s", array($this->fields['parents'].',%', $this->fields['parents']));
        $objDO->add_where("parents LIKE %s", $this->fields['parents'].',%');
        $objDO->add_orderby('parents, name');
        $arrObjFolder = $objDO->get_objects();
        
        // Construction de l'arbre
        $arrTree = array('folders' => array(), 'files' => array(), 'tree' => array());
        
        // Premier noeud, le dossier courant
        $arrTree['folders'][$this->fields['id']] = $this;
        
        // Gestion des dossiers
        foreach($arrObjFolder as $objFolder) 
        {
            $arrTree['folders'][$objFolder->fields['id']] = $objFolder;
            $arrTree['tree'][$objFolder->fields['id_folder']]['folders'][] = &$arrTree['folders'][$objFolder->fields['id']];
        }

        // Gestion des fichiers
        foreach($arrObjFile as $objFile)  
        {
            if (isset($arrTree['folders'][$objFile->fields['id_folder']]))
            {
                $arrTree['files'][$objFile->fields['id']] = $objFile;
                $arrTree['tree'][$objFile->fields['id_folder']]['files'][] = &$arrTree['files'][$objFile->fields['id']];
            }
        }
        
        return $arrTree;
    }
    
    /**
     * Retourne un tableau contenant la liste des fichiers sour la forme $arr['id_file'] => array('file', 'folder', 'path') 
     * 
     * @return array tableau de chemins
     */
    public function getlist()
    {
        $arrTree = $this->gettree();
        return self::_getlist_rec($arrTree);
    }
    
    
    /**
     * Retourne un tableau contenant la liste des fichiers sour la forme $arr['id_file'] => array('file', 'folder', 'path') 
     * 
     * @param array $arrTree arbre des dossiers/fichiers
     * @param int $intIdFolder identifiant du dossier courant
     * @param string $strPath chemin relatif par rapport au dossier de départ 
     * @return array tableau de chemins
     */
    
    private static function _getlist_rec(&$arrTree, $intIdFolder = null, $strPath = '')
    {
        $arrFiles = array();
        
        if (is_null($intIdFolder)) 
        {
            reset($arrTree['tree']);
            $intIdFolder = key($arrTree['tree']);
        }
        else $strPath .= $arrTree['folders'][$intIdFolder]->fields['name'].'/';
        
        // Parcours des fichiers de la branche courante
        if (isset($arrTree['tree'][$intIdFolder]['files']))
        {
            foreach($arrTree['tree'][$intIdFolder]['files'] as $objFile)
            {
                $arrFiles[$objFile->fields['id']] = array('file' => $objFile, 'folder' => $arrTree['folders'][$intIdFolder], 'path' => $strPath.$objFile->fields['name']);
            }
        }
        
        // Parcours des dossiers de la branche courante
        if (isset($arrTree['tree'][$intIdFolder]['folders']))
        {
            foreach($arrTree['tree'][$intIdFolder]['folders'] as $objFolder)
            {
                $arrFiles = $arrFiles + self::_getlist_rec($arrTree, $objFolder->fields['id'], $strPath);
            }
        }
        
        return $arrFiles;
    }
    
    /**
     * Retourne les fichiers du dossier
     * 
     * @param bool $booRecursive true si la fonction doit retourner les fichiers des sous-dossiers
     * @return array tableau de "documentsfile"
     * 
     */
    public function getfiles($booRecursive = false)
    {
        
        $objDO = new data_object_collection('documentsfile');
        if (!$booRecursive) $objDO->add_where('id_folder = %d', $this->fields['id']);
        $objDO->add_orderby('name');
        $arrObjFile = $objDO->get_objects(true);
        
        return $arrObjFile;
    }
    
    /**
     * Retourne le dossier racine d'un objet
     * 
     * @param int $id_object id de l'objet
     * @param string $id_record id de l'enregistrement
     * @param string $id_module id du module
     * @return documentsfolder dossier racine
     */
    
    public static function getroot($id_object, $id_record, $id_module = null)
    {
        if (empty($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];
            
        $objDO = new data_object_collection('documentsfolder');
        $objDO->add_where('id_object = %d', $id_object);
        $objDO->add_where('id_record = %s', $id_record);
        $objDO->add_where('id_module = %d', $id_module);
        $objDO->add_where("parents = '0'");
        
        $arrObjFolder = $objDO->get_objects();
        
        return empty($arrObjFolder) ? null : current($arrObjFolder);
    }
    
}

