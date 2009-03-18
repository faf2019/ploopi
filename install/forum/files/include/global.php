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
 * Fonctions, constantes, variables globales
 *
 * @package forum
 * @subpackage global
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

Define ('_FORUM_OBJECT_CAT',      1);
Define ('_FORUM_OBJECT_SUBJECT',  2);
Define ('_FORUM_OBJECT_MESSAGE',  3);

define ('_FORUM_ACTION_ADMIN',              1); // Enable in role
define ('_FORUM_ACTION_ADD_CAT',            2); // Not show in role
define ('_FORUM_ACTION_ADD_SUBJECT',        3); //  "
define ('_FORUM_ACTION_ADD_MESSAGE',        4); //  "
define ('_FORUM_ACTION_MODIFY_CAT',         5); //  "
define ('_FORUM_ACTION_MODIFY_SUBJECT',     6); //  "
define ('_FORUM_ACTION_MODIFY_MESSAGE',     7); //  "
define ('_FORUM_ACTION_MODERATE_SUBJECT',   8); //  "
define ('_FORUM_ACTION_MODERATE_MESSAGE',   9); //  "
define ('_FORUM_ACTION_VALIDATE_SUBJECT',  10); //  "
define ('_FORUM_ACTION_VALIDATE_MESSAGE',  11); //  "
define ('_FORUM_ACTION_DELETE_CAT',        12); //  "
define ('_FORUM_ACTION_DELETE_SUBJECT',    13); //  "
define ('_FORUM_ACTION_DELETE_MESSAGE',    14); //  "


/**
 * get if this message, subject, categorie  is enable
 *
 * @param int $id_object
 * @param int $id_record
 * @param int $id_module
 * @return true / false
 */
function forum_record_isenabled($id_object, $id_record, $id_module)
{
    global $db;

    $enabled = false;

    if(!isset($_SESSION['ploopi']['forum'][$id_module]['catvisible']))
    {
      $strForumSql = "SELECT id,visible
                      FROM ploopi_mod_forum_cat
                      WHERE ploopi_mod_forum_cat.id_module = {$id_module}";
      $objForumSqlResult = $db->query($strForumSql);
      while ($arrForumFields = $db->fetchrow($objForumSqlResult))
      {
        $_SESSION['ploopi']['forum'][$id_module]['catvisible'][$arrForumFields['id']] = $arrForumFields['visible'];
      }
    }

    include_once './modules/forum/include/functions.php';

    switch($id_object)
    {
        case _FORUM_OBJECT_CAT:
          if(forum_IsAdminOrModer($id_record,_FORUM_OBJECT_CAT,false,$id_module)) return(true);

          if (isset($_SESSION['ploopi']['forum'][$id_module]['catvisible'][$arrForumFields['id']]))
            return($_SESSION['ploopi']['forum'][$id_module]['catvisible'][$arrForumFields['id']]);

          break;
        case _FORUM_OBJECT_SUBJECT:
        case _FORUM_OBJECT_MESSAGE:

          include_once './modules/forum/class_forum_mess.php';

          $objForumMess = new forum_mess();
          // mess open and know state of categorie and categorie is visible
          if ($objForumMess->open($id_record))
          {
            //if user is a admin or moderator of this cat of forum
            if(forum_IsAdminOrModer($objForumMess->fields['id_cat'],_FORUM_OBJECT_CAT,false,$id_module)) return(true);

            // If this cat is visble
            if(isset($_SESSION['ploopi']['forum'][$id_module]['catvisible'][$objForumMess->fields['id_cat']])
                && $_SESSION['ploopi']['forum'][$id_module]['catvisible'][$objForumMess->fields['id_cat']] == 1)
              return($objForumMess->fields['validated']); // If mess/subject is validated
          }
          break;
    }

    return($enabled);
}
?>