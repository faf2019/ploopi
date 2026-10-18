<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Suite 37 — gabarit ploopi4 : contrat avec le socle et les modules, icônes,
 * feuilles de style, extensions du socle (menu, préférences d'affichage).
 * Ne demande que PHP en ligne de commande.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();

$strGabarit = PLOOPI_ROOT.'/templates/backoffice/ploopi4';
$strIndex = file_get_contents("{$strGabarit}/index.tpl");
$strLight = file_get_contents("{$strGabarit}/light.tpl");

/**
 * Le contrat que les modules et le socle attendent d'un gabarit du backoffice
 * (doc/habillage.md) : identifiants, conteneur défilant des fenêtres, formulaire
 * de connexion.
 */
T::section('contrat avec le socle et les modules');

foreach(array(
    'id="background"' => 'enveloppe de l\'application (#background)',
    'id="pagecontent" class="hook"' => 'conteneur défilant des fenêtres (#pagecontent.hook)',
    'id="ploopi_mod_mess"' => 'messages des modules (#ploopi_mod_mess)',
    'id="tpl_ploopi_tickets_new"' => 'compteur de messages (#tpl_ploopi_tickets_new)',
    'id="login_form"' => 'formulaire de connexion (#login_form)',
    'id="ploopi_login"' => 'identifiant (#ploopi_login)',
    'id="ploopi_password"' => 'mot de passe (#ploopi_password)',
    'id="ploopi_password_new"' => 'mot de passe expiré (#ploopi_password_new)',
    'id="ploopi_password_new_confirm"' => 'confirmation (#ploopi_password_new_confirm)',
    'id="checkpass"' => 'jauge de robustesse (#checkpass)',
    'id="lostpassword_form"' => 'mot de passe perdu (#lostpassword_form)',
) as $strAiguille => $strLabel) T::contient($strLabel, $strAiguille, $strIndex);

foreach(array('{PAGE_CONTENT}', '{ADDITIONAL_JAVASCRIPT}', '{ADDITIONAL_HEAD}', '<!-- BEGIN module_css -->', '<!-- BEGIN ploopi_js -->', '<!-- BEGIN module_js -->') as $strAiguille)
{
    T::contient("index.tpl : {$strAiguille}", $strAiguille, $strIndex);
    T::contient("light.tpl : {$strAiguille}", $strAiguille, $strLight);
}

foreach(array('img/template/close_popup.png', 'img/system/btn_edit.png', 'img/arrays/arrows.png', 'img/export/xls.png', 'img/treeview/group.png') as $strImage)
{
    T::vrai("image attendue par le socle et les modules : {$strImage}", file_exists("{$strGabarit}/{$strImage}"));
}

/**
 * Prêt pour une CSP effective (lot 11) : ni gestionnaire d'évènement, ni style, ni
 * script en ligne dans le gabarit, hors du JavaScript que le socle y injecte.
 */
T::section('rien en ligne dans le gabarit');

foreach(array('index.tpl' => $strIndex, 'light.tpl' => $strLight) as $strFichier => $strTpl)
{
    T::vrai("{$strFichier} : aucun attribut on…=", !preg_match('/\son[a-z]+\s*=/i', $strTpl));
    T::vrai("{$strFichier} : aucun attribut style=", !preg_match('/\sstyle\s*=/i', $strTpl));
    preg_match_all('@<script(?![^>]*\ssrc=)[^>]*>(.*?)</script>@s', $strTpl, $arrScripts);
    T::eq("{$strFichier} : seul script en ligne, celui du socle", array('{ADDITIONAL_JAVASCRIPT}'), array_map('trim', $arrScripts[1]));
}

/**
 * Icônes : un seul jeu (Tabler), dans le sprite généré par tools/ploopi4_icones.php.
 * Toute icône demandée par le gabarit, le shell ou les écrans du module system doit
 * y figurer : une icône absente ne s'afficherait pas, sans erreur.
 */
T::section('sprite d\'icônes');

exec('php '.escapeshellarg(PLOOPI_ROOT.'/tools/ploopi4_icones.php').' --check 2>&1', $arrSortie, $intRetour);
T::eq('img/icons.svg à jour (tools/ploopi4_icones.php --check)', 0, $intRetour);

$strSprite = file_get_contents("{$strGabarit}/img/icons.svg");
preg_match_all('/<symbol id="i-([a-z0-9-]+)"/', $strSprite, $arrMatches);
$arrSprite = array_flip($arrMatches[1]);

$arrDemandes = array();
$funcAjouter = function($strFichier, $arrNoms) use (&$arrDemandes) {
    foreach($arrNoms as $strNom) $arrDemandes[$strNom][] = basename($strFichier);
};

// Gabarit et scripts : références au sprite et appels de shell.icon()
foreach(array("{$strGabarit}/index.tpl", "{$strGabarit}/js/shell.js", PLOOPI_ROOT.'/modules/system/include/workspaces.js') as $strFichier)
{
    $strCode = file_get_contents($strFichier);
    preg_match_all('/#i-([a-z0-9-]+)/', $strCode, $arrM1);
    preg_match_all('/icon\(\'([a-z0-9-]+)\'/', $strCode, $arrM2);
    $funcAjouter($strFichier, array_merge($arrM1[1], $arrM2[1]));
}

// Icônes des types de module (skin.ini)
foreach(parse_ini_file("{$strGabarit}/skin.ini") as $strCle => $strValeur)
{
    if (strpos($strCle, 'icon_') === 0) $funcAjouter('skin.ini', array($strValeur));
}

// Écrans et menus du module system : appels de icon(), icône passée à addmenu() après
// la cible, tableaux (libellé, icône), choix d'icône selon l'entité ($strIcon), icône des
// composants de ploopi\system\ui (état vide, carte, section) et de system_open_bloc()
$strArg = '(?:[^,()]|\\([^()]*\\))*';
$arrMenus = array_merge(glob(PLOOPI_ROOT.'/modules/system/admin_workspaces*.php'), array(PLOOPI_ROOT.'/modules/system/block.php', PLOOPI_ROOT.'/modules/system/block_public.php'));
foreach(array_merge(glob(PLOOPI_ROOT.'/modules/system/*.php'), array(PLOOPI_ROOT.'/modules/system/include/functions.php', PLOOPI_ROOT.'/modules/system/classes/ui.php')) as $strFichier)
{
    $strCode = file_get_contents($strFichier);
    $arrNoms = array();
    $arrMotifs = array(
        '/icon\\(\'([a-z0-9-]+)\'/',
        '/emptystate\\(\'([a-z][a-z0-9-]*)\'/',
        "/(?:card_open|system_open_bloc)\\({$strArg}, '([a-z][a-z0-9-]*)'/",
        "/section_open\\({$strArg}, {$strArg}, '([a-z][a-z0-9-]*)'/",
        '/_DESC, \'([a-z][a-z0-9-]*)\'(?!\\s*=>)/',
        '/urlencode\\(\'[^\']*\'\\), \'([a-z][a-z0-9-]*)\'\\)/',
        '/\'icon\' => \'([a-z][a-z0-9-]*)\'/',
    );
    // Menus et écrans des espaces : addmenu(), tableaux (libellé, icône), onglets
    if (in_array($strFichier, $arrMenus)) $arrMotifs = array_merge($arrMotifs, array(
        '/addmenu\\([^;]*?\'\',\\s*\'([a-z][a-z0-9-]*)\'/s',
        '/array\\(_SYSTEM_[A-Z_]+, \'((?!p4-)[a-z][a-z0-9-]*)\'\\)/',
        '/=> array\\(\'[^\']+\', \'([a-z][a-z0-9-]*)\'\\)/',
        '/array\\(\\$tabs\\[\'\\w+\'\\]\\[\'url\'\\], \'([a-z][a-z0-9-]*)\'/',
    ));
    foreach($arrMotifs as $strMotif)
    {
        preg_match_all($strMotif, $strCode, $arrM);
        $arrNoms = array_merge($arrNoms, $arrM[1]);
    }
    // $strIcon = 'x' ; $strIcon = … ? 'x' : 'y' (hors paramètres d'une fonction)
    preg_match_all('/(?<![(,] )(?<!\\()\\$strIcon = [^;]*;/', $strCode, $arrLignes);
    foreach($arrLignes[0] as $strLigne)
    {
        preg_match_all('/(?:(?<![=!])= |[?:] )\'([a-z][a-z0-9-]*)\'/', $strLigne, $arrM);
        $arrNoms = array_merge($arrNoms, $arrM[1]);
    }
    $funcAjouter($strFichier, $arrNoms);
}

$arrAbsentes = array();
foreach($arrDemandes as $strNom => $arrFichiers)
{
    if (!isset($arrSprite[$strNom])) $arrAbsentes[] = "{$strNom} (".implode(', ', array_unique($arrFichiers)).')';
}
T::vrai('icônes demandées trouvées dans le sprite ('.sizeof($arrDemandes).' noms)', empty($arrAbsentes), implode(', ', $arrAbsentes));
T::vrai('relevé des icônes non vide (contrôle effectif)', sizeof($arrDemandes) > 40, sizeof($arrDemandes).' noms');

/**
 * Feuilles de style : l'agrégat servi est la concaténation des sources (compress.sh) ;
 * les règles d'éléments de la zone historique laissent l'interface de ploopi4 à son
 * propre dessin.
 */
T::section('feuilles de style');

$strAgregat = '';
foreach(glob("{$strGabarit}/css/base_*.css") as $strFichier) $strAgregat .= file_get_contents($strFichier);
T::vrai('css/styles.pack.css = base_*.css concaténés (tools/compress.sh)', $strAgregat === file_get_contents("{$strGabarit}/css/styles.pack.css"));

exec('php '.escapeshellarg(PLOOPI_ROOT.'/tools/ploopi3_modules.php').' --check 2>&1', $arrSortie, $intRetour);
T::eq('base_5_modules.css = palette des modules générée (tools/ploopi3_modules.php --check)', 0, $intRetour);

$strSkin = file_get_contents("{$strGabarit}/css/base_5_skin.css");
T::vrai('base_5_skin.css : la section « CHAMPS ET BOUTONS » de ploopi3 est retirée', strpos($strSkin, 'CHAMPS ET BOUTONS') === false && !preg_match('/^(select|input|textarea|button),?\s*$/m', $strSkin));

/**
 * Composant historique posé dans une carte refondue (GED de la fiche utilisateur,
 * annotations d'un espace) : son enveloppe .p4-legacy le rend à la zone historique,
 * sans quoi ses boutons gardaient le dessin du navigateur (glyphe blanc sur gris).
 */
$strFiltre = ':where(:not(.p4-ui *), .p4-legacy :not(.p4-legacy .p4-ui *))';
$strHistorique = str_replace($strFiltre, '§FILTRE§', file_get_contents("{$strGabarit}/css/base_6_historique.css"));
preg_match_all('/^((?:select|input|textarea|button)[^{,]*)[,{]/m', $strHistorique, $arrRegles);
$arrNonGardees = array_filter($arrRegles[1], function($strSel) { return strpos($strSel, '§FILTRE§') === false && !preg_match('/^button\.(icon|small_icon|invert)/', trim($strSel)); });
T::vrai('base_6_historique.css : chaque règle d\'élément exclut .p4-ui, sauf dans .p4-legacy ('.sizeof($arrRegles[1]).' sélecteurs)', empty($arrNonGardees) && sizeof($arrRegles[1]) > 10, implode(' | ', $arrNonGardees));

$strJetons = file_get_contents("{$strGabarit}/css/base_0_jetons.css");
foreach(array('--p3-primary', '--p3-surface', '--p3-text', '--p3-border', '--p3-font-size') as $strVariable)
{
    T::contient("palette de ploopi3 redéfinie : {$strVariable}", "{$strVariable}:", $strJetons);
}
T::contient('thème sombre explicite', ':root[data-theme="dark"]', $strJetons);
T::contient('thème sombre du système (réglage « auto »)', ':root[data-theme="auto"]', $strJetons);

/**
 * Le contenu des modules suit le thème sombre : il n'est plus forcé en clair, et les
 * couleurs écrites en dur sont reprises par les jetons (teintes, styles en ligne).
 */
$strShell = file_get_contents("{$strGabarit}/css/base_3_shell.css");
T::contientpas('contenu des modules : plus de jetons clairs forcés', 'data-p4-module="system"', $strJetons);
T::contientpas('contenu des modules : plus de color-scheme clair forcé', 'data-p4-module="system"', $strShell);
T::contient('thème sombre : titres des écrans historiques lisibles', '--p3-primary-dark: var(--primary-text)', $strJetons);
foreach(array('annotation', 'subscription', 'validation', 'share', 'calendar') as $strFeuille)
{
    $strCss = file_get_contents("{$strGabarit}/css/base_5_{$strFeuille}.css");
    // Sélecteurs d'attribut ([style*="background-color:#ffffff"]) exceptés : ils lisent un style, n'en posent pas
    T::vrai("base_5_{$strFeuille}.css : aucun fond écrit en dur", !preg_match('/background(-color)?\s*:\s*#/i', preg_replace('/\[style[^\]]*\]/i', '', $strCss)));
}
$strEnLigne = file_get_contents("{$strGabarit}/css/base_9_enligne.css");
T::contient('styles.pack.css agrège base_9_enligne.css', $strEnLigne, file_get_contents("{$strGabarit}/css/styles.pack.css"));
T::vrai('base_9_enligne.css : accolades équilibrées', substr_count($strEnLigne, '{') == substr_count($strEnLigne, '}'));
// Couleurs de texte en ligne des modules (« color:#… » sans espace) : chacune est reprise
$arrCouleurs = array();
foreach(suivis(glob(PLOOPI_ROOT.'/install/*/files/{,*/,*/*/}*.php', GLOB_BRACE)) as $strFichier)
{
    if (preg_match_all('/(?<![-\w])color:(#[0-9a-f]{3,6})\b/i', file_get_contents($strFichier), $arrM)) $arrCouleurs = array_merge($arrCouleurs, array_map('strtolower', $arrM[1]));
}
$arrCouleurs = array_unique($arrCouleurs);
$arrOubliees = array_filter($arrCouleurs, function($strC) use ($strEnLigne) { return stripos($strEnLigne, "\"color:{$strC}\"") === false; });
T::vrai('base_9_enligne.css reprend chaque couleur de texte en ligne des modules ('.sizeof($arrCouleurs).')', sizeof($arrCouleurs) > 3 && empty($arrOubliees), implode(' ', $arrOubliees));
$arrSkinIni = parse_ini_file("{$strGabarit}/skin.ini");
T::vrai('skin.ini : lignes alternées et couleurs en jetons', $arrSkinIni['bgline1'] === 'var(--surface)' && $arrSkinIni['bgline2'] === 'var(--surface-2)' && strpos($arrSkinIni['colprim'], 'var(') === 0);

/**
 * Socle : options de menu (icône, section), génération du gabarit, préférences
 */
T::section('ploopi\\block : icône et section facultatives');

$objBlock = new ploopi\block();
$objBlock->addmenu('Libellé', 'admin.php', true);
$objBlock->addmenu('Autre', 'admin.php', false, '', 'Users-Group"><script>', 'Organisation');
$arrMenu = $objBlock->getmenu();
T::eq('sans icône : champ vide (rétrocompatible)', '', $arrMenu[0]['icon']);
T::eq('icône réduite à [a-z0-9-]', 'users-groupscript', $arrMenu[1]['icon']);
T::eq('section conservée', 'Organisation', $arrMenu[1]['section']);

T::section('ploopi\\skin : génération, icônes de module, préférences');

if (!isset($_SESSION)) $_SESSION = array();
$_SESSION['ploopi']['mode'] = 'backoffice';

$objSkin3 = new ploopi\skin('ploopi3');
T::eq('ploopi3 : génération 0 (écrans historiques)', 0, $objSkin3->version());
T::eq('ploopi3 : pas d\'icône vectorielle', '', $objSkin3->icon('users'));
T::eq('ploopi3 : pas d\'icône de module', '', $objSkin3->moduleicon('doc'));

$objSkin4 = new ploopi\skin('ploopi4');
T::eq('ploopi4 : génération 4', 4, $objSkin4->version());
T::eq('ploopi4 : icône du type de module doc', 'folders', $objSkin4->moduleicon('doc'));
T::eq('ploopi4 : icône par défaut d\'un type inconnu', 'apps', $objSkin4->moduleicon('inconnu'));

include_once "{$strGabarit}/class_skin.php";
$objSkinP4 = new \skin();
T::contient('ploopi4 : icon() référence le sprite', 'img/icons.svg#i-users"', $objSkinP4->icon('users'));
T::contient('ploopi4 : icon() nettoie le nom', '#i-xscript"', $objSkinP4->icon('x"><script>'));

$_SESSION['ploopi']['connected'] = 1;
$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_theme'] = 'dark';
$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_density'] = 'compact';
T::eq('préférences de l\'utilisateur connecté', array('dark', 'compact'), ploopi\skin::getdisplayprefs());
$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_theme'] = 'rose" onload="x';
$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_density'] = '';
T::eq('valeur inconnue : réglages par défaut', array('auto', 'comfortable'), ploopi\skin::getdisplayprefs());
$_SESSION['ploopi']['connected'] = 0;
$_COOKIE['ploopi_theme'] = 'light';
T::eq('déconnecté : thème choisi sur ce navigateur', array('light', 'comfortable'), ploopi\skin::getdisplayprefs());
$_COOKIE['ploopi_theme'] = '"><script>';
T::eq('déconnecté : cookie invalide ignoré', array('auto', 'comfortable'), ploopi\skin::getdisplayprefs());

/**
 * Tableaux avancés (skin::display_array) : sous ploopi4, un <table> unique trié et
 * paginé dans le navigateur (js/skin.js), sans variable sérialisée ; sous ploopi3, le
 * rendu historique (flottants, rafraîchissement AJAX)
 */
T::section('skin::display_array : tableau fluide sous ploopi4');

include_once PLOOPI_ROOT.'/lang/french.php';

$funcRendu = function($objSkin, $arrCols, $arrValues, $strId, $arrOptions) {
    $arrErreurs = array();
    set_error_handler(function($intNo, $strMsg) use (&$arrErreurs) { $arrErreurs[] = $strMsg; return true; });
    ob_start();
    $objSkin->display_array($arrCols, $arrValues, $strId, $arrOptions);
    $strHtml = ob_get_clean();
    restore_error_handler();
    return array($strHtml, $arrErreurs);
};

$arrCols = array();
$arrCols['actions_left']['coche'] = array('label' => 'Sél.', 'width' => 30, 'options' => array('sort' => true));
$arrCols['left']['nom'] = array('label' => 'Nom', 'width' => 200, 'options' => array('sort' => true));
$arrCols['auto']['desc'] = array('label' => '<b>Description</b>');
$arrCols['right']['date'] = array('label' => 'Date', 'width' => 100, 'options' => array('sort' => true));
$arrCols['right']['nb'] = array('label' => 'Nombre', 'width' => 60, 'options' => array('sort' => true, 'sort_flag' => SORT_NUMERIC));
$arrCols['actions_right']['modifier'] = array('label' => '', 'width' => 30);
$arrCols['actions_right']['supprimer'] = array('label' => '', 'width' => 30);

$arrValues = array();
foreach(array('Éric' => 10, 'alain' => 9, 'Zoé' => 100) as $strNom => $intNb)
{
    $arrValues[] = array(
        'values' => array(
            'coche' => array('label' => '<input type="checkbox" />'),
            'nom' => array('label' => ploopi\str::htmlentities($strNom)),
            'desc' => array('label' => "<i>{$strNom}</i>"),
            'date' => array('label' => '01/02/2026', 'sort_label' => '20260201'),
            'nb' => array('label' => $intNb),
            'supprimer' => array('label' => '<a href="#">x</a>'),
        ),
        'link' => 'admin.php?ploopi_url=abc',
        'description' => 'Ouvrir <b>"'.$strNom.'"</b><script>',
    );
}
// Ligne sans la colonne de tri : clé vide, sans avertissement
$arrValues[] = array('values' => array('desc' => array('label' => 'sans nom')));

list($strHtml, $arrErreurs) = $funcRendu($objSkinP4, $arrCols, $arrValues, 'p4arr_test', array('sortable' => true, 'orderby_default' => 'nb', 'sort_default' => 'DESC', 'limit' => 2));

T::eq('aucun avertissement (ligne sans la colonne de tri)', array(), $arrErreurs);
T::contient('conteneur : même id, tri et page initiaux, mémorisation', 'id="ploopi_explorer_main_p4arr_test" data-p4-arr="p4arr_test" data-p4-arr-orderby="nb" data-p4-arr-sort="DESC" data-p4-arr-limit="2" data-p4-arr-page="1" data-p4-arr-persist="1"', $strHtml);
T::contient('un seul <table> aux styles de ploopi4', '<table class="p4-table p4-arr"><thead class="p4-ui">', $strHtml);
T::contientpas('aucun élément historique (flottants)', 'ploopi_explorer_title', $strHtml);
T::contientpas('aucun script en ligne', '<script', preg_replace('/title="[^"]*"/', '', $strHtml));

preg_match('@<thead[^>]*>(.*?)</thead>@s', $strHtml, $arrThead);
preg_match_all('@<th\b@', $arrThead[1], $arrTh);
preg_match_all('@data-p4-arr-sort="([a-z]+)"|<th class="p4-arr-acts" scope="col"><span class="p4-sr-only">|<th scope="col">(<b>Description</b>)@', $arrThead[1], $arrOrdre);
$arrOrdreVu = array_map(function($a, $b) { return $a != '' ? $a : ($b != '' ? 'desc' : 'actions'); }, $arrOrdre[1], $arrOrdre[2]);
T::eq('colonnes dans l\'ordre visuel historique (droite et actions de droite inversées)', array('coche', 'nom', 'desc', 'nb', 'date', 'actions', 'actions'), $arrOrdreVu);
T::eq('une seule colonne par définition (actions_left triable : plus de colonne fantôme)', 7, sizeof($arrTh[0]));
T::contient('colonne actions_left triable', 'data-p4-arr-sort="coche"', $strHtml);
T::contient('colonne en tri : aria-sort et icône de sens', 'aria-sort="descending" scope="col"><button type="button" class="p4-sort" data-p4-arr-sort="nb" data-p4-arr-num>Nombre', $strHtml);
T::contient('icône de sens dans le sprite', '#i-chevron-down"', $strHtml);
T::contient('colonne triable : icône de tri', '#i-arrows-sort"', $strHtml);
T::contient('libellé de colonne HTML gardé tel quel', '<th scope="col"><b>Description</b></th>', $strHtml);
T::contient('largeur de colonne reportée sur l\'en-tête', '<th style="width:200px;" scope="col">', $strHtml);

T::contient('clé de tri : sort_label', 'data-p4-arr-key="20260201"', $strHtml);
T::contient('clé de tri : majuscules sans accents, comme l\'affichage historique', 'data-p4-arr-key="ERIC"', $strHtml);
T::contient('clé de tri : texte affiché (entités décodées, sans balises)', 'data-p4-arr-key="ZOE"', $strHtml);
T::contient('libellé de colonne sur chaque cellule (affichage en cartes)', 'data-label="Description"', $strHtml);

preg_match_all('@data-p4-arr-i="(\d)"( hidden)?@', $strHtml, $arrLignes);
T::eq('tri initial fait par le serveur (Nombre décroissant, numérique)', array('2', '0', '1', '3'), $arrLignes[1]);
T::eq('pagination initiale : lignes hors page masquées', array('', '', ' hidden', ' hidden'), $arrLignes[2]);
T::contient('pied : lignes affichées', '1–2 sur 4', $strHtml);
T::contient('pied : pagination construite par le navigateur', 'data-p4-arr-pager', $strHtml);

T::contient('lien de ligne dans la cellule titre (colonne auto)', '<td class="p4-arr-title" data-label="Description"><a class="p4-arr-rowlink" href="admin.php?ploopi_url=abc" title="Ouvrir &quot;Zo&eacute;&quot;">', $strHtml);
T::contientpas('description du lien : ni balise ni script', 'Ouvrir <b>', $strHtml);
T::contient('cellule d\'actions marquée (pas de retour à la ligne des icônes)', '<td class="p4-arr-acts"><a href="#">x</a></td>', $strHtml);
T::contient('barre de tri de l\'affichage en cartes', '<option value="nb" selected>Nombre</option>', $strHtml);

$objSV = new ploopi\serializedvar('p4arr_test');
T::vrai('ploopi4 : aucune variable sérialisée (plus d\'aller-retour serveur)', $objSV->read() === false);

list($strHtml) = $funcRendu($objSkinP4, $arrCols, $arrVide = array(), 'p4arr_vide', array('sortable' => true));
T::contient('tableau vide : état vide', '<div class="p4-ui p4-empty">', $strHtml);
T::contient('tableau vide : libellé', ploopi\str::htmlentities(_PLOOPI_ARRAY_EMPTY), $strHtml);

// Cellule titre déjà interactive : ancre vide à côté du contenu
$arrColsLien = array('left' => array('nom' => array('label' => 'Nom', 'width' => 100)));
$arrValuesLien = array(array('values' => array('nom' => array('label' => '<a href="autre.php">Nom</a>')), 'link' => 'admin.php', 'onclick' => 'x();'));
list($strHtml) = $funcRendu($objSkinP4, $arrColsLien, $arrValuesLien, '', array());
T::contient('cellule interactive : ancre de ligne vide avec libellé accessible', '<a class="p4-arr-rowlink" href="admin.php" onclick="x();" aria-label="Nom"></a><a href="autre.php">Nom</a>', $strHtml);
T::contientpas('tableau anonyme : ni tri ni page mémorisés', 'data-p4-arr-persist', $strHtml);

list($strHtml) = $funcRendu($objSkin3, $arrCols, $arrValues, 'p4arr_test3', array('sortable' => true, 'limit' => 2));
T::contient('ploopi3 : rendu historique (flottants)', 'id="ploopi_explorer_title_p4arr_test3"', $strHtml);
T::contientpas('ploopi3 : pas de tableau fluide', 'data-p4-arr', $strHtml);
T::contient('ploopi3 : pagination historique (AJAX)', 'class="ploopi_array_page"', $strHtml);
$objSV = new ploopi\serializedvar('p4arr_test3');
T::vrai('ploopi3 : variable sérialisée pour le rafraîchissement AJAX', $objSV->read() !== false);

$strSkinJs = file_get_contents(PLOOPI_ROOT.'/js/skin.js');
T::contient('js/skin.js : tableaux insérés après le chargement (AJAX, fenêtres)', 'MutationObserver', $strSkinJs);
T::contient('js/skin.js : tri et page mémorisés pour la session de l\'onglet', 'sessionStorage', $strSkinJs);
T::contient('js/skin.js : array_refresh rejoué dans le navigateur sous ploopi4', "main.hasAttribute('data-p4-arr')", $strSkinJs);
/**
 * Le conteneur du tableau porte lui-même l'état initial (data-p4-arr-sort, -page) : le
 * gestionnaire de clic, en cherchant ces attributs sur les ancêtres, le trouvait pour
 * tout clic dans le tableau et annulait la navigation — liens et icônes d'action morts
 * (« Modifier le formulaire » de forms, relevé sous Docker). Seuls les boutons de tri,
 * de page et de sens sont visés.
 */
preg_match("@document\.addEventListener\('click', function\(e\) \{(?:\s*//[^\n]*)*\s*var el = e\.target\.closest \? e\.target\.closest\('([^']+)'\)@", $strSkinJs, $arrSel);
$arrCibles = array_map('trim', explode(',', $arrSel[1] ?? ''));
T::vrai('js/skin.js : gestionnaire du tri et de la pagination trouvé', count($arrCibles) === 3, $arrSel[1] ?? 'introuvable');
T::vrai('js/skin.js : il ne vise que des boutons, jamais le conteneur du tableau', count(array_filter($arrCibles, function($s) { return strpos($s, 'button[') !== 0; })) === 0, implode(' | ', $arrCibles));
T::contient('feuille des tableaux fluides : affichage en cartes', '@container p4arr (max-width: 639px)', file_get_contents("{$strGabarit}/css/base_9_tableaux.css"));

/**
 * Paramètres system_theme et system_density : installation neuve et mise à jour
 */
T::section('paramètres d\'affichage (base de données)');

$strMaj = PLOOPI_ROOT.'/install/system/update_ploopi_'._PLOOPI_VERSION.'.sql';
T::vrai('script de mise à jour de la version courante présent', file_exists($strMaj), basename($strMaj));
// Les paramètres d'affichage sont apparus en 1.10.3.0 : c'est ce script qui les crée en mise à jour.
$strMaj = PLOOPI_ROOT.'/install/system/update_ploopi_1.10.3.0.sql';
$strPloopiSql = file_get_contents(PLOOPI_ROOT.'/install/system/ploopi.sql');
foreach(array('system_theme', 'system_density') as $strParam)
{
    T::contient("{$strParam} : type de paramètre (installation)", "(1, '{$strParam}',", $strPloopiSql);
    T::contient("{$strParam} : type de paramètre (mise à jour)", "(1, '{$strParam}',", file_exists($strMaj) ? file_get_contents($strMaj) : '');
}
T::contient('ploopi.sql porte la version courante', "SET `version` = '"._PLOOPI_VERSION."'", $strPloopiSql);

/**
 * Écrans des modules : fenêtres de ploopi4 (class_skin.php), positionnement des
 * fenêtres ouvertes par open_popup (js/popup.js), feuille propre aux modules
 */
T::section('écrans des modules');

if (!isset($_SESSION['ploopi']['mode'])) $_SESSION['ploopi']['mode'] = 'backoffice';
if (!isset($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup'])) $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup'] = 0;

if (!class_exists('skin', false)) include_once "{$strGabarit}/class_skin.php";
$objSkin4 = new skin();

$strPopup = $objSkin4->create_popup('Titre <b>gras</b>', '<p>contenu</p>', 'p4t_popup');
T::contient('create_popup : bouton de fermeture (écouteur délégué)', '<button type="button" class="ploopi_popup_close p4-popup-close" id="close_p4t_popup" data-popup="p4t_popup"', $strPopup);
T::contient('create_popup : nom accessible du bouton', 'aria-label="Fermer"', $strPopup);
T::contient('create_popup : icône du sprite', 'icons.svg#i-x', $strPopup);
T::contient('create_popup : titre déplaçable (poignée handle_<id>)', '<div class="p4-popup-title handle_p4t_popup">Titre <b>gras</b></div>', $strPopup);
// Déplacement par js/popup.js (ploopi.popup.enhance), sans script renvoyé avec la fenêtre (CSP stricte, § 3.32)
T::contient('create_popup : fenêtre repérée pour js/popup.js', 'data-p4-popup="p4t_popup"', $strPopup);
T::contientpas('create_popup : aucun script en ligne', '<script', $strPopup);
T::contient('js/popup.js : fenêtres insérées rendues déplaçables', "draggable({ zindex: 99999, handle: '.handle_'+id })", file_get_contents(PLOOPI_ROOT.'/js/popup.js'));
T::contient('create_popup : contenu', '<div class="simplebloc_content"><p>contenu</p></div>', $strPopup);
T::vrai('create_popup : plus d\'image de fermeture', strpos($strPopup, 'close_popup.png') === false);

$strPopup = $objSkin4->open_popup('Ressources', '<p>x</p>', 'p4t_open', array('intWidth' => 220, 'booCentered' => false, 'booClosable' => false, 'intPosx' => 10, 'intPosy' => 20));
T::contient('open_popup : conteneur masqué', '<div id="p4t_open" style="display:none;">', $strPopup);
T::contient('open_popup : ouverture par ploopi.popup.ize', "ploopi.popup.ize('p4t_open', 220, false, 10, 20);", $strPopup);
T::vrai('open_popup : sans bouton de fermeture si booClosable vaut false', strpos($strPopup, 'ploopi_popup_close') === false);
T::contient('open_popup : titre déplaçable', 'handle_p4t_open', $strPopup);

$strPopupJs = file_get_contents(PLOOPI_ROOT.'/js/popup.js');
T::vrai('js/popup.js : plus d\'appel à Prototype (document.viewport)', strpos($strPopupJs, 'document.viewport') === false);
T::contient('js/popup.js : ize() ramène la position à la zone d\'accroche', 'ph.offset().left', $strPopupJs);

$strModulesCss = file_get_contents("{$strGabarit}/css/base_9_modules.css");
T::contient('styles.pack.css agrège base_9_modules.css', $strModulesCss, file_get_contents("{$strGabarit}/css/styles.pack.css"));
T::vrai('base_9_modules.css : accolades équilibrées', substr_count($strModulesCss, '{') == substr_count($strModulesCss, '}'));
T::vrai('base_9_modules.css : ne touche pas aux tableaux (display_array)', strpos($strModulesCss, 'ploopi_explorer') === false);

$strTitre = $objSkin4->create_pagetitle('Actualit&eacute;s', '', 'Sous-titre');
T::contient('create_pagetitle : titre de niveau 1 (html du module gardé)', '<div class="pagetitle p4-pagetitle"><h1>Actualit&eacute;s</h1>', $strTitre);
T::contient('create_pagetitle : complément sous le titre', '<div class="p4-pagetitle-extra">Sous-titre</div>', $strTitre);
T::contientpas('create_pagetitle : historique sous ploopi3', '<h1>', $objSkin3->create_pagetitle('Actualités'));

// Arborescence : racine r, enfants a (avec a1) et b ; a1 sélectionné
$arrNoeuds = array(
    'r' => array('id' => 'r', 'label' => 'Racine', 'parents' => array(0), 'icon' => './img/r.png', 'link' => 'admin.php?r', 'onclick' => ''),
    'a' => array('id' => 'a', 'label' => 'Dossier <A>', 'parents' => array(0, 'r'), 'icon' => './img/a.png', 'link' => 'admin.php?a', 'onclick' => '', 'node_link' => '', 'node_onclick' => "ploopi.skin.treeview_shownode('a', 'q', 'admin-light.php')"),
    'b' => array('id' => 'b', 'label' => 'Feuille', 'parents' => array(0, 'r'), 'icon' => './img/b.png', 'link' => '', 'onclick' => ''),
    'a1' => array('id' => 'a1', 'label' => 'Sélection', 'parents' => array(0, 'r', 'a'), 'icon' => './img/c.png', 'link' => '', 'onclick' => ''),
);
$arrArbre = array(0 => array('r'), 'r' => array('a', 'b'), 'a' => array('a1'));
$strArbre = $objSkin4->display_treeview($arrNoeuds, $arrArbre, 'a1');
T::contient('display_treeview : identifiants du socle (nœud, chevron, enfants)', 'id="treeview_nodea"', $strArbre);
T::contient('display_treeview : chevron de dépliage (t<id>), ouvert vers la sélection', '<span class="p4-tree-twisty" id="ta" data-p4-open="1">', $strArbre);
T::contient('display_treeview : dépliage du module (node_onclick)', "onclick=\"javascript:ploopi.skin.treeview_shownode('a', 'q', 'admin-light.php');\"", $strArbre);
T::contient('display_treeview : enfants ouverts (n<id>)', '<div class="p4-tree-children" id="na" style="display:block;">', $strArbre);
T::contient('display_treeview : feuille sans chevron', '<span class="p4-tree-toggle"></span><img class="p4-tree-icon" src="./img/b.png"', $strArbre);
T::contient('display_treeview : nœud sélectionné', '<div class="p4-tree-row p4-tree-sel"><span class="p4-tree-toggle"></span><img class="p4-tree-icon" src="./img/c.png" alt="" /><a class="p4-tree-label" href="javascript:void(0);" aria-current="true">', $strArbre);
T::contient('display_treeview : libellé échappé', 'Dossier &lt;A&gt;', $strArbre);
T::contient('display_treeview : racine sans chevron', 'class="treeview_node p4-tree-node p4-tree-root" id="treeview_noder"><div class="p4-tree-row"><img', $strArbre);
T::contientpas('display_treeview : plus d\'images de lignes', 'treeview/line.png', $strArbre);

// Annuaire sur petit écran : arbre repliable (résumé masqué par le module, affiché par
// ploopi4), légende groupée sans changer le rendu historique
$strDirSource = file_get_contents(PLOOPI_ROOT.'/install/directory/files/public_directory.php');
$strDirCss = file_get_contents(PLOOPI_ROOT.'/install/directory/files/include/styles.css');
T::contient('annuaire : arbre des rubriques dans un <details> ouvert, repliable', '<details class="directory_tree_toggle ploopi_fold" open>', $strDirSource);
T::contient('GED : arbre des dossiers dans un <details> ouvert, repliable', '<details class="doc_tree_toggle ploopi_fold" open>', file_get_contents(PLOOPI_ROOT.'/install/doc/files/public.php'));
T::vrai('GED : résumé masqué par la feuille du module (rendu historique)', (bool) preg_match('/\.doc_tree_toggle > summary \{\s*display:none;/', file_get_contents(PLOOPI_ROOT.'/install/doc/files/include/styles.css')));
T::contient('annuaire : résumé échappé', '<summary><span><?php echo ploopi\\str::htmlentities($strTreeLabel); ?> :</span>', $strDirSource);
T::vrai('annuaire : résumé masqué par la feuille du module (rendu historique)', (bool) preg_match('/\.directory_tree_toggle > summary \{\s*display:none;/', $strDirCss));
T::contient('socle : repli seulement si le résumé est affiché (ploopi.skin.fold)', "if (s && window.getComputedStyle(s).display != 'none') d.open = false;", file_get_contents(PLOOPI_ROOT.'/js/skin.js'));
T::contient('ploopi4 : résumé affiché sur petit écran', 'body details.ploopi_fold > summary {', $strModulesCss);
T::eq('annuaire : sept paires de légende groupées', 7, substr_count($strDirSource, '<span class="directory_legend_item">'));
T::vrai('annuaire : paires sans boîte propre hors ploopi4 (display: contents)', (bool) preg_match('/p\.ploopi_va > span\.directory_legend_item \{\s*display:contents;/', $strDirCss));

// Formulaires : saisies en cartes sur petit écran (attributs neutres posés par le module)
$strFormsSource = file_get_contents(PLOOPI_ROOT.'/install/forms/files/public_forms_viewreplies.php');
T::contient('forms : ligne d\'en-tête marquée, libellé du tri échappé', '<tr class="forms_viewlist_head" data-label="<?php echo ploopi\\str::htmlentities(_FORMS_SORTBY); ?>"', $strFormsSource);
T::contient('forms : libellé de colonne échappé sur chaque cellule', 'data-label=\\"".ploopi\\str::htmlentities($row[\'label\'])."\\"', $strFormsSource);
T::eq('forms : cellules d\'actions marquées (en-tête et lignes)', 2, substr_count($strFormsSource, 'class="forms_viewlist_actions"'));
T::contient('forms : _FORMS_SORTBY défini', "define ('_FORMS_SORTBY',", file_get_contents(PLOOPI_ROOT.'/install/forms/files/lang/french.php'));
T::contient('ploopi4 : saisies en cartes sur petit écran', 'body table.forms_viewlist tr:not(.forms_viewlist_head) {', $strModulesCss);
T::vrai('forms : aucune règle pour ces marques dans la feuille du module (rendu historique)', strpos(file_get_contents(PLOOPI_ROOT.'/install/forms/files/include/styles.css'), 'forms_viewlist_head') === false);

// Calendriers en taille « Auto » : largeur mesurée par le socle (js/skin.js)
$strAutoJs = file_get_contents(PLOOPI_ROOT.'/js/skin.js');
T::contient('js/skin.js : largeur automatique (ploopi.skin.autowidth)', 'ploopi.skin.autowidth = (function()', $strAutoJs);
T::contient('js/skin.js : opération et paramètre contrôlés avant l\'appel', "if (!/^[a-z0-9_]+$/.test(op) || !/^[a-z0-9_]+$/.test(param)", $strAutoJs);
foreach(array('planning' => 'planning', 'booking' => 'booking') as $strMod => $strPrefixe)
{
    T::contient("{$strMod} : calendrier marqué pour la largeur automatique", "data-autowidth-op=\"{$strPrefixe}_refresh\" data-autowidth-param=\"{$strPrefixe}_autowidth\"", file_get_contents(PLOOPI_ROOT."/install/{$strMod}/files/public_planning.php"));
}
T::contient('ploopi4 : fenêtre des ressources des réservations sous la page sur petit écran', ':is(#popup_planning, #popup_booking).ploopi_popup', $strModulesCss);
T::contient('ploopi4 : barres d\'actions des modules dans l\'ordre du grand écran sur petit écran', ".ploopi_tabs:not(:has(> a.left, > input, > span.title)) {\n        display: flex;\n        flex-direction: row-reverse;\n        flex-wrap: wrap-reverse;", $strModulesCss);

$strSkinJs = file_get_contents(PLOOPI_ROOT.'/js/skin.js');
T::contient('treeview_shownode : chevron de ploopi4 (data-p4-open)', "elt.setAttribute('data-p4-open'", $strSkinJs);
T::contient('treeview_shownode : images plus/minus historiques gardées', "elt.src.replace('plus', 'minus')", $strSkinJs);

/**
 * Libellés : toute constante _SYSTEM_* employée par les écrans refondus est définie
 */
T::section('libellés des écrans refondus');

// Libellés (lang/french.php) et constantes du module (include/global.php)
$strLangue = file_get_contents(PLOOPI_ROOT.'/modules/system/lang/french.php').file_get_contents(PLOOPI_ROOT.'/modules/system/include/global.php');
$arrManquantes = array();
$arrEcrans = array_filter(glob(PLOOPI_ROOT.'/modules/system/*.php'), function($strFichier) { return strpos(basename($strFichier), 'admin_workspaces') === 0 || strpos(file_get_contents($strFichier), 'ploopi\\system\\ui') !== false; });
foreach($arrEcrans as $strFichier)
{
    preg_match_all('/\b(_SYSTEM_[A-Z0-9_]+)\b/', file_get_contents($strFichier), $arrConstantes);
    foreach(array_unique($arrConstantes[1]) as $strConstante)
    {
        if (in_array($strConstante, array('_SYSTEM_WORKSPACES', '_SYSTEM_GROUPS'))) continue;
        if (strpos($strLangue, "'{$strConstante}'") === false) $arrManquantes[] = basename($strFichier).' : '.$strConstante;
    }
}
T::vrai('constantes définies ('.sizeof($arrEcrans).' écrans)', empty($arrManquantes), implode(', ', $arrManquantes));

T::section('calendriers : texte des événements en thème sombre');
/**
 * Un événement coloré (couleur d'une ressource, état d'une réservation) garde un texte
 * sombre, ses teintes étant claires. Le fond blanc par défaut (booking, ressource sans
 * couleur) est, lui, converti en surface sombre par base_9_enligne.css : le texte forcé
 * sombre devenait illisible (« 09:00 Réunion » noir sur fond sombre, relevé sous Docker).
 */
$strCalendrier = file_get_contents("{$strGabarit}/css/base_5_calendar.css");
preg_match('@([^{}]*)\{\s*color:#15232a;\s*\}@', $strCalendrier, $arrRegle);
$arrSel = array_filter(array_map('trim', explode(',', $arrRegle[1] ?? '')), function($s) { return strpos($s, 'event') !== false; });
T::vrai('règle du texte sombre des événements colorés trouvée', count($arrSel) === 2, $arrRegle[1] ?? 'introuvable');
T::vrai('le fond blanc par défaut en est exclu', count(array_filter($arrSel, function($s) { return stripos($s, ':not([style*="background-color:#ffffff" i])') === false; })) === 0, implode(' | ', $arrSel));

T::section('déclinaison ploopi4.1 : autres couleurs, même gabarit');
/**
 * ploopi4.1 ne contient que son skin.ini (extends = ploopi4) et sa palette : le socle sert
 * les fichiers de ploopi4 (gabarits, scripts, icônes, polices) et ajoute la feuille de la
 * palette après les autres. Les écrans refondus, les comportements et les correctifs de
 * ploopi4 s'appliquent donc tels quels.
 */
$strDecl = PLOOPI_ROOT.'/templates/backoffice/ploopi4.1';
$arrIni = is_file("{$strDecl}/skin.ini") ? parse_ini_file("{$strDecl}/skin.ini") : array();
T::eq('skin.ini : déclinaison de ploopi4', 'ploopi4', $arrIni['extends'] ?? null);
T::vrai('aucun gabarit dupliqué (index.tpl, light.tpl, scripts, icônes)', !file_exists("{$strDecl}/index.tpl") && !file_exists("{$strDecl}/light.tpl") && !is_dir("{$strDecl}/js") && !is_dir("{$strDecl}/img"));

$arrRes = ploopi\skin::resolve('ploopi4.1');
T::eq('résolution : fichiers servis par ploopi4', './templates/backoffice/ploopi4', $arrRes['path'] ?? null);
T::eq('résolution : feuille de la palette', './templates/backoffice/ploopi4.1/css/palette.css', $arrRes['palette'] ?? null);
$arrRes = ploopi\skin::resolve('ploopi4');
T::vrai('résolution : ploopi4 inchangé, sans palette', ($arrRes['path'] ?? '') === './templates/backoffice/ploopi4' && ($arrRes['palette'] ?? null) === '');
T::eq('résolution : gabarit inconnu refusé', null, ploopi\skin::resolve('inconnu'));
T::eq('résolution : nom hors du dossier des gabarits refusé', null, ploopi\skin::resolve('../ploopi4'));
T::vrai('proposé dans la liste des gabarits des espaces', in_array('ploopi4.1', ploopi\system::getavailabletemplates('backoffice')));

foreach (array('index.tpl', 'light.tpl') as $strTpl)
{
    $strCode = file_get_contents("{$strGabarit}/{$strTpl}");
    $intPack = strpos($strCode, 'css/styles.pack.css');
    $intPalette = strpos($strCode, '<!-- BEGIN template_palette -->');
    T::vrai("{$strTpl} : palette chargée après la feuille du gabarit", $intPack !== false && $intPalette !== false && $intPalette > $intPack);
}

$strPalette = (string) @file_get_contents("{$strDecl}/css/palette.css");
/**
 * Lecture des jetons d'un bloc de la palette
 *
 * @param string $strSelecteur début du bloc (« :root, », « :root[data-theme="dark"] »…)
 * @return array jeton => valeur
 */
$fnJetons = function($strSelecteur) use ($strPalette) {
    $intDebut = strpos($strPalette, $strSelecteur);
    if ($intDebut === false) return array();
    $strBloc = substr($strPalette, $intDebut, strpos($strPalette, '}', $intDebut) - $intDebut);
    preg_match_all('/(--[a-z0-9-]+)\s*:\s*([^;]+);/', $strBloc, $arrM, PREG_SET_ORDER);
    $arrJetons = array();
    foreach ($arrM as $arrL) $arrJetons[$arrL[1]] = trim($arrL[2]);
    // Références entre jetons du bloc (--primary: var(--brand-600))
    foreach ($arrJetons as $strJeton => $strValeur)
    {
        for ($i = 0; $i < 5 && preg_match('/^var\((--[a-z0-9-]+)\)$/', $strValeur, $arrRef) && isset($arrJetons[$arrRef[1]]); $i++) $strValeur = $arrJetons[$arrRef[1]];
        $arrJetons[$strJeton] = $strValeur;
    }
    return $arrJetons;
};
/** Rapport de contraste WCAG 2.1 entre deux couleurs #rrggbb */
$fnContraste = function($strA, $strB) {
    $fnLum = function($strHex) {
        $arrC = array_map(function($s) { $c = hexdec($s) / 255; return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4); }, str_split(ltrim($strHex, '#'), 2));
        return 0.2126 * $arrC[0] + 0.7152 * $arrC[1] + 0.0722 * $arrC[2];
    };
    $arrL = array($fnLum($strA), $fnLum($strB));
    rsort($arrL);
    return ($arrL[0] + 0.05) / ($arrL[1] + 0.05);
};
$arrClair = $fnJetons(':root,');
$arrSombre = $fnJetons(':root[data-theme="dark"]');
$arrAuto = $fnJetons(':root[data-theme="auto"]');
T::vrai('palette : jetons clairs, sombres et automatiques', count($arrClair) > 20 && count($arrSombre) > 15 && $arrSombre == $arrAuto, count($arrClair).' / '.count($arrSombre).' / '.count($arrAuto));
T::vrai('palette : couleur principale distincte de ploopi4', ($arrClair['--brand-600'] ?? '') !== '' && stripos(file_get_contents("{$strGabarit}/css/base_0_jetons.css"), '--brand-600: '.$arrClair['--brand-600']) === false);
$arrCouples = array(
    // texte (4,5:1) puis contours de saisie (3:1)
    array('--text', '--bg', 4.5), array('--text', '--surface', 4.5), array('--text-2', '--surface', 4.5), array('--text-3', '--surface', 4.5),
    array('--text-3', '--surface-2', 4.5), array('--primary-text', '--surface', 4.5), array('--primary-text', '--primary-soft', 4.5),
    array('--on-primary', '--primary', 4.5), array('--border-field', '--surface', 3),
);
foreach (array('clair' => $arrClair, 'sombre' => $arrSombre) as $strTheme => $arrJ)
{
    $arrFaibles = array();
    foreach ($arrCouples as $arrC)
    {
        $strA = $arrJ[$arrC[0]] ?? null;
        $strB = $arrJ[$arrC[1]] ?? null;
        if (!$strA || !$strB || !preg_match('/^#[0-9a-f]{6}$/i', $strA) || !preg_match('/^#[0-9a-f]{6}$/i', $strB)) { $arrFaibles[] = "{$arrC[0]}/{$arrC[1]} absent"; continue; }
        $fltR = $fnContraste($strA, $strB);
        if ($fltR < $arrC[2]) $arrFaibles[] = sprintf('%s/%s %.2f', $arrC[0], $arrC[1], $fltR);
    }
    T::vrai("palette ({$strTheme}) : contrastes WCAG AA", empty($arrFaibles), implode(', ', $arrFaibles));
}
T::vrai('palette : texte blanc lisible sur la barre supérieure (--brand-800)', isset($arrClair['--brand-800']) && $fnContraste('#ffffff', $arrClair['--brand-800']) >= 4.5);

exit(T::bilan('37_ploopi4_gabarit'));
