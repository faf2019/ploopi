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
 * Fonctions de gestion des validateurs
 * Permet de gérer des validateurs sur un enregistrement d'un objet.
  *
 * @package ploopi
 * @subpackage validation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Génère l'identifiant d'un bloc de validateur
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 *
 * @return string identifiant du bloc
 */

function ploopi_validation_generateid($id_object = -1, $id_record = -1, $id_module = -1)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    return md5("{$id_object}_{$id_record}_{$id_module}");
}


/**
 * Insère le bloc de sélection des validateurs pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param unknown_type $id_action identifiant de l'action requise
 */

function ploopi_validation_selectusers($id_object = 0, $id_record = '', $id_module = -1, $id_action = -1, $strTitle = null, $strForceValidationId = null)
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    if (is_null($strTitle)) $strTitle = 'Validateurs';

    $strValidationId = is_null($strForceValidationId) ? ploopi_validation_generateid($id_object, $id_record, $id_module) : $strForceValidationId;

    $_SESSION['ploopi']['validation'][$strValidationId] = array('users_selected' => array(), 'groups_selected' => array());

    $db->query("
        SELECT  id_validation, type_validation
        FROM    ploopi_validation
        WHERE   id_object = {$id_object}
        AND     id_record = '".addslashes($id_record)."'
        AND     id_module = '".addslashes($id_module)."'
    ");

    while ($row = $db->fetchrow())
    {
        switch($row['type_validation'])
        {
            case 'user':
                $_SESSION['ploopi']['validation'][$strValidationId]['users_selected'][$row['id_validation']] = $row['id_validation'];
            break;

            case 'group':
                $_SESSION['ploopi']['validation'][$strValidationId]['groups_selected'][$row['id_validation']] = $row['id_validation'];
            break;
        }
    }

    ?>
    <a class="ploopi_validation_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('ploopi_validation_<?php echo $strValidationId; ?>');">
        <p class="ploopi_va">
            <img src="<?php echo "{$_SESSION['ploopi']['template_path']}/img/validation/validation.png"; ?>">
            <span><?php echo $strTitle; ?></span>
        </p>
    </a>
    <div id="ploopi_validation_<?php echo $strValidationId; ?>" style="display:block;">
        <div class="ploopi_validation_search_form">
            <p class="ploopi_va">
                <span>Recherche groupes/utilisateurs:&nbsp;</span>
                <input type="text" id="ploopi_validation_userfilter_<?php echo $strValidationId; ?>" class="text">
                <img onmouseover="javascript:this.style.cursor='pointer';" onclick="ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=validation_search_users&validation_id=<?php echo $strValidationId; ?>&ploopi_validation_userfilter='+ploopi_getelem('ploopi_validation_userfilter_<?php echo $strValidationId; ?>').value+'&id_action=<?php echo $id_action; ?>', 'div_validation_search_result_<?php echo $strValidationId; ?>');" style="border:0px" src="<?php echo "{$_SESSION['ploopi']['template_path']}/img/validation/search.png"; ?>">
            </p>
        </div>
        <div id="div_validation_search_result_<?php echo $strValidationId; ?>"></div>

        <div class="ploopi_validation_title">Sélection actuelle :</div>
        <div class="ploopi_validation_authorizedlist" id="div_validation_users_selected_<?php echo $strValidationId; ?>">
        <?php if (empty($_SESSION['ploopi']['validation'][$strValidationId]['users_selected'])) echo 'Aucune accrédidation'; ?>
        </div>
        <?php
        if (!empty($_SESSION['ploopi']['validation'][$strValidationId]))
        {
            ?>
            <script type="text/javascript">
                ploopi_ajaxloader('div_validation_users_selected_<?php echo $strValidationId; ?>');
                ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=validation_select_user&validation_id=<?php echo $strValidationId; ?>', 'div_validation_users_selected_<?php echo $strValidationId; ?>');
            </script>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Enregistre les validateurs pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param string $strForceValidationId identifiant du bloc
 */

function ploopi_validation_save($id_object = 0, $id_record = '', $id_module = -1, $strForceValidationId = null)
{
    global $db;
    include_once './include/classes/validation.php';

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $strValidationId = is_null($strForceValidationId) ? ploopi_validation_generateid($id_object, $id_record, $id_module) : $strForceValidationId;

    $db->query("DELETE FROM ploopi_validation WHERE id_object = {$id_object} AND id_record = '".$db->addslashes($id_record)."' AND id_module = {$id_module}");

    if (!empty($_SESSION['ploopi']['validation'][$strValidationId]['users_selected']))
    {
        foreach($_SESSION['ploopi']['validation'][$strValidationId]['users_selected'] as $id_user)
        {
            $validation = new validation();
            $validation->fields =
                array(
                    'id_module' => $id_module,
                    'id_record' => $id_record,
                    'id_object' => $id_object,
                    'type_validation' => 'user',
                    'id_validation' => $id_user
                );
            $validation->save();

        }
    }

    if (!empty($_SESSION['ploopi']['validation'][$strValidationId]['groups_selected']))
    {
        foreach($_SESSION['ploopi']['validation'][$strValidationId]['groups_selected'] as $id_group)
        {
            $validation = new validation();
            $validation->fields =
                array(
                    'id_module' => $id_module,
                    'id_record' => $id_record,
                    'id_object' => $id_object,
                    'type_validation' => 'group',
                    'id_validation' => $id_group
                );
            $validation->save();

        }
    }

    unset($_SESSION['ploopi']['validation'][$strValidationId]);
}

/**
 * Renvoie les informations de validation en fonction d'un utilisateur, d'un objet ou d'un enregistrement
 *
 * @param int $id_object identifiant de l'objet
 * @param string/array $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param int/array $id_val identifiant de l'utilisateur ou du groupe
 * @param string $type_val type de validateur (user/group)
 * @return array validation
 */

function ploopi_validation_get($id_object = 0, $id_record = '', $id_module = -1, $id_val = 0, $type_val = 'user')
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $sql =  "SELECT * FROM ploopi_validation WHERE id_module = {$id_module}";
    if ($id_object != 0) $sql .= " AND id_object = {$id_object}";
    if ($id_record != '')
    {
        if(is_array($id_record))
        {
            $value = '\'0';
            foreach($id_record as $id) $value .= '\',\''.$db->addslashes($id);
            $value .= '\'';

            $sql .= " AND id_record IN ({$value})";
        }
        else
            $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
    }

    if (!empty($id_val))
    {
        if(is_array($id_val))
        {
            $id_val = implode(',',$id_val);
            switch($type_val)
            {
                case 'user' : $sql .= " AND id_validation IN ({$id_val}) AND type_validation = 'user'"; break;
                case 'group' : $sql .= " AND id_validation IN ({$id_val}) AND type_validation = 'group'"; break;
            }
        }
        else
        {
            switch($type_val)
            {
                case 'user' : $sql .= " AND id_validation = {$id_val} AND type_validation = 'user'"; break;
                case 'group' : $sql .= " AND id_validation = {$id_val} AND type_validation = 'group'"; break;
            }
        }
    }

    $db->query($sql);

    return($db->getarray());
}

/**
 * Supprime les informations de validation attachées à un objet/enregistrement/utilisateur
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param int $id_val identifiant de l'utilisateur ou du groupe
 * @param string $type_val type de validateur (user/group)
 */
function ploopi_validation_delete($id_object = 0, $id_record = '', $id_module = -1, $id_val = 0, $type_val = 'user')
{
    global $db;

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $sql = "DELETE FROM ploopi_validation WHERE id_module = {$id_module}";
    if ($id_object != 0) $sql .= " AND id_object = {$id_object}";
    if ($id_record != '') $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
    if ($id_val != 0)
    {
        switch($type_val)
        {
            case 'user' : $sql .= " AND id_validation = {$id_user} AND type_validation = 'user'"; break;
            case 'group' : $sql .= " AND id_validation = {$id_user} AND type_validation = 'group'"; break;
        }
    }

    $db->query($sql);
}
