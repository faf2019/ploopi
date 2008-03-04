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

$newfolder = false;

$docfolder = new docfolder();

doc_getworkflow();

$wfusers = array();
if (!$_GET['addfolder'] && $docfolder->open($currentfolder)) // modifying
{
    foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, $docfolder->fields['id_folder']) as $value) $wfusers[] = $value['id_workflow'];

    $wf_validator = in_array($docfolder->fields['id_folder'], $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']);

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
        <?
    }
    else
    {
        ?>
        <div class="doc_fileform_title">Modification d'un Dossier</div>
        <?
    }
}
else // creating
{
    foreach(ploopi_workflow_get(_DOC_OBJECT_FOLDER, $currentfolder) as $value) $wfusers[] = $value['id_workflow'];

    $wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']);

    $newfolder = true;

    $docfolder->init_description();
    $docfolder->fields['foldertype'] = 'private';
    $readonly = false;
    ?>
    <div class="doc_fileform_title">Nouveau Dossier</div>
    <?
}
?>

<div class="doc_fileform_main">
    <div>
        <?
        if (!$readonly)
        {
            ?>
            <form name="docfolder_form" action="<? echo $scriptenv; ?>"  onsubmit="javascript:return doc_folder_validate(this, <? echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);" method="post" enctype="multipart/form-data" target="doc_folderform_iframe">
            <input type="hidden" name="op" value="doc_foldersave">
            <input type="hidden" name="currentfolder" value="<? echo htmlentities($currentfolder); ?>">
            <?
            if (!$newfolder)
            {
                ?>
                <input type="hidden" name="docfolder_id" value="<? echo htmlentities($currentfolder); ?>">
                <?
            }
        }
        ?>

        <div class="ploopi_form" style="float:left;width:40%;">
            <div style="padding:2px;">
                <p>
                    <label>Nom du Dossier:</label>
                    <?
                    if ($readonly)
                    {
                        ?>
                        <span><? echo htmlentities($docfolder->fields['name']); ?></span>
                        <?
                    }
                    else
                    {
                        ?>
                        <input type="text" class="text" name="docfolder_name" id="docfolder_name" value="<? echo htmlentities($docfolder->fields['name']); ?>" tabindex="1">
                        <?
                    }
                    ?>
                </p>
                <p>
                    <label>Type de Dossier:</label>
                    <?
                    if ($readonly)
                    {
                        ?>
                        <span><? echo htmlentities($foldertypes[$docfolder->fields['foldertype']]); ?></span>
                        <?
                    }
                    else
                    {
                        ?>
                        <select class="select" name="docfolder_foldertype" onchange="javascript:ploopi_getelem('doc_share').style.display = (this.value == 'shared') ? 'block' : 'none'; ploopi_getelem('doc_workflow').style.display = (this.value == 'private') ? 'none' : 'block';" tabindex="2">
                            <?
                            foreach($foldertypes as $key => $value)
                            {
                                ?>
                                <option <? if ($docfolder->fields['foldertype'] == $key) echo 'selected'; ?> value="<? echo $key; ?>"><? echo htmlentities($value); ?></option>
                                <?
                            }
                            ?>
                        </select>
                        <?
                    }
                    ?>
                </p>
                <p>
                    <label>Conteneur en Lecture seule:</label>
                    <?
                    if ($readonly)
                    {
                        ?>
                        <span><? echo ($docfolder->fields['readonly']) ? 'oui' : 'non'; ?></span>
                        <?
                    }
                    else
                    {
                        ?>
                        <input type="checkbox" name="docfolder_readonly" value="1" <? if ($docfolder->fields['readonly']) echo 'checked'; ?> tabindex="3">
                        <?
                    }
                    ?>
                </p>
                <p>
                    <label>Contenu en Lecture seule:</label>
                    <?
                    if ($readonly)
                    {
                        ?>
                        <span><? echo ($docfolder->fields['readonly_content']) ? 'oui' : 'non'; ?></span>
                        <?
                    }
                    else
                    {
                        ?>
                        <input type="checkbox" name="docfolder_readonly_content" value="1" <? if ($docfolder->fields['readonly_content']) echo 'checked'; ?> tabindex="4">
                        <?
                    }
                    ?>
                </p>
            </div>
        </div>
        <div class="ploopi_form" style="float:left;width:58%;">
            <div style="padding:2px;">
                <p>
                    <label>Commentaire:</label>
                    <?
                    if ($readonly)
                    {
                        ?>
                        <span><? echo ploopi_nl2br(htmlentities($docfolder->fields['description'])); ?></span>
                        <?
                    }
                    else
                    {
                        ?>
                        <textarea class="text" name="docfolder_description" tabindex="5"><? echo htmlentities($docfolder->fields['description']); ?></textarea>
                        <?
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <?
    if (!$readonly && ploopi_isactionallowed(_DOC_ACTION_WORKFLOW_MANAGE))
    {
        ?>
        <div id="doc_workflow" style="clear:both;<? echo ($docfolder->fields['foldertype'] == 'private') ? 'display:none;' : 'display:block;'; ?>">
            <? ploopi_workflow_selectusers(_DOC_OBJECT_FOLDER, ($newfolder) ? '' : $docfolder->fields['id']); ?>
        </div>
        <?
    }
    else echo '<div id="doc_workflow" style="clear:both;margin:0;padding:0;visibility:hidden;"></div>';

    if (!$readonly)
    {
        ?>
        <div id="doc_share" style="clear:both;<? echo ($docfolder->fields['foldertype'] == 'shared') ? 'display:block;' : 'display:none;'; ?>">
            <? ploopi_shares_selectusers(_DOC_OBJECT_FOLDER, ($newfolder) ? '' : $docfolder->fields['id']); ?>
        </div>
        <?
    }
    else echo '<div id="doc_share" style="clear:both;margin:0;padding:0;visibility:hidden;"></div>';
    ?>

    <div style="clear:both;float:right;padding:4px;">
        <input type="button" class="flatbutton" value="<? echo _PLOOPI_BACK; ?>" onclick="javascript:doc_explorer(<? echo $currentfolder; ?>);">
        <?
        if (!$readonly)
        {
            ?>
            <input type="button" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>" onclick="javascript:if (doc_folder_validate(document.docfolder_form, <? echo ($newfolder && !empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>)) document.docfolder_form.submit();" tabindex="6">
            <?
        }
        ?>
    </div>

    <?
    if (!$readonly)
    {
        ?>
        </form>
        <?
    }
    ?>
</div>

<?
if (!$readonly)
{
    ?>
    <script type="text/javascript">
    document.docfolder_form.docfolder_name.focus();
    </script>
    <iframe name="doc_folderform_iframe" src="./img/blank.gif" style="width:0;height:0;display:none;"></iframe>
    <?
}

if (!$newfolder) include './modules/doc/public_folder_actions.php';
?>
