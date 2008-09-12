<?php
/*
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad

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

ploopi_init_module('forum');

include_once './modules/forum/class_forum_cat.php';
include_once './modules/forum/class_forum_mess.php';

include_once './modules/forum/include/functions.php';

// If limit = 0 => no limit !
if(!isset($_SESSION['ploopi']['forum']['arrays']))
{
  $_SESSION['ploopi']['forum']['arrays']['subject'] = array('limit' => 25,
                                                            'orderby' => 'timestp',
                                                            'orderin' => 'DESC',
                                                            'page' => 1, 
                                                            'id' => 0);
  
  $_SESSION['ploopi']['forum']['arrays']['mess'] = array( 'limit' => 25,
                                                          'orderby' => 'timestp',
                                                          'orderin' => 'ASC',
                                                          'page' => 1, 
                                                          'id' => 0);
}

//Control $_SESSION['ploopi']['forum']['array']...
forum_CtrlParam();

$strForumGroups = ploopi_viewworkspaces();

$strForumSqlLimitGroupCat = " ploopi_mod_forum_cat.id_workspace IN ($strForumGroups)";
$strForumSqlLimitGroupMess = " ploopi_mod_forum_mess.id_workspace IN ($strForumGroups)";

$strForumSqlAddFiltre = '';

$op = (empty($_GET['op'])) ? '' : $_GET['op'];

// AJAX (with ploopi_die() in admin.ajax.php !)
if(strpos($op,'ajax') !== false) 
  include './modules/forum/public.ajax.php';

// Titre 
echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);

// Open Categorie (if it's possible) here because it's very useful and test right in same time
if(isset($_GET['id_cat']))
{
  $objForumCat = new forum_cat();
  if($objForumCat->open($_GET['id_cat']))
  { 
    // Id_cat is ok and open, you have the right to use this cat ?
    if($objForumCat->fields['visible'] == 0 && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN))
    {
      unset($objForumCat);
      ploopi_redirect('admin.php?op=error');
    }
  }
  else
  {
    unset($objForumCat);
  }
}

// Traitement...
switch($op)
{
  case 'mess': // LIST MESSAGES
    // Contrôls
    if(!isset($objForumCat) || !isset($_GET['id_subject']))
      ploopi_redirect('admin.php?op=error');
    
    if(isset($_GET['page']))
      $_SESSION['ploopi']['forum']['arrays']['mess']['page'] = $_GET['page'];
   
    if(isset($_GET['order']))
    {
      if($_GET['order']==$_SESSION['ploopi']['forum']['arrays']['mess']['orderby'])
      {
        $_SESSION['ploopi']['forum']['arrays']['mess']['orderin'] = ($_SESSION['ploopi']['forum']['arrays']['mess']['orderin']=='ASC') ? 'DESC' : 'ASC';
      }
      else
      {
        $_SESSION['ploopi']['forum']['arrays']['mess']['orderby'] = $_GET['order'];
        $_SESSION['ploopi']['forum']['arrays']['mess']['orderin'] = 'DESC';
      }
    }
    
    forum_CtrlParam();
    
    include './modules/forum/public_mess.inc.php';
    break;
  case 'mess_add' :
  case 'mess_edit' :
  case 'subject_add' :
  case 'subject_edit' :
    // Test action allowed in public_mess_edit ;-)
    include './modules/forum/public_mess_edit.inc.php';
    break;
  case 'mess_save' :
  case 'subject_save' :
    // Contrôls
    if(!isset($objForumCat))
      ploopi_redirect('admin.php?op=error');
    
    $objForumMess = new forum_mess();
    
    if(isset($_GET['id_mess'])) // It's a know message
    {
      $objForumMess->open($_GET['id_mess']);
      
      //if this action is forbiden
      if(($objForumCat->fields['closed'] == 1
           || $objForumMess->fields['moderate_id_user'] > 0
           || $objForumMess->fields['validated_id_user'] > 0
           || $objForumMess->fields['id_author'] != $_SESSION['ploopi']['user']['id'])
           && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN)) 
        ploopi_redirect('admin.php?op=error');
    }
    else // it's a new mess/subject
    {
      $objForumMess->init_description();
      
      if(isset($_GET['id_subject'])) // it's a new/edit message
      {
        $objForumSubject = new forum_mess();
        $objForumSubject->open($_GET['id_subject']);
        // if this action is forbiden
        if(($objForumCat->fields['closed'] == 1 || $objForumSubject->fields['closed'] == 1)
              && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN))
          ploopi_redirect('admin.php?op=error');
          
        unset($objForumSubject);
      }
      else // it's a new/edit subject
      {
        // if this action is forbiden
        if($objForumCat->fields['closed'] == 1
              && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN)) 
          ploopi_redirect('admin.php?op=error');
      }
    }
    
    $objForumMess->fields['id_cat']     = $objForumCat->fields['id'];
    $objForumMess->fields['id_subject'] = (isset($_GET['id_subject'])) ? $_GET['id_subject'] : 0;
    $objForumMess->fields['closed']     = (isset($_POST['forum_close']) && $_POST['forum_close'] == 1) ? 1 : 0;
    $objForumMess->fields['title']      = $_POST['forum_title'];
    $objForumMess->fields['content']    = $_POST['fck_forum_content'];
    
    $objForumMess->fields['validated'] = ($objForumCat->fields['mustbe_validated'] == 1 && $objForumMess->fields['validated'] != 1) ? 0 : 1;
          
    // if id_subject = 0 traitement in method save ! (id_subject <= id)
    // your are a moderator/admin auto validated in methode save
    $objForumMess->save(); 
    
    if($objForumMess->fields['validated'] == 0 && !ploopi_isactionallowed(_FORUM_ACTION_ADMIN)) // If it's not validated add a message
    {
      if($objForumMess->fields['id'] == $objForumMess->fields['id_subject'])
        $_SESSION['ploopi']['forum']['info'] = array('id' => $objForumMess->fields['id'], 'mess' => '<div class="forum_mess_message">'._FORUM_SUBJECT_TOVALID.'</div>');
      else
        $_SESSION['ploopi']['forum']['info'] = array('id' => $objForumMess->fields['id'], 'mess' => '<div class="forum_mess_message">'._FORUM_MESS_TOVALID.'</div>');
    }
    if($op == 'mess_save') // It's a message edit
      ploopi_redirect("admin.php?op=search&id_mess={$objForumMess->fields['id']}");
    else // It's a subject edit
      ploopi_redirect("admin.php?op=search&id_subject={$objForumMess->fields['id']}");
    break;
  case 'mess_validate' :
  case 'mess_edit_validate' :
    // Contrôls
    if(!isset($objForumCat) || !isset($_GET['id_mess']) 
        || !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN))
      ploopi_redirect('admin.php?op=error');

    $objForumMess = new forum_mess();
    
    $objForumMess->open($_GET['id_mess']);
    $objForumMess->validate();
    if($op == 'mess_validate')
      ploopi_redirect(ploopi_urlencode('admin.php?op=mess&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumMess->fields['id_subject']).'#idMess_title_'.$objForumMess->fields['id'],false);
    else
      ploopi_redirect('admin.php?op=mess_edit&id_cat='.$objForumCat->fields['id'].'&id_mess='.$objForumMess->fields['id']);
    break;
  case 'mess_delete' :
    // Contrôls
    if(!isset($objForumCat) || !isset($_GET['id_mess']))
      ploopi_redirect('admin.php?op=error');
    
    $objForumMess = new forum_mess();
    
    $objForumMess->open($_GET['id_mess']);
    
    // if this action is forbiden (or it's a subject)
    if(($objForumCat->fields['closed'] == 1
            || $objForumMess->fields['closed'] == 1 
            || $objForumMess->fields['moderate_id_user'] > 0
            || $objForumMess->fields['validated_id_user'] > 0
            || $objForumMess->fields['id_author'] != $_SESSION['ploopi']['user']['id'])
          && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN))
      ploopi_redirect('admin.php?op=error');

    // If it's a subject no delete, just modify
    if($objForumMess->fields['id'] == $objForumMess->fields['id_subject'])
    {
      if(substr($objForumMess->fields['title'],-1,10) != ' (Deleted)')
        $objForumMess->fields['title'] .= ' (Deleted)';
      $objForumMess->fields['content'] = '';
      $objForumMess->fields['closed'] = 1; // and close the subject !
      $objForumMess->save();
    }
    else
    {
      $objForumMess->delete();
    }
    ploopi_redirect('admin.php?op=mess&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumMess->fields['id_subject']);
    break;
  case 'subject': // LIST SUBJECTS
    // Contrôls
    if(!isset($objForumCat) || ($objForumCat->fields['visible'] == 0 && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN)))
      ploopi_redirect('admin.php?op=error');
      
    if(isset($_GET['page']))
      $_SESSION['ploopi']['forum']['arrays']['subject']['page'] = $_GET['page'];
    
    if(isset($_GET['order']))
    {
      if($_GET['order']==$_SESSION['ploopi']['forum']['arrays']['subject']['orderby'])
      {
        $_SESSION['ploopi']['forum']['arrays']['subject']['orderin'] = ($_SESSION['ploopi']['forum']['arrays']['subject']['orderin']=='ASC') ? 'DESC' : 'ASC';
      }
      else
      {
        $_SESSION['ploopi']['forum']['arrays']['subject']['orderby'] = $_GET['order'];
        $_SESSION['ploopi']['forum']['arrays']['subject']['orderin'] = 'DESC';
      }
    }
    
    forum_CtrlParam();
    
    include './modules/forum/public_subject.inc.php';
    break;
  case 'subject_delete':
    // Contrôls
    if(!isset($objForumCat) || !isset($_GET['id_mess']))
      ploopi_redirect('admin.php?op=error');
    
    $objForumMess = new forum_mess();
    $objForumMess->open($_GET['id_mess']);

    // if this action is forbiden (or it's not a subject)
    if($objForumMess->fields['id'] != $objForumMess->fields['id_subject']
        ||(($objForumCat->fields['visible'] == 0
            || $objForumCat->fields['closed'] == 1
            || $objForumMess->fields['closed'] == 1
            || $objForumMess->fields['moderate_id_user'] > 0
            || $objForumMess->fields['validated_id_user'] > 0
            || $objForumMess->fields['id_author'] != $_SESSION['ploopi']['user']['id']) 
          && !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN)))
      ploopi_redirect('admin.php?op=error');
    
    $objForumMess->deleteSubject();
      
    ploopi_redirect('admin.php?op=subject&id_cat='.$objForumCat->fields['id']);
    break;
  case 'subject_openclose':
    // Contrôls
    if(!isset($objForumCat) || !isset($_GET['id_subject']) || !forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN))
      ploopi_redirect('admin.php?op=error');

    $objForumSubject = new forum_mess();
    $objForumSubject->open($_GET['id_subject']);
    if($objForumSubject->fields['closed'] == 1)
      $objForumSubject->openSubject();
    else
      $objForumSubject->closeSubject();
    
    //ploopi_redirect("admin.php?op=search&id_subject={$objForumSubject->fields['id']}");
    ploopi_redirect('admin.php?op=mess&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumSubject->fields['id']);
    break; 
  case 'search':
    // Search a message in a subject (and... subject in cat for links) 
    if(isset($_GET['id_mess']) && is_numeric($_GET['id_mess']) && $_GET['id_mess'] > 0) 
    {
      $arrSearch = forum_GetMessPage($_GET['id_mess'],$_SESSION['ploopi']['forum']['arrays']);
      if($arrSearch['page'] != 0)
      {
        $_SESSION['ploopi']['forum']['arrays']['mess']['id'] = $arrSearch['id_subject'];
        $_SESSION['ploopi']['forum']['arrays']['mess']['page'] = $arrSearch['page'];
        $_SESSION['ploopi']['forum']['arrays']['subject']['id'] = $arrSearch['id_cat'];
        $_SESSION['ploopi']['forum']['arrays']['subject']['page'] = $arrSearch['page_subject'];
        ploopi_redirect(ploopi_urlencode('admin.php?op=mess&id_cat='.$arrSearch['id_cat'].'&id_subject='.$arrSearch['id_subject']).'#idMess_title_'.$arrSearch['id_mess'],false);
      }
    } // Search a subject in a categorie
    elseif(isset($_GET['id_subject']) && $_GET['id_subject'] > 0) 
    {
      $arrSearch = forum_GetSubjectPage($_GET['id_subject'],$_SESSION['ploopi']['forum']['arrays']['subject']);
      if($arrSearch['page'] != 0)
      {
        $_SESSION['ploopi']['forum']['arrays']['subject']['id'] = $arrSearch['id_cat'];
        $_SESSION['ploopi']['forum']['arrays']['subject']['page'] = $arrSearch['page'];
        ploopi_redirect(ploopi_urlencode('admin.php?op=subject&id_cat='.$arrSearch['id_cat']).'#idSubject_'.$arrSearch['id_subject'],false);
      }
    }
    // Error
    ploopi_redirect('admin.php?op=error');
    break;
  case 'categ_add':
  case 'categ_edit':
    // Contrôls
    if(!ploopi_isactionallowed(_FORUM_ACTION_ADMIN)) 
      ploopi_redirect('admin.php?op=error');

    include './modules/forum/public_categ_edit.inc.php';
    break;
  case 'categ_save' :
    // Contrôls
    if(!ploopi_isactionallowed(_FORUM_ACTION_ADMIN))
      ploopi_redirect('admin.php?op=error');
      
    if(!isset($objForumCat))
    {
      $objForumCat = new forum_cat();
      $objForumCat->init_description();
    }
    $objForumCat->setvalues($_POST,'forum_');
    $objForumCat->save();

    ploopi_validation_save(_FORUM_OBJECT_CAT, $objForumCat->fields['id']);
    ploopi_redirect('admin.php?op=categ');
    break;
  case 'categ_delete':
    // Contrôls
    if(!ploopi_isactionallowed(_FORUM_ACTION_ADMIN))
      ploopi_redirect('admin.php?op=error');

    if(!isset($objForumCat))
    {
      $objForumCat = new forum_cat();
      $objForumCat->init_description();
    }
    $objForumCat->delete();
    ploopi_redirect('admin.php?op=categ');
    break;
  default: // LIST CATEGORIES
    echo $skin->open_simplebloc();
    include './modules/forum/public_categ.inc.php';
    echo $skin->close_simplebloc();
    break;
}
if(isset($objForumCat)) unset($objForumCat);
//phpinfo();
?>
