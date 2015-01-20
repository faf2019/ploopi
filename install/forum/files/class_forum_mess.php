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

/**
 * Gestion des sujets et messages
 *
 * @package forum
 * @subpackage subjects and messages
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Class forum_mess for ploopi_mod_forum_mess (Message et Subject)
 *
 */
class forum_mess extends data_object
{
  /**
   * Constructor
   *
   * @return object forum_mess
   */
  function forum_mess()
  {
    parent::data_object('ploopi_mod_forum_mess');
  }

  /**
   * Open with a control if id mess is ok
   *
   * @param int id message
   * @return int nombre d'enregistrements
   */
  function open($intId)
  {
    if(!is_numeric($intId) || $intId <= 0) return false;

    $numrows = parent::open($intId);

    return $numrows;
  }

  /**
   * Save the message/subject data's
   */
  function save()
  {
    $booForumNew = $this->new;

    $booForumIsAdminModer = forum_IsAdminOrModer($this->fields['id_cat'],_FORUM_ACTION_ADMIN);

    $objForumMyCat = new forum_cat();
    $objForumMyCat->open($this->fields['id_cat']);

    if($this->new) // creation
    {
      $this->fields['id_author'] = $_SESSION['ploopi']['user']['id'];
      $this->fields['author'] = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
      $this->fields['timestp'] = ploopi_createtimestamp();
    }
    else
    {
      // Modification
      if($this->fields['id_author'] == $_SESSION['ploopi']['user']['id'])
      {
        $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();
      }
      else // Moderation
      {
        $this->fields['moderate_id_user'] = $_SESSION['ploopi']['user']['id'];
        $this->fields['moderate_timestp'] = ploopi_createtimestamp();
      }
    }
    $this->setuwm();

    // Forced validation (it's a moderator and it's his message !)
    if($booForumIsAdminModer && $this->fields['id_author'] == $_SESSION['ploopi']['user']['id'])
      $this->fields['validated'] = 1;

    // Save if title is ok for a subject (or it's not a subject...).
    if((($this->fields['id_subject'] == $this->fields['id'] || $this->fields['id_subject'] == 0) && trim($this->fields['title']) != '')
        || ($this->fields['id_subject'] != $this->fields['id'] && $this->fields['id_subject'] > 0))
    {
      parent::save();

      //If id_subject = 0 it's a new subject => re-save width id_subject = id (for group by in request and lot of test !!!)
      if($this->fields['id_subject'] == 0)
      {
        $this->fields['id_subject'] = $this->fields['id'];
        parent::save();
      }

//      if($booForumIsAdminModer)
//      {
//        if($this->fields['closed'])
//          $this->closeSubject();
//        else
//          $this->openSubject();
//      }

      if($this->fields['id_subject'] == $this->fields['id']) // it is a subject
      {
        // create search index
        ploopi_search_create_index(_FORUM_OBJECT_SUBJECT, $this->fields['id'], $this->fields['title'], strip_tags(ploopi_html_entity_decode($this->fields['title'])), '', true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);
        ploopi_search_create_index(_FORUM_OBJECT_MESSAGE, $this->fields['id'], $this->fields['title'], strip_tags(ploopi_html_entity_decode($this->fields['title'].' '.$this->fields['content'])), '', true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);
        // Log
        if($booForumNew) // Create
          ploopi_create_user_action_log(_FORUM_ACTION_ADD_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
        else // Modify
        {
          if($this->fields['id_author'] == $_SESSION['ploopi']['user']['id']) //By author
            ploopi_create_user_action_log(_FORUM_ACTION_MODIFY_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
          else // By moderator
            ploopi_create_user_action_log(_FORUM_ACTION_MODERATE_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
        }
      }
      else
      {
        // create search index
        ploopi_search_create_index(_FORUM_OBJECT_MESSAGE, $this->fields['id'], $this->fields['title'], strip_tags(ploopi_html_entity_decode($this->fields['title'].' '.$this->fields['content'])), '', true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);
        // Log
        if($booForumNew) // Create
          ploopi_create_user_action_log(_FORUM_ACTION_ADD_MESSAGE, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
        else // Modify
        {
          if($this->fields['id_author'] == $_SESSION['ploopi']['user']['id']) //By author
            ploopi_create_user_action_log(_FORUM_ACTION_MODIFY_MESSAGE, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
          else // By moderator
            ploopi_create_user_action_log(_FORUM_ACTION_MODERATE_MESSAGE, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
        }
      }

      // Just for new subject/mess -> subscription
      if($booForumNew) $this->SendToSubscribers();
    }
    unset($objForumMyCat);
  }

  /**
   * Validate a subject/message by a moderator
   */
  function validate()
  {
    $this->fields['validated'] = 1;
    $this->fields['validated_id_user'] = $_SESSION['ploopi']['user']['id'];
    $this->fields['validated_timestp'] = ploopi_createtimestamp();
    parent::save();

    // Log
    if($this->fields['id_subject'] == $this->fields['id']) // it is a subject
      ploopi_create_user_action_log(_FORUM_ACTION_VALIDATE_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
    else
      ploopi_create_user_action_log(_FORUM_ACTION_VALIDATE_MESSAGE, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');

    $this->SendToSubscribers();
  }

  /**
   * Delete a message
   */
  function delete()
  {
    if($this->fields['id'] == $this->fields['id_subject'])
      $this->deleteSubject();
    else
    {
      // Delete search_index of this message
      ploopi_search_remove_index(_FORUM_OBJECT_MESSAGE, $this->fields['id']);

      // Log
      ploopi_create_user_action_log(_FORUM_ACTION_DELETE_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');

      parent::delete();
    }
  }

  /**
   * Open a subject
   *
   * All messages in this subject will be unmarqued "closed"
   */
  function openSubject()
  {
    global $db;

    $strForumSqlOpenSubject = "UPDATE ploopi_mod_forum_mess SET ploopi_mod_forum_mess.closed = '0'
                                WHERE ploopi_mod_forum_mess.id_subject = {$this->fields['id_subject']}
                                  AND ploopi_mod_forum_mess.id_module = {$this->fields['id_module']}";
    $db->query($strForumSqlOpenSubject);

  }

  /**
   * Close the subject,
   * All messages in this subject will be marqued "closed" !
   */
  function closeSubject()
  {
    global $db;

    $strForumSqlCloseSubject = "UPDATE ploopi_mod_forum_mess SET ploopi_mod_forum_mess.closed = '1'
                                WHERE ploopi_mod_forum_mess.id_subject = {$this->fields['id_subject']}
                                  AND ploopi_mod_forum_mess.id_module = {$this->fields['id_module']}";
    $db->query($strForumSqlCloseSubject);
  }

  /**
   * Delete the subject and all messages in this subject and subscription
   */
  function deleteSubject()
  {
    global $db;

    // Search all id subscription's link with this subject
    $strListSubscripToDelete = "''";
    $strForumSql = "SELECT id
                    FROM ploopi_subscription
                    WHERE ploopi_subscription.id_module = {$_SESSION['ploopi']['moduleid']}
                      AND ploopi_subscription.id_object = "._FORUM_OBJECT_MESSAGE."
                      AND ploopi_subscription.id_record = {$this->fields['id']}";

    $objForumSqlResult = $db->query($strForumSql);
    while ($arrForumFields = $db->fetchrow($objForumSqlResult))
      $strListSubscripToDelete .= ",'".$arrForumFields['id']."'";

    $strForumSqlDelete = "DELETE FROM ploopi_subscription
                          WHERE ploopi_subscription.id IN ({$strListSubscripToDelete})";
    $db->query($strForumSqlDelete);

    $strForumSqlDelete = "DELETE FROM ploopi_subscription_action
                          WHERE ploopi_subscription_action.id_subscription IN ({$strListSubscripToDelete})";
    $db->query($strForumSqlDelete);

    // Search all mess for Delete search_index of all message in this subject
    $strForumSqlSearch = "SELECT id
                          FROM ploopi_mod_forum_mess
                          WHERE id_module = {$this->fields['id_module']}
                            AND id_cat = {$this->fields['id_cat']}
                            AND id_subject = {$this->fields['id']}";
    $objForumSqlResult = $db->query($strForumSqlSearch);
    while ($arrForumFields = $db->fetchrow($objForumSqlResult))
      ploopi_search_remove_index(_FORUM_OBJECT_MESSAGE, $arrForumFields['id']);

    // Delete search_index of this subject
    ploopi_search_remove_index(_FORUM_OBJECT_SUBJECT, $this->fields['id']);

    // Log
    ploopi_create_user_action_log(_FORUM_ACTION_DELETE_SUBJECT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');

    // Delete subject and all mess
    $strForumSqlDelete = "DELETE FROM ploopi_mod_forum_mess
                          WHERE id_module = {$this->fields['id_module']}
                            AND id_cat = {$this->fields['id_cat']}
                            AND id_subject = {$this->fields['id']}";
    $db->query($strForumSqlDelete);

  }

  /**
   * Send ticket for subcription
   */
  function SendToSubscribers()
  {
    $arrForumTo = array();
    $arrAdminOrModerat = array();

    include_once './modules/forum/class_forum_cat.php';

    $objForumCat = new forum_cat();
    $objForumCat->open($this->fields['id_cat']);

    if($this->fields['id'] == $this->fields['id_subject']) // it's a new subject
    {
      $strForumTitleCat = $objForumCat->fields['title'];
      $strMessage = _FORUM_TICKET_NEW_SUBJECT;
    }
    else // it's a response
    {
      $objForumSubject = new forum_mess();
      $objForumSubject->open($this->fields['id_subject']);
      $strForumTitleSubject = $objForumCat->fields['title'].' >> '.$objForumSubject->fields['title'];
      unset($objForumSubject);
      $strMessage = _FORUM_TICKET_NEW_MESSAGE;
    }

    // If it's not validate (not public) -> send just for subscribers type admin or moderator...
    if($this->fields['validated'] == 0 || $objForumCat->fields['closed'] == 1 || $this->fields['closed'] == 1)
    {
      $arrAdminOrMode = array();
      // Get list of admin
      // $arrAdminOrMode = ploopi_actions_getusers(_FORUM_ACTION_ADMIN); // Uncomment this line if you want alway send to admin
      // Get list of moderator
      $arrAdminOrMode += ploopi_validation_get(_FORUM_OBJECT_CAT,$this->fields['id_cat']);
      foreach($arrAdminOrMode as $value)
      {
          if ($value['type_validation'] == 'group') // recherche des utilisateurs du groupe
          {
            $value['type_validation'] = 'user'; //petite astuce pour récupérer l'enregistrement comme si c'était un utilisateur
            $objGroup = new group();
            $objGroup->open($value['id_validation']);
            $arrUsers = $objGroup->getusers();
            foreach($arrUsers as $arrUser)
            {
              $value['id_validation'] = $arrUser['id'];
              $arrForumTo[$value['id_validation']] = $value;
            }
          }
          else $arrForumTo[$value['id_validation']] = $value;
      }
      unset($arrAdminOrMode);

      if($this->fields['validated'] == 0)
        $strMessage .= '<br/><b>'._FORUM_STAY_VALIDATED.'</b>';
      if($objForumCat->fields['closed'] == 1)
        $strMessage .= '<br/><b>'._FORUM_CLOSE_CAT.'</b>';
      if($this->fields['closed'] == 1)
        $strMessage .= '<br/><b>'._FORUM_CLOSE_SUBJET.'</b>';
    }
    else // cat or message is public. Send to all subscribers
    {
      // Get subscribers for the categories
      if($this->fields['id'] == $this->fields['id_subject']) // it's a new subject
      {
        $arrForumTo = ploopi_subscription_getusers(_FORUM_OBJECT_SUBJECT, $this->fields['id_cat'], array(_FORUM_ACTION_ADD_SUBJECT));
      }
      else // it's a response
      {
        $arrForumTo = ploopi_subscription_getusers(_FORUM_OBJECT_SUBJECT, $this->fields['id_cat'], array(_FORUM_ACTION_ADD_MESSAGE));
        $arrForumTo += ploopi_subscription_getusers(_FORUM_OBJECT_MESSAGE, $this->fields['id_subject'], array(_FORUM_ACTION_ADD_MESSAGE));
      }
    }
    unset($objForumCat);

    if($this->fields['id'] == $this->fields['id_subject']) // it's a subject
      ploopi_subscription_notify(_FORUM_OBJECT_SUBJECT, $this->fields['id'], _FORUM_ACTION_ADD_SUBJECT, $strForumTitleCat, array_keys($arrForumTo), $strMessage);
    else
      ploopi_subscription_notify(_FORUM_OBJECT_MESSAGE, $this->fields['id'], _FORUM_ACTION_ADD_MESSAGE, $strForumTitleSubject, array_keys($arrForumTo), $strMessage);
  }
}
?>
