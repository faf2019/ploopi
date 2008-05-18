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
                <input type="hidden" name="directory_favorites_id_list" value="<? echo $directory_list->fields['id']; ?>">
                <div class="ploopi_form">
                    <p>
                        <label>Libellé:</label>
                        <input type="text" class="text" name="directory_list_label" value="<? echo htmlentities($directory_list->fields['label']); ?>">
                    </p>
                </div>
                <div style="padding:0 4px 4px 0;text-align:right">
                    <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('popup_directory_list_form');">
                    <?
                    if ($ploopi_op == 'directory_list_addnew')
                    {
                        $title = 'Création d\'une nouvelle liste';
                        ?><input type="submit" class="button" value="<? echo _PLOOPI_ADD; ?>"><?
                    }
                    else
                    {
                        $title = 'Modification d\'une liste';
                        ?><input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>"><?
                    }
                    ?>
                    </div>
                </form>
                <?
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
                ?>
                <form action="admin.php" method="post">
                    <input type="hidden" name="op" value="directory_favorites_add">
                    <input type="hidden" name="directory_favorites_id_user" value="<? if (!empty($_GET['directory_favorites_id_user'])) echo $_GET['directory_favorites_id_user']; ?>">
                    <input type="hidden" name="directory_favorites_id_contact" value="<? if (!empty($_GET['directory_favorites_id_contact'])) echo $_GET['directory_favorites_id_contact']; ?>">
                    <div style="padding:4px;background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;">
                        <span style="font-weight:bold;">Modifier les rattachements :</span>
                        <br /><i>Choix d'une ou plusieurs listes</i>
                    </div>
                    <?
                    if (empty($arrLists))
                    {
                        ?>
                        <div style="padding:4px;">
                            <a href="<? echo ploopi_urlencode("admin.php?directoryTabItem=tabFavorites"); ?>"><i>Attention, vous devez ajouter au moins une liste pour gérer vos favoris !</i></a>
                        </div>
                        <?
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
                            <?
                        }
                        foreach($arrLists as $row)
                        {
                            ?>
                            <div class="directory_checkbox" onclick="javascript:directory_checklist('<? echo $row['id']; ?>');">
                                <input type="checkbox" class="directory_id_list" id="directory_id_list<? echo $row['id']; ?>" name="directory_favorites_id_list[]" value="<? echo $row['id']; ?>" onclick="javascript:directory_checklist('<? echo $row['id']; ?>');" <? if ($row['nbfav']>0) echo 'checked'; ?> />
                                <span><? echo $row['label']; ?></span>
                            </div>            
                            <?
                        }
                    }
                    ?>
                    <div style="padding:4px;background-color:#e0e0e0;border-top:1px solid #c0c0c0;text-align:right;">
                        <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('popup_directory_addtofavorites');">
                        <?
                        if (!empty($arrLists))
                        {
                            ?>
                            <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>">
                            <?
                        }
                        ?>
                    </div>
                </form>          
                <?
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
                    $title = '';
                    include './modules/directory/public_directory_view.php';
                }
                ploopi_die();
            break;
    
        }
    }
}
?>