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
 * Affichage du log 'historique des actions'
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_SYSTEM_LABEL_ACTIONHISTORY);

$intLimit = 5000;

//Paramètre de filtre
$arrSearchPattern = array();
$arrSearchPattern['date'] = (isset($_POST['filter_date'])) ? $_POST['filter_date'] : '';
$arrSearchPattern['date2'] = (isset($_POST['filter_date2'])) ? $_POST['filter_date2'] : '';
$arrSearchPattern['user'] = (isset($_POST['filter_user'])) ? $_POST['filter_user'] : '';
$arrSearchPattern['workspace'] = (isset($_POST['filter_workspace'])) ? $_POST['filter_workspace'] : '';
$arrSearchPattern['module'] = (isset($_POST['filter_module'])) ? $_POST['filter_module'] : '';
$arrSearchPattern['action'] = (isset($_POST['filter_action'])) ? $_POST['filter_action'] : '';
$arrSearchPattern['record'] = (isset($_POST['filter_record'])) ? $_POST['filter_record'] : '';
$arrSearchPattern['ip'] = (isset($_POST['filter_ip'])) ? $_POST['filter_ip'] : '';

$arrWhere = array();

if (!empty($arrSearchPattern['date']))    $arrWhere[] = "ploopi_user_action_log.timestp >= '".user_action_log::getdb()->addslashes(ploopi_local2timestamp($arrSearchPattern['date']))."'";
if (!empty($arrSearchPattern['date2']))   $arrWhere[] = "ploopi_user_action_log.timestp <= '".user_action_log::getdb()->addslashes(ploopi_timestamp_add(ploopi_local2timestamp($arrSearchPattern['date2']),0,0,0,0,1))."'";
if (!empty($arrSearchPattern['user']))    $arrWhere[] = "ploopi_user_action_log.user LIKE '%".user_action_log::getdb()->addslashes($arrSearchPattern['user'])."%'";
if (!empty($arrSearchPattern['workspace'])) $arrWhere[] = "ploopi_user_action_log.workspace LIKE '%".user_action_log::getdb()->addslashes($arrSearchPattern['workspace'])."%'";
if (!empty($arrSearchPattern['module']))  $arrWhere[] = "ploopi_user_action_log.module LIKE '%".user_action_log::getdb()->addslashes($arrSearchPattern['module'])."%'";
if (!empty($arrSearchPattern['action']))  $arrWhere[] = "ploopi_user_action_log.action LIKE '%".user_action_log::getdb()->addslashes($arrSearchPattern['action'])."%'";
if (!empty($arrSearchPattern['record']))  $arrWhere[] = "ploopi_user_action_log.id_record LIKE '%".user_action_log::getdb()->addslashes($arrSearchPattern['record'])."%'";
if (!empty($arrSearchPattern['ip']))      $arrWhere[] = "ploopi_user_action_log.ip LIKE '".user_action_log::getdb()->addslashes($arrSearchPattern['ip'])."%'";

$strWhere = (empty($arrWhere)) ? '' : ' WHERE '.implode(' AND ', $arrWhere);

if (!empty($_POST['historyoption']))
{
    switch($_POST['historyoption'])
    {
        case 'delete':
            $sql =  "
                    DELETE          ploopi_user_action_log
                    FROM            ploopi_user_action_log
                    {$strWhere}
                    ";
            user_action_log::getdb()->query($sql);

            ploopi_redirect("admin.php?op=actionhistory");
        break;

        case 'exportcsv':
            ploopi_ob_clean();

            $sql =  "
                    SELECT          ploopi_user_action_log.*
                    FROM            ploopi_user_action_log
                    {$strWhere}
                    ORDER BY        ploopi_user_action_log.timestp DESC
                    ";

            user_action_log::getdb()->query($sql);

            header("Cache-control: private");
            header("Content-type: text/x-csv");
            header("Content-Disposition: attachment; filename=actionlog.csv");
            header("Pragma: public");

            echo "\"date\";\"time\";\"ip\";\"id_user\";\"user\";\"id_workspace\";\"workspace\";\"id_module\";\"module\";\"id_action\";\"action\";\"record\"\r\n";
            while($row = user_action_log::getdb()->fetchrow())
            {
                foreach($row as &$value) $value = str_replace('"', '\"', $value);
                $date_local = ploopi_timestamp2local($row['timestp']);
                
                echo "\"{$date_local['date']}\";\"{$date_local['time']}\";\"{$row['ip']}\";\"{$row['id_user']}\";\"{$row['user']}\";\"{$row['id_workspace']}\";\"{$row['workspace']}\";{$row['id_module']};\"{$row['module']}\";\"{$row['id_action']}\";\"{$row['action']}\";\"".addslashes($row['id_record'])."\"\r\n";
            }

            ploopi_die();
        break;
    }
}
?>

<form action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post" id="form_loghistory">
<input type="hidden" name="op" value="actionhistory">
<input type="hidden" name="historyoption" id="historyoption" value="">
<div style="margin: 0;border-bottom:2px solid #c0c0c0;padding:4px; with: 99%;">
    <div style="margin:0; padding:0; float:left;width:49%;" class="ploopi_form">
        <p>
            <label>Entre le (date):</label>
            <input type="text" class="text" name="filter_date" id="filter_date" style="width:100px;" value="<?php echo ploopi_htmlentities($arrSearchPattern['date']); ?>"><?php ploopi_open_calendar('filter_date'); ?>
        </p>
        <p>
            <label>et le (date):</label>
            <input type="text" class="text" name="filter_date2" id="filter_date2" style="width:100px;" value="<?php echo ploopi_htmlentities($arrSearchPattern['date2']); ?>"><?php ploopi_open_calendar('filter_date2'); ?>
        </p>
        <p>
            <label>Utilisateur:</label>
            <input type="text" class="text" name="filter_user" value="<?php echo ploopi_htmlentities($arrSearchPattern['user']); ?>">
        </p>
        <p>
            <label>Espace de travail:</label>
            <input type="text" class="text" name="filter_workspace" value="<?php echo ploopi_htmlentities($arrSearchPattern['workspace']); ?>">
        </p>
    </div>

    <div style="margin:0; padding:0; float:left;width:50%;" class="ploopi_form">
        <p>
            <label>Module:</label>
            <input type="text" class="text" name="filter_module" value="<?php echo ploopi_htmlentities($arrSearchPattern['module']); ?>">
        </p>
        <p>
            <label>Action:</label>
            <input type="text" class="text" name="filter_action" value="<?php echo ploopi_htmlentities($arrSearchPattern['action']); ?>">
        </p>
        <p>
            <label>Enregistrement:</label>
            <input type="text" class="text" name="filter_record" value="<?php echo ploopi_htmlentities($arrSearchPattern['record']); ?>">
        </p>
        <p>
            <label>IP:</label>
            <input type="text" class="text" name="filter_ip" value="<?php echo ploopi_htmlentities($arrSearchPattern['ip']); ?>">
        </p>
    </div>
    <div style="clear:both;text-align:right;padding:4px;">
        <input type="button" class="button" value="Effacer les logs (selon le filtre)" onclick="javascript:if (confirm('<?php echo _SYSTEM_MSG_CONFIRMLOGDELETE; ?>')) {$('historyoption').value='delete';$('form_loghistory').submit();}">
        <input type="button" class="button" value="Export CSV" onclick="javascript:$('historyoption').value='exportcsv';$('form_loghistory').submit();">
        <input type="submit" class="button" value="Filtrer">
    </div>
</div>
</form>
<?php
$sql =  "
        SELECT      count(*) as c
        FROM        ploopi_user_action_log
        {$strWhere}
        ";

user_action_log::getdb()->query($sql);
$row = user_action_log::getdb()->fetchrow();
$intCount = $row['c'];
?>
<div style="padding:4px;border-bottom:1px solid #c0c0c0;background:#e0e0e0;"><b><?php echo $intCount; ?> élément(s) trouvés</b> <?php if ($intCount > $intLimit) { ?>- Affichage des <?php echo $intLimit; ?> premiers enregistrements - Utilisez les filtres ci-dessus pour des résultats plus précis <?php } ?></div>
<?php
$sql =  "
        SELECT      ploopi_user_action_log.*
        FROM        ploopi_user_action_log
        {$strWhere}
        ORDER BY    ploopi_user_action_log.timestp DESC
        LIMIT       0, {$intLimit}
        ";

user_action_log::getdb()->query($sql);

$arrColumns = array();
$arrValues = array();

$arrColumns['left']['timestp'] = array(
    'label' => 'Date/Heure',
    'width' => '130',
    'options' => array('sort' => true),
);

$arrColumns['left']['ip'] = array(
    'label' => 'IP client',
    'width' => '110',
    'options' => array('sort' => true),
);

$arrColumns['left']['user'] = array(
    'label' => 'Utilisateur',
    'width' => '120',
    'options' => array('sort' => true)
);

$arrColumns['left']['workspace'] = array(
    'label' => 'Espace de travail',
    'width' => '140',
    'options' => array('sort' => true)
);

$arrColumns['left']['module'] = array(
    'label' => 'Module',
    'width' => '100',
    'options' => array('sort' => true)
);

$arrColumns['left']['action'] = array(
    'label' => 'Action',
    'width' => '200',
    'options' => array('sort' => true)
);

$arrColumns['auto']['record'] = array(
    'label' => 'Enregistrement',
    'options' => array('sort' => true)
);

$c = 0;

while($row = user_action_log::getdb()->fetchrow())
{
    $date_local = ploopi_timestamp2local($row['timestp']);

    $arrValues[$c]['values']['ip']     = array('label' => ploopi_htmlentities($row['ip']));

    $arrValues[$c]['values']['timestp']    = array('label' => ploopi_htmlentities("{$date_local['date']} {$date_local['time']}"), 'sort_label' => $row['timestp']);

    $arrValues[$c]['values']['user']     = array('label' => ploopi_htmlentities($row['user']));

    $arrValues[$c]['values']['workspace']     = array('label' => ploopi_htmlentities($row['workspace']));
    
    $arrValues[$c]['values']['module']    = array('label' => ploopi_htmlentities($row['module']));

    $arrValues[$c]['values']['action']    = array('label' => ploopi_htmlentities($row['action']));

    $arrValues[$c]['values']['record']     = array('label' => ploopi_htmlentities($row['id_record']));
    $c++;
}

?>
<div style="margin:0; padding:0; border-bottom:1px solid #c0c0c0; height:0px; font-size: 0em;"></div>
<?php
$skin->display_array(
    $arrColumns,
    $arrValues,
    'array_actionlog',
    array(
        'sortable' => true,
        'orderby_default' => 'timestp',
        'sort_default' => 'DESC',
        'limit' => 100,
        'page' => 1
    )
);

echo $skin->close_simplebloc();
?>
