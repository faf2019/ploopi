<?php
/*
 Copyright (c) 2007-2018 Ovensia
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
 * Partie publique du module
 *
 * @package forms
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */
ploopi\module::init('forms');

global $field_types;
global $field_formats;
global $field_operators;
global $form_types;
global $forms_graphic_types;
global $forms_graphic_line_aggregation;
global $forms_graphic_operation;

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsField.php';
include_once './modules/forms/classes/formsRecord.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

/*
if ($op == 'forms_print')
{
    $objForm = new formsForm();
    if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
    {
        $objForm->includeCss(true);
        ?>
        <div class="forms_form">
        <form id="form"></form>
        </div>
        <script type="text/javascript">
            jQuery('#form')[0].innerHTML = window.opener.document.forms_form_<?php echo ploopi\str::htmlentities($_GET['forms_id']); ?>.innerHTML;
            Event.observe(window, 'load', function() {
                <?php
                for ($i=1; $i<=$objForm->getNbPanels();$i++)
                {
                    ?>
                    $('panel_<?php echo $i; ?>').style.display = 'block';
                    <?php
                }
                ?>
                window.print();
                window.close();
            });
        </script>
        <?php
    }
    return;
}
*/

$sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi\system::viewworkspaces($_SESSION['ploopi']['moduleid']).')';

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

switch($op)
{
    case 'forms_save':
        $objForm = new formsForm();
        if (isset($_POST['forms_id']) && $_POST['forms_id'] != '')
        {
            $objForm->open($_POST['forms_id']);
            $objForm->fields['autobackup'] = $_POST['forms_autobackup'];
            $objForm->fields['autobackup_date'] = ploopi\date::local2timestamp($_POST['forms_autobackup_date']);
            $objForm->save();
            ploopi\output::redirect("admin.php?op=forms_viewreplies&forms_id={$_POST['forms_id']}");
        }
        ploopi\output::redirect('admin.php');
    break;

    case 'forms_reply_display':
    case 'forms_reply_add':
    case 'forms_reply_modify':
        if (ploopi\acl::isactionallowed(_FORMS_ACTION_ADDREPLY) || $op == 'forms_reply_display')
        {
            $objForm = new formsForm();

            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
            {
                if (ploopi\session::setflag('forms_nbclick', $_GET['forms_id'])) $objForm->fields['viewed']++;
                $objForm->quicksave();

                include './modules/forms/public_forms_display.php';
            }
            else ploopi\output::redirect('admin.php');
        }
        else ploopi\output::redirect('admin.php');
    break;

    /**
     * Consultation des données
     */
    case 'forms_viewreplies':
    case 'forms_filter':
    case 'forms_deletedata':
        $objForm = new formsForm();

        if (empty($_REQUEST['forms_id']) || !is_numeric($_REQUEST['forms_id']) || !$objForm->open($_REQUEST['forms_id'])) ploopi\output::redirect('admin.php');

        if ($objForm->isPublished() && (!$objForm->fields['option_adminonly'] || ploopi\acl::isactionallowed(_FORMS_ACTION_ADMIN)))
        {

            if ($op == 'forms_deletedata')
            {
                $objForm->deleteData();
                ploopi\output::redirect("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}");
            }
            else
            {
                /**
                 * Lecture des données du formulaire
                 */
                list($arrData, $intNumRows, $arrFormFilter) = $objForm->prepareData();

                include_once './modules/forms/public_forms_viewreplies.php';
            }
        }
        else ploopi\output::redirect('admin.php');
    break;

    default:
        include('./modules/forms/public_forms_list.php');
    break;
}
?>
