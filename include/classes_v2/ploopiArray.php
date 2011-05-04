<?php
class ploopiArray extends ploopiFactory
{
    private $_arrData;

    public function __construct($arrData)
    {
        $this->setArray($arrData);
    }

    public function getArray() { return $this->_arrData; }

    public function getIterator() { return new ArrayIterator($this->_arrData); }

    public function setArray($arrData)
    {
        if (!is_array($arrData)) throw new ploopiException('Not an array');
        $this->_arrData = $arrData;
        return $this;
    }

    public function implode($strGlue = '') { return ploopiString::getInstance(implode($strGlue, $this->_arrData)); }

    private function _sanitizeKeysRec($arrData)
    {
        $arrNewArray = array();

        foreach($arrData as $strKey => $mixValue)
        {
            $strKey = preg_replace("/[^a-z0-9_]/i", "_", ploopiString::getInstance($strKey)->convertAccents()->getString());
            //$strKey = preg_replace("/[^a-z0-9_]/", "_", $strKey);

            // Cas particulier des clés non conformes
            if (strlen($strKey) == 0) $strKey = 'xml';
            elseif (substr($strKey,0,1) == '_') $strKey = 'xml'.$strKey;

            if (is_array($mixValue)) $arrNewArray[$strKey] = $this->_sanitizeKeysRec($mixValue);
            else $arrNewArray[$strKey] = $mixValue;
        }

        return $arrNewArray;
    }

    /**
     * "Nettoie" les clés d'un tableau multidimensionnel afin que les clés soient compatibles avec des noms d'entités ou de variables
     *
     * @return ploopiArray le tableau modifié
     */
    public function sanitizeKeys()
    {
        $this->_arrData = $this->_sanitizeKeysRec($this->_arrData);
        return $this;
    }


    private function _mapRec($cbFunction, $mixedVar, $booInstanciable)
    {
        if (is_array($mixedVar)) { foreach($mixedVar as $strKey => $mixedValue) $mixedVar[$strKey] = $this->_mapRec($cbFunction, $mixedValue, $booInstanciable); return $mixedVar; }
        elseif (is_object($mixedVar)) { foreach(get_object_vars($mixedVar) as $strKey => $mixedValue)  $mixedVar->$strKey = $this->_mapRec($cbFunction, $mixedValue, $booInstanciable); return $mixedVar; }
        else
        {
            if ($booInstanciable && is_array($cbFunction))
            {
                $obj = new $cbFunction[0]($mixedVar);
                $obj->$cbFunction[1]();
                return $obj->__toString();
            }
            else return call_user_func($cbFunction, $mixedVar);
        }
    }

    /**
     * Applique récursivement une fonction sur les éléments d'un tableau
     * Les éléments peuvent être des tableaux récursifs ou des objets récursifs
     *
     * @param callback $cbFunction fonction à appliquer sur le tableau
     * @return ploopiArray le tableau modifié
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see array_map
     */

    public function map($cbFunction, $booInstanciable = false)
    {
        $this->_arrData = $this->_mapRec($cbFunction, $this->_arrData, $booInstanciable);
        return $this;
    }


    /**
     * Retourne le contenu d'un tableau multidimensionnel au format JSON
     *
     * @param boolean $booForceUTF8 true si les données doivent être convertie en UTF-8 (depuis ISO-8859-1)
     * @return string contenu JSON
     */

    public function toJson($booForceUTF8 = true)
    {
        $objArray = $this->getClone()->sanitizeKeys();

        if ($booForceUTF8) $objArray->map('utf8_encode');

        return json_encode($objArray->getArray());
    }

    /**
     * Retourne le contenu d'un tableau multidimensionnel au format XML
     *
     * @param string $strRootName nom du noeud racine
     * @param string $strDefaultTagName nom des noeuds 'anonymes'
     * @param string $strEncoding charset utilisé
     * @return string contenu XML
     */

    public function toXml($strRootName = 'data', $strDefaultTagName = 'row', $strEncoding = 'ISO-8859-1', $booSanitizeKeys = true)
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

        // Sérialisation
        if ($booSanitizeKeys) $objError = $objSerializer->serialize($this->getClone()->sanitizeKeys()->getArray());
        else $objError = $objSerializer->serialize($this->getArray());

        // Détection d'erreur PEAR
        if (PEAR::isError($objError)) return false;

        // Contenu XML
        return $objSerializer->getSerializedData();
    }

    private static function _csvEchap($strValue, $strTextSep)
    {
        return $strTextSep.str_replace($strTextSep, $strTextSep.$strTextSep, $strValue).$strTextSep;
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format CSV
     *
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFieldSep séparateur de champs
     * @param string $strLineSep séparateur de lignes
     * @param string $strTextSep caractère d'encapsulation des contenus
     * @return string contenu CSV
     */

    public function toCsv($booHeader = true, $strFieldSep = ',', $strLineSep = "\n", $strTextSep = '"', $booClean = true)
    {
        // Tableau des lignes du fichier CSV
        $arrCSV = array();

        $objArray = $this->getClone();

        if ($booClean) $objArray->map(array('ploopiString', 'iso8859Clean'), true);

        // Fonction d'échappement & formatage du contenu
        $funcLineEchap = create_function('$value', 'return \''.$strTextSep.'\'.str_replace(\''.$strTextSep.'\', \''.$strTextSep.$strTextSep.'\', $value).\''.$strTextSep.'\';');

        // Ajout de la ligne d'entête
        if ($booHeader) $arrCSV[] = implode($strFieldSep, ploopiArray::getInstance(array_keys(reset($objArray->getArray())))->map($funcLineEchap)->getArray());

        // Traitement des contenus
        foreach($objArray->getIterator() as $row)
        {
            $arrCSV[] = implode($strFieldSep, ploopiArray::getInstance($row)->map($funcLineEchap)->getArray());
        }

        return implode($strLineSep, $arrCSV).$strLineSep;
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format HTML
     *
     * @param unknown_type $booHeader
     * @param unknown_type $strClassName
     * @return unknown
     */

    function toHtml($booHeader = true, $strClassName = 'ploopi_array', $booHtmlEntities = true)
    {
        // Tableau des lignes
        $arrHTML = array();

        if (!empty($this->_arrData))
        {
            // Fonction de formatage du contenu
            $funcLineTH = create_function('$value', 'return \'<th>\'.htmlentities($value).\'</th>\';');
            $funcLineTD = create_function('$value', 'return \'<td>\'.htmlentities($value).\'</td>\';');

            ploopiArray::getInstance(array_keys(reset($this->_arrData)))->map($funcLineTH)->implode();

            // Ajout de la ligne d'entête
            if ($booHeader) $arrHTML[] = '<tr>'.ploopiArray::getInstance(array_keys(reset($this->_arrData)))->map($funcLineTH)->implode().'</tr>';

            // Traitement des contenus
            foreach($this->_arrData as $row) $arrHTML[] = '<tr>'.ploopiArray::getInstance($row)->map($funcLineTD)->implode().'</tr>';
        }

        // contenu HTML
        return '<table class="'.$strClassName.'">'.ploopiArray::getInstance($arrHTML)->implode().'</table>';
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format XLS
     *
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFileName nom du fichier
     * @param string $strSheetName nom de la feuille dans le document XLS
     * @param array $arrDataFormats formats des colonnes ('title', 'type', 'width')
     * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'file', 'send', 'setborder')
     * @return binary contenu XLS
     */

    function toXls($booHeader = true, $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
    {
        ploopi_unset_error_handler();
        // Attention deprecated php 5.3
        require_once 'Spreadsheet/Excel/Writer.php';
        ploopi_set_error_handler();

        $arrDefautOptions = array(
            'landscape' => true,
            'fitpage_width' => true,
            'fitpage_height' => false,
            'file' => null,
            'send' => false,
            'setborder' => false
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);

        // Création du document
        if ($arrOptions['send'])
        {
            // Envoi direct vers le client
            $objWorkBook = new Spreadsheet_Excel_Writer();
            $objWorkBook->send($arrOptions['file']);
        }
        else
        {
            if (empty($arrOptions['file']))
            {
                // Création d'un fichier temporaire, retour du contenu via la méthode
                $strFileName = tempnam(sys_get_temp_dir(), uniqid());
                $objWorkBook = new Spreadsheet_Excel_Writer($strFileName);
            }
            else
            {
                // Ecriture dans le fichier passé en option
                $objWorkBook = new Spreadsheet_Excel_Writer($arrOptions['file']);
            }
        }

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

        // Création d'une feuille de données
        $objWorkSheet = $objWorkBook->addWorksheet($strSheetName);
        if ($arrOptions['fitpage_width'] || $arrOptions['fitpage_height']) $objWorkSheet->fitToPages($arrOptions['fitpage_width'] ? 1 : 0, $arrOptions['fitpage_height'] ? 1 : 0);
        if ($arrOptions['landscape']) $objWorkSheet->setLandscape();

        if (!empty($this->_arrData))
        {
            // Définition des formats de colonnes
            if (!empty($arrDataFormats))
            {
                $intCol = 0;
                foreach(array_keys(reset($this->_arrData)) as $strKey)
                {
                    if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->setColumn($intCol, $intCol, $arrDataFormats[$strKey]['width']);
                    $intCol++;
                }
            }

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = 0;
                foreach(array_keys(reset($this->_arrData)) as $strKey) $objWorkSheet->writeString(0, $intCol++, isset($arrDataFormats[$strKey]['title']) ? $arrDataFormats[$strKey]['title'] : $strKey, $objFormatTitle);
            }
            // Traitement des contenus
            $intLine = 1;
            foreach($this->_arrData as $row)
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
                            $objWorkSheet->writeNumber($intLine, $intCol++, $strValue, $objFormat);
                        break;

                        default:
                            $objWorkSheet->writeString($intLine, $intCol++, $strValue, $objFormat);
                        break;
                    }
                }
                $intLine++;
            }
        }

        // fermeture du document
        $objWorkBook->close();

        if (!$arrOptions['send'] && empty($arrOptions['file']))
        {
            $strFileContent = file_get_contents($strFileName);
            unlink($strFileName);
            return $strFileContent;
        }

        return void;
    }


    private static function _linkPage($intPage, $strPage, $strUrlMask, $intPageSel = 0)
    {
        return $intPageSel == $intPage ? str_replace('{p}', $strPage, '<strong>{p}</strong>') : str_replace('{p}', $strPage, '<a href="'.$strUrlMask.'">{p}</a>');
    }

    public static function getPages($intNumRows, $intMaxLines = 50, $strUrlMask = '?page={p}', $intPageSel = 1)
    {
        $arrPages = array();

        // Affichage des pages (optionnel)
        if ($intMaxLines > 0 && $intMaxLines < $intNumRows)
        {
            $intNumPages = ceil($intNumRows / $intMaxLines);

            // Fleche page précédente
            if ($intPageSel > 1) $arrPages[] = self::_linkPage($intPageSel-1, '&laquo;', $strUrlMask);

            // On affiche toujours la premiere page
            $arrPages[] = self::_linkPage(1, 1, $strUrlMask, $intPageSel);

            // Affichage "..." après première page
            if ($intPageSel > 4) $arrPages[] = '...';

            // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
            for ($i = $intPageSel - 2; $i <= $intPageSel + 2; $i++)
            {
                if ($i>1 && $i<$intNumPages) $arrPages[] = self::_linkPage($i, $i, $strUrlMask, $intPageSel);
            }

            // Affichage "..." avant dernière page
            if ($intPageSel < $intNumPages - 3) $arrPages[] = '...';

            // Dernière page
            if ($intNumPages>1) $arrPages[] = self::_linkPage($intNumPages, $intNumPages, $strUrlMask, $intPageSel);

            // Fleche page suivante
            if ($intPageSel < $intNumPages) $arrPages[] = ploopi_array_page($intPageSel+1, '&raquo;', $strUrlMask);
        }

        return implode(' ', $arrPages);
    }

}
