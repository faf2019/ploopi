<?php
/*
    Copyright (c) 2007-2018 Ovensia
    Copyright (c) 2009-2010 HeXad
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
 * Gestion des rubriques
 *
 * @package webedit
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$heading = new webedit_heading();
$heading->open($headingid);


$objUser = new ploopi\user();
$objUser->open($_SESSION['ploopi']['userid']);
$arrGroups = $objUser->getgroups(true);

// Recupère les Rédacteurs
$intEditorHeadingId = 0;
$arrEditorUsers = array();
$booEditorHeadingIdIsRoot = true;
$arrEditor = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $headingid);
if (empty($arrEditor)) // pas de partages pour cette rubrique, on recherche sur les parents
{
    $booEditorHeadingIdIsRoot = false;
    $arrParents = explode(';', $heading->fields['parents']);
    for ($i = sizeof($arrParents)-1; $i >= 0; $i--)
    {
        $arrEditor = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $arrParents[$i]);
        if (!empty($arrEditor))
        {
            $intEditorHeadingId = $arrParents[$i];
            break;
        }
    }
}
else
    $intEditorHeadingId = $headingid;

/**
 * L'utilisateur connecté est-il rédacteur ?
 */
$booIsAllowedEdit = $booIsEditor = false;
foreach($arrEditor as $value)
{
    if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booIsAllowedEdit = $booIsEditor = true;
    if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booIsAllowedEdit = $booIsEditor = true;

    $arrEditorUsers[$value['type_validation']][] = $value['id_validation'];
}

// Si l'utilisateur connecté n'est pas un "Rédacteur" on verif ses droits pour l'action _WEBEDIT_ACTION_CATEGORY_EDIT
if(!$booIsAllowedEdit) $booIsAllowedEdit = ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT);
?>

<p class="ploopi_va" style="background-color:#e0e0e0;padding:6px;border-bottom:1px solid #c0c0c0;">
    <?php
    if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_STATS))
    {
        ?>
        <img style="display:block;float:right;cursor:pointer;" src="./modules/webedit/img/chart.png" alt="Statistiques" title="Statistiques de visites de cette rubrique" onclick="javascript:webedit_stats_open(null, <?php echo $heading->fields['id']; ?>, event);">
        <?php
    }

    if ($heading->fields['id_heading'] == 0) // racine
    {
        ?>
        <img src="./modules/webedit/img/base.png">
        <span style="font-weight:bold;">Modification de la racine &laquo; <?php echo ploopi\str::htmlentities($heading->fields['label']); ?> &raquo;</span>
        <?php
    }
    else
    {
        ?>
        <img src="./modules/webedit/img/folder.png">
        <span style="font-weight:bold;">Modification de la rubrique &laquo; <?php echo ploopi\str::htmlentities($heading->fields['label']); ?> &raquo;</span>
        <?php
    }
    ?>
</p>
<div id="webedit_heading_toolbar">
    <?php
    if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || $booIsAllowedEdit)
    {
        ?>
        <p class="ploopi_va" style="float:left;padding:6px;cursor:pointer;" title="Ajouter un article" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=article_addnew"); ?>';" >
            <img src="./modules/webedit/img/doc_add.png">
            <span>Ajouter un article</span>
        </p>
        <?php
    }

    //if ($booIsAllowedEdit) Modifié par SE le 01/06/2010 (demande SZSIC)
    if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
    {
        ?>
        <p class="ploopi_va" style="float:left;padding:6px;cursor:pointer;" title="Ajouter une sous-rubrique" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=heading_addnew"); ?>';" >
            <img src="./modules/webedit/img/folder_add.png">
            <span>Ajouter une sous-rubrique</span>
        </p>
        <?php
    }
    // Ici on ne controle pas si c'est un rédacteur car ils n'ont de toutes les façons pas le droit de créer des racines !
    if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT) && $heading->fields['depth'] == 1) // root (Interdit au rédacteur !)
    {
        ?>
        <p class="ploopi_va" style="float:left;padding:6px;cursor:pointer;" title="Ajouter une racine" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=heading_addroot"); ?>';" >
            <img src="./modules/webedit/img/base_add.png">
            <span>Ajouter une racine</span>
        </p>
        <?php
    }

    //if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_DELETECAT) && $heading->fields['id_heading'] != 0)
    if ((ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT) || ($booIsEditor && !$booEditorHeadingIdIsRoot)) && !($heading->fields['id_heading'] == 0 && $heading->fields['position'] == 1) )
    {
        ?>
        <p class="ploopi_va" style="float:left;padding:6px;cursor:pointer;" title="Supprimer cette rubrique" onclick="javascript:ploopi.confirmlink('<?php echo ploopi\crypt::urlencode("admin.php?op=heading_delete"); ?>','<?php echo _PLOOPI_CONFIRM; ?>');" >
            <img src="./modules/webedit/img/folder_del.png">
            <span>Supprimer cette rubrique</span>
        </p>
        <?php
    }
    ?>
</div>

<?php
if ($booIsAllowedEdit)
{
    ?>
    <form style="margin:0;" action="<?php echo ploopi\crypt::urlencode('admin.php?op=heading_save'); ?>" method="post" onsubmit="javascript:return webedit_heading_validate(this);">
    <?php
}
?>

<div class="webedit_main_form">
<?php
if ($display_type == 'advanced')
{
    ?>
    <div class="ploopi_form" style="float:left;width:45%;">
        <div style="padding:2px;">
            <p style="font-weight:bold;">Propriétés principales:</p>
            <p>
                <label>Libellé:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_label"  value="<?php echo ploopi\str::htmlentities($heading->fields['label']); ?>" tabindex="1" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['label']).'</span>';
                ?>
            </p>
            <p>
                <label>Description:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <textarea class="text" name="webedit_heading_description" tabindex="2"><?php echo ploopi\str::htmlentities($heading->fields['description']); ?></textarea>
                    <?php
                }
                else echo '<span>'.ploopi\str::nl2br(ploopi\str::htmlentities($heading->fields['description'])).'</span>';
                ?>
            </p>
            <p>
                <label>Gabarit:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <select class="select" name="webedit_heading_template" tabindex="3">
                        <?php
                        if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                        else $webedit_template_name = '';
                        ?>
                        <option value=""><?php echo $webedit_template_name; ?></option>
                        <?php
                        $webedit_templates = webedit_gettemplates();
                        foreach ($webedit_templates as $tpl)
                        {
                            ?>
                            <option value="<?php echo $tpl; ?>" <?php if ($heading->fields['template'] == $tpl) echo 'selected'; ?>><?php echo $tpl; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                }
                else
                {
                    if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                    else $webedit_template_name = $heading->fields['template'];
                    echo '<span>'.ploopi\str::htmlentities($webedit_template_name).'</span>';
                }
                ?>
            </p>
            <p>
                <label>Position:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="head_position" value="<?php echo ploopi\str::htmlentities($heading->fields['position']); ?>" style="width:40px;" tabindex="4" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['position']).'</span>';
                ?>

            </p>

            <p>
                <label for="webedit_heading_visible" style="cursor:pointer;"><strong>Visible dans le menu:</strong></label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_visible" id="webedit_heading_visible" class="checkbox" value="1" <?php if ($heading->fields['visible']) echo 'checked'; ?> tabindex="5" />
                    <?php
                }
                else echo ($heading->fields['visible']) ? 'oui' : 'non';
                ?>
            </p>
            <p>
                <label for="webedit_heading_url_window" style="cursor:pointer;">Ouvrir une nouvelle fenêtre:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_url_window" id="webedit_heading_url_window" class="checkbox" value="1" <?php if ($heading->fields['url_window']) echo 'checked'; ?> tabindex="9" />
                    <?php
                }
                else echo ($heading->fields['url_window']) ? 'oui' : 'non';
                ?>
            </p>
            <p>
                <label>Trier les articles:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <select class="select" name="webedit_heading_sortmode">
                    <?php
                    foreach($heading_sortmodes as $key => $value)
                    {
                        ?>
                        <option <?php if ($heading->fields['sortmode'] == $key) echo 'selected'; ?> value="<?php echo ploopi\str::htmlentities($key); ?>"><?php echo ploopi\str::htmlentities($value); ?></option>
                        <?php
                    }
                    ?>
                    </select>
                    <?php
                }
                else
                {
                    ?><span><?php echo ploopi\str::htmlentities($heading_sortmodes[$heading->fields['sortmode']]); ?></span><?php
                }
                ?>
            </p>
            <p>
                <label for="webedit_heading_feed_enabled" style="cursor:pointer;">Fournir un flux RSS:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_feed_enabled" id="webedit_heading_feed_enabled" class="checkbox" value="1" <?php if ($heading->fields['feed_enabled']) echo 'checked'; ?> tabindex="9" />
                    <?php
                }
                else echo ($heading->fields['feed_enabled']) ? 'oui' : 'non';
                ?>
            </p>
            <p>
                <label for="webedit_heading_subscription_enabled" style="cursor:pointer;">Autoriser les abonnements:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_subscription_enabled" id="webedit_heading_subscription_enabled" class="checkbox" value="1" <?php if ($heading->fields['subscription_enabled']) echo 'checked'; ?> tabindex="9" />
                    <?php
                }
                else echo ($heading->fields['subscription_enabled']) ? 'oui' : 'non';
                ?>
            </p>
        </div>
    </div>

    <div class="ploopi_form" style="float:left;width:54%;">
        <div style="padding:2px;">
            <p style="font-weight:bold;">Contenu:</p>

            <p>
                <label style="width:20%;">&nbsp;</label>
                <span style="width:75%;">
                    <?php
                    if ($booIsAllowedEdit)
                    {
                        ?>
                        <span style="clear:both;cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_article_first');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="article_first" id="heading_content_type_article_first" <?php if ($heading->fields['content_type'] == 'article_first') echo 'checked'; ?> />Afficher le premier article
                        </span>
                        <span style="clear:both;cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_article_redirect');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="article_redirect" id="heading_content_type_article_redirect" <?php if ($heading->fields['content_type'] == 'article_redirect') echo 'checked'; ?> />Redirection vers un article ou une rubrique
                        </span>
                        <span style="padding-left:20px;">
                            <?php
                            $redirect_title = '';
                            if (!empty($heading->fields['linkedpage']))
                            {
                                if(substr($heading->fields['linkedpage'],0,1) == 'h') // C'est un heading !
                                {
                                    $objHeading = new webedit_heading();
                                    if ($objHeading->open(substr($heading->fields['linkedpage'],1))) $redirect_title = $objHeading->fields['label'];
                                }
                                else // C'est un article
                                {
                                    $article = new webedit_article('draft');
                                    if ($article->open($heading->fields['linkedpage'])) $redirect_title = $article->fields['title'];
                                }
                            }
                            ?>
                            <input type="hidden" id="webedit_heading_linkedpage" name="webedit_heading_linkedpage" value="<?php echo ploopi\str::htmlentities($heading->fields['linkedpage']); ?>">
                            <input type="text" readonly class="text" style="width:150px;" id="linkedpage_displayed" value="<?php echo ploopi\str::htmlentities($redirect_title); ?>">
                            <img src="./modules/webedit/img/ico_choose_article.png" style="display:block;float:left;cursor:pointer;margin:2px 4px;" title="Choisir un article" alt="Choisir" onclick="javascript:ploopi.popup.show(ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=webedit_heading_selectredirect',false), 300, event, 'click', 'webedit_popup_selectredirect');" />
                            <img src="./modules/webedit/img/ico_clear_article.png" style="display:block;float:left;cursor:pointer;margin:2px 4px;" title="Effacer la redirection" alt="Choisir" onclick="javascript:ploopi.getelem('webedit_heading_linkedpage').value='';ploopi.getelem('linkedpage_displayed').value='';" />
                        </span>
                        <span style="cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_url_redirect');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="url_redirect" id="heading_content_type_url_redirect" <?php if ($heading->fields['content_type'] == 'url_redirect') echo 'checked'; ?> />Redirection vers une URL
                        </span>
                        <span style="padding-left:20px;">
                            <input type="text" class="text" name="webedit_heading_url" style="width:100%;" value="<?php echo ploopi\str::htmlentities($heading->fields['url']); ?>" onkeyup="javascript:if (this.value.length>0 && !jQuery('#heading_content_type_url_redirect')[0].checked) ploopi.checkbox_click(event, 'heading_content_type_url_redirect');" tabindex="8" />
                        </span>
                        <span style="cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_headings');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="headings" id="heading_content_type_headings" <?php if ($heading->fields['content_type'] == 'headings') echo 'checked'; ?> />Afficher le contenu des sous rubriques
                        </span>
                        <span style="cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_blog');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="blog" id="heading_content_type_blog" <?php if ($heading->fields['content_type'] == 'blog') echo 'checked'; ?> />Afficher comme Blog
                        </span>
                        <span style="cursor:pointer;" onclick="javascript:ploopi.checkbox_click(event, 'heading_content_type_sitemap');">
                            <input style="cursor:pointer;" type="radio" name="webedit_heading_content_type" value="sitemap" id="heading_content_type_sitemap" <?php if ($heading->fields['content_type'] == 'sitemap') echo 'checked'; ?> />Afficher le plan du site
                        </span>
                        <?php
                    }
                    else
                    {
                        switch($heading->fields['content_type'])
                        {
                            case 'article_first':
                                ?>
                                Afficher le premier article
                                <?php
                            break;

                            case 'article_redirect':
                                ?>
                                Redirection vers un article ou une rubrique : <br />
                                <?php
                                if (!empty($heading->fields['linkedpage']))
                                {
                                    if(substr($heading->fields['linkedpage'],0,1) == 'h') // C'est un heading !
                                    {
                                        $objHeading = new webedit_heading();
                                        if ($objHeading->open(substr($heading->fields['linkedpage'],1))) $redirect_title = $objHeading->fields['label'];
                                    }
                                    else // C'est un article
                                    {
                                        $article = new webedit_article('draft');
                                        if ($article->open($heading->fields['linkedpage'])) $redirect_title = $article->fields['title'];
                                    }
                                }
                                else $redirect_title = '';

                                echo ploopi\str::htmlentities($redirect_title);
                            break;

                            case 'url_redirect':
                                ?>
                                Redirection vers une URL : <br />
                                <?php
                                echo ploopi\str::htmlentities($heading->fields['url']);
                            break;

                            case 'headings':
                                ?>
                                Afficher des liens vers les sous-rubriques et les articles
                                <?php
                            break;
                        }
                    }
                    ?>
                </span>
            </p>

            <p style="font-weight:bold;">Propriétés annexes:</p>
            <p>
                <label>Couleur:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" style="width:100px;cursor:pointer" class="text color {hash:true}" name="webedit_heading_color" id="webedit_heading_color" value="<?php echo ploopi\str::htmlentities($heading->fields['color']); ?>" tabindex="10" />
                <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['color']).'</span>';
                ?>
            </p>
            <p>
                <label>Position x:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" style="width:100px;" class="text" name="webedit_heading_posx"  value="<?php echo ploopi\str::htmlentities($heading->fields['posx']); ?>" tabindex="11" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['posx']).'</span>';
                ?>
            </p>
            <p>
                <label>Position y:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" style="width:100px;" class="text" name="webedit_heading_posy"  value="<?php echo ploopi\str::htmlentities($heading->fields['posy']); ?>" tabindex="12" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['posy']).'</span>';
                ?>
            </p>
            <p>
                <label>Champ Libre 1:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_free1"  value="<?php echo ploopi\str::htmlentities($heading->fields['free1']); ?>" tabindex="13" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['free1']).'</span>';
                ?>
            </p>
            <p>
                <label>Champ Libre 2:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_free2"  value="<?php echo ploopi\str::htmlentities($heading->fields['free2']); ?>" tabindex="14" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['free2']).'</span>';
                ?>
            </p>

        </div>
    </div>
    <?php
}
else
{
    ?>
    <input type="hidden" name="webedit_heading_url_window" value="<?php echo ploopi\str::htmlentities($heading->fields['url_window']); ?>" />
    <input type="hidden" name="webedit_heading_feed_enabled" value="<?php echo ploopi\str::htmlentities($heading->fields['feed_enabled']); ?>" />
    <input type="hidden" name="webedit_heading_subscription_enabled" value="<?php echo ploopi\str::htmlentities($heading->fields['subscription_enabled']); ?>" />

    <div class="ploopi_form" style="float:left;width:45%;">
        <div style="padding:2px;">
            <p>
                <label>Libellé:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="webedit_heading_label"  value="<?php echo ploopi\str::htmlentities($heading->fields['label']); ?>" tabindex="1" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['label']).'</span>';
                ?>
            </p>
            <p>
                <label>Gabarit:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <select class="select" name="webedit_heading_template" tabindex="3">
                        <?php
                        if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                        else $webedit_template_name = '';
                        ?>
                        <option value=""><?php echo $webedit_template_name; ?></option>
                        <?php
                        $webedit_templates = webedit_gettemplates();
                        foreach ($webedit_templates as $tpl)
                        {
                            ?>
                            <option value="<?php echo $tpl; ?>" <?php if ($heading->fields['template'] == $tpl) echo 'selected'; ?>><?php echo $tpl; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                }
                else
                {
                    if (isset($headings['list'][$headingid]['herited_template']) && $headings['list'][$headingid]['herited_template']) $webedit_template_name = $headings['list'][$headingid]['template'].' (hérité)';
                    else $webedit_template_name = $heading->fields['template'];
                    echo '<span>'.ploopi\str::htmlentities($webedit_template_name).'</span>';
                }
                ?>
            </p>
            <p>
                <label>Position:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="text" class="text" name="head_position" value="<?php echo ploopi\str::htmlentities($heading->fields['position']); ?>" style="width:40px;" tabindex="4" />
                    <?php
                }
                else echo '<span>'.ploopi\str::htmlentities($heading->fields['position']).'</span>';
                ?>
            </p>
            <p>
                <label for="webedit_heading_visible" style="cursor:pointer;"><strong>Visible:</strong></label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_visible" id="webedit_heading_visible" class="checkbox" value="1" <?php if ($heading->fields['visible']) echo 'checked'; ?> tabindex="5" />
                    <?php
                }
                else echo ($heading->fields['visible']) ? 'oui' : 'non';
                ?>
            </p>
        </div>
    </div>

    <div class="ploopi_form" style="float:left;width:54%;">
        <div style="padding:2px;">
            <p>
                <label>Description:</label>
                <?php
                if ($booIsAllowedEdit)
                {
                    ?>
                    <textarea class="text" name="webedit_heading_description" tabindex="2"><?php echo ploopi\str::htmlentities($heading->fields['description']); ?></textarea>
                    <?php
                }
                else echo '<span>'.ploopi\str::nl2br(ploopi\str::htmlentities($heading->fields['description'])).'</span>';
                ?>
            </p>
        </div>
    </div>
    <?php
}
?>
</div>

<?php

// récupère les validateurs
$arrWfUsers = array();
$arrWf = ploopi\validation::get(_WEBEDIT_OBJECT_HEADING, $headingid);
$intWfHeadingId = $headingid;

if (empty($arrWf)) // pas de validateur pour cette rubrique, on recherche sur les parents
{
    $arrParents = explode(';', $heading->fields['parents']);
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

// récupère les partages
$arrSharesUsers = array();
$intSharesHeadingId = 0;
$arrShares = ploopi\share::get(-1, _WEBEDIT_OBJECT_HEADING, $headingid);

if (empty($arrShares)) // pas de partages pour cette rubrique, on recherche sur les parents
{
    $arrParents = explode(';', $heading->fields['parents']);
    for ($i = sizeof($arrParents)-1; $i >= 0; $i--)
    {
        $arrShares = ploopi\share::get(-1, _WEBEDIT_OBJECT_HEADING, $arrParents[$i]);
        if (!empty($arrShares))
        {
            $intSharesHeadingId = $arrParents[$i];
            break;
        }
    }
}
else
    $intSharesHeadingId = $headingid;

foreach($arrShares as $value) $arrSharesUsers[$value['type_share']][] = $value['id_share'];

?>
<div style="clear:both;padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>Validateurs</strong> (utilisateurs qui peuvent publier)</legend>

        <p class="ploopi_va" style="padding:0 2px 2px 2px;"><span>Validateurs </span><?php if ($intWfHeadingId && $intWfHeadingId != $headingid) echo "<em>&nbsp;héritées de &laquo;&nbsp;</em><a href=\"".ploopi\crypt::urlencode("admin.php?headingid={$intWfHeadingId}")."\">{$headings['list'][$intWfHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
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

        <?php
        if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_WORKFLOW_MANAGE) && ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            ?>
            <div style="border:1px solid #c0c0c0;overflow:hidden;">
            <?php ploopi\validation::selectusers(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], -1, _WEBEDIT_ACTION_ARTICLE_PUBLISH, $intWfHeadingId == $headingid ? 'Modifier la listes des validateurs :' : 'Définir une nouvelle liste de validateurs :'); ?>
            </div>
            <?php
        }
        ?>
    </fieldset>
</div>

<div style="clear:both;padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>Rédacteurs</strong> (utilisateurs qui peuvent gérer cette branche)</legend>

        <p class="ploopi_va" style="padding:0 2px 2px 2px;"><span>Rédacteurs </span><?php if ($intEditorHeadingId && $intEditorHeadingId != $headingid) echo "<em>&nbsp;héritées de &laquo;&nbsp;</em><a href=\"".ploopi\crypt::urlencode("admin.php?headingid={$intEditorHeadingId}")."\">{$headings['list'][$intEditorHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
            <?php
            if (!empty($arrEditorUsers))
            {
                if (!empty($arrEditorUsers['group']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                    ploopi\db::get()->query(
                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrEditorUsers['group']).") ORDER BY label"
                    );

                    while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                }
                if (!empty($arrEditorUsers['user']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                    ploopi\db::get()->query(
                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrEditorUsers['user']).") ORDER BY lastname, firstname"
                    );

                    while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                }
            }
            else echo '<em>Aucune accréditation</em>';
            ?>
        </p>

        <?php
        if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_HEADING_BACK_EDITOR_MANAGE) && ploopi\acl::isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            ?>
            <div style="border:1px solid #c0c0c0;overflow:hidden;">
            <?php ploopi\validation::selectusers(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $heading->fields['id'], -1,_WEBEDIT_ACTION_HEADING_BACK_EDITOR, $intEditorHeadingId == $headingid ? 'Modifier la listes des rédacteurs :' : 'Définir une nouvelle liste de rédacteurs :'); ?>
            </div>
            <?php
        }
        ?>
    </fieldset>
</div>

<div style="clear:both; padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>Autorisations d'accès</strong> (utilisateurs qui peuvent accéder à une rubrique privée)</legend>
        <p class="ploopi_checkbox" style="padding: 0 0 0 2px;">
            <label for="heading_private">Rubrique privée (accès avec un compte utilisateur):</label>
            <?php
            if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ACCESS_MANAGE))
            {
                ?>
                <input type="checkbox" name="webedit_heading_private" id="webedit_heading_private" value="1" <?php if ($heading->fields['private']) echo 'checked="checked"'; ?> onchange="javascript:jQuery('#heading_private_form')[0].style.display = (this.checked) ? 'block' : 'none';"/>
                <?php
            }
            else
                echo ($heading->fields['private']) ? 'oui' : 'non';
            ?>
        </p>

        <div style="clear:both;padding: 4px 0px;display:<?php echo $heading->fields['private'] ? 'block' : 'none'; ?>;" id="heading_private_form">
            <p class="ploopi_checkbox" style="padding:0 0 0 2px;">
                <label for="heading_private">Toujours visible dans le menu :</label>
                <?php
                if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ACCESS_MANAGE))
                {
                    ?>
                    <input type="checkbox" name="webedit_heading_private_visible" id="webedit_heading_private_visible" value="1" <?php if ($heading->fields['private_visible']) echo 'checked="checked"'; ?> />
                    <?php
                }
                else
                    echo ($heading->fields['private_visible']) ? 'oui' : 'non';
                ?>
            </p>
            <p class="ploopi_va" style="padding:6px 2px 2px 2px;"><span>Autorisations d'accès </span><?php if ($intSharesHeadingId && $intSharesHeadingId != $headingid) echo "<em>&nbsp;héritées de &laquo;&nbsp;</em><a href=\"".ploopi\crypt::urlencode("admin.php?headingid={$intSharesHeadingId}")."\">{$headings['list'][$intSharesHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
                <?php
                if (!empty($arrSharesUsers))
                {
                    if (!empty($arrSharesUsers['group']))
                    {
                        $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                        ploopi\db::get()->query(
                            "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrSharesUsers['group']).") ORDER BY label"
                        );

                        while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['label'])."&nbsp;</span>";
                    }
                    if (!empty($arrSharesUsers['user']))
                    {
                        $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                        ploopi\db::get()->query(
                            "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrSharesUsers['user']).") ORDER BY lastname, firstname"
                        );

                        while ($row = ploopi\db::get()->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi\str::htmlentities($row['name'])."&nbsp;</span>";
                    }
                }
                else echo '<em>Aucune accréditation</em>';
                ?>
            </p>
            <?php
            if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ACCESS_MANAGE))
            {
                ?>
                <div style="border:1px solid #c0c0c0;overflow:hidden;">
                <?php
                    ploopi\share::selectusers(_WEBEDIT_OBJECT_HEADING, $heading->fields['id'], -1, ($intSharesHeadingId && $intSharesHeadingId == $headingid) ? 'Modifier la listes des autorisations d\'accès:' : 'Définir une nouvelle liste d\'autorisations d\'accès:');
                ?>
                </div>
                <?php
            }
            ?>
        </div>
    </fieldset>
</div>

<?php
if ($booIsAllowedEdit)
{
    ?>
    <div style="text-align:right;padding:4px;">
        <input class="flatbutton" type="reset" value="Annuler">
        <input class="flatbutton" type="submit" value="Enregistrer">
    </div>
    </form>
    <?php
}
?>

<div style="margin:0 4px 4px 4px;border-style:solid;border-width:1px 1px 0 1px;border-color:#c0c0c0;">
    <p class="ploopi_va" style="background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:4px 6px;overflow:auto;">
        <?php
        if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || $booIsEditor)
        {
            ?>
                <a style="float:right;text-decoration:none;" href="<?php echo ploopi\crypt::urlencode("admin.php?op=article_addnew"); ?>">&nbsp;Ajouter un article</a>
                <img style="float:right;border:0px;" src="./modules/webedit/img/doc_add.png">
            <?php
        }
        ?>
        <b>Liste des articles de la rubrique &laquo; <?php echo ploopi\str::htmlentities($heading->fields['label']); ?> &raquo;</b>
    </p>
    <?php
    $articles_columns = array();

    $articles_columns['auto']['titre'] = array('label' => 'Titre', 'options' => array('sort' => true));
    $articles_columns['right']['auteur'] = array('label' => 'Auteur', 'width' => '130', 'options' => array('sort' => true));
    $articles_columns['right']['misenligne'] = array('label' => 'Mise en ligne', 'width' => '140');
    $articles_columns['right']['vers'] = array('label' => 'Vers.', 'width' => '70', 'options' => array('sort' => true));
    $articles_columns['right']['date'] = array('label' => 'Date', 'width' => '80', 'options' => array('sort' => true));

    $articles_columns['left']['pos'] = array('label' => 'P.', 'width' => '55', 'options' => array('sort' => true, 'sort_flag' => SORT_NUMERIC));
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
            $ldate = (!empty($row['timestp'])) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '', 'time' => '');

            $timestp_local = (!empty($row['timestp'])) ? ploopi\date::timestamp2local($row['timestp']) : array('date' => '');
            $timestp_published_local = (!empty($row['timestp_published'])) ? ploopi\date::timestamp2local($row['timestp_published']) : array('date' => '');
            $timestp_unpublished_local = (!empty($row['timestp_unpublished'])) ? ploopi\date::timestamp2local($row['timestp_unpublished']) : array('date' => '');

            $published = (!empty($timestp_published_local['date'])) ? "à partir du {$timestp_published_local['date']}" : '';
            $published .= (!empty($timestp_unpublished_local['date'])) ? (empty($published) ? '' : '<br />')."jusqu'au {$timestp_unpublished_local['date']}" : '';

            $art_title = ($row['status'] == 'wait') ? "{$row['title']} *" : $row['title'];

            $articles_values[$c]['values']['date'] = array('label' => ploopi\str::htmlentities($timestp_local['date']), 'style' => '', 'sort_label' => $row['timestp']);
            $articles_values[$c]['values']['pos'] = array('label' => ploopi\str::htmlentities($row['position']), 'style' => '');
            $articles_values[$c]['values']['ref'] = array('label' => ploopi\str::htmlentities($row['reference']), 'style' => '');
            $articles_values[$c]['values']['titre'] = array('label' => "<img src=\"./modules/webedit/img/doc{$articles['list'][$row['id']]['new_version']}.png\"><span>{$art_title}</span>", 'style' => '');
            $articles_values[$c]['values']['vers'] = array('label' => ploopi\str::htmlentities($row['version']), 'style' => '');
            $articles_values[$c]['values']['misenligne'] = array('label' => $published, 'style' => '');
            $articles_values[$c]['values']['auteur'] = array('label' => ploopi\str::htmlentities($row['author']), 'style' => '');

            if (ploopi\acl::isadmin() || $booWfVal || $booIsEditor || ($_SESSION['ploopi']['userid'] == $row['id_user'] && $articles['list'][$row['id']]['online_id'] == ''))
            {
                $articles_values[$c]['values']['actions'] = array('label' =>  "<a style=\"display:block;float:right;\" title=\"Supprimer\" href=\"javascript:ploopi.confirmlink('".ploopi\crypt::urlencode("admin.php?op=article_delete&articleid={$row['id']}")."','Êtes-vous certain de vouloir supprimer l\'article &laquo; ".addslashes($row['title'])." &raquo; ?');\"><img style=\"border:0px;\" src=\"./modules/webedit/img/doc_del.png\"></a>", 'style' => '');
            }
            else $articles_values[$c]['values']['actions'] = array('label' => '&nbsp;', 'style' => '');

            $articles_values[$c]['description'] = ploopi\str::htmlentities($row['title']);
            $articles_values[$c]['link'] = ploopi\crypt::urlencode("admin.php?op=article_modify&articleid={$row['id']}");
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

    ploopi\skin::get()->display_array($articles_columns, $articles_values, 'webedit_articlelist', $options);
    ?>
</div>

<?php
if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_SUBSCRIBERS_MANAGE)) // Gestion des abonnés ?
{
    ?>
    <div style="margin:0 4px 4px 4px;border-style:solid;border-width:1px 1px 0 1px;border-color:#c0c0c0;">
        <p class="ploopi_va" style="background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:4px 6px;overflow:auto;">
            <b>Liste des abonnés frontoffice (anonymes) de la rubrique &laquo; <?php echo ploopi\str::htmlentities($heading->fields['label']); ?> &raquo;</b>
        </p>
        <?php
        $subscribers_columns = array();

        $subscribers_columns['auto']['email'] =
            array(
                'label' => 'Adresse email',
                'options' => array('sort' => true)
            );

        $subscribers_columns['actions_right']['actions'] =
            array(
                'label' => '&nbsp;',
                'width' => '22'
            );

        $subscribers_values = array();

        $sql = "SELECT * FROM ploopi_mod_webedit_heading_subscriber WHERE (id_heading = {$headingid} OR id_heading = 0) AND id_module = {$_SESSION['ploopi']['moduleid']}";
        ploopi\db::get()->query($sql);

        $c = 0;

        while ($row = ploopi\db::get()->fetchrow())
        {
            $subscribers_values[$c]['values']['email'] =
                array(
                    'label' => ploopi\str::htmlentities($row['email']).($row['id_heading'] == 0 ? ' <em>(tout le site)</em>' : '')
                );

            $subscribers_values[$c]['values']['actions'] =
                array(
                    'label' => "<img style=\"cursor:pointer;\" title=\"Supprimer cet abonné\" alt=\"Supprimer\" onclick=\"javascript:ploopi.confirmlink('".ploopi\crypt::urlencode("admin-light.php?ploopi_op=webedit_subscriber_delete&webedit_subscriber_email={$row['email']}&webedit_subscriber_id_heading={$row['id_heading']}")."','Êtes-vous certain de vouloir supprimer cet abonné ?');\" src=\"./modules/webedit/img/ico_trash.png\"></a>",
                    'style' => 'text-align:center;'
                );

            $subscribers_values[$c]['description'] = ploopi\str::htmlentities($row['email']);

            $c++;
        }

        ploopi\skin::get()->display_array($subscribers_columns, $subscribers_values, 'webedit_subscribers', $options = array('sortable' => true, 'orderby_default' => 'email'));
        ?>
    </div>
    <?php
}

$parents = explode(';', $heading->fields['parents']);
for ($i = 0; $i < sizeof($parents); $i++)
{
    if (ploopi\subscription::subscribed(_WEBEDIT_OBJECT_HEADING, $parents[$i]))
    {
        ?>
        <div style="padding:2px 4px;font-weight:bold;">
        Vous héritez de l'abonnement à &laquo; <a href="<?php echo ploopi\crypt::urlencode("admin.php?headingid={$parents[$i]}"); ?>"><?php echo ploopi\str::htmlentities($headings['list'][$parents[$i]]['label']); ?></a> &raquo;
        </div>
        <?php
    }
}

$arrAllowedActions = array(
    _WEBEDIT_ACTION_ARTICLE_EDIT,
    _WEBEDIT_ACTION_ARTICLE_PUBLISH,
    _WEBEDIT_ACTION_CATEGORY_EDIT
);


ploopi\subscription::display(_WEBEDIT_OBJECT_HEADING, $headingid, $arrAllowedActions, "à « {$heading->fields['label']} »");
?>
<div style="border-top:1px solid #c0c0c0;">
<?php ploopi\annotation::display(_WEBEDIT_OBJECT_HEADING, $headingid, $heading->fields['label']); ?>
</div>
