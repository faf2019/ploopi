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
 * Affichage des informations sur un dossier
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see ploopi_share_get
 * @see ploopi_validation_get
 * @see _DOC_OBJECT_FOLDER
 */

/**
 * Si $currentfolder est empty (vide ou 0), c'est que l'on est dans le dossier personnel (racine)
 */

$objFolder = new docfolder();
if (empty($currentfolder) || !$objFolder->open($currentfolder) || !$objFolder->isEnabled()) $currentfolder = 0;

if (!empty($currentfolder)) 
{
    $style = ($objFolder->fields['published']) ? '' : 'style="background-color:#ffe0e0;"';
    
    ?>
    <div class="doc_folderinfo" <?php echo $style; ?>>
        <?php
        //if (ploopi_isactionallowed(_DOC_ACTION_MODIFYFOLDER) && (!$docfolder->fields['readonly'] || $_SESSION['ploopi']['userid'] == $docfolder->fields['id_user']))
        //{
            ?>
            <div style="float:right;height:40px;">
                <p style="margin:0;padding:4px 8px;">
                    <a href="<?php echo ploopi_urlencode("admin.php?op=doc_folderform&currentfolder={$currentfolder}&addfolder=0"); ?>"><img style="border:0;" src="./modules/doc/img/edit.png" /></a>
                </p>
            </div>
            <?php
        //}
        ?>
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 0px 4px 8px;">
                <img src="./modules/doc/img/folder<?php if ($docfolder->fields['foldertype'] == 'shared') echo '_shared'; ?><?php if ($docfolder->fields['foldertype'] == 'public') echo '_public'; ?><?php if ($docfolder->fields['readonly']) echo '_locked'; ?>.png" />
            </p>
        </div>
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 8px;">
                <strong><?php echo htmlentities($docfolder->fields['name']); ?></strong>
                <br />Dossier <?php echo $foldertypes[$docfolder->fields['foldertype']]; ?><?php if ($docfolder->fields['readonly']) echo ' en lecture seule'; ?>
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Propriétaire</strong>:
                <br />
                <?php
                include_once './include/classes/user.php';
                $user = new user();
                if ($user->open($docfolder->fields['id_user'])) echo "{$user->fields['lastname']} {$user->fields['firstname']}";
                else echo '<i>supprimé</i>';
                ?>
            </p>
        </div>
        <?php
        /**
         * si dossier perso
         */
        if ($docfolder->fields['foldertype'] == 'private')
        {
            ?>
            <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
                <p style="margin:0;padding:4px 8px;">
                    <strong>Information</strong>
                    <br />Les données de ce dossier sont exclusivement privées
                </p>
            </div>
            <?php
        }
        
        /**
         * si dossier partagés, affichage des partages
         */
        if ($docfolder->fields['foldertype'] == 'shared')
        {
            ?>
            <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
                <p style="margin:0;padding:4px 8px;">
                    <strong>Partages</strong>:
                    <br />
                    <?php
                    $shusers = array(); 
                    foreach(ploopi_share_get(-1, _DOC_OBJECT_FOLDER, $currentfolder) as $value) $shusers[] = $value['id_share'];
    
                    $users = array();
                    if (!empty($shusers))
                    {
                        $sql = "SELECT concat(lastname, ' ', firstname) FROM ploopi_user WHERE id in (".implode(',',$shusers).") ORDER BY lastname, firstname";
                        $db->query($sql);
                        $arrUsers = $db->getarray();
                        if (!empty($arrUsers)) echo implode(', ', $arrUsers);
                        else echo "Aucun partage";
                    }
                    else echo "Aucun partage";
                    ?>
                </p>
            </div>
            <?php
        }
        
        /**
         * Pour les dossiers non privés, affichage des validateurs s'ils existent
         */
        if ($docfolder->fields['foldertype'] != 'private')
        {
            ?>
            <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
                <p style="margin:0;padding:4px 8px;">
                    <strong>Validateurs</strong>:
                    <br />
                    <?php
                    $wfusers = array();
                    foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_validation'];
    
                    $users = array();
                    if (!empty($wfusers))
                    {
                        $sql = "SELECT concat(lastname, ' ', firstname) FROM ploopi_user WHERE id in (".implode(',',$wfusers).") ORDER BY lastname, firstname";
                        $db->query($sql);
    
                        $arrUsers = $db->getarray();
                        if (!empty($arrUsers)) echo implode(', ', $arrUsers);
                        else echo "Aucune accréditation";
                    }
                    else echo "Aucune accréditation";
                    ?>
                </p>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
else
{
    /**
     * Dossier personnel / racine
     */
    ?>
    <div class="doc_folderinfo">
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 0px 4px 8px;">
                <img src="./modules/doc/img/folder_home.png" />
            </p>
        </div>
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Racine</strong>
                <br />Dossier Personnel
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Information</strong>
                <br />Les données de ce dossier sont exclusivement privées
            </p>
        </div>
    </div>
    <?php
}
?>
