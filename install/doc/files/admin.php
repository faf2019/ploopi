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
 * Interface d'administration du module
 *
 * @package doc
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('doc');

/**
 * On vérifie que l'utilisateur est administrateur du module
 */

if (ploopi_isactionallowed(_DOC_ACTION_ADMIN))
{
    $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

    if (!empty($_GET['docTabItem'])) $_SESSION['doc']['docTabItem'] = $_GET['docTabItem'];
    if (!isset($_SESSION['doc']['docTabItem'])) $_SESSION['doc']['docTabItem'] = '';

    $tabs[_DOC_TAB_PARSERS] =
        array(
            'title' => 'Gestion des analyseurs',
            'url' => "admin.php?docTabItem="._DOC_TAB_PARSERS
        );

    $tabs[_DOC_TAB_INDEX] =
        array(
            'title' => 'Indexation',
            'url' => "admin.php?docTabItem="._DOC_TAB_INDEX
        );

    $tabs[_DOC_TAB_STATS] =
        array(
            'title' => 'Statistiques',
            'url' => "admin.php?docTabItem="._DOC_TAB_STATS
        );

    echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
    echo $skin->create_tabs($tabs, $_SESSION['doc']['docTabItem']);

    switch($_SESSION['doc']['docTabItem'])
    {
        case _DOC_TAB_PARSERS:
            switch($op)
            {
                // save
                case 'docparser_save':
                    include_once './modules/doc/class_docparser.php';
                    $docparser = new docparser();
                    if (isset($_POST['docparser_id'])) $docparser->open($_POST['docparser_id']);
                    $docparser->setvalues($_POST,'docparser_');
                    $docparser->save();
                    ploopi_redirect("admin.php");
                break;

                // delete
                case 'docparser_delete':
                    include_once './modules/doc/class_docparser.php';
                    $docparser = new docparser();
                    $docparser->open($_GET['docparser_id']);
                    $docparser->delete();
                    ploopi_redirect('admin.php');
                break;

                case 'docpaser_modify':
                default:
                    include './modules/doc/admin_docparser_list.php';
                break;
            }
        break;

        case _DOC_TAB_INDEX:
            switch($op)
            {
                case 'execute':
                    include './modules/doc/admin_docparser_execute.php';
                break;

                default:
                    echo $skin->open_simplebloc('Indexation');
                    ?>
                    <div style="padding:4px;">
                        <input type="button" class="button" value="Ré-Indexer" onclick="javascript:ploopi_confirmlink('admin.php?op=execute','Attention cette procédure va ré-indexer tous les fichiers. Le traitement peut être très long...');">&nbsp;Cette procédure permet de ré-indexer le contenu des documents du module.
                    </div>
                    <?php
                    echo $skin->close_simplebloc();
                break;
            }
        break;

        case _DOC_TAB_STATS:
            include './modules/doc/admin_docparser_stats.php';
        break;
    }
}
?>
