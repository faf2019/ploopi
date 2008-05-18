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
 * Gestion des articles
 *
 * @package webedit
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$article = new webedit_article($type);

// get workflow validators
$wfusers = array();
$wf = ploopi_workflow_get(_WEBEDIT_OBJECT_HEADING, $headingid);
$wf_headingid = $headingid;

if (empty($wf)) // pas de validateur pour cette rubrique, on recherche sur les parents
{
    $parents = explode(';', $headings['list'][$headingid]['parents']);
    for ($i = sizeof($parents)-1; $i >= 0; $i--)
    {
        $wf = ploopi_workflow_get(_WEBEDIT_OBJECT_HEADING, $parents[$i]);
        if (!empty($wf))
        {
            $wf_headingid = $parents[$i];
            break;
        }
    }
}

foreach($wf as $value) $wfusers[] = $value['id_workflow'];


$title = '';
switch($op)
{
    case 'article_addnew':
        // force switching to draft
        $type = $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['type'] = 'draft';

        $user = new user();
        $user->open($_SESSION['ploopi']['userid']);

        $article->init_description();
        $article->fields['author'] = "{$user->fields['firstname']} {$user->fields['lastname']}";
        $article->fields['status'] = 'edit';
        $article->fields['visible'] = 1;
        $articleid = -1;
        $title = "Ajout d'un nouvel article";

        $article_timestp = $article_timestp_published = $article_timestp_unpublished = $lastupdate_timestp = $lastupdate_user = '';

        $isnewversion = 0;

    break;

    case 'article_modify':
        $article->open($articleid);
        $title = "Modification de l'article '{$article->fields['title']}'";

        $ldate = ($article->fields['timestp']) ? ploopi_timestamp2local($article->fields['timestp']) : array('date' => '');
        $article_timestp = $ldate['date'];

        $ldate = ($article->fields['timestp_published']) ? ploopi_timestamp2local($article->fields['timestp_published']) : array('date' => '');
        $article_timestp_published = $ldate['date'];

        $ldate = ($article->fields['timestp_unpublished']) ? ploopi_timestamp2local($article->fields['timestp_unpublished']) : array('date' => '');
        $article_timestp_unpublished = $ldate['date'];

        $ldate = ($article->fields['lastupdate_timestp']) ? ploopi_timestamp2local($article->fields['lastupdate_timestp']) : array('date' => '', 'time' => '');
        $lastupdate_timestp = "{$ldate['date']} {$ldate['time']}";

        $user = new user();
        if ($user->open($article->fields['lastupdate_id_user'])) $lastupdate_user = "{$user->fields['firstname']} {$user->fields['lastname']} ({$user->fields['login']})";
        else $lastupdate_user = '';

        $isnewversion = $articles['list'][$articleid]['new_version'];
    break;
}

$content = strip_tags(html_entity_decode($article->fields['content']));

list($keywords, $words_indexed, $words_overall) = ploopi_getwords($content);

$keywords = array_slice($keywords, 0 , 20, true);


?>
<div style="background-color:#e0e0e0;padding:4px;border-bottom:1px solid #c0c0c0;">
    <p class="ploopi_va" style="font-weight:bold;">

    <img title="Agrandir/Réduire" alt="Agrandir/Réduire" style="cursor:pointer;" onclick="javascript:ploopi_switchdisplay('webedit_tree');ploopi_switchdisplay('webedit_article_options');ploopi_xmlhttprequest('admin-light.php', 'op=xml_switchdisplay&display='+ploopi_getelem('webedit_tree').style.display, true);" src="./modules/webedit/img/fullscreen.png">
    <img src="./modules/webedit/img/doc<? echo $isnewversion; ?>.png">
    <?
    echo "<span>{$title} - </span>";

    if ($type == 'draft')
    {
        $msg = '';
        $img = 'ico_yes.gif';

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
            <span>Document de Travail<? echo $msg; ?>&nbsp;</span>
        <?
    }
    else
    {
        ?>
            <span>Version en Ligne : non modifiable&nbsp;</span>
        <?
    }

    $readonly = (!(ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) && $type == 'draft' && ($article->fields['status'] == 'edit' || (in_array($_SESSION['ploopi']['userid'],$wfusers)) && ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_PUBLISH))));
    //$readonly = (!ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || ($type == 'online') || (($type == 'draft') && ($article->fields['status'] == 'wait') && (!(ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_PUBLISH) || in_array($_SESSION['ploopi']['userid'],$wfusers))) ));
    ?>
    </p>
</div>

<form name="form_webedit_article" style="margin:0;" action="<? echo $scriptenv; ?>" method="post" onsubmit="javascript:return webedit_article_validate(this,'<? echo $type; ?>','<? echo $article->fields['status']; ?>', <? echo (in_array($_SESSION['ploopi']['userid'],$wfusers)) ? 'true' : 'false'; ?>);">
<input type="hidden" name="op" value="article_save">
<input type="hidden" name="articleid" id="articleid" value="<? echo $article->fields['id']; ?>">

<div style="padding:4px;border-bottom:1px solid #c0c0c0;background-color:#e8e8e8;">
<?
if ($type == 'draft')
{
    $article_online = new webedit_article();
    ?>
    Statut:&nbsp;
    <select name="webedit_article_status" class="select">
        <?
        foreach($article_status as $key => $value)
        {
            ?>
            <option <? echo ($key == $article->fields['status']) ? 'selected' : ''; ?> value="<? echo $key; ?>"><? echo $value; ?></option>
            <?
        }
        ?>
    </select>
    <?
    if ($article->fields['status'] == 'wait')
    {
        //L'article est en attente de validation et n'est plus modifiable (sauf pour les validateurs). Vous devez changer son statut pour le modifier.
        ?>
        <?
    }

    if ($article_online->open($article->fields['id']))
    {
        ?>
        <input type="button" class="flatbutton" value="Voir la Version en Ligne" onclick="javascript:document.location.href='<? echo "{$scriptenv}?op=article_modify&articleid={$articleid}&type=online"; ?>';">
        <?
    }
}
else
{
    ?>
    <input type="button" class="flatbutton" value="Modifier le Brouillon" onclick="javascript:document.location.href='<? echo "{$scriptenv}?op=article_modify&articleid={$articleid}&type=draft"; ?>';">
    Cette version n'est pas modifiable, vous devez d'abord modifier le brouillon puis publier l'article.
    <?
}
?>
</div>

<div style="padding:4px;overflow:auto;">
    <div id="webedit_article_options" style="display:<? echo $_SESSION['webedit'][$_SESSION['ploopi']['moduleid']]['treeview_display']; ?>;">
        <div class="ploopi_form" style="float:left; width:54%;">
            <div style="padding:2px;">
                <p>
                    <label>Référence:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_reference" value="<? echo $article->fields['reference']; ?>" tabindex="1" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['reference'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Titre:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_title" value="<? echo $article->fields['title']; ?>" tabindex="2" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['title'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <div style="padding:2px;"><strong>Optimisation du Référencement (balises meta):</strong></div>
                <p>
                    <label>Titre (title):</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_metatitle" id="webedit_article_metatitle" value="<? echo $article->fields['metatitle']; ?>" tabindex="2" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['metatitle'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Mots Clés (keywords):</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_metakeywords" id="webedit_article_metakeywords" value="<? echo $article->fields['metakeywords']; ?>" tabindex="2" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['metakeywords'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <?
                if (!$readonly)
                {
                    ?>
                    <p>
                        <label>Suggestions:</label>
                        <span id="webedit_suggestions">
                            <?
                            $keywords_disp_array = array();

                            foreach($keywords as $kw => $value)
                            {
                                $keywords_disp_array[] = '<a href="javascript:void(0);" onclick="javascript:webedit_article_keywordscomplete(\''.addslashes($kw).'\');">'.$kw.'</a><sup>'.$value.'</sup>';
                            }
                            echo implode(' ', $keywords_disp_array);
                            ?>
                        </span>
                    </p>
                    <?
                }
                ?>
                <p>
                    <label>Description (description):</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <textarea class="text" name="webedit_article_metadescription" style="height:50px;"><? echo htmlentities($article->fields['metadescription'], ENT_QUOTES); ?></textarea>
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['metadescription']).'</span>';
                    ?>
                </p>
                <div style="padding:2px;"><strong>Classification:</strong></div>
                <p>
                    <label>Etiquettes:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_tags" id="webedit_article_tags" value="<? echo htmlentities($article->fields['tags'], ENT_QUOTES); ?>" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['tags']).'</span>';
                    ?>
                </p>
            </div>
        </div>
        <div class="ploopi_form" style="float:left; width:45%;">
            <div style="padding:2px;">
                <p>
                    <label>Auteur:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input class="text" type="text" name="webedit_article_author" value="<? echo htmlentities($article->fields['author'], ENT_QUOTES); ?>" tabindex="3" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['author']).'</span>';
                    ?>
                </p>
                <p>
                    <label>Date:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp" id="webedit_article_timestp" value="<? echo $article_timestp; ?>" tabindex="4" />
                        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('webedit_article_timestp', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <?
                    }
                    else echo '<span>'.htmlentities($article_timestp, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Version:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_version" value="<? echo htmlentities($article->fields['version'], ENT_QUOTES); ?>" tabindex="5" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['version'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Position:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:40px;" class="text" type="text" name="webedit_art_position" value="<? echo htmlentities($article->fields['position'], ENT_QUOTES); ?>" tabindex="11" />
                        <?
                    }
                    else echo '<span>'.htmlentities($article->fields['position'], ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Visible dans le menu:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input type="checkbox" name="webedit_article_visible" style="width:14px;" value="1" <? if ($article->fields['visible']) echo 'checked'; ?> tabindex="12" />
                        <?
                    }
                    else echo ($article->fields['visible']) ? 'oui' : 'non';
                    ?>
                </p>
                <p>
                    <label>Commentaires autorisés:</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input type="checkbox" name="webedit_article_comments_allowed" style="width:14px;" value="1" <? if ($article->fields['comments_allowed']) echo 'checked'; ?> tabindex="12" />
                        <?
                    }
                    else echo ($article->fields['comments_allowed']) ? 'oui' : 'non';
                    ?>
                </p>
                <div style="padding:2px;"><strong>Options de mise en ligne:</strong></div>
                <p>
                    <label>Début :</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp_published" id="webedit_article_timestp_published" value="<? echo htmlentities($article_timestp_published, ENT_QUOTES); ?>" tabindex="13" />
                        <a href="#" onclick="javascript:ploopi_calendar_open('webedit_article_timestp_published', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <?
                    }
                    else echo '<span>'.htmlentities($article_timestp_published, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <p>
                    <label>Fin :</label>
                    <?
                    if (!$readonly)
                    {
                        ?>
                        <input style="width:100px;" class="text" type="text" name="webedit_article_timestp_unpublished" id="webedit_article_timestp_unpublished" value="<? echo htmlentities($article_timestp_unpublished, ENT_QUOTES); ?>" tabindex="14" />
                        <a href="#" onclick="javascript:ploopi_calendar_open('webedit_article_timestp_unpublished', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <?
                    }
                    else echo '<span>'.htmlentities($article_timestp_unpublished, ENT_QUOTES).'</span>';
                    ?>
                </p>
                <?
                if (!$readonly &&   $op == 'article_modify')
                {
                    ?>
                    <div style="padding:2px;"><strong>Dernières modifications:</strong></div>
                    <div style="padding:2px;">
                    <?
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

                    $db->query($sql);
                    if ($db->numrows() == 0) echo "Aucun historique pour cet article";
                    else
                    {
                        ?>
                        <select class="select" id="article_backup">
                        <?
                        while ($row = $db->fetchrow())
                        {
                            $ldate = ($row['timestp']) ? ploopi_timestamp2local($row['timestp']) : array('date' => '', 'time' => '');
                            $size = sprintf("%.02f",($row['l']/1024));
                            ?>
                            <option value="<? echo $row['timestp']; ?>"><? echo "{$ldate['date']} {$ldate['time']} par {$row['login']} - {$size} kio"; ?></option>
                            <?
                        }
                        ?>
                        </select>
                        <input type="button" class="button" value="Utiliser" style="width:20%;" onclick="javascript:webedit_backup_reload();">
                        <?
                    }
                    ?>
                    </div>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div style="clear:both;padding:4px;background-color:#e8e8e8;border-top:1px solid #c0c0c0;">
    <strong>Validateurs <? if ($wf_headingid != $headingid) echo "(Hérités de {$headings['list'][$wf_headingid]['label']})"; ?></strong>:
    <?
    if (!empty($wfusers))
    {
        $sql = "SELECT login FROM ploopi_user WHERE id in (".implode(',',$wfusers).") ORDER BY lastname, firstname";
        $db->query($sql);
        $arrUsers = $db->getarray();
        echo (empty($arrUsers)) ? 'Aucune accréditation' : implode(', ', $arrUsers);
    }
    else echo 'Aucune accréditation';

    if ($op != 'article_addnew')
    {
        ?>
        <br /><strong>Dernière modification le </strong><? echo $lastupdate_timestp; ?><strong> par </strong><? echo $lastupdate_user; ?>
        <?
        $url = "index.php?headingid={$headingid}&articleid={$articleid}";
        if (_PLOOPI_FRONTOFFICE_REWRITERULE) $url = ploopi_urlrewrite($url, $article->fields['metatitle']);
        ?>
        <br /><strong>Adresse de cette page : </strong>
        <?
        // si publié, on affiche un lien vers l'article
        if ($isnewversion == 2) echo 'article non publié';
        else
        {
            ?><a href="<? echo $url; ?>" target="_blank"><? echo $url; ?></a><?
        }
    }

    ?>
</div>

<div style="padding:4px;background-color:#e0e0e0;clear:both;border-width: 1px 0;border-color:#c0c0c0;border-style:solid;">
    <div style="float:right;" class="webedit_form_buttons">
    <?
    if ($type == 'draft')
    {
        if ($op != 'article_addnew' && in_array($_SESSION['ploopi']['userid'],$wfusers))
        {
            ?>
            <input class="flatbutton" style="font-weight:bold;" type="submit" name="publish" value="Publier">
            <?
        }
        else echo "Vous ne pouvez pas publier&nbsp;&nbsp;";
        ?>
        <input class="flatbutton" type="submit" value="Enregistrer">
        <?
    }
    ?>
    </div>
    <input class="flatbutton" type="button" value="Retour" onclick="javascript:document.location.href='<? echo $scriptenv ?>';">
    <?
    if ($op != 'article_addnew' && (in_array($_SESSION['ploopi']['userid'],$wfusers) || ($_SESSION['ploopi']['userid'] == $article->fields['id_user'] && $articles['list'][$articleid]['online_id'] == '')))
    {
        ?>
        <input class="flatbutton" type="button" value="<? echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<? echo ploopi_urlencode("{$scriptenv}?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <? echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?
    }
    ?>
</div>
<div style="clear:both;">
    <div id="xToolbar"></div>
    <?
    if (!$readonly)
    {
        ?>
        <input type="hidden" id="fck_webedit_article_content" name="fck_webedit_article_content" value="">
        <?
    }

    ?>
    <iframe id="webedit_frame_editor" style="border:0;width:100%;height:750px;margin:0;padding:0;" src="<? echo ploopi_urlencode("index.php?headingid={$headingid}&articleid={$articleid}&webedit_mode=edit&type={$type}&readonly={$readonly}"); ?>"></iframe>
</div>

<div style="padding:4px;background-color:#e0e0e0;clear:both;border-width: 1px 0;border-color:#c0c0c0;border-style:solid;">
    <div style="float:right;" class="webedit_form_buttons">
    <?
    if ($type == 'draft')
    {
        if ($op != 'article_addnew' && in_array($_SESSION['ploopi']['userid'],$wfusers))
        {
            ?>
            <input class="flatbutton" style="font-weight:bold;" type="submit" name="publish" value="Publier">
            <?
        }
        else echo "Vous ne pouvez pas publier&nbsp;&nbsp;";
        ?>
        <input class="flatbutton" type="submit" value="Enregistrer">
        <?
    }
    ?>
    </div>
    <input class="flatbutton" type="button" value="Retour" onclick="javascript:document.location.href='<? echo $scriptenv ?>';">
    <?
    if ($op != 'article_addnew' && (in_array($_SESSION['ploopi']['userid'],$wfusers) || ($_SESSION['ploopi']['userid'] == $article->fields['id_user'] && $articles['list'][$articleid]['online_id'] == '')))
    {
        ?>
        <input class="flatbutton" type="button" value="<? echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<? echo ploopi_urlencode("{$scriptenv}?op=article_delete&articleid={$article->fields['id']}"); ?>','Êtes-vous certain de vouloir supprimer l\'article &laquo; <? echo addslashes($article->fields['title']); ?> &raquo; ?');">
        <?
    }
    ?>
</div>

</form>
<?
if ($op != 'article_addnew')
{
    ?>
    <div style="clear:both;">
        <? $arrAllowedActions = array(_WEBEDIT_ACTION_ARTICLE_EDIT, _WEBEDIT_ACTION_ARTICLE_PUBLISH);?>
        <div style="border-bottom:1px solid #c0c0c0;">
        <? ploopi_subscription(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $arrAllowedActions, "à &laquo; {$article->fields['title']} &raquo;"); ?>
        </div>
        <? ploopi_annotation(_WEBEDIT_OBJECT_ARTICLE_ADMIN, $article->fields['id'], $article->fields['title']); ?>
    </div>
    <?
}
?>
