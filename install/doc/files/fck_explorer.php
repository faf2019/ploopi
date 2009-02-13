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
 * Explorateur de documents intégré à FCKeditor
 * 
 * @package doc
 * @subpackage fckeditor
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * On commence par tester si une instance du module DOC est présente.
 * Car le module peut être installé mais pas instancié !
 */

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
    /**
     * Inclusion des classes nécessaires et initialisation du module
     */
    include_once './modules/doc/class_docfile.php';
    include_once './modules/doc/class_docfolder.php';
    include_once './include/classes/workspace.php';
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
            <option value="<?php echo $id_child; ?>" label="<?php echo $f['list'][$id_child]['name']; ?>"><?php echo htmlentities("{$path} / {$f['list'][$id_child]['name']}"); ?></option>
            <?php
            doc_fckexplorer_displayfolders($f, $id_child, "{$path} / {$f['list'][$id_child]['name']}");
        }
    }
}

echo $skin->open_simplebloc();
?>

<div style="padding:4px;border-bottom:1px solid #a0a0a0;">
    Dossier :
    <select class="select" name="doc_choosefolder" id="doc_choosefolder" onchange="javascript:doc_fckexplorer_switch_folder(this.value, '<?php echo $ploopi_op; ?>');">
    <option value="0"></option>
    <?php
    $default_folder = 0;
    foreach($folders as $mlabel => $f)
    {
        ?>
        <optgroup label="<?php echo htmlentities($mlabel); ?>"><?php echo htmlentities($mlabel); ?></optgroup>
        <?php
        if (!$default_folder && isset($f['tree'][0][0])) $default_folder = $f['tree'][0][0];
        doc_fckexplorer_displayfolders($f);
    }
    ?>
    </select>
</div>

<div id="doc_filebrowser" style="padding:4px;"></div>

<?php echo $skin->close_simplebloc(); ?>

<script language="javascript">
doc_fckexplorer_set_folder('<?php echo $default_folder; ?>', '<?php echo $ploopi_op; ?>');
</script>
