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
 * Affichage du bloc d'abonnement et du bloc d'annotation sur un dossier
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see _DOC_OBJECT_FOLDER
 * @see ploopi_subscription_subscribed
 * @see ploopi_subscription
 * @see ploopi_annotation
 */

/**
 * On affiche d'abord le bloc d'abonnement seulement si le dossier n'est pas privé/personnel
 */
?>
<div style="border-bottom:1px solid #c0c0c0;">
<?php
if ($docfolder->fields['foldertype'] != 'private')
{
    $arrAllowedActions = array( _DOC_ACTION_ADDFOLDER,
                                _DOC_ACTION_ADDFILE,
                                _DOC_ACTION_MODIFYFOLDER,
                                _DOC_ACTION_MODIFYFILE,
                                _DOC_ACTION_DELETEFOLDER,
                                _DOC_ACTION_DELETEFILE
                             );
    
    $parents = explode(',', $docfolder->fields['parents']);
    for ($i = 0; $i < sizeof($parents); $i++)
    {
        if (ploopi_subscription_subscribed(_DOC_OBJECT_FOLDER, $parents[$i]))
        {
            $objDocFolderSub = new docfolder();
            $objDocFolderSub->open($parents[$i])
            ?>
            <div style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">
            Vous héritez de l'abonnement à &laquo; <a href="javascript:void(0);" onclick="javascript:doc_browser('<?php echo $parents[$i]; ?>');"><?php echo $objDocFolderSub->fields['name']; ?></a> &raquo; 
            </div>
            <?php
        }
    }
    ploopi_subscription(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], $arrAllowedActions);
}
?>
</div>
<?php 
/**
 * Affichage du bloc d'annotations
 */
ploopi_annotation(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], $docfolder->fields['name']); ?>