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
 * Décline la palette de ploopi3 sur les feuilles de style des modules.
 *
 * Lit install/<module>/files/include/styles.css et écrit
 * templates/backoffice/ploopi3/css/base_modules.css : les mêmes sélecteurs,
 * préfixés par « body » (la feuille du module est chargée après celle du
 * gabarit), avec leurs seules propriétés de couleur, réécrites dans les
 * variables --p3-*. Aucune dimension, aucune marge, aucun flottant n'est repris :
 * la mise en page des modules ne peut pas changer.
 *
 * Ne sont repris que les sélecteurs propres au module (classe ou identifiant
 * préfixé par son nom) : une règle générique (« table », « select ») recopiée
 * dans le gabarit s'appliquerait à toutes les pages.
 *
 * Usage, depuis la racine du dépôt :
 *   php tools/ploopi3_modules.php          régénère base_modules.css
 *   php tools/ploopi3_modules.php --check  échoue si base_modules.css n'est pas à jour
 *
 * La même feuille est écrite pour ploopi4 (css/base_5_modules.css), dont les
 * variables --p3-* suivent la palette et le thème.
 *
 * Puis tools/compress.sh pour l'agrégat styles.pack.css.
 *
 * @package ploopi
 * @subpackage tools
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$strRacine = dirname(__DIR__);
$strCible = "{$strRacine}/templates/backoffice/ploopi3/css/base_modules.css";
$arrCibles = array($strCible, "{$strRacine}/templates/backoffice/ploopi4/css/base_5_modules.css");

$arrNoms = array(
    'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000', 'green' => '#008000',
    'blue' => '#0000ff', 'gray' => '#808080', 'grey' => '#808080', 'silver' => '#c0c0c0',
    'orange' => '#ffa500', 'yellow' => '#ffff00', 'navy' => '#000080', 'maroon' => '#800000',
);

/**
 * Couleur CSS → [r, v, b] (0-255), ou null si elle n'est pas reconnue
 */
function ploopi3_rgb($strCouleur, $arrNoms)
{
    $strCouleur = strtolower(trim($strCouleur));
    if (isset($arrNoms[$strCouleur])) $strCouleur = $arrNoms[$strCouleur];
    if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/', $strCouleur, $m)) return array(hexdec($m[1].$m[1]), hexdec($m[2].$m[2]), hexdec($m[3].$m[3]));
    if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/', $strCouleur, $m)) return array(hexdec($m[1]), hexdec($m[2]), hexdec($m[3]));
    if (preg_match('/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $strCouleur, $m)) return array((int)$m[1], (int)$m[2], (int)$m[3]);
    return null;
}

/**
 * Couleur d'origine → variable de la palette, selon le rôle (texte, fond, bordure)
 */
function ploopi3_variable($arrRgb, $strRole, $booSurvol)
{
    list($r, $v, $b) = array($arrRgb[0] / 255, $arrRgb[1] / 255, $arrRgb[2] / 255);
    $fltMax = max($r, $v, $b);
    $fltMin = min($r, $v, $b);
    $fltLum = ($fltMax + $fltMin) / 2;
    $fltSat = ($fltMax == $fltMin) ? 0 : (($fltLum > 0.5) ? ($fltMax - $fltMin) / (2 - $fltMax - $fltMin) : ($fltMax - $fltMin) / ($fltMax + $fltMin));

    // Teinte, en degrés
    $fltTeinte = 0;
    if ($fltMax != $fltMin)
    {
        if ($fltMax == $r) $fltTeinte = 60 * fmod((($v - $b) / ($fltMax - $fltMin)), 6);
        elseif ($fltMax == $v) $fltTeinte = 60 * ((($b - $r) / ($fltMax - $fltMin)) + 2);
        else $fltTeinte = 60 * ((($r - $v) / ($fltMax - $fltMin)) + 4);
        if ($fltTeinte < 0) $fltTeinte += 360;
    }

    // Gris
    if ($fltSat < 0.15 || ($fltLum > 0.97) || ($fltLum < 0.08))
    {
        switch($strRole)
        {
            case 'texte':
                if ($fltLum < 0.35) return 'var(--p3-text)';
                if ($fltLum < 0.75) return 'var(--p3-text-muted)';
                return null; // texte clair, sur fond sombre : inchangé

            case 'fond':
                if ($booSurvol && $fltLum >= 0.6) return 'var(--p3-primary-softer)';
                if ($fltLum >= 0.99) return 'var(--p3-surface)';
                if ($fltLum >= 0.92) return 'var(--p3-surface-2)';
                if ($fltLum >= 0.70) return 'var(--p3-surface-3)';
                return null; // fond sombre : inchangé

            case 'bordure':
                if ($fltLum >= 0.78) return 'var(--p3-border)';
                if ($fltLum >= 0.45) return 'var(--p3-border-strong)';
                return 'var(--p3-border-field)';
        }
        return null;
    }

    // Couleurs : par famille de teinte
    if ($fltTeinte < 20 || $fltTeinte >= 330) $strFamille = 'danger';
    elseif ($fltTeinte < 70) $strFamille = 'warning';
    elseif ($fltTeinte < 170) $strFamille = 'success';
    elseif ($fltTeinte < 260) $strFamille = 'info';
    else return null; // violets : inchangés

    switch($strRole)
    {
        case 'texte':
            return ($fltLum < 0.8) ? "var(--p3-{$strFamille})" : null;

        case 'fond':
            if ($fltLum >= 0.8) return "var(--p3-{$strFamille}-soft)";
            return null; // aplat saturé (repère, pastille) : inchangé

        case 'bordure':
            return ($fltLum >= 0.8) ? null : "var(--p3-{$strFamille})";
    }
    return null;
}

/**
 * Remplace, dans une valeur, chaque couleur par sa variable (null si aucune ne change)
 */
function ploopi3_valeur($strValeur, $strRole, $booSurvol, $arrNoms)
{
    $booChange = false;
    $strMotif = '/#[0-9a-fA-F]{6}\b|#[0-9a-fA-F]{3}\b|rgba?\([^)]*\)|\b(?:'.implode('|', array_keys($arrNoms)).')\b/i';
    $strNouvelle = preg_replace_callback($strMotif, function($m) use ($strRole, $booSurvol, $arrNoms, &$booChange) {
        $arrRgb = ploopi3_rgb($m[0], $arrNoms);
        if ($arrRgb === null) return $m[0];
        $strVariable = ploopi3_variable($arrRgb, $strRole, $booSurvol);
        if ($strVariable === null) return $m[0];
        $booChange = true;
        return $strVariable;
    }, $strValeur);

    return $booChange ? $strNouvelle : null;
}

/**
 * Déclarations de couleur d'une règle, réécrites (propriété => valeur)
 */
function ploopi3_declarations($strBloc, $booSurvol, $arrNoms)
{
    $arrSortie = array();
    foreach(explode(';', $strBloc) as $strDecl)
    {
        if (strpos($strDecl, ':') === false) continue;
        list($strProp, $strValeur) = array_map('trim', explode(':', $strDecl, 2));
        $strProp = strtolower($strProp);
        $strImportant = '';
        if (preg_match('/\s*!important\s*$/i', $strValeur)) { $strImportant = ' !important'; $strValeur = preg_replace('/\s*!important\s*$/i', '', $strValeur); }

        if ($strProp === 'color') $arrCibles = array('color' => 'texte');
        elseif ($strProp === 'background-color') $arrCibles = array('background-color' => 'fond');
        elseif ($strProp === 'background') $arrCibles = array('background-color' => 'fond');
        elseif (preg_match('/^border(-(top|right|bottom|left))?(-color)?$/', $strProp, $m)) $arrCibles = array('border'.(empty($m[1]) ? '' : $m[1]).'-color' => 'bordure');
        elseif ($strProp === 'outline-color' || $strProp === 'outline') $arrCibles = array('outline-color' => 'bordure');
        else continue;

        foreach($arrCibles as $strPropCible => $strRole)
        {
            // Raccourcis (background, border) : seule la couleur est reprise
            if ($strPropCible !== $strProp)
            {
                if (!preg_match('/#[0-9a-fA-F]{3,6}\b|rgba?\([^)]*\)|\b(?:'.implode('|', array_keys($arrNoms)).')\b/i', $strValeur, $m)) continue;
                $strValeur = $m[0];
            }
            $strNouvelle = ploopi3_valeur($strValeur, $strRole, $booSurvol, $arrNoms);
            if ($strNouvelle !== null) $arrSortie[$strPropCible] = $strNouvelle.$strImportant;
        }
    }
    return $arrSortie;
}

$arrModules = array_map('basename', glob("{$strRacine}/install/*", GLOB_ONLYDIR));

// Seuls les modules du dépôt : une copie de travail garde souvent d'anciens paquets dans
// install/, qui ne sont pas livrés (même règle que les tests, suivi() de tests/bootstrap.php).
// Sans git, tous les dossiers sont pris.
exec('git -c safe.directory='.escapeshellarg($strRacine).' -C '.escapeshellarg($strRacine).' ls-files -- install 2>/dev/null', $arrSuivis, $intRc);
if ($intRc === 0 && !empty($arrSuivis))
{
    $arrSuivisModules = array();
    foreach ($arrSuivis as $strSuivi) $arrSuivisModules[explode('/', $strSuivi)[1]] = true;
    $arrModules = array_filter($arrModules, function($strModule) use ($arrSuivisModules) { return isset($arrSuivisModules[$strModule]); });
}
sort($arrModules);

$strSortie = "/*\n    Généré par tools/ploopi3_modules.php : ne pas modifier à la main.\n\n"
    ."    Les feuilles des modules (install/<module>/files/include/styles.css), avec leurs\n"
    ."    seules couleurs, réécrites dans la palette de ploopi3. Préfixe « body » : la\n"
    ."    feuille du module est chargée après celle du gabarit. Les retouches faites à\n"
    ."    la main sont dans base_modules_retouches.css.\n*/\n";
$intRegles = 0;
$arrEcartes = array();

foreach($arrModules as $strModule)
{
    $strFichier = "{$strRacine}/install/{$strModule}/files/include/styles.css";
    if ($strModule === 'system' || !file_exists($strFichier)) continue;

    $strCss = preg_replace('#/\*.*?\*/#s', '', file_get_contents($strFichier));
    preg_match_all('#([^{}]+)\{([^{}]*)\}#', $strCss, $arrRegles, PREG_SET_ORDER);

    // Préfixe du module : au moins ses quatre premières lettres
    $strPrefixe = preg_quote(substr($strModule, 0, 4), '/');
    $strBlocModule = '';

    foreach($arrRegles as $arrRegle)
    {
        $arrSelecteurs = array();
        foreach(explode(',', $arrRegle[1]) as $strSel)
        {
            $strSel = trim(preg_replace('/\s+/', ' ', $strSel));
            if ($strSel === '') continue;
            if (!preg_match("/[.#]{$strPrefixe}/i", $strSel)) { $arrEcartes[$strModule][] = $strSel; continue; }
            $arrSelecteurs[] = (preg_match('/^(html|body)\b/i', $strSel) ? 'html ' : 'body ').$strSel;
        }
        if (empty($arrSelecteurs)) continue;

        $booSurvol = (bool)preg_match('/:hover|:focus|:active/i', $arrRegle[1]);
        $arrDecl = ploopi3_declarations($arrRegle[2], $booSurvol, $arrNoms);
        if (empty($arrDecl)) continue;

        $strBlocModule .= "\n".implode(",\n", $arrSelecteurs)." {\n";
        foreach($arrDecl as $strProp => $strValeur) $strBlocModule .= "    {$strProp}: {$strValeur};\n";
        $strBlocModule .= "}\n";
        $intRegles++;
    }

    if ($strBlocModule !== '') $strSortie .= "\n/******************\n  ".strtoupper($strModule)."\n*******************/\n".$strBlocModule;
}

if (in_array('--check', $argv))
{
    $booAJour = true;
    foreach($arrCibles as $strFichier) $booAJour = $booAJour && file_exists($strFichier) && file_get_contents($strFichier) === $strSortie;
    echo $booAJour ? "base_modules.css est à jour (ploopi3, ploopi4)\n" : "base_modules.css n'est pas à jour : php tools/ploopi3_modules.php\n";
    exit($booAJour ? 0 : 1);
}

foreach($arrCibles as $strFichier) file_put_contents($strFichier, $strSortie);
echo "{$intRegles} règle(s) écrite(s) dans templates/backoffice/ploopi3/css/base_modules.css et templates/backoffice/ploopi4/css/base_5_modules.css\n";
foreach($arrEcartes as $strModule => $arrSel) echo "  {$strModule} : ".count($arrSel)." sélecteur(s) générique(s) écarté(s) : ".implode(' | ', $arrSel)."\n";
