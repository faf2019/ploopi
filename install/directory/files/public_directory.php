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

echo $skin->open_simplebloc($title);
?>
<div style="padding:4px;background-color:#e0e0e0;border-bottom:2px solid #c0c0c0;">
<? echo $desc; ?>
</div>

<div style="overflow:hidden;">

<?
// get user favorites
// ==================

$favorites = array();
$sql =  "
        SELECT  *
        FROM    ploopi_mod_directory_favorites
        WHERE   id_user = {$_SESSION['ploopi']['userid']}
        ";
$db->query($sql);

while ($row = $db->fetchrow())
{
    if ($row['id_contact']) $favorites["contact_{$row['id_contact']}"] = $row;
    if ($row['id_ploopi_user']) $favorites["user_{$row['id_ploopi_user']}"] = $row;
}


switch($_SESSION['directory']['directoryTabItem'])
{
    case 'tabMycontacts':
        $directory_contact = new directory_contact();
        $directory_contact->init_description();
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:$('directory_addcontact').style.display='block';">
            <p class="ploopi_va">
                <img border="0" src="./modules/directory/img/ico_add_contact.png">
                <span><? echo _DIRECTORY_ADDNEWCONTACT; ?></span>
            </p>
        </a>
        <div id="directory_addcontact" style="display:none;">
            <? include_once './modules/directory/public_directory_form.php'; ?>
        </div>
        <?
    break;

    case 'tabSearch':
        if (isset($_POST['fulltext']))  $_SESSION['directory']['search']['fulltext'] = $_POST['fulltext'];
        if (isset($_POST['lastname']))  $_SESSION['directory']['search']['lastname'] = $_POST['lastname'];
        if (isset($_POST['firstname'])) $_SESSION['directory']['search']['firstname'] = $_POST['firstname'];
        if (isset($_POST['service']))   $_SESSION['directory']['search']['service'] = $_POST['service'];
        if (isset($_POST['function']))  $_SESSION['directory']['search']['function'] = $_POST['function'];
        if (isset($_POST['city']))      $_SESSION['directory']['search']['city'] = $_POST['city'];
        if (isset($_POST['country']))   $_SESSION['directory']['search']['country'] = $_POST['country'];
        if (isset($_POST['group']))     $_SESSION['directory']['search']['group'] = $_POST['group'];

        if (!isset($_SESSION['directory']['search']['fulltext']))   $_SESSION['directory']['search']['fulltext'] = '';
        if (!isset($_SESSION['directory']['search']['lastname']))   $_SESSION['directory']['search']['lastname'] = '';
        if (!isset($_SESSION['directory']['search']['firstname']))  $_SESSION['directory']['search']['firstname'] = '';
        if (!isset($_SESSION['directory']['search']['service']))    $_SESSION['directory']['search']['service'] = '';
        if (!isset($_SESSION['directory']['search']['function']))   $_SESSION['directory']['search']['function'] = '';
        if (!isset($_SESSION['directory']['search']['city']))       $_SESSION['directory']['search']['city'] = '';
        if (!isset($_SESSION['directory']['search']['country']))    $_SESSION['directory']['search']['country'] = '';
        if (!isset($_SESSION['directory']['search']['group']))      $_SESSION['directory']['search']['group'] = '';

        ?>
        <div style="padding:4px;background-color:#f0f0f0;border-bottom:2px solid #c0c0c0;">
            <form action="<? echo $scriptenv; ?>" method="post">
            <table cellpadding="2" cellspacing="1">
            <input type="hidden" name="op" value="search">
            <tr>
                <td align="right"><? echo _DIRECTORY_NAME; ?>: </td>
                <td><input type="text" class="text" size="20" name="lastname" value="<? echo $_SESSION['directory']['search']['lastname']; ?>" /></td>
                <td align="right"><? echo _DIRECTORY_SERVICE; ?>: </td>
                <td><input type="text" class="text" size="20" name="service" value="<? echo $_SESSION['directory']['search']['service']; ?>" /></td>
                <td align="right"><? echo _DIRECTORY_CITY; ?>: </td>
                <td><input type="text" class="text" size="20" name="city" value="<? echo $_SESSION['directory']['search']['city']; ?>" /></td>
            </tr>
            <tr>
                <td align="right"><? echo _DIRECTORY_FIRSTNAME; ?>: </td>
                <td><input type="text" class="text" size="20" name="firstname" value="<? echo $_SESSION['directory']['search']['firstname']; ?>" /></td>
                <td align="right"><? echo _DIRECTORY_FUNCTION; ?>: </td>
                <td><input type="text" class="text" size="20" name="function" value="<? echo $_SESSION['directory']['search']['function']; ?>" /></td>
                <td align="right"><? echo _DIRECTORY_COUNTRY; ?>: </td>
                <td><input type="text" class="text" size="20" name="country" value="<? echo $_SESSION['directory']['search']['country']; ?>" /></td>
            </tr>
            <tr>
                <td align="right"><? echo _DIRECTORY_FULLTEXT; ?>: </td>
                <td><input type="text" class="text" size="20" name="fulltext" value="<? echo $_SESSION['directory']['search']['fulltext']; ?>" /></td>
                <td colspan="4" align="right"><input type="submit" class="button" value="<? echo _PLOOPI_SEARCH; ?>"></td>
            </tr>
            </table>
            </form>
        </div>
        <?
    break;
}


switch($_SESSION['directory']['directoryTabItem'])
{
    /* CONTACTS
     * ======== */

    case 'tabMycontacts':
        $columns = array();
        $values = array();

        $columns['auto']['name'] = array('label' => _DIRECTORY_NAME, 'options' => array('sort' => true));
        $columns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $columns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $columns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
        $columns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
        $columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => 82);

        $sql =  "
                SELECT  *
                FROM    ploopi_mod_directory_contact
                WHERE   id_user = {$_SESSION['ploopi']['userid']}
                ";

        $db->query($sql);

        $c = 0;
        while ($row = $db->fetchrow())
        {
            $email = ($row['email']) ? '<img src="./modules/directory/img/ico_email.png">' : '';

            
            $actions =  '
                        <a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>
                        <a href="'.ploopi_urlencode("{$scriptenv}?op=directory_modify&contact_id={$row['id']}").'"><img title="Modifier" src="./modules/directory/img/ico_modify.png"></a>
                        <a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("{$scriptenv}?op=directory_delete&contact_id={$row['id']}").'\',\''._DIRECTORY_CONFIRM_DELETECONTACT.'\')"><img title="Supprimer" src="./modules/directory/img/ico_delete.png"></a>
                        ';

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites']) 
            {
                if (!isset($favorites["contact_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
            }
            
            $values[$c]['values']['name'] = array('label' => "{$row['lastname']} {$row['firstname']}");
            $values[$c]['values']['service'] = array('label' => $row['service']);
            $values[$c]['values']['function'] = array('label' => $row['function']);
            $values[$c]['values']['phone'] = array('label' => $row['phone']);
            $values[$c]['values']['email'] = array('label' => $email);
            $values[$c]['values']['actions'] = array('label' => $actions);

            $values[$c]['description'] = "{$row['lastname']} {$row['firstname']}";
            $values[$c]['link'] = 'javascript:void(0);';
            $values[$c]['onclick'] = "javascript:directory_view(event, '', '{$row['id']}');";
            $values[$c]['style'] = '';

            $c++;
        }

        $skin->display_array($columns, $values, 'array_directory', array('sortable' => true, 'orderby_default' => 'name'));
    break;

    /* GROUPS
     * ====== */

    case 'tabMygroup':
        $columns = array();
        $values = array();

        $columns['auto']['groups'] = array('label' => _DIRECTORY_GROUPS,    'options' => array('sort' => true));
        $columns['left']['name'] = array('label' => _DIRECTORY_NAME,        'width' => 150, 'options' => array('sort' => true));
        $columns['left']['login'] = array('label' => _DIRECTORY_LOGIN,      'width' => 100, 'options' => array('sort' => true));
        $columns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $columns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $columns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
        $columns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
        $columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '42');

        // il faut chercher les groupes rattachés à l'espace !
        include_once './modules/system/class_workspace.php';
        $workspace = new workspace();
        $workspace->fields['id'] = $_SESSION['ploopi']['workspaceid'];
        $groups = $workspace->getgroups(true);
        $list_groups = (sizeof($groups)) ? ' IN ('.implode(',',array_keys($groups)).') ' : ' = -1 ';

        $sql =  "
                SELECT      u.*

                FROM        ploopi_user u

                LEFT JOIN   ploopi_group_user gu
                ON          gu.id_user = u.id

                LEFT JOIN   ploopi_workspace_user wu
                ON          wu.id_user = u.id

                WHERE       (wu.id_workspace = {$_SESSION['ploopi']['workspaceid']} OR gu.id_group {$list_groups})

                GROUP BY    u.id
                ";

        $res = $db->query($sql);

        $c = 0;
        while ($row = $db->fetchrow($res))
        {
            $email = ($row['email']) ? '<img src="./modules/directory/img/ico_email.png">' : '';

            $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \''.$row['id'].'\', \'\');"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>';

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
            {
                //if (!isset($favorites["user_{$row['id']}"])) $actions .='<a href="'.ploopi_urlencode("{$scriptenv}?op=directory_favorites_add&user_id={$row['id']}").'"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                if (!isset($favorites["user_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
            }

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

            $values[$c]['values']['name'] = array('label' => "{$row['lastname']} {$row['firstname']}");
            $values[$c]['values']['login'] = array('label' => $row['login']);
            $values[$c]['values']['groups'] = array('label' => $workspaces_list);
            $values[$c]['values']['service'] = array('label' => $row['service']);
            $values[$c]['values']['function'] = array('label' => $row['function']);
            $values[$c]['values']['phone'] = array('label' => $row['phone']);
            $values[$c]['values']['email'] = array('label' => $email);
            $values[$c]['values']['actions'] = array('label' => $actions);

            $values[$c]['description'] = "{$row['lastname']} {$row['firstname']}";
            $values[$c]['link'] = 'javascript:void(0);';
            $values[$c]['onclick'] = "javascript:directory_view(event, '{$row['id']}', '');";            
            $values[$c]['style'] = '';

            $c++;
        }

        $skin->display_array($columns, $values, 'array_directory', array('sortable' => true, 'orderby_default' => 'name'));

    break;

    /* FAVORITES
     * ========= */

    case 'tabFavorites':
        
        if (!empty($_GET['directory_favorites_id_list']) && is_numeric($_GET['directory_favorites_id_list'])) $_SESSION['directory']['id_list'] = $_GET['directory_favorites_id_list'];
        if (!isset($_SESSION['directory']['id_list'])) $_SESSION['directory']['id_list'] = 0;

        $id_list = $_SESSION['directory']['id_list'];
        ?>
        <div style="padding:4px;background-color:#d8d8d8;border-bottom:2px solid #c0c0c0;">
            <p class="ploopi_va">
                <span>Choix de la liste à afficher :</span>
                <select class="select" onchange="javascript:directory_list_change(this);" id="directory_favorites_id_list">
                <option value="0">(tous)</option>
                <?
                // get lists
                $sql =  "
                        SELECT      l.*, IF(ISNULL(f.id_list),0,count(*)) as nbfav 
                        
                        FROM        ploopi_mod_directory_list l
                        
                        LEFT JOIN   ploopi_mod_directory_favorites f
                        ON          f.id_list = l.id
                        
                        WHERE       l.id_module = {$_SESSION['ploopi']['moduleid']} 
                        AND         l.id_workspace = {$_SESSION['ploopi']['workspaceid']} 
                        AND         l.id_user = {$_SESSION['ploopi']['userid']} 
                        
                        GROUP BY    l.id
                        
                        ORDER BY    l.label
                        ";
                        
                $db->query($sql);
                $arrLists = $db->getarray();
                foreach($arrLists as $row)
                {
                    ?>
                    <option <? if ($id_list == $row['id']) echo 'selected'; ?> value="<? echo $row['id']; ?>"><? echo $row['label']; ?> (<? echo $row['nbfav']; ?> fav)</option>
                    <?
                }
                ?>
                </select>
                <img src="./modules/directory/img/ico_newlist.png" title="Ajouter une liste" onclick="javascript:directory_list_addnew(event);" style="cursor:pointer;"/>
                <img src="./modules/directory/img/ico_modify.png" title="Modifier la liste sélectionnée" onclick="javascript:directory_list_modify(event);" style="cursor:pointer;display:<? echo ($id_list>0) ? 'inline' : 'none'; ?>;" id="directory_list_modify_link" />
                <img src="./modules/directory/img/ico_delete.png" title="Supprimer la liste sélectionnée" onclick="javascript:ploopi_confirmlink('<? echo "{$scriptenv}?op=directory_list_delete&directory_favorites_id_list="; ?>'+$('directory_favorites_id_list').value, 'Êtes vous certain de vouloir supprimer cette liste ?');" style="cursor:pointer;display:<? echo ($id_list>0) ? 'inline' : 'none'; ?>;" id="directory_list_delete_link" />
                
                <?
                if (empty($arrLists))
                {
                    ?><span><i>Attention, vous devez ajouter au moins une liste pour gérer vos favoris !</i></span><?
                }
                ?>
            </p>
        </div>
        <div id="directory_favorites_list">
            <? include_once './modules/directory/public_favorites.php'; ?>
        </div>
        <?
    break;

    /* SEARCH
     * ====== */

    case 'tabSearch':
        $columns = array();
        $values = array();

        $columns['auto']['groups'] = array('label' => _DIRECTORY_GROUPS,    'options' => array('sort' => true));
        $columns['left']['type'] = array('label' => _DIRECTORY_TYPE,        'width' => 90, 'options' => array('sort' => true));
        $columns['left']['name'] = array('label' => _DIRECTORY_NAME,        'width' => 150, 'options' => array('sort' => true));
        $columns['left']['login'] = array('label' => _DIRECTORY_LOGIN,      'width' => 100, 'options' => array('sort' => true));
        $columns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $columns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $columns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
        $columns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
        $columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '42');

        $result = array();
        if (!$_SESSION['directory']['search']['group'])
        {
            $sql =  "
                    SELECT  ploopi_mod_directory_contact.*, 'contact' as usertype, '' as login
                    FROM    ploopi_mod_directory_contact
                    WHERE   ploopi_mod_directory_contact.id_user = {$_SESSION['ploopi']['userid']}
                    ";

            if ($_SESSION['directory']['search']['fulltext'])
            {
                $fulltext_array = explode(' ', $_SESSION['directory']['search']['fulltext']);

                $words = '';
                foreach($fulltext_array as $word)
                {
                    $word = $db->addslashes($word);

                    if ($words != "") $words .= " ";
                    $words .= "+{$word}*";
                }

                $sql .= " AND ( MATCH(lastname,firstname,service,function,city,country) AGAINST ('{$words}' IN BOOLEAN MODE)) ";
            }

            if ($_SESSION['directory']['search']['lastname']) $sql .= " AND lastname LIKE '%".$db->addslashes($_SESSION['directory']['search']['lastname'])."%'";
            if ($_SESSION['directory']['search']['firstname']) $sql .= " AND firstname LIKE '%".$db->addslashes($_SESSION['directory']['search']['firstname'])."%'";
            if ($_SESSION['directory']['search']['service']) $sql .= " AND service LIKE '%".$db->addslashes($_SESSION['directory']['search']['service'])."%'";
            if ($_SESSION['directory']['search']['function']) $sql .= " AND function LIKE '%".$db->addslashes($_SESSION['directory']['search']['function'])."%'";
            if ($_SESSION['directory']['search']['city']) $sql .= " AND city LIKE '%".$db->addslashes($_SESSION['directory']['search']['city'])."%'";
            if ($_SESSION['directory']['search']['country']) $sql .= " AND country LIKE '%".$db->addslashes($_SESSION['directory']['search']['country'])."%'";

            $db->query($sql);
            while ($row = $db->fetchrow()) $result[] = $row;
        }

        $where = array();

        if ($_SESSION['directory']['search']['fulltext'])
        {
            $where[] = " ( MATCH(lastname,firstname,service,function,city,country) AGAINST ('{$words}' IN BOOLEAN MODE))";
        }

        if ($_SESSION['directory']['search']['lastname']) $where[] = " lastname LIKE '%".$db->addslashes($_SESSION['directory']['search']['lastname'])."%'";
        if ($_SESSION['directory']['search']['firstname']) $where[] = " firstname LIKE '%".$db->addslashes($_SESSION['directory']['search']['firstname'])."%'";
        if ($_SESSION['directory']['search']['service']) $where[] = " service LIKE '%".$db->addslashes($_SESSION['directory']['search']['service'])."%'";
        if ($_SESSION['directory']['search']['function']) $where[] = " function LIKE '%".$db->addslashes($_SESSION['directory']['search']['function'])."%'";
        if ($_SESSION['directory']['search']['city']) $where[] = " city LIKE '%".$db->addslashes($_SESSION['directory']['search']['city'])."%'";
        if ($_SESSION['directory']['search']['country']) $where[] = " country LIKE '%".$db->addslashes($_SESSION['directory']['search']['country'])."%'";

        $where_sql = (empty($where)) ? '' : ' WHERE '.implode(' AND ', $where);

        $sql =  "
                SELECT      u.*,
                            'user' as usertype
                FROM        ploopi_user u
                {$where_sql}
                ";

        $db->query($sql);

        while ($row = $db->fetchrow()) $result[] = $row;

        if (sizeof($result)>200)
        {
            ?>
            <div style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">Il y a trop de réponses. Vous devriez préciser vos critères de recherche.</div>
            <?
        }
        else
        {
            $c = 0;
            foreach($result as $row)
            {
                $email = ($row['email']) ? '<img src="./modules/directory/img/ico_email.png">' : '';


                switch ($row['usertype'])
                {
                    case 'user':
                        $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'])) ? _DIRECTORY_USERS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'];

                        $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \''.$row['id'].'\', \'\');"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>';
                        $actions =  '<a href="'.ploopi_urlencode("{$scriptenv}?op=directory_view&user_id={$row['id']}").'"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>';

                        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
                        {
                            if (!isset($favorites["user_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \''.$row['id'].'\', \'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                            else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \''.$row['id'].'\', \'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
                        }
                        
            
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

                        $values[$c]['link'] = 'javascript:void(0);';
                        $values[$c]['onclick'] = "javascript:directory_view(event, '{$row['id']}', '');";
                    break;

                    case 'contact':
                        $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Ouvrir" src="./modules/directory/img/ico_open.png"></a>';

                        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites']) 
                        {
                            if (!isset($favorites["contact_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                            else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
                        }
                        
                        $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'];

                        $workspaces_list = '';
                        
                        $values[$c]['link'] = 'javascript:void(0);';
                        $values[$c]['onclick'] = "javascript:directory_view(event, '', '{$row['id']}');";
                    break;
                }

                $values[$c]['values']['type'] = array('label' => $level_display);
                $values[$c]['values']['name'] = array('label' => "{$row['lastname']} {$row['firstname']}");
                $values[$c]['values']['login'] = array('label' => $row['login']);
                $values[$c]['values']['groups'] = array('label' => $workspaces_list);
                $values[$c]['values']['service'] = array('label' => $row['service']);
                $values[$c]['values']['function'] = array('label' => $row['function']);
                $values[$c]['values']['phone'] = array('label' => $row['phone']);
                $values[$c]['values']['email'] = array('label' => $email);
                $values[$c]['values']['actions'] = array('label' => $actions);

                $values[$c]['description'] = "{$row['lastname']} {$row['firstname']}";
                $values[$c]['style'] = '';

                $c++;
            }

            $skin->display_array($columns, $values, 'array_directory', array('sortable' => true, 'orderby_default' => 'name'));
        }
    break;
}
?>
</div>

<p class="ploopi_va" style="padding:4px;background-color:#e0e0e0;">
    <span style="font-weight:bold;"><? echo _DIRECTORY_LEGEND; ?>:&nbsp;&nbsp;</span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_open.png" /><span><? echo _DIRECTORY_LEGEND_VIEW; ?></span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_modify.png" /><span><? echo _DIRECTORY_LEGEND_MODIFY; ?></span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_delete.png" /><span><? echo _DIRECTORY_LEGEND_DELETE; ?></span>
    <?
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
    {
        ?>
        <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_fav_add.png" /><span><? echo _DIRECTORY_LEGEND_FAVADD; ?></span>
        <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_fav_modify.png" /><span><? echo _DIRECTORY_LEGEND_FAVMODIFY; ?></span>
        <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_cut.png" /><span><? echo _DIRECTORY_LEGEND_FAVDEL; ?></span>
        <?
    }
    ?>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_email.png" /><span><? echo _DIRECTORY_LEGEND_EMAIL; ?></span>
</p>

<? echo $skin->close_simplebloc(); ?>
