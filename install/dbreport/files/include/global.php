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

/**
 * Fonctions, constantes, variables globales
 *
 * @package dbreport
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * ACTION : G�rer les requ�tes
 */
define('_DBREPORT_ACTION_MANAGE', 10);

/**
 * ACTION : Verrouiller les requ�tes
 */
define('_DBREPORT_ACTION_LOCK', 99);

// Liste des op�rations
global $arrDbReportOperations;
$arrDbReportOperations = array(
    'groupby' =>  'Regroupement',
    'sum' => 'Somme',
    'avg' => 'Moyenne',
    'min' => 'Min',
    'max' => 'Max',
    'count' => 'Compte',
    'intervals' => 'Regroupement par Intervalles'
);

// Liste des ordres de tri
global $arrDbReportSort;
$arrDbReportSort = array(
    'asc' => 'Croissant',
    'desc' => 'D�croissant'
);

// Liste des crit�res pour les filtres
global $arrDbReportCriteria;
$arrDbReportCriteria = array(
    '=' => '=',
    '<' => '<',
    '>' => '>',
    '<=' => '<=',
    '>=' => '>=',
    '<>' => '<>',
    'like' => 'Contient',
    'begining' => 'Commence par',
    'ending' => 'Termine par',
    'between' => 'Entre',
    'in' => 'Dans la liste de valeurs'
);

// Liste des fonctions propos�es (MySQL)
global $arrDbReportFunctions;
$arrDbReportFunctions = array(
    'math' => array(
        'ABS( % )',
        'ACOS( % )',
        'ASIN( % )',
        'ATAN( % )',
        'CEIL( % )',
        'COS( % )',
        'COT( % )',
        'DEGREES( % )',
        'EXP( % )',
        'FLOOR( % )',
        'LN( % )',
        'LOG( % )',
        'LOG( B , % )',
        'LOG2( % )',
        'LOG10( % )',
        'MOD( % , M)',
        'PI()',
        'POW( % , Y )',
        'RAND()',
        'ROUND( % )',
        'ROUND( % , D )',
        'SIGN( % )',
        'SIN( % )',
        'SQRT( % )',
        'TAN( % )',
        'TRUNCATE( % , D )'
    ),
    'string' => array(
        'ASCII( % )',
        'CHAR( % )',
        'CHAR_LENGTH( % )',
        'CONCAT( % , str1 , str2 , . . . )',
        'CONCAT_WS( separator , % , str1 , str2 , . . . )',
        'CONV( % , from_base , to_base )',
        'HEX( % )',
        'INSERT( % , pos , len , newstr )',
        'INSTR( % , substr )',
        'LEFT( % , len )',
        'LENGTH( % )',
        'LOCATE( substr , % , pos )',
        'LOWER( % )',
        'LPAD( % , len , padstr )',
        'LTRIM( % )',
        'OCT( % )',
        'QUOTE( % )',
        'REPEAT( % , count )',
        'REPLACE( % , from_str , to_str )',
        'REVERSE( % )',
        'RIGHT( % , len )',
        'RPAD( % , len , padstr )',
        'RTRIM( % )',
        'SPACE( n )',
        'SUBSTRING( % , pos , len )',
        'TRIM( % )',
        'UNHEX( % )',
        'UPPER( % )'
    )
);

// Liste des types support�s
global $arrDbReportTypes;
$arrDbReportTypes = array(
    'integer' => 'Nombre entier',
    'float' => 'Nombre d�cimal',
    'string' => 'Cha�ne de caract�res',
    'boolean' => 'Bool�en',
    'date' => 'Date'
);

function dbreport_getbasictype($strMySqlType)
{
    $strMySqlType = strtolower($strMySqlType);
    $strBasicType = 'string';

    if (strstr($strMySqlType,'double') || strstr($strMySqlType,'float')) $strBasicType = 'float';
    elseif (strstr($strMySqlType,'tinyint(1)')) $strBasicType = 'boolean';
    elseif (strstr($strMySqlType,'int')) $strBasicType = 'integer';
    elseif (strstr($strMySqlType,'char')) $strBasicType = 'string';
    elseif (strstr($strMySqlType,'date')) $strBasicType = 'date';

    return $strBasicType;
}


function dbreport_getdata($strWsId, $arrParams, $strFormat = 'raw', $strDbreportCode = '')
{
    global $objCache;

    // R�cup�ration du param�tre de dur�e de cache
    $intCacheLifetime = ploopi_getparam('dbreport_cache_lifetime', ploopi_getmoduleid('dbreport')); // Attention, ici on prend le premier module trouv�, ne fonctionne pas en multi-instance !

    // Instanciation du cache
    $objCache = new ploopi_cache($strWsId.','.implode(',', $arrParams), $intCacheLifetime);

    // Lecture du cache, pr�sent ?
    if (!$mixedVar = $objCache->get_var())
    {
        if (empty($strWsId)) $strError = "Requ�te non fournie";
        else
        {
            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './include/classes/data_object_collection.php';

            $mixedVar = null;

            $objDOC = new data_object_collection('dbreport_query');
            $objDOC->add_where('ws_id = %s', $strWsId);
            $objDOC->add_where('ws_activated = 1');
            $arrQueries = $objDOC->get_objects();

            if(sizeof($arrQueries) == 1) //  Id de requ�te unique trouv�
            {
                $row = $arrQueries[0]->fields;

                if ($row['ws_code'] == '' || $row['ws_code'] == $strDbreportCode)
                {
                    // Lecture de l'IP du client (c'est un tableau il peut en avoir plusieurs � cause notamment des proxies)
                    $arrRemoteIp = $_SESSION['ploopi']['remote_ip'];

                    if ($row['ws_ip'] == '' || in_array($row['ws_ip'], $arrRemoteIp))
                    {
                        $objDbrQuery = $arrQueries[0];

                        // G�n�ration de la requ�te SQL
                        if (!$objDbrQuery->generate($arrParams)) ploopi_die();

                        // Ex�cution de la requ�te et stockage du r�sultat
                        if ($strFormat != 'sql') $objDbrQuery->exec($intCacheLifetime);

                        switch($strFormat)
                        {
                            case 'sql':
                                $mixedVar = $objDbrQuery->getquery();
                            break;

                            case 'html':
                                include_once './include/functions/array.php';
                                $mixedVar = '
                                    <html><style>
                                    body {font: 11px Verdana,Tahoma,Arial,sans-serif;}
                                    table {padding:0px;margin:2px;border:1px solid #c0c0c0;border-collapse:collapse;}
                                    td, th {padding:2px 4px;border:1px solid #888;}
                                    th {background-color:#ddd;}
                                    </style><body>
                                '.ploopi_array2html($objDbrQuery->getresult()).'</body></html>';
                            break;

                            case 'xls':
                                include_once './include/functions/array.php';
                                $mixedVar = ploopi_array2xls($objDbrQuery->getresult(), true, 'dbreport.xls', 'query');
                            break;

                            case 'sxc':
                            case 'ods':
                            case 'pdf':
                                include_once './include/classes/odf.php';

                                // Cr�ation d'un fichier temporaire XLS
                                // Cr�ation du dossier de travail (si n'existe pas)
                                ploopi_makedir($strOutputPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'dbreport'._PLOOPI_SEP.'tmp');
                                $strFileId = uniqid();
                                $strOutputXls = $strOutputPath._PLOOPI_SEP."{$strFileId}.xls";

                                // G�n�ration du fichier XLS
                                ploopi_array2xls($objDbrQuery->getresult(), true, $strOutputXls, 'query', null, array('tofile' => true, 'setborder' => true));

                                // Instanciation du convertisseur ODF
                                $objOdfConverter = new odf_converter(ploopi_getparam('dbreport_webservice_jodconverter', ploopi_getmoduleid('dbreport')));

                                $rawOuputFile = $strOutputPath._PLOOPI_SEP."{$strFileId}.{$strFormat}";

                                switch($strFormat)
                                {
                                    case 'pdf':
                                        $strOuputMime = 'application/pdf';
                                    break;

                                    case 'sxc':
                                        $strOuputMime = 'application/vnd.sun.xml.calc';
                                    break;

                                    case 'ods':
                                        $strOuputMime = 'application/vnd.oasis.opendocument.spreadsheet';
                                    break;
                                }

                                // Conversion du document dans le format s�lectionn�
                                $mixedVar = $objOdfConverter->convert(file_get_contents($strOutputXls), 'application/vnd.ms-excel', $strOuputMime);
                                // Suppression du fichier temporaire XLS
                                unlink($strOutputXls);
                            break;

                            case 'csv':
                                include_once './include/functions/array.php';
                                $mixedVar = ploopi_array2csv($objDbrQuery->getresult());
                            break;

                            case 'json':
                                include_once './include/functions/array.php';
                                $mixedVar = ploopi_array2json($objDbrQuery->getresult());
                            break;

                            case 'json_opt':
                                include_once './include/functions/array.php';
                                $mixedVar = ploopi_array2json($objDbrQuery->getresult_opt());
                            break;

                            case 'xml':
                                include_once './include/functions/array.php';
                                $mixedVar = ploopi_array2xml($objDbrQuery->getresult());
                            break;

                            case 'ser':
                                $mixedVar = serialize($objDbrQuery->getresult());
                            break;

                            case 'raw':
                                $mixedVar = $objDbrQuery->getresult();
                            break;

                            default:
                            case 'txt':
                                $mixedVar = print_r($objDbrQuery->getresult(), true);
                            break;

                        }

                    }
                    else $strError = "Adresse IP invalide";
                }
                else $strError = "Code invalide";
            }
            else $strError = "Identifiant &laquo; {$strWsId} &raquo; incorrect";
        }

        if (!empty($strError)) ploopi_die($strError);

        // Sauvegarde de la variable en cache
        $objCache->save_var($mixedVar);
    }

    return $mixedVar;
}



?>