<?php
/*
    Copyright (c) 2009-2011 Ovensia
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
 * Gestion des expressions booléennes.
 *
 * @package forms
 * @subpackage BooleanParser
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe permettant de valider et calculer une expression booléenne.
 *
 * Accepte les opérateurs 'AND', 'OR', 'NOT', '&&', '||', '&', '|', '!'.
 *
 * @package forms
 * @subpackage BooleanParser
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

class formsBooleanParser
{
    private $arrValues;
    private $objExprA;
    private $objExprB;
    private $arrExpr;
    private $strVal;
    private $strExpression;
    private $strOperator;

    /**
     * Opérateurs supportés
     *
     * @var array
     */
    private static $arrOperators = array(
        '&' => true,
        '|' => true,
        '!' => true,
    );

    /**
     * Définition des Opérateurs
     *
     * @var array
     */
    private static $arrOperatorsDef = array(
        '&' => 'AND',
        '|' => 'OR',
        '!' => 'NOT',
    );

    /**
     * Expression rationnelle permettant de nettoyer une formule de ses caractères non valides et/ou inutiles
     *
     * @var unknown_type
     */
    private static $strRegexpCleanFormula = '/[^a-zA-Z0-9\(\)&\|!]/';

    /**
     * Constructeur de la classe
     *
     * @param string $strExpression expression arithmétique à évaluer
     * @param array $arrValues tableau associant à chaque variable de l'expression une valeur
     */
    public function __construct($strExpression, $arrValues = array())
    {
        // On traduit l'expression
        $this->strExpression = preg_replace(
            array(
                '/([ \)])AND([ \(])/',
                '/([ \)])OR([ \(])/',
                '/([ &|]?)NOT([ \(])/',
                '/&+/',
                '/\|+/',
                self::$strRegexpCleanFormula,
            ),
            array(
                '$1&$2',
                '$1|$2',
                '$1!$2',
                '&',
                '|',
                '',
            ),
            strtoupper($strExpression)
        );

        $this->arrValues = $arrValues;
        $this->objExprA = null;
        $this->objExprB = null;
        $this->arrExpr = null;
        $this->strOperator = null;

        if ($this->strExpression == '' || is_null($this->strExpression))
        {
            $this->strVal = null;
        }
        else if (is_bool($this->strExpression))
        {
            $this->strVal = $this->strExpression;
        }
        else
        {
            $this->strVal = null;

            $p=0;

            $cp0 = 0;

            $l = strlen($this->strExpression);

            //on recupere la liste des arrOperators
            $arrOperators = self::$arrOperators;

            // On parcourt l'expression caractère par caractère
            // On compte les parenthèses ( et ) pour vérifier que le nombre est correct
            for ($i = 0; $i < $l; $i++)
            {
                // Parenthèse ouvrante
                if ($this->strExpression[$i] == '(') $p++;
                // Parenthèse fermante
                else if ($this->strExpression[$i] == ')') $p--;
                // Pas de parenthèse encore ouverte
                else if ($p == 0)
                {
                    // Opérateur OU (prio des opérateurs)
                    if ($this->strExpression[$i] == '|')
                    {
                        $arrOperators['&'] = false;
                    }
                }
            }

            if ($p!==0)
            {
                throw new Exception('Nombre de parenthèses incorrect');
            }

            for ($i = $l-1; $i >= 0; $i--)
            {
                if ($p === 0)
                {
                    $cp0++;
                }

                // Parenthèse ouvrante
                if ($this->strExpression[$i] == '(') $p++;
                // Parenthèse fermante
                else if ($this->strExpression[$i] == ')') $p--;
                // Pas de parenthèse encore ouverte && Opérateur connu && Opérateur "autorisé"
                else if ($p == 0 && in_array($this->strExpression[$i], array_keys(self::$arrOperators)) && $arrOperators[$this->strExpression[$i]])
                {
                    // Opérateur
                    $this->strOperator = $this->strExpression[$i];

                    if ($this->strOperator == '!')
                    {
                        // Détection cas particulier double opérateur
                        if (isset($this->strExpression[$i-1]) && in_array($this->strExpression[$i-1], array_keys(self::$arrOperators)))
                        {
                            $this->strOperator = $this->strExpression[$i-1];

                            // Expression A (partie à gauche de l'opérateur)
                            $this->objExprA = new self(substr($this->strExpression, 0, $i-1), $this->arrValues);

                            // Expression B (partie à droite de l'opérateur)
                            $this->objExprB = new self(substr($this->strExpression, $i), $this->arrValues);

                            break;
                        }
                        else
                        {
                            // CAS C1! non valide
                            if (substr($this->strExpression, 0, $i) != '') throw new Exception ('Erreur dans l\'enchainement des opérateurs...');
                            if (substr($this->strExpression, $i+1) == '') throw new Exception ('Erreur dans l\'enchainement des opérateurs...');
                        }
                    }

                    // Expression A (partie à gauche de l'opérateur)
                    $this->objExprA = new self(substr($this->strExpression, 0, $i), $this->arrValues);

                    // Expression B (partie à droite de l'opérateur)
                    $this->objExprB = new self(substr($this->strExpression, $i+1), $this->arrValues);

                    $this->strOperator;
                    break;
                }
            }
            if (is_null($this->objExprA) && $cp0 == 1)
            {
                if (is_numeric(substr($this->strExpression, 1, $l-2))) $this->strVal = substr($this->strExpression, 1, $l-2);
                else $this->objExprA = new self(substr($this->strExpression, 1, $l-2), $this->arrValues);
            }
            if (is_null($this->objExprA))
            {
                $first = strpos($this->strExpression, '(');
                $end = strrpos($this->strExpression, ')');

                $this->strVal = $this->strExpression;
            }
        }
    }

    /**
     * Retourne les variables rencontrées dans l'expression
     */

    public function getVars()
    {
        $arrVars = array();

        if (!is_null($this->strVal)) { if (!is_bool($this->strVal)) $arrVars[$this->strVal] = $this->strVal; }
        else
        {
            if (!is_null($this->objExprA)) $arrVars = array_merge($arrVars, $this->objExprA->getVars());
            if (!is_null($this->objExprB)) $arrVars = array_merge($arrVars, $this->objExprB->getVars());
        }

        return $arrVars;
    }

    /**
     * Retourne la valeur d'une variable
     *
     * @param string $str variable
     * @return boolean
     */

    private function boolVal($str)
    {
        if (isset($this->arrValues[$str])) return $this->arrValues[$str] == true;
        else return $str == true;
    }

    /**
     * Retourne l'expression nettoyée
     */

    public function getExpression() { return $this->strExpression; }

    /**
     * Retourne la valeur numérique de l'expression
     *
     * @return mixed
     */
    public function getVal()
    {
        static $c = 0;

        if (!is_null($this->strVal)) return $this->boolVal($this->strVal);

        else if (is_null($this->objExprB))
        {
            if (is_null($this->objExprA))
                throw new Exception ('Erreur dans l\'enchainement des opérateurs...');
            else
                return $this->objExprA->getVal();
        }
        else if ($this->strOperator == '&')
        {
            return $this->objExprA->getVal() && $this->objExprB->getVal();
        }
        else if ($this->strOperator == '|')
        {
            return $this->objExprA->getVal() || $this->objExprB->getVal();
        }
        else if ($this->strOperator == '!')
        {
            /*
            if (!is_null($this->objExprA->getVal()))
            {
                throw new Exception ('Erreur dans l\'enchainement des operateurs...');
            }
            else return !$this->objExprB->getVal();
            */
            return !$this->objExprB->getVal();
        }
        else
        {
            throw new Exception('Opérateur '.$this->strOperator.' non défini');
            return null;
        }
    }


    /**
     * Affiche l'expression sous forme d'une arbre
     */
    public function displayTree()
    {
        if (!is_null($this->strVal)) echo ovensia\ploopi\str::htmlentities($this->strVal);
        else if (is_null($this->objExprB) && !is_null($this->objExprA)) $this->objExprA->displayTree();
        else if (is_null($this->objExprA) && !is_null($this->objExprB))
        {
            if (!is_null($this->strOperator)) echo ovensia\ploopi\str::htmlentities($this->strOperator);
            $this->objExprB->displayTree();
        }
        else if (!is_null($this->objExprA) && !is_null($this->objExprB))
        {
            echo '<ul><li>';
            $this->objExprA->displayTree();
            echo '</li><li>', $this->strOperator, '</li><li>';
            $this->objExprB->displayTree();
            echo '</li></ul>';
        }
    }


    /**
     * Retourne la notation polonaise inversée de l'expression arithmétique
     *
     * @return string
     */
    public function toRPN()
    {
        if (!is_null($this->strVal)) return $this->strVal;
        else if (is_null($this->objExprB) && !is_null($this->objExprA)) return $this->objExprA->toRPN();
        else if (is_null($this->objExprA) && !is_null($this->objExprB))
        {
            if (!is_null($this->strOperator)) return $this->objExprB->toRPN(). ' '. $this->strOperator;
            else return $this->objExprB->toRPN();
        }
        else if (!is_null($this->objExprA) && !is_null($this->objExprB)) return $this->objExprA->toRPN().' '.$this->objExprB->toRPN().' '.$this->strOperator;
    }


    /**
     * Affecte une valeur à une variable
     *
     * @param string $strVar variable
     * @param float $floVal valeur de la variable
     */
    public function setVal($strVar, $floVal)
    {
        $this->arrValues[$strVar] = $floVal;

        if (!is_null($this->objExprA)) $this->objExprA->setVal($strVar, $floVal);
        if (!is_null($this->objExprB)) $this->objExprB->setVal($strVar, $floVal);
    }

    /**
     * Retourne les fonctions disponibles
     *
     * @return array fonctions disponibles
     */
    static public function getFunctions()
    {
        return self::$arrFunctions;
    }

    /**
     * Retourne la définition des fonctions disponibles
     *
     * @return array définition des fonctions disponibles
     */
    static public function getFunctionsDef()
    {
        return self::$arrFunctionsDef;
    }

    /**
     * Retourne les opérateurs disponibles
     *
     * @return array opérateurs disponibles
     */
    static public function getOperators()
    {
        return self::$arrOperators;
    }

    /**
     * Retourne la définition des opérateurs disponibles
     *
     * @return array définition des opérateurs disponibles
     */
    static public function getOperatorsDef()
    {
        return self::$arrOperatorsDef;
    }


    static public function test()
    {
        try {
            //$objParser = new self('NOT(C1 NOT AND (C2 OR(C3 &&& NOT C4)))', array('C1' => false, 'C2' => true, 'C3' => true, 'C4' => true));
            //$objParser = new self('NOT(C1 AND NOT (C2 OR(C3 &&& NOT C4)))', array('C1' => false, 'C2' => true, 'C3' => true, 'C4' => true));
            $objParser = new self('NOT C1', array('C1' => false, 'C2' => true, 'C3' => true, 'C4' => true));
            echo '<br />'.$objParser->toRPN();
            echo '<br />'.$objParser->displayTree();

            var_dump($objParser->getVal());
        }
        catch (Exception $e) { echo $e->getMessage(); }
    }

}

?>
