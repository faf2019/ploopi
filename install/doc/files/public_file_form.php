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

$newfile = false;

$docfile = new docfile();

if (isset($_GET['docfile_md5id']) && $docfile->openmd5($_GET['docfile_md5id']))
{
    // on vérifie que l'utilisateur a bien le droit de modifier ce fichier (en fonction du statut du dossier parent)
    $readonly = !(ploopi_isadmin() || (ploopi_isactionallowed(_DOC_ACTION_MODIFYFILE) && ((!$docfolder_readonly_content && !$docfile->fields['readonly']) || $docfile->fields['id_user'] == $_SESSION['ploopi']['userid'])));
    $title = ($readonly) ? '(lecture seule)' : ''
    ?>
    <div class="doc_fileform_title">
        <a title="Télécharger ZIP" style="display:block;float:right;margin-left:10px;" href="<? echo ploopi_urlencode("{$scriptenv}?op=doc_filedownloadzip&docfile_md5id={$docfile->fields['md5id']}"); ?>">Télécharger ZIP</a>
        <a title="Télécharger" style="display:block;float:right;margin-left:10px;" href="<? echo ploopi_urlencode("{$scriptenv}?op=doc_filedownload&docfile_md5id={$docfile->fields['md5id']}"); ?>">Télécharger</a>
        <a title="Ouvrir" style="display:block;float:right;margin-left:10px;" href="<? echo ploopi_urlencode("{$scriptenv}?op=doc_fileview&docfile_md5id={$docfile->fields['md5id']}"); ?>" target="_blank">Ouvrir</a>
        <? echo htmlentities($docfile->fields['name'])." {$title}"; ?>
    </div>

    <div class="doc_fileform_main">

        <div class="doc_moreinfo">
            <? echo $skin->open_simplebloc('Historique des versions'); ?>
            <div class="doc_moreinfo_box">
                <?
                $array_columns = array();
                $array_values = array();

                $array_columns['left']['vers'] = array( 'label' => 'Vers',
                                                        'width' => '60',
                                                        'options' => array('sort' => true)
                                                        );

                $array_columns['right']['taille'] = array(  'label' => 'Taille',
                                                            'width' => '80',
                                                            'options' => array('sort' => true)
                                                            );

                $array_columns['right']['par'] = array( 'label' => 'Par',
                                                        'width' => '100',
                                                        'options' => array('sort' => true)
                                                        );

                $array_columns['right']['modif'] = array(   'label' => 'Modifié le',
                                                            'width' => '140',
                                                            'options' => array('sort' => true)
                                                            );

                $array_columns['auto']['fichier'] = array(  'label' => 'Fichier',
                                                            'options' => array('sort' => true)
                                                            );

                $c = 0;

                $history = $docfile->gethistory();

                //ploopi_print_r($history);
                foreach($history as $row)
                {
                    $ldate_modify = (!empty($row['timestp_modify'])) ? ploopi_timestamp2local($row['timestp_modify']) : array('date' => '', 'time' => '');

                    $array_values[$c]['values']['vers']     = array('label' => $row['version'], 'style' => '');
                    $array_values[$c]['values']['taille']   = array('label' => sprintf("%0.2f kio", ($row['size']/1024)), 'style' => '');
                    $array_values[$c]['values']['par']  = array('label' => $row['login'], 'style' => '');
                    $array_values[$c]['values']['modif']    = array('label' => "{$ldate_modify['date']} {$ldate_modify['time']}", 'style' => '');
                    $array_values[$c]['values']['fichier']  = array('label' => $row['name'], 'style' => '');
                    $array_values[$c]['description'] = htmlentities("{$row['name']} ({$row['version']})");
                    $array_values[$c]['link'] = "{$scriptenv}?op=doc_filedownload&docfile_md5id={$row['md5id']}&version={$row['version']}";
                    $array_values[$c]['style'] = '';
                    $c++;
                }

                $skin->display_array($array_columns, $array_values, 'docfile_history', array('height' => 100, 'sortable' => true, 'orderby_default' => 'vers', 'sort_default' => 'DESC'));
                ?>
            </div>
            <? echo $skin->close_simplebloc(); ?>

            <? echo $skin->open_simplebloc('Métadonnées / Propriétés'); ?>
            <div class="doc_moreinfo_box">
            <?
            $sql = "SELECT * FROM ploopi_mod_doc_meta WHERE id_file = {$docfile->fields['id']}";
            $db->query($sql);

            $array_columns = array();
            $array_values = array();

            $array_columns['left']['meta'] = array( 'label' => 'Propriété',
                                                        'width' => '150',
                                                        'options' => array('sort' => true)
                                                        );

            $array_columns['auto']['valeur'] = array(   'label' => 'Valeur',
                                                        'options' => array('sort' => true)
                                                        );

            $c = 0;

            while ($row = $db->fetchrow())
            {
                $array_values[$c]['values']['meta']     = array('label' => $row['meta'], 'style' => '');
                $array_values[$c]['values']['valeur']   = array('label' => $row['value'], 'style' => '');
                $array_values[$c]['description'] = $row['meta'];
                $array_values[$c]['link'] = '';
                $array_values[$c]['style'] = '';
                $c++;
            }

            $skin->display_array($array_columns, $array_values, 'docfile_meta', array('height' => 100, 'sortable' => true));
            ?>
            </div>
            <? echo $skin->close_simplebloc(); ?>

            <? echo $skin->open_simplebloc('Mots clés les plus courants'); ?>
            <div class="doc_moreinfo_box">
            <?
            $array_columns = array();
            $array_values = array();

            $array_columns['right']['ratio'] = array(   'label' => 'Ratio',
                                                        'width' => '60',
                                                        'options' => array('sort' => true)
                                                        );

            $array_columns['right']['weight'] = array(  'label' => 'Poids',
                                                        'width' => '60',
                                                        'options' => array('sort' => true)
                                                        );

            $array_columns['right']['relevance'] = array(   'label' => 'Pertinence',
                                                            'width' => '100',
                                                            'options' => array('sort' => true)
                                                            );

            $array_columns['right']['stem'] = array(    'label' => 'Racine',
                                                        'width' => '100',
                                                        'options' => array('sort' => true)
                                                        );

            $array_columns['auto']['keyword'] = array(  'label' => 'Mot Clé',
                                                        'options' => array('sort' => true)
                                                        );

            $index = ploopi_search_get_index(_DOC_OBJECT_FILE, $docfile->fields['md5id']);

            //ploopi_print_r($index);


            $c = 1;

            foreach ($index as $row)
            {
                $array_values[$c]['values']['relevance']= array('label' => $row['relevance'], 'sort_label' => sprintf("%03d%06d", $row['relevance'], $row['weight']));
                $array_values[$c]['values']['ratio']    = array('label' => sprintf("%0.3f", $row['ratio']));

                if ($row['weight'] == _PLOOPI_INDEXATION_METAWEIGHT) // META
                {
                    $array_values[$c]['values']['weight']   = array('label' => 'meta');
                }
                else
                {
                    $array_values[$c]['values']['weight']   = array('label' => $row['weight']);
                }

                $array_values[$c]['values']['keyword']  = array('label' => $row['keyword']);
                $array_values[$c]['values']['stem']     = array('label' => $row['stem']);
                $array_values[$c]['description'] = "{$c} - {$row['keyword']}";
                $c++;
            }

            $skin->display_array($array_columns, $array_values, 'docfile_words', array('height' => 100, 'sortable' => true, 'orderby_default' => 'relevance', 'sort_default' => 'DESC'));
            ?>
            </div>
            <? echo $skin->close_simplebloc(); ?>
        </div>
    <?
}
else
{
    $newfile = true;
    $docfile->init_description();
    $readonly = false;
    ?>
    <div class="doc_fileform_title">Nouveau Fichier</div>
    <div class="doc_fileform_main">
    <?
}

if (!$readonly)
{
    doc_getworkflow();
    $wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['workflow']['folders']);

    if (_PLOOPI_USE_CGIUPLOAD)
    {
        $sid = doc_guid();
        ?>
        <form method="post" enctype="multipart/form-data" action="<? echo _PLOOPI_CGI_PATH; ?>/upload.cgi?sid=<? echo $sid; ?>" onsubmit="javascript:return doc_file_validate(this,<? echo ($newfile) ? 'true' : 'false'; ?>,<? echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>, '<? echo $sid; ?>', '<? echo _PLOOPI_CGI_PATH; ?>');" target="doc_fileform_iframe">
        <input type="hidden" name="op" value="doc_filesave">
        <input type="hidden" name="currentfolder" value="<? echo $currentfolder; ?>">
        <input type="hidden" name="docfile_md5id" value="<? echo $docfile->fields['md5id']; ?>">
        <input type="hidden" name="redirect" value="../admin.php">
        <?
    }
    else
    {
        ?>
        <form method="post" enctype="multipart/form-data" action="<? echo $scriptenv; ?>"  onsubmit="javascript:return doc_file_validate(this,<? echo ($newfile) ? 'true' : 'false'; ?>,<? echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);" target="doc_fileform_iframe">
        <input type="hidden" name="op" value="doc_filesave">
        <input type="hidden" name="currentfolder" value="<? echo $currentfolder; ?>">
        <input type="hidden" name="docfile_md5id" value="<? echo $docfile->fields['md5id']; ?>">
        <?
    }
}
$max_formsize = doc_max_formsize();
$max_filesize = doc_max_filesize();
?>
<input type="hidden" name="MAX_FILE_SIZE" value="<? echo $max_filesize*1024; ?>">
    <?
    if ($newfile)
    {
        ?>
        <div style="padding:2px;">
        <div style="padding:2px;font-weight:bold;">Fichiers : </div>
        <?
        for ($i=0;$i<5;$i++)
        {
            ?>
            <p class="ploopi_va" style="margin-bottom:2px;">
                <input type="file" class="text" name="docfile_file_<? echo $i; ?>" />&nbsp;<input type="text" style="width:300px;" maxlength="100" class="text" name="docfile_description_<? echo $i; ?>" />
                <input type="checkbox" name="docfile_readonly_<? echo $i; ?>" id="docfile_readonly_<? echo $i; ?>" value="1">
                <span style="cursor:pointer;" onclick="javascript:$('docfile_readonly_<? echo $i; ?>').checked = !$('docfile_readonly_<? echo $i; ?>').checked;">Lecture Seule</span>
            </p>
            <?
        }
        ?>

        <div id="doc_progressbar" style="display:none;"><div id="doc_progressbar_bg"></div></div>
        <div id="doc_progressbar_txt"></div>

        <div>Taille maxi autorisée par fichier : <b><? echo ($max_filesize) ? "{$max_filesize} ko" : 'pas de limite'; ?></b></div>
        <div>Taille maxi autorisée par envoi : <b><? echo ($max_formsize) ? "{$max_formsize} ko" : 'pas de limite'; ?></b></div>
        <?
    }
    else
    {
        include_once './modules/system/class_user.php';

        $user = new user();
        $user_modify = new user();
        
        if ($user->open($docfile->fields['id_user'])) $user_login = $user->fields['login'];
        else $user_login = "<i>supprimé</i>";

        if ($user_modify->open($docfile->fields['id_user_modify'])) $user_modify_login = $user->fields['login'];
        else $user_modify_login = "<i>supprimé</i>";
        
        $ldate_modify = (!empty($docfile->fields['timestp_modify'])) ? ploopi_timestamp2local($docfile->fields['timestp_modify']) : array('date' => '', 'time' => '');
        //echo $user->fields['login'];
        ?>
        <div class="ploopi_form" style="padding:2px;">
        <p>
            <label>Nom du Fichier:</label>
            <?
            if ($readonly) echo htmlentities($docfile->fields['name']);
            else
            {
                ?>
                <input type="text" class="text" name="docfile_name" value="<? echo htmlentities($docfile->fields['name']); ?>">
                <?
            }
            ?>
        </p>
        <p>
            <label>Version:</label>
            <span><? echo $docfile->fields['version']; ?></span>
        </p>
        <p>
            <label>Taille:</label>
            <span><? printf("%0.2f kio", ($docfile->fields['size']/1024)); ?></span>
        </p>
        <p>
            <label>Propriétaire:</label>
            <span><? echo $user_login; ?></span>
        </p>
        <p>
            <label>Modifié par:</label>
            <span><? echo $user_modify_login; ?></span>
        </p>
        <p>
            <label>Dernière modification:</label>
            <span><? echo "{$ldate_modify['date']} {$ldate_modify['time']}"; ?></span>
        </p>
        <p>
            <label>Lecture Seule:</label>
            <?
            if ($readonly) echo ($docfile->fields['readonly']) ? 'oui' : 'non';
            else
            {
                ?>
                <input type="checkbox" name="docfile_readonly" value="1" <? if ($docfile->fields['readonly']) echo 'checked'; ?>>
                <?
            }
            ?>
        </p>
        <p>
            <label>Commentaire:</label>
            <?
            if ($readonly) echo ploopi_nl2br(htmlentities($docfile->fields['description']));
            else
            {
                ?>
                <textarea class="text" name="docfile_description"><? echo htmlentities($docfile->fields['description']); ?></textarea>
                <?
            }
            ?>
        </p>
        <?
        if (!$readonly)
        {
            ?>
            <p>
                <label>Déposer une nouvelle Version:</label>
                <input type="file" class="text" name="docfile_file">
            </p>
            <div id="doc_progressbar" style="display:none;"><div id="doc_progressbar_bg"></div></div>
            <div id="doc_progressbar_txt"></div>
            <?
        }
    }
    ?>
    <div style="padding:4px;text-align:right;">
        <input type="button" class="flatbutton" value="<? echo _PLOOPI_BACK; ?>" onclick="javascript:doc_explorer(<? echo $currentfolder; ?>);">
        <?
        if (!$readonly)
        {
            ?>
            <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
            <?
            if (!$newfile)
            {
                ?>
                <input type="button" class="flatbutton" value="Ré-indéxer" onclick="javascript:doc_fileindex(<? echo $currentfolder; ?>, '<? echo $_GET['docfile_md5id']; ?>');">
                <?
            }
        }
        ?>
    </div>
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
    <iframe name="doc_fileform_iframe" src="./img/blank.gif" style="display:none;"></iframe>
    <?
}


if (!$newfile)
{
    ?>                             
    <div style="border-bottom:1px solid #c0c0c0;">
    <?
    if ($docfolder->fields['foldertype'] != 'private')
    {
        $arrAllowedActions = array( _DOC_ACTION_MODIFYFILE,
                                    _DOC_ACTION_DELETEFILE
                                 );
                                 
        $parents = explode(',', "{$docfolder->fields['parents']},{$docfolder->fields['id']}");
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
        ploopi_subscription(_DOC_OBJECT_FILE, $docfile->fields['md5id'], $arrAllowedActions);
    }
    ?>
    </div>
    <?
    ploopi_annotation(_DOC_OBJECT_FILE, $docfile->fields['md5id'], $docfile->fields['name']);
}
?>
