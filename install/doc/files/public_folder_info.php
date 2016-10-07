<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @copyright Ovensia
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

if (!empty($currentfolder))
{
    $style = ($objFolder->fields['published']) ? '' : 'style="background-color:#ffe0e0;"';

    ?>
    <div class="doc_folderinfo" <?php echo $style; ?>>
        <div style="float:right;height:40px;">
            <p style="margin:0;padding:4px 8px;">
                <a href="<?php echo ploopi\crypt::urlencode("admin.php?op=doc_folderform&currentfolder={$currentfolder}&addfolder=0"); ?>"><img style="border:0;" src="./modules/doc/img/edit.png" /></a>
            </p>
        </div>
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 0px 4px 8px;">
                <img src="./modules/doc/img/folder<?php if ($objFolder->fields['foldertype'] == 'shared') echo '_shared'; ?><?php if ($objFolder->fields['foldertype'] == 'public') echo '_public'; ?><?php if ($objFolder->fields['readonly']) echo '_locked'; ?>.png" />
            </p>
        </div>
        <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px 8px;">
                <strong><?php echo ploopi\str::htmlentities($objFolder->fields['name']); ?></strong>
                <br />Dossier <?php echo ploopi\str::htmlentities($foldertypes[$objFolder->fields['foldertype']]); ?><?php if ($objFolder->fields['readonly']) echo ' protégé'; ?>
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Propriétaire</strong>:
                <br />
                <?php
                $user = new ploopi\user();
                if ($user->open($objFolder->fields['id_user'])) echo ploopi\str::htmlentities("{$user->fields['lastname']} {$user->fields['firstname']}");
                else echo '<i>supprimé</i>';
                ?>
            </p>
        </div>
        <?php
        /**
         * si dossier perso
         */
        if ($objFolder->fields['foldertype'] == 'private')
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
        if ($objFolder->fields['foldertype'] == 'shared')
        {
            ?>
            <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
                <div style="margin:0;padding:4px 8px;">
                    <div><strong>Partages</strong>:</div>
                    <p class="ploopi_va">
                    <?php
                    $arrShares = array();
                    foreach(ploopi\share::get(-1, _DOC_OBJECT_FOLDER, $currentfolder) as $value) $arrShares[$value['type_share']][] = $value['id_share'];

                    if (!empty($arrShares))
                    {
                        if (!empty($arrShares['group']))
                        {
                            $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                            ploopi\loader::getdb()->query(
                                "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrShares['group']).") ORDER BY label"
                            );

                            while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                        }
                        if (!empty($arrShares['user']))
                        {
                            $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                            ploopi\loader::getdb()->query(
                                "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrShares['user']).") ORDER BY lastname, firstname"
                            );

                            while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                        }
                    }
                    else echo '<span>Aucun partage</span>';
                    ?>
                    </p>
                </div>
            </div>
            <?php
        }

        /**
         * Pour les dossiers non privés, affichage des validateurs s'ils existent
         */
        if ($objFolder->fields['foldertype'] != 'private')
        {
            ?>
            <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
                <div style="margin:0;padding:4px 8px;">
                    <div><strong>Validateurs</strong>:</div>
                    <p class="ploopi_va">
                    <?php
                    $arrValidation = array();
                    foreach(ploopi\validation::get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $arrValidation[$value['type_validation']][] = $value['id_validation'];

                    if (!empty($arrValidation))
                    {
                        if (!empty($arrValidation['group']))
                        {
                            $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                            ploopi\loader::getdb()->query(
                                "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrValidation['group']).") ORDER BY label"
                            );

                            while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                        }
                        if (!empty($arrValidation['user']))
                        {
                            $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                            ploopi\loader::getdb()->query(
                                "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrValidation['user']).") ORDER BY lastname, firstname"
                            );

                            while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                        }
                    }
                    else echo '<span>Aucune accréditation</span>';
                    ?>
                    </p>
                </div>
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
