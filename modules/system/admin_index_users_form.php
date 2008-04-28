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

$user = new user();

if (!empty($_GET['user_id'])) $user->open($_GET['user_id']);
else $user->init_description();

// detect server timezone
$date = date_create();
$server_timezone = date_timezone_get($date);
$server_timezoneid = timezone_name_get($server_timezone);
if (empty($user->fields['timezone'])) $user->fields['timezone'] = $server_timezoneid;

$user_date_expire = (!empty($user->fields['date_expire']) && $user->fields['date_expire'] != '00000000000000') ? ploopi_timestamp2local($user->fields['date_expire']) : array('date' => '');

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

if (isset($_SESSION['module_system']) && !empty($_SESSION['module_system']))
{
    $user->fields['lastname'] = $_SESSION['module_system']['user_lastname'];
    $user->fields['firstname'] = $_SESSION['module_system']['user_firstname'];
    $user->fields['login'] = $_SESSION['module_system']['user_login'];
    $user->fields['date_expire'] = $_SESSION['module_system']['user_date_expire'];
    $user->fields['email'] = $_SESSION['module_system']['user_email'];
    $user->fields['phone'] = $_SESSION['module_system']['user_phone'];
    $user->fields['fax'] = $_SESSION['module_system']['user_fax'];
    $user->fields['address'] = $_SESSION['module_system']['user_address'];
    $user->fields['comments'] = $_SESSION['module_system']['user_comments'];
    $user->fields['id_type'] = $_SESSION['module_system']['user_id_type'];
    $workspace_user->fields['adminlevel'] = $_SESSION['module_system']['userworkspace_adminlevel'];

    //ploopi_print_r($user);

    $_SESSION['module_system'] = '';
    unset($_SESSION['module_system']);
}

?>

<form name="form_modify_user" action="<? echo $scriptenv ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return system_user_validate(this, <? echo ($user->new) ? 'true' : 'false'; ?>)">
<input type="hidden" name="op" value="save_user">
<input type="hidden" name="user_id" value="<? if (!$user->new) echo $user->fields['id']; ?>">
<div>

<?

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
    <div class="error" style="padding:2px;text-align:center;"><? echo $error; ?></div>
    <?
}
?>
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:2px;">
            <p>
                <label><? echo _SYSTEM_LABEL_LASTNAME; ?>:</label>
                <input type="text" class="text" name="user_lastname"  value="<? echo htmlentities($user->fields['lastname']); ?>" tabindex="1" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FIRSTNAME; ?>:</label>
                <input type="text" class="text" name="user_firstname"  value="<? echo htmlentities($user->fields['firstname']); ?>" tabindex="2" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_SERVICE; ?>:</label>
                <input type="text" class="text" name="user_service"  value="<? echo htmlentities($user->fields['service']); ?>" tabindex="3" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FUNCTION; ?>:</label>
                <input type="text" class="text" name="user_function"  value="<? echo htmlentities($user->fields['function']); ?>" tabindex="4" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PHONE; ?>:</label>
                <input type="text" class="text" name="user_phone"  value="<? echo htmlentities($user->fields['phone']); ?>" tabindex="5" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_MOBILE; ?>:</label>
                <input type="text" class="text" name="user_mobile"  value="<? echo htmlentities($user->fields['mobile']); ?>" tabindex="6" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_FAX; ?>:</label>
                <input type="text" class="text" name="user_fax"  value="<? echo htmlentities($user->fields['fax']); ?>" tabindex="7" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_ADDRESS; ?>:</label>
                <textarea class="text" name="user_address" tabindex="8"><? echo htmlentities($user->fields['address']); ?></textarea>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_POSTALCODE; ?>:</label>
                <input type="text" class="text" name="user_postalcode"  value="<? echo htmlentities($user->fields['postalcode']); ?>" tabindex="9" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_CITY; ?>:</label>
                <input type="text" class="text" name="user_city"  value="<? echo htmlentities($user->fields['city']); ?>" tabindex="10" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COUNTRY; ?>:</label>
                <input type="text" class="text" name="user_country"  value="<? echo htmlentities($user->fields['country']); ?>"  tabindex="11" />
            </p>
        </div>
    </div>
    <div style="float:left;width:50%;" class="ploopi_form">
        <div style="padding:2px;">
            <p>
                <label><? echo _SYSTEM_LABEL_LOGIN; ?>:</label>
                <?
                if ($user->new)
                {
                    ?>
                    <input type="text" class="text" name="user_login"  value="<? echo htmlentities($user->fields['login']); ?>" tabindex="21" />
                    <?
                }
                else
                {
                    ?>
                    <span><? echo htmlentities($user->fields['login']); ?></span>
                    <?
                }
                ?>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PASSWORD; ?>:</label>
                <input type="password" class="text" name="usernewpass"  value="" tabindex="22" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_PASSWORD_CONFIRM; ?>:</label>
                <input type="password" class="text" name="usernewpass_confirm"  value="" tabindex="23" />
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_EXPIRATION_DATE; ?>:</label>
                <input type="text" style="width:100px;" class="text" name="user_date_expire" id="user_date_expire" value="<? echo $user_date_expire['date']; ?>" tabindex="27" />
                <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('user_date_expire', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_EMAIL; ?>:</label>
                <input type="text" class="text" name="user_email"  value="<? echo htmlentities($user->fields['email']); ?>" tabindex="24" />
            </p>
            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_ticketsbyemail');">
                <label><? echo _SYSTEM_LABEL_TICKETSBYEMAIL; ?>:</label>
                <input style="width:16px;" type="checkbox" id="user_ticketsbyemail" name="user_ticketsbyemail" value="1" <? if ($user->fields['ticketsbyemail']) echo 'checked'; ?> tabindex="25" />
            </p>
            
            <?
            // get server offset
            $offset = timezone_offset_get($server_timezone, $date);
            
            $s = ($offset>0) ? '+' : '-';
            
            $hh = floor(abs($offset) / 3600);
            $mm = floor((abs($offset) % 3600) / 60);            
            ?>

            <p class="checkbox" onclick="javascript:ploopi_checkbox_click(event,'user_servertimezone');">
                <label><? echo _SYSTEM_LABEL_SERVERTIMEZONE; ?>:</label>
                <span>
                    <input style="width:16px;" type="checkbox" id="user_servertimezone" name="user_servertimezone" value="1" <? if ($user->fields['servertimezone']) echo 'checked'; ?> tabindex="25" onchange="$('user_timezone_choice').style.display = (this.checked) ? 'none' : 'block';"/>
                    <? echo "{$server_timezoneid} (". (($offset == 0) ? 'UTC' : sprintf("GMT %s%02dh%02d",$s, $hh, $mm)). ")"; ?>
                </span>
            </p>

            <p id="user_timezone_choice" style="display:<? echo ($user->fields['servertimezone']) ? 'none' : 'block'; ?>">
                <label><? echo _SYSTEM_LABEL_TIMEZONE; ?>:</label>
                <?
                $timezone_abbreviations = timezone_abbreviations_list();
                //ploopi_print_r($timezone_abbreviations);
                
                foreach($timezone_abbreviations as $value)
                {
                    foreach($value as $key => $value)
                    {
                        if (!empty($value['timezone_id']) && strpos($value['timezone_id'], '/') !== false) 
                        {
                            $offset = timezone_offset_get(timezone_open($value['timezone_id']), $date);
                            
                            //don't use $value['offset'] !;
                            
                            $s = ($offset>0) ? '+' : '-';
                            
                            $hh = floor(abs($offset) / 3600);
                            $mm = floor((abs($offset) % 3600) / 60);
                            
                            $arrZones[$value['timezone_id']] = array(   'offset' => $offset,
                                                                        'label' => str_replace(array('/', '_'), array(' / ', ' '), $value['timezone_id']),
                                                                        'offset_display' => ($offset == 0) ? 'UTC' : sprintf("GMT %s%02dh%02d",$s, $hh, $mm)
                                                                    );
                        }
                    }
                }

                ksort($arrZones);
                
                ?>
                <select class="select" name="user_timezone"  tabindex="26">
                <?
                foreach ($arrZones as $key => $value)
                {
                    ?>
                    <option value="<? echo htmlentities($key); ?>" <? if ($user->fields['timezone'] == $key) echo 'selected'; ?>><? echo htmlentities($value['label']); ?> (<? echo $value['offset_display']; ?>)</option>
                    <?
                }
                ?>
                </select>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COMMENTS; ?>:</label>
                <textarea class="text" name="user_comments" tabindex="28"><? echo htmlentities($user->fields['comments']); ?></textarea>
            </p>
            <p>
                <label><? echo _SYSTEM_LABEL_COLOR; ?>:</label>
                <input type="text" style="width:100px;" class="text" name="user_color" id="user_color" value="<? echo htmlentities($user->fields['color']); ?>" tabindex="29" />
                <a href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('user_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
            </p>
            <?
            if ($_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
            {
                ?>
                <p style="margin-top:2px;padding:4px 0;border:2px solid #a0a0a0;background-color:#c0c0c0;">
                    <label style="font-weight:bold;"><? echo _SYSTEM_LABEL_LEVEL; ?>:</label>
                    <select class="select" name="userworkspace_adminlevel" tabindex="30">
                    <?
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
            <?
            }
            ?>
        </div>
    </div>
</div>
<div style="clear:both;float:right;padding:4px;">
    <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
</div>
</form>
