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
 */

include_once './include/functions/system.php';
$arrModules = ploopi_getmoduleid('doc', false);
if (empty($arrModules))
{
    ploopi_die('<div class="error">Module DOC absent</div>');
}

/**
 * Initialisation du module DOC
 */
ploopi_init_module('doc');

$db->query("
    SELECT  m.label,
            fold.id,
            fold.name,
            fold.id_module,
            fold.parents,
            fold.foldertype,
            fold.id_folder

    FROM    ploopi_mod_doc_folder fold,
            ploopi_module m

    WHERE   fold.foldertype = 'public'
    AND     fold.id_module IN (".implode(',', $arrModules).")
    AND     fold.id_module = m.id

    ORDER BY    m.label,
                fold.name
");

$arrFolders = array();
while ($fields = $db->fetchrow())
{
    $arrFolders[$fields['label']]['list'][$fields['id']] = $fields;
    $arrFolders[$fields['label']]['tree'][$fields['id_folder']][] = $fields['id'];
}

echo $skin->open_simplebloc();
?>

<div style="padding:4px;border-bottom:1px solid #a0a0a0;">
    Dossier :
    <select class="select" name="doc_choosefolder" id="doc_choosefolder" onchange="javascript:doc_fckexplorer_switch_folder(this.value, '<?php echo $ploopi_op; ?>');">
    <option value="0"></option>
    <?php
    $intDefaultFolder = 0;
    foreach($arrFolders as $strModuleLabel => $arrSubFolders)
    {
        ?>
        <optgroup label="<?php echo htmlentities($strModuleLabel); ?>"><?php echo htmlentities($strModuleLabel); ?></optgroup>
        <?php
        if (!$intDefaultFolder && isset($arrSubFolders['tree'][0][0])) $intDefaultFolder = $arrSubFolders['tree'][0][0];
        doc_fckexplorer_displayfolders($arrSubFolders);
    }
    ?>
    </select>
</div>

<div id="doc_filebrowser" style="padding:4px;"></div>

<?php echo $skin->close_simplebloc(); ?>

<script language="javascript">
doc_fckexplorer_set_folder('<?php echo $intDefaultFolder; ?>', '<?php echo $ploopi_op; ?>');
</script>
