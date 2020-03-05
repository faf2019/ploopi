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
 * Gestion des objets insérables dans une page de contenu (WebEdit)
 *
 * @package forms
 * @subpackage wce
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */

ploopi\module::init('forms');

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsField.php';

global $field_formats; // from form/include/global.php
global $field_operators; // from form/include/global.php

global $articleid;
global $headingid;
global $template_name;

if (!empty($_REQUEST['op'])) $op = $_REQUEST['op'];

switch($op)
{
    case 'display':
        $objForm = new formsForm();
        if ($objForm->open($obj['object_id']))
        {
            $objForm->render(null, 'frontoffice');
        }
    break;

    case 'end':
        $objForm = new formsForm();
        if ($objForm->open($obj['object_id']))
        {
            ?>
            <div id="forms_response"><?php echo nl2br($objForm->fields['cms_response']); ?></div>
            <?php
        }
    break;

    case 'end_error':
        $objForm = new formsForm();
        if ($objForm->open($obj['object_id']))
        {
            ?>
            <div id="forms_response"><?php echo _FORMS_ERROR_CAPTCHA; ?></div>
            <?php
        }
    break;

}
?>
