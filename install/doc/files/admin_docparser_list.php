<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
    Copyright (c) 2009 HeXad
    
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
 * Gestion des parsers de documents
 *
 * @package doc
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * D'abord le mini formulaire d'ajout/modif, puis la liste des parsers existants
 */

echo $skin->open_simplebloc(_DOC_TAB_TITLE_PARSERS);

include_once './modules/doc/class_docparser.php';
$docparser = new docparser();

if (isset($_GET['docparser_id'])) $docparser->open($_GET['docparser_id']);
else $docparser->init_description();
?>

<div class="doc_admin_titlebar">
    <p class="ploopi_va" style="float:right;">
        <a href="javascript:void(0);" onclick="javascript:doc_parser_add();"><img style="margin-right:4px;" src="./modules/doc/img/ico_add.gif" /><span>Ajouter un analyseur</span></a>
    </p>
    <div style="font-weight:bold;">Liste des analyseurs</div>
</div>

<div id="docparser_form" class="doc_admin_form" style="display:<?php echo (isset($_GET['docparser_id'])) ? 'block' : 'none'; ?>;">
    <form action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
    <input type="hidden" name="op" value="docparser_save">
    <input type="hidden" name="docparser_id" id="docparser_id" value="<?php echo $docparser->fields['id']; ?>">
    <p class="ploopi_va">
        <label>Libellé:</label>
        <input type="text" class="text" name="docparser_label" id="docparser_label" value="<?php echo ploopi_htmlentities($docparser->fields['label']); ?>" tabindex="1" size="12" />

        <label>Extension:</label>
        <input type="text" class="text" name="docparser_extension" id="docparser_extension" value="<?php echo ploopi_htmlentities($docparser->fields['extension']); ?>" tabindex="2" size="4" />

        <label>Commande:</label>
        <input type="text" class="text" name="docparser_path" id="docparser_path" value="<?php echo ploopi_htmlentities($docparser->fields['path']); ?>" tabindex="3" size="45" />

        <input type="submit" class="button" value="Valider" tabindex="4" />
        <input type="button" class="button" value="Annuler" onclick="javascript:$('docparser_form').style.display = 'hidden';" />
    </p>
    </form>
</div>

<?php
$array_columns = array();
$array_values = array();

$array_columns['left']['label'] = array(    'label' => 'Libellé',
                                            'width' => '200',
                                            'options' => array('sort' => true)
                                            );

$array_columns['left']['ext'] = array(  'label' => 'Ext',
                                        'width' => '70',
                                        'options' => array('sort' => true)
                                        );

$array_columns['auto']['path'] = array( 'label' => 'Commande',
                                        'options' => array('sort' => true)
                                        );

$array_columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '42');

$sql =  "
        SELECT p.*, e.filetype
        FROM ploopi_mod_doc_parser p
        LEFT JOIN   ploopi_mimetype e
        ON          e.ext = p.extension
        ";
$db->query($sql);

$c = 0;
while ($row = $db->fetchrow())
{
    $actions =  "<a title=\"Supprimer\" style=\"display:block;float:right;\" href=\"javascript:void(0);\" onclick=\"javascript:ploopi_confirmlink('admin.php?op=docparser_delete&docparser_id={$row['id']}','Êtes-vous certain de vouloir supprimer cette commande ?');\"><img src=\"./modules/doc/img/ico_trash.png\" /></a>
                <a title=\"Modifier\" style=\"display:block;float:right;\" href=\"admin.php?op=docparser_modify&docparser_id={$row['id']}\"><img src=\"./modules/doc/img/ico_modify.png\" /></a>";

    $ico = (file_exists("./img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

    $array_values[$c]['values']['label'] = array('label' => $row['label'], 'style' => '');
    $array_values[$c]['values']['ext'] = array('label' => "<img src=\"./img/mimetypes/{$ico}\" /><span>&nbsp;{$row['extension']}</span>", 'style' => '');
    $array_values[$c]['values']['path'] = array('label' => $row['path'], 'style' => '');
    $array_values[$c]['values']['actions'] = array('label' => $actions, 'style' => '');
    $array_values[$c]['description'] = $row['label'];
    $array_values[$c]['link'] = "admin.php?op=docparser_modify&docparser_id={$row['id']}";
    $array_values[$c]['style'] = '';

    $c++;
}

$skin->display_array($array_columns, $array_values, 'docparser_list', array('sortable' => true, 'orderby_default' => 'label'));

echo $skin->close_simplebloc();
?>
