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
        case 'wiki_page_print':
            ploopi_init_module('wiki');
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? 'wiki' : $_GET['wiki_page_id'];

            $objWikiPage = new wiki_page();
            if($objWikiPage->open($strWikiPageId))
            {
                ?>
                <html>
                <head>
                    <title><?php echo ploopi_htmlentities($strWikiPageId); ?></title>
                    <link rel="stylesheet" href="./modules/wiki/include/styles.css" type="text/css" />
                    <link rel="stylesheet" href="./vendor/components/highlightjs/styles/vs.css">
                    <script src="./vendor/components/highlightjs/highlight.pack.min.js"></script>
                    <script>hljs.initHighlightingOnLoad();</script>
                    <style>@page { size : landscape }</style>
                    <script type="text/javascript">window.onload = function() { print(); };</script>
                </head>
                <body class="wiki_print">
                    <div id="wiki_page" class="wiki_page"><?php echo wiki_render($objWikiPage->fields['content']); ?></div>
                    <div id="wiki_page_print_info">R&eacute;vision num&eacute;ro <?php echo ploopi_htmlentities($objWikiPage->fields['revision']); ?> modifi&eacute; le <?php echo implode(' à ', ploopi_timestamp2local($objWikiPage->fields['ts_modified'])); ?></div>
                </body>
                </html>
                <?php
            }
            ploopi_die();
        break;



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

            if (isset($_POST['fck_wiki_page_content'])) $objWikiPage->fields['content'] = ploopi_iso8859_clean($_POST['fck_wiki_page_content']);
            $objWikiPage->save();

            // on envoie le ticket de notification d'action sur l'objet
            ploopi_subscription_notify(
                _WIKI_OBJECT_PAGE,
                $objWikiPage->fields['id'],
                _WIKI_ACTION_PAGE_MODIFY,
                $objWikiPage->fields['id'],
                array_keys(
                    ploopi_subscription_getusers(
                        _WIKI_OBJECT_PAGE,
                        $objWikiPage->fields['id'],
                        array(_WIKI_ACTION_PAGE_MODIFY)
                    )
                ),
                'Cet objet à été modifié'
            );

            ploopi_create_user_action_log(_WIKI_ACTION_PAGE_MODIFY, $objWikiPage->fields['id']);

            ploopi_search_remove_index(_WIKI_OBJECT_PAGE, $objWikiPage->fields['id']);
            ploopi_search_create_index(_WIKI_OBJECT_PAGE, $objWikiPage->fields['id'], $objWikiPage->fields['id'], strip_tags(ploopi_html_entity_decode(wiki_render($objWikiPage->fields['content']))), $objWikiPage->fields['id'], true, $objWikiPage->fields['ts_created'], $objWikiPage->fields['ts_modified']);

            ploopi_redirect_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId));
        break;

        case 'wiki_page_delete':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? 'wiki' : $_GET['wiki_page_id'];

            $objWikiPage = new wiki_page();
            if ($objWikiPage->open($strWikiPageId))
            {
                ploopi_search_remove_index(_WIKI_OBJECT_PAGE, $strWikiPageId);
                $objWikiPage->delete();

                // on envoie le ticket de notification d'action sur l'objet
                ploopi_subscription_notify(
                    _WIKI_OBJECT_PAGE,
                    $strWikiPageId,
                    _WIKI_ACTION_PAGE_DELETE,
                    $strWikiPageId,
                    array_keys(
                        ploopi_subscription_getusers(
                            _WIKI_OBJECT_PAGE,
                            $strWikiPageId,
                            array(_WIKI_ACTION_PAGE_DELETE)
                        )
                    ),
                    'Cet objet à été supprimé'
                );

                ploopi_create_user_action_log(_WIKI_ACTION_PAGE_DELETE, $strWikiPageId);
            }

            ploopi_redirect("admin.php");
        break;

        case 'wiki_page_lock':
        case 'wiki_page_unlock':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            $strWikiPageId = (empty($_GET['wiki_page_id'])) ? '' : $_GET['wiki_page_id'];

            $objWikiPage = new wiki_page();
            if ($objWikiPage->open($strWikiPageId))
            {
                $objWikiPage->lock($ploopi_op == 'wiki_page_lock');
                ploopi_create_user_action_log($ploopi_op == 'wiki_page_lock' ? _WIKI_ACTION_PAGE_LOCK : _WIKI_ACTION_PAGE_UNLOCK, $strWikiPageId);
            }
            ploopi_redirect_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId));
        break;

        case 'wiki_page_rename':
            ploopi_init_module('wiki', false, false, false);
            include_once './modules/wiki/classes/class_wiki_page.php';

            if (isset($_POST['wiki_page_newid']) && isset($_GET['wiki_page_id']))
            {
                // pas de changement
                if ($_POST['wiki_page_newid'] == $_GET['wiki_page_id']) ploopi_redirect_trusted("admin.php?wiki_page_id=".urlencode($_GET['wiki_page_id']));
                else
                {
                    // On va vérifier que le "nouvel" ID n'existe pas déjà
                    $objWikiPageVerif = new wiki_page();
                    $objWikiPage = new wiki_page();
                    if (!$objWikiPageVerif->open($_POST['wiki_page_newid']) && $objWikiPage->open($_GET['wiki_page_id']))
                    {
                        $objWikiPage->rename($_POST['wiki_page_newid'], isset($_POST['wiki_page_rename_redirect']));
                        ploopi_create_user_action_log(_WIKI_ACTION_PAGE_RENAME, "{$_GET['wiki_page_id']} -> {$_POST['wiki_page_newid']}");
                        ploopi_redirect_trusted("admin.php?wiki_page_id=".urlencode($objWikiPage->fields['id']));
                    }
                    else ploopi_redirect_trusted("admin.php?op=wiki_page_rename&wiki_page_id=".urlencode($_GET['wiki_page_id'])."&wiki_rename_error");
                }
            }

            ploopi_redirect('admin.php');
        break;
    }
}
?>
