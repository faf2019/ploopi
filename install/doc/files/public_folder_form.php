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
 * Affichage du formulaire de modification d'un dossier
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see doc_getvalidation
 * @see ploopi_validation_get
 * @see _DOC_OBJECT_FOLDER
 */

/**
 * Traitement différent pour ajout et modification de dossier
 */
$newfolder = false;
$docfolder = new docfolder();

/**
 * Chargement du validation
 */
doc_getvalidation();

$wfusers = array();

$addfolder = isset($_GET['addfolder']) ? $_GET['addfolder'] : 0;

if (!$addfolder && $docfolder->open($currentfolder)) // modifying
{
    foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, $docfolder->fields['id_folder']) as $value) $wfusers[] = $value['id_validation'];

    $wf_validator = in_array($docfolder->fields['id_folder'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']);

    // on vérifie que l'utilisateur a bien le droit de modifier ce dossier (en fonction du statut du dossier et du dossier parent)
    $docfolder_readonly_content = false;

    if (!empty($docfolder->fields['id_folder']))
    {
        $docfolder_parent = new docfolder();
        $docfolder_parent->open($docfolder->fields['id_folder']);
        $docfolder_readonly_content = ($docfolder_parent->fields['readonly_content'] && $docfolder_parent->fields['id_user'] != $_SESSION['ploopi']['userid']);
    }

    $readonly = !(ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_MODIFYFOLDER) && ((!$docfolder_readonly_content && !$docfolder->fields['readonly']) || $docfolder->fields['id_user'] == $_SESSION['ploopi']['userid'])));
    //$readonly = (($docfolder->fields['readonly'] && $docfolder->fields['id_user'] != $_SESSION['ploopi']['userid']) || $docfolder_readonly_content || !ploopi_isactionallowed(_DOC_ACTION_MODIFYFOLDER));

    if ($readonly)
    {
        ?>
        <div class="doc_fileform_title">Consultation d'un Dossier (lecture seule)</div>
        <?php
    }
    else
    {
        ?>
        <div class="doc_fileform_title">Modification d'un Dossier</div>
        <?php
    }
}
else // creating
{
    if (!empty($currentfolder)) // si pas le dossier racine, on cherche les validateurs
    {
        foreach(ploopi_validation_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_validation'];
        $wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']);
    }
    else $wf_validator = false;

    $newfolder = true;

    $docfolder->init_description();
    $docfolder->fields['foldertype'] = 'private';
    $readonly = false;
    ?>
    <div class="doc_fileform_title">Nouveau Dossier</div>
    <?php
}
?>
<div class="doc_fileform_main">
    <div>
        <?php
        if (!$readonly)
        {
            ?>
            <form name="docfolder_form" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_foldersave&currentfolder={$currentfolder}".($newfolder ? '' : "&docfolder_id={$currentfolder}")); ?>"  onsubmit="javascript:return doc_folder_validate(this, <?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);" method="post" enctype="multipart/form-data">
            <?php
        }
        ?>

        <div class="ploopi_form" style="float:left;width:40%;">
            <div style="padding:2px;">
                <p>
                    <label>Nom du Dossier:</label>
                    <?php
                    if ($readonly)
                    {
                        ?>
                        <span><?php echo htmlentities($docfolder->fields['name']); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="text" class="text" name="docfolder_name" id="docfolder_name" value="<?php echo htmlentities($docfolder->fields['name']); ?>" tabindex="1">
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>Type de Dossier:</label>
                    <?php
                    if ($readonly)
                    {
                        ?>
                        <span><?php echo htmlentities($foldertypes[$docfolder->fields['foldertype']]); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <select class="select" name="docfolder_foldertype" onchange="javascript:ploopi_getelem('doc_share').style.display = (this.value == 'shared') ? 'block' : 'none'; ploopi_getelem('doc_validation').style.display = (this.value == 'private') ? 'none' : 'block'; ploopi_getelem('doc_private').style.display = (this.value == 'private') ? 'block' : 'none'; " tabindex="2">
                            <?php
                            foreach($foldertypes as $key => $value)
                            {
                                ?>
                                <option <?php if ($docfolder->fields['foldertype'] == $key) echo 'selected'; ?> value="<?php echo $key; ?>"><?php echo htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>Conteneur en Lecture seule:</label>
                    <?php
                    if ($readonly)
                    {
                        ?>
                        <span><?php echo ($docfolder->fields['readonly']) ? 'oui' : 'non'; ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="checkbox" name="docfolder_readonly" value="1" <?php if ($docfolder->fields['readonly']) echo 'checked'; ?> tabindex="3">
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>Contenu en Lecture seule:</label>
                    <?php
                    if ($readonly)
                    {
                        ?>
                        <span><?php echo ($docfolder->fields['readonly_content']) ? 'oui' : 'non'; ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="checkbox" name="docfolder_readonly_content" value="1" <?php if ($docfolder->fields['readonly_content']) echo 'checked'; ?> tabindex="4">
                        <?php
                    }
                    ?>
                </p>
            </div>
        </div>
        <div class="ploopi_form" style="float:left;width:58%;">
            <div style="padding:2px;">
                <p>
                    <label>Commentaire:</label>
                    <?php
                    if ($readonly)
                    {
                        ?>
                        <span><?php echo ploopi_nl2br(htmlentities($docfolder->fields['description'])); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <textarea class="text" name="docfolder_description" tabindex="5"><?php echo htmlentities($docfolder->fields['description']); ?></textarea>
                        <?php
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <?php
    if (!$readonly)
    {
        if (ploopi_isactionallowed(_DOC_ACTION_WORKFLOW_MANAGE))
        {
            ?>
            <div id="doc_validation" style="clear:both;<?php echo ($docfolder->fields['foldertype'] == 'private') ? 'display:none;' : 'display:block;'; ?>">
                <?php ploopi_validation_selectusers(_DOC_OBJECT_FOLDER, ($newfolder) ? '' : $docfolder->fields['id'], -1, -1, null, 'doc_validation_folder'); ?>
            </div>
            <?php
        }
        ?>
        <div id="doc_share" style="clear:both;<?php echo ($docfolder->fields['foldertype'] == 'shared') ? 'display:block;' : 'display:none;'; ?>">
            <?php ploopi_share_selectusers(_DOC_OBJECT_FOLDER, ($newfolder) ? '' : $docfolder->fields['id'], -1, null, 'doc_share_folder'); ?>
        </div>
        <div id="doc_private" style="clear:both;<?php echo ($docfolder->fields['foldertype'] == 'private') ? 'display:block;' : 'display:none;'; ?>">
            <div style="margin:4px;border:1px solid #c0c0c0;padding:4px;background-color:#f8f8f8;">
                <strong>Attention !</strong>
                <br />Seuls les administrateurs &laquo; Système &raquo; ainsi que le propriétaire du dossier pourront accéder à ce dossier.
            </div>
        </div>
        <?php
    }
    ?>
    <div style="clear:both;float:right;padding:4px;">
        <input type="button" class="flatbutton" value="<?php echo _PLOOPI_BACK; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$currentfolder}"); ?>'">
        <?php
        if (!$readonly)
        {
            ?>
            <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="6">
            <?php
        }
        ?>
    </div>

    <?php
    if (!$readonly)
    {
        ?>
        </form>
        <?php
    }
    ?>
</div>

<?php
if (!$readonly)
{
    ?>
    <script type="text/javascript">
    $('docfolder_name').focus();
    </script>
    <?php
}

if (!$newfolder) include './modules/doc/public_folder_actions.php';
?>
