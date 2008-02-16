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
<div class="doc_folderinfo">
<? 
if (!empty($currentfolder)) 
{
        
    //if (ploopi_isactionallowed(_DOC_ACTION_MODIFYFOLDER) && (!$docfolder->fields['readonly'] || $_SESSION['ploopi']['userid'] == $docfolder->fields['id_user']))
    //{
        ?>
        <div style="float:right;height:40px;">
            <p style="margin:0;padding:4px 8px;">
                <a href="javascript:void(0);" onclick="javascript:doc_folderform(<? echo $currentfolder; ?>);"><img style="border:0;" src="./modules/doc/img/edit.png" /></a>
            </p>
        </div>
        <?
    //}
    ?>
    <div style="float:left;height:40px;">
        <p style="margin:0;padding:4px 0px 4px 8px;">
            <img src="./modules/doc/img/folder<? if ($docfolder->fields['foldertype'] == 'shared') echo '_shared'; ?><? if ($docfolder->fields['foldertype'] == 'public') echo '_public'; ?><? if ($docfolder->fields['readonly']) echo '_locked'; ?>.png" />
        </p>
    </div>
    <div style="float:left;height:40px;">
        <p style="margin:0;padding:4px 8px;">
            <strong><? echo $docfolder->fields['name']; ?></strong>
            <br />Dossier <? echo $foldertypes[$docfolder->fields['foldertype']]; ?><? if ($docfolder->fields['readonly']) echo ' en lecture seule'; ?>
        </p>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
        <p style="margin:0;padding:4px 8px;">
            <strong>Propriétaire</strong>:
            <br />
            <?
            include_once './modules/system/class_user.php';
            $user = new user();
            $user->open($docfolder->fields['id_user']);
            echo $user->fields['login'];
            ?>
        </p>
    </div>
    <?
    if ($docfolder->fields['foldertype'] == 'shared')
    {
        ?>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Partages</strong>:
                <br />
                <?
                $shusers = array(); 
                foreach(ploopi_shares_get(-1, _DOC_OBJECT_FOLDER, $currentfolder) as $value) $shusers[] = $value['id_share'];

                $users = array();
                if (!empty($shusers))
                {
                    $sql = "SELECT id,login,lastname,firstname FROM ploopi_user WHERE id in (".implode(',',$shusers).") ORDER BY lastname, firstname";
                    $db->query($sql);
                    while ($row = $db->fetchrow()) $users[$row['id']] = $row;
                    
                    if (sizeof($users))
                    {
                        $c=1;
                        foreach($users as $user)
                        {
                            echo "{$user['login']}";
                            if ($c++<sizeof($users)) echo ', ';
                        }
                    }
                    else echo "Aucun partage";
                }
                else echo "Aucun partage";
                ?>
            </p>
        </div>
        <?
    }
    
    if ($docfolder->fields['foldertype'] != 'private')
    {
        ?>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px 8px;">
                <strong>Validateurs</strong>:
                <br />
                <?
                $wfusers = array();
                foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_workflow'];

                $users = array();
                if (!empty($wfusers))
                {
                    $sql = "SELECT id,login,lastname,firstname FROM ploopi_user WHERE id in (".implode(',',$wfusers).") ORDER BY lastname, firstname";
                    $db->query($sql);
                    while ($row = $db->fetchrow()) $users[$row['id']] = $row;
                    
                    if (!empty($users))
                    {
                        $c=1;
                        foreach($users as $user)
                        {
                            echo "{$user['login']}";
                            if ($c++<sizeof($users)) echo ', ';
                        }
                    }
                    else echo "Aucune accréditation";
                }
                else echo "Aucune accréditation";
                ?>
            </p>
        </div>
        <?
    }
}
else
{
    ?>
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
    <?
}
?>
</div>
