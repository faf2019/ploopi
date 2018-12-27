<?php
/*
    Copyright (c) 2010 HeXad
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
 * @package doc
 * @subpackage template
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi\module::init('doc');

$strListDocWorkspace = ploopi\system::viewworkspaces($template_moduleid,'doc');

$strDocSelect =  "
                SELECT      f.id, f.name, f.id_module
                 
                FROM        ploopi_mod_doc_folder f
                 
                WHERE       f.allow_feeds = '1'
                AND         f.foldertype = 'public'
                AND         f.id_workspace IN ({$strListDocWorkspace})
                
                ORDER BY    f.name asc 
                ";

$sqlDocResult = ploopi\db::get()->query($strDocSelect);

if(ploopi\db::get()->numrows($sqlDocResult)) $template_body->assign_block_vars('switch_docfeed', array());

while($docFields = ploopi\db::get()->fetchrow($sqlDocResult))
{
    $template_body->assign_block_vars('switch_docfeed.rss' , array(
        'URL' => ploopi\str::urlrewrite('./backend.php?format=rss&ploopi_moduleid='.$docFields['id_module'].'&id_folder='.$docFields['id'], doc_getrewriterules(), $docFields['name'].'.xml',null,true),
        'TITLE' => $docFields['name']
        )
    );
    
    $template_body->assign_block_vars('switch_docfeed.atom' , array(
        'URL' => ploopi\str::urlrewrite('./backend.php?format=atom&ploopi_moduleid='.$docFields['id_module'].'&id_folder='.$docFields['id'], doc_getrewriterules(), $docFields['name'].'.xml',null,true),
        'TITLE' => $docFields['name']
        )
    );
}
?>

