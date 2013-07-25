<?php
/*
    Copyright (c) 2013 Ovensia
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
 * Classe abstraite globale du module
 *
 * @package dbreport
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

abstract class dbreport {

    /**
     * ACTION : Gérer les requêtes
     */
    const _ACTION_MANAGE = 10;
    /**
     * ACTION : Verrouiller les requêtes
     */
    const _ACTION_LOCK = 99;

    // Liste des opérations
    private static $_arrOperations = array(
        'groupby' => 'Regroupement',
        'intervals' => 'Regroupement par Intervalles',
        'sum' => 'Somme',
        'avg' => 'Moyenne',
        'min' => 'Min',
        'max' => 'Max',
        'count' => 'Compte',
        'stddev_pop' => 'Ecart type de la population',
        'stddev_samp' => "Ecart type de l'échantillon",
        'var_pop' => 'Variance de la population',
        'var_samp' => "Variance de l'échantillon"
    );

    // Liste des ordres de tri
    private static $_arrSorts = array(
        'asc' => 'Croissant',
        'desc' => 'Décroissant'
    );

    // Liste des critères pour les filtres
    private static $_arrCriterias = array(
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

    // Liste des fonctions proposées (MySQL)
    private static $_arrFunctions = array(
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
        'date' => array(
            'DATE( % )',
            'DATEDIFF( % , D )',
            'DAY( % )',
            'DAYNAME( % )',
            'DAYOFMONTH( % )',
            'DAYOFWEEK( % )',
            'DAYOFYEAR( % )',
            'FROM_UNIXTIME( % )',
            'HOUR( % )',
            'LAST_DAY( % )',
            'MICROSECOND( % )',
            'MINUTE( % )',
            'MONTH( % )',
            'MONTHNAME( % )',
            'QUARTER( % )',
            'TIME( % )',
            'TIMEDIFF( % , T )',
            'TIMESTAMP( % )',
            'TIME_TO_SEC( % )',
            'TO_DAYS( % )',
            'UNIX_TIMESTAMP( % )',
            'WEEK( % )',
            'WEEKDAY( % )',
            'WEEKOFYEAR( % )',
            'YEAR( % )'
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

    // Liste des types supportés
    private static $_arrTypes = array(
        'integer' => 'Nombre entier',
        'float' => 'Nombre décimal',
        'string' => 'Chaîne de caractères',
        'boolean' => 'Booléen',
        'date' => 'Date'
    );

    public static function getOperations() {
        return self::$_arrOperations;
    }

    public static function getOperation($strKey) {
        return isset(self::$_arrOperations[$strKey]) ? self::$_arrOperations[$strKey] : '';
    }

    public static function getSorts() {
        return self::$_arrSorts;
    }

    public static function getSort($strKey) {
        return isset(self::$_arrSorts[$strKey]) ? self::$_arrSorts[$strKey] : '';
    }

    public static function getCriterias() {
        return self::$_arrCriterias;
    }

    public static function getCriteria($strKey) {
        return isset(self::$_arrCriterias[$strKey]) ? self::$_arrCriterias[$strKey] : '';
    }

    public static function getTypes() {
        return self::$_arrTypes;
    }

    public static function getType($strKey) {
        return isset(self::$_arrTypes[$strKey]) ? self::$_arrTypes[$strKey] : '';
    }

    public static function getFunctions($strKey) {
        return self::$_arrFunctions[$strKey];
    }

    public static function getBasicType($strMySqlType)
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


    public static function getData($strWsId, $arrParams, $strFormat = 'raw', $strDbreportCode = '')
    {
        global $objCache;

        // Récupération du paramètre de durée de cache
        $intCacheLifetime = ploopi_getparam('dbreport_cache_lifetime', ploopi_getmoduleid('dbreport')); // Attention, ici on prend le premier module trouvé, ne fonctionne pas en multi-instance !

        // Instanciation du cache
        $objCache = new ploopi_cache($strWsId.','.implode(',', $arrParams), $intCacheLifetime);

        // Lecture du cache, présent ?
        if (!$mixedVar = $objCache->get_var())
        {
            if (empty($strWsId)) $strError = "Requête non fournie";
            else
            {
                include_once './modules/dbreport/classes/class_dbreport_query.php';
                include_once './include/classes/data_object_collection.php';

                set_time_limit(300);

                $mixedVar = null;

                $objDOC = new data_object_collection('dbreport_query');
                $objDOC->add_where('ws_id = %s', $strWsId);
                $objDOC->add_where('ws_activated = 1');
                $arrQueries = $objDOC->get_objects();

                if(sizeof($arrQueries) == 1) //  Id de requête unique trouvé
                {
                    $row = $arrQueries[0]->fields;

                    if ($row['ws_code'] == '' || $row['ws_code'] == $strDbreportCode)
                    {
                        // Lecture de l'IP du client (c'est un tableau il peut en avoir plusieurs à cause notamment des proxies)
                        $arrRemoteIp = $_SESSION['ploopi']['remote_ip'];

                        if ($row['ws_ip'] == '' || in_array($row['ws_ip'], $arrRemoteIp))
                        {
                            $objDbrQuery = $arrQueries[0];

                            // Génération de la requête SQL
                            if (!$objDbrQuery->generate($arrParams)) ploopi_die();

                            // Exécution de la requête et stockage du résultat
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
                                    $mixedVar = ploopi_array2excel($objDbrQuery->getresult(), true, 'dbreport.xls', 'query', null, array('writer' => 'excel5'));
                                break;

                                case 'xlsx':
                                    include_once './include/functions/array.php';
                                    $mixedVar = ploopi_array2excel($objDbrQuery->getresult(), true, 'dbreport.xlsx', 'query', null, array('writer' => 'excel2007'));
                                break;

                                case 'sxc':
                                case 'ods':
                                case 'pdf':
                                    include_once './include/classes/odf.php';

                                    // Génération du fichier XLSX
                                    $strXlsContent = ploopi_array2excel($objDbrQuery->getresult(), true,  'dbreport.xlsx', 'query', null, array('writer' => 'excel2007'));

                                    // Instanciation du convertisseur ODF
                                    $objOdfConverter = new odf_converter(ploopi_getparam('system_webservice_jodconverter', ploopi_getmoduleid('system')));

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

                                    // Conversion du document dans le format sélectionné
                                    $mixedVar = $objOdfConverter->convert($strXlsContent, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $strOuputMime);
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




    /**
     * Retourne les infos de la table
     * @param string $strTableName nom de la table
     * @return array
     */
    public static function getTableInfo($strTableName) {

        $objQuery = new ploopi_query_select();
        $objQuery->add_raw("SHOW TABLE STATUS WHERE name = %s", $strTableName);
        $objRs = $objQuery->execute();

        // Table existe ?
        if ($objRs->numrows()) {

            $rowInfo = $objRs->fetchrow();

            $objQuery = new ploopi_query_select();
            $objQuery->add_raw("SHOW INDEX FROM %r WHERE Key_name = 'PRIMARY'", $strTableName);
            $objRs = $objQuery->execute();
            $rowInfo['Primary'] = '';

            while ($row = $objRs->fetchrow()) {
                if ($rowInfo['Primary'] != '') $rowInfo['Primary'] .= ',';
                $rowInfo['Primary'] = $row['Column_name'];
            }

            return $rowInfo;
        }
        else return false;

    }

    /**
     * Retourne les champs de la table
     * @param string $strTableName nom de la table
     * @return array
     */
    public static function getTableFields($strTableName) {

        $arrFields = array();

        $objQuery = new ploopi_query_select();
        $objQuery->add_raw("SHOW FULL COLUMNS FROM `%r`", $strTableName);
        $objRs = $objQuery->execute();
        while ($row = $objRs->fetchrow()) $arrFields[$row['Field']] = $row;

        return $arrFields;
    }

    /**
     * Retourne les indexes de la table
     * @param string $strTableName nom de la table
     * @return array
     */
    public static function getTableIndexes($strTableName) {

        $arrIndexes = array();

        $objQuery = new ploopi_query_select();
        $objQuery->add_raw("SHOW INDEX FROM `%r`", $strTableName);
        $objRs = $objQuery->execute();
        while ($row = $objRs->fetchrow()) $arrIndexes[$row['Column_name']] = $row;

        return $arrIndexes;
    }

}
