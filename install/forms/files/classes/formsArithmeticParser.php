<?php
/*
    Copyright (c) 2009-2010 Ovensia
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
 * Gestion des expressions arithm�tiques.
 *
 * @package forms
 * @subpackage ArithmeticParser
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe permettant de valider et calculer une expression arithm�tique.
 *
 * Accepte les op�rateurs '+', '-', '/', '*', '%', '^'.
 *
 * Accepte �galement les fonctions arithm�tiques de php :
 *
 * abs : Valeur absolue
 * acos : Arc cosinus
 * acosh : Arc cosinus hyperbolique
 * asin : Arc sinus
 * asinh : Arc sinus hyperbolique
 * atan : Arc tangente
 * atanh : Arc tangente hyperbolique
 * ceil : Arrondit au nombre sup�rieur
 * cos : Cosinus
 * cosh : Cosinus hyperbolique
 * deg2rad : Convertit un nombre de degr�s en radians
 * exp : Calcul l'exponentielle
 * floor : Arrondit � l'entier inf�rieur
 * fmod : Retourne le reste de la division
 * log10 : Logarithme en base 10
 * log1p : Calcule pr�cis�ment log(1 + nombre)
 * log : Logarithme naturel (n�p�rien)
 * pow : Expression exponentielle
 * rad2deg : Conversion de radians en degr�s
 * round : Arrondi un nombre � virgule flottante
 * sin : Sinus
 * sinh : Sinus hyperbolique
 * sqrt : Racine carr�e
 * tan : Tangente
 * tanh : Tangente hyperbolique
 *
 * min : D�termine la plus petite valeur
 * max : D�termine la plus grande valeur
 *
 * @package forms
 * @subpackage ArithmeticParser
 * @copyright Ovensia/SZSIC
 * @license non d�finie (non libre)
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

class formsArithmeticParser
{
    private $arrVars;
    private $objExprA;
    private $objExprB;
    private $arrExpr;
    private $strVal;
    private $strFunc;

    private $strOperator;

    /**
     * Op�rateurs support�s
     *
     * @var array
     */
    private static $arrOperators = array(
        '+' => true,    // addition
        '-' => true,    // soustraction
        '*' => true,    // multiplication
        '/' => true,    // division
        '^' => true,    // exposant
        '%' => true     // modulo
    );

    /**
     * D�finition des Op�rateurs
     *
     * @var array
     */
    private static $arrOperatorsDef = array(
        '+' => 'Addition',
        '-' => 'Soustraction',
        '*' => 'Multiplication',
        '/' => 'Division',
        '^' => 'Exposant',
        '%' => 'Modulo'
    );


    /**
     * Fonctions arithm�tiques support�s
     *
     * @var array
     */
    private static $arrFunctions = array(
        'abs',
        'acos',
        'acosh',
        'asin',
        'asinh',
        'atan',
        'atanh',
        'ceil',
        'cos',
        'cosh',
        'deg2rad',
        'exp',
        'floor',
        'fmod',
        'log10',
        'log1p',
        'log',
        'max',
        'min',
        'pow',
        'rad2deg',
        'round',
        'sin',
        'sinh',
        'sqrt',
        'tan',
        'tanh',
        'mktime',
        'substr',
        'time',
    );

    /**
     * D�finition des fonctions arithm�tiques
     *
     * @var array
     */
    private static $arrFunctionsDef = array(
        'abs' => 'abs(x) : Valeur absolue',
        'acos' => 'acos(x) : Arc cosinus',
        'acosh' => 'acosh(x) : Arc cosinus hyperbolique',
        'asin' => 'asin(x) : Arc sinus',
        'asinh' => 'asinh(x) : Arc sinus hyperbolique',
        'atan' => 'atan(x) : Arc tangente',
        'atanh' => 'atanh(x) : Arc tangente hyperbolique',
        'ceil' => 'ceil(x) : Arrondit au nombre sup�rieur',
        'cos' => 'cos(x) : Cosinus',
        'cosh' => 'cosh(x) : Cosinus hyperbolique',
        'deg2rad' => 'deg2rad(x) : Convertit un nombre de degr�s en radians',
        'exp' => 'exp(x) : Calcul l\'exponentielle',
        'floor' => 'floor(x) : Arrondit � l\'entier inf�rieur',
        'fmod' => 'fmod(x,y) : Retourne le reste de la division',
        'log10' => 'log10(x) : Logarithme en base 10',
        'log1p' => 'logip(x) : Calcule pr�cis�ment log(1 + nombre)',
        'log' => 'log(x) : Logarithme naturel (n�p�rien)',
        'max' => 'max(x,y,z,...) : D�termine la plus grande valeur',
        'min' => 'min(x,y,z,...) : D�termine la plus petite valeur',
        'pow' => 'pow(x,y) : Expression exponentielle',
        'rad2deg' => 'rad2deg(x) : Conversion de radians en degr�s',
        'round' => 'round(x,y) Arrondi un nombre � virgule flottante',
        'sin' => 'sin(x) : Sinus',
        'sinh' => 'sinh(x) : Sinus hyperbolique',
        'sqrt' => 'sqrt(x) : Racine carr�e',
        'tan' => 'tan(x) : Tangente',
        'tanh' => 'tanh(x) : Tangente hyperbolique',
        'mktime' => 'mktime(H, i, s, n, j, Y) : Timestamp unix',
        'substr' => 'substr(x,y,z) : Sous-cha�ne',
        'time' => 'time() : Timestamp unix actuel',
    );

    /**
     * Expression rationnelle permettant de nettoyer une formule de ses caract�res non valides et/ou inutiles
     *
     * @var unknown_type
     */
    private static $strRegexpCleanFormula = '/[^a-zA-Z0-9\.,\(\)\+\-\*\/%\^]/';

    /**
     * Constructeur de la classe
     *
     * @param string $strExpression expression arithm�tique � �valuer
     * @param array $arrVars tableau associant � chaque variable de l'expression une valeur
     */
    function __construct($strExpression, $arrVars = array())
    {
        // On nettoie l'expression
        $strExpression = preg_replace(self::$strRegexpCleanFormula, '', $strExpression);

        //$strExpression = str_replace(str_split(" \n\r\t"), '', $strExpression);

        $this->arrVars = $arrVars;
        $this->objExprA = null;
        $this->objExprB = null;
        $this->arrExpr = null;
        $this->strOperator = null;

        if (empty($strExpression))
        {
            $this->strVal = 0;
        }
        else if (is_numeric($strExpression))
        {
            $this->strVal = $strExpression;
        }
        else
        {
            $this->strVal = null;

            $p=0;

            $cp0 = 0;

            $l = strlen($strExpression);

            //on recupere la liste des arrOperators
            $arrOperators = self::$arrOperators;

            // On parcourt l'expression caract�re par caract�re
            // On compte les parenth�ses ( et ) pour v�rifier que le nombre est correct
            for ($i = 0; $i < $l; $i++)
            {
                // Parenth�se ouvrante
                if ($strExpression[$i] == '(') $p++;
                // Parenth�se fermante
                else if ($strExpression[$i] == ')') $p--;
                // Pas de parenth�se encore ouverte
                else if ($p == 0)
                {
                    // Op�rateur + ou -
                    if ($strExpression[$i] == '+' || $strExpression[$i] == '-')
                    {
                        $arrOperators['*'] = false;
                        $arrOperators['/'] = false;
                        $arrOperators['%'] = false;
                        $arrOperators['^'] = false;
                    }
                    // Op�rateur * ou /
                    else if ($strExpression[$i] =='*' || $strExpression[$i] =='/' || $strExpression[$i] =='%')
                    {
                        $arrOperators['^'] = false;
                    }
                }
            }

            if ($p!==0)
            {
                throw new Exception('Nombre de parenth�ses incorrect');
            }

            for ($i = $l-1; $i >= 0; $i--)
            {
                if ($p === 0)
                {
                    $cp0++;
                }

                // Parenth�se ouvrante
                if ($strExpression[$i] == '(') $p++;
                // Parenth�se fermante
                else if ($strExpression[$i] == ')') $p--;
                // Pas de parenth�se encore ouverte && Op�rateur connu && Op�rateur "autoris�"
                else if ($p == 0 && in_array($strExpression[$i], array_keys(self::$arrOperators)) && $arrOperators[$strExpression[$i]])
                {
                    // Op�rateur
                    $this->strOperator = $strExpression[$i];

                    // Expression A (partie � gauche de l'op�rateur)
                    $this->objExprA = new self(substr($strExpression, 0, $i), $arrVars);

                    // Expression B (partie � droite de l'op�rateur)
                    $this->objExprB = new self(substr($strExpression, $i+1), $arrVars);
                    break;
                }
            }
            if (is_null($this->objExprA) && $cp0 == 1)
            {
                if (is_numeric(substr($strExpression, 1, $l-2)))
                {
                    $this->strVal = substr($strExpression, 1, $l-2);
                }
                else
                {
                    $this->objExprA = new self(substr($strExpression, 1, $l-2), $arrVars);
                }
            }
            if (is_null($this->objExprA))
            {
                $first = strpos($strExpression, '(');
                $end = strrpos($strExpression, ')');

                // Est-ce une fonction ?
                if ($first !== false)
                {
                    $this->strFunc = substr($strExpression, 0, $first);

                    if (!in_array($this->strFunc, self::$arrFunctions))
                    {
                        throw new Exception($p > 0 ? "Fonction \"{$this->strFunc}\" inconnue" : "Variable \"{$strExpression}\" inconnue");
                    }
                    else
                    {
                        // Gestion des fonctions imbriqu�es � param�tres multiples

                        $arrStrExpr = array();
                        $p = 0;
                        $e = 0;

                        for ($i = $first+1; $i <= $end-1; $i++)
                        {
                            // Parenth�se ouvrante
                            if ($strExpression[$i] == '(') $p++;
                            // Parenth�se fermante
                            elseif ($strExpression[$i] == ')') $p--;

                            if ($p === 0 && $strExpression[$i] == ',') $e++;
                            else
                            {
                                if (!isset($arrStrExpr[$e])) $arrStrExpr[$e] = '';
                                $arrStrExpr[$e] .= $strExpression[$i];
                            }
                        }
                        $this->arrExpr = array();

                        foreach($arrStrExpr as $strExpr) $this->arrExpr[] = new self($strExpr, $arrVars);
                    }
                }
                // Ca doit �tre une variable
                else $this->strVal = $strExpression;
            }
        }
    }

    /**
     * Retourne la valeur num�rique d'une variable
     *
     * @param string $str variable
     * @return float/boolean
     */

    private function numVal($str)
    {
        if (isset($this->arrVars[$str])) return floatval($this->arrVars[$str]);
        else if (is_numeric($str)) return floatval($str);
        else return false;
    }

    /**
     * Retourne la valeur num�rique de l'expression
     *
     * @return mixed
     */
    public function getVal()
    {
        if (!is_null($this->strFunc))
        {
            // Analyse des param�tres de la fonction
            $v = array();
            foreach($this->arrExpr as $strExpr) $v[] = $strExpr->getVal();

            $res = null;

            // Evaluation via le parser PHP
            ploopi_unset_error_handler();
            // Evaluation de l'expression
            eval("\$res = {$this->strFunc}(".implode(',', $v).");");
            ploopi_set_error_handler();

            if(is_null($res)) throw new Exception ("Erreur lors de l'�valuation de l'expression {$this->strFunc}(".implode(',', $v).");");

            return $res;
        }

        if (!is_null($this->strVal)) return $this->numVal($this->strVal);

        else if (is_null($this->objExprB))
        {
            if (is_null($this->objExprA))
                throw new Exception ('Erreur probablement dans l\'enchainement des operateurs...');
            else
                return $this->objExprA->getVal();
        }
        else if ($this->strOperator == '+')
        {
            return $this->objExprA->getVal() + $this->objExprB->getVal();
        }
        else if ($this->strOperator == '-')
        {
            return $this->objExprA->getVal() - $this->objExprB->getVal();
        }
        else if ($this->strOperator == '*')
        {
            return $this->objExprA->getVal() * $this->objExprB->getVal();
        }
        else if ($this->strOperator == '/')
        {
            $a = $this->objExprB->getVal();

            if ($a == 0)
                throw new Exception('Division par 0');
            else
                return $this->objExprA->getVal() / $a;
        }
        else if ($this->strOperator == '%')
        {
            $a = $this->objExprB->getVal();

            if ($a == 0)
                throw new Exception('Division par 0');
            else
                return $this->objExprA->getVal() % $a;
        }
        else if ($this->strOperator == '^')
        {
            $a = $this->objExprA->getVal();
            $b = $this->objExprB->getVal();

            if ($b == 0) return 1;
            else if ($a == 0) return 0;
            else if ($b != intval($b) && $a <= 0)
            {
                if (1/$b == intval(1/$b) && abs(1/$b) % 2 == 1) return -pow(-$a, $b);
                else throw new Exception('On ne peut pas prendre un exposant non entier sur un nombre negatif');
            }
            else
            {
                return pow($a, intval($b));
            }
        }
        else
        {
            throw new Exception('operator '.$this->strOperator.'non defini');
            return null;
        }
    }


    /**
     * Affiche l'expression sous forme d'une arbre
     */
    public function displayTree()
    {
        if (!is_null($this->strFunc))
        {
            echo $this->strFunc.'(';
            foreach($this->arrExpr as $strExpr) $strExpr->displayTree();
            echo ')';
        }
        else if (!is_null($this->strVal)) echo $this->strVal;
        else if (is_null($this->objExprB) && !is_null($this->objExprA)) $this->objExprA->displayTree();
        else if (is_null($this->objExprA) && !is_null($this->objExprB))
        {
            if (!is_null($this->strOperator)) echo $this->strOperator;
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
     * Retourne la notation polonaise invers�e de l'expression arithm�tique
     *
     * @return string
     */
    public function toRPN()
    {
        if (!is_null($this->strFunc))
        {
            $str = '';
            foreach($this->arrExpr as $strExpr) $str .= $strExpr->toRPN().' ';
            $str .= $this->strFunc;
            return $str;
        }
        else if (!is_null($this->strVal)) return $this->strVal;
        else if (is_null($this->objExprB) && !is_null($this->objExprA)) return $this->objExprA->toRPN();
        else if (is_null($this->objExprA) && !is_null($this->objExprB))
        {
            if (!is_null($this->strOperator)) return $this->objExprB->toRPN(). ' '. $this->strOperator;
            else return $this->objExprB->toRPN();
        }
        else if (!is_null($this->objExprA) && !is_null($this->objExprB)) return $this->objExprA->toRPN().' '.$this->objExprB->toRPN().' '.$this->strOperator;
    }


    /**
     * Affecte une valeur � une variable
     *
     * @param string $strVar variable
     * @param float $floVal valeur de la variable
     */
    public function setVal($strVar, $floVal)
    {
        $this->arrVars[$strVar] = $floVal;

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
     * Retourne la d�finition des fonctions disponibles
     *
     * @return array d�finition des fonctions disponibles
     */
    static public function getFunctionsDef()
    {
        return self::$arrFunctionsDef;
    }

    /**
     * Retourne les op�rateurs disponibles
     *
     * @return array op�rateurs disponibles
     */
    static public function getOperators()
    {
        return self::$arrOperators;
    }

    /**
     * Retourne la d�finition des op�rateurs disponibles
     *
     * @return array d�finition des op�rateurs disponibles
     */
    static public function getOperatorsDef()
    {
        return self::$arrOperatorsDef;
    }


}

?>
