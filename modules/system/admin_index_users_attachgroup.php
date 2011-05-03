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
 * Affichage des groupes d'utilisateurs "rattachables" à l'espace de travail courant
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Gestion du filtrage
 */
if (isset($_POST['reset'])) $pattern = '';
else $pattern = (empty($_POST['pattern'])) ? '' : $_POST['pattern'];

$arrWhere = array();
$arrWhere[] = 'ploopi_group.system = 0';

$arrCurrentGroups = array_keys($workspace->getgroups());

if (!ploopi_isadmin()) // filtrage des groupes visibles si l'utilisateur n'est pas admin système
{
    // liste des groupes (id) "rattachables" (sans filtrage)
    $grp_list = array_diff(array_keys($workspaces['list'][$workspaceid]['groups']), $arrCurrentGroups);
    $nbgroup = sizeof($grp_list);
        
    if (!empty($grp_list)) $arrWhere[] = 'ploopi_group.id IN ('.implode(',',$grp_list).')';
    else $arrWhere[] = 'ploopi_group.id = 0';
}
else
{
    // Comptage groupes dispo pour les admins
    $sql = "SELECT count(*) as nb FROM ploopi_group";
    if (!empty($arrCurrentGroups)) 
    {
        $sql .= ' WHERE id NOT IN ('.implode(',', $arrCurrentGroups).')';
        $arrWhere[] = 'id NOT IN ('.implode(',', $arrCurrentGroups).')';
    }
    
    $db->query();
    $row = $db->fetchrow();
    $nbgroup = $row['nb'];
}

if ($pattern != '') $alphaTabItem = 99; // tous
else
{
    $alphaTabItem = (empty($_GET['alphaTabItem'])) ? -1 : $_GET['alphaTabItem'];

    if ($alphaTabItem == -1)
    {
        // aucun caractère de filtrage sélectionné. On recherche si on en met un par défaut (si trop de groupes) ou si on sélectionne "tous"
        if ($nbgroup < 25) $alphaTabItem = 99;
    }
}

?>
<div style="padding:4px;">
    <?php
    $tabs_char = array();

    // Génération des onglets
    for($i=1;$i<27;$i++)
        $tabs_char[$i] =
            array(
                'title' => chr($i+64),
                'url' => "admin.php?alphaTabItem={$i}"
            );

    $tabs_char[98] =
        array(
            'title' => '#',
            'url' => 'admin.php?alphaTabItem=98'
        );

    $tabs_char[99] =
        array(
            'title' => '<em>tous</em>',
            'url' => 'admin.php?alphaTabItem=99'
        );

    echo $skin->create_tabs($tabs_char, $alphaTabItem);
    ?>
</div>

<form action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
    <span><?php echo _SYSTEM_LABEL_GROUP; ?> :</span>
    <input class="text" ID="system_user" name="pattern" type="text" size="15" maxlength="255" value="<?php echo htmlentities($pattern); ?>">
    <input type="submit" value="<?php echo _PLOOPI_FILTER; ?>" class="button">
    <input type="submit" name="reset" value="<?php echo _PLOOPI_RESET; ?>" class="button">
</p>
</form>

<?php

if ($alphaTabItem == 99) // tous ou recherche
{
    if ($pattern != '')
    {
        $pattern = $db->addslashes($pattern);
        $arrWhere[] .=  "ploopi_group.label LIKE '%{$pattern}%'";
    }
}
else
{
    // 98 : # => non alpha
    if ($alphaTabItem == 98) $arrWhere[] = "ASCII(LCASE(LEFT(ploopi_group.label,1))) NOT BETWEEN 97 AND 122";
    // alpha
    else $arrWhere[] = "ASCII(LCASE(LEFT(ploopi_group.label,1))) = ".($alphaTabItem+96);
}

$strWhere = (empty($arrWhere)) ? '' : ' WHERE '.implode(' AND ', $arrWhere);

$sql =  "
        SELECT      ploopi_group.id,
                    ploopi_group.label,
                    ploopi_group.parents

        FROM        ploopi_group

        {$strWhere}
        ";

$columns = array();
$values = array();

$columns['left']['label']       = array('label' => _SYSTEM_LABEL_GROUP, 'width' => '200', 'options' => array('sort' => true));
$columns['auto']['parents']     = array('label' => _SYSTEM_LABEL_PARENTS, 'options' => array('sort' => true));
$columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '24', 'style' => 'text-align:center;');

$c = 0;

$result = $db->query($sql);

while ($fields = $db->fetchrow($result))
{
    $array_parents = system_getparents($fields['parents'], 'group');
    array_shift($array_parents);

    $str_parents = '';
    foreach($array_parents as $parent) $str_parents .= ($str_parents == '') ? $parent['label']: " > {$parent['label']}";

    $values[$c]['values']['label']      = array('label' => htmlentities($fields['label']));
    $values[$c]['values']['parents']    = array('label' => htmlentities($str_parents));
    $values[$c]['values']['actions']    = array('label' => '<a href="'.ploopi_urlencode("admin.php?op=attach_group&orgid={$fields['id']}&alphaTabItem={$alphaTabItem}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_attach.png" title="'._SYSTEM_LABEL_ATTACH.'"></a>');

    $c++;
}

$skin->display_array($columns, $values, 'array_grouplist', array('sortable' => true, 'orderby_default' => 'label'));
?>
