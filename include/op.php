<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2009-2010 HeXad
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
 * Opérations génériques.
 * Calendrier, Colorpicker, Captcha...
 *
 * @package ploopi
 * @subpackage global
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * inclusions fonctions système (ploopi_die...)
 */
include_once './include/functions/system.php';

if (isset($_REQUEST['ploopi_op'])) $ploopi_op = $_REQUEST['ploopi_op'];

if (isset($ploopi_op))
{
    switch($ploopi_op)
    {
        case 'ploopi_robots':
            include_once './include/classes/cache.php';

            // Mise en cache
            $objCache = new ploopi_cache(_PLOOPI_BASEPATH.'/robots.txt', 300);

            if (!$objCache->start())
            {
                if (_PLOOPI_FRONTOFFICE) {
                    $arrDisallow = array(
                        '/bin/',
                        '/cgi/',
                        '/config/',
                        '/data/',
                        '/doc/',
                        '/FCKeditor/',
                        '/img/',
                        '/include/',
                        '/install/',
                        '/js/',
                        '/lang/',
                        '/lib/',
                        '/modules/',
                        '/templates/',
                        '/tools/',
                    );

                    foreach($arrDisallow as &$strDisallow) $strDisallow = "Disallow "._PLOOPI_SELFPATH.$strDisallow;

                    echo "User-agent: *\nSitemap: "._PLOOPI_BASEPATH."/sitemap.xml\n".implode("\n", $arrDisallow);
                }
                else {
                    echo "User-agent: *\r\nDisallow: *";
                }

                $objCache->end();
            }

            header('Content-Type: text/plain');
            ploopi_die();
        break;

        case 'ploopi_lostpassword':
        case 'ploopi_lostpassword_confirm':

            if (!isset($_REQUEST['ploopi_lostpassword_login']) && !isset($_REQUEST['ploopi_lostpassword_email'])) ploopi_die();

            $arrWhere = array();
            if (!empty($_REQUEST['ploopi_lostpassword_login'])) $arrWhere[] = "login = '".$db->addslashes($_REQUEST['ploopi_lostpassword_login'])."'";
            if (!empty($_REQUEST['ploopi_lostpassword_email'])) $arrWhere[] = "email = '".$db->addslashes($_REQUEST['ploopi_lostpassword_email'])."'";

            $db->query('
                SELECT  id,
                        email,
                        lastname,
                        firstname

                FROM    ploopi_user

                WHERE   '.implode(' AND ', $arrWhere)
            );

            switch ($db->numrows())
            {
                case 0: // erreur : inconnu
                    $intError = _PLOOPI_ERROR_LOSTPASSWORD_UNKNOWN;
                break;

                case 1: // ok
                    $row = $db->fetchrow();
                    if (!empty($row['email']))
                    {

                        include_once './include/classes/user.php';
                        $objUser = new user();
                        $objUser->open($row['id']);

                        // confirmation de modification de mdp
                        if ($ploopi_op == 'ploopi_lostpassword_confirm')
                        {
                            // si code de confirmation fourni
                            if (!empty($_GET['ploopi_lostpassword_confirmcode']))
                            {
                                include_once './include/classes/confirmation_code.php';
                                $confirmation_code = new confirmation_code();

                                // si action en cours avec le bon code de confirmation
                                if ($confirmation_code->open("ploopi_lostpassword{$objUser->fields['id']}") && $confirmation_code->fields['code'] == $_GET['ploopi_lostpassword_confirmcode'])
                                {

                                    // ok on peut générer le nouveau mot de passe et l'enregistrer
                                    $strPass = ploopi_generatepassword();
                                    $objUser->setpassword($strPass);
                                    $objUser->fields['password_force_update'] = 1;
                                    $objUser->save();

                                    // ok on peut envoyer le mail
                                    ploopi_send_mail(
                                        array(
                                            array(
                                                    'name' => $_SERVER['HTTP_HOST'],
                                                    'address' => trim(current(explode(',', _PLOOPI_ADMINMAIL)))
                                            )
                                        ),
                                        array(
                                            array(
                                                'name' => "{$row['lastname']} {$row['firstname']}",
                                                'address' => $row['email']
                                            )
                                        ),
                                        "{$_SERVER['HTTP_HOST']} : modification de votre mot de passe",
                                        "Bonjour,\n\nvous recevez ce message car vous avez effectué une demande de mot de passe sur le site {$_SERVER['HTTP_HOST']}.\n\nVotre nouveau mot de passe est le suivant :\n\n{$strPass}",
                                        null,
                                        null,
                                        null,
                                        null,
                                        false
                                    );

                                    $confirmation_code->delete();
                                    $intMsg = _PLOOPI_MSG_PASSWORDSENT;
                                }
                            }
                        }
                        else
                        {
                            include_once './include/classes/confirmation_code.php';

                            $confirmation_code = new confirmation_code();
                            $strAction = "ploopi_lostpassword{$objUser->fields['id']}";

                            if ($confirmation_code->open($strAction))
                            {
                                $confirmation_code->delete();
                                $confirmation_code = new confirmation_code();
                            }

                            $confirmation_code = new confirmation_code();
                            $confirmation_code->fields['action'] = $strAction;
                            $confirmation_code->save();

                            ploopi_send_mail(
                                array(
                                    array(
                                            'name' => $_SERVER['HTTP_HOST'],
                                            'address' => trim(current(explode(',', _PLOOPI_ADMINMAIL)))
                                    )
                                ),
                                array(
                                    array(
                                        'name' => "{$row['lastname']} {$row['firstname']}",
                                        'address' => $row['email']
                                    )
                                ),
                                "{$_SERVER['HTTP_HOST']} : modification de votre mot de passe",
                                "Bonjour,\n\nvous recevez ce message car vous avez effectué une demande de mot de passe sur le site {$_SERVER['HTTP_HOST']}.\n\nVous devez confirmer cette demande en cliquant sur le lien suivant:\n\n"._PLOOPI_BASEPATH."/".ploopi_urlencode("admin.php?ploopi_op=ploopi_lostpassword_confirm&ploopi_lostpassword_login={$_REQUEST['ploopi_lostpassword_login']}&ploopi_lostpassword_email={$_REQUEST['ploopi_lostpassword_email']}&ploopi_lostpassword_confirmcode={$confirmation_code->fields['code']}"),
                                null,
                                null,
                                null,
                                null,
                                false
                            );

                            $intMsg = _PLOOPI_MSG_MAILSENT;
                        }
                    }
                    else $intError = _PLOOPI_ERROR_LOSTPASSWORD_INVALID;
                break;

                default:  // erreur : plusieurs réponses
                    $intError = _PLOOPI_ERROR_LOSTPASSWORD_MANYRESPONSES;
                break;
            }

            if (isset($intError)) $_SESSION['ploopi']['errorcode'] = $intError;
            elseif (isset($intMsg)) $_SESSION['ploopi']['msgcode'] = $intMsg;

            ploopi_redirect('admin.php');
        break;

        case 'calendar_open':
            ob_start();

            $month = date('n');
            $year = date('Y');

            if (!empty($_REQUEST['inputfield_id'])) $_SESSION['calendar']['inputfield_id'] = $_REQUEST['inputfield_id'];

            if (empty($_SESSION['calendar']['inputfield_id'])) ploopi_die();

            if (!empty($_REQUEST['selected_date']))
            {
                $sel_day = $sel_month = $sel_year = 0;

                switch(_PLOOPI_DATEFORMAT)
                {
                    case _PLOOPI_DATEFORMAT_US:
                        if (preg_match(_PLOOPI_DATEFORMAT_EREG_US, $_REQUEST['selected_date'], $regs))
                        {
                            $sel_day = $regs[3];
                            $sel_month = $regs[2];
                            $sel_year = $regs[1];

                            $month = $sel_month;
                            $year = $sel_year;
                        }
                    break;

                    case _PLOOPI_DATEFORMAT_FR:
                        if (preg_match(_PLOOPI_DATEFORMAT_EREG_FR, $_REQUEST['selected_date'], $regs))
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
            elseif (isset($_REQUEST['calendar_month']) && isset($_REQUEST['calendar_year']))
            {
                $month = $_REQUEST['calendar_month'];
                $year = $_REQUEST['calendar_year'];
            }

            // Vérifications basiques
            if (empty($_SESSION['calendar']['selected_day']) || empty($_SESSION['calendar']['selected_year']) || empty($_SESSION['calendar']['selected_month']) || empty($_SESSION['calendar']['selected_day']) || $_SESSION['calendar']['selected_month'] < 1 || $_SESSION['calendar']['selected_month'] > 12 || $_SESSION['calendar']['selected_day'] < 1 || $_SESSION['calendar']['selected_day'] > 31)
            {
                $_SESSION['calendar']['selected_month'] = date('n');
                $_SESSION['calendar']['selected_day'] = date('d');
                $_SESSION['calendar']['selected_year'] = date('Y');
            }

            if (empty($month) || empty($year) || $month < 1 || $month > 12)
            {
                $month = date('n');
                $year = date('Y');
            }

            settype($day,'int');
            settype($month,'int');
            settype($year,'int');

            $selectedday = mktime(0,0,0,$_SESSION['calendar']['selected_month'], $_SESSION['calendar']['selected_day'], $_SESSION['calendar']['selected_year']);
            $today = mktime(0,0,0,date('n'),date('j'),date('Y'));

            $firstday = mktime(0,0,0,$month,1,$year);

            $weekday = date('N', $firstday);

            $prev_month = ($month-1)%12+(($month-1)%12 == 0)*12;
            $next_month = ($month+1)%12+(($month+1)%12 == 0)*12;

            $prev_year = $year - ($prev_month == 12);
            $next_year = $year + ($next_month == 1);

            if ($_SESSION['ploopi']['mode'] == 'backoffice' && !empty($_SESSION['ploopi']['template_path'])) $strIconsPath = $_SESSION['ploopi']['template_path'];
            else $strIconsPath = '.';

            $strScript = $_SESSION['ploopi']['mode'] == 'backoffice' ? 'admin' : 'index';
            ?>
            <div id="calendar">
                <div class="calendar_row">
                    <div class="calendar_arrow" style="float:right;">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $strScript; ?>-light.php', '<?php echo ploopi_queryencode("ploopi_op=calendar_open&calendar_month={$next_month}&calendar_year={$next_year}"); ?>', 'ploopi_popup_calendar');"><img style="border:0;" src="<?php echo $strIconsPath; ?>/img/calendar/next.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $strScript; ?>-light.php', '<?php echo ploopi_queryencode("ploopi_op=calendar_open&calendar_month={$month}&calendar_year=".($year+1)); ?>', 'ploopi_popup_calendar');"><img style="border:0;" src="<?php echo $strIconsPath; ?>/img/calendar/nextx2.png"></a>
                    </div>
                    <div class="calendar_arrow" style="float:left;">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $strScript; ?>-light.php', '<?php echo ploopi_queryencode("ploopi_op=calendar_open&calendar_month={$month}&calendar_year=".($year-1)); ?>', 'ploopi_popup_calendar');"><img style="border:0;" src="<?php echo $strIconsPath; ?>/img/calendar/prevx2.png"></a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('<? echo $strScript; ?>-light.php', '<?php echo ploopi_queryencode("ploopi_op=calendar_open&calendar_month={$prev_month}&calendar_year={$prev_year}"); ?>', 'ploopi_popup_calendar');"><img style="border:0;" src="<?php echo $strIconsPath; ?>/img/calendar/prev.png"></a>
                    </div>
                    <div class="calendar_month">
                        <?php echo "{$ploopi_months[$month]}<br />{$year}"; ?>
                    </div>
                </div>
                <div class="calendar_row">
                    <div class="calendar_day">&nbsp;</div>
                    <?php
                    for ($d=1; $d<=7; $d++)
                    {
                        ?>
                        <div class="calendar_day"><?php echo ploopi_htmlentities($ploopi_days[$d][0]); ?></div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if ($weekday > 1)
                {
                    $w = date('W', ploopi_timestamp2unixtimestamp(sprintf("%04d%02d01000000", $year, $month)));
                    ?>
                    <div class="calendar_row">
                    <div class="calendar_week">s<?php echo $w; ?></div>
                    <?php
                    for ($c = 1; $c < $weekday; $c++)
                    {
                        /**
                         * Affichage des derniers jours du mois précédent
                         */

                        $ts = ploopi_timestamp_add(sprintf("%04d%02d01000000", $year, $month), 0, 0, 0, 0, $c-$weekday);
                        $localdate = ploopi_timestamp2local($ts);
                        $d = intval(substr($ts, 6, 2), 10);
                        ?>
                        <div class="calendar_day"><a class="calendar_outmonth" href="javascript:void(0);" onclick="javascript:$('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>').value='<?php echo ploopi_htmlentities($localdate['date']); ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_dispatch_onchange('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>');"><?php echo $d; ?></a></div>
                        <?php
                    }
                }

                /**
                 * Boucle principale sur tous les jours du mois à afficher
                 */
                for ($d = 1; $d <= date('t', $firstday) ; $d++)
                {
                    if ($weekday == 8) $weekday = 1;

                    /**
                     * Chaque début de semaine = une nouvelle ligne
                     */
                    if ($weekday == 1)
                    {
                        $w = date('W', ploopi_timestamp2unixtimestamp(sprintf("%04d%02d%02d000000", $year, $month, $d)));
                        ?>
                        <div class="calendar_row">
                        <div class="calendar_week">s<?php echo $w; ?></div>
                        <?php
                    }
                    $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", $year, $month, $d));
                    $class = '';
                    $currentday = mktime(0,0,0,$month, $d, $year);
                    if ($currentday == $selectedday) $class = 'class="calendar_day_selected"';
                    elseif ($currentday == $today) $class = 'class="calendar_day_today"';
                    ?>
                        <div class="calendar_day"><a <?php echo $class; ?> href="javascript:void(0);" onclick="javascript:$('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>').value='<?php echo ploopi_htmlentities($localdate['date']); ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_dispatch_onchange('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>');"><?php echo $d; ?></a></div>
                    <?php

                    /**
                     * Chaque fin de semaine = fin de ligne
                     */
                    if ($weekday == 7) echo '</div>';

                    $weekday++;
                }

                /**
                 * Si le mois ne se termine pas un dimanche
                 */
                if ($weekday <= 7)
                {
                    for ($c = $weekday; $c <= 7 ; $c++)
                    {

                        $ts = ploopi_timestamp_add(sprintf("%04d%02d01000000", $year, $month), 0, 0, 0, 1, $c-$weekday);
                        $localdate = ploopi_timestamp2local($ts);
                        $d = intval(substr($ts, 6, 2), 10);
                        ?>
                        <div class="calendar_day"><a class="calendar_outmonth" href="javascript:void(0);" onclick="javascript:$('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>').value='<?php echo ploopi_htmlentities($localdate['date']); ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_dispatch_onchange('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>');"><?php echo $d; ?></a></div>
                        <?php
                    }

                    echo '</div>';

                }

                $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", date('Y'), date('n'), date('j')));
                ?>
                <div class="calendar_row" style="height:1.2em;overflow:hidden;">
                    <a style="display:block;float:left;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:$('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>').value='<?php echo ploopi_htmlentities($localdate['date']); ?>';ploopi_hidepopup('ploopi_popup_calendar');ploopi_dispatch_onchange('<?php echo ploopi_htmlentities($_SESSION['calendar']['inputfield_id']); ?>');">Aujourd'hui</a>
                    <a style="display:block;float:right;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('ploopi_popup_calendar');">Fermer</a>
                </div>
            </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();

            echo $skin->create_popup("Choix d'une date", $content, 'ploopi_popup_calendar');
            ploopi_die();
        break;

        case 'ploopi_get_userphoto':
            // Envoi de la photo d'un utilisateur vers le client
            $objUser = new user();
            if (!empty($_GET['ploopi_user_id']) && is_numeric($_GET['ploopi_user_id']) && $objUser->open($_GET['ploopi_user_id']))
            {
                $strPhotoPath = $objUser->getphotopath();
                if (file_exists($strPhotoPath)) ploopi_downloadfile($strPhotoPath, 'user.png', false, false);
            }
            ploopi_die();
        break;

        /*
         * Traitement des captchas
         */
        case 'ploopi_get_captcha':
            include_once './include/classes/captcha.php';

            $idcaptcha = (isset($_GET['id_captcha']) && !empty($_GET['id_captcha'])) ? $_GET['id_captcha'] : '';

            $objCaptcha = new captcha(
                $idcaptcha,
                array(
                    'captchawidth'  => 130,     // Taille X
                    'captchaheight' => 45,      // Taille Y
                    'captchaeasy'   => false,   // Captcha simple a lire (alternance de console/voyelle uniquement majuscule)
                    'charspace'     => 25,      // espace entre les caractères
                    'charsizemin'   => 16,      // taille de police mini
                    'charsizemax'   => 20,      // taille de police maxi
                    'noiselinemax'  => 4,       // nombre max de lignes dessinées
                    'nbcirclemax'   => 2,       // nombre max de cercle dessinées
                    'brushsize'     => 2,       // taille maxi de la brosse pour tracer les points/traits/cercles
                    'captchausetimer' => 4      // temps mini entre 2 refresh
                )
            );

            $objCaptcha->createCaptcha();
            ploopi_die();
        break;

        case 'ploopi_get_captcha_sound':
            include_once './include/classes/captcha.php';

            $idcaptcha = (isset($_GET['id_captcha']) && !empty($_GET['id_captcha'])) ? $_GET['id_captcha'] : '';

            $objCaptchaSound = new captcha_sound($idcaptcha);

            $objCaptchaSound->outputAudioFile();
            ploopi_die();
        break;

        case 'ploopi_get_captcha_verif':
            if(!empty($_POST['value']))
            {
                $idcaptcha = (isset($_GET['id_captcha']) && !empty($_GET['id_captcha'])) ? $_GET['id_captcha'] : '';

                include_once './include/classes/captcha.php';
                $objCaptcha = new captcha($idcaptcha);
                echo ($objCaptcha->verifCaptcha($_POST['value'])) ? 1 : 0;
            }
            else
                echo 0;

            ploopi_die();
        break;
    }

    if ($_SESSION['ploopi']['connected'])
    {
        include_once './include/op/annotation.php';
        include_once './include/op/documents.php';
        include_once './include/op/filexplorer.php';
        include_once './include/op/share.php';
        include_once './include/op/subscription.php';
        include_once './include/op/validation.php';
        include_once './include/op/tickets.php';
        include_once './modules/system/op.php';

        switch($ploopi_op)
        {
            case 'ploopi_switchdisplay':
                if (!empty($_GET['id'])) $_SESSION['ploopi']['switchdisplay'][$_GET['id']] = $_GET['display'];
                ploopi_die();
            break;

            case 'ploopi_checkpasswordvalidity':
                if (!isset($_POST['password'])) ploopi_die();
                if (_PLOOPI_USE_COMPLEXE_PASSWORD) echo ploopi_checkpasswordvalidity($_POST['password']);
                else echo true;
                ploopi_die();
            break;

            case 'ploopi_skin_array_refresh':
                if (!empty($_GET['array_id'])) $skin->display_array_refresh($_GET['array_id'], empty($_GET['array_orderby']) ? null : $_GET['array_orderby'], empty($_GET['array_page']) ? null : $_GET['array_page']);
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
                <?php
                $select_object = "
                    SELECT  ploopi_mb_wce_object.*,
                            ploopi_module.label as module_label,
                            ploopi_module.id as module_id

                    FROM    ploopi_mb_wce_object,
                            ploopi_module,
                            ploopi_module_workspace

                    WHERE   ploopi_mb_wce_object.id_module_type = ploopi_module.id_module_type
                      AND   ((ploopi_module_workspace.id_module = ploopi_module.id AND ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['workspaceid']}) OR ploopi_mb_wce_object.id_module_type = 1)
                    ORDER BY module_label, label
                ";

                $result_object = $db->query($select_object);
                while ($fields_object = $db->fetchrow($result_object))
                {
                    if ($fields_object['select_label'] != '')
                    {
                        $select = "select {$fields_object['select_id']}, {$fields_object['select_label']} from {$fields_object['select_table']} where id_module = {$fields_object['module_id']} ORDER BY {$fields_object['select_label']}";
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
                    <?php
                    foreach($array_modules as $key => $value)
                    {
                        //if ($fields_column['id_object'] == $key) $sel = 'selected';
                        //else $sel = '';
                        $sel = '';
                        ?>
                        <option <?php echo $sel; ?> value="<?php echo $key; ?>"><?php echo ploopi_htmlentities("{$value['module_label']} » {$value['label']}"); if (!empty($value['object_label'])) echo ploopi_htmlentities(" » {$value['object_label']}"); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
                $main_content = ob_get_contents();
                @ob_end_clean();

                $template_body->assign_vars(array(
                    'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                    'ADDITIONAL_JAVASCRIPT' => $ploopi_additional_javascript,
                    'PAGE_CONTENT'          => $main_content
                    )
                );

                $template_body->pparse('body');
                ploopi_die();
            break;
        }

    }

    //echo $_SESSION['ploopi']['workspaceid'];
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
