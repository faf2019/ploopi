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
 * Interface d'administration du module Newsletter.
 * 
 * @package newsletter
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('newsletter');
include_once './modules/newsletter/class_newsletter_subscriber.php';
include_once './modules/newsletter/class_newsletter_letter.php';
include_once './modules/newsletter/class_newsletter_send.php';

include_once './modules/newsletter/include/functions.php';

$op = (empty($_GET['op'])) ? '' : $_GET['op'];
$strNewsletterMenuBlock = (empty($_GET['newsletter_menu'])) ? '' : $_GET['newsletter_menu'];

switch ($strNewsletterMenuBlock)
{
  /*
   * MENU CONSULTATION
   */
  case 'consult':
    /*
     * En mode consultation il n'y a que 2 choix :
     * - on connait id_newsletter on est donc en demande de popup d'affichage
     * - on ne le connait pas, on affiche la liste des Newsletters consultables
     */
    if(isset($_GET['id_newsletter']))
    {
      //Ouverture d'un buffer affichage
      ob_start();
      ploopi_init_module('newsletter');
      
      global $ploopi_days;
      global $ploopi_months;
      
      $strNewsletterMode = 'display';
      ?>
      <div style="padding:4px; height:500px; overflow:auto;">
      <iframe id="newsletter_frame_consult" style="border:0;width:100%;height:500px;margin:0;padding:0;" src="<? echo ploopi_urlencode("index-quick.php?id_module={$_SESSION['ploopi']['moduleid']}&ploopi_op=newsletter_consult&id_newsletter={$_GET['id_newsletter']}"); ?>"></iframe>
      </div>
      <?php
      $content = ob_get_contents(); // Recupération du buffer
      ob_end_clean(); //Nettoyage du buffeur
      
      // Creation de la popup
      echo $skin->create_popup('Newsletter',$content,'newsletter_popup_consult');
      ploopi_die();
    }
    else
    {
      // Ouverture en consultation
      echo $skin->create_pagetitle(_NEWSLETTER_PAGE_TITLE_CONSULT);
      include './modules/newsletter/admin_newsletter_list.inc.php';
    }
  break;

  /*
   * MENU ADMINISTRATION
   */
  default:
    switch ($op)
    {
      /*
       * Gestion des op des inscriptions (pour le Backoffice. Le Frontoffice est dans op.php)
       */
      case 'subscrib_save':   // Sauvegarde de donnée d'inscriptions
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIF_SUBSCRIBER) && !empty($_GET['email_subscrib']))
        {
          $objNewsletterSubscrib = new newsletter_subscriber();
          if($objNewsletterSubscrib->open($_GET['email_subscrib']))
          {
            $objNewsletterSubscrib->setvalues($_POST,'subscrib_');
            $objNewsletterSubscrib->save();
          }
        }
        ploopi_redirect('admin.php');
      break;
      
      case 'subscrib_delete': // Suppression de donnée d'inscriptions
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_DELETE_SUBSCRIBER) && !empty($_GET['email_subscrib']))
        {
          $objNewsletterSubscrib = new newsletter_subscriber();
          if($objNewsletterSubscrib->open($_GET['email_subscrib']))
            $objNewsletterSubscrib->delete();
        }
        ploopi_redirect('admin.php');
      break;
      
      case 'subscrib_switch_active': // Changement d'état de active
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIF_SUBSCRIBER) && !empty($_GET['email_subscrib']))
        {
          $objNewsletterSubscrib = new newsletter_subscriber();
          if($objNewsletterSubscrib->open($_GET['email_subscrib']))
          {
            $objNewsletterSubscrib->fields['active'] = ($objNewsletterSubscrib->fields['active'] == 1) ? 0 : 1; 
            $objNewsletterSubscrib->save();
          }
        }
        ploopi_redirect('admin.php');
      break;
      /*
       * Gestion des op des Newsletter
       */
      case 'newsletter_save': // sauvegarde les newsletter
        if((ploopi_isactionallowed(_NEWSLETTER_ACTION_WRITE) && !isset($_GET['id_newsletter'])) || 
           (ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY) && isset($_GET['id_newsletter'])))
        {
          $objNewsletter = new newsletter();
          if(isset($_GET['id_newsletter']))
          {
            if(!$objNewsletter->open($_GET['id_newsletter'])) ploopi_redirect('admin.php');
          }
          $objNewsletter->fields['content'] = $_POST['fck_newsletter_content'];
          $objNewsletter->setvalues($_POST,'newsletter_'); 
          $objNewsletter->save();
          
          // Enregistrement des validateurs
          if(ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_VALIDATOR))
            ploopi_validation_save(_NEWSLETTER_OBJECT_NEWSLETTER,$objNewsletter->fields['id']);
          
          ploopi_redirect("admin.php?op=newsletter_modify&id_newsletter={$objNewsletter->fields['id']}");
        }
        ploopi_redirect('admin.php');
      break;
      
      case 'newsletter_validate': // valide une newsletter
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_VALIDATE) && isset($_GET['id_newsletter']))
        {
          $objNewsletter = new newsletter();
          if($objNewsletter->open($_GET['id_newsletter']))
          {
            $objNewsletter->validate();
            ploopi_redirect("admin.php?op=newsletter_modify&id_newsletter={$objNewsletter->fields['id']}");
          }
        }
        ploopi_redirect('admin.php');
      break;
      
      case 'newsletter_pdf': // Génère le pdf a partir du contenu de la newsletter
        if(isset($_GET['id_newsletter']))
        {
          $objNewsletter = new newsletter();
          if($objNewsletter->open($_GET['id_newsletter']))
          {
            $objNewsletter->create_pdf();
            ploopi_redirect("admin.php?op=newsletter_modify&id_newsletter={$objNewsletter->fields['id']}");
          }
        }
        ploopi_redirect('admin.php');
      break;
      
      case 'newsletter_delete': // Supprime une newsletter
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_DELETE) && isset($_GET['id_newsletter']))
        {
          $objNewsletter = new newsletter();
          if($objNewsletter->open($_GET['id_newsletter']))
            $objNewsletter->delete();
        }
        ploopi_redirect('admin.php');
      break;
      /*
       * Gestion des op des envois
       */
      case 'newsletter_send': // Envoi de la newsletter
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_SEND) && isset($_GET['id_newsletter']))
        {
          $intIdNeswsletter = $_GET['id_newsletter'];
          $objSendletter = new newsletter_send($_GET['id_newsletter']);
          if($objSendletter->newsletter_send_letter()) // Envoi de la lettre
          {
            $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletter_return_send'] = _NEWSLETTER_LABEL_RETURN_SEND_OK;
            
            //Sauvegarde dans la newsletter les détails de l'envoi
            $objNewsletter = new newsletter();
            $objNewsletter->open($_GET['id_newsletter']);
            $objNewsletter->send(); 
            unset($objNewsletter);
            
            ploopi_redirect('admin.php?newsletterToolbarSend=tabNewsletterSendOk');
          }
          else
          {
            $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletter_return_send'] = _NEWSLETTER_LABEL_RETURN_SEND_ERROR;
            ploopi_redirect('admin.php?newsletterToolbarSend=tabNewsletterSendTodo');
          }
        }
        ploopi_redirect('admin.php');
      break;
      /*
       * Gestion des paramètres
       */
      case 'newsletter_param_save':
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_PARAM))
        {
          include_once './modules/newsletter/class_newsletter_param.php';
          // HOST
          $objNewsletterParam = new newsletter_param();
          $objNewsletterParam->open($_SESSION['ploopi']['moduleid'],'host'); //ouvert ou pas on s'en fiche c'est juste pour le save de data_object
          $objNewsletterParam->fields['value'] = $_POST['host'];
          $objNewsletterParam->save();
          // FROM_NAME
          $objNewsletterParam = new newsletter_param();
          $objNewsletterParam->open($_SESSION['ploopi']['moduleid'],'from_name'); //ouvert ou pas on s'en fiche c'est juste pour le save de data_object
          $objNewsletterParam->fields['value'] = $_POST['from_name'];
          $objNewsletterParam->save();
          // FROM_EMAIL
          $objNewsletterParam = new newsletter_param();
          $objNewsletterParam->open($_SESSION['ploopi']['moduleid'],'from_email'); //ouvert ou pas on s'en fiche c'est juste pour le save de data_object
          $objNewsletterParam->fields['value'] = $_POST['from_email'];
          $objNewsletterParam->save();
          // SEND_BY
          $objNewsletterParam = new newsletter_param();
          $objNewsletterParam->open($_SESSION['ploopi']['moduleid'],'send_by'); //ouvert ou pas on s'en fiche c'est juste pour le save de data_object
          $objNewsletterParam->fields['value'] = ($_POST['send_by'] == '') ? 0 : $_POST['send_by'];
          $objNewsletterParam->save();
          unset($objNewsletterParam);
        }
        ploopi_redirect('admin.php');
      break;
      case 'newsletter_save_global_validator': // sauvegarde les validateurs globaux
        if(ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_VALIDATOR))
          ploopi_validation_save(_NEWSLETTER_OBJECT_NEWSLETTER,'newsletter');
        ploopi_redirect('admin.php');
      break;
      /*
       * Affichage des onglets
       */
      default:
        // Onglet liste des letters
        $tabs['tabNewsletter'] = array('title'   => _NEWSLETTER_LABELTAB_LETTER_LIST, 'url' => "admin.php?newsletterTabAdmin=tabNewsletter");
        // Bouton de gestion des inscrits
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_SUBSCRIBER))
            $tabs['tabNewsletterSubscriber'] = array( 'title' => _NEWSLETTER_LABELTAB_SUBSCRIBER, 'url' => "admin.php?newsletterTabAdmin=tabNewsletterSubscriber");
        //Onglet de gestion des envois
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_SEND))
            $tabs['tabNewsletterSend'] = array('title'   => _NEWSLETTER_LABELTAB_SEND, 'url' => "admin.php?newsletterTabAdmin=tabNewsletterSend");
        //Onglet de gestion des bannières
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_BANNIERE))
          $tabs['tabNewsletterBanniere'] = array('title'   => _NEWSLETTER_LABELTAB_BANNIERE, 'url' => "admin.php?newsletterTabAdmin=tabNewsletterBanniere");
        //Onglet de gestion des parametres
        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_PARAM) || ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_VALIDATOR))
            $tabs['tabNewsletterParam'] = array('title'   => _NEWSLETTER_LABELTAB_PARAM, 'url' => "admin.php?newsletterTabAdmin=tabNewsletterParam");
        
        if (!empty($_GET['newsletterTabAdmin'])) $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'] = $_GET['newsletterTabAdmin'];
        if (!isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'])) $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'] = '';

        if (ploopi_isactionallowed(_NEWSLETTER_ACTION_PARAM))
        {
          //Force à aller dans les param si ce module n'est pas paramétré.
          include_once './modules/newsletter/class_newsletter_param.php';
          $objNewsletterParam = new newsletter_param();
          if(!$objNewsletterParam->open($_SESSION['ploopi']['moduleid'],'host'))
            $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'] = 'tabNewsletterParam';
          unset($objNewsletterParam);
        }

        echo $skin->create_pagetitle(str_replace("LABEL",$_SESSION['ploopi']['modulelabel'],_NEWSLETTER_PAGE_TITLE));
        echo $skin->create_tabs($tabs,$_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin']);

        //Inclusion en fonction du bouton selectionné
        switch($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'])
        {
          // Gestion des envois
          case 'tabNewsletterSend':
            include './modules/newsletter/admin_send.inc.php';
          break;
          // Gestion des inscrits
          case 'tabNewsletterSubscriber':
            include './modules/newsletter/admin_subscrib.inc.php';
          break;
          //Gestion des Banniere
          case 'tabNewsletterBanniere':
            include './modules/newsletter/admin_banniere.inc.php';
          break;
          // Paramétrage
          case 'tabNewsletterParam':
            include './modules/newsletter/admin_param.inc.php';
          break;
          // Gestion des newsletter
          default:
            include './modules/newsletter/admin_newsletter.inc.php';
          break;
        }
      break;
      
    }
  break;
}
?>
