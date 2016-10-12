<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestion des champs
 *
 * @package forms
 * @subpackage field
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusions
 */

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsRecord.php';
include_once './modules/forms/classes/formsArithmeticParser.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_field
 *
 * @package forms
 * @subpackage field
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class formsField extends ploopi\data_object
{

    private $_strOriginalFieldName;
    private $_strOriginalType;
    private $_strOriginalFormula;
    private $_intOriginalPosition;


    /**
     * Retourne le chemin physique d'un fichier stocké dans un champ
     * @param integer $intFormsId
     * @param integer $intRecordId
     * @param string $strFileName
     * @param integer $intModuleId
     */
    public static function getFilePath($intFormsId, $intRecordId, $strFileName = '', $intModuleId = null)
    {
        if (empty($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
        return _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$intModuleId._PLOOPI_SEP.$intFormsId._PLOOPI_SEP.$intRecordId._PLOOPI_SEP.$strFileName;
    }

    /**
     * Met à jour les formules de champs calculés lors d'un changement de position
     */

    private static function _updateFormulas($arrPositions)
    {
        $objCol = new ploopi\data_object_collection('formsField');
        $objCol->add_where("type = 'calculation'");
        $objCol->add_where("formula != ''");
        foreach($objCol->get_objects() as $objField)
        {
            $objField->fields['formula'] = preg_replace_callback('/C([0-9]+)/', function($arrMatches)use($arrPositions) {
                return isset($arrPositions[$arrMatches[1]]) ? 'C'.$arrPositions[$arrMatches[1]] : $arrMatches[0];
            }, $objField->fields['formula']);

            $objField->quicksave();
        }
    }

    /**
     * Constructeur de la classe
     *
     * @return field
     */

    public function __construct()
    {
        $this->_strOriginalFieldName = '';
        $this->_strOriginalType = '';
        $this->_strOriginalFormula = '';
        $this->_intOriginalPosition = 0;

        parent::__construct('ploopi_mod_forms_field');

        $this->fields['type'] = '';
    }

    /**
     * Gère le clone
     */

    public function __clone()
    {
        // Personnalisation du clone
        $this->new = true;
        $this->fields['id'] = null;
    }

    /**
     * Ouvre un champ
     * @see include/classes/data_object::open()
     */
    public function open(...$args)
    {
        $booOpen = parent::open($args);

        if ($booOpen) {
            $this->_intOriginalPosition = $this->fields['position'];

            if (!$this->fields['separator'] && !$this->fields['captcha'])
            {
                $this->_strOriginalFieldName = $this->fields['fieldname'];
                $this->_strOriginalType = $this->getSqlType();
                $this->_strOriginalFormula = $this->fields['formula'];
            }
        }

        return $booOpen;
    }

    /**
     * Ouvre un champ selon sa position
     */
    public function openByPos($intPos)
    {
        $objCol = new ploopi\data_object_collection('formsField');
        $objCol->add_where('position = %d', $intPos);
        $objField = current($objCol->get_objects());
        return $objField === false ? false : $this->open($objField->fields['id']);
    }

    /**
     * Enregistre les données brutes du champ
     *
     * @return int identifiant du champ enregistré
     */
    public function quicksave() { return parent::save(); }

    /**
     * Enregistre le champ
     *
     * @return int identifiant du champ enregistré
     */
    public function save($booUpdateTable = true)
    {
        $db = ploopi\db::get();

        if (empty($this->fields['separator']) && empty($this->fields['html']) && empty($this->fields['captcha'])) $this->_createPhysicalName();

        if ($this->fields['type'] == 'calculation') $this->fields['format'] = 'float';

        $booIsNew = $this->isnew();

        /**
         * Traitement de la position
         */

        // Contrôle de validité de la position
        if (is_numeric($this->fields['position']) && $this->fields['position'] < 1) $this->fields['position'] = 1;
        else
        {
            $db->query("Select max(position) as maxpos FROM `ploopi_mod_forms_field` WHERE id_form = {$this->fields['id_form']}");
            $row = $db->fetchrow();

            if (!is_numeric($this->fields['position']) || $this->fields['position'] > $row['maxpos']+(int)$booIsNew) $this->fields['position'] = $row['maxpos']+(int)$booIsNew;
        }

        // Positions modifiées (utile pour modification des formules des champs calculés)
        $arrPositions = array();

        if ($booIsNew)
        {
            $db->query("SELECT max(position) as maxpos FROM `ploopi_mod_forms_field` WHERE id_form = {$this->fields['id_form']}");
            $intMax = current($db->fetchrow());
            for ($intI = $this->fields['position']; $intI <= $intMax; $intI++) $arrPositions[$intI] = $intI+1;

            // Déplacer tous les champs en dessous de la position d'insertion vers le bas
            $db->query("UPDATE ploopi_mod_forms_field SET position=position+1 where position >= {$this->fields['position']} AND id_form = {$this->fields['id_form']}");
        }
        else
        {
            // Nouvelle position définie
            if ($this->fields['position'] != $this->_intOriginalPosition)
            {
                // Il faut impacter les formules des champs calculés
                $arrPositions[$this->_intOriginalPosition] = $this->fields['position'];

                if ($this->fields['position'] > $this->_intOriginalPosition)
                {
                    for ($intI = $this->_intOriginalPosition+1; $intI <= $this->fields['position']; $intI++) $arrPositions[$intI] = $intI-1;
                    // Déplacer tous les champs entre la position d'origine et la position de destination vers le haut
                    $db->query("UPDATE ploopi_mod_forms_field SET position=position-1 WHERE position BETWEEN ".($this->_intOriginalPosition+1)." AND {$this->fields['position']} AND id_form = {$this->fields['id_form']}");
                }
                else
                {
                    for ($intI = $this->fields['position']; $intI <= $this->_intOriginalPosition-1; $intI++) $arrPositions[$intI] = $intI+1;
                    // Déplacer tous les champs entre la position de destination et la position d'origine vers le bas
                    $db->query("UPDATE ploopi_mod_forms_field SET position=position+1 where position BETWEEN {$this->fields['position']} AND ".($this->_intOriginalPosition-1)." AND id_form = {$this->fields['id_form']}");
                }
            }
        }

        /**
         * Enregistrement
         */
        $res = parent::save();

        /**
         * Mise à jour de la bdd
         */
        if ($booUpdateTable && !empty($this->fields['fieldname']))
        {

            // Lecture du type actuel du champ
            $strType = $this->getSqlType();

            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form']))
            {
                if ($booIsNew)
                {
                    /**
                     * Ajout du champ physique
                     */
                    $db->query("ALTER TABLE `".$objForm->getDataTableName()."` ADD `{$this->fields['fieldname']}` {$strType}");
                }
                else
                {
                    /**
                     * Mise à jour du champ physique
                     */

                    // Modification de la structure si changement de nom ou de type
                    if ($this->_strOriginalFieldName != $this->fields['fieldname'] || $this->_strOriginalType != $strType)
                    {
                        // Attention le changement de structure peut ne pas être compatible avec l'index existant.
                        // Il faut donc supprimer l'index à chaque modification de structure, puis le recréer
                        $db->query("SHOW INDEXES FROM `".$objForm->getDataTableName()."` WHERE Key_name = '{$this->_strOriginalFieldName}'");
                        if ($db->numrows()) $db->query("ALTER TABLE `".$objForm->getDataTableName()."` DROP INDEX `{$this->_strOriginalFieldName}`");

                        $db->query("ALTER TABLE `".$objForm->getDataTableName()."` CHANGE `{$this->_strOriginalFieldName}` `{$this->fields['fieldname']}` {$strType} ");
                    }
                }

                $db->query("SHOW INDEXES FROM `".$objForm->getDataTableName()."`");
                // 64 indexes max sur une table MyISAM
                if ($db->numrows() < 64)
                {
                    // Type standard
                    if (strpos($strType, 'TEXT') === false)
                    {
                        $db->query("ALTER TABLE `".$objForm->getDataTableName()."` ADD INDEX (`{$this->fields['fieldname']}`)");
                    }
                    // Type texte (longtext...)
                    else
                    {
                        $db->query("ALTER TABLE `".$objForm->getDataTableName()."` ADD INDEX (`{$this->fields['fieldname']}` (32))");
                    }
                }

                $objForm->updateMetabase();
            }
        }

        /**
         * Mise à jour des formules de champs calculés
         */
        if (!empty($arrPositions)) self::_updateFormulas($arrPositions);

        /**
         * Mise à jour des données de champs calculés
         */

        if ($this->fields['type'] == 'calculation' && $this->fields['formula'] != $this->_strOriginalFormula)
        {
            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form'])) $objForm->calculate();
        }

        return $res;
    }

    /**
     * Supprime le champ
     *
     * @return boolean
     */
    public function delete()
    {
        $db = ploopi\db::get();

        $arrPositions = array();

        if (!$this->fields['separator'] && !$this->fields['captcha'])
        {
            /**
             * Suppression physique
             */
            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form'])) $db->query("ALTER TABLE `".$objForm->getDataTableName()."` DROP `{$this->_strOriginalFieldName}`");
        }

        // Il faut impacter les formules des champs calculés
        $arrPositions[$this->fields['position']] = null;

        // Autres changements de positions
        $db->query("SELECT max(position) as m FROM `ploopi_mod_forms_field` WHERE id_form = {$this->fields['id_form']}");
        $intMax = current($db->fetchrow());
        for ($intI = $this->fields['position']+1; $intI <= $intMax; $intI++) $arrPositions[$intI] = $intI-1;

        /**
         * Mise à jour des position des autres champs
         */
        $db->query("UPDATE `ploopi_mod_forms_field` SET position = position - 1 WHERE position > {$this->fields['position']} AND id_form = {$this->fields['id_form']}");

        if (!empty($arrPositions)) self::_updateFormulas($arrPositions);

        return parent::delete();
    }

    /**
     * Génère et met à jour le nom physique du champ utilisé pour l'export physique du formulaire.
     * Attention, fonction récursive.
     * @param boolean $booFixUnicity permet de gérer les problèmes de doublons
     */
    private function _createPhysicalName($intCount = 0)
    {
        if (!empty($this->fields['separator']) || !empty($this->fields['captcha'])) return null;

        $strFieldName = '';

        /**
         * Génération du nom physique
         */
        if (empty($this->fields['fieldname']))
        {
            /**
             * Suppression des accents
             * Conversion en minuscule
             * Conversion des caractères non alphanum
             * Suppression des espaces inutiles
             * Ajout d'un préfixe obligatoire "form_"
             */

            $strFieldName = substr('_'.trim(preg_replace("/[^[:alnum:]]+/", "_", ploopi\str::convertaccents(strtolower(trim($this->fields['name'])))), '_'), 0, 60);
        }
        else $strFieldName = $strFieldName = substr('_'.trim(preg_replace("/[^[:alnum:]]+/", "_", ploopi\str::convertaccents(strtolower(trim($this->fields['fieldname'])))), '_'), 0, 60);


        // Fix spécial doublon
        if ($intCount > 0) $strFieldName .= '_'.$intCount;

        /**
         * Vérification de l'unicité
         */
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('count(*) as c');
        $objQuery->add_from('ploopi_mod_forms_field');
        if (!$this->isnew()) $objQuery->add_where('id != %d', $this->fields['id']);
        $objQuery->add_where('id_form = %d', $this->fields['id_form']);
        $objQuery->add_where('fieldname = %s', $strFieldName);
        /* Pas Unique => on relance */
        if (current($objQuery->execute()->getarray(true)) > 0) $this->_createPhysicalName($intCount+1);
        else $this->fields['fieldname'] = $strFieldName;
    }


    /**
     * Génère le type SQL pour le champ
     * @return string $strType Type SQL
     */
    public function getSqlType()
    {
        if (!empty($this->fields['separator']) || !empty($this->fields['captcha'])) return null;

        $strType = '';

        /**
         * Gestion des types de champs
         */
        switch($this->fields['type'])
        {
            case 'autoincrement':
                $strType = "INT(10) UNSIGNED DEFAULT 0";
            break;

            case 'color':
                $strType = "VARCHAR(32) DEFAULT NULL";
            break;

            case 'textarea':
                $strType = "LONGTEXT DEFAULT NULL";
            break;

            case 'calculation':
                $strType = "DOUBLE DEFAULT NULL";
            break;

            case 'text':
                switch($this->fields['format'])
                {
                    case 'string':
                        $intSize = $this->fields['maxlength'];
                        if ($intSize <= 0 || $intSize > 255) $intSize = 255;
                        $strType = "VARCHAR({$intSize}) DEFAULT NULL";
                    break;

                    case 'integer':
                        $strType = "INT(10) DEFAULT NULL";
                    break;

                    case 'float':
                        $strType = "DOUBLE DEFAULT NULL";
                    break;

                    case 'date':
                        $strType = "INT(8) UNSIGNED DEFAULT NULL";
                    break;

                    case 'time':
                        $strType = "VARCHAR(16) DEFAULT NULL";
                    break;

                    default:
                        $strType = "VARCHAR(255) DEFAULT NULL";
                    break;
                }
            break;

            default:
                $strType = "VARCHAR(255) DEFAULT NULL";
            break;
        }

        return $strType;
    }

    /**
     * Retourne toutes les valeurs prises par le champ
     * @return array tableau
     */
    public function getValues($arrParams = null, $booDistinct = true)
    {
        $objForm = new formsForm();
        if ($objForm->open($this->fields['id_form']))
        {
            $objQuery = new ploopi\query_select();
            $objQuery->add_from($objForm->getDataTableName());
            $objQuery->add_select(($booDistinct ? 'DISTINCT ' : '').$this->fields['fieldname']);
            $objQuery->add_orderby($this->fields['fieldname']);
            // Application du filtre
            if (!empty($arrParams)) foreach($arrParams as $strField => $strValue) $objQuery->add_where("{$strField} = %s", $strValue);
            return $objQuery->execute()->getarray(true);
        }

        return array();
    }

    /**
     * Retourne le résultat d'une fonction d'agrégat sur le champ
     * @param string $strFunction fonction appliquée (CNT, SUM, MIN, MAX, AVG, AVG, STD, VAR)
     * @return mixed résultat ou null
     */
    public function getAggregate($strFunction)
    {
        $objForm = new formsForm();
        if ($objForm->open($this->fields['id_form']))
        {
            $objQuery = new ploopi\query_select();
            $objQuery->add_from($objForm->getDataTableName());
            switch(strtoupper($strFunction))
            {
                case 'CNT': // COUNT
                    $objQuery->add_select("count(`{$this->fields['fieldname']}`)");
                break;

                case 'SUM': // SUM
                    $objQuery->add_select("sum(`{$this->fields['fieldname']}`)");
                break;

                case 'MIN': // MIN
                    $objQuery->add_select("min(`{$this->fields['fieldname']}`)");
                break;

                case 'MAX': // MAX
                    $objQuery->add_select("max(`{$this->fields['fieldname']}`)");
                break;

                case 'AVG': // AVERAGE
                    $objQuery->add_select("avg(`{$this->fields['fieldname']}`)");
                break;

                case 'STD': // STANDARD DEVIATION
                    $objQuery->add_select("std(`{$this->fields['fieldname']}`)");
                break;

                case 'VAR': // VARIANCE
                    $objQuery->add_select("variance(`{$this->fields['fieldname']}`)");
                break;

                default: // ERREUR
                    return null;
                break;
            }

            $arrRes = current($objQuery->execute()->getarray(true));
            return empty($arrRes) ? null : $arrRes;
        }

        return null;
    }
}
?>
