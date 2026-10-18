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

namespace ploopi;

use ploopi;

/**
 * Gestion des annotations
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class annotation extends data_object
{
    /**
     * Étiquettes, séparées par des espaces ou des virgules. null : save() ne touche pas
     * aux étiquettes ; une chaîne (vide comprise) les remplace.
     *
     * @var string|null
     */
    public $tags = null;

    /**
     * Constructeur de la classe
     */

    public function __construct()
    {
        parent::__construct('ploopi_annotation', 'id');
    }

    /**
     * Enregistre l'annotation et les tags associés
     *
     * @return int identifiant de l'annotation
     */

    public function save()
    {
        $db = db::get();

        $this->fields['id_element'] = md5(sprintf("%04d%04d%s", $this->fields['id_module'], $this->fields['id_object'], $this->fields['id_record']));

        $id_annotation = parent::save();

        if (is_null($this->tags)) return $id_annotation;

        $tags = array_unique(preg_split('/[,\s]+/u', (string) $this->tags, -1, PREG_SPLIT_NO_EMPTY));
        $arrIdTags = array();

        foreach($tags as $tag)
        {
            $tag = trim($tag);

            $tag_clean = preg_replace("/[^a-zA-Z0-9]/","",str::convertaccents($tag));

            $select = "SELECT id FROM ploopi_tag WHERE tag = '".$db->addslashes($tag)."' AND id_user = {$this->fields['id_user']}";
            $rs = $db->query($select);
            if (!($row = $db->fetchrow($rs)))
            {
                $objtag = new tag();
                $objtag->fields['tag'] = $tag;
                $objtag->fields['tag_clean'] = $tag_clean;
                $objtag->fields['id_user'] = $this->fields['id_user'];
                $id_tag = $objtag->save();
            }
            else $id_tag = $row['id'];

            $arrIdTags[] = (int) $id_tag;

            $annotation_tag = new annotation_tag();
            if (!$annotation_tag->open($id_annotation, $id_tag))
            {
                $annotation_tag->fields['id_tag'] = $id_tag;
                $annotation_tag->fields['id_annotation'] = $id_annotation;
                $annotation_tag->save();
            }
        }

        // Étiquettes retirées (modification)
        $db->query("DELETE FROM ploopi_annotation_tag WHERE id_annotation = ".((int) $id_annotation).(empty($arrIdTags) ? '' : ' AND id_tag NOT IN ('.implode(',', $arrIdTags).')'));

        return($id_annotation);
    }

    /**
     * Supprime l'annotation
     */

    public function delete()
    {
        $db = db::get();

        $select =   "
                    SELECT  *
                    FROM    ploopi_annotation_tag
                    WHERE   id_annotation = {$this->fields['id']}
                    ";

        $rs = $db->query($select);
        while ($row = $db->fetchrow($rs))
        {
            $annotation_tag = new annotation_tag();
            $annotation_tag->open($this->fields['id'], $row['id_tag']);
            $annotation_tag->delete();
        }

        parent::delete();
    }


    /**
     * Renvoie le nombre d'annotation sur un objet
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_user identifiant de l'utilisateur
     * @param int $id_workspace identifiant de l'espace
     * @param int $id_module identifiant du module
     * @return int nombre d'annotation
     */

    public static function getnb($id_object, $id_record, $id_user = -1, $id_workspace = -1, $id_module = -1)
    {
        $db = db::get();

        if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];
        if ($id_workspace == -1) $id_workspace = $_SESSION['ploopi']['workspaceid'];
        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

        $db->query("
            SELECT      count(*) as c
            FROM        ploopi_annotation a
            WHERE       a.id_record = '".$db->addslashes($id_record)."'
            AND         a.id_object = {$id_object}
            AND         a.id_module = {$id_module}
            AND         (a.private = 0
            OR          (a.private = 1 AND a.id_user = {$id_user}))
        ");

        if ($fields = $db->fetchrow()) $nbanno = $fields['c'];
        else $nbanno = 0;

        return($nbanno);
    }

    /**
     * Insère le bloc d'annotation pour un enregistrement d'un objet
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param string $object_label libellé de l'objet
     */

    public static function display($id_object, $id_record, $object_label = '')
    {
        global $ploopi_annotation_private;

        // Enregistrement pas encore créé (formulaire d'ajout) : rien à annoter
        if ((string) $id_record === '') return;

        // generate annotation id
        $id_annotation = md5("{$_SESSION['ploopi']['moduleid']}_{$id_object}_".addslashes((string) $id_record));

        $_SESSION['annotation'][$id_annotation] =
            array(
                'id_object' => $id_object,
                'id_record' => $id_record,
                'object_label' => $object_label
            );
        ?>
        <div id="ploopiannotation_<?php echo $id_annotation; ?>">
        <?php annotation::display_refresh($id_annotation); ?>
        </div>
        <?php
    }

    /**
     * Annotations visibles d'un enregistrement (publiques, et privées de l'utilisateur), avec
     * leurs étiquettes, des plus récentes aux plus anciennes
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @return array [id => ['fields' => …, 'tags' => [id_tag => tag]]]
     */

    private static function getlist($id_object, $id_record)
    {
        $db = db::get();

        $select =   "
                    SELECT      a.*,
                                u.firstname,
                                u.lastname,
                                u.login,
                                t.id as idtag,
                                t.tag
                    FROM        ploopi_annotation a

                    INNER JOIN  ploopi_user u ON a.id_user = u.id

                    LEFT JOIN   ploopi_annotation_tag at ON a.id = at.id_annotation
                    LEFT JOIN   ploopi_tag t ON t.id = at.id_tag

                    WHERE       a.id_record = '".$db->addslashes($id_record)."'
                    AND         a.id_object = ".((int) $id_object)."
                    AND         a.id_module = ".((int) $_SESSION['ploopi']['moduleid'])."
                    AND         (a.private = 0
                    OR          (a.private = 1 AND a.id_user = ".((int) $_SESSION['ploopi']['userid'])."))
                    ORDER BY    a.date_annotation DESC, a.id DESC
                    ";

        $rs_anno = $db->query($select);

        $array_anno = array();
        while ($fields = $db->fetchrow($rs_anno))
        {
            $array_anno[$fields['id']]['fields'] = $fields;
            if (!is_null($fields['tag'])) $array_anno[$fields['id']]['tags'][$fields['idtag']] = $fields['tag'];
        }

        return $array_anno;
    }

    /**
     * Bloc d'annotations du gabarit ploopi4 : liste en fil (auteur, date, étiquettes,
     * confidentialité), formulaire d'ajout ; aucun gestionnaire en ligne
     * (attributs data-p4-anno-*, délégation dans js/annotation.js)
     *
     * @param string $id_annotation identifiant du bloc
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $nbanno nombre d'annotations visibles
     * @param boolean $booShow liste dépliée
     */

    private static function display_p4($id_annotation, $id_object, $id_record, $nbanno, $booShow)
    {
        $skin = skin::get();
        $strId = str::htmlentities($id_annotation);
        $array_anno = self::getlist($id_object, $id_record);
        ?>
        <section class="p4-anno" data-p4-anno="<?php echo $strId; ?>">
            <button type="button" class="p4-anno-toggle" id="annotation_count_<?php echo $strId; ?>" data-p4-anno-toggle aria-expanded="<?php echo $booShow ? 'true' : 'false'; ?>" aria-controls="annotation_list_<?php echo $strId; ?>">
                <?php echo $skin->icon('notes'); ?>
                <span class="p4-anno-title">Annotations</span>
                <span class="p4-badge<?php echo $nbanno ? ' p4-badge-brand' : ''; ?>"><?php echo (int) $nbanno; ?></span>
                <?php echo $skin->icon('chevron-down', 'p4-anno-chevron'); ?>
            </button>

            <div class="p4-anno-body" id="annotation_list_<?php echo $strId; ?>"<?php if (!$booShow) echo ' hidden'; ?>>
                <?php
                if (empty($array_anno))
                {
                    ?>
                    <p class="p4-anno-empty">Aucune annotation pour le moment.</p>
                    <?php
                }
                else
                {
                    ?>
                    <ol class="p4-anno-list">
                    <?php
                    foreach($array_anno as $anno)
                    {
                        $fields = $anno['fields'];
                        $ldate = date::timestamp2local($fields['date_annotation']);
                        $strAuteur = trim("{$fields['firstname']} {$fields['lastname']}");
                        if ($strAuteur == '') $strAuteur = $fields['login'];
                        $strInitiales = mb_strtoupper(mb_substr($fields['firstname'], 0, 1).mb_substr($fields['lastname'], 0, 1));
                        if ($strInitiales == '') $strInitiales = mb_strtoupper(mb_substr($fields['login'], 0, 2));
                        ?>
                        <li class="p4-anno-item">
                            <span class="p4-avatar" aria-hidden="true"><?php echo str::htmlentities($strInitiales); ?></span>
                            <div class="p4-anno-main">
                                <div class="p4-anno-meta">
                                    <strong><?php echo str::htmlentities($strAuteur); ?></strong>
                                    <span class="p4-anno-date"><?php echo str::htmlentities("{$ldate['date']} à ".substr($ldate['time'], 0, 5)); ?></span>
                                    <?php if ($fields['private']) { ?><span class="p4-badge p4-badge-warning"><?php echo $skin->icon('lock'); ?>Privée</span><?php } ?>
                                </div>
                                <div class="p4-anno-text"><?php echo str::make_links(str::nl2br(str::htmlentities($fields['content']))); ?></div>
                                <?php
                                if (!empty($anno['tags']))
                                {
                                    ?>
                                    <div class="p4-anno-tags">
                                        <?php foreach($anno['tags'] as $idtag => $tag) { ?>
                                        <button type="button" class="p4-tag" data-p4-anno-tag="<?php echo (int) $idtag; ?>" title="Autres annotations portant cette étiquette">#<?php echo str::htmlentities($tag); ?></button>
                                        <?php } ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            if ($fields['id_user'] == $_SESSION['ploopi']['userid'])
                            {
                                ?>
                                <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon p4-btn-sm p4-anno-delete" data-p4-anno-delete="<?php echo (int) $fields['id']; ?>" title="Supprimer cette annotation" aria-label="Supprimer cette annotation"><?php echo $skin->icon('trash'); ?></button>
                                <?php
                            }
                            ?>
                        </li>
                        <?php
                    }
                    ?>
                    </ol>
                    <?php
                }
                ?>

                <form class="p4-anno-form" action="" method="post" id="form_annotation_<?php echo $strId; ?>" target="form_annotation_target_<?php echo $strId; ?>" data-p4-anno-form novalidate>
                    <input type="hidden" name="ploopi_op" value="annotation_save">
                    <input type="hidden" name="id_annotation" value="<?php echo $strId; ?>">

                    <div class="p4-field">
                        <label for="ploopi_annotation_content_<?php echo $strId; ?>">Nouvelle annotation</label>
                        <textarea class="p4-input" rows="3" id="ploopi_annotation_content_<?php echo $strId; ?>" name="ploopi_annotation_content" placeholder="Votre remarque…" required></textarea>
                    </div>

                    <div class="p4-anno-form-row">
                        <div class="p4-field">
                            <label for="ploopi_annotationtags_<?php echo $strId; ?>">Étiquettes <small>(facultatives, séparées par des espaces)</small></label>
                            <input class="p4-input" type="text" name="ploopi_annotationtags" id="ploopi_annotationtags_<?php echo $strId; ?>" data-p4-anno-tags autocomplete="off">
                            <div class="p4-anno-suggest" id="tagsfound_<?php echo $strId; ?>"></div>
                        </div>
                        <label class="p4-check p4-anno-private">
                            <input type="checkbox" name="ploopi_annotation_private" value="1">
                            <span><?php echo $skin->icon('lock'); ?>Privée<small>visible par vous seul</small></span>
                        </label>
                    </div>

                    <p class="p4-anno-error" role="alert" hidden>Saisissez le texte de l'annotation.</p>

                    <div class="p4-anno-actions">
                        <button type="reset" class="p4-btn"><?php echo _PLOOPI_CANCEL; ?></button>
                        <button type="submit" class="p4-btn p4-btn-primary"><?php echo $skin->icon('plus'); ?><span>Ajouter</span></button>
                    </div>
                </form>
            </div>
            <iframe name="form_annotation_target_<?php echo $strId; ?>" title="Enregistrement de l'annotation" hidden></iframe>
        </section>
        <?php
    }

    /**
     * Rafraichit le bloc d'annotation pour un enregistrement d'un objet
     *
     * @param string $id_annotation identifiant du bloc d'annotation
     */

    public static function display_refresh($id_annotation)
    {
        $db = db::get();

        $id_object = $_SESSION['annotation'][$id_annotation]['id_object'];
        $id_record = $_SESSION['annotation'][$id_annotation]['id_record'];

        $select =   "
                    SELECT      count(*) as c
                    FROM        ploopi_annotation a
                    WHERE       a.id_record = '".$db->addslashes($id_record)."'
                    AND         a.id_object = {$id_object}
                    AND         a.id_module = {$_SESSION['ploopi']['moduleid']}
                    AND         (a.private = 0
                    OR          (a.private = 1 AND a.id_user = {$_SESSION['ploopi']['userid']}))
                    ";
        $rs_anno = $db->query($select);

        if ($fields = $db->fetchrow($rs_anno)) $nbanno = $fields['c'];
        else $nbanno = 0;

        $annotation_show = (isset($_SESSION['ploopi']['annotation']['show'][$id_annotation]));

        // Gabarit ploopi4 : bloc refondu (composants p4-*, comportement dans js/annotation.js)
        if (skin::get()->version() >= 4)
        {
            self::display_p4($id_annotation, $id_object, $id_record, $nbanno, $annotation_show);
            return;
        }

        ?>
        <a name="annotation_<?php echo $id_annotation; ?>" style="display:none;"></a>
        <div style="overflow:hidden;">
            <a id="annotation_count_<?php echo $id_annotation; ?>" class="ploopi_annotation_viewlist" href="javascript:void(0);" onclick="javascript:ploopi.getelem('annotation_list_<?php echo $id_annotation; ?>').style.display=(ploopi.getelem('annotation_list_<?php echo $id_annotation; ?>').style.display=='block') ? 'none' : 'block'; ploopi.xhr.send('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_show&object_id=<?php echo $id_annotation; ?>');">
                <img border="0" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/annotation.png">
                <span><?php echo $nbanno; ?> annotation<?php echo ($nbanno>1) ? 's' : ''; ?></span>
            </a>

            <div style="display:<?php echo ($annotation_show) ? 'block' : 'none'; ?>;" id="annotation_list_<?php echo $id_annotation; ?>">
                <?php
                $array_anno = self::getlist($id_object, $id_record);

                foreach($array_anno as $anno)
                {

                    $fields = $anno['fields'];

                    $ldate = ploopi\date::timestamp2local($fields['date_annotation']);
                    $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;

                    $private = '';
                    if ($fields['private']) $private = '<div style="float:right;font-weight:bold;color:#a60000;">[ Privé ]</div>';

                    ?>
                    <div class="ploopi_annotation_row_<?php echo $numrow; ?>">
                        <div style="padding:2px 4px;">
                            <?php echo $private; ?>
                            <div style="float:right;padding:0 4px;">par <strong><?php echo ploopi\str::htmlentities("{$fields['firstname']} {$fields['lastname']}"); ?></strong> le <?php echo $ldate['date']; ?> à <?php echo $ldate['time']; ?></div>
                            <?php
                            if (isset($anno['tags']) && is_array($anno['tags']))
                            {
                                echo "<b>tags :</b>";
                                foreach($anno['tags'] as $idtag => $tag)
                                {
                                    ?>
                                    <a href="javascript:void(0);" onclick="javascript:ploopi.popup.show('','400',event,'click');ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_taghistory&id_tag=<?php echo $idtag; ?>', 'ploopi_popup');return false;"><?php echo ploopi\str::htmlentities($tag); ?></a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div style="clear:both;padding:2px 4px;">
                            <?php echo ploopi\str::make_links(ploopi\str::nl2br(ploopi\str::htmlentities($fields['content']))); ?>
                        </div>
                        <div style="clear:both;">
                            <?php
                            if ($fields['id_user'] == $_SESSION['ploopi']['userid'])
                            {
                                ?>
                                <div style="float:right;padding:2px 4px;">
                                    <a href="javascript:ploopi.annotations.remove('<?php echo $id_annotation; ?>', '<?php echo $fields['id']; ?>');">supprimer</a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }

                $id_module_type = (isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'] : 0;

                $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;
                ?>
                <div class="ploopi_annotation_row_<?php echo $numrow; ?>">
                    <form action="" method="post" id="form_annotation_<?php echo $id_annotation; ?>" target="form_annotation_target_<?php echo $id_annotation; ?>" onsubmit="return ploopi.annotations.validate(this);">
                    <input type="hidden" name="ploopi_op" value="annotation_save">
                    <input type="hidden" name="id_annotation" value="<?php echo $id_annotation; ?>">

                    <div class="ploopi_annotation_titleform">Ajout d'une Annotation <?php echo (isset($ploopi_annotation_private)) ? 'privée' : ''; ?></div>
                    <div style="padding:2px 4px;"><input type="checkbox" name="ploopi_annotation_private" value="1">Privée (visible par vous uniquement)</div>
                    <div style="padding:2px 4px;">Tags:</div>
                    <div style="padding:2px 4px;"><input type="text" class="text" style="width:99%;" name="ploopi_annotationtags" id="ploopi_annotationtags_<?php echo $id_annotation; ?>" autocomplete="off"></div>
                    <div style="padding:2px 4px;" id="tagsfound_<?php echo $id_annotation; ?>"></div>
                    <div style="padding:2px 4px;">Commentaire:</div>
                    <div style="padding:2px 4px;"><textarea class="text" style="width:99%;" rows="4" name="ploopi_annotation_content"></textarea></div>

                    <div style="padding:2px 4px;text-align:right;">
                        <input type="button" onclick="ploopi.getelem('form_annotation_<?php echo $id_annotation; ?>').ploopi_op.value=''; ploopi.getelem('form_annotation_<?php echo $id_annotation; ?>').submit()" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>">
                        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <iframe name="form_annotation_target_<?php echo $id_annotation; ?>" src="./img/blank.gif" style="display:none;"></iframe>

        <script type="text/javascript">
            ploopi.annotations.tag_init('<?php echo $id_annotation; ?>');
        </script>
        <?php
    }


}
