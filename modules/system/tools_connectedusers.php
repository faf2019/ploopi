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
 * Affichage temps réel du log 'utilisateurs connectés'
 * 
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */
echo $skin->open_simplebloc(_SYSTEM_LABEL_CONNECTEDUSERS);

if (isset($_GET['monitoring'])) header("Refresh: {$_GET['monitoring']}; URL=".ploopi_urlencode("admin.php?op=connectedusers&monitoring={$_GET['monitoring']}"));

$columns = array();
$values = array();

$columns['left']['timestp']     = array('label' => 'Date/Heure', 'width' => '130', 'options' => array('sort' => true));
$columns['left']['ip']          = array('label' => 'IP client', 'width' => '110', 'options' => array('sort' => true));
$columns['auto']['domaine']     = array('label' => 'Domaine', 'options' => array('sort' => true));
$columns['right']['module']     = array('label' => 'Module', 'width' => '100', 'options' => array('sort' => true));
$columns['right']['workspace']  = array('label' => 'Espace', 'width' => '150', 'options' => array('sort' => true));
$columns['right']['name']       = array('label' => 'Nom', 'width' => '150', 'options' => array('sort' => true));
$columns['right']['login']      = array('label' => 'Login', 'width' => '100', 'options' => array('sort' => true));

$c = 0;

$sql =  "
        SELECT      ploopi_connecteduser.*,
                    ploopi_user.login, ploopi_user.firstname, ploopi_user.lastname,
                    ploopi_workspace.label as workspacelabel,
                    ploopi_module.label as modulelabel
        FROM        ploopi_connecteduser
        LEFT JOIN   ploopi_user ON ploopi_connecteduser.user_id = ploopi_user.id
        LEFT JOIN   ploopi_workspace ON ploopi_connecteduser.workspace_id = ploopi_workspace.id
        LEFT JOIN   ploopi_module ON ploopi_connecteduser.module_id = ploopi_module.id
        ";

$db->query($sql);

while($row = $db->fetchrow())
{
    $date_local = ploopi_timestamp2local($row['timestp']);

    $values[$c]['values']['ip']     = array('label' => htmlentities($row['ip']));
    $values[$c]['values']['domaine']    = array('label' => htmlentities($row['domain']));
    if (is_null($row['login']))
    {
        $values[$c]['values']['login']  = array('label' => 'anonyme', 'style' => 'font-style:italic;');
        $values[$c]['values']['name']   = array('label' => '&nbsp;');
    }
    else
    {
        $values[$c]['values']['login']  = array('label' => htmlentities($row['login']));
        $values[$c]['values']['name']   = array('label' => htmlentities("{$row['firstname']} {$row['lastname']}"));
    }
    $values[$c]['values']['workspace']  = array('label' => htmlentities($row['workspacelabel']));
    $values[$c]['values']['module']     = array('label' => htmlentities($row['modulelabel']));
    $values[$c]['values']['timestp']    = array('label' => htmlentities("{$date_local['date']} {$date_local['time']}"), 'sort_label' => $row['timestp']);
    $c++;

}

$skin->display_array($columns, $values, 'array_connectedusers', array('sortable' => true, 'orderby_default' => 'timestp', 'sort_default' => 'DESC'));
?>

<div style="padding:4px;text-align:right;">
<?
if (isset($_GET['monitoring']))
{
    ?>
    <input type="button" class="button" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?op=connectedusers"); ?>'" value="Arrêter le  monitoring">
    <?
}
else
{
    ?>
    <input type="text" class="text" value="<? echo (isset($_GET['monitoring'])) ? $_GET['monitoring'] : 2; ?>" size="2" id="system_monitoring_delay">
    <input type="button" class="button" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?op=connectedusers"); ?>&monitoring='+$('system_monitoring_delay').value;" value="Monitoring">
    <?
}
?>
</div>

<? echo $skin->close_simplebloc(); ?>
