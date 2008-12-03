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
?>

<?
/**
 * Class forum_cat for ploopi_mod_forum_cat (Categories)
 *
 */
class forum_cat extends data_object
{
  /**
   * Constructor
   *
   * @return object forum_cat
   */
  function forum_cat()
  {
    parent::data_object('ploopi_mod_forum_cat');
    $this->oldvisible = 1;
  }

  /**
   * Open with a control if id_cat is ok
   *
   */
  function open($intId)
  {

    if(!is_numeric($intId) || $intId <= 0) return false;

    $numrows = parent::open($intId);

    $this->oldvisible = $this->fields['visible']; // For change state for search index

    return $numrows;
  }

  /**
   * Save the categories data's
   *
   */
  function save()
  {
    global $db;

    $this->setuwm(); // first for getlastposition !

    if($this->new)
    {
      $this->fields['position'] = $this->getlastposition()+1;
      $this->fields['id_author'] = $_SESSION['ploopi']['user']['id'];
      $this->fields['author'] = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
      $this->fields['timestp'] = ploopi_createtimestamp();
      /** Log **/
      ploopi_create_user_action_log(_FORUM_ACTION_ADD_CAT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
    }
    else
    {
      $this->fields['lastupdate_id_user'] = $_SESSION['ploopi']['user']['id'];
      $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();
      /** Log **/
      ploopi_create_user_action_log(_FORUM_ACTION_MODIFY_CAT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');
    }

    //***** Search Index *****//
    ploopi_search_create_index(_FORUM_OBJECT_CAT, $this->fields['id'], $this->fields['title'], strip_tags(html_entity_decode($this->fields['title'].' '.$this->fields['description'])), '', true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);

    parent::save();
  }

  /**
   * Delete a categories width :
   * - All link with moderator
   * - All subscription
   * - All subjects and messages !
   *
   */
  function delete()
  {

    global $db;

    //***** Delete all moderator to this categ *****//
    unset($_SESSION['ploopi']['workflow']['users_selected']);
    ploopi_validation_save(_FORUM_OBJECT_CAT, $this->fields['id']);

    //***** Delete all subscription to this categ and all her subjects *****//
    //***** Delete all search index *****//
    // Search all subject in this cat
    $arrListSubject[] = 0;
    $strForumSql = "SELECT id, id_subject
                    FROM ploopi_mod_forum_mess
                    WHERE ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
                      AND ploopi_mod_forum_mess.id_cat = {$this->fields['id']}";
    $objForumSqlResult = $db->query($strForumSql);
    while ($arrForumFields = $db->fetchrow($objForumSqlResult))
    {
      if($arrForumFields['id'] == $arrForumFields['id_subject'])
      {
        // Create array with list of subject in this cat
        $arrListSubject[] = $arrForumFields['id'];
        // Delete search_index of this subject
        ploopi_search_remove_index(_FORUM_OBJECT_SUBJECT, $arrForumFields['id']);
      }
      else
      {
        // Delete search_index of this message
        ploopi_search_remove_index(_FORUM_OBJECT_MESSAGE, $arrForumFields['id']);
      }
    }

    $strListSubject = implode(",",$arrListSubject);

    // Search all id subscription's link with this cat and her subject
    $strListSubscripToDelete = "''";
    $strForumSql = "SELECT id
                    FROM ploopi_subscription
                    WHERE ploopi_subscription.id_module = {$_SESSION['ploopi']['moduleid']}
                      AND ((ploopi_subscription.id_object = "._FORUM_OBJECT_SUBJECT."
                              AND ploopi_subscription.id_record = {$this->fields['id']})
                            OR
                           (ploopi_subscription.id_object = "._FORUM_OBJECT_MESSAGE."
                              AND ploopi_subscription.id_record IN ({$strListSubject}))
                          )";

    $objForumSqlResult = $db->query($strForumSql);
    while ($arrForumFields = $db->fetchrow($objForumSqlResult))
      $strListSubscripToDelete .= ",'".$arrForumFields['id']."'";

    $strForumSqlDelete = "DELETE FROM ploopi_subscription
                          WHERE ploopi_subscription.id IN ({$strListSubscripToDelete})";
    $db->query($strForumSqlDelete);

    $strForumSqlDelete = "DELETE FROM ploopi_subscription_action
                          WHERE ploopi_subscription_action.id_subscription IN ({$strListSubscripToDelete})";
    $db->query($strForumSqlDelete);

    //***** Delete all subjects and messages in this categorie *****//
    $strForumSqlDelete = "DELETE FROM ploopi_mod_forum_mess
                          WHERE ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
                            AND ploopi_mod_forum_mess.id_cat = {$this->fields['id']}";
    $db->query($strForumSqlDelete);

    //***** Delete search_index of this categorie *****//
    ploopi_search_remove_index(_FORUM_OBJECT_CAT, $this->fields['id']);

    /** Log **/
    ploopi_create_user_action_log(_FORUM_ACTION_DELETE_CAT, ploopi_strcut($this->fields['title'],200).'(id='.$this->fields['id'].')');

    // Delete categorie
    parent::delete();

    $this->renumber();
  }

  /**
   * renumber all position categories
   *
   */
  function renumber()
  {
    global $db;

    $db->query('SET @compteur=0');
    $db->query("UPDATE ploopi_mod_forum_cat
                    SET position = @compteur:=@compteur+1
                    WHERE ploopi_mod_forum_cat.id_module = {$this->fields['id_module']}
                    ORDER BY ploopi_mod_forum_cat.position ASC");
  }

  /**
   * Get the last position
   *
   * @return int last position
   */
  function getlastposition()
  {
    global $db;

    $strRequest = "SELECT MAX(ploopi_mod_forum_cat.position) AS maxposit
                    FROM ploopi_mod_forum_cat
                    WHERE ploopi_mod_forum_cat.id_module = {$this->fields['id_module']}
                    GROUP BY ploopi_mod_forum_cat.id_module";
    $sqlmaxposition = $db->query($strRequest);

    if(!$db->numrows($sqlmaxposition)) return 0;

    $value = $db->fetchrow($sqlmaxposition);

    return $value['maxposit'];
  }
}
?>