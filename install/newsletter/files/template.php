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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package newsletter
 * @subpackage template
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('newsletter');

if(isset($_GET['switch_newsletter_unsubscrib']) || isset($_GET['newsletter_unsubscrib_return']))
{
  if (isset($_GET['newsletter_unsubscrib_return']) && isset($newsletter_subscription_messages[$_GET['newsletter_unsubscrib_return']]) && !empty($newsletter_subscription_messages[$_GET['newsletter_unsubscrib_return']])) // réponse suite à une demande de désabonnement
  {
    // réponse Désabonnement par mail
    $template_body->assign_block_vars(
        'switch_newsletter_unsubscrib_response', 
        array(
            'RESPONSE' => $newsletter_subscription_messages[$_GET['newsletter_unsubscrib_return']]
        )
    );
  }
  else
  {
    // action Désabonnement par mail
    $template_body->assign_block_vars(
        'switch_newsletter_unsubscrib', 
        array('ACTION' => ploopi_urlencode("index.php?id_module={$template_moduleid}&ploopi_op=newsletter_unsubscrib", null, null, null, null, false))
    );
  }    
}

$template_body->assign_block_vars(
    'switch_newsletter_subscription', 
    array('ACTION' => ploopi_urlencode("index.php?id_module={$template_moduleid}&ploopi_op=newsletter_subscribe", null, null, null, null, false))
);

if (isset($_GET['newsletter_subscription_return']) && isset($newsletter_subscription_messages[$_GET['newsletter_subscription_return']]) && !empty($newsletter_subscription_messages[$_GET['newsletter_subscription_return']])) // réponse suite à une demande d'abonnement
{
    $template_body->assign_block_vars(
        'switch_newsletter_subscription.switch_response', 
        array(
            'CONTENT' => $newsletter_subscription_messages[$_GET['newsletter_subscription_return']]
        )
    );
        
}
?>

