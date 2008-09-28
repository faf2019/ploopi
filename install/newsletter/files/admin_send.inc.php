<?php
/*
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

/**
 * Entrée de la gestion des Newsletters
 *
 * @package newsletter
 * @subpackage send
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

// Traitement sur icone sélectionné 
if (!empty($_GET['newsletterToolbarSend']))  
  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarSend'] = $_GET['newsletterToolbarSend'];
  
if (!isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarSend'])) 
  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarSend'] = 'tabNewsletterSendTodo';

// Si action autorisée, mettre la barre de menu
if(ploopi_isactionallowed(_NEWSLETTER_ACTION_WRITE))
{
  $toolbar = array();
  $toolbar['tabNewsletterSendTodo'] = array(
                                    'title' => _NEWSLETTER_LABELICON_SEND_TODO,
                                    'url'   => 'admin.php?newsletterToolbarSend=tabNewsletterSendTodo',
                                    'icon'  => './modules/newsletter/img/send.png',
                                    'width' => '80'
                                );
  $toolbar['tabNewsletterSendOk'] = array(
                                    'title' => _NEWSLETTER_LABELICON_SEND_OK,
                                    'url'   => 'admin.php?newsletterToolbarSend=tabNewsletterSendOk',
                                    'icon'  => './modules/newsletter/img/sent.png',
                                    'width' => '80'
                                );

  echo '<div style="padding: 2px 0 0 0;">';                              
  echo $skin->create_toolbar($toolbar,$_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarSend']);
  echo '</div>';
}

switch ($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarSend']) 
{
  case 'tabNewsletterSendTodo':
        include './modules/newsletter/admin_send_todo.inc.php';
  break;
  default:
    switch ($op)
    {
      case 'newsletter_list_to':
        include './modules/newsletter/admin_send_list_receiv.inc.php';
      break;
      default:
        include './modules/newsletter/admin_send_ok.inc.php';
      break;    
    }
  break;
}