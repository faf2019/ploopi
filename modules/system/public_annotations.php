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
 * Interface de gestion des annotations
 *
 * @package system
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Switch entre les différentes opérations
 */

switch($op)
{
    default:
        echo $skin->create_pagetitle('Annotations');
        echo $skin->open_simplebloc();

        $page = (empty($_GET['page'])) ? 1 : $_GET['page'];
        ?>

            <div id="system_annotation_tags">
                <div id="system_annotation_titlebar">
                    <b>Mes tags : </b><a href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=annotation"); ?>">voir tous les tags</a>
                </div>
                <?php
                $select =   "
                            SELECT  t.*, count(*) as c
                            FROM    ploopi_tag t,
                                    ploopi_annotation_tag at
                            WHERE   t.id_user = {$_SESSION['ploopi']['userid']}
                            AND     t.id = at.id_tag
                            GROUP BY t.id
                            ORDER BY t.tag
                            ";

                $rs = $db->query($select);
                $tags = array();
                $max_c = 0;
                while ($row = $db->fetchrow($rs))
                {
                    if (!empty($row['c']) && $row['c'] > $max_c) $max_c = $row['c'];
                    $tags[$row['id']] = $row;
                }

                $maxsize = 10;
                $minsize = 10;
                foreach($tags as $idt => $tag)
                {
                    $size = $minsize + $maxsize * $tag['c'] / $max_c;
                    ?>
                    <a title="utilisé <?php echo $tag['c']; ?> fois" class="system_annotation_tag<?php if (!empty($_GET['idtag']) && $_GET['idtag'] == $idt) echo 'sel'; ?>" style="font-size: <?php echo $size; ?>px;" href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=annotation&idtag={$idt}"); ?>"><?php echo htmlentities($tag['tag']); ?><span style="vertical-align: 4px; font-size: 7px"> <?php echo $tag['c']; ?></span></a>
                    <?php
                }

                ?>
            </div>

            <div id="system_annotation_list">
                <?php
                $idtag = '';

                if (!empty($_GET['idtag']) && is_numeric($_GET['idtag']))
                {
                    $idtag = $_GET['idtag'];

                    $select =   "
                                SELECT      a.*,
                                            t.id as tagid,
                                            t.tag,
                                            m.label as module_name,
                                            o.label as object_name,
                                            o.script

                                FROM        ploopi_annotation a

                                INNER JOIN  ploopi_annotation_tag at2
                                ON          a.id = at2.id_annotation
                                AND         at2.id_tag = '".$db->addslashes($_GET['idtag'])."'

                                LEFT JOIN   ploopi_annotation_tag at ON at.id_annotation = a.id
                                LEFT JOIN   ploopi_tag t ON t.id = at.id_tag

                                LEFT JOIN   ploopi_module m ON a.id_module = m.id
                                LEFT JOIN   ploopi_mb_object o ON a.id_object = o.id AND m.id_module_type = o.id_module_type

                                WHERE       a.id_user = {$_SESSION['ploopi']['userid']}
                                ORDER BY    a.date_annotation DESC
                                ";
                }
                else
                {
                    $select =   "
                                SELECT      a.*,
                                            t.id as tagid,
                                            t.tag,
                                            m.label as module_name,
                                            o.label as object_name,
                                            o.script

                                FROM        ploopi_annotation a

                                LEFT JOIN   ploopi_annotation_tag at ON at.id_annotation = a.id
                                LEFT JOIN   ploopi_tag t ON t.id = at.id_tag

                                LEFT JOIN   ploopi_module m ON a.id_module = m.id
                                LEFT JOIN   ploopi_mb_object o ON a.id_object = o.id AND m.id_module_type = o.id_module_type

                                WHERE       a.id_user = {$_SESSION['ploopi']['userid']}
                                ORDER BY    a.date_annotation DESC
                                ";
                }

                $rs = $db->query($select);
                $arrAnnotations = array();
                while ($row = $db->fetchrow($rs))
                {
                    if (!isset($arrAnnotations[$row['id']])) $arrAnnotations[$row['id']] = $row;
                    if (!is_null($row['tag'])) $arrAnnotations[$row['id']]['tags'][$row['tagid']] = $row['tag'];
                }
                ?>

                <div id="system_annotation_titlebar">
                <?php
                $nb_anno_page = 10;
                $numrows = sizeof($arrAnnotations);
                $nbpage = ($numrows - $numrows % $nb_anno_page) / $nb_anno_page + ($numrows % $nb_anno_page > 0);

                if ($nbpage>0)
                {
                    ?>
                    <div style="float:right;">
                        <div style="float:left;">page :&nbsp;</div>
                        <?php
                        for ($p = 1; $p <= $nbpage; $p++)
                        {
                            ?>
                            <a class="system_annotation_page<?php if ($p==$page) echo '_sel'; ?>" href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=annotation&page={$p}&idtag={$idtag}"); ?>"><?php echo $p; ?></a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
                </div>

                <?php
                // on se positionne sur le bon enregistrement
                for ($i=0; $i<($page-1)*$nb_anno_page; $i++) next($arrAnnotations);

                $annotation = current($arrAnnotations);
                for  ($i=0; $i<$nb_anno_page && !empty($annotation); $i++)
                {
                    $object_script = str_replace(
                                                    array(
                                                        '<IDRECORD>',
                                                        '<IDMODULE>',
                                                        '<IDWORKSPACE>'
                                                    ),
                                                    array(
                                                        $annotation['id_record'],
                                                        $annotation['id_module'],
                                                        $annotation['id_workspace']
                                                    ),
                                                    $annotation['script']
                                        );
                    $ldate = ploopi_timestamp2local($annotation['date_annotation']);
                    $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
                    ?>
                    <div class="system_annotation_row" style="background-color:<?php echo $color; ?>">
                        <div class="system_annotation_title">
                            <a href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu=1&{$object_script}"); ?>"><?php echo htmlentities($annotation['object_label']); ?></a>
                        </div>
                        <div class="system_annotation_date">
                            le <?php echo $ldate['date']; ?> à <?php echo $ldate['time']; ?>
                        </div>

                        <div  class="system_annotation_content"><?php echo ploopi_make_links(ploopi_nl2br(htmlentities($annotation['content']))); ?></div>

                        <div class="system_annotation_taglist">
                        <?php
                        if (isset($annotation['tags']) && sizeof($annotation['tags'])>0)
                        {
                            ?>
                            tags:
                            <?php
                            foreach($annotation['tags'] as $idtag => $tag)
                            {
                                ?>
                                <a href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=annotation&idtag={$idtag}"); ?>"><?php echo htmlentities($tag); ?></a>
                                <?php
                            }
                        }
                        ?>
                        </div>
                        <div class="system_annotation_module">
                            <a href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu=1&{$object_script}"); ?>"><b><?php echo $annotation['module_name']; ?></b>  / <?php echo htmlentities($annotation['object_name']); ?></a>
                        </div>
                    </div>
                    <?php
                    next($arrAnnotations);
                    $annotation = current($arrAnnotations);
                }
                ?>
            </div>
        <?php
        echo $skin->close_simplebloc();
    break;
}
?>
