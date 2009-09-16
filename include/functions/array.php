<?php
/**
 * Retourne le contenu d'un tableau multidimensionnel au format JSON
 *
 * @param array $arrArray tableau de données
 * @param boolean $booForceUTF8 true si les données doivent être convertie en UTF-8 (depuis ISO-8859-1)
 * @return string contenu JSON
 */

function ploopi_array2json($arrArray, $booForceUTF8 = true)
{
    return json_encode($booForceUTF8 ? ploopi_array_map('utf8_encode',$arrArray) : $arrArray);
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
    if (PEAR::isError($objSerializer->serialize($arrArray))) return false;
    
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
 * @return binary contenu XLS
 */

function ploopi_array2xls($arrArray, $booHeader = true, $strFileName = 'document.xls', $strSheetName = 'Feuille', $booLandscape = true)
{
    require_once 'Spreadsheet/Excel/Writer.php';

    // Création du document
    $objWorkBook = new Spreadsheet_Excel_Writer();

    $objFormatTitle = $objWorkBook->addFormat( array( 'Align' => 'center', 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter', 'FgColor' => 'silver'));
    $objFormat = $objWorkBook->addFormat( array( 'TextWrap' => 1, 'Align' => 'left', 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));

    $objWorkSheet = $objWorkBook->addWorksheet($strSheetName);
    $objWorkSheet->fitToPages(1, 0);
    if ($booLandscape) $objWorkSheet->setLandscape();
    
    if (!empty($arrArray))
    {
        // Ajout de la ligne d'entête
        if ($booHeader) 
        {
            $intCol = 0;
            foreach(array_keys(reset($arrArray)) as $strValue) $objWorkSheet->writeString(0, $intCol++, $strValue, $objFormatTitle);
        }
        
        // Traitement des contenus
        $intLine = 1;
        foreach($arrArray as $row) 
        {
            $intCol = 0;
            foreach($row as $strValue) $objWorkSheet->writeString($intLine, $intCol++, $strValue, $objFormat);
            $intLine++;
        }
    }
        
    // fermeture du document
    $objWorkBook->close();
    
    // envoi du document
    return $objWorkBook->send($strFileName);
}


?>