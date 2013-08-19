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
 * Affichage du formulaire de modification d'un fichier
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see _PLOOPI_USE_CGIUPLOAD
 * @see _DOC_OBJECT_FILE
 * @see _PLOOPI_PATHSHARED
 *
 * @see doc_getvalidation
 * @see ploopi_subscription
 * @see ploopi_annotation
 */

/**
 * On traite d'abord le cas d'un nouveau fichier (ou plusieurs nouveaux fichiers...), puis la modification d'un fichier
 */

$newfile = true;
$docfile = new docfile();

$max_formsize = doc_max_formsize();
$max_filesize = doc_max_filesize();

$newfile = !(isset($_GET['docfile_md5id']) && $docfile->openmd5($_GET['docfile_md5id']));

$booServerModeAvailable = (_PLOOPI_PATHSHARED != '' && file_exists(_PLOOPI_PATHSHARED) && is_readable(_PLOOPI_PATHSHARED));

/**
 * Nouveaux fichiers à déposer
 */

if ($newfile)
{
    /**
     * Initialisation de l'objet docfile
     */

    $docfile->init_description();
    ?>
    <div class="doc_fileform_title">Nouveau Fichier</div>
    <div class="doc_fileform_main">
        <?php
        if ($booServerModeAvailable)
        {
            ?>
            <div style="padding:4px;">
                <div>Les fichiers sont situés : </div>
                <p class="ploopi_checkbox" style="padding:2px 0;" onclick="javascript:ploopi_checkbox_click(event, '_docfile_location_host');">
                    <input type="radio" name="_docfile_location" id="_docfile_location_host" value="host" checked="checked" onchange="javascript:$('doc_form_host').style.display = 'block'; $('doc_form_server').style.display = 'none';" />
                    <span>sur mon poste</span>
                </p>
                <p class="ploopi_checkbox" style="padding:2px 0;" onclick="javascript:ploopi_checkbox_click(event, '_docfile_location_server');">
                    <input type="radio" name="_docfile_location" id="_docfile_location_server" value="server" onchange="javascript:$('doc_form_host').style.display = 'none'; $('doc_form_server').style.display = 'block';" />
                    <span>sur le serveur</span>
                </p>
            </div>
            <?php
        }
        ?>
        <div id="doc_form_host" style="display:block;">
            <?php
            /**
             * Chargement du validation
             */

            doc_getvalidation();
            $wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']);

            if (_PLOOPI_USE_CGIUPLOAD)
            {
                $sid = doc_guid();
                ?>
                <form method="post" enctype="multipart/form-data" action="<?php echo _PLOOPI_CGI_PATH; ?>/upload.cgi?sid=<?php echo $sid; ?>" onsubmit="javascript:return doc_file_validate(this,<?php echo ($newfile) ? 'true' : 'false'; ?>,<?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>, '<?php echo $sid; ?>', '<?php echo _PLOOPI_CGI_PATH; ?>');">
                <input type="hidden" name="redirect" value="../<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_filesave&currentfolder={$currentfolder}&doc_mode=host"); ?>">
                <?php
            }
            else
            {
                ?>
                <form method="post" enctype="multipart/form-data" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_filesave&currentfolder={$currentfolder}"); ?>"  onsubmit="javascript:return doc_file_validate(this,<?php echo ($newfile) ? 'true' : 'false'; ?>,<?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);">
                <?php
            }
            ?>
            <input type="hidden" name="doc_mode" id="doc_mode" value="host">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_filesize*1024; ?>">
            <div style="padding:2px;">
                <div style="padding:2px;font-weight:bold;">Fichiers : </div>
                <?php
                for ($i=0;$i<5;$i++)
                {
                    ?>
                    <p class="ploopi_va" style="margin-bottom:2px;">
                        <input type="file" name="docfile_file_<?php echo $i; ?>" />&nbsp;<input type="text" style="width:250px;" maxlength="100" name="docfile_description_<?php echo $i; ?>" placeholder="Commentaire" />
                        <span class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, 'docfile_readonly_<?php echo $i; ?>_host');">
                            <input type="checkbox" name="docfile_readonly_<?php echo $i; ?>" id="docfile_readonly_<?php echo $i; ?>_host" value="1">
                            <span>Contenu protégé</span>
                        </span>
                        <span class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, 'docfile_decompress_<?php echo $i; ?>_host');">
                            <input type="checkbox" name="docfile_decompress_<?php echo $i; ?>" id="docfile_decompress_<?php echo $i; ?>_host" value="1">
                            <span>A décompresser (zip uniquement)</span>
                        </span>
                    </p>
                    <?php
                }
                ?>

                <div id="doc_progressbar" style="display:none;"><div id="doc_progressbar_bg"></div></div>
                <div id="doc_progressbar_txt"></div>

                <div>Taille maxi autorisée par fichier : <b><?php echo ($max_filesize) ? "{$max_filesize} ko" : 'pas de limite'; ?></b></div>
                <div>Taille maxi autorisée par envoi : <b><?php echo ($max_formsize) ? "{$max_formsize} ko" : 'pas de limite'; ?></b></div>

                <div style="padding:4px;text-align:right;">
                    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_BACK; ?>" onclick="javascript:doc_explorer(<?php echo $currentfolder; ?>);">
                    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
                </div>
            </div>
            </form>
        </div>

        <?php
        if ($booServerModeAvailable)
        {
            ?>
            <div id="doc_form_server" style="display:none;">
                <form method="post" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_filesave&currentfolder={$currentfolder}&doc_mode=server"); ?>"  onsubmit="javascript:return doc_file_validate(this,<?php echo ($newfile) ? 'true' : 'false'; ?>,<?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);">
                <div style="padding:2px;">
                    <div style="padding:2px;font-weight:bold;">Fichiers : </div>
                    <?php
                    for ($i=0;$i<5;$i++)
                    {
                        ?>
                        <p class="ploopi_va" style="margin-bottom:2px;">
                            <input type="text" class="text" name="docfile_file_<?php echo $i; ?>" id="docfile_file_server_<?php echo $i; ?>" value="" style="width:160px;cursor:pointer;" onclick="javascript:ploopi_filexplorer_popup('<?php echo ploopi_filexplorer_init(_PLOOPI_PATHSHARED, "docfile_file_server_{$i}", "docfile_explorer_{$i}"); ?>', event);" readonly="readonly" />
                            <input type="button" class="button" value="Parcourir" style="width:90px;" onclick="javascript:ploopi_filexplorer_popup('<?php echo ploopi_filexplorer_init(_PLOOPI_PATHSHARED, "docfile_file_server_{$i}", "docfile_explorer_{$i}"); ?>', event);" />&nbsp;<input type="text" style="width:250px;" maxlength="100" class="text" name="docfile_description_<?php echo $i; ?>" />
                            <span class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, 'docfile_readonly_<?php echo $i; ?>_server');">
                                <input type="checkbox" name="docfile_readonly_<?php echo $i; ?>" id="docfile_readonly_<?php echo $i; ?>_server" value="1">
                                <span>Contenu protégé</span>
                            </span>
                            <span class="ploopi_checkbox" onclick="javascript:ploopi_checkbox_click(event, 'docfile_decompress_<?php echo $i; ?>_server');">
                                <input type="checkbox" name="docfile_decompress_<?php echo $i; ?>" id="docfile_decompress_<?php echo $i; ?>_server" value="1">
                                <span>A décompresser</span>
                            </span>
                        </p>
                        <?php
                    }
                    ?>
                    <div style="padding:4px;text-align:right;">
                        <input type="button" class="flatbutton" value="<?php echo _PLOOPI_BACK; ?>" onclick="javascript:doc_explorer(<?php echo $currentfolder; ?>);">
                        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
                    </div>
                </div>
                </form>
            </div>
            <?php
        }
        ?>
    </div>
    <?php

}
else
{
    /**
     * on vérifie que l'utilisateur a bien le droit de modifier ce fichier (en fonction du statut du dossier parent)
     */

    $readonly = doc_file_isreadonly($docfile->fields, _DOC_ACTION_MODIFYFILE);

    $title = $readonly ? '(Contenu protégé)' : '';

    $docfile_tab = empty($_GET['docfile_tab']) ? 'open' : $_GET['docfile_tab'];

    $db->query("SELECT filetype FROM ploopi_mimetype WHERE ext = '{$docfile->fields['extension']}'");
    $row = $db->fetchrow();

    $ico = (!empty($row['filetype']) && file_exists("./img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';
    ?>

    <div class="doc_fileinfo">
        <a href="javascript:void(0);" onclick="javascript:ploopi_tickets_new(event, '<?php echo _DOC_OBJECT_FILE ?>','<?php echo $docfile->fields['md5id']; ?>', '<?php echo $docfile->fields['name']; ?>');" title="Envoyer en Pièce Jointe">
            <p class="ploopi_va">
                <img src="./modules/doc/img/send.png" />
                <span>Envoyer</span>
            </p>
        </a>
        <a href="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownloadzip&docfile_md5id={$docfile->fields['md5id']}"); ?>" title="Télécharger Zip">
            <p class="ploopi_va">
                <img src="./modules/doc/img/downloadzip.png" />
                <span>Télécharger Zip</span>
            </p>
        </a>
        <a href="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=doc_filedownload&docfile_md5id={$docfile->fields['md5id']}"); ?>" title="Télécharger">
            <p class="ploopi_va">
                <img src="./modules/doc/img/download.png" />
                <span>Télécharger</span>
            </p>
        </a>

        <div>
            <p class="ploopi_va" style="white-space:nowrap;overflow:hidden;">
                <img src="./img/mimetypes/<?php echo $ico; ?>" />
                <strong><?php echo ploopi_htmlentities("{$docfile->fields['name']} {$title}"); ?></strong>
            </p>
        </div>
    </div>

    <?php

    if (!empty($currentfolder) && $objFolder->fields['foldertype'] == 'public')
    {
        $strPublicUrl = _PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$docfile->fields['md5id']}", doc_getrewriterules(), $docfile->fields['name'], null, true);
        ?>
        <p class="ploopi_va" style="padding:4px;border-bottom:1px solid #aaa;background-color:#ddd;">
            <strong>URL publique du fichier :</strong>
            <a title="URL publique permettant de télécharger ce fichier" href="<?php echo $strPublicUrl; ?>"><?php echo $strPublicUrl; ?></a>
        </p>
        <?
    }
    ?>

    <div class="ploopi_tabs" style="margin-top:1px;">
        <a <?php if ($docfile_tab == 'history') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=history"); ?>"><img src="./modules/doc/img/ico_history.png"><span>Anciennes versions</span></a>
        <a <?php if ($docfile_tab == 'keywords') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=keywords"); ?>" title="Mots clés"><img src="./modules/doc/img/ico_keywords.png"><span>Mots clés</span></a>
        <a <?php if ($docfile_tab == 'meta') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=meta"); ?>" title="Métadonnées / Propriétés"><img src="./modules/doc/img/ico_meta.png"><span>Métadonnées</span></a>
        <a <?php if ($docfile_tab == 'modify') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=modify"); ?>" title="Modifier le fichier"><img src="./modules/doc/img/ico_main.png"><span>Modifier</span></a>
        <?
        if (ploopi_getsessionvar('unoconv') === true || ploopi_getsessionvar('jodconv') === true)
        {
            $arrRenderer = doc_getrenderer($docfile->fields['extension']);
            if (isset($arrRenderer[1]) && $arrRenderer[1] == 'unoconv')
            {
                ?>
                <a <?php if ($docfile_tab == 'pdf') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=pdf"); ?>" title="Voir le contenu"><img src="./modules/doc/img/pdf.png"><span>Voir en PDF</span></a>
                <?
            }
        }
        ?>
        <a <?php if ($docfile_tab == 'open') echo 'style="font-weight:bold;"'; ?> href="<?php echo ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}&docfile_tab=open"); ?>" title="Voir le contenu"><img src="./modules/doc/img/ico_open.png"><span>Voir</span></a>

    </div>

    <?php
    if (file_exists($docfile->getfilepath())) {
        switch($docfile_tab)
        {
            /**
             * Affichage du contenu du fichier (texte, multimédia)
             */
            case 'pdf':
            case 'open':
                $arrRenderer = doc_getrenderer($docfile->fields['extension']);

                if (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight'])) $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight'] = '600';

                switch($arrRenderer[0])
                {
                    case 'highlighter':
                        ?>
                        <div style="border:1px solid #c0c0c0;margin:4px;padding:4px;background-color:#ffffff;height:<?php echo $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight']; ?>px;overflow:auto;">
                        <?php
                        require_once "Text/Highlighter.php";
                        require_once "Text/Highlighter/Renderer/Html.php";
                        $objHL = Text_Highlighter::factory($arrRenderer[1]);

                        $objHL->setRenderer(new Text_Highlighter_Renderer_Html());

                        $ptrHandle = fopen($docfile->getfilepath(), "rb");
                        $strFileContent = '';
                        while (!feof($ptrHandle)) $strFileContent .= fread($ptrHandle, 8192);
                        fclose($ptrHandle);

                        $strLines = implode(range(1, count(explode("\n", $strFileContent))), '<br />');
                        echo "<div class=\"doc_hl-content\"><table><tr><td class=\"doc_hl-num\">\n$strLines\n</td><td class=\"doc_hl-src\">\n".$objHL->highlight($strFileContent)."\n</td></tr></table></div>"
                        ?>
                        </div>
                        <?php
                    break;

                    case 'flash':
                        ?>
                        <script type="text/javascript" src="./lib/swfobject/swfobject.js"></script>
                        <div id="doc_flash_player">Player</div>
                        <script type="text/javascript">
                        var so = new SWFObject('<?php echo ploopi_urlencode("admin-light.php?ploopi_op=doc_fileview&docfile_md5id={$docfile->fields['md5id']}"); ?>','mpl','100%','<? echo $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight']; ?>','9');
                        so.write('doc_flash_player');
                        </script>
                        <?
                    break;

                    case 'jw_player':
                        ?>
                        <script type="text/javascript" src="./lib/swfobject/swfobject.js"></script>
                        <div id="doc_jw_player">Player</div>
                        <script type="text/javascript">
                        var so = new SWFObject('./lib/jw_player/player.swf','mpl','100%','<?php echo $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight']; ?>','9');
                        so.addParam('allowscriptaccess','always');
                        so.addParam('allowfullscreen','true');
                        so.addParam('flashvars','file=<?php echo _PLOOPI_BASEPATH.'/'.ploopi_urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$docfile->fields['md5id']}", doc_getrewriterules(), $docfile->fields['name'], null, true); ?>');
                        so.write('doc_jw_player');
                        </script>
                        <?php
                    break;

                    case 'div':
                        ?>
                        <div style="border:1px solid #c0c0c0;margin:4px;padding:4px;background-color:#ffffff;height:<?php echo $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight']; ?>px;overflow:auto;">
                        <?
                        $strFileContent = file_get_contents($docfile->getfilepath());
                        $strLines = implode(range(1, count(explode("\n", $strFileContent))), '<br />');
                        echo "<div class=\"doc_hl-content\"><table><tr><td class=\"doc_hl-num\">\n$strLines\n</td><td class=\"doc_hl-src\">\n<pre>{$strFileContent}\n</pre></td></tr></table></div>"
                        ?>
                        </div>
                        <?
                    break;

                    case 'video':
                        ?>
                         <video id='v1' src="<?php echo ploopi_urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$docfile->fields['md5id']}", doc_getrewriterules(), $docfile->fields['name'], null, true); ?>" controls="true"><div style="padding:10px;">Votre navigateur ne supporte pas la balise "video".<br /><a href="<? echo ploopi_urlrewrite("index.php?ploopi_op=doc_file_download&docfile_md5id={$docfile->fields['md5id']}", doc_getrewriterules(), $docfile->fields['name'], null, true); ?>">Cliquez sur ce lien pour télécharger le document</a></div></video>
                        <?
                    break;

                    default:
                    case 'iframe':
                        ?>
                        <div style="border:1px solid #c0c0c0;margin:4px;background-color:#f0f0f0;"><iframe src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=doc_fileview&docfile_md5id={$docfile->fields['md5id']}".($docfile_tab == 'pdf' && isset($arrRenderer[1]) && $arrRenderer[1] == 'unoconv' ? '&doc_viewmode=pdf' : '')); ?>" style="border:0;width:100%;margin:0;padding:0;height:<?php echo $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_viewerheight']; ?>px;"></iframe></div>
                        <?
                    break;
                }
            break;

            case 'keywords':
                $array_columns = array();
                $array_values = array();

                $array_columns['right']['ratio'] =
                    array(
                        'label' => 'Ratio',
                        'width' => '60',
                        'options' => array('sort' => true)
                    );

                $array_columns['right']['weight'] =
                    array(
                        'label' => 'Poids',
                        'width' => '60',
                        'options' => array('sort' => true)
                    );

                $array_columns['right']['relevance'] =
                    array(
                        'label' => 'Pertinence',
                        'width' => '100',
                        'options' => array('sort' => true)
                    );

                $array_columns['auto']['keyword'] =
                    array(
                        'label' => 'Mot Clé',
                        'options' => array('sort' => true)
                    );

                $index = ploopi_search_get_index(_DOC_OBJECT_FILE, $docfile->fields['md5id']);

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
                    $array_values[$c]['description'] = "{$c} - {$row['keyword']}";
                    $c++;
                }

                $skin->display_array($array_columns, $array_values, 'docfile_words', array('sortable' => true, 'orderby_default' => 'relevance', 'sort_default' => 'DESC'));
                break;

            case 'meta':
                $sql = "SELECT * FROM ploopi_mod_doc_meta WHERE id_file = {$docfile->fields['id']}";
                $db->query($sql);

                $array_columns = array();
                $array_values = array();

                $array_columns['left']['meta'] =
                    array(
                        'label' => 'Propriété',
                        'width' => '150',
                        'options' => array('sort' => true)
                    );

                $array_columns['auto']['valeur'] =
                    array(
                        'label' => 'Valeur',
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

                $skin->display_array($array_columns, $array_values, 'docfile_meta', array('sortable' => true));
            break;

            case 'history':
                $array_columns = array();
                $array_values = array();

                $array_columns['left']['vers'] =
                    array(
                        'label' => 'Vers',
                        'width' => '60',
                        'options' => array('sort' => true)
                    );

                $array_columns['right']['taille'] =
                    array(
                        'label' => 'Taille',
                        'width' => '80',
                        'options' => array('sort' => true)
                    );

                $array_columns['right']['par'] =
                    array(
                        'label' => 'Par',
                        'width' => '100',
                        'options' => array('sort' => true)
                    );

                $array_columns['right']['modif'] =
                    array(
                        'label' => 'Modifié le',
                        'width' => '140',
                        'options' => array('sort' => true)
                    );

                $array_columns['auto']['fichier'] =
                    array(
                        'label' => 'Fichier',
                        'options' => array('sort' => true)
                    );

                $c = 0;

                $history = $docfile->gethistory();

                foreach($history as $row)
                {
                    $ldate_modify = (!empty($row['timestp_modify'])) ? ploopi_timestamp2local($row['timestp_modify']) : array('date' => '', 'time' => '');

                    $array_values[$c]['values']['vers']     = array('label' => $row['version'], 'style' => '');
                    $array_values[$c]['values']['taille']   = array('label' => sprintf("%0.2f kio", ($row['size']/1024)), 'style' => '');
                    $array_values[$c]['values']['par']  = array('label' => $row['login'], 'style' => '');
                    $array_values[$c]['values']['modif']    = array('label' => "{$ldate_modify['date']} {$ldate_modify['time']}", 'style' => '');
                    $array_values[$c]['values']['fichier']  = array('label' => $row['name'], 'style' => '');
                    $array_values[$c]['description'] = ploopi_htmlentities("{$row['name']} ({$row['version']})");
                    $array_values[$c]['link'] = "admin.php?ploopi_op=doc_filedownload&docfile_md5id={$row['md5id']}&version={$row['version']}";
                    $array_values[$c]['style'] = '';
                    $c++;
                }

                $skin->display_array($array_columns, $array_values, 'docfile_history', array('sortable' => true, 'orderby_default' => 'vers', 'sort_default' => 'DESC'));
            break;

            default:
                ?>
                <div class="doc_fileform_main">
                    <?php
                    if (!$readonly)
                    {
                        doc_getvalidation();
                        $wf_validator = in_array($currentfolder, $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']);

                        if (_PLOOPI_USE_CGIUPLOAD)
                        {
                            $sid = doc_guid();
                            ?>
                            <form method="post" enctype="multipart/form-data" action="<?php echo _PLOOPI_CGI_PATH; ?>/upload.cgi?sid=<?php echo $sid; ?>" onsubmit="javascript:return doc_file_validate(this,<?php echo ($newfile) ? 'true' : 'false'; ?>,<?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>, '<?php echo $sid; ?>', '<?php echo _PLOOPI_CGI_PATH; ?>');">
                            <input type="hidden" name="redirect" value="../<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_filesave&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}"); ?>">
                            <?php
                        }
                        else
                        {
                            ?>
                            <form method="post" enctype="multipart/form-data" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=doc_filesave&currentfolder={$currentfolder}&docfile_md5id={$docfile->fields['md5id']}"); ?>" onsubmit="javascript:return doc_file_validate(this,<?php echo ($newfile) ? 'true' : 'false'; ?>,<?php echo (!empty($wfusers) && !$wf_validator) ? 'true' : 'false'; ?>);">
                            <?php
                        }
                        ?>
                        <input type="hidden" name="doc_mode" id="doc_mode" value="host">
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_filesize*1024; ?>">
                        <?php
                    }
                    ?>

                    <div style="float:right;width:40%;">
                        <?php
                        if (!$readonly)
                        {
                            ?>
                            <fieldset style="border:1px solid #c0c0c0;margin:4px 4px 0 0;">
                                <legend>Mettre à jour avec un fichier situé</LEGEND>
                                <?php
                                if ($booServerModeAvailable)
                                {
                                    ?>
                                    <div style="padding:4px;">
                                        <p class="ploopi_checkbox" style="padding:2px 0;" onclick="javascript:ploopi_checkbox_click(event, '_docfile_location_host');">
                                            <input type="radio" name="_docfile_location" id="_docfile_location_host" value="host" checked="checked" onchange="javascript:$('doc_form_host').style.display = 'block'; $('doc_form_server').style.display = 'none'; $('docfile_file_server').value = ''; $('doc_mode').value='host'; " />
                                            <span>sur mon poste</span>
                                        </p>
                                        <p class="ploopi_checkbox" style="padding:2px 0;" onclick="javascript:ploopi_checkbox_click(event, '_docfile_location_server');">
                                            <input type="radio" name="_docfile_location" id="_docfile_location_server" value="server" onchange="javascript:$('doc_form_host').style.display = 'none'; $('doc_form_server').style.display = 'block'; $('docfile_file_host').value = ''; $('doc_mode').value='server';" />
                                            <span>sur le serveur</span>
                                        </p>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div id="doc_form_host" style="display:block;">
                                    <p class="ploopi_va" style="margin-bottom:2px;">
                                        <input type="file" name="docfile_file_host" id="docfile_file_host" />
                                    </p>
                                </div>
                                <?php
                                if ($booServerModeAvailable)
                                {
                                    ?>
                                    <div id="doc_form_server" style="display:none;">
                                        <p class="ploopi_va" style="margin-bottom:2px;">
                                            <input type="text" class="text" name="_docfile_file_server" id="docfile_file_server" value="" style="width:200px;" readonly />
                                            <input type="button" class="button" value="Parcourir" style="width:90px;" onclick="javascript:ploopi_filexplorer_popup('<?php echo ploopi_filexplorer_init(_PLOOPI_PATHSHARED, "docfile_file_server", "docfile_explorer"); ?>', event);" />
                                        </p>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div id="doc_progressbar" style="display:none;"><div id="doc_progressbar_bg"></div></div>
                                <div id="doc_progressbar_txt"></div>
                            </fieldset>
                            <?php
                        }
                        ?>
                    </div>

                    <div style="float:left;width:59%;">
                        <?php
                        include_once './include/classes/user.php';

                        $user = new user();
                        $user_modify = new user();

                        if ($user->open($docfile->fields['id_user'])) $user_name = "{$user->fields['lastname']} {$user->fields['firstname']}";
                        else $user_name = "<i>supprimé</i>";

                        if ($user_modify->open($docfile->fields['id_user_modify'])) $user_modify_name = "{$user->fields['lastname']} {$user->fields['firstname']}";
                        else $user_modify_name = "<i>supprimé</i>";

                        $ldate_modify = (!empty($docfile->fields['timestp_modify'])) ? ploopi_timestamp2local($docfile->fields['timestp_modify']) : array('date' => '', 'time' => '');
                        //echo $user->fields['login'];
                        ?>
                        <div class="ploopi_form" style="padding:2px;">
                            <p>
                                <label>Nom du Fichier:</label>
                                <?php
                                if ($readonly) echo ploopi_htmlentities($docfile->fields['name']);
                                else
                                {
                                    ?>
                                    <input type="text" class="text" name="docfile_name" value="<?php echo ploopi_htmlentities($docfile->fields['name']); ?>">
                                    <?php
                                }
                                ?>
                            </p>
                            <p>
                                <label>Dossier:</label>
                                <?php
                                $strParent = '';
                                $objDocFolderParent = new docfolder();
                                if ($objDocFolderParent->open($docfile->fields['id_folder'])) $strParent = $objDocFolderParent->fields['name'];
                                elseif ($docfile->fields['id_folder'] == 0) $strParent = 'Racine';

                                if ($readonly)
                                {
                                    ?>
                                    <span><a title="Aller au dossier" href="<? echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$docfile->fields['id_folder']}"); ?>"><?php echo ploopi_htmlentities($strParent); ?></a></span>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <input type="hidden" name="docfile_id_folder" id="docfolder_id_folder" value="<? echo $docfile->fields['id_folder']; ?>" />
                                    <a title="Choisir un autre dossier parent" href="javascript:void(0);" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_folderselect&doc_id_folder='+$('docfolder_id_folder').value, false), 300, event, 'click', 'doc_popup_folderselect');" class="ploopi_va">
                                        <span style="width:auto;" id="docfolder_id_folder_name"><?php echo ploopi_htmlentities($strParent); ?></span><img style="margin-left:6px;" src="./modules/doc/img/ico_folder.png" />
                                    </a>
                                    <?php
                                }
                                ?>
                            </p>
                            <p>
                                <label>Version:</label>
                                <span><?php echo $docfile->fields['version']; ?></span>
                            </p>
                            <p>
                                <label>Taille:</label>
                                <span><?php printf("%0.2f kio", ($docfile->fields['size']/1024)); ?></span>
                            </p>
                            <p>
                                <label>Propriétaire:</label>
                                <span><?php echo $user_name; ?></span>
                            </p>
                            <p>
                                <label>Modifié par:</label>
                                <span><?php echo $user_modify_name; ?></span>
                            </p>
                            <p>
                                <label>Dernière modification:</label>
                                <span><?php echo "{$ldate_modify['date']} {$ldate_modify['time']}"; ?></span>
                            </p>
                            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event, 'docfile_readonly');">
                                <label>Contenu protégé:</label>
                                <?php
                                if ($readonly) echo ($docfile->fields['readonly']) ? 'oui' : 'non';
                                else
                                {
                                    ?>
                                    <input type="checkbox" class="checkbox" id="docfile_readonly" name="docfile_readonly" value="1" <?php if ($docfile->fields['readonly']) echo 'checked'; ?>>
                                    <?php
                                }
                                ?>
                            </p>
                            <p>
                                <label>Commentaire:</label>
                                <?php
                                if ($readonly) echo ploopi_nl2br(ploopi_htmlentities($docfile->fields['description']));
                                else
                                {
                                    ?>
                                    <textarea class="text" name="docfile_description"><?php echo ploopi_htmlentities($docfile->fields['description']); ?></textarea>
                                    <?php
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <div style="clear:both;padding:4px;text-align:right;">
                        <?php
                        if (!$readonly)
                        {
                            ?>
                            <input type="button" class="flatbutton" value="Ré-indéxer" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin-light.php?ploopi_op=doc_fileindex&currentfolder={$currentfolder}&docfile_md5id={$_GET['docfile_md5id']}"); ?>';">
                            <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
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
            break;
        }
    }
    else
    {
        ?>
        <div style="padding:4px;" class="error">ERREUR: le fichier n'existe plus</div>
        <?
    }
    ?>

    <div style="border-bottom:1px solid #c0c0c0;">
    <?php
    if (isset($objFolder->fields['foldertype']) && $objFolder->fields['foldertype'] != 'private')
    {
        $arrAllowedActions = array(
            _DOC_ACTION_MODIFYFILE,
            _DOC_ACTION_DELETEFILE
        );

        $parents = explode(',', "{$objFolder->fields['parents']},{$objFolder->fields['id']}");
        for ($i = 0; $i < sizeof($parents); $i++)
        {
            if (ploopi_subscription_subscribed(_DOC_OBJECT_FOLDER, $parents[$i]))
            {
                $objDocFolderSub = new docfolder();
                $objDocFolderSub->open($parents[$i])
                ?>
                <div style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">
                Vous héritez de l'abonnement à &laquo; <a href="<? echo ploopi_urlencode("admin.php?op=doc_browser&currentfolder={$parents[$i]}"); ?>"><?php echo $objDocFolderSub->fields['name']; ?></a> &raquo;
                </div>
                <?php
            }
        }
        ploopi_subscription(_DOC_OBJECT_FILE, $docfile->fields['md5id'], $arrAllowedActions);
    }
    ?>
    </div>
    <?php
    if (ploopi_getparam('doc_viewannotations')) ploopi_annotation(_DOC_OBJECT_FILE, $docfile->fields['md5id'], $docfile->fields['name']);
}
?>
