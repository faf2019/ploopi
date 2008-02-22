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
<? echo $skin->open_simplebloc('','100%'); ?>
<div style="padding:4px;" ?>
<?
// Test if a DOC module is present
// -------------------------------
$isdoc = false;
foreach($_SESSION['ploopi']['modules'] as $instance)
{
    if($instance['moduletype'] == 'doc')
    {
        $isdoc = true;
        break;
    }
}

if(!$isdoc) ploopi_die('<b><font color="red">Module DOC absent</font></b>');
else
{
    include_once './modules/doc/class_docfile.php';
    include_once './modules/doc/class_docfolder.php';
    include_once './modules/system/class_workspace.php';
    ploopi_init_module('doc');
}

if (!isset($explorer_type)) $explorer_type = '';

$workspaces = ploopi_viewworkspaces();
$sqllimitworkspace=" AND ploopi_module_workspace.id_workspace IN ($workspaces)";

// get all folders from all available doc modules
$select     =   "
            SELECT  ploopi_module.label,
                    fold.id,
                    fold.name,
                    fold.id_module,
                    fold.parents,
                    fold.foldertype
                    
            FROM    ploopi_mod_doc_folder fold,
                    ploopi_module,
                    ploopi_module_type,
                    ploopi_module_workspace
                    
            WHERE   ploopi_module_type.label = 'doc'
            AND     fold.foldertype = 'public'
            AND     ploopi_module_workspace.id_module = ploopi_module.id
            AND     ploopi_module.id_module_type = ploopi_module_type.id
            AND     fold.id_module = ploopi_module.id
            AND     ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['workspaceid']}
            ORDER BY    ploopi_module.label,
                    fold.parents
            ";
// ajouter :
//          AND     fold.id_workspace = {$_SESSION['ploopi']['workspaceid']}

$db->query($select);
?>
Dossier : 
<select class="select" name="choosefolder" id="choosefolder" onchange="javascript:switch_folder(this.value);">
<option value="0"></option>
<?
$default_folder = 0;
while ($fields = $db->fetchrow())
{
    if (!$default_folder) $default_folder = $fields['id'];
    $gp= new workspace();
    
    switch($fields['foldertype'])
    {
        case _DOCFOLDER_TYPE_MYGROUP:
            $gp->open(substr(strrchr($fields['name'], "_"),1));
            $fields['name'] = _DOC_MYGROUP." ({$gp->fields['label']})";
        break;
        
        case _DOCFOLDER_TYPE_COMMON:
            $fields['name'] = _DOC_COMMON;
        break;
    }
    ?>
    <option value="<? echo $fields['id']; ?>"><? echo "{$fields['label']} > {$fields['name']}"; ?></option>
    <?
}
?>
</select>
<?

// buld dir sel for all modules available
$select =   "
        SELECT  distinct doc.id,
                doc.name,
                doc.size,
                doc.id_folder,
                doc.id_module 
        FROM    ploopi_mod_doc_file doc,
                ploopi_mod_doc_folder fold,
                ploopi_module,
                ploopi_module_type,
                ploopi_module_workspace
                
        WHERE   doc.id_module = ploopi_module.id
        AND     ploopi_module_workspace.id_module = ploopi_module.id
        AND     ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['workspaceid']}
        AND     ploopi_module.id_module_type = ploopi_module_type.id
        AND     ploopi_module_type.label = 'doc'
        AND     doc.id_folder = fold.id
        AND     fold.foldertype = 'public'
                    
        ORDER BY    doc.id_folder,
                doc.id_module,
                doc.name
        ";

$rs = $db->query($select);
?>
<script language="javascript">
var lf = new Array();
<?

$image_ext = array('jpg', 'gif', 'png', 'bmp');
$image_flash=array('swf');

$doc = new docfile();

$i=0;
while ($fields = $db->fetchrow($rs))
{
    $doc->open($fields['id']);
    // image or not image ?
    $is_image = in_array(ploopi_file_getextension($fields['name']),$image_ext);
    // flash ?
    //$is_flash = in_array(ploopi_file_getextension($fields_files['name']),$image_flash);

    //if ( ((!$img || $is_image || $is_flash) && !$flash) || ( ($flash && $is_flash) ))
    if ($is_image || $explorer_type != 'image_galery')
    {
        ?>
        lf[<? echo $i; ?>]=new Array(8);
        lf[<? echo $i; ?>][0]= "<? echo $fields['id']; ?>";
        lf[<? echo $i; ?>][1]= "<? echo $fields['name']; ?>";
        lf[<? echo $i; ?>][2]= "<? echo $fields['id_module']; ?>";
        lf[<? echo $i; ?>][3]= "<? echo $fields['id_folder']; ?>";
        lf[<? echo $i; ?>][4]= "<? echo $doc->getwebpath(); ?>";
        lf[<? echo $i; ?>][5]="<? printf("%.2f",round($fields['size']/1024,2)); ?>";
        
        img = new Image();
        img.src = lf[<? echo $i; ?>][4];
        lf[<? echo $i; ?>][6]= img.width;
        lf[<? echo $i; ?>][7]= img.height;
        <?
        $i++;
    }
}
?>
function switch_folder(idfolder)
{
    sr = ploopi_getelem('showroom');
    sr.innerHTML = '';
    
    for (i=0;i<lf.length;i++)
    {
        if (lf[i][3] == idfolder) 
        {
            <? 
            switch ($explorer_type)
            {
                case 'image_galery':
                ?>
                sr.innerHTML +=     '<a style="display:block;float:left;margin:0 10px 4px 0;border:1px solid #d0d0d0;background-color:#f0f0f0;font-size:0.8em;text-align:center;" href="#"onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\''+lf[i][4]+'\';opener.UpdatePreview();window.close();">'+
                                            '<div style="padding:2px;background-color:#ffffff;">'+
                                            '<img height="75" src="'+lf[i][4]+'" />'+
                                            '</div>'+
                                            '<div style="padding:2px;font-weight:bold;">'+lf[i][1]+'</div>'+
                                            '<div style="padding:2px;">'+lf[i][5]+' ko '+'('+lf[i][6]+' x '+lf[i][7]+')</div>'+
                                        '</a>';
                <?
                break;
                
                default:
                ?>
                extra = '&nbsp;';
                if (lf[i][6]) extra = ' '+lf[i][6]+' x '+lf[i][7];
                // ./index-light.php?op=download_doc&doc_id=36
                sr.innerHTML +=     '<a style="display:block;clear:both;margin:0;border-bottom:1px solid #d0d0d0;background-color:#f0f0f0;font-size:0.8em;overflow:auto;" href="#"onclick="javascript:ploopi_getelem(\'txtFile\',parent.document).value=\'index-light.php?op=download_doc&doc_id='+lf[i][0]+'\';">'+
                                            '<div style="float:left;width:210px;padding:2px;font-weight:bold;text-align:left;background-color:<? echo $skin->values['bgline2']; ?>;">'+lf[i][1]+'</div>'+
                                            '<div style="float:left;width:60px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline1']; ?>;">'+lf[i][5]+' ko</div>'+
                                            '<div style="float:left;width:70px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline2']; ?>;">'+extra+'</div>'+
                                        '</a>';
                <?
                break;
            }
            ?>
        }
    }
}

function set_folder(idfolder)
{
    cf = ploopi_getelem('choosefolder');
    trouve = false;
    i=0;
    while (i<=cf.length && !trouve)
    {
        if (cf.options[i].value == idfolder) {cf.selectedIndex = i; trouve=true;}
        i++;
    }
     switch_folder(idfolder)
}
</script>
</div>

<div id="showroom" style="border-top:1px solid #c0c0c0; padding:4px;overflow:auto;"></div>
<?
echo $skin->close_simplebloc();
?>

<script language="javascript">
set_folder('<? echo $default_folder; ?>');

/*
if (window.attachEvent) // teste l'existence de l'objet
{
    // code compatible IE
    window.attachEvent('onload', set_folder('<? echo $default_folder; ?>'));
}
else
{
    // code compatible Gecko
    window.onload = set_folder('<? echo $default_folder; ?>');
}
*/
</script>
