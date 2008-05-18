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

include_once './include/classes/data_object.php';

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
    var $oldname;
    var $tmpfile;
    var $draftfile;

    function documentsfile()
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
        $this->draftfile = 'none';
    }



    function open($id)
    {
        $res = parent::open($id);
        $this->oldname = $this->fields['name'];
        return($res);
    }

    function save()
    {
        global $db;
        $error = 0;
        if (isset($this->fields['folder'])) unset($this->fields['folder']);

        if (!isset($this->oldname)) $this->oldname = '';

        if ($this->new) // insert
        {

            if ($this->tmpfile == 'none' && $this->draftfile == 'none') $error = _DOC_ERROR_EMPTYFILE;

            if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;

            if (!$error)
            {
                $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                $id = parent::save();

                $basepath = $this->getbasepath();
                $filepath = $this->getfilepath();

                if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

                if (!$error && is_writable($basepath))
                {
                    if ($this->draftfile != 'none')
                    {
                        if (!rename($this->draftfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    }
                    elseif ($this->tmpfile != 'none')
                    {
                        if (!move_uploaded_file($this->tmpfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    }
                    
                    if (!$error)
                    {
                        chmod($filepath, 0777);
                        $this->getcontent();
                    }
                }
                else $error = _DOC_ERROR_FILENOTWRITABLE;
            }

        }
        else // update
        {
            //$this->getcontent();
            
            if ((!empty($this->tmpfile) && $this->tmpfile != 'none') || (!empty($this->draftfile) && $this->draftfile != 'none'))
            {
                if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;
                
                if (!$error)
                {
                    $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                    $basepath = $this->getbasepath();
                    $filepath = $this->getfilepath();
                    
                    if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    
                    if (!$error)
                    {
                        // on déplace l'ancien fichier
                        /*
                        if (file_exists($filepath) && is_writable($basepath)) 
                        {
                            rename($filepath, $filepath_vers);
                            //$this->createhistory();
                        }
                        */

                        // on copie le nouveau
                        if (!$error && is_writable($basepath))
                        {
                            if ($this->draftfile != 'none')
                            {
                                if (rename($this->draftfile, $filepath))
                                {
                                    chmod($filepath, 0777);
                                    $this->getcontent();
                                }
                                else $error = _DOC_ERROR_FILENOTWRITABLE;
                            }
                            if ($this->tmpfile != 'none')
                            {
                                if (move_uploaded_file($this->tmpfile, $filepath))
                                {
                                    chmod($filepath, 0777);
                                    $this->getcontent();
                                }
                                else $error = _DOC_ERROR_FILENOTWRITABLE;
                            }
                        }
                        else $error = _DOC_ERROR_FILENOTWRITABLE;
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
                        $this->getcontent();
                        parent::save();
                    }
                    else $error = _DOC_ERROR_FILENOTWRITABLE;
                }
                else
                {
                    $this->getcontent();
                    parent::save();
                }
            }
            else
            {
                parent::save();
            }
        }

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new documentsfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }

        return($error);
    }


    function delete()
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
    
    function getbasepath()
    {
        $basepath = ploopi_documents_getpath()._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
        ploopi_makedir($basepath);
        return($basepath);
    }

    function getfilepath()
    {
        return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id']}.{$this->fields['extension']}");
    }
    
    function getcontent()
    {
        global $db;
    }

    function geturl($attachement = true)
    {
        return(ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$this->fields['id']}&attachement={$attachement}"));
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
    function documentsfolder()
    {
        parent::data_object('ploopi_documents_folder');
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['parents']=0;
    }

    function save()
    {
        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new documentsfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $this->fields['parents'] = "{$docfolder_parent->fields['parents']},{$this->fields['id_folder']}";
            $ret = parent::save();
            $docfolder_parent->fields['nbelements'] = ploopi_documents_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }
        else $ret = parent::save();
        
        return ($ret);
    }

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
}

