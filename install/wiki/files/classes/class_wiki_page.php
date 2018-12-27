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
 * Gestion des pages
 *
 * @package wiki
 * @subpackage page
 * @copyright Ovensia
 * @author Ovensia
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe wiki_page_history
 */
include_once './modules/wiki/classes/class_wiki_page_history.php';

/**
 * Classe d'accès à la table 'ploopi_mod_wiki_page'
 *
 * @package wiki
 * @subpackage page
 * @author Ovensia
 * @copyright Ovensia
 */

class wiki_page extends ploopi\data_object
{

    private $objPageHistory;

    /**
     * Constructeur de la classe
     *
     * @return wiki_page
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_mod_wiki_page',
            'id',
            'id_module'
        );

        $this->objPageHistory = null;
    }

    /**
     * Ouvre une page
     *
     * @param string $strIdPage identifiant de la page
     * @param int $intIdModule identifiant du module (optionnel, par défaut le module courant)
     *
     * @return boolean true si la page existe
     */
    public function open(...$args)
    {
        $strIdPage = $args[0];
        $intIdModule = isset($args[1]) ? $args[1] : null;

        if (!parent::open($strIdPage, is_null($intIdModule) ? $_SESSION['ploopi']['moduleid'] : $intIdModule))
        {
            parent::init_description();
            $this->fields['content'] = "h1. {$strIdPage}";
            return false;
        }
        else
        {
            $this->objPageHistory = new wiki_page_history();
            $this->objPageHistory->fields['content'] = $this->fields['content'];
            $this->objPageHistory->fields['id_page'] = $this->fields['id'];
            $this->objPageHistory->fields['revision'] = $this->fields['revision'];
            $this->objPageHistory->fields['ts_modified'] = $this->fields['ts_modified'];
            $this->objPageHistory->fields['id_user'] = $this->fields['id_user'];
            $this->objPageHistory->fields['id_workspace'] = $this->fields['id_workspace'];
            $this->objPageHistory->fields['id_module'] = $this->fields['id_module'];
        }

        return true;
    }

    /**
     * Enregistre la page et l'historique de modification
     */
    public function save()
    {
        if ($this->isnew())
        {
            $this->fields['ts_created'] = ploopi\date::createtimestamp();
            $this->fields['ts_modified'] = $this->fields['ts_created'];
            $this->fields['revision'] = 1;
            if (empty($this->fields['id_module'])) parent::setuwm();
        }
        else
        {
            if (!is_null($this->objPageHistory)) $this->objPageHistory->save();

            $this->fields['ts_modified'] = ploopi\date::createtimestamp();
            $this->fields['revision']++;
            parent::setuwm();
        }

        $this->index();

        return parent::save();
    }

    /**
     * Gère la redirection des liens après un renommage de page (modification de l'id)
     *
     * @param string $strNewId nouvel id
     *
     * @return boolean true si la page a pu être enregistrée
     */
    public function redirectLinks($strNewId)
    {
        $strOldId = ploopi\str::htmlentities($this->fields['id']);
        $strNewId = ploopi\str::htmlentities($strNewId);
        $this->fields['content'] = str_replace("[[{$strOldId}]]", "[[{$strNewId}]]", $this->fields['content']);
        $this->index();
        return parent::save();
    }

    /**
     * Vérouille la page et l'enregistre sans mettre à jour l'historique des modifications
     *
     * @param $booLock boolean true si la page doit être vérouillée
     *
     * @return boolean true si la page a pu être enregistrée
     */
    public function lock($booLock = true)
    {
        $this->fields['locked'] = $booLock;
        return parent::save();
    }

    /**
     * Retourne l'historique des modification de la page dans un tableau
     *
     * @return array tableau contenant l'historique des modifications
     */
    public function getHistory()
    {
        $db = ploopi\db::get();

        $db->query("
            SELECT      ph.*, u.lastname, u.firstname, u.login
            FROM        ploopi_mod_wiki_page_history ph
            LEFT JOIN   ploopi_user u ON u.id = ph.id_user
            WHERE       ph.id_page = '".$db->addslashes($this->fields['id'])."'
            AND         ph.id_module = {$this->fields['id_module']}
            ORDER BY    ph.revision DESC
        ");

        return $db->getarray();
    }

    /**
     * Renomme une page, redirige éventuellement les liens depuis d'autres pages vers cette page
     *
     * @param string $strNewId nouvel identifiant de la page
     * @param boolean $booRedirectLinks true si les liens vers cette page doivent être redirigés
     *
     * @return boolean true si la page a pu être enregistrée
     */
    public function rename($strNewId, $booRedirectLinks)
    {
        $db = ploopi\db::get();

        $this->remove_index();

        // Renommer l'historique
        $db->query("UPDATE ploopi_mod_wiki_page_history SET id_page = '".$db->addslashes($strNewId)."' WHERE id_page = '".$db->addslashes($this->fields['id'])."' AND id_module = {$this->fields['id_module']}");

        // Renommer les liens vers le nouvel ID (on sélectionne d'abord les pages du module)
        $rs = $db->query("SELECT id FROM ploopi_mod_wiki_page WHERE id_module = {$this->fields['id_module']}");
        while ($row = $db->fetchrow($rs))
        {

            if ($booRedirectLinks)
            {
                $objWikiPage = new wiki_page();
                if ($objWikiPage->open($row['id'])) $objWikiPage->redirectLinks($strNewId);
            }
        }

        // Renommer la page
        $this->objPageHistory->fields['id_page'] = $this->fields['id'] = $strNewId;

        return $this->save();
    }


    public function remove_index()
    {
        ploopi\search_index::remove(_WIKI_OBJECT_PAGE, $this->fields['id'], $this->fields['id_module']);
    }

    /**
     * Ajoute la page à l'index de recherche
     */
    public function index()
    {
        $this->remove_index();
        ploopi\search_index::add(_WIKI_OBJECT_PAGE, $this->fields['id'], $this->fields['id'], strip_tags(ploopi\str::html_entity_decode(wiki_render($this->fields['content']))), $this->fields['id'], true, $this->fields['ts_created'], $this->fields['ts_modified']);
    }
}
