<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';
include_once './include/classes/query.php';

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsRecord.php';
include_once './modules/forms/classes/formsArithmeticParser.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_field
 *
 * @package forms
 * @subpackage field
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class formsField extends data_object
{

    private $_strOriginalFieldName;
    private $_strOriginalType;
    private $_strOriginalFormula;


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
     * Constructeur de la classe
     *
     * @return field
     */

    public function __construct()
    {
        $this->_strOriginalFieldName = '';
        $this->_strOriginalType = '';
        $this->_strOriginalFormula = '';

        parent::__construct('ploopi_mod_forms_field');
    }

    /**
     * Ouvre un champ
     * @see include/classes/data_object::open()
     */
    public function open($intId)
    {
        $booOpen = parent::open($intId);

        if (!$this->fields['separator'] && !$this->fields['captcha'])
        {
            $this->_strOriginalFieldName = $this->fields['fieldname'];
            $this->_strOriginalType = $this->getSqlType();
            $this->_strOriginalFormula = $this->fields['formula'];
        }

        return $booOpen;
    }

    /**
     * Enregistre le champ
     *
     * @return int identifiant du champ enregistré
     */
    public function save($booUpdateTable = true)
    {
        global $db;

        if (empty($this->fields['separator']) && empty($this->fields['captcha'])) $this->_createPhysicalName();

        $booIsNew = $this->isnew();

        /**
         * Enregistrement
         */
        $res = parent::save();

        /**
         * Mise à jour de la bdd
         */
        if ($booUpdateTable && !empty($this->fields['fieldname']))
        {

            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form']))
            {
                if ($booIsNew)
                {
                    /**
                     * Ajout du champ physique
                     */
                    $db->query("ALTER TABLE `".$objForm->getDataTableName()."` ADD `{$this->fields['fieldname']}` ".$this->getSqlType());
                    $db->query("ALTER TABLE `".$objForm->getDataTableName()."` ADD INDEX (`{$this->fields['fieldname']}`)");
                }
                else
                {
                    /**
                     * Mise à jour du champ physique
                     */

                    // Lecture du type actuel du champ
                    $strType = $this->getSqlType();

                    // Modification de la structure si changement de nom ou de type
                    if ($this->_strOriginalFieldName != $this->fields['fieldname'] || $this->_strOriginalType != $strType)
                    {
                        $db->query("ALTER TABLE `".$objForm->getDataTableName()."` CHANGE `{$this->_strOriginalFieldName}` `{$this->fields['fieldname']}` {$strType}");
                    }
                }

                $objForm->updateMetabase();
            }
        }

        /**
         * Mise à jour des données de champs calculés
         */

        if ($this->fields['type'] == 'calculation' && $this->fields['formula'] != $this->_strOriginalFormula)
        {
            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form']))
            {
                // Lecture des champs du formulaire
                $arrObjFields = $objForm->getFields();

                // Lecture des enregistrement du formulaire
                $objQuery = new ploopi_query_select();
                $objQuery->add_select('`#id` as id');
                $objQuery->add_from($objForm->getDataTableName());

                // Pour chaque enregistrement du formulaire
                foreach($objQuery->execute()->getarray(true) as $intId)
                {
                    // Traitement de l'enregistrement
                    $objRecord = new formsRecord($objForm);
                    if ($objRecord->open($intId))
                    {
                        // Initalisation du tableau des variables
                        $booCalculation = false;
                        $arrVariables = array();
                        foreach($arrObjFields as $objField)
                        {
                            if ($objField->fields['type'] == 'calculation') $booCalculation = true;

                            $arrVariables['C'.$objField->fields['position']] = $objRecord->fields[$objField->fields['fieldname']];
                            if (!is_numeric($arrVariables['C'.$objField->fields['position']])) $arrVariables['C'.$objField->fields['position']] = 0;
                        }

                        /**
                         * Il y avait au moins un champ de type "calculation"
                         */

                        if ($booCalculation)
                        {
                            foreach($arrObjFields as $objField)
                            {
                                if ($objField->fields['type'] == 'calculation')
                                {
                                    try {
                                        // Interprétation du calcul
                                        $objParser = new formsArithmeticParser($objField->fields['formula'], $arrVariables);
                                        $arrVariables['C'.$objField->fields['position']] = $objRecord->fields[$objField->fields['fieldname']] = $objParser->getVal();
                                    }
                                    catch(Exception $e) { }
                                }
                            }

                            $objRecord->save();
                        }
                    }
                }
            }
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
        global $db;

        /**
         * Suppression physique
         */
        $objForm = new formsForm();
        if ($objForm->open($this->fields['id_form'])) $db->query("ALTER TABLE `".$objForm->getDataTableName()."` DROP `{$this->_strOriginalFieldName}`");

        /**
         * Mise à jour des position des autres champs
         */
        $db->query("UPDATE `ploopi_mod_forms_field` SET position = position - 1 WHERE position > {$this->fields['position']} AND id_form = {$this->fields['id_form']}");

        return parent::delete();
    }

    /**
     * Génère et met à jour le nom physique du champ utilisé pour l'export physique du formulaire.
     * Attention, fonction récursive.
     * @param boolean $booFixUnicity permet de gérer les problèmes de doublons
     */
    private function _createPhysicalName($booFixUnicity = false)
    {
        if (!empty($this->fields['separator']) || !empty($this->fields['captcha'])) return null;

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

            $this->fields['fieldname'] = '_'.trim(preg_replace("/[^[:alnum:]]+/", "_", ploopi_convertaccents(strtolower(trim($this->fields['name'])))), '_');
        }
        else
        {
            // Fix spécial doublon
            if ($booFixUnicity) $this->fields['fieldname'] = $this->fields['fieldname'].'_';
            else
            {
                // Permet de protéger les modifications manuelle de la valeur du nom physique
                $this->fields['fieldname'] = '_'.trim(preg_replace("/[^[:alnum:]]+/", "_", ploopi_convertaccents(strtolower(trim($this->fields['fieldname'])))), '_');
            }
        }

        /**
         * Vérification de l'unicité
         */
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('count(*) as c');
        $objQuery->add_from('ploopi_mod_forms_field');
        if (!$this->isnew()) $objQuery->add_where('id != %d', $this->fields['id']);
        $objQuery->add_where('id_form = %d', $this->fields['id_form']);
        $objQuery->add_where('fieldname = %s', $this->fields['fieldname']);
        /* Pas Unique => on relance */
        if (current($objQuery->execute()->getarray(true)) > 0) $this->_createPhysicalName(true);
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
                $strType = "VARCHAR(32) DEFAULT ''";
            break;

            case 'textarea':
                $strType = "LONGTEXT DEFAULT ''";
            break;

            case 'text':
                switch($this->fields['format'])
                {
                    case 'string':
                        $intSize = $this->fields['maxlength'];
                        if ($intSize <= 0 || $intSize > 255) $intSize = 255;
                        $strType = "VARCHAR({$intSize}) DEFAULT ''";
                    break;

                    case 'integer':
                        $strType = "INT(10) DEFAULT 0";
                    break;

                    case 'float':
                        $strType = "DOUBLE DEFAULT 0";
                    break;

                    case 'date':
                        $strType = "INT(8) UNSIGNED DEFAULT 0";
                    break;

                    case 'time':
                        $strType = "VARCHAR(16) DEFAULT ''";
                    break;

                    default:
                        $strType = "VARCHAR(255) DEFAULT ''";
                    break;
                }
            break;

            default:
                $strType = "VARCHAR(255) DEFAULT ''";
            break;
        }

        return $strType;
    }

    /**
     * Retourne le résultat d'une fonction d'agrégat sur le champ
     * @param string $strFunction fonction appliquée
     * @return mixed résultat ou null
     */
    public function getAggregate($strFunction)
    {
        $strFunction = strtolower($strFunction);

        if (in_array($strFunction, array('min', 'max', 'avg', 'sum', 'count')))
        {
            $objForm = new formsForm();
            if ($objForm->open($this->fields['id_form']))
            {
                $objQuery = new ploopi_query_select();
                $objQuery->add_from($objForm->getDataTableName());
                $objQuery->add_select("{$strFunction}({$this->fields['fieldname']}) as v");
                $arrRes = current($objQuery->execute()->getarray(true));
                return empty($arrRes) ? 0 : $arrRes;
            }
        }

        return null;
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
            $objQuery = new ploopi_query_select();
            $objQuery->add_from($objForm->getDataTableName());
            $objQuery->add_select(($booDistinct ? 'DISTINCT ' : '').$this->fields['fieldname']);
            $objQuery->add_orderby($this->fields['fieldname']);
            // Application du filtre
            if (!empty($arrParams)) foreach($arrParams as $strField => $strValue) $objQuery->add_where("{$strField} = %s", $strValue);
            return $objQuery->execute()->getarray(true);
        }

        return array();
    }
}
?>
