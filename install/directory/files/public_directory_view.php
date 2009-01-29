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
 * Affichage d'un contact
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Création d'un buffer pour alimenter un popup
 */

ob_start();

// Le document est-il imprimable ?
$booPrintable = isset($_GET['directory_print']);

if (!empty($_GET['directory_id_contact']))
{
    include_once './modules/directory/class_directory_contact.php';

    $usr = new directory_contact();
    $usr->open($_GET['directory_id_contact']);
    $popup_title = _DIRECTORY_VIEWCONTACT;
}
elseif (!empty($_GET['directory_id_user']))
{
    $usr = new user();
    $usr->open($_GET['directory_id_user']);
    $popup_title = _DIRECTORY_VIEWUSER;
}
else ploopi_die();

$strName = htmlentities(trim($usr->fields['lastname'].' '.$usr->fields['firstname']));
?>

<h1 class="directory_title" style="background-color:#c0c0c0;border-bottom:1px solid #a0a0a0;">
    <?
    if (!$booPrintable) 
    {
        ?><a href="javascript:void(0);" onclick="javascript:ploopi_openwin('<? echo ploopi_urlencode('admin-light.php?op=directory_view&directory_id_user='.(empty($_GET['directory_id_user']) ? '' : $_GET['directory_id_user']).'&directory_id_contact='.(empty($_GET['directory_id_contact']) ? '' : $_GET['directory_id_contact']).'&directory_print'); ?>',550,400);return false;"><img style="display:block;float:right" src="./modules/directory/img/ico_print.png" title="Imprimer" alt="Imprimer" /></a><?
    }
    echo $strName; 
    ?>
</h1>
<div>
    <div style="float:left;width:110px;">
        <?
        if (file_exists($usr->getphotopath()))
        {
            if (!empty($_GET['directory_id_user']))
            {
                ?><img title="Photo de <? echo $strName; ?>" src="<? echo ploopi_urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$usr->fields['id']}"); ?>" style="border:1px solid #404040;display:block;margin:5px auto;" /><?
            }
            else
            {
                ?><img title="Photo de <? echo $strName; ?>" src="<? echo ploopi_urlencode("admin-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$usr->fields['id']}"); ?>" style="border:1px solid #404040;display:block;margin:5px auto;" /><?
            }
        }
        ?>
    </div>
    <div style="margin-left:110px;border-left:1px solid #a0a0a0;">
        <div class="ploopi_form" style="padding:4px;">
            <h2 class="directory_title" style="background-color:#d0d0d0;border-bottom:1px solid #a0a0a0;">Profil</h2>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_SERVICE; ?>:</label>
                <span><? echo htmlentities($usr->fields['service']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_OFFICE; ?>:</label>
                <span><? echo htmlentities($usr->fields['office']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_FUNCTION; ?>:</label>
                <span><? echo htmlentities($usr->fields['function']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_NUMBER; ?>:</label>
                <span><? echo htmlentities($usr->fields['number']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_PHONE; ?>:</label>
                <span><? echo htmlentities($usr->fields['phone']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_MOBILE; ?>:</label>
                <span><? echo htmlentities($usr->fields['mobile']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_FAX; ?>:</label>
                <span><? echo htmlentities($usr->fields['fax']); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_EMAIL; ?>:</label>
                <span><a href="mailto:<? echo htmlentities($usr->fields['email']); ?>"><? echo htmlentities($usr->fields['email']); ?></a></span>
            </p>
            <?
            $arrAddress = array();

            if (!empty($usr->fields['address'])) $arrAddress[] = ploopi_nl2br(htmlentities($usr->fields['address']));
            if (!empty($usr->fields['postalcode']) || !empty($usr->fields['city'])) $arrAddress[] = ploopi_nl2br(htmlentities(trim($usr->fields['postalcode'].' '.$usr->fields['city'])));
            if (!empty($usr->fields['country'])) $arrAddress[] = ploopi_nl2br(htmlentities($usr->fields['country']));
            ?>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_ADDRESS; ?>:</label>
                <span><? echo implode('<br />', $arrAddress); ?></span>
            </p>
            <p>
                <label style="font-weight:bold;"><? echo _DIRECTORY_COMMENTARY; ?>:</label>
                <span><? echo ploopi_nl2br(htmlentities($usr->fields['comments'])); ?></span>
            </p>

            <?
            if (!empty($_GET['directory_id_contact']) && !empty($usr->fields['id_heading']))
            {
                include_once './modules/directory/class_directory_heading.php';
                
                $intIdHeading = $usr->fields['id_heading'];
                ?>
                <h2 class="directory_title" style="background-color:#d0d0d0;border-bottom:1px solid #a0a0a0;">Rattachements (rubriques)</h2>
                <div style="padding:4px;">
                    <?
                    // Récupération des rubriques de contacts partagés
                    $arrHeadings = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts'] ? directory_getheadings() : array();
                    
                    if (isset($arrHeadings['list'][$usr->fields['id_heading']]['parents']))
                    {
                        $arrParents = split(';', $arrHeadings['list'][$usr->fields['id_heading']]['parents']);
                        $arrTitle  = array();
                        foreach($arrParents as $intId)
                            if (isset($arrHeadings['list'][$intId]))
                                $arrTitle[] = $arrHeadings['list'][$intId]['label'];

                        $arrTitle[] = $arrHeadings['list'][$usr->fields['id_heading']]['label'];
                    }
                    
                    echo ploopi_nl2br(htmlentities(implode("\n", $arrTitle)));
                    ?>
                </div>
                <?
            }

            if (!empty($_GET['directory_id_user']))
            {
                ?>
                <h2 class="directory_title" style="background-color:#d0d0d0;border-bottom:1px solid #a0a0a0;">Groupes d'utilisateurs</h2>
                <div style="padding:4px;">
                    <?
                    $user_gp = $usr->getgroups();

                    // on met les libellés dans un tableau
                    $groups_list = array();
                    $groups_list_id = array();

                    foreach($user_gp as $gp) $groups_list[sprintf("%04d%s", $gp['depth'], $gp['label'])] = $gp['label'];

                    // on trie par profondeur + libellé
                    ksort($groups_list);

                    // on affiche
                    echo implode('<br />',$groups_list);
                    ?>
                </div>

                <h2 class="directory_title" style="background-color:#d0d0d0;border-bottom:1px solid #a0a0a0;">Rattachements (espaces de travail)</h2>
                <div style="padding:4px;">
                    <?
                    $user_ws = $usr->getworkspaces();

                    // on met les libellés dans un tableau
                    $workspaces_list = array();
                    foreach($user_ws as $ws) $workspaces_list[sprintf("%04d%s", $ws['depth'], $ws['label'])] = $ws['label'];

                    // on trie par profondeur + libellé
                    ksort($workspaces_list);

                    // on affiche
                    echo implode('<br />',$workspaces_list);
                    ?>
                </div>

                <h2 class="directory_title" style="background-color:#d0d0d0;border-bottom:1px solid #a0a0a0;">Attributions / Rôles</h2>
                <div style="padding:4px;">
                    <?
                    // Recherche des rôles
                    $arrRoles = array();

                    if (!empty($user_ws))
                    {
                        // recherche des rôles "groupe"
                        if (!empty($user_gp))
                        {

                            $db->query("
                                SELECT      wgr.id_group,
                                            wgr.id_workspace,
                                            r.id,
                                            r.id_module,
                                            r.label as role_label,
                                            m.label as module_label

                                FROM        ploopi_role r,
                                            ploopi_workspace_group_role wgr,
                                            ploopi_module m

                                WHERE       wgr.id_role = r.id
                                AND         r.id_module = m.id
                                AND         wgr.id_group IN (".implode(',', array_keys($user_gp)).")
                                AND         wgr.id_workspace IN (".implode(',', array_keys($user_ws)).")
                            ");

                            while ($row = $db->fetchrow()) $arrRoles["{$row['id_workspace']}_{$row['id']}"] = sprintf("%s : <strong>%s</strong> dans le module <strong>%s</strong>", $user_ws[$row['id_workspace']]['label'], $row['role_label'], $row['module_label']);

                            // recherche des rôles "utilisateur"
                            $db->query("
                                SELECT      wur.id_user,
                                            wur.id_workspace,
                                            r.id,
                                            r.id_module,
                                            r.label as role_label,
                                            m.label as module_label

                                FROM        ploopi_role r,
                                            ploopi_workspace_user_role wur,
                                            ploopi_module m

                                WHERE       wur.id_role = r.id
                                AND         r.id_module = m.id
                                AND         wur.id_user = {$usr->fields['id']}
                                AND         wur.id_workspace IN (".implode(',', array_keys($user_ws)).")
                            ");

                            while ($row = $db->fetchrow()) $arrRoles["{$row['id_workspace']}_{$row['id']}"] = sprintf("%s : <strong>%s</strong> dans le module <strong>%s</strong>", $user_ws[$row['id_workspace']]['label'], $row['role_label'], $row['module_label']);
                        }
                    }

                    if (empty($arrRoles))
                    {
                        echo "<em>Aucun rôle</em>";
                    }
                    else
                    {
                        // on trie par espace / rôle
                        ksort($arrRoles);

                        // on affiche
                        echo implode('<br />',$arrRoles);
                    }
                    ?>
                </div>
                <?
            }
            ?>
        </div>
    </div>
</div>

<?
if ($booPrintable)
{
    ob_flush();
    ?>
    <script type="text/javascript">
        window.print();
    </script>
    <?
}
else
{
    /**
     * On récupère le contenu du buffer
     */
    
    $content = ob_get_contents();
    ob_end_clean();
    
    /**
     * On affiche le popup
     */
    
    echo $skin->create_popup($popup_title, $content, 'popup_directory_view');
    ploopi_die();
}
?>
