<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2011 Ovensia
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
 * Interface d'administration du module
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ovensia\ploopi\module::init('forms');

global $field_types;
global $field_formats;
global $field_operators;
global $form_types;
global $forms_graphic_types;
global $forms_graphic_line_aggregation;
global $forms_graphic_operation;

/**
 * On vérifie que l'utilisateur connecté est admin du module
 */

if (ovensia\ploopi\acl::isactionallowed(_FORMS_ACTION_ADMIN))
{
    include_once './modules/forms/classes/formsForm.php';
    include_once './modules/forms/classes/formsField.php';
    include_once './modules/forms/classes/formsGraphic.php';
    include_once './modules/forms/classes/formsGroup.php';

    $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

    if (!empty($_GET['formsTabItem'])) $_SESSION['forms']['formsTabItem'] = $_GET['formsTabItem'];
    if (!isset($_SESSION['forms']['formsTabItem'])) $_SESSION['forms']['formsTabItem'] = '';

    $sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ovensia\ploopi\system::viewworkspaces($_SESSION['ploopi']['moduleid']).')';

    $tabs['formlist'] =
        array(
            'title' => _FORMS_LABELTAB_LIST,
            'url' => "admin.php?formsTabItem=formlist"
        );

    $tabs['formadd'] =
        array(
            'title' => _FORMS_LABELTAB_ADD,
            'url' => "admin.php?formsTabItem=formadd"
        );

    echo $skin->create_pagetitle(ovensia\ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
    echo $skin->create_tabs($tabs, $_SESSION['forms']['formsTabItem']);


    switch($_SESSION['forms']['formsTabItem'])
    {
        case 'formlist':
            switch($op)
            {
                case 'forms_html_add':
                case 'forms_html_modify':
                case 'forms_separator_add':
                case 'forms_separator_modify':
                case 'forms_field_add':
                case 'forms_field_modify':
                case 'forms_graphic_add':
                case 'forms_graphic_modify':
                case 'forms_group_add':
                case 'forms_group_modify':
                case 'forms_modify':
                    include './modules/forms/admin_forms_modify.php';
                break;

                default:
                    include './modules/forms/admin_forms_list.php';
                break;
            }
        break;

        case 'formadd':
            switch($op)
            {
                default:
                    include './modules/forms/admin_forms_modify.php';
                break;
            }
        break;

    }

    if (isset($_GET['termine']))
    {
        ?>
        <script type="text/javascript">
            alert('Terminé !');
        </script>
        <?php
    }
}
?>
