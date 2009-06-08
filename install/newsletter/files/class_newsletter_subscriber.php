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
 * Gestion des newsletters
 *
 * @package newsletter
 * @subpackage subscriber
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_newsletter_subscriber
 *
 * @package newsletter
 * @subpackage subscriber
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class newsletter_subscriber extends data_object
{
  private $IdEmail;
  /**
   * Constructeur de la classe
   *
   * @return data_object
   */
  function newsletter_subscriber()
  {
      parent::data_object('ploopi_mod_newsletter_subscriber','email','id_module');
  }

  /**
   * Enter description here...
   *
   * @param string email
   * @param int id_module (option)
   * @return int nombre d'enregistrements
   */
  function open($strEmail, $intIdModule = -1)
  {
    // Recupération de la clef d'ouverture (email)
    $this->IdEmail = $strEmail;

    if($intIdModule == -1 && isset($_SESSION['ploopi']['moduleid'])) $intIdModule = $_SESSION['ploopi']['moduleid'];
    return parent::open($strEmail,$intIdModule);
  }

  /**
   * Enregistre une inscription
   *
   * @return boolean true si l'enregistrement a été effectué
   */
  function save()
  {
    // si c'est un save depuis frontoffice (module webedit) besoin de faire init_module pour les constantes de Newsletter pour les logs
    if(!ploopi_ismoduleallowed('newsletter')) ploopi_init_module('newsletter');

    $new_subscribe = $this->new;

    if($this->new) // si c'est une nouvelle inscription ces données sont ajoutées d'office sinon elles peuvent changer
    {
      $this->fields['timestp_subscribe'] = ploopi_createtimestamp();
      $this->fields['ip'] = implode(',',ploopi_getip());
      $this->fields['active'] = 1;
    }
    else
    {
      /*
      les clefs indiquées au constructeur newsletter_subscriber sont 'email' et 'id_module' donc email n'est pas modifiable directement... (contrainte ploopi)
      Donc, en cas de modification d'email, on force la suppression et la 're'création d'une nouvelle inscription
      */
      // Contrôle si on va changer de email
      if(!$new_subscribe && $this->IdEmail != $this->fields['email'])
      {
        $strEmailTmp = $this->fields['email'];
        $this->fields['email'] = $this->IdEmail;
        $this->delete(false);
        $this->fields['email'] = $strEmailTmp;
        $this->new = true;
      }
    }

    if(empty($this->fields['id_module'])) $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    $result_save = parent::save();

    //Log
    if($result_save)
    {
      if($new_subscribe)
        ploopi_create_user_action_log(_NEWSLETTER_ACTION_NEW_SUBSCRIBER, ploopi_strcut($this->fields['email'],200),-1,$this->fields['id_module']);
      else
        ploopi_create_user_action_log(_NEWSLETTER_ACTION_MODIF_SUBSCRIBER, ploopi_strcut($this->fields['email'],200),-1,$this->fields['id_module']);
    }

    return $result_save;
  }

  /**
   * Supprime une inscription avec log
   *
   */
  function delete($withLog = true)
  {
    // Log
    if($withLog)
      ploopi_create_user_action_log(_NEWSLETTER_ACTION_DELETE_SUBSCRIBER, ploopi_strcut($this->fields['email'],200),-1,$this->fields['id_module']);

    parent::delete();
  }
}
?>