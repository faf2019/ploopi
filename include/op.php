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

include_once './include/functions/rights.php';

if (isset($_REQUEST['ploopi_op'])) $ploopi_op = $_REQUEST['ploopi_op'];

if (isset($ploopi_op))
{
    include_once './include/op_annotations.php';
    include_once './include/op_documents.php';
    include_once './include/op_shares.php';
    include_once './include/op_subscriptions.php';
    include_once './include/op_workflow.php';
    
    switch($ploopi_op)
    {
        case 'ploopi_switchdisplay':
            if (!empty($_GET['id'])) $_SESSION['ploopi']['switchdisplay'][$_GET['id']] = $_GET['display'];
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            ploopi_die();
        break;

        case 'ploopi_checkpasswordvalidity':
            if (!$_SESSION['ploopi']['connected'] || !isset($_GET['password'])) ploopi_die();
            if (_PLOOPI_USE_COMPLEXE_PASSWORD) echo ploopi_checkpasswordvalidity($_GET['password']);
            else echo true;
            ploopi_die();
        break;

        case 'ploopi_skin_array_refresh':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            $skin->display_array_refresh($_GET['array_id'], $_GET['array_orderby']);
            ploopi_die();
        break;

        case 'colorpicker_open':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            ?>
            <div style="overflow:hidden;padding:2px;background-color:#ffffff;z-index:1;">
                <div style="margin-bottom:2px;overflow:hidden;">
                    <div style="float:left;position:relative;width:35px;height:200px;z-index:3;">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:5;" src="./img/colorpicker/h.png" id="colorpicker_h">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:10;" src="./img/colorpicker/position.png" id="colorpicker_position">
                    </div>
                    <div style="float:left;position:relative;width:200px;height:200px;margin-left:2px;z-index:3;">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:5;" src="./img/colorpicker/sv.png" id="colorpicker_sv">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:10;" src="./img/colorpicker/crosshairs.png" id="colorpicker_crosshairs">
                    </div>
                </div>
                <div style="clear:both;width:237px;height:30px;z-index:5;" id="colorpicker_selectedcolor">
                <input type="button" class="button" style="margin:6px;float:right;" value="fermer" onclick="javascript:ploopi_getelem('<? echo $_GET['inputfield_id']; ?>').value = ploopi_getelem('colorpicker_inputcolor').value; ploopi_hidepopup();var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_GET['inputfield_id']; ?>').dispatchEvent(e);">
                <input type="text" class="text" id="colorpicker_inputcolor" style="margin:6px;width:60px;float:left;" value="<? echo $_GET['colorpicker_value']; ?>">
                </div>
            </div>
            <?
            ploopi_die();
        break;

        case 'calendar_open':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $month = date('n');
            $year = date('Y');

            if (isset($_GET['selected_date']))
            {
                $sel_day = $sel_month = $sel_year = 0;

                switch(_PLOOPI_DATEFORMAT)
                {
                    case _PLOOPI_DATEFORMAT_US:
                        if (ereg(_PLOOPI_DATEFORMAT_EREG_US, $_GET['selected_date'], $regs))
                        {
                            $sel_day = $regs[3];
                            $sel_month = $regs[2];
                            $sel_year = $regs[1];

                            $month = $sel_month;
                            $year = $sel_year;
                        }
                    break;

                    case _PLOOPI_DATEFORMAT_FR:
                        if (ereg(_PLOOPI_DATEFORMAT_EREG_FR, $_GET['selected_date'], $regs))
                        {
                            $sel_day = $regs[1];
                            $sel_month = $regs[2];
                            $sel_year = $regs[3];

                            $month = $sel_month;
                            $year = $sel_year;
                        }
                    break;
                }

                $_SESSION['calendar'] = array(
                                            'selected_month'    => $sel_month,
                                            'selected_day'      => $sel_day,
                                            'selected_year'     => $sel_year
                                        );
            }
            elseif (isset($_GET['calendar_month']) && isset($_GET['calendar_year']))
            {
                    $month = $_GET['calendar_month'];
                    $year = $_GET['calendar_year'];
            }

            settype($day,'integer');
            settype($month,'integer');
            settype($year,'integer');

            if (isset($_GET['inputfield_id'])) $_SESSION['calendar']['inputfield_id'] = $_GET['inputfield_id'];

            $selectedday = mktime(0,0,0,$_SESSION['calendar']['selected_month'], $_SESSION['calendar']['selected_day'], $_SESSION['calendar']['selected_year']);
            $today = mktime(0,0,0,date('n'),date('j'),date('Y'));

            $firstday = mktime(0,0,0,$month,1,$year);

            $weekday = date('w', $firstday);
            if ($weekday == 0) $weekday = 7;

            $prev_month = ($month-1)%12+(($month-1)%12 == 0)*12;
            $next_month = ($month+1)%12+(($month+1)%12 == 0)*12;

            $prev_year = $year - ($prev_month == 12);
            $next_year = $year + ($next_month == 1);
            ?>
            <div id="calendar">
                <div class="calendar_row">
                    <div class="calendar_arrow">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=calendar_open&calendar_month=<? echo $prev_month; ?>&calendar_year=<? echo $prev_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/prev.png"></a>
                    </div>
                    <div class="calendar_month">
                        <? echo "{$ploopi_agenda_months[$month]} $year"; ?>
                    </div>
                    <div class="calendar_arrow">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=calendar_open&calendar_month=<? echo $next_month; ?>&calendar_year=<? echo $next_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/next.png"></a>
                    </div>
                </div>
                <div class="calendar_row">
                <?
                foreach($ploopi_agenda_days as $d)
                {
                    ?>
                    <div class="calendar_day"><? echo $d[0]; ?></div>
                    <?
                }
                ?>
                </div>
                <?
                if ($weekday > 1)
                {
                    ?>
                    <div class="calendar_row">
                    <?
                    for ($d = 1; $d < $weekday; $d++)
                    {
                        ?>
                        <div class="calendar_day"><div>&nbsp;</div></div>
                        <?
                    }
                }

                for ($d = 1; $d <= date('t', $firstday) ; $d++)
                {
                    if ($weekday == 8) $weekday = 1;

                    if ($weekday == 1)
                    {
                        ?>
                        <div class="calendar_row">
                        <?
                    }
                    $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", $year, $month, $d));
                    $class = '';
                    $currentday = mktime(0,0,0,$month, $d, $year);
                    if ($currentday == $selectedday) $class = 'class="calendar_day_selected"';
                    elseif ($currentday == $today) $class = 'class="calendar_day_today"';
                    ?>
                        <div class="calendar_day"><a <? echo $class; ?> href="javascript:void(0);" onclick="javascript:ploopi_getelem('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').dispatchEvent(e);"><? echo $d; ?></a></div>
                    <?

                    if ($weekday == 7) echo '</div>';
                    $weekday++;
                }

                if ($weekday <= 7)
                {
                    for ($d = $weekday; $d <= 7 ; $d++)
                    {
                        ?>
                        <div class="calendar_day"><div>&nbsp;</div></div>
                        <?
                    }

                    echo '</div>';
                }

                $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", date('Y'), date('n'), date('j')));
                ?>
                <div class="calendar_row" style="height:1.2em;">
                    <a style="display:block;float:left;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_getelem('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').dispatchEvent(e);">Aujourd'hui</a>
                    <a style="display:block;float:right;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('ploopi_popup_calendar');">Fermer</a>
                </div>
            </div>
            <?
            ploopi_die();
        break;

        default: // look for ploopi_op in modules
            if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
            {
                foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
                {
                    if (isset($_SESSION['ploopi']['modules'][$idm]))
                    {
                        if ($_SESSION['ploopi']['modules'][$idm]['active'])
                        {
                            $ploopi_mod_opfile = "./modules/{$_SESSION['ploopi']['modules'][$idm]['moduletype']}/op.php";
                            if (file_exists($ploopi_mod_opfile)) include_once $ploopi_mod_opfile;
                        }
                    }

                }
            }
            include_once "./modules/system/op.php";
        break;
    }

    //ploopi_die('fonction non définie');
}

?>
