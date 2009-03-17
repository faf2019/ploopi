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
 * Opérations
 *
 * @package forms
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Opérations pour les utilisateurs connectés uniquement
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On vérifie qu'on est bien dans le module FORMS.
     */

    if (ploopi_ismoduleallowed('forms'))
    {
        switch($ploopi_op)
        {
            case 'forms_xml_switchdisplay':
                if (!empty($_GET['display']))
                {
                    $switch = (!isset($_GET['switch'])) ? 'empty' : $_GET['switch'];
                    $_SESSION['forms'][$_SESSION['ploopi']['moduleid']][$switch] = $_GET['display'];
                }
                ploopi_die();
            break;
            
            case 'forms_export':
                ploopi_init_module('forms');
                if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT) && !empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
                {
                    $id_module = $_SESSION['ploopi']['moduleid'];
                    include './modules/forms/op_export.php';
                }
            break;

            case 'forms_delete_data':
                include_once './modules/form/include/global.php';

                if (ploopi_isactionallowed(_FORMS_ACTION_BACKUP))
                {
                    ?>
                    <div style="background:#f0f0f0;border-bottom:1px solid #c0c0c0;font-weight:bold;padding:2px;">Suppression des données</div>
                    <div style="padding:2px;">
                    <?php
                    if (!empty($_GET['form_id']) && !empty($_GET['form_delete_date']))
                    {
                        $form_delete_date = ploopi_local2timestamp($_GET['form_delete_date']);

                        $form_delete_date = ploopi_timestamp_add($form_delete_date, 0, 0, 0, 0, 1, 0);

                        $sql = "SELECT COUNT(*) as c FROM ploopi_mod_forms_reply WHERE id_form = '".$db->addslashes($_GET['form_id'])."' AND date_validation < {$form_delete_date}";
                        $db->query($sql);
                        $row = $db->fetchrow();

                        echo "{$row['c']} enregistement(s) ont été supprimés";

                        $sql =  "
                                DELETE  r, rf
                                FROM    ploopi_mod_forms_reply r,
                                        ploopi_mod_forms_reply_field rf
                                WHERE   r.id_form = '".$db->addslashes($_GET['form_id'])."'
                                AND     r.date_validation < {$form_delete_date}
                                AND     rf.id_reply = r.id
                                ";
                        $db->query($sql);
                    }
                    ?>
                    </div>
                    <div style="background:#f0f0f0;border-top:1px solid #c0c0c0;text-align:right;padding:2px;"><a href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('forms_deletedata');document.location.reload();">Fermer</a></div>
                    <?php
                }
                ploopi_die();
            break;
        }
    }

    /**
     * Autres opérations
     */

    switch($ploopi_op)
    {
        case 'forms_download_file':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                if (!empty($_GET['reply_id']) && !empty($_GET['field_id']) && is_numeric($_GET['reply_id']) && is_numeric($_GET['field_id']))
                {
                    include_once './modules/forms/class_reply_field.php';
                    $reply_field = new reply_field();
                    if ($reply_field->open($_GET['reply_id'], $_GET['field_id']))
                    {
                        $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$id_module._PLOOPI_SEP.$reply_field->fields['id_form']._PLOOPI_SEP.$_GET['reply_id']._PLOOPI_SEP;
                        ploopi_downloadfile("{$path}{$reply_field->fields['value']}", $reply_field->fields['value']);
                    }
                }
            }
            ploopi_die();
        break;

        case 'forms_display':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/op_viewlist.php';
            }
            ploopi_die();
        break;

        case 'forms_export':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/public_forms_export.php';
            }
            ploopi_die();
        break;

        case 'forms_openreply':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $reply_id = $_GET['forms_reply_id'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                $forms = new form();
                $forms->open($id_form);

                include_once './modules/forms/op_display.php';

                $content = ob_get_contents();
                ob_end_clean();

                echo $skin->create_popup($forms->fields['label'], $content, 'popup_forms_openreply');
            }
            ploopi_die();
        break;

        case 'forms_save':
            if (!empty($_POST['forms_fuid']) && isset($_SESSION['forms'][$_POST['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';
                include_once './modules/forms/class_reply.php';
                include_once './modules/forms/class_reply_field.php';

                $reply_id = $_POST['forms_reply_id'];
                $id_form = $_SESSION['forms'][$_POST['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_POST['forms_fuid']]['id_module'];

                include_once './modules/forms/op_save.php';
                ?>
                <script type="text/javascript">
                    window.parent.document.location.reload();
                </script>
                <?php
            }
            ploopi_die();
        break;
    }
}
?>
