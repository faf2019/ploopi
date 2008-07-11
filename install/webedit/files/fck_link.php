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
 * Explorateur de rubriques/pages intégré à FCKeditor
 * 
 * @package webedit
 * @subpackage fckeditor
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ce script peut être appelé depuis le module WebEdit (lien vers un article) ou depuis un module externe (via FCKeditor).
 * Il faut donc 'choisir' le moduleid de travail.
 * Par défaut on prend le module WEBEDIT sur lequel on est déjà.
 */
if (isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['moduletype']) && $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['moduletype'] == 'webedit')
{
    $webedit_idm = $_SESSION['ploopi']['moduleid'];
}
else
{
    /**
     * Sinon on va chercher le 1er dispo dans les modules accessibles depuis l'espace de travail courant.
     */
    $webedit_idm = 0;
    foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
    {
        if (isset($_SESSION['ploopi']['modules'][$idm]['active']) && $_SESSION['ploopi']['modules'][$idm]['active'] && $_SESSION['ploopi']['modules'][$idm]['label'] == 'WEBEDIT') $webedit_idm = $idm;
    }
}

if ($webedit_idm)
{
    /**
     * Initialisation du module
     */
    ploopi_init_module('webedit');

    /**
     * Inclusion des classes du module
     */
    include_once './modules/webedit/class_article.php';
    include_once './modules/webedit/class_heading.php';

    /**
     * Chargement des rubriques et articles
     */
    $headings = webedit_getheadings($webedit_idm);
    $articles = webedit_getarticles($webedit_idm);

    switch($ploopi_op)
    {
        case 'webedit_detail_heading';
            echo webedit_build_tree($_GET['hid'], $_GET['str'], (isset($_GET['option'])) ? $_GET['option'] : '');
            ploopi_die();
        break;

        case 'webedit_selectlink':
            $treeview = webedit_gettreeview('selectlink');
            echo $skin->display_treeview($treeview['list'], $treeview['tree']);
        break;
    }

}
else echo "Aucun module WEBEDIT disponible";
?>
