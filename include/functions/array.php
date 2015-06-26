<?php
/*
    Copyright (c) 2009 Ovensia
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

include_once './include/functions/string.php';

/**
 * Retourne le contenu d'un tableau multidimensionnel au format JSON
 *
 * @param array $arrArray tableau de données
 * @param boolean $booForceUTF8 true si les données doivent être convertie en UTF-8 (depuis ISO-8859-1)
 * @return string contenu JSON
 */

function ploopi_array2json($arrArray, $booForceUTF8 = true)
{
    return json_encode($booForceUTF8 ? ploopi_array_map('utf8_encode',ploopi_array_cleankeys($arrArray)) : ploopi_array_cleankeys($arrArray));
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

/**
 * Retourne le contenu d'un tableau multidimensionnel au format XML
 *
 * @param array $arrArray tableau de données
 * @param string $strRootName nom du noeud racine
 * @param string $strDefaultTagName nom des noeuds 'anonymes'
 * @param string $strEncoding charset utilisé
 * @return string contenu XML
 */

function ploopi_array2xml($arrArray, $strRootName = 'data', $strDefaultTagName = 'row', $strEncoding = 'ISO-8859-1')
{
    include_once './lib/array2xml/Array2Xml.php';
    $arrArray = ploopi_array_map('utf8_encode', $arrArray);
    
    Array2XML::init('1.0', $strEncoding);
    $xml = Array2XML::createXML($strRootName, array($strDefaultTagName => $arrArray));
    return $xml->saveXML();    
}

/**
 * Retourne le contenu d'un tableau à 2 dimensions au format CSV
 *
 * @param array $arrArray tableau de données
 * @param array $arrOptions options du format CSV : booHeader:true si la ligne d'entête doit être ajoutée (nom des colonnes), strFieldSep:séparateur de champs, strLineSep:séparateur de lignes, strTextSep:caractère d'encapsulation des contenus
 * @param array $arrTitles nom des colonnes (optionnel)
 * @return string contenu CSV
 */

function ploopi_array2csv($arrArray, $arrOptions = array())
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
        if ($arrOptions['booClean']) $arrArray = ploopi_array_map('ploopi_iso8859_clean', $arrArray);

        // Fonction d'échappement & formatage du contenu
        $funcLineEchap = null;
        
        if ($arrOptions['strTextSep'] != '') {
            $funcLineEchap = create_function('$value', 'return \''.$arrOptions['strTextSep'].'\'.str_replace(\''.$arrOptions['strTextSep'].'\', \''.$arrOptions['strTextSep'].$arrOptions['strTextSep'].'\', $value).\''.$arrOptions['strTextSep'].'\';');
        } elseif ($arrOptions['strFieldSep'] != '') {
            $funcLineEchap = create_function('$value', 'return str_replace(\''.$arrOptions['strFieldSep'].'\', \'\\'.$arrOptions['strFieldSep'].'\', $value);');
        }

        // Ajout de la ligne d'entête
        if ($arrOptions['booHeader']) $arrCSV[] = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? array_keys(reset($arrArray)) : ploopi_array_map($funcLineEchap, array_keys(reset($arrArray))));

        // Traitement des contenus
        foreach($arrArray as $row) $arrCSV[] = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? $row : ploopi_array_map($funcLineEchap, $row));
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

function ploopi_array2html($arrArray, $booHeader = true, $strClassName = 'ploopi_array')
{
    // Tableau des lignes
    $arrHTML = array();

    if (!empty($arrArray))
    {
        // Ajout de la ligne d'entête
        if ($booHeader) $arrHTML[] = '<tr>'.implode('', ploopi_array_map(function($value) { return '<th>'.ploopi_htmlentities($value).'</th>'; }, array_keys(reset($arrArray)))).'</tr>';

        // Traitement des contenus
        foreach($arrArray as $row) $arrHTML[] = '<tr>'.implode('', ploopi_array_map(function($value) { return '<td>'.ploopi_htmlentities($value).'</td>'; }, $row)).'</tr>';
    }

    // contenu HTML
    return '<table class="'.$strClassName.'">'.implode('', $arrHTML).'</table>';
}

/**
 * Retourne le contenu d'un tableau à 2 dimensions au format XLS
 *
 * @param array $arrArray tableau de données
 * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
 * @param string $strFileName nom du fichier
 * @param string $strSheetName nom de la feuille dans le document XLS
 * @param array $arrDataFormats formats des colonnes ('title', 'type', 'width')
 * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'tofile', 'setborder')
 * @return binary contenu XLS
 */
function ploopi_array2xls($arrArray, $booHeader = true, $strFileName = 'document.xls', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
{
     // Ajout dossier complémentaire PEAR (version corrigée pour php 5.4)
    ini_set('include_path', ini_get('include_path').':'.realpath('.').'/lib/PEAR');
    require_once 'Spreadsheet/Excel/Writer.php';

    $workbook = new Spreadsheet_Excel_Writer();
    $worksheet = $workbook->addWorksheet();

    $arrDefautOptions = array(
        'landscape' => true,
        'fitpage_width' => true,
        'fitpage_height' => false,
        'tofile' => false,
        'setborder' => false
    );

    $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);

    // Création du document
    if ($arrOptions['tofile']) $objWorkBook = new Spreadsheet_Excel_Writer($strFileName);
    else { $objWorkBook = new Spreadsheet_Excel_Writer(); $objWorkBook->send($strFileName); }

    $objFormatTitle = $objWorkBook->addFormat( array( 'Align' => 'center', 'TextWrap' => 1, 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter', 'FgColor' => 'silver'));
    if ($arrOptions['setborder']) { $objFormatTitle->setBorder(1); $objFormatTitle->setBorderColor('black'); }
    $objFormatDefault = $objWorkBook->addFormat( array( 'TextWrap' => 1, 'Align' => 'left', 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
    if ($arrOptions['setborder']) { $objFormatDefault->setBorder(1); $objFormatDefault->setBorderColor('black'); }

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

        switch($strKey)
        {
            case 'string': $objFormat->setAlign('left'); break;
            case 'float': $objFormat->setNumFormat('#,##0.00;-#,##0.00'); break;
            case 'float_percent': $objFormat->setNumFormat('#,##0.00 %;-#,##0.00 %'); break;
            case 'float_euro': $objFormat->setNumFormat('#,##0.00 €;-#,##0.00 €'); break;
            case 'integer': $objFormat->setNumFormat('#,##0;-#,##0'); break;
            case 'integer_percent': $objFormat->setNumFormat('#,##0 %;-#,##0 %'); break;
            case 'integer_euro': $objFormat->setNumFormat('#,##0 €;-#,##0 €'); break;
            case 'date': $objFormat->setNumFormat('DD/MM/YYYY'); break;
            case 'datetime' : $objFormat->setNumFormat('DD/MM/YYYY HH:MM:SS'); break;
        }
    }
    unset($objFormat);

    $objWorkSheet = $objWorkBook->addWorksheet($strSheetName);
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

        // Ajout de la ligne d'entête
        if ($booHeader)
        {
            $intCol = 0;
            foreach(array_keys(reset($arrArray)) as $strKey) $objWorkSheet->writeString(0, $intCol++, isset($arrDataFormats[$strKey]['title']) ? $arrDataFormats[$strKey]['title'] : $strKey, $objFormatTitle);
        }
        // Traitement des contenus
        $intLine = 1;
        foreach($arrArray as $row)
        {
            $intCol = 0;
            foreach($row as $strKey => $strValue)
            {
                if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                // On vérifie si un format de donné est proposé pour le champ
                $objFormat = (!empty($arrDataFormats[$strKey]['type']) && !empty($arrFormats[$arrDataFormats[$strKey]['type']])) ? $arrFormats[$arrDataFormats[$strKey]['type']] : $objFormatDefault;

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
                        if ($strValue != '') $objWorkSheet->writeNumber($intLine, $intCol, $strValue, $objFormat);
                    break;

                    default:
                        $objWorkSheet->writeString($intLine, $intCol, $strValue, $objFormat);
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

function ploopi_array2excel($arrArray, $booHeader = true, $strFileName = 'document.xls', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
{
    include_once './lib/PHPExcel/PHPExcel.php';

    $objWorkBook = new PHPExcel;
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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color'=> array('rgb' => 'DDDDDD')
        )
    );

    // Bordure optionnelle sur le titre
    if ($arrOptions['setborder']) {

        $rowTitleStyle['borders'] = array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        );

    }

    // Style par défaut pour la feuille
    $objWorkSheet->getDefaultStyle()->applyFromArray($rowDefaultStyle);

    // Titre
    $objWorkSheet->setTitle(utf8_encode($strSheetName));

    // Fit to page
    $objWorkSheet->getPageSetup()->setFitToWidth($arrOptions['fitpage_width']);
    $objWorkSheet->getPageSetup()->setFitToHeight($arrOptions['fitpage_height']);

    // Paysage
    if ($arrOptions['landscape']) $objWorkSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    if (!empty($arrArray))
    {
        $intLineMax = sizeof($arrArray)+1;

        $intLine = 1;
        $intMaxCol = sizeof(array_keys(reset($arrArray)))-1;
        // Calcul colonne type Excel "Bijective base-26"
        $chrMaxCol = ($intMaxCol>25 ? chr(64+floor($intMaxCol/26)) : '').chr(65+$intMaxCol%26);

        if (!empty($arrOptions['headers'])) {
            foreach($arrOptions['headers'] as $strHeader) {

                $objWorkSheet->setCellValueByColumnAndRow(0, $intLine, utf8_encode($strHeader));

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
            foreach(array_keys(reset($arrArray)) as $strKey) $objWorkSheet->setCellValueByColumnAndRow(++$intCol, $intLine, utf8_encode($arrDataFormats[$strKey]['title']));

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
                    $strValue = PHPExcel_Shared_Date::PHPToExcel($strValue);
                }

                // Calcul colonne type Excel "Bijective base-26"
                $chrCol = ($intCol>25 ? chr(64+floor($intCol/26)) : '').chr(65+$intCol%26);

                switch($arrDataFormats[$strKey]['type']) {
                    case 'string':
                        $objWorkSheet->setCellValueExplicit("{$chrCol}{$intLine}", utf8_encode($strValue), PHPExcel_Cell_DataType::TYPE_STRING);
                    break;

                    default:
                        $objWorkSheet->setCellValueByColumnAndRow($intCol, $intLine, utf8_encode($strValue));
                    break;
                }

                if (is_array($mixValue)) {
                    if (isset($mixValue['style'])) {
                        $objWorkSheet->getStyle("{$chrCol}{$intLine}")->applyFromArray($mixValue['style']);
                    }
                }

                $intCol++;
            }

            //$objWorkSheet->getRowDimension($intLine)->setRowHeight(-1);
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
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000')
                    )
                );

            }

            // Application du format
            switch($arrDataFormats[$strKey]['type']) {

                case 'float': $strFormat = '0.00'; break;
                case 'float_percent': $strFormat = '0.00%'; break;
                case 'float_euro': $strFormat = '[$EUR ]#,##0.00_-'; break;
                case 'integer': $strFormat = '0'; break;
                case 'integer_percent': $strFormat = '0%'; break;
                case 'integer_euro': $strFormat = '[$EUR ]#,##0_-'; break;
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
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                );
            }

            // Application du style sur la colonne
            $objWorkSheet->getStyle("{$chrCol}2:{$chrCol}{$intLineMax}")->applyFromArray($rowStyle);

            // Largeur de colonne
            if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->getColumnDimension(chr($intCol+65))->setWidth($arrDataFormats[$strKey]['width']);

            $intCol++;

        }
    }


    // Sélection du writer
    switch($arrOptions['writer']) {

        case 'excel5':
            $objWriter = new PHPExcel_Writer_Excel5($objWorkBook);
        break;

        case 'pdf':
            $objWriter = new PHPExcel_Writer_PDF($objWorkBook);
            $objWriter->setSheetIndex(0);
            header('Content-type: application/pdf');
            header("Content-Disposition:inline;filename={$strFileName}");

            $objWriter->save('php://output');
            die();


        break;

        case 'csv':
            $objWriter = new PHPExcel_Writer_CSV($objWorkBook);
            $objWriter->setSheetIndex(0);
            $writer->setDelimiter(",");

        break;

        case 'html':
            $objWriter = new PHPExcel_Writer_HTML($objWorkBook);
            $objWriter->setSheetIndex(0);
        break;

        case 'excel2007':
        default:
            $objWriter = new PHPExcel_Writer_Excel2007($objWorkBook);
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


function ploopi_array2ods($arrArray, $booHeader = true, $strFileName = 'document.ods', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
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
function ploopi_array_cleankeys($arrArray)
{
    if (!is_array($arrArray)) return $arrArray;

    $arrNewArray = array();

    foreach($arrArray as $strKey => $mixValue)
    {
        $strKey = preg_replace("/[^a-z0-9_]/", "_", strtolower(ploopi_convertaccents($strKey)));

        // Cas particulier des clés non conformes
        if (strlen($strKey) == 0) $strKey = 'xml';
        elseif (substr($strKey,0,1) == '_') $strKey = 'xml'.$strKey;

        if (is_array($mixValue)) $arrNewArray[$strKey] = ploopi_array_cleankeys($mixValue);
        else $arrNewArray[$strKey] = $mixValue;
    }

    return $arrNewArray;
}

function ploopi_array_page($intPage, $strLabel, $strUrlMask, $intPageSel = 0)
{
    return $intPageSel == $intPage ? str_replace('{l}', $strLabel, '<strong>{l}</strong>') : str_replace(array('{p}', '{l}'), array($intPage, $strLabel), '<a href="'.$strUrlMask.'">{l}</a>');
}

function ploopi_array_getpages($intNumRows, $intMaxLines = 50, $strUrlMask = '?page={p}', $intPageSel = 1)
{
    $arrPages = array();

    // Affichage des pages (optionnel)
    if ($intMaxLines > 0 && $intMaxLines < $intNumRows)
    {
        $intNumPages = ceil($intNumRows / $intMaxLines);

        // Fleche page précédente
        if ($intPageSel > 1) $arrPages[] = ploopi_array_page($intPageSel-1, '&laquo;', $strUrlMask, $intPageSel);

        // On affiche toujours la premiere page
        $arrPages[] = ploopi_array_page(1, 1, $strUrlMask, $intPageSel);

        // Affichage "..." après première page
        if ($intPageSel > 4) $arrPages[] = '...';

        // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
        for ($i = $intPageSel - 2; $i <= $intPageSel + 2; $i++)
        {
            if ($i>1 && $i<$intNumPages) $arrPages[] = ploopi_array_page($i, $i, $strUrlMask, $intPageSel);
        }

        // Affichage "..." avant dernière page
        if ($intPageSel < $intNumPages - 3) $arrPages[] = '...';

        // Dernière page
        if ($intNumPages>1) $arrPages[] = ploopi_array_page($intNumPages, $intNumPages, $strUrlMask, $intPageSel);

        // Fleche page suivante
        if ($intPageSel < $intNumPages) $arrPages[] = ploopi_array_page($intPageSel+1, '&raquo;', $strUrlMask, $intPageSel);
    }

    return implode(' ', $arrPages);
}
