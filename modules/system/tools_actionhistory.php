<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Ouverture du bloc
 */

echo system_open_bloc(_SYSTEM_LABEL_ACTIONHISTORY);

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

if (!empty($arrSearchPattern['date']))    $arrWhere[] = "ploopi_user_action_log.timestp >= '".ploopi\user_action_log::getdb()->addslashes(ploopi\date::local2timestamp($arrSearchPattern['date']))."'";
if (!empty($arrSearchPattern['date2']))   $arrWhere[] = "ploopi_user_action_log.timestp <= '".ploopi\user_action_log::getdb()->addslashes(ploopi\date::timestamp_add(ploopi\date::local2timestamp($arrSearchPattern['date2']),0,0,0,0,1))."'";
if (!empty($arrSearchPattern['user']))    $arrWhere[] = "ploopi_user_action_log.user LIKE '%".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['user'])."%'";
if (!empty($arrSearchPattern['workspace'])) $arrWhere[] = "ploopi_user_action_log.workspace LIKE '%".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['workspace'])."%'";
if (!empty($arrSearchPattern['module']))  $arrWhere[] = "ploopi_user_action_log.module LIKE '%".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['module'])."%'";
if (!empty($arrSearchPattern['action']))  $arrWhere[] = "ploopi_user_action_log.action LIKE '%".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['action'])."%'";
if (!empty($arrSearchPattern['record']))  $arrWhere[] = "ploopi_user_action_log.id_record LIKE '%".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['record'])."%'";
if (!empty($arrSearchPattern['ip']))      $arrWhere[] = "ploopi_user_action_log.ip LIKE '".ploopi\user_action_log::getdb()->addslashes($arrSearchPattern['ip'])."%'";

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
            ploopi\user_action_log::getdb()->query($sql);

            ploopi\output::redirect("admin.php?op=actionhistory");
        break;

        case 'exportcsv':
            ploopi\buffer::clean();

            $sql =  "
                    SELECT          ploopi_user_action_log.*
                    FROM            ploopi_user_action_log
                    {$strWhere}
                    ORDER BY        ploopi_user_action_log.timestp DESC
                    ";

            ploopi\user_action_log::getdb()->query($sql);

            header("Cache-control: private");
            header("Content-type: text/x-csv");
            header("Content-Disposition: attachment; filename=actionlog.csv");
            header("Pragma: public");

            echo "\"date\";\"time\";\"ip\";\"id_user\";\"user\";\"id_workspace\";\"workspace\";\"id_module\";\"module\";\"id_action\";\"action\";\"record\"\r\n";
            while($row = ploopi\user_action_log::getdb()->fetchrow())
            {
                foreach($row as &$value) $value = str_replace('"', '\"', $value);
                $date_local = ploopi\date::timestamp2local($row['timestp']);

                echo "\"{$date_local['date']}\";\"{$date_local['time']}\";\"{$row['ip']}\";\"{$row['id_user']}\";\"{$row['user']}\";\"{$row['id_workspace']}\";\"{$row['workspace']}\";{$row['id_module']};\"{$row['module']}\";\"{$row['id_action']}\";\"{$row['action']}\";\"".addslashes($row['id_record'])."\"\r\n";
            }

            ploopi\system::kill();
        break;
    }
}

// Gabarits de génération 4 : filtres refondus (mêmes champs, mêmes options)
if (ploopi\skin::get()->version() >= 4)
{
    $funcLocalTs = function($strLocal) { return $strLocal == '' ? '' : ploopi\date::local2timestamp($strLocal); };

    echo '<form class="p4-form p4-form-dense p4-ui p4-filters-form" action="'.ploopi\crypt::urlencode('admin.php').'" method="post"><input type="hidden" name="op" value="actionhistory" /><div class="p4-fgrid">';
    echo ploopi\system\ui::field(_SYSTEM_TOOLS_FROM, ploopi\system\ui::date('filter_date', $funcLocalTs($arrSearchPattern['date'])), array('for' => 'filter_date', 'width' => 'c3'));
    echo ploopi\system\ui::field(_SYSTEM_TOOLS_TO, ploopi\system\ui::date('filter_date2', $funcLocalTs($arrSearchPattern['date2'])), array('for' => 'filter_date2', 'width' => 'c3'));
    foreach(array('filter_user' => array(_SYSTEM_USERS_USER, 'user'), 'filter_workspace' => array(_SYSTEM_WS_TYPE_WORKSPACE, 'workspace'), 'filter_module' => array(_SYSTEM_ROLEF_MODULE, 'module'), 'filter_action' => array(_SYSTEM_TOOLS_ACTION, 'action'), 'filter_record' => array(_SYSTEM_TOOLS_RECORD, 'record'), 'filter_ip' => array(_SYSTEM_TOOLS_IP, 'ip')) as $strName => $arrField)
    {
        echo ploopi\system\ui::field($arrField[0], ploopi\system\ui::input($strName, $arrSearchPattern[$arrField[1]], array('type' => 'search')), array('for' => $strName, 'width' => 'c3'));
    }
    echo '</div><div class="p4-row p4-wrap p4-filters-actions">';
    echo ploopi\system\ui::button(array('label' => _SYSTEM_TOOLS_LOG_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'type' => 'submit', 'attrs' => array('name' => 'historyoption', 'value' => 'delete'), 'confirm' => _SYSTEM_TOOLS_LOG_DELETE_CONFIRM));
    echo '<span class="p4-grow"></span>';
    echo ploopi\system\ui::button(array('label' => _SYSTEM_TOOLS_EXPORTCSV, 'icon' => 'download', 'class' => 'sm', 'type' => 'submit', 'attrs' => array('name' => 'historyoption', 'value' => 'exportcsv')));
    echo ploopi\system\ui::button(array('label' => _SYSTEM_USERS_FILTER, 'icon' => 'filter', 'class' => 'primary sm', 'type' => 'submit'));
    echo '</div></form>';
}
else
{
?>

<form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" id="form_loghistory">
<input type="hidden" name="op" value="actionhistory">
<input type="hidden" name="historyoption" id="historyoption" value="">
<div style="margin: 0;border-bottom:2px solid #c0c0c0;padding:4px; with: 99%;">
    <div style="margin:0; padding:0; float:left;width:49%;" class="ploopi_form">
        <p>
            <label><?php echo _SYSTEM_TOOLS_FILTER_DATE1; ?> :</label>
            <input type="text" class="text" name="filter_date" id="filter_date" style="width:100px;" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['date']); ?>"><?php ploopi\date::open_calendar('filter_date'); ?>
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_FILTER_DATE2; ?> :</label>
            <input type="text" class="text" name="filter_date2" id="filter_date2" style="width:100px;" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['date2']); ?>"><?php ploopi\date::open_calendar('filter_date2'); ?>
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_USER; ?> :</label>
            <input type="text" class="text" name="filter_user" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['user']); ?>">
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_WORKSPACE; ?> :</label>
            <input type="text" class="text" name="filter_workspace" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['workspace']); ?>">
        </p>
    </div>

    <div style="margin:0; padding:0; float:left;width:50%;" class="ploopi_form">
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_MODULE; ?> :</label>
            <input type="text" class="text" name="filter_module" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['module']); ?>">
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_ACTION; ?> :</label>
            <input type="text" class="text" name="filter_action" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['action']); ?>">
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_RECORD; ?> :</label>
            <input type="text" class="text" name="filter_record" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['record']); ?>">
        </p>
        <p>
            <label><?php echo _SYSTEM_TOOLS_COL_IP; ?> :</label>
            <input type="text" class="text" name="filter_ip" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['ip']); ?>">
        </p>
    </div>
    <div style="clear:both;text-align:right;padding:4px;">
        <input type="button" class="button ploopi_button_danger" value="Effacer les logs (selon le filtre)" onclick="javascript:if (confirm('<?php echo _SYSTEM_MSG_CONFIRMLOGDELETE; ?>')) {jQuery('#historyoption')[0].value='delete';jQuery('#form_loghistory').submit();}">
        <input type="button" class="button" value="Export CSV" onclick="javascript:jQuery('#historyoption')[0].value='exportcsv';jQuery('#form_loghistory').submit();">
        <input type="submit" class="button" value="Filtrer">
    </div>
</div>
</form>
<?php
}
?>
<?php
$sql =  "
        SELECT      count(*) as c
        FROM        ploopi_user_action_log
        {$strWhere}
        ";

ploopi\user_action_log::getdb()->query($sql);
$row = ploopi\user_action_log::getdb()->fetchrow();
$intCount = $row['c'];
if (ploopi\skin::get()->version() >= 4)
{
    echo '<p class="p4-ui p4-dir-toolbar"><strong>'.ploopi\str::htmlentities(str_replace('<NB>', $intCount, _SYSTEM_TOOLS_LOG_COUNT)).'</strong></p>';
    if ($intCount > $intLimit) echo ploopi\system\ui::callout('info', ploopi\str::htmlentities(str_replace('<NB>', $intLimit, _SYSTEM_TOOLS_LOG_LIMIT)));
}
else
{
?>
<div class="system_bandeau"><b><?php echo $intCount; ?> élément(s) trouvés</b> <?php if ($intCount > $intLimit) { ?>- Affichage des <?php echo $intLimit; ?> premiers enregistrements - Utilisez les filtres ci-dessus pour des résultats plus précis <?php } ?></div>
<?php
}
$sql =  "
        SELECT      ploopi_user_action_log.*
        FROM        ploopi_user_action_log
        {$strWhere}
        ORDER BY    ploopi_user_action_log.timestp DESC
        LIMIT       0, {$intLimit}
        ";

ploopi\user_action_log::getdb()->query($sql);

$arrColumns = array();
$arrValues = array();

$arrColumns['left']['timestp'] = array(
    'label' => _SYSTEM_TOOLS_COL_DATETIME,
    'width' => '130',
    'options' => array('sort' => true),
);

$arrColumns['left']['ip'] = array(
    'label' => _SYSTEM_TOOLS_COL_IP,
    'width' => '110',
    'options' => array('sort' => true),
);

$arrColumns['left']['user'] = array(
    'label' => _SYSTEM_TOOLS_COL_USER,
    'width' => '120',
    'options' => array('sort' => true)
);

$arrColumns['left']['workspace'] = array(
    'label' => _SYSTEM_TOOLS_COL_WORKSPACE,
    'width' => '140',
    'options' => array('sort' => true)
);

$arrColumns['left']['module'] = array(
    'label' => _SYSTEM_TOOLS_COL_MODULE,
    'width' => '100',
    'options' => array('sort' => true)
);

$arrColumns['left']['action'] = array(
    'label' => _SYSTEM_TOOLS_COL_ACTION,
    'width' => '200',
    'options' => array('sort' => true)
);

$arrColumns['auto']['record'] = array(
    'label' => _SYSTEM_TOOLS_COL_RECORD,
    'options' => array('sort' => true)
);

$c = 0;

while($row = ploopi\user_action_log::getdb()->fetchrow())
{
    $date_local = ploopi\date::timestamp2local($row['timestp']);

    $arrValues[$c]['values']['ip']     = array('label' => ploopi\str::htmlentities($row['ip']));

    $arrValues[$c]['values']['timestp']    = array('label' => ploopi\str::htmlentities("{$date_local['date']} {$date_local['time']}"), 'sort_label' => $row['timestp']);

    $arrValues[$c]['values']['user']     = array('label' => ploopi\str::htmlentities($row['user']));

    $arrValues[$c]['values']['workspace']     = array('label' => ploopi\str::htmlentities($row['workspace']));

    $arrValues[$c]['values']['module']    = array('label' => ploopi\str::htmlentities($row['module']));

    $arrValues[$c]['values']['action']    = array('label' => ploopi\str::htmlentities($row['action']));

    $arrValues[$c]['values']['record']     = array('label' => ploopi\str::htmlentities($row['id_record']));
    $c++;
}

if (ploopi\skin::get()->version() < 4) echo '<div style="margin:0; padding:0; border-bottom:1px solid #c0c0c0; height:0px; font-size: 0em;"></div>';

system_display_array(
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

echo system_close_bloc();
?>
