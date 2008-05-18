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
 * Sauvegarde d'un enregistrement d'un formulaire
 *
 * @package forms
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see ploopi_send_form
 */

/**
 * Ouverture du formulaire
 */

$forms = new form();
$forms->open($id_form);

/**
 * Tableau contenant les données du mail à envoyer
 */

$email_array = array();

$isnew = false;

$reply = new reply();
if (!empty($reply_id)) // existant
{
    $reply->open($reply_id);
    $email_array['Formulaire']['Opération'] = 'Modification d\'Enregistrement';
}
else // nouveau
{
    $isnew = true;
    $email_array['Formulaire']['Opération'] = 'Nouvel Enregistrement';
    $reply->fields['date_validation'] = ploopi_createtimestamp();
    $reply->fields['id_module'] = $id_module;
    $reply->fields['id_user'] = $_SESSION['ploopi']['userid'];
    $reply->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];

    $reply->fields['id_object'] = $_SESSION['forms'][$_POST['forms_fuid']]['id_object'];
    $reply->fields['id_record'] = $_SESSION['forms'][$_POST['forms_fuid']]['id_record'];
}

$email_array['Formulaire']['Titre'] = $forms->fields['label'];
$email_array['Formulaire']['Date'] = $reply->fields['date_validation'];

$reply->fields['id_form'] = $id_form;
$reply->fields['ip'] = $_SERVER['REMOTE_ADDR'];
$reply->save();

$email_array['Formulaire']['Adresse IP'] = $reply->fields['ip'];

$sql =  "
        SELECT  *
        FROM    ploopi_mod_forms_field
        WHERE   id_form = {$id_form}
        ORDER BY position
        ";

$rs_fields = $db->query($sql);

/**
 * Pour chaque champs du formulaire
 */

while ($fields = $db->fetchrow($rs_fields))
{
    $value = '';
    $fieldok = false;
    $error = false;

    /**
     * Champs de type fichier, on va devoir déposer le fichier 
     */
    if ($fields['type'] == 'file' && !empty($_FILES['field_'.$fields['id']]['name']))
    {
        $fieldok = true;
        $value = $_FILES['field_'.$fields['id']]['name'];
        $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$id_module._PLOOPI_SEP.$id_form._PLOOPI_SEP.$reply->fields['id']._PLOOPI_SEP;
        $error = ($_FILES['field_'.$fields['id']]['size'] > _PLOOPI_MAXFILESIZE);

        if (!$error)
        {
            ploopi_makedir($path);
            if (file_exists($path) && is_writable($path))
            {
                move_uploaded_file($_FILES['field_'.$fields['id']]['tmp_name'], $path.$value);
                chmod($path.$value, 0640);
            }
        }
    }

    /**
     * Pour tous les champs (sauf autoincrement)
     */
    
    if (isset($_POST['field_'.$fields['id']]))
    {
        $fieldok = true;
        if (is_array($_POST['field_'.$fields['id']]))
        {
            foreach($_POST['field_'.$fields['id']] as $val)
            {
                if ($value != '') $value .= '||';
                $value .= $val;
            }
        }
        else $value = $_POST['field_'.$fields['id']];
    }
    else
    {
        /**
         * Calcul de l'autoincrement s'il s'agit d'un nouvel enregistrement
         */
        
        if ($fields['type'] == 'autoincrement' && $isnew) // not in form => need to be calculated
        {
            $fieldok = true;
            $select = "SELECT max(value*1) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_form = '{$id_form}' AND id_field = '{$fields['id']}'";
            $rs_maxinc = $db->query($select);
            $fields_maxinc = $db->fetchrow($rs_maxinc);
            $value = ($fields_maxinc['maxinc'] == '' || $fields_maxinc['maxinc'] == 0) ? 1 : $fields_maxinc['maxinc']+1;
        }
    }

    if ($fieldok = true)
    {
        $reply_field = new reply_field();
        if (isset($reply_id))
        {
            $reply_field->open($reply_id, $fields['id']);
        }

        $reply_field->fields['id_field'] = $fields['id'];
        $reply_field->fields['id_form'] = $id_form;
        $reply_field->fields['id_reply'] = $reply->fields['id'];

        if (!(($fields['type'] == 'autoincrement' || $fields['type'] == 'file') && $value == '')) $reply_field->fields['value'] = $value;

        $reply_field->save();

        $email_array['Contenu']["({$fields['id']}) {$fields['name']}"] = $reply_field->fields['value'];
    }

}

if ($forms->fields['email'] != '')
{
    $list_email = explode(';',$forms->fields['email']);
    foreach($list_email as $email)
    {
        $from[0] = array('name' => $email, 'address' => $email);
        $to[] = array('name' => $email, 'address' => $email);
    }
    /**
     * Envoi du formulaire par mail
     */
    
    ploopi_send_form($from, $to, $email_array['Formulaire']['Titre'], $email_array);
}

?>
