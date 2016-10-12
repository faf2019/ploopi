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

/**
 * Gestion des dossiers
 *
 * @package doc
 * @subpackage folder
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe docfile.
 */

include_once './modules/doc/class_docfile.php';

/**
 * Classe d'accès à la table ploopi_mod_doc_folder
 *
 * @package doc
 * @subpackage folder
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class docfolder extends ploopi\data_object
{
    private $id_folder = 0;
    private $parents = '';

    /**
     * Constructeur de la classe
     *
     * @return docfolder
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_doc_folder');
        $this->fields['timestp_create'] = ploopi\date::createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['parents'] = 0;
    }

    public function open(...$args)
    {
        if ($res = parent::open($args))
        {
            $this->id_folder = $this->fields['id_folder'];
            $this->parents = $this->fields['parents'];
        }

        return $res;
    }



    /**
     * Enregistre le dossier.
     * Les dossiers n'existent pas physiquement.
     * Mise à jour du nombre d'élément du dossier parent.
     *
     * @return int identifiant du dossier
     *
     * @see doc_countelements
     */
    public function save()
    {
        $db = ploopi\db::get();

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new docfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $this->fields['parents'] = "{$docfolder_parent->fields['parents']},{$this->fields['id_folder']}";
            $ret = parent::save();
            $docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }
        else
        {
            $this->fields['parents'] = '0';
            $ret = parent::save();
        }


        // Cas d'un changement de parent, il faut mettre à jour en cascade tous les enfants
        if ($this->fields['id_folder'] != $this->id_folder)
        {
            $objCol = new ploopi\data_object_collection('docfolder');
            $objCol->add_where('parents LIKE %s', "{$this->parents},{$this->fields['id']}%");
            foreach($objCol->get_objects() as $objChildFolder) $objChildFolder->save();

        }

        return $ret;
    }

    /**
     * Publie le dossier et son contenu
     *
     * @return unknown
     */

    public function publish()
    {
        $db = ploopi\db::get();

        $ret = 0;
        if (!$this->fields['published'])
        {
            $this->fields['published'] = 1;
            $ret = $this->save();
            $db->query("UPDATE ploopi_mod_doc_folder SET waiting_validation = 0 WHERE waiting_validation = {$this->fields['id']}");
        }

        return($ret);
    }

    /**
     * Détermine si le dossier est accessible par l'utilisateur connecté
     *
     * @return boolean true si me dossier est accessible par l'utilisateur connecté
     */

    public function isEnabled()
    {
        $booFolderEnabled = false;

        if ($this->fields['id_user'] == $_SESSION['ploopi']['userid'] || ploopi\acl::isadmin() || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN)) $booFolderEnabled = true;
        else
        {
            if ($this->fields['foldertype'] == 'public' && in_array($this->fields['id_workspace'], explode(',', ploopi\system::viewworkspaces()))) $booFolderEnabled = true;
            else
            {
                doc_getshare();
                if (in_array($this->fields['id'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) $booFolderEnabled = true;
            }
        }

        return($booFolderEnabled);
    }

    /**
     * Supprime le dossier.
     * Supprime le contenu (fichiers+dossier)
     */

    public function delete()
    {
        $db = ploopi\db::get();

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

    /**
     * Retourne la liste des abonnés au dossier (en vérifiant les partages)
     *
     * @return array tableau des utilisateurs abonnés
     */

    public function getSubscribers($arrActions)
    {
        $arrSubscribers = array();

        // Si le dossier n'est pas privé
        if ($this->fields['foldertype'] != 'private')
        {
            // on construit la liste des objets parents (y compris l'objet courant)
            $arrFolderList = preg_split('/,/', "{$this->fields['parents']},{$this->fields['id']}");

            // on cherche la liste des abonnés à chacun des objets pour construire une liste globale d'abonnés
            foreach ($arrFolderList as $intObjectId)
                $arrSubscribers += ploopi\subscription::getusers(_DOC_OBJECT_FOLDER, $intObjectId, $arrActions);

            // Si dossier partagé, on vérifie que l'abonné est dans les partages
            if ($this->fields['foldertype'] == 'shared')
            {
                // On récupère les utilisateurs pour lesquels le dossier est partagé
                $arrShareUsers = ploopi\share::get(-1, _DOC_OBJECT_FOLDER, $intObjectId);

                // Tableau résultat des utilisateurs abonnés et pour lesquels le dossier est partagé
                $arrShareSubscribers = array();

                // On ne garde que les utilisateurs pour qui le dossier est partagé
                foreach($arrShareUsers as $u) if ($u['type_share'] == 'user' && isset($arrSubscribers[$u['id_share']])) $arrShareSubscribers[$u['id_share']] = $arrSubscribers[$u['id_share']];

                // On affecte les utilisateurs que l'on garde à la liste des destinataires de l'abonnement
                $arrSubscribers = $arrShareSubscribers;
            }
        }

        return $arrSubscribers;
    }
}
