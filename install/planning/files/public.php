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
 * @author St�phane Escaich
 */

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->open_simplebloc('Planning');
?>
<div id="planning_main">
<? include_once './modules/planning/public_planning.php'; ?>
</div>
<?
echo $skin->close_simplebloc(); 

/**
 * Affichage du popup de s�lection des ressources
 */
ob_start();
?>
<div id="planning_ressource_list">
<form id="planning_resource_list_form" action="<? echo ploopi_urlencode('admin-light.php?ploopi_op=planning_setresources'); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform(this, 'planning_main');">
<?    
foreach ($arrResources as $strResourceType => $arrResourceType)
{
    $strResourceTitle = '';
    switch($strResourceType)
    {
        case 'user': $strResourceTitle = 'Utilisateurs'; break;
        case 'group': $strResourceTitle = 'Groupes'; break;
    }
    ?>
    <p class="ploopi_va" style="border-width:1px 0;border-style:solid;border-color:#bbb;background-color:#ddd;">
        <img src="<? echo "{$_SESSION['ploopi']['template_path']}/img/system/ico_{$strResourceType}.png"; ?>" />
        <strong><? echo $strResourceTitle; ?></strong>
    </p>
    <?
    foreach($arrResourceType as $row)
    {
        ?>
        <p class="checkbox" style="background-color:<? echo $row['color']; ?>;" onclick="javascript:ploopi_checkbox_click(event, 'planning_resource<? echo $strResourceType[0].$row['id']; ?>');">
            <input type="checkbox" name="planning_resources[<? echo $strResourceType; ?>][<? echo $row['id']; ?>]" id="planning_resource<? echo $strResourceType[0].$row['id']; ?>" value="<? echo $row['id']; ?>" <? if (!empty($arrSearchPattern['planning_resources'][$strResourceType][$row['id']])) echo 'checked="checked"'; ?> onclick="javascript:$('planning_resource_list_form').onsubmit();" />
            <span><? echo $row['label']; ?><span>
        </p>
        <?
    }
}
?>
</form>
</div>
<?    
$content = ob_get_contents();
ob_end_clean();

echo $skin->open_popup(
    'Ressources affich�es', 
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