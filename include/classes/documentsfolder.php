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

namespace ploopi;

use ploopi;

/**
 * Classe de gestion des fichiers (ne pas confondre avec le module DOC)
 *
 * @package ploopi
 * @subpackage document
 * @copyright Ovensia
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
    function __construct()
    {
        parent::__construct('ploopi_documents_folder');
        $this->fields['timestp_create'] = date::createtimestamp();
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
        $db = loader::getdb();

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
                $docfolder_parent->fields['nbelements'] = documents::countelements($this->fields['id_folder']);
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
        $db = loader::getdb();

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
            $docfolder_parent->fields['nbelements'] = documents::countelements($this->fields['id_folder']);
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
        $objDocChild->fields['timestp_create'] = $objDocChild->fields['timestp_modify'] = date::createtimestamp();
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
        $objDO = new data_object_collection(__NAMESPACE__.'\\documentsfile');
        $objDO->add_orderby('name');
        $arrObjFile = $objDO->get_objects(true);

        // Lecture des dossiers
        $objDO = new data_object_collection(__NAMESPACE__.'\\documentsfolder');
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

        $objDO = new data_object_collection(__NAMESPACE__.'\\documentsfile');
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

        $objDO = new data_object_collection(__NAMESPACE__.'\\documentsfolder');
        $objDO->add_where('id_object = %d', $id_object);
        $objDO->add_where('id_record = %s', $id_record);
        $objDO->add_where('id_module = %d', $id_module);
        $objDO->add_where("parents = '0'");

        $arrObjFolder = $objDO->get_objects();

        return empty($arrObjFolder) ? null : current($arrObjFolder);
    }

}

