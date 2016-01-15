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
 * Partie publique du module
 *
 * @package planning
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

echo $skin->create_pagetitle(ploopi_htmlentities($_SESSION['ploopi']['modulelabel']));
echo $skin->open_simplebloc('Planning');
?>
<div id="planning_main">
<? include_once './modules/planning/public_planning.php'; ?>
</div>
<?
echo $skin->close_simplebloc();

/**
 * Affichage du popup de sélection des ressources
 */
ob_start();
?>
<div id="planning_ressource_list">
<form id="planning_resource_list_form" action="<? echo ploopi_urlencode('admin-light.php?ploopi_op=planning_setresources'); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform($('planning_resource_list_form'), 'planning_main'); return false;">
<?
foreach ($arrResources as $strResourceType => $arrResourceType)
{
    if (!empty($arrResourceType))
    {
        $strResourceTitle = '';
        switch($strResourceType)
        {
            case 'user': $strResourceTitle = 'Utilisateurs'; break;
            case 'group': $strResourceTitle = 'Groupes'; break;
        }
        ?>
        <a href="javascript:void(0);" onclick="javascript:with ($('planning_<?php echo $strResourceType; ?>_list')) { style.display = (style.display == 'block') ? 'none' : 'block'; }">
            <p class="ploopi_va" style="border-width:1px 0;border-style:solid;border-color:#bbb;background-color:#ddd;">
                <img src="<? echo "{$_SESSION['ploopi']['template_path']}/img/system/ico_{$strResourceType}.png"; ?>" />
                <strong><? echo ploopi_htmlentities($strResourceTitle); ?></strong>
            </p>
        </a>
        <div id="planning_<?php echo $strResourceType; ?>_list" style="display:block;">
            <?
            foreach($arrResourceType as $row)
            {
                ?>
                <p class="checkbox" style="background-color:<? echo $row['color']; ?>;" onclick="javascript:ploopi_checkbox_click(event, 'planning_resource<? echo $strResourceType[0].$row['id']; ?>');">
                    <input type="checkbox" name="planning_resources[<? echo $strResourceType; ?>][<? echo $row['id']; ?>]" id="planning_resource<? echo $strResourceType[0].$row['id']; ?>" value="<? echo $row['id']; ?>" <? if (!empty($arrSearchPattern['planning_resources'][$strResourceType][$row['id']])) echo 'checked="checked"'; ?> onchange="javascript:$('planning_resource_list_form').onsubmit();" />
                    <span><? echo ploopi_htmlentities($row['label']); ?><span>
                </p>
                <?
            }
            ?>
        </div>
        <?
    }
}
?>
</form>

<form id="planning_search_form" action="<? echo ploopi_urlencode('admin-light.php?ploopi_op=planning_search'); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform($('planning_search_form'), 'planning_search_result'); return false;">
<p class="ploopi_va" style="border-width:1px 0;border-style:solid;border-color:#bbb;background-color:#ddd;">
    <img src="./modules/planning/img/ico_search.png" />
    <strong>Rechercher un événement</strong>
</p>
<p class="ploopi_va">
    <input type="text" title="Champ de recherche" alt="Champ de recherche" class="text" style="width:145px;" name="query_string" value="" />
    <input type="submit" title="Bouton pour valider la recherche" class="button" style="width:30px;" value="go" />
</p>
</form>
</div>

<div id="planning_search_result">
</div>
<?
$content = ob_get_contents();
ob_end_clean();

echo $skin->open_popup(
    'Ressources affichées',
    $content,
    'popup_planning',
    array(
        'intWidth' => 200,
        'intPosx' => '$(\'planning_display\').viewportOffset().left +  $(\'planning_display\').getWidth() - 206',
        'intPosy' => '$(\'planning_display\').viewportOffset().top + 20',
        'booCentered' => false
    )
);
?>
