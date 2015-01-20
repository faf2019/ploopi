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
 * Partie publique du module permettant de gérer l'affichage des pages, l'historique (+diff) et le renommage
 *
 * @package wiki
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe wiki_page
 */

include_once './modules/wiki/classes/class_wiki_page.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$strWikiPageId = (empty($_GET['wiki_page_id'])) ? '' : $_GET['wiki_page_id'];

if ($strWikiPageId == '') // cas particulier, pas d'id renseigné => recherche de root
{
    $db->query("SELECT id FROM ploopi_mod_wiki_page WHERE root = 1 AND id_module = {$_SESSION['ploopi']['moduleid']}");
    if ($db->numrows())
    {
        $row = $db->fetchrow();
        $strWikiPageId = $row['id'];
    }
    else // Pas de page root ! => Problème !!!!
    {
        echo "bug";
    }
}

$objWikiPage = new wiki_page();
$booExists = $objWikiPage->open($strWikiPageId);

// Gestion de l'historique des visites
if (!isset($_SESSION['wiki']['history'])) $_SESSION['wiki']['history'] = array();

$arrUrlHistory = array();
foreach($_SESSION['wiki']['history'] as $strPageId) $arrUrlHistory[] = "<a href=\"".ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strPageId))."\">{$strPageId}</a>";

if ($booExists)
{
    if (empty($_SESSION['wiki']['history']) || $_SESSION['wiki']['history'][0] != $strWikiPageId)
    {
        array_unshift($_SESSION['wiki']['history'], $strWikiPageId);
        if (sizeof($_SESSION['wiki']['history']) > 5) array_pop($_SESSION['wiki']['history']);
    }
}

// Vérification du droit de modification
if ($op == 'wiki_page_modify' && (!ploopi_isactionallowed(_WIKI_ACTION_PAGE_MODIFY) || $objWikiPage->fields['locked'])) $op = '';

echo $skin->open_simplebloc(ploopi_htmlentities($strWikiPageId));
?>
<div>
    <div class="ploopi_tabs">
    <?php
        if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_MODIFY))
        {
            if ($booExists)
            {
                if ($objWikiPage->fields['locked'])
                {
                    ?>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_history&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_history') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_history.png" />Historique
                    </a>
                    <a style="cursor:not-allowed;color:#777;">
                        <img src="./modules/wiki/img/ico_delete.png" />Supprimer
                    </a>
                    <a style="cursor:not-allowed;color:#777;">
                        <img src="./modules/wiki/img/ico_rename.png" />Renommer
                    </a>
                    <?
                    if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_LOCK))
                    {
                        ?>
                        <a href="<? echo ploopi_urlencode_trusted("admin-light.php?ploopi_op=wiki_page_unlock&wiki_page_id=".urlencode($strWikiPageId)); ?>">
                            <img src="./modules/wiki/img/ico_unlock.png" />Déverrouiller
                        </a>
                        <?
                    }
                    ?>
                    <a style="cursor:not-allowed;color:#777;">
                        <img src="./modules/wiki/img/ico_printer.png" />Imprimer
                    </a>
                    <a style="cursor:not-allowed;color:#777;">
                        <img src="./modules/wiki/img/ico_modify.png" />Modifier
                    </a>
                    <?
                    if (!ploopi_isactionallowed(_WIKI_ACTION_PAGE_LOCK))
                    {
                        ?>
                        <img src="./modules/wiki/img/ico_lock.png" style="margin-left:4px;"/>
                        <span class="error">Cette page est verouillée, vous ne pouvez pas la modifier</span>
                        <?
                    }
                    ?>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == '') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_view.png" />Visualiser
                    </a>
                    <?
                }
                else
                {
                    ?>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_history&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_history') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_history.png" />Historique
                    </a>
                    <a href="javascript:void(0);" onclick="if (confirm('Êtes vous certain de vouloir supprimer cette page ?')) document.location.href='<? echo ploopi_urlencode_trusted("admin-light.php?ploopi_op=wiki_page_delete&wiki_page_id=".urlencode($strWikiPageId)); ?>'; return false;">
                        <img src="./modules/wiki/img/ico_delete.png" />Supprimer
                    </a>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_rename&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_rename') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_rename.png" />Renommer
                    </a>
                    <?
                    if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_LOCK))
                    {
                        ?>
                        <a href="<? echo ploopi_urlencode_trusted("admin-light.php?ploopi_op=wiki_page_lock&wiki_page_id=".urlencode($strWikiPageId)); ?>">
                            <img src="./modules/wiki/img/ico_lock.png" />Verrouiller
                        </a>
                        <?
                    }
                    ?>
                    <a href="javascript:void(0);" onclick="javascript:ploopi_openwin('<? echo ploopi_urlencode("admin-light.php?ploopi_op=wiki_page_print&wiki_page_id={$strWikiPageId}") ?>', 800, 600)">
                        <img src="./modules/wiki/img/ico_printer.png" />Imprimer
                    </a>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_modify&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_modify') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_modify.png" />Modifier
                    </a>
                    <a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == '') echo 'style="font-weight:bold;" '; ?>>
                        <img src="./modules/wiki/img/ico_view.png" />Visualiser
                    </a>
                    <?
                }
            }
            else
            {
                ?>
                <a style="cursor:not-allowed;color:#777;">
                    <img src="./modules/wiki/img/ico_history.png" />Historique
                </a>
                <a style="cursor:not-allowed;color:#777;">
                    <img src="./modules/wiki/img/ico_delete.png" />Supprimer
                </a>
                <a style="cursor:not-allowed;color:#777;">
                    <img src="./modules/wiki/img/ico_rename.png" />Renommer
                </a>
                <?
                if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_LOCK))
                {
                    ?>
                    <a style="cursor:not-allowed;color:#777;">
                        <img src="./modules/wiki/img/ico_lock.png" />Verrouiller
                    </a>
                    <?
                }
                ?>
                <a style="cursor:not-allowed;color:#777;">
                    <img src="./modules/wiki/img/ico_printer.png" />Imprimer
                </a>
                <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_modify&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_modify') echo 'style="font-weight:bold;" '; ?>>
                    <img src="./modules/wiki/img/ico_modify.png" />Modifier
                </a>
                <a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == '') echo 'style="font-weight:bold;" '; ?>>
                    <img src="./modules/wiki/img/ico_view.png" />Visualiser
                </a>
                <?
            }
        }
        else
        {

            if ($booExists)
            {
                ?>
                <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_history&wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == 'wiki_page_history') echo 'style="font-weight:bold;" '; ?>>
                    <img src="./modules/wiki/img/ico_history.png" />Historique
                </a>
                <a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == '') echo 'style="font-weight:bold;" '; ?>>
                    <img src="./modules/wiki/img/ico_view.png" />Visualiser
                </a>
                <?
            }
            else
            {
                ?>
                <a style="cursor:not-allowed;color:#777;">
                    <img src="./modules/wiki/img/ico_history.png" />Historique
                </a>
                <a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>" <? if ($op == '') echo 'style="font-weight:bold;" '; ?>>
                    <img src="./modules/wiki/img/ico_view.png" />Visualiser
                </a>
                <?
            }
        }
        ?>
    </div>
    <div style="padding:4px 8px;background-color:#ddd;border-bottom:1px solid #ccc;">
        Pages visitées : <?php echo implode(' &raquo; ', $arrUrlHistory); ?>
    </div>


    <?
    switch($op)
    {
        case 'wiki_page_rename':
            include_once './include/classes/form.php';

            $objForm = new form( 'wiki_form_page_rename', ploopi_urlencode_trusted("admin.php?ploopi_op=wiki_page_rename&wiki_page_id=".urlencode($strWikiPageId)), 'post', array('legend' => '* Champs obligatoires') );

            $objForm->addField( new form_field('input:text', 'Titre:', $objWikiPage->fields['id'], 'wiki_page_newid', null, array('required' => true)) );
            $objForm->addField( new form_checkbox('Rediriger les liens existants:', 1, true, 'wiki_page_rename_redirect', 'wiki_page_rename_redirect', array('class_form' => 'ploopi_checkbox')) );

            $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
            $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

            echo $objForm->render();
        break;

        case 'wiki_page_history':
            if (!empty($_GET['wiki_page_revision']) && is_numeric($_GET['wiki_page_revision']))
            {
                $objWikiPageHistory = new wiki_page_history();
                if ($objWikiPageHistory->open($strWikiPageId, $_GET['wiki_page_revision']))
                {
                    $objUser = new user();
                    $strUser = $objUser->open($objWikiPageHistory->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>';

                    $strLocalDate = implode(' ', ploopi_timestamp2local($objWikiPageHistory->fields['ts_modified']));
                    $strRev = "<strong>{$_GET['wiki_page_revision']}</strong> ({$strUser}, {$strLocalDate})";

                    ?>
                    <div id="wiki_diff_title">Révision <? echo ploopi_htmlentities($strRev); ?>
                    <?
                    if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_MODIFY) && !$objWikiPage->fields['locked'])
                    {
                        ?> - <a href="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_modify&wiki_page_id=".urlencode($strWikiPageId)."&wiki_page_revision={$_GET['wiki_page_revision']}"); ?>">Revenir à cette version</a><?
                    } ?> :</div>
                    <div id="wiki_page" class="wiki_page"><? echo wiki_render($objWikiPageHistory->fields['content']); ?></div>
                    <?php
                }
            }
            elseif (!empty($_POST['wiki_history_diff1']) && !empty($_POST['wiki_history_diff2']) && is_numeric($_POST['wiki_history_diff1']) && is_numeric($_POST['wiki_history_diff2']))
            {
                include_once './modules/wiki/classes/class_wiki_page_history.php';

                $strContent1 = $strContent2 = null;

                // Infos sur la revision "1"
                if ($_POST['wiki_history_diff1'] != $objWikiPage->fields['revision'])
                {
                    $objWikiPageHistory = new wiki_page_history();
                    if ($objWikiPageHistory->open($strWikiPageId, $_POST['wiki_history_diff1'])) $strContent1 = $objWikiPageHistory->fields['content'];

                    $objUser = new user();
                    $strUser = $objUser->open($objWikiPageHistory->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>';

                    $strLocalDate = implode(' ', ploopi_timestamp2local($objWikiPageHistory->fields['ts_modified']));

                    $strRevision = "&op=wiki_page_history&wiki_page_revision={$_POST['wiki_history_diff1']}";
                }
                else
                {
                    $strContent1 = $objWikiPage->fields['content'];

                    $objUser = new user();
                    $strUser = $objUser->open($objWikiPage->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>';

                    $strLocalDate = implode(' ', ploopi_timestamp2local($objWikiPage->fields['ts_modified']));

                    $strRevision = '';
                }

                $strRevLink1 = "<a href=\"".ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId).$strRevision)."\"><strong>{$_POST['wiki_history_diff1']}</strong> ({$strUser}, {$strLocalDate})</a>";

                // Infos sur la revision "2"
                if ($_POST['wiki_history_diff2'] != $objWikiPage->fields['revision'])
                {
                    $objWikiPageHistory = new wiki_page_history();
                    if ($objWikiPageHistory->open($strWikiPageId, $_POST['wiki_history_diff2'])) $strContent2 = $objWikiPageHistory->fields['content'];

                    $objUser = new user();
                    $strUser = $objUser->open($objWikiPageHistory->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>';

                    $strLocalDate = implode(' ', ploopi_timestamp2local($objWikiPageHistory->fields['ts_modified']));

                    $strRevision = "&op=wiki_page_history&wiki_page_revision={$_POST['wiki_history_diff2']}";
                }
                else
                {
                    $strContent2 = $objWikiPage->fields['content'];

                    $objUser = new user();
                    $strUser = $objUser->open($objWikiPage->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>';

                    $strLocalDate = implode(' ', ploopi_timestamp2local($objWikiPage->fields['ts_modified']));

                    $strRevision = '';
                }

                $strRevLink2 = "<a href=\"".ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId).$strRevision)."\"><strong>{$_POST['wiki_history_diff2']}</strong> ({$strUser}, {$strLocalDate})</a>";

                include_once "Horde/String.php";
                include_once "Horde/Text/Diff.php";
                include_once "Horde/Text/Diff/Op/Base.php";
                include_once "Horde/Text/Diff/Op/Add.php";
                include_once "Horde/Text/Diff/Op/Change.php";
                include_once "Horde/Text/Diff/Op/Copy.php";
                include_once "Horde/Text/Diff/Engine/Native.php";
                include_once "Horde/Text/Diff/Renderer.php";
                include_once "Horde/Text/Diff/Renderer/Inline.php";

                if ($strContent1 == $strContent2) $strDiff = $strContent1;
                else
                {
                    $objTextDiff = new Horde_Text_Diff('auto', array(explode("\n", $strContent2), explode("\n", $strContent1)));

                    $objRenderer = new Horde_Text_Diff_Renderer_Inline();
                    $strDiff = $objRenderer->render($objTextDiff);
                }

                echo '<div id="wiki_diff_title">Différences entre les révisions '.$strRevLink2.' et '.$strRevLink1.' :</div><div id="wiki_diff">'.ploopi_nl2br($strDiff).'</div><div id="wiki_diff_legend"<span>Légende:</span>&nbsp;<ins>Texte ajouté</ins>&nbsp;<del>Texte supprimé</del></div>';
            }
            else
            {

                $columns = array();
                $values = array();

                $columns['actions_right']['diff2'] =
                    array(
                        'label' => '&nbsp;',
                        'width' => 20,
                        'options' => array('sort' => true)
                    );

                $columns['actions_right']['diff1'] =
                    array(
                        'label' => '&nbsp;',
                        'width' => 20,
                        'options' => array('sort' => true)
                    );

                $columns['right']['revision'] =
                    array(
                        'label' => 'Rev.',
                        'width' => 60,
                        'options' => array('sort' => true)
                    );


                $columns['left']['ts_modified'] =
                    array(
                        'label' => 'Date de mise à jour',
                        'width' => 160,
                        'options' => array('sort' => true)
                    );

                $columns['auto']['user'] =
                    array(
                        'label' => 'Auteur',
                        'options' => array('sort' => true)
                    );

                $objUser = new user();

                $arrLocalDate = ploopi_timestamp2local($objWikiPage->fields['ts_modified']);

                $values[] = array(
                    'values' => array(
                        'revision' => array(
                            'label' => $objWikiPage->fields['revision'],
                            'style' => 'text-align:right;'
                        ),
                        'diff1' => array(
                            'label' => '<input type="radio" name="wiki_history_diff1" style="margin-left:2px;" value="'.$objWikiPage->fields['revision'].'" tabindex="100" checked="checked" />'
                        ),
                        'diff2' => array(
                            'label' => '&nbsp;'
                        ),
                        'user' => array(
                            'label' => $objUser->open($objWikiPage->fields['id_user']) ? trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}") : '<em>Utilisateur supprimé</em>'
                        ),
                        'ts_modified' => array(
                            'label' => implode(' ', $arrLocalDate)
                        )
                    ),
                    'description' => "Ouvrir la dernière révision ({$objWikiPage->fields['revision']})",
                    'link' => ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId))
                );

                $intTabIndex = 101;
                $booChecked = false;
                $booLast = false;

                foreach($objWikiPage->getHistory() as $arrPageHistory)
                {
                    $arrLocalDate = ploopi_timestamp2local($arrPageHistory['ts_modified']);
                    $booLast = $arrPageHistory['revision'] == 1;

                    $values[] = array(
                        'values' => array(
                            'revision' => array(
                                'label' => ploopi_htmlentities($arrPageHistory['revision']),
                                'style' => 'text-align:right;'
                            ),
                            'diff1' => array(
                                'label' => $booLast ? '&nbsp;' : '<input type="radio" name="wiki_history_diff1" style="margin-left:2px;" value="'.$arrPageHistory['revision'].'" tabindex="'.$intTabIndex++.'" />'
                            ),
                            'diff2' => array(
                                'label' => '<input type="radio" name="wiki_history_diff2" style="margin-left:2px;" value="'.$arrPageHistory['revision'].'" tabindex="'.$intTabIndex++.'" '.($booChecked ? '' : 'checked="checked" ').'/>'
                            ),
                            'user' => array(
                                'label' => is_null($arrPageHistory['lastname']) ? '<em>Utilisateur supprimé</em>' : ploopi_htmlentities(trim("{$arrPageHistory['lastname']} {$arrPageHistory['firstname']}"))
                            ),
                            'ts_modified' => array(
                                'label' => implode(' ', $arrLocalDate)
                            )
                        ),
                        'description' => ploopi_htmlentities("Ouvrir la révision {$arrPageHistory['revision']}"),
                        'link' => ploopi_urlencode_trusted("admin.php?op=wiki_page_history&wiki_page_id=".urlencode($strWikiPageId)."&wiki_page_revision={$arrPageHistory['revision']}")
                    );

                    if (!$booChecked) $booChecked = true;
                }

                ?>
                <form action="<? echo ploopi_urlencode_trusted("admin.php?op=wiki_page_history&wiki_page_id=".urlencode($strWikiPageId)); ?>" method="post">
                <?php $skin->display_array($columns, $values, 'wiki_history', array('sortable' => true, 'orderby_default' => 'revision', 'sort_default' => 'DESC')); ?>
                <div style="text-align:right;"><input type="submit" class="button" value="Voir les différences" style="margin:4px;"/></div>
                </form>
                <?php
            }
        break;

        case 'wiki_page_modify':
            if (ploopi_isactionallowed(_WIKI_ACTION_PAGE_MODIFY))
            {
                $strPageContent = $objWikiPage->fields['content'];

                // Récupération du contenu d'une révision particulière (si demandé)
                if (!empty($_GET['wiki_page_revision']) && is_numeric($_GET['wiki_page_revision']))
                {
                    $objWikiPageHistory = new wiki_page_history();
                    if ($objWikiPageHistory->open($strWikiPageId, $_GET['wiki_page_revision'])) $strPageContent = $objWikiPageHistory->fields['content'];
                }
                ?>
                <div id="wiki_modify">
                    <form action="<? echo ploopi_urlencode_trusted("admin-light.php?ploopi_op=wiki_page_save&wiki_page_id=".urlencode($strWikiPageId)); ?>" method="post">
                        <textarea accesskey="e" class="wiki-edit text" style="width:99%;" id="wiki_page_content" name="fck_wiki_page_content" rows="25"><? echo ploopi_htmlentities($strPageContent); ?></textarea>
                        <div style="text-align:right"><input type="button" class="button" value="Annuler" onclick="javascript:document.location.href='<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($strWikiPageId)); ?>';" /><input type="submit" class="button" value="Enregistrer" style="margin-left:4px;" /></div>
                    </form>
                </div>
                <script type="text/javascript">
                    //<![CDATA[
                    var toolbar = new jsToolBar($('wiki_page_content'));
                    toolbar.setHelpLink('Formatage du texte: <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(350, event, \'popup_wiki_help\', \'<? echo ploopi_urlencode('admin-light.php?ploopi_op=wiki_help'); ?>\');"; return false;">Aide</a>');
                    toolbar.draw();
                    //]]>
                </script>
                <?
            }
            else ploopi_redirect('admin.php');
        break;

        // consultation
        default:
            ?>
            <div id="wiki_page" class="wiki_page"><? echo wiki_render($objWikiPage->fields['content']); ?></div>
            <?
        break;
    }
    ?>
    <div style="border-top:1px solid #ccc;">
        <?php
            ploopi_subscription(
                _WIKI_OBJECT_PAGE,
                $strWikiPageId,
                array(
                    _WIKI_ACTION_PAGE_MODIFY,
                    _WIKI_ACTION_PAGE_DELETE
                ),
                "à « {$strWikiPageId} »"
            );
        ?>
    </div>
    <div style="border-top:1px solid #ccc;">
        <?php ploopi_annotation(_WIKI_OBJECT_PAGE, $strWikiPageId, $strWikiPageId); ?>
    </div>
</div>
<?php echo $skin->close_simplebloc(); ?>
