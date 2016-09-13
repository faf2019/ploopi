<?php
/*
    Copyright (c) 2011 Ovensia
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
 * Partie publique du module permettant de r�indexer les pages
 *
 * @package wiki
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

echo $skin->open_simplebloc('R�indexation des pages');
?>
<div id="wiki_index">
<?php
    ovensia\ploopi\module::init('cus');
    set_time_limit(0);

    include_once './modules/wiki/classes/class_wiki_page.php';

    $intI = 0;

    $objCol = new ovensia\ploopi\data_object_collection('wiki_page');
    $objCol->add_where('id_module = %d', $_SESSION['ploopi']['moduleid']);
    foreach($objCol->get_objects() as $objPage)
    {
        $objPage->index();
        $db->flush_log();
        $intI++;
    }


    ?>
    R�sultat de l'indexation : <?php echo $intI; ?> page(s) trait�e(s)
</div>
<?php echo $skin->close_simplebloc(); ?>
