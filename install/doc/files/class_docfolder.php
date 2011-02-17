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
 * Gestion des dossiers
 *
 * @package doc
 * @subpackage folder
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Inclusion de la classe docfile.
 */

include_once './modules/doc/class_docfile.php';

/**
 * Classe d'acc�s � la table ploopi_mod_doc_folder
 *
 * @package doc
 * @subpackage folder
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class docfolder extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return docfolder
     */

    function docfolder()
    {
        parent::data_object('ploopi_mod_doc_folder');
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['parents']=0;
    }

    /**
     * Enregistre le dossier.
     * Les dossiers n'existent pas physiquement.
     * Mise � jour du nombre d'�l�ment du dossier parent.
     *
     * @return int identifiant du dossier
     *
     * @see doc_countelements
     */
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

    /**
     * Publie le dossier et son contenu
     *
     * @return unknown
     */

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

    /**
     * D�termine si le dossier est accessible par l'utilisateur connect�
     *
     * @return boolean true si me dossier est accessible par l'utilisateur connect�
     */

    function isEnabled()
    {
        $booFolderEnabled = false;

        if ($this->fields['id_user'] == $_SESSION['ploopi']['userid'] || ploopi_isadmin()) $booFolderEnabled = true;
        else
        {
            if ($this->fields['foldertype'] == 'public' && in_array($this->fields['id_workspace'], explode(',', ploopi_viewworkspaces()))) $booFolderEnabled = true;
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

    /**
     * Retourne la liste des abonn�s au dossier (en v�rifiant les partages)
     *
     * @return array tableau des utilisateurs abonn�s
     */

    function getSubscribers($arrActions)
    {
        $arrSubscribers = array();

        // Si le dossier n'est pas priv�
        if ($this->fields['foldertype'] != 'private')
        {
            // on construit la liste des objets parents (y compris l'objet courant)
            $arrFolderList = preg_split('/,/', "{$this->fields['parents']},{$this->fields['id']}");

            // on cherche la liste des abonn�s � chacun des objets pour construire une liste globale d'abonn�s
            foreach ($arrFolderList as $intObjectId)
                $arrSubscribers += ploopi_subscription_getusers(_DOC_OBJECT_FOLDER, $intObjectId, $arrActions);

            // Si dossier partag�, on v�rifie que l'abonn� est dans les partages
            if ($this->fields['foldertype'] == 'shared')
            {
                // On r�cup�re les utilisateurs pour lesquels le dossier est partag�
                $arrShareUsers = ploopi_share_get(-1, _DOC_OBJECT_FOLDER, $intObjectId);

                // Tableau r�sultat des utilisateurs abonn�s et pour lesquels le dossier est partag�
                $arrShareSubscribers = array();

                // On ne garde que les utilisateurs pour qui le dossier est partag�
                foreach($arrShareUsers as $u) if ($u['type_share'] == 'user' && isset($arrSubscribers[$u['id_share']])) $arrShareSubscribers[$u['id_share']] = $arrSubscribers[$u['id_share']];

                // On affecte les utilisateurs que l'on garde � la liste des destinataires de l'abonnement
                $arrSubscribers = $arrShareSubscribers;
            }
        }

        return $arrSubscribers;
    }
}
