<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Contrôles sur les sources : syntaxe PHP et conventions de code (cf. doc/reference.md §14).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
amorcer_socle();

/**
 * Liste les fichiers PHP du dépôt, hors dépendances et dossiers techniques.
 *
 * @param array $arrRacines dossiers à parcourir, relatifs à la racine du dépôt
 * @return array chemins relatifs
 */
function lister_php($arrRacines)
{
    $arrFichiers = array();

    foreach($arrRacines as $strRacine)
    {
        $strChemin = PLOOPI_ROOT.'/'.$strRacine;
        if (!is_dir($strChemin)) continue;

        $objIterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($strChemin, FilesystemIterator::SKIP_DOTS));

        foreach($objIterateur as $objFichier)
        {
            if (!$objFichier->isFile() || strtolower($objFichier->getExtension()) != 'php' || !suivi($objFichier->getPathname())) continue;
            $arrFichiers[] = substr($objFichier->getPathname(), strlen(PLOOPI_ROOT) + 1);
        }
    }

    sort($arrFichiers);
    return $arrFichiers;
}

chdir(PLOOPI_ROOT);

$arrRacines = array('include', 'lib', 'config', 'modules', 'install', 'templates', 'tests');
$arrFichiers = lister_php($arrRacines);

T::section('syntaxe PHP');
T::vrai('des fichiers PHP sont bien trouvés', sizeof($arrFichiers) > 100, sizeof($arrFichiers).' fichier(s)');

/**
 * Le dépôt utilise la syntaxe de balise courte dans quelques fichiers : le lint doit
 * être exécuté avec le même réglage que la production (cf. doc/reference.md §17).
 */
$arrErreurs = array();
foreach($arrFichiers as $strFichier)
{
    $strSortie = array();
    $intCode = 0;
    exec('php -d short_open_tag=On -l '.escapeshellarg($strFichier).' 2>&1', $strSortie, $intCode);
    if ($intCode !== 0) $arrErreurs[$strFichier] = implode(' ', $strSortie);
}

T::vrai(
    'aucune erreur de syntaxe sur '.sizeof($arrFichiers).' fichiers',
    empty($arrErreurs),
    sizeof($arrErreurs).' fichier(s) en erreur : '.implode(' | ', array_keys($arrErreurs))
);

T::section('conventions de code (include/classes)');
$arrClasses = lister_php(array('include/classes'));
T::vrai('les classes du socle sont trouvées', sizeof($arrClasses) > 50, sizeof($arrClasses).' classe(s)');

/**
 * Écarts connus, constatés à la mise en place de la suite. Ils ne sont pas corrigés
 * ici (le correctif ne relève pas des tests) mais sont listés nommément pour que
 * TOUT NOUVEL écart fasse échouer le contrôle.
 */
$arrDerogations = array(
    // En-tête GPL absent.
    'entete' => array(),
    // Le fichier ne porte pas le nom de la classe qu'il déclare.
    'nommage' => array()
);

$arrSansEntete = array();
$arrAvecTab = array();
$arrMalNommees = array();

foreach($arrClasses as $strFichier)
{
    $strSource = file_get_contents($strFichier);

    // En-tête GPL obligatoire (doc/reference.md §14)
    if (strpos($strSource, 'GNU General Public License') === false && !in_array($strFichier, $arrDerogations['entete'], true)) $arrSansEntete[] = $strFichier;

    // Indentation : 4 espaces, jamais de tabulation (tools/clean.sh)
    if (strpos($strSource, "\t") !== false) $arrAvecTab[] = $strFichier;

    // Un fichier = une classe, nom de fichier = nom de la classe (doc/reference.md §7.1)
    $strAttendu = basename($strFichier, '.php');
    if (preg_match('/^\s*(?:abstract\s+|final\s+)*(?:class|interface|trait)\s+([a-zA-Z0-9_]+)/m', $strSource, $arrMatch))
    {
        if (strtolower($arrMatch[1]) !== strtolower($strAttendu) && !in_array($strFichier, $arrDerogations['nommage'], true)) $arrMalNommees[] = $strFichier.' (classe '.$arrMatch[1].')';
    }
}

T::vrai('toutes les classes portent l\'en-tête GPL', empty($arrSansEntete), implode(', ', $arrSansEntete));
T::vrai('aucune tabulation dans les classes du socle', empty($arrAvecTab), implode(', ', $arrAvecTab));
T::vrai('nom de fichier = nom de la classe', empty($arrMalNommees), implode(', ', $arrMalNommees));

// Les écarts connus sont surveillés : s'ils disparaissent, la dérogation doit être retirée.
foreach($arrDerogations as $strType => $arrListe)
    foreach($arrListe as $strFichier)
        T::vrai("dérogation '{$strType}' toujours justifiée : {$strFichier}", file_exists($strFichier), 'fichier disparu, retirer la dérogation');

T::section('autoload');
/**
 * La règle de nommage de loader::_classToFile() doit être vérifiable pour chaque
 * classe du socle : ploopi\<classe> -> include/classes/<classe>.php
 */
$arrNonChargeables = array();
foreach($arrClasses as $strFichier)
{
    if (in_array($strFichier, $arrDerogations['nommage'], true)) continue;
    $strClasse = 'ploopi\\'.basename($strFichier, '.php');
    if (!ploopi\loader::classExists($strClasse)) $arrNonChargeables[] = $strClasse;
}
amorcer_socle();
T::vrai('chaque classe du socle est résolue par l\'autoload', empty($arrNonChargeables), implode(', ', $arrNonChargeables));

T::section('obsolète en PHP 8.5');
/**
 * PHP 8.5 rend obsolètes, dès la compilation : « case X; » et « default; » (point-virgule
 * au lieu des deux-points), l'opérateur accent grave (`commande`) et les transtypages non
 * canoniques ((integer), (boolean), (double), (binary)). Ils deviendront des erreurs en
 * PHP 9. Relevé par php -l sous PHP 8.5 ; contrôlé ici par le tokenizer, sur toute version.
 */
$arrObsoletes85 = array();
foreach($arrFichiers as $strFichier)
{
    if (preg_match('@^modules/(?!system/)@', $strFichier)) continue; // copies déployées : la source est dans install/
    $strCode = file_get_contents(PLOOPI_ROOT.'/'.$strFichier);
    if (strpos($strCode, 'case') === false && strpos($strCode, 'default') === false && strpos($strCode, '`') === false && stripos($strCode, '(integer)') === false && stripos($strCode, '(boolean)') === false && stripos($strCode, '(double)') === false && stripos($strCode, '(binary)') === false) continue;
    $arrTokens = @token_get_all($strCode);
    $intN = count($arrTokens);
    for ($i = 0; $i < $intN; $i++)
    {
        $mixT = $arrTokens[$i];
        if ($mixT === '`') { $arrObsoletes85[] = "{$strFichier} (accent grave)"; while (++$i < $intN && $arrTokens[$i] !== '`'); continue; }
        if (!is_array($mixT)) continue;
        if (in_array($mixT[0], array(T_INT_CAST, T_BOOL_CAST, T_DOUBLE_CAST, T_STRING_CAST)) && !in_array(strtolower(preg_replace('/\s+/', '', $mixT[1])), array('(int)', '(bool)', '(float)', '(string)')))
            $arrObsoletes85[] = "{$strFichier}:{$mixT[2]} ({$mixT[1]})";
        if ($mixT[0] == T_CASE || $mixT[0] == T_DEFAULT)
        {
            // Premier « : » ou « ; » au niveau de l'étiquette (les ?: imbriqués comptent un niveau)
            $intProf = 0;
            for ($j = $i + 1; $j < $intN; $j++)
            {
                $mixU = $arrTokens[$j];
                if (is_array($mixU)) { if ($mixT[0] == T_DEFAULT && $j == $i + 1 && $mixU[0] == T_DOUBLE_ARROW) break; continue; }
                if (in_array($mixU, array('(', '[', '{', '?'))) $intProf++;
                elseif (in_array($mixU, array(')', ']', '}'))) { if (--$intProf < 0) break; }
                elseif ($mixU === ':') { if ($intProf == 0) break; $intProf--; }
                elseif ($mixU === ';') { if ($intProf == 0) $arrObsoletes85[] = "{$strFichier}:{$mixT[2]} (".strtolower($mixT[1])." suivi de ;)"; break; }
                elseif ($mixU === ',' && $mixT[0] == T_DEFAULT && $intProf == 0) break; // default d'un match()
            }
        }
    }
}
T::vrai('aucune construction obsolète en PHP 8.5', empty($arrObsoletes85), count($arrObsoletes85).' : '.implode(', ', array_slice($arrObsoletes85, 0, 12)));

T::section('fonctions supprimées en PHP 8');
/**
 * Le projet doit tourner sur les versions 8.x, y compris anciennes. Trois
 * constructions provoquent une erreur fatale dès PHP 8.0 et ne peuvent donc pas
 * réapparaître dans l'arbre :
 *
 * - create_function(), supprimée ;
 * - each(), supprimée ;
 * - implode($tableau, $glu), l'ordre inverse n'étant plus accepté.
 *
 * L'analyse passe par le lexer de PHP et non par une expression rationnelle sur le
 * texte : les commentaires qui CITENT ces fonctions — à commencer par ceux des
 * correctifs — ne doivent pas être pris pour des appels.
 *
 * Aucune exception : jpgraph, longtemps embarqué dans le module forms et exclu de ce
 * contrôle, s'installe désormais par Composer (mitoteam/jpgraph), hors de l'arbre analysé.
 */
$arrExceptions = array();

/**
 * Relève les appels à une fonction supprimée en PHP 8, hors commentaires.
 *
 * @param string $strFichier chemin du fichier
 * @return array noms des constructions fautives trouvées
 */
function appels_interdits($strFichier)
{
    $arrTrouves = array();

    $arrJetons = @token_get_all(file_get_contents($strFichier));
    if (empty($arrJetons)) return $arrTrouves;

    // On ne garde que le code : ni commentaires, ni espaces
    $arrCode = array();
    foreach($arrJetons as $mixJeton)
    {
        if (is_array($mixJeton) && in_array($mixJeton[0], array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE), true)) continue;
        $arrCode[] = $mixJeton;
    }

    foreach($arrCode as $intPos => $mixJeton)
    {
        if (!is_array($mixJeton) || $mixJeton[0] !== T_STRING) continue;

        $strNom = strtolower($mixJeton[1]);
        if (!in_array($strNom, array('create_function', 'each', 'implode'), true)) continue;

        // Appel de fonction, et non méthode ou constante de classe
        $mixPrecedent = isset($arrCode[$intPos - 1]) ? $arrCode[$intPos - 1] : null;
        if (is_array($mixPrecedent) && in_array($mixPrecedent[0], array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION), true)) continue;
        if (!isset($arrCode[$intPos + 1]) || $arrCode[$intPos + 1] !== '(') continue;

        if ($strNom === 'implode')
        {
            // implode($tableau, 'glu') : ordre inverse, refusé depuis PHP 8.0
            $mixArg1 = isset($arrCode[$intPos + 2]) ? $arrCode[$intPos + 2] : null;
            $mixVirgule = isset($arrCode[$intPos + 3]) ? $arrCode[$intPos + 3] : null;
            $mixArg2 = isset($arrCode[$intPos + 4]) ? $arrCode[$intPos + 4] : null;

            if (is_array($mixArg1) && $mixArg1[0] === T_VARIABLE
                && $mixVirgule === ','
                && is_array($mixArg2) && $mixArg2[0] === T_CONSTANT_ENCAPSED_STRING)
                $arrTrouves[] = 'implode inversé';

            continue;
        }

        $arrTrouves[] = $strNom.'()';
    }

    return array_unique($arrTrouves);
}

$arrTrouves = array();

foreach(lister_php(array('include', 'lib', 'modules', 'install', 'config')) as $strFichier)
{
    foreach($arrExceptions as $strException) if (strpos($strFichier, $strException) === 0) continue 2;

    foreach(appels_interdits($strFichier) as $strNom) $arrTrouves[] = "{$strFichier} ({$strNom})";
}

T::vrai(
    'aucun appel à create_function(), each() ou implode() inversé',
    empty($arrTrouves),
    implode(', ', array_slice($arrTrouves, 0, 6))
);

T::section('appels incomplets');
/**
 * mb_convert_encoding() exige au moins deux arguments (chaîne, encodage cible) :
 * appelée avec un seul, elle lève ArgumentCountError. La PR #63 en avait laissé
 * treize dans dbreport en remplaçant utf8_encode(), une erreur fatale au rendu de
 * tout graphique. Même lecture par le lexer que ci-dessus.
 *
 * @param string $strFichier chemin du fichier
 * @return int nombre d'appels à un seul argument
 */
function appels_incomplets($strFichier)
{
    $intTrouves = 0;

    $arrJetons = @token_get_all(file_get_contents($strFichier));
    if (empty($arrJetons)) return 0;

    $arrCode = array();
    foreach($arrJetons as $mixJeton)
    {
        if (is_array($mixJeton) && in_array($mixJeton[0], array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE), true)) continue;
        $arrCode[] = $mixJeton;
    }

    foreach($arrCode as $intPos => $mixJeton)
    {
        if (!is_array($mixJeton) || $mixJeton[0] !== T_STRING || strtolower($mixJeton[1]) !== 'mb_convert_encoding') continue;
        if (!isset($arrCode[$intPos + 1]) || $arrCode[$intPos + 1] !== '(') continue;

        // virgules de premier niveau jusqu'à la parenthèse fermante
        $intProfondeur = 0;
        $intVirgules = 0;
        for ($i = $intPos + 1; isset($arrCode[$i]); $i++)
        {
            $mixCourant = is_array($arrCode[$i]) ? $arrCode[$i][1] : $arrCode[$i];
            if (in_array($mixCourant, array('(', '[', '{'), true) || (is_array($arrCode[$i]) && in_array($arrCode[$i][0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))) $intProfondeur++;
            elseif (in_array($mixCourant, array(')', ']', '}'), true)) { if (--$intProfondeur === 0) break; }
            elseif ($mixCourant === ',' && $intProfondeur === 1) $intVirgules++;
        }

        if ($intVirgules === 0) $intTrouves++;
    }

    return $intTrouves;
}

$arrTrouves = array();
foreach(lister_php(array('include', 'lib', 'modules', 'install', 'config')) as $strFichier)
{
    $intTrouves = appels_incomplets($strFichier);
    if ($intTrouves > 0) $arrTrouves[] = "{$strFichier} ({$intTrouves})";
}

T::vrai('aucun appel à mb_convert_encoding() sans encodage cible', empty($arrTrouves), implode(', ', array_slice($arrTrouves, 0, 6)));

/**
 * « ${var} » est déprécié depuis PHP 8.2 ; sa forme correcte est « {$var} ». La PR
 * #63 avait écrit « {var} », sans dollar, dans nanogallery : la chaîne n'est plus
 * interpolée et sort telle quelle (liens des galeries, styles de cadre). On relève
 * dans les chaînes entre guillemets doubles une accolade suivie d'un identifiant
 * puis de « [ », « -> » ou « } ». {p} est un gabarit légitime (str_replace).
 */
$arrGabarits = array('{p}');
$arrTrouves = array();
foreach(lister_php(array('include', 'lib', 'modules', 'install', 'config')) as $strFichier)
{
    foreach((array)@token_get_all(file_get_contents($strFichier)) as $mixJeton)
    {
        if (!is_array($mixJeton)) continue;
        $booGuillemets = $mixJeton[0] === T_ENCAPSED_AND_WHITESPACE || ($mixJeton[0] === T_CONSTANT_ENCAPSED_STRING && $mixJeton[1][0] === '"');
        if ($booGuillemets && preg_match('/\{[a-zA-Z_][a-zA-Z0-9_]*(\[|->|\})/', $mixJeton[1], $arrMatches) && !in_array($arrMatches[0], $arrGabarits, true))
            $arrTrouves[] = "{$strFichier}:{$mixJeton[2]} ({$arrMatches[0]})";
    }
}

T::vrai('aucune interpolation sans dollar ({var} pour {$var})', empty($arrTrouves), implode(', ', array_slice($arrTrouves, 0, 6)));

T::section('commandes shell');
/**
 * Une commande shell ne doit jamais interpoler une variable dans une chaîne entre
 * guillemets doubles : chaque valeur passe par escapeshellarg(), ou par un cast
 * entier. Le module doc construisait « jhead "{$path}" », où le chemin se termine
 * par l'extension du fichier déposé — et $(...) reste interprété entre guillemets
 * doubles ; forms passait le format d'export reçu en GET à unoconv.
 *
 * Relevé par le lexer : appels exec/shell_exec/passthru/system/popen/proc_open et
 * affectations de $exec/$cmd, dont l'instruction contient une chaîne interpolée.
 *
 * @param string $strFichier chemin du fichier
 * @return array lignes fautives
 */
function commandes_interpolees($strFichier)
{
    $arrTrouves = array();
    $arrJetons = @token_get_all(file_get_contents($strFichier));
    if (empty($arrJetons)) return $arrTrouves;

    $arrCode = array();
    foreach($arrJetons as $mixJeton)
    {
        if (is_array($mixJeton) && in_array($mixJeton[0], array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE), true)) continue;
        $arrCode[] = $mixJeton;
    }

    $arrFonctions = array('exec', 'shell_exec', 'passthru', 'system', 'popen', 'proc_open');

    foreach($arrCode as $intPos => $mixJeton)
    {
        if (!is_array($mixJeton)) continue;

        $booAppel = $mixJeton[0] === T_STRING && in_array(strtolower($mixJeton[1]), $arrFonctions, true)
            && isset($arrCode[$intPos + 1]) && $arrCode[$intPos + 1] === '('
            && !(isset($arrCode[$intPos - 1]) && is_array($arrCode[$intPos - 1]) && in_array($arrCode[$intPos - 1][0], array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION), true));
        $booAffectation = $mixJeton[0] === T_VARIABLE && in_array($mixJeton[1], array('$exec', '$cmd'), true)
            && isset($arrCode[$intPos + 1]) && $arrCode[$intPos + 1] === '=';

        if (!$booAppel && !$booAffectation) continue;

        // jusqu'à la fin de l'instruction : une chaîne « "..." » qui contient une variable
        $booChaine = false;
        for ($i = $intPos + 1; isset($arrCode[$i]) && $arrCode[$i] !== ';'; $i++)
        {
            if ($arrCode[$i] === '"') $booChaine = !$booChaine;
            elseif ($booChaine && is_array($arrCode[$i]) && in_array($arrCode[$i][0], array(T_VARIABLE, T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))
            {
                $arrTrouves[] = $mixJeton[2];
                break;
            }
        }
    }

    return array_unique($arrTrouves);
}

$arrTrouves = array();
foreach(lister_php(array('include', 'lib', 'modules', 'install', 'config')) as $strFichier)
{
    foreach(commandes_interpolees($strFichier) as $intLigne) $arrTrouves[] = "{$strFichier}:{$intLigne}";
}

T::vrai('aucune commande shell n\'interpole de variable entre guillemets doubles', empty($arrTrouves), implode(', ', array_slice($arrTrouves, 0, 8)));

T::section('Prototype remplacé par jQuery');
/**
 * Prototype et script.aculo.us (protoaculous) ont été retirés au profit de jQuery.
 * Un appel resté en place échoue en silence : graphique dbreport blanc, options de
 * dossier de la GED figées, ajout de sous-ressources de booking cassé. Sous jQuery,
 * $('id') ne désigne plus l'élément d'identifiant « id » mais une balise <id>.
 *
 * On cherche dans le PHP, le JS et les gabarits du socle, des modules et des
 * habillages : le chargement de la bibliothèque, les API propres à Prototype, et les
 * anciennes fonctions ploopi_* qui l'accompagnaient (remplacées par ploopi.*).
 * Les bibliothèques tierces (lib/, vendor) ne sont pas concernées.
 */
function lister_sources($arrRacines)
{
    $arrFichiers = array();
    foreach($arrRacines as $strRacine)
    {
        $strChemin = PLOOPI_ROOT.'/'.$strRacine;
        if (!is_dir($strChemin)) continue;
        $objIterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($strChemin, FilesystemIterator::SKIP_DOTS));
        foreach($objIterateur as $objFichier)
        {
            $strRelatif = substr($objFichier->getPathname(), strlen(PLOOPI_ROOT) + 1);
            if (!$objFichier->isFile() || !in_array(strtolower($objFichier->getExtension()), array('php', 'js', 'tpl'))) continue;
            if (preg_match('@(^|/)(lib|vendor)/|functions\.pack\.js$|\.min\.js$@', $strRelatif) || !suivi($strRelatif)) continue;
            $arrFichiers[] = $strRelatif;
        }
    }
    return $arrFichiers;
}

$strBalises = 'div|span|p|a|img|input|textarea|select|option|form|table|tr|td|th|ul|li|body|html|head|label|button|iframe|h[1-6]|script|document|window';
$arrMotifs = array(
    'chargement de protoaculous' => '/protoaculous|prototype-adapter/',
    'Event.observe' => '/Event\.observe\s*\(/',
    'Ajax.Request / Ajax.Updater' => '/new\s+Ajax\./',
    '$$() de Prototype' => '/\$\$\(/',
    '.invoke()' => '/\.invoke\(/',
    'script.aculo.us (Effect, Draggable)' => '/new\s+(Effect\.|Draggable\()/',
    'PeriodicalExecuter' => '/PeriodicalExecuter/',
    'Prototype.Browser' => '/Prototype\.Browser/',
    // Identifiant fermé par un guillemet, concaténé (+) ou complété en PHP (<?php, <?=)
    '$(\'id\') au sens de Prototype' => '/\$\(\s*\\\\?[\'"](?!(?:'.$strBalises.')\b)[A-Za-z_][\w-]*(?:[\'"+]|<\?)/',
    'anciennes fonctions ploopi_*' => '/\bploopi_(showpopup|hidepopup|xmlhttprequest_topopup|window_onload_stock|calendar_open|checkbox_click)\s*\(/',
);

$arrPrototype = array();
foreach(lister_sources(array('include', 'modules/system', 'install', 'js', 'templates', 'config')) as $strFichier)
{
    $strCode = file_get_contents($strFichier);
    foreach($arrMotifs as $strMotif => $strRegex)
    {
        if (preg_match($strRegex, $strCode)) $arrPrototype[] = "{$strFichier} ({$strMotif})";
    }
}
T::vrai('aucun reste de Prototype dans le socle, les modules et les habillages', empty($arrPrototype), implode(', ', array_slice($arrPrototype, 0, 12)));

T::section('CKEditor remplacé par TinyMCE');
/**
 * CKEditor 4 (fin de vie, failles corrigées seulement dans la version commerciale) et
 * FCKeditor avant lui sont remplacés par TinyMCE, derrière un point d'entrée unique
 * (js/editor.js, ploopi.editor) : un appel resté en place laisserait un champ sans
 * éditeur, ou un contenu non relu à l'enregistrement. Les fenêtres de choix gardent le
 * paramètre CKEditorFuncNum (nom hérité), rendu par ploopi.editor.picked().
 */
$arrMotifsEditeur = array(
    'API CKEditor (CKEDITOR.)' => '/\bCKEDITOR\s*\./',
    'API FCKeditor (FCKeditorAPI)' => '/FCKeditorAPI/',
    'script de CKEditor ou FCKeditor' => '@(ckeditor|fckeditor)\.js@i',
    'chemin vendor/ckeditor' => '@vendor/ckeditor@i',
);
$arrEditeur = array();
foreach(lister_sources(array('include', 'modules/system', 'install', 'js', 'templates', 'config')) as $strFichier)
{
    $strCode = file_get_contents($strFichier);
    foreach($arrMotifsEditeur as $strMotif => $strRegex)
    {
        if (preg_match($strRegex, $strCode)) $arrEditeur[] = "{$strFichier} ({$strMotif})";
    }
}
T::vrai('aucun appel à CKEditor ou FCKeditor dans le socle, les modules et les habillages', empty($arrEditeur), implode(', ', array_slice($arrEditeur, 0, 12)));
T::vrai('point d\'entrée de l\'éditeur présent (js/editor.js)', strpos((string)@file_get_contents(PLOOPI_ROOT.'/js/editor.js'), 'ploopi.editor = ') !== false);
T::vrai('éditeur inclus dans l\'agrégat js/functions.pack.js', strpos((string)@file_get_contents(PLOOPI_ROOT.'/js/functions.pack.js'), 'ploopi.editor = ') !== false);
$arrComposer = json_decode(file_get_contents(PLOOPI_ROOT.'/composer.json'), true);
T::vrai('composer.json : ckeditor/ckeditor retiré, tinymce/tinymce requis', !isset($arrComposer['require']['ckeditor/ckeditor']) && isset($arrComposer['require']['tinymce/tinymce']));
T::vrai('composer.json : aucun avis de sécurité ignoré', empty($arrComposer['config']['policy']['advisories']['ignore-id']));

T::section('chemins vers vendor/');
/**
 * Les scripts et feuilles des dépendances sont appelés par un chemin écrit en dur
 * (« ./vendor/… »). Une erreur de casse passe inaperçue sous Windows ou macOS et donne
 * un 404 sous Linux : webedit chargeait ./vendor/EastDesire/jscolor/ alors que Composer
 * installe vendor/eastdesire/ — sélecteur de couleur absent, constaté en navigateur.
 * La comparaison se fait nom par nom, sans dépendre de la casse du système de fichiers.
 */
function chemin_exact($strRelatif)
{
    $strCourant = PLOOPI_ROOT;
    foreach(array_filter(explode('/', $strRelatif), 'strlen') as $strNom)
    {
        if (!is_dir($strCourant) || !in_array($strNom, scandir($strCourant), true)) return false;
        $strCourant .= '/'.$strNom;
    }
    return true;
}

// Chemins cités qui ne sont pas des dépendances Composer
$arrDerogationsVendor = array(
    // Visionneuse Flash de la GED (public_file_form.php, cas « flash ») : morte avec Flash,
    // swfobject n'a jamais figuré dans composer.json. Module en fin de vie, non corrigé.
    'vendor/swfobject/swfobject/swfobject/swfobject.js' => 'install/doc/files/public_file_form.php',
);

if (!file_exists(PLOOPI_ROOT.'/vendor/autoload.php')) T::skip('chemins vers vendor/ existants', 'vendor/ absent : lancer composer install');
else
{
    $arrIntrouvables = array();
    $intCites = 0;
    foreach(lister_sources(array('include', 'modules/system', 'install', 'templates', 'config')) as $strFichier)
    {
        if (!preg_match_all('@\./(vendor/[A-Za-z0-9_./-]+)@', file_get_contents($strFichier), $arrChemins)) continue;
        foreach(array_unique($arrChemins[1]) as $strChemin)
        {
            $intCites++;
            if (isset($arrDerogationsVendor[$strChemin]) && $arrDerogationsVendor[$strChemin] == $strFichier) continue;
            if (!chemin_exact(rtrim($strChemin, '/.'))) $arrIntrouvables[] = "{$strFichier} ({$strChemin})";
        }
    }
    T::vrai('des chemins vers vendor/ sont trouvés', $intCites > 5, "{$intCites} chemin(s)");
    T::vrai('chaque chemin vers vendor/ existe, casse comprise', empty($arrIntrouvables), implode(', ', $arrIntrouvables));

    foreach($arrDerogationsVendor as $strChemin => $strFichier)
        T::vrai("dérogation vendor/ toujours justifiée : {$strChemin}", strpos((string)@file_get_contents($strFichier), './'.$strChemin) !== false && !chemin_exact($strChemin), 'chemin retiré ou devenu valide, retirer la dérogation');
}

T::section('encodage');
$arrNonUtf8 = array();
foreach($arrClasses as $strFichier)
{
    if (!mb_check_encoding(file_get_contents($strFichier), 'UTF-8')) $arrNonUtf8[] = $strFichier;
}
T::vrai('les classes du socle sont en UTF-8', empty($arrNonUtf8), implode(', ', $arrNonUtf8));

/**
 * Tout fichier texte livré, au-delà des classes : un gabarit, une traduction JS ou la liste
 * des mots vides (config/commonwords_fr.txt, comparée à des mots UTF-8) restés en
 * ISO-8859-1 affichent des accents corrompus ou ne correspondent jamais.
 */
$arrNonUtf8 = array();
$objIterateur = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator(PLOOPI_ROOT, FilesystemIterator::SKIP_DOTS),
    function($objFichier) {
        $strRelatif = substr($objFichier->getPathname(), strlen(PLOOPI_ROOT) + 1);
        // Copies déployées (hors system), dépendances, données et état local
        return !preg_match('#^(vendor|data|build|\.git|modules/(?!system(/|$)))#', $strRelatif);
    }
));
foreach($objIterateur as $objFichier)
{
    if (!in_array(strtolower($objFichier->getExtension()), array('php', 'js', 'css', 'tpl', 'txt', 'sql', 'xml', 'md', 'ini', 'html', 'json')) || !suivi($objFichier->getPathname())) continue;
    if (!mb_check_encoding(file_get_contents($objFichier->getPathname()), 'UTF-8')) $arrNonUtf8[] = substr($objFichier->getPathname(), strlen(PLOOPI_ROOT) + 1);
}
T::vrai('tous les fichiers texte livrés sont en UTF-8', empty($arrNonUtf8), implode(', ', array_slice($arrNonUtf8, 0, 6)));

/**
 * Balise courte « <? » : avec short_open_tag à Off (valeur de php.ini-production), le code
 * qui la suit part tel quel dans la page, ou casse l'analyse du fichier. Douze fichiers en
 * portaient encore, révélés par tools/lint.sh.
 */
T::section('pas de balise PHP courte');

$arrBalisesCourtes = array();
foreach (array('include', 'modules/system', 'install', 'templates', 'lib/template', 'config', 'index.php', 'cli', 'cron') as $strRacine)
{
    $strChemin = PLOOPI_ROOT.'/'.$strRacine;
    if (is_file($strChemin)) $arrFichiers = array($strChemin);
    elseif (is_dir($strChemin))
    {
        $arrFichiers = array();
        $objIterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($strChemin, FilesystemIterator::SKIP_DOTS));
        foreach ($objIterateur as $objFichier) if (in_array($objFichier->getExtension(), array('php', 'tpl'), true) && suivi($objFichier->getPathname())) $arrFichiers[] = $objFichier->getPathname();
    }
    else continue;

    foreach ($arrFichiers as $strFichier)
        if (preg_match('/<\?(?!php|=|xml)/', (string)file_get_contents($strFichier))) $arrBalisesCourtes[] = substr($strFichier, strlen(PLOOPI_ROOT) + 1);
}
T::vrai('aucune balise <? sans php', empty($arrBalisesCourtes), implode(', ', $arrBalisesCourtes));

exit(T::bilan('13_conventions'));
