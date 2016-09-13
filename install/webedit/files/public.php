<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Partie publique du module
 *
 * @package webedit
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ovensia\ploopi\module::init('webedit');

global $article_status;
global $heading_sortmodes;

/**
 * Inclusions des classes du module
 */
include_once './modules/webedit/class_article.php';
include_once './modules/webedit/class_heading.php';

if (!isset($op)) $op = '';

switch($op)
{
    case 'display_iframe':
        /**
         * Affichage de la partie frontoffice dans le portail (dans un iframe)
         */
        include_once './modules/webedit/display.php';
    break;

    default:
        echo $skin->create_pagetitle(ovensia\ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
        echo $skin->open_simplebloc('Voir les articles');
        $options = '';
        if (!empty($_REQUEST['headingid'])) $options = "&headingid={$_REQUEST['headingid']}";
        if (!empty($_REQUEST['articleid'])) $options = "&articleid={$_REQUEST['articleid']}";
        ?><iframe id="webedit_frame_editor" style="border:0;width:100%;height:400px;margin:0;padding:0;" src="<?php echo "index.php?moduleid={$_SESSION['ploopi']['moduleid']}{$options}&webedit_mode=render&type="; ?>"></iframe><?php
        echo $skin->close_simplebloc();
    break;
}
?>
