<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès à la table ploopi_annotation
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class annotation extends data_object
{

    /**
     * Constructeur de la classe
     *
     * @return annotation
     */

    public function __construct()
    {
        parent::__construct('ploopi_annotation','id');
    }

    /**
     * Enregistre l'annotation et les tags associés
     *
     * @return int identifiant de l'annotation
     */

    public function save()
    {
        $db = loader::getdb();

        $this->fields['id_element'] = md5(sprintf("%04d%04d%s", $this->fields['id_module'], $this->fields['id_object'], $this->fields['id_record']));

        $id_annotation = parent::save();

        $tags = array_unique(preg_split('/(,)|( )/', $this->tags, -1, PREG_SPLIT_NO_EMPTY));

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

            $annotation_tag = new annotation_tag();
            if (!$annotation_tag->open($id_annotation, $id_tag))
            {
                $annotation_tag->fields['id_tag'] = $id_tag;
                $annotation_tag->fields['id_annotation'] = $id_annotation;
                $annotation_tag->save();
            }
        }

        return($id_annotation);
    }

    /**
     * Supprime l'annotation
     */

    public function delete()
    {
        $db = loader::getdb();

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
        $db = loader::getdb();

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

        // generate annotation id
        $id_annotation = md5("{$_SESSION['ploopi']['moduleid']}_{$id_object}_".addslashes($id_record));

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
     * Rafraichit le bloc d'annotation pour un enregistrement d'un objet
     *
     * @param string $id_annotation identifiant du bloc d'annotation
     */

    public static function display_refresh($id_annotation)
    {
        $db = loader::getdb();

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

        ?>
        <a name="annotation_<?php echo $id_annotation; ?>" style="display:none;"></a>
        <div style="overflow:hidden;">
            <a id="annotation_count_<?php echo $id_annotation; ?>" class="ploopi_annotation_viewlist" href="javascript:void(0);" onclick="javascript:ploopi_getelem('annotation_list_<?php echo $id_annotation; ?>').style.display=(ploopi_getelem('annotation_list_<?php echo $id_annotation; ?>').style.display=='block') ? 'none' : 'block'; ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_show&object_id=<?php echo $id_annotation; ?>');">
                <img border="0" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/annotation.png">
                <span><?php echo $nbanno; ?> annotation<?php echo ($nbanno>1) ? 's' : ''; ?></span>
            </a>

            <div style="display:<?php echo ($annotation_show) ? 'block' : 'none'; ?>;" id="annotation_list_<?php echo $id_annotation; ?>">
                <?php
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
                            AND         a.id_object = {$id_object}
                            AND         a.id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         (a.private = 0
                            OR          (a.private = 1 AND a.id_user = {$_SESSION['ploopi']['userid']}))
                            ORDER BY    a.date_annotation DESC
                            ";

                $rs_anno = $db->query($select);

                $array_anno = array();
                while ($fields = $db->fetchrow($rs_anno))
                {
                    $array_anno[$fields['id']]['fields'] = $fields;
                    if (!is_null($fields['tag'])) $array_anno[$fields['id']]['tags'][$fields['idtag']] = $fields['tag'];
                }

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
                                    <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click');ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_taghistory&id_tag=<?php echo $idtag; ?>', 'ploopi_popup');return false;"><?php echo ploopi\str::htmlentities($tag); ?></a>
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
                                    <a href="javascript:ploopi_annotation_delete('<?php echo $id_annotation; ?>', '<?php echo $fields['id']; ?>');">supprimer</a>
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
                    <form action="" method="post" id="form_annotation_<?php echo $id_annotation; ?>" target="form_annotation_target_<?php echo $id_annotation; ?>" onsubmit="return ploopi_annotation_validate(this);">
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
                        <input type="button" onclick="ploopi_getelem('form_annotation_<?php echo $id_annotation; ?>').ploopi_op.value=''; ploopi_getelem('form_annotation_<?php echo $id_annotation; ?>').submit()" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>">
                        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <iframe name="form_annotation_target_<?php echo $id_annotation; ?>" src="./img/blank.gif" style="display:none;"></iframe>

        <script type="text/javascript">
            ploopi_annotation_tag_init('<?php echo $id_annotation; ?>');
        </script>
        <?php
    }


}

/**
 * Classe d'accès à la table ploopi_annotation_tag
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class annotation_tag extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return annotation_tag
     */

    public function annotation_tag()
    {
        parent::__construct('ploopi_annotation_tag','id_annotation','id_tag');
    }

    /**
     * Supprime le tag s'il n'est plus utilisé
     */

    public function delete()
    {
        $db = loader::getdb();

        $select =   "
                    SELECT  count(*) as c
                    FROM    ploopi_annotation_tag
                    WHERE   id_tag = {$this->fields['id_tag']}
                    AND     id_annotation <> {$this->fields['id_annotation']}
                    ";

        $rs = $db->query($select);
        if (!($row = $db->fetchrow($rs)) || $row['c'] == 0)
        {
            $tag = new tag();
            $tag->open($this->fields['id_tag']);
            $tag->delete();
        }

        parent::delete();
    }

}

/**
 * Classe d'accès à la table ploopi_tag
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class tag extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return tag
     */

    public function tag()
    {
        parent::__construct('ploopi_tag','id');
    }
}
