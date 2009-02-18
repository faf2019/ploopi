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
 * Interface de modification d'un utilisateur 
 *
 * @package system
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture d'une instance de l'objet user
 */

$user = new user();

// Suppression de la variable de stockage de la photo temporaire
if (isset($_SESSION['system']['user_photopath'])) unset($_SESSION['system']['user_photopath']);

if (empty($_GET['user_id']) || !is_numeric($_GET['user_id']) || !$user->open($_GET['user_id']))
{
    $user->init_description();
    $user->fields['servertimezone'] = 1;
    
    // Récuération des données de l'utilisateur (problème lors de la création) => pour remplir le formulaire
    if (!empty($_SESSION['system']['save_user']))
    {
        foreach($user->fields as $field => $value)
        {
            if (isset($_SESSION['system']['save_user']["user_{$field}"])) $user->fields[$field] = $_SESSION['system']['save_user']["user_{$field}"];
        }
    }    
}

// detect server timezone
$date = date_create();
$server_timezone = date_timezone_get($date);
$server_timezoneid = timezone_name_get($server_timezone);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $server_timezoneid;

$user_date_expire = (!empty($user->fields['date_expire'])) ? ploopi_timestamp2local($user->fields['date_expire']) : array('date' => '');

if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
{
    $workspace_user = new workspace_user();
    if (!empty($workspaceid) && !empty($_GET['user_id']))
    {
        $workspace_user->open($workspaceid, $_GET['user_id']);
    }
    else
    {
        $workspace_user->init_description();
        $workspace_user->fields['id_user'] = -1;
    }
}



$arrFormurl[] = 'op=save_user'; 
if (isset($_REQUEST['confirm'])) $arrFormurl[] = 'confirm'; 
if (!$user->new)  $arrFormurl[] = "user_id={$user->fields['id']}"; 
?>
<form name="form_modify_user" action="<?php echo ploopi_urlencode('admin.php?'.implode('&',$arrFormurl)); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return system_user_validate(this, <?php echo ($user->new) ? 'true' : 'false'; ?>)">
<?php

if (isset($_REQUEST['error']))
{
    $error = '';

    switch($_REQUEST['error'])
    {
        case 'password':
            $error = ploopi_nl2br(_SYSTEM_MSG_PASSWORDERROR);
        break;

        case 'passrejected':
            $error = ploopi_nl2br(_SYSTEM_MSG_LOGINPASSWORDERROR);
        break;

        case 'login':
            $error = ploopi_nl2br(_SYSTEM_MSG_LOGINERROR);
        break;
    }
    ?>
    <div class="error" style="padding:2px;text-align:center;"><?php echo $error; ?></div>
    <?php
}
if (isset($_REQUEST['confirm']))
{
    ?>
    <div class="error" style="padding:2px;text-align:center;"><?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION1; ?></div>
    <div style="margin:10px;">
    <?php
    $db->query("
        SELECT  
            id, 
            login,
            lastname, 
            firstname, 
            email, 
            service, 
            office, 
            function 
            
        FROM 
            ploopi_user
             
        WHERE 
            lastname = '{$_SESSION['system']['save_user']['user_lastname']}' 
        AND 
            firstname = '{$_SESSION['system']['save_user']['user_firstname']}'
    ");
        
    $arrColumns = array();
    $arrValues = array();
    
    $arrColumns['left']['name'] = 
        array(
            'label' => _SYSTEM_LABEL_LASTNAME.', '._SYSTEM_LABEL_FIRSTNAME, 
            'width' => 150, 
            'options' => array('sort' => true)
        );
        
    $arrColumns['left']['login'] = 
        array(
            'label' => _SYSTEM_LABEL_LOGIN, 
            'width' => 100, 
            'options' => array('sort' => true)
        );
            
    $arrColumns['left']['service'] = 
        array(
            'label' => _SYSTEM_LABEL_SERVICE, 
            'width' => 100, 
            'options' => array('sort' => true)
        );
        
    $arrColumns['left']['office'] = 
        array(
            'label' => _SYSTEM_LABEL_OFFICE, 
            'width' => 100, 
            'options' => array('sort' => true)
        );
        
        
    $arrColumns['left']['function'] = 
        array(
            'label' => _SYSTEM_LABEL_FUNCTION, 
            'width' => 100, 
            'options' => array('sort' => true)
        );
        
    $arrColumns['auto']['origin'] = 
        array(
            'label' => _SYSTEM_LABEL_ORIGIN, 
            'options' => array('sort' => true)
        );
        
        
    while ($row = $db->fetchrow())
    {
        $objUser = new user();
        $objUser->fields['id'] = $row['id'];
        
        $arrGroups = $objUser->getgroups();
        $currentGroup = current($arrGroups);
        
        $arrValues[] = 
            array(
                'values' => 
                    array(
                        'name' =>
                            array(
                                'label' => htmlentities("{$row['lastname']}, {$row['firstname']}")
                            ),
                        'login' =>
                            array(
                                'label' => htmlentities($row['login'])
                            ),
                        'origin' =>
                            array(
                                'label' => htmlentities($currentGroup['label'])
                            ),
                        'service' =>
                            array(
                                'label' => htmlentities($row['service'])
                            ),
                        'office' =>
                            array(
                                'label' => htmlentities($row['office'])
                            ),
                        'function' =>
                            array(
                                'label' => htmlentities($row['function'])
                            ),
                    ),
                'description' => _SYSTEM_LABEL_ATTACH,
                'link' => ploopi_urlencode("admin.php?op=attach_user&user_id={$row['id']}")
            );
        
            
            /*
        $values[$c]['values']['origin'] = 
            array(
                'label' => '<a href="'.ploopi_urlencode("admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&groupid={$currentgroup['id']}&alphaTabItem=".(ord(strtolower($fields['lastname']))-96)).'">'.htmlentities($currentgroup['label']).'</a>'
            );*/
    }

    echo $skin->open_simplebloc('Choisir un utilisateur existant');
    $skin->display_array($arrColumns, $arrValues, 'array_createuser_confirm', array('sortable' => true, 'orderby_default' => 'name'));
    echo $skin->close_simplebloc();
    ?>
    </div>
    <div class="error" style="padding:2px;text-align:center;"><?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION2; ?></div>
    <?php
}
?>
<div>
    <div style="float:left;width:50%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Informations personnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_LASTNAME; ?><sup style="font-size:.7em">&nbsp;1</sup>:</label>
                        <input type="text" class="text" name="user_lastname"  value="<?php echo htmlentities($user->fields['lastname']); ?>" tabindex="1" />
                    </p>
                    <p>
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_FIRSTNAME; ?><sup style="font-size:.7em">&nbsp;1</sup>:</label>
                        <input type="text" class="text" name="user_firstname"  value="<?php echo htmlentities($user->fields['firstname']); ?>" tabindex="2" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CIVILITY; ?>:</label>
                        <select class="select" name="user_civility" style="width:100px;" tabindex="3">
                            <option value=""></option>                
                            <?php
                            foreach ($ploopi_civility as $value)
                            {
                                ?>
                                <option value="<?php echo htmlentities($value); ?>" <?php if ($user->fields['civility'] == $value) echo 'selected'; ?>><?php echo htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations professionnelles</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_SERVICE; ?>:</label>
                        <input type="text" class="text" name="user_service"  value="<?php echo htmlentities($user->fields['service']); ?>" tabindex="4" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FUNCTION; ?>:</label>
                        <input type="text" class="text" name="user_function"  value="<?php echo htmlentities($user->fields['function']); ?>" tabindex="5" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_RANK; ?>:</label>
                        <input type="text" class="text" name="user_rank"  value="<?php echo htmlentities($user->fields['rank']); ?>" tabindex="6" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_NUMBER; ?>:</label>
                        <input type="text" class="text" name="user_number"  value="<?php echo htmlentities($user->fields['number']); ?>" tabindex="7" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHONE; ?>:</label>
                        <input type="text" class="text" name="user_phone"  value="<?php echo htmlentities($user->fields['phone']); ?>" tabindex="8" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_MOBILE; ?>:</label>
                        <input type="text" class="text" name="user_mobile"  value="<?php echo htmlentities($user->fields['mobile']); ?>" tabindex="9" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FAX; ?>:</label>
                        <input type="text" class="text" name="user_fax"  value="<?php echo htmlentities($user->fields['fax']); ?>" tabindex="10" />
                    </p>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Lieu de travail</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_BUILDING; ?>:</label>
                        <input type="text" class="text" name="user_building"  value="<?php echo htmlentities($user->fields['building']); ?>" tabindex="11" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_FLOOR; ?>:</label>
                        <input type="text" class="text" name="user_floor"  value="<?php echo htmlentities($user->fields['floor']); ?>" tabindex="12" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_OFFICE; ?>:</label>
                        <input type="text" class="text" name="user_office"  value="<?php echo htmlentities($user->fields['office']); ?>" tabindex="13" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_ADDRESS; ?>:</label>
                        <textarea class="text" name="user_address" tabindex="14"><?php echo htmlentities($user->fields['address']); ?></textarea>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_POSTALCODE; ?>:</label>
                        <input type="text" class="text" name="user_postalcode"  value="<?php echo htmlentities($user->fields['postalcode']); ?>" tabindex="15" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_CITY; ?>:</label>
                        <input type="text" class="text" name="user_city"  value="<?php echo htmlentities($user->fields['city']); ?>" tabindex="16" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COUNTRY; ?>:</label>
                        <input type="text" class="text" name="user_country"  value="<?php echo htmlentities($user->fields['country']); ?>"  tabindex="17" />
                    </p>
                </div>
            </fieldset>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div style="padding:2px;">
            <fieldset class="fieldset">
                <legend>Compte d'utilisateur</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php if ($user->new) echo '<em>*&nbsp;</em>'; echo _SYSTEM_LABEL_LOGIN; ?>:</label>
                        <?php
                        if ($user->new)
                        {
                            ?>
                            <input type="text" class="text" name="user_login"  value="<?php echo htmlentities($user->fields['login']); ?>" tabindex="21" />
                            <?php
                        }
                        else
                        {
                            ?>
                            <strong><?php echo htmlentities($user->fields['login']); ?></strong>
                            <?php
                        }
                        ?>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD; ?>:</label>
                        <input type="password" class="text" name="usernewpass" id="usernewpass" value="" tabindex="22" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                        <input type="password" class="text" name="usernewpass_confirm" id="usernewpass_confirm" value="" tabindex="23" />
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EXPIRATION_DATE; ?>:</label>
                        <input type="text" style="width:100px;" class="text" name="user_date_expire" id="user_date_expire" value="<?php echo $user_date_expire['date']; ?>" tabindex="24" />
                        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('user_date_expire', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_EMAIL; ?>:</label>
                        <input type="text" class="text" name="user_email"  value="<?php echo htmlentities($user->fields['email']); ?>" tabindex="25" />
                    </p>
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_ticketsbyemail');">
                        <label><?php echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                        <input style="width:16px;" type="checkbox" id="user_ticketsbyemail" name="user_ticketsbyemail" value="1" <?php if ($user->fields['ticketsbyemail']) echo 'checked'; ?> tabindex="26" />
                    </p>
                        
                    <?php
                    // get server offset
                    $offset = timezone_offset_get($server_timezone, $date);
                    
                    $s = ($offset>0) ? '+' : '-';
                    
                    $hh = floor(abs($offset) / 3600);
                    $mm = floor((abs($offset) % 3600) / 60);            
                    ?>
        
                    <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_servertimezone');">
                        <label><?php echo _SYSTEM_LABEL_SERVERTIMEZONE; ?>:</label>
                        <span>
                            <input style="width:16px;" type="checkbox" id="user_servertimezone" name="user_servertimezone" value="1" <?php if ($user->fields['servertimezone']) echo 'checked'; ?> tabindex="27" onchange="$('user_timezone_choice').style.display = (this.checked) ? 'none' : 'block';"/>
                            <?php echo "{$server_timezoneid} (". (($offset == 0) ? 'UTC' : sprintf("UTC %s%02dh%02d",$s, $hh, $mm)). ")"; ?>
                        </span>
                    </p>
        
                    <p id="user_timezone_choice" style="display:<?php echo ($user->fields['servertimezone']) ? 'none' : 'block'; ?>">
                        <label><?php echo _SYSTEM_LABEL_TIMEZONE; ?>:</label>
                        <?php
                        $timezone_abbreviations = timezone_abbreviations_list();
                        //ploopi_print_r($timezone_abbreviations);
                        
                        foreach($timezone_abbreviations as $value)
                        {
                            foreach($value as $key => $value)
                            {
                                if (!empty($value['timezone_id']) && strpos($value['timezone_id'], '/') !== false) 
                                {
                                    $objDateTimeZone = timezone_open($value['timezone_id']);
                                    
                                    if ($objDateTimeZone !== false)
                                    {
                                        $offset = timezone_offset_get($objDateTimeZone, $date);
                                        
                                        //don't use $value['offset'] !;
                                        
                                        $s = ($offset>0) ? '+' : '-';
                                        
                                        $hh = floor(abs($offset) / 3600);
                                        $mm = floor((abs($offset) % 3600) / 60);
                                        
                                        $arrZones[$value['timezone_id']] = 
                                            array(   
                                                'offset' => $offset,
                                                'label' => str_replace(array('/', '_'), array(' / ', ' '), $value['timezone_id']),
                                                'offset_display' => ($offset == 0) ? 'UTC' : sprintf("GMT %s%02dh%02d",$s, $hh, $mm)
                                            );
                                    }
                                }
                            }
                        }
        
                        ksort($arrZones);
                        
                        ?>
                        <select class="select" name="user_timezone"  tabindex="28">
                        <?php
                        foreach ($arrZones as $key => $value)
                        {
                            ?>
                            <option value="<?php echo htmlentities($key); ?>" <?php if ($user->fields['timezone'] == $key) echo 'selected'; ?>><?php echo htmlentities($value['label']); ?> (<?php echo $value['offset_display']; ?>)</option>
                            <?php
                        }
                        ?>
                        </select>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COLOR; ?>:</label>
                        <input type="text" style="width:100px;" class="text" name="user_color" id="user_color" value="<?php echo htmlentities($user->fields['color']); ?>" tabindex="29" />
                        <a href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('user_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                    </p>
                    <p>
                        <label><?php echo _SYSTEM_LABEL_PHOTO; ?>:</label>
                        <span>
                        <a href="javascript:void(0);" onclick="javascript:system_choose_photo(event, '<?php echo $user->fields['id']; ?>');">Choisir une photo</a>
                        <br /><span id="system_user_photo">
                        <?php
                        if (file_exists($user->getphotopath()))
                        {
                            ?><img src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$user->fields['id']}"); ?>" /><?php
                        }
                        ?>
                        </span>
                        </span>
                    </p>
                    <?php
                    if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
                    {
                        ?>
                        <p style="margin-top:2px;padding:4px 0;border:2px solid #a0a0a0;background-color:#c0c0c0;">
                            <label style="font-weight:bold;"><?php echo _SYSTEM_LABEL_LEVEL; ?>:</label>
                            <select class="select" name="userworkspace_adminlevel" tabindex="30">
                            <?php
                            foreach ($ploopi_system_levels as $id => $label)
                            {
                                if ($id <= $_SESSION['ploopi']['adminlevel'])
                                {
                                    $sel = ($workspace_user->fields['adminlevel'] == $id) ? 'selected' : '';
                                    echo "<option $sel value=\"$id\">$label</option>";
                                }
                                // user / group admin
                            }
                            ?>
                            </select>
                        </p>
                    <?php
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>Informations complémentaires</legend>
                <div class="ploopi_form">
                    <p>
                        <label><?php echo _SYSTEM_LABEL_COMMENTS; ?>:</label>
                        <textarea class="text" name="user_comments" tabindex="30"><?php echo htmlentities($user->fields['comments']); ?></textarea>
                    </p>
                </div>
            </fieldset>            
        </div>
    </div>
</div>
<div style="clear:both;padding:4px;text-align:right;">
    <em><?php if ($user->new) echo '<sup>(1)</sup> Champs utilisés pour tester la présence de l\'utilisateur / '; ?>* Champs obligatoires</em>&nbsp;
    <?php
    if (isset($_REQUEST['confirm']))
    {
        ?>
        <input type="submit" class="flatbutton" value="<?php echo _SYSTEM_MSG_CREATEUSER_CONFIRMATION3; ?>">
        <?php
    }
    else
    {
        ?>
        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
        <?php
    }
    ?>
</div>
</form>

<script type="text/javascript">
    ploopi_window_onload_stock(
        function() {
            $('usernewpass').value = '';
            $('usernewpass_confirm').value = '';
        }
    );
        
</script>
