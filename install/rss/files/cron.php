<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Gestion de la mise à jour automatique des flux via cron
 *
 * @package rss
 * @subpackage cron
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * inclusions de quelques dépendances (car l'environnement chargé par le script principal est très léger)
 */

include_once './include/functions/date.php';
include_once './include/functions/string.php';
include_once './include/functions/search_index.php';

/**
 * Chargement de la classe principale de gestion des flux
 */

include_once './modules/rss/class_rss_feed.php';

/**
 * Initilialisation du module
 */

ploopi_init_module('rss', false, false, false);

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

$select =  "SELECT      feed.id,
                        feed.lastvisit,
                        feed.revisit
            FROM        ploopi_mod_rss_feed feed
            WHERE       feed.id_module = '{$cron_moduleid}'
            ";

$result = $db->query($select);
while ($fields = $db->fetchrow($result))
{
  if(($fields['lastvisit'] == 0) || (ploopi_createtimestamp() - $fields['lastvisit']) > $fields['revisit'])
  {
    $rss_feed = new rss_feed();
    if ($rss_feed->open($fields['id']))
    {
      $rss_feed->updatecache();
    }
    unset($rss_feed);
  }
}
?>
