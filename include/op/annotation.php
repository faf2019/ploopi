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

/**
 * Opérations sur les annotation
 *
 * @package ploopi
 * @subpackage annotation
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

switch($ploopi_op)
{
    case 'annotation':
        if (empty($_GET['id_annotation'])) ploopi\system::kill();

        ploopi\annotation::display_refresh($_GET['id_annotation']);

        ploopi\system::kill();
    break;

    case 'annotation_taghistory':
        ?>
        <div class="ploopi_annotation_popup">
        <?php
        if (isset($_GET['id_tag']) && is_numeric($_GET['id_tag']))
        {
            $tag = new ploopi\tag();
            $tag->open($_GET['id_tag']);

            ?>
            <div style="padding:4px;">Le tag <b><?php echo ploopi\str::htmlentities($tag->fields['tag']); ; ?></b> a aussi été utilisé sur les annotation suivantes :</div>
            <div class="ploopi_annotation_popup_list">
            <?php

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

            $rs = ploopi\loader::getdb()->query($select);

            while ($fields = ploopi\loader::getdb()->fetchrow($rs))
            {
                $ld = ploopi\date::timestamp2local($fields['date_annotation']);
                ?>
                <div class="ploopi_annotation_row_<?php echo $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2; ?>" style="padding:4px;">
                    <div style="float:right;"><?php echo ploopi\str::htmlentities("le {$ld['date']} à {$ld['time']}"); ?></div>
                    <div style="font-weight:bold;"><?php echo ploopi\str::htmlentities("{$fields['title']}"); ?></div>
                    <div style="clear:both;padding-top:4px;"><?php echo ploopi\str::make_links(ploopi\str::nl2br(ploopi\str::htmlentities($fields['content']))); ?></div>
                    <?php
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
                        <div style="clear:both;padding-top:4px;text-align:right;"><a href="<?php echo "admin.php?ploopi_mainmenu=1&{$object_script}"; ?>"><?php echo ploopi\str::htmlentities("{$fields['module_name']} / {$fields['object_name']} / {$fields['object_label']}"); ?></a></div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
            </div>
            <?php
        }
        else echo "erreur";
        ?>
        <!--a style="display:block;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a-->
        <div style="padding:4px;text-align:right"><a href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a></div>
        </div>
        <?php
        ploopi\system::kill();
    break;

    case 'annotation_searchtags':
        if (!empty($_GET['tag']))
        {
            $select =   "
                        SELECT  t.id,
                                t.tag,
                                count(*) as c
                        FROM    ploopi_tag t,
                                ploopi_annotation_tag at
                        WHERE   t.tag LIKE '".ploopi\loader::getdb()->addslashes($_GET['tag'])."%'
                        AND     t.id_user = {$_SESSION['ploopi']['userid']}
                        AND     t.id = at.id_tag
                        GROUP BY t.id
                        ORDER BY c DESC
                        ";

            $rs = ploopi\loader::getdb()->query($select);
            $c=0;

            while ($fields = ploopi\loader::getdb()->fetchrow($rs))
            {
                if ($c++) echo '|';
                echo ploopi\str::htmlentities("{$fields['tag']};{$fields['c']}");
            }
        }
        ploopi\system::kill();
    break;

    case 'annotation_delete':
        $annotation = new ploopi\annotation();

        if (!empty($_GET['ploopi_annotation_id']) && is_numeric($_GET['ploopi_annotation_id']) && $annotation->open($_GET['ploopi_annotation_id']) && $annotation->fields['id_user'] == $_SESSION['ploopi']['userid'])
        {
            $annotation->delete();
        }
    break;

    case 'annotation_save':
        if (!empty($_POST['id_annotation']))
        {
            $annotation = new ploopi\annotation();
            $annotation->setvalues($_POST,'ploopi_annotation_');

            $annotation->fields['id_object'] = $_SESSION['annotation'][$_POST['id_annotation']]['id_object'];
            $annotation->fields['id_record'] = $_SESSION['annotation'][$_POST['id_annotation']]['id_record'];
            $annotation->fields['object_label'] = $_SESSION['annotation'][$_POST['id_annotation']]['object_label'];

            if (isset($_POST['ploopi_annotationtags'])) $annotation->tags = $_POST['ploopi_annotationtags'];
            if (!isset($_POST['ploopi_annotation_private'])) $annotation->fields['private'] = 0;

            $annotation->fields['date_annotation'] = ploopi\date::createtimestamp();
            $annotation->setuwm();

            if (!empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) $annotation->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
            $annotation->save();
            ?>
            <script type="text/javascript">
                window.parent.ploopi\annotation::display('<?php echo ploopi\str::htmlentities($_POST['id_annotation']); ?>');
            </script>
        <?php
        }
        ploopi\system::kill();
        //ploopi\ticket::send($annotation->fields['id_object'], $annotation->fields['id_record'], $annotation->fields['object_label'], $annotation->fields['title'], $annotation->fields['content']);
    break;

    case 'annotation_show':
        if (isset($_GET['object_id']))
        {
            if (isset($_SESSION['ploopi']['annotation']['show'][$_GET['object_id']])) unset($_SESSION['ploopi']['annotation']['show'][$_GET['object_id']]);
            else $_SESSION['ploopi']['annotation']['show'][$_GET['object_id']] = 1;
        }
    break;
}
