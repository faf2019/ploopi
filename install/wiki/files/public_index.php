<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Partie publique du module permettant de gérer l'affichage des pages, l'historique (+diff) et le renommage
 *
 * @package wiki
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

switch($strWikiMenu)
{
    case 'index_title':
        $strOrderBy = 'id';
        $strTitle = 'Index par titre'; 
    break;
    
    case 'index_date':
        $strOrderBy = 'ts_modified DESC';
        $strTitle = 'Index par date'; 
    break;
    
}

echo $skin->open_simplebloc($strTitle);
?>
<div id="wiki_index">
    <?php
    
    $db->query("
        SELECT      *
        FROM        ploopi_mod_wiki_page
        WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
        ORDER BY    {$strOrderBy}
    ");
        
    if ($strWikiMenu == 'index_title') echo '<ul>';

    $strPrevDate = '';
    
    while ($row = $db->fetchrow())
    {
        $strDate = current(ploopi_timestamp2local($row['ts_modified']));
        if ($strWikiMenu == 'index_date' && $strDate != $strPrevDate)
        {
            if ($strPrevDate != '') echo '</ul>'; 
            
            echo "<h1>{$strDate}</h1>\n<ul>";
            
            $strPrevDate = $strDate;
        }
        ?>
        <li><a href="<? echo ploopi_urlencode_trusted("admin.php?wiki_page_id=".urlencode($row['id'])); ?>"><? echo ploopi_htmlentities($row['id']); ?></a></li>
        <?php
    }

    if ($strWikiMenu == 'index_title' || $strPrevDate != '') echo '</ul>';
    ?>
</div>
<?php echo $skin->close_simplebloc(); ?>
