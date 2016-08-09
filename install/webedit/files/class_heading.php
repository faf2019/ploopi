<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2009-2010 HeXad
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
 * Gestion des rubriques
 *
 * @package webedit
 * @subpackage heading
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_webedit_heading
 *
 * @package webedit
 * @subpackage heading
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class webedit_heading extends data_object
{
    /**
     * Contructeur de la classe
     *
     * @return webedit_heading
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_webedit_heading');
    }

    /**
     * Supprime la rubrique et sont contenu (articles, sous-rubriques)
     */

    public function delete()
    {
        include_once './modules/webedit/class_article.php';

        global $db;

        // suppression des abonnements (frontoffice)
        $db->query("DELETE FROM ploopi_mod_webedit_heading_subscriber WHERE id_heading = {$this->fields['id']}");

        // suppression des sous-rubriques
        $rs1 = $db->query("SELECT id FROM ploopi_mod_webedit_heading WHERE id_heading = {$this->fields['id']}");

        while ($row = $db->fetchrow($rs1))
        {
            $h = new webedit_heading();
            if($h->open($row['id'])) $h->delete();
        }

        // Suppression des brouillons de la rubrique (les articles avec)
        $rs2 = $db->query("SELECT id FROM ploopi_mod_webedit_article_draft WHERE id_heading = {$this->fields['id']}");

        while ($row = $db->fetchrow($rs2))
        {
            $a = new webedit_article('draft');
            if($a->open($row['id'])) $a->delete();
        }

        // Changement de position des autres rubriques
        $db->query("UPDATE ploopi_mod_webedit_heading SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']} AND id_module = {$this->fields['id_module']}");

        // Supprime les redirections vers cette rubrique
        $db->query("UPDATE ploopi_mod_webedit_heading SET linkedpage = 0 WHERE linkedpage = 'h{{$this->fields['id_heading']}}'");

        parent::delete();
    }
}
