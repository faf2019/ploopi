<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Affichage de l'annuaire
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Habillage global de l'annuaire
 */

echo $skin->open_simplebloc(ploopi_htmlentities($title));
?>
<div style="padding:4px;background-color:#e0e0e0;border-bottom:2px solid #c0c0c0;">
<?php echo $desc; ?>
</div>

<div style="overflow:hidden;">
<?php
/**
 * On récupère les favoris
 */

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
    /**
     * Lien pour ajouter un nouveau contact
     */

    case 'tabMycontacts':
        $directory_contact = new directory_contact();
        $directory_contact->init_description();
        ?>
        <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:$('directory_addcontact').style.display='block';">
            <p class="ploopi_va">
                <img border="0" src="./modules/directory/img/ico_add_contact.png">
                <span><?php echo _DIRECTORY_ADDNEWCONTACT; ?></span>
            </p>
        </a>
        <div id="directory_addcontact" style="display:none;">
            <?php include_once './modules/directory/public_directory_form.php'; ?>
        </div>
        <?php
    break;

    /**
     * Affichage du formulaire de recherche
     */

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
        <div class="directory_search">
            <form action="<?php echo ploopi_urlencode('admin.php?op=search'); ?>" method="post">
            <div class="ploopi_form" style="width:33%;float:left;">
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_NAME; ?>:</label>
                    <input type="text" class="text" size="20" name="lastname" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['lastname']); ?>" tabindex="101" />
                </p>
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_FIRSTNAME; ?>:</label>
                    <input type="text" class="text" size="20" name="firstname" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['firstname']); ?>" tabindex="102" />
                </p>
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_FULLTEXT; ?>:</label>
                    <input type="text" class="text" size="20" name="fulltext" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['fulltext']); ?>" tabindex="107" />
                </p>
            </div>
            <div class="ploopi_form" style="width:33%;float:left;">
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_SERVICE; ?>:</label>
                    <input type="text" class="text" size="20" name="service" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['service']); ?>" tabindex="103" />
                </p>
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_FUNCTION; ?>:</label>
                    <input type="text" class="text" size="20" name="service" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['service']); ?>" tabindex="104" />
                </p>
            </div>
            <div class="ploopi_form" style="width:33%;float:left;">
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_CITY; ?>:</label>
                    <input type="text" class="text" size="20" name="city" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['city']); ?>" tabindex="105" />
                </p>
                <p>
                    <label style="font-weight:bold;"><?php echo _DIRECTORY_COUNTRY; ?>:</label>
                    <input type="text" class="text" size="20" name="country" value="<?php echo ploopi_htmlentities($_SESSION['directory']['search']['country']); ?>" tabindex="106" />
                </p>
                <p>
                    <label>&nbsp;</label>
                    <input type="submit" class="button" value="<?php echo _PLOOPI_SEARCH; ?>" style="width:100px;" tabindex="110" />
                </p>
            </div>
            </form>
        </div>
        <?php
    break;
}

switch($_SESSION['directory']['directoryTabItem'])
{
    /**
     * Affichage de l'onglet 'Mes Contacts'
     */

    case 'tabMycontacts':
        $arrColumns = array();
        $arrValues = array();

        $arrColumns['auto']['name'] = array('label' => _DIRECTORY_NAME, 'options' => array('sort' => true));
        $arrColumns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $arrColumns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $arrColumns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
        $arrColumns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
        $arrColumns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => 86);

        $sql =  "
                SELECT  *
                FROM    ploopi_mod_directory_contact
                WHERE   id_user = {$_SESSION['ploopi']['userid']}
                AND     id_heading = 0
                ";

        $db->query($sql);

        $c = 0;
        while ($row = $db->fetchrow())
        {
            $email = ($row['email']) ? '<a href="mailto:'.ploopi_htmlentities($row['email']).'" title="'.ploopi_htmlentities(_DIRECTORY_SEND_EMAIL.': '.$row['email']).'"><img src="./modules/directory/img/ico_email.png"></a>' : '';

            $actions = '
                <a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>
                <a href="javascript:void(0);" onclick="javascript:directory_modify(event, \''.$row['id'].'\');"><img title="Modifier le Contact" src="./modules/directory/img/ico_modify.png"></a>
                <a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?ploopi_op=directory_contact_delete&directory_contact_id={$row['id']}").'\',\''._DIRECTORY_CONFIRM_DELETECONTACT.'\')"><img title="Supprimer" src="./modules/directory/img/ico_delete.png"></a>
            ';

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
            {
                if (!isset($favorites["contact_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
            }

            $arrValues[$c]['values']['name'] = array('label' => ploopi_htmlentities("{$row['lastname']} {$row['firstname']}"));
            $arrValues[$c]['values']['service'] = array('label' => ploopi_htmlentities($row['service']));
            $arrValues[$c]['values']['function'] = array('label' => ploopi_htmlentities($row['function']));
            $arrValues[$c]['values']['phone'] = array('label' => ploopi_htmlentities($row['phone']));
            $arrValues[$c]['values']['email'] = array('label' => $email);
            $arrValues[$c]['values']['actions'] = array('label' => $actions);

            $c++;
        }

        $skin->display_array($arrColumns, $arrValues, 'array_directory', array('sortable' => true, 'orderby_default' => 'name', 'limit' => ploopi_getparam('directory_pagesize')));
    break;

    /**
     * Affichage de l'onglet 'Mon Espace'
     */

    case 'tabMygroup':
        $arrColumns = array();
        $arrValues = array();


        $arrColumns['auto']['name'] = array('label' => _DIRECTORY_NAME, 'options' => array('sort' => true));
        $arrColumns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $arrColumns['right']['ticket'] = array('label' => _DIRECTORY_TICKET,     'width' => 55);
        $arrColumns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $arrColumns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 150, 'options' => array('sort' => true));
        $arrColumns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 150, 'options' => array('sort' => true));

        if (ploopi_getparam('directory_display_workspaces'))
        {
            $arrColumns['right']['groups'] = array('label' => _DIRECTORY_GROUPS,    'width' => 150, 'options' => array('sort' => true));
        }

        $arrColumns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '42');

        // il faut chercher les groupes rattachés à l'espace !
        include_once './include/classes/workspace.php';

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
            $email = ($row['email']) ? '<a href="mailto:'.ploopi_htmlentities($row['email']).'" title="'.ploopi_htmlentities(_DIRECTORY_SEND_EMAIL.': '.$row['email']).'"><img src="./modules/directory/img/ico_email.png"></a>' : '';
            $ticket = '<a href="javascript:void(0);" onclick="javascript:ploopi_tickets_new(event, null, null, null, '.$row['id'].');"><img title="'._DIRECTORY_SEND_TICKET.'" src="./modules/directory/img/ico_ticket.png"></a>';

            $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \''.$row['id'].'\', \'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>';

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
            {
                //if (!isset($favorites["user_{$row['id']}"])) $actions .='<a href="'.ploopi_urlencode("admin.php?op=directory_favorites_add&user_id={$row['id']}").'"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
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

            $arrValues[$c]['values']['name'] = array('label' => ploopi_htmlentities("{$row['lastname']} {$row['firstname']}"));
            $arrValues[$c]['values']['groups'] = array('label' => ploopi_htmlentities($workspaces_list));
            $arrValues[$c]['values']['service'] = array('label' => ploopi_htmlentities($row['service']));
            $arrValues[$c]['values']['function'] = array('label' => ploopi_htmlentities($row['function']));
            $arrValues[$c]['values']['phone'] = array('label' => ploopi_htmlentities($row['phone']));
            $arrValues[$c]['values']['email'] = array('label' => $email);
            $arrValues[$c]['values']['ticket'] = array('label' => $ticket);
            $arrValues[$c]['values']['actions'] = array('label' => $actions);

            $c++;
        }

        $skin->display_array($arrColumns, $arrValues, 'array_directory', array('sortable' => true, 'orderby_default' => 'name', 'limit' => ploopi_getparam('directory_pagesize')));

    break;

    /**
     * Affichage de l'onglet 'Mes Favoris'
     */

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
                <?php
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
                    <option <?php if ($id_list == $row['id']) echo 'selected'; ?> value="<?php echo $row['id']; ?>"><?php echo ploopi_htmlentities($row['label']); ?> (<?php echo ploopi_htmlentities($row['nbfav']); ?> fav)</option>
                    <?php
                }
                ?>
                </select>
                <span onclick="javascript:directory_list_addnew(event);" style="cursor:pointer;">
                    <img src="./modules/directory/img/ico_newlist.png" title="Ajouter une liste" /><span style="margin:0 10px 0 2px;">Ajouter une liste</span>
                </span>
                <span onclick="javascript:directory_list_modify(event);" style="cursor:pointer;display:<?php echo ($id_list>0) ? 'inline' : 'none'; ?>;" id="directory_list_modify_link" >
                    <img src="./modules/directory/img/ico_modify.png" title="Modifier la liste sélectionnée"  /><span style="margin:0 10px 0 2px;">Modifier la liste sélectionnée</span>
                </span>
                <span onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_list_delete")."&directory_favorites_id_list="; ?>'+$('directory_favorites_id_list').value, 'Êtes vous certain de vouloir supprimer cette liste ?');" style="cursor:pointer;display:<?php echo ($id_list>0) ? 'inline' : 'none'; ?>;" id="directory_list_delete_link">
                    <img src="./modules/directory/img/ico_delete.png" title="Supprimer la liste sélectionnée" /><span style="margin:0 10px 0 2px;">Supprimer la liste sélectionnée</span>
                </span>

                <?php
                if (empty($arrLists))
                {
                    ?><span><i>Attention, vous devez ajouter au moins une liste pour gérer vos favoris !</i></span><?php
                }
                ?>
            </p>
        </div>
        <div id="directory_favorites_list">
            <?php include_once './modules/directory/public_favorites.php'; ?>
        </div>
        <?php
    break;

    /**
     * Affichage de l'onglet 'Recherche'
     */

    case 'tabSearch':

        // Récupération des rubriques de contacts partagés
        $arrHeadings = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts'] ? directory_getheadings() : array();

        $arrColumns = array();
        $arrValues = array();

        $arrColumns['left']['type'] = array('label' => _DIRECTORY_TYPE,        'width' => 90, 'options' => array('sort' => true));
        $arrColumns['auto']['name'] = array('label' => _DIRECTORY_NAME,        'options' => array('sort' => true));
        $arrColumns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
        $arrColumns['right']['ticket'] = array('label' => _DIRECTORY_TICKET,     'width' => 55);
        $arrColumns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
        $arrColumns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 150, 'options' => array('sort' => true));
        $arrColumns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 150, 'options' => array('sort' => true));

        if (ploopi_getparam('directory_display_workspaces'))
        {
            $arrColumns['right']['groups'] = array('label' => _DIRECTORY_GROUPS,    'width' => 150, 'options' => array('sort' => true));
        }

        $arrColumns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '42');

        $result = array();

        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts'] || $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mycontacts'])
        {
            $arrContact = array();
            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts']) $arrContact[] = 'dc.id_heading > 0';
            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mycontacts']) $arrContact[] = "(dc.id_user = {$_SESSION['ploopi']['userid']} AND dc.id_heading = 0)";

           $sql = "
                SELECT  dc.*,
                        'contact' as usertype,
                        '' as login
                FROM    ploopi_mod_directory_contact dc
                WHERE   (".implode(' OR ', $arrContact).")
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

                $sql .= " AND ( MATCH(lastname,firstname,service,function,city,country,office,number,comments) AGAINST ('{$words}' IN BOOLEAN MODE)) ";
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
            $where[] = " ( MATCH(lastname,firstname,service,function,city,country,office,number,comments) AGAINST ('{$words}' IN BOOLEAN MODE))";
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
            <?php
        }
        else
        {
            $c = 0;
            foreach($result as $row)
            {
                $email = ($row['email']) ? '<a href="mailto:'.ploopi_htmlentities($row['email']).'" title="'.ploopi_htmlentities(_DIRECTORY_SEND_EMAIL.': '.$row['email']).'"><img src="./modules/directory/img/ico_email.png"></a>' : '';
                $ticket = '';

                switch ($row['usertype'])
                {
                    case 'user':
                        $level_display = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'])) ? _DIRECTORY_USERS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_users'];

                        $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \''.$row['id'].'\', \'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>';

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

                        $ticket = '<a href="javascript:void(0);" onclick="javascript:ploopi_tickets_new(event, null, null, null, '.$row['id'].');"><img title="'._DIRECTORY_SEND_TICKET.'" src="./modules/directory/img/ico_ticket.png"></a>';
                    break;

                    case 'contact':
                        $actions =  '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>';

                        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
                        {
                            if (!isset($favorites["contact_{$row['id']}"])) $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                            else $actions .='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
                        }

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
                                $arrParents = preg_split('/;/', $arrHeadings['list'][$row['id_heading']]['parents']);
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

                $arrValues[$c]['values']['type'] = array('label' => $level_display);
                $arrValues[$c]['values']['name'] = array('label' => ploopi_htmlentities("{$row['lastname']} {$row['firstname']}"));
                $arrValues[$c]['values']['groups'] = array('label' => ploopi_htmlentities($workspaces_list));
                $arrValues[$c]['values']['service'] = array('label' => ploopi_htmlentities($row['service']));
                $arrValues[$c]['values']['function'] = array('label' => ploopi_htmlentities($row['function']));
                $arrValues[$c]['values']['phone'] = array('label' => ploopi_htmlentities($row['phone']));
                $arrValues[$c]['values']['email'] = array('label' => $email);
                $arrValues[$c]['values']['ticket'] = array('label' => $ticket);
                $arrValues[$c]['values']['actions'] = array('label' => $actions);

                $c++;
            }

            $skin->display_array($arrColumns, $arrValues, 'array_directory', array('sortable' => true, 'orderby_default' => 'name', 'limit' => ploopi_getparam('directory_pagesize')));
        }
    break;

    case 'tabSharedContacts':
        include_once './modules/directory/class_directory_heading.php';

        // Largeur du panneau de gauche (treeview)
        $intLeftPanelWidth = 250;

        // Récupération des rubriques
        $arrHeadings = directory_getheadings();

        if (empty($arrHeadings['tree'][0])) $arrHeadings['tree'][0][] = 0;

        if (isset($_REQUEST['directory_heading_id'])) $_SESSION['directory']['directory_heading_id'] = $_REQUEST['directory_heading_id'];
        if (empty($_SESSION['directory']['directory_heading_id'])) $_SESSION['directory']['directory_heading_id'] = current($arrHeadings['tree'][0]);

        // Id de la rubrique sélectionnée
        $intHeadingId = $_SESSION['directory']['directory_heading_id'];

        // Récupération de la structure du treeview
        $arrTreeview = directory_gettreeview($arrHeadings);

        // Instanciation de l'objet 'Rubrique'
        $objHeading = new directory_heading();
        ?>
        <div class="directory_shared_main">
            <div class="directory_shared_treeview" id="directory_shared_treeview">
                <div class="ploopi_tabs">
                    <a href="<?php echo ploopi_urlencode("admin.php?directory_heading_id={$intHeadingId}&op=directory_heading_viewall"); ?>"><img src="./modules/directory/img/ico_viewall.png">Voir toutes les rubriques</a>
                </div>
                <div style="padding:10px;">
                    <?php echo $skin->display_treeview($arrTreeview['list'], $arrTreeview['tree'], $intHeadingId, null, $op == 'directory_heading_viewall'); ?>
                </div>
            </div>
            <div class="directory_shared_heading" id="directory_shared_heading">
                <div>
                    <?php
                    // Ouverture de la rubrique
                    if ($objHeading->open($intHeadingId))
                    {
                        // calcul de profondeur pour affichage du libellé de rubrique
                        $intDepth = sizeof($arrTreeview['list'][$intHeadingId]['parents']);

                        // Construction du tableau des libellés de niveaux de rubriques
                        foreach(array(1, $intDepth, $intDepth+1) as $d)
                        {
                            $strHeadingLabel = !empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]["directory_label_depth{$d}"]) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]["directory_label_depth{$d}"] : 'une/cette/rubrique';
                            $arrHeadingLabel[$d] = preg_split('/\//', $strHeadingLabel);
                        }

                        // On récupère les gestionnaires
                        $arrWfUsers = array();
                        $arrWf = ploopi_validation_get(_DIRECTORY_OBJECT_HEADING, $intHeadingId);
                        $intWfHeadingId = $intHeadingId;

                        if (empty($arrWf)) // pas de validateur pour cette rubrique, on recherche sur les parents
                        {
                            $parents = explode(';', $arrHeadings['list'][$intHeadingId]['parents']);
                            for ($i = sizeof($parents)-1; $i >= 0; $i--)
                            {
                                $arrWf = ploopi_validation_get(_DIRECTORY_OBJECT_HEADING, $parents[$i]);
                                if (!empty($arrWf))
                                {
                                    $intWfHeadingId = $parents[$i];
                                    break;
                                }
                            }
                        }

                        $objUser = new user();
                        $objUser->open($_SESSION['ploopi']['userid']);
                        $arrGroups = $objUser->getgroups(true);

                        /**
                         * L'utilisateur connecté est-il gestionnaire de la rubrique (admin ou validateur) ?
                         */
                        $booModify = ploopi_isadmin() || $arrHeadings['list'][$intHeadingId]['isvalidator'];

                        foreach($arrWf as $value)
                        {
                            if (!$booModify)
                            {
                                if ($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) $booModify = true;
                                if ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']])) $booModify = true;
                            }

                            $arrWfUsers[$value['type_validation']][] = $value['id_validation'];
                        }

                        if ($booModify) // Version modifiable
                        {
                            ?>
                            <div class="ploopi_tabs">
                                <a href="javascript:void(0);" onclick="javascript:if (confirm('Êtes vous certain de vouloir supprimer <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[$intDepth][1]), ploopi_htmlentities($arrHeadingLabel[$intDepth][2])); ?> (et les sous-rubriques attachées) ?')) document.location.href='<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_delete&directory_heading_id={$objHeading->fields['id']}"); ?>';return false;"><img src="./modules/directory/img/ico_delete.png">Supprimer</a>
                                <a href="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_add&directory_heading_id_heading={$objHeading->fields['id']}"); ?>"><img src="./modules/directory/img/ico_new.png">Ajouter <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[$intDepth+1][0]), ploopi_htmlentities($arrHeadingLabel[$intDepth+1][2])); ?></a>
                                <?php if (ploopi_isadmin()) { ?><a href="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_add&directory_heading_id_heading=0"); ?>"><img src="./modules/directory/img/ico_newroot.png">Ajouter <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[1][0]), ploopi_htmlentities($arrHeadingLabel[1][2])); ?></a><?php } ?>
                            </div>
                            <?php
                        }


                        if ($op == 'directory_modify') // interface débloquée
                        {
                            if ($booModify)
                            {
                                ?>
                                <form method="post" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_save&directory_heading_id={$objHeading->fields['id']}"); ?>" onsubmit="javascript:return directory_heading_validate(this);">
                                <?php
                            }
                            elseif (ploopi_isactionallowed(_DIRECTORY_ACTION_MANAGERS))
                            {
                                ?>
                                <form method="post" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_save&directory_heading_id={$objHeading->fields['id']}"); ?>">
                                <?php
                            }

                            if ($booModify) // Gestionnaire uniquement
                            {
                                ?>
                                <div class="ploopi_form">
                                    <p>
                                        <label>Niveau:</label>
                                        <span><?php echo ploopi_htmlentities($arrHeadingLabel[$intDepth][2]); ?></span>
                                    </p>
                                    <p>
                                        <label>Libellé:</label>
                                        <input name="directory_heading_label" id="directory_heading_label" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['label']); ?>" />
                                    </p>
                                    <p>
                                        <label>Description:</label>
                                        <textarea name="directory_heading_description" class="text" style="height:50px;"><?php echo ploopi_htmlentities($objHeading->fields['description']); ?></textarea>
                                    </p>
                                    <p>
                                        <label>Position:</label>
                                        <input name="directory_heading_position" type="text" class="text" style="width:50px;" value="<?php echo ploopi_htmlentities($objHeading->fields['position']); ?>" />
                                        <input type="checkbox" class="checkbox" name="_directory_heading_forcepos" value="1" />Forcer la position
                                    </p>
                                    <p>
                                        <label>Téléphone:</label>
                                        <input name="directory_heading_phone" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['phone']); ?>" style="width:140px;" />
                                    </p>
                                    <p>
                                        <label>Fax:</label>
                                        <input name="directory_heading_fax" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['fax']); ?>" style="width:140px;" />
                                    </p>
                                    <p>
                                        <label>Adresse:</label>
                                        <textarea name="directory_heading_address" class="text" style="height:50px;"><?php echo ploopi_htmlentities($objHeading->fields['address']); ?></textarea>
                                    </p>
                                    <p>
                                        <label>Code Postal:</label>
                                        <input name="directory_heading_postalcode" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['postalcode']); ?>" style="width:80px;" />
                                    </p>
                                    <p>
                                        <label>Ville:</label>
                                        <input name="directory_heading_city" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['city']); ?>" />
                                    </p>
                                    <p>
                                        <label>Pays:</label>
                                        <input name="directory_heading_country" type="text" class="text" value="<?php echo ploopi_htmlentities($objHeading->fields['country']); ?>" />
                                    </p>
                                </div>
                                <?php
                            }
                        }

                        if (!$booModify || empty($op)) // Version non modifiable
                        {
                            $arrParents = preg_split('/;/', $arrHeadings['list'][$intHeadingId]['parents']);
                            $arrTitle  = array();

                            foreach($arrParents as $intId)
                                if (isset($arrHeadings['list'][$intId]))
                                    $arrTitle[] = $arrHeadings['list'][$intId]['label'];

                            $arrTitle[] = $objHeading->fields['label'];


                            ?>
                            <h1 class="directory_title"><?php echo ploopi_htmlentities(implode(' > ', $arrTitle)); ?></h1>
                            <?php
                            if (!empty($objHeading->fields['description']))
                            {
                                ?><div style="padding:4px;"><em><?php echo ploopi_nl2br(ploopi_htmlentities($objHeading->fields['description'])); ?></em></div><?php
                            }

                            // Construction de la chaîne de téléphone
                            $arrPhone = array();
                            if (!empty($objHeading->fields['phone'])) $arrPhone[] = ploopi_htmlentities("Tel: ".$objHeading->fields['phone']);
                            if (!empty($objHeading->fields['fax'])) $arrPhone[] = ploopi_htmlentities("Fax: ".$objHeading->fields['fax']);

                            if (!empty($arrPhone))
                            {
                                ?><div style="padding:4px;"><?php echo implode(' - ', $arrPhone); ?></div><?php
                            }

                            // Construction de la chaîne d'adresse
                            $arrAddress = array();
                            if (!empty($objHeading->fields['address'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities($objHeading->fields['address']));
                            if (!empty($objHeading->fields['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities(trim($objHeading->fields['postalcode'].' '.$objHeading->fields['city'])));
                            if (!empty($objHeading->fields['country'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities($objHeading->fields['country']));

                            if (!empty($arrAddress))
                            {
                                ?><div style="padding:4px;"><?php echo implode('<br />', $arrAddress); ?></div><?php
                            }

                        }

                        if ($op == 'directory_modify') // interface débloquée
                        {
                            if (ploopi_isactionallowed(_DIRECTORY_ACTION_MANAGERS)) // Gestion des gestionnaires
                            {
                                ?>
                                <div style="clear:both;padding:4px;">
                                    <fieldset class="fieldset" style="padding:6px;">
                                        <legend><strong>Gestionnaires <?php if ($intWfHeadingId != $intHeadingId) echo "<em>(Hérités de &laquo; <a href=\"".ploopi_urlencode("admin.php?directory_heading_id={$intWfHeadingId}")."\">{$arrHeadings['list'][$intWfHeadingId]['label']}</a> &raquo;)</em>"; ?></strong></legend>

                                        <p class="ploopi_va" style="padding:0 2px 2px 2px;">
                                            <?php
                                            if (!empty($arrWfUsers))
                                            {
                                                if (!empty($arrWfUsers['group']))
                                                {
                                                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                                                    $db->query(
                                                        "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrWfUsers['group']).") ORDER BY label"
                                                    );

                                                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi_htmlentities($row['label'])."&nbsp;</span>";
                                                }
                                                if (!empty($arrWfUsers['user']))
                                                {
                                                    $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                                                    $db->query(
                                                        "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrWfUsers['user']).") ORDER BY lastname, firstname"
                                                    );

                                                    while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi_htmlentities($row['name'])."&nbsp;</span>";
                                                }
                                            }
                                            else echo '<em>Aucune accréditation</em>';
                                            ?>
                                        </p>
                                        <div style="clear:both;padding:4px;">
                                            <div style="border:1px solid #c0c0c0;overflow:hidden;">
                                            <?php ploopi_validation_selectusers(_DIRECTORY_OBJECT_HEADING, $intHeadingId, -1, _DIRECTORY_ACTION_CONTACTS, sprintf("Gestionnaires de %s %s", $arrHeadingLabel[$intDepth][1], $arrHeadingLabel[$intDepth][2])); ?>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <?php
                            }

                            if ($booModify || ploopi_isactionallowed(_DIRECTORY_ACTION_MANAGERS))
                            {
                                ?>
                                <div style="text-align:right;padding:4px;">
                                    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?directory_heading_id={$intHeadingId}"); ?>';">
                                    <input type="reset" class="button" value="<?php echo _PLOOPI_RESET; ?>">
                                    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>">
                                </div>
                                </form>
                                <?php
                            }
                        }

                        if (!$booModify || !ploopi_isactionallowed(_DIRECTORY_ACTION_MANAGERS) || empty($op)) // Version non modifiable
                        {
                            ?>
                            <div class="directory_shared_managers">
                                <div style="float:left;">
                                    <em>
                                        <strong>Gestionnaires <?php if ($intWfHeadingId != $intHeadingId) echo "(Hérités de &laquo; <a href=\"".ploopi_urlencode("admin.php?directory_heading_id={$intWfHeadingId}")."\">{$arrHeadings['list'][$intWfHeadingId]['label']}</a> &raquo;)"; ?></strong>:

                                        <div style="clear:both;padding:4px;">
                                            <p class="ploopi_va" style="padding:0 2px 2px 2px;">
                                                <?php
                                                if (!empty($arrWfUsers))
                                                {
                                                    if (!empty($arrWfUsers['group']))
                                                    {
                                                        $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\">";

                                                        $db->query(
                                                            "SELECT label FROM ploopi_group WHERE id in (".implode(',',$arrWfUsers['group']).") ORDER BY label"
                                                        );

                                                        while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi_htmlentities($row['label'])."&nbsp;</span>";
                                                    }
                                                    if (!empty($arrWfUsers['user']))
                                                    {
                                                        $strIcon = "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\">";

                                                        $db->query(
                                                            "SELECT concat(lastname, ' ', firstname) as name FROM ploopi_user WHERE id in (".implode(',',$arrWfUsers['user']).") ORDER BY lastname, firstname"
                                                        );

                                                        while ($row = $db->fetchrow()) echo "{$strIcon}<span>&nbsp;".ploopi_htmlentities($row['name'])."&nbsp;</span>";
                                                    }
                                                }
                                                else echo '<em>Aucune accréditation</em>';
                                                ?>
                                            </p>
                                        </div>
                                    </em>
                                </div>
                                <?php
                                if (($booModify || ploopi_isactionallowed(_DIRECTORY_ACTION_MANAGERS)) && empty($op)) // interface bloquée
                                {
                                    ?>
                                        <div style="clear:both;text-align:right;">
                                        <input type="button" class="button" value="Modifier" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?directory_heading_id={$intHeadingId}&op=directory_modify"); ?>';">
                                        </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php

                        }
                        ?>

                        <div style="border-top:1px solid #a0a0a0;">
                            <?php
                            if ($booModify) // Version modifiable
                            {
                                ?>
                                <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('directory_import');">
                                    <p class="ploopi_va">
                                        <img border="0" src="./modules/directory/img/ico_import.png">
                                        <span><?php echo _DIRECTORY_IMPORTCONTACTS; ?></span>
                                    </p>
                                </a>
                                <div id="directory_import" style="display:none;">
                                    <?php include_once './modules/directory/public_directory_import.php'; ?>
                                </div>
                                <?php
                                $directory_contact = new directory_contact();
                                $directory_contact->init_description();
                                $directory_contact->fields['id_heading'] = $intHeadingId;
                                ?>
                                <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('directory_addcontact');">
                                    <p class="ploopi_va">
                                        <img border="0" src="./modules/directory/img/ico_add_contact.png">
                                        <span><?php echo _DIRECTORY_ADDNEWCONTACT; ?></span>
                                    </p>
                                </a>
                                <div id="directory_addcontact" style="display:none;">
                                    <?php include_once './modules/directory/public_directory_form.php'; ?>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="ploopi_form_title">
                                <a href="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=directory_export&directory_format=csv&directory_heading_id={$intHeadingId}"); ?>" style="margin-left:6px;display:block;float:right;" title="Export CSV"><img src="./modules/directory/img/mime/csv.png" /></a>
                                <a href="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=directory_export&directory_format=xls&directory_heading_id={$intHeadingId}"); ?>" style="margin-left:6px;display:block;float:right;" title="Export XLS"><img src="./modules/directory/img/mime/xls.png" /></a>
                                <a href="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=directory_export&directory_format=xml&directory_heading_id={$intHeadingId}"); ?>" style="margin-left:6px;display:block;float:right;" title="Export XML"><img src="./modules/directory/img/mime/xml.png" /></a>
                                <span>Liste des contacts rattachés à <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[$intDepth][1]), ploopi_htmlentities($arrHeadingLabel[$intDepth][2])); ?></span>
                            </div>
                            <?php
                            $arrColumns = array();
                            $arrValues = array();

                            $arrColumns['left']['position'] = array('label' => 'P.',     'width' => 35, 'options' => array('sort' => true));
                            $arrColumns['auto']['name'] = array('label' => _DIRECTORY_NAME, 'options' => array('sort' => true));
                            $arrColumns['right']['email'] = array('label' => _DIRECTORY_EMAIL,     'width' => 50, 'options' => array('sort' => true));
                            $arrColumns['right']['phone'] = array('label' => _DIRECTORY_PHONE,     'width' => 100, 'options' => array('sort' => true));
                            $arrColumns['right']['function'] = array('label' => _DIRECTORY_FUNCTION, 'width' => 120, 'options' => array('sort' => true));
                            $arrColumns['right']['service'] = array('label' => _DIRECTORY_SERVICE, 'width' => 120, 'options' => array('sort' => true));
                            $arrColumns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => 82);

                            $sql =  "
                                    SELECT  *
                                    FROM    ploopi_mod_directory_contact
                                    WHERE   id_heading = {$intHeadingId}
                                    ";

                            $rs = $db->query($sql);

                            if ($db->numrows($rs))
                            {
                                $c = 0;
                                while ($row = $db->fetchrow($rs))
                                {
                                    $email = ($row['email']) ? '<a href="mailto:'.ploopi_htmlentities($row['email']).'" title="'.ploopi_htmlentities(_DIRECTORY_SEND_EMAIL.': '.$row['email']).'"><img src="./modules/directory/img/ico_email.png"></a>' : '';

                                    $arrActions = array();
                                    $arrActions[] = '<a href="javascript:void(0);" onclick="javascript:directory_view(event, \'\', \''.$row['id'].'\');"><img title="Voir le Profil" src="./modules/directory/img/ico_open.png"></a>';
                                    if ($booModify) $arrActions[] = '<a href="javascript:void(0);" onclick="javascript:directory_modify(event, \''.$row['id'].'\');"><img title="Modifier le Contact" src="./modules/directory/img/ico_modify.png"></a>';
                                    if ($booModify) $arrActions[] = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?ploopi_op=directory_contact_delete&directory_contact_id={$row['id']}").'\',\''._DIRECTORY_CONFIRM_DELETECONTACT.'\')"><img title="Supprimer" src="./modules/directory/img/ico_delete.png"></a>';

                                    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
                                    {
                                        if (!isset($favorites["contact_{$row['id']}"])) $arrActions[] = '<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Ajouter aux favoris" src="./modules/directory/img/ico_fav_add.png"></a>';
                                        else $arrActions[] ='<a href="javascript:void(0);" onclick="javascript:directory_addtofavorites(event, \'\', \''.$row['id'].'\');"><img title="Modifier les favoris" src="./modules/directory/img/ico_fav_modify.png"></a>';
                                    }

                                    $arrValues[$c]['values']['position'] = array('label' => ploopi_htmlentities($row['position']));
                                    $arrValues[$c]['values']['name'] = array('label' => ploopi_htmlentities("{$row['lastname']} {$row['firstname']}"));
                                    $arrValues[$c]['values']['service'] = array('label' => ploopi_htmlentities($row['service']));
                                    $arrValues[$c]['values']['function'] = array('label' => ploopi_htmlentities($row['function']));
                                    $arrValues[$c]['values']['phone'] = array('label' => ploopi_htmlentities($row['phone']));
                                    $arrValues[$c]['values']['email'] = array('label' => $email);
                                    $arrValues[$c]['values']['actions'] = array('label' => implode('', $arrActions));

                                    $c++;
                                }
                            }

                            $skin->display_array($arrColumns, $arrValues, 'array_directory', array('sortable' => true, 'orderby_default' => 'position', 'limit' => ploopi_getparam('directory_pagesize')));

                            if (!$db->numrows($rs))
                            {
                                ?>
                                <div style="padding:4px;text-align:center;">Il n'y a pas de contact rattaché à <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[$intDepth][1]), ploopi_htmlentities($arrHeadingLabel[$intDepth][2])); ?></div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    else
                    {
                        // récupération du libellé des rubriques de niveau 1
                        $strHeadingLabel = !empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]["directory_label_depth1"]) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]["directory_label_depth1"] : 'une/cette/rubrique';
                        $arrHeadingLabel[1] = preg_split('/\//', $strHeadingLabel);
                        ?>
                        <div class="ploopi_tabs">
                            <?php if (ploopi_isadmin()) { ?><a href="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_heading_add&directory_heading_id_heading=0"); ?>"><img src="./modules/directory/img/ico_newroot.png">Ajouter <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[1][0]), ploopi_htmlentities($arrHeadingLabel[1][2])); ?></a><?php } ?>
                        </div>
                        <?php
                        if (empty($arrHeadings['list']))
                        {
                            ?>
                            <div class="error" style="padding:10px;text-align:center;">Vous devez d'abord créer <?php printf("%s %s", ploopi_htmlentities($arrHeadingLabel[1][0]), ploopi_htmlentities($arrHeadingLabel[1][2])); ?></div>
                            <?php
                        }
                        else
                        {
                            ?>
                            <div class="error" style="padding:10px;text-align:center;">ERREUR - Cette rubrique n'existe pas</div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if ($op == 'directory_modify')
        {
            ?>
            <script type="text/javascript">
            ploopi_window_onload_stock(
                function() {
                        $('directory_heading_label').focus();
                        $('directory_heading_label').select();
                    }
                );
            </script>
            <?php
        }
    break;

    case 'tabSpeedDialing':
        $objSpeedDialing = new directory_speeddialing();
        $objSpeedDialing->init_description();

        $arrHeadings = $db->getarray(
            $db->query("
                SELECT      distinct(ds.heading)
                FROM        ploopi_mod_directory_speeddialing ds
                ORDER BY    ds.label
            "), true
        );

        if (ploopi_isactionallowed(_DIRECTORY_ACTION_SPEEDDIALING))
        {
            ?>
            <a class="ploopi_form_title" href="javascript:void(0);" onclick="javascript:$('directory_addnumber').style.display='block';">
                <p class="ploopi_va">
                    <img border="0" src="./modules/directory/img/ico_phone.png">
                    <span><?php echo _DIRECTORY_ADDNEWNUMBER; ?></span>
                </p>
            </a>
            <div id="directory_addnumber" style="display:none;border-bottom:1px solid #c0c0c0;overflow:auto;">
                <form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_speeddialing_save&directory_speeddialing_id={$objSpeedDialing->fields['id']}"); ?>" method="post" onsubmit="return directory_speeddialing_validate(this);">
                <p class="ploopi_va" style="padding:4px;">
                    <span>Rubrique:</span>
                    <select class="select" name="directory_speeddialing_heading" style="width:150px;" tabindex="110">
                        <option value="" style="font-style:italic;">(Rubrique existante)</option>
                        <?php foreach($arrHeadings as $strHeading) echo '<option value="'.ploopi_htmlentities($strHeading).'">'.ploopi_htmlentities($strHeading).'</option>'; ?>
                    </select>
                    <em>ou</em>
                    <input type="text" name="_directory_speeddialing_newheading" class="text" style="width:100px;" value="Nouvelle rubrique" tabindex="111" onfocus="javascript:this.value = '';" />
                    <span>Libellé:</span>
                    <input type="text" name="directory_speeddialing_label" class="text" style="width:150px;" tabindex="115" />
                    <span>Numéro:</span>
                    <input type="text" name="directory_speeddialing_number" class="text" style="width:90px;" maxlength="32" tabindex="116" />
                    <span>Abrégé:</span>
                    <input type="text" name="directory_speeddialing_shortnumber" class="text" style="width:60px;" maxlength="16" tabindex="117" />
                    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php"); ?>';" tabindex="121" />
                    <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="120" />

                </p>
                </form>
            </div>
            <?php
        }
        ?>
        <div style="border-bottom:1px solid #c0c0c0;overflow:auto;">
            <div class="ploopi_form_title">
                <span>Liste des numéros abrégés</span>
            </div>
            <?php
            $arrColumns = array();
            $arrValues = array();

            $arrColumns['left']['heading'] = array('label' => 'Rubrique', 'width' => 200, 'options' => array('sort' => true));
            $arrColumns['auto']['label'] = array('label' => 'Libellé', 'options' => array('sort' => true));
            $arrColumns['right']['shortnumber'] = array('label' => 'Abrégé', 'width' => 90, 'options' => array('sort' => true));
            $arrColumns['right']['number'] = array('label' => 'Numéro', 'width' => 120, 'options' => array('sort' => true));
            if (ploopi_isactionallowed(_DIRECTORY_ACTION_SPEEDDIALING))
            {
                $arrColumns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => 42);
            }

            $rs = $db->query("
                SELECT  *
                FROM    ploopi_mod_directory_speeddialing
            ");

            if ($db->numrows($rs))
            {
                $c = 0;
                while ($row = $db->fetchrow())
                {

                    $arrValues[$c]['values']['heading'] = array('label' => ploopi_htmlentities($row['heading']), 'sort_label' => strtoupper(ploopi_convertaccents(sprintf("%-255s_%-255s", $row['heading'], $row['label']))));
                    $arrValues[$c]['values']['label'] = array('label' => ploopi_htmlentities($row['label']), 'sort_label' => strtoupper(ploopi_convertaccents($row['label'])));
                    $arrValues[$c]['values']['shortnumber'] = array('label' => ploopi_htmlentities($row['shortnumber']));
                    $arrValues[$c]['values']['number'] = array('label' => ploopi_htmlentities($row['number']));
                    $arrValues[$c]['description'] = ploopi_htmlentities($row['label']);

                    if (ploopi_isactionallowed(_DIRECTORY_ACTION_SPEEDDIALING))
                    {
                        $arrActions = array();
                        $arrActions[] = '<a href="javascript:void(0);" onclick="javascript:directory_speeddialing_modify(event, \''.$row['id'].'\');"><img title="Modifier le numéro" src="./modules/directory/img/ico_modify.png"></a>';
                        $arrActions[] = '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin-light.php?ploopi_op=directory_speeddialing_delete&directory_speeddialing_id={$row['id']}").'\',\''._DIRECTORY_CONFIRM_DELETENUMBER.'\')"><img title="Supprimer" src="./modules/directory/img/ico_delete.png"></a>';
                        $arrValues[$c]['values']['actions'] = array('label' => implode('', $arrActions));
                        $arrValues[$c]['link'] = 'javascript:void(0);';
                        $arrValues[$c]['onclick'] = 'directory_speeddialing_modify(event, \''.$row['id'].'\');';
                    }
                    $c++;
                }
            }

            $skin->display_array($arrColumns, $arrValues, 'array_directory_speeddialing', array('sortable' => true, 'orderby_default' => 'heading', 'limit' => ploopi_getparam('directory_pagesize')));

            if (!$db->numrows($rs))
            {
                ?>
                <div style="padding:4px;text-align:center;">Il n'y a pas de numéro abrégé</div>
                <?php
            }
            ?>
        </div>
        <?php

    break;
}
?>
</div>

<p class="ploopi_va" style="padding:4px;background-color:#e0e0e0;">
    <span style="font-weight:bold;"><?php echo _DIRECTORY_LEGEND; ?>:&nbsp;&nbsp;</span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_open.png" /><span><?php echo _DIRECTORY_LEGEND_VIEW; ?></span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_modify.png" /><span><?php echo _DIRECTORY_LEGEND_MODIFY; ?></span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_delete.png" /><span><?php echo _DIRECTORY_LEGEND_DELETE; ?></span>
    <?php
    if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
    {
        ?>
        <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_fav_add.png" /><span><?php echo _DIRECTORY_LEGEND_FAVADD; ?></span>
        <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_fav_modify.png" /><span><?php echo _DIRECTORY_LEGEND_FAVMODIFY; ?></span>
        <?php
    }
    ?>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_email.png" /><span><?php echo _DIRECTORY_LEGEND_EMAIL; ?></span>
    <img style="margin:0 4px 0 10px;" src="./modules/directory/img/ico_ticket.png" /><span><?php echo _DIRECTORY_LEGEND_TICKET; ?></span>
</p>

<?php echo $skin->close_simplebloc(); ?>
