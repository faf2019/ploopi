<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Préparation à l'affichage d'un formulaire
 *
 * @package forms
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On récupère les dates de publication du formulaire
 */

$pubdate_start = ($objForm->fields['pubdate_start']) ? ploopi\date::timestamp2local($objForm->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($objForm->fields['pubdate_end']) ? ploopi\date::timestamp2local($objForm->fields['pubdate_end']) : array('date' => '');

switch($op)
{
    case 'forms_reply_add':
    case 'forms_reply_modify':
        $strRenderMode = 'modify';
    break;

    case 'forms_reply_display':
        $strRenderMode = 'view';
    break;

    default:
        $strRenderMode = 'preview';
    break;
}

$intReplyId = isset($_REQUEST['record_id']) && is_numeric($_REQUEST['record_id']) ? $_REQUEST['record_id'] : null;

echo ploopi\skin::get()->open_simplebloc();

$objForm->render($intReplyId, $strRenderMode);

echo ploopi\skin::get()->close_simplebloc();


