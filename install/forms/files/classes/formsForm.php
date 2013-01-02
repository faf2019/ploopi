<?php
/*
    Copyright (c) 2007-2011 Ovensia
    Copyright (c) 2010 HeXad
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
 * Gestion des formulaires
 *
 * @package forms
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Autres dépendances
 */

include_once './include/classes/data_object_collection.php';
include_once './include/classes/query.php';
include_once './include/classes/mb.php';
include_once './include/classes/form.php';
include_once './include/classes/module.php';

include_once './modules/forms/classes/formsField.php';
include_once './modules/forms/classes/formsGraphic.php';
include_once './modules/forms/classes/formsGroup.php';
include_once './modules/forms/classes/formsBooleanParser.php';
include_once './modules/forms/classes/formsArithmeticParser.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_form
 *
 * @package forms
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class formsForm extends data_object
{
    /**
     * Tableau des champs du formulaire
     * @var array
     */

    private $_arrFields;

    /**
     * Tableau des champs et séparateurs du formulaire
     * @var array
     */

    private $_arrFieldsWithSep;

    /**
     * @var Nom de la table associée au formulaire
     */
    private $_strTablename = null;

    /**
     * Champs statiques d'un formulaire (date de validation, utilisateur, groupe, ip)
     */

    private static $_arrStaticFields = array();

    private static function _initStaticFields()
    {
        if (empty(self::$_arrStaticFields))
        {
            ploopi_init_module('forms');
            self::$_arrStaticFields = array(
                'date_validation' => _FORMS_DATEVALIDATION,
                'user_login' => _FORMS_USER,
                'workspace_label' => _FORMS_WORKSPACE,
                'ip' => _FORMS_IP
            );
        }
    }

    /**
     * Traduit une date FR au format XLS
     */

    private static function _dateFrToXls($strDate)
    {
        if (empty($strDate)) return '';

        $arrDate = explode(' ', $strDate);
        $intTs = ploopi_timestamp2unixtimestamp(ploopi_local2timestamp($arrDate[0], isset($arrDate[1]) ? $arrDate[1] : '00:00:00'));

        return empty($intTs) ? '' : $intTs / 86400 + 25569 + 1/12;
    }

    /**
     * Retourne le nom de la variable pour le stockage temporaire des données (cookie/session)
     */

    public static function getVarName($intIdForm) { return sprintf("form_values_%d_%d", $intIdForm, $_SESSION['ploopi']['userid']); }

    /**
     * Retourne les champs statiques du formulaire
     * @return array tableau contenant les champs
     */

    public static function getStaticFields()
    {
        self::_initStaticFields();
        return self::$_arrStaticFields;
    }

    /**
     * Constructeur de la classe
     *
     * @return form
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_forms_form');
        self::_initStaticFields();
        $this->_arrFields = null;
        $this->_arrFieldsWithSep = null;
    }

    /**
     * Gère le clone
     */

    public function __clone()
    {
        // Stock ancien ID
        $intOldId = $this->fields['id'];
        $strOldTableName = $this->getDataTableName();

        // Personnalisation du clone
        $this->new = true;
        $this->fields['label'] = 'Clone de '.$this->fields['label'];
        $this->fields['id'] = null;
        $this->fields['viewed'] = 0;

        // Enregistrement du clone pour récupérer une nouvel ID
        $this->save();

        // Correspondances de clones
        $arrGroups = array();
        $arrFields = array();

        // Clonage des groupes
        $objDoc = new data_object_collection('formsGroup');
        $objDoc->add_where('id_form = %d', $intOldId);
        foreach($objDoc->get_objects() as $obj)
        {
            $objClone = clone $obj;
            $objClone->fields['id_form'] = $this->fields['id'];
            $objClone->save();

            $arrGroups[$obj->fields['id']] = $objClone;
        }

        // Clonage des champs
        $objDoc = new data_object_collection('formsField');
        $objDoc->add_where('id_form = %d', $intOldId);
        foreach($objDoc->get_objects() as $obj)
        {
            $objClone = clone $obj;
            $objClone->fields['id_form'] = $this->fields['id'];

            // Récupération du bon groupe (le clone)
            if (isset($arrGroups[$objClone->fields['id_group']])) $objClone->fields['id_group'] = $arrGroups[$objClone->fields['id_group']]->fields['id'];
            $objClone->save();

            $arrFields[$obj->fields['id']] = $objClone;
        }

        // Clonage des graphiques
        $objDoc = new data_object_collection('formsGraphic');
        $objDoc->add_where('id_form = %d', $intOldId);
        foreach($objDoc->get_objects() as $obj)
        {
            $objClone = clone $obj;
            $objClone->fields['id_form'] = $this->fields['id'];

            // Récupération des bons champs (clones)
            for ($i=1;$i<=5;$i++) if (isset($arrFields[$objClone->fields["line{$i}_field"]])) $objClone->fields["line{$i}_field"] = $arrFields[$objClone->fields["line{$i}_field"]]->fields['id'];

            $objClone->save();
        }

        // Il faut impacter la composition des groupes avec les nouveaux champs
        foreach($arrGroups as $objGroup)
        {
            $arrConditions = $objGroup->getConditions();
            foreach($arrConditions as $key => $row) if (isset($arrFields[$arrConditions[$key]['field']])) $arrConditions[$key]['field'] = $arrFields[$arrConditions[$key]['field']]->fields['id'];

            $objGroup->fields['conditions'] = ploopi_serialize($arrConditions);
            $objGroup->save();
        }
    }

    /**
     * Enregistre le formulaire
     *
     * @return int indentifiant du formulaire
     */

    public function save($booExport = true)
    {
        $booIsNew = $this->isnew();

        $res = parent::save();

        if ($booIsNew) $this->generateTable();
        $this->updateMetabase();

        return $res;
    }

    /**
     * Supprime le formulaire
     */

    public function delete()
    {
        // Suppression des champs du formulaire
        foreach($this->getFields(true) as $objField) $objField->delete();

        // Suppression des graphiques du formulaire
        foreach($this->getGraphics() as $objGraphic) $objGraphic->delete();

        // Suppression des groupes du formulaire
        foreach($this->getGroups() as $objGroup) $objGroup->delete();

        // Suppression de la métabase associée
        $this->removeMetabase();

        // Suppression de la représentation physique du formulaire
        $this->_dropTable();

        // Suppression de la représentation conceptuelle du formulaire
        return parent::delete();
    }

    /**
     * Retourne une liste d'identifiants d'espaces de travail.
     * Application de la vue aux données d'un formulaire.
     */

    public function viewWorkspaces()
    {

        switch($this->fields['option_view'])
        {
            default:
            case 'private':
                $strWorkspaces = $_SESSION['ploopi']['workspaceid'];
            break;

            case 'desc':
                $arrWorkspaces = explode(';',$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['parents']);
                $arrWorkspaces[] = $_SESSION['ploopi']['workspaceid'];
                $strWorkspaces = implode(',', $arrWorkspaces);
            break;

            case 'asc':
                $objWorkspace = new workspace();
                $objWorkspace->open($_SESSION['ploopi']['workspaceid']);
                $arrWorkspaces = array_keys($objWorkspace->getChildren());
                $arrWorkspaces[] = $_SESSION['ploopi']['workspaceid'];
                $strWorkspaces = implode(',', $arrWorkspaces);
            break;

            case 'global':
                $strWorkspaces = '';
            break;
        }

        return $strWorkspaces;
    }

    /**
     * Initialise le nom utilisé pour la table physique
     */

    private function _setDataTableName()
    {
        $this->_strTablename = sprintf("form_%d_%d", $this->fields['id_module'], $this->fields['id']);
    }

    /**
     * Retourne le nom utilisé pour la table physique
     */

    public function getDataTableName()
    {
        $this->_setDataTableName();
        return $this->_strTablename;
    }

    /**
     * Retourne la liste des graphiques
     *
     * @return array tableau des graphiques indexés par les identifiants
     */

    public function getGraphics()
    {
        $objDOC = new data_object_collection('formsGraphic');
        $objDOC->add_where('id_form = %d', $this->fields['id']);
        return $objDOC->get_objects(true);
    }

    /**
     * Retourne la liste des groupes
     *
     * @return array tableau des groupes indexés par les identifiants
     */

    public function getGroups()
    {
        $objDOC = new data_object_collection('formsGroup');
        $objDOC->add_where('id_form = %d', $this->fields['id']);
        return $objDOC->get_objects(true);
    }

    /**
     * Retourne la liste des champs du formulaire
     *
     * @param boolean $booWidthSep true si on souhaite récupérer également les séparateurs
     * @return array tableau des champs indexés par les identifiants
     */

    public function getFields($booNotOnlyFields = false)
    {
        if ($booNotOnlyFields)
        {
            if (is_null($this->_arrFieldsWithSep))
            {
                $objDOC = new data_object_collection('formsField');
                $objDOC->add_where('id_form = %d', $this->fields['id']);
                $objDOC->add_orderby('position');

                $this->_arrFieldsWithSep = $objDOC->get_objects(true);
            }

            return $this->_arrFieldsWithSep;
        }
        else
        {
            if (is_null($this->_arrFields))
            {
                $objDOC = new data_object_collection('formsField');
                $objDOC->add_where('id_form = %d', $this->fields['id']);
                $objDOC->add_where('`separator` = 0');
                $objDOC->add_where('`html` = 0');
                $objDOC->add_where('`captcha` = 0');
                $objDOC->add_orderby('position');

                $this->_arrFields = $objDOC->get_objects(true);
            }

            return $this->_arrFields;
        }
    }

    /**
     * Retourne les titres de colonnes
     * @return array titres de colonnes
     */

    public function getTitles()
    {
        // Tableau des titres de colonnes
        $arrTitles = array();

        foreach(self::$_arrStaticFields as $strKey => $strValue)
        {
            $booDisplay = false;
            switch($strKey)
            {
                case 'date_validation':
                    $booDisplay = ($this->fields['option_displaydate']);
                break;

                case 'user_login':
                    $booDisplay = ($this->fields['option_displayuser']);
                break;

                case 'workspace_label':
                    $booDisplay = ($this->fields['option_displaygroup']);
                break;

                case 'ip':
                    $booDisplay = ($this->fields['option_displayip']);
                break;
            }

            $arrTitles[$strKey] = array('label' => $strValue, 'exportview' => $booDisplay, 'arrayview' => $booDisplay, 'wceview' => false, 'adminonly' => false, 'type' => '', 'format' => 'string');
        }

        foreach($this->getFields() as $strKey => $objField)
        {
            $arrTitles[$strKey] = array(
                'label' => $objField->fields['name'],
                'exportview' => $objField->fields['option_exportview'],
                'arrayview' => $objField->fields['option_arrayview'],
                'wceview' => $objField->fields['option_wceview'],
                'adminonly' => $objField->fields['option_adminonly'],
                'type' => $objField->fields['type'],
                'format' => $objField->fields['format']
            );
        }

        return $arrTitles;
    }


    /**
     * Retourne le nombre de lignes du formulaire en fonction du filtre saisie par jour/utilisateur
     * @return int nombre de lignes du formulaire
     */

    public function getNumRowsOnly()
    {
        if (!$this->fields['option_onlyone'] && !$this->fields['option_onlyoneday']) return 0;

        $objQuery = new ploopi_query_select();
        $objQuery->add_select('count(*) as c');
        $objQuery->add_from($this->getDataTableName());

        if ($this->fields['option_onlyone']) $objQuery->add_where('user_id = %d', $_SESSION['ploopi']['userid']);
        if ($this->fields['option_onlyoneday']) $objQuery->add_where('LEFT(date_validation,8) = %s', substr(ploopi_createtimestamp(), 0, 8));

        return current($objQuery->execute()->getarray(true));
    }


    /**
     * Retourne le nombre de lignes du formulaire en fonction de la visibilité de l'utilisateur sur les données
     * @param boolean $booWorkspaceFilter true si on souhaite appliquer le filtrage par espace de travail
     * @return int nombre de lignes du formulaire
     */

    public function getNumRows($booWorkspaceFilter = false)
    {
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('count(*) as c');
        $objQuery->add_from($this->getDataTableName());

        /**
         * Filtrage en fonction de la vue du module sur les espaces
         */
        if ($booWorkspaceFilter)
        {
            $arrWorkspaces = $this->viewWorkspaces();
            if (!empty($arrWorkspaces)) $objQuery->add_where('workspace_id IN (%e)', array($arrWorkspaces));
        }

        return current($objQuery->execute()->getarray(true));
    }

    /**
     * Génère l'objet "ploopi_query" contenant la requête SQL permettant de lire les données du formulaire dans la base de données
     * @param boolean $booWorkspaceFilter true si on souhaite appliquer le filtrage par espace de travail
     * @param boolean $booBackupFilter true si on souhaite appliquer l'archivage des données
     * @param array $arrFilter tableau permettant d'appliquer un filtre de recherche
     * @param array $arrOrderBy tableau permettant de définir le tri
     * @param integer $intNumPage numéro de la page à afficher
     * @param boolean $booFieldNamesAsKey true pour utiliser les noms de champs comme clé du tableau de données
     * @return ploopi_query la requête préparée
     */

    private function _getQuery($booWorkspaceFilter = false, $booBackupFilter = false, $arrFilter = array(), $arrOrderBy = array(), $intNumPage = 0, $booFieldNamesAsKey = false, $booExport = false, $booRawData = false, $booDelete = false)
    {
        // L'utilisateur est admin ?
        $booIsAdmin = ploopi_isactionallowed(_FORMS_ACTION_ADMIN, -1, $this->fields['id_module']);

        /**
         * Tableau contenant les champs du formulaire
         */
        $arrObjField = $this->getFields();

        /**
         * Structure de base de la requête de consultation des données du formulaire
         */
        $objQuery = $booDelete ? new ploopi_query_delete() : new ploopi_query_select();

        $objQuery->add_from('`'.$this->getDataTableName().'` rec');

        // Requête de suppression : pas besoin de sélectionner les champs
        if (!$booDelete)
        {
            /**
             * Attention, si booExport = true, on adapte la requête pour ne récupérer que les champs exportables (pour ne pas retraiter les données ensuite)
             */

            // Seul l'admin peut exporter le champ ID, par contre on en a besoin en visu
            if (!$booExport || ploopi_isactionallowed(_FORMS_ACTION_ADMIN, -1, $this->fields['id_module'])) $objQuery->add_select('rec.`#id` as `id`');

            if ($booExport || $this->fields['option_displaydate'])
            {
                if ($booRawData) $objQuery->add_select('rec.`date_validation`');
                else $objQuery->add_select("CONCAT(SUBSTRING(rec.`date_validation`, 7, 2), '/', SUBSTRING(rec.`date_validation`, 5, 2), '/', SUBSTRING(rec.`date_validation`, 1, 4), ' ', SUBSTRING(rec.`date_validation`, 9, 2), ':', SUBSTRING(rec.`date_validation`, 11, 2), ':', SUBSTRING(rec.`date_validation`, 13, 2)) as `date_validation`");
            }

            /**
             * Construction des jointures sur les champs statiques (ip, user, etc...)
             */
            $objQuery->add_select('rec.`user_id`');

            if ($booExport || $this->fields['option_displayuser'])
            {

                $objQuery->add_leftjoin('ploopi_user pu ON pu.id = rec.user_id');
                $objQuery->add_select('pu.login as `user_login`');
                $objQuery->add_select('pu.firstname as `user_firstname`');
                $objQuery->add_select('pu.lastname as `user_lastname`');
            }

            $objQuery->add_select('rec.`workspace_id`');

            if ($booExport || $this->fields['option_displaygroup'])
            {
                $objQuery->add_leftjoin('ploopi_workspace pw ON pw.id = rec.workspace_id');
                $objQuery->add_select('pw.label as `workspace_label`');
                $objQuery->add_select('pw.code as `workspace_code`');
            }
            if ($booExport || $this->fields['option_displayip']) $objQuery->add_select('rec.`ip`');
        }



        /**
         * Filtrage en fonction de la vue du module sur les espaces
         */
        if ($booWorkspaceFilter)
        {
            $arrWorkspaces = $this->viewWorkspaces();
            if (!empty($arrWorkspaces)) $objQuery->add_where('workspace_id IN (%e)', array($arrWorkspaces));
        }

        /**
         * Filtrage en fonction des paramètres d'archivage
         */
        if ($booBackupFilter)
        {
            if (empty($_SESSION['forms'][$this->fields['id']]['unlockbackup']))
            {
                if ($this->fields['autobackup'] > 0) $objQuery->add_where('rec.date_validation >= %s', ploopi_timestamp_add(ploopi_createtimestamp(), 0, 0, 0, 0, -$this->fields['autobackup']));
                if (!empty($this->fields['autobackup_date'])) $objQuery->add_where('rec.date_validation >= %s', ploopi_timestamp_add($this->fields['autobackup_date'], 0, 0, 0, 0, 1, 0));
            }
        }



        if (!$booDelete)
        {
            /**
             * Sélection des champ
             */

            foreach($arrObjField as $objField)
            {
                // Choix de la colonne en fonction de critères multiples
                // 1. Export + visibilité et droit admin
                // 2. Affichage + visibilité
                if (($booExport && $objField->fields['option_exportview'] && ($booIsAdmin || !$objField->fields['option_adminonly'])) || (!$booExport && $objField->fields['option_arrayview']))
                {
                    $strAlias = $booFieldNamesAsKey ? $objField->fields['fieldname'] : $objField->fields['id'];
                    // Traitement spécial des données DATE
                    // Pas l'idéal de faire ça au niveau SQL mais permet d'éviter un post traitement
                    if ($objField->fields['format'] == 'date')
                        $strSelect = $booRawData ? "IF(rec.`{$objField->fields['fieldname']}` = 0, '', CONCAT(rec.`{$objField->fields['fieldname']}`, '000000'))" : "IF(rec.`{$objField->fields['fieldname']}` = 0, '', CONCAT(SUBSTRING(rec.`{$objField->fields['fieldname']}`,7,2), '/', SUBSTRING(rec.`{$objField->fields['fieldname']}`,5,2), '/', SUBSTRING(rec.`{$objField->fields['fieldname']}`,1,4)))";
                    else
                        $strSelect = "rec.`{$objField->fields['fieldname']}`";

                    // Ajout du select
                    $objQuery->add_select("{$strSelect} as `{$strAlias}`");
                }
            }
        }
        else $objQuery->add_delete('rec');

        /**
         * Application du filtre utilisateur
         */
        foreach($arrFilter as $strIdField => $arrFieldFilter)
        {
            foreach($arrFieldFilter as $row)
            {
                // On stocke la valeur du filtre dans un tableau (pour traitement générique)
                switch($row['op'])
                {
                    case 'in':
                        // Découpage sur la caractère ","
                        $arrValues = explode(',', $row['value']);
                    break;

                    case 'between':
                        // Découpage sur la caractère ";"
                        $arrValues = explode(';', $row['value']);
                        // Contrôle du nombre de valeurs (2 attendues)
                        if (sizeof($arrValues) < 2) $arrValues[1] = '';
                    break;

                    default:
                        $arrValues = array($row['value']);
                    break;
                }

                if (is_numeric($strIdField)) // Filtre sur champ dynamique du formulaire
                {
                    if (isset($arrObjField[$strIdField]))
                    {
                        $strFieldName = "rec.`{$arrObjField[$strIdField]->fields['fieldname']}`";

                        /**
                         * Traitement spécial sur les données du filtre en fonction du format du champ
                         * Par exemple, si l'utilisateur saisit une date, on la transforme
                         */
                        switch ($arrObjField[$strIdField]->fields['format'])
                        {
                            case 'date':
                                foreach($arrValues as $key => $null) $arrValues[$key] = substr(ploopi_local2timestamp($arrValues[$key]), 0, 8);
                            break;

                            case 'time':
                                /**
                                 * @todo : time format
                                 */
                            break;

                        }
                    }

                }
                else // Filtre sur champ statique (ip, user, etc...)
                {
                    switch($strIdField)
                    {
                        case 'user_login': $strFieldName = 'rec.`user_login`'; break;
                        case 'workspace_label': $strFieldName = 'rec.`workspace_label`'; break;
                        case 'ip': $strFieldName = 'rec.`ip`'; break;
                        case 'date_validation':
                            $strFieldName = 'rec.`date_validation`';
                            foreach($arrValues as $key => $value)
                            {
                                $arrDT = explode(' ', $value);
                                // Date et heure
                                if (sizeof($arrDT) >= 2) $arrValues[$key] = ploopi_local2timestamp($arrDT[0], $arrDT[1]);
                                // Date seule
                                else
                                {
                                    $arrValues[$key] = ploopi_local2timestamp($arrDT[0]);
                                    // Cas particulier: recherche exacte sur date seule
                                    if ($row['op'] == '=')
                                    {
                                        $row['op'] = 'begin';
                                        $arrValues[$key] = substr($arrValues[$key], 0, 8);
                                    }
                                }
                            }
                        break;
                        default: $strFieldName = ''; break;
                    }
                }

                // Intégration du filtre dans la requête SQL
                if (!empty($strFieldName))
                {
                    switch($row['op'])
                    {
                        case '=':
                        case '>':
                        case '<':
                        case '>=':
                        case '<=':
                        case '<>':
                            $objQuery->add_where("{$strFieldName} {$row['op']} %s", $arrValues[0]);
                        break;

                        case 'in':
                            $objQuery->add_where("{$strFieldName} in (%t)", array($arrValues));
                        break;

                        case 'between':
                            $objQuery->add_where("{$strFieldName} between %s and %s", $arrValues);
                        break;

                        case 'begin':
                            $objQuery->add_where("{$strFieldName} like %s", "{$arrValues[0]}%");
                        break;

                        case 'like':
                            $objQuery->add_where("{$strFieldName} like %s", "%{$arrValues[0]}%");
                        break;
                    }
                }
            }
        }



        if (!$booDelete)
        {

            /**
             * Application du critère de tri
             */

            foreach($arrOrderBy as $strIdField => $strWay)
            {
                if (is_numeric($strIdField)) // Filtre sur champ dynamique du formulaire
                {
                    $strFieldName = "rec.`{$arrObjField[$strIdField]->fields['fieldname']}`";
                }
                else // Filtre sur champ statique (ip, user, etc...)
                {
                    switch($strIdField)
                    {
                        case 'user_login': $strFieldName = 'rec.`user_login`'; break;
                        case 'workspace_label': $strFieldName = 'rec.`workspace_label`'; break;
                        case 'date_validation': $strFieldName = 'rec.`date_validation`'; break;
                        case 'ip': $strFieldName = 'rec.`ip`'; break;
                        default: $strFieldName = ''; break;
                    }
                }


                if (in_array($strWay, array('ASC', 'DESC'))) $objQuery->add_orderby("{$strFieldName} {$strWay}");
            }

            /**
             * Application de la clause LIMIT
             */

            if (!empty($this->fields['nbline']) && !empty($intNumPage) && is_numeric($this->fields['nbline']) && is_numeric($intNumPage))
            {
                $intLimitStart = ($intNumPage-1)*$this->fields['nbline'];

                $objQuery->add_limit("{$intLimitStart}, {$this->fields['nbline']}");
            }
        }

        return $objQuery;
    }



    /**
     * Retourne toutes les données brutes du formulaire
     * @return array données du formulaire
     */

    public function getRawData()
    {
        $arrData = array();

        // Lecture des champs du formulaire
        $arrObjFields = $this->getFields();

        // Lecture des données
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('`'.$this->getDataTableName().'` rec');
        $objQuery->add_orderby('`#id`');

        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow()) {

            foreach($arrObjFields as $objField) {
                $arrData[$row['#id']]['C'.$objField->fields['position']] = $row[$objField->fields['fieldname']];
            }
        }

        return $arrData;
    }

    /**
     * Retourne les données filtrées du formulaire ainsi que le nombre de ligne (avec filtre, toutes pages comprises)
     * @param boolean $booWorkspaceFilter true si on souhaite appliquer le filtrage par espace de travail
     * @param boolean $booBackupFilter true si on souhaite appliquer l'archivage des données
     * @param array $arrFilter tableau permettant d'appliquer un filtre de recherche
     * @param array $arrOrderBy tableau permettant de définir le tri
     * @param integer $intNumPage numéro de la page à afficher
     * @param boolean $booFieldNamesAsKey true pour utiliser les noms de champs comme clé du tableau de données
     * @return array données du formulaire
     */

    private function _getData($booWorkspaceFilter = false, $booBackupFilter = false, $arrFilter = array(), $arrOrderBy = array(), $intNumPage = 0, $booFieldNamesAsKey = false, $booExport = false, $booRawData = false, $booDelete = false)
    {
        /**
         * Construction de la requête
         * @var ploopi_query
         */
        $objQuery = $this->_getQuery($booWorkspaceFilter, $booBackupFilter, $arrFilter, $arrOrderBy, $intNumPage, $booFieldNamesAsKey, $booExport, $booRawData, $booDelete);


        if ($booDelete)
        {
            $objQuery->execute();
            return null;
        }

        /**
         * Requête spéciale pour compter les enregistrements (sans "limit")
         * @var ploopi_query
         */
        $objCount = clone $objQuery;
        $objCount->remove_select();
        $objCount->add_select('count(*) as c');
        $objCount->remove_limit();

        /**
         * Retourne les données de la page sélectionnée et le nombre d'enregistrement total
         */
        return array($objQuery->execute()->getArray(), current($objCount->execute()->getarray(true)));
    }

    /**
     * Suppression des données selon le filtre utilisateur
     */
    public function deleteData()
    {
        $_SESSION['forms'][$this->fields['id']] = array();
        ploopi_setsessionvar('filter', null);
        ploopi_setsessionvar('formfilter', null);

        $this->prepareData(false, false, false, false, true);

        // Doit on recalculer les données ? (uniquement si agrégat)
        $booCalculation = false;
        $booAggregate = false;

        foreach($this->getFields() as $objField)
        {
            if ($objField->fields['type'] == 'calculation')
            {
                $booCalculation = true;

                // On va détecter si le calcul fait appel à un agrégat (dans ce cas il faut recalculer toutes les données du formulaire)
                $objParser = new formsArithmeticParser($objField->fields['formula']);

                // Extraction des variables de l'expression
                $arrVars = $objParser->getVars();

                // Pour chaque variable attendue dans l'expression
                foreach($arrVars as $strVar)
                {
                    // Analyse de la variable
                    if (preg_match('/C([0-9]+)_?([A-Z]{0,3})/', $strVar, $arrMatches) > 0)
                    {
                        if (!empty($arrMatches[2])) $booAggregate = true;
                    }
                }
            }
        }

        if ($booAggregate) $this->calculate();
    }


    /**
     * Suppression des données plus anciennes qu'une date
     */
    public function deleteToDate($intTs)
    {
        $objQuery = new ploopi_query_delete();
        $objQuery->add_from('`'.$this->getDataTableName().'`');
        $objQuery->add_where('`date_validation` <= %d', $intTs);
        $objQuery->execute();
    }


    /**
     * Prépare les données du formulaire et les retourne.
     * Récupère les paramètres de la page, prépare la session, configuration du filtre de recherche et du tri.
     * @param boolean $booUnlockPageLimit
     * @return array
     */

    public function prepareData($booUnlockPageLimit = false, $booFieldNamesAsKey = false, $booExport = false, $booRawData = false, $booDelete = false)
    {
        $ploopi_op = isset($_REQUEST['ploopi_op']) ? $_REQUEST['ploopi_op'] : (isset($_REQUEST['op']) ? $_REQUEST['op'] : '');

        // GET GPC
        if (isset($_GET['reset']))
        {
            $_SESSION['forms'][$this->fields['id']] = array();
            ploopi_setsessionvar('filter', null);
            ploopi_setsessionvar('formfilter', null);
        }
        if (isset($_GET['page'])) $_SESSION['forms'][$this->fields['id']]['page'] = $_GET['page'];
        if (isset($_GET['orderby'])) $_SESSION['forms'][$this->fields['id']]['orderby'] = $_GET['orderby'];
        if (isset($_GET['option'])) $_SESSION['forms'][$this->fields['id']]['option'] = $_GET['option'];
        if (isset($_REQUEST['unlockbackup'])) $_SESSION['forms'][$this->fields['id']]['unlockbackup'] = $_REQUEST['unlockbackup'];

        // VERIF SESSION
        if (!isset($_SESSION['forms'][$this->fields['id']]['page']) || $ploopi_op == 'forms_filter') $_SESSION['forms'][$this->fields['id']]['page'] = 1;
        if (!isset($_SESSION['forms'][$this->fields['id']]['orderby'])) $_SESSION['forms'][$this->fields['id']]['orderby'] = 'date_validation';
        if (!isset($_SESSION['forms'][$this->fields['id']]['option'])) $_SESSION['forms'][$this->fields['id']]['option'] = ($_SESSION['forms'][$this->fields['id']]['orderby'] == 'datevalidation') ? 'DESC' : '';
        if (!isset($_SESSION['forms'][$this->fields['id']]['unlockbackup'])) $_SESSION['forms'][$this->fields['id']]['unlockbackup'] = 0;

        /**
         * Récupération du filtre de recherche
         */

        $arrFilter = array();
        $arrFormFilter = array();

        if (ploopi_isactionallowed(_FORMS_ACTION_FILTER))
        {
            if ($ploopi_op != 'forms_filter') // Lecture session
            {
                $arrFilter = ploopi_getsessionvar('filter');
                if (!is_array($arrFilter)) $arrFilter = array();

                // Tableau spécifique pour le formulaire
                $arrFormFilter = ploopi_getsessionvar('formfilter');
                if (!is_array($arrFormFilter)) $arrFormFilter = array();
            }

            $intI = 1;
            while (isset($_POST["filter_value_{$intI}"]) && $_POST["filter_value_{$intI}"] != '')
            {
                $arrFormFilter[$intI] = array('field' => $_POST["filter_field_{$intI}"], 'op' => $_POST["filter_op_{$intI}"], 'value' => $_POST["filter_value_{$intI}"]);
                $arrFilter[$_POST["filter_field_{$intI}"]][] = array('op' => $_POST["filter_op_{$intI}"], 'value' => $_POST["filter_value_{$intI}"]);
                $intI++;
            }

            ploopi_setsessionvar('filter', $arrFilter);
            ploopi_setsessionvar('formfilter', $arrFormFilter);
        }

        /**
         * Récupération du critère de tri
         */
        $arrOrderBy = array();
        $arrOrderBy[$_SESSION['forms'][$this->fields['id']]['orderby']] = $_SESSION['forms'][$this->fields['id']]['option'];

        /**
         * Sélection page
         */

        if ($this->fields['nbline'] > 0 && !$booUnlockPageLimit) list($arrData, $intNumRows) = $this->_getData(true, true, $arrFilter, $arrOrderBy, $_SESSION['forms'][$this->fields['id']]['page'], $booFieldNamesAsKey, $booExport, $booRawData, $booDelete);
        else list($arrData, $intNumRows) = $this->_getData(true, true, $arrFilter, $arrOrderBy, 0, $booFieldNamesAsKey, $booExport, $booRawData, $booDelete);

        return array($arrData, $intNumRows, $arrFormFilter);
    }

    /**
     * Affiche les données du tableau dans un format bureautique (csv, xls, xml, html)
     * @param string $strFormat format souhaité (csv, xls, xml, html)
     */

    public function export($strFormat, $booExport = true)
    {
        ploopi_loadv2();

        require_once './include/classes/odf.php';

        // Lecture des données
        list($arrData) = $this->prepareData(true, true, $booExport, false);

        // Suppression de la colonne 'id' pour l'impression (autorisé en export)
        if (!$booExport && !empty($arrData))
        {
            foreach(array_keys($arrData) as $strKey)
            {
                unset($arrData[$strKey]['id']);
                unset($arrData[$strKey]['user_id']);
                unset($arrData[$strKey]['user_firstname']);
                unset($arrData[$strKey]['user_lastname']);
                unset($arrData[$strKey]['workspace_id']);
                unset($arrData[$strKey]['workspace_code']);
            }
        }


        $strFormat = strtolower($strFormat);

        ploopi_ob_clean();

        switch($strFormat)
        {
            case 'csv':
                // Lecture des paramètres CSV
                $strFormat = ploopi_getparam('forms_export_csvextension', $this->fields['id_module']);
                $strFieldSep = str_replace('(tab)',"\t", ploopi_getparam('forms_export_fieldseparator', $this->fields['id_module']));
                $strLineSep = str_replace(array('(cr)', '(lf)'), array("\r", "\n"), ploopi_getparam('forms_export_lineseparator', $this->fields['id_module']));
                $strTextSep = ploopi_getparam('forms_export_textseparator', $this->fields['id_module']);

                if (empty($strFormat)) $strFormat = 'csv';
                if (empty($strFieldSep)) $strFieldSep = ',';
                if (empty($strLineSep)) $strLineSep = "\n";
                if (empty($strTextSep)) $strTextSep = '"';

                echo ploopiArray::getInstance($arrData)->toCsv(true, $strFieldSep, $strLineSep, $strTextSep);
            break;

            case 'xls':
            case 'sxc':
            case 'ods':
            case 'pdf':
                // Options d'export
                $arrOptions = array(
                    'landscape' => $this->fields['export_landscape'] == 1,
                    'fitpage_width' => $this->fields['export_fitpage_width'] == 1,
                    'fitpage_height' => $this->fields['export_fitpage_height'] == 1,
                    'setborder' => $this->fields['export_border'] == 1
                );

                if ($booExport)
                {
                    // Préparation du document XLS, colonnes statiques
                    $arrTitles = array(
                        'id' => array('type' => 'integer', 'width' => 10),
                        'date_validation' => array('type' => 'datetime', 'width' => 20),
                        'ip' => array('type' => 'string', 'width' => 15),
                        'user_id' => array('type' => 'integer', 'width' => 10),
                        'user_login' => array('type' => 'string', 'width' => 15),
                        'user_firstname' => array('type' => 'string', 'width' => 15),
                        'user_lastname' => array('type' => 'string', 'width' => 15),
                        'workspace_id' => array('type' => 'integer', 'width' => 10),
                        'workspace_label' => array('type' => 'string', 'width' => 15),
                        'workspace_code' => array('type' => 'string', 'width' => 15),
                    );
                }
                else
                {
                    // Préparation du document XLS, colonnes statiques
                    $arrTitles = array(
                        'date_validation' => array('type' => 'datetime', 'width' => 20, 'title' => 'Date de Validation'),
                        'ip' => array('type' => 'string', 'width' => 15, 'title' => 'Adresse IP'),
                        'user_login' => array('type' => 'string', 'width' => 15, 'title' => 'Utilisateur'),
                        'workspace_label' => array('type' => 'string', 'width' => 15, 'title' => 'Espace'),
                    );
                }

                // Attribution des types de colonnes (colonnes dynamiques)
                foreach($this->getFields() as $strKey => $objField)
                {
                    switch($objField->fields['format'])
                    {
                        case 'float':
                            $strType = 'float';
                        break;

                        case 'integer':
                            $strType = 'integer';
                        break;

                        case 'date':
                            $strType = 'date';
                        break;

                        case 'time':
                            $strType = 'time';
                        break;

                        default:
                            $strType = 'string';
                        break;
                    }

                    $arrTitles[$objField->fields['fieldname']] = array('type' => $strType, 'width' => empty($objField->fields['export_width']) ? null : $objField->fields['export_width'] );
                }

                // Attribution des libellés de colonnes (impression uniquement) (colonnes dynamiques)
                if (!$booExport) foreach($this->getFields() as $strKey => $objField) $arrTitles[$objField->fields['fieldname']]['title'] = $objField->fields['name'];

                // Traitement des dates XLS
                $arrFields = $this->getFields();
                foreach(array_keys($arrData) as $strKey)
                {
                    if (isset($arrData[$strKey]['date_validation'])) $arrData[$strKey]['date_validation'] = self::_dateFrToXls($arrData[$strKey]['date_validation']);
                    foreach($arrFields as $objField)
                    {
                        if ($objField->fields['format'] == 'date') if (isset($arrData[$strKey][$objField->fields['fieldname']])) $arrData[$strKey][$objField->fields['fieldname']] = self::_dateFrToXls($arrData[$strKey][$objField->fields['fieldname']]);
                    }
                }

                if ($strFormat == 'xls') echo ploopiArray::getInstance($arrData)->toXls(true, 'export', $arrTitles, $arrOptions);
                elseif (ploopi_getparam('forms_webservice_jodconverter') != '')
                {
                    // Init de l'interface avec le convertisseur
                    $objOdfConverter = new odf_converter(ploopi_getparam('forms_webservice_jodconverter'));

                    // Détermination du type mime du format demandé
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

                    // Génération XLS + Conversion
                    echo $objOdfConverter->convert(ploopiArray::getInstance($arrData)->toXls(true, 'export', $arrTitles, $arrOptions), 'application/vnd.ms-excel', $strOuputMime);
                }
            break;

            case 'xml':
                echo ploopiArray::getInstance($arrData)->toXml();
            break;

            case 'html':
                echo ploopiArray::getInstance($arrData)->toHtml();
            break;

            default:
                ploopi_die();
            break;
        }

        $strFileName = "export.{$strFormat}";

        header('Content-Type: ' . ploopi_getmimetype($strFileName));
        header('Content-Disposition: attachment; Filename="'.$strFileName.'"');
        header('Cache-Control: private');
        header('Pragma: private');
        header('Content-Length: '.ob_get_length());
        header('Content-Encoding: None');
        ploopi_die();

    }


    /**
     * Suppression de la table liée au formulaire
     */

    private function _dropTable()
    {
        global $db;

        $db->query("DROP TABLE IF EXISTS `".$this->getDataTableName()."`");
    }

    /**
     * Génération de la table liée au formulaire (nouveau formulaire)
     */

    public function generateTable()
    {
        global $db;

        $this->_setDataTableName();

        /**
         * Suppression de la table si elle existe déjà
         */

        $this->_dropTable();


        /**
         * Création de la table
         */

        // Traitement des champs par défaut
        $arrDefaultFields = array(
            '#id' => 'INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', //INT(10) DEFAULT 0',
            'date_validation' => 'BIGINT(14) DEFAULT 0',
            'ip' => "VARCHAR(16) DEFAULT ''",
            'user_id' => 'INT(10) UNSIGNED DEFAULT 0',
            'user_login' => "VARCHAR(255) DEFAULT ''",
            'user_firstname' => "VARCHAR(255) DEFAULT ''",
            'user_lastname' => "VARCHAR(255) DEFAULT ''",
            'workspace_id' => 'INT(10) UNSIGNED DEFAULT 0',
            'workspace_label' => "VARCHAR(255) DEFAULT ''",
            'workspace_code' => "VARCHAR(255) DEFAULT ''"
        );

        $arrSqlFields = array();
        $arrSqlIndexes = array();

        foreach($arrDefaultFields as $strFieldName => $strType)
        {
            /**
             * Ajout du champ
             */
            $arrSqlFields[] = "`{$strFieldName}` {$strType} ";

            /**
             * Ajout de l'index
             */
            if ($strFieldName != '#id') $arrSqlIndexes[] = "ALTER TABLE `{$this->_strTablename}` ADD INDEX ( `{$strFieldName}` )";
        }


        // Traitement des champs dynamiques
        foreach($this->getFields() as $intId => $objField)
        {
            /**
             * Création du nom physique pour le champ
             */
            $objField->save(false);

            /**
             * Ajout du champ
             */
            $arrSqlFields[] = "`{$objField->fields['fieldname']}` ".$objField->getSqlType();

            /**
             * Ajout de l'index
             */
            $arrSqlIndexes[] = "ALTER TABLE `{$this->_strTablename}` ADD INDEX ( `{$objField->fields['fieldname']}` )";
        }

        $db->query("CREATE TABLE `{$this->_strTablename}` (".implode(', ', $arrSqlFields).") TYPE=MyISAM;");

        /**
         * Création des indexes
         */
        foreach($arrSqlIndexes as $strSql) $db->query($strSql);
    }

    /**
     * Crée un export physique des données du formulaire (uniquement utile pour la transition entre 2.5 et 3.0)
     */

    public function exportToTable()
    {
        global $db;

        $this->generateTable();

        /**
         * Insertion des données en injection directe
         */

        $db->query("INSERT INTO `{$this->_strTablename}` ".$this->_getQuery(false, false, array(), array(), 0, true)->get_sql());
    }


    public function removeMetabase()
    {
        $objModule = new module();
        if (!$objModule->open($this->fields['id_module'])) return;

        $this->_setDataTableName();

        $objQuery = new ploopi_query_delete();
        $objQuery->add_from('ploopi_mb_schema');
        $objQuery->add_where('tablesrc = %s', $this->_strTablename);
        $objQuery->add_where('id_module_type = %d', $objModule->fields['id_module_type']);
        $objQuery->execute();

        $objQuery = new ploopi_query_delete();
        $objQuery->add_from('ploopi_mb_relation');
        $objQuery->add_where('tablesrc = %s', $this->_strTablename);
        $objQuery->add_where('id_module_type = %d', $objModule->fields['id_module_type']);
        $objQuery->execute();

        $objQuery = new ploopi_query_delete();
        $objQuery->add_from('ploopi_mb_field');
        $objQuery->add_where('tablename = %s', $this->_strTablename);
        $objQuery->add_where('id_module_type = %d', $objModule->fields['id_module_type']);
        $objQuery->execute();

        $objQuery = new ploopi_query_delete();
        $objQuery->add_from('ploopi_mb_table');
        $objQuery->add_where('name = %s', $this->_strTablename);
        $objQuery->add_where('id_module_type = %d', $objModule->fields['id_module_type']);
        $objQuery->execute();

    }

    /**
     * Met à jour les données de la métabase pour le formulaire
     */

    public function updateMetabase()
    {
        $objModule = new module();
        if (!$objModule->open($this->fields['id_module'])) return;

        $this->_setDataTableName();

        $this->removeMetabase();

        $intIdModType = $_SESSION['ploopi']['modules'][$this->fields['id_module']]['id_module_type'];

        $objMbTable = new mb_table();
        $objMbTable->fields['name'] = $this->_strTablename;
        $objMbTable->fields['label'] = $this->fields['label'];
        $objMbTable->fields['visible'] = 1;
        $objMbTable->fields['id_module_type'] = $objModule->fields['id_module_type'];
        $objMbTable->save();

        // Relations avec user/workspace
        $objMbSchema = new mb_schema();
        $objMbSchema->fields['tablesrc'] = $this->_strTablename;
        $objMbSchema->fields['tabledest'] = 'ploopi_user';
        $objMbSchema->fields['id_module_type'] = $objModule->fields['id_module_type'];
        $objMbSchema->save();

        $objMbRelation = new mb_relation();
        $objMbRelation->fields['tablesrc'] = $this->_strTablename;
        $objMbRelation->fields['fieldsrc'] = 'user_id';
        $objMbRelation->fields['tabledest'] = 'ploopi_user';
        $objMbRelation->fields['fielddest'] = 'id';
        $objMbRelation->fields['id_module_type'] = $objModule->fields['id_module_type'];
        $objMbRelation->save();

        $objMbSchema = new mb_schema();
        $objMbSchema->fields['tablesrc'] = $this->_strTablename;
        $objMbSchema->fields['tabledest'] = 'ploopi_workspace';
        $objMbSchema->fields['id_module_type'] = $objModule->fields['id_module_type'];
        $objMbSchema->save();

        $objMbRelation = new mb_relation();
        $objMbRelation->fields['tablesrc'] = $this->_strTablename;
        $objMbRelation->fields['fieldsrc'] = 'workspace_id';
        $objMbRelation->fields['tabledest'] = 'ploopi_workspace';
        $objMbRelation->fields['fielddest'] = 'id';
        $objMbRelation->fields['id_module_type'] = $objModule->fields['id_module_type'];
        $objMbRelation->save();

        $arrFields = $this->getFields();

        foreach($arrFields as $objField)
        {
            if (!in_array($objField->fields['name'], array('user_id', 'workspace_id')))
            {
                //$objField->save(false);

                $objMbField = new mb_field();
                $objMbField->fields['tablename'] = $this->_strTablename;
                $objMbField->fields['name'] = $objField->fields['fieldname'];
                $objMbField->fields['label'] = $objField->fields['name'];

                switch($objField->fields['type'])
                {
                    case 'text':
                        switch($objField->fields['format'])
                        {
                            case 'integer':
                                $objMbField->fields['type'] = 'int(10)';
                            break;

                            case 'float':
                                $objMbField->fields['type'] = 'double';
                            break;

                            case 'date':
                                $objMbField->fields['type'] = 'int(8)';
                            break;

                            case 'time':
                                $objMbField->fields['type'] = 'varchar(10)';
                            break;

                            default:
                                $objMbField->fields['type'] = 'varchar(255)';
                            break;
                        }
                    break;

                    case 'textarea':
                        $objMbField->fields['type'] = 'text';
                    break;

                    case 'autoincrement':
                        $objMbField->fields['type'] = 'int(10)';
                    break;

                    case 'color':
                        $objMbField->fields['type'] = 'varchar(16)';
                    break;

                    case 'tablelink':
                        // Ouverture du champ lié
                        $objLinkedField = new formsField();
                        if ($objLinkedField->open($objField->fields['values']))
                        {
                            // Ouverture du formulaire lié
                            $objLinkedForm = new formsForm();
                            if ($objLinkedForm->open($objLinkedField->fields['id_form']))
                            {
                                // Sauvegarde de la relation
                                $objMbSchema = new mb_schema();
                                if (!$objMbSchema->open($this->_strTablename, $objLinkedForm->getDataTableName(), $intIdModType))
                                {
                                    $objMbSchema->fields['tablesrc'] = $this->_strTablename;
                                    $objMbSchema->fields['tabledest'] = $objLinkedForm->getDataTableName();
                                    $objMbSchema->fields['id_module_type'] = $intIdModType;
                                    $objMbSchema->save();
                                }

                                $objMbRelation = new mb_relation();
                                $objMbRelation->fields['tablesrc'] = $this->_strTablename;
                                $objMbRelation->fields['fieldsrc'] = $objField->fields['fieldname'];
                                $objMbRelation->fields['tabledest'] = $objLinkedForm->getDataTableName();
                                $objMbRelation->fields['fielddest'] = $objLinkedField->fields['fieldname'];
                                $objMbRelation->fields['id_module_type'] = $intIdModType;
                                $objMbRelation->save();
                            }
                        }
                    break;

                    default:
                        $objMbField->fields['type'] = 'varchar(255)';
                    break;
                }

                $objMbField->fields['visible'] = '1';
                $objMbField->fields['id_module_type'] = $intIdModType;
                $objMbField->save();
            }
        }
    }

    /**
     * Inclusion de la feuille de style du formulaire
     */
    public function includeCss($booPrint = false)
    {
        global $template_body;

        $strCssTemplate = (!empty($this->fields['model']) && file_exists("./modules/forms/templates/{$this->fields['model']}/style.css")) ? $this->fields['model'] : 'default';

        $template_body->assign_block_vars('module_css', array(
            'PATH' => "./modules/forms/templates/{$strCssTemplate}/style.css",
        ));

        if ($booPrint)
        {
            $template_body->assign_block_vars('module_css', array(
                'PATH' => "./modules/forms/templates/{$strCssTemplate}/print.css",
            ));
        }
    }

    /**
     * Rendu du formulaire (front/back/preview)
     * @param $intIdRecord
     * @param $strRenderMode : 'modify', 'view', 'preview'
     * @param $booIncludeCss (true/false)
     * @param $booDisplay (true/false)
     */
    public function render($intIdRecord = null, $strRenderMode = 'modify', $booIncludeCss = true, $booDisplay = true)
    {
        // Identifiant du formulaire
        $strFormId = 'forms_form_'.$this->fields['id'];

        // Panel par défaut (numéro page)
        $intDefaultPanel = 1;

        // L'utilisateur est admin ?
        $booIsAdmin = ploopi_isactionallowed(_FORMS_ACTION_ADMIN, -1, $this->fields['id_module']);

        // Contenu du formulaire
        $arrFieldsContent = array();

        // Ouverture de l'enregistrement lié
        if (!empty($intIdRecord) && is_numeric($intIdRecord))
        {
            $objRecord = new formsRecord($this);
            if ($objRecord->open($intIdRecord))
            {
                // Enregistrement existant, on charge les données
                foreach($this->getFields() as $objField)
                {
                    $objRecord->fields[$objField->fields['fieldname']];

                    $arrFieldsContent[$objField->fields['id']] = explode('||', $objRecord->fields[$objField->fields['fieldname']]);

                    if ($objField->fields['format'] == 'date') $arrFieldsContent[$objField->fields['id']][0] = empty($arrFieldsContent[$objField->fields['id']][0]) ? '' : current(ploopi_timestamp2local("{$arrFieldsContent[$objField->fields['id']][0]}000000"));

                    if ($objField->fields['type'] == 'calculation') $arrFieldsContent[$objField->fields['id']][0] .= " (calculé lors de l'enregistrement)";
                }
            }
            else unset($objRecord);
        }

        // Chargement des valeurs par défaut (nouvel enregistrement ou erreur)
        if (empty($objRecord))
        {
            foreach($this->getFields() as $objField)
            {
                switch($objField->fields['type'])
                {
                    case 'autoincrement':
                        $arrFieldsContent[$objField->fields['id']][0] = ($objField->getAggregate('max')+1).' (à valider)';
                    break;

                    case 'calculation':
                        $arrFieldsContent[$objField->fields['id']][0] = "(calculé lors de l'enregistrement)";
                    break;

                    default:
                        $arrFieldsContent[$objField->fields['id']] = explode('||', $objField->fields['defaultvalue']);

                        switch($arrFieldsContent[$objField->fields['id']][0])
                        {
                            case '=date()':
                                $localdate = ploopi_timestamp2local(ploopi_createtimestamp());
                                $arrFieldsContent[$objField->fields['id']][0] = $localdate['date'];
                            break;

                            case '=time()':
                                $localdate = ploopi_timestamp2local(ploopi_createtimestamp());
                                $arrFieldsContent[$objField->fields['id']][0] = $localdate['time'];
                            break;
                        }

                    break;
                }
            }
        }


        // Chargement du cookie uniquement sur nouvel enregistrement ou lors de l'impression
        if (empty($objRecord) || $strRenderMode == 'print')
        {
            // Lecture du cookie s'il existe
            $strVarName = self::getVarName($this->fields['id']).'_'.($strRenderMode == 'print' ? 'print' : 'save');

            if (!empty($_COOKIE[$strVarName]))
            {

                $arrValues = array();

                ploopi_unset_error_handler();
                if (isset($_COOKIE[$strVarName])) $arrValues = ploopi_array_map('utf8_decode', json_decode(gzuncompress(ploopi_base64_decode($_COOKIE[$strVarName])), true));
                ploopi_set_error_handler();

                foreach($arrValues as $intFieldId => $strValue) if (is_numeric($intFieldId)) $arrFieldsContent[$intFieldId] = explode('||', $strValue);

                if (isset($arrValues['panel'])) $intDefaultPanel = $arrValues['panel'];
            }
        }

        // Options du formulaire
        $arrFormOptions = array(
            'class' => 'forms_form',
            'onsubmit' => "return ploopi.{$strFormId}_validate(this);"
        );

        switch($strRenderMode)
        {
            case 'modify':
                $strUrl = ploopi_urlencode("admin.php?ploopi_op=forms_reply_save&forms_id={$this->fields['id']}");
                if (!empty($objRecord)) $strUrl .= "&forms_record_id={$intIdRecord}";
            break;

            case 'view':
            case 'print':
                $strUrl = '';
                $arrFormOptions['onsubmit'] = 'return false;';
            break;

            case 'preview':
                $strUrl = '';
                $arrFormOptions['onsubmit'] = 'return false;';
            break;

            case 'frontoffice':
                if ($this->fields['typeform'] != 'cms') return false;

                global $articleid;
                global $headingid;

                $arrUrlParams = array();

                if (!empty($headingid)) $arrUrlParams[] = "headingid={$headingid}";
                if (!empty($articleid)) $arrUrlParams[] = "articleid={$articleid}";
                if (!empty($_REQUEST['webedit_mode'])) $arrUrlParams[] = "webedit_mode={$_REQUEST['webedit_mode']}";
                $arrUrlParams[] = "ploopi_op=forms_reply_save";
                $arrUrlParams[] = "forms_id={$this->fields['id']}";

                $strUrl = ploopi_urlencode('index.php?'.implode('&',$arrUrlParams));
            break;

        }

        $arrFormOptions['style'] = $this->fields['style'];

        // Accès en modification ?
        $booModify = $strRenderMode == 'modify' || $strRenderMode == 'frontoffice';

        // Instanciation de l'objet formulaire (affichage)
        $objForm = new form(
            $strFormId,
            $strUrl,
            'post',
            $arrFormOptions
        );

        $strDesc = $this->fields['description'] == '' ? '' : '<p>'.ploopi_nl2br(htmlentities($this->fields['description'])).'</p>';
        $objForm->addField( new form_html('<h1>'.ploopi_nl2br(htmlentities($this->fields['label'])).$strDesc.'</h1>') );

        /**
         * Pré-traitement nécessaire pour les liaisons multiples de champs liés à des formulaires (en français : listes imbriquées)
         * Il faut détecter si plusieurs champs pointent vers des champs différents de la même table
         */
        $arrLinkedFields = array();

        foreach($this->getFields(true) as $objField)
        {
            if ($objField->fields['type'] == 'tablelink')
            {
                // "values" contient l'id du champ lié
                $objLinkedField = new formsField();
                if ($objLinkedField->open($objField->fields['values']))
                {
                    $arrLinkedFields['fields'][$objField->fields['values']] = $objLinkedField;
                    $arrLinkedFields['forms'][$objLinkedField->fields['id_form']][(int)$objField->fields['id']] = (int)$objField->fields['values'];
                }
            }
        }

        /**
         * Utile pour remplir les champs du formulaire en modification
         */
        $arrParams = array();
        $objPanel = null;

        // Panels qui vont servir de support pour le multi-page
        $arrPanels = array();

        // Groupes conditionnels
        $arrGroups = array();

        // Focus sur le premier champ (on va y stocker le nom du champ)
        $strFocus = '';

        // Pour chaque champs du formulaire
        foreach($this->getFields(true) as $objField)
        {
            // Regroupement des champs par groupes conditionnels
            if (!empty($objField->fields['id_group'])) $arrGroups[$objField->fields['id_group']][] = $objField->fields['id'];

            if ($objField->fields['option_pagebreak'] || is_null($objPanel))
            {
                $intS = sizeof($arrPanels);

                $arrPanels[] = 'panel_'.($intS+1);
                $arrOptions = array('style' => '');
                if ($objField->fields['option_pagebreak']) $arrOptions['style'] .= 'page-break-before:always;';
                if ($intS) $arrOptions['style'] .= 'display:none;';

                $objForm->addPanel($objPanel = new form_panel($arrPanels[$intS], 'Page '.($intS+1), $arrOptions));
            }

            if ($objField->fields['separator'])
            {
                $strStyle = empty($objField->fields['interline']) ? '' : "margin-top:{$objField->fields['interline']}px;";
                $strDesc = $objField->fields['description'] == '' ? '' : '<p>'.ploopi_nl2br(htmlentities($objField->fields['description'])).'</p>';

                $objPanel->addField( new form_html('<h'.$objField->fields['separator_level'].' id="field_'.$objField->fields['id'].'_form" style="'.$strStyle.$objField->fields['style_form'].'">'.ploopi_nl2br(htmlentities($objField->fields['name'])).$strDesc.'</h'.$objField->fields['separator_level'].'>') );
            }
            elseif ($objField->fields['html'])
            {
                $strStyle = empty($objField->fields['interline']) ? '' : "margin-top:{$objField->fields['interline']}px;";

                $objPanel->addField( new form_html('<div class="xhtml" id="field_'.$objField->fields['id'].'_form" style="'.$strStyle.'">'.$objField->fields['xhtmlcontent_cleaned'].'</div>') );
            }
            elseif ($objField->fields['option_formview'] && (!$objField->fields['option_adminonly'] || $booIsAdmin))
            {
                if($objField->fields['captcha'])
                {
                    $objPanel->addField( new form_html('<div>Les CAPTCHAs ne sont pas gérés</div>') );
                }
                else
                {
                    $strFieldType = '';
                    $strDataType = 'string';
                    $intMaxLength = 255;

                    // Requis ? car général :
                    $booRequired = $objField->fields['option_needed'] == 1;
                    // Exceptions :
                    if ($objField->fields['type'] == 'file' && current($arrFieldsContent[$objField->fields['id']]) != '') $booRequired = false;

                    $arrOptions = array(
                        'required' => $booRequired,
                        'description' => ploopi_nl2br(htmlentities($objField->fields['description'])),
                        'style' => $objField->fields['style_field'],
                        'style_form' => (empty($objField->fields['interline']) ? '' : "margin-top:{$objField->fields['interline']}px;") . $objField->fields['style_form'],
                        'class_form' => 'field'
                    );

                    // Le champ fait partie d'un groupe conditionnel ? Si oui, on ne l'affiche pas
                    if ($objField->fields['id_group'] != 0) $arrOptions['style_form'] .= "display:none;";

                    // Traitement automatique (sauvegarde des données) sur l'événement onchange
                    $arrOptions['onchange'] = "ploopi.{$strFormId}_onchange();";

                    if (!$booModify) $arrOptions['disabled'] = true;

                    switch($objField->fields['type'])
                    {
                        case 'text':
                            if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                            switch($objField->fields['format'])
                            {
                                case 'integer':
                                    $strFieldType = 'input:text';
                                    $strDataType = 'int';
                                    $intMaxLength = 10;
                                break;

                                case 'float':
                                    $strFieldType = 'input:text';
                                    $strDataType = 'float';
                                    $intMaxLength = 16;
                                break;

                                case 'date':
                                    $strFieldType = 'input:text';
                                    $strDataType = 'date';
                                    $intMaxLength = 10;
                                break;

                                case 'time':
                                    $strFieldType = 'input:text';
                                    $strDataType = 'time';
                                    $intMaxLength = 5;
                                break;

                                case 'email':
                                    $strFieldType = 'input:text';
                                    $strDataType = 'email';
                                    $intMaxLength = 255;
                                break;

                                default:
                                    $strFieldType = 'input:text';
                                    $strDataType = 'string';
                                    $intMaxLength = 255;
                                break;
                            }
                        break;

                        case 'tablelink':
                            /**
                              * On ne doit afficher les données que si le lien est unique vers la table ou si c'est le premier champ lié
                              * (les valeurs des autres champs sont déterminées en fonction des choix déjà effectués sur les précédents champs)
                              */
                            if (isset($arrLinkedFields['fields'][$objField->fields['values']]))
                            {
                                if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                                $objLinkedField = $arrLinkedFields['fields'][$objField->fields['values']];

                                // Initialisation de la liste des paramètres vers le formulaire X
                                if (!isset($arrParams[$objLinkedField->fields['id_form']])) $arrParams[$objLinkedField->fields['id_form']] = array();

                                // Lecture des valeurs du champs lié en fonction des paramètres déjà saisis
                                $arrValues = array('' => '') + $objLinkedField->getValues($arrParams[$objLinkedField->fields['id_form']]);

                                // Mise à jour des paramètres déjà saisis pour le prochain champ imbriqué dans la boucle
                                $arrParams[$objLinkedField->fields['id_form']][$objLinkedField->fields['fieldname']] = current($arrFieldsContent[$objField->fields['id']]);

                                $arrOptions['onchange'] .= "forms_field_tablelink_onchange({$objField->fields['id']}, ".json_encode(array_keys($arrLinkedFields['forms'][$objLinkedField->fields['id_form']])).",'".ploopi_urlencode('admin-light.php?ploopi_op=forms_tablelink_values')."');";

                                $objPanel->addField( new form_select(
                                    $objField->fields['name'],
                                    $arrValues,
                                    current($arrFieldsContent[$objField->fields['id']]),
                                    'field_'.$objField->fields['id'],
                                    'field_'.$objField->fields['id'],
                                    $arrOptions
                                ));
                            }
                        break;

                        case 'color':
                            if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                            $arrValues = explode('||',$objField->fields['values']);
                            $arrSelectOptions = array();
                            $arrSelectOptions[] = new form_select_option('', '');
                            foreach($arrValues as $strValue) $arrSelectOptions[] = new form_select_option(' ', $strValue, null, array('style' => "background-color:{$strValue}"));
                            $arrOptions['onchange'] .= "this.style.backgroundColor = this[this.selectedIndex].style.backgroundColor;";

                            $objPanel->addField( new form_select(
                                $objField->fields['name'],
                                $arrSelectOptions,
                                current($arrFieldsContent[$objField->fields['id']]),
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'select':
                            if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                            $arrValues = explode('||',$objField->fields['values']);
                            $arrValues = empty($arrValues) ? array() : array_combine($arrValues, $arrValues);

                            $objPanel->addField( new form_select(
                                $objField->fields['name'],
                                array('' => '') + $arrValues,
                                current($arrFieldsContent[$objField->fields['id']]),
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'checkbox':
                            $arrValues = explode('||',$objField->fields['values']);

                            $objPanel->addField( new form_checkbox_list(
                                $objField->fields['name'],
                                array_combine($arrValues, $arrValues),
                                $arrFieldsContent[$objField->fields['id']],
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'radio':
                            $arrValues = explode('||',$objField->fields['values']);
                            $objPanel->addField( new form_radio_list(
                                $objField->fields['name'],
                                array_combine($arrValues, $arrValues),
                                current($arrFieldsContent[$objField->fields['id']]),
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'textarea':
                            if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                            $objPanel->addField( new form_field(
                                'textarea',
                                $objField->fields['name'],
                                current($arrFieldsContent[$objField->fields['id']]),
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'file':
                            if (empty($strFocus)) $strFocus = 'field_'.$objField->fields['id'];

                            $objPanel->addField( new form_field(
                                'input:file',
                                $objField->fields['name'],
                                current($arrFieldsContent[$objField->fields['id']]),
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions
                            ));
                        break;

                        case 'autoincrement':
                            $strFieldType = 'input:text';
                            $strDataType = 'string';
                            $arrOptions['disabled'] = true;
                            $arrOptions['required'] = false;
                        break;

                        case 'calculation':
                            $strFieldType = 'input:text';
                            $strDataType = 'string';
                            $arrOptions['disabled'] = true;
                            $arrOptions['required'] = false;
                        break;

                        case 'color':
                            $strFieldType = 'input:text';
                            $strDataType = 'color';
                        break;

                        default:
                            $strFieldType = 'input:text';
                            $strDataType = 'string';
                        break;
                    }

                    switch($strFieldType)
                    {
                        case 'input:text':
                            if (!empty($objField->fields['maxlength'])) $intMaxLength = $objField->fields['maxlength'];

                            $objPanel->addField( new form_field(
                                'input:text',
                                htmlentities($objField->fields['name']),
                                isset($arrFieldsContent[$objField->fields['id']]) ? current($arrFieldsContent[$objField->fields['id']]) : '',
                                'field_'.$objField->fields['id'],
                                'field_'.$objField->fields['id'],
                                $arrOptions + array(
                                    'datatype' => $strDataType,
                                    'maxlength' => $intMaxLength,
                                )
                            ));
                        break;
                    }
                }
            }
        }

        switch($strRenderMode)
        {
            case 'modify':
            case 'frontoffice':
                if ($strRenderMode != 'frontoffice')
                {
                    $objForm->addButton( new form_button('input:button', 'Retour', null, null, array('onclick' => "document.location.href='".ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$this->fields['id']}")."';")) );
                }

                //$objForm->addButton( new form_button('input:reset', 'Réinitialiser', null, null, array('style' => 'margin-right:10px;') ) );
                $objForm->addButton( new form_button('input:button', 'Réinitialiser', null, null, array('style' => 'margin-right:10px;', 'onclick' => "document.location.reload();")) );
                $objForm->addButton( new form_button('input:button', 'Imprimer', null, null, array('onclick' => "ploopi.{$strFormId}_quicksave('print'); ploopi_openwin('".ploopi_urlencode("index-light.php?ploopi_op=forms_print&forms_id={$this->fields['id']}&record_id={$intIdRecord}")."', 800, 600);")) );
            break;

            case 'view':
                $objForm->addButton( new form_button('input:button', 'Retour', null, null, array('onclick' => "document.location.href='".ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$this->fields['id']}")."';")) );
                $objForm->addButton( new form_button('input:button', 'Imprimer', null, null, array('onclick' => "ploopi.{$strFormId}_quicksave('print'); ploopi_openwin('".ploopi_urlencode("index-light.php?ploopi_op=forms_print&forms_id={$this->fields['id']}&record_id={$intIdRecord}")."', 800, 600);")) );
            break;

            case 'preview':
                $objForm->addButton( new form_button('input:button', 'Retour', null, null, array('onclick' => "ploopi_hidepopup('forms_preview');")) );
            break;
        }

        /**
         * Gestion des groupes conditionnels
         */

        $strJsCond = "ploopi.{$strFormId}_checkgroup = function(g) {\nswitch(g) {";
        $strJsGroup = '';

        $arrFields = $this->getFields();

        foreach($arrGroups as $intIdGroup => $rowGroup)
        {
            foreach($rowGroup as $intIdField)
            {
                $strJsGroup .= "\n$('field_{$intIdField}_form').style.display = ploopi.{$strFormId}_checkgroup({$intIdGroup}) ? 'block' : 'none';";
            }

            $strJsCond .= "\ncase {$intIdGroup}:";

            $objGroup = new formsGroup();
            $objGroup->open($intIdGroup);
            $arrConditions = $objGroup->getConditions();

            foreach($arrConditions as $key => $row)
            {
                if (empty($row['field']) && empty($row['op']))
                {
                    $strJsCond .= "\nvar C{$key} = true;";
                }
                else
                {
                    $objFieldVar = $arrFields[$row['field']];

                    // Stockage des valeurs du formulaire pour les variables concernées par la condition
                    $strJsCond .= "\nvar V{$key} = new Array();";
                    switch($objFieldVar->fields['type'])
                    {
                        case 'radio':
                            $strJsCond .= "\nfor (i=0; i<$('{$strFormId}')['field_{$row['field']}'].length;i++) if ($('{$strFormId}')['field_{$row['field']}'][i].checked) V{$key}.push( forms_removeaccents($('{$strFormId}')['field_{$row['field']}'][i].value).toUpperCase() );";
                        break;

                        case 'checkbox':
                            $strJsCond .= "\nfor (i=0; i<$('{$strFormId}')['field_{$row['field']}[]'].length;i++) if ($('{$strFormId}')['field_{$row['field']}[]'][i].checked) V{$key}.push( forms_removeaccents($('{$strFormId}')['field_{$row['field']}[]'][i].value).toUpperCase() );";
                        break;

                        default:
                            $strJsCond .= "\nV{$key}.push( forms_removeaccents($('{$strFormId}').field_{$row['field']}.value).toUpperCase() );";
                        break;
                    }

                    $strValue = addslashes(strtoupper(ploopi_convertaccents($row['value'])));

                    $strJsCond .= "\nvar C{$key} = false;";
                    $strJsCond .= "\nfor (i=0;i<V{$key}.length;i++) {";


                    switch($row['op'])
                    {
                        // Traité comme une chaîne (pas de sens en numérique)
                        case 'begin':
                            $strJsCond .= "C{$key} = C{$key} || (V{$key}[i].match(/".$strValue.".*/i));";
                        break;

                        // Traité comme une chaîne (pas de sens en numérique)
                        case 'like':
                            $strJsCond .= "C{$key} = C{$key} || (V{$key}[i].match(/.*".$strValue.".*/i));";
                        break;

                        case 'between':
                            $arrValues = array_map('trim', explode(',', $strValue));
                            if (sizeof($arrValues) == 2)
                            {
                                switch($objFieldVar->fields['format'])
                                {
                                    case 'integer':
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] >= parseInt('".$arrValues[0]."', 10) && V{$key}[i] <= parseInt('".$arrValues[1]."', 10));";
                                    break;

                                    case 'float':
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] >= parseFloat('".$arrValues[0]."', 10) && V{$key}[i] <= parseFloat('".$arrValues[1]."', 10));";
                                    break;

                                    default:
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] >= '".$arrValues[0]."' && V{$key}[i] <= '".$arrValues[1]."');";
                                    break;
                                }
                            }
                        break;

                        case 'in':
                            foreach(array_map('trim', explode(',', $strValue)) as $strValue)
                            {
                                switch($objFieldVar->fields['format'])
                                {
                                    case 'integer':
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] == parseInt('".$strValue."', 10));";
                                    break;

                                    case 'float':
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] == parseFloat('".$strValue."', 10));";
                                    break;

                                    default:
                                        $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] == '".$strValue."');";
                                    break;
                                }
                            }
                        break;

                        // Les opérateurs de base
                        default:
                            if ($row['op'] == '=') $row['op'] = '==';
                            if ($row['op'] == '<>') $row['op'] = '!=';

                            switch($objFieldVar->fields['format'])
                            {
                                case 'integer':
                                    $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] {$row['op']} parseInt('".$strValue."', 10));";
                                break;

                                case 'float':
                                    $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] {$row['op']} parseFloat('".$strValue."', 10));";
                                break;

                                default:
                                    $strJsCond .= "C{$key} = C{$key} || (V{$key}[i] {$row['op']} '".$strValue."');";
                                break;
                            }
                        break;
                    }

                    $strJsCond .= "}";
                }
            }

            // Test de l'expression booléenne
            try {
                $objParser = new formsBooleanParser($objGroup->fields['formula']);
                $strJsCond .= "\nreturn ".str_replace(array('&', '|'), array(' && ', ' || '), $objParser->getExpression()).';';
            }
            catch (Exception $e) { echo '<br /><strong>'.$e->getMessage().'</strong>'; }

            $strJsCond .= "\nbreak;";


        }
        $strJsCond .= "\n}\n}";

        $objForm->addJs("
            ploopi.currentpanel = 1;
            ploopi.nbpanel = ".sizeof($arrPanels).";

            ploopi.{$strFormId}_quicksave = function(mode) {
                query = $('{$strFormId}').serialize();
                query += (query == '' ? '' : '&')+'ploopi_xhr=1&ploopi_op=forms_quicksave&forms_form_id={$this->fields['id']}&forms_mode='+mode+'&forms_panel='+ploopi.currentpanel;
                ploopi_xmlhttprequest('admin-light.php', query, false, false, 'POST');
            };

            {$strJsCond}

            // Vérification des groupes
            ploopi.{$strFormId}_checkgroups = function() {
                {$strJsGroup}
            };

            // Changement de valeur dans un champ
            ploopi.{$strFormId}_onchange = function() {
                ploopi.{$strFormId}_checkgroups();
            };

            // Sélection du bon panel au chargement
            Event.observe(window, 'load', function() {
                $('{$strFocus}').focus();
                ploopi.{$strFormId}_checkgroups();
            });

        ");


        /**
         * Gestion du multipage
         */

        if (sizeof($arrPanels) > 1)
        {
            // Code JS pour cacher tous les panels
            $strJsHidePanels = '';

            // Code JS du switch des fonctions de validation des panels
            $strJsSwitch = '';

            $objForm->addButton( new form_button('input:button', 'Précédent', null, "{$strFormId}_btn_prev", array('style' => 'margin-left:2px;font-weight:bold;display:none;', 'onclick' => "ploopi.{$strFormId}_prevpanel();")) );


            // Pour chaque panel, on affiche un bouton et on prépare le code JS
            foreach($arrPanels as $intNum => $strPanelId)
            {
                $intId = $intNum+1;

                $arrOptions = array(
                    'onclick' => "ploopi.{$strFormId}_switchpanel('{$intId}');"
                );

                if ($intNum == 0) $arrOptions['class'] = 'selected';

                $strJsHidePanels .= "$('{$strPanelId}').style.display='none';";

                if ($this->fields['option_multidisplaypages'])
                {
                    $objForm->addButton( new form_button('input:button', 'Page '.($intNum+1), null, "{$strFormId}_btn_{$intId}", $arrOptions ) );
                    $strJsHidePanels .= " $('{$strFormId}_btn_{$intId}').className = '';";
                }

                $strJsSwitch .= "\ncase {$intId}: return ploopi.{$strFormId}_{$strPanelId}_validate(form); break;";
            }

            $objForm->addButton( new form_button('input:button', 'Suivant', null, "{$strFormId}_btn_next", array('style' => 'margin-left:2px;font-weight:bold;', 'onclick' => "ploopi.{$strFormId}_nextpanel();")) );

            $objForm->addJs("
                // Switch de panel avec vérification des données
                ploopi.{$strFormId}_switchpanel = function(panel, save) {
                    if (typeof(save) == 'undefined') save = true;

                    for (p = 1; p < panel ; p++)
                    {
                        if (!ploopi.{$strFormId}_validate_panel($('{$strFormId}'), p))
                        {
                            this.{$strFormId}_selectpanel(p);
                            return;
                        }
                    }

                    this.{$strFormId}_selectpanel(panel, save);
                };

                // Sélection d'un panel par le numéro de page + sauvegarde des données
                ploopi.{$strFormId}_selectpanel = function(panel, save) {
                    if (typeof(save) == 'undefined') save = true;

                    {$strJsHidePanels}

                    $('{$strFormId}_btn_prev').style.display = panel>1 ? 'block' : 'none';
                    $('{$strFormId}_btn_next').style.display = panel<this.nbpanel ? 'block' : 'none';

                    if ($('{$strFormId}_btn_'+panel)) $('{$strFormId}_btn_'+panel).className = 'selected';
                    if ($('{$strFormId}_btn_submit')) $('{$strFormId}_btn_submit').style.display = panel == this.nbpanel ? 'block' : 'none';

                    $('panel_'+panel).style.display='block';
                    this.currentpanel = panel;

                    if (save) ploopi.{$strFormId}_quicksave('save');
                };

                // Panel suivant
                ploopi.{$strFormId}_nextpanel = function() {
                    if (ploopi.{$strFormId}_validate_panel($('{$strFormId}'), this.currentpanel))
                    {
                        panel = parseInt(this.currentpanel, 10) + 1;
                        if (panel > this.nbpanel) panel = this.nbpanel;
                        this.{$strFormId}_selectpanel(panel);
                    }
                };

                // Panel précédent
                ploopi.{$strFormId}_prevpanel = function() {
                    panel = parseInt(this.currentpanel, 10) - 1;
                    if (panel < 1) panel = 1;
                    this.{$strFormId}_selectpanel(panel);
                };

                // Validation d'un panel uniquement
                ploopi.{$strFormId}_validate_panel = function(form, p) {
                    switch(p)
                    {
                        {$strJsSwitch}
                    }

                    return true;
                }

                // Validation du formulaire (écrase la fonction d'origine de la classe form)
                ploopi.{$strFormId}_validate = function(form) {
                    for (p = 1; p <= ploopi.nbpanel ; p++)
                    {
                        if (!ploopi.{$strFormId}_validate_panel(form, p))
                        {
                            ploopi.{$strFormId}_switchpanel(p);
                            return false;
                        }
                    }

                    return true;
                };


                // Sélection du bon panel au chargement
                Event.observe(window, 'load', function() {
                    ploopi.{$strFormId}_switchpanel({$intDefaultPanel}, false);
                });

            ");
        }


        switch($strRenderMode)
        {
            case 'modify':
            case 'frontoffice':
                if (sizeof($arrPanels) > 1 && !$this->fields['option_multidisplaysave'])
                {
                    $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, "{$strFormId}_btn_submit", array('style' => 'margin-left:2px;display:none;')) );
                }
                else
                {
                    $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );
                }
            break;
        }



        if ($booIncludeCss) $this->includeCss();

        if ($booDisplay) echo $objForm->render();
        else return $objForm->render();
    }


    /**
     * Retourne le nombre de pages du formulaire
     * @return int Nombre de pages
     */
    public function getNbPanels()
    {
        $intNb = 0;

        // Pour chaque champs du formulaire
        foreach($this->getFields(true) as $objField) if ($objField->fields['option_pagebreak'] || $intNb == 0) $intNb++;

        return $intNb;
    }


    /**
     * Recherche si le formulaire contient un captcha
     *
     * @return boolean True si form contient un captcha sinon false
     */

    public function captchainform()
    {
        global $db;

        $select = "SELECT id FROM ploopi_mod_forms_field WHERE id_form = {$this->fields['id']} AND captcha = 1";

        $sqlCaptchaInForm = $db->query($select);

        return ($db->numrows($sqlCaptchaInForm) > 0);
    }

    /**
     * Indique si un formulaire est publié à l'instant précis
     * @return boolean true si le formulaire est publié
     */
    public function isPublished()
    {
        $intTsToday = ploopi_createtimestamp();

        return ($this->fields['pubdate_start'] <= $intTsToday || empty($this->fields['pubdate_start'])) && ($this->fields['pubdate_end'] >= $intTsToday || empty($this->fields['pubdate_end']));
    }

    /**
     * Recalcule toutes les valeurs de champs calculés (appelé notamment lors de la modification de formule d'un champ calculé, ou lors de l'insertion d'un enregistrement contenant un champ calculé)
     */

    public function calculate()
    {
        set_time_limit(0);

        // Lecture des champs du formulaire
        $arrObjFields = $this->getFields();
        $arrObjFieldsCalc = $this->getFields();

        // Tri des champs par position
        $arrFieldsByPos = array();
        foreach($arrObjFields as $objField) $arrFieldsByPos[$objField->fields['position']] = $objField;

        // Variables issues d'agrégats (à calculer)
        $arrStaticVariables = array();

        // Chargement de l'ensemble des données brutes (donc non calculées) du formulaire
        $arrData = $this->getRawData();


        // Traitement du formulaire, colonne par colonne
        foreach($arrObjFields as $objField)
        {
            // On ne s'intéresse qu'aux colonnes calculées
            if ($objField->fields['type'] == 'calculation') {

                // Pour chaque ligne de données
                foreach($arrData as $intId => $row) {

                    $arrVariables = $row;

                    try {
                        // Interprétation du calcul
                        $objParser = new formsArithmeticParser($objField->fields['formula']);

                        // Extraction des variables de l'expression
                        $arrVars = $objParser->getVars();

                        // Pour chaque variable attendue dans l'expression
                        foreach($arrVars as $strVar)
                        {
                            if (isset($arrStaticVariables[$strVar])) $arrVariables[$strVar] = $arrStaticVariables[$strVar];
                            else
                            {
                                // Analyse de la variable
                                if (preg_match('/C([0-9]+)_?([A-Z]{0,3})/', $strVar, $arrMatches) > 0)
                                {
                                    // $arrMatches[1] => position du champ
                                    // $arrMatches[2] => agrégat (optionnel)
                                    if (!empty($arrMatches[2])) // Ca nous intéresse, il faut calculer l'agrégat
                                    {
                                        $arrVariables[$strVar] = $arrStaticVariables[$strVar] = $arrFieldsByPos[$arrMatches[1]]->getAggregate($arrMatches[2]);
                                    }
                                }
                            }
                        }

                        $objParser = new formsArithmeticParser($objField->fields['formula'], $arrVariables);

                        // Mise à jour du tableau de données
                        $arrData[$intId]['C'.$objField->fields['position']] = $objParser->getVal();

                        // Enregistrement de la valeur modifiée en base de données (obligatoire pour le calcul des agrégats)
                        $objRecord = new formsRecord($this);
                        if ($objRecord->open($intId))
                        {
                            $objRecord->fields[$objField->fields['fieldname']] = $arrData[$intId]['C'.$objField->fields['position']];
                            $objRecord->save();
                        }
                    }
                    catch(Exception $e) { }
                }
            }
        }
    }
}
?>
