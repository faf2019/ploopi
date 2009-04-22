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
 * Gestionnaire d'op de la Newsletter
 */

/**
 * Partie op public du module
 *
 * @package newsletter
 * @subpackage op
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

switch($ploopi_op)
{
  /*
   * Affichage de la newsletter dans l'iframe de l'editeur ou en affichage direct
   */
  case 'newsletter_tpl';
  case 'newsletter_consult';
    ploopi_init_module('newsletter');

    /*
     * LOAD LANGUAGE FILE
     */
    if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'] != 'french' && file_exists("./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php"))
    {
        include_once "./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php";
    }
    else include_once "./lang/french.php"; // default language file (french)

    if($ploopi_op == 'newsletter_consult') $strNewsletterMode = 'display';
    include './modules/newsletter/display.php';
  break;

  /*
   * Inscription/désinscription depuis le bloc en front
   */
  case 'newsletter_subscribe':
    // Gestion des abonnements et désabonnements en front
    ploopi_init_module('newsletter');

    $return = -1;

    if (!empty($_POST['subscription_email']) && ploopi_checkemail($_POST['subscription_email']) &&
        !empty($_GET['id_module']) && is_numeric($_GET['id_module']))
    {
      include_once './modules/newsletter/class_newsletter_subscriber.php';

      $objSubscriber = new newsletter_subscriber();
      if (!$objSubscriber->open($_POST['subscription_email'],$_GET['id_module']))
      {
        $objSubscriber->fields['email'] = $_POST['subscription_email'];
        $objSubscriber->fields['id_module'] = $_GET['id_module'];
        $objSubscriber->save();
        $return = _NEWSLETTER_SUBSCRIPTION_SUBSCRIBED;
      }
      else
      {
        $objSubscriber->delete();
        $return = _NEWSLETTER_SUBSCRIPTION_UNSUBSCRIBED;
      }
    }
    else $return = _NEWSLETTER_SUBSCRIPTION_ERROR_EMAIL;

    $return2 = '';
    if(isset($_GET['headingid'])) $return2 .= '&headingid='.$_GET['headingid'];
    if(isset($_GET['articleid'])) $return2 .= '&articleid='.$_GET['articleid'];

    ploopi_redirect("index.php?newsletter_subscription_return={$return}{$return2}");
  break;

  /*
   * Désinscription depuis le lien dans la newsletter qui redirige vers une page spéciale en front
   */
  case 'newsletter_unsubscrib':
    ploopi_init_module('newsletter');

    $return = _NEWSLETTER_SUBSCRIPTION_ERROR_EMAIL;

    if (!empty($_POST['unsubcrib_email']) && ploopi_checkemail($_POST['unsubcrib_email']) &&
        !empty($_GET['id_module']) && is_numeric($_GET['id_module']))
    {
      include_once './modules/newsletter/class_newsletter_subscriber.php';

      $objSubscriber = new newsletter_subscriber();
      if ($objSubscriber->open($_POST['unsubcrib_email'],$_GET['id_module']))
      {
        $objSubscriber->delete();
        $return = _NEWSLETTER_SUBSCRIPTION_UNSUBSCRIBED;
      }
    }
    ploopi_redirect("index.php?newsletter_unsubscrib_return={$return}");
  break;

  case 'newsletter_display_banniere':

    if(!empty($_GET['banniere_id']))
    {
      include_once './include/functions/filesystem.php';
      include_once './include/functions/documents.php';
      include_once './include/classes/documents.php';

      $doc = new documentsfile();
      if ($doc->open($_GET['banniere_id']))
        ploopi_downloadfile($doc->getfilepath(),$doc->fields['label'],false,false);
    }
    ploopi_die();
  break;

  default:
  break;
}
?>