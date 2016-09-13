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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Gestion interne des documents
 *
 * @package ploopi
 * @subpackage document
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

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
    public function __construct()
    {
        parent::__construct('ploopi_documents_file');
        $this->fields['id_user'] = 0;
        $this->fields['timestp_create'] = date::createtimestamp();
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
    public function open(...$args)
    {
        if ($res = parent::open($args[0])) $this->oldname = $this->fields['name'];
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

                $this->fields['timestp_modify'] = date::createtimestamp();

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
            $docfolder_parent->fields['nbelements'] = documents::countelements($this->fields['id_folder']);
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
            $docfolder_parent->fields['nbelements'] = documents::countelements($this->fields['id_folder']);
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
        $basepath = documents::getpath()._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
        fs::makedir($basepath);
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
        return crypt::urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$this->fields['md5id']}&attachement={$attachement}");
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
