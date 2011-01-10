<?php
/*
    Copyright (c) 2007-2011 Ovensia
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
 * @author St�phane Escaich
 */

$objUser = new user();
$objUser->open($_SESSION['ploopi']['userid']);
$arrGroups = $objUser->getgroups(true);

// Recup�re les R�dacteurs
$intEditorHeadingId = 0;
$arrEditorUsers = array();
$booEditorHeadingIdIsRoot = true;
$arrEditor = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $headingid);
$intEditorHeadingId = $headingid;

/**
 * L'utilisateur connect� est-il r�dacteur ?
 */
$booIsAllowedEdit = $booIsEditor = false;
foreach($arrEditor as $value)
{
    if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booIsAllowedEdit = $booIsEditor = true;
    if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booIsAllowedEdit = $booIsEditor = true;

    $arrEditorUsers[$value['type_validation']][] = $value['id_validation'];
}

// Si l'utilisateur connect� n'est pas un "R�dacteur" on verif ses droits pour l'action _WEBEDIT_ACTION_CATEGORY_EDIT
if(!$booIsAllowedEdit) $booIsAllowedEdit = ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT);
?>

<p class="ploopi_va" style="background-color:#e0e0e0;padding:6px;border-bottom:1px solid #c0c0c0;">
    <img src="./modules/webedit/img/blocs.png">
    <span style="font-weight:bold;">Gestion des blocs</span>
</p>
<div id="webedit_heading_toolbar">
    <?php
    if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || $booIsAllowedEdit)
    {
        ?>
        <p class="ploopi_va" style="float:left;padding:6px;cursor:pointer;" title="Ajouter un article" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=bloc_addnew"); ?>';" >
            <img src="./modules/webedit/img/doc_add.png">
            <span>Ajouter un bloc</span>
        </p>
        <?php
    }
    ?>
</div>

<?php
if ($booIsAllowedEdit)
{
    ?>
    <form style="margin:0;" action="<?php echo ploopi_urlencode('admin.php?op=heading_save'); ?>" method="post">
    <?php
}

// r�cup�re les validateurs
$arrWfUsers = array();
$arrWf = ploopi_validation_get(_WEBEDIT_OBJECT_HEADING, $headingid);
$intWfHeadingId = $headingid;

/**
 * L'utilisateur connect� est-il validateur ?
 */
$booWfVal = false;
foreach($arrWf as $value)
{
    if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booWfVal = true;
    if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booWfVal = true;

    $arrWfUsers[$value['type_validation']][] = $value['id_validation'];
}
?>
<div style="clear:both;padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>Validateurs</strong> (utilisateurs qui peuvent publier)</legend>

        <p class="ploopi_va" style="padding:0 2px 2px 2px;"><span>Validateurs </span><?php if ($intWfHeadingId && $intWfHeadingId != $headingid) echo "<em>&nbsp;h�rit�es de &laquo;&nbsp;</em><a href=\"".ploopi_urlencode("admin.php?headingid={$intWfHeadingId}")."\">{$headings['list'][$intWfHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
            <?php
            if (!empty($arrWfUsers))
            {
                if (!empty($arrWfUsers['group']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                    $db->query(
                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrWfUsers['group']).") ORDER BY label"
                    );

                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;{$row['label']}&nbsp;</span>";
                }
                if (!empty($arrWfUsers['user']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                    $db->query(
                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrWfUsers['user']).") ORDER BY lastname, firstname"
                    );

                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;{$row['name']}&nbsp;</span>";
                }
            }
            else echo '<em>Aucune accr�ditation</em>';
            ?>
        </p>

        <?php
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_WORKFLOW_MANAGE) && ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            ?>
            <div style="border:1px solid #c0c0c0;overflow:hidden;">
            <?php ploopi_validation_selectusers(_WEBEDIT_OBJECT_HEADING, $headingid, -1, _WEBEDIT_ACTION_ARTICLE_PUBLISH, $intWfHeadingId == $headingid ? 'Modifier la listes des validateurs :' : 'D�finir une nouvelle liste de validateurs :'); ?>
            </div>
            <?php
        }
        ?>
    </fieldset>
</div>

<div style="clear:both;padding:4px;">
    <fieldset class="fieldset" style="padding:6px;">
        <legend><strong>R�dacteurs</strong> (utilisateurs qui peuvent g�rer cette branche)</legend>

        <p class="ploopi_va" style="padding:0 2px 2px 2px;"><span>R�dacteurs </span><?php if ($intEditorHeadingId && $intEditorHeadingId != $headingid) echo "<em>&nbsp;h�rit�es de &laquo;&nbsp;</em><a href=\"".ploopi_urlencode("admin.php?headingid={$intEditorHeadingId}")."\">{$headings['list'][$intEditorHeadingId]['label']}</a><em>&nbsp;&raquo;</em>"; ?><span>:</span>
            <?php
            if (!empty($arrEditorUsers))
            {
                if (!empty($arrEditorUsers['group']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                    $db->query(
                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrEditorUsers['group']).") ORDER BY label"
                    );

                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;{$row['label']}&nbsp;</span>";
                }
                if (!empty($arrEditorUsers['user']))
                {
                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                    $db->query(
                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrEditorUsers['user']).") ORDER BY lastname, firstname"
                    );

                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;{$row['name']}&nbsp;</span>";
                }
            }
            else echo '<em>Aucune accr�ditation</em>';
            ?>
        </p>

        <?php
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_HEADING_BACK_EDITOR_MANAGE) && ploopi_isactionallowed(_WEBEDIT_ACTION_CATEGORY_EDIT))
        {
            ?>
            <div style="border:1px solid #c0c0c0;overflow:hidden;">
            <?php ploopi_validation_selectusers(_WEBEDIT_OBJECT_HEADING_BACK_EDITOR, $headingid, -1,_WEBEDIT_ACTION_HEADING_BACK_EDITOR, $intEditorHeadingId == $headingid ? 'Modifier la listes des r�dacteurs :' : 'D�finir une nouvelle liste de r�dacteurs :'); ?>
            </div>
            <?php
        }
        ?>
    </fieldset>
</div>

<?
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
        if (ploopi_isactionallowed(_WEBEDIT_ACTION_ARTICLE_EDIT) || $booIsEditor)
        {
            ?>
                <a style="float:right;text-decoration:none;" href="<?php echo ploopi_urlencode("admin.php?op=bloc_addnew"); ?>">&nbsp;Ajouter un bloc</a>
                <img style="float:right;border:0px;" src="./modules/webedit/img/doc_add.png">
            <?php
        }
        ?>
        <b>Liste des blocs</b>
    </p>
    <?php
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

            $published = (!empty($timestp_published_local['date'])) ? "� partir du {$timestp_published_local['date']}" : '';
            $published .= (!empty($timestp_unpublished_local['date'])) ? (empty($published) ? '' : '<br />')."jusqu'au {$timestp_unpublished_local['date']}" : '';

            $art_title = ($row['status'] == 'wait') ? "{$row['title']} *" : $row['title'];

            $articles_values[$c]['values']['date'] = array('label' => $timestp_local['date'], 'style' => '', 'sort_label' => $row['timestp']);
            $articles_values[$c]['values']['pos'] = array('label' => $row['position'], 'style' => '');
            $articles_values[$c]['values']['ref'] = array('label' => $row['reference'], 'style' => '');
            $articles_values[$c]['values']['titre'] = array('label' => "<img src=\"./modules/webedit/img/doc{$articles['list'][$row['id']]['new_version']}.png\"><span>{$art_title}</span>", 'style' => '');
            $articles_values[$c]['values']['vers'] = array('label' => $row['version'], 'style' => '');
            $articles_values[$c]['values']['misenligne'] = array('label' => $published, 'style' => '');
            $articles_values[$c]['values']['auteur'] = array('label' => $row['author'], 'style' => '');

            if (ploopi_isadmin() || $booWfVal || $booIsEditor || ($_SESSION['ploopi']['userid'] == $row['id_user'] && $articles['list'][$row['id']]['online_id'] == ''))
            {
                $articles_values[$c]['values']['actions'] = array('label' =>  "<a style=\"display:block;float:right;\" title=\"Supprimer\" href=\"javascript:ploopi_confirmlink('admin.php?op=article_delete&articleid={$row['id']}','�tes-vous certain de vouloir supprimer l\'article &laquo; ".addslashes($row['title'])." &raquo; ?');\"><img style=\"border:0px;\" src=\"./modules/webedit/img/doc_del.png\"></a>", 'style' => '');
            }
            else $articles_values[$c]['values']['actions'] = array('label' => '&nbsp;', 'style' => '');

            $articles_values[$c]['description'] = $row['title'];
            $articles_values[$c]['link'] = ploopi_urlencode("admin.php?op=article_modify&articleid={$row['id']}");
            $articles_values[$c]['style'] = '';

            $c++;
        }
    }
    
    $options = array('sortable' => true, 'orderby_default' => 'pos');

    $skin->display_array($articles_columns, $articles_values, 'webedit_articlelist', $options);
    ?>
</div>

<?php
$arrAllowedActions = array(
    _WEBEDIT_ACTION_ARTICLE_EDIT,
    _WEBEDIT_ACTION_ARTICLE_PUBLISH
);

ploopi_subscription(_WEBEDIT_OBJECT_HEADING, $headingid, $arrAllowedActions, "� &laquo; Blocs &raquo;");
?>
<div style="border-top:1px solid #c0c0c0;">
<?php ploopi_annotation(_WEBEDIT_OBJECT_HEADING, $headingid, 'Blocs'); ?>
</div>
