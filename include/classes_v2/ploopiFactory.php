<?php
/**
 * Classe factory g�n�rique qui fournit une m�thode getInstance.
 * Cette m�thode est capable de cr�er une instance de la classe appelante (en utilisant �ventuellement le contructeur public et les param�tres adapt�s)
 * @param array $arrArgs arguments dynamiques
 * @return object instance de l'objet
 */

abstract class ploopiFactory
{
    public static function getInstance()
    {
        // Lecture des arguments
        $arrArgs = func_get_args();

        // D�termination de la classe appelante (h�rit�e)
        $strClassName = get_called_class();

        // V�rification d'existence (utile ?)
        if (!class_exists($strClassName)) throw new ploopiException("Unknown class &laquo; {$strClassName} &raquo;");

        // Cr�ation d'un objet Reflexion de la classe
        $objReflection = new ReflectionClass($strClassName);

        // V�rification de l'instanciabilit� de la classe appelante (h�rit�e)
        if (!$objReflection->isInstantiable()) throw new ploopiException("Class &laquo; {$strClassName} &raquo; not instanciable");

        // Lecture du constructeur de la classe appelante (h�rit�e)
        $objConstructMethod = $objReflection->getConstructor();

        // V�rification de l'existence d'un constructeur public (pour l'appeler)
        if ($objConstructMethod instanceof ReflectionMethod && $objConstructMethod->isPublic())
        {
            // On v�rifie le nombre de param�tres attendus par le constructeur de la classe appelante (h�rit�e)
            $intRequiredParams = $objConstructMethod->getNumberOfRequiredParameters();
            if (sizeof($arrArgs) < $intRequiredParams) throw new ploopiException("The factory method &laquo; {$strClassName}.getInstance() &raquo; requires at least {$intRequiredParams} parameter(s) : \n".implode(', ', $objMethod->getParameters()));

            return $objReflection->newInstanceArgs($arrArgs);
        }
        else return $objReflection->newInstanceArgs();
    }

    public function getClone() { return clone $this; }
}
