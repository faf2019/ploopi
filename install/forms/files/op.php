<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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
 * Opérations pour tous les utilisateurs connectés ou non
 */

switch($ploopi_op)
{
    case 'forms_tablelink_values':
        include_once './modules/forms/classes/formsForm.php';
        include_once './modules/forms/classes/formsField.php';

        if (!empty($_GET['forms_fields']) && !empty($_GET['forms_params']) && !empty($_GET['forms_requested']))
        {
            $arrParams = array();
            foreach($_GET['forms_params'] as $intFieldId => $strValue)
            {
                $objField = new formsField();
                if ($objField->open($intFieldId))
                {
                    // Valeur de la table liée
                    $objFieldValues = new formsField();
                    if ($objFieldValues->open($intFieldId))
                    {
                        $arrParams[$objFieldValues->fields['fieldname']] = $strValue;
                    }
                }
            }

            // Champ à remplir
            $objField = new formsField();
            $objForm = new formsForm();
            // On vérifie l'existence du champ, du formulaire et qu'il s'agit d'un formulaire CMS (donc public)
            if ($objField->open($_GET['forms_requested']) && ($_SESSION['ploopi']['connected'] || ($objForm->open($objField->fields['id_form']) && $objForm->fields['typeform'] == 'cms')))
            {
                // Valeur de la table liée
                $objFieldValues = new formsField();
                if ($objFieldValues->open($objField->fields['values']))
                {
                    ploopi_print_json(array_keys($objFieldValues->getValues($arrParams)));
                }
            }
        }

        ploopi_die();
    break;

    case 'forms_reply_save':
        include './modules/forms/op_reply_save.php';
    break;
}


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
            case 'forms_reply_delete':

                ploopi_init_module('forms');

                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsRecord.php';

                $objForm = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    $objRecord = new formsRecord($objForm);
                
                    if (!empty($_GET['record_id']) && is_numeric($_GET['record_id']) && $objRecord->open($_GET['record_id']))
                    {
                        if (ploopi_isadmin() || (
                            ploopi_isactionallowed(_FORMS_ACTION_DELETE) && (
                                ($objForm->fields['option_modify'] == 'user' && $objRecord->fields['user_id'] == $_SESSION['ploopi']['userid']) ||
                                ($objForm->fields['option_modify'] == 'group' && $objRecord->fields['workspace_id'] == $_SESSION['ploopi']['workspaceid'])  ||
                                ($objForm->fields['option_modify'] == 'all')
                            )
                        ))
                        {
                            $objRecord->delete();
                        }
                    }
                    
                    ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
                }
                
                ploopi_redirect('admin.php');
            break;


            case 'forms_download_file':
                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsRecord.php';
                include_once './modules/forms/classes/formsField.php';

                $objForm = new formsForm();

                // Ouverture formulaire
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    $objRecord = new formsRecord($objForm);
                    $objField = new formsField();

                    // Ouverture enregistrement + champ
                    if (!empty($_GET['record_id']) && !empty($_GET['field_id']) && is_numeric($_GET['record_id']) && is_numeric($_GET['field_id']) && $objRecord->open($_GET['record_id']) && $objField->open($_GET['field_id']))
                    {
                        if (!empty($objRecord->fields[$objField->fields['fieldname']]))
                        {
                            ploopi_downloadfile(
                                formsField::getFilePath($_GET['forms_id'], _PLOOPI_SEP.$_GET['record_id'], $objRecord->fields[$objField->fields['fieldname']]),
                                $objRecord->fields[$objField->fields['fieldname']]
                            );
                        }
                    }
                    // On ne passe ici qu'en cas d'échec de téléchargement
                    ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
                }
                // Formulaire invalide
                ploopi_redirect('admin.php');
            break;

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
                    <div style="background:#f0f0f0;border-top:1px solid #c0c0c0;text-align:right;padding:2px;"><a href="javascript:void(0);" onmouseup="javascript:ploopi_hidepopup('forms_deletedata');document.location.reload();">Fermer</a></div>
                    <?php
                }
                ploopi_die();
            break;

            case 'forms_graphic_display':
                ob_start();
                ?>
                <div style="background-color:#fff;overflow:auto;">
                <?php
                if (isset($_POST['forms_graphic_id']) && isset($_POST['forms_graphic_width']))
                {
                    ?>
                    <iframe style="width:100%;height:500px;" src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=forms_graphic_generate&forms_graphic_id={$_POST['forms_graphic_id']}&forms_rand=".microtime()); ?>"></iframe>
                    <img src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=forms_graphic_generate&forms_graphic_id={$_POST['forms_graphic_id']}&forms_graphic_width={$_POST['forms_graphic_width']}&forms_rand=".microtime()); ?>" />
                    <?
                }
                else
                {
                    echo "erreur";
                }
                ?>
                </div>
                <?php
                $strContent = ob_get_contents();
                ob_end_clean();
                ploopi_die($skin->create_popup('Graphique', $strContent, 'forms_popup_graphic'));
            break;

            case 'forms_graphic_generate':
                include_once './modules/forms/classes/formsGraphic.php';
                include_once './modules/forms/classes/formsField.php';

                $objGraphic = new formsGraphic();
                if (isset($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
                {
                    $intWidth = isset($_GET['forms_graphic_width']) && is_numeric($_GET['forms_graphic_width']) ? $_GET['forms_graphic_width'] : null;
                    //$intHeight = $intWidth*3/5;

                    $objGraphic->render($intWidth);
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

                if (!empty($_GET['record_id']) && !empty($_GET['field_id']) && is_numeric($_GET['record_id']) && is_numeric($_GET['field_id']))
                {
                    include_once './modules/forms/classes/formsReplyField.php';
                    $reply_field = new formsReplyField();
                    if ($reply_field->open($_GET['record_id'], $_GET['field_id']))
                    {
                        $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$id_module._PLOOPI_SEP.$reply_field->fields['id_form']._PLOOPI_SEP.$_GET['record_id']._PLOOPI_SEP;
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
                include_once './modules/forms/classes/formsForm.php';

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
                include_once './modules/forms/classes/formsForm.php';

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
                include_once './modules/forms/classes/formsForm.php';

                $record_id = $_GET['forms_record_id'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                $forms = new formsForm();
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
                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsReply.php';
                include_once './modules/forms/classes/formsReplyField.php';

                $record_id = $_POST['forms_record_id'];
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
