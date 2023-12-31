<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Gestion des blocs
 *
 * @package webedit
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$article = new webedit_article($type);

// récupère les validateurs
$arrWfUsers = array('group' => array(), 'user' => array());
$arrWf = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING, $headingid);
$intWfHeadingId = $headingid;


$objUser = new ploopi\user();
$objUser->open($_SESSION['ploopi']['userid']);
$arrGroups = $objUser->getgroups(true);

/**
 * L'utilisateur connecté est-il validateur ?
 */
$booWfVal = false;
foreach($arrWf as $value)
{
    if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booWfVal = true;
    if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booWfVal = true;

    $arrWfUsers[$value['type_validation']][] = $value['id_validation'];
}

$title = '';
switch($op)
{
    case 'bloc_addnew':
        // force switching to draft
        $type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';

        $user = new ploopi\user();
        $user->open($_SESSION['ploopi']['userid']);

        $article->init_description();
        $article->fields['author'] = "{$user->fields['firstname']} {$user->fields['lastname']}";
        $article->fields['status'] = 'edit';
        $article->fields['visible'] = 1;
        $articleid = -1;
        $title = "Ajout d'un nouveau bloc";

        $article_timestp_published = $article_timestp_unpublished = $lastupdate_timestp = $lastupdate_user = '';

        $article_timestp = current(ploopi\date::timestamp2local(ploopi\date::createtimestamp()));

        $isnewversion = 0;

    break;

    case 'bloc_modify':
        $article->open($articleid);
        $title = "Modification du bloc '{$article->fields['title']}'";

        $ldate = ($article->fields['timestp']) ? ploopi\date::timestamp2local($article->fields['timestp']) : array('date' => '');
        $article_timestp = $ldate['date'];

        $ldate = ($article->fields['timestp_published']) ? ploopi\date::timestamp2local($article->fields['timestp_published']) : array('date' => '');
        $article_timestp_published = $ldate['date'];

        $ldate = ($article->fields['timestp_unpublished']) ? ploopi\date::timestamp2local($article->fields['timestp_unpublished']) : array('date' => '');
        $article_timestp_unpublished = $ldate['date'];

        $ldate = ($article->fields['lastupdate_timestp']) ? ploopi\date::timestamp2local($article->fields['lastupdate_timestp']) : array('date' => '', 'time' => '');
        $lastupdate_timestp = "{$ldate['date']} {$ldate['time']}";

        $user = new ploopi\user();
        if ($user->open($article->fields['lastupdate_id_user'])) $lastupdate_user = "{$user->fields['firstname']} {$user->fields['lastname']} ({$user->fields['login']})";
        else $lastupdate_user = '';

        $isnewversion = $blocs['list'][$articleid]['new_version'];
    break;
}
?>

<div style="background-color:#e0e0e0;padding:4px;border-bottom:1px solid #c0c0c0;">
    <p class="ploopi_va" style="font-weight:bold;">
        <?php
        if ($type != 'draft' && ploopi\acl::isactionallowed(_WEBEDIT_ACTION_STATS))
        {
            ?>
            <img style="display:block;float:right;cursor:pointer;" src="./modules/webedit/img/chart.png" alt="Statistiques" title="Statistiques de visites de cet article" onclick="javascript:webedit_stats_open(<?php echo $article->fields['id']; ?>, null, event);">
            <?php
        }
        ?>
        <img src="./modules/webedit/img/doc<?php echo $isnewversion; ?>.png">
        <?php
        echo "<span>".ploopi\str::htmlentities($title)." - </span>";

        if ($type == 'draft')
        {
            switch($article->fields['status'])
            {
                case 'wait':
                    $msg = "&nbsp;(Attente de validation)";
                break;

                case 'edit':
                default:
                    $msg = "&nbsp;(Modifiable)";
                break;
            }
            ?>
                <span>Document de Travail<?php echo $msg; ?>&nbsp;</span>
            <?php
        }
        else
        {
            ?>
                <span>Version en Ligne : non modifiable&nbsp;</span>
            <?php
        }
        $readonly = (!((ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || webedit_isEditor($headingid)) && $type == 'draft' && ($article->fields['status'] == 'edit' || ($booWfVal) && ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ARTICLE_PUBLISH))));
        ?>
    </p>
</div>

<form name="form_webedit_article" style="margin:0;" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" onsubmit="javascript:return webedit_bloc_validate(this,'<?php echo $type; ?>','<?php echo ploopi\str::htmlentities($article->fields['status']); ?>', <?php echo $booWfVal ? 'true' : 'false'; ?>);">
<input type="hidden" name="op" value="bloc_save">
<input type="hidden" name="articleid" id="articleid" value="<?php echo $article->fields['id']; ?>">

<div style="padding:4px;overflow:auto;">
    <div id="webedit_article_options" style="display:<?php echo $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display']; ?>;">

    <?php
    if ($display_type == 'advanced')
    {
        ?>
        <div class="ploopi_form" style="float:left; width:54%;">
            <div style="padding:2px;">
                <div style="padding:2px;"><strong>Propriétés principales:</strong></div>
                <p>
                    <label>Identifiant:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_title" value="<?php echo ploopi\str::htmlentities($article->fields['title']); ?>" tabindex="2" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['title'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Auteur:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_author" value="<?php echo ploopi\str::htmlentities($article->fields['author']); ?>" tabindex="3" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['author']).'</span>';
                    ?>
                </p>
            </div>
        </div>

        <div class="ploopi_form" style="float:left; width:45%;">
            <div style="padding:2px;">
                <div style="padding:2px;"><strong>Propriétés annexes:</strong></div>
                <p>
                    <label>Date:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp" id="webedit_article_timestp" value="<?php echo ploopi\str::htmlentities($article_timestp); ?>" tabindex="4" />
                        <?php ploopi\date::open_calendar('webedit_article_timestp'); ?>
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article_timestp, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Version:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_version" value="<?php echo ploopi\str::htmlentities($article->fields['version']); ?>" tabindex="5" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['version'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Largeur (px):</label>
                    <?php
                    if (ploopi\acl::isadmin() && !$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_width" value="<?php echo ploopi\str::htmlentities($article->fields['width']); ?>" tabindex="6" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['width'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Hauteur (px):</label>
                    <?php
                    if (ploopi\acl::isadmin() && !$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_height" value="<?php echo ploopi\str::htmlentities($article->fields['height']); ?>" tabindex="7" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['height'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <?php
                if (!$readonly && $op == 'bloc_modify')
                {
                    ?>
                    <div style="padding:2px;"><strong>Dernières modifications:</strong></div>
                    <div style="padding:2px;">
                    <?php
                    $sql =  "
                            SELECT      b.timestp,
                                        CHAR_LENGTH(b.content) as l,
                                        u.login

                            FROM        ploopi_mod_webedit_article_backup b

                            LEFT JOIN   ploopi_user u
                            ON          u.id = b.id_user

                            WHERE       b.id_article = {$article->fields['id']}

                            ORDER BY    b.timestp DESC

                            LIMIT       0,10
                            ";

                    ploopi\db::get()->query($sql);
                    if (ploopi\db::get()->numrows() == 0) echo "Aucun historique pour cet article";
                    else
                    {
                        ?>
                        <select class="select" id="article_backup">
                        <?php
                        while ($row = ploopi\db::get()->fetchrow())
                        {
                            $ldate = ($row['timestp']) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '', 'time' => '');
                            $size = sprintf("%.02f",($row['l']/1024));
                            ?>
                            <option value="<?php echo ploopi\str::htmlentities($row['timestp']); ?>"><?php echo ploopi\str::htmlentities("{$ldate['date']} {$ldate['time']} par {$row['login']} - {$size} kio"); ?></option>
                            <?php
                        }
                        ?>
                        </select>
                        <input type="button" class="button" value="Utiliser" style="width:20%;" onclick="javascript:webedit_backup_reload(true);">
                        <?php
                    }
                    ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <div style="clear:both;">
            <div>
                <label for="webedit_article_disabledfilter" style="cursor:pointer;">Désactiver le validateur XHTML 1.0 Strict (inclusion javascript, styles):</label>
                <?php
                if (!$readonly)
                {
                    ?>
                    <input type="checkbox" name="webedit_article_disabledfilter" id="webedit_article_disabledfilter" class="checkbox" value="1" <?php if ($article->fields['disabledfilter']) echo 'checked="checked"'; ?> tabindex="12" />
                    <?php
                }
                else echo ($article->fields['disabledfilter']) ? 'oui' : 'non';
                ?>
            </div>
        </div>
        <?php
    }
    else
    {
        ?>
        <input type="hidden" name="webedit_article_disabledfilter" value="<?php echo ploopi\str::htmlentities($article->fields['disabledfilter']); ?>" />
        <div class="ploopi_form" style="float:left; width:54%;">
            <div style="padding:2px;">
                <p>
                    <label>Identifiant:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_title" value="<?php echo ploopi\str::htmlentities($article->fields['title']); ?>" tabindex="2" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['title'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Auteur:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_author" value="<?php echo ploopi\str::htmlentities($article->fields['author']); ?>" tabindex="3" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['author']).'</span>';
                    ?>
                </p>
            </div>
        </div>

        <div class="ploopi_form" style="float:left; width:45%;">
            <div style="padding:2px;">
                <p>
                    <label>Date:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp" id="webedit_article_timestp" value="<?php echo ploopi\str::htmlentities($article_timestp); ?>" tabindex="4" />
                        <a href="javascript:void(0);" onclick="javascript:ploopi.calendar.open('webedit_article_timestp', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article_timestp, ENT_QUOTES).'</span>';
                    ?>
                </p>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
</div>

<div style="clear:both;padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>Validateurs</strong> (utilisateurs qui peuvent publier)</legend>

        <p class="ploopi_va" style="padding:0 2px 2px 2px;"><span>Validateurs </span><?php if ($intWfHeadingId != $headingid) echo "<em>&nbsp;héritées de &laquo;&nbsp;</em><a href=\"".ploopi\crypt::urlencode("admin.php?headingid={$intWfHeadingId}")."\">{$headings['list'][$intWfHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
            <?php
            if (!empty($arrWfUsers))
            {
                if (!empty($arrWfUsers['group']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                    ploopi\db::get()->query(
                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrWfUsers['group']).") ORDER BY label"
                    );

                    while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                }
                if (!empty($arrWfUsers['user']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                    ploopi\db::get()->query(
                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrWfUsers['user']).") ORDER BY lastname, firstname"
                    );

                    while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                }
            }
            else echo '<em>Aucune accréditation</em>';
            ?>
        </p>
    </fieldset>
</div>

<div style="clear:both;padding:4px;background-color:#e8e8e8;border-top:1px solid #c0c0c0;">
    <?php

    if ($op != 'bloc_addnew')
    {
        ?>
        <strong>&nbsp;-&nbsp;Dernière modification le </strong><?php echo $lastupdate_timestp; ?><strong> par </strong><?php echo ploopi\str::htmlentities($lastupdate_user); ?>
        <?php
    }

    ?>
</div>

<div style="padding:4px; background-color:#e0e0e0; clear:both; border-width: 1px 0;border-color:#c0c0c0;border-style:solid; overflow: hidden;">
        <div style="float:right;" class="webedit_form_buttons">
        <?php
        if ($type == 'draft')
        {
            ?>
            Statut:&nbsp;
            <?php
            if(!$readonly)
            {
                ?>
                <select name="webedit_article_status" class="select">
                    <?php
                    foreach($article_status as $key => $value)
                    {
                        ?>
                        <option <?php echo ($key == $article->fields['status']) ? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo ploopi\str::htmlentities($value); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
            }
            else
                echo ploopi\str::htmlentities($article_status[$article->fields['status']]);

            if ($op != 'article_addnew' && ($booWfVal || ploopi\acl::isadmin()))
            {
                ?>
                <input class="flatbutton" style="font-weight:bold;" type="submit" name="publish" value="Publier">
                <?php
            }

            if(!$readonly)
            {
                ?>
                <input class="flatbutton" type="submit" value="Enregistrer">
                <?php
            }
        }
        ?>
        </div>
    <?php
    if ($op != 'article_addnew' && (ploopi\acl::isadmin() || $booWfVal || ($_SESSION['ploopi']['userid'] == $article->fields['id_user'] && $articles['list'][$articleid]['online_id'] == '')))
    {
        ?>
        <input class="flatbutton" type="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi.confirmlink('<?php echo ploopi\crypt::urlencode("admin.php?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <?php echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?php
    }
    if ($type == 'draft')
    {
        $article_online = new webedit_article();
        if ($article_online->open($article->fields['id']))
        {
            ?>
            <input type="button" class="flatbutton" value="Voir la Version en Ligne" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=bloc_modify&articleid={$articleid}&type=online"); ?>';">
            <?php
        }
    }
    else
    {
        ?>
        <input type="button" class="flatbutton" value="Modifier le Brouillon" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=bloc_modify&articleid={$articleid}&type=draft"); ?>';">
        Cette version n'est pas modifiable, vous devez d'abord modifier le brouillon puis publier l'article.
        <?php
    }
    ?>
</div>

<div style="clear:both;">
    <div id="xToolbar"></div>
    <?php
    if (!$readonly)
    {
        ?>
        <script src="./vendor/ckeditor/ckeditor/ckeditor.js"></script>
        <textarea name="fck_webedit_article_content" id="editor"><?php echo $article->fields['content']; ?></textarea>
        <script>

            // Ajout d'un plugin externe
            CKEDITOR.plugins.addExternal('tag', '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/plugins/tag/'; ?>', 'plugin.js');

            /*
            // http://docs.ckeditor.com/#!/guide/plugin_sdk_styles
            CKEDITOR.plugins.add( 'tag', {
                init: function( editor ) {
                    var pluginDirectory = '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/plugins/tag/'; ?>';
                    editor.addContentsCss( pluginDirectory + 'styles.css' );
                }
            } );
            */

            // http://docs.ckeditor.com/#!/guide/dev_file_browser_api
            CKEDITOR.replace( 'editor', {
                customConfig: '<?php echo _PLOOPI_BASEPATH.'/modules/webedit/ckeditor/config.js'; ?>',
                // doc_selectfile
                filebrowserBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=webedit_selector'); ?>',
                filebrowserImageBrowseUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=doc_selectimage'); ?>',
                // Url de choix des objets
                objectBrowserUrl: '<?php echo _PLOOPI_BASEPATH.'/admin-light.php?'.ploopi\crypt::queryencode('ploopi_op=ploopi_getobjects'); ?>',
                // Chargement de styles complémentaires (on remet le fichier par défaut en 1er)
                // Puis on ajoute la feuille de style des plugins...
                contentsCss: [
                    CKEDITOR.basePath+'contents.css',
                    '<?php echo _PLOOPI_BASEPATH; ?>/modules/webedit/ckeditor/plugins/tag/styles.css',
                ],
                extraPlugins: 'sharedspace,colorbutton,print',
                removePlugins: 'elementspath',

                sharedSpaces: {
                    top : 'xToolbar',
                },
                width: '<? echo $article->fields['width'] ? $article->fields['width'] : '100%'; ?>',
                height: '<? echo $article->fields['height'] ? $article->fields['height'] : '500px'; ?>',

                <?php
                if ($_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['display_type'] == 'beginner') {
                    ?>
                    toolbar: [
                        ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ],
                        ['Styles','Format','Font','FontSize'],
                        ['TextColor','BGColor'],
                        ['Image','Link','Unink'],
                    ]
                    <?php
                }
                ?>
            });
        </script>

        <?php
    }
    else
    {
        ?>
        <div style="padding:4px;"><?php echo $article->fields['content']; ?></div>
        <?php
    }
    ?>
</div>

<div style="padding:4px;background-color:#e0e0e0;clear:both;border-width: 1px 0;border-color:#c0c0c0;border-style:solid;">
    <?php
    if(!$readonly)
    {
        ?>
        <div style="float:right;" class="webedit_form_buttons">
        <?php
        if ($type == 'draft')
        {
            if ($op != 'article_addnew' && ($booWfVal || ploopi\acl::isadmin()))
            {
                ?>
                <input class="flatbutton" style="font-weight:bold;" type="submit" name="publish" value="Publier">
                <?php
            }
            ?>
            <input class="flatbutton" type="submit" value="Enregistrer">
            <?php
        }
        ?>
        </div>
        <?php
    }

    if ($op != 'article_addnew' && (ploopi\acl::isadmin() || $booWfVal || ($_SESSION['ploopi']['userid'] == $article->fields['id_user'] && $articles['list'][$articleid]['online_id'] == '')))
    {
        ?>
        <input class="flatbutton" type="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi.confirmlink('<?php echo ploopi\crypt::urlencode("admin.php?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <?php echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?php
    }
    if ($type == 'draft')
    {
        $article_online = new webedit_article();
        if ($article_online->open($article->fields['id']))
        {
            ?>
            <input type="button" class="flatbutton" value="Voir la Version en Ligne" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=bloc_modify&articleid={$articleid}&type=online"); ?>';">
            <?php
        }
    }
    else
    {
        ?>
        <input type="button" class="flatbutton" value="Modifier le Brouillon" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=bloc_modify&articleid={$articleid}&type=draft"); ?>';">
        Cette version n'est pas modifiable, vous devez d'abord modifier le brouillon puis publier l'article.
        <?php
    }
    ?>
</div>

</form>
<?php
if ($op != 'bloc_addnew')
{
    ?>
    <div style="clear:both;">
        <?php $arrAllowedActions = array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH);?>
        <div style="border-bottom:1px solid #c0c0c0;">
        <?php ploopi\subscription::display(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $arrAllowedActions, "à &laquo; {$article->fields['title']} &raquo;"); ?>
        </div>
        <div style="border-bottom:1px solid #c0c0c0;">
        <?php ploopi\annotation::display(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $article->fields['title']); ?>
        </div>
    </div>
    <?php
}
?>
