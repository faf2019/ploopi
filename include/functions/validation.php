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
 * Fonctions de gestion du validation interne (attention concept de validation très léger).
 * Permet de gérer des validateurs sur un enregistrement d'un objet.
  * 
 * @package ploopi
 * @subpackage validation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Insère le bloc de sélection des validateurs pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param unknown_type $id_action identifiant de l'action requise
 */

function ploopi_validation_selectusers($id_object = 0, $id_record = 0, $id_module = -1, $id_action = -1)
{
    global $db;

    if (isset($_SESSION['ploopi']['validation']['users_selected'])) unset($_SESSION['ploopi']['validation']['users_selected']);

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $sql =   "
            SELECT  id_validation 
            FROM    ploopi_validation 
            WHERE   id_object = {$id_object} 
            AND     id_record = '".$db->addslashes($id_record)."' 
            AND     id_module = {$id_module}
            ";
    $db->query($sql);
    while ($row = $db->fetchrow())
    {
        $_SESSION['ploopi']['validation']['users_selected'][$row['id_validation']] = $row['id_validation'];
    }

    ?>
    <a class="ploopi_validation_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('ploopi_validation');">
        <p class="ploopi_va">
            <img src="<? echo "{$_SESSION['ploopi']['template_path']}/img/validation/validation.png"; ?>">
            <span>Validateurs</span>
        </p>
    </a>
    <div id="ploopi_validation" style="display:block;">
        <div class="ploopi_validation_search_form">
            <p class="ploopi_va">
                <span>Recherche groupes/utilisateurs:&nbsp;</span>
                <input type="text" id="ploopi_validation_userfilter" class="text">
                <img onmouseover="javascript:this.style.cursor='pointer';" onclick="ploopi_xmlhttprequest_todiv('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=validation_search_users&ploopi_validation_userfilter='+ploopi_getelem('ploopi_validation_userfilter').value+'&id_action=<? echo $id_action; ?>', 'div_validation_search_result');" style="border:0px" src="<? echo "{$_SESSION['ploopi']['template_path']}/img/validation/search.png"; ?>">
            </p>
        </div>
        <div id="div_validation_search_result"></div>

        <div class="ploopi_validation_title">Accréditations :</div>
        <div class="ploopi_validation_authorizedlist" id="div_validation_users_selected">
        <? if (empty($_SESSION['ploopi']['validation']['users_selected'])) echo 'Aucune accrédidation'; ?>
        </div>
        <?
        if (!empty($_SESSION['ploopi']['validation']['users_selected']))
        {
            ?>
            <script type="text/javascript">
                ploopi_ajaxloader('div_validation_users_selected');
                ploopi_xmlhttprequest_todiv('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=validation_select_user', 'div_validation_users_selected');
            </script>
            <?
        }
        ?>
    </div>
    <?
}


/**
 * Enregistre les validateurs pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 */
 
function ploopi_validation_save($id_object = 0, $id_record = 0, $id_module = -1)
{
    global $db;
    include_once './include/classes/validation.php';

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $db->query("DELETE FROM ploopi_validation WHERE id_object = {$id_object} AND id_record = '".$db->addslashes($id_record)."' AND id_module = {$id_module}");

    if (!empty($_SESSION['ploopi']['validation']['users_selected']))
    {
        foreach($_SESSION['ploopi']['validation']['users_selected'] as $id_user)
        {
            $validation = new validation();
            $validation->fields = array(  'id_module'     => $id_module,
                                        'id_record'     => $id_record,
                                        'id_object'     => $id_object,
                                        'type_validation' => 'user',
                                        'id_validation'   => $id_user
                                );
            $validation->save();

        }
        
        unset($_SESSION['ploopi']['validation']['users_selected']);
    }
}

/**
 * Renvoie les informations du validation en fonction d'un utilisateur, d'un objet ou d'un enregistrement
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 * @param int $id_user identifiant de l'utilisateur
 * @return array validation
 */

function ploopi_validation_get($id_object = 0, $id_record = 0,  $id_module = -1, $id_user = 0)
{
    global $db;

    $validation = array();

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $sql =  "SELECT * FROM ploopi_validation WHERE id_module = {$id_module}";
    if ($id_object != 0) $sql .= " AND id_object = {$id_object}";
    if ($id_record != 0) $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
    if ($id_user != 0) $sql .= " AND id_validation = {$id_user} AND type_validation = 'user'";
    
    $db->query($sql);

    while ($row = $db->fetchrow())
    {
        $validation[] = $row;
    }

    return($validation);
}
?>
