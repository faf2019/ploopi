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
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

// Gestion du menu selectionné
if (!empty($_GET['newsletterToolbarNewsletter']))
  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'] = $_GET['newsletterToolbarNewsletter'];
if (!isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter']))
  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'] = 'tabNewsletterList';

// Si c'est une demande de modification on passe sur l'icone de modif en force
if(isset($op) && ($op == 'newsletter_create' || $op == 'newsletter_modify'))  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'] = 'tabNewsletterNew';

// Barre de menu
$toolbar = array();
$toolbar['tabNewsletterList'] = array(
                                  'title' => _NEWSLETTER_LABELICON_LIST,
                                  'url'   => 'admin.php?newsletterToolbarNewsletter=tabNewsletterList',
                                  'icon'  => './modules/newsletter/img/newsletter_list.png',
                                  'width' => '80'
                              );
if(ploopi_isactionallowed(_NEWSLETTER_ACTION_WRITE))
{
  $toolbar['tabNewsletterNew'] = array(
                                    'title' => _NEWSLETTER_LABELICON_NEW,
                                    'url'   => 'admin.php?newsletterToolbarNewsletter=tabNewsletterNew&op=newsletter_create',
                                    'icon'  => './modules/newsletter/img/newsletter_new.png',
                                    'width' => '80'
                                );
}
?>

<div style="padding: 2px 0 0 0;">
  <?php
  echo $skin->create_toolbar($toolbar,$_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter']);
  ?>
</div>
<?php

// Si c'est juste pour une validation on doit allez en edition mais pas de bouton tabNewsletterNew. On passe en force le tabNewsletterNew
if (!empty($_GET['newsletterToolbarNewsletter']) && $_GET['newsletterToolbarNewsletter'] != $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'])
  $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'] = $_GET['newsletterToolbarNewsletter'];

switch ($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'])
{
  case 'tabNewsletterNew': // Création / Modification de newsletter
    include './modules/newsletter/admin_newsletter_edit.inc.php';
    break;
    default: // Liste des newsletter
        include './modules/newsletter/admin_newsletter_list.inc.php';
    break;
}
?>