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
 * Fonctions de gestion des partages sur des enregistrements d'objets.
 *
 * @package ploopi
 * @subpackage share
 * @copyright Netlor, Ovensia
 * @license GPL
 */

/**
 * Insère le bloc de partage pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 */

function ploopi_shares_selectusers($id_object = -1, $id_record = -1, $id_module = -1)
{
    global $db;

    if (isset($_SESSION['ploopi']['shares']['users_selected'])) unset($_SESSION['ploopi']['shares']['users_selected']);

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $db->query("SELECT id_share FROM ploopi_share WHERE id_object = {$id_object} AND id_record = '".addslashes($id_record)."' AND id_module = {$id_module}");
    while ($row = $db->fetchrow())
    {
        $_SESSION['ploopi']['shares']['users_selected'][$row['id_share']] = $row['id_share'];
    }

    ?>
    <a class="ploopi_shares_title" href="#" onclick="javascript:ploopi_switchdisplay('ploopi_shares');">
        <p class="ploopi_va">
            <img src="<? echo "{$_SESSION['ploopi']['template_path']}/img/shares/shares.png"; ?>">
            <span>Partages</span>
        </p>
    </a>
    <div id="ploopi_shares" style="display:block;">
        <div class="ploopi_shares_search_form">
            <p class="ploopi_va">
                <span>Recherche groupes/utilisateurs:&nbsp;</span>
                <input type="text" id="ploopi_shares_userfilter" class="text">
                <img onmouseover="javascript:this.style.cursor='pointer';" onclick="ploopi_xmlhttprequest_todiv('index-light.php','ploopi_op=shares_search_users&ploopi_shares_userfilter='+ploopi_getelem('ploopi_shares_userfilter').value,'','div_shares_search_result');" style="border:0px" src="<? echo "{$_SESSION['ploopi']['template_path']}/img/shares/search.png"; ?>">
            </p>
        </div>
        <div id="div_shares_search_result"></div>

        <div class="ploopi_shares_title">Autorisations :</div>
        <div class="ploopi_shares_authorizedlist" id="div_shares_users_selected"><? if (empty($_SESSION['ploopi']['shares']['users_selected'])) echo 'Aucune autorisation'; ?></div>
        <?
        if (!empty($_SESSION['ploopi']['shares']['users_selected']))
        {
            ?>
            <script type="text/javascript">
                ploopi_ajaxloader('div_shares_users_selected');
                ploopi_xmlhttprequest_todiv('index-light.php','ploopi_op=shares_select_user','','div_shares_users_selected')
            </script>
            <?
        }
        ?>
    </div>
    <?
}

/**
 * Enregistre les partages sélectionnés pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_module identifiant du module
 */

function ploopi_shares_save($id_object = -1, $id_record = -1, $id_module = -1)
{
    global $db;
    include_once './include/classes/class_share.php';

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $db->query("DELETE FROM ploopi_share WHERE id_object = {$id_object} AND id_record = '".addslashes($id_record)."' AND id_module = {$id_module}");

    if (!empty($_SESSION['ploopi']['shares']['users_selected']))
    {
        foreach($_SESSION['ploopi']['shares']['users_selected'] as $id_user)
        {
            $share = new share();
            $share->fields = array( 'id_module'     => $id_module,
                                    'id_record'     => $id_record,
                                    'id_object'     => $id_object,
                                    'type_share'    => 'user',
                                    'id_share'      => $id_user
                                );
            $share->save();

        }
    }
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
 
function ploopi_shares_get($id_user = -1, $id_object = -1, $id_record = -1,  $id_module = -1)
{
    global $db;
    
    $shares = array();

    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    $sql =  "SELECT * FROM ploopi_share WHERE id_module = {$id_module}";
    if ($id_object != -1) $sql .= " AND id_object = {$id_object}";
    if ($id_record != -1) $sql .= " AND id_record = '".$db->addslashes($id_record)."'";
    if ($id_user != -1) $sql .= " AND id_share = {$id_user} AND type_share = 'user'";

    $db->query($sql);

    while ($row = $db->fetchrow())
    {
        $shares[] = $row;
    }

    return($shares);
}
?>
