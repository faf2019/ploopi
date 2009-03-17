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
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe "form"
 */
include_once './modules/forms/class_form.php';

/**
 * On ouvre le formulaire.
 * Si l'identifiant n'est pas valide => redirection.
 */
global $forms_id;

$forms = new form();

if (!empty($_REQUEST['forms_id']) && is_numeric($_REQUEST['forms_id']) && $forms->open($_REQUEST['forms_id'])) $forms_id = $_REQUEST['forms_id'];
else ploopi_redirect('admin.php');

// GET GPC
if (isset($_GET['reset']))  $_SESSION['forms'][$forms_id] = array();
if (isset($_GET['page'])) $_SESSION['forms'][$forms_id]['page'] = $_GET['page'];
if (isset($_GET['orderby'])) $_SESSION['forms'][$forms_id]['orderby'] = $_GET['orderby'];
if (isset($_GET['option'])) $_SESSION['forms'][$forms_id]['option'] = $_GET['option'];
if (isset($_REQUEST['unlockbackup'])) $_SESSION['forms'][$forms_id]['unlockbackup'] = $_REQUEST['unlockbackup'];

// VERIF SESSION
if (!isset($_SESSION['forms'][$forms_id]['page']) || $ploopi_op == 'forms_filter') $_SESSION['forms'][$forms_id]['page'] = 0;
if (!isset($_SESSION['forms'][$forms_id]['orderby'])) $_SESSION['forms'][$forms_id]['orderby'] = 'datevalidation';
if (!isset($_SESSION['forms'][$forms_id]['option'])) $_SESSION['forms'][$forms_id]['option'] = ($_SESSION['forms'][$forms_id]['orderby'] == 'datevalidation') ? 'DESC' : '';
if (!isset($_SESSION['forms'][$forms_id]['unlockbackup'])) $_SESSION['forms'][$forms_id]['unlockbackup'] = 0;

$lmax = 1;

// GET FILTER PARAMS
if (ploopi_isactionallowed(_FORMS_ACTION_FILTER))
{
    for ($l=1;$l<=$lmax;$l++)
    {
        if(isset($_POST["filter_field_{$l}"])) $_SESSION['forms'][$forms_id]["filter_field_{$l}"] = $_POST["filter_field_{$l}"];
        if(isset($_POST["filter_op_{$l}"])) $_SESSION['forms'][$forms_id]["filter_op_{$l}"] = $_POST["filter_op_{$l}"];
        if(isset($_POST["filter_value_{$l}"])) $_SESSION['forms'][$forms_id]["filter_value_{$l}"] = $_POST["filter_value_{$l}"];

        if (!isset($_SESSION['forms'][$forms_id]["filter_field_{$l}"])) $_SESSION['forms'][$forms_id]["filter_field_{$l}"] = '';
        if (!isset($_SESSION['forms'][$forms_id]["filter_op_{$l}"])) $_SESSION['forms'][$forms_id]["filter_op_{$l}"] = '';
        if (!isset($_SESSION['forms'][$forms_id]["filter_value_{$l}"])) $_SESSION['forms'][$forms_id]["filter_value_{$l}"] = '';
        elseif ($_SESSION['forms'][$forms_id]["filter_value_{$l}"] != '') $lmax++; // on adapte le nombre de filtre en fonction des filtres déjà utilisés

        ${"filter_field_{$l}"} = $_SESSION['forms'][$forms_id]["filter_field_{$l}"];
        ${"filter_op_{$l}"} = $_SESSION['forms'][$forms_id]["filter_op_{$l}"];
        ${"filter_value_{$l}"} = $_SESSION['forms'][$forms_id]["filter_value_{$l}"];
    }
}

// GET WORKSPACES
$workspaces = forms_viewworkspaces($_SESSION['ploopi']['moduleid'], $_SESSION['ploopi']['workspaceid'], $forms->fields['option_view']);

// INIT ARRAY TITLES
$data_title = array();
$data_title['datevalidation']   = array ('label' => _FORMS_DATEVALIDATION, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['user']             = array ('label' => _FORMS_USER, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['group']            = array ('label' => _FORMS_GROUP, 'sep' => 0, 'type' => '', 'format' => '');
$data_title['ip']               = array ('label' => _FORMS_IP, 'sep' => 0, 'type' => '', 'format' => '');

// GET FORM FIELDS
$sql =  "
        SELECT  *
        FROM    ploopi_mod_forms_field
        WHERE   id_form = {$forms_id}
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

$select =   "
            SELECT      fr.*,
                        u.firstname,
                        u.lastname,
                        u.login,
                        w.code,
                        w.label as w_label,
                        m.label as m_label

            FROM        ploopi_mod_forms_reply fr

            INNER JOIN  ploopi_module m
            ON          fr.id_module = m.id
            AND         m.id = {$_SESSION['ploopi']['moduleid']}

            LEFT JOIN   ploopi_user u
            ON          fr.id_user = u.id

            LEFT JOIN   ploopi_workspace w
            ON          fr.id_workspace = w.id

            WHERE   fr.id_form = $forms_id
            AND     fr.id_workspace IN ({$workspaces})
            ";

$rs = $db->query($select);

// construction du jeu de données brut (liste des réponses)
$data = array();
while ($fields = $db->fetchrow($rs))
{
    $c = $fields['id'];

    $data[$c] = array();

    $data[$c]['datevalidation'] = $fields['date_validation'];
    $data[$c]['user'] = $fields['login'];
    $data[$c]['group'] = $fields['w_label'];
    $data[$c]['ip'] = $fields['ip'];
    $data[$c]['id_user'] = $fields['id_user'];
    $data[$c]['id_workspace'] = $fields['id_workspace'];

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
    //ploopi_print_r($array_values);

    foreach($array_fields as $key => $value)
    {
        $data[$c][$key] = (isset($array_values[$key]['value'])) ? str_replace('"','\'',$array_values[$key]['value']) : '';
    }

    foreach($array_fields as $key => $value)
    {
        $data[$c][$key] = (isset($array_values[$key]['value'])) ? $array_values[$key]['value'] : '';
        if ($data_title[$key]['format'] == 'date') $data[$c][$key] = ploopi_local2timestamp(substr($data[$c][$key],0,10), '00:00:00');
    }

}
// compare 2 chaines en ordre naturel
function compare($a, $b)
{
    global $forms_id;

    $orderby = $_SESSION['forms'][$forms_id]['orderby'];
    $option = $_SESSION['forms'][$forms_id]['option'];

    if ($option == 'DESC') return strnatcasecmp($b[$orderby], $a[$orderby]);
    else return strnatcasecmp($a[$orderby], $b[$orderby]);
}

uasort($data, "compare");

// construction du jeu de données filtré
$export = array();
$actual_ts = ploopi_createtimestamp();

foreach ($data as $reply_id => $detail)
{
    $filter_ok = true;
    if (    !$_SESSION['forms'][$forms_id]["unlockbackup"] &&
            (
                ($forms->fields['autobackup'] > 0 && ploopi_timestamp_add($detail['datevalidation'], 0, 0, 0, 0, $forms->fields['autobackup']) < $actual_ts)
            ||  (!empty($forms->fields['autobackup_date']) && $detail['datevalidation'] < ploopi_timestamp_add($forms->fields['autobackup_date'], 0, 0, 0, 0, 1, 0))
            )
        )
    {
        $filter_ok = false;
    }
    else
    {
        for ($l=1;$l<=$lmax;$l++)
        {
            if($filter_ok && isset(${"filter_field_{$l}"}) && ${"filter_field_{$l}"} != '')
            {
                // cas particulier du champ automatique "datevalidation" qui enregistre la date de création au format timestamp
                if (${"filter_field_{$l}"} == 'datevalidation')
                {
                    $val1 = substr($detail[${"filter_field_{$l}"}],0,8).'000000';
                    $val2 = ploopi_local2timestamp(${"filter_value_{$l}"});
                }
                else
                {
                    // cas particulier du format date (on suppose que les données sont saisies au format date FR
                    if (isset($data_title[${"filter_field_{$l}"}]['format']) && $data_title[${"filter_field_{$l}"}]['format'] == 'date')
                    {
                        $val1 = ploopi_local2timestamp($detail[${"filter_field_{$l}"}]);
                        $val2 = ploopi_local2timestamp(${"filter_value_{$l}"});
                    }
                    else
                    {
                        $val1 = strtoupper(ploopi_convertaccents($detail[${"filter_field_{$l}"}]));
                        $val2 = strtoupper(ploopi_convertaccents(${"filter_value_{$l}"}));
                    }
                }

                if ($val2 != '')
                {
                    switch(${"filter_op_{$l}"})
                    {
                        case '=':
                            $list_values = split(';',$val2);
                            $filter_ok = false;
                            foreach($list_values as $val2) $filter_ok = $filter_ok || ($val1 == $val2);
                        break;

                        case '>':
                            $filter_ok = ($val1 > $val2);
                        break;

                        case '<':
                            $filter_ok = ($val1 < $val2);
                        break;

                        case '>=':
                            $filter_ok = ($val1 >= $val2);
                        break;

                        case '<=':
                            $filter_ok = ($val1 <= $val2);
                        break;

                        case 'like':
                            $list_values = split(';',$val2);
                            $filter_ok = false;
                            foreach($list_values as $val2) $filter_ok = $filter_ok || strstr($val1,$val2);
                        break;

                        case 'begin':
                            $list_values = split(';',$val2);
                            $filter_ok = false;
                            foreach($list_values as $val2) $filter_ok = $filter_ok || (strpos($val1,$val2) === 0);
                        break;
                    }
                }
                else $filter_ok = true;
            }
        }

        foreach($detail as $key => $value)
        {
            //ploopi_print_r($data_title);
            if ($key == 'datevalidation')
            {
                $ldate = ploopi_timestamp2local($value);
                $detail[$key] = "{$ldate['date']} {$ldate['time']}";
            }
            elseif (isset($data_title[$key]) && $data_title[$key]['format'] == 'date' && !empty($value))
            {
                $ldate = ploopi_timestamp2local($value);
                $detail[$key] = $ldate['date'];
            }
        }
    }
    if ($filter_ok) $export[$reply_id] = $detail;
}

if ($ploopi_op == 'forms_deletedata' && ploopi_isactionallowed(_FORMS_ACTION_DELETE) && !empty($export))
{
    ploopi_print_r($export);
    $arrExportDelete = array();
    foreach ($export as $reply_id => $detail)
    {
        // Droit de suppression d'un enregistrement
        if (ploopi_isadmin() || (
                ploopi_isactionallowed(_FORMS_ACTION_DELETE) && (
                    ($forms->fields['option_modify'] == 'user' && $detail['id_user'] == $_SESSION['ploopi']['userid']) || 
                    ($forms->fields['option_modify'] == 'group' && $detail['id_workspace'] == $_SESSION['ploopi']['workspaceid'])  || 
                    ($forms->fields['option_modify'] == 'all')
                )
            ))
        {
            $arrExportDelete[$reply_id] = 1;
        } 
    }    
    
    if (!empty($arrExportDelete))
    {
    
        $db->query("
            DELETE  r, rf
            FROM    ploopi_mod_forms_reply r,
                    ploopi_mod_forms_reply_field rf
            WHERE   r.id IN (".implode(',',array_keys($arrExportDelete)).")
            AND     r.id = rf.id_reply
        ");
    }
    
    ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$forms_id}");
}

$_SESSION['forms']['export'] = $export;
$_SESSION['forms']['export_title'] = $data_title;
$_SESSION['forms']['export_fields'] = $array_fields;
?>
