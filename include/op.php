<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * inclusions fonctions système (ploopi\system::kill...)
 */

/**
 * Renvoie les opérations qu'un module déclare publiques, c'est-à-dire dispensées du jeton
 * anti-CSRF.
 *
 * La déclaration se fait dans modules/<module>/include/publicops.php, qui retourne un
 * tableau de noms d'opérations. Un module sans ce fichier n'a aucune opération publique.
 *
 * @param string $strModuleType nom du type de module
 * @return array noms des opérations publiques
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

function ploopi_op_getpublicops($strModuleType)
{
    static $arrCache = array();

    if (isset($arrCache[$strModuleType])) return $arrCache[$strModuleType];

    $arrPublicOps = array();
    $strFichier = "./modules/{$strModuleType}/include/publicops.php";

    if (file_exists($strFichier))
    {
        $mixDeclaration = include $strFichier;
        if (is_array($mixDeclaration)) $arrPublicOps = $mixDeclaration;
    }

    return $arrCache[$strModuleType] = $arrPublicOps;
}

if (isset($_REQUEST['ploopi_op']))
{
    $ploopi_op = $_REQUEST['ploopi_op'];

    switch($ploopi_op)
    {
        /**
         * Point de collecte des rapports de violation de la CSP en Report-Only
         * (lot 11, décision D6). Le navigateur y POSTe ses rapports sans session
         * ni jeton : le corps est lu brut, un exemplaire lisible est journalisé,
         * et rien d'autre n'est exposé — l'opération ne lit pas la session.
         */
        case 'ploopi_csp_report':
            $strRapport = file_get_contents('php://input');
            if ($strRapport !== '' && strlen($strRapport) < 65536)
            {
                // Le rapport du navigateur est un document JSON : en extraire
                // l'essentiel pour un journal lisible, conserver le reste tel quel.
                $arrRapport = json_decode($strRapport, true);
                $arrRapport = isset($arrRapport['csp-report']) && is_array($arrRapport['csp-report']) ? $arrRapport['csp-report'] : array();

                /**
                 * Le point de collecte est anonyme : chaque champ est une chaîne
                 * quelconque. On n'en garde que du texte sur une ligne, borné —
                 * un saut de ligne fabriquerait une fausse entrée de journal.
                 */
                $champ = function($strCle, $strDefaut) use ($arrRapport) {
                    if (!isset($arrRapport[$strCle]) || !is_scalar($arrRapport[$strCle])) return $strDefaut;
                    $strValeur = preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string)$arrRapport[$strCle]);
                    return mb_strlen($strValeur) > 255 ? mb_substr($strValeur, 0, 255).'…' : $strValeur;
                };

                $strDocument = $champ('document-uri', '?');
                $strDirective = $champ('violated-directive', '?');
                $strBloc = $champ('source-file', '');
                $intLigne = isset($arrRapport['line-number']) && is_scalar($arrRapport['line-number']) ? (int)$arrRapport['line-number'] : 0;
                // disposition « enforce » : la politique appliquée a bloqué (§ 3.32), sinon simple mesure
                $strPolitique = $champ('disposition', 'report') == 'enforce' ? 'CSP bloquante' : 'CSP Report-Only';
                $strMessage = "{$strPolitique} : {$strDirective} sur {$strDocument}".($strBloc !== '' ? " ({$strBloc} ligne {$intLigne})" : '');
                ploopi\error::syslog(LOG_INFO, $strMessage);
                // Journal fichier de l'instance : le rapport doit y être mesurable
                // sans accès au syslog du serveur.
                if (defined('_PLOOPI_LOG_ERRORS') && _PLOOPI_LOG_ERRORS && defined('_PLOOPI_LOG_ERRORS_FILE') && _PLOOPI_LOG_ERRORS_FILE != '')
                {
                    file_put_contents(_PLOOPI_LOG_ERRORS_FILE, "=============================================\n{$strMessage}\n", FILE_APPEND);
                }
            }
            ploopi\system::kill();
        break;

        case 'ploopi_robots':

            // Mise en cache
            $objCache = new ploopi\cache(_PLOOPI_BASEPATH.'/robots.txt', 300);

            if (!$objCache->start())
            {
                if (_PLOOPI_FRONTOFFICE) {
                    $arrDisallow = array(
                        '/bin/',
                        '/cgi/',
                        '/config/',
                        '/data/',
                        '/doc/',
                        '/img/',
                        '/include/',
                        '/install/',
                        '/js/',
                        '/lang/',
                        '/lib/',
                        '/modules/',
                        // '/templates/', (pose problème pour l'indexation Googlebot)
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
            ploopi\system::kill();
        break;

        case 'ploopi_lostpassword':
        case 'ploopi_lostpassword_confirm':

            if (!isset($_REQUEST['ploopi_lostpassword_login']) && !isset($_REQUEST['ploopi_lostpassword_email'])) ploopi\system::kill();

            // Adresse et nom du site pour les courriels : jamais l'entête Host (system::publicurl)
            $strAdresse = ploopi\system::publicurl();
            $strSite = $strAdresse === false ? '' : (string)parse_url($strAdresse, PHP_URL_HOST);
            $strLpLogin = isset($_REQUEST['ploopi_lostpassword_login']) && is_string($_REQUEST['ploopi_lostpassword_login']) ? $_REQUEST['ploopi_lostpassword_login'] : '';
            $strLpEmail = isset($_REQUEST['ploopi_lostpassword_email']) && is_string($_REQUEST['ploopi_lostpassword_email']) ? $_REQUEST['ploopi_lostpassword_email'] : '';

            /**
             * La réponse ne dit jamais si le compte existe :
             * même message pour un compte inconnu, sans adresse, ambigu ou servi. Les cas
             * sont journalisés pour l'administrateur.
             */
            $intMsg = _PLOOPI_MSG_MAILSENT;
            $row = false;

            if ($strAdresse === false)
            {
                // Aucune adresse sûre pour le lien : voir system::publicurl()
                ploopi\error::syslog(LOG_WARNING, "Mot de passe oublié : hôte '{$_SERVER['HTTP_HOST']}' non déclaré, aucun lien envoyé. Renseigner _PLOOPI_PUBLIC_URL dans config/config.php");
                $intError = _PLOOPI_ERROR_LOSTPASSWORD_UNAVAILABLE;
            }
            elseif ($strLpLogin !== '' || $strLpEmail !== '')
            {
                $arrWhere = array();
                if ($strLpLogin !== '') $arrWhere[] = "login = '".ploopi\db::get()->addslashes($strLpLogin)."'";
                if ($strLpEmail !== '') $arrWhere[] = "email = '".ploopi\db::get()->addslashes($strLpEmail)."'";

                ploopi\db::get()->query('SELECT id, email, lastname, firstname FROM ploopi_user WHERE '.implode(' AND ', $arrWhere));

                if (ploopi\db::get()->numrows() == 1) $row = ploopi\db::get()->fetchrow();
                else ploopi\error::syslog(LOG_INFO, 'Mot de passe oublié : '.ploopi\db::get()->numrows().' compte(s) pour la demande');

                if ($row && empty($row['email'])) { ploopi\error::syslog(LOG_INFO, "Mot de passe oublié : compte {$row['id']} sans adresse"); $row = false; }
            }

            if ($row && $ploopi_op == 'ploopi_lostpassword_confirm')
            {
                /**
                 * Le lien mène à la saisie d'un nouveau mot de passe : plus aucun mot de passe
                 * n'est envoyé en clair par courriel. Le code vaut une heure
                 * (_PLOOPI_LOSTPASSWORD_DELAY) et une seule fois.
                 */
                $strCode = isset($_GET['ploopi_lostpassword_confirmcode']) && is_string($_GET['ploopi_lostpassword_confirmcode']) ? $_GET['ploopi_lostpassword_confirmcode'] : '';
                $confirmation_code = new ploopi\confirmation_code();
                $intDelai = defined('_PLOOPI_LOSTPASSWORD_DELAY') ? (int)_PLOOPI_LOSTPASSWORD_DELAY : 3600;

                $booCode = $strCode !== '' && $confirmation_code->open("ploopi_lostpassword{$row['id']}") && hash_equals((string)$confirmation_code->fields['code'], $strCode);
                // Code juste mais périmé : supprimé
                if ($booCode && ploopi\date::timestamp2unixtimestamp($confirmation_code->fields['timestp']) < time() - $intDelai) { $confirmation_code->delete(); $booCode = false; }

                if (!$booCode)
                {
                    $_SESSION['ploopi']['errorcode'] = _PLOOPI_ERROR_LOSTPASSWORD_EXPIRED;
                    ploopi\output::redirect('admin.php');
                }

                $strErreur = '';
                if (isset($_POST['ploopi_lostpassword_new']) && isset($_POST['ploopi_lostpassword_new_confirm']))
                {
                    if ($_POST['ploopi_lostpassword_new'] !== $_POST['ploopi_lostpassword_new_confirm']) $strErreur = 'Les deux saisies ne correspondent pas';
                    elseif (!ploopi\security::passwordallowed($_POST['ploopi_lostpassword_new'])) $strErreur = 'Mot de passe refusé : au moins '.(int) _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE.' caractères'.(_PLOOPI_USE_COMPLEXE_PASSWORD ? ', dont une minuscule, une majuscule, un chiffre et un caractère de ponctuation' : '');
                    else
                    {
                        $objUser = new ploopi\user();
                        $objUser->open($row['id']);
                        $objUser->setpassword($_POST['ploopi_lostpassword_new']);
                        $objUser->fields['password_force_update'] = 0;
                        $objUser->fields['failed_attemps'] = 0;
                        $objUser->fields['jailed_since'] = 0;
                        $objUser->save();
                        $confirmation_code->delete();
                        ploopi\user_action_log::record(_SYSTEM_ACTION_LOGIN_OK, "mot de passe réinitialisé ({$row['id']})", _PLOOPI_MODULE_SYSTEM, _PLOOPI_MODULE_SYSTEM);

                        $_SESSION['ploopi']['msgcode'] = _PLOOPI_MSG_PASSWORDSENT;
                        ploopi\output::redirect('admin.php');
                    }
                }

                // Formulaire de saisie, sans script (CSP stricte)
                $strAction = ploopi\crypt::urlencode('admin.php?ploopi_op=ploopi_lostpassword_confirm&ploopi_lostpassword_login='.urlencode($strLpLogin).'&ploopi_lostpassword_email='.urlencode($strLpEmail).'&ploopi_lostpassword_confirmcode='.urlencode($strCode));
                ploopi\buffer::clean();
                ploopi\csp::strict();
                $strTitre = 'Nouveau mot de passe';
                ?><!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php echo $strTitre; ?></title>
<style>body{font-family:sans-serif;max-width:24rem;margin:3rem auto;padding:0 1rem}label,input,button{display:block;width:100%;box-sizing:border-box;margin:.4rem 0}input{padding:.5rem}button{padding:.6rem}.erreur{color:#b00020}</style></head>
<body><h1><?php echo $strTitre; ?></h1>
<?php if ($strErreur !== '') echo '<p class="erreur" role="alert">'.ploopi\str::htmlentities($strErreur).'</p>'; ?>
<form method="post" action="<?php echo ploopi\str::htmlentities($strAction); ?>">
<label for="lp_new">Nouveau mot de passe</label><input type="password" id="lp_new" name="ploopi_lostpassword_new" autocomplete="new-password" required>
<label for="lp_conf">Confirmation</label><input type="password" id="lp_conf" name="ploopi_lostpassword_new_confirm" autocomplete="new-password" required>
<button type="submit">Enregistrer</button>
</form></body></html><?php
                ploopi\system::kill();
            }
            elseif ($row)
            {
                $confirmation_code = new ploopi\confirmation_code();
                $strAction = "ploopi_lostpassword{$row['id']}";

                // Une demande toutes les cinq minutes au plus par compte
                if ($confirmation_code->open($strAction) && ploopi\date::timestamp2unixtimestamp($confirmation_code->fields['timestp']) > time() - 300)
                {
                    ploopi\error::syslog(LOG_INFO, "Mot de passe oublié : demande répétée pour le compte {$row['id']}, ignorée");
                }
                else
                {
                    if (!$confirmation_code->isnew()) $confirmation_code->delete();

                    $confirmation_code = new ploopi\confirmation_code();
                    $confirmation_code->fields['action'] = $strAction;
                    $confirmation_code->save();

                    ploopi\mail::send(
                        array(
                            array(
                                    'name' => $strSite,
                                    'address' => trim(current(explode(',', _PLOOPI_ADMINMAIL)))
                            )
                        ),
                        array(
                            array(
                                'name' => "{$row['lastname']} {$row['firstname']}",
                                'address' => $row['email']
                            )
                        ),
                        "{$strSite} : modification de votre mot de passe",
                        "Bonjour,\n\nvous recevez ce message car vous avez effectué une demande de mot de passe sur le site {$strSite}.\n\nPour choisir un nouveau mot de passe, suivez ce lien (valable une heure, une seule fois) :\n\n".$strAdresse."/".ploopi\crypt::urlencode("admin.php?ploopi_op=ploopi_lostpassword_confirm&ploopi_lostpassword_login=".urlencode($strLpLogin)."&ploopi_lostpassword_email=".urlencode($strLpEmail)."&ploopi_lostpassword_confirmcode={$confirmation_code->fields['code']}")."\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.",
                        null,
                        null,
                        null,
                        null,
                        false
                    );
                }
            }
            elseif ($ploopi_op == 'ploopi_lostpassword_confirm') $intError = _PLOOPI_ERROR_LOSTPASSWORD_EXPIRED;

            if (isset($intError)) $_SESSION['ploopi']['errorcode'] = $intError;
            else $_SESSION['ploopi']['msgcode'] = $intMsg;

            ploopi\output::redirect('admin.php');
        break;
        case 'calendar_open':
            ob_start();

            $month = date('n');
            $year = date('Y');

            if (!empty($_REQUEST['inputfield_id']))
            {
                // Identifiant recopié dans le JavaScript de chaque jour : un identifiant simple, rien d'autre
                if (!preg_match('/^[A-Za-z_][A-Za-z0-9_-]*$/', $_REQUEST['inputfield_id'])) ploopi\system::kill();
                $_SESSION['calendar']['inputfield_id'] = $_REQUEST['inputfield_id'];
            }

            if (empty($_SESSION['calendar']['inputfield_id'])) ploopi\system::kill();

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
            $booP4 = $_SESSION['ploopi']['mode'] == 'backoffice' && ploopi\skin::get()->version() >= 4;
            ?>
            <div id="calendar" data-ploopi-calendar-field="<?php echo ploopi\str::htmlentities($_SESSION['calendar']['inputfield_id']); ?>">
                <div class="calendar_row">
                    <?php
                    /**
                     * Flèches de navigation : icônes du sprite et libellé sous ploopi4,
                     * images du gabarit ailleurs
                     */
                    $funcFleche = function($intMois, $intAnnee, $strImage, $strIcone, $strLabel) use ($strScript, $strIconsPath, $booP4) {
                        // Chargement dans la fenêtre par js/actions.js (aucun gestionnaire en ligne, CSP)
                        $strAttrs = ' data-ploopi-todiv="ploopi_popup_calendar" data-ploopi-script="'.$strScript.'-light.php" data-ploopi-query="'.ploopi\str::htmlentities(ploopi\crypt::queryencode("ploopi_op=calendar_open&calendar_month={$intMois}&calendar_year={$intAnnee}")).'"';
                        if ($booP4) return '<a href="#" class="calendar_nav" aria-label="'.ploopi\str::htmlentities($strLabel).'" title="'.ploopi\str::htmlentities($strLabel).'"'.$strAttrs.'>'.ploopi\skin::get()->icon($strIcone).'</a>';
                        return '<a href="#"'.$strAttrs.'><img style="border:0;" src="'.$strIconsPath.'/img/calendar/'.$strImage.'.png"></a>';
                    };
                    ?>
                    <div class="calendar_arrow" style="float:right;">
                        <?php echo $funcFleche($next_month, $next_year, 'next', 'chevron-right', _PLOOPI_LABEL_CALENDAR_NEXTMONTH); ?>
                        <?php echo $funcFleche($month, $year + 1, 'nextx2', 'chevrons-right', _PLOOPI_LABEL_CALENDAR_NEXTYEAR); ?>
                    </div>
                    <div class="calendar_arrow" style="float:left;">
                        <?php echo $funcFleche($month, $year - 1, 'prevx2', 'chevrons-left', _PLOOPI_LABEL_CALENDAR_PREVYEAR); ?>
                        <?php echo $funcFleche($prev_month, $prev_year, 'prev', 'chevron-left', _PLOOPI_LABEL_CALENDAR_PREVMONTH); ?>
                    </div>
                    <div class="calendar_month">
                        <?php echo $booP4 ? "{$ploopi_months[$month]} {$year}" : "{$ploopi_months[$month]}<br />{$year}"; ?>
                    </div>
                </div>
                <div class="calendar_row">
                    <div class="calendar_day">&nbsp;</div>
                    <?php
                    for ($d=1; $d<=7; $d++)
                    {
                        ?>
                        <div class="calendar_day"><?php echo ploopi\str::htmlentities($ploopi_days[$d][0]); ?></div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if ($weekday > 1)
                {
                    $w = date('W', ploopi\date::timestamp2unixtimestamp(sprintf("%04d%02d01000000", $year, $month)));
                    ?>
                    <div class="calendar_row">
                    <div class="calendar_week">s<?php echo $w; ?></div>
                    <?php
                    for ($c = 1; $c < $weekday; $c++)
                    {
                        /**
                         * Affichage des derniers jours du mois précédent
                         */

                        $ts = ploopi\date::timestamp_add(sprintf("%04d%02d01000000", $year, $month), 0, 0, 0, 0, $c-$weekday);
                        $localdate = ploopi\date::timestamp2local($ts);
                        $d = intval(substr($ts, 6, 2), 10);
                        ?>
                        <div class="calendar_day"><a class="calendar_outmonth" href="#" data-ploopi-calendar-day="<?php echo ploopi\str::htmlentities($localdate['date']); ?>"><?php echo $d; ?></a></div>
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
                        $w = date('W', ploopi\date::timestamp2unixtimestamp(sprintf("%04d%02d%02d000000", $year, $month, $d)));
                        ?>
                        <div class="calendar_row">
                        <div class="calendar_week">s<?php echo $w; ?></div>
                        <?php
                    }
                    $localdate = ploopi\date::timestamp2local(sprintf("%04d%02d%02d000000", $year, $month, $d));
                    $class = '';
                    $currentday = mktime(0,0,0,$month, $d, $year);
                    if ($currentday == $selectedday) $class = 'class="calendar_day_selected"';
                    elseif ($currentday == $today) $class = 'class="calendar_day_today"';
                    ?>
                        <div class="calendar_day"><a <?php echo $class; ?> href="#" data-ploopi-calendar-day="<?php echo ploopi\str::htmlentities($localdate['date']); ?>"><?php echo $d; ?></a></div>
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

                        $ts = ploopi\date::timestamp_add(sprintf("%04d%02d01000000", $year, $month), 0, 0, 0, 1, $c-$weekday);
                        $localdate = ploopi\date::timestamp2local($ts);
                        $d = intval(substr($ts, 6, 2), 10);
                        ?>
                        <div class="calendar_day"><a class="calendar_outmonth" href="#" data-ploopi-calendar-day="<?php echo ploopi\str::htmlentities($localdate['date']); ?>"><?php echo $d; ?></a></div>
                        <?php
                    }

                    echo '</div>';

                }

                $localdate = ploopi\date::timestamp2local(sprintf("%04d%02d%02d000000", date('Y'), date('n'), date('j')));
                ?>
                <div class="calendar_row calendar_foot" style="height:1.2em;overflow:hidden;">
                    <a style="display:block;float:left;line-height:1.2em;height:1.2em;" href="#" data-ploopi-calendar-day="<?php echo ploopi\str::htmlentities($localdate['date']); ?>"><?php echo ploopi\str::htmlentities(_PLOOPI_LABEL_CALENDAR_TODAY); ?></a>
                    <a style="display:block;float:right;line-height:1.2em;height:1.2em;" href="#" data-ploopi-popup-hide="ploopi_popup_calendar"><?php echo ploopi\str::htmlentities(_PLOOPI_LABEL_CALENDAR_CLOSE); ?></a>
                </div>
            </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();

            echo ploopi\skin::get()->create_popup(_PLOOPI_LABEL_CALENDAR_TITLE, $content, 'ploopi_popup_calendar');
            ploopi\system::kill();
        break;

        case 'ploopi_get_userphoto':
            // Envoi de la photo d'un utilisateur vers le client
            $objUser = new ploopi\user();
            /**
             * Session connectée, ou URL chiffrée par le serveur (objet WCE de system en
             * frontoffice)
             */
            if ((!empty($_SESSION['ploopi']['connected']) || ploopi\loader::urlchiffree()) && !empty($_GET['ploopi_user_id']) && is_numeric($_GET['ploopi_user_id']) && $objUser->open($_GET['ploopi_user_id']))
            {
                $strPhotoPath = $objUser->getphotopath();
                if (file_exists($strPhotoPath)) ploopi\fs::downloadfile($strPhotoPath, 'user.png', false, false);
            }
            ploopi\system::kill();
        break;
    }

    /**
     * Un visiteur non connecté n'a jamais de jeton : seules les opérations publiques des
     * modules lui sont accessibles.
     */
    $booTokenValide = false;

    if ($_SESSION['ploopi']['connected'])
    {
        /**
         * Les opérations du socle et du module "system" modifient des données sensibles
         * (documents, tickets, partages, comptes utilisateurs...) : elles exigent un jeton
         * anti-CSRF valide.
         *
         * admin.php et admin-light.php passent par loader::start() qui a déjà validé le jeton,
         * mais index-quick.php court-circuite start() : sans ce contrôle, ces opérations sont
         * exécutables depuis un site tiers avec la session de la victime.
         *
         * @see ploopi\loader::checktoken
         */
        $booTokenValide = ploopi\loader::checktoken();

        if ($booTokenValide)
        {
            include_once './include/op/annotation.php';
            include_once './include/op/documents.php';
            include_once './include/op/filexplorer.php';
            include_once './include/op/share.php';
            include_once './include/op/subscription.php';
            include_once './include/op/validation.php';
            include_once './include/op/tickets.php';
            include_once './modules/system/op.php';
        }
        else ploopi\error::syslog(LOG_INFO, "Jeton absent ou non valide, opération '{$ploopi_op}' ignorée");

        // Opérations du bloc « connecté » : jeton exigé comme les autres
        if ($booTokenValide) switch($ploopi_op)
        {
            case 'ploopi.switchdisplay':
                // Valeur réécrite dans un attribut style : block ou none, rien d'autre
                if (!empty($_GET['id']) && isset($_GET['display']) && in_array($_GET['display'], array('block', 'none'), true)) $_SESSION['ploopi']['switchdisplay'][$_GET['id']] = $_GET['display'];
                ploopi\system::kill();
            break;

            case 'ploopi_checkpasswordvalidity':
                if (!isset($_POST['password'])) ploopi\system::kill();
                echo ploopi\security::passwordallowed($_POST['password']);
                ploopi\system::kill();
            break;

            case 'ploopi_skin_array_refresh':
                if (!empty($_GET['array_id'])) ploopi\skin::get()->display_array_refresh($_GET['array_id'], empty($_GET['array_orderby']) ? null : $_GET['array_orderby'], empty($_GET['array_page']) ? null : $_GET['array_page']);
                ploopi\system::kill();
            break;

            case 'ploopi_getobjects':
                /*
                ob_start();
                ?>
                <script language="javascript">

                var oEditor = window.parent.InnerDialogLoaded() ;
                var FCKLang = oEditor.FCKLang ;
                var FCKPlaceholders = oEditor.FCKPlaceholders ;

                window.onload = function ()
                {
                    oEditor.FCKLanguageManager.TranslatePage( document ) ;

                    LoadSelected() ;

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

                $result_object = ploopi\db::get()->query($select_object);
                while ($fields_object = ploopi\db::get()->fetchrow($result_object))
                {
                    if ($fields_object['select_label'] != '')
                    {
                        $select = "select {$fields_object['select_id']}, {$fields_object['select_label']} from {$fields_object['select_table']} where id_module = {$fields_object['module_id']} ORDER BY {$fields_object['select_label']}";
                        ploopi\db::get()->query($select);

                        while ($fields = ploopi\db::get()->fetchrow())
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
                        <option <?php echo $sel; ?> value="<?php echo $key; ?>"><?php echo ploopi\str::htmlentities("{$value['module_label']} » {$value['label']}"); if (!empty($value['object_label'])) echo ploopi\str::htmlentities(" » {$value['object_label']}"); ?></option>
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
                    'CSP_NONCE'             => ploopi\csp::nonce(),
                    'PAGE_CONTENT'          => $main_content
                    )
                );

                $template_body->pparse('body');
                ploopi\system::kill();
                */
                ploopi\buffer::clean();

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

                $array_modules = array();
                $result_object = ploopi\db::get()->query($select_object);
                while ($fields_object = ploopi\db::get()->fetchrow($result_object))
                {
                    // Seulement les modules où l'utilisateur détient au moins une action
                    if ($fields_object['module_id'] != _PLOOPI_MODULE_SYSTEM && !ploopi\acl::isactionallowed(-1, $_SESSION['ploopi']['workspaceid'], $fields_object['module_id'])) continue;

                    if ($fields_object['select_label'] != '')
                    {
                        $select = "select {$fields_object['select_id']}, {$fields_object['select_label']} from {$fields_object['select_table']} where id_module = {$fields_object['module_id']} ORDER BY {$fields_object['select_label']}";
                        ploopi\db::get()->query($select);

                        while ($fields = ploopi\db::get()->fetchrow())
                        {
                            $fields_object['object_label'] = $fields[$fields_object['select_label']];
                            $array_modules["{$fields_object['id']},{$fields_object['module_id']},{$fields[$fields_object['select_id']]}"] = "{$fields_object['module_label']} » {$fields_object['label']} » {$fields_object['object_label']}";
                        }
                    }
                    else $array_modules["{$fields_object['id']},{$fields_object['module_id']}"] = "{$fields_object['module_label']} » {$fields_object['label']}";
                }

                ploopi\str::print_json($array_modules, false, false);

                ploopi\system::kill();

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
                    $strModuleType = $_SESSION['ploopi']['modules'][$idm]['moduletype'];
                    $ploopi_mod_opfile = "./modules/{$strModuleType}/op.php";

                    if (file_exists($ploopi_mod_opfile))
                    {
                        /**
                         * Les opérations d'un module exigent, comme celles du socle, un jeton
                         * anti-CSRF valide : index-quick.php court-circuite loader::start(),
                         * qui est l'endroit où le jeton était vérifié.
                         *
                         * Un module peut déclarer les opérations qui échappent à ce contrôle
                         * dans modules/<module>/include/publicops.php, fichier retournant un
                         * tableau de noms d'opérations. N'y déclarer que des opérations sans
                         * effet de bord, appelées par des URL qui ne portent pas de jeton :
                         * vignettes, téléchargements liés depuis une page publique.
                         *
                         * @see ploopi\loader::checktoken
                         */
                        if (!$booTokenValide && !in_array($ploopi_op, ploopi_op_getpublicops($strModuleType), true))
                        {
                            ploopi\error::syslog(LOG_INFO, "Jeton absent ou non valide, opération '{$ploopi_op}' du module {$strModuleType} ignorée");
                            continue;
                        }

                        include_once $ploopi_mod_opfile;
                    }
                }
            }
        }
    }

}
