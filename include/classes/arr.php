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

namespace ploopi;

use ploopi;
use LSS\Array2XML;

/**
 * Opérations sur les tableaux
 *
 * @package ploopi
 * @subpackage array
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class arr
{

    /**
     * Applique récursivement une fonction sur les éléments d'un tableau
     * Les éléments peuvent être des tableaux récursifs ou des objets récursifs
     *
     * @param callback $func fonction à appliquer sur le tableau
     * @param array $var variable à modifier
     * @return array le tableau modifié
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     *
     * @see array_map
     */

    public static function map($func, $var)
    {
        if (is_array($var)) { foreach($var as $key => $value) $var[$key] = self::map($func, $value); return $var; }
        elseif (is_object($var)) { foreach(get_object_vars($var) as $key => $value)  $var->$key = self::map($func, $value); return $var; }
        else return call_user_func($func, $var);
    }

    /**
     * Retourne le contenu d'un tableau multidimensionnel au format JSON
     *
     * @param array $arrArray tableau de données
     * @param boolean $booForceUTF8 true si les données doivent être convertie en UTF-8 (depuis ISO-8859-1)
     * @return string contenu JSON
     */

    public static function tojson($arrArray, $booForceUTF8 = true)
    {
        return json_encode($booForceUTF8 ? self::map(function($str) { return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1'); },self::cleankeys($arrArray)) : self::cleankeys($arrArray));
    }

    /**
     * Retourne le contenu d'un tableau multidimensionnel au format XML
     *
     * @param array $arrArray tableau de données
     * @param string $strRootName nom du noeud racine
     * @param string $strDefaultTagName nom des noeuds 'anonymes'
     * @param string $strEncoding charset utilisé
     * @return string contenu XML
     */

    public static function toxml_deprecated($arrArray, $strRootName = 'data', $strDefaultTagName = 'row', $strEncoding = 'ISO-8859-1')
    {
        $arrArray = self::map(function($str) { return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1'); }, $arrArray);

        Array2XML::init('1.0', $strEncoding);
        $xml = Array2XML::createXML($strRootName, array($strDefaultTagName => $arrArray));
        return $xml->saveXML();
    }

    /**
     * Retourne le contenu d'un tableau multidimensionnel au format XML
     *
     * @param array $arrData tableau de données
     * @param SimpleXMLElement $xml_data objet XML en cours de construction (laisser vide)
     * @param string $parent noeud parent (laisser vide)
     * @return string contenu XML
     */

    public static function toxml($arrData) {
        return self::_toxml($arrData)->asXML();
    }

    /**
     * Construit un objet XML à partir d'un tableau de données
     *
     * @param array $arrData tableau de données
     * @param SimpleXMLElement $xml_data objet XML en cours de construction
     * @param string $parent noeud parent
     * @return SimpleXMLElement objet XML
     */

    private static function _toxml($arrData, $xml_data = null, $parent = null) {
        if (is_null($xml_data)) {
            $key = 'root';
            $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><'.$key.'></'.$key.'>');
            self::_toxml($arrData, $xml_data);

            return $xml_data;
        }

        foreach($arrData as $key => $value ) {
            if (substr($key, 0, 1) === '@') {
                $xml_data->addAttribute(substr($key, 1), htmlspecialchars($value));
            }
            else {
                if(is_array($value)) {
                    if(is_numeric($key)){
                        $key = empty($parent) ? 'row' : $parent;
                        $subnode = $xml_data->addChild($key);
                        self::_toxml($value, $subnode);
                    }
                    else {
                        if (is_array($value) && key($value) === 0) {
                            self::_toxml($value, $xml_data, $key);
                        }
                        else {
                            $subnode = $xml_data->addChild($key);
                            self::_toxml($value, $subnode);
                        }
                    }
                } else {
                    $xml_data->addChild($key, htmlspecialchars($value));
                }
            }
        }

        return $xml_data;
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format CSV
     *
     * @param array $arrArray tableau de données
     * @param array $arrOptions options du format CSV : booHeader:true si la ligne d'entête doit être ajoutée (nom des colonnes), strFieldSep:séparateur de champs, strLineSep:séparateur de lignes, strTextSep:caractère d'encapsulation des contenus
     * @param array $arrTitles nom des colonnes (optionnel)
     * @return string contenu CSV
     */

    public static function tocsv($arrArray, $arrOptions = array())
    {
        $arrDefaultOptions = array(
            'booHeader' => true,
            'strFieldSep' => ',',
            'strLineSep' => "\n",
            'strTextSep' => '"',
            'booClean' => true
        );

        $arrOptions = array_merge($arrDefaultOptions, $arrOptions);

        // Tableau des lignes du fichier CSV
        $arrCSV = array();

        if (!empty($arrArray))
        {
            if ($arrOptions['booClean']) $arrArray = self::map(array(__NAMESPACE__.'\\str', 'iso8859_clean'), $arrArray);

            // Fonction d'échappement & formatage du contenu
            $funcLineEchap = null;

            if ($arrOptions['strTextSep'] != '') {
                $funcLineEchap = function($value) use($arrOptions) { 
                    return $arrOptions['strTextSep'].str_replace($arrOptions['strTextSep'], $arrOptions['strTextSep'].$arrOptions['strTextSep'], $value).$arrOptions['strTextSep'];
                };
            } elseif ($arrOptions['strFieldSep'] != '') {
                $funcLineEchap = function($value) use($arrOptions) { 
                    return str_replace($arrOptions['strFieldSep'], '\\'.$arrOptions['strFieldSep'], $value);
                };
            }

            // Ajout de la ligne d'entête
            if ($arrOptions['booHeader']) $arrCSV[] = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? array_keys(reset($arrArray)) : self::map($funcLineEchap, array_keys(reset($arrArray))));

            // Traitement des contenus
            foreach($arrArray as $row) $arrCSV[] = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? $row : self::map($funcLineEchap, $row));
        }

        // contenu CSV
        return implode($arrOptions['strLineSep'], $arrCSV).$arrOptions['strLineSep'];
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format HTML
     *
     * @param unknown_type $arrArray
     * @param unknown_type $booHeader
     * @param unknown_type $strClassName
     * @return unknown
     */

    public static function tohtml($arrArray, $booHeader = true, $strClassName = 'ploopi_array')
    {
        // Tableau des lignes
        $arrHTML = array();

        if (!empty($arrArray))
        {
            // Ajout de la ligne d'entête
            if ($booHeader) $arrHTML[] = '<tr>'.implode('', self::map(function($value) { return '<th>'.str::htmlentities($value).'</th>'; }, array_keys(reset($arrArray)))).'</tr>';

            // Traitement des contenus
            foreach($arrArray as $row) $arrHTML[] = '<tr>'.implode('', self::map(function($value) { return '<td>'.str::htmlentities($value).'</td>'; }, $row)).'</tr>';
        }

        // contenu HTML
        return '<table class="'.$strClassName.'">'.implode('', $arrHTML).'</table>';
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format XLS (ISO-8859-1)
     *
     * @param array $arrArray tableau de données
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFileName nom du fichier
     * @param string $strSheetName nom de la feuille dans le document XLS
     * @param array $arrDataFormats formats des colonnes ('title', 'type', 'width')
     * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'tofile', 'setborder', 'textwrap')
     * @return binary contenu XLS
     */
    public static function toxls($arrArray, $booHeader = true, $strFileName = 'document.xls', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
    {
        $arrDefautOptions = array(
            'landscape' => true,
            'fitpage_width' => true,
            'fitpage_height' => false,
            'tofile' => false,
            'setborder' => false,
            'textwrap' => true,
            'header' => []
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);

        // Création du document
        if ($arrOptions['tofile']) $objWorkBook = new \Spreadsheet_Excel_Writer($strFileName);
        else { $objWorkBook = new \Spreadsheet_Excel_Writer(); $objWorkBook->send($strFileName); }


        $objFormatTitle = $objWorkBook->addFormat( array( 'Align' => 'center', 'TextWrap' => 1, 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter', 'FgColor' => 'silver'));
        if ($arrOptions['setborder']) { $objFormatTitle->setBorder(1); $objFormatTitle->setBorderColor('black'); }
        if ($arrOptions['textwrap']) { $objFormatTitle->setTextWrap(); }
        $objFormatDefault = $objWorkBook->addFormat( array( 'TextWrap' => 1, 'Align' => 'left', 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
        if ($arrOptions['setborder']) { $objFormatDefault->setBorder(1); $objFormatDefault->setBorderColor('black'); }
        if ($arrOptions['textwrap']) { $objFormatDefault->setTextWrap(); }

        // Définition des différents formats numériques/text
        $arrFormats = array(
            'string' => null,
            'float' => null,
            'float_percent' => null,
            'float_euro' => null,
            'integer' => null,
            'integer_percent' => null,
            'integer_euro' => null,
            'date' => null,
            'datetime' => null
        );

        foreach($arrFormats as $strKey => &$objFormat)
        {
            $objFormat = $objWorkBook->addFormat( array( 'Align' => 'right', 'TextWrap' => 1, 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
            if ($arrOptions['setborder']) { $objFormat->setBorder(1); $objFormat->setBorderColor('black'); }
            if ($arrOptions['textwrap']) { $objFormat->setTextWrap(); }

            switch($strKey)
            {
                case 'string': $objFormat->setAlign('left'); break;
                case 'float': $objFormat->setNumFormat('#,##0.00;-#,##0.00'); break;
                case 'float_percent': $objFormat->setNumFormat('#,##0.00 %;-#,##0.00 %'); break;
                case 'float_euro': $objFormat->setNumFormat(utf8_decode('#,##0.00 ;-#,##0.00 ')); break;
                case 'integer': $objFormat->setNumFormat('#,##0;-#,##0'); break;
                case 'integer_percent': $objFormat->setNumFormat('#,##0 %;-#,##0 %'); break;
                case 'integer_euro': $objFormat->setNumFormat(utf8_decode('#,##0 ;-#,##0 ')); break;
                case 'date': $objFormat->setNumFormat('DD/MM/YYYY'); break;
                case 'datetime' : $objFormat->setNumFormat('DD/MM/YYYY HH:MM:SS'); break;
            }
        }
        unset($objFormat);

        $objWorkSheet = $objWorkBook->addWorksheet($strSheetName);
        /*
        $objWorkBook->setVersion(8);
        $objWorkSheet->setInputEncoding('UTF-8');
        */

        if ($arrOptions['fitpage_width'] || $arrOptions['fitpage_height']) $objWorkSheet->fitToPages($arrOptions['fitpage_width'] ? 1 : 0, $arrOptions['fitpage_height'] ? 1 : 0);
        if ($arrOptions['landscape']) $objWorkSheet->setLandscape();

        if (!empty($arrArray))
        {
            // Définition des formats de colonnes
            if (!empty($arrDataFormats))
            {
                $intCol = 0;
                foreach(array_keys(reset($arrArray)) as $strKey)
                {
                    if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->setColumn($intCol, $intCol, $arrDataFormats[$strKey]['width']);
                    $intCol++;
                }
            }

            $intLine = 0;

            if (!empty($arrOptions['header'])) {
                foreach($arrOptions['header'] as $row) {
                    $intCol = 0;
                    foreach($row as $strValue) $objWorkSheet->writeString($intLine, $intCol++, iconv('UTF-8', 'CP1252', $strValue), $objFormatDefault);
                    $intLine++;
                }
            }

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = 0;
                foreach(array_keys(reset($arrArray)) as $strKey) $objWorkSheet->writeString($intLine, $intCol++, isset($arrDataFormats[$strKey]['title']) ? iconv('UTF-8', 'CP1252', $arrDataFormats[$strKey]['title']) : iconv('UTF-8', 'CP1252', $strKey), $objFormatTitle);
                $intLine++;
            }

            $idcolor = 15;
            $arrColors = [];

            // Traitement des contenus
            foreach($arrArray as $row)
            {
                $intCol = 0;
                foreach($row as $strKey => $strValue)
                {
                    $bgcolor = '';
                    if (is_array($strValue)) {
                        $value = '';
                        if (isset($strValue['value'])) $value = $strValue['value'];
                        if (isset($strValue['bgcolor'])) $bgcolor = $strValue['bgcolor'];
                        $strValue = $value;
                    }

                    if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                    $k = is_array($bgcolor) ? $strKey.implode('_', $bgcolor) : $strKey.$bgcolor;

                    if (!isset($arrFormats[$k])) {
                        $objFormat = $objWorkBook->addFormat( array( 'Align' => 'right', 'TextWrap' => 1, 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
                        if ($arrOptions['setborder']) { $objFormat->setBorder(1); $objFormat->setBorderColor('black'); }
                        if ($arrOptions['textwrap']) { $objFormat->setTextWrap(); }

                        if (!empty($bgcolor)) {
                            if (is_array($bgcolor)) {
                                $colorcode = implode(',', $bgcolor);
                                if (!isset($arrColors[$colorcode])) $arrColors[$colorcode] = ++$idcolor;

                                $id = $arrColors[$colorcode];

                                $objWorkBook->setCustomColor(++$id, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
                                $objFormat->setFgColor($id);
                            }
                            else $objFormat->setFgColor($bgcolor);
                        }

                        switch($arrDataFormats[$strKey]['type'])
                        {
                            case 'float': $objFormat->setNumFormat('#,##0.00;-#,##0.00'); break;
                            case 'float_percent': $objFormat->setNumFormat('#,##0.00 %;-#,##0.00 %'); break;
                            case 'float_euro': $objFormat->setNumFormat(utf8_decode('#,##0.00 ;-#,##0.00 ')); break;
                            case 'integer': $objFormat->setNumFormat('#,##0;-#,##0'); break;
                            case 'integer_percent': $objFormat->setNumFormat('#,##0 %;-#,##0 %'); break;
                            case 'integer_euro': $objFormat->setNumFormat(utf8_decode('#,##0 ;-#,##0 ')); break;
                            case 'date': $objFormat->setNumFormat('DD/MM/YYYY'); break;
                            case 'datetime' : $objFormat->setNumFormat('DD/MM/YYYY HH:MM:SS'); break;
                            case 'string': default: $objFormat->setAlign('left'); break;
                        }

                        $arrFormats[$k] = $objFormat;
                    }
                    else $objFormat = $arrFormats[$k];


                    switch($arrDataFormats[$strKey]['type'])
                    {
                        case 'float':
                        case 'float_percent':
                        case 'float_euro':
                        case 'integer':
                        case 'integer_percent':
                        case 'integer_euro':
                        case 'date':
                        case 'datetime':
                            if ($strValue !== '') $objWorkSheet->writeNumber($intLine, $intCol, $strValue, $objFormat);
                        break;

                        default:
                            $objWorkSheet->writeString($intLine, $intCol, iconv('UTF-8', 'CP1252', $strValue), $objFormat);
                        break;
                    }
                    $intCol++;
                }
                $intLine++;
            }
        }

        // fermeture du document
        $objWorkBook->close();

        return true;
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions aux formats XLSX / XLS
     *
     * @param array $arrArray tableau de données
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFileName nom du fichier
     * @param string $strSheetName nom de la feuille dans le document XLS
     * @param array $arrDataFormats formats des colonnes ('title', 'type', 'width')
     * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'tofile', 'setborder', 'writer', 'headers')
     * @return binary contenu XLS
     */

    public static function toexcel($arrArray, $booHeader = true, $strFileName = 'document.xlsx', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
    {
        $objWorkBook = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $objWorkSheet = $objWorkBook->getActiveSheet();

        $arrDefautOptions = array(
            'landscape' => true,
            'fitpage_width' => true,
            'fitpage_height' => false,
            'tofile' => false,
            'setborder' => false,
            'writer' => 'excel2007' // excel2007, excel5, csv, html, pdf (instable)
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);


        // Uniformisation des formats
        if (!empty($arrArray)) {
            foreach(array_keys(reset($arrArray)) as $strKey) {
                if (!isset($arrDataFormats[$strKey])) {
                    $arrDataFormats[$strKey] = array(
                        'type' => '',
                        'title' => $strKey
                    );
                }
            }
        }

        foreach($arrDataFormats as $strKey => $row) {
            if (!isset($row['type'])) $arrDataFormats[$strKey]['type'] = '';
            if (!isset($row['title'])) $arrDataFormats[$strKey]['title'] = $strKey;
        }


        // Style par défaut pour toute la feuille
        $rowDefaultStyle = array(
            'font'  => array(
                'bold'  => false,
                'size'  => 10,
                'name'  => 'Arial',
                'color' => array('rgb' => '000000')
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap' => true,
                'shrinkToFit' => true,
            )
        );

        // Style titre
        $rowTitleStyle = array(
            'font'  => array(
                'bold'  => true,
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap' => true,
                //'shrinkToFit' => true,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color'=> array('rgb' => 'DDDDDD')
            )
        );

        // Bordure optionnelle sur le titre
        if ($arrOptions['setborder']) {

            $rowTitleStyle['borders'] = array(
                'allborders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            );

        }

        // Style par défaut pour la feuille
        $objWorkSheet->getParent()->getDefaultStyle()->applyFromArray($rowDefaultStyle);
        $objWorkSheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

        // Titre
        $objWorkSheet->setTitle($strSheetName);

        // Fit to page
        $objWorkSheet->getPageSetup()->setFitToWidth($arrOptions['fitpage_width']);
        $objWorkSheet->getPageSetup()->setFitToHeight($arrOptions['fitpage_height']);
        $objWorkSheet->getDefaultRowDimension()->setRowHeight(-1);

        // Paysage
        if ($arrOptions['landscape']) $objWorkSheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        if (!empty($arrArray))
        {
            $intLineMax = sizeof($arrArray)+1;

            $intLine = 1;
            $intMaxCol = sizeof(array_keys(reset($arrArray)))-1;
            // Calcul colonne type Excel "Bijective base-26"
            $chrMaxCol = ($intMaxCol>25 ? chr(64+floor($intMaxCol/26)) : '').chr(65+$intMaxCol%26);

            if (!empty($arrOptions['headers'])) {
                foreach($arrOptions['headers'] as $strHeader) {

                    $objWorkSheet->setCellValueByColumnAndRow(0, $intLine, $strHeader);

                    $objWorkSheet->getStyle("A{$intLine}:{$chrMaxCol}{$intLine}")->applyFromArray($rowTitleStyle);
                    $objWorkSheet->mergeCells("A{$intLine}:{$chrMaxCol}{$intLine}");
                    //On fusionne la plage
                    $intLine++;
                }
            }

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = 1;
                foreach(array_keys(reset($arrArray)) as $strKey) $objWorkSheet->setCellValueByColumnAndRow($intCol++, $intLine, $arrDataFormats[$strKey]['title']);


                $intCol -=2;

                // Calcul colonne type Excel "Bijective base-26"
                $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                $objWorkSheet->getStyle("A{$intLine}:{$chrCol}{$intLine}")->applyFromArray($rowTitleStyle);

                $objWorkSheet->getRowDimension(1)->setRowHeight(24);

                $intLine++;
            }

            // Traitement des contenus
            foreach($arrArray as $row)
            {
                $intCol = 0;
                foreach($row as $strKey => $mixValue)
                {
                    if (is_array($mixValue)) {
                        $strValue = &$mixValue['content'];
                    }
                    else $strValue = &$mixValue;
                    // Conversion date
                    if (isset($arrDataFormats[$strKey]['type']) && in_array($arrDataFormats[$strKey]['type'], array('datetime', 'date'))) {
                        $strValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime(date('Y-m-d H:i:s', $strValue)));
                    }

                    // Calcul colonne type Excel "Bijective base-26"
                    $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);


                    switch($arrDataFormats[$strKey]['type']) {
                        case 'string':
                            $objWorkSheet->setCellValueExplicit("{$chrCol}{$intLine}", $strValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        break;

                        default:
                            $objWorkSheet->setCellValueByColumnAndRow($intCol+1, $intLine, $strValue);
                        break;
                    }

                    if (is_array($mixValue)) {
                        if (isset($mixValue['style'])) {
                            $objWorkSheet->getStyle("{$chrCol}{$intLine}")->applyFromArray($mixValue['style']);
                        }
                    }

                    $intCol++;
                }

                $intLine++;
            }


            $objWorkSheet->getDefaultRowDimension()->setRowHeight(-1);
            //$objWorkSheet->getColumnDimension(A)->setAutoSize(true);
            // Traitement des contenus
            $intCol = 0;


            // Mise en forme des données
            foreach(array_keys(reset($arrArray)) as $strKey)
            {
                if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                // Calcul colonne type Excel "Bijective base-26"
                $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                $rowStyle = $rowDefaultStyle;

                // Bordure optionnelle
                if ($arrOptions['setborder']) {

                    $rowStyle['borders'] = array(
                        'outline' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        )
                    );

                }

                // Application du format
                switch($arrDataFormats[$strKey]['type']) {

                    case 'float': $strFormat = '0.00'; break;
                    case 'float_percent': $strFormat = '0.00%'; break;
                    case 'float_euro': $strFormat = '#,##0.00_-[$EUR]'; break;
                    case 'integer': $strFormat = '0'; break;
                    case 'integer_percent': $strFormat = '0%'; break;
                    case 'integer_euro': $strFormat = '#,##0_-[$EUR]'; break;
                    case 'date': $strFormat = 'dd/mm/yyyy'; break;
                    case 'datetime' : $strFormat = 'dd/mm/yyyy hh:mm:ss'; break;

                    default:
                    case 'string': $strFormat = 'General'; break;

                }

                // Alignement à droite des dates, valeurs numériques
                if ($arrDataFormats[$strKey]['type'] != 'string') {
                    $rowStyle['alignment'] = array(
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                    );
                }


                // Application du style sur la colonne
                $objWorkSheet->getStyle("{$chrCol}2:{$chrCol}{$intLineMax}")->applyFromArray($rowStyle);
                // Application du format sur la colonne
                $objWorkSheet->getStyle("{$chrCol}2:{$chrCol}{$intLineMax}")->getNumberFormat()->applyFromArray( [ 'formatCode' => $strFormat ] );

                //echo "{$chrCol}2:{$chrCol}{$intLineMax}";
                //ploopi\output::print_r($rowStyle);

                // Largeur de colonne
                if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->getColumnDimension($chrCol)->setWidth($arrDataFormats[$strKey]['width']);

                $intCol++;

            }
        }

        //die();


        // Sélection du writer
        switch($arrOptions['writer']) {

            case 'excel5':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($objWorkBook);
            break;

            case 'pdf':
                $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objWorkBook, 'Mpdf');
                //$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Mpdf($objWorkBook);
                $objWriter->setSheetIndex(0);
            break;

            case 'csv':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($objWorkBook);
                $objWriter = new \PHPExcel_Writer_CSV($objWorkBook);
                $objWriter->setSheetIndex(0);
                $writer->setDelimiter(",");
            break;

            case 'html':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($objWorkBook);
                $objWriter->setSheetIndex(0);
            break;

            case 'ods':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($objWorkBook);
            break;

            case 'excel2007':
            default:
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objWorkBook);
                //$objWriter = new \PHPExcel_Writer_Excel2007($objWorkBook);
                $objWriter->setOffice2003Compatibility(true);
            break;
        }

        // Création du document
        if ($arrOptions['tofile']) {
            $objWriter->save($strFileName);
        }
        else {
            ob_start();
            // Les headers ne sont pas envoyés
            $objWriter->save('php://output');
            return ob_get_clean();
        }

        return true;
    }


/*

    {
        $objWorkBook = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $objWorkSheet = $objWorkBook->getActiveSheet();

        $arrDefautOptions = array(
            'landscape' => true,
            'fitpage_width' => true,
            'fitpage_height' => false,
            'tofile' => false,
            'setborder' => true,
            'writer' => 'excel2007' // excel2007, excel5, csv, html, pdf (instable)
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);


        // Uniformisation des formats
        if (!empty($arrArray)) {
            foreach(array_keys(reset($arrArray)) as $strKey) {
                if (!isset($arrDataFormats[$strKey])) {
                    $arrDataFormats[$strKey] = array(
                        'type' => '',
                        'title' => $strKey
                    );
                }
            }
        }

        foreach($arrDataFormats as $strKey => $row) {
            if (!isset($row['type'])) $arrDataFormats[$strKey]['type'] = '';
            if (!isset($row['title'])) $arrDataFormats[$strKey]['title'] = $strKey;
        }


        // Style par défaut pour toute la feuille
        $rowDefaultStyle = array(
            'font'  => array(
                'bold'  => false,
                'size'  => 10,
                'name'  => 'Arial',
                'color' => array('argb' => '000000')
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap' => true,
                //'shrinkToFit' => false,
            )
        );

        // Style titre
        $rowTitleStyle = array(
            'font'  => array(
                'bold'  => true,
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap' => true,
                //'shrinkToFit' => false,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color'=> array('rgb' => 'DDDDDD')
            )
        );

        // Bordure optionnelle sur le titre
        if ($arrOptions['setborder']) {

            $rowTitleStyle['borders'] = array(
                'outline' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            );

        }

        // Style par défaut pour la feuille
        $objWorkSheet->getParent()->getDefaultStyle()->applyFromArray($rowDefaultStyle);
        $objWorkSheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

        // Titre
        $objWorkSheet->setTitle($strSheetName);

        // Fit to page
        $objWorkSheet->getPageSetup()->setFitToWidth($arrOptions['fitpage_width']);
        $objWorkSheet->getPageSetup()->setFitToHeight($arrOptions['fitpage_height']);
        $objWorkSheet->getDefaultRowDimension()->setRowHeight(-1);

        // Paysage
        if ($arrOptions['landscape']) $objWorkSheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        if (!empty($arrArray))
        {
            $intLineMax = sizeof($arrArray)+1;

            $intLine = 1;
            $intMaxCol = sizeof(array_keys(reset($arrArray)))-1;
            // Calcul colonne type Excel "Bijective base-26"
            $chrMaxCol = ($intMaxCol>25 ? chr(64+floor($intMaxCol/26)) : '').chr(65+$intMaxCol%26);

            if (!empty($arrOptions['headers'])) {
                foreach($arrOptions['headers'] as $strHeader) {

                    $objWorkSheet->setCellValueByColumnAndRow(1, $intLine, $strHeader);

                    $objWorkSheet->getStyle("A{$intLine}:{$chrMaxCol}{$intLine}")->applyFromArray($rowTitleStyle);
                    $objWorkSheet->mergeCells("A{$intLine}:{$chrMaxCol}{$intLine}");
                    //On fusionne la plage
                    $intLine++;
                }
            }

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = -1;
                foreach(array_keys(reset($arrArray)) as $strKey) $objWorkSheet->setCellValueByColumnAndRow(++$intCol, $intLine, $arrDataFormats[$strKey]['title']);

                // Calcul colonne type Excel "Bijective base-26"
                $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                $objWorkSheet->getStyle("A{$intLine}:{$chrCol}{$intLine}")->applyFromArray($rowTitleStyle);

                $objWorkSheet->getRowDimension(1)->setRowHeight(24);

                $intLine++;
            }

            // Traitement des contenus
            foreach($arrArray as $row)
            {
                $intCol = 0;
                foreach($row as $strKey => $mixValue)
                {
                    if (is_array($mixValue)) {
                        $strValue = &$mixValue['content'];
                    }
                    else $strValue = &$mixValue;
                    // Conversion date
                    if (isset($arrDataFormats[$strKey]['type']) && in_array($arrDataFormats[$strKey]['type'], array('datetime', 'date'))) {
                        $strValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($strValue + date('Z', $strValue));
                    }

                    // Calcul colonne type Excel "Bijective base-26"
                    $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                    switch($arrDataFormats[$strKey]['type']) {
                        case 'string':
                            $objWorkSheet->setCellValueExplicit("{$chrCol}{$intLine}", $strValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        break;

                        default:
                            $objWorkSheet->setCellValueByColumnAndRow($intCol, $intLine, $strValue);
                        break;
                    }

                    if (is_array($mixValue)) {
                        if (isset($mixValue['style'])) {
                            $objWorkSheet->getStyle("{$chrCol}{$intLine}")->applyFromArray($mixValue['style']);
                        }
                    }

                    $intCol++;
                }

                $intLine++;
            }

            $objWorkSheet->getDefaultRowDimension()->setRowHeight(-1);

            // Traitement des contenus
            $intCol = 0;


            // Mise en forme des données
            foreach(array_keys(reset($arrArray)) as $strKey)
            {
                if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                // Calcul colonne type Excel "Bijective base-26"
                $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                $rowStyle = $rowDefaultStyle;

                // Bordure optionnelle
                if ($arrOptions['setborder']) {

                    $rowStyle['borders'] = array(
                        'outline' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        )
                    );

                }

                // Application du format
                switch($arrDataFormats[$strKey]['type']) {

                    case 'float': $strFormat = '0.00'; break;
                    case 'float_percent': $strFormat = '0.00%'; break;
                    case 'float_euro': $strFormat = '#,##0.00_-[$EUR]'; break;
                    case 'integer': $strFormat = '0'; break;
                    case 'integer_percent': $strFormat = '0%'; break;
                    case 'integer_euro': $strFormat = '#,##0_-[$EUR]'; break;
                    case 'date': $strFormat = 'dd/mm/yyyy'; break;
                    case 'datetime' : $strFormat = 'dd/mm/yyyy hh:mm:ss'; break;

                    default:
                    case 'string': $strFormat = 'General'; break;

                }

                // Impact du format de donnée sur le style
                $rowStyle['numberformat'] = array(
                    'code' => $strFormat
                );

                // Alignement à droite des dates, valeurs numériques
                if ($arrDataFormats[$strKey]['type'] != 'string') {
                    $rowStyle['alignment'] = array(
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    );
                }

                // Application du style sur la colonne
                $objWorkSheet->getStyle("{$chrCol}2:{$chrCol}{$intLineMax}")->applyFromArray($rowStyle);

                // Largeur de colonne
                if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->getColumnDimension($chrCol)->setWidth($arrDataFormats[$strKey]['width']);
                // Largeur de colonne automatique
                else $objWorkSheet->getColumnDimension($chrCol)->setAutoSize(true);

                $intCol++;

            }
        }


        // Sélection du writer
        switch($arrOptions['writer']) {

            case 'excel5':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($objWorkBook);
            break;

            case 'pdf':
                $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objWorkBook, 'Mpdf');
                //$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Mpdf($objWorkBook);
                $objWriter->setSheetIndex(0);
            break;

            case 'csv':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($objWorkBook);
                $objWriter = new \PHPExcel_Writer_CSV($objWorkBook);
                $objWriter->setSheetIndex(0);
                $writer->setDelimiter(",");
            break;

            case 'html':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($objWorkBook);
                $objWriter->setSheetIndex(0);
            break;

            case 'ods':
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($objWorkBook);
            break;

            case 'excel2007':
            default:
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objWorkBook);
                //$objWriter = new \PHPExcel_Writer_Excel2007($objWorkBook);
                $objWriter->setOffice2003Compatibility(true);
            break;

        }

        // Création du document
        if ($arrOptions['tofile']) {
            $objWriter->save($strFileName);
        }
        else {
            ob_start();
            // Les headers ne sont pas envoyés
            $objWriter->save('php://output');
            return ob_get_clean();
        }

        return true;
    }

*/

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format ODS
     *
     * @param array $arrArray tableau de données
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFileName nom du fichier
     * @param string $strSheetName nom de la feuille dans le document ODS
     * @param array $arrDataFormats formats des colonnes ('type', 'width') // EXPERIMENTAL ou NON IMPLEMENTE
     * @param array $arrOptions Options de configuration de l'export ('tofile')
     * @return binary contenu ODS
     */


    public static function toods($arrArray, $booHeader = true, $strFileName = 'document.ods', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
    {
        require_once './lib/ods/ods.php';

        $objOds = newOds(); //create a new ods file

        $arrDefautOptions = array(
            'tofile' => false,
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);


        if (!empty($arrArray))
        {
            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = 0;
                foreach(array_keys(reset($arrArray)) as $strKey) $objOds->addCell($strSheetName, 0, $intCol++, isset($arrDataFormats[$strKey]['title']) ? $arrDataFormats[$strKey]['title'] : $strKey, 'string');
            }
            // Traitement des contenus
            $intLine = 1;
            foreach($arrArray as $row)
            {
                $intCol = 0;
                foreach($row as $strKey => $strValue)
                {
                    if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                    switch($arrDataFormats[$strKey]['type'])
                    {
                        case 'float':
                            $objOds->addCell($strSheetName, $intLine, $intCol++, $strValue, 'float');
                        break;

                        default:
                            $objOds->addCell($strSheetName, $intLine, $intCol++, $strValue, 'string');
                        break;
                    }
                }
                $intLine++;
            }
        }

        // Génération du document
        if ($arrDefautOptions['tofile']) $strFile = $strFileName;
        else $strFile = tempnam(sys_get_temp_dir(), 'ods').'.ods';

        saveOds($objOds, $strFile);
        if (!$arrDefautOptions['tofile']) {

            header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
            header('Content-disposition: inline; filename="'.$strFileName.'"');
            header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
            header('Accept-Ranges: bytes');
            header('Cache-control: private');
            header('Pragma: private');
            header('Content-length: '.filesize($strFile));
            header('Content-Encoding: None');
            readfile($strFile);
        }

        return true;
    }


    /**
     * "Nettoie" les clés d'un tableau multidimensionnel afin que les clés soient compatibles avec des noms d'entités ou de variables
     *
     * @param array $arrArray tableau à nettoyer
     * @return array tableau nettoyé
     */
    public static function cleankeys($arrArray)
    {
        if (!is_array($arrArray)) return $arrArray;

        $arrNewArray = array();

        foreach($arrArray as $strKey => $mixValue)
        {
            $strKey = preg_replace("/[^a-z0-9_]/", "_", strtolower(str::convertaccents($strKey)));

            // Cas particulier des clés non conformes
            if (strlen($strKey) == 0) $strKey = 'xml';
            elseif (substr($strKey,0,1) == '_') $strKey = 'xml'.$strKey;

            if (is_array($mixValue)) $arrNewArray[$strKey] = self::cleankeys($mixValue);
            else $arrNewArray[$strKey] = $mixValue;
        }

        return $arrNewArray;
    }

    /**
     * Génération d'un lien de page pour la consultation d'un tableau multipage
     *
     * @param integer $intPage numéro de page
     * @param string $strLabel libellé de page
     * @param string $strUrlMask masque d'URL
     * @param integer $intPageSel page sélectionnée
     * @return string lien de page
     */
    private static function _page($intPage, $strLabel, $strUrlMask, $intPageSel = 0)
    {
        return $intPageSel == $intPage ? str_replace('{l}', $strLabel, '<strong>{l}</strong>') : str_replace(array('{p}', '{l}'), array($intPage, $strLabel), '<a href="'.$strUrlMask.'">{l}</a>');
    }

    /**
     * Retourne un tableau de liens vers des pages pour la consultation d'un tableau multipage
     *
     * @param integer $intNumRows nombre de lignes dans le tableau
     * @param integer $intMaxLines nombre de lignes max par pages
     * @param string $strUrlMask masque d'URL. {p} = numéro de page
     * @param integer $intPageSel page sélectionnée
     * @return array tableau de liens vers des pages
     */
    public static function getpages($intNumRows, $intMaxLines = 50, $strUrlMask = '?page={p}', $intPageSel = 1)
    {
        $arrPages = array();

        // Affichage des pages (optionnel)
        if ($intMaxLines > 0 && $intMaxLines < $intNumRows)
        {
            $intNumPages = ceil($intNumRows / $intMaxLines);

            // Fleche page précédente
            if ($intPageSel > 1) $arrPages[] = self::_page($intPageSel-1, '&laquo;', $strUrlMask, $intPageSel);

            // On affiche toujours la premiere page
            $arrPages[] = self::_page(1, 1, $strUrlMask, $intPageSel);

            // Affichage "..." après première page
            if ($intPageSel > 4) $arrPages[] = '...';

            // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
            for ($i = $intPageSel - 2; $i <= $intPageSel + 2; $i++)
            {
                if ($i>1 && $i<$intNumPages) $arrPages[] = self::_page($i, $i, $strUrlMask, $intPageSel);
            }

            // Affichage "..." avant dernière page
            if ($intPageSel < $intNumPages - 3) $arrPages[] = '...';

            // Dernière page
            if ($intNumPages>1) $arrPages[] = self::_page($intNumPages, $intNumPages, $strUrlMask, $intPageSel);

            // Fleche page suivante
            if ($intPageSel < $intNumPages) $arrPages[] = self::_page($intPageSel+1, '&raquo;', $strUrlMask, $intPageSel);
        }

        return implode(' ', $arrPages);
    }
}
