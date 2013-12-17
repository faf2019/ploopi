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
 * Interface permettant de lister les formulaires
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du tableau contenant la liste des formulaires
 */

echo $skin->open_simplebloc(_FORMS_LABELTAB_LIST);

$array_columns = array();
$array_values = array();

$array_columns['auto']['label'] =
    array(
        'label' => _FORMS_LABEL,
        'options' => array('sort' => true)
    );

$array_columns['right']['typeform'] =
    array(
        'label' => _FORMS_TYPEFORM,
        'width' => 220,
        'options' => array('sort' => true)
    );

$array_columns['right']['desc'] =
    array(
        'label' => _FORMS_DESCRIPTION,
        'width' => 300,
        'options' => array('sort' => true)
    );


$array_columns['actions_right']['actions'] =
    array(
        'label' => '',
        'width' => 85
    );


$sql =  "
        SELECT  *
        FROM    ploopi_mod_forms_form
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        {$sqllimitgroup}
        ORDER BY pubdate_start DESC, pubdate_end DESC
        ";

$db->query($sql);

$c=0;

while ($fields = $db->fetchrow())
{
    $pubdate_start = ($fields['pubdate_start']) ? ploopi_timestamp2local($fields['pubdate_start']) : array('date' => '');
    $pubdate_end = ($fields['pubdate_end']) ? ploopi_timestamp2local($fields['pubdate_end']) : array('date' => '');

    $open = ploopi_urlencode("admin.php?op=forms_modify&forms_id={$fields['id']}");
    $clone = ploopi_urlencode("admin.php?ploopi_op=forms_clone&forms_id={$fields['id']}");
    $delete = ploopi_urlencode("admin.php?ploopi_op=forms_delete&forms_id={$fields['id']}");
    $view = ploopi_urlencode("admin.php?ploopi_action=public&op=forms_viewreplies&forms_id={$fields['id']}");

    $array_values[$c]['values']['label']        = array('label' => ploopi_htmlentities($fields['label']));
    $array_values[$c]['values']['desc']         = array('label' => ploopi_htmlentities($fields['description']));
    $array_values[$c]['values']['typeform']     = array('label' => ploopi_htmlentities(isset($form_types[$fields['typeform']]) ? $form_types[$fields['typeform']] : ''));
    $array_values[$c]['values']['date_start']   = array('label' => ploopi_htmlentities($pubdate_start['date']));
    $array_values[$c]['values']['date_end']     = array('label' => ploopi_htmlentities($pubdate_end['date']));
    $array_values[$c]['values']['actions']      = array('label' => '
        <a href="'.$open.'" title="Modifier le formulaire"><img src="./modules/forms/img/ico_modify.png" alt="Modifier le formulaire"></a>
        <a href="javascript:void(0);" onclick="javascript:if (confirm(\'Attention cette action va cloner ce formulaire.\nÊtes vous certain de vouloir continuer ?\')) document.location.href=\''.$clone.'&data=\'+confirm(\'Copier les données ?\');" title="Cloner le formulaire"><img src="./modules/forms/img/ico_clone.png" alt="Cloner le formulaire"></a>
        <a href="'.$view.'" title="Consulter les données du formulaire"><img src="./modules/forms/img/ico_view.png" alt="Consulter les données du formulaire"></a>
        <a href="javascript:void(0);" onclick="javascript:ploopi_confirmlink(\''.$delete.'\',\'Attention cette action va supprimer définitivement le formulaire.\nÊtes vous certain de vouloir continuer ?\');"><img border="0" src="./modules/forms/img/ico_trash.png"></a>');


//      <a href="'.admin.php.'?op=forms_generate_tables_from_list&forms_id='.$fields['id'].'" title="Générer les données physiques du formulaire""><img src="./modules/forms/img/ico_renew.png" alt="Générer les données physiques du formulaire"></a>

    $array_values[$c]['description'] = "Ouvrir le Formulaire";
    $array_values[$c]['link'] = $open;
    $c++;
}

$skin->display_array($array_columns, $array_values, 'forms_list', array('sortable' => true, 'orderby_default' => 'label'));

echo $skin->close_simplebloc();
?>
