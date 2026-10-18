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
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/

/**
 * Rendu des graphiques du module forms en PNG, avec GD seul.
 *
 * Remplace JpGraph (QPL, incompatible avec la GPL). Les valeurs stockées en base pour la
 * police et le marqueur (FF_VERA = 18, MARK_SQUARE = 1…) gardent leur sens : les
 * graphiques déjà paramétrés se redessinent sans migration.
 *
 * Le dessin se fait au double de la taille demandée puis est réduit, ce qui lisse les
 * traits sans extension d'anti-crénelage.
 *
 * @package forms
 * @subpackage forms_chart
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class formsChart
{
    // Polices (valeurs historiques de JpGraph)
    const FONT_VERA = 18;
    const FONT_VERAMONO = 19;
    const FONT_VERASERIF = 20;
    const FONT_DV_SANSSERIF = 47;
    const FONT_DV_SERIF = 48;
    const FONT_DV_SANSSERIFMONO = 49;
    const FONT_DV_SERIFCOND = 50;
    const FONT_DV_SANSSERIFCOND = 51;

    // Marqueurs (valeurs historiques de JpGraph)
    const MARK_SQUARE = 1;
    const MARK_UTRIANGLE = 2;
    const MARK_DTRIANGLE = 3;
    const MARK_DIAMOND = 4;
    const MARK_CIRCLE = 5;
    const MARK_FILLEDCIRCLE = 6;
    const MARK_CROSS = 7;
    const MARK_STAR = 8;
    const MARK_X = 9;
    const MARK_LEFTTRIANGLE = 10;
    const MARK_RIGHTTRIANGLE = 11;
    const MARK_FLASH = 12;

    /**
     * Facteur de sur-échantillonnage
     */
    const SCALE = 2;

    private static $_arrFonts = array(
        self::FONT_VERA => 'Vera.ttf',
        self::FONT_VERAMONO => 'VeraMono.ttf',
        self::FONT_VERASERIF => 'VeraSe.ttf',
        self::FONT_DV_SANSSERIF => 'DejaVuSans.ttf',
        self::FONT_DV_SERIF => 'DejaVuSerif.ttf',
        self::FONT_DV_SANSSERIFMONO => 'DejaVuSansMono.ttf',
        self::FONT_DV_SERIFCOND => 'DejaVuSerifCondensed.ttf',
        self::FONT_DV_SANSSERIFCOND => 'DejaVuSansCondensed.ttf',
    );

    private static $_arrNamedColors = array(
        'black' => '000000', 'white' => 'ffffff', 'red' => 'ff0000', 'green' => '008000', 'blue' => '0000ff',
        'yellow' => 'ffff00', 'orange' => 'ffa500', 'gray' => '808080', 'grey' => '808080', 'lightgray' => 'd3d3d3',
        'lightgrey' => 'd3d3d3', 'darkgray' => 'a9a9a9', 'purple' => '800080', 'brown' => 'a52a2a', 'pink' => 'ffc0cb',
        'cyan' => '00ffff', 'magenta' => 'ff00ff', 'navy' => '000080', 'teal' => '008080', 'lime' => '00ff00',
    );

    private $_img;
    private $_intW;
    private $_intH;
    private $_strFont;
    private $_arrColors = array();

    /**
     * Polices disponibles (identifiant => libellé), pour le formulaire d'administration
     */
    public static function getFonts()
    {
        return array(
            self::FONT_VERA => 'Vera',
            self::FONT_VERAMONO => 'Vera Mono',
            self::FONT_VERASERIF => 'Vera Serif',
            self::FONT_DV_SERIF => 'DejaVu Serif',
            self::FONT_DV_SANSSERIF => 'DejaVu Sans',
            self::FONT_DV_SANSSERIFMONO => 'DejaVu Sans Mono',
            self::FONT_DV_SANSSERIFCOND => 'DejaVu Sans Condensed',
            self::FONT_DV_SERIFCOND => 'DejaVu Serif Condensed',
        );
    }

    /**
     * Marqueurs disponibles (identifiant => libellé), pour le formulaire d'administration
     */
    public static function getMarks()
    {
        return array(
            self::MARK_SQUARE => 'Carré',
            self::MARK_CIRCLE => 'Cercle vide',
            self::MARK_FILLEDCIRCLE => 'Cercle plein',
            self::MARK_UTRIANGLE => 'Triangle vers le haut',
            self::MARK_DTRIANGLE => 'Triangle vers le bas',
            self::MARK_LEFTTRIANGLE => 'Triangle vers la gauche',
            self::MARK_RIGHTTRIANGLE => 'Triangle vers la droite',
            self::MARK_DIAMOND => 'Losange',
            self::MARK_CROSS => 'Croix',
            self::MARK_X => 'Croix 2',
            self::MARK_STAR => 'Etoile',
            self::MARK_FLASH => 'Eclair',
        );
    }

    /**
     * @param int $intW largeur finale
     * @param int $intH hauteur finale
     * @param int $intFont identifiant de police (FONT_*), Vera par défaut
     * @param string $strFontDir dossier des polices
     */
    public function __construct($intW, $intH, $intFont, $strFontDir)
    {
        $this->_intW = max(50, min(4000, (int)$intW));
        $this->_intH = max(50, min(4000, (int)$intH));

        $strFile = isset(self::$_arrFonts[(int)$intFont]) ? self::$_arrFonts[(int)$intFont] : self::$_arrFonts[self::FONT_VERA];
        $this->_strFont = rtrim($strFontDir, '/').'/'.$strFile;
        if (!is_readable($this->_strFont)) throw new RuntimeException('police introuvable : '.$strFile);

        $this->_img = imagecreatetruecolor($this->_intW * self::SCALE, $this->_intH * self::SCALE);
        imagealphablending($this->_img, true);
        imagefill($this->_img, 0, 0, $this->color('white'));
    }

    public function __destruct()
    {
        $this->_img = null;
    }

    /* ------------------------------------------------------------------ primitives */

    /**
     * Couleur GD : nom, #rrggbb, rrggbb ; l'opacité vaut 1 (pleine) à 0 (transparente)
     */
    public function color($strColor, $floOpacity = 1)
    {
        $strColor = strtolower(trim((string)$strColor));
        if (isset(self::$_arrNamedColors[$strColor])) $strColor = self::$_arrNamedColors[$strColor];
        $strColor = ltrim($strColor, '#');
        if (!preg_match('/^[0-9a-f]{6}$/', $strColor)) $strColor = '000000';

        $intAlpha = (int)round((1 - max(0, min(1, $floOpacity))) * 127);
        $strKey = $strColor.$intAlpha;
        if (!isset($this->_arrColors[$strKey]))
            $this->_arrColors[$strKey] = imagecolorallocatealpha($this->_img, hexdec(substr($strColor, 0, 2)), hexdec(substr($strColor, 2, 2)), hexdec(substr($strColor, 4, 2)), $intAlpha);

        return $this->_arrColors[$strKey];
    }

    private function s($mixV) { return (int)round($mixV * self::SCALE); }

    public function line($x1, $y1, $x2, $y2, $col, $floWeight = 1)
    {
        imagesetthickness($this->_img, max(1, $this->s($floWeight)));
        imageline($this->_img, $this->s($x1), $this->s($y1), $this->s($x2), $this->s($y2), $col);
        imagesetthickness($this->_img, 1);
    }

    public function rect($x1, $y1, $x2, $y2, $col, $booFill = true)
    {
        if ($booFill) imagefilledrectangle($this->_img, $this->s(min($x1, $x2)), $this->s(min($y1, $y2)), $this->s(max($x1, $x2)), $this->s(max($y1, $y2)), $col);
        else imagerectangle($this->_img, $this->s($x1), $this->s($y1), $this->s($x2), $this->s($y2), $col);
    }

    /**
     * @param array $arrPoints x1, y1, x2, y2… (coordonnées logiques)
     */
    public function polygon($arrPoints, $col, $booFill = true, $floWeight = 1)
    {
        $arrP = array_map(array($this, 's'), $arrPoints);
        if (count($arrP) < 6) return;

        if ($booFill) imagefilledpolygon($this->_img, $arrP, $col);
        else
        {
            imagesetthickness($this->_img, max(1, $this->s($floWeight)));
            imagepolygon($this->_img, $arrP, $col);
            imagesetthickness($this->_img, 1);
        }
    }

    public function polyline($arrPoints, $col, $floWeight = 1)
    {
        for ($i = 2; $i < count($arrPoints); $i += 2) $this->line($arrPoints[$i - 2], $arrPoints[$i - 1], $arrPoints[$i], $arrPoints[$i + 1], $col, $floWeight);
    }

    /**
     * Secteur (ou ellipse entière) plein
     */
    public function pie($cx, $cy, $w, $h, $fltFrom, $fltTo, $col)
    {
        if ($fltTo - $fltFrom <= 0.05) return;
        imagefilledarc($this->_img, $this->s($cx), $this->s($cy), $this->s($w), $this->s($h), (int)round($fltFrom), (int)round($fltTo), $col, IMG_ARC_PIE);
    }

    /**
     * Texte ; $strAlign : left, center, right. Les retours à la ligne sont respectés.
     * Renvoie la largeur du texte (coordonnées logiques).
     */
    public function text($x, $y, $strText, $fltSize, $col, $strAlign = 'left', $fltAngle = 0, $strVAlign = 'top')
    {
        $arrLines = explode("\n", (string)$strText);
        $fltSizePx = $fltSize * self::SCALE;
        $fltLineH = $fltSize * 1.5;
        $fltMax = 0;

        foreach ($arrLines as $intLine => $strLine)
        {
            $arrBox = imagettfbbox($fltSizePx, 0, $this->_strFont, $strLine);
            $fltW = ($arrBox[2] - $arrBox[0]) / self::SCALE;
            $fltMax = max($fltMax, $fltW);
        }

        $fltBlockH = $fltLineH * count($arrLines);
        $fltDy = ($strVAlign == 'middle') ? -$fltBlockH / 2 : (($strVAlign == 'bottom') ? -$fltBlockH : 0);

        foreach ($arrLines as $intLine => $strLine)
        {
            $arrBox = imagettfbbox($fltSizePx, 0, $this->_strFont, $strLine);
            $fltW = ($arrBox[2] - $arrBox[0]) / self::SCALE;
            $fltLx = ($strAlign == 'center') ? -$fltW / 2 : (($strAlign == 'right') ? -$fltW : 0);
            $fltLy = $fltDy + $fltLineH * $intLine + $fltSize * 1.05; // ligne de base

            // Rotation autour du point d'ancrage
            $fltRad = deg2rad(-$fltAngle);
            $fltPx = $x + $fltLx * cos($fltRad) - $fltLy * sin($fltRad);
            $fltPy = $y + $fltLx * sin($fltRad) + $fltLy * cos($fltRad);

            imagettftext($this->_img, $fltSizePx, $fltAngle, $this->s($fltPx), $this->s($fltPy), $col, $this->_strFont, $strLine);
        }

        return $fltMax;
    }

    public function textWidth($strText, $fltSize)
    {
        $fltMax = 0;
        foreach (explode("\n", (string)$strText) as $strLine)
        {
            $arrBox = imagettfbbox($fltSize * self::SCALE, 0, $this->_strFont, $strLine);
            $fltMax = max($fltMax, ($arrBox[2] - $arrBox[0]) / self::SCALE);
        }
        return $fltMax;
    }

    /**
     * Marqueur centré en (x, y)
     */
    public function mark($intType, $x, $y, $fltWidth, $strColor, $floOpacity)
    {
        $col = $this->color($strColor, $floOpacity);
        $line = $this->color($strColor, 1);
        $r = max(1, $fltWidth);

        switch ((int)$intType)
        {
            case self::MARK_CIRCLE:
                imagesetthickness($this->_img, $this->s(1));
                imagearc($this->_img, $this->s($x), $this->s($y), $this->s(2 * $r), $this->s(2 * $r), 0, 360, $line);
                imagesetthickness($this->_img, 1);
            break;

            case self::MARK_FILLEDCIRCLE:
                imagefilledellipse($this->_img, $this->s($x), $this->s($y), $this->s(2 * $r), $this->s(2 * $r), $col);
            break;

            case self::MARK_UTRIANGLE:
                $this->polygon(array($x, $y - $r, $x + $r, $y + $r, $x - $r, $y + $r), $col);
            break;

            case self::MARK_DTRIANGLE:
                $this->polygon(array($x, $y + $r, $x + $r, $y - $r, $x - $r, $y - $r), $col);
            break;

            case self::MARK_LEFTTRIANGLE:
                $this->polygon(array($x - $r, $y, $x + $r, $y - $r, $x + $r, $y + $r), $col);
            break;

            case self::MARK_RIGHTTRIANGLE:
                $this->polygon(array($x + $r, $y, $x - $r, $y - $r, $x - $r, $y + $r), $col);
            break;

            case self::MARK_DIAMOND:
                $this->polygon(array($x, $y - $r, $x + $r, $y, $x, $y + $r, $x - $r, $y), $col);
            break;

            case self::MARK_CROSS:
                $this->line($x - $r, $y, $x + $r, $y, $line, 1.5);
                $this->line($x, $y - $r, $x, $y + $r, $line, 1.5);
            break;

            case self::MARK_X:
                $this->line($x - $r, $y - $r, $x + $r, $y + $r, $line, 1.5);
                $this->line($x - $r, $y + $r, $x + $r, $y - $r, $line, 1.5);
            break;

            case self::MARK_STAR:
                for ($i = 0; $i < 4; $i++)
                {
                    $fltA = $i * M_PI / 4;
                    $this->line($x - $r * cos($fltA), $y - $r * sin($fltA), $x + $r * cos($fltA), $y + $r * sin($fltA), $line, 1);
                }
            break;

            case self::MARK_FLASH:
                $this->polygon(array($x + $r * 0.3, $y - $r, $x - $r * 0.6, $y + $r * 0.1, $x, $y + $r * 0.1, $x - $r * 0.3, $y + $r, $x + $r * 0.6, $y - $r * 0.1, $x, $y - $r * 0.1), $col);
            break;

            case self::MARK_SQUARE:
            default:
                $this->rect($x - $r, $y - $r, $x + $r, $y + $r, $col);
        }
    }

    /* ------------------------------------------------------------------ éléments communs */

    /**
     * Titre centré en haut du graphique
     */
    public function title($strTitle, $fltSize)
    {
        $this->text($this->_intW / 2, 8, $strTitle, $fltSize, $this->color('black'), 'center');
    }

    /**
     * Légende : pastille de couleur + libellé, alignée à droite, sous le titre
     *
     * @param array $arrEntries liste de array('label' =>, 'color' =>, 'opacity' =>)
     */
    public function legend($arrEntries, $fltSize, $fltTop = 40)
    {
        if (empty($arrEntries)) return;

        $fltLineH = $fltSize * 1.9;
        $fltW = 0;
        foreach ($arrEntries as $arrE) $fltW = max($fltW, $this->textWidth($arrE['label'], $fltSize));
        $fltBoxW = $fltW + $fltSize * 2.6 + 10;
        $fltX = $this->_intW - $fltBoxW - 6;

        $this->rect($fltX, $fltTop, $this->_intW - 6, $fltTop + $fltLineH * count($arrEntries) + 6, $this->color('white', 0.9));
        $this->rect($fltX, $fltTop, $this->_intW - 6, $fltTop + $fltLineH * count($arrEntries) + 6, $this->color('808080'), false);

        foreach (array_values($arrEntries) as $i => $arrE)
        {
            $fltY = $fltTop + 4 + $i * $fltLineH;
            $this->rect($fltX + 5, $fltY + 2, $fltX + 5 + $fltSize * 1.4, $fltY + 2 + $fltSize * 1.1, $this->color($arrE['color'], isset($arrE['opacity']) ? $arrE['opacity'] : 1));
            $this->text($fltX + 5 + $fltSize * 2, $fltY, $arrE['label'], $fltSize, $this->color('black'));
        }
    }

    /* ------------------------------------------------------------------ types de graphiques */

    /**
     * Secteurs
     *
     * @param array $arrData libellé => valeur
     * @param array $arrColors couleurs des secteurs (même nombre que de valeurs)
     * @param array $arrOpt font_size_legend, font_size_data, center_x, center_y, percent (bool), depth3d (bool)
     */
    public function drawPie($arrData, $arrColors, $arrOpt)
    {
        $fltTotal = array_sum($arrData);
        if ($fltTotal <= 0) throw new RuntimeException('aucune valeur à représenter');

        $fltCx = $this->_intW * (float)$arrOpt['center_x'];
        $fltCy = $this->_intH * (float)$arrOpt['center_y'];
        $b3d = !empty($arrOpt['depth3d']);

        $fltR = min($this->_intW, $this->_intH) * 0.3;
        $fltRy = $b3d ? $fltR * 0.6 : $fltR;
        $fltDepth = $b3d ? $fltR * 0.25 : 0;

        // Épaisseur : mêmes secteurs, plus sombres, décalés vers le bas
        if ($b3d)
        {
            for ($d = $fltDepth; $d > 0; $d -= 0.5)
            {
                $fltA = -90;
                foreach (array_values($arrData) as $i => $fltV)
                {
                    $fltSweep = 360 * $fltV / $fltTotal;
                    $this->pie($fltCx, $fltCy + $d, 2 * $fltR, 2 * $fltRy, $fltA, $fltA + $fltSweep, $this->color($this->shade($arrColors[$i], 0.7)));
                    $fltA += $fltSweep;
                }
            }
        }

        $fltA = -90;
        $arrMids = array();
        foreach (array_values($arrData) as $i => $fltV)
        {
            $fltSweep = 360 * $fltV / $fltTotal;
            $this->pie($fltCx, $fltCy, 2 * $fltR, 2 * $fltRy, $fltA, $fltA + $fltSweep, $this->color($arrColors[$i]));
            $arrMids[$i] = deg2rad($fltA + $fltSweep / 2);
            $fltA += $fltSweep;
        }

        // Filet blanc entre les secteurs
        $fltA = -90;
        foreach (array_values($arrData) as $i => $fltV)
        {
            $this->line($fltCx, $fltCy, $fltCx + $fltR * cos(deg2rad($fltA)), $fltCy + $fltRy * sin(deg2rad($fltA)), $this->color('white'), 1);
            $fltA += 360 * $fltV / $fltTotal;
        }

        // Valeurs
        foreach (array_values($arrData) as $i => $fltV)
        {
            if ($fltV / $fltTotal < 0.03) continue;
            $strLabel = !empty($arrOpt['percent']) ? round($fltV * 100 / $fltTotal).' %' : (string)$fltV;
            $this->text($fltCx + 0.65 * $fltR * cos($arrMids[$i]), $fltCy + 0.65 * $fltRy * sin($arrMids[$i]), $strLabel, $arrOpt['font_size_data'], $this->color('black'), 'center', 0, 'middle');
        }

        $arrEntries = array();
        foreach (array_keys($arrData) as $i => $strLabel) $arrEntries[] = array('label' => (string)$strLabel, 'color' => $arrColors[$i]);
        $this->legend($arrEntries, $arrOpt['font_size_legend']);
    }

    /**
     * Échelle lisible : borne haute arrondie et pas des graduations
     */
    private function niceScale($fltMax, $intTicks = 6)
    {
        if ($fltMax <= 0) return array(1, 0.2);

        $fltRaw = $fltMax / $intTicks;
        $fltMag = pow(10, floor(log10($fltRaw)));
        foreach (array(1, 2, 2.5, 5, 10) as $fltM)
            if ($fltRaw <= $fltM * $fltMag) { $fltStep = $fltM * $fltMag; break; }

        return array(ceil($fltMax / $fltStep) * $fltStep, $fltStep);
    }

    /**
     * Histogrammes et courbes
     *
     * @param string $strKind bar, barc, line ou linec
     * @param array $arrLabels libellés de l'axe x
     * @param array $arrSeries liste de array('data' => [...], 'color' =>, 'legend' =>)
     * @param array $arrOpt margin_*, font_size_*, label_angle, mark_*, transparency, fill_transparency, shadow*, filled, percent, title_x
     */
    public function drawCartesian($strKind, $arrLabels, $arrSeries, $arrOpt)
    {
        $intN = count($arrLabels);
        if ($intN < 1 || empty($arrSeries)) throw new RuntimeException('aucune donnée à représenter');

        $fltL = (float)$arrOpt['margin_left'];
        $fltR = $this->_intW - (float)$arrOpt['margin_right'];
        $fltT = (float)$arrOpt['margin_top'];
        $fltB = $this->_intH - (float)$arrOpt['margin_bottom'];
        if ($fltR - $fltL < 50 || $fltB - $fltT < 50) throw new RuntimeException('marges trop grandes pour la taille du graphique');

        $fltMax = 0;
        foreach ($arrSeries as $arrS) foreach ($arrS['data'] as $fltV) $fltMax = max($fltMax, $fltV);
        list($fltTop, $fltStep) = $this->niceScale($fltMax);

        $fSize = $arrOpt['font_size_data'];
        $colGrid = $this->color('d0d0d0');
        $colAxis = $this->color('404040');

        $fnY = function($fltV) use ($fltT, $fltB, $fltTop) { return $fltB - ($fltB - $fltT) * $fltV / $fltTop; };
        $fltSlot = ($fltR - $fltL) / $intN;
        $fnX = function($i) use ($fltL, $fltSlot) { return $fltL + $fltSlot * ($i + 0.5); };

        // Grille et graduations
        for ($fltV = 0; $fltV <= $fltTop + $fltStep / 1000; $fltV += $fltStep)
        {
            $fltY = $fnY($fltV);
            $this->line($fltL, $fltY, $fltR, $fltY, $colGrid, 1);
            $this->text($fltL - 6, $fltY, (string)round($fltV, 2), $fSize, $colAxis, 'right', 0, 'middle');
        }

        // Axes
        $this->line($fltL, $fltT, $fltL, $fltB, $colAxis, 1);
        $this->line($fltL, $fltB, $fltR, $fltB, $colAxis, 1);

        // Étiquettes de l'axe x : une sur n si elles se chevauchent
        $fltLabW = 0;
        foreach ($arrLabels as $strLabel) $fltLabW = max($fltLabW, $this->textWidth($strLabel, $fSize));
        $fltAngle = (float)$arrOpt['label_angle'];
        $intEvery = ($fltAngle == 0 && $fltLabW > 0) ? max(1, (int)ceil(($fltLabW + 4) / $fltSlot)) : 1;
        foreach ($arrLabels as $i => $strLabel)
        {
            if ($i % $intEvery) continue;
            $this->line($fnX($i), $fltB, $fnX($i), $fltB + 4, $colAxis, 1);
            if ($fltAngle == 0) $this->text($fnX($i), $fltB + 6, $strLabel, $fSize, $colAxis, 'center');
            else $this->text($fnX($i), $fltB + 6, $strLabel, $fSize, $colAxis, 'right', $fltAngle);
        }

        if (!empty($arrOpt['title_x'])) $this->text(($fltL + $fltR) / 2, $this->_intH - $fSize * 2.2, $arrOpt['title_x'], $fSize, $this->color('black'), 'center');
        if (!empty($arrOpt['percent'])) $this->text($fltL, $fltT - $fSize * 2.2, '%', $fSize, $this->color('black'), 'center');

        // Les séries cumulées se dessinent de la plus haute à la plus basse
        $arrOrder = array_keys($arrSeries);
        if (in_array($strKind, array('barc', 'linec'))) $arrOrder = array_reverse($arrOrder);

        $intSeries = count($arrSeries);
        $fltGroup = $fltSlot * 0.8;

        foreach ($arrOrder as $intIdx)
        {
            $arrS = $arrSeries[$intIdx];
            $strColor = empty($arrS['color']) ? 'black' : $arrS['color'];
            $colLine = $this->color($strColor, 1 - (float)$arrOpt['transparency']);

            if ($strKind == 'bar' || $strKind == 'barc')
            {
                $fltBarW = ($strKind == 'bar') ? $fltGroup / $intSeries : $fltGroup;
                foreach ($arrS['data'] as $i => $fltV)
                {
                    $fltX1 = ($strKind == 'bar') ? $fnX($i) - $fltGroup / 2 + $fltBarW * $intIdx : $fnX($i) - $fltBarW / 2;
                    $fltX2 = $fltX1 + $fltBarW;
                    if (!empty($arrOpt['shadow']) && ($strKind == 'bar' || $intIdx == $intSeries - 1))
                        $this->rect($fltX1 + 3, $fnY($fltV) + 3, $fltX2 + 3, $fltB, $this->color($strColor, 1 - (float)$arrOpt['shadow_transparency']));
                    $this->rect($fltX1, $fnY($fltV), $fltX2, $fltB, $this->color($strColor, empty($arrOpt['filled']) ? 0.45 : 1 - (float)$arrOpt['fill_transparency']));
                    $this->rect($fltX1, $fnY($fltV), $fltX2, $fltB, $colLine, false);
                }
            }
            else
            {
                $arrPts = array();
                foreach ($arrS['data'] as $i => $fltV) { $arrPts[] = $fnX($i); $arrPts[] = $fnY($fltV); }

                if (!empty($arrOpt['filled']) && count($arrPts) >= 4)
                    $this->polygon(array_merge($arrPts, array($fnX($intN - 1), $fltB, $fnX(0), $fltB)), $this->color($strColor, 1 - (float)$arrOpt['fill_transparency']));

                $this->polyline($arrPts, $colLine, 2);
                for ($k = 0; $k < count($arrPts); $k += 2)
                    $this->mark($arrOpt['mark_type'], $arrPts[$k], $arrPts[$k + 1], $arrOpt['mark_width'], $strColor, 1 - (float)$arrOpt['mark_transparency']);
            }
        }

        $arrEntries = array();
        foreach ($arrSeries as $arrS) $arrEntries[] = array('label' => (string)$arrS['legend'], 'color' => empty($arrS['color']) ? 'black' : $arrS['color']);
        $this->legend($arrEntries, $arrOpt['font_size_legend']);
    }

    /**
     * Radars
     */
    public function drawRadar($arrLabels, $arrSeries, $arrOpt)
    {
        $intN = count($arrLabels);
        if ($intN < 3 || empty($arrSeries)) throw new RuntimeException('un radar demande au moins trois valeurs');

        $fSize = $arrOpt['font_size_data'];
        $fltCx = $this->_intW * (float)$arrOpt['center_x'];
        $fltCy = $this->_intH * (float)$arrOpt['center_y'];
        $fltRad = min($this->_intW, $this->_intH) * 0.3;

        $fltMax = 0;
        foreach ($arrSeries as $arrS) foreach ($arrS['data'] as $fltV) $fltMax = max($fltMax, $fltV);
        list($fltTop, $fltStep) = $this->niceScale($fltMax, 5);

        $colGrid = $this->color('d0d0d0');
        $fnPt = function($i, $fltV) use ($fltCx, $fltCy, $fltRad, $fltTop, $intN) {
            $fltA = -M_PI / 2 + 2 * M_PI * $i / $intN;
            return array($fltCx + $fltRad * $fltV / $fltTop * cos($fltA), $fltCy + $fltRad * $fltV / $fltTop * sin($fltA));
        };

        for ($fltV = $fltStep; $fltV <= $fltTop + $fltStep / 1000; $fltV += $fltStep)
        {
            $arrP = array();
            for ($i = 0; $i < $intN; $i++) $arrP = array_merge($arrP, $fnPt($i, $fltV));
            $this->polygon($arrP, $colGrid, false, 1);
            list($fltX, $fltY) = $fnPt(0, $fltV);
            $this->text($fltX + 3, $fltY - $fSize, (string)round($fltV, 2), $fSize * 0.9, $this->color('808080'));
        }

        for ($i = 0; $i < $intN; $i++)
        {
            list($fltX, $fltY) = $fnPt($i, $fltTop);
            $this->line($fltCx, $fltCy, $fltX, $fltY, $colGrid, 1);
            $fltA = -M_PI / 2 + 2 * M_PI * $i / $intN;
            $strAlign = (abs(cos($fltA)) < 0.2) ? 'center' : (cos($fltA) > 0 ? 'left' : 'right');
            $this->text($fltCx + ($fltRad + 8) * cos($fltA), $fltCy + ($fltRad + 8) * sin($fltA), $arrLabels[$i], $fSize, $this->color('404040'), $strAlign, 0, 'middle');
        }

        $arrOrder = array_keys($arrSeries);
        if (!empty($arrOpt['cumulative'])) $arrOrder = array_reverse($arrOrder);

        foreach ($arrOrder as $intIdx)
        {
            $arrS = $arrSeries[$intIdx];
            $strColor = empty($arrS['color']) ? 'black' : $arrS['color'];
            $arrP = array();
            foreach ($arrS['data'] as $i => $fltV) $arrP = array_merge($arrP, $fnPt($i, $fltV));

            if (!empty($arrOpt['filled'])) $this->polygon($arrP, $this->color($strColor, 1 - (float)$arrOpt['fill_transparency']));
            $this->polygon($arrP, $this->color($strColor, 1 - (float)$arrOpt['transparency']), false, 2);
            for ($k = 0; $k < count($arrP); $k += 2) $this->mark($arrOpt['mark_type'], $arrP[$k], $arrP[$k + 1], $arrOpt['mark_width'], $strColor, 1 - (float)$arrOpt['mark_transparency']);
        }

        $arrEntries = array();
        foreach ($arrSeries as $arrS) $arrEntries[] = array('label' => (string)$arrS['legend'], 'color' => empty($arrS['color']) ? 'black' : $arrS['color']);
        $this->legend($arrEntries, $arrOpt['font_size_legend']);
    }

    /**
     * Assombrit une couleur hexadécimale (facteur 0..1)
     */
    private function shade($strColor, $fltFactor)
    {
        $strColor = ltrim(trim((string)$strColor), '#');
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $strColor)) return '000000';
        return sprintf('%02x%02x%02x', hexdec(substr($strColor, 0, 2)) * $fltFactor, hexdec(substr($strColor, 2, 2)) * $fltFactor, hexdec(substr($strColor, 4, 2)) * $fltFactor);
    }

    /**
     * PNG final, réduit à la taille demandée
     *
     * @return string contenu du fichier PNG
     */
    public function png()
    {
        $resOut = imagecreatetruecolor($this->_intW, $this->_intH);
        imagecopyresampled($resOut, $this->_img, 0, 0, 0, 0, $this->_intW, $this->_intH, $this->_intW * self::SCALE, $this->_intH * self::SCALE);

        ob_start();
        imagepng($resOut);
        $strPng = ob_get_clean();
        unset($resOut);

        return $strPng;
    }
}
