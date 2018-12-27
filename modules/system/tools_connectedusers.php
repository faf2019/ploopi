<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Ouverture du bloc
 */
echo ploopi\skin::get()->open_simplebloc(_SYSTEM_LABEL_CONNECTEDUSERS);

if (isset($_GET['monitoring'])) header("Refresh: ".intval($_GET['monitoring'])."; URL=".ploopi\crypt::urlencode("admin.php?op=connectedusers&monitoring={$_GET['monitoring']}"));

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

ploopi\db::get()->query($sql);

while($row = ploopi\db::get()->fetchrow())
{
    $date_local = ploopi\date::timestamp2local($row['timestp']);

    $values[$c]['values']['ip']     = array('label' => ploopi\str::htmlentities($row['ip']));
    $values[$c]['values']['domaine']    = array('label' => ploopi\str::htmlentities($row['domain']));
    if (is_null($row['login']))
    {
        $values[$c]['values']['login']  = array('label' => 'anonyme', 'style' => 'font-style:italic;');
        $values[$c]['values']['name']   = array('label' => '&nbsp;');
    }
    else
    {
        $values[$c]['values']['login']  = array('label' => ploopi\str::htmlentities($row['login']));
        $values[$c]['values']['name']   = array('label' => ploopi\str::htmlentities("{$row['firstname']} {$row['lastname']}"));
    }
    $values[$c]['values']['workspace']  = array('label' => ploopi\str::htmlentities($row['workspacelabel']));
    $values[$c]['values']['module']     = array('label' => ploopi\str::htmlentities($row['modulelabel']));
    $values[$c]['values']['timestp']    = array('label' => ploopi\str::htmlentities("{$date_local['date']} {$date_local['time']}"), 'sort_label' => $row['timestp']);
    $c++;

}

ploopi\skin::get()->display_array($columns, $values, 'array_connectedusers', array('sortable' => true, 'orderby_default' => 'timestp', 'sort_default' => 'DESC'));
?>

<div style="padding:4px;text-align:right;">
<?php
if (isset($_GET['monitoring']))
{
    ?>
    <input type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=connectedusers"); ?>'" value="Arrêter le  monitoring">
    <?php
}
else
{
    ?>
    <input type="text" class="text" value="<?php echo (isset($_GET['monitoring'])) ? ploopi\str::htmlentities($_GET['monitoring']) : 2; ?>" size="2" id="system_monitoring_delay">
    <input type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=connectedusers"); ?>&monitoring='+jQuery('#system_monitoring_delay')[0].value;" value="Monitoring">
    <?php
}
?>
</div>

<?php echo ploopi\skin::get()->close_simplebloc(); ?>
