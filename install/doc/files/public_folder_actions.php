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

?>
<div style="border-bottom:1px solid #c0c0c0;">
<?
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
            <div style="padding:2px 4px;font-weight:bold;">
            Vous héritez de l'abonnement à &laquo; <a href="javascript:void(0);" onclick="javascript:doc_browser('<? echo $parents[$i]; ?>');"><? echo $objDocFolderSub->fields['name']; ?></a> &raquo; 
            </div>
            <?
        }
    }
    ploopi_subscription(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], $arrAllowedActions);
}
?>
</div>
<? ploopi_annotation(_DOC_OBJECT_FOLDER, $docfolder->fields['id'], $docfolder->fields['name']); ?>