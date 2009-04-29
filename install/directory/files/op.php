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
 * Opérations
 *
 * @package directory
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Si on est connecté
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On verifie qu'on est bien dans le module Directory
     */

    if (ploopi_ismoduleallowed('directory'))
    {
        switch($ploopi_op)
        {
            case 'directory_list_addnew':
            case 'directory_list_modify':
                ob_start();
                include_once './modules/directory/class_directory_list.php';

                $directory_list = new directory_list();

                if (!empty($_GET['directory_favorites_id_list']) && is_numeric($_GET['directory_favorites_id_list'])) $directory_list->open($_GET['directory_favorites_id_list']);
                else $directory_list->init_description();

                ?>
                <form method="post" onsubmit="javascript:return directory_list_validate(this);" >
                <input type="hidden" name="ploopi_op" value="directory_list_save">
                <input type="hidden" name="directory_favorites_id_list" value="<?php echo $directory_list->fields['id']; ?>">
                <div class="ploopi_form">
                    <p>
                        <label>Libellé:</label>
                        <input type="text" class="text" name="directory_list_label" value="<?php echo htmlentities($directory_list->fields['label']); ?>">
                    </p>
                </div>
                <div style="padding:0 4px 4px 0;text-align:right">
                    <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('popup_directory_list_form');">
                    <?php
                    if ($ploopi_op == 'directory_list_addnew')
                    {
                        $title = 'Création d\'une nouvelle liste';
                        ?><input type="submit" class="button" value="<?php echo _PLOOPI_ADD; ?>"><?php
                    }
                    else
                    {
                        $title = 'Modification d\'une liste';
                        ?><input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>"><?php
                    }
                    ?>
                    </div>
                </form>
                <?php
                $content = ob_get_contents();
                ob_end_clean();
                echo $skin->create_popup($title , $content, 'popup_directory_list_form');
                ploopi_die();
            break;

            case 'directory_list_save':
                include_once './modules/directory/class_directory_list.php';

                $directory_list = new directory_list();

                if (!empty($_POST['directory_favorites_id_list']) && is_numeric($_POST['directory_favorites_id_list'])) $directory_list->open($_POST['directory_favorites_id_list']);
                else $directory_list->setuwm();

                $directory_list->setvalues($_POST, 'directory_list_');
                $directory_favorites_id_list = $directory_list->save();

                ploopi_redirect("admin.php?directoryTabItem=tabFavorites&directory_favorites_id_list={$directory_favorites_id_list}");
            break;

            case 'directory_getlists':
                if (!empty($_GET['directory_favorites_id_user']) && is_numeric($_GET['directory_favorites_id_user']))
                {
                    $where = "AND f.id_ploopi_user = {$_GET['directory_favorites_id_user']}";
                }
                elseif (!empty($_GET['directory_favorites_id_contact']) && is_numeric($_GET['directory_favorites_id_contact']))
                {
                    $where = "AND f.id_contact = {$_GET['directory_favorites_id_contact']}";
                }
                else ploopi_die();

                // get lists
               $sql =  "
                        SELECT      l.*, IF(ISNULL(f.id_list),0,count(*)) as nbfav

                        FROM        ploopi_mod_directory_list l

                        LEFT JOIN   ploopi_mod_directory_favorites f
                        ON          f.id_list = l.id
                        {$where}

                        WHERE       l.id_module = {$_SESSION['ploopi']['moduleid']}
                        AND         l.id_workspace = {$_SESSION['ploopi']['workspaceid']}
                        AND         l.id_user = {$_SESSION['ploopi']['userid']}

                        GROUP BY    l.id

                        ORDER BY    l.label
                        ";

                $db->query($sql);
                $arrLists = $db->getarray();
                $isfav = false;
                foreach($arrLists as $row) if ($row['nbfav']>0) {$isfav = true; break;}

                $intUserId = empty($_GET['directory_favorites_id_user']) ? 0 : $_GET['directory_favorites_id_user'];
                $intContactId = empty($_GET['directory_favorites_id_contact']) ? 0 : $_GET['directory_favorites_id_contact'];
                ?>
                <form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_favorites_add&directory_favorites_id_user={$intUserId}&directory_favorites_id_contact={$intContactId}"); ?>" method="post">
                    <div style="padding:4px;background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;">
                        <span style="font-weight:bold;">Modifier les rattachements :</span>
                        <br /><i>Choix d'une ou plusieurs listes</i>
                    </div>
                    <?php
                    if (empty($arrLists))
                    {
                        ?>
                        <div style="padding:4px;">
                            <a href="<?php echo ploopi_urlencode("admin.php?directoryTabItem=tabFavorites"); ?>"><i>Attention, vous devez ajouter au moins une liste pour gérer vos favoris !</i></a>
                        </div>
                        <?php
                    }
                    else
                    {
                        if ($isfav)
                        {
                            ?>
                            <div class="directory_checkbox" onclick="javascript:directory_checklist('0');">
                                <input type="checkbox" id="directory_id_list0" name="directory_favorites_id_list[]" value="0" onclick="javascript:directory_checklist('0');" />
                                <span style="color:#a60000;font-weight:bold;">Supprimer les rattachements</span>
                            </div>
                            <?php
                        }
                        foreach($arrLists as $row)
                        {
                            ?>
                            <div class="directory_checkbox" onclick="javascript:directory_checklist('<?php echo $row['id']; ?>');">
                                <input type="checkbox" class="directory_id_list" id="directory_id_list<?php echo $row['id']; ?>" name="directory_favorites_id_list[]" value="<?php echo $row['id']; ?>" onclick="javascript:directory_checklist('<?php echo $row['id']; ?>');" <?php if ($row['nbfav']>0) echo 'checked'; ?> />
                                <span><?php echo htmlentities($row['label']); ?></span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    <div style="padding:4px;background-color:#e0e0e0;border-top:1px solid #c0c0c0;text-align:right;">
                        <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('popup_directory_addtofavorites');">
                        <?php
                        if (!empty($arrLists))
                        {
                            ?>
                            <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>">
                            <?php
                        }
                        ?>
                    </div>
                </form>
                <?php
                ploopi_die();
            break;

            case 'directory_favorites':
                ploopi_init_module('directory');
                include_once './modules/directory/public_favorites.php';
                ploopi_die();
            break;

            case 'directory_view':
                if ((!empty($_GET['directory_id_contact']) && is_numeric($_GET['directory_id_contact'])) || (!empty($_GET['directory_id_user']) && is_numeric($_GET['directory_id_user'])))
                {
                    ploopi_init_module('directory');
                    include './modules/directory/public_directory_view.php';
                }
                ploopi_die();
            break;

            case 'directory_modify':
                if ((!empty($_GET['directory_id_contact']) && is_numeric($_GET['directory_id_contact'])))
                {
                    ob_start();

                    ploopi_init_module('directory');
                    include_once './modules/directory/class_directory_contact.php';

                    $directory_contact = new directory_contact();
                    $directory_contact->open($_GET['directory_id_contact']);

                    include './modules/directory/public_directory_form.php';

                    $content = ob_get_contents();
                    ob_end_clean();

                    /**
                     * On affiche le popup
                     */

                    echo $skin->create_popup("Modification d'un contact", $content, 'popup_directory_modify');
                }

                ploopi_die();
            break;

            case 'directory_contact_save':
                include_once './modules/directory/class_directory_contact.php';
                include_once './modules/directory/class_directory_heading.php';

                $directory_contact = new directory_contact();
                if (!empty($_GET['directory_contact_id']) && is_numeric($_GET['directory_contact_id'])) $directory_contact->open($_GET['directory_contact_id']);

                // Rattachement à une rubrique
                if (!empty($_GET['directory_heading_id']) && is_numeric($_GET['directory_heading_id']))
                {
                    $directory_heading = new directory_heading();
                    if ($directory_heading->open($_GET['directory_heading_id'])) $directory_contact->fields['id_heading'] = $_GET['directory_heading_id'];
                }

                $directory_contact->setvalues($_POST, 'directory_contact_');
                $directory_contact->setuwm();
                $directory_contact->save();

                // Photo ?
                if (!empty($_SESSION['directory']['contact_photopath']))
                {
                    ploopi_makedir(_PLOOPI_PATHDATA._PLOOPI_SEP.'directory');

                    // photo temporaire présente => copie dans le dossier définitif
                    rename($_SESSION['directory']['contact_photopath'], $directory_contact->getphotopath());
                    unset($_SESSION['directory']['contact_photopath']);
                }

                ploopi_redirect('admin.php');
            break;

            case 'directory_contact_delete':
                include_once './modules/directory/class_directory_contact.php';

                if (!empty($_GET['directory_contact_id']) && is_numeric($_GET['directory_contact_id']))
                {
                    $directory_contact = new directory_contact();
                    if ($directory_contact->open($_GET['directory_contact_id'])) $directory_contact->delete();
                }
                ploopi_redirect('admin.php');
            break;

            case 'directory_favorites_add':
                include_once './modules/directory/class_directory_favorites.php';
                
                if (!empty($_GET['directory_favorites_id_user']) && is_numeric($_GET['directory_favorites_id_user']))
                {
                    $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_ploopi_user = {$_GET['directory_favorites_id_user']} AND id_user = {$_SESSION['ploopi']['userid']} AND id_contact = 0");
                    if (isset($_POST['directory_favorites_id_list']) && is_array($_POST['directory_favorites_id_list']))
                    {
                        foreach($_POST['directory_favorites_id_list'] as $id_list)
                        {
                            if ($id_list > 0)
                            {
                                $directory_favorites = new directory_favorites();
                                $directory_favorites->open(0, $_SESSION['ploopi']['userid'], $_GET['directory_favorites_id_user'], $id_list);
                                $directory_favorites->save();
                            }
                        }
                    }
                }
                elseif (!empty($_GET['directory_favorites_id_contact']) && is_numeric($_GET['directory_favorites_id_contact']))
                {
                    $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_ploopi_user = 0 AND id_user = {$_SESSION['ploopi']['userid']} AND id_contact = {$_GET['directory_favorites_id_contact']}");
                    if (isset($_POST['directory_favorites_id_list']) && is_array($_POST['directory_favorites_id_list']))
                    {
                        foreach($_POST['directory_favorites_id_list'] as $id_list)
                        {
                            if ($id_list > 0)
                            {
                                $directory_favorites = new directory_favorites();
                                $directory_favorites->open($_GET['directory_favorites_id_contact'], $_SESSION['ploopi']['userid'], 0, $id_list);
                                $directory_favorites->save();
                            }
                        }
                    }
                }
                ploopi_redirect('admin.php');
            break;

            case 'directory_list_delete':
                include_once './modules/directory/class_directory_list.php';

                if (!empty($_GET['directory_favorites_id_list']) && is_numeric($_GET['directory_favorites_id_list']))
                {
                    $directory_list = new directory_list();
                    if ($directory_list->open($_GET['directory_favorites_id_list'])) $directory_list->delete();
                }
                ploopi_redirect("admin.php?directoryTabItem=tabFavorites");
            break;

            case 'directory_choose_photo':
                if (empty($_GET['directory_photo_id'])) ploopi_die();

                // Popup de choix d'une photo pour un utilisateur
                ob_start();
                ploopi_init_module('directory');
                ?>
                <form action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_send_photo&directory_photo_id={$_GET['directory_photo_id']}"); ?>" method="post" enctype="multipart/form-data" target="directory_contact_photo_iframe">
                <p class="ploopi_va" style="padding:2px;">
                    <label><?php echo _DIRECTORY_PHOTO; ?>: </label>
                    <input type="file" name="directory_contact_photo" />
                    <input type="submit" class="button" name="<?php echo _PLOOPI_SAVE; ?>" />
                </p>
                </form>
                <iframe name="directory_contact_photo_iframe" style="display:none;"></iframe>
                <?php
                $content = ob_get_contents();
                ob_end_clean();

                echo $skin->create_popup("Chargement d'une nouvelle photo", $content, 'popup_directory_choose_photo');
                ploopi_die();
            break;

            case 'directory_send_photo':
                // Envoi d'une photo temporaire dans la fiche contact

                if (!empty($_GET['directory_photo_id']))
                {
                    // On vérifie qu'un fichier a bien été uploadé
                    if (!empty($_FILES['directory_contact_photo']['tmp_name']))
                    {
                        $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
                        ploopi_makedir($strTmpPath);
                        $_SESSION['directory']['contact_photopath'] = tempnam($strTmpPath, '');
                        ploopi_resizeimage($_FILES['directory_contact_photo']['tmp_name'], 0, 100, 150, 'png', 0, $_SESSION['directory']['contact_photopath']);
                    }
                    ?>
                    <script type="text/javascript">
                        new function() {
                            window.parent.ploopi_getelem('directory_contact_photo<?php echo $_GET['directory_photo_id']; ?>', window.parent.document).innerHTML = '<img src="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=directory_get_photo&'.ploopi_createtimestamp()); ?>" />';
                            window.parent.ploopi_hidepopup('popup_directory_choose_photo');
                        }
                    </script>
                    <?php
                }
                ploopi_die();
            break;

            case 'directory_get_photo':
                // Envoi de la photo temporaire vers le client
                if (!empty($_SESSION['directory']['contact_photopath'])) ploopi_downloadfile($_SESSION['directory']['contact_photopath'], 'contact.png', false, false);
                ploopi_die();
            break;

            // Gestion des rubriques
            case 'directory_heading_detail':
                ploopi_init_module('directory');
                if (!empty($_GET['directory_heading_id']))
                {
                    $treeview = directory_gettreeview(directory_getheadings());
                    echo $skin->display_treeview($treeview['list'], $treeview['tree'], null, $_GET['directory_heading_id']);
                }
                ploopi_die();
            break;

            case 'directory_heading_delete':
                include_once './modules/directory/class_directory_heading.php';

                $objHeading = new directory_heading();
                if (empty($_GET['directory_heading_id']) || !is_numeric($_GET['directory_heading_id']) || !$objHeading->open($_GET['directory_heading_id'])) ploopi_redirect('admin.php');

                $intIdParent = $objHeading->fields['id_heading'];

                $objHeading->delete();

                ploopi_redirect("admin.php?directoryTabItem=tabSharedContacts&directory_heading_id={$intIdParent}");
            break;

            case 'directory_heading_add':
                ploopi_init_module('directory', false, false, false);
                include_once './modules/directory/class_directory_heading.php';

                $objHeading = new directory_heading();
                if (empty($_GET['directory_heading_id_heading']) || !is_numeric($_GET['directory_heading_id_heading']) || !$objHeading->open($_GET['directory_heading_id_heading']))
                {
                    // calcul pos
                    $rs = $db->query("SELECT max(position) AS maxpos FROM ploopi_mod_directory_heading WHERE id_heading = 0");
                    $row = $db->fetchrow();

                    if (!isset($row['maxpos'])) $row['maxpos'] = 0;

                    // on va créer une racine
                    $objHeadingChild = new directory_heading();
                    $objHeadingChild->fields['label'] = 'Nouvelle Racine';
                    $objHeadingChild->fields['id_heading'] = 0;
                    $objHeadingChild->fields['position'] = $row['maxpos']+1;
                }
                else $objHeadingChild = $objHeading->create_child();

                $objHeadingChild->save();

                ploopi_redirect("admin.php?op=directory_heading_add&directory_heading_id={$objHeadingChild->fields['id']}&op=directory_modify");
            break;

            case 'directory_heading_save':
                ploopi_init_module('directory', false, false, false);
                include_once './modules/directory/class_directory_heading.php';

                $objHeading = new directory_heading();

                if (empty($_GET['directory_heading_id']) || !is_numeric($_GET['directory_heading_id']) || !$objHeading->open($_GET['directory_heading_id'])) ploopi_redirect('admin.php');

                $objHeading->setvalues($_POST, 'directory_heading_');
                $objHeading->save();

                ploopi_validation_save(_DIRECTORY_OBJECT_HEADING, $objHeading->fields['id']);

                ploopi_redirect("admin.php?directory_heading_id={$_GET['directory_heading_id']}");
            break;
        }
    }
}

switch($ploopi_op)
{
    case 'directory_contact_getphoto':
        // Envoi de la photo d'un contact vers le client
        include_once './modules/directory/class_directory_contact.php';

        $directory_contact = new directory_contact();
        if (!empty($_GET['directory_contact_id']) && is_numeric($_GET['directory_contact_id']) && $directory_contact->open($_GET['directory_contact_id']))
        {
            $strPhotoPath = $directory_contact->getphotopath();
            if (file_exists($strPhotoPath)) ploopi_downloadfile($strPhotoPath, 'contact.png', false, false);
        }
        ploopi_die();
    break;
}
?>

