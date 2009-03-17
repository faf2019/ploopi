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
 * Préparation des données d'un formulaire pour affichage ou export
 *
 * @package forms
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see forms_viewworkspaces
 */

/**
 * On récupère quelques variables GET
 */

$orderby = (!isset($_GET['orderby'])) ? 'datevalidation' : $_GET['orderby'];
$option = (!isset($_GET['option'])) ? (( $orderby == 'datevalidation' ) ? 'DESC' : '' )  : $_GET['option'];

$lmax = 1;

$forms = new form();
$forms->open($id_form);

$workspaces = forms_viewworkspaces($id_module, $_SESSION['ploopi']['workspaceid'], $forms->fields['option_view']);

$data_title = array();

if ($_SESSION['forms'][$forms_fuid]['options']['object_display'])
{
    $data_title['object']   = array ('label' => $_SESSION['forms'][$forms_fuid]['options']['object_label'], 'sep' => 0, 'type' => '', 'format' => '');
}

$data_title['datevalidation']   = array ('label' => _FORMS_DATEVALIDATION, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['user']             = array ('label' => _FORMS_USER, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['group']            = array ('label' => _FORMS_GROUP, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['ip']               = array ('label' => _FORMS_IP, 'sep' => 0, 'type' => '', 'format' => '');

//if ($forms->fields['option_displaygroup'])
//$data[0][3] = _FORMS_MODULE;

$sql =  "
        SELECT  *
        FROM    ploopi_mod_forms_field
        WHERE   id_form = {$id_form}
        ORDER BY position
        ";

$db->query($sql);

$array_fields = array();

while ($fields = $db->fetchrow())
{
    if (!$fields['separator'])
    {
        $array_fields[$fields['id']] = $fields;
    }
    $data_title[$fields['id']]['label'] = $fields['name'];
    $data_title[$fields['id']]['sep'] = $fields['separator'];
    $data_title[$fields['id']]['seplev'] = $fields['separator_level'];
    $data_title[$fields['id']]['type'] = $fields['type'];
    $data_title[$fields['id']]['format'] = $fields['format'];
}

$search_pattern = array();
$search_pattern[] = "fr.id_form = {$id_form}";
$search_pattern[] = "fr.id_workspace IN ({$workspaces})";
$search_pattern[] = "fr.id_object = {$_SESSION['forms'][$forms_fuid]['id_object']}";
if ($_SESSION['forms'][$forms_fuid]['options']['filter_mode'] == 'like') $search_pattern[] = "fr.id_record LIKE '".$db->addslashes($_SESSION['forms'][$forms_fuid]['id_record'])."%'";
else $search_pattern[] = "fr.id_record = '".$db->addslashes($_SESSION['forms'][$forms_fuid]['id_record'])."'";

$select =   "
            SELECT      fr.*,
                        u.id as userid,
                        u.firstname,
                        u.lastname,
                        u.login,
                        w.id as workspaceid,
                        w.code,
                        w.label as w_label,
                        m.label as m_label

            FROM        ploopi_mod_forms_reply fr

            INNER JOIN  ploopi_module m
            ON          fr.id_module = m.id
            AND         m.id = {$id_module}

            LEFT JOIN   ploopi_user u
            ON          fr.id_user = u.id

            LEFT JOIN   ploopi_workspace w
            ON          fr.id_workspace = w.id

            WHERE   ".implode(' AND ', $search_pattern);

$rs = $db->query($select);

// construction du jeu de données brut (liste des réponses)
$data = array();
while ($fields = $db->fetchrow($rs))
{
    $c = $fields['id'];

    $data[$c] = array();

    if ($_SESSION['forms'][$forms_fuid]['options']['object_display'])
    {
        $data[$c]['record'] = $fields['id_record'];
    }

    $data[$c]['datevalidation'] = $fields['date_validation'];
    $data[$c]['user'] = $fields['login'];
    $data[$c]['userid'] = $fields['userid'];
    $data[$c]['group'] = $fields['w_label'];
    $data[$c]['groupid'] = $fields['workspaceid'];
    $data[$c]['ip'] = $fields['ip'];

/*
 *  $data[$c]['userid'] = $fields['userid'];
    $data[$c]['workspaceid'] = $fields['workspaceid'];
*/
    $sql =  "
            SELECT  rf.*, f.type
            FROM    ploopi_mod_forms_reply_field rf,
                    ploopi_mod_forms_field f
            WHERE   rf.id_reply = {$fields['id']}
            AND     f.id = rf.id_field
            AND     f.separator = 0
            ";

    $rs_replies = $db->query($sql);

    $array_values = array();

    while ($fields_replies = $db->fetchrow($rs_replies))
    {
        $array_values[$fields_replies['id_field']] = $fields_replies;
    }

    foreach($array_fields as $key => $value)
    {
        $data[$c][$key] = (isset($array_values[$key]['value'])) ? $array_values[$key]['value'] : '';
        if ($data_title[$key]['format'] == 'date') $data[$c][$key] = ploopi_local2timestamp(substr($data[$c][$key],0,10), '00:00:00');
    }

}

// strnatcascmp sur $data
uasort($data, create_function('$a,$b', '$orderby = "'.$orderby.'";$option = "'.$option.'";$res=($option == "DESC")?strnatcasecmp($b[$orderby], $a[$orderby]):strnatcasecmp($a[$orderby], $b[$orderby]);return($res);'));

// Formatage des données (dates)
foreach ($data as $reply_id => $detail)
{
    foreach($detail as $key => $value)
    {
        if ($key == 'record')
        {
            // affectation d'une valeur à l'objet (si définie)
            if (isset($_SESSION['forms'][$forms_fuid]['options']['object_values'][$value])) $data[$reply_id]['object'] = $_SESSION['forms'][$forms_fuid]['options']['object_values'][$value];
        }
        elseif ($key == 'datevalidation')
        {
            $ldate = ploopi_timestamp2local($value);
            $data[$reply_id][$key] = "{$ldate['date']} {$ldate['time']}";
        }
        elseif (isset($data_title[$key]) && $data_title[$key]['format'] == 'date' && !empty($value))
        {
            $ldate = ploopi_timestamp2local($value);
            $data[$reply_id][$key] = $ldate['date'];
        }
    }
}
?>
