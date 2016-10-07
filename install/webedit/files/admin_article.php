<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestion des articles
 *
 * @package webedit
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$article = new webedit_article($type);

// récupère les validateurs
$arrWfUsers = array('group' => array(), 'user' => array());
$arrWf = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING, $headingid);
$intWfHeadingId = $headingid;

if (empty($arrWf)) // pas de validateur pour cette rubrique, on recherche sur les parents
{
    $arrParents = explode(';', $headings['list'][$headingid]['parents']);
    for ($i = sizeof($arrParents)-1; $i >= 0; $i--)
    {
        $arrWf = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING, $arrParents[$i]);
        if (!empty($arrWf))
        {
            $intWfHeadingId = $arrParents[$i];
            break;
        }
    }
}

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
    case 'article_addnew':
        // force switching to draft
        $type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';

        $user = new ploopi\user();
        $user->open($_SESSION['ploopi']['userid']);

        $article->init_description();
        $article->fields['author'] = "{$user->fields['firstname']} {$user->fields['lastname']}";
        $article->fields['status'] = 'edit';
        $article->fields['visible'] = 1;
        $articleid = -1;
        $title = "Ajout d'un nouvel article";

        $article_timestp_published = $article_timestp_unpublished = $lastupdate_timestp = $lastupdate_user = '';

        $article_timestp = current(ploopi\date::timestamp2local(ploopi\date::createtimestamp()));

        $isnewversion = 0;

    break;

    case 'article_modify':
        $article->open($articleid);
        $title = "Modification de l'article '{$article->fields['title']}'";

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

        $isnewversion = $articles['list'][$articleid]['new_version'];
    break;
}

$content = strip_tags(ploopi\str::html_entity_decode($article->fields['content']));

list($keywords, $words_indexed, $words_overall) = ploopi\str::getwords($content);

$keywords = array_slice($keywords, 0 , 20, true);

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

<form name="form_webedit_article" style="margin:0;" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" onsubmit="javascript:return webedit_article_validate(this,'<?php echo $type; ?>','<?php echo ploopi\str::htmlentities($article->fields['status']); ?>', <?php echo $booWfVal ? 'true' : 'false'; ?>);">
<input type="hidden" name="op" value="article_save">
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
                    <label>Titre:</label>
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
                    <label>Position:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:40px;" class="text" type="text" name="webedit_art_position" value="<?php echo ploopi\str::htmlentities($article->fields['position']); ?>" tabindex="11" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['position'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label for="webedit_article_visible" style="cursor:pointer;">Visible dans le menu:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input type="checkbox" name="webedit_article_visible" id="webedit_article_visible" class="checkbox" value="1" <?php if ($article->fields['visible']) echo 'checked="checked"'; ?> tabindex="12" />
                        <?php
                    }
                    else echo ($article->fields['visible']) ? 'oui' : 'non';
                    ?>
                </p>

                <div style="padding:2px;"><strong>Optimisation du Référencement (balises meta):</strong></div>
                <p>
                    <label>Titre (title):</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_metatitle" id="webedit_article_metatitle" value="<?php echo ploopi\str::htmlentities($article->fields['metatitle']); ?>" tabindex="2" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['metatitle'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Mots Clés (keywords):</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_metakeywords" id="webedit_article_metakeywords" value="<?php echo ploopi\str::htmlentities($article->fields['metakeywords']); ?>" tabindex="2" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['metakeywords'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <?php
                if (!$readonly)
                {
                    ?>
                    <p>
                        <label>Suggestions:</label>
                        <span id="webedit_suggestions">
                            <?php
                            $keywords_disp_array = array();

                            foreach($keywords as $kw => $value)
                            {
                                $keywords_disp_array[] = '<a href="javascript:void(0);" onclick="javascript:webedit_article_keywordscomplete(\''.addslashes($kw).'\');">'.$kw.'</a><sup>'.$value.'</sup>';
                            }
                            echo implode(' ', $keywords_disp_array);
                            ?>
                        </span>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <label>Description (description):</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <textarea class="text" name="webedit_article_metadescription" style="height:50px;"><?php echo ploopi\str::htmlentities($article->fields['metadescription'], ENT_QUOTES); ?></textarea>
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['metadescription']).'</span>';
                    ?>
                </p>
            </div>
        </div>

        <div class="ploopi_form" style="float:left; width:45%;">
            <div style="padding:2px;">
                <div style="padding:2px;"><strong>Propriétés annexes:</strong></div>
                <?php
                // on ne peut pas changer de parent à la création de l'article ou si on est en redacteur sans droit sur cet article
                if ($op != 'article_addnew')
                {
                    ?>
                    <p>
                        <?php
                        $heading_label = '';
                        $heading = new webedit_heading();
                        if (!empty($article->fields['id_heading']) && $heading->open($article->fields['id_heading'])) $heading_label = $heading->fields['label'];
                        ?>
                        <label>Rubrique parent:</label>
                        <span>
                        <?php
                        // on ne peut pas changer de parent à la création de l'article ou si on est en redacteur sans droit sur cet article
                        if(!$readonly)
                        {
                            ?>
                                <input type="hidden" id="webedit_article_id_heading" name="webedit_article_id_heading" value="<?php echo $article->fields['id_heading']; ?>">
                                <input type="text" readonly class="text" style="width:150px;" id="heading_displayed" value="<?php echo ploopi\str::htmlentities($heading_label); ?>">
                                <img src="./modules/webedit/img/ico_choose_article.png" style="cursor:pointer;" title="Choisir une rubrique parent" alt="Choisir" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_article_selectheading&hid='+$('webedit_article_id_heading').value, false), 300, event, 'click', 'webedit_popup_selectheading');" />
                            <?php
                        }
                        else
                            echo ploopi\str::htmlentities($heading_label);
                        ?>
                        </span>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <label>Référence:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_reference" value="<?php echo ploopi\str::htmlentities($article->fields['reference']); ?>" tabindex="1" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['reference'], ENT_QUOTES).'</span>';
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
                    <label>Commentaires autorisés:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input type="checkbox" name="webedit_article_comments_allowed" style="width:14px;" value="1" <?php if ($article->fields['comments_allowed']) echo 'checked="checked"'; ?> tabindex="12" />
                        <?php
                    }
                    else echo ($article->fields['comments_allowed']) ? 'oui' : 'non';
                    ?>
                </p>
                <div style="padding:2px;"><strong>Options de mise en ligne:</strong></div>
                <p>
                    <label>Début :</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp_published" id="webedit_article_timestp_published" value="<?php echo ploopi\str::htmlentities($article_timestp_published); ?>" tabindex="13" />
                        <?php
                        ploopi\date::open_calendar('webedit_article_timestp_published');
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article_timestp_published, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Fin :</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp_unpublished" id="webedit_article_timestp_unpublished" value="<?php echo ploopi\str::htmlentities($article_timestp_unpublished); ?>" tabindex="14" />
                        <?php
                        ploopi\date::open_calendar('webedit_article_timestp_unpublished');
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article_timestp_unpublished, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <div style="padding:2px;"><strong>Classification (nuage de tags):</strong></div>
                <p>
                    <label>Etiquettes:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_tags" id="webedit_article_tags" value="<?php echo ploopi\str::htmlentities($article->fields['tags']); ?>" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['tags']).'</span>';
                    ?>
                </p>
                <?php
                if (!$readonly && $op == 'article_modify')
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

                    ploopi\loader::getdb()->query($sql);
                    if (ploopi\loader::getdb()->numrows() == 0) echo "Aucun historique pour cet article";
                    else
                    {
                        ?>
                        <select class="select" id="article_backup">
                        <?php
                        while ($row = ploopi\loader::getdb()->fetchrow())
                        {
                            $ldate = ($row['timestp']) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '', 'time' => '');
                            $size = sprintf("%.02f",($row['l']/1024));
                            ?>
                            <option value="<?php echo ploopi\str::htmlentities($row['timestp']); ?>"><?php echo ploopi\str::htmlentities("{$ldate['date']} {$ldate['time']} par {$row['login']} - {$size} kio"); ?></option>
                            <?php
                        }
                        ?>
                        </select>
                        <input type="button" class="button" value="Utiliser" style="width:20%;" onclick="javascript:webedit_backup_reload();">
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
            <div><?php echo ploopi\str::htmlentities("Code supplémentaire, non filtré, à insérer dans la balise <HEAD> (js, css, meta, title...) :"); ?> (<a href="javascript:void(0);" onclick="javascript:$('fck_webedit_article_headcontent').style.height=(parseInt($('fck_webedit_article_headcontent').style.height,10)+20)+'px';" title="Permet d'agrandir la zone de saisie de 20px">agrandir la zone</a>)</div>
            <div>
            <?php
            $strHeadContent = htmlentities($article->fields['headcontent'], version_compare(phpversion(), '5.4', '<') ? ENT_COMPAT : ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');

            if (!$readonly)
            {
                ?>
                <textarea id="fck_webedit_article_headcontent" name="fck_webedit_article_headcontent" class="text" style="width:99%;height:16px;"><?php echo $strHeadContent; ?></textarea>
                <?php
            }
            else
            {
                ?>
                <div id="fck_webedit_article_headcontent" style="height:16px;">
                <pre>
                <?php
                echo $strHeadContent;
                ?>
                </pre>
                </div>
                <?php
            }
            ?>
            </div>
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
        <input type="hidden" name="webedit_article_visible" value="<?php echo ploopi\str::htmlentities($article->fields['visible']); ?>" />
        <input type="hidden" name="webedit_article_comments_allowed" value="<?php echo ploopi\str::htmlentities($article->fields['comments_allowed']); ?>" />
        <input type="hidden" name="webedit_article_disabledfilter" value="<?php echo ploopi\str::htmlentities($article->fields['disabledfilter']); ?>" />
        <div class="ploopi_form" style="float:left; width:54%;">
            <div style="padding:2px;">
                <p>
                    <label>Titre:</label>
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
                <p>
                    <label for="webedit_article_visible" style="cursor:pointer;">Visible dans le menu:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input type="checkbox" name="webedit_article_visible" id="webedit_article_visible" class="checkbox" value="1" <?php if ($article->fields['visible']) echo 'checked="checked"'; ?> tabindex="12" />
                        <?php
                    }
                    else echo ($article->fields['visible']) ? 'oui' : 'non';
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
                        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('webedit_article_timestp', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article_timestp, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Position:</label>
                    <?php
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:40px;" class="text" type="text" name="webedit_art_position" value="<?php echo ploopi\str::htmlentities($article->fields['position']); ?>" tabindex="11" />
                        <?php
                    }
                    else echo '<span>'.ploopi\str::htmlentities($article->fields['position'], ENT_QUOTES).'</span>';
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

                    ploopi\loader::getdb()->query(
                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrWfUsers['group']).") ORDER BY label"
                    );

                    while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                }
                if (!empty($arrWfUsers['user']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                    ploopi\loader::getdb()->query(
                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrWfUsers['user']).") ORDER BY lastname, firstname"
                    );

                    while ($row = ploopi\loader::getdb()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                }
            }
            else echo '<em>Aucune accréditation</em>';
            ?>
        </p>
    </fieldset>
</div>

<div style="clear:both;padding:4px;background-color:#e8e8e8;border-top:1px solid #c0c0c0;">
    <?php

    if ($op != 'article_addnew')
    {
        ?>
        <strong>&nbsp;-&nbsp;Dernière modification le </strong><?php echo $lastupdate_timestp; ?><strong> par </strong><?php echo ploopi\str::htmlentities($lastupdate_user); ?>
        <br /><strong>Adresse de cette page : </strong>
        <?php
        // si publié, on affiche un lien vers l'article
        if ($isnewversion == 2) echo 'article non publié';
        else
        {
            $url = $article->geturl();
            ?><a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a><?php
        }
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
        <input class="flatbutton" type="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<?php echo ploopi\crypt::urlencode("admin.php?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <?php echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?php
    }
    if ($type == 'draft')
    {
        $article_online = new webedit_article();
        if ($article_online->open($article->fields['id']))
        {
            ?>
            <input type="button" class="flatbutton" value="Voir la Version en Ligne" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=article_modify&articleid={$articleid}&type=online"); ?>';">
            <?php
        }
    }
    else
    {
        ?>
        <input type="button" class="flatbutton" value="Modifier le Brouillon" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=article_modify&articleid={$articleid}&type=draft"); ?>';">
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
        <input type="hidden" id="fck_webedit_article_content" name="fck_webedit_article_content" value="">
        <?php
    }
    ?>
    <iframe id="webedit_frame_editor" style="border:0;width:100%;height:750px;margin:0;padding:0;" src="<?php echo ploopi\crypt::urlencode("index.php?headingid={$headingid}&articleid={$articleid}&webedit_mode=edit&type={$type}&readonly={$readonly}"); ?>"></iframe>
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
        <input class="flatbutton" type="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<?php echo ploopi\crypt::urlencode("admin.php?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <?php echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?php
    }
    if ($type == 'draft')
    {
        $article_online = new webedit_article();
        if ($article_online->open($article->fields['id']))
        {
            ?>
            <input type="button" class="flatbutton" value="Voir la Version en Ligne" onclick="javascript:document.location.href='<?php echo "admin.php?op=article_modify&articleid={$articleid}&type=online"; ?>';">
            <?php
        }
    }
    else
    {
        ?>
        <input type="button" class="flatbutton" value="Modifier le Brouillon" onclick="javascript:document.location.href='<?php echo "admin.php?op=article_modify&articleid={$articleid}&type=draft"; ?>';">
        Cette version n'est pas modifiable, vous devez d'abord modifier le brouillon puis publier l'article.
        <?php
    }
    ?>
</div>

</form>
<?php
if ($op != 'article_addnew')
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
        <?php
        $objComment = new webedit_article_comment();
        $objComment->admin_comment($article->fields['id']); ?>
    </div>
    <?php
}
?>
