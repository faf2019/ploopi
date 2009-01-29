<?php
/*
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
 * Affichage des favoris
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

// Récupération des rubriques de contacts partagés
$arrHeadings = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts'] ? directory_getheadings() : array();

/**
 * Création du tableau des favoris
 */

if (!empty($_GET['directory_favorites_id_list']) && is_numeric($_GET['directory_favorites_id_list'])) $_SESSION['directory']['id_list'] = $_GET['directory_favorites_id_list'];
$id_list = $_SESSION['directory']['id_list'];

$where = (empty($id_list)) ? '' : " AND f.id_list = {$id_list}";

$columns = array();
$values = array();

$columns['auto']['groups'] = array('label' => _DIRECTORY_GROUPS,    'options' => array('sort' => true));
$columns['left']['type'] = array('label' => _DIRECTORY_TYPE,        'width' => 90, 'options' => array('sort' => true));
$columns['left']['name'] = array('label' => _DIRECTORY_NAME,        'width' => 150, 'options' => array('sort' => true));
$columns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
$columns['right']['ticket'] = array('label' => _DIRECTORY_TICKET,     'width' => 55);
$columns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
$columns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
$columns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => 42);


$result = array();

$sql =  "
        SELECT  c.*, 'contact' as usertype, '' as login

        FROM    ploopi_mod_directory_contact c,
                ploopi_mod_directory_favorites f

        WHERE   f.id_user = {$_SESSION['ploopi']['userid']}
        AND     f.id_contact = c.id
        {$where}
        
        GROUP BY c.id
        ";

$db->query($sql);
while ($row = $db->fetchrow()) $result[] = $row;

$sql =  "
        SELECT  u.*,
                'user' as usertype

        FROM    ploopi_user u,
                ploopi_mod_directory_favorites f
                
        WHERE   f.id_user = {$_SESSION['ploopi']['userid']}
        AND     f.id_ploopi_user = u.id
        {$where}

        GROUP BY u.id
        ";

$db->query($sql);
while ($row = $db->fetchrow()) $result[] = $row;

$c = 0;
foreach($result as $row)
{
    $email = ($row['email']) ? '<a href="mailto:'.htmlentities($row['email']).'" title="'.htmlentities(_DIRECTORY_SEND_EMAIL.': '.$row['email']).'"><img src="./modules/directory/img/ico_email.png"></a>' : '&nbsp;';
    $ticket = '&nbsp;';
    
    switch ($row['usertype'])
    {
        case 'user':
            $actions =  '
                        <a href="javascript:void(0);" onclick="javascript:directory_view(event, \''.$row['id'].'\', \'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \''.$row['id'].'\', \'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>
                        ';
            
            $field_id = 'user_id';
            $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'])) ? _DIRECTORY_USERS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'];

            // on va chercher les espaces auxquels l'utilisateur peut accéder
            $user = new user();
            $user->open($row['id']);
            $user_ws = $user->getworkspaces();

            // on met les libellés dans un tableau
            $workspaces_list = array();
            foreach($user_ws as $ws) $workspaces_list[sprintf("%04d%s", $ws['depth'], $ws['label'])] = $ws['label'];

            // on trie par profondeur + libellé
            ksort($workspaces_list);

            // on met tout ça dans une chaine
            $workspaces_list = implode(', ',$workspaces_list);

            //$values[$c]['link'] = 'javascript:void(0);';
            //$values[$c]['onclick'] = "javascript:directory_view(event, '{$row['id']}', '');";
            $ticket = '<a href="javascript:void(0);" onclick="javascript:ploopi_tickets_new(event, null, null, null, '.$row['id'].');"><img title="'._DIRECTORY_SEND_TICKET.'" src="./modules/directory/img/ico_ticket.png"></a>';
        break;

        case 'contact':
            $actions =  '
                        <a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>
                        ';
            
            if (empty($row['id_heading'])) // contact perso
            {
                $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'];
                $workspaces_list = '';                
            }
            else // contact partagé
            {
                $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'])) ? _DIRECTORY_SHAREDCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'];

                if (isset($arrHeadings['list'][$row['id_heading']]['parents']))
                {
                    $arrParents = split(';', $arrHeadings['list'][$row['id_heading']]['parents']);
                    $arrTitle  = array();
                    foreach($arrParents as $intId)
                        if (isset($arrHeadings['list'][$intId]))
                            $arrTitle[] = $arrHeadings['list'][$intId]['label'];
    
                    $arrTitle[] = $arrHeadings['list'][$row['id_heading']]['label'];
                    $workspaces_list = implode(' > ', $arrTitle);
                }
                else $workspaces_list = '';
            }
        break;
    }

    
    $values[$c]['values']['type'] = array('label' => $level_display);
    $values[$c]['values']['name'] = array('label' => "{$row['lastname']} {$row['firstname']}");
    $values[$c]['values']['groups'] = array('label' => $workspaces_list);
    $values[$c]['values']['service'] = array('label' => ($row['service']) ? $row['service'] : '&nbsp;');
    $values[$c]['values']['function'] = array('label' => ($row['function']) ? $row['function'] : '&nbsp;');
    $values[$c]['values']['phone'] = array('label' => ($row['phone']) ? $row['phone'] : '&nbsp;');
    $values[$c]['values']['email'] = array('label' => $email);
    $values[$c]['values']['ticket'] = array('label' => $ticket);
    $values[$c]['values']['actions'] = array('label' => $actions);

    $values[$c]['description'] = "{$row['lastname']} {$row['firstname']}";
    $values[$c]['style'] = '';

    $c++;
}

$skin->display_array($columns, $values, 'array_directory', array('sortable' => true, 'orderby_default' => 'name'));
?>