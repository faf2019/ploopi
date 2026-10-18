<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion des partages (table ploopi_share)
 *
 * @package ploopi
 * @subpackage share
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class share extends data_object
{
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_share','id');
    }

    /**
     * Génère l'identifiant d'un bloc de partages
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_module identifiant du module
     *
     * @return string identifiant du bloc
     */

    public static function generateid($id_object = -1, $id_record = -1, $id_module = -1)
    {
        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

        return md5("{$id_object}_{$id_record}_{$id_module}");
    }

    /**
     * Insère le bloc de partage pour un enregistrement d'un objet
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_module identifiant du module
     * @param int $strTitle titre du bloc
     * @param string $strForceShareId identifiant du bloc
     *
     * @return string identifiant du bloc
     */

    public static function selectusers($id_object = -1, $id_record = -1, $id_module = -1, $strTitle = null, $strForceShareId = null, $strPathIcon = null)
    {
        $db = db::get();

        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

        if (is_null($strTitle)) $strTitle = 'Partages';

        $strShareId = is_null($strForceShareId) ? self::generateid($id_object, $id_record, $id_module) : $strForceShareId;

        $_SESSION['ploopi']['share'][$strShareId] = array('users_selected' => array(), 'groups_selected' => array());

        $db->query("
            SELECT  id_share, type_share
            FROM    ploopi_share
            WHERE   id_object = {$id_object}
            AND     id_record = '".addslashes((string) $id_record)."'
            AND     id_module = '".addslashes($id_module)."'
        ");

        while ($row = $db->fetchrow())
        {
            switch($row['type_share'])
            {
                case 'user':
                    $_SESSION['ploopi']['share'][$strShareId]['users_selected'][$row['id_share']] = $row['id_share'];
                break;

                case 'group':
                    $_SESSION['ploopi']['share'][$strShareId]['groups_selected'][$row['id_share']] = $row['id_share'];
                break;
            }
        }

        // ploopi4 : recherche et sélection en pastilles (ploopi\userselect)
        if (userselect::modern())
        {
            userselect::form('share', $strShareId, $strTitle, 'ploopi_share_userfilter_'.$strShareId, crypt::queryencode("ploopi_op=share_search_users&share_id={$strShareId}"), crypt::queryencode("ploopi_op=share_select_user&share_id={$strShareId}"), 'Aucune autorisation');
            return $strShareId;
        }

        ?>
        <a class="ploopi_share_title" href="#" data-ploopi-switchdisplay="ploopi_share_<?php echo $strShareId; ?>">
            <p class="ploopi_va">
                <img src="<?php echo empty($strPathIcon) ? "{$_SESSION['ploopi']['template_path']}/img/share/share.png" : $strPathIcon; ?>">
                <span><?php echo $strTitle; ?></span>
            </p>
        </a>
        <div id="ploopi_share_<?php echo $strShareId; ?>" style="display:block;">
            <div class="ploopi_share_search_form">
                <p class="ploopi_va">
                    <span>Recherche groupes/utilisateurs:&nbsp;</span>
                    <input type="text" id="ploopi_share_userfilter" class="text" data-ploopi-enterclick="ploopi_share_search_<?php echo $strShareId; ?>" />
                    <img id="ploopi_share_search_<?php echo $strShareId; ?>" data-ploopi-search="div_share_search_result_<?php echo $strShareId; ?>" data-ploopi-query="<?php echo str::htmlentities(crypt::queryencode("ploopi_op=share_search_users&share_id={$strShareId}")); ?>" data-ploopi-field="ploopi_share_userfilter" data-ploopi-param="ploopi_share_userfilter" style="border:0px;cursor:pointer;" src="<?php echo "{$_SESSION['ploopi']['template_path']}/img/share/search.png"; ?>">
                </p>
            </div>
            <div id="div_share_search_result_<?php echo $strShareId; ?>"></div>

            <div class="ploopi_share_title">Sélection actuelle :</div>
            <div class="ploopi_share_authorizedlist" id="div_share_users_selected_<?php echo $strShareId; ?>"<?php if (!empty($_SESSION['ploopi']['share'][$strShareId])) echo ' data-ploopi-load="'.str::htmlentities(crypt::queryencode("ploopi_op=share_select_user&share_id={$strShareId}")).'"'; ?>><?php if (empty($_SESSION['ploopi']['share'][$strShareId])) echo 'Aucune autorisation'; ?></div>
        </div>
        <?php

        return $strShareId;
    }

    /**
     * Enregistre les partages sélectionnés pour un enregistrement d'un objet
     *
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_module identifiant du module
     * @param string $strForceShareId identifiant du bloc
     */

    public static function add($id_object = -1, $id_record = -1, $id_module = -1, $strForceShareId = null)
    {
        $db = db::get();

        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

        $strShareId = is_null($strForceShareId) ? self::generateid($id_object, $id_record, $id_module) : $strForceShareId;

        $db->query("DELETE FROM ploopi_share WHERE id_object = {$id_object} AND id_record = '".addslashes((string) $id_record)."' AND id_module = {$id_module}");

        if (!empty($_SESSION['ploopi']['share'][$strShareId]['users_selected']))
        {
            foreach($_SESSION['ploopi']['share'][$strShareId]['users_selected'] as $id_user)
            {
                $share = new self();
                $share->fields = array(
                    'id_module'     => $id_module,
                    'id_record'     => $id_record,
                    'id_object'     => $id_object,
                    'type_share'    => 'user',
                    'id_share'      => $id_user
                );

                $share->save();

            }
        }

        if (!empty($_SESSION['ploopi']['share'][$strShareId]['groups_selected']))
        {
            foreach($_SESSION['ploopi']['share'][$strShareId]['groups_selected'] as $id_group)
            {
                $share = new self();
                $share->fields = array(
                    'id_module'     => $id_module,
                    'id_record'     => $id_record,
                    'id_object'     => $id_object,
                    'type_share'    => 'group',
                    'id_share'      => $id_group
                );

                $share->save();

            }
        }

        unset($_SESSION['ploopi']['share'][$strShareId]);
    }

    /**
     * Renvoie les partages pour un utilisateur, un objet, un enregistrement d'un module
     *
     * @param int $id_user identifiant de l'utilisateur
     * @param int $id_object identifiant de l'objet
     * @param string $id_record identifiant de l'enregistrement
     * @param int $id_module identifiant du module
     * @return array tableau contenant la liste des partages
     */

    public static function get($id_user = -1, $id_object = -1, $id_record = -1,  $id_module = -1)
    {
        $db = db::get();

        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

        $sql =  "SELECT * FROM ploopi_share WHERE id_module = {$id_module}";
        if ($id_object != -1) $sql .= " AND id_object = {$id_object}";
        if ($id_record != -1) $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
        if ($id_user != -1) $sql .= " AND id_share = {$id_user} AND type_share = 'user'";

        $db->query($sql);

        return($db->getarray());
    }

}
