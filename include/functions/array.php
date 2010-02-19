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

function ploopi_array2xml($arrArray, $strRootName = 'data', $strDefaultTagName = 'row', $strEncoding = 'ISO-8859-1')
{
    require_once 'XML/Serializer.php';

    // Configuration du serializer XML
    $objSerializer = new XML_Serializer(
        array (
           'addDecl' => true,
           'encoding' => $strEncoding,
           'indent' => '  ',
           'rootName' => $strRootName,
           'defaultTagName' => $strDefaultTagName,
        )
    ); 
    
    // Sérialisation & détection d'erreur
    if (PEAR::isError($objSerializer->serialize(ploopi_array_cleankeys($arrArray)))) return false;
    
    // Contenu XML
    return $objSerializer->getSerializedData(); 
}  

/**
 * Retourne le contenu d'un tableau à 2 dimensions au format CSV
 *
 * @param array $arrArray tableau de données
 * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
 * @param string $strFieldSep séparateur de champs
 * @param string $strLineSep séparateur de lignes
 * @param string $strTextSep caractère d'encapsulation des contenus
 * @return string contenu CSV
 */

function ploopi_array2csv($arrArray, $booHeader = true, $strFieldSep = ',', $strLineSep = "\n", $strTextSep = '"')
{
    // Tableau des lignes du fichier CSV
    $arrCSV = array();
    
    if (!empty($arrArray))
    {
        // Fonction d'échappement & formatage du contenu
        $funcLineEchap = create_function('$value', 'return \''.$strTextSep.'\'.str_replace(\''.$strTextSep.'\', \'\\'.$strTextSep.'\', $value).\''.$strTextSep.'\';');
    
        // Ajout de la ligne d'entête
        if ($booHeader) $arrCSV[] = implode($strFieldSep, ploopi_array_map($funcLineEchap, array_keys(reset($arrArray))));
        
        // Traitement des contenus
        foreach($arrArray as $row) $arrCSV[] = implode($strFieldSep, ploopi_array_map($funcLineEchap, $row));
    }
        
    // contenu CSV
    return implode($strLineSep, $arrCSV).$strLineSep;
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
        // Fonction de formatage du contenu
        $funcLineTH = create_function('$value', 'return "<th>$value</th>";');
        $funcLineTD = create_function('$value', 'return "<td>$value</td>";');
        
        
        // Ajout de la ligne d'entête
        if ($booHeader) $arrHTML[] = '<tr>'.implode('', ploopi_array_map($funcLineTH, array_keys(reset($arrArray)))).'</tr>';
        
        // Traitement des contenus
        foreach($arrArray as $row) $arrHTML[] = '<tr>'.implode('', ploopi_array_map($funcLineTD, $row)).'</tr>';
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
 * @param array $arrDataFormats formats des colonnes ('type', 'width')
 * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'tofile', 'setborder')
 * @return binary contenu XLS
 */

function ploopi_array2xls($arrArray, $booHeader = true, $strFileName = 'document.xls', $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
{
    require_once 'Spreadsheet/Excel/Writer.php';

    $workbook = new Spreadsheet_Excel_Writer();
    $worksheet =& $workbook->addWorksheet();
    
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
    
    $objFormatTitle = $objWorkBook->addFormat( array( 'Align' => 'center', 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter', 'FgColor' => 'silver'));
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
        'date' => null
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
                // On vérifie si un format de donné est proposé pour le champ
                $objFormat = (!empty($arrDataFormats[$strKey]['type']) && !empty($arrFormats[$arrDataFormats[$strKey]['type']])) ? $arrFormats[$arrDataFormats[$strKey]['type']] : $objFormatDefault;

                $objWorkSheet->write($intLine, $intCol++, $strValue, $objFormat);
            }
            $intLine++;
        }
    }
    
    // fermeture du document
    $objWorkBook->close();
    
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
        if (is_array($mixValue)) $arrNewArray[$strKey] = ploopi_array_cleankeys($mixValue);
        else $arrNewArray[$strKey] = $mixValue;
    }
    
    return $arrNewArray;
}
?>