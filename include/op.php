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
    switch($ploopi_op)
    {
        case 'colorpicker_open':
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
            $month = date('n');
            $year = date('Y');
            
            if (!empty($_GET['inputfield_id'])) $_SESSION['calendar']['inputfield_id'] = $_GET['inputfield_id'];
            
            if (empty($_SESSION['calendar']['inputfield_id'])) ploopi_die();
            
            if (!empty($_GET['selected_date']))
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

                $_SESSION['calendar']['selected_month'] = $sel_month;
                $_SESSION['calendar']['selected_day'] = $sel_day;
                $_SESSION['calendar']['selected_year'] = $sel_year;
            }
            elseif (isset($_GET['calendar_month']) && isset($_GET['calendar_year']))
            {
                $month = $_GET['calendar_month'];
                $year = $_GET['calendar_year'];
            }
            
            if (empty($_SESSION['calendar']['selected_day']))
            {
                $_SESSION['calendar']['selected_month'] = date('n');
                $_SESSION['calendar']['selected_day'] = date('d');
                $_SESSION['calendar']['selected_year'] = date('Y');
            }

            settype($day,'integer');
            settype($month,'integer');
            settype($year,'integer');

            $selectedday = mktime(0,0,0,$_SESSION['calendar']['selected_month'], $_SESSION['calendar']['selected_day'], $_SESSION['calendar']['selected_year']);
            $today = mktime(0,0,0,date('n'),date('j'),date('Y'));

            $firstday = mktime(0,0,0,$month,1,$year);

            $weekday = date('w', $firstday);
            if ($weekday == 0) $weekday = 7;

            $prev_month = ($month-1)%12+(($month-1)%12 == 0)*12;
            $next_month = ($month+1)%12+(($month+1)%12 == 0)*12;

            $prev_year = $year - ($prev_month == 12);
            $next_year = $year + ($next_month == 1);
            
            
            if ($_SESSION['ploopi']['mode'] == 'admin' && !empty($_SESSION['ploopi']['template_path'])) $strIconsPath = $_SESSION['ploopi']['template_path'];
            else $strIconsPath = '.';
             
            ?>
            <div id="calendar">
                <div class="calendar_row">
                    <div class="calendar_arrow" style="float:right;">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $scriptenv; ?>','ploopi_op=calendar_open&calendar_month=<? echo $next_month; ?>&calendar_year=<? echo $next_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $strIconsPath; ?>/img/calendar/next.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $scriptenv; ?>','ploopi_op=calendar_open&calendar_month=<? echo $month; ?>&calendar_year=<? echo ($year+1); ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $strIconsPath; ?>/img/calendar/nextx2.png"></a>
                    </div>
                    <div class="calendar_arrow" style="float:left;">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $scriptenv; ?>','ploopi_op=calendar_open&calendar_month=<? echo $month; ?>&calendar_year=<? echo ($year-1); ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $strIconsPath; ?>/img/calendar/prevx2.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $scriptenv; ?>','ploopi_op=calendar_open&calendar_month=<? echo $prev_month; ?>&calendar_year=<? echo $prev_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $strIconsPath; ?>/img/calendar/prev.png"></a>
                    </div>
                    <div class="calendar_month">
                        <? echo "{$ploopi_agenda_months[$month]}<br />{$year}"; ?>
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
                        <div class="calendar_day"><a <? echo $class; ?> href="javascript:void(0);" onclick="javascript:$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_calendar_dispatchevent('<? echo $_SESSION['calendar']['inputfield_id']; ?>');"><? echo $d; ?></a></div>
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
                    <a style="display:block;float:left;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_calendar_dispatchevent('<? echo $_SESSION['calendar']['inputfield_id']; ?>');">Aujourd'hui</a>
                    <a style="display:block;float:right;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('ploopi_popup_calendar');">Fermer</a>
                </div>
            </div>
            <?
            ploopi_die();
        break;
    }


    if ($_SESSION['ploopi']['connected'])
    {
        include_once './include/op_annotations.php';
        include_once './include/op_documents.php';
        include_once './include/op_shares.php';
        include_once './include/op_subscriptions.php';
        include_once './include/op_workflow.php';
        include_once './include/op_tickets.php';
        include_once './modules/system/op.php';
        
        
        switch($ploopi_op)
        {
            case 'ploopi_switchdisplay':
                if (!empty($_GET['id'])) $_SESSION['ploopi']['switchdisplay'][$_GET['id']] = $_GET['display'];
                ploopi_die();
            break;
    
            case 'ploopi_checkpasswordvalidity':
                if (!isset($_GET['password'])) ploopi_die();
                if (_PLOOPI_USE_COMPLEXE_PASSWORD) echo ploopi_checkpasswordvalidity($_GET['password']);
                else echo true;
                ploopi_die();
            break;
    
            case 'ploopi_skin_array_refresh':
                $skin->display_array_refresh($_GET['array_id'], $_GET['array_orderby']);
                ploopi_die();
            break;
    
            case 'ploopi_getobjects':
                ob_start();
                ?>
                <script language="javascript">
        
                var oEditor = window.parent.InnerDialogLoaded() ;
                var FCKLang = oEditor.FCKLang ;
                var FCKPlaceholders = oEditor.FCKPlaceholders ;
        
                window.onload = function ()
                {
                    /* First of all, translate the dialog box texts */
                    oEditor.FCKLanguageManager.TranslatePage( document ) ;
        
                    LoadSelected() ;
        
                    /* Show the "Ok" button. */
                    window.parent.SetOkButton( true ) ;
                };
        
                var eSelected = oEditor.FCKSelection.GetSelectedElement() ;
        
                function LoadSelected()
                {
                    if ( !eSelected )
                        return ;
        
                    var info = eSelected._fckplaceholder.split("/");
                    var sValue = info[0];
        
                    if ( eSelected.tagName == 'SPAN' && eSelected._fckplaceholder )
                    {
                        var obj = document.getElementById('ploopi_webedit_objects');
                        for (i=0;i<obj.length;i++) if (obj[i].value == sValue) obj.selectedIndex = i;
                    }
                    else
                        eSelected == null ;
                }
        
                function Ok()
                {
                    var obj = document.getElementById('ploopi_webedit_objects');
        
                    var sValue = obj[obj.selectedIndex].value+'/'+obj[obj.selectedIndex].text ;
        
                    if ( eSelected && eSelected._fckplaceholder == sValue )
                        return true ;
        
                    if ( sValue.length == 0 )
                    {
                        alert( FCKLang.PlaceholderErrNoName ) ;
                        return false ;
                    }
        
                    if ( FCKPlaceholders.Exist( sValue ) )
                    {
                        alert( FCKLang.PlaceholderErrNameInUse ) ;
                        return false ;
                    }
        
                    FCKPlaceholders.Add( sValue ) ;
                    return true ;
                }
        
                </script>
        
                <div style="padding:4px 0;">Choix d'un objet PLOOPI à insérer dans la page :</div>
                <?
                $select_object =    "
                                    SELECT  ploopi_mb_wce_object.*,
                                            ploopi_module.label as module_label,
                                            ploopi_module.id as module_id
        
                                    FROM    ploopi_mb_wce_object,
                                            ploopi_module,
                                            ploopi_module_workspace
        
                                    WHERE   ploopi_mb_wce_object.id_module_type = ploopi_module.id_module_type
                                    AND     ploopi_module_workspace.id_module = ploopi_module.id
                                    AND     ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['workspaceid']}
                                    ";
        
                $result_object = $db->query($select_object);
                while ($fields_object = $db->fetchrow($result_object))
                {
                    if ($fields_object['select_label'] != '')
                    {
                        $select = "select {$fields_object['select_id']}, {$fields_object['select_label']} from {$fields_object['select_table']} where id_module = {$fields_object['module_id']}";
                        $db->query($select);
        
                        while ($fields = $db->fetchrow())
                        {
                            $fields_object['object_label'] = $fields[$fields_object['select_label']];
                            $array_modules["{$fields_object['id']},{$fields_object['module_id']},{$fields[$fields_object['select_id']]}"] = $fields_object;
                        }
                    }
                    else $array_modules["{$fields_object['id']},{$fields_object['module_id']}"] = $fields_object;
                }
                ?>
                <select id="ploopi_webedit_objects" style="width:100%;">
                    <option value="0">(aucun)</option>
                    <?
                    foreach($array_modules as $key => $value)
                    {
                        //if ($fields_column['id_object'] == $key) $sel = 'selected';
                        //else $sel = '';
                        $sel = '';
                        ?>
                        <option <? echo $sel; ?> value="<? echo $key; ?>"><? echo "{$value['module_label']} » {$value['label']}"; if (!empty($value['object_label'])) echo " » {$value['object_label']}"; ?></option>
                        <?
                    }
                    ?>
                </select>
                <?
                $main_content = ob_get_contents();
                @ob_end_clean();
        
                $template_body->assign_vars(array(
                    'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                    'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
                    'PAGE_CONTENT'          => $main_content
                    )
                );
        
                $template_body->pparse('body');
                ploopi_die();
            break;
        }
        
    }
    
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

}
?>
