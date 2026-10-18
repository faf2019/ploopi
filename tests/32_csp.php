<?php
/*    Copyright (c) 2007-2018 Ovensia    Contributors hold Copyright (c) to their code submissions.
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  0211-1301  USA*/

/**
 * Suite 32 — CSP en Report-Only (lot 11, décision D6).
 *
 * Une Content-Security-Policy stricte est bloquée par les nombreux gestionnaires
 * onclick en ligne du backoffice : la faire échouer casserait l'application. La
 * feuille de route tranche : mesurer d'abord. Le navigateur reçoit une politique
 * en Report-Only — il n'applique rien, il rapporte chaque violation au point de
 * collecte du socle.
 *
 * La suite vérifie que :
 * - l'entête Report-Only est posée sur les pages servies (backoffice et
 *   frontoffice) et porte la directive report-uri du point de collecte ;
 * - le point de collecte (ploopi_csp_report) répond sans session ni jeton —
 *   le navigateur POSTe ses rapports sans cookies ;
 * - un rapport posté est journalisé dans le journal des erreurs de l'instance,
 *   sous une forme lisible (directive, document, source) ;
 * - un rapport vide n'écrit rien.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();

/**
 * Le journal des erreurs de l'instance, où error::syslog() écrit.
 */
function csp_journal()
{
    return '/tmp/ploopi_error.log';
}

T::section('Entête Report-Only sur les pages servies');

H::reset();
$arrRep = H::req('/admin.php');
T::vrai('l\'entête CSP Report-Only est posée sur le backoffice',
    stripos($arrRep['headers'], 'Content-Security-Policy-Report-Only:') !== false);
preg_match('/Content-Security-Policy-Report-Only: *([^\\r\\n]*)/i', $arrRep['headers'], $arrMatches);
$strCsp = isset($arrMatches[1]) ? $arrMatches[1] : '';
T::contient('la politique porte la directive report-uri du point de collecte',
    'report-uri '.cfg('base_url').'/index-quick.php?ploopi_op=ploopi_csp_report', $strCsp);
T::contient('la directive script-src est restreinte (les onclick en ligne seront rapportés)',
    "script-src ".chr(39)."self".chr(39), $strCsp);

T::section('Point de collecte sans session ni jeton');

// le rapport type que POSTe un navigateur
$strRapport = json_encode(array('csp-report' => array(
    'document-uri' => cfg('base_url').'/admin.php',
    'violated-directive' => "script-src 'self'", 
    'source-file' => cfg('base_url').'/admin.php',
    'line-number' => 42,
)));

// sans session établie : le navigateur n'envoie pas les cookies vers report-uri
H::reset();
$strJournal = csp_journal();
clearstatcache();
$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;
$arrRep = H::req('/index-quick.php?ploopi_op=ploopi_csp_report', $strRapport, array('Content-Type: application/csp-report'));
T::vrai('le point de collecte répond', $arrRep['code'] !== 0, "HTTP {$arrRep['code']}");

clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contient('le rapport est journalisé, avec la directive en cause',
    "script-src ".chr(39)."self".chr(39)." sur ".cfg('base_url').'/admin.php', $strAjout);
T::contient('et la source du blocage',
    'ligne 42', $strAjout);

// rapport vide : rien à journaliser
$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;
H::req('/index-quick.php?ploopi_op=ploopi_csp_report', '');
clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contientpas('un rapport vide n\'écrit rien', 'CSP Report-Only', $strAjout);

/**
 * Le point de collecte est anonyme : son contenu est celui de n'importe qui.
 * Un saut de ligne dans un champ du rapport fabriquait une fausse ligne de
 * journal, un champ tableau une alerte « Array to string conversion ».
 */
T::section('Rapport forgé');

$strRapport = json_encode(array('csp-report' => array(
    'document-uri' => "https://exemple.test/\n=============================================\nFAUSSE_ENTREE_CSP",
    'violated-directive' => array('script-src'),
    'source-file' => str_repeat('A', 5000),
    'line-number' => 7,
)));
$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;
H::req('/index-quick.php?ploopi_op=ploopi_csp_report', $strRapport, array('Content-Type: application/csp-report'));
clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contient('témoin : le rapport forgé est journalisé', 'CSP Report-Only', $strAjout);
T::vrai('un saut de ligne ne fabrique pas de fausse ligne de journal', !preg_match('/^FAUSSE_ENTREE_CSP/m', $strAjout), $strAjout);
T::contientpas('un champ tableau ne produit pas d\'alerte PHP', 'Array to string', $strAjout);
T::vrai('un champ démesuré est tronqué', strpos($strAjout, str_repeat('A', 1000)) === false);

/**
 * § 3.32 : politique de base appliquée partout, politique stricte (nonce) sur les écrans
 * prêts (page de connexion et module system sous ploopi4), politique cible en
 * Report-Only. Le nonce vaut pour la session : les fragments chargés en AJAX portent
 * celui de la page qui les reçoit.
 */
amorcer_socle();

/** Entête d'une réponse */
function csp_entete($arrRep, $strNom)
{
    return preg_match('/^'.preg_quote($strNom, '/').': *([^\r\n]*)/mi', $arrRep['headers'], $arrM) ? $arrM[1] : '';
}

/** Nonce d'une politique */
function csp_nonce($strPolitique)
{
    return preg_match("/'nonce-([^']+)'/", $strPolitique, $arrM) ? $arrM[1] : '';
}

/** Environnement réglé sur le module system de l'espace 2 */
function csp_env()
{
    $arrEnv = explode('/', H::env());
    if (sizeof($arrEnv) !== 5) return '';
    $arrEnv[0] = _PLOOPI_MENU_WORKSPACES; $arrEnv[1] = 2; $arrEnv[2] = _PLOOPI_MODULE_SYSTEM; $arrEnv[3] = 'admin';
    return implode('/', $arrEnv);
}

/** Connexion de l'administrateur de l'instance */
function csp_connecter()
{
    H::reset();
    H::req('/admin.php', 'ploopi_login='.urlencode(cfg('admin_login')).'&ploopi_password='.urlencode(cfg('admin_password')));
    H::req('/admin.php');
}

T::section('Politique stricte : page de connexion (ploopi4)');

H::reset();
$arrRep = H::req('/admin.php');
$strCsp = csp_entete($arrRep, 'Content-Security-Policy');
$strNonce = csp_nonce($strCsp);
T::vrai('politique appliquée (et non plus seulement Report-Only)', $strCsp !== '', $arrRep['headers']);
T::vrai('script-src avec nonce', $strNonce !== '' && strpos($strCsp, "script-src 'self' 'nonce-{$strNonce}'") !== false, $strCsp);
foreach(array("object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'self'", 'report-uri ') as $strDirective) T::contient("directive {$strDirective}", $strDirective, $strCsp);
T::contientpas('jamais unsafe-eval', 'unsafe-eval', $arrRep['headers']);
T::vrai('le script en ligne du gabarit porte le nonce', $strNonce !== '' && strpos($arrRep['body'], 'nonce="'.$strNonce.'"') !== false);
T::eq('aucun script en ligne sans nonce dans la page', '', ploopi\csp::inlinescript(str_replace($strNonce, ploopi\csp::nonce(), $arrRep['body'])));
T::contient('politique cible toujours mesurée (Report-Only, même nonce)', "'nonce-{$strNonce}'", csp_entete($arrRep, 'Content-Security-Policy-Report-Only'));

T::section('Politique stricte : écrans du module system (ploopi4)');

csp_connecter();
$strEnv = csp_env();
$arrRep = H::req('/admin.php?system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces&ploopi_env='.urlencode($strEnv));
$strCsp = csp_entete($arrRep, 'Content-Security-Policy');
$strNonce = csp_nonce($strCsp);
T::vrai('écran d\'un espace : politique stricte', $strNonce !== '', $strCsp);
$arrRep2 = H::req('/admin.php?system_level=system&sysToolbarItem=tools&op=list&ploopi_env='.urlencode($strEnv));
T::eq('même nonce pour toute la session (fragments AJAX)', $strNonce, csp_nonce(csp_entete($arrRep2, 'Content-Security-Policy')));
$arrRep = H::req('/admin-light.php?ploopi_op=tickets_new&ploopi_env='.urlencode($strEnv));
T::contient('fenêtre de ploopi4 servie', 'data-p4-popup="system_popupticket"', $arrRep['body']);
T::eq('fenêtre de ploopi4 : aucun script en ligne (déplaçable par popup.js)', '', ploopi\csp::inlinescript($arrRep['body']));

T::section('GED du socle dans la fiche d\'un utilisateur (ploopi4)');

$intAdmin = (int) db()->query("SELECT id FROM ploopi_user WHERE login = '".db()->real_escape_string(cfg('admin_login'))."'")->fetch_row()[0];
$arrRep = H::req('/admin.php?system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=modify_user&user_id='.$intAdmin.'&ploopi_env='.urlencode($strEnv));
T::contient('témoin : la fiche insère le navigateur de la GED', 'data-ploopi-documents="openfolder"', $arrRep['body']);
T::vrai('politique stricte (plus aucun gestionnaire en ligne)', csp_nonce(csp_entete($arrRep, 'Content-Security-Policy')) !== '', ploopi\csp::inlinescript($arrRep['body']));
preg_match('/data-ploopi-documents="openfile" data-query="([^"]+)"/', $arrRep['body'], $arrM);
$arrRep = H::req('/admin-light.php?'.html_entity_decode($arrM[1] ?? ''));
T::contient('formulaire d\'un fichier : paramètres en attributs', 'data-ploopi-documents-form="new"', $arrRep['body']);
T::eq('formulaire d\'un fichier : aucun script en ligne', '', ploopi\csp::inlinescript($arrRep['body']));
T::contientpas('formulaire d\'un fichier : plus d\'alerte de débogage', "alert('submit')", file_get_contents(PLOOPI_ROOT.'/include/op/documents.php'));

T::section('Garde-fou : texte qui ressemble à un gestionnaire');
/**
 * Le contrôle de fin de requête est prudent : un nom d'utilisateur contenant
 * « onclick= » (affiché échappé, sans effet) fait garder la politique de base à la page,
 * journalisée ; rien ne casse.
 */
db()->query("INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled) VALUES ('csp_temoin', '', 'Garde', 'x onclick=1', 'csp@example.test', '".date('YmdHis')."', 0)");
$intTemoin = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intTemoin}, 2, 1, 10)");
clearstatcache();
$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;
$arrRep = H::req('/admin.php?system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=modify_user&user_id='.$intTemoin.'&ploopi_env='.urlencode($strEnv));
db()->query("DELETE FROM ploopi_workspace_user WHERE id_user = {$intTemoin}");
db()->query("DELETE FROM ploopi_user WHERE id = {$intTemoin}");
$strCsp = csp_entete($arrRep, 'Content-Security-Policy');
T::contient('témoin : le nom est affiché, échappé', 'x onclick=1', $arrRep['body']);
T::vrai('politique de base gardée', $strCsp !== '' && csp_nonce($strCsp) === '' && strpos($strCsp, "'unsafe-inline'") !== false, $strCsp);
clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contient('écart journalisé, avec l\'extrait en cause', 'CSP stricte non appliquée', $strAjout);

T::section('Politique de base : autres gabarits');

db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
csp_connecter();
$arrRep = H::req('/admin.php?system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces&ploopi_env='.urlencode(csp_env()));
db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");
$strCsp = csp_entete($arrRep, 'Content-Security-Policy');
T::vrai('ploopi3 : politique de base appliquée, sans nonce', $strCsp !== '' && csp_nonce($strCsp) === '', $strCsp);
T::vrai('ploopi3 : scripts d\'une autre origine interdits', strpos($strCsp, "script-src 'self' 'unsafe-inline'") !== false, $strCsp);
T::vrai('ploopi3 : les scripts en ligne du gabarit portent quand même le nonce', (bool) preg_match('/<script type="text\/javascript" nonce="[0-9a-f]{32}">/', $arrRep['body']));

T::section('Rapport d\'une violation bloquée');

$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;
H::req('/index-quick.php?ploopi_op=ploopi_csp_report', json_encode(array('csp-report' => array('document-uri' => cfg('base_url').'/admin.php', 'violated-directive' => 'script-src-attr', 'disposition' => 'enforce'))), array('Content-Type: application/csp-report'));
clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contient('journalisé comme bloquant', 'CSP bloquante : script-src-attr', $strAjout);

T::section('Détection du script en ligne (csp::inlinescript)');

$strN = ploopi\csp::nonce();
foreach(array(
    array('page propre', '', '<a href="x" data-p4="on">Mon onglet</a><script src="a.js"></script><script nonce="'.$strN.'">var s = "<a onclick=x>";</script><script type="application/json">{"onclick":1}</script><script></script>'),
    array('gestionnaire', 'onclick', '<a href="x" onclick="go()">x</a>'),
    array('gestionnaire en majuscules, sur deux lignes', 'ONCLICK', "<a href=\"x\"\nONCLICK = \"go()\">x</a>"),
    array('URL javascript:', 'javascript:', '<a href="javascript:void(0)">x</a>'),
    array('script sans nonce', 'alert', '<script>alert(1)</script>'),
    array('script d\'un autre nonce', 'alert', '<script nonce="0123">alert(1)</script>'),
) as $arrCas)
{
    $strRes = ploopi\csp::inlinescript($arrCas[2]);
    T::vrai($arrCas[0], $arrCas[1] === '' ? $strRes === '' : stripos($strRes, $arrCas[1]) !== false, $strRes);
}

T::section('Aucun eval dans le JavaScript du socle et des modules');

$arrEval = array();
foreach(suivis(array_merge(glob(PLOOPI_ROOT.'/js/*.js'), glob(PLOOPI_ROOT.'/templates/backoffice/*/js/*.js'), glob(PLOOPI_ROOT.'/modules/system/include/*.js'), glob(PLOOPI_ROOT.'/install/*/files/include/*.js'))) as $strFichier)
{
    if (preg_match('/\.(pack|min)\.js$/', $strFichier)) continue;
    foreach(file($strFichier) as $intLigne => $strLigne)
    {
        if (preg_match('/\beval\s*\(|new\s+Function\s*\(|set(?:Timeout|Interval)\s*\(\s*["\']/', $strLigne)) $arrEval[] = str_replace(PLOOPI_ROOT.'/', '', $strFichier).':'.($intLigne + 1);
    }
}
T::vrai('ni eval, ni new Function, ni setTimeout sur une chaîne (pas de unsafe-eval)', empty($arrEval), implode(', ', $arrEval));

/**
 * Modules (§ 3.33) : composants du socle sans script en ligne, déclaration par module,
 * et contrôle permanent des modules déclarés prêts
 */
T::section('Formulaire du socle (ploopi\form) sans script en ligne');

$objForm = new ploopi\form('csp_form', 'admin.php', 'post');
$objForm->addPanel($objPanel = new ploopi\form_panel('csp_panel'));
$objPanel->addField(new ploopi\form_field('input:text', 'Titre "x" :', '', 'csp_title', 'csp_title', array('required' => true, 'datatype' => 'string')));
$objPanel->addField(new ploopi\form_field('input:text', 'Nombre :', '', 'csp_nb', 'csp_nb', array('datatype' => 'int')));
$objForm->addButton(new ploopi\form_button('input:button', 'Annuler', null, null, array('dataset' => array('ploopi-href' => 'admin.php?a=1&b="2"'))));
$strForm = $objForm->render();
T::contientpas('aucun script généré', '<script', $strForm);
T::contientpas('aucun onsubmit', 'onsubmit', $strForm);
T::contient('envoi validé par js/actions.js', 'data-ploopi-validate-submit="1"', $strForm);
preg_match('/data-ploopi-validate="([^"]*)"/', $strForm, $arrM);
$arrSpec = json_decode(html_entity_decode($arrM[1] ?? '', ENT_QUOTES, 'UTF-8'), true);
T::eq('description : fonction de validation conservée', 'csp_form_validate', $arrSpec['func'] ?? null);
$arrChamps = array();
foreach($arrSpec['panels'] ?? array() as $arrPanel) foreach($arrPanel['fields'] as $arrChamp) $arrChamps[$arrChamp['name']] = $arrChamp;
T::eq('description : champ obligatoire, libellé et format', array('name' => 'csp_title', 'label' => 'Titre "x" :', 'format' => 'string', 'hide' => 'csp_title_form'), $arrChamps['csp_title'] ?? null);
T::eq('description : champ facultatif', 'emptyint', $arrChamps['csp_nb']['format'] ?? null);
T::contient('dataset échappé', 'data-ploopi-href="admin.php?a=1&amp;b=&quot;2&quot;"', $strForm);
$objForm->addJs('var csp_test = 1;');
T::vrai('script ajouté par le module (addJs) : avec le nonce', (bool) preg_match('/<script type="text\/javascript" nonce="[0-9a-f]{32}">var csp_test = 1;<\/script>/', $objForm->render()));
$objForm2 = new ploopi\form('csp_form2', 'admin.php', 'post', array('onsubmit' => 'return ma_fonction(this);'));
$objForm2->addPanel(new ploopi\form_panel('p'));
T::contient('onsubmit fourni par un module : conservé (politique de base)', 'onsubmit="javascript:return ma_fonction(this);"', $objForm2->render());

T::section('Arborescence du socle : attributs plutôt que onclick');

$strAttrs = ploopi\skin::treeview_attrs(array('data-ploopi-action' => 'news2_select', 'data-ploopi-args' => array('a"b', 'c'), 'onclick' => 'alert(1)', 'data-X' => 'y'));
T::contient('action', 'data-ploopi-action="news2_select"', $strAttrs);
T::contient('arguments en JSON échappé', 'data-ploopi-args="[&quot;a\&quot;b&quot;,&quot;c&quot;]"', $strAttrs);
T::contientpas('nom hors data-* écarté', 'onclick', $strAttrs);
T::contientpas('nom non conforme écarté', 'data-X', $strAttrs);

T::section('Déclaration d\'un module prêt (_<MODULE>_CSP)');

define('_CSPTEMOIN_CSP', true);
define('_CSPFAUX_CSP', 1);
T::vrai('constante vraie : module prêt', ploopi\csp::moduleready('csptemoin'));
T::vrai('constante absente : non', !ploopi\csp::moduleready('cspabsent'));
T::vrai('constante non booléenne : non', !ploopi\csp::moduleready('cspfaux'));
T::vrai('nom non conforme : non', !ploopi\csp::moduleready('a-b'));

T::section('Choix d\'une date : fenêtre sans gestionnaire en ligne');

csp_connecter();
$arrRep = H::req('/admin-light.php?ploopi_op=calendar_open&inputfield_id=csp_date&selected_date='.urlencode('15/10/2026').'&ploopi_env='.urlencode(csp_env()));
T::contient('jours choisis par attribut', 'data-ploopi-calendar-day="15/10/2026"', $arrRep['body']);
T::contient('champ visé porté par le calendrier', 'data-ploopi-calendar-field="csp_date"', $arrRep['body']);
T::eq('aucun gestionnaire, aucune URL javascript:', '', ploopi\csp::inlinescript($arrRep['body']));

T::section('Calendrier (ploopi\\calendar) : événements par attributs');
/**
 * Options arrAttrs et arrCloseAttrs de calendarEvent (planning) : aucun gestionnaire en
 * ligne, noms hors data-* écartés ; script du glisser-déposer avec le nonce, valeurs en
 * JSON (une URL piégée ne ferme pas le script)
 */
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['ploopi']['template_path'])) $_SESSION['ploopi']['template_path'] = './templates/backoffice/ploopi4';
foreach (array('days' => array('strDateBegin' => '20261012', 'strDateEnd' => '20261016'), 'month' => array('intMonth' => 10, 'intYear' => 2026)) as $strVue => $arrOptions)
{
    $objCalendrier = new ploopi\calendar(800, 600, $strVue);
    $objCalendrier->setoptions($arrOptions);
    $objCalendrier->addChannel(new ploopi\calendarChannel('Canal'), 'c1');
    $objCalendrier->addEvent(new ploopi\calendarEvent('20261014100000', '20261014120000', '<time_begin>', 'contenu', 'c1', array(
        'strHref' => '#',
        'arrAttrs' => array('data-ploopi-topopup' => 'csp_popup', 'onclick' => 'alert(1)'),
        'arrCloseAttrs' => array('data-ploopi-action' => 'csp_suppr', 'data-ploopi-args' => array('a"b')),
        'arrOnDrop' => array('url' => "admin.php?x='1'</script><b id=\"evade\">", 'element_id' => 'csp_main'),
    )));
    ob_start();
    $objCalendrier->display();
    $strHtml = ob_get_clean();
    T::eq("vue {$strVue} : aucun script en ligne", '', ploopi\csp::inlinescript($strHtml));
    T::contient("vue {$strVue} : attributs de l'événement", 'data-ploopi-topopup="csp_popup"', $strHtml);
    if ($strVue == 'days')
    {
        T::contient('vue days : attributs de la croix, en JSON échappé', 'data-ploopi-args="[&quot;a\\&quot;b&quot;]"', $strHtml);
        T::contientpas('vue days : l\'URL de dépôt ne ferme pas le script', '<b id="evade">', $strHtml);
    }
}
$objCalendrier = new ploopi\calendar(800, 600, 'month');
$objCalendrier->setoptions(array('intMonth' => 10, 'intYear' => 2026));
$objCalendrier->addEvent(new ploopi\calendarEvent('20261014100000', '20261014120000', '', 'contenu', null, array('strOnClick' => 'historique();')));
ob_start();
$objCalendrier->display();
T::contient('témoin : strOnClick reste rendu (modules non migrés)', 'onclick="historique();"', ob_get_clean());

T::section('Calendrier, rendu moderne (ploopi4, backoffice)');
/**
 * Grille fluide (calendar::_displayModern) : mêmes options et mêmes événements, aucun
 * script en ligne, événements simultanés côte à côte, attributs échappés
 */
$arrSessionAvant = $_SESSION['ploopi'];
$_SESSION['ploopi']['mode'] = 'backoffice';
$_SESSION['ploopi']['template_path'] = './templates/backoffice/ploopi4';
$funcRendu = function($strVue, $arrOptions, $arrChannels, $arrEvents) {
    $objCalendrier = new ploopi\calendar(900, 700, $strVue);
    $objCalendrier->setoptions($arrOptions);
    foreach ($arrChannels as $strId => $strTitre) $objCalendrier->addChannel(new ploopi\calendarChannel($strTitre, '#3366cc'), $strId);
    foreach ($arrEvents as $objEvent) $objCalendrier->addEvent($objEvent);
    ob_start();
    $objCalendrier->display();
    return ob_get_clean();
};
$funcEvenement = function($strDebut, $strFin, $strCanal, $arrPlus = array()) {
    return new ploopi\calendarEvent($strDebut, $strFin, '<time_begin> / <time_end>', 'contenu '.$strDebut, $strCanal, array_merge(array(
        'strHref' => '#',
        'strColor' => '#e8a33d',
        'arrAttrs' => array('data-ploopi-topopup' => 'csp_popup', 'onclick' => 'alert(1)'),
    ), $arrPlus));
};

$strHtml = $funcRendu('days', array('strDateBegin' => '20261012', 'strDateEnd' => '20261018', 'intHourBegin' => 6, 'intHourEnd' => 21), array('a' => 'Salle A', 'b' => 'Salle B'), array(
    $funcEvenement('20261014100000', '20261014120000', 'a', array('arrCloseAttrs' => array('data-ploopi-action' => 'csp_suppr'), 'arrOnDrop' => array('url' => "admin.php?x='1'\"><b id=\"evade\">", 'element_id' => 'csp_main'))),
    $funcEvenement('20261014110000', '20261014150000', 'a'),
    $funcEvenement('20261014090000', '20261014100000', 'b'),
));
T::eq('jours : aucun script en ligne', '', ploopi\csp::inlinescript($strHtml));
T::contient('jours : grille fluide', 'class="p4-cal p4-cal-days"', $strHtml);
T::eq('jours : une colonne par jour', 7, preg_match_all('/class="p4-cal-day(?: [^"]*)?" data-calendar-date=/', $strHtml));
T::contient('jours : colonne datée pour le dépôt', 'data-calendar-date="20261014"', $strHtml);
T::contient('jours : en-têtes des canaux', '>Salle B</span>', $strHtml);
T::contient('jours : attributs de l\'événement', 'data-ploopi-topopup="csp_popup"', $strHtml);
T::contientpas('jours : nom hors data-* écarté', 'alert(1)', $strHtml);
T::contient('jours : croix par attributs', 'data-ploopi-action="csp_suppr"', $strHtml);
T::contientpas('jours : l\'URL de dépôt reste dans son attribut', '<b id="evade">', $strHtml);
T::contient('jours : URL de dépôt échappée', 'data-calendar-drop="admin.php?x=&#039;1&#039;&quot;&gt;&lt;b', $strHtml);
preg_match_all('/class="p4-cal-ev[^"]*" style="top:[0-9.]+%;height:[0-9.]+%;left:([0-9.]+)%/', $strHtml, $arrLefts);
T::vrai('jours : deux événements simultanés côte à côte (cascade)', count(array_unique($arrLefts[1])) >= 2, implode(' / ', $arrLefts[1]));
T::contient('jours : ouverture une demi-heure avant 8 h (premier événement à 9 h)', 'data-calendar-scroll="7.5"', $strHtml);

$strHtml = $funcRendu('month', array('intMonth' => 10, 'intYear' => 2026), array(), array($funcEvenement('20261014100000', '20261016120000', '')));
T::eq('mois : aucun script en ligne', '', ploopi\csp::inlinescript($strHtml));
T::contient('mois : grille', 'class="p4-cal p4-cal-month has-weeks"', $strHtml);
T::eq('mois : un événement sur trois jours, trois pastilles', 3, substr_count($strHtml, 'class="p4-cal-chip"'));
T::contient('mois : jours du mois voisin repérés', 'is-out', $strHtml);

$_SESSION['ploopi']['mode'] = 'frontoffice';
$strHtml = $funcRendu('month', array('intMonth' => 10, 'intYear' => 2026), array(), array());
T::contientpas('témoin : le frontoffice garde le rendu historique', 'p4-cal', $strHtml);
$_SESSION['ploopi'] = $arrSessionAvant;

T::section('Composants du socle insérés par les modules : aucun script en ligne');
/**
 * Partages, validations, abonnements, GED, calendrier, formulaires : ce qu'un module
 * prêt insère dans ses écrans (les options on* de ploopi\form restent, pour l'existant)
 */
foreach(array('include/classes/share.php', 'include/op/share.php', 'include/classes/validation.php', 'include/op/validation.php', 'include/classes/subscription.php', 'include/op/subscription.php', 'include/classes/documents.php', 'include/classes/date.php') as $strFichier)
{
    $arrEcarts = array();
    foreach(file(PLOOPI_ROOT.'/'.$strFichier) as $intLigne => $strLigne)
    {
        if (preg_match('/^\s*(\*|\/\/|#)/', $strLigne)) continue;
        if (preg_match('/(?<![\$\w])on[a-z]+\s*=\s*\\\\?["\']|javascript:|<script\b(?![^>]*\b(nonce|src)\b)/i', $strLigne)) $arrEcarts[] = $intLigne + 1;
    }
    T::vrai("{$strFichier} : aucun script en ligne", empty($arrEcarts), 'lignes '.implode(', ', $arrEcarts));
}

T::section('Modules déclarés prêts : aucun script en ligne dans leurs sources');
/**
 * Contrôle permanent : un module qui définit _<MODULE>_CSP à true dans son
 * include/global.php ne doit porter, dans ses sources PHP et gabarits, ni gestionnaire
 * on*=, ni URL javascript:, ni <script> en ligne sans nonce, ni option 'on*' => des
 * classes du socle (ploopi\form, display_array), qui produirait un gestionnaire
 */
$arrPrets = array();
foreach(suivis(glob(PLOOPI_ROOT.'/install/*/files/include/global.php')) as $strGlobal)
{
    if (preg_match("/define\s*\(\s*'_[A-Z0-9_]+_CSP'\s*,\s*true\s*\)/", file_get_contents($strGlobal))) $arrPrets[] = basename(dirname(dirname(dirname($strGlobal))));
}
T::vrai('news2, nanogallery, wiki, dbreport, planning et booking déclarés prêts', !array_diff(array('news2', 'nanogallery', 'wiki', 'dbreport', 'planning', 'booking'), $arrPrets), implode(', ', $arrPrets));
foreach($arrPrets as $strModule)
{
    $arrEcarts = array();
    $objIt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PLOOPI_ROOT."/install/{$strModule}/files", FilesystemIterator::SKIP_DOTS));
    foreach($objIt as $objFichier)
    {
        if (!preg_match('/\.(php|tpl)$/', $objFichier->getFilename()) || !suivi($objFichier->getPathname())) continue;
        foreach(file($objFichier->getPathname()) as $intLigne => $strLigne)
        {
            if (preg_match('/^\s*(\*|\/\/|#)/', $strLigne)) continue;
            if (preg_match('/(?<![\$\w])on[a-z]+\s*=\s*\\\\?["\']|javascript:|<script\b(?![^>]*\b(nonce|src)\b)(?![^>]*application\/json)|[\'"]on(click|dblclick|change|input|submit|reset|focus|blur|load|error|select|key[a-z]+|mouse[a-z]+|touch[a-z]+|drag[a-z]*|drop|wheel|scroll|contextmenu)[\'"]\s*=>/i', $strLigne)) $arrEcarts[] = str_replace(PLOOPI_ROOT."/install/{$strModule}/files/", '', $objFichier->getPathname()).':'.($intLigne + 1);
        }
    }
    T::vrai("{$strModule} : aucun script en ligne", empty($arrEcarts), implode(', ', array_slice($arrEcarts, 0, 8)));
}

exit(T::bilan());
