<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Mon espace › Mes notes, gabarits de génération 4 : étiquettes à gauche, notes de
 * l'utilisateur à droite (objet annoté, date, texte, étiquettes), dix par page, chacune
 * modifiable et supprimable (traitement dans public_annotations.php).
 *
 * Mêmes requêtes que public_annotations.php, qui inclut ce fichier sous ploopi4.
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$db = ploopi\db::get();
$intUserId = (int) $_SESSION['ploopi']['userid'];
$intTag = (!empty($_GET['idtag']) && is_numeric($_GET['idtag'])) ? (int) $_GET['idtag'] : 0;

// Étiquettes de l'utilisateur
$arrTags = array();
$rs = $db->query("
    SELECT      t.*, COUNT(*) AS c
    FROM        ploopi_tag t
    INNER JOIN  ploopi_annotation_tag at ON at.id_tag = t.id
    WHERE       t.id_user = {$intUserId}
    GROUP BY    t.id
    ORDER BY    t.tag
");
while ($row = $db->fetchrow($rs)) $arrTags[$row['id']] = $row;

// Notes, éventuellement filtrées par étiquette
$rs = $db->query("
    SELECT      a.*,
                t.id AS tagid,
                t.tag,
                m.label AS module_name,
                mt.label AS module_type,
                o.label AS object_name,
                o.script
    FROM        ploopi_annotation a
    ".($intTag ? "INNER JOIN ploopi_annotation_tag at2 ON at2.id_annotation = a.id AND at2.id_tag = {$intTag}" : '')."
    LEFT JOIN   ploopi_annotation_tag at ON at.id_annotation = a.id
    LEFT JOIN   ploopi_tag t ON t.id = at.id_tag
    LEFT JOIN   ploopi_module m ON a.id_module = m.id
    LEFT JOIN   ploopi_module_type mt ON mt.id = m.id_module_type
    LEFT JOIN   ploopi_mb_object o ON a.id_object = o.id AND m.id_module_type = o.id_module_type
    WHERE       a.id_user = {$intUserId}
    ORDER BY    a.date_annotation DESC
");

$arrNotes = array();
while ($row = $db->fetchrow($rs))
{
    if (!isset($arrNotes[$row['id']])) $arrNotes[$row['id']] = $row + array('tags' => array());
    if (!is_null($row['tag'])) $arrNotes[$row['id']]['tags'][$row['tagid']] = $row['tag'];
}

$intPerPage = 10;
$intPages = max(1, (int) ceil(sizeof($arrNotes) / $intPerPage));
$intPage = (!empty($_GET['page']) && is_numeric($_GET['page'])) ? min(max(1, (int) $_GET['page']), $intPages) : 1;

echo ui::pagehead(_PLOOPI_LABEL_MYANNOTATIONS, _SYSTEM_MYS_NOTES_SUBTITLE);
?>
<div class="p4-aside-layout p4-ui">
    <?php echo ui::card_open(_SYSTEM_MYS_TAGS, 'list-details', array(), array('class' => 'p4-tagcard')); ?>
        <div class="p4-tagcloud">
            <a class="p4-badge<?php if (!$intTag) echo ' p4-badge-brand'; ?>" href="<?php echo ploopi\crypt::urlencode('admin.php?op=annotation'); ?>"><?php echo ui::e(_SYSTEM_MYS_ALLNOTES); ?></a>
            <?php
            foreach($arrTags as $intId => $arrTag)
            {
                ?>
                <a class="p4-badge<?php if ($intTag == $intId) echo ' p4-badge-brand'; ?>" href="<?php echo ploopi\crypt::urlencode("admin.php?op=annotation&idtag={$intId}"); ?>" title="<?php echo ui::e(str_replace('<NB>', $arrTag['c'], _SYSTEM_MYS_TAG_USED)); ?>"><?php echo ui::e($arrTag['tag']); ?><span class="p4-count p4-count-muted"><?php echo (int) $arrTag['c']; ?></span></a>
                <?php
            }
            if (empty($arrTags)) echo '<p class="p4-text-xs p4-muted">'.ui::e(_SYSTEM_MYS_NOTAGS).'</p>';
            ?>
        </div>
    <?php echo ui::card_close(); ?>

    <div class="p4-stack">
        <?php
        if (empty($arrNotes))
        {
            echo ui::card_open();
            echo ui::emptystate('notes', _SYSTEM_MYS_NONOTES);
            echo ui::card_close();
        }
        else
        {
            foreach(array_slice($arrNotes, ($intPage - 1) * $intPerPage, $intPerPage, true) as $arrNote)
            {
                $strScript = str_replace(array('<IDRECORD>', '<IDMODULE>', '<IDWORKSPACE>'), array($arrNote['id_record'], $arrNote['id_module'], $arrNote['id_workspace']), (string) $arrNote['script']);
                $strUrl = ploopi\crypt::urlencode("admin.php?ploopi_mainmenu=1&{$strScript}");
                $arrDate = ploopi\date::timestamp2local($arrNote['date_annotation']);
                ?>
                <article class="p4-card p4-note">
                    <div class="p4-card-body">
                        <div class="p4-note-head">
                            <span class="p4-card-icon"><?php echo ui::icon($arrNote['module_type'] ? ploopi\skin::get()->moduleicon($arrNote['module_type']) : 'notes'); ?></span>
                            <div class="p4-grow">
                                <h3><a href="<?php echo ui::e($strUrl); ?>"><?php echo ui::e($arrNote['object_label'] != '' ? $arrNote['object_label'] : _SYSTEM_MYS_NOTE_NOLABEL); ?></a></h3>
                                <p class="p4-text-xs p4-muted"><?php echo ui::e(trim("{$arrNote['module_name']} · {$arrNote['object_name']}", ' ·')); ?></p>
                            </div>
                            <time class="p4-text-xs p4-muted"><?php echo ui::e("{$arrDate['date']} ".substr($arrDate['time'], 0, 5)); ?></time>
                        </div>
                        <div class="p4-note-content"><?php echo ploopi\str::make_links(ploopi\str::nl2br(ui::e($arrNote['content']))); ?></div>
                        <?php
                        if (!empty($arrNote['tags']) || $arrNote['private'])
                        {
                            echo '<div class="p4-chips">';
                            if ($arrNote['private']) echo ui::badge(_SYSTEM_MYS_NOTE_PRIVATE, 'warning');
                            foreach($arrNote['tags'] as $intId => $strTag) echo '<a class="p4-badge" href="'.ui::e(ploopi\crypt::urlencode("admin.php?op=annotation&idtag={$intId}")).'">'.ui::e($strTag).'</a>';
                            echo '</div>';
                        }

                        // Modifier (formulaire replié, sans script) et supprimer : notes de l'utilisateur seul
                        $intNote = (int) $arrNote['id'];
                        $strAction = ploopi\crypt::urlencode('admin.php?op=annotation');
                        $strRetour = '<input type="hidden" name="annotation_id" value="'.$intNote.'" /><input type="hidden" name="idtag" value="'.$intTag.'" /><input type="hidden" name="page" value="'.$intPage.'" />';
                        ?>
                        <div class="p4-row p4-wrap p4-note-actions">
                            <details class="p4-note-edit">
                                <summary class="p4-btn p4-btn-ghost p4-btn-sm"><?php echo ui::icon('pencil', 'p4-icon-sm').ui::e(_SYSTEM_LABEL_MODIFY); ?></summary>
                                <form class="p4-form p4-note-form" action="<?php echo $strAction; ?>" method="post" data-p4-note-form="<?php echo $intNote; ?>">
                                    <input type="hidden" name="annotation_action" value="save" /><?php echo $strRetour; ?>
                                    <?php
                                    echo ui::field(_SYSTEM_MYS_NOTE_TEXT, ui::textarea('annotation_content', $arrNote['content'], array('id' => "annotation_content_{$intNote}", 'rows' => 4, 'required' => true)), array('for' => "annotation_content_{$intNote}", 'width' => 'c12', 'required' => true));
                                    echo ui::field(_SYSTEM_MYS_NOTE_TAGS, ui::input('annotation_tags', implode(' ', $arrNote['tags']), array('id' => "annotation_tags_{$intNote}", 'autocomplete' => 'off')), array('for' => "annotation_tags_{$intNote}", 'width' => 'c12', 'hint' => ui::e(_SYSTEM_MYS_NOTE_TAGS_HINT)));
                                    echo ui::toggle('annotation_private', $arrNote['private'], _SYSTEM_MYS_NOTE_PRIVATE, ui::e(_SYSTEM_MYS_NOTE_PRIVATE_HINT), array('id' => "annotation_private_{$intNote}"));
                                    ?>
                                    <div class="p4-row p4-wrap"><span class="p4-grow"></span><?php echo ui::button(array('label' => _SYSTEM_MYS_NOTE_SAVE, 'icon' => 'check', 'class' => 'primary sm', 'type' => 'submit')); ?></div>
                                </form>
                            </details>
                            <form action="<?php echo $strAction; ?>" method="post">
                                <input type="hidden" name="annotation_action" value="delete" /><?php echo $strRetour; ?>
                                <?php echo ui::button(array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'type' => 'submit', 'confirm' => _SYSTEM_MYS_NOTE_CONFIRMDELETE, 'attrs' => array('data-p4-note-delete' => $intNote))); ?>
                            </form>
                        </div>
                    </div>
                </article>
                <?php
            }

            if ($intPages > 1)
            {
                echo '<nav class="p4-pager" aria-label="'.ui::e(_SYSTEM_MSG_PAGES).'">';
                for ($intP = 1; $intP <= $intPages; $intP++) echo '<a class="p4-btn p4-btn-sm'.($intP == $intPage ? ' p4-active' : '').'" href="'.ui::e(ploopi\crypt::urlencode("admin.php?op=annotation&page={$intP}&idtag={$intTag}")).'"'.($intP == $intPage ? ' aria-current="page"' : '').'>'.$intP.'</a>';
                echo '</nav>';
            }
        }
        ?>
    </div>
</div>
