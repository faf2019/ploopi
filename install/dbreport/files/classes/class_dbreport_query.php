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
 * Gestion des requêtes
 *
 * @package dbreport
 * @subpackage query
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

include_once './include/classes/query.php';

include_once './modules/dbreport/classes/class_dbreport_queryfield.php';
include_once './modules/dbreport/classes/class_dbreport_querytable.php';

/**
 * Classe de gestion des requêtes
 *
 */
class dbreport_query extends data_object
{
    /**
     * Requête SQL générée
     *
     * @var string
     */
    private $strSqlQuery;

    /**
     * Objet Query
     *
     * @var ploopi_query_select
     */
    private $objQuery;

    /**
     * Tableau contenant les champs de la requête
     *
     * @var array
     */
    private $arrFields;

    /**
     * Tableau contenant le résultat de la requête
     *
     * @var array
     */
    private $arrResult;

    /**
     * Code d'erreur
     *
     * @var int
     */
    private $intErrorCode;

    /**
     * code d'erreur standard : pas d'erreur
     *
     * @var int
     */
    private static $_ERROR_OK       = 0;

    /**
     * code d'erreur "request" : erreur dans la requête
     *
     * @var int
     */
    private static $_ERROR_REQUEST  = 1;

    /**
     * code d'erreur "param" : erreur de paramètre
     *
     * @var int
     */
    private static $_ERROR_PARAM    = 2;


    /**
     * Types de graphiques
     *
     * @var array
     */

    private static $_arrChartTypes = array(

        'pie' => 'Secteurs',
        'doughnut' => 'Secteurs (anneau)',

        // 'stepLine' => 'Escaliers',

        'bar' => 'Barres',
        'stackedBar' => 'Barres cumulées',
        'stackedBar100' => 'Barres cumulées à 100%',

        'column' => 'Colonnes',
        'stackedColumn' => 'Colonnes cumulées',
        'stackedColumn100' => 'Colonnes cumulées à 100%',

        'line' => 'Lignes',
        'stackedLine' => 'Lignes cumulées',
        'stackedLine100' => 'Lignes cumulées à 100%',

        'spline' => 'Courbes',
        'stackedSpline' => 'Courbes cumulées',
        'stackedSpline100' => 'Courbes cumulées à 100%',

        'area' => 'Zones droites',
        'stackedArea' => 'Zones droites cumulées',
        'stackedArea100' => 'Zones droites cumulées à 100%',

        'splineArea' => 'Zones courbes',
        'stackedSplineArea' => 'Zones courbes cumulées',
        'stackedSplineArea100' => 'Zones courbes cumulées à 100%',

    );

    private static $_arrChartBasicTypes = array(
        'bar' => 'bar',
        'stackedBar' => 'bar',
        'stackedBar100' => 'bar',
        'column' => 'column',
        'stackedColumn' => 'column',
        'stackedColumn100' => 'column',
        'line' => 'line',
        'stackedLine' => 'line',
        'stackedLine100' => 'line',
        'spline' => 'spline',
        'stackedSpline' => 'spline',
        'stackedSpline100' => 'spline',
        'area' => 'area',
        'stackedArea' => 'area',
        'stackedArea100' => 'area',
        'splineArea' => 'areaspline',
        'stackedSplineArea' => 'areaspline',
        'stackedSplineArea100' => 'areaspline',
        'pie' => 'pie',
        'doughnut' => 'pie'
    );

     /*
    private static $_arrChartTypes = array(
        'bar' => 'Barres',
        'stackedBar' => 'Barres cumulées',
        'stackedBar100' => 'Barres cumulées à 100%',
        'column' => 'Colonnes',
        'stackedColumn' => 'Colonnes cumulées',
        'stackedColumn100' => 'Colonnes cumulées à 100%',
        'stepLine' => 'Escaliers',
        'line' => 'Lignes',
        'spline' => 'Courbes',
        'area' => 'Zones (lignes)',
        'splineArea' => 'Zones (courbes)',
        'pie' => 'Secteurs',
        'doughnut' => 'Secteurs (anneau)'
    );
    */

    /**
     * Polices
     *
     * @var array
     */
    private static $_arrChartFonts = array(
        'Tahoma' => 'Tahoma',
        'Verdana' => 'Verdana',
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Georgia' => 'Georgia',
        'Trebuchet MS' => 'Trebuchet MS',
        'Times New Roman' => 'Times New Roman',
        'Courier New' => 'Courier New',
        'Comic Sans MS' => 'Comic Sans MS'
    );

    /**
     * Styles
     *
     * @var array
     */
    private static $_arrChartStyles = array(
        'normal' => 'Normal',
        'italic' => 'Italique',
        'oblique' => 'Oblique'
    );

    /**
     * Epaisseur
     *
     * @var array
     */
    private static $_arrChartWeights = array(
        'lighter' => 'Fin',
        'normal' => 'Normal',
        'bold' => 'Epais',
        'bolder' => 'Très épais '
    );

    /**
     * Alignement horizontal
     *
     * @var array
     */
    private static $_arrChartAligns = array(
        'left' => 'Gauche',
        'center' => 'Centre',
        'right' => 'Droite'
    );

    /**
     * Alignement vertical
     *
     * @var array
     */
    private static $_arrChartValigns = array(
        'top' => 'Haut',
        'center' => 'Centre',
        'bottom' => 'Bas'
    );

    /**
     * Types de markers
     *
     * @var array
     */
    private static $_arrChartMarkers = array(
        'circle' => 'Rond',
        'square' => 'Carré',
        'triangle' => 'Triangle',
        'cross' => 'Croix'
    );

    /**
     * Tris
     *
     * @var array
     */
    private static $_arrChartSorts = array(
        'asc' => 'Ascendant',
        'desc' => 'Descendant',
        'asc_val' => 'Ascendant (val)',
        'desc_val' => 'Descendant (val)'
    );

    /**
     * Sets de couleurs
     *
     * @var array
     */
    private static $_arrChartColorSets = array(
        'default' => array(
            '219,77,76',
            '219,132,78',
            '237,166,55',
            '167,167,55',
            '134,170,101',
            '138,171,175',
            '75,179,211',
            '105,200,255',
            '190,190,190',
            '50,50,50',
        ),

        'highcharts v3' => array(
            '47,126,216',
            '13,35,58',
            '139,188,33',
            '145,0,0',
            '26,173,206',
            '73,41,112',
            '242,143,67',
            '119,161,229',
            '196,37,37',
            '166,201,106',
            '0,0,0',
        ),

        'highcharts v2' => array(
            '69,114,167',
            '170,70,67',
            '137,165,78',
            '128,105,155',
            '61,150,174',
            '219,132,61',
            '146,168,205',
            '164,125,124',
            '181,202,146',
            '0,0,0',
        ),

        'ipod' => array(
            '133,105,207',
            '13,159,216',
            '138,215,73',
            '238,206,0',
            '248,152,31',
            '248,14,39',
            '246,64,174',
            '120,118,121',
            '0,0,0',
        ),

        'bujumbura' => array(
            '190,66,14',
            '190,109,14',
            '107,79,46',
            '204,160,102',
            '224,215,82',
            '165,190,14',
            '65,151,227',
            '170,168,169',
            '0,0,0',
        ),

        'confetti' => array(
            '109,176,143',
            '242,194,94',
            '114,168,178',
            '160,172,98',
            '209,125,101',
            '156,127,149',
            '238,229,160',
            '178,178,178',
            '110,110,110',
        ),

        'desert_flower' => array(
            '178,78,3',
            '242,286,97',
            '159,116,255',
            '148,169,152',
            '219,173,0',
            '247,234,0',
            '253,186,230',
            '224,5,161',
        ),

        'zombies' => array(
            '40,118,56',
            '160,219,69',
            '152,229,208',
            '19,156,178',
            '71,78,208',
            '191,55,199',
            '238,164,209',
            '227,41,53',
            '252,209,8',
            '248,251,108',
            '170,168,169',
            '0,0,0',
        )
    );


    public static function getChartTypes() {
        return self::$_arrChartTypes;
    }

    public static function getChartType($strType) {
        return isset(self::$_arrChartTypes[$strType]) ? self::$_arrChartTypes[$strType] : '';
    }

    public static function getChartFonts() {
        return self::$_arrChartFonts;
    }

    public static function getChartFont($strFont) {
        return isset(self::$_arrChartFonts[$strFont]) ? self::$_arrChartFonts[$strFont] : '';
    }

    public static function getChartColorSets() {
        return self::$_arrChartColorSets;
    }

    public static function getChartColorSet($strColorSet) {
        return isset(self::$_arrChartColorSets[$strColorSet]) ? self::$_arrChartColorSets[$strColorSet] : array();
    }

    public static function getChartColorSetJson($strColorSet, $floOpacity = 1) {
        $rowColorSet = self::getChartColorSet($strColorSet);
        foreach($rowColorSet as $key => $value) $rowColorSet[$key] = "rgba({$value},{$floOpacity})";
        return json_encode($rowColorSet);
    }

    public static function getChartMarkers() {
        return self::$_arrChartMarkers;
    }

    public static function getChartMarker($strMarker) {
        return isset(self::$_arrChartMarkers[$strType]) ? self::$_arrChartMarkers[$strType] : '';
    }

    public static function getChartAligns() {
        return self::$_arrChartAligns;
    }

    public static function getChartAlign($strAlign) {
        return isset(self::$_arrChartAligns[$strType]) ? self::$_arrChartAligns[$strAlign] : '';
    }

    public static function getChartValigns() {
        return self::$_arrChartValigns;
    }

    public static function getChartValign($strValign) {
        return isset(self::$_arrChartValigns[$strType]) ? self::$_arrChartValigns[$strValign] : '';
    }

    public static function getChartSorts() {
        return self::$_arrChartSorts;
    }

    public static function getChartSort($strSort) {
        return isset(self::$_arrChartSorts[$strSort]) ? self::$_arrChartSorts[$strSort] : '';
    }

    /**
     * Configure MySQL en FR
     */
    private function _setFr()
    {
        global $db;
        $db->query("SET lc_time_names = 'fr_FR'");
    }

    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_mod_dbreport_query');

        $this->strSqlQuery = '';
        $this->objQuery = new ploopi_query_select();
        $this->arrResult = array();
        $this->intErrorCode = self::$_ERROR_OK;
    }

    /**
     * Enregistrement de la requête
     */
    public function save()
    {
        $this->fields['timestp_update'] = ploopi_createtimestamp();
        return parent::save();
    }

    /**
     * Suppression de la requête
     */
    public function delete()
    {
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('ploopi_mod_dbreport_querytable');
        $objQuery->add_where('id_query = %d', $this->fields['id']);
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {
            $objDbrQueryTable = new dbreport_querytable();
            $objDbrQueryTable->open($row['id']);
            $objDbrQueryTable->delete();
        }

        parent::delete();
    }


    /**
     * Gère le clone et les entités liées
     *
     * @return dbreport_query
     */
    public function __clone()
    {
        // Stock ancien ID
        $intClonedId = $this->fields['id'];

        // Personnalisation du clone
        $this->new = true;
        $this->fields['label'] = 'Clone de '.$this->fields['label'];
        $this->fields['ws_id'] = $this->fields['ws_id'] != '' ? 'clone_de_'.$this->fields['ws_id'] : '';
        $this->fields['locked'] = 0;
        $this->fields['id'] = null;

        // Enregistrement du clone pour récupérer une nouvel ID
        $this->save();

        // Clonage de la relation avec "ploopi_mod_dbreport_querytable"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('null, `tablename`, `alias`, `id_module_type`, %d', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_querytable");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_querytable");
        $objQueryIns->add_raw(" ".$objQuerySel->get_sql());
        $objQueryIns->execute();

        // Clonage de la relation avec "ploopi_mod_dbreport_queryfield"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('`id`, `tablename`, `id_module_type`, `fieldname`, `label`, `function`, `visible`, `sort`, `criteria`, `type_criteria`, `or`, `type_or`, `intervals`, `operation`, `position`, `series`');
        $objQuerySel->add_from("ploopi_mod_dbreport_queryfield");
        $objQuerySel->add_where('id_query = %d', $intClonedId);
        $objQuerySel->add_orderby('position');
        $objRs = $objQuerySel->execute();

        $arrQueryFields = array();
        // Pour chaque champ
        while($row = $objRs->fetchrow()) {
            $objQueryField = new dbreport_queryfield();
            $objQueryField->fields = $row;
            $objQueryField->fields['id_query'] = $this->fields['id'];
            $objQueryField->fields['id'] = null;
            $arrQueryFields[$row['id']] = $objQueryField->save();
        }

        // Traitement des champs liés (graphique)
        foreach(array('pivot_x','pivot_y','pivot_val','chart_x','chart_y','chart_val') as $key) {
            $this->fields[$key] = isset($arrQueryFields[$this->fields[$key]]) ? $arrQueryFields[$this->fields[$key]] : 0;
        }

        $this->save();

        // Clonage de la relation avec "ploopi_mod_dbreport_queryrelation"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('%d, `tablename_src`, `fieldname_src`,`tablename_dest`, `fieldname_dest`,`active`', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_queryrelation");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_queryrelation");
        $objQueryIns->add_raw(" ".$objQuerySel->get_sql());
        $objQueryIns->execute();

        // Clonage de la relation avec "ploopi_mod_dbreport_query_module_type"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('%d, `id_module_type`', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_query_module_type");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_query_module_type");
        $objQueryIns->add_raw(" ".$objQuerySel->get_sql());
        $objQueryIns->execute();
    }

    /**
     * Prépare la requête SQL
     *
     * @param array $arrParam tableau de paramètre
     * @return boolean true si la requête a pu être préparée
     */
    public function generate($arrParam = null)
    {
        ploopi_init_module('dbreport', false, false, false);

        /**
         * Génération de la requête SQL
         * @todo Nettoyage des nom de critere ? (%)
         */

        $this->strSqlQuery = '';
        $this->objQuery = new ploopi_query_select();

        $arrSqlSelect = array();
        $arrSqlFrom = array();
        $arrSqlWhere = array();
        $arrSqlJoin = array();
        $arrSqlGroupBy = array();
        $arrSqlOrderBy = array();
        $arrSqlHaving = array();
        $arrSqlQuery = array();

        $this->arrFields = array(); // tableau des champs de la requête

        /* Boucle sur les champs de la requête (avec le type de champ) */
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('drf.*, mbf.type');
        $objQuery->add_from('ploopi_mod_dbreport_queryfield drf');
        $objQuery->add_from('ploopi_mb_field mbf');
        $objQuery->add_where('mbf.tablename = drf.tablename');
        $objQuery->add_where('mbf.name = drf.fieldname');
        $objQuery->add_where('drf.id_query = %d', $this->fields['id']);
        $objQuery->add_orderby('drf.position');
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {

            $strSqlSelect = '';
            $strSqlGroupBy = '';
            $strSqlWhere = '';
            $strSqlHaving = '';

            // Paramètre de la clause SELECT
            $arrSelectParams = array();

            // Nom du champ dans la requête (init)
            $strQueryField = "`{$row['tablename']}`.`{$row['fieldname']}`";

            if ($row['function'] != '')  $strQueryField = str_replace('%', $strQueryField, $row['function']);

            $strDefaultLabel = "{$row['tablename']}_{$row['fieldname']}";

            /**
             * Construction de la clause SELECT
             * Traitement "operation" et "function"
             * Impacte $arrSqlSelect, $strSqlGroupBy et $strSqlOrderBy
             */

            if ($row['visible'])
            {
                switch($row['operation'])
                {
                    case '':
                        $strSqlSelect = $strQueryField;
                    break;

                    case 'groupby':
                        $strSqlSelect = $strQueryField;

                        $this->objQuery->add_groupby(($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`");
                    break;

                    case 'intervals':
                        // Exemple d'intervalle :
                        // If(auteur_affaire.age BETWEEN 12 AND 16,'12-16',If(auteur_affaire.age BETWEEN 17 AND 19,'17-19','20 et +')) as tranche,

                        // On va se servir de IF imbriqués

                        // Nombre de parenthèses ouvertes (à fermer)
                        $intParenthesis = 0;

                        $arrSqlWhereOr = array();
                        $strSqlWhereOr = '';
                        $arrWhereParams = array();

                        // Supprime le ; en trop à la fin (si il existe) dans la saisie des intervalles par l'utilisateur
                        if (substr($row['intervals'], -1) == ';') $row['intervals'] = substr($row['intervals'], 0, -1);

                        // On crée un tableau avec les intervalles (; pour séparer)
                        $arrIntervals = explode(';', $row['intervals']);

                        $intI = 1;
                        // Pour chaque intervalle
                        foreach($arrIntervals as $strInterval)
                        {
                            if ($strInterval != '')
                            {
                                // On décompose l'intervalle (séparateur: "-")
                                $arrInterval = explode('-', $strInterval);
                                if (sizeof($arrInterval) <= 2)
                                {
                                    if (sizeof($arrInterval) == 1) $arrInterval[1] = $arrInterval[0];

                                    // IntervalSql va contenir les dates transformées pour SQL
                                    $arrIntervalSql = $arrInterval;

                                    if ($row['type'] == 'date')
                                    {
                                        $arrIntervalSql[0] = ploopi_local2timestamp($arrInterval[0]);
                                        if ($arrInterval[1] != '+') $arrIntervalSql[1] = ploopi_local2timestamp($arrInterval[1]);
                                    }

                                    if ($intI < sizeof($arrIntervals))
                                    {
                                        $arrSqlWhereOr[] = "{$strQueryField} BETWEEN %s AND %s";
                                        $arrWhereParams[] = $arrIntervalSql[0];
                                        $arrWhereParams[] = $arrIntervalSql[1];

                                        // cas général
                                        $strSqlSelect .= "if ({$strQueryField} BETWEEN %s AND %s, %s, ";
                                        $arrSelectParams[] = $arrIntervalSql[0];
                                        $arrSelectParams[] = $arrIntervalSql[1];
                                        $arrSelectParams[] = "{$arrInterval[0]} à {$arrInterval[1]}";

                                        $intParenthesis++;
                                    }
                                    else // cas particulier du dernier intervalle
                                    {
                                        if ($arrInterval[1] == '+')
                                        {
                                            $arrSqlWhereOr[] = "{$strQueryField} >= %s";
                                            $arrWhereParams[] = $arrIntervalSql[0];

                                            // tout ce qui est supérieur
                                            $strSqlSelect .= "%s";
                                            $arrSelectParams[] = "{$arrInterval[0]} et {$arrInterval[1]}";
                                        }
                                        else
                                        {
                                            $arrSqlWhereOr[] = "{$strQueryField} BETWEEN %s AND %s";
                                            $arrWhereParams[] = $arrIntervalSql[0];
                                            $arrWhereParams[] = $arrIntervalSql[1];

                                            // intervalle normal
                                            $strSqlSelect .= "if ({$strQueryField} BETWEEN %s AND %s, %s,null";
                                            $arrSelectParams[] = $arrIntervalSql[0];
                                            $arrSelectParams[] = $arrIntervalSql[1];
                                            $arrSelectParams[] = "{$arrInterval[0]} à {$arrInterval[1]}";

                                            $intParenthesis++;
                                        }
                                    }
                                }
                            }

                            $intI++;
                        }

                        // Clause WHERE
                        $this->objQuery->add_where('('.implode(' OR ', $arrSqlWhereOr).')', $arrWhereParams);

                        // Clause SELECT
                        // On ferme les parenthèses en fonction du nombre de IF
                        $strSqlSelect .= str_repeat(')', $intParenthesis);

                        // Clause GROUP BY
                        $this->objQuery->add_orderby(($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`");
                    break;

                    case 'count_distinct':
                        $strOpName = 'COUNT';

                        // /!\ On change le label par défaut en incluant le nom de l'opération
                        $strDefaultLabel = dbreport::getOperation($row['operation'])." de {$strDefaultLabel}";

                        $strSqlSelect .= "{$strOpName}(DISTINCT {$strQueryField})";
                    break;

                    default: // AUTRES OPERATIONS
                        $strOpName = strtoupper($row['operation']);

                        // /!\ On change le label par défaut en incluant le nom de l'opération
                        $strDefaultLabel = dbreport::getOperation($row['operation'])." de {$strDefaultLabel}";

                        $strSqlSelect .= "{$strOpName}({$strQueryField})";
                    break;
                }

                $row['query_label'] = $strLabel = ($row['label']) ? $row['label'] : $strDefaultLabel;
                $this->arrFields[$row['id']] = $row;

                if ($row['function_group'] != '')  $strSqlSelect = str_replace('%', $strSqlSelect, $row['function_group']);

                // On ajoute la clause SELECT
                $this->objQuery->add_select("{$strSqlSelect} AS `{$strLabel}`", $arrSelectParams);

                /* Gestion du tri */
                if ($row['sort'] != '')
                {
                    $strSqlOrderBy = ($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`";
                    $this->objQuery->add_orderby($strSqlOrderBy.' '.strtoupper($row['sort']));
                }
            }
            else {
                if ($row['function_group'] != '')  $strQueryField = str_replace('%', $strQueryField, $row['function_group']);

                // Gestion du tri pour un champ non visible
                if ($row['sort'] != '') $this->objQuery->add_orderby($strQueryField.' '.strtoupper($row['sort']));
            }
            // FIN de if ($row['visible'])


            /**
             * Construction des clauses WHERE et HAVING
             * Traitement fusionné de "criteria" et "or"
             * Impacte $strSqlWhere et $strSqlHaving
             */

            $arrWhereParams = array();
            $arrHavingParams = array();

            // Lecture des critères depuis l'URL
            foreach(array('criteria', 'or') as $strCrit)
            {
                if ($row["type_{$strCrit}"] != '')
                {
                    // Cas particulier ou le filtre est un paramètre
                    if (strlen($row[$strCrit]) > 0 && $row[$strCrit][0] == '@')
                    {
                        if (isset($arrParam[$row[$strCrit]]))
                        {
                            $row[$strCrit] = $arrParam[$row[$strCrit]];
                        }
                        else
                        {
                            $this->intErrorCode = self::$_ERROR_PARAM;
                            return false;
                        }
                    }
                }
            }

            // Permet de déterminé sur la clause OR est applicable ou non sur ce champ
            $booOrIsValid = ($row['type_criteria'] != '') && ($row['type_or'] != '') && ($row['criteria'] != '%') && ($row['or'] != '%');

            foreach(array('criteria', 'or') as $strCrit)
            {
                if ($row["type_{$strCrit}"] != '')
                {
                    if ($row[$strCrit] != '%')
                    {
                        $arrCriteria = ($row["type_{$strCrit}"] == 'between') ? explode('-',$row[$strCrit]) : null; // Explosition du critère si BETWEEN

                        if ($row["type_{$strCrit}"] != 'between' || ($row["type_{$strCrit}"] == 'between' && sizeof($arrCriteria) == 2))
                        {
                            if ($row['operation'] == 'groupby' || $row['operation'] == '')
                            {
                                if ($strCrit == 'criteria' && $booOrIsValid) $strSqlWhere .= '(';
                                if ($strCrit == 'or' && $booOrIsValid) $strSqlWhere .= " OR ";

                                $strSqlWhere .= $strQueryField.' '; //($row['label']) ? "`{$row['label']}` " : "`{$strDefaultLabel}` ";
                                $strValue = '';

                                if (strstr($row['type'],'int')) // type int
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%e)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrWhereParams[0][] = intval($strVal, 10);
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %d AND %d";
                                        $arrWhereParams[] = intval($arrCriteria[0], 10);
                                        $arrWhereParams[] = intval($arrCriteria[1], 10);
                                    }
                                    else $strValue = intval($row[$strCrit], 10);
                                }
                                elseif (strstr($row['type'],'double') || strstr($row['type'],'float')) // type double
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrWhereParams[0][] = floatval(str_replace(array(' ',','), array('', '.'), $strVal));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %f AND %f";
                                        $arrWhereParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[0]));
                                        $arrWhereParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[1]));
                                    }
                                    else $strValue = floatval(str_replace(array(' ',','), array('', '.'), $row[$strCrit]));
                                }
                                elseif (strstr($row['type'],'date')) // type date
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%t)";
                                        $arrWhereParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %s AND %s";
                                        $arrWhereParams[] = ploopi_local2timestamp($arrCriteria[0]);
                                        $arrWhereParams[] = ploopi_local2timestamp($arrCriteria[1]);
                                    }
                                    else $strValue = ploopi_local2timestamp($row[$strCrit]);
                                }
                                else // Type char/text/enum/???
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%t)";
                                        $arrWhereParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %s AND %s";
                                        $arrWhereParams[] = $arrCriteria[0];
                                        $arrWhereParams[] = $arrCriteria[1];
                                    }
                                    else $strValue = $row[$strCrit];
                                }

                                if ($row["type_{$strCrit}"] != 'between' && $row["type_{$strCrit}"] != 'in')
                                {
                                    switch($row["type_{$strCrit}"])
                                    {
                                        case 'like':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "%{$strValue}%";
                                        break;

                                        case 'begining':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "{$strValue}%";
                                        break;

                                        case 'ending':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "%{$strValue}";
                                        break;

                                        default:
                                            $strSqlWhere .= $row["type_{$strCrit}"].' %s';
                                            $arrWhereParams[] = $strValue;
                                        break;
                                    }
                                }

                                if ($strCrit == 'or' && $booOrIsValid) $strSqlWhere .= ')';
                            }
                            else // Critère sur une opération de type 'Compte', 'Somme', etc....
                            {
                                // un critère sur une opération nécessite l'utilisation de HAVING

                                if ($strCrit == 'criteria' && $booOrIsValid) $strSqlHaving .= '(';
                                if ($strCrit == 'or' && $booOrIsValid) $strSqlHaving .= ' OR ';

                                $strSqlHaving .= ($row['label']) ? "`{$row['label']}` " : "`{$strDefaultLabel}` ";
                                $strValue = '';

                                if (strstr($row['type'],'double') || strstr($row['type'],'float') || in_array($row['operation'], array('count', 'sum', 'avg'))) // type double ou opération arithmétique
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrHavingParams[0][] = floatval(str_replace(array(' ',','), array('', '.'), $strVal));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %f AND %f";
                                        $arrHavingParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[0]));
                                        $arrHavingParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[1]));
                                    }
                                    else $strValue = floatval(str_replace(array(' ',','), array('', '.'), $row[$strCrit]));
                                }
                                elseif (strstr($row['type'],'int')) // type int
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrHavingParams[0][] = intval($strVal, 10);
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %d AND %d";
                                        $arrHavingParams[] = intval($arrCriteria[0], 10);
                                        $arrHavingParams[] = intval($arrCriteria[1], 10);
                                    }
                                    else $strValue = intval($row[$strCrit], 10);
                                }
                                elseif (strstr($row['type'],'date')) // type date
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%t)";
                                        $arrHavingParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %s AND %s";
                                        $arrHavingParams[] = ploopi_local2timestamp($arrCriteria[0]);
                                        $arrHavingParams[] = ploopi_local2timestamp($arrCriteria[1]);
                                    }
                                    else $strValue = ploopi_local2timestamp($row[$strCrit]);
                                }
                                else // Type char/text/enum/???
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%t)";
                                        $arrHavingParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %s AND %s";
                                        $arrHavingParams[] = $arrCriteria[0];
                                        $arrHavingParams[] = $arrCriteria[1];
                                    }
                                    else $strValue = $row[$strCrit];
                                }

                                if ($row["type_{$strCrit}"] != 'between' && $row["type_{$strCrit}"] != 'in')
                                {
                                    switch($row["type_{$strCrit}"])
                                    {
                                        case 'like':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "%{$strValue}%";
                                        break;

                                        case 'begining':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "{$strValue}%";
                                        break;

                                        case 'ending':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "%{$strValue}";
                                        break;

                                        default:
                                            $strSqlHaving .= $row["type_{$strCrit}"].' %s';
                                            $arrHavingParams[] = $strValue;
                                        break;
                                    }
                                }

                                if ($strCrit == 'or' && $booOrIsValid) $strSqlHaving .= ')';
                            }
                        }
                    }
                }
            }

            if ($strSqlWhere != '') $this->objQuery->add_where($strSqlWhere, $arrWhereParams);
            if ($strSqlHaving != '') $this->objQuery->add_having($strSqlHaving, $arrHavingParams);

        } // FIN while


        /**
         * Construction de la clause FROM
         */

        // Recherche des tables de la requête
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('ploopi_mod_dbreport_querytable');
        $objQuery->add_where('id_query = %d', $this->fields['id']);
        $objQuery->add_orderby('id');
        $objRs = $objQuery->execute();

        $arrTables = array();
        $arrJoins = array();

        while ($row = $objRs->fetchrow()) if ($row['tablename'] != '') $arrTables[$row['tablename']] = 1;

        /**
         * Construction de la clause JOIN
         */
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('ploopi_mod_dbreport_queryrelation qr');
        $objQuery->add_where('qr.id_query = %d', $this->fields['id']);
        $objQuery->add_where('qr.active = 1');
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {
            // Stockage des jointures suivant l'origine
            $arrJoins[$row['tablename_src']][$row['tablename_dest']]['type_join'] = $row['type_join'];
            $arrJoins[$row['tablename_dest']][$row['tablename_src']]['type_join'] = $row['type_join'];

            $arrJoins[$row['tablename_src']][$row['tablename_dest']]['relation'][] = "`{$row['tablename_src']}`.`{$row['fieldname_src']}` = `{$row['tablename_dest']}`.`{$row['fieldname_dest']}`";
            $arrJoins[$row['tablename_dest']][$row['tablename_src']]['relation'][] = "`{$row['tablename_dest']}`.`{$row['fieldname_dest']}` = `{$row['tablename_src']}`.`{$row['fieldname_src']}`";
        }

        if (!empty($arrTables)) {
            // Table principale
            $this->objQuery->add_from("`".key($arrTables)."`");

            // Tables "connues" au moment d'ajouter une jointure
            $arrKnownTables = array();
            $arrKnownTables[] = key($arrTables);

            // On parcourt les autres tables pour créer des jointures INNER
            while (next($arrTables) !== false) {
                $strTable = key($arrTables);

                // Recherche des jointures entre la table courante et les tables connues
                foreach($arrKnownTables as $strKnownTable) {
                    if (!empty($arrJoins[$strTable][$strKnownTable])) {
                        switch($arrJoins[$strTable][$strKnownTable]['type_join']) {
                            case 'right':
                                $this->objQuery->add_rightjoin("`{$strTable}` ON ".implode(' AND ', $arrJoins[$strTable][$strKnownTable]['relation']));
                            break;

                            case 'left':
                                $this->objQuery->add_leftjoin("`{$strTable}` ON ".implode(' AND ', $arrJoins[$strTable][$strKnownTable]['relation']));
                            break;

                            default:
                            case 'inner':
                                $this->objQuery->add_innerjoin("`{$strTable}` ON ".implode(' AND ', $arrJoins[$strTable][$strKnownTable]['relation']));
                            break;
                        }
                    }
                }

                // Une nouvelle table connue !
                $arrKnownTables[] = $strTable;
            }
        }

        if ($this->fields['rowlimit']) $this->objQuery->add_limit("0, {$this->fields['rowlimit']}");

        $this->strSqlQuery = $this->objQuery->get_sql();

        return true;
    }

    /**
     * Exécute la requête
     *
     * @param int $intCacheLifetime durée de vie du cache
     * @param boolean $booGetRaw si true, n'applique pas la transformation
     * @return boolean true si la requête a pu être exécutée
     */
    public function exec($intCacheLifetime = 0, $booGetRaw = false)
    {
        // Génération de la requête
        //if ($this->generate($arrParam))
        if (!empty($this->strSqlQuery))
        {
            $objCache = new ploopi_cache($this->getcacheid(), $intCacheLifetime);

            if (!$this->arrResult = $objCache->get_var())
            {
                if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);
                // Exécution de la requête

                $this->_setFr();

                set_time_limit(0);
                // Exécution de la requête
                $objRs = $this->objQuery->execute();

                $this->arrResult = $objRs->getarray();

                if (!$booGetRaw) {
                    // Tableau croisé ?
                    if ($this->fields['transformation'] == 'pivot_table')
                    {
                        // Champs configurés ?
                        if (!empty($this->fields['pivot_x']) && !empty($this->fields['pivot_y']) && !empty($this->fields['pivot_val']))
                        {
                            // Champs valides ?
                            if (isset($this->arrFields[$this->fields['pivot_x']]) && isset($this->arrFields[$this->fields['pivot_y']]) && isset($this->arrFields[$this->fields['pivot_val']]))
                            {

                                $strPivotxField = $this->arrFields[$this->fields['pivot_x']]['label'];
                                $strPivotyField = $this->arrFields[$this->fields['pivot_y']]['label'];
                                $strPivotvalField = $this->arrFields[$this->fields['pivot_val']]['label'];

                                // Construction du jeu de données
                                $arrPivot = array();

                                // Construction du tableau de valeurs pour l'abscisse
                                $arrPivotX = array();
                                foreach($this->arrResult as $row) $arrPivotX[$row[$strPivotxField]] = 1;

                                // Ensuite on crée le tableau croisé
                                foreach($this->arrResult as $row)
                                {
                                    if (!isset($arrPivot[$row[$strPivotyField]])) {
                                        // Init Row
                                        $arrPivot[$row[$strPivotyField]][$strPivotyField] = $row[$strPivotyField];
                                        foreach(array_keys($arrPivotX) as $strPivotX) $arrPivot[$row[$strPivotyField]][$strPivotX] = null;
                                    }
                                    $arrPivot[$row[$strPivotyField]][$row[$strPivotxField]] = $row[$strPivotvalField];
                                }

                                $this->arrResult = $arrPivot;
                                unset($arrPivot);
                            }
                        }
                    }
                }

                $objCache->save_var($this->arrResult);

            }

            return true;
        }
        else return false;
    }



    /**
     * Exécute la requête
     *
     * @param array $arrParam tableau optionnel de paramètres
     * @return boolean true si la requête a pu être exécutée
     */
    public function getrs()
    {
        if (!empty($this->strSqlQuery))
        {
            $this->_setFr();

            set_time_limit(0);
            // Exécution de la requête
            return $this->objQuery->execute();
        }

        return false;
    }


    /**
     * Retourne l'id du cache pour la requête
     *
     * @return string id du cache
     */
    public function getcacheid()
    {
        return empty($this->strSqlQuery) ? false : "dbreport/query/{$this->fields['id']}/{$this->fields['timestp_update']}/".md5($this->strSqlQuery);
    }

    /**
     * Retourne un tableau contenant le résultat de la requête
     *
     * @return array
     */
    public function getresult() { return $this->arrResult; }

    /**
     * Retourne un tableau optimisé contenant le résultat de la requête
     *
     * @return array
     */
    public function getresult_opt()
    {
        $arrArray = $this->getresult();
        $arrNewArray = array('titles' => array(), 'data' => array());
        foreach($arrArray as $intIdLine => $arrLine)
        {
            // Stockage des titres
            if (empty($arrNewArray['titles'])) foreach($arrLine as $strKey => $strValue) $arrNewArray['titles'][] = $strKey;
            // Stockage des données
            foreach($arrLine as $strKey => $strValue) $arrNewArray['data'][$intIdLine][] = $strValue;
        }

        return $arrNewArray;
    }

    /**
     * Retourne le code SQL généré de la requête
     *
     * @return string
     */
    public function getquery() { return $this->strSqlQuery; }



    /**
     * Fonction d'export optimisée pour traiter les gros volumes de données.
     * Pas de bufferisation, pas de stockage des données en mémoire.
     */
    public function export_raw_csv($strFileName = null, $booSetHeaders = true, $strContentDisposition = 'attachment') {

        $strFormat = 'csv';
        $strCharset = 'iso-8859-1';

        ploopi_ob_clean(true);
        ob_start();

        $intSize = 0;

        if (($objRs = $this->getrs()) !== false) {
            $first = true;

            while ($row = $objRs->fetchrow()) {

                if ($first) {
                    if ($booSetHeaders) {
                        $line = '';
                        foreach(array_keys($row) as $str) {
                            if ($line != '') $line .= ',';
                            $line .= '"'.str_replace('"', '""', $str).'"';
                        }
                        $line .= "\n";
                        echo $line;
                        $intSize += mb_strlen($line, '8bit');
                    }

                    $first = false;
                }

                $line = '';
                foreach($row as $key => $str) {
                    if ($line != '') $line .= ',';
                    $line .= '"'.str_replace('"', '""', $str).'"';
                }
                $line .= "\n";
                echo $line;
                $intSize += mb_strlen($line, '8bit');
            }
        }



        if (is_null($strFileName)) $strFileName = "dbreport.{$strFormat}";

        header('Content-Type: '.ploopi_getmimetype($strFileName).'; charset='.$strCharset);
        header('Content-Disposition: '.$strContentDisposition.'; Filename="'.$strFileName.'"');
        header('Cache-Control: private');
        header('Pragma: private');
        header('Content-Length: '.$intSize);
        header('Content-Encoding: none');
        ploopi_die();
    }


    /**
     * Génère le contenu du fichier
     *
     * @param string $strFormat Format du fichier parmi sql, html, xls, sxc, ods, pdf, csv, json, json_opt, xml, ser, txt
     * @param string $strFileName Nom du fichier (sans extension)
     * @param boolean $booSetHeaders true si le headers doivent être générés (true par défaut)
     * @param string $strContentDisposition Type de disposition parmi 'attachment', 'inline'
     */
    public function export($strFormat = 'xml', $strFileName = null, $booSetHeaders = true, $strContentDisposition = 'attachment')
    {
        include_once './include/functions/array.php';
        include_once './include/classes/odf.php';

        set_time_limit(300);

        $strFormat = strtolower($strFormat);
        $strCharset = 'iso-8859-1';

        switch($strFormat)
        {
            case 'sql':
                echo $this->getquery();
            break;

            case 'html':
                echo '
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
                        <style>
                        body {font: 11px Verdana,Tahoma,Arial,sans-serif;}
                        table {padding:0px;margin:2px;border:1px solid #c0c0c0;border-collapse:collapse;}
                        td, th {padding:2px 4px;border:1px solid #888;}
                        th {background-color:#ddd;}
                        </style>
                    </head>
                    <body>'.ploopi_array2html($this->getresult()).'</body></html>';
            break;

            case 'xls':
                echo ploopi_array2excel($this->getresult(), true, 'dbreport.xls', 'query', null, array('writer' => 'excel5'));
            break;

            case 'xlsx':
                echo ploopi_array2excel($this->getresult(), true, 'dbreport.xlsx', 'query', null, array('writer' => 'excel2007'));
            break;

            case 'odt': // experimental
                $strHtml = ploopi_utf8encode('
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
                        <style>
                        body {font: 11px Verdana,Tahoma,Arial,sans-serif;}
                        table {padding:0px;margin:2px;border:1px solid #c0c0c0;border-collapse:collapse;}
                        td, th {padding:2px 4px;border:1px solid #888;}
                        th {background-color:#ddd;}
                        </style>
                    </head>
                    <body>'.ploopi_array2html($this->getresult()).'</body></html>');
                $strCharset = 'UTF-8';

                include_once './lib/xhtml2odt/class.odt.odtfile.php';
                ODTFile::get($strHtml, realpath('.').'/modules/dbreport/odt/template.odt');
            break;

            case 'sxc':
            case 'ods':
            case 'pdf':
                // Génération du fichier XLSX
                $strXlsContent = ploopi_array2excel($this->getresult(), true,  'dbreport.xlsx', 'query', null, array('writer' => 'excel2007'));

                // Instanciation du convertisseur ODF
                $objOdfConverter = new odf_converter(ploopi_getparam('system_jodwebservice', _PLOOPI_MODULE_SYSTEM));

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
                echo $objOdfConverter->convert($strXlsContent, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $strOuputMime);
            break;

            case 'csv':
                echo ploopi_array2csv($this->getresult());
            break;

            case 'json':
                echo ploopi_array2json($this->getresult());
                $strCharset = 'UTF-8';
            break;

            case 'json_opt':
                echo ploopi_array2json($this->getresult_opt());
                $strCharset = 'UTF-8';
            break;

            case 'xml':
                //ploopi_die($objDbrQuery->getresult());
                echo ploopi_array2xml($this->getresult());
            break;

            case 'ser':
                echo serialize($this->getresult());
            break;

            default:
            case 'txt':
                print_r($this->getresult());
            break;
        }

        if ($booSetHeaders)
        {
            if (is_null($strFileName)) $strFileName = "dbreport.{$strFormat}";

            header('Content-Type: '.ploopi_getmimetype($strFileName).'; charset='.$strCharset);
            header('Content-Disposition: '.$strContentDisposition.'; Filename="'.$strFileName.'"');
            header('Cache-Control: private');
            header('Pragma: private');
            header('Content-Length: '.ob_get_length());
            header("Content-Encoding: None");
            header("X-Ploopi: Download"); // Permet d'indiquer au gestionnaire de buffer qu'il s'agit d'un téléchargement de fichier @see ploopi_ob_callback
        }
    }

    /**
     * Retourne l'uri du webservice associé à la requête
     *
     * @return string
     */
    public function getwsuri()
    {
        $arrParams = $this->getparams();

        // Construction de la chaine de paramètres
        $arrUrlParams = array();
        $arrUrlParams['format'] = 'format=xml';
        if (!empty($this->fields['ws_code'])) $arrUrlParams['code'] = "code={$this->fields['ws_code']}";

        foreach($arrParams as $strParam => $row) $arrUrlParams[$strParam] = "{$strParam}=";

        return _PLOOPI_BASEPATH."/wsdbr/{$this->fields['ws_id']}?".implode('&', $arrUrlParams);
    }

    /**
     * Retourne le détail des champs de la requête
     *
     * @return array tableau des champs
     */
    public function getfields()
    {
        $objCol = new data_object_collection('dbreport_queryfield');
        $objCol->add_where('id_query = %d', $this->fields['id']);
        return $objCol->get_objects();
    }

    /**
     * Retourne le détail des paramètres de la requête
     *
     * @return array tableau des paramètres
     */
    public function getparams()
    {
        $arrParams = array();

        // Recherche des champs de la requête et détection des paramètres utilisateur
        $arrObjFields = $this->getfields();
        // Pour chaque champ de la requête
        foreach($arrObjFields as $objField)
        {
            // Pour chaque type de critère
            foreach(array('criteria', 'or') as $strCrit)
            {
                if (!empty($objField->fields[$strCrit]) && $objField->fields[$strCrit][0] == '@') $arrParams[$objField->fields[$strCrit]] = $objField->fields;
            }
        }

        return $arrParams;
    }

    /**
     * Retourne le dernier code d'erreur rencontré
     *
     * @return int
     */
    public function geterror() { return $this->intErrorCode; }

    /**
     * Retourne un objet en fonction de l'id utilisé pour le webservice
     *
     * @param string $strWsId id utilisé pour le webservice
     * @return dbreport_query instance de dbreport_query
     */
    public static function getInstanceByWsId($strWsId)
    {
        include_once './include/classes/data_object_collection.php';

        $objCol = new data_object_collection('dbreport_query');
        $objCol->add_where('ws_id = %s', $strWsId);

        return current($objCol->get_objects());
    }


    /**
     * Retourne les données de la requête dans un format exploitable pour la contruction d'un graphique
     *
     * @param boolean $booAutocomplete true pour compléter les données manquantes dans les séries
     * @return array Tableau des données
     */
    private function _getChartData($booAutocomplete = true) {

        $arrData = array('series' => array(), 'categories' => array());

        $booLegend = $this->fields['chart_legend_display'] == 1;
        $booIndex = $this->fields['chart_indexes_display'] == 1;

        if ($this->fields['chart'] != '' && !empty($this->arrResult))
        {
            // Champs structurants : x,y,val
            $strChartxField = $this->arrFields[$this->fields['chart_x']]['label'];
            $strChartyField = !in_array($this->fields['chart'], array('pie', 'doughnut')) && isset($this->arrFields[$this->fields['chart_y']]['label']) ? $this->arrFields[$this->fields['chart_y']]['label'] : null;
            $strChartvalField = $this->arrFields[$this->fields['chart_val']]['label'];

            // Copie du résultat
            $arrResult = $this->arrResult;

            if ($booAutocomplete && isset($strChartyField)) {

                // Calcul des séries pour X et Y
                $arrValuesX = array();
                $arrValuesY = array();
                // Index des données (on cherche les données manquantes)
                $arrIndex = array();

                // Création des séries de valeurs pour X et Y
                foreach($arrResult as $row) {
                    $arrValuesX[$row[$strChartxField]] = 1;
                    $arrValuesY[$row[$strChartyField]] = 1;
                    $arrIndex[$row[$strChartxField]][$row[$strChartyField]] = 1;
                }

                // On complète ce qu'il manque
                foreach(array_keys($arrValuesX) as $strValueX) {
                    foreach(array_keys($arrValuesY) as $strValueY) {
                        if (!isset($arrIndex[$strValueX][$strValueY])) {
                            $arrResult[] = array(
                                $strChartxField => $strValueX,
                                $strChartyField => $strValueY,
                                $strChartvalField => 0,
                            );
                        }
                    }
                }
            }

            // Calcul des totaux par série (Pour: calcul de %, tri des séries par valeur, limit)
            $arrSeriesY = array();
            $arrSeriesX = array();
            foreach($arrResult as $row) {
                if (isset($strChartxField)) {
                    if (!isset($arrSeriesX[$row[$strChartxField]])) $arrSeriesX[$row[$strChartxField]] = 0;
                    $arrSeriesX[$row[$strChartxField]] += $row[$strChartvalField];
                }

                if (isset($strChartyField)) {
                    if (!isset($arrSeriesY[$row[$strChartyField]])) $arrSeriesY[$row[$strChartyField]] = 0;
                    $arrSeriesY[$row[$strChartyField]] += $row[$strChartvalField];
                }
                else {
                    if (!isset($arrSeriesY[0])) $arrSeriesY[0] = 0;
                    $arrSeriesY[0] += $row[$strChartvalField];
                }
            }

            $strChartSortX = $this->fields['chart_sort_x'];
            $strChartSortY = $this->fields['chart_sort_y'];


            // Sélection des données en fonction de la limite pour chaque dimension
            if ($this->fields['chart_limit_x'] || $this->fields['chart_limit_y']) {
                $intLimitX = $this->fields['chart_limit_x'];
                $intLimitY = $this->fields['chart_limit_y'];

                // Tri X
                switch($strChartSortX) {
                    case 'asc':
                        ksort($arrSeriesX);
                    break;

                    case 'desc':
                        krsort($arrSeriesX);
                    break;

                    case 'asc_val':
                        asort($arrSeriesX);
                    break;

                    case 'desc_val':
                        arsort($arrSeriesX);
                    break;
                }

                if ($intLimitX) {
                    $arrSeriesX = $intLimitX > 0 ? array_slice($arrSeriesX, 0, $intLimitX, true) : array_slice($arrSeriesX, $intLimitX, -$intLimitX, true);
                }

                // Tri Y
                switch($strChartSortY) {
                    case 'asc':
                        ksort($arrSeriesY);
                    break;

                    case 'desc':
                        krsort($arrSeriesY);
                    break;

                    case 'asc_val':
                        asort($arrSeriesY);
                    break;

                    case 'desc_val':
                        arsort($arrSeriesY);
                    break;
                }

                if ($intLimitY) {
                    $arrSeriesY = $intLimitY > 0 ? array_slice($arrSeriesY, 0, $intLimitY, true) : array_slice($arrSeriesY, $intLimitY, -$intLimitY, true);
                }


                // Suppression des données hors limite
                foreach($arrResult as $key => $row) {
                    if (isset($strChartxField) && $this->fields['chart_limit_x'] && !isset($arrSeriesX[$row[$strChartxField]])) unset($arrResult[$key]);
                    elseif (isset($strChartyField) && $this->fields['chart_limit_y'] && !isset($arrSeriesY[$row[$strChartyField]])) unset($arrResult[$key]);
                }
            }
            // Tri des données sur Y puis X, éventuellement par valeur sur X ou Y
            usort($arrResult, function($a, $b) use ($strChartxField, $strChartyField, $strChartSortX, $strChartSortY, $arrSeriesX, $arrSeriesY) {
                $intCmp = 0;
                if (isset($strChartyField)) {
                    switch($strChartSortY) {
                        case 'asc':
                            $intCmp = strnatcmp($a[$strChartyField], $b[$strChartyField]);
                        break;

                        case 'desc':
                            $intCmp = strnatcmp($b[$strChartyField], $a[$strChartyField]);
                        break;

                        case 'asc_val':
                            $intCmp = strnatcmp($arrSeriesY[$a[$strChartyField]], $arrSeriesY[$b[$strChartyField]]);
                        break;

                        case 'desc_val':
                            $intCmp = strnatcmp($arrSeriesY[$b[$strChartyField]], $arrSeriesY[$a[$strChartyField]]);
                        break;
                    }
                }

                if ($intCmp == 0) {
                    if (isset($strChartxField)) {
                        switch($strChartSortX) {
                            case 'asc':
                                $intCmp = strnatcmp($a[$strChartxField], $b[$strChartxField]);
                            break;

                            case 'desc':
                                $intCmp = strnatcmp($b[$strChartxField], $a[$strChartxField]);
                            break;

                            case 'asc_val':
                                $intCmp = strnatcmp($arrSeriesX[$a[$strChartxField]], $arrSeriesX[$b[$strChartxField]]);
                            break;

                            case 'desc_val':
                                $intCmp = strnatcmp($arrSeriesX[$b[$strChartxField]], $arrSeriesX[$a[$strChartxField]]);
                            break;
                        }
                    }
                }

                return $intCmp;
            });




            $arrData['categories'] = array_keys($arrSeriesX);
            $arrSeries = array();


            foreach($arrResult as $key => $row) {

                $strSerie = utf8_encode(isset($strChartyField) ? $row[$strChartyField] : $strChartxField);
                // Nouvelle série ?
                if (!isset($arrSeries[$strSerie])) $arrSeries[$strSerie] = sizeof($arrSeries);

                $keySerie = $arrSeries[$strSerie];

                if (empty($arrData['series'][$keySerie])) {
                    $arrData['series'][$keySerie] = array(
                        'name' => $strSerie,
                        'data' => array(),
                    );

                    if ($this->fields['chart_indexes_display']) {
                        $arrData['series'][$keySerie]['dataLabels'] = array(
                            'enabled' => true,
                            'color' => $this->fields['chart_indexes_font_color'],
                            'style' => array(
                                'fontSize' => $this->fields['chart_indexes_font_size'].'px',
                                'fontFamily' => $this->fields['chart_font'],
                            ),
                            'rotation' => $this->fields['chart_indexes_rotation'],
                            'x' => intval($this->fields['chart_indexes_x']),
                            'y' => intval($this->fields['chart_indexes_y']),
                            /*
                            'align' => 'right',
                            */
                        );

                        if ($this->fields['chart_indexes_format'] != '') {
                            $arrData['series'][$keySerie]['dataLabels']['format'] = $this->fields['chart_indexes_format'];
                        }

                    }


                }

                $arrData['series'][$keySerie]['data'][] = array((string)$row[$strChartxField], floatval($row[$strChartvalField]));

            }

        }

        return $arrData;

    }


    /**
     * Rendu du graphique via la librairie highcharts
     */

    public function displayChart() {

        // Type de graphique ok ?
        if (empty(self::$_arrChartBasicTypes[$this->fields['chart']])) return;
        // Dimension 1 ok ?
        if (empty($this->fields['chart_x']) || !isset($this->arrFields[$this->fields['chart_x']])) return;
        // Dimension 2 ok ?
        if (empty($this->fields['chart_val']) || !isset($this->arrFields[$this->fields['chart_val']])) return;

        // Lecture des données du graphique
        $arrData = $this->_getChartData();

        $strChartxField = $this->arrFields[$this->fields['chart_x']]['label'];
        $strChartyField = !in_array($this->fields['chart'], array('pie', 'doughnut')) && isset($this->arrFields[$this->fields['chart_y']]['label']) ? $this->arrFields[$this->fields['chart_y']]['label'] : null;
        $strChartvalField = $this->arrFields[$this->fields['chart_val']]['label'];

        ?>

        <div id="container" style="display:block; height: <? echo $this->fields['chart_height']; ?>px; width: <? echo $this->fields['chart_width']; ?>px;"></div>
        <script type="text/javascript">


        Event.observe(window, 'load', function() {
            var chart = new Highcharts.Chart({

                colors: <? echo $this->fields['chart_colorset'] ? self::getChartColorSetJson($this->fields['chart_colorset']) : "['{$this->fields['chart_color']}']"; ?>,
                lang: {
                    decimalPoint: '.',
                    thousandsSep: ' ',
                    downloadJPEG: '<? echo utf8_encode('Télécharger JPG'); ?>',
                    downloadPDF: '<? echo utf8_encode('Télécharger PDF'); ?>',
                    downloadPNG: '<? echo utf8_encode('Télécharger PNG'); ?>',
                    downloadSVG: '<? echo utf8_encode('Télécharger SVG'); ?>',
                    printChart: '<? echo utf8_encode('Imprimer'); ?>',
                    contextButtonTitle: '<? echo utf8_encode('Menu contextuel'); ?>',
                },
                chart: {
                    renderTo: 'container',
                    type: '<? echo addslashes(strip_tags(self::$_arrChartBasicTypes[$this->fields['chart']])); ?>',
                    // margin: [ 50, 50, 100, 80],
                    backgroundColor: '<? echo addslashes(strip_tags($this->fields['chart_background'])); ?>',
                    animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                    borderWidth: <? echo $this->fields['chart_border_width']; ?>,
                    borderColor: '<? echo addslashes(strip_tags($this->fields['chart_border_color'])); ?>',
                },
                title: {
                    text: '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_title']))); ?>',
                    style: {
                        fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                        fontSize: '<? echo $this->fields['chart_title_font_size']; ?>px',
                        color: '<? echo addslashes(strip_tags($this->fields['chart_title_font_color'])); ?>',
                    }
                },
                subtitle: {
                    text: '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_subtitle']))); ?>',
                    style: {
                        fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                        fontSize: '<? echo $this->fields['chart_title_font_size']/2; ?>px',
                        color: '<? echo addslashes(strip_tags($this->fields['chart_title_font_color'])); ?>',
                    }
                },
                legend: {
                    backgroundColor: '#FFFFFF',
                    reversed: false,
                    enabled: <? echo $this->fields['chart_legend_display'] ? 'true' : 'false'; ?>,
                    align: '<? echo addslashes(strip_tags($this->fields['chart_legend_align'])); ?>',
                    verticalAlign: '<? echo addslashes(strip_tags($this->fields['chart_legend_valign'])); ?>',
                    style: {
                        fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                        fontSize: '<? echo $this->fields['chart_legend_font_size']; ?>px',
                        color: '<? echo addslashes(strip_tags($this->fields['chart_legend_font_color'])); ?>',
                    },
                    // labelFormat: '{point.y:.2f}',

                },
                tooltip: {
                    <? if ($this->fields['chart_tooltip_format'] != '') { ?>
                    pointFormat: '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_tooltip_format']))); ?>',
                    <? } ?>
                    shared: true,
                },
                plotOptions: {
                    series: {
                        <? if (in_array($this->fields['chart'], array('stackedColumn', 'stackedBar', 'stackedLine', 'stackedSpline', 'stackedArea', 'stackedSplineArea'))) { ?>
                        stacking: 'normal'
                        <? } ?>
                        <? if (in_array($this->fields['chart'], array('stackedColumn100', 'stackedBar100', 'stackedLine100', 'stackedSpline100', 'stackedArea100', 'stackedSplineArea100'))) { ?>
                        stacking: 'percent'
                        <? } ?>
                    },
                    pie: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        allowPointSelect: true,
                        shadow: true,
                        center: ['50%', '50%'],
                        <? if ($this->fields['chart'] == 'doughnut') { ?>
                        size: '80%', // Donut
                        innerSize: '50%', // Donut
                        <? } ?>
                    },
                    spline: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        lineWidth: <? echo $this->fields['chart_line_thickness']; ?>,
                        marker: {
                            enabled: true
                        },
                    },
                    line: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        lineWidth: <? echo $this->fields['chart_line_thickness']; ?>,
                        marker: {
                            enabled: true
                        },
                    },
                    area: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        lineWidth: <? echo $this->fields['chart_line_thickness']; ?>,
                        marker: {
                            enabled: true
                        },
                    },
                    areaspline: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        lineWidth: <? echo $this->fields['chart_line_thickness']; ?>,
                        marker: {
                            enabled: true
                        },
                    },
                    bar: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        borderWidth: 0,
                    },
                    column: {
                        animation: <? echo $this->fields['chart_animation'] ? 'true' : 'false'; ?>,
                        borderWidth: 0,
                    },
                },



                xAxis: {
                    categories: <? echo json_encode($arrData['categories']); ?>,
                    title: {
                        text: '<? echo utf8_encode(addslashes(strip_tags($strChartxField))); ?>',
                        style: {
                            fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                            fontSize: '<? echo $this->fields['chart_axis_font_size']; ?>px',
                            color: '<? echo addslashes(strip_tags($this->fields['chart_axis_font_color'])); ?>',
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                            fontSize: '<? echo $this->fields['chart_axis_font_size']; ?>px',
                            color: '<? echo addslashes(strip_tags($this->fields['chart_axis_font_color'])); ?>',
                        },
                        formatter: function() {
                            return '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_value_x_prefix']))); ?>' + this.value + '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_value_x_suffix']))); ?>';
                        }

                    },
                    <? if ($this->fields['chart_interlaced_display']) { ?>
                        alternateGridColor: '<? echo addslashes(strip_tags($this->fields['chart_interlaced_x_color'])); ?>',
                    <? } ?>
                    gridLineColor: '<? echo addslashes(strip_tags($this->fields['chart_grid_color'])); ?>',
                    gridLineWidth: <? echo $this->fields['chart_grid_x_thickness']; ?>,
                    lineColor: '<? echo addslashes(strip_tags($this->fields['chart_axis_color'])); ?>',
                    lineWidth: <? echo $this->fields['chart_axis_x_thickness']; ?>,
                    tickColor: '<? echo addslashes(strip_tags($this->fields['chart_axis_color'])); ?>',
                    tickWidth: <? echo $this->fields['chart_axis_x_thickness']; ?>,

                },
                yAxis: {
                    title: {
                        text: '<? echo utf8_encode(addslashes(strip_tags($strChartvalField))); ?>',
                        style: {
                            fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                            fontSize: '<? echo $this->fields['chart_axis_font_size']; ?>px',
                            color: '<? echo addslashes(strip_tags($this->fields['chart_axis_font_color'])); ?>',
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: '<? echo addslashes(strip_tags($this->fields['chart_font'])); ?>',
                            fontSize: '<? echo $this->fields['chart_axis_font_size']; ?>px',
                            color: '<? echo addslashes(strip_tags($this->fields['chart_axis_font_color'])); ?>',
                        },
                        formatter: function() {
                            return '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_value_y_prefix']))); ?>' + this.value + '<? echo utf8_encode(addslashes(strip_tags($this->fields['chart_value_y_suffix']))); ?>';
                        }
                    },
                    <? if ($this->fields['chart_interlaced_display']) { ?>
                        alternateGridColor: '<? echo addslashes(strip_tags($this->fields['chart_interlaced_y_color'])); ?>',
                    <? } ?>
                    gridLineColor: '<? echo addslashes(strip_tags($this->fields['chart_grid_color'])); ?>',
                    gridLineWidth: <? echo $this->fields['chart_grid_y_thickness']; ?>,
                    lineColor: '<? echo addslashes(strip_tags($this->fields['chart_axis_color'])); ?>',
                    lineWidth: <? echo $this->fields['chart_axis_y_thickness']; ?>,
                    tickColor: '<? echo addslashes(strip_tags($this->fields['chart_axis_color'])); ?>',
                    tickWidth: <? echo $this->fields['chart_axis_y_thickness']; ?>,

                },
                series: <? echo json_encode($arrData['series']); ?>,
                credits: {
                    enabled: true,
                    href: 'http://www.ploopi.org',
                    text: 'Ploopi / DbReport',
                }
            });
        });

        </script>
        <?
    }
}
?>
