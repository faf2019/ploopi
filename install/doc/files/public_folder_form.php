<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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

    $readonly = doc_folder_isreadonly($docfolder->fields, _DOC_ACTION_MODIFYFOLDER);

    if ($readonly)
    {
        ?>
        <div class="doc_fileform_title">Consultation d'un Dossier (modification interdite)</div>
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
    $docfolder->fields['id_folder'] = $currentfolder;
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
                        <span><?php echo ploopi_htmlentities($docfolder->fields['name']); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="text" class="text" name="docfolder_name" id="docfolder_name" value="<?php echo ploopi_htmlentities($docfolder->fields['name']); ?>" tabindex="1">
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>Dossier parent:</label>
                    <?php
                    $strParent = '';
                    $objDocFolderParent = new docfolder();
                    if ($objDocFolderParent->open($docfolder->fields['id_folder'])) $strParent = $objDocFolderParent->fields['name'];
                    elseif ($docfolder->fields['id_folder'] == 0) $strParent = 'Racine';

                    if ($readonly || $addfolder)
                    {
                        ?>
                        <span><a title="Aller au dossier" href="<?php echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$docfolder->fields['id_folder']}"); ?>"><?php echo ploopi_htmlentities($strParent); ?></a></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="hidden" name="docfolder_id_folder" id="docfolder_id_folder" value="<?php echo $docfolder->fields['id_folder']; ?>" />
                        <a title="Choisir un autre dossier parent" href="javascript:void(0);" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_folderselect&doc_excludes=<?php echo $currentfolder; ?>&doc_id_folder='+$('docfolder_id_folder').value, false), 300, event, 'click', 'doc_popup_folderselect');" class="ploopi_va">
                            <span style="width:auto;" id="docfolder_id_folder_name"><?php echo ploopi_htmlentities($strParent); ?></span><img style="margin-left:6px;" src="./modules/doc/img/ico_folder.png" />
                        </a>
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
                        <span><?php echo ploopi_htmlentities($foldertypes[$docfolder->fields['foldertype']]); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <select class="select" name="docfolder_foldertype" onchange="javascript:switch_foldertype(this)" tabindex="3">
                            <?php
                            foreach($foldertypes as $key => $value)
                            {
                                ?>
                                <option <?php if ($docfolder->fields['foldertype'] == $key) echo 'selected'; ?> value="<?php echo $key; ?>"><?php echo ploopi_htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>Contenu protégé:<br /><em>(Les autres utilisateurs ne peuvent pas déposer de fichier)</em></label>
                    <?php
                    if ($readonly)
                    //if ($readonly || ($docfolder->fields['id_user'] != $_SESSION['ploopi']['userid'] && !ploopi_isadmin() && !ploopi_isactionallowed(_DOC_ACTION_ADMIN)))
                    {
                        ?>
                        <span><?php echo ($docfolder->fields['readonly']) ? 'oui' : 'non'; ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <input type="checkbox" name="docfolder_readonly" value="1" <?php if ($docfolder->fields['readonly']) echo 'checked'; ?> tabindex="4">
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
                        <span><?php echo ploopi_nl2br(ploopi_htmlentities($docfolder->fields['description'])); ?></span>
                        <?php
                    }
                    else
                    {
                        ?>
                        <textarea class="text" name="docfolder_description" tabindex="6"><?php echo ploopi_htmlentities($docfolder->fields['description']); ?></textarea>
                        <?php
                    }
                    ?>
                </p>
                <div id="doc_allow_feeds" style="<?php echo ($docfolder->fields['foldertype'] == 'private') ? 'display:none;' : 'display:block;'; ?>">
                    <p>
                        <label>Activer les flux RSS/Atom:</label>
                        <?php
                        if ($readonly)
                        {
                            ?>
                            <span><?php echo ($docfolder->fields['allow_feeds']) ? 'oui' : 'non'; ?></span>
                            <?php
                        }
                        else
                        {
                            ?>
                            <input type="checkbox" name="docfolder_allow_feeds" value="1" <?php if ($docfolder->fields['allow_feeds']) echo 'checked'; ?> onchange="javascript:($('doc_feed_url').style.display == 'none') ? $('doc_feed_url').show() : $('doc_feed_url').hide();" tabindex="7">
                            <?php
                        }
                        ?>
                        <div id="doc_feed_url"  style="<?php echo ($docfolder->fields['allow_feeds']) ? 'display:block;' : 'display:none;'; ?>">
                            <?php
                            if(!empty($docfolder->fields['id']))
                            {
                                ?>
                                <p>
                                    <label>RSS :</label>
                                    <span>
                                    <a title="RSS - <?php echo ploopi_htmlentities($docfolder->fields['name']); ?>" href="<?php  echo ploopi_urlrewrite('./backend.php?format=rss&ploopi_moduleid='.$_SESSION['ploopi']['moduleid'].'&id_folder='.$docfolder->fields['id'], doc_getrewriterules(), $docfolder->fields['name'].'.xml',null,true); ?>" type="application/rss+xml" rel="alternate">
                                        <?php echo _PLOOPI_BASEPATH.ploopi_urlrewrite('/backend.php?format=rss&ploopi_moduleid='.$_SESSION['ploopi']['moduleid'].'&id_folder='.$docfolder->fields['id'], doc_getrewriterules(), $docfolder->fields['name'].'.xml',null,true); ?>
                                    </a>
                                    </span>
                                </p>
                                <p>
                                    <label>Atom :</label>
                                    <span>
                                    <a title="Atom - <?php echo ploopi_htmlentities($docfolder->fields['name']); ?>" href="<?php  echo ploopi_urlrewrite('./backend.php?format=atom&ploopi_moduleid='.$_SESSION['ploopi']['moduleid'].'&id_folder='.$docfolder->fields['id'], doc_getrewriterules(), $docfolder->fields['name'].'.xml',null,true); ?>" type="application/atom+xml" rel="alternate">
                                        <?php echo _PLOOPI_BASEPATH.ploopi_urlrewrite('/backend.php?format=atom&ploopi_moduleid='.$_SESSION['ploopi']['moduleid'].'&id_folder='.$docfolder->fields['id'], doc_getrewriterules(), $docfolder->fields['name'].'.xml',null,true); ?>
                                    </a>
                                    </span>
                                </p>
                                <div style="text-align: center; font-size: 0.8em;">nb: les flux ne sont actifs qu'après enregistrement</div>
                                <?php
                            }
                            else
                            {
                                ?>
                                <div style="text-align: center; font-size: 0.8em;">Les flux ne seront affichés qu'après enregistrement</div>
                                <?php
                            }
                            ?>
                        </div>
                    </p>
                </div>
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
            <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="8">
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

    switch_foldertype = function(select) {
        switch (select.value)
        {
            case 'shared':
                $('doc_share', 'doc_validation').invoke('show');
                $('doc_private', 'doc_allow_feeds').invoke('hide');
                break;
            case 'private':
                $('doc_private').show();
                $('doc_share', 'doc_validation', 'doc_allow_feeds').invoke('hide');
                break;
            case 'public':
                $('doc_validation', 'doc_allow_feeds').invoke('show');
                $('doc_share').hide();
                break;
        }
    }
    </script>
    <?php
}

if (!$newfolder) include './modules/doc/public_folder_actions.php';
?>
