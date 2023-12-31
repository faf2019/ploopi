<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @author Ovensia
 */

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
echo ploopi\skin::get()->open_simplebloc('Planning');
?>
<div id="planning_main">
<?php include_once './modules/planning/public_planning.php'; ?>
</div>
<?php
echo ploopi\skin::get()->close_simplebloc();

/**
 * Affichage du popup de sélection des ressources
 */
ob_start();
?>
<div id="planning_ressource_list">
<form id="planning_resource_list_form" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_setresources'); ?>" method="post" onsubmit="javascript:ploopi.xhr.submit(jQuery('#planning_resource_list_form')[0], 'planning_main'); return false;">
<?php
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
                <img src="<?php echo "{$_SESSION['ploopi']['template_path']}/img/system/ico_{$strResourceType}.png"; ?>" />
                <strong><?php echo ploopi\str::htmlentities($strResourceTitle); ?></strong>
            </p>
        </a>
        <div id="planning_<?php echo $strResourceType; ?>_list" style="display:block;">
            <?php
            foreach($arrResourceType as $row)
            {
                ?>
                <p class="checkbox" style="background-color:<?php echo $row['color']; ?>;" onclick="javascript:ploopi.checkbox_click(event, 'planning_resource<?php echo $strResourceType[0].$row['id']; ?>');">
                    <input type="checkbox" name="planning_resources[<?php echo $strResourceType; ?>][<?php echo $row['id']; ?>]" id="planning_resource<?php echo $strResourceType[0].$row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if (!empty($arrSearchPattern['planning_resources'][$strResourceType][$row['id']])) echo 'checked="checked"'; ?> onchange="javascript:jQuery('#planning_resource_list_form')[0].onsubmit();" />
                    <span><?php echo ploopi\str::htmlentities($row['label']); ?><span>
                </p>
                <?php
            }
            ?>
        </div>
        <?php
    }
}
?>
</form>

<form id="planning_search_form" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_search'); ?>" method="post" onsubmit="javascript:ploopi.xhr.submit(jQuery('#planning_search_form')[0], 'planning_search_result'); return false;">
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
<?php
$content = ob_get_contents();
ob_end_clean();

echo ploopi\skin::get()->open_popup(
    'Ressources affichées',
    $content,
    'popup_planning',
    array(
        'intWidth' => 200,
        'intPosx' => 'jQuery(\'#planning_display\').viewportOffset().left +  $(\'#planning_display\').width() - 206',
        'intPosy' => 'jQuery(\'#planning_display\').viewportOffset().top + 20',
        'booCentered' => false
    )
);
?>
