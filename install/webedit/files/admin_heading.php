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

$heading = new webedit_heading();
$heading->open($headingid);
?>

<p class="ploopi_va" style="background-color:#e0e0e0;padding:6px;border-bottom:1px solid #c0c0c0;">
    <?
    if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT))
    {
        ?>
        <a style="text-decoration:none;float:right;" href="<? echo "{$scriptenv}?op=article_addnew"; ?>">&nbsp;Ajouter un article</a>
        <img style="float:right;border:0px;cursor:pointer;" onclick="javascript:document.location.href='<? echo "{$scriptenv}?op=article_addnew"; ?>';" src="./modules/webedit/img/doc_add.png">
        <?
    }
    if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
    {
        ?>
        <a style="text-decoration:none;float:right;padding-right:6px;" href="<? echo "{$scriptenv}?op=heading_addnew"; ?>">&nbsp;Ajouter une sous-rubrique</a>
        <img style="float:right;border:0px;cursor:pointer;" onclick="javascript:document.location.href='<? echo "{$scriptenv}?op=heading_addnew"; ?>';" src="./modules/webedit/img/folder_add.png">
        <?
        if ($heading->fields['depth'] == 1) // root
        {
            ?>
            <a style="text-decoration:none;float:right;padding-right:6px;" href="<? echo "{$scriptenv}?op=heading_addroot"; ?>">&nbsp;Ajouter une racine</a>
            <img style="float:right;border:0px;cursor:pointer;" onclick="javascript:document.location.href='<? echo "{$scriptenv}?op=heading_addroot"; ?>';" src="./modules/webedit/img/folder_add.png">
            <?
        }
    }

    //if (ploopi_isactionallowed(_WEBEDIT_ACTION_DELETECAT) && $heading->fields['id_heading'] != 0)
    if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT) && !($heading->fields['id_heading'] == 0 && $heading->fields['position'] == 1))
    {
        ?>
        <a style="text-decoration:none;float:right;padding-right:6px;" href="javascript:void(0);" onclick="javascript:ploopi_confirmlink('<? echo "{$scriptenv}?op=heading_delete"; ?>','<? echo _PLOOPI_CONFIRM; ?>');">&nbsp;Supprimer cette rubrique</a>
        <img style="float:right;border:0px;cursor:pointer;" src="./modules/webedit/img/folder_del.png" onclick="javascript:ploopi_confirmlink('<? echo "{$scriptenv}?op=heading_delete"; ?>','<? echo _PLOOPI_CONFIRM; ?>');">
        <?
    }

    ?>
    <img title="Agrandir/Réduire" alt="Agrandir/Réduire" style="cursor:pointer;" onclick="javascript:ploopi_switchdisplay('webedit_tree');ploopi_switchdisplay('webedit_article_options');ploopi_xmlhttprequest('admin-light.php', 'op=xml_switchdisplay&display='+ploopi_getelem('webedit_tree').style.display, true);" src="./modules/webedit/img/fullscreen.png">
    
    <span style="font-weight:bold;">Propriété de la rubrique &laquo; <? echo $heading->fields['label']; ?> &raquo;</span>
</p>

<?
if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
{
    ?>
    <form style="margin:0;" action="<? echo $scriptenv; ?>" method="post" onsubmit="javascript:return webedit_heading_validate(this);">
    <input type="hidden" name="op" value="heading_save">
    <?
}
?>

<div class="webedit_main_form">
    <div class="ploopi_form" style="float:left;width:45%;">
        <div style="padding:2px;">
            <p style="font-weight:bold;">Propriétés:</p>
            <p>
                <label>Libellé:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_label"  value="<? echo htmlentities($heading->fields['label']); ?>" tabindex="1" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['label']).'</span>';
                ?>
            </p>
            <p>
                <label>Description:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <textarea class="text" name="webedit_heading_description" tabindex="2"><? echo htmlentities($heading->fields['description']); ?></textarea>
                    <?
                }
                else echo '<span>'.ploopi_nl2br(htmlentities($heading->fields['description'])).'</span>';
                ?>
            </p>
            <p>
                <label>Template:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <select class="select" name="webedit_heading_template" tabindex="3">
                        <?
                        if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                        else $webedit_template_name = '';
                        ?>
                        <option value=""><? echo $webedit_template_name; ?></option>
                        <?
                        $webedit_templates = webedit_gettemplates();
                        foreach ($webedit_templates as $tpl)
                        {
                            ?>
                            <option value="<? echo $tpl; ?>" <? if ($heading->fields['template'] == $tpl) echo 'selected'; ?>><? echo $tpl; ?></option>
                            <?
                        }
                        ?>
                    </select>
                    <?
                }
                else
                {
                    if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                    else $webedit_template_name = $heading->fields['template'];
                    echo '<span>'.htmlentities($webedit_template_name).'</span>';
                }
                ?>
            </p>
            <p>
                <label>Position:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" class="text" name="head_position" value="<? echo htmlentities($heading->fields['position']); ?>" style="width:40px;" tabindex="4" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['position']).'</span>';
                ?>

            </p>
            <p>
                <label for="webedit_heading_visible" style="cursor:pointer;"><strong>Visible:</strong></label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_visible" id="webedit_heading_visible" style="width:14px;" value="1" <? if ($heading->fields['visible']) echo 'checked'; ?> tabindex="5" />
                    <?
                }
                else echo ($heading->fields['visible']) ? 'oui' : 'non';
                ?>
            </p>
            <p>
                <label for="webedit_heading_url_window" style="cursor:pointer;">Ouvrir une nouvelle fenêtre:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_url_window" id="webedit_heading_url_window" style="width:14px;" value="1" <? if ($heading->fields['url_window']) echo 'checked'; ?> tabindex="9" />
                    <?
                }
                else echo ($heading->fields['url_window']) ? 'oui' : 'non';
                ?>
            </p>
            <p>
                <label>Trier les articles:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <select class="select" name="webedit_heading_sortmode">
                    <?
                    foreach($heading_sortmodes as $key => $value)
                    {
                        ?>
                        <option <? if ($heading->fields['sortmode'] == $key) echo 'selected'; ?> value="<? echo htmlentities($key); ?>"><? echo htmlentities($value); ?></option>
                        <?
                    }
                    ?>
                    </select>
                    <?
                }
                else
                {
                    ?><span><? echo $heading_sortmodes[$heading->fields['sortmode']]; ?></span><?
                }
                ?>
            </p>
        </div>
    </div>
    <div class="webedit_form" style="float:left;width:54%;">
        <div style="padding:2px;">
            <p style="font-weight:bold;">Contenu:</p>
            
            <p>
                <label>Type de Contenu:</label>
                <span>
                    <?
                    if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                    {
                        ?>
                        <div style="clear:both;cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'heading_content_type_article_first');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="article_first" id="heading_content_type_article_first" <? if ($heading->fields['content_type'] == 'article_first') echo 'checked'; ?> />Afficher le premier article
                        </div>
                        <div style="clear:both;cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'heading_content_type_article_redirect');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="article_redirect" id="heading_content_type_article_redirect" <? if ($heading->fields['content_type'] == 'article_redirect') echo 'checked'; ?> />Redirection vers un article
                        </div>
                        <div style="padding-left:20px;">
                            <?
                            if (!empty($heading->fields['linkedpage']))
                            {
                                $article = new webedit_article('draft');
                                $article->open($heading->fields['linkedpage']);
                                $article_title = $article->fields['title'];
                            }
                            else $article_title = '';
                            ?>
                            <input type="hidden" id="webedit_heading_linkedpage" name="webedit_heading_linkedpage" value="<? echo $heading->fields['linkedpage']; ?>">
                            <input type="text" readonly class="text" style="width:150px;" id="linkedpage_displayed" value="<? echo $article_title; ?>">
                            <img src="./modules/webedit/img/ico_choose_article.png" style="cursor:pointer;" title="Choisir un article" alt="Choisir" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php','op=heading_selectredirect',false), 300, event, 'click', 'webedit_popup_selectredirect');" />
                            <img src="./modules/webedit/img/ico_clear_article.png" style="cursor:pointer;" title="Effacer la redirection" alt="Choisir" onclick="javascript:ploopi_getelem('webedit_heading_linkedpage').value='';ploopi_getelem('linkedpage_displayed').value='';" />
                        </div>
                        <div style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'heading_content_type_url_redirect');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="url_redirect" id="heading_content_type_url_redirect" <? if ($heading->fields['content_type'] == 'url_redirect') echo 'checked'; ?> />Redirection vers une URL
                        </div>
                        <div style="padding-left:20px;">
                            <input type="text" class="text" name="webedit_heading_url" style="width:95%;" value="<? echo htmlentities($heading->fields['url']); ?>" onkeyup="javascript:if (this.value.length>0 && !$('heading_content_type_url_redirect').checked) ploopi_checkbox_click(event, 'heading_content_type_url_redirect');" tabindex="8" />
                        </div>
                        <div style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'heading_content_type_headings');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="headings" id="heading_content_type_headings" <? if ($heading->fields['content_type'] == 'headings') echo 'checked'; ?> />Afficher le contenu des sous rubriques
                        </div>
                        <div style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'heading_content_type_sitemap');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="sitemap" id="heading_content_type_sitemap" <? if ($heading->fields['content_type'] == 'sitemap') echo 'checked'; ?> />Afficher le plan du site
                        </div>
                        <?
                    }
                    else
                    {
                        switch($heading->fields['content_type'])
                        {
                            case 'article_first':
                                ?>
                                Afficher le premier article
                                <?
                            break;
                                
                            case 'article_redirect':
                                ?>
                                Redirection vers un article : <br />
                                <?
                                if (!empty($heading->fields['linkedpage']))
                                {
                                    $article = new webedit_article('draft');
                                    $article->open($heading->fields['linkedpage']);
                                    $article_title = $article->fields['title'];
                                }
                                else $article_title = '';
                                
                                echo $article_title;
                            break;
                                
                            case 'url_redirect':
                                ?>
                                Redirection vers une URL : <br /> 
                                <?
                                echo htmlentities($heading->fields['url']);                            
                            break;
                                
                            case 'headings':
                                ?>
                                Afficher des liens vers les sous-rubriques et les articles
                                <?
                            break;
                        }
                    }
                    ?>                    
                </span>
            </p>
            <p style="font-weight:bold;">Paramètres spécifiques:</p>
            <p>
                <label>Couleur:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" style="width:100px;" class="text" name="webedit_heading_color" id="webedit_heading_color" value="<? echo htmlentities($heading->fields['color']); ?>" tabindex="10" />
                    <a href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('webedit_heading_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                <?
                }
                else echo '<span>'.htmlentities($heading->fields['color']).'</span>';
                ?>
            </p>
            <p>
                <label>Position x:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" style="width:100px;" class="text" name="webedit_heading_posx"  value="<? echo htmlentities($heading->fields['posx']); ?>" tabindex="11" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['posx']).'</span>';
                ?>
            </p>
            <p>
                <label>Position y:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" style="width:100px;" class="text" name="webedit_heading_posy"  value="<? echo htmlentities($heading->fields['posy']); ?>" tabindex="12" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['posy']).'</span>';
                ?>
            </p>
            <p>
                <label>Champ Libre 1:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_free1"  value="<? echo htmlentities($heading->fields['free1']); ?>" tabindex="13" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['free1']).'</span>';
                ?>
            </p>
            <p>
                <label>Champ Libre 2:</label>
                <?
                if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_free2"  value="<? echo htmlentities($heading->fields['free2']); ?>" tabindex="14" />
                    <?
                }
                else echo '<span>'.htmlentities($heading->fields['free2']).'</span>';
                ?>
            </p>

        </div>
    </div>
</div>

<?
// get workflow validators
$wfusers = array();
$wf = ploopi_workflow_get(_WEBEDIT_OBJECT_HEADING, $headingid);
$wf_headingid = $headingid;

if (empty($wf)) // pas de validateur pour cette rubrique, on recherche sur les parents
{
    $parents = explode(';', $heading->fields['parents']);
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

?>
<div style="clear:both;padding:4px;">
    <strong>Validateurs <? if ($wf_headingid != $headingid) echo "(Hérités de &laquo; <a href=\"".ploopi_urlencode("{$scriptenv}?headingid={$wf_headingid}")."\">{$headings['list'][$wf_headingid]['label']}</a> &raquo;)"; ?></strong>:
    <?
    if (!empty($wfusers))
    {
        $sql = "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$wfusers).") ORDER BY lastname, firstname";
        $db->query($sql);
        $arrUsers = $db->getarray();
        echo (empty($arrUsers)) ? 'Aucune accréditation' : implode(', ', $arrUsers);
    }
    else echo 'Aucune accréditation';
    ?>
</div>


<?
if (ploopi_isactionallowed(_WEBEDIT_ACTION_WORKFLOW_MANAGE))
{
    ?>
    <div style="clear:both;padding:4px;font-weight:bold;"><? echo ($wf_headingid == $headingid) ? 'Vous pouvez modifier la liste des validateurs :' : 'Vous pouvez définir de nouveaux validateurs :'; ?></div>
    <div style="clear:both;padding:4px;">
        <div style="border:1px solid #c0c0c0;overflow:hidden;">
        <?
            ploopi_workflow_selectusers(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], -1, _WEBEDIT_ACTION_ARTICLE_PUBLISH);
        ?>
        </div>
    </div>
    <?
}

if (ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
{
    ?>
    <div style="text-align:right;padding:4px;">
        <input class="flatbutton" type="reset" value="Annuler">
        <input class="flatbutton" type="submit" value="Enregistrer">
    </div>
    </form>
    <?
}
?>

<div style="margin:0 4px 4px 4px;border-style:solid;border-width:1px 1px 0 1px;border-color:#c0c0c0;">
    <p class="ploopi_va" style="background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:4px 6px;overflow:auto;">
        <?
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT))
        {
            ?>
                <a style="float:right;text-decoration:none;" href="<? echo "$scriptenv?op=article_addnew"; ?>">&nbsp;Ajouter un article</a>
                <img style="float:right;border:0px;" src="./modules/webedit/img/doc_add.png">
            <?
        }
        ?>
        <b>Liste des articles de la rubrique &laquo; <? echo $heading->fields['label'] ?> &raquo;</b>
    </p>
    <?
    $articles_columns = array();
    
    $articles_columns['auto']['titre'] = array('label' => 'Titre', 'options' => array('sort' => true));
    $articles_columns['right']['auteur'] = array('label' => 'Auteur', 'width' => '130', 'options' => array('sort' => true));
    $articles_columns['right']['misenligne'] = array('label' => 'Mise en ligne', 'width' => '140');
    $articles_columns['right']['vers'] = array('label' => 'Vers.', 'width' => '60', 'options' => array('sort' => true));
    $articles_columns['right']['date'] = array('label' => 'Date', 'width' => '80', 'options' => array('sort' => true));
    
    $articles_columns['left']['pos'] = array('label' => 'P.', 'width' => '35', 'options' => array('sort' => true));
    $articles_columns['left']['ref'] = array('label' => 'Ref.', 'width' => '60', 'options' => array('sort' => true));
    
    $articles_columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '22');
    
    $articles_values = array();
    
    $c = 0;
    
    if (!empty($articles['tree'][$headingid]))
    {
        foreach($articles['tree'][$headingid] as $key => $idart)
        {
            $row = $articles['list'][$idart];
    
            $color = (!isset($color) || $color == 2) ? 1 : 2;
            $ldate = (!empty($row['timestp'])) ? ploopi_timestamp2local($row['timestp']) : array('date' => '', 'time' => '');
    
            $timestp_local = (!empty($row['timestp'])) ? ploopi_timestamp2local($row['timestp']) : array('date' => '');
            $timestp_published_local = (!empty($row['timestp_published'])) ? ploopi_timestamp2local($row['timestp_published']) : array('date' => '');
            $timestp_unpublished_local = (!empty($row['timestp_unpublished'])) ? ploopi_timestamp2local($row['timestp_unpublished']) : array('date' => '');
    
            $published = (!empty($timestp_published_local['date'])) ? "à partir du {$timestp_published_local['date']}" : '';
            $published .= (!empty($timestp_unpublished_local['date'])) ? (empty($published) ? '' : '<br />')."jusqu'au {$timestp_unpublished_local['date']}" : '';
    
            $art_title = ($row['status'] == 'wait') ? "{$row['title']} *" : $row['title'];
    
            $articles_values[$c]['values']['date'] = array('label' => $timestp_local['date'], 'style' => '', 'sort_label' => $row['timestp']);
            $articles_values[$c]['values']['pos'] = array('label' => $row['position'], 'style' => '');
            $articles_values[$c]['values']['ref'] = array('label' => $row['reference'], 'style' => '');
            $articles_values[$c]['values']['titre'] = array('label' => "<img src=\"./modules/webedit/img/doc{$articles['list'][$row['id']]['new_version']}.png\"><span>{$art_title}</span>", 'style' => '');
            $articles_values[$c]['values']['vers'] = array('label' => $row['version'], 'style' => '');
            $articles_values[$c]['values']['misenligne'] = array('label' => $published, 'style' => '');
            $articles_values[$c]['values']['auteur'] = array('label' => $row['author'], 'style' => '');
    
            //if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_PUBLISH) || in_array($_SESSION['ploopi']['userid'],$wfusers))
            if (in_array($_SESSION['ploopi']['userid'],$wfusers) || ($_SESSION['ploopi']['userid'] == $row['id_user'] && $articles['list'][$row['id']]['online_id'] == ''))
            {
                $articles_values[$c]['values']['actions'] = array('label' =>  "<a style=\"display:block;float:right;\" href=\"javascript:ploopi_confirmlink('{$scriptenv}?op=article_delete&articleid={$row['id']}','Êtes-vous certain de vouloir supprimer l\'article &laquo; ".addslashes($row['title'])." &raquo; ?');\"><img style=\"border:0px;\" src=\"./modules/webedit/img/doc_del.png\"></a>", 'style' => '');
            }
            else $articles_values[$c]['values']['actions'] = array('label' => '&nbsp;', 'style' => '');
    
            $articles_values[$c]['description'] = $row['title'];
            $articles_values[$c]['link'] = ploopi_urlencode("{$scriptenv}?op=article_modify&articleid={$row['id']}");
            $articles_values[$c]['style'] = '';
    
            $c++;
        }
    }
    
    switch($heading->fields['sortmode'])
    {
        case 'bydate':
            $options = array('sortable' => true, 'orderby_default' => 'date', 'sort_default' => 'DESC');
        break;
    
        case 'bydaterev':
            $options = array('sortable' => true, 'orderby_default' => 'date');
        break;
    
        case 'bypos':
        default:
            $options = array('sortable' => true, 'orderby_default' => 'pos');
        break;
    }
    
    
    $skin->display_array($articles_columns, $articles_values, 'webedit_articlelist', $options);
    ?>
</div>

<?
$parents = explode(';', $heading->fields['parents']);
for ($i = 0; $i < sizeof($parents); $i++)
{
    if (ploopi_subscription_subscribed(_WEBEDIT_OBJECT_HEADING, $parents[$i]))
    {
        ?>
        <div style="padding:2px 4px;font-weight:bold;">
        Vous héritez de l'abonnement à &laquo; <a href="<? echo ploopi_urlencode("{$scriptenv}?headingid={$parents[$i]}"); ?>"><? echo $headings['list'][$parents[$i]]['label']; ?></a> &raquo; 
        </div>
        <?
    }
}

$arrAllowedActions = array( _WEBEDIT_ACTION_ARTICLE_EDIT,
                            _WEBEDIT_ACTION_ARTICLE_PUBLISH,
                            _WEBEDIT_ACTION_CATEGORY_EDIT
                         );

ploopi_subscription(_WEBEDIT_OBJECT_HEADING, $headingid, $arrAllowedActions, "à &laquo; {$heading->fields['label']} &raquo;"); 
?>
<div style="border-top:1px solid #c0c0c0;">
<? ploopi_annotation(_WEBEDIT_OBJECT_HEADING, $headingid, $heading->fields['label']); ?>
</div>
