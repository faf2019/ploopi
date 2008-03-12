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

switch($ploopi_op)
{
    case 'annotation':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (empty($_GET['id_annotation'])) ploopi_die();

        $id_object = $_SESSION['annotations'][$_GET['id_annotation']]['id_object'];
        $id_record = $_SESSION['annotations'][$_GET['id_annotation']]['id_record'];

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

        $annotation_show = (isset($_SESSION['ploopi']['annotations']['show'][$_GET['id_annotation']]));

        ?>
        <a name="annotation_<? echo $_GET['id_annotation']; ?>" style="display:none;"></a>
        <div style="overflow:hidden;">
            <a id="annotations_count_<? echo $_GET['id_annotation']; ?>" class="ploopi_annotation_viewlist" href="javascript:void(0);" onclick="javascript:ploopi_getelem('annotations_list_<? echo $_GET['id_annotation']; ?>').style.display=(ploopi_getelem('annotations_list_<? echo $_GET['id_annotation']; ?>').style.display=='block') ? 'none' : 'block'; ploopi_xmlhttprequest('index-light.php','ploopi_op=annotation_show&object_id=<? echo $_GET['id_annotation']; ?>');">
                <img border="0" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/annotation.png">
                <span><? echo $nbanno; ?> annotation<? echo ($nbanno>1) ? 's' : ''; ?></span>
            </a>

            <div style="display:<? echo ($annotation_show) ? 'block' : 'none'; ?>;" id="annotations_list_<? echo $_GET['id_annotation']; ?>">

            <?

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

                $ldate = ploopi_timestamp2local($fields['date_annotation']);
                $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;

                $private = '';
                if ($fields['private']) $private = 'Priv�';

                ?>
                <div class="ploopi_annotations_row_<? echo $numrow; ?>">
                    <div>
                        <div style="float:right;padding:2px 4px;">par <strong><? echo "{$fields['firstname']} {$fields['lastname']}"; ?></strong> le <? echo $ldate['date']; ?> � <? echo $ldate['time']; ?> <? echo $private; ?></div>
                        <div style="padding:2px 4px;"><strong><? echo htmlentities($fields['title']); ?></strong></div>
                    </div>
                    <div style="clear:both;padding:2px 4px;"><? echo ploopi_make_links(ploopi_nl2br(htmlentities($fields['content']))); ?></div>
                    <div style="clear:both;">
                        <?
                        if ($fields['id_user'] == $_SESSION['ploopi']['userid'])
                        {
                            ?>
                            <div style="float:right;padding:2px 4px;">
                                <a href="javascript:ploopi_annotation_delete('<? echo $_GET['id_annotation']; ?>', '<? echo $fields['id']; ?>');">supprimer</a>
                            </div>
                            <?
                        }
                        ?>
                        <div style="padding:2px 4px;">
                        <?
                        if (isset($anno['tags']) && is_array($anno['tags']))
                        {
                            echo "<b>tags :</b>";
                            foreach($anno['tags'] as $idtag => $tag)
                            {
                                ?>
                                <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=annotation_taghistory&id_tag=<? echo $idtag; ?>','','ploopi_popup');return false;"><? echo htmlentities($tag); ?></a>
                                <?
                            }
                        }
                        ?>
                        </div>
                    </div>
                </div>
                <?
            }

            if ($_SESSION['ploopi']['connected'])
            {
                $id_module_type = (isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'] : 0;

                $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;
                ?>
                <div class="ploopi_annotations_row_<? echo $numrow; ?>">
                    <form action="" method="post" id="form_annotation_<? echo $_GET['id_annotation']; ?>" target="form_annotation_target_<? echo $_GET['id_annotation']; ?>" onsubmit="return ploopi_annotation_validate(this);">
                    <input type="hidden" name="ploopi_op" value="annotation_save">
                    <input type="hidden" name="id_annotation" value="<? echo $_GET['id_annotation']; ?>">

                    <div class="ploopi_annotations_titleform">Ajout d'une Annotation <? echo (isset($ploopi_annotation_private)) ? 'priv�e' : ''; ?></div>
                    <div style="padding:2px 4px;"><input type="checkbox" name="ploopi_annotation_private" value="1">Priv�e (visible par vous uniquement)</div>
                    <div style="padding:2px 4px;">Titre:</div>
                    <div style="padding:2px 4px;"><input type="text" class="text" style="width:99%;" name="ploopi_annotation_title"></div>
                    <div style="padding:2px 4px;">Tags:</div>
                    <div style="padding:2px 4px;"><input type="text" class="text" style="width:99%;" name="ploopi_annotationtags" id="ploopi_annotationtags_<? echo $_GET['id_annotation']; ?>" autocomplete="off"></div>
                    <div style="padding:2px 4px;" id="tagsfound_<? echo $_GET['id_annotation']; ?>"></div>
                    <div style="padding:2px 4px;">Commentaire:</div>
                    <div style="padding:2px 4px;"><textarea class="text" style="width:99%;" rows="4" name="ploopi_annotation_content"></textarea></div>

                    <div style="padding:2px 4px;text-align:right;">
                        <input type="button" onclick="ploopi_getelem('form_annotation_<? echo $_GET['id_annotation']; ?>').ploopi_op.value=''; ploopi_getelem('form_annotation_<? echo $_GET['id_annotation']; ?>').submit()" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>">
                        <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
                    </div>
                    </form>
                </div>
                <?
            }
            ?>
            </div>
        </div>
        <iframe name="form_annotation_target_<? echo $_GET['id_annotation']; ?>" src="./img/blank.gif" style="width:0;height:0;display:none;"></iframe>

        <script type="text/javascript">
            ploopi_annotation_tag_init('<? echo $_GET['id_annotation']; ?>');
        </script>
        <?
        ploopi_die();
    break;

    case 'annotation_taghistory':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        ?>
        <div class="ploopi_annotation_popup">
        <?
        if (isset($_GET['id_tag']) && is_numeric($_GET['id_tag']))
        {
            include_once './include/global.php';
            include_once './modules/system/class_tag.php';

            $tag = new tag();
            $tag->open($_GET['id_tag']);

            ?>
            <div style="padding:4px;">Le tag <b><? echo $tag->fields['tag'] ; ?></b> a aussi �t� utilis� sur les annotations suivantes :</div>
            <div class="ploopi_annotation_popup_list">
            <?


            $select =   "
                        SELECT      a.*,
                                    o.script,
                                    o.label as object_name,
                                    m.label as module_name

                        FROM        ploopi_annotation a

                        INNER JOIN  ploopi_annotation_tag at
                        ON          at.id_annotation = a.id
                        AND         at.id_tag = {$_GET['id_tag']}

                        INNER JOIN  ploopi_module m
                        ON          a.id_module = m.id

                        LEFT JOIN   ploopi_mb_object o ON o.id = a.id_object AND o.id_module_type = m.id_module_type

                        ORDER BY    a.date_annotation DESC
                        ";

            $rs = $db->query($select);

            while ($fields = $db->fetchrow($rs))
            {
                $ld = ploopi_timestamp2local($fields['date_annotation']);
                ?>
                <div class="ploopi_annotations_row_<? echo $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2; ?>" style="padding:4px;">
                    <div style="float:right;"><? echo "le {$ld['date']} � {$ld['time']}"; ?></div>
                    <div style="font-weight:bold;"><? echo "{$fields['title']}"; ?></div>
                    <div style="clear:both;padding-top:4px;"><? echo ploopi_make_links(ploopi_nl2br(htmlentities($fields['content']))); ?></div>
                    <?
                    if ($fields['id_record'] != '')
                    {
                        $object_script = str_replace(
                                                    array(
                                                        '<IDRECORD>',
                                                        '<IDMODULE>',
                                                        '<IDWORKSPACE>'
                                                    ),
                                                    array(
                                                        $fields['id_record'],
                                                        $fields['id_module'],
                                                        $fields['id_workspace']
                                                    ),
                                                    $fields['script']
                                        );
                        ?>
                        <div style="clear:both;padding-top:4px;text-align:right;"><a href="<? echo "admin.php?ploopi_mainmenu=1&{$object_script}"; ?>"><? echo "{$fields['module_name']} / {$fields['object_name']} / {$fields['object_label']}"; ?></a></div>
                        <?
                    }
                    ?>
                </div>
                <?
            }
            ?>
            </div>
            <?
        }
        else echo "erreur";
        ?>
        <!--a style="display:block;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a-->
        <div style="padding:4px;text-align:right"><a href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a></div>
        </div>
        <?
        ploopi_die();
    break;

    case 'annotation_searchtags':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (!empty($_GET['tag']))
        {
            $select =   "
                        SELECT  t.id,
                                t.tag,
                                count(*) as c
                        FROM    ploopi_tag t,
                                ploopi_annotation_tag at
                        WHERE   t.tag LIKE '".$db->addslashes($_GET['tag'])."%'
                        AND     t.id_user = {$_SESSION['ploopi']['userid']}
                        AND     t.id = at.id_tag
                        GROUP BY t.id
                        ORDER BY c DESC
                        ";

            $rs = $db->query($select);
            $c=0;

            while ($fields = $db->fetchrow($rs))
            {
                if ($c++) echo '|';
                echo "{$fields['tag']};{$fields['c']}";
            }
        }
    break;

    case 'annotation_delete':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        include_once './include/classes/class_annotation.php';
        $annotation = new annotation();

        if (!empty($_GET['ploopi_annotation_id']) && is_numeric($_GET['ploopi_annotation_id']) && $annotation->open($_GET['ploopi_annotation_id']) && $annotation->fields['id_user'] == $_SESSION['ploopi']['userid'])
        {
            $annotation->delete();
        }
    break;

    case 'annotation_save':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (!empty($_POST['id_annotation']))
        {
            include_once './include/classes/class_annotation.php';

            $annotation = new annotation();
            $annotation->setvalues($_POST,'ploopi_annotation_');

            $annotation->fields['id_object'] = $_SESSION['annotations'][$_POST['id_annotation']]['id_object'];
            $annotation->fields['id_record'] = $_SESSION['annotations'][$_POST['id_annotation']]['id_record'];
            $annotation->fields['object_label'] = $_SESSION['annotations'][$_POST['id_annotation']]['object_label'];

            if (isset($_POST['ploopi_annotationtags'])) $annotation->tags = $_POST['ploopi_annotationtags'];
            if (!isset($_POST['ploopi_annotation_private'])) $annotation->fields['private'] = 0;

            $annotation->fields['date_annotation'] = ploopi_createtimestamp();
            $annotation->setuwm();

            if (!empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) $annotation->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
            $annotation->save();
            ?>
            <script type="text/javascript">
                window.parent.ploopi_annotation('<? echo $_POST['id_annotation']; ?>');
            </script>
        <?
        }
        ploopi_die();
        //ploopi_tickets_send($annotation->fields['id_object'], $annotation->fields['id_record'], $annotation->fields['object_label'], $annotation->fields['title'], $annotation->fields['content']);
    break;

    case 'annotation_show':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (isset($_GET['object_id']))
        {
            if (isset($_SESSION['ploopi']['annotations']['show'][$_GET['object_id']])) unset($_SESSION['ploopi']['annotations']['show'][$_GET['object_id']]);
            else $_SESSION['ploopi']['annotations']['show'][$_GET['object_id']] = 1;
        }
    break;
}
?>