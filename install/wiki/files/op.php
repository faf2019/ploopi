<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Opérations du module wiki
 * 
 * @package wiki
 * @subpackage op
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * On vérifie qu'on est bien dans le module Booking.
 */

if (ploopi_ismoduleallowed('wiki'))
{
    /**
     * Affichage de l'aide en ligne
     */
    
    switch($ploopi_op)
    {
        case 'wiki_help':
            ob_start();
            include_once './modules/wiki/op_help.php';
            $content = ob_get_contents();
            ob_end_clean();
            ploopi_die($skin->create_popup('Syntaxe Wiki - Aide en Ligne', $content, 'popup_wiki_help'));
        break;
        
        case 'wiki_page_save':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? 'wiki' : $_GET['wiki_page_id'];

            $objWikiPage = new wiki_page();
            $objWikiPage->open($strWikiPageId);
            $objWikiPage->fields['id'] = $strWikiPageId; 
            
            if (isset($_POST['wiki_page_content'])) $objWikiPage->fields['content'] = $_POST['wiki_page_content'];
            $objWikiPage->save();
            
            ploopi_redirect("admin.php?wiki_page_id={$strWikiPageId}");   
        break;
                
        case 'wiki_page_delete':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? 'wiki' : $_GET['wiki_page_id'];

            $objWikiPage = new wiki_page();
            if ($objWikiPage->open($strWikiPageId)) $objWikiPage->delete();
            
            ploopi_redirect("admin.php");   
        break;
                
        case 'wiki_page_lock':
        case 'wiki_page_unlock':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? '' : $_GET['wiki_page_id'];
            
            $objWikiPage = new wiki_page();
            if ($objWikiPage->open($strWikiPageId)) $objWikiPage->lock($ploopi_op == 'wiki_page_lock');
            
            ploopi_redirect("admin.php?wiki_page_id={$strWikiPageId}");   
        break;
        
        case 'wiki_page_rename':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';
            
            ploopi_print_r($_POST);
            ploopi_print_r($_GET);
            
            if (isset($_POST['wiki_page_newid']) && isset($_GET['wiki_page_id']))
            {
                // pas de changement
                if ($_POST['wiki_page_newid'] == $_GET['wiki_page_id']) ploopi_redirect("admin.php?wiki_page_id={$_GET['wiki_page_id']}");
                else
                {
                    // On va vérifier que le "nouvel" ID n'existe pas déjà
                    $objWikiPageVerif = new wiki_page();
                    $objWikiPage = new wiki_page();
                    if (!$objWikiPageVerif->open($_POST['wiki_page_newid']) && $objWikiPage->open($_GET['wiki_page_id'])) 
                    {
                        $objWikiPage->rename($_POST['wiki_page_newid'], isset($_POST['wiki_page_rename_redirect']));
                        ploopi_redirect("admin.php?wiki_page_id={$objWikiPage->fields['id']}");
                    }
                    else ploopi_redirect("admin.php?op=wiki_page_rename&wiki_page_id={$_GET['wiki_page_id']}&wiki_rename_error");
                }
            }
            
            ploopi_redirect('admin.php');
        break;
    }
}
?>
