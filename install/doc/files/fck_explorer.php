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
    include_once('./modules/doc/class_docfile.php');
    include_once('./modules/doc/class_docfolder.php');
    include_once('./modules/system/class_workspace.php');
    ploopi_init_module('doc');
}

$workspaces = ploopi_viewworkspaces();
$sqllimitworkspace=" AND ploopi_module_workspace.id_workspace IN ($workspaces)";

// get all folders from all available doc modules
$select     =   "
            SELECT  ploopi_module.label,
                    fold.id,
                    fold.name,
                    fold.id_module,
                    fold.parents,
                    fold.foldertype,
                    fold.id_folder

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
                        fold.name
            ";

$db->query($select);

$folders = array();
while ($fields = $db->fetchrow())
{
    $folders[$fields['label']]['list'][$fields['id']] = $fields;
    $folders[$fields['label']]['tree'][$fields['id_folder']][] = $fields['id'];
}


function doc_fckexplorer_displayfolders(&$f, $id_folder = 0, $path = ' ')
{
    if (isset($f['tree'][$id_folder]))
    {
        foreach($f['tree'][$id_folder] as $id_child)
        {
            ?>
            <option value="<? echo $id_child; ?>" label="<? echo $f['list'][$id_child]['name']; ?>"><? echo htmlentities("{$path} / {$f['list'][$id_child]['name']}"); ?></option>
            <?
            doc_fckexplorer_displayfolders($f, $id_child, "{$path} / {$f['list'][$id_child]['name']}");
        }
    }
}

echo $skin->open_simplebloc();
?>

<div style="padding:4px;border-bottom:1px solid #a0a0a0;">
    Dossier :
    <select class="select" name="doc_choosefolder" id="doc_choosefolder" onchange="javascript:doc_fckexplorer_switch_folder(this.value, '<? echo $ploopi_op; ?>');">
    <option value="0"></option>
    <?
    $default_folder = 0;
    foreach($folders as $mlabel => $f)
    {
        ?>
        <optgroup label="<? echo htmlentities($mlabel); ?>"><? echo htmlentities($mlabel); ?></optgroup>
        <?
        if (!$default_folder && isset($f['tree'][0][0])) $default_folder = $f['tree'][0][0];
        doc_fckexplorer_displayfolders($f);
    }
    ?>
    </select>
</div>



<?


// build dir sel for all modules available

/*
$select =   "
        SELECT  distinct doc.id,
                doc.md5id,
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

switch($ploopi_op)
{
    case 'doc_selectimage':
        $filter_ext = array('jpg', 'gif', 'png', 'bmp');
    break;

    case 'doc_selectflash':
        $filter_ext = array('swf');
    break;

    default:
        $filter_ext = array();
    break;
}

$i=0;
while ($fields = $db->fetchrow($rs))
{
    if (empty($filter_ext) || in_array(ploopi_file_getextension($fields['name']),$filter_ext))
    {
        ?>
        lf[<? echo $i; ?>]=new Array(8);
        lf[<? echo $i; ?>][0]= "<? echo $fields['id']; ?>";
        lf[<? echo $i; ?>][1]= "<? echo $fields['name']; ?>";
        lf[<? echo $i; ?>][2]= "<? echo $fields['id_module']; ?>";
        lf[<? echo $i; ?>][3]= "<? echo $fields['id_folder']; ?>";
        lf[<? echo $i; ?>][4]= "<? echo ploopi_urlencode("./index-quick.php?ploopi_op=doc_file_download&docfile_md5id={$fields['md5id']}"); ?>";
        lf[<? echo $i; ?>][5]= "<? echo ploopi_urlencode("./index-quick.php?ploopi_op=doc_image_get&docfile_md5id={$fields['md5id']}&height=75"); ?>";
        lf[<? echo $i; ?>][6]="<? printf("%.2f",round($fields['size']/1024,2)); ?>";

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
            switch ($ploopi_op)
            {
                case 'doc_selectimage':
                ?>
                    sr.innerHTML +=     '<a style="display:block;float:left;margin:0 10px 4px 0;border:1px solid #d0d0d0;background-color:#f0f0f0;font-size:0.8em;text-align:center;" href="#"onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\''+lf[i][4]+'\';opener.UpdatePreview();window.close();">'+
                                                '<div style="padding:2px;background-color:#ffffff;">'+
                                                '<img height="75" src="'+lf[i][5]+'" />'+
                                                '</div>'+
                                                '<div style="padding:2px;font-weight:bold;">'+lf[i][1]+'</div>'+
                                                '<div style="padding:2px;">'+lf[i][6]+' ko</div>'+
                                            '</a>';
                <?
                break;

                case 'doc_selectflash':
                ?>
                    extra = '&nbsp;';

                    sr.innerHTML +=     '<a style="display:block;clear:both;margin:0;border-bottom:1px solid #d0d0d0;background-color:#f0f0f0;font-size:0.8em;overflow:auto;" href="#"onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\''+lf[i][4]+'\';opener.UpdatePreview();window.close();">'+
                                                '<div style="float:left;width:210px;padding:2px;font-weight:bold;text-align:left;background-color:<? echo $skin->values['bgline2']; ?>;">'+lf[i][1]+'</div>'+
                                                '<div style="float:left;width:60px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline1']; ?>;">'+lf[i][6]+' ko</div>'+
                                                '<div style="float:left;width:70px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline2']; ?>;">'+extra+'</div>'+
                                            '</a>';
                <?
                break;

                default:
                    ?>
                    extra = '&nbsp;';

                    sr.innerHTML +=     '<a style="display:block;clear:both;margin:0;border-bottom:1px solid #d0d0d0;background-color:#f0f0f0;font-size:0.8em;overflow:auto;" href="#"onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\''+lf[i][4]+'\';ploopi_getelem(\'cmbLinkProtocol\',opener.document).value=\'\';window.close();">'+
                                                '<div style="float:left;width:210px;padding:2px;font-weight:bold;text-align:left;background-color:<? echo $skin->values['bgline2']; ?>;">'+lf[i][1]+'</div>'+
                                                '<div style="float:left;width:60px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline1']; ?>;">'+lf[i][6]+' ko</div>'+
                                                '<div style="float:left;width:70px;padding:2px;text-align:right;background-color:<? echo $skin->values['bgline2']; ?>;">'+extra+'</div>'+
                                            '</a>';
                    <?
                break;
            }
            ?>
        }
    }
}

</script>
</div>
*
*/
?>

<div id="doc_filebrowser" style="padding:4px;"></div>

<? echo $skin->close_simplebloc(); ?>

<script language="javascript">
doc_fckexplorer_set_folder('<? echo $default_folder; ?>', '<? echo $ploopi_op; ?>');
</script>
